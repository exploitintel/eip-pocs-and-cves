using System.Security.Cryptography;
using System.Text;
using Microsoft.Extensions.Options;

namespace OpenDeepWiki.Chat.Config;

/// <summary>
/// 基于 AES 的配置加密实现
/// </summary>
public class AesConfigEncryption : IConfigEncryption
{
    private const string EncryptionPrefix = "ENC:";
    private readonly byte[] _key;
    private readonly byte[] _iv;
    
    public AesConfigEncryption(IOptions<ConfigEncryptionOptions> options)
    {
        var encryptionKey = options.Value.EncryptionKey;
        
        // 如果没有配置密钥，使用默认密钥（仅用于开发环境）
        if (string.IsNullOrEmpty(encryptionKey))
        {
            encryptionKey = "OpenDeepWiki_Default_Key_32Bytes!";
        }
        
        // 确保密钥长度为 32 字节（AES-256）
        _key = DeriveKey(encryptionKey, 32);
        // IV 长度为 16 字节
        _iv = DeriveKey(encryptionKey + "_IV", 16);
    }
    
    /// <summary>
    /// 从密钥字符串派生指定长度的字节数组
    /// </summary>
    private static byte[] DeriveKey(string key, int length)
    {
        using var sha256 = SHA256.Create();
        var hash = sha256.ComputeHash(Encoding.UTF8.GetBytes(key));
        var result = new byte[length];
        Array.Copy(hash, result, Math.Min(hash.Length, length));
        return result;
    }
    
    /// <inheritdoc />
    public string Encrypt(string plainText)
    {
        if (string.IsNullOrEmpty(plainText))
            return plainText;
            
        if (IsEncrypted(plainText))
            return plainText;
        
        using var aes = Aes.Create();
        aes.Key = _key;
        aes.IV = _iv;
        aes.Mode = CipherMode.CBC;
        aes.Padding = PaddingMode.PKCS7;
        
        using var encryptor = aes.CreateEncryptor();
        var plainBytes = Encoding.UTF8.GetBytes(plainText);
        var encryptedBytes = encryptor.TransformFinalBlock(plainBytes, 0, plainBytes.Length);
        
        return EncryptionPrefix + Convert.ToBase64String(encryptedBytes);
    }
    
    /// <inheritdoc />
    public string Decrypt(string cipherText)
    {
        if (string.IsNullOrEmpty(cipherText))
            return cipherText;
            
        if (!IsEncrypted(cipherText))
            return cipherText;
        
        var encryptedData = cipherText[EncryptionPrefix.Length..];
        var encryptedBytes = Convert.FromBase64String(encryptedData);
        
        using var aes = Aes.Create();
        aes.Key = _key;
        aes.IV = _iv;
        aes.Mode = CipherMode.CBC;
        aes.Padding = PaddingMode.PKCS7;
        
        using var decryptor = aes.CreateDecryptor();
        var decryptedBytes = decryptor.TransformFinalBlock(encryptedBytes, 0, encryptedBytes.Length);
        
        return Encoding.UTF8.GetString(decryptedBytes);
    }
    
    /// <inheritdoc />
    public bool IsEncrypted(string data)
    {
        return !string.IsNullOrEmpty(data) && data.StartsWith(EncryptionPrefix);
    }
}

/// <summary>
/// 配置加密选项
/// </summary>
public class ConfigEncryptionOptions
{
    /// <summary>
    /// 加密密钥
    /// </summary>
    public string EncryptionKey { get; set; } = string.Empty;
}
