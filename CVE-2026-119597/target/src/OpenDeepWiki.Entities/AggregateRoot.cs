using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace OpenDeepWiki.Entities;

/// <summary>
/// 聚合根基类（DDD设计）
/// </summary>
/// <typeparam name="TKey">主键类型</typeparam>
public abstract class AggregateRoot<TKey>
{
    /// <summary>
    /// 主键ID
    /// </summary>
    [Key]
    public TKey Id { get; set; } = default!;

    /// <summary>
    /// 创建时间（UTC）
    /// </summary>
    public DateTime CreatedAt { get; set; } = DateTime.UtcNow;

    /// <summary>
    /// 更新时间（UTC）
    /// </summary>
    public DateTime? UpdatedAt { get; set; }

    /// <summary>
    /// 删除时间（UTC）
    /// </summary>
    public DateTime? DeletedAt { get; set; }

    /// <summary>
    /// 是否已删除（软删除）
    /// </summary>
    public bool IsDeleted { get; set; } = false;

    /// <summary>
    /// 版本号（用于乐观并发控制）
    /// </summary>
    [Timestamp]
    public byte[]? Version { get; set; }

    /// <summary>
    /// 标记为已删除
    /// </summary>
    public virtual void MarkAsDeleted()
    {
        IsDeleted = true;
        DeletedAt = DateTime.UtcNow;
        UpdatedAt = DateTime.UtcNow;
    }

    /// <summary>
    /// 更新时间戳
    /// </summary>
    public virtual void UpdateTimestamp()
    {
        UpdatedAt = DateTime.UtcNow;
    }
}
