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
    // 默认输出类型后台默认不加HTML
    // 'default_return_type'    => '',

    // +----------------------------------------------------------------------
    // | 模板设置
    // +----------------------------------------------------------------------

    
'adminmenu'               => [

    [
        // 全局设置
        'name'=>'glob_set_name',
        'ico'=>'fa-cog',
        'url'=>'admin/setting/default',
        'child'=>[
            [
            // 站点信息
            'name'=>'web_set_name',
            'ico'=>'',
            'url'=>'/admin/setting/site',
            'url_extra'=>'',
            ],
            [
            // 基本设置
            'name'=>'base_set_name',
            'ico'=>'',
            'url'=>'/admin/setting/base',
            'url_extra'=>'',
            ],

            [
            // 注册访问
            'name'=>'reg_view_name',
            'ico'=>'',
            'url'=>'/admin/setting/register',
            'url_extra'=>'',
            ],
            [
            // 站点功能
            'name'=>'web_function_name',
            'ico'=>'',
            'url'=>'/admin/setting/funcs',
            'url_extra'=>'?',
                ],
            [
            // 分类设置
            'name'=>'category_set_name',
            'ico'=>'',
            'url'=>'/admin/category/index'
                ],
            
             [
            // 邮件设置
            'name'=>'mail_set_name',
            'ico'=>'',
            'url'=>'/admin/setting/mail',
            'url_extra'=>'',
                ],
             [
            // 开放平台
            'name'=>'admin_open_name',
            'ico'=>'',
            'url'=>'/admin/setting/openid',
            'url_extra'=>'',
                ],
             [
            // 界面设置
            'name'=>'visual_set_name',
            'ico'=>'',
            'url'=>'/admin/setting/template',
            'url_extra'=>'',
                ],
            [
            // 分成设置
            'name'=>'分成设置',
            'ico'=>'',
            'url'=>'/admin/setting/spread',
            'url_extra'=>'',
                ],
             [
            // sxwxopen
            'name'=>'宿洗小哥',
            'ico'=>'',
            'url'=>'/admin/setting/sxwxopen'
                ],
            [
            // sxwxopen
            'name'=>'官网邮件',
            'ico'=>'',
            'url'=>'/admin/setting/indexmail'
                ],
            [
            // sxwxopen
            'name'=>'发票内容',
            'ico'=>'',
            'url'=>'/admin/setting/invoice_content'
                ],
            [
            // sxwxopen
            'name'=>'数据分析',
            'ico'=>'',
            'url'=>'/admin/xcx/index'
                ],
        ],
    ],
     'goods'=>[
        // 
        'name'=>'商品管理',
        'ico'=>'fa-shopping-cart',
        'url'=>'goods/message/default',
        'child'=>[
              [
                'name'=>'商品分类',
                'ico'=>'',
                'url'=>'/goods/cat/index',
                'url_extra'=>'',
                ],
            [
                'name'=>'商品分类标签',
                'ico'=>'',
                'url'=>'/goods/tag/index',
                'url_extra'=>'',
                ],
             [
                'name'=>'运费管理',
                'ico'=>'',
                'url'=>'/order/express/index',
                'url_extra'=>'',
                ],
            [
                'name'=>'优惠券管理',
                'ico'=>'',
                'url'=>'/goods/coupon/index',
                'url_extra'=>'',
            ],
             
         
            
        ],
    ],
    'coupon'=>[
        // 
        'name'=>'活动管理',
        'ico'=>'fa-gamepad',
        'url'=>'goods/message/default',
        'child'=>[
              [
                'name'=>'宿卡活动配置',
                'ico'=>'',
                'url'=>'/admin/sxcard/setting',
                'url_extra'=>'',
                ],
            [
                'name'=>'首件免单活动配置',
                'ico'=>'',
                'url'=>'/admin/sxcard/firstfree',
                'url_extra'=>'',
                ],
                [
                'name'=>'充值赠送配置',
                'ico'=>'',
                'url'=>'/goods/recharge/index',
                'url_extra'=>'',
                ],
        ],
    ],
     'order'=>[
        // 
        'name'=>'订单管理',
        'ico'=>'fa-money',
        'url'=>'ordert/message/default',
        'child'=>[
            [
                'name'=>'订单列表',
                'ico'=>'',
                'url'=>'/order/admin/lists',
                'url_extra'=>'',
                ],   
        ],
    ],
    'store'=>[
        // 
        'name'=>'门店管理',
        'ico'=>'fa-medium',
        'url'=>'store/message/default',
        'child'=>[
            [
                'name'=>'门店列表',
                'ico'=>'',
                'url'=>'/store/admin/lists',
                'url_extra'=>'',
            ],
             [
                'name'=>'门店职位管理',
                'ico'=>'',
                'url'=>'/store/department/lists',
                'url_extra'=>'',
            ],
            [
                'name'=>'门店权限规则',
                'ico'=>'',
                'url'=>'/store/admin/rule',
                'url_extra'=>'',
            ],
             
         
            
        ],
    ],
    'adminmenu'=>[
        // 
        'name'=>'content_model_name',
        'ico'=>'fa-puzzle-piece',
        'url'=>'adminmenu/message/default',
        'child'=>[
        ],
    ],
   
    [
        // 用户中心
        'name'=>'user_center_name',
        'ico'=>'fa-group',
        'url'=>'ucenter/message/default',
        'child'=>[
            [
            // 用户列表
            'name'=>'user_list_name',
            'ico'=>'',
            'url'=>'admin/user/index'
                ],
            [
                // 用户列表
                'name'=>'宿洗用户',
                'ico'=>'',
                'url'=>'admin/user/user_list_coupon'
            ],



        ],
    ],
     [
        // 权限管理
        'name'=>'power_message_name',
        'ico'=>'fa-key',
        'url'=>'power/message/default',
        'child'=>[
            [
            // 管理员列表
            'name'=>'admin_list_name',
            'ico'=>'',
            'url'=>'admin/user/admin'
                ],
            [
            // 用户组
            'name'=>'admin_user_group',
            'ico'=>'',
            'url'=>'admin/user/group'
                ],
            
        ],
    ],
     [
        // 微信管理
        'name'=>'微信管理',
        'ico'=>'fa-weixin',
        'url'=>'wx/message/default',
        'child'=>[
            [
            // 
            'name'=>'微信',
            'ico'=>'',
            'url'=>'wx/admin/wxlist'
            ]
          
        ],
    ],
    [
        // 运营管理
        'name'=>'operation_management_name',
        'ico'=>'fa-pie-chart',
        'url'=>'operation/message/default',
        'child'=>[
            [
            // 
            'name'=>'adv_manager_name',
            'ico'=>'',
            'url'=>'admin/adv/advpostion'
            ],
            [
            // 
            'name'=>'酒店管理',
            'ico'=>'',
            'url'=>'admin/hotel/lists'
            ],
            [
            // 
            'name'=>'推广管理',
            'ico'=>'',
            'url'=>'admin/spread/lists'
            ],
             [
            // 
            'name'=>'留言管理',
            'ico'=>'',
            'url'=>'admin/spread/leaving'
            ]

          
        ],
    ],
     [
        'name'=>'物流端',
        'ico'=>'fa-fighter-jet',
        'url'=>'operation/rz/default',
        'child'=>[
          [
            // 
            'name'=>'实名认证申请',
            'ico'=>'',
            'url'=>'admin/user/express_realname'
            ],
            
            [
            // 
            'name'=>'提现申请',
            'ico'=>'',
            'url'=>'admin/WithdrawCash/lists'
            ],
            [
            // 
            'name'=>'物流人员列表',
            'ico'=>'',
            'url'=>'admin/user/express_users'
            ],
            
            
        ],
    ],
    [
        'name'=>'酒店端',
        'ico'=>'fa-building',
        'url'=>'hotel/drupal/default',
        'child'=>[
          
            [
            // 
            'name'=>'酒店认证',
            'ico'=>'',
            'url'=>'admin/User/hotel_authen'
            ],
            [
            //
            'name'=>'酒店提现申请',
            'ico'=>'',
            'url'=>'admin/WithdrawCash/hotelcash'
            ],
            [
            //
            'name'=>'酒店找回密码申请',
            'ico'=>'',
            'url'=>'admin/User/hotel_backpass'
            ],
            [
            //
            'name'=>'酒店用户列表',
            'ico'=>'',
            'url'=>'admin/User/hotel_users'
            ],
            
        ],
    ],[
        'name'=>'自定义数据',
        'ico'=>'fa-drupal',
        'url'=>'self/data/default',
        'child'=>[
          [
            // 
            'name'=>'自定义评价',
            'ico'=>'',
            'url'=>'admin/Selfdb/comment'
            ],
            
        ],
    ],
 
],


];
