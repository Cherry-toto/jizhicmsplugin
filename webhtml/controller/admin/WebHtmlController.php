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


namespace A\plugins;

use A\c\CommonController;
use FrPHP\lib\Controller;
use FrPHP\Extend\Page;
class WebHtmlController extends CommonController
{
	

	function index(){
		
		if($_POST){
			//检查插件是否开启
			if(!M('plugins')->find(['filepath'=>'webhtml','isopen'=>1])){
				echo '您还未开启插件，不能使用！';
				exit;
			}
			
			
			$path = $this->frparam('path',1,false);
			if(!$path || $path=='' || $path=='/'){
				echo '请输入存储文件夹名称！';
				exit;
			}
			if($this->webconf['pc_html']!='/' && $this->webconf['pc_html']!=''){
				echo '电脑静态目录必须为[ / ]，请先修改电脑静态目录！';exit;
			}
			$_SESSION['web_path'] = $path.'/';
			
			//创建目录
			if(!is_dir(APP_PATH.$path)){
				$r = mkdir(APP_PATH.$path,0777,true);
				if(!$r){
					//JsonReturn(['code'=>1,'msg'=>'根目录创建目录失败！']);
					echo '系统创建 [ '.str_replace('/','\\',APP_PATH.$path).' ] 目录失败!';exit;
				}
			}
			
			
			if(!is_dir(APP_PATH.$path.'/static')){
				$r = mkdir(APP_PATH.$path.'/static',0777,true);
				if(!$r){
					//JsonReturn(['code'=>1,'msg'=>'根目录创建目录失败！']);
					echo '系统创建 [ '.str_replace('/','\\',APP_PATH.$path.'/static').' ] 目录失败!';exit;
				}
			}
			
			$dst = APP_PATH.$path.'/static/'.$this->webconf['pc_template'];
			if(!is_dir($dst)){
				$r = mkdir($dst,0777,true);
				if(!$r){
					//JsonReturn(['code'=>1,'msg'=>'根目录创建目录失败！']);
					echo '系统创建 [ '.str_replace('/','\\',$dst).' ] 目录失败!';exit;
				}
			}
			$src = APP_PATH.'static/'.$this->webconf['pc_template'];
			//移动静态资源
			echo '系统正在移动静态资源到指定目录 [ '.APP_PATH.$path.' ] <br/>';
			$this->recurse_copy($src,$dst);
			
			$opts = array(
			  'http'=>array(
				'method'=>"GET",
				'header'=>"Cookie: PHPSESSID=".$_COOKIE['PHPSESSID']."\r\n"
			  )
			);

			$context = stream_context_create($opts);
			
			$type = $this->frparam('type');
			setCache('web_html_cache',null);
			
			$classtypedata = classTypeData();
		
			foreach($classtypedata as $k=>$v){
				$classtypedata[$k]['children'] = get_children($v,$classtypedata);
			}
			if($type==1){
				
				$model = $this->frparam('model',1);
				$isshow = $this->frparam('isshow');
				$tid = $this->frparam('tid');
				$www = get_domain();
				$sql = ' 1=1 ';
				if($isshow!=2){
					$sql.=' and isshow=1 ';
				}
				
				//单独更新
				$modelname = get_info_table('molds',['biaoshi'=>$model],'name');
				switch($model){
					case 'classtype':
						if($tid){
							$sql.=' and id in('.implode(",",$classtypedata[$tid]["children"]["ids"]).') ';
						}
						echo '正在进行栏目静态HTML生成，请稍后……<br/>';
						$this->html_classtype($sql);
						file_put_contents(APP_PATH.$_SESSION['web_path'].'index.html',file_get_contents($www,false,$context));
						echo '成功生成主页HTML [ index.html ] <br/>';
						//判断有没有更新完毕
						if(getCache('web_html_cache')){
							echo '更新内容比较多，刷新继续更新~';
							echo '<script>window.location.reload();</script>';exit;
						}
						echo '静态HTML已全部更新完毕！';
						
						//清空数据缓存
						if(is_dir(APP_PATH.'cache/data')){
							if($handle = opendir(APP_PATH.'cache/data')){
				
							  while (false !== ($file = readdir($handle))){
								 if($file!='.' && $file!='..' ){
									
									unlink(APP_PATH.'cache/data/'.$file);
								 }
							  }
							  closedir($handle);
							}
						}
						exit;
					break;
					//文章商品模块是同样的
					default:
						if($tid){
							$sql.=' and tid in('.implode(",",$classtypedata[$tid]["children"]["ids"]).') ' ;
						}
						echo '正在进行模块静态HTML生成，请稍后……<br/>';
						$this->html_molds($model,$sql);
						
						file_put_contents(APP_PATH.$_SESSION['web_path'].'index.html',file_get_contents($www,false,$context));
						echo '成功生成主页HTML [ index.html ] <br/>';
						//判断有没有更新完毕
						if(getCache('web_html_cache')){
							echo '更新内容比较多，刷新继续更新~';
							echo '<script>window.location.reload();</script>';exit;
						}
						echo '静态HTML已全部更新完毕！';
						
						//清空数据缓存
						if(is_dir(APP_PATH.'cache/data')){
							if($handle = opendir(APP_PATH.'cache/data')){
				
							  while (false !== ($file = readdir($handle))){
								 if($file!='.' && $file!='..' ){
									
									unlink(APP_PATH.'cache/data/'.$file);
								 }
							  }
							  closedir($handle);
							}
						}
						exit;
					break;
					
					
				}
				
				
			}else{
				//批量更新
				//以防内容过多，更新不过来
				$model = $this->frparam('model',2);
				$isshow = $this->frparam('isshow',2);
				$tid = $this->frparam('tid',2);
				$www = get_domain();
				set_time_limit(0);
				if($model && $isshow){
					foreach($model as $k=>$v){
						
						$sql = ' 1=1 ';
						if($isshow[$k]!=2){
							$sql.=' and isshow=1 ';
						}
								
						if($v=='classtype'){
							if($tid[$k]){
								$sql.=' and id in('.implode(",",$classtypedata[$tid[$k]]["children"]["ids"]).') ';
							}
							echo '正在进行栏目静态HTML生成，请稍后……<br/>';
							$this->html_classtype($sql);
						}else{
							if($tid[$k]){
								$sql.=' and tid in('.implode(",",$classtypedata[$tid[$k]]["children"]["ids"]).') ';
							}
							echo '正在进行模块静态HTML生成，请稍后……<br/>';
							$this->html_molds($v,$sql);
							
						}
						
						
					}
					file_put_contents(APP_PATH.$_SESSION['web_path'].'index.html',file_get_contents($www,false,$context));
					echo '成功生成主页HTML [ index.html ] <br/>';
					//判断有没有更新完毕
					if(getCache('web_html_cache')){
						echo '更新内容比较多，刷新继续更新~';
						echo '<script>window.location.reload();</script>';exit;
					}
						//清空数据缓存
						if(is_dir(APP_PATH.'cache/data')){
							if($handle = opendir(APP_PATH.'cache/data')){
				
							  while (false !== ($file = readdir($handle))){
								 if($file!='.' && $file!='..' ){
									
									unlink(APP_PATH.'cache/data/'.$file);
								 }
							  }
							  closedir($handle);
							}
						}
					echo '批量生成HTML完成！';exit;
					
					
				}
				
				
				
				
			}
			
			
		}
		
		
		//是否有静态HTML更新
		$html_cache = getCache('web_html_cache');
		if($html_cache){
			/*
				type  更新类型
				sql   更新的sql查询
				model   更新模型
				curren_num  当前更新数
				all_num  总数

			*/
			$newcache = [];
			foreach($html_cache as $k=>$v){
				switch($v['type']){
					//栏目数超过100个
					case 'classtype':
					$limit = $v['curren_num'].',100';
					if(($v['curren_num']+100) >=$v['all_num']){
						
					}else{
						$v[$k]['curren_num'] = $v['curren_num']+100;
						$newcache[]=$v;
					}
					echo '正在进行栏目静态HTML生成，请稍后……<br/>';
					$this->html_classtype($v['sql'],$limit);
					
					
					break;
					//分页超过100页
					case 'classtype_page':
					//暂时取消
					break;
					//模块内容超过100个
					case 'molds':
						$limit = $v['curren_num'].',100';
						if(($v['curren_num']+100) >=$v['all_num']){
							
						}else{
							$v[$k]['curren_num'] = $v['curren_num']+100;
							$newcache[]=$v;
						}
						echo '正在进行模块静态HTML生成，请稍后……<br/>';
						$this->html_molds($v['model'],$v['sql'],$limit);
					
					break;
					
					
					
				}
				
			}
			
			if(empty($newcache)){
				setCache('web_html_cache',null);
			}else{
				setCache('web_html_cache',$newcache);
				echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><meta http-equiv="refresh" content="3;URL='.U('index').'">';
				echo '系统正在进行下一步生成静态HTML，请稍后~';
				exit;
			}
			
			
		
			
			
		}
		
		
		
		$this->display('webhtml');
	}
	
	
	function html_classtype($sql,$limit=null){
		
		$opts = array(
		  'http'=>array(
			'method'=>"GET",
			'header'=>"Cookie: PHPSESSID=".$_COOKIE['PHPSESSID']."\r\n"
		  )
		);

		$context = stream_context_create($opts);
		$www = get_domain();
		//计算栏目个数
		if($limit==null){
			$count = M('classtype')->getCount($sql);
			if($count>100){
				$limit = '1,100';
				$cache_html = getCache('web_html_cache');
				if($cache_html){
					$cache_html[]=[
						'type' => 'classtype',
						'sql' => $sql,
						'model' => 'classtype',
						'curren_num' => 100,
						'all_num' => $count,
					];
				}else{
					$cache_html = [];
					$cache_html[]=[
						'type' => 'classtype',
						'sql' => $sql,
						'model' => 'classtype',
						'curren_num' => 100,
						'all_num' => $count,
					];
				}
				setCache('web_html_cache',$cache_html);
				
			}
			
		}
		
		$lists = M('classtype')->findAll($sql,' id asc ',null,$limit);
		
		$classtypedata = classTypeData();
		foreach($classtypedata as $k=>$v){
			$classtypedata[$k]['children'] = get_children($v,$classtypedata);
		}
		if($lists){
			//更新静态注意事项：
			//1 创建目录文件夹--权限问题
			//2 栏目在根目录中
			//3 从缓存中抓取是最快的
			
			foreach($lists as $v){
				$filename = $v['htmlurl'].File_TXT;
				//创建文件夹
				if(!is_dir(APP_PATH.$_SESSION['web_path'])){
					$r = mkdir(APP_PATH.$_SESSION['web_path'],0777,true);
					if(!$r){
						//JsonReturn(['code'=>1,'msg'=>'根目录创建目录失败！']);
						echo '系统创建 [ '.str_replace('/','\\',APP_PATH.$_SESSION['web_path']).' ] 目录失败!';exit;
					}
					echo '创建目录 [ '.str_replace('/','\\',APP_PATH.$_SESSION['web_path']).' ] 成功！<br/>';
				}
				if(strpos($filename,'/')!==false){
					$filepath = explode('/',$filename);
					array_pop($filepath);
					$dir = APP_PATH.$_SESSION['web_path'].implode('/',$filepath);
					$create_dir = APP_PATH.$_SESSION['web_path'];
					foreach($filepath as $vv){
						$create_dir.=$vv;
						if(!is_dir($create_dir)){
							$r = mkdir($create_dir,0777,true);
							if(!$r){
								//JsonReturn(['code'=>1,'msg'=>'根目录创建目录失败！']);
								echo '系统创建['.str_replace('/','\\',$create_dir).']目录失败!';exit;
							}
							echo '创建目录['.str_replace('/','\\',$create_dir).']成功！<br/>';
						}
						$create_dir.='/';
						
					}
				}
				
				
				
				$url = APP_PATH.$filename;
				$file_url = APP_PATH.$_SESSION['web_path'].$filename;
				$cache_url = APP_PATH.'cache/data/'.md5(frencode($url));
				if(file_exists($cache_url)){
					//栏目在根目录里
					file_put_contents($file_url,file_get_contents($cache_url,false,$context));
				}else{
					file_put_contents($file_url,file_get_contents($www.'/'.$filename,false,$context));
				}
				echo '成功生成静态HTML [ '.str_replace('/','\\',$file_url).' ]<br/>';
				//检查分页
				$sql = 'tid in('.implode(",",$classtypedata[$v['id']]["children"]["ids"]).') ';
				$count = M($v['molds'])->getCount($sql);
				$pagenum = ceil($count/$v['lists_num']);
				if($pagenum>1){
					echo '正在生成分页静态HTML……<br/>';
					for($i=1;$i<=$pagenum;$i++){
						$filename = $v['htmlurl'].'-'.$i.File_TXT;
						$url = APP_PATH.$filename;
						$file_url = APP_PATH.$_SESSION['web_path'].$filename;
						$cache_url = APP_PATH.'cache/data/'.md5(frencode($url));
						if(file_exists($cache_url)){
							//栏目在根目录里
							file_put_contents($file_url,file_get_contents($cache_url,false,$context));
						}else{
							file_put_contents($file_url,file_get_contents($www.'/'.$filename,false,$context));
						}
					}
					echo '成功生成静态HTML [ '.str_replace('/','\\',$file_url).' ]<br/>';
				}
					
				
			}
		}
		
	}
	
	function html_molds($model,$sql=null,$limit=null){
		$modelname = get_info_table('molds',['biaoshi'=>$model],'name');
		$opts = array(
		  'http'=>array(
			'method'=>"GET",
			'header'=>"Cookie: PHPSESSID=".$_COOKIE['PHPSESSID']."\r\n"
		  )
		);

		$context = stream_context_create($opts);
		if($limit==null){
			$count =  M($model)->getCount($sql);
			if($count>100){
				$limit = '1,100';
				$cache_html = getCache('web_html_cache');
				if($cache_html){
					$cache_html[]=[
						'type' => 'molds',
						'sql' => $sql,
						'model' => $model,
						'curren_num' => 100,
						'all_num' => $count,
					];
				}else{
					$cache_html = [];
					$cache_html[]=[
						'type' => 'molds',
						'sql' => $sql,
						'model' => $model,
						'curren_num' => 100,
						'all_num' => $count,
					];
				}
				setCache('web_html_cache',$cache_html);
			}
		}
		
		$lists = M($model)->findAll($sql,' id asc ',null,$limit);
		$www = get_domain();
		if($lists && is_array($lists)){
			//更新静态注意事项：
			//1 创建目录文件夹--权限问题
			//2 栏目在根目录中
			//3 从缓存中抓取是最快的
			
			foreach($lists as $v){
				
				//检测htmlurl是否为空
				if(trim($v['htmlurl'])==''){
					//JsonReturn(['code'=>1,'msg'=>$modelname.'模块未绑定栏目，无法生存HTML！']);
					echo $modelname.'模块未绑定栏目，无法生存HTML！';exit;
				}
				
				
				//需要检测文件夹是否存在
				//创建文件夹
				if(!is_dir(APP_PATH.$_SESSION['web_path'])){
					$r = mkdir(APP_PATH.$_SESSION['web_path'],0777,true);
					if(!$r){
						//JsonReturn(['code'=>1,'msg'=>'根目录创建目录失败！']);
						echo '系统创建 [ '.str_replace('/','\\',APP_PATH.$_SESSION['web_path']).' ] 目录失败!';exit;
					}
					echo '创建目录 [ '.str_replace('/','\\',APP_PATH.$_SESSION['web_path']).' ] 成功！<br/>';
				}
				
				if(strpos($v['htmlurl'],'/')!==false){
					$filepath = explode('/',$v['htmlurl']);
					//array_pop($filepath);
					$dir = APP_PATH.$_SESSION['web_path'].implode('/',$filepath);
					$create_dir = APP_PATH.$_SESSION['web_path'];
					foreach($filepath as $vv){
						$create_dir.=$vv;
						if(!is_dir($create_dir)){
							
							$r = mkdir($create_dir,0777,true);
							if(!$r){
								//JsonReturn(['code'=>1,'msg'=>'根目录创建目录失败！']);
								echo '系统创建 [ '.str_replace('/','\\',$create_dir).' ] 目录失败!';exit;
							}
							echo '创建目录 [ '.str_replace('/','\\',$create_dir).' ] 成功！<br/>';
						}
						$create_dir.='/';
						
					}
				}else{
					if(!is_dir(APP_PATH.$_SESSION['web_path'].$v['htmlurl'])){
						$r = mkdir(APP_PATH.$_SESSION['web_path'].$v['htmlurl'],0777,true);
						if(!$r){
							//JsonReturn(['code'=>1,'msg'=>'根目录创建目录失败！']);
							echo '系统创建 [ '.str_replace('/','\\',APP_PATH.$_SESSION['web_path'].$v['htmlurl']).' ] 目录失败！';exit;
						}
					}
				}
				
				
				
				$url = APP_PATH.$v['htmlurl'].'/'.$v['id'].File_TXT;
				$filename = APP_PATH.$_SESSION['web_path'].$v['htmlurl'].'/'.$v['id'].File_TXT;
				$cache_url = APP_PATH.'cache/data/'.md5(frencode($url));
				if(file_exists($cache_url)){
				
					file_put_contents($filename,file_get_contents($cache_url,false,$context));
				}else{
					file_put_contents($filename,file_get_contents($www.'/'.$v['htmlurl'].'/'.$v['id'].File_TXT,false,$context));
				}
				echo '成功生成静态HTML [ '.str_replace('/','\\',$filename).' ] <br/>';
				
			}
		}
		
	}
	
	
	// 原目录，复制到的目录
	function recurse_copy($src,$dst) {  
	 
		$dir = opendir($src);
		@mkdir($dst);
		while(false !== ( $file = readdir($dir)) ) {
			if (( $file != '.' ) && ( $file != '..' )) {
				if ( is_dir($src . '/' . $file) ) {
					$this->recurse_copy($src . '/' . $file,$dst . '/' . $file);
				}
				else {
					copy($src . '/' . $file,$dst . '/' . $file);
				}
			}
		}
		closedir($dir);
	}

	
	
}