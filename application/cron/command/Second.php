<?php
/**
 * 每秒执行一次
 */
namespace app\cron\command;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;
use think\Cache;
use think\Validate;
class Second extends Command
{
  
    protected function configure()
    {
      
      $this->setName('order')->setDescription('do for second');
    }
 
    protected function execute(Input $input, Output $output)
    {
      $output->writeln('start wash_step '.date('Y-m-d H:i:s'));
    
      $output->writeln('end wash_step '.date('Y-m-d H:i:s'));
      
    }
   
}