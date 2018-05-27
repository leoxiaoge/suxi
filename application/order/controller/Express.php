<?php
##运费管理
 namespace app\order\controller;
 use app\common\controller\AdminBase;
class Express extends AdminBase{

	public function _initialize()
	{
		parent::_initialize();
	}
    /**
     * [index 运费梯形价格表]
     * @Author   Jerry
     * @DateTime 2017-08-07T14:54:00+0800
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
        $data = $this->getbase->getdb('order_express')
                              ->order($order)
                              ->where($map)
                              ->paginate();

        // 分页数据
        $page = $data->render();
        return $this->builder('table')
        ->setPageTitle('运费梯形价格表')
        ->setSearch(['id' => 'id', 'name' => '商品名称']) // 设置搜索参数
        ->setTableName('order_express')
        ->setPrimaryKey('id')
        // ->addOrder('id,sort')
        ->addColumn('id', 'id')
        ->addColumn('status', '状态','switch')
        ->addColumn('amount_reached', '达到金额','text.edit')
        ->addColumn('express_fee', '运费金额','text.edit')
        ->addColumn('remark', '备注信息','text.edit')
        ->addColumn('right_button', '操作', 'btn')
         ->addRightButtons(['edit'=>['href'=>url('order/express/edit',['id'=>'__id__'])], 'delete' => ['data-tips' => '删除后无法恢复。','field'=>'id']]) 
        ->addTopButton('edit',['class'=>"btn btn-default",'icon'=>'fa fa-plus','title'=>'发布运费价','href'=>url('order/express/edit')]) // 
        ->setRowList($data) // 设置表格数据
        ->setPages($page) // 设置分页数据
        ->fetch();
    }
   /**
    * [edit 编辑运费]
    * @Author   Jerry
    * @DateTime 2017-08-07T15:44:06+0800
    * @Example  eg:
    * @return   [type]                   [description]
    */
    public function edit(){
         if($id = input('id')){
             $info = $this->getbase->getdb('order_express')->where("id = '{$id}'")->find();
             extract($info);
         }
         //当前商品信息

      return $this->builder('form')
        ->setUrl(url('systems/ajax/tmkedit'))
        ->addHidden('field','id')
        ->addHidden('gourl',url('order/express/index'))
        ->addHidden('id',$id)
        ->addHidden('table','order_express')
        ->setPageTitle('发布运费价')
        ->addNumber('amount_reached', '达到金额', 'eg:达到15,运费金额10元',$amount_reached)
        ->addNumber('express_fee', '运费金额', '',$express_fee)
        ->addTextarea('remark', '备注信息', '',$remark)
        ->addRadio('status', '状态','',[0=>'禁用',1=>'开启'],$status===0?0:1)
        ->fetch(); 
        }

   
}