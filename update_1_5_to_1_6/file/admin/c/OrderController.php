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

class OrderController extends CommonController
{


	function index(){
		
		$this->endtime = $this->frparam('end',1);
		$this->starttime = $this->frparam('start',1);
		$this->orderno = $this->frparam('orderno',1);
		$this->username = $this->frparam('username',1);	
		$this->tel = $this->frparam('tel',1);
		$this->isshow = $this->frparam('isshow');
		$this->tid = $this->frparam('tid');
		
        $this->classtypes = $this->classtypetree;
		$this->fields_list = M('Fields')->findAll(array('molds'=>'orders','islist'=>1),'orders desc');
		$data = $this->frparam();
		$res = molds_search('orders',$data);
		$get_sql = ($res['fields_search_check']!='') ? (' and '.$res['fields_search_check']) : '';
		$this->fields_search = $res['fields_search'];
		
		if($this->frparam('ajax')){
			$sql = ' 1=1 ';
			$classtypedata = classTypeData();
			foreach($classtypedata as $k=>$v){
				$classtypedata[$k]['children'] = get_children($v,$classtypedata);
			}
			if($this->frparam('start',1)){
				$starttime = strtotime($this->frparam('start',1));
				$sql .= " and addtime >= ".$starttime;
			}
			if($this->frparam('end',1)){
				$endtime = strtotime($this->frparam('end',1).' 23:59:59');
				$sql .=" and addtime <= ".$endtime;
			}
			if($this->frparam('orderno',1)){
				$sql .= " and orderno like '%".$this->frparam('orderno',1)."%' ";
			}
			
			if($this->frparam('username',1)){
				$sql .= " and username like '%".$this->frparam('username',1)."%' ";
			}
			if($this->frparam('tid')){
				$sql .= " and tid=".$this->frparam('tid')." ";
			}
			if($this->frparam('isshow')){
				$isshow = $this->frparam('isshow')==7 ? 0 : $this->frparam('isshow');
				$sql .= " and isshow=".$isshow." ";
			}
			if($this->frparam('tel',1)){
				$sql .= " and tel like '%".$this->frparam('tel',1)."%' ";
			}
			$sql .= $get_sql;
			
			$page = new Page('Orders');
			$pagelist = $page->where($sql)->setPage(array('limit'=>20,'page'=>$this->frparam('page',0,1)))->orderby('addtime desc,id desc')->go();
			
			$ajaxdata = [];
			$overpay_num = 0;
			$notpay_num = 0;
			$allmoney = 0.00;
			foreach($pagelist as $k=>$v){
				if($v['ispay']==1){
					$overpay_num+=1;
					$allmoney+=$v['price'];
				}else{
					$notpay_num+=1;
				}
				$v['new_tid'] = $v['tid']!=0 ? $classtypedata[$v['tid']]['classname']:'-';
				switch($v['isshow']){
					case 1:
					$v['new_isshow'] = '<span class="layui-badge">待付款</span>';
					break;
					case 2:
					$v['new_isshow'] = '<span class="layui-badge layui-bg-green">已付</span>';
					break;
					case 3:
					$v['new_isshow'] = '<span class="layui-badge layui-bg-orange">超时</span>';
					break;
					case 4:
					$v['new_isshow'] = '<span class="layui-badge">待审核待支付</span>';
					break;
					case 5:
					$v['new_isshow'] = '<span class="layui-badge layui-bg-black">已发货</span>';
					break;
					case 6:
					$v['new_isshow'] = '<span class="layui-badge layui-bg-gray">已废弃</span>';
					break;
					default:
					$v['new_isshow'] = '<span class="layui-badge layui-bg-blue">被删除</span>';
					break;
					
					
				}
				
				$v['new_addtime'] = date('Y-m-d H:i:s',$v['addtime']);
				$v['new_ispay'] = $v['ispay']==1 ? '<span class="layui-badge layui-bg-green">已付</span>' : '<span class="layui-badge">未付</span>';
				
				$v['edit_url'] = U('details',array('id'=>$v['id']));
				$v['new_paytime'] = $v['paytime']==0?'-':date('Y-m-d H:i:s',$v['paytime']);
				foreach($this->fields_list as $vv){
					$v[$vv['field']] = format_fields($vv,$v[$vv['field']]);
				}
				$ajaxdata[]=$v;
				
			}
			
			$pages = $page->pageList();
			$this->lists = $pagelist;
			$this->pages = $pages;
			$this->sum = $page->sum;
			//统计
			$all = $page->sum;
			
			$this->all = $all;
			$this->overpay_num = $overpay_num;
			$this->notpay_num = $notpay_num;
			$this->allmoney = $allmoney;
			JsonReturn(['code'=>0,'data'=>$ajaxdata,'count'=>$page->sum,'overpay_num'=>$this->overpay_num,'notpay_num'=>$this->notpay_num,'allmoney'=>$this->allmoney,'all'=>$this->all]);
		}
		
		
		
        
    	$this->display('order-list');
    
    }
	
	function details(){
		$this->fields_biaoshi = 'orders';
		$classtypedata = classTypeData();
		foreach($classtypedata as $k=>$v){
			$classtypedata[$k]['children'] = get_children($v,$classtypedata);
		}
		$this->classtypedata = $classtypedata;
		$id = $this->frparam('id');
		if($id){
			$data = M('Orders')->find(['id'=>$id]);
			if($_POST){
				//检测更改状态
				$isshow = $this->frparam('isshow');
				if($data['isshow']!=$isshow && $isshow==5){
					//更改为已发货状态
					//检查邮件配置
					if($this->webconf['email_server'] && $this->webconf['email_port'] &&  $this->webconf['send_email'] &&  $this->webconf['send_pass']){
						//检查客户是否提交邮箱
						if($data['receive_email']!=''){
							$title = '您的订单发货通知-'.$this->webconf['web_name'];
							if($this->webconf['send_msg']!=''){
								$body = str_replace('{xxx}',$data['receive_username'],$this->webconf['send_msg']);
							}else{
								$body = '尊敬的'.$data['receive_username'].'，您的订单已发货了，这几天请您留意一下快递，谢谢您的惠顾！期待再次为您服务！';
							}
							
							$body.='<br/>订单详细信息如下：<br/>';
							$body.='<table style="min-width:500px">
							<tr><th width="20%">主图</th><th width="20%">商品</th><th width="20%">价格</th><th width="20%">购买数量</th><th width="20%">总价</th></tr>';
							
							foreach(explode('||',$data['body']) as $v){
								if($v!=''){
									$d = explode('-',$v);
									//tid-id-num-price
									if($d[0]!=''){
										$type = $this->classtypedata[$d[0]];//栏目
										$product = M($type['molds'])->find(['id'=>$d[1]]);
										$body.='<tr><td width="20%"><img width="200px" src="'.get_domain().$product['litpic'].'" /></td><td width="20%">'.$product['title'].'</td><td width="20%">￥'.$d[3].'元</td><td width="20%">'.$d[2].'</td><td width="20%">￥'.($d[3]*$d[2]).'元</td></tr>';
										
									}
									
									
								}
								
							}
							
							$body.='<tr><td>折扣：</td><td colspan="4">￥'.$data['discount'].'元</td></tr><tr><td>运费：</td><td colspan="4">￥'.$data['yunfei'].'元</td></tr><tr><td>合计：</td><td colspan="4">￥'.$data['price'].'元</td></tr></table><br/>';
							$body.='收件地址：'.$data['receive_address'].' 联系电话：'.$data['receive_tel'];
							if($this->webconf['send_email']!=''){
								send_mail($this->webconf['send_email'],$this->webconf['send_pass'],$this->webconf['send_name'],$data['receive_email'],$title,$body);
							}
							
							
							
						}
					}
					
				
				}
					M('orders')->update(['id'=>$data['id']],['isshow'=>$isshow]);
					JsonReturn(['code'=>0,'msg'=>'操作成功！']);
					
				
			}
			
			
			
			$this->data = $data;
			$this->display('order-details');
		}
		
		
	}
	function deleteorder(){
		$id = $this->frparam('id');
		if($id){
			if(M('orders')->delete('id='.$id)){
				JsonReturn(array('code'=>0,'msg'=>'删除成功！'));
			}else{
				JsonReturn(array('code'=>1,'msg'=>'删除失败！'));
			}
		}
	}
	

//批量删除
	function deleteAll(){
		$data = $this->frparam('data',1);
		if($data!=''){
			if(M('Orders')->delete('id in('.$data.')')){
				JsonReturn(array('code'=>0,'msg'=>'批量删除成功！'));
				
			}else{
				JsonReturn(array('code'=>1,'msg'=>'批量操作失败！'));
			}
		}
	}



}