using System.Reflection;

namespace OpenDeepWiki.Endpoints;

/// <summary>
/// 系统信息相关接口
/// </summary>
public static class SystemEndpoints
{
    public static void MapSystemEndpoints(this WebApplication app)
    {
        var group = app.MapGroup("/api/system")
            .WithTags("System");

        group.MapGet("/version", GetVersion)
            .WithSummary("获取系统版本信息")
            .WithDescription("返回当前系统的版本号和构建信息");
    }

    /// <summary>
    /// 获取系统版本信息
    /// </summary>
    private static IResult GetVersion()
    {
        var assembly = Assembly.GetExecutingAssembly();
        var version = assembly.GetName().Version?.ToString() ?? "1.0.0";
        var informationalVersion = assembly
            .GetCustomAttribute<AssemblyInformationalVersionAttribute>()?.InformationalVersion ?? version;

        return Results.Ok(new
        {
            success = true,
            data = new
            {
                version = informationalVersion,
                assemblyVersion = version,
                productName = "OpenDeepWiki"
            }
        });
    }
}
