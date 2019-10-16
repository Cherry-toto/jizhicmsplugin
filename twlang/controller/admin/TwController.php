<?php

// +----------------------------------------------------------------------
// | JiZhiCMS { 极致CMS，给您极致的建站体验 }  
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2099 http://www.jizhicms.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 留恋风 <2581047041@qq.com>
// +----------------------------------------------------------------------
// | Date：2019/08
// +----------------------------------------------------------------------


namespace A\plugins;

use A\c\CommonController;
use FrPHP\lib\Controller;
use FrPHP\Extend\Page;
class TwController extends CommonController
{
	
	
	public function index(){
		$this->admin = $_SESSION['admin'];
		$desktop = M('Layout')->find(array('gid'=>$_SESSION['admin']['gid']));
		if(!$desktop){
			$desktop = M('Layout')->find(array('isdefault'=>1));
		}
		
		$this->left_layout = json_decode($desktop['left_layout'],true);
		$this->left_num = count($this->left_layout);
		$this->top_layout = json_decode($desktop['top_layout'],true);
		$rulers = M('Ruler')->findAll(array('isdesktop'=>1));
		$actions = array();
		foreach($rulers as $k=>$v){
			$actions[$v['id']] = $v;
		}
		
		$this->actions = $actions;

		$this->display('twlang/index');
		exit;
	}
	
	
	
	
	
}