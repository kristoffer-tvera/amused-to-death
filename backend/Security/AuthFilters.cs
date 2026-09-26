namespace AmusedToDeath.Api.Security;

/// <summary>
/// Endpoint filters for the two authorization gates: a logged-in session and an
/// admin session. On failure they short-circuit with the uniform
/// { "error": "..." } JSON body and the matching status code.
/// </summary>
public static class AuthFilters
{
    /// <summary>Requires a logged-in session (equivalent to require_auth()).</summary>
    public static RouteHandlerBuilder RequireAuth(this RouteHandlerBuilder builder) =>
        builder.AddEndpointFilter(async (ctx, next) =>
        {
            var user = ctx.HttpContext.RequestServices.GetRequiredService<ICurrentUser>();
            if (!user.IsAuthenticated)
            {
                return Results.Json(new { error = "Unauthorized" }, statusCode: StatusCodes.Status401Unauthorized);
            }
            return await next(ctx);
        });

    /// <summary>Requires an admin session (equivalent to require_admin()).</summary>
    public static RouteHandlerBuilder RequireAdmin(this RouteHandlerBuilder builder) =>
        builder.AddEndpointFilter(async (ctx, next) =>
        {
            var user = ctx.HttpContext.RequestServices.GetRequiredService<ICurrentUser>();
            if (!user.IsAdmin)
            {
                return Results.Json(new { error = "Unauthorized" }, statusCode: StatusCodes.Status401Unauthorized);
            }
            return await next(ctx);
        });
}
