@extends('base::_layouts.store')

@section('title', '卖家中心 - 发布宝贝')

@section('breadcrumbs')
<ul class="breadcrumb">
    <li>
        <a href="/store">卖家中心</a>
    </li>
    <li>
        <a href="/store/order#">宝贝管理</a>
    </li>
    <li class="active">发布宝贝</li>
</ul>
@endsection

@section('main-content')
    <div class="content">
        <form method="POST" class="main-form">
        {{ csrf_field() }}
<!--
        <div class="type">
            <div class="ralignment">宝贝类型：<span class="red">*</span></div>
            <span><input type="radio" name="mold" id="radio1" checked><label for="radio1">全新</label></span>
            <span><input type="radio" name="mold" id="radio2"><label for="radio2">二手</label></span>
            <span><input type="radio" name="mold" id="radio3"><label for="radio3">洗护</label></span>
            <span><input type="radio" name="mold" id="radio4"><label for="radio4">定制</label></span>
        </div>
 -->
        <div class="binding">
            <div class="ralignment"><span>绑定产品云库ID：</span></div>
            <div class="product-info">
                <select class="product" name="cloud_id">
                </select>
            </div>
        </div>
        <div class="spec">
            <div class="ralignment"><span>规格：</span></div>
            <div class="input-box">

            </div>
        </div>
        <div class="classify">
            <div class="ralignment"><span>所属分类：</span></div>
            <div class="classify-box">
                <div class="row">
                    <div class="col-sm-4 brand">
                        <div class="brand-box">
                            <input type="hidden" name="category_id_1">
                            <ul id="brand-ul">
                            </ul>
                        </div>
                    </div>
                    <div class="col-sm-4 set clearfix">
                        <div class="set-box">
                            <input type="hidden" name="category_id_2">
                            <ul id="set-ul">
                                <span>等待你的点击...</span>
                            </ul>
                        </div>
                    </div>
                    <div class="col-sm-4 spu clearfix">
                        <div class="spu-box">
                            <input type="hidden" name="category_id_3">
                            <ul id="spu-ul">
                                <span>等待你的点击...</span>
                            </ul>
                        </div>
                    </div>
                </div>
                <!--<select class="brand">
                    <option></option>
                </select>-->
                <!--<select class="set">
                    <option></option>
                </select>-->
            </div>
        </div>
        <div class="content-info">
            <ul class="tab">
            </ul>
            <div class="all-info">
            </div>
            <div class="upload">
                <span id="submit">确认</span>
            </div>
        </form>
        </div>
    </div>
@endsection

@section('head-assets-after')
<link rel="stylesheet" href="/assets/_thirdparty/select2/css/select2.css">
<!-- <link rel="stylesheet" href="/assets/_thirdparty/jHsDate/css/jHsDate.css"> -->
<link rel="stylesheet" href="/assets/_thirdparty/ueditor/themes/iframe.css">
<link rel="stylesheet" href="/assets/_thirdparty/laydate/theme/default/laydate.css">
<link rel="stylesheet" href="/assets/mall/css/store.goods.release.css">
@endsection

@section('foot-assets-after')
<script src="/assets/_thirdparty/select2/js/select2.js"></script>
<script src="/assets/_thirdparty/select2/js/zh-CN.js"></script>
<script src="/assets/_thirdparty/ueditor/ueditor.config.js"></script>
<script src="/assets/_thirdparty/ueditor/ueditor.all.js"></script>

<!-- <script src="/assets/_thirdparty/jHsDate/js/jHsDate.js"></script> -->
<script src="/assets/_thirdparty/laydate/laydate.js"></script>

<script src="/assets/_thirdparty/jquery-form/jquery.form.min.js"></script>
<script src="/assets/_thirdparty/jquery-toaster/jquery.toaster.js"></script>

<script src="/assets/mall/js/store.goods.release.js"></script>

<script>
    $(function() {
        var base = new Base();
        base.initForm('./');

        // 点击规格颜色
        $(document).on('click', '.content .spec input',function () {
            var self = $(this);
            var itemId = self.attr('id');
            var itemFormNamePreFix = 'goods_info[' + itemId + ']';

            if ($(this).is(':checked')) {
                // 判断是否已经渲染过了，渲染过就显示出来，否则就调ajax渲染出来
                if ($("#item-tab-" + itemId).is(":hidden")) {
                    $("#item-tab-" + itemId).show();
                    $("#item-tab-content-" + itemId).show();
                    return;
                }

                $.get('/api/product/item-sku/' + itemId, {}, function(res){
                    // 替换模板的变量
                    var $tpl = $('#tpl-attr-info').clone();
                    var html = $tpl.html().assign({
                        'newId': itemId
                    });
                    var $newItem = $(html);

                    // 渲染配置信息
                    var item_name, item_no;
                    var attr_info_tr = $newItem.find('.attr-info tr:first');
                    for (var idx in res.data.attr_info) {
                        if (res.data.attr_info[idx].name == '全称') {
                            item_name = res.data.attr_info[idx].value;
                        }

                        if (res.data.attr_info[idx].name == '货号') {
                            item_no = res.data.attr_info[idx].value;
                        }

                        if (idx == 0) {
                            attr_info_tr.find('td:first').text(res.data.attr_info[idx].name);
                            attr_info_tr.find('td:last').text(res.data.attr_info[idx].value.join('，'));
                        } else {
                            var $clone_tr = attr_info_tr.clone();
                            $clone_tr.find('td:first').text(res.data.attr_info[idx].name);
                            $clone_tr.find('td:last').text(res.data.attr_info[idx].value.join('，'));

                            $newItem.find('.attr-info tbody').append($clone_tr);
                        }
                    }

                    // 渲染产品备选图片
                    var default_img = $newItem.find('.default-img img:first');
                    for (var idx in res.data.item_image) {
                        if (idx == 0) {
                            default_img.attr('src', res.data.item_image[idx]);
                        } else {
                            var $clone_img = default_img.clone();
                            $clone_img.attr('src', res.data.item_image[idx]);

                            $newItem.find('.default-img').append($clone_img);
                        }
                    }

                    // 渲染宝贝标题
                    $newItem.find('.title input').val(item_name);

                    // 渲染tab功能
                    $('.tab li').removeClass('li-style');
                    $('.all-info .list-show').addClass('hide');
                    $('.tab').append(
                        '<li id="item-tab-'+itemId+'" rel="item-tab-content-'+itemId+'" data-id="'+itemId+'" class="li-style">' + self.next().val() + '</li>'
                    );
                    $('.all-info').append($newItem);

                    // 百度编辑器
                    var ue = UE.getEditor('ueditor-' + itemId, {
                        initialFrameWidth: '100%',
                        initialFrameHeight: '400',
                        catchRemoteImageEnable: true,
                        toolbars: [[
                            'fullscreen', 'source', '|',
                            'paragraph', 'fontfamily', 'fontsize', '|',
                            'bold', 'italic', 'underline', '|', 'forecolor', 'backcolor', '|', 'removeformat', '|',
                            'justifyleft', 'justifycenter', 'justifyright', '|', 'insertorderedlist', 'insertunorderedlist', 'horizontal', '|',
                            'link', 'unlink', 'simpleupload', 'insertimage', 'wordimage', '|',
                            'inserttable', 'deletetable', 'insertparagraphbeforetable', 'insertrow', 'deleterow', 'insertcol', 'deletecol', 'mergecells', 'mergeright', 'mergedown', 'splittocells', 'splittorows', 'splittocols', 'charts', '|',
                            'undo', 'redo', '|', 'preview', 'drafts'
                        ]]
                    });

                    // 时间插件
                    laydate.render({
                        elem: '#online-time-' + itemId,
                        type: 'datetime'
                    });
                    laydate.render({
                        elem: '#sell-time-' + itemId,
                        type: 'datetime'
                    });

                    $('#image-' + itemId).sortable({
                        placeholder: 'image-item ui-state-highlight'
                    })
                }).error(function() {
                    alert('error');
                });
            } else {
                if ($("#item-tab-" + itemId).hasClass('li-style')) {
                    // 找到第一个tab，然后把它显示出来
                    $(".tab li:visible").first().click();
                }

                // 移除tab选项
                $("#item-tab-" + itemId).hide();
                // 移除tab内容
                $("#item-tab-content-" + itemId).hide();
            }
        });
        // 修改tab中li的内容
        $('.content').on('change','.input-box input[type=text]',function(){
            var newId = $(this).prev().attr('id');
            var newContent = $(this).val();
            var nnn = $('.tab').find('li[id=item-tab-'+newId+']').html(newContent);
        })

        // 处理选择按钮上传
        $(document).on('change', '.image-data', function (event) {
            for (idx in event.target.files) {
                if (typeof event.target.files[idx] == 'object') {
                    uploadFile(event.target.files[idx], $(this).closest('.form-group').find('.image-list'));
                }
            }

            $(this).val('');
            return false;
        });

        // 点击图片添加到上面
        $(document).on('click','.default-img img',function () {
            var imgSrc = $(this).attr('src');
            var target = $(this).closest('.form-group').find('.image-list');

            var imageItem = '<div class="image-item">';
            imageItem += '<button type="button" class="close"><span aria-hidden="true">×</span></button>';
            imageItem += '<img src="' + imgSrc + '">';
            imageItem += '<input type="hidden" name="' + target.data('name-prefix') + '[goods_image][]" value="' + imgSrc + '">';
            imageItem += '</div>';
            target.append(imageItem);
        })

        $(document).on('click', '.image-item .close', function () {
            $(this).closest('.image-item').remove();
        });

        $(document).on('click', '#submit', function () {
            $('.content-info .tab li:hidden').each(function() {
                UE.getEditor('ueditor-' + $(this).data('id')).destroy();
                $('#' + $(this).attr('rel')).remove();
                $(this).remove();
            });

            $('textarea[id^="ueditor-"]').each(function() {
                $(this).remove();
            });

            $(this).closest('form').submit();
        });
    });

    // 图片上传
    function uploadFile(file, target) {
        var formData = new FormData();
        formData.append('image_data', file);
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

        $.ajax('/common/image/upload', {
            method: 'POST',
            contentType: false,
            processData: false,
            data: formData,
            error: function (xhr, error) {

            },
            success: function (response) {
                if (response.result != true) {

                    return;
                }

                var imageItem = '<div class="image-item">';
                imageItem += '<button type="button" class="close"><span aria-hidden="true">×</span></button>';
                imageItem += '<img src="' + response.imgInfo.uri + '">';
                imageItem += '<input type="hidden" name="' + target.data('name-prefix') + '[goods_image][]" value="' + response.imgInfo.uri + '">';
                imageItem += '</div>';
                target.append(imageItem);
            }
        });
    }

</script>
@endsection

@section('hidden-items')
<div id="tpl-attr-info">
    <div class="list-show" id="item-tab-content-{$newId}">
        <div class="title">
            <div class="ralignment">
                <p>宝贝标题：
                    <span class="red">*</span>
                </p>
            </div>
            <input type="text" size="80" name="goods_info[{$newId}][goods_title]" value="" id="title-{$newId}">
        </div>
        <div class="mes">
            <div class="ralignment"><span>配置信息：</span></div>
            <div class="mes-box">
                <table class="attr-info">
                    <tr>
                        <td></td>
                        <td></td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="img">
            <div class="ralignment"><span class="img-title">宝贝图片：<span class="red">*</span></span></div>
            <div class="form-group">
                <label>产品图片</label>
                <div style="display:inline-block;margin-left:10px; vertical-align:middle;">
                    <div class="file-group">
                        <button type="button" class="btn btn-default btn-xs">
                            <i class="fa fa-cloud-upload"></i> 选择要上传的文件
                        </button>
                        <input id="file-{$newId}" type="file" class="image-data" multiple>
                    </div>
                </div>
                <div id="image-{$newId}" class="image-list" class="ui-sortable" data-name-prefix="goods_info[{$newId}]">

                </div>
                <div class="default-img" id="default-{$newId}" data-name-prefix="goods_info[{$newId}]">
                    <img src="">
                </div>
            </div>
        </div>
        <div class="size">
            <div class="ralignment"><span>鞋码：</span></div>
            <div class="size-shoe">
            @foreach ($data as $id => $value)
                <div>
                    <input type="checkbox" value="{{ $id }}"><label>{{ $value }}</label>
                    <input type="text" name="goods_info[{$newId}][goods_size][{{ $id }}][stock]" disabled placeholder="库存">
                    <input type="text" name="goods_info[{$newId}][goods_size][{{ $id }}][goods_price]" disabled placeholder="价格">
                </div>
            @endforeach
            </div>
        </div>
        <div class="introduce">
            <div class="ralignment"><p>宝贝描述：<span class="red">*</span></p></div>
            <div class="contents">
                <script id="ueditor-{$newId}" name="goods_info[{$newId}][goods_detail]" type="text/plain"></script>
            </div>
        </div>
        <div class="fare">
            <div class="ralignment"><p>运费：<span class="red">*</span></p></div>
            <span><input type="radio" name="goods_info[{$newId}][has_freight]" value="1" checked><input type="text" class="fare1" name="goods_info[{$newId}][freight]"><label>元</label></span>
            <span><input type="radio" name="goods_info[{$newId}][has_freight]" value="0"><label>包邮</label></span>
<!--             <span><input type="radio" name="goods_info[{$newId}][freight_type]" value="2"><label>贵重商品到付</label></span> -->
        </div>
        <div class="grounding-time">
            <div class="ralignment"><span>上架时间：</span></div>
            <div class="time">
                <input type="radio" name="goods_info[{$newId}][is_sell]" value="1" checked><label>立即</label><br>
                <input type="radio" name="goods_info[{$newId}][is_sell]" value="0"><label><input type="text" id="online-time-{$newId}" name="goods_info[{$newId}][sell_time]" />上架</label><br>
<!--                 <input type="radio" name="time-{$newId}"><label>立即上架<input type="text" id="sell-time-{$newId}" />销售</label> -->
            </div>
        </div>
<!--
        <div class="bill">
            <div class="ralignment"><span>发票：</span></div>
            <input type="radio" name="bill-{$newId}" checked><label>有</label>
            <input type="radio" name="bill-{$newId}" class="bill2"><label>无</label>
        </div>
        <div class="card">
            <div class="ralignment"><span>质保卡：</span></div>
            <input type="radio" name="card-{$newId}" checked><label>有</label>
            <input type="radio" name="card-{$newId}" class="card2"><label>无</label>
        </div>
 -->
        <div class="service">
            <div class="ralignment"><span>服务保障：</span></div>
            <div class="service-ensure">
                <input type="checkbox" name="goods_info[{$newId}][service][]" value="1"><label>10天无理由</label><br>
                <input type="checkbox" name="goods_info[{$newId}][service][]" value="2"><label>自营</label><br>
                <input type="checkbox" name="goods_info[{$newId}][service][]" value="3"><label>可配送海外</label><br>
                <input type="checkbox" name="goods_info[{$newId}][service][]" value="4"><label>当天17点前发货</label><br>
                <input type="checkbox" name="goods_info[{$newId}][service][]" value="5"><label>货到付款</label><br>
            </div>
        </div>
    </div>
</div>
@endsection
