<?php

// +----------------------------------------------------------------------
// | JiZhiCMS { 极致CMS，给您极致的建站体验 }  
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2099 http://www.jizhicms.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 留恋风 <2581047041@qq.com>
// +----------------------------------------------------------------------
// | Date：2019/09/20
// +----------------------------------------------------------------------


namespace Home\plugins;

use Home\c\CommonController;
use FrPHP\lib\Controller;
use FrPHP\Extend\Page;

class WebApiController extends CommonController
{
	

	function index(){
		$pw = $this->frparam('pw',1);
		
		//检测是否开启插件
		//检测是否关闭插件或者卸载插件
		$w['filepath'] = 'webapi';
		$w['isopen'] = 1;
		$res = M('plugins')->find($w);
		if($res){
			$config = json_decode($res['config'],1);
			if(!$config && $pw!=$config['password']){
				echo '登录密码错误';exit;
			}
			$tid = $this->frparam('tid');
			if(!$tid){
				echo '栏目不能为空';
				exit;
			}
			$type = $this->classtypedata[$tid];
			switch($type['molds']){
				case 'article':
				$data = $this->frparam();
				$data['seo_title'] = ($this->frparam('seo_title',1,'')=='')?$this->frparam('title',1,''):$this->frparam('seo_title',1,'');
				$data['addtime'] = strtotime($data['addtime']);//格式化时间
				$data['body'] = $this->frparam('body',4);
				$data['userid'] = $this->frparam('userid',1,'1');
				$data['description'] = ($this->frparam('description',1,'')=='') ? newstr(strip_tags($data['body']),200) : $this->frparam('description',1);
				if($this->frparam('litpic',1,'')==''){
					$pattern='/<img.+src=\"?(.+\.(jpg|gif|bmp|bnp|PNG))\"?.+>/i';
					if($this->frparam('body',1,'')!=''){
						preg_match_all($pattern,$_POST['body'],$matchContent);
					
						if(isset($matchContent[1][0])){
							$data['litpic'] = $matchContent[1][0];
						}else{
							$data['litpic'] = '';
						}
					}else{
						$data['litpic'] = '';
					}
					
				}
				
				$pclass = $type;
				$data['molds'] = $pclass['molds'];
				$data['htmlurl'] = $pclass['htmlurl'];
				$data = get_fields_data($data,'article');
				
				if(M('Article')->add($data)){
					echo 0;
				}else{
					echo '录入数据库失败';
				}
				
				
				break;
				
				case 'product':
				$data = $this->frparam();
				$data['seo_title'] = ($this->frparam('seo_title',1,'')=='')?$this->frparam('title',1,''):$this->frparam('seo_title',1,'');
				$data['addtime'] = strtotime($data['addtime']);
				$data['body'] = $this->frparam('body',4,'');
				$data['price'] = $this->frparam('price',3,'0');
				$data['userid'] = $this->frparam('userid',1,'1');
				$data['description'] = ($this->frparam('description',1,'')=='') ? newstr(strip_tags($data['body']),200) : $this->frparam('description',1);
				if($this->frparam('litpic',1,'')==''){
					$pattern='/<img.+src=\"?(.+\.(jpg|gif|bmp|bnp|PNG))\"?.+>/i';
					if($this->frparam('body',1)!=''){
						preg_match_all($pattern,$_POST['body'],$matchContent);
					
						if(isset($matchContent[1][0])){
							$data['litpic'] = $matchContent[1][0];
						}else{
							$data['litpic'] = '';
						}
					}else{
						$data['litpic'] = '';
					}
					
				}
				if(array_key_exists('pictures_urls',$data) && $data['pictures_urls']!=''){
					 $data['pictures'] = implode('||',format_param($data['pictures_urls'],2));
				}else{
					$data['pictures'] = '';
				}
				
				
				$pclass = $type;
				$data['molds'] = $pclass['molds'];
				$data['htmlurl'] = $pclass['htmlurl'];
				$data = get_fields_data($data,'product');
				if(M('product')->add($data)){
					echo 0;
				}else{
					echo '录入数据库失败';
				}
				
				break;
				
				case 'classtype':
					
					$htmlurl = $this->frparam('htmlurl',1);
					$first = $this->frparam('first',0,1);
					if($htmlurl==''){
						if($first){
							$htmlurl = str_replace(' ','',pinyin($this->frparam('classname',1),'first'));
						}else{
							$htmlurl = str_replace(' ','',pinyin($this->frparam('classname',1)));
						}
						
					}
					$w['pid'] = $this->frparam('pid',0,0);
					$w['orders'] = $this->frparam('orders',0,0);
					$w['classname'] = $this->frparam('classname',1,'');
					$w['molds'] = $this->frparam('molds',1,'');
					$w['description'] = $this->frparam('description',1,'');
					$w['keywords'] = $this->frparam('keywords',1,'');
					$w['litpic'] = $this->frparam('litpic',1,'');
					$w['body'] = $this->frparam('body',4,'');
					$w['htmlurl'] = $htmlurl;
					$w['iscover'] = $this->frparam('iscover',0,0);
					$w['lists_html'] = $this->frparam('lists_html',1,'');
					$w['details_html'] = $this->frparam('details_html',1,'');
					$w['lists_num'] = $this->frparam('lists_num',0,10);
					if($w['lists_html']=='' && $w['details_html']==''){
						$parent = M('classtype')->find(array('id'=>$w['pid']));
						if($parent['iscover']==1){
							$w['lists_html']=$parent['lists_html'];
							$w['details_html']=$parent['details_html'];
							$w['lists_num']=$parent['lists_num'];
						}
					}
					
					
					$data = $this->frparam();
					$data = get_fields_data($data,'classtype');
					$w = array_merge($data,$w);
					$a = M('classtype')->add($w);
					if($a){
						setCache('classtypetree',null);
						echo 0;
					}else{
						echo '录入数据库失败';
					}
				
				break;
				
				
				default:
					$data = $this->frparam();
					$pclass = $type;
					$data['htmlurl'] = $pclass['htmlurl'];
					$data = get_fields_data($data,$molds);
					if(M($molds)->add($data)){
						echo 0;
					}else{
						echo '录入数据库失败';
					}
					
					
				
				break;
				
			}
			
			
			
		}else{
			echo '插件未开启';
		}
		
		
		
		
		
		
	}
	
	function getClass(){
		$pw = $this->frparam('pw',1);
		$w['filepath'] = 'webapi';
		$w['isopen'] = 1;
		$res = M('plugins')->find($w);
		if($res){
			$config = json_decode($res['config'],1);
			if(!$config && $pw!=$config['password']){
				echo '登录密码错误';exit;
			}
			$lists = M('classtype')->findAll();
			foreach($lists as $v){
				
				echo '<option value="'.$v['id'].'">'.$v['classname'].'</option>';
			}
		}else{
			echo '插件未开启';
		}
		
		
	}
	
	
}