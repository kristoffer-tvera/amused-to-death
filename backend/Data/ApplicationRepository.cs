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
    /// matches. Returns the (id, token) pair on success.
    /// </summary>
    public async Task<ApplicationSaveResult> UpdateAsync(ApplicationSaveRequest r, CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);
        await db.ExecuteAsync(new CommandDefinition(
            """
            UPDATE applications
            SET name=@Name, server=@Server, battle_tag=@Btag, spec=@Spec, ui_screenshot_url=@Ui,
                reason=@Reason, history=@History, alts=@Alts
            WHERE id=@Id AND edit_token=@Auth
            """,
            r, cancellationToken: ct));
        return new ApplicationSaveResult(r.Id, r.Auth ?? "");
    }
}
