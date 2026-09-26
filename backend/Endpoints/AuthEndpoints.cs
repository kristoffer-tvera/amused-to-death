using Microsoft.Extensions.Options;
using AmusedToDeath.Api.Configuration;
using AmusedToDeath.Api.Data;
using AmusedToDeath.Api.Models;
using AmusedToDeath.Api.Security;
using AmusedToDeath.Api.Services;

namespace AmusedToDeath.Api.Endpoints;

/// <summary>
/// Authentication routes.
///
/// Discord is the identity provider for now, isolated behind DiscordOAuthService
/// so it can be swapped for Battle.net later without touching session handling.
/// A successful login establishes our own DB-backed session (auth table) and
/// sets an HttpOnly session cookie.
///
///   GET  /api/auth/me                 -> { user, admin } | null   (JSON)
///   POST /api/auth/logout             -> { success: true }        (JSON)
///   GET  /api/auth/discord/login      -> 302 to Discord authorize (browser nav)
///   GET  /api/auth/discord/callback   -> exchange code, set session, 302 home
/// </summary>
public static class AuthEndpoints
{
    private static readonly TimeSpan SessionLifetime = TimeSpan.FromDays(30);

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

        // Begin Discord OAuth (browser navigation).
        group.MapGet("/discord/login", (DiscordOAuthService discord) =>
            Results.Redirect(discord.BuildAuthorizeUrl()))
            .WithSummary("Start Discord login")
            .WithDescription("Redirects the browser to Discord's OAuth consent screen.");

        // Discord OAuth callback.
        group.MapGet("/discord/callback", async (string? code, HttpContext ctx,
            DiscordOAuthService discord, SessionService sessions, CharacterRepository characters,
            IOptions<AppOptions> options, CancellationToken ct) =>
        {
            if (string.IsNullOrEmpty(code))
            {
                return Results.Json(new { error = "Missing OAuth code" }, statusCode: StatusCodes.Status400BadRequest);
            }

            var username = await discord.ExchangeCodeForUsernameAsync(code, ct);
            if (string.IsNullOrEmpty(username))
            {
                return Results.Json(new { error = "Discord OAuth failed" }, statusCode: StatusCodes.Status401Unauthorized);
            }

            // Legacy gate: the user must already have a character row.
            if (!await characters.ExistsForDiscordAsync(username, ct))
            {
                return Results.Json(
                    new { error = "You have no characters. Have an officer make one for you." },
                    statusCode: StatusCodes.Status401Unauthorized);
            }

            // Admin now comes from guild rank (Battle.net login). Discord login
            // grants a regular session only.
            var token = await sessions.CreateSessionAsync(username, isAdmin: false, SessionLifetime, ct);
            sessions.WriteCookie(ctx.Response, token, SessionLifetime);
            return Results.Redirect(options.Value.FrontendBaseUrl + "/");
        })
            .WithSummary("Discord OAuth callback")
            .WithDescription("Exchanges the OAuth code, establishes a session, and redirects to the frontend.");

        return app;
    }
}
