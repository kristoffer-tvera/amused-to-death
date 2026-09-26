using System.Security.Cryptography;
using Dapper;
using AmusedToDeath.Api.Models;

namespace AmusedToDeath.Api.Data;

/// <summary>
/// Application persistence with append-only revision history.
///
/// The `applications` row is the stable identity (id + edit_token) and also
/// carries a denormalized copy of the LATEST field values so existing list/detail
/// reads stay simple and fast. Every accepted change also writes an immutable
/// snapshot into `application_versions` and repoints applications.current_version_id.
///
/// Access model (unchanged for applicants):
///   * Anonymous applicant (edit_token) -> latest state only, no version history.
///   * Logged-in reviewer -> latest state plus full version list and any snapshot.
/// The version-history methods are reviewer-only by convention; the endpoints
/// gate them with RequireAuth so an applicant's token can never reach them.
/// </summary>
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
    /// Fetches one application's LATEST state. When <paramref name="editToken"/> is
    /// provided the applicant's token must match (self-service); when null the
    /// caller is treated as admin (unrestricted). The edit_token is never selected
    /// into the result model, so it cannot leak to the client.
    /// </summary>
    public async Task<ApplicationDetail?> GetAsync(int id, string? editToken, CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);
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
    /// Lists the revision history for an application, newest first. Reviewer-only —
    /// callers must have already authorized the request (endpoints use RequireAuth).
    /// </summary>
    public async Task<IReadOnlyList<ApplicationVersionSummary>> ListVersionsAsync(int applicationId, CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);
        var rows = await db.QueryAsync<ApplicationVersionSummary>(new CommandDefinition(
            """
            SELECT version_no, created_at
            FROM application_versions
            WHERE application_id = @applicationId
            ORDER BY version_no DESC
            """,
            new { applicationId }, cancellationToken: ct));
        return rows.AsList();
    }

    /// <summary>
    /// Fetches a single snapshot by version number. Reviewer-only. Returns null if
    /// the application or that version number does not exist.
    /// </summary>
    public async Task<ApplicationVersion?> GetVersionAsync(int applicationId, int versionNo, CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);
        return await db.QuerySingleOrDefaultAsync<ApplicationVersion>(new CommandDefinition(
            """
            SELECT version_no, name, server, battle_tag, spec, ui_screenshot_url, reason, history, alts, created_at
            FROM application_versions
            WHERE application_id = @applicationId AND version_no = @versionNo
            """,
            new { applicationId, versionNo }, cancellationToken: ct));
    }

    /// <summary>
    /// Inserts a new application: identity row, version 1 snapshot, and the
    /// current-version pointer, all in one transaction. Generates a fresh edit
    /// token (18 hex chars, matching legacy bin2hex(random_bytes(9))).
    /// </summary>
    public async Task<ApplicationSaveResult> InsertAsync(ApplicationSaveRequest r, CancellationToken ct = default)
    {
        var editToken = Convert.ToHexString(RandomNumberGenerator.GetBytes(9)).ToLowerInvariant();
        await using var db = await _connections.OpenConnectionAsync(ct);
        await using var tx = await db.BeginTransactionAsync(ct);

        var id = await db.ExecuteScalarAsync<int>(new CommandDefinition(
            """
            INSERT INTO applications (name, edit_token, server, battle_tag, spec, ui_screenshot_url, reason, history, alts)
            VALUES (@Name, @editToken, @Server, @Btag, @Spec, @Ui, @Reason, @History, @Alts)
            RETURNING id
            """,
            new { r.Name, editToken, r.Server, r.Btag, r.Spec, r.Ui, r.Reason, r.History, r.Alts },
            tx, cancellationToken: ct));

        var versionId = await InsertVersionAsync(db, tx, id, versionNo: 1, r, ct);

        await db.ExecuteAsync(new CommandDefinition(
            "UPDATE applications SET current_version_id = @versionId WHERE id = @id",
            new { versionId, id }, tx, cancellationToken: ct));

        await tx.CommitAsync(ct);
        return new ApplicationSaveResult(id, editToken);
    }

    /// <summary>
    /// Applies an update as a new immutable version, token-gated. If the submitted
    /// fields are identical to the latest version, nothing is written: no new
    /// snapshot, no updated_at bump, and the result reports Changed = false so the
    /// caller can skip the Discord ping. On a real change a new snapshot is
    /// inserted (version_no = latest + 1), the denormalized latest fields on the
    /// applications row are refreshed, and current_version_id is repointed — all
    /// in one transaction. The returned version numbers drive the webhook links.
    /// </summary>
    public async Task<ApplicationUpdateResult> UpdateAsync(ApplicationSaveRequest r, CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);

        // Load the latest snapshot for this application, token-gated. Joining
        // through the current-version pointer ensures we compare against exactly
        // what the applicant last saw.
        var latest = await db.QuerySingleOrDefaultAsync<LatestVersion>(new CommandDefinition(
            """
            SELECT v.version_no,
                   v.name, v.server, v.battle_tag, v.spec, v.ui_screenshot_url,
                   v.reason, v.history, v.alts
            FROM applications a
            JOIN application_versions v ON v.id = a.current_version_id
            WHERE a.id = @Id AND a.edit_token = @Auth
            """,
            r, cancellationToken: ct));

        if (latest is null)
        {
            // Bad token or missing application — nothing to change. Report a no-op
            // so no side effects fire. Version 0 signals "nothing".
            return new ApplicationUpdateResult(r.Id, r.Auth ?? "", Changed: false, NewVersionNo: 0, PreviousVersionNo: 0);
        }

        if (latest.Matches(r))
        {
            // Zero diff: no new version, no updated_at bump, no ping. Report the
            // unchanged latest version number for both slots.
            return new ApplicationUpdateResult(
                r.Id, r.Auth ?? "", Changed: false,
                NewVersionNo: latest.VersionNo, PreviousVersionNo: latest.VersionNo);
        }

        var previousVersionNo = latest.VersionNo;
        var newVersionNo = previousVersionNo + 1;

        await using var tx = await db.BeginTransactionAsync(ct);

        var versionId = await InsertVersionAsync(db, tx, r.Id, newVersionNo, r, ct);

        // Refresh the denormalized latest fields and repoint at the new snapshot.
        // Touching the row fires the updated_at trigger, which is correct here —
        // this is a genuine change. Re-check the token in the WHERE as defense.
        await db.ExecuteAsync(new CommandDefinition(
            """
            UPDATE applications
            SET name=@Name, server=@Server, battle_tag=@Btag, spec=@Spec, ui_screenshot_url=@Ui,
                reason=@Reason, history=@History, alts=@Alts, current_version_id=@versionId
            WHERE id=@Id AND edit_token=@Auth
            """,
            new { r.Name, r.Server, r.Btag, r.Spec, r.Ui, r.Reason, r.History, r.Alts, versionId, r.Id, r.Auth },
            tx, cancellationToken: ct));

        await tx.CommitAsync(ct);

        return new ApplicationUpdateResult(
            r.Id, r.Auth ?? "", Changed: true,
            NewVersionNo: newVersionNo, PreviousVersionNo: previousVersionNo);
    }

    /// <summary>Inserts one snapshot row and returns its id. Shared by insert/update.</summary>
    private static async Task<int> InsertVersionAsync(
        System.Data.Common.DbConnection db, System.Data.Common.DbTransaction tx,
        int applicationId, int versionNo, ApplicationSaveRequest r, CancellationToken ct)
    {
        return await db.ExecuteScalarAsync<int>(new CommandDefinition(
            """
            INSERT INTO application_versions
                (application_id, version_no, name, server, battle_tag, spec, ui_screenshot_url, reason, history, alts)
            VALUES
                (@applicationId, @versionNo, @Name, @Server, @Btag, @Spec, @Ui, @Reason, @History, @Alts)
            RETURNING id
            """,
            new { applicationId, versionNo, r.Name, r.Server, r.Btag, r.Spec, r.Ui, r.Reason, r.History, r.Alts },
            tx, cancellationToken: ct));
    }

    /// <summary>
    /// The latest snapshot's editable fields, loaded for no-op detection. Column
    /// names map via Dapper's underscore matching (battle_tag -> BattleTag).
    /// </summary>
    private sealed class LatestVersion
    {
        public int VersionNo { get; set; }
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
