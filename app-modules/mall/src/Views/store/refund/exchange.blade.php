@extends('base::_layouts.store')

@section('title', '卖家中心 - 退款售后管理 - 处理换货申请')

@section('main-content')
    <!--面包屑-->
    <div class="main-content-inner">
        <div class="breadcrumbs" id="breadcrumbs">
            <ul class="breadcrumb">
                <li>
                    <a href="#">交易管理</a>
                </li>
                <li>
                   <a href="/store/refund">退款售后管理</a>
                </li>
                <li>
                    处理换货申请
                </li>
            </ul>
        </div>
    </div>
    <div class="exchange">
        <div class="title">
            <ul>
                <li class="first li-already"><span></span>买家申请换货</li>
                <li class="second {!! $data->refund_status == 10 ? 'li-now' : 'li-already' !!}"><span></span>卖家处理换货申请</li>
                <li class="third
                @if($data->refund_status == 20 || $data->refund_status == 30)
                    li-now
                @elseif(count($data->detail) == 3)
                    li-already
                @endif
                "><span></span>买家退货</li>
                <li class="fourth {!! $data->refund_status == 0 || $data->refund_status == 40 ? 'li-now' : '' !!}"><span></span>{!! $data->refund_status == 0 ? '已拒绝换货' : '换货完毕' !!}</li>
            </ul>
        </div>
        <div class="content clearfix">
            <!--左边内容容器-->
            <div class="container">
                <div class="inside">
                    <div class="left {!! $data->refund_status != 10 ? 'hide' : '' !!}">
                        <!--提醒部分-->
                        <div class="handleApply">
                            <p class="leftFirst">请处理换货申请</p>
                            <p>请及时通过电话联系买家核实详情</p>
                            <p>妥善处理售后服务</p>
                            <p>如买家有特殊要求请及时处理</p>
                        </div>
                        <!--操作-->
                        <div class="handle">
                            <button class="leftFirstButton">同意换货申请</button>
                            <button class="leftSecondButton">拒绝换货申请</button>
                        </div>
                    </div>
                    <!--拒绝换货申请-->
                    <div class="leftRefuse hide">
                        <div class="refuse">
                            <form method="post" action="/store/refund/{{ $data->id }}/reject" >
                                {{ method_field('PUT') }}
                                {{ csrf_field() }}
                                <p>拒绝原因：<select name="reject_reason">
                                                <option value="请选择拒绝原因">请选择拒绝原因</option>
                                                <option value="商品已经影响二次销售">商品已经影响二次销售</option>
                                                <option value="申请时间已超售后服务时限">申请时间已超售后服务时限</option>
                                                <option value="其他">其他</option>
                                                <option value="请承担发货运费，商品发出时完好">请承担发货运费，商品发出时完好</option>
                                            </select>
                                </p>
                                <p>拒绝说明：<textarea name="seller_description" placeholder="请填入拒绝说明"></textarea></p>
                                <div class="upload">上传凭证：
                                    <div class="form-group">
                                        <div style="display:inline-block;margin-left:10px; vertical-align:middle;">
                                            <div class="file-group">
                                                <button type="button" class="btn btn-default btn-xs">
                                                    <i class="fa fa-cloud-upload"></i> 选择要上传的文件
                                                </button>
                                                <input id="" type="file" class="image-data" multiple>
                                            </div>
                                        </div>
                                        <div id="" class="image-list" class="ui-sortable">

                                        </div>
                                    </div>
                                </div>
                                <div class="button">
                                    <input type="hidden" name="option_type" value="1">
                                    <input class="firstButton" type="submit" value="拒绝申请">
                                    <input class="secondButton" type="button" value="取消并返回"/>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!--拒绝换货-->
                    <div class="leftRefuseAgain hide">
                        <div class="refuse">
                            <form method="post" action="/store/refund/{{ $data->id }}/reject" >
                        	{{ method_field('PUT') }}
                                {{ csrf_field() }}
                                <p>拒绝原因：<select name="reject_reason">
                                                <option value="请选择拒绝原因">请选择拒绝原因</option>
                                                <option value="商品已经影响二次销售">商品已经影响二次销售</option>
                                                <option value="申请时间已超售后服务时限">申请时间已超售后服务时限</option>
                                                <option value="其他">其他</option>
                                                <option value="请承担发货运费，商品发出时完好">请承担发货运费，商品发出时完好</option>
                                            </select>
                                </p>
                                <p>拒绝说明：<textarea name="seller_description" placeholder="请填入拒绝说明"></textarea></p>
                                <div class="upload">上传凭证：
                                    <div class="form-group">
                                        <div style="display:inline-block;margin-left:10px; vertical-align:middle;">
                                            <div class="file-group">
                                                <button type="button" class="btn btn-default btn-xs">
                                                    <i class="fa fa-cloud-upload"></i> 选择要上传的文件
                                                </button>
                                                <input id="" type="file" class="image-data" multiple>
                                            </div>
                                        </div>
                                        <div id="" class="image-list" class="ui-sortable">

                                        </div>
                                    </div>
                                </div>
                                <div class="button">
                                    <input type="hidden" name="option_type" value="3">
                                    <input class="firstButton" type="submit" value="拒绝申请">
                                    <input class="secondButton" type="button" value="取消并返回"/>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- 换货成功 -->
                    <div class="leftExchangeSuccess {!! $data->refund_status == 40 ? '' : 'hide' !!}">
                        <div class="exchangeSuccess">
                            <p class="successTitle">退货退款款完成</p>
                            <p>换货成功时间：{{ $data->finished_at }}</p>
                            <p>新订单号：{{ $data->new_order_id }}</p>
                        </div>
                    </div>
                    <!--已拒绝换货-->
                    <div class="leftExchangeFail {!! $data->refund_status == 0 ? '' : 'hide' !!}">
                        <div class="exchangeFail">
                            <p class="failTitle">已拒绝换货</p>
                            @if($data->refund_status == 0 && count($data->detail) == 1)
                            <p>拒绝换货时间：{{ $data->finished_at }}</p>
                            <p>拒绝原因：{{ $data->detail()->where('option_type', 1)->first()->reject_reason }}</p>
                            <p>拒绝说明：{{ $data->detail()->where('option_type', 1)->first()->seller_description }}</p>
                            @elseif($data->refund_status == 0 && count($data->detail) == 3)
                            <p>拒绝换货时间：{{ $data->finished_at }}</p>
                            <p>拒绝原因：{{ $data->detail()->where('option_type', 3)->first()->reject_reason }}</p>
                            <p>拒绝说明：{{ $data->detail()->where('option_type', 3)->first()->seller_description }}</p>
                            @endif
                        </div>
                    </div>
                    <!--同意换货申请-->
                    <div class="leftPromise hide">
                        <div class="promise">
                            <form method="post" action="/store/refund/{{ $data->id }}/agree-apply" >
                            {{ method_field('PUT') }}
                            {{ csrf_field() }}
                                <div class="returnAddress clearfix">
                                    @php
                                        $consigners = DB::table('mall_consigner')->get();
                                    @endphp
                                    <p class="returnGoods">退货地址：
                                        <select name="consigner_refund_id">
                                        @foreach($consigners as $value)
                                            <option value="{{ $value->id }}">{{ $value->area_info.' '.$value->address.'，'.$value->zip_code.'，'.$value->name.'，'.$value->mb_phone }}</option>
                                        @endforeach
                                        </select>
                                    </p>
                                </div>
                                <a href="javascript:;">管理退货地址</a>
                                <p>退货说明：<textarea name="seller_description" placeholder="请填入退货说明"></textarea></p>
                                <div class="button">
                                    <input type="submit" class="firstButton" value="同意退货">
                                    <input type="button" class="secondButton" value="取消并返回">
                                </div>
                            </form>
                        </div>
                    </div>
                    <!--同意换货-->
                    <div class="leftPromiseAgain hide">
                        <div class="promise">
                            <div class="returnAddress clearfix">
                                <p class="returnGoods">退货地址：</p>
                                <div class="address">
                                    <div class="takeName">收货人：<span>高翔宇 13J73Q888AA</span></div>
                                    <div class="takeAddress">收货地址：<span>天津天津市西青区姚村景福花园仁德里7号楼4门</span></div>
                                    <img src="/assets/_layouts/store/img/下拉.png">
                                </div>
                            </div>
                            <a href="javascript:;">管理退货地址</a>
                            <p>退货说明：<textarea name="" placeholder="请填入退货说明"></textarea></p>
                            <div class="button">
                                <button class="firstButton">同意退货</button>
                                <button class="secondButton">取消并返回</button>
                            </div>
                        </div>
                    </div>
                    <!--等待买家发货-->
                    <div class="leftWaitBuyers {!! $data->refund_status == 20 ? '' : 'hide' !!}">
                        <div class="waitBuyers">
                            <p class="waitTitle">请等待买家退货</p>
                            <p>请及时通过电话联系买家核实详情</p>
                            <p>妥善处理售后服务</p>
                            <p>如买家有特殊要求请及时处理</p>
                        </div>
                    </div>
                    <!--商家待确认收货-->
                    <div class="leftConfirmGoods {!! $data->refund_status == 30 ? '' : 'hide' !!}">
                        @php
                            $type_info = $data->detail()->where('option_type', 2)->first()
                        @endphp
                        <div class="confirmGoods">
                            <p class="confirTitle">请确认收货</p>
                            <p>买家已退货，收到买家退货时，请验货后同意换货</p>
                        </div>
                        @if($data->refund_status == 30)
                        <div class="buyersAlready">
                            <p class="buyersTitle">买家已退货：{{ $type_info->refund_waybill_no }}（{{ $type_info->refund_logistics_company }}）<a href="javascript:;">查看物流详情</a></p>
                            <p>请及时通过电话联系买家核实详情</p>
                            <p>妥善处理售后服务</p>
                            <p>如买家有特殊要求请及时处理</p>
                            <div class="button">
                                <button class="firstButton">确认收货，同意换货</button>
                                <button class="secondButton">拒绝换货</button>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                <!--协商历史-->
                <div class="consultHistory">
                    <p class="consult">协商历史</p>

                    @if($data->refund_status == 40)
                    <div class="info">
                        <img src="" class="userImg">
                        <p>客服 <span>{{ $data->finished_at }}</span></p>
                        <p>客服同意换货，新订单号：{{ $data->new_order_id }}元</p>
                    </div>
                    <div class="info">
                        <img src="" class="userImg">
                        <p>客服 <span>{{ $data->finished_at }}</span></p>
                        <p>客服确认收货</p>
                    </div>
                    @elseif($data->refund_status == 0)
                    <div class="info">
                        <img src="" class="userImg">
                        <p>客服 <span>{{ $data->finished_at }}</span></p>
                        <p>客服拒绝换货</p>
                    </div>
                    @endif

                    @if($data->refund_status >= 30 || count($data->detail) == 3)
                    @php
                        $option_info_2 = $data->detail()->where('option_type', 2)->first()
                    @endphp
                    <div class="info">
                        <img src="{{ $data->user->profile->avatar }}" class="userImg">
                        <p>{{ $data->user->name }} <span>{{ $data->detail->where('option_type', 2)->first()->created_at }}</span></p>
                        <p>买家退货。物流公司：{{ $option_info_2->refund_logistics_company }}，物流单号：{{ $option_info_2->refund_waybill_no }}，快递方式：快递，退货说明：{{ $option_info_2->buyer_description }}</p>
                    </div>
                    @endif

                    @if($data->refund_status >= 20 || count($data->detail) == 3)
                    @php
                        $option_info_1 = $data->detail()->where('option_type', 1)->first()
                    @endphp
                    <div class="info">
                        <img src="" class="userImg">
                        <p>客服 <span>{{ $option_info_1->created_at }}</span></p>
                        <p>客服确认收货地址：{{ $option_info_1->consigner->name.'，'.$option_info_1->consigner->mb_phone.'，'.$option_info_1->consigner->area_info.' '.$option_info_1->consigner->address.'，'.$option_info_1->consigner->zip_code }}。说明：{{ $option_info_1->seller_description }}</p>
                    </div>
                    <div class="info">
                        <img src="" class="userImg">
                        <p>客服 <span>{{ $option_info_1->created_at }}</span></p>
                        <p>客服同意了本次售后服务申请</p>
                    </div>
                    @endif

                    <div class="info">
                        <img src="{{ $data->user->profile->avatar }}" class="userImg">
                        <p>{{ $data->user->name }} <span>{{ $data->created_at }}</span></p>
                        <p>发起了换货申请，货物状态：已收到货，原因：{{ $data->reason }}，说明：{{ $data->description ? $data->description : '无' }}</p>
                    </div>
                </div>
                <!--客服留言-->
                <div class="service">
                    <span>客服留言：</span>
                    <textarea data-content="{{ $data->id }}" placeholder="请输入">{{ $data->seller_remark }}</textarea>
                    <button>确定</button>
                </div>
            </div>
            <!--右边-->
            <div class="right">
                <div class="details">
                    退款详情
                </div>
                <div class="goodsInfoOne clearfix">
                    @php
                        $goods = $data->orderGoods()->where('goods_id', $data->goods_id)->first()
                    @endphp
                    <img src="{{ $goods->goods_image }}">
                    <p>{{ $goods->goods_name }}</p>
                </div>
                <div class="goodsInfoTwo">
                    <p><span>买&emsp;&emsp;家：</span>{{ $data->user->name }} <a href="#"><img src="/assets/_layouts/store/img/message.png"></a></p>
                    <p><span>订单编号：</span>{{ $data->order->order_no }}</p>
                    <p><span>成交时间：</span>{{ $data->order->finished_at }}</p>
                    <p><span>单&emsp;&emsp;价：</span>¥{{ $goods->goods_price }}*{{ $goods->goods_num }}</p>
                    <p><span>邮&emsp;&emsp;费：</span>¥{{ $data->order->freight }}</p>
                    <p><span>商品总价：</span>¥{{ $goods->goods_price * $goods->goods_num }}</p>
                </div>
                <div class="goodsInfoThree">
                    <p><span>服务单号：</span>{{ $data->id }}</p>
                    <p><span>退款金额：</span>¥{{ $data->refund_amount }}</p>
                    <p><span>原&emsp;&emsp;因：</span>{{ $data->reason }}</p>
                    <p><span>要&emsp;&emsp;求：</span>换货</p>
                    <p><span>买家留言：</span>{{ $data->description }}</p>
                </div>
                <div class="goodsInfoFour">
                    <p>买家收货信息</p>
                    @php
                        $name = $data->order->expansion->consignee_name;
                        $info = json_decode($data->order->expansion->consignee_info);
                    @endphp
                    <p><span>收货人：</span>{{ $name }}</p>
                    <p><span>联系电话：</span>{{ $info->mb_phone }}</p>
                    <p><span>收货地址：</span>{{ $info->area_info . ' ' . $info->address }}</p>
                </div>
            </div>
        </div>
        <div class="oftenProblem">
            <p class="problemTitle">常见问题</p>
            <p class="smallTitle">1. 买家一直申请退款或者售后怎么办？</p>
            <p class="problemContent">需核实。。。。<a href="javascript:;">查看更多</a></p>
            <p class="smallTitle">1. 收到买家退货破损、使用，影响2次销售怎么办？</p>
            <p class="problemContent">建议第一。。。<a href="javascript:;">查看更多</a></p>
        </div>
    </div>
@endsection

@section('head-assets-after')
<link rel="stylesheet" href="/assets/mall/css/store.refund.common.css">
@endsection

@section('foot-assets-after')
<script src="/assets/mall/js/store.refund.common.js"></script>
<script>
    $(function(){
        // 同意换货申请
        $('.inside .left .handle .leftFirstButton').on('click',function(){
            $('.inside .leftPromise').removeClass('hide').siblings().addClass('hide');
        });
        // 同意换货申请》取消并返回
        $('.leftPromise .promise .button').on('click','.secondButton',function(){
            $('.inside .left').removeClass('hide').siblings().addClass('hide');
        })
        // 确认收货》拒绝退款
        $('.leftConfirmGoods .buyersAlready .button .secondButton').on('click',function(){
            $('.inside .leftRefuseAgain').removeClass('hide').siblings().addClass('hide');
        })
        // 二次拒绝换货》取消并返回
        $('.leftRefuseAgain .refuse .button .secondButton').on('click',function(){
            $('.leftConfirmGoods').removeClass('hide').siblings().addClass('hide');
        })
        // 客服留言
        $('.service').on('click','button',function(){
            var content = $('.service textarea').val();
            var id = $('.service textarea').attr('data-content');
            var token = $('meta[name=csrf-token]')
            var newToken = token.attr('content')
            $.ajax({
                url:'/store/refund/'+id+'/remark',
                type: 'put',
                async: true,
                data:{
                    '_token':newToken,
                    'remark':content
                },
                dataType: 'json',
                success: function (res) {
                    alert('修改客服留言成功')
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    alert('修改客服留言失败')
                }
            })
        })
    })
</script>
@endsection
