<?php
/**
 * 每分钟执行一次
 */
namespace app\cron\command;
// use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;
use think\Cache;
use think\Validate;
class Minute extends Base
{

    protected function configure()
    {
      
      $this->setName('minute')->setDescription('do fro minutes ');
    }
 
    protected function execute(Input $input, Output $output)
    {
      $output->writeln('start meninute>> '.date('Y-m-d H:i:s'));
      $this->wash_step($output);##自动工顺序处理
      $output->writeln('end meninute>> '.date('Y-m-d H:i:s'));
      $output->writeln('==========================================================================================');
      
    }
    /**
     * [wash_step 自动工顺序处理]
     * @Author   Jerry
     * @DateTime 2017-09-26T14:26:49+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    protected function wash_step($output){
      $output->writeln('开始执行订单工序: '.date('Y-m-d H:i:s'));
      $pagesize = 20;
      $start = 0;
      # 基本参数 勿动！！！
      do {
          if($start>1000){
            // $output->writeln($start);
            die('this is finish');
          }

          $time = getset('wash_step_time');
          // $output->writeln('time'.$time);
          if($time<1){
            $output->writeln('时间为0不执行');
              // Log::save('时间为0不执行','debug');
            return true;
          }
          ##所有订单
         $allorder = db('order',$this->sqlconfig())->alias('o')->join([['order_info oi','oi.order_id = o.id']])->where("o.status=6 and oi.order_status=6")->field('o.order_number,o.id,oi.create_time')->limit($start,$pagesize)->join()->select();
         if(is_array($allorder)&&count($allorder)>0){
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
                  if(db('order_info',$this->sqlconfig())->where(['order_id'=>$v['id'],'order_status'=>$order_status])->count()<1){
                    $output->writeln('执行工顺序：'.$vw);
                    db('order_info',$this->sqlconfig())->insert($step);
                  }
                }
              }

            }
              $start++;
         }else{
           return true;
         }
        } while (true);
    }
   
}