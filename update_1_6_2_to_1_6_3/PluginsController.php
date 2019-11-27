<?php

// +----------------------------------------------------------------------
// | JiZhiCMS { 极致CMS，给您极致的建站体验 }  
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2099 http://www.jizhicms.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 留恋风 <2581047041@qq.com>
// +----------------------------------------------------------------------
// | Date：2019/11/24
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
		if(!version_compare($this->webconf['web_version'],'1.6.2','==')){
			JsonReturn(['code'=>1,'msg'=>'您的软件系统版本为'.$this->webconf['web_version'].'，该插件仅支持1.6.2版本升级到1.6.3版本！']);
		}

		//替换文件
		//备份文件
		$dir = APP_PATH.'A/exts/update_1_6_2_to_1_6_3/back';
		copy(APP_PATH.'A/t/tpl/article-add.html',$dir.'/article-add.html');
		copy(APP_PATH.'A/t/tpl/article-list.html',$dir.'/article-list.html');
		copy(APP_PATH.'A/t/tpl/beifen.html',$dir.'/beifen.html');
		copy(APP_PATH.'A/t/tpl/classtype-add.html',$dir.'/classtype-add.html');
		copy(APP_PATH.'A/t/tpl/classtype-edit.html',$dir.'/classtype-edit.html');
		copy(APP_PATH.'A/t/tpl/comment-list.html',$dir.'/comment-list.html');
		copy(APP_PATH.'A/t/tpl/extmolds-add.html',$dir.'/extmolds-add.html');
		copy(APP_PATH.'A/t/tpl/extmolds-edit.html',$dir.'/extmolds-edit.html');
		copy(APP_PATH.'A/t/tpl/extmolds-list.html',$dir.'/extmolds-list.html');
		copy(APP_PATH.'A/t/tpl/fields-add.html',$dir.'/fields-add.html');
		copy(APP_PATH.'A/t/tpl/fields-edit.html',$dir.'/fields-edit.html');
		copy(APP_PATH.'A/t/tpl/index.html',$dir.'/index.html');
		copy(APP_PATH.'A/t/tpl/member-add.html',$dir.'/member-add.html');
		copy(APP_PATH.'A/t/tpl/message-list.html',$dir.'/message-list.html');
		copy(APP_PATH.'A/t/tpl/molds-list.html',$dir.'/molds-list.html');
		copy(APP_PATH.'A/t/tpl/product-add.html',$dir.'/product-add.html');
		copy(APP_PATH.'A/t/tpl/product-list.html',$dir.'/product-list.html');
		copy(APP_PATH.'A/t/tpl/ruler-list.html',$dir.'/ruler-list.html');
		copy(APP_PATH.'A/t/tpl/showlabel.html',$dir.'/showlabel.html');
		copy(APP_PATH.'A/t/tpl/sys.html',$dir.'/sys.html');
		copy(APP_PATH.'A/t/tpl/welcome.html',$dir.'/welcome.html');
		copy(APP_PATH.'A/t/tpl/style/css/xadmin.css',$dir.'/xadmin.css');
		copy(APP_PATH.'A/t/tpl/style/js/xadmin.js',$dir.'/xadmin.js');
		//替换文件
		$dir = APP_PATH.'A/exts/update_1_6_2_to_1_6_3/file/public/admintpl';
		$dh =  opendir($dir);
		while (($file= readdir($dh)) !== false){
			if( $file!="." && $file!=".." && (strpos($file,'.css')===false) && (strpos($file,'.js')===false)){
			 copy($dir."/".$file,APP_PATH.'A/t/tpl/'.$file);
		    }
		}
		closedir($dh);
		copy($dir.'/xadmin.css',APP_PATH.'A/t/tpl/style/css/xadmin.css');
		copy($dir.'/xadmin.js',APP_PATH.'A/t/tpl/style/js/xadmin.js');
		//备份文件
		$dir = APP_PATH.'A/exts/update_1_6_2_to_1_6_3/back';
		copy(APP_PATH.'Conf/Functions.php',$dir.'/Functions.php');
		copy(APP_PATH.'FrPHP/Extend/DatabaseTool.php',$dir.'/DatabaseTool.php');
		copy(APP_PATH.'Home/c/CommentController.php',$dir.'/CommentController.php');
		copy(APP_PATH.'Home/c/HomeController.php',$dir.'/HomeController.php');
		copy(APP_PATH.'Home/c/MessageController.php',$dir.'/MessageController.php');
		//替换
		$dir = APP_PATH.'A/exts/update_1_6_2_to_1_6_3/file/public';
		copy($dir.'/conf/Functions.php',APP_PATH.'Conf/Functions.php');
		copy($dir.'/frphp/DatabaseTool.php',APP_PATH.'FrPHP/Extend/DatabaseTool.php');
		copy($dir.'/home/CommentController.php',APP_PATH.'Home/c/CommentController.php');
		copy($dir.'/home/HomeController.php',APP_PATH.'Home/c/HomeController.php');
		copy($dir.'/home/MessageController.php',APP_PATH.'Home/c/MessageController.php');
		
		if(file_exists(APP_PATH.'install/index.php')){
			copy($dir.'/install/index.php',APP_PATH.'install/index.php');
		
		}

		//插入SQL
		if(!M('ruler')->find(['fc'=>'Article/checkAll'])){
			$w['name'] ='批量审核'; 
			$w['fc'] ='Article/checkAll'; 
			$w['pid'] = 8; 
			$w['isdesktop'] = 0; 
			$w['sys'] = 1; 
			M('ruler')->add($w);
		}
		if(!M('ruler')->find(['fc'=>'Product/checkAll'])){
			$w['name'] ='批量审核'; 
			$w['fc'] ='Product/checkAll'; 
			$w['pid'] = 104; 
			$w['isdesktop'] = 0; 
			$w['sys'] = 1; 
			M('ruler')->add($w);
		}
		if(!M('ruler')->find(['fc'=>'Message/checkAll'])){
			$w['name'] ='批量审核'; 
			$w['fc'] ='Message/checkAll'; 
			$w['pid'] = 21; 
			$w['isdesktop'] = 0; 
			$w['sys'] = 1; 
			M('ruler')->add($w);
		}
		if(!M('ruler')->find(['fc'=>'Comment/checkAll'])){
			$w['name'] ='批量审核'; 
			$w['fc'] ='Comment/checkAll'; 
			$w['pid'] = 15; 
			$w['isdesktop'] = 0; 
			$w['sys'] = 1; 
			M('ruler')->add($w);
		}
		if(!M('ruler')->find(['fc'=>'Extmolds/checkAll/molds/links'])){
			$w['name'] ='批量审核友情链接'; 
			$w['fc'] ='Extmolds/checkAll/molds/links'; 
			$w['pid'] = 77; 
			$w['isdesktop'] = 0; 
			$w['sys'] = 0; 
			M('ruler')->add($w);
		}
		if(!M('ruler')->find(['fc'=>'Extmolds/checkAll/molds/tags'])){
			$w['name'] ='批量审核TAG'; 
			$w['fc'] ='Extmolds/checkAll/molds/tags'; 
			$w['pid'] = 77; 
			$w['isdesktop'] = 0; 
			$w['sys'] = 0; 
			M('ruler')->add($w);
		}
		$w = [];
		if(!M('sysconfig')->find(['field'=>'autocheckmessage'])){
			$w['field'] ='autocheckmessage'; 
			$w['title'] ='是否留言自动审核'; 
			$w['tip'] = '开启后，留言自动审核（显示）'; 
			$w['type'] = 0; 
			$w['data'] = '0'; 
			M('sysconfig')->add($w);
		}
		if(!M('sysconfig')->find(['field'=>'autocheckcomment'])){
			$w['field'] ='autocheckcomment'; 
			$w['title'] ='是否评论自动审核'; 
			$w['tip'] = '开启后评论自动审核（显示）'; 
			$w['type'] = 0; 
			$w['data'] = '0'; 
			M('sysconfig')->add($w);
		}
		if(!M('sysconfig')->find(['field'=>'mingan'])){
			$w['field'] ='mingan'; 
			$w['title'] ='网站敏感词过滤'; 
			$w['tip'] = '将敏感词放到里面，用“,”分隔，用{xxx}代替通配内容'; 
			$w['type'] = 0; 
			$w['data'] = '最,最佳,最具,最爱,最赚,最优,最优秀,最大,最大程度,最高,最高级,最高端,最奢侈,最低,最低级,最低价,最底,最便宜,史上最低价,最流行,最受欢迎,最时尚,最聚拢,最符合,最舒适,最先,最先进,最先进科学,最后,最新,最新技术,最新科学,第一,中国第一,全网第一,销量第一,排名第一,唯一,第一品牌,NO.1,TOP1,独一无二,全国第一,遗留,一天,仅此一次,仅此一款,最后一波,全国{xxx}大品牌之一,销冠,国家级,国际级,世界级,千万级,百万级,星级,5A,甲级,超甲级,顶级,顶尖,尖端,顶级享受,高级,极品,极佳,绝佳,绝对,终极,极致,致极,极具,完美,绝佳,极佳,至,至尊,至臻,臻品,臻致,臻席,压轴,问鼎,空前,绝后,绝版,无双,非此莫属,巅峰,前所未有,无人能及,顶级,鼎级,鼎冠,定鼎,完美,翘楚之作,不可再生,不可复制,绝无仅有,寸土寸金,淋漓尽致,无与伦比,唯一,卓越,卓著,稀缺,前无古人后无来者,绝版,珍稀,臻稀,稀少,绝无仅有,绝不在有,稀世珍宝,千金难求,世所罕见,不可多得,空前绝后,寥寥无几,屈指可数,独家,独创,独据,开发者,缔造者,创始者,发明者,首个,首选,独家,首发,首席,首府,首选,首屈一指,全国首家,国家领导人,国门,国宅,首次,填补国内空白,国际品质,黄金旺铺,黄金价值,黄金地段,金钱,金融汇币图片,外国货币,金牌,名牌,王牌,领先上市,巨星,著名,掌门人,至尊,冠军,王之王,王者楼王,墅王,皇家,世界领先,遥遥领先,领导者,领袖,引领,创领,领航,耀领,史无前例,前无古人,永久,万能,百分之百,特供,专供,专家推荐,国家{xxx}领导人推荐'; 
			M('sysconfig')->add($w);
		}

		//更新
		M('layout')->update(['id'=>1],['left_layout'=>'[{"name":"网站管理","icon":"&amp;#xe699;","nav":["42","9","95","83","147","22"]},{"name":"商品管理","icon":"&amp;#xe698;","nav":["105","129","2","118","123","16"]},{"name":"扩展管理","icon":"&amp;#xe6ce;","nav":["76","116","141","142","143","35","61","154","153"]},{"name":"系统设置","icon":"&amp;#xe6ae;","nav":["40","54","49","70","115","114","0","0","66","0"]}]']);
		
		//检查系统是版本，特殊文件替换，及相关SQL操作
		if(!defined('DB_TYPE') || DB_TYPE=='mysql'){
			$dir = APP_PATH.'A/exts/update_1_6_2_to_1_6_3/file/mysql/admin';
			$dh =  opendir($dir);
			while (($file= readdir($dh)) !== false){
				if( $file!="." && $file!=".." && (strpos($file,'.css')===false) && (strpos($file,'.js')===false)){
				 copy($dir."/".$file,APP_PATH.'A/c/'.$file);
			    }
			}
			closedir($dh);


		}else{

			$dir = APP_PATH.'A/exts/update_1_6_2_to_1_6_3/file/sqlite/admin';
			$dh =  opendir($dir);
			while (($file= readdir($dh)) !== false){
				if( $file!="." && $file!=".." && (strpos($file,'.css')===false) && (strpos($file,'.js')===false)){
				 copy($dir."/".$file,APP_PATH.'A/c/'.$file);
			    }
			}
			closedir($dh);

		}

		$config = include(APP_PATH.'Conf/config.php');
	   	$config['APP_DEBUG'] = true;
	   	$ress = file_put_contents(APP_PATH.'Conf/config.php', '<?php return ' . var_export($config, true) . '; ?>');
	   	$dir = APP_PATH.'A/exts/update_1_6_2_to_1_6_3/file/public';
	   	copy($dir.'/index.php',APP_PATH.'index.php');
		copy($dir.'/admin.php',APP_PATH.'admin.php');
		
		//更新系统版本
		M('sysconfig')->update(['field'=>'web_version'],['data'=>'1.6.3']);
		
		
		
		
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




