using System.Diagnostics;
using Microsoft.EntityFrameworkCore;
using Microsoft.Extensions.DependencyInjection;
using Microsoft.Extensions.Hosting;
using Microsoft.Extensions.Logging;
using Microsoft.Extensions.Options;
using OpenDeepWiki.EFCore;
using OpenDeepWiki.Entities;
using OpenDeepWiki.Services.Repositories;
using OpenDeepWiki.Services.Wiki;

namespace OpenDeepWiki.Services.Translation;

/// <summary>
/// Background service that discovers and processes translation tasks.
/// </summary>
public class TranslationWorker : BackgroundService
{
    private static readonly TimeSpan PollingInterval = TimeSpan.FromSeconds(30);

    private readonly IServiceScopeFactory _scopeFactory;
    private readonly ILogger<TranslationWorker> _logger;

    public TranslationWorker(
        IServiceScopeFactory scopeFactory,
        ILogger<TranslationWorker> logger)
    {
        _scopeFactory = scopeFactory;
        _logger = logger;
    }

    protected override async Task ExecuteAsync(CancellationToken stoppingToken)
    {
        _logger.LogInformation("Translation worker started. Polling interval: {PollingInterval}s",
            PollingInterval.TotalSeconds);

        while (!stoppingToken.IsCancellationRequested)
        {
            try
            {
                await ProcessPendingTasksAsync(stoppingToken);
            }
            catch (OperationCanceledException) when (stoppingToken.IsCancellationRequested)
            {
                _logger.LogInformation("Translation worker is shutting down");
                break;
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Translation processing loop failed unexpectedly");
            }

            await Task.Delay(PollingInterval, stoppingToken);
        }

        _logger.LogInformation("Translation worker stopped");
    }

    private async Task ProcessPendingTasksAsync(CancellationToken stoppingToken)
    {
        using var scope = _scopeFactory.CreateScope();
        var translationService = scope.ServiceProvider.GetService<ITranslationService>();
        var context = scope.ServiceProvider.GetService<IContext>();
        var repositoryAnalyzer = scope.ServiceProvider.GetService<IRepositoryAnalyzer>();
        var wikiGenerator = scope.ServiceProvider.GetService<IWikiGenerator>();
        var processingLogService = scope.ServiceProvider.GetService<IProcessingLogService>();
        var skillMarkdownBuilder = scope.ServiceProvider.GetService<IRepositorySkillMarkdownBuilder>();
        var wikiOptions = scope.ServiceProvider.GetService<IOptions<WikiGeneratorOptions>>()?.Value;

        if (translationService == null || context == null || repositoryAnalyzer == null ||
            wikiGenerator == null || wikiOptions == null)
        {
            _logger.LogWarning("Required services not registered, skip translation processing");
            return;
        }

        await ScanAndCreateTranslationTasksAsync(context, wikiOptions, stoppingToken);

        while (!stoppingToken.IsCancellationRequested)
        {
            var task = await translationService.GetNextPendingTaskAsync(stoppingToken);
            if (task == null)
            {
                _logger.LogDebug("No pending translation tasks found");
                break;
            }

            await ProcessTaskAsync(
                task,
                translationService,
                context,
                repositoryAnalyzer,
                wikiGenerator,
                skillMarkdownBuilder,
                processingLogService,
                stoppingToken);
        }
    }

    private async Task ScanAndCreateTranslationTasksAsync(
        IContext context,
        WikiGeneratorOptions wikiOptions,
        CancellationToken stoppingToken)
    {
        var branchLanguages = await context.BranchLanguages
            .Include(bl => bl.RepositoryBranch)
            .ThenInclude(rb => rb!.Repository)
            .Where(bl => !bl.IsDeleted &&
                         bl.RepositoryBranch != null &&
                         !bl.RepositoryBranch.IsDeleted &&
                         bl.RepositoryBranch.Repository != null &&
                         !bl.RepositoryBranch.Repository.IsDeleted &&
                         bl.RepositoryBranch.Repository.Status == RepositoryStatus.Completed)
            .ToListAsync(stoppingToken);

        var createdCount = 0;

        foreach (var branchLanguage in branchLanguages)
        {
            stoppingToken.ThrowIfCancellationRequested();

            var translationLanguages = wikiOptions.GetTranslationLanguages(branchLanguage.LanguageCode);
            if (translationLanguages.Count == 0)
            {
                continue;
            }

            var repository = branchLanguage.RepositoryBranch!.Repository!;

            foreach (var targetLang in translationLanguages)
            {
                var existingLang = await context.BranchLanguages
                    .AnyAsync(l => l.RepositoryBranchId == branchLanguage.RepositoryBranchId &&
                                   l.LanguageCode == targetLang &&
                                   !l.IsDeleted, stoppingToken);

                if (existingLang)
                {
                    continue;
                }

                var existingTask = await context.TranslationTasks
                    .FirstOrDefaultAsync(t => t.RepositoryBranchId == branchLanguage.RepositoryBranchId &&
                                              t.TargetLanguageCode == targetLang &&
                                              !t.IsDeleted, stoppingToken);

                if (existingTask != null)
                {
                    if (existingTask.Status == TranslationTaskStatus.Pending ||
                        existingTask.Status == TranslationTaskStatus.Processing)
                    {
                        continue;
                    }

                    if (existingTask.Status == TranslationTaskStatus.Completed)
                    {
                        continue;
                    }

                    if (existingTask.Status == TranslationTaskStatus.Failed &&
                        existingTask.RetryCount < existingTask.MaxRetryCount)
                    {
                        existingTask.Status = TranslationTaskStatus.Pending;
                        existingTask.ErrorMessage = null;
                        createdCount++;

                        _logger.LogDebug(
                            "Translation task reset to pending. TargetLang: {TargetLang}, Repository: {Org}/{Repo}, RetryCount: {RetryCount}",
                            targetLang,
                            repository.OrgName,
                            repository.RepoName,
                            existingTask.RetryCount);
                    }

                    continue;
                }

                var task = new TranslationTask
                {
                    Id = Guid.NewGuid().ToString(),
                    RepositoryId = repository.Id,
                    RepositoryBranchId = branchLanguage.RepositoryBranchId,
                    SourceBranchLanguageId = branchLanguage.Id,
                    TargetLanguageCode = targetLang.ToLowerInvariant(),
                    Status = TranslationTaskStatus.Pending
                };

                context.TranslationTasks.Add(task);
                createdCount++;

                _logger.LogDebug(
                    "Translation task created. TargetLang: {TargetLang}, Repository: {Org}/{Repo}",
                    targetLang,
                    repository.OrgName,
                    repository.RepoName);
            }
        }

        if (createdCount > 0)
        {
            await context.SaveChangesAsync(stoppingToken);
            _logger.LogInformation("Created {Count} translation tasks from scan", createdCount);
        }
    }

    private async Task ProcessTaskAsync(
        TranslationTask task,
        ITranslationService translationService,
        IContext context,
        IRepositoryAnalyzer repositoryAnalyzer,
        IWikiGenerator wikiGenerator,
        IRepositorySkillMarkdownBuilder? skillMarkdownBuilder,
        IProcessingLogService? processingLogService,
        CancellationToken stoppingToken)
    {
        var stopwatch = Stopwatch.StartNew();

        _logger.LogInformation(
            "Starting translation task. TaskId: {TaskId}, TargetLang: {TargetLang}, RetryCount: {RetryCount}",
            task.Id, task.TargetLanguageCode, task.RetryCount);

        await translationService.MarkAsProcessingAsync(task.Id, stoppingToken);

        if (processingLogService != null)
        {
            await processingLogService.LogAsync(
                task.RepositoryId,
                ProcessingStep.Translation,
                $"寮€濮嬬炕璇戜换鍔? {task.TargetLanguageCode}",
                cancellationToken: stoppingToken);
        }

        try
        {
            var repository = await context.Repositories
                .FirstOrDefaultAsync(r => r.Id == task.RepositoryId && !r.IsDeleted, stoppingToken);

            if (repository == null)
            {
                throw new InvalidOperationException($"Repository not found: {task.RepositoryId}");
            }

            if (wikiGenerator is WikiGenerator generator)
            {
                generator.SetCurrentRepository(task.RepositoryId, $"{repository.OrgName}/{repository.RepoName}");
            }

            var branch = await context.RepositoryBranches
                .FirstOrDefaultAsync(b => b.Id == task.RepositoryBranchId && !b.IsDeleted, stoppingToken);

            if (branch == null)
            {
                throw new InvalidOperationException($"Branch not found: {task.RepositoryBranchId}");
            }

            var sourceBranchLanguage = await context.BranchLanguages
                .FirstOrDefaultAsync(l => l.Id == task.SourceBranchLanguageId && !l.IsDeleted, stoppingToken);

            if (sourceBranchLanguage == null)
            {
                throw new InvalidOperationException($"Source branch language not found: {task.SourceBranchLanguageId}");
            }

            var workspace = await repositoryAnalyzer.PrepareWorkspaceAsync(
                repository,
                branch.BranchName,
                branch.LastCommitId,
                stoppingToken);

            try
            {
                var targetBranchLanguage = await wikiGenerator.TranslateWikiAsync(
                    workspace,
                    sourceBranchLanguage,
                    task.TargetLanguageCode,
                    stoppingToken);

                if (repository.GenerateSkill && skillMarkdownBuilder is not null)
                {
                    await skillMarkdownBuilder.RefreshSkillMarkdownAsync(
                        context,
                        repository,
                        branch,
                        targetBranchLanguage,
                        stoppingToken);
                }

                await translationService.MarkAsCompletedAsync(task.Id, stoppingToken);

                stopwatch.Stop();
                _logger.LogInformation(
                    "Translation task completed. TaskId: {TaskId}, TargetLang: {TargetLang}, Duration: {Duration}ms",
                    task.Id, task.TargetLanguageCode, stopwatch.ElapsedMilliseconds);

                if (processingLogService != null)
                {
                    await processingLogService.LogAsync(
                        task.RepositoryId,
                        ProcessingStep.Translation,
                        $"缈昏瘧瀹屾垚: {task.TargetLanguageCode}锛岃€楁椂 {stopwatch.ElapsedMilliseconds}ms",
                        cancellationToken: stoppingToken);
                }
            }
            finally
            {
                await repositoryAnalyzer.CleanupWorkspaceAsync(workspace, stoppingToken);
            }
        }
        catch (OperationCanceledException) when (stoppingToken.IsCancellationRequested)
        {
            await translationService.MarkAsFailedAsync(task.Id, "Task cancelled", stoppingToken);
            throw;
        }
        catch (Exception ex)
        {
            stopwatch.Stop();
            _logger.LogError(ex,
                "Translation task failed. TaskId: {TaskId}, TargetLang: {TargetLang}, Duration: {Duration}ms",
                task.Id, task.TargetLanguageCode, stopwatch.ElapsedMilliseconds);

            await translationService.MarkAsFailedAsync(task.Id, ex.Message, stoppingToken);

            if (processingLogService != null)
            {
                await processingLogService.LogAsync(
                    task.RepositoryId,
                    ProcessingStep.Translation,
                    $"缈昏瘧澶辫触: {task.TargetLanguageCode} - {ex.Message}",
                    cancellationToken: stoppingToken);
            }
        }
    }
}
