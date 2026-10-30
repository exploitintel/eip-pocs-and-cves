namespace OpenDeepWiki.Models;

/// <summary>
/// Wiki 文档响应
/// </summary>
public class WikiDocResponse
{
    /// <summary>
    /// 文档路径
    /// </summary>
    public string Path { get; set; } = string.Empty;

    /// <summary>
    /// 文档标题
    /// </summary>
    public string Title { get; set; } = string.Empty;

    /// <summary>
    /// Markdown 内容
    /// </summary>
    public string Content { get; set; } = string.Empty;
}
