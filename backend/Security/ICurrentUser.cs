namespace AmusedToDeath.Api.Security;

/// <summary>
/// Represents the authenticated principal for the current request.
/// Mirrors the old current_user() / is_admin() helpers, which read from the
/// PHP session. Here the values are resolved once per request from the session
/// cookie (see SessionMiddleware) and exposed to endpoints/services.
/// </summary>
public interface ICurrentUser
{
    /// <summary>The logged-in identity (BattleTag), or null if anonymous.</summary>
    string? Name { get; }

    /// <summary>The durable Blizzard account id (OAuth sub) that owns characters.</summary>
    string? OwnerId { get; }

    /// <summary>True when the account's guild rank is within the admin threshold.</summary>
    bool IsAdmin { get; }

    bool IsAuthenticated => Name is not null;

    /// <summary>Populates the principal for this request. Called by the session middleware.</summary>
    void Set(string? name, string? ownerId, bool isAdmin);
}

public sealed class CurrentUser : ICurrentUser
{
    public string? Name { get; private set; }
    public string? OwnerId { get; private set; }
    public bool IsAdmin { get; private set; }

    public void Set(string? name, string? ownerId, bool isAdmin)
    {
        Name = name;
        OwnerId = ownerId;
        IsAdmin = isAdmin;
    }
}
