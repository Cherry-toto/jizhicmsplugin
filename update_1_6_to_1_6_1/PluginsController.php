<?php

// +----------------------------------------------------------------------
// | JiZhiCMS { 极致CMS，给您极致的建站体验 }  
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2099 http://www.jizhicms.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 留恋风 <2581047041@qq.com>
// +----------------------------------------------------------------------
// | Date：2019/10/19
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
		
		//检测版本号
		if(!version_compare($this->webconf['web_version'],'1.6','==')){
			JsonReturn(['code'=>1,'msg'=>'您的软件系统版本为'.$this->webconf['web_version'].'，该插件仅支持1.6版本升级到1.6.1版本！']);
		}
		
		//更新系统版本
		M('sysconfig')->update(['field'=>'web_version'],['data'=>'1.6.1']);
		
		
		//新增字段
		//文章模块
		$sql = 'SHOW COLUMNS FROM '.DB_PREFIX.'article ';
		$list = M()->findSql($sql);
		$isgo_tags = true;
	
		foreach($list as $v){
			if($v['Field']=='tags'){
				$isgo_tags = false;
				
			}
			
		}
		if($isgo_tags){
			$sql = "ALTER TABLE ".DB_PREFIX."article ADD tags VARCHAR(255)  default NULL ";
			M()->runSql($sql);
		}
		
		
		
		
		//商品模块
		$sql = 'SHOW COLUMNS FROM '.DB_PREFIX.'product ';
		$list = M()->findSql($sql);
		$isgo_tags = true;
	
		foreach($list as $v){
			if($v['Field']=='tags'){
				$isgo_tags = false;
				
			}
			
		}
		if($isgo_tags){
			$sql = "ALTER TABLE ".DB_PREFIX."product ADD tags VARCHAR(255)  default NULL ";
			M()->runSql($sql);
		}
		//tags
		$sql = 'SHOW COLUMNS FROM '.DB_PREFIX.'tags ';
		$list = M()->findSql($sql);
		$isgo_number = true;
		foreach($list as $v){
			if($v['Field']=='number'){
				$isgo_number = false;
				
			}
			
		}
		if($isgo_number){
			$sql = "ALTER TABLE ".DB_PREFIX."tags ADD number Int(11)  default '0' ";
			M()->runSql($sql);
		}
		
		//插入一条新增字段
		if(!M('fields')->find(['field'=>'number','molds'=>'tags'])){
			M('fields')->add(['field'=>'number','molds'=>'tags','fieldname'=>'标签数','tips'=>'无需填写，程序自动处理','fieldtype'=>4,'tids'=>',1,2,3,','fieldlong'=>'255','isshow'=>1,'islist'=>1,'orders'=>0,'vdata'=>'0']);
		}
		
		//更换文件
		//后台模板
		$dir = APP_PATH.'A/exts/update_1_6_to_1_6_1/file/tpl';
		$dh =  opendir($dir);
		while (($file= readdir($dh)) !== false){
			if( $file!="." && $file!=".."){
			 copy($dir."/".$file,APP_PATH.'A/t/tpl/'.$file);
		    }
		}
		closedir($dh);
		
		if(!is_dir(APP_PATH.'A/t/tpl/style/tags')){
			mkdir(APP_PATH.'A/t/tpl/style/tags',0777);
		}
		$dir = APP_PATH.'A/exts/update_1_6_to_1_6_1/file/tags';
		$dh =  opendir($dir);
		while (($file= readdir($dh)) !== false){
			if( $file!="." && $file!=".."){
			 copy($dir."/".$file,APP_PATH.'A/t/tpl/style/tags/'.$file);
		    }
		}
		closedir($dh);
		
		//后台控制器
		$dir = APP_PATH.'A/exts/update_1_6_to_1_6_1/file/admin';
		$dh =  opendir($dir);
		while (($file= readdir($dh)) !== false){
			if( $file!="." && $file!=".."){
			 copy($dir."/".$file,APP_PATH.'A/c/'.$file);
		    }
		}
		closedir($dh);
		//前台控制器
		$dir = APP_PATH.'A/exts/update_1_6_to_1_6_1/file/home';
		$dh =  opendir($dir);
		while (($file= readdir($dh)) !== false){
			if( $file!="." && $file!=".."){
			 copy($dir."/".$file,APP_PATH.'Home/c/'.$file);
		    }
		}
		closedir($dh);
		
		//更新配置
		setCache('customconfig',null);
	    setCache('classtype',null);
	    setCache('mobileclasstype',null);
		
		
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
		
		M('plugins')->update(['id'=>$data['id']],['config'=>json_encode($data,JSON_UNESCAPED_UNICODE)]);
		setCache('hook',null);//清空hook缓存
		JsonReturn(['code'=>0,'msg'=>'设置成功！']);
		
	}
	
	
}




