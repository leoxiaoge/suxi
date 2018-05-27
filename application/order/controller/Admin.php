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

namespace app\order\controller;
use app\common\controller\Base;
use think\Cache;
use think\Db;
// use think\helper\Hash;
class Admin extends Base
{
  public function lists(){
    //分类数据
        $map = $this->getMap();
        $order = $this->getOrder();
        $data = $this->getbase->getdb('order')
                              ->order($order)
                              ->where($map)
                              ->paginate();
        foreach ($data as $k => $v) {
              $v['status'] =     config('order_status')[$v['status']];
        }
        
        // show($data);
        // 分页数据
        $page = $data->render();
        return $this->builder('table')
        ->setPageTitle('订单列表')
        ->setSearch(['id' => 'id', 'order_number' => '订单号']) // 设置搜索参数
        ->setTableName('order')
        ->setPrimaryKey('id')
        ->addOrder('id')
        ->addColumn('id', 'id')
        ->addColumn('order_number', '订单号')
        ->addColumn('user_name', '用户名')
        ->addColumn('user_address', '地址')
        ->addColumn('good_name', '下单商品名称')
        ->addColumn('good_price', '商品总价(RMB)')
        ->addColumn('status','当前状态')
        // ->addColumn('sort', '排序', 'number')
        // ->addColumn('status', '状态', 'switch')
        // ->addColumn('right_button', '其它信息', 'btn')
        ->addRightButtons([
                    'calendar' => ['icon'=>'fa fa-calendar','title'=>'时间线','class'=>'btn btn-xs btn-default frAlert','url'=>url('order/admin/timeline',['orderid'=>"__id__"]),'href'=>'javascript:;'],
                    'detail' => ['icon'=>'fa fa-newspaper-o','title'=>'物流线','class'=>'btn btn-xs btn-default frAlert','url'=>url('order/admin/order_express',['orderid'=>"__id__"]),'href'=>'javascript:;'],
                    ])
        ->addColumn('right_button', '操作', 'btn')
        ->addTopButton('cailist',['class'=>"btn btn-default",'title'=>'返回分类','href'=>url('goods/cat/index'),'icon'=>'fa fa-mail-reply-all']) // 添加顶部按钮
        ->addTopButton('edit',['class'=>"btn btn-default",'title'=>'发布商品','href'=>url('goods/goods/edit',['catid'=>input('catid')])]) // 添加顶部按钮
        // ->addRightButtons(['edit'=>['href'=>url('goods/goods/edit',['catid'=>input('catid'),'id'=>'__id__'])],'delete' => ['data-tips' => '删除后无法恢复。','field'=>'id']])
        ->setRowList($data) // 设置表格数据
        ->setPages($page) // 设置分页数据
        ->fetch();
  }
  /**
   * [timeLine 时间线]
   * @Author   WuSong
   * @DateTime 2017-09-25T11:57:29+0800
   * @Example  eg:
   * @return   [type]                   [description]
   */
  public function timeLine(){
        $map = $this->getMap();
        $orderid = (int)input('orderid');
          $re = model('Base')->getall('order_info',['where'=>['order_id'=>$orderid,],'order'=>'id desc']);
          $data = [];
          foreach ($re as  $v) {
            $v['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
            // $v['remark'] = decode($v['remark']);
            $data[] = $v;
          }

      return $this->builder('table')
              // ->setTemplate(APP_PATH. 'manager/view/public/template_table.html')
              ->hideCheckbox()
              ->addColumn('id', 'id')
              ->addColumn('create_time', '发生时间')
              ->addColumn('remark', '发生内容')
              ->addColumn('order_number', '订单号')
              ->setRowList($data) // 设置表格数据
              ->fetch(); 
    }

    /**
     * [order_express 物流线]
     * @Author   WuSong
     * @DateTime 2017-09-25T11:57:43+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function order_express(){
        $map = $this->getMap();
        $orderid = (int)input('orderid');
        $re = DB::table(config('database.prefix').'express_order_users_log')
            ->alias('oi')
            ->join(config('database.prefix').'users_express ue','oi.express_id=ue.id','LEFT')
            ->field('oi.*,oi.create_time create_time,ue.realname')
            ->where('oi.order_id',$orderid)
            ->order('oi.id','desc')
            ->select();

          $data = [];
          foreach ($re as  $v) {
                $data[] = $v;
          }

      return $this->builder('table')
              // ->setTemplate(APP_PATH. 'manager/view/public/template_table.html')
              ->hideCheckbox()
              ->addColumn('id', 'id')
              ->addColumn('create_time', '发生时间')
              // ->addColumn('remark', '发生内容')
              ->addColumn('log', '物流日志')
              ->addColumn('realname', '配送员')
              ->setRowList($data) // 设置表格数据
              ->fetch(); 
    }

}
