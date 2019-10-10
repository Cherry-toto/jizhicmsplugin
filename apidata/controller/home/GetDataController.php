<?php

// +----------------------------------------------------------------------
// | JiZhiCMS { 极致CMS，给您极致的建站体验 }  
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2099 http://www.jizhicms.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 留恋风 <2581047041@qq.com>
// +----------------------------------------------------------------------
// | Date：2019/10/08
// +----------------------------------------------------------------------


namespace Home\plugins;

use Home\c\CommonController;
use FrPHP\lib\Controller;
use FrPHP\Extend\Page;

class GetDataController extends CommonController
{
	function _init(){
		parent::_init();
		$apidata = M('plugins')->find(['filepath'=>'apidata','isopen'=>1]);
		if(!$apidata){
			JsonReturn(['code'=>1,'msg'=>'插件未开启！']);
		}
		$this->config = json_decode($apidata['config'],1);
		
		$this->checkIp();
		$this->checkKey();
	}
	
	//获取数据处理
	/**
	
		model: 模块名，必填
		key:访问秘钥，必填
		where:查询条件，默认查询该模块所有内容，json格式{'tid':1}，可以不填
		orders:查询排序，默认id倒序，字符串格式： 'addtime desc' ，可以不填
		limit:显示条数，默认查询所有，数字，正整数，可以不填
		fields:获取字段，默认查询所有，字符串格式： 'id,classname,tid' ，可以不填
	
	**/
	function index(){
		
		$tables = explode(',',$this->config['tables']);
		$table = $this->frparam('model',1);
		if(is_array($tables) && in_array($table,$tables)){
			$model = $this->frparam('model',1,false);
			$where = $this->frparam('where',1,null);
			$orders = $this->frparam('orders',1,' id desc ');
			$limit = $this->frparam('limit',0,null);
			$fields = $this->frparam('fields',1,null);
			if(!$model){
				JsonReturn(['code'=>1,'msg'=>'model参数错误！']);
			}
			if($where!==null){
				$where = json_decode($where,1);
			}
		
			$res = M($model)->findAll($where,$orders,$fields,$limit);
			JsonReturn(['code'=>0,'data'=>$res]);
			
		}else{
			JsonReturn(['code'=>1,'msg'=>'数据不存在！']);
		}
		
	}
	
	//IP审核
	function checkIp(){
		$ip = GetIP();//获取IP
		if($this->config['ischeckip']==1){
			$iplist = explode('||',$this->config['iplist']);
			if(!is_array($iplist) || !in_array($ip,$iplist)){
				JsonReturn(['code'=>1,'msg'=>'IP不在白名单！']);
			}
		}
		
	}
	//秘钥审核
	function checkKey(){
		$key = $this->config['key'];
		$current_key = $this->frparam('key',1);
		if($key!=$current_key){
			JsonReturn(['code'=>1,'msg'=>'秘钥错误！']);
		}
	}
	
	
}