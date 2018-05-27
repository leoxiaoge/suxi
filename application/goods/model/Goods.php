<?php

namespace app\goods\model;
use think\Model;
use think\Db;
class Goods extends Model
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
     public function CatAndgoods(){
     	$sql = "SELECT * FROM ".config('database.prefix')."goods as g LEFT JOIN ".config('database.prefix')."goods_cat as gc ON g.catid=gc.id";
     	return $this->query($sql);
    	
    }
    /**
     * [goodsAll 所有商品]
     * @Author   Jerry
     * @DateTime 2017-07-05T12:08:15+0800
     * @Example  eg:
     */
    public function goodsAll(){
    	return $this->select()->toArray();
    }
    /**
     * [f_catid_t_goods 通过分类查找商品]
     * @Author   Jerry
     * @DateTime 2017-07-05T12:13:30+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function f_catid_t_goods($catid,$field='g.id,g.name,g.price,g.unit,g.picture as b_picture,g.status,att.name as picture,g.id,g.catid,g.remark'){
    	 return $this->where(['catid'=>$catid,'g.status'=>1])->alias('g')->join('attachment att','g.picture = att.id','left')->order('g.sort desc,g.id desc')->field($field)->select()->toArray();
       // show($this->getLastSql());
       // die;
    }


   
}