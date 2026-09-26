using System.Net.Http.Headers;
using System.Text.Json;
using Microsoft.Extensions.Options;
using AmusedToDeath.Api.Configuration;

namespace AmusedToDeath.Api.Services;

/// <summary>
/// Handles the Discord OAuth2 authorization-code flow used for login.
/// This is the identity provider today; it is deliberately isolated behind this
/// service so it can be swapped for a Battle.net login later without touching
/// the session/endpoint code. It only produces a username.
/// </summary>
public sealed class DiscordOAuthService
{
    private readonly HttpClient _http;
    private readonly DiscordOptions _options;

    public DiscordOAuthService(HttpClient http, IOptions<AppOptions> options)
    {
        _http = http;
        _options = options.Value.Discord;
    }

    /// <summary>Builds the Discord authorize URL the browser is redirected to.</summary>
    public string BuildAuthorizeUrl()
    {
        var query = new Dictionary<string, string?>
        {
            ["client_id"] = _options.ClientId,
            ["redirect_uri"] = _options.RedirectUri,
            ["response_type"] = "code",
            ["scope"] = "identify",
        };
        var qs = string.Join('&', query.Select(kv => $"{kv.Key}={Uri.EscapeDataString(kv.Value ?? "")}"));
        return $"https://discord.com/api/oauth2/authorize?{qs}";
    }

    /// <summary>
    /// Exchanges an authorization code for the user's Discord username.
    /// Returns null if the exchange or profile fetch fails.
    /// </summary>
    public async Task<string?> ExchangeCodeForUsernameAsync(string code, CancellationToken ct = default)
    {
        using var tokenRequest = new HttpRequestMessage(HttpMethod.Post, "https://discord.com/api/oauth2/token")
        {
            Content = new FormUrlEncodedContent(new Dictionary<string, string>
            {
                ["grant_type"] = "authorization_code",
                ["client_id"] = _options.ClientId,
                ["client_secret"] = _options.ClientSecret,
                ["redirect_uri"] = _options.RedirectUri,
                ["code"] = code,
            }),
        };
        tokenRequest.Headers.Accept.Add(new MediaTypeWithQualityHeaderValue("application/json"));

        using var tokenResponse = await _http.SendAsync(tokenRequest, ct);
        if (!tokenResponse.IsSuccessStatusCode)
        {
            return null;
        }

        using var tokenDoc = JsonDocument.Parse(await tokenResponse.Content.ReadAsStringAsync(ct));
        if (!tokenDoc.RootElement.TryGetProperty("access_token", out var accessTokenEl))
        {
            return null;
        }
        var accessToken = accessTokenEl.GetString();

        using var meRequest = new HttpRequestMessage(HttpMethod.Get, "https://discord.com/api/users/@me");
        meRequest.Headers.Authorization = new AuthenticationHeaderValue("Bearer", accessToken);

        using var meResponse = await _http.SendAsync(meRequest, ct);
        if (!meResponse.IsSuccessStatusCode)
        {
            return null;
        }

        using var meDoc = JsonDocument.Parse(await meResponse.Content.ReadAsStringAsync(ct));
        return meDoc.RootElement.TryGetProperty("username", out var u) ? u.GetString() : null;
    }
}
