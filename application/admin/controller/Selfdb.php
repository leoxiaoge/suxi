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
class Selfdb extends AdminBase
{
 /**
  * [index 用户评论数据管理]
  * @return [type] [description]
  */
  public function comment(){
    $map = $this->getMap();
    $order = $this->getOrder();
    $re = model('Base')->getpages('selfdb_comment',['where'=>$map,
                                              // 'alias'=>'u',
                                              // 'leftjoin'=>[['users_group ug','u.group_id=ug.group_id']],
                                              'order'=>$order,
                                              'list_rows'=>$_GET['list_rows']
                                              ]);
    return $this->builder('table')
    ->setPageTitle('用户列表')
    ->setSearch(['id' => '用户id', 'name' => '姓名']) // 设置搜索参数
    ->setTableName('selfdb_comment')
    ->setPrimaryKey('id')
    ->addOrder('id')
    ->addColumn('id', 'id')
    ->addColumn('name', '姓名')
    ->addColumn('time', '时间')
    ->addColumn('img', '图片')
    ->addColumn('comment', '评论')
    ->addColumn('phone', '手机号')
    ->addColumn('right_button', '操作', 'btn')
    ->addTopButton('edit',['class'=>"btn btn-default",'icon'=>'fa fa-plus','title'=>'添加评价数据','href'=>"/admin/selfdb/editcomment"]) // 添加顶部按钮
    ->addRightButtons(['edit'=>['href'=>url('admin/selfdb/editcomment',['id'=>'__id__'])], 'delete' => ['data-tips' => '删除后无法恢复。','field'=>'id']]) // 批量添加右侧按钮
    ->setRowList($data_list) // 设置表格数据
    ->setRowList($re) // 设置表格数据
    ->fetch();
  }
 
  /**
   * [edit 后台管理员编辑管理]
   * @Author   Jerry
   * @DateTime 2017-06-14T11:03:57+0800
   * @Example  eg:
   * @return   [type]                   [description]
   */
  public function editcomment(){
    $id = (int)input('id');
    if($id>0){
      if($re = model('Base')->getone('selfdb_comment',['where'=>['id'=>$id]])){
        extract($re);
      }
    }
    return $this->builder('form')
    ->setUrl(url('systems/api/tmkedit'))
    ->addHidden('table','selfdb_comment')
    ->addHidden('gourl',url('admin/selfdb/comment'))
    ->addHidden('field','id')
    ->addHidden('id',$id)
    ->setPageTitle('添加评价数据')
    ->addText('name', '姓名','',$name)
    ->addDatetime('time', '时间','',$time)
    ->addImage('img', '图片','',$img)
    ->addTextarea('comment', '评论','',$comment)
    ->addText('phone', '手机号','',$phone)

    ->fetch(); 

  }






}
