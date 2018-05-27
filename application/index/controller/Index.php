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

use app\index\model\Index as Indexss;
use think\Controller;
use think\Request;
use think\Session;
use think\Cookie;
use think\Route;
use think\Hook ;
class index extends Controller
{

	public function _initialize()
    {
       //  $contents = include(APP_PATH.'common/conf/sxcomment.php');
       // $phoneprex = ['134','135','136','137','138','139','147','150','151','152','157','158','159','182','183','187','188','189','130','131','132','155','156','185','186','145','133','153','180'];
       //  foreach ($contents as $k => $v) {
       //      if(model('base')->getcount('selfdb_comment',['where'=>['comment'=>$v]])<1){
       //          $comment = [
       //          'time'=>'2017-9-'.rand(10,27).' '.rand(0,23).':'.rand(0,60).':'.rand(0,60),
       //          'phone'=>$phoneprex[rand(0,count($phoneprex)-1)].'****'.rand(1234,9876),
       //          'comment'=>$v

       //          ];
       //          // show($comment);
       //         model('base')->getadd('selfdb_comment',$comment);
       //     }
       //  }
    	// echo PUBLIC_PATH;
    	 // $allorder = db('order')->alias('o')->join([['order_info oi','oi.order_id = o.id']])->where("o.status=6 and oi.order_status=6")->field('o.order_number,o.id,oi.create_time')->limit($start,$pagesize)->join()->select();
    	 // show($allorder);
       // echo "string";

    }

 //    public function index()
 //    {
 //       $redis=new \Redis();

 // $redis->connect('127.0.0.1',6379);

 // // $redis->auth('123456');

 // $redis->set('test','helloworld');

 // echo $redis->get('test');
 // die;
 //      phpinfo();
 //      die;
 //      // if(cache('test')){
 //      //   show(cache('test'))
 //      // }else{
 //      //   cache('test',['text'=>'cache']);
 //      // }
 //    	echo "小程序搜索'宿洗'";
 //      // echo "string";

 //    }


 //    public function char()
 //    {
 //        echo 1;
 //        return $this->fetch('/index/char');
 //    }


    public function index(){

     return $this->fetch('');
    }



}

