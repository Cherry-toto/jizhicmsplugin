<?php

// +----------------------------------------------------------------------
// | JiZhiCMS { 极致CMS，给您极致的建站体验 }  
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2099 http://www.jizhicms.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 留恋风 <2581047041@qq.com>
// +----------------------------------------------------------------------
// | Date：2019/08
// +----------------------------------------------------------------------


namespace Home\c;

use FrPHP\lib\Controller;
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
			
			if($this->webconf['autocheckmessage']==1){
				$w['isshow'] = 1;
			}else{
				$w['isshow'] = 0;
			}
			
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
					JsonReturn(['code'=>0,'msg'=>'您操作过于频繁，请10分钟后再尝试！']);
				}
				Error('您操作过于频繁，请10分钟后再尝试！');
			}
			
			
			$res = M('message')->add($w);
			if($res){
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