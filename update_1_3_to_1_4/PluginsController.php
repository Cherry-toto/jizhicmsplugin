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
			if(strpos($_SESSION['admin']['paction'],','.$action.',')==false){
				$ac = M('Ruler')->find(array('fc'=>$action));
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
		if($this->webconf['web_version']!='1.3'){
			JsonReturn(['code'=>1,'msg'=>'您的软件系统版本为'.$this->webconf['web_version'].'，该插件仅支持1.3版本升级到1.4版本！']);
		}
		//更新系统版本
		M('sysconfig')->update(['field'=>'web_version'],['data'=>'1.4']);
		setCache('webconfig',null);
		//更换文件
		$filelist = [
			APP_PATH.'A/exts/update_1_4/file/Fr.php'=>APP_PATH.'FrPHP/Fr.php',
			APP_PATH.'A/exts/update_1_4/file/Functions.php'=>APP_PATH.'Conf/Functions.php',
			APP_PATH.'A/exts/update_1_4/file/ScreenController.php'=>APP_PATH.'Home/c/ScreenController.php',
			APP_PATH.'A/exts/update_1_4/file/HomeController.php'=>APP_PATH.'Home/c/HomeController.php',
			APP_PATH.'A/exts/update_1_4/file/MypayController.php'=>APP_PATH.'Home/c/MypayController.php',
			APP_PATH.'A/exts/update_1_4/file/OrderController.php'=>APP_PATH.'Home/c/OrderController.php',
			APP_PATH.'A/exts/update_1_4/file/MoldsController.php'=>APP_PATH.'A/c/MoldsController.php',
			APP_PATH.'A/exts/update_1_4/file/ArticleController.php'=>APP_PATH.'A/c/ArticleController.php',
			APP_PATH.'A/exts/update_1_4/file/ProductController.php'=>APP_PATH.'A/c/ProductController.php',
			APP_PATH.'A/exts/update_1_4/file/MessageController.php'=>APP_PATH.'A/c/MessageController.php',
			APP_PATH.'A/exts/update_1_4/file/ExtmoldsController.php'=>APP_PATH.'A/c/ExtmoldsController.php',
			APP_PATH.'A/exts/update_1_4/file/article-list.html'=>APP_PATH.'A/t/tpl/article-list.html',
			APP_PATH.'A/exts/update_1_4/file/product-list.html'=>APP_PATH.'A/t/tpl/product-list.html',
			APP_PATH.'A/exts/update_1_4/file/message-list.html'=>APP_PATH.'A/t/tpl/message-list.html',
			APP_PATH.'A/exts/update_1_4/file/message-details.html'=>APP_PATH.'A/t/tpl/message-details.html',
			APP_PATH.'A/exts/update_1_4/file/extmolds-list.html'=>APP_PATH.'A/t/tpl/extmolds-list.html',
			APP_PATH.'A/exts/update_1_4/file/extmolds-add.html'=>APP_PATH.'A/t/tpl/extmolds-add.html',
			APP_PATH.'A/exts/update_1_4/file/extmolds-edit.html'=>APP_PATH.'A/t/tpl/extmolds-edit.html',
			APP_PATH.'A/exts/update_1_4/file/wechat/WxpayCheckOrder.php'=>APP_PATH.'FrPHP/Extend/pay/wechat/WxpayCheckOrder.php',
			APP_PATH.'A/exts/update_1_4/file/wechat/WxpayH5Service.php'=>APP_PATH.'FrPHP/Extend/pay/wechat/WxpayH5Service.php',
			APP_PATH.'A/exts/update_1_4/file/wechat/WxpayScan.php'=>APP_PATH.'FrPHP/Extend/pay/wechat/WxpayScan.php',
			APP_PATH.'A/exts/update_1_4/file/wechat/WxpayService.php'=>APP_PATH.'FrPHP/Extend/pay/wechat/WxpayService.php',
			APP_PATH.'A/exts/update_1_4/file/wechat/WxpayServiceCheck.php'=>APP_PATH.'FrPHP/Extend/pay/wechat/WxpayServiceCheck.php'
		];
		
		foreach($filelist as $k=>$v){
			copy($k,$v);
		}
		
		//删除SQL
		M('fields')->delete(['field'=>'isshow']);
		//更新表字段信息
		$sql = "ALTER TABLE ".DB_PREFIX."links  modify column isshow tinyint(1);";
		$sql .= "ALTER TABLE ".DB_PREFIX."tags  modify column isshow tinyint(1);";
		$sql .="ALTER TABLE ".DB_PREFIX."links ALTER COLUMN isshow SET DEFAULT '1';";
		$sql .="ALTER TABLE ".DB_PREFIX."tags ALTER COLUMN isshow SET DEFAULT '1';";
		$sql .="ALTER TABLE ".DB_PREFIX."message ADD isshow tinyint(1) DEFAULT '0';";
		M()->runSql($sql);
		
		
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




