using OpenDeepWiki.Chat.Abstractions;
using OpenDeepWiki.Chat.Sessions;

namespace OpenDeepWiki.Chat.Execution;

/// <summary>
/// Agent 执行器接口
/// 负责处理消息并生成响应
/// </summary>
public interface IAgentExecutor
{
    /// <summary>
    /// 执行 Agent 处理消息
    /// </summary>
    /// <param name="message">用户消息</param>
    /// <param name="session">对话会话</param>
    /// <param name="cancellationToken">取消令牌</param>
    /// <returns>Agent 响应</returns>
    Task<AgentResponse> ExecuteAsync(
        IChatMessage message, 
        IChatSession session, 
        CancellationToken cancellationToken = default);
    
    /// <summary>
    /// 流式执行 Agent
    /// </summary>
    /// <param name="message">用户消息</param>
    /// <param name="session">对话会话</param>
    /// <param name="cancellationToken">取消令牌</param>
    /// <returns>Agent 响应块流</returns>
    IAsyncEnumerable<AgentResponseChunk> ExecuteStreamAsync(
        IChatMessage message, 
        IChatSession session, 
        CancellationToken cancellationToken = default);
}
