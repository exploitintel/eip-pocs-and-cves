using Microsoft.EntityFrameworkCore;
using OpenDeepWiki.EFCore;
using OpenDeepWiki.Entities;

namespace OpenDeepWiki.Services.Chat;

/// <summary>
/// DTO for recording a request.
/// </summary>
public class RecordRequestDto
{
    public string AppId { get; set; } = string.Empty;
    public int InputTokens { get; set; }
    public int OutputTokens { get; set; }
}

/// <summary>
/// DTO for statistics response.
/// </summary>
public class AppStatisticsDto
{
    public string AppId { get; set; } = string.Empty;
    public DateTime Date { get; set; }
    public long RequestCount { get; set; }
    public long InputTokens { get; set; }
    public long OutputTokens { get; set; }
}

/// <summary>
/// DTO for aggregated statistics.
/// </summary>
public class AggregatedStatisticsDto
{
    public string AppId { get; set; } = string.Empty;
    public DateTime StartDate { get; set; }
    public DateTime EndDate { get; set; }
    public long TotalRequests { get; set; }
    public long TotalInputTokens { get; set; }
    public long TotalOutputTokens { get; set; }
    public List<AppStatisticsDto> DailyStatistics { get; set; } = new();
}


/// <summary>
/// Interface for app statistics service.
/// </summary>
public interface IAppStatisticsService
{
    /// <summary>
    /// Records a request for statistics tracking.
    /// </summary>
    Task RecordRequestAsync(RecordRequestDto dto, CancellationToken cancellationToken = default);

    /// <summary>
    /// Gets statistics for an app within a date range.
    /// </summary>
    Task<AggregatedStatisticsDto> GetStatisticsAsync(
        string appId,
        DateTime startDate,
        DateTime endDate,
        CancellationToken cancellationToken = default);

    /// <summary>
    /// Gets daily statistics for an app.
    /// </summary>
    Task<List<AppStatisticsDto>> GetDailyStatisticsAsync(
        string appId,
        DateTime startDate,
        DateTime endDate,
        CancellationToken cancellationToken = default);
}

/// <summary>
/// App statistics service implementation.
/// </summary>
public class AppStatisticsService : IAppStatisticsService
{
    private readonly IContext _context;
    private readonly ILogger<AppStatisticsService> _logger;

    public AppStatisticsService(IContext context, ILogger<AppStatisticsService> logger)
    {
        _context = context;
        _logger = logger;
    }

    /// <inheritdoc />
    public async Task RecordRequestAsync(RecordRequestDto dto, CancellationToken cancellationToken = default)
    {
        var today = DateTime.UtcNow.Date;

        // Try to find existing statistics for today
        var stats = await _context.AppStatistics
            .FirstOrDefaultAsync(s => s.AppId == dto.AppId && s.Date == today, cancellationToken);

        if (stats == null)
        {
            // Create new statistics record for today
            stats = new AppStatistics
            {
                Id = Guid.NewGuid(),
                AppId = dto.AppId,
                Date = today,
                RequestCount = 1,
                InputTokens = dto.InputTokens,
                OutputTokens = dto.OutputTokens,
                CreatedAt = DateTime.UtcNow
            };
            _context.AppStatistics.Add(stats);
        }
        else
        {
            // Update existing statistics
            stats.RequestCount++;
            stats.InputTokens += dto.InputTokens;
            stats.OutputTokens += dto.OutputTokens;
            stats.UpdatedAt = DateTime.UtcNow;
        }

        await _context.SaveChangesAsync(cancellationToken);

        _logger.LogDebug("Recorded request for app {AppId}: +{InputTokens} input, +{OutputTokens} output",
            dto.AppId, dto.InputTokens, dto.OutputTokens);
    }


    /// <inheritdoc />
    public async Task<AggregatedStatisticsDto> GetStatisticsAsync(
        string appId,
        DateTime startDate,
        DateTime endDate,
        CancellationToken cancellationToken = default)
    {
        var dailyStats = await GetDailyStatisticsAsync(appId, startDate, endDate, cancellationToken);

        return new AggregatedStatisticsDto
        {
            AppId = appId,
            StartDate = startDate,
            EndDate = endDate,
            TotalRequests = dailyStats.Sum(s => s.RequestCount),
            TotalInputTokens = dailyStats.Sum(s => s.InputTokens),
            TotalOutputTokens = dailyStats.Sum(s => s.OutputTokens),
            DailyStatistics = dailyStats
        };
    }

    /// <inheritdoc />
    public async Task<List<AppStatisticsDto>> GetDailyStatisticsAsync(
        string appId,
        DateTime startDate,
        DateTime endDate,
        CancellationToken cancellationToken = default)
    {
        var stats = await _context.AppStatistics
            .Where(s => s.AppId == appId && s.Date >= startDate.Date && s.Date <= endDate.Date)
            .OrderBy(s => s.Date)
            .Select(s => new AppStatisticsDto
            {
                AppId = s.AppId,
                Date = s.Date,
                RequestCount = s.RequestCount,
                InputTokens = s.InputTokens,
                OutputTokens = s.OutputTokens
            })
            .ToListAsync(cancellationToken);

        return stats;
    }
}
