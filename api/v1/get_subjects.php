<?php
// 引入数据库连接
require_once '../../db.php';

// 设置响应头为JSON
header('Content-Type: application/json');

// 获取token参数
$token = isset($_GET['token']) ? $_GET['token'] : null;

// 如果token为空，返回错误信息
if (!$token) {
    echo json_encode(['error' => 'Token is required']);
    exit();
}

// 根据token查找对应的用户
$stmt = $pdo->prepare("SELECT * FROM users WHERE token = ?");
$stmt->execute([$token]);
$user = $stmt->fetch();

// 如果没有找到用户，返回错误信息
if (!$user) {
    echo json_encode(['error' => 'User not found']);
    exit();
}

// 获取用户的学科
$subjects = json_decode($user['subjects'], true);

// 如果用户没有学科，返回提示信息
if (!$subjects) {
    echo json_encode(['message' => 'No subjects found for this user']);
    exit();
}

// 返回学科列表
echo json_encode(['subjects' => $subjects]);
?>
