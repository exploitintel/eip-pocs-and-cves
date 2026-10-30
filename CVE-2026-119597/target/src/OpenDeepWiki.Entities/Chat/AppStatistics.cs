using System.ComponentModel.DataAnnotations;

namespace OpenDeepWiki.Entities;

/// <summary>
/// 应用统计数据实体
/// 记录每日调用次数、Token消耗等统计信息
/// </summary>
public class AppStatistics : AggregateRoot<Guid>
{
    /// <summary>
    /// 关联的应用ID
    /// </summary>
    [Required]
    [StringLength(64)]
    public string AppId { get; set; } = string.Empty;

    /// <summary>
    /// 统计日期
    /// </summary>
    public DateTime Date { get; set; }

    /// <summary>
    /// 请求总数
    /// </summary>
    public long RequestCount { get; set; } = 0;

    /// <summary>
    /// 输入Token数量
    /// </summary>
    public long InputTokens { get; set; } = 0;

    /// <summary>
    /// 输出Token数量
    /// </summary>
    public long OutputTokens { get; set; } = 0;
}
