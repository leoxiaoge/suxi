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
$database1 = [
         // 'hostname'       => '10.66.224.67',
        'hostname'       => 'www.qiaolibeilang.com',
        // 数据库名
        'database'       => 'qlbltest',
        // 用户名
        'username'       => 'qlbl',
        // 'username'       => 'qlbl',
        // 密码
        'password'       => 'qlbl123456',
        // 端口
        'hostport'       => '3306',//10051
        // 'hostport'       => '3306',//10051
    ];
$database2 = [
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
];

$base =  [
    // 数据库类型
    'type'           => 'mysql',
    // 服务器地址
    
    // 连接dsn
    'dsn'            => '', 
    // 数据库连接参数
    'params'         => [],
    // 数据库编码默认采用utf8
    'charset'        => 'utf8',
    // 数据库表前缀
    'prefix'         => 'qlbl_',
    // 数据库调试模式
    'debug'          => true,
    // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
    'deploy'         => 0,
    // 数据库读写是否分离 主从式有效
    'rw_separate'    => false,
    // 读写分离后 主服务器数量
    'master_num'     => 1,
    // 指定从服务器序号
    'slave_no'       => '',
    // 是否严格检查字段是否存在
    'fields_strict'  => false,
    // 数据集返回类型 array 数组 collection Collection对象
    'resultset_type' => 'array',
    // 是否自动写入时间戳字段
    'auto_timestamp' => false,
    // 是否需要进行SQL性能分析
    'sql_explain'    => false,
];
$server = $_SERVER;
$database = [];
if(isset($server['HTTP_HOST'])&&!empty($server['HTTP_HOST'])){
    if($server['HTTP_HOST']=="demo.thinkask.cn"||$server['HTTP_HOST']=="qlbl.com"||$server['HTTP_HOST']=="www.ithelp.org.cn"||$server['HTTP_HOST']=="ithelp.org.cn"){
        $database =$database1;
    }else{
         $database =$database2;
    }   
}
 
return array_merge_recursive($base,$database);
