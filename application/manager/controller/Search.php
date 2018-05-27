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
class Search extends ManagerBase
{

	public function _initialize()
    {
     	parent::_initialize();  
     	
    }
    public function index(){
         $map = $this->getMap();
         $order = $this->getOrder();
         $status = config('order_status');
         if($map){
           $re = model('Base')->getpages('order',['where'=>$map,'order'=>$order,'list_rows'=>$_GET['list_rows']]);
            if($re){
                  $data = [];
                  foreach ($re as  $v) {
                  $v['user_phone'] = decode($v['user_phone']);
                  $v['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
                  $v['status_Zh'] = "";
                  $v['status_order']  =$status[$v['status']];
                  //订单状态
                 
                  ##查询商品
                  if(trim($v['good_id'])){
                    $numArr = explode(",", trim($v['good_num'],','));
                    foreach (explode(",", trim($v['good_id'],',')) as $ki => $vi) {
                      $v['num'][$vi] = $numArr[$ki];
                    }
                    $v['goods'] = $this->getbase->getall('goods',['where'=>"id in(".trim($v['good_id'],',').")",'field'=>'id,name,price']);
                  }else{
                    $v['goods'] = [];
                  }
                  $data[] = $v;
                }
                 // 分页数据
              $page = $re->render();
            }
         
         }

          $express  = $this->getbase->getall('users_express',['where'=>['status'=>1,'real_status'=>2],'field'=>'id,phone,avatarurl,realname']);
          $this->assign('express',$express);
          
          return $this->builder('table')
          ->setTemplate(APP_PATH. 'manager/view/public/template_table_search.html')
          ->setPageTitle('分类列表')
          ->setSearch(['order_number' => '订单号']) // 设置搜索参数
          ->setTableName('category')
          ->setPrimaryKey('id')
          ->setTableName('gy_student')
          ->setPrimaryKey('id')
          ->addOrder('id')
          ->hideCheckbox()
          ->addColumn('order_number', '订单号')
          ->addColumn('user_name', '姓名')
          ->addColumn('user_phone', '电话号码')
          ->addColumn('good_name', '商品名')
          ->addColumn('goods_counts', '商品数量')
          ->addColumn('good_price', '商品总价(元)')
          ->addColumn('order_price', '实付金额(元)')
          ->addColumn('user_address', '地址')
          ->addColumn('create_time', '下单时间')
          ->addColumn('status_order', '订单状态')
          ->addColumn('right_button', '操作', 'btn')
          ->addRightButtons(['edit'=>['href'=>"/admin/adv/edit/postion_id/{$postion_id}/id/__id__/"], 'delete' => ['data-tips' => '删除后无法恢复。','field'=>'id']])
          ->setRowList($data) // 设置表格数据据
          ->setPages($page) // 设置分页数据
          ->fetch();
    }
 







}
