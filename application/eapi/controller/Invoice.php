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
use app\common\controller\Base;
use think\db;
use think\Cache;
// use think\
// use qcloudcos\Cosapi;
class Invoice extends PublicBase
{

    public function _initialize()
    {
        parent::_initialize();

    }
    
    /**
     * [invoice 发票首页]
     * @Author   WuSong
     * @DateTime 2017-10-13T15:28:40+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function invoice()
    {  

        $u_id = (int) input('uid');
        $order =Db::table(config('database.prefix').'order')
                    ->where('u_id',$u_id)
                    ->where('status=9 OR status=10')
                    ->where('invoice_status','0')
                    ->field('order_number,good_name,take_time,order_price')
                    ->select();


        return  returnJson(0,'success','',$order);
    }

    /**
     * [money_sum 统计被选中订单价格]
     * @Author   WuSong
     * @DateTime 2017-10-13T11:31:12+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function money_sum(){
        $data=input();
        $u_id =(int)input('uid');
        $order_id =addslashes($data['order_id']);
        //##统计总价格
        $money_statistics =  $this->getbase->getsum('order',['where'=>"id in($order_id)",'field'=>'order_price']);

         return  returnJson(0,'success','',$money_statistics);
    }

    /**
     * [users_invoice 添加发票信息]
     * @Author   WuSong
     * @DateTime 2017-10-12T16:10:00+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function users_invoice(){

        $requests       = input();
        $uid            = (int)$requests['uid'];
        $order_id       = implode(',',$requests['post.order_id']);
        $money          =$requests['money'];
        // $money          = addslashes($this->getbase->getsum('order',['where'=>"id in($order_id)",'field'=>'order_price']));
        $rise           = addslashes($requests['rise']);
        $distinguish    = addslashes($requests['distinguish']);
        $content        = addslashes(getset($requests['invoice']));
        $remark         = addslashes($requests['remark']);
        $users_name     = addslashes($requests['users_name']);
        $users_phone    = addslashes($requests['users_phone']);
        $address        = addslashes($requests['address']);
        $address_provite= $requests['address_provite'];
        $email          = addslashes($requests['user_email']);
        //##拆分省市区
        $arr            = explode(',', $address_provite);
        $province       = addslashes($arr[0]);
        $city           = addslashes($arr[1]);
        $area           = addslashes($arr[2]);

        //##统计总价格
       

        //提交确认发票信息 

        $data['u_id']            = $uid;                 //## 用户ID
        $data['rise']            = $rise;                //## 发票抬头
        $data['distinguish']     = $distinguish;         //## 纳税人识别号
        $data['content']         = $content;             //## 发票内容
        $data['money']           = $money;               //## 发票金额
        $data['remark']          = $remark;              //## 备注
        $data['users_name']      = $users_name;          //## 姓名
        $data['users_phone']     = encode($users_phone); //## 电话
        $data['province']        = $province;            //## 省
        $data['city']            = $city;                //## 市
        $data['area']            = $area;                //## 区
        $data['address']         = $address;             //## 详细地址
        $data['create_time']     = date('Y-m-d H:i:s');  //## 申请发票时间
        $data['order_id']        = $order_id;            //## 开发票的订单ID
        $data['user_email']      = $email;               //## 电子邮件
  
        //验证规则
        $rule = [
            'u_id'               => 'require',
            'rise'               => 'require',
            'distinguish'        => 'require',
            'users_name'         => 'require',
            'users_phone'        => 'require',
            'address'            => 'require',
            'user_email'         => 'email',
        ];


        $msg = [
            'u_id.require'       => '用户ID必须要有' ,
            'rise.require'       => '发票抬头必须要有',
            'distinguish.require'=> '纳税人识别号必须要有',
            'address.require'    => '详细地址不能为空',
            'users_name.require' => '收件人姓名必须要有',
            'users_phone.require'=> '收件人电话必须要有',
            'money.require'      => '金额不能为空',
            'email.email'        => '邮件格式不对',


        ];

        // //tp5验证规则
        $result   = $this->validate($rule,$data,$msg);
        if($result !== true){
            return  returnJson(1,$result,'');
        }


       if(false!==$this->getbase->getadd('users_invoice',$data)){
         $this->getbase->getedit('order',['where'=>"id in($order_id)"],['invoice_status'=>'1']);
            return  returnJson(0,'success','',$data);
       }  
        
    }

    /**
     * [invoice_all 发票列表]
     * @Author   WuSong
     * @DateTime 2017-10-30T15:54:26+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function invoice_all(){
        $requests = input();
        //查询该用户下的所有发票
        $data  =  $this->getbase->getall('users_invoice',['where'=>['u_id'=>$requests['uid']]]);

        return returnJson(0,'success','',$data);

    }

    /**
     * [invoice_details 发票详情]
     * @Author   WuSong
     * @DateTime 2017-10-30T15:59:27+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function invoice_details(){
        $requests = input();
        $data = $this->getbase->getone('users_invoice',['where'=>['id'=>$requests['invoice_id']]]);
        //统计改发票包含几个订单
        $order = array_sum(explode(',',$data['order_id']));
        $data['orders']=$order;
        return returnJson(0,'success','',$data);
    }

    /**
     * [invoice_orders 发票内容]
     * @Author   WuSong
     * @DateTime 2017-10-30T16:35:45+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function invoice_orders(){
        $requests = input();
        $data = $this->getbase->getone('users_invoice',['where'=>['id'=>$requests['invoice_id']]]);
        $order_id = $data['order_id'];
        $orders = $this->getbase->getall('order',['where'=>"id in($order_id)"]);
        foreach ($orders as $k => $v) {
                   //## 简单处理订单ID，数量
            $good_id =trim($v['good_id'],',');
            $good_num =$v['good_num'];

            //历史商品ID 数量
            $good_info= [];
            $goods_id = explode(',',trim($v['good_id'],'-1'));
            $goods_num = explode(',',trim($v['good_num'],'-1'));

            foreach ($goods_id as $ka =>$va) {
                   
                    $good_info[$va]= $goods_num[$ka];
            }

            //查找订单商品ID对应的商品数据
            $goods_info =  $this->getbase->getall('goods',['where'=>"id in($good_id)",'field'=>'id,name,price,picture,catid'],'LEFT');
              foreach ($goods_info as $ke => $ve) {
                $ve['good_id'] =$goods_id[$ka];
                $ve['picture']= get_file_path($ve['picture']);
                $ve['num']  = $good_info[$ve['id']];
                $ve['prices']= $ve['num']*$ve['price'];
                $ve['express_id'] = $id;
                $data['goods_info'][]=$ve;
              }

            //统计商品总数量
           $data['goods_num_all'] =  array_sum(explode(',',trim($good_num,'-1')));


            
        }
        return returnJson(0,'success','',$data);   

    }
}
