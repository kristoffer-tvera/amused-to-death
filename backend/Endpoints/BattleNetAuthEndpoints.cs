using System.Security.Cryptography;
using Microsoft.Extensions.Options;
using AmusedToDeath.Api.Configuration;
using AmusedToDeath.Api.Data;
using AmusedToDeath.Api.Models;
using AmusedToDeath.Api.Security;
using AmusedToDeath.Api.Services;

namespace AmusedToDeath.Api.Endpoints;

/// <summary>
/// Battle.net login + character import.
///
///   GET  /api/auth/bnet/login       -> 302 to Blizzard authorize (browser nav)
///   GET  /api/auth/bnet/callback    -> exchange code, fetch characters, park them
///                                       in a pending-login cache, redirect to the
///                                       SPA character picker
///   GET  /api/auth/bnet/characters  -> the picker payload (max-level chars + guild flags)
///   POST /api/auth/bnet/import      -> validate guild membership, import/migrate the
///                                       chosen characters, establish the real session
///
/// Ownership is keyed on the Blizzard account "sub" (stable), stored in
/// characters.owner_id. The BattleTag is only a display label.
/// </summary>
public static class BattleNetAuthEndpoints
{
    private static readonly TimeSpan SessionLifetime = TimeSpan.FromDays(30);
    private static readonly TimeSpan PendingCookieLifetime = TimeSpan.FromMinutes(10);

    public static IEndpointRouteBuilder MapBattleNetAuthEndpoints(this IEndpointRouteBuilder app)
    {
        var group = app.MapGroup("/api/auth/bnet").WithTags("Battle.net Login");

        // 1. Begin login (browser navigation). ?reimport=true forces the character
        //    picker even for a returning account (used by the Home "Re-import" button).
        group.MapGet("/login", (bool? reimport, BattleNetAuthService bnet) =>
        {
            var state = reimport == true
                ? "reimport"
                : Convert.ToHexString(RandomNumberGenerator.GetBytes(8)).ToLowerInvariant();
            return Results.Redirect(bnet.BuildAuthorizeUrl(state));
        })
            .WithSummary("Start Battle.net login");

        // 2. OAuth callback. Returning users (who already own characters) are
        //    logged straight in after a strict guild re-verify; only accounts with
        //    no characters — or an explicit ?reimport=true — see the picker.
        group.MapGet("/callback", async (string? code, string? state, HttpContext ctx,
            BattleNetAuthService bnet, BattleNetService gameData, PendingLoginStore pending,
            SessionService sessions, CharacterRepository characters, IOptions<AppOptions> options,
            CancellationToken ct) =>
        {
            if (string.IsNullOrEmpty(code))
            {
                return Results.Json(new { error = "Missing OAuth code" }, statusCode: StatusCodes.Status400BadRequest);
            }

            var accessToken = await bnet.ExchangeCodeAsync(code, ct);
            if (accessToken is null)
            {
                return Results.Json(new { error = "Battle.net login failed" }, statusCode: StatusCodes.Status401Unauthorized);
            }

            var identity = await bnet.GetIdentityAsync(accessToken, ct);
            if (identity is null)
            {
                return Results.Json(new { error = "Could not read Battle.net account" }, statusCode: StatusCodes.Status401Unauthorized);
            }

            var maxLevelChars = await bnet.GetMaxLevelCharactersAsync(accessToken, ct);

            // "reimport" is carried through the OAuth state param so the Home button
            // can force the picker even for an account that already has characters.
            var forceReimport = state == "reimport";

            // Returning user fast-path: already owns characters and isn't forcing a
            // re-import. Re-verify guild membership and log straight in.
            if (!forceReimport && await characters.HasOwnedAsync(identity.Sub, ct))
            {
                var ownedNames = await characters.OwnedNamesAsync(identity.Sub, ct);
                var ownedSet = new HashSet<string>(ownedNames, StringComparer.OrdinalIgnoreCase);

                // Strict re-verify: at least one OWNED character must still be in the
                // guild. Rosters are per-realm, so we check the account's max-level
                // characters against their own realms' rosters (one fetch per realm,
                // cached) and keep only those the user actually owns. A single-realm
                // lookup here wrongly rejected members whose guild character sits on
                // a different realm than the first max-level character.
                var ownedInGuild = maxLevelChars.Where(c => ownedSet.Contains(c.Name)).ToList();
                var bestRank = await BestGuildRankAsync(gameData, bnet.GuildName, ownedInGuild, ct);

                if (bestRank == int.MaxValue)
                {
                    return Results.Redirect(options.Value.FrontendBaseUrl + "/?error=not-in-guild");
                }

                var admin = bestRank <= options.Value.BattleNet.AdminMaxRank;
                var sessionToken = await sessions.CreateSessionAsync(identity.BattleTag, identity.Sub, admin, SessionLifetime, ct);
                sessions.WriteCookie(ctx.Response, sessionToken, SessionLifetime);
                return Results.Redirect(options.Value.FrontendBaseUrl + "/");
            }

            // First-time (or forced re-import): park the login and show the picker.
            var pendingToken = Convert.ToHexString(RandomNumberGenerator.GetBytes(16)).ToLowerInvariant();
            pending.Set(pendingToken, new PendingLogin(identity.Sub, identity.BattleTag, accessToken, maxLevelChars));
            WritePendingCookie(ctx.Response, pendingToken);
            return Results.Redirect(options.Value.FrontendBaseUrl + "/bnet/pick");
        })
            .WithSummary("Battle.net OAuth callback");

        // 3. The picker payload: max-level characters + guild membership flags.
        group.MapGet("/characters", async (HttpContext ctx, BattleNetAuthService bnet, BattleNetService gameData,
            PendingLoginStore pending, CancellationToken ct) =>
        {
            var login = ReadPending(ctx, pending);
            if (login is null)
            {
                return Results.Json(new { error = "No login in progress" }, statusCode: StatusCodes.Status401Unauthorized);
            }

            // Resolve guild membership from the cached guild roster (one call for
            // all characters, rather than one profile fetch per character).
            foreach (var c in login.Characters)
            {
                var ranks = await gameData.GetGuildRanksAsync(c.RealmSlug, bnet.GuildName, ct);
                c.InGuild = ranks.ContainsKey(c.Name);
                c.Guild = c.InGuild ? bnet.GuildName : null;
            }

            var payload = new BattleNetCharacterList
            {
                BattleTag = login.BattleTag,
                MaxLevel = login.Characters.Count > 0 ? login.Characters.Max(c => c.Level) : 0,
                Characters = login.Characters,
                HasGuildCharacter = login.Characters.Any(c => c.InGuild),
            };
            return Results.Ok(payload);
        })
            .WithSummary("List the logged-in account's max-level characters")
            .WithDescription("Requires a Battle.net login in progress (pending-login cookie set by the callback).");

        // 4. Import chosen characters and establish the session.
        group.MapPost("/import", async (BattleNetImportRequest body, HttpContext ctx,
            BattleNetAuthService bnet, BattleNetService gameData, PendingLoginStore pending, SessionService sessions,
            CharacterRepository characters, IOptions<AppOptions> options, CancellationToken ct) =>
        {
            var login = ReadPending(ctx, pending);
            if (login is null)
            {
                return Results.Json(new { error = "No login in progress" }, statusCode: StatusCodes.Status401Unauthorized);
            }

            if (body.Picks.Count == 0)
            {
                return Results.Json(new { error = "No characters selected" }, statusCode: StatusCodes.Status400BadRequest);
            }
            if (body.MainIndex < 0 || body.MainIndex >= body.Picks.Count)
            {
                return Results.Json(new { error = "Invalid main selection" }, statusCode: StatusCodes.Status400BadRequest);
            }

            // Resolve the picks against the characters we discovered for this account,
            // so a caller can't import arbitrary characters they don't own.
            var resolved = new List<BattleNetCharacter>();
            foreach (var pick in body.Picks)
            {
                var match = login.Characters.FirstOrDefault(c =>
                    string.Equals(c.Name, pick.Name, StringComparison.OrdinalIgnoreCase) &&
                    string.Equals(c.Realm, pick.Realm, StringComparison.OrdinalIgnoreCase));
                if (match is null)
                {
                    return Results.Json(new { error = $"Character not on your account: {pick.Name}-{pick.Realm}" },
                        statusCode: StatusCodes.Status400BadRequest);
                }
                resolved.Add(match);
            }

            // Guild gate + rank: find the best (lowest) guild rank across the
            // chosen characters. Rosters are per-realm, so we look each character up
            // against its OWN realm's roster — picks can span multiple realms, and a
            // single-realm lookup would miss a guild character on another realm.
            // Admin status = best rank among the chosen guild characters is within
            // the configured admin cutoff. Requiring only one guild character to
            // match matches the "at least one" membership rule.
            var bestRank = await BestGuildRankAsync(gameData, bnet.GuildName, resolved, ct);

            if (bestRank == int.MaxValue)
            {
                return Results.Json(
                    new { error = $"None of your selected characters are in the guild \"{bnet.GuildName}\"." },
                    statusCode: StatusCodes.Status403Forbidden);
            }

            var isAdmin = bestRank <= options.Value.BattleNet.AdminMaxRank;

            // Import/migrate each pick. First pass creates/claims rows and records ids
            // so we can wire up the main relationship in a second pass.
            var ids = new int[resolved.Count];
            for (var i = 0; i < resolved.Count; i++)
            {
                var c = resolved[i];
                var existing = await characters.FindByNameRealmAsync(c.Name, c.Realm, ct);
                if (existing is not null)
                {
                    // Migrate: keep the original record, attach ownership.
                    await characters.ClaimAsync(existing.Id, login.Sub, existing.Main, ct);
                    ids[i] = existing.Id;
                }
                else
                {
                    ids[i] = await characters.InsertOwnedAsync(c.Name, c.Realm, c.ClassId, login.Sub, main: null, ct);
                }
            }

            // Set the main relationship: every pick points its main at the chosen main
            // (the main points at itself, which the frontend treats as "is a main").
            var mainId = ids[body.MainIndex];
            for (var i = 0; i < ids.Length; i++)
            {
                await characters.SetMainAsync(ids[i], mainId, ct);
            }

            // Establish the real session: BattleTag as display name, Blizzard sub
            // as the owner id (matches characters.owner_id), admin from guild rank.
            var token = await sessions.CreateSessionAsync(login.BattleTag, login.Sub, isAdmin, SessionLifetime, ct);
            sessions.WriteCookie(ctx.Response, token, SessionLifetime);
            ClearPendingCookie(ctx.Response);
            pending.Remove(ReadPendingToken(ctx)!);

            return Results.Ok(new { success = true, imported = ids.Length, main_id = mainId, admin = isAdmin });
        })
            .WithSummary("Import chosen characters and finish login")
            .WithDescription("Requires at least one selected max-level character to be in the configured guild.");

        return app;
    }

    /// <summary>
    /// Best (lowest) guild rank across the given characters, or int.MaxValue if
    /// none are in the guild. Guild rosters are per-realm, so characters are
    /// grouped by realm slug and each realm's roster is fetched once (cached in
    /// the service). This keeps the "at least one character in the guild" rule
    /// working even when a user's characters span multiple realms.
    /// </summary>
    private static async Task<int> BestGuildRankAsync(
        BattleNetService gameData, string guildName,
        IEnumerable<BattleNetCharacter> chars, CancellationToken ct)
    {
        var bestRank = int.MaxValue;
        foreach (var byRealm in chars.GroupBy(c => c.RealmSlug, StringComparer.OrdinalIgnoreCase))
        {
            var ranks = await gameData.GetGuildRanksAsync(byRealm.Key, guildName, ct);
            foreach (var c in byRealm)
            {
                if (ranks.TryGetValue(c.Name, out var rank) && rank < bestRank)
                {
                    bestRank = rank;
                }
            }
        }
        return bestRank;
    }

    private static PendingLogin? ReadPending(HttpContext ctx, PendingLoginStore pending)
    {
        var token = ReadPendingToken(ctx);
        return token is null ? null : pending.Get(token);
    }

    private static string? ReadPendingToken(HttpContext ctx) =>
        ctx.Request.Cookies.TryGetValue(PendingLoginStore.CookieName, out var t) && !string.IsNullOrEmpty(t) ? t : null;

    private static void WritePendingCookie(HttpResponse response, string token) =>
        response.Cookies.Append(PendingLoginStore.CookieName, token, new CookieOptions
        {
            HttpOnly = true,
            Secure = true,
            SameSite = SameSiteMode.Lax,
            Path = "/",
            MaxAge = PendingCookieLifetime,
        });

    private static void ClearPendingCookie(HttpResponse response) =>
        response.Cookies.Delete(PendingLoginStore.CookieName, new CookieOptions
        {
            HttpOnly = true,
            Secure = true,
            SameSite = SameSiteMode.Lax,
            Path = "/",
        });
}
