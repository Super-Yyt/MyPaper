<?php
session_start();
require_once 'db.php';

// 检查是否传递了class_id和subject
if (!isset($_GET['class_id']) || !isset($_GET['subject'])) {
    echo json_encode(['error' => '缺少必要参数']);
    exit();
}

// 获取传递的class_id和subject
$class_id = (int)$_GET['class_id'];
$subject = $_GET['subject'];

// 获取当前会话中的用户ID
$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
if ($user_id === 0) {
    echo json_encode(['error' => '用户未登录']);
    exit();
}

// 获取当天日期
$date = date('Y-m-d');

// 查询该科目的作业内容
$query = "SELECT assignment_text FROM assignments WHERE class_id = :class_id AND subject = :subject AND date = :date";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':class_id', $class_id, PDO::PARAM_INT);
$stmt->bindParam(':subject', $subject, PDO::PARAM_STR);
$stmt->bindParam(':date', $date, PDO::PARAM_STR);
$stmt->execute();

$assignment = $stmt->fetch(PDO::FETCH_ASSOC);

// 返回作业内容，如果没有作业，则返回空字符串
if ($assignment) {
    echo json_encode(['assignment_text' => $assignment['assignment_text']]);
} else {
    echo json_encode(['assignment_text' => '']);
}
