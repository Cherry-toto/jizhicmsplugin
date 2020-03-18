<?php

// +----------------------------------------------------------------------
// | JiZhiCMS { 极致CMS，给您极致的建站体验 }  
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2099 http://www.jizhicms.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 留恋风 <2581047041@qq.com>
// +----------------------------------------------------------------------
// | Date：2019/08/24
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
		$filepath = APP_PATH.'Admin/plugins/ClasstypeTemplateController.php';
		
		//移动后台文件
		$dir = APP_PATH.'A/t/tpl';
		copy($dir."/classtype-add.html",APP_PATH.'A/exts/classtypetemplate/back/classtype-add.html');
		copy($dir."/classtype-edit.html",APP_PATH.'A/exts/classtypetemplate/back/classtype-edit.html');
		$dir = APP_PATH.'A/exts/classtypetemplate/file';
		copy($dir."/classtype-add.html",APP_PATH.'A/t/tpl/classtype-add.html');
		copy($dir."/classtype-edit.html",APP_PATH.'A/t/tpl/classtype-edit.html');
		
		if(M('plugins')->find(['filepath'=>'classtypetemplate'])){
			M('plugins')->update(['filepath'=>'classtypetemplate'],['version'=>1.1]);
		}
		

		return true;
		
	}
	
	//卸载程序,对新增字段、表等进行删除SQL操作，或者其他操作
	public function uninstall(){
		//下面是删除test表的SQL操作
		$dir = APP_PATH.'A/exts/classtypetemplate/back';
		copy($dir."/classtype-add.html",APP_PATH.'A/t/tpl/classtype-add.html');
		copy($dir."/classtype-edit.html",APP_PATH.'A/t/tpl/classtype-edit.html');
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
		$w['template'] = format_param($data['template'],1);
		if($w['template']==''){
			JsonReturn(['code'=>1,'msg'=>'项目模板文件目录不能为空！']);
		}
		//判断是否存在模板
		if(!file_exists(APP_PATH.'Home/'.$w['template'])){
			JsonReturn(['code'=>1,'msg'=>APP_PATH.'Home/'.$w['template'].'目录不存在！']);
		}
		M('plugins')->update(['id'=>$data['id']],['config'=>json_encode($w,JSON_UNESCAPED_UNICODE)]);
		
		JsonReturn(['code'=>0,'msg'=>'设置成功！']);
		
		
	}
	
	
}




