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
class User extends AdminBase
{
 /**
  * [index 用户管理]
  * @return [type] [description]
  */
  public function index(){
    $map = $this->getMap();
    $order = $this->getOrder();
    $re = model('Base')->getpages('users',['where'=>$map,
                                              'alias'=>'u',
                                              'leftjoin'=>[['users_group ug','u.group_id=ug.group_id']],
                                              'order'=>$order,
                                              'list_rows'=>$_GET['list_rows']
                                              ]);
    return $this->builder('table')
    ->setPageTitle('用户列表')
    ->setSearch(['uid' => '用户id', 'user_name' => '姓名']) // 设置搜索参数
    ->setTableName('users')
    ->setPrimaryKey('uid')
    ->addOrder('uid')
    ->addColumn('uid', 'id')
    ->addColumn('user_name', '姓名')
    ->addColumn('email', 'email')
    ->addColumn('mobile', '用户手机')
    ->addColumn('avatar_file', '头像文件')
    ->addColumn('sex', '性别')
    ->addColumn('birthday', '生日')
    ->addColumn('reg_ip', '注册IP')
    ->addColumn('last_login', '最后登录时间')
    ->addColumn('last_ip', '最后登录 IP')
    ->addColumn('online_time', '在线时间')
    ->addColumn('right_button', '操作', 'btn')
      ->addRightButtons(['edit', 'delete' => ['data-tips' => '删除后无法恢复。','field'=>'uid']]) // 批量添加右侧按钮
      ->setRowList($data_list) // 设置表格数据
    ->setRowList($re) // 设置表格数据
    ->fetch();
  }

  /**
   * [edit 编辑用户信息]
   * @Author   Jerry
   * @DateTime 2017-06-16T09:35:41+0800
   * @Example  eg:
   * @return   [type]                   [description]
   */
  public function edit(){
    $id = $this->request->only(['id']);
    $id = (int)$id['id'];
    if($id>0){
      $this->assign($users = model('Base')->getone('users',['where'=>['uid'=>$id],'cache'=>false]));
      $this->assign($users_attrib = model('Base')->getone('users_attrib',['where'=>['uid'=>$id],'cache'=>false]));
    }
    $this->assign('jobs',$province=model('Base')->getall('jobs'));
    $this->assign('category',$category=model('Base')->getall('users_group'));
   return $this->fetch('admin/user/edit');
  } 
  /**
   * [group 组管理]
   * @return [type] [description]
   */
  public function group(){
  	$map = $this->getMap(); 
    $order = $this->getOrder();
    $re = model('Base')->getpages('users_group',['where'=>$map,
                                              'order'=>$order,
                                              'list_rows'=>$_GET['list_rows']
                                              ]);
    return $this->builder('table')
    ->setPageTitle('用户组列表')
    ->setSearch(['group_id' => 'id']) // 设置搜索参数
    ->setTableName('users_group')
    ->setPrimaryKey('group_id')
    ->addOrder('group_id')
    ->addColumn('group_id', 'id')
    // ->addColumn('type', '类型0-会员组 1-系统组')
    ->addColumn('group_name', '组名')
    ->addColumn('right_button', '操作', 'btn')
    ->addTopButton('edit',['class'=>"btn btn-default",'title'=>'添加用户组','href'=>'/admin/user/editgroup','icon'=>'fa fa-plus-circle']) // 
    ->addRightButtons(
      ['edit'=>['href'=>url('admin/user/editgroup',['group_id'=>'__ID__'])], 
      'delete' => ['data-tips' => '删除后无法恢复。','field'=>'group_id'],
    'custor'=>['href'=>url('admin/auth/edit',['group_id'=>'__ID__']), 'title' => '授权','icon'  => 'fa fa-shield','class'=>'btn btn-xs btn-default',]]) // 批量添加右侧按钮
    ->setRowList($data_list) // 设置表格数据
    ->setRowList($re) // 设置表格数据
    ->fetch();
  }

  public function editgroup(){
    $data = $this->request->param();
    if($data['group_id']){
      extract(model('base')->getone('users_group',['where'=>['group_id'=>$data['group_id']]]));
    }
     return $this->builder('form')
    ->setUrl(url('systems/ajax/tmkedit'))
    ->addHidden('table','users_group')
    ->addHidden('gourl',url('admin/user/group'))
    ->addHidden('field','group_id')
    ->addHidden('group_id',$group_id)
    ->setPageTitle('添加后台用户组')
    ->addText('group_name', '组名','',$group_name)

    ->fetch();

    // return $this->fetch('admin/user/editgroup');

  }
  /**
   * [invites 批量邀请]
   * @return [type] [description]
   */
  public function invites(){
  	return $this->fetch('admin/user/invites');
  }
  /**
   * [job 职位管理]
   * @return [type] [description]
   */
  public function job(){

  	$this->assign('job',$job=model('Base')->getall('jobs'));
  	// show($job);
	return $this->fetch('admin/user/job');
  }
 
  /**
   * [edit 后台管理员编辑管理]
   * @Author   Jerry
   * @DateTime 2017-06-14T11:03:57+0800
   * @Example  eg:
   * @return   [type]                   [description]
   */
  public function adminedit(){
    $uid = (int)input('uid');
    if($uid>0){
      $adminInfo = model('Base')->getone('admin',['where'=>['uid'=>$uid],'cache'=>false]);
    }
    return $this->builder('form')
    ->setUrl(url('admin/api/addadmin'))
    ->addHidden('table','handbook')
    ->addHidden('gourl',url('admin/user/admin'))
    ->addHidden('field','uid')
    // ->addHidden('catid',$catid)
    ->addHidden('uid',$adminInfo['uid'])
    ->setPageTitle('添加后台管理员')
    ->addText('user_name', '姓名','',$adminInfo['user_name'])
    ->addText('email', 'email','',$adminInfo['email'])
    ->addText('mobile', '用户手机','',$adminInfo['mobile'])
    ->addText('password', '密码','')
    ->addSelect('group_id', '所属组','',formatArr(model('base')->getall('users_group',['field'=>'group_id,group_name']),'group_id','group_name'),$adminInfo['group_id'])
    ->addImage('avatar_file', '用户图相','',$adminInfo['mobile'])
    ->fetch(); 

  }
  /**
   * [admin 后台用户列表]
   * @Author   Jerry
   * @DateTime 2017-06-14T08:55:00+0800
   * @Example  eg:
   * @return   [type]                   [description]
   */
  public function admin(){
     $map = $this->getMap();
    $order = $this->getOrder();
    $re = model('Base')->getpages('admin',['where'=>$map,
                                              'alias'=>'u',
                                              'leftjoin'=>[['users_group ug','u.group_id=ug.group_id']],
                                              'order'=>$order,
                                              'list_rows'=>$_GET['list_rows']
                                              ]);
    return $this->builder('table')
    ->setPageTitle('用户列表')
    ->setSearch(['uid' => '用户id', 'user_name' => '姓名']) // 设置搜索参数
    ->setTableName('admin')
    ->setPrimaryKey('uid')
    ->addOrder('uid')
    ->addColumn('uid', 'id')
    ->addColumn('user_name', '姓名')
    ->addColumn('email', 'email')
    ->addColumn('mobile', '用户手机')
    ->addColumn('avatar_file', '头像文件')
    ->addColumn('sex', '性别')
    ->addColumn('birthday', '生日')
    ->addColumn('reg_ip', '注册IP')
    ->addColumn('last_login', '最后登录时间')
    ->addColumn('last_ip', '最后登录 IP')
    ->addColumn('online_time', '在线时间')
    ->addColumn('right_button', '操作', 'btn')
    ->addRightButtons(['edit'=>['href'=>'/admin/user/adminedit/uid/__ID__'], 'delete' => ['data-tips' => '删除后无法恢复。','field'=>'uid']]) // 批量添加右侧按钮
    ->addTopButton('edit',['class'=>"btn btn-default",'title'=>'添加管理员','href'=>'/admin/user/adminedit','icon'=>'fa fa-plus-circle']) // 添加顶部按钮
    ->setRowList($data_list) // 设置表格数据
    ->setRowList($re) // 设置表格数据
    ->fetch();
  }



    /**
     * [admin 后台查看推广人数]
     * @Author   Jerry
     * @DateTime 2017-06-14T08:55:00+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
  public function user_list_coupon(){
      $map = $this->getMap();
      $order = $this->getOrder();



      $re = model('Base')->getpages('users_wx',['where'=>$map,

          'order'=>$order,
          'list_rows'=>$_GET['list_rows']
      ]);


     foreach ($re as $k => $v){

         $find = Db::name('users_coupon')->where('u_id',$v['id'])->count();
         $v['coupon_have'] =$find;
         $find = Db::name('users_coupon')->where('t_id',$v['id'])->count();
         $v['coupon_recommend'] =$find;
         $v['phone'] = decode($v['phone']);

         $v['name'] = urldecode($v['name']);
         $re[$k] = $v;

     }



      return $this->builder('table')
          ->setPageTitle('用户列表')
          ->setSearch(['id' => '用户id', 'name' => '姓名']) // 设置搜索参数
          ->setTableName('users_wx')
          ->setPrimaryKey('id')
          ->addOrder('id')
          ->addColumn('id', 'id')
          ->addColumn('name', '姓名')
          ->addColumn('phone', '用户手机')
          ->addColumn('gender', '性别', 'gender', '', ['未知','男', '女'])
          ->addColumn('coupon_have', '拥有优惠券数量')
          ->addColumn('coupon_recommend', '邀请人数')
          ->addColumn('right_button', '操作', 'btn')
          ->addTopButton('edit',['class'=>"btn btn-default",'title'=>'导出EXL','href'=>'/admin/user/exl_user','icon'=>'fa fa-plus-circle']) // 添加顶部按钮
          ->addRightButtons(['calendar' => ['icon'=>'fa fa-group','title'=>'查看邀请的用户','class'=>'btn btn-xs btn-default frAlert','href'=>'recommend/uid/__ID__']]) // 批量添加右侧按钮
          ->setRowList($re) // 设置表格数据
          ->fetch();



}

/*
 * 查看推广人数
 * */
public function recommend(){
    $id = input('uid');

    $find = Db::name('users_coupon')->where('t_id',$id)->field('u_id')->select();

    $arr  = array();
    foreach ($find as $k => $v){

        $arr[$k] = $v['u_id'];
    }


    $order = $this->getOrder();

    if(!empty($arr)){
        $map = 'id in  ('.implode(',',$arr).')';
    }else{
        $map = $this->getMap();
    }


    $re = model('Base')->getpages('users_wx',['where'=>$map,
        'order'=>$order,
        'list_rows'=>$_GET['list_rows']
    ]);

    foreach ($re as $k => $v){

        $find = Db::name('users_coupon')->where('u_id',$v['id'])->count();
        $v['coupon_have'] =$find;
        $find = Db::name('users_coupon')->where('t_id',$v['id'])->count();
        $v['coupon_recommend'] =$find;
        $v['phone'] = decode($v['phone']);
        $v['name'] = urldecode($v['name']);


        $re[$k] = $v;

    }

    return $this->builder('table')
        ->setPageTitle('用户列表')
        ->setSearch(['id' => '用户id', 'name' => '姓名']) // 设置搜索参数
        ->setTableName('users_wx')
        ->setPrimaryKey('id')
        ->addOrder('id')
        ->addColumn('id', 'id')
        ->addColumn('name', '姓名')
        ->addColumn('phone', '用户手机')
        ->addColumn('gender', '性别', 'gender', '', ['女','男', '未知'])
        ->addColumn('coupon_have', '拥有优惠券数量')
        ->addColumn('coupon_recommend', '邀请人数')
        ->addColumn('right_button', '操作', 'btn')
        ->addRightButtons(['calendar' => ['icon'=>'fa fa-group','title'=>'查看邀请的用户','class'=>'btn btn-xs btn-default frAlert','href'=>'recommend/uid/__ID__']]) // 批量添加右侧按钮
        ->setRowList($re) // 设置表格数据
        ->fetch();
}
/**
     * [express_realname 物流端实名认证]
     * @Author   Jerry
     * @DateTime 2017-09-11T15:52:19+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function express_realname(){
      $html =  $this->builder('table');
           //当前位置ID下面的广告例表
       $group = input('group')?input('group'):'news';
        $map = $this->getMap();
         $list_tab = [
            'news' => ['title' => '待审核', 'url' => url('admin/user/express_realname', ['group' => 'news'])],
            'finish' => ['title' => '已审核', 'url' => url('admin/user/express_realname', ['group' => 'finish'])],
            
            'notpass' => ['title' => '审核失败', 'url' => url('admin/user/express_realname', ['group' => 'notpass'])],
            'notreal' => ['title' => '未提交审核', 'url' => url('admin/user/express_realname', ['group' => 'notreal'])],
        ];
      switch ($group) {

        case 'news':
          $map['real_status'] = 1;
         $html = $html->addTopButton('edit',['icon'=>'fa fa-check','class'=>"btn btn-default",'form'=>'tableForm','class'=>'tPost btn btn-default','title'=>'通过审核','url'=>url('admin/api/express_real_access'),'href'=>"javascript:;"])->addRightButtons(['notpass'=>['icon'=>'fa fa-close','title'=>'拒绝通过','href'=>url('admin/user/notpass_real_express',['id'=>'__id__'])]]);
          break;
         case 'finish':
          $map['real_status'] = 2;
          break;
           case 'notpass':
          $map['real_status'] = '-1';
          $html = $html->addColumn('real_remark','失败原因');
          // show($map);
          break;
          case 'notreal':
          $map['real_status'] = '0';
          break;

      }
      $order = $this->getOrder();
      $data = $this->getbase->getdb('users_express')
                            ->order($order)
                            ->where($map)
                            ->paginate();
      $formatData = [];
      foreach ($data as  $v) {
        $v['phone'] = decode($v['phone']);
        $v['id_card'] = decode($v['id_card']);
        $formatData[] = $v;
      }
      // 分页数据
      $page = $data->render();
      
      return $html->setPageTitle('广告列表')
      ->setSearch(['id' => 'id', 'name' => '姓名']) // 设置搜索参数
      ->setTableName('users_express')
      ->setPrimaryKey('id')
      ->setTabNav($list_tab,  $group)
      ->addOrder('id')
      ->addColumn('id', 'id')
      ->addColumn('name', '姓名')
      ->addColumn('phone', '手机号')
      ->addColumn('avatarurl', '图相','picture')
      ->addColumn('create_time', '注册时间')
      ->addColumn('money', '当前余额')
      ->addColumn('frozen_money', '冻结金额')
      ->addColumn('id_card', '身份证号码')
      ->addColumn('id_card_face', '身份证正面','picture_path')
      ->addColumn('id_card_side', '身份证反面','picture_path')
      ->addColumn('id_card_hand', '手持身份证','picture_path')
      ->addColumn('realname', '真实姓名')
      // ->addColumn('status', '状态',"switch")
      ->addColumn('right_button', '操作', 'btn')
      ->setRowList($formatData) // 设置表格数据
      ->setPages($page) // 设置分页数据
      ->fetch();
      
    }
    /**
     * [notpass_real_express description]
     * @Author   Jerry
     * @DateTime 2017-09-11T18:55:32+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function notpass_real_express(){
      ##查出当前的信息
      if($remark = $this->getbase->getone('users_express',['where'=>['id'=>input('id')],'field'=>'real_remark'])){
        extract($remark);
      }
      return $this->builder('form')
      ->setUrl(url('admin/api/notpass_real_express'))
      ->addHidden('field','id')
      ->addHidden('gourl','admin/user/express_realname')
      ->addHidden('id',input('id'))
      ->addHidden('table','users_express')
      ->setPageTitle('拒绝原因')
      ->addTextarea('real_remark','拒绝原因', '',$real_remark)
      ->addValidate([
                  'real_remark'          => 'require',
                    ],[
                  'real_remark.require'  => '拒绝原因为必填',


                    ])
      ->fetch(); 
    }
    /**
     * [hotel_authen 酒店实名认证]
     * @Author   WuSong
     * @DateTime 2017-09-13T09:31:27+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
     public function hotel_authen(){
       $html = $this->builder('table');
       $group = input('group')?input('group'):'news';
       $map = $this->getMap();
       $list_tab = [
            'news' => ['title' => '待审核', 'url' => url('admin/user/hotel_authen', ['group' => 'news'])],
            'finish' => ['title' => '已审核', 'url' => url('admin/user/hotel_authen', ['group' => 'finish'])],
            'notpass' => ['title' => '审核失败', 'url' => url('admin/user/hotel_authen', ['group' => 'notpass'])],
            'notreal' => ['title' => '未提交审核', 'url' => url('admin/user/hotel_authen', ['group' => 'notreal'])],
      ];
      switch ($group) {

        case 'news':
          $map = "h.cash_status=1";
         $html = $html->addTopButton('edit',['icon'=>'fa fa-check','class'=>"btn btn-default",'form'=>'tableForm','class'=>'tPost btn btn-default','title'=>'通过审核','url'=>url('admin/api/hotel_authen'),'href'=>"javascript:;"])->addRightButtons(['notpass'=>['icon'=>'fa fa-close','title'=>'拒绝通过','href'=>url('admin/user/notpass_status_hotel',['id'=>'__id__'])]]);
          break;
         case 'finish':
          $map ='h.cash_status=2';
          break;
           case 'notpass':
          $map = 'h.cash_status=-1';
          break;
          case 'notreal':
          $map = 'h.cash_status=0';
          break;

      }
      $order = $this->getOrder();
      $data = $this->getbase->getdb('hotel')
                            ->alias('h')
                            ->join('hotel_authen ha','h.id=ha.hotel_id','left')
                            ->order($order)
                            ->where($map)
                            ->field('h.*,h.phone as hp,ha.*,h.create_date as hct,h.status as hs,ha.phone haphone,ha.name,ha.id_card,ha.bankname,ha.create_date,ha.open_name,ha.bank_id,ha.banktype,ha.hotel_id,ha.status_remark,h.id id')
                            ->paginate();
                            // show($data);
      $formatData = [];
      foreach ($data as  $v) {
        $arr= [$v['province'],$v['city'],$v['area'],$v['address']];
        $atr = implode('', $arr);
        $v['atr']=$atr;
        $v['haphone'] = decode($v['haphone']);
        $v['id_card'] = decode($v['id_card']);
        if($v['banktype']==0){
          $v['banktype']="对公"; 
        }else{
           $v['banktype']="对私";
        }
        // show($v);
        $formatData[] = $v;
      }
      // 分页数据
      $page = $data->render();
      
      return $html->setPageTitle('广告列表')
      ->setSearch(['id' => 'id', 'name' => '姓名']) // 设置搜索参数
      ->setTableName('hotel_authen')
      ->setPrimaryKey('id')
      ->setTabNav($list_tab,  $group)
      ->addOrder('id')
      ->addColumn('id', 'id')
      ->addColumn('accounts','账户')
      ->addColumn('haphone', '手机号')
      ->addColumn('atr','酒店地址')
      ->addColumn('hotel_name','酒店名称')
      ->addColumn('create_data', '注册时间')
      ->addColumn('id_card', '身份证号码')
      ->addColumn('bankname','银行')
      ->addColumn('open_name','开户人名称')
      ->addColumn('bank_id','银行卡号')
      ->addColumn('banktype','对公对私')
      ->addColumn('head', '头部照片','picture_path')
      ->addColumn('license', '营业执照','picture_path')
      ->addColumn('name', '真实姓名')
      ->addColumn('status_remark','失败原因')
      // ->addColumn('status', '状态',"switch")
      ->addColumn('right_button', '操作', 'btn')
      ->setRowList($formatData) // 设置表格数据
      ->setPages($page) // 设置分页数据
      ->fetch();

     }

     /**
      * [notpass_status_hotel description]
      * @Author   WuSong
      * @DateTime 2017-09-13T11:33:07+0800
      * @Example  eg:
      * @return   [type]                   [description]
      */
     public function notpass_status_hotel(){
      ##查出当前的信息
      if($remark = $this->getbase->getone('hotel_authen',['where'=>['hotel_id'=>input('id')],'field'=>'status_remark'])){
        extract($remark);
      }
      return $this->builder('form')
      ->addHidden('gourl','admin/user/hotel_authen')
      ->setUrl(url('admin/api/notpass_status_hotel'))
      ->addHidden('field','hotel_id')
      // ->addHidden('gourl','/admin/adv/lists/postion_id/'.input('postion_id'))
      ->addHidden('hotel_id',input('id'))
      ->addHidden('table','hotel_authen')
      ->setPageTitle('拒绝原因')
      ->addTextarea('status_remark','拒绝原因', '',$status_remark)
      ->addValidate([
                  'status_remark'          => 'require',
                    ],[
                  'status_remark.require'  => '拒绝原因为必填',


                    ])
      ->fetch(); 
    }

    /**
     * [hotelbackpass 酒店找回密码]
     * @Author   WuSong
     * @DateTime 2017-09-18T18:14:13+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function hotel_backpass(){
       $html = $this->builder('table');
       $group = input('group')?input('group'):'news';
       $map = $this->getMap();
       $list_tab = [
            'news' => ['title' => '待审核', 'url' => url('admin/user/hotel_backpass', ['group' => 'news'])],
            'finish' => ['title' => '已审核', 'url' => url('admin/user/hotel_backpass', ['group' => 'finish'])],
            
            'notpass' => ['title' => '审核失败', 'url' => url('admin/user/hotel_backpass', ['group' => 'notpass'])],
            // 'notreal' => ['title' => '已完成', 'url' => url('admin/user/hotel_backpass', ['group' => 'notreal'])],
      ];
      switch ($group) {

        case 'news':
          $map = "status=0";
         $html = $html->addTopButton('edit',['icon'=>'fa fa-check','class'=>"btn btn-default",'form'=>'tableForm','class'=>'tPost btn btn-default','title'=>'通过审核','url'=>url('admin/api/hotel_backpass'),'href'=>"javascript:;"])->addRightButtons(['notpass'=>['icon'=>'fa fa-close','title'=>'拒绝通过','href'=>url('admin/user/notpass_status_hotelbackpass',['id'=>'__id__'])]]);
          break;
         case 'finish':
          $map ='status=2';
          break;
           case 'notpass':
          $map = 'status=-1';
          // show($map);
          break;
          case 'notreal':
          $map = 'status=0';
          break;


      }
      $order = $this->getOrder();
      $data = $this->getbase->getdb('hotel_backpass_log')
                            ->order($order)
                            ->where($map)
                            ->paginate();

      // 分页数据
      $page = $data->render();
      return $html->setPageTitle('广告列表')
      ->setSearch(['id' => 'id', 'hotel_name' => '酒店名称']) // 设置搜索参数
      ->setTableName('hotel_backpass_log')
      ->setPrimaryKey('id')
      ->setTabNav($list_tab,  $group)
      ->addOrder('id')
      ->addColumn('id', 'id')
      ->addColumn('hotel_name','酒店名称')
      ->addColumn('phone', '手机号')
      ->addColumn('business','酒店营业执照')
      ->addColumn('create_date', '申请时间')
      ->addColumn('remark','失败原因')
      ->addColumn('right_button', '操作', 'btn')
      ->setRowList($data) // 设置表格数据
      ->setPages($page) // 设置分页数据
      ->fetch();

     }

    /**
     * [notpass_status_hotelbackpass 酒店申请拒绝]
     * @Author   WuSong
     * @DateTime 2017-09-18T10:06:15+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function notpass_status_hotelbackpass(){
      ##查出当前的信息
      if($remark = $this->getbase->getone('hotel_backpass_log',['where'=>['id'=>input('id')],'field'=>'remark'])){
        extract($remark);
      }
      return $this->builder('form')
      ->addHidden('gourl','admin/user/hotel_backpass')
      ->setUrl(url('admin/api/notpass_status_hotelbackpass'))
      ->addHidden('field','id')
      // ->addHidden('gourl','/admin/adv/lists/postion_id/'.input('postion_id'))
      ->addHidden('id',input('id'))
      ->addHidden('table','hotel_backpass_log')
      ->setPageTitle('拒绝原因')
      ->addTextarea('remark','拒绝原因', '',$status_remark)
      ->addValidate([
                  'remark'          => 'require',
                    ],[
                  'remark.require'  => '拒绝原因为必填',


                    ])
      ->fetch(); 
    }
    /**
     * [hotel_users 酒店用户列表]
     * @Author   WuSong
     * @DateTime 2017-10-09T11:10:56+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function hotel_users(){
      // $order = $this->getOrder();
      $data = DB::table(config('database.prefix').'hotel')
                    ->alias('h')
                    ->join(config('database.prefix').'hotel_authen ha','h.id = ha.hotel_id','LEFT')
                    ->field('h.*,ha.name,ha.phone hap')
                    ->where('cash_status','>',-2)
                    ->order('cash_status','desc')
                    ->select(); 
      $money_all = [];
      foreach ($data as $k => $v) {
          //组合地址
         $arr= [$v['province'],$v['city'],$v['area'],$v['address']];
         $data[$k]['hotel_address'] =implode('', $arr);
         //解析手机号
         $data[$k]['phone'] = decode($v['hap']);
         

         //统计条数
         $data[$k]['spread']= $this->getbase->getcount('hotel',['where'=>'h.id=ohs.hotel_id','alias'=>'h','join'=>[['qlbl_order_hotel_spread_log ohs','ohs.hotel_id=h.id']],'field'=>'ohs.hotel_id']);
         //统计总金钱
        
        


          if($v['hs']==1){
            $data[$k]['hs']='正常';
          }else{
            $data[$k]['hs']='不可用';          
          };
         
          if($v['cash_status']==0){
            $data[$k]['cash_status'] = '未审核';
          }else if($v['cash_status']==1){
            $data[$k]['cash_status'] = '已提交';
          }else if($v['cash_status']==2){
            $data[$k]['cash_status'] = '已审核';
          };

      }

      // 分页数据
      // $page = $data->render();
      return $this->builder('table')
      ->setPageTitle('酒店用户列表')
      ->setSearch(['id' => 'id', 'hotel_name' => '酒店名称']) // 设置搜索参数
      ->setTableName('hotel')
      ->setPrimaryKey('id')
      ->addOrder('id')
      ->addColumn('id', 'id')
      ->addColumn('hotel_name','酒店名称')
      ->addColumn('phone', '手机号')
      ->addColumn('name','酒店负责人')
      ->addColumn('hotel_address','酒店地址')
      ->addColumn('accounts','酒店账号')
      ->addColumn('money','账号可用金钱')
      ->addColumn('frozen_money','账号冻结金钱')
      ->addColumn('spread','推广单量')
      ->addColumn('spread_money','推广总收入')
      ->addColumn('head','头像','picture_path')
      ->addColumn('cash_status','通过审核状态')
      ->addColumn('hs','是否可用')
      ->addColumn('right_button', '操作', 'btn')
      ->addRightButtons([
                  'calendar' => ['icon'=>'fa fa-calendar','title'=>'银行卡信息','class'=>'btn btn-xs btn-default frAlert','url'=>url('admin/user/hotel_bank',['hotelid'=>"__id__"]),'href'=>'javascript:;'],
                  'detail' => ['icon'=>'fa fa-newspaper-o','title'=>'执照信息','class'=>'btn btn-xs btn-default frAlert','url'=>url('admin/user/hotel_business',['hotelid'=>"__id__"]),'href'=>'javascript:;'],
                  ])
      ->setRowList($data) // 设置表格数据
      ->fetch();
    }

    /**
     * [hotel_bank 酒店银行卡信息]
     * @Author   WuSong
     * @DateTime 2017-10-16T15:39:57+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function hotel_bank(){
       $hotelid = (int)input('hotelid');

          $data = DB::table(config('database.prefix').'hotel_authen')
            ->where('hotel_id',$hotelid)
            ->select();

            foreach ($data as $k => $v) {
              if($v['banktype']==0){
                $data[$k]['banktype']='对公';
              }else{
                $data[$k]['banktype']='对私';
              };
              $data[$k]['id_card']= decode($v['id_card']);
            }
        return $this->builder('table')
                ->setPageTitle('银行卡信息')
                ->hideCheckbox()
                ->addColumn('bankname','开户银行')
                ->addColumn('bank_id','银行卡号')
                ->addColumn('id_card','身份证号')
                ->addColumn('open_name','开户人')
                ->addColumn('banktype','对公/对私')
                ->setRowList($data) // 设置表格数据
                ->fetch(); 
    }

    /**
     * [hotel_business 执照信息]
     * @Author   WuSong
     * @DateTime 2017-10-16T15:40:32+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function hotel_business(){

          $hotelid = (int)input('hotelid');

          $data = DB::table(config('database.prefix').'hotel')
                    ->alias('h')
                    ->join(config('database.prefix').'hotel_authen ha','h.id = ha.hotel_id','LEFT')
                    ->field('ha.id,ha.business,h.license,h.create_date hc,ha.create_date')
                    ->where('hotel_id',$hotelid)
                    ->select(); 
        return $this->builder('table')
                ->setPageTitle('营业执照信息')
                ->hideCheckbox()
                ->addColumn('business','营业执照号码')
                ->addColumn('license','营业执照','picture_path')
                ->addColumn('hc','酒店申请时间')
                ->addColumn('create_date', '认证申请时间')
                ->setRowList($data) // 设置表格数据
                ->fetch(); 
    }

    /**
     * [express_users 物流人员列表]
     * @Author   WuSong
     * @DateTime 2017-10-09T14:08:42+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function express_users(){
        // $order = $this->getOrder();
        $data = DB::table(config('database.prefix').'users_express')
                    ->where('real_status','>',-1)
                    ->order('real_status','desc')
                    ->select(); 
        foreach ($data as $k => $v) {
            //取件查询
            $data[$k]['take_info']= $this->getbase->getcount('express_order_users',['where'=>['express_id'=>$v['id'],'type'=>1,'status'=>4]]);
            //送件查询
            $data[$k]['give_info']= $this->getbase->getcount('express_order_users',['where'=>['express_id'=>$v['id'],'type'=>2,'status'=>4]]);
            //异常单量
            $data[$k]['abnormal_info']= $this->getbase->getcount('express_order_users_log',['where'=>['express_id'=>$v['id'],'log'=>'物流员拒绝接单','change_status'=>1]]);
            //完成单量
            $data[$k]['over_info']= $this->getbase->getcount('express_order_users',['where'=>['express_id'=>$v['id'],'status'=>4]]);
            //解析手机号，身份证号
            $data[$k]['phone'] = decode($v['phone']);
            $data[$k]['id_card'] = decode($v['id_card']);

           if($v['status']==1){
            $data[$k]['status']='开工';
           }else if($v['status']==-1){
            $data[$k]['status'] ='关工';
           }else if($v['status']==-2){
            $data[$k]['status']='限制';
           };

           if($v['real_status']==0){
            $data[$k]['real_status']='未实名';
           }else if($v['real_status']==1){
            $data[$k]['real_status']='申请中';
           }else if($v['real_status']==2){
            $data[$k]['real_status']='已实名';
           };
           //统计条数
           $data[$k]['spread']=  Db::table(config('database.prefix').'order_spread_log')
                                ->alias('os')
                                ->where('os.express_id',$v['id'])
                                ->where('os.share_proportion','10%')
                                ->count();
          //统计推广总收入
           $data[$k]['spread_money']=  Db::table(config('database.prefix').'order_spread_log')
                                ->alias('os')
                                ->where('os.express_id',$v['id'])
                                ->where('os.share_proportion','10%')
                                ->sum('os.money');
        }

        // 分页数据
        // $page = $data->render();
        return $this->builder('table')
        ->setPageTitle('物流用户列表')
        ->setSearch(['id' => 'id', 'realname' => '物流人员名称']) // 设置搜索参数
        ->setTableName('users_express')
        ->setPrimaryKey('id')
        ->addOrder('id')
        ->addColumn('id', 'id')
        ->addColumn('realname','姓名')
        ->addColumn('phone','手机')
        ->addColumn('money','账号余额')
        ->addColumn('frozen_money','冻结余额')
        ->addColumn('id_card','身份证号')
        // ->addColumn('cardid','银行卡号')
        // ->addColumn('bankname','所属银行')
        // ->addColumn('cardtype','卡种')
        ->addColumn('take_info','取件量')
        ->addColumn('give_info','送件量')
        ->addColumn('abnormal_info','异常单量')
        ->addColumn('over_info','完成单量')
        ->addColumn('spread','推广次数')
        ->addColumn('spread_money','推广收入')
        ->addColumn('create_time','注册时间')
        ->addColumn('real_status','是否认证')
        ->addColumn('status','状态')
        ->addColumn('right_button', '操作', 'btn')
        ->addRightButtons([
                    'calendar' => ['icon'=>'fa fa-calendar','title'=>'银行卡信息','class'=>'btn btn-xs btn-default frAlert','url'=>url('admin/user/express_bank',['expressid'=>"__id__"]),'href'=>'javascript:;'],
                    ])
        ->setRowList($data) // 设置表格数据
        ->fetch();
      }

      /**
       * [express_bank 物流人员银行卡信息]
       * @Author   WuSong
       * @DateTime 2017-10-16T14:51:23+0800
       * @Example  eg:
       * @return   [type]                   [description]
       */
      public function express_bank(){
        $expressid = (int)input('expressid');

          $data = DB::table(config('database.prefix').'users_express_bankcard')
            ->where('express_id',$expressid)
            ->select();
        return $this->builder('table')
                ->setPageTitle('银行卡信息')
                ->hideCheckbox()
                ->addColumn('bankname', '所属银行')
                ->addColumn('cardid', '银行卡号')
                ->addColumn('cardtype', '银行卡种类')
                ->setRowList($data) // 设置表格数据
                ->fetch(); 


      }

    /**

     * excel表格导出

     * @param string $fileName 文件名称

     * @param array $headArr 表头名称

     * @param array $data 要导出的数据

     * @author static7  */

    public function excelExport($ame , $headArr = [], $data = []) {

        $fileName = $ame."_" . date("Y_m_d", time()) . ".xls";

        $objPHPExcel = new \PHPExcel();

        $objPHPExcel->getProperties();

        $key = ord("A"); // 设置表头

        foreach ($headArr as $v) {

            $colum = chr($key);

            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($colum . '1', $v);

            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($colum . '1', $v);

            $key += 1;

        }

        $column = 2;

        $objActSheet = $objPHPExcel->getActiveSheet();

        foreach ($data as $key => $rows) { // 行写入

            $span = ord("A");

            foreach ($rows as $keyName => $value) { // 列写入

                $objActSheet->setCellValue(chr($span) . $column, $value);

                $span++;

            }

            $column++;

        }

        $fileName = iconv("utf-8", "gb2312", $fileName); // 重命名表

        $objPHPExcel->setActiveSheetIndex(0); // 设置活动单指数到第一个表,所以Excel打开这是第一个表

        header('Content-Type: application/vnd.ms-excel');

        header("Content-Disposition: attachment;filename=$fileName");

        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        $objWriter->save('php://output'); // 文件通过浏览器下载

        exit();

    }
    


    /**

     * tp5 使用excel导出

     * @param

     * @author staitc7  * @return mixed

     */
    public function exl_user() {

        $name='用户推广导出';
        //导出内容
        $header=['用户id','用户名称','用户手机号','用户注册地址','推广人名称','推广人id'];


        $wx = Db::name('users_wx')->select();


        $array = array();
        $i = 0;
        foreach ($wx as $k => $v){

            $ft = Db::name('users_coupon')->where('u_id',$v['id'])->find();

            $wxt = Db::name('users_wx')->where('id',$ft['id'])->find();

            $data = array($v['id'],urldecode($v['name']),decode($v['phone']),$ft['address'],urldecode($wxt['name']),$wxt['id']);

            $array[$i] = $data;




            $i++;
        }

        $this->excelExport($name,$header,$array);

    }





}
