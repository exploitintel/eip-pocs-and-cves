using System.Text;
using Microsoft.Extensions.Options;
using OpenDeepWiki.Chat.Abstractions;

namespace OpenDeepWiki.Chat.Queue;

/// <summary>
/// 文本消息合并器
/// 将多条连续的短文本消息合并为一条
/// </summary>
public class TextMessageMerger : IMessageMerger
{
    private readonly MessageQueueOptions _options;

    public TextMessageMerger(IOptions<MessageQueueOptions> options)
    {
        _options = options.Value;
    }

    /// <inheritdoc />
    public MergeResult TryMerge(IReadOnlyList<IChatMessage> messages)
    {
        if (!CanMerge(messages))
        {
            return new MergeResult(false, messages);
        }

        var merged = MergeMessages(messages);
        return new MergeResult(true, [merged]);
    }

    /// <inheritdoc />
    public bool CanMerge(IReadOnlyList<IChatMessage> messages)
    {
        // 至少需要2条消息才能合并
        if (messages.Count < 2)
            return false;

        // 所有消息必须是文本类型
        if (messages.Any(m => m.MessageType != ChatMessageType.Text))
            return false;

        // 所有消息必须来自同一发送者
        var firstSenderId = messages[0].SenderId;
        if (messages.Any(m => m.SenderId != firstSenderId))
            return false;

        // 所有消息必须来自同一平台
        var firstPlatform = messages[0].Platform;
        if (messages.Any(m => m.Platform != firstPlatform))
            return false;

        // 检查总长度是否在阈值内
        var totalLength = messages.Sum(m => m.Content.Length);
        if (totalLength > _options.MergeThreshold)
            return false;

        // 检查时间窗口
        var firstTimestamp = messages[0].Timestamp;
        var lastTimestamp = messages[^1].Timestamp;
        var timeSpan = lastTimestamp - firstTimestamp;
        if (timeSpan.TotalMilliseconds > _options.MergeWindowMs)
            return false;

        return true;
    }

    private IChatMessage MergeMessages(IReadOnlyList<IChatMessage> messages)
    {
        var contentBuilder = new StringBuilder();
        
        for (var i = 0; i < messages.Count; i++)
        {
            if (i > 0)
                contentBuilder.AppendLine();
            contentBuilder.Append(messages[i].Content);
        }

        var firstMessage = messages[0];
        var lastMessage = messages[^1];

        // 合并元数据
        var mergedMetadata = MergeMetadata(messages);

        return new ChatMessage
        {
            MessageId = Guid.NewGuid().ToString(),
            SenderId = firstMessage.SenderId,
            ReceiverId = firstMessage.ReceiverId,
            Content = contentBuilder.ToString(),
            MessageType = ChatMessageType.Text,
            Platform = firstMessage.Platform,
            Timestamp = lastMessage.Timestamp,
            Metadata = mergedMetadata
        };
    }

    private static IDictionary<string, object>? MergeMetadata(IReadOnlyList<IChatMessage> messages)
    {
        var hasMetadata = messages.Any(m => m.Metadata != null && m.Metadata.Count > 0);
        if (!hasMetadata)
            return null;

        var merged = new Dictionary<string, object>
        {
            ["mergedCount"] = messages.Count,
            ["originalMessageIds"] = messages.Select(m => m.MessageId).ToList()
        };

        // 合并所有消息的元数据（后面的覆盖前面的）
        foreach (var message in messages)
        {
            if (message.Metadata == null) continue;
            foreach (var kvp in message.Metadata)
            {
                merged[kvp.Key] = kvp.Value;
            }
        }

        return merged;
    }
}
