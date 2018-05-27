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
class ucenter extends Base{
  public function _initialize(){
    parent::_initialize();
  }
  
  /**
   * [login 登陆]
   * @Author   WuSong
   * @DateTime 2017-09-04T18:33:50+0800
   * @Example  eg:
   * @return   [type]                   [description]
   */
  public function login(){
    if(cookie('express_id')){
      $this->redirect(url('express/index/index'));
    }
    $this->assign('title','登陆');
    return $this->fetch('');
    
  }  

   /**
   * [modPass 密码找回]
   * @Author   WuSong
   * @DateTime 2017-09-04T18:34:24+0800
   * @Example  eg:
   * @return   [type]                   [description]
   */
  public function backpasss(){
    $this->assign('title','密码找回');
    return $this->fetch('');
  }
  
   /**
   * [register 用户注册]
   * @Author   WuSong
   * @DateTime 2017-09-04T18:34:36+0800
   * @Example  eg:
   * @return   [type]                   [description]
   */
  public function register(){


    return $this->fetch('');
  }

    
     
}
