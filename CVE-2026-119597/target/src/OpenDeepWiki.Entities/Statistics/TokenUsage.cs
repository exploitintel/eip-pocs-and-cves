using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace OpenDeepWiki.Entities;

/// <summary>
/// Token 消耗记录实体
/// </summary>
public class TokenUsage : AggregateRoot<string>
{
    /// <summary>
    /// 关联的仓库ID（可选）
    /// </summary>
    [StringLength(36)]
    public string? RepositoryId { get; set; }

    /// <summary>
    /// 关联的用户ID（可选）
    /// </summary>
    [StringLength(36)]
    public string? UserId { get; set; }

    /// <summary>
    /// 输入 Token 数量
    /// </summary>
    public int InputTokens { get; set; }

    /// <summary>
    /// 输出 Token 数量
    /// </summary>
    public int OutputTokens { get; set; }

    /// <summary>
    /// Input tokens that provider reported as prompt cache hits.
    /// </summary>
    public int CachedInputTokens { get; set; }

    /// <summary>
    /// Input tokens that provider used to create prompt cache entries.
    /// </summary>
    public int CacheCreationInputTokens { get; set; }

    /// <summary>
    /// AI provider identifier used for the request.
    /// </summary>
    [StringLength(100)]
    public string? ProviderId { get; set; }

    /// <summary>
    /// AI provider display name used for the request.
    /// </summary>
    [StringLength(100)]
    public string? ProviderName { get; set; }

    /// <summary>
    /// AI provider protocol type used for the request.
    /// </summary>
    [StringLength(50)]
    public string? ProviderType { get; set; }

    /// <summary>
    /// Provider model identifier used for the request.
    /// </summary>
    [StringLength(100)]
    public string? ModelId { get; set; }

    /// <summary>
    /// 使用的模型名称
    /// </summary>
    [StringLength(100)]
    public string? ModelName { get; set; }

    /// <summary>
    /// Input token price per one million tokens.
    /// </summary>
    public decimal? InputTokenPrice { get; set; }

    /// <summary>
    /// Output token price per one million tokens.
    /// </summary>
    public decimal? OutputTokenPrice { get; set; }

    /// <summary>
    /// Cache hit input token price per one million tokens.
    /// </summary>
    public decimal? CacheHitTokenPrice { get; set; }

    /// <summary>
    /// Cache creation input token price per one million tokens.
    /// </summary>
    public decimal? CacheCreationTokenPrice { get; set; }

    /// <summary>
    /// Calculated input token cost.
    /// </summary>
    public decimal InputCost { get; set; }

    /// <summary>
    /// Calculated output token cost.
    /// </summary>
    public decimal OutputCost { get; set; }

    /// <summary>
    /// Calculated total token cost.
    /// </summary>
    public decimal TotalCost { get; set; }

    /// <summary>
    /// 操作类型（catalog, content, chat 等）
    /// </summary>
    [StringLength(50)]
    public string? Operation { get; set; }

    /// <summary>
    /// 记录时间
    /// </summary>
    public DateTime RecordedAt { get; set; } = DateTime.UtcNow;

    /// <summary>
    /// 关联的仓库导航属性
    /// </summary>
    [ForeignKey("RepositoryId")]
    public virtual Repository? Repository { get; set; }

    /// <summary>
    /// 关联的用户导航属性
    /// </summary>
    [ForeignKey("UserId")]
    public virtual User? User { get; set; }
}
