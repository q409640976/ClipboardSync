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
$limit = isset($data['limit']) ? intval($data['limit']) : 10;

// 验证客户端是否存在
$stmt = $pdo->prepare("SELECT * FROM clients WHERE groupID = ? AND clientID = ?");
$stmt->execute([$groupID, $clientID]);
if (!$stmt->fetch()) {
    http_response_code(403);
    die(json_encode(['error' => '未授权的客户端']));
}

// 获取未同步的数据 - 优化版本
$stmt = $pdo->prepare("
    SELECT d.* FROM (
        SELECT * FROM datas
        WHERE groupID = ?
        AND clientID != ?
        ORDER BY lastUse desc
        LIMIT ?
    ) d
    LEFT JOIN (
        SELECT md5 
        FROM syncdatas 
        WHERE groupID = ? AND clientID = ?
        ORDER BY ID DESC
        LIMIT ?
    ) s ON d.md5 = s.md5
    WHERE s.md5 IS NULL
");
$limitPlus = $limit + 10; // 创建一个临时变量
$stmt->bindParam(1, $groupID, PDO::PARAM_STR);
$stmt->bindParam(2, $clientID, PDO::PARAM_STR);
$stmt->bindParam(3, $limit, PDO::PARAM_INT);
$stmt->bindParam(4, $groupID, PDO::PARAM_STR);
$stmt->bindParam(5, $clientID, PDO::PARAM_STR);
$stmt->bindParam(6, $limitPlus, PDO::PARAM_INT);

$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 记录同步状态
if (!empty($items)) {
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("INSERT INTO syncdatas (md5, groupID, clientID, syncTime) VALUES (?, ?, ?, NOW())");
        foreach ($items as $item) {
            $stmt->execute([$item['md5'], $groupID, $clientID]);
        }
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        die(json_encode(['error' => $e->getMessage()]));
    }
}

// 返回数据
echo json_encode($items);
?>