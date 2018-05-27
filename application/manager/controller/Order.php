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
namespace app\manager\controller;
use app\common\controller\ManagerBase;
use think\Cache;
use think\Db;
use think\helper\Hash;
class Order extends ManagerBase
{

	public function _initialize()
    {
     	parent::_initialize();  
     	
    }

    /**
     * [edit 门店备注]
     * @Author   Jerry
     * @DateTime 2017-08-22T17:39:35+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function store_remark(){
      // $id = (int)input('id');
      // return $this->builder('form')
      //         ->hideCheckbox()
      //         ->addColumn('id', 'id')
      //         ->addColumn('create_time', '发生时间')
      //         ->addColumn('remark', '发生内容')
      //         ->addColumn('order_number', '订单号')
      //         ->setRowList($data) // 设置表格数据
      //         ->fetch(); 
    }


    public function order_print(){
       $id = (int)input('orderid');
      ##订单信息
      $orderinfo = $this->getbase->getone('order',['where'=>['id'=>$id]]);
      $dispatch = $this->getbase->getall('order_pendant',['where'=>['order_number'=>$orderinfo['order_number']]]);
      ##对相同物品订单大于1的处理
      $ordersum = explode(",", trim($orderinfo['good_num'],','));
      $goodsids = explode(",", trim($orderinfo['good_id'],','));
      $order_ids = [];
      ##组ID
      foreach ($ordersum as $k => $v) {
        if($v>1){
            for ($i=0; $i < $v; $i++) { 
             $order_ids[] = $goodsids[$k];
            }
        }else{
          $order_ids[] = $goodsids[$k];
        }
      }
      ##查出订单信息
     
      $goodsinfo = [];
      if (is_array($order_ids)) {
        $goodsCount = explode(",", trim($orderinfo['good_num'],','));
          foreach ($order_ids as $k => $v) {
              $data = $this->getbase->getone('goods',['where'=>['id'=>$v],'field'=>'id,name,price']);
              $data['hang_number']=$dispatch[$k]['hang_number'];
              $data['piece_id']=$dispatch[$k]['piece_id'];
              $data['t_code']=$dispatch[$k]['t_code'];
              $data['count']=$goodsCount[$k];
              $goodsinfo[] = $data;
          }
          
      }
      $this->assign('orderinfo',$orderinfo);
      $this->assign('goodsinfo',$goodsinfo);
        return $this->fetch();
    }
    /**
     * [jatoolsPrinter 杰表打印]
     * @Author   Jerry
     * @DateTime 2017-10-13T14:55:47+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function jatoolsPrinter(){
        $id = (int)input('orderid');
      ##订单信息
      $orderinfo = $this->getbase->getone('order',['where'=>['id'=>$id]]);
      $dispatch = $this->getbase->getall('order_pendant',['where'=>['order_number'=>$orderinfo['order_number']]]);
      ##对相同物品订单大于1的处理
      $ordersum = explode(",", trim($orderinfo['good_num'],','));
      $goodsids = explode(",", trim($orderinfo['good_id'],','));
      $order_ids = [];
      ##组ID
      foreach ($ordersum as $k => $v) {
        if($v>1){
            for ($i=0; $i < $v; $i++) { 
             $order_ids[] = $goodsids[$k];
            }
        }else{
          $order_ids[] = $goodsids[$k];
        }
      }
      ##查出订单信息
      $goodsinfo = [];
      if (is_array($order_ids)) {
        $goodsCount = explode(",", trim($orderinfo['good_num'],','));
        foreach ($order_ids as $k => $v) {
            $data = $this->getbase->getone('goods',['where'=>['id'=>$v],'field'=>'id,name,price']);
            $data['hang_number']=$dispatch[$k]['hang_number'];
            $data['piece_id']=$dispatch[$k]['piece_id'];
            $data['t_code']=$dispatch[$k]['t_code'];
            $data['count']=$goodsCount[$k];
            $goodsinfo[] = $data;
        }
      }
      $this->assign('orderinfo',$orderinfo);
      $this->assign('goodsinfo',$goodsinfo);
        return $this->fetch();
    }
    /**
     * [order_detail description]
     * @Author   Jerry
     * @DateTime 2017-08-26T11:59:25+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function order_detail(){
       $id = (int)input('orderid');
      ##订单信息
      $orderinfo = $this->getbase->getone('order',['where'=>['id'=>$id]]);
      $dispatch = $this->getbase->getall('order_pendant',['where'=>['order_number'=>$orderinfo['order_number']]]);
      ##对相同物品订单大于1的处理
      $ordersum = explode(",", trim($orderinfo['good_num'],','));
      $goodsids = explode(",", trim($orderinfo['good_id'],','));
      $order_ids = [];
      ##组ID
      foreach ($ordersum as $k => $v) {
        if($v>1){
            for ($i=0; $i < $v; $i++) { 
             $order_ids[] = $goodsids[$k];
            }
        }else{
          $order_ids[] = $goodsids[$k];
        }
      }
      ##查出订单信息
     
      $goodsinfo = [];
      if (is_array($order_ids)) {
        $goodsCount = explode(",", trim($orderinfo['good_num'],','));
        foreach ($order_ids as $k => $v) {
            $data = $this->getbase->getone('goods',['where'=>['id'=>$v],'field'=>'id,name,price']);
            $data['hang_number']=$dispatch[$k]['hang_number'];
            $data['piece_id']=$dispatch[$k]['piece_id'];
            $data['t_code']=$dispatch[$k]['t_code'];
            $data['count']=$goodsCount[$k];
            $goodsinfo[] = $data;
        }
          
      }
      $this->assign('orderinfo',$orderinfo);
      $this->assign('goodsinfo',$goodsinfo);
        return $this->fetch();
    }



    /**
     * [new 新订单]
     * @Author   Jerry
     * @DateTime 2017-08-04T09:17:45+0800
     * @Example  eg:
     * @return   [type]      
     *              [description]
     */
    public function news_more(){

      $group = input('group')?input('group'):'news';
       $list_tab = [
            'news' => ['title' => '待收取', 'url' => url('manager/order/news_more', ['group' => 'news'])],
            'check' => ['title' => '待检查', 'url' => url('manager/order/news_more', ['group' => 'check'])],
            'doing' => ['title' => '待清洗','url' => url('manager/order/news_more', ['group' => 'doing'])],
            'washfinish' => ['title' => '待洗完', 'url' => url('manager/order/news_more', ['group' => 'washfinish'])],
            'express' => ['title' => '待配送', 'url' => url('manager/order/news_more', ['group' => 'express'])],
            'finish' => ['title' => '待完成', 'url' => url('manager/order/news_more', ['group' => 'finish'])],
        ];
        $html =  $this->builder('table')
              ->setTemplate(APP_PATH. 'manager/view/public/template_table.html')
              // ->setPageTips('所有用户支付的新订单单','warning')
              ->setPageTitle('订单列表')
              ->addOrder('id')
              ->setTabNav($list_tab,  $group)
              ->addColumn('id', 'id')
              ->addColumn('order_number', '订单号')
              ->addColumn('user_name', '姓名')
              ->addColumn('user_phone', '电话号码')
              ->addColumn('good_name', '商品名')
              ->addColumn('goods_counts', '商品数量')
              ->addColumn('good_price', '商品总价(元)')
              ->addColumn('order_price', '实付金额(元)')
              ->addColumn('user_address', '地址')
              ->addColumn('create_time', '下单时间')
              // ->addColumn('status_Zh', '状态')
              ->addColumn('right_button', '操作', 'btn');
        $map = $this->getMap();
        $order = $this->getOrder();

          switch ($group) {
            case 'news':##已支付完的订单
              $map['status'] =1;
              $html = $html->setPageTips('所有用户支付的新订单','danger')
                           ->addTopButtons(['accept' => ['id' => 'accept','title'=>'接单并取件','class'=>'btn  btn-info tPost','icon'=>'ti-truck icon-lg','href'=>'javascript:;','url'=>url('manager/api/accept'),'form'=>'tableForm']])->addRightButtons([
                       'calendar' => ['icon'=>'fa fa-remove','title'=>'取消订单','class'=>'btn btn-xs btn-default frAlert','url'=>url('manager/order/refund',['id'=>"__id__"]),'href'=>'javascript:;'],
                   ]);
              $html = $html->setTemplate(APP_PATH. 'manager/view/public/template_order.html');
                ##所有配送员
              $this->assign('express',$this->getbase->getall('users_express',['where'=>['status'=>1,'real_status'=>2],'field'=>'id,phone,avatarurl,realname']));
              break;
            
            case 'check':##待检查
             $map['status'] =4;
              $html = $html->setPageTips('所有配送员收取过来的订单','danger')
                       ->addTopButtons(['accept' => ['id' => 'accept','title'=>'检查完成','class'=>'btn  btn-warning tPost','icon'=>'ti-truck icon-lg','href'=>'javascript:;','url'=>url('manager/api/check'),'form'=>'tableForm']]) // 批量添加顶部按钮
              ->addRightButtons([
                       'refund' => ['icon'=>'fa fa-remove','title'=>'取消订单','class'=>'btn btn-xs btn-danger frAlert','url'=>url('manager/order/refund',['id'=>"__id__"]),'href'=>'javascript:;'],
                       // 'print' => ['icon'=>'fa fa-print','title'=>'打印单据','target'=>'_blank','class'=>'btn btn-xs btn-default','href'=>url('manager/order/order_print',['orderid'=>"__id__"]),'url'=>'javascript:;'],
                       'detail' => ['icon'=>'fa fa-newspaper-o','title'=>'单据信息','class'=>'btn btn-xs btn-default frAlert','url'=>url('manager/order/order_detail',['orderid'=>"__id__"]),'href'=>'javascript:;'],
                   ]);
            break;
            

            case 'doing':##待清洗
             $map['status'] =5;
              $html = $html->setPageTips('所有检查完成，待清洗的订单','danger')
                       ->addTopButtons(['accept' => ['id' => 'accept','title'=>'开始清洗','class'=>'btn  btn-success tPost','icon'=>'ti-truck icon-lg','href'=>'javascript:;','url'=>url('manager/api/doing'),'form'=>'tableForm']]) // 批量添加顶部按钮
                      ->addRightButtons([
                       'refund' => ['icon'=>'fa fa-remove','title'=>'取消订单','class'=>'btn btn-xs btn-danger frAlert','url'=>url('manager/order/refund',['id'=>"__id__"]),'href'=>'javascript:;'],
                       'print' => ['icon'=>'fa fa-print','title'=>'打印单据','target'=>'_blank','class'=>'btn btn-xs btn-default','href'=>url('manager/order/order_print',['orderid'=>"__id__"]),'url'=>'javascript:;'],
                       'detail' => ['icon'=>'fa fa-newspaper-o','title'=>'单据信息','class'=>'btn btn-xs btn-default frAlert','url'=>url('manager/order/order_detail',['orderid'=>"__id__"]),'href'=>'javascript:;'],
                   ]);
            break;
            
           case 'washfinish':##待洗完
             $map['status'] =6;
              $html = $html->setPageTips('所有正在清洗的订单','danger')
                      ->addTopButtons(['accept' => ['id' => 'accept','title'=>'完成清洗','class'=>'btn  btn-success tPost','icon'=>'ti-truck icon-lg','href'=>'javascript:;','url'=>url('manager/api/washfinish'),'form'=>'tableForm']]) // 批量添加顶部按钮
                      ->addRightButtons([
                    'calendar' => ['icon'=>'fa fa-calendar','title'=>'时间线','class'=>'btn btn-xs btn-default frAlert','url'=>url('manager/order/timeline',['orderid'=>"__id__"]),'href'=>'javascript:;'],
                    'print' => ['icon'=>'fa fa-print','title'=>'打印单据','target'=>'_blank','class'=>'btn btn-xs btn-default','href'=>url('manager/order/order_print',['orderid'=>"__id__"]),'url'=>'javascript:;'],
                      'detail' => ['icon'=>'fa fa-newspaper-o','title'=>'','class'=>'btn btn-xs btn-default frAlert','url'=>url('manager/order/order_detail',['orderid'=>"__id__"]),'href'=>'javascript:;'],
                      'print' => ['icon'=>'fa fa-print','title'=>'打印单据','target'=>'_blank','class'=>'btn btn-xs btn-default','href'=>url('manager/order/order_print',['orderid'=>"__id__"]),'url'=>'javascript:;'],
                      'detail' => ['icon'=>'fa fa-newspaper-o','title'=>'单据信息','class'=>'btn btn-xs btn-default frAlert','url'=>url('manager/order/order_detail',['orderid'=>"__id__"]),'href'=>'javascript:;'],
                    ]);
            break;

            case 'express':##待洗完
             $map['status'] =7;
              $html = $html->setPageTips('所有已完成清洗的订单','danger')
                      ->addTopButtons(['accept' => ['id' => 'accept','title'=>'配送','class'=>'btn  btn-success tPost','icon'=>'ti-truck icon-lg','href'=>'javascript:;','url'=>url('manager/api/dispatch'),'form'=>'tableForm']]) // 批量添加顶部按钮
                      ->addRightButtons([
                    'calendar' => ['icon'=>'fa fa-calendar','title'=>'时间线','class'=>'btn btn-xs btn-default frAlert','url'=>url('manager/order/timeline',['orderid'=>"__id__"]),'href'=>'javascript:;'],
                    'print' => ['icon'=>'fa fa-print','title'=>'打印单据','target'=>'_blank','class'=>'btn btn-xs btn-default','href'=>url('manager/order/order_print',['orderid'=>"__id__"]),'url'=>'javascript:;'],
                      'detail' => ['icon'=>'fa fa-newspaper-o','title'=>'','class'=>'btn btn-xs btn-default frAlert','url'=>url('manager/order/order_detail',['orderid'=>"__id__"]),'href'=>'javascript:;'],
                      'detail' => ['icon'=>'fa fa-newspaper-o','title'=>'单据信息','class'=>'btn btn-xs btn-default frAlert','url'=>url('manager/order/order_detail',['orderid'=>"__id__"]),'href'=>'javascript:;'],
                    ]);
                   $html = $html->setTemplate(APP_PATH. 'manager/view/public/template_order.html');
                ##所有配送员
              $this->assign('express',$this->getbase->getall('users_express',['where'=>['status'=>1,'real_status'=>2],'field'=>'id,phone,avatarurl,realname']));
            break;
            

            case 'finish':##完成订单
             $map['status'] =8;
              $html = $html->setPageTips('所有正在配送的订单','danger')
                      ->addTopButtons(['accept' => ['id' => 'accept','title'=>'订单完结','class'=>'btn  btn-success tPost','icon'=>'ti-truck icon-lg','href'=>'javascript:;','url'=>url('manager/api/finish'),'form'=>'tableForm']]) // 批量添加顶部按钮
                      ->addRightButtons([
                          'calendar' => ['icon'=>'fa fa-calendar','title'=>'时间线','class'=>'btn btn-xs btn-default frAlert','url'=>url('manager/order/timeline',['orderid'=>"__id__"]),'href'=>'javascript:;'],
                           'print' => ['icon'=>'fa fa-print','title'=>'打印单据','target'=>'_blank','class'=>'btn btn-xs btn-default','href'=>url('manager/order/order_print',['orderid'=>"__id__"]),'url'=>'javascript:;'],
                          'detail' => ['icon'=>'fa fa-newspaper-o','title'=>'单据信息','class'=>'btn btn-xs btn-default frAlert','url'=>url('manager/order/order_detail',['orderid'=>"__id__"]),'href'=>'javascript:;'],

              ]);
            break;

             default:
              $map['status'] =1;
            break;
          }
          $re = model('Base')->getpages('order',['where'=>$map,'order'=>$order,'list_rows'=>$_GET['list_rows']]);
          $data = [];
          foreach ($re as  $v) {
            $v['user_phone'] = decode($v['user_phone']);
            $v['create_time'] = date('Y-m-d H:i:s',$v['create_time']); 
            $v['goods_counts'] = array_sum(explode(",", $v['good_num']));
            $v['status_Zh'] = "<span class='btn btn-sm btn-$class'>".config('order_status')[$v['status']]."</span>";
              $data[] = $v;
          }
          $page = $re->render();
       return $html->setRowList($data)->setPages($page)->fetch(); 
          
      
           
    }
   
    /**
     * [timeLine 订单时间线]
     * @Author   Jerry
     * @DateTime 2017-08-07T10:59:17+0800
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
     * [index 所有订单]
     * @Author   Jerry
     * @DateTime 2017-08-04T09:17:53+0800
     * @Example  eg:
     * @return   [type]                   [description]
     *   0=>'下单成功未支付',
      *  1=>'下单成功已支付商家未接单',
      *  3=>'商家已接单',
      *  4=>'商家已通知快递员上门取件',
      *  5=>'快递员已取件',
      *  6=>'商家收到货品',
      *  7=>'商家完成订单',
      *  8=>'快递员已取件正在配送',
      *  9=>'已经送达',
      *  10=>'送达成功',
      *  11=>'送达失败', 
     */
    public function index()
    {
         $group = input('group')?input('group'):"all";
         $map = $this->getMap();
         $order = $this->getOrder();
         if($group&&$group!="all"){
          ##不同状态判断
          $map['status'] = (int)$group;
         }else{
          $map = 'status <> -1';
         }
          $re = model('Base')->getpages('order',['where'=>$map,'order'=>$order,'list_rows'=>$_GET['list_rows']]);
          $data = [];
          foreach ($re as  $v) {
            $v['user_phone'] = decode($v['user_phone']);
            $v['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
            if($v['status']==1){
                $class = "warning";##成功未接单
            }elseif($v['status'==11]){
                $class = "danger";
            }else{
                $class="default";
            }
            $v['status_Zh'] = "<span class='btn btn-sm btn-$class'>".config('order_status')[$v['status']]."</span>";
              $data[] = $v;
          }
          // 分页数据
          $page = $re->render();
          $list_tab = [
              'all' => ['title' => '所有订单', 'url' => url('manager/order/index')],
              '1' => ['title' => '支付完成', 'url' => url('manager/order/index', ['group' => '1'])],
              '4' => ['title' => '收件中', 'url' => url('manager/order/index', ['group' => '4'])],
              '6' => ['title' => '清洗中', 'url' => url('manager/order/index', ['group' => '6'])],
              '8' => ['title' => '派送中', 'url' => url('manager/order/index', ['group' => '8'])],
              '10' => ['title' => '完成派送', 'url' => url('manager/order/index', ['group' =>'10'])],
          ];



          return $this->builder('table')
          ->setTemplate(APP_PATH. 'manager/view/public/template_table.html')
          ->setTabNav($list_tab,  $group)
          ->setPageTitle('分类列表')
          ->setSearch(['id' => '订单id', 'order_number' => '订单号']) // 设置搜索参数
          ->setTableName('category')
          ->setPrimaryKey('id')
          ->setTableName('gy_student')
          ->setPrimaryKey('id')
          ->addOrder('id')
          ->addColumn('id', 'id')
              ->addColumn('order_number', '订单号')
              ->addColumn('user_name', '姓名')
              ->addColumn('user_phone', '电话号码')
              ->addColumn('good_name', '商品名')
              ->addColumn('goods_counts', '商品数量')
              ->addColumn('good_price', '商品总价(元)')
              ->addColumn('order_price', '实付金额(元)')
              ->addColumn('user_address', '地址')
              ->addColumn('create_time', '下单时间')
              ->addColumn('status_Zh', '状态')
              // ->addColumn('right_button', '操作', 'btn')
          ->setRowList($data) // 设置表格数据据
          ->setPages($page) // 设置分页数据
          ->fetch();
		
    }


  
    /**
     * [abnormal 订单异常]
     * @Author   Jerry
     * @DateTime 2017-08-25T15:21:43+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
   public function abnormal(){
      
      return $this->builder('form')
          ->setUrl(url('manager/api/refund_no'))
          ->addHidden('oid',intval(input('id')))
          ->setPageTitle('异常订单处理')
          ->addText('remarks', '异常原因')
          ->hideBtn('back')
          ->fetch();




  }

    /**
     * [refund 取消订单]
     * @Author   Jerry
     * @DateTime 2017-08-25T15:21:17+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function refund(){
        $list_tab = [
            'tab1' => ['title' => '提交', 'url' => url('manager/api/refund_no', ['group' => 'tab1'])],
            'tab2' => ['title' => '关闭', 'url' => url('manager/order/news', ['group' => 'tab2'])],
        ];
        return $this->builder('form')
            ->setUrl(url('manager/api/refund_no'))
            ->addHidden('oid',intval(input('id')))
            ->setPageTitle('取消订单')
            ->addText('remarks', '取消原因')
            ->setBtnTitle(['submit' => '提交', 'back' => '关闭'])
            // ->addRadio('register_seccode', '新用户注册显示验证码', '', ['Y' => '是', 'N' => '否'],getset('register_seccode'))
            ->fetch();
    }
    /**
     * [abnormity 异常订单处理（物流）]
     * @Author   WuSong
     * @DateTime 2017-09-27T19:04:02+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function abnormity(){

      $group = input('group')?input('group'):'news';
       $list_tab = [
            'news' => ['title' => '等待重新派件', 'url' => url('manager/order/news_more', ['group' => 'news'])],
        ];

      $re = $this->getbase->getall('order',['alias'=>'o','join'=>[['express_order_users eou','o.id=eou.order_id']],'where'=>'eou.status=-10 or eou.status=-70','field'=>'o.id,o.order_number,o.user_name,o.user_phone,o.good_name,o.good_price,o.order_price,o.user_address,o.create_time,o.good_num,eou.express_id,eou.remark']);

          foreach ($re as $k=> $v) {
 
            $re[$k]['good_num'] = array_sum(explode(',', trim($v['good_num'],',')));

          }
      
          $this->assign('express',$this->getbase->getall('users_express',['where'=>['status'=>1,'real_status'=>2],'field'=>'id,phone,avatarurl,realname']));

          return $this->builder('table')
              ->setTemplate(APP_PATH. 'manager/view/public/template_order.html')

              // ->setPageTips('所有用户支付的新订单单','warning')
              ->setPageTitle('订单列表')
              ->addOrder('id')
              ->setTabNav($list_tab,  $group)
              ->addColumn('id', 'id')
              ->addColumn('order_number', '订单号')
              ->addColumn('user_name', '姓名')
              ->addColumn('user_phone', '电话号码')
              ->addColumn('good_name', '商品名')
              ->addColumn('good_num', '商品数量')
              ->addColumn('good_price', '商品总价(元)')
              ->addColumn('order_price', '实付金额(元)')
              ->addColumn('user_address', '地址')
              ->addColumn('create_time', '下单时间')
              // ->addColumn('right_button', '操作', 'btn') 
              ->addColumn('remark','拒绝理由')
              ->addTopButtons(['accept' => ['id' => 'accept','title'=>'重新派单','class'=>'btn  btn-info tPost','icon'=>'ti-truck icon-lg','href'=>'javascript:;','url'=>url('manager/api/redistribution'),'form'=>'tableForm']])
              ->setRowList($re)
              ->fetch();
    }

    /**
     * [overtime 超时订单(物流)]
     * @Author   WuSong
     * @DateTime 2017-09-28T15:46:49+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function overtime(){
       $group = input('group')?input('group'):'news';
       $list_tab = [
            'news' => ['title' => '超时订单处理', 'url' => url('manager/order/news_more', ['group' => 'news'])],
        ];
      $time = date('Y-m-d H:i:s' ,time()- 5*3600);
      $re = $this->getbase->getall('order',['where'=>"status >0 and status <9  and take_time <'$time'"]);

          foreach ($re as $k=> $v) {
            $re[$k]['good_num'] = array_sum(explode(',', trim($v['good_num'],',')));
            $re[$k]['user_phone'] = decode($re[$k]['user_phone']);
            $re[$k]['timeout'] = date("d H:i",(time()-(strtotime($v['give_time']))-3600*5));
            
          }
           // $page = $re->render();
          
          return $this->builder('table')
              ->setTemplate(APP_PATH. 'manager/view/public/template_order.html')

              // ->setPageTips('所有用户支付的新订单单','warning')
              ->setPageTitle('订单列表')
              ->addOrder('id')
              ->setSearch(['id' => '订单id', 'order_number' => '订单号'])
              ->setTabNav($list_tab,  $group)
              ->addColumn('id', 'id')
              ->addColumn('order_number', '订单号')
              ->addColumn('user_name', '姓名')
              ->addColumn('user_phone', '电话号码')
              ->addColumn('good_name', '商品名')
              ->addColumn('good_num', '商品数量')
              ->addColumn('good_price', '商品总价(元)')
              ->addColumn('order_price', '实付金额(元)')
              ->addColumn('user_address', '地址')
              ->addColumn('take_time', '下单时间')
              ->addColumn('give_time', '送单时间') 
              ->addColumn('timeout', '超时时间') 
              ->setRowList($re)
              // ->setPages($page)
              ->fetch();

    }
    /**
     * [e_news 所有订单处理]
     * @Author   Jerry
     * @DateTime 2017-10-30T10:08:13+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
      public function e_news(){

      $group = input('group')?input('group'):'bespeak';
       $list_tab = [
            'bespeak' => ['title' => '预约'.$this->_countOrder(['status'=>10]), 'url' => url('manager/order/e_news', ['group' => 'bespeak'])],
            'check' => ['title' => '待检查'.$this->_countOrder(['status'=>40]), 'url' => url('manager/order/e_news', ['group' => 'check'])],
            'sixzero' => ['title' => '洗前检查'.$this->_countOrder(['status'=>60]), 'url' => url('manager/order/e_news', ['group' => 'sixzero'])],
            'sixone' => ['title' => '衣服分类'.$this->_countOrder(['status'=>61]), 'url' => url('manager/order/e_news', ['group' => 'sixone'])],
            'sixtwo' => ['title' => '洗前处理'.$this->_countOrder(['status'=>62]), 'url' => url('manager/order/e_news', ['group' => 'sixtwo'])],
            'sixthree' => ['title' => '衣服洗涤'.$this->_countOrder(['status'=>63]), 'url' => url('manager/order/e_news', ['group' => 'sixthree'])],
            'sixfour' => ['title' => '衣服烘干'.$this->_countOrder(['status'=>64]), 'url' => url('manager/order/e_news', ['group' => 'sixfour'])],
            'sixfive' => ['title' => '衣服整烫'.$this->_countOrder(['status'=>65]), 'url' => url('manager/order/e_news', ['group' => 'sixfive'])],
            'sixsix' => ['title' => '洗后检查'.$this->_countOrder(['status'=>66]), 'url' => url('manager/order/e_news', ['group' => 'sixsix'])],
            'dispatching' => ['title' => '清洗完待配送'.$this->_countOrder(['status'=>70]), 'url' => url('manager/order/e_news', ['group' => 'dispatching'])],
            'finish' => ['title' => '配送完成'.$this->_countOrder(['status'=>90]), 'url' => url('manager/order/e_news', ['group' => 'finish'])],
            // 'take_abnormal' =>  ['title' => '取件异常'.$this->_count_take(['type'=> 1, "status"=>'-1']), 'url' => url('manager/order/e_news', ['group' => 'take_abnormal'])],##暂时隐藏 Jerry2017-11-9
             // 'give_abnormal' =>  ['title' => '送件异常'.$this->_count_give(['type'=> 2, "status"=>'-1']), 'url' => url('manager/order/e_news', ['group' => 'give_abnormal'])], ##暂时隐藏 Jerry2017-11-9
        ];

        $html =  $this->builder('table')
              ->setTemplate(APP_PATH. 'manager/view/public/template_table.html')
              ->setPageTitle('订单列表')
              ->addOrder('id')
              ->setTabNav($list_tab,  $group)
              ->addColumn('id', 'id')
              ->addColumn('order_number', '订单号');
              
        $map = $this->getMap();
        $order = $this->getOrder();

          switch ($group) {
            case 'bespeak':##预约成功
                $map['status'] ='10';
                $html = $this->_bespeak($html);##表格对象
                 ##所有配送员
              $this->assign('express',$this->getbase->getall('users_express',['where'=>['status'=>1,'real_status'=>2],'field'=>'id,phone,avatarurl,realname']));
              break;



            case 'check':##检查并清洗
             $map['status'] ='40';
              $html = $this->_check($html);##表格对象
            break;
            
            case 'sixzero':##检查并清洗
             $map['status'] ='60';
              $html = $this->_sixzero($html);##表格对象
            break;

            case 'sixone':##检查并清洗
             $map['status'] ='61';
              $html = $this->_sixone($html);##表格对象
            break;

            case 'sixtwo':##检查并清洗
             $map['status'] ='62';
              $html = $this->_sixtwo($html);##表格对象
            break;

            case 'sixthree':##检查并清洗
             $map['status'] ='63';
              $html = $this->_sixthree($html);##表格对象
            break;

            case 'sixfour':##检查并清洗
             $map['status'] ='64';
              $html = $this->_sixfour($html);##表格对象
            break;

            case 'sixfive':##检查并清洗
             $map['status'] ='65';
              $html = $this->_sixfive($html);##表格对象
            break;

            case 'sixsix':##检查并清洗
             $map['status'] ='66';
              $html = $this->_sixsix($html);##表格对象
            break;

            case 'dispatching':##洗完并配送
             $map['status'] ='70';
             $html = $this->_dispatching($html);
                ##所有配送员
              $this->assign('express',$this->getbase->getall('users_express',['where'=>['status'=>1,'real_status'=>2],'field'=>'id,phone,avatarurl,realname']));
            break;
            


            case 'finish':##完成订单
             $map['status'] ='90';
             $html = $this->_finish($html);
            break;

            //取件异常订单
            case 'take_abnormal':##洗完并配送
             $map['status'] = '-1';
             $html = $this->_take_abnormal($html);
                ##所有配送员
              $this->assign('express',$this->getbase->getall('users_express',['where'=>['status'=>1,'real_status'=>2],'field'=>'id,phone,avatarurl,realname']));
            break;

            //送件异常订单
            case 'give_abnormal':##洗完并配送
             $map['status']= '-1';
             $html = $this->_give_abnormal($html);
                ##所有配送员
              $this->assign('express',$this->getbase->getall('users_express',['where'=>['status'=>1,'real_status'=>2],'field'=>'id,phone,avatarurl,realname']));
            break;

          }
          $map['create_time'] = ['>',config('transition_time')];##新老版本过度
          $re = $this->getbase->getpages('order',['where'=>$map,'order'=>$order]);
          $data = [];
          foreach ($re as  $v) {
            $v['user_phone'] = decode($v['user_phone']);
            $v['create_time'] = date('Y-m-d H:i:s',$v['create_time']); 
            $v['goods_counts'] = array_sum(explode(",", $v['good_num']));
            $v['status_Zh'] = "<span class='btn btn-sm btn-$class'>".config('order_status')[$v['status']]."</span>";
              $data[] = $v;
          }
          $page = $re->render();
       return $html->setRowList($data)->setPages($page)->fetch(); 
          
      
           
    }
    /**
     * [_countOrder 求和 订单数据]
     * @Author   Jerry
     * @DateTime 2017-10-30T14:25:30+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    private function _countOrder($map){
      $map['create_time'] = ['>',config('transition_time')];##新老版本过度
      if($this->getbase->getcount('order',['where'=>$map])>0){
         return "<span class='badge badge-header badge-danger' style='top:15px;'>".$this->getbase->getcount('order',['where'=>$map]). "</span>";
      }
       
    }
    //取件异常
    private function _count_take($map){
       if($this->getbase->getcount('express_order_users',['where'=>$map])>0){
        return "<span class='badge badge-header badge-danger' style='top:15px;'>".$this->getbase->getcount('express_order_users',['where'=>$map])."</span>";
      }
       // return "<span class='badge badge-header badge-danger' style='top:15px;'>".$this->getbase->getcount('express_order_users',['where'=>$map])."</span>";
    }
    //送件异常
    private function _count_give($map){
      //  $map = "type=2 and status=-1";
       if($this->getbase->getcount('express_order_users',['where'=>$map])>0){
         return "<span class='badge badge-header badge-danger' style='top:15px;'>".$this->getbase->getcount('express_order_users',['where'=>$map])."</span>";
      }
    }

    
      /**
       * [_take_abnormal 取件异常]
       * @Author   WuSong
       * @DateTime 2017-11-01T16:18:43+0800
       * @Example  eg:
       * @param    [type]                   $html [description]
       * @return   [type]                         [description]
       */
    private function _take_abnormal($html){

           $re = $this->getbase->getall('order',['where'=>"eou.type =1 and eou.status= -1",'alias'=>'o','join'=>[['qlbl_express_order_users eou','eou.order_id=o.id']],'field'=>'o.id,o.order_number,o.user_name,o.good_name,o.user_phone,o.good_num,o.good_price,o.order_price,o.user_address,o.take_time,o.status,eou.type,eou.create_date,eou.remark eouremark'],'LEFT');
          foreach ($re as $k=> $v) {
            
            $re[$k]['good_num'] = array_sum(explode(',', trim($v['good_num']),','));
            $re[$k]['user_phone'] = decode($v['user_phone']);

          }
          $this->assign('express',$this->getbase->getall('users_express',['where'=>['status'=>1,'real_status'=>2],'field'=>'id,phone,avatarurl,realname']));

             
              $html = $html ->addColumn('user_name', '姓名')
              ->addColumn('user_phone', '电话号码')
              ->addColumn('good_name', '商品名')
              ->addColumn('good_num', '商品数量')
              ->addColumn('good_price', '商品总价(元)')
              ->addColumn('order_price', '实付金额(元)')
              ->addColumn('user_address', '地址')
              ->addColumn('eouremark','拒绝理由')
              ->setPageTips('所有取件被拒的订单','danger')
              ->addTopButtons(['accept' => ['id' => 'accept','title'=>'重新派单(取)','class'=>'btn  btn-info tPost','icon'=>'ti-truck icon-lg','href'=>'javascript:;','url'=>url('manager/api/e_take_redistribution'),'form'=>'tableForm']])
              ->setTemplate(APP_PATH. 'manager/view/public/template_order.html')
              ->setRowList($re);
              return $html;
    }

    /**
     * [_give_abnormal 送件异常]
     * @Author   WuSong
     * @DateTime 2017-11-01T16:19:08+0800
     * @Example  eg:
     * @param    [type]                   $html [description]
     * @return   [type]                         [description]
     */
    private function _give_abnormal($html){

           $re = $this->getbase->getall('order',['where'=>"eou.type =2 and eou.status= -1",'alias'=>'o','join'=>[['qlbl_express_order_users eou','eou.order_id=o.id']],'field'=>'o.id,o.order_number,o.user_name,o.good_name,o.user_phone,o.good_num,o.good_price,o.order_price,o.user_address,o.take_time,o.status,eou.type,eou.create_date,eou.remark eouremark'],'LEFT');
          foreach ($re as $k=> $v) {
            
            $re[$k]['good_num'] = array_sum(explode(',', trim($v['good_num']),','));
            $re[$k]['user_phone'] = decode($v['user_phone']);

          }
          $this->assign('express',$this->getbase->getall('users_express',['where'=>['status'=>1,'real_status'=>2],'field'=>'id,phone,avatarurl,realname']));

             
              $html = $html ->addColumn('user_name', '姓名')
              ->addColumn('user_phone', '电话号码')
              ->addColumn('good_name', '商品名')
              ->addColumn('good_num', '商品数量')
              ->addColumn('good_price', '商品总价(元)')
              ->addColumn('order_price', '实付金额(元)')
              ->addColumn('user_address', '地址') 
              ->addColumn('eouremark','拒绝理由')
              ->setPageTips('所有送件被拒的订单','danger')
              ->addTopButtons(['accept' => ['id' => 'accept','title'=>'重新派单(送)','class'=>'btn  btn-info tPost','icon'=>'ti-truck icon-lg','href'=>'javascript:;','url'=>url('manager/api/e_give_redistribution'),'form'=>'tableForm']])
              ->setTemplate(APP_PATH. 'manager/view/public/template_order.html')
              ->setRowList($re);
              return $html;
    }




    /**
     * [_bespeak 预约处理]
     * @Author   Jerry
     * @DateTime 2017-10-30T10:34:27+0800
     * @Example  eg:
     * @param    [type]                   $html [表单对象]
     * @return   [type]                         [description]
     */
    private function _bespeak($html){


            $html = $html->addColumn('user_name', '姓名')
              ->addColumn('user_phone', '电话号码')
              ->addColumn('good_name', '商品名')
              ->addColumn('goods_counts', '商品数量')
              ->addColumn('good_price', '商品总价(元)')
              ->addColumn('order_price', '实付金额(元)')
              ->addColumn('user_address', '地址')
              ->addColumn('create_time', '下单时间')
              ->addColumn('right_button', '操作', 'btn')
              ->setPageTips('所有用户预约未支付的新订单<br/>**如果开启单物流模式，将会自动分配','danger')
                           ->addTopButtons(['accept' => ['id' => 'accept','title'=>'指派人员取衣','class'=>'btn  btn-info tPost','icon'=>'ti-truck icon-lg','href'=>'javascript:;','url'=>url('manager/api/e_getgoods'),'form'=>'tableForm']])
                           ->addRightButtons([
                       'calendar' => ['icon'=>'fa fa-remove','title'=>'取消订单','class'=>'btn btn-xs btn-default frAlert','url'=>url('manager/order/e_refund',['id'=>"__id__"]),'href'=>'javascript:;'],
                   ])->setTemplate(APP_PATH. 'manager/view/public/template_order.html');

              return $html;
               

    }
    /**
     * [_dispatching 清洗完成，开始配送]
     * @Author   Jerry
     * @DateTime 2017-10-30T14:00:44+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    private  function _dispatching($html){
           return $html->addColumn('user_name', '姓名')
              ->addColumn('user_phone', '电话号码')
              ->addColumn('good_name', '商品名')
              ->addColumn('goods_counts', '商品数量')
              ->addColumn('good_price', '商品总价(元)')
              ->addColumn('order_price', '实付金额(元)')
              ->addColumn('user_address', '地址')
              ->addColumn('create_time', '下单时间')
              ->addColumn('right_button', '操作', 'btn')
              ->setPageTips('所有已完成清洗的订单','danger')
                      ->addTopButtons(['accept' => ['id' => 'accept','title'=>'开始配送','class'=>'btn  btn-success tPost','icon'=>'ti-truck icon-lg','href'=>'javascript:;','url'=>url('manager/api/e_dispatch'),'form'=>'tableForm']]) // 批量添加顶部按钮
                      ->addRightButtons([
                    'calendar' => ['icon'=>'fa fa-calendar','title'=>'时间线','class'=>'btn btn-xs btn-default frAlert','url'=>url('manager/order/timeline',['orderid'=>"__id__"]),'href'=>'javascript:;'],
                    'print' => ['icon'=>'fa fa-print','title'=>'打印单据','target'=>'_blank','class'=>'btn btn-xs btn-default','href'=>url('manager/ordertest/order_print',['orderid'=>"__id__"]),'url'=>'javascript:;'],
                      'detail' => ['icon'=>'fa fa-newspaper-o','title'=>'','class'=>'btn btn-xs btn-default frAlert','url'=>url('manager/ordertest/order_detail',['orderid'=>"__id__"]),'href'=>'javascript:;'],
                      'detail' => ['icon'=>'fa fa-newspaper-o','title'=>'单据信息','class'=>'btn btn-xs btn-default frAlert','url'=>url('manager/ordertest/order_detail',['orderid'=>"__id__"]),'href'=>'javascript:;'],
                    ])->setTemplate(APP_PATH. 'manager/view/public/template_order.html');
        

    } 
    /**
     * [_finish description]
     * @Author   Jerry
     * @DateTime 2017-10-30T14:07:10+0800
     * @Example  eg:
     * @param    [type]                   $html [description]
     * @return   [type]                         [description]
     */
    private function _finish($html){
       $html = $html->addColumn('user_name', '姓名')
              ->addColumn('user_phone', '电话号码')
              ->addColumn('good_name', '商品名')
              ->addColumn('goods_counts', '商品数量')
              ->addColumn('good_price', '商品总价(元)')
              ->addColumn('order_price', '实付金额(元)')
              ->addColumn('user_address', '地址')
              ->addColumn('create_time', '下单时间')
              ->addColumn('right_button', '操作', 'btn')
              // ->setPageTips('所有已完成清洗的订单','danger')
              ->setPageTips('所有正在配送的订单','danger')
              ->addTopButtons(['accept' => ['id' => 'accept','title'=>'订单完结','class'=>'btn  btn-success tPost','icon'=>'ti-truck icon-lg','href'=>'javascript:;','url'=>url('manager/api/e_finish'),'form'=>'tableForm']]) // 批量添加顶部按钮
              ->addRightButtons([
                  'calendar' => ['icon'=>'fa fa-calendar','title'=>'时间线','class'=>'btn btn-xs btn-default frAlert','url'=>url('manager/order/timeline',['orderid'=>"__id__"]),'href'=>'javascript:;'],
                   'print' => ['icon'=>'fa fa-print','title'=>'打印单据','target'=>'_blank','class'=>'btn btn-xs btn-default','href'=>url('manager/ordertest/order_print',['orderid'=>"__id__"]),'url'=>'javascript:;'],
                  'detail' => ['icon'=>'fa fa-newspaper-o','title'=>'单据信息','class'=>'btn btn-xs btn-default frAlert','url'=>url('manager/ordertest/order_detail',['orderid'=>"__id__"]),'href'=>'javascript:;'],]);
       return $html;                   
    }
    /**
     * [_check description]
     * @Author   Jerry
     * @DateTime 2017-10-30T11:13:54+0800
     * @Example  eg:
     * @html     [obj]                    ['表格对象']
     * @return   [type]                   [description]
     */
    private function _check($html){

      $html = $html->addColumn('user_name', '姓名')
              ->addColumn('user_phone', '电话号码')
              ->addColumn('good_name', '商品名')
              ->addColumn('goods_counts', '商品数量')
              ->addColumn('good_price', '商品总价(元)')
              ->addColumn('order_price', '实付金额(元)')
              ->addColumn('user_address', '地址')
              ->addColumn('create_time', '下单时间')
              ->addColumn('right_button', '操作', 'btn')
              ->setPageTips('所有刚从客服取回的衣服','danger')
              ->addTopButtons(['accept' => ['id' => 'accept','title'=>'检查完成，开始清洗','class'=>'btn  btn-warning tPost','icon'=>'ti-truck icon-lg','href'=>'javascript:;','url'=>url('manager/api/e_check'),'form'=>'tableForm']]) // 批量添加顶部按钮
              ->addRightButtons([
                       // 'refund' => ['icon'=>'fa fa-remove','title'=>'取消订单','class'=>'btn btn-xs btn-danger frAlert','url'=>url('manager/ordertest/e_refund_back_money',['id'=>"__id__"]),'href'=>'javascript:;'],
                       'remark' => ['icon'=>'fa fa-edit','title'=>'订单备注','html'=>'订单备注','class'=>'btn btn-xs btn-success frAlert','url'=>url('manager/order/e_remark',['id'=>"__id__"]),'href'=>'javascript:;'],
                       'detail' => ['icon'=>'fa fa-newspaper-o','title'=>'单据信息','class'=>'btn btn-xs btn-default frAlert','url'=>url('manager/order/order_detail',['orderid'=>"__id__"]),'href'=>'javascript:;'],
                   ]);

        return $html;
    }
    /**
     * [e_remark 订单管理员备注信息]
     * @Author   Jerry
     * @DateTime 2017-10-30T11:36:03+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function e_remark(){
      $admin_remarksInfo = $this->getbase->getone('order',['where'=>['id'=>(int)input('id')],'field'=>'admin_remarks']);
        return $this->builder('form')
            ->setUrl(url('manager/api/e_remark'))
            ->addHidden('id',intval(input('id')))
            ->setPageTitle('备注信息')
            ->addTextarea('admin_remarks', '备注信息','',$admin_remarksInfo['admin_remarks'])##门店订单备注
            ->setBtnTitle(['submit' => '提交'])
            ->fetch();
    }
    /**
     * [refund 取消订单]
     * @Author   Jerry
     * @DateTime 2017-08-25T15:21:17+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function e_refund(){
        return $this->builder('form')
            ->setUrl(url('manager/api/e_refund'))
            ->addHidden('id',intval(input('id')))
            ->setPageTitle('取消订单')
            ->addText('remarks', '取消原因')
            ->setBtnTitle(['submit' => '提交', 'back' => '关闭'])
            ->fetch();
    }
    /**
     * [e_refund_back_money 订单取消和退款处理 ##此逻辑需要和前台配合，先处理前台后再处理此逻辑]
     * @Author   Jerry
     * @DateTime 2017-10-30T11:32:26+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    // public function e_refund_back_money(){

    // }

    ##################################
    #                                #
    #     以下为洗护流程批量操作     #
    #                                #
    ##################################

    /**
     * [_sixone 衣服洗前检查]
     * @Author   WuSong
     * @DateTime 2017-11-10T14:37:47+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function _sixzero($html){

        $html = $html->addColumn('user_name', '姓名')
              ->addColumn('user_phone', '电话号码')
              ->addColumn('good_name', '商品名')
              ->addColumn('goods_counts', '商品数量')
              ->addColumn('good_price', '商品总价(元)')
              ->addColumn('order_price', '实付金额(元)')
              ->addColumn('user_address', '地址')
              ->addColumn('create_time', '下单时间')
              ->addColumn('right_button', '操作', 'btn')
              ->setPageTips('所有准备进行衣服洗前检查的订单','danger')
              ->addTopButtons(['accept' => ['id' => 'accept','title'=>'衣服分类','class'=>'btn  btn-warning tPost','icon'=>'ti-truck icon-lg','href'=>'javascript:;','url'=>url('manager/api/_sixzero'),'form'=>'tableForm']]) // 批量添加顶部按钮
              ->addRightButtons([
                       // 'refund' => ['icon'=>'fa fa-remove','title'=>'取消订单','class'=>'btn btn-xs btn-danger frAlert','url'=>url('manager/ordertest/e_refund_back_money',['id'=>"__id__"]),'href'=>'javascript:;'],
                       'remark' => ['icon'=>'fa fa-edit','title'=>'订单备注','html'=>'订单备注','class'=>'btn btn-xs btn-success frAlert','url'=>url('manager/order/e_remark',['id'=>"__id__"]),'href'=>'javascript:;'],
                       'detail' => ['icon'=>'fa fa-newspaper-o','title'=>'单据信息','class'=>'btn btn-xs btn-default frAlert','url'=>url('manager/order/order_detail',['orderid'=>"__id__"]),'href'=>'javascript:;'],
                   ]);

            return $html;
    }

    /**
     * [_sixone 衣服分类]
     * @Author   WuSong
     * @DateTime 2017-11-10T15:15:28+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function _sixone($html){

        $html = $html->addColumn('user_name', '姓名')
              ->addColumn('user_phone', '电话号码')
              ->addColumn('good_name', '商品名')
              ->addColumn('goods_counts', '商品数量')
              ->addColumn('good_price', '商品总价(元)')
              ->addColumn('order_price', '实付金额(元)')
              ->addColumn('user_address', '地址')
              ->addColumn('create_time', '下单时间')
              ->addColumn('right_button', '操作', 'btn')
              ->setPageTips('所有准备进行衣服分类的订单','danger')
              ->addTopButtons(['accept' => ['id' => 'accept','title'=>'衣服洗前处理','class'=>'btn  btn-warning tPost','icon'=>'ti-truck icon-lg','href'=>'javascript:;','url'=>url('manager/api/_sixone'),'form'=>'tableForm']]) // 批量添加顶部按钮
              ->addRightButtons([
                       // 'refund' => ['icon'=>'fa fa-remove','title'=>'取消订单','class'=>'btn btn-xs btn-danger frAlert','url'=>url('manager/ordertest/e_refund_back_money',['id'=>"__id__"]),'href'=>'javascript:;'],
                       'remark' => ['icon'=>'fa fa-edit','title'=>'订单备注','html'=>'订单备注','class'=>'btn btn-xs btn-success frAlert','url'=>url('manager/order/e_remark',['id'=>"__id__"]),'href'=>'javascript:;'],
                       'detail' => ['icon'=>'fa fa-newspaper-o','title'=>'单据信息','class'=>'btn btn-xs btn-default frAlert','url'=>url('manager/order/order_detail',['orderid'=>"__id__"]),'href'=>'javascript:;'],
                   ]);

        return $html;
    }


    /**
     * [_sixtwo 衣服洗前处理]
     * @Author   WuSong
     * @DateTime 2017-11-10T15:17:01+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function _sixtwo($html){

        $html = $html->addColumn('user_name', '姓名')
              ->addColumn('user_phone', '电话号码')
              ->addColumn('good_name', '商品名')
              ->addColumn('goods_counts', '商品数量')
              ->addColumn('good_price', '商品总价(元)')
              ->addColumn('order_price', '实付金额(元)')
              ->addColumn('user_address', '地址')
              ->addColumn('create_time', '下单时间')
              ->addColumn('right_button', '操作', 'btn')
              ->setPageTips('所有准备进行衣服洗前处理的订单','danger')
              ->addTopButtons(['accept' => ['id' => 'accept','title'=>'衣服洗涤(干洗/水洗)','class'=>'btn  btn-warning tPost','icon'=>'ti-truck icon-lg','href'=>'javascript:;','url'=>url('manager/api/_sixtwo'),'form'=>'tableForm']]) // 批量添加顶部按钮
              ->addRightButtons([
                       // 'refund' => ['icon'=>'fa fa-remove','title'=>'取消订单','class'=>'btn btn-xs btn-danger frAlert','url'=>url('manager/ordertest/e_refund_back_money',['id'=>"__id__"]),'href'=>'javascript:;'],
                       'remark' => ['icon'=>'fa fa-edit','title'=>'订单备注','html'=>'订单备注','class'=>'btn btn-xs btn-success frAlert','url'=>url('manager/order/e_remark',['id'=>"__id__"]),'href'=>'javascript:;'],
                       'detail' => ['icon'=>'fa fa-newspaper-o','title'=>'单据信息','class'=>'btn btn-xs btn-default frAlert','url'=>url('manager/order/order_detail',['orderid'=>"__id__"]),'href'=>'javascript:;'],
                   ]);

        return $html;
    }


    /**
     * [_sixthree 衣服洗涤(干洗/水洗)]
     * @Author   WuSong
     * @DateTime 2017-11-10T15:17:16+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function _sixthree($html){

        $html = $html->addColumn('user_name', '姓名')
              ->addColumn('user_phone', '电话号码')
              ->addColumn('good_name', '商品名')
              ->addColumn('goods_counts', '商品数量')
              ->addColumn('good_price', '商品总价(元)')
              ->addColumn('order_price', '实付金额(元)')
              ->addColumn('user_address', '地址')
              ->addColumn('create_time', '下单时间')
              ->addColumn('right_button', '操作', 'btn')
              ->setPageTips('所有准备进行衣服洗涤(干洗/水洗)的订单','danger')
              ->addTopButtons(['accept' => ['id' => 'accept','title'=>'衣服烘干','class'=>'btn  btn-warning tPost','icon'=>'ti-truck icon-lg','href'=>'javascript:;','url'=>url('manager/api/_sixthree'),'form'=>'tableForm']]) // 批量添加顶部按钮
              ->addRightButtons([
                       // 'refund' => ['icon'=>'fa fa-remove','title'=>'取消订单','class'=>'btn btn-xs btn-danger frAlert','url'=>url('manager/ordertest/e_refund_back_money',['id'=>"__id__"]),'href'=>'javascript:;'],
                       'remark' => ['icon'=>'fa fa-edit','title'=>'订单备注','html'=>'订单备注','class'=>'btn btn-xs btn-success frAlert','url'=>url('manager/order/e_remark',['id'=>"__id__"]),'href'=>'javascript:;'],
                       'detail' => ['icon'=>'fa fa-newspaper-o','title'=>'单据信息','class'=>'btn btn-xs btn-default frAlert','url'=>url('manager/order/order_detail',['orderid'=>"__id__"]),'href'=>'javascript:;'],
                   ]);

        return $html;
    }


    /**
     * [_sixfour 衣服烘干]
     * @Author   WuSong
     * @DateTime 2017-11-10T15:17:36+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function _sixfour($html){

        $html = $html->addColumn('user_name', '姓名')
              ->addColumn('user_phone', '电话号码')
              ->addColumn('good_name', '商品名')
              ->addColumn('goods_counts', '商品数量')
              ->addColumn('good_price', '商品总价(元)')
              ->addColumn('order_price', '实付金额(元)')
              ->addColumn('user_address', '地址')
              ->addColumn('create_time', '下单时间')
              ->addColumn('right_button', '操作', 'btn')
              ->setPageTips('所有准备进行衣服烘干的订单','danger')
              ->addTopButtons(['accept' => ['id' => 'accept','title'=>'衣服整烫(熨烫/缝补)','class'=>'btn  btn-warning tPost','icon'=>'ti-truck icon-lg','href'=>'javascript:;','url'=>url('manager/api/_sixfour'),'form'=>'tableForm']]) // 批量添加顶部按钮
              ->addRightButtons([
                       // 'refund' => ['icon'=>'fa fa-remove','title'=>'取消订单','class'=>'btn btn-xs btn-danger frAlert','url'=>url('manager/ordertest/e_refund_back_money',['id'=>"__id__"]),'href'=>'javascript:;'],
                       'remark' => ['icon'=>'fa fa-edit','title'=>'订单备注','html'=>'订单备注','class'=>'btn btn-xs btn-success frAlert','url'=>url('manager/order/e_remark',['id'=>"__id__"]),'href'=>'javascript:;'],
                       'detail' => ['icon'=>'fa fa-newspaper-o','title'=>'单据信息','class'=>'btn btn-xs btn-default frAlert','url'=>url('manager/order/order_detail',['orderid'=>"__id__"]),'href'=>'javascript:;'],
                   ]);

        return $html;
    }

    /**
     * [_sixfive 衣服整烫(熨烫/缝补)]
     * @Author   WuSong
     * @DateTime 2017-11-10T15:19:11+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function _sixfive($html){

        $html = $html->addColumn('user_name', '姓名')
              ->addColumn('user_phone', '电话号码')
              ->addColumn('good_name', '商品名')
              ->addColumn('goods_counts', '商品数量')
              ->addColumn('good_price', '商品总价(元)')
              ->addColumn('order_price', '实付金额(元)')
              ->addColumn('user_address', '地址')
              ->addColumn('create_time', '下单时间')
              ->addColumn('right_button', '操作', 'btn')
              ->setPageTips('所有准备进行衣服整烫(熨烫/缝补)的订单','danger')
              ->addTopButtons(['accept' => ['id' => 'accept','title'=>'衣服洗后检查','class'=>'btn  btn-warning tPost','icon'=>'ti-truck icon-lg','href'=>'javascript:;','url'=>url('manager/api/_sixfive'),'form'=>'tableForm']]) // 批量添加顶部按钮
              ->addRightButtons([
                       // 'refund' => ['icon'=>'fa fa-remove','title'=>'取消订单','class'=>'btn btn-xs btn-danger frAlert','url'=>url('manager/ordertest/e_refund_back_money',['id'=>"__id__"]),'href'=>'javascript:;'],
                       'remark' => ['icon'=>'fa fa-edit','title'=>'订单备注','html'=>'订单备注','class'=>'btn btn-xs btn-success frAlert','url'=>url('manager/order/e_remark',['id'=>"__id__"]),'href'=>'javascript:;'],
                       'detail' => ['icon'=>'fa fa-newspaper-o','title'=>'单据信息','class'=>'btn btn-xs btn-default frAlert','url'=>url('manager/order/order_detail',['orderid'=>"__id__"]),'href'=>'javascript:;'],
                   ]);

        return $html;
    }

    /**
     * [_sixsix 衣服洗后检查]
     * @Author   WuSong
     * @DateTime 2017-11-10T15:19:28+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function _sixsix($html){

        $html = $html->addColumn('user_name', '姓名')
              ->addColumn('user_phone', '电话号码')
              ->addColumn('good_name', '商品名')
              ->addColumn('goods_counts', '商品数量')
              ->addColumn('good_price', '商品总价(元)')
              ->addColumn('order_price', '实付金额(元)')
              ->addColumn('user_address', '地址')
              ->addColumn('create_time', '下单时间')
              ->addColumn('right_button', '操作', 'btn')
              ->setPageTips('所有准备进行衣服洗后检查的订单','danger')
              ->addTopButtons(['accept' => ['id' => 'accept','title'=>'衣物清洗完成','class'=>'btn  btn-warning tPost','icon'=>'ti-truck icon-lg','href'=>'javascript:;','url'=>url('manager/api/_sixsix'),'form'=>'tableForm']]) // 批量添加顶部按钮
              ->addRightButtons([
                       // 'refund' => ['icon'=>'fa fa-remove','title'=>'取消订单','class'=>'btn btn-xs btn-danger frAlert','url'=>url('manager/ordertest/e_refund_back_money',['id'=>"__id__"]),'href'=>'javascript:;'],
                       'remark' => ['icon'=>'fa fa-edit','title'=>'订单备注','html'=>'订单备注','class'=>'btn btn-xs btn-success frAlert','url'=>url('manager/order/e_remark',['id'=>"__id__"]),'href'=>'javascript:;'],
                       'detail' => ['icon'=>'fa fa-newspaper-o','title'=>'单据信息','class'=>'btn btn-xs btn-default frAlert','url'=>url('manager/order/order_detail',['orderid'=>"__id__"]),'href'=>'javascript:;'],
                   ]);

        return $html;
    }


}
