<?php
/*
+--------------------------------------------------------------------------
|  /门店管理员
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
use app\common\controller\Base;
use think\Cache;
use think\Db;
use think\helper\Hash;

class User extends Base
{

	public function _initialize()
    {
     	parent::_initialize();  
     	
    }
    /**
     * [login 登陆]
     * @Author   Jerry
     * @DateTime 2017-08-24T09:44:32+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function login(){
        return $this->fetch();
    }
    /**
     * [logout 退出]
     * @Author   Jerry
     * @DateTime 2017-08-24T09:44:38+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function logout(){
    	 session('suid',null);
           session('suinfo',null);
    	$this->success('成功退出');
    }



    
}
