$(function(){
    // 选项切换
    $('.main-content-inner ul li:eq(0)').click(function(){
        $(this).addClass('li-style').siblings().removeClass('li-style');
        $('.sell-goods').removeClass('hide').siblings().addClass('hide');
    })
    $('.main-content-inner ul li:eq(1)').click(function(){
        $(this).addClass('li-style').siblings().removeClass('li-style');
        $('.recommend-goods').removeClass('hide').siblings().addClass('hide');
    })

    // 库存排序
    $('.stock').click(function(){
        if ($(this).find('img:eq(1)').css('display') == 'none') {
            $(this).find('img:eq(1)').css('display', 'inline-block').siblings().css('display', 'none')
        } else {
            $(this).find('img:eq(0)').css('display', 'inline-block').siblings().css('display', 'none')
        }
    })
    // 总销量排序
    $('.total-sales').click(function(){
        if ($(this).find('img:eq(1)').css('display') == 'none') {
            $(this).find('img:eq(1)').css('display', 'inline-block').siblings().css('display', 'none')
        } else {
            $(this).find('img:eq(0)').css('display', 'inline-block').siblings().css('display', 'none')
        }
    })
    // 浏览量排序
    $('.page-view').click(function(){
        if ($(this).find('img:eq(1)').css('display') == 'none') {
            $(this).find('img:eq(1)').css('display', 'inline-block').siblings().css('display', 'none')
        } else {
            $(this).find('img:eq(0)').css('display', 'inline-block').siblings().css('display', 'none')
        }
    })
    // 发布时间排序
    $('.release-time').click(function(){
        if ($(this).find('img:eq(1)').css('display') == 'none') {
            $(this).find('img:eq(1)').css('display', 'inline-block').siblings().css('display', 'none')
        } else {
            $(this).find('img:eq(0)').css('display', 'inline-block').siblings().css('display', 'none')
        }
    })
})