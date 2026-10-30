using Microsoft.Extensions.Hosting;
using Microsoft.Extensions.Logging;
using OpenDeepWiki.Chat.Providers;
using OpenDeepWiki.Chat.Routing;

namespace OpenDeepWiki.Chat;

/// <summary>
/// Provider 初始化后台服务
/// 负责在应用启动时初始化所有 Provider 并注册到路由器
/// Requirements: 2.2, 2.4
/// </summary>
public class ProviderInitializationService : IHostedService
{
    private readonly IServiceProvider _serviceProvider;
    private readonly ILogger<ProviderInitializationService> _logger;

    public ProviderInitializationService(
        IServiceProvider serviceProvider,
        ILogger<ProviderInitializationService> logger)
    {
        _serviceProvider = serviceProvider;
        _logger = logger;
    }

    public async Task StartAsync(CancellationToken cancellationToken)
    {
        _logger.LogInformation("开始初始化 Chat Provider...");

        using var scope = _serviceProvider.CreateScope();
        var providers = scope.ServiceProvider.GetRequiredService<IEnumerable<IMessageProvider>>();
        var router = _serviceProvider.GetRequiredService<IMessageRouter>();

        foreach (var provider in providers)
        {
            try
            {
                _logger.LogInformation("正在初始化 Provider: {PlatformId} ({DisplayName})", 
                    provider.PlatformId, provider.DisplayName);

                await provider.InitializeAsync(cancellationToken);
                
                // 注册到路由器
                router.RegisterProvider(provider);
                
                _logger.LogInformation("Provider {PlatformId} 初始化成功，已启用: {IsEnabled}", 
                    provider.PlatformId, provider.IsEnabled);
            }
            catch (Exception ex)
            {
                // Requirements: 2.4 - Provider 初始化失败时记录错误并继续加载其他 Provider
                _logger.LogError(ex, "Provider {PlatformId} 初始化失败，将继续加载其他 Provider", 
                    provider.PlatformId);
            }
        }

        _logger.LogInformation("Chat Provider 初始化完成，共注册 {Count} 个 Provider", 
            router.GetAllProviders().Count());
    }

    public async Task StopAsync(CancellationToken cancellationToken)
    {
        _logger.LogInformation("正在关闭 Chat Provider...");

        var router = _serviceProvider.GetRequiredService<IMessageRouter>();
        
        foreach (var provider in router.GetAllProviders())
        {
            try
            {
                await provider.ShutdownAsync(cancellationToken);
                _logger.LogInformation("Provider {PlatformId} 已关闭", provider.PlatformId);
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Provider {PlatformId} 关闭时发生错误", provider.PlatformId);
            }
        }

        _logger.LogInformation("Chat Provider 已全部关闭");
    }
}
