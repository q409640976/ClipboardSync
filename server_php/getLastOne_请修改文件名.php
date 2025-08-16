<?php
require_once 'db.php';
header('Content-Type: application/json');

// 定义AES密钥和组ID
define('AES_KEY', '请修改');
define('GROUP_ID', 'default');

/**
 * AES-256-CBC解密函数
 * 参考Rust代码中的AES加密实现
 */
function aes_decrypt($encrypted_data, $key) {
    // Base64解码
    $data = base64_decode($encrypted_data);
    if ($data === false) {
        return false;
    }
    
    // 提取IV（前16字节）和加密数据
    if (strlen($data) < 16) {
        return false;
    }
    
    $iv = substr($data, 0, 16);
    $encrypted = substr($data, 16);
    
    // 使用SHA256生成32字节密钥
    $hash_key = hash('sha256', $key, true);
    
    // AES-256-CBC解密
    $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $hash_key, OPENSSL_RAW_DATA, $iv);
    
    return $decrypted;
}

try {
    // 从数据库获取最新一个文本类型的剪切板数据
    $stmt = $pdo->prepare("
        SELECT * FROM datas 
        WHERE groupID = ? 
        AND contentType = 'text'
        ORDER BY lastUse DESC 
        LIMIT 1
    ");
    
    $stmt->execute([GROUP_ID]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$result) {
        http_response_code(404);
        echo "";
        exit;
    }
    
    // 解密content字段
    $decrypted_content = aes_decrypt($result['content'], AES_KEY);
    
    if ($decrypted_content === false) {
        http_response_code(500);
        echo "";
        exit;
    }
    
    // 返回解密后的内容
    echo $decrypted_content;
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => '数据库错误: ' . $e->getMessage()]);
}
?>