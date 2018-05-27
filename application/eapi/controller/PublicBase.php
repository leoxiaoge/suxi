<?php
namespace app\eapi\controller;
use app\common\controller\ApiBase;
use think\Cache;
use think\Db;
use think\Validate;
use think\helper\Hash;
class PublicBase extends ApiBase
{


    public function _initialize()
    {
        // $database = [
        // // 数据库类型
        // 'type'           => 'mysql',
        // // 服务器地址
        
        // // 连接dsn
        // 'dsn'            => '', 
        // // 数据库连接参数
        // 'params'         => [],
        // // 数据库编码默认采用utf8
        // 'charset'        => 'utf8',
        // // 数据库表前缀
        // 'prefix'         => 'qlbl_',
        // // 数据库调试模式
        // 'debug'          => true,
        // // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
        // 'deploy'         => 0,
        // // 数据库读写是否分离 主从式有效
        // 'rw_separate'    => false,
        // // 读写分离后 主服务器数量
        // 'master_num'     => 1,
        // // 指定从服务器序号
        // 'slave_no'       => '',
        // // 是否严格检查字段是否存在
        // 'fields_strict'  => false,
        // // 数据集返回类型 array 数组 collection Collection对象
        // 'resultset_type' => 'array',
        // // 是否自动写入时间戳字段
        // 'auto_timestamp' => false,
        // // 是否需要进行SQL性能分析
        // 'sql_explain'    => false,


        //  // 'hostname'       => '10.66.224.67',
        // 'hostname'       => 'www.qiaolibeilang.com',
        // // 数据库名
        // 'database'       => 'qlbltest',
        // // 用户名
        // 'username'       => 'qlbl',
        // // 'username'       => 'qlbl',
        // // 密码
        // 'password'       => 'qlbl123456',
        // // 端口
        // 'hostport'       => '3306',//10051
        // // 'hostport'       => '3306',//10051
        // ];
        // config('database',$database);
          if($_SERVER['HTTP_HOST']!="qlbl.com"&&$_SERVER['HTTP_HOST']!="ithelp.org.cn"&&$_SERVER['HTTP_HOST']!="www.qlbl.cn"){
                if(!$this->request->isPost()){
                     return returnJson('1','database error ');  
                 }      
             }
         

       parent::_initialize();
    }
    /**
     * [cCacheTime 接口缓存生成时间,调此接口，方便生成缓存时间，前端来处理缓存]
     * @Author   Jerry
     * @DateTime 2017-10-24T10:05:18+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    protected function cCacheTime($name){
        cache($name.'cache_time',time());
    }
    /**
     * [rCacheTime 返出缓存时间戳，方便前台比对缓存，是否需要重新请求接口]
     * @Author   Jerry
     * @DateTime 2017-10-24T10:07:22+0800
     * @Example  eg:
     * @param    [type]                   $name [description]
     * @return   [type]                         [description]
     */
    public function rCacheTime(){
        $name = addslashes(input('name'));
        if(!$name) return ;
        if(cache($name.'cache_time')){
            return cache($name.'cache_time');
        }else{
            return time();
        }
        
    }

    public function  validate($rule,$data,$msg){
        $vali = new Validate($rule,$msg);
        $res  = $vali ->check($data);
        if(!$res){
            return $vali->getError();
        }
        return $res;
    }
    /**
     * 将xml转为array
     * @param string $xml
     * return array
     */
 protected function _xml_to_array($xml){
        if(!$xml){
            return false;
        }
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $data;
    }
    /**
     * [_wx_post_data 微信数据,XML（微信支付时使用）]
     * @Author   Jerry
     * @DateTime 2017-11-01T14:36:01+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
protected  function _wx_post_data(){
    $receipt = $_REQUEST;
        if($receipt==null){
            $receipt = file_get_contents("php://input");
            if($receipt == null){
                $receipt = $GLOBALS['HTTP_RAW_POST_DATA'];
            }
        }
    return $receipt;
    }
/**
     * 将参数拼接为url: key=value&key=value
     * @param $params
     * @return string
     */
protected function _ToUrlParams( $params ){
        $string = '';
        if( !empty($params) ){
            $array = array();
            foreach( $params as $key => $value ){
                $array[] = $key.'='.$value;
            }
            $string = implode("&",$array);
        }
        return $string;
    }


    /*
     * 给微信发送确认订单金额和签名正确，SUCCESS信息 -xzz0521
     */
protected function _return_success(){
    $return['return_code'] = 'SUCCESS';
    $return['return_msg'] = 'OK';
    $xml_post = '<xml>
                <return_code>'.$return['return_code'].'</return_code>
                <return_msg>'.$return['return_msg'].'</return_msg>
                </xml>';
    echo $xml_post;exit;
}

}
