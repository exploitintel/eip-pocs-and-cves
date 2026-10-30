using System.Collections.Concurrent;
using Microsoft.Extensions.Caching.Memory;
using OpenDeepWiki.Cache.Abstractions;

namespace OpenDeepWiki.Cache.Memory;

public sealed class MemoryCacheAdapter(IMemoryCache cache) : ICache
{
    private readonly IMemoryCache _cache = cache;
    private static readonly ConcurrentDictionary<string, SemaphoreSlim> _locks = new();

    public Task<T?> GetAsync<T>(string key, CancellationToken cancellationToken = default)
    {
        cancellationToken.ThrowIfCancellationRequested();
        return Task.FromResult(_cache.TryGetValue(key, out T? value) ? value : default);
    }

    public Task SetAsync<T>(string key, T value, CacheEntryOptions options, CancellationToken cancellationToken = default)
    {
        cancellationToken.ThrowIfCancellationRequested();
        var entryOptions = new MemoryCacheEntryOptions();

        if (options.AbsoluteExpirationRelativeToNow.HasValue)
        {
            entryOptions.SetAbsoluteExpiration(options.AbsoluteExpirationRelativeToNow.Value);
        }

        if (options.SlidingExpiration.HasValue)
        {
            entryOptions.SetSlidingExpiration(options.SlidingExpiration.Value);
        }

        _cache.Set(key, value, entryOptions);
        return Task.CompletedTask;
    }

    public Task RemoveAsync(string key, CancellationToken cancellationToken = default)
    {
        cancellationToken.ThrowIfCancellationRequested();
        _cache.Remove(key);
        return Task.CompletedTask;
    }

    public Task<bool> ExistsAsync(string key, CancellationToken cancellationToken = default)
    {
        cancellationToken.ThrowIfCancellationRequested();
        return Task.FromResult(_cache.TryGetValue(key, out _));
    }

    public async Task<ICacheLock?> AcquireLockAsync(string key, TimeSpan timeout, CancellationToken cancellationToken = default)
    {
        cancellationToken.ThrowIfCancellationRequested();
        var semaphore = _locks.GetOrAdd(key, _ => new SemaphoreSlim(1, 1));
        var acquired = await semaphore.WaitAsync(timeout, cancellationToken);
        if (!acquired)
        {
            return null;
        }

        return new MemoryCacheLock(key, semaphore);
    }

    private sealed class MemoryCacheLock : ICacheLock
    {
        private readonly string _key;
        private readonly SemaphoreSlim _semaphore;
        private bool _disposed;

        public MemoryCacheLock(string key, SemaphoreSlim semaphore)
        {
            _key = key;
            _semaphore = semaphore;
        }

        public void Dispose()
        {
            if (_disposed) return;
            _semaphore.Release();
            _disposed = true;
        }

        public ValueTask DisposeAsync()
        {
            Dispose();
            return ValueTask.CompletedTask;
        }
    }
}
