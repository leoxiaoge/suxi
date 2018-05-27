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
namespace app\express\controller;
use app\common\controller\Base;
use think\DB;
use think\Cache;
use think\Validate;
class Api extends Base{
          public function _initialize(){
            parent::_initialize();
          }
          /**
           * [login description]
           * @Author   Jerry
           * @DateTime 2017-09-01T18:05:51+0800
           * @Example  eg:
           * @return   [type]                   [description]
           */
         public function login(){
         	if($this->request->isPost()){
        		$data = input();
         		$code = session('express_code');
         		if(!(int)input('phone')) return returnJson(1,'手机号不能为空');
         		if(!input('password')) return returnJson(1,'密码不能为空');
         		if(!input('code')) return returnJson(1,'验证不能为空');
         		if((int)trim(input('code'))!=(int)$code) return returnJson('1','验证码错误');
         		##此手机号是否已经注册
         		if($this->getbase->getcount('users_express',['where'=>['phone'=>encode(input('phone'))]])>0){
         			return returnJson('1','此手机号已被注册，如忘记密码，请选择找回密码');
         		}
         		$data['password'] = encode(input('password'));
         		$data['phone'] = encode(input('phone'));
            $data['create_time'] = date('Y-m-d H:i:s');
         		if($this->getbase->getadd('users_express',$data)){
         			return returnJson('0','注册成功','express/user/login');
         		}else{
         			return returnJson(1, '注册失败，请重试');
         		 }
           	}
         }
         /**
          * [getcode 手机验证码]
          * @Author   WuSong
          * @DateTime 2017-09-04T17:53:03+0800
          * @Example  eg:
          * @return   [type]                   [description]
          */
         public function getcode(){

         	if($this->request->isPost()){
         		$data = input();
         		$code = rand(1111,9999);
            //获取最新一条数据的时间，通过手机号匹配
            if($phone = $this->getbase->getone('user_message',['where'=>['phone'=>$data['phone']],'field'=>'phone,time','order'=>'id desc'])){
              $nowtime = time();
              $sqltime = $phone['time'];
              if(($nowtime-$sqltime)<60){
                return returnJson(1,'请求过于频繁，请稍后在试');
              }
         

            } 
         		$re = send_phone($data['phone'],[$code,'3'],config('sms_tempid'));
         		session('express_code',$code);
            //插入表单
            $this->getbase->getadd('user_message',['time'=>time(),'code'=>$code,'phone'=>$data['phone']]);

         		if($re->errmsg=="OK"&&$re->result==0){    

         			return returnJson(0);
         		}else{
         			return returnJson(1,$re->errmsg);
         		}

         	}
         }
          
         /**
          * [loginone 登陆]
          * @Author   WuSong
          * @DateTime 2017-09-04T17:50:55+0800
          * @Example  eg:
          * @return   [type]                   [description]
          */
         public function loginone(){
          if($this->request->isPost()){
                $password = input('post.password');
                $phone = input('post.phone');
                //查询匹配手机号相关数据
                $phones =DB::table(config('database.prefix').'users_express')->where('phone',encode($phone))->find();  
                if(!is_array($phones)){ 
                  return returnJson('1','手机号码不正确');
                }
                
                if($phones['password'] != encode($password)){
                  return returnJson('1','密码错误');
                }

                cookie('express_id',$phones['id']);


                return returnJson('0','登陆成功','express/index/index');

              }
         }
         /**
          * [backPass 密码找回]
          * @Author   WuSong
          * @DateTime 2017-09-04T17:50:22+0800
          * @Example  eg:
          * @return   [type]                   [description]
          */
          public function backpasss(){
            if($this->request->isPost()){
              $data=input();
              $code =session('express_code');
              $phone =input('post.phone');
              $password = input('post.password');
              //查询手机号相关数据
              $phones = DB::table(config('database.prefix').'users_express')->where('phone',encode($phone))->find();
              if(!(int)input('phone')) return returnJson(1,'手机号不能为空');
              if(!input('password')) return returnJson(1,'密码不能为空');
              if(encode($phone) != $phones['phone']){

                return returnJson(1,'请输入正确的手机号');
              }
              if(encode($password) == $phones['passsword']){
                return returnJson(1,'新密码与旧密码不能相同');
              }

                $data['password'] = encode(input('password'));
                $data['phone'] = encode(input('phone'));
                //删除查询到的数据
                $phones = DB::table(config('database.prefix').'users_express')->where('phone',encode($phone))->delete();
                if($this->getbase->getadd('users_express',$data)){

                  return returnJson('0','密码找回成功','express/user/login');
                }else{
                  return returnJson(1, '找回失败');
                }

              }

            }
            /**
             * [loginout 退出登陆]
             * @Author   WuSong
             * @DateTime 2017-09-04T17:50:33+0800
             * @Example  eg:
             * @return   [type]                   [description]
             */
            public function loginout(){
              $loginout = cookie('express_id',NULL);
              if(!$loginout){
                return returnJson('0','退出登陆成功','express/ucenter/login');
              }
            }
            /**
             * [express_status 物流取衣]
             * @Author   WuSong
             * @DateTime 2017-09-06T18:09:54+0800
             * @Example  eg:
             * @return   [type]                   [description]
             */
           public function express_status_take(){
            if($this->request->isPost()){
              $eou_id = (int)input('eou_id');
             if(!$eou_id) return returnJson('1','数据异常');
              $eouinfo = $this->getbase->getone('express_order_users',['where'=>['id'=>$eou_id]]);
              ##物流订单关联表信息
              #status=>20 同意取件
               if(false!==$this->getbase->getedit('express_order_users',['where'=>['id'=>$eou_id]],['status'=>1])){
                  // $this->getbase->getedit('order',['where'=>['id'=>$eouinfo['order_id']]],['status'=>30]);
                  ##写入日志
                  $log = [
                    'express_id'=>$eouinfo['express_id'],
                    'ip'=>fetch_ip(),
                    'log'=>"物流员已接单",
                    'create_time'=>date('Y-m-d H:i:s'),
                    // 'remark'=>''
                    'order_id'=>$eouinfo['order_id'],
                    'edu_id'=>$eou_id,
                    'change_status'=>'20',
                    // 'admin_id'=>
                  ];
                  $this->getbase->getadd('express_order_users_log',$log);
                  return returnJson(0,'成功接单,请尽快前往取件');
               }else{
                return returnJson(1,'数据异常，请稍后再试');
               }
            }
           }


           /**
            * [express_status_give 物流送衣]
            * @Author   WuSong
            * @DateTime 2017-10-31T12:33:14+0800
            * @Example  eg:
            * @return   [type]                   [description]
            */
           public function express_status_give(){
            if($this->request->isPost()){
              $eou_id = (int)input('eou_id');
             if(!$eou_id) return returnJson('1','数据异常');
              $eouinfo = $this->getbase->getone('express_order_users',['where'=>['id'=>$eou_id]]);
              //查询订单ID跟订单号
              $order = $this->getbase->getone('order',['where'=>['id'=>$eouinfo['order_id']],'field'=>'id,order_number']);

              ##物流订单关联表信息
              #status=>1 同意送件
               if(false!==$this->getbase->getedit('express_order_users',['where'=>['id'=>$eou_id]],['status'=>1])){
                  $this->getbase->getedit('order',['where'=>['id'=>$eouinfo['order_id']]],['status'=>90]);
                  ##写入日志
                  $log = [
                    'express_id'=>$eouinfo['express_id'],
                    'ip'=>fetch_ip(),
                    'log'=>"物流员已接单",
                    'create_time'=>date('Y-m-d H:i:s'),
                    // 'remark'=>''
                    'order_id'=>$eouinfo['order_id'],
                    'edu_id'=>$eou_id,
                    'change_status'=>'90',
                    // 'admin_id'=>
                  ];
                  $this->getbase->getadd('express_order_users_log',$log);
                  $logs = [
                      'order_id'=>$order['id'],
                      'create_time'=>time(),
                      'remark'=>'物流员已接单，配送中',
                      'uid'=>1,
                      'type'=>2,
                      'status'=>1,
                      'order_number'=>$order['order_number'],
                      'express_id'=>$eouinfo['express_id'],
                      'order_status'=>'90',
                  ];
                  $this->getbase->getone('order_info',$logs);
                  return returnJson(0,'成功接单,请尽快送达');
               }else{
                return returnJson(1,'数据异常，请稍后再试');
               }
            }
           }


           /**
            * `物流配送完毕(待用中，暂时没用。等待流程确定的时候用)
            * @Author   Jerry
            * @DateTime 2017-09-21T11:16:06+0800
            * @Example  eg:
            * @return   [type]                   [description]
            */
           public function express_finish(){
             if($this->request->isPost()){
              $eou_id = (int)input('eou_id');
               if(!$eou_id) return returnJson('1','数据异常');
                ##物流订单关联表信息
                #status=>1 同意取件
                 if(false!==$this->getbase->getedit('express_order_users',['where'=>['id'=>$eou_id]],['status'=>3])){
                    $eouinfo = $this->getbase->getone('express_order_users',['where'=>['id'=>$eou_id]]);
                    ##写入日志
                    $log = [
                      'express_id'=>$eouinfo['express_id'],
                      'ip'=>fetch_ip(),
                      'log'=>"物流员完成配送",
                      'create_time'=>date('Y-m-d H:i:s'),
                      'order_id'=>$eouinfo['order_id'],
                      'edu_id'=>$eou_id,
                      'change_status'=>'3',
                    ];
                    $this->getbase->getadd('express_order_users_log',$log);
                    return returnJson(0,'成功接单');
                 }else{
                  return returnJson(1,'数据异常，请稍后再试');
                 }
            }
           }
            /**
             * [modnames 修改名字]
             * @Author   WuSong
             * @DateTime 2017-09-06T18:09:54+0800
             * @Example  eg:
             * @return   [type]                   [description]
             */
            public function modnames(){
              if($this->request->isPost()){
                $data=input();
                $id = (int)cookie('express_id');
                $data['name']=addslashes(input('post.name'));
                //修改用户名
                $this->getbase->getedit('users_express',['where'=>['id'=>$id]],['name'=>$data['name']]);


                return returnJson('0','用户名修改成功','express/user/index');
              }

          }

           /**
             * [modpasss 修改密码]
             * @Author   WuSong
             * @DateTime 2017-09-07T18:09:54+0800
             * @Example  eg:
             * @return   [type]                   [description]
             */
            public function modpasss(){
              if($this->request->isPost()){
                $data = input();
                $id =(int)cookie('express_id');
                if(!input('newpassword')) return returnJson(1,'新密码不能为空');

                $oldpassword = encode(input('post.oldpassword'));
                $newpassword = encode(input('post.newpassword'));
                $newpasswordok =encode(input('post.newpasswordok'));
                //查相同ID的数据
                $users_info= $this->getbase->getone('users_express',['where'=>['id'=>$id]]);

                if($oldpassword != $users_info['password']){
                  return returnJson(1,'原密码错误');
                }

                if($oldpassword == $newpassword){
                  return returnJson(1,'原密码与新密码不得一致');
                }
                if($newpassword != $newpasswordok){
                  return returnJson(1,'新密码两次必须相同');
                }
                //修改
                $data['password']=addslashes(encode(input('post.newpassword')));
                $this->getbase->getedit('users_express',['where'=>['id'=>$id]],['password'=>$data['password']]);
                cookie('express_id',null);
                return returnJson('0','密码修改成功',url('express/ucenter/login'));
              }
            }
            /**
             * [upimg 实名认证上传身份证正面照]
             * @Author   Jerry
             * @DateTime 2017-09-09T14:10:51+0800
             * @Example  eg:
             * @return   [type]                   [description]
             */
            public function upimgface(){
               if ($data = $_POST['base64']) {
                    if(cookie('express_id')>1){
                        ##插入到当前用户当中
                        if($fileface = $this->uploadimg($data,rand(1,8888).'avatar'.cookie('express_id'),'/public/uploads/express/realname/')){
                            $this->getbase->getedit('users_express',['where'=>['id'=>cookie('express_id')]],['id_card_face'=>$fileface]);
                              return returnJson('0',$fileface);
                        }else{
                          return returnJson(1,'服务器异常，请稍后再试');
                        }
                    }
                     
                }
            }
            /**
             * [upimg 身份证反面照]
             * @Author   WuSong
             * @DateTime 2017-09-09T15:45:23+0800
             * @Example  eg:
             * @return   [type]                   [description]
             */
             public function upimgside(){
               if ($data = $_POST['base64']) {
                    if(cookie('express_id')>1){
                        ##插入到当前用户当中
                        if($fileside = $this->uploadimg($data,rand(1,8888).'avatar'.cookie('express_id'),'/public/uploads/express/realname/')){
                            $this->getbase->getedit('users_express',['where'=>['id'=>cookie('express_id')]],['id_card_side'=>$fileside]);
                              return returnJson('0',$fileside);
                        }else{
                          return returnJson(1,'服务器异常，请稍后再试');
                        }
                    }
                     
                }
            }
            /**
             * [upimg 手持身份证照]
             * @Author   WuSong
             * @DateTime 2017-09-09T15:44:54+0800
             * @Example  eg:
             * @return   [type]                   [description]
             */
            public function upimghand(){
               if ($data = $_POST['base64']) {
                    if(cookie('express_id')>1){
                        ##插入到当前用户当中
                        if($filehand = $this->uploadimg($data,rand(1,8888).'avatar'.cookie('express_id'),'/public/uploads/express/realname/')){
                            $this->getbase->getedit('users_express',['where'=>['id'=>cookie('express_id')]],['id_card_hand'=>$filehand]);
                              return returnJson('0',$filehand);
                        }else{
                          return returnJson(1,'服务器异常，请稍后再试');
                        }
                    }
                     
                }
            }
            /**
             * [relname 实名认证]
             * @Author   WuSong
             * @DateTime 2017-09-09T15:53:50+0800
             * @Example  eg:
             * @return   [type]                   [description]
             */
            public function relname(){
              if($this->request->isPost()){
                $data = input();
                $reData = [];
                $id = (int)cookie('express_id');
                $reData['realname'] =addslashes(input('realname'));
                $reData['id_card'] = trim(addslashes(encode(input('id_card'))));
                $reData['real_status'] = 1;
                $reData['create_time']=date('Y-m-d H:i:s');
                $realname_info = $this->getbase->getone('users_express',['where'=>['id'=>$id]]);
               
                 $rule =   [
                      'realname'  => 'require|chs',
                      'id_card'   => 'require',
                      // 'id_card_face' => 'require',    
                      // 'id_card_side' => 'require',    
                      // 'id_card_hand' => 'require',    
                  ];
                  
                 $message  =   [
                    'realname.require' => '姓名不能为空',
                    'realname.chs' => '姓名只能为汉字',
                    'id_card.require' => '身份证号不能为空',
                    // 'id_card_face.require' => '请先上传身份证正面照',
                    // 'id_card_side.require' => '请先上传身份证背面照',
                    // 'id_card_hand.require' => '请上传手持身份证照',
                    
                ];
                $validate = new Validate($rule,$message);
                $result   = $validate->check($reData);
                if(!$result){
                  return returnJson(1,$validate->getError());
                }else{
                    if(empty($realname_info['id_card_face'])){
                  return returnJson(1,'请先上传身份证正面照');
                  }
                  if(empty($realname_info['id_card_side'])){
                    return returnJson(1,'请先上传身份证背面照');
                  }
                  if(empty($realname_info['id_card_hand'])){
                    return returnJson(1,'请上传手持身份证照');
                  }
                }
                ##本地验证身份证号码是否正确
                if(!is_idcard($reData['id_card'])) return returnJson('1','身份证号码格式错误');
                ##接口验证身份证信息
                $re = id_card_juhe($reData['id_card'],$reData['realname'],$id);
                if(!is_array(json_decode($re))){
                  $reData['id_card']=encode(addslashes($reData['id_card']));
                  if(false!==$this->getbase->getedit('users_express',['where'=>['id'=>$id]],$reData)){
                    ##写入日志
                          $log = [
                            'express_id'=>$id,
                            'create_time'=>date('Y-m-d H:i:s'),
                            'log'=>'实名认证申请',
                            'remarks'=>'物流人员实名认证申请',

                            'ip'=>fetch_ip(),
                          ];
                        $this->getbase->getadd('express_real_authen_log',$log);

                        ##短信给商家
                        $phone = getset('authen_express_phone');
                        if($phone){
                          $phoneArr =explode(",", $phone);
                          if(is_array($phoneArr)){
                              foreach ($phoneArr as  $v) {
                                if($v){
                                  send_phone($v,['（个人认证请求）'],'44688');
                                }
                            }
                          }
                        }
                        ##短信给商家
                    return returnJson(0,'实名认证已提交，请等待审核','express/index/index');
                  }
                }
                
              }

            }
            /**
             * [uparatar 上传用户图像]
             * @Author   Jerry
             * @DateTime 2017-09-08T11:37:53+0800
             * @Example  eg:
             * @return   [type]                   [description]
             */
            public function uparatar(){
               if ($data = $_POST['base64']) {
                    if(cookie('express_id')>1){
                        ##插入到当前用户当中
                        if($file = $this->uploadimg($data,rand(1,8888).'avatar'.cookie('express_id'),'/public/uploads/express/avatar/')){
                            $this->getbase->getedit('users_express',['where'=>['id'=>cookie('express_id')]],['avatarurl'=>$file]);
                              return returnJson('0',$file);
                        }else{
                          return returnJson(1,'服务器异常，请稍后再试');
                        }
                    }
                     
                }
            }
            /**
             * [uploadimg  上传图片]
             * @Author   Jerry
             * @DateTime 2017-09-08T11:36:07+0800
             * @Example  eg:
             * @return   [type]                   [description]
             */
            public function  uploadimg($data,$name='',$path='/public/uploads/express/'){
                    $name = $name?$name:date('YmdHis',time());
                    preg_match("/data:image\/(.*);base64,/",$data,$res);
                      if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $data, $result)){
                        $type = $result[2];
                        $new_file = $path.$name.'.'.$type;
                        $imgBase64 = base64_decode(str_replace($result[1], '', $data));
                        if (file_put_contents(".".$new_file, $imgBase64)){
                               $fh = fopen(".".$new_file, "r");
                                $data = fread($fh, filesize(".".$new_file));
                                $length = filesize(".".$new_file);
                                fclose($fh);
                                // $info['type'] = "image/".$type;
                                // $type = ($type=="jpeg")?"jpg":$type;
                                // $info['name'] = time() . "." . $type; //自定义图片名称
                                // $datas['path'] = $new_file;
                                // $datas['msg'] = "上传成功";
                                return $new_file;
                          
                        }else{
                               return false;
                        }
                      }
            }

            /**
             * [authtion 手机号认证]
             * @Author   WuSong
             * @DateTime 2017-09-07T13:09:54+0800
             * @Example  eg:
             * @return   [type]                   [description]
             */
            public function authtion(){
              if($this->request->isPost()){
                $data = input();
                $id = (int)cookie('express_id');
                $name= input('post.name');
                $users_id =input('post.users_id');
                $users_info= $this->getbase->getone('users_express',['where'=>['id'=>$id]]);
                if(empty($users_id)){
                  return returnJson(1,'身份证号不能为空');
                }
                if(strlen($users_id)!=18){
                  return returnJson(1,'身份证号码不正确');
                }
                if($users_id == $users_info['users_id']){

                    return returnJson(1,'该身份证号已被注册');
                }
               

                $data['name']=addslashes($name);
                $data['users_id']=addslashes($users_id);
                $this->getbase->getedit('users_express',['where'=>['id'=>$id]],['name'=>$data['name']]);
                $this->getbase->getedit('users_express',['where'=>['id'=>$id]],['users_id'=>$data['users_id']]);
                
                if(strlen($users_id)==18){
                 return returnJson('0','认证成功','express/user/index');
                }
              }
            }

            /**
             * [addcards 添加银行卡]
             * @Author   WuSong
             * @DateTime 2017-09-08T10:04:54+0800
             * @Example  eg:
             * @return   [type]                   [description]
             */
            public function addcards(){
                if($this->request->isPost()){
                  $data=input();
                  $id = (int)cookie('express_id');
                  $name = input('post.name');
                  $cardtype = input('post.cardtype');
                  $cardid =input('post.cardid');
                  if(empty($cardid)){
                    return returnJson(1,'银行卡号不能为空');
                  }
                  if(strlen($cardid) !=19 and strlen($cardid) !=16 ){
                    return returnJson(1,'银行卡号不正确');
                  }
                  ##接口验证银行卡是否正确
                  $re = is_bankcard($cardid,$id);
                  if(!is_array(json_decode($re))){

                    ##接口检查，身份证信息是否与当前的卡号信息相匹配。$expressinfo['phone']  $expressinfo['id_card']
                    // $expressinfo = $this->getbase->getone('users_express',['where'=>['id'=>$id]]);
                    // $api_bank_info = bankcard_info($expressinfo['realname'],430722199009138477,$cardid,1383838456,$id);
                    // die;
                    $data['name'] =addslashes($name);
                    $data['cardid'] = addslashes($cardid);
                    $data['cardtype'] = addslashes($cardtype);
                    $data['express_id']=addslashes($id);
                    if($this->getbase->getadd('users_express_bankcard',$data)){
                       return returnJson('0','银行卡添加成功','express/my/mycards');
                    }else{
                       return returnJson(1, '注册失败，请重试');
                    }
                  }
                  
                }
            }

             /**
              * [card_info 解除绑定]
              * @Author   WuSong
              * @DateTime 2017-09-08T18:37:23+0800
              * @Example  eg:
              * @return   [type]                   [description]
              */
            public function card_info(){
              if($this->request->isPost()){
                $id=(int)cookie('express_id');
                $password=input('post.password');
                //查询账号相关银行卡信息
         
                if($this->getbase->getcount('users_express',['where'=>['password'=>encode($password),'id'=>$id]])){
                  
                  $this->getbase->getdel('users_express_bankcard',['where'=>['express_id'=>$id]]);
                  return returnJson('0','解除绑定成功','express/my/mycards');
                }else{
                  return returnJson(1,'密码错误，请重新输入');
                }

                
              }
            }

            /**
             * [withdraw_cash 提现]
             * @Author   Jerry
             * @DateTime 2017-09-08T14:51:01+0800
             * @Example  eg:
             * @return   [type]                   [description]
             */
            public function withdraw_cash(){
              if($this->request->isPost()){
                if(cookie('express_id')<1) return returnJson('1','请先登陆!');
                $money = floatval(input('money'));
                $bankcard = addslashes(input('bankcard'));
                if($money<1) return returnJson('1','最小提现金额1元');
                ##查看当前帐户的金额是否充足
                $expressinfo = $this->getbase->getone('users_express',['where'=>['id'=>cookie('express_id')]]);
                if($expressinfo['money']<$money) return returnJson('1','提现金额不能大于帐户金额');
                ##把当前提现金额冻结
                $data = [
                'money'=>$expressinfo['money']-$money,
                'frozen_money'=>$expressinfo['money']+$money,
                ];
                $this->getbase->getedit('users_express',['where'=>['id'=>cookie('express_id')]],$data);

                ##提现申请
                $withdraw_cash = [
                'express_id'=>cookie('express_id'),
                'create_date'=>date('Y-m-d H:i:s'),
                'money' =>$money,
                'status'=>1,
                'bankcard'=>$bankcard,
                ];
                $withdraw_cash_id = $this->getbase->getadd('express_withdraw_cash',$withdraw_cash);
                if($withdraw_cash_id){
                   ##写入日志
                  $log = [
                    'withdraw_cash_id'=>$withdraw_cash,
                    'create_time'=>date('Y-m-d H:i:s'),
                    'source_moeny'=>$expressinfo['money'],
                    'change_money'=>$expressinfo['money']-$money,
                    'log'=>'提现申请',
                    'money'=>$money,
                    'remark'=>'用户申请提现，提现金额为:'.$money,
                    'express_id'=>cookie('express_id'),

                  ];
                  $this->getbase->getadd('express_withdraw_cash_log',$log);
                      ##发送短信给商家
                        $phone = getset('authen_express_phone');
                        if($phone){
                          $phoneArr =explode(",", $phone);
                          if(is_array($phoneArr)){
                              foreach ($phoneArr as  $v) {
                                if($v){
                                  send_phone($v,['(物流端提现申请)','金额:'.$money.'元'],'44685');
                                }
                            }
                          }
                        }
                        ##发送短信给商家

                  return returnJson(0,'提现成功');
                }else{
                  return returnJson(1,'提现失败，请稍后再试');
                }
               
                
              }
           

            }

            /**
             * [order_delete 取消订单]
             * @Author   WuSong
             * @DateTime 2017-09-27T18:08:09+0800
             * @Example  eg:
             * @return   [type]                   [description]
             */
            public function cancel_take(){
            if($this->request->isPost()){
              $eou_id = (int)input('eou_id');
              $cancel_reason = input('cancel_reason');
             if(!$eou_id) return returnJson('1','数据异常');
             $eouinfo = $this->getbase->getone('express_order_users',['where'=>['id'=>$eou_id]]);

              ##物流订单关联表信息
              #status=>-1 拒绝取单
               if(false!==$this->getbase->getedit('express_order_users',['where'=>['id'=>$eou_id]],['status'=>'-1','remark'=>$cancel_reason])){

                  // $this->getbase->getedit('order',['where'=>['id'=>$eouinfo['order_id']]],['status'=>-10]);
                  ##写入日志
                  $log = [
                    'express_id'=>$eouinfo['express_id'],
                    'ip'=>fetch_ip(),
                    'log'=>"物流员拒绝取单",
                    'create_time'=>date('Y-m-d H:i:s'),
                    'remark'=>$cancel_reason,
                    'order_id'=>$eouinfo['order_id'],
                    'edu_id'=>$eou_id,
                    'change_status'=>'-1',
                  ];
                  $this->getbase->getadd('express_order_users_log',$log);

                  $re =  pushordermsg('20','有一个异常订单待处理','订单号为'.$eouinfo['order_id']."拒绝理由是：$cancel_reason");

                  return returnJson(0,'已提交申请');
               }else{
                return returnJson(1,'数据异常，请稍后再试');
               }
            }
           }

           /**
            * [cancel_give 物流人员拒绝送单]
            * @Author   WuSong
            * @DateTime 2017-10-31T11:21:23+0800
            * @Example  eg:
            * @return   [type]                   [description]
            */
           public function cancel_give(){
            if($this->request->isPost()){
              $eou_id = (int)input('eou_id');
              $cancel_reason = input('cancel_reason');
             if(!$eou_id) return returnJson('1','数据异常');
                  $eouinfo = $this->getbase->getone('express_order_users',['where'=>['id'=>$eou_id]]);
              ##物流订单关联表信息
              #status=>-1 拒绝送件
               if(false!==$this->getbase->getedit('express_order_users',['where'=>['id'=>$eou_id]],['status'=>'-1','remark'=>$cancel_reason])){
                    // $this->getbase->getedit('order',['where'=>['id'=>$eouinfo['order_id']]],['status'=>-70]);

                  ##写入日志
                  $log = [
                    'express_id'=>$eouinfo['express_id'],
                    'ip'=>fetch_ip(),
                    'log'=>"物流员拒绝送单",
                    'create_time'=>date('Y-m-d H:i:s'),
                    'remark'=>$cancel_reason,
                    'order_id'=>$eouinfo['order_id'],
                    'edu_id'=>$eou_id,
                    'change_status'=>'-1',
                  ];
                  $this->getbase->getadd('express_order_users_log',$log);

                  $re =  pushordermsg('20','有一个异常订单待处理','订单号为'.$eouinfo['order_id']."拒绝理由是：$cancel_reason");
                  
                  return returnJson(0,'已提交申请');
               }else{
                return returnJson(1,'数据异常，请稍后再试');
               }
            }
           }


          // public function  goods(){
          //   if($this->request->isPost()){
          //     $id = input('post.id');
          //     show($id);
          //     $goods_info =DB::table(config('database.prefix').'goods')
          //                   ->alias('g')
          //                   ->join(config('database.prefix').'goods_cat gc','g.catid=gc.id','LEFT')
          //                   ->field('g.name,g.price,g.picture,gc.title')
          //                   ->where('gc.id',$id)
          //                   ->select();
          //     foreach ($goods_info as $k => $v) {
          //       $goods_info[$k]['picture']= get_file_path($v['picture']);
          //     }
              
          //     return returnJson($goods_info);

          //   }

          // }
             /**
              * [orderedit 订单商品修改]
              * @Author   WuSong
              * @DateTime 2017-10-19T11:28:06+0800
              * @Example  eg:
              * @return   [type]                   [description]
              */
             public function orderedit(){
                if($this->request->isPost()){
                  $data=input();
                  if(!$data['ids'] OR !$data['num']){
                      return returnJson(1, '请选择商品后再下单');
                     
                  }
                  $order_id = $data['orderid'];
                  $good_id =trim($data['ids'],',');
                  $good_num =$data['num'];
                  // show($good_id);
                  // show($good_num);
                  //历史商品ID 数量
                  $good_info= [];
                  $goods_id = explode(',', trim($data['ids'],','));
                  $goods_num = explode(',',trim($data['num'],','));

                  // show($goods_id);
                  // show($goods_num);
                  foreach ($goods_id as $k =>$v) {
                         
                          $good_info[$v]= $goods_num[$k];
                  }

                  //查找订单商品ID对应的商品数据
                  $goods_info =  $this->getbase->getall('goods',['where'=>"id in($good_id)",'field'=>'id,name,price,picture,catid']);
                    foreach ($goods_info as $ke => $ve) {
                      $ve['good_id'] =$goods_id[$k];
                      $ve['picture']= get_file_path($ve['picture']);
                      $ve['num']  = $good_info[$ve['id']];
                      $ve['prices']= $ve['num']*$ve['price'];
                      $ve['express_id'] = $id;
                      $request[]=$ve;
                    }
                  foreach ($request as $k => $v) {
                   
                        $goods_price[] = $v['prices'];
                      }
                  $goods_price_all =array_sum($goods_price);
                  //修改订单数据
                  if(false!==$this->getbase->getedit('order',['where'=>['id'=>$order_id]],['good_name'=>$goods_all,'good_price'=>$goods_price_all,'good_num'=>$data['num'],'update_time'=>time(),'good_id'=>$data['ids']])){
                      Cache::clear('express_order_users');
                     return returnJson(0,'商品修改成功');
                   }
                   return returnJson('1','商品修改失败，请稍后再试');
                 }
           }
           /**
            * [address_edit 修改用户收货信息]
            * @Author   WuSong
            * @DateTime 2017-10-24T16:31:14+0800
            * @Example  eg:
            * @return   [type]                   [description]
            */
          public function address_edit(){
              if($this->request->isPost()){
                  $data = input();
                  $user_name = addslashes(input('post.user_name'));
                  $user_address = addslashes(input('post.user_address'));
                  $user_phone = addslashes(encode(input('post.user_phone')));
                  $order_id = input('order_id');
                    
                  $rule =   [
                          'user_name'  => 'require|chs',
                          'user_phone'   => 'require',
                          'user_address' => 'require',    
                          // 'id_card_side' => 'require',    
                          // 'id_card_hand' => 'require',    
                      ];
                      
                     $message  =   [
                        'user_name.require' => '姓名不能为空',
                        'user_name.chs' => '姓名只能为汉字',
                        'user_phone.require' => '手机号码不能为空',
                        'user_address.require' => '地址不能为空',
                        // 'id_card_side.require' => '请先上传身份证背面照',
                        // 'id_card_hand.require' => '请上传手持身份证照',
                        
                    ];
                    $validate = new Validate($rule,$message);
                    $result   = $validate->check($reData);

                 //修改订单数据
                    if(false!==$this->getbase->getedit('order',['where'=>['id'=>$order_id]],['user_name'=>$user_name,'user_address'=>$user_address,'user_phone'=>$user_phone])){
                      return returnJson(0,'地址修改成功',url('express/orderedit/make',['order_id'=>$order_id]));
                    }
                    return returnJson('1','用户信息修改失败，请稍后再试');
                  
              }
          }

            /**
             * [make_edit 修改订单确认(取件)]
             * @Author   WuSong
             * @DateTime 2017-10-24T16:31:00+0800
             * @Example  eg:
             * @return   [type]                   [description]
             */
            public function make_edit(){
              if($this->request->isPost()){
                  $order_id = addslashes(input('order_id'));
                  $give_time =addslashes(input('post.give_time'));
                  $status= 30 ;
                  //修改时间
                    if(false!==$this->getbase->getedit('order',['where'=>['id'=>$order_id]],['give_time'=>$give_time,'status'=>$status])){
                      $order = $this->getbase->getone('order',['where'=>"o.id={$order_id} AND eou.type=1",'alias'=>'o','join'=>[['qlbl_express_order_users eou','eou.order_id = o.id']],'field'=>'o.order_number,eou.express_id']);
                          //写入日志
                        $params = [
                        'order_id'=> $order_id,
                        'uid' => $order['express_id'],##门店ID
                        'order_number' => $order['order_number'],
                        'order_status' => $status,
                        'type'=>2

                    ];
                    order_info($params);
                       cache('order_'.$order_id,NULL);
                       return returnJson(0,'下单成功，请提醒客户付款','express/order/index');
                    }
                   
                   return returnJson('1','订单确认失败，请联系管理员');
              }
            }

}
