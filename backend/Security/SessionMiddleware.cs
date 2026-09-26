namespace AmusedToDeath.Api.Security;

/// <summary>
/// Runs on every request (like the old bootstrap.php). Reads the session cookie,
/// resolves it against the auth table, and populates ICurrentUser for the request.
/// Anonymous requests simply leave ICurrentUser unset.
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
            var username = await sessions.ResolveUserAsync(token, context.RequestAborted);
            if (username is not null)
            {
                currentUser.Set(username, sessions.IsAdmin(username));
            }
        }

        await _next(context);
    }
}
