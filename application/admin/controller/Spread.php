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
class Spread extends AdminBase {
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
      $re = model('Base')->getpages('spread',['where'=>$map,'order'=>$order,'list_rows'=>$_GET['list_rows']]);
      return $this->builder('table')
      ->setPageTitle('推广列表')
      ->setSearch(['id' => 'id', 'name' => '酒店名']) // 设置搜索参数
      ->setTableName('spread')
      ->setPrimaryKey('id')
      ->addOrder('id')
      ->addColumn('id', 'id')
      ->addColumn('title', '标题','text.edit')
      ->addColumn('tag', '标识','text.edit')
      ->addColumn('url', '推广地址','text.edit')
      ->addColumn('start_date', '开始时间','text.edit')
      ->addColumn('end_date', '结束时间','text.edit')
      ->addColumn('rule', '规则说明')
      ->addColumn('status', '状态',"switch")
      ->addColumn('right_button', '操作', 'btn')
      ->addTopButton('edit',['class'=>"btn btn-default",'title'=>'添加推广','icon'=>'fa fa-plus','href'=>url('admin/spread/edit')]) // 添加顶部按钮
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
		$info = $this->getbase->getone('spread',['where'=>['id'=>$id]]);
		extract($info);
	}
     return $this->builder('form')
      ->setUrl(url('systems/ajax/tmkedit'))
      ->addHidden('gourl',url('admin/spread/lists'))
      ->addHidden('field','id')
      ->addHidden('id',$id)
      ->addHidden('table','spread')
      ->setPageTitle('添加新推广')
      ->addText('title', '标题','',$title)
      ->addText('tag', '标识','',$tag)
      ->addText('url', '推广地址','二维码地址，全路径',$url)
      ->addDatetime('start_date', '推广地址','',$start_date)
      ->addDatetime('end_date', '推广地址','',$end_date)
      ->addTextarea('rule', '规则说明','',$rule)
      ->addTextarea('remark', '备注','',$remark)
	->addRadio('status', '状态','',[0=>'禁用',1=>'开启'],empty($status)?1:$status)
      ->fetch(); 
}

/**
 * [leaving 官网留言审核]
 * @Author   WuSong
 * @DateTime 2017-09-25T09:18:28+0800
 * @Example  eg:
 * @return   [type]                   [description]
 */
public function leaving(){
      $html =  $this->builder('table');
           //当前位置ID下面的广告例表
       $group = input('group')?input('group'):'news';
        $map = $this->getMap();
         $list_tab = [
            'news' => ['title' => '待处理', 'url' => url('admin/spread/leaving', ['group' => 'news'])],
            'finish' => ['title' => '已处理', 'url' => url('admin/spread/leaving', ['group' => 'finish'])],
            
           
        ];
      switch ($group) {

        case 'news':
          $map['status'] = 0;
         $html = $html->addTopButton('edit',['icon'=>'fa fa-check','class'=>"btn btn-default",'form'=>'tableForm','class'=>'tPost btn btn-default','title'=>'通过审核','url'=>url('admin/api/leaving_access'),'href'=>"javascript:;"]);
          break;
         case 'finish':
          $map['status'] = 1;
          break;
          

      }
      $order = $this->getOrder();
      $data = $this->getbase->getdb('index_message')
                            ->order($order)
                            ->where($map)
                            ->paginate();
      
      // 分页数据
      $page = $data->render();
      
      return $html->setPageTitle('广告列表')
      ->setSearch(['id' => 'id', 'name' => '姓名']) // 设置搜索参数
      ->setTableName('index_message')
      ->setPrimaryKey('id')
      ->setTabNav($list_tab,  $group)
      ->addOrder('id')
      ->addColumn('id', 'id')
      ->addColumn('name', '姓名')
      ->addColumn('phone', '手机号')
      ->addColumn('email', '邮箱')
      ->addColumn('message', '留言内容')
      ->setRowList($data) // 设置表格数据
      ->setPages($page) // 设置分页数据
      ->fetch();
      
    }
	
	
}
