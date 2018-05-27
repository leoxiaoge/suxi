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
class Goods extends PublicBase
{

    public function _initialize()
    {
        parent::_initialize();  
    }
    /**
     * [taglist 标签TAG]
     * @Author   Jerry
     * @DateTime 2017-10-16T17:31:32+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function taglist(){
        if(!cache('xcx_taglist')){
                $taglist = $this->_gettaglist();
                if($taglist){
                   cache('xcx_taglist',$taglist); 
                }
             
         }
        return returnJson(0,'success','',cache('taglist')); 
    }
    /**
     * [_gettaglist description]
     * @Author   Jerry
     * @DateTime 2017-10-23T09:18:38+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    private function _gettaglist(){
        return  $this->getbase->getall('goods_cat_tag',['order'=>'sort desc','field'=>'id,title','where'=>['status'=>1]]);
    }
   
    /**
     * [goods 商品列表]
     * @Author   Jerry
     * @DateTime 2017-10-23T09:09:24+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function goods_cat_tag(){
        if(!cache('xcx_all_goods')){
            $goodsinfo['taglist'] = model('common/base')->getall('goods_cat_tag',['order'=>'sort desc','field'=>'id,title','where'=>['status'=>1]]);
            $goodsinfo['cat'] = model('common/base')->getall('goods_cat',['where'=>'status = 1','field'=>'id,title,tagid','order'=>'sort desc']);
            $goods = model('common/base')->getall('goods',['order'=>'sort desc','where'=>'status=1 ','field'=>'id,name,price,catid,picture']);
            ##拼接数组。在商品里面指定TAGID所属。
            $cat_tag = [];
            foreach ($goodsinfo['cat'] as $v) {
                $cat_tag[$v['id']] = $v['tagid'];
            }
            foreach ($goods as &$vi) {
                    $vi['picture'] = config('host').get_file_path($vi['picture']);
                    $vi['num'] = 0;
                    $vi['tagid'] = $cat_tag[$vi['catid']];
                    $attr = $this->getbase->getall('goods_attr',['where'=>['goods_id'=>$vi['id'],'status'=>1],'field'=>'value']);
                    $vi['attr'] = $attr?$attr:'';
                }
            $goodsinfo['goods'] = $goods;
            $this->cCacheTime('goods_cat_tag');##当前缓存生成时间
            cache('xcx_all_goods',$goodsinfo);

        }
        return returnJson(0,'success','',cache('xcx_all_goods'));

    }


}
