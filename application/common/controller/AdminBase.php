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
namespace app\common\controller;
use app\common\controller\Base;
class AdminBase extends Base
{
  public function _initialize() {
    parent::_initialize();

    if($userinfo = \app\common\auth\Auth::Auth('admin')->getAdminInfo()){
    	$this->assign('userinfo',$userinfo);
    }else{
        gourl(url('ucenter/admin/login'));
    }

      #当前用户的权限
          // $auth = $this->getbase->getall('store_auth',['where'=>['store_department_id'=>$userinfo['store_department_id']]]);
          // $nowUrl = strtolower($this->request->module().'/'.$this->request->controller().'/'.$this->request->action());
          // $auths = [];
          // foreach ($auth as $k => $v) {
          //   $auths[] = $v['url'];
          // }
          // if($uid!=1){
          //   if(!in_array($nowUrl, $auths)){
          //     $this->error('没有操作权限');
          //   }
          // }
          

          ##权限菜单
          $manager_left_menu = config('manager_left_menu');
          // show()
          ##暂时只处理两级
          // if($uid!=1){
          //   foreach ($manager_left_menu as $k => $v) {
          //      if(in_array(trim($v['url'],'/'),$auths)){
          //           if(is_array($v['child'])&&count($v['child'])>0){
          //               foreach ($v['child'] as $kc => $vc) {
          //                   if(!in_array(trim($vc['url'],'/'),$auths)){
          //                     // show($vc['url']);
          //                     // show($auths);
          //                       unset($manager_left_menu[$k]['child'][$kc]);
          //                   }
          //               }
          //           }
          //      }else{
          //         unset($manager_left_menu[$k]);
          //      }
          //   }
          // }
       
  }

}
