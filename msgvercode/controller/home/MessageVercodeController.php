<?php

// +----------------------------------------------------------------------
// | JiZhiCMS { 极致CMS，给您极致的建站体验 }  
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2099 http://www.jizhicms.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 留恋风 <2581047041@qq.com>
// +----------------------------------------------------------------------
// | Date：2019/09/06
// +----------------------------------------------------------------------


namespace Home\plugins;

use Home\c\CommonController;
use FrPHP\lib\Controller;
use FrPHP\Extend\Page;

class MessageVercodeController extends CommonController
{
	

	function checkvercode(){
		
		if($_POST){
			if(md5(md5($this->frparam('vercode',1)))!=$_SESSION['frcode']){
				if($this->frparam('ajax')){
					$xdata = array('code'=>1,'msg'=>'验证码错误！');
					JsonReturn($xdata);
				}
				Error('验证码错误！');
			}
			
			
		}
		
	}
	
}