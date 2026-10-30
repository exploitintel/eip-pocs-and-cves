namespace OpenDeepWiki.Services.Repositories;

/// <summary>
/// Per-process identity used to bind wiki generation locks and slots.
/// </summary>
public sealed class WikiGenerationInstanceIdentity
{
    public WikiGenerationInstanceIdentity(string? instanceId = null)
    {
        InstanceId = string.IsNullOrWhiteSpace(instanceId)
            ? $"{Environment.MachineName}-{Guid.NewGuid():N}"
            : instanceId;
    }

    public string InstanceId { get; }
}
