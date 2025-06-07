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

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subjects = $_POST['subjects'] ?? [];
    $teacher_id = $_POST['teacher_id'];

    if (!empty($teacher_id) && !empty($subjects)) {
        // 将学科数组转为 JSON 格式
        $subjects_json = json_encode($subjects);

        // 检查数据库中是否已经有该 teacher_id 和 class_id 的记录
        $query = "SELECT * FROM teacher_subjects WHERE teacher_id = :teacher_id AND class_id = :class_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
        $stmt->bindParam(':class_id', $class_id, PDO::PARAM_INT);
        $stmt->execute();

        // 如果记录存在，更新该记录，否则插入新记录
        if ($stmt->rowCount() > 0) {
            // 更新操作
            $query = "UPDATE teacher_subjects SET subject = :subject WHERE teacher_id = :teacher_id AND class_id = :class_id";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':subject', $subjects_json, PDO::PARAM_STR);
            $stmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
            $stmt->bindParam(':class_id', $class_id, PDO::PARAM_INT);
            $stmt->execute();
            echo "学科更新成功！";
            header("Location: classManager.php?class_id={$class_id}");
            exit();
        } else {
            // 插入操作
            $query = "INSERT INTO teacher_subjects (class_id, teacher_id, subject) VALUES (:class_id, :teacher_id, :subject)";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':class_id', $class_id, PDO::PARAM_INT);
            $stmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
            $stmt->bindParam(':subject', $subjects_json, PDO::PARAM_STR);
            $stmt->execute();
            echo "学科分配成功！";
            header("Location: classManager.php?class_id={$class_id}");
            exit();
        }
    } else {
        echo "请选择教师和科目。";
        header("Location: classManager.php?class_id={$class_id}");
        exit();
    }
}
?>