using Dapper;
using AmusedToDeath.Api.Models;

namespace AmusedToDeath.Api.Data;

public sealed class AttendanceRepository
{
    private readonly IDbConnectionFactory _connections;

    public AttendanceRepository(IDbConnectionFactory connections)
    {
        _connections = connections;
    }

    public async Task<IReadOnlyList<RaidAttendanceRow>> ForRaidAsync(int raidId, CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);
        var rows = await db.QueryAsync<RaidAttendanceRow>(new CommandDefinition(
            """
            SELECT a.id, a.created_at, a.bosses, a.paid, a.raid_id, a.character_id,
                   c.name, c.class, c.main, c.ilvl,
                   c.role_tank, c.role_heal, c.role_dps
            FROM attendance a
            INNER JOIN characters c ON a.character_id = c.id
            WHERE a.raid_id = @raidId
            ORDER BY a.created_at ASC
            """,
            new { raidId }, cancellationToken: ct));
        return rows.AsList();
    }

    public async Task<IReadOnlyList<CharacterAttendanceRow>> ForCharacterAsync(int characterId, CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);
        var rows = await db.QueryAsync<CharacterAttendanceRow>(new CommandDefinition(
            """
            SELECT a.id, a.character_id, a.raid_id, a.bosses, a.paid,
                   a.created_at, a.updated_at,
                   r.name, r.gold, r.comment
            FROM attendance a
            INNER JOIN raids r ON a.raid_id = r.id
            WHERE a.character_id = @characterId
            """,
            new { characterId }, cancellationToken: ct));
        return rows.AsList();
    }

    public async Task AddAsync(int characterId, int raidId, int bosses, CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);
        await db.ExecuteAsync(new CommandDefinition(
            """
            INSERT INTO attendance (character_id, raid_id, bosses)
            VALUES (@characterId, @raidId, @bosses)
            """,
            new { characterId, raidId, bosses }, cancellationToken: ct));
    }

    public async Task UpdateAsync(int characterId, int raidId, int bosses, bool paid, CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);
        await db.ExecuteAsync(new CommandDefinition(
            """
            UPDATE attendance SET bosses = @bosses, paid = @paid
            WHERE character_id = @characterId AND raid_id = @raidId
            """,
            new { characterId, raidId, bosses, paid }, cancellationToken: ct));
    }

    public async Task DeleteAsync(int characterId, int raidId, CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);
        await db.ExecuteAsync(new CommandDefinition(
            "DELETE FROM attendance WHERE character_id = @characterId AND raid_id = @raidId",
            new { characterId, raidId }, cancellationToken: ct));
    }

    /// <summary>Adds every owned, non-hidden MAIN character to the raid, ignoring
    /// any that already exist (via the unique (raid_id, character_id) constraint).
    /// A main is a character that is its own main (or has no main set).</summary>
    public async Task AddAllRaidersAsync(int raidId, CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);
        await db.ExecuteAsync(new CommandDefinition(
            """
            INSERT INTO attendance (character_id, raid_id, bosses)
            SELECT c.id, @raidId, 0 FROM characters c
            WHERE c.owner_id IS NOT NULL AND c.hidden = false
              AND (c.main IS NULL OR c.main = -1 OR c.main = c.id)
            ON CONFLICT (raid_id, character_id) DO NOTHING
            """,
            new { raidId }, cancellationToken: ct));
    }

    public async Task RemoveZeroBossesAsync(int raidId, CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);
        await db.ExecuteAsync(new CommandDefinition(
            "DELETE FROM attendance WHERE bosses = 0 AND raid_id = @raidId",
            new { raidId }, cancellationToken: ct));
    }

    public async Task SetAllPaidAsync(int raidId, CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);
        await db.ExecuteAsync(new CommandDefinition(
            "UPDATE attendance SET paid = true WHERE raid_id = @raidId",
            new { raidId }, cancellationToken: ct));
    }
}
