namespace OpenDeepWiki.Models;

/// <summary>
/// 仓库文档响应
/// </summary>
public class RepositoryDocResponse
{
    /// <summary>
    /// 文档是否存在
    /// </summary>
    public bool Exists { get; set; } = true;

    /// <summary>
    /// 文档路径（slug）
    /// </summary>
    public string Slug { get; set; } = string.Empty;

    /// <summary>
    /// Markdown 内容
    /// </summary>
    public string Content { get; set; } = string.Empty;

    /// <summary>
    /// 来源文件列表
    /// 记录生成此文档时读取的源代码文件路径
    /// </summary>
    public List<string> SourceFiles { get; set; } = [];

    /// <summary>
    /// Repository Git URL, used by the client to build source file links
    /// for the correct hosting platform (GitHub, GitLab, Azure DevOps, ...).
    /// </summary>
    public string? GitUrl { get; set; }

    /// <summary>
    /// Branch the document was generated from, used as the ref in file links.
    /// </summary>
    public string? Branch { get; set; }
}
