<?php
require_once 'db.php';

if (isset($_GET['teacher_id'])) {
    $teacher_id = (int) $_GET['teacher_id'];

    // 查询教师的科目
    $query = "SELECT subjects FROM users WHERE id = :teacher_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
    $stmt->execute();

    // 获取教师的科目字段
    $teacher = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($teacher && isset($teacher['subjects'])) {
        echo json_encode(json_decode($teacher['subjects'], true)); // 返回科目数组
    } else {
        echo json_encode([]); // 没有科目
    }
}
?>
