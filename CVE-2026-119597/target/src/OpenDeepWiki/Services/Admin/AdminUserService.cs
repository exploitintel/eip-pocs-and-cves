using Microsoft.EntityFrameworkCore;
using OpenDeepWiki.EFCore;
using OpenDeepWiki.Entities;
using OpenDeepWiki.Models.Admin;

namespace OpenDeepWiki.Services.Admin;

/// <summary>
/// 管理端用户服务实现
/// </summary>
public class AdminUserService : IAdminUserService
{
    private readonly IContext _context;

    public AdminUserService(IContext context)
    {
        _context = context;
    }

    public async Task<AdminUserListResponse> GetUsersAsync(int page, int pageSize, string? search, string? roleId)
    {
        var query = _context.Users.Where(u => !u.IsDeleted);

        if (!string.IsNullOrWhiteSpace(search))
        {
            query = query.Where(u => u.Name.Contains(search) || u.Email.Contains(search));
        }

        if (!string.IsNullOrWhiteSpace(roleId))
        {
            var userIds = await _context.UserRoles
                .Where(ur => ur.RoleId == roleId && !ur.IsDeleted)
                .Select(ur => ur.UserId)
                .ToListAsync();
            query = query.Where(u => userIds.Contains(u.Id));
        }

        var total = await query.CountAsync();
        var users = await query
            .OrderByDescending(u => u.CreatedAt)
            .Skip((page - 1) * pageSize)
            .Take(pageSize)
            .ToListAsync();

        var userIdList = users.Select(u => u.Id).ToList();
        var userRoles = await _context.UserRoles
            .Where(ur => userIdList.Contains(ur.UserId) && !ur.IsDeleted)
            .Join(_context.Roles, ur => ur.RoleId, r => r.Id, (ur, r) => new { ur.UserId, RoleName = r.Name })
            .ToListAsync();

        var items = users.Select(u => new AdminUserDto
        {
            Id = u.Id,
            Name = u.Name,
            Email = u.Email,
            Avatar = u.Avatar,
            Phone = u.Phone,
            Status = u.Status,
            StatusText = GetStatusText(u.Status),
            IsSystem = u.IsSystem,
            LastLoginAt = u.LastLoginAt,
            LastLoginIp = u.LastLoginIp,
            Roles = userRoles.Where(r => r.UserId == u.Id).Select(r => r.RoleName).ToList(),
            CreatedAt = u.CreatedAt
        }).ToList();

        return new AdminUserListResponse
        {
            Items = items,
            Total = total,
            Page = page,
            PageSize = pageSize
        };
    }

    public async Task<AdminUserDto?> GetUserByIdAsync(string id)
    {
        var user = await _context.Users.FirstOrDefaultAsync(u => u.Id == id && !u.IsDeleted);
        if (user == null) return null;

        var roles = await _context.UserRoles
            .Where(ur => ur.UserId == id && !ur.IsDeleted)
            .Join(_context.Roles, ur => ur.RoleId, r => r.Id, (ur, r) => r.Name)
            .ToListAsync();

        return new AdminUserDto
        {
            Id = user.Id,
            Name = user.Name,
            Email = user.Email,
            Avatar = user.Avatar,
            Phone = user.Phone,
            Status = user.Status,
            StatusText = GetStatusText(user.Status),
            IsSystem = user.IsSystem,
            LastLoginAt = user.LastLoginAt,
            LastLoginIp = user.LastLoginIp,
            Roles = roles,
            CreatedAt = user.CreatedAt
        };
    }

    public async Task<AdminUserDto> CreateUserAsync(CreateUserRequest request)
    {
        var exists = await _context.Users.AnyAsync(u => u.Email == request.Email && !u.IsDeleted);
        if (exists) throw new InvalidOperationException("该邮箱已被注册");

        var user = new User
        {
            Id = Guid.NewGuid().ToString(),
            Name = request.Name,
            Email = request.Email,
            Password = BCrypt.Net.BCrypt.HashPassword(request.Password),
            Phone = request.Phone,
            Status = 1,
            CreatedAt = DateTime.UtcNow
        };

        _context.Users.Add(user);

        // 分配角色
        if (request.RoleIds?.Any() == true)
        {
            foreach (var roleId in request.RoleIds)
            {
                _context.UserRoles.Add(new UserRole
                {
                    Id = Guid.NewGuid().ToString(),
                    UserId = user.Id,
                    RoleId = roleId,
                    CreatedAt = DateTime.UtcNow
                });
            }
        }

        await _context.SaveChangesAsync();
        return (await GetUserByIdAsync(user.Id))!;
    }

    public async Task<bool> UpdateUserAsync(string id, UpdateUserRequest request)
    {
        var user = await _context.Users.FirstOrDefaultAsync(u => u.Id == id && !u.IsDeleted);
        if (user == null) return false;

        if (request.Name != null) user.Name = request.Name;
        if (request.Email != null) user.Email = request.Email;
        if (request.Phone != null) user.Phone = request.Phone;
        if (request.Avatar != null) user.Avatar = request.Avatar;

        user.UpdatedAt = DateTime.UtcNow;
        await _context.SaveChangesAsync();
        return true;
    }

    public async Task<bool> DeleteUserAsync(string id)
    {
        var user = await _context.Users.FirstOrDefaultAsync(u => u.Id == id && !u.IsDeleted);
        if (user == null) return false;
        if (user.IsSystem) throw new InvalidOperationException("系统用户不能删除");

        user.IsDeleted = true;
        user.UpdatedAt = DateTime.UtcNow;
        await _context.SaveChangesAsync();
        return true;
    }

    public async Task<bool> UpdateUserStatusAsync(string id, int status)
    {
        var user = await _context.Users.FirstOrDefaultAsync(u => u.Id == id && !u.IsDeleted);
        if (user == null) return false;

        user.Status = status;
        user.UpdatedAt = DateTime.UtcNow;
        await _context.SaveChangesAsync();
        return true;
    }

    public async Task<bool> UpdateUserRolesAsync(string id, List<string> roleIds)
    {
        var user = await _context.Users.FirstOrDefaultAsync(u => u.Id == id && !u.IsDeleted);
        if (user == null) return false;

        // 删除现有角色
        var existingRoles = await _context.UserRoles
            .Where(ur => ur.UserId == id && !ur.IsDeleted)
            .ToListAsync();
        foreach (var role in existingRoles)
        {
            role.IsDeleted = true;
        }

        // 添加新角色
        foreach (var roleId in roleIds)
        {
            _context.UserRoles.Add(new UserRole
            {
                Id = Guid.NewGuid().ToString(),
                UserId = id,
                RoleId = roleId,
                CreatedAt = DateTime.UtcNow
            });
        }

        await _context.SaveChangesAsync();
        return true;
    }

    public async Task<bool> ResetPasswordAsync(string id, string newPassword)
    {
        var user = await _context.Users.FirstOrDefaultAsync(u => u.Id == id && !u.IsDeleted);
        if (user == null) return false;

        user.Password = BCrypt.Net.BCrypt.HashPassword(newPassword);
        user.UpdatedAt = DateTime.UtcNow;
        await _context.SaveChangesAsync();
        return true;
    }

    private static string GetStatusText(int status) => status switch
    {
        0 => "禁用",
        1 => "正常",
        2 => "待验证",
        _ => "未知"
    };
}
