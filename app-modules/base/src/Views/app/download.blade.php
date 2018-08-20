<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <!--<meta name="viewport" content="width=device-width, initial-scale=1.0">-->
    <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=0" />
    <meta name="apple-mobile-web-app-capable" content="yes">
    <!-- 启用360浏览器的极速模式(webkit) -->
    <meta name="renderer" content="webkit">
    <!-- 避免IE使用兼容模式 -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- 针对手持设备优化，主要是针对一些老的不识别viewport的浏览器，比如黑莓 -->
    <meta name="HandheldFriendly" content="true">
    <!-- 微软的老式浏览器 -->
    <meta name="MobileOptimized" content="320">
    <!-- uc强制竖屏 -->
    <meta name="screen-orientation" content="portrait">
    <!-- QQ强制竖屏 -->
    <meta name="x5-orientation" content="portrait">
    <!-- UC强制全屏 -->
    <meta name="full-screen" content="yes">
    <!-- QQ强制全屏 -->
    <meta name="x5-fullscreen" content="true">
    <!-- UC应用模式 -->
    <meta name="browsermode" content="application">
    <!-- QQ应用模式 -->
    <meta name="x5-page-mode" content="app">
    
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>BAN-All For Sneaker</title>

    <link rel="stylesheet" href="/m/download/css/download.css">
</head>

<body>
    <div class="download">
        <div class="box">
            <a href="https://itunes.apple.com/cn/app/ban-%E4%B8%8D%E8%A6%81%E7%8E%A9%E9%9E%8B/id1321398134?mt=8" class="Ios" id="Ios">IOS下载</a>
            <a href="http://sj.qq.com/myapp/detail.htm?apkName=com.wj.shoes" class="android" id="android">Android下载</a>
        </div>
    </div>
    <script>
        // 判断机型方法
        var isMobile = {
            Android: function() {
                return navigator.userAgent.match(/Android/i) ? true : false;
            },
            BlackBerry: function() {
                return navigator.userAgent.match(/BlackBerry/i) ? true : false;
            },
            iOS: function() {
                return navigator.userAgent.match(/iPhone|iPad|iPod/i) ? true : false;
            },
            Windows: function() {
                return navigator.userAgent.match(/IEMobile/i) ? true : false;
            },
            any: function() {
                return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Windows());
            }
        };
        if(isMobile.iOS()){
            var _mta_btn_id = 'Ios';
            (function() {
                var mta = document.createElement("script");
                mta.src = "//pingjs.qq.com/mta/channel_stats.js?v1";
                mta.setAttribute("name", "MTA_CHANNEL");
                mta.setAttribute("app_key", "I6RRA2QSG87T");
                mta.setAttribute("app_flag", "iOSBAN://");
                var s = document.getElementsByTagName("script")[0];
                s.parentNode.insertBefore(mta, s);
            })();
        }else if(isMobile.Android()){
            var _mta_btn_id = 'android';
            (function() {
                var mta = document.createElement("script");
                mta.src = "//pingjs.qq.com/mta/channel_stats.js?v1";
                mta.setAttribute("name", "MTA_CHANNEL");
                mta.setAttribute("app_key", "A821UPMF6WJW");
                var s = document.getElementsByTagName("script")[0];
                s.parentNode.insertBefore(mta, s);
            })();
        }
       
        
    </script>
</body>
</html>
