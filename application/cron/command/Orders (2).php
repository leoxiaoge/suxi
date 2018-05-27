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
// ini_set('memory_limit','1024M');
namespace app\cron\controller;
use app\common\controller\Base;
use think\DB;
use think\Cache;
use think\Log;
class Order extends Base{
  private $wash_step;
  public function _initialize(){
    parent::_initialize();
  }
  public function gocron(){
    $pagesize = 20;
    $start = 0;
    # 基本参数 勿动！！！
    do {
      $re = $this->wash_step($start,$pagesize);
       $start++;
      } while (true);

    
  }
  /**
   * [wash_step 加入洗衣工序]
   * @Author   Jerry
   * @DateTime 2017-09-22T10:46:28+0800
   * @Example  eg:
   * @return   [type]                   [description]
   */
  public function wash_step($start,$pagesize){
    echo $start;
    $time = getset('wash_step_time');
    if($time<1){
        Log::save('时间为0不执行','debug');
       return ;
    }
   
    // 
    ##所有订单
    $allorder = $this->getbase->getall('order',['join'=>[['order_info oi','oi.order_id = o.id']],'alias'=>'o','field'=>'o.order_number,o.id,oi.create_time','where'=>"o.status=6 and oi.order_status=6",'limit'=>"$start,$pagesize"]);
    foreach ($allorder as $k => $v) {
      ##时间差计算
      $ctime = time()-$v['create_time'];##秒差
      $m = ($ctime/60);##分钟
      $systemM = getset('wash_step_time');##工序时间

      ##开始工序
      $wash_step = config('wash_step');
      foreach ($wash_step as $kw => $vw) {
        ##分钟差大于设置的时间差，插入工序值
        if($m>(($kw+1)*$systemM)){
          $order_status = '6.'.($kw+1);
          ##插入当前工序
          $step = [
          'order_id'=>$v['id'],
          'create_time'=>$v['create_time']+(($kw+1)*$systemM*60),
          'remark'=>$vw,
          'type'=>'1',
          'order_number'=>$v['order_number'],
          'order_status'=>$order_status,
          ];
          ##工序值加入，避免重复加入
          if($this->getbase->getcount('order_info',['where'=>['order_id'=>$v['id'],'order_status'=>$order_status]])<1){
            $this->getbase->getadd('order_info',$step);
          }
        }
      }

    }
    return $allorder;
  }
     
   

}