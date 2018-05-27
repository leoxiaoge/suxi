<?php

 namespace app\goods\controller;
 use app\common\controller\AdminBase;
class Coupon extends AdminBase{

	public function _initialize()
	{
		parent::_initialize();
	}
    /**
     * [index 优惠券]
     * @Author   Jerry
     * @DateTime 2017-11-06T10:16:28+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function index(){
      //分类数据
        $map = $this->getMap();
        $map['status'] = 1;


        $order = $this->getOrder();
        $order = $order?$order:"sort desc";
        $data = $this->getbase->getdb('coupon')
                              ->order($order)
                              ->where($map)
                              ->paginate();
        // 分页数据
        $page = $data->render();

        $res = [];
        foreach ($data as $k => $v){

           $v['expire'] = $this->time_tran(strtotime($v['expire'].' 00:00:00'));
           $v['discount'] = $v['discount'].'元';
            $v['num'] = $v['num'].'张';
            $v['l_price'] = $v['l_price'].'元';
            $res[] = $v;
        }

        return $this->builder('table')
        ->setPageTitle('优惠券列表')
        ->setSearch(['id' => 'id', 'name' => '优惠金额']) // 设置搜索参数
        ->setTableName('coupon')
        ->setPrimaryKey('id')
        ->addOrder('id,sort,num')
        ->addColumn('id', 'id')
        ->addColumn('title', '标题描述','text.edit')
        ->addColumn('effective_days', '有效天数')
        ->addColumn('discount', '优惠金额')
        ->addColumn('expire', '到期时间')
        ->addColumn('num', '剩余数量')
        ->addColumn('type', '优惠类型', 'type', '', ['金额限制','推荐人/邀请人','新用户','自定义','门店赠送'])
        ->addColumn('l_price', '限制金额','l_price')
        ->addColumn('sort', '排序', 'number')
        ->addColumn('status', '状态', 'switch')
        ->addColumn('right_button', '操作', 'btn')

        ->addTopButton('edit',['class'=>"btn btn-default",'title'=>'添加优惠券','href'=>url('goods/coupon/edit',['conid'=>input('conid')])]) // 添加顶部按钮
        ->addRightButtons(['edit'=>['href'=>url('goods/coupon/edit',['conid'=>input('conid'),'id'=>'__id__'])],'delete' => ['data-tips' => '删除后无法恢复。','field'=>'id']])
        ->setRowList($res) // 设置表格数据
        ->setPages($page) // 设置分页数据
        ->fetch();
    }
    /**
     * [editcat 编辑优惠券]
     * @Author   Jerry
     * @DateTime 2017-07-03T14:59:11+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function edit(){
         if($id = input('id')){
             $info = $this->getbase->getdb('coupon')->where("id = '{$id}'")->find();
             extract($info);
             $time =0;
         }else{
             $time =time();
         }

      return $this->builder('form')
        ->setUrl(url('systems/ajax/tmkedit'))
        ->addHidden('field','id')
        ->addHidden('gourl',url('goods/coupon/index',['conid'=>input('id')]))
        ->addHidden('id',$id)
        ->addHidden('table','coupon')
        ->addHidden('a_id', $_COOKIE['thikask_admin_uid'])
        ->addHidden('id',input('id'))
        ->addHidden('u_time',time())
        ->addHidden('c_time',$time>0?$time:'')
        ->setPageTitle('发布优惠券')
        ->addText('title', '标题描述', '',$title)
        ->addRadio('type', '类型','',['金额限制','推荐人/邀请人','新用户','自定义','门店赠送'],$type===0?0:$type===1?1:2)
        ->addNumber('discount', '优惠金额', '',$discount)
        ->addNumber('num','优惠券数量','',$num)
        ->addDate('expire', '到期时间', '', $expire, 'yyyy-mm-dd')
        ->addNumber('l_price', '限制金额', '',$l_price)
        ->addNumber('effective_days', '有效天数', '当前时间+有效天数=结束时间',$effective_days)
        ->addNumber('sort', '排序','',$sort)
        ->addRadio('status', '状态','',[0=>'隐藏',1=>'开启'],$status===0?0:1)
        ->fetch(); 
        }


    /**
     * [index 时间差计算]
     * @Author   wb
     * @describe  eg:传入时间戳
     * @return 距离now的时间差
     */
    public function time_tran($the_time) {
        $now_time = date("Y-m-d H:i:s", time());
        //echo $now_time;
        $now_time = strtotime($now_time);
        $show_time = $the_time;
        $dur = $show_time - $now_time ;
        if ($dur < 0) {
            return '已过期';
        } else {
            if ($dur < 60) {
                return $dur . '秒后';
            } else {
                if ($dur < 3600) {
                    return floor($dur / 60) . '分钟后';
                } else {
                    if ($dur < 86400) {
                        return floor($dur / 3600) . '小时后';
                    } else {
                            return floor($dur / 86400) . '天后';
                    }
                }
            }
        }
    }

    //




}