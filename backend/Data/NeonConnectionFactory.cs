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
    private readonly ILogger<NeonConnectionFactory> _logger;

    public NeonConnectionFactory(NpgsqlDataSource dataSource, ILogger<NeonConnectionFactory> logger)
    {
        _dataSource = dataSource;
        _logger = logger;
    }

    public async Task<NpgsqlConnection> OpenConnectionAsync(CancellationToken ct = default)
    {
        // Every physical connection open can wake a scale-to-zero database and
        // costs metered compute. Logged at Debug so the open rate is observable
        // when diagnosing unexpected compute usage, without noise in production.
        _logger.LogDebug("Opening database connection.");
        var connection = await _dataSource.OpenConnectionAsync(ct);
        return connection;
    }
}
