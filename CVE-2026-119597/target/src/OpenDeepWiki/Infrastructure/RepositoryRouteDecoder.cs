namespace OpenDeepWiki.Infrastructure;

public static class RepositoryRouteDecoder
{
    public static (string Owner, string Repo) DecodeOwnerAndRepo(string owner, string repo)
    {
        return (DecodeRouteSegment(owner), DecodeRouteSegment(repo));
    }

    public static string DecodeRouteSegment(string value)
    {
        if (string.IsNullOrEmpty(value) || !value.Contains('%'))
        {
            return value;
        }

        try
        {
            return Uri.UnescapeDataString(value);
        }
        catch (Exception)
        {
            return value;
        }
    }
}
