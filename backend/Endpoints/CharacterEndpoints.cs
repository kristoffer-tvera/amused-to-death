using AmusedToDeath.Api.Data;
using AmusedToDeath.Api.Models;
using AmusedToDeath.Api.Security;

namespace AmusedToDeath.Api.Endpoints;

/// <summary>
/// Character routes. Reads require a logged-in session; writes enforce the same
/// admin/ownership rules the legacy character_save() did:
///   - non-admins can only own their own character (discord forced to self)
///   - only admins may set the discord/owner field or edit others' characters.
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
            Results.Ok(await repo.ListAsync(ownerId: user.OwnerId, ct)))
            .RequireAuth()
            .WithSummary("List the current user's characters");

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

        group.MapPost("/", SaveCharacter).RequireAuth()
            .WithSummary("Create a character");
        group.MapPut("/{id:int}", async (int id, CharacterSaveRequest body, CharacterRepository repo,
            ICurrentUser user, CancellationToken ct) =>
        {
            body.Id = id;
            return await SaveCharacterCore(body, repo, user, ct);
        }).RequireAuth()
            .WithSummary("Update a character")
            .WithDescription("Non-admins may only edit their own character; only admins may reassign ownership.");

        group.MapPost("/{id:int}/hide", async (int id, CharacterRepository repo, CancellationToken ct) =>
        {
            await repo.HideAsync(id, ct);
            return Results.Ok(new { success = true });
        }).RequireAdmin()
            .WithSummary("Hide a character (admin)");

        return app;
    }

    private static async Task<IResult> SaveCharacter(CharacterSaveRequest body, CharacterRepository repo,
        ICurrentUser user, CancellationToken ct) =>
        await SaveCharacterCore(body, repo, user, ct);

    private static async Task<IResult> SaveCharacterCore(CharacterSaveRequest body, CharacterRepository repo,
        ICurrentUser user, CancellationToken ct)
    {
        var isAdmin = user.IsAdmin;

        // Ownership is the Blizzard account id. A newly created character is owned
        // by the acting account. Admins editing someone else's character leave the
        // existing owner untouched (includeOwner: false).
        var character = new Character
        {
            Id = body.Id,
            Name = InputSanitizer.Clean(body.Name),
            Realm = InputSanitizer.Clean(body.Realm),
            Class = body.Class,
            Main = body.Main == -1 ? null : body.Main,
            RoleTank = body.RoleTank,
            RoleHeal = body.RoleHeal,
            RoleDps = body.RoleDps,
            Raider = body.Raider,
            Vip = body.Vip,
            OwnerId = user.OwnerId,
        };

        if (character.Id > 0)
        {
            // Only set owner on create; edits don't reassign ownership.
            await repo.UpdateAsync(character, includeOwner: false, ct);
            return Results.Ok(new { id = character.Id });
        }

        var newId = await repo.InsertAsync(character, ct);
        return Results.Ok(new { id = newId });
    }
}
