<?php
/*
+--------------------------------------------------------------------------
|  /门店管理员
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
class Ucenter extends ManagerBase
{

	public function _initialize()
    {
     	parent::_initialize();  
     	
    }
  
    public function changepwd(){
     if(!session('suid')){
    		$this->redirect(url('manager/user/login',['gourl'=>encode(url('manager/index/index'))]));
   		}
    	return $this->builder('form')
	    ->setTemplate(APP_PATH. 'manager/view/public/template_form.html')
	    ->setUrl(url('manager/api/changepwd'))
	    // ->addHidden('store_id',$suinfo['store_id'])
	    // ->addHidden('last_edit_store_user',$suinfo['id'])
	    ->setPageTitle('修改密码')
	    ->addPassword('old_pwd', '原始密码', '')
	    ->addPassword('pwd', '新密码', '')
	    ->addPassword('confirm_pwd', '新密码', '')
	    ->hideBtn('back')
	    ->fetch(); 
    }








}
