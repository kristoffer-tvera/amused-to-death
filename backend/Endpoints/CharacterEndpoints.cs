using AmusedToDeath.Api.Data;
using AmusedToDeath.Api.Models;
using AmusedToDeath.Api.Security;

namespace AmusedToDeath.Api.Endpoints;

/// <summary>
/// Character routes. Character identity/data (name, class, realm, item level) is
/// exclusively Blizzard-sourced via the import flow and is NOT editable here. The
/// only user-editable data is the tank/heal/dps role flags and visibility; the
/// "main" relationship is set through the Battle.net character picker.
/// </summary>
public static class CharacterEndpoints
{
    public static IEndpointRouteBuilder MapCharacterEndpoints(this IEndpointRouteBuilder app)
    {
        var group = app.MapGroup("/api/characters").WithTags("Characters");

        group.MapGet("/", async (CharacterRepository repo, CancellationToken ct) =>
            Results.Ok(await repo.ListAsync(ownerId: null, ct)))
            .RequireAuth()
            .WithSummary("List all visible characters");

        group.MapGet("/mine", async (CharacterRepository repo, ICurrentUser user, CancellationToken ct) =>
            Results.Ok(user.OwnerId is null
                ? Array.Empty<Character>()
                : await repo.ListOwnedIncludingHiddenAsync(user.OwnerId, ct)))
            .RequireAuth()
            .WithSummary("List the current user's characters")
            .WithDescription("Includes the owner's hidden characters so they can toggle visibility.");

        group.MapGet("/{id:int}", async (int id, CharacterRepository repo, CancellationToken ct) =>
        {
            var character = await repo.GetAsync(id, ct);
            return character is null ? Results.Ok<Character?>(null) : Results.Ok(character);
        }).RequireAuth()
            .WithSummary("Get a character by id");

        group.MapGet("/{id:int}/alts", async (int id, CharacterRepository repo, CancellationToken ct) =>
            Results.Ok(await repo.AltsAsync(id, ct)))
            .RequireAuth()
            .WithSummary("List a character's alts");

        // Set the tank/heal/dps role flags — the only user-editable character data.
        // Owners may set roles on their own characters; admins on any.
        group.MapPost("/{id:int}/roles", async (int id, CharacterRolesRequest body,
            CharacterRepository repo, ICurrentUser user, CancellationToken ct) =>
        {
            var character = await repo.GetAsync(id, ct);
            if (character is null)
            {
                return Results.Json(new { error = "Character not found" }, statusCode: StatusCodes.Status404NotFound);
            }

            var owns = user.OwnerId is not null && character.OwnerId == user.OwnerId;
            if (!owns && !user.IsAdmin)
            {
                return Results.Json(new { error = "Not your character" }, statusCode: StatusCodes.Status403Forbidden);
            }

            await repo.SetRolesAsync(id, body.RoleTank, body.RoleHeal, body.RoleDps, ct);
            return Results.Ok(new { success = true });
        }).RequireAuth()
            .WithSummary("Set a character's roles (tank/heal/dps)")
            .WithDescription("Owners can set roles on their own characters; admins on any. This is the only editable character data.");

        // Toggle a character's visibility. Owners may toggle their own characters;
        // admins may toggle any.
        group.MapPost("/{id:int}/visibility", async (int id, CharacterVisibilityRequest body,
            CharacterRepository repo, ICurrentUser user, CancellationToken ct) =>
        {
            var character = await repo.GetAsync(id, ct);
            if (character is null)
            {
                return Results.Json(new { error = "Character not found" }, statusCode: StatusCodes.Status404NotFound);
            }

            var owns = user.OwnerId is not null && character.OwnerId == user.OwnerId;
            if (!owns && !user.IsAdmin)
            {
                return Results.Json(new { error = "Not your character" }, statusCode: StatusCodes.Status403Forbidden);
            }

            await repo.SetHiddenAsync(id, body.Hidden, ct);
            return Results.Ok(new { success = true, hidden = body.Hidden });
        }).RequireAuth()
            .WithSummary("Show or hide a character")
            .WithDescription("Owners can toggle their own characters; admins can toggle any.");

        return app;
    }
}
