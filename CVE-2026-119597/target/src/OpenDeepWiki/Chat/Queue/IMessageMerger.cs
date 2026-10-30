using OpenDeepWiki.Chat.Abstractions;

namespace OpenDeepWiki.Chat.Queue;

/// <summary>
/// 消息合并器接口
/// 用于将多条短消息合并为一条
/// </summary>
public interface IMessageMerger
{
    /// <summary>
    /// 尝试合并消息
    /// </summary>
    /// <param name="messages">要合并的消息列表</param>
    /// <returns>合并结果，如果无法合并则返回原始消息</returns>
    MergeResult TryMerge(IReadOnlyList<IChatMessage> messages);
    
    /// <summary>
    /// 检查消息是否可以合并
    /// </summary>
    /// <param name="messages">要检查的消息列表</param>
    /// <returns>是否可以合并</returns>
    bool CanMerge(IReadOnlyList<IChatMessage> messages);
}

/// <summary>
/// 合并结果
/// </summary>
/// <param name="WasMerged">是否进行了合并</param>
/// <param name="Messages">结果消息列表</param>
public record MergeResult(bool WasMerged, IReadOnlyList<IChatMessage> Messages);
