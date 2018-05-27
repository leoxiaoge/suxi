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
    'mac_table'=>[
        [
        'title'=>'基本设置',
        'url'=>url('manager/store/setting'),
        'icon'=>'prefapp.png'
        ],
        [
        'title'=>'最新订单',
        'url'=>url('manager/order/e_news'),
        'icon'=>'icloud.png'
        ]
        ,
        [
        'title'=>'修改密码',
        'url'=>url('manager/ucenter/changepwd'),
        'icon'=>'finder.png'
        ]

    ]
      


  

];
