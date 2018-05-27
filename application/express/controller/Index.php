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
use think\DB;
class Index extends ExpressBase{
  public function _initialize(){
    parent::_initialize();
  }

  public function index(){
     $id= cookie('express_id');
     $openid = $this->getbase->getone('users_express',['where'=>['id'=>cookie('express_id')],'field'=>'wx_openid']);
     // show($openid);
    if(empty($openid['wx_openid'])&&!session('wx_express_session')){
        session('wx_express_session','true');

       $this->redirect(url('express/index/auths'));

    }
    ##授权
    ##如果用户没有绑定微信，跳至授经页
  	$this->assign('title','首页');
    return $this->fetch('');
  }
  public function testmsg(){
    // 发送模板消息
    $this->wx();
    $openid = $this->getbase->getone('users_express',['where'=>['id'=>cookie('express_id')],'field'=>'wx_openid']);
    $data['touser'] = $openid['wx_openid'];
        $data['template_id'] = 'IYF7A6oaxMr249rXDKZ8AYS_SMaPSzWJusFFK6e2das';
        $data['url'] = "#";
        $data['topcolor'] = "#FF0000";
        $data['msgtype'] = "news";
        $data['data'] = array(
            'first'=>array(
              "value"=>"test","color"=>"#0000",  //参数颜色  
            ),
            'keyword1'=>array(
              "value"=>"test","color"=>"#0000",   //参数颜色  
            ),
            'keyword2'=>array(
              "value"=>"test","color"=>"#0000",   //参数颜色  
            ),
            'keyword3'=>array(
              "value"=>"test","color"=>"#0000",   //参数颜色  
            ),
            'remark'=>array(
              "value"=>"test","color"=>"#0000",   //参数颜色  
            ),
          );
        // show($this);
   $re = $this->wx->sendTemplateMessage($data);
  }

  /**
   * [auth 微信授权]
   * @Author   Jerry
   * @DateTime 2017-09-20T09:47:59+0800
   * @Example  eg:
   * @return   [type]                   [description]
   */
  public function auths(){
    $this->wx();
    // show($this->wx->getOauthRedirect("http://demo.thinkask.cn/express/index/callback",time(),'snsapi_base'));
    $this->redirect($this->wx->getOauthRedirect("https://www.qiaolibeilang.com/express/index/callback",'','snsapi_base'));
  }
  /**
   * [callback 授权回调]
   * @Author   Jerry
   * @DateTime 2017-09-20T10:27:50+0800
   * @Example  eg:
   * @return   function                 [description]
   */
  public function callback(){
    $this->wx();
    $token = $this->wx->getOauthAccessToken();
    $this->getbase->getedit('users_express',['where'=>['id'=>cookie('express_id')]],['wx_openid'=>$token['openid'],'wx_c_time'=>date('Y-m-d H:i:s')]);
    $this->redirect(url('express/index/index'));
  }


  }