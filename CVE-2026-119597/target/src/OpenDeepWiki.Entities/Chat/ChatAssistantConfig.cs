using System.ComponentModel.DataAnnotations;

namespace OpenDeepWiki.Entities;

/// <summary>
/// 对话助手配置实体
/// 存储管理员配置的模型、MCPs、Skills等
/// </summary>
public class ChatAssistantConfig : AggregateRoot<Guid>
{
    /// <summary>
    /// 是否启用对话助手功能
    /// </summary>
    public bool IsEnabled { get; set; } = false;

    /// <summary>
    /// 启用的模型ID列表（JSON数组）
    /// </summary>
    [StringLength(2000)]
    public string? EnabledModelIds { get; set; }

    /// <summary>
    /// 启用的MCP ID列表（JSON数组）
    /// </summary>
    [StringLength(2000)]
    public string? EnabledMcpIds { get; set; }

    /// <summary>
    /// 启用的Skill ID列表（JSON数组）
    /// </summary>
    [StringLength(2000)]
    public string? EnabledSkillIds { get; set; }

    /// <summary>
    /// 默认模型ID
    /// </summary>
    [StringLength(100)]
    public string? DefaultModelId { get; set; }

    /// <summary>
    /// 是否启用图片上传功能
    /// </summary>
    public bool EnableImageUpload { get; set; } = false;
}
