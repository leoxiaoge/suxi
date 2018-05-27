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
class Month extends Base{
  public function _initialize(){
    parent::_initialize();
  }
  /**
   * [index 订单量查询]
   * @Author   WuSong
   * @DateTime 2017-09-29T15:13:49+0800
   * @Example  eg:
   * @return   [type]                   [description]
   */
  public function index(){

    $id = cookie('express_id');
    //取件查询
    $take_info= $this->getbase->getcount('express_order_users',['where'=>['express_id'=>$id,'type'=>1,'status'=>4]]);
    //送件查询
    $give_info= $this->getbase->getcount('express_order_users',['where'=>['express_id'=>$id,'type'=>2,'status'=>4]]);
    //异常单量
    $abnormal_info= $this->getbase->getcount('express_order_users_log',['where'=>['express_id'=>$id,'log'=>'物流员拒绝接单','change_status'=>1]]);
    //完成单量
    $over_info= $this->getbase->getcount('express_order_users',['where'=>['express_id'=>$id,'status'=>4]]);


    //取件数量
    $this->assign('take_info',$take_info);
    //送件数量
    $this->assign('give_info',$give_info);
    //异常单量
    $this->assign('abnormal_info',$abnormal_info);
    //完成单量
    $this->assign('over_info',$over_info);
    //标题
  	$this->assign('title','订单');
  	return $this->fetch('');
  }
 	

  }