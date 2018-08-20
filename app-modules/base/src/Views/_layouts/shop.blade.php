<!DOCTYPE html>
<html lang="zh-cn">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1170">
<meta name="keyword" content="情趣用品,成人用品,成人内衣裤,安全套,避孕套,飞机杯，跳蛋">
<meta name="description" content="健康，合法的情趣用品购买和体验渠道">
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

.media img {
    border: 1px solid #ccc;
    width: 60px;
}
.media {
    font-size: 14px;
}
.media .price .cny,
.caption .price .cny {
    color: #CB9B09;
}
.media .price em,
.caption .price em {
    color: red;
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

            <form action="/shop/s" class="navbar-form navbar-left" role="search" style="margin-top: 20px;">
                <div class="input-group" style="margin: auto 120px;">
                    <input type="text" class="form-control" name="kw" value="{{ Request::input('kw') }}" placeholder="热门：飞机杯 跳蛋 震动棒 延时 护士" style="width: 400px; border-color: #d9534f;">
                    <span class="input-group-btn">
                        <button class="btn btn-danger" type="submit">　<i class="fa fa-search"></i>　</button>
                    </span>
                </div><!-- /input-group -->
            </form>

            <a href="/cart" class="btn btn-sm btn-default btn-cart pull-right shop-cart" style="margin-top: 20px;"><i class="fa fa-shopping-cart"></i> 购物车 <span class="badge shop-cart-number">0</span></a>
        </div>
    </div><!-- /.navbar -->

    <div class="container">
        @yield('main-contents')
    </div>
</div><!-- /#wraper -->

<!--footer-->
<div id="footer">
    <div class="container">
        <p class="text-muted pull-left">{!! $setting['copyright'] !!} <script type="text/javascript">var cnzz_protocol = (("https:" == document.location.protocol) ? " https://" : " http://");document.write(unescape("%3Cspan id='cnzz_stat_icon_2048668'%3E%3C/span%3E%3Cscript src='" + cnzz_protocol + "s19.cnzz.com/stat.php%3Fid%3D2048668%26show%3Dpic1' type='text/javascript'%3E%3C/script%3E"));</script></p>
        @php($serv_navi = new Apk\Navi\Services\Navi(); $link_footer = $serv_navi->getNavi('link_footer'))
        @if($link_footer)
        <p class="text-muted pull-right">
            @foreach($link_footer->top_items as $idx => $navi_item)
            {{ $idx == 0 ? '' : ' | ' }}<a href="{{ $navi_item->navi_link }}"{{ $navi_item->new_window_open ? ' target="_blank"' : ''}}>{{ $navi_item->navi_title }}</a>
            @endforeach
        </p>
        @else
        <div class="alert alert-danger pull-right" style="margin-bottom: 0;">未设置 link_footer</div>
        @endif
    </div>
</div><!-- /#footer -->

<!-- 代码 开始 -->
<div class="yb_conct">
    <div class="yb_bar">
        <ul>
            <li class="yb_top">返回顶部</li>
            <li class="yb_phone">13820693412</li>
            <li class="yb_QQ">
                <a target="_blank" href="tencent://message/?uin=18775315&Site=www.sex1024.com&Menu=yes">QQ咨询</a>
            </li>
            <li class="yb_ercode" style="height:52px; margin-bottom:0;">微信二维码 <br>
                <img class="hd_qr" src="/assets/base/images/QR.jpg" width="100%" alt="微信号：fanybook"><br>
                <p>微信号：fanybook</p>
            </li>
        </ul>
    </div>
</div>
<!-- 代码 结束 -->

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

{!! script('/third-party/jquery-kefu/js/kefu.js') !!}
{!! style('/third-party/jquery-kefu/css/kefu.css') !!}

{!! script('/third-party/jquery-validate/jquery.validate.min.js') !!}
{!! script('/third-party/jquery-validate/additional-methods.min.js') !!}
{!! script('/third-party/jquery-form/jquery.form.min.js') !!}

{!! script('/assets/shop/js/cart.list.js') !!}

<script type="text/javascript">
$(function() {
    var cart = new CartList();
    cart.getItemList();
});
</script>

@yield('foot-assets')
</body>
</html>