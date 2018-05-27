<?php
/*
+--------------------------------------------------------------------------
|   thinkask [#开源系统#]
|   ========================================
|   http://www.thinkask.cn
|   ========================================
|   如果有兴趣可以加群{开发交流群} 485114585
|   ========================================
|   更改插件记得先备份，先备份，先备份，先备份
|   ========================================
+---------------------------------------------------------------------------
 */ 
namespace app\api\controller;
use think\Db;
use think\Cache;
class Goods extends PublicBase
{

    public function _initialize()
    {
        parent::_initialize();  
    }
    /**
     * [index 商品列表]
     * @Author   Jerry
     * @DateTime 2017-07-05T11:39:46+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function lists()
    {
        // echo 
        $cat = model('goods/goodsCat')->catAll();
        foreach ($cat as &$v) {
             $goods = model('goods/goods')->f_catid_t_goods($v['id']);
             foreach ($goods as &$vi) {
                $vi['picture'] = get_file_path($vi['picture']);
                unset($vi['id']);
                unset($vi['catid']);
             }
             unset($v['id']);
             $v['goods']  = $goods;
        }
        return  returnJson(0,'success','',$cat);
    }




    /**
     * [index 小程序首页数据]
     * @Author   wb
     * @DateTime 2017-07-11T11:39:46+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function index()
    {

        //获取首页接口  判断是否有缓存 没有缓存 重新生成缓存 如果有缓存直接调用
        if(cache('xcx_index')){
            return  returnJson(0,'success','',cache('xcx_index'));
        }else{
            //生成缓存函数
            $array = $this->cache_index();
            //如果没有缓存则抛错
            if(empty($array)){
                return  returnJson(1,'服务器暂时故障，请稍后再试','',$array);
            }
            //生成该用户数据缓存
            cache('xcx_index',$array,56000);
            return  returnJson(0,'success','',$array);
        }


    }
/**
    *首页缓存数据
    */
    public function cache_index(){
        //获取所有产品类型
        $goods = $this->getbase->getall('goods',['join'=>[['goods_cat gc','gc.id=g.catid']],'where'=>'g.status=1 and gc.status=1','alias'=>'g','field'=>'gc.sort gcsort,g.name,g.price,g.unit,g.picture,g.catid type,g.catid catid,gc.title catname,g.id','order'=>'gc.sort desc,g.sort desc']);
        $specialGoods = [];##特殊商品
        $baseGoods = [];##一般商品
        $cat = [];
        $first = [];##临时存在放第一个数据
        foreach ($goods as $k=>$v) {
            $goods[$k]['picture'] = config('domain_url').get_file_path($v['picture']);
            $goods[$k]['number'] = 0;
            
            ##分类
            $cat[$v['catid']] = $v['catname'];
            ##特殊商品
            if($v['catid']==6){
                $attr = $this->getbase->getall('goods_attr',['where'=>['goods_id'=>$v['id'],'status'=>1],'field'=>'name,value']);
                    $box_attr = [];
                    if(is_array($attr)&&count($attr)>1){
                        foreach ($attr as $ki=>$vi) {
                            if($vi['name']=="包装数"){
                                $attname = explode("\n", $vi['value']);
                                if(is_array($attname)){
                                    foreach ($attname as &$va) {
                                        if(explode("|", $va)){
                                            $e = explode("|", $va);
                                            $e[1] = str_replace("\r", "", $e[1]); ##这符拼接，方便小程序写样式
                                           $box_attr[] = $e; 
                                        }
                                    }
                                }
                                unset($attr[$ki]);## 删除包装数属性
                            }
                        }
                }
                $v['picture'] = config('domain_url').get_file_path($v['picture']);
                $v['number'] = 0;
                $v['attr'] = $attr;
                $v['box_attr'] = $box_attr;
                ##把值往前面提
                $first[] = $v;
                unset($goods[$k]);
                // array_unshift($goods, $temp);
            }
            ##特殊商品属性

        }
        // show($goods);
        ##把数据提到前面
        foreach ($first as $v) {
            array_unshift($goods, $v);
        }
        // show($goods);
        //450*150 轮播图链接 图片直接覆盖 直接写入链接地址
        $b = array(
            array('link'=>'/pages/index/index','url'=>'https://www.qiaolibeilang.com/public/xcx/lun1.png'),
            array('link'=>'/pages/index/index','url'=>'https://www.qiaolibeilang.com/public/xcx/lun2.png'),
            // array('link'=>'/pages/index/index','url'=>'https://www.qiaolibeilang.com/public/xcx/lun3.png'),
            array('link'=>'/pages/index/index','url'=>'https://www.qiaolibeilang.com/public/xcx/lun4.png'),
        );
    
        return array('data'=>$goods,'img'=>$b,'nav'=>$cat);
    }


//     /**
//     *首页缓存数据
//     */
//     public function cache_index(){
//         //获取所有产品类型
//         $cat   = model('goods/goodsCat')->catAll();
//         $menu  = array();
//         $data  = array();
//         $i     = 0;
//         $o     = 0;
//         $special = array();
//         //根据产品分类 便利后进行产品查询
//         foreach ($cat as &$v) {
//             $menu[$v['id']] = $v['title'];
//             $goods = model('goods/goods')->f_catid_t_goods($v['id']);
//             //判断如果是特殊产品则拿出 例如catid=6
//             foreach ($goods as &$vi) {
//                 if($vi['catid'] != 6){
//                     $vi['picture'] = config('domain_url').get_file_path($vi['b_picture']);
//                     $vi['type'] =$vi['catid'];
//                     $vi['back'] ='background:#fff;';
//                     $vi['number'] =0;
//                     $attr = $this->getbase->getall('goods_attr',['where'=>['goods_id'=>$vi['id']]]);
//                     $vi['attrs'] = $attr?$attr:'';
//                     if(!empty($vi)){
//                         $data[$i] = $vi;
//                         $i++;
//                     }
//                 }else{
//                     $vi['picture'] = config('domain_url').get_file_path($vi['b_picture']);
//                     $vi['type'] =$vi['catid'];
//                     $vi['back'] ='background:#fff;';
//                     $vi['number'] =0;
//                     $special[$o]  = $vi;
//                     $o++;
//                 }

//             }
//         }



//         //catid为6 为特殊产品 放在数组第一个
//         //此方法为数组排序
//         $flag=array();
//         foreach($data as $arr2){
//             $flag[]=$arr2["sort"];
//         }
//         array_multisort($flag, SORT_ASC, $data);
//         //排序后对特殊产品对数组插入
//         for($i=0;$i<count($special);$i++){
//             array_unshift($data,$special[$i]);
//         }


// //450*150 轮播图链接 图片直接覆盖 直接写入链接地址
//         $b = array(
//             array('link'=>'/pages/index/index','url'=>'https://www.qiaolibeilang.com/public/xcx/lun1.png'),
//             array('link'=>'/pages/index/index','url'=>'https://www.qiaolibeilang.com/public/xcx/lun2.png'),
//             array('link'=>'/pages/index/index','url'=>'https://www.qiaolibeilang.com/public/xcx/lun3.png'),
//             array('link'=>'/pages/index/index','url'=>'https://www.qiaolibeilang.com/public/xcx/lun4.png'),
//         );



//         return array('data'=>$data,'img'=>$b,'nav'=>$menu);
//     }



    /**
     * [index 小程序确认订单提交数据]
     * @Author   wb
     * @DateTime 2017-07-11T11:39:46+0800
     * @Example  eg:
     * @return   用户在首页提交确认订单数据。入库返回订单id
     */
    public function pay_order()
    {
        $p_id  = input('post.id');
        $p_num = input('post.num');
        $uid = input('post.uid');
        //提交确认订单信息 产品id  数量  以及用户id

        $data['id']  = $p_id;
        $data['num'] = $p_num;
        $data['uid'] = $uid;

        $rule = [
            'id'   => 'require',
            'num'  => 'require',
            'uid'  =>'require'
        ];


        $msg = [
            'id.require' => '产品id必须要有',
            'num.require' => '产品数量必须要有',
            'uid.require' => '用户id必须要有',
        ];

        //tp5验证规则
        $result   = $this->validate($rule,$data,$msg);
        if($result !== true){
            return  returnJson(1,$result,'');
        }


        //用户确认订单 提交数据信息
        $id = array_filter(explode(',',$p_id));
        $num = array_filter(explode(',',$p_num));

        //根据循环产品 及数量来查询产品的信息
        $data = array();
        $i    = 0;
        $good_name = '';
        foreach ($id as $k => $v){
                $goods = Db::name('goods')->where('id', $v)->find();
                $data[$i]['num'] = $num[$i];
                $data[$i]['price'] = $goods['price'];
                $good_name .= $goods['name'].',';
                $i++;
        }

        //查询产品信息后计算价格
            $price = 0; // 总价格
            $c_num = 0; //总数量
            foreach ($data as $k => $v) {
                $price = $price + ($v['num'] * $v['price']);
                $c_num += $v['num'];
            }

            //生成优惠券a查看用户是否有自己的优惠券
        $cou_pon = Db::name('users_coupon')->where('u_id='.$uid.' and status=0 ')->order('id desc')->find();
            //如果有优惠券则判断 用户是否达到使用优惠券的规则
        if(!empty($cou_pon)){

            $coupon = Db::name('coupon')->where('l_price<='.$price.' and id='.$cou_pon['c_id'].' and status=1')
                ->find();
            //如果有则使用
            $discount = array('d_id'=>$coupon['id'],'price'=>$coupon['discount']); //优惠券
        }else{
            $coupon = Db::name('coupon')->where('l_price<='.$price.' and status=1 and type = 0')->order('l_price desc')->select();
                    //如果没有则使用满减优惠券
            if(!empty($coupon)){
                foreach($coupon as $k => $v){

                    if($this->time_tran(strtotime($v['expire'].' 00:00:00')) != '已过期' ){
                        $discount = array('d_id'=>$v['id'],'price'=>$v['discount']); //优惠券

                        continue;
                    }else{
                        //如果没有满减可以用则直接返回没有优惠券
                        $discount = array('d_id'=>0,'price'=>0); //优惠券
                    }
                }
            }
        }
        //再次判断如果没有则 0
       if(empty($discount['d_id'])){
            $discount = array('d_id'=>0,'price'=>0); //优惠券
        }

        //生成配送信息
        $exp = Db::name('order_express')->where('amount_reached<='.$price.' and status=1 ')->order('amount_reached desc')->select();
        ##商务洗免运费（运营需求）;
        $idArr = explode(",", input('id'));
        $ids = trim(input('id'),',');
        ##查分类，如果分类有为6的，免运费
        $cats = $this->getbase->getall('goods',['where'=>"id in($ids)",'field'=>'catid']);
        $catidArr = [];
        foreach ($cats as $v) {
            $catidArr[] = $v['catid'];
        }
        ##如果分类为6（商务洗）免运费
        if(in_array(6, $catidArr)){
            //计算配送价格为订单总价
            $price    = $price;
            $ps_price = 0.00;//配送费
            $expres_fee_remark  = '商务洗免运费';
            $f_price  = $price-$discount['price']; //实际付费价格aaa 
        }else{
            ##运费规则，正常规则算法
            foreach ($exp as $k => $v) {
                if($price>=$v['amount_reached']){
                    //计算配送价格为订单总价
                    $price    = $price+$v['express_fee'];
                    $ps_price = $v['express_fee'];//配送费
                    $expres_fee_remark  = $v['remark'];
                    $f_price  = $price-$discount['price']; //实际付费价格aaa
                    break; 
                }
               
            }
        }


        $nowtime=time();   //获取现在的时间戳
        $starttime=mktime(0,0,0,date("m"),date("d"),date("Y"));   //获取当月的第一天0时

        $map['create_time']=array('between',array($starttime,$nowtime));

        $order = Db::name('order')->where($map)->count();

        //生成件号
        $c_order  = strlen($order+1);
        if($c_order == 1){
            $order = '000'.$order;
        }elseif($c_order == 2){
            $order = '00'.$order;
        }elseif($c_order == 3){
            $order = '0'.$order;
        }elseif($c_order == 4){
            $order = $order;
        }
        //产品数量
        if(strlen($c_num)== 1) {
         $c_num  = '0'.$c_num;
        }

        //生产订单号  随机数4哥 时间   订单件号 和数量
        $order_number = 'SX'.mt_rand(100,999).date('Ymd',time()).$order.$c_num;

        $array = array('order_number'=>$order_number,
            'remarks'=>'无',
            'u_id'=>$uid,
            'user_address'=>'无',
            'user_phone'=>'无',
            'user_name'=>'无',
            'good_name'=>$good_name,
            'order_price'=>$f_price,
            'good_price'=>$price,
            'coupon_id'=>$discount['d_id'],
            'coupon_price'=>$discount['price'],
            'distribution_price'=>$ps_price,
            'distribution_id'=>1,
            'good_id'=>$p_id,
            'good_num'=>$p_num,
            'create_time'=>time(),
            'update_time'=>time(),
            'status'=>-1,
            'expres_fee_remark'=>$expres_fee_remark,
            'take_time'=>'无',
            'give_time'=>'无'

        );
        //添加生成订单
        $db = Db::name('order')->insert($array);
        $o_id = Db::name('order')->getLastInsID();
        //件号 三位数为产品id  2位数为用户提交的产品件数 -后为序号
        if($o_id){
            return  returnJson(0,'success','',$o_id);
        }

    }

    /**
     * [index 时间差计算当前订单时间具体当前时间有多久]
     * @Author   wb
     * @describe  eg:传入时间戳
     * @return 距离now的时间差
     */
    public function time_tran($the_time) {
        $now_time = date("Y-m-d H:i:s", time());
        $now_time = strtotime($now_time);
        $show_time = $the_time;
        $dur = $show_time - $now_time ;
        if ($dur < 0) {
            return '已过期';
        } else {
            if ($dur < 60) {
                return $dur . '秒后';
            } else {
                if ($dur < 3600) {
                    return floor($dur / 60) . '分钟后';
                } else {
                    if ($dur < 86400) {
                        return floor($dur / 3600) . '小时后';
                    } else {
                        return floor($dur / 86400) . '天后';
                    }
                }
            }
        }
    }

}
