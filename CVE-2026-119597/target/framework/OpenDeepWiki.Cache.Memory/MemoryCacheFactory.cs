using Microsoft.Extensions.Caching.Memory;
using OpenDeepWiki.Cache.Abstractions;

namespace OpenDeepWiki.Cache.Memory;

public sealed class MemoryCacheFactory(IMemoryCache cache) : ICacheFactory
{
    private readonly IMemoryCache _cache = cache;

    public ICache Create(string? name = null)
    {
        return new MemoryCacheAdapter(_cache);
    }
}
