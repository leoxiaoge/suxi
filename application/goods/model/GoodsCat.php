<?php

namespace app\goods\model;
use think\Model;
use think\Db;
class GoodsCat extends Model
{
  	 protected $resultSetType = 'collection';
  	 // protected $prefix = 'qlbl_';
  	 /**
  	  * [CatAndgoods 所有分类和商品]
  	  * @Author   Jerry
  	  * @DateTime 2017-07-05T11:43:33+0800
  	  * @Example  eg:
  	  * @return   [type]                   [description]
  	  */
     public function catAll($field='title,status,id'){
     	return $this->order('sort desc')->where("")->field($field)->select()->toArray();
    	
    }
 
   
}