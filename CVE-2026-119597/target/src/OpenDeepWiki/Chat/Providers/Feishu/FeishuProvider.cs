using System.Net.Http.Headers;
using System.Security.Cryptography;
using System.Text;
using System.Text.Json;
using Microsoft.AspNetCore.Http;
using Microsoft.Extensions.Logging;
using Microsoft.Extensions.Options;
using OpenDeepWiki.Chat.Abstractions;

namespace OpenDeepWiki.Chat.Providers.Feishu;

/// <summary>
/// 飞书消息 Provider 实现
/// </summary>
public class FeishuProvider : BaseMessageProvider
{
    private readonly HttpClient _httpClient;
    private readonly FeishuProviderOptions _feishuOptions;
    private string? _accessToken;
    private DateTime _tokenExpireTime = DateTime.MinValue;
    private readonly SemaphoreSlim _tokenLock = new(1, 1);
    
    /// <summary>
    /// 飞书支持的消息类型
    /// </summary>
    private static readonly HashSet<ChatMessageType> SupportedMessageTypes = new()
    {
        ChatMessageType.Text,
        ChatMessageType.Image,
        ChatMessageType.RichText,
        ChatMessageType.Card
    };
    
    public override string PlatformId => "feishu";
    public override string DisplayName => "飞书";
    
    public FeishuProvider(
        ILogger<FeishuProvider> logger,
        IOptions<FeishuProviderOptions> options,
        HttpClient httpClient)
        : base(logger, options)
    {
        _httpClient = httpClient;
        _feishuOptions = options.Value;
    }
    
    /// <summary>
    /// 初始化 Provider，获取 Access Token
    /// </summary>
    public override async Task InitializeAsync(CancellationToken cancellationToken = default)
    {
        await base.InitializeAsync(cancellationToken);
        
        if (string.IsNullOrEmpty(_feishuOptions.AppId) || string.IsNullOrEmpty(_feishuOptions.AppSecret))
        {
            Logger.LogWarning("Feishu AppId or AppSecret not configured, provider will not be fully functional");
            return;
        }
        
        try
        {
            await GetAccessTokenAsync(cancellationToken);
            Logger.LogInformation("Feishu provider initialized successfully");
        }
        catch (Exception ex)
        {
            Logger.LogError(ex, "Failed to initialize Feishu provider");
        }
    }
    
    /// <summary>
    /// 解析飞书原始消息为统一格式
    /// </summary>
    public override async Task<IChatMessage?> ParseMessageAsync(string rawMessage, CancellationToken cancellationToken = default)
    {
        try
        {
            var webhookEvent = JsonSerializer.Deserialize<FeishuWebhookEvent>(rawMessage);
            if (webhookEvent == null)
            {
                Logger.LogWarning("Failed to deserialize Feishu webhook event");
                return null;
            }
            
            // 处理加密消息
            if (!string.IsNullOrEmpty(webhookEvent.Encrypt))
            {
                var decrypted = DecryptMessage(webhookEvent.Encrypt);
                if (decrypted == null)
                {
                    Logger.LogWarning("Failed to decrypt Feishu message");
                    return null;
                }
                webhookEvent = JsonSerializer.Deserialize<FeishuWebhookEvent>(decrypted);
            }
            
            // 检查是否是消息事件
            var eventType = webhookEvent?.Header?.EventType ?? webhookEvent?.Type;
            if (eventType != "im.message.receive_v1" && eventType != "message")
            {
                Logger.LogDebug("Ignoring non-message event: {EventType}", eventType);
                return null;
            }
            
            var message = webhookEvent?.Event?.Message;
            if (message == null)
            {
                Logger.LogWarning("No message content in Feishu event");
                return null;
            }
            
            var senderId = webhookEvent?.Event?.Sender?.SenderId?.OpenId ?? string.Empty;
            var (messageType, content) = ParseFeishuMessageContent(message.MessageType, message.Content);
            
            return new ChatMessage
            {
                MessageId = message.MessageId,
                SenderId = senderId,
                ReceiverId = message.ChatId,
                Content = content,
                MessageType = messageType,
                Platform = PlatformId,
                Timestamp = ParseTimestamp(message.CreateTime),
                Metadata = new Dictionary<string, object>
                {
                    { "chat_type", message.ChatType },
                    { "chat_id", message.ChatId },
                    { "raw_message_type", message.MessageType },
                    { "mentions", message.Mentions ?? new List<FeishuMention>() }
                }
            };
        }
        catch (Exception ex)
        {
            Logger.LogError(ex, "Failed to parse Feishu message");
            return null;
        }
    }
    
    /// <summary>
    /// 发送消息到飞书
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
            
            // 构建发送请求
            var (msgType, content) = ConvertToFeishuFormat(processedMessage);
            var request = new FeishuSendMessageRequest
            {
                ReceiveId = targetUserId,
                MsgType = msgType,
                Content = content
            };
            
            var url = $"{_feishuOptions.ApiBaseUrl}/im/v1/messages?receive_id_type=open_id";
            var httpRequest = new HttpRequestMessage(HttpMethod.Post, url)
            {
                Content = new StringContent(
                    JsonSerializer.Serialize(request),
                    Encoding.UTF8,
                    "application/json")
            };
            httpRequest.Headers.Authorization = new AuthenticationHeaderValue("Bearer", token);
            
            var response = await _httpClient.SendAsync(httpRequest, cancellationToken);
            var responseContent = await response.Content.ReadAsStringAsync(cancellationToken);
            
            var apiResponse = JsonSerializer.Deserialize<FeishuSendMessageResponse>(responseContent);
            
            if (apiResponse?.Code == 0)
            {
                return new SendResult(true, apiResponse.Data?.MessageId);
            }
            
            var shouldRetry = IsRetryableError(apiResponse?.Code ?? -1);
            return new SendResult(
                false,
                ErrorCode: apiResponse?.Code.ToString(),
                ErrorMessage: apiResponse?.Message,
                ShouldRetry: shouldRetry);
        }, cancellationToken);
    }

    
    /// <summary>
    /// 验证飞书 Webhook 请求
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
            
            var webhookEvent = JsonSerializer.Deserialize<FeishuWebhookEvent>(body);
            if (webhookEvent == null)
            {
                return new WebhookValidationResult(false, ErrorMessage: "Invalid request body");
            }
            
            // 处理加密消息
            if (!string.IsNullOrEmpty(webhookEvent.Encrypt))
            {
                var decrypted = DecryptMessage(webhookEvent.Encrypt);
                if (decrypted == null)
                {
                    return new WebhookValidationResult(false, ErrorMessage: "Failed to decrypt message");
                }
                webhookEvent = JsonSerializer.Deserialize<FeishuWebhookEvent>(decrypted);
            }
            
            // URL 验证请求
            if (webhookEvent?.Type == "url_verification")
            {
                // 验证 Token
                if (!string.IsNullOrEmpty(_feishuOptions.VerificationToken) &&
                    webhookEvent.Token != _feishuOptions.VerificationToken)
                {
                    return new WebhookValidationResult(false, ErrorMessage: "Invalid verification token");
                }
                
                return new WebhookValidationResult(true, Challenge: webhookEvent.Challenge);
            }
            
            // 验证事件 Token（2.0 格式）
            var token = webhookEvent?.Header?.Token ?? webhookEvent?.Token;
            if (!string.IsNullOrEmpty(_feishuOptions.VerificationToken) &&
                token != _feishuOptions.VerificationToken)
            {
                return new WebhookValidationResult(false, ErrorMessage: "Invalid event token");
            }
            
            return new WebhookValidationResult(true);
        }
        catch (Exception ex)
        {
            Logger.LogError(ex, "Failed to validate Feishu webhook");
            return new WebhookValidationResult(false, ErrorMessage: ex.Message);
        }
    }
    
    #region 私有方法
    
    /// <summary>
    /// 获取 Access Token（带缓存）
    /// </summary>
    private async Task<string> GetAccessTokenAsync(CancellationToken cancellationToken)
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
            
            var url = $"{_feishuOptions.ApiBaseUrl}/auth/v3/tenant_access_token/internal";
            var request = new
            {
                app_id = _feishuOptions.AppId,
                app_secret = _feishuOptions.AppSecret
            };
            
            var response = await _httpClient.PostAsync(
                url,
                new StringContent(JsonSerializer.Serialize(request), Encoding.UTF8, "application/json"),
                cancellationToken);
            
            var content = await response.Content.ReadAsStringAsync(cancellationToken);
            var tokenResponse = JsonSerializer.Deserialize<FeishuTokenResponse>(content);
            
            if (tokenResponse?.Code != 0)
            {
                throw new InvalidOperationException($"Failed to get access token: {tokenResponse?.Message}");
            }
            
            _accessToken = tokenResponse.TenantAccessToken;
            _tokenExpireTime = DateTime.UtcNow.AddSeconds(_feishuOptions.TokenCacheSeconds);
            
            Logger.LogDebug("Feishu access token refreshed, expires at {ExpireTime}", _tokenExpireTime);
            
            return _accessToken;
        }
        finally
        {
            _tokenLock.Release();
        }
    }
    
    /// <summary>
    /// 解密飞书消息
    /// </summary>
    private string? DecryptMessage(string encryptedContent)
    {
        if (string.IsNullOrEmpty(_feishuOptions.EncryptKey))
        {
            Logger.LogWarning("Encrypt key not configured, cannot decrypt message");
            return null;
        }
        
        try
        {
            var encryptKey = _feishuOptions.EncryptKey;
            var keyBytes = SHA256.HashData(Encoding.UTF8.GetBytes(encryptKey));
            var encryptedBytes = Convert.FromBase64String(encryptedContent);
            
            using var aes = Aes.Create();
            aes.Key = keyBytes;
            aes.Mode = CipherMode.CBC;
            aes.Padding = PaddingMode.None;
            
            // IV 是加密数据的前 16 字节
            var iv = encryptedBytes[..16];
            var cipherText = encryptedBytes[16..];
            
            aes.IV = iv;
            
            using var decryptor = aes.CreateDecryptor();
            var decryptedBytes = decryptor.TransformFinalBlock(cipherText, 0, cipherText.Length);
            
            // 移除 PKCS7 填充
            var paddingLength = decryptedBytes[^1];
            var result = Encoding.UTF8.GetString(decryptedBytes, 0, decryptedBytes.Length - paddingLength);
            
            return result;
        }
        catch (Exception ex)
        {
            Logger.LogError(ex, "Failed to decrypt Feishu message");
            return null;
        }
    }
    
    /// <summary>
    /// 解析飞书消息内容
    /// </summary>
    private (ChatMessageType Type, string Content) ParseFeishuMessageContent(string msgType, string content)
    {
        try
        {
            return msgType switch
            {
                "text" => ParseTextMessage(content),
                "image" => (ChatMessageType.Image, ParseImageMessage(content)),
                "post" => (ChatMessageType.RichText, ParsePostMessage(content)),
                "interactive" => (ChatMessageType.Card, content),
                _ => (ChatMessageType.Unknown, content)
            };
        }
        catch (Exception ex)
        {
            Logger.LogWarning(ex, "Failed to parse Feishu message content of type {Type}", msgType);
            return (ChatMessageType.Unknown, content);
        }
    }
    
    private (ChatMessageType, string) ParseTextMessage(string content)
    {
        var textContent = JsonSerializer.Deserialize<FeishuTextContent>(content);
        return (ChatMessageType.Text, textContent?.Text ?? content);
    }
    
    private string ParseImageMessage(string content)
    {
        var imageContent = JsonSerializer.Deserialize<FeishuImageContent>(content);
        return imageContent?.ImageKey ?? content;
    }
    
    private string ParsePostMessage(string content)
    {
        // 富文本消息保持原始 JSON 格式
        return content;
    }
    
    /// <summary>
    /// 转换为飞书消息格式
    /// </summary>
    public (string MsgType, string Content) ConvertToFeishuFormat(IChatMessage message)
    {
        return message.MessageType switch
        {
            ChatMessageType.Text => ("text", JsonSerializer.Serialize(new FeishuTextContent { Text = message.Content })),
            ChatMessageType.Image => ("image", JsonSerializer.Serialize(new FeishuImageContent { ImageKey = message.Content })),
            ChatMessageType.Card => ("interactive", message.Content),
            ChatMessageType.RichText => ("post", message.Content),
            _ => ("text", JsonSerializer.Serialize(new FeishuTextContent { Text = message.Content }))
        };
    }
    
    /// <summary>
    /// 解析时间戳
    /// </summary>
    private static DateTimeOffset ParseTimestamp(string timestamp)
    {
        if (long.TryParse(timestamp, out var ms))
        {
            return DateTimeOffset.FromUnixTimeMilliseconds(ms);
        }
        return DateTimeOffset.UtcNow;
    }
    
    /// <summary>
    /// 判断是否为可重试的错误
    /// </summary>
    private static bool IsRetryableError(int errorCode)
    {
        // 飞书常见可重试错误码
        return errorCode switch
        {
            99991400 => true,  // 请求频率超限
            99991663 => true,  // 服务端内部错误
            99991672 => true,  // 服务暂时不可用
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
        var maxRetries = _feishuOptions.MaxRetryCount;
        var retryDelayBase = _feishuOptions.RetryDelayBase;
        
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
                    "Feishu API call failed (attempt {Attempt}/{MaxRetries}), retrying in {Delay}ms. Error: {Error}",
                    attempt + 1, maxRetries + 1, delay, result.ErrorMessage);
                
                await Task.Delay(delay, cancellationToken);
            }
            catch (Exception ex) when (attempt < maxRetries)
            {
                var delay = retryDelayBase * (int)Math.Pow(2, attempt);
                Logger.LogWarning(ex,
                    "Feishu API call threw exception (attempt {Attempt}/{MaxRetries}), retrying in {Delay}ms",
                    attempt + 1, maxRetries + 1, delay);
                
                await Task.Delay(delay, cancellationToken);
            }
        }
        
        return new SendResult(false, ErrorCode: "MAX_RETRIES_EXCEEDED", ErrorMessage: "Maximum retry attempts exceeded");
    }
    
    #endregion
    
    #region 消息卡片构建辅助方法
    
    /// <summary>
    /// 创建简单的文本卡片
    /// </summary>
    public static string CreateTextCard(string title, string content, string headerColor = "blue")
    {
        var card = new FeishuCard
        {
            Config = new FeishuCardConfig(),
            Header = new FeishuCardHeader
            {
                Title = new FeishuCardText { Content = title },
                Template = headerColor
            },
            Elements = new List<object>
            {
                new FeishuCardMarkdown { Content = content }
            }
        };
        
        return JsonSerializer.Serialize(card);
    }
    
    /// <summary>
    /// 创建带分割线的多段卡片
    /// </summary>
    public static string CreateMultiSectionCard(string title, IEnumerable<string> sections, string headerColor = "blue")
    {
        var elements = new List<object>();
        var sectionList = sections.ToList();
        
        for (var i = 0; i < sectionList.Count; i++)
        {
            elements.Add(new FeishuCardMarkdown { Content = sectionList[i] });
            if (i < sectionList.Count - 1)
            {
                elements.Add(new FeishuCardDivider());
            }
        }
        
        var card = new FeishuCard
        {
            Config = new FeishuCardConfig(),
            Header = new FeishuCardHeader
            {
                Title = new FeishuCardText { Content = title },
                Template = headerColor
            },
            Elements = elements
        };
        
        return JsonSerializer.Serialize(card);
    }
    
    #endregion
}
