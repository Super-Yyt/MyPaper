<?php

session_start();
require_once 'db.php'; // 引入数据库连接

// 检查用户是否已登录
if (!isset($_SESSION['user_email'])) {
    header("Location: regLog.html");
    exit();
}

// 获取用户ID
$user_id = $_SESSION['user_id'];

$qq_email = $_SESSION['user_email'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$subjects = json_decode($user['subjects'], true);
?>
<!DOCTYPE html>
<html lang="zh">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>用户主页</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/mdui@1.0.2/dist/css/mdui.min.css">
  <style>
    body {
      background-color: #f5f5f5;
      font-family: 'Roboto', sans-serif;
    }
    .profile-container {
      max-width: 800px;
      margin: 50px auto;
      background-color: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    .profile-header {
      text-align: center;
      margin-bottom: 20px;
    }
    .profile-avatar {
      width: 100px;
      height: 100px;
      border-radius: 50%;
      margin: 0 auto;
      background-color: #00796b;
      display: flex;
      justify-content: center;
      align-items: center;
      color: white;
      font-size: 40px;
    }
    .profile-details {
      text-align: center;
    }
    .profile-details p {
      font-size: 16px;
      color: #555;
    }
    .btn-update {
      margin-top: 20px;
      display: block;
      width: 200px;
      margin: 20px auto;
    }
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

    .goclass-btn {
        background-color: #007BFF;
        color: #fff;
        padding: 5px 10px;
        border-radius: 5px;
        cursor: pointer;
        border: none;
    }

    .goclass-btn:hover {
        background-color: #0056b3;
    }

    .subject-card {
        display: inline-block;
        background-color: #e0f7fa;
        padding: 15px;
        border-radius: 8px;
        margin: 5px;
        width: 120px;
        text-align: center;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }

    .subject-card:hover {
        transform: translateY(-5px);
    }

    .subject-card span {
        display: block;
        font-size: 16px;
        color: #00796b;
        font-weight: bold;
    }

    @keyframes fadeIn {
        0% { opacity: 0; }
        100% { opacity: 1; }
    }
  </style>
</head>
<body>

  <div class="mdui-container profile-container">
    <div class="profile-header">
      <div class="profile-avatar"><?php echo $_SESSION['username']; ?></div>
      <h2 class="mdui-typo-headline">教师名：<?php echo $_SESSION['username']; ?></h2>
    </div>
    <div class="profile-details">
      <p><strong>注册时间：</strong><?php echo $_SESSION['reg_time']; ?></p>
      <p><strong>账号状态：</strong>正常</p>
      <?php
      if ($subjects) {
        echo '<p><strong>您的学科：</strong></p>';
        echo '<div style="display: flex; flex-wrap: wrap; justify-content: center;">';
        foreach ($subjects as $subject) {
            echo "<div class='subject-card'><span>$subject</span></div>";
        }
        echo '</div>';
      } else {
          echo '<p>请前往设置选择学科</p>';
      }
      ?>
    </div>
  </div>
<div class="mdui-container profile-container">
  <h3>我所在的班级</h3>
      <ul id="class-list">
          <!-- 班级列表将由PHP渲染 -->
          <?php
          $user_id = $_SESSION['user_id'];

          // 获取用户的班级ID数组
          $stmt = $pdo->prepare("SELECT classes FROM users WHERE id = ?");
          $stmt->execute([$user_id]);
          $user_classes = json_decode($stmt->fetchColumn(), true);

          // 如果用户有班级，显示班级信息
          if (!empty($user_classes)) {
              $placeholders = implode(',', array_fill(0, count($user_classes), '?'));
              $stmt = $pdo->prepare("SELECT class_id, class_name, class_token FROM classes WHERE class_id IN ($placeholders)");
              $stmt->execute($user_classes);
              while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                  echo "<li class='class-item' id='class-{$row['class_id']}'>
                          <div>
                              <strong>班级ID:</strong> {$row['class_id']}<br>
                              <strong>班级名称:</strong> {$row['class_name']}
                          </div>
                          <a href=\"class.php?class_id={$row['class_id']}\" target=\"_blank\"><button class='goclass-btn'>进入班级</button></a>
                      </li>";
              }
          } else {
              echo "<li>暂无班级</li>";
          }
          ?>
</div>
  <script src="https://cdn.jsdelivr.net/npm/mdui@1.0.2/dist/js/mdui.min.js"></script>
</body>
</html>
