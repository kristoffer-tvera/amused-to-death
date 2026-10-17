using System.Reflection;
using DbUp;
using DbUp.Engine.Output;

namespace AmusedToDeath.Api.Data;

/// <summary>
/// Runs pending database migrations when explicitly invoked with --migrate.
///
/// Scripts live in Migrations/*.sql, embedded in this assembly, named
/// NNNN_Description.sql and applied in lexical order. DbUp records applied
/// scripts in a "schemaversions" table and skips anything already run.
///
/// Review pending scripts and database compatibility before invoking this
/// migration command; schema changes are not part of ordinary API startup.
/// </summary>
public static class DatabaseMigrator
{
    public static void Run(string connectionString, ILogger logger)
    {
        var upgrader = DeployChanges
            .To.PostgresqlDatabase(connectionString)
            .WithScriptsEmbeddedInAssembly(
                Assembly.GetExecutingAssembly(),
                name =>
                    name.Contains(".Migrations.")
                    && name.EndsWith(".sql", StringComparison.OrdinalIgnoreCase)
            )
            .WithTransactionPerScript()
            .LogTo(new DbUpLoggerAdapter(logger))
            .Build();

        if (!upgrader.IsUpgradeRequired())
        {
            logger.LogInformation("Database is up to date; no migrations to apply.");
            return;
        }

        var result = upgrader.PerformUpgrade();
        if (!result.Successful)
        {
            // Fail fast: a broken schema means the app can't serve correctly.
            throw new InvalidOperationException("Database migration failed.", result.Error);
        }

        logger.LogInformation("Applied {Count} database migration(s).", result.Scripts.Count());
    }

    /// <summary>Bridges DbUp's IUpgradeLog to the app's ILogger.</summary>
    private sealed class DbUpLoggerAdapter(ILogger logger) : IUpgradeLog
    {
        public void LogTrace(string format, params object[] args) => logger.LogTrace(format, args);

        public void LogDebug(string format, params object[] args) => logger.LogDebug(format, args);

        public void LogInformation(string format, params object[] args) =>
            logger.LogInformation(format, args);

        public void LogWarning(string format, params object[] args) =>
            logger.LogWarning(format, args);

        public void LogError(string format, params object[] args) => logger.LogError(format, args);

        public void LogError(Exception ex, string format, params object[] args) =>
            logger.LogError(ex, format, args);
    }
}
