namespace OpenDeepWiki.Models;

/// <summary>
/// 仓库目录树节点
/// </summary>
public class RepositoryTreeNodeResponse
{
    /// <summary>
    /// 显示名称
    /// </summary>
    public string Title { get; set; } = string.Empty;

    /// <summary>
    /// 路由 slug
    /// </summary>
    public string Slug { get; set; } = string.Empty;

    /// <summary>
    /// 子节点
    /// </summary>
    public List<RepositoryTreeNodeResponse> Children { get; set; } = [];
}
