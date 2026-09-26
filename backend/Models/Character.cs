using System.Text.Json.Serialization;

namespace AmusedToDeath.Api.Models;

/// <summary>
/// A guild character. Property names match the snake_case DB columns (Dapper
/// maps role_tank -> RoleTank etc.). The timestamp columns are created_at /
/// updated_at in the DB, but the frontend still reads added_date / change_date,
/// so those two are pinned to the legacy JSON names via [JsonPropertyName].
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

    [JsonPropertyName("added_date")] public DateTime CreatedAt { get; set; }
    [JsonPropertyName("change_date")] public DateTime UpdatedAt { get; set; }
}
