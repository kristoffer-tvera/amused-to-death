namespace AmusedToDeath.Api.Models;

/// <summary>
/// A recruitment application. The <c>auth</c> token is NEVER serialized to the
/// client (matches the legacy behaviour of unset($row['auth'])), so it is not a
/// property on the response models below.
/// </summary>
public sealed class ApplicationSummary
{
    public int Id { get; set; }
    public string? Name { get; set; }
    public string? Server { get; set; }
    public string? Spec { get; set; }
    public DateTime ChangeDate { get; set; }
}

public sealed class ApplicationDetail
{
    public int Id { get; set; }
    public string? Name { get; set; }
    public string? Server { get; set; }
    public string? Btag { get; set; }
    public string? Spec { get; set; }
    public string? Ui { get; set; }
    public string? Reason { get; set; }
    public string? History { get; set; }
    public string? Alts { get; set; }
    public DateTime AddedDate { get; set; }
    public DateTime ChangeDate { get; set; }
}
