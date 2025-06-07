<?php

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

session_start();

// 引入数据库连接
require_once 'db.php';


// 检查用户是否已登录
if (!isset($_SESSION['user_email'])) {
    header("Location: regLog.html");
    exit();
}

if (isset($_GET['code'])) {
    $invite_code = $_GET['code'];

    // 查询code是否存在且未使用
    $query = "SELECT * FROM code WHERE code = :code AND is_used = 0";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':code', $invite_code, PDO::PARAM_STR);
    $stmt->execute();
    $code_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($code_data) {
        $class_id = $code_data['class_id'];

        // 获取当前用户ID
        $user_id = $_SESSION['user_id'];

        // 获取当前班级的class_teacher和class_headteacher字段
        $query = "SELECT class_teacher, class_headteacher FROM classes WHERE class_id = :class_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':class_id', $class_id, PDO::PARAM_INT);
        $stmt->execute();
        $class_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // 处理可能为空的class_teacher字段
        $class_teacher = json_decode($class_data['class_teacher'], true);
        if (!is_array($class_teacher)) {
            $class_teacher = [];  // 如果class_teacher为空或无效，则初始化为空数组
        }

        $class_headteacher = $class_data['class_headteacher'];

        // 判断用户是否是班主任
        if ($user_id == $class_headteacher) {
            echo "您是该班级的班主任，无需加入班级！";
            echo '<script>setTimeout(function(){window.location.href="panel.php";}, 2000);</script>';
            echo '<p><a href="regLog.html">没有自动返回点我</a></p>';
        } else {
            // 判断用户是否已经是该班级的教师
            if (in_array($user_id, $class_teacher)) {
                echo "您已经是该班级的教师!";
                echo '<script>setTimeout(function(){window.location.href="regLog.html";}, 2000);</script>';
                echo '<p><a href="panel.php">没有自动返回点我</a></p>';
            } else {
                // 如果用户没有加入班级，展示确认加入的表单
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_join'])) {
                    // 获取用户的班级列表
                    $query = "SELECT classes FROM users WHERE id = :user_id";
                    $stmt = $pdo->prepare($query);
                    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                    $stmt->execute();
                    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    // 处理可能为空的classes字段
                    $user_classes = json_decode($user_data['classes'], true);
                    if (!is_array($user_classes)) {
                        $user_classes = [];  // 如果classes为空或无效，则初始化为空数组
                    }

                    // 判断用户是否已经加入该班级
                    if (!in_array($class_id, $user_classes)) {
                        // 将班级ID添加到用户的班级列表
                        $user_classes[] = $class_id;

                        // 更新用户的classes字段
                        $query = "UPDATE users SET classes = :classes WHERE id = :user_id";
                        $stmt = $pdo->prepare($query);
                        $stmt->bindParam(':classes', json_encode($user_classes), PDO::PARAM_STR);
                        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                        $stmt->execute();

                        // 将班级的教师ID添加到班级教师列表
                        $class_teacher[] = $user_id;

                        // 更新班级的class_teacher字段
                        $query = "UPDATE classes SET class_teacher = :class_teacher WHERE class_id = :class_id";
                        $stmt = $pdo->prepare($query);
                        $stmt->bindParam(':class_teacher', json_encode($class_teacher), PDO::PARAM_STR);
                        $stmt->bindParam(':class_id', $class_id, PDO::PARAM_INT);
                        $stmt->execute();

                        // 更新链接为已使用
                        $query = "UPDATE code SET is_used = 1 WHERE code = :code";
                        $stmt = $pdo->prepare($query);
                        $stmt->bindParam(':code', $invite_code, PDO::PARAM_STR);
                        $stmt->execute();

                        echo "您已成功加入该班级!";
                        echo '<script>setTimeout(function(){window.location.href="panel.php";}, 2000);</script>';
                        echo '<p><a href="regLog.html">没有自动返回点我</a></p>';
                        exit();
                    } else {
                        echo "您已是该班级的成员!";
                        echo '<script>setTimeout(function(){window.location.href="panel.php";}, 2000);</script>';
                        echo '<p><a href="regLog.html">没有自动返回点我</a></p>';
                        exit();
                    }
                } else {
                    // 显示确认加入班级的表单
                    echo '<form method="POST">
                            <p>您确认要加入该班级吗？</p>
                            <button type="submit" name="confirm_join">确认加入</button>
                          </form>';
                }
            }
        }
    } else {
        echo "无效或已使用的邀请链接!";
        echo '<script>setTimeout(function(){window.location.href="panel.php";}, 2000);</script>';
        echo '<p><a href="regLog.html">没有自动返回点我</a></p>';
        exit();
    }
}

?>
