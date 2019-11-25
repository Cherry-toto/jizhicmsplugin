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


namespace A\c;

use FrPHP\lib\Controller;
use FrPHP\Extend\Page;

class CommentController extends CommonController
{
	

	function addcomment(){
		$this->fields_biaoshi = 'comment';
		if($this->frparam('go',1)==1){
			
			$data = $this->frparam();
			$data['addtime'] = strtotime($data['addtime']);
			$data['body'] = $this->frparam('body',4);
			$data['userid'] = $_SESSION['admin']['id'];
			
			
			$data = get_fields_data($data,'comment');
			
			if(M('Comment')->add($data)){
				//Success('添加成功！',U('index'));
				JsonReturn(array('code'=>0,'msg'=>'添加成功！'));
				exit;
			}else{
				//Error('添加失败！');
				JsonReturn(array('code'=>1,'msg'=>'添加失败！'));
				exit;
			}
			
			
			
		}
		
		//$classtype = M('classtype')->findAll(null,'orders desc');
		//$classtype = getTree($classtype);
	
		$this->classtypes = $this->classtypetree;;
		$this->display('comment-add');
	}

	//批量删除评论
	function deleteAll(){
		$data = $this->frparam('data',1);
		if($data!=''){
			if(M('comment')->delete('id in('.$data.')')){
				JsonReturn(array('code'=>0,'msg'=>'批量删除成功！'));
				
			}else{
				JsonReturn(array('code'=>1,'msg'=>'批量操作失败！'));
			}
		}
	}
	
	//评论管理
	function commentlist(){
		
		$this->tid=  $this->frparam('tid');
		$this->aid = $this->frparam('aid');
		$this->isshow = $this->frparam('isshow');
		$this->userid = $this->frparam('userid');
		$this->body = $this->frparam('body',1);
		$this->fields_list = M('Fields')->findAll(array('molds'=>'comment','islist'=>1),'orders desc');
		$data = $this->frparam();
		$res = molds_search('comment',$data);
		$get_sql = ($res['fields_search_check']!='') ? (' and '.$res['fields_search_check']) : '';
		$this->fields_search = $res['fields_search'];
		$this->classtypes = $this->classtypetree;
		if($this->frparam('ajax')){
			
			$page = new Page('Comment');
			$sql = '1=1';
			if($this->frparam('tid')){
				$sql .= ' and tid='.$this->frparam('tid');
			}
			if($this->frparam('aid')){
				$sql.=" and aid = ".$this->frparam('aid')." ";
			}
			if($this->frparam('body',1)){
				$sql.=" and body like  '%".$this->frparam('body',1)."%' ";
			}
			if($this->frparam('userid')!=0){
				$sql.=" and userid = ".$this->frparam('userid')." ";
			}
			$sql .= $get_sql;
			$data = $page->where($sql)->orderby('id desc')->page($this->frparam('page',0,1))->go();
			
			$ajaxdata = [];
			$classtypedata = classTypeData();
			
			foreach($data as $k=>$v){
				$v['new_username'] = $v['userid']!=0 ? get_info_table('member',array('id'=>$v['userid']),'username') : '';
				$v['new_user'] = $v['userid']!=0 ? U('Member/memberedit',['id'=>$v['userid']]) : '';
				if($v['tid']!=0 && isset($classtypedata[$v['tid']])){
					$v['new_tid'] = $classtypedata[$v['tid']]['classname'];
				}else{
					$v['new_tid'] =  '';
				}
				if($v['aid']!=0 && $v['tid']!=0 && isset($classtypedata[$v['tid']])){
					$adata = M($classtypedata[$v['tid']]['molds'])->find(['id'=>$v['aid']]);
					if($adata){
						$v['new_aid_url'] = get_domain().'/'.$adata['htmlurl'].'/'.$v['aid'];
					}else{
						$v['new_aid_url'] = '';
					}
					
				}else{
					$v['new_aid_url'] = '';
				}
				
				$v['new_zid'] = $v['zid']!=0 ? U('Comment/editcomment',array('id'=>$v['zid'])) : '';
				$v['new_pid'] = $v['pid']!=0 ? U('Comment/editcomment',array('id'=>$v['pid'])) : '';
				
				$v['new_isshow'] = $v['isshow']==1 ? '已审' : '未审';
				$v['new_isread'] = $v['isread']==1 ? '已读' : '未读';
				$v['new_addtime'] = date('Y-m-d H:i:s',$v['addtime']);
				$v['edit_url'] = U('Comment/editcomment',array('id'=>$v['id']));
				
				foreach($this->fields_list as $vv){
					$v[$vv['field']] = format_fields($vv,$v[$vv['field']]);
				}
				$ajaxdata[]=$v;
				
			}
			
			$pages = $page->pageList();
			$this->pages = $pages;
			$this->lists = $data;
			$this->sum = $page->sum;
			JsonReturn(['code'=>0,'data'=>$ajaxdata,'count'=>$page->sum]);
			
		}
		
		
		
		
		$this->display('comment-list');
		
		
	}
	
	public function editcomment(){
		$this->fields_biaoshi = 'comment';
		if($this->frparam('go',1)==1){
			
			$data = $this->frparam();
			$data['addtime'] = strtotime($data['addtime']);
			$data['body'] = $this->frparam('body',4);
			$data = get_fields_data($data,'comment');
			if($this->frparam('id')){
				if(M('Comment')->update(array('id'=>$this->frparam('id')),$data)){
					//Success('修改成功！',U('index'));
					JsonReturn(array('code'=>0,'msg'=>'修改成功！','url'=>U('index')));
					exit;
				}else{
					//Error('修改失败！');
					JsonReturn(array('code'=>1,'msg'=>'修改失败！'));
					exit;
				}
			
			}
			
			
			
		}
		if($this->frparam('id')){
			$this->data = M('Comment')->find(array('id'=>$this->frparam('id')));
		}
		//$classtype = M('classtype')->findAll(null,'orders desc');
		//$classtype = getTree($classtype);
	
		$this->classtypes = $this->classtypetree;;
		$this->display('comment-details');
		
	}
	function deletecomment(){
		$id = $this->frparam('id');
		if($id){
			if(M('Comment')->delete('id='.$id)){
				//Success('删除成功！',U('index'));
				JsonReturn(array('code'=>0,'msg'=>'删除成功！'));
			}else{
				//Error('删除失败！');
				JsonReturn(array('code'=>1,'msg'=>'删除失败！'));
			}
		}
	}
	
	
	//批量审核
	function checkAll(){
		$data = $this->frparam('data',1);
		if($data!=''){
			$isshow = $this->frparam('isshow')==1 ? 1 : 0;
			M('comment')->update('id in('.$data.')',['isshow'=>$isshow]);
			JsonReturn(array('code'=>0,'msg'=>'批量审核成功！'));
		}else{
			JsonReturn(array('code'=>1,'msg'=>'批量审核失败！'));
		}
	}
	
	
	
	
	
	
	
	
	
	
}