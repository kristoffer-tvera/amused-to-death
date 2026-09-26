using System.Security.Cryptography;
using Dapper;
using Microsoft.Extensions.Options;
using AmusedToDeath.Api.Configuration;
using AmusedToDeath.Api.Data;

namespace AmusedToDeath.Api.Security;

/// <summary>
/// Owns the session lifecycle. A session is a random token stored in the
/// "sessions" table (token, username, expires_at) and mirrored in an HttpOnly
/// cookie.
///
/// Because the session is a DB-backed bearer token, it survives process restarts
/// and works across a stateless API — no server-side session store required. The
/// identity provider (Discord today, Battle.net later) only supplies the
/// username; everything after that is IdP-agnostic.
/// </summary>
public sealed class SessionService
{
    public const string CookieName = "a2d_session";

    private readonly IDbConnectionFactory _connections;
    private readonly AppOptions _options;

    public SessionService(IDbConnectionFactory connections, IOptions<AppOptions> options)
    {
        _connections = connections;
        _options = options.Value;
    }

    public bool IsAdmin(string username) =>
        _options.Admins.Contains(username, StringComparer.OrdinalIgnoreCase);

    /// <summary>
    /// Resolves the username for a session token if it exists and has not expired.
    /// Returns null for missing/expired tokens.
    /// </summary>
    public async Task<string?> ResolveUserAsync(string token, CancellationToken ct = default)
    {
        if (string.IsNullOrEmpty(token))
        {
            return null;
        }

        await using var db = await _connections.OpenConnectionAsync(ct);
        var row = await db.QuerySingleOrDefaultAsync<(string username, DateTime expiresAt)?>(
            new CommandDefinition(
                "SELECT username, expires_at FROM sessions WHERE token = @token",
                new { token },
                cancellationToken: ct));

        if (row is null || row.Value.expiresAt < DateTime.UtcNow)
        {
            return null;
        }

        return row.Value.username;
    }

    /// <summary>
    /// Creates a new session token for the given username, persists it, and
    /// returns the token. Tokens are 13 random bytes -> 26 hex chars
    /// (sessions.token is VARCHAR(26)).
    /// </summary>
    public async Task<string> CreateSessionAsync(string username, TimeSpan lifetime, CancellationToken ct = default)
    {
        var token = Convert.ToHexString(RandomNumberGenerator.GetBytes(13)).ToLowerInvariant();
        var expiresAt = DateTime.UtcNow.Add(lifetime);

        await using var db = await _connections.OpenConnectionAsync(ct);
        await db.ExecuteAsync(new CommandDefinition(
            "INSERT INTO sessions (token, username, expires_at) VALUES (@token, @username, @expiresAt)",
            new { token, username, expiresAt },
            cancellationToken: ct));

        return token;
    }

    /// <summary>Deletes a session token (logout).</summary>
    public async Task DestroySessionAsync(string token, CancellationToken ct = default)
    {
        if (string.IsNullOrEmpty(token))
        {
            return;
        }

        await using var db = await _connections.OpenConnectionAsync(ct);
        await db.ExecuteAsync(new CommandDefinition(
            "DELETE FROM sessions WHERE token = @token",
            new { token },
            cancellationToken: ct));
    }

    public void WriteCookie(HttpResponse response, string token, TimeSpan lifetime)
    {
        response.Cookies.Append(CookieName, token, new CookieOptions
        {
            HttpOnly = true,
            Secure = true,
            SameSite = SameSiteMode.Lax,
            Path = "/",
            MaxAge = lifetime,
        });
    }

    public void ClearCookie(HttpResponse response)
    {
        response.Cookies.Delete(CookieName, new CookieOptions
        {
            HttpOnly = true,
            Secure = true,
            SameSite = SameSiteMode.Lax,
            Path = "/",
        });
    }
}
