namespace AmusedToDeath.Api.Configuration;

/// <summary>
/// Strongly-typed application settings, bound from the "App" configuration section.
/// Replaces the values that used to live in the PHP secrets.php file.
/// </summary>
public sealed class AppOptions
{
    public const string SectionName = "App";

    /// <summary>Absolute base URL of the frontend, used to build redirect/webhook links.</summary>
    public string FrontendBaseUrl { get; set; } = "https://amusedtodeath.eu";

    /// <summary>Origins allowed by CORS (the SPA origin(s)).</summary>
    public string[] CorsOrigins { get; set; } = [];

    public BattleNetOptions BattleNet { get; set; } = new();
    public WebhookOptions Webhooks { get; set; } = new();
}

public sealed class BattleNetOptions
{
    public string ClientId { get; set; } = "";
    public string ClientSecret { get; set; } = "";

    /// <summary>Battle.net region, e.g. "eu" -> eu.battle.net / eu.api.blizzard.com.</summary>
    public string Region { get; set; } = "eu";

    /// <summary>
    /// OAuth redirect URI registered in the Blizzard application. Where Blizzard
    /// sends the browser back after the user authorizes login.
    /// </summary>
    public string RedirectUri { get; set; } = "";

    /// <summary>
    /// The guild a character must belong to for the account to be allowed in.
    /// Compared case-insensitively against the character's guild name.
    /// </summary>
    public string GuildName { get; set; } = "Amused to Death";

    /// <summary>
    /// Highest guild-rank NUMBER that still grants admin. Blizzard ranks are
    /// 0-based with 0 = Guild Master (most authority) ascending. So a value of 3
    /// means ranks 0..3 are admins and 4+ are regular users. Tunable so the
    /// cutoff can be corrected without a redeploy.
    /// </summary>
    public int AdminMaxRank { get; set; } = 3;
}

public sealed class WebhookOptions
{
    public string Announcement { get; set; } = "";
    public string Recruitment { get; set; } = "";
}
