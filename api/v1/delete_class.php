<?php
require_once '../../db.php';

// 获取传入的参数
$token = isset($_GET['token']) ? $_GET['token'] : null;
$class_id = isset($_GET['class_id']) ? $_GET['class_id'] : null;

if ($token && $class_id) {
    try {
        // 查询 users 表，验证 token 是否有效，并获取用户的 id
        $stmt = $pdo->prepare("SELECT id FROM users WHERE token = :token");
        $stmt->bindParam(':token', $token);
        $stmt->execute();

        // 检查用户是否存在
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $user_id = $user['id'];

            // 查询 classes 表，获取 head_teacher 和 class_id
            $stmt = $pdo->prepare("SELECT class_headteacher FROM classes WHERE class_id = :class_id");
            $stmt->bindParam(':class_id', $class_id);
            $stmt->execute();

            $class = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($class) {
                // 比较 class_id 中的 head_teacher 与当前用户的 id 是否完全相同
                if ($class['class_headteacher'] === $user_id) {
                    // 执行删除操作
                    $stmt = $pdo->prepare("DELETE FROM classes WHERE class_id = :class_id");
                    $stmt->bindParam(':class_id', $class_id);
                    $stmt->execute();

                    echo json_encode(['status' => 'success', 'message' => 'Class deleted successfully.']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Unauthorized: User is not the head teacher.']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Class not found.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid token.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Missing parameters.']);
}

?>
