<?php

// +----------------------------------------------------------------------
// | JiZhiCMS { 极致CMS，给您极致的建站体验 }  
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2099 http://www.jizhicms.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 留恋风 <2581047041@qq.com>
// +----------------------------------------------------------------------
// | Date：2019/11/17
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
		 $this->classtypetree =  get_classtype_tree();
		//插件模板页目录
		
		$this->tpl = '@'.dirname(__FILE__).'/tpl/';
		
		/**
			在下面添加自定义操作
		**/
		
		
	}
	
	//执行SQL语句在此处处理,或者移动文件也可以在此处理
	public  function install(){
		//下面是新增test表的SQL操作
		$filepath = APP_PATH.'A/plugins/ClasstypeNavController.php';
		if(file_exists($filepath)){
			JsonReturn(array('code'=>1,'msg'=>'后台A/plugins下面已存在相应的ClasstypeNav控制器！'));
		}
		//检测版本号
		if(!version_compare($this->webconf['web_version'],'1.6','>')){
			JsonReturn(['code'=>1,'msg'=>'您的软件系统版本为'.$this->webconf['web_version'].'，该插件仅支持1.6版本以上！']);
		}

		//注册到hook里面
		if(defined('DB_TYPE')){
			switch (DB_TYPE) {
				case 'mysql':
					$w['module'] = 'A';
					$w['namespace'] = 'A\\\\c';
					$w['controller'] = 'Classtype';
					$w['action'] = 'addclass';
					$w['all_action'] = 0;
					$w['hook_namespace'] = '\\\\A\\\\plugins';
					$w['hook_controller'] = 'ClasstypeNav';
					$w['hook_action'] = 'addclass';
					$w['plugins_name'] = 'classtypenav';//插件文件夹
					$w['addtime'] = time();
					M('hook')->add($w);
					$w['module'] = 'A';
					$w['namespace'] = 'A\\\\c';
					$w['controller'] = 'Classtype';
					$w['action'] = 'deleteclass';
					$w['all_action'] = 0;
					$w['hook_namespace'] = '\\\\A\\\\plugins';
					$w['hook_controller'] = 'ClasstypeNav';
					$w['hook_action'] = 'deleteclass';
					$w['plugins_name'] = 'classtypenav';//插件文件夹
					M('hook')->add($w);
					$w['module'] = 'A';
					$w['namespace'] = 'A\\\\c';
					$w['controller'] = 'Classtype';
					$w['action'] = 'editclass';
					$w['all_action'] = 0;
					$w['hook_namespace'] = '\\\\A\\\\plugins';
					$w['hook_controller'] = 'ClasstypeNav';
					$w['hook_action'] = 'editclass';
					$w['plugins_name'] = 'classtypenav';//插件文件夹
					M('hook')->add($w);
					break;
				case 'sqlite':
					$w['module'] = 'A';
					$w['namespace'] = 'A\\c';
					$w['controller'] = 'Classtype';
					$w['action'] = 'addclass';
					$w['all_action'] = 0;
					$w['hook_namespace'] = '\\A\\plugins';
					$w['hook_controller'] = 'ClasstypeNav';
					$w['hook_action'] = 'addclass';
					$w['plugins_name'] = 'classtypenav';//插件文件夹
					$w['addtime'] = time();
					M('hook')->add($w);
					$w['module'] = 'A';
					$w['namespace'] = 'A\\c';
					$w['controller'] = 'Classtype';
					$w['action'] = 'deleteclass';
					$w['all_action'] = 0;
					$w['hook_namespace'] = '\\A\\plugins';
					$w['hook_controller'] = 'ClasstypeNav';
					$w['hook_action'] = 'deleteclass';
					$w['plugins_name'] = 'classtypenav';//插件文件夹
					M('hook')->add($w);
					$w['module'] = 'A';
					$w['namespace'] = 'A\\c';
					$w['controller'] = 'Classtype';
					$w['action'] = 'editclass';
					$w['all_action'] = 0;
					$w['hook_namespace'] = '\\A\\plugins';
					$w['hook_controller'] = 'ClasstypeNav';
					$w['hook_action'] = 'editclass';
					$w['plugins_name'] = 'classtypenav';//插件文件夹
					M('hook')->add($w);
					break;
				default:
					# code...
					break;
			}
		}else{
			$w['module'] = 'A';
				$w['namespace'] = 'A\\\\c';
				$w['controller'] = 'Classtype';
				$w['action'] = 'addclass';
				$w['all_action'] = 0;
				$w['hook_namespace'] = '\\\\A\\\\plugins';
				$w['hook_controller'] = 'ClasstypeNav';
				$w['hook_action'] = 'addclass';
				$w['plugins_name'] = 'classtypenav';//插件文件夹
				$w['addtime'] = time();
				M('hook')->add($w);
				$w['module'] = 'A';
				$w['namespace'] = 'A\\\\c';
				$w['controller'] = 'Classtype';
				$w['action'] = 'deleteclass';
				$w['all_action'] = 0;
				$w['hook_namespace'] = '\\\\A\\\\plugins';
				$w['hook_controller'] = 'ClasstypeNav';
				$w['hook_action'] = 'deleteclass';
				$w['plugins_name'] = 'classtypenav';//插件文件夹
				M('hook')->add($w);
				$w['module'] = 'A';
				$w['namespace'] = 'A\\\\c';
				$w['controller'] = 'Classtype';
				$w['action'] = 'editclass';
				$w['all_action'] = 0;
				$w['hook_namespace'] = '\\\\A\\\\plugins';
				$w['hook_controller'] = 'ClasstypeNav';
				$w['hook_action'] = 'editclass';
				$w['plugins_name'] = 'classtypenav';//插件文件夹
				M('hook')->add($w);
		}
		
		setCache('hook',null);//清空hook缓存

		//检查是否使用了【栏目便签插件】
		if(M('plugins')->find(['filepath'=>'classtypetemplate'])){
			//修复相关文件
			//移动后台文件
			$dir = APP_PATH.'A/t/tpl';
			copy($dir."/classtype-add.html",APP_PATH.'A/exts/classtypenav/back/classtype-add.html');
			copy($dir."/classtype-edit.html",APP_PATH.'A/exts/classtypenav/back/classtype-edit.html');
			$dir = APP_PATH.'A/exts/classtypenav/file';
			copy($dir."/classtype-add.html",APP_PATH.'A/t/tpl/classtype-add.html');
			copy($dir."/classtype-edit.html",APP_PATH.'A/t/tpl/classtype-edit.html');
		}


		$this->setNav();



		return true;
		
	}
	
	//卸载程序,对新增字段、表等进行删除SQL操作，或者其他操作
	public function uninstall(){
		//下面是删除test表的SQL操作
		M('hook')->delete(['plugins_name'=>'classtypenav']);
		setCache('hook',null);//清空hook缓存
		//检查是否使用了【栏目便签插件】
		if(M('plugins')->find(['filepath'=>'classtypetemplate'])){
			$dir = APP_PATH.'A/exts/classtypenav/back';
			copy($dir."/classtype-add.html",APP_PATH.'A/t/tpl/classtype-add.html');
			copy($dir."/classtype-edit.html",APP_PATH.'A/t/tpl/classtype-edit.html');
		}
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

	function setNav(){
		$classtypenav = M('ruler')->find(['fc'=>'Classtypenav']);
		if(!$classtypenav){
			$w['fc'] = 'Classtypenav';
			$w['name'] = '栏目导航';
			$w['pid'] = 0;
			$w['isdesktop'] = 0;
			$w['sys'] = 0;
			$pid = M('ruler')->add($w);
		}else{
			$pid = $classtypenav['id'];
		}

		foreach ($this->classtypetree as $key => $value) {
			$w=[];
			$w['pid'] = $pid;
			$w['isdesktop'] = 1;
			$w['sys']=0;
			switch ($value['molds']) {
				case 'article':
					if($value['details_html']==''){
						$w['fc'] = 'Classtype/editclass/id/'.$value['id'];
					}else{
						$w['fc'] = 'Article/articlelist/tid/'.$value['id'];
					}
					
					break;
				case 'product':
					if($value['details_html']==''){
						$w['fc'] = 'Classtype/editclass/id/'.$value['id'];
					}else{
						$w['fc'] = 'Product/productlist/tid/'.$value['id'];
					}
					
					break;
				case 'orders':
				case 'member':
				case 'member_group':
				case 'comment':
				case 'message':
				case 'collect':
				case 'links':
				case 'level':
				case 'tags':
					$w['fc'] = 'Classtype/editclass/id/'.$value['id'];
					break;
				default:
					if($value['details_html']==''){
						$w['fc'] = 'Classtype/editclass/id/'.$value['id'];
					}else{
						$w['fc'] = 'Extmolds/index/molds/'.$value['molds'].'/tid/'.$value['id'];
					}
					break;
			}
			
			$w['name'] = $value['classname'];
			if(!M('ruler')->find($w)){
				M('ruler')->add($w);
			}
			
		}

	}
	
	
}




