<!DOCTYPE html>
<html lang="zh-cn">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1170">
<meta name="keyword" content="情趣用品,成人用品,情趣用品体验,安全套,避孕套,飞机杯，跳蛋">
<meta name="description" content="洗白白，健康的合法的情趣用品购买和体验店">
<meta name="author" content="fanybook.com">
<meta name="renderer" content="webkit">
<meta http-equiv="Cache-Control" content="no-siteapp"/>
<link rel="shortcut icon" href="/favicon.ico"/>
<title>@yield('title') - {{ $setting['site_name'] }}</title>

<!-- Bootstrap -->
{!! style('/third-party/bootstrap3/css/bootstrap.min.css') !!}
{!! style('/third-party/font-awesome4/css/font-awesome.min.css') !!}

<!-- Custom styles -->
{!! style('/assets/base/css/app.css') !!}
{!! style('/assets/shop/css/base.css') !!}

<style type="text/css">
.container {
    width: 1170px !important;
}

body {
    min-width: 1170px !important;
}

.tab-pane {
    background: #fff;
    margin-bottom: 20px;
}

/*.main-form button {
    margin-top: 10px;
}*/

.navbar-nav li {
    position: relative !important;
}

.nav > li > a {
    position: static;
    padding: 20px;
}

.badge-number {
    position: absolute;
    top: 6px;
    left: 80%;
    border-radius: 7px 7px 7px 0 !important;
}

.badge-default {
    background-color: #777;
}

.badge-success {
    background-color: #5cb85c;
}

.badge-danger {
    background-color: #d9534f;
}

.tag-editor {
/*    margin-top: 10px !important;*/
    border-color: #ccc !important;
}

.ellipsis {
    display: inline-block;
    white-space: nowrap;
    word-wrap: normal;
    width: 100%;
    overflow: hidden;
    -ms-text-overflow: ellipsis;
    -o-text-overflow: ellipsis;
    -webkit-text-overflow: ellipsis;
    text-overflow: ellipsis;
}
</style>

@yield('head-assets')

<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
    <script src="//cdn.bootcss.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="//cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
</head>

<body>
<div id="wraper">
    <div class="topbar">
        <div class="container" style="color: #ccc;">
            {!! $setting['topbar_notice'] !!}
            <div class="pull-right">
                @if (Auth::check())
                <a href="javascript:void(0);">您好：{{ is_null(Auth::user()->nickname) ? Auth::user()->email : Auth::user()->nickname }}</a> | <a href="/user">用户中心</a> | <a href="/logout" target="_blank">退出</a>
                @else
                <a href="/login">登录</a> | <a href="/regist">注册</a>
                @endif
            </div>
        </div>
    </div>

    <!-- Fixed navbar -->
    <div class="navbar navbar-default">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">展开导航</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="/" title="草榴商城"></a>
            </div>

            <form class="navbar-form navbar-left" role="search" style="margin-top: 20px;">
                <div class="input-group" style="margin: auto 120px;">
                    <input type="text" class="form-control" placeholder="热门：飞机杯 跳蛋 震动棒 延时 护士" style="width: 400px; border-color: #d9534f;">
                    <span class="input-group-btn">
                      <button class="btn btn-danger" type="button">　<i class="fa fa-search"></i>　</button>
                    </span>
                </div><!-- /input-group -->
            </form>

            <a href="/cart" class="btn btn-sm btn-default btn-cart pull-right shop-cart" style="margin-top: 20px;"><i class="fa fa-shopping-cart"></i> 购物车 <span class="badge shop-cart-number">0</span></a>
        </div>
    </div><!-- /.navbar -->

    <div class="container">
        <div id="main-contents" class="row">
            <div class="col-xs-2">
                <div class="panel panel-default">
                    <div class="panel-heading">用户中心</div>
                    <ul class="list-group">
                        <li class="list-group-item">
                            <a href="/user/order">　我的订单</a>
                        </li>
                        <li class="list-group-item">
                            <a href="/user/consignee">　收货地址</a>
                        </li>
                        <li class="list-group-item">
                            <a href="/user/profile">　资料完善</a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="col-xs-10">
                <div class="panel panel-default">
                    <div class="panel-body goods-list" style="min-height: 600px;">
                        @yield('sub-contents')
                    </div>
                </div>
            </div>
        </div>
    </div>
</div><!-- /#wraper -->

<!--footer-->
<div id="footer">
    <div class="container">
        <p class="text-muted pull-left">{!! $setting['copyright'] !!}</p>
        <p class="text-muted pull-right"><a href="/blog/p/business">商务合作</a> | <a href="/blog/p/disclaimer" title="我们与草榴社区完全无关">免责声明</a> | <a href="/blog/p/about-us">关于我们</a></p>
    </div>
</div><!-- /#footer -->

<div id="hidden-items" style="display: none;">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" id="csrfToken">
    @yield('hidden-items')
</div><!-- /#hidden-items -->

<!-- Foot Assets
================================================== -->
{!! script('/third-party/jquery2/jquery-2.1.1.min.js') !!}
{!! script('/third-party/bootstrap3/js/bootstrap.min.js') !!}
{!! script('/assets/base/js/app.js') !!}

{!! script('/third-party/jquery-notifyBar/js/jquery.notifyBar.js') !!}
{!! style('/third-party/jquery-notifyBar/css/jquery.notifyBar.css') !!}

{!! script('/third-party/jquery-colorbox/js/jquery.colorbox-min.js') !!}
{!! style('/third-party/jquery-colorbox/css/colorbox.css') !!}

{!! script('/third-party/jquery-validate/jquery.validate.min.js') !!}
{!! script('/third-party/jquery-validate/additional-methods.min.js') !!}
{!! script('/third-party/jquery-form/jquery.form.min.js') !!}

@yield('foot-assets')
</body>
</html>