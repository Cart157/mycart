@extends('base::_layouts.store')

@section('title', '卖家中心 - 出售中的宝贝')

@section('breadcrumbs')
<ul class="breadcrumb">
    <li>
        <a href="/store">卖家中心</a>
    </li>
    <li>
        <a href="/store/goods">宝贝管理</a>
    </li>
    <li class="active">出售中的宝贝</li>
</ul>
@endsection

@section('nav-tabs')
<div class="space-10"></div>

<ul class="nav nav-pills">
    <li class="active">
        <a href="/store/goods">出售中的宝贝</a>
    </li>

    <li>
        <a href="/store/goods-recommend">橱窗推荐宝贝</a>
    </li>

    <li class="pull-right">
        <a href="/store/goods-trash"><i class="fa fa-trash-o"></i>宝贝回收站</a>
    </li>
</ul>
@endsection

@section('search-box')
<div class="clearfix form-actions">
    <form class="form-search" autocomplete="off">
        <div class="row form-group">
            <div class="col-xs-5">
                <label class="control-label no-padding-right">宝贝名称：</label>
                <span class="align-middle">
                    <input type="text" class="search-query input-sm" name="goods_name" value="{{ Request::input('goods_name') }}">
                </span>
            </div>
            <div class="col-xs-5">
                <label class="control-label no-padding-right">店铺分类：</label>
                <select style="width: 168px;">
                    <option value="" selected="selected" disabled="disabled" style="display: none;"></option>
                    <option value="goods">淘宝</option>
                    <option value="goods">京东</option>
                    <option value="goods">国美fdsa</option>
                </select>
            </div>
        </div>
        <div class="row form-group">
            <div class="col-xs-5">
                <label class="control-label no-padding-right">货号：</label>
                <span class="align-middle">
                    <input type="text" class="search-query input-sm" name="item_no" value="{{ Request::input('item_no') }}">
                </span>
            </div>
            <div class="col-xs-5">
                <label class="control-label no-padding-right">价格：</label>
                <span class="align-middle">
                    <input type="text" class="search-query input-sm" name="price_from" value="{{ Request::input('price_from') }}" style="width: 74px;"> 至
                    <input type="text" class="search-query input-sm" name="price_to" value="{{ Request::input('price_to') }}" style="width: 74px;">
                </span>
            </div>
        </div>
        <div class="row form-group" style="margin-bottom: 0;">
            <div class="col-xs-3 col-xs-offset-9" style="text-align: right; padding-right: 36px;">
                <!-- <span class="align-middle">清空条件</span> -->
                <button class="btn btn-xs btn-default btn-white" type="reset">
                    <i class="ace-icon fa fa-remove red"></i>
                    清空条件
                </button>
                <button class="btn btn-xs btn-default btn-white" type="submit">
                    <i class="ace-icon fa fa-search green"></i>
                    搜索
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@section('main-content')
    <div class="option">
        <!--出售中宝贝-->
        <div class="sell-goods">
            <div class="data">
                <p>共有出售中宝贝<span> {{ $data->total() }} </span>条记录</p>
                <form class="main-form" method="POST">
                {{ method_field('PUT') }}
                {{ csrf_field() }}
                <table id="simple-table" class="table table-hover table-bordered-bottom">
                    <thead>
                        <tr>
                            <th width="40"></th>
                            <th width="320" style="text-align:center">宝贝名称</th>
                            <th width="120">价格</th>
                            <th style="color:#0579c6" class="stock">
                                库存
                                @if (Request::input('order_by') == 'total_stock' && Request::input('order_mod') == 'asc')
                                <img src="/assets/_layouts/store/img/向上粗箭头.png" rel="total_stock:desc">
                                @else
                                <img src="/assets/_layouts/store/img/向下粗箭头.png" rel="total_stock:asc">
                                @endif
                            </th>
                            <th width="120" style="color:#0579c6" class="total-sales">
                                总销量
                                @if (Request::input('order_by') == 'total_sales_cnt' && Request::input('order_mod') == 'asc')
                                <img src="/assets/_layouts/store/img/向上粗箭头.png" rel="total_sales_cnt:desc">
                                @else
                                <img src="/assets/_layouts/store/img/向下粗箭头.png" rel="total_sales_cnt:asc">
                                @endif
                            </th>
                            <th width="120" style="color:#0579c6" class="release-time">
                                发布时间
                                @if (Request::input('order_by') == 'sell_time' && Request::input('order_mod') == 'asc')
                                <img src="/assets/_layouts/store/img/向上粗箭头.png" rel="sell_time:desc">
                                @else
                                <img src="/assets/_layouts/store/img/向下粗箭头.png" rel="sell_time:asc">
                                @endif
                            </th>
                            <th width="100">操作</th>
                        </tr>
                        <tr class="tools-row">
                            <th class="center">
                                <label class="pos-rel">
                                    <input type="checkbox" class="ace" id="check-all" />
                                    <span class="lbl"></span>
                                </label>
                            </th>
                            <th colspan="6">
                                <label for="check-all" style="margin-right: 10px;">
                                    全选
                                </label>
                                <button class="btn btn-xs btn-white btn-default op-delete">
                                    <i class="ace-icon fa fa-trash red2"></i>
                                    删除
                                </button>
                                <button class="btn btn-xs btn-white btn-default op-offsell">
                                    <i class="ace-icon fa fa-ban red2"></i>
                                    下架
                                </button>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $goods)
                        <tr>
                            <td class="center">
                                <label class="pos-rel">
                                <input type="checkbox" class="ace" name="id[]" value="{{ $goods->id }}">
                                <span class="lbl"></span>
                            </label>
                            </td>

                            <td>
                                <div class="media">
                                    <div class="media-left">
                                        <a href="/store/goods/{{ $goods->spu_id }}/edit">
                                            <img class="media-object" data-src="holder.js/64x64" alt="64x64" src="{{ $goods->cover_image }}" data-holder-rendered="true"
                                                style="width: 48px; height: 48px;">
                                        </a>
                                    </div>
                                    <div class="media-body">
                                        <a href="/store/goods/{{ $goods->spu_id }}/edit">{{ $goods->name }}</a>
                                    </div>
                                </div>
                            </td>
                            <td style="color:#ff6600">
                            @php
                                $sku_list = $goods->spu->sku()->where('color_id', $goods->color_id)->get();
                                if($sku_list->min('goods_price') == $sku_list->max('goods_price')) {
                                    echo $sku_list->min('goods_price');
                                } else {
                                    echo $sku_list->min('goods_price').'<br>'.'-'.'<br>'.$sku_list->max('goods_price');
                                }
                            @endphp
                            </td>
                            <td class="hidden-480">{{ $goods->total_stock }}</td>
                            <td>{{ $goods->total_sales_cnt }}</td>

                            <td class="hidden-480">
                                <span>{{ $goods->sell_time }}</span>
                            </td>

                            <td>
                                <a href="/store/goods/{{ $goods->spu_id }}/edit">编辑宝贝</a><br>
                                <a href="javascript:;" class="op-offsell">下架</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                </form>
                <div class="clearfix">
                @php
                    $parameters = ['price_from' => Request::input('price_from'),
                                   'price_to'   => Request::input('price_to'),
                                   'item_no'    => Request::input('item_no'),
                                   'goods_name' => Request::input('goods_name'),
                                   'order_by'   => Request::input('order_by'),
                                   'order_mod'  => Request::input('order_mod')]
                @endphp
                    {{ $data->appends($parameters)->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@section('head-assets-after')
<link rel="stylesheet" href="/assets/mall/css/store.goods.sell.css">
@endsection

@section('foot-assets-after')
<script src="/assets/_thirdparty/jquery-form/jquery.form.min.js"></script>
<script src="/assets/_thirdparty/jquery-toaster/jquery.toaster.js"></script>

<script src="/assets/mall/js/store.goods.sell.js"></script>
<script>
    $(function() {
        $('.page-jump .btn-link').on('click', function() {
            change_page($(this).parent().prev().val());
        });

        $('.page-jump input').on('keypress', function(event) {
            if(event.keyCode==13) {
                $(this).next().find('.btn-link').click();
            }
        });

        $('.search button[type="reset"]').on('click', function() {
            $(this).closest('form').find('input').attr('value', '');

            $(this).closest('form').find('input').val('');
            $(this).closest('form').find('select').val('');
            return false;
        });

        $('#simple-table img').on('click', function() {
            var order_by = $(this).attr('rel').split(':');
            var newUrl = change_url_arg(location.href, 'order_by', order_by[0]);
                newUrl = change_url_arg(newUrl, 'order_mod', order_by[1]);

            location.href = newUrl;
        });

        $('button.op-delete').on('click', function() {
            $(this).closest('form').find('input[name="_method"]').val('DELETE');
        });

        $('button.op-offsell').on('click', function() {
            $(this).closest('form').find('input[name="_method"]').val('PUT');
        });

        $('a.op-offsell').on('click', function() {
            $(this).closest('tr').find('input.ace[type="checkbox"]').prop("checked", true);
            $(this).closest('form').find('input[name="_method"]').val('PUT');
            $('button.op-offsell').click();
        });

        var base = new Base();
        base.initForm(window.location.href);
    });
</script>
@endsection
