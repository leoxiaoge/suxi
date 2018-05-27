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

class Xcx extends AdminBase {

	public function _initialize() {
    parent::_initialize();
  }

		public function index(){
			//获取token GET
			$url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wxeefecafcd02450af&secret=117d8ae7a22a417380c1a4af7fe906c4";
			$data_token = json_decode(curl_get_contents($url),true);

			//概况趋势
			$summarytrend = $this->summarytrend($data_token);
			$this->assign('summarytrend',$summarytrend);
			
			//访问趋势
			$lyvisittrend = $this->lyvisittrend($data_token);
			$this->assign('lyvisittrend',$lyvisittrend);

			//访问留存(周)
			$weeklyretaininfo = $this->weeklyretaininfo($data_token);
			$this->assign('weeklyretaininfo',$weeklyretaininfo);

			//访问页面 
			$visitpage = $this->visitpage($data_token);
			$this->assign('visitpage',$visitpage);

			return $this->fetch('admin/xcx/index');

		}
		/**
		 * [index //概况趋势 POST]
		 * @Author   WuSong
		 * @DateTime 2017-10-18T14:59:06+0800
		 * @Example  eg:
		 * @return   [type]                   [description]
		 */
		private function summarytrend($data_token){
				//##先查询，查询完加缓存
				if(!cache(date('ymd').'summarytrend')){
					$url = "https://api.weixin.qq.com/datacube/getweanalysisappiddailysummarytrend?access_token=".$data_token['access_token'];
					$time = [];
					for ($i=1; $i < 8; $i++) { 
						$time[] = date('Ymd',strtotime('-'.$i.' days'));
					}
					foreach ($time as $v) {
						$param = "{".'"begin_date"'.":".$v.",".'"end_date"'.":".$v."}";
						$re= json_decode(curl_post_contents($url, $param),true);
						$data_survey[] = $re['list'][0];
					}
					$nowtime = date('ymd');
					if($data_survey) cache($nowtime.'summarytrend',$data_survey);
				}
				$data = cache(date('ymd').'summarytrend');
			
				return $data;
		}

		/**
		 * [lyvisittrend 访问趋势 POST]
		 * @Author   WuSong
		 * @DateTime 2017-10-18T15:37:25+0800
		 * @Example  eg:
		 * @return   [type]                   [description]
		 */
		private function lyvisittrend($data_token){
				//##先查询，查询完加缓存
				if(!cache(date('ymd').'lyvisittrend')){
					$url = "https://api.weixin.qq.com/datacube/getweanalysisappiddailyvisittrend?access_token=".$data_token['access_token'];
					$time= [];
					for($i=1; $i < 8;$i++ ){
						$time[] = date('Ymd',strtotime('-'.$i.'days'));
					}
					foreach ($time as $v) {
						$param = "{".'"begin_date"'.":".$v.",".'"end_date"'.":".$v."}";
						$re= json_decode(curl_post_contents($url, $param),true);
						$data_visit[] = $re['list'][0];
					}
					$nowtime = date('ydm');
					if($data_visit) cache($nowtime.'lyvisittrend',$data_visit);
				}
				$data=cache(date('ydm').'lyvisittrend');
				return $data;
		}

		/**
		 * [weeklyretaininfo 访问留存(周) POST ]
		 * @Author   WuSong
		 * @DateTime 2017-10-18T16:41:56+0800
		 * @Example  eg:
		 * @return   [type]                   [description]
		 */
		private function weeklyretaininfo($data_token){
			//##先查询，查询完加缓存
			if(!cache(date('ymd').'weeklyretaininfo')){
				$url = "https://api.weixin.qq.com/datacube/getweanalysisappidweeklyretaininfo?access_token=".$data_token['access_token'];

					$param = '{"begin_date" : "20170911","end_date" : "20170917"}';
					$re= json_decode(curl_post_contents($url, $param),true);
					$data_visit[] = $re;
				}
				$nowtime = date('ydm');
				if($data_visit) cache($nowtime.'weeklyretaininfo',$data_visit);
			$data=cache(date('ydm').'weeklyretaininfo');
			return $data;
		}

		

		/**
		 * [visitpage 访问页面 POST]
		 * @Author   WuSong
		 * @DateTime 2017-10-18T17:50:44+0800
		 * @Example  eg:
		 * @return   [type]                   [description]
		 */
		public function visitpage($data_token){
			if(!cache(date('ymd').'visitpage')){
				$url = "https://api.weixin.qq.com/datacube/getweanalysisappidvisitpage?access_token=".$data_token['access_token'];
				$time= [];
				for($i=1; $i < 8;$i++ ){
					$time[] = date('Ymd',strtotime('-'.$i.'days'));
				}
				foreach ($time as $v) {
					$param = "{".'"begin_date"'.":".$v.",".'"end_date"'.":".$v."}";
					$re= json_decode(curl_post_contents($url, $param),true);
					$data_visit[] = $re;
				}
				$nowtime = date('ydm');
				if($data_visit) cache($nowtime.'visitpage',$data_visit);
			}
			$data=cache(date('ydm').'visitpage');
			return $data;
		}
}