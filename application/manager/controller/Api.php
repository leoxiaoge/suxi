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
class Api extends Base
{

	public function _initialize()
    {
     	parent::_initialize();  
     	
    }
    /**22+28
     * [dologin 职员登陆]
     * @Author   Jerry
     * @DateTime 2017-08-02T12:14:56+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function dologin()
    {
        if($this->request->isPost()){
            $mobile = addslashes(input('mobile'));
            $password = addslashes(input('password'));
            if(empty($mobile)) returnJson('1','手机号不能为空');
            if(empty($password)) returnJson('1','密码不能为空');
            ##用户名是否存在
            if($info = $this->getbase->getone('store_users',['where'=>['mobile'=>encode($mobile)]])){
                // show($info);
                if($info['password']==encode($password)){
                    session('suid',$info['id']);
                    session('suinfo',$info);
                    returnJson(0,'登陆成功',decode(input('gourl')));
                }else{
                    returnJson(1,'密码错误');
                }
            }else{
                returnJson(1,'用户不存在');
            }
        }
     // return $this->fetch();
		
    }
    /**
     * [changepwd 修改密码]
     * @Author   Jerry
     * @DateTime 2017-08-24T09:39:35+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function changepwd(){
        if($this->request->isPost()){
            ##当前用户的密码
            $suinfo = session('suinfo');
            $data = input();
            if(encode($data['old_pwd'])!=$suinfo['password']) returnJson(1,'原始密码错误');
            if($data['pwd']!=$data['confirm_pwd']) returnJson(1,'新密码两次输入的密码不一至');
            $this->getbase->getedit('store_users',['where'=>['id'=>$suinfo['id']]],['password'=>encode($data['pwd'])]);
            session('suid',null);
            session('suinfo',null);
            returnJson(0,'密码修改成功');

        }
            // session('suid',$info['id']);
                    // session('suinfo',$info);
    }
    /**
     * [editsetting 添加门店基本设置]
     * @Author   Jerry
     * @DateTime 2017-08-04T18:32:51+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function editsetting(){
        if($this->request->isPost()){
            if((int)input('store_id')<1) returnJson(1,'门店的ID不能为空');
            
            $data = input();
            $data['postion_count'] = (int)input('postion_count');
            ##先判读，此门店是否有此设置
            if($this->getbase->getcount('store_setting',['where'=>['store_id'=>input('store_id')]])>0){
                ##修改
                $this->getbase->getedit('store_setting',['where'=>['store_id'=>input('store_id')]],$data);
                returnJson(0);
            }else{
                ##新增
                if($this->getbase->getadd('store_setting',$data)){
                    returnJson(0);
                }else{
                    returnJson(1);
                }
            }
        }
    }
    /**
     * [accept 门店接单]
     * @Author   Jerry
     * @DateTime 2017-08-04T21:13:33+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function accept(){
        if($this->request->isPost()){


          
            $data = input();
            if(!$data['express_id']) return returnJson('1','请先指取件的派物流小哥');
            if(isset($data['ids'])&&is_array($data['ids'])){
                $idsStr = implode(",", $data['ids']);
                ##删除用户缓存 后期更改 为了时间 直接删除缓存
                $this->del_cache($idsStr);
                ##更改订单状态
                $this->getbase->getedit('order',['where'=>['id'=>['in',$idsStr]]],['status'=>4]);
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
                    $orderInfo['remark'] = '配送员正在取件途中';
                    $orderInfo['uid'] = $store_id;##门店ID
                    $orderInfo['type'] = 1;##1为商家2为快递员
                    $orderInfo['express_id'] = $data['express_id'];##取件员的ID
                    $orderInfo['order_number'] = $v['order_number'];##门店ID
                    $orderInfo['order_status'] = 4;
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
     * [check 前台检查]
     * @Author   Jerry
     * @DateTime 2017-08-25T11:37:32+0800
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
                $this->getbase->getedit('order',['where'=>['id'=>['in',$idsStr]]],['status'=>5]);
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
                    $orderInfo['order_status'] = 5;
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
                        'change_status'=>'4',
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
     * [doing 开始清洗]
     * @Author   Jerry
     * @DateTime 2017-09-08T08:50:08+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function doing(){
        if($this->request->isPost()){
            $data = input();
            if(isset($data['ids'])&&is_array($data['ids'])){
                $idsStr = implode(",", $data['ids']);
                ##删除用户缓存 后期更改 为了时间 直接删除缓存
                $this->del_cache($idsStr);
                ##更改订单状态
                $this->getbase->getedit('order',['where'=>['id'=>['in',$idsStr]]],['status'=>6]);
                ##指插入订单信息
                $orderInfo = [];
                ##订单的相关信息
                $orderinfos = $this->getbase->getall('order',['where'=>['id'=>['in',$idsStr]],'field'=>'order_number,id,u_id']);
                foreach ($orderinfos as $k => $v) {
                    $orderInfo['order_id'] = $v['id'];
                    Cache::rm('data'.$v['u_id']);
                    $orderInfo['create_time'] = time();
                    $orderInfo['remark'] = '检查完成,开始清洗';
                    $orderInfo['uid'] = session('suinfo')['store_id'];##门店ID
                    $orderInfo['type'] = 1;##1为商家2为快递员
                    $orderInfo['order_number'] = $v['order_number'];##订单号.
                    $orderInfo['order_status'] = 6;
                    $re = $this->getbase->getadd('order_info',$orderInfo);


                    ##清掉挂位
                   // $re = $this->getbase->getedit('order_pendant',['where'=>['order_number'=>$v['order_number']]],['status'=>'-1']);
                    
                }
                returnJson(0,'开始清洗');
            }else{
               returnJson(1,'您需要选择订单才能操作!');
            }
        }
    }


  




    /**
     * [washfinish 完成清洗]
     * @Author   Jerry
     * @DateTime 2017-09-08T08:58:03+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function washfinish(){
         if($this->request->isPost()){
            $data = input();
            if(isset($data['ids'])&&is_array($data['ids'])){
                $idsStr = implode(",", $data['ids']);
                ##删除用户缓存 后期更改 为了时间 直接删除缓存
                $this->del_cache($idsStr);
                ##更改订单状态
                $this->getbase->getedit('order',['where'=>['id'=>['in',$idsStr]]],['status'=>7]);
                ##指插入订单信息
                $orderInfo = [];
                ##订单的相关信息
                $orderinfos = $this->getbase->getall('order',['where'=>['id'=>['in',$idsStr]],'field'=>'order_number,id,u_id']);
                foreach ($orderinfos as $k => $v) {
                    $orderInfo['order_id'] = $v['id'];
                        Cache::rm('data'.$v['u_id']);
                    $orderInfo['create_time'] = time();
                    $orderInfo['remark'] = '已清洗完成，等待分配配送员';
                    $orderInfo['uid'] = session('suinfo')['store_id'];##门店ID
                    $orderInfo['type'] = 1;##1为商家2为快递员
                    $orderInfo['order_number'] = $v['order_number'];##门店ID
                    $orderInfo['order_status'] = 7;
                    $re = $this->getbase->getadd('order_info',$orderInfo);
                    ##清掉挂位
                   // $re = $this->getbase->getedit('order_pendant',['where'=>['order_number'=>$v['order_number']]],['status'=>'-1']);
                    
                }
                returnJson(0,'清洗完成');
            }else{
               returnJson(1,'您需要选择订单才能操作!');
            }
        }
    }
    /**
     * [dispatch 开始配送]
     * @Author   Jerry
     * @DateTime 2017-08-07T10:40:06+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function dispatch(){
         if($this->request->isPost()){
            $data = input();
            if(!$data['express_id']) return returnJson('1','请先指派件的物流小哥');
            if(isset($data['ids'])&&is_array($data['ids'])){
                $idsStr = implode(",", $data['ids']);
                ##删除用户缓存 后期更改 为了时间 直接删除缓存
                $this->del_cache($idsStr);
                ##更改订单状态
                $this->getbase->getedit('order',['where'=>['id'=>['in',$idsStr]]],['status'=>8]);
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
                    $orderInfo['order_status'] = 8;
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
                $this->getbase->getedit('order',['where'=>['id'=>['in',$idsStr]]],['status'=>9]);
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
                    $orderInfo['order_status'] = 9;
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

/**
 * [e_refund 取消订单]
 * @Author   Jerry
 * @DateTime 2017-10-30T10:20:16+0800
 * @Example  eg:
 * @return   [type]                   [description]
 */
public  function e_refund(){
    if($this->request->isPost()){
            $storInfo = session('suinfo');
            $id = (int)input('id');
            $orderInfo = $this->getbase->getone('order',['where'=>['id'=>$id]]);
            $remarks = addslashes(input('remarks'));
            if(!$orderInfo) returnJson(1,'订单信息有误');
            if(!$remarks) returnJson(1,'取消原因不能为空');
            $log = [
                'order_id' =>$id,
                'uid' => $storInfo['store_id'],
                'status' => 1,
                'type'=> 1,
                'remark' => $remarks,
                'create_time' => time(),
                'order_number'=>$orderInfo['order_number'],
            ];
            ##处理客户显示的信息
            $this->getbase->getadd('order_info',$log);
            ##订单信息
            $order['status'] = '-20';##取消预约
            $order['msg'] = '商家取消预约，原因:'.$remarks;
            $this->getbase->getedit('order',['where'=>['id'=>$id]],$order);
            returnJson(0,'取消预约成功','','_parent_reload');##close-parent-reload父级页面刷新
        }
     }
     /**
      * [e_getgoods 取衣]
      * @Author   Jerry
      * @DateTime 2017-10-30T11:12:17+0800
      * @Example  eg:
      * @return   [type]                   [description]
      */
  public function e_getgoods(){
        if($this->request->isPost()){
            $data = input();
            $status = 20;
            if(!$data['express_id']) return returnJson('1','请先指定取件员');
            if(isset($data['ids'])&&is_array($data['ids'])){
                $idsStr = implode(",", $data['ids']);
                ##更改订单状态
                $this->getbase->getedit('order',['where'=>['id'=>['in',$idsStr]]],['status'=>$status]);
                $orderInfo = [];
                ##订单的相关信息
                $orderinfos = $this->getbase->getall('order',['where'=>['id'=>['in',$idsStr]],'field'=>'order_number,id,u_id,user_address,take_time']);
                $storeInfo =session('suinfo');
                ##物流员相关信息
                $expressinfo = $this->getbase->getone('users_express',['where'=>['id'=>$data['express_id']],'field'=>'wx_openid,realname']);
                foreach ($orderinfos as $k => $v) {
                    ##订单状态日志
                    $orderInfo['order_id'] = $v['id'];
                    $orderInfo['create_time'] = time();
                    $orderInfo['remark'] = '物流员正在前往取件中';
                    $orderInfo['uid'] = $storeInfo['store_id'];##门店ID
                    $orderInfo['type'] = 1;##1为商家2为快递员
                    $orderInfo['express_id'] = $data['express_id'];##取件员的ID
                    $orderInfo['order_number'] = $v['order_number'];##门店ID
                    $orderInfo['order_status'] = $status;
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
                          'change_status'=>$status,
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
     * [check 衣服洗前检查]
     * @Author   Jerry
     * @DateTime 2017-08-25T11:37:32+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function e_check(){
        if($this->request->isPost()){
            $data = input();
            $status = 60;
            if(isset($data['ids'])&&is_array($data['ids'])){
                $idsStr = implode(",", $data['ids']);
                ##更改订单状态
                $this->getbase->getedit('order',['where'=>['id'=>['in',$idsStr]]],['status'=>$status]);
                ##指插入订单信息
                $orderInfo = [];
                ##订单的相关信息
                $orderinfos = $this->getbase->getall('order',['where'=>['id'=>['in',$idsStr]],'field'=>'order_number,id,u_id,good_num']);
                $storeInfo = session('suinfo');
                ##生成挂件
                ##有多少挂位
                $postion_count = $this->getbase->getone('store_setting',['where'=>['store_id'=>$storeInfo['store_id']],'field'=>'postion_count']);
                foreach ($orderinfos as $k => $v) {
                    $params = [
                        'order_id'=> $v['id'],
                        'uid' => $storeInfo['store_id'],##门店ID
                        'order_number' => $v['order_number'],
                        'order_status' => $status,

                    ];
                    order_info($params);
                    $good_num = array_sum(explode(",", trim($v['good_num'],',')));
                    ##物流取件金额
                    $this->pickup($v);
                    #挂号相关
                    $this->_pendant($v,$good_num,$storeInfo['store_id']);
                     ##挂号相关
 
                        ##挂件管理E
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
                        'change_status'=>$status,
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
 * [e_remark 订单管理员备注]
 * @Author   Jerry
 * @DateTime 2017-10-30T11:36:32+0800
 * @Example  eg:
 * @return   [type]                   [description]
 */
public function e_remark(){
    if($this->request->isPost()){
        $admin_remarks =addslashes(input('admin_remarks'));
        $id = (int)input('id');
        ##更新管理员备注信息
        if(false!==$this->getbase->getedit('order',['where'=>['id'=>$id]],['admin_remarks'=>$admin_remarks])){
            returnJson(0,'备注信息处理成功');
        }else{
            returnJson(1,'服务器繁忙，请稍后再试');
        }
    }
}

        /**
         * [e_dispatch 清洗完成配送]
         * @Author   WuSong
         * @DateTime 2017-10-31T19:00:35+0800
         * @Example  eg:
         * @return   [type]                   [description]
         */
         public function e_dispatch(){
         if($this->request->isPost()){
            $data = input();
            $status = 80;
            if(!$data['express_id']) return returnJson('1','请先指派件的物流小哥');
            if(isset($data['ids'])&&is_array($data['ids'])){
                $idsStr = implode(",", $data['ids']);
                ##删除用户缓存 后期更改 为了时间 直接删除缓存
                $this->del_cache($idsStr);
                ##更改订单状态
                $this->getbase->getedit('order',['where'=>['id'=>['in',$idsStr]]],['status'=>$status]);
                ##指插入订单信息
                $orderInfo = [];
                ##订单的相关信息
                $orderinfos = $this->getbase->getall('order',['where'=>['id'=>['in',$idsStr]],'field'=>'order_number,id,u_id,user_address,give_time']);
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
                    $orderInfo['order_status'] = $status;
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
                          'change_status'=>$status,
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
     * [e_finish 订单完结]
     * @Author   WuSong
     * @DateTime 2017-10-31T19:17:35+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
     public function e_finish(){
        if($this->request->isPost()){
            $data = input();
            $status = 100;
            if(isset($data['ids'])&&is_array($data['ids'])){
                $idsStr = implode(",", $data['ids']);
                ##删除用户缓存 后期更改 为了时间 直接删除缓存
                $this->del_cache($idsStr);
                ##更改订单状态
                $this->getbase->getedit('order',['where'=>['id'=>['in',$idsStr]]],['status'=>$status]);
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
                    $orderInfo['order_status'] = $status;
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
                        'change_status'=>'100',
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
     * [e_take_redistribution 取件异常重新派单]
     * @Author   WuSong
     * @DateTime 2017-11-01T16:37:44+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function e_take_redistribution(){
        if($this->request->isPost()){
            $data = input();
            $status = 20;
            if(!$data['express_id']) return returnJson('1','请先指取件的派物流小哥');
            if(isset($data['ids'])&&is_array($data['ids'])){
                $idsStr = implode(",", $data['ids']);
                ##删除用户缓存 后期更改 为了时间 直接删除缓存
                ##更改订单状态
                $this->getbase->getedit('order',['where'=>['id'=>['in',$idsStr]]],['status'=>$status]);
                $this->del_cache($idsStr);
                ##查询中间表
                $eou_info = $this->getbase->getall('order',['where'=>"eou.type = 1 and eou.status= -1 and o.id  in ($idsStr)",'alias'=>'o','join'=>[['qlbl_express_order_users eou','eou.order_id=o.id']],'field'=>'o.id,o.order_number,o.user_name,o.good_name,o.user_phone,o.good_num,o.good_price,o.order_price,o.user_address,o.take_time,o.status,eou.type,eou.create_date,eou.remark eouremark,eou.id eou_id'],'LEFT');
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
                    $id = $v['eou_id'];
                    if(false!==$this->getbase->getedit('express_order_users',['where'=>"id =$id and type =1"],['express_id'=>$data['express_id'],'status'=>0,'create_date'=>date('Y-m-d H:i:s')])){
                           
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

    /**
     * [e_take_redistribution 送件异常重新派单]
     * @Author   WuSong
     * @DateTime 2017-11-01T17:01:05+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function e_give_redistribution(){
        if($this->request->isPost()){
            $data = input();
            $status = 80;
            if(!$data['express_id']) return returnJson('1','请先指取件的派物流小哥');
            if(isset($data['ids'])&&is_array($data['ids'])){
                $idsStr = implode(",", $data['ids']);
                ##删除用户缓存 后期更改 为了时间 直接删除缓存
                

                $this->del_cache($idsStr);
                 ##更改订单状态
                $this->getbase->getedit('order',['where'=>['id'=>['in',$idsStr]]],['status'=>$status]);
                ##查询中间表
                $eou_info = $this->getbase->getall('order',['where'=>"eou.type = 2 and eou.status= -1 and o.id  in ($idsStr)",'alias'=>'o','join'=>[['qlbl_express_order_users eou','eou.order_id=o.id']],'field'=>'o.id,o.order_number,o.user_name,o.good_name,o.user_phone,o.good_num,o.good_price,o.order_price,o.user_address,o.take_time,o.status,eou.type,eou.create_date,eou.remark eouremark,eou.id eou_id'],'LEFT');
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
                    $id = $v['eou_id'];
                    if(false!==$this->getbase->getedit('express_order_users',['where'=>"id =$id and type =2"],['express_id'=>$data['express_id'],'status'=>0,'create_date'=>date('Y-m-d H:i:s')])){
                            
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


    //##以下为订单工序操作
    //status值判断从40-80
    /**
     * [status_fourzero 洗前检查]
     * @Author   WuSong
     * @DateTime 2017-11-03T10:09:19+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function status_fourzero(){
        if($this->request->isPost()){
            $request  = input();
            $status = 60;
            if(is_array($request['order_id'])){
                returnJson(1,'参数错误');
            }
            $order = $this->getbase->getone('order',['where'=>['id'=>$request['order_id']]]);
                 if($order['status'] !=40){
                        return returnJson('1','操作失败，请确认已付款后再进行操作');
                    }
                ##更改订单状态
                $this->getbase->getedit('order',['where'=>['id'=>$request['order_id']]],['status'=>$status]);
                ##订单的相关信息
                $orderinfo = $this->getbase->getone('order',['where'=>['id'=>$request['order_id']],'field'=>'id,order_number,id,u_id,good_num']);

                $storeInfo = session('suinfo');
                ##生成挂件

                       $params = [
                        'order_id'=> $orderinfo['id'],
                        'uid' => $storeInfo['store_id'],##门店ID
                        'order_number' => $orderinfo['order_number'],
                        'order_status' => $status,

                    ];
                    order_info($params);

                    $good_num = array_sum(explode(",", trim($orderinfo['good_num'],',')));
                    ##物流取件金额
                    $this->pickup($orderinfo);
                    #挂号相关
                    $this->_pendant($orderinfo,$good_num,$storeInfo['store_id']);
                     ##挂号相关

                    ##挂件管理E
            
                   ##处理物流信息
                    #物流关联表状态改为已完成，生成物流关联表日志
                    #type=>1 收衣的状态
                     if(false!==$this->getbase->getedit('express_order_users',['where'=>['order_id'=>$orderinfo['id'],'type'=>1],'order'=>'id desc'],['status'=>'4'])){
                        $eouinfo = $this->getbase->getone('express_order_users',['where'=>['order_id'=>$orderinfo['id'],'type'=>1],'order'=>'id desc']);
                        ##写入日志
                      $log = [
                        'express_id'=>$eouinfo['express_id'],
                        'ip'=>fetch_ip(),
                        'log'=>"门店已收到衣服",
                        'create_time'=>date('Y-m-d H:i:s'),
                        'remark'=>'门店已收到衣服，并检查完成',
                        'order_id'=>$eouinfo['order_id'],
                        'edu_id'=>$eouinfo['id'],
                        'change_status'=>$status,
                        // 'admin_id'=>
                      ];
                      $this->getbase->getadd('express_order_users_log',$log);
                        returnJson(0,'检查完成');

                     }
                    
                returnJson(1,'操作失败，请重新操作!');
            }
        
    }

/**
 * [_pendant 订单挂号处理]
 * @Author   Jerry
 * @DateTime 2017-11-06T17:49:35+0800
 * @Example  eg:
 * @param    int                    $good_num [订单数量（总数据）]
 * @return   [type]                             [description]
 */
private function _pendant($orderinfo,$good_num='',$store_id=1){
    ##挂号
    if((int)$good_num){
        for ($l = 1; $l<=$good_num; $l++){
             $order_pendant = $this->getbase->getone('order_pendant',['order'=>'id desc']);
            ##挂号相关
            if($order_pendant['t_code'] > 0){
                $i = $order_pendant['t_code']+1;
            }else{
                $i = 850000;
            }
           $array = [
                'order_number'=>$orderinfo['order_number'],
               'order_id'=>$orderinfo['id'],
               'piece_id'=>$good_num.'-'.$l,
               't_code'=>$i,
               'store_id'=>$store_id,
           ]; 
           $this->getbase->getadd('order_pendant',$array);           
        }
        
    }
    ##挂位
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
  $order_colse_count = $this->getbase->getall('order_pendant',['where'=>['order_number'=>$orderinfo['order_number']],'field'=>'order_id,piece_id,id,order_number']);
    $thumbKey=0;
    $postion_count = $this->getbase->getone('store_setting',['where'=>['store_id'=>$store_id],'field'=>'postion_count']);##门店总挂位
    ##循环挂位 去掉已使用的挂位。把订单放入到未使用的挂位 $thumbKey：数组KEY,  $i:当前位置号
    for ($i=1; $i < ($postion_count['postion_count']+1); $i++) { 
        if($thumbKey<count($order_colse_count)){
             ##此挂号没有被用，并且此订单挂号信息存在
           if(!in_array($i, $over_hang_number_format)&&isset($order_colse_count[$thumbKey])){
                ##分配挂号
                $re = $this->getbase->getedit('order_pendant',['where'=>['id'=>$order_colse_count[$thumbKey]['id']]],['store_id'=>$store_id,'hang_number'=>($thumbKey+1),'status'=>1]);##($thumbKey+1) 挂号编号从而开始
                $thumbKey++;
           }
       }
           
    }
    return true;
    
}
    /**
     * [status_sixzero 衣服分类]
     * @Author   WuSong
     * @DateTime 2017-11-03T10:10:57+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function status_sixzero(){
        if($this->request->isPost()){
            $request = input();
            $status = 61;
            $order = $this->getbase->getone('order',['where'=>['id'=>$request['order_id']]]);
            //判断是否有此订单
                if($this->getbase->getcount('order',['where'=>['id'=>$request['order_id']]])>0){
                    //判断订单表状态是否为已检查
                    if($order['status'] !=60){
                        return returnJson('1','操作失败，请确认已检查后再进行操作');
                    }
                    if(false!==$this->getbase->getedit('order',['where'=>['id'=>$request['order_id']]],['status'=>$status])){

                        //插入日志
                        $log =[
                            'order_id'=>$request['order_id'],
                            'create_time'=>time(),
                            'remark' =>'衣服分类',
                            'uid'=>1,
                            'type'=>1,
                            'status'=>1,
                            'order_number'=>$order['order_number'],
                            'order_status'=>$status,
                        ];
                        if($this->getbase->getadd('order_info',$log)){
                            return returnJson(0,'操作成功');
                        }
                    }
            }
        }
    }
    /**
     * [status_sixweone 衣服洗前处理]
     * @Author   WuSong
     * @DateTime 2017-11-03T10:14:02+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function status_sixone(){
        if($this->request->isPost()){
           $request = input();
            $status = 62;
            $order = $this->getbase->getone('order',['where'=>['id'=>$request['order_id']]]);
            //判断是否有此订单
                if($this->getbase->getcount('order',['where'=>['id'=>$request['order_id']]])>0){
                    //判断订单表状态是否为已支付
                    if($order['status'] !=61){
                        return returnJson('1','操作失败，请确认衣服已分类后再进行操作');
                    }
                    if(false!==$this->getbase->getedit('order',['where'=>['id'=>$request['order_id']]],['status'=>$status])){

                        //插入日志
                        $log =[
                            'order_id'=>$request['order_id'],
                            'create_time'=>time(),
                            'remark' =>'衣服洗前处理',
                            'uid'=>1,
                            'type'=>1,
                            'status'=>1,
                            'order_number'=>$order['order_number'],
                            'order_status'=>$status,
                        ];
                        if($this->getbase->getadd('order_info',$log)){
                            return returnJson(0,'操作成功');
                        }
                    }
            }
        }
    }

    /**
     * [status_sixtwo 衣服洗涤（干洗/水洗）]
     * @Author   WuSong
     * @DateTime 2017-11-03T10:15:09+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function status_sixtwo(){
        if($this->request->isPost()){
           $request  = input();
            $status = 63;
            $order = $this->getbase->getone('order',['where'=>['id'=>$request['order_id']]]);
            //判断是否有此订单
                if($this->getbase->getcount('order',['where'=>['id'=>$request['order_id']]])>0){
                    //判断订单表状态是否为已支付
                    if($order['status'] !=62){
                        return returnJson('1','操作失败，请确认已洗前处理后再进行操作');
                    }
                    if(false!==$this->getbase->getedit('order',['where'=>['id'=>$request['order_id']]],['status'=>$status])){

                        //插入日志
                        $log =[
                            'order_id'=>$request['order_id'],
                            'create_time'=>time(),
                            'remark' =>'衣服洗涤（干洗/水洗）',
                            'uid'=>1,
                            'type'=>1,
                            'status'=>1,
                            'order_number'=>$order['order_number'],
                            'order_status'=>$status,
                        ];
                        if($this->getbase->getadd('order_info',$log)){
                            return returnJson(0,'操作成功');
                        }
                    }
            }
        }
    }

    /**
     * [status_sixthree 衣服烘干]
     * @Author   WuSong
     * @DateTime 2017-11-03T10:17:13+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function status_sixthree(){
        if($this->request->isPost()){
            $request = input();
            $status = 64;
            $order = $this->getbase->getone('order',['where'=>['id'=>$request['order_id']]]);
            //判断是否有此订单
                if($this->getbase->getcount('order',['where'=>['id'=>$request['order_id']]])>0){
                    //判断订单表状态是否为已支付
                    if($order['status'] !=63){
                        return returnJson('1','操作失败，请确认已衣服洗涤后再进行操作');
                    }
                    if(false!==$this->getbase->getedit('order',['where'=>['id'=>$request['order_id']]],['status'=>$status])){

                        //插入日志
                        $log =[
                            'order_id'=>$request['order_id'],
                            'create_time'=>time(),
                            'remark' =>'衣服烘干',
                            'uid'=>1,
                            'type'=>1,
                            'status'=>1,
                            'order_number'=>$order['order_number'],
                            'order_status'=>$status,
                        ];
                        if($this->getbase->getadd('order_info',$log)){
                            return returnJson(0,'操作成功');
                        }
                    }
            }
        }
    }

    /**
     * [status_sixfour 衣服整烫(熨烫/缝补)]
     * @Author   WuSong
     * @DateTime 2017-11-03T10:17:58+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function status_sixfour(){
        if($this->request->isPost()){
            $request = input();
            $status = 65;
            $order = $this->getbase->getone('order',['where'=>['id'=>$request['order_id']]]);
            //判断是否有此订单
                if($this->getbase->getcount('order',['where'=>['id'=>$request['order_id']]])>0){
                    //判断订单表状态是否为已支付
                    if($order['status'] !=64){
                        return returnJson('1','操作失败，请确认衣服已烘干后再进行操作');
                    }
                    if(false!==$this->getbase->getedit('order',['where'=>['id'=>$request['order_id']]],['status'=>$status])){

                        //插入日志
                        $log =[
                            'order_id'=>$request['order_id'],
                            'create_time'=>time(),
                            'remark' =>'衣服整烫(熨烫/缝补)',
                            'uid'=>1,
                            'type'=>1,
                            'status'=>1,
                            'order_number'=>$order['order_number'],
                            'order_status'=>$status,
                        ];
                        if($this->getbase->getadd('order_info',$log)){
                            return returnJson(0,'操作成功');
                        }
                    }
            }
        }
    }

    /**
     * [status_sixfive 衣服洗后检查]
     * @Author   WuSong
     * @DateTime 2017-11-03T10:20:05+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function status_sixfive(){
        if($this->request->isPost()){
            $request = input();
            $status = 66;
            $order = $this->getbase->getone('order',['where'=>['id'=>$request['order_id']]]);
            //判断是否有此订单
                if($this->getbase->getcount('order',['where'=>['id'=>$request['order_id']]])>0){
                    //判断订单表状态是否为已支付
                    if($order['status'] !=65){
                        return returnJson('1','操作失败，请确认衣服已整烫后再进行操作');
                    }
                    if(false!==$this->getbase->getedit('order',['where'=>['id'=>$request['order_id']]],['status'=>$status])){

                        //插入日志
                        $log =[
                            'order_id'=>$request['order_id'],
                            'create_time'=>time(),
                            'remark' =>'衣服洗后检查',
                            'uid'=>1,
                            'type'=>1,
                            'status'=>1,
                            'order_number'=>$order['order_number'],
                            'order_status'=>$status,
                        ];
                        if($this->getbase->getadd('order_info',$log)){
                            return returnJson(0,'操作成功');
                        }
                    }
            }
        }
    }
    /**
     * [status_sixsix 完成订单清洗]
     * @Author   WuSong
     * @DateTime 2017-11-03T10:22:03+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function status_sixsix(){
        if($this->request->isPost()){
            $request = input();
            $status = 70;
            $order = $this->getbase->getone('order',['where'=>['id'=>$request['order_id']]]);
            //判断是否有此订单
                if($this->getbase->getcount('order',['where'=>['id'=>$request['order_id']]])>0){
                    //判断订单表状态是否为已支付
                    if($order['status'] !=66){
                        return returnJson('1','操作失败，请确认已洗后检查后再进行操作');
                    }
                    if(false!==$this->getbase->getedit('order',['where'=>['id'=>$request['order_id']]],['status'=>$status])){

                        //插入日志
                        $log =[
                            'order_id'=>$request['order_id'],
                            'create_time'=>time(),
                            'remark' =>'清洗完成',
                            'uid'=>1,
                            'type'=>1,
                            'status'=>1,
                            'order_number'=>$order['order_number'],
                            'order_status'=>$status,
                        ];
                        if($this->getbase->getadd('order_info',$log)){
                            return returnJson(0,'操作成功');
                        }
                    }
            }
        }
    }

    /**
     * [status_sevenzero 开始配送]
     * @Author   WuSong
     * @DateTime 2017-11-03T10:23:54+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function status_sevenzero(){
        if($this->request->isPost()){
            $request = input();    
            $status = 80;
            if(!$request['express_id']){
                return returnJson('1','请先指取件的派物流小哥');
            }
            $order = $this->getbase->getone('order',['where'=>['id'=>$request['order_id']]]);
            //判断是否有此订单
                if($this->getbase->getcount('order',['where'=>['id'=>$request['order_id']]])>0){
                    //判断订单表状态是否为已支付
                    if($order['status'] !=70){
                        return returnJson('1','操作失败，请确认已完成订单清洗后再进行操作');
                    }
                     ##更改订单状态
                $this->getbase->getedit('order',['where'=>['id'=>['in',$request['order_id']]]],['status'=>$status]);
                ##指插入订单信息
                $orderInfo = [];
                ##订单的相关信息
                $orderinfos = $this->getbase->getall('order',['where'=>['id'=>['in',$request['order_id']]],'field'=>'order_number,id,u_id,user_address,give_time']);
                ##物流员相关信息
                $expressinfo = $this->getbase->getone('users_express',['where'=>['id'=>$request['express_id']],'field'=>'wx_openid,realname']);
                foreach ($orderinfos as $k => $v) {
                    $orderInfo['order_id'] = $v['id'];
                        Cache::rm('data'.$v['u_id']);
                    $orderInfo['create_time'] = time();
                    $orderInfo['remark'] = '开始配送中，请保持手机畅通';
                    $orderInfo['uid'] = session('suinfo')['store_id'];##门店ID
                    $orderInfo['type'] = 1;##1为商家2为快递员
                    $orderInfo['order_number'] = $v['order_number'];##门店ID
                    $orderInfo['express_id'] = $request['express_id'];##派件员的ID
                    $orderInfo['order_status'] = $status;
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
                    if($eou_id = $this->getbase->getadd('express_order_users',['express_id'=>$request['express_id'],'order_id'=>$v['id'],'type'=>'2','create_date'=>date('Y-m-d H:i:s'),'pick_up_date'=>date('Y-m-d H:i:s')])){
                        ##写入物流信息
                        $log = [
                          'express_id'=>$request['express_id'],
                          'ip'=>fetch_ip(),
                          'log'=>"等待物流员取件配送",
                          'create_time'=>date('Y-m-d H:i:s'),
                          'order_id'=>$v['id'],
                          'edu_id'=>$eou_id,
                          'change_status'=>$status,
                        ];
                        $this->getbase->getadd('express_order_users_log',$log);
                        ##指定物流

                    }

                }
                 

                returnJson(0,'开始配送');
            }else{
               returnJson(1,'操作有误，请重新操作!');
            }

        }
    }
    /**
     * [status_ninezero 配送完成]
     * @Author   WuSong
     * @DateTime 2017-11-03T15:36:02+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function status_ninezero(){
        if($this->request->isPost()){
           $request = input();
            $status = 100;
            $order = $this->getbase->getone('order',['where'=>['id'=>$request['order_id']]]);
            //判断是否有此订单
                if($this->getbase->getcount('order',['where'=>['id'=>$request['order_id']]])>0){
                    //判断订单表状态是否为物流员已接单
                    if($order['status'] !=90){
                        return returnJson('1','操作失败，请确认订单是否已送达后再进行操作');
                    }
                $this->getbase->getedit('order',['where'=>['id'=>['in',$request['order_id']]]],['status'=>$status]);
                ##指插入订单信息
                $orderInfo = [];
                ##订单的相关信息
                // show($idsStr);
                $orderinfos = $this->getbase->getall('order',['where'=>['id'=>['in',$request['order_id']]],'field'=>'order_number,id,u_id,good_price,id,u_id,user_address']);
                // show($orderinfos);
                foreach ($orderinfos as $k => $v) {
                    $orderInfo['order_id'] = $v['id'];
                        Cache::rm('data'.$v['u_id']);
                    $orderInfo['create_time'] = time();
                    $orderInfo['remark'] = '已成功送达';
                    $orderInfo['uid'] = session('suinfo')['store_id'];##门店ID
                    $orderInfo['type'] = 1;##1为商家2为快递员
                    $orderInfo['order_number'] = $v['order_number'];##门店ID
                    $orderInfo['order_status'] = $status;
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
                        'change_status'=>'100',
                        // 'admin_id'=>
                      ];
                      // show($log['order_id']);
                      $this->getbase->getadd('express_order_users_log',$log);
                     }
                    
                     //插入日志
                        $logs =[
                            'order_id'=>$request['order_id'],
                            'create_time'=>time(),
                            'remark' =>'客户已收衣，订单完结',
                            'uid'=>1,
                            'type'=>1,
                            'status'=>1,
                            'order_number'=>$v['order_number'],
                            'order_status'=>$status,
                        ];
                        $this->getbase->getadd('order_info',$logs);
                    }
                    returnJson(0,'完结订单成功');
                }else{
                   returnJson(1,'操作有误，请重新操作');
                }
            }
    }

    //清除缓存
    public function clear(){
        Cache::clear();
       returnJson(0,'清除缓存成功');
    }
    /**
     * [discount 门店优惠券赠送]
     * @Author   WuSong
     * @DateTime 2017-11-10T11:59:54+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function discount(){
        if($this->request->isPost()){
            //微信用户ID 优惠券ID 备注
            $data = input();
            if(!$data['uid']||!$data||!$data['title']) returnJson(1,'赠送失败');

            //用户信息
            $users_wx = $this->getbase->getone('users_wx',['where'=>['id'=>$data['uid']]]);
            //优惠券信息
            $coupon = $this->getbase->getone('coupon',['where'=>['id'=>$data['id']]]);
            //写入用户持有优惠券表
            $log  = [
                'c_id' =>$coupon['id'],
                'u_id' =>$users_wx['id'],
                'status'=>1,
                'start_time'=>date('Y-m-d'),
                'end_time' => date("Y-m-d",strtotime("+1 day")),
            ];
            if(false!==$this->getbase->getadd('users_coupon',$log)){
                //更改优惠券数量
                $this->getbase->getedit('coupon',['where'=>['id'=>$data['id']]],['num'=>$coupon['num']-1]);
                //写入门店赠送优惠券日志表
                $logs = [
                    'u_id'=>$users_wx['id'],
                    'c_id'=>$coupon['id'],
                    'admin_id'=>session('suid'),
                    'create_time'=>date('Y-m-d H:i:s'),
                    'c_price'=>$coupon['discount'],
                    'remark'=>$data['title']
                ];

                $this->getbase->getadd('manager_coupon_log',$logs);
                return returnJson(0,'赠送成功');

            }

            return returnJson(1,'赠送失败，请重新再试');
        }
    }

    ##################################
    #                                #
    #     以下为洗护流程批量操作     #
    #                                #
    ##################################
    /**
     * [_sixone 洗前处理（批量）]
     * @Author   WuSong
     * @DateTime 2017-11-10T14:43:13+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function _sixzero(){
        if($this->request->isPost()){
            $data = input();
            $status = 61;
            if(isset($data['ids'])&&is_array($data['ids'])){
                $idsStr = implode(",", $data['ids']);
                ##删除用户缓存 后期更改 为了时间 直接删除缓存
                $this->del_cache($idsStr);
                ##更改订单状态
                $this->getbase->getedit('order',['where'=>['id'=>['in',$idsStr]]],['status'=>$status]);
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
                    $orderInfo['remark'] = '衣服分类';
                    $orderInfo['uid'] = session('suinfo')['store_id'];##门店ID
                    $orderInfo['type'] = 1;##1为商家2为快递员
                    $orderInfo['order_number'] = $v['order_number'];##门店ID
                    $orderInfo['order_status'] = $status;
                    $re = $this->getbase->getadd('order_info',$orderInfo);  
                }
                returnJson(0,'操作成功');
            }else{
               returnJson(1,'您需要选择订单才能操作!');
            }
        }
    }

    /**
     * [_sixtwo ]
     * @Author   WuSong
     * @DateTime 2017-11-10T14:46:10+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function _sixone(){
        if($this->request->isPost()){
            $data = input();
            $status = 62;
            if(isset($data['ids'])&&is_array($data['ids'])){
                $idsStr = implode(",", $data['ids']);
                ##删除用户缓存 后期更改 为了时间 直接删除缓存
                $this->del_cache($idsStr);
                ##更改订单状态
                $this->getbase->getedit('order',['where'=>['id'=>['in',$idsStr]]],['status'=>$status]);
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
                    $orderInfo['remark'] = '衣服洗前处理';
                    $orderInfo['uid'] = session('suinfo')['store_id'];##门店ID
                    $orderInfo['type'] = 1;##1为商家2为快递员
                    $orderInfo['order_number'] = $v['order_number'];##门店ID
                    $orderInfo['order_status'] = $status;
                    $re = $this->getbase->getadd('order_info',$orderInfo);  
                }
                returnJson(0,'操作成功');
            }else{
               returnJson(1,'您需要选择订单才能操作!');
            }
        }
    }

    /**
     * [_sixthree 衣服洗涤(干洗/水洗)]
     * @Author   WuSong
     * @DateTime 2017-11-10T14:47:55+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function _sixtwo(){
        if($this->request->isPost()){
            $data = input();
            $status = 63;
            if(isset($data['ids'])&&is_array($data['ids'])){
                $idsStr = implode(",", $data['ids']);
                ##删除用户缓存 后期更改 为了时间 直接删除缓存
                $this->del_cache($idsStr);
                ##更改订单状态
                $this->getbase->getedit('order',['where'=>['id'=>['in',$idsStr]]],['status'=>$status]);
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
                    $orderInfo['remark'] = '衣服洗涤(干洗/水洗)';
                    $orderInfo['uid'] = session('suinfo')['store_id'];##门店ID
                    $orderInfo['type'] = 1;##1为商家2为快递员
                    $orderInfo['order_number'] = $v['order_number'];##门店ID
                    $orderInfo['order_status'] = $status;
                    $re = $this->getbase->getadd('order_info',$orderInfo);  
                }
                returnJson(0,'操作成功');
            }else{
               returnJson(1,'您需要选择订单才能操作!');
            }
        }
    }

    /**
     * [_sixfour 衣服烘干]
     * @Author   WuSong
     * @DateTime 2017-11-10T14:59:23+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function _sixthree(){
        if($this->request->isPost()){
            $data = input();
            $status = 64;
            if(isset($data['ids'])&&is_array($data['ids'])){
                $idsStr = implode(",", $data['ids']);
                ##删除用户缓存 后期更改 为了时间 直接删除缓存
                $this->del_cache($idsStr);
                ##更改订单状态
                $this->getbase->getedit('order',['where'=>['id'=>['in',$idsStr]]],['status'=>$status]);
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
                    $orderInfo['remark'] = '衣服烘干';
                    $orderInfo['uid'] = session('suinfo')['store_id'];##门店ID
                    $orderInfo['type'] = 1;##1为商家2为快递员
                    $orderInfo['order_number'] = $v['order_number'];##门店ID
                    $orderInfo['order_status'] = $status;
                    $re = $this->getbase->getadd('order_info',$orderInfo);  
                }
                returnJson(0,'操作成功');
            }else{
               returnJson(1,'您需要选择订单才能操作!');
            }
        } 
    }

    /**
     * [_sixfive 衣服整烫(熨烫/缝补)]
     * @Author   WuSong
     * @DateTime 2017-11-10T15:02:40+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function  _sixfour(){
        if($this->request->isPost()){
            $data = input();
            $status = 65;
            if(isset($data['ids'])&&is_array($data['ids'])){
                $idsStr = implode(",", $data['ids']);
                ##删除用户缓存 后期更改 为了时间 直接删除缓存
                $this->del_cache($idsStr);
                ##更改订单状态
                $this->getbase->getedit('order',['where'=>['id'=>['in',$idsStr]]],['status'=>$status]);
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
                    $orderInfo['remark'] = '衣服整烫(熨烫/缝补)';
                    $orderInfo['uid'] = session('suinfo')['store_id'];##门店ID
                    $orderInfo['type'] = 1;##1为商家2为快递员
                    $orderInfo['order_number'] = $v['order_number'];##门店ID
                    $orderInfo['order_status'] = $status;
                    $re = $this->getbase->getadd('order_info',$orderInfo);  
                }
                returnJson(0,'操作成功');
            }else{
               returnJson(1,'您需要选择订单才能操作!');
            }
        }
    }

    /**
     * [_sixsix 衣服洗后检查]
     * @Author   WuSong
     * @DateTime 2017-11-10T15:03:03+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function _sixfive(){
        if($this->request->isPost()){
            $data = input();
            $status = 66;
            if(isset($data['ids'])&&is_array($data['ids'])){
                $idsStr = implode(",", $data['ids']);
                ##删除用户缓存 后期更改 为了时间 直接删除缓存
                $this->del_cache($idsStr);
                ##更改订单状态
                $this->getbase->getedit('order',['where'=>['id'=>['in',$idsStr]]],['status'=>$status]);
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
                    $orderInfo['remark'] = '衣服洗后检查';
                    $orderInfo['uid'] = session('suinfo')['store_id'];##门店ID
                    $orderInfo['type'] = 1;##1为商家2为快递员
                    $orderInfo['order_number'] = $v['order_number'];##门店ID
                    $orderInfo['order_status'] = $status;
                    $re = $this->getbase->getadd('order_info',$orderInfo);  
                }
                returnJson(0,'操作成功');
            }else{
               returnJson(1,'您需要选择订单才能操作!');
            }
        }
    }


    /**
     * [_sixsix 完成订单清洗]
     * @Author   WuSong
     * @DateTime 2017-11-10T15:10:54+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function _sixsix(){
        if($this->request->isPost()){
            $data = input();
            $status = 70;
            if(isset($data['ids'])&&is_array($data['ids'])){
                $idsStr = implode(",", $data['ids']);
                ##删除用户缓存 后期更改 为了时间 直接删除缓存
                $this->del_cache($idsStr);
                ##更改订单状态
                $this->getbase->getedit('order',['where'=>['id'=>['in',$idsStr]]],['status'=>$status]);
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
                    $orderInfo['remark'] = '完成衣物清洗';
                    $orderInfo['uid'] = session('suinfo')['store_id'];##门店ID
                    $orderInfo['type'] = 1;##1为商家2为快递员
                    $orderInfo['order_number'] = $v['order_number'];##门店ID
                    $orderInfo['order_status'] = $status;
                    $re = $this->getbase->getadd('order_info',$orderInfo);  
                }
                returnJson(0,'操作成功');
            }else{
               returnJson(1,'您需要选择订单才能操作!');
            }
        }
    }


}
