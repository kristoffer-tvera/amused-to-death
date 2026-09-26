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

    /// <summary>Usernames (Discord for now) that are granted admin access.</summary>
    public string[] Admins { get; set; } = [];

    public DiscordOptions Discord { get; set; } = new();
    public BattleNetOptions BattleNet { get; set; } = new();
    public WebhookOptions Webhooks { get; set; } = new();
}

public sealed class DiscordOptions
{
    public string ClientId { get; set; } = "";
    public string ClientSecret { get; set; } = "";
    public string RedirectUri { get; set; } = "";
}

public sealed class BattleNetOptions
{
    public string ClientId { get; set; } = "";
    public string ClientSecret { get; set; } = "";

    /// <summary>Battle.net region, e.g. "eu" -> eu.battle.net / eu.api.blizzard.com.</summary>
    public string Region { get; set; } = "eu";
}

public sealed class WebhookOptions
{
    public string Announcement { get; set; } = "";
    public string Recruitment { get; set; } = "";
}
