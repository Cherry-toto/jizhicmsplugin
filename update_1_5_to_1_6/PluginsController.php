<?php

// +----------------------------------------------------------------------
// | JiZhiCMS { 极致CMS，给您极致的建站体验 }  
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2099 http://www.jizhicms.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 留恋风 <2581047041@qq.com>
// +----------------------------------------------------------------------
// | Date：2019/10/10
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
		if(version_compare($this->webconf['web_version'],'1.6','>=')){
			JsonReturn(['code'=>1,'msg'=>'您的软件系统版本为'.$this->webconf['web_version'].'，该插件仅支持1.5x版本升级到1.6版本！']);
		}
		
		//更新系统版本
		M('sysconfig')->update(['field'=>'web_version'],['data'=>'1.6']);
		
		
		//新增公共配置
		if(!M('sysconfig')->find(['field'=>'isrelative'])){
			M('sysconfig')->add(['field'=>'isrelative','title'=>'基本信息下扩展','tip'=>'新增字段是否显示在【基本信息】底部，默认在【扩展信息】下','type'=>0,'data'=>'1']);
		}
		if(!M('sysconfig')->find(['field'=>'overtime'])){
			M('sysconfig')->add(['field'=>'overtime','title'=>'订单超时','tip'=>'按小时计算，超过该小时订单过期，仅限于开启支付后，0代表不限制','type'=>0,'data'=>'4']);
		}
		if(!M('sysconfig')->find(['field'=>'islevelurl'])){
			M('sysconfig')->add(['field'=>'islevelurl','title'=>'开启层级URL','tip'=>'默认关闭层级URL，开启后URL会按照父类层级展现','type'=>0,'data'=>'1']);
		}
		if(!M('sysconfig')->find(['field'=>'iscachepage'])){
			M('sysconfig')->add(['field'=>'iscachepage','title'=>'缓存完整页面','tip'=>'前台完整页面缓存，结合缓存时间，可以提高访问速度','type'=>0,'data'=>'1']);
		}
		if(!M('sysconfig')->find(['field'=>'isautohtml'])){
			M('sysconfig')->add(['field'=>'isautohtml','title'=>'自动生成静态HTML','tip'=>'前台访问网站页面，将自动生成静态HTML，下次访问直接进入静态HTML页面','type'=>0,'data'=>'0']);
		}
		if(!M('sysconfig')->find(['field'=>'pc_html'])){
			M('sysconfig')->add(['field'=>'pc_html','title'=>'PC静态文件目录','tip'=>'电脑端静态HTML存放目录，默认根目录[ / ]','type'=>0,'data'=>'/']);
		}
		if(!M('sysconfig')->find(['field'=>'mobile_html'])){
			M('sysconfig')->add(['field'=>'mobile_html','title'=>'WAP静态文件目录','tip'=>'手机端静态HTML存放目录，默认[ m ]，PC和WAP静态目录不能相同，否则文件会混乱','type'=>0,'data'=>'m']);
		}
		setCache('webconfig',null);
		//新增权限
		if(!M('ruler')->find(['fc'=>'Sys/high-level'])){
			M('ruler')->add(['fc'=>'Sys/high-level','name'=>'高级设置','pid'=>39,'isdesktop'=>0,'sys'=>1]);
		}
		if(!M('ruler')->find(['fc'=>'Sys/email-order'])){
			M('ruler')->add(['fc'=>'Sys/email-order','name'=>'邮箱订单','pid'=>39,'isdesktop'=>0,'sys'=>1]);
		}
		if(!M('ruler')->find(['fc'=>'Sys/payconfig'])){
			M('ruler')->add(['fc'=>'Sys/payconfig','name'=>'支付配置','pid'=>39,'isdesktop'=>0,'sys'=>1]);
		}
		if(!M('ruler')->find(['fc'=>'Sys/wechatbind'])){
			M('ruler')->add(['fc'=>'Sys/wechatbind','name'=>'公众号配置','pid'=>39,'isdesktop'=>0,'sys'=>1]);
		}
		//新增字段
		//文章模块
		$sql = 'SHOW COLUMNS FROM '.DB_PREFIX.'article ';
		$list = M()->findSql($sql);
		$isgo_istop = true;
		$isgo_ishot = true;
		$isgo_istuijian = true;
		foreach($list as $v){
			if($v['Field']=='istop'){
				$isgo_istop = false;
				
			}
			if($v['Field']=='ishot'){
				$isgo_ishot = false;
				
			}
			if($v['Field']=='istuijian'){
				$isgo_istuijian = false;
				
			}
		}
		if($isgo_istop){
			$sql = "ALTER TABLE ".DB_PREFIX."article ADD istop VARCHAR(2)  default '0' ";
			M()->runSql($sql);
		}
		if($isgo_ishot){
			$sql = "ALTER TABLE ".DB_PREFIX."article ADD ishot VARCHAR(2)  default '0' ";
			M()->runSql($sql);
		}
		if($isgo_istuijian){
			$sql = "ALTER TABLE ".DB_PREFIX."article ADD istuijian VARCHAR(2)  default '0' ";
			M()->runSql($sql);
		}
		
		
		
		//商品模块
		$sql = 'SHOW COLUMNS FROM '.DB_PREFIX.'product ';
		$list = M()->findSql($sql);
		$isgo_istop = true;
		$isgo_ishot = true;
		$isgo_istuijian = true;
		foreach($list as $v){
			if($v['Field']=='istop'){
				$isgo_istop = false;
				
			}
			if($v['Field']=='ishot'){
				$isgo_ishot = false;
				
			}
			if($v['Field']=='istuijian'){
				$isgo_istuijian = false;
				
			}
		}
		if($isgo_istop){
			$sql = "ALTER TABLE ".DB_PREFIX."product ADD istop VARCHAR(2) default '0' ";
			M()->runSql($sql);
		}
		if($isgo_ishot){
			$sql = "ALTER TABLE ".DB_PREFIX."product ADD ishot VARCHAR(2) default '0' ";
			M()->runSql($sql);
		}
		if($isgo_istuijian){
			$sql = "ALTER TABLE ".DB_PREFIX."product ADD istuijian VARCHAR(2)  default '0' ";
			M()->runSql($sql);
		}
		
		
		
		//栏目添加gourl外链
		$sql = 'SHOW COLUMNS FROM '.DB_PREFIX.'classtype ';
		$list = M()->findSql($sql);
		$isgo = true;
		foreach($list as $v){
			if($v['Field']=='gourl'){
				$isgo = false;
			}
		}
		if($isgo){
			$sql = "ALTER TABLE ".DB_PREFIX."classtype ADD gourl VARCHAR(255) default NULL ";
			M()->runSql($sql);
		}
		//模型添加isclasstype栏目显示
		$sql = 'SHOW COLUMNS FROM '.DB_PREFIX.'molds ';
		$list = M()->findSql($sql);
		$isgo = true;
		foreach($list as $v){
			if($v['Field']=='isclasstype'){
				$isgo = false;
			}
		}
		if($isgo){
			$sql = "ALTER TABLE ".DB_PREFIX."molds ADD isclasstype TINYINT(1) default '1' ";
			M()->runSql($sql);
		}
		
		//更换文件
		//后台模板
		$dir = APP_PATH.'A/exts/update_1_5_to_1_6/file/admin/tpl';
		$dh =  opendir($dir);
		while (($file= readdir($dh)) !== false){
			if( $file!="." && $file!=".."){
			 copy($dir."/".$file,APP_PATH.'A/t/tpl/'.$file);
		    }
		}
		closedir($dh);
		//后台控制器
		$dir = APP_PATH.'A/exts/update_1_5_to_1_6/file/admin/c';
		$dh =  opendir($dir);
		while (($file= readdir($dh)) !== false){
			if( $file!="." && $file!=".."){
			 copy($dir."/".$file,APP_PATH.'A/c/'.$file);
		    }
		}
		closedir($dh);
		//前台控制器
		$dir = APP_PATH.'A/exts/update_1_5_to_1_6/file/home/c';
		$dh =  opendir($dir);
		while (($file= readdir($dh)) !== false){
			if( $file!="." && $file!=".."){
			 copy($dir."/".$file,APP_PATH.'Home/c/'.$file);
		    }
		}
		closedir($dh);
		//前台配置
		$dir = APP_PATH.'A/exts/update_1_5_to_1_6/file/config';
		$dh =  opendir($dir);
		while (($file= readdir($dh)) !== false){
			if( $file!="." && $file!=".."){
			 copy($dir."/".$file,APP_PATH.'Conf/'.$file);
		    }
		}
		closedir($dh);
		//系统文件
		copy(APP_PATH.'A/exts/update_1_5_to_1_6/file/frphp/Fr.php',APP_PATH.'FrPHP/Fr.php');
		copy(APP_PATH.'A/exts/update_1_5_to_1_6/file/frphp/View.php',APP_PATH.'FrPHP/lib/View.php');
	
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




