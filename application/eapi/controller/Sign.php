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
class Sign extends PublicBase
{

    public function _initialize()
    {
        parent::_initialize();

    }

    /**
     * [sign 签到]
     * @Author   WuSong
     * @DateTime 2017-10-17T14:04:13+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    // public function sign()
    // { 
    //     $data = input();
    //     $id = (int)input('post.uid');
    //     $users =  $this->getbase->getone('users_ux',['where'=>['id'=>$id],'field'=>'name,integral,sign_day,continuity_sign_day']);
    //     //当前用户的签到记录
        
    //     //判断是否已签到
    //     if($this->getbase->getone('user_sign_log',['where'=>['sign_time'=>date('Y-m-d'),'u_id'=>$id,'order'=>'id desc']])){
    //          return returnJson(0,'您已签到','');
    //     }else{

    //              //用户当前积分
    //         $integral  = $users['integral'];
    //         //用户累计签到天数
    //         $sign =$users['continuity_sign_day'];
    //         if($sign==1){
    //             $integral = $integral+1;
    //         }else if($sign==2){
    //             $integral = $integral+2;
    //         }else if($sign==3){
    //             $integral = $integral+3;
    //         }else if($sign==4){
    //             $integral = $integral+4;
    //         }else if($sign==5){
    //             $integral = $integral+5;
    //         }else if($sign==6){
    //             $integral = $integral+6;
    //         }else if($sign>6){
    //             $integral = $integral+7;
    //         }

    //         $log = [
    //            'u_id'=>$id,
    //            'sign_time'=>date('Y-m-d'),
    //            'sign_day'=>$users['sign_day']+1,
    //            'users_name'=>$users['name'],
    //            'integral'=>$integral,

    //         ];
    //         $this->getbase->getadd('user_sign_log',$data))
    //         $this->getbase->getedit('users',['where'=>['uid'=>$uid]],['sign'=>$data['sign_day'],'integral'=>$users['integral']+1]);
           
            
        
    //         $log = [
    //             'integral' => +1,
    //             'remarks'  =>'每日签到积分+1',
    //             'create_time' =>date('Y-m-d H:i:s'),
    //             'u_id' =>$uid,
    //             'status' => 0
    //         ];
    //         $this->getbase->getadd('users_integral_log',$log);
    //             return returnJson(0,'签到成功,累计签到天数'.$sign,'');

    //         }else{
    //             return returnJson(1,'系统忙，稍后再试','');
    //         }

        // }
      // 
        
        

      
     


    


   
   






}
