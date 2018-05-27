<?php

 namespace app\goods\controller;
 use app\common\controller\AdminBase;
class Recharge extends AdminBase{

	public function _initialize()
	{
		parent::_initialize();
	}
    /**
     * [index 用户充值赠送金额]
     * @Author   WuSong
     * @DateTime 2017-11-14T11:02:34+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function index(){

        $map = $this->getMap();
        if(input('id')){
          $map['id'] = input('id');
        }
        $order = $this->getOrder();
        $data = $this->getbase->getdb('recharge_money')
                              ->order($order)
                              ->where($map)
                              ->paginate();

        return $this->builder('table')
        ->setPageTitle('数据列表')
        ->setTableName('recharge_money')
        ->setPrimaryKey('id')
        ->addOrder('id,money')
        ->addColumn('id', 'id')
        ->addColumn('money', '充值金额')
        ->addColumn('give_money', '赠送金额')
        ->addColumn('create_time', '创建时间')
        ->addColumn('remarks','备注')
        ->addColumn('status', '状态','switch')
        ->addColumn('right_button', '操作', 'btn')
        ->addTopButton('edit',['class'=>"btn btn-default",'title'=>'新建配置','href'=>url('goods/recharge/edit')]) // 添加顶部按钮
        ->addRightButton('edit')
        ->setRowList($data) // 设置表格数据
        ->fetch();
    }

    /**
     * [edit 新建配置]
     * @Author   WuSong
     * @DateTime 2017-11-14T11:08:49+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function edit(){
         if($id = input('id')){
        $nvinfo = $this->getbase->getdb('recharge_money')->where("id = '{$id}'")->find();
            extract($nvinfo);
          }
          return $this->builder('form')
            ->setUrl(url('systems/ajax/tmkedit'))
            ->addHidden('field','id')
            ->addHidden('gourl',url('goods/recharge/index'))
            ->addHidden('id',$id)
            ->addHidden('table','recharge_money')
            ->setPageTitle('编辑分类')
            ->addText('money', '金额', '',$money)
            ->addText('give_money', '赠送金额', '',$give_money)
            ->addText('remarks','备注','',$remarks)
            ->addRadio('status', '状态','',[0=>'隐藏',1=>'开启'],$status===0?0:1)
            ->fetch(); 
            }

   
}