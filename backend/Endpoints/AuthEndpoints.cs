using AmusedToDeath.Api.Models;
using AmusedToDeath.Api.Security;

namespace AmusedToDeath.Api.Endpoints;

/// <summary>
/// Session routes shared across the app. Login itself is handled by the
/// Battle.net flow (see BattleNetAuthEndpoints); this file only exposes the
/// current-user probe and logout.
///
///   GET  /api/auth/me      -> { user, admin } | null   (JSON)
///   POST /api/auth/logout  -> { success: true }        (JSON)
/// </summary>
public static class AuthEndpoints
{
    public static IEndpointRouteBuilder MapAuthEndpoints(this IEndpointRouteBuilder app)
    {
        var group = app.MapGroup("/api/auth").WithTags("Auth");

        // Current principal. Public — returns null when anonymous.
        group.MapGet("/me", (ICurrentUser user) =>
            user.IsAuthenticated
                ? Results.Ok(new MeResponse(user.Name!, user.IsAdmin))
                : Results.Ok<MeResponse?>(null))
            .WithSummary("Get the current user")
            .WithDescription("Returns { user, admin } when logged in, or null when anonymous.");

        // Logout: destroy the session token and clear the cookie.
        group.MapPost("/logout", async (HttpContext ctx, SessionService sessions, CancellationToken ct) =>
        {
            var token = ctx.Request.Cookies[SessionService.CookieName];
            if (!string.IsNullOrEmpty(token))
            {
                await sessions.DestroySessionAsync(token, ct);
            }
            sessions.ClearCookie(ctx.Response);
            return Results.Ok(new { success = true });
        })
            .WithSummary("Log out");

        return app;
    }
}
