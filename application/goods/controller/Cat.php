<?php

 namespace app\goods\controller;
 use app\common\controller\AdminBase;
class Cat extends AdminBase{

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
        $data = $this->getbase->getdb('goods_cat')
                              ->order($order)
                              ->where($map)
                              ->paginate();
        $re  = [];
        foreach ($data as $k => $v) {
            $re[$k] = $v;
            $re[$k]['countGoods'] = '共'.$this->getbase->getcount('goods',['where'=>['catid'=>$v['id']]]).'个商品';
            $taginfo = $this->getbase->getone('goods_cat_tag',['where'=>['id'=>$v['tagid']],'field'=>'title']);
            $re[$k]['tagname'] = $taginfo['title'];
        }
        // 分页数据
        $page = $data->render();
        return $this->builder('table')
        ->setPageTitle('分类列表')
        ->setSearch(['id' => 'id', 'title' => '分类标题']) // 设置搜索参数
        ->setTableName('goods_cat')
        ->setPrimaryKey('id')
        ->addOrder('id,sort')
        ->addColumn('id', 'id')
        ->addColumn('title', '分类标题')
        ->addColumn('tagname', '所属标签')
        ->addColumn('countGoods', '商品数量')
        ->addColumn('create_time', '创建时间')
        ->addColumn('sort', '排序','number')
        ->addColumn('status', '状态','switch')
        ->addColumn('right_button', '操作', 'btn')

        ->addTopButton('edit',['class'=>"btn btn-default",'title'=>'新建分类','href'=>url('goods/cat/edit')]) // 添加顶部按钮
        ->addRightButton('list', ['icon'=>'fa fa-list-ul','title'=>'商品列表','class'=>'btn btn-default btn-xs ','href'=>url('goods/goods/index',['catid'=>'__id__'])])
        ->addRightButton('edit')
        ->setRowList($re) // 设置表格数据
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
    $nvinfo = $this->getbase->getdb('goods_cat')->where("id = '{$id}'")->find();
        extract($nvinfo);
      }
      return $this->builder('form')
        ->setUrl(url('systems/ajax/tmkedit'))
        ->addHidden('field','id')
        ->addHidden('gourl',url('goods/cat/index'))
        ->addHidden('id',$id)
        ->addHidden('table','goods_cat')
        ->setPageTitle('编辑分类')
        ->addText('title', '标题', '',$title)
        ->addSelect('tagid','标签','',formatArr($this->getbase->getall('goods_cat_tag',['field'=>'id,title']),'id','title'),$tagid)
        ->addNumber('sort', '排序','',$sort)
        ->addRadio('status', '状态','',[0=>'隐藏',1=>'开启'],$status===0?0:1)
        ->fetch(); 
        }

   
}