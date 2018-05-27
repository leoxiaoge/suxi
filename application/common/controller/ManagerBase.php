<?php
/*
+--------------------------------------------------------------------------
|   商家门店平台管理中心
|   ========================================
+---------------------------------------------------------------------------
 */
namespace app\common\controller;
use app\common\controller\Base;
class ManagerBase extends Base
{

  public  $suinfo = "";
  public function _initialize() {
    parent::_initialize();
    if(!session('suid')){
    	$this->redirect(url('manager/user/login',['gourl'=>encode(url('manager/index/index'))]));
    }else{
      $this->suinfo = session('suinfo');
    	$this->assign('suinfo',session('suinfo'));
      $this->assign('domainUrl',strtolower($this->request->module().'/'.$this->request->controller().'/'.$this->request->action()));
      $uid = session('suid');


      ##开始权限控制
      #当前用户的信息
      $userinfo = $this->getbase->getone('store_users',['where'=>['id'=>$uid],'field'=>'store_department_id']);
      ##当前角色的职位
      $departJob = $this->getbase->getone('store_department',['where'=>['id'=>$userinfo['store_department_id']]]);
      $this->assign('departJob',$departJob);
      #当前用户的权限
      $auth = $this->getbase->getall('store_auth',['where'=>['store_department_id'=>$userinfo['store_department_id']]]);
      $nowUrl = strtolower($this->request->module().'/'.$this->request->controller().'/'.$this->request->action());
      $auths = [];
      foreach ($auth as $k => $v) {
        $auths[] = $v['url'];
      }
      $superAdmin = [8,14];##超级管理员权限ID号
      // if(!in_array($uid, $superAdmin)){
      //   if(!in_array($nowUrl, $auths)){
      //     $this->error('没有操作权限');
      //   }
      // }
      

      ##权限菜单
      $manager_left_menu = config('manager_left_menu');
      
      ##暂时只处理两级
      // if(!in_array($uid, $superAdmin)){
      //   foreach ($manager_left_menu as $k => $v) {
      //      if(in_array(trim($v['url'],'/'),$auths)){
      //           if(is_array($v['child'])&&count($v['child'])>0){
      //               foreach ($v['child'] as $kc => $vc) {
      //                   if(!in_array(trim($vc['url'],'/'),$auths)){
      //                       unset($manager_left_menu[$k]['child'][$kc]);
      //                   }
      //               }
      //           }
      //      }else{
      //         unset($manager_left_menu[$k]);
      //      }
      //   }
      // }
      // show($manager_left_menu);
      

      $this->assign('manager_left_menu',$manager_left_menu);

      // show(strtolower($this->request->module().'/'.$this->request->controller().'/'.$this->request->action()));
      // die;
    }
    //商家门店相关权限处理
    //
     // $this->powerlogin();##是否登陆
   
  }
 

 

}
