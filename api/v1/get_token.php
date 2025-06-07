<?php
// 引入数据库连接
require_once '../../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // 获取 URL 参数
    if (isset($_GET['qq_email']) && isset($_GET['password'])) {
        $qq_email = $_GET['qq_email'];
        $password = $_GET['password'];

        // 查询用户
        $stmt = $pdo->prepare("SELECT * FROM users WHERE qq_email = ?");
        $stmt->execute([$qq_email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // 密码正确，生成 token
            $token = bin2hex(random_bytes(16));  // 生成一个随机 token

            // 更新数据库，将 token 写入该用户行
            $updateStmt = $pdo->prepare("UPDATE users SET token = ? WHERE qq_email = ?");
            $updateStmt->execute([$token, $qq_email]);

            // 返回 token 给请求源
            echo json_encode([
                'status' => 'success',
                'token' => $token
            ]);
        } else {
            // 用户名或密码错误
            echo json_encode([
                'status' => 'error',
                'message' => '账号或密码错误！'
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => '缺少必要的参数！'
        ]);
    }
}
?>
