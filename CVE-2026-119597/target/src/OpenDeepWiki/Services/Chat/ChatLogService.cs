using Microsoft.EntityFrameworkCore;
using OpenDeepWiki.EFCore;
using OpenDeepWiki.Entities;

namespace OpenDeepWiki.Services.Chat;

/// <summary>
/// DTO for recording a chat log.
/// </summary>
public class RecordChatLogDto
{
    public string AppId { get; set; } = string.Empty;
    public string? UserIdentifier { get; set; }
    public string Question { get; set; } = string.Empty;
    public string? AnswerSummary { get; set; }
    public int InputTokens { get; set; }
    public int OutputTokens { get; set; }
    public string? ModelUsed { get; set; }
    public string? SourceDomain { get; set; }
}

/// <summary>
/// DTO for chat log response.
/// </summary>
public class ChatLogDto
{
    public Guid Id { get; set; }
    public string AppId { get; set; } = string.Empty;
    public string? UserIdentifier { get; set; }
    public string Question { get; set; } = string.Empty;
    public string? AnswerSummary { get; set; }
    public int InputTokens { get; set; }
    public int OutputTokens { get; set; }
    public string? ModelUsed { get; set; }
    public string? SourceDomain { get; set; }
    public DateTime CreatedAt { get; set; }
}

/// <summary>
/// DTO for chat log query parameters.
/// </summary>
public class ChatLogQueryDto
{
    public string AppId { get; set; } = string.Empty;
    public DateTime? StartDate { get; set; }
    public DateTime? EndDate { get; set; }
    public string? Keyword { get; set; }
    public int Page { get; set; } = 1;
    public int PageSize { get; set; } = 20;
}


/// <summary>
/// DTO for paginated chat log response.
/// </summary>
public class PaginatedChatLogsDto
{
    public List<ChatLogDto> Items { get; set; } = new();
    public int TotalCount { get; set; }
    public int Page { get; set; }
    public int PageSize { get; set; }
    public int TotalPages => (int)Math.Ceiling((double)TotalCount / PageSize);
}

/// <summary>
/// Interface for chat log service.
/// </summary>
public interface IChatLogService
{
    /// <summary>
    /// Records a chat log entry.
    /// </summary>
    Task<ChatLogDto> RecordChatLogAsync(RecordChatLogDto dto, CancellationToken cancellationToken = default);

    /// <summary>
    /// Gets chat logs with filtering and pagination.
    /// </summary>
    Task<PaginatedChatLogsDto> GetLogsAsync(ChatLogQueryDto query, CancellationToken cancellationToken = default);

    /// <summary>
    /// Gets a single chat log by ID.
    /// </summary>
    Task<ChatLogDto?> GetLogByIdAsync(Guid id, string appId, CancellationToken cancellationToken = default);

    /// <summary>
    /// Records a diagnostic error log when an AI provider fails.
    /// </summary>
    Task LogChatErrorAsync(Guid sessionId, Exception ex, string modelUsed = "", CancellationToken cancellationToken = default);
}

/// <summary>
/// Chat log service implementation.
/// </summary>
public class ChatLogService : IChatLogService
{
    private readonly IContext _context;
    private readonly ILogger<ChatLogService> _logger;

    public ChatLogService(IContext context, ILogger<ChatLogService> logger)
    {
        _context = context;
        _logger = logger;
    }


    /// <inheritdoc />
    public async Task<ChatLogDto> RecordChatLogAsync(RecordChatLogDto dto, CancellationToken cancellationToken = default)
    {
        var log = new ChatLog
        {
            Id = Guid.NewGuid(),
            AppId = dto.AppId,
            UserIdentifier = dto.UserIdentifier,
            Question = dto.Question,
            AnswerSummary = dto.AnswerSummary,
            InputTokens = dto.InputTokens,
            OutputTokens = dto.OutputTokens,
            ModelUsed = dto.ModelUsed,
            SourceDomain = dto.SourceDomain,
            CreatedAt = DateTime.UtcNow
        };

        _context.ChatLogs.Add(log);
        await _context.SaveChangesAsync(cancellationToken);

        _logger.LogDebug("Recorded chat log for app {AppId}: {Question}", dto.AppId, dto.Question[..Math.Min(50, dto.Question.Length)]);

        return MapToDto(log);
    }

    /// <inheritdoc />
    public async Task<PaginatedChatLogsDto> GetLogsAsync(ChatLogQueryDto query, CancellationToken cancellationToken = default)
    {
        var queryable = _context.ChatLogs
            .Where(l => l.AppId == query.AppId && !l.IsDeleted);

        // Apply date filters
        if (query.StartDate.HasValue)
        {
            queryable = queryable.Where(l => l.CreatedAt >= query.StartDate.Value);
        }

        if (query.EndDate.HasValue)
        {
            queryable = queryable.Where(l => l.CreatedAt <= query.EndDate.Value);
        }

        // Apply keyword filter
        if (!string.IsNullOrWhiteSpace(query.Keyword))
        {
            var keyword = query.Keyword.ToLower();
            queryable = queryable.Where(l =>
                l.Question.ToLower().Contains(keyword) ||
                (l.AnswerSummary != null && l.AnswerSummary.ToLower().Contains(keyword)));
        }

        // Get total count
        var totalCount = await queryable.CountAsync(cancellationToken);

        // Apply pagination
        var items = await queryable
            .OrderByDescending(l => l.CreatedAt)
            .Skip((query.Page - 1) * query.PageSize)
            .Take(query.PageSize)
            .Select(l => MapToDto(l))
            .ToListAsync(cancellationToken);

        return new PaginatedChatLogsDto
        {
            Items = items,
            TotalCount = totalCount,
            Page = query.Page,
            PageSize = query.PageSize
        };
    }


    /// <inheritdoc />
    public async Task<ChatLogDto?> GetLogByIdAsync(Guid id, string appId, CancellationToken cancellationToken = default)
    {
        var log = await _context.ChatLogs
            .FirstOrDefaultAsync(l => l.Id == id && l.AppId == appId && !l.IsDeleted, cancellationToken);

        return log != null ? MapToDto(log) : null;
    }

    /// <inheritdoc />
    public async Task LogChatErrorAsync(Guid sessionId, Exception ex, string modelUsed = "", CancellationToken cancellationToken = default)
    { 
        // Log the error details in a structured way
        var category = ex.GetType().Name; 
        
        // Create a special chat log entry for this error
        var errorDto = new RecordChatLogDto
        {
           AppId = "System_Error",
           UserIdentifier = "Diagnostic",
           Question = $"AI_FAILURE_SESSION_{sessionId}",
           AnswerSummary = $"Error: {category} | Message: {ex.Message}",
           ModelUsed = modelUsed
     };
        
        await RecordChatLogAsync(errorDto, cancellationToken);

       _logger.LogError(ex, "Critical AI Provider error recorded for session {SessionId}", sessionId);
    } 

    /// <summary>
    /// Maps a ChatLog entity to a DTO.
    /// </summary>
    private static ChatLogDto MapToDto(ChatLog log)
    {
        return new ChatLogDto
        {
            Id = log.Id,
            AppId = log.AppId,
            UserIdentifier = log.UserIdentifier,
            Question = log.Question,
            AnswerSummary = log.AnswerSummary,
            InputTokens = log.InputTokens,
            OutputTokens = log.OutputTokens,
            ModelUsed = log.ModelUsed,
            SourceDomain = log.SourceDomain,
            CreatedAt = log.CreatedAt
        };
    }
}
