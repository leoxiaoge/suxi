<?php

namespace app\api\controller;
use app\common\controller\ApiBase;
use think\Cache;
use think\Db;
use think\Validate;
use think\helper\Hash;
class PublicBase extends ApiBase
{
  //  private  $key = 'nozuonodie';
	/**
	 * [_initialize description  encode('qlblapi20160704rand');]
	 * @Author   Jerry
	 * @DateTime 2017-07-05T14:38:53+0800
	 * @Example  eg:
	 * @return   [type]                   [description]
	 * key:qlblapi
	 * secret:cNWmxYi5bMGTFhwlaTIwMTYwNzA0cmFuZA6f918e6f918e 验证预留
	 */

	public function _initialize()
    {
        die('API接口不再使用，请使用eapi');
        // show($this->request->isPost());
          if($_SERVER['HTTP_HOST']!="qlbl.com"&&$_SERVER['HTTP_HOST']!="demo.thinkask.cn"){
                if(!$this->request->isPost()){
                     return returnJson('1','database error ');  
                 }      
             }
    	 

       parent::_initialize();
    }




    public function  validate($rule,$data,$msg){
        $vali = new Validate($rule);
        $res  = $vali ->check($data);
        if(!$res){
            return $vali->getError();
        }
        return $res;
    }




}
