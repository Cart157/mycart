@extends('base::_layouts.store')

@section('main-content')
    <div class="main-content-inner">
        <div class="breadcrumbs" id="breadcrumbs">
            <ul class="breadcrumb">
                <li>
                    <a href="/store">卖家中心</a>
                </li>
                <li>
                    评价管理
                </li>
            </ul>
        </div>
        <!-- /.page-content -->
    </div>
    <div class="assess">
        <div class="assess-num">
            <div class="mes">
                <p>累计评价：<span>{{ $count['evaluation'] }}个</span></p>
                <p>平均：<span>{{ $count['avg_score'] }}分</span></p>
            </div>
            <table>
                <thead>
                    <tr>
                        <th></th>
                        <th>最近1周</th>
                        <th>最近1个月</th>
                        <th>最近6个月</th>
                        <th>6个月前</th>
                        <th>总计</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>5分</td>
                        <td>{{ $count['score_5_week'] }}</td>
                        <td>{{ $count['score_5_month'] }}</td>
                        <td>{{ $count['score_5_halfyear'] }}</td>
                        <td>{{ $count['score_5_before'] }}</td>
                        <td>{{ $count['score_5'] }}</td>
                    </tr>
                    <tr>
                        <td>4分</td>
                        <td>{{ $count['score_4_week'] }}</td>
                        <td>{{ $count['score_4_month'] }}</td>
                        <td>{{ $count['score_4_halfyear'] }}</td>
                        <td>{{ $count['score_4_before'] }}</td>
                        <td>{{ $count['score_4'] }}</td>
                    </tr>
                    <tr>
                        <td>3分</td>
                        <td>{{ $count['score_3_week'] }}</td>
                        <td>{{ $count['score_3_month'] }}</td>
                        <td>{{ $count['score_3_halfyear'] }}</td>
                        <td>{{ $count['score_3_before'] }}</td>
                        <td>{{ $count['score_3'] }}</td>
                    </tr>
                    <tr>
                        <td>2分</td>
                        <td>{{ $count['score_2_week'] }}</td>
                        <td>{{ $count['score_2_month'] }}</td>
                        <td>{{ $count['score_2_halfyear'] }}</td>
                        <td>{{ $count['score_2_before'] }}</td>
                        <td>{{ $count['score_2'] }}</td>
                    </tr>
                    <tr>
                        <td>1分</td>
                        <td>{{ $count['score_1_week'] }}</td>
                        <td>{{ $count['score_1_month'] }}</td>
                        <td>{{ $count['score_1_halfyear'] }}</td>
                        <td>{{ $count['score_1_before'] }}</td>
                        <td>{{ $count['score_1'] }}</td>
                    </tr>
                    <tr>
                        <td>总计</td>
                        <td>{{ $count['evaluation_week'] }}</td>
                        <td>{{ $count['evaluation_month'] }}</td>
                        <td>{{ $count['evaluation_halfyear'] }}</td>
                        <td>{{ $count['evaluation_before'] }}</td>
                        <td>{{ $count['evaluation'] }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="assess-content">
            <ul>
                <li><a href="/store/evaluate" {!! !Request::has('is_delete') ? ' class="li-style"' : '' !!}>来自买家的评价</a></li>
                <li><a href="/store/evaluate?is_delete=1" {!! Request::input('is_delete') == 1 ? ' class="li-style"' : '' !!}>已删除的评价</a></li>
            </ul>
            <div class="choose">
                <table class="table-buyer">
                    <thead>
                        <tr>
                            <th style="width:130px">
                                <form>
                                    <select name="score">
                                        <option value="" selected="selected">分数</option>
                                        <option value="5">5</option>
                                        <option value="4">4</option>
                                        <option value="3">3</option>
                                        <option value="2">2</option>
                                        <option value="1">1</option>
                                    </select>
                                    @if(Request::input('is_delete') == 1)
                                        <input type="hidden" name="is_delete" value="1">
                                    @endif
                                    <input type="submit" value="筛选" class="screen">
                                </form>
                            </th>
                            <th style="width:150px">评价</th>
                            <th style="width:150px">买家</th>
                            <th style="width:200px">宝贝信息</th>
                            <th style="width:200px">订单号</th>
                            <th style="width:70px">操作</th>
                        </tr>
                        <tr class="scan">
                            <td colspan="6">
                                <p>扫描您店铺近30天收到的评价，查看是否有异常。<a href="javascript:;">立刻扫描</a></p>
                            </td>
                        </tr>
                    </thead>
                    @foreach($data as $value)
                    <tbody>
                        <tr>
                            <td>{{ $value->score }}分</td>
                            <td>
                                <p>{{ $value->content }}</p>
                                <p>[{{ $value->created_at }}]</p>
                            </td>
                            <td>
                                <p>买家：<span>{{ $value->user->name}}</span></p>

                            </td>
                            <td>
                                <a href="javascript:;">
                                {{ $value->goods()->withTrashed()->first()->name }}
                            </a>
                                <p><span>{{ $value->goods()->withTrashed()->first()->goods_price }}</span>元</p>
                            </td>
                            <td>
                                {{ $value->order->order_no }}
                            </td>
                            <td>
                            @if($value->deleted_at == null)
                                <input type="hidden" data-id="{{ $value->id }}" class="trId">
                                <a href="javascript:;" class="del">删除</a>
                            @endif
                                <a href="javascript:;" class="reply">回复</a>
                            </td>
                        </tr>
                    </tbody>
                    @endforeach
                </table>
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
                        确定删除？
                    </h4>
                </div>
                <!--<div class="modal-body">
                </div>-->
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
@endsection

@section('head-assets-after')
<link rel="stylesheet" href="/assets/mall/css/store.rated-manage.css">
@endsection

@section('foot-assets-after')
<script src="/assets/mall/js/store.rated-manage.js"></script>
@endsection
