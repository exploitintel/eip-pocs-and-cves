using Microsoft.EntityFrameworkCore;
using OpenDeepWiki.EFCore;
using OpenDeepWiki.Entities;
using OpenDeepWiki.Services.AI;
using OpenDeepWiki.Services.Admin;

namespace OpenDeepWiki.Infrastructure;

/// <summary>
/// 数据库初始化服务
/// </summary>
public static class DbInitializer
{
    /// <summary>
    /// 初始化数据库（创建默认角色和OAuth提供商）
    /// </summary>
    public static async Task InitializeAsync(IServiceProvider serviceProvider)
    {
        using var scope = serviceProvider.CreateScope();
        var context = scope.ServiceProvider.GetRequiredService<IContext>();
        var configuration = scope.ServiceProvider.GetRequiredService<IConfiguration>();

        // 确保数据库已创建
        if (context is DbContext dbContext)
        {
            await dbContext.Database.EnsureCreatedAsync();
        }

        // 初始化默认角色
        await InitializeRolesAsync(context);

        // 初始化默认管理员账户
        await InitializeAdminUserAsync(context);

        // 初始化OAuth提供商
        await InitializeOAuthProvidersAsync(context);

        // Schema migrations for existing databases
        if (context is DbContext migrationCtx)
        {
            var isSqlite = migrationCtx.Database.ProviderName?.Contains("Sqlite",
                StringComparison.OrdinalIgnoreCase) == true;

            if (isSqlite)
            {
                await MigrateSqliteAsync(migrationCtx);
            }
            else
            {
                await MigratePostgresqlAsync(migrationCtx);
            }
        }

        // 初始化默认 MCP 提供商
        await InitializeMcpProvidersAsync(context);

        // 初始化系统设置默认值（仅在首次运行时从环境变量创建）
        await BackfillOpenAIResponsesModelProviderTypesAsync(context);

        await SystemSettingDefaults.InitializeDefaultsAsync(configuration, context);

        await MigrateAiConfigurationAsync(scope.ServiceProvider);

        await RefreshBundledSkillsAsync(scope.ServiceProvider);
    }

    private static async Task MigrateAiConfigurationAsync(IServiceProvider serviceProvider)
    {
        try
        {
            var migrationService = serviceProvider.GetRequiredService<IAiConfigurationMigrationService>();
            await migrationService.MigrateAsync();
        }
        catch (Exception ex)
        {
            var logger = serviceProvider.GetService<ILoggerFactory>()?.CreateLogger("DbInitializer");
            logger?.LogWarning(ex, "Failed to migrate legacy AI settings");
        }
    }

    private static async Task RefreshBundledSkillsAsync(IServiceProvider serviceProvider)
    {
        try
        {
            var toolsService = serviceProvider.GetRequiredService<IAdminToolsService>();
            await toolsService.RefreshSkillsFromDiskAsync();
        }
        catch (Exception ex)
        {
            var logger = serviceProvider.GetService<ILoggerFactory>()?.CreateLogger("DbInitializer");
            logger?.LogWarning(ex, "Failed to refresh bundled skills from disk");
        }
    }

    private static async Task BackfillOpenAIResponsesModelProviderTypesAsync(IContext context)
    {
        var models = await context.AiModelConfigs
            .Where(model => !model.IsDeleted)
            .ToListAsync();

        var changed = false;
        foreach (var model in models)
        {
            if (!string.IsNullOrWhiteSpace(model.ProviderType))
            {
                continue;
            }

            var providerType = AiProviderResolver.NormalizeModelProviderType(null, model.ModelId);
            if (providerType == null ||
                string.Equals(model.ProviderType, providerType, StringComparison.OrdinalIgnoreCase))
            {
                continue;
            }

            model.ProviderType = providerType;
            model.UpdatedAt = DateTime.UtcNow;
            changed = true;
        }

        if (changed)
        {
            await context.SaveChangesAsync();
        }
    }

    private static async Task InitializeAdminUserAsync(IContext context)
    {
        const string adminEmail = "admin@routin.ai";
        const string adminPassword = "Admin@123";

        var exists = await context.Users.AnyAsync(u => u.Email == adminEmail && !u.IsDeleted);
        if (exists) return;

        var adminRole = await context.Roles.FirstOrDefaultAsync(r => r.Name == "Admin" && !r.IsDeleted);
        if (adminRole == null) return;

        var adminUser = new User
        {
            Id = Guid.NewGuid().ToString(),
            Name = "admin",
            Email = adminEmail,
            Password = BCrypt.Net.BCrypt.HashPassword(adminPassword),
            Status = 1,
            IsSystem = true,
            CreatedAt = DateTime.UtcNow
        };

        context.Users.Add(adminUser);

        var userRole = new UserRole
        {
            Id = Guid.NewGuid().ToString(),
            UserId = adminUser.Id,
            RoleId = adminRole.Id,
            CreatedAt = DateTime.UtcNow
        };

        context.UserRoles.Add(userRole);
        await context.SaveChangesAsync();
    }

    private static async Task InitializeRolesAsync(IContext context)
    {
        var roles = new[]
        {
            new Role
            {
                Id = Guid.NewGuid().ToString(),
                Name = "Admin",
                Description = "系统管理员",
                IsActive = true,
                IsSystemRole = true,
                CreatedAt = DateTime.UtcNow
            },
            new Role
            {
                Id = Guid.NewGuid().ToString(),
                Name = "User",
                Description = "普通用户",
                IsActive = true,
                IsSystemRole = true,
                CreatedAt = DateTime.UtcNow
            }
        };

        foreach (var role in roles)
        {
            var exists = await context.Roles.AnyAsync(r => r.Name == role.Name && !r.IsDeleted);
            if (!exists)
            {
                context.Roles.Add(role);
            }
        }

        await context.SaveChangesAsync();
    }

    private static async Task InitializeOAuthProvidersAsync(IContext context)
    {
        var providers = new[]
        {
            new OAuthProvider
            {
                Id = Guid.NewGuid().ToString(),
                Name = "github",
                DisplayName = "GitHub",
                AuthorizationUrl = "https://github.com/login/oauth/authorize",
                TokenUrl = "https://github.com/login/oauth/access_token",
                UserInfoUrl = "https://api.github.com/user",
                ClientId = "YOUR_GITHUB_CLIENT_ID",
                ClientSecret = "YOUR_GITHUB_CLIENT_SECRET",
                RedirectUri = "http://localhost:8080/api/oauth/github/callback",
                Scope = "user:email",
                UserInfoMapping = "{\"id\":\"id\",\"name\":\"login\",\"email\":\"email\",\"avatar\":\"avatar_url\"}",
                IsActive = false,
                CreatedAt = DateTime.UtcNow
            },
            new OAuthProvider
            {
                Id = Guid.NewGuid().ToString(),
                Name = "gitee",
                DisplayName = "Gitee",
                AuthorizationUrl = "https://gitee.com/oauth/authorize",
                TokenUrl = "https://gitee.com/oauth/token",
                UserInfoUrl = "https://gitee.com/api/v5/user",
                ClientId = "YOUR_GITEE_CLIENT_ID",
                ClientSecret = "YOUR_GITEE_CLIENT_SECRET",
                RedirectUri = "http://localhost:8080/api/oauth/gitee/callback",
                Scope = "user_info emails",
                UserInfoMapping = "{\"id\":\"id\",\"name\":\"name\",\"email\":\"email\",\"avatar\":\"avatar_url\"}",
                IsActive = false,
                CreatedAt = DateTime.UtcNow
            }
        };

        foreach (var provider in providers)
        {
            var exists = await context.OAuthProviders.AnyAsync(p => p.Name == provider.Name && !p.IsDeleted);
            if (!exists)
            {
                context.OAuthProviders.Add(provider);
            }
        }

        await context.SaveChangesAsync();
    }

    private static async Task InitializeMcpProvidersAsync(IContext context)
    {
        const string providerName = "OpenDeepWiki Global MCP";
        const string globalMcpPath = "/api/mcp";

        var provider = await context.McpProviders
            .FirstOrDefaultAsync(p => p.Name == providerName);

        if (provider is { IsDeleted: true })
        {
            return;
        }

        if (provider is null)
        {
            context.McpProviders.Add(new McpProvider
            {
                Id = Guid.NewGuid().ToString(),
                Name = providerName,
                Description = "Global OpenDeepWiki MCP endpoint that routes questions across repositories.",
                ServerUrl = globalMcpPath,
                TransportType = "streamable_http",
                RequiresApiKey = false,
                IsActive = true,
                SortOrder = 0,
                AllowedTools = """["list_repositories","search_repositories","search_docs","read_doc"]""",
                CreatedAt = DateTime.UtcNow
            });
            await context.SaveChangesAsync();
            return;
        }

        var changed = false;
        if (provider.ServerUrl != globalMcpPath)
        {
            provider.ServerUrl = globalMcpPath;
            changed = true;
        }

        if (changed)
        {
            provider.UpdatedAt = DateTime.UtcNow;
            await context.SaveChangesAsync();
        }
    }

    private static async Task MigrateSqliteAsync(DbContext ctx)
    {
        await ctx.Database.ExecuteSqlRawAsync(@"
            CREATE TABLE IF NOT EXISTS GraphifyArtifacts (
                Id TEXT NOT NULL PRIMARY KEY,
                RepositoryId TEXT NOT NULL,
                RepositoryBranchId TEXT NOT NULL,
                Status INTEGER NOT NULL,
                CommitId TEXT,
                OutputRoot TEXT,
                EntryFilePath TEXT,
                GraphJsonPath TEXT,
                ReportPath TEXT,
                ErrorMessage TEXT,
                StartedAt TEXT,
                CompletedAt TEXT,
                CreatedAt TEXT NOT NULL,
                UpdatedAt TEXT,
                DeletedAt TEXT,
                IsDeleted INTEGER NOT NULL DEFAULT 0,
                Version BLOB,
                FOREIGN KEY (RepositoryId) REFERENCES Repositories(Id) ON DELETE CASCADE,
                FOREIGN KEY (RepositoryBranchId) REFERENCES RepositoryBranches(Id) ON DELETE CASCADE
            )");
        await ctx.Database.ExecuteSqlRawAsync(
            "CREATE UNIQUE INDEX IF NOT EXISTS IX_GraphifyArtifacts_RepositoryBranchId ON GraphifyArtifacts (RepositoryBranchId)");
        await ctx.Database.ExecuteSqlRawAsync(
            "CREATE INDEX IF NOT EXISTS IX_GraphifyArtifacts_RepositoryId ON GraphifyArtifacts (RepositoryId)");
        await ctx.Database.ExecuteSqlRawAsync(
            "CREATE INDEX IF NOT EXISTS IX_GraphifyArtifacts_Status_CreatedAt ON GraphifyArtifacts (Status, CreatedAt)");

        // Create ApiKeys table (SQLite types)
        await ctx.Database.ExecuteSqlRawAsync(@"
            CREATE TABLE IF NOT EXISTS ApiKeys (
                Id TEXT NOT NULL PRIMARY KEY,
                Name TEXT NOT NULL,
                KeyPrefix TEXT NOT NULL,
                KeyHash TEXT NOT NULL,
                UserId TEXT NOT NULL,
                Scope TEXT NOT NULL DEFAULT 'mcp:read',
                ExpiresAt TEXT,
                LastUsedAt TEXT,
                LastUsedIp TEXT,
                CreatedAt TEXT NOT NULL,
                UpdatedAt TEXT,
                DeletedAt TEXT,
                IsDeleted INTEGER NOT NULL DEFAULT 0,
                Version BLOB,
                FOREIGN KEY (UserId) REFERENCES Users(Id)
            )");
        await ctx.Database.ExecuteSqlRawAsync(
            "CREATE UNIQUE INDEX IF NOT EXISTS IX_ApiKeys_KeyPrefix ON ApiKeys (KeyPrefix)");
        await ctx.Database.ExecuteSqlRawAsync(
            "CREATE INDEX IF NOT EXISTS IX_ApiKeys_UserId ON ApiKeys (UserId)");

        await ctx.Database.ExecuteSqlRawAsync(@"
            CREATE TABLE IF NOT EXISTS AiProviderConfigs (
                Id TEXT NOT NULL PRIMARY KEY,
                Name TEXT NOT NULL,
                DisplayName TEXT,
                ProviderType TEXT NOT NULL,
                BaseUrl TEXT NOT NULL,
                ApiKey TEXT,
                AuthType TEXT NOT NULL DEFAULT 'ApiKey',
                IsBuiltIn INTEGER NOT NULL DEFAULT 0,
                IsActive INTEGER NOT NULL DEFAULT 1,
                SupportsModelDiscovery INTEGER NOT NULL DEFAULT 1,
                ModelsEndpoint TEXT,
                DefaultModelId TEXT,
                SystemProxyUrl TEXT,
                OAuthConfigJson TEXT,
                ChannelConfigJson TEXT,
                AccountsJson TEXT,
                RequestOverridesJson TEXT,
                IconUrl TEXT,
                Description TEXT,
                SortOrder INTEGER NOT NULL DEFAULT 0,
                CreatedAt TEXT NOT NULL,
                UpdatedAt TEXT,
                DeletedAt TEXT,
                IsDeleted INTEGER NOT NULL DEFAULT 0,
                Version BLOB
            )");
        await ctx.Database.ExecuteSqlRawAsync(
            "CREATE UNIQUE INDEX IF NOT EXISTS IX_AiProviderConfigs_Name ON AiProviderConfigs (Name)");
        await ctx.Database.ExecuteSqlRawAsync(
            "CREATE INDEX IF NOT EXISTS IX_AiProviderConfigs_IsActive ON AiProviderConfigs (IsActive)");

        await ctx.Database.ExecuteSqlRawAsync(@"
            CREATE TABLE IF NOT EXISTS AiModelConfigs (
                Id TEXT NOT NULL PRIMARY KEY,
                ProviderId TEXT NOT NULL,
                ModelId TEXT NOT NULL,
                Name TEXT NOT NULL,
                DisplayName TEXT,
                ModelType TEXT NOT NULL DEFAULT 'chat',
                ProviderType TEXT,
                ContextWindow INTEGER,
                MaxOutputTokens INTEGER,
                InputTokenPrice TEXT,
                OutputTokenPrice TEXT,
                CacheHitTokenPrice TEXT,
                CacheCreationTokenPrice TEXT,
                SupportsThinking INTEGER NOT NULL DEFAULT 0,
                SupportsVision INTEGER NOT NULL DEFAULT 0,
                SupportsTools INTEGER NOT NULL DEFAULT 1,
                SupportsJsonMode INTEGER NOT NULL DEFAULT 0,
                IsDefault INTEGER NOT NULL DEFAULT 0,
                IsActive INTEGER NOT NULL DEFAULT 1,
                CapabilitiesJson TEXT,
                ThinkingConfigJson TEXT,
                RequestOverridesJson TEXT,
                TagsJson TEXT,
                Description TEXT,
                SortOrder INTEGER NOT NULL DEFAULT 0,
                CreatedAt TEXT NOT NULL,
                UpdatedAt TEXT,
                DeletedAt TEXT,
                IsDeleted INTEGER NOT NULL DEFAULT 0,
                Version BLOB
            )");
        await ctx.Database.ExecuteSqlRawAsync(
            "CREATE UNIQUE INDEX IF NOT EXISTS IX_AiModelConfigs_ProviderId_ModelId ON AiModelConfigs (ProviderId, ModelId)");
        await ctx.Database.ExecuteSqlRawAsync(
            "CREATE INDEX IF NOT EXISTS IX_AiModelConfigs_IsActive ON AiModelConfigs (IsActive)");

        await ctx.Database.ExecuteSqlRawAsync(@"
            CREATE TABLE IF NOT EXISTS BranchGenerationTasks (
                Id TEXT NOT NULL PRIMARY KEY,
                RepositoryId TEXT NOT NULL,
                BranchId TEXT NOT NULL,
                Status INTEGER NOT NULL,
                Mode INTEGER NOT NULL,
                Priority INTEGER NOT NULL,
                IsManualTrigger INTEGER NOT NULL,
                RetryCount INTEGER NOT NULL,
                ErrorMessage TEXT,
                StartedAt TEXT,
                CompletedAt TEXT,
                RequestedBy TEXT,
                TargetCommitId TEXT,
                CreatedAt TEXT NOT NULL,
                UpdatedAt TEXT,
                DeletedAt TEXT,
                IsDeleted INTEGER NOT NULL DEFAULT 0,
                Version BLOB,
                FOREIGN KEY (RepositoryId) REFERENCES Repositories(Id) ON DELETE CASCADE,
                FOREIGN KEY (BranchId) REFERENCES RepositoryBranches(Id) ON DELETE CASCADE
            )");
        await ctx.Database.ExecuteSqlRawAsync(
            "CREATE INDEX IF NOT EXISTS IX_BranchGenerationTasks_BranchId_Status ON BranchGenerationTasks (BranchId, Status)");
        await ctx.Database.ExecuteSqlRawAsync(
            "CREATE UNIQUE INDEX IF NOT EXISTS IX_BranchGenerationTasks_BranchId_Status_Mode ON BranchGenerationTasks (BranchId, Status, Mode) WHERE Status IN (0, 1)");
        await ctx.Database.ExecuteSqlRawAsync(
            "CREATE INDEX IF NOT EXISTS IX_BranchGenerationTasks_RepositoryId_Status ON BranchGenerationTasks (RepositoryId, Status)");
        await ctx.Database.ExecuteSqlRawAsync(
            "CREATE INDEX IF NOT EXISTS IX_BranchGenerationTasks_Status_Priority_CreatedAt ON BranchGenerationTasks (Status, Priority, CreatedAt)");

        await ctx.Database.ExecuteSqlRawAsync(@"
            CREATE TABLE IF NOT EXISTS RepositoryGenerationLocks (
                Id TEXT NOT NULL PRIMARY KEY,
                RepositoryId TEXT NOT NULL,
                OwnerType INTEGER NOT NULL,
                OwnerId TEXT NOT NULL,
                Scope INTEGER NOT NULL,
                AcquiredAt TEXT NOT NULL,
                CreatedAt TEXT NOT NULL,
                UpdatedAt TEXT,
                DeletedAt TEXT,
                IsDeleted INTEGER NOT NULL DEFAULT 0,
                Version BLOB,
                FOREIGN KEY (RepositoryId) REFERENCES Repositories(Id) ON DELETE CASCADE
            )");
        await ctx.Database.ExecuteSqlRawAsync(
            "CREATE UNIQUE INDEX IF NOT EXISTS IX_RepositoryGenerationLocks_RepositoryId ON RepositoryGenerationLocks (RepositoryId)");

        await ctx.Database.ExecuteSqlRawAsync(@"
            CREATE TABLE IF NOT EXISTS WikiGenerationSlots (
                Id TEXT NOT NULL PRIMARY KEY,
                SlotIndex INTEGER NOT NULL,
                WorkType INTEGER,
                RepositoryId TEXT,
                OwnerId TEXT,
                InstanceId TEXT,
                AcquiredAt TEXT,
                HeartbeatAt TEXT,
                CreatedAt TEXT NOT NULL,
                UpdatedAt TEXT,
                DeletedAt TEXT,
                IsDeleted INTEGER NOT NULL DEFAULT 0,
                Version BLOB
            )");
        await ctx.Database.ExecuteSqlRawAsync(
            "CREATE UNIQUE INDEX IF NOT EXISTS IX_WikiGenerationSlots_SlotIndex ON WikiGenerationSlots (SlotIndex)");

        // Add Description column if not exists
        var connection = ctx.Database.GetDbConnection();
        if (connection.State != System.Data.ConnectionState.Open)
            await connection.OpenAsync();
        await AddSqliteColumnIfMissingAsync(connection, ctx, "Repositories", "Description", "TEXT");
        await AddSqliteColumnIfMissingAsync(connection, ctx, "Repositories", "GenerateSkill", "INTEGER NOT NULL DEFAULT 1");
        await AddSqliteColumnIfMissingAsync(connection, ctx, "Repositories", "ScanDepthMode", "INTEGER NOT NULL DEFAULT 0");
        await AddSqliteColumnIfMissingAsync(connection, ctx, "Repositories", "DirectoryTreeDepthOverride", "INTEGER");
        await AddSqliteColumnIfMissingAsync(connection, ctx, "Repositories", "FileListDepthOverride", "INTEGER");
        await AddSqliteColumnIfMissingAsync(connection, ctx, "Repositories", "MaxTreeNodes", "INTEGER");
        await AddSqliteColumnIfMissingAsync(connection, ctx, "Repositories", "MaxFilesPerDirectory", "INTEGER");
        await AddSqliteColumnIfMissingAsync(connection, ctx, "Repositories", "MaxTotalFiles", "INTEGER");
        await AddSqliteColumnIfMissingAsync(connection, ctx, "Repositories", "ExtraExcludedDirsJson", "TEXT");
        await AddSqliteColumnIfMissingAsync(connection, ctx, "Repositories", "ScanProfileHash", "TEXT");
        await AddSqliteColumnIfMissingAsync(connection, ctx, "Repositories", "ScanProfileReason", "TEXT");
        await AddSqliteColumnIfMissingAsync(connection, ctx, "Repositories", "ScanProfileConfidence", "REAL");
        await AddSqliteColumnIfMissingAsync(connection, ctx, "Repositories", "ScanProfileUpdatedAt", "TEXT");
        await AddSqliteColumnIfMissingAsync(connection, ctx, "BranchLanguages", "SkillGeneratedAt", "TEXT");
        await AddSqliteColumnIfMissingAsync(connection, ctx, "BranchLanguages", "SkillMarkdown", "TEXT");
        await AddSqliteColumnIfMissingAsync(connection, ctx, "ModelConfigs", "AiProviderId", "TEXT");
        await AddSqliteColumnIfMissingAsync(connection, ctx, "ChatApps", "AiProviderId", "TEXT");
        await AddSqliteColumnIfMissingAsync(connection, ctx, "AiModelConfigs", "ProviderType", "TEXT");
        await AddSqliteColumnIfMissingAsync(connection, ctx, "AiModelConfigs", "CacheHitTokenPrice", "TEXT");
        await AddSqliteColumnIfMissingAsync(connection, ctx, "AiModelConfigs", "CacheCreationTokenPrice", "TEXT");
        await AddSqliteColumnIfMissingAsync(connection, ctx, "TokenUsages", "ProviderId", "TEXT");
        await AddSqliteColumnIfMissingAsync(connection, ctx, "TokenUsages", "ProviderName", "TEXT");
        await AddSqliteColumnIfMissingAsync(connection, ctx, "TokenUsages", "ProviderType", "TEXT");
        await AddSqliteColumnIfMissingAsync(connection, ctx, "TokenUsages", "ModelId", "TEXT");
        await AddSqliteColumnIfMissingAsync(connection, ctx, "TokenUsages", "CachedInputTokens", "INTEGER NOT NULL DEFAULT 0");
        await AddSqliteColumnIfMissingAsync(connection, ctx, "TokenUsages", "CacheCreationInputTokens", "INTEGER NOT NULL DEFAULT 0");
        await AddSqliteColumnIfMissingAsync(connection, ctx, "TokenUsages", "InputTokenPrice", "TEXT");
        await AddSqliteColumnIfMissingAsync(connection, ctx, "TokenUsages", "OutputTokenPrice", "TEXT");
        await AddSqliteColumnIfMissingAsync(connection, ctx, "TokenUsages", "CacheHitTokenPrice", "TEXT");
        await AddSqliteColumnIfMissingAsync(connection, ctx, "TokenUsages", "CacheCreationTokenPrice", "TEXT");
        await AddSqliteColumnIfMissingAsync(connection, ctx, "TokenUsages", "InputCost", "TEXT NOT NULL DEFAULT '0'");
        await AddSqliteColumnIfMissingAsync(connection, ctx, "TokenUsages", "OutputCost", "TEXT NOT NULL DEFAULT '0'");
        await AddSqliteColumnIfMissingAsync(connection, ctx, "TokenUsages", "TotalCost", "TEXT NOT NULL DEFAULT '0'");
        await AddSqliteColumnIfMissingAsync(connection, ctx, "RepositoryBranches", "GenerationStatus", "INTEGER");
        await AddSqliteColumnIfMissingAsync(connection, ctx, "RepositoryBranches", "LastGenerationTaskId", "TEXT");
        await AddSqliteColumnIfMissingAsync(connection, ctx, "RepositoryBranches", "LastGenerationError", "TEXT");
        await AddSqliteColumnIfMissingAsync(connection, ctx, "RepositoryBranches", "LastGenerationStartedAt", "TEXT");
        await AddSqliteColumnIfMissingAsync(connection, ctx, "RepositoryBranches", "LastGenerationCompletedAt", "TEXT");
        await AddSqliteColumnIfMissingAsync(connection, ctx, "RepositoryProcessingLogs", "BranchId", "TEXT");
        await AddSqliteColumnIfMissingAsync(connection, ctx, "RepositoryProcessingLogs", "GenerationTaskId", "TEXT");
        await AddSqliteColumnIfMissingAsync(connection, ctx, "RepositoryGenerationLocks", "InstanceId", "TEXT");
        await AddSqliteColumnIfMissingAsync(connection, ctx, "RepositoryGenerationLocks", "HeartbeatAt", "TEXT");
        await ctx.Database.ExecuteSqlRawAsync(
            "CREATE INDEX IF NOT EXISTS IX_RepositoryProcessingLogs_RepositoryId_BranchId_GenerationTaskId_CreatedAt ON RepositoryProcessingLogs (RepositoryId, BranchId, GenerationTaskId, CreatedAt)");
        await ctx.Database.ExecuteSqlRawAsync(
            "CREATE INDEX IF NOT EXISTS IX_RepositoryProcessingLogs_BranchId ON RepositoryProcessingLogs (BranchId)");
        await ctx.Database.ExecuteSqlRawAsync(
            "CREATE INDEX IF NOT EXISTS IX_RepositoryProcessingLogs_GenerationTaskId ON RepositoryProcessingLogs (GenerationTaskId)");
    }

    private static async Task AddSqliteColumnIfMissingAsync(
        System.Data.Common.DbConnection connection,
        DbContext ctx,
        string tableName,
        string columnName,
        string columnDefinition)
    {
        await using var cmd = connection.CreateCommand();
        cmd.CommandText = $"SELECT COUNT(*) FROM pragma_table_info('{tableName}') WHERE name=$name";
        var parameter = cmd.CreateParameter();
        parameter.ParameterName = "$name";
        parameter.Value = columnName;
        cmd.Parameters.Add(parameter);

        var result = await cmd.ExecuteScalarAsync();
        if (Convert.ToInt64(result) == 0)
        {
            await ctx.Database.ExecuteSqlRawAsync(
                $"ALTER TABLE {tableName} ADD COLUMN {columnName} {columnDefinition}");
        }
    }

    private static async Task MigratePostgresqlAsync(DbContext ctx)
    {
        await ctx.Database.ExecuteSqlRawAsync(@"
            CREATE TABLE IF NOT EXISTS ""GraphifyArtifacts"" (
                ""Id"" TEXT NOT NULL PRIMARY KEY,
                ""RepositoryId"" TEXT NOT NULL,
                ""RepositoryBranchId"" TEXT NOT NULL,
                ""Status"" INTEGER NOT NULL,
                ""CommitId"" TEXT,
                ""OutputRoot"" TEXT,
                ""EntryFilePath"" TEXT,
                ""GraphJsonPath"" TEXT,
                ""ReportPath"" TEXT,
                ""ErrorMessage"" TEXT,
                ""StartedAt"" TIMESTAMP WITH TIME ZONE,
                ""CompletedAt"" TIMESTAMP WITH TIME ZONE,
                ""CreatedAt"" TIMESTAMP WITH TIME ZONE NOT NULL,
                ""UpdatedAt"" TIMESTAMP WITH TIME ZONE,
                ""DeletedAt"" TIMESTAMP WITH TIME ZONE,
                ""IsDeleted"" BOOLEAN NOT NULL DEFAULT FALSE,
                ""Version"" BYTEA,
                FOREIGN KEY (""RepositoryId"") REFERENCES ""Repositories""(""Id"") ON DELETE CASCADE,
                FOREIGN KEY (""RepositoryBranchId"") REFERENCES ""RepositoryBranches""(""Id"") ON DELETE CASCADE
            )");
        await ctx.Database.ExecuteSqlRawAsync(@"
            CREATE UNIQUE INDEX IF NOT EXISTS ""IX_GraphifyArtifacts_RepositoryBranchId"" ON ""GraphifyArtifacts"" (""RepositoryBranchId"")");
        await ctx.Database.ExecuteSqlRawAsync(@"
            CREATE INDEX IF NOT EXISTS ""IX_GraphifyArtifacts_RepositoryId"" ON ""GraphifyArtifacts"" (""RepositoryId"")");
        await ctx.Database.ExecuteSqlRawAsync(@"
            CREATE INDEX IF NOT EXISTS ""IX_GraphifyArtifacts_Status_CreatedAt"" ON ""GraphifyArtifacts"" (""Status"", ""CreatedAt"")");

        // Create ApiKeys table (PostgreSQL types)
        await ctx.Database.ExecuteSqlRawAsync(@"
            CREATE TABLE IF NOT EXISTS ""ApiKeys"" (
                ""Id"" TEXT NOT NULL PRIMARY KEY,
                ""Name"" TEXT NOT NULL,
                ""KeyPrefix"" TEXT NOT NULL,
                ""KeyHash"" TEXT NOT NULL,
                ""UserId"" TEXT NOT NULL,
                ""Scope"" TEXT NOT NULL DEFAULT 'mcp:read',
                ""ExpiresAt"" TIMESTAMP WITH TIME ZONE,
                ""LastUsedAt"" TIMESTAMP WITH TIME ZONE,
                ""LastUsedIp"" TEXT,
                ""CreatedAt"" TIMESTAMP WITH TIME ZONE NOT NULL,
                ""UpdatedAt"" TIMESTAMP WITH TIME ZONE,
                ""DeletedAt"" TIMESTAMP WITH TIME ZONE,
                ""IsDeleted"" BOOLEAN NOT NULL DEFAULT FALSE,
                ""Version"" BYTEA,
                FOREIGN KEY (""UserId"") REFERENCES ""Users""(""Id"")
            )");
        await ctx.Database.ExecuteSqlRawAsync(@"
            CREATE UNIQUE INDEX IF NOT EXISTS ""IX_ApiKeys_KeyPrefix"" ON ""ApiKeys"" (""KeyPrefix"")");
        await ctx.Database.ExecuteSqlRawAsync(@"
            CREATE INDEX IF NOT EXISTS ""IX_ApiKeys_UserId"" ON ""ApiKeys"" (""UserId"")");

        await ctx.Database.ExecuteSqlRawAsync(@"
            CREATE TABLE IF NOT EXISTS ""AiProviderConfigs"" (
                ""Id"" TEXT NOT NULL PRIMARY KEY,
                ""Name"" TEXT NOT NULL,
                ""DisplayName"" TEXT,
                ""ProviderType"" TEXT NOT NULL,
                ""BaseUrl"" TEXT NOT NULL,
                ""ApiKey"" TEXT,
                ""AuthType"" TEXT NOT NULL DEFAULT 'ApiKey',
                ""IsBuiltIn"" BOOLEAN NOT NULL DEFAULT FALSE,
                ""IsActive"" BOOLEAN NOT NULL DEFAULT TRUE,
                ""SupportsModelDiscovery"" BOOLEAN NOT NULL DEFAULT TRUE,
                ""ModelsEndpoint"" TEXT,
                ""DefaultModelId"" TEXT,
                ""SystemProxyUrl"" TEXT,
                ""OAuthConfigJson"" TEXT,
                ""ChannelConfigJson"" TEXT,
                ""AccountsJson"" TEXT,
                ""RequestOverridesJson"" TEXT,
                ""IconUrl"" TEXT,
                ""Description"" TEXT,
                ""SortOrder"" INTEGER NOT NULL DEFAULT 0,
                ""CreatedAt"" TIMESTAMP WITH TIME ZONE NOT NULL,
                ""UpdatedAt"" TIMESTAMP WITH TIME ZONE,
                ""DeletedAt"" TIMESTAMP WITH TIME ZONE,
                ""IsDeleted"" BOOLEAN NOT NULL DEFAULT FALSE,
                ""Version"" BYTEA
            )");
        await ctx.Database.ExecuteSqlRawAsync(@"
            CREATE UNIQUE INDEX IF NOT EXISTS ""IX_AiProviderConfigs_Name"" ON ""AiProviderConfigs"" (""Name"")");
        await ctx.Database.ExecuteSqlRawAsync(@"
            CREATE INDEX IF NOT EXISTS ""IX_AiProviderConfigs_IsActive"" ON ""AiProviderConfigs"" (""IsActive"")");

        await ctx.Database.ExecuteSqlRawAsync(@"
            CREATE TABLE IF NOT EXISTS ""AiModelConfigs"" (
                ""Id"" TEXT NOT NULL PRIMARY KEY,
                ""ProviderId"" TEXT NOT NULL,
                ""ModelId"" TEXT NOT NULL,
                ""Name"" TEXT NOT NULL,
                ""DisplayName"" TEXT,
                ""ModelType"" TEXT NOT NULL DEFAULT 'chat',
                ""ProviderType"" TEXT,
                ""ContextWindow"" INTEGER,
                ""MaxOutputTokens"" INTEGER,
                ""InputTokenPrice"" NUMERIC(18, 8),
                ""OutputTokenPrice"" NUMERIC(18, 8),
                ""CacheHitTokenPrice"" NUMERIC(18, 8),
                ""CacheCreationTokenPrice"" NUMERIC(18, 8),
                ""SupportsThinking"" BOOLEAN NOT NULL DEFAULT FALSE,
                ""SupportsVision"" BOOLEAN NOT NULL DEFAULT FALSE,
                ""SupportsTools"" BOOLEAN NOT NULL DEFAULT TRUE,
                ""SupportsJsonMode"" BOOLEAN NOT NULL DEFAULT FALSE,
                ""IsDefault"" BOOLEAN NOT NULL DEFAULT FALSE,
                ""IsActive"" BOOLEAN NOT NULL DEFAULT TRUE,
                ""CapabilitiesJson"" TEXT,
                ""ThinkingConfigJson"" TEXT,
                ""RequestOverridesJson"" TEXT,
                ""TagsJson"" TEXT,
                ""Description"" TEXT,
                ""SortOrder"" INTEGER NOT NULL DEFAULT 0,
                ""CreatedAt"" TIMESTAMP WITH TIME ZONE NOT NULL,
                ""UpdatedAt"" TIMESTAMP WITH TIME ZONE,
                ""DeletedAt"" TIMESTAMP WITH TIME ZONE,
                ""IsDeleted"" BOOLEAN NOT NULL DEFAULT FALSE,
                ""Version"" BYTEA
            )");
        await ctx.Database.ExecuteSqlRawAsync(@"
            CREATE UNIQUE INDEX IF NOT EXISTS ""IX_AiModelConfigs_ProviderId_ModelId"" ON ""AiModelConfigs"" (""ProviderId"", ""ModelId"")");
        await ctx.Database.ExecuteSqlRawAsync(@"
            CREATE INDEX IF NOT EXISTS ""IX_AiModelConfigs_IsActive"" ON ""AiModelConfigs"" (""IsActive"")");

        await ctx.Database.ExecuteSqlRawAsync(@"
            CREATE TABLE IF NOT EXISTS ""BranchGenerationTasks"" (
                ""Id"" TEXT NOT NULL PRIMARY KEY,
                ""RepositoryId"" TEXT NOT NULL,
                ""BranchId"" TEXT NOT NULL,
                ""Status"" INTEGER NOT NULL,
                ""Mode"" INTEGER NOT NULL,
                ""Priority"" INTEGER NOT NULL,
                ""IsManualTrigger"" BOOLEAN NOT NULL,
                ""RetryCount"" INTEGER NOT NULL,
                ""ErrorMessage"" TEXT,
                ""StartedAt"" TIMESTAMP WITH TIME ZONE,
                ""CompletedAt"" TIMESTAMP WITH TIME ZONE,
                ""RequestedBy"" TEXT,
                ""TargetCommitId"" TEXT,
                ""CreatedAt"" TIMESTAMP WITH TIME ZONE NOT NULL,
                ""UpdatedAt"" TIMESTAMP WITH TIME ZONE,
                ""DeletedAt"" TIMESTAMP WITH TIME ZONE,
                ""IsDeleted"" BOOLEAN NOT NULL DEFAULT FALSE,
                ""Version"" BYTEA,
                FOREIGN KEY (""RepositoryId"") REFERENCES ""Repositories""(""Id"") ON DELETE CASCADE,
                FOREIGN KEY (""BranchId"") REFERENCES ""RepositoryBranches""(""Id"") ON DELETE CASCADE
            )");
        await ctx.Database.ExecuteSqlRawAsync(@"
            CREATE INDEX IF NOT EXISTS ""IX_BranchGenerationTasks_BranchId_Status"" ON ""BranchGenerationTasks"" (""BranchId"", ""Status"")");
        await ctx.Database.ExecuteSqlRawAsync(@"
            CREATE UNIQUE INDEX IF NOT EXISTS ""IX_BranchGenerationTasks_BranchId_Status_Mode"" ON ""BranchGenerationTasks"" (""BranchId"", ""Status"", ""Mode"") WHERE ""Status"" IN (0, 1)");
        await ctx.Database.ExecuteSqlRawAsync(@"
            CREATE INDEX IF NOT EXISTS ""IX_BranchGenerationTasks_RepositoryId_Status"" ON ""BranchGenerationTasks"" (""RepositoryId"", ""Status"")");
        await ctx.Database.ExecuteSqlRawAsync(@"
            CREATE INDEX IF NOT EXISTS ""IX_BranchGenerationTasks_Status_Priority_CreatedAt"" ON ""BranchGenerationTasks"" (""Status"", ""Priority"", ""CreatedAt"")");

        await ctx.Database.ExecuteSqlRawAsync(@"
            CREATE TABLE IF NOT EXISTS ""RepositoryGenerationLocks"" (
                ""Id"" TEXT NOT NULL PRIMARY KEY,
                ""RepositoryId"" TEXT NOT NULL,
                ""OwnerType"" INTEGER NOT NULL,
                ""OwnerId"" TEXT NOT NULL,
                ""Scope"" INTEGER NOT NULL,
                ""AcquiredAt"" TIMESTAMP WITH TIME ZONE NOT NULL,
                ""CreatedAt"" TIMESTAMP WITH TIME ZONE NOT NULL,
                ""UpdatedAt"" TIMESTAMP WITH TIME ZONE,
                ""DeletedAt"" TIMESTAMP WITH TIME ZONE,
                ""IsDeleted"" BOOLEAN NOT NULL DEFAULT FALSE,
                ""Version"" BYTEA,
                FOREIGN KEY (""RepositoryId"") REFERENCES ""Repositories""(""Id"") ON DELETE CASCADE
            )");
        await ctx.Database.ExecuteSqlRawAsync(@"
            CREATE UNIQUE INDEX IF NOT EXISTS ""IX_RepositoryGenerationLocks_RepositoryId"" ON ""RepositoryGenerationLocks"" (""RepositoryId"")");

        await ctx.Database.ExecuteSqlRawAsync(@"
            CREATE TABLE IF NOT EXISTS ""WikiGenerationSlots"" (
                ""Id"" TEXT NOT NULL PRIMARY KEY,
                ""SlotIndex"" INTEGER NOT NULL,
                ""WorkType"" INTEGER,
                ""RepositoryId"" TEXT,
                ""OwnerId"" TEXT,
                ""InstanceId"" TEXT,
                ""AcquiredAt"" TIMESTAMP WITH TIME ZONE,
                ""HeartbeatAt"" TIMESTAMP WITH TIME ZONE,
                ""CreatedAt"" TIMESTAMP WITH TIME ZONE NOT NULL,
                ""UpdatedAt"" TIMESTAMP WITH TIME ZONE,
                ""DeletedAt"" TIMESTAMP WITH TIME ZONE,
                ""IsDeleted"" BOOLEAN NOT NULL DEFAULT FALSE,
                ""Version"" BYTEA
            )");
        await ctx.Database.ExecuteSqlRawAsync(@"
            CREATE UNIQUE INDEX IF NOT EXISTS ""IX_WikiGenerationSlots_SlotIndex"" ON ""WikiGenerationSlots"" (""SlotIndex"")");

        // Add Description column if not exists
        await ctx.Database.ExecuteSqlRawAsync(@"
            ALTER TABLE ""Repositories"" ADD COLUMN IF NOT EXISTS ""Description"" TEXT;
            ALTER TABLE ""Repositories"" ADD COLUMN IF NOT EXISTS ""GenerateSkill"" BOOLEAN NOT NULL DEFAULT TRUE;
            ALTER TABLE ""Repositories"" ADD COLUMN IF NOT EXISTS ""ScanDepthMode"" INTEGER NOT NULL DEFAULT 0;
            ALTER TABLE ""Repositories"" ADD COLUMN IF NOT EXISTS ""DirectoryTreeDepthOverride"" INTEGER;
            ALTER TABLE ""Repositories"" ADD COLUMN IF NOT EXISTS ""FileListDepthOverride"" INTEGER;
            ALTER TABLE ""Repositories"" ADD COLUMN IF NOT EXISTS ""MaxTreeNodes"" INTEGER;
            ALTER TABLE ""Repositories"" ADD COLUMN IF NOT EXISTS ""MaxFilesPerDirectory"" INTEGER;
            ALTER TABLE ""Repositories"" ADD COLUMN IF NOT EXISTS ""MaxTotalFiles"" INTEGER;
            ALTER TABLE ""Repositories"" ADD COLUMN IF NOT EXISTS ""ExtraExcludedDirsJson"" TEXT;
            ALTER TABLE ""Repositories"" ADD COLUMN IF NOT EXISTS ""ScanProfileHash"" TEXT;
            ALTER TABLE ""Repositories"" ADD COLUMN IF NOT EXISTS ""ScanProfileReason"" TEXT;
            ALTER TABLE ""Repositories"" ADD COLUMN IF NOT EXISTS ""ScanProfileConfidence"" NUMERIC(5, 4);
            ALTER TABLE ""Repositories"" ADD COLUMN IF NOT EXISTS ""ScanProfileUpdatedAt"" TIMESTAMP WITH TIME ZONE;
            ALTER TABLE ""BranchLanguages"" ADD COLUMN IF NOT EXISTS ""SkillGeneratedAt"" TIMESTAMP WITH TIME ZONE;
            ALTER TABLE ""BranchLanguages"" ADD COLUMN IF NOT EXISTS ""SkillMarkdown"" TEXT;
            ALTER TABLE ""ModelConfigs"" ADD COLUMN IF NOT EXISTS ""AiProviderId"" TEXT;
            ALTER TABLE ""ChatApps"" ADD COLUMN IF NOT EXISTS ""AiProviderId"" TEXT;
            ALTER TABLE ""AiModelConfigs"" ADD COLUMN IF NOT EXISTS ""ProviderType"" TEXT;
            ALTER TABLE ""AiModelConfigs"" ADD COLUMN IF NOT EXISTS ""CacheHitTokenPrice"" NUMERIC(18, 8);
            ALTER TABLE ""AiModelConfigs"" ADD COLUMN IF NOT EXISTS ""CacheCreationTokenPrice"" NUMERIC(18, 8);
            ALTER TABLE ""TokenUsages"" ADD COLUMN IF NOT EXISTS ""ProviderId"" TEXT;
            ALTER TABLE ""TokenUsages"" ADD COLUMN IF NOT EXISTS ""ProviderName"" TEXT;
            ALTER TABLE ""TokenUsages"" ADD COLUMN IF NOT EXISTS ""ProviderType"" TEXT;
            ALTER TABLE ""TokenUsages"" ADD COLUMN IF NOT EXISTS ""ModelId"" TEXT;
            ALTER TABLE ""TokenUsages"" ADD COLUMN IF NOT EXISTS ""CachedInputTokens"" INTEGER NOT NULL DEFAULT 0;
            ALTER TABLE ""TokenUsages"" ADD COLUMN IF NOT EXISTS ""CacheCreationInputTokens"" INTEGER NOT NULL DEFAULT 0;
            ALTER TABLE ""TokenUsages"" ADD COLUMN IF NOT EXISTS ""InputTokenPrice"" NUMERIC(18, 8);
            ALTER TABLE ""TokenUsages"" ADD COLUMN IF NOT EXISTS ""OutputTokenPrice"" NUMERIC(18, 8);
            ALTER TABLE ""TokenUsages"" ADD COLUMN IF NOT EXISTS ""CacheHitTokenPrice"" NUMERIC(18, 8);
            ALTER TABLE ""TokenUsages"" ADD COLUMN IF NOT EXISTS ""CacheCreationTokenPrice"" NUMERIC(18, 8);
            ALTER TABLE ""TokenUsages"" ADD COLUMN IF NOT EXISTS ""InputCost"" NUMERIC(18, 8) NOT NULL DEFAULT 0;
            ALTER TABLE ""TokenUsages"" ADD COLUMN IF NOT EXISTS ""OutputCost"" NUMERIC(18, 8) NOT NULL DEFAULT 0;
            ALTER TABLE ""TokenUsages"" ADD COLUMN IF NOT EXISTS ""TotalCost"" NUMERIC(18, 8) NOT NULL DEFAULT 0;
            ALTER TABLE ""RepositoryBranches"" ADD COLUMN IF NOT EXISTS ""GenerationStatus"" INTEGER;
            ALTER TABLE ""RepositoryBranches"" ADD COLUMN IF NOT EXISTS ""LastGenerationTaskId"" TEXT;
            ALTER TABLE ""RepositoryBranches"" ADD COLUMN IF NOT EXISTS ""LastGenerationError"" TEXT;
            ALTER TABLE ""RepositoryBranches"" ADD COLUMN IF NOT EXISTS ""LastGenerationStartedAt"" TIMESTAMP WITH TIME ZONE;
            ALTER TABLE ""RepositoryBranches"" ADD COLUMN IF NOT EXISTS ""LastGenerationCompletedAt"" TIMESTAMP WITH TIME ZONE;
            ALTER TABLE ""RepositoryProcessingLogs"" ADD COLUMN IF NOT EXISTS ""BranchId"" TEXT;
            ALTER TABLE ""RepositoryProcessingLogs"" ADD COLUMN IF NOT EXISTS ""GenerationTaskId"" TEXT;
            ALTER TABLE ""RepositoryGenerationLocks"" ADD COLUMN IF NOT EXISTS ""InstanceId"" TEXT;
            ALTER TABLE ""RepositoryGenerationLocks"" ADD COLUMN IF NOT EXISTS ""HeartbeatAt"" TIMESTAMP WITH TIME ZONE;");
        await ctx.Database.ExecuteSqlRawAsync(@"
            CREATE INDEX IF NOT EXISTS ""IX_RepositoryProcessingLogs_RepositoryId_BranchId_GenerationTaskId_CreatedAt"" ON ""RepositoryProcessingLogs"" (""RepositoryId"", ""BranchId"", ""GenerationTaskId"", ""CreatedAt"")");
        await ctx.Database.ExecuteSqlRawAsync(@"
            CREATE INDEX IF NOT EXISTS ""IX_RepositoryProcessingLogs_BranchId"" ON ""RepositoryProcessingLogs"" (""BranchId"")");
        await ctx.Database.ExecuteSqlRawAsync(@"
            CREATE INDEX IF NOT EXISTS ""IX_RepositoryProcessingLogs_GenerationTaskId"" ON ""RepositoryProcessingLogs"" (""GenerationTaskId"")");
    }
}
