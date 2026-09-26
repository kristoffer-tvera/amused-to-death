using System.Security.Cryptography;
using Dapper;
using AmusedToDeath.Api.Data;

namespace AmusedToDeath.Api.Security;

/// <summary>
/// Owns the session lifecycle. A session is a random token stored in the
/// "sessions" table (token, username, is_admin, expires_at) and mirrored in an
/// HttpOnly cookie.
///
/// Admin status is decided once at login (from the user's guild rank) and stored
/// on the session, so it can be read cheaply on every request without hitting
/// the guild roster each time. Because the session is a DB-backed bearer token,
/// it survives process restarts and needs no server-side session store.
/// </summary>
public sealed class SessionService
{
    public const string CookieName = "a2d_session";

    private readonly IDbConnectionFactory _connections;

    public SessionService(IDbConnectionFactory connections)
    {
        _connections = connections;
    }

    /// <summary>
    /// Resolves a session token to its principal (BattleTag, owner id, admin flag)
    /// if it exists and has not expired. Returns null for missing/expired tokens.
    /// </summary>
    public async Task<(string Username, string? OwnerId, bool IsAdmin)?> ResolveUserAsync(string token, CancellationToken ct = default)
    {
        if (string.IsNullOrEmpty(token))
        {
            return null;
        }

        await using var db = await _connections.OpenConnectionAsync(ct);
        var row = await db.QuerySingleOrDefaultAsync<(string username, string? ownerId, bool isAdmin, DateTime expiresAt)?>(
            new CommandDefinition(
                "SELECT username, owner_id, is_admin, expires_at FROM sessions WHERE token = @token",
                new { token },
                cancellationToken: ct));

        if (row is null || row.Value.expiresAt < DateTime.UtcNow)
        {
            return null;
        }

        return (row.Value.username, row.Value.ownerId, row.Value.isAdmin);
    }

    /// <summary>
    /// Creates a session for the given account: BattleTag as the display name,
    /// the Blizzard sub as the owner id, plus the admin flag. Returns the token.
    /// Tokens are 13 random bytes -> 26 hex chars (sessions.token is VARCHAR(26)).
    /// </summary>
    public async Task<string> CreateSessionAsync(string username, string ownerId, bool isAdmin, TimeSpan lifetime, CancellationToken ct = default)
    {
        var token = Convert.ToHexString(RandomNumberGenerator.GetBytes(13)).ToLowerInvariant();
        var expiresAt = DateTime.UtcNow.Add(lifetime);

        await using var db = await _connections.OpenConnectionAsync(ct);
        await db.ExecuteAsync(new CommandDefinition(
            "INSERT INTO sessions (token, username, owner_id, is_admin, expires_at) VALUES (@token, @username, @ownerId, @isAdmin, @expiresAt)",
            new { token, username, ownerId, isAdmin, expiresAt },
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
