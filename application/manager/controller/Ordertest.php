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
namespace app\manager\controller;
use app\common\controller\ManagerBase;
use think\Cache;
use think\Db;
use think\helper\Hash;
class Ordertest extends ManagerBase
{

	public function _initialize()
    {
     	parent::_initialize();  
     	
    }

    public function order_print(){
      $id =(int)input('orderid');

      ##订单信息
      $orderinfo = $this->getbase->getone('order',['where'=>['id'=>$id]]);
      $dispatch = $this->getbase->getall('order_pendant',['where'=>['order_number'=>$orderinfo['order_number']]]);
      ##对相同物品订单大于1的处理
      $ordersum = explode(',', trim($orderinfo['good_num'],','));

      $goodsids = explode(',', trim($orderinfo['good_id'],','));
      $order_ids= [];
      ##组ID
      foreach ( $ordersum as $k => $v) {
        if($v>1){
          for ($i=0; $i < $v; $i++) { 
            $order_ids[] = $goodsids[$k];
          }
        }else{
          $order_ids[] = $goodsids[$k];
        }
      }
      $goodsinfo = [];
      if(is_array($order_ids)){
        $goodsCount = explode(',', trim($orderinfo['good_num'],','));
        foreach ($order_ids as $k => $v) {
          $data =$this->getbase->getone('goods',['where'=>['id'=>$v],'field'=>'id,name,price']);
          $data['hang_number'] = $dispatch[$k]['hang_number'];
          $data['piece_id'] = $dispatch[$k]['piece_id'];
          $data['t_code'] = $goodsCount[$k];
          $goodsinfo[] = $data;
        }
      }

      $this->assign('orderinfo',$orderinfo);
      $this->assign('goodsinfo',$goodsinfo);

        return $this->fetch();

    }
     

     public function jatoolsPrinter(){
      $id = (int)input('orderid');
      $orderinfo = $this->getbase->getone('order',['where'=>['id'=>$id]]);
      $dispatch = $this->getbase->getall('order_pendant',['where'=>['order_number'=>$orderinfo['order_number']]]);
      ##对相同物品订单大于1的处理
      $ordersum = explode(',', trim($orderinfo['good_sum'],','));
      $goodsids = explode(',', trim($orderinfo['good_id'],','));
      $order_ids = [];
      ##组ID
      foreach ($ordersum as $k => $v) {
        if($v>1){
          for ($i=0; $i < $v; $i++) { 
            $order_ids[] = $goodsids[$k];
          }
        }else{
          $order_ids[] = $goodsids[$k];
        }
      }

      ##查出订单信息
      $goodsinfo = [];
      if(is_array($order_ids)){
        $goodsCount  = explode(',', trim($orderinfo['good_num'],','));
        foreach ($order_ids as $k => $v) {
          $data = $this->getbase->getone('goods',['where'=>['id'=>$v],'field'=>'id,name,price']);
            $data['hang_number']=$dispatch[$k]['hang_number'];
            $data['piece_id']=$dispatch[$k]['piece_id'];
            $data['t_code']=$dispatch[$k]['t_code'];
            $data['count']=$goodsCount[$k];
            $goodsinfo[] = $data;
        }
      }

       $this->assign('orderinfo',$orderinfo);
       $this->assign('goodsinfo',$goodsinfo);
       return $this->fetch();

     }

     public function order_detail(){
       $id = (int)input('orderid');
      ##订单信息
      $orderinfo = $this->getbase->getone('order',['where'=>['id'=>$id]]);
      $dispatch = $this->getbase->getall('order_pendant',['where'=>['order_number'=>$orderinfo['order_number']]]);
      ##对相同物品订单大于1的处理
      $ordersum = explode(",", trim($orderinfo['good_num'],','));
      $goodsids = explode(",", trim($orderinfo['good_id'],','));
      $order_ids = [];
      ##组ID
      foreach ($ordersum as $k => $v) {
        if($v>1){
            for ($i=0; $i < $v; $i++) { 
             $order_ids[] = $goodsids[$k];
            }
        }else{
          $order_ids[] = $goodsids[$k];
        }
      }
      ##查出订单信息
     
      $goodsinfo = [];
      if (is_array($order_ids)) {
        $goodsCount = explode(",", trim($orderinfo['good_num'],','));
        foreach ($order_ids as $k => $v) {
            $data = $this->getbase->getone('goods',['where'=>['id'=>$v],'field'=>'id,name,price']);
            $data['hang_number']=$dispatch[$k]['hang_number'];
            $data['piece_id']=$dispatch[$k]['piece_id'];
            $data['t_code']=$dispatch[$k]['t_code'];
            $data['count']=$goodsCount[$k];
            $goodsinfo[] = $data;
        }
          
      }
      $this->assign('orderinfo',$orderinfo);
      $this->assign('goodsinfo',$goodsinfo);
        return $this->fetch();
    }


    public function news_more(){

      $group = input('group')?input('group'):'check';
       $list_tab = [
            // 'appointment' => ['title' => '预约取衣', 'url' => url('manager/ordertest/news_more', ['group' => 'appointment'])],
            'check' => ['title' => '检查并清洗', 'url' => url('manager/ordertest/news_more', ['group' => 'check'])],
            
            'express' => ['title' => '清洗完成待配送', 'url' => url('manager/ordertest/news_more', ['group' => 'express'])],
            'finish' => ['title' => '配送到达', 'url' => url('manager/ordertest/news_more', ['group' => 'finish'])],
        ];

        $html =  $this->builder('table')
              ->setTemplate(APP_PATH. 'manager/view/public/template_table.html')
              ->setPageTitle('订单列表')
              ->addOrder('id')
              ->setTabNav($list_tab,  $group)
              ->addColumn('id', 'id')
              ->addColumn('order_number', '订单号')
              ->addColumn('user_name', '姓名')
              ->addColumn('user_phone', '电话号码')
              ->addColumn('good_name', '商品名(元)')
              ->addColumn('goods_counts', '商品数量')
              ->addColumn('good_price', '商品总价(元)')
              ->addColumn('order_price', '实付金额(元)')
              ->addColumn('user_address', '地址')
              ->addColumn('create_time', '下单时间')
              ->addColumn('right_button', '操作', 'btn');
        $map = $this->getMap();
        $order = $this->getOrder();

          switch ($group) {

           
            case 'check':##检查并清洗
             $map['status'] =60;
              $html = $html->setPageTips('所有已检查并正在清洗的订单','danger')
                       ->addTopButtons(['accept' => ['id' => 'accept','title'=>'检查并清洗完成','class'=>'btn  btn-warning tPost','icon'=>'ti-truck icon-lg','href'=>'javascript:;','url'=>url('manager/apitest/check'),'form'=>'tableForm']]) // 批量添加顶部按钮
              ->addRightButtons([
                       'refund' => ['icon'=>'fa fa-remove','title'=>'取消订单','class'=>'btn btn-xs btn-danger frAlert','url'=>url('manager/ordertest/refund',['id'=>"__id__"]),'href'=>'javascript:;'],
                      
                       'detail' => ['icon'=>'fa fa-newspaper-o','title'=>'单据信息','class'=>'btn btn-xs btn-default frAlert','url'=>url('manager/ordertest/order_detail',['orderid'=>"__id__"]),'href'=>'javascript:;'],
                   ]);
            break;
            

            case 'express':##洗完并配送
             $map['status'] =80;
              $html = $html->setPageTips('所有已完成清洗的订单','danger')
                      ->addTopButtons(['accept' => ['id' => 'accept','title'=>'开始配送','class'=>'btn  btn-success tPost','icon'=>'ti-truck icon-lg','href'=>'javascript:;','url'=>url('manager/apitest/dispatch'),'form'=>'tableForm']]) // 批量添加顶部按钮
                      ->addRightButtons([
                    'calendar' => ['icon'=>'fa fa-calendar','title'=>'时间线','class'=>'btn btn-xs btn-default frAlert','url'=>url('manager/ordertest/timeline',['orderid'=>"__id__"]),'href'=>'javascript:;'],
                    'print' => ['icon'=>'fa fa-print','title'=>'打印单据','target'=>'_blank','class'=>'btn btn-xs btn-default','href'=>url('manager/ordertest/order_print',['orderid'=>"__id__"]),'url'=>'javascript:;'],
                      'detail' => ['icon'=>'fa fa-newspaper-o','title'=>'','class'=>'btn btn-xs btn-default frAlert','url'=>url('manager/ordertest/order_detail',['orderid'=>"__id__"]),'href'=>'javascript:;'],
                      'detail' => ['icon'=>'fa fa-newspaper-o','title'=>'单据信息','class'=>'btn btn-xs btn-default frAlert','url'=>url('manager/ordertest/order_detail',['orderid'=>"__id__"]),'href'=>'javascript:;'],
                    ]);
                   $html = $html->setTemplate(APP_PATH. 'manager/view/public/template_order.html');
                ##所有配送员
              $this->assign('express',$this->getbase->getall('users_express',['where'=>['status'=>1,'real_status'=>2],'field'=>'id,phone,avatarurl,realname']));
            break;
            

            case 'finish':##完成订单
             $map['status'] =90;
              $html = $html->setPageTips('所有正在配送的订单','danger')
                      ->addTopButtons(['accept' => ['id' => 'accept','title'=>'订单完结','class'=>'btn  btn-success tPost','icon'=>'ti-truck icon-lg','href'=>'javascript:;','url'=>url('manager/apitest/finish'),'form'=>'tableForm']]) // 批量添加顶部按钮
                      ->addRightButtons([
                          'calendar' => ['icon'=>'fa fa-calendar','title'=>'时间线','class'=>'btn btn-xs btn-default frAlert','url'=>url('manager/ordertest/timeline',['orderid'=>"__id__"]),'href'=>'javascript:;'],
                           'print' => ['icon'=>'fa fa-print','title'=>'打印单据','target'=>'_blank','class'=>'btn btn-xs btn-default','href'=>url('manager/ordertest/order_print',['orderid'=>"__id__"]),'url'=>'javascript:;'],
                          'detail' => ['icon'=>'fa fa-newspaper-o','title'=>'单据信息','class'=>'btn btn-xs btn-default frAlert','url'=>url('manager/ordertest/order_detail',['orderid'=>"__id__"]),'href'=>'javascript:;'],

              ]);
            break;

             default:
              $map['status'] =60;
            break;
          }
          $re = model('Base')->getpages('order',['where'=>$map,'order'=>$order,'list_rows'=>$_GET['list_rows']]);
          $data = [];
          foreach ($re as  $v) {
            $v['user_phone'] = decode($v['user_phone']);
            $v['create_time'] = date('Y-m-d H:i:s',$v['create_time']); 
            $v['goods_counts'] = array_sum(explode(",", $v['good_num']));
            $v['status_Zh'] = "<span class='btn btn-sm btn-$class'>".config('order_status')[$v['status']]."</span>";
              $data[] = $v;
          }
          $page = $re->render();
       return $html->setRowList($data)->setPages($page)->fetch(); 
          
      
           
    }




 

}
