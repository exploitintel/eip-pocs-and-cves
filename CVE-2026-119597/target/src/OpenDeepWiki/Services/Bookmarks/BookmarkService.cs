using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;
using OpenDeepWiki.EFCore;
using OpenDeepWiki.Entities;
using OpenDeepWiki.Models.Bookmark;

namespace OpenDeepWiki.Services.Bookmarks;

/// <summary>
/// 收藏服务
/// 处理用户收藏仓库相关业务逻辑
/// </summary>
[MiniApi(Route = "/api/v1/bookmarks")]
[Tags("收藏")]
public class BookmarkService(IContext context)
{
    /// <summary>
    /// 添加收藏
    /// 原子性增加仓库收藏计数
    /// </summary>
    /// <param name="request">添加收藏请求</param>
    /// <returns>收藏操作响应</returns>
    [HttpPost("/")]
    public async Task<IResult> AddBookmarkAsync([FromBody] AddBookmarkRequest request)
    {
        try
        {
            // 验证仓库是否存在
            var repository = await context.Repositories
                .AsNoTracking()
                .FirstOrDefaultAsync(r => r.Id == request.RepositoryId);

            if (repository is null)
            {
                return Results.NotFound(new BookmarkResponse
                {
                    Success = false,
                    ErrorMessage = "仓库不存在"
                });
            }

            // 验证用户是否存在
            var user = await context.Users
                .AsNoTracking()
                .FirstOrDefaultAsync(u => u.Id == request.UserId);

            if (user is null)
            {
                return Results.NotFound(new BookmarkResponse
                {
                    Success = false,
                    ErrorMessage = "用户不存在"
                });
            }

            // 检查是否已收藏
            var existingBookmark = await context.UserBookmarks
                .AsNoTracking()
                .FirstOrDefaultAsync(b => b.UserId == request.UserId && b.RepositoryId == request.RepositoryId);

            if (existingBookmark is not null)
            {
                return Results.Conflict(new BookmarkResponse
                {
                    Success = false,
                    ErrorMessage = "已收藏该仓库"
                });
            }

            // 使用事务确保原子性
            var dbContext = context as DbContext;
            if (dbContext is null)
            {
                return Results.Json(new BookmarkResponse
                {
                    Success = false,
                    ErrorMessage = "服务器内部错误"
                }, statusCode: StatusCodes.Status500InternalServerError);
            }

            await using var transaction = await dbContext.Database.BeginTransactionAsync();
            try
            {
                // 创建收藏记录
                var bookmark = new UserBookmark
                {
                    Id = Guid.NewGuid().ToString(),
                    UserId = request.UserId,
                    RepositoryId = request.RepositoryId
                };

                context.UserBookmarks.Add(bookmark);
                await context.SaveChangesAsync();

                // 原子性增加收藏计数
                await dbContext.Database.ExecuteSqlRawAsync(
                    "UPDATE Repositories SET BookmarkCount = BookmarkCount + 1 WHERE Id = {0}",
                    request.RepositoryId);

                await transaction.CommitAsync();

                return Results.Ok(new BookmarkResponse
                {
                    Success = true,
                    BookmarkId = bookmark.Id
                });
            }
            catch
            {
                await transaction.RollbackAsync();
                throw;
            }
        }
        catch (Exception)
        {
            return Results.Json(new BookmarkResponse
            {
                Success = false,
                ErrorMessage = "服务器内部错误"
            }, statusCode: StatusCodes.Status500InternalServerError);
        }
    }

    /// <summary>
    /// 取消收藏
    /// 原子性减少仓库收藏计数
    /// </summary>
    /// <param name="repositoryId">仓库ID</param>
    /// <param name="userId">用户ID</param>
    /// <returns>收藏操作响应</returns>
    [HttpDelete("{repositoryId}")]
    public async Task<IResult> RemoveBookmarkAsync(string repositoryId, [FromQuery] string userId)
    {
        try
        {
            // 查找收藏记录
            var bookmark = await context.UserBookmarks
                .FirstOrDefaultAsync(b => b.UserId == userId && b.RepositoryId == repositoryId);

            if (bookmark is null)
            {
                return Results.NotFound(new BookmarkResponse
                {
                    Success = false,
                    ErrorMessage = "收藏记录不存在"
                });
            }

            // 使用事务确保原子性
            var dbContext = context as DbContext;
            if (dbContext is null)
            {
                return Results.Json(new BookmarkResponse
                {
                    Success = false,
                    ErrorMessage = "服务器内部错误"
                }, statusCode: StatusCodes.Status500InternalServerError);
            }

            await using var transaction = await dbContext.Database.BeginTransactionAsync();
            try
            {
                // 删除收藏记录
                context.UserBookmarks.Remove(bookmark);
                await context.SaveChangesAsync();

                // 原子性减少收藏计数（确保不会变为负数）
                await dbContext.Database.ExecuteSqlRawAsync(
                    "UPDATE Repositories SET BookmarkCount = CASE WHEN BookmarkCount > 0 THEN BookmarkCount - 1 ELSE 0 END WHERE Id = {0}",
                    repositoryId);

                await transaction.CommitAsync();

                return Results.Ok(new BookmarkResponse
                {
                    Success = true,
                    BookmarkId = bookmark.Id
                });
            }
            catch
            {
                await transaction.RollbackAsync();
                throw;
            }
        }
        catch (Exception)
        {
            return Results.Json(new BookmarkResponse
            {
                Success = false,
                ErrorMessage = "服务器内部错误"
            }, statusCode: StatusCodes.Status500InternalServerError);
        }
    }

    /// <summary>
    /// 获取用户收藏列表
    /// 支持分页查询，按收藏时间降序排列
    /// </summary>
    /// <param name="userId">用户ID</param>
    /// <param name="page">页码（从1开始）</param>
    /// <param name="pageSize">每页大小</param>
    /// <returns>收藏列表响应</returns>
    [HttpGet("/")]
    public async Task<BookmarkListResponse> GetUserBookmarksAsync(
        [FromQuery] string userId,
        [FromQuery] int page = 1,
        [FromQuery] int pageSize = 20)
    {
        // 参数验证
        if (page < 1) page = 1;
        if (pageSize < 1) pageSize = 20;
        if (pageSize > 100) pageSize = 100;

        // 查询用户收藏总数
        var total = await context.UserBookmarks
            .AsNoTracking()
            .Where(b => b.UserId == userId)
            .CountAsync();

        // 分页查询收藏列表，按创建时间降序排列
        var bookmarks = await context.UserBookmarks
            .AsNoTracking()
            .Where(b => b.UserId == userId)
            .OrderByDescending(b => b.CreatedAt)
            .Skip((page - 1) * pageSize)
            .Take(pageSize)
            .Include(b => b.Repository)
            .ToListAsync();

        // 转换为响应模型
        var items = bookmarks
            .Where(b => b.Repository is not null)
            .Select(b => new BookmarkItemResponse
            {
                BookmarkId = b.Id,
                RepositoryId = b.RepositoryId,
                RepoName = b.Repository!.RepoName,
                OrgName = b.Repository.OrgName,
                Description = null, // Repository 实体暂无 Description 字段
                StarCount = b.Repository.StarCount,
                ForkCount = b.Repository.ForkCount,
                BookmarkCount = b.Repository.BookmarkCount,
                BookmarkedAt = b.CreatedAt
            })
            .ToList();

        return new BookmarkListResponse
        {
            Items = items,
            Total = total,
            Page = page,
            PageSize = pageSize
        };
    }

    /// <summary>
    /// 检查收藏状态
    /// 查询用户是否已收藏指定仓库
    /// </summary>
    /// <param name="repositoryId">仓库ID</param>
    /// <param name="userId">用户ID</param>
    /// <returns>收藏状态响应</returns>
    [HttpGet("{repositoryId}/status")]
    public async Task<BookmarkStatusResponse> GetBookmarkStatusAsync(
        string repositoryId,
        [FromQuery] string userId)
    {
        // 如果用户ID为空，返回未收藏状态
        if (string.IsNullOrWhiteSpace(userId))
        {
            return new BookmarkStatusResponse
            {
                IsBookmarked = false,
                BookmarkedAt = null
            };
        }

        // 查询收藏记录
        var bookmark = await context.UserBookmarks
            .AsNoTracking()
            .FirstOrDefaultAsync(b => b.UserId == userId && b.RepositoryId == repositoryId);

        return new BookmarkStatusResponse
        {
            IsBookmarked = bookmark is not null,
            BookmarkedAt = bookmark?.CreatedAt
        };
    }
}
