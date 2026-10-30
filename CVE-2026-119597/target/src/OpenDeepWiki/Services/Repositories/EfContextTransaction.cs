using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Storage;
using OpenDeepWiki.EFCore;

namespace OpenDeepWiki.Services.Repositories;

internal static class EfContextTransaction
{
    public static async Task<IDbContextTransaction?> BeginIfSupportedAsync(
        IContext context,
        CancellationToken cancellationToken)
    {
        if (context is not DbContext dbContext)
        {
            return null;
        }

        if (string.Equals(dbContext.Database.ProviderName, "Microsoft.EntityFrameworkCore.InMemory", StringComparison.Ordinal))
        {
            return null;
        }

        return await dbContext.Database.BeginTransactionAsync(cancellationToken);
    }

    public static void ClearPendingChanges(IContext context)
    {
        if (context is DbContext dbContext)
        {
            dbContext.ChangeTracker.Clear();
        }
    }
}
