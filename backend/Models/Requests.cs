namespace AmusedToDeath.Api.Models;

/// <summary>Response for GET /api/auth/me. Null body when not logged in.</summary>
public sealed record MeResponse(string User, bool Admin);

/// <summary>Battle.net token status for the /bnet page.</summary>
public sealed record BNetStatus(bool HasToken, int Remaining);

// ─── Character roles ───────────────────────────────────────────────────────
// The only user-editable character data. Everything else is Blizzard-sourced.
public sealed class CharacterRolesRequest
{
    public bool RoleTank { get; set; }
    public bool RoleHeal { get; set; }
    public bool RoleDps { get; set; }
}

/// <summary>Toggle a character's visibility (hidden true/false).</summary>
public sealed class CharacterVisibilityRequest
{
    public bool Hidden { get; set; }
}

// ─── Raid save ───────────────────────────────────────────────────────────────
public sealed class RaidSaveRequest
{
    public int Id { get; set; }
    public string Name { get; set; } = "";
    public int Gold { get; set; }
    public bool Paid { get; set; }
    public string Comment { get; set; } = "";
}

// ─── Attendance ───────────────────────────────────────────────────────────────
public sealed class AttendanceAddRequest
{
    public int Character { get; set; }
    public int Raid { get; set; }
    public int Bosses { get; set; }
}

public sealed class AttendanceUpdateRequest
{
    public int CharacterId { get; set; }
    public int RaidId { get; set; }
    public int Bosses { get; set; }
    public bool Paid { get; set; }
}

// ─── Application submit (public) ───────────────────────────────────────────────
public sealed class ApplicationSaveRequest
{
    public int Id { get; set; }
    public string? Auth { get; set; }
    public string Name { get; set; } = "";
    public string Server { get; set; } = "";
    public string Btag { get; set; } = "";
    public string Spec { get; set; } = "";
    public string Ui { get; set; } = "";
    public string Reason { get; set; } = "";
    public string History { get; set; } = "";
    public string Alts { get; set; } = "";

    /// <summary>Honeypot field. Must equal "meme" for the submit to be accepted.</summary>
    public string? Pepe { get; set; }
}

public sealed record ApplicationSaveResult(int Id, string Auth);
