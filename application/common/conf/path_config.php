<?php
/**
 * Groups configuration for default Minify implementation
 * @package Minify
 */

/**
 * You may wish to use the Minify URI Builder app to suggest
 * changes. http://yourdomain/min/builder/
 *
 * See http://code.google.com/p/minify/wiki/CustomSource for other ideas
 **/

$static_path = "/public/static/";
$tmake_path = $static_path."system/Tmake/";
$init_path   = $tmake_path. "init/";
$libs_path   = $tmake_path. "core/";
$c_js_path   = $tmake_path. "c_js/";
$c_css_path   = $tmake_path. "c_css/";
$manager_path = "/public/static/template/admin/";
return [
    'layer_js'     =>[ ##layui
            // $init_path. "jquery.min.js",
            '/public/static/lay/layui/layui.js',
            "/public/static/tPublic/action.js",
            "/public/static/tPublic/boot-think.js",
        
        ],
    'layer_css'     =>[ ##layui
        '/public/static/lay/layui/css/layui.css',
        "/public/static/tPublic/boot-think.css",
        
        ],
    'font'=>[ ##字体图片
        $c_css_path."font-awesome.min.css",
    ],
    'core_js' => [ // 默认加载
        $init_path. "bootstrap.min.js",
        $init_path. "jquery.slimscroll.min.js",
        $init_path. "jquery.scrollLock.min.js",
        $init_path. "jquery.appear.min.js",
        $init_path. "jquery.countTo.min.js",
        $init_path. "jquery.placeholder.min.js",
        $init_path. "js.cookie.min.js",
        $libs_path. "bootstrap3-editable/js/bootstrap-editable.js",
        $libs_path. "magnific-popup/magnific-popup.min.js",

        $c_js_path . "app.js",
        $c_js_path . "thinkask.js",
        $c_js_path . "form.js",
        $c_js_path . "aside.js",
        $c_js_path . "table.js",

        $libs_path. "bootstrap-notify/bootstrap-notify.min.js",
        $libs_path. "sweetalert/sweetalert.min.js",



    ],
    'public_onui_css'=>[
        ##公共的ONUICSS，保留了其它的表单样式。去掉了一些公共样式，解决冲突的问题
        $c_css_path . "public_oneui.css",
    ],
    'core_css' => [ // 默认加载
        $libs_path. "bootstrap3-editable/css/bootstrap-editable.css",
        $libs_path. "magnific-popup/magnific-popup.min.css",
        $c_css_path . "bootstrap.min.css",
        $c_css_path . "oneui.css",
        "/public/static/lay/layui/css/layui.css",
        $c_css_path . "thinkask.css",

        $libs_path. "sweetalert/sweetalert.min.css",
    ],
    'datepicker_js' => [ // 日期选择
        $libs_path. "bootstrap-datepicker/bootstrap-datepicker.min.js",
        $libs_path. "bootstrap-datepicker/locales/bootstrap-datepicker.zh-CN.min.js",
    ],
    'datepicker_css' => [ // 日期选择
        $libs_path. "bootstrap-datepicker/bootstrap-datepicker3.min.css",
    ],
    'datetimepicker_js' => [ // 日期时间选择
        $libs_path. "bootstrap-datetimepicker/moment.min.js",
        $libs_path. "bootstrap-datetimepicker/bootstrap-datetimepicker.min.js",
        $libs_path. "bootstrap-datetimepicker/locale/zh-cn.js",
    ],
    'datetimepicker_css' => [ // 日期时间选择
        $libs_path. "bootstrap-datetimepicker/bootstrap-datetimepicker.min.css"
    ],
    'webuploader_js' => [ // 文件或图片上传
        $libs_path. "webuploader/webuploader.min.js",
    ],
    'webuploader_css' => [ // 文件或图片上传
        $libs_path. "webuploader/webuploader.css",
    ],
    'select2_js' => [ // 下拉框
        $libs_path. "select2/select2.full.min.js",
    ],
    'select2_css' => [ // 下拉框
        $libs_path. "select2/select2.min.css",
        $libs_path. "select2/select2-bootstrap.min.css",
    ],
    'tags_js' => [ // 标签
        $libs_path. "jquery-tags-input/jquery.tagsinput.min.js",
    ],
    'tags_css' => [ // 标签
        $libs_path. "jquery-tags-input/jquery.tagsinput.min.css",
    ],
    'validate_js' => [ // 验证
        $libs_path. "jquery-validation/jquery.validate.min.js",
    ],
    'editable_js' => [ // 快速编辑
        $libs_path. "bootstrap3-editable/js/bootstrap-editable.js",
    ],
    'editable_css' => [ // 快速编辑
        $libs_path. "bootstrap3-editable/css/bootstrap-editable.css",
    ],
    'colorpicker_js' => [ // 取色器
        $libs_path. "bootstrap-colorpicker/bootstrap-colorpicker.min.js",
    ],
    'colorpicker_css' => [ // 取色器
        $libs_path. "bootstrap-colorpicker/css/bootstrap-colorpicker.min.css",
    ],
    'editormd_js' => [ // markdown编辑器
        $libs_path. "editormd/editormd.min.js",
    ],
    'jcrop_js' => [ // 图片裁剪
        $libs_path. "jcrop/js/Jcrop.min.js",
    ],
    'jcrop_css' => [ // 图片裁剪
        $libs_path. "jcrop/css/Jcrop.min.css",
    ],
    'masked_inputs_js' => [ // 格式文本
        $libs_path. "masked-inputs/jquery.maskedinput.min.js",
    ],
    'rangeslider_js' => [ // 范围
        $libs_path. "ion-rangeslider/js/ion.rangeSlider.min.js",
    ],
    'rangeslider_css' => [ // 范围
        $libs_path. "ion-rangeslider/css/ion.rangeSlider.min.css",
        $libs_path. "ion-rangeslider/css/ion.rangeSlider.skinHTML5.min.css",
    ],
    'nestable_js' => [ // 拖拽排序
        $libs_path. "jquery-nestable/jquery.nestable.js",
    ],
    'nestable_css' => [ // 拖拽排序
        $libs_path. "jquery-nestable/jquery.nestable.css",
    ],
    'wangeditor_js' => [ // wang编辑器
        $libs_path. "wang-editor/js/wangEditor.min.js",
    ],
    'wangeditor_css' => [ // wang编辑器
        $libs_path. "wang-editor/css/wangEditor.min.css",
    ],
    'summernote_js' => [ // summernote编辑器
        $libs_path. "summernote/summernote.min.js",
        $libs_path. "summernote/lang/summernote-zh-CN.js",
    ],
    'summernote_css' => [ // summernote编辑器
        $libs_path. "summernote/summernote.min.css",
    ],
    'admin_init_css'=>[
            "/public/static/system/Tmake/c_css/bootstrap.min.css",
            "/public/static/system/Tmake/c_css/oneui.css",
            "/public/static/system/Tmake/c_css/thinkask.css",
            "/public/static/system/Tmake/c_css/admin.css",
    ],
    'admin_iframe_parent_css'=>[
    ##后台父级级
    '/public/static/template/admin/css/bootstrap.min.css',##<!--Bootstrap Stylesheet [ REQUIRED ]-->
    '/public/static/template/admin/css/nifty.min.css',##<!--Nifty Stylesheet [ REQUIRED ]-->
    '/public/static/template/admin/themify-icons/themify-icons.min.css',##<!--Themify Icons [ OPTIONAL ]-->
    '/public/static/template/admin/css/pace.min.css',

    ],
    'admin_iframe_parent_js'=>[
    ##后台父级级+
        '/public/static/template/admin/js/jquery-2.2.4.min.js',
        '/public/static/template/admin/js/pace.min.js',
        // '/public/static/template/admin/js/bootstrap.min.js',##<!-3.3.6-->
        // '/public/static/system/Tmake/init/bootstrap.min.js',## <!-3.3.5-->
        '/public/static/template/admin/js/nifty.min.js'

    ],
    'manager_css'=>[
    ##MANAGER管理
        '/public/static/template/admin/css/bootstrap.min.css',
        '/public/static/template/admin/css/nifty.min.css',
         '/public/static/template/admin/themify-icons/themify-icons.min.css',##<!--Themify Icons [ OPTIONAL ]-->
        '/public/static/template/admin/css/pace.min.css',

    ],
    'jquery'=>[
     '/public/static/template/admin/js/jquery-2.2.4.min.js',
    ],
     'manager_js'=>[
    ##MANAGER管理
        '/public/static/template/admin/js/nifty.min.js',
        '/public/static/template/manager/manager.js',
    ],
    'manager_plugins_js'=>[
        '/public/static/template/admin/plugins/pace/pace.min.js',
    ##MANAGER管理所有插件JS
    ],
    'manager_plugins_css'=>[
        '/public/static/template/admin/plugins/pace/pace.min.css',

    ## MANAGER管理所有插件css
    ],

];