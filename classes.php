<?php

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

session_start();
require_once 'db.php'; // 引入数据库连接

// 检查用户是否已登录
if (!isset($_SESSION['user_email'])) {
    header("Location: regLog.html");
    exit();
}

// 获取用户ID
$user_id = $_SESSION['user_id'];

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_name = $_POST['class_name'];
    $class_token = bin2hex(random_bytes(16));

    // 在数据库中插入新班级
    $stmt = $pdo->prepare("INSERT INTO classes (class_name, class_token, class_headteacher) VALUES (?, ?, ?)");
    $stmt->execute([$class_name, $class_token, $user_id]);

    // 获取新班级的ID
    $class_id = $pdo->lastInsertId();

    // 更新用户的班级数组
    $stmt = $pdo->prepare("SELECT classes FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_classes = json_decode($stmt->fetchColumn(), true);
    $user_classes[] = $class_id; // 将新班级ID添加到用户的班级数组

    $stmt = $pdo->prepare("UPDATE users SET classes = ? WHERE id = ?");
    $stmt->execute([json_encode($user_classes), $user_id]);

    // 重定向回到班级页面
    header('Location: panel.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>创建班级</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/mdui@0.4.3/dist/css/mdui.min.css">
    <script src="https://cdn.jsdelivr.net/npm/mdui@0.4.3/dist/js/mdui.min.js"></script>
    <style>
        /* 修改样式，增加条目分隔 */
        .class-item {
            margin: 10px 0;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .manager-btn {
            background-color: #007BFF;
            color: #fff;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            border: none;
        }

        .manager-btn:hover {
            background-color: #0056b3;
        }

        @keyframes fadeIn {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }
    </style>
</head>
<body class="mdui-theme-primary-blue mdui-theme-accent-light-blue">
    <div class="mdui-container">
        <h2>创建班级</h2>

        <!-- 创建班级表单 -->
        <form action="classes.php" method="POST" class="mdui-form">
            <div class="mdui-textfield">
                <label class="mdui-textfield-label">班级名称</label>
                <input class="mdui-textfield-input" type="text" name="class_name" required />
            </div>
            <button class="mdui-btn mdui-btn-raised mdui-ripple" type="submit">创建班级</button>
        </form>

        <h3>我的班级</h3>
        <ul id="class-list">
            <!-- 班级列表将由PHP渲染 -->
            <?php
            $user_id = $_SESSION['user_id'];

            // 获取用户的班级ID数组
            $stmt = $pdo->prepare("SELECT class_id FROM classes WHERE class_headteacher = ?");
            $stmt->execute([$user_id]);
            $class_ids = $stmt->fetchAll(PDO::FETCH_COLUMN, 0); // 获取班级ID数组

            // 如果用户有班级，显示班级信息
            if (!empty($class_ids)) {
                // 构建SQL查询中的占位符
                $placeholders = implode(',', array_fill(0, count($class_ids), '?'));
                $stmt = $pdo->prepare("SELECT class_id, class_name, class_token FROM classes WHERE class_id IN ($placeholders)");
                $stmt->execute($class_ids); // 直接将$class_ids作为参数传递

                // 输出班级信息
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<li class='class-item' id='class-{$row['class_id']}'>
                            <div>
                                <strong>班级ID:</strong> {$row['class_id']}<br>
                                <strong>班级名称:</strong> {$row['class_name']}<br>
                                <strong>班级Token:</strong> {$row['class_token']}
                            </div>
                            <a href=\"classManager.php?class_id={$row['class_id']}\" target=\"_blank\"><button class='manager-btn'>管理班级</button></a>
                        </li>";
                }
            } else {
                echo "<li>暂无班级</li>";
            }
            ?>
        </ul>
    </div>
</body>
</html>
