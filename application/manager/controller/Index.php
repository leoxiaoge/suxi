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
class index extends ManagerBase
{

	public function _initialize()
    {
     	parent::_initialize();  
     	
    }
    public function test(){
        // 条型码
        $data['order_number'] = 'SX860201710305199';
      c_barcode($data['order_number'],'./public/uploads/barcode/'.$data['order_number'].'.png');
        
        return $this->fetch();
    }
 
    public function pushmsg(){
        $re = pushordermsg('20','您有一个新订单','新订单XXXXXX介绍');
        // show($re);
    }

    public function index()
    {
        // show($_SERVER);
        // echo config('database.database');

        $order = $this->order_count();
      //  Db::table('think_user')->sum('score');

        $user = $this->user_count();

        $this->assign([
            'order'  => $order,
            'user' => $user
        ]);


       // show($_SESSION);
     return $this->fetch();
		
    }

    public function user_count(){
        $data = Db::name('users_wx')->field('id,create_time')->select();


        $user['count_user'] = count($data);
        $now_mon = strtotime(date('Y-m'));
        $now_day = strtotime(date('Y-m-d'));


        $mon = strtotime($this->mondayTime(time() + 24*3600*2,false));

        $month   = 0;
        $day     = 0;

        $week  = 0;

        foreach ($data as $k => $v){
            if($now_mon<$v['create_time']){
                $month++;
            }
            if($now_day<$v['create_time']){
                $day++;


            }
            if($mon<$v['create_time']){
                $week++;
            }

        }

        $user['count_month'] = $month;
        $user['count_day'] = $day;
        $user['week_a'] = $week;

        return $user;


    }

    public function order_count(){
        $data = Db::name('order')->field('id,order_price,create_time,status')->where('status >= 0')->select();


        $order['count_order'] = count($data);
        $now_mon = strtotime(date('Y-m'));
        $now_day = strtotime(date('Y-m-d'));


        $mon = strtotime($this->mondayTime(time() + 24*3600*2,false));

        $month   = 0;
        $day     = 0;
        $t_day     = 0;
        $week  = 0;
        $price = 0;
        $t_price = 0;
        foreach ($data as $k => $v){
            if($now_mon<$v['create_time']){
                $month++;
            }
            if($now_day<$v['create_time']){
                $day++;
                $price = $price+$v['order_price'];
                if($v['status'] == 11){
                    $t_price = $t_price+$v['order_price'];
                    $t_day++;
                }
            }
            if($mon<$v['create_time']){
                $week++;
            }

        }

        $order['count_month'] = $month;
        $order['count_day'] = $day;
        $order['week_a'] = $week;
        $order['price']  = $price;
        $order['t_price'] =$t_price;
        $order['t_day'] =$t_day;
        return $order;
    }

    public function mondayTime($timestamp=0,$is_return_timestamp=true){
        static $cache ;
        $id = $timestamp.$is_return_timestamp;
        if(!isset($cache[$id])){
            if(!$timestamp) $timestamp = time();
            $monday_date = date('Y-m-d',$timestamp-86400*date('w',$timestamp)+(date('w',$timestamp)>0?86400:-/*6*86400*/518400));
            if($is_return_timestamp){
                $cache[$id] = strtotime($monday_date);
            }else{
                $cache[$id] = $monday_date;
            }
        }
        return $cache[$id];
    }


    public function user(){
        $id = session('suinfo');

##需指定门店ID

        $storeid  = $id['store_id'];
        $storeinfo = $this->getbase->getone('store',['where'=>['id'=>$storeid]]);

        $map = $this->getMap();
        $order = $this->getOrder();
        $re = model('Base')->getpages('store_users',['where'=>$map,'order'=>$order,'list_rows'=>$_GET['list_rows']]);
        $data = [];
        foreach ($re as $v) {
            $info= $this->getbase->getone('store',['where'=>['id'=>$v['store_id']],'field'=>'name']);##所在门店
            $v['store_zh_neme'] =current($info)?current($info):"没有指定门店";
            $info= $this->getbase->getone('store_department',['where'=>['id'=>$v['store_department_id']],'field'=>'name']);##所在部门，职位
            $v['department_zh_neme'] =current($info)?current($info):"没有指定职位" ;
            $v['mobile'] = decode($v['mobile']);
            $data[] = $v;
        }
        // show($data);
        return $this->builder('table')
            ->setTemplate(APP_PATH. 'manager/view/public/template_table.html')
            ->setPageTitle('门店('.$storeinfo['name'].')')
            ->setSearch(['id' => 'id', 'user_name' => '姓名']) // 设置搜索参数
            ->setTableName('store_users')
            ->setPrimaryKey('id')
            ->addOrder('id')
            ->addColumn('id', 'id')
            ->addColumn('user_name', '姓名')
            ->addColumn('mobile', '电话')
            ->addColumn('department_zh_neme', '所在部门')
            ->addColumn('store_zh_neme', '所在门店')
            // ->addColumn('sort', '排序')
            ->addColumn('right_button', '操作', 'btn')
            ->addRightButtons(['edit'=>['href'=>url('manager/index/edit_user',['storeid'=>$storeid,'id'=>'__id__'])],

                'delete' => ['data-tips' => '删除后无法恢复。','field'=>'id'],

            ]) // 批量添加右侧按钮
            ->addTopButton('edit',['icon'=>'fa fa-plus','class'=>"btn btn-default",'title'=>'添加员工','href'=>url('manager/index/edit_user',['storeid'=>$storeid])]) // 添加顶部按钮

            ->setRowList($data) // 设置表格数据
            ->fetch();



    }


    public function edit_user(){
        ##需指定门店ID
        if(($storeid = (int)input('storeid'))<1){
            $this->error('门店归属异常');
        }else{
            $storeinfo = $this->getbase->getone('store',['where'=>['id'=>$storeid]]);
        }

        ##部门/职位
        $department = $this->getbase->getall('store_department',['field'=>'name,id']);

        if((int)input('id')){
            $detail = $this->getbase->getone('store_users',['where'=>['id'=>input('id')]]);
            extract($detail);
        }

        return $this->builder('form')
            ->setTemplate(APP_PATH. 'manager/view/public/template_form.html')
            ->setUrl(url('manager/api/editwork'))
            ->setPageTitle('添加职员('.$storeinfo['name'].')')
            ->addText('user_name', '姓名','',$user_name)
            ->addText('mobile', '手机号','',decode($mobile))
            ->addText('password', '密码','默认为:123456')
            ->addRadio('sex','性别','',['男','女'],$sex?$sex:0)
            ->addSelect('store_department_id','职位/部门','',formatArr($department,'id','name'),$store_department_id)
            ->addHidden('gourl',url('manager/index/user',['storeid'=>$storeid]))
            ->addHidden('store_id',(int)input('storeid'))
            ->addHidden('id',(int)input('id'))


            ->fetch();
    }




    public function group(){
        $id = session('suinfo');

##需指定门店ID

        $storeid  = $id['store_id'];
        $storeinfo = $this->getbase->getone('store',['where'=>['id'=>$storeid]]);

        $map = $this->getMap();
        $store = model('Base')->getpages('store_users',['where'=>$map,'store_id'=>$storeid]);
        $data = [];
        $i    = 0;

        foreach ($store as $k => $v) {

            $re = $this->getbase->getdb('store_group')->where("uid = ".$v['id'])->find();

            $str_exp =  array_filter(explode(',',$re['group']));

            $title   = '';
            foreach ($str_exp as $vs){
                $t = $this->getbase->getone('store_rule',['where'=>['id'=>$vs]]);
                $title .=','.$t['title'];
            }

            $data[$i]['id'] = $v['id'];
            $data[$i]['user_name'] = $v['user_name'];
            $data[$i]['rule_name'] = substr($title,1);
            $data[$i]['uid'] = $v['id'];
$i++;
        }


        // show($data);
        return $this->builder('table')
            ->setTemplate(APP_PATH. 'manager/view/public/template_table.html')
            ->setPageTitle('('.$storeinfo['name'].')授权')
            ->setSearch(['id' => 'id', 'user_name' => '姓名']) // 设置搜索参数
            ->setTableName('store_group')
            ->setPrimaryKey('id')
            ->addOrder('id')
            ->addColumn('id', 'id')
            ->addColumn('user_name', '姓名')
            ->addColumn('rule_name', '权限名称')
            ->addColumn('right_button', '操作', 'btn')
            ->addRightButtons([
                'user' => ['href'=>url('manager/index/edit_group',['id'=>'__id__']),'class'=>"btn btn-default btn-xs",'icon' => 'fa fa-users','title'=>'所有员工'],
                'delete' => ['data-tips' => '删除后无法恢复。','field'=>'id'],

            ]) // 批量添加右侧按钮

            ->setRowList($data) // 设置表格数据
            ->fetch();



    }

    public function edit_group(){


        $id = input('id');
        $res = $this->getbase->getdb('store_users')->where("id = ".$id)->find();
        $map = $this->getMap();



        $re = $this->getbase->getdb('store_rule')
            ->where('status=1')
            ->paginate();
        // 分页数据
        $page = $re->render();
                return $this->builder('table')
                    ->setTemplate(APP_PATH. 'manager/view/public/template_table.html')
                    ->setPageTitle('('.$res['user_name'].')授权')
                    ->setSearch([ 'title' => '权限名称']) // 设置搜索参数
                    ->setTableName('store_group')
                    ->setPrimaryKey('id')
                    ->addOrder('id')
                    ->addColumn('id', 'id')
                    ->addColumn('title', '权限介绍')
                    ->addColumn('rule', '权限地址')
                    ->addColumn('status', '状态')
                    // ->setExtraJs($accept_js)
                    ->addTopButtons(['accept' => ['id' => 'accept','title'=>'授权用户','class'=>'btn  btn-info tPost','icon'=>'ti-truck icon-lg','href'=>'javascript:;','url'=>url('manager/api/edit_group'),'form'=>'tableForm','where' => $id]]) // 批量添加顶部按钮
                    // ->addRightButtons([
                    //   'edit',
                    //   'delete' => ['data-tips' => '删除后无法恢复。','field'=>'id'],
                    // ])
                    // 批量添加右侧按钮
                    ->setRowList($re) // 设置表格数据
                    ->setPages($page) // 设置分页数据
                    ->fetch();


    }

        /**
         * [discount 优惠券赠送]
         * @Author   WuSong
         * @DateTime 2017-11-10T11:23:51+0800
         * @Example  eg:
         * @return   [type]                   [description]
         */
        public function discount(){
            $coupon = $this->getbase->getall('coupon',['where'=>['status'=>1,'type'=>4]]);
            $users_wx = $this->getbase->getall('users_wx');
            $data=[];
            foreach ($users_wx as $k => $v) {
                $v['name'] = urldecode($v['name']);
                $v['uid'] = $v['id'];
                $data[] =$v;
            }
            return $this->builder('form')
            ->setTemplate(APP_PATH. 'manager/view/public/template_form.html')
            ->setUrl(url('manager/api/discount'))
            ->setPageTitle('优惠券赠送')
            ->addSelect('uid', '指定用户', '', formatArr($data,'uid','name'),$id)
            ->addSelect('id','优惠券种类','', formatArr($coupon,'id','title'),$id)
            ->addText('title', '优惠券备注(必填)','',$title)
            ->fetch();
        }








}
