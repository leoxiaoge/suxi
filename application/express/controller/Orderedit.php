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
use think\Cache;
class Orderedit extends ExpressBase{
  public function _initialize(){
    parent::_initialize();
  }

 	  /**
     * [address 收货信息修改]
     * @Author   WuSong
     * @DateTime 2017-10-24T14:59:36+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function address(){
      $order_id = input('order_id');
      //订单数据
      $order = $this->getbase->getone('order',['where'=>['id'=>$order_id]]);

       //转码手机号
      $phone = decode($order['user_phone']);
      //渲染模板
      $this->assign('phone',$phone);
      $this->assign('order',$order);
      $this->assign('title','修改收货信息');
      return $this->fetch('');
    }
    /**
     * [make 订单信息确认]
     * @Author   WuSong
     * @DateTime 2017-10-24T14:59:21+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function make(){
      $order_id = input('order_id');
      //订单数据
      $order = $this->getbase->getone('order',['where'=>['id'=>$order_id]]);

     $good_id =trim($order['good_id'],',');
      $good_num =$order['good_num'];
      // show($good_id);
      //历史商品ID 数量
      $good_info= [];
      $goods_id =  explode(',',trim($order['good_id'],','));
      $goods_num = explode(',',trim($order['good_num'],','));

      foreach ($goods_id as $k =>$v) {
             
              $good_info[$v]= $goods_num[$k];
      }

      //查找订单商品ID对应的商品数据
      $goods_info =  $this->getbase->getall('goods',['where'=>"id in($good_id)",'field'=>'id,name,price,picture,catid']);
        foreach ($goods_info as $ke => $ve) {
          $ve['good_id'] =$goods_id[$k];
          $ve['picture']= get_file_path($ve['picture']);
          $ve['num']  = $good_info[$ve['id']];
          $ve['prices']= $ve['num']*$ve['price'];
          $ve['express_id'] = $id;
          $data[]=$ve;
        }
        // show($data);
        // die;
      foreach ($data as $k => $v) {
       
            $goods_price[] = $v['prices'];
          }
        // show($goods_price);
      $goods_price_all =array_sum($goods_price);
      //总价
      $goods_price = array_sum($goods_price_all);
      //转码手机号
      $phone = decode($order['user_phone']);
      $order['give_time'] = trim($order['give_time'])?$order['give_time']:date('Y-m-d H:i',strtotime("+5 hour"));
      $give_time = $order['give_time'];
      //渲染模板
      $this->assign('give_time',$give_time);
      $this->assign('time',$time);
      $this->assign('phone',$phone);
      $this->assign('data',$data);
      $this->assign('order',$order);
      $this->assign('goods_price_all',$goods_price_all);
      $this->assign('title','确认订单');
      return $this->fetch('');
    }

    /**
     * [product 修改商品信息]
     * @Author   WuSong
     * @DateTime 2017-10-24T14:59:54+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function product(){
      ##商品标签
      $goods_cat_tag = $this->getbase->getall('goods_cat_tag',['where'=>'status = 1']);

     $this->assign('goods_cat_tag',$goods_cat_tag); 
     ##分类
     
      $goodscat = [];

       foreach ($goods_cat_tag as $k => $v) {
        $goodscat['tag_id_'.$v['id']][] = model('common/base')->getall('goods_cat',['where'=>'status = 1 and tagid = '.$v['id']]);
       }


     $this->assign('goodscat',$goodscat);
   
     ##商品
     //所有分类
       $cats = model('common/base')->getall('goods_cat',['where'=>'status=1']);
       $goods = [];
       foreach ($cats as $k => $v) {
         $goods['cat_id_'.$v['id']][] = model('common/base')->getall('goods',['where'=>'status=1 and catid = '.$v['id']]);
       }
    $this->assign('goods',$goods);

    //## 订单数据
    
    $id = cookie('express_id');
    $order_id =(int)input('order_id');
    if($this->getbase->getcount('express_order_users',['where'=>['express_id'=>$id,'order_id'=>$order_id]])>0){
      ##订单数据
      $order = $this->getbase->getone('order',['where'=>['id'=>$order_id],'field'=>'id,good_id,good_num,user_name,user_phone,order_number,user_address,remarks,good_price,order_price,take_time,give_time']);
    }else{
      $this->error('你不能修改此订单');
    }  
  
    if(!empty($order['good_id'])){
    //## 简单处理订单ID，数量
    $good_id =trim($order['good_id'],',');
    $good_num =$order['good_num'];
    //历史商品ID 数量
    $good_info= [];
    $goods_id = explode(',', trim($order['good_id'],','));
    $goods_num = explode(',',trim($order['good_num'],','));

    foreach ($goods_id as $k =>$v) {
           
            $good_info[$v]= $goods_num[$k];
    }
    //渲染
    $this->assign('goods_id',$goods_id);
    $this->assign('good_info',$good_info);

    //查找订单商品ID对应的商品数据
    $goods_info =  $this->getbase->getall('goods',['where'=>"id in($good_id)",'field'=>'id,name,price,picture,catid'],'LEFT');
      foreach ($goods_info as $ke => $ve) {
        $ve['good_id'] =$goods_id[$k];
        $ve['picture']= get_file_path($ve['picture']);
        $ve['num']  = $good_info[$ve['id']];
        $ve['prices']= $ve['num']*$ve['price'];
        $ve['express_id'] = $id;
        $data[]=$ve;
      }
    //统计商品总数量
    $goods_num_all =  array_sum(explode(',',trim($good_num,',')));
    $this->assign('goods_num_all',$goods_num_all);

    //商品总价格
    foreach ($data as $k => $v) {
      $goods_price[] = $v['prices'];
    }
    $goods_price_all =array_sum($goods_price);
    $this->assign('goods_price_all',$goods_price_all);
    }

    //渲染模板
    $user_phone =decode($order['user_phone']);
    $this->assign('user_phone',$user_phone);
    $this->assign('data',$data);
    $this->assign('order',$order);
    $this->assign('修改订单',$title);
    return $this->fetch('');
    }


  }