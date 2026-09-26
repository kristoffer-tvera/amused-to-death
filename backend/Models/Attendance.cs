using System.Text.Json.Serialization;

namespace AmusedToDeath.Api.Models;

/// <summary>
/// The joined attendance row returned for a raid roster: the attendance record
/// plus the attending character's details. DB columns are snake_case
/// (character_id, raid_id, created_at), but the frontend Raid page reads
/// camelCase / prefixed JSON names, so those are pinned with [JsonPropertyName]:
///   attendance:  id, added_date, bosses, paid, raidId, characterId
///   character:   character_name, character_class, character_main,
///                character_ilvl, character_role_tank/heal/dps
/// </summary>
public sealed class RaidAttendanceRow
{
    public int Id { get; set; }
    [JsonPropertyName("added_date")] public DateTime CreatedAt { get; set; }
    public int Bosses { get; set; }
    public bool Paid { get; set; }
    [JsonPropertyName("raidId")] public int RaidId { get; set; }
    [JsonPropertyName("characterId")] public int CharacterId { get; set; }

    [JsonPropertyName("character_name")] public string Name { get; set; } = "";
    [JsonPropertyName("character_class")] public int Class { get; set; }
    [JsonPropertyName("character_main")] public int? Main { get; set; }
    [JsonPropertyName("character_ilvl")] public int Ilvl { get; set; }
    [JsonPropertyName("character_role_tank")] public bool RoleTank { get; set; }
    [JsonPropertyName("character_role_heal")] public bool RoleHeal { get; set; }
    [JsonPropertyName("character_role_dps")] public bool RoleDps { get; set; }
}

/// <summary>
/// A character's attendance joined with the raid it belongs to. Mirrors the
/// legacy attendance_for_character() projection (attendance.* + raid columns).
/// </summary>
public sealed class CharacterAttendanceRow
{
    public int Id { get; set; }
    [JsonPropertyName("characterId")] public int CharacterId { get; set; }
    [JsonPropertyName("raidId")] public int RaidId { get; set; }
    public int Bosses { get; set; }
    public bool Paid { get; set; }
    [JsonPropertyName("added_date")] public DateTime CreatedAt { get; set; }
    [JsonPropertyName("change_date")] public DateTime UpdatedAt { get; set; }

    // Raid columns (joined)
    public string? Name { get; set; }
    public int Gold { get; set; }
    public string? Comment { get; set; }
}
