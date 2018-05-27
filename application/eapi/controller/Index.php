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
class index extends PublicBase
{

    public function _initialize()
    {
        parent::_initialize();

    }

    public function index()
    {

        ##banner
        if(!cache('xcx_banner')){
            $banner = $this->getbase->getall('adv',['where'=>['postion_id'=>2],'field'=>'img']);
            foreach ($banner as &$v) {
                $v['img'] = config('host').get_file_path($v['img']);
            }
            cache('xcx_banner',$banner);
        }
        $data['banner'] = cache('xcx_banner');
         ##评价
        $data['comment'] = $this->_c_comment();
        return returnJson(0,'success','',$data);
    }
     /**
     * [comment 评价接口]
     * @Author   Jerry
     * @DateTime 2017-09-18T17:48:03+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function comment(){
        $pages = (int)input('pages');
        $this->_c_comment($pages);
       return returnJson(0,'success','',$info);

    }
    /**
     * [_c_comment 生成评价]
     * @Author   Jerry
     * @DateTime 2017-10-16T15:54:13+0800
     * @Example  eg:
     * @param    [type]                   $pages [description]
     * @return   [type]                          [description]
     */
    private function _c_comment($pages){
        $limitpage = $pages*10;
        if(!cache('comment_self_'.$limitpage)){
             $info = $this->getbase->getall('selfdb_comment',['limit'=>"{$limitpage},10",'order'=>'time desc','field'=>'time,comment,phone']);
             cache('comment_self_'.$limitpage,$info);
        }else{
             $info =cache('comment_self_'.$limitpage);
        }
        return $info;
    }
   






}
