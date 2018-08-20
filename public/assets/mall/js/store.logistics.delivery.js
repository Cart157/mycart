$(function(){
    // 三级联动ajax
    $.ajax({
        url:'/api/location',
        type:'get',
        async:true,
        data:{},
        success:function(res){
            $(res.data).each(function(inx,item){
                $('.province').append(
                    '<option value="'+item.area_code+'">'+item.area_name+'</option>'
                )
            })
        },
        error:function(){

        }
    });
    $('.province').on('click',function(){
        var code = $(this).find('option:checked').attr('value');
        $.ajax({
            url:'/api/location?code='+code,
            type:'get',
            async:true,
            data:{},
            success:function(res){
                $('.city').html('');
                $('.district').html('');
                $(res.data).each(function(inx,item){
                    $('.city').append(
                        '<option value="'+item.area_code+'">'+item.area_name+'</option>'
                    )
                })
            },
            error:function(){

            }
        })
    })
    $('.city').on('click',function(){
        var code = $(this).find('option:checked').attr('value');
        $.ajax({
            url:'/api/location?code='+code,
            type:'get',
            async:true,
            data:{},
            success:function(res){
                $('.district').html('');
                $(res.data).each(function(inx,item){
                    $('.district').append(
                        '<option value="'+item.area_code+'">'+item.area_name+'</option>'
                    )
                })
            },
            error:function(){

            }
        })
    })
    $('.first-revise').on('click',function(){
        $('.modify').removeClass('hide');
        var address = $('#first-bottom').find('span').eq(1).text();
        var postcode = $('#first-bottom').find('span').eq(2).text();
        var name = $('#first-bottom').find('span').eq(3).text();
        var phone = $('#first-bottom').find('span').eq(4).text();
        $('.modify').find('p:eq(0) span input').val(address);
        $('.modify').find('p:eq(1) input').val(name);
        $('.modify').find('p:eq(3) input').val(phone);
        $('.modify').find('p:eq(4) input').val(postcode);
        // 地区
        var area = $('#first-bottom').find('span:eq(5) input').val();
        var province = area.substr(0,2)+'0000';
        var city = area.substr(0,4)+'00';
        $('.modify .province option').each(function(){
            var choose = $(this).attr('value');
            if(province == choose){
                $(this).attr('selected','selected')
            }
        })
        $.ajax({
            url:'/api/location?code='+province,
            type:'get',
            async:true,
            data:{},
            success:function(res){
                $(res.data).each(function(inx,item){
                    $('.city').append(
                        '<option value="'+item.area_code+'">'+item.area_name+'</option>'
                    )
                })
            },
            error:function(){

            }
        })
        $.ajax({
            url:'/api/location?code='+city,
            type:'get',
            async:true,
            data:{},
            success:function(res){
                $(res.data).each(function(inx,item){
                    $('.district').append(
                        '<option value="'+item.area_code+'">'+item.area_name+'</option>'
                    )
                    $('.modify .district option').each(function(){
                        var choose = $(this).attr('value')
                        if(area == choose){
                            $(this).attr('selected','selected')
                        } 
                    })
                })
            },
            error:function(){

            }
        })  
    })
    $('.modify i').on('click',function(){
        $('.modify').addClass('hide');
    })
    $('.modify').on('click','.confirm',function(){
        var province = $('.modify').find('p:eq(0) .province option:selected').text();
        var city = $('.modify').find('p:eq(0) .city option:selected').text();
        var district = $('.modify').find('p:eq(0) .district option:selected').text();
        var area = province+city+district;
        var newArea = $('.modify .district option:selected').val();
        var newAddress = $('.modify').find('p:eq(0) span input').val();
        var newPostcode = $('.modify').find('p:eq(4) input').val();
        var newName = $('.modify').find('p:eq(1) input').val();
        var newPhone = $('.modify').find('p:eq(3) input').val();
        $('#first-bottom').find('span:eq(0)').text(area);
        $('#first-bottom').find('span:eq(0)').append(
            '<input type="hidden" name="area_info" value="'+area+'">'
        )
        $('#first-bottom').find('span').eq(1).text(newAddress);
        $('#first-bottom').find('span:eq(1)').append(
            '<input type="hidden" name="address" value="'+newAddress+'">'
        )
        $('#first-bottom').find('span').eq(2).text(newPostcode);
        $('#first-bottom').find('span:eq(2)').append(
            '<input type="hidden" name="zip_code" value="'+newPostcode+'">'
        )
        $('#first-bottom').find('span').eq(3).text(newName);
        $('#first-bottom').find('span:eq(3)').append(
            '<input type="hidden" name="name" value="'+newName+'">'
        )
        $('#first-bottom').find('span').eq(4).text(newPhone);
        $('#first-bottom').find('span:eq(4)').append(
            '<input type="hidden" name="mb_phone" value="'+newPhone+'">'
        )
        $('#first-bottom').find('span:eq(5) input').val(newArea);
        $('.modify').addClass('hide');
    })
})
