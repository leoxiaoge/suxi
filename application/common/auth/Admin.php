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

namespace app\common\auth;
use think\Cache;
use think\Config;
use think\Loader;
use think\Request;
use think\Session;
use think\Cookie;
use think\Db;
use app\ucenter\model\Admin as AdminModel;
class Admin extends Auth
{
    public $log         = true;
    private $request;
    private $param;
    private $module;
    private $controller;
    private $action;
    private $field_user;
    private $field_uid;
    public function __construct()
    {
        $this->request      = Request::instance();
        $this->param        = $this->request->param();
        $this->module       = $this->request->module();
        $this->controller   = $this->request->controller();
        $this->action       = $this->request->action();
        // $this->field_user   =Config::get('session.prefix').Config::get('auth_user');
        // $this->field_uid    =$this->field_user.".".Config::get('auth_uid');

    }


    private function Auth_error($status,$msg='',$url='',$remark=[]){
        $data['status'] = $status;
        $data['msg']    = $msg;
        $data['url']    = $url;
        $data['remark'] = $remark;
        return $data;
    }

    /**
     * [creatAdmin 后台用户]
     * @Author   Jerry
     * @DateTime 2017-06-12T16:17:57+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function creatAdmin($userinfo){
        Cookie::set('admin_uid',$userinfo[Config::get('auth_uid')]);
        return $userinfo[Config::get('auth_uid')];
    }
    /**
     * [delAdmin 删除当前的后台用户（退出登陆）]
     * @Author   Jerry
     * @DateTime 2017-06-12T17:01:04+0800
     * @Example  eg:
     * @param    [type]                   $uid [description]
     * @return   [type]                        [description]
     */
    public function delAdmin(){
         Cookie::set('admin_uid',null);
         return true;
    }
    /**
     * [getUser 获得后台的用户数据信息]
     * @Author   Jerry
     * @DateTime 2017-06-12T16:33:17+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function getAdminInfo(){
        if(Cookie::get('admin_uid')){
            $userinfo = AdminModel::f_uid_to_info(Cookie::get('admin_uid'));
            unset($userinfo['password']);
        }else{
           $userinfo = "";
        }
        return $userinfo;
    }





}