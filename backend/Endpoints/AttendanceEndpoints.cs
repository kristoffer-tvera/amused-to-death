using AmusedToDeath.Api.Data;
using AmusedToDeath.Api.Models;
using AmusedToDeath.Api.Security;

namespace AmusedToDeath.Api.Endpoints;

/// <summary>
/// Attendance routes. Reads are exposed under the owning resource
/// (/api/raids/{raidId}/attendance, /api/characters/{characterId}/attendance);
/// individual add/update/delete live under /api/attendance. All require a session.
/// </summary>
public static class AttendanceEndpoints
{
    public static IEndpointRouteBuilder MapAttendanceEndpoints(this IEndpointRouteBuilder app)
    {
        app.MapGet("/api/raids/{raidId:int}/attendance",
            async (int raidId, AttendanceRepository repo, CancellationToken ct) =>
                Results.Ok(await repo.ForRaidAsync(raidId, ct)))
            .RequireAuth();

        app.MapGet("/api/characters/{characterId:int}/attendance",
            async (int characterId, AttendanceRepository repo, CancellationToken ct) =>
                Results.Ok(await repo.ForCharacterAsync(characterId, ct)))
            .RequireAuth();

        var group = app.MapGroup("/api/attendance");

        group.MapPost("/", async (AttendanceAddRequest body, AttendanceRepository repo, CancellationToken ct) =>
        {
            await repo.AddAsync(body.Character, body.Raid, body.Bosses, ct);
            return Results.Ok(new { success = true });
        }).RequireAuth();

        group.MapPut("/", async (AttendanceUpdateRequest body, AttendanceRepository repo, CancellationToken ct) =>
        {
            await repo.UpdateAsync(body.CharacterId, body.RaidId, body.Bosses, body.Paid, ct);
            // Legacy returned a bare boolean; preserve that shape.
            return Results.Ok(true);
        }).RequireAuth();

        group.MapDelete("/", async (int characterId, int raidId, AttendanceRepository repo, CancellationToken ct) =>
        {
            await repo.DeleteAsync(characterId, raidId, ct);
            return Results.Ok(new { success = true });
        }).RequireAuth();

        return app;
    }
}
