$(function () {
    // 选项切换
    $('.main-content-inner ul li:eq(0)').click(function(){
        $(this).addClass('li-style').siblings().removeClass('li-style');
        $('.all').removeClass('hide').siblings().addClass('hide');
    })
    $('.main-content-inner ul li:eq(1)').click(function(){
        $(this).addClass('li-style').siblings().removeClass('li-style');
        $('.sell-out').removeClass('hide').siblings().addClass('hide');
    })
    $('.main-content-inner ul li:eq(2)').click(function(){
        $(this).addClass('li-style').siblings().removeClass('li-style');
        $('.undercarriage').removeClass('hide').siblings().addClass('hide');
    })
    $('.main-content-inner ul li:eq(3)').click(function(){
        $(this).addClass('li-style').siblings().removeClass('li-style');
        $('.soon-start').removeClass('hide').siblings().addClass('hide');
    })
    $('.main-content-inner ul li:eq(4)').click(function(){
        $(this).addClass('li-style').siblings().removeClass('li-style');
        $('.never-grounding').removeClass('hide').siblings().addClass('hide');
    })

    // 总销量排序
    $('.total-sales').click(function () {
        if ($(this).find('img:eq(1)').css('display') == 'none') {
            $(this).find('img:eq(1)').css('display', 'inline-block').siblings().css('display', 'none')
        } else {
            $(this).find('img:eq(0)').css('display', 'inline-block').siblings().css('display', 'none')
        }
    })
    // 创建时间排序
    $('.create-time').click(function () {
        if ($(this).find('img:eq(1)').css('display') == 'none') {
            $(this).find('img:eq(1)').css('display', 'inline-block').siblings().css('display', 'none')
        } else {
            $(this).find('img:eq(0)').css('display', 'inline-block').siblings().css('display', 'none')
        }
    })
})