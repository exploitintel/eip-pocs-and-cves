using System.ComponentModel.DataAnnotations;

namespace OpenDeepWiki.Entities;

/// <summary>
/// MCP 配置实体
/// </summary>
public class McpConfig : AggregateRoot<string>
{
    /// <summary>
    /// MCP 名称
    /// </summary>
    [Required]
    [StringLength(100)]
    public string Name { get; set; } = string.Empty;

    /// <summary>
    /// MCP 描述
    /// </summary>
    [StringLength(500)]
    public string? Description { get; set; }

    /// <summary>
    /// MCP 服务器地址
    /// </summary>
    [Required]
    [StringLength(500)]
    public string ServerUrl { get; set; } = string.Empty;

    /// <summary>
    /// API 密钥
    /// </summary>
    [StringLength(500)]
    public string? ApiKey { get; set; }

    /// <summary>
    /// 是否启用
    /// </summary>
    public bool IsActive { get; set; } = true;

    /// <summary>
    /// 排序顺序
    /// </summary>
    public int SortOrder { get; set; } = 0;
}
