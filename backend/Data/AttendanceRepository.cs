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
            SELECT a.id, a.added_date, a.bosses, a.paid, a."raidId", a."characterId",
                   c.name, c.class, c.main, c.discord, c.vip, c.ilvl,
                   c.role_tank, c.role_heal, c.role_dps
            FROM attendance a
            INNER JOIN characters c ON a."characterId" = c.id
            WHERE a."raidId" = @raidId
            ORDER BY a.added_date ASC
            """,
            new { raidId }, cancellationToken: ct));
        return rows.AsList();
    }

    public async Task<IReadOnlyList<CharacterAttendanceRow>> ForCharacterAsync(int characterId, CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);
        var rows = await db.QueryAsync<CharacterAttendanceRow>(new CommandDefinition(
            """
            SELECT a.id, a."characterId", a."raidId", a.bosses, a.paid,
                   a.added_date, a.change_date,
                   r.name, r.gold, r.comment
            FROM attendance a
            INNER JOIN raids r ON a."raidId" = r.id
            WHERE a."characterId" = @characterId
            """,
            new { characterId }, cancellationToken: ct));
        return rows.AsList();
    }

    public async Task AddAsync(int characterId, int raidId, int bosses, CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);
        await db.ExecuteAsync(new CommandDefinition(
            """
            INSERT INTO attendance ("characterId", "raidId", bosses)
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
            WHERE "characterId" = @characterId AND "raidId" = @raidId
            """,
            new { characterId, raidId, bosses, paid }, cancellationToken: ct));
    }

    public async Task DeleteAsync(int characterId, int raidId, CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);
        await db.ExecuteAsync(new CommandDefinition(
            """DELETE FROM attendance WHERE "characterId" = @characterId AND "raidId" = @raidId""",
            new { characterId, raidId }, cancellationToken: ct));
    }

    /// <summary>Adds every raider (character.raider = true) to the raid, ignoring
    /// any that already exist (matches legacy INSERT IGNORE via the unique
    /// (raidId, characterId) constraint).</summary>
    public async Task AddAllRaidersAsync(int raidId, CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);
        await db.ExecuteAsync(new CommandDefinition(
            """
            INSERT INTO attendance ("characterId", "raidId", bosses)
            SELECT c.id, @raidId, 0 FROM characters c WHERE c.raider = true
            ON CONFLICT ("raidId", "characterId") DO NOTHING
            """,
            new { raidId }, cancellationToken: ct));
    }

    public async Task RemoveZeroBossesAsync(int raidId, CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);
        await db.ExecuteAsync(new CommandDefinition(
            """DELETE FROM attendance WHERE bosses = 0 AND "raidId" = @raidId""",
            new { raidId }, cancellationToken: ct));
    }

    public async Task SetAllPaidAsync(int raidId, CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);
        await db.ExecuteAsync(new CommandDefinition(
            """UPDATE attendance SET paid = true WHERE "raidId" = @raidId""",
            new { raidId }, cancellationToken: ct));
    }
}
