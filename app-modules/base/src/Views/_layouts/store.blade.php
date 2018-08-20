<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta charset="utf-8" />
    <title>@yield('title')</title>

    <meta name="description" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}" />


    <!--图标-->
    <link rel="shortcut icon" href="/favicon.ico">

    @yield('head-assets-before')

    <link rel="stylesheet" href="/assets/_thirdparty/bootstrap3/css/bootstrap.min.css">

    <link rel="stylesheet" href="/assets/_thirdparty/ace-admin/css/font-awesome.min.css">

    <!-- text fonts -->
    <link rel="stylesheet" href="/assets/_thirdparty/ace-admin/css/fonts.googleapis.com.css">

    <!-- ace styles -->
    <link rel="stylesheet" href="/assets/_thirdparty/ace-admin/css/ace.min.css" class="ace-main-stylesheet" id="main-ace-style">

    <!--引入样式-->
    <link rel="stylesheet" href="/assets/_layouts/store/css/common.css">
    @yield('head-assets-after')

    <!-- HTML5shiv and Respond.js for IE8 to support HTML5 elements and media queries -->
    <!--[if lte IE 8]>
        <script src="/assets/_thirdparty/H5C3/js/html5shiv.min.js"></script>
        <script src="/assets/_thirdparty/H5C3/js/respond.min.js"></script>
    <![endif]-->
</head>

<body class="no-skin">
<!--     <div class="header container">
        步履科技
    </div>
 -->
    <div id="navbar" class="navbar navbar-default">
        <div class="navbar-container container" id="navbar-container">
            <div class="navbar-header pull-left">
                <a href="/" class="navbar-brand">
                    <small>
                        <i class="fa fa-home"></i>
                        步履科技 <small>/ 卖家中心</small>
                    </small>
                </a>
            </div>

            <div class="navbar-buttons navbar-header pull-right" role="navigation">
                <ul class="nav ace-nav">
                    {{--<li class="grey dropdown-modal">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                            <i class="ace-icon fa fa-tasks"></i>
                            <span class="badge badge-grey">4</span>
                        </a>

                        <ul class="dropdown-menu-right dropdown-navbar dropdown-menu dropdown-caret dropdown-close">
                            <li class="dropdown-header">
                                <i class="ace-icon fa fa-check"></i> 4 Tasks to complete
                            </li>

                            <li class="dropdown-content">
                                <ul class="dropdown-menu dropdown-navbar">
                                    <li>
                                        <a href="#">
                                            <div class="clearfix">
                                                <span class="pull-left">Software Update</span>
                                                <span class="pull-right">65%</span>
                                            </div>

                                            <div class="progress progress-mini">
                                                <div style="width:65%" class="progress-bar"></div>
                                            </div>
                                        </a>
                                    </li>

                                    <li>
                                        <a href="#">
                                            <div class="clearfix">
                                                <span class="pull-left">Hardware Upgrade</span>
                                                <span class="pull-right">35%</span>
                                            </div>

                                            <div class="progress progress-mini">
                                                <div style="width:35%" class="progress-bar progress-bar-danger"></div>
                                            </div>
                                        </a>
                                    </li>

                                    <li>
                                        <a href="#">
                                            <div class="clearfix">
                                                <span class="pull-left">Unit Testing</span>
                                                <span class="pull-right">15%</span>
                                            </div>

                                            <div class="progress progress-mini">
                                                <div style="width:15%" class="progress-bar progress-bar-warning"></div>
                                            </div>
                                        </a>
                                    </li>

                                    <li>
                                        <a href="#">
                                            <div class="clearfix">
                                                <span class="pull-left">Bug Fixes</span>
                                                <span class="pull-right">90%</span>
                                            </div>

                                            <div class="progress progress-mini progress-striped active">
                                                <div style="width:90%" class="progress-bar progress-bar-success"></div>
                                            </div>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <li class="dropdown-footer">
                                <a href="#">
                                        See tasks with details
                                        <i class="ace-icon fa fa-arrow-right"></i>
                                    </a>
                            </li>
                        </ul>
                    </li>--}}

                    <li class="purple dropdown-modal">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                            <i class="ace-icon fa fa-bell icon-animated-bell"></i>
                            <span class="badge badge-important">8</span>
                        </a>

                        <ul class="dropdown-menu-right dropdown-navbar navbar-pink dropdown-menu dropdown-caret dropdown-close">
                            <li class="dropdown-header">
                                <i class="ace-icon fa fa-exclamation-triangle"></i> 8 Notifications
                            </li>

                            <li class="dropdown-content">
                                <ul class="dropdown-menu dropdown-navbar navbar-pink">
                                    <li>
                                        <a href="#">
                                            <div class="clearfix">
                                                <span class="pull-left">
                                                    <i class="btn btn-xs no-hover btn-pink fa fa-comment"></i>
                                                    New Comments
                                                </span>
                                                <span class="pull-right badge badge-info">+12</span>
                                            </div>
                                        </a>
                                    </li>

                                    <li>
                                        <a href="#">
                                            <div class="clearfix">
                                                <span class="pull-left">
                                                    <i class="btn btn-xs btn-primary fa fa-user"></i> Bob just signed up as an editor ...
                                                </span>
                                                <span class="pull-right badge badge-success">+8</span>
                                            </div>
                                        </a>
                                    </li>

                                    <li>
                                        <a href="#">
                                            <div class="clearfix">
                                                <span class="pull-left">
                                                        <i class="btn btn-xs no-hover btn-success fa fa-shopping-cart"></i>
                                                        New Orders
                                                    </span>
                                                <span class="pull-right badge badge-success">+8</span>
                                            </div>
                                        </a>
                                    </li>

                                    <li>
                                        <a href="#">
                                            <div class="clearfix">
                                                <span class="pull-left">
                                                        <i class="btn btn-xs no-hover btn-info fa fa-twitter"></i>
                                                        Followers
                                                    </span>
                                                <span class="pull-right badge badge-info">+11</span>
                                            </div>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <li class="dropdown-footer">
                                <a href="#">
                                        See all notifications
                                        <i class="ace-icon fa fa-arrow-right"></i>
                                    </a>
                            </li>
                        </ul>
                    </li>

                    {{--<li class="green dropdown-modal">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                            <i class="ace-icon fa fa-envelope icon-animated-vertical"></i>
                            <span class="badge badge-success">5</span>
                        </a>

                        <ul class="dropdown-menu-right dropdown-navbar dropdown-menu dropdown-caret dropdown-close">
                            <li class="dropdown-header">
                                <i class="ace-icon fa fa-envelope-o"></i> 5 Messages
                            </li>

                            <li class="dropdown-content">
                                <ul class="dropdown-menu dropdown-navbar">
                                    <li>
                                        <a href="#" class="clearfix">
                                            <img src="/assets/_thirdparty/ace-admin/images/avatars/avatar.png" class="msg-photo" alt="Alex's Avatar" />
                                            <span class="msg-body">
                                                    <span class="msg-title">
                                                        <span class="blue">Alex:</span>                                            Ciao sociis natoque penatibus et auctor ...
                                            </span>

                                            <span class="msg-time">
                                                        <i class="ace-icon fa fa-clock-o"></i>
                                                        <span>a moment ago</span>
                                            </span>
                                            </span>
                                        </a>
                                    </li>

                                    <li>
                                        <a href="#" class="clearfix">
                                            <img src="/assets/_thirdparty/ace-admin/images/avatars/avatar3.png" class="msg-photo" alt="Susan's Avatar" />
                                            <span class="msg-body">
                                                    <span class="msg-title">
                                                        <span class="blue">Susan:</span>                                            Vestibulum id ligula porta felis euismod ...
                                            </span>

                                            <span class="msg-time">
                                                        <i class="ace-icon fa fa-clock-o"></i>
                                                        <span>20 minutes ago</span>
                                            </span>
                                            </span>
                                        </a>
                                    </li>

                                    <li>
                                        <a href="#" class="clearfix">
                                            <img src="/assets/_thirdparty/ace-admin/images/avatars/avatar4.png" class="msg-photo" alt="Bob's Avatar" />
                                            <span class="msg-body">
                                                    <span class="msg-title">
                                                        <span class="blue">Bob:</span>                                            Nullam quis risus eget urna mollis ornare ...
                                            </span>

                                            <span class="msg-time">
                                                        <i class="ace-icon fa fa-clock-o"></i>
                                                        <span>3:15 pm</span>
                                            </span>
                                            </span>
                                        </a>
                                    </li>

                                    <li>
                                        <a href="#" class="clearfix">
                                            <img src="/assets/_thirdparty/ace-admin/images/avatars/avatar2.png" class="msg-photo" alt="Kate's Avatar" />
                                            <span class="msg-body">
                                                    <span class="msg-title">
                                                        <span class="blue">Kate:</span>                                            Ciao sociis natoque eget urna mollis ornare ...
                                            </span>

                                            <span class="msg-time">
                                                        <i class="ace-icon fa fa-clock-o"></i>
                                                        <span>1:33 pm</span>
                                            </span>
                                            </span>
                                        </a>
                                    </li>

                                    <li>
                                        <a href="#" class="clearfix">
                                            <img src="/assets/_thirdparty/ace-admin/images/avatars/avatar5.png" class="msg-photo" alt="Fred's Avatar" />
                                            <span class="msg-body">
                                                    <span class="msg-title">
                                                        <span class="blue">Fred:</span>                                            Vestibulum id penatibus et auctor ...
                                            </span>

                                            <span class="msg-time">
                                                        <i class="ace-icon fa fa-clock-o"></i>
                                                        <span>10:09 am</span>
                                            </span>
                                            </span>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <li class="dropdown-footer">
                                <a href="inbox.html">
                                        See all messages
                                        <i class="ace-icon fa fa-arrow-right"></i>
                                    </a>
                            </li>
                        </ul>
                    </li>--}}

                    <li class="light-blue dropdown-modal">
                        <a data-toggle="dropdown" href="#" class="dropdown-toggle">
                            <img class="nav-user-photo" src="/assets/_thirdparty/ace-admin/images/avatars/user.jpg" alt="Jason's Photo" />
                            <span class="user-info">
                                    <small>Welcome,</small>
                                    Jason
                                </span>

                            <i class="ace-icon fa fa-caret-down"></i>
                        </a>

                        <ul class="user-menu dropdown-menu-right dropdown-menu dropdown-yellow dropdown-caret dropdown-close">
                            <li>
                                <a href="#">
                                    <i class="ace-icon fa fa-cog"></i> Settings
                                </a>
                            </li>

                            <li>
                                <a href="profile.html">
                                    <i class="ace-icon fa fa-user"></i> Profile
                                </a>
                            </li>

                            <li class="divider"></li>

                            <li>
                                <a href="#">
                                    <i class="ace-icon fa fa-power-off"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
        <!-- /.navbar-container -->
    </div>

    <div class="main-container container" id="main-container">
        <div id="sidebar" class="sidebar responsivee">
            <ul class="nav nav-list">
                <li class="hover">
                    <a href="#">
                        <span class="menu-text" style="font-size: 16px; padding-left: 5px;">
                            常用操作
                        </span>
                    </a>
                    <!--<div style="padding: 5px 15px; line-height: 30px;"><a href="/store/order">已卖出的宝贝</a> | <a href="/store/goods/publish">发布宝贝</a><br><a href="/store/goods">出售中的宝贝</a> | <a href="/store/evaluate">评价管理</a></div>-->
                    <div>
                        <p><span><a href="/store/order">已卖出的宝贝</a></span><span><a href="/store/goods/publish">发布宝贝</a></span></p>
                        <p><span><a href="/store/goods">出售中的宝贝</a></span><span>评价管理<!-- <a href="/store/evaluate">评价管理</a> --></span></p>
                    </div>
                </li>

                <li class="hover">
                    <a href="#" class="dropdown-toggle">
                        <i class="menu-icon fa fa-desktop light-blue"></i>
                        <span class="menu-text">
                            交易管理
                        </span>
                    </a>

                    <!--<div style="padding: 5px 15px; line-height: 30px;"><a href="/store/order">已卖出的宝贝</a> | <a href="/store/evaluate">评价管理</a></div>-->
                    <div>
                        <p><span><a href="/store/order">已卖出的宝贝</a></span><span><a href="/store/refund">退款售后管理</a></span></p>
                        <p>评价管理<span><!-- <a href="/store/evaluate">评价管理</a> --></span></p>
                    </div>
                </li>

                <li class="hover">
                    <a href="#" class="dropdown-toggle">
                        <i class="menu-icon fa fa-list light-blue"></i>
                        <span class="menu-text">
                            物流管理
                        </span>
                    </a>

                    <!--<div style="padding: 5px 15px; line-height: 30px;"><a href="/store/logistics">发货</a></div>-->
                    <div>
                        <p><span><a href="/store/logistics">发货</a></span></p>
                    </div>
                </li>

                <li class="hover">
                    <a href="#" class="dropdown-toggle">
                        <i class="menu-icon fa fa-pencil-square-o light-blue"></i>
                        <span class="menu-text">
                            宝贝管理
                        </span>
                    </a>

                    <!--<div style="padding: 5px 15px; line-height: 30px;">
                        <a href="/store/goods/publish">发布宝贝</a> | <a href="/store/goods">出售中的宝贝</a><br>
                        <a href="/store/goods-recommend">橱窗推荐</a> | <a href="/store/goods-offline">仓库中的宝贝</a>
                    </div>-->
                    <div>
                        <p><span><a href="/store/goods/publish">发布宝贝</a></span><span><a href="/store/goods">出售中的宝贝</a></span></p>
                        <p><span>橱窗推荐<!-- <a href="/store/goods-recommend">橱窗推荐</a> --></span><span>仓库中的宝贝<!-- <a href="/store/goods-offline">仓库中的宝贝</a> --></span></p>
                    </div>
                </li>

                <li class="hover">
                    <a href="widgets.html" class="dropdown-toggle">
                        <i class="menu-icon fa fa-list-alt light-blue"></i>
                        <span class="menu-text">
                            店铺管理
                        </span>
                    </a>

                    <!--<div style="padding: 5px 15px; line-height: 30px;">
                        店铺装修 | 宝贝分类管理<br>
                        图片空间 | 子账号管理
                    </div>-->
                    <div>
                        <p><span><a href="#">宝贝分类管理</a></span><span><a href="#">图片空间</a></span></p>
                        <!-- <p><span>店铺装修</span><span>子账号管理</span></p> -->
                    </div>
                </li>

                <li class="hover">
                    <a href="#" class="dropdown-toggle">
                        <i class="menu-icon fa fa-tag light-blue"></i>
                        <span class="menu-text">
                            营销中心
                        </span>
                    </a>

                    <!--<div style="padding: 5px 15px; line-height: 30px;">生意参谋 | 活动设计</div>-->
                    <div>
                        <p><span>生意参谋</span><span>活动设计</span></p>
                    </div>
                </li>
            </ul>
            <!-- /.nav-list -->
        </div>

        <div class="main-content">
            <div class="main-content-inner">
                <div class="breadcrumbs" id="breadcrumbs">
                    @yield('breadcrumbs')
                </div>
                <!-- /.page-content -->

                @yield('nav-tabs')

                @yield('search-box')

                @yield('main-content')
            </div>
        </div>
        <!-- /.main-content -->

        <a href="#" id="btn-scroll-up" class="btn-scroll-up btn btn-sm btn-inverse">
            <i class="ace-icon fa fa-angle-double-up icon-only bigger-110"></i>
        </a>
    </div>
    <!-- /.main-container -->

    <div class="footer">
        <div class="footer-inner">
            <div class="footer-content container">
                <span class="bigger-120">
                        <span class="blue bolder">BULV</span> Application &copy; 2017
                </span>
            </div>
        </div>
    </div>

    <div id="hidden-items" style="display: none;">
        @yield('hidden-items')
    </div>

    <!-- basic scripts -->

    <!--[if !IE]> -->
    <script src="/assets/_thirdparty/jquery2/js/jquery-2.1.4.min.js"></script>
    <!-- <![endif]-->
    <script src="/assets/_thirdparty/jquery-ui/js/jquery-ui.min.js"></script>
    <script src="/assets/_thirdparty/bootstrap3/js/bootstrap.min.js"></script>

    <!-- ace scripts -->
    <script src="/assets/_thirdparty/ace-admin/js/ace.min.js"></script>

    <!--引入js-->
    <script src="/assets/base.js"></script>
    <script src="/assets/_layouts/store/js/common.js"></script>
    @yield('foot-assets-after')
</body>
</html>
