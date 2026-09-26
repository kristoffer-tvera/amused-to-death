using Dapper;
using AmusedToDeath.Api.Models;

namespace AmusedToDeath.Api.Data;

/// <summary>
/// Data access for characters. Postgres translations of the queries that lived
/// in the legacy Services.php. Authorization is enforced in the endpoint layer;
/// this type only runs SQL.
/// </summary>
public sealed class CharacterRepository
{
    private readonly IDbConnectionFactory _connections;

    public CharacterRepository(IDbConnectionFactory connections)
    {
        _connections = connections;
    }

    public async Task<IReadOnlyList<Character>> ListAsync(string? ownerDiscord, CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);
        if (ownerDiscord is not null)
        {
            var mine = await db.QueryAsync<Character>(new CommandDefinition(
                "SELECT * FROM characters WHERE discord = @ownerDiscord AND hidden = false",
                new { ownerDiscord }, cancellationToken: ct));
            return mine.AsList();
        }

        var all = await db.QueryAsync<Character>(new CommandDefinition(
            "SELECT * FROM characters WHERE hidden = false", cancellationToken: ct));
        return all.AsList();
    }

    public async Task<Character?> GetAsync(int id, CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);
        return await db.QuerySingleOrDefaultAsync<Character>(new CommandDefinition(
            "SELECT * FROM characters WHERE id = @id", new { id }, cancellationToken: ct));
    }

    public async Task<IReadOnlyList<Character>> AltsAsync(int mainId, CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);
        var rows = await db.QueryAsync<Character>(new CommandDefinition(
            "SELECT * FROM characters WHERE main = @mainId AND hidden = false",
            new { mainId }, cancellationToken: ct));
        return rows.AsList();
    }

    /// <summary>Inserts a new character and returns its id.</summary>
    public async Task<int> InsertAsync(Character c, CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);
        return await db.ExecuteScalarAsync<int>(new CommandDefinition(
            """
            INSERT INTO characters (name, class, main, realm, role_tank, role_heal, role_dps, raider, vip, discord)
            VALUES (@Name, @Class, @Main, @Realm, @RoleTank, @RoleHeal, @RoleDps, @Raider, @Vip, @Discord)
            RETURNING id
            """,
            c, cancellationToken: ct));
    }

    /// <summary>Updates a character. When <paramref name="includeDiscord"/> is
    /// false the discord/owner column is left untouched (non-admin path).</summary>
    public async Task UpdateAsync(Character c, bool includeDiscord, CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);
        var sql = includeDiscord
            ? """
              UPDATE characters SET name=@Name, class=@Class, main=@Main, realm=@Realm,
                     role_tank=@RoleTank, role_heal=@RoleHeal, role_dps=@RoleDps,
                     raider=@Raider, vip=@Vip, discord=@Discord
              WHERE id=@Id
              """
            : """
              UPDATE characters SET name=@Name, class=@Class, main=@Main, realm=@Realm,
                     role_tank=@RoleTank, role_heal=@RoleHeal, role_dps=@RoleDps,
                     raider=@Raider, vip=@Vip
              WHERE id=@Id
              """;
        await db.ExecuteAsync(new CommandDefinition(sql, c, cancellationToken: ct));
    }

    public async Task HideAsync(int id, CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);
        await db.ExecuteAsync(new CommandDefinition(
            "UPDATE characters SET hidden = true WHERE id = @id", new { id }, cancellationToken: ct));
    }

    public async Task SetIlvlAsync(int id, int ilvl, CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);
        await db.ExecuteAsync(new CommandDefinition(
            "UPDATE characters SET ilvl = @ilvl WHERE id = @id", new { id, ilvl }, cancellationToken: ct));
    }

    /// <summary>True if a character row exists for the given discord username.</summary>
    public async Task<bool> ExistsForDiscordAsync(string discord, CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);
        return await db.ExecuteScalarAsync<bool>(new CommandDefinition(
            "SELECT EXISTS(SELECT 1 FROM characters WHERE discord = @discord)",
            new { discord }, cancellationToken: ct));
    }

    // ─── Battle.net import support ───────────────────────────────────────────

    /// <summary>Finds a character by name + realm (case-insensitive), or null.</summary>
    public async Task<Character?> FindByNameRealmAsync(string name, string realm, CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);
        return await db.QuerySingleOrDefaultAsync<Character>(new CommandDefinition(
            "SELECT * FROM characters WHERE lower(name) = lower(@name) AND lower(realm) = lower(@realm) LIMIT 1",
            new { name, realm }, cancellationToken: ct));
    }

    /// <summary>Inserts a Battle.net-imported character owned by the given account.</summary>
    public async Task<int> InsertOwnedAsync(string name, string realm, int classId, string ownerId,
        int? main, CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);
        return await db.ExecuteScalarAsync<int>(new CommandDefinition(
            """
            INSERT INTO characters (name, class, realm, owner_id, main)
            VALUES (@name, @classId, @realm, @ownerId, @main)
            RETURNING id
            """,
            new { name, classId, realm, ownerId, main }, cancellationToken: ct));
    }

    /// <summary>Claims an existing character for an account (the "migrate" path):
    /// sets owner_id, and optionally the class and main, without disturbing other
    /// data on the record.</summary>
    public async Task ClaimAsync(int id, string ownerId, int? main, CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);
        await db.ExecuteAsync(new CommandDefinition(
            "UPDATE characters SET owner_id = @ownerId, main = @main WHERE id = @id",
            new { id, ownerId, main }, cancellationToken: ct));
    }

    /// <summary>Sets which character is a given character's main (change-main later).</summary>
    public async Task SetMainAsync(int id, int? main, CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);
        await db.ExecuteAsync(new CommandDefinition(
            "UPDATE characters SET main = @main WHERE id = @id",
            new { id, main }, cancellationToken: ct));
    }
}
