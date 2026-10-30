using OpenDeepWiki.Services.Admin;
using OpenDeepWiki.Services.Auth;

namespace OpenDeepWiki.Endpoints;

public static class ApiKeyEndpoints
{
    public static IEndpointRouteBuilder MapApiKeyEndpoints(this IEndpointRouteBuilder app)
    {
        var group = app.MapGroup("/api/auth/api-keys")
            .WithTags("API Keys")
            .RequireAuthorization();

        // List own API keys
        group.MapGet("/", async (IUserContext userContext, IAdminApiKeyService service) =>
        {
            if (string.IsNullOrEmpty(userContext.UserId))
                return Results.Unauthorized();

            var keys = await service.ListApiKeysForUserAsync(userContext.UserId);
            return Results.Ok(keys);
        });

        // Create own API key
        group.MapPost("/", async (CreateUserApiKeyRequest request, IUserContext userContext, IAdminApiKeyService service) =>
        {
            if (string.IsNullOrEmpty(userContext.UserId))
                return Results.Unauthorized();

            try
            {
                var result = await service.CreateApiKeyForUserAsync(
                    userContext.UserId, request.Name, request.Scope, request.ExpiresInDays);
                return Results.Ok(result);
            }
            catch (ArgumentException ex)
            {
                return Results.BadRequest(new { error = true, message = ex.Message });
            }
        });

        // Revoke own API key
        group.MapDelete("/{id}", async (string id, IUserContext userContext, IAdminApiKeyService service) =>
        {
            if (string.IsNullOrEmpty(userContext.UserId))
                return Results.Unauthorized();

            var revoked = await service.RevokeApiKeyForUserAsync(userContext.UserId, id);
            return revoked
                ? Results.Ok(new { message = "API key revoked" })
                : Results.NotFound(new { error = true, message = "API key not found" });
        });

        return app;
    }
}

public class CreateUserApiKeyRequest
{
    public required string Name { get; set; }
    public string? Scope { get; set; }
    public int? ExpiresInDays { get; set; }
}
