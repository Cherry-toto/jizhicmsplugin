<?php

// +----------------------------------------------------------------------
// | JiZhiCMS { 极致CMS，给您极致的建站体验 }  
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2099 http://www.jizhicms.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 留恋风 <2581047041@qq.com>
// +----------------------------------------------------------------------
// | Date：2019/09/21
// +----------------------------------------------------------------------


namespace Home\plugins;

use Home\c\CommonController;
use FrPHP\lib\Controller;
use FrPHP\Extend\Page;

class MessageSafeController extends CommonController
{
	

	function checksafe(){
		
		if($_POST){
			$w['filepath'] = 'msgsafe';
			$w['isopen'] = 1;
			$res = M('plugins')->find($w);
			if($res){
				$config = json_decode($res['config'],1);
				$ipnumber = (int)$config['ipnumber'];
				$telnumber = (int)$config['telnumber'];
				if($ipnumber==0 || $telnumber==0){
					if($this->frparam('ajax')){
						JsonReturn(['code'=>1,'msg'=>$config['msg']]);
					}
					Error($config['msg']);
				}else{
					
					$start = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
					$end = mktime(23, 59, 59, date('m'), date('d'), date('Y'));

					
					
					$tel = $this->frparam('tel',1);
					$sql = "addtime>=".$start." and addtime<=".$end." and tel='".$tel."'";
					$count = M('message')->getCount($sql);
					if($count>$telnumber){
						if($this->frparam('ajax')){
							JsonReturn(['code'=>1,'msg'=>$config['msg']]);
						}
						Error($config['msg']);
					}
					$ip = GetIP();
					$sql = "addtime>=".$start." and addtime<=".$end." and ip='".$ip."'";
					$count = M('message')->getCount($sql);
					if($count>$ipnumber){
						if($this->frparam('ajax')){
							JsonReturn(['code'=>1,'msg'=>$config['msg']]);
						}
						Error($config['msg']);
					}
					
				}
				
				
			}
			
			
		}
		
	}
	
	
	
}