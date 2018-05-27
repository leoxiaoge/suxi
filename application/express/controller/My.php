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
use think\Db;
class My extends ExpressBase{
  public function _initialize(){
    parent::_initialize();
  }
  /**
   * [index 账户首页]
   * @Author   WuSong
   * @DateTime 2017-09-08T17:42:27+0800
   * @Example  eg:
   * @return   [type]                   [description]
   */
  public function index(){
  

    $id=(int)cookie('express_id');
    //查询账号相关银行卡信息
    $my_info=DB::table(config('database.prefix').'users_express')
                ->alias('ue')
                ->join(config('database.prefix').'users_express_bankcard ueb','ueb.express_id=ue.id','left')
                ->where('ue.id',$id)
                ->find();
     //截取中间数字并*替换
    if(strlen($my_info['cardid'])==16){
      $cardid=substr_replace($my_info['cardid'],'*********',4,8);
    }elseif (strlen($my_info['cardid'])==19) {
      $cardid=substr_replace($my_info['cardid'],'***********',4,11);
    }
    $my_info['money'] = $my_info['money']?$my_info['money']:'0.00';
    $this->assign('cardid',$cardid);
    $this->assign('my_info',$my_info);
    
    $this->assign('title','账户首页');
    return $this->fetch('');
  }
  
  /**
   * [myCard 我的银行卡]
   * @Author   WuSong
   * @DateTime 2017-09-08T10:58:02+0800
   * @Example  eg:
   * @return   [type]                   [description]
   */
  public function mycards(){
   $id=(int)cookie('express_id');
    //查询账号相关银行卡信息
    $my_info=DB::table(config('database.prefix').'users_express')
                ->alias('ue')
                ->join(config('database.prefix').'users_express_bankcard ueb','ueb.express_id=ue.id')
                ->where('ue.id',$id)
                ->find();
     //截取中间数字并*替换
    if(strlen($my_info['cardid'])==16){
      $cardid=substr_replace($my_info['cardid'],'*********',4,8);
    }elseif (strlen($my_info['cardid'])==19) {
      $cardid=substr_replace($my_info['cardid'],'***********',4,11);
    }
    $this->assign('cardid',$cardid);
    $this->assign('my_info',$my_info);
    
    $this->assign('title','我的银行卡');
    return $this->fetch('');
  }
  /**
   * [relname 实名认证]
   * @Author   Jerry
   * @DateTime 2017-09-09T10:07:27+0800
   * @Example  eg:
   * @return   [type]                   [description]
   */
  public function relname(){
      $this->assign('title','实名认证');
      $realinfo = $this->getbase->getone('users_express',['where'=>['id'=>cookie('express_id')]]);
      if($realinfo['real_status']==1){
        $this->error('您的实名认证正在审核中...',url('express/index/index'));
      }else{
        $this->assign($realinfo);
      }
      return $this->fetch('');
  }
  /**
   * [cash_detail 帐务明细]
   * @Author   Jerry
   * @DateTime 2017-09-08T15:50:16+0800
   * @Example  eg:
   * @return   [type]                   [description]
   */
  public function cash_detail(){
 
   $type = input('type');
   $type = $type?$type:"spread";

    switch ($type) {
      case 'spread':
        ##分成记录
         $spread_log = $this->getbase->getall('order_spread_log',['where'=>['express_id'=>cookie('express_id')]]);
         $this->assign('spread_log',$spread_log);
        break;
      case 'withdraw':
        ##提现记录
         $withdraw = $this->getbase->getall('express_withdraw_cash',['where'=>['express_id'=>cookie('express_id')]]);
         // show($withdraw);
         $this->assign('withdraw',$withdraw);
        break;
     
    }
    $this->assign('select',$type);
    return $this->fetch();
  }
  /**
   * [addCard 添加银行卡]
   * @Author   WuSong
   * @DateTime 2017-09-08T10:57:45+0800
   * @Example  eg:
   */
  public function addcards(){
    
    $id=cookie('express_id');
    $bank_card =  $this->getbase->getone('users_express',['where'=>['id'=>$id]]);
    $this->assign('bank_card',$bank_card);
    $this->assign('title','添加银行卡');
  	return $this->fetch('');
  }

  /**
   * [card_info 解除绑定]
   * @Author   WuSong
   * @DateTime 2017-09-08T17:56:10+0800
   * @Example  eg:
   * @return   [type]                   [description]
   */
  public function card_info(){

   $id=(int)cookie('express_id');
    //查询账号相关银行卡信息
    $my_info=DB::table(config('database.prefix').'users_express')
                ->alias('ue')
                ->join(config('database.prefix').'users_express_bankcard ueb','ueb.express_id=ue.id')
                ->where('ue.id',$id)
                ->find();
     //截取中间数字并*替换
    if(strlen($my_info['cardid'])==16){
      $cardid=substr_replace($my_info['cardid'],'*********',4,8);
    }elseif (strlen($my_info['cardid'])==19) {
      $cardid=substr_replace($my_info['cardid'],'***********',4,11);
    }
    $this->assign('cardid',$cardid);
    $this->assign('my_info',$my_info);
    $this->assign('title','解除绑定');
   return $this->fetch('');
  }
}