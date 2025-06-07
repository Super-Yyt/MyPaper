<?php
session_start();

// 检查用户是否已登录
if (!isset($_SESSION['user_email'])) {
    header("Location: regLog.html");
    exit();
}

require_once 'db.php';

$qq_email = $_SESSION['user_email'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE qq_email = ?");
$stmt->execute([$qq_email]);
$user = $stmt->fetch();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 获取提交的操作类型
    $action = $_POST['action'] ?? '';

    if ($action === 'update_teacher_name') {
        // 处理设置教师名称
        $teacherName = $_POST['teacher_name'] ?? '';
        if (!empty($teacherName)) {
            $stmt = $pdo->prepare("UPDATE users SET username = :teacher_name WHERE id = :user_id");
            $stmt->execute([
                ':teacher_name' => $teacherName,
                ':user_id' => $_SESSION['user_id']
            ]);
            $_SESSION['username'] = $teacherName;
            header("Location: panel.php"); 
            exit;
        }
    } elseif ($action === 'update_subjects') {
        // 处理设置学科
        $subjects = $_POST['subjects'] ?? [];
        $validSubjects = ['数学', '语文', '英语', '物理', '化学', '生物', '历史', '地理', '音乐', '信息技术'];
        $selectedSubjects = array_intersect($subjects, $validSubjects);
        if (!empty($selectedSubjects)) {
            $stmt = $pdo->prepare("UPDATE users SET subjects = :subjects WHERE id = :user_id");
            $stmt->execute([
                ':subjects' => json_encode($selectedSubjects),
                ':user_id' => $_SESSION['user_id']
            ]);
            $_SESSION['subjects'] = $selectedSubjects; 
            header("Location: panel.php");
            exit;
        } else {
            header("Location: settings.php?error=invalid_subjects");
            exit;
        }
    } elseif ($action === 'logout') {
        // 退出登录处理
        session_destroy(); // 销毁会话
        setcookie('user_info', '', time() - 3600, '/'); 

        // 重定向到登录页面
        header("Location: regLog.html"); 
        exit;
    } elseif ($action === 'join') {
        $code = $_POST['code'] ?? '';
        header("Location: code.php?code={$code}"); 
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="zh">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>设置页面</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/mdui@0.4.3/dist/css/mdui.min.css">
  <style>
    /* 自定义样式，确保页面在手机和电脑上自适应 */
    .content-wrapper {
      padding: 20px;
    }
    .mdui-container {
      max-width: 800px;
      margin: auto;
    }
  </style>
</head>
<body class="mdui-theme-primary-blue mdui-theme-accent-light-blue">

  <div class="mdui-container content-wrapper">
    <!-- 设置学科 -->
    <div class="mdui-card">
      <div class="mdui-card-header">
        <div class="mdui-card-primary-title">设置学科</div>
      </div>
      <div class="mdui-card-content">
        <form action="settings.php" method="post">
          <div class="mdui-textfield">
            <label class="mdui-textfield-label">选择学科</label>
            <div class="mdui-row">
              <!-- 第一排学科 -->
              <div class="mdui-col mdui-col-xs-6">
                <label class="mdui-checkbox">
                  <input type="checkbox" name="subjects[]" value="数学" />
                  <i class="mdui-checkbox-icon"></i> 数学
                </label>
                <label class="mdui-checkbox">
                  <input type="checkbox" name="subjects[]" value="语文" />
                  <i class="mdui-checkbox-icon"></i> 语文
                </label>
                <label class="mdui-checkbox">
                  <input type="checkbox" name="subjects[]" value="英语" />
                  <i class="mdui-checkbox-icon"></i> 英语
                </label>
                <label class="mdui-checkbox">
                  <input type="checkbox" name="subjects[]" value="物理" />
                  <i class="mdui-checkbox-icon"></i> 物理
                </label>
              </div>
              
              <!-- 第二排学科 -->
              <div class="mdui-col mdui-col-xs-6">
                <label class="mdui-checkbox">
                  <input type="checkbox" name="subjects[]" value="化学" />
                  <i class="mdui-checkbox-icon"></i> 化学
                </label>
                <label class="mdui-checkbox">
                  <input type="checkbox" name="subjects[]" value="生物" />
                  <i class="mdui-checkbox-icon"></i> 生物
                </label>
                <label class="mdui-checkbox">
                  <input type="checkbox" name="subjects[]" value="历史" />
                  <i class="mdui-checkbox-icon"></i> 历史
                </label>
                <label class="mdui-checkbox">
                  <input type="checkbox" name="subjects[]" value="地理" />
                  <i class="mdui-checkbox-icon"></i> 地理
                </label>
              </div>
            </div>

            <div class="mdui-row">
              <div class="mdui-col mdui-col-xs-6">
                <label class="mdui-checkbox">
                  <input type="checkbox" name="subjects[]" value="音乐" />
                  <i class="mdui-checkbox-icon"></i> 音乐
                </label>
              </div>
              <div class="mdui-col mdui-col-xs-6">
                <label class="mdui-checkbox">
                  <input type="checkbox" name="subjects[]" value="信息技术" />
                  <i class="mdui-checkbox-icon"></i> 信息技术
                </label>
              </div>
            </div>
          </div>
          <button class="mdui-btn mdui-btn-raised mdui-color-theme" type="submit" name="action" value="update_subjects">提交</button>
        </form>
      </div>
    </div>

    <!-- 加入班级 -->
    <div class="mdui-card">
      <div class="mdui-card-header">
        <div class="mdui-card-primary-title">加入班级</div>
      </div>
      <div class="mdui-card-content">
        <form action="settings.php" method="post">
          <div class="mdui-textfield">
            <label class="mdui-textfield-label" for="code">邀请码</label>
            <input class="mdui-textfield-input" type="text" id="code" name="code" required />
          </div>
          <button class="mdui-btn mdui-btn-raised mdui-color-theme" type="submit" name="action" value="join">提交</button>
        </form>
      </div>
    </div>

    <!-- 设置教师名称 -->
    <div class="mdui-card">
      <div class="mdui-card-header">
        <div class="mdui-card-primary-title">设置教师名称</div>
      </div>
      <div class="mdui-card-content">
        <form action="settings.php" method="post">
          <div class="mdui-textfield">
            <label class="mdui-textfield-label" for="teacher-name">教师名称</label>
            <input class="mdui-textfield-input" type="text" id="teacher-name" name="teacher_name" required />
          </div>
          <button class="mdui-btn mdui-btn-raised mdui-color-theme" type="submit" name="action" value="update_teacher_name">提交</button>
        </form>
      </div>
    </div>

    <!-- 重置邮箱 -->
    <div class="mdui-card">
      <div class="mdui-card-header">
        <div class="mdui-card-primary-title">重置邮箱(没做)</div>
      </div>
      <div class="mdui-card-content">
        <form action="settings.php" method="post">
          <div class="mdui-textfield">
            <label class="mdui-textfield-label" for="new-email">新邮箱</label>
            <input class="mdui-textfield-input" type="email" id="new-email" name="new_email" required />
          </div>
          <button class="mdui-btn mdui-btn-raised mdui-color-theme" type="submit" name="action" value="reset_email">提交</button>
        </form>
      </div>
    </div>

    <!-- 重置密码 -->
    <div class="mdui-card">
      <div class="mdui-card-header">
        <div class="mdui-card-primary-title">重置密码(没做)</div>
      </div>
      <div class="mdui-card-content">
        <form action="settings.php" method="post">
          <div class="mdui-textfield">
            <label class="mdui-textfield-label" for="new-password">新密码</label>
            <input class="mdui-textfield-input" type="password" id="new-password" name="new_password" required />
          </div>
          <button class="mdui-btn mdui-btn-raised mdui-color-theme" type="submit" name="action" value="reset_password">提交</button>
        </form>
      </div>
    </div>
    <!-- 退出登录 -->
    <div class="mdui-card">
      <div class="mdui-card-header">
        <div class="mdui-card-primary-title">退出登录</div>
      </div>
      <div class="mdui-card-content">
        <form action="settings.php" method="post">
          <button class="mdui-btn mdui-btn-raised mdui-color-theme" type="submit" name="action" value="logout">退出登录</button>
        </form>
      </div>
    </div>
  </div>

  <!-- MDUI JS -->
  <script src="https://cdn.jsdelivr.net/npm/mdui@0.4.3/dist/js/mdui.min.js"></script>

</body>
</html>
