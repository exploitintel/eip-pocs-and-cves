using System.ComponentModel.DataAnnotations;

namespace OpenDeepWiki.Entities;

/// <summary>
/// 用户创建的对话应用实体
/// 包含AppId、AppSecret和配置信息，用于嵌入到外部网站
/// </summary>
public class ChatApp : AggregateRoot<Guid>
{
    /// <summary>
    /// 所属用户ID
    /// </summary>
    [Required]
    [StringLength(100)]
    public string UserId { get; set; } = string.Empty;

    /// <summary>
    /// 应用名称
    /// </summary>
    [Required]
    [StringLength(100)]
    public string Name { get; set; } = string.Empty;

    /// <summary>
    /// 应用描述
    /// </summary>
    [StringLength(500)]
    public string? Description { get; set; }

    /// <summary>
    /// 应用图标URL
    /// </summary>
    [StringLength(500)]
    public string? IconUrl { get; set; }

    /// <summary>
    /// 公开的应用ID（用于嵌入脚本）
    /// </summary>
    [Required]
    [StringLength(64)]
    public string AppId { get; set; } = string.Empty;

    /// <summary>
    /// 服务端验证密钥
    /// </summary>
    [Required]
    [StringLength(128)]
    public string AppSecret { get; set; } = string.Empty;

    /// <summary>
    /// 是否启用域名校验
    /// </summary>
    public bool EnableDomainValidation { get; set; } = false;

    /// <summary>
    /// 允许的域名列表（JSON数组）
    /// </summary>
    [StringLength(2000)]
    public string? AllowedDomains { get; set; }

    /// <summary>
    /// AI模型提供商类型（OpenAI、OpenAIResponses、Anthropic）
    /// </summary>
    [Required]
    [StringLength(50)]
    public string ProviderType { get; set; } = "OpenAI";

    [StringLength(100)]
    public string? AiProviderId { get; set; }

    /// <summary>
    /// API密钥
    /// </summary>
    [StringLength(500)]
    public string? ApiKey { get; set; }

    /// <summary>
    /// API基础URL
    /// </summary>
    [StringLength(500)]
    public string? BaseUrl { get; set; }

    /// <summary>
    /// 可用模型列表（JSON数组）
    /// </summary>
    [StringLength(1000)]
    public string? AvailableModels { get; set; }

    /// <summary>
    /// 默认模型
    /// </summary>
    [StringLength(100)]
    public string? DefaultModel { get; set; }

    /// <summary>
    /// 每分钟请求限制
    /// </summary>
    public int? RateLimitPerMinute { get; set; }

    /// <summary>
    /// 是否激活
    /// </summary>
    public bool IsActive { get; set; } = true;
}
