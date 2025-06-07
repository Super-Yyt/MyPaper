<?php

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

session_start();

$key = '{你的密钥}';

function decryptData($encryptedData, $key) {
    list($encrypted, $iv) = explode('::', base64_decode($encryptedData), 2);
    return openssl_decrypt($encrypted, 'aes-256-cbc', $key, 0, $iv);
}

// 检查用户是否已登录
if (!isset($_SESSION['user_email'])) {
    // 如果 cookies 中有登录信息，尝试从 cookies 登录
    if (isset($_COOKIE['user_info'])) {
        $cookieData = decryptData($_COOKIE['user_info'], $key);
        $userData = json_decode($cookieData, true);
        
        // 检查从 cookies 获取到的数据是否有效
        if ($userData && isset($userData['qq_email'])) {
            $_SESSION['user_email'] = $userData['qq_email'];
            $_SESSION['username'] = $userData['username'];
            $_SESSION['user_id'] = $userData['user_id'];
            $_SESSION['reg_time'] = $userData['reg_time'];
            header("Location: panel.php");
            exit;
        } else {
        echo "邮箱或密码错误！";
        echo '<script>setTimeout(function(){window.location.href="regLog.html";}, 2000);</script>';
        echo '<p><a href="regLog.html">没有自动返回点我</a></p>';
        exit();
        }
    } 
}

require_once 'db.php';

$qq_email = $_SESSION['user_email'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE qq_email = ?");
$stmt->execute([$qq_email]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>教师面板</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/mdui@0.4.3/dist/css/mdui.min.css"/>
    <style>
        /* 确保底栏始终在页面底部 */
        body {
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        /* 内容区域 */
        #content {
            flex-grow: 1;
            overflow-y: auto;
            padding-bottom: 60px; /* 给底栏留出空间 */
        }

        /* 底栏样式 */
        .mdui-bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 100;
        }
    </style>
</head>
<body class="mdui-appbar-with-toolbar">

    <!-- 顶栏 -->
    <div class="mdui-appbar mdui-appbar-fixed mdui-color-theme">
        <div class="mdui-toolbar">
            <span class="mdui-typo-title">教师<?php echo $_SESSION['username']; ?>的面板</span><a href="regLog.html">如果无法注册点我</a>
        </div>
    </div>

    <!-- 内容区 -->
    <div id="content" class="mdui-container" style="margin-top: 64px;">
        <!-- 动态内容会加载到这里 -->
    </div>

    <!-- 底栏 -->
    <div class="mdui-bottom-nav">
        <a href="javascript:void(0);" class="mdui-bottom-nav-item" id="home" mdui-tooltip="{content: '首页'}">
            <i class="mdui-icon material-icons">home</i>
        </a>
        <a href="javascript:void(0);" class="mdui-bottom-nav-item" id="settings" mdui-tooltip="{content: '设置'}">
            <i class="mdui-icon material-icons">settings</i>
        </a>
        <a href="javascript:void(0);" class="mdui-bottom-nav-item" id="classes" mdui-tooltip="{content: '班级'}">
            <i class="mdui-icon material-icons">book</i>
        </a>
    </div>

    <!-- MDUI JS -->
    <script src="https://cdn.jsdelivr.net/npm/mdui@0.4.3/dist/js/mdui.min.js"></script>
    <script src="js/panel.js"></script>

    <script>
        // 页面加载时默认加载首页内容
        loadContent('home');

        // 点击底栏按钮时加载相应的内容
        document.getElementById('home').addEventListener('click', function() {
            loadContent('home');
        });

        document.getElementById('settings').addEventListener('click', function() {
            loadContent('settings');
        });

        document.getElementById('classes').addEventListener('click', function() {
            loadContent('classes');
        });
    </script>

</body>
</html>
