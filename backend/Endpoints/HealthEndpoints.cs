using AmusedToDeath.Api.Data;
using Dapper;

namespace AmusedToDeath.Api.Endpoints;

public static class HealthEndpoints
{
    private const string ReadinessQuery = """
        SELECT to_regclass('public.characters') IS NOT NULL
           AND to_regclass('public.raids') IS NOT NULL
           AND to_regclass('public.attendance') IS NOT NULL
           AND to_regclass('public.applications') IS NOT NULL
           AND to_regclass('public.sessions') IS NOT NULL
           AND to_regclass('public.application_versions') IS NOT NULL;
        """;

    public static void MapHealthEndpoints(this WebApplication app)
    {
        // Liveness: "is the process up and serving?" — deliberately does NOT touch
        // the database. Container healthchecks poll this frequently (every few
        // seconds), so hitting Postgres here would keep a scale-to-zero database
        // (e.g. Neon) permanently awake and burn metered compute around the clock.
        app.MapGet("/api/health/live", () => Results.Ok(new { status = "alive" }))
            .AllowAnonymous();

        // Readiness: "can the app actually reach its dependencies?" — opens a DB
        // connection and verifies the schema. This WAKES the database, so it must
        // only be called on demand (e.g. post-deploy verification), never on a
        // short polling loop. Do not point a container healthcheck at it.
        app.MapGet(
                "/api/health",
                async (IDbConnectionFactory connections, ILoggerFactory loggerFactory, CancellationToken ct) =>
                {
                    var logger = loggerFactory.CreateLogger("Health");
                    try
                    {
                        await using var db = await connections.OpenConnectionAsync(ct);
                        var ready = await db.ExecuteScalarAsync<bool>(
                            new CommandDefinition(ReadinessQuery, cancellationToken: ct)
                        );

                        if (!ready)
                        {
                            logger.LogWarning("Readiness check failed: expected schema objects are missing.");
                            return Results.StatusCode(StatusCodes.Status503ServiceUnavailable);
                        }

                        return (IResult)Results.Ok(new { status = "ready" });
                    }
                    catch (Npgsql.NpgsqlException ex)
                    {
                        logger.LogError(ex, "Readiness check failed: could not query the database.");
                        return Results.StatusCode(StatusCodes.Status503ServiceUnavailable);
                    }
                }
            )
            .AllowAnonymous();
    }
}
