@extends('base::_layouts.store')

@section('title', '卖家中心 - 数据库')

@section('main-content')
    <div class="main-content-inner">
        <div class="breadcrumbs" id="breadcrumbs">
            <ul class="breadcrumb">
                <li>
                    <a href="#">Home</a>
                </li>
                <li>
                    <a href="#">Other Pages</a>
                </li>
                <li class="active">Blank Page</li>
            </ul>
        </div>
        <div class="clearfix form-actions">
            <form class="form-search">
                <div class="row">
                    <div class="col-xs-12">
                        <label class="control-label no-padding-right">搜索商品分类：</label>
                        <span class="align-middle">
                                <input type="text" class="search-query input-sm">
                            </span>
                        <button class="btn btn-xs" type="button">搜索</button>
                    </div>
                </div>
            </form>
            <div class="row linkage">
                <div class="col-sm-4 brand">
                    <div class="brand-pp">
                        品牌
                        <a href="javascript:;">[+]</a>
                    </div>
                    <div class="brand-box">
                        <ul id="brand-ul">
                            <li>礼品箱包</li>
                            <li>礼品箱包</li>
                            <li>礼品箱包</li>
                            <li>礼品箱包</li>
                            <li>礼品箱包</li>
                            <li>礼品箱包</li>
                            <li>礼品箱包</li>
                            <li>礼品箱包</li>
                            <li>礼品箱包</li>
                            <li>礼品箱包</li>
                            <li>礼品箱包</li>
                            <li>礼品箱包</li>
                            <li>礼品箱包</li>
                            <li>礼品箱包</li>
                        </ul>
                    </div>
                </div>
                <div class="col-sm-4 set clearfix">
                    <div class="set-xl">
                        系列
                        <a href="javascript:;">[+]</a>
                    </div>
                    <div class="set-box">
                        <ul id="set-ul">
                            <!--<span>等待你的点击...</span>-->
                            <li>礼品箱包</li>
                            <li>礼品箱包</li>
                            <li>礼品箱包</li>
                            <li>礼品箱包</li>
                            <li>礼品箱包</li>
                            <li>礼品箱包</li>
                            <li>礼品箱包</li>
                        </ul>
                    </div>
                </div>
                <div class="col-sm-4 spu clearfix">
                    <div class="spu-sp">
                        款式
                        <a href="javascript:;">[+]</a>
                    </div>
                    <div class="spu-box">
                        <ul id="spu-ul">
                            <!--<span>等待你的点击...</span>-->
                            <li>礼品箱包</li>
                            <li>礼品箱包</li>
                            <li>礼品箱包</li>
                            <li>礼品箱包</li>
                            <li>礼品箱包</li>
                            <li>礼品箱包</li>
                            <li>礼品箱包</li>
                            <li>礼品箱包</li>
                            <li>礼品箱包</li>
                            <li>礼品箱包</li>
                            <li>礼品箱包</li>
                            <li>礼品箱包</li>
                            <li>礼品箱包</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="row info">
            <div class="img col-xs-3">
                <img src="/assets/_layouts/store/img/avatar04.png" alt="">
            </div>
            <div class="edit col-sm-8">
                <textarea></textarea>
            </div>
        </div>
        <div class="submit">
            <span>提交</span>
        </div>
        <div class="page-content">
            <div class="row">
                <div class="col-xs-12">
                    <!-- PAGE CONTENT BEGINS -->
                    <!-- PAGE CONTENT BEGINS -->
                    <div class="row">
                        <div class="col-xs-12">

                        </div>
                        <!-- /.span -->
                    </div>
                    <!-- /.row -->
                    <!-- PAGE CONTENT ENDS -->
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /.page-content -->
    </div>
@endsection

@section('head-assets-after')
<link rel="stylesheet" href="/assets/mall/css/store.manage.database.css">
@endsection

@section('foot-assets-after')
<script src="/assets/mall/js/store.manage.database.js"></script>
@endsection