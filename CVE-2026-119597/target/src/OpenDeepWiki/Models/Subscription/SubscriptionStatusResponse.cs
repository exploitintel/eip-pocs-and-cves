namespace OpenDeepWiki.Models.Subscription;

/// <summary>
/// 订阅状态响应
/// </summary>
public class SubscriptionStatusResponse
{
    /// <summary>
    /// 是否已订阅
    /// </summary>
    public bool IsSubscribed { get; set; }

    /// <summary>
    /// 订阅时间（仅在已订阅时有值）
    /// </summary>
    public DateTime? SubscribedAt { get; set; }
}
