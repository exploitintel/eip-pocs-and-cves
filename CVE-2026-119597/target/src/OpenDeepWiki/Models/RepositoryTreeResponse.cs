using OpenDeepWiki.Entities;

namespace OpenDeepWiki.Models;

/// <summary>
/// 仓库目录树响应
/// </summary>
public class RepositoryTreeResponse
{
    /// <summary>
    /// 组织名称
    /// </summary>
    public string Owner { get; set; } = string.Empty;

    /// <summary>
    /// 仓库名称
    /// </summary>
    public string Repo { get; set; } = string.Empty;

    /// <summary>
    /// 默认 slug
    /// </summary>
    public string DefaultSlug { get; set; } = string.Empty;

    /// <summary>
    /// 目录树节点
    /// </summary>
    public List<RepositoryTreeNodeResponse> Nodes { get; set; } = [];

    /// <summary>
    /// 仓库处理状态
    /// </summary>
    public RepositoryStatus Status { get; set; } = RepositoryStatus.Completed;

    /// <summary>
    /// 状态名称
    /// </summary>
    public string StatusName => Status.ToString();

    /// <summary>
    /// 仓库是否存在
    /// </summary>
    public bool Exists { get; set; } = true;

    /// <summary>
    /// 当前分支
    /// </summary>
    public string CurrentBranch { get; set; } = string.Empty;

    /// <summary>
    /// 当前语言
    /// </summary>
    public string CurrentLanguage { get; set; } = string.Empty;

    public bool HasGraphifyArtifact { get; set; }

    public int? GraphifyStatus { get; set; }

    public string? GraphifyStatusName { get; set; }
}
