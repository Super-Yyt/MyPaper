<?php

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// 引入数据库连接
require_once 'db.php';


session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {


    // 获取表单数据
    $username = trim($_POST['username']);
    $qq_email = trim($_POST['qq_email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // 进行表单数据验证

    // 验证用户名格式
    if (strlen($username) < 3 || strlen($username) > 20) {
        echo "用户名长度必须在3到20个字符之间！";
        exit();
    }

    // 验证电子邮件格式
    if (!filter_var($qq_email, FILTER_VALIDATE_EMAIL)) {
        echo "无效的电子邮件地址！";
        exit();
    }

    // 检查密码是否匹配
    if ($password !== $confirm_password) {
        echo "密码不匹配！";
        echo '<script>setTimeout(function(){window.location.href="regLog.html";}, 2000);</script>';
        echo '<p><a href="regLog.html">没有自动返回点我</a></p>';
        exit();
    }


    // 加密密码
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // 插入数据到数据库
    $stmt = $pdo->prepare("INSERT INTO users (username, qq_email, password) VALUES (?, ?, ?)");
    $stmt->execute([$username, $qq_email, $hashed_password]);

    echo "注册成功！";
    echo '<script>setTimeout(function(){window.location.href="regLog.html";}, 2000);</script>';
    echo '<p><a href="regLog.html">没有自动返回点我</a></p>';
    exit();
}


?>