<!DOCTYPE html>
<html lang="zh">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>远程作业发布平台</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/mdui@0.4.3/dist/css/mdui.min.css">
  <style>
    body {
      background: linear-gradient(135deg, #ff7e5f, #feb47b);
      font-family: 'Arial', sans-serif;
      color: #333;
    }
    .hero-section {
      background: linear-gradient(45deg, #0078d4, #005fa3);
      color: white;
      padding: 80px 20px;
      text-align: center;
      border-radius: 8px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
      margin-bottom: 40px;
    }
    .hero-section h1 {
      font-size: 48px;
      margin: 0;
      font-weight: bold;
    }
    .hero-section p {
      font-size: 18px;
      margin: 20px 0;
    }
    .hero-section a {
      padding: 10px 30px;
      font-size: 16px;
      text-decoration: none;
      border-radius: 30px;
    }
    .download-section {
      background-color: #fff;
      padding: 60px 20px;
      border-radius: 8px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
      text-align: center;
    }
    .download-section h2 {
      font-size: 32px;
      margin-bottom: 20px;
      color: #333;
    }
    .download-section p {
      font-size: 18px;
      margin-bottom: 40px;
    }
    .download-btn-container {
      display: flex;
      justify-content: center;
      gap: 20px;
      flex-wrap: wrap;
    }
    .mdui-btn {
      font-size: 18px;
      padding: 15px 30px;
      border-radius: 50px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
      transition: all 0.3s ease-in-out;
      display: flex;
      align-items: center;
      justify-content: center; /* 保证按钮文字居中 */
      text-align: center; /* 保证文字居中 */
    }
    .mdui-btn:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
    }
    footer {
      text-align: center;
      padding: 30px;
      background-color: #222;
      color: white;
      font-size: 16px;
      border-top: 4px solid #0078d4;
    }
    @media (max-width: 768px) {
      .hero-section h1 {
        font-size: 36px;
      }
      .download-section h2 {
        font-size: 28px;
      }
    }
  </style>
</head>
<body class="mdui-theme-primary-indigo mdui-theme-accent-pink">

  <!-- Hero Section -->
  <div class="hero-section">
    <h1>远程作业发布平台</h1>
    <p>轻松管理与发布远程作业，助力高效工作</p>
    <a href="panel.php" class="mdui-btn mdui-btn-raised mdui-color-theme mdui-ripple">开始使用</a>
  </div>

  <!-- Download Section -->
  <div id="download" class="download-section">
    <h2>客户端下载</h2>
    <p>下载客户端，开始使用远程作业发布功能</p>
    <div class="download-btn-container">
      <a href="/app/acmt1.0.apk" class="mdui-btn mdui-btn-raised mdui-color-green mdui-ripple">安卓 客户端</a>
      <a href="#" class="mdui-btn mdui-btn-raised mdui-color-blue mdui-ripple">Windows 客户端</a>
      <a href="#" class="mdui-btn mdui-btn-raised mdui-color-red mdui-ripple">Mac 客户端</a>
      <a href="#" class="mdui-btn mdui-btn-raised mdui-color-yellow mdui-ripple">Linux 客户端</a>
      <a href="/app/index.zip" class="mdui-btn mdui-btn-raised mdui-color-green mdui-ripple">HTML (白板)客户端</a>
    </div>
  </div>

  <!-- Footer -->
  <footer>
    <p>&copy; 2025 CMDSTECH. 版权所有.</p>
  </footer>

  <!-- MDUI JS -->
  <script src="https://cdn.jsdelivr.net/npm/mdui@0.4.3/dist/js/mdui.min.js"></script>
</body>
</html>
