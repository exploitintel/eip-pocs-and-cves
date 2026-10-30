using System.Security.Claims;
using System.Security.Cryptography;
using System.Text;
using System.Text.Encodings.Web;
using Microsoft.AspNetCore.Authentication;
using Microsoft.EntityFrameworkCore;
using Microsoft.Extensions.Options;
using OpenDeepWiki.EFCore;

namespace OpenDeepWiki.MCP;

public class ApiKeyAuthenticationHandler : AuthenticationHandler<AuthenticationSchemeOptions>
{
    private const string ApiKeyPrefix = "dwk_";
    private readonly IServiceScopeFactory _scopeFactory;

    public ApiKeyAuthenticationHandler(
        IOptionsMonitor<AuthenticationSchemeOptions> options,
        ILoggerFactory logger,
        UrlEncoder encoder,
        IServiceScopeFactory scopeFactory)
        : base(options, logger, encoder)
    {
        _scopeFactory = scopeFactory;
    }

    protected override async Task<AuthenticateResult> HandleAuthenticateAsync()
    {
        var authHeader = Request.Headers.Authorization.ToString();
        if (string.IsNullOrEmpty(authHeader) || !authHeader.StartsWith("Bearer ", StringComparison.OrdinalIgnoreCase))
            return AuthenticateResult.NoResult();

        var token = authHeader["Bearer ".Length..].Trim();
        if (!token.StartsWith(ApiKeyPrefix))
            return AuthenticateResult.NoResult();

        // Extract prefix and compute hash
        var randomPart = token[ApiKeyPrefix.Length..];
        if (randomPart.Length < 8)
            return AuthenticateResult.Fail("Invalid API key format");

        var keyPrefix = randomPart[..8];
        var tokenBytes = Encoding.UTF8.GetBytes(token);
        var hashBytes = SHA256.HashData(tokenBytes);
        var keyHash = Convert.ToHexString(hashBytes).ToLowerInvariant();

        // Look up in database using a new scope (handler is singleton-like)
        using var scope = _scopeFactory.CreateScope();
        var context = scope.ServiceProvider.GetRequiredService<IContext>();

        var apiKey = await context.ApiKeys
            .Include(k => k.User)
            .FirstOrDefaultAsync(k => k.KeyPrefix == keyPrefix && !k.IsDeleted);

        if (apiKey == null)
            return AuthenticateResult.Fail("Invalid API key");

        // Constant-time hash comparison
        var storedHashBytes = Convert.FromHexString(apiKey.KeyHash);
        if (!CryptographicOperations.FixedTimeEquals(hashBytes, storedHashBytes))
            return AuthenticateResult.Fail("Invalid API key");

        // Check expiration
        if (apiKey.ExpiresAt.HasValue && apiKey.ExpiresAt.Value < DateTime.UtcNow)
            return AuthenticateResult.Fail("API key has expired");

        // Check user exists and is not deleted
        if (apiKey.User == null || apiKey.User.IsDeleted)
            return AuthenticateResult.Fail("Associated user not found or disabled");

        // Load user roles
        var userRoles = await context.UserRoles
            .Where(ur => ur.UserId == apiKey.UserId)
            .Join(context.Roles.Where(r => !r.IsDeleted),
                  ur => ur.RoleId, r => r.Id,
                  (ur, r) => r.Name)
            .ToListAsync();

        // Build claims (same as JwtService)
        var claims = new List<Claim>
        {
            new(ClaimTypes.NameIdentifier, apiKey.UserId),
            new(ClaimTypes.Name, apiKey.User.Name ?? string.Empty),
            new(ClaimTypes.Email, apiKey.User.Email ?? string.Empty),
        };
        foreach (var role in userRoles)
        {
            claims.Add(new Claim(ClaimTypes.Role, role));
        }

        var identity = new ClaimsIdentity(claims, Scheme.Name);
        var principal = new ClaimsPrincipal(identity);
        var ticket = new AuthenticationTicket(principal, Scheme.Name);

        // Capture values before fire-and-forget (HttpContext may be recycled after response completes)
        var apiKeyId = apiKey.Id;
        var remoteIp = Request.HttpContext.Connection.RemoteIpAddress?.ToString();

        // Update last used info (fire and forget)
        _ = Task.Run(async () =>
        {
            try
            {
                using var updateScope = _scopeFactory.CreateScope();
                var updateContext = updateScope.ServiceProvider.GetRequiredService<IContext>();
                var keyToUpdate = await updateContext.ApiKeys.FindAsync(apiKeyId);
                if (keyToUpdate != null)
                {
                    keyToUpdate.LastUsedAt = DateTime.UtcNow;
                    keyToUpdate.LastUsedIp = remoteIp;
                    await updateContext.SaveChangesAsync();
                }
            }
            catch (Exception ex)
            {
                Logger.LogWarning(ex, "Failed to update API key last used info for prefix {KeyPrefix}", keyPrefix);
            }
        });

        Logger.LogInformation("API key authenticated: prefix={KeyPrefix}, user={Email}", keyPrefix, apiKey.User.Email);
        return AuthenticateResult.Success(ticket);
    }
}
