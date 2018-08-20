<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>注册 - BAN</title>
<!-- Tell the browser to be responsive to screen width -->
<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
<!-- Bootstrap 3.3.6 -->
<link rel="stylesheet" href="/assets/third-party/bootstrap3/css/bootstrap.min.css">
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
<!-- Theme style -->
<link rel="stylesheet" href="/assets/third-party/adminLTE/css/AdminLTE.min.css">
<!-- iCheck -->
<link rel="stylesheet" href="/assets/third-party/iCheck/square/blue.css">

<style>
body {
  overflow-y: scroll;
}
</style>

<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->
</head>
<body class="hold-transition register-page">
<div class="register-box">
  <div class="register-logo">
    <a href="/"><b>Ban</b> Sneaker</a>
  </div>

  <div class="nav-tabs-custom" style="margin-bottom: 0;">
    <!-- Nav tabs -->
    <ul class="nav nav-tabs">
      @php
        $arr = [];
        if (Request::has('return_url')) {
          $arr = ['return_url' => Request::input('return_url')];
        }
      @endphp
      <li class="text-center" style="width: 50%; margin: 0;"><a href="{{ route('user.auth.login', $arr) }}">登录</a></li>
      <li class="text-center active" style="width: 50%; margin: 0;"><a href="javascript:void(0);">注册</a></li>
    </ul>
  </div>
  <div class="register-box-body">
    <form class="main-form" method="POST" accept-charset="UTF-8" autocomplete="off" novalidate="novalidate">
      <input type="hidden" name="_token" value="{{ csrf_token() }}">
      <div class="form-group has-feedback">
        <input type="text" class="form-control" placeholder="昵称（为保安全，互动时不公示）" name="nickname">
        <span class="glyphicon glyphicon-user form-control-feedback"></span>
      </div>
      <div class="form-group has-feedback">
        <input type="email" class="form-control" placeholder="邮箱" name="email">
        <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
      </div>
      <div class="form-group has-feedback">
        <input type="password" class="form-control" placeholder="密码" name="password">
        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
      </div>
      <div class="form-group has-feedback">
        <input type="password" class="form-control" placeholder="密码确认" name="password_confirmation">
        <span class="glyphicon glyphicon-ok form-control-feedback"></span>
      </div>
      <div class="form-group has-feedback">
        <input type="text" class="form-control" placeholder="验证码" name="captcha">
        <span class="glyphicon glyphicon-qrcode form-control-feedback"></span>
      </div>
      <div class="row">
        <div class="col-xs-12">
          <div class="checkbox icheck">
            <label>
              <input type="checkbox" name="accept"> 接受服务条款（<a href="#">阅读条款</a>）
            </label>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-xs-12 text-right">
          <button type="button" class="btn btn-danger btn-flat" id="send-captcha">往邮箱发送验证码</button>
          <button type="submit" class="btn btn-primary btn-flat">　注册　</button>
        </div>
      </div>
    </form>
  </div>
  <!-- /.form-box -->
</div>
<!-- /.register-box -->

<!-- jQuery 2.2.3 -->
<script src="/assets/third-party/jquery2/jquery-2.1.1.min.js"></script>
<!-- Bootstrap 3.3.6 -->
<script src="/assets/third-party/bootstrap3/js/bootstrap.min.js"></script>
<!-- iCheck -->
<script src="/assets/third-party/iCheck/icheck.min.js"></script>
<script>
  $(function () {
    $('input').iCheck({
      checkboxClass: 'icheckbox_square-blue',
      radioClass: 'iradio_square-blue',
      increaseArea: '20%' // optional
    });
  });
</script>

<script src="/assets/third-party/jquery-form/jquery.form.min.js"></script>
<script src="/assets/third-party/jquery-toaster/jquery.toaster.js"></script>
<script src="/assets/base.js"></script>

<!-- Page script -->
<script>
  $(function () {
    var base = new Base();
    base.initForm();

    $(document).on('click', '#send-captcha', function() {
        base.sendCaptcha($(this));
    });

    @if (Session::has('captcha_time') && time() - Session::get('captcha_time') < 60)
    var second = {{ 60 - (time() - Session::get('captcha_time')) }};
    base.disableCaptcha($('#send-captcha'), second);
    @endif
  });
</script>
</body>
</html>
