<!DOCTYPE html>
<html lang="zh-cn">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
<meta name="renderer" content="webkit">
<meta http-equiv="Cache-Control" content="no-siteapp" />
<link rel="shortcut icon" href="/favicon.ico"/>
<title>登陆画面 - 管理后台</title>

<!-- Bootstrap -->
{!! style('/assets/_thirdparty/bootstrap3/css/bootstrap.min.css') !!}
{!! style('/assets/_thirdparty/font-awesome4/css/font-awesome.min.css') !!}

<!-- Custom styles -->
{!! style('/assets/base/css/admin.auth.css') !!}
{!! style('/assets/base/css/app.css') !!}

<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
<script src="http://cdn.bootcss.com/html5shiv/3.7.2/html5shiv.min.js"></script>
<script src="http://cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
</head>

<body>
<div id="wraper">
    <div class="container">
        <div id="auth-box">
            <div id="auth-box-top">
                <form method="POST" class="main-form" accept-charset="UTF-8" autocomplete="off" novalidate="novalidate">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">

                    <h2>管理后台</h2>

                    <input type="text" class="form-control" name="email" placeholder="管理帐号">
                    <input type="password" class="form-control" name="password" placeholder="密码">
                    <input type="text" class="form-control" name="captcha" placeholder="验证码(歌词填空)">
                    <div class="alert alert-warning">
                        <button type="button" class="close" title="刷新更换验证码"><span class="fa fa-refresh"></span></button>
                        <p><strong></strong><br><br></p>
                    </div>
                    <button class="btn btn-primary btn-lg btn-block">登录</button>
                </form>
            </div>
        </div>
    </div><!-- /container -->
</div><!-- /#wraper -->

<div id="footer">
    <div class="container">
        <p class="text-muted">{!! $site_setting['site_copyright'] or '' !!}</p>
    </div>
</div>

<div id="hidden-items" style="display: none;">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" id="csrfToken">
</div>

<!-- Foot Assets
================================================== -->
{!! script('/assets/_thirdparty/jquery2/js/jquery-2.1.4.min.js') !!}
{!! script('/assets/_thirdparty/bootstrap3/js/bootstrap.min.js') !!}

{!! script('/assets/_thirdparty/jquery-validate/jquery.validate.min.js') !!}
{!! script('/assets/_thirdparty/jquery-validate/additional-methods.min.js') !!}
{!! script('/assets/_thirdparty/jquery-form/jquery.form.min.js') !!}

{!! script('/assets/_thirdparty/jquery-notifyBar/js/jquery.notifyBar.js') !!}
{!! style('/assets/_thirdparty/jquery-notifyBar/css/jquery.notifyBar.css') !!}

{!! script('/assets/base/js/admin.auth.captcha.js') !!}
{!! script('/assets/base/js/admin.auth.login.js') !!}

<script type="text/javascript">
$(function() {
    // 验证码
    var captcha = new Captcha();
    captcha.get();

    $('.alert-warning button').on('click', function() {
        $(this).find('span').addClass('fa-spin');
        captcha.get(this);
        return false;
    });
});
</script>
</body>
</html>
