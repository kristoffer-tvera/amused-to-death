using System.Text.Json.Serialization;

namespace AmusedToDeath.Api.Models;

/// <summary>
/// The joined attendance row returned for a raid roster: the attendance record
/// plus the attending character's details. The JSON field names are chosen to
/// match exactly what the frontend Raid page reads:
///   attendance:  id, added_date, bosses, paid, raidId, characterId
///   character:   character_name, character_class, character_main,
///                character_discord, character_vip, character_ilvl,
///                character_role_tank/heal/dps
/// The character_* prefix disambiguates the joined columns from the attendance
/// row's own fields (both tables have e.g. an id/name).
/// </summary>
public sealed class RaidAttendanceRow
{
    public int Id { get; set; }
    public DateTime AddedDate { get; set; }
    public int Bosses { get; set; }
    public bool Paid { get; set; }
    [JsonPropertyName("raidId")] public int RaidId { get; set; }
    [JsonPropertyName("characterId")] public int CharacterId { get; set; }

    [JsonPropertyName("character_name")] public string Name { get; set; } = "";
    [JsonPropertyName("character_class")] public int Class { get; set; }
    [JsonPropertyName("character_main")] public int? Main { get; set; }
    [JsonPropertyName("character_discord")] public string? Discord { get; set; }
    [JsonPropertyName("character_vip")] public bool Vip { get; set; }
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
    public DateTime AddedDate { get; set; }
    public DateTime ChangeDate { get; set; }

    // Raid columns (joined)
    public string? Name { get; set; }
    public int Gold { get; set; }
    public string? Comment { get; set; }
}
