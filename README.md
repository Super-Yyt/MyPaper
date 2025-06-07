注意几点:
1.panel.php的$key = '{你的密钥}';这一行把{你的密钥}换成实际密钥并且和login.php的一起换了,两者要求一模一样
如$key = '1234567890';

2.db.php文件是数据库连接文件,请改为实际内容

3.我是数据库文件.sql.gz可以通过phpadminmysql导入数据库

4.app/index.zip是白板客户端的压缩包,是HTML格式,
请先在白板上安装CORS Unblock插件
然后下载本压缩包
解压
用记事本或其他编辑器打开(你也可以先把文件后缀名改成.txt在双击打开,编辑完以后改回.html)
把第99行myHeaders.append("Host", "{domain}");的{domain}改为实际域名
如myHeaders.append("Host", "3t.lol");

把111行fetch("https://{domain}/api/v1/get_assignment.php?class_token={token}", requestOptions)的{domain}换成实际域名,如果没有启用ssl,则把前面的https换成http,把{token}换成实际的班级token
如fetch("https://3t.lol/api/v1/get_assignment.php?class_token=1234567890qwertyuiop", requestOptions)