<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>登录 - BAN</title>
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
<body class="hold-transition login-page">
<div class="login-box">
  <div class="login-logo">
    <a href="/"><b>Ban</b> Sneaker</a>
  </div>
  <!-- /.login-logo二维码已被扫描<br>等待确认... -->

  <div class="login-qrcode" style="text-align: center;">
    <div class="qrcode-img" style="width: 150px; height: 150px; margin: 0 auto 15px; box-shadow: 0 0 5px rgba(0,0,0,.3); position: relative;">
        {!! $qr_code !!}
        <div class="qrcode-alert" style="width: 150px; height: 150px; position: absolute; top: 0;background: rgba(0,0,0,.7); color: #fff; display: none;">
          <span style="align-self: center; width: 100%;"></span>
        </div>
    </div>
    <p class="text-warning">请使用 app 进行扫描登录</p>
  </div>
</div>
<!-- /.login-box -->

<!-- jQuery 2.2.3 -->
<script src="/assets/third-party/jquery2/jquery-2.1.1.min.js"></script>
<!-- Bootstrap 3.3.6 -->
<script src="/assets/third-party/bootstrap3/js/bootstrap.min.js"></script>
<script src="/assets/base.js"></script>
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

    // alert(111);
    var unqid = '{{ $md5_uniqid }}';
    var query = setInterval(function() {
      $.post('/qrlogin/query', {"uuid": unqid, "from": "uhome"}, function(res) {
        console.log(res);
        if (res.status != 200) {
          clearInterval(query);
          $('.qrcode-alert span').html(res.message);
          $('.qrcode-alert').css('display', 'flex');
        }
      });
    }, 4000);

    setTimeout(function() {
      clearInterval(query);
    }, 100000);
</script>

<script src="/assets/third-party/jquery-form/jquery.form.min.js"></script>
<script src="/assets/third-party/jquery-toaster/jquery.toaster.js"></script>
<!-- Page script -->
<script>
  $(function () {
    var base = new Base();
    base.initForm();
  });
</script>
</body>
</html>
