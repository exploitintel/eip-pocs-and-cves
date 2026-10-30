using Microsoft.Extensions.DependencyInjection;
using Microsoft.Extensions.Logging;
using OpenDeepWiki.EFCore;
using OpenDeepWiki.Services.Wiki;

namespace OpenDeepWiki.Services.Repositories;

internal sealed class WikiGenerationHeartbeat : IAsyncDisposable
{
    private readonly CancellationTokenSource _localCts;
    private readonly CancellationTokenSource _linkedCts;
    private readonly Task _loop;

    private WikiGenerationHeartbeat(
        CancellationTokenSource localCts,
        CancellationTokenSource linkedCts,
        Task loop)
    {
        _localCts = localCts;
        _linkedCts = linkedCts;
        _loop = loop;
    }

    public static WikiGenerationHeartbeat Start(
        IServiceScopeFactory scopeFactory,
        WikiGenerationWorkLease lease,
        WikiGeneratorOptions options,
        ILogger logger,
        CancellationToken cancellationToken)
    {
        var localCts = new CancellationTokenSource();
        var linkedCts = CancellationTokenSource.CreateLinkedTokenSource(localCts.Token, cancellationToken);
        return new WikiGenerationHeartbeat(
            localCts,
            linkedCts,
            RunAsync(scopeFactory, lease, options, logger, linkedCts.Token));
    }

    private static async Task RunAsync(
        IServiceScopeFactory scopeFactory,
        WikiGenerationWorkLease lease,
        WikiGeneratorOptions options,
        ILogger logger,
        CancellationToken cancellationToken)
    {
        var interval = TimeSpan.FromSeconds(options.GetGenerationHeartbeatIntervalSeconds());
        using var timer = new PeriodicTimer(interval);

        try
        {
            while (await timer.WaitForNextTickAsync(cancellationToken))
            {
                using var scope = scopeFactory.CreateScope();
                var coordinator = scope.ServiceProvider.GetRequiredService<IWikiGenerationCoordinator>();
                var context = scope.ServiceProvider.GetRequiredService<IContext>();
                await coordinator.HeartbeatAsync(context, lease, cancellationToken);
            }
        }
        catch (OperationCanceledException) when (cancellationToken.IsCancellationRequested)
        {
        }
        catch (Exception ex)
        {
            logger.LogWarning(
                ex,
                "Wiki generation heartbeat failed. RepositoryId: {RepositoryId}, OwnerId: {OwnerId}",
                lease.RepositoryId,
                lease.OwnerId);
        }
    }

    public async ValueTask DisposeAsync()
    {
        await _localCts.CancelAsync();
        try
        {
            await _loop;
        }
        catch (OperationCanceledException)
        {
        }

        _linkedCts.Dispose();
        _localCts.Dispose();
    }
}
