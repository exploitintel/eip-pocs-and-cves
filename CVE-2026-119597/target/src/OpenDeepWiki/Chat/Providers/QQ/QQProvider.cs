using System.Net.Http.Headers;
using System.Text;
using System.Text.Json;
using Microsoft.AspNetCore.Http;
using Microsoft.Extensions.Logging;
using Microsoft.Extensions.Options;
using OpenDeepWiki.Chat.Abstractions;

namespace OpenDeepWiki.Chat.Providers.QQ;

/// <summary>
/// QQ 机器人消息 Provider 实现
/// 支持频道消息、群聊消息和 C2C 私聊消息
/// </summary>
public class QQProvider : BaseMessageProvider
{
    private readonly HttpClient _httpClient;
    private readonly QQProviderOptions _qqOptions;
    private string? _accessToken;
    private DateTime _tokenExpireTime = DateTime.MinValue;
    private readonly SemaphoreSlim _tokenLock = new(1, 1);
    
    /// <summary>
    /// QQ 支持的消息类型
    /// </summary>
    private static readonly HashSet<ChatMessageType> SupportedMessageTypes = new()
    {
        ChatMessageType.Text,
        ChatMessageType.Image,
        ChatMessageType.RichText,
        ChatMessageType.Card
    };
    
    public override string PlatformId => "qq";
    public override string DisplayName => "QQ机器人";
    
    public QQProvider(
        ILogger<QQProvider> logger,
        IOptions<QQProviderOptions> options,
        HttpClient httpClient)
        : base(logger, options)
    {
        _httpClient = httpClient;
        _qqOptions = options.Value;
    }
    
    /// <summary>
    /// 初始化 Provider，获取 Access Token
    /// </summary>
    public override async Task InitializeAsync(CancellationToken cancellationToken = default)
    {
        await base.InitializeAsync(cancellationToken);
        
        if (string.IsNullOrEmpty(_qqOptions.AppId) || string.IsNullOrEmpty(_qqOptions.AppSecret))
        {
            Logger.LogWarning("QQ AppId or AppSecret not configured, provider will not be fully functional");
            return;
        }
        
        try
        {
            await GetAccessTokenAsync(cancellationToken);
            Logger.LogInformation("QQ provider initialized successfully");
        }
        catch (Exception ex)
        {
            Logger.LogError(ex, "Failed to initialize QQ provider");
        }
    }
    
    /// <summary>
    /// 解析 QQ 原始消息为统一格式
    /// </summary>
    public override async Task<IChatMessage?> ParseMessageAsync(string rawMessage, CancellationToken cancellationToken = default)
    {
        try
        {
            var webhookEvent = JsonSerializer.Deserialize<QQWebhookEvent>(rawMessage);
            if (webhookEvent == null)
            {
                Logger.LogWarning("Failed to deserialize QQ webhook event");
                return null;
            }
            
            // 只处理消息事件
            var eventType = webhookEvent.EventType;
            if (!IsMessageEvent(eventType))
            {
                Logger.LogDebug("Ignoring non-message event: {EventType}", eventType);
                return null;
            }
            
            return eventType switch
            {
                QQEventType.GroupAtMessageCreate => ParseGroupMessage(webhookEvent),
                QQEventType.C2CMessageCreate => ParseC2CMessage(webhookEvent),
                QQEventType.AtMessageCreate or QQEventType.MessageCreate => ParseChannelMessage(webhookEvent),
                QQEventType.DirectMessageCreate => ParseDirectMessage(webhookEvent),
                _ => null
            };
        }
        catch (Exception ex)
        {
            Logger.LogError(ex, "Failed to parse QQ message");
            return null;
        }
    }
    
    /// <summary>
    /// 发送消息到 QQ
    /// </summary>
    public override async Task<SendResult> SendMessageAsync(
        IChatMessage message, 
        string targetUserId, 
        CancellationToken cancellationToken = default)
    {
        return await SendWithRetryAsync(async () =>
        {
            var token = await GetAccessTokenAsync(cancellationToken);
            
            // 降级不支持的消息类型
            var processedMessage = DegradeMessage(message, SupportedMessageTypes);
            
            // 根据目标用户 ID 格式判断消息类型
            var (messageType, targetId, groupOpenId) = ParseTargetUserId(targetUserId);
            
            return messageType switch
            {
                QQMessageTargetType.Group => await SendGroupMessageAsync(processedMessage, groupOpenId!, targetId, token, cancellationToken),
                QQMessageTargetType.C2C => await SendC2CMessageAsync(processedMessage, targetId, token, cancellationToken),
                QQMessageTargetType.Channel => await SendChannelMessageAsync(processedMessage, targetId, token, cancellationToken),
                _ => new SendResult(false, ErrorCode: "UNKNOWN_TARGET_TYPE", ErrorMessage: "Unknown target user type")
            };
        }, cancellationToken);
    }
    
    /// <summary>
    /// 验证 QQ Webhook 请求
    /// </summary>
    public override async Task<WebhookValidationResult> ValidateWebhookAsync(
        HttpRequest request, 
        CancellationToken cancellationToken = default)
    {
        try
        {
            request.EnableBuffering();
            using var reader = new StreamReader(request.Body, Encoding.UTF8, leaveOpen: true);
            var body = await reader.ReadToEndAsync(cancellationToken);
            request.Body.Position = 0;
            
            var webhookEvent = JsonSerializer.Deserialize<QQWebhookEvent>(body);
            if (webhookEvent == null)
            {
                return new WebhookValidationResult(false, ErrorMessage: "Invalid request body");
            }
            
            // 处理 HTTP 回调验证请求（OpCode 13）
            if (webhookEvent.OpCode == 13)
            {
                // 验证请求需要返回特定格式
                return new WebhookValidationResult(true, Challenge: webhookEvent.Data?.Id);
            }
            
            return new WebhookValidationResult(true);
        }
        catch (Exception ex)
        {
            Logger.LogError(ex, "Failed to validate QQ webhook");
            return new WebhookValidationResult(false, ErrorMessage: ex.Message);
        }
    }

    
    #region 消息解析方法
    
    /// <summary>
    /// 判断是否为消息事件
    /// </summary>
    private static bool IsMessageEvent(string? eventType)
    {
        return eventType is QQEventType.AtMessageCreate 
            or QQEventType.MessageCreate 
            or QQEventType.DirectMessageCreate
            or QQEventType.GroupAtMessageCreate
            or QQEventType.C2CMessageCreate;
    }
    
    /// <summary>
    /// 解析群聊消息
    /// </summary>
    private IChatMessage? ParseGroupMessage(QQWebhookEvent webhookEvent)
    {
        var data = webhookEvent.Data;
        if (data == null) return null;
        
        var senderId = data.Author?.MemberOpenId ?? data.Author?.Id ?? string.Empty;
        var content = CleanMentions(data.Content);
        var messageType = DetermineMessageType(data);
        
        return new ChatMessage
        {
            MessageId = data.Id,
            SenderId = senderId,
            ReceiverId = data.GroupOpenId,
            Content = content,
            MessageType = messageType,
            Platform = PlatformId,
            Timestamp = ParseTimestamp(data.Timestamp),
            Metadata = new Dictionary<string, object>
            {
                { "event_type", QQEventType.GroupAtMessageCreate },
                { "group_openid", data.GroupOpenId ?? string.Empty },
                { "msg_seq", data.MsgSeq ?? 0 },
                { "mentions", data.Mentions ?? new List<QQMention>() },
                { "raw_msg_id", data.Id }
            }
        };
    }
    
    /// <summary>
    /// 解析 C2C 私聊消息
    /// </summary>
    private IChatMessage? ParseC2CMessage(QQWebhookEvent webhookEvent)
    {
        var data = webhookEvent.Data;
        if (data == null) return null;
        
        // C2C 消息的发送者 ID 在 author.user_openid 中
        var senderId = data.Author?.MemberOpenId ?? data.Author?.Id ?? string.Empty;
        var messageType = DetermineMessageType(data);
        
        return new ChatMessage
        {
            MessageId = data.Id,
            SenderId = senderId,
            ReceiverId = null,
            Content = data.Content,
            MessageType = messageType,
            Platform = PlatformId,
            Timestamp = ParseTimestamp(data.Timestamp),
            Metadata = new Dictionary<string, object>
            {
                { "event_type", QQEventType.C2CMessageCreate },
                { "msg_seq", data.MsgSeq ?? 0 },
                { "raw_msg_id", data.Id }
            }
        };
    }
    
    /// <summary>
    /// 解析频道消息
    /// </summary>
    private IChatMessage? ParseChannelMessage(QQWebhookEvent webhookEvent)
    {
        var data = webhookEvent.Data;
        if (data == null) return null;
        
        var senderId = data.Author?.Id ?? string.Empty;
        var content = CleanMentions(data.Content);
        var messageType = DetermineMessageType(data);
        
        return new ChatMessage
        {
            MessageId = data.Id,
            SenderId = senderId,
            ReceiverId = data.ChannelId,
            Content = content,
            MessageType = messageType,
            Platform = PlatformId,
            Timestamp = ParseTimestamp(data.Timestamp),
            Metadata = new Dictionary<string, object>
            {
                { "event_type", webhookEvent.EventType ?? string.Empty },
                { "channel_id", data.ChannelId ?? string.Empty },
                { "guild_id", data.GuildId ?? string.Empty },
                { "mentions", data.Mentions ?? new List<QQMention>() },
                { "raw_msg_id", data.Id }
            }
        };
    }
    
    /// <summary>
    /// 解析私信消息
    /// </summary>
    private IChatMessage? ParseDirectMessage(QQWebhookEvent webhookEvent)
    {
        var data = webhookEvent.Data;
        if (data == null) return null;
        
        var senderId = data.Author?.Id ?? string.Empty;
        var messageType = DetermineMessageType(data);
        
        return new ChatMessage
        {
            MessageId = data.Id,
            SenderId = senderId,
            ReceiverId = data.GuildId,
            Content = data.Content,
            MessageType = messageType,
            Platform = PlatformId,
            Timestamp = ParseTimestamp(data.Timestamp),
            Metadata = new Dictionary<string, object>
            {
                { "event_type", QQEventType.DirectMessageCreate },
                { "guild_id", data.GuildId ?? string.Empty },
                { "raw_msg_id", data.Id }
            }
        };
    }
    
    /// <summary>
    /// 清理消息中的 @ 提及标记
    /// </summary>
    private static string CleanMentions(string content)
    {
        if (string.IsNullOrEmpty(content)) return content;
        
        // 移除 <@!用户ID> 格式的 @ 提及
        var cleaned = System.Text.RegularExpressions.Regex.Replace(content, @"<@!\d+>", "");
        // 移除 <@用户ID> 格式的 @ 提及
        cleaned = System.Text.RegularExpressions.Regex.Replace(cleaned, @"<@\d+>", "");
        
        return cleaned.Trim();
    }
    
    /// <summary>
    /// 根据消息内容确定消息类型
    /// </summary>
    private static ChatMessageType DetermineMessageType(QQEventData data)
    {
        if (data.Attachments != null && data.Attachments.Count > 0)
        {
            var attachment = data.Attachments[0];
            if (attachment.ContentType.StartsWith("image/"))
                return ChatMessageType.Image;
            if (attachment.ContentType.StartsWith("audio/"))
                return ChatMessageType.Audio;
            if (attachment.ContentType.StartsWith("video/"))
                return ChatMessageType.Video;
            return ChatMessageType.File;
        }
        
        return ChatMessageType.Text;
    }
    
    #endregion
    
    #region 消息发送方法
    
    /// <summary>
    /// 发送群聊消息
    /// </summary>
    private async Task<SendResult> SendGroupMessageAsync(
        IChatMessage message,
        string groupOpenId,
        string? msgId,
        string token,
        CancellationToken cancellationToken)
    {
        var request = new QQSendGroupMessageRequest
        {
            Content = message.Content,
            MsgType = ConvertToQQMsgType(message.MessageType),
            MsgId = msgId
        };
        
        // 如果有原始消息序列号，添加到请求中（被动回复）
        if (message.Metadata?.TryGetValue("msg_seq", out var msgSeqObj) == true && msgSeqObj is int msgSeq)
        {
            request.MsgSeq = msgSeq;
        }
        
        var url = $"{GetApiBaseUrl()}/v2/groups/{groupOpenId}/messages";
        return await SendApiRequestAsync<QQSendGroupMessageRequest, QQSendMessageResponse>(url, request, token, cancellationToken);
    }
    
    /// <summary>
    /// 发送 C2C 私聊消息
    /// </summary>
    private async Task<SendResult> SendC2CMessageAsync(
        IChatMessage message,
        string userOpenId,
        string token,
        CancellationToken cancellationToken)
    {
        var request = new QQSendC2CMessageRequest
        {
            Content = message.Content,
            MsgType = ConvertToQQMsgType(message.MessageType)
        };
        
        // 如果有原始消息 ID，添加到请求中（被动回复）
        if (message.Metadata?.TryGetValue("raw_msg_id", out var rawMsgId) == true && rawMsgId is string msgId)
        {
            request.MsgId = msgId;
        }
        
        var url = $"{GetApiBaseUrl()}/v2/users/{userOpenId}/messages";
        return await SendApiRequestAsync<QQSendC2CMessageRequest, QQSendMessageResponse>(url, request, token, cancellationToken);
    }
    
    /// <summary>
    /// 发送频道消息
    /// </summary>
    private async Task<SendResult> SendChannelMessageAsync(
        IChatMessage message,
        string channelId,
        string token,
        CancellationToken cancellationToken)
    {
        var request = new QQSendChannelMessageRequest
        {
            Content = message.Content
        };
        
        // 如果有原始消息 ID，添加到请求中（被动回复）
        if (message.Metadata?.TryGetValue("raw_msg_id", out var rawMsgId) == true && rawMsgId is string msgId)
        {
            request.MsgId = msgId;
        }
        
        // 处理图片消息
        if (message.MessageType == ChatMessageType.Image)
        {
            request.Image = message.Content;
            request.Content = null;
        }
        
        var url = $"{GetApiBaseUrl()}/channels/{channelId}/messages";
        return await SendApiRequestAsync<QQSendChannelMessageRequest, QQSendMessageResponse>(url, request, token, cancellationToken);
    }
    
    /// <summary>
    /// 发送 API 请求
    /// </summary>
    private async Task<SendResult> SendApiRequestAsync<TRequest, TResponse>(
        string url,
        TRequest request,
        string token,
        CancellationToken cancellationToken)
        where TResponse : QQSendMessageResponse
    {
        var httpRequest = new HttpRequestMessage(HttpMethod.Post, url)
        {
            Content = new StringContent(
                JsonSerializer.Serialize(request),
                Encoding.UTF8,
                "application/json")
        };
        httpRequest.Headers.Authorization = new AuthenticationHeaderValue("QQBot", token);
        
        var response = await _httpClient.SendAsync(httpRequest, cancellationToken);
        var responseContent = await response.Content.ReadAsStringAsync(cancellationToken);
        
        if (response.IsSuccessStatusCode)
        {
            var apiResponse = JsonSerializer.Deserialize<TResponse>(responseContent);
            if (apiResponse != null)
            {
                return new SendResult(true, apiResponse.Id);
            }
        }
        
        // 解析错误响应
        var errorResponse = JsonSerializer.Deserialize<QQApiResponse>(responseContent);
        var shouldRetry = IsRetryableError((int)response.StatusCode, errorResponse?.Code ?? 0);
        
        return new SendResult(
            false,
            ErrorCode: errorResponse?.Code.ToString() ?? response.StatusCode.ToString(),
            ErrorMessage: errorResponse?.Message ?? responseContent,
            ShouldRetry: shouldRetry);
    }
    
    /// <summary>
    /// 转换为 QQ 消息类型
    /// </summary>
    private static int ConvertToQQMsgType(ChatMessageType messageType)
    {
        return messageType switch
        {
            ChatMessageType.Text => QQMsgType.Text,
            ChatMessageType.RichText => QQMsgType.Markdown,
            ChatMessageType.Card => QQMsgType.Ark,
            _ => QQMsgType.Text
        };
    }
    
    /// <summary>
    /// 解析目标用户 ID，确定消息类型
    /// </summary>
    private static (QQMessageTargetType Type, string TargetId, string? GroupOpenId) ParseTargetUserId(string targetUserId)
    {
        // 格式: group:{groupOpenId}:{msgId} 或 c2c:{userOpenId} 或 channel:{channelId}
        var parts = targetUserId.Split(':');
        
        if (parts.Length >= 2)
        {
            return parts[0].ToLower() switch
            {
                "group" => (QQMessageTargetType.Group, parts.Length > 2 ? parts[2] : string.Empty, parts[1]),
                "c2c" => (QQMessageTargetType.C2C, parts[1], null),
                "channel" => (QQMessageTargetType.Channel, parts[1], null),
                _ => (QQMessageTargetType.Unknown, targetUserId, null)
            };
        }
        
        // 默认作为频道消息处理
        return (QQMessageTargetType.Channel, targetUserId, null);
    }
    
    #endregion

    
    #region 鉴权和 Token 管理
    
    /// <summary>
    /// 获取 Access Token（带缓存）
    /// </summary>
    public async Task<string> GetAccessTokenAsync(CancellationToken cancellationToken = default)
    {
        if (!string.IsNullOrEmpty(_accessToken) && DateTime.UtcNow < _tokenExpireTime)
        {
            return _accessToken;
        }
        
        await _tokenLock.WaitAsync(cancellationToken);
        try
        {
            // 双重检查
            if (!string.IsNullOrEmpty(_accessToken) && DateTime.UtcNow < _tokenExpireTime)
            {
                return _accessToken;
            }
            
            var url = "https://bots.qq.com/app/getAppAccessToken";
            var request = new
            {
                appId = _qqOptions.AppId,
                clientSecret = _qqOptions.AppSecret
            };
            
            var response = await _httpClient.PostAsync(
                url,
                new StringContent(JsonSerializer.Serialize(request), Encoding.UTF8, "application/json"),
                cancellationToken);
            
            var content = await response.Content.ReadAsStringAsync(cancellationToken);
            var tokenResponse = JsonSerializer.Deserialize<QQTokenResponse>(content);
            
            if (string.IsNullOrEmpty(tokenResponse?.AccessToken))
            {
                throw new InvalidOperationException($"Failed to get access token: {content}");
            }
            
            _accessToken = tokenResponse.AccessToken;
            _tokenExpireTime = DateTime.UtcNow.AddSeconds(_qqOptions.TokenCacheSeconds);
            
            Logger.LogDebug("QQ access token refreshed, expires at {ExpireTime}", _tokenExpireTime);
            
            return _accessToken;
        }
        finally
        {
            _tokenLock.Release();
        }
    }
    
    /// <summary>
    /// 获取 API 基础 URL
    /// </summary>
    private string GetApiBaseUrl()
    {
        return _qqOptions.UseSandbox ? _qqOptions.SandboxApiBaseUrl : _qqOptions.ApiBaseUrl;
    }
    
    #endregion
    
    #region 重试机制
    
    /// <summary>
    /// 判断是否为可重试的错误
    /// </summary>
    private static bool IsRetryableError(int httpStatusCode, int errorCode)
    {
        // HTTP 状态码判断
        if (httpStatusCode is 429 or 500 or 502 or 503 or 504)
            return true;
        
        // QQ 平台错误码判断
        return errorCode switch
        {
            11281 => true,  // 频率限制
            11282 => true,  // 并发限制
            11264 => true,  // 服务器内部错误
            _ => false
        };
    }
    
    /// <summary>
    /// 带重试的发送逻辑
    /// </summary>
    private async Task<SendResult> SendWithRetryAsync(
        Func<Task<SendResult>> sendFunc,
        CancellationToken cancellationToken)
    {
        var maxRetries = _qqOptions.MaxRetryCount;
        var retryDelayBase = _qqOptions.RetryDelayBase;
        
        for (var attempt = 0; attempt <= maxRetries; attempt++)
        {
            try
            {
                var result = await sendFunc();
                
                if (result.Success || !result.ShouldRetry || attempt >= maxRetries)
                {
                    return result;
                }
                
                // 指数退避
                var delay = retryDelayBase * (int)Math.Pow(2, attempt);
                Logger.LogWarning(
                    "QQ API call failed (attempt {Attempt}/{MaxRetries}), retrying in {Delay}ms. Error: {Error}",
                    attempt + 1, maxRetries + 1, delay, result.ErrorMessage);
                
                await Task.Delay(delay, cancellationToken);
            }
            catch (Exception ex) when (attempt < maxRetries)
            {
                var delay = retryDelayBase * (int)Math.Pow(2, attempt);
                Logger.LogWarning(ex,
                    "QQ API call threw exception (attempt {Attempt}/{MaxRetries}), retrying in {Delay}ms",
                    attempt + 1, maxRetries + 1, delay);
                
                await Task.Delay(delay, cancellationToken);
            }
        }
        
        return new SendResult(false, ErrorCode: "MAX_RETRIES_EXCEEDED", ErrorMessage: "Maximum retry attempts exceeded");
    }
    
    #endregion
    
    #region 辅助方法
    
    /// <summary>
    /// 解析时间戳
    /// </summary>
    private static DateTimeOffset ParseTimestamp(string timestamp)
    {
        if (DateTimeOffset.TryParse(timestamp, out var result))
        {
            return result;
        }
        
        if (long.TryParse(timestamp, out var ms))
        {
            return DateTimeOffset.FromUnixTimeMilliseconds(ms);
        }
        
        return DateTimeOffset.UtcNow;
    }
    
    /// <summary>
    /// 转换消息为 QQ 格式（用于测试）
    /// </summary>
    public (int MsgType, string Content) ConvertToQQFormat(IChatMessage message)
    {
        var msgType = ConvertToQQMsgType(message.MessageType);
        return (msgType, message.Content);
    }
    
    #endregion
}

/// <summary>
/// QQ 消息目标类型
/// </summary>
public enum QQMessageTargetType
{
    /// <summary>
    /// 未知类型
    /// </summary>
    Unknown,
    
    /// <summary>
    /// 群聊消息
    /// </summary>
    Group,
    
    /// <summary>
    /// C2C 私聊消息
    /// </summary>
    C2C,
    
    /// <summary>
    /// 频道消息
    /// </summary>
    Channel
}
