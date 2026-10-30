using Microsoft.AspNetCore.Http;
using Microsoft.Extensions.Logging;
using Microsoft.Extensions.Options;
using OpenDeepWiki.Chat.Abstractions;

namespace OpenDeepWiki.Chat.Providers;

/// <summary>
/// Provider 基类，提供通用实现
/// </summary>
public abstract class BaseMessageProvider : IMessageProvider
{
    protected readonly ILogger Logger;
    protected readonly IOptions<ProviderOptions> Options;
    
    public abstract string PlatformId { get; }
    public abstract string DisplayName { get; }
    public virtual bool IsEnabled => Options.Value.Enabled;
    
    protected BaseMessageProvider(ILogger logger, IOptions<ProviderOptions> options)
    {
        Logger = logger;
        Options = options;
    }
    
    public virtual Task InitializeAsync(CancellationToken cancellationToken = default)
    {
        Logger.LogInformation("Initializing {Provider}", DisplayName);
        return Task.CompletedTask;
    }
    
    public abstract Task<IChatMessage?> ParseMessageAsync(string rawMessage, CancellationToken cancellationToken = default);
    
    public abstract Task<SendResult> SendMessageAsync(IChatMessage message, string targetUserId, CancellationToken cancellationToken = default);
    
    public virtual async Task<IEnumerable<SendResult>> SendMessagesAsync(
        IEnumerable<IChatMessage> messages, 
        string targetUserId, 
        CancellationToken cancellationToken = default)
    {
        var results = new List<SendResult>();
        foreach (var message in messages)
        {
            var result = await SendMessageAsync(message, targetUserId, cancellationToken);
            results.Add(result);
            
            if (!result.Success && !result.ShouldRetry)
                break;
                
            // 默认消息间隔
            await Task.Delay(Options.Value.MessageInterval, cancellationToken);
        }
        return results;
    }
    
    public virtual Task<WebhookValidationResult> ValidateWebhookAsync(HttpRequest request, CancellationToken cancellationToken = default)
    {
        return Task.FromResult(new WebhookValidationResult(true));
    }
    
    public virtual Task ShutdownAsync(CancellationToken cancellationToken = default)
    {
        Logger.LogInformation("Shutting down {Provider}", DisplayName);
        return Task.CompletedTask;
    }
    
    /// <summary>
    /// 消息类型降级处理
    /// 当消息类型不被目标平台支持时，将其降级为文本消息
    /// </summary>
    /// <param name="message">原始消息</param>
    /// <param name="supportedTypes">目标平台支持的消息类型集合</param>
    /// <returns>降级后的消息（如果需要降级）或原始消息</returns>
    protected virtual IChatMessage DegradeMessage(IChatMessage message, ISet<ChatMessageType>? supportedTypes = null)
    {
        // 文本消息不需要降级
        if (message.MessageType == ChatMessageType.Text)
            return message;
        
        // 如果没有指定支持的类型，默认只支持文本
        supportedTypes ??= new HashSet<ChatMessageType> { ChatMessageType.Text };
        
        // 如果消息类型被支持，不需要降级
        if (supportedTypes.Contains(message.MessageType))
            return message;
            
        Logger.LogWarning(
            "Message type {Type} not supported by platform {Platform}, degrading to text", 
            message.MessageType, 
            PlatformId);
        
        return new ChatMessage
        {
            MessageId = message.MessageId,
            SenderId = message.SenderId,
            ReceiverId = message.ReceiverId,
            Content = $"[{message.MessageType}] {message.Content}",
            MessageType = ChatMessageType.Text,
            Platform = message.Platform,
            Timestamp = message.Timestamp,
            Metadata = message.Metadata
        };
    }
}
