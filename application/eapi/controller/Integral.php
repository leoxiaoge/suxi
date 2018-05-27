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
namespace app\eapi\controller;
use think\Db;
use think\Cache;
class Integral extends PublicBase
{

    public function _initialize()
    {
        parent::_initialize();

    }
    /**
     * [integral 余额积分查询]
     * @Author   WuSong
     * @DateTime 2017-10-19T15:31:20+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    // public function integral(){

    //     $uid = input('uid');

    //     $sign =  $this->getbase->getall('users_integral_log',['where'=>['u_id'=>$uid]]);

    //     return returnJson(0,'success','',$data);
    // }

}
