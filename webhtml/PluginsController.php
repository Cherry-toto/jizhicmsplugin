<?php

// +----------------------------------------------------------------------
// | JiZhiCMS { 极致CMS，给您极致的建站体验 }  
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2099 http://www.jizhicms.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 留恋风 <2581047041@qq.com>
// +----------------------------------------------------------------------
// | Date：2019/10/09
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
		$filepath = APP_PATH.'Admin/plugins/WebHtmlController.php';
		if(file_exists($filepath)){
			JsonReturn(array('code'=>1,'msg'=>'前台Admin下面已存在相应的WebHtml控制器！'));
		}
		//移动静态HTML文件到后台tpl中
		$src = APP_PATH.'A/exts/webhtml/file/webhtml.html';
		$dst = APP_PATH.'A/t/tpl/webhtml.html';
		copy($src,$dst);
		
		//写入系统权限控制
		$w['name'] = '独立静态网站';
		$w['fc'] = 'WebHtml';
		$pid = M('ruler')->add($w);
		$w['name'] = '生成静态网站';
		$w['fc'] = 'WebHtml/index';
		$w['pid'] = $pid;
		$w['isdesktop'] = 1;
		$n = M('ruler')->add($w);
		
		//写入左侧导航栏
		$dao = M('layout')->find('id=1');
		$left_layout = json_decode($dao['left_layout'],1);
		$left_layout[]=[
			"name" => "独立静态网站",
			"icon" => "&amp;#xe6da;",
			"nav" => array($n)
		];
		$left_layout = json_encode($left_layout,JSON_UNESCAPED_UNICODE);
		M('layout')->update(['id'=>$dao['id']],['left_layout'=>$left_layout]);
		return true;
		
	}
	
	//卸载程序,对新增字段、表等进行删除SQL操作，或者其他操作
	public function uninstall(){
		//下面是删除test表的SQL操作
		
		//删除文件
		unlink(APP_PATH.'A/t/tpl/webhtml.html');
		//删除权限表
		M('ruler')->delete(['fc'=>'WebHtml']);
		M('ruler')->delete(['fc'=>'WebHtml/index']);
		//删除左侧导航
		$dao = M('layout')->find('id=1');
		$left_layout = json_decode($dao['left_layout'],1);
		$new_layout = [];
		foreach($left_layout as $v){
			if($v['name']!='独立静态网站'){
				$new_layout[]=$v;
			}
		}
		$left_layout = json_encode($new_layout,JSON_UNESCAPED_UNICODE);
		M('layout')->update(['id'=>$dao['id']],['left_layout'=>$left_layout]);
		
		
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
		JsonReturn(['code'=>0,'msg'=>'设置成功！']);
		
		
		
	}
	
	
}




