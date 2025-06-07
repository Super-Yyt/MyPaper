<?php

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

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
$class_teacher = $class['class_teacher'];

// 检查用户是否为班主任或教师
$is_headteacher = ($user_id == $class_headteacher);
$is_teacher = false;


if (!empty($class_teacher)) {
    $teacher_ids = json_decode($class_teacher, true);
    if (in_array($user_id, $teacher_ids)) {
        $is_teacher = true;
    }
}

// 如果不是班主任或教师，返回错误信息
if (!$is_headteacher && !$is_teacher) {
    echo '您不是该班级的教师';
    exit();
}

$query = "SELECT subject FROM teacher_subjects WHERE teacher_id = :teacher_id AND class_id = :class_id";
$stmt = $pdo->prepare($query);

// 绑定参数
$stmt->bindParam(':teacher_id', $user_id, PDO::PARAM_INT);
$stmt->bindParam(':class_id', $class_id, PDO::PARAM_INT);

// 执行查询
$stmt->execute();


// 获取查询结果
$subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (empty($subjects)) {
    echo '您没有所授学科';
    exit();
}

// 将 \uXXXX 转换为正常字符
function decode_unicode($string) {
    return preg_replace_callback('/\\\u([0-9a-f]{4})/i', function ($matches) {
        return mb_convert_encoding(pack('H*', $matches[1]), 'UTF-8', 'UCS-2');
    }, $string);
}

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subject = $_POST['subject'];
    $assignment_text = $_POST['assignment_text'];
    $class_id = (int) $_POST['class_id'];
    $date = date('Y-m-d'); // 获取当前日期

    // 检查当天是否已经提交该科目的作业
    $query = "SELECT assignment_id FROM assignments WHERE class_id = :class_id AND subject = :subject AND date = :date";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':class_id', $class_id, PDO::PARAM_INT);
    $stmt->bindParam(':subject', $subject, PDO::PARAM_STR);
    $stmt->bindParam(':date', $date, PDO::PARAM_STR);
    $stmt->execute();

    $existing_assignment = $stmt->fetch(PDO::FETCH_ASSOC);

    // 如果当天已存在该作业，更新作业内容，否则插入新作业
    if ($existing_assignment) {
        // 更新作业
        $query = "UPDATE assignments SET assignment_text = :assignment_text, teacher_id = :teacher_id WHERE assignment_id = :assignment_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':assignment_text', $assignment_text, PDO::PARAM_STR);
        $stmt->bindParam(':teacher_id', $user_id, PDO::PARAM_STR);
        $stmt->bindParam(':assignment_id', $existing_assignment['assignment_id'], PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo '作业更新成功';
            echo '<script>setTimeout(function(){window.location.href="class.php?class_id=' . $class_id . '";}, 2000);</script>';
            exit();
        } else {
            echo '作业更新失败';
            echo '<script>setTimeout(function(){window.location.href="class.php?class_id=' . $class_id . '";}, 2000);</script>';
            exit();
        }
    } else {
        // 插入新作业
        $query = "INSERT INTO assignments (teacher_id, class_id, subject, assignment_text, date) 
                VALUES (:teacher_id, :class_id, :subject, :assignment_text, :date)";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':teacher_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':class_id', $class_id, PDO::PARAM_INT);
        $stmt->bindParam(':subject', $subject, PDO::PARAM_STR);
        $stmt->bindParam(':assignment_text', $assignment_text, PDO::PARAM_STR);
        $stmt->bindParam(':date', $date, PDO::PARAM_STR);

        if ($stmt->execute()) {
            echo '作业提交成功';
            echo '<script>setTimeout(function(){window.location.href="class.php?class_id=' . $class_id . '";}, 2000);</script>';
            exit();
        } else {
            echo '作业提交失败';
            echo '<script>setTimeout(function(){window.location.href="class.php?class_id=' . $class_id . '";}, 2000);</script>';
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>提交作业</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/mdui@0.4.3/dist/css/mdui.min.css">
<style>
/* 调整作业内容输入框的大小 */
#assignment_text {
    height: 300px; /* 设置更大的高度 */
}

/* 按钮容器使用 flexbox 布局 */
.button-container {
    display: flex;
    justify-content: space-between; /* 按钮之间均匀分布 */
    margin-top: 20px;
}

/* 自定义刷新按钮样式为红色 */
#refresh-assignment {
    background-color: red;
    color: white;
    flex: 1; /* 按钮占据相等空间 */
    margin-left: 10px; /* 设置按钮间的间距 */
}

/* 提交按钮样式 */
#submit-assignment {
    flex: 1; /* 按钮占据相等空间 */
}

/* 页面背景 */
body {
    background-color: #f7f7f7;
}

/* 内容框样式 */
.mdui-container {
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    padding: 20px;
}

/* 页面标题样式 */
.mdui-typo-headline {
    margin-bottom: 20px;
    text-align: center;
}

/* 修改选择框和文本框的外观 */
.mdui-textfield-input, .mdui-select {
    font-size: 1rem;
    padding: 10px;
}

/* 按钮增加圆角 */
.mdui-btn {
    border-radius: 5px;
}

/* 增加页面的内边距 */
.container {
    padding: 20px;
}
</style>
</head>
<body class="mdui-theme-primary-indigo mdui-theme-accent-pink">
<div class="mdui-container mdui-m-t-5">
    <div class="mdui-typo-headline"><a href="panel.php" class="mdui-typo-title mdui-text-color-theme">面板(点这里返回)</a> / 提交作业</div>

    <form action="class.php?class_id=<?php echo $class_id; ?>" method="POST">

        <!-- 作业学科选择 -->
        <div class="mdui-textfield mdui-textfield-floating-label">
            <label for="subject">选择学科</label>
            <select class="mdui-select" name="subject" id="subject" required style="text-align: center;">
                <?php foreach ($subjects as $subject):
                    $subject_names = json_decode(decode_unicode($subject['subject']), true);
                    foreach ($subject_names as $name): ?>
                        <option value="<?php echo htmlspecialchars($name); ?>">
                            <?php echo htmlspecialchars($name); ?>
                        </option>
                    <?php endforeach;
                endforeach; ?>
            </select>
        </div>

        <!-- 作业内容 -->
        <div class="mdui-textfield mdui-textfield-floating-label">
            <label for="assignment_text">作业内容</label>
            <textarea class="mdui-textfield-input" name="assignment_text" id="assignment_text" required></textarea>
        </div>

        <input type="hidden" name="class_id" value="<?php echo $class_id; ?>">

        <!-- 按钮容器，两个按钮并排显示并对齐 -->
        <div class="button-container">
            <button type="button" class="mdui-btn mdui-btn-raised mdui-color-red" id="refresh-assignment">刷新作业</button>
            <button type="submit" class="mdui-btn mdui-btn-raised mdui-color-theme" id="submit-assignment">提交作业</button>
        </div>
        
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/mdui@0.4.3/dist/js/mdui.min.js"></script>

<script>
// 获取选择的科目并根据选择获取对应的作业内容
document.getElementById('subject').addEventListener('change', function() {
    var subject = this.value;
    var classId = <?php echo $class_id; ?>; // 当前班级ID

    // 发送 AJAX 请求获取作业内容
    fetch('get_assignment.php?class_id=' + classId + '&subject=' + encodeURIComponent(subject))
        .then(response => response.json())
        .then(data => {
            // 如果返回了作业内容，则填充文本框
            if (data.assignment_text !== undefined) {
                document.getElementById('assignment_text').value = data.assignment_text;
            } else {
                document.getElementById('assignment_text').value = '';
            }
        })
        .catch(error => {
            console.error('获取作业内容时发生错误:', error);
        });
});

// 刷新作业按钮点击事件
document.getElementById('refresh-assignment').addEventListener('click', function() {
    var subject = document.getElementById('subject').value;
    var classId = <?php echo $class_id; ?>; // 当前班级ID

    // 发送 AJAX 请求获取作业内容
    fetch('get_assignment.php?class_id=' + classId + '&subject=' + encodeURIComponent(subject))
        .then(response => response.json())
        .then(data => {
            // 如果返回了作业内容，则填充文本框
            if (data.assignment_text !== undefined) {
                document.getElementById('assignment_text').value = data.assignment_text;
            } else {
                document.getElementById('assignment_text').value = '';
            }
        })
        .catch(error => {
            console.error('获取作业内容时发生错误:', error);
        });
});

// 如果页面第一次加载，自动获取作业内容
document.addEventListener('DOMContentLoaded', function() {
    var subject = document.getElementById('subject').value;
    var classId = <?php echo $class_id; ?>; // 当前班级ID

    // 发送 AJAX 请求获取作业内容
    fetch('get_assignment.php?class_id=' + classId + '&subject=' + encodeURIComponent(subject))
        .then(response => response.json())
        .then(data => {
            // 如果返回了作业内容，则填充文本框
            if (data.assignment_text !== undefined) {
                document.getElementById('assignment_text').value = data.assignment_text;
            } else {
                document.getElementById('assignment_text').value = '';
            }
        })
        .catch(error => {
            console.error('获取作业内容时发生错误:', error);
        });
});
</script>

</body>
</html>
