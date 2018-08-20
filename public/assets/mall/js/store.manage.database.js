$(function () {
    $('.linkage .brand .brand-box ul').on('mousedown', 'li', function () {
        $(this).addClass('wall').siblings().removeClass('wall');
    })
    $('.linkage .set .set-box ul').on('mousedown', 'li', function () {
        $(this).addClass('wall').siblings().removeClass('wall');
    })
    $('.linkage .spu .spu-box ul').on('mousedown', 'li', function () {
        $(this).addClass('wall').siblings().removeClass('wall');
    })
    // 拖拽排序
    $( "#brand-ul" ).sortable({
        stop:function(){
            alert(111)
            // PNotify.removeAll();
            // new PNotify({
            //     text: '服务器异常！',
            //     type: 'error',
            // });
        }
    });
    $( "#set-ul" ).sortable();
    $( "#spu-ul" ).sortable();
})