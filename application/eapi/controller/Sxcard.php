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
use think\Db;
use think\Cache;
use qcloudcos\Cosapi;
class Sxcard extends PublicBase
{


	public function _initialize()
    {
     	parent::_initialize();  
    }
    /**
     * [lists 宿卡列表]
     * @Author   Jerry
     * @DateTime 2017-11-06T15:27:52+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function lists(){
        $uid = (int)input('uid');
        if(!$uid) returnJson(1,'参数有误');
        $re= $this->getbase->getall('users_sx_scard',['where'=>"uid = {$uid} and overplus_times>0 and end_date>='".date('Y-m-d H:i:s')."'",'order'=>'create_date asc','field'=>'overplus_times,end_date,user_times']);
        // show($re);
        foreach ($re as &$v) {
            $v['end_date'] = date('Y-m-d',strtotime($v['end_date']));
        }
        $data['cards'] = $re?$re:'';
        $userinfo = $this->getbase->getone('users_wx',['where'=>['id'=>$uid],'field'=>'pay_password']);
        $data['is_setpaypw'] = $userinfo['pay_password']?1:0;

       $sxcard = $this->getbase->getall('order',['where'=>"o.u_id = $uid AND pl.pay_tool='sxcard' ",'alias'=>'o','join'=>[['qlbl_pay_log pl','pl.order_id = o.order_number']],'field'=>'pl.create_time'],'LEFT');
        foreach ($sxcard as $ki => $vi) {
           $data['sx_card'][] =date('Y-m-d H:i:s',$vi['create_time']);
        }
        
        returnJson(0,'success','',$data);

    }
    /**
     * [getcode 发送手机验证码]
     * @Author   Jerry
     * @DateTime 2017-11-06T15:49:09+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function getcode(){
        $uid = (int)input('uid');
        $phone = input('phone');
        if(!$uid||!$phone) returnJson(1,'参数有误');
        $data['p'] = $phone;
        $rule = [
            'p'  => 'require|number|length:10,13',
        ];


        $msg = [
            'p.require' => '必须要有手机号',
            'p.length'     => '手机号码不符',
            'p.number'   => '手机号必须是数字',
        ];
        $result   = $this->validate($rule,$data,$msg);
        if($result !== true){
            return  returnJson(1,$result,'');
        }
        //发送手机验证码
        $rsp  = $this->_send_phone($phone,$uid);
       if($rsp===true){
        returnJson(0,'success','');
       }else{
        returnJson(1,'success','',$rsp);
       }
        
    }
    /**
     *短信发送接口
     */
    private function _send_phone($phone,$uid=''){
        //加载第三方提供类
        include EXTEND_PATH.'SmsSender.php';
        include_once EXTEND_PATH.'SmsTools.php';
            // 请根据实际 appid 和 appkey 进行开发，以下只作为演示 sdk 使用
            $singleSender = new \SmsSender(config('sms_appid'), config('sms_appkey'));
            //验证码
            $code   = mt_rand(1000,9999);
            $params = array( $code, "3");
            $result = $singleSender->sendWithParam("86", $phone, config('sms_tempid'), $params, "宿洗", "", "");
            $rsp = json_decode($result);
            // show($rsp);
            //发送成功后
            if($rsp->errmsg == 'OK'){
                $data['time'] = time();
                $data['code'] = $code;
                $data['is_start'] = 0;
                $data['phone'] = $phone;
                $data['uid'] = $uid;
                $this->getbase->getadd('user_message',$data);
                return true;
            }else{
                return $rsp->errmsg;
            }

    }
    /**
     * [setpaypw 设置支付密码]
     * @Author   Jerry
     * @DateTime 2017-11-06T16:31:19+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function setpaypw(){
        $pay_password = addslashes(input('paypwd'));##支付密码
        $repaypwd = addslashes(input('repaypwd'));##确认支付密码
        $phone = trim(addslashes(input('phone')));
        $code = trim(addslashes(input('code')));##手机验证码
        $uid = (int)input('uid');
        ##验证验证码
        if(!$uid) returnJson(1,'参数有误');
        if(!$pay_password) returnJson(1,'支付密码不能为空');
        if($pay_password!=$repaypwd){
                returnJson(1,'两次输入的密码不一至');
            }
        if($codeinfo = $this->getbase->getone('user_message',['where'=>"uid = '{$uid}' and code = '{$code}' and phone='{$phone}' and is_start<1",'field'=>'id'])){
            
            $userinfo = [
                'pay_password' =>encode($pay_password),
                'phone' =>encode($phone),
            ];
            if(false!==$this->getbase->getedit('users_wx',['where'=>['id'=>$uid]],$userinfo)){
                $this->getbase->getedit('user_message',['where'=>['id'=>$codeinfo['id']]],['is_start'=>1]);
                returnJson(0,'设置成功');
            }else{
                returnJson(1,'服务器忙，请稍后再试');

            }

        }else{
            returnJson(1,'验证码错误');
        }

    }
    /**
     * [recharge 宿卡充值]
     * @Author   Jerry
     * @DateTime 2017-11-01T16:30:59+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function recharge(){
        $uid = (int)input('uid');
        if(!$uid)returnJson(1,'参数有误');
        $openidInfo = $this->getbase->getone('users_wx',['where'=>['id'=>$uid],'field'=>'oppenid']);
        $openid = $openidInfo['oppenid'];
        include EXTEND_PATH.'WeixinPay.php';
        $appid=config('appid_one');//小程序appid
        $mch_id=config('mch_id_one');//商户号
        $key=config('key_one');//支付平台秘钥
        $total_fee = getset('sx_card_price')*100;
       // $total_fee = 0.01*100;##测试数据，1分钱
        $out_trade_no = 'sxcard'.date('Ymd').time().'_'.rand(111,999);##随机号，避免商户订单重复的问题
        ##写入到充值页面中
        ##更新订单表中的out_trade_no
        $sx_scard_order = [
            'uid'=>$uid,
            'recharge'=>getset('sx_card_price'),
            'create_date'=>date('Y-m-d H:i:s'),
            'order_number'=>$out_trade_no,
        ];
        if(false!==$this->getbase->getadd('sx_scard_order',$sx_scard_order)){
             $body = "宿卡充值";
            //支付接口 提供所需要的参数 回调写在小程序内
            $weixinpay = new  \WeixinPay($appid,$openid,$mch_id,$key,$out_trade_no,$body,$total_fee,config('host_ip'),config('host').'/eapi/sxcard/notify');
            $return=$weixinpay->pay();
            return returnJson(0, 'success', '', $return);  
        }else{
          returnJson(1,'服务器忙，请稍后再试');  
        }
    }
    /**
     * [payment_love 宿卡可赠送]
     * @Author   Jerry
     * @DateTime 2017-11-09T11:56:14+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function sxcard_love(){
        $uid = (int)input('uid');
        if(!$uid)returnJson(1,'参数有误');
        $openidInfo = $this->getbase->getone('users_wx',['where'=>['id'=>$uid],'field'=>'oppenid']);
        $openid = $openidInfo['oppenid'];
        include EXTEND_PATH.'WeixinPay.php';
        $appid=config('appid_one');//小程序appid
        $mch_id=config('mch_id_one');//商户号
        $key=config('key_one');//支付平台秘钥
        // $total_fee = getset('sx_card_price')*100;
        $total_fee = 0.01*100;##测试数据，1分钱
        $out_trade_no = 'sxcardlo'.date('Ymd').time().'_'.rand(111,999);##随机号，避免商户订单重复的问题
        ##写入到充值页面中
        ##更新订单表中的out_trade_no
        $sx_scard_lover_order = [
            'uid'=>$uid,
            'recharge'=>getset('sx_card_price_love'),
            'create_date'=>date('Y-m-d H:i:s'),
            'order_number'=>$out_trade_no,
        ];
        if(false!==$this->getbase->getadd('sx_scard_lover_order',$sx_scard_lover_order)){
             $body = "宿卡充值";
            //支付接口 提供所需要的参数 回调写在小程序内
            $weixinpay = new  \WeixinPay($appid,$openid,$mch_id,$key,$out_trade_no,$body,$total_fee,config('host_ip'),config('host').'/eapi/sxcard/notify_love');
            $return=$weixinpay->pay();
            return returnJson(0, 'success', '', $return);  
        }else{
          returnJson(1,'服务器忙，请稍后再试');  
        }
    }
    /**
     * [notify_love 宿卡（可赠送回调）]
     * @Author   Jerry
     * @DateTime 2017-11-09T12:00:27+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function notify_love(){
         $post = $this->_wx_post_data();    //接受POST数据XML个数
        $post_data = $this->_xml_to_array($post);   //微信支付成功，返回回调地址url的数据：XML转数组Array
        $postSign = $post_data['sign'];
        unset($post_data['sign']);
        ksort($post_data);// 对数据进行排序
        $str = $this->_ToUrlParams($post_data);//对数组数据拼接成key=value字符串
        $user_sign = strtoupper(md5($post_data));   //再次生成签名，与$postSign比较
        if($post_data['return_code']=='SUCCESS'&&$postSign){
             ##当前订单信息
            $sxCard = $this->getbase->getone('sx_scard_lover_order',['where'=>['order_number'=>$post_data['out_trade_no']]]);
            if($sxCard['status']=='1'){
                $this->_return_success();
            }else{
                    ##增加
                    $card = [
                        'uid'=>$sxCard['uid'],
                        'create_date'=>date('Y-m-d H:i:s'),
                    ];
                    if($this->getbase->getadd('users_sx_scard_love',$card)){
                         ##宿卡订单状态
                        $this->getbase->getedit('sx_scard_lover_order',['where'=>['id'=>$sxCard['id']]],['status'=>1]);
                         //写入日志
                        $sx_scard_log = [
                            'uid' => $sxCard['uid'],
                            'system_remark' => '消费金额:'.getset('sx_card_price_love').';可赠送',
                            'log' => '成功购买一张心意卡',
                            'order_id' => $sxCard['id'],
                            'create_date' => date('Y-m-d H:i:s'),
                            'type'=>'1'
                            ];
                        $this->getbase->getadd('sx_scard_love_log',$sx_scard_log);  
                    }else{
                        returnJson(1,'服务器忙，请稍后再试');
                    }
                
                $this->_return_success();
            }
        }else{
            returnJson(1,'购买失败');
        }
    }
    /**
     * [payment 宿卡支付]
     * @Author   Jerry
     * @DateTime 2017-11-04T14:07:54+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function payment(){
        $uid = (int)input('uid');
        $orderid = (int)input('orderid');
        $paypwd = input('paypwd');
        if(!$uid||!$orderid||!$paypwd) returnJson(1,'密码错误');
        // 匹配支付密码
        if($this->getbase->getcount('users_wx',['where'=>['pay_password'=>encode($paypwd),'id'=>$uid]])<1){
            returnJson(1,'支付密码错误');
        }
        ##用户是否有开通宿卡
        ###所购买的宿卡按IDASC 排序
        if($re = $this->getbase->getone('users_sx_scard',['where'=>"uid = {$uid} and overplus_times>0 and end_date>='".date('Y-m-d H:i:s')."'",'order'=>'create_date asc'])){
            ##减掉宿卡次数
            if(false!==$this->getbase->getedit('users_sx_scard',['where'=>"uid = {$uid} and id = ".$re['id']],['overplus_times'=>($re['overplus_times']-1)])){
                $this->getbase->getedit('order',['where'=>['id'=>$orderid]],['status'=>40]);
                $order_number = $this->getbase->getone('order',['where'=>['id'=>$orderid],'field'=>'order_number']);
                //写入支付日志
                $pay_log = array(
                    'order_id' => $order_number['order_number'],
                    'remarks' => '宿卡支付成功',
                    'pay_type' => 0,
                    'pay_price' => 0,
                    'create_time' => time(),
                    'pay_tool'  =>'sxcard',
                );
                $this->getbase->getadd('pay_log',$pay_log);
                ##订单日志
                $orderInfo['create_time'] = time();
                $orderInfo['remark'] = '宿卡支付成功';
                $orderInfo['uid'] = 1;##门店ID
                $orderInfo['type'] = 3;##1为商家2为快递员3为用户
                $orderInfo['order_status'] = 40;
                $orderInfo['order_id'] = $orderid;
                $re = $this->getbase->getadd('order_info',$orderInfo);
                returnJson(0,'支付成功');
            }else{
                returnJson(1,'服务器忙，请稍后再试');
            }
        }else{
            returnJson(1,'您的宿卡次数已用完');
        }
        
    }
    /**
     * [notify 宿卡充值回调]
     * @Author   Jerry
     * @DateTime 2017-11-01T16:37:41+0800
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
            $sxCard = $this->getbase->getone('sx_scard_order',['where'=>['order_number'=>$post_data['out_trade_no']]]);
            /*
            * 首先判断，订单是否已经更新为ok，因为微信会总共发送8次回调确认
            * 其次，订单已经为ok的，直接返回SUCCESS
            * 最后，订单没有为ok的，更新状态为ok，返回SUCCESS
            * 40=>'已支付',##下单成功已支付商家未接单
            */
            if($sxCard['status']=='1'){
                ##40=>'已支付',##下单成功已支付商家未接单
                $this->_return_success();
            }else{
                 
                //写入用户信息
                ##先判断用户是否买过此卡，如果买过，增加次数，没买过。插入数据
                // if($info = $this->getbase->getone('users_sx_scard',['where'=>['uid'=>$sxCard['uid']]])){
                //     ##修改次数
                //      $card = [
                //         'user_times'=>getset('sx_card_max')+$info['user_times'],##可使用次数
                //         'overplus_times'=>getset('sx_card_max')+$info['overplus_times'],##剩余次数
                //     ];
                //     if(false!==$this->getbase->getedit('users_sx_scard',['where'=>['uid'=>$sxCard['uid']]],$card)){
                //        $this->_sccard_log($sxCard); 
                //     }else{
                //         returnJson(1,'服务器忙，请稍后再试');
                //     }
                // }else{
                    ##增加
                    $card = [
                        'uid'=>$sxCard['uid'],
                        'user_times'=>getset('sx_card_max'),##可使用次数
                        'overplus_times'=>getset('sx_card_max'),##剩余次数
                        'create_date'=>date('Y-m-d H:i:s'),
                        'end_date'=>date('Y-m-d H:i:s',strtotime("+1 months")),##过期时间

                    ];
                    if($this->getbase->getadd('users_sx_scard',$card)){
                       $this->_sccard_log($sxCard);   
                    }else{
                        returnJson(1,'服务器忙，请稍后再试');
                    }
                // }
                
                $this->_return_success();
            }
        }else{
            returnJson(1,'购买失败');
        }
    }
    /**
     * [_sccard_log 日志信息]
     * @Author   Jerry
     * @DateTime 2017-11-01T17:13:52+0800
     * @Example  eg:
     * @param    [type]                   $sxCard [description]
     * @return   [type]                           [description]
     */
    private function _sccard_log($sxCard){
        ##宿卡订单状态
        $this->getbase->getedit('sx_scard_order',['where'=>['id'=>$sxCard['id']]],['status'=>1]);
         //写入日志
        $sx_scard_log = [
            'uid' => $sxCard['uid'],
            'system_remark' => '消费金额:'.getset('sx_card_price').';可使用次数：'.getset('sx_card_max'),
            'log' => '成功购买一张宿卡',
            'order_id' => $sxCard['id'],
            'create_date' => date('Y-m-d H:i:s'),
            'type'=>'1'
            ];
        $this->getbase->getadd('sx_scard_log',$sx_scard_log);
    }
    
 
    
}