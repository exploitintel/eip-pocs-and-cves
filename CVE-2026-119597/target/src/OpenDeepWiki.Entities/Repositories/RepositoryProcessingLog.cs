using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace OpenDeepWiki.Entities;

/// <summary>
/// 处理步骤类型
/// </summary>
public enum ProcessingStep
{
    /// <summary>
    /// 准备工作区
    /// </summary>
    Workspace = 0,
    
    /// <summary>
    /// 生成目录
    /// </summary>
    Catalog = 1,
    
    /// <summary>
    /// 生成文档内容
    /// </summary>
    Content = 2,
    
    /// <summary>
    /// 多语言翻译
    /// </summary>
    Translation = 3,
    
    /// <summary>
    /// 生成思维导图
    /// </summary>
    MindMap = 4,
    
    /// <summary>
    /// 完成
    /// </summary>
    Complete = 5,

    /// <summary>
    /// 生成 Graphify 图谱
    /// </summary>
    Graphify = 6
}

/// <summary>
/// 仓库处理日志实体
/// </summary>
public class RepositoryProcessingLog : AggregateRoot<string>
{
    /// <summary>
    /// 关联的仓库ID
    /// </summary>
    [Required]
    [StringLength(36)]
    public string RepositoryId { get; set; } = string.Empty;

    [StringLength(36)]
    public string? BranchId { get; set; }

    [StringLength(36)]
    public string? GenerationTaskId { get; set; }

    /// <summary>
    /// 当前处理步骤
    /// </summary>
    public ProcessingStep Step { get; set; } = ProcessingStep.Workspace;

    /// <summary>
    /// 日志消息
    /// </summary>
    [Required]
    public string Message { get; set; } = string.Empty;

    /// <summary>
    /// 是否为AI输出
    /// </summary>
    public bool IsAiOutput { get; set; } = false;

    /// <summary>
    /// 工具调用名称（如果是工具调用）
    /// </summary>
    [StringLength(100)]
    public string? ToolName { get; set; }

    /// <summary>
    /// 关联的仓库导航属性
    /// </summary>
    [ForeignKey("RepositoryId")]
    public virtual Repository? Repository { get; set; }

    [ForeignKey("BranchId")]
    public virtual RepositoryBranch? RepositoryBranch { get; set; }

    [ForeignKey("GenerationTaskId")]
    public virtual BranchGenerationTask? GenerationTask { get; set; }
}
