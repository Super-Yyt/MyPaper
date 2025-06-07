<?php
// 引入数据库连接
require_once 'db.php';

session_start();

// 加密和解密函数
function encryptData($data, $key) {
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);
    return base64_encode($encrypted . '::' . $iv);
}

function decryptData($encryptedData, $key) {
    list($encrypted, $iv) = explode('::', base64_decode($encryptedData), 2);
    return openssl_decrypt($encrypted, 'aes-256-cbc', $key, 0, $iv);
}

$key = '{你的密钥}';

// 如果 cookies 中有登录信息，尝试从 cookies 登录
if (isset($_COOKIE['user_info'])) {
    $cookieData = decryptData($_COOKIE['user_info'], $key);
    $userData = json_decode($cookieData, true);
    
    // 检查从 cookies 获取到的数据是否有效
    if ($userData && isset($userData['qq_email'])) {
        $_SESSION['user_email'] = $userData['qq_email'];
        $_SESSION['username'] = $userData['username'];
        $_SESSION['user_id'] = $userData['user_id'];
        $_SESSION['reg_time'] = $userData['reg_time'];
        header("Location: panel.php");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 获取表单数据
    $qq_email = $_POST['qq_email'];
    $password = $_POST['password'];

    // 查询用户
    $stmt = $pdo->prepare("SELECT * FROM users WHERE qq_email = ?");
    $stmt->execute([$qq_email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // 登录成功，保存会话信息到 session
        $_SESSION['user_email'] = $qq_email;
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['reg_time'] = $user['register_time'];

        // 将会话信息加密并存储到 cookies
        $userInfo = json_encode([
            'qq_email' => $qq_email,
            'username' => $user['username'],
            'user_id' => $user['id'],
            'reg_time' => $user['register_time']
        ]);
        $encryptedData = encryptData($userInfo, $key);

        // 设置 cookies，有效期为 30 天
        setcookie('user_info', $encryptedData, time() + 30 * 24 * 3600, '/');

        header("Location: panel.php");
        exit;
    } else {
        echo "邮箱或密码错误！";
        echo '<script>setTimeout(function(){window.location.href="regLog.html";}, 2000);</script>';
        echo '<p><a href="regLog.html">没有自动返回点我</a></p>';
        exit();
    }
}
?>
