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
namespace app\index\controller;
use think\Validate;
use app\common\controller\Base;
use app\common\func\thinkask;

class Api extends Base{
	public function _initialize(){
    parent::_initialize();
  }



    public function leaving(){
    	if($this->request->isPost()){
    		$data['name'] = addslashes(input('post.name'));
    		$data['email'] = addslashes(input('post.email'));
    		$data['phone'] = addslashes(input('post.phone'));
    		$data['message'] = addslashes(input('post.message'));
    		

    		$rule = [
		          'name|姓名' =>'require|chs',
		          'phone|电话号码' =>'require|max:11|number',
		          'email|邮箱' =>'require|email',
		          'message|留言' => 'require',
		          

		      ];
		      $validate = new Validate($rule);
		      $result   = $validate->check($data);
		        if(!$result){
		          return returnJson(1,$validate->getError());
		        }
		        $content = "客户姓名:".$data['name']."<br/>"."客户邮箱:".$data['email']."<br/>"."客户电话:".$data['phone']."<br/>"."留言内容:".$data['message'];



    		 if($this->getbase->getadd('index_message',$data)){
    		 	$re = send_mail(getset('receive_email'), ['subject'=>'官网留言，请及时处理','content'=>$content]);
    		 }
    	
			return returnJson('0','留言成功，我们会在第一时间联系您！','index/index/index');




		}
	}
}
