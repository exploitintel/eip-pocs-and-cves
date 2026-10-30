namespace OpenDeepWiki.Services.Admin;

public interface IAdminApiKeyService
{
    Task<ApiKeyCreateResult> CreateApiKeyAsync(string name, string userId, string? scope = null, int? expiresInDays = null);
    Task<List<ApiKeyListItem>> ListApiKeysAsync();
    Task<bool> RevokeApiKeyAsync(string id);

    // User-scoped methods (users manage their own keys)
    Task<ApiKeyCreateResult> CreateApiKeyForUserAsync(string userId, string name, string? scope = null, int? expiresInDays = null);
    Task<List<ApiKeyListItem>> ListApiKeysForUserAsync(string userId);
    Task<bool> RevokeApiKeyForUserAsync(string userId, string id);
}

public class ApiKeyCreateResult
{
    public required string Id { get; set; }
    public required string Name { get; set; }
    public required string KeyPrefix { get; set; }
    public required string Scope { get; set; }
    public DateTime? ExpiresAt { get; set; }
    public required string PlainTextKey { get; set; }
}

public class ApiKeyListItem
{
    public required string Id { get; set; }
    public required string Name { get; set; }
    public required string KeyPrefix { get; set; }
    public required string UserId { get; set; }
    public string? UserEmail { get; set; }
    public required string Scope { get; set; }
    public DateTime? ExpiresAt { get; set; }
    public DateTime? LastUsedAt { get; set; }
    public DateTime CreatedAt { get; set; }
}
