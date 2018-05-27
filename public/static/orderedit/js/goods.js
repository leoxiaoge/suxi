$(function(){

    // ##点击标签
        $('li.tag').click(function(event) {
            var id = $(this).attr('data-id');
            $(this).addClass('select').siblings('li').removeClass('select');
            $('#tag_id_'+id).show().siblings('.cat-left').hide();
            $('#tag_id_'+id).find('li').eq(0).click()
        });


    // ##点击分类
    $('.cat-left li').click(function(event) {
            var id = $(this).attr('data-id');
            $(this).addClass('select').siblings('li').removeClass('select');
            $('.cat_id_'+id).show().siblings('.goods').hide();
        });

    })

    //##商品加减
    $('.increase').click(function(event) {
        var num= $(this).siblings('.num').html();
        var id = getDataInfo($(this),'data-id');
        var name = getDataInfo($(this),'data-name');
        var number = getDataInfo($(this),'data-number');
        var price = getDataInfo($(this),'data-price');
        var img = getDataInfo($(this),'data-img');
        
        num = parseInt(num)
        num++
        $(this).siblings('.num').html(num);
        
        // ##判断此商品是否在购物蓝中存在
        if($('.goods_car_'+id).attr('class')){
             // ##购物车中有此商品
            $('.goods_car_'+id).find('.car_num').html(num)
        }else{
            // ##购物车中没有此商品
            addCarGoods(id,name,num,price,img);
        }
        
        car_goods(id);
        car_goods_num(id);
        
        
        car_price_sum(id)

    });

    /**
     * [car_goods 购物车商品显示]
     * @Author   WuSong
     * @DateTime 2017-10-23T15:31:22+0800
     * @Example  eg:
     * @return   {[type]}                 [description]
     */
    function car_goods(id){
        //商品单价
        var good_price = $(".goods .goods_list_"+id).find('.price_goods').html()
        //商品数量
        var good_num = parseInt($('.goods_car_main .goods_car_'+id).find('.car_num').html())
        //商品总价
        var good_price_all = good_price*good_num
        
        $('.goods_car_main .goods_car_'+id).find('.price_info').html('￥'+good_price_all+'.00');
    }

    /**
     * [car_price_sum 商品价格统计]
     * @Author   WuSong
     * @DateTime 2017-10-23T17:35:12+0800
     * @Example  eg:
     * @return   {[type]}                 [description]
     */
    function car_price_sum(id){
        var price_sum = 0;
         $('.goods_car_main li').each(function() {
             price_sum += ($('.price_info',this).text().replace('￥','')-0);
         });
         $('.new').html('￥'+price_sum+'.00')
    }

    /**
     * [car_goods_num 购物车商品数量计算]
     * @Author   WuSong
     * @DateTime 2017-10-23T15:26:40+0800
     * @Example  eg:
     * @return   {[type]}                 [description]
     */
    function car_goods_num(id){

        var sum = 0;
        var goods_list = $('.goods_car_main li').length;
        for (var i = 0; i < goods_list; i++) {
          sum +=   $('.goods_car_main li').eq(i).find('.car_num').text()*1;
            
        };
        $('.num_all').html(sum)
    }
    //商品减少
    $('.reduce').click(function(event) {
        var num=$(this).siblings('.num').html();
        var id = getDataInfo($(this),'data-id');
        num = parseInt(num)
        num--
        if(num <1){
            num=0;
            // ## 删除购物车里面的商品
            removeCarGoods(id)
        }
        $(this).siblings('.num').html(num);
        $('.goods_car_'+id).find('.car_num').html(num);
        
        car_goods(id);
        
        
        car_goods_num(id);
       
        
       car_price_sum(id)
    });


    /**
     * [goodsListNum 商品列表改变数量]
     * @Author   WuSong
     * @DateTime 2017-10-21T17:25:05+0800
     * @Example  eg:
     * @param    {[type]}                 id     [description]
     * @param    {[type]}                 number [description]
     * @return   {[type]}                        [description]
     */
    function goodsListNum(id,number){

        $(".goods .goods_list_"+id).find('.num').html(number);
    }

    /**
     * [removeCarGoods 删除商品]
     * @Author   WuSong
     * @DateTime 2017-10-21T15:19:08+0800
     * @Example  eg:
     * @param    {[type]}                 param [clss元素]
     * @return   {[type]}                       [description]
     */
    function removeCarGoods(id){
        $('.goods_car_'+id).remove()
    }
    /**
     * [shopcar 购物车]
     * @Author   WuSong
     * @DateTime 2017-10-21T14:55:59+0800
     * @Example  eg:
     * @param    {[type]}                 name   [description]
     * @param    {[type]}                 number [description]
     * @param    {[type]}                 price  [description]
     * @param    {[type]}                 img    [description]
     * @return   {[type]}                        [description]
     */
    function addCarGoods(id,name,number,price,img){
        var html = '<li class = "goods_car_'+id+'" data-id="'+id+'">\
                <div class="left">\
                    <img src="'+img+'" />\
                    <span>'+name+'</span>\
                </div>\
                <div class="right">\
                    <div class="price_info">￥'+price+'</div>\
                    <div class="number-bar">\
                        <img src="/public/static/orderedit/img/-.svg" class="car_reduce" />\
                        <div class="car_num">'+number+'</div>\
                        <img src="/public/static/orderedit/img/plus.svg" class=" car_increase"  />\
                    </div>\
                </div>\
            </li>';
        $('.goods_car_main').append(html);
    }
    /**
     * [getDataInfo 获得数据信息]
     * @Author   WuSong
     * @DateTime 2017-10-21T15:03:21+0800
     * @Example  eg:
     * @param    {[type]}                 name [description]
     * @return   {[type]}                      [description]
     */
    function getDataInfo(clicks,name){
        return clicks.siblings('.num').attr(name);

    }



    function ball(){
            var bal=$('.mark-footer').attr('title');
            if(bal == 1){
                $('.mark-footer').hide().attr('title',0);
            }else{
                $('.mark-footer').show().attr('title',1);
            }
        }
        $('.menu-left>li ,.menu-top>li').on('click',function(){
            $(this).parents('ul').find('li').removeClass('select');
            $(this).addClass('select');
        })  

    //购物车增减
    $('.goods_car_main').on('click', '.car_increase', function(event) {

        var num= parseInt($(this).siblings('.car_num').html());
        var id = $(this).parents('li').attr('data-id');

        number =num+1;

        $(this).siblings('.car_num').html(number);
        goodsListNum(id,number)
        
        car_goods(id);

        car_goods_num(id);
       car_price_sum(id)

        
    });
    // ##购物车减商品
    $('.goods_car_main').on('click', '.car_reduce', function(event) {
        var num= parseInt($(this).siblings('.car_num').html());
        var id = $(this).parents('li').attr('data-id');
        number =num-1;
        if(number <1){
            number=0;
            removeCarGoods(id)

        }
        $(this).siblings('.car_num').html(number);
        //## 修改商品列表数量
        goodsListNum(id,number)

        car_goods(id);
        
        car_goods_num(id);
        
        
        car_price_sum(id)

    });

