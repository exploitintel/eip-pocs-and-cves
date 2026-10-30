using System.Text.Json;
using Microsoft.EntityFrameworkCore;
using Microsoft.Extensions.Logging;
using Microsoft.Extensions.Options;
using OpenDeepWiki.Chat.Abstractions;
using OpenDeepWiki.EFCore;
using OpenDeepWiki.Entities;

namespace OpenDeepWiki.Chat.Queue;

/// <summary>
/// 基于数据库的消息队列实现
/// </summary>
public class DatabaseMessageQueue : IMessageQueue
{
    private readonly IContext _context;
    private readonly ILogger<DatabaseMessageQueue> _logger;
    private readonly MessageQueueOptions _options;
    private static readonly JsonSerializerOptions JsonOptions = new()
    {
        PropertyNamingPolicy = JsonNamingPolicy.CamelCase
    };

    public DatabaseMessageQueue(
        IContext context,
        ILogger<DatabaseMessageQueue> logger,
        IOptions<MessageQueueOptions> options)
    {
        _context = context;
        _logger = logger;
        _options = options.Value;
    }

    /// <inheritdoc />
    public async Task EnqueueAsync(QueuedMessage message, CancellationToken cancellationToken = default)
    {
        var entity = new ChatMessageQueue
        {
            Id = Guid.TryParse(message.Id, out var id) ? id : Guid.NewGuid(),
            SessionId = Guid.TryParse(message.SessionId, out var sessionId) ? sessionId : null,
            TargetUserId = message.TargetUserId,
            Platform = message.Message.Platform,
            MessageContent = SerializeMessage(message.Message),
            QueueType = message.Type.ToString(),
            Status = "Pending",
            RetryCount = message.RetryCount,
            ScheduledAt = message.ScheduledAt?.UtcDateTime,
            CreatedAt = DateTime.UtcNow
        };

        _context.ChatMessageQueues.Add(entity);
        await _context.SaveChangesAsync(cancellationToken);
        
        _logger.LogDebug("消息已入队: {MessageId}, 类型: {Type}", message.Id, message.Type);
    }

    /// <inheritdoc />
    public async Task<QueuedMessage?> DequeueAsync(CancellationToken cancellationToken = default)
    {
        var now = DateTime.UtcNow;
        
        // 查找下一个待处理的消息（按创建时间排序，FIFO）
        var entity = await _context.ChatMessageQueues
            .Where(q => q.Status == "Pending" && (q.ScheduledAt == null || q.ScheduledAt <= now))
            .OrderBy(q => q.CreatedAt)
            .FirstOrDefaultAsync(cancellationToken);

        if (entity == null)
            return null;

        // 标记为处理中
        entity.Status = "Processing";
        entity.UpdatedAt = DateTime.UtcNow;
        await _context.SaveChangesAsync(cancellationToken);

        var message = DeserializeMessage(entity.MessageContent, entity.Platform);
        if (message == null)
        {
            _logger.LogWarning("无法反序列化消息: {MessageId}", entity.Id);
            entity.Status = "Failed";
            entity.ErrorMessage = "消息反序列化失败";
            await _context.SaveChangesAsync(cancellationToken);
            return null;
        }

        return new QueuedMessage(
            entity.Id.ToString(),
            message,
            entity.SessionId?.ToString() ?? string.Empty,
            entity.TargetUserId,
            Enum.TryParse<QueuedMessageType>(entity.QueueType, out var type) ? type : QueuedMessageType.Incoming,
            entity.RetryCount,
            entity.ScheduledAt.HasValue ? new DateTimeOffset(entity.ScheduledAt.Value, TimeSpan.Zero) : null
        );
    }

    /// <inheritdoc />
    public async Task<int> GetQueueLengthAsync(CancellationToken cancellationToken = default)
    {
        return await _context.ChatMessageQueues
            .CountAsync(q => q.Status == "Pending" || q.Status == "Processing", cancellationToken);
    }

    /// <inheritdoc />
    public async Task CompleteAsync(string messageId, CancellationToken cancellationToken = default)
    {
        if (!Guid.TryParse(messageId, out var id))
        {
            _logger.LogWarning("无效的消息ID: {MessageId}", messageId);
            return;
        }

        var entity = await _context.ChatMessageQueues.FindAsync([id], cancellationToken);
        if (entity == null)
        {
            _logger.LogWarning("消息不存在: {MessageId}", messageId);
            return;
        }

        entity.Status = "Completed";
        entity.UpdatedAt = DateTime.UtcNow;
        await _context.SaveChangesAsync(cancellationToken);
        
        _logger.LogDebug("消息已完成: {MessageId}", messageId);
    }

    /// <inheritdoc />
    public async Task FailAsync(string messageId, string reason, CancellationToken cancellationToken = default)
    {
        if (!Guid.TryParse(messageId, out var id))
        {
            _logger.LogWarning("无效的消息ID: {MessageId}", messageId);
            return;
        }

        var entity = await _context.ChatMessageQueues.FindAsync([id], cancellationToken);
        if (entity == null)
        {
            _logger.LogWarning("消息不存在: {MessageId}", messageId);
            return;
        }

        entity.RetryCount++;
        
        // 检查是否超过最大重试次数
        if (entity.RetryCount >= _options.MaxRetryCount)
        {
            // 移入死信队列
            entity.Status = "DeadLetter";
            entity.ErrorMessage = reason;
            _logger.LogWarning("消息已移入死信队列: {MessageId}, 原因: {Reason}", messageId, reason);
        }
        else
        {
            entity.Status = "Failed";
            entity.ErrorMessage = reason;
            _logger.LogWarning("消息处理失败: {MessageId}, 重试次数: {RetryCount}, 原因: {Reason}", 
                messageId, entity.RetryCount, reason);
        }
        
        entity.UpdatedAt = DateTime.UtcNow;
        await _context.SaveChangesAsync(cancellationToken);
    }

    /// <inheritdoc />
    public async Task RetryAsync(string messageId, int delaySeconds = 30, CancellationToken cancellationToken = default)
    {
        if (!Guid.TryParse(messageId, out var id))
        {
            _logger.LogWarning("无效的消息ID: {MessageId}", messageId);
            return;
        }

        var entity = await _context.ChatMessageQueues.FindAsync([id], cancellationToken);
        if (entity == null)
        {
            _logger.LogWarning("消息不存在: {MessageId}", messageId);
            return;
        }

        entity.RetryCount++;
        
        // 检查是否超过最大重试次数
        if (entity.RetryCount >= _options.MaxRetryCount)
        {
            entity.Status = "DeadLetter";
            entity.ErrorMessage = "超过最大重试次数";
            _logger.LogWarning("消息已移入死信队列: {MessageId}, 超过最大重试次数", messageId);
        }
        else
        {
            entity.Status = "Pending";
            entity.QueueType = QueuedMessageType.Retry.ToString();
            entity.ScheduledAt = DateTime.UtcNow.AddSeconds(delaySeconds);
            _logger.LogDebug("消息已加入重试队列: {MessageId}, 延迟: {Delay}秒", messageId, delaySeconds);
        }
        
        entity.UpdatedAt = DateTime.UtcNow;
        await _context.SaveChangesAsync(cancellationToken);
    }

    /// <inheritdoc />
    public async Task<int> GetDeadLetterCountAsync(CancellationToken cancellationToken = default)
    {
        return await _context.ChatMessageQueues
            .CountAsync(q => q.Status == "DeadLetter", cancellationToken);
    }

    /// <inheritdoc />
    public async Task<IReadOnlyList<DeadLetterMessage>> GetDeadLetterMessagesAsync(
        int skip = 0, 
        int take = 100, 
        CancellationToken cancellationToken = default)
    {
        var entities = await _context.ChatMessageQueues
            .Where(q => q.Status == "DeadLetter")
            .OrderByDescending(q => q.UpdatedAt)
            .Skip(skip)
            .Take(take)
            .ToListAsync(cancellationToken);

        var result = new List<DeadLetterMessage>();
        foreach (var entity in entities)
        {
            var message = DeserializeMessage(entity.MessageContent, entity.Platform);
            if (message != null)
            {
                result.Add(new DeadLetterMessage(
                    entity.Id.ToString(),
                    message,
                    entity.SessionId?.ToString() ?? string.Empty,
                    entity.TargetUserId,
                    Enum.TryParse<QueuedMessageType>(entity.QueueType, out var type) 
                        ? type : QueuedMessageType.Incoming,
                    entity.RetryCount,
                    entity.ErrorMessage,
                    entity.UpdatedAt.HasValue 
                        ? new DateTimeOffset(entity.UpdatedAt.Value, TimeSpan.Zero) 
                        : DateTimeOffset.UtcNow,
                    new DateTimeOffset(entity.CreatedAt, TimeSpan.Zero)
                ));
            }
        }

        return result;
    }

    /// <inheritdoc />
    public async Task<bool> ReprocessDeadLetterAsync(string messageId, CancellationToken cancellationToken = default)
    {
        if (!Guid.TryParse(messageId, out var id))
        {
            _logger.LogWarning("无效的消息ID: {MessageId}", messageId);
            return false;
        }

        var entity = await _context.ChatMessageQueues.FindAsync([id], cancellationToken);
        if (entity == null || entity.Status != "DeadLetter")
        {
            _logger.LogWarning("死信消息不存在: {MessageId}", messageId);
            return false;
        }

        // 重置状态，重新入队
        entity.Status = "Pending";
        entity.RetryCount = 0;
        entity.ErrorMessage = null;
        entity.ScheduledAt = null;
        entity.UpdatedAt = DateTime.UtcNow;
        
        await _context.SaveChangesAsync(cancellationToken);
        _logger.LogInformation("死信消息已重新入队: {MessageId}", messageId);
        
        return true;
    }

    /// <inheritdoc />
    public async Task<bool> DeleteDeadLetterAsync(string messageId, CancellationToken cancellationToken = default)
    {
        if (!Guid.TryParse(messageId, out var id))
        {
            _logger.LogWarning("无效的消息ID: {MessageId}", messageId);
            return false;
        }

        var entity = await _context.ChatMessageQueues.FindAsync([id], cancellationToken);
        if (entity == null || entity.Status != "DeadLetter")
        {
            _logger.LogWarning("死信消息不存在: {MessageId}", messageId);
            return false;
        }

        _context.ChatMessageQueues.Remove(entity);
        await _context.SaveChangesAsync(cancellationToken);
        _logger.LogInformation("死信消息已删除: {MessageId}", messageId);
        
        return true;
    }

    /// <inheritdoc />
    public async Task<int> ClearDeadLetterQueueAsync(CancellationToken cancellationToken = default)
    {
        var deadLetters = await _context.ChatMessageQueues
            .Where(q => q.Status == "DeadLetter")
            .ToListAsync(cancellationToken);

        var count = deadLetters.Count;
        if (count > 0)
        {
            _context.ChatMessageQueues.RemoveRange(deadLetters);
            await _context.SaveChangesAsync(cancellationToken);
            _logger.LogInformation("已清空死信队列，删除 {Count} 条消息", count);
        }

        return count;
    }

    /// <inheritdoc />
    public async Task MoveToDeadLetterAsync(string messageId, string reason, CancellationToken cancellationToken = default)
    {
        if (!Guid.TryParse(messageId, out var id))
        {
            _logger.LogWarning("无效的消息ID: {MessageId}", messageId);
            return;
        }

        var entity = await _context.ChatMessageQueues.FindAsync([id], cancellationToken);
        if (entity == null)
        {
            _logger.LogWarning("消息不存在: {MessageId}", messageId);
            return;
        }

        entity.Status = "DeadLetter";
        entity.ErrorMessage = reason;
        entity.UpdatedAt = DateTime.UtcNow;
        
        await _context.SaveChangesAsync(cancellationToken);
        _logger.LogWarning("消息已移入死信队列: {MessageId}, 原因: {Reason}", messageId, reason);
    }

    private static string SerializeMessage(IChatMessage message)
    {
        var dto = new ChatMessageDto
        {
            MessageId = message.MessageId,
            SenderId = message.SenderId,
            ReceiverId = message.ReceiverId,
            Content = message.Content,
            MessageType = message.MessageType.ToString(),
            Platform = message.Platform,
            Timestamp = message.Timestamp,
            Metadata = message.Metadata
        };
        return JsonSerializer.Serialize(dto, JsonOptions);
    }

    private static IChatMessage? DeserializeMessage(string json, string platform)
    {
        try
        {
            var dto = JsonSerializer.Deserialize<ChatMessageDto>(json, JsonOptions);
            if (dto == null) return null;

            return new ChatMessage
            {
                MessageId = dto.MessageId,
                SenderId = dto.SenderId,
                ReceiverId = dto.ReceiverId,
                Content = dto.Content,
                MessageType = Enum.TryParse<ChatMessageType>(dto.MessageType, out var type) 
                    ? type : ChatMessageType.Text,
                Platform = dto.Platform ?? platform,
                Timestamp = dto.Timestamp,
                Metadata = dto.Metadata
            };
        }
        catch
        {
            return null;
        }
    }

    private class ChatMessageDto
    {
        public string MessageId { get; set; } = string.Empty;
        public string SenderId { get; set; } = string.Empty;
        public string? ReceiverId { get; set; }
        public string Content { get; set; } = string.Empty;
        public string MessageType { get; set; } = "Text";
        public string? Platform { get; set; }
        public DateTimeOffset Timestamp { get; set; }
        public IDictionary<string, object>? Metadata { get; set; }
    }
}
