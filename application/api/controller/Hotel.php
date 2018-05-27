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
use qcloudcos\Cosapi;
class Hotel extends PublicBase
{


	public function _initialize()
    {

     	parent::_initialize();  
    }
    /**
     * [hotel 根据地区查酒店]
     * @Author   Jerry
     * @DateTime 2017-09-06T10:41:23+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function search(){
         $province = addslashes(input('province'));
         $city = addslashes(input('city'));
         $hotel = $this->getbase->getall('hotel',['where'=>['province'=>$province,'city'=>$city],'field'=>'hotel_name name']);
         return returnJson(0,'success','',$hotel);
    }



}