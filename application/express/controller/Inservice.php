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
use app\common\controller\Base;
use think\Db;
class Inservice extends Base{
  public function _initialize(){
    parent::_initialize();
  }
  /**
   * [index 订单详情]
   * @Author   WuSong
   * @DateTime 2017-09-25T17:00:45+0800
   * @Example  eg:
   * @param    [type]                   $order [description]
   * @return   [type]                          [description]
   */
  public function index(){

  	$id = cookie('express_id');
  	$order_id =(int)input('order_id');
  	if($this->getbase->getcount('express_order_users',['where'=>['express_id'=>$id,'order_id'=>$order_id]])>0){
		// $order =$this->getbase->getone('order',['where'=>['id'=>$order_id]]);
    $order = DB::table(config('database.prefix').'order')
                        ->alias('o')
                        ->join(config('database.prefix').'express_order_users eou','o.id=eou.order_id','LEFT')
                        ->field('o.id,o.good_id,o.good_num,o.user_name,o.user_phone,o.order_number,o.user_address,o.remarks,o.status,o.take_time,o.give_time,eou.type,eou.status eoustatus')
                        ->where('o.id',$order_id)
                        ->find();

  	}else{
  		$this->error('你不能查看此订单');
  	}

  	$good_id =explode(',', trim($order['good_id'],','));
  	$good_num =explode(',', trim($order['good_num'],','));
  	foreach ($good_id as $k => $v) {
  		$good_info = $this->getbase->getall('goods',['where'=>['id'=>$v]]);
  		foreach ($good_info as $ke => $ve) {
  			$ve['picture']= get_file_path($ve['picture']);
  			$ve['num']  = $good_num[$k];
  			$ve['prices']= $good_num[$k]*$ve['price'];
  			$ve['express_id'] = $id;
  			$data[]=$ve;

  		}

  	};
    $user_phone =decode($order['user_phone']);
    $this->assign('user_phone',$user_phone);
  	$this->assign('data',$data);
  	$this->assign('order',$order);
  	$this->assign('title','订单详情');
  	return $this->fetch('');
  }
 	

  }