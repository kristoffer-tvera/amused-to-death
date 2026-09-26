using System.Text.Json.Serialization;

namespace AmusedToDeath.Api.Models;

/// <summary>The identity we get from Blizzard's /oauth/userinfo after login.</summary>
public sealed record BattleNetIdentity(string Sub, string BattleTag);

/// <summary>
/// A World of Warcraft character discovered on the user's Battle.net account,
/// enriched with the fields we need for the picker and the guild gate.
/// </summary>
public sealed class BattleNetCharacter
{
    public string Name { get; set; } = "";
    public string Realm { get; set; } = "";

    /// <summary>Realm slug as used in API paths (e.g. "stormscale").</summary>
    public string RealmSlug { get; set; } = "";

    public int Level { get; set; }

    /// <summary>WoW class id (matches the frontend's class icon mapping).</summary>
    public int ClassId { get; set; }

    /// <summary>Guild name if known (populated by the guild lookup), else null.</summary>
    public string? Guild { get; set; }

    /// <summary>True when this character's guild matches the configured guild.</summary>
    public bool InGuild { get; set; }
}

/// <summary>Response for GET /api/auth/bnet/characters — the picker payload.</summary>
public sealed class BattleNetCharacterList
{
    public string BattleTag { get; set; } = "";

    /// <summary>The inferred current level cap (highest level on the account).</summary>
    public int MaxLevel { get; set; }

    /// <summary>Max-level characters the user can choose to import.</summary>
    public IReadOnlyList<BattleNetCharacter> Characters { get; set; } = [];

    /// <summary>True if at least one max-level character is in the configured guild.</summary>
    public bool HasGuildCharacter { get; set; }
}

/// <summary>Request body for POST /api/auth/bnet/import.</summary>
public sealed class BattleNetImportRequest
{
    /// <summary>Names of the characters the user chose to import (name + realm).</summary>
    public List<BattleNetImportPick> Picks { get; set; } = [];

    /// <summary>Which pick is the main: index into <see cref="Picks"/>.</summary>
    public int MainIndex { get; set; }
}

public sealed class BattleNetImportPick
{
    public string Name { get; set; } = "";
    public string Realm { get; set; } = "";
}
