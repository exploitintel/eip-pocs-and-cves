using Microsoft.EntityFrameworkCore;
using Microsoft.Extensions.AI;
using ModelContextProtocol.Client;
using OpenDeepWiki.EFCore;
using OpenDeepWiki.Entities;

namespace OpenDeepWiki.Services.Chat;

/// <summary>
/// Interface for converting MCP configurations to AI tools.
/// </summary>
public interface IMcpToolConverter
{
    /// <summary>
    /// Converts MCP configurations to AI tools.
    /// </summary>
    /// <param name="mcpIds">List of MCP configuration IDs to convert.</param>
    /// <param name="cancellationToken">Cancellation token.</param>
    /// <returns>List of AI tools created from MCP configurations.</returns>
    Task<List<AITool>> ConvertMcpConfigsToToolsAsync(
        List<string> mcpIds,
        CancellationToken cancellationToken = default);
}

/// <summary>
/// Converts MCP configurations to AI tools that can be used by the chat assistant.
/// Connects to each configured MCP server as a real MCP client (Streamable HTTP) and
/// exposes every tool the server advertises, rather than a single opaque wrapper tool.
/// </summary>
public class McpToolConverter : IMcpToolConverter, IAsyncDisposable
{
    private readonly IContext _context;
    private readonly ILoggerFactory _loggerFactory;
    private readonly ILogger<McpToolConverter> _logger;
    private readonly List<McpClient> _clients = new();

    public McpToolConverter(
        IContext context,
        ILoggerFactory loggerFactory,
        ILogger<McpToolConverter> logger)
    {
        _context = context;
        _loggerFactory = loggerFactory;
        _logger = logger;
    }

    /// <inheritdoc />
    public async Task<List<AITool>> ConvertMcpConfigsToToolsAsync(
        List<string> mcpIds,
        CancellationToken cancellationToken = default)
    {
        var tools = new List<AITool>();

        if (mcpIds == null || mcpIds.Count == 0)
        {
            return tools;
        }

        // Load MCP configurations from database
        var mcpConfigs = await _context.McpConfigs
            .Where(m => mcpIds.Contains(m.Id) && m.IsActive && !m.IsDeleted)
            .ToListAsync(cancellationToken);

        foreach (var config in mcpConfigs)
        {
            try
            {
                var mcpTools = await ConnectAndListToolsAsync(config, cancellationToken);
                tools.AddRange(mcpTools);
                _logger.LogInformation("Loaded {Count} tools from MCP server: {Name}", mcpTools.Count, config.Name);
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Failed to connect to MCP server: {Name}", config.Name);
            }
        }

        return tools;
    }

    /// <summary>
    /// Connects to an MCP server over Streamable HTTP and lists the tools it exposes.
    /// The connection is kept open for the lifetime of this converter (one chat request)
    /// so the returned tools remain callable; it is closed in <see cref="DisposeAsync"/>.
    /// </summary>
    private async Task<List<AITool>> ConnectAndListToolsAsync(McpConfig config, CancellationToken cancellationToken)
    {
        var transportOptions = new HttpClientTransportOptions
        {
            Endpoint = new Uri(config.ServerUrl),
            Name = config.Name
        };

        if (!string.IsNullOrEmpty(config.ApiKey))
        {
            transportOptions.AdditionalHeaders = new Dictionary<string, string>
            {
                ["Authorization"] = $"Bearer {config.ApiKey}"
            };
        }

        var transport = new HttpClientTransport(transportOptions, _loggerFactory);
        var client = await McpClient.CreateAsync(transport, loggerFactory: _loggerFactory, cancellationToken: cancellationToken);
        _clients.Add(client);

        var mcpTools = await client.ListToolsAsync(cancellationToken: cancellationToken);
        return mcpTools.Cast<AITool>().ToList();
    }

    public async ValueTask DisposeAsync()
    {
        foreach (var client in _clients)
        {
            try
            {
                await client.DisposeAsync();
            }
            catch (Exception ex)
            {
                _logger.LogWarning(ex, "Failed to dispose MCP client");
            }
        }

        _clients.Clear();
    }
}
