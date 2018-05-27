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
namespace app\manager\controller;
use app\common\controller\Base;
use think\Cache;
use think\Db;
use think\helper\Hash;
class Apitest extends Base
{

	public function _initialize()
    {
     	parent::_initialize();  
     	
    }


    

    /**
     * [appointment 预约上门]
     * @Author   WuSong
     * @DateTime 2017-10-25T11:19:31+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function appointment(){
        if($this->request->isPost()){
            $data = input();
            if(!$data['express_id']){
                $data['express_id'] =getset('express_id');
            }
 
            if(isset($data['ids'])&&is_array($data['ids'])){
                $idsStr = implode(",", $data['ids']);
                ##删除用户缓存 后期更改 为了时间 直接删除缓存
                $this->del_cache($idsStr);
                 ##更改订单状态
                $this->getbase->getedit('order',['where'=>['id'=>['in',$idsStr]]],['status'=>60]);
                ##指插入订单信息
                $orderInfo = [];
                ##订单的相关信息
                $orderinfos = $this->getbase->getall('order',['where'=>['id'=>['in',$idsStr]],'field'=>'order_number,id,u_id,user_address,take_time']);
                $store_id = session('suinfo')['store_id'];

                ##物流员相关信息
                $expressinfo = $this->getbase->getone('users_express',['where'=>['id'=>$data['express_id']],'field'=>'wx_openid,realname']);
                foreach ($orderinfos as $k => $v) {
                    ##订单状态日志
                    Cache::rm('data'.$v['u_id']);
                    $orderInfo['order_id'] = $v['id'];
                    $orderInfo['create_time'] = time();
                    $orderInfo['remark'] = '取件员正在途中';
                    $orderInfo['uid'] = $store_id;##门店ID
                    $orderInfo['type'] = 1;##1为商家2为快递员
                    $orderInfo['express_id'] = $data['express_id'];##取件员的ID
                    $orderInfo['order_number'] = $v['order_number'];##门店ID
                    $orderInfo['order_status'] = 60;
                    $re = $this->getbase->getadd('order_info',$orderInfo);
                    ##订单状态日志
                    
                    ##发送微信消息
                    if($expressinfo['wx_openid']){
                        ##如果发送不成功，可能是IP白明单的问题
                        $re = $this->wx_msg($expressinfo['wx_openid'],[
                                                                'url'=>'http://www.qiaolibeilang.com/express/order/index.html',
                                                                'first'=>$expressinfo['realname'].',您有一个新的物流订单，请您尽快取件',
                                                                'keyword1'=>$orderInfo['order_number'],
                                                                'keyword2'=>'取件',
                                                                'keyword3'=>$v['take_time'],
                                                                'remark'=>'取件地址:'.$v['user_address'].';点击查看详细信息',
                                                                    ]);
                      
                    }
                    ##发送微信消息

                    ##指定物流
                    if($eou_id = $this->getbase->getadd('express_order_users',['express_id'=>$data['express_id'],'order_id'=>$v['id'],'type'=>'1','create_date'=>date('Y-m-d H:i:s'),'pick_up_date'=>$v['take_time']])){
                        ##写入物流信息
                        $log = [
                          'express_id'=>$data['express_id'],
                          'ip'=>fetch_ip(),
                          'log'=>"分配物流员，等待物流员确认取件",
                          'create_time'=>date('Y-m-d H:i:s'),
                          'order_id'=>$v['id'],
                          'edu_id'=>$eou_id,
                          'change_status'=>'0',
                        ];
                        $this->getbase->getadd('express_order_users_log',$log);
                        ##指定物流

                    }
                    
                }
                
                returnJson(0,'成功接单');
            }else{
               returnJson(1,'您需要选择订单才能操作!');
            }
        }
    }
 
    /**
     * [check 检查并清洗]
     * @Author   WuSong
     * @DateTime 2017-10-30T10:06:07+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function check(){
        if($this->request->isPost()){
            $data = input();
            if(isset($data['ids'])&&is_array($data['ids'])){
                $idsStr = implode(",", $data['ids']);
                ##删除用户缓存 后期更改 为了时间 直接删除缓存
                $this->del_cache($idsStr);
                ##更改订单状态
                $this->getbase->getedit('order',['where'=>['id'=>['in',$idsStr]]],['status'=>80]);
                ##指插入订单信息
                $orderInfo = [];
                ##订单的相关信息
                $orderinfos = $this->getbase->getall('order',['where'=>['id'=>['in',$idsStr]],'field'=>'order_number,id,u_id,good_num']);
                $store_id = session('suinfo')['store_id'];

                ##生成挂件
                ##有多少挂位
                $postion_count = $this->getbase->getone('store_setting',['where'=>['store_id'=>$store_id],'field'=>'postion_count']);
                foreach ($orderinfos as $k => $v) {
                    Cache::rm('data'.$v['u_id']);
                    $orderInfo['order_id'] = $v['id'];
                    $orderInfo['create_time'] = time();
                    $orderInfo['remark'] = '门店收到衣服，清洗前的检查';
                    $orderInfo['uid'] = $store_id;##门店ID
                    $orderInfo['type'] = 1;##1为商家2为快递员
                    $orderInfo['order_number'] = $v['order_number'];##门店ID
                    $orderInfo['order_status'] = 80;
                    $re = $this->getbase->getadd('order_info',$orderInfo);
                    $good_num = array_sum(explode(",", trim($v['good_num'],',')));
                    

                    ##物流取件金额
                    $this->pickup($v);


                   for ($l = 1; $l<=$good_num; $l++){
                    ##挂号相关
                       $order_pendant = Db::name('order_pendant')
                            ->order('id','desc')
                           ->find();

                        if($order_pendant['t_code'] > 0){
                            $i = $order_pendant['t_code']+1;
                        }else{
                            $i = 850000;
                        }

                       $array = array('order_number'=>$v['order_number'],
                           'order_id'=>$v['id'],
                           'piece_id'=>$good_num.'-'.$l,
                           't_code'=>$i
                       );
                       
                        Db::name('order_pendant')->insert($array);
                         ##挂号相关
                      ##挂件管理S 
                        ##查出已用的挂位
                        $over_hang_number = $this->getbase->getall('order_pendant',['where'=>['store_id'=>$store_id,'status'=>'1'],'field'=>'hang_number']);
                        ##已使用的挂位
                        $over_hang_number_format = [];
                        if(count($over_hang_number)>0){
                            foreach ($over_hang_number as $ki => $vi) {
                                $over_hang_number_format[] = $vi['hang_number'];
                            }
                        }
                        ##此订单生成的挂位号
                      $order_colse_count = $this->getbase->getall('order_pendant',['where'=>['order_number'=>$v['order_number']],'field'=>'order_id,piece_id,id,order_number']);
                        $thumbKey=0;
                        ##循环挂位 去掉已使用的挂位。把订单放入到未使用的挂位 $thumbKey：数组KEY,  $i:当前位置号
                        for ($i=1; $i < ($postion_count['postion_count']+1); $i++) { 
                            if($thumbKey<count($order_colse_count)){
                                 ##此挂号没有被用，并且此订单挂号信息存在
                               if(!in_array($i, $over_hang_number_format)&&isset($order_colse_count[$thumbKey])){
                                    ##分配挂号
                                    // echo($i.'====');
                                    $re = $this->getbase->getedit('order_pendant',['where'=>['id'=>$order_colse_count[$thumbKey]['id']]],['store_id'=>$store_id,'hang_number'=>($thumbKey+1),'status'=>1]);##($thumbKey+1) 挂号编号从而开始
                                    $thumbKey++;
                               }
                           }
                               
                        }
                        ##挂件管理E
                   }
                   ##处理物流信息
                    #物流关联表状态改为已完成，生成物流关联表日志
                    #type=>1 收衣的状态
                     if(false!==$this->getbase->getedit('express_order_users',['where'=>['order_id'=>$v['id'],'type'=>1],'order'=>'id desc'],['status'=>'4'])){
                        $eouinfo = $this->getbase->getone('express_order_users',['where'=>['order_id'=>$v['id'],'type'=>1],'order'=>'id desc']);
                        ##写入日志
                      $log = [
                        'express_id'=>$eouinfo['express_id'],
                        'ip'=>fetch_ip(),
                        'log'=>"门店已收到衣服",
                        'create_time'=>date('Y-m-d H:i:s'),
                        'remark'=>'门店已收到衣服，并检查完成',
                        'order_id'=>$eouinfo['order_id'],
                        'edu_id'=>$eouinfo['id'],
                        'change_status'=>'15',
                        // 'admin_id'=>
                      ];
                      $this->getbase->getadd('express_order_users_log',$log);
                     }
                    ##处理物流信息
                }
                returnJson(0,'检查完成');
            }else{
               returnJson(1,'您需要选择订单才能操作!');
            }
        }
    }

    /**
     * [accounting 物流取单金额加成]
     * @Author   WuSong
     * @DateTime 2017-09-26T14:55:25+0800
     * @Example  eg:
     * @param    [type]                   $order_info [description]
     * @return   [type]                               [description]
     */
     public function pickup($order_info){
          //##表关联查询
           $express_info = DB::table(config('database.prefix').'order')
                        ->alias('o')
                        ->join(config('database.prefix').'express_order_users euo','o.id=euo.order_id','LEFT')
                        ->join(config('database.prefix').'users_express ue','ue.id=euo.express_id','LEFT')
                        ->field('ue.id express_id,euo.type,ue.money,o.id order_id,o.order_number,ue.id id')
                        ->where('o.id',$order_info['id'])
                        ->where('euo.type',1)
                        ->find();
                        // show($express_info);
            if(is_array($express_info)){
                ##取单金额核算
                ##添加金额
                    ##开始核算处理
                    ##当前物流员的金额
                    $money = $this->getbase->getone('users_express',['where'=>['id'=>$express_info['express_id']],'field'=>'money']);
                    $s_moeny = $money['money']+getset('spread_express_take');
                       if(false!==$this->getbase->getedit('users_express',['where'=>['id'=>$express_info['express_id']]],['money'=>$s_moeny])){
                            ##插入日志
                             $log = [
                                'order_id'=>$express_info['order_id'],##订单号
                                'remarks'=>'取件金额+'.getset('spread_express_take').'元',##备注
                                'create_time'=>date('Y-m-d H:i:s'),##时间
                                'order_number'=>$express_info['order_number'],##订单号
                                'log'=>'物流取件',##日志记录
                                'money'=>getset('spread_express_take'),##产生金额
                                'express_id'=>$express_info['id'],
                                'share_proportion'=>getset('spread_express_take'),##核算金额
                            ];
                              $re = $this->getbase->getadd('order_spread_log',$log);
                        } 
                
            }
       
        
        
    }

    /**
     * [dispatch 清洗完成并配送]
     * @Author   WuSong
     * @DateTime 2017-10-30T10:06:38+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function dispatch(){
         if($this->request->isPost()){
            $data = input();
            $orderid = implode(',',$data['ids']);
           $order_time =  $this->getbase->getall('express_order_users_log',['where'=>['order_id'=>['in',$orderid]]]);

           foreach ($order_time as $k => $v) {
              if(strtotime($v['create_time'])+10800>time()){
                $ordertime[]= $v['order_id'];
            }
           }
           $vtime = implode(',',$ordertime);
           if(!is_null($vtime)){
           return returnJson(1,'编号为'.$vtime.'未达标，请重新选择');
           }
             if(!$data['express_id']){
                $data['express_id'] =getset('express_id');
            }
            if(isset($data['ids'])&&is_array($data['ids'])){
                $idsStr = implode(",", $data['ids']);
                ##删除用户缓存 后期更改 为了时间 直接删除缓存
                $this->del_cache($idsStr);
                ##更改订单状态
                $this->getbase->getedit('order',['where'=>['id'=>['in',$idsStr]]],['status'=>90]);
                ##指插入订单信息
                $orderInfo = [];
                ##订单的相关信息
                $orderinfos = $this->getbase->getall('order',['where'=>['id'=>['in',$idsStr]],'field'=>'order_number,id,u_id,user_address']);

                ##物流员相关信息
                $expressinfo = $this->getbase->getone('users_express',['where'=>['id'=>$data['express_id']],'field'=>'wx_openid,realname']);

                foreach ($orderinfos as $k => $v) {
                    $orderInfo['order_id'] = $v['id'];
                        Cache::rm('data'.$v['u_id']);
                    $orderInfo['create_time'] = time();
                    $orderInfo['remark'] = '开始配送中，请保持手机畅通';
                    $orderInfo['uid'] = session('suinfo')['store_id'];##门店ID
                    $orderInfo['type'] = 1;##1为商家2为快递员
                    $orderInfo['order_number'] = $v['order_number'];##门店ID
                    $orderInfo['express_id'] = $data['express_id'];##派件员的ID
                    $orderInfo['order_status'] = 90;
                    $re = $this->getbase->getadd('order_info',$orderInfo);
                    ##清掉挂位
                   $re = $this->getbase->getedit('order_pendant',['where'=>['order_number'=>$v['order_number']]],['status'=>'-1']);
                    ##发送微信消息
                    if($expressinfo['wx_openid']){
                        ##如果发送不成功，可能是IP白明单的问题
                        $re = $this->wx_msg($expressinfo['wx_openid'],[
                                                                'url'=>'http://www.qiaolibeilang.com/express/order/index.html',
                                                                'first'=>$expressinfo['realname'].',您有一个新的物流订单,需要配送',
                                                                'keyword1'=>$orderInfo['order_number'],
                                                                'keyword2'=>'配送',
                                                                'keyword3'=>date('Y-m-d H:i:s'),
                                                                'remark'=>'送件地址:'.$v['user_address'].';点击查看详细信息',
                                                                    ]);
                      
                    }
                    ##发送微信消息

                    ##指定物流
                    if($eou_id = $this->getbase->getadd('express_order_users',['express_id'=>$data['express_id'],'order_id'=>$v['id'],'type'=>'2','create_date'=>date('Y-m-d H:i:s'),'pick_up_date'=>date('Y-m-d H:i:s')])){
                        ##写入物流信息
                        $log = [
                          'express_id'=>$data['express_id'],
                          'ip'=>fetch_ip(),
                          'log'=>"等待物流员取件配送",
                          'create_time'=>date('Y-m-d H:i:s'),
                          'order_id'=>$v['id'],
                          'edu_id'=>$eou_id,
                          'change_status'=>'0',
                        ];
                        $this->getbase->getadd('express_order_users_log',$log);
                        ##指定物流

                    }
                }
                returnJson(0,'开始配送');
            }else{
               returnJson(1,'您需要选择订单才能操作!');
            }
        }
    }
    /**
     * [finish 订单完结]
     * @Author   Jerry
     * @DateTime 2017-08-29T17:40:50+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function finish(){
        if($this->request->isPost()){
            $data = input();
            if(isset($data['ids'])&&is_array($data['ids'])){
                $idsStr = implode(",", $data['ids']);
                ##删除用户缓存 后期更改 为了时间 直接删除缓存
                $this->del_cache($idsStr);
                ##更改订单状态
                $this->getbase->getedit('order',['where'=>['id'=>['in',$idsStr]]],['status'=>100]);
                ##指插入订单信息
                $orderInfo = [];
                ##订单的相关信息
                // show($idsStr);
                $orderinfos = $this->getbase->getall('order',['where'=>['id'=>['in',$idsStr]],'field'=>'order_number,id,u_id,good_price,id,u_id,user_address']);
                // show($orderinfos);
                foreach ($orderinfos as $k => $v) {
                    $orderInfo['order_id'] = $v['id'];
                        Cache::rm('data'.$v['u_id']);
                    $orderInfo['create_time'] = time();
                    $orderInfo['remark'] = '已成功送达';
                    $orderInfo['uid'] = session('suinfo')['store_id'];##门店ID
                    $orderInfo['type'] = 1;##1为商家2为快递员
                    $orderInfo['order_number'] = $v['order_number'];##门店ID
                    $orderInfo['order_status'] = 100;
                    $re = $this->getbase->getadd('order_info',$orderInfo);
                    // show($re);
                    ##清掉挂位
                    // 物流用户分成
                    $this->spread($v);

                    //物流送单金额加成
                    $this->send($v);

                    ##酒店分成
                    $this->hotel_spread($v);

                    ##处理物流信息
                    #物流关联表状态改为已完成，生成物流关联表日志
                    #type=>1 收衣的状态
  
                     if(false!==$this->getbase->getedit('express_order_users',['where'=>['order_id'=>$v['id'],'type'=>2],'order'=>'id desc'],['status'=>'4'])){
                        $eouinfo = $this->getbase->getone('express_order_users',['where'=>['order_id'=>$v['id'],'type'=>2],'order'=>'id desc']);
                        ##写入日志
                      $log = [
                        'express_id'=>$eouinfo['express_id'],
                        'ip'=>fetch_ip(),
                        'log'=>"客户已收到衣服",
                        'create_time'=>date('Y-m-d H:i:s'),
                        'remark'=>'后台完结',
                        'order_id'=>$eouinfo['order_id'],
                        'edu_id'=>$eouinfo['id'],
                        'change_status'=>'4',
                        // 'admin_id'=>
                      ];
                      // show($log['order_id']);
                      $this->getbase->getadd('express_order_users_log',$log);
                     }

                    ##处理物流信息
                    #
                    #
                    
                    
                }
                returnJson(0,'完结订单成功');
            }else{
               returnJson(1,'您需要选择订单才能操作!');
            }
        }
    }
    /**
     * [send 物流送单金额加成]
     * @Author   WuSong
     * @DateTime 2017-09-26T15:14:51+0800
     * @Example  eg:
     * @param    [type]                   $order_info [description]
     * @return   [type]                               [description]
     */
    public function send($order_info){

          //##表关联查询
           $express_info = DB::table(config('database.prefix').'order')
                        ->alias('o')
                        ->join(config('database.prefix').'express_order_users euo','o.id=euo.order_id','LEFT')
                        ->join(config('database.prefix').'users_express ue','ue.id=euo.express_id','LEFT')
                        ->field('ue.id express_id,euo.type,ue.money,o.id order_id,o.order_number,ue.id id')
                        ->where('o.id',$order_info['id'])
                        ->where('euo.type',2)
                        ->find();

            if(is_array($express_info)){
                ##送单金额核算
                ##添加金额
                  $money = $this->getbase->getone('users_express',['where'=>['id'=>$express_info['express_id']],'field'=>'money']);
                  $s_moeny = $money['money']+getset('spread_express_give');
                    ##开始核算处理
                       if(false!==$this->getbase->getedit('users_express',['where'=>['id'=>$express_info['express_id']]],['money'=>$s_moeny])){
                            ##插入日志
                             $log = [
                                'order_id'=>$express_info['order_id'],##订单号
                                'remarks'=>'送件金额+'.getset('spread_express_give').'元',##备注
                                'create_time'=>date('Y-m-d H:i:s'),##时间
                                'order_number'=>$express_info['order_number'],##订单号
                                'log'=>'物流送件',##日志记录
                                'money'=>getset('spread_express_give'),##产生金额
                                'express_id'=>$express_info['id'],
                                'share_proportion'=>getset('spread_express_give'),##金额
                            ];
                              $re = $this->getbase->getadd('order_spread_log',$log);
                        } 
                
            }
       
        
        
    }






    /**
     * [hotel_spread 酒店分成]
     * @Author   Jerry
     * @DateTime 2017-09-15T15:36:19+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function hotel_spread($order_info){
           $uid = $order_info['u_id'];
           $order_address = $order_info['user_address'];
           $get_spread_money = sprintf("%.2f",$order_info['good_price']*(getset('spread_hotel')/100));##分成百分比
            ##酒店推广分成(总金额下面的分成比例。规则，当前用户是否填写了酒店名字,酒店用户享有分成)
            #查出所有酒店（认证通过并且状态为可用的）
            $allHotel = $this->getbase->getall('hotel',['where'=>'status=1','alias'=>'h','join'=>[['qlbl_hotel_authen ha','ha.hotel_id=h.id']],'field'=>'h.id hid,h.hotel_name,h.address,h.status,ha.name']);
            if(is_array($allHotel)){
               foreach ($allHotel as $k => $v) {
                    if(strpos($order_address, $v['hotel_name'])){
                        ##订单地址，是否包含酒店名,如果包含，就给此酒店分成
                        if($this->getbase->getcount('order_hotel_spread_log',['where'=>['hotel_id'=>$v['hid'],'order_id'=>$order_info['id']]])<1){
                             ##开始分成处理
                           if(false!==DB::name('hotel')->where("id = {$v['hid']}")->setInc('money',$get_spread_money)){
                            #插入日志
                                 $log = [
                                    'order_id'=>$order_info['id'],##订单号
                                    'remarks'=>'推广分成金额+'.$get_spread_money,##备注
                                    'create_time'=>date('Y-m-d H:i:s'),##时间
                                    'order_number'=>$order_info['order_number'],##订单号
                                    'log'=>'推广分成',##日志记录
                                    // 'uid'=>$order_info['uid'],##用户ID
                                    'money'=>$get_spread_money,##产生金额
                                    'hotel_id'=>$v['hid'],
                                    'share_proportion'=>getset('spread_express').'%',##分成比例
                                    'math_date'=>date('Ym'),
                                ];
                                $re = $this->getbase->getadd('order_hotel_spread_log',$log);
                            } 
                        }
                        break;
                    }
                } 
            }
        ##酒店分成(如果收货地址中，和酒店表中的名字匹配享受分成){还没开始}
    }
    /**
     * [spread 物流，酒店分成成品核算]
     * @Author   Jerry
     * @DateTime 2017-09-07T16:45:48+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function spread($order_info){
        $uid = $order_info['u_id'];
        ##物流推广分成(总金额下面的分成比例。规则，如果在express_spread_user表中，当前用户是从物流中推广出来的，物流用户享有分成)
            #当前uid是否有分成出去
            $express_id = $this->getbase->getone('express_spread_user',['where'=>['uid'=>$uid],'field'=>'express_id']);
            if(is_array($express_id)){
                ##开始分成核算
                $express_get_spread_money = sprintf("%.2f",$order_info['good_price']*(getset('spread_express')/100));##分成百分比
                ##更改金额
                ##查看些订单是否有分过成（避免异常订单的重复分成）
                if($this->getbase->getcount('order_spread_log',['where'=>['order_id'=>$order_info['id']]])<1){
                    ##开始分成处理
                       if(false!==DB::name('users_express')->where("id = {$express_id['express_id']}")->setInc('money',$express_get_spread_money)){
                            ##插入日志
                             $log = [
                                'order_id'=>$order_info['id'],##订单号
                                'remarks'=>'推广分成金额+'.$express_get_spread_money,##备注
                                'create_time'=>date('Y-m-d H:i:s'),##时间
                                'order_number'=>$order_info['order_number'],##订单号
                                'log'=>'推广分成',##日志记录
                                // 'uid'=>$order_info['uid'],##用户ID
                                'money'=>$express_get_spread_money,##产生金额
                                'express_id'=>$express_id['express_id'],
                                'share_proportion'=>getset('spread_express').'%',##分成比例
                            ];
                              $re = $this->getbase->getadd('order_spread_log',$log);
                        } 
                }
                
            }
        ##酒店分成(如果收货地址中，和酒店表中的名字匹配享受分成){还没开始}
        
        
    }
    


    /**
     * [getCountNotify 获得当前门店的通知]
     * @Author   Jerry
     * @DateTime 2017-08-07T12:02:02+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function getCountNotify(){
        ##当前用户所属于门店
        $store_id =session('suinfo')['store_id'];
        $count = $this->getbase->getcount('notification',['where'=>['recipient_uid'=>$store_id,'recipient_type'=>'2','read_flag'=>0]]);
        returnJson(0,'success','',['count'=>$count]);
    }



    /**
     * [getCountNotify 取消订单]
     * @Author   Jerry
     * @DateTime 2017-08-07T12:02:02+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public  function refund_no(){
        include_once EXTEND_PATH.'WxPay.Api.php';


        $oid   = intval(input('post.oid'));//订单id
        $mch_id=config('mch_id_one');//商户号
        $order = Db::name('order')->where('id', $oid)->find();
        $total_fee = $order['order_price']*100;
        $out_trade_no = $order['order_number'];
        include_once EXTEND_PATH."WxPay.Data.php";


        // $refund_fee = $_REQUEST["refund_fee"];
        $input = new \WxPayRefund();

        $input->SetOut_trade_no($out_trade_no);
        $input->SetTotal_fee($total_fee);
        $input->SetRefund_fee($total_fee);


        $input->SetOut_refund_no($mch_id.date("YmdHis"));
        $input->SetOp_user_id($mch_id);
        $return =\WxPayApi::refund($input);


        if($return['return_code'] == 'SUCCESS' and $return['result_code'] == 'SUCCESS'){
            Cache::rm('data'.$order['u_id']);
            $array = array('order_id' =>$oid,
                'uid' => $_COOKIE['thikask_admin_uid'],
                'status' => 1,
                'type'=> 1,
                'remark' => encode(input('remarks')),
                'create_time' => time(),
                'order_number'=>$order['order_number']
            );
            $db = Db::name('order_info')->insert($array);

            $data['status'] =11;
            $data['msg'] ='店铺取消订单';
            $db = Db::table('qlbl_order')
                ->where('id', $order['id'])
                ->update($data);

            $res['pay_type'] =1;
            $res['remarks'] ='用户取消订单';
            $db = Db::table('qlbl_pay_log')
                ->where('order_id', $order['order_number'])
                ->update($res);
        }
    }


    public function del_cache($id){
        $sid = explode(',',$id);
        foreach ($sid as $k => $v){
            $order =  Db::table('qlbl_order')->field('u_id')->where('id ='.$v)->find();
            Cache::rm('data'.$order['u_id']);
        }
    }



    /**
     * [editwork 门店职员]
     * @Author   Jerry
     * @DateTime 2017-08-01T15:55:00+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function editwork(){
        if($this->request->isPost()){
            $data = input();
            if(empty($data['user_name'])||empty($data['mobile']))  returnJson(1,'姓名，或者手机号不能为空');
            $data['mobile'] = empty($data['mobile'])?"":encode($data['mobile']);
            if((int)$data['id']>0){
                if(empty($data['password'])){
                    unset($data['password']);
                }else{
                    $data['password'] = encode($data['password']);
                }
                $id = $data['id'];
                unset($data['id']);
                $this->getbase->getedit('store_users',['where'=>['id'=>(int)$id]],$data);
                returnJson(0,'',$data['gourl']);
            }else{
                unset($data['id']);
                $data['password'] = empty($data['password'])?encode('123456'):encode($data['password']);
                if($this->getbase->getadd('store_users',$data)){
                    returnjson(0,'',$data['gourl']);
                }else{
                    returnjson(1);
                }

            }

        }
    }

    public function edit_group(){


        $arrs = $_POST['ids'];

        $str = implode(',',$arrs);



        $order = Db::name('store_group')->where('uid', $_POST['where'])->find();
    if(!empty($order)){
        $c['group']     = $str;
        Db::table('qlbl_store_group')
            ->where('uid',  $_POST['where'])
            ->update($c);
    }else{
        $c['group']     = $str;
        $c['uid']   = $_POST['where'];

        $db = Db::name('store_group')->insert($c);
    }
        returnJson(0,'','/manager/index/group');


        //returnjson(0,'',$data['gourl']);



    }
/**
 * [rbacauth 授权]
 * @Author   Jerry
 * @DateTime 2017-08-23T10:09:54+0800
 * @Example  eg:
 * @return   [type]                   [description]
 */
public function rbacauth(){
      if($this->request->isPost()){
        // $suinfo = session('suinfo');
        $department_id = (int)input('department_id');
        if($department_id<1) returnJson(1,'没有指定权限所有者');
        $data = input();
        // show($suinfo);
        ##清掉之前的数据
        $this->getbase->getdel('store_auth',['where'=>['store_department_id'=>$department_id]]);
        foreach ($data['auth'] as $k => $v) {
           $authData = [
            'url'=>trim($v,'/'),
            'store_department_id'=>$data['department_id'],
            'extra'=>'',

           ];
           $this->getbase->getadd('store_auth',$authData);
        }
        returnJson(0,'授权成功');
      }  
    }

    /**
     * [redistribution 门店重新派单]
     * @Author   WuSong
     * @DateTime 2017-09-28T11:56:48+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function redistribution(){
        if($this->request->isPost()){
            $data = input();
            if(!$data['express_id']) return returnJson('1','请先指取件的派物流小哥');
            if(isset($data['ids'])&&is_array($data['ids'])){
                $idsStr = implode(",", $data['ids']);

                ##删除用户缓存 后期更改 为了时间 直接删除缓存
                $this->del_cache($idsStr);
                ##查询中间表
                $eou_info = $this->getbase->getall('express_order_users',['where'=>"eou.status=-1 and o.id in($idsStr)",'alias'=>'eou','join'=>[['qlbl_order o','o.id=eou.order_id']],'field'=>'o.id,eou.express_id,eou.status,eou.remark,eou.create_date,o.user_address,o.order_number,o.take_time,eou.id eou_id']);
                ##物流员相关信息
                $expressinfo = $this->getbase->getone('users_express',['where'=>['id'=>$data['express_id']],'field'=>'wx_openid,realname']);
                foreach ($eou_info as $k => $v) {                    
                    ##发送微信消息
                    if($expressinfo['wx_openid']){
                        ##如果发送不成功，可能是IP白明单的问题
                        $re = $this->wx_msg($expressinfo['wx_openid'],[
                                                                'url'=>'http://www.qiaolibeilang.com/express/order/index.html',
                                                                'first'=>$expressinfo['realname'].',您有一个新的物流订单，请您尽快取件',
                                                                'keyword1'=>$v['order_number'],
                                                                'keyword2'=>'取件',
                                                                'keyword3'=>$v['take_time'],
                                                                'remark'=>'取件地址:'.$v['user_address'].';点击查看详细信息',
                                                                    ]);

                      
                    }
                    ##发送微信消息
                    ##指定物流
                    
                    if(false!==$this->getbase->getedit('express_order_users',['where'=>['id'=>$v['eou_id']]],['express_id'=>$v['express_id'],'status'=>0,'create_date'=>date('Y-m-d H:i:s')])){
                        ##写入物流信息
                        $log = [
                          'express_id'=>$data['express_id'],
                          'ip'=>fetch_ip(),
                          'log'=>"分配物流员，等待物流员确认取件",
                          'create_time'=>date('Y-m-d H:i:s'),
                          'order_id'=>$v['id'],
                          'edu_id'=>$v['eou_id'],
                          'change_status'=>'0',
                        ];
                        $this->getbase->getadd('express_order_users_log',$log);
                        ##指定物流

                    }
                    
                }
                
                returnJson(0,'重新派单成功');
            }else{
               returnJson(1,'您需要选择订单才能操作!');
            }
        }
    }



}
