using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;
using OpenDeepWiki.EFCore;
using OpenDeepWiki.Entities;
using OpenDeepWiki.Models.Subscription;

namespace OpenDeepWiki.Services.Subscriptions;

/// <summary>
/// 订阅服务
/// 处理用户订阅仓库相关业务逻辑
/// </summary>
[MiniApi(Route = "/api/v1/subscriptions")]
[Tags("订阅")]
public class SubscriptionService(IContext context)
{
    /// <summary>
    /// 添加订阅
    /// 原子性增加仓库订阅计数
    /// </summary>
    [HttpPost("/")]
    public async Task<IResult> AddSubscriptionAsync([FromBody] AddSubscriptionRequest request)
    {
        try
        {
            var repository = await context.Repositories
                .AsNoTracking()
                .FirstOrDefaultAsync(r => r.Id == request.RepositoryId);

            if (repository is null)
            {
                return Results.NotFound(new SubscriptionResponse
                {
                    Success = false,
                    ErrorMessage = "仓库不存在"
                });
            }

            var user = await context.Users
                .AsNoTracking()
                .FirstOrDefaultAsync(u => u.Id == request.UserId);

            if (user is null)
            {
                return Results.NotFound(new SubscriptionResponse
                {
                    Success = false,
                    ErrorMessage = "用户不存在"
                });
            }


            var existingSubscription = await context.UserSubscriptions
                .AsNoTracking()
                .FirstOrDefaultAsync(s => s.UserId == request.UserId && s.RepositoryId == request.RepositoryId);

            if (existingSubscription is not null)
            {
                return Results.Conflict(new SubscriptionResponse
                {
                    Success = false,
                    ErrorMessage = "已订阅该仓库"
                });
            }

            var dbContext = context as DbContext;
            if (dbContext is null)
            {
                return Results.Json(new SubscriptionResponse
                {
                    Success = false,
                    ErrorMessage = "服务器内部错误"
                }, statusCode: StatusCodes.Status500InternalServerError);
            }

            await using var transaction = await dbContext.Database.BeginTransactionAsync();
            try
            {
                var subscription = new UserSubscription
                {
                    Id = Guid.NewGuid().ToString(),
                    UserId = request.UserId,
                    RepositoryId = request.RepositoryId
                };

                context.UserSubscriptions.Add(subscription);
                await context.SaveChangesAsync();

                await dbContext.Database.ExecuteSqlRawAsync(
                    "UPDATE Repositories SET SubscriptionCount = SubscriptionCount + 1 WHERE Id = {0}",
                    request.RepositoryId);

                await transaction.CommitAsync();

                return Results.Ok(new SubscriptionResponse
                {
                    Success = true,
                    SubscriptionId = subscription.Id
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
            return Results.Json(new SubscriptionResponse
            {
                Success = false,
                ErrorMessage = "服务器内部错误"
            }, statusCode: StatusCodes.Status500InternalServerError);
        }
    }


    /// <summary>
    /// 取消订阅
    /// 原子性减少仓库订阅计数
    /// </summary>
    [HttpDelete("{repositoryId}")]
    public async Task<IResult> RemoveSubscriptionAsync(string repositoryId, [FromQuery] string userId)
    {
        try
        {
            var subscription = await context.UserSubscriptions
                .FirstOrDefaultAsync(s => s.UserId == userId && s.RepositoryId == repositoryId);

            if (subscription is null)
            {
                return Results.NotFound(new SubscriptionResponse
                {
                    Success = false,
                    ErrorMessage = "订阅记录不存在"
                });
            }

            var dbContext = context as DbContext;
            if (dbContext is null)
            {
                return Results.Json(new SubscriptionResponse
                {
                    Success = false,
                    ErrorMessage = "服务器内部错误"
                }, statusCode: StatusCodes.Status500InternalServerError);
            }

            await using var transaction = await dbContext.Database.BeginTransactionAsync();
            try
            {
                context.UserSubscriptions.Remove(subscription);
                await context.SaveChangesAsync();

                await dbContext.Database.ExecuteSqlRawAsync(
                    "UPDATE Repositories SET SubscriptionCount = CASE WHEN SubscriptionCount > 0 THEN SubscriptionCount - 1 ELSE 0 END WHERE Id = {0}",
                    repositoryId);

                await transaction.CommitAsync();

                return Results.Ok(new SubscriptionResponse
                {
                    Success = true,
                    SubscriptionId = subscription.Id
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
            return Results.Json(new SubscriptionResponse
            {
                Success = false,
                ErrorMessage = "服务器内部错误"
            }, statusCode: StatusCodes.Status500InternalServerError);
        }
    }


    /// <summary>
    /// 检查订阅状态
    /// </summary>
    [HttpGet("{repositoryId}/status")]
    public async Task<SubscriptionStatusResponse> GetSubscriptionStatusAsync(
        string repositoryId,
        [FromQuery] string userId)
    {
        if (string.IsNullOrWhiteSpace(userId))
        {
            return new SubscriptionStatusResponse
            {
                IsSubscribed = false,
                SubscribedAt = null
            };
        }

        var subscription = await context.UserSubscriptions
            .AsNoTracking()
            .FirstOrDefaultAsync(s => s.UserId == userId && s.RepositoryId == repositoryId);

        return new SubscriptionStatusResponse
        {
            IsSubscribed = subscription is not null,
            SubscribedAt = subscription?.CreatedAt
        };
    }

    /// <summary>
    /// 获取用户订阅列表
    /// </summary>
    [HttpGet("/")]
    public async Task<SubscriptionListResponse> GetUserSubscriptionsAsync(
        [FromQuery] string userId,
        [FromQuery] int page = 1,
        [FromQuery] int pageSize = 20)
    {
        if (page < 1) page = 1;
        if (pageSize < 1) pageSize = 20;
        if (pageSize > 100) pageSize = 100;

        var total = await context.UserSubscriptions
            .AsNoTracking()
            .Where(s => s.UserId == userId)
            .CountAsync();

        var subscriptions = await context.UserSubscriptions
            .AsNoTracking()
            .Where(s => s.UserId == userId)
            .OrderByDescending(s => s.CreatedAt)
            .Skip((page - 1) * pageSize)
            .Take(pageSize)
            .Include(s => s.Repository)
            .ToListAsync();

        var items = subscriptions
            .Where(s => s.Repository is not null)
            .Select(s => new SubscriptionItemResponse
            {
                SubscriptionId = s.Id,
                RepositoryId = s.RepositoryId,
                RepoName = s.Repository!.RepoName,
                OrgName = s.Repository.OrgName,
                Description = null,
                StarCount = s.Repository.StarCount,
                ForkCount = s.Repository.ForkCount,
                SubscriptionCount = s.Repository.SubscriptionCount,
                SubscribedAt = s.CreatedAt
            })
            .ToList();

        return new SubscriptionListResponse
        {
            Items = items,
            Total = total,
            Page = page,
            PageSize = pageSize
        };
    }
}
