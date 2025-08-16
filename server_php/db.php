<?php
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=jqb;charset=utf8mb4",
        "db_user",
        "db_pwd",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die(json_encode(['error' => '数据库连接失败']));
}
?>