// 加载页面内容的函数
function loadContent(page) {
    let contentDiv = document.getElementById('content');
    contentDiv.innerHTML = '加载中...';

    let xhr = new XMLHttpRequest();
    xhr.open('GET', page + '.php', true); // 请求对应的页面内容
    xhr.onload = function() {
        if (xhr.status === 200) {
            contentDiv.innerHTML = xhr.responseText; // 加载返回的页面内容
        } else {
            contentDiv.innerHTML = '加载失败，请稍后再试！';
        }
    };
    xhr.send();
}