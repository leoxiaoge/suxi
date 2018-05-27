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
namespace app\eapi\controller;
use think\Db;
use think\Cache;
use qcloudcos\Cosapi;
class User extends PublicBase
{
    //短信接口
    public $appid;// = 1400037030;//sdkappid
    public $appkey;// = "3472a1e245731fb5041464de63753ed6";//秘钥
    public $templId;// = 31931;//模板id

	public function _initialize()
    {
        $this->appid = config('sms_appid');
        $this->appkey = config('sms_appkey');
        $this->templId = config('sms_tempid');
        
     	parent::_initialize();  
    }
    /**
     * [info 个人中心，用户信息]
     * @Author   Jerry
     * @DateTime 2017-11-02T16:01:08+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function info(){
        $uid = (int)input('uid');
        if(!$uid) returnJson(1,'参数有误');
        $data['remainder'] = 0.00;##余额
        $coupon = $this->getbase->getcount('users_coupon',[
                                            'where'=>'uc.u_id='.$uid. ' and uc.end_time>="'.date('Y-m-d').'"',
                                            'alias'=>'uc',
                                            'join'=>[['coupon c','uc.c_id = c.id']],
                                            ]);
        $data['coupon'] = $coupon?$coupon:'0';##优惠券
            $userinfo = $this->getbase->getone('users_wx',['where'=>['id'=>$uid],'field'=>'phone']);
        $data['phone'] = $userinfo['phone']?substr(decode($userinfo['phone']), 0,3).'****'.substr(decode($userinfo['phone']), -4):'';;##手机号
            //当前时间
            $time = date("Y-m-d H:i:s",time());
            //用户持有的宿卡
            $sxCard = $this->getbase->getall('users_sx_scard',['where'=>['uid'=>$uid],'field'=>'overplus_times,end_date']);
            $sccardtime = [];
            foreach ($sxCard as $k => $v) {
                //如果该卡过期时间大于当前时间，取出
                if($v['end_date']>$time){
                    $sccardtime[] = $v['overplus_times'];
                }
            }
            //统计宿卡总次数
            $data['sxCard'] = (int)array_sum($sccardtime);
            $data['sxCard'] = $data['sxCard']?$data['sxCard']:'未购买';##宿卡
            $data['integral'] = '0';##积分
            returnJson(0,'success','',$data);
    }
    
    /**
     * [getcomment 选择不重复的评论，第次]
     * @Author   Jerry
     * @DateTime 2017-09-21T16:09:03+0800
     * @Example  eg:
     * @param    [type]                   $choosed_comment [description]
     * @return   [type]                                    [description]
     */
    public function getcomment($choosed_comment){
        $comment = [
            '洗得又干净又快，速度真的只有5小时,支持',
            '物流小哥好帅，哈哈哈！',
            '临时有事，小哥等了好久。不好意思!',
            '深圳出差，没带衣服，看到酒店的推广，试了下，真心不错，快点覆盖到别的区吧。支持',
            '陪客户去工厂，客户只带了一套。然后悲了。。。。还好有宿洗',
            '5小时到达，真没吹牛',
            '很不错的一',
            '衣服有个小洞，宿洗还帮我处理了，谢谢!',
            '很棒的一次体验！',
            '666...;',
            '很方便，出去玩了，然后回酒店就给我送过来了，好快',
            '总体还不错。值得体验',
            '出去玩方便，这样的服务太好了。',
            '还是大深圳好啊，服务就是不一样，来深圳玩，没带够衣服。玩前下单，回来后就把洗好的衣服给我了。太方便了!',
            '比酒店里面的强太多了。值得一试',
            '物流的小哥真心不错，你们辛苦了，这样说老板会给你加工资吗？哈哈!',
            '辛苦小哥了，跑了几趟！',
            '果然是专业团队，不管是流程，还是服务。棒棒哒!~',
            '比我上次在酒店的要强太多了。宿洗，果然强大!',


        ];
        $comment_number = rand(0,(count($comment)-1));
        if(in_array($comment_number, $choosed_comment)){
            self::getcomment($choosed_comment);
        }

        $data['number'] = $comment_number;
        $data['comment'] = $comment[$comment_number];
        return $data;
    }
    

    /**
     * [index 用户授权接口]
     * @Author   wb
     * @DateTime 2017-07-11T11:39:46+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function code()
    {
        $code       = input("code");
        $data['uid']  = $code;

        $rule = [
            'uid'  => 'require',
        ];

        $msg = [
            'uid.require' => '用户id必须要有',
        ];


        $result   = $this->validate($rule,$data,$msg);
        if($result !== true){
            return  returnJson(1,$result,'');
        }

        //小程序获得token从而获取用户详细信息根据小程序接口获取用户信息
        $appid      = config('appid_one');
        $secret     = config('secret_one');//'6bc4848260aa4eeab96462db29f010d2';
        $url        = "https://api.weixin.qq.com/sns/jscode2session?appid=".$appid."&secret=".$secret."&js_code="
            .$code."&grant_type=authorization_code";
        $res  = $this->http($url);
        $data =json_decode($res);
        $array = array('code'=>$data->session_key,'openid'=>$data->openid);
        return  returnJson(0,'success','',$array);

    }

    /**
     * [index 分享二维码函数 生成 用户推广二维码]
     * @Author   wb
     * @DateTime 2017-07-11T11:39:46+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function share_code(){

        $appid      = config('appid_two');
        $secret     = config('secret_two');//'6bc4848260aa4eeab96462db29f010d2';
        $id         = input('post.id');
        $data['uid']  = $id;

        $rule = [
            'uid'  => 'require',
        ];

        $msg = [
            'uid.require' => '用户id必须要有',
        ];


        $result   = $this->validate($rule,$data,$msg);
        if($result !== true){
            return  returnJson(1,$result,'');
        }
        //小程序获得token从而根据设定路劲与二维码宽度来生成小程序二维码
        $url        = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$secret;
        $res  = $this->http($url,'','GET');
        $data =json_decode($res);

        $urls = "https://api.weixin.qq.com/wxa/getwxacode?access_token=".$data->access_token;
        //用于生成个人专属小程序二维码
        $path="/pages/share_reg/share_reg?share_id=".$id;
        $width=300;
        $post_data='{"path":"'.$path.'","width":'.$width.'}';

        $result = $this->http_post($post_data,$urls);
        //创建二维码的时候 根据用户id存储名字   目前没有二维码图片目录
        $n = time().$id;
        $name =PUBLIC_PATH.'wx_user/'.$n.'.jpg' ;

        $img = file_put_contents($name, $result);

        $i_name = "https://www.qiaolibeilang.com/public/wx_user/".$n.".jpg";

        return  returnJson(0,'success1','',$i_name);


    }




    /**
     * [index http请求get]
     * @Author   wb
     */
    function http($url, $data='', $method='GET'){
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        if($method=='POST'){
            curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
            if ($data != ''){
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包

            }
        }
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        $tmpInfo = curl_exec($curl); // 执行操作
        curl_close($curl); // 关闭CURL会话

        return $tmpInfo; // 返回数据
    }

    /**
     * [index http请求post]
     * @Author   wb
     */
    function http_post($data,$url)
    {
        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL,$url);


        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

        //启用时会发送一个常规的POST请求,为1或者为true
        if(!empty($data)){
            curl_setopt($curl,CURLOPT_POST,1);
            curl_setopt($curl,CURLOPT_POSTFIELDS,$data);//需要要传送的内容
        }
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);


        $return_str = curl_exec($curl);


        curl_close($curl);
        return $return_str;
    }



    /**
     * [index 小程序用户授权接口]
     * @Author   wb
     * 授权后将用户数据入库
     */
    public function index()
    {
        $avatarUrl       = input("avatarUrl")?input("avatarUrl"):'http://www.qiaolibeilang.com/public/static/images/default_avatar/avatar-mid-img.png';
        $openId          = input("openid");
        $gender          = input("gender");
        $nickName        = input("name");

        $data['avatarUrl'] = $avatarUrl;
        $data['gender'] = $gender;
        $data['openId'] = $openId;
        $data['nickName'] = $nickName;
        // show($data);
        $rule = [
            // 'avatarUrl|图相'  => 'require',
            'gender|姓名' => 'require',
            'openId|授权id' => 'require',
            'nickName|昵称' => 'require'
        ];



        $result   = $this->validate($rule,$data);
        if($result !== true){
            return  returnJson(1,$result,'');
        }

    //如果youopenid 的情况下判断是否存在 如果存在则返回用户信息
        if(input("openid")){
            $userinfo = $this->getbase->getone('users_wx',['where'=>['oppenid'=>$openId]]);
            if($userinfo){
                return  returnJson(0,'success','',['uid'=>$userinfo['id']]);
            }
        }else{
            return  returnJson(1,'error','');
        }
    //如果不存在则新加用户 添加优惠券 如果you老会员推荐则给老会员加优惠券。 返回添加用户信息
        $data['name']        =  urlencode($nickName);
        $data['gender']      = $gender;
        $data['avatarurl']    = $avatarUrl;
        $data['unionid']      = '';
        $data['create_time']  = time();
        $data['oppenid']      = $openId;
        $userId = $this->getbase->getadd('users_wx',$data);
        if ($userId) {
            $this->getbase->getadd('users',['wxid'=>$userId]);
            //新用户送优惠券
            $conpon = $this->getbase->getone('coupon',['where'=>'id = 9','field'=>'effective_days,id']);##自定义优惠券
            if(!empty($conpon)){
                $c['u_id']      = $userId;
                $c['c_id']    = $conpon['id'];
                $uid = input("uid");
                $c['t_id']      = $uid;
                $c['address']      = input("address");
                $c['la']      = input("la");
                $c['lo']      = input("lo");
                $c['start_time']      = date('Y-m-d');
                $c['end_time']      = date('Y-m-d',strtotime('+'.trim($conpon['effective_days']).' days'));
                // show($c);
                $this->getbase->getadd('users_coupon',$c);
                //db('coupon')->where('id', $conpon['id'])->setDec('num');##减去优惠券数量
            }

            //如果有老用户则给老用户送优惠券 ,暂时取消邀请人的优惠券， 此块之前的逻辑太乱,需重新整理接口  By Jerry 2017-11-4
            if(!empty($uid)){
                // $conpon = Db::name('coupon')->where('status = 1 and type=1')->order('sort desc')->find();
                // $uid = input("uid");
                // $a['u_id']      = $uid;
                // $a['c_id']    = $conpon['id'];
                // $dbb = Db::name('users_coupon')->insert($a);
                
            }
            return  returnJson(0,'success','',['uid'=>$userId]);

        }else{
            return  returnJson(1,'服务器繁忙','');
        }

    }
    /**
     * [spread  物流端扫码注册]
     * @Author   Jerry
     * @DateTime 2017-09-07T15:48:22+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function spread(){
          $avatarUrl       = input("avatarUrl");
        $openId          = input("openid");
        $gender          = input("gender");
        $nickName        = input("name");

        $data['avatarUrl'] = $avatarUrl;
        $data['gender'] = $gender;
        $data['openId'] = $openId;
        $data['nickName'] = $nickName;

        $rule = [
            'avatarUrl'  => 'require',
            'gender' => 'require',
            'openId' => 'require',
            'nickName' => 'require'
        ];



        $result   = $this->validate($rule,$data);
        if($result !== true){
            return  returnJson(1,$result,'');
        }

    //如果youopenid 的情况下判断是否存在 如果存在则返回用户信息
        if(input("?post.openid")){
            $find = Db::name('users_wx')->where('oppenid',"$openId")->find();
            if($find){
                $array = array('uid'=>$find['id']);
                return  returnJson(1,'此活动只针对新注册的用户','',$array);
            }
        }else{
            return  returnJson(1,'error','');
        }


    //如果不存在则新加用户 添加优惠券 如果you老会员推荐则给老会员加优惠券。 返回添加用户信息
        $data['name']        =  urlencode($nickName);
        $data['gender']      = $gender;
        $data['avatarurl']    = $avatarUrl;
        $data['unionid']      = '';
        $data['create_time']  = time();
        $data['oppenid']      = $openId;

        $db = Db::name('users_wx')->insert($data);
        $userId = Db::name('users_wx')->getLastInsID();
        if ($userId) {
            ##推广分成信息
             $tag = input('tag');
            if(!$tag)return returnJson(1,'推广异常');
            switch ($tag) {
                case 'express':
                    $shareid = (int)input('shareid');
                    if($shareid){
                        $data = [
                            'uid'=>$userId,
                            'express_id'=>$shareid,
                            'create_date'=>date('Y-m-d H:i:s'),
                            'tag'=>$tag,

                        ];
                        $this->getbase->getadd('express_spread_user',$data);
                    }
                    break;
                default:
                  
                    break;
            }
            ##推广分成信息

            $datas['wxid']      = $userId;
            $dba = Db::name('users')->insert($datas);
            //判断是否有优惠券可送
            $conpon = Db::name('coupon')->where('status = 1 and type=2 and num >0')->order('sort desc')->find();

            if(!empty($conpon)){
                $c['u_id']      = $userId;
                $c['c_id']    = $conpon['id'];
                $uid = input("uid");
                $c['t_id']      = $uid;

                $c['address']      = input("address");
                $c['la']      = input("la");
                $c['lo']      = input("lo");
                $dbb = Db::name('users_coupon')->insert($c);
                db('coupon')->where('id', $conpon['id'])->setDec('num');
            }

            //如果有老用户则给老用户送优惠券

            if(!empty($uid)){
                $conpon = Db::name('coupon')->where('status = 1 and type=1')->order('sort desc')->find();
                $uid = input("uid");
                $a['u_id']      = $uid;
                $a['c_id']    = $conpon['id'];
                $dbb = Db::name('users_coupon')->insert($a);
                Cache::rm('coupon'.$uid);

            }

            if ($dbb) {
                $array = array('uid'=>$userId);
                return  returnJson(0,'success','',$array);
            }else{
                return  returnJson(1,'error','');
            }

        }else{
            return  returnJson(1,'error','');
        }
    }



    /**
     * [index 小程序登陆页面接口]
     * @Author   wb
     * @Example  eg:
     * @return   返回用户id
     */
    public function login()
    {
        $uid        = input("post.uid");
        $phone      = input("post.phone");
        $c_id      = input("post.c_id");

        $data['uid'] = $uid;
        $data['phone'] = $phone;
        $data['c_id'] = $c_id;


        $rule = [
            'uid'  => 'length:1,11',
            'phone' => 'require|number|max:25',
            'c_id' => 'number',
        ];
        $phone    = encode($phone);

        $msg = [
            'uid.length'     => '用户id长度错误',
            'phone.number'   => '手机号必须为数值',
            'phone.require' => '手机号必须要有',
            'phone.max'     => '手机号长度错误',
            'c_id.number'   => '优惠券id必须数值',
        ];


        $result   = $this->validate($rule,$data,$msg);
        if($result !== true){
            return  returnJson(1,$result,'');
        }


        //验证用户绑定手机号
        $code['is_start'] = 1;
        $db = Db::table('qlbl_user_message')
            ->where('id', $c_id)
            ->update($code);


        //如果有传入uid则是用户绑定手机号 进行修改返回成功与失败
        if(!empty($uid)){
            $res['phone'] = $phone;
            $this->getbase->getedit('users_wx',['where'=>['id'=>$uid]],$res);
            return  returnJson(3,'success','',$db);

        }
        //如果传入手机号则是用户登陆界面 判断库内是否有此手机号 有的话直接登陆并且返回信息
        $datau['phone']        = $phone;
        if(input("?post.phone")){
            $find = Db::name('users_wx')->where('phone',"$phone")->find();
            if($find){
                return  returnJson(0,'success','',$find);
            }
        }else{
            return  returnJson(1,'error','');
        }

        //如果没有则进行入库 返回用户id
        $userId = $this->getbase->getadd('users_wx',$datau);
        if ($userId) {
            $datas['wxid']      = $userId;
            $db = $this->getbase->getadd('users',$datas);
            if ($db) {
                $array = array('uid'=>$userId);
                return  returnJson(0,'success','',$array);
            }else{
                return  returnJson(1,'error','');
            }

        }else{
            return  returnJson(1,'error','');
        }

    }


    /**
     * [index 用户个人订单页面]
     * @Author   wb
     * @describe  eg:传入用户id
     * @return  返回用户评论列表
     * "status": 0,   // 0下单成功未支付-1下单成功已支付商家未接单-3商家已接单-4商家已通知快递员上门取件-5快递员已取件-6商家收到货品-7商家完成订单-8快递员已取件正在配送-9已经送达-10送达成功-11送达失败
       * 16:39:11
       * 乔利贝朗-邓通京 2017/9/22 16:39:11
       * 未支付：0 服务中：1-8 待评价：9 已完成：9-10 全部：0-11
        *乔利贝朗-邓通京 2017/9/22 16:40:15
       * 11是送达失败，当初是指衣物丢失状态
     */
    public function my_order(){
        // 1：未支付，2：服务中，3：已完成
        $u_id  = (int)input('post.u_id');
        $type = (int) input('type');
        switch ($type) {
            case '1':
                $where = "status = 0";
                break;
            case '2':
               $where = "status in(1,2,3,4,5,6,7,8)";
                break;
            case '3':
                $where = "status in(9,10)";
                break;
            default :
                $where = "status!=-1";
                break;
            
        }
        $data['uid']  = $u_id;
        $rule = [
            'uid'  => 'require|number|length:1,11',
        ];
        $msg = [
            'uid.require' => '必须要有用户id',
            'uid.length'     => '用户id长度不符',
            'uid.number'   => '用户id为数值',
        ];
        $result   = $this->validate($rule,$data,$msg);
        if($result !== true){
            return  returnJson(1,$result,'');
        }
        $where = $where." and u_id = {$u_id} and status!=12";

        $orders = $this->getbase->getall('order',['where'=>$where,'field'=>'id order_id,u_id,order_number,good_name,good_price,good_id,good_num,create_time,status','order'=>'id desc']);
        foreach ($orders as $k => $v) {
            #订单图片
            $goodsinfo = $this->getbase->getall('goods',['where'=>['id'=>['in',trim($v['good_id'])]],'field'=>'picture']);
                foreach ($goodsinfo as  $vi) {
                    $orders[$k]['pictures'][]=config('host').get_file_path($vi['picture']);
                }
            ##是否评价
            if($comment = $this->getbase->getone('order_remark',['where'=>['order_id'=>$v['order_number'],'uid'=>$v['u_id']]])){
                // show($comment);
                ##综合评星
                $orders[$k]['stars'] = ($comment['level_express']+$comment['level_express_u']+$comment['level_neat']+$comment['level_packing'])/4;
                ##是否评价
                $orders[$k]['is_eval'] = 1;

                //写入积分缓存
                $log = [
                        'integral' => +5,
                        'remarks'  =>'用户评价积分'+5,
                        'create_time' =>date('Y-m-d H:i:s'),
                        'u_id' =>$u_id,
                        'status' => 3
                    ];
                    $integral = $this->getbase->getone('users',['where'=>['uid'=>$u_id],'field'=>'integral']);
                    if(false!=$this->getbase->getadd('users_integral_log',$log)){
                        $this->getbase->getedit('users',['where'=>['uid'=>$u_id]],['integral'=>$integral+5]);
                    }


            }else{
                $orders[$k]['stars'] = 0;
                $orders[$k]['is_eval'] = 0;
            }
            
            if($orders[$k]['status']=="0"){
                $orders[$k]['status_dec'] = "未支付"; 
            }elseif(in_array($orders[$k]['status'], [1,2,3,4,5,6,7,8])){
                $orders[$k]['status_dec'] = "服务中"; 
            }elseif(in_array($orders[$k]['status'], [9,10])){
                $orders[$k]['status_dec'] = $orders[$k]['is_eval']?"已完成":"待评价"; 
            }elseif($orders[$k]['status']==11){
                $orders[$k]['status_dec'] = "配送失败";
            }
            $orders[$k]['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
            $orders[$k]['goods_num'] = array_sum(explode(",", $v['good_num']));
            // $orders[$k]['status_dec'] = date('Y-m-d H:i:s',$v['create_time']);
              unset($orders[$k]['good_id']);  
              unset($orders[$k]['good_num']);  
              unset($orders[$k]['u_id']);  
        }

       




        return returnJson(0,'success','',$orders);
        // show($orders);
     




    }

    /**
     * [phone_login 手机号码登陆]
     * @Author   Jerry
     * @DateTime 2017-07-11T19:26:07+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function phone_login(){
        $phone = (int)input('phone');
        $code = (int)input('code');##手机验证码，预留
        if(!$phone) returnJson('1','手机号码不能为空');
        if(!preg_match("/^1[34578]{1}\d{9}$/",$phone)){
            return returnJson('1','手机号格式错误');
        }
        //用户是否存在
        if($this->base->getcount('user',['where'=>['mobile'=>encode($phone)]])>0){
            $this->base->getedit('user',['where'=>['mobile'=>encode($phone)]],['last_active'=>time()]);
            $userinfo = $this->getbase->getone('user',['where'=>['mobile'=>encode($phone)],'field'=>'uid']);
            $uid = $userinfo['uid'];
        }else{
            $uid = $this->base->getadd('user',['mobile'=>encode($phone),'last_active'=>time(),'reg_time'=>time(),'user_name'=>encode($phone)]);

        }
        returnJson(0,'success',['data'=>['uid'=>$uid]]);
    }


    /**
     * [phone_login 手机号码登陆验证码]
     * @Author   Jerry
     * @DateTime 2017-07-11T19:26:07+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function user_msg_code(){
        $phone = input('post.phone');
        $data['p'] = $phone;
        $rule = [
            'p'  => 'require|number|length:10,13',
        ];


        $msg = [
            'p.require' => '必须要有手机号',
            'p.length'     => '手机号码不符',
            'p.number'   => '手机号必须是数字',
        ];


        $result   = $this->validate($rule,$data,$msg);
        if($result !== true){
            return  returnJson(1,$result,'');
        }

        //发送手机验证码
        $data  = $this->send_phone($phone);

        returnJson(0,'success','',$data);
    }


    /**
     * [index 用户个人中心]
     * @Author   wb
     * @describe  eg:传入用户id
     * @return  返回用户信息
     */
    public function user_index(){
        $uid = input('post.uid');
        $data['uid']  = $uid;

        $rule = [
            'uid'  => 'require|number|length:1,11',
        ];
        $msg = [
            'uid.require' => '没有用户id标示',
            'uid.length'     => '用户id不符',
            'uid.number'   => '用户id必须为数值',
        ];
        $result   = $this->validate($rule,$data,$msg);
          if($result !== true){
        return  returnJson(1,$result,'');
           }
        //获取用户信息
        $data = Db::name('users_wx')->field('name as u_name,phone as u_phone,avatarurl as u_img')->where('id',$uid)->find();
          if(empty($data)){
              return  returnJson(2,'无此用户','');
          }
          //查看用户是否绑定 隐藏号码
        if($data['u_phone'] != ''){
            $str = substr_replace(decode($data['u_phone']),'****',3,4);
        }
        //用户是否有评价
        $count = Db::name('order_remark')->where('uid',$uid)->count();
        //用户是否有优惠券
        $c_coupon = Db::name('users_coupon')->where('u_id='.$uid.' and status=0 ')->count();
        //组装 返回
        $array=array(
            'u_img'=>$data['u_img'],
            'u_name'=>urldecode($data['u_name']),
            'u_phone'=>$str,
            'u_price'=>'55.00',
            "u_code"=>0, ##用户积分
            "u_coupon"=> $c_coupon, ##用户优惠券
            "u_evaluate"=> $count##用户评论
        );

        return  returnJson(0,'success','',$array);

    }


    /**
     * [index 用户投诉请求接口]
     * @Author   wb
     * @describe  eg:传入用户id
     * @return  返回用户评论列表
     */
    public function complaint(){
        $uid = input('post.uid');
        $text = input('post.text');

        $data['uid']  = $uid;
        $data['text'] = $text;
        $rule = [
            'uid'  => 'require|number|length:1,11',
            'text'   => 'require|chsAlphaNum|max:25'
        ];
        $result   = $this->validate($rule,$data);
        if($result == false){
            return  returnJson(1,'参数有误','',$result);
        }
        //添加评论 返回成功与失败
        $data['uid']        = $uid;
        $data['content']      = $text;
        $data['create_time']  = time();
        $db = Db::name('user_complaint')->insert($data);

        return  returnJson(0,'success','',array('res'=>$db));
    }


    /**
     * [index 我的评价]
     * @Author   wb
     * @describe  eg:传入用户id
     * @return  返回用户评论列表
     */
    public function my_order_comment(){

            $data['uid']  = input('post.uid');
            $uid          =   $data['uid'];

            $rule = [
                'uid'  => 'require|number|length:1,11',
            ];

            $msg = [
                'uid.require' => '没有用户id标示',
                'uid.length'     => '用户id不符',
                'uid.number'   => '用户id必须为数值',
            ];

            $result   = $this->validate($rule,$data,$msg);
            if($result !== true){
                return  returnJson(1,$result,'');
            }
            ##评价数量
            $commentCount = $this->getbase->getcount('order_remark',['where'=>['uid'=>$uid],'order'=>'id desc']);
            if($commentCount){
                 if(cache('my_order_comment_count'.$uid)==$commentCount&&cache('my_order_comment_lists'.$uid)&&cache('my_order_comment_count'.$uid)>0){
                $data = cache('my_order_comment_lists'.$uid);
                }else{
                    cache('my_order_comment_count'.$uid,$commentCount);
                    //查看我的评价获取评价信息
                   $data = Db::name('order_remark')->where('uid',$uid)->order("id desc")->select();
                    if(is_array($data)&&count($data)>0){
                        //对每个评价的星级均分 返回评价列表
                       foreach ($data as &$v){
                           $stars =  $v['level_express']+$v['level_express_u']+$v['level_neat']+$v['level_packing'];

                           unset($v['level_express']);
                           unset($v['level_express_u']);
                           unset($v['level_neat']);
                           unset($v['level_packing']);
                           $time =  date('Y-m-d H:i:s',$v['order_time']);
                           $v['order_times'] = $this->time_tran($time);
                           $t = explode(' ',$time);
                           $v['order_time'] =$t[0];
                           ##评价图片
                           $imgs = $this->getbase->getall('order_remark_img',['where'=>['item_id'=>$v['id'],'item_type'=>'order_remark'],'field'=>'img']);
                           $img = [];
                           if(is_array($imgs)&&count('imgs')>0){
                            foreach ($imgs as $ki => $vi) {
                                $img[] = $vi['img'];
                            }
                           }
                           ##评价图片
                           $v['img'] = $img;
                           $v['stars'] = $stars/4;
                       }
                       cache('my_order_comment_lists'.$uid,$data); 
                    }else{
                        return  returnJson(2,'error','');
                    }
                }
            }else{
                return  returnJson(2,'error','');   
            }
           
            return  returnJson(0,'success','',array('res'=>$data));
    }


    /**
     * [index 订单评价评价]
     * @Author   wb
     * @describe  eg:传入用户订单id
     * @return  返回评论
     */
    public function my_o_comment(){


        $oid  = input('post.oid');
        //我的评价 对单个的评价信息返回
        $data = Db::name('order_remark')->where(" order_id ='".$oid."'")->find();
        if(!$data){
            return returnJson(0,'没有评价');
        }
        if(!cache('my_o_comment_'.$oid)){
            $stars =  $data['level_express']+$data['level_express_u']+$data['level_neat']+$data['level_packing'];
            unset($data['level_express']);
            unset($data['level_express_u']);
            unset($data['level_neat']);
            unset($data['level_packing']);

            $time =  date('Y-m-d H:i:s',$data['order_time']);
            $data['order_times'] = $this->time_tran($time);
            $t = explode(' ',$time);
            $data['order_time'] =$t[0];
              ##评价图片
               $imgs = $this->getbase->getall('order_remark_img',['where'=>['item_id'=>$data['id'],'item_type'=>'order_remark'],'field'=>'img']);
               if(is_array($imgs)&&count('imgs')>0){
                foreach ($imgs as $k => $v) {
                    $img[] = $v['img'];
                }
               }else{
                $img = [];
               }
               ##评价图片
            $data['img'] = $img;
            $data['stars'] = $stars/4;
            cache('my_o_comment_'.$oid,$data);
        }else{  
            $data = cache('my_o_comment_'.$oid);
        }
        return  returnJson(0,'success','',$data);
    }




    /**
     * [index 腾讯云 COS 对象存储 demo]
     * @Author   wb
     */
    public function demo(){
        include '/www/web/demo/public_html/extend/include.php';
        include '/www/web/demo/public_html/extend/src/qcloud\cos\api/api.php';
        $bucket = 'upload';
        $src = './hello.txt';
        $dst = '/testfolder/hello.txt';
        $dst2 = 'hello2.txt';
        $folder = '/upload';


        $config = array(
            'app_id' => '1253900967',
            'secret_id' => 'AKIDpnt7VyuKBCIXrFtNbxOuaOIJQmEms9yt',
            'secret_key' => 'BXyaJ8kTH9COf0ZCxlfhwG0zEXvNuUdr',
            'region' => 'gz',
            'timeout' => 60
        );
        $cosApi = new \Api($config);

        // 创建文件夹
        $ret = $cosApi->createFolder($bucket, $folder);
        var_dump($ret);
        return  returnJson(1,'success','',array('res'=>$ret));

    }

    /**
     * [index 用户查看地址管理]
     * @Author   jerry
     * @describe  eg:传入用户id
     * @return  返回用户地址列表
     */
    public function user_address(){
        $aid = (int) input('aid');
        $uid = (int) input('uid');
        if(!$uid){
            returnJson(1,'参数有误');
        }
        if($aid){
            //查看单个地址信息
            $data = Db::name('address')->where('id='.$aid.' and stat=0')->field('address_info,sex,u_name,u_phone,id,sex,la,lo,address')->find();
            $data['u_phone'] = decode($data['u_phone']);
            return  returnJson(0,'success','',$data);
        }
        $data['uid']  = $uid;
        $rule = [
            'uid'  => 'require|number|length:1,11',
        ];
        $msg = [
            'uid.require' => '没有用户id标示',
            'uid.length'     => '用户id不符',
            'uid.number'   => '用户id必须为数值',
        ];

        $result   = $this->validate($rule,$data,$msg);
        if($result !== true){
            return  returnJson(1,$result,'');
        }
        //查看用户的地址信息
        //field('address_info,u_name,u_phone,id')
        $data = Db::name('address')->where('uid='.$data['uid'].' and stat=0')->select();
        $array = array();
        foreach($data as $k=> $v){
            $array[$k]['u_name']= $v['u_name'];
            $array[$k]['id']= $v['id'];
            $array[$k]['address']= $v['address'];
            $array[$k]['address_info']= $v['address_info'];
            $array[$k]['u_phone']= decode($v['u_phone']);//解密
            $array[$k]['lo']= $v['lo'];
            $array[$k]['la']= $v['la'];//解密
        }

        return  returnJson(0,'success','',$array);
    }


   


    /**
     * [index 修改/新增用户地址]
     * @Author   wb
     * @describe  eg:按照type区分修改类型0为查看要修改的数据信息1为提交修改的数据信息
     * @return  修改成功后的数据信息
     */
    public function up_address(){
        $id    = intval(input('post.id'));
        $data['u_name']   = input('post.name');
        $data['u_phone']  = input('post.phone');
        $data['address'] = input('post.address');
        $data['address_info'] = input('post.address_info');
        $data['sex']    = (int)input('post.sex');
        $data['lo'] = input('post.lo');
        $data['la']    = input('post.la');
        $data['uid']    = (int)input('post.uid');
        //判断是否有id  如果有id 则修改 如果没有则加入 一个增加和修改的数据操作
       if($id > 0 and !empty($id)){
           $rule = [
               'u_name'   => 'require|chsAlphaNum|max:25',
               'u_phone' => 'require|number|max:25',
               'address_info'   => 'require',
               'sex' => 'require|number|in:1,0',
               'lo' => 'require',
               'la' => 'require',
           ];

           $msg = [
               'uid.require' => '没有用户id标示',
               'uid.length'     => '用户id不符',
               'u_name.chsAlphaNum'   => '用户名称不符规范',
               'u_name.require' => '没有用户名称',
               'u_phone.max'     => '手机号长度错误',
               'u_phone.number'   => '手机号必须数值',
               'u_phone.require' => '手机号必须要有',
               'address_info.require' => '地址信息必须要有',
               'sex.in'     => '性别错误',
               'sex.number'   => '性别必须为数值',
               'sex.require' => '性别必须要有',
               'lo.require' => '经度必须要有',
               'la.require' => '纬度必须要有',

           ];

            $result   = $this->validate($rule,$data,$msg);
            if($result == false){
                return  returnJson(1,'参数有误','',$result);
            }
            $data['u_phone'] = encode($data['u_phone']);
            Db::table('qlbl_address')
                ->where('id', $id)
                ->update($data);
            $data['id']    = $id;
            return  returnJson(0,'修改成功','',$data);
        }else{

            $rule = [
                'uid'  => 'require|length:1,11',
                'u_name'   => 'require|chsAlphaNum|max:25',
                'u_phone' => 'require|number|max:25',
                'address_info'   => 'require',
                'sex' => 'require|number|in:1,0',
                'lo' => 'require',
                'la' => 'require',
            ];
            $msg = [
                'uid.require' => '没有用户id标示',
                'uid.length'     => '用户id不符',
                'u_name.chsAlphaNum'   => '用户名称不符规范',
                'u_name.require' => '没有用户名称',
                'u_phone.max'     => '手机号长度错误',
                'u_phone.number'   => '手机号必须数值',
                'u_phone.require' => '手机号必须要有',
                'address_info.require' => '地址信息必须要有',
                'sex.in'     => '性别错误',
                'sex.number'   => '性别必须为数值',
                'sex.require' => '性别必须要有',
                'lo.require' => '经度必须要有',
                'la.require' => '纬度必须要有',

            ];
            $result   = $this->validate($rule,$data,$msg);
            if($result !== true){
                return  returnJson(1,$result,'');
            }

            $find = Db::name('users_wx')->where('id',$data['uid'])->find();
            if($find['phone'] == ''){
                $res['phone'] = encode($data['u_phone']);
                Db::table('qlbl_users_wx')
                    ->where('id', $data['uid'])
                    ->update($res);
            }



            $data['stat'] = 0;
            $data['u_phone'] = encode($data['u_phone']);
            $db = Db::name('address')->insert($data);
            $userId = Db::name('address')->getLastInsID();
            $data['u_phone'] = decode($data['u_phone']);
            $data['id'] = $userId;
            return  returnJson(0,'新增成功','',$data);

        }
    }

    /**
     * [index 删除用户地址]
     * @Author   wb
     * @describe  eg:传入用户id与地址id
     * @return  修改成后返回1成功0失败
     */
    public function del_address(){
        $ad    = intval(input('post.address_id'));//地址id
        $id    = intval(input('post.uid'));
        $data['stat']    = 1;
        $res   = Db::table('qlbl_address')
            ->where('id='.$ad.' and uid='.$id)
            ->update($data);
        return  returnJson(0,'success','',$res);
    }

    /**
     * [index 删除用户订单]
     * @Author   wb
     * @describe  eg:传入用户id与地址id
     * @return  修改成后返回1成功0失败
     */
    public function del_order(){
        $data['id']  = input('post.id');

        $rule = [
            'id'  => 'require|number|length:1,11',
        ];


        $msg = [
            'id.length'     => '用户id长度错误',
            'id.number'   => '用户id必须为数值',
            'id.require' => '用户id必须要有',
        ];


        $result   = $this->validate($rule,$data,$msg);
        if($result !== true){
            return  returnJson(1,$result,'');
        }

        $find = Db::name('order')->where('id',$data['id'])->find();

        //直接删除缓存中订单
        $cache = Cache::get('data'.$find['u_id']);






        $arr['status'] =12;
        $arr['msg'] ='用户删除订单';
        $db = Db::table('qlbl_order')
            ->where('id', $data['id'])
            ->update($arr);

        if(!empty($cache)){
            unset($cache[$data['id']]);
            Cache::set('data'.$find['u_id'],$cache,27200);
           // return  returnJson(0,'cache_success','',$cache);
        }else{
            $array = $this->cache_myorder(2,$find['u_id']);
            if(empty($array)){
                return  returnJson(0,'','',$array);
            }
            Cache::set('data'.$find['u_id'],$array,27200);

          //  return  returnJson(0,'cache_success','',$array);
        }




         return  returnJson(0,'成功','',$db);
    }


    /**
     * [index 时间差计算]
     * @Author   wb
     * @describe  eg:传入时间戳
     * @return 距离now的时间差
     */
    public function time_tran($the_time) {
        $now_time = date("Y-m-d H:i:s", time());
        //echo $now_time;
        $now_time = strtotime($now_time);
        $show_time = strtotime($the_time);
        $dur = $now_time - $show_time;
        if ($dur < 0) {
            return $the_time;
        } else {
            if ($dur < 60) {
                return $dur . '秒前';
            } else {
                if ($dur < 3600) {
                    return floor($dur / 60) . '分钟前';
                } else {
                    if ($dur < 86400) {
                        return floor($dur / 3600) . '小时前';
                    } else {
                        if ($dur < 259200) {//3天内
                            return floor($dur / 86400) . '天前';
                        } else {
                            $t = explode(' ',$the_time);
                            return $t[0];
                        }
                    }
                }
            }
        }
    }


    /**
     *短信发送接口
     */
    public function send_phone($phone){
        //加载第三方提供类
        include EXTEND_PATH.'SmsSender.php';
        include_once EXTEND_PATH.'SmsTools.php';

            // 请根据实际 appid 和 appkey 进行开发，以下只作为演示 sdk 使用

            $singleSender = new \SmsSender($this->appid, $this->appkey);
            //验证码
            $code   = mt_rand(1000,9999);
            $params = array( $code, "3");
            $result = $singleSender->sendWithParam("86", $phone, $this->templId, $params, "宿洗", "", "");
            $rsp = json_decode($result);
            //发送成功后
            if($rsp->errmsg == 'OK'){
                $data['time'] = time();
                $data['code'] = $code;
                $data['is_start'] = 0;
                $data['phone'] = $phone;
                $db = Db::name('user_message')->insert($data);
                $userId = Db::name('user_message')->getLastInsID();
                $data['id'] = $userId;
                return  returnJson(0,'success','',$data);
            }

    }
  

    /**
     *用户分享接口
     */
    public function new_usershare(){

        $data['id']  = input('post.uid');

        $rule = [
            'id'  => 'require|number|length:1,11',
        ];


        $msg = [
            'id.length'     => '用户id长度错误',
            'id.number'   => '用户id必须为数值',
            'id.require' => '用户id必须要有',
        ];


        $result   = $this->validate($rule,$data,$msg);
        if($result !== true){
            return  returnJson(1,$result,'');
        }
        $conpon = Db::name('coupon')->where('status = 1 and type=2')->order('sort desc')->find();


        $array= array();
        $array['coupon']['discount'] = $conpon['discount'];
        $array['coupon']['expire'] = $conpon['expire'];
        $array['coupon']['l_price'] = $conpon['l_price'];
        $array['coupon']['id'] = $conpon['id'];


        $coupon = Db::name('users_coupon')->where('t_id='.$data['id'].' and status=0 ')->select();
        //获取某人分享后 领取某人的优惠券的新用户
        foreach ($coupon as $k => $v){
           $kes = Db::name('users_wx')->where('id',$v['u_id'])->find();
            $array['user_list'][$k]['id'] = $kes['id'];
            $time =  date('Y-m-d H:i:s',$kes['create_time']);
            $array['user_list'][$k]['time'] = $this->time_tran($time);
            $array['user_list'][$k]['name'] = $kes['name'];
            $array['user_list'][$k]['price'] = 20;
            $array['user_list'][$k]['img'] = $kes['avatarurl'];
            //"id": 52,//用户id
            //    "time": "20分钟前",//多久前领取
            //    "name": "saka",//名称
            //    "price": 5,//领取的优惠券面额
            //    "img": 5//头像
        }


        return  returnJson(0,'success','',$array);

    }



    public function code_extension(){
        //生成推广人员自己的二维码页
        $appid      = config('appid_two');
        $secret     = config('secret_two');//'6bc4848260aa4eeab96462db29f010d2';
        $url        = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$secret;
        $res  = $this->http($url,'','GET');
        $data =json_decode($res);
        $urls = "https://api.weixin.qq.com/wxa/getwxacode?access_token=".$data->access_token;
        $path="/pages/share_code/share_code";
        $width=300;
        $post_data='{"path":"'.$path.'","width":'.$width.'}';
        $result = $this->http_post($post_data,$urls);
        //创建二维码的时候 根据用户id存储名字   目前没有二维码图片目录
        $n = 'code'.time();
        $name =PUBLIC_PATH.'wx_user/'.$n.'.jpg' ;
        $img = file_put_contents($name, $result);
        $i_name = config('host')."/public/wx_user/".$n.".jpg";
        return  returnJson(0,'success1','',$i_name);


    }




}