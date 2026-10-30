namespace OpenDeepWiki.Models;

/// <summary>
/// 仓库分支和语言响应（从数据库获取）
/// </summary>
public class RepositoryBranchesResponse
{
    /// <summary>
    /// 仓库ID
    /// </summary>
    public string RepositoryId { get; set; } = string.Empty;

    /// <summary>
    /// 分支列表
    /// </summary>
    public List<BranchItem> Branches { get; set; } = [];

    /// <summary>
    /// 所有可用语言
    /// </summary>
    public List<string> Languages { get; set; } = [];

    /// <summary>
    /// 默认分支
    /// </summary>
    public string DefaultBranch { get; set; } = string.Empty;

    /// <summary>
    /// 默认语言
    /// </summary>
    public string DefaultLanguage { get; set; } = string.Empty;
}

/// <summary>
/// 分支项
/// </summary>
public class BranchItem
{
    /// <summary>
    /// 分支名称
    /// </summary>
    public string Name { get; set; } = string.Empty;

    public string? Id { get; set; }

    public string? GenerationStatus { get; set; }

    public string? LastGenerationTaskId { get; set; }

    public string? LastGenerationError { get; set; }

    public DateTime? LastGenerationStartedAt { get; set; }

    public DateTime? LastGenerationCompletedAt { get; set; }

    /// <summary>
    /// 该分支支持的语言列表
    /// </summary>
    public List<string> Languages { get; set; } = [];
}

/// <summary>
/// Git平台分支列表响应（从远程API获取）
/// </summary>
public class GitBranchesResponse
{
    /// <summary>
    /// 分支列表
    /// </summary>
    public List<GitBranchItem> Branches { get; set; } = [];

    /// <summary>
    /// 默认分支
    /// </summary>
    public string? DefaultBranch { get; set; }

    /// <summary>
    /// 是否支持获取分支（平台是否支持）
    /// </summary>
    public bool IsSupported { get; set; }
}

/// <summary>
/// Git分支项
/// </summary>
public class GitBranchItem
{
    /// <summary>
    /// 分支名称
    /// </summary>
    public string Name { get; set; } = string.Empty;

    /// <summary>
    /// 是否为默认分支
    /// </summary>
    public bool IsDefault { get; set; }
}
