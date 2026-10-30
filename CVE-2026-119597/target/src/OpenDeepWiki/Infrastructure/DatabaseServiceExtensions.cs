using OpenDeepWiki.EFCore;

namespace OpenDeepWiki.Infrastructure;

/// <summary>
/// 数据库服务扩展
/// </summary>
public static class DatabaseServiceExtensions
{
    /// <summary>
    /// 根据配置添加数据库服务
    /// </summary>
    public static IServiceCollection AddDatabase(this IServiceCollection services, IConfiguration configuration)
    {
        var dbType = configuration.GetValue<string>("Database:Type")?.ToLowerInvariant()
            ?? Environment.GetEnvironmentVariable("DB_TYPE")?.ToLowerInvariant()
            ?? "sqlite";

        var connectionString = configuration.GetConnectionString("Default")
            ?? Environment.GetEnvironmentVariable("CONNECTION_STRING")
            ?? GetDefaultConnectionString(dbType);

        return dbType switch
        {
            "sqlite" => AddSqlite(services, connectionString),
            "postgresql" or "postgres" => AddPostgresql(services, connectionString),
            _ => throw new InvalidOperationException($"不支持的数据库类型: {dbType}。支持的类型: sqlite, postgresql")
        };
    }

    private static IServiceCollection AddSqlite(IServiceCollection services, string connectionString)
    {
        // 动态加载 Sqlite 程序集
        var assembly = LoadProviderAssembly("OpenDeepWiki.Sqlite");
        var extensionType = assembly.GetType("OpenDeepWiki.Sqlite.SqliteServiceCollectionExtensions")
            ?? throw new InvalidOperationException("找不到 SqliteServiceCollectionExtensions 类型");

        var method = extensionType.GetMethod("AddOpenDeepWikiSqlite")
            ?? throw new InvalidOperationException("找不到 AddOpenDeepWikiSqlite 方法");

        method.Invoke(null, [services, connectionString]);
        return services;
    }

    private static IServiceCollection AddPostgresql(IServiceCollection services, string connectionString)
    {
        // 动态加载 Postgresql 程序集
        var assembly = LoadProviderAssembly("OpenDeepWiki.Postgresql");
        var extensionType = assembly.GetType("OpenDeepWiki.Postgresql.PostgresqlServiceCollectionExtensions")
            ?? throw new InvalidOperationException("找不到 PostgresqlServiceCollectionExtensions 类型");

        var method = extensionType.GetMethod("AddOpenDeepWikiPostgresql")
            ?? throw new InvalidOperationException("找不到 AddOpenDeepWikiPostgresql 方法");

        method.Invoke(null, [services, connectionString]);
        return services;
    }

    private static System.Reflection.Assembly LoadProviderAssembly(string assemblyName)
    {
        try
        {
            return System.Reflection.Assembly.Load(assemblyName);
        }
        catch (Exception ex)
        {
            throw new InvalidOperationException(
                $"无法加载数据库提供程序程序集 '{assemblyName}'。请确保已添加对应的项目引用。", ex);
        }
    }

    private static string GetDefaultConnectionString(string dbType)
    {
        return dbType switch
        {
            "sqlite" => "Data Source=opendeepwiki.db",
            "postgresql" or "postgres" => "Host=localhost;Database=opendeepwiki;Username=postgres;Password=postgres",
            _ => throw new InvalidOperationException($"未知的数据库类型: {dbType}")
        };
    }
}
