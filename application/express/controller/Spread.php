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
|   推广
+---------------------------------------------------------------------------
 */
namespace app\express\controller;
use app\common\controller\ExpressBase;
class Spread extends ExpressBase{
  public function _initialize(){
    parent::_initialize();
  }
  /**
   * [share 分享,推广]
   * @Author   Jerry
   * @DateTime 2017-09-06T11:37:48+0800
   * @Example  eg:
   * @return   [type]                   [description]
   */
  public function share(){
    // show(cookie('express_id'));
        ##查出所有的推广
        $share = $this->getbase->getall('spread');
        if(is_array($share)){
          foreach ($share as &$v) {
            $v['code'] = create_wx_code($v['url'].cookie('express_id').'&tag='.$v['tag'],$v['tag'].cookie('express_id'),$_SERVER['HTTP_HOST']);
          }
        }

        $this->assign('share',$share);
        $this->assign('title','推广分享');
        return $this->fetch();

  }

  
  

  }
