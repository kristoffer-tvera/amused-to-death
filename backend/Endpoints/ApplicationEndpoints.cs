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
        var group = app.MapGroup("/api/applications");

        group.MapGet("/", async (ApplicationRepository repo, CancellationToken ct) =>
            Results.Ok(await repo.ListAsync(ct)))
            .RequireAuth();

        // Public route: admins get full access; applicants must supply their
        // matching auth token via ?auth=. Not gated by RequireAuth because
        // applicants are anonymous — access control is the token itself.
        group.MapGet("/{id:int}", async (int id, string? auth, ApplicationRepository repo,
            ICurrentUser user, CancellationToken ct) =>
        {
            var effectiveAuth = user.IsAdmin ? null : auth;
            // A non-admin with no token cannot see anything.
            if (!user.IsAdmin && string.IsNullOrEmpty(auth))
            {
                return Results.Ok<ApplicationDetail?>(null);
            }
            var result = await repo.GetAsync(id, effectiveAuth, ct);
            return Results.Ok(result);
        });

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

            // Sanitize all free-text fields, matching legacy input handling.
            body.Name = InputSanitizer.Clean(body.Name);
            body.Server = InputSanitizer.Clean(body.Server);
            body.Btag = InputSanitizer.Clean(body.Btag);
            body.Spec = InputSanitizer.Clean(body.Spec);
            body.Ui = InputSanitizer.Clean(body.Ui);
            body.Reason = InputSanitizer.Clean(body.Reason);
            body.History = InputSanitizer.Clean(body.History);
            body.Alts = InputSanitizer.Clean(body.Alts);

            var result = isNew
                ? await repo.InsertAsync(body, ct)
                : await repo.UpdateAsync(body, ct);

            var title = (isNew ? "New app!" : "App update!")
                + $" ({body.Name} - {body.Server}) -- {options.Value.FrontendBaseUrl}/app/{result.Id}";
            await webhooks.SendAsync(options.Value.Webhooks.Recruitment, title, ct: ct);

            return Results.Ok(result);
        });

        return app;
    }
}
