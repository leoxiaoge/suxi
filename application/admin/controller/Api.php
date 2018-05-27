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
class Api extends AdminBase {
	/**
	 * [addadmin 添加管理员]
	 * @Author   Jerry
	 * @DateTime 2017-06-14T11:23:47+0800
	 * @Example  eg:
	 * @return   [type]                   [description]
	 */
	public function addadmin(){
		if($this->request->isPost()){
			// $this->success('dna');
			if(!input('user_name')) return returnJson('1','用户名不能为空');
			$uid = (int)$_POST['uid'];

			//当前用户是否存在
			if($uid<1){
				//新加用户
				if(!input('password')) return returnJson('1','密码不能为空');
				if(model('base')->getcount('admin',['where'=>['user_name'=>trim(input('user_name'))]])>0) return returnJson('1','管理员已存在'); 
				if(model('base')->getcount('admin',['where'=>['email'=>trim(input('email'))]])>0) return returnJson('1','管理员邮箱已存在'); 
				$data = $_POST;
				unset($data['uid']);
				$data['reg_time'] = time();
				$data['salt'] = rand_str(4);
       			$data['password'] = encode_pwd($password,'adminprs');
       			if($re = model('base')->getadd('admin',$data)){
       				return returnJson(0,'',$data['gourl'],$res);
       			}else{
       				// dump($re);
       				return returnJson(1);
       			}
			}else{
				##查出当前的用户
				#密码如果存在就是修改，如果不存在就不改。
				$adminInfo = model('base')->getone('admin',['where'=>['uid'=>$uid]]);
				$data = $_POST;
				$data['password'] = $data['password']?encode_pwd($data['password'],$adminInfo['salt']):$adminInfo['password'];
				model('base')->getedit('admin',['where'=>['uid'=>$uid]],$data);
				return returnJson(0,'',$data['gourl']);

			}
		}
	}
	/**
	 * [auth 授权管理]
	 * @Author   Jerry
	 * @DateTime 2017-06-15T12:16:52+0800
	 * @Example  eg:
	 * @return   [type]                   [description]
	 */
	public function rbacauth(){
		if($this->request->isPost()){
			if(!$_POST['auth']) returnJson('1','请先选择权限');
			foreach ($_POST['auth'] as $k => $v) {
				$url = trim($v,'/');
				if($url){
					$urlAttr = explode("/", $url);
					model('base')->getadd('auth_access',['group_id'=>$_POST['group_id'],'module'=>$urlAttr[0],'controller'=>$urlAttr[1],'action'=>$urlAttr[2],'extra'=>count($urlAttr)>3?$url:null]);
					// show($urlAttr);
				}
			}
			return returnJson('0','授权成功',url('admin/user/group'));
			// show($_POST);
		}
	}
	/**
	 * [edithotel 编辑酒店 ]
	 * @Author   Jerry
	 * @DateTime 2017-09-06T09:52:54+0800
	 * @Example  eg:
	 * @return   [type]                   [description]
	 */
	public function edithotel(){
		if($this->request->isPost()){
			$data = input();
			if(!$data['phone']||!$data['password']) $this->error('用户名或者密码不能为空');
			$data['password'] = encode($data['password']); 
			$data['phone'] = encode($data['phone']);
			$data['create_date'] = date('Y-m-d H:i:s');
			// $data['uid'] = $data['uid'];
			
			if($this->getbase->getadd('hotel',$data)){
				return returnJson('0','操作成功',url('admin/hotel/lists'));
			}else{
				return returnJson('1','操作失败');
			}
			// show($data);
		}
	}
	/**
	 * [withdrawcashaccess 提现审核通过]
	 * @Author   Jerry
	 * @DateTime 2017-09-08T18:01:03+0800
	 * @Example  eg:
	 * @return   [type]                   [description]
	 */
	public function withdrawcashaccess(){
		if($this->request->isPost()){
			$data = input();
			if(is_array($data['ids'])){
				$idsstr = implode(",", $data['ids']);
				if(false!==$this->getbase->getedit('express_withdraw_cash',['where'=>['id'=>['in',$idsstr]]],['status'=>'2'])){

					foreach ($data['ids'] as $v) {
						##写入日志
			          $log = [
			            'withdraw_cash_id'=>$v,
			            'create_time'=>date('Y-m-d H:i:s'),
			            'log'=>'提现审核通过',
			            'remarks'=>'申请状态改为2',
			            'admin_id'=>cookie('admin_uid'),

			          ];
			          $this->getbase->getadd('express_withdraw_cash_through_log',$log);
					}

					 ##发短信
			          if(getset('withdraw_success_sns')=='Y'){
			          	 $withdraw_info = $this->getbase->getone('express_withdraw_cash',['where'=>['id'=>$v],'field'=>'express_id,money']);
                  		$realname = $this->getbase->getone('users_express',['where'=>['id'=>$withdraw_info['express_id']],'field'=>'realname,phone']);
			          	 if($real_phone = decode($realname['phone'])){
			          	 	send_phone($real_phone,[$realname['realname'],$withdraw_info['money']],'44827');
			          	 }
			          	 
			          }
			          ##发短信
					 


					return returnJson(0,'审核成功');
				}else{
					return returnJson(1,'审核失败，请稍后再试');
				}
			}else{
				return returnJson(1,'选择要审核的内容');
			}
			
		}
	}
	/**
	 * [notpass_withdraw_hotel 酒店申请被拒绝]
	 * @Author   Jerry
	 * @DateTime 2017-09-19T18:02:15+0800
	 * @Example  eg:
	 * @return   [type]                   [description]
	 */
	public function notpass_withdraw_hotel(){
		if($this->request->isPost()){
	            $data = $this->request->param();
	             if($data['validate']) $this->validate($data['validate'],'',$data,'api');
	            if($data[$data['field']]){
	                $where = "{$data['field']}='{$data[$data['field']]}'";
	                $id = $data[$data['field']];
	                unset($data[$data['field']]);
	                if(false!==model('Base')->getedit($data['table'],['where'=>$where],['status'=>'-1','remark'=>$data['remark']])){
	                	##写入日志
					         ##写入日志
						          $log = [
						            'withdraw_cash_id'=>$id,
						            'create_time'=>date('Y-m-d H:i:s'),
						            'log'=>'提现金额被拒，原因:'.$data['remark'],
						            'remarks'=>'申请状态改为-1',
						            'admin_id'=>cookie('admin_uid'),
						          ];
					          $this->getbase->getadd('hotel_withdraw_cash_log',$log);

					          ##发短信
					          if(getset('withdraw_error_sns_hotel')=='Y'){
					          	##提现的相关信息
					          	$withdrawinfo = $this->getbase->getone('hotel_withdraw_cash',['where'=>['id'=>$id],'field'=>'hotel_id']);
					          	##酒店信息
					          	 $authinfo = $this->getbase->getone('hotel_authen',['where'=>['hotel_id'=>$withdrawinfo['hotel_id']],'field'=>'phone,name']);
					          	 ##酒店名
					          	 $hotelname = $this->getbase->getone('hotel',['where'=>['id'=>$withdrawinfo['hotel_id']],'field'=>'hotel_name']);
					          	 if($real_phone = decode($authinfo['phone'])){
					          	 	$re = send_phone($real_phone,[$authinfo['name'].'('.$hotelname['hotel_name'].')',$data['remark']],'44828');
					          	 }
					          	}
					          ##发短信
	                	return returnJson(0,'操作成功',$data['gourl']);
	                }else{
	                	return returnJson(1,'操作失败');
	                }

	                
	           }else{
	           	 return returnJson(1,'where参数不能为空');
	           }
		}
	}
	/**
	 * [notpass_withdraw_express 物流端拒绝提现]
	 * @Author   Jerry
	 * @DateTime 2017-09-19T17:04:11+0800
	 * @Example  eg:
	 * @return   [type]                   [description]
	 */
	public function notpass_withdraw_express(){
		if($this->request->isPost()){
	            $data = $this->request->param();
	             if($data['validate']) $this->validate($data['validate'],'',$data,'api');
	            if($data[$data['field']]){
	                $where = "{$data['field']}='{$data[$data['field']]}'";
	                $id = $data[$data['field']];
	                unset($data[$data['field']]);
	                // $data['update_time'] = date('Y-m-d H:i:s',time());
	                // $data['status'] = '-1';
	                if(false!==model('Base')->getedit($data['table'],['where'=>$where],['status'=>'-1','remark'=>$data['remark']])){
	                	##写入日志
					         ##写入日志
						          $log = [
						            'withdraw_cash_id'=>$id,
						            'create_time'=>date('Y-m-d H:i:s'),
						            'log'=>'提现金额被拒，原因:'.$data['remark'],
						            'remarks'=>'申请状态改为-1',
						            'admin_id'=>cookie('admin_uid'),
						          ];
					          $this->getbase->getadd('express_withdraw_cash_through_log',$log);
					          ###发短信
					          if(getset('withdraw_error_sns')=='Y'){
					          	 $withdraw_info = $this->getbase->getone('express_withdraw_cash',['where'=>['id'=>$id],'field'=>'express_id,money']);
		                  		 $realname = $this->getbase->getone('users_express',['where'=>['id'=>$withdraw_info['express_id']],'field'=>'realname,phone']);
					          	 if($real_phone = decode($realname['phone'])){
					          	 	send_phone($real_phone,[$realname['realname'],$data['remark']],'44828');
					          	 }
					          	 
					          }
					          ##发短信
	                	return returnJson(0,'操作成功',$data['gourl']);
	                }else{
	                	return returnJson(1,'操作失败');
	                }

	                
	           }else{
	           	 return returnJson(1,'where参数不能为空');
	           }
		}
	}
	/**
	 * [withdrawcashsuccess 完成转款]
	 * @Author   Jerry
	 * @DateTime 2017-09-08T18:28:47+0800
	 * @Example  eg:
	 * @return   [type]                   [description]
	 */
	public function withdrawcashsuccess(){
		if($this->request->isPost()){
			$data = input();
			if(is_array($data['ids'])){
				$idsstr = implode(",", $data['ids']);
				if(false!==$this->getbase->getedit('express_withdraw_cash',['where'=>['id'=>['in',$idsstr]]],['status'=>'3'])){
					foreach ($data['ids'] as $v) {
						##写入日志
				          $log = [
				            'withdraw_cash_id'=>$v,
				            'create_time'=>date('Y-m-d H:i:s'),
				            'log'=>'提现金额已发放',
				            'remarks'=>'申请状态改为3',
				            'admin_id'=>cookie('admin_uid'),
				          ];
			          $this->getbase->getadd('express_withdraw_cash_through_log',$log);
			          ##减掉用户冻结的金额
			          $withinfo = $this->getbase->getone('express_withdraw_cash',['where'=>['id'=>$v],'field'=>'money,express_id']);##申请信息

			          $this->getbase->getdb('users_express')->where("id = {$withinfo['express_id']}")->setDec('frozen_money',$withinfo['money']);
			          // $this->getbase->getdel('users_express',['where'=>['id'=>$withinfo['express_id']]],['frozen_money'=>"frozen_money-$withinfo['money']"]);
					}
					return returnJson(0,'审核成功');
				}else{
					return returnJson(1,'审核失败，请稍后再试');
				}
			}else{
				return returnJson(1,'选择要审核的内容');
			}
			
		}
	}

	/**
	 * [hotelwithdrawcashaccess 酒店提现审核通过]
	 * @Author   WuSong
	 * @DateTime 2017-09-14T15:40:10+0800
	 * @Example  eg:
	 * @return   [type]                   [description]
	 */
	public function hotelwithdrawcashaccess(){
		if($this->request->isPost()){
			$data = input();
			if(is_array($data['ids'])){
				$idsstr = implode(",", $data['ids']);
				if(false!==$this->getbase->getedit('hotel_withdraw_cash',['where'=>['id'=>['in',$idsstr]]],['status'=>'2'])){

					foreach ($data['ids'] as $v) {
						##写入日志
			           $log = [
			            'withdraw_cash_id'=>$v,
			            'create_time'=>date('Y-m-d H:i:s'),
			            'log'=>'提现审核通过',
			            'money'=>$money,
			            'remarks'=>'申请状态改为2',
			            'admin_id'=>cookie('admin_uid'),
			          ];

			          $this->getbase->getadd('hotel_withdraw_cash_log',$log);
			           ##发短信
			          if(getset('authen_success_sns_hotel')=='Y'){
			          	##提现的相关信息
			          	$withdrawinfo = $this->getbase->getone('hotel_withdraw_cash',['where'=>['id'=>$v],'field'=>'money,hotel_id']);
			          	##酒店信息
			          	 $authinfo = $this->getbase->getone('hotel_authen',['where'=>['hotel_id'=>$withdrawinfo['hotel_id']],'field'=>'phone,name']);
			          	 ##酒店名
			          	 $hotelname = $this->getbase->getone('hotel',['where'=>['id'=>$withdrawinfo['hotel_id']],'field'=>'hotel_name']);
			          	 if($real_phone = decode($authinfo['phone'])){
			          	 	$re = send_phone($real_phone,[$authinfo['name'].'('.$hotelname['hotel_name'].')',$withdrawinfo['money']],'44827');
			          	 }
			          	 
			          }
			          ##发短信

					}
					 


					return returnJson(0,'审核成功');
				}else{
					return returnJson(1,'审核失败，请稍后再试');
				}
			}else{
				return returnJson(1,'选择要审核的内容');
			}
			
		}
	}
	/**
	 * [hotelwithdrawcashsuccess 酒店完成转款]
	 * @Author   WuSong
	 * @DateTime 2017-09-14T11:18:21+0800
	 * @Example  eg:
	 * @return   [type]                   [description]
	 */
	public function hotelwithdrawcashsuccess(){
		if($this->request->isPost()){
			$data = input();
			if(is_array($data['ids'])){
				$idsstr = implode(",", $data['ids']);
				if(false!==$this->getbase->getedit('hotel_withdraw_cash',['where'=>['id'=>['in',$idsstr]]],['status'=>'3'])){
					foreach ($data['ids'] as $v) {
						##写入日志
				          $log = [
				            'withdraw_cash_id'=>$v,
				            'create_time'=>date('Y-m-d H:i:s'),
				            'log'=>'提现金额已发放',
				            'remarks'=>'申请状态改为3',
				            'admin_id'=>cookie('admin_uid'),
				          ];
			          $this->getbase->getadd('hotel_withdraw_cash_log',$log);
			          ##减掉用户冻结的金额
			          $withinfo = $this->getbase->getone('hotel_withdraw_cash',['where'=>['id'=>$v],'field'=>'money,hotel_id']);##申请信息


			          $this->getbase->getdb('hotel')->where('id',$withinfo['hotel_id'])->setDec('frozen_money',$withinfo['money']);

			          // $this->getbase->getdel('users_express',['where'=>['id'=>$withinfo['express_id']]],['frozen_money'=>"frozen_money-$withinfo['money']"]);
					}
					return returnJson(0,'审核成功');
				}else{
					return returnJson(1,'审核失败，请稍后再试');
				}
			}else{
				return returnJson(1,'选择要审核的内容');
			}
			
		}
	}

	/**
	 * [express_real_status 物流实名认证通过]
	 * @Author   Jerry
	 * @DateTime 2017-09-11T18:27:38+0800
	 * @Example  eg:
	 * @return   [type]                   [description]
	 */
	public function express_real_access(){
		if($this->request->isPost()){
			$data = input();
			if(count($data['ids'])>0){
				$idsstr =implode(",", $data['ids']);
				if(false!==$this->getbase->getedit('users_express',['where'=>['id'=>['in',$idsstr]]],['real_status'=>'2','real_remark'=>''])){
					foreach ($data['ids'] as $v) {
							##写入日志
					          $log = [
					            'express_id'=>$v,
					            'create_time'=>date('Y-m-d H:i:s'),
					            'log'=>'实名认证通过',
					            'remark'=>'实名认证状态改为2，后台操作人id'.cookie('admin_uid'),
					            'admin_id'=>cookie('admin_uid'),
					            'ip'=>fetch_ip(),
					          ];
					          $this->getbase->getadd('express_real_authen_log',$log);
					          ##发短信
					          if(getset('authen_success_sns')=='Y'){
					          	 $phone = $this->getbase->getone('users_express',['where'=>['id'=>$v],'field'=>'phone,realname']);
					          	 if($real_phone = decode($phone['phone'])){
					          	 	send_phone($real_phone,[$phone['realname']],'44829');
					          	 }
					          	 
					          }
					          ##发短信
				          
				      }
				}
				return returnJson(0,'审核成功');
			}else{
				return returnJson(1,'请先选择用户');
			}
		}
	}
	/**
	 * [notpass_real_express 实名认证取消]
	 * @Author   Jerry
	 * @DateTime 2017-09-12T09:31:23+0800
	 * @Example  eg:
	 * @return   [type]                   [description]
	 */
	public function notpass_real_express(){
		if($this->request->isPost()){
	            $data = $this->request->param();
	             if($data['validate']) $this->validate($data['validate'],'',$data,'api');
	            if($data[$data['field']]){
	                $where = "{$data['field']}='{$data[$data['field']]}'";
	                $express_id = $data[$data['field']];
	                unset($data[$data['field']]);
	                $data['update_time'] = date('Y-m-d H:i:s',time());
	                $data['real_status'] = '-1';
	                if(false!==model('Base')->getedit($data['table'],['where'=>$where],$data)){
	                	##写入日志
					          $log = [
					            'express_id'=>$express_id,
					            'create_time'=>date('Y-m-d H:i:s'),
					            'log'=>'实名认证失败,原因:'.$data['real_remark'],
					            'remark'=>'实名认证状态改为-1，后台操作人id'.cookie('admin_uid'),
					            'admin_id'=>cookie('admin_uid'),
					            'ip'=>fetch_ip(),
					          ];
					          $this->getbase->getadd('express_real_authen_log',$log);
	                		##发短信
					          if(getset('authen_error_sns')=='Y'){
					          	 $phone = $this->getbase->getone('users_express',['where'=>['id'=>$express_id],'field'=>'phone,realname']);
					          	 if($real_phone = decode($phone['phone'])){
					          	 	$re = send_phone($real_phone,[$phone['realname'],$data['real_remark']],'44830');
					          	 }
					          	 
					          }
					          ##发短信
	                	return returnJson(0,'操作成功',$data['gourl']);
	                }else{
	                	return returnJson(1,'操作失败');
	                }

	                
	           }else{
	           	 return returnJson(1,'where参数不能为空');
	           }
		}
	}


	/**
	 * [hotel_authen 酒店实名认证通过]
	 * @Author   WuSong
	 * @DateTime 2017-09-13T09:24:38+0800
	 * @Example  eg:
	 * @return   [type]                   [description]
	 */
	public function hotel_authen(){
		if($this->request->isPost()){
			$data = input();
			if(count($data['ids'])>0){
				$idsstr =implode(",", $data['ids']);

				if(false!==$this->getbase->getedit('hotel',['where'=>['id'=>['in',$idsstr]]],['cash_status'=>'2'])){

					foreach ($data['ids'] as $v) {
							##写入日志
					          $log = [
					            'hotel_id'=>$v,
					            'create_date'=>date('Y-m-d H:i:s'),
					            'log'=>'酒店认证通过',
					            'remark'=>'酒店认证状态改为2，后台操作人id'.cookie('admin_uid'),
					            'admin_id'=>cookie('admin_uid'),
					            'ip'=>fetch_ip(),
					          ];
				          $this->getbase->getadd('hotel_authen_log',$log);
					           ##发短信
					          if(getset('authen_success_sns_hotel')=='Y'){
					          	##认证的相关信息
					          	 $authinfo = $this->getbase->getone('hotel_authen',['where'=>['hotel_id'=>$v],'field'=>'phone,name']);
					          	 ##酒店名
					          	 $hotelname = $this->getbase->getone('hotel',['where'=>['id'=>$v]]);
					          	 if($real_phone = decode($authinfo['phone'])){
					          	 	$re = send_phone($real_phone,[$authinfo['name'],$hotelname['hotel_name']],'44920');
					          	 }
					          	 
					          }
				          	##发短信
				      }
				     
				}
				return returnJson(0,'审核成功');
			}else{
				return returnJson(1,'请先选择用户');
			}
		}
	}


	/**
	 * [notpass_status_hotel 酒店实名认证失败]
	 * @Author   WuSong
	 * @DateTime 2017-09-13T09:26:37+0800
	 * @Example  eg:
	 * @return   [type]                   [description]
	 */
	public function notpass_status_hotel(){
		if($this->request->isPost()){
	            $data = $this->request->param();
	             if($data['validate']) $this->validate($data['validate'],'',$data,'api');
	            if($data[$data['field']]){
	                $where = "{$data['field']}='{$data[$data['field']]}'";
	                $hotel_id = $data[$data['field']];
	                unset($data[$data['field']]);
	                ##更改状态
	                if(false!==model('Base')->getedit('hotel',['where'=>['id'=>$hotel_id]],['cash_status'=>'-1'])){
	                	// show(model('base')->getLastSql());
	                	 		model('base')->getedit($data['table'],['where'=>$where],['status_remark'=>addslashes(input('status_remark'))]);##更改备注
	                	##写入日志
					          $log = [
					            'hotel_id'=>$hotel_id,
					            'create_date'=>date('Y-m-d H:i:s'),
					            'log'=>'实名认证失败,失败原因：'.$data['remark'],
					            'remark'=>'实名认证状态改为-1，后台操作人id'.cookie('admin_uid'),
					            'admin_id'=>cookie('admin_uid'),
					            'ip'=>fetch_ip(),
					          ];
				          $this->getbase->getadd('hotel_authen_log',$log);
	                		 ##发短信
					          if(getset('authen_error_sns_hotel')=='Y'){
					          	##认证的相关信息
					          	 $authinfo = $this->getbase->getone('hotel_authen',['where'=>['hotel_id'=>$hotel_id],'field'=>'phone,name']);
					          	 ##酒店名
					          	 $hotelname = $this->getbase->getone('hotel',['where'=>['id'=>$hotel_id]]);
					          	 if($real_phone = decode($authinfo['phone'])){
					          	 	$re = send_phone($real_phone,[$authinfo['name'],$hotelname['hotel_name'],$data['remark']],'44922');
					          	 }
					          	 
					          }
				          	##发短信
	                	return returnJson(0,'操作成功',$data['gourl']);
	                }else{
	                	return returnJson(1,'操作失败');
	                }

	                
	           }else{
	           	 return returnJson(1,'where参数不能为空');
	           }
		}
	}
	/**
	 * [hotel_backpass 找回密码申请审核通过]
	 * @Author   WuSong
	 * @DateTime 2017-09-18T19:12:48+0800
	 * @Example  eg:
	 * @return   [type]                   [description]
	 */
	public function hotel_backpass(){
		if($this->request->isPost()){
			$data = input();
			if(count($data['ids'])>0){
				$idsstr =implode(",", $data['ids']);
				if(false!==$this->getbase->getedit('hotel_backpass_log',['where'=>['id'=>['in',$idsstr]]],['status'=>'2'])){
					
					foreach ($data['ids'] as $v) {
							##写入日志
					          $log = [
					            'id'=>$v,
					            'hotel_backpass_id'=>cookie('hotel_id'),
					            'create_time'=>date('Y-m-d H:i:s'),
					            'log'=>'实名认证通过',
					            'remarks'=>'实名认证状态改为2，后台操作人id'.cookie('admin_uid'),
					            'admin_id'=>cookie('admin_uid'),
					            'ip'=>fetch_ip(),
					          ];
				          $this->getbase->getadd('hotel_backpass_cash_log',$log);
				      }
				}
				return returnJson(0,'审核成功');
			}else{
				return returnJson(1,'请先选择用户');
			}
		}
	}

	/**
	 * [notpass_status_hotelbackpass 申请密码找回审核失败]
	 * @Author   WuSong
	 * @DateTime 2017-09-18T20:03:43+0800
	 * @Example  eg:
	 * @return   [type]                   [description]
	 */
	public function notpass_status_hotelbackpass(){
		if($this->request->isPost()){
	            $data = $this->request->param();
	             if($data['validate']) $this->validate($data['validate'],'',$data,'api');
	            if($data[$data['field']]){
	                $where = "{$data['field']}='{$data[$data['field']]}'";
	                $hotel_id = $data['id'];
	                unset($data[$data['field']]);
	                // $data['update_time'] = date('Y-m-d H:i:s',time());
	                $data['status'] = '-1';

	                if(false!==model('Base')->getedit($data['table'],['where'=>$where],$data)){
	                	##写入日志
					          $log = [
					            'id'=>$hotel_id,
					            'create_time'=>date('Y-m-d H:i:s'),
					            'log'=>'实名认证失败,失败原因：'.$data['remark'],
					            'remarks'=>'实名认证状态改为-1，后台操作人id'.cookie('admin_uid'),
					            'admin_id'=>cookie('admin_uid'),
					            'ip'=>fetch_ip(),
					          ];
				          $this->getbase->getadd('hotel_backpass_cash_log',$log);
	                	
	                	return returnJson(0,'操作成功',$data['gourl']);
	                }else{
	                	return returnJson(1,'操作失败');
	                }

	                
	           }else{
	           	 return returnJson(1,'where参数不能为空');
	           }
		}
	}

	/**
	 * [leaving_access 留言审核通过]
	 * @Author   WuSong
	 * @DateTime 2017-09-18T20:03:43+0800
	 * @Example  eg:
	 * @return   [type]                   [description]
	 */
	public function leaving_access(){
		if($this->request->isPost()){
			$data = input();
			if(count($data['ids'])>0){
				$idsstr =implode(",", $data['ids']);
				false!==$this->getbase->getedit('index_message',['where'=>['id'=>['in',$idsstr]]],['status'=>'1']);
					
				return returnJson(0,'留言审核成功');
			}else{
				return returnJson(1,'留言审核失败');
			}
		}
	}
		
}
