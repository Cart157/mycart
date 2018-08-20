<!DOCTYPE html>
<html lang="zh-cn">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1170">
<title>@yield('title') - BAN</title>

<!-- Custom styles -->
{!! style('/assets/_layouts/front/css/common.css') !!}
{!! style('/assets/_thirdparty/animate/css/animate.css') !!}

@yield('head-assets')

<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
    <script src="//cdn.bootcss.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="//cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
</head>

<body>
    <header class="container clearfix">
        <div class="logo-img">
            <a href="javascrpt:;"><img src="/assets/_layouts/front/img/log2_ban.png"></a>
        </div>
        <div class="right-nav">
            <ul class="top-nav">
                <li><a href="/" class="nav-style">首页</a></li>
                <li><a href="/index.html">下载APP</a></li>
                <li><a href="javascript:;">关于我们</a></li>
            </ul>
        </div>
    </header>
    <section>
        <div class="home">
            <!--logo-search-->
            <div class="logo-search container">
                <div class="logo">
                    <a href="/">
                        <img src="/assets/_layouts/front/img/BAN.png" alt="BAN-logo" title="BAN" class="animated infinite swing">
                    </a>
                </div>
                <div class="search">
                    <div class="search-top">
                        <input type="text">
                        <button class="go-search">搜索</button>
                    </div>
                    <ul>
                        <li><a href="javascript:;">Air Jordan</a></li>
                        <span></span>
                        <li><a href="javascript:;">哈达威</a></li>
                        <span></span>
                        <li><a href="javascript:;">314996-610</a></li>
                        <span></span>
                        <li><a href="javascript:;">慈善DB</a></li>
                        <span></span>
                        <li><a href="javascript:;">迈克尔·乔丹</a></li>
                        <span></span>
                        <li><a href="javascript:;">货号含义</a></li>
                    </ul>
                </div>
            </div>
        	<div class="main-content">
        		@yield('main-content')
        	</div>
        </div>
    </section>

    <div id="hidden-items" style="display: none;">
        @yield('hidden-items')
    </div>
    <!-- 二维码 -->
    <div class="qr-code">
        <span class="code-close">X</span>
        <div class="code-left">
            <img src="/assets/product/image/home-wx.png" alt="">
            <p>关注公众号</p>
            <p>球鞋动态早知道</p>
        </div>
        <div class="code-right">
            <img src="/assets/product/image/wx_home.png" alt="">
            <p>扫描下载APP Sneaker Girl聚集地 一切围绕鞋子开始</p>
        </div>
    </div>
    <footer>
        <div class="footer-content container clearfix">
            <div class="ban-img">
                <img src="/assets/_layouts/front/img/BAN.png">
            </div>
            <div class="company">
                <p>天津步履科技有限公司</p>
                <p>意见反馈：yj@bulvkeji.com</p>
                <p>商务合作：sw@bulvkeji.com</p>
            </div> 
            <div class="download-img clearfix">
                <div>
                    <img src="/assets/product/image/home-wx.png">
                    <p>关注微信</p>
                </div>
                <div>
                    <img src="/assets/product/image/home-download.png">
                    <p>下载APP</p>
                </div>
            </div>
            <div class="our">
                <p><span>关于我们</span><span>联系我们</span></p>
                <p>Copyright 2018 www.tosneaker.com All Rights Reserved</p>
                <p>津ICP备17008071号-1</p>
                <div style="margin:0 auto; padding:10px 0 0;">
                    <a target="_blank" href="http://www.beian.gov.cn/portal/registerSystemInfo?recordcode=12010602120182" style="display:inline-block;text-decoration:none;height:20px;line-height:20px;"><img src="/assets/product/image/guohui.png" style="float:left;"/><p style="float:left;height:20px;line-height:20px;margin: 0px 0px 0px 5px; color:#fff;">津公网安备 12010602120182号</p></a>
                </div>
            </div>
        </div>
    </footer>
    <!--引入js-->
    <script src="/assets/_thirdparty/jquery2/js/jquery-2.1.4.min.js"></script>
    <script src="/assets/base.js"></script>
	<script src="/assets/_layouts/front/js/common.js"></script>
	@yield('foot-assets-after')

</body>
</html>