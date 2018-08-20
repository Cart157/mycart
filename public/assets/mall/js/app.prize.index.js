$(function () { 
    function GetQueryString(name){  
         var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");  
         var r = window.location.search.substr(1).match(reg);  
         if(r!=null)return  unescape(r[2]); return null;  
    }  
    var token;
    if(GetQueryString("token") !=null && GetQueryString("token").toString().length>1){  
       token = GetQueryString("token");  
    }  
    $.ajax({
        url: '/api/mall/promotion/prize?token='+token,
        type: 'get',
        async: true,
        data: {},
        dataType: 'json',
        success: function (res) {
            $('.prize-one span').append(
                '<img src="/assets/mall/img/trophy13.png">幸运值：' + res.data.lucky_value + ''
            )
           if(res.data.cost_coin == 0){
                $('.clickImg').append(
                    '<p>免费</p>'
                );
            }else{
                $('.clickImg').append(
                    '<p>'+res.data.cost_coin+'金币</p>'
                );
            }
            // 奖品渲染
            $(res.item).each(function (inx, item) {
                if (item.id == 1) {
                    $('.luck-unit-0').append(
                        '<img src="https://static.tosneaker.com'+item.image+'">' +
                        '<p>' + item.name + '</p>'
                    )
                }
                if (item.id == 2) {
                    $('.luck-unit-1').append(
                        '<img src="https://static.tosneaker.com'+item.image+'">' +
                        '<p>' + item.name + '</p>'
                    )
                }
                if (item.id == 3) {
                    $('.luck-unit-2').append(
                        '<img src="https://static.tosneaker.com'+item.image+'">' +
                        '<p>' + item.name + '</p>'
                    )
                }
                if (item.id == 4) {
                    $('.luck-unit-3').append(
                        '<img src="https://static.tosneaker.com'+item.image+'">' +
                        '<p>' + item.name + '</p>'
                    )
                }
                if (item.id == 5) {
                    $('.luck-unit-4').append(
                        '<img src="https://static.tosneaker.com'+item.image+'">' +
                        '<p>' + item.name + '</p>'
                    )
                }
                if (item.id == 6) {
                    $('.luck-unit-5').append(
                        '<img src="https://static.tosneaker.com'+item.image+'">' +
                        '<p>' + item.name + '</p>'
                    )
                }
                if (item.id == 7) {
                    $('.luck-unit-6').append(
                        '<img src="https://static.tosneaker.com'+item.image+'">' +
                        '<p>' + item.name + '</p>'
                    )
                }
                if (item.id == 8) {
                    $('.luck-unit-7').append(
                        '<img src="https://static.tosneaker.com'+item.image+'">' +
                        '<p>' + item.name + '</p>'
                    )
                }
            })
            // 获奖名单渲染
            $(res.extra).each(function (inx, item) {
                $('.list-header .list-info-user').append(
                    '<div class="item" id="'+item.id+'">'+
                        '<div class="item-left">'+
                            '<img src="https://static.tosneaker.com'+item.user_avatar+'">'+
                            '<div class="item-info">'+
                                '<span>'+item.user_name+'</span>'+
                                '<span>'+item.prize_name+'</span>'+
                            '</div>'+
                        '</div>'+
                        '<div class="time-info">'+
                            '<span class="time">'+item.prize_at+'</span>'+
                        '</div>'+
                    '</div>'
                )
            })
            // $('.time').liveTimeAgo();
        },
        error: function () {
            console.log(error);
        }
    })
    
    var luck = {
        index: 0,	//当前转动到哪个位置，起点位置
        count: 0,	//总共有多少个位置
        timer: 0,	//setTimeout的ID，用clearTimeout清除
        speed: 20,	//初始转动速度
        times: 0,	//转动次数
        cycle: 50,	//转动基本次数：即至少需要转动多少次再进入抽奖环节
        prize: -1,	//中奖位置
        nn: 0,
        init: function (id) {
            if ($('.prize').find(".luck-unit").length > 0) {
                $luck = $('.prize');
                $units = $luck.find(".luck-unit");
                this.obj = $luck;
                this.count = $units.length;
            };
        },


        roll: function (id) {
            var index = this.index;
            var count = this.count;
            var luck = this.obj;
            $(luck).find(".luck-unit-" + index).removeClass("active");
            index += 1;
            if (index > count - 1) {
                index = 0;
            };
            $(luck).find(".luck-unit-" + index).addClass("active");
            this.index = index;
            return false;
        },
        stop: function (index) {
            this.prize = index;
            return false;
        }
    };


    function roll() {
        luck.times += 1;
        luck.roll();
        if (luck.times > luck.cycle + 10 && luck.prize == luck.index) {
            clearTimeout(luck.timer);
            $('.pop').css('height',$(document).height())
            setTimeout(function(){
                $('.pop').show();
            },200)
            document.body.style.overflow='hidden';
            luck.prize = -1;
            luck.times = 0;
            click = false;
        } else {
            if (luck.times < luck.cycle) {
                luck.speed -= 10;
            } else if (luck.times == luck.cycle) {
                var index = Math.random() * (luck.count) | 0;
                if (index > 5) {
                    index = 7;
                } else {
                    index = 5;
                }
                luck.prize = luck.nn;//最终中奖位置
            } else {
                if (luck.times > luck.cycle + 10 && ((luck.prize == 0 && luck.index == 7) || luck.prize == luck.index + 1)) {
                    luck.speed += 110;
                } else {
                    luck.speed += 20;
                }
            }
            if (luck.speed < 40) {
                luck.speed = 40;
            };
            luck.timer = setTimeout(roll, luck.speed);
            // luck.timer = setTimeout('function(){roll(id)}',luck.speed);
        }
        return false;
    }
    var click = false;
    window.onload = function () {
        luck.init('luck');
        $('.clickImg').click(function () {
            if(click){
                return false;
            }else{
                $.ajax({
                    url: '/api/mall/promotion/prize?token='+token,
                    type: 'post',
                    async: true,
                    data: {},
                    dataType: 'json',
                    success: function (res) {
                        $(res).each(function (inx, item) {
                            if (item.status == 403) {
                                $('.pop-box span').text(item.message);  
                                $('.pop').css('height',$(document).height())
                                setTimeout(function(){
                                    $('.pop').show();
                                },50)
                                document.body.style.overflow='hidden';
                            } else {
                                var id;
                                var name;
                                $(res.data).each(function (inx, items) {
                                    id = items.id;
                                    name = items.name
                                })
                                if (click) {
                                    return false;
                                }
                                else {
                                    if(id==1){
                                        luck.speed = 100;
                                        luck.nn = id - 1;
                                        roll();
                                        click = true;
                                        $('.pop-box span').text(name);
                                        return false;
                                    }else{
                                        luck.speed = 100;
                                        luck.nn = id - 1;
                                        roll();
                                        click = true;
                                        $('.pop-box span').text('恭喜您抽中'+name);
                                        return false;
                                    }
                                }

                            }
                        });
                        $.ajax({
                            url: '/api/mall/promotion/prize?token='+token,
                            type: 'get',
                            async: true,
                            data: {},
                            dataType: 'json',
                            success: function (res) {
                                $('.prize-one span').html('');
                                $('.prize-one span').append(
                                    '<img src="/assets/mall/img/trophy13.png">幸运值：' + res.data.lucky_value + ''
                                );
                                $('.clickImg').html('');
                                if(res.data.cost_coin == 0){
                                    $('.clickImg').append(
                                        '<p>免费</p>'
                                    );
                                }else{
                                    $('.clickImg').append(
                                        '<p>'+res.data.cost_coin+'金币</p>'
                                    );
                                }
                            },
                            error: function () {
                                console.log(error)
                            }
                        })
                    },
                    error: function () {
                        $('.pop-box span').text('登录后才能抽奖呦！');  
                        $('.pop').css('height',$(document).height())
                        setTimeout(function(){
                            $('.pop').show();
                        },50)
                        document.body.style.overflow='hidden';
                    }
                });
            }
        });
    }

    // 点击确定抽奖弹出框
    $('.pop-box p').click(function () {
        $('.pop').hide();
        document.body.style.overflow='auto';
        $.ajax({
            url: '/api/mall/promotion/prize?token='+token,
            type: 'get',
            async: true,
            data: {},
            dataType: 'json',
            success: function (res) {
                // 获奖名单渲染
                $('.list-header .list-info-user').html('');
                $(res.extra).each(function (inx, item) {
                    $('.list-header .list-info-user').append(
                        '<div class="item" id="'+item.id+'">'+
                            '<div class="item-left">'+
                                '<img src="https://static.tosneaker.com'+item.user_avatar+'">'+
                                '<div class="item-info">'+
                                    '<span>'+item.user_name+'</span>'+
                                    '<span>'+item.prize_name+'</span>'+
                                '</div>'+
                            '</div>'+
                            '<div class="time-info">'+
                                '<span class="time">'+item.prize_at+'</span>'+
                            '</div>'+
                        '</div>'
                    )
                })
                // $('.time').liveTimeAgo();
            },
            error: function () {
                console.log(error)
            }
        })
    })
})