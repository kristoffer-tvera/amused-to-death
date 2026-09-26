using Microsoft.Extensions.Options;
using AmusedToDeath.Api.Configuration;
using AmusedToDeath.Api.Data;
using AmusedToDeath.Api.Security;
using AmusedToDeath.Api.Services;

namespace AmusedToDeath.Api.Endpoints;

/// <summary>
/// Battle.net integration. All routes require a logged-in session. The Blizzard
/// access token is acquired transparently by BattleNetService as needed — there
/// is no manual token step.
///   POST /api/bnet/purge-non-guild         -> hide characters no longer in the guild (admin)
///   POST /api/bnet/refresh-all             -> refresh every character's ilvl; hide duds (admin)
///   POST /api/characters/{id}/refresh-ilvl -> refresh a character's item level
/// </summary>
public static class BattleNetEndpoints
{
    public static IEndpointRouteBuilder MapBattleNetEndpoints(this IEndpointRouteBuilder app)
    {
        var group = app.MapGroup("/api/bnet").WithTags("Battle.net");

        // Cross-reference the current guild roster and hide any owned characters
        // no longer in the guild. Never deletes (preserves raid history). Admin-only.
        group.MapPost("/purge-non-guild", async (CharacterRepository characters, BattleNetService gameData,
            IOptions<AppOptions> options, CancellationToken ct) =>
        {
            var bn = options.Value.BattleNet;
            var ranks = await gameData.GetGuildRanksAsync(bn.GuildRealmSlug, bn.GuildName, ct);
            if (ranks.Count == 0)
            {
                return Results.Json(new { error = "Could not read the guild roster." },
                    statusCode: StatusCodes.Status502BadGateway);
            }

            var hidden = await characters.HideAbsentFromRosterAsync(ranks.Keys.ToArray(), ct);
            return Results.Ok(new { success = true, hidden });
        }).RequireAdmin()
            .WithSummary("Hide characters no longer in the guild (admin)")
            .WithDescription("Fetches the current guild roster and hides owned characters absent from it. Characters are hidden, never deleted, to preserve raid history.");

        // Refresh every visible character's item level from Battle.net. A character
        // that Blizzard returns nothing for is a likely dud (deleted, renamed, or
        // transferred) and gets hidden — BUT only if at least one character
        // refreshed successfully. If ZERO succeed we assume a systemic outage
        // (bad credentials, Blizzard down) and hide nothing, so we never wipe the
        // roster over a transient failure. Hiding preserves raid history.
        group.MapPost("/refresh-all", async (CharacterRepository characters, BattleNetService bnet, CancellationToken ct) =>
        {
            var all = await characters.ListAsync(ownerId: null, ct);

            var succeeded = 0;
            var failedIds = new List<int>();

            foreach (var c in all)
            {
                var result = await bnet.GetItemLevelAsync(c.Realm, c.Name, ct);
                if (result.Success)
                {
                    await characters.SetIlvlAsync(c.Id, result.Ilvl, ct);
                    succeeded++;
                }
                else
                {
                    // Mark the failing character (ilvl = -1) and remember it as a
                    // hide candidate; whether we actually hide depends on the
                    // aggregate outcome below.
                    await characters.SetIlvlAsync(c.Id, -1, ct);
                    failedIds.Add(c.Id);
                }

                // Gentle pacing to avoid hammering Blizzard's rate limits.
                await Task.Delay(200, ct);
            }

            // Only hide duds when at least one character came back — otherwise this
            // is a systemic problem and we leave everything visible.
            var hidden = 0;
            if (succeeded > 0)
            {
                foreach (var id in failedIds)
                {
                    await characters.HideAsync(id, ct);
                    hidden++;
                }
            }

            return Results.Ok(new
            {
                success = true,
                refreshed = succeeded,
                failed = failedIds.Count,
                hidden,
                systemic_failure = succeeded == 0 && failedIds.Count > 0,
            });
        }).RequireAdmin()
            .WithSummary("Refresh all item levels and hide duds (admin)")
            .WithDescription("Refreshes every visible character from Battle.net. Characters Blizzard returns nothing for are hidden as likely duds — but only if at least one refresh succeeded, to avoid mass-hiding during an outage.");

        app.MapPost("/api/characters/{id:int}/refresh-ilvl",
            async (int id, CharacterRepository characters, BattleNetService bnet, CancellationToken ct) =>
        {
            var character = await characters.GetAsync(id, ct);
            if (character is null)
            {
                return Results.Json(new { error = "Character not found" }, statusCode: StatusCodes.Status404NotFound);
            }

            var result = await bnet.GetItemLevelAsync(character.Realm, character.Name, ct);
            if (!result.Success)
            {
                // Mark the character with ilvl = -1 on failure, matching legacy behaviour.
                await characters.SetIlvlAsync(id, -1, ct);
                return Results.Json(new { error = $"BNet API returned {result.UpstreamStatus}" },
                    statusCode: result.UpstreamStatus);
            }

            await characters.SetIlvlAsync(id, result.Ilvl, ct);
            return Results.Ok(new { ilvl = result.Ilvl });
        }).RequireAuth()
            .WithTags("Battle.net")
            .WithSummary("Refresh a character's item level from Battle.net");

        return app;
    }
}
