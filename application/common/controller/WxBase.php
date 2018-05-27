<?php
/*
+--------------------------------------------------------------------------
|  
|   ========================================
+---------------------------------------------------------------------------
 */
namespace app\common\controller;
use app\common\controller\Base;
class WxBase extends Base
{
  protected $wx;
  protected $options;
  public function _initialize() {
    parent::_initialize();
    include_once(EXTEND_PATH.'wechat.class.php');
    $this->options = [
      'token'=>config('sxxg_token'), //填写你设定的key
      'encodingaeskey'=>config('sxxg_encodingaeskey'), //填写加密用的EncodingAESKey
      'appid'=>config('sxxg_appid'), //填写高级调用功能的app id
      'appsecret'=>config('sxxg_AppSecret') //填写高级调用功能的密钥

    ];
    $this->wx = new \Wechat($this->options);
  }

 

}
