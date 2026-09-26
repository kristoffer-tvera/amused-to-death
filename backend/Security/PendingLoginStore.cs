using Microsoft.Extensions.Caching.Memory;
using AmusedToDeath.Api.Models;

namespace AmusedToDeath.Api.Security;

/// <summary>
/// Holds the transient state of a Battle.net login between the OAuth callback and
/// the character-import step, keyed by a random token carried in a short-lived
/// cookie. This is what lets us fetch the character list once (at callback) and
/// reuse it for both the picker view and the import, without refetching from
/// Blizzard on every request.
///
/// Backed by IMemoryCache with a ~10 minute sliding window — "cached in a way to
/// avoid refetching within a reasonable amount of time".
/// </summary>
public sealed class PendingLoginStore
{
    public const string CookieName = "a2d_pending_login";
    private static readonly TimeSpan Ttl = TimeSpan.FromMinutes(10);

    private readonly IMemoryCache _cache;

    public PendingLoginStore(IMemoryCache cache)
    {
        _cache = cache;
    }

    private static string Key(string token) => $"pending-login:{token}";

    public void Set(string token, PendingLogin login) =>
        _cache.Set(Key(token), login, new MemoryCacheEntryOptions { SlidingExpiration = Ttl });

    public PendingLogin? Get(string token) =>
        _cache.TryGetValue(Key(token), out PendingLogin? login) ? login : null;

    public void Remove(string token) => _cache.Remove(Key(token));
}

/// <summary>
/// The cached login-in-progress: who the user is (Blizzard sub + BattleTag),
/// their live access token (for the guild lookup at import), and the max-level
/// characters discovered at callback time.
/// </summary>
public sealed record PendingLogin(
    string Sub,
    string BattleTag,
    string AccessToken,
    IReadOnlyList<BattleNetCharacter> Characters);
