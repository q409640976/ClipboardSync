<?php
require_once 'db.php';
header('Content-Type: application/json');

// 定义常量
define('AES_KEY', '请修改');
define('GROUP_ID', 'default');
define('CLIENT_ID', 'from_web_api');

// AES加密函数
function aes_encrypt($data, $key) {
    // 使用SHA256生成32字节密钥
    $key = hash('sha256', $key, true);
    
    // 生成随机IV（16字节）
    $iv = openssl_random_pseudo_bytes(16);
    
    // AES-256-CBC加密
    $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    
    if ($encrypted === false) {
        throw new Exception('加密失败');
    }
    
    // 将IV和加密数据合并后进行Base64编码
    return base64_encode($iv . $encrypted);
}

// 检查是否为PUT请求
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    die(json_encode(['error' => '仅支持PUT请求']));
}

// 获取PUT请求的纯文本内容
$content = file_get_contents('php://input');

// 验证文本内容
if (empty($content)) {
    http_response_code(400);
    die(json_encode(['error' => '缺少文本内容']));
}

try {
    // 获取当前时间戳并转换为MySQL datetime格式
    $current_time = date('Y-m-d H:i:s');
    
    // 确保客户端记录存在
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE groupID = ? AND clientID = ?");
    $stmt->execute([GROUP_ID, CLIENT_ID]);
    if (!$stmt->fetch()) {
        // 客户端不存在，创建新的客户端记录
        $stmt = $pdo->prepare("INSERT IGNORE INTO clients (groupID, clientID, createTime, lastActive) VALUES (?, ?, ?, ?)");
        $stmt->execute([GROUP_ID, CLIENT_ID, $current_time, $current_time]);
    }
    
    // 加密内容
    $encrypted_content = aes_encrypt($content, AES_KEY);
    
    // 生成MD5哈希
    $md5 = md5($content);
    
    // 检查数据是否已存在
    $stmt = $pdo->prepare("SELECT lastUse FROM datas WHERE md5 = ?");
    $stmt->execute([$md5]);
    $existing = $stmt->fetch();
    
    if (!$existing) {
        // 插入新数据
        $stmt = $pdo->prepare("INSERT INTO datas (md5, groupID, clientID, content, contentType, createAt, lastUse) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $result = $stmt->execute([
            $md5,
            GROUP_ID,
            CLIENT_ID,
            $encrypted_content,
            'text',
            $current_time,
            $current_time
        ]);
        
        if ($result) {
            echo "新增成功";
        } else {
            throw new Exception('数据插入失败');
        }
    } else {
        // 更新现有数据的lastUse时间
        $stmt = $pdo->prepare("UPDATE datas SET lastUse = ?, content = ? WHERE md5 = ?");
        $result = $stmt->execute([$current_time, $encrypted_content, $md5]);
        
        if ($result) {
            // 清除同步记录
            $stmt = $pdo->prepare("DELETE FROM syncdatas WHERE md5 = ?");
            $stmt->execute([$md5]);
            
            echo "更新成功";
        } else {
            throw new Exception('数据更新失败');
        }
    }
    
} catch (Exception $e) {
    http_response_code(500);
    die(json_encode(['error' => $e->getMessage()]));
}
?>