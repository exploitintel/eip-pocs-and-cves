using System.Diagnostics;
using Microsoft.EntityFrameworkCore;
using OpenDeepWiki.EFCore;
using OpenDeepWiki.Entities;
using OpenDeepWiki.Services.Repositories;
using OpenDeepWiki.Services.Wiki;

namespace OpenDeepWiki.Services.Graphify;

public class GraphifyArtifactWorker : BackgroundService
{
    private static readonly TimeSpan PollingInterval = TimeSpan.FromSeconds(30);

    private readonly IServiceScopeFactory _scopeFactory;
    private readonly ILogger<GraphifyArtifactWorker> _logger;

    public GraphifyArtifactWorker(
        IServiceScopeFactory scopeFactory,
        ILogger<GraphifyArtifactWorker> logger)
    {
        _scopeFactory = scopeFactory;
        _logger = logger;
    }

    protected override async Task ExecuteAsync(CancellationToken stoppingToken)
    {
        _logger.LogInformation("Graphify worker started. Polling interval: {PollingInterval}s",
            PollingInterval.TotalSeconds);

        try
        {
            await RecoverOrphanedProcessingAsync(stoppingToken);
        }
        catch (OperationCanceledException) when (stoppingToken.IsCancellationRequested)
        {
            return;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to recover orphaned Processing Graphify artifacts");
        }

        while (!stoppingToken.IsCancellationRequested)
        {
            try
            {
                await ProcessPendingArtifactsAsync(stoppingToken);
            }
            catch (OperationCanceledException) when (stoppingToken.IsCancellationRequested)
            {
                _logger.LogInformation("Graphify worker is shutting down");
                break;
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Graphify processing loop failed unexpectedly");
            }

            await Task.Delay(PollingInterval, stoppingToken);
        }

        _logger.LogInformation("Graphify worker stopped");
    }

    private async Task RecoverOrphanedProcessingAsync(CancellationToken cancellationToken)
    {
        using var scope = _scopeFactory.CreateScope();
        var context = scope.ServiceProvider.GetRequiredService<IContext>();

        var artifacts = await context.GraphifyArtifacts
            .Where(a => !a.IsDeleted && a.Status == GraphifyArtifactStatus.Processing)
            .ToListAsync(cancellationToken);

        foreach (var artifact in artifacts)
        {
            artifact.Status = GraphifyArtifactStatus.Pending;
            artifact.ErrorMessage = "Reset from Processing after application restart.";
            artifact.UpdateTimestamp();
        }

        if (artifacts.Count > 0)
        {
            await context.SaveChangesAsync(cancellationToken);
            _logger.LogInformation("Reset {Count} orphaned Graphify artifact(s)", artifacts.Count);
        }
    }

    private async Task ProcessPendingArtifactsAsync(CancellationToken cancellationToken)
    {
        using var scope = _scopeFactory.CreateScope();
        var context = scope.ServiceProvider.GetRequiredService<IContext>();
        var repositoryAnalyzer = scope.ServiceProvider.GetRequiredService<IRepositoryAnalyzer>();
        var runner = scope.ServiceProvider.GetRequiredService<IGraphifyCliRunner>();
        var publisher = scope.ServiceProvider.GetService<IUnderstandQuicklyPublisher>();
        var processingLogService = scope.ServiceProvider.GetService<IProcessingLogService>();

        while (!cancellationToken.IsCancellationRequested)
        {
            var artifact = await context.GraphifyArtifacts
                .Include(a => a.Repository)
                .Include(a => a.RepositoryBranch)
                .Where(a => !a.IsDeleted &&
                            a.Status == GraphifyArtifactStatus.Pending &&
                            a.Repository != null &&
                            !a.Repository.IsDeleted &&
                            a.RepositoryBranch != null &&
                            !a.RepositoryBranch.IsDeleted)
                .OrderBy(a => a.CreatedAt)
                .FirstOrDefaultAsync(cancellationToken);

            if (artifact == null)
            {
                break;
            }

            await ProcessArtifactAsync(
                artifact,
                context,
                repositoryAnalyzer,
                runner,
                publisher,
                processingLogService,
                cancellationToken);
        }
    }

    private async Task ProcessArtifactAsync(
        GraphifyArtifact artifact,
        IContext context,
        IRepositoryAnalyzer repositoryAnalyzer,
        IGraphifyCliRunner runner,
        IUnderstandQuicklyPublisher? publisher,
        IProcessingLogService? processingLogService,
        CancellationToken cancellationToken)
    {
        var repository = artifact.Repository!;
        var branch = artifact.RepositoryBranch!;
        var stopwatch = Stopwatch.StartNew();

        artifact.Status = GraphifyArtifactStatus.Processing;
        artifact.StartedAt = DateTime.UtcNow;
        artifact.CompletedAt = null;
        artifact.ErrorMessage = null;
        artifact.UpdateTimestamp();
        await context.SaveChangesAsync(cancellationToken);

        if (processingLogService != null)
        {
            await processingLogService.LogAsync(
                repository.Id,
                ProcessingStep.Graphify,
                $"Starting Graphify generation: {branch.BranchName}",
                cancellationToken: cancellationToken);
        }

        try
        {
            var workspace = await repositoryAnalyzer.PrepareWorkspaceAsync(
                repository,
                branch.BranchName,
                branch.LastCommitId,
                cancellationToken);

            var result = await runner.GenerateAsync(workspace, cancellationToken);

            artifact.Status = GraphifyArtifactStatus.Completed;
            artifact.CommitId = result.CommitId;
            artifact.OutputRoot = result.OutputRoot;
            artifact.EntryFilePath = result.EntryFilePath;
            artifact.GraphJsonPath = result.GraphJsonPath;
            artifact.ReportPath = result.ReportPath;
            artifact.CompletedAt = DateTime.UtcNow;
            artifact.ErrorMessage = null;
            artifact.UpdateTimestamp();

            stopwatch.Stop();
            _logger.LogInformation(
                "Graphify generation completed. ArtifactId: {ArtifactId}, Repository: {Org}/{Repo}, Branch: {Branch}, Duration: {Duration}ms",
                artifact.Id,
                repository.OrgName,
                repository.RepoName,
                branch.BranchName,
                stopwatch.ElapsedMilliseconds);

            if (processingLogService != null)
            {
                await processingLogService.LogAsync(
                    repository.Id,
                    ProcessingStep.Graphify,
                    $"Graphify generation complete: {branch.BranchName}, duration: {stopwatch.ElapsedMilliseconds}ms",
                    cancellationToken: cancellationToken);
            }

            // Opt-in publish to understand-quickly. The publisher is a no-op when
            // disabled; failures inside it are logged but never fail the artifact
            // (the local graph.json is already written).
            if (publisher != null && !string.IsNullOrWhiteSpace(repository.OrgName) &&
                !string.IsNullOrWhiteSpace(repository.RepoName))
            {
                try
                {
                    await publisher.PublishAsync(
                        result,
                        $"{repository.OrgName}/{repository.RepoName}",
                        cancellationToken);
                }
                catch (Exception ex)
                {
                    _logger.LogWarning(ex,
                        "UnderstandQuickly publish failed for {Org}/{Repo} — graph.json is still local.",
                        repository.OrgName, repository.RepoName);
                }
            }
        }
        catch (Exception ex)
        {
            stopwatch.Stop();
            artifact.Status = GraphifyArtifactStatus.Failed;
            artifact.ErrorMessage = TrimError(ex.Message);
            artifact.CompletedAt = DateTime.UtcNow;
            artifact.UpdateTimestamp();

            _logger.LogError(
                ex,
                "Graphify generation failed. ArtifactId: {ArtifactId}, Repository: {Org}/{Repo}, Branch: {Branch}, Duration: {Duration}ms",
                artifact.Id,
                repository.OrgName,
                repository.RepoName,
                branch.BranchName,
                stopwatch.ElapsedMilliseconds);

            if (processingLogService != null)
            {
                await processingLogService.LogAsync(
                    repository.Id,
                    ProcessingStep.Graphify,
                    $"Graphify generation failed: {branch.BranchName} - {ex.Message}",
                    cancellationToken: cancellationToken);
            }
        }

        await context.SaveChangesAsync(cancellationToken);
    }

    private static string TrimError(string value)
    {
        const int maxLength = 4000;
        return value.Length <= maxLength ? value : value[..maxLength];
    }
}
