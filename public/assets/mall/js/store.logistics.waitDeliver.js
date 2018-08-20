$(function(){
    // 等待发货和已发货切换
    $('.delivery .delivery-option').on('click','li:eq(0)',function(){
        $(this).addClass('li-style').siblings().removeClass('li-style');
        $('.wait-delivery').removeClass('hide').next().addClass('hide');
    })
    $('.delivery .delivery-option').on('click','li:eq(1)',function(){
        $(this).addClass('li-style').siblings().removeClass('li-style');
        $('.already-delivery').removeClass('hide').prev().addClass('hide');
    })
    // 复选框方法
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
    // 等待发货复选框
    var wait = $('#wait');
    var waitCheck = $('.wait-delivery table tr td input[type=checkbox]');
    checkBox(wait,waitCheck);
    // 已发货复选框
    var already = $('#already');
    var alreadyCheck = $('.already-delivery table tr td input[type=checkbox]')
    checkBox(already,alreadyCheck);
})