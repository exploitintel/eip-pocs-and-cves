using Microsoft.EntityFrameworkCore;
using OpenDeepWiki.EFCore;
using OpenDeepWiki.Entities;
using OpenDeepWiki.Models.Admin;

namespace OpenDeepWiki.Services.Admin;

/// <summary>
/// 管理端系统设置服务实现
/// </summary>
public class AdminSettingsService : IAdminSettingsService
{
    private readonly IContext _context;

    public AdminSettingsService(IContext context)
    {
        _context = context;
    }

    public async Task<List<SystemSettingDto>> GetSettingsAsync(string? category)
    {
        var query = _context.SystemSettings.Where(s => !s.IsDeleted);

        if (!string.IsNullOrWhiteSpace(category))
        {
            query = query.Where(s => s.Category == category);
        }

        return await query
            .OrderBy(s => s.Category)
            .ThenBy(s => s.Key)
            .Select(s => new SystemSettingDto
            {
                Id = s.Id,
                Key = s.Key,
                Value = s.Value,
                Description = s.Description,
                Category = s.Category
            })
            .ToListAsync();
    }

    public async Task<SystemSettingDto?> GetSettingByKeyAsync(string key)
    {
        var setting = await _context.SystemSettings
            .FirstOrDefaultAsync(s => s.Key == key && !s.IsDeleted);

        if (setting == null) return null;

        return new SystemSettingDto
        {
            Id = setting.Id,
            Key = setting.Key,
            Value = setting.Value,
            Description = setting.Description,
            Category = setting.Category
        };
    }

    public async Task UpdateSettingsAsync(List<UpdateSettingRequest> requests)
    {
        foreach (var request in requests)
        {
            var setting = await _context.SystemSettings
                .FirstOrDefaultAsync(s => s.Key == request.Key && !s.IsDeleted);

            if (setting != null)
            {
                setting.Value = request.Value;
                setting.UpdatedAt = DateTime.UtcNow;
            }
            else
            {
                _context.SystemSettings.Add(new SystemSetting
                {
                    Id = Guid.NewGuid().ToString(),
                    Key = request.Key,
                    Value = request.Value,
                    Category = "general",
                    CreatedAt = DateTime.UtcNow
                });
            }
        }

        await _context.SaveChangesAsync();
    }
}
