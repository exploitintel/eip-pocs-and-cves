namespace OpenDeepWiki.Cache.Abstractions;

public interface ICache
{
    Task<T?> GetAsync<T>(string key, CancellationToken cancellationToken = default);
    Task SetAsync<T>(string key, T value, CacheEntryOptions options, CancellationToken cancellationToken = default);
    Task RemoveAsync(string key, CancellationToken cancellationToken = default);
    Task<bool> ExistsAsync(string key, CancellationToken cancellationToken = default);
    Task<ICacheLock?> AcquireLockAsync(string key, TimeSpan timeout, CancellationToken cancellationToken = default);
}
