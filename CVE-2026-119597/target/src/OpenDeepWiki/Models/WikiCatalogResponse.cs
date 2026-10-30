namespace OpenDeepWiki.Models;

/// <summary>
/// Wiki 目录响应
/// </summary>
public class WikiCatalogResponse
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
    /// 默认路径
    /// </summary>
    public string DefaultPath { get; set; } = string.Empty;

    /// <summary>
    /// 目录项列表
    /// </summary>
    public List<WikiCatalogItemResponse> Items { get; set; } = [];
}

/// <summary>
/// Wiki 目录项响应
/// </summary>
public class WikiCatalogItemResponse
{
    /// <summary>
    /// 标题
    /// </summary>
    public string Title { get; set; } = string.Empty;

    /// <summary>
    /// 路径
    /// </summary>
    public string Path { get; set; } = string.Empty;

    /// <summary>
    /// 排序顺序
    /// </summary>
    public int Order { get; set; }

    /// <summary>
    /// 是否有文档内容
    /// </summary>
    public bool HasContent { get; set; }

    /// <summary>
    /// 子目录项
    /// </summary>
    public List<WikiCatalogItemResponse> Children { get; set; } = [];
}
