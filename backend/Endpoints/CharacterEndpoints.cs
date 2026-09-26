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
        var group = app.MapGroup("/api/characters");

        group.MapGet("/", async (CharacterRepository repo, CancellationToken ct) =>
            Results.Ok(await repo.ListAsync(ownerDiscord: null, ct)))
            .RequireAuth();

        group.MapGet("/mine", async (CharacterRepository repo, ICurrentUser user, CancellationToken ct) =>
            Results.Ok(await repo.ListAsync(ownerDiscord: user.Name, ct)))
            .RequireAuth();

        group.MapGet("/{id:int}", async (int id, CharacterRepository repo, CancellationToken ct) =>
        {
            var character = await repo.GetAsync(id, ct);
            return character is null ? Results.Ok<Character?>(null) : Results.Ok(character);
        }).RequireAuth();

        group.MapGet("/{id:int}/alts", async (int id, CharacterRepository repo, CancellationToken ct) =>
            Results.Ok(await repo.AltsAsync(id, ct)))
            .RequireAuth();

        group.MapPost("/", SaveCharacter).RequireAuth();
        group.MapPut("/{id:int}", async (int id, CharacterSaveRequest body, CharacterRepository repo,
            ICurrentUser user, CancellationToken ct) =>
        {
            body.Id = id;
            return await SaveCharacterCore(body, repo, user, ct);
        }).RequireAuth();

        group.MapPost("/{id:int}/hide", async (int id, CharacterRepository repo, CancellationToken ct) =>
        {
            await repo.HideAsync(id, ct);
            return Results.Ok(new { success = true });
        }).RequireAdmin();

        return app;
    }

    private static async Task<IResult> SaveCharacter(CharacterSaveRequest body, CharacterRepository repo,
        ICurrentUser user, CancellationToken ct) =>
        await SaveCharacterCore(body, repo, user, ct);

    private static async Task<IResult> SaveCharacterCore(CharacterSaveRequest body, CharacterRepository repo,
        ICurrentUser user, CancellationToken ct)
    {
        var isAdmin = user.IsAdmin;

        // Non-admins may only own their own character; admins may assign discord.
        var discord = isAdmin ? (body.Discord ?? user.Name) : user.Name;

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
            Discord = discord,
        };

        if (character.Id > 0)
        {
            await repo.UpdateAsync(character, includeDiscord: isAdmin, ct);
            return Results.Ok(new { id = character.Id });
        }

        var newId = await repo.InsertAsync(character, ct);
        return Results.Ok(new { id = newId });
    }
}
