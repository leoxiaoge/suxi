<?php
namespace app\cron\command;
 
use think\console\Command;
use think\console\Input;
use think\console\Output;
 
class Base extends Command
{
	protected function sqlconfig(){
			return $this->test();
	}
	protected function test(){
		return [
	          // 数据库类型
	          'type'        => 'mysql',
	          // 服务器地址
	          'hostname'    => 'www.qiaolibeilang.com',
	          // 数据库名
	          'database'    => 'qlbltest',
	          // 数据库用户名
	          'username'    => 'qlbl',
	          // 数据库密码
	          'password'    => 'qlbl123456',
	          // 数据库编码默认采用utf8
	          'charset'     => 'utf8',
	          // 数据库表前缀
	          'prefix'      => 'qlbl_',

	          'hostport'    =>'3306',
	      ];
	}
	protected function produce(){

		return [
		 		// 数据库类型
	          	'type'        => 'mysql',
			     'hostname'       => '10.66.224.67',
			        // 'hostname'       => '594a1d9c9a3b6.gz.cdb.myqcloud.com',
			        // 数据库名
			        'database'       => 'qlbl',
			        // 用户名
			        // 'username'       => 'root',
			        'username'       => 'qlbl',
			        // 密码
			        'password'       => 'qlbl88888888',
			        // 端口
			        // 'hostport'       => '10051',//10051
			        'hostport'       => '3306',//10051
			        // 数据库编码默认采用utf8
			          'charset'     => 'utf8',
			          // 数据库表前缀
			         'prefix'      => 'qlbl_',
			];
	}
    
}