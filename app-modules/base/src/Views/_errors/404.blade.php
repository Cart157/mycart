@extends('sec_base::_layout.auth')

@section('title')404页面未找到！@stop

@section('wraper-contents')
<div id="auth-box">
    @php($detect = new Mobile_Detect; $deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer'))
    <div id="auth-box-top">
        <h2 class="form-signin-heading">404 <small>页面未找到！</small></h2>
        {{ $exception->getMessage() }}
    </div>
    <div id="auth-box-bottom" class="text-right">
        <a href="/">返回网站首页</a>
    </div>
</div>
@stop
