using System.ComponentModel.DataAnnotations;

namespace OpenDeepWiki.Models;

/// <summary>
/// 重新生成仓库文档请求
/// </summary>
public class RegenerateRequest
{
    /// <summary>
    /// 仓库所有者
    /// </summary>
    [Required]
    [StringLength(100)]
    public string Owner { get; set; } = string.Empty;

    /// <summary>
    /// 仓库名称
    /// </summary>
    [Required]
    [StringLength(100)]
    public string Repo { get; set; } = string.Empty;
}

/// <summary>
/// 重新生成仓库文档响应
/// </summary>
public class RegenerateResponse
{
    /// <summary>
    /// 是否成功
    /// </summary>
    public bool Success { get; set; }

    /// <summary>
    /// 错误信息
    /// </summary>
    public string? ErrorMessage { get; set; }
}
