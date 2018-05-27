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
class ExpressBase extends Base
{
  protected $wx;
  protected $options;
  protected $access_token;
  public function _initialize() {
  	parent::_initialize();
    if((int)cookie('express_id')<1){
    	$this->redirect('express/ucenter/login','请先登陆后操作');
    }else{
    	##查出用户信息
    	$userinfo = $this->getbase->getone('users_express',['where'=>['id'=>cookie('express_id')]]);
    	unset($userinfo['password']);
    	$userinfo['name'] = $userinfo['name']?$userinfo['name']:decode($userinfo['phone']);
    	$userinfo['avatarurl'] = $userinfo['avatarurl']?$userinfo['avatarurl']:'/public/static/images/default_avatar/avatar-mid-img.png';
    	$this->assign('userinfo',$userinfo);
    }
  }
  protected function wx(){
    include_once(EXTEND_PATH.'wechat.class.php');
      $options = [
        'token'=>config('sxxg_token'), //填写你设定的key
        'encodingaeskey'=>config('sxxg_encodingaeskey'), //填写加密用的EncodingAESKey
        'appid'=>config('sxxg_appid'), //填写高级调用功能的app id
        'appsecret'=>config('sxxg_appsecret') //填写高级调用功能的密钥

      ];
      $this->wx = new \Wechat($options);
      show($this->wx);
  }

}
