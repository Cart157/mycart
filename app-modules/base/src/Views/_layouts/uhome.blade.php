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
    <link rel="stylesheet" href="/assets/_layouts/uhome/css/common.css">
    @yield('head-assets-after')

    <!-- HTML5shiv and Respond.js for IE8 to support HTML5 elements and media queries -->
    <!--[if lte IE 8]>
        <script src="/assets/_thirdparty/H5C3/js/html5shiv.min.js"></script>
        <script src="/assets/_thirdparty/H5C3/js/respond.min.js"></script>
    <![endif]-->
</head>
@php
    $user = Auth::user();
    if($user->author)
    {
        $author = Auth::user()->author;
    }
@endphp
<body class="no-skin">
    <!-- <div class="header container">
        步履科技
    </div> -->

    <div id="navbar" class="navbar navbar-default">
        <div class="navbar-container container" id="navbar-container">
            <!-- <button type="button" class="navbar-toggle menu-toggler pull-left" id="menu-toggler" data-target="#sidebar">
                    <span class="sr-only">Toggle sidebar</span>

                    <span class="icon-bar"></span>

                    <span class="icon-bar"></span>

                    <span class="icon-bar"></span>
                </button> -->
            <img src="/assets/_layouts/uhome/images/ban_logo.png" class="logo">
            <div class="navbar-buttons navbar-header pull-right" role="navigation">
                <ul class="nav ace-nav">
                    <li class="dope"><img src="/assets/_layouts/uhome/images/dope.png" alt=""></li>
                    <li class="light-blue dropdown-modal">
                        <a data-toggle="dropdown" href="#" class="dropdown-toggle">
                            <img class="nav-user-photo" src="{{ cdn() . $user->profile->avatar }}" alt="用户头像" />
                            <!-- <span class="user-info">
                                    <small>Welcome,</small>
                                    Jason
                                </span> -->

                            <i class="ace-icon fa fa-caret-down"></i>
                        </a>

                        <ul class="user-menu dropdown-menu-right dropdown-menu dropdown-yellow dropdown-caret dropdown-close">
                            <!--<li>
                                <a href="#">
                                    <i class="ace-icon fa fa-cog"></i> Settings
                                </a>
                            </li>

                            <li>
                                <a href="profile.html">
                                    <i class="ace-icon fa fa-user"></i> Profile
                                </a>
                            </li>

                            <li class="divider"></li>-->

                            <li>
                                <a href="/logout">
                                    <i class="ace-icon fa fa-power-off"></i> 退出
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
            <div class="user-infos">
                <span><img src="{{ cdn() . $user->profile->avatar }}" alt=""></span>
                <p class="name-me">{{ $author->real_name }}</p>
                <p class="autograph-me">{{ $author->user->profile->bio }}</p>
                <div class="num clearfix">
                    <div class="posts data-me">
                        <p>{{ $user->articles()->count() }}</p>
                        <p>总文章数</p>
                    </div>
                    <div class="total-browsing data-me">
                        <p>{{ $author->view_cnt }}</p>
                        <p>总浏览量</p>
                    </div>
                </div>
            </div>
            <div class="personal">
                <div class="personal-title">个人资料</div>
                <ul class="nav nav-list navigation">
                    <li>
                        <a href="/uhome/cms/article">文章</a>
                    </li>
                    <li>
                        <a href="/uhome/cms/article/cash">提现</a>
                    </li>
                    <li>
                        <a href="/uhome/cms/article/auth">身份认证</a>
                    </li>
                </ul>
            </div>
            <!-- /.nav-list -->
        </div>

        <div class="main-content">
            @yield('main-content')
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
    <script src="/assets/_layouts/uhome/js/common.js"></script>
    <script type="text/javascript">
        // Set active state on menu element
        var current_url = "/uhome/cms/article";
        var str = window.location.pathname; //获取URL的路径部分（就是文件地址）

        var index = str .lastIndexOf("\/");
        str  = str .substring(index + 1, str .length); //截取字符串中最后一个斜杠后面的内容

        var full_url = current_url+'/'+str;
        var $navLinks = $("ul.nav-list li a");
        // First look for an exact match including the search string
        var $curentPageLink = $navLinks.filter(
            function() { return $(this).attr('href') === full_url; }
        );
        // If not found, look for the link that starts with the url
        if(!$curentPageLink.length > 0){
            $curentPageLink = $navLinks.filter(
                function() { return $(this).attr('href') === current_url || current_url.startsWith($(this).attr('href')+'/'); }
            );
        }
        $curentPageLink.addClass('active-li');
    </script>
    @yield('foot-assets-after')
</body>
</html>
