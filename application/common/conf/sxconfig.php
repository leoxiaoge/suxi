<?php
##宿洗配置项目

return [
// 0下单成功未支付-1下单成功已支付商家未接单-3商家已接单-4商家已通知快递员上门取件-5快递员已取件-6商家收到货品-7商家完成订单-8快递员已取件正在配送-9已经送达-10送达成功-11送达失败-12用户删除订 单
    'order_status'=>[
        '-1'=>'用户下单，但没有提交地址等信息',
        '-8'=>'预约订单',
        0=>'未支付',##下单成功未支付
        1=>'已支付',##下单成功已支付商家未接单
        3=>'已接单',##商家已接单
        4=>'取件中',##商家已通知快递员上门取件
        5=>'前台检查',##快递员已取件( 检查 )
        6=>'开始清洗',##商家收到货品
        7=>'完成清洗',##商家完成订单
        8=>'开始配送',##快递员已取件正在配送
        9=>'已经送达',##已经送达
        10=>'送达成功',##送达成功(并且已评价)
        11=>'送达失败',##送达失败
        12=>'用户取消订单',##用户取消订单
        ##新规则
        '-10'=>'拒绝接单',
        '-20'=>'取消预约',
        '-30'=>'取件失败',
        '-40'=>'配送失败',
        '-50'=>'用户删除订单',

        //临时添加
        '-25'=>'取件超时',
        '-85'=>'送件超时',
        // '-99'=>'异常订单',
        '-70'=>'送件拒绝',
        //---------
        10=>'预约成功',##用户预约成功
        20=>'前往取件',##前往取件
        30=>'未支付',##下单成功未支付
        40=>'已支付',##下单成功已支付商家未接单
        50=>'已接单',##商家已接单，送往门店中
        60=>'洗前检查',##快递员已取件( 检查 )
        61=>'衣服分类',
        62=>'衣服洗前处理',
        63=>'衣服洗涤(干洗/水洗)', ##开始清洗
        64=>'衣服洪干',   
        65=>'衣服整烫(熨烫/缝补)',
        66=> '衣服洗后检查',
        70=>'完成订单清洗',##商家完成订单，待配送员取件
        80=>'开始配送',##快递员已取件正在配送
        90=>'配送完成',##已经送达
        100=>'送达成功',##送达成功(并且已评价)

    ],
    'order_info'=>[
        30=>'下单成功，等待付款', //确认订单，等待付款
        40=>'已支付',
        50=>'已接单',##商家已接单，送往门店中
        60=>'洗前检查',##快递员已取件( 检查 )
        61=>'衣服分类',
        62=>'衣服洗前处理',
        63=>'衣服洗涤(干洗/水洗)', ##开始清洗
        64=>'衣服洪干',   
        65=>'衣服整烫(熨烫/缝补)',
        66=> '衣服洗后检查',
        70=>'完成订单清洗',##商家完成订单，待配送员取件
        80=>'开始配送',##快递员已取件正在配送
        90=>'配送完成',##已经送达
        100=>'送达成功',##送达成功(并且已评价)

    ],
    'wash_step'=>['衣服分类','衣服洗前处理','衣服洗涤(干洗/水洗)','衣服洪干','衣服整烫(熨烫/缝补)','衣服洗后检查'],
    ##提现
    'withdraw_status'=>[
        '1'=>'审核中',
        '2'=>'通过审核打款中',
        '3'=>'完成打款',
        '-1'=>'申请失败',
        // 1：申请提现，2：通过审核打款中，3完成打款，-1：申请失败
    ],
    'transition_time'=>strtotime("2017-11-14"),##新老版本过渡时间

    'save_img_path'=>PUBLIC_PATH."wx_user/images",
    //微信正式
    'appid_one'=>'wxeefecafcd02450af',
    'mch_id_one'=>'1487031592',
    'key_one'=>'38b0376bde3ec359bbd30deb2a8d07a2',
    'secret_one'=>'117d8ae7a22a417380c1a4af7fe906c4',

    //微信测试 速洗测试
    // 'appid_one'=>'wx86a80f1c141d98a7',
    // 'mch_id_one'=>'1483141062',
    // 'key_one'=>'38b0376bde3ec359bbd30deb2a8d07a2',
    // 'secret_one'=>'2d30651b895796b50c2ecf3bed927f28',

    //短信
    'sms_appid'=>1400037030,
    'sms_appkey'=>'3472a1e245731fb5041464de63753ed6',
    'sms_tempid'=>31931,
    'domain_url'=>'https://www.qiaolibeilang.com/',
    ##宿洗小哥
    'sxxg_appid'=>'wxfc13d9bf8d4c0816',
    'sxxg_token'=>'qlbl',
    'sxxg_AppSecret'=>'2b88eb6a0897fd773787b9f1cd476d9c',
    'sxxg_encodingaeskey' =>'TsLTR845YOTXYSsCdOQS5SnyYftBPUboplA1IaLygN6',
    ##宿洗
    'sx_appid'=>'wx2b386010f6162be5',
    'sx_token'=>'qlbl',
    'sx_AppSecret'=>'8b668882043739864a3c447a1edb17eb',
    'sx_encodingaeskey' =>'TsLTR845YOTXYSsCdOQS5SnyYftBPUboplA1IaLygN6',
    'bank'=>[
        '中国银行'=>[
            'name'=>'中国银行',
            'bankimg1'=>'ic-zhongguoyinhang',
            'bankimg2'=>'ic-zhongguoyinhang',
            'color1'   =>'#972030',
            'color2'   =>'#A78169',
            'backgroupcolor'=>'#AA9273',
            ],
        '工商银行'=>[
            'name'=>'工商银行',
            'bankimg1'=>'ic-gongshangyinhang',
            'bankimg2'=>'ic-gongshangyinhang',
            'color1'  =>'#AB2C1A',
            'color2'  =>'#A83020',
            'backgroupcolor'=>'#C29143',
            ],
        '建设银行'=>[
            'name'=>'建设银行',
            'bankimg1'=>'ic-jiansheyinhang',
            'bankimg2'=>'ic-jiansheyinhang',
            'color1'  =>'#053D8F',
            'color2'  =>'#3B1E34',
            'backgroupcolor'=>'#451924',
            ],
        '民生银行'=>[
            'name'=>'民生银行',
            'bankimg1'=>'ic-minshengyinhang',
            'bankimg2'=>'ic-minshengyinhang',
            'color1'  =>'#178EC6',
            'color2'  =>'#3C787C',
            'backgroupcolor'=>'#427182',
            ],
        '农业银行'=>[
            'name'=>'农业银行',
            'bankimg1'=>'ic-nongyeyinhang',
            'bankimg2'=>'ic-nongyeyinhang',
            'color1'  =>'#008566',
            'color2'  =>'#396462',
            'backgroupcolor'=>'#435E61',
            ],
        '中信银行'=>[
            'name'=>'中信银行',
            'bankimg1'=>'ic-zhongxinyinhang',
            'bankimg2'=>'ic-zhongxinyinhang',
            'color1'  =>'#D60011',
            'color2'  =>'#5E2A26',
            'backgroupcolor'=>'#49312A',
            ],
        '广发银行'=>[
            'name'=>'广发银行',
            'bankimg1'=>'ic-guangfayinhang',
            'bankimg2'=>'ic-guangfayinhang',
            'color1'  =>'#E5001F',
            'color2'  =>'#6A4766',
            'backgroupcolor'=>'#555373',
            ],
        '中国邮政'=>[
            'name'=>'中国邮政',
            'bankimg1'=>'ic-youzhengyinhang',
            'bankimg2'=>'ic-youzhengyinhang',
            'color1'  =>'#007047',
            'color2'  =>'#66AE89',
            'backgroupcolor'=>'#57A57F',
            ],
        '交通银行'=>[
            'name'=>'交通银行',
            'bankimg1'=>'ic-jiaotongyinhang',
            'bankimg2'=>'ic-jiaotongyinhang',
            'color1'  =>'#11366F',
            'color2'  =>'#1F5367',
            'backgroupcolor'=>'#1D4F68',
            ],
        '光大银行'=>[
            'name'=>'光大银行',
            'bankimg1'=>'ic-guangdayinhang',
            'bankimg2'=>'ic-guangdayinhang',
            'color1'  =>'#F8981D',
            'color2'  =>'#CA7C6A',
            'backgroupcolor'=>'#C27778',
            ],
        '华夏银行'=>[
            'name'=>'华夏银行',
            'bankimg1'=>'ic-huaxiayinhang',
            'bankimg2'=>'ic-huaxiayinhang',
            'color1'  =>'#E50012',
            'color2'  =>'#8B87B3',
            'backgroupcolor'=>'#7B9FCF',
            ],
        '招商银行'=>[
            'name'=>'招商银行',
            'bankimg1'=>'ic-zhaoshangyinhang',
            'bankimg2'=>'ic-zhaoshangyinhang',
            'color1'  =>'#E41E26',
            'color2'  =>'#C05D34',
            'backgroupcolor'=>'#BA6837',
            ],
        '上海银行'=>[
            'name'=>'上海银行',
            'bankimg1'=>'ic-shanghaiyinhang',
            'bankimg2'=>'ic-shanghaiyinhang',
            'color1'  =>'#0035D9',
            'color2'  =>'#837458',
            'backgroupcolor'=>'#6D6967',
            ],
        '平安银行'=>[
            'name'=>'平安银行',
            'bankimg1'=>'ic-pinganyinhang',
            'bankimg2'=>'ic-pinganyinhang',
            'color1'  =>'#FF3204',
            'color2'  =>'#884E4A',
            'backgroupcolor'=>'#735356',
            ],
        '兴业银行'=>[
            'name'=>'兴业银行',
            'bankimg1'=>'ic-xingyeyinhang',
            'bankimg2'=>'ic-xingyeyinhang',
            'color1'  =>'#004186',
            'color2'  =>'#6F424C',
            'backgroupcolor'=>'#824242',
            ],
        '浦发银行'=>[
            'name'=>'浦发银行',
            'bankimg1'=>'ic-pufayinhang',
            'bankimg2'=>'ic-pufayinhang',
            'color1'  =>'#2D5082',
            'color2'  =>'#413B51',
            'backgroupcolor'=>'#443749',
            ],
        '北京银行'=>[
            'name'=>'北京银行',
            'bankimg1'=>'ic-pufayinhang',
            'bankimg2'=>'ic-pufayinhang',
            'color1'  =>'#C11E22',
            'color2'  =>'#C36F58',
            'backgroupcolor'=>'#C37D61',
            ],
        ],
        'month'=>[
            '一月'=>[
                'name'=>'一月',
                'value'=>'1',
                ],
            '二月'=>[
                'name'=>'二月',
                'value'=>'2',
                ],
            '三月'=>[
                'name'=>'三月',
                'value'=>'3',
                ],
            '四月'=>[
                'name'=>'四月',
                'value'=>'4',
                ],
            '五月'=>[
                'name'=>'五月',
                'value'=>'5',
                ],
            '六月'=>[
                'name'=>'六月',
                'value'=>'6',
                ],
            '七月'=>[
                'name'=>'七月',
                'value'=>'7',
                ],
            '八月'=>[
                'name'=>'八月',
                'value'=>'8',
                ],
            '九月'=>[
                'name'=>'九月',
                'value'=>'9',
                ],
            '十月'=>[
                'name'=>'十月',
                'value'=>'10',
                ],
            '十一月'=>[
                'name'=>'十一月',
                'value'=>'11',
                ],
            '十二月'=>[
                'name'=>'十二月',
                'value'=>'12',
                ],
        ],

//测试商户号1483141062
//测试appidwx86a80f1c141d98a7
   // 6bc4848260aa4eeab96462db29f010d2
//38b0376bde3ec359bbd30deb2a8d07a2
//正式商户号1487031592
//38b0376bde3ec359bbd30deb2a8d07a2
];