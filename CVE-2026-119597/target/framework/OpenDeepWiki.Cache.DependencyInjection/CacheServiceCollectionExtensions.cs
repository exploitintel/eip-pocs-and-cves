using Microsoft.Extensions.DependencyInjection;
using OpenDeepWiki.Cache.Abstractions;
using OpenDeepWiki.Cache.Memory;

namespace OpenDeepWiki.Cache.DependencyInjection;

public static class CacheServiceCollectionExtensions
{
    public static IServiceCollection AddOpenDeepWikiCache(this IServiceCollection services)
    {
        services.AddMemoryCache();
        services.AddSingleton<ICacheFactory, MemoryCacheFactory>();
        services.AddSingleton<ICache>(sp => sp.GetRequiredService<ICacheFactory>().Create());
        return services;
    }
}
