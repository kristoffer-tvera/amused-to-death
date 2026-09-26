using System.Text;
using System.Text.Json;

namespace AmusedToDeath.Api.Services;

/// <summary>
/// Fires Discord webhook messages. Mirrors the legacy discord_webhook():
/// posts { content, allowed_mentions:{ parse:[...] } } as JSON. A blank webhook
/// URL is a no-op. Failures are swallowed (best-effort notification, like the
/// original) so a webhook outage never breaks the API call that triggered it.
/// </summary>
public sealed class DiscordWebhookService
{
    private readonly HttpClient _http;
    private readonly ILogger<DiscordWebhookService> _logger;

    public DiscordWebhookService(HttpClient http, ILogger<DiscordWebhookService> logger)
    {
        _http = http;
        _logger = logger;
    }

    public async Task SendAsync(string webhookUrl, string content, string[]? mentions = null, CancellationToken ct = default)
    {
        if (string.IsNullOrEmpty(webhookUrl))
        {
            return;
        }

        object payload = mentions is { Length: > 0 }
            ? new { content, allowed_mentions = new { parse = mentions } }
            : new { content };

        try
        {
            var json = JsonSerializer.Serialize(payload);
            using var body = new StringContent(json, Encoding.UTF8, "application/json");
            using var response = await _http.PostAsync(webhookUrl, body, ct);
            if (!response.IsSuccessStatusCode)
            {
                _logger.LogWarning("Discord webhook returned {Status}", (int)response.StatusCode);
            }
        }
        catch (Exception ex)
        {
            _logger.LogWarning(ex, "Discord webhook post failed");
        }
    }
}
