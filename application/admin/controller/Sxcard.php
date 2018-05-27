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

class Sxcard extends AdminBase {

public function _initialize() {
    parent::_initialize();
  }
  /**
   * [setting 宿卡设置 ]
   * @Author   Jerry
   * @DateTime 2017-11-01T16:06:09+0800
   * @Example  eg:
   * @return   [type]                   [description]
   */
	public function setting(){
		##所有商品
		$goods = $this->getbase->getall('goods',['where'=>['status'=>1],'field'=>'id,name']);
		 return $this->builder('form')
	    ->setUrl(url('admin/ajax/saveconfig'))
	    ->setPageTitle('宿卡配置')
	    ->addNumber('sx_card_max', '参与衣服的最大件数', '每次可以洗的最大件数，0为不限制',getset('sx_card_max'))
	    ->addNumber('can_user_max', '可以使用的次数', '',getset('can_user_max'))
	    ->addNumber('sx_card_price', '宿卡价格', '',getset('sx_card_price'))
	    ->addNumber('sx_card_price_love', '宿卡价格(可赠送)', '',getset('sx_card_price_love'))
	    ->addCheckbox('sx_card_goods_choose', '参与此活动的商品', '', formatArr($goods,'id','name'),getset('sx_card_goods_choose'))
	    ->fetch();
	}
	/**
	 * [firstfree 首件免单]
	 * @Author   Jerry
	 * @DateTime 2017-11-01T16:06:01+0800
	 * @Example  eg:
	 * @return   [type]                   [description]
	 */
	public function firstfree(){
		##所有商品
		$goods = $this->getbase->getall('goods',['where'=>['status'=>1],'field'=>'id,name']);
		 return $this->builder('form')
	    ->setUrl(url('admin/ajax/saveconfig'))
	    ->setPageTitle('首件免单配置')
	    ->setPageTips('此配置，不可以在正常工作期间更改，会直接影响用户的支付金额','danger')
	    ->addRadio('isopen_first_free', '是否开启首件免单', '', ['关闭','开启'], getset('isopen_first_free'))
	    ->addRadio('first_free_kind_type', '免费种类', '', ['expensive' => '免最贵', ' cheap' => '免便宜'], getset('first_free_kind_type'))
	    ->addCheckbox('first_free_goods_choose', '参与此活动的商品', '', formatArr($goods,'id','name'),getset('first_free_goods_choose'))
	    ->fetch();
	}

}