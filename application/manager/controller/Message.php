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
use app\common\controller\ManagerBase;
use think\Cache;
use think\Db;
use think\helper\Hash;
class Message extends ManagerBase
{

	public function _initialize()
    {
     	parent::_initialize();  
     	
    }
    /**
     * [lists 消息列表]
     * @Author   Jerry
     * @DateTime 2017-08-04T17:47:55+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
  public function lists(){
     $map = $this->getMap();
      $order = $this->getOrder();
      $_GET['list_rows'] = $_GET['list_rows']?$_GET['list_rows']:10;
      $this->assign('noti',model('base')->getpages('notification',['join'=>[[config('database.prefix').'notification_data nod','no.notification_id=nod.notification_id']],'alias'=>'no','field'=>'no.*,nod.data','order'=>'id desc']));
    return $this->fetch();
  }

  




}
