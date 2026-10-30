using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Design;

namespace OpenDeepWiki.Sqlite;

/// <summary>
/// 设计时数据库上下文工厂，用于EF Core迁移
/// </summary>
public class DesignTimeDbContextFactory : IDesignTimeDbContextFactory<SqliteDbContext>
{
    public SqliteDbContext CreateDbContext(string[] args)
    {
        var optionsBuilder = new DbContextOptionsBuilder<SqliteDbContext>();
        // 使用一个虚拟连接字符串，仅用于生成迁移
        optionsBuilder.UseSqlite("Data Source=opendeepwiki.db");
        return new SqliteDbContext(optionsBuilder.Options);
    }
}
