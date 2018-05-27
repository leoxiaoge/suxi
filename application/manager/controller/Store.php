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
class Store extends ManagerBase
{

	public function _initialize()
    {
     	parent::_initialize();  
     	
    }
    /**
     * [postion 挂位]
     * @Author   Jerry
     * @DateTime 2017-08-21T09:29:33+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function postion(){
        ##当前门店的挂位
        $store_id = session('suinfo')['store_id'];
        $keyword = trim(addslashes(input('keyword')));
        $postion_count = $this->getbase->getone('store_setting',['where'=>['store_id'=>$store_id],'field'=>'postion_count']);
        ##当前门店所有已被分配的挂号
        if($keyword){
            $where = ['store_id'=>$store_id,'status'=>'1','order_number'=>$keyword];
        }else{
            $where = ['store_id'=>$store_id,'status'=>'1'];

        }
        $all = $this->getbase->getall('order_pendant',['where'=>['store_id'=>$store_id,'status'=>'1']]);
        $postion = [];
       foreach ($all as $k => $v) {
           $postion[$v['hang_number']] = $v;
       }


       $this->assign($postion_count);
       $this->assign('postion',$postion);

        return $this->fetch();
    }
    /**
     * [setting 门店基本设置]
     * @Author   Jerry
     * @DateTime 2017-08-04T18:02:38+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
  public function setting(){
    ##配置项
    $suinfo = session('suinfo');
    $postion_count = $this->getbase->getone('store_setting',['where'=>['store_id'=>$suinfo['store_id']]]);
    // show($postion_count);
    extract($postion_count);
    return $this->builder('form')
    ->setTemplate(APP_PATH. 'manager/view/public/template_form.html')
    ->setUrl(url('manager/api/editsetting'))
    ->addHidden('store_id',$suinfo['store_id'])
    ->addHidden('last_edit_store_user',$suinfo['id'])
    ->setPageTitle('门店设置')
    ->addNumber('postion_count', '挂位总数（挂衣服总数量）', '',$postion_count?$postion_count:100)
    ->addRadio('is_open_nitify_phone', '是否开启短信通知服务', '',['关闭','开启'],$is_open_nitify_phone)
    ->addText('notify_phone', '通知号码', '下单付款后通知的电话号码',$notify_phone)
    ->fetch(); 

  }
   /**
     * [users_express 指派物流人员]
     * @Author   WuSong
     * @DateTime 2017-10-26T17:04:09+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
public function users_express(){

    ##物流人员
    $data = $this->getbase->getall('users_express',['where'=>['status'=>1]]);

    return $this->builder('form')
    ->setTemplate(APP_PATH. 'manager/view/public/template_form.html')
    ->setUrl(url('admin/ajax/saveconfig'))
    ->setPageTitle('物流指定人员设置')
    ->addRadio('open_single_express', '是否开启单物流模式', '单物流模式，直接把订单指派给物流员，不会有派物流环节',['关闭','开启'],getset('open_single_express'))
    ->addSelect('express_id', '指定人员', '', formatArr($data,'id','realname'),getset('express_id'))
    ->fetch();
    }
  


       
    }
    
 // getset('receive_users')