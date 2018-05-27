<?php
/**
 * 每天执行一次
 */
namespace app\cron\command;
// use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;
use think\Cache;
use think\Validate;
class Day extends Base
{

    protected function configure()
    {
      
      $this->setName('day')->setDescription('some cron in everyday');
    }
 
    protected function execute(Input $input, Output $output)
    {
      $output->writeln('start wash_step '.date('Y-m-d H:i:s'));
       // $output->writeln($this->sqlconfig());
      $output->writeln('end wash_step '.date('Y-m-d H:i:s'));
      
    }
    /**
     * [content 自动化评价处理]
     * @Author   Jerry
     * @DateTime 2017-09-27T15:37:54+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    protected function content(){
       $contents = include(APP_PATH.'common/conf/sxcomment.php');
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

    }
   
}