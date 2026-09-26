using System.Data;
using Npgsql;

namespace AmusedToDeath.Api.Data;

/// <summary>
/// Creates open connections to the Neon Postgres database.
/// Registered as a singleton; each call yields a fresh connection that the
/// caller owns and disposes (Npgsql pools the underlying physical connections).
/// </summary>
public interface IDbConnectionFactory
{
    Task<NpgsqlConnection> OpenConnectionAsync(CancellationToken ct = default);
}

public sealed class NeonConnectionFactory : IDbConnectionFactory
{
    private readonly NpgsqlDataSource _dataSource;

    public NeonConnectionFactory(NpgsqlDataSource dataSource)
    {
        _dataSource = dataSource;
    }

    public async Task<NpgsqlConnection> OpenConnectionAsync(CancellationToken ct = default)
    {
        var connection = await _dataSource.OpenConnectionAsync(ct);
        return connection;
    }
}
