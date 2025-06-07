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
$class_teachers = json_decode($class['class_teacher'], true); 

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

// 从数据库中获取该班级所有的邀请链接
$query = "SELECT code FROM code WHERE class_id = :class_id AND is_used = 0"; // 获取未使用的所有链接
$stmt = $pdo->prepare($query);
$stmt->bindParam(':class_id', $class_id, PDO::PARAM_INT);
$stmt->execute();

// 获取查询结果并存储到 $links 数组中
$links = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>班级信息</title>
    <!-- 引入mdui样式 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/mdui@0.4.3/dist/css/mdui.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/mdui@0.4.3/dist/js/mdui.min.js"></script>
    <style>
        body {
            background-color: #f5f5f5;
        }
        .mdui-container {
            max-width: 1200px;
        }
        .mdui-typo h1 {
            font-size: 2.5rem;
            font-weight: 500;
            margin-bottom: 20px;
        }
        .mdui-typo p {
            font-size: 1.1rem;
        }
        .mdui-card {
            margin-top: 20px;
        }
        .mdui-divider {
            margin: 20px 0;
        }
        .mdui-list-item {
            font-size: 1.1rem;
        }
        .mdui-select {
            width: 100%;
        }
    </style>
</head>
<body class="mdui-theme-primary-indigo mdui-theme-accent-pink">
    <div class="mdui-container">
        <div class="mdui-typo">
            <h1><a href="panel.php">面板(点这里返回)</a>/班级管理系统</h1>
            <p>当前班级ID: <?php echo $class_id; ?></p>
            <p>用户ID: <?php echo $user_id; ?></p>
        </div>

        <div class="mdui-divider"></div>

        <?php if ($is_headteacher): ?>
            <div class="mdui-card">
                <div class="mdui-card-primary">
                    <div class="mdui-card-primary-title">您是该班级的班主任 (<?php echo $class_headteacher_name; ?>)</div>
                </div>
                <div class="mdui-card-content">
                    <p>您可以访问班级信息，进行编辑或管理。</p>
                </div>
            </div>

            <div class="mdui-card">
                <div class="mdui-card-primary">
                    <div class="mdui-card-primary-title">分配教师学科</div>
                </div>
                <div class="mdui-card-content">
                    <form action="subjects.php?class_id=<?php echo $class_id; ?>" method="post">
                        <!-- 选择教师 -->
                        <div class="mdui-textfield">
                            <label class="mdui-textfield-label">选择教师</label>
                            <select class="mdui-select" name="teacher_id" id="teacher_id" required>
                                <option value="" disabled selected>请选择教师</option>
                                <?php
                                // 确保班主任出现在教师列表中
                                array_unshift($teachers, [
                                    'id' => $class_headteacher,
                                    'username' => $class_headteacher_name,
                                    'subjects' => []
                                ]);
                                foreach ($teachers as $teacher): ?>
                                    <option value="<?php echo $teacher['id']; ?>"><?php echo $teacher['id']; ?> - <?php echo $teacher['username']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- 选择学科 -->
                        <div class="mdui-textfield">
                            <label class="mdui-textfield-label">选择学科</label>
                            <select class="mdui-select" name="subjects[]" id="subjects" multiple required>
                                <!-- 通过JS动态加载 -->
                            </select>
                        </div>

                        <button class="mdui-btn mdui-btn-raised mdui-color-theme" action="subject" type="submit">提交</button>
                    </form>
                </div>
            </div>

            <!-- 创建邀请码部分 -->
            <div class="mdui-card">
                <div class="mdui-card-primary">
                    <div class="mdui-card-primary-title">创建邀请码</div>
                </div>
                <div class="mdui-card-content">
                    <form action="create_links.php?class_id=<?php echo $class_id; ?>" method="post">
                        <input type="hidden" name="action" value="create_links">
                        <button class="mdui-btn mdui-btn-raised mdui-color-theme" type="submit">生成邀请码</button>
                    </form>

                    <?php if ($invite_link): ?>
                        <div class="mdui-list">
                            <div class="mdui-list-item">
                                <div class="mdui-list-item-content">
                                    <span class="mdui-list-item-title">邀请码：</span>
                                    <a href="<?php echo $invite_link; ?>" target="_blank" class="mdui-text-color-theme"><?php echo $invite_link; ?></a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <h3>当前未使用的邀请码：</h3>
                    <div class="mdui-list">
                        <?php foreach ($links as $link): ?>
                            <div class="mdui-list-item">
                                <div class="mdui-list-item-content">
                                    <span class="mdui-list-item-title">
                                        邀请码: <?php echo $link['code']; ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="mdui-card">
                <div class="mdui-card-primary">
                    <div class="mdui-card-primary-title">您没有访问权限</div>
                </div>
                <div class="mdui-card-content">
                    <p>您没有权限访问此班级的信息。</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // 动态加载教师授科目
        document.getElementById('teacher_id').addEventListener('change', function() {
            var teacherId = this.value;
            var subjectsSelect = document.getElementById('subjects');
            subjectsSelect.innerHTML = ''; // 清空现有选项

            // 发送请求获取所选教师的科目
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'get_teacher_subjects.php?teacher_id=' + teacherId, true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    var subjects = JSON.parse(xhr.responseText);
                    if (subjects.length > 0) {
                        subjects.forEach(function(subject) {
                            var option = document.createElement('option');
                            option.value = subject;
                            option.textContent = subject;
                            subjectsSelect.appendChild(option);
                        });
                    }
                }
            };
            xhr.send();
        });
    </script>
</body>
</html>
