using Microsoft.Extensions.Logging;

namespace OpenDeepWiki.Chat.Config;

/// <summary>
/// 配置变更通知器
/// 用于在配置变更时通知相关组件
/// </summary>
public class ConfigChangeNotifier : IConfigChangeNotifier
{
    private readonly ILogger<ConfigChangeNotifier> _logger;
    private readonly List<ConfigChangeSubscription> _subscriptions = new();
    private readonly object _lock = new();
    private long _subscriptionIdCounter;
    
    public ConfigChangeNotifier(ILogger<ConfigChangeNotifier> logger)
    {
        _logger = logger;
    }
    
    /// <inheritdoc />
    public IDisposable Subscribe(string? platform, Action<ConfigChangeEvent> handler)
    {
        var subscriptionId = Interlocked.Increment(ref _subscriptionIdCounter);
        var subscription = new ConfigChangeSubscription(subscriptionId, platform, handler);
        
        lock (_lock)
        {
            _subscriptions.Add(subscription);
        }
        
        _logger.LogDebug("Added config change subscription {Id} for platform: {Platform}", 
            subscriptionId, platform ?? "all");
        
        return new SubscriptionDisposable(() => Unsubscribe(subscriptionId));
    }
    
    /// <inheritdoc />
    public void NotifyChange(string platform, ConfigChangeType changeType)
    {
        var changeEvent = new ConfigChangeEvent(platform, changeType, DateTimeOffset.UtcNow);
        
        List<ConfigChangeSubscription> matchingSubscriptions;
        lock (_lock)
        {
            matchingSubscriptions = _subscriptions
                .Where(s => s.Platform == null || s.Platform == platform)
                .ToList();
        }
        
        _logger.LogInformation("Notifying {Count} subscribers of config change for platform: {Platform}, type: {Type}",
            matchingSubscriptions.Count, platform, changeType);
        
        foreach (var subscription in matchingSubscriptions)
        {
            try
            {
                subscription.Handler(changeEvent);
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error in config change handler for subscription {Id}", subscription.Id);
            }
        }
    }
    
    /// <summary>
    /// 取消订阅
    /// </summary>
    private void Unsubscribe(long subscriptionId)
    {
        lock (_lock)
        {
            var subscription = _subscriptions.FirstOrDefault(s => s.Id == subscriptionId);
            if (subscription != null)
            {
                _subscriptions.Remove(subscription);
                _logger.LogDebug("Removed config change subscription {Id}", subscriptionId);
            }
        }
    }
    
    /// <summary>
    /// 订阅信息
    /// </summary>
    private record ConfigChangeSubscription(long Id, string? Platform, Action<ConfigChangeEvent> Handler);
    
    /// <summary>
    /// 订阅取消辅助类
    /// </summary>
    private class SubscriptionDisposable : IDisposable
    {
        private readonly Action _disposeAction;
        private bool _disposed;
        
        public SubscriptionDisposable(Action disposeAction)
        {
            _disposeAction = disposeAction;
        }
        
        public void Dispose()
        {
            if (!_disposed)
            {
                _disposeAction();
                _disposed = true;
            }
        }
    }
}

/// <summary>
/// 配置变更通知器接口
/// </summary>
public interface IConfigChangeNotifier
{
    /// <summary>
    /// 订阅配置变更
    /// </summary>
    /// <param name="platform">平台标识，null 表示订阅所有平台</param>
    /// <param name="handler">变更处理器</param>
    /// <returns>取消订阅的 IDisposable</returns>
    IDisposable Subscribe(string? platform, Action<ConfigChangeEvent> handler);
    
    /// <summary>
    /// 通知配置变更
    /// </summary>
    /// <param name="platform">平台标识</param>
    /// <param name="changeType">变更类型</param>
    void NotifyChange(string platform, ConfigChangeType changeType);
}

/// <summary>
/// 配置变更事件
/// </summary>
public record ConfigChangeEvent(
    string Platform,
    ConfigChangeType ChangeType,
    DateTimeOffset Timestamp
);

/// <summary>
/// 配置变更类型
/// </summary>
public enum ConfigChangeType
{
    /// <summary>
    /// 新增
    /// </summary>
    Created,
    
    /// <summary>
    /// 更新
    /// </summary>
    Updated,
    
    /// <summary>
    /// 删除
    /// </summary>
    Deleted,
    
    /// <summary>
    /// 重载
    /// </summary>
    Reloaded
}
