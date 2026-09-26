using System.Security.Cryptography;
using Dapper;
using AmusedToDeath.Api.Models;

namespace AmusedToDeath.Api.Data;

public sealed class ApplicationRepository
{
    private readonly IDbConnectionFactory _connections;

    public ApplicationRepository(IDbConnectionFactory connections)
    {
        _connections = connections;
    }

    /// <summary>Public list — no secrets. updated_at maps to UpdatedAt.</summary>
    public async Task<IReadOnlyList<ApplicationSummary>> ListAsync(CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);
        var rows = await db.QueryAsync<ApplicationSummary>(new CommandDefinition(
            "SELECT id, name, server, spec, updated_at FROM applications", cancellationToken: ct));
        return rows.AsList();
    }

    /// <summary>
    /// Fetches one application. When <paramref name="editToken"/> is provided the
    /// applicant's token must match (self-service access); when null the caller
    /// is treated as admin (unrestricted). The edit_token is never selected into
    /// the result model, so it cannot leak to the client.
    /// </summary>
    public async Task<ApplicationDetail?> GetAsync(int id, string? editToken, CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);
        // battle_tag / ui_screenshot_url map to BattleTag / UiScreenshotUrl via
        // Dapper's underscore matching; created_at/updated_at likewise.
        const string cols = "id, name, server, battle_tag, spec, ui_screenshot_url, reason, history, alts, created_at, updated_at";
        if (editToken is not null)
        {
            return await db.QuerySingleOrDefaultAsync<ApplicationDetail>(new CommandDefinition(
                $"SELECT {cols} FROM applications WHERE id = @id AND edit_token = @editToken",
                new { id, editToken }, cancellationToken: ct));
        }

        return await db.QuerySingleOrDefaultAsync<ApplicationDetail>(new CommandDefinition(
            $"SELECT {cols} FROM applications WHERE id = @id",
            new { id }, cancellationToken: ct));
    }

    /// <summary>
    /// Inserts a new application, generating a fresh edit token (18 hex chars,
    /// matching the legacy bin2hex(random_bytes(9))). Returns id + token so the
    /// applicant can be handed their /app/{id}?auth={token} URL.
    /// </summary>
    public async Task<ApplicationSaveResult> InsertAsync(ApplicationSaveRequest r, CancellationToken ct = default)
    {
        var editToken = Convert.ToHexString(RandomNumberGenerator.GetBytes(9)).ToLowerInvariant();
        await using var db = await _connections.OpenConnectionAsync(ct);
        var id = await db.ExecuteScalarAsync<int>(new CommandDefinition(
            """
            INSERT INTO applications (name, edit_token, server, battle_tag, spec, ui_screenshot_url, reason, history, alts)
            VALUES (@Name, @editToken, @Server, @Btag, @Spec, @Ui, @Reason, @History, @Alts)
            RETURNING id
            """,
            new { r.Name, editToken, r.Server, r.Btag, r.Spec, r.Ui, r.Reason, r.History, r.Alts },
            cancellationToken: ct));
        return new ApplicationSaveResult(id, editToken);
    }

    /// <summary>
    /// Updates an existing application, but only if the supplied edit token
    /// matches. Returns the (id, token) pair, plus whether a row was actually
    /// changed. When the incoming fields are byte-for-byte identical to what is
    /// stored, the UPDATE is skipped entirely so the updated_at trigger does not
    /// fire and callers can suppress side effects (e.g. the Discord webhook).
    /// </summary>
    public async Task<ApplicationUpdateResult> UpdateAsync(ApplicationSaveRequest r, CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);

        // Load the current editable fields for this application (token-gated,
        // same as the write below). If nothing comes back the token is wrong or
        // the row is gone — fall through to the UPDATE, which will match nothing.
        var current = await db.QuerySingleOrDefaultAsync<EditableFields>(new CommandDefinition(
            """
            SELECT name, server, battle_tag, spec, ui_screenshot_url, reason, history, alts
            FROM applications
            WHERE id=@Id AND edit_token=@Auth
            """,
            r, cancellationToken: ct));

        if (current is not null && current.Matches(r))
        {
            // No real change — skip the write so the BEFORE UPDATE trigger never
            // bumps updated_at, and signal "unchanged" so the webhook is skipped.
            return new ApplicationUpdateResult(r.Id, r.Auth ?? "", Changed: false);
        }

        await db.ExecuteAsync(new CommandDefinition(
            """
            UPDATE applications
            SET name=@Name, server=@Server, battle_tag=@Btag, spec=@Spec, ui_screenshot_url=@Ui,
                reason=@Reason, history=@History, alts=@Alts
            WHERE id=@Id AND edit_token=@Auth
            """,
            r, cancellationToken: ct));
        return new ApplicationUpdateResult(r.Id, r.Auth ?? "", Changed: true);
    }

    /// <summary>
    /// The editable columns, loaded for no-op detection. Column names map to
    /// these properties via Dapper's underscore matching (battle_tag -> BattleTag).
    /// </summary>
    private sealed class EditableFields
    {
        public string? Name { get; set; }
        public string? Server { get; set; }
        public string? BattleTag { get; set; }
        public string? Spec { get; set; }
        public string? UiScreenshotUrl { get; set; }
        public string? Reason { get; set; }
        public string? History { get; set; }
        public string? Alts { get; set; }

        /// <summary>True when every editable field equals the incoming request.</summary>
        public bool Matches(ApplicationSaveRequest r) =>
            Same(Name, r.Name)
            && Same(Server, r.Server)
            && Same(BattleTag, r.Btag)
            && Same(Spec, r.Spec)
            && Same(UiScreenshotUrl, r.Ui)
            && Same(Reason, r.Reason)
            && Same(History, r.History)
            && Same(Alts, r.Alts);

        // Treat NULL in the DB as equivalent to an empty incoming string, since
        // the request model defaults these to "" and never sends null.
        private static bool Same(string? stored, string incoming) =>
            string.Equals(stored ?? "", incoming ?? "", StringComparison.Ordinal);
    }
}
