using System.Net;
using Microsoft.Extensions.Options;

namespace AmusedToDeath.Api.Configuration;

public sealed class AppOptionsValidator : IValidateOptions<AppOptions>
{
    private static readonly HashSet<string> SupportedBattleNetRegions = new(
        StringComparer.OrdinalIgnoreCase
    )
    {
        "eu",
        "us",
        "kr",
        "tw",
        "cn",
    };

    public ValidateOptionsResult Validate(string? name, AppOptions options)
    {
        var failures = new List<string>();

        if (
            !IsHttpUrl(
                options.FrontendBaseUrl,
                originOnly: false,
                allowQuery: false,
                allowFragment: false
            )
        )
        {
            failures.Add(
                "App:FrontendBaseUrl must be an absolute HTTP or HTTPS URL without a query or fragment."
            );
        }

        if (options.CorsOrigins is not { Length: > 0 })
        {
            failures.Add("App:CorsOrigins must contain at least one origin.");
        }
        else
        {
            for (var index = 0; index < options.CorsOrigins.Length; index++)
            {
                if (
                    !IsHttpUrl(
                        options.CorsOrigins[index],
                        originOnly: true,
                        allowQuery: false,
                        allowFragment: false
                    )
                )
                {
                    failures.Add(
                        $"App:CorsOrigins[{index}] must be an HTTP or HTTPS origin without a path, query, or fragment."
                    );
                }
            }
        }

        if (!IPAddress.TryParse(options.TrustedProxyAddress, out _))
        {
            failures.Add("App:TrustedProxyAddress must be a valid IP address.");
        }

        if (options.BattleNet is null)
        {
            failures.Add("App:BattleNet must be configured.");
        }
        else
        {
            var battleNet = options.BattleNet;
            if (string.IsNullOrWhiteSpace(battleNet.ClientId))
            {
                failures.Add("App:BattleNet:ClientId is required.");
            }

            if (string.IsNullOrWhiteSpace(battleNet.ClientSecret))
            {
                failures.Add("App:BattleNet:ClientSecret is required.");
            }

            if (!SupportedBattleNetRegions.Contains(battleNet.Region))
            {
                failures.Add("App:BattleNet:Region must be one of eu, us, kr, tw, or cn.");
            }

            if (
                !IsHttpUrl(
                    battleNet.RedirectUri,
                    originOnly: false,
                    allowQuery: true,
                    allowFragment: false
                )
            )
            {
                failures.Add(
                    "App:BattleNet:RedirectUri must be an absolute HTTP or HTTPS URL without a fragment."
                );
            }

            if (string.IsNullOrWhiteSpace(battleNet.GuildName))
            {
                failures.Add("App:BattleNet:GuildName is required.");
            }

            if (string.IsNullOrWhiteSpace(battleNet.GuildRealmSlug))
            {
                failures.Add("App:BattleNet:GuildRealmSlug is required.");
            }

            if (battleNet.AdminMaxRank < 0)
            {
                failures.Add("App:BattleNet:AdminMaxRank must be zero or greater.");
            }
        }

        if (options.Webhooks is null)
        {
            failures.Add("App:Webhooks must be configured.");
        }
        else
        {
            ValidateWebhook(options.Webhooks.Announcement, "App:Webhooks:Announcement", failures);
            ValidateWebhook(options.Webhooks.Recruitment, "App:Webhooks:Recruitment", failures);
        }

        return failures.Count == 0
            ? ValidateOptionsResult.Success
            : ValidateOptionsResult.Fail(failures);
    }

    private static bool IsHttpUrl(
        string? value,
        bool originOnly,
        bool allowQuery,
        bool allowFragment
    )
    {
        if (
            !Uri.TryCreate(value, UriKind.Absolute, out var uri)
            || (uri.Scheme != Uri.UriSchemeHttp && uri.Scheme != Uri.UriSchemeHttps)
            || string.IsNullOrEmpty(uri.Host)
            || !string.IsNullOrEmpty(uri.UserInfo)
        )
        {
            return false;
        }

        return (!originOnly || uri.AbsolutePath == "/")
            && (allowQuery || string.IsNullOrEmpty(uri.Query))
            && (allowFragment || string.IsNullOrEmpty(uri.Fragment));
    }

    private static void ValidateWebhook(string? value, string settingName, List<string> failures)
    {
        if (string.IsNullOrEmpty(value))
        {
            return;
        }

        if (
            !Uri.TryCreate(value, UriKind.Absolute, out var uri)
            || uri.Scheme != Uri.UriSchemeHttps
            || string.IsNullOrEmpty(uri.Host)
            || !string.IsNullOrEmpty(uri.UserInfo)
        )
        {
            failures.Add($"{settingName} must be an absolute HTTPS URL when configured.");
        }
    }
}
