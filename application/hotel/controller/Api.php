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
namespace app\hotel\controller;
use app\common\controller\Base;
use think\DB;
use think\Validate;
class Api extends Base{
  public function _initialize(){
    parent::_initialize();
  }
  /**
   * [register 注册]
   * @Author   WuSong
   * @DateTime 2017-09-11T11:43:21+0800
   * @Example  eg:
   * @return   [type]                   [description]
   */
    
  

  public function register(){
  	if($this->request->isPost()){
  		$data 		= input();
  		$accounts	= addslashes(input('post.accounts'));
  		$password	= addslashes(input('post.password'));
  		$passwordok = addslashes(input('post.passwordok'));
  		$address_provite = addslashes(input('post.address_provite'));
  		$arr = explode(' ', $address_provite);
  		$province= $data['province']= $arr[0];
  		$city = $data['city']	=$arr[1];
  		$area = $data['area']	=$arr[2];
  		$address	= addslashes(input('post.address'));
  		$name 		=addslashes(input('post.hotel_name'));
      $agree  = addslashes(input('post.agree'));
  		if(!input('accounts')) return returnJson(1,'账号不能为空');
  		// ##匹配手机号是否已注册
  		$hotel_info = $this->getbase->getall('hotel');
  		foreach ($hotel_info as $k => $v) {
  			if($accounts ==$v['accounts']){
  			return returnJson(1,'该账号已经被注册过');
  			}
  		}
      if( strlen(input('accounts')) <7) return returnJson(1,'账号过于简单，请重新设置！');

  		if(!input('password')) return returnJson(1,'密码不能为空');
      if(input('password') == '123456') return returnJson(1,'密码过于简单');
  		if(!input('passwordok')) return returnJson(1,'确认密码不能为空');
  		if(!input('hotel_name')) return returnJson(1,'酒店名称不能为空');
      // if( strlen(input('hotel_name')) <14) return returnJson(1,'酒店名称不正确');
  		if(!input('address_provite')) return returnJson(1,'酒店地址不能为空');
  		if(!input('address')) return returnJson(1,'详细地址不能为空');
      if( strlen(input('address')) <10) return returnJson(1,'酒店地址不正确');
      if($agree  != 1) return returnJson(1,'请先阅读商业协议');
  		if($password != $passwordok){
  			return returnJson(1,'两次输入的密码不一致');
  		}
  		$data['accounts']	=addslashes(input('post.accounts'));
  		$data['password']	=addslashes(encode(input('post.password')));
  		$data['address']	=addslashes(input('post.address'));
  		$data['hotel_name']		=addslashes(input('post.hotel_name'));
  		$data['province'] =$province;
  		$data['city'] =$city;
  		$data['area'] =$area;
      $data['create_date']=date('Y-m-d H:i:s');
  		if($this->getbase->getadd('hotel',$data)){
 			return returnJson('0','注册成功','hotel/ucenter/login');
 		}else{
 			return returnJson(1, '注册失败，请重试');
 		 }
  	}
  }


  /**
   * [login 登录]
   * @Author   WuSong
   * @DateTime 2017-09-11T14:04:20+0800
   * @Example  eg:
   * @return   [type]                   [description]
   */
  public function login(){
  	if($this->request->isPost()){
      $data= input();
  		$accounts = addslashes(input('post.accounts'));
  		$password = addslashes(input('post.password'));
  		// ##匹配用户名
      if(input($accounts)){
        return returnJson(1,'用户名不能为空');
      }
      if(input($password)){
        return returnJson(1,'密码不能为空');
      }
      $login_info = $this->getbase->getone('hotel',['where'=>['accounts'=>$accounts]]);
  		if($accounts != $login_info['accounts']){
  			return returnJson(1,'用户名或者密码错误');
  		}
  		if($password != (decode($login_info['password']))){
  			return returnJson(1,'用户名或者密码错误');
  		}

  			cookie('hotel_id',$login_info['id']);
  			return returnJson('0','登陆成功','hotel/index/index');
  		}

  	}

  /**
   * [upimglicense 营业执照上传]
   * @Author   WuSong
   * @DateTime 2017-09-12T09:19:11+0800
   * @Example  eg:
   * @return   [type]                   [description]
   */
  public function upimglicense(){
       if ($data = $_POST['base64']) {
            if(cookie('hotel_id')>0){
                ##插入到当前用户当中
                if($filelicense = $this->uploadimg($data,rand(1,8888).'avatar'.cookie('hotel_id'),'/public/uploads/hotel/authen/')){
                    $this->getbase->getedit('hotel',['where'=>['id'=>cookie('hotel_id')]],['license'=>$filelicense]);
                      return returnJson('0',$filelicense);
                }else{
                  return returnJson(1,'服务器异常，请稍后再试');
                }
            }
             
        }
    }

    /**
     * [uploadimg 上传方法]
     * @Author   WuSong
     * @DateTime 2017-09-12T10:51:40+0800
     * @Example  eg:
     * @param    [type]                   $data [description]
     * @param    string                   $name [description]
     * @param    string                   $path [description]
     * @return   [type]                         [description]
     */
    public function  uploadimg($data,$name='',$path='/public/uploads/hotel/'){
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
   * [authen 认证]
   * @Author   WuSong
   * @DateTime 2017-09-11T17:35:12+0800
   * @Example  eg:
   * @return   [type]                   [description]
   */
  public function authen(){
    if($this->request->isPost()){
      $data= input();
      $auData = [];
      $id = cookie('hotel_id');
      $code = session('hotel_code');
      // ##数据提交
      $auData['name'] = addslashes(input('name'));
      $auData['hotel_name']= addslashes(input('hotel_name'));
      $auData['address'] = addslashes(input('address'));
      $auData['phone'] = encode(trim(addslashes(input('post.phone'))));
      $auData['id_card'] = trim(addslashes(input('post.id_card')));
      $auData['open_name'] = addslashes(input('post.open_name'));
      $auData['bankname'] = addslashes(input('post.bankname'));
      $auData['bank_id'] = trim(addslashes(input('post.bank_id')));
      $auData['banktype'] = addslashes(input('post.banktype'));
      $auData['hotel_id']=addslashes(input('post.hotel_id'));
      $auData['create_date']=date('Y-m-d H:i:s');
      $auData['business'] =addslashes(input('post.business'));
      $auData['address_provite'] = addslashes(input('post.address_provite'));
      $arr = explode(' ', $auData['address_provite']);
      $auData['province']=$arr[0];
      $auData['city']=$arr[1];
      $auData['area']=$arr[2];
      //验证规则
      $rule = [
          // 'open_name' =>'require|chs|max:20',
          'phone' =>'require',
          'id_card' =>'require|max:20',
          'bank_id' => 'require|number',
          'business' =>'require',
          'address' =>'require',
          'hotel_name'=>'require',
          'name' =>'require',

      ];
      $message = [
          'hotel_name.require' =>'酒店名称不能为空',
          'address.require'   =>'详细地址不能为空',
          'business.require' =>'营业执照号码不能为空',
          // 'open_name.require' => '开户名不能为空',
          // 'open_name.chs'   =>'开户名只能为汉字',
          'bank_id.require' =>'银行卡号不能为空',
          'name.require' =>'姓名不能为空',
          'phone.require' =>'电话号码不能为空',
          'id_card.require' =>'身份证号不能为空',

      ];
      $validate = new Validate($rule,$message);
      $result   = $validate->check($auData);
        if(!$result){
          return returnJson(1,$validate->getError());
        }else{
          ##对公只有12位
          // if(strlen($auData['bank_id']) !=19 and strlen($auData['bank_id']) !=16 ){
          //   return returnJson(1,'银行卡号不正确');
          // }
        }

        if(!$auData['address_provite']){
          return returnJson(1,'地区信息不能为空');
        }

        $license = $this->getbase->getone('hotel',['where'=>['id'=>$id]]);
        if(!$license['license']){
          return returnJson(1,'营业执照必须上传');
        }

        if($this->getbase->getcount('hotel_authen',['where'=>['phone'=>encode(input('phone'))]])>0){
          return returnJson('1','此手机号已被注册，如忘记密码，请选择找回密码');
        }

        ##验证码匹配
        if(!input('code')) return returnJson(1,'验证不能为空');
        if((int)trim(input('code'))!=(int)$code) return returnJson('1','验证码错误');
         ##此手机号是否已经注册
        
        ##本地验证身份证号码是否正确
        if(!is_idcard($auData['id_card'])) return returnJson('1','身份证号码格式错误');
        ##接口验证身份证信息
        $re = id_card_juhe($auData['id_card'],$auData['name']);
        if(!is_array(json_decode($re))){
          $auData['id_card']=encode(addslashes($auData['id_card']));

        


        $this->getbase->getedit('hotel',['where'=>['id'=>$id]],['address'=>$auData['address'],'hotel_name'=>$auData['hotel_name'],'province'=>$auData['province'],'city'=>$auData['city'],'area'=>$auData['area']]);
          if($this->getbase->getadd('hotel_authen',$auData)){
            ##写入日志
            $this->getbase->getedit('hotel',['where'=>['id'=>$id]],['cash_status'=>1]);
                  $log = [
                    'hotel_id'=>$id,
                    'create_time'=>date('Y-m-d H:i:s'),
                    'log'=>'酒店认证申请',
                    'remarks'=>'酒店实名认证申请',
                    'ip'=>fetch_ip(),
                  ];
                $this->getbase->getadd('hotel_authen_log',$log);
               ##短信给商家
                $phone = getset('authen_hotel_phone');
                if($phone){
                  $phoneArr =explode(",", $phone);
                  if(is_array($phoneArr)){
                      foreach ($phoneArr as  $v) {
                        if($v){
                          send_phone($v,['（商家认证请求）'],'44688');
                        }
                    }
                  }
                }
                ##短信给商家
              
            return returnJson(0,'酒店实名认证已提交，请等待审核','hotel/index/index');
          }
        }
        


      }

    }
    /**
     * [upimghead 头像上传]
     * @Author   WuSong
     * @DateTime 2017-09-12T15:45:41+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
   public function upimghead(){
     if ($data = $_POST['base64']) {
          if(cookie('hotel_id')>0){
              ##插入到当前用户当中
              if($filehead = $this->uploadimg($data,rand(1,8888).'avatar'.cookie('hotel_id'),'/public/uploads/hotel/authen/')){
                  $this->getbase->getedit('hotel',['where'=>['id'=>cookie('hotel_id')]],['head'=>$filehead]);
                    return returnJson('0',$filehead);
              }else{
                return returnJson(1,'服务器异常，请稍后再试');
              }
          }
           
      }
    }

    /**
     * [loginout 退出登陆]
     * @Author   WuSong
     * @DateTime 2017-09-12T16:02:03+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function loginout(){
      $loginout = cookie('hotel_id',NULL);
      if(!$loginout){
        return returnJson('0','退出登陆成功','hotel/ucenter/login');
      }
    }

    /**
     * [delcard 解绑银行卡]
     * @Author   WuSong
     * @DateTime 2017-09-13T15:48:19+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function delcard(){
      if($this->request->isPost()){
        $id=(int)cookie('hotel_id');
        $password= input('post.password');
        // ##遍历查询
         $hotel_info = DB::table(config('database.prefix').'hotel')
                    ->alias('h')
                    ->join(config('database.prefix').'hotel_authen ha','h.id=ha.hotel_id','LEFT')
                    ->field('h.*,h.status hs,ha.*')
                    ->where('ha.hotel_id',$id)
                    ->find();      

        //查询账号相关银行卡信息
        if($this->getbase->getcount('hotel',['where'=>['password'=>encode($password),'id'=>$id]])){
          $this->getbase->getedit('hotel_authen',['where'=>['hotel_id'=>$id]],['open_name'=>'','bankname'=>'','bank_id'=>'','banktype'=>'']);
          return returnJson('0','解除绑定成功','hotel/my/index');
        }else{
          return returnJson(1,'密码错误，请重新输入');
        }

        
      }
    }


    /**
     * [addcard 添加银行卡]
     * @Author   WuSong
     * @DateTime 2017-09-13T18:14:55+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function addcard(){
      if($this->request->isPost()){
        $id= cookie('hotel_id');
        $data = input();
        $data['bankname']=addslashes(input('post.bankname'));
        $data['bank_id'] =addslashes(input('post.bank_id'));
        $data['open_name']=addslashes(input('post.open_name'));
        $data['banktype'] =addslashes(input('post.banktype'));
        // ##遍历查询
         $hotel_info = DB::table(config('database.prefix').'hotel')
                    ->alias('h')
                    ->join(config('database.prefix').'hotel_authen ha','h.id=ha.hotel_id','LEFT')
                    ->field('h.*,h.status hs,ha.*')
                    ->where('ha.hotel_id',$id)
                    ->find();
        if($this->getbase->getcount('hotel_authen',['where'=>['hotel_id'=>$id]])){
          if(strlen($data['bank_id']) !=19 and strlen($data['bank_id']) !=16 ){
            return returnJson(1,'银行卡号不正确');
          }

           $this->getbase->getedit('hotel_authen',['where'=>['hotel_id'=>$id]],['open_name'=>$data['open_name'],'bankname'=>$data['bankname'],'bank_id'=>$data['bank_id'],'banktype'=>$data['banktype']]);
           return returnJson('0','添加银行卡成功','hotel/my/index');
        }
          return returnJson(1,'添加失败');

      }
    }
    /**
     * [withdraw_cash 提现]
     * @Author   WuSong
     * @DateTime 2017-09-14T10:23:15+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function withdraw_cash(){
      if($this->request->isPost()){
        $id= cookie('hotel_id');
        if(cookie('hotel_id')<1) return returnJson('1','请先登陆!');
        $money = floatval(input('money'));
        $bank_id = addslashes(input('bank_id'));
        if($money<1) return returnJson('1','最小提现金额1元');
        ##查看当前帐户的金额是否充足
        #
        $hotelinfo = DB::table(config('database.prefix').'hotel')
                    ->alias('h')
                    ->join(config('database.prefix').'hotel_authen ha','h.id=ha.hotel_id','LEFT')
                    ->field('h.*,h.status hs,h.phone hp,ha.*')
                    ->where('h.id',$id)
                    ->find();
        if($hotelinfo['money']<$money) return returnJson('1','提现金额不能大于帐户金额');
        ##把当前提现金额冻结

        $data = [
        'money'=>$hotelinfo['money']-$money,
        'frozen_money'=>$hotelinfo['frozen_money']+$money,
        ];


        $this->getbase->getedit('hotel',['where'=>['id'=>$id]],$data);
        ##提现申请
        $withdraw_cash = [
        'hotel_id'=>cookie('hotel_id'),
        'create_date'=>date('Y-m-d H:i:s'),
        'money' =>$money,
        'status'=>1,
        'bank_id'=>$bank_id,
        ];
        $withdraw_cash_id = $this->getbase->getadd('hotel_withdraw_cash',$withdraw_cash);
        if($withdraw_cash_id){
           ##写入日志
          $log = [
            'withdraw_cash_id'=>$withdraw_cash,
            'create_time'=>date('Y-m-d H:i:s'),
            'source_money'=>$hotelinfo['money'],
            'change_money'=>$hotelinfo['money']-$money,
            'log'=>'提现申请',
            'money'=>$money,
            'remarks'=>'用户申请提现，提现金额为:'.$money,
            'hotel_id'=>cookie('hotel_id'),

          ];
          $this->getbase->getadd('hotel_withdraw_cash_log',$log);
            ##发送短信给商家
                $phone = getset('withdraw_hotel_phone');
                if($phone){
                  $phoneArr =explode(",", $phone);
                  if(is_array($phoneArr)){
                      foreach ($phoneArr as  $v) {
                        if($v){
                          send_phone($v,['(酒店端提现申请)','金额:'.$money.'元'],'44685');
                        }
                    }
                  }
                }
                ##发送短信给商家
          return returnJson(0,'提现申请成功');
        }else{
          return returnJson(1,'提现申请失败，请稍后再试');
        }
       
        
      }
    }

    /**
     * [forpass 修改密码]
     * @Author   WuSong
     * @DateTime 2017-09-18T14:19:40+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function forpass(){
      if($this->request->isPost()){
        $data = input();
        $id = cookie('hotel_id');
        $data['oldpassword']= addslashes(encode(input('post.oldpassword')));
        $data['newpassword']= addslashes(encode(input('post.newpassword')));
        $data['newpasswordok']= addslashes(encode(input('post.newpasswordok')));

        // ##匹配查询
        $pass = $this->getbase->getone('hotel',['where'=>['id'=>$id]]);

        if($data['oldpassword'] !=$pass['password']){
          return returnJson(1,'原密码不正确');
        }
        if(!$data['newpassword']){
          return returnJson(1,'新密码不能为空');
        }
        if(strlen($data['newpassword']) <7){
          return returnJson(1,'新密码不能小于6位');
        }
        if(decode($data['newpassword'])  == '123456'){
          return returnJson(1,'新密码不能过于简单');
        }
        if(!$data['newpasswordok']){
          return returnJson(1,'确认密码不能为空');
        }
        if($data['newpasswordok'] !=$data['newpassword']){
          return returnJson(1,'新密码与确认密码不一致');
        }
          $this->getbase->getedit('hotel',['where'=>['id'=>$id]],['password'=>$data['newpassword']]);
          cookie('hotel_id',NULL);
          return returnJson('0','密码修改成功密码修改成功，请重新登陆','hotel/ucenter/login');
        }
    }

    public function backpass(){
      if($this->request->isPost()){
        $data =input();
        $data['hotel_name']=input('post.hotel_name');
        $data['business'] =input('post.business');
        $data['phone'] = input('post.phone');

        if(!$data['hotel_name']){
          return returnJson(1,'酒店名称不能为空');
        }
        if(!$data['business']){
          return returnJson(1,'酒店营业执照号码不能为空');
        }
        if(!$data['phone']){
          return returnJson(1,'电话号码不能为空');
        }
        if(strlen($data['phone']) !=11){
          return returnJson(1,'电话号码不正确');
        }
           ##写入日志
          $log = [
            'hotel_name'=>$data['hotel_name'],
            'create_date'=>date('Y-m-d H:i:s'),
            'business'=>$data['business'],
            'phone'=>$data['phone'],
            'log'=>'密码找回申请',            
          ];
          $this->getbase->getadd('hotel_backpass_log',$log);
          
        return returnJson('0','找回密码申诉成功,请耐心等待','hotel/ucenter/login');

      }
    }
    
    /**
     * [getcode 短信验证]
     * @Author   WuSong
     * @DateTime 2017-09-19T17:56:12+0800
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
      session('hotel_code',$code);
      //插入表单
      $this->getbase->getadd('user_message',['time'=>time(),'code'=>$code,'phone'=>$data['phone']]);

      if($re->errmsg=="OK"&&$re->result==0){    

        return returnJson(0);
      }else{
        return returnJson(1,$re->errmsg);
      }

    }
   }
}
