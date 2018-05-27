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
namespace app\hotel\controller;
use app\common\controller\HotelBase;
use think\DB;
class User extends HotelBase{
    public function _initialize(){
      parent::_initialize();
    }
    /**
     * [index 个人信息首页]
     * @Author   WuSong
     * @DateTime 2017-09-12T16:06:14+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function index(){
       if(!cookie('hotel_id')){
      $this->redirect(url('hotel/ucenter/login'));
    } 
      $id=cookie('hotel_id');
      //查询个人信息
      $hotel_info=$this->getbase->getone('hotel',['where'=>['id'=>$id]]);
      if($hotel_info['cash_status'] !=2){
        $this->redirect(url('hotel/index/index'));
      }

      //数组组合成字符串
      $arr= [$hotel_info['province'],$hotel_info['city'],$hotel_info['area'],$hotel_info['address']];

      $atr = implode('', $arr);
      $this->assign('atr',$atr);
      $this->assign('hotel_info',$hotel_info);
      $this->assign('title','个人信息');
      return $this->fetch('');
    }
    /**
     * [authen 认证]
     * @Author   WuSong
     * @DateTime 2017-09-11T17:54:16+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function authen(){
      if(!cookie('hotel_id')){
      $this->redirect(url('hotel/ucenter/login'));
    }

    // if($hotel_info['cash_status'] !=2){
    //     $this->redirect(url('hotel/index/index'));
    //   }

      $id= cookie('hotel_id');

      $hotel=$this->getbase->getone('hotel',['where'=>['id'=>$id]],['field'=>['cash_status'=>1]]);
      if($hotel['cash_status']==1){
        return $this->error('正在审核中请耐心等待');
      }
      //查询对应ID信息
      $authen_info = DB::table(config('database.prefix').'hotel')
                    ->alias('h')
                    ->join(config('database.prefix').'hotel_authen ha','h.id=ha.hotel_id','LEFT')
                    ->field('h.*,h.phone hp,h.status hs,ha.*')
                    ->where('h.id',$id)
                    ->find();


      // $a = $this->getbase->getone('hotel',['where'=>['id'=>$id]]);
      // ##重组
      $arr= [$authen_info['province'],$authen_info['city'],$authen_info['area']];
      $atr = implode('', $arr);
      $phone = decode($authen_info['phone']);
      $names = ($authen_info['name']);
      $id_card= decode($authen_info['id_card']);

      // ##渲染
      $this->assign('atr',$atr);
      $this->assign('names',$names);
      $this->assign('phone',$phone);
      $this->assign('id_card',$id_card);
      $this->assign('atr',$atr);
      $this->assign('authen_info',$authen_info);
      $this->assign('id',$id);
      $this->assign('title','信息认证');
      return $this->fetch('');
    }

    /**
     * [forpass 修改密码]
     * @Author   WuSong
     * @DateTime 2017-09-18T14:09:38+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function forpass(){

      $this->assign('title','修改密码');
      return $this->fetch('');
    }
}