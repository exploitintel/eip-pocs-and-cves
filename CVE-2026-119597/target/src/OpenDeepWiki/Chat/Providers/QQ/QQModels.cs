using System.Text.Json.Serialization;

namespace OpenDeepWiki.Chat.Providers.QQ;

#region Webhook 事件模型

/// <summary>
/// QQ 机器人 Webhook 事件基础结构
/// </summary>
public class QQWebhookEvent
{
    /// <summary>
    /// 操作码
    /// </summary>
    [JsonPropertyName("op")]
    public int OpCode { get; set; }
    
    /// <summary>
    /// 事件序列号
    /// </summary>
    [JsonPropertyName("s")]
    public int? Sequence { get; set; }
    
    /// <summary>
    /// 事件类型
    /// </summary>
    [JsonPropertyName("t")]
    public string? EventType { get; set; }
    
    /// <summary>
    /// 事件 ID
    /// </summary>
    [JsonPropertyName("id")]
    public string? EventId { get; set; }
    
    /// <summary>
    /// 事件数据
    /// </summary>
    [JsonPropertyName("d")]
    public QQEventData? Data { get; set; }
}

/// <summary>
/// QQ 事件数据
/// </summary>
public class QQEventData
{
    /// <summary>
    /// 消息 ID
    /// </summary>
    [JsonPropertyName("id")]
    public string Id { get; set; } = string.Empty;
    
    /// <summary>
    /// 消息内容
    /// </summary>
    [JsonPropertyName("content")]
    public string Content { get; set; } = string.Empty;
    
    /// <summary>
    /// 消息时间戳
    /// </summary>
    [JsonPropertyName("timestamp")]
    public string Timestamp { get; set; } = string.Empty;
    
    /// <summary>
    /// 频道 ID
    /// </summary>
    [JsonPropertyName("channel_id")]
    public string? ChannelId { get; set; }
    
    /// <summary>
    /// 子频道 ID
    /// </summary>
    [JsonPropertyName("guild_id")]
    public string? GuildId { get; set; }
    
    /// <summary>
    /// 群聊 ID
    /// </summary>
    [JsonPropertyName("group_id")]
    public string? GroupId { get; set; }
    
    /// <summary>
    /// 群聊 OpenID
    /// </summary>
    [JsonPropertyName("group_openid")]
    public string? GroupOpenId { get; set; }
    
    /// <summary>
    /// 消息作者
    /// </summary>
    [JsonPropertyName("author")]
    public QQAuthor? Author { get; set; }
    
    /// <summary>
    /// 消息成员信息
    /// </summary>
    [JsonPropertyName("member")]
    public QQMember? Member { get; set; }
    
    /// <summary>
    /// @ 提及列表
    /// </summary>
    [JsonPropertyName("mentions")]
    public List<QQMention>? Mentions { get; set; }
    
    /// <summary>
    /// 附件列表
    /// </summary>
    [JsonPropertyName("attachments")]
    public List<QQAttachment>? Attachments { get; set; }
    
    /// <summary>
    /// 消息序列号（用于被动回复）
    /// </summary>
    [JsonPropertyName("msg_seq")]
    public int? MsgSeq { get; set; }
}

/// <summary>
/// QQ 消息作者
/// </summary>
public class QQAuthor
{
    /// <summary>
    /// 用户 ID
    /// </summary>
    [JsonPropertyName("id")]
    public string Id { get; set; } = string.Empty;
    
    /// <summary>
    /// 用户 OpenID（群聊场景）
    /// </summary>
    [JsonPropertyName("member_openid")]
    public string? MemberOpenId { get; set; }
    
    /// <summary>
    /// 用户名
    /// </summary>
    [JsonPropertyName("username")]
    public string Username { get; set; } = string.Empty;
    
    /// <summary>
    /// 头像 URL
    /// </summary>
    [JsonPropertyName("avatar")]
    public string? Avatar { get; set; }
    
    /// <summary>
    /// 是否是机器人
    /// </summary>
    [JsonPropertyName("bot")]
    public bool IsBot { get; set; }
}

/// <summary>
/// QQ 成员信息
/// </summary>
public class QQMember
{
    /// <summary>
    /// 加入时间
    /// </summary>
    [JsonPropertyName("joined_at")]
    public string JoinedAt { get; set; } = string.Empty;
    
    /// <summary>
    /// 昵称
    /// </summary>
    [JsonPropertyName("nick")]
    public string? Nick { get; set; }
    
    /// <summary>
    /// 角色列表
    /// </summary>
    [JsonPropertyName("roles")]
    public List<string>? Roles { get; set; }
}

/// <summary>
/// QQ @ 提及
/// </summary>
public class QQMention
{
    /// <summary>
    /// 被 @ 的用户 ID
    /// </summary>
    [JsonPropertyName("id")]
    public string Id { get; set; } = string.Empty;
    
    /// <summary>
    /// 被 @ 的用户名
    /// </summary>
    [JsonPropertyName("username")]
    public string Username { get; set; } = string.Empty;
    
    /// <summary>
    /// 是否是机器人
    /// </summary>
    [JsonPropertyName("bot")]
    public bool IsBot { get; set; }
}

/// <summary>
/// QQ 附件
/// </summary>
public class QQAttachment
{
    /// <summary>
    /// 附件 ID
    /// </summary>
    [JsonPropertyName("id")]
    public string Id { get; set; } = string.Empty;
    
    /// <summary>
    /// 文件名
    /// </summary>
    [JsonPropertyName("filename")]
    public string Filename { get; set; } = string.Empty;
    
    /// <summary>
    /// 内容类型
    /// </summary>
    [JsonPropertyName("content_type")]
    public string ContentType { get; set; } = string.Empty;
    
    /// <summary>
    /// 文件大小
    /// </summary>
    [JsonPropertyName("size")]
    public long Size { get; set; }
    
    /// <summary>
    /// 文件 URL
    /// </summary>
    [JsonPropertyName("url")]
    public string Url { get; set; } = string.Empty;
    
    /// <summary>
    /// 图片宽度
    /// </summary>
    [JsonPropertyName("width")]
    public int? Width { get; set; }
    
    /// <summary>
    /// 图片高度
    /// </summary>
    [JsonPropertyName("height")]
    public int? Height { get; set; }
}

#endregion

#region C2C 私聊消息模型

/// <summary>
/// QQ C2C 私聊消息事件
/// </summary>
public class QQC2CMessageEvent
{
    /// <summary>
    /// 消息 ID
    /// </summary>
    [JsonPropertyName("id")]
    public string Id { get; set; } = string.Empty;
    
    /// <summary>
    /// 消息内容
    /// </summary>
    [JsonPropertyName("content")]
    public string Content { get; set; } = string.Empty;
    
    /// <summary>
    /// 消息时间戳
    /// </summary>
    [JsonPropertyName("timestamp")]
    public string Timestamp { get; set; } = string.Empty;
    
    /// <summary>
    /// 消息作者
    /// </summary>
    [JsonPropertyName("author")]
    public QQC2CAuthor? Author { get; set; }
    
    /// <summary>
    /// 附件列表
    /// </summary>
    [JsonPropertyName("attachments")]
    public List<QQAttachment>? Attachments { get; set; }
}

/// <summary>
/// QQ C2C 消息作者
/// </summary>
public class QQC2CAuthor
{
    /// <summary>
    /// 用户 OpenID
    /// </summary>
    [JsonPropertyName("user_openid")]
    public string UserOpenId { get; set; } = string.Empty;
}

#endregion

#region 群聊消息模型

/// <summary>
/// QQ 群聊消息事件
/// </summary>
public class QQGroupMessageEvent
{
    /// <summary>
    /// 消息 ID
    /// </summary>
    [JsonPropertyName("id")]
    public string Id { get; set; } = string.Empty;
    
    /// <summary>
    /// 消息内容
    /// </summary>
    [JsonPropertyName("content")]
    public string Content { get; set; } = string.Empty;
    
    /// <summary>
    /// 消息时间戳
    /// </summary>
    [JsonPropertyName("timestamp")]
    public string Timestamp { get; set; } = string.Empty;
    
    /// <summary>
    /// 群聊 OpenID
    /// </summary>
    [JsonPropertyName("group_openid")]
    public string GroupOpenId { get; set; } = string.Empty;
    
    /// <summary>
    /// 消息作者
    /// </summary>
    [JsonPropertyName("author")]
    public QQGroupAuthor? Author { get; set; }
    
    /// <summary>
    /// 附件列表
    /// </summary>
    [JsonPropertyName("attachments")]
    public List<QQAttachment>? Attachments { get; set; }
}

/// <summary>
/// QQ 群聊消息作者
/// </summary>
public class QQGroupAuthor
{
    /// <summary>
    /// 成员 OpenID
    /// </summary>
    [JsonPropertyName("member_openid")]
    public string MemberOpenId { get; set; } = string.Empty;
}

#endregion


#region API 响应模型

/// <summary>
/// QQ API 基础响应
/// </summary>
public class QQApiResponse
{
    /// <summary>
    /// 错误码（0 表示成功）
    /// </summary>
    [JsonPropertyName("code")]
    public int Code { get; set; }
    
    /// <summary>
    /// 错误信息
    /// </summary>
    [JsonPropertyName("message")]
    public string Message { get; set; } = string.Empty;
}

/// <summary>
/// QQ Access Token 响应
/// </summary>
public class QQTokenResponse
{
    /// <summary>
    /// Access Token
    /// </summary>
    [JsonPropertyName("access_token")]
    public string AccessToken { get; set; } = string.Empty;
    
    /// <summary>
    /// 过期时间（秒）
    /// </summary>
    [JsonPropertyName("expires_in")]
    public int ExpiresIn { get; set; }
}

/// <summary>
/// QQ 发送消息响应
/// </summary>
public class QQSendMessageResponse : QQApiResponse
{
    /// <summary>
    /// 消息 ID
    /// </summary>
    [JsonPropertyName("id")]
    public string? Id { get; set; }
    
    /// <summary>
    /// 消息时间戳
    /// </summary>
    [JsonPropertyName("timestamp")]
    public string? Timestamp { get; set; }
}

#endregion

#region 发送消息请求模型

/// <summary>
/// QQ 发送频道消息请求
/// </summary>
public class QQSendChannelMessageRequest
{
    /// <summary>
    /// 消息内容
    /// </summary>
    [JsonPropertyName("content")]
    public string? Content { get; set; }
    
    /// <summary>
    /// 消息嵌入内容
    /// </summary>
    [JsonPropertyName("embed")]
    public QQEmbed? Embed { get; set; }
    
    /// <summary>
    /// Ark 消息
    /// </summary>
    [JsonPropertyName("ark")]
    public QQArk? Ark { get; set; }
    
    /// <summary>
    /// 引用消息 ID
    /// </summary>
    [JsonPropertyName("msg_id")]
    public string? MsgId { get; set; }
    
    /// <summary>
    /// 图片 URL
    /// </summary>
    [JsonPropertyName("image")]
    public string? Image { get; set; }
    
    /// <summary>
    /// Markdown 消息
    /// </summary>
    [JsonPropertyName("markdown")]
    public QQMarkdown? Markdown { get; set; }
}

/// <summary>
/// QQ 发送群聊消息请求
/// </summary>
public class QQSendGroupMessageRequest
{
    /// <summary>
    /// 消息内容
    /// </summary>
    [JsonPropertyName("content")]
    public string? Content { get; set; }
    
    /// <summary>
    /// 消息类型：0 文本，1 图文混排，2 Markdown，3 Ark，4 Embed，7 富媒体
    /// </summary>
    [JsonPropertyName("msg_type")]
    public int MsgType { get; set; }
    
    /// <summary>
    /// 引用消息 ID（被动回复必填）
    /// </summary>
    [JsonPropertyName("msg_id")]
    public string? MsgId { get; set; }
    
    /// <summary>
    /// 消息序列号（被动回复必填）
    /// </summary>
    [JsonPropertyName("msg_seq")]
    public int? MsgSeq { get; set; }
    
    /// <summary>
    /// 富媒体消息
    /// </summary>
    [JsonPropertyName("media")]
    public QQMedia? Media { get; set; }
    
    /// <summary>
    /// Markdown 消息
    /// </summary>
    [JsonPropertyName("markdown")]
    public QQMarkdown? Markdown { get; set; }
    
    /// <summary>
    /// Ark 消息
    /// </summary>
    [JsonPropertyName("ark")]
    public QQArk? Ark { get; set; }
}

/// <summary>
/// QQ 发送 C2C 私聊消息请求
/// </summary>
public class QQSendC2CMessageRequest
{
    /// <summary>
    /// 消息内容
    /// </summary>
    [JsonPropertyName("content")]
    public string? Content { get; set; }
    
    /// <summary>
    /// 消息类型：0 文本，1 图文混排，2 Markdown，3 Ark，4 Embed，7 富媒体
    /// </summary>
    [JsonPropertyName("msg_type")]
    public int MsgType { get; set; }
    
    /// <summary>
    /// 引用消息 ID（被动回复必填）
    /// </summary>
    [JsonPropertyName("msg_id")]
    public string? MsgId { get; set; }
    
    /// <summary>
    /// 消息序列号
    /// </summary>
    [JsonPropertyName("msg_seq")]
    public int? MsgSeq { get; set; }
    
    /// <summary>
    /// 富媒体消息
    /// </summary>
    [JsonPropertyName("media")]
    public QQMedia? Media { get; set; }
    
    /// <summary>
    /// Markdown 消息
    /// </summary>
    [JsonPropertyName("markdown")]
    public QQMarkdown? Markdown { get; set; }
    
    /// <summary>
    /// Ark 消息
    /// </summary>
    [JsonPropertyName("ark")]
    public QQArk? Ark { get; set; }
}

/// <summary>
/// QQ Embed 消息
/// </summary>
public class QQEmbed
{
    /// <summary>
    /// 标题
    /// </summary>
    [JsonPropertyName("title")]
    public string Title { get; set; } = string.Empty;
    
    /// <summary>
    /// 描述
    /// </summary>
    [JsonPropertyName("description")]
    public string? Description { get; set; }
    
    /// <summary>
    /// 提示文本
    /// </summary>
    [JsonPropertyName("prompt")]
    public string? Prompt { get; set; }
    
    /// <summary>
    /// 缩略图
    /// </summary>
    [JsonPropertyName("thumbnail")]
    public QQThumbnail? Thumbnail { get; set; }
    
    /// <summary>
    /// 字段列表
    /// </summary>
    [JsonPropertyName("fields")]
    public List<QQEmbedField>? Fields { get; set; }
}

/// <summary>
/// QQ Embed 字段
/// </summary>
public class QQEmbedField
{
    /// <summary>
    /// 字段名
    /// </summary>
    [JsonPropertyName("name")]
    public string Name { get; set; } = string.Empty;
}

/// <summary>
/// QQ 缩略图
/// </summary>
public class QQThumbnail
{
    /// <summary>
    /// 图片 URL
    /// </summary>
    [JsonPropertyName("url")]
    public string Url { get; set; } = string.Empty;
}

/// <summary>
/// QQ Ark 消息
/// </summary>
public class QQArk
{
    /// <summary>
    /// Ark 模板 ID
    /// </summary>
    [JsonPropertyName("template_id")]
    public int TemplateId { get; set; }
    
    /// <summary>
    /// Ark 键值对列表
    /// </summary>
    [JsonPropertyName("kv")]
    public List<QQArkKv>? Kv { get; set; }
}

/// <summary>
/// QQ Ark 键值对
/// </summary>
public class QQArkKv
{
    /// <summary>
    /// 键
    /// </summary>
    [JsonPropertyName("key")]
    public string Key { get; set; } = string.Empty;
    
    /// <summary>
    /// 值
    /// </summary>
    [JsonPropertyName("value")]
    public string? Value { get; set; }
    
    /// <summary>
    /// 对象列表
    /// </summary>
    [JsonPropertyName("obj")]
    public List<QQArkObj>? Obj { get; set; }
}

/// <summary>
/// QQ Ark 对象
/// </summary>
public class QQArkObj
{
    /// <summary>
    /// 对象键值对列表
    /// </summary>
    [JsonPropertyName("obj_kv")]
    public List<QQArkObjKv>? ObjKv { get; set; }
}

/// <summary>
/// QQ Ark 对象键值对
/// </summary>
public class QQArkObjKv
{
    /// <summary>
    /// 键
    /// </summary>
    [JsonPropertyName("key")]
    public string Key { get; set; } = string.Empty;
    
    /// <summary>
    /// 值
    /// </summary>
    [JsonPropertyName("value")]
    public string Value { get; set; } = string.Empty;
}

/// <summary>
/// QQ Markdown 消息
/// </summary>
public class QQMarkdown
{
    /// <summary>
    /// Markdown 模板 ID
    /// </summary>
    [JsonPropertyName("template_id")]
    public int? TemplateId { get; set; }
    
    /// <summary>
    /// 自定义 Markdown 内容
    /// </summary>
    [JsonPropertyName("content")]
    public string? Content { get; set; }
    
    /// <summary>
    /// 模板参数
    /// </summary>
    [JsonPropertyName("params")]
    public List<QQMarkdownParam>? Params { get; set; }
}

/// <summary>
/// QQ Markdown 参数
/// </summary>
public class QQMarkdownParam
{
    /// <summary>
    /// 参数键
    /// </summary>
    [JsonPropertyName("key")]
    public string Key { get; set; } = string.Empty;
    
    /// <summary>
    /// 参数值列表
    /// </summary>
    [JsonPropertyName("values")]
    public List<string>? Values { get; set; }
}

/// <summary>
/// QQ 富媒体消息
/// </summary>
public class QQMedia
{
    /// <summary>
    /// 文件信息（上传后获得）
    /// </summary>
    [JsonPropertyName("file_info")]
    public string FileInfo { get; set; } = string.Empty;
}

#endregion

#region WebSocket 相关模型

/// <summary>
/// QQ WebSocket 网关响应
/// </summary>
public class QQGatewayResponse
{
    /// <summary>
    /// WebSocket 连接地址
    /// </summary>
    [JsonPropertyName("url")]
    public string Url { get; set; } = string.Empty;
}

/// <summary>
/// QQ WebSocket 鉴权数据
/// </summary>
public class QQIdentifyData
{
    /// <summary>
    /// 机器人 Token
    /// </summary>
    [JsonPropertyName("token")]
    public string Token { get; set; } = string.Empty;
    
    /// <summary>
    /// 订阅的事件类型
    /// </summary>
    [JsonPropertyName("intents")]
    public int Intents { get; set; }
    
    /// <summary>
    /// 分片信息 [当前分片, 总分片数]
    /// </summary>
    [JsonPropertyName("shard")]
    public int[]? Shard { get; set; }
}

/// <summary>
/// QQ WebSocket 心跳数据
/// </summary>
public class QQHeartbeatData
{
    /// <summary>
    /// 最后一次收到的消息序列号
    /// </summary>
    [JsonPropertyName("d")]
    public int? LastSequence { get; set; }
}

/// <summary>
/// QQ WebSocket Ready 事件数据
/// </summary>
public class QQReadyData
{
    /// <summary>
    /// 协议版本
    /// </summary>
    [JsonPropertyName("version")]
    public int Version { get; set; }
    
    /// <summary>
    /// 会话 ID
    /// </summary>
    [JsonPropertyName("session_id")]
    public string SessionId { get; set; } = string.Empty;
    
    /// <summary>
    /// 机器人用户信息
    /// </summary>
    [JsonPropertyName("user")]
    public QQAuthor? User { get; set; }
    
    /// <summary>
    /// 分片信息
    /// </summary>
    [JsonPropertyName("shard")]
    public int[]? Shard { get; set; }
}

#endregion

#region 操作码常量

/// <summary>
/// QQ WebSocket 操作码
/// </summary>
public static class QQOpCode
{
    /// <summary>
    /// 服务端推送消息
    /// </summary>
    public const int Dispatch = 0;
    
    /// <summary>
    /// 客户端发送心跳
    /// </summary>
    public const int Heartbeat = 1;
    
    /// <summary>
    /// 客户端发送鉴权
    /// </summary>
    public const int Identify = 2;
    
    /// <summary>
    /// 客户端恢复连接
    /// </summary>
    public const int Resume = 6;
    
    /// <summary>
    /// 服务端要求重连
    /// </summary>
    public const int Reconnect = 7;
    
    /// <summary>
    /// 鉴权失败
    /// </summary>
    public const int InvalidSession = 9;
    
    /// <summary>
    /// 服务端发送 Hello
    /// </summary>
    public const int Hello = 10;
    
    /// <summary>
    /// 心跳确认
    /// </summary>
    public const int HeartbeatAck = 11;
    
    /// <summary>
    /// HTTP 回调确认
    /// </summary>
    public const int HttpCallbackAck = 12;
}

/// <summary>
/// QQ 事件类型常量
/// </summary>
public static class QQEventType
{
    /// <summary>
    /// 连接就绪
    /// </summary>
    public const string Ready = "READY";
    
    /// <summary>
    /// 恢复成功
    /// </summary>
    public const string Resumed = "RESUMED";
    
    /// <summary>
    /// 频道消息（公域）
    /// </summary>
    public const string AtMessageCreate = "AT_MESSAGE_CREATE";
    
    /// <summary>
    /// 频道消息（私域）
    /// </summary>
    public const string MessageCreate = "MESSAGE_CREATE";
    
    /// <summary>
    /// 私信消息
    /// </summary>
    public const string DirectMessageCreate = "DIRECT_MESSAGE_CREATE";
    
    /// <summary>
    /// 群聊 @ 消息
    /// </summary>
    public const string GroupAtMessageCreate = "GROUP_AT_MESSAGE_CREATE";
    
    /// <summary>
    /// C2C 私聊消息
    /// </summary>
    public const string C2CMessageCreate = "C2C_MESSAGE_CREATE";
}

/// <summary>
/// QQ 消息类型常量
/// </summary>
public static class QQMsgType
{
    /// <summary>
    /// 文本消息
    /// </summary>
    public const int Text = 0;
    
    /// <summary>
    /// 图文混排
    /// </summary>
    public const int Mixed = 1;
    
    /// <summary>
    /// Markdown
    /// </summary>
    public const int Markdown = 2;
    
    /// <summary>
    /// Ark
    /// </summary>
    public const int Ark = 3;
    
    /// <summary>
    /// Embed
    /// </summary>
    public const int Embed = 4;
    
    /// <summary>
    /// 富媒体
    /// </summary>
    public const int Media = 7;
}

#endregion
