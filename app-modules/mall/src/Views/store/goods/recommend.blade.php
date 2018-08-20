@extends('base::_layouts.store')

@section('title', '卖家中心 - 橱窗推荐宝贝')

@section('breadcrumbs')
<ul class="breadcrumb">
    <li>
        <a href="/store">卖家中心</a>
    </li>
    <li>
        <a href="/store/goods">宝贝管理</a>
    </li>
    <li class="active">橱窗推荐宝贝</li>
</ul>
@endsection

@section('nav-tabs')
<div class="space-10"></div>

<ul class="nav nav-pills">
    <li>
        <a href="/store/goods">出售中的宝贝</a>
    </li>

    <li class="active">
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
        <!--橱窗推荐宝贝-->
        <div class="recommend-goods">
            <div class="data">
                <div class="already-recommend">
                    <span>已推荐（30/30）</span>
                </div>

                <table id="simple-table" class="table table-hover table-bordered-bottom">
                    <thead>
                        <tr>
                            <th width="40"></th>
                            <th width="320" style="text-align:center">宝贝名称</th>
                            <th width="120">价格</th>
                            <th style="color:#0579c6" class="stock">
                                库存
                                <img src="/assets/_layouts/store/img/向下粗箭头.png" style="display:inline-block">
                                <img src="/assets/_layouts/store/img/向上粗箭头.png" style="display:none">
                            </th>
                            <th width="120" style="color:#0579c6" class="total-sales">
                                总销量
                                <img src="/assets/_layouts/store/img/向下粗箭头.png" style="display:inline-block">
                                <img src="/assets/_layouts/store/img/向上粗箭头.png" style="display:none">
                            </th>
                            <th width="120" style="color:#0579c6" class="page-view">
                                浏览量
                                <img src="/assets/_layouts/store/img/向下粗箭头.png" style="display:inline-block">
                                <img src="/assets/_layouts/store/img/向上粗箭头.png" style="display:none">
                            </th>
                            <th width="120" style="color:#0579c6" class="release-time">
                                发布时间
                                <img src="/assets/_layouts/store/img/向下粗箭头.png" style="display:inline-block">
                                <img src="/assets/_layouts/store/img/向上粗箭头.png" style="display:none">
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
                            <th colspan="7">
                                <label for="check-all" style="margin-right: 10px;">
                                    全选
                                </label>
                                <button class="btn btn-xs btn-white btn-default">
                                    <i class="ace-icon fa fa-times red2"></i>
                                    取消推荐
                                </button>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="center">
                                <label class="pos-rel">
                                <input type="checkbox" class="ace" />
                                <span class="lbl"></span>
                            </label>
                            </td>

                            <td>
                                <div class="media">
                                    <div class="media-left">
                                        <a href="#">
                                            <img class="media-object" data-src="holder.js/64x64" alt="64x64" src="./assets/_layouts/mall/image/avatar04.png" data-holder-rendered="true"
                                                style="width: 48px; height: 48px;">
                                        </a>
                                    </div>
                                    <div class="media-body">
                                        <a href="#">我是一只小青龙，小青龙，小青龙。我有许多小秘密，小秘密，小秘密。</a>
                                    </div>
                                </div>
                            </td>
                            <td style="color:#ff6600">170000.00</td>
                            <td class="hidden-480">233696</td>
                            <td>21</td>
                            <td>58</td>
                            <td class="hidden-480">
                                <span>2017-10-18 12:69</span>
                            </td>

                            <td>
                                <a href="#">取消推荐</a><br>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="clearfix">
                    <div class="input-group pull-right page-jump" style="margin-left: 10px;">
                        <input class="form-control" type="text">
                        <span class="input-group-btn">
                        <button class="btn btn-link btn-default" type="button">
                            跳转
                        </button>
                    </span>
                    </div>
                    <ul class="pagination pull-right no-margin">
                        <li>
                            <a href="#">
                            上一页
                        </a>
                        </li>

                        <li class="active">
                            <a href="#">1</a>
                        </li>

                        <li>
                            <a href="#">2</a>
                        </li>

                        <li>
                            <a href="#">3</a>
                        </li>

                        <li>
                            <a href="#">4</a>
                        </li>

                        <li>
                            <a href="#">5</a>
                        </li>

                        <li>
                            <a href="#">
                            下一页
                        </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('head-assets-after')
<link rel="stylesheet" href="/assets/mall/css/store.goods.sell.css">
@endsection

@section('foot-assets-after')
<script src="/assets/mall/js/store.goods.sell.js"></script>
@endsection
