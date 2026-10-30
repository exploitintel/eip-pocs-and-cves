using Microsoft.AspNetCore.Mvc;
using OpenDeepWiki.Models;
using OpenDeepWiki.Services.Repositories;

namespace OpenDeepWiki.Endpoints;

/// <summary>
/// Hand-written multipart endpoints for repository uploads.
/// The MiniApi source generator does not preserve the [FromForm] binding, so the generated
/// /submit-archive endpoint is registered as a JSON body and rejects multipart/form-data with 415.
/// This endpoint reads the multipart form explicitly and delegates to the existing service logic.
/// </summary>
public static class RepositoryUploadEndpoints
{
    public static IEndpointRouteBuilder MapRepositoryUploadEndpoints(this IEndpointRouteBuilder app)
    {
        app.MapPost("/api/v1/repositories/submit-archive", SubmitArchiveAsync)
            .WithTags("仓库")
            .WithName("SubmitArchiveRepository")
            .WithSummary("通过 ZIP 压缩包创建仓库")
            .DisableAntiforgery();

        return app;
    }

    private static async Task<IResult> SubmitArchiveAsync(
        HttpRequest httpRequest,
        [FromServices] RepositoryService repositoryService)
    {
        if (!httpRequest.HasFormContentType)
        {
            return Results.BadRequest(new { message = "请使用 multipart/form-data 格式上传" });
        }

        var form = await httpRequest.ReadFormAsync();
        var archive = form.Files["archive"];

        var branchName = form["branchName"].ToString();
        var request = new ArchiveRepositorySubmitRequest
        {
            OrgName = form["orgName"].ToString(),
            RepoName = form["repoName"].ToString(),
            BranchName = string.IsNullOrWhiteSpace(branchName) ? "main" : branchName,
            LanguageCode = form["languageCode"].ToString(),
            IsPublic = bool.TryParse(form["isPublic"], out var isPublic) && isPublic,
            GenerateSkill = bool.TryParse(form["generateSkill"], out var generateSkill) && generateSkill,
            Archive = archive,
        };

        var repository = await repositoryService.SubmitArchiveAsync(request);
        return Results.Ok(repository);
    }
}
