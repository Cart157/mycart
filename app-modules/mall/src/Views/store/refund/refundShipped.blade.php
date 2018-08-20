@extends('base::_layouts.store')

@section('title', '卖家中心 - 退款售后管理 - 处理退款(已发货)申请')

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
                    处理退款(已发货)申请
                </li>
            </ul>
        </div>
    </div>
    <div class="exchange">
        <div class="titleOne">
            <ul>
                <li class="first li-already"><span></span>买家申请仅退款</li>
                <li class="second {!! $data->refund_status == 10 ? 'li-now' : 'li-already' !!}"><span></span>卖家处理退款申请</li>
                <li class="third {!! $data->refund_status == 10 ? '' : 'li-now' !!}"><span></span>{!! $data->refund_status == 0 ? '已拒绝退款' : '退款完成' !!}</li>
            </ul>
        </div>
        <div class="content clearfix">
            <!--左边内容容器-->
            <div class="container">
                <div class="inside">
                    <div class="left {!! $data->refund_status == 0 || $data->refund_status == 40 ? 'hide' : '' !!}">
                        <!--提醒部分-->
                        <div class="handleApply">
                            <p class="leftFirst">请处理退款申请</p>
                            <p>请及时通过电话联系买家核实详情</p>
                            <p>妥善处理售后服务</p>
                            <p>如买家有特殊要求请及时处理</p>
                        </div>
                        <!--操作-->
                        <div class="handle">
                            <button class="leftFirstButton">同意退款</button>
                            <button class="leftSecondButton">拒绝申请</button>
                        </div>
                    </div>
                    <!--拒绝退款-->
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
                    <!-- 退款成功 -->
                    <div class="leftExchangeSuccess {!! $data->refund_status == 40 ? '' : 'hide' !!}">
                        <div class="exchangeSuccess">
                            <p class="successTitle">退款完成</p>
                            <p>退款成功时间：{{ $data->finished_at }}</p>
                            <p>退款金额：¥ {{ $data->refund_amount }}元</p>
                        </div>
                    </div>
                    <!--已拒绝退款-->
                    <div class="leftExchangeFail {!! $data->refund_status == 0 ? '' : 'hide' !!}">
                        <div class="exchangeFail">
                            <p class="failTitle">已拒绝退款</p>
                            @if($data->refund_status == 0)
                            <p>拒绝退款时间：{{ $data->finished_at }}</p>
                            <p>拒绝原因：{{ $data->detail->first()->reject_reason }}</p>
                            <p>拒绝说明：{{ $data->detail->first()->seller_description }}</p>
                            @endif
                        </div>
                    </div>
                    <!--同意退款-->
                    <div class="leftPromise hide">
                        <form method="POST" action="/payment/refund" class="main-form">
                        {{ csrf_field() }}
                        <input type="hidden" name="refund_id" value="{{ $data->id }}">
                        <div class="promise">
                            <p>订单金额：{{ $data->order->order_amount }}<br>客户要求的退款金额：{{ $data->refund_amount }}</p>
                            @if($data->refund_amount >= $data->order->order_amount)
                            <p class="returnGoods">退款方式：<input type="radio" name="refund_mod" value="1" checked> 全额 <input type="radio" name="refund_mod" value="2" disabled> 部分 （备注：此处不可选，根据用户要求的金额和订单金额比较得出）</p>
                            @else
                            <p class="returnGoods">退款方式：<input type="radio" name="refund_mod" value="1" disabled> 全额 <input type="radio" name="refund_mod" value="2" checked> 部分 （备注：此处不可选，根据用户要求的金额和订单金额比较得出）</p>
                            <p class="returnGoods">
                                支付工具：
                                <select name="refund_payment">
                                    <option value="alipay">支付宝</option>
                                    <option value="wechat">微信支付</option>
                                </select>&nbsp;&nbsp;
                                本次退款交易号：
                                <input type="text" name="refund_trade_no" value="" size="51">
                            </p>
                            @endif
                            <p>退款备注：<textarea name="seller_description" placeholder="请填入退款备注" style="resize: vertical;"></textarea></p>
                            <div class="button">
                                <input type="hidden" name="option_type" value="1">
                                <input class="firstButton" type="submit" value="确认退款">
                                <input class="secondButton" type="button" value="取消并返回"/>
                            </div>
                        </div>
                        </form>
                    </div>
                </div>
                <!--协商历史-->
                <div class="consultHistory">
                    <p class="consult">协商历史</p>
                    @if($data->refund_status == 40)
                    <div class="info">
                        <img src="" class="userImg">
                        <p>客服 <span>{{ $data->finished_at }}</span></p>
                        <p>客服主动同意，退款给卖家{{ $data->refund_amount }}元</p>
                    </div>
                    @elseif($data->refund_status == 0)
                    <div class="info">
                        <img src="" class="userImg">
                        <p>客服 <span>{{ $data->finished_at }}</span></p>
                        <p>客服拒绝退款</p>
                    </div>
                    @endif
                    <div class="info">
                        <img src="{{ $data->user->profile->avatar }}" class="userImg">
                        <p>{{ $data->user->name }} <span>{{ $data->created_at }}</span></p>
                        <p>发起了退款申请，货物状态：已收到货，原因：{{ $data->reason }}，说明：{{ $data->description ? $data->description : '无' }}</p>
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
                    <p><span>要&emsp;&emsp;求：</span>退款</p>
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
<script src="/assets/_thirdparty/jquery-form/jquery.form.min.js"></script>
<script src="/assets/_thirdparty/jquery-toaster/jquery.toaster.js"></script>

<script src="/assets/mall/js/store.refund.common.js"></script>
<script>
    $(function(){
        var base = new Base();
        base.initForm('../');

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

        $('.left .handle').on('click','.leftFirstButton',function(){
            $('.inside .leftPromise').removeClass('hide').siblings().addClass('hide');
        })

        $('.leftPromise .button').on('click','.secondButton',function(){
            $('.inside .left').removeClass('hide').siblings().addClass('hide');
        })
    })
</script>
@endsection
