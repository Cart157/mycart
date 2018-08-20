$(function(){
    // // 打开
    // $('.open span').click(function(){
    //     alert(1111);
    //     $(this).parent().addClass('hide');
    //     $('.search').removeClass('hide animated flipOutX').addClass('animated flipInX');
    // })
    // // 收起
    // $('.search p').click(function(){
    //     alert(2222);
    //     $(this).parent().removeClass('animated flipInX').addClass('animated flipOutX');
    //     setTimeout(function(){
    //         clearTimeout();
    //         $('.search').addClass('hide');
    //     },1000);
    //     $('.open').removeClass('hide');
    // })

    // 选项切换
    $('.sold-content ul li:eq(0)').click(function(){
        $(this).addClass('li-style').siblings().removeClass('li-style');
        $('.three-order').removeClass('hide').siblings().addClass('hide');
    })
    $('.sold-content ul li:eq(1)').click(function(){
        $(this).addClass('li-style').siblings().removeClass('li-style');
        $('.wait-payment').removeClass('hide').siblings().addClass('hide');
    })
    $('.sold-content ul li:eq(2)').click(function(){
        $(this).addClass('li-style').siblings().removeClass('li-style');
        $('.wait-delivery').removeClass('hide').siblings().addClass('hide');
    })
    $('.sold-content ul li:eq(3)').click(function(){
        $(this).addClass('li-style').siblings().removeClass('li-style');
        $('.already-delivery').removeClass('hide').siblings().addClass('hide');
    })
    $('.sold-content ul li:eq(4)').click(function(){
        $(this).addClass('li-style').siblings().removeClass('li-style');
        $('.customer-service').removeClass('hide').siblings().addClass('hide');
    })
    $('.sold-content ul li:eq(5)').click(function(){
        $(this).addClass('li-style').siblings().removeClass('li-style');
        $('.need-assess').removeClass('hide').siblings().addClass('hide');
    })
    $('.sold-content ul li:eq(6)').click(function(){
        $(this).addClass('li-style').siblings().removeClass('li-style');
        $('.succeed-order').removeClass('hide').siblings().addClass('hide');
    })
    $('.sold-content ul li:eq(7)').click(function(){
        $(this).addClass('li-style').siblings().removeClass('li-style');
        $('.close-order').removeClass('hide').siblings().addClass('hide');
    })

    function checkBox(a,b){
        var total = $(a);
        var solo = $(b);
        total.click(function(){
            var checked = $(this).prop('checked');
            if(checked){
                $(this).parent().parent().find('table tbody td input[type=checkbox]').prop('checked',true);
            }else{
                $(this).parent().parent().find('table tbody td input[type=checkbox]').prop('checked',false);
            }
        })
        solo.click(function(){
            var checked = $(this).prop('checked');
            var len = $(this).parents('table').parent().find('table tbody td input[type=checkbox]').length;
            var checkLen = $(this).parents('table').parent().find('table tbody td input[type=checkbox]:checked').length;
            if(len == checkLen){
                $(a).prop('checked',true);
            }else{
                $(a).prop('checked',false);
            }
        })
    }
    // 近三个月订单
    var all = $('#all');
    var allCheck = $('.three-order table tbody td input[type=checkbox]');
    checkBox(all,allCheck);
    // 等待买家付款
    var payment = $('#payment');
    var paymentCheck = $('.wait-payment table tbody td input[type=checkbox]');
    checkBox(payment,paymentCheck);
    // 等待发货
    var waitDelivery = $('#waitDelivery');
    var waitDeliveryCheck = $('.wait-delivery table tbody td input[type=checkbox]');
    checkBox(waitDelivery,waitDeliveryCheck);
    // 已发货
    var alreadyDelivery = $('#alreadyDelivery');
    var alreadyDeliveryCheck = $('.already-delivery table tbody td input[type=checkbox]');
    checkBox(alreadyDelivery,alreadyDeliveryCheck);
    // 售后中
    var customer = $('#customer');
    var customerCheck = $('.customer-service table tbody td input[type=checkbox]');
    checkBox(customer,customerCheck);
    // 需要评价
    var need = $('#need');
    var needCheck = $('.need-assess table tbody td input[type=checkbox]');
    checkBox(need,needCheck);
    // 成功的订单
    var succeed = $('#succeed');
    var succeedCheck = $('.succeed-order table tbody td input[type=checkbox]');
    checkBox(succeed,succeedCheck);
    // 关闭中订单
    var close = $('#close');
    var closeCheck = $('.close-order table tbody td input[type=checkbox]');
    checkBox(close,closeCheck);

    // 交易关闭
    var closeId;
    $(document).on('click','.tradeClose',function(){
        $('#myModal').modal('show');
        closeId = $(this).attr('data-info');
    })
    $('#myModal .modal-dialog .modal-footer .submit').on('click',function(){
        var token = $('meta[name=csrf-token]')
        var newToken =token.attr('content');
        $.ajax({
            url: '/store/order/cancel/'+closeId,
            type: 'put',
            data:{'_token':newToken},
            dataType:'json',
            success:function(res){
                window.location.href ="/store/order?order_status=0";
            },
            error:function(){
                alert('服务器错误')
            }
        })
        $('#myModal').modal('hide');
    })
    // 修改价格
    var edit;
    $(document).on('click','.revisePrice',function(){
        $('#reviseModal .modal-dialog .modal-content .modal-body input').val('');
        $('#reviseModal').modal('show');
        var goodsTotal = $(this).closest('td').find('.goodsTotal').attr('data-num');
        var goodsFreight = $(this).closest('td').find('p .goodsFreight').text();
        $('#reviseModal .modal-dialog .modal-content .modal-body .total').val(goodsTotal);
        $('#reviseModal .modal-dialog .modal-content .modal-body .freight').val(goodsFreight);
        edit = $(this).attr('data-info');
    })
    $('#reviseModal .modal-dialog .modal-footer .submit').on('click',function(){
        var token = $('meta[name=csrf-token]');
        var newToken =token.attr('content');
        var newTotal = $('#reviseModal .modal-dialog .modal-content .modal-body .total').val();
        var newFreight = $('#reviseModal .modal-dialog .modal-content .modal-body .freight').val();
        $.ajax({
            url: '/store/order/edit-price/'+edit,
            type:'put',
            data:{
                '_token':newToken,
                'goods_amount':newTotal,
                'freight':newFreight
            },
            dataType:'json',
            success:function(res){
                window.location.href ="/store/order?order_status=10";
            },
            error:function(){
                alert('请求出错');
            }
        })
        $('#reviseModal').modal('hide');
    })
})
