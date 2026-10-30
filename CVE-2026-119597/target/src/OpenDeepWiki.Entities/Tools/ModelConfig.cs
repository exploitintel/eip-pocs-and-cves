using System.ComponentModel.DataAnnotations;

namespace OpenDeepWiki.Entities;

/// <summary>
/// AI 模型配置实体
/// </summary>
public class ModelConfig : AggregateRoot<string>
{
    /// <summary>
    /// 模型显示名称
    /// </summary>
    [Required]
    [StringLength(100)]
    public string Name { get; set; } = string.Empty;

    /// <summary>
    /// 模型提供商（OpenAI, Anthropic, AzureOpenAI 等）
    /// </summary>
    [Required]
    [StringLength(50)]
    public string Provider { get; set; } = string.Empty;

    [StringLength(100)]
    public string? AiProviderId { get; set; }

    /// <summary>
    /// 模型ID
    /// </summary>
    [Required]
    [StringLength(100)]
    public string ModelId { get; set; } = string.Empty;

    /// <summary>
    /// API 端点
    /// </summary>
    [StringLength(500)]
    public string? Endpoint { get; set; }

    /// <summary>
    /// API 密钥
    /// </summary>
    [StringLength(500)]
    public string? ApiKey { get; set; }

    /// <summary>
    /// 是否为默认模型
    /// </summary>
    public bool IsDefault { get; set; } = false;

    /// <summary>
    /// 是否启用
    /// </summary>
    public bool IsActive { get; set; } = true;

    /// <summary>
    /// 模型描述
    /// </summary>
    [StringLength(500)]
    public string? Description { get; set; }
}
