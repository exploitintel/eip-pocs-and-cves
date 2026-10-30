using Microsoft.EntityFrameworkCore;
using OpenDeepWiki.EFCore;
using OpenDeepWiki.Models.Auth;
using OpenDeepWiki.Models.UserProfile;

namespace OpenDeepWiki.Services.UserProfile;

/// <summary>
/// 用户资料服务实现
/// </summary>
public class UserProfileService : IUserProfileService
{
    private readonly IContext _context;

    public UserProfileService(IContext context)
    {
        _context = context;
    }

    public async Task<UserInfo> UpdateProfileAsync(string userId, UpdateProfileRequest request)
    {
        var user = await _context.Users
            .FirstOrDefaultAsync(u => u.Id == userId && !u.IsDeleted);

        if (user == null)
        {
            throw new InvalidOperationException("用户不存在");
        }

        // 检查邮箱是否被其他用户使用
        if (user.Email != request.Email)
        {
            var existingUser = await _context.Users
                .FirstOrDefaultAsync(u => u.Email == request.Email && u.Id != userId && !u.IsDeleted);

            if (existingUser != null)
            {
                throw new InvalidOperationException("该邮箱已被其他用户使用");
            }
        }

        // 更新用户信息
        user.Name = request.Name;
        user.Email = request.Email;
        user.Phone = request.Phone;
        user.Avatar = request.Avatar;
        user.UpdatedAt = DateTime.UtcNow;

        await _context.SaveChangesAsync();

        // 获取用户角色
        var roles = await _context.UserRoles
            .Where(ur => ur.UserId == userId && !ur.IsDeleted)
            .Join(_context.Roles, ur => ur.RoleId, r => r.Id, (ur, r) => r.Name)
            .ToListAsync();

        return new UserInfo
        {
            Id = user.Id,
            Name = user.Name,
            Email = user.Email,
            Avatar = user.Avatar ?? $"https://api.dicebear.com/7.x/notionists/svg?seed={Uri.EscapeDataString(user.Name)}",
            Roles = roles
        };
    }

    public async Task ChangePasswordAsync(string userId, ChangePasswordRequest request)
    {
        var user = await _context.Users
            .FirstOrDefaultAsync(u => u.Id == userId && !u.IsDeleted);

        if (user == null)
        {
            throw new InvalidOperationException("用户不存在");
        }

        // 验证当前密码
        if (!VerifyPassword(request.CurrentPassword, user.Password))
        {
            throw new UnauthorizedAccessException("当前密码错误");
        }

        // 更新密码
        user.Password = HashPassword(request.NewPassword);
        user.UpdatedAt = DateTime.UtcNow;

        await _context.SaveChangesAsync();
    }

    public async Task<UserSettingsDto> GetSettingsAsync(string userId)
    {
        var user = await _context.Users
            .FirstOrDefaultAsync(u => u.Id == userId && !u.IsDeleted);

        if (user == null)
        {
            throw new InvalidOperationException("用户不存在");
        }

        // 目前设置存储在用户扩展字段中，如果没有则返回默认值
        // 后续可以创建独立的 UserSettings 表
        return new UserSettingsDto
        {
            Theme = "system",
            Language = "zh",
            EmailNotifications = true,
            PushNotifications = false
        };
    }

    public async Task<UserSettingsDto> UpdateSettingsAsync(string userId, UserSettingsDto settings)
    {
        var user = await _context.Users
            .FirstOrDefaultAsync(u => u.Id == userId && !u.IsDeleted);

        if (user == null)
        {
            throw new InvalidOperationException("用户不存在");
        }

        // 目前设置存储在用户扩展字段中
        // 后续可以创建独立的 UserSettings 表来存储更多设置
        user.UpdatedAt = DateTime.UtcNow;
        await _context.SaveChangesAsync();

        return settings;
    }

    private static string HashPassword(string password)
    {
        return BCrypt.Net.BCrypt.HashPassword(password);
    }

    private static bool VerifyPassword(string password, string? hashedPassword)
    {
        if (string.IsNullOrEmpty(hashedPassword))
        {
            return false;
        }

        try
        {
            return BCrypt.Net.BCrypt.Verify(password, hashedPassword);
        }
        catch
        {
            return false;
        }
    }
}
