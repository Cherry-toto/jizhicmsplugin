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

class ExtmoldsController extends Controller
{
	function _init(){
		if(!isset($_SESSION['admin']) || $_SESSION['admin']['id']==0){
      	  Error('请登录账户~',U('Login/index'));
        
		}
		
		if($_SESSION['admin']['isadmin']!=1){
			if(strpos($_SESSION['admin']['paction'],','.APP_CONTROLLER.',')===false){
				$molds = $this->frparam('molds',1);
				$action = APP_CONTROLLER.'/'.APP_ACTION.'/molds/'.$molds;
				
				if(strpos($_SESSION['admin']['paction'],','.$action.',')===false){
				   $ac = M('Ruler')->find(array('fc'=>$action));
				   if($this->frparam('ajax')){
					   
					   JsonReturn(['code'=>1,'msg'=>'您没有【'.$ac['name'].'】的权限！','url'=>U('Index/index')]);
				   }
				   Error('您没有'.$ac['name'].'的权限！');
				}
			}
		   
		  
		}
		 $this->admin = $_SESSION['admin'];
		
		  $webconf = webConf();
		  $template = get_template();
		  $this->webconf = $webconf;
		  $this->template = $template;
		  $this->tpl = Tpl_style.$template.'/';
		  $customconf = get_custom();
		  $this->customconf = $customconf;
		  $this->classtypetree =  get_classtype_tree();
		  if($_SESSION['admin']['isadmin']!=1){
			$tids = $_SESSION['admin']['tids'];
			foreach ($this->classtypetree as $k => $v) {
				if($v['pid']==0){
					if(strpos($_SESSION['admin']['tids'],','.$v['id'].',')!==false){
						$children = get_children($v,$this->classtypetree,5);
						foreach($children as $vv){
							if(strpos($_SESSION['admin']['tids'],','.$vv['id'].',')===false){
								$tids .= ','.$vv['id'].',';
							}
						}
					}
				}
				
			}
			
		}else{
			$tids = '0';
		}
		$this->tids = $tids;
	}
	public function index(){
		
		$classtypedata = classTypeData();
		foreach($classtypedata as $k=>$v){
			$classtypedata[$k]['children'] = get_children($v,$classtypedata);
		}
		$molds = $this->frparam('molds',1);
		if($molds==''){
			Error('模块为空，请选择模块！');
		}
		$this->molds = M('Molds')->find(array('biaoshi'=>$molds));
		$data = $this->frparam();
		$res = molds_search($molds,$data);
		$this->isshow = $this->frparam('isshow');
		$this->tid = $this->frparam('tid');
		$this->fields_search = $res['fields_search'];
		$this->fields_list = M('Fields')->findAll(array('molds'=>$molds,'islist'=>1),'orders desc,id asc');
		$this->classtypes = $this->classtypetree;
		if($this->frparam('ajax')){
			
			$sql = '1=1';
			if($this->admin['isadmin']!=1){
				$a1 = explode(',',$this->tids);
				$a2 = array_filter($a1);
				$tids = implode(',',$a2);
				$sql.=' and tid in('.$tids.') ';
			}
			if($this->frparam('isshow')){
				if($this->frparam('isshow')==1){
					$isshow=1;
				}else if($this->frparam('isshow')==2){
					$isshow=0;
				}else{
					$isshow = 2;
				}
				$sql .= ' and isshow='.$isshow;
			}
			$get_sql = ($res['fields_search_check']!='') ? (' and '.$res['fields_search_check']) : '';
			$sql .= $get_sql;
			if($this->frparam('tid')){
				$sql .= ' and tid in('.implode(",",$classtypedata[$this->frparam('tid')]["children"]["ids"]).')';
				
			}
			
			$page = new Page($molds);
			$data = $page->where($sql)->orderby('orders desc,id desc')->page($this->frparam('page',0,1))->go();
			$ajaxdata = [];
			foreach($data as $k=>$v){
				if(isset($classtypedata[$v['tid']])){
					$v['new_tid'] = $v['tid']!=0 ? $classtypedata[$v['tid']]['classname'] : '-';
				}else{
					$v['new_tid'] = '[未分类]';
				}
				
				$v['new_isshow'] = $v['isshow']==1 ? '已审' : ($v['isshow']==2 ? '退回' : '未审');
				$v['view_url'] = $v['htmlurl']!='' ? get_domain().'/'.$v['htmlurl'].'/'.$v['id'] : '';
				$v['edit_url'] = U('Extmolds/editmolds',array('id'=>$v['id'],'molds'=>$molds));
				
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
		
		
		
		
		$this->display('extmolds-list');
		
	}
	
	public function addmolds(){
		$molds = $this->frparam('molds',1);
		$this->fields_biaoshi = $molds;
		if($this->frparam('go',1)==1){
			
			$data = $this->frparam();
			if(isset($data['tid'])){
				$pclass = get_info_table('classtype',array('id'=>$data['tid']));
				$data['htmlurl'] = $pclass['htmlurl'];
			}
			
			$data = get_fields_data($data,$molds);
			if(M($molds)->add($data)){
				
				JsonReturn(array('code'=>0,'msg'=>'添加成功,继续添加~','url'=>U('Extmolds/addmolds',['tid'=>$data['tid'],'molds'=>$molds])));
				
			}else{
				
				JsonReturn(array('code'=>1,'msg'=>'添加失败！'));
				
			}
			
			
			
		}
		$classtype = M('classtype')->findAll(array('molds'=>$molds),'orders desc');
		$classtype = set_class_haschild($classtype);
		$classtype = getTree($classtype);
		
		$this->classtypes = $classtype;
		$this->tid =  $this->frparam('tid',0,0);
		$this->molds = M('Molds')->find(array('biaoshi'=>$molds));
		
		$this->display('extmolds-add');
	}
	
	public function editmolds(){
		$molds = $this->frparam('molds',1);
		$this->fields_biaoshi = $molds;
		if($this->frparam('go',1)==1){
			
			$data = $this->frparam();
			if(isset($data['tid'])){
				$pclass = get_info_table('classtype',array('id'=>$data['tid']));
				$data['htmlurl'] = $pclass['htmlurl'];
			}
			$data = get_fields_data($data,$molds);
			if($this->frparam('id')){
				if(M($molds)->update(array('id'=>$this->frparam('id')),$data)){
					if($this->webconf['release_award_open']==1 && $data['isshow']==1){
						$award = round($this->webconf['release_award'],2);
						$max_award = round($this->webconf['release_max_award'],2);
						$member_id = M($molds)->getField(['id'=>$this->frparam('id')],'member_id');
						
						if($member_id!=0 && $award>0){
							$rr = M('buylog')->find(['userid'=>$member_id,'type'=>3,'molds'=>$molds,'aid'=>$this->frparam('id'),'msg'=>'发布奖励']);
							if(!$rr){
								$start = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
								$end = mktime(23, 59, 59, date('m'), date('d'), date('Y'));

								$sql = " addtime>=".$start." and addtime<".$end." and userid=".$member_id." and type=3 and msg='发布奖励' ";
								$all = M('buylog')->findAll($sql,null,'amount');
								$all_jifen = 0;
								if($all){
									foreach($all as $v){
										$all_jifen+=$v['amount'];
									}
								}
								
								if($max_award==0 || ($all_jifen<$max_award && $max_award!=0)){
									$w['userid'] = $member_id;
		                			$w['buytype'] = 'jifen';
						   	  		$w['type'] = 3;
						   	  		$w['molds'] = $molds;
						   	  		$w['aid'] = $this->frparam('id');
						   	  		$w['msg'] = '发布奖励';
						   	  		$w['addtime'] = time();
						   	  		$w['orderno'] = 'No'.date('YmdHis');
						   	  		$w['amount'] = $award;
						   	  		$w['money'] = $w['amount']/($this->webconf['money_exchange']);
						   	  		$r = M('buylog')->add($w);
						   	  		M('member')->goInc(['id'=>$member_id],'jifen',$award);
								}
							}
							
						}
					}
					JsonReturn(array('code'=>0,'msg'=>'修改成功！'));
					
				}else{
					
					JsonReturn(array('code'=>1,'msg'=>'修改失败！'));
					
				}
			
			}else{
				JsonReturn(array('code'=>1,'msg'=>'缺少ID'));
				
			}
			
			
			
		}
		$this->data = M($molds)->find(array('id'=>$this->frparam('id')));
		$this->molds = M('Molds')->find(array('biaoshi'=>$molds));
		$this->tid =  $this->data['tid'];
		$this->classtypetree =  get_classtype_tree();
		$this->classtypes = $this->classtypetree;
		$this->display('extmolds-edit');
	}
	
	public function  copymolds(){
		$id = $this->frparam('id');
		$molds = $this->frparam('molds',1);
		if($id){
			$data = M($molds)->find(['id'=>$id]);
			unset($data['id']);
			if(M($molds)->add($data)){
				
				JsonReturn(array('code'=>0,'msg'=>'复制成功！'));
				exit;
			}else{
				
				JsonReturn(array('code'=>1,'msg'=>'复制失败！'));
				exit;
			}
			
			
		}
	}
	
	//批量删除
	function deleteAll(){
		$data = $this->frparam('data',1);
		$molds = $this->frparam('molds',1);
		if($data!=''){
			if(M($molds)->delete('id in('.$data.')')){
				JsonReturn(array('code'=>0,'msg'=>'批量删除成功！'));
				
			}else{
				JsonReturn(array('code'=>1,'msg'=>'批量操作失败！'));
			}
		}
	}
	//单一删除
	function deletemolds(){
		$id = $this->frparam('id');
		$molds = $this->frparam('molds',1);
		if($id){
			if(M($molds)->delete('id='.$id)){
				
				JsonReturn(array('code'=>0,'msg'=>'删除成功！'));
			}else{
				
				JsonReturn(array('code'=>1,'msg'=>'删除失败！'));
			}
		}
	}
	
		//修改排序
	function editOrders(){

		$field = $this->frparam('field',1);
		$w[$field] = $this->frparam('value',1);
		$molds = $this->frparam('molds',1);
		$r = M($molds)->update(array('id'=>$this->frparam('id')),$w);
		if(!$r){
			JsonReturn(array('code'=>1,'info'=>'修改失败！'));
		}
		JsonReturn(array('code'=>0,'info'=>'修改成功！'));

	}
	//批量修改栏目
	function changeType(){
		$data = $this->frparam('data',1);
		$molds = $this->frparam('molds',1);
		$tid = $this->frparam('tid');
		if($data!=''){
			$list = M($molds)->findAll('id in('.$data.')');
			$r = true;
			foreach($list as $v){
				$w['tid'] = $tid;
				$type = M('classtype')->find(array('id'=>$tid));
				$w['htmlurl'] = $type['htmlurl'];
				M($molds)->update(array('id'=>$v['id']),$w);
			}
			JsonReturn(array('code'=>0,'msg'=>'批量修改成功！'));
		}
	}
	//批量复制
	function copyAll(){
		$data = $this->frparam('data',1);
		$molds = $this->frparam('molds',1);
		if($data!=''){
			$list = M($molds)->findAll('id in('.$data.')');
			$r = true;
			foreach($list as $v){
				unset($v['id']);
				M($molds)->add($v);
			}
			JsonReturn(array('code'=>0,'msg'=>'批量复制成功！'));
				
		}
	}

	//批量审核
	function checkAll(){
		$data = $this->frparam('data',1);
		$molds = $this->frparam('molds',1);
		if($data!=''){
			if($this->frparam('isshow')==1){
				$isshow = 1;
			}else if($this->frparam('isshow')==2){
				$isshow = 0;
			}else{
				$isshow = 2;
			}
			if($isshow==1){
				$all = M($molds)->findAll('id in('.$data.')');
				$award = round($this->webconf['release_award'],2);
				$max_award = round($this->webconf['release_max_award'],2);
				$start = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
				$end = mktime(23, 59, 59, date('m'), date('d'), date('Y'));

				foreach ($all as $k => $v) {
					if($v['isshow']!=1){
						//start
						if($this->webconf['release_award_open']==1){
							$member_id = $v['member_id'];
							if($member_id!=0 && $award>0){
								$rr = M('buylog')->find(['userid'=>$member_id,'type'=>3,'molds'=>$molds,'aid'=>$v['id'],'msg'=>'发布奖励']);
								if(!$rr){
									
									$sql = " addtime>=".$start." and addtime<".$end." and userid=".$member_id." and type=3 and msg='发布奖励' ";
									$all = M('buylog')->findAll($sql,null,'amount');
									$all_jifen = 0;
									if($all){
										foreach($all as $vv){
											$all_jifen+=$vv['amount'];
										}
									}
									
									if($max_award==0 || ($all_jifen<$max_award && $max_award!=0)){
										$w['userid'] = $member_id;
			                			$w['buytype'] = 'jifen';
							   	  		$w['type'] = 3;
							   	  		$w['molds'] = $molds;
							   	  		$w['aid'] = $v['id'];
							   	  		$w['msg'] = '发布奖励';
							   	  		$w['addtime'] = time();
							   	  		$w['orderno'] = 'No'.date('YmdHis');
							   	  		$w['amount'] = $award;
							   	  		$w['money'] = $w['amount']/($this->webconf['money_exchange']);
							   	  		$r = M('buylog')->add($w);
							   	  		M('member')->goInc(['id'=>$member_id],'jifen',$award);
									}
								}
								
							}
						}
						//end
					}
				}
				
			}
			M($molds)->update('id in('.$data.')',['isshow'=>$isshow]);
			JsonReturn(array('code'=>0,'msg'=>'批量审核成功！'));
		}else{
			JsonReturn(array('code'=>1,'msg'=>'批量审核失败！'));
		}
	}
}