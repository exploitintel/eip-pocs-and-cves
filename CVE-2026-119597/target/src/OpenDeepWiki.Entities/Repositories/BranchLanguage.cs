using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace OpenDeepWiki.Entities;

/// <summary>
/// 分支语言实体
/// </summary>
public class BranchLanguage : AggregateRoot<string>
{
    /// <summary>
    /// 仓库分支ID
    /// </summary>
    [Required]
    [StringLength(36)]
    public string RepositoryBranchId { get; set; } = string.Empty;

    /// <summary>
    /// 语言代码
    /// </summary>
    [Required]
    [StringLength(50)]
    public string LanguageCode { get; set; } = string.Empty;

    /// <summary>
    /// 语言更新总结
    /// </summary>
    [StringLength(2000)]
    public string? UpdateSummary { get; set; }

    /// <summary>
    /// Generated English SKILL.md content for exporting this branch language as a skill package.
    /// </summary>
    public string? SkillMarkdown { get; set; }

    /// <summary>
    /// UTC time when SkillMarkdown was generated.
    /// </summary>
    public DateTime? SkillGeneratedAt { get; set; }

    /// <summary>
    /// 是否为默认语言
    /// </summary>
    public bool IsDefault { get; set; }

    /// <summary>
    /// 项目架构思维导图内容
    /// 格式: # 一级标题\n## 二级标题:文件路径\n### 三级标题
    /// </summary>
    public string? MindMapContent { get; set; }

    /// <summary>
    /// 思维导图生成状态
    /// </summary>
    public MindMapStatus MindMapStatus { get; set; } = MindMapStatus.Pending;

    /// <summary>
    /// 仓库分支导航属性
    /// </summary>
    [ForeignKey("RepositoryBranchId")]
    public virtual RepositoryBranch? RepositoryBranch { get; set; }
}

/// <summary>
/// 思维导图生成状态
/// </summary>
public enum MindMapStatus
{
    /// <summary>
    /// 等待生成
    /// </summary>
    Pending = 0,

    /// <summary>
    /// 正在生成
    /// </summary>
    Processing = 1,

    /// <summary>
    /// 生成完成
    /// </summary>
    Completed = 2,

    /// <summary>
    /// 生成失败
    /// </summary>
    Failed = 3
}
