$(function () {
    // 绑定产品云库ID
    $(".product").select2({
        placeholder: '请选择',
        language: "zh-CN",
        // tags: true,
        multiple: false,
        ajax: {
            url: '/api/product/cloud-spu',
            type: "get",
            dataType: "json",
            delay: 250,
            data: function (params) {
                return {
                    q: params.term,
                    page: params.page
                };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;
                var itemList = [];
                var arr = data.data
                for (item in arr) {
                    itemList.push({ id: arr[item].id, text: arr[item].name })
                }
                return {
                    results: itemList,
                    pagination: {
                        more: (params.page * 30) < data.total_count
                    }
                };
            },
            cache: true
        },
        //escapeMarkup: function (markup) { return markup; }, // 自定义格式化防止xss注入
        minimumInputLength: 1,//最少输入多少个字符后开始查询
        //formatResult: function formatRepo(repo) { return repo.text; }, // 函数用来渲染结果
        //formatSelection: function formatRepoSelection(repo) { return repo.text; }, // 函数用于呈现当前的选
    })
    $('.product').change(function () {
        var id = $(".product").select2("data")[0].id;
        $.ajax({
            url: '/api/product/item-spu/' + id + '/cloud-sku',
            type: 'get',
            async: true,
            data: {},
            dataType: 'json',
            success: function (res) {
                $('.input-box').html('');
                $('.tab').html('');
                $('.all-info').html('');
                $(res.data).each(function (inx, item) {
                    $('.input-box').append(
                        //'<span><input type="checkbox" name="checkbox" id="' + item.id + '"><input type="text" value="' + item.item_no + '" disabled></span>'
                        '<span><input type="checkbox" id="' + item.id + '"><input type="text" name="goods_info['+ item.id +'][color_name]" value="' + item.item_no + '" disabled></span>'
                    )
                })
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                alert('没有该云产品')
            }
        })
    })

    // 动态生成
    $('.content .spec .input-box').on('click', 'input123', function () {
        var newVal = $(this).next().text();
        var newId = $(this).attr('id');
        if ($(this).is(':checked')) {
            $('.tab').append(
                '<li id="' + newId + '" class="li-style">' + newVal + '</li>'
            )
            $('.all-info').append(
                '<div class="list-show" id="' + newId + '">' +
                '<div class="title">' +
                '<p>宝贝标题：' +
                '<span class="red">*</span>' +
                '</p>' +
                '<input type="text" maxlength="" value="" id="title-' + newId + '">' +
                // '还可以输入<span class="num" id="spanNum-' + newId + '">' + 30 + '</span>个字' +
                '</div>' +
                '<div class="mes">' +
                '<span>配置信息：</span>' +
                '<div class="mes-box">' +
                '<table>' +
                '<tr>' +
                '<td>上市时间</td>' +
                '<td>减肥的库拉索发动机款式辅导教师卡乐芙简单快乐撒奋斗就是拉风的就是咖啡 简单快乐撒奋斗就是拉风的就是萨范德萨范德萨发发付款打脸萨父级的</td>' +
                '</tr>' +
                '<tr>' +
                '<td>上市时间</td>' +
                '<td align="left">斗就是拉风的就是萨范德萨范德萨发发付款打脸萨父级的</td>' +
                '</tr>' +
                '<tr>' +
                '<td>上市时间</td>' +
                '<td align="left">斗就是拉风的就是萨范德萨范德萨发发付款打脸萨父级的</td>' +
                '</tr>' +
                '</table>' +
                '</div>' +
                '</div>' +
                '<div class="img">' +
                '<span class="img-title">宝贝图片：<span class="red">*</span></span>' +
                '<div class="form-group">' +
                '<label>产品图片</label>' +
                '<div style="display:inline-block;margin-left:10px; vertical-align:middle;">' +
                '<div class="file-group">' +
                '<button type="button" class="btn btn-default btn-xs">' +
                '<i class="fa fa-cloud-upload"></i> 选择要上传的文件' +
                '</button>' +
                '<input id="file-' + newId + '" type="file" class="image-data" multiple>' +
                '</div>' +
                '</div>' +
                '<div id="image-' + newId + '" class="image-list" class="ui-sortable">' +

                '</div>' +
                '<div class="default-img" id="default-' + newId + '">' +
                '<img src="/assets/_layouts/store/img/avatar04.png">' +
                '<img src="/assets/_layouts/store/img/浏览量.png">' +
                '<img src="/assets/_layouts/store/img/avatar04.png">' +
                '<img src="/assets/_layouts/store/img/浏览量.png">' +
                '<img src="/assets/_layouts/store/img/avatar04.png">' +
                '</div>' +
                '</div>' +
                '</div>' +
                '<div class="size">' +
                '<span>鞋码：</span>' +
                '<div class="size-shoe">' +
                '<div>' +
                '<input type="checkbox">' +
                '<input type="text" disabled value="27.5">' +
                '</div>' +
                '<div>' +
                '<input type="checkbox">' +
                '<input type="text" disabled value="27.5">' +
                '</div>' +
                '<div>' +
                '<input type="checkbox">' +
                '<input type="text" disabled value="27.5">' +
                '</div>' +
                '<div>' +
                '<input type="checkbox">' +
                '<input type="text" disabled value="27.5">' +
                '</div>' +
                '<div>' +
                '<input type="checkbox">' +
                '<input type="text" disabled value="27.5">' +
                '</div>' +
                '<div>' +
                '<input type="checkbox">' +
                '<input type="text" disabled value="27.5">' +
                '</div>' +
                '<div>' +
                '<input type="checkbox">' +
                '<input type="text" disabled value="27.5">' +
                '</div>' +
                '</div>' +
                '</div>' +
                '<div class="introduce">' +
                '<p>宝贝描述：<span class="red">*</span></p>' +
                '<div class="contents">' +
                '<script id="ueditor-' + newId + '" name="" type="text/plain"></script>' +
                '</div>' +
                '</div>' +
                '<div class="fare">' +
                '<p>运费：<span class="red">*</span></p>' +
                '<input type="radio" name="fare-' + newId + '" checked><input type="text" class="fare1"><label>元</label>' +
                '<input type="radio" name="fare-' + newId + '"><label>包邮</label>' +
                '<input type="radio" name="fare-' + newId + '"><label>贵重商品到付</label>' +
                '</div>' +
                '<div class="grounding-time">' +
                '<span>上架时间：</span>' +
                '<div class="time">' +
                '<input type="radio" name="time-' + newId + '" checked><label>立即</label><br>' +
                '<input type="radio" name="time-' + newId + '"><label><input type="text" id="datetimeformat-' + newId + '" />上架</label><br>' +
                '<input type="radio" name="time-' + newId + '"><label>立即上架<input type="text" id="datetime-' + newId + '" />销售</label>' +
                '</div>' +
                '</div>' +
                '<div class="bill">' +
                '<span>发票：</span>' +
                '<input type="radio" name="bill-' + newId + '" checked><label>有</label>' +
                '<input type="radio" name="bill-' + newId + '" class="bill2"><label>无</label>' +
                '</div>' +
                '<div class="card">' +
                '<span>质保卡：</span>' +
                '<input type="radio" name="card-' + newId + '" checked><label>有</label>' +
                '<input type="radio" name="card-' + newId + '" class="card2"><label>无</label>' +
                '</div>' +
                '<div class="service">' +
                '<span>服务保障：</span>' +
                '<div class="service-ensure">' +
                '<input type="checkbox" name="service-' + newId + '" checked><label>7天无理由</label><br>' +
                '<input type="checkbox" name="service-' + newId + '"><label>包邮售后</label><br>' +
                '<input type="checkbox" name="service-' + newId + '"><label>15天无理由</label><br>' +
                '<input type="checkbox" name="service-' + newId + '"><label>30天无理由</label><br>' +
                '<input type="checkbox" name="service-' + newId + '"><label>一辈子无理由</label><br>' +
                '</div>' +
                '</div>' +
                '</div>'
            )
            // 图片上传
            function uploadFile(file) {
                var formData = new FormData();
                formData.append('image_data', file);
                formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
                $.ajax('/common/image/upload', {
                    method: 'POST',
                    contentType: false,
                    processData: false,
                    data: formData,
                    error: function (xhr, error) {
                        // PNotify.removeAll();
                        // new PNotify({
                        //     text: '图片上传失败',
                        //     type: 'error',
                        // });
                    },
                    success: function (response) {
                        if (response.result != true) {
                            // PNotify.removeAll();
                            // new PNotify({
                            //     text: response.message,
                            //     type: 'error',
                            // });
                            return;
                        }

                        var imageItem = '<div class="image-item">';
                        imageItem += '<button type="button" class="close"><span aria-hidden="true">×</span></button>';
                        imageItem += '<img src="' + response.imgInfo.uri + '">';
                        imageItem += '<input type="hidden" name="{{$fieldp[' + name + ']}}" value="' + response.imgInfo.uri + '">';
                        imageItem += '</div>';
                        $('#image-' + newId).append(imageItem);

                        // PNotify.removeAll();
                        // new PNotify({
                        //     text: '图片上传成功',
                        //     type: 'success',
                        // });
                    }
                });
            }
            // 处理选择按钮上传
            $('#file-' + newId).on('change', function (event) {
                for (idx in event.target.files) {
                    if (typeof event.target.files[idx] == 'object') {
                        uploadFile(event.target.files[idx]);
                    }
                }

                $(this).val('');
                return false;
            });

            $(document).on('click', '.image-item .close', function () {
                $(this).closest('.image-item').remove();
            });

            $(document).on('keyup', 'input[name="ori_url"]', function () {
                $(this).closest('.form-group').find('a').attr('href', $(this).val());
            });

            $('form').on('submit', function () {
                $('.error-placeholder').remove();
            });
            $("#image-" + newId).sortable({
                placeholder: 'image-item ui-state-highlight'
            });
            $("#image-" + newId).disableSelection();
            // 点击图片添加到上面
            $('#default-' + newId).on('click', 'img', function () {
                var imgSrc = $(this).attr('src')
                var imageItem = '<div class="image-item">';
                imageItem += '<button type="button" class="close"><span aria-hidden="true">×</span></button>';
                imageItem += '<img src="' + imgSrc + '">';
                imageItem += '<input type="hidden" name="" value="">';
                imageItem += '</div>';
                $('#image-' + newId).append(imageItem);
            })

            // 宝贝标题字数控制
            // function setShow(obj, maxlength) {
            //     var rem = maxlength - obj.val().length;
            //     if (rem < 0) {
            //         rem = 0;
            //     }
            //     $('#spanNum-' + newId).html(rem)
            // }
            // $('#title-' + newId).on('keyup', function () {
            //     setShow($(this), 30);
            // })
            // 日期初始化
            $('#datetimeformat-' + newId).jHsDate({
                format: 'yyyy-MM-dd  hh:mm'
            });
            $('#datetime-' + newId).jHsDate({
                format: 'yyyy-MM-dd  hh:mm'
            });
            // 百度编辑器
            var ue = UE.getEditor('ueditor-' + newId, {
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
            // 动态生成li切换
            $('.tab li').each(function () {
                if ($(this).hasClass('li-style')) {
                    $(this).siblings().removeClass('li-style');
                }
            })
            $('.all-info .list-show').each(function () {
                if (!$(this).hasClass('hide')) {
                    $(this).siblings().addClass('hide');
                }
            })
        } else {
            $('.tab li').each(function () {
                if ($(this).attr('id') == newId) {
                    $(this).remove();
                }
            })
            $('.all-info .list-show').each(function () {
                if ($(this).attr('id') == newId) {
                    $(this).remove();
                }
            })
        }
    })

    // $('.tab').on('click','li',function(){
    //     var oldId = $(this).attr('id');
    //     $(this).addClass('li-style').siblings().removeClass('li-style');
    //     $('.all-info .list-show').each(function(){
    //         if($(this).attr('id') == oldId){
    //             $(this).removeClass('hide').siblings().addClass('hide');
    //         }
    //     })
    // })
    $(document).on('click', '.tab li', function () {
        var contentId = $(this).attr('rel');
        $(this).addClass('li-style').siblings().removeClass('li-style');
        $('#' + contentId).removeClass('hide').siblings().addClass('hide');
    });
    // 点击复选框可编辑(鞋码部分)
    $('.content').on('click', '.size input', function () {
        var checked = $(this).find('input[type=checkbox]:checked');
        if ($(this).is(':checked')) {
            $(this).next().next().attr('disabled', false).css('border', '1px solid #ccc');
            $(this).next().next().next().attr('disabled', false).css('border', '1px solid #ccc');
        } else {
            $(this).next().next().attr('disabled', true).css('border', 'none');
            $(this).next().next().val('');
            $(this).next().next().next().attr('disabled', true).css('border', 'none');
            $(this).next().next().next().val('');
        }
    })
    // 点击复选框可编辑(规格部分)
    $('.content').on('click','.input-box input[type=checkbox]',function(){
        var checked = $(this).find('input[type=checkbox]:checked');
        if($(this).is(':checked')){
            $(this).next().attr('disabled', false).css('border', '1px solid #aaa');
        }else{
            $(this).next().attr('disabled', true).css('border', 'none');
        }
    })


    // 联动li样式
    $('.brand .brand-box ul').on('mousedown', 'li', function () {
        $(this).addClass('wall').siblings().removeClass('wall');
    })
    $('.set .set-box ul').on('mousedown', 'li', function () {
        $(this).addClass('wall').siblings().removeClass('wall');
    })
    $('.spu .spu-box ul').on('mousedown', 'li', function () {
        $(this).addClass('wall').siblings().removeClass('wall');
    })

    if ($('input[name="category_id_1"]').val() == '') {
        // 渲染所属分类第一列
        $.ajax({
            url: '/api/mall/category?parent_id=0',
            type: 'get',
            async: true,
            dataType: 'json',
            success: function (res) {
                $(res.data).each(function(inx,item){
                    $(".brand ul").append(
                        "<li id='" + item.id + "'>" +
                        item.name +
                        "</li>"
                    )
                })
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                alert('服务器异常')
            }
        });
    }

    // 动态生成
    $('.brand ul').on('click','li',function(){
        $('input[name="category_id_1"]').val($(this).attr('id'));
        $('.set ul').empty();
        $('.spu ul').empty();
        var id = $(this).prop('id');
        $.ajax({
            url: '/api/mall/category?parent_id='+id,
            type:'get',
            async:true,
            data:{
            },
            success:function(res){
                $(res.data).each(function(inx,item){
                    $(".set ul").append("<li id='" + item.id + "'>" + item.name + "</li>")
                })
            },
            error:function(){
                alert('服务器异常')
            }
        })
    })
    $('.set ul').on('click','li',function(){
        $('input[name="category_id_2"]').val($(this).attr('id'));
        $('.spu ul').empty();
        var id = $(this).prop('id');
        $.ajax({
            url: '/api/mall/category?parent_id='+id,
            type:'get',
            async:true,
            data:{

            },
            success:function(res){
                $(res.data).each(function(inx,item){
                    $(".spu ul").append("<li id='" + item.id + "'>" + item.name + "</li>")
                })
            },
            error:function(){
                alert('服务器异常')
            }
        })
    })
    $('.spu ul').on('click','li',function(){
        $('input[name="category_id_3"]').val($(this).attr('id'));
    })
})