<?php

// +----------------------------------------------------------------------
// | JiZhiCMS { 极致CMS，给您极致的建站体验 }  
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2099 http://www.jizhicms.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 留恋风 <2581047041@qq.com>
// +----------------------------------------------------------------------
// | Date：2019/09/15
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
		if($this->webconf['web_version']!='1.4'){
			JsonReturn(['code'=>1,'msg'=>'您的软件系统版本为'.$this->webconf['web_version'].'，该插件仅支持1.4版本升级到1.5版本！']);
		}
		//更新系统版本
		M('sysconfig')->update(['field'=>'web_version'],['data'=>'1.5']);
		
		//新增公共配置
		if(!M('sysconfig')->find(['field'=>'domain'])){
			M('sysconfig')->add(['field'=>'domain','title'=>'网站网址','tip'=>'全局网址,最后不带/(斜杠)','type'=>0,'data'=>'']);
		}
		setCache('webconfig',null);
		//更换文件
		$filelist = [
			APP_PATH.'A/exts/update_1_4_to_1_5/file/fields-add.html'=>APP_PATH.'A/t/tpl/fields-add.html',
			APP_PATH.'A/exts/update_1_4_to_1_5/file/fields-edit.html'=>APP_PATH.'A/t/tpl/fields-edit.html',
			APP_PATH.'A/exts/update_1_4_to_1_5/file/member-add.html'=>APP_PATH.'A/t/tpl/member-add.html',
			APP_PATH.'A/exts/update_1_4_to_1_5/file/member-edit.html'=>APP_PATH.'A/t/tpl/member-edit.html',
			APP_PATH.'A/exts/update_1_4_to_1_5/file/sys.html'=>APP_PATH.'A/t/tpl/sys.html',
			APP_PATH.'A/exts/update_1_4_to_1_5/file/Functions.php'=>APP_PATH.'Conf/Functions.php',
			APP_PATH.'A/exts/update_1_4_to_1_5/file/Page.php'=>APP_PATH.'FrPHP/Extend/Page.php',
			APP_PATH.'A/exts/update_1_4_to_1_5/file/Fr.php'=>APP_PATH.'FrPHP/Fr.php',
			APP_PATH.'A/exts/update_1_4_to_1_5/file/CommonController.php'=>APP_PATH.'Home/c/CommonController.php',
			APP_PATH.'A/exts/update_1_4_to_1_5/file/HomeController.php'=>APP_PATH.'Home/c/HomeController.php',
			APP_PATH.'A/exts/update_1_4_to_1_5/file/LoginController.php'=>APP_PATH.'Home/c/LoginController.php',
			APP_PATH.'A/exts/update_1_4_to_1_5/file/UserController.php'=>APP_PATH.'Home/c/UserController.php',
			APP_PATH.'A/exts/update_1_4_to_1_5/file/README.md'=>APP_PATH.'FrPHP/lib/README.md',
			APP_PATH.'A/exts/update_1_4_to_1_5/file/readme.txt'=>APP_PATH.'readme.txt',
			APP_PATH.'A/exts/update_1_4_to_1_5/file/View.php'=>APP_PATH.'FrPHP/lib/View.php',
			APP_PATH.'A/exts/update_1_4_to_1_5/file/FieldsController.php'=>APP_PATH.'A/c/FieldsController.php',
			
		];
		
		foreach($filelist as $k=>$v){
			copy($k,$v);
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
		
		M('plugins')->update(['id'=>$data['id']],['config'=>json_encode($data,JSON_UNESCAPED_UNICODE)]);
		setCache('hook',null);//清空hook缓存
		JsonReturn(['code'=>0,'msg'=>'设置成功！']);
		
	}
	
	
}




