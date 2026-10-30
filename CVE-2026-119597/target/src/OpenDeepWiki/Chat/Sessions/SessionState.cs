namespace OpenDeepWiki.Chat.Sessions;

/// <summary>
/// 会话状态枚举
/// </summary>
public enum SessionState
{
    /// <summary>
    /// 活跃状态，可以接收和处理消息
    /// </summary>
    Active,
    
    /// <summary>
    /// 处理中，正在执行 Agent
    /// </summary>
    Processing,
    
    /// <summary>
    /// 等待中，等待用户响应
    /// </summary>
    Waiting,
    
    /// <summary>
    /// 已过期，超过配置的过期时间
    /// </summary>
    Expired,
    
    /// <summary>
    /// 已关闭，会话已结束
    /// </summary>
    Closed
}
