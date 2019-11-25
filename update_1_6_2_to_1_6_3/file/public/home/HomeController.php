<?php

// +----------------------------------------------------------------------
// | JiZhiCMS { 极致CMS，给您极致的建站体验 }  
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2099 http://www.jizhicms.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 留恋风 <2581047041@qq.com>
// +----------------------------------------------------------------------
// | Date：2019/01-2019/02
// +----------------------------------------------------------------------


namespace Home\c;

use FrPHP\lib\Controller;
use FrPHP\Extend\Page;
use FrPHP\Extend\ArrayPage;

class HomeController extends CommonController
{

	//首页
	function index(){
		//检查缓存
		$url = current_url();
		$cache_file = APP_PATH.'cache/data/'.md5(frencode($url));
		$this->cache_file = $cache_file;
		$this->start_cache($cache_file);
		$this->display($this->template.'/index');
		$this->end_cache($this->cache_file);

	}
	//栏目
	function jizhi(){
		//接收前台所有的请求
		$request_url = REQUEST_URI;
		$position = strpos($request_url,'?');
		$url = ($position!==FALSE) ? substr($request_url,0,$position) : $request_url;
		$url = substr($url,1,strlen($url)-1);
		
	
		if($url=='' || $url=='/' || $url=='index.php' || $url=='index'.File_TXT){
			$this->index();exit;
		}
		
		//检查缓存
		$cache_file = APP_PATH.'cache/data/'.md5(frencode($url));
		$this->cache_file = $cache_file;
		if(!$this->frparam('ajax')){
			$this->start_cache($cache_file);
		}
		//  news/123.html  news-123.html  news-list-123.html
		$url = str_ireplace(File_TXT,'',$url);
		
		if(!$this->webconf['islevelurl']){
			//没有开启URL层级
			if(strpos($url,'/')!==false){
				$urls = explode('/',$url);
				//内容详情页
				$html = $urls[0];
				$id = (int)$urls[1];
				$res = M('classtype')->find(array('htmlurl'=>$html));
			}else{
				
				
				//栏目页
				$this->frpage = $this->frparam('page',0,1);
				if(strpos($url,'-')!==false){
					//检测是否为分页
					$res = M('classtype')->find(array('htmlurl'=>$url));
					if(!$res){
						//存在分页,取最后一个字符串
						$html_x = explode('-',$url);
						$this->frpage = array_pop($html_x);
						if(!$this->frpage){
							$this->error('链接错误！');exit;
						}
						$html = implode('-',$html_x);//再次拼接
						$res = M('classtype')->find(array('htmlurl'=>$html));
						
					}else{
						//不是分页
						
					}
					
				}else{
					$html = $url;
					$res = M('classtype')->find(array('htmlurl'=>$html));
					
				}
			}
		
		}else{
			//开启URL层级
			//判断是否为栏目
			$html=$url;
			$res = M('classtype')->find(array('htmlurl'=>$html));
			if(!$res){
				
				if(strpos($url,'/')!==false){
					$urls = explode('/',$url);
					$urls_end = array_pop($urls);
					if(strpos($urls_end,'-')!==false){
						//分页
						//存在分页,取最后一个字符串
						$html_x = explode('-',$urls_end);
						$this->frpage = array_pop($html_x);
						if(!$this->frpage){
							$this->error('链接错误！');exit;
						}
						$urls[] = implode('-',$html_x);//再次拼接
						$html = implode('/',$urls);
						$res = M('classtype')->find(array('htmlurl'=>$html));
						
					}else{
						//可能是数字？
						$html = implode('/',$urls);
						$id = (int)$urls_end;
						if($id<0){
							$this->error('链接错误！');exit;
						}
						$res = M('classtype')->find(array('htmlurl'=>$html));
					}
					
					
					
					
					
				}else{
					//栏目页
					$this->frpage = $this->frparam('page',0,1);
					if(strpos($url,'-')!==false){
						//检测是否为分页
						//存在分页,取最后一个字符串
						$html_x = explode('-',$url);
						$this->frpage = array_pop($html_x);
						if(!$this->frpage){
							$this->error('链接错误！');exit;
						}
						$html = implode('-',$html_x);//再次拼接
						$res = M('classtype')->find(array('htmlurl'=>$html));
						
					}else{
						$html = $url;
						$res = M('classtype')->find(array('htmlurl'=>$html));
						
					}
				}
				
			}
			
			
		}
		
		
		
		
		if($res){
			$res['url'] = $this->classtypedata[$res['id']]['url'];
			$this->type =  $res;
			//检查授权
			if($res['gid']!=0){
				if(!$this->islogin){
					Redirect(U('Login/index'));
				}else{
					if($this->member['gid']<$res['gid']){
						Error('对不起，您没有访问权限！');
					}
				}
				
			}
			
			if(isset($id)){
				
				//默认是详情页-非详情页另做处理
				$this->id = $id;
				$this->jizhi_details($this->id);
				if(!$this->frparam('ajax')){
				$this->end_cache($this->cache_file);
				}
				
			}
			
			$sql = ' isshow=1 ';
			$molds = $res['molds'];
			$sql .= ' and tid in ('.implode(',',$this->classtypedata[$res['id']]['children']['ids']).') ';
			$page = new Page($molds);
			
			//手动设置分页条数
			$limit = $res['lists_num'];
			if($this->frparam('limit')){
				$limit = $this->frparam('limit');
			}
			//只适合article和product
			if($molds=='article' || $molds=='product'){
				//$sql.=" or (istop=1 and isshow=1)";
				$data = $page->where($sql)->orderby('istop desc,orders desc,addtime desc,id desc')->limit($limit)->page($this->frpage)->go();
			}else{
				$data = $page->where($sql)->orderby('orders desc,id desc')->limit($limit)->page($this->frpage)->go();
			}
			
			
			$pages = $page->pageList(3,'-');
			
			$this->pages = $pages;//组合分页
			
			foreach($data as $k=>$v){
				if(isset($v['htmlurl'])){
					$data[$k]['url'] = gourl($v['id'],$v['htmlurl']);
				}
				
			}
			
			$this->lists = $data;//列表数据
			$this->sum = $page->sum;//总数据
			$this->listpage = $page->listpage;//分页数组-自定义分页可用
			$this->prevpage = $page->prevpage;//上一页
			$this->nextpage = $page->nextpage;//下一页
			$this->allpage = $page->allpage;//总页数
			//清空screen筛选
			if(isset($_SESSION['screen'])){
				$_SESSION['screen'] = null;
			}
			$this->filters = [];
			
			if($this->frparam('ajax')){
				
				if($this->frparam('ajax_tpl')){
					$this->display($this->template.'/'.$res['molds'].'/ajax_list_'.$res['lists_html']);
					exit;
				}
				
				JsonReturn(['code'=>0,'data'=>$data,'type'=>$res,'sum'=>$this->sum,'allpage'=>$this->allpage]);
				
			}
			//面包屑导航
			$classtypetree = array_reverse($this->classtypetree);
			$isgo = false;
			$newarray = [];
			$parent = [];//标记父类
			$istop = false;
			foreach($classtypetree as $k=>$v){
				if($v['id']==$res['id'] && !$isgo){
					$isgo = true;
					$res['level'] = $v['level'];
					$newarray[]=$v;
				}
				if($v['id']==$res['id'] && $v['level']==0){
					break;
				}
				if($v['level']==0 && $v['id']!=$res['id'] && $v['id']!=$res['pid']){
					if(!$istop && $isgo && $parent['level']!=0){
						$newarray[]=$v;
						$istop = true;
					}
					$isgo = false;
				}
				if($isgo &&  $v['id']!=$res['id'] && $res['level']>$v['level']){
					if(isset($parent['pid'])){
						if($parent['level']!=$v['level']){
							$newarray[]=$v;
						}
						
					}else{
						$newarray[]=$v;
					}
					$parent = $v;
				}
			}
			$newarray2 = array_reverse($newarray);
			$positions='<a href="'.get_domain().'">首页</a>';
			foreach($newarray2 as $v){
				$positions.='  &gt;  <a href="'.$v['url'].'">'.$v['classname'].'</a>';
			}
			
			$this->positions_data = $newarray2;
			$this->positions = $positions;
			
			$this->display($this->template.'/'.$res['molds'].'/'.$res['lists_html']);
			if(!$this->frparam('ajax')){
			$this->end_cache($this->cache_file);
			}

			
		}else{
			
			//进入自定义页面
			/**
				规定自定义页面的文件名跟访问的URL名相同，存放在page文件夹下面
			
			**/
			//html
			$url = ($position!==FALSE) ? substr($request_url,0,$position) : $request_url;
			$url = substr($url,1,strlen($url)-1);
			$html = str_ireplace(File_TXT,'',$url);
			$filepath = APP_PATH.APP_HOME.'/'.HOME_VIEW.'/'.$this->template.'/page/'.$html.'.html';
			if(file_exists($filepath)){
				$this->display($this->template.'/page/'.$html);
				exit;
			}
			
			//错误页面->404
			$this->error('输入url错误！');
			exit;
		}
		
		
		
				
	}
	//自由定义链接
	function auto_url(){
		$html = $this->frparam('html',1);
		$id = $this->frparam('id');
		$tid = $this->frparam('tid');
		$isclass = true;
		//检查缓存
		$url = REQUEST_URI;
		$cache_file = APP_PATH.'cache/data/'.md5(frencode($url));
		$this->cache_file = $cache_file;
		if(!$this->frparam('ajax')){
		$this->start_cache($cache_file);
		}
		if($id && $html){
			//详情页+html
			$res = M('classtype')->find(array('htmlurl'=>$html));
			if(!$res){ $this->error('链接错误！');}
			$isclass = false;
		}else if($id && $tid){
			//详情页
			$res = M('classtype')->find(array('id'=>$tid));
			if(!$res){ $this->error('链接错误！');}
			$isclass = false;
		}else if($tid){
			//栏目页
			$res = M('classtype')->find(array('id'=>$tid));
			if(!$res){ $this->error('链接错误！');}
			
		}else{
			//html只有栏目页
			$res = M('classtype')->find(array('htmlurl'=>$html));
			if(!$res){ $this->error('链接错误！');}
			
		}
		
		$res['url'] = $this->classtypedata[$res['id']]['url'];
		$this->type = $res;
		//检查授权
		if($res['gid']!=0){
			if(!$this->islogin){
				Redirect(U('Login/index'));
			}else{
				if($this->member['gid']<$res['gid']){
					Error('对不起，您没有访问权限！');
				}
			}
			
		}
		if($isclass){
			$sql = ' isshow=1 ';
			$molds = $res['molds'];
			$sql .= ' and tid in ('.implode(',',$this->classtypedata[$res['id']]['children']['ids']).') ';
			$page = new Page($molds);
			
			//手动设置分页条数
			$limit = $res['lists_num'];
			if($this->frparam('limit')){
				$limit = $this->frparam('limit');
			}
			
			//只适合article和product
			if($molds=='article' || $molds=='product'){
				$sql.=" or (istop=1 and isshow=1) ";
			
				$data = $page->where($sql)->orderby('istop desc,orders desc,id desc')->limit($limit)->page($this->frpage)->go();
			}else{
				$data = $page->where($sql)->orderby('orders desc,id desc')->limit($limit)->page($this->frpage)->go();
			}
			
			
			$pages = $page->pageList(3,'-');
			$this->pages = $pages;//组合分页
			foreach($data as $k=>$v){
				if(isset($v['htmlurl'])){
					$data[$k]['url'] = gourl($v['id'],$v['htmlurl']);
				}
				
			}
			$this->lists = $data;//列表数据
			$this->sum = $page->sum;//总数据
			$this->listpage = $page->listpage;//分页数组-自定义分页可用
			$this->prevpage = $page->prevpage;//上一页
			$this->nextpage = $page->nextpage;//下一页
			$this->allpage = $page->allpage;//总页数
			//清空screen筛选
			if(isset($_SESSION['screen'])){
				$_SESSION['screen'] = null;
			}
			$this->filters = [];
			
			if($this->frparam('ajax')){
				
				if($this->frparam('ajax_tpl')){
					$this->display($this->template.'/'.$res['molds'].'/ajax_list_'.$res['lists_html']);
					exit;
				}
				
				JsonReturn(['code'=>0,'data'=>$data,'type'=>$res,'sum'=>$this->sum,'allpage'=>$this->allpage]);
				
			}
			//面包屑导航
			$classtypetree = array_reverse($this->classtypetree);
			$isgo = false;
			$newarray = [];
			$parent = [];//标记父类
			$istop = false;
			foreach($classtypetree as $k=>$v){
				if($v['id']==$res['id'] && !$isgo){
					$isgo = true;
					$res['level'] = $v['level'];
					$newarray[]=$v;
				}
				if($v['id']==$res['id'] && $v['level']==0){
					break;
				}
				if($v['level']==0 && $v['id']!=$res['id'] && $v['id']!=$res['pid']){
					if(!$istop && $isgo && $parent['level']!=0){
						$newarray[]=$v;
						$istop = true;
					}
					$isgo = false;
				}
				if($isgo &&  $v['id']!=$res['id'] && $res['level']>$v['level']){
					if(isset($parent['pid'])){
						if($parent['level']!=$v['level']){
							$newarray[]=$v;
						}
						
					}else{
						$newarray[]=$v;
					}
					$parent = $v;
				}
			}
			$newarray2 = array_reverse($newarray);
			$positions='<a href="'.get_domain().'">首页</a>';
			foreach($newarray2 as $v){
				$positions.='  &gt;  <a href="'.$v['url'].'">'.$v['classname'].'</a>';
			}
			
			$this->positions_data = $newarray2;
			$this->positions = $positions;
			
			$this->display($this->template.'/'.$res['molds'].'/'.$res['lists_html']);
			if(!$this->frparam('ajax')){
			$this->end_cache($this->cache_file);
			}
			
		}else{
			
			//默认是详情页-非详情页另做处理
			$this->id = $id;
			$this->jizhi_details($this->id);
			if(!$this->frparam('ajax')){
			$this->end_cache($this->cache_file);
			}
			
		}
		
		
		
	}
	
	
	
	//详情
	function jizhi_details($id){
		
		if(!$id){
			$this->error('缺少ID！');
		}
		
		$details = M($this->type['molds'])->find(array('id'=>$id,'isshow'=>1));
		if(!$details){
			$this->error('未找到相应内容！');
			exit;
		}
		$details['url'] = gourl($details['id'],$details['htmlurl']);
		
		//body
		if(array_key_exists('body',$details)){
			$con = $details['body'];
			$sql = " isshow=1 and (url!='' or newname!='') "; 
			$tags = M('tags')->findAll($sql);
			if($tags){
				foreach($tags as $v){
					// $name = 'CMS';
					// $newname="极致CMS";
					// $url = 'http://www.jizhicms.com';
					// $num = 30;
					$url = $v['url'];
					$num = $v['num'];
					$target = $v['target'];
					$name = $v['keywords'];
					$newname = $v['newname']!='' ? $v['newname'] : $name;
					if($url!=''){
						$astr = "<a href='".$url."' target='".$target."' title='".$newname."'><strong>".$newname."</strong></a>";
					}else{
						$astr = $newname;
					}
					
					$con = preg_replace( '|(<img\b[^>]*?)('.$name.')([^>]*?\=)([^>]*?)('.$name.')([^>]*?>)|U', '$1%&&&&&%$3$4%&&&&&%$6', $con);
					$con = preg_replace( '|(<img\b[^>]*?)('.$name.')([^>]*?>)|U', '$1%&&&&&%$3', $con);
					$con = preg_replace( '|(<a\b[^>]*?)('.$name.')([^>]*?>)(<[^<]*?)('.$name.')([^>]*?>)|U', '$1%&&&&&%$3$4%&&&&&%$6', $con);

					$con = str_replace_limit($name, $astr, $con, $num);
					$con = str_replace('%&&&&&%', $newname, $con);
					
					
				}
				
			}
			
			$details['body'] = $con;
			
		}
		
		
		$this->jz = $details;
		
		$aprev_sql = ' id<'.$id.' and tid in ('.implode(',',$this->classtypedata[$this->type['id']]['children']['ids']).') ';
		$anext_sql = ' id>'.$id.' and tid in ('.implode(',',$this->classtypedata[$this->type['id']]['children']['ids']).') ';
		$aprev = M($this->type['molds'])->find($aprev_sql,'id desc');
		$anext = M($this->type['molds'])->find($anext_sql,'id desc');
		if($aprev){
			$aprev['url'] = gourl($aprev['id'],$aprev['htmlurl']);
		}
		if($anext){
			$anext['url'] = gourl($anext['id'],$anext['htmlurl']);
		}
		$this->aprev = $aprev;
		$this->anext = $anext;
		
		//面包屑导航
		$classtypetree = array_reverse($this->classtypetree);
		$isgo = false;
		$newarray = [];
		$parent = [];//标记父类
		$istop = false;
		foreach($classtypetree as $k=>$v){
			if($v['id']==$this->type['id'] && !$isgo){
				$isgo = true;
				$this->type['level'] = $v['level'];
				$newarray[]=$v;
			}
			if($v['id']==$this->type['id'] && $v['level']==0){
				break;
			}
			if($v['level']==0 && $v['id']!=$this->type['id'] && $v['id']!=$this->type['pid']){
				if(!$istop && $isgo && $parent['level']!=0){
					$newarray[]=$v;
					$istop = true;
				}
				$isgo = false;
			}
			if($isgo &&  $v['id']!=$this->type['id'] && $this->type['level']>$v['level']){
				
				if(isset($parent['pid'])){
					if($parent['level']!=$v['level']){
						$newarray[]=$v;
					}
					
				}else{
					$newarray[]=$v;
				}
				$parent = $v;
				
			}
		}
		$newarray2 = array_reverse($newarray);
		$positions='<a href="'.get_domain().'">首页</a>';
		foreach($newarray2 as $v){
			$positions.='  &gt;  <a href="'.$v['url'].'">'.$v['classname'].'</a>';
		}
		
		$this->positions_data = $newarray2;
		$this->positions = $positions;
		
		if($this->frparam('ajax')){
			JsonReturn(['code'=>0,'jz'=>$details,'prev'=>$aprev,'next'=>$anext]);
		}
		
		$this->display($this->template.'/'.$this->type['molds'].'/'.$this->type['details_html']);
		
	}
	
	
	//搜索--单一模块搜索
	function search(){
		$tables = explode('|',$this->webconf['search_table']);
		$molds = $this->frparam('molds',1);//搜索的模块
		$tid = $this->frparam('tid',1);
		if(in_array($molds,$tables) && $molds!=''){
			$word = $this->frparam('word',1);
			if($word==''){
				if($this->frparam('ajax')){
					JsonReturn(['code'=>1,'data'=>'','msg'=>'请输入关键词搜索！']);
				}
				Error('请输入关键词搜索！');
				
			}
			$this->word = $word;
			$sql =  " isshow=1 and title like '%".$word."%'  ";
			if($tid){
				$sql.=' and tid in('.$tid.') ';
			}
			
			$page = new Page($molds);
			$page->typeurl = 'search';
			$data = $page->where($sql)->orderby('id desc')->limit(15)->page($this->frparam('page',0,1))->go();
			$pages = $page->pageList(3,'&page=');
			
			$this->pages = $pages;//组合分页
			
			foreach($data as $k=>$v){
				$data[$k]['url'] = gourl($v['id'],$v['htmlurl']);
				$data[$k]['title'] = str_replace($word,'<b style="color:#f00">'.$word.'</b>',$v['title']);
			}
			
			$this->lists = $data;//列表数据
			$this->sum = $page->sum;//总数据
			$this->listpage = $page->listpage;//分页数组-自定义分页可用
			$this->prevpage = $page->prevpage;//上一页
			$this->nextpage = $page->nextpage;//下一页
			$this->allpage = $page->allpage;//总页数
		
			if($this->frparam('ajax')){
				if($this->frparam('ajax_tpl')){
					$this->display($this->template.'/ajax_search_list');
					exit;
				}
				JsonReturn(['code'=>0,'data'=>$data,'msg'=>'success']);
			}
		
			$this->display($this->template.'/search');
			
			
		}else{
			if($this->frparam('ajax')){
				JsonReturn(['code'=>1,'data'=>'','msg'=>'搜索超出设定范围！']);
			}
			Error('搜索超出设定范围！');
		}

	}
	//多模块搜索
	function searchAll(){
		$tables = explode('|',$this->webconf['search_table']);
		$molds = $this->frparam('molds',2);//搜索的模块
		$tid = $this->frparam('tid',1);
		if($molds && is_array($molds)){
			$allow_table = [];
			foreach($molds as $v){
				if(in_array($v,$tables)){
					$allow_table[]=$v;
				}
			}
			if(count($allow_table)==0){
				if($this->frparam('ajax')){
					JsonReturn(['code'=>1,'data'=>'','msg'=>'您的搜索超出设定范围！']);
				}
				Error('您的搜索超出设定范围！');
			}
			
			$word = $this->frparam('word',1);
			if($word==''){
				if($this->frparam('ajax')){
					JsonReturn(['code'=>1,'data'=>'','msg'=>'请输入关键词搜索！']);
				}
				Error('请输入关键词搜索！');
			}
			$this->word = $word;
			$sql =  "  isshow=1 and title like '%".$word."%'  ";
			if($tid){
				$sql.=' and tid in('.$tid.') ';
			}
		
			$lists = [];
			foreach($allow_table as $v){
				$list_a = M($v)->findAll($sql);
				if($list_a){
					$lists = count($lists)>0 ? array_merge($lists,$list_a) : $list_a;
				}
			}
			
			$arraypage = new \ArrayPage($lists);
			$data = $arraypage->setPage(['limit'=>10])->go();
			
			foreach($data as $k=>$v){
				$data[$k]['url'] = gourl($v['id'],$v['htmlurl']);
				$data[$k]['classname'] = $this->classtypedata[$v['tid']]['classname'];
				$data[$k]['title'] = str_replace($word,'<b style="color:#f00">'.$word.'</b>',$v['title']);
			}
			$this->lists = $data;
			$this->pages = $arraypage->pageList();
			if($this->frparam('ajax')){
				if($this->frparam('ajax_tpl')){
					$this->display($this->template.'/ajax_searchall_list');
					exit;
				}
				JsonReturn(['code'=>0,'data'=>$data]);
			}
			$this->display($this->template.'/searchall');
			
			
		}else{
			if($this->frparam('ajax')){
				JsonReturn(['code'=>1,'data'=>'','msg'=>'请输入关键词搜索！']);
			}
			Error('请输入关键词搜索！');
			
		}

	}
	

	
	//错误页面
	function error($msg){
		//echo $msg;
		$this->display($this->template.'/404');
	}
	
	//开启检查缓存
	function start_cache($cache_file){
		$cache_file = $cache_file.'_'.$this->template.'.php';
		if($this->webconf['iscachepage']==1){
			if(file_exists($cache_file)){
				
				//获取当前时间戳
				$now_time = time();
				//获取缓存文件时间戳
				$last_time = filemtime($cache_file);
				//如果缓存文件生成超过指定的时间直接删除文件
				if((($now_time - $last_time)/60)>webConf('cache_time')){
					unlink($cache_file);
				}else{
					//有缓存文件直接调用
					$content =  file_get_contents($cache_file);
					echo substr($content,14);
					exit;
				}
				
				
			}
		}
	
		//开启缓存
		ob_start();
	}
	//结束缓存
	function end_cache($cache_file){
		$cache_file = $cache_file.'_'.$this->template.'.php';
		
		//获取缓存
		$content = ob_get_contents();
		
		if($this->webconf['isautohtml']==1){
			$filepath = substr($_SERVER["REQUEST_URI"],1,strlen($_SERVER["REQUEST_URI"])-1);
			$file = APP_PATH.$filepath;
			if(strpos($filepath,'/')!==false){
				$filepath = explode('/',$filepath);
				array_pop($filepath);
				$create_dir = APP_PATH;
				foreach($filepath as $vv){
					$create_dir.=$vv;
					if(!is_dir($create_dir)){
						$r = mkdir($create_dir,0777,true);
						if(!$r){
							echo '系统创建 [ '.str_replace('/','\\',$create_dir).' ] 目录失败!';exit;
						}
						
					}
					$create_dir.='/';
					
				}
				
				
			}
			if(strpos($file,'.html')===false){
				$file.='index.html';
			}
			
			
			file_put_contents($file,$content);
			
			
		}
		if($this->webconf['iscachepage']==1){
			//写入到缓存内容到指定的文件夹
			$content ='<?php die();?>'.$content;
			$fp = fopen($cache_file,'w');
			fwrite($fp,$content);
			fclose($fp);
		}
		ob_flush();  
		flush();
		ob_end_clean(); 
		
		
		exit;
	}
	
	

	

}