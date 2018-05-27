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
    // | 模板设置
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
    'formtype'=>[
         'text' => '单文本',
        'textarea' => '多文本',
        'tags' => 'tags 标签',
        'time' => '时间戳',
        'btn' => '按钮',
        'trigger' => '触发器',
        'array' => '数组',
        'bmap' => '百度地图',
        'checkbox' => '多选框',
        'switch' => 'switch 开关',
        'colorpicker' => '取色器',
        'date' => 'date 日期',
        'daterange' => 'daterange 日期区间',
        'datetime' => 'datetime 日期+时间',
        'number' => '数字',
        'password' => '密码',
        'radio' => '单选框',
        'select' => '下拉菜单',
        'file' => '单文件',
        'files' => '多文件',
        'group' => 'group 组',
        'hidden' => 'hidden 隐藏域',
        'validate' => 'validate 验证规则',
        'icon' => 'icon 图标选择器',
        'image' => '单图片',
        'images' => '金图片',
        'jcrop' => 'jcrop',
        'linkage' => 'linkage 普通联动',
        'linkages' => 'linkages 快速联动',
        // 'masked' => 'masked',
        
        'range' => 'range 区间',
        
        // 'sort' => 'sort',
        // 'static' => 'static',
        'summernote' => 'summernote 编辑器',
        'editormd' => 'editormd 编辑器',
        'ckeditor' => 'ckeditor 编辑器',
        'ueditor' => 'ueditor 编辑器',
        'wangeditor' => 'wangeditor 编辑器',
        // 'formitem' => 'formitem',
        // 'formitems' => 'formitems',

    ],

         // 申请表的状态
        'SQLTYPE' =>array(
            'VARCHAR' => array(
                //值 
                "value"         =>"VARCHAR",
                //最长
                "maxlength"     =>255,
                //小数点位数
                "decimal"       =>0,
                //正则
                "pattern" =>"",
                'errortips'=>"",
                ),
             'TEXT' => array(
                //值 
                "value"         =>"TEXT",
                //最长
                "maxlength"     =>0,
                //小数点位数
                "decimal"       =>0,
                 //正则
                "pattern" =>"",
                'errortips'=>"",
                ),
             'longtext' => array(
                //值 
                "value"         =>"longtext",
                //最长
                "maxlength"     =>0,
                //小数点位数
                "decimal"       =>0,
                 //正则
                "pattern" =>"",
                'errortips'=>"",
                ),

             'INT' => array(
                //无法设置小数点
                //值 
                "value"         =>"INT",
                //最长
                "maxlength"     =>11,
                //小数点位数
                "decimal"       =>0,
                 //正则
                "pattern" =>"/^[0-9-]+$/",
                'errortips'=>"整数",
                ),
             'tinyint' => array(
                //无法设置小数点
                //值 
                "value"         =>"tinyint",
                //最长
                "maxlength"     =>4,
                //小数点位数
                "decimal"       =>0,
                 //正则
                "pattern" =>"/^[0-9-]+$/",
                'errortips'=>"整数",
                ),
             'bigint' => array(
                //无法设置小数点
                //值 
                "value"         =>"bigint",
                //最长
                "maxlength"     =>20,
                //小数点位数
                "decimal"       =>0,
                 //正则
                "pattern" =>"/^[0-9-]+$/",
                'errortips'=>"整数",
                ),
            'decimal' => array(
                //无法设置小数点
                //值 
                "value"         =>"decimal",
                //最长
                "maxlength"     =>10,
                //小数点位数
                "decimal"       =>0,
                 //正则
                "pattern" =>"/^[0-9.-]+$/",
                'errortips'=>"数字",
                ),
            'float' => array(
                //无法设置小数点
                //值 
                "value"         =>"float",
                //最长
                "maxlength"     =>0,
                //小数点位数
                "decimal"       =>2,
                 //正则
                "pattern" =>"/^[0-9.-]+$/",
                'errortips'=>"数字",
                ),
            // '' =>"float",
            // 'date' =>"date",
            // 'time' =>"time",
            // 'datetime' =>"datetime",
            // 'timestamp' =>"timestamp",
            // 'year' =>"year",
            // 'smallint' =>"smallint",
            // 'mediumint' =>"mediumint",
            
            // 'double' =>"double",
            // 'char' =>"char",
            // 'tinytext' =>"tinytext",
            // 'mediumtext' =>"mediumtext",
            // 
            ),


         // 常用正则表
        'REGEX' =>array(
            0=>array(
                'ename'=>"number",
                'name'=>"数字",
                'regex'=>"/^[0-9.-]+$/",
                //验证未通过时的提示信息
                'errortips'=>"数字",

                ),
             1=>array(
                'ename'=>"integer",
                'name'=>"整数",
                'regex'=>"/^[0-9-]+$/",

                ),
              2=>array(
                'ename'=>"nandl",
                'name'=>"数字+字母",
                'regex'=>"/^[0-9a-z]+$/i",
                'errortips'=>"数字+字母",

                ),
              3=>array(
                'ename'=>"letter",
                'name'=>"字母",
                'regex'=>"/^[a-z]+$/i",

                ),
              4=>array(
                'ename'=>"email",
                'name'=>"邮件",
                'regex'=>"/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/",
                'errortips'=>"邮件",

                ),
              5=>array(
                'ename'=>"url",
                'name'=>"URL链接",
                'regex'=>"/^http:\/\//",
                'errortips'=>"URL链接",

                ),
              6=>array(
                'ename'=>"qq",
                'name'=>"QQ",
                'regex'=>"/^[0-9]{5,20}$/",
                'errortips'=>"QQ",

                ),
              7=>array(
                'ename'=>"phone",
                'name'=>"手机号",
                'regex'=>"/^(1)[0-9]{10}$/",
                'errortips'=>"手机号",

                ),
              8=>array(
                'ename'=>"mobile",
                'name'=>"电话号",
                'regex'=>"/^[0-9-]{6,13}$/",
                'errortips'=>"电话号",

                ),
               9=>array(
                'ename'=>"mobile",
                'name'=>"任意字符",
                'regex'=>"/\s\S/",
                'errortips'=>"任意字符",

                ),
         
            ),

];
