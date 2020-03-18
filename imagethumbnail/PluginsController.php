<?php

// +----------------------------------------------------------------------
// | JiZhiCMS { 极致CMS，给您极致的建站体验 }  
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2099 http://www.jizhicms.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 留恋风 <2581047041@qq.com>
// +----------------------------------------------------------------------
// | Date：2019/10/27
// +----------------------------------------------------------------------

namespace A\exts;

use FrPHP\lib\Controller;
use FrPHP\Extend\Page;
class PluginsController extends Controller {
	
	
	//自动执行
	public function _init(){
		/**
			继承系统默认配置
		
		**/
		
		//检查当前账户是否合乎操作
		if(!isset($_SESSION['admin']) || $_SESSION['admin']['id']==0){
			Redirect(U('Login/index'));
			
		}
 
	    if($_SESSION['admin']['isadmin']!=1){
			if(strpos($_SESSION['admin']['paction'],','.APP_CONTROLLER.',')!==false){
			   
			}else{
				$action = APP_CONTROLLER.'/'.APP_ACTION;
				if(strpos($_SESSION['admin']['paction'],','.$action.',')===false){
				   $ac = M('Ruler')->find(array('fc'=>$action));
				   if($this->frparam('ajax')){
					   
					   JsonReturn(['code'=>1,'msg'=>'您没有【'.$ac['name'].'】的权限！','url'=>U('Index/index')]);
				   }
				   Error('您没有【'.$ac['name'].'】的权限！',U('Index/index'));
				}
			}
		   
		  
		}
	  
	    $webconf = webConf();
	    $this->webconf = $webconf;
	    $customconf = get_custom();
	    $this->customconf = $customconf;
		
		//插件模板页目录
		
		$this->tpl = '@'.dirname(__FILE__).'/tpl/';
		
		/**
			在下面添加自定义操作
		**/
		
		
	}
	
	//执行SQL语句在此处处理,或者移动文件也可以在此处理
	public  function install(){
		//下面是新增test表的SQL操作
		//检测是否已安装前台插件
		$filepath = APP_PATH.'A/plugins/ImageController.php';
	
		
		//注册到hook里面
		$w['module'] = 'A';
		$w['namespace'] = 'A';
		$w['controller'] = 'Common';
		$w['action'] = 'uploads';
		$w['all_action'] = 1;
		$w['hook_namespace'] = 'A';
		$w['hook_controller'] = 'Image';
		$w['hook_action'] = 'uploads';
		$w['plugins_name'] = 'imagethumbnail';//插件文件夹
		$w['addtime'] = time();
		M('hook')->add($w);
		
		
		return true;
		
	}
	
	//卸载程序,对新增字段、表等进行删除SQL操作，或者其他操作
	public function uninstall(){
		//下面是删除test表的SQL操作
		M('hook')->delete(['plugins_name'=>'imagethumbnail']);
		return true;
	}
	
	//安装页面介绍,操作说明
	public function desc(){
		
		$this->display($this->tpl.'plugins-description.html');
	}
	
	//配置文件,插件相关账号密码等操作
	public  function setconf($plugins){
		//将插件赋值到模板中
		$this->plugins = $plugins;
		$this->config = json_decode($plugins['config'],1);
		$this->classtypetree = get_classtype_tree();
		$this->display($this->tpl.'plugins-body.html');
	}
	//获取插件内提交的数据处理
	public function setconfigdata($data){
		$data['default_open'] = format_param($data['default_open']);
		$data['default_rate_x'] = format_param($data['default_rate_x']);
		$data['default_rate_y'] = format_param($data['default_rate_y']);
		$data['default_value_x'] = format_param($data['default_value_x']);
		$data['default_value_y'] = format_param($data['default_value_y']);
		$data['small_open'] = format_param($data['small_open']);
		$data['small_rate_x'] = format_param($data['small_rate_x']);
		$data['small_rate_y'] = format_param($data['small_rate_y']);
		$data['small_value_x'] = format_param($data['small_value_x']);
		$data['small_value_y'] = format_param($data['small_value_y']);
		$data['large_open'] = format_param($data['large_open']);
		$data['large_rate_x'] = format_param($data['large_rate_x']);
		$data['large_rate_y'] = format_param($data['large_rate_y']);
		$data['large_value_x'] = format_param($data['large_value_x']);
		$data['large_value_y'] = format_param($data['large_value_y']);
		$data['gif_open'] = format_param($data['gif_open']);
		$data['tids_1'] = (format_param($data['tids_1'],2) && count(format_param($data['tids_1'],2))>0)?','.implode(',',format_param($data['tids_1'],2)).',':'';
		$data['tids_2'] = (format_param($data['tids_2'],2) && count(format_param($data['tids_2'],2))>0)?','.implode(',',format_param($data['tids_2'],2)).',':'';
		$data['tids_3'] = (format_param($data['tids_3'],2) && count(format_param($data['tids_3'],2))>0)?','.implode(',',format_param($data['tids_3'],2)).',':'';
		if($data['default_open']==1){
			if( ($data['default_rate_x']==0 || $data['default_rate_y']==0) && ($data['default_value_x']==0 || $data['default_value_y']==0)){
				JsonReturn(['code'=>1,'msg'=>'中等图设置失败！']);
			}
		}
		if($data['small_open']==1){
			if( ($data['small_rate_x']==0 || $data['small_rate_y']==0) && ($data['small_value_x']==0 || $data['small_value_y']==0)){
				JsonReturn(['code'=>1,'msg'=>'小图设置失败！']);
			}
		}
		if($data['large_open']==1){
			if( ($data['large_rate_x']==0 || $data['large_rate_y']==0) && ($data['large_value_x']==0 || $data['large_value_y']==0)){
				JsonReturn(['code'=>1,'msg'=>'大图设置失败！']);
			}
		}
		
		M('plugins')->update(['id'=>$data['id']],['config'=>json_encode($data,JSON_UNESCAPED_UNICODE)]);
		setCache('hook',null);//清空hook缓存
		JsonReturn(['code'=>0,'msg'=>'设置成功！']);
		
	}
	
	
}




