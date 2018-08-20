<!DOCTYPE html>
<html lang="zh-cn">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="keyword" content="站长博客, 首席铲屎官, 正规猫舍, 纯种猫, 英短, 英国短毛猫, 蓝猫, cfa, wcf, caa">
<meta name="description" content="喵仔の家首席铲屎官的站长博客，主要介绍纯种英短蓝猫和稀有色英国短毛猫，以及喂养，治疗，繁育，比赛的过程。">
<meta name="renderer" content="webkit">
<meta http-equiv="Cache-Control" content="no-siteapp"/>

<title>@yield('title') - 站长博客 - {{ $setting['site_name'] }}</title>

<!-- Bootstrap -->
{!! style('/third-party/bootstrap3/css/bootstrap.min.css') !!}
{!! style('/third-party/font-awesome4/css/font-awesome.min.css') !!}

<!-- Custom styles -->
{!! style('/assets/blog/css/front.css') !!}
<style type="text/css">
  /* Custom page CSS
  -------------------------------------------------- */
  /* Not required for template or sticky footer method. */

/*      #wrap > .container {
    padding: 60px 15px 0;
  }*/
  .container .text-muted {
    margin: 20px 0;
  }

  #footer > .container {
    padding-left: 15px;
    padding-right: 15px;
  }

  code {
    font-size: 80%;
  }

  h1 {
    margin-top: 0;
  }

  #tag-list {
    margin: 0;
    padding: 0;
    list-style: none;
  }
  #tag-list li {
    float: left;
    border: 1px solid #ccc;
    margin: 0 5px 5px 0;
    padding: 4px 8px;
  }

  .article-body img {
      max-width: 100%;
  }

  a:focus {outline-style:none; -moz-outline-style: none;}
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
<!-- Wrap all page content here -->
<div id="wrap">
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
            <a class="navbar-brand" href="/blog">　　　　　　　　　　<b> 站长博客</b></a>
        </div>
        <div class="collapse navbar-collapse">
            <ul class="nav navbar-nav navbar-right">
                <li><a href="/">返回商城</a></li>
                <li><a href="/blog/news">商城新闻</a></li>
                <li><a href="/blog/help">商城购买帮助</a></li>
                <li><a href="/blog/p/about-me">关于我</a></li>
            </ul>
        </div><!--/.nav-collapse -->
    </div>
</div>

<div class="container">
    <!-- Begin page breadcrumb -->
    @if (!Request::is('/'))
    <ol class="breadcrumb">
        当前位置：
        <li><a href="/blog">博客首页</a></li>
        @yield('breadcrumb')
    </ol>
    @endif

    <!-- Begin page content -->
    <div class="row">
        <div class="col-md-9">
            @yield('main-contents')
        </div>

        <div class="col-md-3">
            <form  accept-charset="UTF-8" novalidate="novalidate" autocomplete="off">
            <div id="article-search" class="input-group">
                <input type="text" class="form-control" name="kw" placeholder="博客内搜索..." value="{{ Request::input('kw') }}">
                <span class="input-group-btn">
                    <button class="btn btn-default" type="submit">搜索</button>
                </span>
            </div>
            </form>

            <div class="panel panel-default">
                <div class="panel-heading">标签云</div>
                <div class="panel-body">
                    @php($at_inst = new Apk\Article\Services\ArticleTag())
                    @php($atags = $at_inst->getArticleTag())
                    @foreach ($atags as $tag)
                    <a href="/blog?tag={{ $tag->id }}" style="font-size: {{ font_size($at_inst->getMaxArticleTag(), $tag->article_cnt) }}px;">{{ $tag->tag_name }}</a>
                    @endforeach
                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading">最新文章</div>
                <div class="panel-body">
                    @php($serv_article = new Apk\Article\Services\Article())
                    @php($newst = $serv_article->getNewBlogArticle())
                    <ul class="clearfix">
                        @foreach ($newst as $article)
                        <li><a href="/blog/article/{{ $article->id }}" title="{{ $article->article_title }}">{{ $article->article_title }}</a></li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div><!-- /.row -->
</div><!-- /.container -->
</div>

<div id="footer">
    <div class="container">
        <p class="text-muted">{!! $setting['copyright'] !!} <script type="text/javascript">var cnzz_protocol = (("https:" == document.location.protocol) ? " https://" : " http://");document.write(unescape("%3Cspan id='cnzz_stat_icon_2048668'%3E%3C/span%3E%3Cscript src='" + cnzz_protocol + "s19.cnzz.com/stat.php%3Fid%3D2048668%26show%3Dpic1' type='text/javascript'%3E%3C/script%3E"));</script></p>
    </div>
</div>

<div id="hidden-items" style="display: none;">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" id="csrfToken">
    @yield('hidden-items')
</div><!-- /#hidden-items -->

<!-- Foot Assets
================================================== -->
{!! script('/third-party/jquery2/jquery-2.1.1.min.js') !!}
{!! script('/third-party/bootstrap3/js/bootstrap.min.js') !!}
{!! script('/assets/base/js/app.js') !!}

{!! script('/third-party/google-code-prettify/prettify.js') !!}
{!! style('/third-party/google-code-prettify/uniform.css') !!}

{!! script('/third-party/jquery-colorbox/js/jquery.colorbox-min.js') !!}
{!! style('/third-party/jquery-colorbox/css/colorbox.css') !!}

@yield('foot-assets')

<script type="text/javascript">
$(function() {
    $('.article-body').each(function() {
        // 给图片添加colorbox
        var groupId = $(this).closest('.article-item').attr('id');
        var tagA = $('<a href="#" class="' + groupId + '" hidefocus="true"></a>');
        $(this).find('img').each(function() {
            var newA = tagA.clone();

            // 设置a标签
            newA.attr('href', $(this).attr('src'));
            newA.attr('title', $(this).attr('title'));

            // 替换原来的img标签
            newA.append($(this).clone());
            $(this).replaceWith(newA);
        });

        $('.article-body .' + groupId).colorbox({rel: groupId, maxWidth: "90%", maxHeight: "90%"});
    });

    // 代码高亮
    $('.article-body pre').addClass("prettyprint");
    prettyPrint();
//    // 显示
//    $('.article-body').show();
});
</script>
</body>
</html>