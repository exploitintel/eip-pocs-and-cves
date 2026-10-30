using Microsoft.EntityFrameworkCore;
using OpenDeepWiki.EFCore;
using OpenDeepWiki.Entities;

namespace OpenDeepWiki.Services.Repositories;

public interface IBranchFullGenerationCleaner
{
    Task CleanAsync(IContext context, RepositoryBranch branch, CancellationToken cancellationToken = default);
}

public sealed class BranchFullGenerationCleaner : IBranchFullGenerationCleaner
{
    public async Task CleanAsync(IContext context, RepositoryBranch branch, CancellationToken cancellationToken = default)
    {
        var branchLanguageIds = await context.BranchLanguages
            .Where(language => language.RepositoryBranchId == branch.Id && !language.IsDeleted)
            .Select(language => language.Id)
            .ToListAsync(cancellationToken);

        if (branchLanguageIds.Count > 0)
        {
            var oldCatalogs = await context.DocCatalogs
                .Where(catalog => branchLanguageIds.Contains(catalog.BranchLanguageId))
                .ToListAsync(cancellationToken);

            var docFileIds = oldCatalogs
                .Where(catalog => catalog.DocFileId != null)
                .Select(catalog => catalog.DocFileId!)
                .Distinct()
                .ToArray();

            if (oldCatalogs.Count > 0)
            {
                context.DocCatalogs.RemoveRange(oldCatalogs);
            }

            if (docFileIds.Length > 0)
            {
                var oldDocFiles = await context.DocFiles
                    .Where(file => docFileIds.Contains(file.Id))
                    .ToListAsync(cancellationToken);

                if (oldDocFiles.Count > 0)
                {
                    context.DocFiles.RemoveRange(oldDocFiles);
                }
            }
        }

        var unfinishedIncrementalTasks = await context.IncrementalUpdateTasks
            .Where(task => task.BranchId == branch.Id &&
                           (task.Status == IncrementalUpdateStatus.Pending ||
                            task.Status == IncrementalUpdateStatus.Processing))
            .ToListAsync(cancellationToken);

        if (unfinishedIncrementalTasks.Count > 0)
        {
            context.IncrementalUpdateTasks.RemoveRange(unfinishedIncrementalTasks);
        }

        branch.LastCommitId = null;
        branch.LastProcessedAt = null;
        branch.GenerationStatus = BranchGenerationTaskStatus.Pending;
        branch.LastGenerationError = null;
        branch.LastGenerationStartedAt = null;
        branch.LastGenerationCompletedAt = null;
        branch.UpdateTimestamp();
        context.RepositoryBranches.Update(branch);
    }
}
