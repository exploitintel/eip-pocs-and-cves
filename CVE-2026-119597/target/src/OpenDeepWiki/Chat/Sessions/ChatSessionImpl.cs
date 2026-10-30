using OpenDeepWiki.Chat.Abstractions;

namespace OpenDeepWiki.Chat.Sessions;

/// <summary>
/// 对话会话实现类
/// 维护用户与 Agent 之间的对话上下文
/// </summary>
public class ChatSessionImpl : IChatSession
{
    private readonly List<IChatMessage> _history = [];
    private readonly object _lock = new();
    private SessionState _state;
    private DateTimeOffset _lastActivityAt;
    
    /// <summary>
    /// 最大历史消息数量，默认100条
    /// </summary>
    public int MaxHistoryCount { get; set; } = 100;
    
    /// <inheritdoc />
    public string SessionId { get; }
    
    /// <inheritdoc />
    public string UserId { get; }
    
    /// <inheritdoc />
    public string Platform { get; }
    
    /// <inheritdoc />
    public SessionState State
    {
        get
        {
            lock (_lock)
            {
                return _state;
            }
        }
    }
    
    /// <inheritdoc />
    public IReadOnlyList<IChatMessage> History
    {
        get
        {
            lock (_lock)
            {
                return _history.ToList().AsReadOnly();
            }
        }
    }
    
    /// <inheritdoc />
    public DateTimeOffset CreatedAt { get; }
    
    /// <inheritdoc />
    public DateTimeOffset LastActivityAt
    {
        get
        {
            lock (_lock)
            {
                return _lastActivityAt;
            }
        }
    }
    
    /// <inheritdoc />
    public IDictionary<string, object>? Metadata { get; set; }

    /// <summary>
    /// 创建新会话
    /// </summary>
    /// <param name="sessionId">会话ID</param>
    /// <param name="userId">用户ID</param>
    /// <param name="platform">平台标识</param>
    public ChatSessionImpl(string sessionId, string userId, string platform)
    {
        SessionId = sessionId ?? throw new ArgumentNullException(nameof(sessionId));
        UserId = userId ?? throw new ArgumentNullException(nameof(userId));
        Platform = platform ?? throw new ArgumentNullException(nameof(platform));
        CreatedAt = DateTimeOffset.UtcNow;
        _lastActivityAt = CreatedAt;
        _state = SessionState.Active;
    }
    
    /// <summary>
    /// 从现有数据恢复会话
    /// </summary>
    /// <param name="sessionId">会话ID</param>
    /// <param name="userId">用户ID</param>
    /// <param name="platform">平台标识</param>
    /// <param name="state">会话状态</param>
    /// <param name="createdAt">创建时间</param>
    /// <param name="lastActivityAt">最后活动时间</param>
    /// <param name="history">历史消息</param>
    /// <param name="metadata">元数据</param>
    public ChatSessionImpl(
        string sessionId,
        string userId,
        string platform,
        SessionState state,
        DateTimeOffset createdAt,
        DateTimeOffset lastActivityAt,
        IEnumerable<IChatMessage>? history = null,
        IDictionary<string, object>? metadata = null)
    {
        SessionId = sessionId ?? throw new ArgumentNullException(nameof(sessionId));
        UserId = userId ?? throw new ArgumentNullException(nameof(userId));
        Platform = platform ?? throw new ArgumentNullException(nameof(platform));
        _state = state;
        CreatedAt = createdAt;
        _lastActivityAt = lastActivityAt;
        Metadata = metadata;
        
        if (history != null)
        {
            _history.AddRange(history);
        }
    }
    
    /// <inheritdoc />
    public void AddMessage(IChatMessage message)
    {
        ArgumentNullException.ThrowIfNull(message);
        
        lock (_lock)
        {
            _history.Add(message);
            
            // 如果超过最大历史数量，移除最早的消息
            while (_history.Count > MaxHistoryCount && MaxHistoryCount > 0)
            {
                _history.RemoveAt(0);
            }
            
            _lastActivityAt = DateTimeOffset.UtcNow;
        }
    }
    
    /// <inheritdoc />
    public void ClearHistory()
    {
        lock (_lock)
        {
            _history.Clear();
            _lastActivityAt = DateTimeOffset.UtcNow;
        }
    }
    
    /// <inheritdoc />
    public void UpdateState(SessionState state)
    {
        lock (_lock)
        {
            _state = state;
            _lastActivityAt = DateTimeOffset.UtcNow;
        }
    }
    
    /// <inheritdoc />
    public void Touch()
    {
        lock (_lock)
        {
            _lastActivityAt = DateTimeOffset.UtcNow;
        }
    }
}
