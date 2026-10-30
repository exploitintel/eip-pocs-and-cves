using Microsoft.EntityFrameworkCore;
using Microsoft.Extensions.DependencyInjection;
using OpenDeepWiki.EFCore;

namespace OpenDeepWiki.Postgresql;

public class PostgresqlDbContext(DbContextOptions<PostgresqlDbContext> options)
    : MasterDbContext(options)
{
}

/// <summary>
/// Factory for creating PostgresqlDbContext instances in parallel operations.
/// </summary>
public class PostgresqlContextFactory : IContextFactory
{
    private readonly IDbContextFactory<PostgresqlDbContext> _factory;

    public PostgresqlContextFactory(IDbContextFactory<PostgresqlDbContext> factory)
    {
        _factory = factory ?? throw new ArgumentNullException(nameof(factory));
    }

    public IContext CreateContext()
    {
        return _factory.CreateDbContext();
    }
}

public static class PostgresqlServiceCollectionExtensions
{
    public static IServiceCollection AddOpenDeepWikiPostgresql(
        this IServiceCollection services,
        string connectionString)
    {
        // Register pooled DbContext for both normal DI and factory usage
        services.AddPooledDbContextFactory<PostgresqlDbContext>(
            options => options.UseNpgsql(connectionString));

        // Register IContext to resolve from the pool
        services.AddScoped<IContext>(sp =>
            sp.GetRequiredService<IDbContextFactory<PostgresqlDbContext>>().CreateDbContext());

        // Register IContextFactory
        services.AddSingleton<IContextFactory, PostgresqlContextFactory>();

        return services;
    }
}
