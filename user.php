<?php
session_start();

// 检查用户是否已登录
if (!isset($_SESSION['user_email'])) {
    header("Location: regLog.html");
    exit;
}

require_once 'db.php';

$stmt = $pdo->prepare("SELECT * FROM users WHERE qq_email = ?");
$stmt->execute([$qq_email]);
$user = $stmt->fetch();
?>