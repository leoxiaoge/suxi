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
class User extends ExpressBase{
  public function _initialize(){
    parent::_initialize();
  }
  
  public function index(){
   
    $data=input();
    $id = cookie('express_id');
    $users_info= $this->getbase->getone('users_express',['where'=>['id'=>$id]]);
    $users_info['phone']=decode($users_info['phone']);
    $this->assign('users_info',$users_info);
    // if(!isset($users_info['name'])){
    //   $this->success('你还未认证，请认证后再进行操作','express/user/authtion');
    // }
    $this->assign('title','个人');
    return $this->fetch('');
  }

  /**
   * [modName 修改名称]
   * @Author   WuSong
   * @DateTime 2017-09-04T18:34:06+0800
   * @Example  eg:
   * @return   [type]                   [description]
   */
  public function modnames(){

    
    $id=(int)cookie('express_id');
    $modName = $this->getbase->getone('users_express',['where'=>['id'=>$id]]);
    $this->assign('modName',$modName);
    $this->assign('title','修改名称');
  	return $this->fetch('');
    
  }
  /**
   * [modPass 修改密码]
   * @Author   WuSong
   * @DateTime 2017-09-04T18:34:24+0800
   * @Example  eg:
   * @return   [type]                   [description]
   */
  public function modpasss(){
   
    $this->assign('title','修改密码');
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

    $this->assign('title','注册');
  	return $this->fetch('');
  }

  /**
   * [register 实名认证]
   * @Author   WuSong
   * @DateTime 2017-09-04T18:34:36+0800
   * @Example  eg:
   * @return   [type]                   [description]
   */

    public function authtion(){
      if(cookie('express_id') == NULL){
      $this->redirect(url('express/index/index'));
    }

      $this->assign('title','认证');

      return $this->fetch('');
     }
    
     
}
