using OpenDeepWiki.Chat.Abstractions;

namespace OpenDeepWiki.Chat.Sessions;

/// <summary>
/// 对话会话接口
/// 维护用户与 Agent 之间的对话上下文
/// </summary>
public interface IChatSession
{
    /// <summary>
    /// 会话唯一标识
    /// </summary>
    string SessionId { get; }
    
    /// <summary>
    /// 用户标识
    /// </summary>
    string UserId { get; }
    
    /// <summary>
    /// 平台标识
    /// </summary>
    string Platform { get; }
    
    /// <summary>
    /// 会话状态
    /// </summary>
    SessionState State { get; }
    
    /// <summary>
    /// 对话历史
    /// </summary>
    IReadOnlyList<IChatMessage> History { get; }
    
    /// <summary>
    /// 创建时间
    /// </summary>
    DateTimeOffset CreatedAt { get; }
    
    /// <summary>
    /// 最后活动时间
    /// </summary>
    DateTimeOffset LastActivityAt { get; }
    
    /// <summary>
    /// 会话元数据
    /// </summary>
    IDictionary<string, object>? Metadata { get; }
    
    /// <summary>
    /// 添加消息到历史
    /// </summary>
    /// <param name="message">要添加的消息</param>
    void AddMessage(IChatMessage message);
    
    /// <summary>
    /// 清空历史
    /// </summary>
    void ClearHistory();
    
    /// <summary>
    /// 更新状态
    /// </summary>
    /// <param name="state">新状态</param>
    void UpdateState(SessionState state);
    
    /// <summary>
    /// 更新最后活动时间
    /// </summary>
    void Touch();
}
