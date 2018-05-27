<?php

 namespace app\goods\controller;
 use app\common\controller\AdminBase;
class Tag extends AdminBase{

	public function _initialize()
	{
		parent::_initialize();
	}
    /**
     * [cat 商品分类列表]
     * @Author   Jerry
     * @DateTime 2017-07-03T14:52:50+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function index(){
      //分类数据
        $map = $this->getMap();
        if(input('catid')){
          $map['catid'] = input('catid');
        }
        $order = $this->getOrder();
        $data = $this->getbase->getdb('goods_cat_tag')
                              ->order($order)
                              ->where($map)
                              ->paginate();
        // 分页数据
        $page = $data->render();
        return $this->builder('table')
        ->setPageTitle('标签列表')
        ->setSearch(['id' => 'id', 'title' => '分类标题']) // 设置搜索参数
        ->setTableName('goods_cat_tag')
        ->setPrimaryKey('id')
        ->addOrder('id,sort')
        ->addColumn('id', 'id')
        ->addColumn('title', '标签标题')
        ->addColumn('create_time', '创建时间')
        ->addColumn('picture', '商品图','picture')
        ->addColumn('sort', '排序','number')
        ->addColumn('status', '状态','switch')
        ->addColumn('right_button', '操作', 'btn')
        ->addTopButton('edit',['class'=>"btn btn-default",'title'=>'新建标签','href'=>url('goods/tag/edit')]) // 添加顶部按钮
        ->addRightButton('edit')
        ->addRightButton('delete',['field'=>'id'])
        ->setRowList($data) // 设置表格数据
        ->setPages($page) // 设置分页数据
        ->fetch();
    }
    /**
     * [editcat 编辑分类]
     * @Author   Jerry
     * @DateTime 2017-07-03T14:59:11+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function edit(){
         if($id = input('id')){
    $nvinfo = $this->getbase->getdb('goods_cat_tag')->where("id = '{$id}'")->find();
        extract($nvinfo);
      }
      return $this->builder('form')
        ->setUrl(url('systems/ajax/tmkedit'))
        ->addHidden('field','id')
        ->addHidden('gourl',url('goods/tag/index'))
        ->addHidden('id',$id)
        ->addHidden('table','goods_cat_tag')
        ->setPageTitle('编辑分类')
        ->addText('title', '标题', '',$title)
        ->addNumber('sort', '排序','',$sort)
        ->addRadio('status', '状态','',[0=>'隐藏',1=>'开启'],$status===0?0:1)
        ->addImage('picture', '商品图', '',$picture)
        ->fetch(); 
        }

   
}