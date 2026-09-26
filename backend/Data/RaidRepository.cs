using Dapper;
using AmusedToDeath.Api.Models;

namespace AmusedToDeath.Api.Data;

public sealed class RaidRepository
{
    private readonly IDbConnectionFactory _connections;

    public RaidRepository(IDbConnectionFactory connections)
    {
        _connections = connections;
    }

    public async Task<IReadOnlyList<Raid>> ListAsync(CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);
        var rows = await db.QueryAsync<Raid>(new CommandDefinition(
            "SELECT * FROM raids", cancellationToken: ct));
        return rows.AsList();
    }

    public async Task<Raid?> GetAsync(int id, CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);
        return await db.QuerySingleOrDefaultAsync<Raid>(new CommandDefinition(
            "SELECT * FROM raids WHERE id = @id", new { id }, cancellationToken: ct));
    }

    public async Task<int> InsertAsync(Raid r, CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);
        return await db.ExecuteScalarAsync<int>(new CommandDefinition(
            """
            INSERT INTO raids (name, gold, paid, comment)
            VALUES (@Name, @Gold, @Paid, @Comment)
            RETURNING id
            """,
            r, cancellationToken: ct));
    }

    public async Task UpdateAsync(Raid r, CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);
        await db.ExecuteAsync(new CommandDefinition(
            "UPDATE raids SET name=@Name, gold=@Gold, paid=@Paid, comment=@Comment WHERE id=@Id",
            r, cancellationToken: ct));
    }
}
