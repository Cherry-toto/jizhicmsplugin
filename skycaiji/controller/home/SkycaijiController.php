<?php

// +----------------------------------------------------------------------
// | JiZhiCMS { 极致CMS，给您极致的建站体验 }  
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2099 http://www.jizhicms.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 留恋风 <2581047041@qq.com>
// +----------------------------------------------------------------------
// | Date：2019/11/18
// +----------------------------------------------------------------------


namespace Home\plugins;

use Home\c\CommonController;
use FrPHP\lib\Controller;
use FrPHP\Extend\Page;

class SkycaijiController extends CommonController
{
	
//http://www.demo.mm/Skycaiji/index?pw=123456
	function index(){
		$pw = $this->frparam('pw',1);

		$w['filepath'] = 'skycaiji';
		$w['isopen'] = 1;
		$res = M('plugins')->find($w);
		if($res){
			$config = json_decode($res['config'],1);
			if(!$config && $pw!=$config['password']){
				
				JsonReturn(['id'=>0,'target'=>'','desc'=>'','error'=>'登录密码错误']);
			}
			$tid = $this->frparam('tid');
			if(!$tid){
				JsonReturn(['id'=>0,'target'=>'','desc'=>'','error'=>'栏目不能为空']);
				
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

				//检测是否开启伪原创
				if($config['iswyc']==1){
					$data['title'] = $this->fy($data['title']);
					$data['seo_title'] = $this->fy($data['seo_title']);
					if(isset($data['keywords'])){
						$data['keywords'] = $this->fy($data['keywords']);
					}
					$data['body'] = $this->fy($data['body']);

				}
				

				$r = M('Article')->add($data);
				if($r){
					JsonReturn(['id'=>$r,'target'=>gourl($r,$data['htmlurl']),'desc'=>'标题：'.$data['title'],'error'=>'测试成功！']);

				}else{
					JsonReturn(['id'=>0,'target'=>'','desc'=>'','error'=>'测试失败！']);
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
				$r = M('product')->add($data);
				if($r){
					JsonReturn(['id'=>$r,'target'=>gourl($r,$data['htmlurl']),'desc'=>'标题：'.$data['title'],'error'=>'测试成功！']);

				}else{
					JsonReturn(['id'=>0,'target'=>'','desc'=>'','error'=>'测试失败！']);
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
					$r = M('classtype')->add($w);
					if($r){
						setCache('classtypetree',null);
						JsonReturn(['id'=>$r,'target'=>get_domain().'/'.$w['htmlurl'],'desc'=>'栏目名：'.$data['classname'],'error'=>'测试成功！']);

					}else{
						JsonReturn(['id'=>0,'target'=>'','desc'=>'','error'=>'测试失败！']);
					}
				
				break;
				
				
				default:
					$data = $this->frparam();
					$pclass = $type;
					$data['htmlurl'] = $pclass['htmlurl'];
					$data = get_fields_data($data,$molds);
					$r = M($molds)->add($data);
					
					if($r){
						JsonReturn(['id'=>$r,'target'=>get_domain().'/'.$data['htmlurl'].'/'.$r,'desc'=>'','error'=>'测试成功！']);

					}else{
						JsonReturn(['id'=>0,'target'=>'','desc'=>'','error'=>'测试失败！']);
					}
					
					
				
				break;
				
			}
			
			
			
		}else{
			JsonReturn(['id'=>0,'target'=>'','desc'=>'','error'=>'插件未开启']);
		}
		
		
		
		
		
		
	}

		function translate($text,$from,$to){
		$url = "http://translate.google.cn/translate_a/single?client=gtx&dt=t&ie=UTF-8&oe=UTF-8&sl=$from&tl=$to&q=". urlencode($text);
		set_time_limit(0);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS,20);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 40);
		curl_setopt($ch, CURLOPT_URL, $url);
		$result = curl_exec($ch);
		curl_close($ch);
	        $result = json_decode($result);
		if(!empty($result)){
		foreach($result[0] as $k){
			$v[] = $k[0];
		}
		return implode(" ", $v);
		}
	}

	function fy($data){
		$zh_en=$this->translate($data,'zh-CN','EN');
		if($zh_en){
				
			$en_zh=$this->translate($zh_en,'EN','zh-CN');
			if($en_zh){
				$info=$en_zh;
				return $info;
			}else{
				JsonReturn(['id'=>0,'target'=>'','desc'=>'','error'=>'翻译内容太多或者操作太频繁!']);
			}
		}else{
			JsonReturn(['id'=>0,'target'=>'','desc'=>'','error'=>'翻译内容太多或者操作太频繁!']);
		}
	}
	
	


	
	
	
}