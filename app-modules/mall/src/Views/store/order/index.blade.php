@extends('base::_layouts.store')

@section('title', '卖家中心 - 已卖出的宝贝')

@section('breadcrumbs')
<ul class="breadcrumb">
    <li>
        <a href="/store">卖家中心</a>
    </li>
    <li>
        <a href="/store/order">交易管理</a>
    </li>
    <li class="active">已卖出的宝贝</li>
</ul>
@endsection

@section('nav-tabs')
<div class="space-10"></div>

<ul class="nav nav-pills">
    <li{!! !Request::has('order_status') && !Request::has('refund_status') ? ' class="active"' : '' !!}>
        <a href="/store/order">
            所有订单
        </a>
    </li>

    <li{!! Request::input('order_status') == 10 ? ' class="active"' : '' !!}>
        <a href="/store/order?order_status=10">
            等待卖家付款
        </a>
    </li>

    <li{!! Request::input('order_status') == 20 ? ' class="active"' : '' !!}>
        <a href="/store/order?order_status=20">
            等待发货
        </a>
    </li>

    <li{!! Request::input('order_status') == 30 ? ' class="active"' : '' !!}>
        <a href="/store/order?order_status=30">
            已发货
        </a>
    </li>

    <li{!! Request::input('refund_status') == 1 ? ' class="active"' : '' !!}>
        <a href="/store/order?refund_status=1">
            退款中
        </a>
    </li>

    <li{!! Request::input('order_status') == 40 && Request::input('evaluation_status') === '1' ? ' class="active"' : '' !!}>
        <a href="/store/order?order_status=40&evaluation_status=1">
            需要评价
        </a>
    </li>

    <li{!! Request::input('order_status') == 40 && !Request::has('evaluation_status') ? ' class="active"' : '' !!}>
        <a href="/store/order?order_status=40">
            成功的订单
        </a>
    </li>

    <li{!! Request::input('order_status') === '0' ? ' class="active"' : '' !!}>
        <a href="/store/order?order_status=0">
            关闭的订单
        </a>
    </li>
</ul>
@endsection

@section('search-box')
<div class="clearfix form-actions">
    <form class="form-search" autocomplete="off">
        <div class="row form-group">
            <div class="col-xs-5">
                <label class="control-label no-padding-right">商品ID：</label>
                <span class="align-middle">
                    <input type="text" class="search-query input-sm" name="goods_name" value="{{ Request::input('goods_name') }}">
                </span>
            </div>
            <div class="col-xs-5">
                <label class="control-label no-padding-right">宝贝名称：</label>
                <span class="align-middle">
                    <input type="text" class="search-query input-sm" name="goods_name" value="{{ Request::input('goods_name') }}">
                </span>
            </div>
        </div>
        <div class="row form-group">
            <div class="col-xs-5">
                <label class="control-label no-padding-right">下单时间：</label>
                <span class="align-middle">
                    <input type="text" class="search-query input-sm" name="price_from" value="{{ Request::input('price_from') }}" style="width: 74px;"> 至
                    <input type="text" class="search-query input-sm" name="price_to" value="{{ Request::input('price_to') }}" style="width: 74px;">
                </span>
            </div>
            <div class="col-xs-5">
                <label class="control-label no-padding-right">订单编号：</label>
                <span class="align-middle">
                    <input type="text" class="search-query input-sm" name="item_no" value="{{ Request::input('item_no') }}">
                </span>
            </div>
        </div>
        <div class="row form-group">
            <div class="col-xs-5">
                <label class="control-label no-padding-right">订单状态：</label>
                <select style="width: 168px;">
                    <option value="">全部</option>
                    <option value="">近三个月订单</option>
                    <option value="10">等待买家付款</option>
                    <option value="20">等待发货</option>
                    <option value="30">已发货</option>
                    <option value="42">退款中</option>
                    <option value="41">需要评价</option>
                    <option value="40">成功的订单</option>
                    <option value="0">关闭中订单</option>
                </select>
            </div>
            <div class="col-xs-5">
                <label class="control-label no-padding-right">售后服务：</label>
                <select style="width: 168px;">
                    <option value="serve">全部</option>
                    <option value="serve">按摩</option>
                    <option value="serve">打麻将</option>
                    <option value="serve">打游戏</option>
                </select>
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

{{--<div class="search-box clearfix">
    <!--打开-->
    <div class="open">
        <span>打开搜索<img src="/assets/_layouts/store/img/下拉.png"></span>
    </div>

    <!--搜索-->
    <div class="search hide">
        <form>
            <span>商品ID：<input type="text" name="goods_id" value="{{ Request::input('goods_id') }}"></span>
            <span>宝贝名称：<input type="text" name="goods_name" value="{{ Request::input('goods_name') }}"></span>
            <span>下单时间：<input type="text" name="created_from" value="{{ Request::input('created_from') }}">-<input type="text" name="created_to" value="{{ Request::input('created_to') }}"></span>
            <span>订单编号：<input type="text" name="order_no" value="{{ Request::input('order_no') }}"></span>
            <span>
                订单状态：
                <select name="order_status">
                    <option value="">全部</option>
                    <option value="">近三个月订单</option>
                    <option value="10">等待买家付款</option>
                    <option value="20">等待发货</option>
                    <option value="30">已发货</option>
                    <option value="42">退款中</option>
                    <option value="41">需要评价</option>
                    <option value="40">成功的订单</option>
                    <option value="0">关闭中订单</option>
                </select>
            </span>
            <span>
                售后服务：
                <select>
                    <option value="serve">全部</option>
                    <option value="serve">按摩</option>
                    <option value="serve">打麻将</option>
                    <option value="serve">打游戏</option>
                </select>
            </span>

            <span><button type="reset">清空条件</button></span>
            <span><button type="submit">搜索</button></span>
        </form>
        <p>
            收起<img src="/assets/_layouts/store/img/收起.png">
        </p>
    </div>
</div>--}}
@endsection

@section('main-content')


    <!--卖出内容-->
    <div class="sold-content">
<!--         <ul>
            <li><a href="/store/order" {!! !Request::has('order_status') && !Request::has('refund_status') ? ' class="li-style"' : '' !!}>所有订单</a></li>
            <li><a href="/store/order?order_status=10" {!! Request::input('order_status') == 10 ? ' class="li-style"' : '' !!}>等待买家付款</a></li>
            <li><a href="/store/order?order_status=20" {!! Request::input('order_status') == 20 ? ' class="li-style"' : '' !!}>等待发货</a></li>
            <li><a href="/store/order?order_status=30" {!! Request::input('order_status') == 30 ? ' class="li-style"' : '' !!}>已发货</a></li>
            <li><a href="/store/order?refund_status=1" {!! Request::input('refund_status') == 1 ? ' class="li-style"' : '' !!}>退款中</a></li>
            <li><a href="/store/order?order_status=40&evaluation_status=1" {!! Request::input('order_status') == 40 && Request::input('evaluation_status') === '1' ? ' class="li-style"' : '' !!}>需要评价</a></li>
            <li><a href="/store/order?order_status=40" {!! Request::input('order_status') == 40 && !Request::has('evaluation_status') ? ' class="li-style"' : '' !!}>成功的订单</a></li>
            <li><a href="/store/order?order_status=0" {!! Request::input('order_status') === '0' ? ' class="li-style"' : '' !!}>关闭中订单</a></li>
        </ul> -->
        <div class="all">
            <div class="three-order">
                <div class="check-all">
                    <input type="checkbox" id="all"><label for="all">全选</label>
                    <button class="delivery">批量发货</button>
                    <button>批量标记</button>
                    <button class="exempt">批量免运费</button>
                    <input type="checkbox" id="no-order"><label for="no-order">不显示已关闭订单</label>
                    <span>
                        <button>上一页</button>
                        <button>下一页</button>
                    </span>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th style="width:300px">宝贝</th>
                            <th style="width:100px">单价</th>
                            <th style="width:50px">数量</th>
                            <th style="width:70px">售后</th>
                            <th style="width:120px">买家</th>
                            <th style="width:100px">交易状态</th>
                            <th style="width:120px">实收款</th>
                            <th style="width:100px">评价</th>
                        </tr>
                    </thead>
                    @foreach ($data as $order)
                    <tbody>
                        <tr>
                            <td colspan="8" class="first-td">
                                <input type="checkbox" id="time">
                                <i>订单号：{{ $order->order_no}}</i>
                                <i>下单时间：{{ $order->created_at}}</i>
                            </td>
                        </tr>

                        @foreach($order->goods as $key => $goods)
                        @php
                            $row = count($order->goods)
                        @endphp
                        <tr>
                            <td>
                                <img src="{{ $goods->goods_image }}">
                                <span>{{ $goods->goods_name }}</span>
                                <span>{{ $goods->goods_spec }}</span>
                            </td>
                            <td>￥{{ $goods->goods_price }}</td>
                            <td>{{ $goods->goods_num }}</td>
                            <td>
                            @if($order->refund_status == 1)
                                <a href="/store/refund/{{ $order->id }}">处理售后</a>
                            @endif
                            </td>

                            @if($key == 0)
                            <td rowspan="{{ $row }}">
                                <p>{{ $order->user->name }}</p>
                                <a href="javascript:;">联系买家</a>
                            </td>
                            <td rowspan="{{ $row }}">
                                <p>
                                @if($order->order_status == 0)
                                    交易关闭
                                @elseif($order->order_status == 10)
                                    等待买家付款</p>
                                    <a href="#" class="tradeClose" data-info="{{ $order->id }}">关闭交易</a><p>
                                @elseif($order->order_status == 20)
                                    买家已付款</p>
                                    <a href="/store/logistics/delivery/{{ $order->id }}">发货</a><p>
                                @elseif($order->order_status == 30)
                                    卖家已发货
                                @else
                                    交易成功
                                @endif
                                </p>
                                <a href="/store/order/{{ $order->id }}">详情</a>
                            </td>
                            <td rowspan="{{ $row }}">
                                <p class="goodsTotal" data-num="{{ $order->goods_amount }}">{{ $order->order_amount }}</p>
                                <p>(含快递：￥<span class="goodsFreight">{{ $order->freight }}</span>)</p>
                                @if($order->order_status == 10)
                                    <p><a href="#" class="revisePrice"  data-info="{{ $order->id }}">修改价格</a></p>
                                @elseif($order->order_status == 30)
                                    <p><a href="#">查看物流</a></p>
                                @endif
                            </td>
                            <td rowspan="{{ $row }}">
                                @if($order->order_status == 40 && $order->evaluation_status == 1)
                                    <a href="/store/evaluation?order_id={{ $order->id }}">查看卖家评价</a>
                                @elseif($order->order_status == 40 && $order->evaluation_status == 0)
                                    买家还未评价
                                @endif
                            </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                    @endforeach
                </table>
                <div class="clearfix">
                    {{ $data->appends(Request::all())->links() }}
                </div>
            </div>
        </div>
    </div>
    <!--模态框-->
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                        &times;
                    </button>
                    <h4 class="modal-title" id="myModalLabel">
                        关闭交易
                    </h4>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">关闭
                    </button>
                    <button type="button" class="btn btn-primary submit">
                        确定
                    </button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal -->
    </div>
    <div class="modal fade" id="reviseModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                        &times;
                    </button>
                    <h4 class="modal-title" id="myModalLabel">
                        价格修改
                    </h4>
                </div>
                <div class="modal-body">
                    商品总价：
                    <input type="text" class="total">
                    运费：
                    <input type="text" class="freight">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">关闭
                    </button>
                    <button type="button" class="btn btn-primary submit">
                        提交更改
                    </button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal -->
    </div>

@endsection

@section('head-assets-after')
<!-- <link rel="stylesheet" href="/assets/_thirdparty/animate/css/animate.css"> -->
<!-- <link rel="stylesheet" href="/assets/mall/css/store.goods.sold.css"> -->
@endsection

@section('foot-assets-after')
<!-- <script src="/assets/mall/js/store.goods.sold.js"></script> -->
<script type="text/javascript">
// $(function() {
//     // 打开
//     $(document).on('click', '.open span', function() {
//         $(this).parent().addClass('hide');
//         $('.search').removeClass('hide animated flipOutX').addClass('animated flipInX');
//     });

//     // 收起
//     $(document).on('click', '.search p', function() {
//         $(this).parent().removeClass('animated flipInX').addClass('animated flipOutX');
//         setTimeout(function(){
//             clearTimeout();
//             $('.search').addClass('hide');
//         },1000);
//         $('.open').removeClass('hide');
//     });

//     $(document).on('click', '.breadcrumb a', function() {
//         alert(123);
//         return false;
//     });
// });
</script>
@endsection
