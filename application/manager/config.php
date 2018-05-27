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

return [

    // +----------------------------------------------------------------------
    // | 模板设置aaa
    // +----------------------------------------------------------------------

    'template'               => [
        // 模板引擎类型 支持 php think 支持扩展
        'type'         => 'Think',
        // 模板路径
        'view_path'    => '',
        // 模板后缀
        'view_suffix'  => 'html',
        // 模板文件名分隔符
        'view_depr'    => DS,
        // 模板引擎普通标签开始标记
        'tpl_begin'    => '{',
        // 模板引擎普通标签结束标记
        'tpl_end'      => '}',
        // 标签库标签开始标记
        'taglib_begin' => '{',
        // 标签库标签结束标记
        'taglib_end'   => '}',
        'taglib_build_in'=>"cx,\\app\\common\\taglib\\Thinkask",
    ],
    'manager_left_menu'=>[
        'home' => [
                'name'=>'首页',
                'ico'=>'fa-home',
                'url'=>'/manager/index/index',
                'child'=>[
                   
               
                 ]
            ],
            'ucenter' => [
                'name'=>'用户中心',
                'ico'=>'fa-user',
                'url'=>'manager/ucenter/default',
                'child'=>[
                    [
                    'name'=>'修改密码',
                    'ico'=>'',
                    'url'=>'/manager/ucenter/changepwd',
                    'url_extra'=>'',
                    ]
                    

               
                 ]
            ],
            'setting' => [
                'name'=>'系统相关',
                'ico'=>'fa-cogs',
                'url'=>'manager/index/default',
                'child'=>[
                    // [
                    // 'name'=>'系统消息',
                    // 'ico'=>'',
                    // 'url'=>'/manager/message/lists',
                    // 'url_extra'=>'',
                    // ],
                    [
                    'name'=>'基本设置',
                    'ico'=>'',
                    'url'=>'/manager/store/setting',
                    'url_extra'=>'',
                    ],
                    // [
                    // 'name'=>'物流人员管理',
                    // 'ico'=>'',
                    // 'url'=>'/manager/store/express',
                    // 'url_extra'=>'',
                    // ],
                    [
                    'name'=>'默认物流人员',
                    'ico'=>'',
                    'url'=>'/manager/store/users_express',
                    'url_extra'=>'',
                    ]

               
                 ]
            ],
            'order' => [
                'name'=>'订单管理',
                'ico'=>'fa-cc-discover',
                'url'=>'order/index/default',
                'child'=>[
                 [
                    'name'=>'订单搜索',
                    'ico'=>'',
                    'url'=>'/manager/search/index',
                    'url_extra'=>'',
                    ],
                    // [
                    // 'name'=>'最新订单',
                    // 'ico'=>'',
                    // 'url'=>'/manager/order/news_more',
                    // 'url_extra'=>'',
                    // ],
                    [
                    'name'=>'最新订单(新)',
                    'ico'=>'',
                    'url'=>'/manager/order/e_news',
                    'url_extra'=>'',
                    ],
                    // [
                    // 'name'=>'异常订单',
                    // 'ico'=>'',
                    // 'url'=>'/manager/order/abnormity',
                    // 'url_extra'=>'',
                    // ],
                    // [
                    // 'name'=>'超时订单',
                    // 'ico'=>'',
                    // 'url'=>'/manager/order/overtime',
                    // 'url_extra'=>'',
                    // ],
                     
                    //  [
                    // 'name'=>'所有订单',
                    // 'ico'=>'',
                    // 'url'=>'/manager/order/index',
                    // 'url_extra'=>'',
                    // ], 
                    [
                    'name'=>'挂位',
                    'ico'=>'',
                    'url'=>'/manager/store/postion',
                    'url_extra'=>'',
                    ],[
                    'name'=>' - 时间线',
                    'ico'=>'',
                    'url'=>'manager/order/timeline',
                    'url_extra'=>'',
                    'hide'=>'true'
                     ],[
                    'name'=>' - 打印单据',
                    'ico'=>'',
                    'url'=>'manager/order/order_print',
                    'url_extra'=>'',
                    'hide'=>'true'
                    ],[
                        'name'=>' - 查看订单详情',
                        'ico'=>'',
                        'url'=>'manager/order/order_detail',
                        'url_extra'=>'',
                        'hide'=>'true'
                    ],
                     
               
                 ]
            ],'store' => [
            'name'=>'门店管理',
            'ico'=>'fa fa-gear',
            'url'=>'store/index/default',
            'child'=>[
                [
                    'name'=>'查看店员',
                    'ico'=>'',
                    'url'=>'/manager/index/user',
                    'url_extra'=>'',
                ],
                [
                    'name'=>'角色&权限',
                    'ico'=>'',
                    'url'=>'/manager/rbac/index',
                    'url_extra'=>'',
                ],
                [
                    'name'=>' - 修改员工信息',
                    'ico'=>'',
                    'url'=>'manager/index/edit_user',
                    'url_extra'=>'',
                    'hide'=>'true'
                ],
                [
                    'name'=>'优惠券赠送',
                    'ico'=>'',
                    'url'=>'/manager/index/discount',
                    'url_extra'=>'',
                    ]
            ]
        ],'test' => [
            'name'=>'迭代功能测试',
            'ico'=>'fa fa-gear',
            'url'=>'manager/index/testdefault',
            'child'=>[
               [
                'name'=>'测试打印',
                'ico'=>'',
                'url'=>'manager/index/test',
                'url_extra'=>'',
                ],
                // [
                // 'name'=>'测试订单',
                // 'ico'=>'',
                // 'url'=>'/manager/order/news_more',
                // 'url_extra'=>'',
                // ],
            ]
        ]

    ],


  

];
