<?php

// +----------------------------------------------------------------------
// | JiZhiCMS { 极致CMS，给您极致的建站体验 }  
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2099 http://www.jizhicms.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 留恋风 <2581047041@qq.com>
// +----------------------------------------------------------------------
// | Date：2019/09/24
// +----------------------------------------------------------------------


namespace Home\plugins;

use Home\c\CommonController;
use FrPHP\Extend\Page;

class MessageController extends CommonController
{

	function index(){
		
		if($_POST){
			
			$w = $this->frparam();
			$w = get_fields_data($w,'message',0);
			
			$w['body'] = $this->frparam('body',1,'','POST');
			$w['user'] = $this->frparam('user',1,'','POST');
			$w['tel'] = $this->frparam('tel',1,'','POST');
			$w['aid'] = $this->frparam('aid',0,0,'POST');
			$w['tid'] = $this->frparam('tid',0,0,'POST');
			
			
			
			$w['ip'] = GetIP();
			$w['addtime'] = time();
			if(isset($_SESSION['member'])){
				$w['userid'] = $_SESSION['member']['id'];
			}
			
			if($this->frparam('title',1,'','POST')==''){
				//$this->error('标题不能为空！');
				if($this->frparam('ajax')){
					JsonReturn(['code'=>1,'msg'=>'标题不能为空！']);
				}
				Error('标题不能为空！');
			}
			if($w['user']==''){
				//$this->error('姓名不能为空！');
				if($this->frparam('ajax')){
					JsonReturn(['code'=>1,'msg'=>'称呼不能为空！']);
				}
				Error('称呼不能为空！');
			}
			
			
			$w['title'] = $this->frparam('title',1);
			//仅在存在手机号的情况进行检测手机号是否有效-可自由设置
			if($w['tel']!=''){
				if(!preg_match("/^(13[0-9]|14[579]|15[0-3,5-9]|16[6]|17[0135678]|18[0-9]|19[89])\\d{8}$/",$w['tel'])){  
					//$this->error('您的手机号格式不正确！');
					if($this->frparam('ajax')){
						JsonReturn(['code'=>1,'msg'=>'您的手机号格式不正确！']);
					}
					Error('您的手机号格式不正确！');
				}
				
			}
			
			
			
			
			if(!isset($_SESSION['message_time'])){
				$_SESSION['message_time'] = time();
				$_SESSION['message_num'] = 0;
			}
			
			if(($_SESSION['message_time']+10*60)<time()){
				$_SESSION['message_num'] = 0;
			}
			$_SESSION['message_num']++;
			if($_SESSION['message_num']>10 && ($_SESSION['message_time']+10*60)<time()){
				//$this->error('您操作过于频繁，请10分钟后再尝试！');
				if($this->frparam('ajax')){
					JsonReturn(['code'=>1,'msg'=>'您操作过于频繁，请10分钟后再尝试！']);
				}
				Error('您操作过于频繁，请10分钟后再尝试！');
			}
			
			
			$res = M('message')->add($w);
			if($res){
				
				$plugins = M('plugins')->find(['filepath'=>'msgemail','isopen'=>1]);
				if($plugins){
					$config = unserialize($plugins['config']);
					$email_server = ($config['email_server']=='') ? $this->webconf['email_server'] : $config['email_server'];
					$email_port = ($config['email_port']=='') ? $this->webconf['email_port'] : $config['email_port'];
					$send_email = ($config['send_email']=='') ? $this->webconf['send_email'] : $config['send_email'];
					$send_pass = ($config['send_pass']=='') ? $this->webconf['send_pass'] : $config['send_pass'];
					$send_name = ($config['send_name']=='') ? $this->webconf['send_name'] : $config['send_name'];
					$shou_email = ($config['shou_email']=='') ? $this->webconf['send_email'] : $config['shou_email'];
					
					if($email_server && $email_port &&  $send_email &&  $send_pass){
						$title = $config['title'];
						$fields =  M('fields')->findAll(['molds'=>'message','isshow'=>1]);
						$table_fields =['{title}','{tid}','{aid}','{user}','{ip}','{body}','{tel}','{addtime}'];
						$classname = $w['tid']!=0 ? $this->classtypedata[$w['tid']]['classname']:'无';
						if($w['tid']){
							$aid = M($this->classtypedata[$w['tid']]['molds'])->getField(['id'=>$w['aid']],'title');
						}else{
							$aid = '无';
						}
						
						$values = [$w['title'],$classname,$aid,$w['user'],$w['ip'],$w['body'],$w['tel'],date('Y-m-d H:i:s',$w['addtime'])];
						foreach($fields as $k=>$v){
							switch($v['fieldtype']){
								
								case 5:
								$values[] = '<img width="200px" height="200px" src="'.get_domain().'/'.$w[$v['field']].'">';
								break;
								case 7:
								case 12:
									
									if($w[$v['field']]){
										$r = explode(',',$v['body']);
										foreach($r as $s){
											$d = explode('=',$s);
											if($d[1]==$w[$v['field']]){
												$values[] = $d[0];
											}
										}
										
									}else{
										$values[] = '';
									}
									
								
								break;
								case 8:
									if($w[$v['field']]){
										$r = explode(',',$v['body']);
										$rr = array();
										foreach($r as $s){
												$d = explode('=',$s);
												if(strpos($w[$v['field']],','.$d[1].',')!==false){
													$rr[]=$d[0];
												}
										}
										$values[] =  implode(',',$rr);
									}else{
										$values[] = '';
									}
								break;
								case 13:
								if($w[$v['field']]){
									$bdy = explode(',',$v['body']);
									$biaoshi = M('molds')->getField(['id'=>$bdy[0]],'biaoshi');
									$res = M($biaoshi)->getField(['id'=>$w[$v['field']]],$bdy[1]);
									$values[] = $res;
								}else{
									$values[] = 0;
								}
								
								break;
								default:
								$values[] = $w[$v['field']];
								break;
								
							}
							
							$table_fields[]='{'.$v['field'].'}';
							
						}
						$title = str_replace($table_fields,$values,$config['title']);
						$body =str_replace($table_fields,$values,$config['body']);
						
						
						send_mail($send_email,$send_pass,$send_name,$send_email,$title,$body,$shou_email);
						
						
					}
					
				}
				
			
				
				
				if($this->frparam('ajax')){
					JsonReturn(['code'=>1,'msg'=>'提交成功！我们会尽快回复您！','url'=>get_domain()]);
				}
				Success('提交成功！我们会尽快回复您！',get_domain());
			}else{
				if($this->frparam('ajax')){
					JsonReturn(['code'=>1,'msg'=>'提交失败，请重试！']);
				}
				//$this->error('提交失败，请重试！');
				Error('提交失败，请重试！');
			}
			
			
			
		}
		

		
	}
}