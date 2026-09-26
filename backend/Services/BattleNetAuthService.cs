using System.Net.Http.Headers;
using System.Text.Json;
using Microsoft.Extensions.Options;
using AmusedToDeath.Api.Configuration;
using AmusedToDeath.Api.Models;

namespace AmusedToDeath.Api.Services;

/// <summary>
/// Battle.net OAuth login + WoW account character discovery.
///
/// Distinct from BattleNetService (which uses an app-level client-credentials
/// token to read item levels). This one drives the *user* login: it runs the
/// authorization-code flow, reads the account identity, and lists the user's
/// characters from the WoW Account Profile Summary so they can pick which to
/// import. Guild membership is resolved per chosen character at import time.
/// </summary>
public sealed class BattleNetAuthService
{
    private readonly HttpClient _http;
    private readonly BattleNetOptions _options;
    private readonly ILogger<BattleNetAuthService> _logger;

    public BattleNetAuthService(HttpClient http, IOptions<AppOptions> options, ILogger<BattleNetAuthService> logger)
    {
        _http = http;
        _options = options.Value.BattleNet;
        _logger = logger;
    }

    public string GuildName => _options.GuildName;

    private string OAuthHost => $"https://{_options.Region}.battle.net";
    private string ApiHost => $"https://{_options.Region}.api.blizzard.com";
    private string ProfileNamespace => $"profile-{_options.Region}";

    /// <summary>Blizzard authorize URL for the login redirect.</summary>
    public string BuildAuthorizeUrl(string state)
    {
        var query = new Dictionary<string, string?>
        {
            ["client_id"] = _options.ClientId,
            ["redirect_uri"] = _options.RedirectUri,
            ["response_type"] = "code",
            ["scope"] = "openid wow.profile",
            ["state"] = state,
        };
        var qs = string.Join('&', query.Select(kv => $"{kv.Key}={Uri.EscapeDataString(kv.Value ?? "")}"));
        return $"{OAuthHost}/oauth/authorize?{qs}";
    }

    /// <summary>Exchanges an authorization code for a user access token.</summary>
    public async Task<string?> ExchangeCodeAsync(string code, CancellationToken ct = default)
    {
        using var request = new HttpRequestMessage(HttpMethod.Post, $"{OAuthHost}/oauth/token")
        {
            Content = new FormUrlEncodedContent(new Dictionary<string, string>
            {
                ["grant_type"] = "authorization_code",
                ["code"] = code,
                ["redirect_uri"] = _options.RedirectUri,
            }),
        };
        var basic = Convert.ToBase64String(
            System.Text.Encoding.UTF8.GetBytes($"{_options.ClientId}:{_options.ClientSecret}"));
        request.Headers.Authorization = new AuthenticationHeaderValue("Basic", basic);

        using var response = await _http.SendAsync(request, ct);
        if (!response.IsSuccessStatusCode)
        {
            _logger.LogWarning("Battle.net token exchange failed with {Status}", (int)response.StatusCode);
            return null;
        }

        using var doc = JsonDocument.Parse(await response.Content.ReadAsStringAsync(ct));
        return doc.RootElement.TryGetProperty("access_token", out var t) ? t.GetString() : null;
    }

    /// <summary>Reads the logged-in account's identity (stable sub + BattleTag).</summary>
    public async Task<BattleNetIdentity?> GetIdentityAsync(string accessToken, CancellationToken ct = default)
    {
        using var request = new HttpRequestMessage(HttpMethod.Get, $"{OAuthHost}/oauth/userinfo");
        request.Headers.Authorization = new AuthenticationHeaderValue("Bearer", accessToken);

        using var response = await _http.SendAsync(request, ct);
        if (!response.IsSuccessStatusCode)
        {
            _logger.LogWarning("Battle.net userinfo failed with {Status}", (int)response.StatusCode);
            return null;
        }

        using var doc = JsonDocument.Parse(await response.Content.ReadAsStringAsync(ct));
        var root = doc.RootElement;
        // "sub" is the stable account id; "battletag" is the display name.
        var sub = root.TryGetProperty("sub", out var s) ? s.GetString()
            : root.TryGetProperty("id", out var i) ? i.ToString()
            : null;
        var battletag = root.TryGetProperty("battletag", out var b) ? b.GetString() : null;

        if (string.IsNullOrEmpty(sub))
        {
            return null;
        }
        return new BattleNetIdentity(sub, battletag ?? sub);
    }

    /// <summary>
    /// Lists the account's characters via the WoW Account Profile Summary, then
    /// filters to the inferred max level (highest level on the account).
    /// Guild is not populated here — it's resolved per chosen character at import.
    /// </summary>
    public async Task<List<BattleNetCharacter>> GetMaxLevelCharactersAsync(string accessToken, CancellationToken ct = default)
    {
        using var request = new HttpRequestMessage(HttpMethod.Get,
            $"{ApiHost}/profile/user/wow?namespace={ProfileNamespace}&locale=en_GB");
        request.Headers.Authorization = new AuthenticationHeaderValue("Bearer", accessToken);

        using var response = await _http.SendAsync(request, ct);
        if (!response.IsSuccessStatusCode)
        {
            _logger.LogWarning("Battle.net account profile failed with {Status}", (int)response.StatusCode);
            return [];
        }

        using var doc = JsonDocument.Parse(await response.Content.ReadAsStringAsync(ct));
        var all = new List<BattleNetCharacter>();

        if (doc.RootElement.TryGetProperty("wow_accounts", out var accounts))
        {
            foreach (var account in accounts.EnumerateArray())
            {
                if (!account.TryGetProperty("characters", out var chars)) continue;
                foreach (var c in chars.EnumerateArray())
                {
                    var name = c.TryGetProperty("name", out var n) ? n.GetString() ?? "" : "";
                    var level = c.TryGetProperty("level", out var l) ? l.GetInt32() : 0;

                    var realmName = "";
                    var realmSlug = "";
                    if (c.TryGetProperty("realm", out var realm))
                    {
                        realmName = realm.TryGetProperty("name", out var rn) ? rn.GetString() ?? "" : "";
                        realmSlug = realm.TryGetProperty("slug", out var rs) ? rs.GetString() ?? "" : "";
                    }

                    var className = c.TryGetProperty("playable_class", out var pc)
                        && pc.TryGetProperty("name", out var cn) ? cn.GetString() ?? "" : "";

                    all.Add(new BattleNetCharacter
                    {
                        Name = name,
                        Realm = realmName,
                        RealmSlug = realmSlug,
                        Level = level,
                        ClassId = MapClassNameToId(className),
                    });
                }
            }
        }

        if (all.Count == 0)
        {
            return all;
        }

        // Infer the current level cap as the highest level on the account.
        var maxLevel = all.Max(c => c.Level);
        return all.Where(c => c.Level == maxLevel).ToList();
    }

    /// <summary>
    /// Maps a Blizzard class name to the site's internal class id scheme (see
    /// frontend/src/data/classes.ts), which differs from Blizzard's official ids.
    /// </summary>
    private static int MapClassNameToId(string className) => className switch
    {
        "Druid" => 0,
        "Paladin" => 1,
        "Warrior" => 2,
        "Demon Hunter" => 3,
        "Hunter" => 4,
        "Mage" => 5,
        "Rogue" => 6,
        "Death Knight" => 7,
        "Priest" => 8,
        "Warlock" => 9,
        "Shaman" => 10,
        "Monk" => 11,
        "Evoker" => 12,
        _ => -1,
    };
}
