using Dapper;
using AmusedToDeath.Api.Data;

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
        app.MapGet("/api/health", async (IDbConnectionFactory connections, CancellationToken ct) =>
        {
            try
            {
                await using var db = await connections.OpenConnectionAsync(ct);
                var ready = await db.ExecuteScalarAsync<bool>(
                    new CommandDefinition(ReadinessQuery, cancellationToken: ct));

                return ready
                    ? (IResult)Results.Ok(new { status = "ready" })
                    : Results.StatusCode(StatusCodes.Status503ServiceUnavailable);
            }
            catch (Npgsql.NpgsqlException)
            {
                return Results.StatusCode(StatusCodes.Status503ServiceUnavailable);
            }
        }).AllowAnonymous();
    }
}