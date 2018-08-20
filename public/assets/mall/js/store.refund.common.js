$(function(){
    // 换货处理
    $('.left .handle').on('click','.leftSecondButton',function(){
        $('.inside .leftRefuse').removeClass('hide').siblings().addClass('hide');
    })
    // 拒绝换货处理
//    $('.leftRefuse .refuse .button button:eq(0)').on('click',function(){
//        $('.inside .leftExchangeFail').removeClass('hide').siblings().addClass('hide');
//        // $('.title ul .third').text('换货失败');
//        $('.title ul .fourth').addClass('li-now').siblings().removeClass('li-now').addClass('li-already');
//    })
    // 拒绝换货取消并返回
    $('.leftRefuse .refuse .button input:eq(2)').on('click',function(){
        $('.inside .left').removeClass('hide').siblings().addClass('hide');
    })
    // 修改退货地址
    $('.leftPromise').on('click','.promise .returnAddress .address img',function(){
        $('#myModal').modal('show');
        var name = $('.leftPromise .promise .returnAddress .address .takeName span').text();
        var address = $('.leftPromise .promise .returnAddress .address .takeAddress span').text();
        $('#myModal .modal-dialog .modal-content .modal-body .total').val(name);
        $('#myModal .modal-dialog .modal-content .modal-body .freight').val(address);
    })
    // $('#myModal .modal-dialog .modal-content .modal-footer .submit').on('click',function(){
    //     var newName = $('#myModal .modal-dialog .modal-content .modal-body .total').val();
    //     var newAddress = $('#myModal .modal-dialog .modal-content .modal-body .freight').val();
    //     $('.leftPromise .promise .returnAddress .address .takeName span').text(newName);
    //     $('.leftPromise .promise .returnAddress .address .takeAddress span').text(newAddress);
    //     $('#myModal').modal('hide');
    // })


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
                imageItem += '<input type="hidden" name="reject_image[]" value="' + response.imgInfo.uri + '">';
                imageItem += '</div>';
                target.append(imageItem);
            }
        });
    };
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
    $(document).on('click', '.image-item .close', function () {
        $(this).closest('.image-item').remove();
    });
    
})