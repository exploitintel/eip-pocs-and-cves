using OpenDeepWiki.Services.Admin;

namespace OpenDeepWiki.Endpoints.Admin;

public static class AdminApiKeyEndpoints
{
    public static RouteGroupBuilder MapAdminApiKeyEndpoints(this RouteGroupBuilder group)
    {
        var apiKeys = group.MapGroup("/api-keys");

        apiKeys.MapPost("/", async (CreateApiKeyRequest request, IAdminApiKeyService service) =>
        {
            try
            {
                var result = await service.CreateApiKeyAsync(request.Name, request.UserId, request.Scope, request.ExpiresInDays);
                return Results.Ok(result);
            }
            catch (ArgumentException ex)
            {
                return Results.BadRequest(new { error = true, message = ex.Message });
            }
        });

        apiKeys.MapGet("/", async (IAdminApiKeyService service) =>
        {
            var keys = await service.ListApiKeysAsync();
            return Results.Ok(keys);
        });

        apiKeys.MapDelete("/{id}", async (string id, IAdminApiKeyService service) =>
        {
            var revoked = await service.RevokeApiKeyAsync(id);
            return revoked ? Results.Ok(new { message = "API key revoked" }) : Results.NotFound(new { error = true, message = "API key not found" });
        });

        return apiKeys;
    }
}

public class CreateApiKeyRequest
{
    public required string Name { get; set; }
    public required string UserId { get; set; }
    public string? Scope { get; set; }
    public int? ExpiresInDays { get; set; }
}
