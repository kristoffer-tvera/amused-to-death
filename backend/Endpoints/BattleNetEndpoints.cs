using AmusedToDeath.Api.Data;
using AmusedToDeath.Api.Models;
using AmusedToDeath.Api.Security;
using AmusedToDeath.Api.Services;

namespace AmusedToDeath.Api.Endpoints;

/// <summary>
/// Battle.net integration. All routes require a logged-in session.
///   POST /api/bnet/token             -> acquire an app access token (admin action)
///   GET  /api/bnet/status            -> whether a token is held + seconds remaining
///   POST /api/characters/{id}/refresh-ilvl -> refresh a character's item level
/// </summary>
public static class BattleNetEndpoints
{
    public static IEndpointRouteBuilder MapBattleNetEndpoints(this IEndpointRouteBuilder app)
    {
        var group = app.MapGroup("/api/bnet");

        group.MapPost("/token", async (BattleNetService bnet, CancellationToken ct) =>
        {
            var ok = await bnet.AcquireTokenAsync(ct);
            return ok
                ? Results.Ok(new { success = true, remaining = bnet.RemainingSeconds })
                : Results.Json(new { error = "Failed to create Battle.net access token" },
                    statusCode: StatusCodes.Status502BadGateway);
        }).RequireAuth();

        group.MapGet("/status", (BattleNetService bnet) =>
            Results.Ok(new BNetStatus(bnet.HasToken, bnet.RemainingSeconds)))
            .RequireAuth();

        app.MapPost("/api/characters/{id:int}/refresh-ilvl",
            async (int id, CharacterRepository characters, BattleNetService bnet, CancellationToken ct) =>
        {
            var character = await characters.GetAsync(id, ct);
            if (character is null)
            {
                return Results.Json(new { error = "Character not found" }, statusCode: StatusCodes.Status404NotFound);
            }

            if (!bnet.HasToken)
            {
                return Results.Json(new { error = "Missing Battle.net token" }, statusCode: StatusCodes.Status401Unauthorized);
            }

            var result = await bnet.GetItemLevelAsync(character.Realm, character.Name, ct);
            if (result is null || !result.Value.Success)
            {
                // Mark the character with ilvl = -1 on failure, matching legacy behaviour.
                await characters.SetIlvlAsync(id, -1, ct);
                var status = result?.UpstreamStatus ?? StatusCodes.Status401Unauthorized;
                return Results.Json(new { error = $"BNet API returned {status}" }, statusCode: status);
            }

            await characters.SetIlvlAsync(id, result.Value.Ilvl, ct);
            return Results.Ok(new { ilvl = result.Value.Ilvl });
        }).RequireAuth();

        return app;
    }
}
