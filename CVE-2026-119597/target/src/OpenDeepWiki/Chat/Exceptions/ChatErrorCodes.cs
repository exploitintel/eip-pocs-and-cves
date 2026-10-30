namespace OpenDeepWiki.Chat.Exceptions;

/// <summary>
/// 对话助手错误码常量
/// 定义所有对话相关的错误码，用于前后端统一错误处理
/// Requirements: 11.1, 11.2, 11.3
/// </summary>
public static class ChatErrorCodes
{
    #region 功能状态错误 (1xxx)
    
    /// <summary>
    /// 对话助手功能未启用
    /// </summary>
    public const string FEATURE_DISABLED = "FEATURE_DISABLED";
    
    /// <summary>
    /// 功能配置缺失
    /// </summary>
    public const string CONFIG_MISSING = "CONFIG_MISSING";
    
    #endregion

    #region 模型相关错误 (2xxx)
    
    /// <summary>
    /// 模型不可用
    /// </summary>
    public const string MODEL_UNAVAILABLE = "MODEL_UNAVAILABLE";
    
    /// <summary>
    /// 模型配置无效
    /// </summary>
    public const string MODEL_CONFIG_INVALID = "MODEL_CONFIG_INVALID";
    
    /// <summary>
    /// 没有可用的模型
    /// </summary>
    public const string NO_AVAILABLE_MODELS = "NO_AVAILABLE_MODELS";
    
    #endregion

    #region 应用相关错误 (3xxx)
    
    /// <summary>
    /// 无效的AppId
    /// </summary>
    public const string INVALID_APP_ID = "INVALID_APP_ID";
    
    /// <summary>
    /// 应用未配置模型
    /// </summary>
    public const string APP_MODEL_NOT_CONFIGURED = "APP_MODEL_NOT_CONFIGURED";
    
    /// <summary>
    /// 应用已禁用
    /// </summary>
    public const string APP_DISABLED = "APP_DISABLED";
    
    #endregion

    #region 域名校验错误 (4xxx)
    
    /// <summary>
    /// 域名不在允许列表中
    /// </summary>
    public const string DOMAIN_NOT_ALLOWED = "DOMAIN_NOT_ALLOWED";
    
    /// <summary>
    /// 无法获取请求来源域名
    /// </summary>
    public const string DOMAIN_UNKNOWN = "DOMAIN_UNKNOWN";
    
    #endregion

    #region 限流错误 (5xxx)
    
    /// <summary>
    /// 请求频率超限
    /// </summary>
    public const string RATE_LIMIT_EXCEEDED = "RATE_LIMIT_EXCEEDED";
    
    #endregion

    #region 文档相关错误 (6xxx)
    
    /// <summary>
    /// 文档不存在
    /// </summary>
    public const string DOCUMENT_NOT_FOUND = "DOCUMENT_NOT_FOUND";
    
    /// <summary>
    /// 文档访问被拒绝
    /// </summary>
    public const string DOCUMENT_ACCESS_DENIED = "DOCUMENT_ACCESS_DENIED";
    
    /// <summary>
    /// 仓库不存在
    /// </summary>
    public const string REPOSITORY_NOT_FOUND = "REPOSITORY_NOT_FOUND";
    
    #endregion

    #region 工具调用错误 (7xxx)
    
    /// <summary>
    /// MCP调用失败
    /// </summary>
    public const string MCP_CALL_FAILED = "MCP_CALL_FAILED";
    
    /// <summary>
    /// 工具执行失败
    /// </summary>
    public const string TOOL_EXECUTION_FAILED = "TOOL_EXECUTION_FAILED";
    
    /// <summary>
    /// 工具不存在
    /// </summary>
    public const string TOOL_NOT_FOUND = "TOOL_NOT_FOUND";
    
    #endregion

    #region 连接和超时错误 (8xxx)
    
    /// <summary>
    /// 连接失败
    /// </summary>
    public const string CONNECTION_FAILED = "CONNECTION_FAILED";
    
    /// <summary>
    /// 请求超时
    /// </summary>
    public const string REQUEST_TIMEOUT = "REQUEST_TIMEOUT";
    
    /// <summary>
    /// SSE流中断
    /// </summary>
    public const string STREAM_INTERRUPTED = "STREAM_INTERRUPTED";
    
    #endregion

    #region 内部错误 (9xxx)
    
    /// <summary>
    /// 内部服务器错误
    /// </summary>
    public const string INTERNAL_ERROR = "INTERNAL_ERROR";
    
    /// <summary>
    /// 未知错误
    /// </summary>
    public const string UNKNOWN_ERROR = "UNKNOWN_ERROR";
    
    #endregion

    /// <summary>
    /// 获取错误码对应的默认消息
    /// </summary>
    /// <param name="errorCode">错误码</param>
    /// <returns>默认错误消息</returns>
    public static string GetDefaultMessage(string errorCode)
    {
        return errorCode switch
        {
            FEATURE_DISABLED => "对话助手功能未启用",
            CONFIG_MISSING => "功能配置缺失",
            MODEL_UNAVAILABLE => "模型不可用，请选择其他模型",
            MODEL_CONFIG_INVALID => "模型配置无效",
            NO_AVAILABLE_MODELS => "暂无可用模型，请联系管理员配置",
            INVALID_APP_ID => "无效的应用ID",
            APP_MODEL_NOT_CONFIGURED => "应用未配置AI模型",
            APP_DISABLED => "应用已禁用",
            DOMAIN_NOT_ALLOWED => "当前域名不在允许列表中",
            DOMAIN_UNKNOWN => "无法获取请求来源域名",
            RATE_LIMIT_EXCEEDED => "请求频率超限，请稍后重试",
            DOCUMENT_NOT_FOUND => "文档不存在",
            DOCUMENT_ACCESS_DENIED => "文档访问被拒绝",
            REPOSITORY_NOT_FOUND => "仓库不存在",
            MCP_CALL_FAILED => "MCP工具调用失败",
            TOOL_EXECUTION_FAILED => "工具执行失败",
            TOOL_NOT_FOUND => "工具不存在",
            CONNECTION_FAILED => "连接失败，请检查网络",
            REQUEST_TIMEOUT => "请求超时，请重试",
            STREAM_INTERRUPTED => "数据流中断，请重试",
            INTERNAL_ERROR => "服务器内部错误",
            UNKNOWN_ERROR => "未知错误",
            _ => "发生错误"
        };
    }

    /// <summary>
    /// 判断错误是否可重试
    /// </summary>
    /// <param name="errorCode">错误码</param>
    /// <returns>是否可重试</returns>
    public static bool IsRetryable(string errorCode)
    {
        return errorCode switch
        {
            CONNECTION_FAILED => true,
            REQUEST_TIMEOUT => true,
            STREAM_INTERRUPTED => true,
            RATE_LIMIT_EXCEEDED => true,
            INTERNAL_ERROR => true,
            _ => false
        };
    }
}
