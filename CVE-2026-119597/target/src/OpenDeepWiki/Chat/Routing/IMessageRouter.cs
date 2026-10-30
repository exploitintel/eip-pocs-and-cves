using OpenDeepWiki.Chat.Abstractions;
using OpenDeepWiki.Chat.Providers;

namespace OpenDeepWiki.Chat.Routing;

/// <summary>
/// 消息路由器接口
/// 负责将消息路由到正确的 Provider
/// </summary>
public interface IMessageRouter
{
    /// <summary>
    /// 路由入站消息
    /// </summary>
    /// <param name="message">入站消息</param>
    /// <param name="cancellationToken">取消令牌</param>
    Task RouteIncomingAsync(IChatMessage message, CancellationToken cancellationToken = default);
    
    /// <summary>
    /// 路由出站消息
    /// </summary>
    /// <param name="message">出站消息</param>
    /// <param name="targetUserId">目标用户ID</param>
    /// <param name="cancellationToken">取消令牌</param>
    Task RouteOutgoingAsync(IChatMessage message, string targetUserId, CancellationToken cancellationToken = default);
    
    /// <summary>
    /// 获取指定平台的 Provider
    /// </summary>
    /// <param name="platform">平台标识</param>
    /// <returns>Provider 实例，如果不存在则返回 null</returns>
    IMessageProvider? GetProvider(string platform);
    
    /// <summary>
    /// 获取所有已注册的 Provider
    /// </summary>
    /// <returns>所有已注册的 Provider 集合</returns>
    IEnumerable<IMessageProvider> GetAllProviders();
    
    /// <summary>
    /// 注册 Provider
    /// </summary>
    /// <param name="provider">要注册的 Provider</param>
    void RegisterProvider(IMessageProvider provider);
    
    /// <summary>
    /// 注销 Provider
    /// </summary>
    /// <param name="platform">平台标识</param>
    /// <returns>是否成功注销</returns>
    bool UnregisterProvider(string platform);
    
    /// <summary>
    /// 检查指定平台是否有已注册的 Provider
    /// </summary>
    /// <param name="platform">平台标识</param>
    /// <returns>是否存在</returns>
    bool HasProvider(string platform);
}
