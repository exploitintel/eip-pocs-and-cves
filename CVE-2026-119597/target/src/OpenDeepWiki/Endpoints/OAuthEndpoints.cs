using Microsoft.AspNetCore.Mvc;
using OpenDeepWiki.Models.Auth;
using OpenDeepWiki.Services.OAuth;

namespace OpenDeepWiki.Endpoints;

/// <summary>
/// OAuth认证相关端点
/// </summary>
public static class OAuthEndpoints
{
    public static IEndpointRouteBuilder MapOAuthEndpoints(this IEndpointRouteBuilder app)
    {
        var group = app.MapGroup("/api/oauth")
            .WithTags("OAuth认证")
            .WithOpenApi();

        // 获取OAuth授权URL
        group.MapGet("/{provider}/authorize", async (
            [FromRoute] string provider,
            [FromQuery] string? state,
            [FromServices] IOAuthService oauthService) =>
        {
            try
            {
                var authUrl = await oauthService.GetAuthorizationUrlAsync(provider, state);
                return Results.Ok(new { success = true, data = new { authorizationUrl = authUrl } });
            }
            catch (InvalidOperationException ex)
            {
                return Results.BadRequest(new { success = false, message = ex.Message });
            }
            catch (Exception ex)
            {
                return Results.BadRequest(new { success = false, message = ex.Message });
            }
        })
        .WithName("GetOAuthAuthorizationUrl")
        .WithSummary("获取OAuth授权URL")
        .WithDescription("支持的提供商: github, gitee")
        .Produces<object>(200)
        .Produces(400);

        // OAuth回调处理
        group.MapGet("/{provider}/callback", async (
            [FromRoute] string provider,
            [FromQuery] string code,
            [FromQuery] string? state,
            [FromServices] IOAuthService oauthService) =>
        {
            try
            {
                var response = await oauthService.HandleCallbackAsync(provider, code, state);
                return Results.Ok(new { success = true, data = response });
            }
            catch (InvalidOperationException ex)
            {
                return Results.BadRequest(new { success = false, message = ex.Message });
            }
            catch (Exception ex)
            {
                return Results.BadRequest(new { success = false, message = ex.Message });
            }
        })
        .WithName("OAuthCallback")
        .WithSummary("OAuth回调处理")
        .WithDescription("OAuth提供商回调此端点以完成授权流程")
        .Produces<LoginResponse>(200)
        .Produces(400);

        // GitHub登录快捷方式
        group.MapGet("/github/login", async (
            [FromQuery] string? state,
            [FromServices] IOAuthService oauthService) =>
        {
            try
            {
                var authUrl = await oauthService.GetAuthorizationUrlAsync("github", state);
                return Results.Redirect(authUrl);
            }
            catch (Exception ex)
            {
                return Results.BadRequest(new { success = false, message = ex.Message });
            }
        })
        .WithName("GitHubLogin")
        .WithSummary("GitHub登录")
        .ExcludeFromDescription();

        // Gitee登录快捷方式
        group.MapGet("/gitee/login", async (
            [FromQuery] string? state,
            [FromServices] IOAuthService oauthService) =>
        {
            try
            {
                var authUrl = await oauthService.GetAuthorizationUrlAsync("gitee", state);
                return Results.Redirect(authUrl);
            }
            catch (Exception ex)
            {
                return Results.BadRequest(new { success = false, message = ex.Message });
            }
        })
        .WithName("GiteeLogin")
        .WithSummary("Gitee登录")
        .ExcludeFromDescription();

        return app;
    }
}
