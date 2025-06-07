<?php
// 设置 Content-Type 为 JSON 格式
header('Content-Type: application/json');

require_once "../../db.php";

// 获取 GET 请求中的 token
$token = isset($_GET['token']) ? $_GET['token'] : null;

// 如果没有传入 token，则返回错误
if ($token === null) {
    echo json_encode(['error' => 'Token is required']);
    exit;
}

// 查询数据库，查找与传入 token 匹配的记录
try {
    $stmt = $pdo->prepare("SELECT id, username, register_time, qq_email, classes FROM users WHERE token = :token LIMIT 1");
    $stmt->bindParam(':token', $token, PDO::PARAM_STR);
    $stmt->execute();

    // 检查是否找到记录
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // 找到记录，返回 id, username 和 regtime
        echo json_encode([
            'id' => $user['id'],
            'username' => $user['username'],
            'regtime' => $user['register_time'],
            'email' => $user['qq_email'],
            'classes' => $user['classes']
        ]);
    } else {
        // 如果没有找到记录，返回错误
        echo json_encode(['error' => 'Token not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database query failed: ' . $e->getMessage()]);
}
?>
