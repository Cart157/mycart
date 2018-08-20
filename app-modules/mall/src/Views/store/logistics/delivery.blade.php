@extends('base::_layouts.store')

@section('main-content')
    <div class="main-content-inner">
        <div class="breadcrumbs" id="breadcrumbs">
            <ul class="breadcrumb">
                <li>
                    <a href="/store">卖家中心</a>
                </li>
                <li>
                    <a href="/store/logistics">物流管理</a>
                </li>
                <li class="active">发货</li>
            </ul>
        </div>
        <!-- /.page-content -->
    </div>
    <form method="post">
    {{ method_field('PUT') }}
	{{ csrf_field() }}
        <div class="min-content-info">
            <div class="first">
                <div class="first-confirm">
                    <span>第一步</span>
                    <span>确认收货信息及交易详情</span>
                </div>
                <div class="table">
                    <div class="top">
                        <span>订单编号：{{ $data->order_no }}</span>
                        <span>下单时间：{{ $data->created_at }}</span>
                    </div>

                    <table class="middle">
                        <tbody>
                        @foreach ($data->goods as $key => $goods)
                        @php
                        	$row = count($data->goods)
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
                                    <span>买家选择：快递</span> 备忘信息：
                                    <textarea placeholder="您可以在此输入备忘信息（仅卖家自己看见）" name="memo"></textarea>
                                </td>
                                @endif
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                    <div class="bottom" id="first-bottom">
                    @php
                    	$companies = DB::table('mall_logistics')->get();
                    	$consigners = DB::table('mall_consigner')->get();
                    	$name = $data->expansion->consignee_name;
                    	$info = json_decode($data->expansion->consignee_info);
                    	$zip_code = $info->zip_code ? $info->zip_code : '000000';
                    	$buyer_info = $info->area_info.' '.$info->address.'，'.$zip_code.'，'.$name.'，'.$info->mb_phone;
                    @endphp
                        买家收货信息：
                        <span><input type="hidden" name="area_info" value="{{ $info->area_info }}">{{ $info->area_info }}</span>
                        <span><input type="hidden" name="address" value="{{ $info->address }}">{{ $info->address }}</span>，
                        <span><input type="hidden" name="zip_code" value="{{ $zip_code }}">{{ $zip_code }}</span>，
                        <span><input type="hidden" name="name" value="{{ $name }}">{{ $name }}</span>，
                        <span><input type="hidden" name="mb_phone" value="{{ $info->mb_phone }}">{{ $info->mb_phone }}</span>
                        <span><input type="hidden" name="area_code" value="{{ $info->area_code }}"></span>
                        <a href="javascript:;" class="first-revise">修改收货信息</a>
                    </div>
                    <div class="modify hide">
                        <p>
                            收货地址：<select class="province">
                                        <!--<option value="000000" style="color:#999;">-请选择省-</option> -->
                                    </select>
                                    <select class="city">
                                        <!--<option value="000000" style="color:#999;">-请选择市-</option> -->
                                    </select>
                                    <select class="district" id="district">
                                        <!--<option value="000000" style="color:#999;">-请选择区-</option> -->
                                    </select>
                                    <span><input type="text"></span>
                        </p>
                        <p>&emsp;联系人：<input type="text"></p>
                        <p class="phone">电话号码：<input type="number"><input type="number"><input type="number"></p>
                        <p>手机号码：<input type="number"></p>
                        <p>邮政编码：<input type="number"></p>
                        <span class="confirm">确认</span>
                        <i>X</i>
                    </div>
                </div>
            </div>
            <div class="second">
                <div class="second-confirm">
                    <span>第二步</span>
                    <span>确认发货/退货信息</span>
                </div>
                <div class="second-table">
                    <div class="top" id="second-bottom">
                        我的发货信息：
                    <select name="consigner_delivery_id">
                    @foreach($consigners as $value)
                    	<option value="{{ $value->id }}">{{ $value->area_info.' '.$value->address.'，'.$value->zip_code.'，'.$value->name.'，'.$value->mb_phone }}</option>
                    @endforeach
                    </select>
                    </div>
                    <div class="bottom" id="third-bottom">
                        我的退货信息：
                    <select name="consigner_refund_id">
                    @foreach($consigners as $value)
                    	<option value="{{ $value->id }}">{{ $value->area_info.' '.$value->address.'，'.$value->zip_code.'，'.$value->name.'，'.$value->mb_phone }}</option>
                    @endforeach
                    </select>
                    </div>
                </div>
            </div>
            <div class="third">
                <div class="third-confirm">
                    <span>第三步</span>
                    <span>选择物流服务</span>
                    <a href="javascript:;">什么是上门取件</a>
                    <span class="care">（您交易发生的地区支持以下物流方式）</span>
                    <a href="javascript:;">过去三个月中，派送过此收货地址的物流公司列表</a>
                </div>
                <div class="express">
                    <select name="logistics_company_id">
                    @foreach($companies as $value)
                    	<option value="{{ $value->id }}">{{ $value->name }}</option>
                    @endforeach
                    </select>
                </div>
                <div class="number">
                    <input type="number" placeholder="请输入单号" name="waybill_no">
                    <button type="submit">发货</button>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('head-assets-after')
<link rel="stylesheet" href="/assets/mall/css/store.logistics.delivery.css">
@endsection

@section('foot-assets-after')
<script src="/assets/mall/js/store.logistics.delivery.js"></script>
@endsection
