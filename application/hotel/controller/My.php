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
class My extends HotelBase{
    public function _initialize(){
      parent::_initialize();
    }
    /**
     * [index 首页]
     * @Author   WuSong
     * @DateTime 2017-09-12T16:10:34+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function index(){
        if(!cookie('hotel_id')){
      $this->redirect(url('hotel/ucenter/login'));
    }
    $id= cookie('hotel_id');

    //遍历查询
    $hotel_info = DB::table(config('database.prefix').'hotel')
                    ->alias('h')
                    ->join(config('database.prefix').'hotel_authen ha','h.id=ha.hotel_id','LEFT')
                    ->field('h.*,h.status ha,ha.*')
                    ->where('ha.hotel_id',$id)
                    ->find();
                    
    //将银行卡中间位数变为*
    if(strlen($hotel_info['bank_id'])==16){
      $bank_id=substr_replace($hotel_info['bank_id'],'*********',4,8);
    }elseif (strlen($hotel_info['bank_id'])==19) {
      $bank_id=substr_replace($hotel_info['bank_id'],'***********',4,11);
    }



    $cash = $this->getbase->getall('hotel_withdraw_cash',['where'=>['hotel_id'=>$id]]);
    foreach ($cash as $k => $v) {
        $cash_info[]=[
            'create_date' => date('Y-m-d',strtotime($v['create_date'])),
            'money' =>$v['money'],
            'status' =>$v['status'],
        ];
    }
    $this->assign('cash',$cash);
    $this->assign('cash_info',$cash_info);
    $this->assign('bank_id',$bank_id);
    $this->assign('hotel_info',$hotel_info);
    $this->assign('title','账户中心');
      return $this->fetch('');
    }
    /**
     * [mycard 我的银行卡]
     * @Author   WuSong
     * @DateTime 2017-09-13T15:31:23+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function mycard(){
    if(!cookie('hotel_id')){
      $this->redirect(url('hotel/ucenter/login'));
    }
    $id= cookie('hotel_id');
    //遍历查询
    $hotel_info = DB::table(config('database.prefix').'hotel')
                    ->alias('h')
                    ->join(config('database.prefix').'hotel_authen ha','h.id=ha.hotel_id','LEFT')
                    ->field('h.*,h.status ha,ha.*')
                    ->where('ha.hotel_id',$id)
                    ->find();
    //将银行卡中间位数变为*
    if(strlen($hotel_info['bank_id'])==16){
      $bank_id=substr_replace($hotel_info['bank_id'],'*********',4,8);
    }elseif (strlen($hotel_info['bank_id'])==19) {
      $bank_id=substr_replace($hotel_info['bank_id'],'***********',4,11);
    }
    $this->assign('bank_id',$bank_id);
    $this->assign('hotel_info',$hotel_info);
    $this->assign('title','我的银行卡');
      return $this->fetch('');
    }
    /**
     * [addcard 添加银行卡]
     * @Author   WuSong
     * @DateTime 2017-09-13T15:31:38+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function addcard(){
        if(!cookie('hotel_id')){
      $this->redirect(url('hotel/ucenter/login'));
    }
     $id= cookie('hotel_id');
    //遍历查询
    $hotel_info = DB::table(config('database.prefix').'hotel')
                    ->alias('h')
                    ->join(config('database.prefix').'hotel_authen ha','h.id=ha.hotel_id','LEFT')
                    ->field('h.*,h.status ha,ha.*')
                    ->where('ha.hotel_id',$id)
                    ->find();
    $this->assign('title','添加银行卡');
      return $this->fetch('');
    }

    /**
     * [delcard 删除银行卡]
     * @Author   WuSong
     * @DateTime 2017-09-13T15:31:50+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function delcard(){
        if(!cookie('hotel_id')){
      $this->redirect(url('hotel/ucenter/login'));
    }
     $id= cookie('hotel_id');
    
    //遍历查询
    $hotel_info = DB::table(config('database.prefix').'hotel')
                    ->alias('h')
                    ->join(config('database.prefix').'hotel_authen ha','h.id=ha.hotel_id','LEFT')
                    ->field('h.*,h.status ha,ha.*')
                    ->where('ha.hotel_id',$id)
                    ->find();
    //将银行卡中间位数变为*
    if(strlen($hotel_info['bank_id'])==16){
      $bank_id=substr_replace($hotel_info['bank_id'],'*********',4,8);
    }elseif (strlen($hotel_info['bank_id'])==19) {
      $bank_id=substr_replace($hotel_info['bank_id'],'***********',4,11);
    }
    $this->assign('bank_id',$bank_id);
    $this->assign('hotel_info',$hotel_info);
    $this->assign('title','解除绑定');
      return $this->fetch('');
    }

}