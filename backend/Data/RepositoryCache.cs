using System.Collections.Concurrent;
using Microsoft.Extensions.Caching.Memory;

namespace AmusedToDeath.Api.Data;

/// <summary>
/// The set of logical tables the repositories read from and write to. Used as
/// cache "regions": a cached read is tagged with the table(s) it depends on, and
/// a write invalidates every cached read tagged with the written table.
/// </summary>
public enum CacheTable
{
    Characters,
    Raids,
    Attendance,
    Applications,
}

/// <summary>
/// A thin read-through cache in front of the repositories, built on the premise
/// that this application is the sole writer to the database: any row we do not
/// change does not change, so a query with the same parameters is safe to serve
/// from memory until we ourselves write to a table it depends on.
///
/// Entries live for 24 hours (a safety net against drift and unbounded growth),
/// but the primary invalidation mechanism is generation-based and immediate: each
/// table carries a monotonically increasing generation counter that is folded
/// into every cache key. Writing to a table bumps its counter, which orphans all
/// previously cached entries for that table at once — no key bookkeeping, and no
/// risk of a stale entry surviving a write. Orphaned entries simply expire.
///
/// Cross-table reads (e.g. the attendance roster embeds character columns) are
/// tagged with every table they touch, so a write to ANY of those tables
/// invalidates the read. Writers must declare every table they mutate.
///
/// Registered as a singleton; <see cref="IMemoryCache"/> is thread-safe and the
/// generation counters are an atomic <see cref="ConcurrentDictionary{TKey,TValue}"/>.
/// </summary>
public sealed class RepositoryCache
{
    private static readonly TimeSpan Ttl = TimeSpan.FromHours(24);

    private readonly IMemoryCache _cache;
    private readonly ConcurrentDictionary<CacheTable, long> _generations = new();

    public RepositoryCache(IMemoryCache cache)
    {
        _cache = cache;
    }

    /// <summary>
    /// Serves <paramref name="key"/> from cache, or runs <paramref name="factory"/>
    /// and caches its result for 24 hours. The entry is invalidated when any of the
    /// tables in <paramref name="tables"/> is written. <paramref name="key"/> must
    /// uniquely capture the query AND its parameters.
    /// </summary>
    public async Task<T> GetOrCreateAsync<T>(
        string key,
        IReadOnlyCollection<CacheTable> tables,
        Func<Task<T>> factory)
    {
        var fullKey = BuildKey(key, tables);
        if (_cache.TryGetValue(fullKey, out T? cached))
        {
            return cached!;
        }

        var value = await factory();

        // Null results are cached too (e.g. "no such id"): a missing row won't
        // appear until we insert it, and an insert invalidates the table.
        _cache.Set(fullKey, value, new MemoryCacheEntryOptions
        {
            AbsoluteExpirationRelativeToNow = Ttl,
        });
        return value;
    }

    /// <summary>
    /// Invalidates every cached read tagged with any of the given tables by
    /// bumping those tables' generation counters. Call this AFTER a successful
    /// write, listing every table the write touched.
    /// </summary>
    public void Invalidate(params CacheTable[] tables)
    {
        foreach (var table in tables)
        {
            _generations.AddOrUpdate(table, 1, (_, current) => current + 1);
        }
    }

    private string BuildKey(string key, IReadOnlyCollection<CacheTable> tables)
    {
        // Fold the current generation of each dependent table into the key so a
        // write (which bumps a generation) makes previously cached keys
        // unreachable. Tables are ordered for a stable key regardless of caller
        // argument order.
        var generations = tables
            .OrderBy(t => t)
            .Select(t => $"{t}={_generations.GetValueOrDefault(t, 0)}");
        return $"repo:{string.Join(",", generations)}:{key}";
    }
}
