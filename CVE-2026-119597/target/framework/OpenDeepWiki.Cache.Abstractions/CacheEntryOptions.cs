namespace OpenDeepWiki.Cache.Abstractions;

public sealed class CacheEntryOptions
{
    public TimeSpan? AbsoluteExpirationRelativeToNow { get; set; }
    public TimeSpan? SlidingExpiration { get; set; }
}
