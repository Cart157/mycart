@extends('base::_layouts.store')

@section('title', '卖家中心 - 首页')

@section('breadcrumbs')
<ul class="breadcrumb">
    <li>
        <a href="/store">卖家中心</a>
    </li>
    <li class="active">首页</li>
</ul>
@endsection

@section('main-content')
    <div class="space-10"></div>

    <div class="data">
        <div class="time-data">
            <span>实时数据</span>
        </div>
        <ul>
            <li>
                <div class="top">
                    <img src="/assets/_layouts/store/img/支付金额.png" alt="">
                    <span>支付金额</span>
                    <span>{{ $count['paid_cnt'] }}</span>
                </div>
                <div class="bottom">
                    <span>昨日全天</span>
                    <span>{{ $order['lastday_paid_amount_cnt'] }}</span>
                </div>
            </li>
            <li>
                <div class="top">
                    <img src="/assets/_layouts/store/img/访客数.png" alt="">
                    <span>访客数</span>
                    <span>-</span>
                </div>
                <div class="bottom">
                    <span>昨日全天</span>
                    <span>-</span>
                </div>
            </li>
            <li>
                <div class="top">
                    <img src="/assets/_layouts/store/img/支付买家数.png" alt="">
                    <span>支付买家数</span>
                    <span>{{ $count['buyer_cnt'] }}</span>
                </div>
                <div class="bottom">
                    <span>昨日全天</span>
                    <span>{{ $order['lastday_paid_user_cnt'] }}</span>
                </div>
            </li>
            <li>
                <div class="top">
                    <img src="/assets/_layouts/store/img/浏览量.png" alt="">
                    <span>浏览量</span>
                    <span>{{ $count['view_cnt'] }}</span>
                </div>
                <div class="bottom">
                    <span>昨日全天</span>
                    <span>-</span>
                </div>
            </li>
            <li>
                <div class="top">
                    <img src="/assets/_layouts/store/img/订单数.png" alt="">
                    <span>支付子订单数</span>
                    <span>{{ $order['has_paid'] }}</span>
                </div>
                <div class="bottom">
                    <span>昨日全天</span>
                    <span>{{ $order['lastday_paid'] }}</span>
                </div>
            </li>
            <li>
                <a href="javascript:;">查看更多数据</a>
                <p>数据由生意参谋提供</p>
            </li>
        </ul>
    </div>
    <div class="remind">
        <div class="remind-title">
            <span>重要提醒</span>
        </div>
        <div class="baby-manage">
            <p>宝贝管理</p>
            <ul>
                <li>
                    出售中的宝贝：<span>{{ $goods['is_sell'] }}</span>
                </li>
                <li>
                    待发货的宝贝：<span>{{ $goods['wait_delivery'] }}</span>
                </li>
                <li>
                    已发货的宝贝：<span>{{ $goods['has_delivered'] }}</span>
                </li>
                <li>
                    今日被浏览宝贝：<span>-</span>
                </li>
            </ul>
        </div>
        <div class="order-remind">
            <p>订单提醒</p>
            <ul>
                <li>待发货订单：<span>{{ $order['wait_delivery'] }}</span></li>
                <li>已发货订单：<span>{{ $order['has_delivered'] }}</span></li>
                <li>售后未完成订单：<span>{{ $order['not_finished'] }}</span></li>
            </ul>
        </div>
        <div class="win-manage">
            <p>橱窗管理</p>
            <ul>
                <li>已经使用橱窗：<span>-</span></li>
            </ul>
        </div>
    </div>
    <div class="activity">
        <div class="activity-title">
            <span>活动中心</span>
        </div>
        <p>官方营销中心</p>
        <div class="activity-content">
            <ul class="activity-head">
                <li class="head-style">所有活动</li>
                <li>我能参加的活动</li>
                <li>邀请我参加的活动</li>
            </ul>
        </div>
        <div class="activity-data">
            <div class="all-activity">
                <ul>
                    <li>
                        <a href="javascript:;">
                            <span>淘宝双11游戏影视充值有礼会场招商 报名中</span>
                            <span>10月12日活动开始</span>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:;">
                            <span>淘宝双11游戏影视充值有礼会场招商 报名中</span>
                            <span>10月12日活动开始</span>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:;">
                            <span>淘宝双11游戏影视充值有礼会场招商 报名中</span>
                            <span>10月12日活动开始</span>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:;">
                            <span>淘宝双11游戏影视充值有礼会场招商 报名中</span>
                            <span>10月12日活动开始</span>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:;">
                            <span>淘宝双11游戏影视充值有礼会场招商 报名中</span>
                            <span>10月12日活动开始</span>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:;">
                            <span>淘宝双11游戏影视充值有礼会场招商 报名中</span>
                            <span>10月12日活动开始</span>
                        </a>
                    </li>
                </ul>
                <p><a href="javascript:;">查看全部活动</a></p>
            </div>
            <div class="join-activity hide">
                <ul>
                    <li>
                        <a href="javascript:;">
                            <span>淘宝双11游戏影视充值有礼会场招商 报名中</span>
                            <span>10月12日活动开始</span>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:;">
                            <span>淘宝双11游戏影视充值有礼会场招商 报名中</span>
                            <span>10月12日活动开始</span>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:;">
                            <span>淘宝双11游戏影视充值有礼会场招商 报名中</span>
                            <span>10月12日活动开始</span>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:;">
                            <span>淘宝双11游戏影视充值有礼会场招商 报名中</span>
                            <span>10月12日活动开始</span>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:;">
                            <span>淘宝双11游戏影视充值有礼会场招商 报名中</span>
                            <span>10月12日活动开始</span>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:;">
                            <span>淘宝双11游戏影视充值有礼会场招商 报名中</span>
                            <span>10月12日活动开始</span>
                        </a>
                    </li>
                </ul>
                <p><a href="javascript:;">查看全部活动</a></p>
            </div>
            <div class="invite-activity hide">
                <ul>
                    <li>
                        <a href="javascript:;">
                            <span>淘宝双11游戏影视充值有礼会场招商 报名中</span>
                            <span>10月12日活动开始</span>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:;">
                            <span>淘宝双11游戏影视充值有礼会场招商 报名中</span>
                            <span>10月12日活动开始</span>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:;">
                            <span>淘宝双11游戏影视充值有礼会场招商 报名中</span>
                            <span>10月12日活动开始</span>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:;">
                            <span>淘宝双11游戏影视充值有礼会场招商 报名中</span>
                            <span>10月12日活动开始</span>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:;">
                            <span>淘宝双11游戏影视充值有礼会场招商 报名中</span>
                            <span>10月12日活动开始</span>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:;">
                            <span>淘宝双11游戏影视充值有礼会场招商 报名中</span>
                            <span>10月12日活动开始</span>
                        </a>
                    </li>
                </ul>
                <p><a href="javascript:;">查看全部活动</a></p>
            </div>
        </div>

    </div>
@endsection

@section('head-assets-after')
<link rel="stylesheet" href="/assets/mall/css/store.home.css">
@endsection

@section('foot-assets-after')
<script src="/assets/mall/js/store.home.js"></script>
@endsection
