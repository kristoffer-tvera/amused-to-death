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

    /// <summary>Public list — no secrets (id, name, server, spec, change_date).</summary>
    public async Task<IReadOnlyList<ApplicationSummary>> ListAsync(CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);
        var rows = await db.QueryAsync<ApplicationSummary>(new CommandDefinition(
            "SELECT id, name, server, spec, change_date FROM applications", cancellationToken: ct));
        return rows.AsList();
    }

    /// <summary>
    /// Fetches one application. When <paramref name="auth"/> is provided the
    /// applicant's token must match (self-service access); when null the caller
    /// is treated as admin (unrestricted). The auth token is never selected into
    /// the result model, so it cannot leak to the client.
    /// </summary>
    public async Task<ApplicationDetail?> GetAsync(int id, string? auth, CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);
        const string cols = "id, name, server, btag, spec, ui, reason, history, alts, added_date, change_date";
        if (auth is not null)
        {
            return await db.QuerySingleOrDefaultAsync<ApplicationDetail>(new CommandDefinition(
                $"SELECT {cols} FROM applications WHERE id = @id AND auth = @auth",
                new { id, auth }, cancellationToken: ct));
        }

        return await db.QuerySingleOrDefaultAsync<ApplicationDetail>(new CommandDefinition(
            $"SELECT {cols} FROM applications WHERE id = @id",
            new { id }, cancellationToken: ct));
    }

    /// <summary>
    /// Inserts a new application, generating a fresh auth token (18 hex chars,
    /// matching the legacy bin2hex(random_bytes(9))). Returns id + token.
    /// </summary>
    public async Task<ApplicationSaveResult> InsertAsync(ApplicationSaveRequest r, CancellationToken ct = default)
    {
        var auth = Convert.ToHexString(RandomNumberGenerator.GetBytes(9)).ToLowerInvariant();
        await using var db = await _connections.OpenConnectionAsync(ct);
        var id = await db.ExecuteScalarAsync<int>(new CommandDefinition(
            """
            INSERT INTO applications (name, auth, server, btag, spec, ui, reason, history, alts)
            VALUES (@Name, @auth, @Server, @Btag, @Spec, @Ui, @Reason, @History, @Alts)
            RETURNING id
            """,
            new { r.Name, auth, r.Server, r.Btag, r.Spec, r.Ui, r.Reason, r.History, r.Alts },
            cancellationToken: ct));
        return new ApplicationSaveResult(id, auth);
    }

    /// <summary>
    /// Updates an existing application, but only if the supplied auth token
    /// matches. Returns the (id, auth) pair on success.
    /// </summary>
    public async Task<ApplicationSaveResult> UpdateAsync(ApplicationSaveRequest r, CancellationToken ct = default)
    {
        await using var db = await _connections.OpenConnectionAsync(ct);
        await db.ExecuteAsync(new CommandDefinition(
            """
            UPDATE applications
            SET name=@Name, server=@Server, btag=@Btag, spec=@Spec, ui=@Ui,
                reason=@Reason, history=@History, alts=@Alts
            WHERE id=@Id AND auth=@Auth
            """,
            r, cancellationToken: ct));
        return new ApplicationSaveResult(r.Id, r.Auth ?? "");
    }
}
