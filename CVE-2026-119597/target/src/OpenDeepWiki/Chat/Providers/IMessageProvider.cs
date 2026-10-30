using Microsoft.AspNetCore.Http;
using OpenDeepWiki.Chat.Abstractions;

namespace OpenDeepWiki.Chat.Providers;

/// <summary>
/// 消息提供者接口
/// </summary>
public interface IMessageProvider
{
    /// <summary>
    /// 平台标识符
    /// </summary>
    string PlatformId { get; }
    
    /// <summary>
    /// 平台显示名称
    /// </summary>
    string DisplayName { get; }
    
    /// <summary>
    /// 是否已启用
    /// </summary>
    bool IsEnabled { get; }
    
    /// <summary>
    /// 初始化 Provider
    /// </summary>
    Task InitializeAsync(CancellationToken cancellationToken = default);
    
    /// <summary>
    /// 解析平台原始消息为统一格式
    /// </summary>
    Task<IChatMessage?> ParseMessageAsync(string rawMessage, CancellationToken cancellationToken = default);
    
    /// <summary>
    /// 发送消息到平台
    /// </summary>
    Task<SendResult> SendMessageAsync(IChatMessage message, string targetUserId, CancellationToken cancellationToken = default);
    
    /// <summary>
    /// 批量发送消息
    /// </summary>
    Task<IEnumerable<SendResult>> SendMessagesAsync(IEnumerable<IChatMessage> messages, string targetUserId, CancellationToken cancellationToken = default);
    
    /// <summary>
    /// 验证 Webhook 请求
    /// </summary>
    Task<WebhookValidationResult> ValidateWebhookAsync(HttpRequest request, CancellationToken cancellationToken = default);
    
    /// <summary>
    /// 关闭 Provider
    /// </summary>
    Task ShutdownAsync(CancellationToken cancellationToken = default);
}
