<?php

// +----------------------------------------------------------------------
// | JiZhiCMS { 极致CMS，给您极致的建站体验 }  
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2099 http://www.jizhicms.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 留恋风 <2581047041@qq.com>
// +----------------------------------------------------------------------
// | Date：2020/02/16
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
		//检测版本号
		if(!version_compare($this->webconf['web_version'],'1.6.6','==')){
			JsonReturn(['code'=>1,'msg'=>'您的软件系统版本为'.$this->webconf['web_version'].'，该插件仅支持1.6.6版本修复！']);
		}

		
		//公共文件安装
		$dir = APP_PATH.'A/exts/jizhicmsupdate/file';

		copy($dir."/PluginsController.php",APP_PATH.'A/c/PluginsController.php');

		//检查文件名是否存在
		if(file_exists(APP_PATH.'install')){
			copy($dir."/db.php",APP_PATH.'install/db.php');
			copy($dir."/test.php",APP_PATH.'install/test.php');
		}
		if(defined('DB_TYPE') && DB_TYPE=='sqlite'){
			if(file_exists(APP_PATH.'install')){
				copy($dir."/db.db",APP_PATH.'install/db.db');
				copy($dir."/test.db",APP_PATH.'install/test.db');
			}
			//新增字段
			$sqlx = "pragma table_info(".DB_PREFIX."buylog)";
			$list = M()->findSql($sqlx);
			$isgo = true;
			foreach($list as $v){
				if($v['name']=='aid'){
					$isgo = false;
					
				}
			}
			$sql = '';
			if($isgo){
				//新增字段
				$sql = "ALTER TABLE ".DB_PREFIX."buylog ADD aid INT(11)  DEFAULT '0' ;";
				M()->runSql($sql);
			}
			
			
			
		}else{
			
			$sql = 'SHOW COLUMNS FROM '.DB_PREFIX.'buylog ';
			$list = M()->findSql($sql);
			$isgo = true;
		
			foreach($list as $v){
				if($v['Field']=='aid'){
					$isgo = false;
					
				}
				
			}
			if($isgo){
				$sql = "ALTER TABLE ".DB_PREFIX."buylog ADD aid INT(11)  DEFAULT '0' ";
				M()->runSql($sql);
			}
			
		}
		
	
		$plugin = M('plugins')->find(['filepath'=>'jizhicmsupdate']);
		if($plugin){
			M('plugins')->update(['id'=>$plugin['id']],['version'=>'2.3','addtime'=>strtotime('2020-03-12')]);
		}
		

	
		return true;
		
	}
	
	//卸载程序,对新增字段、表等进行删除SQL操作，或者其他操作
	public function uninstall(){
		//下面是删除test表的SQL操作
		
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
		
		$this->display($this->tpl.'plugins-body.html');
	}
	
	//获取插件内提交的数据处理
	public function setconfigdata($data){
		//ids
		
		$config = $data;

		M('plugins')->update(['id'=>$data['id']],['config'=>json_encode($config,JSON_UNESCAPED_UNICODE)]);
		setCache('hook',null);//清空hook缓存
		JsonReturn(['code'=>0,'msg'=>'设置成功！']);
	}
	
}




