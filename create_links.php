<?php
session_start();
require_once 'db.php';

// 检查用户是否已登录
if (!isset($_SESSION['user_email'])) {
header("Location: regLog.html");
exit();
}

// 检查是否存在class_id
if (!isset($_GET['class_id'])) {
echo '班级ID不存在';
exit();
}

// 获取class_id并转换为整数
$class_id = (int) $_GET['class_id'];

// 获取当前会话中的用户ID
$user_id = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
if ($user_id === 0) {
echo '用户未登录';
exit();
}

// 查询班级信息
$query = "SELECT class_headteacher, class_teacher FROM classes WHERE class_id = :class_id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':class_id', $class_id, PDO::PARAM_INT);
$stmt->execute();

// 获取查询结果
$class = $stmt->fetch(PDO::FETCH_ASSOC);

if ($class === false) {
echo '班级不存在';
exit();
}

$class_headteacher = $class['class_headteacher'];
$class_teachers = json_decode($class['class_teacher'], true); // 假设class_teacher是存储教师ID的JSON数组

// 判断用户是否是班主任
$is_headteacher = ($user_id == $class_headteacher);

// 如果用户不是班主任，跳转
if (!$is_headteacher) {
echo '<h1>您无权访问此班级信息</h1>';
header("Location: panel.php");
exit();
}

// 获取班主任用户名
$query = "SELECT username FROM users WHERE id = :user_id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':user_id', $class_headteacher, PDO::PARAM_INT);
$stmt->execute();
$class_headteacher_name = $stmt->fetchColumn();

// 获取所有教师详细信息
$teachers = [];
foreach ($class_teachers as $teacher_id) {
$query = "SELECT id, username, subjects FROM users WHERE id = :teacher_id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
$stmt->execute();

$teacher = $stmt->fetch(PDO::FETCH_ASSOC);
if ($teacher) {
    $teachers[] = $teacher;
}
}

// 创建邀请链接的处理部分
$invite_link = ''; // 初始化邀请链接变量
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 生成唯一的邀请码
    $invite_code = bin2hex(random_bytes(16)); // 生成16字节的随机字符串作为code

        // 将邀请链接插入数据库
        $query = "INSERT INTO code (class_id, code, is_used) VALUES (:class_id, :code, 0)";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':class_id', $class_id, PDO::PARAM_INT);
        $stmt->bindParam(':code', $invite_code, PDO::PARAM_STR);
        $stmt->execute();

        // 返回生成的邀请链接
        $invite_link = "https://3t.lol/code.php?code=" . $invite_code;
        header("Location: classManager.php?class_id={$class_id}");
        exit();
    }  

// 从数据库中获取该班级所有的邀请链接
$query = "SELECT code FROM code WHERE class_id = :class_id AND is_used = 0"; // 获取未使用的所有链接
$stmt = $pdo->prepare($query);
$stmt->bindParam(':class_id', $class_id, PDO::PARAM_INT);
$stmt->execute();

// 获取查询结果并存储到 $links 数组中
$links = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>