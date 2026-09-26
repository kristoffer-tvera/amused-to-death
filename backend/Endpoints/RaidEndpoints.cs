using Microsoft.Extensions.Options;
using AmusedToDeath.Api.Configuration;
using AmusedToDeath.Api.Data;
using AmusedToDeath.Api.Models;
using AmusedToDeath.Api.Security;
using AmusedToDeath.Api.Services;

namespace AmusedToDeath.Api.Endpoints;

/// <summary>
/// Raid routes plus the raid-scoped attendance bulk operations. Reads require a
/// session; all writes require admin. Creating a raid fires the announcement
/// webhook, matching the legacy raid_save().
/// </summary>
public static class RaidEndpoints
{
    public static IEndpointRouteBuilder MapRaidEndpoints(this IEndpointRouteBuilder app)
    {
        var group = app.MapGroup("/api/raids").WithTags("Raids");

        group.MapGet("/", async (RaidRepository repo, CancellationToken ct) =>
            Results.Ok(await repo.ListAsync(ct)))
            .RequireAuth()
            .WithSummary("List all raids");

        group.MapGet("/{id:int}", async (int id, RaidRepository repo, CancellationToken ct) =>
        {
            var raid = await repo.GetAsync(id, ct);
            return raid is null ? Results.Ok<Raid?>(null) : Results.Ok(raid);
        }).RequireAuth()
            .WithSummary("Get a raid by id");

        group.MapPost("/", async (RaidSaveRequest body, RaidRepository repo,
            DiscordWebhookService webhooks, IOptions<AppOptions> options, CancellationToken ct) =>
            await SaveRaid(body, repo, webhooks, options.Value, ct))
            .RequireAdmin()
            .WithSummary("Create a raid (admin)")
            .WithDescription("Posts a Discord announcement webhook for the new raid.");

        group.MapPut("/{id:int}", async (int id, RaidSaveRequest body, RaidRepository repo,
            DiscordWebhookService webhooks, IOptions<AppOptions> options, CancellationToken ct) =>
        {
            body.Id = id;
            return await SaveRaid(body, repo, webhooks, options.Value, ct);
        }).RequireAdmin()
            .WithSummary("Update a raid (admin)");

        // Raid-scoped attendance bulk operations (admin only).
        group.MapPost("/{raidId:int}/add-all-raiders", async (int raidId, AttendanceRepository repo, CancellationToken ct) =>
        {
            await repo.AddAllRaidersAsync(raidId, ct);
            return Results.Ok(new { success = true });
        }).RequireAdmin()
            .WithSummary("Add all raiders to a raid (admin)");

        group.MapPost("/{raidId:int}/remove-zero-bosses", async (int raidId, AttendanceRepository repo, CancellationToken ct) =>
        {
            await repo.RemoveZeroBossesAsync(raidId, ct);
            return Results.Ok(new { success = true });
        }).RequireAdmin()
            .WithSummary("Remove attendees with zero bosses (admin)");

        group.MapPost("/{raidId:int}/set-all-paid", async (int raidId, AttendanceRepository repo, CancellationToken ct) =>
        {
            await repo.SetAllPaidAsync(raidId, ct);
            return Results.Ok(new { success = true });
        }).RequireAdmin()
            .WithSummary("Mark all attendees paid (admin)");

        return app;
    }

    private static async Task<IResult> SaveRaid(RaidSaveRequest body, RaidRepository repo,
        DiscordWebhookService webhooks, AppOptions options, CancellationToken ct)
    {
        // Stored raw: React escapes on render and Dapper parameterizes all writes,
        // so no input-time HTML encoding is needed (see migration 0008).
        var raid = new Raid
        {
            Id = body.Id,
            Name = body.Name,
            Gold = body.Gold,
            Paid = body.Paid,
            Comment = body.Comment,
        };

        if (raid.Id > 0)
        {
            await repo.UpdateAsync(raid, ct);
            return Results.Ok(new { id = raid.Id });
        }

        var newId = await repo.InsertAsync(raid, ct);

        await webhooks.SendAsync(
            options.Webhooks.Announcement,
            $"@here New raid ({raid.Name}) posted! Visit {options.FrontendBaseUrl}/raid/?id={newId} to sign up!",
            mentions: ["everyone"], ct);

        return Results.Ok(new { id = newId });
    }
}
