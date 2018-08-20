$(function () {
    // 静态弹出框
    // var options = {
    //     animation: true,
    //     trigger: 'hover' //触发tooltip的事件
    // }
    // $('[data-toggle="tooltip"]').tooltip(options)
    // 弹出模态框
    // var that;
    // $(document).on('click', '.reply', function () {
    //     that = this
    //     $('#myModal').modal('show');
    //     $('.modal .modal-dialog .modal-body input').val('');
    //     var oldText = $(this).parent().find('button').attr('data-original-title');
    //     $('.modal .modal-dialog .modal-body input').val(oldText);
    // })
    // $('.modal .modal-dialog .modal-footer .submit').on('click', function () {
    //     var newText = $('.modal .modal-dialog .modal-body input').val();
    //     $('#myModal').modal('hide');
    //     $(that).parent().find('button').attr('data-original-title', newText);
    // })
    var trId;
    $(document).on('click', '.del', function () {
        $('#myModal').modal('show');
        trId = $(this).prev().attr('data-id');
    })
    
    $('.modal .modal-dialog .modal-footer .submit').on('click', function () {
        var token = $('meta[name=csrf-token]');
        var newToken =token.attr('content');
        $.ajax({
            url: '/store/evaluate/'+trId,
            type: 'delete',
            data: {
                '_token':newToken
            },
            dataType: 'json',
            success: function (res) {
                if(res.status == 204){
                    window.location.href = "/store/evaluate?is_delete=1";
                }else{
                    alert("删除失败！")
                }
            },
            error: function () {
                alert('请求出错！');
            }
        })
        $('#myModal').modal('hide');
    })
})