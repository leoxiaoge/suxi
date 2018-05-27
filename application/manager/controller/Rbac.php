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
class Rbac extends ManagerBase
{

public function _initialize()
{
 	parent::_initialize();  
 	
}
public function index(){
    $map = $this->getMap();
     $order = $this->getOrder();
    $re = model('Base')->getpages('store_department',['where'=>$map,'order'=>$order,'list_rows'=>$_GET['list_rows']]);
      // 分页数据
      $page = $re->render();
  return $this->builder('table')
          ->setTemplate(APP_PATH. 'manager/view/public/template_table.html')
          ->setTabNav($list_tab,  $group)
          ->setPageTitle('订单列表')
          ->setSearch(['id' => '订单id', 'order_number' => '订单号']) // 设置搜索参数
          ->setTableName('category')
          ->setPrimaryKey('id')
          ->setTableName('gy_student')
          ->setPrimaryKey('id')
          ->addOrder('id')
          ->addColumn('id', 'id')
          ->addColumn('name', '名称')
          ->addColumn('dec', '描述')
          ->addColumn('right_button', '操作', 'btn')
          ->addRightButtons([
                   'calendar' => ['icon'=>'fa fa-expeditedssl','title'=>'授权','class'=>'btn btn-xs btn-default frAlert','url'=>url('manager/rbac/empower',['department_id'=>"__id__"]),'href'=>'javascript:;'],
               ])
          // 批量添加右侧按钮
          ->setRowList($re) // 设置表格数据
          ->setPages($page) // 设置分页数据
          ->fetch(); 
}
/**
 * [empower 授权]
 * @Author   Jerry
 * @DateTime 2017-08-23T09:59:49+0800
 * @Example  eg:
 * @return   [type]                   [description]
 */
public function empower(){
    ##当前角色已获权限
    $auth = $this->getbase->getall('store_auth',['where'=>['store_department_id'=>(int)input('department_id')]]);
    $auths = [];
    foreach ($auth as $key => $v) {
        $auths[] = $v['url'];
    }
    $this->assign('authGroups',$auths);
    return $this->fetch();
}
  
 

    









}
