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
namespace app\admin\controller;
use app\common\controller\AdminBase;
use think\Db;
use think\Cache;
class Hotel extends AdminBase {
/**
 * [lists 酒店列表]
 * @Author   Jerry
 * @DateTime 2017-09-06T09:41:39+0800
 * @Example  eg:
 * @return   [type]                   [description]
 */
public function lists(){
	  $map = $this->getMap();
      $order = $this->getOrder();

      $re = model('Base')->getpages('hotel',['where'=>$map,'order'=>$order,'list_rows'=>$_GET['list_rows']]);
      return $this->builder('table')
      ->setPageTitle('酒店列表')
      ->setSearch(['id' => 'id', 'hotel_name' => '酒店名']) // 设置搜索参数
      ->setTableName('hotel')
      ->setPrimaryKey('id')
      ->addOrder('id')
      ->addColumn('id', 'id')
      ->addColumn('hotel_name', '酒店名','text.edit')
      // ->addColumn('phone', '手机号','登陆用')
      // ->addColumn('password', '密码','登陆用')
     
      ->addColumn('province', '省','text.edit')
      ->addColumn('city', '市','text.edit')
      ->addColumn('area', '区','text.edit')
      ->addColumn('address', '地址','text.edit')
      ->addColumn('status', '状态',"switch")
      ->addColumn('right_button', '操作', 'btn')
      ->addTopButton('edit',['class'=>"btn btn-default",'title'=>'添加酒店','icon'=>'fa fa-plus','href'=>url('admin/hotel/edit')]) // 添加顶部按钮
      ->addRightButtons(['edit', 'delete' => ['data-tips' => '删除后无法恢复。','field'=>'id']]) // 批量添加右侧按钮
      ->setRowList($re) // 设置表格数据
      ->fetch();
}
/**
 * [edit 新加酒店]
 * @Author   Jerry
 * @DateTime 2017-09-06T09:41:50+0800
 * @Example  eg:
 * @return   [type]                   [description]
 */
public function edit(){
	$id = (int)input('id');
	if($id>0){
		$info = $this->getbase->getone('hotel',['where'=>['id'=>$id]]);
		extract($info);
	}
     return $this->builder('form')
      ->setUrl(url('admin/api/edithotel'))
      ->setPageTitle('添加酒店')
      ->addText('hotel_name', '酒店名','',$name)
      ->addText('phone', '手机号','登陆用',decode($phone))
      ->addText('password', '密码','登陆用',decode($password))
      ->addText('province', '省','',$province)
      ->addText('city', '市','',$city)
      ->addText('area', '区','',$area)
      ->addText('address', '地址','',$address)
	  ->addRadio('status', '状态','',[0=>'禁用',1=>'开启'],empty($status)?1:$status)
      // ->addNumber('sort', '排序')
      ->fetch(); 
}
	
	
}
