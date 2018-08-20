@extends('sec_base::_layout.admin')
@php($active = 'setting')

@section('title', '网站设置')

@section('breadcrumbs')
<strong>网站设置</strong>
@endsection

@section('main-contents')
<div class="alert alert-info">
    key是唯一的，如果输入新key就是添加新设置项，如果输入老key就是编辑原设置
</div>

<div class="row">
    <div class="col-xs-6">
        <div class="panel panel-default">
            <div class="panel-heading">已有设置</div>
            <table class="table table-bordered table-setting">
                  <thead>
                    <tr>
                        <th>设置项</th>
                        <th>设置值</th>
                        <th>说明</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse ($result as $setting_item)
                    <tr>
                        <td class="setting-key">{{ $setting_item->key }}</td>
                        <td class="setting-value" style="word-break:break-all;">{{ $setting_item->value }}</td>
                        <td class="setting-memo">{{ $setting_item->memo }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center">暂无</td>
                    </tr>
                    @endforelse
                  </tbody>
            </table>
        </div>
    </div>

    <div class="col-xs-6">
        <form method="POST" class="main-form form-horizontal" accept-charset="UTF-8" autocomplete="off" novalidate="novalidate">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <!--设置项-->
            <div class="form-group">
                <label class="col-xs-2 control-label">设置项</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="setting_key">
                </div>
            </div>

            <!--设置值-->
            <div class="form-group">
                <label class="col-xs-2 control-label">设置值</label>
                <div class="col-sm-10">
                    <textarea class="form-control" rows="3" name="setting_value"></textarea>
                </div>
            </div>

            <!--说明-->
            <div class="form-group">
                <label class="col-xs-2 control-label">说明</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" placeholder="非必填" name="setting_memo">
                </div>
            </div>

            <div class="pull-right" style="padding: 15px;">
                <button type="submit" class="btn btn-primary btn-sm">提交保存</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('foot-assets')
{!! script('/assets/admin/js/base/site_setting.js') !!}

<script type="text/javascript">
$(function() {
    var setting = new SiteSetting();
    setting.initForm();

    $('.table-setting tbody tr').on('click', function() {
        $('.main-form input[name="setting_key"]').val($(this).find('.setting-key').text());
        $('.main-form textarea[name="setting_value"]').val($(this).find('.setting-value').text());
        $('.main-form input[name="setting_memo"]').val($(this).find('.setting-memo').text());
    })
});
</script>
@endsection
