using System.Net.Http.Headers;
using System.Text.Json;
using Microsoft.EntityFrameworkCore;
using Microsoft.Extensions.Options;
using OpenDeepWiki.EFCore;
using OpenDeepWiki.Entities;
using OpenDeepWiki.Models.Auth;
using OpenDeepWiki.Services.Auth;

namespace OpenDeepWiki.Services.OAuth;

/// <summary>
/// OAuth服务实现
/// </summary>
public class OAuthService : IOAuthService
{
    private readonly IContext _context;
    private readonly IJwtService _jwtService;
    private readonly JwtOptions _jwtOptions;
    private readonly IHttpClientFactory _httpClientFactory;

    public OAuthService(
        IContext context,
        IJwtService jwtService,
        IOptions<JwtOptions> jwtOptions,
        IHttpClientFactory httpClientFactory)
    {
        _context = context;
        _jwtService = jwtService;
        _jwtOptions = jwtOptions.Value;
        _httpClientFactory = httpClientFactory;
    }

    public async Task<string> GetAuthorizationUrlAsync(string providerName, string? state = null)
    {
        var provider = await GetProviderAsync(providerName);

        state ??= Guid.NewGuid().ToString();

        var queryParams = new Dictionary<string, string>
        {
            ["client_id"] = provider.ClientId,
            ["redirect_uri"] = provider.RedirectUri,
            ["response_type"] = "code",
            ["state"] = state
        };

        if (!string.IsNullOrEmpty(provider.Scope))
        {
            queryParams["scope"] = provider.Scope;
        }

        var queryString = string.Join("&", queryParams.Select(kvp => $"{kvp.Key}={Uri.EscapeDataString(kvp.Value)}"));
        return $"{provider.AuthorizationUrl}?{queryString}";
    }

    public async Task<LoginResponse> HandleCallbackAsync(string providerName, string code, string? state = null)
    {
        var provider = await GetProviderAsync(providerName);

        // 1. 交换授权码获取访问令牌
        var tokenResponse = await ExchangeCodeForTokenAsync(provider, code);

        // 2. 使用访问令牌获取用户信息
        var userInfo = await GetOAuthUserInfoAsync(provider, tokenResponse.AccessToken);

        // 3. 查找或创建用户
        var user = await FindOrCreateUserAsync(provider, userInfo, tokenResponse);

        // 4. 生成JWT令牌
        var roles = await GetUserRolesAsync(user.Id);
        var token = _jwtService.GenerateToken(user, roles);

        return new LoginResponse
        {
            AccessToken = token,
            ExpiresIn = _jwtOptions.ExpirationMinutes * 60,
            User = new UserInfo
            {
                Id = user.Id,
                Name = user.Name,
                Email = user.Email,
                Avatar = user.Avatar,
                Roles = roles
            }
        };
    }

    private async Task<OAuthProvider> GetProviderAsync(string providerName)
    {
        var provider = await _context.OAuthProviders
            .FirstOrDefaultAsync(p => p.Name.ToLower() == providerName.ToLower() && p.IsActive && !p.IsDeleted);

        if (provider == null)
        {
            throw new InvalidOperationException($"OAuth提供商 '{providerName}' 不存在或未启用");
        }

        return provider;
    }

    private async Task<OAuthTokenResponse> ExchangeCodeForTokenAsync(OAuthProvider provider, string code)
    {
        var client = _httpClientFactory.CreateClient();

        var requestData = new Dictionary<string, string>
        {
            ["client_id"] = provider.ClientId,
            ["client_secret"] = provider.ClientSecret,
            ["code"] = code,
            ["redirect_uri"] = provider.RedirectUri,
            ["grant_type"] = "authorization_code"
        };

        var response = await client.PostAsync(provider.TokenUrl, new FormUrlEncodedContent(requestData));
        response.EnsureSuccessStatusCode();

        var content = await response.Content.ReadAsStringAsync();
        var tokenData = JsonSerializer.Deserialize<JsonElement>(content);

        return new OAuthTokenResponse
        {
            AccessToken = tokenData.GetProperty("access_token").GetString() ?? string.Empty,
            TokenType = tokenData.TryGetProperty("token_type", out var tokenType) ? tokenType.GetString() : "Bearer",
            ExpiresIn = tokenData.TryGetProperty("expires_in", out var expiresIn) ? expiresIn.GetInt32() : 0,
            RefreshToken = tokenData.TryGetProperty("refresh_token", out var refreshToken) ? refreshToken.GetString() : null,
            Scope = tokenData.TryGetProperty("scope", out var scope) ? scope.GetString() : null
        };
    }

    private async Task<OAuthUserInfo> GetOAuthUserInfoAsync(OAuthProvider provider, string accessToken)
    {
        var client = _httpClientFactory.CreateClient();
        client.DefaultRequestHeaders.Authorization = new AuthenticationHeaderValue("Bearer", accessToken);

        var response = await client.GetAsync(provider.UserInfoUrl);
        response.EnsureSuccessStatusCode();

        var content = await response.Content.ReadAsStringAsync();
        var userData = JsonSerializer.Deserialize<JsonElement>(content);

        // 解析用户信息映射
        var mapping = string.IsNullOrEmpty(provider.UserInfoMapping)
            ? GetDefaultMapping(provider.Name)
            : JsonSerializer.Deserialize<Dictionary<string, string>>(provider.UserInfoMapping) ?? GetDefaultMapping(provider.Name);

        return new OAuthUserInfo
        {
            Id = GetJsonValue(userData, mapping.GetValueOrDefault("id", "id")),
            Name = GetJsonValue(userData, mapping.GetValueOrDefault("name", "name")),
            Email = GetJsonValue(userData, mapping.GetValueOrDefault("email", "email")),
            Avatar = GetJsonValue(userData, mapping.GetValueOrDefault("avatar", "avatar_url"))
        };
    }

    private async Task<User> FindOrCreateUserAsync(OAuthProvider provider, OAuthUserInfo oauthUserInfo, OAuthTokenResponse tokenResponse)
    {
        // 查找现有的OAuth绑定
        var userOAuth = await _context.UserOAuths
            .Include(uo => uo.User)
            .FirstOrDefaultAsync(uo =>
                uo.OAuthProviderId == provider.Id &&
                uo.OAuthUserId == oauthUserInfo.Id &&
                !uo.IsDeleted);

        User user;

        if (userOAuth != null && userOAuth.User != null)
        {
            // 更新现有绑定
            user = userOAuth.User;
            userOAuth.AccessToken = tokenResponse.AccessToken;
            userOAuth.RefreshToken = tokenResponse.RefreshToken;
            userOAuth.TokenExpiresAt = DateTime.UtcNow.AddSeconds(tokenResponse.ExpiresIn);
            userOAuth.LastLoginAt = DateTime.UtcNow;
            userOAuth.UpdateTimestamp();
        }
        else
        {
            // 尝试通过邮箱查找现有用户
            user = await _context.Users
                .FirstOrDefaultAsync(u => u.Email == oauthUserInfo.Email && !u.IsDeleted);

            if (user == null)
            {
                // 创建新用户
                user = new User
                {
                    Id = Guid.NewGuid().ToString(),
                    Name = oauthUserInfo.Name,
                    Email = oauthUserInfo.Email,
                    Avatar = oauthUserInfo.Avatar,
                    Status = 1,
                    CreatedAt = DateTime.UtcNow
                };
                _context.Users.Add(user);

                // 分配默认角色
                var defaultRole = await _context.Roles
                    .FirstOrDefaultAsync(r => r.Name == "User" && !r.IsDeleted);

                if (defaultRole != null)
                {
                    var userRole = new UserRole
                    {
                        Id = Guid.NewGuid().ToString(),
                        UserId = user.Id,
                        RoleId = defaultRole.Id,
                        CreatedAt = DateTime.UtcNow
                    };
                    _context.UserRoles.Add(userRole);
                }
            }

            // 创建OAuth绑定
            userOAuth = new UserOAuth
            {
                Id = Guid.NewGuid().ToString(),
                UserId = user.Id,
                OAuthProviderId = provider.Id,
                OAuthUserId = oauthUserInfo.Id,
                OAuthUserName = oauthUserInfo.Name,
                OAuthUserEmail = oauthUserInfo.Email,
                OAuthUserAvatar = oauthUserInfo.Avatar,
                AccessToken = tokenResponse.AccessToken,
                RefreshToken = tokenResponse.RefreshToken,
                TokenExpiresAt = DateTime.UtcNow.AddSeconds(tokenResponse.ExpiresIn),
                Scope = tokenResponse.Scope,
                TokenType = tokenResponse.TokenType,
                IsBound = true,
                LastLoginAt = DateTime.UtcNow,
                CreatedAt = DateTime.UtcNow
            };
            _context.UserOAuths.Add(userOAuth);
        }

        // 更新用户最后登录时间
        user.LastLoginAt = DateTime.UtcNow;
        user.UpdateTimestamp();

        await _context.SaveChangesAsync();

        return user;
    }

    private async Task<List<string>> GetUserRolesAsync(string userId)
    {
        return await _context.UserRoles
            .Where(ur => ur.UserId == userId && !ur.IsDeleted)
            .Join(_context.Roles, ur => ur.RoleId, r => r.Id, (ur, r) => r.Name)
            .ToListAsync();
    }

    private static string GetJsonValue(JsonElement element, string path)
    {
        var parts = path.Split('.');
        var current = element;

        foreach (var part in parts)
        {
            if (current.TryGetProperty(part, out var property))
            {
                current = property;
            }
            else
            {
                return string.Empty;
            }
        }

        return current.ValueKind == JsonValueKind.String ? current.GetString() ?? string.Empty : string.Empty;
    }

    private static Dictionary<string, string> GetDefaultMapping(string providerName)
    {
        return providerName.ToLower() switch
        {
            "github" => new Dictionary<string, string>
            {
                ["id"] = "id",
                ["name"] = "login",
                ["email"] = "email",
                ["avatar"] = "avatar_url"
            },
            "gitee" => new Dictionary<string, string>
            {
                ["id"] = "id",
                ["name"] = "name",
                ["email"] = "email",
                ["avatar"] = "avatar_url"
            },
            _ => new Dictionary<string, string>
            {
                ["id"] = "id",
                ["name"] = "name",
                ["email"] = "email",
                ["avatar"] = "avatar_url"
            }
        };
    }
}

internal class OAuthTokenResponse
{
    public string AccessToken { get; set; } = string.Empty;
    public string? TokenType { get; set; }
    public int ExpiresIn { get; set; }
    public string? RefreshToken { get; set; }
    public string? Scope { get; set; }
}

internal class OAuthUserInfo
{
    public string Id { get; set; } = string.Empty;
    public string Name { get; set; } = string.Empty;
    public string Email { get; set; } = string.Empty;
    public string? Avatar { get; set; }
}
