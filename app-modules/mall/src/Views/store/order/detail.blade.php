@extends('base::_layouts.store')

@section('main-content')
    <!--面包屑-->
    <div class="main-content-inner">
        <div class="breadcrumbs" id="breadcrumbs">
            <ul class="breadcrumb">
                <li>
                    <a href="/store">卖家中心</a>
                </li>
                <li>
                    <a href="/store/order#">订单</a>
                </li>
                <li class="active">详情</li>
            </ul>
        </div>
        <!-- /.page-content -->

        <div class="state">
            <div class="state-info">
                <p>当前订单状态：<span>
				@if($data->refund_status == 1)
					售后处理中
				@elseif($data->order_status == 0)
					交易关闭</span></p>
					<!-- <p class="cancel">取消原因：<span>我不想买了</span></p> -->
				@elseif($data->order_status == 10)
					等待买家付款</span></p>
				@elseif($data->order_status == 20)
					买家已付款</span></p>
				@elseif($data->order_status == 30)
					卖家已发货</span></p>
				@elseif($data->order_status == 40)
					交易成功</span></p>
				@endif
            </div>
        </div>
        <div class="order">
            <div class="order-info">订单信息</div>
            <div class="info">
                <div class="buy-info">
                @php
                	$name = $data->expansion->consignee_name;
                	$promotion = $data->expansion->promotion_amount;
                	$info = json_decode($data->expansion->consignee_info);
                	$row = count($data->goods);
                @endphp
                    <p>买家信息</p>
                    <span>昵称：{{ $data->user->name }}</span>
                    <span>姓名：{{ $data->expansion->consignee_name }}</span><br>
                    <span>城市：{{ $info->area_info }}</span>
                    <span>联系电话：{{ $info->mb_phone }}</span>
                </div>
                <div class="order-mes">
                    <p >订单信息</p>
                    <div class="order-mes-top">
                        <div class="left">
                            <span>订单编号：{{ $data->order_no }}</span>
                            <span>付款方式：支付宝</span>
                            <span>发货时间：{{ $data->deliver_at }}</span>
                        </div>
                        <div class="middle">
                            <span>创建时间：{{ $data->created_at }}</span>
                            <span>支付宝/微信流水号：
                            @if($data->order_status >= 20)
                            	{{ $data->payNo->payment_trade_no }}
                            @endif
                            </span>
                        </div>
                        <div class="right">
                            <span>付款时间：{{ $data->payNo->pay_time }}</span>
                            <span>成交时间：{{ $data->finished_at }}</span>
                        </div>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>宝贝</th>
                                <th>宝贝属性</th>
                                <th>状态</th>
                                <th>服务保障</th>
                                <th>单价</th>
                                <th>数量</th>
                                <th>优惠</th>
                                <th>商品总价</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($data->goods as $key => $goods)
                            <tr>
                                <td>
                                    <img src="{{ $goods->goods_image }}">
                                    <span>{{ $goods->goods_name }}</span>
                                </td>
                                <td>
                                    <span>{{ $goods->goods_spec }}</span>
                                </td>
                                <td>
                                    <span>
                                    	@if($data->order_status == 0)
                                    		交易关闭</span></p>
                                    	@elseif($data->order_status == 10)
                                    		等待买家付款</span></p>
                                    	@elseif($data->order_status == 20)
                                    		买家已付款</span></p>
                                    	@elseif($data->order_status == 30)
                                    		卖家已发货</span></p>
                                    	@elseif($data->order_status == 40)
                                    		交易成功</span></p>
                                    	@endif
                                    </span>
                                </td>
                                <td>

                                </td>
                                <td>
                                    <span>{{ $goods->goods_price }}</span>
                                </td>
                                <td>
                                    <span>{{ $goods->goods_num }}</span>
                                </td>
                                @if($key == 0)
                                <td rowspan="{{ $row }}">
									{{ $promotion }}
                                </td>
                                <td rowspan="{{ $row }}">
                                    <span>{{ $data->goods_amount }}</span>
                                    <span>（运费:{{ $data->freight }}）</span>
                                </td>
                                @endif
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <div class="gathering">
                        实收款：<span>￥{{ $data->order_amount }}</span> 元
                    </div>
                </div>
                <div class="logistics-info">
                    <p>物流信息</p>
                    <table>
                        <tbody>
                            <tr>
                                <td>收货地址：</td>
                                <td>{{ $name.','.$info->mb_phone.','.$info->area_info.' '.$info->address }}</td>
                            </tr>
                            <tr>
                                <td>运送方式：</td>
                                <td>快递</td>
                            </tr>
                            <tr>
                                <td>物流公司：</td>
                                <td>
                                @if($data->order_status >= 30)
                                	{{ $data->logistics->name }}
                                @endif
                                </td>
                            </tr>
                            <tr>
                                <td>运单号码：</td>
                                <td>{{ $data->waybill_no }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <span><a href="http://www.kuaidi100.com">查看物流信息</a></span>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('head-assets-after')
<link rel="stylesheet" href="/assets/mall/css/store.order.details.css">
@endsection

@section('foot-assets-after')
<script src="/assets/mall/js/store.order.details.js"></script>
@endsection
