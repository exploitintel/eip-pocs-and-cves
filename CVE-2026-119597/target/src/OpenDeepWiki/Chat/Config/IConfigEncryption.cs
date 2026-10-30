namespace OpenDeepWiki.Chat.Config;

/// <summary>
/// 配置加密服务接口
/// </summary>
public interface IConfigEncryption
{
    /// <summary>
    /// 加密配置数据
    /// </summary>
    /// <param name="plainText">明文数据</param>
    /// <returns>加密后的数据</returns>
    string Encrypt(string plainText);
    
    /// <summary>
    /// 解密配置数据
    /// </summary>
    /// <param name="cipherText">加密数据</param>
    /// <returns>解密后的明文</returns>
    string Decrypt(string cipherText);
    
    /// <summary>
    /// 检查数据是否已加密
    /// </summary>
    /// <param name="data">数据</param>
    /// <returns>是否已加密</returns>
    bool IsEncrypted(string data);
}
