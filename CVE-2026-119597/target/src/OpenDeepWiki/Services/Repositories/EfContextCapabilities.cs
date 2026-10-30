using Microsoft.EntityFrameworkCore;
using OpenDeepWiki.EFCore;

namespace OpenDeepWiki.Services.Repositories;

internal static class EfContextCapabilities
{
    public static bool SupportsExecuteUpdate(IContext context)
    {
        return context is DbContext dbContext &&
               !string.Equals(
                   dbContext.Database.ProviderName,
                   "Microsoft.EntityFrameworkCore.InMemory",
                   StringComparison.Ordinal);
    }
}
