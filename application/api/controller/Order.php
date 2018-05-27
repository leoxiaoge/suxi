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
class Order extends PublicBase
{
    //设置上传图片的大小
    public $img_ad_width = '100';     // 品牌宣传图的宽度
    public $img_ad_height = '150';    // 品牌宣传图的高度
    public $img_save_path ;

    public function _initialize()
    {
        $this->img_save_path=config('save_img_path');
        parent::_initialize();
    }




    /**
     * [index 确认订单数据]
     * @Author   wb
     * @return   提交订单信息id 返回订单主要信息
     */
    public function pay_order()
    {
        $o_id = input('post.order_id');

        $data['uid']  = $o_id;

        $rule = [
            'uid'  => 'require|number|length:1,11',
        ];



        $msg = [
            'id.length'     => '用户id长度错误',
            'id.number'   => '用户id必须为数值',
            'id.require' => '用户id必须要有',
        ];

        //拿到订单id我们查出订单信息病显示
        $result   = $this->validate($rule,$data,$msg);
        if($result !== true){
            return  returnJson(1,$result,'');
        }


        $order = Db::name('order')->where('id', $o_id)->find();
        //是否能查询出订单信息如不能则无效订单
        if(empty($order)){
            return  returnJson(2,'无此订单，查询失败','');
        }
        $g_id = array_filter(explode(',', $order['good_id']));
        $num = array_filter(explode(',', $order['good_num']));
        $list = array();
        $i = 0;
        //获取产品详细信息
        foreach ($g_id as $k => $v) {

                $goods = Db::name('goods')->where('id', $v)->find();
                if ($goods) {
                    $list[$i]['img'] = config('domain_url') . get_file_path($goods['picture']);
                    $list[$i]['num'] = $num[$i];
                    $list[$i]['name'] = $goods['name'];
                    $list[$i]['price'] = $goods['price'];
                    $i++;
                }

        }


        //生成用户下单选择时间
        $time = $this->order_time();
        $data = array();
        //查询用户地址信息
        $address = Db::name('address')->where('uid='.$order['u_id'].' and stat=0')->order('id desc')->select();
        //获取用户地址信息 包含手机号码
        if($address[0]['u_phone']){
            $data['address_id'] = $address[0]['id'];
            $data['address'] = $address[0]['address_info'];
            $data['phone'] = decode($address[0]['u_phone']);
            $data['name'] = $address[0]['u_name'];
            $data['is_phone'] = 0;
        }else{
            //如果没有将把用户绑定手机号填入
            $user_wx = Db::name('users_wx')->field('name as u_name,phone as u_phone')->where('id',$order['u_id'])->find();
            if($user_wx['u_phone']){
                $data['phone'] = decode($user_wx['u_phone']);
                $data['is_phone'] = 2;
                $data['name'] = urldecode($user_wx['u_name']);
            }else{
                //没有任何信息 前台做处理
                $data['phone'] = ' ';
                $data['is_phone'] = 1;
                $data['name'] = ' ';
                $data['msg']  = '请添加姓名与手机号~';
            }
            $data['address'] = ' ';
        }
        //返回用户选择时间
        $data['time'] = $time;
        //产品信息列表
        $data['list'] = $list;
        //配送价格
        $data['ps_price'] = $order['distribution_price'];

        //查询订单配送表
        $exp = Db::name('order_express')->where('amount_reached<='.$order['good_price'].' and status=1 ')->order('amount_reached desc')->select();
        //配送信息
        $data['ps_info'] = $exp;
        $cache = Cache::get('coupon'.$order['u_id']);
        //判断如果没有缓存则生成缓存
        if(empty($cache)){
          $cache =  $this->order_cache_coupon($order['u_id']);
        }
        //查询订单优惠券信息返回 提示内容
        foreach ($cache as $k => $v){
            if($v['id'] == $order['coupon_id']){
               $data['discount_title'] = '满￥'.$v['l_price'].'.00元立减';
            }
        }

        $data['discount_id'] = $order['coupon_id'];
        $data['discount_price'] = $order['coupon_price'];
        $data['f_price'] = $order['order_price'];
        $data['price'] = $order['good_price'];
        $data['remarks'] = $order['remarks'];


        return returnJson(0, 'success', '', $data);

    }

    /**
     * [index 缓存订单优惠券数据]
     * @Author   wb
     * @return   缓存订单优惠券数据
     */
    public function order_cache_coupon($uid){
            //判断优惠券缓存是否存在 如果不存在需要重新获取
            $array = $this->cache_coupon($uid);
            if(empty($array)){
                return  returnJson(1,'服务器暂时故障，请稍后再试','',$array);
            }
            Cache::set('coupon'.$uid,$array,8600);
            return $array;

    }

    /**
     * [index 获取该用户的优惠券]
     * @Author   wb
     * @return   提交订单信息id 返回订单主要信息
     */
    public function coupon(){
        $uid = intval(input("post.uid"));
        $data['uid'] = $uid;


        $rule = [
            'uid'  => 'require',
        ];

        $result   = $this->validate($rule,$data);
        if($result == false){
            return  returnJson(1,'参数有误','',$result);
        }
        //获取该用户的优惠券 存入缓存
        $cache = Cache::get('coupon'.$uid);
        if(!empty($cache)){
            return  returnJson(0,'success','',$cache);
        }else{
            //获取用户的缓存数据 重新加入缓存
            $array = $this->cache_coupon($uid);
            if(empty($array)){
                return  returnJson(1,'服务器暂时故障，请稍后再试','',$array);
            }
            Cache::set('coupon'.$uid,$array,8600);
            return  returnJson(0,'success','',$array);
        }

    }

    /**
     * [index 缓存优惠券]
     * @Author   wb
     * @return   提交订单信息id 返回订单主要信息
     */
    public function cache_coupon($uid){

        //缓存用户优惠券逻辑 获取用户优惠券及 满减可用优惠券
        $coupon = Db::name('users_coupon')->where('u_id='.$uid. ' and status = 0')->order('id desc')->select();

        $cou = Db::name('coupon')->where(' type=0 and status=1')->order('id desc')->select();
        //循环将优惠券信息存入数组中
        foreach ($coupon as $k => $v){
            if($v['c_id'] > 0){

                $cou_ks = Db::name('coupon')->where('id ='.$v['c_id'])->find();

                array_push($cou,$cou_ks);
            }

        }
        $array = array();
        //获取优惠券倒计时间
        foreach($cou as $k => $v){
            $v['expire_time'] = $this->time_tran(strtotime($v['expire'].' 00:00:00'));
            $array[]=$v;
        }
        //返回数据
        return $array;
    }

    /**
     * [index 时间差计算]
     * @Author   wb
     * @describe  eg:传入时间戳
     * @return 距离now的时间差
     */
    public function time_tran($the_time) {
        $now_time = date("Y-m-d H:i:s", time());
        //echo $now_time;
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

    /**
     * [index 确认订单数据 时间输出]
     * @Author   wb
     * @return   提交订单信息id 返回订单主要信息
     */
    public function order_time(){
        //生成下单时间 下单前30分钟 送货时间间隔5小时
        $q_time = array();
        $g_time = array();
                $t_timet = preg_replace('/^0+/', '', date('Y-m-d  H:i', strtotime("+" . 30 . " minute")));

                $q_time[] = array('t' => $t_timet, 'd' => 0);
                $t_timeg = preg_replace('/^0+/', '', date('Y-m-d H:i', strtotime("+" . 5 . " hour")));
                $g_time[] = array('t' => $t_timeg, 'd' => 0);

        return array('q_time' => $q_time, 'g_time' => $g_time);
    }


    /**
     * [index 确认订单数据 时间输出送货时间]
     * @Author   wb
     * @return   提交订单信息id 返回订单主要信息
     */
    public function gettime(){
        $data['fal_q'] = input("post.fal_q");

        $rule = [
            'fal_q' => 'require'
        ];

        $msg = [
            'fal_q.require' => '送货时间必须要有',
        ];


        $result   = $this->validate($rule,$data,$msg);
        if($result !== true){
            return  returnJson(1,$result,'');
        }



        $time =  date("Y").'-'.$data['fal_q'].':00';
        $time = str_replace("月","-",$time);
        $time = str_replace("日","",$time);
        $now  = strtotime($time);


        $difference = $now-time();

        $hour = $difference/3600;


        //返回送货时间
        $g_time = array();
        $len = 22;

        $p = 5+floor($hour);
        for ($i = 0; $i < $len; $i++) {

            if ($i < $len) {
                if ($i == 1) {
                    $d = 1;
                } elseif ($i == 2) {
                    $d = 2;
                } elseif ($i == 3) {
                    $d = 3;
                } else {
                    $d = 0;
                }
                $t = $p * 1;
                $t_time = preg_replace('/^0+/', '', date('m月d日 H:i', strtotime("+" . $t . " hour")));
                $g_time[] = array('t' => $t_time, 'd' => $d);
                $p++;
            }
        }

        return  returnJson(0,'成功','',$g_time);


    }


    /**
     * [index   提交确认订单数据]
     * @Author   wb
     * @return   提交订单信息跳转支付页面进行支付
     */
    public function pay()
    {
        $oid = intval(input("post.oid"));
        $data['user_phone'] = input("post.phone");
        $data['user_name'] = input("post.name");
        $data['remarks'] = input("post.remarks");
        $data['user_address'] = input("post.address");
        $data['give_time'] = input("post.gt");
        $data['take_time'] = input("post.qt");
        $data['address_id'] = input("post.address_id");




        //确认订单并且下单支付 用户准备下单 进行数据修改

        $rule = [
            'user_phone'  => 'require',
            'user_name' => 'require',
            'remarks' => 'require',
            'user_address' => 'require',
            'give_time' => 'require',
            'take_time' => 'require',
            'address_id' => 'require'
        ];

        $result   = $this->validate($rule,$data);
        if($result == false){
            return  returnJson(1,'参数有误','',$result);
        }
        $data['user_phone'] = encode($data['user_phone']);
        //用户进入下单页则把订单状态改成0
        $data['status'] = 0;
        $data['coupon_id'] = input("post.discount_id");
        $data['coupon_price'] = input("post.discount_price");
        $order  = Db::name('order')->where('id', $oid)->find();
        $cou_ks = Db::name('coupon')->where('id ='.$data['coupon_id'])->find();
        //判断是否优惠券可使用
        if($cou_ks['discount'] ==$data['coupon_price'] ){
            $data['order_price'] = $order['good_price']-$order['distribution_price']-$cou_ks['discount']+$order['distribution_price'];
        }

        Db::table('qlbl_order')
            ->where('id', $oid)
            ->update($data);

        //新增缓存

    }


    /**
     * [index 用户个人订单缓存]
     * @Author   wb
     * @describe  eg:传入用户id
     * @return  返回用户评论列表
     */
    public function cache_myorder($t,$u_id){
        //个人用户订单缓存 详情见user内有注释
        if($t ==1){
            $order = Db::table('qlbl_order')->field('id,order_number as order_num,create_time as ctime,status as o_type,good_name as order_info,order_price,good_id,good_num')->where('id ='.$u_id)->select();
        }elseif($t == 2){
            $order = Db::table('qlbl_order')->field('id,order_number as order_num,create_time as ctime,status as o_type,good_name as order_info,order_price,good_id,good_num')->where('u_id ='.$u_id.' and status <12 and status >= 0')->order('id desc')->select();
        }



        $array = array();
        foreach ($order as $k => $v){

            $array[$v['id']]['id'] = $v['id'];
            $array[$v['id']]['order_num'] = $v['order_num'];
            $array[$v['id']]['order_info'] = $v['order_info'];
            $array[$v['id']]['order_price'] = $v['order_price'];

            $array[$v['id']]['ctime'] = date('Y-m-d' ,$v['ctime']);
            $array[$v['id']]['s'] = $v['o_type'];
            if($v['o_type'] >= 9  ){

                $remarks = Db::table('qlbl_order_remark')->field('level_express,level_express_u,level_neat,level_packing')->where('order_id',$v['order_num'])->find();
                if(!empty($remarks) and $v['o_type'] != 11 ){
                    $array[$v['id']]['is_eval'] = 1;
                    $stars =  $remarks['level_express']+$remarks['level_express_u']+$remarks['level_neat']+$remarks['level_packing'];

                    $array[$v['id']]['stars'] = $stars/4;
                    $array[$v['id']]['o_type'] = 2;

                }elseif(!empty($remarks) and $v['o_type'] == 11 ){
                    $array[$v['id']]['is_eval'] = 1;
                    $array[$v['id']]['stars'] = 0;
                    $array[$v['id']]['o_type'] = 2;

                }elseif(empty($remarks) and $v['o_type'] != 11){
                    $array[$v['id']]['o_type'] = 1;
                    $array[$v['id']]['is_eval'] = 0;
                    $array[$v['id']]['stars'] = 0;
                }elseif(empty($remarks) and $v['o_type'] == 11){
                    $array[$v['id']]['o_type'] = 2;
                    $array[$v['id']]['is_eval'] = 0;
                    $array[$v['id']]['stars'] = 0;
                }
            }else{
                $array[$v['id']]['o_type'] = 0;
                $array[$v['id']]['is_eval'] = 0;
                $array[$v['id']]['stars'] = 0;
            }

            $g_id = array_filter(explode(',', $v['good_id']));
            $num = array_filter(explode(',', $v['good_num']));
            $list = array();
            $g_num = 0;
            $i = 0;
            foreach ($g_id as $ks => $vs) {

                //if($vs == 999999){
                //    $list[$i]['img'] = 'https://demo.thinkask.cn/public/xcx/4he1.png';
                //    $g_num =  $g_num + $num[$i];
                //
                //    $i++;
                //}else {
                    $goods = Db::name('goods')->where('id', $vs)->find();
                    if ($goods) {
                        $list[$i]['img'] = config('domain_url') . get_file_path($goods['picture']);
                        $g_num = $g_num + $num[$i];
                        $i++;
                    }
               // }
            }

            $array[$v['id']]['list_img'] = $list;
            $array[$v['id']]['g_num'] = $g_num;

        }
        return $array;

    }


    /**
     * [index   发起支付页面]
     * @Author   wb
     * @return   提交订单信息跳转支付页面进行支付 传入用户id与订单id
     */
    public function pay_info(){

        $o_id = intval(input('post.oid'));
        $uid  = intval(input('post.uid'));
        //Cache::rm('data'.$uid);'coupon'.$uid
        //发起获取订单信息 例如价格 并返回订单价格
        if($o_id<1) return ;
        $order = Db::name('order')->where('id='.$o_id.' and u_id='.$uid)->find();

        $data['price'] = $order['order_price'];
        if(empty($data['price'])){
            return returnJson(1, 'success', '', $data); 
        }else{
          return returnJson(0, 'success', '', $data);  
        }

    }


    /*
     * 订单缓存
     * */
    public function order_cache(){
        //订单缓存 判断订单缓存是否有效
        $uid  = intval(input('post.uid'));
        $o_id = intval(input('post.oid'));
        if($uid<1) return ;

        $cache = Cache::get('data'.$uid);
        //如果订单缓存不存在则重新生成
        if(!empty($cache) ){
            if($o_id > 1){
                $res = $this->cache_myorder(1,$o_id);

                $ret = array_shift($res);
                $cache[$ret['id']] = $ret;

                Cache::set('data'.$uid,$cache,27200);
                return returnJson(1, 'c', '', $cache);
            }
        }else{
            $array = $this->cache_myorder(2,$uid);
            Cache::set('data'.$uid,$array,27200);
            return returnJson(1, 'ccc', '', $array);
        }

    }


    /**
     * [index   支付页面]
     * @Author   wb
     * @return   调用微信sdk进行支付  传入订单信息
     */
    public function payment(){
        include EXTEND_PATH.'WeixinPay.php';
    
        $appid=config('appid_one');//小程序appid
        $openid= input('post.id');//用户openid
        $oid   = intval(input('post.oid'));//订单id
        $mch_id=config('mch_id_one');//商户号
        $key=config('key_one');//支付平台秘钥
       // $out_trade_no = 'SX'.time().mt_rand(10,99);
        $order = Db::name('order')->where('id', $oid)->find();
        $total_fee = $order['order_price']*100;
        $out_trade_no = $order['order_number'];
        $body = "付款";
        //支付接口 提供所需要的参数 回调写在小程序内
        $weixinpay = new  \WeixinPay($appid,$openid,$mch_id,$key,$out_trade_no,$body,$total_fee);
        $return=$weixinpay->pay();
        return returnJson(0, 'success', '', $return);
    }

    /**
     * [monipay_ok 模拟支付成功 ==测试专用===]
     * @Author   Jerry
     * @DateTime 2017-09-07T16:37:45+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function moni_ok(){
           //支付成功则毁掉接口
        $oid = intval(input('post.oid'));
        //更改状态
        $data['status'] = 1;
        Db::table('qlbl_order')
            ->where('id', $oid)
            ->update($data);
        $order = Db::name('order')->where('id', $oid)->find();
        //发送短信
        pushordermsg( $order['order_number'],'您有一个新的订单','订单包含服装为：'.$order['good_name']);

        ##门店负责人的手机号
        $re = $this->getbase->getone('store_setting',['where'=>['store_id'=>1],'field'=>'notify_phone']);
        if($re['notify_phone']){
            $notify_phoneArr = explode(",", trim($re['notify_phone'],','));
            if(is_array($notify_phoneArr)){
                ##给门店管理员发短信
                foreach ($notify_phoneArr as $key => $v) {
                    // send_phone($v,[$order['order_number'],date('Y-m-d H:i:s'),$v['take_time'],$v['give_time']]); 
                    // send_phone($v,[$order['order_number'],date('Y-m-d H:i:s')]); 
                }
            }

        }
        ##门店负责人的手机号
        //写入支付日志
        $array = array('order_id' => $order['order_number'],
            'remarks' => '用户下单支付订单款',
            'pay_type' => 0,
            'pay_price' => $order['order_price'],
            'create_time' => time(),
        );
        $db = Db::name('pay_log')->insert($array);
        //写入优惠券记录
        $array = array('o_id' =>$oid,
            'c_id' => $order['coupon_id'],
            'c_discount' => $order['coupon_price'],
            'discount' => $order['order_price'],
            'c_time' => time(),
        );
        $db = Db::name('order_coupon_log')->insert($array);
        $u_coupon = Db::name('users_coupon')->where('c_id='.$order['coupon_id'].' and u_id='.$order['u_id'].' and status=0')->order('id desc')->find();
        //更改优惠券使用
        if($u_coupon){
            $data_c['status'] = 1;
            Db::table('qlbl_users_coupon')
                ->where('id', $u_coupon['id'])
                ->update($data_c);
        }
        //删除优惠券
        Cache::rm('coupon'.$order['u_id']);
        //重新获取订单缓存
        $cache = Cache::get('data'.$order['u_id']);
        if(!empty($cache)){

             $cache[$order['id']]['s'] = 1;

            Cache::set('data'.$order['u_id'],$cache,27200);

        }else{
            $array = $this->cache_myorder(2,$order['u_id']);

            Cache::set('data'.$order['u_id'],$array,27200);
           // return returnJson(0, 'success', '', $cache);
        }
        $res['id'] = $oid;
       // pushmsg('','您有新的订单啦！订单号：'.$order['order_number'].'价格为：'.$order['order_price']);
        return returnJson(0, 'success', '', $res);
    }
    
    /**
     * [index   支付成功]
     * @Author   wb
     * @return   支付成功后进行逻辑处理 返回用户id
     */
    public function pay_ok()
    {
        //支付成功则毁掉接口
        $oid = intval(input('post.oid'));
        //更改状态
        $data['status'] = 1;
        Db::table('qlbl_order')
            ->where('id', $oid)
            ->update($data);
        $order = Db::name('order')->where('id', $oid)->find();
        //发送短信
        pushordermsg( $order['order_number'],'您有一个新的订单','订单包含服装为：'.$order['good_name']);

        ##门店负责人的手机号
        $re = $this->getbase->getone('store_setting',['where'=>['store_id'=>1],'field'=>'notify_phone']);
        if($re['notify_phone']){
            $notify_phoneArr = explode(",", trim($re['notify_phone'],','));
            if(is_array($notify_phoneArr)){
                ##给门店管理员发短信
                foreach ($notify_phoneArr as $key => $v) {
                    // send_phone($v,[$order['order_number'],date('Y-m-d H:i:s'),$v['take_time'],$v['give_time']]); 
                    send_phone($v,[$order['order_number'],date('Y-m-d H:i:s')]); 
                }
            }

        }
        ##门店负责人的手机号
        //写入支付日志
        $array = array('order_id' => $order['order_number'],
            'remarks' => '用户下单支付订单款',
            'pay_type' => 0,
            'pay_price' => $order['order_price'],
            'create_time' => time(),
        );
        $db = Db::name('pay_log')->insert($array);
        //写入优惠券记录
        $array = array('o_id' =>$oid,
            'c_id' => $order['coupon_id'],
            'c_discount' => $order['coupon_price'],
            'discount' => $order['order_price'],
            'c_time' => time(),
        );
        $db = Db::name('order_coupon_log')->insert($array);
        $u_coupon = Db::name('users_coupon')->where('c_id='.$order['coupon_id'].' and u_id='.$order['u_id'].' and status=0')->order('id desc')->find();
        //更改优惠券使用
        if($u_coupon){
            $data_c['status'] = 1;
            Db::table('qlbl_users_coupon')
                ->where('id', $u_coupon['id'])
                ->update($data_c);
        }
        //删除优惠券
        Cache::rm('coupon'.$order['u_id']);
        //重新获取订单缓存
        $cache = Cache::get('data'.$order['u_id']);
        if(!empty($cache)){

             $cache[$order['id']]['s'] = 1;

            Cache::set('data'.$order['u_id'],$cache,27200);

        }else{
            $array = $this->cache_myorder(2,$order['u_id']);

            Cache::set('data'.$order['u_id'],$array,27200);
           // return returnJson(0, 'success', '', $cache);
        }
        $res['id'] = $oid;
       // pushmsg('','您有新的订单啦！订单号：'.$order['order_number'].'价格为：'.$order['order_price']);
        return returnJson(0, 'success', '', $res);

    }



    /**
     * [index   用户查看自己订单 ]
     * @Author   wb
     * @return   查看订单详情
     */
    public function order_info(){
        $oid   = intval(input('post.o_id'));
        $uid   = intval(input('post.id'));
        $order = Db::name('order')->where('id ='. $oid.' and u_id ='.$uid)->find();
        if(empty($order)){
            return returnJson(1, '无此订单', '');
        }
        //获取订单中的商品id与商品数量
        $g_id = array_filter(explode(',', $order['good_id']));
        $num = array_filter(explode(',', $order['good_num']));
        $list = array();
        $i = 0;
        //查看用户订单内的产品详情例如产品名称图片数量等等
        foreach ($g_id as $k => $v) {

                $goods = Db::name('goods')->where('id', $v)->find();
                if ($goods) {
                    $list[$i]['img'] = config('domain_url') . get_file_path($goods['picture']);
                    $list[$i]['num'] = $num[$i];
                    $list[$i]['name'] = $goods['name'];
                    $list[$i]['price'] = $goods['price'];
                    $i++;
                }

        }
        //查看订单物流信息
        $express = Db::name('order_info')->field('id,create_time,remark as remake')->where("order_id =".$oid )->order('id desc')->select();

        foreach ($express as $k => $v){
            $t_time = explode(' ',date('Y-m-d H:i:s',$v['create_time']));
            $express[$k]['create_time'] = $t_time[0];
            $express[$k]['remake'] = $v['remake'];
        }
        $data = array();
        $data['list'] = $list;
        $data['express'] = $express;
        $data['order_number'] = $order['order_number'];
        $data['address'] = $order['user_address'];
        $data['phone'] = decode($order['user_phone']);
        $data['name'] = $order['user_name'];
        $data['ps_price'] = $order['distribution_price'];
        $data['discount_id'] = $order['coupon_id'];
        $data['discount_price'] = $order['coupon_price'];
        $data['f_price'] = $order['order_price'];
        if($order['status'] == 0){
            $data['price'] = $order['good_price']-$order['coupon_price'];
        }else{
            //前台写反
           // $data['price'] = $order['good_price'];
            $data['price'] = $order['order_price'];
        }
        //$data['price'] = $order['good_price'];
        $data['remarks'] = $order['remarks'];
        $data['status'] = $order['status'];
        $data['is_complaint'] = $order['is_complaint'];
        $data['business_id'] = $order['business_id'];
        $data['create_time'] = date('Y-m-d H:i:s',$order['create_time']);


        return returnJson(0, 'success', '',$data);

    }



    /**
     * [index   订单投诉请求接口 ]
     * @Author   wb
     * @return   提交参数对订单进行验证后添加投诉信息
     */

    public function complaint(){
        $oid   = input('post.o_id');
        $uid   = intval(input('post.id'));
        $type      = input('post.type');
        $content   = input('post.content');

        $data['type'] = $type;
        $data['con']  = $content;
        $data['oid']  = $oid;
        $rule = [
            'user_phone'  => 'require|number',
            'user_name' => 'require|chsAlphaNum',
            'remarks' => 'require|chsAlphaNum',
        ];
        $msg = [

            'user_phone.require' => '用户手机号必须要有',
            'user_phone.number' => '用户手机号必须是数值',
            'user_name.chsAlphaNum' => '用户名称不符合格式',
            'user_name.require' => '用户名称必须要有',
            'remarks.require' => '用户留言必须要有',
            'remarks.chsAlphaNum' => '留言内容不符合格式',
        ];


        $result   = $this->validate($rule,$data,$msg);
        if($result !== true){
            return  returnJson(1,$result,'');
        }

        //生成订单投诉
        $status    = 0;
        $img   = ' ';
        $order = Db::name('order')->where("order_number ='". $oid."' and u_id =".$uid)->find();
        if(empty($order)){
            return returnJson(2, '无此订单', '',0);
        }
        //添加投诉信息后更改订单 完成交易
        $datas['order_id']      = $oid;
        $datas['uid']      = $uid;
        $datas['type']      = $type;
        $datas['content']      = $content;
        $datas['status']      = $status;
        $datas['img']      = $img;
        $data['create']   = time();
        $db = Db::name('order_complaint')->insert($datas);
        $comId = Db::name('order_complaint')->getLastInsID();

        $res['status'] = 10;
        $res['is_complaint'] =1;
        $dba = Db::table('qlbl_order')
            ->where('order_number', $oid)
            ->update($res);
        return returnJson(0, 'success', '',$comId);
    }

    /**
     * [index   用户查看投诉进度 ]
     * @Author   wb
     * @return   提交参数对订单编号与用户id返回订单进度所有信息
     */
    public function complaint_info()
    {
        $oid = input('post.o_id');
        $uid = intval(input('post.id'));
        $data['oid']  = $oid;
        $rule = [
            'oid' => 'require',
        ];
        $msg = [
            'oid.require' => '用户id必须要有',
        ];


        $result   = $this->validate($rule,$data,$msg);
        if($result !== true){
            return  returnJson(1,$result,'');
        }
        //$result   = $this->validate($rule,$data);
        //if($result == false){
        //    return  returnJson(1,'参数有误','',$result);
        //}
        //查询投诉情况直接返回用户对该订单投诉数据
        $order = Db::name('order_complaint')->where("order_id ='". $oid."' and uid =".$uid)->find();
        return returnJson(0, 'success', '',$order);

    }


    /**
     * [index   获取商户电话 ]
     * @Author   wb
     * @return   目前写死
     */
    public function business()
    {
        $oid = input('post.business_id');
        $order['phone'] = 18309272137;
        return returnJson(0, 'success', '',$order);
    }

    /**
     * [index   发表评论接口 ]
     * @Author   wb
     * @return   用户提交评价信息进行验证入库返回评价id
     */
    public function comment(){
        $oid   = input('post.o_id');
        $uid   = intval(input('post.u_id'));
        $content   = input('post.remarks');
        $level_express = input('post.level_express');
        $level_express_u = input('post.level_express_u');
        $level_neat = input('post.level_neat');
        $level_packing = input('post.level');
        $data['level_express'] = $level_express;
        $data['level_express_u'] = $level_express_u;
        $data['content'] = $content;
        $data['level_neat'] = $level_neat;
        $data['level_packing'] = $level_packing;
        $rule = [
            'content|评价内容' => 'require',
            'level_express' => 'require|elt:5',
            'level_express_u' => 'require|elt:5',
            'level_neat' => 'require|elt:5',
            'level_express' => 'require|elt:5'
        ];
        $msg = [
            // 'content.require' => '用户留言必须要有',
            // 'content.chsAlphaNum' => '留言内容不符合格式',
            'level_express.require' => '评星必须要有',
            'level_express.elt' => '星星数量不能大于5',
            'level_express_u.require' => '评星必须要有',
            'level_express_u.elt' => '星星数量不能大于5',
            'level_neat.require' => '评星必须要有',
            'level_neat.elt' => '星星数量不能大于5',
            'level_express.require' => '评星必须要有',
            'level_express.elt' => '星星数量不能大于5',
        ];
        ##查看是否有评价过，如果评价过不让再评
        if($this->getbase->getcount('order_remark',['where'=>['order_id'=>$oid]])>0){
            return returnJson('1','您已评价过些订单，不可以重复评价!');
        }


        $result   = $this->validate($rule,$data,$msg);
        if($result !== true){
            return  returnJson(1,$result,'');
        }

        //用户查看订单是否存在
        $order = Db::name('order')->where("order_number ='". $oid."' and u_id =".$uid)->find();
        if(empty($order)){
            return returnJson(2, 'error', '',0);
        }

        $datas['order_id']      = $oid;
        $datas['uid']           = $uid;
        $datas['remarks']       = $content;
        $datas['img']      =  ' ';
        $datas['order_time']    = time();
        $datas['level_express']        = $level_express;
        $datas['level_express_u']      = $level_express_u;
        $datas['level_neat']           = $level_neat;
        $datas['level_packing']        = $level_packing;
        $comId = $this->getbase->getadd('order_remark',$datas);
        $res['status'] = 10;//
        $dba = Db::table('qlbl_order')
            ->where('order_number', $oid)
            ->update($res);

        //添加评论后返回数据
        return returnJson(0, 'success', '',$comId);

    }


    /**
     * [index   图片上传 ]
     * @Author   wb
     * @return   传入投诉或者评论id 根据type判断是评论或投诉上传图片
    */
    public function upload(){
        if($_FILES['files']['name']){
            //类加载
            include EXTEND_PATH.'Img.php';
            //地址
            $img_path = $this->img_save_path . "/" . date('ymd', time()) . '/'; //保存原图路径
            $img_mpath = $this->img_save_path . "/m" . date('ymd', time()) . '/'; //保存缩略图路径
            $rs = new \Img();

            $img = $rs->uploadimg($_FILES['files'], $img_path, $img_mpath, $this->img_ad_width, $this->img_ad_height);
             $id   =(int) input('post.id');
            $type =(int) input('post.type');
            $table = $type==1?'order_complaint':'order_remark';##1为异常，2为评价
            $db = $this->getbase->getadd('order_remark_img',['img'=>$img,'item_id'=>$id,'item_type'=>$table]);

            return returnJson(0, 'success', '',$db);
        }else{
            return returnJson(1, 'error', '');
        }
       
    }



    /**
     * [index   取消订单 ]
     * @Author   wb
     * @return   传入订单编号和用户id取消订单。
     */
    public function cancel(){

        $oid   = input('post.o_id');
        $uid   = intval(input('post.id'));
        $order = Db::name('order')->where("status = 1 and order_number ='". $oid."' and u_id =".$uid)->find();
        if(empty($order)){
            return returnJson(1, 'error', '',0);
        }


        include_once EXTEND_PATH.'WxPay.Api.php';
        $mch_id=config('mch_id_one');//商户号
        $order = Db::name('order')->where('id', $order['id'])->find();
        $total_fee = $order['order_price']*100;
        $out_trade_no = $oid;
        include_once EXTEND_PATH."WxPay.Data.php";

        //退单接口 提供所需要的参数调用类
        // $refund_fee = $_REQUEST["refund_fee"];
        $input = new \WxPayRefund();

        $input->SetOut_trade_no($out_trade_no);
        $input->SetTotal_fee($total_fee);
        $input->SetRefund_fee($total_fee);


        $input->SetOut_refund_no($mch_id.date("YmdHis"));
        $input->SetOp_user_id($mch_id);
        $return =\WxPayApi::refund($input);


        if($return['return_code'] == 'SUCCESS' and $return['result_code'] == 'SUCCESS'){
        //退单成功后执行更改状态  添加一些数据

            $array = array('order_id' =>$oid,
                'uid' => $uid,
                'status' => 1,
                'type'=> 3,
                'remark' => encode(input('remarks')),
                'create_time' => time(),
                'order_number'=>$order['order_number']
            );
            $db = Db::name('order_info')->insert($array);

            $data['status'] =11;
            $data['msg'] ='用户取消订单';
            $db = Db::table('qlbl_order')
                ->where('id', $order['id'])
                ->update($data);

            $res['pay_type'] =1;
            $res['remarks'] ='用户取消订单';
            $db = Db::table('qlbl_pay_log')
                ->where('order_id', $order['order_number'])
                ->update($res);

            //重新获取缓存判断
            $cache = Cache::get('data'.$order['u_id']);
            if(!empty($cache)){

                $cache[$order['id']]['s'] = 11;
                $cache[$order['id']]['o_type'] = 2;

                Cache::set('data'.$order['u_id'],$cache,27200);
              //  return returnJson(0, 'success', '',$cache);
            }else{
                $array = $this->cache_myorder(2,$order['u_id']);

                Cache::set('data'.$order['u_id'],$array,27200);
            }


            return returnJson(0, 'success', '',$db);
        }else{
            return returnJson(3, '公众号余额不足', '');
        }

        //$data['status'] =11;
        //$data['msg'] ='用户取消订单';
        //$db = Db::table('qlbl_order')
        //    ->where('id', $order['id'])
        //    ->update($data);

    }



    public function del_cache(){
        show(decode('MNTmcY25NMzTQh1lMzg2Njg6f918e'));
        Cache::rm('data71');
        Cache::rm('data72');
    }








}
