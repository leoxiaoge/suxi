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
|   权限管理
+---------------------------------------------------------------------------
 */
namespace app\admin\controller;
use app\common\controller\AdminBase;
class Auth extends AdminBase
{
 /**
  * [index 用户管理]
  * @return [type] [description]
  */
  public function edit(){
    $group_id = (int)input('group_id');
    if(in_array($group_id, config('super_manager'))) $this->error('超级管理员不用添加权限');
    if($group_id<1){
      $this->error('没有指定用户组');
    }
    $groupInfo = model('base')->getone('users_group',['where'=>['group_id'=>$group_id]]);##当前组信息
    $authGroup = model('base')->getall('auth_access',['where'=>['group_id'=>$group_id]]);##当前组权限
    $authGroups = [];##组成一维数组
    foreach ($authGroup as  $v) {
      if($v['extra']){
        $authGroups[] = trim($v['extra'],'/');
      }else{
        $authGroups[] = $v['module'].'/'.$v['controller'].'/'.$v['action'];
      }
    }
    // show($authGroups);
    // $topmenuname = $data['topmenuname']?$data['topmenuname']:"home_action";
    //菜单
    $defaultmenu = config('adminmenu');
    $Models = finddirfromdir(APP_PATH."../application");
    $no_join = ['ajax','index','asset','common','post','ucenter','admin'];
    //模型
    // if($topmenuname=="admin_content_model"){
    //     foreach ($Models as $k => $v) {
    //       $path = APP_PATH."../application/".$v."/menu.php";
    //       if(!in_array($v, $no_join)&&file_exists($path)){
    //         $menu= include($path);
    //         $defaultmenu['adminmenu']['child'][]=$menu['adminmenu'];
            
    //       }
    //   }
    // }
    $this->assign('defaultmenu',$defaultmenu);
    $this->assign('groupInfo',$groupInfo);
    $this->assign('authGroups',$authGroups);


    return $this->fetch('admin/auth/edit');
  }

  
  

}
