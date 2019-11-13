<?php

// +----------------------------------------------------------------------
// | JiZhiCMS { 极致CMS，给您极致的建站体验 }  
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2099 http://www.jizhicms.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 留恋风 <2581047041@qq.com>
// +----------------------------------------------------------------------
// | Date：2019/11/13
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
		if(!version_compare($this->webconf['web_version'],'1.6.1','==')){
			JsonReturn(['code'=>1,'msg'=>'您的软件系统版本为'.$this->webconf['web_version'].'，该插件仅支持1.6.1版本升级到1.6.2版本！']);
		}
		
		//更新系统版本
		M('sysconfig')->update(['field'=>'web_version'],['data'=>'1.6.2']);
		
		
		//新增字段
		//文章模块
		$sql = 'SHOW COLUMNS FROM '.DB_PREFIX.'article ';
		$list = M()->findSql($sql);
		$isgo_member_id = true;
	
		foreach($list as $v){
			if($v['Field']=='member_id'){
				$isgo_member_id = false;
				
			}
			
		}
		if($isgo_member_id){
			$sql = "ALTER TABLE ".DB_PREFIX."article ADD member_id int(11) NOT NULL DEFAULT '0' ";
			M()->runSql($sql);
		}
		

		//商品模块
		$sql = 'SHOW COLUMNS FROM '.DB_PREFIX.'product ';
		$list = M()->findSql($sql);
		$isgo_member_id = true;
	
		foreach($list as $v){
			if($v['Field']=='member_id'){
				$isgo_member_id = false;
				
			}
			
		}
		if($isgo_member_id){
			$sql = "ALTER TABLE ".DB_PREFIX."product ADD member_id int(11) NOT NULL DEFAULT '0' ";
			M()->runSql($sql);
		}
		
		//插入新增字段
		if(!M('fields')->find(['field'=>'member_id','molds'=>'article'])){
			M('fields')->add(['field'=>'member_id','molds'=>'article','fieldname'=>'发布用户','tips'=>'前台会员，无需填写','fieldtype'=>13,'tids'=>',1,6,7,8,9,2,10,11,12,13,3,4,5,14,','body'=>'3,username','fieldlong'=>'11','isshow'=>1,'islist'=>0,'orders'=>0,'vdata'=>'0']);
		}
		if(!M('fields')->find(['field'=>'member_id','molds'=>'product'])){
			M('fields')->add(['field'=>'member_id','molds'=>'product','fieldname'=>'发布用户','tips'=>'前台会员，无需填写','fieldtype'=>13,'tids'=>',1,6,7,8,9,2,10,11,12,13,3,4,5,14,','body'=>'3,username','fieldlong'=>'11','isshow'=>1,'islist'=>0,'orders'=>0,'vdata'=>'0']);
		}
		
		//更换文件
		//后台模板
		$dir = APP_PATH.'A/exts/update_1_6_1_to_1_6_2/file/admintpl';
		$dh =  opendir($dir);
		while (($file= readdir($dh)) !== false){
			if( $file!="." && $file!=".."){
			 copy($dir."/".$file,APP_PATH.'A/t/tpl/'.$file);
		    }
		}
		closedir($dh);
		//后台控制器
		$dir = APP_PATH.'A/exts/update_1_6_1_to_1_6_2/file/admin';
		$dh =  opendir($dir);
		while (($file= readdir($dh)) !== false){
			if( $file!="." && $file!=".."){
			 copy($dir."/".$file,APP_PATH.'A/c/'.$file);
		    }
		}
		closedir($dh);
		//后台插件webhtml更新
		//检查是否存在文件夹
		if(file_exists(APP_PATH.'A/exts/webhtml')){
			$dir = APP_PATH.'A/exts/update_1_6_1_to_1_6_2/file/webhtml';
			copy($dir."/webhtml.html",APP_PATH.'A/exts/webhtml/file/webhtml.html');
			copy($dir."/WebHtmlController.php",APP_PATH.'A/exts/webhtml/controller/admin/WebHtmlController.php');
		}
		//前台home控制器
		$dir = APP_PATH.'A/exts/update_1_6_1_to_1_6_2/file/home';
		$dh =  opendir($dir);
		while (($file= readdir($dh)) !== false){
			if( $file!="." && $file!=".."){
			 copy($dir."/".$file,APP_PATH.'Home/c/'.$file);
		    }
		}
		closedir($dh);
		//公共方法
		$dirhtml = APP_PATH.'A/exts/update_1_6_1_to_1_6_2/file/conf/Functions.php';
		copy($dirhtml,APP_PATH.'Conf/Functions.php');
		//FrPHP框架
		$dir = APP_PATH.'A/exts/update_1_6_1_to_1_6_2/file/frphp';
		copy($dir."/DatabaseTool.php",APP_PATH.'FrPHP/Extend/DatabaseTool.php');
		copy($dir."/FrSession.php",APP_PATH.'FrPHP/Extend/FrSession.php');
		copy($dir."/Functions.php",APP_PATH.'FrPHP/common/Functions.php');
		copy($dir."/Config.php",APP_PATH.'FrPHP/common/Config.php');
		copy($dir."/DBholder.php",APP_PATH.'FrPHP/db/DBholder.php');
		
		//更新配置
		setCache('webconfig',null);
		setCache('customconfig',null);
	    setCache('classtype',null);
	    setCache('mobileclasstype',null);
	    setCache('classtypetree',null);
		
		
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
		
		JsonReturn(['code'=>0,'msg'=>'设置成功！']);
		
	}
	
	
}




