using System.Text.Json.Serialization;
using System.Xml.Serialization;

namespace OpenDeepWiki.Chat.Providers.WeChat;

#region XML 消息模型

/// <summary>
/// 微信接收消息基础结构（XML 格式）
/// </summary>
[XmlRoot("xml")]
public class WeChatXmlMessage
{
    /// <summary>
    /// 开发者微信号
    /// </summary>
    [XmlElement("ToUserName")]
    public string ToUserName { get; set; } = string.Empty;
    
    /// <summary>
    /// 发送方帐号（OpenID）
    /// </summary>
    [XmlElement("FromUserName")]
    public string FromUserName { get; set; } = string.Empty;
    
    /// <summary>
    /// 消息创建时间（整型）
    /// </summary>
    [XmlElement("CreateTime")]
    public long CreateTime { get; set; }
    
    /// <summary>
    /// 消息类型：text、image、voice、video、shortvideo、location、link、event
    /// </summary>
    [XmlElement("MsgType")]
    public string MsgType { get; set; } = string.Empty;
    
    /// <summary>
    /// 消息 ID（64位整型）
    /// </summary>
    [XmlElement("MsgId")]
    public long MsgId { get; set; }
    
    /// <summary>
    /// 消息数据 ID（用于消息排重）
    /// </summary>
    [XmlElement("MsgDataId")]
    public string? MsgDataId { get; set; }
    
    /// <summary>
    /// 文本消息内容
    /// </summary>
    [XmlElement("Content")]
    public string? Content { get; set; }
    
    /// <summary>
    /// 图片链接（由系统生成）
    /// </summary>
    [XmlElement("PicUrl")]
    public string? PicUrl { get; set; }
    
    /// <summary>
    /// 图片/语音/视频消息媒体 ID
    /// </summary>
    [XmlElement("MediaId")]
    public string? MediaId { get; set; }
    
    /// <summary>
    /// 语音格式：amr、speex
    /// </summary>
    [XmlElement("Format")]
    public string? Format { get; set; }
    
    /// <summary>
    /// 语音识别结果（开通语音识别后才有）
    /// </summary>
    [XmlElement("Recognition")]
    public string? Recognition { get; set; }
    
    /// <summary>
    /// 视频消息缩略图的媒体 ID
    /// </summary>
    [XmlElement("ThumbMediaId")]
    public string? ThumbMediaId { get; set; }
    
    /// <summary>
    /// 地理位置纬度
    /// </summary>
    [XmlElement("Location_X")]
    public double? LocationX { get; set; }
    
    /// <summary>
    /// 地理位置经度
    /// </summary>
    [XmlElement("Location_Y")]
    public double? LocationY { get; set; }
    
    /// <summary>
    /// 地图缩放大小
    /// </summary>
    [XmlElement("Scale")]
    public int? Scale { get; set; }
    
    /// <summary>
    /// 地理位置信息
    /// </summary>
    [XmlElement("Label")]
    public string? Label { get; set; }
    
    /// <summary>
    /// 消息标题
    /// </summary>
    [XmlElement("Title")]
    public string? Title { get; set; }
    
    /// <summary>
    /// 消息描述
    /// </summary>
    [XmlElement("Description")]
    public string? Description { get; set; }
    
    /// <summary>
    /// 消息链接
    /// </summary>
    [XmlElement("Url")]
    public string? Url { get; set; }
    
    /// <summary>
    /// 事件类型（仅事件消息有）
    /// </summary>
    [XmlElement("Event")]
    public string? Event { get; set; }
    
    /// <summary>
    /// 事件 KEY 值
    /// </summary>
    [XmlElement("EventKey")]
    public string? EventKey { get; set; }
    
    /// <summary>
    /// 二维码的 ticket
    /// </summary>
    [XmlElement("Ticket")]
    public string? Ticket { get; set; }
}

/// <summary>
/// 微信加密消息结构
/// </summary>
[XmlRoot("xml")]
public class WeChatEncryptedMessage
{
    /// <summary>
    /// 加密的消息内容
    /// </summary>
    [XmlElement("Encrypt")]
    public string Encrypt { get; set; } = string.Empty;
    
    /// <summary>
    /// 消息签名
    /// </summary>
    [XmlElement("MsgSignature")]
    public string? MsgSignature { get; set; }
    
    /// <summary>
    /// 时间戳
    /// </summary>
    [XmlElement("TimeStamp")]
    public string? TimeStamp { get; set; }
    
    /// <summary>
    /// 随机数
    /// </summary>
    [XmlElement("Nonce")]
    public string? Nonce { get; set; }
}

#endregion

#region API 响应模型

/// <summary>
/// 微信 API 基础响应
/// </summary>
public class WeChatApiResponse
{
    /// <summary>
    /// 错误码（0 表示成功）
    /// </summary>
    [JsonPropertyName("errcode")]
    public int ErrorCode { get; set; }
    
    /// <summary>
    /// 错误信息
    /// </summary>
    [JsonPropertyName("errmsg")]
    public string ErrorMessage { get; set; } = string.Empty;
}

/// <summary>
/// 微信 Access Token 响应
/// </summary>
public class WeChatTokenResponse : WeChatApiResponse
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
/// 微信客服消息发送响应
/// </summary>
public class WeChatSendMessageResponse : WeChatApiResponse
{
    /// <summary>
    /// 消息 ID（部分接口返回）
    /// </summary>
    [JsonPropertyName("msgid")]
    public long? MsgId { get; set; }
}

#endregion

#region 客服消息请求模型

/// <summary>
/// 微信客服消息基础请求
/// </summary>
public class WeChatCustomMessageRequest
{
    /// <summary>
    /// 接收者 OpenID
    /// </summary>
    [JsonPropertyName("touser")]
    public string ToUser { get; set; } = string.Empty;
    
    /// <summary>
    /// 消息类型：text、image、voice、video、music、news、mpnews、msgmenu、wxcard、miniprogrampage
    /// </summary>
    [JsonPropertyName("msgtype")]
    public string MsgType { get; set; } = string.Empty;
    
    /// <summary>
    /// 文本消息内容
    /// </summary>
    [JsonPropertyName("text")]
    public WeChatTextContent? Text { get; set; }
    
    /// <summary>
    /// 图片消息内容
    /// </summary>
    [JsonPropertyName("image")]
    public WeChatMediaContent? Image { get; set; }
    
    /// <summary>
    /// 语音消息内容
    /// </summary>
    [JsonPropertyName("voice")]
    public WeChatMediaContent? Voice { get; set; }
    
    /// <summary>
    /// 视频消息内容
    /// </summary>
    [JsonPropertyName("video")]
    public WeChatVideoContent? Video { get; set; }
    
    /// <summary>
    /// 音乐消息内容
    /// </summary>
    [JsonPropertyName("music")]
    public WeChatMusicContent? Music { get; set; }
    
    /// <summary>
    /// 图文消息内容
    /// </summary>
    [JsonPropertyName("news")]
    public WeChatNewsContent? News { get; set; }
}


/// <summary>
/// 微信文本消息内容
/// </summary>
public class WeChatTextContent
{
    /// <summary>
    /// 文本内容
    /// </summary>
    [JsonPropertyName("content")]
    public string Content { get; set; } = string.Empty;
}

/// <summary>
/// 微信媒体消息内容（图片、语音）
/// </summary>
public class WeChatMediaContent
{
    /// <summary>
    /// 媒体 ID
    /// </summary>
    [JsonPropertyName("media_id")]
    public string MediaId { get; set; } = string.Empty;
}

/// <summary>
/// 微信视频消息内容
/// </summary>
public class WeChatVideoContent
{
    /// <summary>
    /// 媒体 ID
    /// </summary>
    [JsonPropertyName("media_id")]
    public string MediaId { get; set; } = string.Empty;
    
    /// <summary>
    /// 缩略图媒体 ID
    /// </summary>
    [JsonPropertyName("thumb_media_id")]
    public string? ThumbMediaId { get; set; }
    
    /// <summary>
    /// 视频标题
    /// </summary>
    [JsonPropertyName("title")]
    public string? Title { get; set; }
    
    /// <summary>
    /// 视频描述
    /// </summary>
    [JsonPropertyName("description")]
    public string? Description { get; set; }
}

/// <summary>
/// 微信音乐消息内容
/// </summary>
public class WeChatMusicContent
{
    /// <summary>
    /// 音乐标题
    /// </summary>
    [JsonPropertyName("title")]
    public string? Title { get; set; }
    
    /// <summary>
    /// 音乐描述
    /// </summary>
    [JsonPropertyName("description")]
    public string? Description { get; set; }
    
    /// <summary>
    /// 音乐链接
    /// </summary>
    [JsonPropertyName("musicurl")]
    public string MusicUrl { get; set; } = string.Empty;
    
    /// <summary>
    /// 高品质音乐链接
    /// </summary>
    [JsonPropertyName("hqmusicurl")]
    public string? HqMusicUrl { get; set; }
    
    /// <summary>
    /// 缩略图媒体 ID
    /// </summary>
    [JsonPropertyName("thumb_media_id")]
    public string ThumbMediaId { get; set; } = string.Empty;
}

/// <summary>
/// 微信图文消息内容
/// </summary>
public class WeChatNewsContent
{
    /// <summary>
    /// 图文消息列表
    /// </summary>
    [JsonPropertyName("articles")]
    public List<WeChatArticle> Articles { get; set; } = new();
}

/// <summary>
/// 微信图文消息文章
/// </summary>
public class WeChatArticle
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
    /// 点击后跳转的链接
    /// </summary>
    [JsonPropertyName("url")]
    public string Url { get; set; } = string.Empty;
    
    /// <summary>
    /// 图文消息的图片链接
    /// </summary>
    [JsonPropertyName("picurl")]
    public string? PicUrl { get; set; }
}

#endregion

#region 消息类型常量

/// <summary>
/// 微信消息类型常量
/// </summary>
public static class WeChatMsgType
{
    /// <summary>
    /// 文本消息
    /// </summary>
    public const string Text = "text";
    
    /// <summary>
    /// 图片消息
    /// </summary>
    public const string Image = "image";
    
    /// <summary>
    /// 语音消息
    /// </summary>
    public const string Voice = "voice";
    
    /// <summary>
    /// 视频消息
    /// </summary>
    public const string Video = "video";
    
    /// <summary>
    /// 小视频消息
    /// </summary>
    public const string ShortVideo = "shortvideo";
    
    /// <summary>
    /// 地理位置消息
    /// </summary>
    public const string Location = "location";
    
    /// <summary>
    /// 链接消息
    /// </summary>
    public const string Link = "link";
    
    /// <summary>
    /// 事件消息
    /// </summary>
    public const string Event = "event";
    
    /// <summary>
    /// 音乐消息
    /// </summary>
    public const string Music = "music";
    
    /// <summary>
    /// 图文消息
    /// </summary>
    public const string News = "news";
}

/// <summary>
/// 微信事件类型常量
/// </summary>
public static class WeChatEventType
{
    /// <summary>
    /// 关注事件
    /// </summary>
    public const string Subscribe = "subscribe";
    
    /// <summary>
    /// 取消关注事件
    /// </summary>
    public const string Unsubscribe = "unsubscribe";
    
    /// <summary>
    /// 扫描带参数二维码事件
    /// </summary>
    public const string Scan = "SCAN";
    
    /// <summary>
    /// 上报地理位置事件
    /// </summary>
    public const string Location = "LOCATION";
    
    /// <summary>
    /// 点击菜单拉取消息事件
    /// </summary>
    public const string Click = "CLICK";
    
    /// <summary>
    /// 点击菜单跳转链接事件
    /// </summary>
    public const string View = "VIEW";
}

#endregion
