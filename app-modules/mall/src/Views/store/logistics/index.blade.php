@extends('base::_layouts.store')

@section('title', '卖家中心 - 宝贝回收站')

@section('breadcrumbs')
<ul class="breadcrumb">
    <li>
        <a href="/store">卖家中心</a>
    </li>
    <li>
        <a href="/store/logistics">物流管理</a>
    </li>
    <li class="active">等待发货的订单</li>
</ul>
@endsection

@section('nav-tabs')
<div class="space-10"></div>

<ul class="nav nav-pills">
    <li{!! !Request::input('delivery_status') == 1 ? ' class="active"' : '' !!}>
        <a href="/store/logistics">等待发货的订单</a>
    </li>

    <li{!! Request::input('delivery_status') == 1 ? ' class="active"' : '' !!}>
        <a href="/store/logistics?delivery_status=1">橱窗推荐宝贝</a>
    </li>
</ul>
@endsection

@section('search-box')
<div class="clearfix form-actions">
    <form class="form-search" autocomplete="off">
        <div class="row form-group">
            <div class="col-xs-4">
                <label class="control-label no-padding-right">收件人名称：</label>
                <span class="align-middle">
                    <input type="text" class="search-query input-sm" name="consignee_name" value="{{ Request::input('consignee_name') }}">
                </span>
            </div>
            <div class="col-xs-4">
                <label class="control-label no-padding-right">买家昵称：</label>
                <span class="align-middle">
                    <input type="text" class="search-query input-sm" name="user_name" value="{{ Request::input('user_name') }}">
                </span>
            </div>
            <div class="col-xs-4">
                <label class="control-label no-padding-right">创建时间：</label>
                <span class="align-middle">
                    <input type="text" class="search-query input-sm" name="created_from" value="{{ Request::input('created_from') }}" style="width: 74px;"> 至
                    <input type="text" class="search-query input-sm" name="created_to" value="{{ Request::input('created_to') }}" style="width: 74px;">
                </span>
            </div>
        </div>
        <div class="row form-group">
            <div class="col-xs-4">
                <label class="control-label no-padding-right">订单编号：</label>
                <span class="align-middle">
                    <input type="text" class="search-query input-sm" name="order_no" value="{{ Request::input('order_no') }}">
                </span>
            </div>
            <div class="col-xs-4">
                <label class="control-label no-padding-right">买家选择：</label>
                <select style="width: 168px;">
                    <option value="name" checked>包邮</option>
                    <option value="name">到付</option>
                    <option value="name">货到付款</option>
                    <option value="name">商品+邮费</option>
                </select>
            </div>
            <div class="col-xs-4">
                <label class="control-label no-padding-right">订单类型：</label>
                <select style="width: 168px;">
                    <option value="name1">正常</option>
                    <option value="name1">结后单</option>
                    <option value="name1">洗护</option>
                    <option value="name1">定制</option>
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

<!-- <div class="wait-search">
    <form>
        <span>收件人名称：<input type="text" name="consignee_name" value="{{ Request::input('consignee_name') }}"></span>
        <span>买家昵称：<input type="text" name="user_name" value="{{ Request::input('user_name') }}"></span>
        <span>创建时间：<input type="text" name="created_from" value="{{ Request::input('created_from') }}">至<input type="text" name="created_to" value="{{ Request::input('created_to') }}"></span>
        <span>订单编号：<input type="text" name="order_no" value="{{ Request::input('order_no') }}"></span>
        <span>
            买家选择：
            <select>
                <option value="name" checked>包邮</option>
                <option value="name">到付</option>
                <option value="name">货到付款</option>
                <option value="name">商品+邮费</option>
            </select>
        </span>
        <span>
            订单类型：
            <select>
                <option value="name1">正常</option>
                <option value="name1">结后单</option>
                <option value="name1">洗护</option>
                <option value="name1">定制</option>
            </select>
        </span>
        <span>
            <input type="hidden" name="delivery_status" value="{{ Request::input('delivery_status') }}">
            <button type="submit">搜索</button>
        </span>
    </form>
</div> -->
@endsection

@section('main-content')
    <div class="delivery">
        <div class="wait-delivery">
            <div class="wait-handle">
                <div class="check-all">
                    <input type="checkbox" id="wait">
                    <input type="button" value="批量发货">
                    <input type="button" value="批量打印发货单">
                    <input type="button" value="批量打印运单">
                    <span><img src="">货到付款、虚拟物品、保障速递、预约配送不支持批量发货。</span>
                </div>
                @foreach($data as $order)
                @php
                    $name = $order->expansion ? $order->expansion->consignee_name : '未知';
                    $info = $order->expansion ? json_decode($order->expansion->consignee_info) : '未知';
                    $order_remark = $order->expansion ? $order->expansion->order_remark : '未知';
                    $deliver_explain = $order->expansion ? $order->expansion->deliver_explain : '未知';
                @endphp
                    <table>
                        <tbody>
                            <tr>
                                <td colspan="2">
                                    <input type="checkbox">
                                    <span>订单编号：{{ $order->order_no }}</span>
                                    <span>创建时间：{{ $order->created_at }}</span>
                                </td>
                            </tr>
                            @foreach($order->goods as $key => $goods)
                            @php
                                $row = count($order->goods)
                            @endphp
                            <tr>
                                <td>
                                    <img src="{{ $goods->goods_image }}" alt="">
                                    <a href="javascript:;">{{ $goods->goods_name }}</a>
                                    <span>{{ $goods->goods_spec }}</span>
                                    <p>￥{{ $goods->goods_price }} x {{ $goods->goods_num }}</p>
                                </td>
                                @if($key == 0)
                                <td rowspan="{{ $row }}">
                                    <span>收货信息：{{ $info->area_info.' '.$info->address.','.$name.','.$info->mb_phone }}</span>
                                    <span>买家选择：快递</span>
                                    <span>买家备注：{{ $order_remark }}</span>
                                    <span>卖家备注：{{ $deliver_explain }}</span>
                                    @if($order->order_status != 20)
                                        <span>快递公司：{{ $order->logistics->name }}</span>
                                        <span>运单号码：{{ $order->waybill_no }}</span>
                                    @endif
                                </td>
                                @endif
                            </tr>
                            @endforeach
                            <tr>
                                <td colspan="2">
                                @if($order->order_status == 20)
                                    <input type="button" value="发货" onclick="location.href='/store/logistics/delivery/{{ $order->id }}'">
                                @else
                                    <input type="button" value="详情" onclick="location.href='/store/order/{{ $order->id }}'">
                                @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                @endforeach
            </div>
        </div>
    </div>
@endsection

@section('head-assets-after')
<link rel="stylesheet" href="/assets/mall/css/store.logistics.waitDeliver.css">
@endsection

@section('foot-assets-after')
<script src="/assets/mall/js/store.logistics.waitDeliver.js"></script>
@endsection
