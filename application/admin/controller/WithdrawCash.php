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
  提现申请
+---------------------------------------------------------------------------
 */
namespace app\admin\controller;
use app\common\controller\AdminBase;
use think\Db;
class WithdrawCash extends AdminBase
{
  public function lists(){

    $html = $this->builder('table');
    $group = input('group')?input('group'):'news';
    $list_tab = [
            'news' => ['title' => '新申请', 'url' => url('admin/WithdrawCash/lists', ['group' => 'news'])],
            'access' => ['title' => '审核通过', 'url' => url('admin/WithdrawCash/lists', ['group' => 'access'])],
            'success' => ['title' => '已打款', 'url' => url('admin/WithdrawCash/lists', ['group' => 'success'])],
            'pass' => ['title' => '申请被拒', 'url' => url('admin/WithdrawCash/lists', ['group' => 'pass'])],
        ];
 
     $map = $this->getMap();
       switch ($group) {
         case 'news':
            $map = "uwc.status = 1";
            $html = $html->addTopButtons(['accept' => ['id' => 'accept','title'=>'通过审核','class'=>'btn  btn-default tPost','icon'=>'ti-truck icon-lg','href'=>'javascript:;','url'=>url('admin/api/withdrawcashaccess'),'form'=>'tableForm']])->addRightButtons(['notpass'=>['icon'=>'fa fa-close','title'=>'拒绝通过','href'=>url('admin/WithdrawCash/notpass_withdraw_express',['id'=>'__id__'])]]);
           break;
            case 'access':
              $map = "uwc.status = 2";
              $html = $html->addTopButtons(['accept' => ['id' => 'accept','title'=>'完成打款','class'=>'btn  btn-default tPost','icon'=>'ti-truck icon-lg','href'=>'javascript:;','url'=>url('admin/api/withdrawcashsuccess'),'form'=>'tableForm']]);
           break;
            case 'success':
            $map = "uwc.status = 3";
           break;
            case 'pass':
            $map = "uwc.status = -1";
             $html = $html->addTopButtons(['accept' => ['id' => 'accept','title'=>'重新通过','class'=>'btn  btn-default tPost','icon'=>'ti-truck icon-lg','href'=>'javascript:;','url'=>url('admin/api/withdrawcashaccess'),'form'=>'tableForm']]);
           break;
       }
    // $order = $this->getOrder();
    $data = $this->getbase->getdb('express_withdraw_cash')
                          ->alias('uwc')
                          // ->order($order)
                          ->join([['users_express u','uwc.express_id=u.id']])
                          ->join([['users_express_bankcard ueb','ueb.express_id=u.id']])
                          ->where($map)
                          ->field('uwc.id id,u.realname as realname,ueb.cardid bankcard,uwc.money money,uwc.create_date create_date,uwc.remark')
                          ->paginate();
                          // show($data);
    // 分页数据
    $page = $data->render();
    return $html->setPageTitle('提现申请列表')
    ->setTabNav($list_tab,  $group)
    // ->setSearch(['express_id' => '用户id', 'id' => '申请id']) // 设置搜索参数
    ->setTableName('adv')
    ->setPrimaryKey('id')
    // ->addOrder('id')
    ->addColumn('id', 'id')
    ->addColumn('realname', '真实姓名')
    ->addColumn('create_date', '申请时间')
    ->addColumn('money', '申请金额')
    ->addColumn('bankcard', '转帐卡号')
    ->addColumn('remark', '备注原因(失败原因)')
    ->addColumn('right_button', '操作', 'btn')

    ->setRowList($data) // 设置表格数据
    ->setPages($page) // 设置分页数据
    ->fetch();
      
  }
    /**
     * [notpass_with_express 个人提现被拒]
     * @Author   Jerry
     * @DateTime 2017-09-19T17:00:09+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function notpass_withdraw_express(){
      ##查出当前的信息
      if($remark = $this->getbase->getone('express_withdraw_cash',['where'=>['id'=>input('id')],'field'=>'remark'])){
        extract($remark);
      }
      return $this->builder('form')
      ->setUrl(url('admin/api/notpass_withdraw_express'))
      ->addHidden('field','id')
      ->addHidden('gourl','admin/WithdrawCash/lists')
      ->addHidden('id',input('id'))
      ->addHidden('table','express_withdraw_cash')
      ->setPageTitle('拒绝原因')
      ->addTextarea('remark','拒绝原因', '',$remark)
      ->addValidate([
                  'remark'          => 'require',
                    ],[
                  'remark.require'  => '拒绝原因为必填',


                    ])
      ->fetch(); 
    }

    /**
     * [notpass_withdraw_hotel 酒店提现被拒]
     * @Author   Jerry
     * @DateTime 2017-09-19T18:00:02+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function notpass_withdraw_hotel(){
      ##查出当前的信息
      if($remark = $this->getbase->getone('express_withdraw_cash',['where'=>['id'=>input('id')],'field'=>'remark'])){
        extract($remark);
      }
      return $this->builder('form')
      ->setUrl(url('admin/api/notpass_withdraw_hotel'))
      ->addHidden('field','id')
      ->addHidden('gourl','admin/WithdrawCash/hotelcash')
      ->addHidden('id',input('id'))
      ->addHidden('table','hotel_withdraw_cash')
      ->setPageTitle('拒绝原因')
      ->addTextarea('remark','拒绝原因', '',$remark)
      ->addValidate([
                  'remark'          => 'require',
                    ],[
                  'remark.require'  => '拒绝原因为必填',


                    ])
      ->fetch(); 
    }
  /**
   * [hotelcash 酒店提现审核]
   * @Author   WuSong
   * @DateTime 2017-09-14T11:16:52+0800
   * @Example  eg:
   * @return   [type]                   [description]
   */
  public function hotelcash(){
    $html = $this->builder('table');
    $group = input('group')?input('group'):'news';
    $list_tab = [
            'news' => ['title' => '新申请', 'url' => url('admin/WithdrawCash/hotelcash', ['group' => 'news'])],
            'access' => ['title' => '审核通过', 'url' => url('admin/WithdrawCash/hotelcash', ['group' => 'access'])],
            'success' => ['title' => '已打款', 'url' => url('admin/WithdrawCash/hotelcash', ['group' => 'success'])],
            'pass' => ['title' => '申请被拒', 'url' => url('admin/WithdrawCash/hotelcash', ['group' => 'pass'])],
        ];
 
     $map = $this->getMap();
       switch ($group) {
         case 'news':
            $map['status'] = 1;
            $html = $html->addTopButtons(['accept' => ['id' => 'accept','title'=>'通过审核','class'=>'btn  btn-default tPost','icon'=>'ti-truck icon-lg','href'=>'javascript:;','url'=>url('admin/api/hotelwithdrawcashaccess'),'form'=>'tableForm']])->addRightButtons(['notpass'=>['icon'=>'fa fa-close','title'=>'拒绝通过','href'=>url('admin/WithdrawCash/notpass_withdraw_hotel',['id'=>'__id__'])]]);
           break;
            case 'access':
              $map['status'] = 2;
              $html = $html->addTopButtons(['accept' => ['id' => 'accept','title'=>'已打款','class'=>'btn  btn-default tPost','icon'=>'ti-truck icon-lg','href'=>'javascript:;','url'=>url('admin/api/hotelwithdrawcashsuccess'),'form'=>'tableForm']]);
           break;
            case 'success':
              $map['status'] = 3;
           break;
            case 'pass':
              $map['status'] = -1;

           break;
       }
    $order = $this->getOrder();
    $data = $this->getbase->getdb('hotel_withdraw_cash')
                          ->order($order)
                          ->where($map)
                          ->paginate();
    // 分页数据
    $page = $data->render();
    return $html->setPageTitle('酒店提现申请列表')
    ->setTabNav($list_tab,  $group)
    ->setSearch(['hotel_id' => '用户id', 'id' => '申请id']) // 设置搜索参数
    ->setTableName('adv')
    ->setPrimaryKey('id')
    ->addOrder('id')
    ->addColumn('id', 'id')
    ->addColumn('hotel_id', '用户id')
    ->addColumn('create_date', '申请时间')
    ->addColumn('money', '申请金额')
    ->addColumn('bank_id', '转帐卡号')
    ->addColumn('right_button', '操作', 'btn')
    ->addColumn('remark','备注/失败原因')
    ->setRowList($data) // 设置表格数据
    ->setPages($page) // 设置分页数据
    ->fetch();
      
  }







}
