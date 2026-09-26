namespace AmusedToDeath.Api.Models;

public sealed class Raid
{
    public int Id { get; set; }
    public string? Name { get; set; }
    public int Gold { get; set; }
    public bool Paid { get; set; }
    public string? Comment { get; set; }
    public DateTime AddedDate { get; set; }
    public DateTime ChangeDate { get; set; }
}
