using System.Text;
using System.Text.RegularExpressions;

namespace AmusedToDeath.Api.Security;

/// <summary>
/// Reproduces the legacy PHP input treatment: every scalar string read from a
/// request was passed through htmlspecialchars(strip_tags($value)). Existing
/// rows in the database were stored that way, so we keep the same treatment to
/// stay byte-compatible with historical data and the frontend's expectations.
///
///   strip_tags        -> remove anything that looks like an HTML/PHP tag
///   htmlspecialchars  -> encode & < > " ' as entities (ENT_QUOTES default in PHP 8.1+)
/// </summary>
public static partial class InputSanitizer
{
    [GeneratedRegex("<[^>]*>", RegexOptions.Singleline)]
    private static partial Regex TagRegex();

    public static string Clean(string? value)
    {
        if (string.IsNullOrEmpty(value))
        {
            return string.Empty;
        }

        var stripped = TagRegex().Replace(value, string.Empty);

        var sb = new StringBuilder(stripped.Length);
        foreach (var c in stripped)
        {
            switch (c)
            {
                case '&': sb.Append("&amp;"); break;
                case '<': sb.Append("&lt;"); break;
                case '>': sb.Append("&gt;"); break;
                case '"': sb.Append("&quot;"); break;
                case '\'': sb.Append("&#039;"); break;
                default: sb.Append(c); break;
            }
        }

        return sb.ToString();
    }
}
