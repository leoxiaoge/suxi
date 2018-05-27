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
namespace app\api\controller;
use think\Db;
use think\Cache;
class index extends PublicBase
{

    public function _initialize()
    {
        parent::_initialize();

    }

    public function index()
    {
    	$uid= get('uid');

    	$tx = DB::table('user');


    	$tx = $_SESSION['pic']; //用户头像
    	$name = $_SESSION['name'];//用户名称
    	$this->assgin('tx',$tx);
    	$this->assgin('name',$name);
        return $this->fetch();

    }
   






}
