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

        // 1. Begin login (browser navigation).
        group.MapGet("/login", (BattleNetAuthService bnet) =>
        {
            var state = Convert.ToHexString(RandomNumberGenerator.GetBytes(8)).ToLowerInvariant();
            return Results.Redirect(bnet.BuildAuthorizeUrl(state));
        })
            .WithSummary("Start Battle.net login");

        // 2. OAuth callback: exchange, fetch characters, park in pending cache.
        group.MapGet("/callback", async (string? code, HttpContext ctx,
            BattleNetAuthService bnet, PendingLoginStore pending, IOptions<AppOptions> options, CancellationToken ct) =>
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

            var characters = await bnet.GetMaxLevelCharactersAsync(accessToken, ct);

            // Park the login-in-progress under a random token carried by a cookie.
            var pendingToken = Convert.ToHexString(RandomNumberGenerator.GetBytes(16)).ToLowerInvariant();
            pending.Set(pendingToken, new PendingLogin(identity.Sub, identity.BattleTag, accessToken, characters));
            WritePendingCookie(ctx.Response, pendingToken);

            // Send the browser to the SPA character picker.
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

            // Guild gate + rank: pull the guild roster once (cached) and check the
            // chosen characters against it. Admin status = best (lowest) rank among
            // the chosen guild characters is within the configured admin cutoff.
            // Assumes the picks share a realm (they come from one account); use the
            // first pick's realm for the roster lookup.
            var realmSlug = resolved[0].RealmSlug;
            var ranks = await gameData.GetGuildRanksAsync(realmSlug, bnet.GuildName, ct);

            var bestRank = int.MaxValue;
            foreach (var c in resolved)
            {
                if (ranks.TryGetValue(c.Name, out var rank) && rank < bestRank)
                {
                    bestRank = rank;
                }
            }

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

            // Establish the real session (identity = BattleTag for now, admin from
            // guild rank) and clean up the pending-login state.
            var token = await sessions.CreateSessionAsync(login.BattleTag, isAdmin, SessionLifetime, ct);
            sessions.WriteCookie(ctx.Response, token, SessionLifetime);
            ClearPendingCookie(ctx.Response);
            pending.Remove(ReadPendingToken(ctx)!);

            return Results.Ok(new { success = true, imported = ids.Length, main_id = mainId, admin = isAdmin });
        })
            .WithSummary("Import chosen characters and finish login")
            .WithDescription("Requires at least one selected max-level character to be in the configured guild.");

        return app;
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
