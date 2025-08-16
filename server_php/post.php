<?php
require_once 'db.php';
header('Content-Type: application/json');

// 获取POST数据
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// 验证必要参数
if (!isset($data['groupID']) || !isset($data['clientID'])) {
    http_response_code(400);
    die(json_encode(['error' => '缺少必要参数']));
}

$groupID = $data['groupID'];
$clientID = $data['clientID'];

// 验证客户端是否存在
$stmt = $pdo->prepare("SELECT * FROM clients WHERE groupID = ? AND clientID = ?");
$stmt->execute([$groupID, $clientID]);
if (!$stmt->fetch()) {
    http_response_code(403);
    die(json_encode(['error' => '未授权的客户端']));
}

// 处理数据
if (isset($data['data']) && is_array($data['data'])) {
    $pdo->beginTransaction();
    try {
        foreach ($data['data'] as $item) {
            // 检查数据是否已存在
            $stmt = $pdo->prepare("SELECT lastUse FROM datas WHERE md5 = ?");
            $stmt->execute([$item['md5']]);
            $existing = $stmt->fetch();
            
            if (!$existing) {
                // 插入新数据
                $stmt = $pdo->prepare("INSERT INTO datas (md5, groupID, clientID, content, contentType, createAt, lastUse) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $item['md5'],
                    $groupID,
                    $clientID,
                    $item['content'],
                    $item['contentType'],
                    $item['createAt'],
                    $item['lastUse']
                ]);
            } else if ($item['lastUse'] > $existing['lastUse']) {
                // 更新现有数据
                $stmt = $pdo->prepare("UPDATE datas SET lastUse = ? WHERE md5 = ?");
                $stmt->execute([$item['lastUse'], $item['md5']]);
                
                // 清除同步记录
                $stmt = $pdo->prepare("DELETE FROM syncdatas WHERE md5 = ?");
                $stmt->execute([$item['md5']]);
            }
        }
        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        die(json_encode(['error' => $e->getMessage()]));
    }
} else {
    http_response_code(400);
    die(json_encode(['error' => '无效的数据格式']));
}
?>