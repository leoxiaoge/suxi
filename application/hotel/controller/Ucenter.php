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
namespace app\hotel\controller;
use app\common\controller\Base;
use think\DB;
class Ucenter extends Base{
    public function _initialize(){
      parent::_initialize();
    }

    public function login(){
      if(cookie('hotel_id')){
      $this->redirect(url('hotel/index/index'));
    }
      $this->assign('title','登陆');
      return $this->fetch('');
    }

    public function backpass(){

    
      $this->assign('title','密码找回');
      return $this->fetch('');
    }
    public function register(){
      $this->assign('title','注册');
      return $this->fetch('');
    }
}