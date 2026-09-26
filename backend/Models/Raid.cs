using System.Text.Json.Serialization;

namespace AmusedToDeath.Api.Models;

public sealed class Raid
{
    public int Id { get; set; }
    public string? Name { get; set; }
    public int Gold { get; set; }
    public bool Paid { get; set; }
    public string? Comment { get; set; }

    // DB columns are created_at / updated_at; frontend still reads the legacy names.
    [JsonPropertyName("added_date")] public DateTime CreatedAt { get; set; }
    [JsonPropertyName("change_date")] public DateTime UpdatedAt { get; set; }
}
