using System.Net.Http.Headers;
using System.Text.Json;
using Microsoft.AspNetCore.Http;
using Microsoft.Extensions.Caching.Memory;
using Microsoft.Extensions.Options;
using AmusedToDeath.Api.Configuration;

namespace AmusedToDeath.Api.Services;

/// <summary>
/// Talks to the Battle.net / Blizzard APIs.
///
/// The client-credentials access token is an implementation detail: it is
/// acquired lazily and cached, and refreshed automatically when missing or
/// expired. Callers just call the data methods (item level, guild roster) and
/// the token is handled transparently — there is no manual "get a token" step.
/// </summary>
public sealed class BattleNetService
{
    private readonly IHttpClientFactory _httpFactory;
    private readonly BattleNetOptions _options;
    private readonly IMemoryCache _cache;
    private readonly ILogger<BattleNetService> _logger;
    private readonly SemaphoreSlim _lock = new(1, 1);

    private static readonly TimeSpan RosterCacheTtl = TimeSpan.FromMinutes(30);

    private string? _token;
    private DateTimeOffset _tokenExpiry;

    public BattleNetService(IHttpClientFactory httpFactory, IOptions<AppOptions> options,
        IMemoryCache cache, ILogger<BattleNetService> logger)
    {
        _httpFactory = httpFactory;
        _options = options.Value.BattleNet;
        _cache = cache;
        _logger = logger;
    }

    private HttpClient CreateClient() => _httpFactory.CreateClient("battlenet");

    private bool HasValidToken => _token is not null && _tokenExpiry > DateTimeOffset.UtcNow;

    private string OAuthHost => $"https://{_options.Region}.battle.net";
    private string ApiHost => $"https://{_options.Region}.api.blizzard.com";

    /// <summary>
    /// Ensures a valid client-credentials token is held, acquiring one if the
    /// current token is missing or expired. Returns the token, or null if
    /// acquisition failed. Serialised so concurrent callers don't stampede.
    /// </summary>
    private async Task<string?> EnsureTokenAsync(CancellationToken ct)
    {
        if (HasValidToken)
        {
            return _token;
        }

        await _lock.WaitAsync(ct);
        try
        {
            // Re-check inside the lock — another caller may have just refreshed it.
            if (HasValidToken)
            {
                return _token;
            }

            using var request = new HttpRequestMessage(HttpMethod.Post, $"{OAuthHost}/oauth/token");
            request.Content = new FormUrlEncodedContent(new Dictionary<string, string>
            {
                ["grant_type"] = "client_credentials",
            });
            var basic = Convert.ToBase64String(
                System.Text.Encoding.UTF8.GetBytes($"{_options.ClientId}:{_options.ClientSecret}"));
            request.Headers.Authorization = new AuthenticationHeaderValue("Basic", basic);

            using var response = await CreateClient().SendAsync(request, ct);
            if (!response.IsSuccessStatusCode)
            {
                _logger.LogWarning("Battle.net token request failed with {Status}", (int)response.StatusCode);
                return null;
            }

            using var doc = JsonDocument.Parse(await response.Content.ReadAsStringAsync(ct));
            _token = doc.RootElement.GetProperty("access_token").GetString();
            var expiresIn = doc.RootElement.TryGetProperty("expires_in", out var e) ? e.GetInt32() : 0;
            _tokenExpiry = DateTimeOffset.UtcNow.AddSeconds(expiresIn);
            return _token;
        }
        finally
        {
            _lock.Release();
        }
    }

    /// <summary>
    /// Fetches the average item level for a character. Acquires a token as needed.
    /// Returns a result carrying the ilvl, or a failure with the upstream HTTP
    /// status (or 401 if a token could not be acquired).
    /// </summary>
    public async Task<BattleNetIlvlResult> GetItemLevelAsync(string realm, string name, CancellationToken ct = default)
    {
        var token = await EnsureTokenAsync(ct);
        if (token is null)
        {
            return BattleNetIlvlResult.Failed(StatusCodes.Status401Unauthorized);
        }

        var encodedName = Uri.EscapeDataString(name.ToLowerInvariant());
        var realmSlug = realm.ToLowerInvariant();
        var url = $"{ApiHost}/profile/wow/character/{realmSlug}/{encodedName}?namespace=profile-{_options.Region}&locale=en_GB";

        using var request = new HttpRequestMessage(HttpMethod.Get, url);
        request.Headers.Authorization = new AuthenticationHeaderValue("Bearer", token);

        using var response = await CreateClient().SendAsync(request, ct);
        if (!response.IsSuccessStatusCode)
        {
            return BattleNetIlvlResult.Failed((int)response.StatusCode);
        }

        using var doc = JsonDocument.Parse(await response.Content.ReadAsStringAsync(ct));
        var ilvl = doc.RootElement.TryGetProperty("average_item_level", out var v) ? v.GetInt32() : 0;
        return BattleNetIlvlResult.Ok(ilvl);
    }

    /// <summary>
    /// Returns the configured guild's roster as a case-insensitive
    /// character-name -> rank map (rank 0 = Guild Master, ascending). Cached for
    /// 30 minutes since guild membership/ranks change slowly. Returns an empty
    /// map on failure. Uses the app-level client-credentials token (the roster
    /// lives in the Game Data API, not the user profile API).
    /// </summary>
    public async Task<IReadOnlyDictionary<string, int>> GetGuildRanksAsync(string realmSlug, string guildName, CancellationToken ct = default)
    {
        var guildSlug = Slugify(guildName);
        var cacheKey = $"guild-ranks:{realmSlug}:{guildSlug}";
        if (_cache.TryGetValue(cacheKey, out IReadOnlyDictionary<string, int>? cached) && cached is not null)
        {
            return cached;
        }

        // Ensure we have an app token (roster is Game Data, client-credentials).
        var token = await EnsureTokenAsync(ct);
        if (token is null)
        {
            return new Dictionary<string, int>();
        }

        var url = $"{ApiHost}/data/wow/guild/{realmSlug.ToLowerInvariant()}/{guildSlug}/roster" +
                  $"?namespace=profile-{_options.Region}&locale=en_GB";

        using var request = new HttpRequestMessage(HttpMethod.Get, url);
        request.Headers.Authorization = new AuthenticationHeaderValue("Bearer", token);

        using var response = await CreateClient().SendAsync(request, ct);
        if (!response.IsSuccessStatusCode)
        {
            _logger.LogWarning("Guild roster fetch failed with {Status}", (int)response.StatusCode);
            return new Dictionary<string, int>();
        }

        var ranks = new Dictionary<string, int>(StringComparer.OrdinalIgnoreCase);
        using var doc = JsonDocument.Parse(await response.Content.ReadAsStringAsync(ct));
        if (doc.RootElement.TryGetProperty("members", out var members))
        {
            foreach (var m in members.EnumerateArray())
            {
                if (!m.TryGetProperty("character", out var ch)) continue;
                var name = ch.TryGetProperty("name", out var n) ? n.GetString() : null;
                var rank = m.TryGetProperty("rank", out var r) ? r.GetInt32() : int.MaxValue;
                if (!string.IsNullOrEmpty(name))
                {
                    ranks[name] = rank;
                }
            }
        }

        _cache.Set(cacheKey, (IReadOnlyDictionary<string, int>)ranks,
            new MemoryCacheEntryOptions { AbsoluteExpirationRelativeToNow = RosterCacheTtl });
        return ranks;
    }

    /// <summary>Guild name -> API slug: lowercase, spaces to hyphens.</summary>
    private static string Slugify(string name) =>
        name.Trim().ToLowerInvariant().Replace(' ', '-');
}

public readonly record struct BattleNetIlvlResult(bool Success, int Ilvl, int UpstreamStatus)
{
    public static BattleNetIlvlResult Ok(int ilvl) => new(true, ilvl, 200);
    public static BattleNetIlvlResult Failed(int status) => new(false, 0, status);
}
