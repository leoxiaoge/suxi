<?php 
use think\Cache;
use think\Config;
use think\Cookie;
use think\Db;
use think\Debug;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\Lang;
use think\Loader;
use think\Log;
use think\Request;
use think\Response;
use think\Session;
use think\Url;
use think\View as ViewTemplate;

/**
 * [build description]
 * @Author   Jerry
 * @DateTime 2017-04-11T14:21:03+0800
 * @Example  eg:
 * @param    [type]                   $name [description]
 * @param    [type]                   $init [description]
 * @param    [type]                   $new  [description]
 * @return   [type]                         [description]
 */
function build($name){
    // show($name);
    return new \app\common\build\builder($name); // 返回全新对象
}

if (!function_exists('clear_js')) {
    /**
     * 过滤js内容
     * @param string $str 要过滤的字符串
     * @author 
     * @return mixed|string
     */
    function clear_js($str = '')
    {
        $search ="/<script[^>]*?>.*?<\/script>/si";
        $str = preg_replace($search, '', $str);
        return $str;
    }
}
if (!function_exists('load_static')) {
    /**
     * 加载静态资源
     * @param string $static 资源名称
     * @param string $type 资源类型
     * @author 
     * @return string
     */
    function load_static($static = '', $type = 'css',$minify='true')
    {
        ##开启minify
        // show($minify);
        // $minify = $minify=="false"?false:true; 
        $minify = $minify=="false"?false:true; 
        // $minify = true;
        $assets_list = config($static);
        if($minify){
            $assets_list = !is_array($assets_list) ? $assets_list : implode(',', $assets_list);
            $url   = '/public/min/?f=';
            $result = $type=='css'?'<link rel="stylesheet" href="'.$url.$assets_list.'">':'<script src="'.$url.$assets_list.'"></script>';
            $result = $result."\n";
        }else{ 
        $result = '';
            foreach ($assets_list as $item) {
                if ($type == 'css') {
                    $result .= '<link rel="stylesheet" href="'.$item.'">'."\n";
                } else {
                    $result .= '<script src="'.$item.'"></script>'."\n";
                }
            } 
        }
       // echo "string";
       
        return $result;
    }
}
if (!function_exists('load_static_default')) {
    /**
     * [load_static_default 加载默认的静态资源]
     * @Author   Jerry
     * @DateTime 2017-05-03T11:50:31+0800
     * @Example  eg:
     * @param    [type]                   $path         [description]
     * @param    string                   $publicStatic [description]
     * @return   [type]                                 [description]
     * // echo "当前模块名称是" . $request->module();
     *  // echo "当前控制器名称是" . $request->controller();
     *   // echo "当前操作名称是" . $request->action();
     *   {:load_static_default('/jpublic/blog/')}
     */
    function load_static_default($path,$publicStatic='global'){
        $request = Request::instance();
        $file = strtolower($request->module()."-".$request->controller()."-".$request->action());
        $files = [
            $publicStatic.".css",
            $publicStatic.".js",
            $file.".css",
            $file.".js",
        ];
        // show($files);
        //路径生成 $path+$controller+文件类型
        //先检查文件是否存在,存在才生成
        // 默认global文件处理
        $result = "";
        foreach ($files as $key => $v) {
                 switch (substr($v, -3)) {
                       case 'css':
                            if(file_exists(".".$path.'css/'.$v)){
                               $result .= '<link rel="stylesheet" href="'.$path.'css/'.$v.'">'."\n"; 
                            }
                           break;
                        case '.js':
                        if(file_exists(".".$path.'js/'.$v)){
                           $result .= '<script src="'.$path.'js/'.$v.'"></script>'."\n"; 
                        }
                        break;
                   }  
            
        }
        return $result;
    }    
}
if (!function_exists('minify')) {
    /**
     * 合并输出js代码或css代码 需要minify插件支付
     * @param string $type 类型：group-分组，file-单个文件，base-基础目录
     * @param string $files 文件名或分组名
     * @author 
     */
    function minify($type = '',$files = '')
    {
        $files = !is_array($files) ? $files : implode(',', $files);
        $url   = '/public/min/?';

        switch ($type) {
            case 'group':
                $url .= 'g=' . $files;
                break;
            case 'file':
                $url .= 'f=' . $files;
                break;
            case 'base':
                $url .= 'b=' . $files;
                break;
        }
        echo $url;
    }
}
if (!function_exists('parse_attr')) {
    /**
     * 解析配置
     * @param string $value 配置值
     * @return array|string
     */
    function parse_attr($value = '') {
        $array = preg_split('/[,;\r\n]+/', trim($value, ",;\r\n"));
        if (strpos($value, ':')) {
            $value  = array();
            foreach ($array as $val) {
                list($k, $v) = explode(':', $val);
                $value[$k]   = $v;
            }
        } else {
            $value = $array;
        }
        return $value;
    }
}

if (!function_exists('get_file_path')) {
    /**
     * 获取附件路径
     * @param int $id 附件id
     * @author 
     * @return string
     */
    function get_file_path($id = 0)
    {
        $path = model('asset/attachment')->getFilePath($id);
        if (!$path) {
            return "/public/static/images/default_avatar/none.png";
        }
        return $path;
    }
}
if (!function_exists('get_file_name')) {
    /**
     * 根据附件id获取文件名
     * @param string $id 附件id
     * @author 
     * @return string
     */
    function get_file_name($id = '')
    {
        $name = model('asset/attachment')->getFileName($id);
        if (!$name) {
            return '没有找到文件';
        }
        return $name;
    }
}
if (!function_exists('get_thumb')) {
    /**
     * 获取图片缩略图路径
     * @param int $id 附件id
     * @author 
     * @return string
     */
    function get_thumb($id = 0)
    {
        $path = model('asset/attachment')->getThumbPath($id);
        if (!$path) {
            return "/public/static/images/default_avatar/none.png";
        }
        return $path;
    }
}
if (!function_exists('get_location')) {
    /**
     * 获取当前位置
     * @author 
     * @return mixed
     */
    function get_location()
    {
        // $location = model('common/node')->getLocation();
        // return $location;
    }
}

/**
 * 处理插件钩子
 * @param string $hook   钩子名称
 * @param mixed $params 传入参数
 * @return void
 */
function hook($hook, $params = array()) {
    \think\Hook::listen($hook, $params);
}
/**
 * 插件显示内容里生成访问插件的url
 * @param string $url url
 * @param array $param 参数
 * 
 */
function addons_url($url, $param = array()) {
    $url        = parse_url($url);
    $case       = config('URL_CASE_INSENSITIVE');
    $addons     = $case ? parse_name($url['scheme']) : $url['scheme'];
    $controller = $case ? parse_name($url['host']) : $url['host'];
    $action     = trim($case ? strtolower($url['path']) : $url['path'], '/');

    /* 解析URL带的参数 */
    if (isset($url['query'])) {
        parse_str($url['query'], $query);
        $param = array_merge($query, $param);
    }

    /* 基础参数 */
    $params = array(
        'mc' => $addons,
        'op' => $controller,
        'ac' => $action,
    );
    $params = array_merge($params, $param); //添加额外参数

    return \think\Url::build('index/addons/execute', $params);
}
/**
 * 获取插件类的类名
 * @param strng $name 插件名
 */
function get_addon_class($name) {
    $class = "\\addons\\" . strtolower($name) . "\\{$name}";
    return $class;
}

/**
 * 获取插件类的配置文件数组
 * @param string $name 插件名
 */
function get_addon_config($name) {
    $class = get_addon_class($name);
    if (class_exists($class)) {
        $addon = new $class();
        return $addon->getConfig();
    } else {
        return array();
    }
}
//默认上传图片
function default_upload_img($var){
    return $var?$var:"/public/static/images/icon/Upload.png";
    
}
//默认图像
function default_avatar_img($var){
    return $var?$var:"/public/static/images/default_avatar/avatar-mid-img.png";
}

/**
 * [send_mail description]
 * @Author   Jerry
 * @DateTime 2017-04-17T14:01:53+0800
 * @Example  eg:
 * @param    [type]                   $customsmail [description]
 * @param    [type]                   $data        [subject,content ...]
 * @param    [type]                   $template    [description]
 * @return   [type]                                [description]
 */
 function send_mail($customsmail,$data,$template='public'){
        $res = action('common/send/mail',['customsmail'=>$customsmail,'data'=>$data,'template'=>$template]);
        return $res;
} 
/**
 * [formatArr 格式化数组,让适应tmake的SELECT]
 * @Author   Jerry
 * @DateTime 2017-04-21T18:11:42+0800
 * @Example  eg:
 * @param    [type]                   $arry [description]
 * @return   [type]                         [description]
 */
function formatArr($array,$key_name,$value_name,$default=[]){
    $formatArr = is_array($default)?$default:[];
    foreach ($array as $k => $v) { 
        $formatArr[$v[$key_name]] = $v[$value_name];
    }
    return $formatArr;
}

if (!function_exists('returnJson')) {
    /**
     * 获取\think\response\Json对象实例
     * @param mixed   $data 返回的数据
     * @param integer $code 状态码
     * @param array   $header 头部
     * @param array   $options 参数
     * @return \think\response\Json
     */
        function returnJson($code = 0,$msg="",$url = null, $data = '', $wait = 3, array $header = [])
        {   
            if (is_null($url)) {
                $url = Request::instance()->isAjax() ? '' : 'javascript:history.back(-1);';
            } elseif ('' !== $url) {
                $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : Url::build($url);
            }
            $result = [
                'code' => $code,
                'msg'  => empty($msg)?($code<1?'操作成功':lang($code)):$msg,
                'data' => $data,
                'url'  => $url,
                'wait' => $wait,
            ];

          /*$type =  (Request::instance()->isAjax()) ? Config::get('default_ajax_return') : Config::get
          ('default_return_type');

            if ('html' == strtolower($type)) {
                $result = ViewTemplate::instance(Config::get('template'), Config::get('view_replace_str'))
                    ->fetch(Config::get('dispatch_error_tmpl'), $result);
            }
          */
            $response = Response::create($result, Config::get('default_ajax_return'))->header($header);// $type
            throw new HttpResponseException($response);

            // $datas['status'] = $status;
            // $datas['code'] = $status;
            // $datas['msg'] = $msg?$msg:($status<1?"操作成功":lang($status));
            // $datas['data'] = $data;
            // $datas['url']  =$url;
            // $datas['wait'] = 3;
            // die(json_encode($datas));
             // return Response::create($datas, 'json')->header([]);
        }
}
if (!function_exists('systemLog')) {
    /**
     * [systemLog 系统日志]
     * @Author   Jerry
     * @DateTime 2017-05-02T09:56:00+0800
     * @Example  eg:
     * @param    [type]                   $uid      [description]
     * @param    [type]                   $username [description]
     * @param    [type]                   $log      [description]
     * @param    string                   $remark   [description]
     * @return   [type]                             [description]
     */
    function systemLog($uid,$user_name,$log,$tag,$remark=""){
        $data = [
        'uid'               =>$uid,
        'ip'                =>fetch_ip(),
        'log'               =>$log,
        'url'               =>$_SERVER['REQUEST_URI'],
        'create_date'       =>date('Y-m-d H:i:s',time()),
        'user_name'         =>$user_name,
        'remark'            =>$remark,
        'http_user_agent'   =>$_SERVER['HTTP_USER_AGENT'],
        'http_accept'       =>$_SERVER['HTTP_ACCEPT'],
        'http_host'         =>$_SERVER['HTTP_HOST'],
        'tag'               =>$tag,
        ];
        model('base')->getadd('system_log',$data);
    }

}

if (!function_exists('turl')) {
    /**
     * turl(thinkask URL生成器)生成 
     * @param string        $url 路由地址
     * @param string|array  $vars 变量
     * @param bool|string   $suffix 生成的URL后缀
     * @param bool|string   $domain 域名
     * @return string
     */
    function turl($url = '', $vars = '', $suffix = true, $domain = false)
    {
        $config = config('multi_route_modules');
         $module = strtolower(current($arr = explode("/", trim($url,'/'))));
         $url = trim($url);

        foreach ($config as $k => $v) {
           if(array_search($module, $v)!==false){
            ##绑定的顶级域名
              return config('domain_agreement').str_replace(config('domain_agreement'), "", trim($k)).Url::build($url, $vars, $suffix, $domain);
           }else{
            ##绑定的其它域名
             return config('domain_agreement').str_replace(config('domain_agreement'), "", config('root_domain')).Url::build($url, $vars, $suffix, $domain);
           }
        }
       
    }
}
if (!function_exists('pushordermsg')) {
    /**
     * [pushordermsg description]
     * @Author   Jerry
     * @DateTime 2017-08-03T15:38:42+0800
     * @Example  eg:
     * @param    string                   $to_uid  [给哪位发送消息 --门店ID]
     * @param    string                   $content [ordernumber,dec]
     * @param    string                   $type    [description]
     * @param    string                   $domain  [请求服务域]
     * @return   [type]                            [description]
     */
    function pushordermsg($o_id,$title='您有一个新的订单',$content='',$to_uid = '', $type = 'order', $domain = 'http://www.qiaolibeilang.com:2121/') 
    {
        if(empty($o_id)){
            ##日志
            socket_log("{'time':'{date('Y-m-d H:i:s'),'remark':'没有传入订单号',}'");
            return false;
        }
        // if($attr['orderid'])
        // 指明给谁推送，为空表示向所有在线用户推送
        $to_uid = "";
        // 推送的url地址，使用自己的服务器地址
        $push_api_url = $domain;
        $post_data = array(
           "type" => $type,
           "content" => $content,
           "o_id" => $o_id,
           "title" => $title,
           "to" => $to_uid, 
        );
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $push_api_url );
        curl_setopt ( $ch, CURLOPT_POST, 1 );
        curl_setopt ( $ch, CURLOPT_HEADER, 0 );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $post_data );
        curl_setopt ($ch, CURLOPT_HTTPHEADER, array("Expect:"));
        $return = curl_exec ( $ch );
        if(!$return){
            ##日志
             socket_log("{'time':'{date('Y-m-d H:i:s'),'remark':'domamin服务器异常',}'");
            return false;
        }
        curl_close ( $ch );
        ##消息
        notification(1,0,2,['title'=>$title,'content'=>$content,'url'=>url('manager/order/news')]);
        return $return;
    }
}
if (!function_exists('socket_log')) {
    /**
     * [socket_log 写入到本地日志]
     * @Author   Jerry
     * @DateTime 2017-08-04T15:41:47+0800
     * @Example  eg:
     * @param    [type]                   $logname [description]
     * @param    [type]                   $content [description]
     * @return   [type]                            [description]
     */
    function socket_log($content='',$logname='socket_log.text',$path='./runtime/'){
        if(!$content) return ;
        
        if($log = file_get_contents($path.$logname)){
            $log = $log.$content;
        }else{
            $log = $content;
        }
        file_put_contents($path.$logname, $log);
    }
}

if (!function_exists('notification')) {
    /**
     * [notification description]
     * @Author   Jerry
     * @DateTime 2017-08-07T11:44:06+0800
     * @Example  eg:
     * @param    string                   $recipient_uid [接收者ID  ]
     * @param    string                   $sender_uid     [发送者ID  0为系统]
     * @param    string                   $recipient_type [接收者类型  接收者类型，1,客户（用户），2，门店，3，管理员  默认：1]
     * @param    string                   $data           [arr 数组]
     * @return   [type]                                   [description]
     */
    function notification($recipient_uid='',$sender_uid=0,$recipient_type='1',$data=[]){
        if(!$recipient_uid) return false; ;
        $notification['recipient_uid'] = $recipient_uid;  
        $notification['sender_uid'] = $sender_uid;  
        $notification['recipient_type'] = $recipient_type; 
        $notification['add_time'] = date('Y-m-d H:i:s'); 
        ##通知表
        if($notification_id = model('base')->getadd('notification',$notification)){
            ##关联通知表
            if(model('base')->getadd('notification_data',['notification_id'=>$notification_id,'data'=>serialize($data)])){
                return true;
            }
        }else{
            return false;
        }


         
    }
}

if (!function_exists('send_phone')) {
    /**
     * [send_phone description]
     * @Author   Jerry
     * @DateTime 2017-08-25T10:32:13+0800
     * @Example  eg:
     * @param    [type]                   $phone   [手机号]
     * @param    [type]                   $params  [参数，['ordernumber','time']]
     * @param    string                   $templId [模板ID]
     * @return   [type]                            [description]
     */
     function send_phone($phone,$params,$templId = '40281',$sign="宿洗"){
        include_once EXTEND_PATH.'SmsSender.php';
        include_once EXTEND_PATH.'SmsTools.php';
        // 请根据实际 appid 和 appkey 进行开发，以下只作为演示 sdk 使用
        $singleSender = new \SmsSender('1400037030','3472a1e245731fb5041464de63753ed6');
        $result = $singleSender->sendWithParam("86", $phone, $templId, $params, $sign, "", "");
        $rsp = json_decode($result);
        // show($rsp);
        return $rsp;

    }
}
if (!function_exists('format_time')) {
    /**
     * 时间戳格式化
     * @param string $time 时间戳
     * @param string $format 输出格式
     * @return false|string
     */
    function format_time($time = '', $format='Y-m-d H:i') {
        return !$time ? '' : date($format, intval($time));
    }
}

if (!function_exists('format_date')) {
    /**
     * 使用bootstrap-datepicker插件的时间格式来格式化时间戳
     * @param null $time 时间戳
     * @param string $format bootstrap-datepicker插件的时间格式 https://bootstrap-datepicker.readthedocs.io/en/stable/options.html#format
     * @author 蔡伟明 <314013107@qq.com>
     * @return false|string
     */
    function format_date($time = null, $format='yyyy-mm-dd') {
        $format_map = [
            'yyyy' => 'Y',
            'yy'   => 'y',
            'MM'   => 'F',
            'M'    => 'M',
            'mm'   => 'm',
            'm'    => 'n',
            'DD'   => 'l',
            'D'    => 'D',
            'dd'   => 'd',
            'd'    => 'j',
        ];

        // 提取格式
        preg_match_all('/([a-zA-Z]+)/', $format, $matches);
        $replace = [];
        foreach ($matches[1] as $match) {
            $replace[] = isset($format_map[$match]) ? $format_map[$match] : '';
        }

        // 替换成date函数支持的格式
        $format = str_replace($matches[1], $replace, $format);
        $time = $time === null ? time() : intval($time);
        return date($format, $time);
    }
}

if (!function_exists('format_moment')) {
    /**
     * 使用momentjs的时间格式来格式化时间戳
     * @param null $time 时间戳
     * @param string $format momentjs的时间格式
     * @author 蔡伟明 <314013107@qq.com>
     * @return false|string
     */
    function format_moment($time = null, $format='YYYY-MM-DD HH:mm') {
        $format_map = [
            // 年、月、日
            'YYYY' => 'Y',
            'YY'   => 'y',
//            'Y'    => '',
            'Q'    => 'I',
            'MMMM' => 'F',
            'MMM'  => 'M',
            'MM'   => 'm',
            'M'    => 'n',
            'DDDD' => '',
            'DDD'  => '',
            'DD'   => 'd',
            'D'    => 'j',
            'Do'   => 'jS',
            'X'    => 'U',
            'x'    => 'u',

            // 星期
//            'gggg' => '',
//            'gg' => '',
//            'ww' => '',
//            'w' => '',
            'e'    => 'w',
            'dddd' => 'l',
            'ddd'  => 'D',
            'GGGG' => 'o',
//            'GG' => '',
            'WW' => 'W',
            'W'  => 'W',
            'E'  => 'N',

            // 时、分、秒
            'HH'  => 'H',
            'H'   => 'G',
            'hh'  => 'h',
            'h'   => 'g',
            'A'   => 'A',
            'a'   => 'a',
            'mm'  => 'i',
            'm'   => 'i',
            'ss'  => 's',
            's'   => 's',
//            'SSS' => '[B]',
//            'SS'  => '[B]',
//            'S'   => '[B]',
            'ZZ'  => 'O',
            'Z'   => 'P',
        ];

        // 提取格式
        preg_match_all('/([a-zA-Z]+)/', $format, $matches);
        $replace = [];
        foreach ($matches[1] as $match) {
            $replace[] = isset($format_map[$match]) ? $format_map[$match] : '';
        }

        // 替换成date函数支持的格式
        $format = str_replace($matches[1], $replace, $format);
        $time = $time === null ? time() : intval($time);
        return date($format, $time);
    }
}

if (!function_exists('format_linkage')) {
    /**
     * 格式化联动数据
     * @param array $data 数据
     * @author 蔡伟明 <314013107@qq.com>
     * @return array
     */
    function format_linkage($data = [])
    {
        $list = [];
        foreach ($data as $key => $value) {
            $list[] = [
                'key'   => $key,
                'value' => $value
            ];
        }
        return $list;
    }
}

/**
 * [create_wx_code 生成小程序员二维码]
 * @Author   Jerry
 * @DateTime 2017-09-06T18:41:56+0800
 * @Example  eg:
 * @param    [type]                   $code_url [二维码的CODE]
 * @param    [type]                   $name     [二维码的名称]
 *  @param    [type]                   $path     [二维码存放路径]
 * @return   [type]                             [description]
 */
function create_wx_code($code_url,$name,$HTTP_HOST="qiaolibeilang.com"){
        $appid      = config('appid_two');
        $secret     = config('secret_two');//'6bc4848260aa4eeab96462db29f010d2';
        //小程序获得token从而根据设定路劲与二维码宽度来生成小程序二维码
        $url        = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$secret;
        $res  = curl_get_contents($url);
        $data =json_decode($res);
        $urls = "https://api.weixin.qq.com/wxa/getwxacode?access_token=".$data->access_token;
        //用于生成个人专属小程序二维码
        $width=300;
        $param='{"path":"'.$code_url.'","width":'.$width.'}';
        $result = curl_post_contents($urls,$param);
        $dir_path = PUBLIC_PATH."spread/";
        $full_path = $dir_path.$name.'.jpg' ;
        $img = file_put_contents($full_path, $result);
        return "http://".$HTTP_HOST."/public/spread/".$name.".jpg";
}
/**
 * [bankcard_info 银行卡四元素校验]
 * @Author   Jerry
 * @DateTime 2017-09-11T10:49:54+0800
 * @Example  eg:
 * @return   [type]                   [description]
 */
function bankcard_info($realname,$idcard,$bankcard,$mobile,$uid='',$uid_type='express'){
    $params = array(
          "key" => 'bf4f153708b4bd6bce69a7d53c0f360b',//
          "realname" => $realname,//真实姓名
          "idcard" => $idcard,//真实姓名
          "bankcard" => $bankcard,//真实姓名
          "mobile" => $mobile,//真实姓名
    );
    $paramstring = http_build_query($params);
    $content = juhecurl('http://v.juhe.cn/verifybankcard4/query',$paramstring);
    $result = json_decode($content,true);
    // show($result);
    // show($result);
    ##写入日志
    api_log([
        'uid'=>$uid,
        'uid_type'=>$uid_type,
        'log'=>'银行卡四元素校验',
        'functions'=>'bankcard_info',
        'create_date'=>date('Y-m-d H:i:s'),
        'remark'=>'',
        'result'=>$content,
        ]);
    if($result){
        if($result['result']['res']=='1'){
            return returnJson('0','success','',$result['result']);
        }else{
            return returnJson('1',$result['reason']);
        }
    }
}
 
/**
 * [bank_true_false 判断银行卡类型及真伪]
 * @Author   Jerry
 * @DateTime 2017-09-11T10:43:35+0800
 * @Example  eg:
 * @return   [type]                   [description]
 */
function is_bankcard($cardid,$uid='',$uid_type='express'){
    $params = array(
          "key" => '087c504759c35cdfd70eb605987e2389',//
          "cardid" => $cardid,//真实姓名
    );
    $paramstring = http_build_query($params);
    $content = juhecurl('http://detectionBankCard.api.juhe.cn/bankCard',$paramstring);
    $result = json_decode($content,true);
     ##写入日志
    api_log([
        'uid'=>$uid,
        'uid_type'=>$uid_type,
        'log'=>'判断银行卡类型及真伪',
        'functions'=>'is_bankcard',
        'create_date'=>date('Y-m-d H:i:s'),
        'remark'=>'',
        'result'=>$content,
        ]);
    if($result){
        if($result['error_code']=='0'){
            return true;
            // return returnJson('0','success','',$result['result']);
        }else{
            return returnJson('1',$result['reason']);
        }
    }
}
 
 /**
  * [id_card_juhe 聚合身份证验证]
  * @Author   Jerry
  * @DateTime 2017-09-11T10:38:23+0800
  * @Example  eg:
  * @return   [type]                   [description]
  */
 function id_card_juhe($idcard,$name,$uid='',$uid_type='express'){
    if(!$idcard||!$name) return returnJson('1','身份证号码或者真实姓名不能为空');
    //配置您申请的appkey
    $appkey = "5a08d0f22031523049a858419b640d34";
 
    //************1.真实姓名和身份证号码判断是否一致************
    $url = "http://op.juhe.cn/idcard/query";
    $params = array(
          "idcard" => $idcard,//身份证号码
          "realname" => $name,//真实姓名
          "key" => $appkey,//应用APPKEY(应用详细页查询)
    );
    $paramstring = http_build_query($params);
    $content = juhecurl($url,$paramstring);
    $result = json_decode($content,true);
     ##写入日志
    api_log([
        'uid'=>$uid,
        'uid_type'=>$uid_type,
        'log'=>'身份证信息检验',
        'functions'=>'id_card_juhe',
        'create_date'=>date('Y-m-d H:i:s'),
        'remark'=>'',
        'result'=>$content,
        ]);
    if($result){
        if($result['error_code']=='0'){
            if($result['result']['res'] == '1'){
                return true;
            }else{
                return returnJson('1','身份证号码和真实姓名不一致');
            }
        }else{
            // echo $result['error_code'].":".$result['reason'];
            return returnJson(0,'success');
        }
    }else{
        return returnJson('1','请求失败');
    }
    //**************************************************
     
 }
 /**
 * 判断是否为合法的身份证号码
 * @param $mobile
 * @return int
 */
function is_idcard($vStr){
  $vCity = array(
    '11','12','13','14','15','21','22',
    '23','31','32','33','34','35','36',
    '37','41','42','43','44','45','46',
    '50','51','52','53','54','61','62',
    '63','64','65','71','81','82','91'
  );
  if (!preg_match('/^([\d]{17}[xX\d]|[\d]{15})$/', $vStr)) return false;
  if (!in_array(substr($vStr, 0, 2), $vCity)) return false;
  $vStr = preg_replace('/[xX]$/i', 'a', $vStr);
  $vLength = strlen($vStr);
  if ($vLength == 18) {
    $vBirthday = substr($vStr, 6, 4) . '-' . substr($vStr, 10, 2) . '-' . substr($vStr, 12, 2);
  } else {
    $vBirthday = '19' . substr($vStr, 6, 2) . '-' . substr($vStr, 8, 2) . '-' . substr($vStr, 10, 2);
  }
  if (date('Y-m-d', strtotime($vBirthday)) != $vBirthday) return false;
  if ($vLength == 18) {
    $vSum = 0;
    for ($i = 17 ; $i >= 0 ; $i--) {
      $vSubStr = substr($vStr, 17 - $i, 1);
      $vSum += (pow(2, $i) % 11) * (($vSubStr == 'a') ? 10 : intval($vSubStr , 11));
    }
    if($vSum % 11 != 1) return false;
  }
  return true;
}

 
 
/**
 * 请求接口返回内容
 * @param  string $url [请求的URL地址]
 * @param  string $params [请求的参数]
 * @param  int $ipost [是否采用POST形式]
 * @return  string
 */
function juhecurl($url,$params=false,$ispost=0){
    $httpInfo = array();
    $ch = curl_init();
 
    curl_setopt( $ch, CURLOPT_HTTP_VERSION , CURL_HTTP_VERSION_1_1 );
    curl_setopt( $ch, CURLOPT_USERAGENT , 'JuheData' );
    curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT , 60 );
    curl_setopt( $ch, CURLOPT_TIMEOUT , 60);
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER , true );
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    if( $ispost )
    {
        curl_setopt( $ch , CURLOPT_POST , true );
        curl_setopt( $ch , CURLOPT_POSTFIELDS , $params );
        curl_setopt( $ch , CURLOPT_URL , $url );
    }
    else
    {
        if($params){
            curl_setopt( $ch , CURLOPT_URL , $url.'?'.$params );
        }else{
            curl_setopt( $ch , CURLOPT_URL , $url);
        }
    }
    $response = curl_exec( $ch );
    if ($response === FALSE) {
        //echo "cURL Error: " . curl_error($ch);
        return false;
    }
    $httpCode = curl_getinfo( $ch , CURLINFO_HTTP_CODE );
    $httpInfo = array_merge( $httpInfo , curl_getinfo( $ch ) );
    curl_close( $ch );
    return $response;
}
/**
 * [api_log 写入APILOG]
 * @Author   Jerry
 * @DateTime 2017-09-11T10:55:19+0800
 * @Example  eg:
 * @return   [type]                   [description]
 */
function api_log($data){
    model('base')->getadd('api_log',$data);
}
/**
 * [_order_log 订单信息流]
 * @Author   Jerry
 * @DateTime 2017-11-06T17:33:28+0800
 * @Example  eg:
 * @param    [type]                   $param [description]
 * @return   [type]                          [description]
 */
function order_info($param){
    $type = $param['type']?$param['type']:1;##默认类型为商家
    $orderinfodesc = config('order_info');
    $orderInfo['order_id'] = $param['order_id'];
    $orderInfo['create_time'] = time();
    $orderInfo['remark'] = $orderinfodesc[$param['order_status']];
    $orderInfo['uid'] = $param['uid'];##门店ID
    $orderInfo['type'] = $type;##1为商家2为快递员
    $orderInfo['order_number'] = $param['order_number'];##门店ID
    $orderInfo['order_status'] = $param['order_status'];
    $base = model('common/base');
    if($base->getcount('order_info',['where'=>['order_status'=>$param['order_status'],'order_id'=>$param['order_id']]])>0){
      return $base->getedit('order_info',['where'=>['order_status'=>$param['order_status'],'order_id'=>$param['order_id']]],$orderInfo);
    }else{
      return $base->getadd('order_info',$orderInfo);  
    }
    
}
/**
 * [c_barcode 生成二维码]
 * @Author   Jerry
 * @DateTime 2017-11-02T09:37:36+0800
 * @Example  eg:
 * @param    [type]                   $Filename [路径+名字]
 * @param    [type]                    $[text] [<二维码内容>]
 * @return   [type]                             [description]
 */
function c_barcode($text='test',$Filename='./barcode.png'){
      // Including all required classes
        require_once(EXTEND_PATH.'barcodegen/class/BCGFontFile.php');
        require_once(EXTEND_PATH.'barcodegen/class/BCGColor.php');
        require_once(EXTEND_PATH.'barcodegen/class/BCGDrawing.php');
        // Including the barcode technology
        require_once(EXTEND_PATH.'barcodegen/class/BCGcode39.barcode.php');
        // Loading Font
        $font = new \BCGFontFile(EXTEND_PATH.'barcodegen/font/Arial.ttf', 18);

        // The arguments are R, G, B for color.
        $color_black = new \BCGColor(0, 0, 0);
        $color_white = new \BCGColor(255, 255, 255);

        $drawException = null;
        try {
            $code = new \BCGcode39();
            $code->setScale(2); // Resolution
            $code->setThickness(30); // Thickness
            $code->setForegroundColor($color_black); // Color of bars
            $code->setBackgroundColor($color_white); // Color of spaces
            $code->setFont($font); // Font (or 0)
            $code->parse($text); // Text
        } catch(Exception $exception) {
            $drawException = $exception;
        }

        /* Here is the list of the arguments
        1 - Filename (empty : display on screen)
        2 - Background color */
        $drawing = new \BCGDrawing('', $color_white);
        if($drawException) {
            $drawing->drawException($drawException);
        } else {
            $drawing->setBarcode($code);
            $drawing->setFilename($Filename);
            $drawing->draw();
        }
        $drawing->finish(\BCGDrawing::IMG_FORMAT_PNG);

}


