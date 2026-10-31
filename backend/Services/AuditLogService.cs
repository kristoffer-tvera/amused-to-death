using System.Diagnostics;
using AmusedToDeath.Api.Security;

namespace AmusedToDeath.Api.Services;

public sealed class AuditLogService(ILogger<AuditLogService> logger)
{
    public void Record(
        string action,
        ICurrentUser actor,
        string entityType,
        string? entityId = null,
        string? details = null
    ) =>
        Record(
            action,
            actor.IsAuthenticated ? "guild_member" : "anonymous",
            actor.Name,
            actor.OwnerId,
            actor.IsAdmin,
            entityType,
            entityId,
            details
        );

    public void Record(
        string action,
        string actorType,
        string? actorName,
        string? actorId,
        bool isAdmin,
        string entityType,
        string? entityId,
        string? details = null
    )
    {
        logger.LogInformation(
            "Audit {Action} actor_type={ActorType} actor={ActorName} actor_id={ActorId} admin={IsAdmin} entity_type={EntityType} entity_id={EntityId} details={Details} trace_id={TraceId}",
            action,
            actorType,
            actorName,
            actorId,
            isAdmin,
            entityType,
            entityId,
            details,
            Activity.Current?.TraceId.ToString()
        );
    }
}
