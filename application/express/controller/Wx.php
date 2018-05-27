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
use think\DB;
class Wx extends Base{
  public function _initialize(){
    parent::_initialize();
  }
  /**
   * [index 宿洗小哥验证]
   * @Author   Jerry
   * @DateTime 2017-09-20T09:45:50+0800
   * @Example  eg:
   * @return   [type]                   [description]
   */
  public function index(){
    include_once(EXTEND_PATH.'wechat.class.php');
      $options = [
        'token'=>config('sxxg_token'), //填写你设定的key
        'encodingaeskey'=>config('sxxg_encodingaeskey'), //填写加密用的EncodingAESKey
        'appid'=>config('sxxg_appid'), //填写高级调用功能的app id
        'appsecret'=>config('sxxg_appsecret') //填写高级调用功能的密钥

      ];
      $wx = new \Wechat($options);
      $wx->valid();
  }


  }