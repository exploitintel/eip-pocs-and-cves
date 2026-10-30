using OpenDeepWiki.Chat.Abstractions;

namespace OpenDeepWiki.Chat.Queue;

/// <summary>
/// 队列消息记录类型
/// </summary>
/// <param name="Id">消息唯一标识</param>
/// <param name="Message">聊天消息</param>
/// <param name="SessionId">关联的会话ID</param>
/// <param name="TargetUserId">目标用户ID</param>
/// <param name="Type">队列消息类型</param>
/// <param name="RetryCount">重试次数</param>
/// <param name="ScheduledAt">计划执行时间</param>
public record QueuedMessage(
    string Id,
    IChatMessage Message,
    string SessionId,
    string TargetUserId,
    QueuedMessageType Type,
    int RetryCount = 0,
    DateTimeOffset? ScheduledAt = null
);
