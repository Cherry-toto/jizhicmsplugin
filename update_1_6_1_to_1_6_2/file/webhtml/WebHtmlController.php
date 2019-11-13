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
		
		$maxlimit = 500;
		$sleep = 2;//最小填0，立即跳转。

		if($_POST){
			//检查插件是否开启
			if(!M('plugins')->find(['filepath'=>'webhtml','isopen'=>1])){
				JsonReturn(['code'=>1,'msg'=>'您还未开启插件，不能使用！']);
				exit;
			}
			
			
			$path = $this->frparam('path',1,false);
			if(!$path || $path=='' || $path=='/'){
				JsonReturn(['code'=>1,'msg'=>'请输入存储文件夹名称！']);
				exit;
			}
			if($this->webconf['pc_html']!='/' && $this->webconf['pc_html']!=''){
			JsonReturn(['code'=>1,'msg'=>'电脑静态目录必须为[ / ]，请先修改电脑静态目录！']);
				exit;
			}
			$_SESSION['web_path'] = $path.'/';
			
			//创建目录
			if(!is_dir(APP_PATH.$path)){
				$r = mkdir(APP_PATH.$path,0777,true);
				if(!$r){
					JsonReturn(['code'=>1,'msg'=>'系统创建 [ '.str_replace('/','\\',APP_PATH.$path).' ] 目录失败!']);
					exit;
				}
			}
			
			
			if(!is_dir(APP_PATH.$path.'/static')){
				$r = mkdir(APP_PATH.$path.'/static',0777,true);
				if(!$r){
					JsonReturn(['code'=>1,'msg'=>'系统创建 [ '.str_replace('/','\\',APP_PATH.$path.'/static').' ] 目录失败!']);
					exit;
				}
			}
			
			$dst = APP_PATH.$path.'/static/'.$this->webconf['pc_template'];
			if(!is_dir($dst)){
				$r = mkdir($dst,0777,true);
				if(!$r){
					JsonReturn(['code'=>1,'msg'=>'系统创建 [ '.str_replace('/','\\',$dst).' ] 目录失败!']);
					exit;
				}
			}
			$src = APP_PATH.'static/'.$this->webconf['pc_template'];
			//移动静态资源
			//echo '系统正在移动静态资源到指定目录 [ '.APP_PATH.$path.' ] <br/>';
			$this->recurse_copy($src,$dst);
			
			
			$type = $this->frparam('type');
			setCache('web_html_cache',null);
			
			$classtypedata = classTypeData();
		
			foreach($classtypedata as $k=>$v){
				$classtypedata[$k]['children'] = get_children($v,$classtypedata);
			}
			$urls = [];
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
						//echo '正在进行栏目静态HTML生成，请稍后……<br/>';
						
						$urls = $this->html_classtype($sql);
						$urls[]= ['url'=>$www,'html'=>APP_PATH.$_SESSION['web_path'].'index.html'];
						setCache('web_html_cache',$urls,86400);
						
						JsonReturn(['code'=>0,'msg'=>'success']);
						exit;
					break;
					//文章商品模块是同样的
					default:
						if($tid){
							$sql.=' and tid in('.implode(",",$classtypedata[$tid]["children"]["ids"]).') ' ;
						}
						
						$urls = $this->html_molds($model,$sql);
						$urls[]= ['url'=>$www,'html'=>APP_PATH.$_SESSION['web_path'].'index.html'];
						setCache('web_html_cache',$urls,86400);
						JsonReturn(['code'=>0,'msg'=>'success']);
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
							
							$urls1 = $this->html_classtype($sql);
							$urls = count($urls1)>0 ? array_merge($urls,$urls1) : $urls;
						}else{
							if($tid[$k]){
								$sql.=' and tid in('.implode(",",$classtypedata[$tid[$k]]["children"]["ids"]).') ';
							}
							
							$urls2 = $this->html_molds($v,$sql);
							$urls = count($urls2)>0 ? array_merge($urls,$urls2) : $urls;
						}
						
						
					}

					$urls[]= ['url'=>$www,'html'=>APP_PATH.$_SESSION['web_path'].'index.html'];
					setCache('web_html_cache',$urls,86400);
					JsonReturn(['code'=>0,'msg'=>'success']);
					exit;
					
					
				}
				
				
				
				
			}
			
			
		}
		
		$www = get_domain();
		//是否有静态HTML更新
		$tohtmlurl = getCache('web_html_cache');
		if($tohtmlurl){
			$opts = array(
			  'http'=>array(
				'method'=>"GET",
				'header'=>"Cookie: PHPSESSID=".$_COOKIE['PHPSESSID']."\r\n"
			  )
			);

			$context = stream_context_create($opts);
			$max = count($tohtmlurl);
			$start_time = getCache('start_time');
			if(!$start_time){
				$start_time = time();
				setCache('start_time',$start_time,86400);
				setCache('allpage',$max);
			}

			$count = 0;
			foreach ($tohtmlurl as $key => $value) {
				if($key<$maxlimit){
					$r = file_put_contents($value['html'],file_get_contents($value['url'],false,$context));
					if(!$r){
						echo $value['html'].'生成失败！<br/>';
					}else{
						echo $value['html'].'生成成功！<br/>';
					}
					$count++;
				}else{
					$tohtmlurl = array_slice($tohtmlurl,$maxlimit);
					setCache('web_html_cache',$tohtmlurl,86400);
					echo '已生成一部分页面，请不要关闭当前页面，还需要继续生成HTML~';
					echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><meta http-equiv="refresh" content="'.$sleep.';URL='.U('index').'">';
					exit;
				}
				
			}
			if($count>=$max){
				setCache('web_html_cache',false);
				echo '页面已全部生成完毕！<br/>';
				$end_time = time();
				$start_time = getCache('start_time');
				$allpage = getCache('allpage');
				echo '总共生成页面数：'.$allpage.' 每次生成页面数：'.$maxlimit.',停顿时间：'.$sleep.'秒,开始时间：'.date('Y-m-d H:i:s',$start_time).' ,结束时间：'.date('Y-m-d H:i:s',$end_time).', 总共花费时间：'.($end_time-$start_time).'秒';
				setCache('start_time',false);
				setCache('allpage',false);
				
				exit;
			}else{

			}
		
			
			
		}
		
		
		
		$this->display('webhtml');
	}
	
	
	function html_classtype($sql,$limit=null){
		
		
		$www = get_domain();
		$lists = M('classtype')->findAll($sql,' id asc ',null,$limit);
		
		$classtypedata = classTypeData();
		foreach($classtypedata as $k=>$v){
			$classtypedata[$k]['children'] = get_children($v,$classtypedata);
		}
		$urls = [];
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
						
						JsonReturn(['code'=>1,'msg'=>'系统创建 [ '.str_replace('/','\\',APP_PATH.$_SESSION['web_path']).' ] 目录失败!']);
					}
					
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
								JsonReturn(['code'=>1,'msg'=>'系统创建['.str_replace('/','\\',$create_dir).']目录失败!']);
								
							}
							
						}
						$create_dir.='/';
						
					}
				}
				
				
				
				$url = APP_PATH.$filename;
				$file_url = APP_PATH.$_SESSION['web_path'].$filename;

				$urls[]=['url'=>$www.'/'.$filename,'html'=>$file_url];

				
				//检查分页
				$sql = 'tid in('.implode(",",$classtypedata[$v['id']]["children"]["ids"]).') ';
				$count = M($v['molds'])->getCount($sql);
				$pagenum = ceil($count/$v['lists_num']);
				if($pagenum>1){
					
					for($i=1;$i<=$pagenum;$i++){
						$filename = $v['htmlurl'].'-'.$i.File_TXT;
						$url = APP_PATH.$filename;
						$file_url = APP_PATH.$_SESSION['web_path'].$filename;
						$urls[]=['url'=>$www.'/'.$filename,'html'=>$file_url];

						
					}
					
				}
					
				
			}
		}
		return $urls;
		
	}
	
	function html_molds($model,$sql=null,$limit=null){
		$modelname = get_info_table('molds',['biaoshi'=>$model],'name');
		
		
		$lists = M($model)->findAll($sql,' id asc ',null,$limit);
		$www = get_domain();
		$urls = [];
		if($lists && is_array($lists)){
		
			foreach($lists as $v){
				
				//检测htmlurl是否为空
				if(trim($v['htmlurl'])==''){
					JsonReturn(['code'=>1,'msg'=>$modelname.'模块未绑定栏目，无法生存HTML！']);
					
				}
				
				
				//需要检测文件夹是否存在
				//创建文件夹
				if(!is_dir(APP_PATH.$_SESSION['web_path'])){
					$r = mkdir(APP_PATH.$_SESSION['web_path'],0777,true);
					if(!$r){
						JsonReturn(['code'=>1,'msg'=>'系统创建 [ '.str_replace('/','\\',APP_PATH.$_SESSION['web_path']).' ] 目录失败!']);
					}
					
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
								JsonReturn(['code'=>1,'msg'=>'系统创建 [ '.str_replace('/','\\',$create_dir).' ] 目录失败!']);
								
							}
							
						}
						$create_dir.='/';
						
					}
				}else{
					if(!is_dir(APP_PATH.$_SESSION['web_path'].$v['htmlurl'])){
						$r = mkdir(APP_PATH.$_SESSION['web_path'].$v['htmlurl'],0777,true);
						if(!$r){
							JsonReturn(['code'=>1,'msg'=>'系统创建 [ '.str_replace('/','\\',APP_PATH.$_SESSION['web_path'].$v['htmlurl']).' ] 目录失败！']);
							
						}
					}
				}
				
				
				
				$url = APP_PATH.$v['htmlurl'].'/'.$v['id'].File_TXT;
				$filename = APP_PATH.$_SESSION['web_path'].$v['htmlurl'].'/'.$v['id'].File_TXT;
				$urls[]=['url'=>$www.'/'.$v['htmlurl'].'/'.$v['id'].File_TXT,'html'=>$filename];
				
				
			}
		}
		return $urls;
		
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