using System.ComponentModel.DataAnnotations;

namespace OpenDeepWiki.Entities.Tools;

/// <summary>
/// Agent Skills 配置实体
/// 遵循 Anthropic Agent Skills 开放标准 (agentskills.io)
/// Skill 以文件夹形式存储，此实体仅记录元数据和管理信息
/// </summary>
public class SkillConfig : AggregateRoot<string>
{
    /// <summary>
    /// Skill 名称（唯一标识符，同时也是文件夹名）
    /// 规范：最大64字符，仅小写字母、数字和连字符，不能以连字符开头或结尾
    /// </summary>
    [Required]
    [StringLength(64)]
    [RegularExpression(@"^[a-z0-9]+(-[a-z0-9]+)*$", 
        ErrorMessage = "名称只能包含小写字母、数字和连字符，且不能以连字符开头或结尾")]
    public string Name { get; set; } = string.Empty;

    /// <summary>
    /// Skill 描述（从 SKILL.md frontmatter 解析）
    /// 规范：最大1024字符
    /// </summary>
    [Required]
    [StringLength(1024)]
    public string Description { get; set; } = string.Empty;

    /// <summary>
    /// 许可证信息（从 SKILL.md frontmatter 解析）
    /// </summary>
    [StringLength(100)]
    public string? License { get; set; }

    /// <summary>
    /// 兼容性要求（从 SKILL.md frontmatter 解析）
    /// 规范：最大500字符
    /// </summary>
    [StringLength(500)]
    public string? Compatibility { get; set; }

    /// <summary>
    /// 预批准的工具列表（空格分隔，从 SKILL.md frontmatter 解析）
    /// </summary>
    [StringLength(1000)]
    public string? AllowedTools { get; set; }

    /// <summary>
    /// Skill 文件夹的相对路径（相对于 skills 根目录）
    /// 例如：code-review、data-analysis
    /// </summary>
    [Required]
    [StringLength(200)]
    public string FolderPath { get; set; } = string.Empty;

    /// <summary>
    /// 是否启用
    /// </summary>
    public bool IsActive { get; set; } = true;

    /// <summary>
    /// 排序顺序
    /// </summary>
    public int SortOrder { get; set; } = 0;

    /// <summary>
    /// 作者
    /// </summary>
    [StringLength(100)]
    public string? Author { get; set; }

    /// <summary>
    /// 版本号
    /// </summary>
    [StringLength(20)]
    public new string Version { get; set; } = "1.0.0";

    /// <summary>
    /// 来源类型
    /// </summary>
    public SkillSource Source { get; set; } = SkillSource.Local;

    /// <summary>
    /// 来源 URL（如果是从远程导入的）
    /// </summary>
    [StringLength(500)]
    public string? SourceUrl { get; set; }

    /// <summary>
    /// 是否包含 scripts 目录
    /// </summary>
    public bool HasScripts { get; set; }

    /// <summary>
    /// 是否包含 references 目录
    /// </summary>
    public bool HasReferences { get; set; }

    /// <summary>
    /// 是否包含 assets 目录
    /// </summary>
    public bool HasAssets { get; set; }

    /// <summary>
    /// SKILL.md 文件大小（字节）
    /// </summary>
    public long SkillMdSize { get; set; }

    /// <summary>
    /// 整个 Skill 文件夹大小（字节）
    /// </summary>
    public long TotalSize { get; set; }
}

/// <summary>
/// Skill 来源类型
/// </summary>
public enum SkillSource
{
    /// <summary>
    /// 本地上传
    /// </summary>
    Local = 0,

    /// <summary>
    /// 从 URL 导入
    /// </summary>
    Remote = 1,

    /// <summary>
    /// 从市场安装
    /// </summary>
    Marketplace = 2
}
