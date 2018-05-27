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

namespace app\eapi\controller;
use app\common\controller\Base;
use think\Db;
use think\Cache;
class Order extends PublicBase
{
    //设置上传图片的大小
    public $img_ad_width = '100';     // 品牌宣传图的宽度
    public $img_ad_height = '150';    // 品牌宣传图的高度
    public $img_save_path ;
    private $spreak_status = [10,20,'-20'];##预约成功，前往取件
    private $no_pay_status = 30;##未支付
    public function _initialize()
    {
        $this->img_save_path=config('save_img_path');
        parent::_initialize();
    }

    /**
     * [bespeak 预约下单]
     * @Author   Jerry
     * @DateTime 2017-10-24T11:06:49+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function bespeak(){
        $requests = input();
        $data['goods_tag'] = addslashes($requests['goodstags']);
        $data['good_num'] = implode(',',$requests['goodnums']);
        $data['good_id'] = implode(',', $requests['goodids']);
        $data['address_id'] =(int)$requests['address_id'];
        $data['u_id'] = (int)$requests['uid'];
        $data['remarks'] = addslashes($requests['remarks']);
        $data['user_name'] = addslashes($requests['uname']);
        $data['user_phone'] = $requests['phone']?encode($requests['phone']):'';
        // $data['good_name'] = addslashes($requests['goodnames']);
        // $data['good_price'] = addslashes($requests['goodprice']);
        $data['user_address'] = addslashes($requests['address']);
        $data['take_time'] = addslashes($requests['take_time']);##取货时间
        $data['give_time'] = addslashes($requests['give_time']);##用户定送货时间
        $data['create_time'] = time();
        $data['status'] = '10'; // -8
        $data['order_number'] = 'SX'.mt_rand(100,999).date('Ymd',time()).rand(1234,9876);//生产订单号  随机数4哥 时间   订单件号 和数量
        $rule = [
            'goods_tag'  => 'require',
            'u_id'  => 'require',
            'user_name'  => 'require',
            'user_phone'  => 'require',
            'user_address'  => 'require',
            'take_time' =>'require',
            'give_time' =>'require',
        ];
        $msg = [
            'goods_tag.require'     => '您必须选择一种需要服务种类',
            'u_id.require'   => '请先登陆',
            'user_name.require' => '请填写姓名',
            'user_phone.require' => '请填写电话号码',
            'take_time.require' => '请选择取货时间',
            'give_time.require' => '请选择送货时间',
            'user_address.require' => '请填写地址',
        ];
        $result   = $this->validate($rule,$data,$msg);
        if($result !== true){
            return  returnJson(1,$result,'');
        }else{
            if($id = model('common/base')->getadd('order',$data)){
                ##2.写入日志
                $order_info['order_id'] = $id;
                $order_info['create_time'] = time();
                $order_info['remark'] = '预约成功,等待物流人员取件';
                $order_info['uid'] = 1;##门店ID
                $order_info['type'] = 1;##1为商家2为快递员
                $order_info['order_number'] =$data['order_number'];##门店ID
                $order_info['order_status'] = 10;
                $this->getbase->getadd('order_info',$order_info);


                ##成功后的其它操作 S 
                $this->_single_express($id,$data['order_number'],$data['take_time']);##单物流模式
                ##发送短信
                $this->_sendMsg([$id,$data['user_name'],decode($data['user_phone']),$data['user_address'],$data['take_time']]);
                ##后台消息推
                pushordermsg( $data['order_number'],'您有一个新的订单','订单包含服装为：'.$data['good_name']);
                ##成功后的其它操作 E
                #生成条形码
                c_barcode($data['order_number'],'./public/uploads/barcode/'.$data['order_number'].'.png');

                

                return returnJson(0,'预约成功','',['orderid'=>$id]); 
            }else{
                return returnJson(1,'服务器忙，请稍后再试');
            }
            
        }


    }
    /**
     * [express 物流员订单管理]
     * @Author   Jerry
     * @DateTime 2017-11-13T17:45:43+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function express(){
        $data = input();
        $order = $this->getbase->getall('order');
        returnJson(0,'success','',$order);

    }
    /**
     * [_single_express 单物流模式]
     * @Author   Jerry
     * @DateTime 2017-10-30T15:26:22+0800
     * @Example  eg:
     * $orderid    订单id
     * $order_number    订单号
     * @return   [type]                   [description]
     */
    private function _single_express($orderid,$order_number,$take_time){
        ##是否是单物流模式，如果开启单物流模式，直接派单取件给指定人
        if(getset('open_single_express')&&getset('express_id')){
            ##开启单物流模式，并且有指定派件人
            ##1.更改订单状态
            $status = 20;
            $this->getbase->getedit('order',['where'=>['id'=>$orderid]],['status'=>$status]);
            ##2.写入日志
            $order_info['order_id'] = $orderid;
            $order_info['create_time'] = time();
            $order_info['remark'] = '物流员正在前往取件中';
            $order_info['uid'] = 1;##门店ID
            $order_info['type'] = 1;##1为商家2为快递员
            $order_info['express_id'] = getset('express_id');##取件员的ID
            $order_info['order_number'] =$order_number;##门店ID
            $order_info['order_status'] = $status;
            $this->getbase->getadd('order_info',$order_info);
            ##3.指定物流 
            $express = [
                'express_id'=>getset('express_id'),
                'order_id'=>$orderid,
                'type'=>'1',
                'create_date'=>date('Y-m-d H:i:s'),
                'pick_up_date'=>$take_time,
            ];
            $eou_id = $this->getbase->getadd('express_order_users',$express); 
            ##4.写入物流日志
             $log = [
                  'express_id'=>getset('express_id'),
                  'ip'=>fetch_ip(),
                  'log'=>"分配物流员，等待物流员确认取件",
                  'create_time'=>date('Y-m-d H:i:s'),
                  'order_id'=>$orderid,
                  'edu_id'=>$eou_id,
                  'change_status'=>$status,
                ];
            $this->getbase->getadd('express_order_users_log',$log);
            
        }
    }
   /**
    * [_sendMsg description]
    * @Author   Jerry
    * @DateTime 2017-10-30T15:49:10+0800
    * @Example  eg:
    * @param    array                    $param [短信的配置信息]
    * @return   [type]                          [description]
    */
    private function _sendMsg($param=[]){
        ##门店负责人的手机号
       $setting = $this->getbase->getone('store_setting',['where'=>['store_id'=>1]]);
       // show($setting);
       if($setting['is_open_nitify_phone']>0){
         if($setting['notify_phone']){
            $notify_phoneArr = explode(",", trim($setting['notify_phone'],','));
            if(is_array($notify_phoneArr)){
                ##给门店管理员发短信
                foreach ($notify_phoneArr as $key => $v) {
                    send_phone($v,$param,'51010');
                }
            }

        }
       }
       
    }

   /**
    * [coupon 获得当前用户的优惠券]
    * @Author   Jerry
    * @DateTime 2017-10-31T15:02:22+0800
    * @Example  eg:
    * @return   [type]                   [description]
    */
    public function coupon(){
        $uid = (int)input('uid');
        $price = input('price');##金额
        if(!$uid) returnJson(1,'参数有误');
        if($price){
            $where = 'uc.u_id='.$uid. ' and uc.status = 0 and uc.end_time>="'.date('Y-m-d').'" and c.l_price<='.$price;
        }else{
            $where = 'uc.u_id='.$uid. ' and uc.status = 0 and uc.end_time>="'.date('Y-m-d').'"';##用户所有的优惠券
        }
      
        ##c.expire 到期时间
            ##uc.status 是否使用，1为使用过
            ##是否达到要求l_price
        $coupon =  $this->getbase->getall('users_coupon',[
                                            'where'=>$where,
                                            // 'order'=>'id desc',
                                            'alias'=>'uc',
                                            'join'=>[['coupon c','uc.c_id = c.id']],
                                            'field'=>'c.discount,c.id,c.l_price,uc.end_time'
                                            ]);
        foreach ($coupon as &$v) {
            $v['dec'] = '满￥'.$v['l_price'].'.00元立减';
            $v['status'] = $v['end_time']<date('Y-m-d')?'0':1;##0过期了，1未过期 
            $v['status_dec'] = $this->time_tran(strtotime($v['end_time']));
            unset($v['l_price']);
            // unset($)
        }

        $coupon = count($coupon)>0?$coupon:'';
        returnJson(0,'success','',$coupon);
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
     * [index   提交确认订单数据]
     * @Author   Jerry
     * @return   提交订单信息跳转支付页面进行支付
     */
    public function pay()
    {
        $orderid = (int)input('orderid');##订单ID
        $uid  = (int)input('uid');##用户ID
        $coupon_id  = (int)input('coupon_id');##优惠券ID
        $distribution_id  = (int)input('distribution_id');##物流ID
        if(!$orderid||!$uid||!$distribution_id) returnJson(1,'参数有误');
        $coupon_money = 0;
        $distribution_money=0;
        if($coupon_id){
            ##优惠券
            ##查出当前优惠券，优惠的金额
            $coupon = $this->getbase->getone('users_coupon',['where'=>['uc.id'=>$coupon_id,'uc.u_id'=>$uid],'join'=>[['coupon c','c.id=uc.c_id']],'alias'=>'uc','field'=>'discount']);
            $coupon_money = is_array($coupon)?$coupon['discount']:0;
        }
        ##运费
        if($distribution_id){
            $distribution = $this->getbase->getone('order_express',['where'=>['id'=>$distribution_id],'field'=>'express_fee']);
            $distribution_money = $distribution['express_fee'];
            
        }
        ##订单信息
        $orderinfo = $this->getbase->getone('order',['where'=>"u_id = {$uid} AND id= {$orderid}",'field'=>'good_id,good_num']);
        if(!$orderinfo) returnJson(1,'数据异常');
         $goodsInfo = $this->_getGoods(trim($orderinfo['good_id'],','),['gnum'=>$orderinfo['good_num']]);
         $good_price = isset($goodsInfo['count_price'])?array_sum($goodsInfo['count_price']):0;##商品总价
         $goods = $goodsInfo['goods']?$goodsInfo['goods']:'';
           ##首件免单
           ##是否开启首件免洗，并且是新用户（就是没有成功下过单,大于30【支付成功】都算成交订单）['u_id'=>$uid,'status']
            if(getset('isopen_first_free')){
               $first_free =  $this->_firstFree($goods,$uid);##首件名单
                $distribution_money = 0;//如果首件免单，不要运费
            }
            $first_free['price'] = isset($first_free['price'])?(int)$first_free['price']:0;##首件免单的单价
            $order_price = ($good_price-$coupon_money-$first_free['price'])+$distribution_money;##优惠后，加运费后的价格
        ##更改订单状态
        $data = [
        'coupon_id'=>$coupon_id,
        'coupon_price'=>$coupon_money,
        'distribution_id'=>$distribution_id,
        'distribution_price'=>$distribution_money,
        'order_price'=>$order_price,##实付
        'good_price'=>$good_price,##商品总价

        ];
        if(false!==$this->getbase->getedit('order',['where'=>['id'=>$orderid,'u_id'=>$uid]],$data)){
            ##首件免单数据  S
            $firstdb = [
                'goodid'=>$first_free['id'],
                'orderid'=>$orderid,
                'price'=>$first_free['price'],
                'uid'=>$uid,
                'num'=>1,
                'picture'=>$first_free['picture'],
            ];
            ##首件名单是否存在
            if($this->getbase->getcount('order_first_free',['where'=>['orderid'=>$orderid]])>0){
                 $this->getbase->getedit('order_first_free',['where'=>['orderid'=>$orderid]],$firstdb);
            }else{
                $this->getbase->getadd('order_first_free',$firstdb); 
            }
            ##首件免单数据 E
            returnJson(0,'success');
        }else{
            returnJson(1,'服务器忙，请稍后再试');
        }
    }




    /**
     * [index   发起支付页面]
     * @Author   wb
     * @return   提交订单信息跳转支付页面进行支付 传入用户id与订单id
     */
    public function pay_info(){

        $orderid = (int)input('orderid');
        $uid  = (int)input('uid');
        //发起获取订单信息 例如价格 并返回订单价格
        if(!$orderid||!$uid) returnJson(1,'参数有误') ;
        $order = $this->getbase->getone('order',['where'=>['id'=>$orderid,'u_id'=>$uid],'field'=>'order_price']);
        $data['price'] = $order['order_price'];

        ##宿卡相关信息
        $data['sxcard_status'] = $this->getbase->getcount('users_sx_scard',['where'=>"uid = {$uid}"]);
        if(empty($data['price'])&&$data['price']!=0){
            return returnJson(1, '服务器忙，请稍后再试'); 
        }else{
          return returnJson(0, 'success', '', $data);  
        }

    }


   


    /**
     * [index   支付页面]
     * @Author   wb
     * @return   调用微信sdk进行支付  传入订单信息
     */
    public function payment(){
        $uid = (int)input('uid');
        $orderid   = input('orderid');//订单id
        if(!$uid||!$orderid)returnJson(1,'参数有误');
        $openidInfo = $this->getbase->getone('users_wx',['where'=>['id'=>$uid],'field'=>'oppenid']);
        $openid = $openidInfo['oppenid'];
        include EXTEND_PATH.'WeixinPay.php';
        $appid=config('appid_one');//小程序appid
        $mch_id=config('mch_id_one');//商户号
        $key=config('key_one');//支付平台秘钥
        $order = $this->getbase->getone('order',['where'=>['id'=>$orderid],'field'=>'order_price,order_number']);
        if(!$order)returnJson(1,'订单异常');
        $total_fee = ($order['order_price']==0?0.01:$order['order_price'])*100;
        // $total_fee = 0.01*100;##测试数据，1分钱
        $out_trade_no = $order['order_number'].'_'.rand(111,99999);##随机号，避免商户订单重复的问题
        ##更新订单表中的out_trade_no
       
        if(false!==$this->getbase->getedit('order',['where'=>['id'=>$orderid]],['out_trade_no'=>$out_trade_no])){
              $body = "付款";
            //支付接口 提供所需要的参数 回调写在小程序内
            $weixinpay = new  \WeixinPay($appid,$openid,$mch_id,$key,$out_trade_no,$body,$total_fee,config('host_ip'),config('host').'/eapi/order/notify');
            $return=$weixinpay->pay();
            return returnJson(0, 'success', '', $return);  
        }else{
            returnJson(1,'服务器忙，请稍后再试'); 
        }
        
    }


    
    /**
     * [index   支付成功]
     * @Author   wb
     * @return   支付成功后进行逻辑处理 返回用户id
     */
    public function pay_ok()
    {
        returnJson(1,'改为异步处理订单结果');
        //支付成功则毁掉接口
        // $orderid = (int)input('orderid');
        //更改状态
        // die;
        // $this->getbase->getedit('order',['where'=>['id'=>$orderid]],['status'=>40]);

        // $order = $this->getbase->getone('order',['where'=>['id'=>$orderid]]);
        // ##门店负责人的手机号
        // //写入支付日志
        // $pay_log = array('order_id' => $order['order_number'],
        //     'remarks' => '完成付款',
        //     'pay_type' => 0,
        //     'pay_price' => $order['order_price'],
        //     'create_time' => time(),
        // );
        // $this->getbase->getadd('pay_log',$pay_log);
        // //写入优惠券记录
        // $order_coupon_log = array('o_id' =>$orderid,
        //     'c_id' => $order['coupon_id'],
        //     'c_discount' => $order['coupon_price'],
        //     'discount' => $order['order_price'],
        //     'c_time' => time(),
        // );
        // $this->getbase->getadd('order_coupon_log',$order_coupon_log);
        // ##更改用户的COUPON表，状态改为1（已使用）
        
        // $this->getbase->getedit('users_coupon',['where'=>['id'=>$order['coupon_id']]],['status'=>1]);
        // return returnJson(0, 'success', '');

    }
    /**
     * [notify 微信支付回调]
     * @Author   Jerry
     * @DateTime 2017-11-01T14:23:10+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function notify(){
        $post = $this->_wx_post_data();    //接受POST数据XML个数
        $post_data = $this->_xml_to_array($post);   //微信支付成功，返回回调地址url的数据：XML转数组Array
        $postSign = $post_data['sign'];
        unset($post_data['sign']);
        
        /* 微信官方提醒：
         *  商户系统对于支付结果通知的内容一定要做【签名验证】,
         *  并校验返回的【订单金额是否与商户侧的订单金额】一致，
         *  防止数据泄漏导致出现“假通知”，造成资金损失。
         */
        ksort($post_data);// 对数据进行排序
        $str = $this->_ToUrlParams($post_data);//对数组数据拼接成key=value字符串
        $user_sign = strtoupper(md5($post_data));   //再次生成签名，与$postSign比较
        
        if($post_data['return_code']=='SUCCESS'&&$postSign){
             ##当前订单信息
            $order = $this->getbase->getone('order',['where'=>['out_trade_no'=>$post_data['out_trade_no']]]);
            /*
            * 首先判断，订单是否已经更新为ok，因为微信会总共发送8次回调确认
            * 其次，订单已经为ok的，直接返回SUCCESS
            * 最后，订单没有为ok的，更新状态为ok，返回SUCCESS
            * 40=>'已支付',##下单成功已支付商家未接单
            */
            if($order['status']=='40'){
                ##40=>'已支付',##下单成功已支付商家未接单
                $this->_return_success();
            }else{
                 $this->getbase->getedit('order',['where'=>['id'=>$order['id']]],['status'=>40]);
                //写入支付日志
                $pay_log = array('order_id' => $order['order_number'],
                    'remarks' => '完成付款',
                    'pay_type' => 0,
                    'pay_price' => $order['order_price'],
                    'create_time' => time(),
                );
                $this->getbase->getadd('pay_log',$pay_log);

                ##订单日志
                $orderInfo['create_time'] = time();
                $orderInfo['remark'] = '完成付款';
                $orderInfo['uid'] = 1;##门店ID
                $orderInfo['type'] = 3;##1为商家2为快递员3为用户
                $orderInfo['order_number'] = $order['order_number'];
                $orderInfo['order_status'] = 40;
                $orderInfo['order_id'] = $order['id'];
                $re = $this->getbase->getadd('order_info',$orderInfo);

                //写入优惠券记录
                $order_coupon_log = [
                    'o_id' =>$order['id'],
                    'c_id' => $order['coupon_id'],
                    'c_discount' => $order['coupon_price'],
                    'discount' => $order['order_price'],
                    'c_time' => time(),
                    ];
                $this->getbase->getadd('order_coupon_log',$order_coupon_log);
                ##更改用户的COUPON表，状态改为1（已使用）
                $this->getbase->getedit('users_coupon',['where'=>['id'=>$order['coupon_id']]],['status'=>1]);
                
                $this->_return_success();
            }
        }else{
            returnJson(1,'支付失败');
        }

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
     * [orders 所有订单]
     * @Author   WuSong
     * @DateTime 2017-10-27T09:37:57+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function orders(){
        $requests = input();
        $status = (int)$requests['status'];
        $uid = (int)$requests['uid'];
        if(!$uid) returnJson(1,'参数有误');
        $goods_tag = $requests['goods_tag'];
        //配置项状态
        $order_status =  config('order_status');
        //## 1.未支付 2.服务中 3.已完成 不传=所有
        switch ($status) {
            case '1':
                $where = "status in(10,20,30)";
                break;
            case '2':
               $where = "status in(40,50,60,61,62,63,64,65,66,70,80,90)";
                break;
            case '3':
                $where = "status in(100)";
                break;
            default :
                $where = "status!=-50";
                break;
            
        }
        $where.=" and u_id = {$uid}";
        //条件
        //订单
        $orders = $this->getbase->getall('order',['where'=>$where,'field'=>'good_num,id,order_number,order_price,create_time,status,good_id,goods_tag','order'=>'id desc']);
        foreach ($orders as $k => $v) {
             if(!in_array($v['status'], $this->spreak_status)){
                $imgs = $this->_getGoodsImg(trim($v['good_id'],','));##商品图片
                $orders[$k]['imgs'] = $imgs;
                $orders[$k]['order_desc'] = "共有".array_sum(explode(',', trim($v['good_num'],','))).'件商品，实付金额：￥'.($v['order_price']?$v['order_price']:0);
            }else{
               $imgs = $this->_getTagsImg(trim($v['goods_tag'],',')); ##标签图片
               $orders[$k]['imgs'] = $imgs;
               $orders[$k]['order_desc'] = "共选择了".array_sum(explode(',', trim($v['good_num'],','))).'件商品';

            }
            ##新老版本，状态过渡
            if($v['create_time']>config('transition_time')){
                ##新版本
                $orders[$k]['status_dec'] = config('order_status')[$v['status']];
            }else{
                ##老版本
                $orders[$k]['status_dec'] = '已完成';
            }
            $orders[$k]['user_phone'] = decode($v['user_phone']);
            $orders[$k]['create_time'] = date('Y-m-d',$v['create_time']);
            
            
            unset($orders[$k]['good_id']);
            unset($orders[$k]['goods_tag']);
        }
        return returnJson(0,'success','',$orders);
    }

    /**
     * [_getGoodsImg 获得商品图片]
     * @Author   Jerry
     * @DateTime 2017-10-31T16:06:01+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    private function _getGoodsImg($ids){
        $goodsimg = $this->getbase->getall('goods',['where'=>"id in ({$ids})",'field'=>'picture']);
        if($goodsimg){
            $imgs = [];
            foreach ($goodsimg as $key => $v) {
               $imgs[] = config('host').get_file_path($v['picture']);
            }
        }else{
            $imgs = '';
        }
      return $imgs;
    }
    /**
     * [_getTagsImg 获得标签图片]
     * @Author   Jerry
     * @DateTime 2017-10-31T16:05:58+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    private function _getTagsImg($ids){
        $tagimg = $this->getbase->getall('goods_cat_tag',['where'=>"id in({$ids})",'field'=>'picture']);
        if($tagimg){
            $imgs = [];
            foreach ($tagimg as $key => $v) {
               $imgs[] = config('host').get_file_path($v['picture']);
            }
        }else{
            $imgs = '';
        }
      return $imgs;

    }
    /**
     * [order_details 订单详情]
     * @Author   Jerry
     * @DateTime 2017-10-27T09:48:45+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function order_details(){
        $uid =(int)input('uid');
        $orderid = (int)input('orderid');
        if(!$uid||!$orderid) returnJson(1,'参数有误');
        $spreak_status = $this->spreak_status;##预约成功，前往取件
        $no_pay_status = $this->no_pay_status;
        //订单数据 1.用户ID 2.订单ID
        $data = $this->getbase->getone('order',['where'=>"u_id = {$uid} AND id= {$orderid}",'field'=>'order_number,user_address,user_name,good_name,good_price,coupon_id,coupon_price,order_price,create_time,status,take_time,give_time,good_id,good_num,status,id,remarks,distribution_price,distribution_id']);
        if(!$data) returnJson(1,'无此订单');
        ##如果状态为10（预约成功）没有商品过滤
        if(!in_array($data['status'], $spreak_status)){
            $goodsInfo = $this->_getGoods(trim($data['good_id'],','),['gnum'=>$data['good_num']]);
            $goods = $goodsInfo['goods'];
            $count_price = $goodsInfo['count_price'];
        }else{
            $goods = '';
        }
        $data['goods'] = $goods;
        $data['status_dec'] = config('order_status')[$data['status']];
        $data['good_price'] = isset($count_price)?array_sum($count_price):0;##商品总价
        ##如果是未付款状态
        if($data['status']==$no_pay_status){
            ## 查出用记现在有的优惠
            //查询订单配送表
            $exp = $this->_exp_fee($data['good_price']);
            $data['distribution_price'] =$exp['express_fee']; ##运费价格
            $data['distribution_id'] = $exp['id']; ##运费id
          
            
            ##首件免单

            if(getset('isopen_first_free')){
               $first_free =  $this->_firstFree($goods,$uid);##首件名单
               $data['distribution_price'] = 0;##首件免配送费
              
            }
            $first_free['price'] = isset($first_free['price'])?(int)$first_free['price']:0;##首件免单的单价
            ##优惠券信息
            $coupon = $this->_coupon($uid,($data['good_price']-$first_free['price']));## 实际限额为当前商品价格-首件免单价格
            $data['coupon_id']  = isset($coupon['id'])?$coupon['id']:'';
            $data['coupon_price'] = isset($coupon['discount'])?$coupon['discount']:0;
            
            
        }else{
            $first_free = $this->getbase->getone('order_first_free',['where'=>['orderid'=>$orderid]]);##付款后的状态
        }
        // show($data['good_price']);
        // show($data['coupon_price']);
        // show($first_free['price']);
        // show($data['distribution_price']);
        ##订单价格
        $data['order_price'] = ($data['good_price']-$data['coupon_price']-$first_free['price'])+$data['distribution_price'];##优惠后,，加运费后的价格

        $data['step'] = $this->_orderStep($orderid);
        $data['first_free'] = $first_free?$first_free:'';
        return returnJson(0,'success','',$data);
    }
    /**
     * [_firstFree 首件免单]
     * @Author   Jerry
     * @DateTime 2017-11-07T09:33:03+0800
     * @Example  eg:
     * @param    [type]                   $order_goods [description]
     * @return   [type]                                [description]
     */
    private function _firstFree($order_goods,$uid){
        $goods = $order_goods;##订单位的商品
        $first_free_goods = getset('first_free_goods_choose');##所有参与名单的商品
        ##首件免单
        ##是否开启首件免洗，并且是新用户（就是没有成功下过单,大于30【支付成功】都算成交订单）['u_id'=>$uid,'status']
           $is_new_user = $this->getbase->getcount('order',['where'=>"u_id = $uid and status>30"]); 
            if(getset('isopen_first_free')&&$is_new_user<1){
                ##订单中的商品
                $first_free = [];##免单的衣服
                if($goods){
                    foreach ($goods as $k => $v) {
                        if(in_array($v['id'], $first_free_goods)){
                           ##免单的类型，最贵？还是最便宜的那件？
                            if(getset('first_free_kind_type')=='expensive'){
                                ##最贵
                                if($first_free){
                                    if($v['price']>$first_free['price']){
                                       $first_free=$v; 
                                    }
                                }else{
                                  $first_free=$v; 
                                }
                            }else{
                                ##最便宜
                                    if($first_free){
                                        if($v['price']<$first_free['price']){
                                           $first_free=$v; 
                                        }
                                }else{
                                  $first_free=$v; 
                                }
                            }
                        }
                        
                    }   
                }
                unset($first_free['prices']);
                return $first_free;
            }
    }
    /**
     * [_getGoods 获得商品列表]
     * @Author   Jerry
     * @DateTime 2017-10-31T14:24:50+0800
     * @Example  eg:
     * @param    [type]                   $ids   [description]
     * @param    array                    $param [description]
     * @return   [type]                          [description]
     */
    private function _getGoods($ids,$param=[]){
            $gid_gnum = [];
            $gnums = explode(",", trim($param['gnum'],','));
           ##商品和数量对应
           foreach (explode(",", trim($ids,',')) as $k => $v) {
               $gid_gnum[$v] = $gnums[$k];
           }
           ##没品重组
            // 总价核算
           $count_price = [];
           $goods = $this->getbase->getall('goods',['where'=>"id in (".trim($ids.")",','),'field'=>'name,id,price,picture']);
            foreach ($goods as &$v) {
                $v['picture'] = config('host').get_file_path($v['picture']);
                $v['num'] = $gid_gnum[$v['id']];
                $cPrices = $gid_gnum[$v['id']]*$v['price'];
                $v['prices'] = $cPrices;
                $count_price[] = $cPrices;
            }
            $data['goods'] = $goods;
            $data['count_price'] = $count_price;
        return $data;
    }
    /**
     * [_orderinfo 订单时间轴信息]
     * @Author   Jerry
     * @DateTime 2017-10-31T14:34:02+0800
     * @Example  eg:
     * @param []    $[orderid]         [<订单ID>]
     * @return   [type]                   [description]
     */
    private function _orderStep($orderid){
       $step =  $this->getbase->getall('order_info',['where'=>['order_id'=>$orderid],'order'=>'create_time desc','field'=>'create_time,remark']);
       foreach ($step as &$v) {
           $v['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
       }
       return $step?$step:'';
    }
    /**
     * [_exp_fee 运费处理]
     * @Author   Jerry
     * @DateTime 2017-10-31T14:33:03+0800
     * @Example  eg:
     * @param    [type]                   $order_price [商品总价]
     * @return   [type]                                [description]
     */
    private function _exp_fee($order_price){
       return  $this->getbase->getone('order_express',['where'=>'amount_reached<="'.$order_price.'" and status = 1','order'=>'amount_reached desc','field'=>'id,express_fee']);

           
    }
    /**
     * [_coupon 优惠券信息]
     * @Author   Jerry
     * @DateTime 2017-10-31T14:28:59+0800
     * @Example  eg:
     * @uid              用户UID
     * @order_price      订单总价
     * @return   [type]                   [description]
     */
    private function _coupon($uid,$order_price){
        // show($order_price);
         ##c.expire 到期时间
            ##uc.status 是否使用，1为使用过
            ##是否达到要求l_price
        return  $this->getbase->getone('users_coupon',[
                                            'where'=>'uc.u_id='.$uid. ' and uc.status = 0 and uc.end_time>='.date('Y-m-d').' and c.l_price<='.$order_price,
                                            'alias'=>'uc',
                                            'join'=>[['coupon c','uc.c_id = c.id']],
                                            'field'=>'c.discount,uc.id,c.expire,l_price'
                                            ]);
        // show($re);
        
         
    }

    /**
     * [appointment_cancel 取消预约]
     * @Author   WuSong
     * @DateTime 2017-10-30T18:17:40+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function appointment_cancel(){
        $requests = input();
        $uid = $requests['uid'];
        $order_id = $requests['order_id'];
        $order_number =$this->getbase->getone('order',['where'=>"id=$order_id"]);
        $status = $order_number['status'];
        if($status!=10){
            return returnJson(1,config('order_status')[$order_number['status']].'的商品不能取消','');
        }
        if(false!==$this->getbase->getedit('order',['where'=>['u_id'=>$uid,'id'=>$order_id]],['status'=>'-20'])){
                $log = [
                    'order_id'=>$order_id,
                    'create_time'=>time(),
                    'remark'=>'用户取消预约',
                    'uid'=>$uid,
                    'type'=>3,
                    'status'=>1,
                    'order_number'=>$order_number['order_number'],

                ];
            $this->getbase->getadd('order_info',$log);
            return returnJson(0,'success','');  
        }else{
            returnJson(1,'服务器忙，请稍后再试');
        }
        
        
       
    }

    /**
     * [order_delete 用户删除订单(只能修改取消预约的订单)]
     * @Author   WuSong
     * @DateTime 2017-11-06T14:31:43+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function order_delete(){
        $requests = input();
        $uid = (int)$requests['uid'];
        $order_id = (int)$requests['order_id'];
        $status = '-50';
        if($this->getbase->getcount('order',['where'=>['id'=>$order_id]])>0){
            $this->getbase->getedit('order',['where'=>['id'=>$order_id,'u_id'=>$uid,'status'=>'-20']],['status'=>$status]);
               return returnJson(0,'success','');
        }else{
            return returnJson(1,'服务器忙，请稍后再试');
        }
    }

}
