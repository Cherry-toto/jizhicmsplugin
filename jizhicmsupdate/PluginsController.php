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
			JsonReturn(['code'=>1,'msg'=>'您的软件系统版本为'.$this->webconf['web_version'].'，该插件仅支持1.6.6版本升级！']);
		}
		//公共文件安装
		$dir = APP_PATH.'A/exts/jizhicmsupdate/file';
		//检查文件名是否存在
		if(file_exists(APP_PATH.'install')){
			copy($dir."/install/db.php",APP_PATH.'install/db.php');
			copy($dir."/install/test.php",APP_PATH.'install/test.php');
		}
		
		copy($dir."/tpl/comment-details.html",APP_PATH.'A/t/tpl/comment-details.html');
		copy($dir."/tpl/membergroup-add.html",APP_PATH.'A/t/tpl/membergroup-add.html');
		copy($dir."/tpl/plugins-list.html",APP_PATH.'A/t/tpl/plugins-list.html');
		copy($dir."/tpl/sys.html",APP_PATH.'A/t/tpl/sys.html');
		copy($dir."/tpl/classtype-edit.html",APP_PATH.'A/t/tpl/classtype-edit.html');
		copy($dir."/tpl/classtype-add.html",APP_PATH.'A/t/tpl/classtype-add.html');
		
		
		copy($dir."/PluginsController.php",APP_PATH.'A/c/PluginsController.php');
		copy($dir."/ClasstypeController.php",APP_PATH.'A/c/ClasstypeController.php');
		copy($dir."/ArrayPage.php",APP_PATH.'FrPHP/Extend/ArrayPage.php');
		copy($dir."/Page.php",APP_PATH.'FrPHP/Extend/Page.php');
		
		
		if(defined('DB_TYPE') && DB_TYPE=='sqlite'){
			//sqlite
			if(file_exists(APP_PATH.'install')){
				copy($dir."/install/db.db",APP_PATH.'install/db.db');
				copy($dir."/install/test.db",APP_PATH.'install/test.db');
			}
			copy($dir."/sqlite/admin/CommonController.php",APP_PATH.'A/c/CommonController.php');
			copy($dir."/sqlite/admin/IndexController.php",APP_PATH.'A/c/IndexController.php');
			copy($dir."/sqlite/admin/PluginsController.php",APP_PATH.'A/c/PluginsController.php');
			copy($dir."/sqlite/admin/SysController.php",APP_PATH.'A/c/SysController.php');
			copy($dir."/sqlite/admin/FieldsController.php",APP_PATH.'A/c/FieldsController.php');
			
			copy($dir."/sqlite/home/CommonController.php",APP_PATH.'Home/c/CommonController.php');
			copy($dir."/sqlite/home/ErrorController.php",APP_PATH.'Home/c/ErrorController.php');
			copy($dir."/sqlite/home/HomeController.php",APP_PATH.'Home/c/HomeController.php');
			copy($dir."/sqlite/home/LoginController.php",APP_PATH.'Home/c/LoginController.php');
			copy($dir."/sqlite/home/UserController.php",APP_PATH.'Home/c/UserController.php');
			
			copy($dir."/sqlite/Fr.php",APP_PATH.'FrPHP/Fr.php');
			
			
		}else{
			//mysql
			copy($dir."/admin/CommonController.php",APP_PATH.'A/c/CommonController.php');
			copy($dir."/admin/IndexController.php",APP_PATH.'A/c/IndexController.php');
			copy($dir."/admin/PluginsController.php",APP_PATH.'A/c/PluginsController.php');
			copy($dir."/admin/SysController.php",APP_PATH.'A/c/SysController.php');
			
			
			copy($dir."/home/CommonController.php",APP_PATH.'Home/c/CommonController.php');
			copy($dir."/home/ErrorController.php",APP_PATH.'Home/c/ErrorController.php');
			copy($dir."/home/HomeController.php",APP_PATH.'Home/c/HomeController.php');
			copy($dir."/home/LoginController.php",APP_PATH.'Home/c/LoginController.php');
			copy($dir."/home/UserController.php",APP_PATH.'Home/c/UserController.php');
			
			copy($dir."/mysql/Fr.php",APP_PATH.'FrPHP/Fr.php');
			
			
			
		}

		$handle = opendir(APP_PATH);
		$admin_url = '';
		while ( false !== ($file = readdir ( $handle )) ) {
			//去掉"“.”、“..”以及带“.xxx”后缀的文件
			if ($file != "." && $file != ".."&&strpos($file,".")) {
				
				if(strpos($file,'.php')!==false && $file!='index.php'){
					$data = file_get_contents(APP_PATH.$file);
					if(strpos($data,"define('APP_HOME','A')")!==false){
						$admin_url = $file;
						break;
					}
					
				}
				
			}
		}
		//关闭句柄
		closedir ( $handle );
		if($admin_url!=''){
			$data = file_get_contents(APP_PATH.$admin_url);
			$datas = preg_replace("/define\('ROOT',(.*?)\);/","",$data);
			$datas = str_replace("define('Tpl_style',ROOT.'A/t/tpl');","define('Tpl_style','/A/t/tpl');",$datas);
			file_put_contents(APP_PATH.$admin_url,$datas);
			
			
		}
		
		//获取前台template
		$indexdata = file_get_contents(APP_PATH.'index.php');
		$r = preg_match("/define\('HOME_VIEW','([^']+)'\)/",$indexdata,$matches);
		if($r){
			$template = $matches[1];
			$tp = $this->webconf['pc_template'];
			if(file_exists(APP_PATH.'Home/'.$template.'/'.$tp.'/user/style.html')){
				copy($dir."/style.html",APP_PATH.'Home/'.$template.'/'.$tp.'/user/style.html');
				copy($dir."/notify.html",APP_PATH.'Home/'.$template.'/'.$tp.'/user/notify.html');
				copy($dir."/people.html",APP_PATH.'Home/'.$template.'/'.$tp.'/user/people.html');
			}
			
		}
		//检查权限是否添加
		if(!M('ruler')->find(['fc'=>'Classtype/get_html','pid'=>41])){
			M('ruler')->add(['fc'=>'Classtype/get_html','pid'=>41,'name'=>'获取栏目模板','isdesktop'=>0,'sys'=>1]);
		}
		

		//新增配置参数
		if(!M('sysconfig')->find(['field'=>'web_logo'])){
			M('sysconfig')->add(['field'=>'web_logo','title'=>'网站LOGO','type'=>0,'data'=>'/static/default/assets/img/logo.png']);
		}

		$plugin = M('plugins')->find(['filepath'=>'jizhicmsupdate']);
		if($plugin){
			M('plugins')->update(['id'=>$plugin['id']],['version'=>'2.4','addtime'=>strtotime('2020-03-26')]);
		}
		
		M('sysconfig')->update(['field'=>'web_version'],['data'=>'1.6.7']);
		
		//更新配置
		setCache('webconfig',null);
		setCache('customconfig',null);

	
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




