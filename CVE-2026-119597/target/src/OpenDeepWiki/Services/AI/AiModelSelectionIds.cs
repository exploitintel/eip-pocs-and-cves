namespace OpenDeepWiki.Services.AI;

public static class AiModelSelectionIds
{
    private const string Prefix = "ai-model:";

    public static string Create(string providerId, string modelId)
    {
        return $"{Prefix}{providerId}:{modelId}";
    }

    public static bool TryParse(string? selectionId, out string providerId, out string modelId)
    {
        providerId = string.Empty;
        modelId = string.Empty;

        if (string.IsNullOrWhiteSpace(selectionId) ||
            !selectionId.StartsWith(Prefix, StringComparison.Ordinal))
        {
            return false;
        }

        var payload = selectionId[Prefix.Length..];
        var separatorIndex = payload.IndexOf(':');
        if (separatorIndex <= 0 || separatorIndex >= payload.Length - 1)
        {
            return false;
        }

        providerId = payload[..separatorIndex];
        modelId = payload[(separatorIndex + 1)..];
        return true;
    }
}
