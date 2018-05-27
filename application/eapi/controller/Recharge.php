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
class Recharge extends PublicBase
{

	public function _initialize()
    {
        parent::_initialize();

    }

    // public function moneys(){
    //   $id = (int)input('id');
    //   if(!$id) returnJson(1,'参数有误');
    //   $money = $this->getbase->getone('recharge_money',['where'=>['id'=>$id]]);
    //   $data['money'] = $money['money'];
    //   $data['give_money'] = $money['give_money'];

    //   return returnJson(0,'success','',$data);

    // }

    /**
     * [recharge 账户充值]
     * @Author   WuSong
     * @DateTime 2017-11-04T17:45:00+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
     public function recharge(){
        $uid = (int)input('uid');
        
        
        if(!$uid) returnJson(1,'参数有误');
        $openidInfo = $this->getbase->getone('users_wx',['where'=>['id'=>$uid],'field'=>'oppenid,money']);
        $openid = $openidInfo['oppenid'];
        include EXTEND_PATH.'WeixinPay.php';
        $appid = config('appid_one'); //小程序appid
        $mch_id = config('mch_id_one'); //商户号
        $key  = config('key_one'); //支付平台秘钥
        //充值的金额
        $total_fee = (int)input('money');
        //判断提交的金额区间，
          switch($total_fee) 
          {
           case $total_fee>=100 && $total_fee<200:
            $give_money = 1;
            break;
           case $total_fee>=200 && $total_fee<300:
            $give_money = 2;
            break;
           case $total_fee>=300 && $total_fee<500:
             $give_money = 3;
            break;
           case $total_fee>=500 && $total_fee<1000:
             $give_money =4;
            break;
          case $total_fee>=1000 && $total_fee<2000:
              $give_money =5;
            break;
           default:
             $give_money =0;
          }
        //判断赠送的金额是多少
        if($this->getbase->getcount('recharge_money',['where'=>['id'=>$give_money,'status'=>1]])>0){
            $give_money_info = $this->getbase->getone('recharge_money',['where'=>['id'=>$give_money,'status'=>1],'field'=>'give_money']);
          }else{
            $give_money_info['give_money'] = 0;
          }
        $money_info = $give_money_info['give_money'];
        // $total_fee = input('money')*100; ##充值金额
        // $total_fee = 0.01*100;  ##测试数据 1分钱
        $out_trade_no = 'sxrecharge'.date('Ymd').time().'_'.rand(111,999); //随机号，避免商户订单重复
        //写入用户充值页面
        ##更新到充值日志中的 out_trade_no
        $sx_recharge  =[
            'uid'=>$uid,
            'remarks'=>'用户充值，充值金额为 : '.$total_fee, 
            'money' =>$total_fee,
            'create_date'=>date("Y-m-d H:i:s"),
            'status'=>0,
            'pay_number'=>$out_trade_no,
            'give_money'=>$money_info,
        ];
        if(false!==$this->getbase->getadd('users_recharge',$sx_recharge)){
            $body = "充值";
            $total_fee = 0.01*100; //测试数据 1分钱
            //支付接口  提供所需要的参数 回调写在小程序内
            $weixinpay = new \WeixinPay($appid,$openid,$mch_id,$key,$out_trade_no,$body,$total_fee,'121.40.85.124','https://ithelp.org.cn/eapi/recharge/notify',$money_info);
            $return = $weixinpay->pay();
            // unset($return);
            return returnJson(0,'success','',$return);
        }else{
            returnJson(1,'服务器繁忙，请稍后再试');
        }
     }

     /**
      * [notify 账户充值回调]
      * @Author   WuSong
      * @DateTime 2017-11-06T08:59:42+0800
      * @Example  eg:
      * @return   [type]                   [description]
      */
     public function notify(){
        $post = $this->_wx_post_data(); //接收POST数据XML
        $post_data = $this->_xml_to_array($post); //微信支付成功，返回回调地址url的数据，XML转数组Array
        $postSign = $post_data['sign'];
        unset($post_data['sign']); //释放

        /* 微信官方提醒：
         *  商户系统对于支付结果通知的内容一定要做【签名验证】,
         *  并校验返回的【订单金额是否与商户侧的订单金额】一致，
         *  防止数据泄漏导致出现“假通知”，造成资金损失。
         */
        ksort($post_data); //对数据进行排序
        $str = $this->_ToUrlParams($post);//对数组数据拼接成key==value字符串
        $user_sign = strtoupper(md5($post_data)); //再次生成签名，与$postSign比较

        if($post_data['return_code']=='SUCCESS'&&$postSign){
            ##充值表充值信息
            $recharge = $this->getbase->getone('users_recharge',['where'=>['pay_number'=>$post_data['out_trade_no']]]);
            $out_trade_no =$post_data['out_trade_no'];
            if($recharge['status']=='1'){

               $this->_return_success();
             }else{
                  


                //修改
                $this->getbase->getedit('users_recharge',['where'=>['pay_number'=>$out_trade_no]],['status'=>'1']);

                $users = $this->getbase->getone('users_wx',['where'=>['id'=>$recharge['uid']]]);
                $money = $users['money']+$recharge['money']+$recharge['give_money'];
                $this ->getbase->getedit('users_wx',['where'=>['id'=>$recharge['uid']]],['money'=>$money]);


                //原生
                // Db::table(config('database.prefix').'users_recharge')
                //       ->alias('ur')
                //       ->join(config('database.prefix').'users_wx uw','uw.id = ur.uid')
                //       ->where('ur.pay_number',$out_trade_no)
                //       ->update(['ur.status'=>'1','uw.money'=>'uw.money'+$recharge['money']]);
                //充值成功写充值日志
                $log = [
                        'u_id' =>$recharge['uid'],
                        'remarks'=>'充值金额为：'.$recharge['money'],
                        'pay_money' =>$recharge['money'],
                        'create_time' => date('Y-m-d H:i:s'),
                        'pay_type' => '1',
                        'status' => '1',
                        'pay_number' => $recharge['pay_number'],
                        'give_money'=>$recharge['give_money'],
                ];
                $this->getbase->getadd('users_recharge_log',$log);
                $this->_return_success();
             }
          }
            returnJson(1,'充值失败');
      }

      /**
       * [balance 账户余额]
       * @Author   WuSong
       * @DateTime 2017-11-14T14:57:10+0800
       * @Example  eg:
       * @return   [type]                   [description]
       */
      public function balance(){
        $uid = (int)input('uid');
        
        if(!$uid) returnJson(1,'参数有误');
        $data = $this->getbase->getone('users_wx',['where'=>['id'=>$uid],'field'=>'money']);
        return returnJson(0,'success','',$data);
      }
}
