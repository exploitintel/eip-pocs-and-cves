using System.Text.Json.Serialization;

namespace OpenDeepWiki.Chat.Providers.Feishu;

#region Webhook 事件模型

/// <summary>
/// 飞书 Webhook 事件基础结构
/// </summary>
public class FeishuWebhookEvent
{
    /// <summary>
    /// 事件模式（1.0 或 2.0）
    /// </summary>
    [JsonPropertyName("schema")]
    public string? Schema { get; set; }
    
    /// <summary>
    /// 事件头信息（2.0 格式）
    /// </summary>
    [JsonPropertyName("header")]
    public FeishuEventHeader? Header { get; set; }
    
    /// <summary>
    /// 事件内容
    /// </summary>
    [JsonPropertyName("event")]
    public FeishuEventContent? Event { get; set; }
    
    /// <summary>
    /// 验证 Token（1.0 格式）
    /// </summary>
    [JsonPropertyName("token")]
    public string? Token { get; set; }
    
    /// <summary>
    /// 事件类型（1.0 格式）
    /// </summary>
    [JsonPropertyName("type")]
    public string? Type { get; set; }
    
    /// <summary>
    /// 验证挑战码（URL 验证时使用）
    /// </summary>
    [JsonPropertyName("challenge")]
    public string? Challenge { get; set; }
    
    /// <summary>
    /// 加密内容（启用加密时）
    /// </summary>
    [JsonPropertyName("encrypt")]
    public string? Encrypt { get; set; }
}

/// <summary>
/// 飞书事件头信息（2.0 格式）
/// </summary>
public class FeishuEventHeader
{
    [JsonPropertyName("event_id")]
    public string EventId { get; set; } = string.Empty;
    
    [JsonPropertyName("event_type")]
    public string EventType { get; set; } = string.Empty;
    
    [JsonPropertyName("create_time")]
    public string CreateTime { get; set; } = string.Empty;
    
    [JsonPropertyName("token")]
    public string Token { get; set; } = string.Empty;
    
    [JsonPropertyName("app_id")]
    public string AppId { get; set; } = string.Empty;
    
    [JsonPropertyName("tenant_key")]
    public string TenantKey { get; set; } = string.Empty;
}

/// <summary>
/// 飞书事件内容
/// </summary>
public class FeishuEventContent
{
    [JsonPropertyName("sender")]
    public FeishuSender? Sender { get; set; }
    
    [JsonPropertyName("message")]
    public FeishuMessage? Message { get; set; }
}

/// <summary>
/// 飞书消息发送者
/// </summary>
public class FeishuSender
{
    [JsonPropertyName("sender_id")]
    public FeishuSenderId? SenderId { get; set; }
    
    [JsonPropertyName("sender_type")]
    public string SenderType { get; set; } = string.Empty;
    
    [JsonPropertyName("tenant_key")]
    public string TenantKey { get; set; } = string.Empty;
}

/// <summary>
/// 飞书发送者 ID
/// </summary>
public class FeishuSenderId
{
    [JsonPropertyName("union_id")]
    public string UnionId { get; set; } = string.Empty;
    
    [JsonPropertyName("user_id")]
    public string UserId { get; set; } = string.Empty;
    
    [JsonPropertyName("open_id")]
    public string OpenId { get; set; } = string.Empty;
}

#endregion

#region 消息模型

/// <summary>
/// 飞书消息
/// </summary>
public class FeishuMessage
{
    [JsonPropertyName("message_id")]
    public string MessageId { get; set; } = string.Empty;
    
    [JsonPropertyName("root_id")]
    public string? RootId { get; set; }
    
    [JsonPropertyName("parent_id")]
    public string? ParentId { get; set; }
    
    [JsonPropertyName("create_time")]
    public string CreateTime { get; set; } = string.Empty;
    
    [JsonPropertyName("chat_id")]
    public string ChatId { get; set; } = string.Empty;
    
    [JsonPropertyName("chat_type")]
    public string ChatType { get; set; } = string.Empty;
    
    [JsonPropertyName("message_type")]
    public string MessageType { get; set; } = string.Empty;
    
    [JsonPropertyName("content")]
    public string Content { get; set; } = string.Empty;
    
    [JsonPropertyName("mentions")]
    public List<FeishuMention>? Mentions { get; set; }
}

/// <summary>
/// 飞书 @ 提及
/// </summary>
public class FeishuMention
{
    [JsonPropertyName("key")]
    public string Key { get; set; } = string.Empty;
    
    [JsonPropertyName("id")]
    public FeishuSenderId? Id { get; set; }
    
    [JsonPropertyName("name")]
    public string Name { get; set; } = string.Empty;
    
    [JsonPropertyName("tenant_key")]
    public string TenantKey { get; set; } = string.Empty;
}

/// <summary>
/// 飞书文本消息内容
/// </summary>
public class FeishuTextContent
{
    [JsonPropertyName("text")]
    public string Text { get; set; } = string.Empty;
}

/// <summary>
/// 飞书图片消息内容
/// </summary>
public class FeishuImageContent
{
    [JsonPropertyName("image_key")]
    public string ImageKey { get; set; } = string.Empty;
}

#endregion


#region API 响应模型

/// <summary>
/// 飞书 API 基础响应
/// </summary>
public class FeishuApiResponse
{
    [JsonPropertyName("code")]
    public int Code { get; set; }
    
    [JsonPropertyName("msg")]
    public string Message { get; set; } = string.Empty;
}

/// <summary>
/// 飞书 Access Token 响应
/// </summary>
public class FeishuTokenResponse : FeishuApiResponse
{
    [JsonPropertyName("tenant_access_token")]
    public string TenantAccessToken { get; set; } = string.Empty;
    
    [JsonPropertyName("expire")]
    public int Expire { get; set; }
}

/// <summary>
/// 飞书发送消息响应
/// </summary>
public class FeishuSendMessageResponse : FeishuApiResponse
{
    [JsonPropertyName("data")]
    public FeishuSendMessageData? Data { get; set; }
}

/// <summary>
/// 飞书发送消息响应数据
/// </summary>
public class FeishuSendMessageData
{
    [JsonPropertyName("message_id")]
    public string MessageId { get; set; } = string.Empty;
}

#endregion

#region 消息卡片模型

/// <summary>
/// 飞书消息卡片
/// </summary>
public class FeishuCard
{
    [JsonPropertyName("config")]
    public FeishuCardConfig? Config { get; set; }
    
    [JsonPropertyName("header")]
    public FeishuCardHeader? Header { get; set; }
    
    [JsonPropertyName("elements")]
    public List<object>? Elements { get; set; }
}

/// <summary>
/// 飞书卡片配置
/// </summary>
public class FeishuCardConfig
{
    [JsonPropertyName("wide_screen_mode")]
    public bool WideScreenMode { get; set; } = true;
    
    [JsonPropertyName("enable_forward")]
    public bool EnableForward { get; set; } = true;
}

/// <summary>
/// 飞书卡片头部
/// </summary>
public class FeishuCardHeader
{
    [JsonPropertyName("title")]
    public FeishuCardText? Title { get; set; }
    
    [JsonPropertyName("template")]
    public string Template { get; set; } = "blue";
}

/// <summary>
/// 飞书卡片文本
/// </summary>
public class FeishuCardText
{
    [JsonPropertyName("tag")]
    public string Tag { get; set; } = "plain_text";
    
    [JsonPropertyName("content")]
    public string Content { get; set; } = string.Empty;
}

/// <summary>
/// 飞书卡片 Markdown 元素
/// </summary>
public class FeishuCardMarkdown
{
    [JsonPropertyName("tag")]
    public string Tag { get; set; } = "markdown";
    
    [JsonPropertyName("content")]
    public string Content { get; set; } = string.Empty;
}

/// <summary>
/// 飞书卡片分割线
/// </summary>
public class FeishuCardDivider
{
    [JsonPropertyName("tag")]
    public string Tag { get; set; } = "hr";
}

#endregion

#region 发送消息请求模型

/// <summary>
/// 飞书发送消息请求
/// </summary>
public class FeishuSendMessageRequest
{
    [JsonPropertyName("receive_id")]
    public string ReceiveId { get; set; } = string.Empty;
    
    [JsonPropertyName("msg_type")]
    public string MsgType { get; set; } = string.Empty;
    
    [JsonPropertyName("content")]
    public string Content { get; set; } = string.Empty;
}

#endregion
