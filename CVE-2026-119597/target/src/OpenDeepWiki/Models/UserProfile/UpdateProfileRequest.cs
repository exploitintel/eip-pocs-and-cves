using System.ComponentModel.DataAnnotations;

namespace OpenDeepWiki.Models.UserProfile;

/// <summary>
/// 更新个人资料请求
/// </summary>
public class UpdateProfileRequest
{
    /// <summary>
    /// 用户名
    /// </summary>
    [Required(ErrorMessage = "用户名不能为空")]
    [StringLength(50, MinimumLength = 2, ErrorMessage = "用户名长度应在2-50个字符之间")]
    public string Name { get; set; } = string.Empty;

    /// <summary>
    /// 邮箱
    /// </summary>
    [Required(ErrorMessage = "邮箱不能为空")]
    [EmailAddress(ErrorMessage = "邮箱格式不正确")]
    public string Email { get; set; } = string.Empty;

    /// <summary>
    /// 手机号
    /// </summary>
    [StringLength(20)]
    public string? Phone { get; set; }

    /// <summary>
    /// 头像URL
    /// </summary>
    [StringLength(500)]
    public string? Avatar { get; set; }
}

/// <summary>
/// 修改密码请求
/// </summary>
public class ChangePasswordRequest
{
    /// <summary>
    /// 当前密码
    /// </summary>
    [Required(ErrorMessage = "当前密码不能为空")]
    public string CurrentPassword { get; set; } = string.Empty;

    /// <summary>
    /// 新密码
    /// </summary>
    [Required(ErrorMessage = "新密码不能为空")]
    [StringLength(100, MinimumLength = 6, ErrorMessage = "密码长度至少6位")]
    public string NewPassword { get; set; } = string.Empty;

    /// <summary>
    /// 确认新密码
    /// </summary>
    [Required(ErrorMessage = "确认密码不能为空")]
    [Compare("NewPassword", ErrorMessage = "两次密码输入不一致")]
    public string ConfirmPassword { get; set; } = string.Empty;
}

/// <summary>
/// 用户设置DTO
/// </summary>
public class UserSettingsDto
{
    /// <summary>
    /// 主题：light, dark, system
    /// </summary>
    public string Theme { get; set; } = "system";

    /// <summary>
    /// 语言
    /// </summary>
    public string Language { get; set; } = "zh";

    /// <summary>
    /// 是否开启邮件通知
    /// </summary>
    public bool EmailNotifications { get; set; } = true;

    /// <summary>
    /// 是否开启推送通知
    /// </summary>
    public bool PushNotifications { get; set; } = false;
}
