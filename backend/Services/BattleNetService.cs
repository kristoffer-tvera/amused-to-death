using System.Net.Http.Headers;
using System.Text.Json;
using Microsoft.Extensions.Options;
using AmusedToDeath.Api.Configuration;

namespace AmusedToDeath.Api.Services;

/// <summary>
/// Talks to the Battle.net / Blizzard APIs.
///
/// The legacy code fetched a client-credentials access token and stashed it in
/// the PHP session, then used it to read a character's average item level. The
/// token is application-scoped (client_credentials), not user-scoped, so here it
/// is cached once at the app level and reused across requests. bnet_status
/// reports whether a token is currently held and how many seconds remain.
/// </summary>
public sealed class BattleNetService
{
    private readonly IHttpClientFactory _httpFactory;
    private readonly BattleNetOptions _options;
    private readonly ILogger<BattleNetService> _logger;
    private readonly SemaphoreSlim _lock = new(1, 1);

    private string? _token;
    private DateTimeOffset _tokenExpiry;

    public BattleNetService(IHttpClientFactory httpFactory, IOptions<AppOptions> options, ILogger<BattleNetService> logger)
    {
        _httpFactory = httpFactory;
        _options = options.Value.BattleNet;
        _logger = logger;
    }

    private HttpClient CreateClient() => _httpFactory.CreateClient("battlenet");

    public bool HasToken => _token is not null && _tokenExpiry > DateTimeOffset.UtcNow;

    public int RemainingSeconds =>
        HasToken ? (int)Math.Max(0, (_tokenExpiry - DateTimeOffset.UtcNow).TotalSeconds) : 0;

    private string OAuthHost => $"https://{_options.Region}.battle.net";
    private string ApiHost => $"https://{_options.Region}.api.blizzard.com";

    /// <summary>
    /// Acquires (or refreshes) an application access token via the
    /// client_credentials grant. Returns true on success.
    /// </summary>
    public async Task<bool> AcquireTokenAsync(CancellationToken ct = default)
    {
        await _lock.WaitAsync(ct);
        try
        {
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
                return false;
            }

            using var doc = JsonDocument.Parse(await response.Content.ReadAsStringAsync(ct));
            _token = doc.RootElement.GetProperty("access_token").GetString();
            var expiresIn = doc.RootElement.TryGetProperty("expires_in", out var e) ? e.GetInt32() : 0;
            _tokenExpiry = DateTimeOffset.UtcNow.AddSeconds(expiresIn);
            return _token is not null;
        }
        finally
        {
            _lock.Release();
        }
    }

    /// <summary>
    /// Fetches the average item level for a character. Returns null if no token
    /// is held; otherwise returns a result carrying either the ilvl or the
    /// upstream HTTP status on failure.
    /// </summary>
    public async Task<BattleNetIlvlResult?> GetItemLevelAsync(string realm, string name, CancellationToken ct = default)
    {
        if (!HasToken)
        {
            return null;
        }

        var encodedName = Uri.EscapeDataString(name.ToLowerInvariant());
        var realmSlug = realm.ToLowerInvariant();
        var url = $"{ApiHost}/profile/wow/character/{realmSlug}/{encodedName}?namespace=profile-{_options.Region}&locale=en_GB";

        using var request = new HttpRequestMessage(HttpMethod.Get, url);
        request.Headers.Authorization = new AuthenticationHeaderValue("Bearer", _token);

        using var response = await CreateClient().SendAsync(request, ct);
        if (!response.IsSuccessStatusCode)
        {
            return BattleNetIlvlResult.Failed((int)response.StatusCode);
        }

        using var doc = JsonDocument.Parse(await response.Content.ReadAsStringAsync(ct));
        var ilvl = doc.RootElement.TryGetProperty("average_item_level", out var v) ? v.GetInt32() : 0;
        return BattleNetIlvlResult.Ok(ilvl);
    }
}

public readonly record struct BattleNetIlvlResult(bool Success, int Ilvl, int UpstreamStatus)
{
    public static BattleNetIlvlResult Ok(int ilvl) => new(true, ilvl, 200);
    public static BattleNetIlvlResult Failed(int status) => new(false, 0, status);
}
