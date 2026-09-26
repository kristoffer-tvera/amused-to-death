using System.Text.Json.Serialization;

namespace AmusedToDeath.Api.Models;

/// <summary>
/// A recruitment application. The edit_token is NEVER serialized to the client,
/// so it is not a property on these response models.
///
/// DB columns were renamed to best-practice (battle_tag, ui_screenshot_url,
/// created_at, updated_at), but the frontend still reads the legacy JSON names
/// (btag, ui, added_date, change_date), so those are pinned with
/// [JsonPropertyName]. Dapper reads use SELECT ... AS aliases to fill the
/// PascalCase properties (see ApplicationRepository).
/// </summary>
public sealed class ApplicationSummary
{
    public int Id { get; set; }
    public string? Name { get; set; }
    public string? Server { get; set; }
    public string? Spec { get; set; }

    [JsonPropertyName("change_date")] public DateTime UpdatedAt { get; set; }
}

public sealed class ApplicationDetail
{
    public int Id { get; set; }
    public string? Name { get; set; }
    public string? Server { get; set; }

    [JsonPropertyName("btag")] public string? BattleTag { get; set; }
    public string? Spec { get; set; }
    [JsonPropertyName("ui")] public string? UiScreenshotUrl { get; set; }
    public string? Reason { get; set; }
    public string? History { get; set; }
    public string? Alts { get; set; }

    [JsonPropertyName("added_date")] public DateTime CreatedAt { get; set; }
    [JsonPropertyName("change_date")] public DateTime UpdatedAt { get; set; }
}
