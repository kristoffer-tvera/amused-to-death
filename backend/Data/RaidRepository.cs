using Dapper;
using AmusedToDeath.Api.Models;

namespace AmusedToDeath.Api.Data;

public sealed class RaidRepository
{
    private readonly IDbConnectionFactory _connections;
    private readonly RepositoryCache _cache;

    // Reads depend on the raids table. Raid writes also change the character's
    // attendance history view (it embeds raid name/gold/comment), so raid writes
    // invalidate Attendance as well.
    private static readonly CacheTable[] ReadTables = [CacheTable.Raids];
    private static readonly CacheTable[] WriteTables = [CacheTable.Raids, CacheTable.Attendance];

    public RaidRepository(IDbConnectionFactory connections, RepositoryCache cache)
    {
        _connections = connections;
        _cache = cache;
    }

    public Task<IReadOnlyList<Raid>> ListAsync(CancellationToken ct = default)
    {
        return _cache.GetOrCreateAsync("raids:list", ReadTables, async () =>
        {
            await using var db = await _connections.OpenConnectionAsync(ct);
            var rows = await db.QueryAsync<Raid>(new CommandDefinition(
                "SELECT * FROM raids", cancellationToken: ct));
            return (IReadOnlyList<Raid>)rows.AsList();
        });
    }

    public Task<Raid?> GetAsync(int id, CancellationToken ct = default)
    {
        return _cache.GetOrCreateAsync($"raids:get:{id}", ReadTables, async () =>
        {
            await using var db = await _connections.OpenConnectionAsync(ct);
            return await db.QuerySingleOrDefaultAsync<Raid>(new CommandDefinition(
                "SELECT * FROM raids WHERE id = @id", new { id }, cancellationToken: ct));
        });
    }

    public async Task<int> InsertAsync(Raid r, CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);
        var id = await db.ExecuteScalarAsync<int>(new CommandDefinition(
            """
            INSERT INTO raids (name, gold, paid, comment)
            VALUES (@Name, @Gold, @Paid, @Comment)
            RETURNING id
            """,
            r, cancellationToken: ct));
        _cache.Invalidate(WriteTables);
        return id;
    }

    public async Task UpdateAsync(Raid r, CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);
        await db.ExecuteAsync(new CommandDefinition(
            "UPDATE raids SET name=@Name, gold=@Gold, paid=@Paid, comment=@Comment WHERE id=@Id",
            r, cancellationToken: ct));
        _cache.Invalidate(WriteTables);
    }
}
