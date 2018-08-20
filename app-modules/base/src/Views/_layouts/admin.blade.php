<!DOCTYPE html>
<html lang="zh-cn">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1170">
<title>@yield('title') - 管理后台</title>

<!-- Bootstrap -->
{!! style('/third-party/bootstrap3/css/bootstrap.min.css') !!}
{!! style('/third-party/font-awesome4/css/font-awesome.min.css') !!}

<!-- Custom styles -->
{!! style('/assets/base/css/app.css') !!}
{!! style('/assets/admin/css/base.css') !!}

@yield('head-assets')

<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
    <script src="//cdn.bootcss.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="//cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
</head>

<body>
<!--[if lte IE 9]>
<p class="browsehappy">你正在使用<strong>过时</strong>的浏览器，不能很好的支持后台系统。 请 <a href="http://browsehappy.com/" target="_blank">升级浏览器</a> 以获得更好的体验！</p>
<![endif]-->

<!-- #admin-header -->
<header id="admin-header" class="navbar navbar-default">
    <div class="container-fluid">
        <!-- 头部-->
        <div class="navbar-header">
            <a class="navbar-brand" href="/admin">{ {{ $setting['site_name'] }} } <small>后台系统</small></a>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
          <ul class="nav navbar-nav">
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">分类导航管理 <span class="caret"></span></a>
              <ul class="dropdown-menu">
                <li><a href="/admin/navi">导航列表</a></li>
                <li><a href="/admin/navi/add">导航添加</a></li>
                <li class="divider"></li>
                <li><a href="/admin/cate-goods">商品分类</a></li>
              </ul>
            </li>
          </ul>

        <!-- 导航 -->
<!--        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">-->
            <ul class="nav navbar-nav navbar-right">
                <li><a href="/admin/feedback"><i class="fa fa-envelope-o"></i> 反馈箱 <span class="badge">5</span></a></li>
                <li><a href="/" target="_blank"><i class="fa fa-life-ring"></i> 查看前台</a></li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><i class="fa fa-users"></i> 管理员 <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li><a href="/admin/user/edit/{{ Auth::user()->id }}"><i class="fa fa-user"></i> 资料修改</a></li>
                        <li><a href="/admin/setting"><i class="fa fa-cog"></i> 网站设置</a></li>
                        <li><a href="/admin/logout"><i class="fa fa-power-off"></i> 退出后台</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</header>
<!-- /#admin-header -->

<!-- #admin-main -->
<main id="admin-main">
    <!-- #admin-sidebar -->
    <nav id="admin-sidebar">
        @php($unfolded = isset($unfolded) ? $unfolded : ''; $class[$unfolded] = ' class="unfolded"';)
        @php($active = isset($active) ? $active : ''; $class[$active] = ' class="active"';)
        <ul>
            <li{!! $class['index'] or '' !!}><a href="/admin">控制台</a></li>

            <li class="divider"></li>

            <li{!! $class['goods'] or '' !!}>
                <a role="button" data-toggle="collapse" href="#collapse-goods"><i class="fa fa-gift"></i> 商品管理 <i class="fa pull-right {{ isset($class['goods']) ? 'fa-minus-square-o' : 'fa-plus-square-o' }}"></i></a>
                <ul class="collapse admin-sidebar-sub{{ isset($class['goods']) ? ' in' : '' }}" id="collapse-goods">
                    <li{!! $class['goods_index'] or '' !!}><a href="/admin/goods"><i class="fa fa-check"></i> 商品列表</a></li>
                    <li{!! $class['goods_add'] or '' !!}><a href="/admin/goods/add"><i class="fa fa-plus"></i> 商品添加</a></li>
                    <li{!! $class['goods_tag_index'] or '' !!}><a href="/admin/goods-tag"><i class="fa fa-check"></i> 商品标签列表</a></li>
                    <li{!! $class['goods_tag_add'] or '' !!}><a href="/admin/goods-tag/add"><i class="fa fa-plus"></i> 商品标签添加</a></li>
                </ul>
            </li>

            <li{!! $class['discount'] or '' !!}>
                <a role="button" data-toggle="collapse" href="#collapse-discount"><i class="fa fa-pie-chart"></i> 折扣管理 <i class="fa pull-right {{ isset($class['discount']) ? 'fa-minus-square-o' : 'fa-plus-square-o' }}"></i></a>
                <ul class="collapse admin-sidebar-sub{{ isset($class['discount']) ? ' in' : '' }}" id="collapse-discount">
                    <li{!! $class['discount_index'] or '' !!}><a href="/admin/discount"><i class="fa fa-check"></i> 折扣列表</a></li>
                    <li{!! $class['discount_add'] or '' !!}><a href="/admin/discount/add"><i class="fa fa-plus"></i> 折扣添加</a></li>
                </ul>
            </li>

<!--            <li{!! $class['crowdfunding'] or '' !!}>
                <a role="button" data-toggle="collapse" href="#collapse-crowdfunding"><i class="fa fa-gift"></i> 众筹管理 <i class="fa pull-right {{ isset($class['crowdfunding']) ? 'fa-minus-square-o' : 'fa-plus-square-o' }}"></i></a>
                <ul class="collapse admin-sidebar-sub{{ isset($class['crowdfunding']) ? ' in' : '' }}" id="collapse-crowdfunding">
                    <li{!! $class['crowdfunding_index'] or '' !!}><a href="/admin/crowdfunding"><i class="fa fa-check"></i> 众筹列表</a></li>
                    <li{!! $class['crowdfunding_add'] or '' !!}><a href="/admin/crowdfunding/add"><i class="fa fa-plus"></i> 众筹添加</a></li>
                </ul>
            </li>-->

            <li{!! $class['trade'] or '' !!}>
                <a role="button" data-toggle="collapse" href="#collapse-trade"><i class="fa fa-calculator"></i> 交易管理 <i class="fa pull-right {{ isset($class['trade']) ? 'fa-minus-square-o' : 'fa-plus-square-o' }}"></i></a>
                <ul class="collapse admin-sidebar-sub{{ isset($class['trade']) ? ' in' : '' }}" id="collapse-trade">
                    <li{!! $class['order_index'] or '' !!}><a href="/admin/order"><i class="fa fa-check"></i> 订单列表</a></li>
                    <li{!! $class['order_add'] or '' !!}><a href="/admin/order/add"><i class="fa fa-plus"></i> 订单添加</a></li>
<!--                    <li{!! $class['payment_index'] or '' !!}><a href="/admin/payment"><i class="fa fa-plus"></i> 支付方式列表</a></li>
                    <li{!! $class['payment_add'] or '' !!}><a href="/admin/payment/add"><i class="fa fa-plus"></i> 支付方式添加</a></li>-->
                </ul>
            </li>

            <li class="divider"></li>

            <li{!! $class['user'] or '' !!}>
                <a role="button" data-toggle="collapse" href="#collapse-user"><i class="fa fa-user"></i> 用户管理 <i class="fa pull-right {{ isset($class['user']) ? 'fa-minus-square-o' : 'fa-plus-square-o' }}"></i></a>
                <ul class="collapse admin-sidebar-sub{{ isset($class['user']) ? ' in' : '' }}" id="collapse-user">
                    <li{!! $class['user_index'] or '' !!}><a href="/admin/user"><i class="fa fa-check"></i> 用户列表</a></li>
                    <li{!! $class['user_add'] or '' !!}><a href="/admin/user/add"><i class="fa fa-plus"></i> 用户添加</a></li>
                </ul>
            </li>

            <li class="divider"></li>

            <li{!! $class['article'] or '' !!}>
                <a role="button" data-toggle="collapse" href="#collapse-article"><i class="fa fa-edit"></i> 文章管理 <i class="fa pull-right {{ isset($class['article']) ? 'fa-minus-square-o' : 'fa-plus-square-o' }}"></i></a>
                <ul class="collapse admin-sidebar-sub{{ isset($class['article']) ? ' in' : '' }}" id="collapse-article">
                    <li{!! $class['article_index'] or '' !!}><a href="/admin/article"><i class="fa fa-check"></i> 文章列表</a></li>
                    <li{!! $class['article_add'] or '' !!}><a href="/admin/article/add"><i class="fa fa-plus"></i> 文章添加</a></li>
                    <li{!! $class['article_tag_index'] or '' !!}><a href="/admin/article-tag"><i class="fa fa-check"></i> 文章标签列表</a></li>
                    <li{!! $class['article_tag_add'] or '' !!}><a href="/admin/article-tag/add"><i class="fa fa-plus"></i> 文章标签添加</a></li>
                </ul>
            </li>

            <li{!! $class['page'] or '' !!}>
                <a role="button" data-toggle="collapse" href="#collapse-page"><i class="fa fa-file-text-o"></i> 页面管理 <i class="fa pull-right {{ isset($class['page']) ? 'fa-minus-square-o' : 'fa-plus-square-o' }}"></i></a>
                <ul class="collapse admin-sidebar-sub{{ isset($class['page']) ? ' in' : '' }}" id="collapse-page">
                    <li{!! $class['page_index'] or '' !!}><a href="/admin/page"><i class="fa fa-check"></i> 页面列表</a></li>
                    <li{!! $class['page_add'] or '' !!}><a href="/admin/page/add"><i class="fa fa-plus"></i> 页面添加</a></li>
                </ul>
            </li>

<!--            <li class="divider"></li>

            <li{!! $class['weixin'] or '' !!}>
                <a role="button" data-toggle="collapse" href="#collapse-weixin"><i class="fa fa-weixin"></i> 微信管理 <i class="fa pull-right {{ isset($class['weixin']) ? 'fa-minus-square-o' : 'fa-plus-square-o' }}"></i></a>
                <ul class="collapse admin-sidebar-sub{{ isset($class['weixin']) ? ' in' : '' }}" id="collapse-weixin">
                    <li{!! $class['weixin_index'] or '' !!}><a href="/admin/weixin"><i class="fa fa-check"></i> 微信设置</a></li>
                    <li{!! $class['weixin_add'] or '' !!}><a href="/admin/weixin/add"><i class="fa fa-plus"></i> 自动回复设置</a></li>
                </ul>
            </li>-->

            <li class="divider"></li>

            <li{!! $class['setting'] or '' !!}><a href="/admin/setting"><i class="fa fa-cog"></i> 网站设置 </a></li>
        </ul>
    </nav>
    <!-- /#admin-sidebar -->

    <!--右边内容-->
    <div id="admin-main-right">
        <!--breadcrumb-->
        <div id="breadcrumbs">
<!--            <i class="fa fa-home color-white mr5 pointer"></i>
            <li>@if(Request::is('admin'))首页@else<a href="/admin">首页</a>@endif</li>-->
            @yield('breadcrumbs')
        </div>

        <!--main-contents-->
        <div id="admin-main-contents">
            @yield('main-contents')
        </div>
    </div>
</main>
<!-- /#admin-main -->

<!--footer-->
<footer id="admin-footer">
    <div id="admin-footer-left"></div>
    <div id="admin-footer-right">
        <p>{!! $setting['copyright'] !!}</p>
    </div>
</footer>
<!--/footer-->

<div id="hidden-items" style="display: none;">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" id="csrfToken">
    @yield('hidden-items')
</div>

<!--js -->
{!! script('/third-party/jquery2/jquery-2.1.1.min.js') !!}
{!! script('/third-party/bootstrap3/js/bootstrap.min.js') !!}
{!! script('/assets/base/js/app.js') !!}
{!! script('/assets/admin/js/base.js') !!}

{!! script('/third-party/jquery-validate/jquery.validate.min.js') !!}
{!! script('/third-party/jquery-validate/additional-methods.min.js') !!}
{!! script('/third-party/jquery-form/jquery.form.min.js') !!}

{!! script('/third-party/jquery-notifyBar/js/jquery.notifyBar.js') !!}
{!! style('/third-party/jquery-notifyBar/css/jquery.notifyBar.css') !!}

@yield('foot-assets')
</body>
</html>