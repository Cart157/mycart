@extends('base::_layouts.store')

@section('title', '卖家中心 - 退款售后管理')

@section('breadcrumbs')
<ul class="breadcrumb">
    <li>
        <a href="/store">卖家中心</a>
    </li>
    <li>
        <a href="#">交易管理</a>
    </li>
    <li>
        退款售后管理
    </li>
</ul>
@endsection

@section('main-content')
    <div class="refundManage">
        <div class="refund">
            <p>退款管理</p>
        </div>
        <!--提示-->
        <div class="prompt">
            <span>如果您搜索不到历史数据，请尝试点击<a href="javascript:;">历史记录</a>进行查询</span>
        </div>
        <!--未完结-->
        <div class="end">
            <div class="left">
                <span>未完结</span>
                <p>{{ $count['not_finished'] }}</p>
            </div>
            <div class="middle">
                <p>三个月未完结：<i>{{ $count['not_finished'] }}</i></p>
                <p><img src="/assets/_layouts/store/img/rectangle1.png"><span>退款待处理：{{ $count['wait_deal'] }}</span><img src="/assets/_layouts/store/img/rectangle1.png"><span>待买家发货：{{ $count['wait_buyer_delivery'] }}</span></p>
                <p><img src="/assets/_layouts/store/img/rectangle2.png"><span>待商家发货：{{ $count['wait_seller_delivery'] }}</span><img src="/assets/_layouts/store/img/rectangle2.png"><span>商家已拒绝：{{ $count['refused'] }}</span></p>
            </div>
        </div>
        <!--搜索-->
        <div class="search">
            <form>
                <span>买家昵称：<input type="text" placeholder="请填写买家昵称" name="user_name"></span>
                <span>订单编号：<input type="text" placeholder="请填写订单编号" name="order_no"></span>
                <span>退款编号：<input type="text" placeholder="请填写退款编号" name="id"></span>
                <span>退款时间：<select name="finished_at">
                                    <option value="">全部</option>
                                    <option value="recent">最近申请</option>
                                    <option value="month">最近一月</option>
                                    <option value="half_year">最近半年</option>
                                </select>
                </span>
                <span class="applyTime">申请时间：<input type="text" class="timeOne" name="created_from">至：<input type="text" class="timeTwo" name="created_to"></span>
                <span class="applyTime">修改时间：<input type="text" class="timeThree" name="updated_from">至：<input type="text" class="timeFour" name="updated_to"></span>
                <span>运单编号：<input type="text" placeholder="请填写退款运单号" name="waybill_no"></span>
                <span class="applyTime">退款金额：<input type="text" name="refund_amount_from">至：<input type="text" name="refund_amount_to"></span>
                <span><button>确定</button></span>
            </form>
        </div>
        <!--tab部分-->
        <div class="type">
            <ul>
                <li><a href="/store/refund" {!! !Request::has('refund_type') && !Request::has('order_status') ? ' class="li-style"' : '' !!}>全部订单</a></li>
                <li><a href="/store/refund?refund_type=1&order_status=20" {!! Request::input('refund_type') == 1 && Request::input('order_status') == 20 ? ' class="li-style"' : '' !!}>仅退款(未发货)</a></li>
                <li><a href="/store/refund?refund_type=1&order_status=31" {!! Request::input('refund_type') == 1 && Request::input('order_status') == 31 ? ' class="li-style"' : '' !!}>仅退款(已发货)</a></li>
                <li><a href="/store/refund?refund_type=2" {!! Request::input('refund_type') == 2 ? ' class="li-style"' : '' !!}>退货(已发货)</a></li>
                <li><a href="javascript:;">维修</a></li>
                <li><a href="/store/refund?refund_type=3" {!! Request::input('refund_type') == 3 ? ' class="li-style"' : '' !!}>换货</a></li>
                <li><a href="javascript:;">补给</a></li>
                <li class="last-li">
                    <a href="javascript:;">导出Excel</a>
                    <a href="javascript:;">管理地址模板</a>
                </li>
            </ul>
            <div class="typeContent">
                <div class="all">
                    <form>
                        <span>退款类型：
                            <select name="sale_type">
                                <option value="">全部</option>
                                <option value="sale">售中退款</option>
                                <option value="after-sale">售后退款</option>
                            </select>
                        </span>
                        <span>退款状态：
                            <select name="refund_status">
                                <option value="">全部</option>
                                <option value="39">进行中的订单</option>
                                <option value="10">退款待处理</option>
                                <option value="20">待买家发货</option>
                                <option value="30">待商家收货</option>
                                <option value="0">已拒绝退款/换货</option>
                                <option value="40">已完成退款/换货</option>
                            </select>
                        </span>
                        <span>小二介入：
                            <select>
                                <option value="全部">全部</option>
                            </select>
                        </span>
            <input type="hidden" name="refund_type" value="{{ Request::input('refund_type') }}">
                        <input type="hidden" name="order_status" value="{{ Request::input('order_status') }}">
                        <span><button>确定</button></span>
                    </form>
                </div>
            </div>
        </div>
        <!--table部分-->
        <div class="table">
            <table class="all">
                <thead>
                    <tr>
                        <th>宝贝</th>
                        <th>交易金额</th>
                        <th>退款金额</th>
                        <th>最近申请时间</th>
                        <th>原因</th>
                        <th>退货物流</th>
                        <th>发货物流</th>
                        <th>退款状态</th>
                    </tr>
                </thead>
                @foreach($data as $refund)
                    <tbody>
                        <tr class="trOne">
                            <td colspan="8">
                                <span>
                                    @if($refund->order->order_status == 40)
                                        <i style="color: red; font-style:normal">(售后)</i>
                                    @else
                                        (售中)
                                    @endif
                                </span>
                                <span>退款编号：{{ $refund->id }}</span>
                                <span>订单号：{{ $refund->order->order_no }}</span>
                                <span>商品编号：{{ $refund->goods_id }}</span>
                                <span>买家昵称：{{ $refund->user->name }}</span>
                                <span><img src="/assets/_layouts/store/img/给我留言.png"></span>
                            </td>
                        </tr>
                        <tr class="trTwo">
                            <td>
                                @php
                                    $goods = $refund->orderGoods()->where('goods_id', $refund->goods_id)->first();
                                    if (!$goods) {
                                        continue;
                                    }
                                @endphp
                                <img src="{{ $goods->goods_image }}">
                                <span>{{ $goods->goods_name }}</span>
                            </td>
                            <td>
                                <p>￥{{ $refund->order->order_amount }}</p>
                                <p>【运】</p>
                            </td>
                            <td>￥{{ $refund->refund_amount }}</td>
                            <td>{{ $refund->created_at }}</td>
                            <td>{{ $refund->reason }}</td>
                            <td>
                                @if($refund->detail()->where('option_type', 2)->first())
                                    {{ $refund->detail()->where('option_type', 2)->first()->refund_waybill_no }}
                                @endif
                            </td>
                            <td>
                                @if($refund->order->order_status == 20)
                                    待发货
                                @elseif($refund->order->order_status == 30)
                                    已发货
                                @elseif($refund->order->order_status == 40)
                                    已收货
                                @endif
                            </td>
                            <td>
                                <a href="/store/refund/{{ $refund->id }}/edit">
                                @if($refund->refund_status == 10)
                                    @if($refund->refund_type == 3)
                                        换货待处理</a>
                                    @elseif($refund->refund_type == 2)
                                        退货退款待处理</a>
                                    @else
                                        退款待处理</a>
                                    @endif
                                @elseif($refund->refund_status == 20)
                                    待买家发货</a>
                                @elseif($refund->refund_status == 30)
                                    待商家家收货</a>
                                @elseif($refund->refund_status == 40)
                                    @if($refund->refund_type == 3)
                                        换货已完成
                                    @elseif($refund->refund_type == 2)
                                        退货退款已完成
                                    @else
                                        退款已完成
                                    @endif
                                @elseif($refund->refund_status == 0)
                                    商家已拒绝
                                @endif
                            </td>
                        </tr>
                    </tbody>
                @endforeach
            </table>
        </div>
    </div>
@endsection

@section('head-assets-after')
<link rel="stylesheet" href="/assets/mall/css/store.refund.afterSales.css">
@endsection

@section('foot-assets-after')
<script src="/assets/_thirdparty/laydate/laydate.js"></script>
<script src="/assets/mall/js/store.refund.afterSales.js"></script>
@endsection
