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

    public async Task<IReadOnlyList<Character>> ListAsync(string? ownerId, CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);
        if (ownerId is not null)
        {
            var mine = await db.QueryAsync<Character>(new CommandDefinition(
                "SELECT * FROM characters WHERE owner_id = @ownerId AND hidden = false",
                new { ownerId }, cancellationToken: ct));
            return mine.AsList();
        }

        var all = await db.QueryAsync<Character>(new CommandDefinition(
            "SELECT * FROM characters WHERE hidden = false", cancellationToken: ct));
        return all.AsList();
    }

    /// <summary>
    /// Lists everything owned by an account, INCLUDING hidden characters. Used for
    /// the owner's own "my characters" view so they can see and un-hide them.
    /// </summary>
    public async Task<IReadOnlyList<Character>> ListOwnedIncludingHiddenAsync(string ownerId, CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);
        var rows = await db.QueryAsync<Character>(new CommandDefinition(
            "SELECT * FROM characters WHERE owner_id = @ownerId",
            new { ownerId }, cancellationToken: ct));
        return rows.AsList();
    }

    /// <summary>
    /// Lists every hidden character regardless of owner. Admin-only: this is the
    /// counterpart to <see cref="ListAsync"/> so admins can see and un-hide
    /// characters they do not own (including ownerless ones), which is otherwise
    /// impossible once hidden. Ordered by name for a stable admin view.
    /// </summary>
    public async Task<IReadOnlyList<Character>> ListHiddenAsync(CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);
        var rows = await db.QueryAsync<Character>(new CommandDefinition(
            "SELECT * FROM characters WHERE hidden = true ORDER BY name", cancellationToken: ct));
        return rows.AsList();
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

    /// <summary>
    /// Sets the tank/heal/dps role flags on a character. This is the only
    /// user-editable character data — everything else is Blizzard-sourced.
    /// </summary>
    public async Task SetRolesAsync(int id, bool tank, bool heal, bool dps, CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);
        await db.ExecuteAsync(new CommandDefinition(
            "UPDATE characters SET role_tank = @tank, role_heal = @heal, role_dps = @dps WHERE id = @id",
            new { id, tank, heal, dps }, cancellationToken: ct));
    }

    public async Task HideAsync(int id, CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);
        await db.ExecuteAsync(new CommandDefinition(
            "UPDATE characters SET hidden = true WHERE id = @id", new { id }, cancellationToken: ct));
    }

    /// <summary>Sets a character's hidden flag to an explicit value.</summary>
    public async Task SetHiddenAsync(int id, bool hidden, CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);
        await db.ExecuteAsync(new CommandDefinition(
            "UPDATE characters SET hidden = @hidden WHERE id = @id", new { id, hidden }, cancellationToken: ct));
    }

    public async Task SetIlvlAsync(int id, int ilvl, CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);
        await db.ExecuteAsync(new CommandDefinition(
            "UPDATE characters SET ilvl = @ilvl WHERE id = @id", new { id, ilvl }, cancellationToken: ct));
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

    /// <summary>Claims a character for an account (the "migrate"/re-import path):
    /// sets owner_id and main, and un-hides it (a re-imported character should be
    /// visible again). Other data on the record is preserved.</summary>
    public async Task ClaimAsync(int id, string ownerId, int? main, CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);
        await db.ExecuteAsync(new CommandDefinition(
            "UPDATE characters SET owner_id = @ownerId, main = @main, hidden = false WHERE id = @id",
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

    /// <summary>All (visible) character names owned by an account. Used to
    /// re-verify guild membership on login without re-fetching from Blizzard.</summary>
    public async Task<IReadOnlyList<string>> OwnedNamesAsync(string ownerId, CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);
        var rows = await db.QueryAsync<string>(new CommandDefinition(
            "SELECT name FROM characters WHERE owner_id = @ownerId AND hidden = false",
            new { ownerId }, cancellationToken: ct));
        return rows.AsList();
    }

    /// <summary>True if the account owns at least one (visible) character.</summary>
    public async Task<bool> HasOwnedAsync(string ownerId, CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);
        return await db.ExecuteScalarAsync<bool>(new CommandDefinition(
            "SELECT EXISTS(SELECT 1 FROM characters WHERE owner_id = @ownerId AND hidden = false)",
            new { ownerId }, cancellationToken: ct));
    }

    /// <summary>
    /// Hides all owned characters whose name is not in the supplied current-roster
    /// set. Never deletes — hiding preserves raid history. Returns the number hidden.
    /// </summary>
    public async Task<int> HideAbsentFromRosterAsync(IReadOnlyCollection<string> rosterNames, CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);
        // Only consider owned characters (guild members). Anything visible whose
        // name is not in the current roster gets hidden.
        return await db.ExecuteAsync(new CommandDefinition(
            """
            UPDATE characters
            SET hidden = true
            WHERE hidden = false
              AND owner_id IS NOT NULL
              AND lower(name) <> ALL(@names)
            """,
            new { names = rosterNames.Select(n => n.ToLowerInvariant()).ToArray() },
            cancellationToken: ct));
    }
}
