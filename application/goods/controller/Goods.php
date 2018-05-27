<?php

 namespace app\goods\controller;
 use app\common\controller\AdminBase;
class Goods extends AdminBase{

	public function _initialize()
	{
		parent::_initialize();
	}
    /**
     * [cat 商品列表]
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
        $order = $order?$order:"sort desc";
        $data = $this->getbase->getdb('goods')
                              ->order($order)
                              ->where($map)
                              ->paginate();
        $catinfo = $this->getbase->getone('goods_cat',['where'=>['id'=>input('catid')]]);
        // 分页数据
        $page = $data->render();
        return $this->builder('table')
        ->setPageTitle('['.$catinfo['title'].']商品列表')
        ->setSearch(['id' => 'id', 'name' => '商品名称']) // 设置搜索参数
        ->setTableName('goods')
        ->setPrimaryKey('id')
        ->addOrder('id,sort')
        ->addColumn('id', 'id')
        ->addColumn('name', '商品名称','text.edit')
        ->addColumn('price', '单价','text.edit')
        ->addColumn('unit', '单位（默认"件"）','text.edit')
        ->addColumn('create_time', '创建时间')
        ->addColumn('picture', '商品图','picture')
        ->addColumn('sort', '排序', 'number')
        ->addColumn('status', '状态', 'switch')
        ->addColumn('right_button', '操作', 'btn')
        ->addTopButton('cailist',['class'=>"btn btn-default",'title'=>'返回分类','href'=>url('goods/cat/index'),'icon'=>'fa fa-mail-reply-all']) // 添加顶部按钮

        ->addTopButton('edit',['class'=>"btn btn-default",'title'=>'发布商品','href'=>url('goods/goods/edit',['catid'=>input('catid')])]) // 添加顶部按钮
        ->addRightButtons(['edit'=>['href'=>url('goods/goods/edit',['catid'=>input('catid'),'id'=>'__id__'])],'delete' => ['data-tips' => '删除后无法恢复。','field'=>'id']
            ,'attr' => ['href'=>url('goods/goods/attr',['catid'=>input('catid'),'goods_id'=>'__id__']),'title'=>'商品属性','icon'=>'fa fa-outdent','class'=>'btn btn-default btn-xs']
            ]) 
        ->setRowList($data) // 设置表格数据
        ->setPages($page) // 设置分页数据
        ->fetch();
    }
    /**
     * [attr 添加商口属性]
     * @Author   Jerry
     * @DateTime 2017-09-28T09:36:56+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function attr(){
       $data = $this->getbase->getdb('goods_attr')
                              ->order("sort desc,id desc")
                              ->where("goods_id = ".input('goods_id'))
                              ->paginate();
        $goodsinfo = $this->getbase->getone('goods',['where'=>['id'=>input('goods_id')],'field'=>'name','order'=>'sort desc']);
        // 分页数据
        $page = $data->render();
        return $this->builder('table')
        ->setPageTitle('['.$goodsinfo['name'].']商品属性')
        ->setSearch(['id' => '属性id', 'name' => '属性']) // 设置搜索参数
        ->setTableName('goods_attr')
        ->setPrimaryKey('id')
        ->addOrder('id')
        ->addColumn('id', 'id')
        ->addColumn('name', '属性名','text.edit')
        ->addColumn('value', '属性值','')
        ->addColumn('sort', '排序','text.edit')
        ->addColumn('right_button', '操作', 'btn')
         ->addRightButtons(
            [
            'edit'=>['href'=>url('goods/goods/editattr',['catid'=>input('catid'),'goods_id'=>input('goods_id'),'id'=>'__id__'])],
            'delete' => ['data-tips' => '删除后无法恢复。','field'=>'id']
            ]) 
        ->addTopButton('cailist',['class'=>"btn btn-default",'title'=>'返回商品','href'=>url('goods/goods/index',['catid'=>input('catid')]),'icon'=>'fa fa-mail-reply-all']) // 添加顶部按钮
        ->addTopButton('edit',['class'=>"btn btn-default",'title'=>'添加新属性','href'=>url('goods/goods/editattr',['goods_id'=>input('goods_id'),'catid'=>input('catid')])]) // 
        ->setRowList($data) // 设置表格数据
        ->setPages($page) // 设置分页数据
        ->fetch(); 
    }
    /**
     * [editattr 修改属性]
     * @Author   Jerry
     * @DateTime 2017-09-28T09:45:09+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function editattr(){
        if($id = input('id')){
             $info = $this->getbase->getdb('goods_attr')->where("id = '{$id}'")->find();
             // show($info);
             extract($info);
         }
          return $this->builder('form')
            ->setUrl(url('systems/ajax/tmkedit'))
            ->addHidden('field','id')
            ->addHidden('id',$id)
            ->addHidden('gourl',url('goods/goods/attr',['catid'=>input('catid'),'goods_id'=>input('goods_id')]))
            ->addHidden('goods_id',input('goods_id'))
            ->addHidden('table','goods_attr')
            ->addHidden('catid',input('catid'))
            ->setPageTitle('添加属性')
            ->addText('name', '属性名', '',$name)
            ->addTextarea('value', '属性值', '',$value)
            ->addNumber('sort', '排序','',$sort)
            ->addRadio('status', '状态','',[0=>'隐藏',1=>'开启'],$status===0?0:1)
            ->fetch();    
    }
    /**
     * [editcat 编辑商品]
     * @Author   Jerry
     * @DateTime 2017-07-03T14:59:11+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function edit(){
         if($id = input('id')){
             $info = $this->getbase->getdb('goods')->where("id = '{$id}'")->find();
             extract($info);
         }
         //当前商品信息
         $catinfo = model('base')->getone('goods_cat',['where'=>['id'=>(int)input('catid')]]);
         // show($catinfo);
         // show(input('catid'));
      return $this->builder('form')
        ->setUrl(url('systems/ajax/tmkedit'))
        ->addHidden('field','id')
        ->addHidden('gourl',url('goods/goods/index',['catid'=>input('catid')]))
        ->addHidden('id',$id)
        ->addHidden('table','goods')
         // ->addBmap('map', '地图', 'Wyg6kzztmEN1qVGe0RtqGFvDUv9iX2A0', '广州市越秀区广州交易广场', '广州市越秀区广州交易广场', '广州市越秀区广州交易广场', 16)
        ->addHidden('catid',input('catid'))
        ->setPageTitle('发布商品')
        ->addText('name', '商品名', '',$name)
        ->addStatic('catname', '商品分类', '',$catinfo['title'])
        ->addNumber('price', '单价', '',$price)
        ->addText('unit', '单位', '',$unit)
        ->addImage('picture', '商品图', '',$picture)
        ->addTextarea('remark', '备注', '',$remark)
        // ->addSelect('catid','分类','',formatArr($cats,'id','title'),input('catid'))
        ->addNumber('sort', '排序','',$sort)
        ->addRadio('status', '状态','',[0=>'隐藏',1=>'开启'],$status===0?0:1)
        ->fetch();
        }

   
}