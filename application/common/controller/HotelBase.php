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
namespace app\common\controller;
use app\common\controller\Base;
class HotelBase extends Base
{
  public function _initialize() {
  	parent::_initialize();
    if((int)cookie('hotel_id')<1){
    	$this->redirect('hotel/ucenter/login','请先登陆后操作');
    }else{
    	##查出用户信息
    	$userinfo = $this->getbase->getone('hotel',['where'=>['id'=>cookie('hotel_id')]]);
    	unset($userinfo['password']);
    	// $userinfo['name'] = $userinfo['name']?$userinfo['name']:decode($userinfo['phone']);
    	$userinfo['head'] = $userinfo['head']?$userinfo['head']:'/public/static/images/default_avatar/avatar-mid-img.png';
    	$this->assign('userinfo',$userinfo);
    }
  }

}
