using System.Security.Cryptography;
using System.Text;

namespace OpenDeepWiki.Chat.Providers.WeChat;

/// <summary>
/// 微信消息加解密工具类
/// 实现微信公众平台消息加解密方案
/// </summary>
public class WeChatCrypto
{
    private readonly string _token;
    private readonly string _appId;
    private readonly byte[] _aesKey;
    private readonly byte[] _iv;
    
    /// <summary>
    /// 初始化微信加解密工具
    /// </summary>
    /// <param name="token">微信服务器配置的 Token</param>
    /// <param name="encodingAesKey">消息加解密密钥（43位字符）</param>
    /// <param name="appId">微信 AppID</param>
    public WeChatCrypto(string token, string encodingAesKey, string appId)
    {
        _token = token;
        _appId = appId;
        
        // EncodingAESKey 是 Base64 编码的 AES 密钥（43位字符 + "=" = 44位 Base64）
        _aesKey = Convert.FromBase64String(encodingAesKey + "=");
        // IV 是 AES 密钥的前 16 字节
        _iv = _aesKey[..16];
    }
    
    /// <summary>
    /// 验证消息签名
    /// </summary>
    /// <param name="signature">微信加密签名</param>
    /// <param name="timestamp">时间戳</param>
    /// <param name="nonce">随机数</param>
    /// <param name="encrypt">加密的消息体（可选，用于消息加密模式）</param>
    /// <returns>签名是否有效</returns>
    public bool VerifySignature(string signature, string timestamp, string nonce, string? encrypt = null)
    {
        var calculatedSignature = CalculateSignature(timestamp, nonce, encrypt);
        return string.Equals(signature, calculatedSignature, StringComparison.OrdinalIgnoreCase);
    }
    
    /// <summary>
    /// 计算消息签名
    /// </summary>
    /// <param name="timestamp">时间戳</param>
    /// <param name="nonce">随机数</param>
    /// <param name="encrypt">加密的消息体（可选）</param>
    /// <returns>签名字符串</returns>
    public string CalculateSignature(string timestamp, string nonce, string? encrypt = null)
    {
        var items = encrypt != null 
            ? new[] { _token, timestamp, nonce, encrypt }
            : new[] { _token, timestamp, nonce };
        
        // 字典序排序
        Array.Sort(items, StringComparer.Ordinal);
        
        // 拼接后 SHA1 哈希
        var combined = string.Concat(items);
        var hash = SHA1.HashData(Encoding.UTF8.GetBytes(combined));
        
        return Convert.ToHexString(hash).ToLowerInvariant();
    }
    
    /// <summary>
    /// 解密消息
    /// </summary>
    /// <param name="encryptedContent">加密的消息内容（Base64 编码）</param>
    /// <returns>解密后的消息内容，如果解密失败返回 null</returns>
    public string? Decrypt(string encryptedContent)
    {
        try
        {
            var encryptedBytes = Convert.FromBase64String(encryptedContent);
            
            using var aes = Aes.Create();
            aes.Key = _aesKey;
            aes.IV = _iv;
            aes.Mode = CipherMode.CBC;
            aes.Padding = PaddingMode.None;
            
            using var decryptor = aes.CreateDecryptor();
            var decryptedBytes = decryptor.TransformFinalBlock(encryptedBytes, 0, encryptedBytes.Length);
            
            // 解析解密后的数据
            // 格式：random(16字节) + msg_len(4字节) + msg + appid
            
            // 跳过前 16 字节随机数
            var msgLenBytes = decryptedBytes[16..20];
            // 网络字节序（大端）转换为消息长度
            if (BitConverter.IsLittleEndian)
            {
                Array.Reverse(msgLenBytes);
            }
            var msgLen = BitConverter.ToInt32(msgLenBytes, 0);
            
            // 提取消息内容
            var msgBytes = decryptedBytes[20..(20 + msgLen)];
            var message = Encoding.UTF8.GetString(msgBytes);
            
            // 提取并验证 AppID
            var appIdStart = 20 + msgLen;
            var appIdBytes = RemovePkcs7Padding(decryptedBytes[appIdStart..]);
            var appId = Encoding.UTF8.GetString(appIdBytes);
            
            if (appId != _appId)
            {
                return null; // AppID 不匹配
            }
            
            return message;
        }
        catch
        {
            return null;
        }
    }
    
    /// <summary>
    /// 加密消息
    /// </summary>
    /// <param name="message">要加密的消息内容</param>
    /// <returns>加密后的消息（Base64 编码）</returns>
    public string Encrypt(string message)
    {
        // 生成 16 字节随机数
        var random = new byte[16];
        RandomNumberGenerator.Fill(random);
        
        // 消息内容字节
        var msgBytes = Encoding.UTF8.GetBytes(message);
        
        // 消息长度（4字节，网络字节序）
        var msgLenBytes = BitConverter.GetBytes(msgBytes.Length);
        if (BitConverter.IsLittleEndian)
        {
            Array.Reverse(msgLenBytes);
        }
        
        // AppID 字节
        var appIdBytes = Encoding.UTF8.GetBytes(_appId);
        
        // 组装明文：random(16) + msg_len(4) + msg + appid
        var plainBytes = new byte[random.Length + msgLenBytes.Length + msgBytes.Length + appIdBytes.Length];
        var offset = 0;
        
        Buffer.BlockCopy(random, 0, plainBytes, offset, random.Length);
        offset += random.Length;
        
        Buffer.BlockCopy(msgLenBytes, 0, plainBytes, offset, msgLenBytes.Length);
        offset += msgLenBytes.Length;
        
        Buffer.BlockCopy(msgBytes, 0, plainBytes, offset, msgBytes.Length);
        offset += msgBytes.Length;
        
        Buffer.BlockCopy(appIdBytes, 0, plainBytes, offset, appIdBytes.Length);
        
        // PKCS7 填充
        var paddedBytes = AddPkcs7Padding(plainBytes);
        
        // AES 加密
        using var aes = Aes.Create();
        aes.Key = _aesKey;
        aes.IV = _iv;
        aes.Mode = CipherMode.CBC;
        aes.Padding = PaddingMode.None;
        
        using var encryptor = aes.CreateEncryptor();
        var encryptedBytes = encryptor.TransformFinalBlock(paddedBytes, 0, paddedBytes.Length);
        
        return Convert.ToBase64String(encryptedBytes);
    }
    
    /// <summary>
    /// 生成加密消息的 XML 响应
    /// </summary>
    /// <param name="encryptedContent">加密后的消息内容</param>
    /// <param name="timestamp">时间戳</param>
    /// <param name="nonce">随机数</param>
    /// <returns>XML 格式的加密响应</returns>
    public string GenerateEncryptedXml(string encryptedContent, string timestamp, string nonce)
    {
        var signature = CalculateSignature(timestamp, nonce, encryptedContent);
        
        return $@"<xml>
<Encrypt><![CDATA[{encryptedContent}]]></Encrypt>
<MsgSignature><![CDATA[{signature}]]></MsgSignature>
<TimeStamp>{timestamp}</TimeStamp>
<Nonce><![CDATA[{nonce}]]></Nonce>
</xml>";
    }
    
    /// <summary>
    /// 添加 PKCS7 填充
    /// </summary>
    private static byte[] AddPkcs7Padding(byte[] data)
    {
        const int blockSize = 32; // 微信使用 32 字节块大小
        var paddingLength = blockSize - (data.Length % blockSize);
        
        var paddedData = new byte[data.Length + paddingLength];
        Buffer.BlockCopy(data, 0, paddedData, 0, data.Length);
        
        for (var i = data.Length; i < paddedData.Length; i++)
        {
            paddedData[i] = (byte)paddingLength;
        }
        
        return paddedData;
    }
    
    /// <summary>
    /// 移除 PKCS7 填充
    /// </summary>
    private static byte[] RemovePkcs7Padding(byte[] data)
    {
        if (data.Length == 0)
            return data;
        
        var paddingLength = data[^1];
        if (paddingLength > data.Length || paddingLength > 32)
            return data;
        
        return data[..^paddingLength];
    }
}
