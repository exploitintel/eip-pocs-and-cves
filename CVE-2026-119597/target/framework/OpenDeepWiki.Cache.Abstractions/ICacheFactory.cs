namespace OpenDeepWiki.Cache.Abstractions;

public interface ICacheFactory
{
    ICache Create(string? name = null);
}
