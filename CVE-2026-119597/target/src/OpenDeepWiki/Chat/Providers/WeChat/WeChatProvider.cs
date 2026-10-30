using System.Net.Http.Headers;
using System.Text;
using System.Text.Json;
using System.Xml.Serialization;
using Microsoft.AspNetCore.Http;
using Microsoft.Extensions.Logging;
using Microsoft.Extensions.Options;
using OpenDeepWiki.Chat.Abstractions;

namespace OpenDeepWiki.Chat.Providers.WeChat;

/// <summary>
/// 微信客服消息 Provider 实现
/// 支持文本、图片和语音消息
/// </summary>
public class WeChatProvider : BaseMessageProvider
{
    private readonly HttpClient _httpClient;
    private readonly WeChatProviderOptions _wechatOptions;
    private WeChatCrypto? _crypto;
    private string? _accessToken;
    private DateTime _tokenExpireTime = DateTime.MinValue;
    private readonly SemaphoreSlim _tokenLock = new(1, 1);
    
    /// <summary>
    /// 微信支持的消息类型
    /// </summary>
    private static readonly HashSet<ChatMessageType> SupportedMessageTypes = new()
    {
        ChatMessageType.Text,
        ChatMessageType.Image,
        ChatMessageType.Audio
    };
    
    public override string PlatformId => "wechat";
    public override string DisplayName => "微信客服";
    
    public WeChatProvider(
        ILogger<WeChatProvider> logger,
        IOptions<WeChatProviderOptions> options,
        HttpClient httpClient)
        : base(logger, options)
    {
        _httpClient = httpClient;
        _wechatOptions = options.Value;
    }
    
    /// <summary>
    /// 初始化 Provider
    /// </summary>
    public override async Task InitializeAsync(CancellationToken cancellationToken = default)
    {
        await base.InitializeAsync(cancellationToken);
        
        if (string.IsNullOrEmpty(_wechatOptions.AppId) || string.IsNullOrEmpty(_wechatOptions.AppSecret))
        {
            Logger.LogWarning("WeChat AppId or AppSecret not configured, provider will not be fully functional");
            return;
        }
        
        // 初始化加解密工具（如果配置了 EncodingAesKey）
        if (!string.IsNullOrEmpty(_wechatOptions.EncodingAesKey) && !string.IsNullOrEmpty(_wechatOptions.Token))
        {
            _crypto = new WeChatCrypto(_wechatOptions.Token, _wechatOptions.EncodingAesKey, _wechatOptions.AppId);
            Logger.LogInformation("WeChat message encryption enabled");
        }
        
        try
        {
            await GetAccessTokenAsync(cancellationToken);
            Logger.LogInformation("WeChat provider initialized successfully");
        }
        catch (Exception ex)
        {
            Logger.LogError(ex, "Failed to initialize WeChat provider");
        }
    }

    
    /// <summary>
    /// 解析微信原始消息为统一格式
    /// </summary>
    public override async Task<IChatMessage?> ParseMessageAsync(string rawMessage, CancellationToken cancellationToken = default)
    {
        try
        {
            // 尝试解析加密消息
            var xmlMessage = await ParseXmlMessageAsync(rawMessage, cancellationToken);
            if (xmlMessage == null)
            {
                Logger.LogWarning("Failed to parse WeChat XML message");
                return null;
            }
            
            // 忽略事件消息（只处理普通消息）
            if (xmlMessage.MsgType == WeChatMsgType.Event)
            {
                Logger.LogDebug("Ignoring WeChat event message: {Event}", xmlMessage.Event);
                return null;
            }
            
            var (messageType, content) = ParseWeChatMessageContent(xmlMessage);
            
            return new ChatMessage
            {
                MessageId = xmlMessage.MsgId.ToString(),
                SenderId = xmlMessage.FromUserName,
                ReceiverId = xmlMessage.ToUserName,
                Content = content,
                MessageType = messageType,
                Platform = PlatformId,
                Timestamp = DateTimeOffset.FromUnixTimeSeconds(xmlMessage.CreateTime),
                Metadata = new Dictionary<string, object>
                {
                    { "msg_type", xmlMessage.MsgType },
                    { "msg_data_id", xmlMessage.MsgDataId ?? string.Empty },
                    { "media_id", xmlMessage.MediaId ?? string.Empty },
                    { "pic_url", xmlMessage.PicUrl ?? string.Empty },
                    { "recognition", xmlMessage.Recognition ?? string.Empty }
                }
            };
        }
        catch (Exception ex)
        {
            Logger.LogError(ex, "Failed to parse WeChat message");
            return null;
        }
    }
    
    /// <summary>
    /// 发送消息到微信
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
            
            // 构建客服消息请求
            var request = BuildCustomMessageRequest(processedMessage, targetUserId);
            
            var url = $"{_wechatOptions.ApiBaseUrl}/cgi-bin/message/custom/send?access_token={token}";
            var httpRequest = new HttpRequestMessage(HttpMethod.Post, url)
            {
                Content = new StringContent(
                    JsonSerializer.Serialize(request, new JsonSerializerOptions 
                    { 
                        DefaultIgnoreCondition = System.Text.Json.Serialization.JsonIgnoreCondition.WhenWritingNull 
                    }),
                    Encoding.UTF8,
                    "application/json")
            };
            
            var response = await _httpClient.SendAsync(httpRequest, cancellationToken);
            var responseContent = await response.Content.ReadAsStringAsync(cancellationToken);
            
            var apiResponse = JsonSerializer.Deserialize<WeChatSendMessageResponse>(responseContent);
            
            if (apiResponse?.ErrorCode == 0)
            {
                return new SendResult(true, apiResponse.MsgId?.ToString());
            }
            
            var shouldRetry = IsRetryableError(apiResponse?.ErrorCode ?? -1);
            return new SendResult(
                false,
                ErrorCode: apiResponse?.ErrorCode.ToString(),
                ErrorMessage: apiResponse?.ErrorMessage,
                ShouldRetry: shouldRetry);
        }, cancellationToken);
    }
    
    /// <summary>
    /// 验证微信 Webhook 请求
    /// </summary>
    public override async Task<WebhookValidationResult> ValidateWebhookAsync(
        HttpRequest request, 
        CancellationToken cancellationToken = default)
    {
        try
        {
            // 获取验证参数
            var signature = request.Query["signature"].ToString();
            var timestamp = request.Query["timestamp"].ToString();
            var nonce = request.Query["nonce"].ToString();
            var echostr = request.Query["echostr"].ToString();
            var msgSignature = request.Query["msg_signature"].ToString();
            
            // URL 验证请求（GET 请求）
            if (request.Method == "GET" && !string.IsNullOrEmpty(echostr))
            {
                if (VerifySignature(signature, timestamp, nonce))
                {
                    return new WebhookValidationResult(true, Challenge: echostr);
                }
                return new WebhookValidationResult(false, ErrorMessage: "Invalid signature");
            }
            
            // 消息请求验证（POST 请求）
            if (request.Method == "POST")
            {
                // 明文模式：验证普通签名
                if (string.IsNullOrEmpty(msgSignature))
                {
                    if (!VerifySignature(signature, timestamp, nonce))
                    {
                        return new WebhookValidationResult(false, ErrorMessage: "Invalid signature");
                    }
                }
                // 加密模式：验证消息签名
                else if (_crypto != null)
                {
                    request.EnableBuffering();
                    using var reader = new StreamReader(request.Body, Encoding.UTF8, leaveOpen: true);
                    var body = await reader.ReadToEndAsync(cancellationToken);
                    request.Body.Position = 0;
                    
                    // 解析加密消息获取 Encrypt 字段
                    var encryptedMsg = DeserializeXml<WeChatEncryptedMessage>(body);
                    if (encryptedMsg != null && !string.IsNullOrEmpty(encryptedMsg.Encrypt))
                    {
                        if (!_crypto.VerifySignature(msgSignature, timestamp, nonce, encryptedMsg.Encrypt))
                        {
                            return new WebhookValidationResult(false, ErrorMessage: "Invalid message signature");
                        }
                    }
                }
            }
            
            return new WebhookValidationResult(true);
        }
        catch (Exception ex)
        {
            Logger.LogError(ex, "Failed to validate WeChat webhook");
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
            
            var url = $"{_wechatOptions.ApiBaseUrl}/cgi-bin/token?grant_type=client_credential&appid={_wechatOptions.AppId}&secret={_wechatOptions.AppSecret}";
            
            var response = await _httpClient.GetAsync(url, cancellationToken);
            var content = await response.Content.ReadAsStringAsync(cancellationToken);
            var tokenResponse = JsonSerializer.Deserialize<WeChatTokenResponse>(content);
            
            if (tokenResponse?.ErrorCode != 0 || string.IsNullOrEmpty(tokenResponse.AccessToken))
            {
                throw new InvalidOperationException($"Failed to get access token: {tokenResponse?.ErrorMessage ?? content}");
            }
            
            _accessToken = tokenResponse.AccessToken;
            _tokenExpireTime = DateTime.UtcNow.AddSeconds(_wechatOptions.TokenCacheSeconds);
            
            Logger.LogDebug("WeChat access token refreshed, expires at {ExpireTime}", _tokenExpireTime);
            
            return _accessToken;
        }
        finally
        {
            _tokenLock.Release();
        }
    }
    
    /// <summary>
    /// 解析 XML 消息（支持加密和明文）
    /// </summary>
    private async Task<WeChatXmlMessage?> ParseXmlMessageAsync(string rawMessage, CancellationToken cancellationToken)
    {
        // 首先尝试解析为加密消息
        var encryptedMsg = DeserializeXml<WeChatEncryptedMessage>(rawMessage);
        
        if (encryptedMsg != null && !string.IsNullOrEmpty(encryptedMsg.Encrypt) && _crypto != null)
        {
            // 解密消息
            var decryptedXml = _crypto.Decrypt(encryptedMsg.Encrypt);
            if (decryptedXml == null)
            {
                Logger.LogWarning("Failed to decrypt WeChat message");
                return null;
            }
            
            return DeserializeXml<WeChatXmlMessage>(decryptedXml);
        }
        
        // 明文消息
        return DeserializeXml<WeChatXmlMessage>(rawMessage);
    }
    
    /// <summary>
    /// 解析微信消息内容
    /// </summary>
    private (ChatMessageType Type, string Content) ParseWeChatMessageContent(WeChatXmlMessage message)
    {
        return message.MsgType switch
        {
            WeChatMsgType.Text => (ChatMessageType.Text, message.Content ?? string.Empty),
            WeChatMsgType.Image => (ChatMessageType.Image, message.PicUrl ?? message.MediaId ?? string.Empty),
            WeChatMsgType.Voice => (ChatMessageType.Audio, message.Recognition ?? message.MediaId ?? string.Empty),
            WeChatMsgType.Video or WeChatMsgType.ShortVideo => (ChatMessageType.Video, message.MediaId ?? string.Empty),
            WeChatMsgType.Location => (ChatMessageType.Text, $"位置: {message.Label} ({message.LocationX}, {message.LocationY})"),
            WeChatMsgType.Link => (ChatMessageType.Text, $"{message.Title}: {message.Url}"),
            _ => (ChatMessageType.Unknown, message.Content ?? string.Empty)
        };
    }
    
    /// <summary>
    /// 构建客服消息请求
    /// </summary>
    private WeChatCustomMessageRequest BuildCustomMessageRequest(IChatMessage message, string targetUserId)
    {
        var request = new WeChatCustomMessageRequest
        {
            ToUser = targetUserId
        };
        
        switch (message.MessageType)
        {
            case ChatMessageType.Text:
                request.MsgType = WeChatMsgType.Text;
                request.Text = new WeChatTextContent { Content = message.Content };
                break;
                
            case ChatMessageType.Image:
                request.MsgType = WeChatMsgType.Image;
                request.Image = new WeChatMediaContent { MediaId = message.Content };
                break;
                
            case ChatMessageType.Audio:
                request.MsgType = WeChatMsgType.Voice;
                request.Voice = new WeChatMediaContent { MediaId = message.Content };
                break;
                
            default:
                // 默认发送文本消息
                request.MsgType = WeChatMsgType.Text;
                request.Text = new WeChatTextContent { Content = message.Content };
                break;
        }
        
        return request;
    }
    
    /// <summary>
    /// 验证签名（明文模式）
    /// </summary>
    private bool VerifySignature(string signature, string timestamp, string nonce)
    {
        if (string.IsNullOrEmpty(_wechatOptions.Token))
            return true; // 未配置 Token 时跳过验证
        
        var items = new[] { _wechatOptions.Token, timestamp, nonce };
        Array.Sort(items, StringComparer.Ordinal);
        
        var combined = string.Concat(items);
        var hash = System.Security.Cryptography.SHA1.HashData(Encoding.UTF8.GetBytes(combined));
        var calculatedSignature = Convert.ToHexString(hash).ToLowerInvariant();
        
        return string.Equals(signature, calculatedSignature, StringComparison.OrdinalIgnoreCase);
    }
    
    /// <summary>
    /// 反序列化 XML
    /// </summary>
    private static T? DeserializeXml<T>(string xml) where T : class
    {
        try
        {
            var serializer = new XmlSerializer(typeof(T));
            using var reader = new StringReader(xml);
            return serializer.Deserialize(reader) as T;
        }
        catch
        {
            return null;
        }
    }
    
    /// <summary>
    /// 判断是否为可重试的错误
    /// </summary>
    private static bool IsRetryableError(int errorCode)
    {
        // 微信常见可重试错误码
        return errorCode switch
        {
            -1 => true,      // 系统繁忙
            40001 => true,   // access_token 无效（可能过期）
            40014 => true,   // access_token 无效
            42001 => true,   // access_token 过期
            45015 => true,   // 回复时间超过限制（可重试）
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
        var maxRetries = _wechatOptions.MaxRetryCount;
        var retryDelayBase = _wechatOptions.RetryDelayBase;
        
        for (var attempt = 0; attempt <= maxRetries; attempt++)
        {
            try
            {
                var result = await sendFunc();
                
                // 如果是 Token 过期错误，清除缓存后重试
                if (!result.Success && result.ErrorCode is "40001" or "40014" or "42001")
                {
                    _accessToken = null;
                    _tokenExpireTime = DateTime.MinValue;
                    
                    if (attempt < maxRetries)
                    {
                        Logger.LogWarning("WeChat access token expired, refreshing and retrying");
                        continue;
                    }
                }
                
                if (result.Success || !result.ShouldRetry || attempt >= maxRetries)
                {
                    return result;
                }
                
                // 指数退避
                var delay = retryDelayBase * (int)Math.Pow(2, attempt);
                Logger.LogWarning(
                    "WeChat API call failed (attempt {Attempt}/{MaxRetries}), retrying in {Delay}ms. Error: {Error}",
                    attempt + 1, maxRetries + 1, delay, result.ErrorMessage);
                
                await Task.Delay(delay, cancellationToken);
            }
            catch (Exception ex) when (attempt < maxRetries)
            {
                var delay = retryDelayBase * (int)Math.Pow(2, attempt);
                Logger.LogWarning(ex,
                    "WeChat API call threw exception (attempt {Attempt}/{MaxRetries}), retrying in {Delay}ms",
                    attempt + 1, maxRetries + 1, delay);
                
                await Task.Delay(delay, cancellationToken);
            }
        }
        
        return new SendResult(false, ErrorCode: "MAX_RETRIES_EXCEEDED", ErrorMessage: "Maximum retry attempts exceeded");
    }
    
    #endregion
    
    #region 公开辅助方法
    
    /// <summary>
    /// 转换消息为微信格式（用于测试）
    /// </summary>
    public (string MsgType, string Content) ConvertToWeChatFormat(IChatMessage message)
    {
        return message.MessageType switch
        {
            ChatMessageType.Text => (WeChatMsgType.Text, message.Content),
            ChatMessageType.Image => (WeChatMsgType.Image, message.Content),
            ChatMessageType.Audio => (WeChatMsgType.Voice, message.Content),
            _ => (WeChatMsgType.Text, message.Content)
        };
    }
    
    /// <summary>
    /// 获取加解密工具实例（用于测试）
    /// </summary>
    public WeChatCrypto? GetCrypto() => _crypto;
    
    #endregion
}
