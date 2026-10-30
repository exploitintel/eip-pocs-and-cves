using Microsoft.EntityFrameworkCore;
using OpenDeepWiki.EFCore;
using OpenDeepWiki.Entities;

namespace OpenDeepWiki.Services.Repositories;

public interface IRepositoryFullRegenerationCleaner
{
    Task CleanAsync(IContext context, Repository repository, CancellationToken cancellationToken = default);
}

public sealed class RepositoryFullRegenerationCleaner : IRepositoryFullRegenerationCleaner
{
    public async Task CleanAsync(IContext context, Repository repository, CancellationToken cancellationToken = default)
    {
        var branches = await context.RepositoryBranches
            .Where(branch => branch.RepositoryId == repository.Id && !branch.IsDeleted)
            .ToListAsync(cancellationToken);
        var branchIds = branches.Select(branch => branch.Id).ToArray();

        var branchLanguageIds = branchIds.Length == 0
            ? new List<string>()
            : await context.BranchLanguages
                .Where(language => branchIds.Contains(language.RepositoryBranchId) && !language.IsDeleted)
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

        var oldLogs = await context.RepositoryProcessingLogs
            .Where(log => log.RepositoryId == repository.Id)
            .ToListAsync(cancellationToken);
        if (oldLogs.Count > 0)
        {
            context.RepositoryProcessingLogs.RemoveRange(oldLogs);
        }

        var unfinishedIncrementalTasks = await context.IncrementalUpdateTasks
            .Where(task => task.RepositoryId == repository.Id &&
                           (task.Status == IncrementalUpdateStatus.Pending ||
                            task.Status == IncrementalUpdateStatus.Processing))
            .ToListAsync(cancellationToken);
        if (unfinishedIncrementalTasks.Count > 0)
        {
            context.IncrementalUpdateTasks.RemoveRange(unfinishedIncrementalTasks);
        }

        foreach (var branch in branches)
        {
            branch.LastCommitId = null;
            branch.LastProcessedAt = null;
            branch.GenerationStatus = null;
            branch.LastGenerationTaskId = null;
            branch.LastGenerationError = null;
            branch.LastGenerationStartedAt = null;
            branch.LastGenerationCompletedAt = null;
            branch.UpdateTimestamp();
        }

        if (branches.Count > 0)
        {
            context.RepositoryBranches.UpdateRange(branches);
        }
    }
}
