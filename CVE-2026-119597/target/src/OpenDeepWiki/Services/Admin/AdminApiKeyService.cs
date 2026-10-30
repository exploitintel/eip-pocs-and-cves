using System.Security.Cryptography;
using System.Text;
using Microsoft.EntityFrameworkCore;
using OpenDeepWiki.EFCore;
using OpenDeepWiki.Entities;

namespace OpenDeepWiki.Services.Admin;

public class AdminApiKeyService : IAdminApiKeyService
{
    private const string KeyPrefix = "dwk_";
    private readonly IContext _context;
    private readonly ILogger<AdminApiKeyService> _logger;

    public AdminApiKeyService(IContext context, ILogger<AdminApiKeyService> logger)
    {
        _context = context;
        _logger = logger;
    }

    public async Task<ApiKeyCreateResult> CreateApiKeyAsync(string name, string userId, string? scope = null, int? expiresInDays = null)
    {
        // Validate user exists
        var user = await _context.Users.FirstOrDefaultAsync(u => u.Id == userId && !u.IsDeleted);
        if (user == null)
            throw new ArgumentException($"User with ID '{userId}' not found");

        // Generate 32 bytes of cryptographic randomness, base64url encode
        var randomBytes = RandomNumberGenerator.GetBytes(32);
        var randomPart = Convert.ToBase64String(randomBytes)
            .Replace("+", "-").Replace("/", "_").TrimEnd('=');

        var fullToken = $"{KeyPrefix}{randomPart}";
        var prefix = randomPart[..8];

        // Compute SHA-256 hash
        var tokenBytes = Encoding.UTF8.GetBytes(fullToken);
        var hashBytes = SHA256.HashData(tokenBytes);
        var hashHex = Convert.ToHexString(hashBytes).ToLowerInvariant();

        var apiKey = new ApiKey
        {
            Id = Guid.NewGuid().ToString(),
            Name = name,
            KeyPrefix = prefix,
            KeyHash = hashHex,
            UserId = userId,
            Scope = scope ?? "mcp:read",
            ExpiresAt = expiresInDays.HasValue ? DateTime.UtcNow.AddDays(expiresInDays.Value) : null,
            CreatedAt = DateTime.UtcNow
        };

        _context.ApiKeys.Add(apiKey);
        await _context.SaveChangesAsync();

        _logger.LogInformation("API key created: id={Id}, name={Name}, prefix={Prefix}, userId={UserId}",
            apiKey.Id, apiKey.Name, prefix, userId);

        return new ApiKeyCreateResult
        {
            Id = apiKey.Id,
            Name = apiKey.Name,
            KeyPrefix = prefix,
            Scope = apiKey.Scope,
            ExpiresAt = apiKey.ExpiresAt,
            PlainTextKey = fullToken
        };
    }

    public async Task<List<ApiKeyListItem>> ListApiKeysAsync()
    {
        return await _context.ApiKeys
            .Where(k => !k.IsDeleted)
            .Include(k => k.User)
            .OrderByDescending(k => k.CreatedAt)
            .Select(k => new ApiKeyListItem
            {
                Id = k.Id,
                Name = k.Name,
                KeyPrefix = k.KeyPrefix,
                UserId = k.UserId,
                UserEmail = k.User != null ? k.User.Email : null,
                Scope = k.Scope,
                ExpiresAt = k.ExpiresAt,
                LastUsedAt = k.LastUsedAt,
                CreatedAt = k.CreatedAt
            })
            .ToListAsync();
    }

    public async Task<bool> RevokeApiKeyAsync(string id)
    {
        var apiKey = await _context.ApiKeys.FirstOrDefaultAsync(k => k.Id == id && !k.IsDeleted);
        if (apiKey == null)
            return false;

        apiKey.MarkAsDeleted();
        await _context.SaveChangesAsync();

        _logger.LogInformation("API key revoked: id={Id}, name={Name}, prefix={Prefix}", apiKey.Id, apiKey.Name, apiKey.KeyPrefix);
        return true;
    }

    public async Task<ApiKeyCreateResult> CreateApiKeyForUserAsync(string userId, string name, string? scope = null, int? expiresInDays = null)
    {
        // Reuse the existing creation logic (user is already authenticated, so they exist)
        return await CreateApiKeyAsync(name, userId, scope, expiresInDays);
    }

    public async Task<List<ApiKeyListItem>> ListApiKeysForUserAsync(string userId)
    {
        return await _context.ApiKeys
            .Where(k => !k.IsDeleted && k.UserId == userId)
            .Include(k => k.User)
            .OrderByDescending(k => k.CreatedAt)
            .Select(k => new ApiKeyListItem
            {
                Id = k.Id,
                Name = k.Name,
                KeyPrefix = k.KeyPrefix,
                UserId = k.UserId,
                UserEmail = k.User != null ? k.User.Email : null,
                Scope = k.Scope,
                ExpiresAt = k.ExpiresAt,
                LastUsedAt = k.LastUsedAt,
                CreatedAt = k.CreatedAt
            })
            .ToListAsync();
    }

    public async Task<bool> RevokeApiKeyForUserAsync(string userId, string id)
    {
        var apiKey = await _context.ApiKeys.FirstOrDefaultAsync(k => k.Id == id && k.UserId == userId && !k.IsDeleted);
        if (apiKey == null)
            return false;

        apiKey.MarkAsDeleted();
        await _context.SaveChangesAsync();

        _logger.LogInformation("API key revoked by user: id={Id}, name={Name}, prefix={Prefix}, userId={UserId}",
            apiKey.Id, apiKey.Name, apiKey.KeyPrefix, userId);
        return true;
    }
}
