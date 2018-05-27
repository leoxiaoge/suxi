<?php

return [
    'miss_model_conf'   =>['ajax','index','asset','common','post','ucenter','admin'],##需要过滤掉的组配置项
    'auth_user'         =>'user',##SESSION字段名
    'auth_uid'          =>'uid',##SESSION字段名
    'white_uid'         =>[],##uid 白名单.有访问所有的权利管理员
    'black_uid'         =>[],##uid 黑名单.不管有没有权限,都不能访问 除超管
    'black_role'        =>[],##黑名单地址 不管有没有权限,都不能访问 除超管
    'white_role'        =>[],##白名单地址 所有管理员都能访问
    'cross_domain'      =>false,##是否跨域
    'auth_modules'      =>['admin'],##需要验证权限的组
    'auth_controller'      =>['admin'],##需要验证权限的控制器
    'auth_action'      =>[],##需要验证权限的方法
    'super_manager'     =>[1,],##超级管理员
    // 'multi_route_modules'       =>['www.ithelp.org.cn'=>['jblog',]],##多顶级域名绑定,需要用turl()生成 绑定到组
    // 'root_domain'       =>'www.thinkask.cn',##默认域名
    // 'root_domain'       =>'www.thinkask.com',##默认域名
    // 'multi_route_modules'       =>['www.thinkask.net'=>['blog','ucenter'],'www.thinkask.org'=>['blog','ucenter']],##多顶级域名绑定,需要用turl()生成 绑定到组
    'domain_agreement'=>'http://',##协议方式.有可以是https
 
];