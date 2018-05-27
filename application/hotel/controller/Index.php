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
class Index extends HotelBase{
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
      
    $id = cookie('hotel_id');
    if(!$this->getbase->getone('hotel',['where'=>['id'=>$id]])){
      cookie('hotel_id',null);
    }
    $hotel_info =Db::table(config('database.prefix').'hotel')
    			->alias('h')
    			->join(config('database.prefix').'hotel_authen ha','h.id = ha.hotel_id','LEFT')
                ->field('h.*,h.phone hp,ha.*')
                ->where('h.id',$id)
    			->find();
    $phone = decode($hotel_info['phone']);
    $this->assign('phone',$phone);
    $hotel_info['money'] = $hotel_info['money']?$hotel_info['money']:'0.00';
    $this->assign('hotel_info',$hotel_info);

    ##当前月分成日志
    $mouth = input('mouth')?input('mouth'):date('m');
    $this->assign('choose_mouth',$mouth);
    $math_date = date('Y').(strlen($mouth)>1?$mouth:'0'.$mouth);
    $this->assign('count_spreat_hotel',$this->getbase->getcount('order_hotel_spread_log',['where'=>['math_date'=>$math_date,'hotel_id'=>$id]]));
    $this->assign('sum_spreat_hotel',$this->getbase->getall('order_hotel_spread_log',['where'=>['math_date'=>$math_date,'hotel_id'=>$id],'field'=>'sum(money) summoney']));

    $this->assign('title','首页');
    
      return $this->fetch('');
    }

}