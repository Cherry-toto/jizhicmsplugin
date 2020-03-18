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


namespace A\plugins;

use A\c\CommonController;
use FrPHP\Extend\Page;
class ClasstypeNavController extends CommonController
{
	
		function addclass(){
		$this->fields_biaoshi = 'classtype';
		
		if($this->frparam('go')==1){

			$htmlurl = $this->frparam('htmlurl',1);
			if($htmlurl==''){
				$htmlurl = str_replace(' ','',pinyin($this->frparam('classname',1)));
			}
			if($this->webconf['islevelurl'] && $this->frparam('pid')!=0){
				//层级
				$classtypetree = classTypeData();
				$htmlurl = $classtypetree[$this->frparam('pid')]['htmlurl'].'/'.$htmlurl;
			}
			
			

			$w['pid'] = $this->frparam('pid');
			$w['orders'] = $this->frparam('orders');
			$w['classname'] = $this->frparam('classname',1);
			$w['molds'] = $this->frparam('molds',1);
			$w['description'] = $this->frparam('description',1);
			$w['keywords'] = $this->frparam('keywords',1);
			$w['litpic'] = $this->frparam('litpic',1);
			$w['body'] = $this->frparam('body',4);
			$w['htmlurl'] = $htmlurl;
			$w['iscover'] = $this->frparam('iscover');
			$w['lists_html'] = $this->frparam('lists_html',1);
			$w['details_html'] = $this->frparam('details_html',1);
			$w['gourl'] = $this->frparam('gourl',1);
			$w['lists_num'] = $this->frparam('lists_num');
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
				$fields=M('fields')->findAll(' tids like "%,'.$w['pid'].',%" ',null,'id,tids');
				foreach ($fields as $v){
					if($v['tids']!=0 && $v['tids']!=''){
						M('fields')->update(array('id'=>$v['id']),array('tids'=>$v['tids'].$a.','));
					}else{
						M('fields')->update(array('id'=>$v['id']),array('tids'=>','.$a.','));
					}
					
				}
				$w['id'] = $a;
				$this->addNav($w);
				//这里
				setCache('classtypetree',null);
				setCache('classtype',null);
				setCache('mobileclasstype',null);
				JsonReturn(array('status'=>1,'info'=>'添加栏目成功，继续添加~','url'=>U('addclass',array('pid'=>$w['pid'],'biaoshi'=>$w['molds']))));
			}else{
				JsonReturn(array('status'=>0,'info'=>'新增失败！'));
			}
		}
		//模块
		$this->molds = M('Molds')->findAll(['isopen'=>1]);
		
		$this->pid = $this->frparam('pid');
		$this->biaoshi = $this->frparam('biaoshi',1);
		//$classtype = M('classtype')->findAll(null,'orders desc');
		//$classtype = getTree($classtype);
		$this->classtypes = $this->classtypetree;
			//var_dump($this->classtypes);
		$this->display('classtype-add');
		
	}
		function editclass(){
		$this->data = M('classtype')->find(array('id'=>$this->frparam('id')));
		$this->fields_biaoshi = 'classtype';
		if($this->frparam('go')==1){
			$htmlurl = $this->frparam('htmlurl',1);
			if($htmlurl==''){
				$htmlurl = str_replace(' ','',pinyin($this->frparam('classname',1)));
			}
			
			
			$w['pid'] = $this->frparam('pid');
			$w['orders'] = $this->frparam('orders');
			$w['classname'] = $this->frparam('classname',1);
			$w['molds'] = $this->frparam('molds',1);
			$w['description'] = $this->frparam('description',1);
			$w['keywords'] = $this->frparam('keywords',1);
			$w['id'] = $this->frparam('id');
			$w['litpic'] = $this->frparam('litpic',1);
			$w['body'] = $this->frparam('body',4);
			$w['htmlurl'] = $htmlurl;
			$w['iscover'] = $this->frparam('iscover');
			$w['lists_html'] = $this->frparam('lists_html',1);
			$w['details_html'] = $this->frparam('details_html',1);
			$w['lists_num'] = $this->frparam('lists_num');
			$w['gourl'] = $this->frparam('gourl',1);
			
			
			
			$data = $this->frparam();
			$data = get_fields_data($data,'classtype');
			$w = array_merge($data,$w);
			
			//检测pid是否为该栏目下级
			if(checkClass($w['pid'],$this->data['id']) || ($w['pid']==$this->data['id'])){
				JsonReturn(array('status'=>0,'info'=>'不能选择当前栏目及下级为顶级栏目'));
			}
			
			
			$a = M('classtype')->update(array('id'=>$w['id']),$w);
			if($a){
				if($w['iscover']==1){
					$children = M('classtype')->update(array('pid'=>$w['id']),array('lists_html'=>$w['lists_html'],'details_html'=>$w['details_html'],'lists_num'=>$w['lists_num']));
				}
				//批量修改栏目对应的模块内容htmlurl
				if($this->data['htmlurl']!=$data['htmlurl']){
					M($data['molds'])->update(array('tid'=>$data['id']),array('htmlurl'=>$data['htmlurl']));
			
				}
				//批量修改栏目url
				if($this->webconf['islevelurl']==1){
					if( ($this->data['htmlurl']!=$data['htmlurl']) || ($this->data['pid']!=$w['pid'])){
						
						//层级
						$classtypetree = classTypeData();
						$children = get_children($classtypetree[$w['id']],$classtypetree,5);
						//计算当前url
						//以前的url替换成当前的url
						$old_htmlurl = $this->data['htmlurl'];
						if(strpos($w['htmlurl'],'/')!==false){
							//获取最后一个
							$htl = explode('/',$w['htmlurl']);
							$htl_new = end($htl);//最后一个名字
							
						}else{
							$htl_new = $w['htmlurl'];
						}
						
						if($w['pid']!=0){
							$p_html = $classtypetree[$w['pid']]['htmlurl'];
							$new_htmlurl = $p_html.'/'.$htl_new;
						}else{
							$new_htmlurl = $htl_new;
						}
						//更新栏目及其内容HTML
						M('classtype')->update(['id'=>$data['id']],['htmlurl'=>$new_htmlurl]);
						M($data['molds'])->update(array('tid'=>$data['id']),array('htmlurl'=>$new_htmlurl));
						
						foreach($children as $v){
							$html = substr($v['htmlurl'],strlen($old_htmlurl));
							$htmlurl_s = $new_htmlurl.$html;
							M('classtype')->update(['id'=>$v['id']],['htmlurl'=>$htmlurl_s]);
							M($v['molds'])->update(['tid'=>$v['id']],['htmlurl'=>$htmlurl_s]);
						}

					}


				}
				$this->setNav($this->data,$w);
				setCache('classtypetree',null);
				setCache('classtype',null);
				setCache('mobileclasstype',null);
				JsonReturn(array('status'=>1));
			}else{
				JsonReturn(array('status'=>0,'info'=>'修改失败！'));
			}
		}
		
		//模块
		$this->molds = M('Molds')->findAll(['isopen'=>1]);
		//$classtype = M('classtype')->findAll(null,'orders desc');
		//$classtype = getTree($classtype);
	
		$this->classtypes = $this->classtypetree;
		$this->display('classtype-edit');
		
	}
	function deleteclass(){
		$id = $this->frparam('id');
		if($id){
			//检测栏目是否有下级
			if(M('classtype')->find(['pid'=>$id])){
				JsonReturn(array('status'=>0,'info'=>'该栏目有子栏目，请先删除子栏目！'));
			}
			
			$a = M('classtype')->delete(array('id'=>$id));
			if($a){
				$classtypetree = classTypeData();
				$oldclass = $classtypetree[$id];
				$this->delNav($oldclass);
				setCache('classtypetree',null);
				setCache('classtype',null);
				setCache('mobileclasstype',null);
				JsonReturn(array('status'=>1));
			}else{
				JsonReturn(array('status'=>0,'info'=>'删除失败！'));
			}
		}
		
	}
	
	//删除栏目
	function delNav($oldclass){
		$classtypenav = M('ruler')->find(['fc'=>'Classtypenav']);
		if(!$classtypenav){
			Error('栏目导航转化插件安装失败，请重新卸载安装！');
		}
		$pid = $classtypenav['id'];
	
		switch ($oldclass['molds']) {
				case 'article':
					if($oldclass['details_html']==''){
						$w['fc'] = 'Classtype/editclass/id/'.$oldclass['id'];
					}else{
						$w['fc'] = 'Article/articlelist/tid/'.$oldclass['id'];
					}
					
					break;
				case 'product':
					if($oldclass['details_html']==''){
						$w['fc'] = 'Classtype/editclass/id/'.$oldclass['id'];
					}else{
						$w['fc'] = 'Product/productlist/tid/'.$oldclass['id'];
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
					$w['fc'] = 'Classtype/editclass/id/'.$oldclass['id'];
					break;
				default:
					if($oldclass['details_html']==''){
						$w['fc'] = 'Classtype/editclass/id/'.$oldclass['id'];
					}else{
						$w['fc'] = 'Extmolds/index/molds/'.$oldclass['molds'].'/tid/'.$oldclass['id'];
					}
					break;
			}
		$w['pid'] =$pid;

		M('ruler')->delete($w);



	}
	//新增栏目
	function addNav($newclass){
		$classtypenav = M('ruler')->find(['fc'=>'Classtypenav']);
		if(!$classtypenav){
			Error('栏目导航转化插件安装失败，请重新卸载安装！');
		}
		$pid = $classtypenav['id'];
		$w['name'] = $newclass['classname'];
		$w['pid'] = $pid;
		switch ($newclass['molds']) {
			case 'article':
				if($newclass['details_html']==''){
					$w['fc'] = 'Classtype/editclass/id/'.$newclass['id'];
				}else{
					$w['fc'] = 'Article/articlelist/tid/'.$newclass['id'];
				}
				
				break;
			case 'product':
				if($newclass['details_html']==''){
					$w['fc'] = 'Classtype/editclass/id/'.$newclass['id'];
				}else{
					$w['fc'] = 'Product/productlist/tid/'.$newclass['id'];
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
				$w['fc'] = 'Classtype/editclass/id/'.$newclass['id'];
				break;
			default:
				if($newclass['details_html']==''){
					$w['fc'] = 'Classtype/editclass/id/'.$newclass['id'];
				}else{
					$w['fc'] = 'Extmolds/index/molds/'.$newclass['molds'].'/tid/'.$newclass['id'];
				}
				break;
		}


		M('ruler')->add($w);


	}
	//更新导航，主要是栏目链接及栏目名字
	function setNav($oldclass,$newclass){
		$classtypenav = M('ruler')->find(['fc'=>'Classtypenav']);
		if(!$classtypenav){
			Error('栏目导航转化插件安装失败，请重新卸载安装！');
		}
		$pid = $classtypenav['id'];
		switch ($oldclass['molds']) {
				case 'article':
					if($oldclass['details_html']==''){
						$w['fc'] = 'Classtype/editclass/id/'.$oldclass['id'];
					}else{
						$w['fc'] = 'Article/articlelist/tid/'.$oldclass['id'];
					}
					
					break;
				case 'product':
					if($oldclass['details_html']==''){
						$w['fc'] = 'Classtype/editclass/id/'.$oldclass['id'];
					}else{
						$w['fc'] = 'Product/productlist/tid/'.$oldclass['id'];
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
					$w['fc'] = 'Classtype/editclass/id/'.$oldclass['id'];
					break;
				default:
					if($oldclass['details_html']==''){
						$w['fc'] = 'Classtype/editclass/id/'.$oldclass['id'];
					}else{
						$w['fc'] = 'Extmolds/index/molds/'.$oldclass['molds'].'/tid/'.$oldclass['id'];
					}
					break;
			}


		$w['pid'] = $pid;
		$classnav = M('ruler')->find($w);
		if($classnav){
			$w['name'] = $newclass['classname'];
			
			switch ($newclass['molds']) {
				case 'article':
					if($newclass['details_html']==''){
						$w['fc'] = 'Classtype/editclass/id/'.$newclass['id'];
					}else{
						$w['fc'] = 'Article/articlelist/tid/'.$newclass['id'];
					}
					
					break;
				case 'product':
					if($newclass['details_html']==''){
						$w['fc'] = 'Classtype/editclass/id/'.$newclass['id'];
					}else{
						$w['fc'] = 'Product/productlist/tid/'.$newclass['id'];
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
					$w['fc'] = 'Classtype/editclass/id/'.$newclass['id'];
					break;
				default:
					if($newclass['details_html']==''){
						$w['fc'] = 'Classtype/editclass/id/'.$newclass['id'];
					}else{
						$w['fc'] = 'Extmolds/index/molds/'.$newclass['molds'].'/tid/'.$newclass['id'];
					}
					break;
			}

			M('ruler')->update(['id'=>$classnav['id']],$w);
		}

		
		

	}
	
	
}