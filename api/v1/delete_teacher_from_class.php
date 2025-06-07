<?php

require_once '../../db.php';

// 获取请求的参数
$token = isset($_GET['token']) ? $_GET['token'] : '';
$class_id = isset($_GET['class_id']) ? $_GET['class_id'] : '';
$teacher_id = isset($_GET['teacher_id']) ? $_GET['teacher_id'] : '';

if (!$token || !$class_id || !$teacher_id) {
    echo json_encode(["status" => "error", "message" => "Missing required parameters."]);
    exit;
}

try {
    // 1. 检查 token 是否匹配 class_headteacher
    $stmt = $pdo->prepare("SELECT id FROM users WHERE token = :token");
    $stmt->execute(['token' => $token]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(["status" => "error", "message" => "Invalid token."]);
        exit;
    }

    // 2. 获取 class_headteacher 和 class_teacher 数据
    $stmt = $pdo->prepare("SELECT class_headteacher, class_teacher FROM classes WHERE class_id = :class_id");
    $stmt->execute(['class_id' => $class_id]);
    $class = $stmt->fetch();

    if (!$class) {
        echo json_encode(["status" => "error", "message" => "Class not found."]);
        exit;
    }

    // 检查 token 是否与 class_headteacher 匹配
    if ($user['id'] !== $class['class_headteacher']) {
        echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
        exit;
    }

    // 3. 从 class_teacher 数组中删除 teacher_id
    $class_teacher = json_decode($class['class_teacher'], true) ?? [];

    if (($key = array_search($teacher_id, $class_teacher)) !== false) {
        unset($class_teacher[$key]);
    } else {
        echo json_encode(["status" => "error", "message" => "Teacher not found in class teacher list."]);
        exit;
    }

    // 4. 更新 class_teacher 字段
    $stmt = $pdo->prepare("UPDATE classes SET class_teacher = :class_teacher WHERE class_id = :class_id");
    $stmt->execute([
        'class_teacher' => json_encode(array_values($class_teacher)),
        'class_id' => $class_id
    ]);

    // 5. 更新 users 表中该教师的 classes 字段
    $stmt = $pdo->prepare("SELECT classes FROM users WHERE id = :teacher_id");
    $stmt->execute(['teacher_id' => $teacher_id]);
    $user = $stmt->fetch();

    if ($user) {
        $user_classes = json_decode($user['classes'], true) ?? [];
        if (($key = array_search($class_id, $user_classes)) !== false) {
            unset($user_classes[$key]);

            // 更新用户的 classes 字段
            $stmt = $pdo->prepare("UPDATE users SET classes = :classes WHERE id = :teacher_id");
            $stmt->execute([
                'classes' => json_encode(array_values($user_classes)),
                'teacher_id' => $teacher_id
            ]);
        }
    }

    // 6. 返回成功
    echo json_encode(["status" => "success", "message" => "Teacher removed successfully from class and user's classes."]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>