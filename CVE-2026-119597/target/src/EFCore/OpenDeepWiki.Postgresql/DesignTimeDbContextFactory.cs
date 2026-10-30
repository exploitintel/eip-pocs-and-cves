using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Design;

namespace OpenDeepWiki.Postgresql;

/// <summary>
/// 设计时数据库上下文工厂，用于EF Core迁移
/// </summary>
public class DesignTimeDbContextFactory : IDesignTimeDbContextFactory<PostgresqlDbContext>
{
    public PostgresqlDbContext CreateDbContext(string[] args)
    {
        var optionsBuilder = new DbContextOptionsBuilder<PostgresqlDbContext>();
        // 使用一个虚拟连接字符串，仅用于生成迁移
        optionsBuilder.UseNpgsql("Host=localhost;Database=opendeepwiki;Username=postgres;Password=postgres");
        return new PostgresqlDbContext(optionsBuilder.Options);
    }
}
