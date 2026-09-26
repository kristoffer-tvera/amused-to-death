namespace AmusedToDeath.Api.Models;

/// <summary>
/// A guild character. Field names serialize to snake_case (global JSON policy)
/// to match the shape the frontend already consumes. The BIT flags from the old
/// MySQL schema are proper booleans now (Postgres boolean); the frontend reads
/// them with truthy checks, so booleans are compatible.
/// </summary>
public sealed class Character
{
    public int Id { get; set; }
    public int Ilvl { get; set; }
    public int? Main { get; set; }
    public string Name { get; set; } = "";
    public int Class { get; set; }
    public string Realm { get; set; } = "";
    public bool RoleTank { get; set; }
    public bool RoleHeal { get; set; }
    public bool RoleDps { get; set; }
    public bool Hidden { get; set; }
    public bool Raider { get; set; }
    public bool Vip { get; set; }
    public string? Discord { get; set; }
    public DateTime AddedDate { get; set; }
    public DateTime ChangeDate { get; set; }
}
