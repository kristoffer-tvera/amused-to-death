namespace AmusedToDeath.Api.Models;

/// <summary>Response for GET /api/auth/me. Null body when not logged in.</summary>
public sealed record MeResponse(string User, bool Admin);

/// <summary>Battle.net token status for the /bnet page.</summary>
public sealed record BNetStatus(bool HasToken, int Remaining);

// ─── Character save ────────────────────────────────────────────────────────
// Accepts JSON. Non-admins cannot change discord/ownership (enforced server-side).
public sealed class CharacterSaveRequest
{
    public int Id { get; set; }
    public string Name { get; set; } = "";
    public string Realm { get; set; } = "";
    public int Class { get; set; }
    public int Main { get; set; } = -1;
    public bool RoleTank { get; set; }
    public bool RoleHeal { get; set; }
    public bool RoleDps { get; set; }
    public bool Raider { get; set; }
    public bool Vip { get; set; }
    public string? Discord { get; set; }
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
