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
namespace app\admin\controller;
use app\common\controller\AdminBase;

class Setting extends AdminBase
{

  public function site(){
  return $this->builder('form')
    ->setUrl(url('admin/ajax/saveconfig'))
    ->setPageTitle('站点信息')
    ->addImage('system_logo', '网站logo',"",getset('system_logo'))
    ->addText('site_name', '网站名称',"",getset('site_name'))
    ->addTextarea('description', '网站简介',"",getset('description'))
    ->addTextarea('keywords', '网站关键词',"",getset('keywords'))
    ->addTextarea('icp_beian', '网站 ICP 备案号',"",getset('icp_beian'))
    ->fetch(); 
  }
  public function register(){
    return $this->builder('form')
    ->setUrl(url('admin/ajax/saveconfig'))
    ->setPageTitle('注册与访问')
    ->addRadio('site_close', '站点关闭', '', ['Y' => '是', 'N' => '否'],getset('site_close'))
    ->addText('close_notice', '站点关闭的提示', '',getset('close_notice'))
    ->addRadio('register_seccode', '新用户注册显示验证码', '', ['Y' => '是', 'N' => '否'],getset('register_seccode'))
    ->addRadio('register_type', '注册类型', '', ['open' => '开放注册', 'close' => '关闭注册'],getset('register_type'))
    ->addNumber('username_length_min', '用户名最少字符数','注: 一个汉字等于 2 个字符',getset('username_length_min'))
    ->addNumber('username_length_max', '用户名最多字符数','注: 一个汉字等于 2 个字符',getset('username_length_max'))
    ->addTextarea('register_agreement', '用户注册协议',"",getset('register_agreement'))
    ->addTextarea('censoruser', '用户注册名不允许出现以下关键字',"每行填写一个关键字, 如: admin, 管理员等",getset('censoruser'))
    ->addTextarea('welcome_message_pm', '新用户注册系统发送的欢迎内容',"新用户注册欢迎内容, 以下变量可作为内容替换: {username}: 用户名{time}: 发送时间{sitename}: 网站名称",getset('welcome_message_pm'))
    ->fetch(); 
  }
  public function funcs(){
    return $this->builder('form')
    ->setUrl(url('admin/ajax/saveconfig'))
    ->setPageTitle('站点功能')
    ->addTextarea('site_announce', "网站公告","",getset('site_announce'))
    ->addTextarea('statistic_code',"网站统计代码", "",getset('statistic_code'))
    ->addTextarea('report_reason',"问题举报理由选项", "每行填写一个举报理由",getset('report_reason'))
    ->addRadio('admin_login_seccode', '管理员后台登录是否需要验证码', '', ['Y' => '是', 'N' => '否'],getset('admin_login_seccode'))


    ->fetch(); 
  }

  public function question(){
       return $this->builder('form')
    ->setUrl(url('admin/ajax/saveconfig'))
    ->setPageTitle('问题设置')
   
    ->addRadio('must_category', '是否强制分组', '', ['Y' => '是', 'N' => '否'],getset('must_category'))
    ->addRadio('open_accusation', '是否开启回复举报', '', ['Y' => '是', 'N' => '否'],getset('open_accusation'))
    ->addNumber('accusation_hidden', '举报多少条后不显示', '填写 0 则被举报马上隐藏不显示',getset('accusation_hidden'))
    ->addRadio('open_comment', '是否开启回复评论', '', ['Y' => '是', 'N' => '否'],getset('open_comment'))
    ->addRadio('open_thanks', '是否开启回复感谢', '', ['Y' => '是', 'N' => '否'],getset('open_thanks'))
    ->addRadio('open_good', '是否开启点赞', '', ['Y' => '是', 'N' => '否'],getset('open_good'))
    ->addNumber('answer_length_lower', '回复内容最小字符数限制', '填写 0 则不限制',getset('answer_length_lower'))
    ->addRadio('new_question_force_add_topic', '新问题强制要求添加标签', '', ['Y' => '是', 'N' => '否'],getset('new_question_force_add_topic'))
    ->addNumber('question_topics_limit', '问题标签数量限制', '填写 0 则不限制',getset('question_topics_limit'))
    ->addNumber('topic_title_limit', '标签标题最大字符数限制', '填写 0 则不限制',getset('topic_title_limit'))
    ->addTextarea('sensitive_words',"敏感词列表", "",getset('sensitive_words'))
    ->fetch(); 
  }
  public function integral(){
     return $this->builder('form')
    ->setUrl(url('admin/ajax/saveconfig'))
    ->setPageTitle('积分与威望')
    ->addRadio('integral_system_enabled', '使用积分系统', '', ['Y' => '是', 'N' => '否'],getset('integral_system_enabled'))
    ->addText('integral_unit', '积分单位', '',getset('integral_unit'))
    ->addNumber('integral_system_config_register', '新用户注册默认拥有积分', '',getset('integral_system_config_register'))
    ->addNumber('integral_system_config_profile', '用户完善资料获得积分（包括头像，一句话介绍，履历等资料）', '',getset('integral_system_config_profile'))
    ->addNumber('integral_system_config_invite', '用户邀请他人注册且被邀请人成功注册', '',getset('integral_system_config_invite'))
    ->addNumber('integral_system_config_new_question', '发起问题', '',getset('integral_system_config_new_question'))
    ->addNumber('integral_system_config_new_answer', '回复问题', '',getset('integral_system_config_new_answer'))
    ->addNumber('integral_system_config_best_answer', '回复被评为最佳回复', '',getset('integral_system_config_best_answer'))
    ->addNumber('integral_system_config_answer_fold', '感谢回复', '',getset('integral_system_config_answer_fold'))
    ->fetch();
  }
  public function permissions(){
     return $this->builder('form')
    ->setUrl(url('admin/ajax/saveconfig'))
    ->setPageTitle('用户权限')
    ->addRadio('answer_unique', '只允许回复一次', '', ['Y' => '是', 'N' => '否'],getset('integral_system_enabled'))
    ->addRadio('answer_self_question', '允许用户回复自己发起的问题', '', ['Y' => '是', 'N' => '否'],getset('integral_system_enabled'))
    ->addRadio('anonymous_enable', '允许匿名发起或回复', '', ['Y' => '是', 'N' => '否'],getset('anonymous_enable'))
    ->fetch();
  }
  public function mail(){
    $mail_config = getset('mail_config');
    return $this->builder('form')
    ->setUrl(url('admin/ajax/saveconfig'))
    ->setPageTitle('邮件设置')
    ->addText('system_message_mail', '系统管理员邮箱', '',getset('system_message_mail'))
    ->addText('mail_config[Host]', 'Host', '',$mail_config['Host'])
    ->addText('mail_config[port]', 'SMTP 端口', '',$mail_config['port'])
    ->addText('mail_config[username]', 'SMTP 端口', '',$mail_config['username'])
    ->addPassword('mail_config[password]', 'SMTP 密码', '',$mail_config['password'])
    ->addText('mail_config[from_email]', '系统邮件来源邮箱地址', '',$mail_config['from_email'])
    ->addText('mail_config[testmail]', '系统测试的邮件地址', '',$mail_config['testmail'])
     ->addRadio('login_success', '登陆成功是否发送邮件', '', ['Y' => '是', 'N' => '否'],getset('login_success'))
     ->addRadio('reg_success', '注册成功是否发送邮件', '', ['Y' => '是', 'N' => '否'],getset('reg_success'))
    ->fetch();
  }
  public function base(){
    return $this->builder('form')
    ->setUrl(url('admin/ajax/saveconfig'))
    ->setPageTitle('基本配置')
    ->addNumber('up_maxSize', '上传最大值(M)', '',getset('up_maxSize'))
    ->addText('allowed_upload_types', '允许的附件文件类型', '',getset('allowed_upload_types'))
    ->addText('allowed_upload_imgs_type', '允许的附件图片类型', '',getset('allowed_upload_imgs_type'))
    ->addRadio('up_type', '上传方式', '', ['local' => '本地', 'oss' => 'oss'],getset('up_type'))
    ->addText('oss_access_key', '阿里云Access Key ID', 'accessKeyId 从OSS获得的AccessKeyId',getset('oss_access_key'))
    ->addText('oss_key_secret', '阿里云Access Key Secret', 'accessKeySecret 从OSS获得的AccessKeySecret',getset('oss_key_secret'))
    ->addText('oss_endpoint', '阿里云数据中心访问域名', 'endpoint 您选定的OSS数据中心访问域名，例如oss-cn-hangzhou.aliyuncs.com',getset('oss_endpoint'))
    ->addText('oss_domain', '图片域名', 'eg:http://datebasejerry.oss-cn-shenzhen.aliyuncs.com||http://images.thinkask.com',getset('oss_domain'))
    ->addText('oss_bucket', '空间名称bucket', '',getset('oss_bucket'))
    // ->addText('oss_bucket_dir', 'bucket目录', '默认为一级目录',getset('oss_bucket_dir'))

    ->fetch();
  }
  public function openid(){
    return $this->builder('form')
    ->setUrl(url('admin/ajax/saveconfig'))
    ->setPageTitle('开放平台')
    ->addRadio('qq_login_enabled', '开启 QQ 登录功能', '', ['Y' => '是', 'N' => '否'],getset('qq_login_enabled'))
    ->addText('qq_login_app_key', 'QQ 登录 AppKey', '',getset('qq_login_app_key'))
    ->addRadio('sina_weibo_enabled', '开启微博登录功能', '', ['Y' => '是', 'N' => '否'],getset('sina_weibo_enabled'))
    ->addText('sina_akey', '微博 AppKey', 'AppKey 需要到 <a href="http://open.weibo.com" target="_blank">微博开放平台</a> 申请 (注意: 请申请网站不要申请应用)',getset('sina_akey'))
    ->addText('sina_skey', '微博 App Secret', '',getset('sina_skey'))
    ->addText('alipay_seller_email', '支付宝账号', '',getset('alipay_seller_email'))
    ->addText('alipay_partner', '支付宝合作身份者 ID', '',getset('alipay_partner'))
    ->addText('alipay_seller_id', '支付宝seller_id', '',getset('alipay_seller_id'))
    ->addText('alipay_key', '支付宝安全检验码key', '',getset('alipay_key'))
    ->addSwitch('cross_domain','跨域支付','',getset('cross_domain')?getset('cross_domain'):0)
    ->addText('baidu_ak','百度访问应用（AK）','',getset('baidu_ak')?getset('baidu_ak'):"")
    // ->addSwitch('cross_domain','跨域支付','',getset('cross_domain')?getset('cross_domain'):0)

    ->fetch();
  }
  public function cache(){
    return $this->builder('form')
    ->setUrl(url('admin/ajax/saveconfig'))
    ->setPageTitle('系统优化')
    ->addText('cache_level_high', '高级别缓存时间', '单位:秒',getset('cache_level_high'))
    ->addText('cache_level_normal', '普通级别缓存时间', '单位:秒',getset('cache_level_normal'))
    ->addText('cache_level_low', '低级别缓存时间', '单位:秒',getset('cache_level_low'))

    ->fetch();
  }
  public function template(){
    //风格
    $template = finddirfromdir(ROOT_PATH ."/template");
    return $this->builder('form')
    ->setUrl(url('admin/ajax/saveconfig'))
    ->setPageTitle('界面设置')
    ->addSelect('ui_style', '用户界面风格', '', $template,getset('ui_style'))
    ->addNumber('cache_level_high', '列表页文章显示条数', '',getset('cache_level_high'))
    ->addNumber('contents_per_question', '列表页问题显示条数', '',getset('contents_per_question'))
    ->addNumber('admin_per_all', '后台分页条数', '',getset('admin_per_all'))
    ->fetch();
  }
  /**
   * [spread 分成]
   * @Author   Jerry
   * @DateTime 2017-09-07T16:48:17+0800
   * @Example  eg:
   * @return   [type]                   [description]
   */
  public function spread(){
    $template = finddirfromdir(ROOT_PATH ."/template");
    return $this->builder('form')
    ->setUrl(url('admin/ajax/saveconfig'))
    ->setPageTitle('分成体系')
    ->addNumber('spread_express', '物流推广分成', '',getset('spread_express'))
    ->addNumber('spread_hotel', '酒店分成', '',getset('spread_hotel'))
    ->addNumber('spread_express_take', '物流取件分成', '',getset('spread_express_take'))
    ->addNumber('spread_express_give', '物流送件分成', '',getset('spread_express_give'))
    ->fetch();
  }
   /**
   * [spread 宿洗小哥端基本设置]
   * @Author   Jerry
   * @DateTime 2017-09-07T16:48:17+0800
   * @Example  eg:
   * @return   [type]                   [description]
   */
  public function sxwxopen(){
    $template = finddirfromdir(ROOT_PATH ."/template");
    return $this->builder('form')
    ->setUrl(url('admin/ajax/saveconfig'))
    ->setPageTitle('认证/提现')
    ->addText('authen_express_phone', '物流认证申请短信通知的的手机号', '多个号码以","号隔开，如:15220135353,15698588525',getset('authen_express_phone'))
    ->addText('authen_hotel_phone', '酒店认证申请短信通知的的手机号', '多个号码以","号隔开，如:15220135353,15698588525',getset('authen_hotel_phone'))
    ->addText('withdraw_express_phone', '物流端提现申请短信通知的手机号', '多个号码以","号隔开，如:15220135353,15698588525',getset('withdraw_express_phone'))
    ->addText('withdraw_hotel_phone', '酒店提现申请短信通知的手机号', '多个号码以","号隔开，如:15220135353,15698588525',getset('withdraw_hotel_phone'))
    ->addRadio('withdraw_success_sns','提现成功（短信通知）','',['Y' => '开启', 'N' => '关闭'],getset('withdraw_success_sns'))
    ->addRadio('withdraw_error_sns','提现失败（短信通知）','',['Y' => '开启', 'N' => '关闭'],getset('withdraw_error_sns'))
    ->addRadio('authen_success_sns','认证成功（短信通知）','',['Y' => '开启', 'N' => '关闭'],getset('authen_success_sns'))
    ->addRadio('authen_error_sns','认证失败（短信通知）','',['Y' => '开启', 'N' => '关闭'],getset('authen_error_sns'))
    ->addRadio('withdraw_success_sns_hotel','酒店提现成功（短信通知）','',['Y' => '开启', 'N' => '关闭'],getset('withdraw_success_sns_hotel'))
    ->addRadio('withdraw_error_sns_hotel','酒店提现失败（短信通知）','',['Y' => '开启', 'N' => '关闭'],getset('withdraw_error_sns_hotel'))
    ->addRadio('authen_success_sns_hotel','酒店认证成功（短信通知）','',['Y' => '开启', 'N' => '关闭'],getset('authen_success_sns_hotel'))
    ->addRadio('authen_error_sns_hotel','酒店认证失败（短信通知）','',['Y' => '开启', 'N' => '关闭'],getset('authen_error_sns_hotel'))
    ->addNumber('wash_step_time', '定时器，工序算法时间(分钟)', 'shell核算订单工序时所有的时间(如果为0不参与工序核算)',getset('wash_step_time'))
    ->addNumber('wash_warning_time', '定单报警时间(分钟)', '剩于时间（当前时间-下单时间<报警时间 开始报警）(如果为0不开启)',getset('wash_warning_time'))
    ->addNumber('wash_express_time', '物流配送总时长(分钟)', '超过此时间为超时 (如果为0不核算)',getset('wash_express_time'))
    ->fetch();
  }
  /**
   * [indexmail 官网邮件]
   * @Author   WuSong
   * @DateTime 2017-09-22T14:55:36+0800
   * @Example  eg:
   * @return   [type]                   [description]
   */
  public function indexmail(){
    return $this->builder('form')
    ->setUrl(url('admin/ajax/saveconfig'))
    ->setPageTitle('官网邮件设置')
    ->addText('receive_email', '指定接收邮箱', '',getset('receive_email'))
    ->fetch();
  }
  /**
   * [invoice_content 发票内容]
   * @Author   WuSong
   * @DateTime 2017-10-13T15:31:42+0800
   * @Example  eg:
   * @return   [type]                   [description]
   */
  public function invoice_content(){
    return $this->builder('form')
    ->setUrl(url('admin/ajax/saveconfig'))
    ->setPageTitle('留言内容设置')
    ->addText('invoice', '留言内容', '',getset('invoice'))
    ->fetch();
  }
 



}
