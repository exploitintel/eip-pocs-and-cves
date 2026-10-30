using OpenDeepWiki.Chat.Abstractions;

namespace OpenDeepWiki.Chat.Queue;

/// <summary>
/// 死信队列消息记录类型
/// </summary>
/// <param name="Id">消息唯一标识</param>
/// <param name="Message">原始聊天消息</param>
/// <param name="SessionId">关联的会话ID</param>
/// <param name="TargetUserId">目标用户ID</param>
/// <param name="OriginalType">原始队列消息类型</param>
/// <param name="RetryCount">重试次数</param>
/// <param name="ErrorMessage">错误信息</param>
/// <param name="FailedAt">失败时间</param>
/// <param name="CreatedAt">创建时间</param>
public record DeadLetterMessage(
    string Id,
    IChatMessage Message,
    string SessionId,
    string TargetUserId,
    QueuedMessageType OriginalType,
    int RetryCount,
    string? ErrorMessage,
    DateTimeOffset FailedAt,
    DateTimeOffset CreatedAt
);
