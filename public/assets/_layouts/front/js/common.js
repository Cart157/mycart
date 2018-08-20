$(function(){
    $('.right-nav .top-nav li:eq(2) a').click(function(){
        var docHeight = $(document).height();
        $(window).scrollTop(docHeight);
    });
    // 开始搜索
    $('.search').on('click','.search-top .go-search',function(){
        var text = encodeURI($(this).siblings().val());
        window.location.href = '/product/search?wd='+text;
    });
    // 点击搜索框下内容
    $('.search').on('click','ul li a',function(){
        var text = $(this).text();
        window.location.href = '/product/search?wd='+text;
    });
    // 回车搜索
    $('.search').on('keyup','.search-top input',function(){
        if(event.keyCode == '13'){
            var text = encodeURI($(this).val());
            window.location.href = '/product/search?wd='+text;
        }
    });
    // 打开首页时动画
    setTimeout(function(){
        (function(){
            $('.qr-code').animate({right:"5px"});
        })();
    },1000);
    // 关闭动画
    $('.qr-code .code-close').click(function(){
        $('.qr-code').animate({right:"-310px"});
    });
    // 图片加载失败显示默认图片
    $('img').error(function(){
        $(this).attr('src', '/assets/product/image/default_img.png');
    });
})