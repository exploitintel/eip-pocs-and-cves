using OpenDeepWiki.Chat.Abstractions;

namespace OpenDeepWiki.Chat.Execution;

/// <summary>
/// Agent 响应
/// </summary>
/// <param name="Success">是否成功</param>
/// <param name="Messages">响应消息列表</param>
/// <param name="ErrorMessage">错误消息（如果失败）</param>
public record AgentResponse(
    bool Success,
    IEnumerable<IChatMessage> Messages,
    string? ErrorMessage = null
)
{
    /// <summary>
    /// 创建成功响应
    /// </summary>
    public static AgentResponse CreateSuccess(IEnumerable<IChatMessage> messages)
        => new(true, messages);
    
    /// <summary>
    /// 创建成功响应（单条消息）
    /// </summary>
    public static AgentResponse CreateSuccess(IChatMessage message)
        => new(true, new[] { message });
    
    /// <summary>
    /// 创建失败响应
    /// </summary>
    public static AgentResponse CreateFailure(string errorMessage)
        => new(false, Enumerable.Empty<IChatMessage>(), errorMessage);
}

/// <summary>
/// Agent 响应块（流式）
/// </summary>
/// <param name="Content">内容块</param>
/// <param name="IsComplete">是否完成</param>
/// <param name="ErrorMessage">错误消息（如果有）</param>
public record AgentResponseChunk(
    string Content,
    bool IsComplete,
    string? ErrorMessage = null
)
{
    /// <summary>
    /// 创建内容块
    /// </summary>
    public static AgentResponseChunk CreateContent(string content)
        => new(content, false);
    
    /// <summary>
    /// 创建完成块
    /// </summary>
    public static AgentResponseChunk CreateComplete()
        => new(string.Empty, true);
    
    /// <summary>
    /// 创建错误块
    /// </summary>
    public static AgentResponseChunk CreateError(string errorMessage)
        => new(string.Empty, true, errorMessage);
}
