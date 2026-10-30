namespace OpenDeepWiki.Chat.Sessions;

/// <summary>
/// 会话管理器接口
/// 负责会话的创建、查找、更新和清理
/// </summary>
public interface ISessionManager
{
    /// <summary>
    /// 获取或创建会话
    /// 如果指定用户和平台的会话已存在，返回现有会话；否则创建新会话
    /// </summary>
    /// <param name="userId">用户标识</param>
    /// <param name="platform">平台标识</param>
    /// <param name="cancellationToken">取消令牌</param>
    /// <returns>会话实例</returns>
    Task<IChatSession> GetOrCreateSessionAsync(
        string userId, 
        string platform, 
        CancellationToken cancellationToken = default);
    
    /// <summary>
    /// 根据会话ID获取会话
    /// </summary>
    /// <param name="sessionId">会话ID</param>
    /// <param name="cancellationToken">取消令牌</param>
    /// <returns>会话实例，如果不存在返回 null</returns>
    Task<IChatSession?> GetSessionAsync(
        string sessionId, 
        CancellationToken cancellationToken = default);
    
    /// <summary>
    /// 更新会话到持久化存储
    /// </summary>
    /// <param name="session">会话实例</param>
    /// <param name="cancellationToken">取消令牌</param>
    Task UpdateSessionAsync(
        IChatSession session, 
        CancellationToken cancellationToken = default);
    
    /// <summary>
    /// 关闭会话
    /// </summary>
    /// <param name="sessionId">会话ID</param>
    /// <param name="cancellationToken">取消令牌</param>
    Task CloseSessionAsync(
        string sessionId, 
        CancellationToken cancellationToken = default);
    
    /// <summary>
    /// 清理过期会话
    /// </summary>
    /// <param name="cancellationToken">取消令牌</param>
    Task CleanupExpiredSessionsAsync(CancellationToken cancellationToken = default);
}
