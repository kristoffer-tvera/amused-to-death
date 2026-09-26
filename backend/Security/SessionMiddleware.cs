namespace AmusedToDeath.Api.Security;

/// <summary>
/// Runs on every request. Reads the session cookie, resolves it against the
/// sessions table, and populates ICurrentUser (username + stored admin flag) for
/// the request. Anonymous requests simply leave ICurrentUser unset.
/// </summary>
public sealed class SessionMiddleware
{
    private readonly RequestDelegate _next;

    public SessionMiddleware(RequestDelegate next)
    {
        _next = next;
    }

    public async Task InvokeAsync(HttpContext context, SessionService sessions, ICurrentUser currentUser)
    {
        var token = context.Request.Cookies[SessionService.CookieName];
        if (!string.IsNullOrEmpty(token))
        {
            var principal = await sessions.ResolveUserAsync(token, context.RequestAborted);
            if (principal is not null)
            {
                currentUser.Set(principal.Value.Username, principal.Value.OwnerId, principal.Value.IsAdmin);
            }
        }

        await _next(context);
    }
}
