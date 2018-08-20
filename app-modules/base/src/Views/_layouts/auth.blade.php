<!DOCTYPE html>
<html lang="zh-cn">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1170">
<meta name="renderer" content="webkit">
<meta http-equiv="Cache-Control" content="no-siteapp" />
<link rel="shortcut icon" href="/favicon.ico"/>

<title>@yield('title') - {{ $setting['site_name'] }}</title>

<!-- Bootstrap -->
{!! style('/third-party/bootstrap3/css/bootstrap.min.css') !!}
{!! style('/third-party/font-awesome4/css/font-awesome.min.css') !!}


<!-- Custom styles -->
{!! style('/assets/base/css/app.css') !!}
{!! style('/third-party/jquery-swiper/css/swiper.min.css') !!}

<style type="text/css">
body {
    font-family: arial, "微软雅黑";
    background-color: #eee;
    min-width: 1170px;
}
.navbar {
    margin-bottom: 0;
}
.container {
    width: 1170px !important;
}

#footer {
    background-color: #f8f8f8;
}

#footer .text-muted {
    margin: 0;
    padding-top: 20px;
}

#auth-box {
    max-width: 390px;
    margin: 0 auto;
    background-color: #fff;
    border: 1px solid #c9c9c9;
}

#auth-box-top {
    padding: 30px 50px 50px;
}

#auth-box-bottom {
    padding: 20px 50px;
    border-top: 1px solid #c9c9c9;
    background-color: #f4f4f4;
}

.main-form h2 {
    font-size: 32px;
}

.main-form h2,
.main-form .checkbox {
    margin-bottom: 10px;
}
.main-form .checkbox {
    font-weight: normal;
}
.main-form .form-control {
    position: relative;
    font-size: 16px;
    height: auto;
    padding: 10px;
}

/*上压下1px，z-index为了边框高亮不缺边*/
.main-form .form-control:focus {
    z-index: 2;
}
.main-form input[type="text"] {
    margin-bottom: -1px;
}
.main-form input[type="password"] {
    margin-bottom: 10px;
}

    body {padding-top: 0;}
    h2 {margin-top: 0;}
    #auth-box {
        width: 320px;
        margin: 0;
        float: right;
        border-top: none;
    }
    #auth-box-top {
        padding: 30px;
    }
    #auth-box-bottom {
        padding: 20px 30px;
    }
    .nav-tabs>li {
        width: 50%;
        text-align: center;
    }
    .nav-tabs>li>a {
        margin-right: 0;
    }
    .nav-tabs>li:first-child>a {
        margin-right: -1px;
    }
    .nav-tabs>li:not(.active)>a {
        border: 1px #ccc solid;
        background-color: #eee;
    }
    .nav-tabs>li:not(.active)>a:hover {
        background-color: #ccc;
    }

.swiper-container,
.swiper-container img {
    width: 100%;
    height: 530px;
}
</style>

<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
<script src="http://cdn.bootcss.com/html5shiv/3.7.2/html5shiv.min.js"></script>
<script src="http://cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
</head>

<body>
<div id="wraper">
    <!-- Fixed navbar -->
    <div class="navbar navbar-default">
        <div class="container">
            <div class="navbar-header">
                <a class="navbar-brand" href="/" title="草榴商城">{{ $setting['site_name'] }}</a>
            </div>
        </div>
    </div><!-- /.navbar -->

    <div id="ad-slider">
        <div class="swiper-container">
            <div class="swiper-wrapper">
                <div class="swiper-slide"><img src="/assets/shop/images/banner01.jpg" alt=""></div>
                <div class="swiper-slide"><img src="/assets/shop/images/banner02.jpg" alt=""></div>
            </div>

            <div class="swiper-pagination"></div>

            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
        </div>
        <div class="container" style="position: relative;">
            <div class="flyme" style="width: 320px; position: absolute; top: -500px; right: 20px; z-index: 99;">
                @php($active = isset($active) ? $active : ''; $class[$active] = ' class="active"';)
                <ul class="nav nav-tabs">
                  <li{!! $class['login'] or '' !!}><a href="/login">登录</a></li>
                  <li{!! $class['regist'] or '' !!}><a href="/regist">注册</a></li>
                </ul>
                @yield('wraper-contents')
            </div>
        </div><!-- /.container -->
    </div>
</div><!-- /#wraper -->

<div id="footer">
    <div class="container">
        <p class="text-muted text-center">{!! $setting['copyright'] !!}</p>
    </div>
</div>

<div id="hidden-items" style="display: none;">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" id="csrfToken">
    @yield('hidden-items')
</div>

<!-- Foot Assets
================================================== -->
{!! script('/third-party/jquery2/jquery-2.1.1.min.js') !!}
{!! script('/third-party/bootstrap3/js/bootstrap.min.js') !!}
{!! script('/assets/base/js/app.js') !!}

{!! script('/third-party/jquery-validate/jquery.validate.min.js') !!}
{!! script('/third-party/jquery-validate/additional-methods.min.js') !!}
{!! script('/third-party/jquery-form/jquery.form.min.js') !!}

{!! script('/third-party/jquery-notifyBar/js/jquery.notifyBar.js') !!}
{!! style('/third-party/jquery-notifyBar/css/jquery.notifyBar.css') !!}

{!! script('/third-party/jquery-swiper/js/swiper.jquery.min.js') !!}
<script type="text/javascript">
$(function() {
    var mySwiper = new Swiper('.swiper-container', {
        loop: true,
        pagination: '.swiper-pagination',
        paginationClickable: true,
        nextButton: '.swiper-button-next',
        prevButton: '.swiper-button-prev',
        spaceBetween: 30
    });
});
</script>

@yield('foot-assets')
</body>
</html>
