<?php

// +----------------------------------------------------------------------
// | JiZhiCMS { 极致CMS，给您极致的建站体验 }  
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2099 http://www.jizhicms.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 留恋风 <2581047041@qq.com>
// +----------------------------------------------------------------------
// | Date：2019/11/24
// +----------------------------------------------------------------------


namespace Home\plugins;

use Home\c\CommonController;
use FrPHP\Extend\Page;
use FrPHP\Extend\ArrayPage;

class BaiduController extends CommonController
{

	//推送频率  推送所有文章/未推送过的
	// x 小时 x 天  x 月
	// 存储推送时间，推送内容ID--制作文章的推送
	function putweb(){

		$plugin = M('plugins')->find(['filepath'=>'baiduseo','isopen'=>1]);
		if($plugin){
			$config = json_decode($plugin['config'],true);
			$config['ids'] = isset($config['ids']) ? $config['ids'] : '';
			$config['updatetime'] = isset($config['updatetime']) ? $config['updatetime'] : 0;
			$puttime = (int)$config['puttime'];//数字
			$puttype = $config['puttype'];//时 天 月
			$lastupdate = $config['updatetime'];//默认0，插件安装的时候写，时间戳
			$nowtime = time();
			$isgo = false;
			switch ($puttype) {
				case '时':
					$time = $lastupdate + $puttime*60*60;
					if($nowtime > $time){
						$isgo = true;
					}

					break;
				case '天':
					$time = 86400*$puttime;
					if(($nowtime-$lastupdate)>=$time){
						$isgo = true;
					}
					break;
				default:
					//月-30天
					$time = 86400*$puttime*30;
					if(($nowtime-$lastupdate)>=$time){
						$isgo = true;
					}
					break;
			}
			$w = $config;
			if($isgo && ( (isset($config['baiduwebapi']) && $config['baiduwebapi']!='') ||  (isset($config['baiduxzapi']) && $config['baiduxzapi']!=''))){
				$sql_ids = $config['ids']=='' ? 0 : str_replace('|',',', $config['ids']);//1,2,3,4 
				$sql = " id not in (".$sql_ids.") and isshow=1 ";
				$articles = M('article')->findAll($sql);
				$urls = [];
				$ids = $config['ids']=='' ? [] : explode('|', $config['ids']);//1|2|3|4 存储推送过的文章ID
				//不能直接用$v['url']
				//loop  就能直接用 $v['url']  列表也可以这样写
				foreach ($articles as $key => $value) {
					$urls[]=gourl($value['id'],$value['htmlurl']);
					$ids[] = $value['id'];
					
				}
              
				if(count($urls)>0){
					//百度站长
					if(isset($config['baiduwebapi']) && $config['baiduwebapi']!=''){
							$api = htmlspecialchars_decode($config['baiduwebapi']);
							$ch = curl_init();
							$options =  array(
								CURLOPT_URL => $api,
								CURLOPT_POST => true,
								CURLOPT_RETURNTRANSFER => true,
								CURLOPT_POSTFIELDS => implode("\n", $urls),
								CURLOPT_HTTPHEADER => array('Content-Type: text/plain'),
							);
							curl_setopt_array($ch, $options);
							$result = curl_exec($ch);
							curl_close($ch);
                   	echo $result;
					}
                  
					if(isset($config['baiduxzapi']) && $config['baiduxzapi']!=''){
							$api = htmlspecialchars_decode($config['baiduxzapi']);
							$ch = curl_init();
							$options =  array(
								CURLOPT_URL => $api,
								CURLOPT_POST => true,
								CURLOPT_RETURNTRANSFER => true,
								CURLOPT_POSTFIELDS => implode("\n", $urls),
								CURLOPT_HTTPHEADER => array('Content-Type: text/plain'),
							);
							curl_setopt_array($ch, $options);
							$result = curl_exec($ch);
							curl_close($ch);
					}

					$ids_x = implode('|',$ids);//把数组用|分隔
					$w['updatetime'] = time();
					$w['ids'] = $ids_x;
                    $config = $w;
				    M('plugins')->update(['id'=>$plugin['id']],['config'=>json_encode($config,JSON_UNESCAPED_UNICODE)]);
				
					
				}

			}else{
				$w['updatetime'] = time();
               	$config = $w;
				M('plugins')->update(['id'=>$plugin['id']],['config'=>json_encode($config,JSON_UNESCAPED_UNICODE)]);
			}



			
			
		}


		
		
		
	}



}