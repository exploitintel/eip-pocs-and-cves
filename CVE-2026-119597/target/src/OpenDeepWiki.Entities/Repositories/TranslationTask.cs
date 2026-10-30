using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace OpenDeepWiki.Entities;

/// <summary>
/// 翻译任务状态
/// </summary>
public enum TranslationTaskStatus
{
    /// <summary>
    /// 待处理
    /// </summary>
    Pending = 0,

    /// <summary>
    /// 处理中
    /// </summary>
    Processing = 1,

    /// <summary>
    /// 已完成
    /// </summary>
    Completed = 2,

    /// <summary>
    /// 失败
    /// </summary>
    Failed = 3
}

/// <summary>
/// 翻译任务实体
/// 用于存储待翻译的 Wiki 任务，由后台服务异步处理
/// </summary>
public class TranslationTask : AggregateRoot<string>
{
    /// <summary>
    /// 仓库ID
    /// </summary>
    [Required]
    [StringLength(36)]
    public string RepositoryId { get; set; } = string.Empty;

    /// <summary>
    /// 仓库分支ID
    /// </summary>
    [Required]
    [StringLength(36)]
    public string RepositoryBranchId { get; set; } = string.Empty;

    /// <summary>
    /// 源语言分支ID
    /// </summary>
    [Required]
    [StringLength(36)]
    public string SourceBranchLanguageId { get; set; } = string.Empty;

    /// <summary>
    /// 目标语言代码
    /// </summary>
    [Required]
    [StringLength(10)]
    public string TargetLanguageCode { get; set; } = string.Empty;

    /// <summary>
    /// 任务状态
    /// </summary>
    public TranslationTaskStatus Status { get; set; } = TranslationTaskStatus.Pending;

    /// <summary>
    /// 错误信息（失败时记录）
    /// </summary>
    [StringLength(2000)]
    public string? ErrorMessage { get; set; }

    /// <summary>
    /// 重试次数
    /// </summary>
    public int RetryCount { get; set; } = 0;

    /// <summary>
    /// 最大重试次数
    /// </summary>
    public int MaxRetryCount { get; set; } = 3;

    /// <summary>
    /// 开始处理时间
    /// </summary>
    public DateTime? StartedAt { get; set; }

    /// <summary>
    /// 完成时间
    /// </summary>
    public DateTime? CompletedAt { get; set; }

    /// <summary>
    /// 仓库导航属性
    /// </summary>
    [ForeignKey("RepositoryId")]
    public virtual Repository? Repository { get; set; }

    /// <summary>
    /// 仓库分支导航属性
    /// </summary>
    [ForeignKey("RepositoryBranchId")]
    public virtual RepositoryBranch? RepositoryBranch { get; set; }

    /// <summary>
    /// 源语言分支导航属性
    /// </summary>
    [ForeignKey("SourceBranchLanguageId")]
    public virtual BranchLanguage? SourceBranchLanguage { get; set; }
}
