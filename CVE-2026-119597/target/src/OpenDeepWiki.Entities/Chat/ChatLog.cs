using System.ComponentModel.DataAnnotations;

namespace OpenDeepWiki.Entities;

/// <summary>
/// 用户提问记录实体
/// 记录通过嵌入脚本的对话内容
/// </summary>
public class ChatLog : AggregateRoot<Guid>
{
    /// <summary>
    /// 关联的应用ID
    /// </summary>
    [Required]
    [StringLength(64)]
    public string AppId { get; set; } = string.Empty;

    /// <summary>
    /// 用户标识（可选）
    /// </summary>
    [StringLength(100)]
    public string? UserIdentifier { get; set; }

    /// <summary>
    /// 用户提问内容
    /// </summary>
    [Required]
    public string Question { get; set; } = string.Empty;

    /// <summary>
    /// AI回复摘要
    /// </summary>
    [StringLength(500)]
    public string? AnswerSummary { get; set; }

    /// <summary>
    /// 输入Token数量
    /// </summary>
    public int InputTokens { get; set; }

    /// <summary>
    /// 输出Token数量
    /// </summary>
    public int OutputTokens { get; set; }

    /// <summary>
    /// 使用的模型
    /// </summary>
    [StringLength(100)]
    public string? ModelUsed { get; set; }

    /// <summary>
    /// 请求来源域名
    /// </summary>
    [StringLength(500)]
    public string? SourceDomain { get; set; }
}
