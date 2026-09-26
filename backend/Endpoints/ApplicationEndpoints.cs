using Microsoft.Extensions.Options;
using AmusedToDeath.Api.Configuration;
using AmusedToDeath.Api.Data;
using AmusedToDeath.Api.Models;
using AmusedToDeath.Api.Security;
using AmusedToDeath.Api.Services;

namespace AmusedToDeath.Api.Endpoints;

/// <summary>
/// Recruitment application routes.
///   GET  /api/applications        -> admin: public summary list
///   GET  /api/applications/{id}   -> admin (full) OR applicant with ?auth= token
///   POST /api/applications        -> PUBLIC submit (honeypot-protected)
/// The auth token is never returned to the client. Submitting fires the
/// recruitment webhook, matching the legacy application_save().
/// </summary>
public static class ApplicationEndpoints
{
    public static IEndpointRouteBuilder MapApplicationEndpoints(this IEndpointRouteBuilder app)
    {
        var group = app.MapGroup("/api/applications").WithTags("Applications");

        group.MapGet("/", async (ApplicationRepository repo, CancellationToken ct) =>
            Results.Ok(await repo.ListAsync(ct)))
            .RequireAuth()
            .WithSummary("List applications")
            .WithDescription("Any logged-in guild member can list applications.");

        // Public route: any logged-in guild member gets full access. Anonymous
        // applicants must supply their own edit token via ?auth=. Not gated by
        // RequireAuth because applicants are anonymous — the token is their access.
        group.MapGet("/{id:int}", async (int id, string? auth, ApplicationRepository repo,
            ICurrentUser user, CancellationToken ct) =>
        {
            // Logged-in members see any application (auth = null -> unrestricted).
            // Anonymous callers are restricted to the application matching their token.
            var effectiveAuth = user.IsAuthenticated ? null : auth;
            if (!user.IsAuthenticated && string.IsNullOrEmpty(auth))
            {
                return Results.Ok<ApplicationDetail?>(null);
            }
            var result = await repo.GetAsync(id, effectiveAuth, ct);
            return Results.Ok(result);
        })
            .WithSummary("Get an application")
            .WithDescription("Logged-in members see any application; anonymous applicants must pass their edit token as ?auth=.");

        // Revision history — reviewer-only. RequireAuth means an applicant's
        // ?auth= edit token can never reach these; the history is invisible to
        // the applicant, satisfying "not a visible feature" for them.
        group.MapGet("/{id:int}/versions", async (int id, ApplicationRepository repo, CancellationToken ct) =>
            Results.Ok(await repo.ListVersionsAsync(id, ct)))
            .RequireAuth()
            .WithSummary("List an application's versions (reviewer)")
            .WithDescription("Logged-in members only. Newest first. Applicants cannot see revision history.");

        group.MapGet("/{id:int}/versions/{versionNo:int}", async (int id, int versionNo,
            ApplicationRepository repo, CancellationToken ct) =>
        {
            var version = await repo.GetVersionAsync(id, versionNo, ct);
            return Results.Ok(version);
        })
            .RequireAuth()
            .WithSummary("Get a specific application version (reviewer)")
            .WithDescription("Logged-in members only. Returns one immutable snapshot; the frontend diff page fetches two of these to compare.");

        // Public submit. Honeypot: pepe must equal "meme" or we no-op silently.
        group.MapPost("/", async (ApplicationSaveRequest body, ApplicationRepository repo,
            DiscordWebhookService webhooks, IOptions<AppOptions> options, CancellationToken ct) =>
        {
            if (body.Pepe != "meme")
            {
                // Bot caught by honeypot — pretend success without persisting.
                return Results.Ok(new { success = true });
            }

            var isNew = body.Id == 0;

            // Free-text fields are stored raw. React escapes on render, and all
            // DB access is parameterized via Dapper, so no input-time encoding is
            // needed (see migration 0008, which decoded the legacy entity soup).

            var baseUrl = options.Value.FrontendBaseUrl;

            if (isNew)
            {
                var inserted = await repo.InsertAsync(body, ct);
                // First version: only a view link (no previous version to diff).
                var title = $"New app! ({body.Name} - {body.Server}) — "
                    + $"[view]({baseUrl}/app/{inserted.Id}?v=1)";
                await webhooks.SendAsync(options.Value.Webhooks.Recruitment, title, ct: ct);
                return Results.Ok(inserted);
            }

            var updated = await repo.UpdateAsync(body, ct);

            // No-op update (button click with zero diff): no new version was
            // written and updated_at was left untouched, so skip the Discord ping.
            // Still report success to the client so the UX is unchanged.
            if (updated.Changed)
            {
                // Two clickable links: the snapshot just created, and a diff of the
                // previous version against the new one.
                var viewUrl = $"{baseUrl}/app/{updated.Id}?v={updated.NewVersionNo}";
                var diffUrl = $"{baseUrl}/app/{updated.Id}/diff?from={updated.PreviousVersionNo}&to={updated.NewVersionNo}";
                var title = $"App update! ({body.Name} - {body.Server}) — "
                    + $"[view this version]({viewUrl}) · [see the diff]({diffUrl})";
                await webhooks.SendAsync(options.Value.Webhooks.Recruitment, title, ct: ct);
            }

            return Results.Ok(new ApplicationSaveResult(updated.Id, updated.Auth));
        })
            .WithSummary("Submit or update an application (public)")
            .WithDescription("Honeypot-protected. Returns { id, auth } so the applicant can revisit their application at /app/{id}?auth={token}.");

        return app;
    }
}
