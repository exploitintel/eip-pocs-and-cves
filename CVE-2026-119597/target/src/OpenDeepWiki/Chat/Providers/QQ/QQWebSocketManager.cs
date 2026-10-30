using System.Net.WebSockets;
using System.Text;
using System.Text.Json;
using Microsoft.Extensions.Logging;
using Microsoft.Extensions.Options;

namespace OpenDeepWiki.Chat.Providers.QQ;

/// <summary>
/// QQ WebSocket 连接管理器
/// 负责维护与 QQ 开放平台的 WebSocket 连接，处理鉴权和心跳
/// </summary>
public class QQWebSocketManager : IDisposable
{
    private readonly ILogger<QQWebSocketManager> _logger;
    private readonly QQProviderOptions _options;
    private readonly HttpClient _httpClient;
    private readonly Func<Task<string>> _getAccessTokenAsync;
    
    private ClientWebSocket? _webSocket;
    private CancellationTokenSource? _heartbeatCts;
    private CancellationTokenSource? _receiveCts;
    private Task? _heartbeatTask;
    private Task? _receiveTask;
    
    private string? _sessionId;
    private int _lastSequence;
    private int _heartbeatInterval;
    private int _reconnectAttempts;
    private bool _isConnected;
    private bool _isDisposed;
    
    /// <summary>
    /// 连接状态变更事件
    /// </summary>
    public event EventHandler<QQConnectionStateChangedEventArgs>? ConnectionStateChanged;
    
    /// <summary>
    /// 收到消息事件
    /// </summary>
    public event EventHandler<QQMessageReceivedEventArgs>? MessageReceived;
    
    /// <summary>
    /// 是否已连接
    /// </summary>
    public bool IsConnected => _isConnected && _webSocket?.State == WebSocketState.Open;
    
    /// <summary>
    /// 会话 ID
    /// </summary>
    public string? SessionId => _sessionId;
    
    public QQWebSocketManager(
        ILogger<QQWebSocketManager> logger,
        IOptions<QQProviderOptions> options,
        HttpClient httpClient,
        Func<Task<string>> getAccessTokenAsync)
    {
        _logger = logger;
        _options = options.Value;
        _httpClient = httpClient;
        _getAccessTokenAsync = getAccessTokenAsync;
    }
    
    /// <summary>
    /// 连接到 QQ WebSocket 网关
    /// </summary>
    public async Task ConnectAsync(CancellationToken cancellationToken = default)
    {
        if (_isDisposed)
            throw new ObjectDisposedException(nameof(QQWebSocketManager));
        
        if (IsConnected)
        {
            _logger.LogWarning("WebSocket is already connected");
            return;
        }
        
        try
        {
            // 获取 WebSocket 网关地址
            var gatewayUrl = await GetGatewayUrlAsync(cancellationToken);
            if (string.IsNullOrEmpty(gatewayUrl))
            {
                throw new InvalidOperationException("Failed to get WebSocket gateway URL");
            }
            
            _logger.LogInformation("Connecting to QQ WebSocket gateway: {Url}", gatewayUrl);
            
            // 创建 WebSocket 连接
            _webSocket = new ClientWebSocket();
            await _webSocket.ConnectAsync(new Uri(gatewayUrl), cancellationToken);
            
            _isConnected = true;
            _reconnectAttempts = 0;
            
            OnConnectionStateChanged(QQConnectionState.Connected);
            
            // 启动接收任务
            _receiveCts = new CancellationTokenSource();
            _receiveTask = ReceiveLoopAsync(_receiveCts.Token);
            
            _logger.LogInformation("Connected to QQ WebSocket gateway");
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to connect to QQ WebSocket gateway");
            _isConnected = false;
            OnConnectionStateChanged(QQConnectionState.Disconnected);
            throw;
        }
    }
    
    /// <summary>
    /// 断开连接
    /// </summary>
    public async Task DisconnectAsync(CancellationToken cancellationToken = default)
    {
        if (_webSocket == null || _webSocket.State != WebSocketState.Open)
            return;
        
        try
        {
            // 停止心跳
            StopHeartbeat();
            
            // 停止接收
            _receiveCts?.Cancel();
            
            // 关闭 WebSocket
            await _webSocket.CloseAsync(WebSocketCloseStatus.NormalClosure, "Closing", cancellationToken);
            
            _isConnected = false;
            OnConnectionStateChanged(QQConnectionState.Disconnected);
            
            _logger.LogInformation("Disconnected from QQ WebSocket gateway");
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error while disconnecting from QQ WebSocket gateway");
        }
    }
    
    /// <summary>
    /// 发送鉴权请求
    /// </summary>
    public async Task IdentifyAsync(int intents, CancellationToken cancellationToken = default)
    {
        var token = await _getAccessTokenAsync();
        
        var identifyPayload = new QQWebhookEvent
        {
            OpCode = QQOpCode.Identify,
            Data = new QQEventData()
        };
        
        // 构建鉴权数据
        var identifyData = new
        {
            token = $"QQBot {token}",
            intents = intents,
            shard = new[] { 0, 1 }
        };
        
        var payload = new
        {
            op = QQOpCode.Identify,
            d = identifyData
        };
        
        await SendAsync(JsonSerializer.Serialize(payload), cancellationToken);
        _logger.LogDebug("Sent identify request with intents: {Intents}", intents);
    }
    
    /// <summary>
    /// 发送恢复连接请求
    /// </summary>
    public async Task ResumeAsync(CancellationToken cancellationToken = default)
    {
        if (string.IsNullOrEmpty(_sessionId))
        {
            _logger.LogWarning("Cannot resume without session ID, will re-identify");
            return;
        }
        
        var token = await _getAccessTokenAsync();
        
        var resumeData = new
        {
            token = $"QQBot {token}",
            session_id = _sessionId,
            seq = _lastSequence
        };
        
        var payload = new
        {
            op = QQOpCode.Resume,
            d = resumeData
        };
        
        await SendAsync(JsonSerializer.Serialize(payload), cancellationToken);
        _logger.LogDebug("Sent resume request for session: {SessionId}", _sessionId);
    }
    
    #region 私有方法
    
    /// <summary>
    /// 获取 WebSocket 网关地址
    /// </summary>
    private async Task<string?> GetGatewayUrlAsync(CancellationToken cancellationToken)
    {
        var token = await _getAccessTokenAsync();
        var baseUrl = _options.UseSandbox ? _options.SandboxApiBaseUrl : _options.ApiBaseUrl;
        var url = $"{baseUrl}/gateway";
        
        var request = new HttpRequestMessage(HttpMethod.Get, url);
        request.Headers.Authorization = new System.Net.Http.Headers.AuthenticationHeaderValue("QQBot", token);
        
        var response = await _httpClient.SendAsync(request, cancellationToken);
        var content = await response.Content.ReadAsStringAsync(cancellationToken);
        
        if (!response.IsSuccessStatusCode)
        {
            _logger.LogError("Failed to get gateway URL: {StatusCode} - {Content}", response.StatusCode, content);
            return null;
        }
        
        var gatewayResponse = JsonSerializer.Deserialize<QQGatewayResponse>(content);
        return gatewayResponse?.Url;
    }
    
    /// <summary>
    /// 接收消息循环
    /// </summary>
    private async Task ReceiveLoopAsync(CancellationToken cancellationToken)
    {
        var buffer = new byte[8192];
        var messageBuilder = new StringBuilder();
        
        try
        {
            while (!cancellationToken.IsCancellationRequested && _webSocket?.State == WebSocketState.Open)
            {
                var result = await _webSocket.ReceiveAsync(new ArraySegment<byte>(buffer), cancellationToken);
                
                if (result.MessageType == WebSocketMessageType.Close)
                {
                    _logger.LogInformation("WebSocket closed by server: {Status} - {Description}",
                        result.CloseStatus, result.CloseStatusDescription);
                    await HandleDisconnectionAsync(cancellationToken);
                    break;
                }
                
                messageBuilder.Append(Encoding.UTF8.GetString(buffer, 0, result.Count));
                
                if (result.EndOfMessage)
                {
                    var message = messageBuilder.ToString();
                    messageBuilder.Clear();
                    
                    await ProcessMessageAsync(message, cancellationToken);
                }
            }
        }
        catch (OperationCanceledException)
        {
            _logger.LogDebug("Receive loop cancelled");
        }
        catch (WebSocketException ex)
        {
            _logger.LogError(ex, "WebSocket error in receive loop");
            await HandleDisconnectionAsync(cancellationToken);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error in receive loop");
        }
    }
    
    /// <summary>
    /// 处理收到的消息
    /// </summary>
    private async Task ProcessMessageAsync(string message, CancellationToken cancellationToken)
    {
        try
        {
            var payload = JsonSerializer.Deserialize<QQWebhookEvent>(message);
            if (payload == null) return;
            
            // 更新序列号
            if (payload.Sequence.HasValue)
            {
                _lastSequence = payload.Sequence.Value;
            }
            
            switch (payload.OpCode)
            {
                case QQOpCode.Hello:
                    await HandleHelloAsync(message, cancellationToken);
                    break;
                    
                case QQOpCode.HeartbeatAck:
                    _logger.LogDebug("Received heartbeat ACK");
                    break;
                    
                case QQOpCode.Dispatch:
                    await HandleDispatchAsync(payload, message, cancellationToken);
                    break;
                    
                case QQOpCode.Reconnect:
                    _logger.LogWarning("Server requested reconnect");
                    await HandleDisconnectionAsync(cancellationToken);
                    break;
                    
                case QQOpCode.InvalidSession:
                    _logger.LogWarning("Invalid session, will re-identify");
                    _sessionId = null;
                    await HandleDisconnectionAsync(cancellationToken);
                    break;
                    
                default:
                    _logger.LogDebug("Received unknown opcode: {OpCode}", payload.OpCode);
                    break;
            }
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error processing message: {Message}", message);
        }
    }
    
    /// <summary>
    /// 处理 Hello 消息
    /// </summary>
    private async Task HandleHelloAsync(string message, CancellationToken cancellationToken)
    {
        try
        {
            // 解析心跳间隔
            using var doc = JsonDocument.Parse(message);
            if (doc.RootElement.TryGetProperty("d", out var data) &&
                data.TryGetProperty("heartbeat_interval", out var interval))
            {
                _heartbeatInterval = interval.GetInt32();
            }
            else
            {
                _heartbeatInterval = _options.HeartbeatInterval;
            }
            
            _logger.LogDebug("Received Hello, heartbeat interval: {Interval}ms", _heartbeatInterval);
            
            // 启动心跳
            StartHeartbeat();
            
            // 发送鉴权或恢复
            if (!string.IsNullOrEmpty(_sessionId))
            {
                await ResumeAsync(cancellationToken);
            }
            else
            {
                // 默认订阅公域消息事件
                // 1 << 30 = 群聊 @ 消息
                // 1 << 25 = C2C 私聊消息
                var intents = (1 << 30) | (1 << 25);
                await IdentifyAsync(intents, cancellationToken);
            }
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error handling Hello message");
        }
    }
    
    /// <summary>
    /// 处理 Dispatch 消息
    /// </summary>
    private Task HandleDispatchAsync(QQWebhookEvent payload, string rawMessage, CancellationToken cancellationToken)
    {
        var eventType = payload.EventType;
        
        if (eventType == QQEventType.Ready)
        {
            // 解析 Ready 数据获取 session_id
            try
            {
                using var doc = JsonDocument.Parse(rawMessage);
                if (doc.RootElement.TryGetProperty("d", out var data) &&
                    data.TryGetProperty("session_id", out var sessionId))
                {
                    _sessionId = sessionId.GetString();
                    _logger.LogInformation("Ready! Session ID: {SessionId}", _sessionId);
                    OnConnectionStateChanged(QQConnectionState.Ready);
                }
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error parsing Ready event");
            }
        }
        else if (eventType == QQEventType.Resumed)
        {
            _logger.LogInformation("Session resumed successfully");
            OnConnectionStateChanged(QQConnectionState.Ready);
        }
        else
        {
            // 触发消息接收事件
            OnMessageReceived(eventType ?? string.Empty, rawMessage);
        }
        
        return Task.CompletedTask;
    }
    
    /// <summary>
    /// 处理断开连接
    /// </summary>
    private async Task HandleDisconnectionAsync(CancellationToken cancellationToken)
    {
        _isConnected = false;
        OnConnectionStateChanged(QQConnectionState.Disconnected);
        
        // 尝试重连
        if (_reconnectAttempts < _options.MaxReconnectAttempts)
        {
            _reconnectAttempts++;
            var delay = _options.ReconnectInterval * _reconnectAttempts;
            
            _logger.LogInformation("Attempting to reconnect ({Attempt}/{Max}) in {Delay}ms",
                _reconnectAttempts, _options.MaxReconnectAttempts, delay);
            
            await Task.Delay(delay, cancellationToken);
            
            try
            {
                OnConnectionStateChanged(QQConnectionState.Reconnecting);
                await ConnectAsync(cancellationToken);
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Reconnection attempt {Attempt} failed", _reconnectAttempts);
            }
        }
        else
        {
            _logger.LogError("Max reconnection attempts reached, giving up");
            OnConnectionStateChanged(QQConnectionState.Failed);
        }
    }
    
    /// <summary>
    /// 启动心跳
    /// </summary>
    private void StartHeartbeat()
    {
        StopHeartbeat();
        
        _heartbeatCts = new CancellationTokenSource();
        _heartbeatTask = HeartbeatLoopAsync(_heartbeatCts.Token);
        
        _logger.LogDebug("Heartbeat started with interval: {Interval}ms", _heartbeatInterval);
    }
    
    /// <summary>
    /// 停止心跳
    /// </summary>
    private void StopHeartbeat()
    {
        _heartbeatCts?.Cancel();
        _heartbeatCts?.Dispose();
        _heartbeatCts = null;
        _heartbeatTask = null;
    }
    
    /// <summary>
    /// 心跳循环
    /// </summary>
    private async Task HeartbeatLoopAsync(CancellationToken cancellationToken)
    {
        try
        {
            while (!cancellationToken.IsCancellationRequested)
            {
                await Task.Delay(_heartbeatInterval, cancellationToken);
                
                if (_webSocket?.State == WebSocketState.Open)
                {
                    await SendHeartbeatAsync(cancellationToken);
                }
            }
        }
        catch (OperationCanceledException)
        {
            _logger.LogDebug("Heartbeat loop cancelled");
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error in heartbeat loop");
        }
    }
    
    /// <summary>
    /// 发送心跳
    /// </summary>
    private async Task SendHeartbeatAsync(CancellationToken cancellationToken)
    {
        var payload = new
        {
            op = QQOpCode.Heartbeat,
            d = _lastSequence > 0 ? (int?)_lastSequence : null
        };
        
        await SendAsync(JsonSerializer.Serialize(payload), cancellationToken);
        _logger.LogDebug("Sent heartbeat, last sequence: {Sequence}", _lastSequence);
    }
    
    /// <summary>
    /// 发送消息
    /// </summary>
    private async Task SendAsync(string message, CancellationToken cancellationToken)
    {
        if (_webSocket?.State != WebSocketState.Open)
        {
            _logger.LogWarning("Cannot send message, WebSocket is not open");
            return;
        }
        
        var bytes = Encoding.UTF8.GetBytes(message);
        await _webSocket.SendAsync(new ArraySegment<byte>(bytes), WebSocketMessageType.Text, true, cancellationToken);
    }
    
    /// <summary>
    /// 触发连接状态变更事件
    /// </summary>
    private void OnConnectionStateChanged(QQConnectionState state)
    {
        ConnectionStateChanged?.Invoke(this, new QQConnectionStateChangedEventArgs(state));
    }
    
    /// <summary>
    /// 触发消息接收事件
    /// </summary>
    private void OnMessageReceived(string eventType, string rawMessage)
    {
        MessageReceived?.Invoke(this, new QQMessageReceivedEventArgs(eventType, rawMessage));
    }
    
    #endregion
    
    #region IDisposable
    
    public void Dispose()
    {
        if (_isDisposed) return;
        
        _isDisposed = true;
        
        StopHeartbeat();
        _receiveCts?.Cancel();
        _receiveCts?.Dispose();
        
        _webSocket?.Dispose();
        
        GC.SuppressFinalize(this);
    }
    
    #endregion
}


/// <summary>
/// QQ 连接状态
/// </summary>
public enum QQConnectionState
{
    /// <summary>
    /// 已断开
    /// </summary>
    Disconnected,
    
    /// <summary>
    /// 连接中
    /// </summary>
    Connecting,
    
    /// <summary>
    /// 已连接
    /// </summary>
    Connected,
    
    /// <summary>
    /// 就绪（已鉴权）
    /// </summary>
    Ready,
    
    /// <summary>
    /// 重连中
    /// </summary>
    Reconnecting,
    
    /// <summary>
    /// 连接失败
    /// </summary>
    Failed
}

/// <summary>
/// 连接状态变更事件参数
/// </summary>
public class QQConnectionStateChangedEventArgs : EventArgs
{
    /// <summary>
    /// 新状态
    /// </summary>
    public QQConnectionState State { get; }
    
    public QQConnectionStateChangedEventArgs(QQConnectionState state)
    {
        State = state;
    }
}

/// <summary>
/// 消息接收事件参数
/// </summary>
public class QQMessageReceivedEventArgs : EventArgs
{
    /// <summary>
    /// 事件类型
    /// </summary>
    public string EventType { get; }
    
    /// <summary>
    /// 原始消息
    /// </summary>
    public string RawMessage { get; }
    
    public QQMessageReceivedEventArgs(string eventType, string rawMessage)
    {
        EventType = eventType;
        RawMessage = rawMessage;
    }
}
