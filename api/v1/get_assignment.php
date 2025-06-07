<?php
// 设置响应头，返回 JSON 格式数据
header('Content-Type: application/json');

require_once "../../db.php";

// 获取 GET 请求中的数据
$class_token = isset($_GET['class_token']) ? $_GET['class_token'] : null;
$date = isset($_GET['date']) ? $_GET['date'] : null;

// 输入检查
if (!$class_token) {
    echo json_encode(['error' => 'class_token is required']);
    exit;
}

// 如果没有传入日期，使用当天的日期
if (!$date) {
    $date = date('Y-m-d'); // 默认日期为当天
}

// 准备 SQL 查询获取 class_id
$sql = 'SELECT class_id FROM classes WHERE class_token = :class_token LIMIT 1';
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':class_token', $class_token, PDO::PARAM_STR);
$stmt->execute();

// 获取 class_id
$class_id = $stmt->fetchColumn();

// 如果没有找到 class_id
if (!$class_id) {
    echo json_encode(['error' => 'Invalid class_token']);
    exit;
}

// 准备 SQL 查询获取所有符合条件的作业记录
$sql = 'SELECT subject, assignment_text FROM assignments WHERE class_id = :class_id AND date = :date';
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':class_id', $class_id, PDO::PARAM_INT);
$stmt->bindParam(':date', $date, PDO::PARAM_STR);
$stmt->execute();

// 获取所有作业记录
$assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 如果没有找到作业
if (!$assignments) {
    echo json_encode(['error' => 'No assignments found']);
    exit;
}

// 返回所有学科和作业文本
echo json_encode([
    'assignments' => $assignments
]);
?>
