using System.Collections.Concurrent;
using System.Runtime.CompilerServices;
using Microsoft.Extensions.Logging;
using OpenDeepWiki.Chat.Abstractions;
using OpenDeepWiki.Chat.Providers;
using OpenDeepWiki.Chat.Queue;

namespace OpenDeepWiki.Chat.Callbacks;

/// <summary>
/// 消息回调管理器
/// 负责将 Agent 响应路由到正确的 Provider 并发送给用户
/// </summary>
public class CallbackManager : IMessageCallback
{
    private readonly ILogger<CallbackManager> _logger;
    private readonly IMessageQueue _messageQueue;
    private readonly Func<string, IMessageProvider?> _providerResolver;
    private readonly ConcurrentDictionary<string, SendStatus> _sendStatusCache;
    private readonly CallbackManagerOptions _options;

    public CallbackManager(
        ILogger<CallbackManager> logger,
        IMessageQueue messageQueue,
        Func<string, IMessageProvider?> providerResolver,
        CallbackManagerOptions? options = null)
    {
        _logger = logger;
        _messageQueue = messageQueue;
        _providerResolver = providerResolver;
        _sendStatusCache = new ConcurrentDictionary<string, SendStatus>();
        _options = options ?? new CallbackManagerOptions();
    }

    /// <inheritdoc />
    public async Task<SendResult> SendAsync(
        string platform,
        string userId,
        IChatMessage message,
        CancellationToken cancellationToken = default)
    {
        ArgumentException.ThrowIfNullOrWhiteSpace(platform);
        ArgumentException.ThrowIfNullOrWhiteSpace(userId);
        ArgumentNullException.ThrowIfNull(message);

        _sendStatusCache[message.MessageId] = SendStatus.Pending;

        var provider = _providerResolver(platform);
        if (provider == null)
        {
            _logger.LogWarning("Provider not found for platform: {Platform}", platform);
            _sendStatusCache[message.MessageId] = SendStatus.Failed;
            return new SendResult(false, ErrorCode: "PROVIDER_NOT_FOUND", 
                ErrorMessage: $"Provider not found for platform: {platform}");
        }

        if (!provider.IsEnabled)
        {
            _logger.LogWarning("Provider {Platform} is disabled", platform);
            _sendStatusCache[message.MessageId] = SendStatus.Failed;
            return new SendResult(false, ErrorCode: "PROVIDER_DISABLED",
                ErrorMessage: $"Provider {platform} is disabled");
        }

        try
        {
            _sendStatusCache[message.MessageId] = SendStatus.Sending;
            var result = await provider.SendMessageAsync(message, userId, cancellationToken);

            if (result.Success)
            {
                _sendStatusCache[message.MessageId] = SendStatus.Sent;
                _logger.LogDebug("Message {MessageId} sent successfully to {UserId} on {Platform}",
                    message.MessageId, userId, platform);
            }
            else if (result.ShouldRetry)
            {
                _sendStatusCache[message.MessageId] = SendStatus.Retrying;
                await EnqueueForRetryAsync(platform, userId, message, cancellationToken);
            }
            else
            {
                _sendStatusCache[message.MessageId] = SendStatus.Failed;
                _logger.LogWarning("Failed to send message {MessageId}: {Error}",
                    message.MessageId, result.ErrorMessage);
            }

            return result;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error sending message {MessageId} to {UserId} on {Platform}",
                message.MessageId, userId, platform);
            _sendStatusCache[message.MessageId] = SendStatus.Failed;
            
            // 加入重试队列
            await EnqueueForRetryAsync(platform, userId, message, cancellationToken);
            
            return new SendResult(false, ErrorCode: "SEND_ERROR",
                ErrorMessage: ex.Message, ShouldRetry: true);
        }
    }


    /// <inheritdoc />
    public async Task<IEnumerable<SendResult>> SendBatchAsync(
        string platform,
        string userId,
        IEnumerable<IChatMessage> messages,
        CancellationToken cancellationToken = default)
    {
        ArgumentException.ThrowIfNullOrWhiteSpace(platform);
        ArgumentException.ThrowIfNullOrWhiteSpace(userId);
        ArgumentNullException.ThrowIfNull(messages);

        var messageList = messages.ToList();
        if (messageList.Count == 0)
        {
            return Enumerable.Empty<SendResult>();
        }

        var provider = _providerResolver(platform);
        if (provider == null)
        {
            _logger.LogWarning("Provider not found for platform: {Platform}", platform);
            return messageList.Select(m => new SendResult(false, 
                ErrorCode: "PROVIDER_NOT_FOUND",
                ErrorMessage: $"Provider not found for platform: {platform}"));
        }

        if (!provider.IsEnabled)
        {
            _logger.LogWarning("Provider {Platform} is disabled", platform);
            return messageList.Select(m => new SendResult(false,
                ErrorCode: "PROVIDER_DISABLED",
                ErrorMessage: $"Provider {platform} is disabled"));
        }

        var results = new List<SendResult>();
        
        foreach (var message in messageList)
        {
            _sendStatusCache[message.MessageId] = SendStatus.Pending;
        }

        try
        {
            // 使用 Provider 的批量发送方法
            var batchResults = await provider.SendMessagesAsync(messageList, userId, cancellationToken);
            var resultList = batchResults.ToList();

            for (int i = 0; i < messageList.Count && i < resultList.Count; i++)
            {
                var message = messageList[i];
                var result = resultList[i];

                if (result.Success)
                {
                    _sendStatusCache[message.MessageId] = SendStatus.Sent;
                }
                else if (result.ShouldRetry)
                {
                    _sendStatusCache[message.MessageId] = SendStatus.Retrying;
                    await EnqueueForRetryAsync(platform, userId, message, cancellationToken);
                }
                else
                {
                    _sendStatusCache[message.MessageId] = SendStatus.Failed;
                }

                results.Add(result);
            }

            return results;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error sending batch messages to {UserId} on {Platform}", userId, platform);
            
            // 将所有消息加入重试队列
            foreach (var message in messageList)
            {
                _sendStatusCache[message.MessageId] = SendStatus.Retrying;
                await EnqueueForRetryAsync(platform, userId, message, cancellationToken);
                results.Add(new SendResult(false, ErrorCode: "BATCH_SEND_ERROR",
                    ErrorMessage: ex.Message, ShouldRetry: true));
            }

            return results;
        }
    }

    /// <inheritdoc />
    public async IAsyncEnumerable<SendResult> SendStreamAsync(
        string platform,
        string userId,
        IAsyncEnumerable<string> contentStream,
        [EnumeratorCancellation] CancellationToken cancellationToken = default)
    {
        ArgumentException.ThrowIfNullOrWhiteSpace(platform);
        ArgumentException.ThrowIfNullOrWhiteSpace(userId);
        ArgumentNullException.ThrowIfNull(contentStream);

        var provider = _providerResolver(platform);
        if (provider == null)
        {
            _logger.LogWarning("Provider not found for platform: {Platform}", platform);
            yield return new SendResult(false, ErrorCode: "PROVIDER_NOT_FOUND",
                ErrorMessage: $"Provider not found for platform: {platform}");
            yield break;
        }

        if (!provider.IsEnabled)
        {
            _logger.LogWarning("Provider {Platform} is disabled", platform);
            yield return new SendResult(false, ErrorCode: "PROVIDER_DISABLED",
                ErrorMessage: $"Provider {platform} is disabled");
            yield break;
        }

        var buffer = new System.Text.StringBuilder();
        var lastSendTime = DateTimeOffset.UtcNow;

        await foreach (var chunk in contentStream.WithCancellation(cancellationToken))
        {
            buffer.Append(chunk);

            // 根据配置的间隔或缓冲区大小决定是否发送
            var timeSinceLastSend = DateTimeOffset.UtcNow - lastSendTime;
            if (buffer.Length >= _options.StreamBufferSize || 
                timeSinceLastSend >= TimeSpan.FromMilliseconds(_options.StreamFlushIntervalMs))
            {
                var message = new ChatMessage
                {
                    Content = buffer.ToString(),
                    Platform = platform,
                    MessageType = ChatMessageType.Text
                };

                var result = await provider.SendMessageAsync(message, userId, cancellationToken);
                yield return result;

                buffer.Clear();
                lastSendTime = DateTimeOffset.UtcNow;
            }
        }

        // 发送剩余内容
        if (buffer.Length > 0)
        {
            var finalMessage = new ChatMessage
            {
                Content = buffer.ToString(),
                Platform = platform,
                MessageType = ChatMessageType.Text
            };

            var finalResult = await provider.SendMessageAsync(finalMessage, userId, cancellationToken);
            yield return finalResult;
        }
    }

    /// <inheritdoc />
    public Task<SendStatus> GetSendStatusAsync(
        string messageId,
        CancellationToken cancellationToken = default)
    {
        ArgumentException.ThrowIfNullOrWhiteSpace(messageId);

        if (_sendStatusCache.TryGetValue(messageId, out var status))
        {
            return Task.FromResult(status);
        }

        return Task.FromResult(SendStatus.Unknown);
    }

    /// <summary>
    /// 将消息加入重试队列
    /// </summary>
    private async Task EnqueueForRetryAsync(
        string platform,
        string userId,
        IChatMessage message,
        CancellationToken cancellationToken)
    {
        var queuedMessage = new QueuedMessage(
            Id: Guid.NewGuid().ToString(),
            Message: message,
            SessionId: string.Empty,
            TargetUserId: userId,
            Type: QueuedMessageType.Retry,
            RetryCount: 1,
            ScheduledAt: DateTimeOffset.UtcNow.AddSeconds(_options.RetryDelaySeconds)
        );

        await _messageQueue.EnqueueAsync(queuedMessage, cancellationToken);
        _logger.LogDebug("Message {MessageId} enqueued for retry", message.MessageId);
    }
}

/// <summary>
/// CallbackManager 配置选项
/// </summary>
public class CallbackManagerOptions
{
    /// <summary>
    /// 重试延迟秒数
    /// </summary>
    public int RetryDelaySeconds { get; set; } = 30;

    /// <summary>
    /// 流式发送缓冲区大小
    /// </summary>
    public int StreamBufferSize { get; set; } = 500;

    /// <summary>
    /// 流式发送刷新间隔（毫秒）
    /// </summary>
    public int StreamFlushIntervalMs { get; set; } = 1000;
}
