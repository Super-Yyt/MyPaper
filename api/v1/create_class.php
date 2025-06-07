<?php

// 开启错误报告，方便调试
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

require_once '../../db.php'; // 引入数据库连接

// 获取 GET 请求参数
$class_name = isset($_GET['class_name']) ? $_GET['class_name'] : null;
$class_token = isset($_GET['token']) ? $_GET['token'] : null;

// 检查参数是否为空
if (!$class_name || !$class_token) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
    exit();
}

// 根据传入的 token 获取用户 ID
$stmt = $pdo->prepare("SELECT id FROM users WHERE token = ?");
$stmt->execute([$class_token]);

// 获取查询结果
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// 如果用户不存在，返回错误
if (!$user) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid token']);
    exit();
}

// 获取用户 ID
$user_id = $user['id'];

// 在数据库中插入新班级
$stmt = $pdo->prepare("INSERT INTO classes (class_name, class_token, class_headteacher) VALUES (?, ?, ?)");
if ($stmt->execute([$class_name, $class_token, $user_id])) {
    // 获取新班级的 ID
    $class_id = $pdo->lastInsertId();

    // 更新用户的班级数组
    $stmt = $pdo->prepare("SELECT classes FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_classes = json_decode($stmt->fetchColumn(), true);

    // 将新班级 ID 添加到用户的班级数组
    $user_classes[] = $class_id;

    $stmt = $pdo->prepare("UPDATE users SET classes = ? WHERE id = ?");
    if ($stmt->execute([json_encode($user_classes), $user_id])) {
        // 成功响应
        echo json_encode(['status' => 'success', 'message' => 'Class created successfully']);
    } else {
        // 更新用户班级数组失败
        echo json_encode(['status' => 'error', 'message' => 'Failed to update user classes']);
    }
} else {
    // 插入班级失败
    echo json_encode(['status' => 'error', 'message' => 'Failed to create class']);
}
?>
