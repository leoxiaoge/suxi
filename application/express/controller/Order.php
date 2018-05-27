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
namespace app\express\controller;
use app\common\controller\ExpressBase;
use think\DB;
use think\Cache;
class Order extends ExpressBase{
  public function _initialize(){
    parent::_initialize();
  }
  
  /**
     * [loginout 订单]
     * @Author   WuSong
     * @DateTime 2017-09-04T17:50:33+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */

  public function index(){
  	$data = input();
    $status = isset($data['type'])?$data['type']:'new';

    switch ($status) {
    	case 'new':
    		 $where = "eou.express_id = ".cookie('express_id')." and eou.status<2";
    		break;
    	
    	default:
    		$where = "eou.express_id = ".cookie('express_id')." and eou.status>3";
    		break;
    }
    $orderinfo = $this->getbase->getall('express_order_users',['where'=>$where,'alias'=>'eou','join'=>[['order o','o.id=eou.order_id']],'field'=>'o.id order_id,o.order_number,o.user_address,user_name,o.good_price,o.status,o.user_phone,o.good_num,o.good_name,o.take_time,eou.pick_up_date,eou.id eou_id,eou.status eoustatus,eou.type','order'=>'eou.status desc']);
    foreach ($orderinfo as $k => $v) {
       $orderinfo[$k]['good_num']=array_sum(explode(',', trim($v['good_num'],',')));

    }
      $this->assign('orderinfo',$orderinfo);
     	$this->assign('status',$status);
      $this->assign('title','订单状态');
    return $this->fetch(''); 
  }

}