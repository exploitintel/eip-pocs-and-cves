namespace OpenDeepWiki.Models.Subscription;

/// <summary>
/// 订阅列表响应
/// </summary>
public class SubscriptionListResponse
{
    /// <summary>
    /// 订阅项列表
    /// </summary>
    public List<SubscriptionItemResponse> Items { get; set; } = [];

    /// <summary>
    /// 总数
    /// </summary>
    public int Total { get; set; }

    /// <summary>
    /// 当前页码
    /// </summary>
    public int Page { get; set; }

    /// <summary>
    /// 每页数量
    /// </summary>
    public int PageSize { get; set; }
}

/// <summary>
/// 订阅项响应
/// </summary>
public class SubscriptionItemResponse
{
    /// <summary>
    /// 订阅记录ID
    /// </summary>
    public string SubscriptionId { get; set; } = string.Empty;

    /// <summary>
    /// 仓库ID
    /// </summary>
    public string RepositoryId { get; set; } = string.Empty;

    /// <summary>
    /// 仓库名称
    /// </summary>
    public string RepoName { get; set; } = string.Empty;

    /// <summary>
    /// 组织名称
    /// </summary>
    public string OrgName { get; set; } = string.Empty;

    /// <summary>
    /// 仓库描述
    /// </summary>
    public string? Description { get; set; }

    /// <summary>
    /// Star数量
    /// </summary>
    public int StarCount { get; set; }

    /// <summary>
    /// Fork数量
    /// </summary>
    public int ForkCount { get; set; }

    /// <summary>
    /// 订阅数量
    /// </summary>
    public int SubscriptionCount { get; set; }

    /// <summary>
    /// 订阅时间
    /// </summary>
    public DateTime SubscribedAt { get; set; }
}
