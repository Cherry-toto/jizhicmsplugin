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

class LoginController extends Controller
{
	function _init(){

		$webconf = webConf();
		$template = get_template();
		$this->webconf = $webconf;
		$this->template = $template;
		if(isset($_SESSION['terminal'])){
			$classtypedata = $_SESSION['terminal']=='mobile' ? classTypeDataMobile() : classTypeData();
		}else{
			$classtypedata = (isMobile() && $webconf['iswap']==1)?classTypeDataMobile():classTypeData();
		}
		
		foreach($classtypedata as $k=>$v){
			$classtypedata[$k]['children'] = get_children($v,$classtypedata);
		}
		$this->classtypedata = $classtypedata;
		$this->common = Tpl_style.'common/';
		$this->tpl = Tpl_style.$template.'/';
		$this->frpage = $this->frparam('page');
		$customconf = get_custom();
		$this->customconf = $customconf;
		if(isset($_SESSION['member'])){
			$this->islogin = true;
			$this->member = $_SESSION['member'];
			
			
		}else{
			$this->islogin = false;
		}

    
    }
	
	public function index(){
		//检测是否已经设置过return_url,防止多次登录覆盖
		if(!isset($_SESSION['return_url'])){
			$referer = (!isset($_SERVER['HTTP_REFERER']) || $_SERVER['HTTP_REFERER']=='') ? U('user/index') : $_SERVER['HTTP_REFERER'];
			$_SESSION['return_url'] = $referer;
		
		}
		
		if($_POST){
			$data['username'] = str_replace("'",'',$this->frparam('tel',1));//进行二次过滤校验
			$data['password'] = str_replace("'",'',$this->frparam('password',1));
			if(!$this->frparam('vercode',1) || md5(md5($this->frparam('vercode',1)))!=$_SESSION['login_vercode']){
				$xdata = array('code'=>1,'msg'=>'验证码错误！');
				if($this->frparam('ajax')){
					JsonReturn($xdata);
				}
				Error('验证码错误！');
			}
			if($data['username']=='' || $data['password']==''){
				$xdata = array('code'=>1,'msg'=>'账户密码不能为空！');
				if($this->frparam('ajax')){
					JsonReturn($xdata);
				}
				Error('账户密码不能为空！');
			}
			
			
			$where['pass'] = md5(md5($data['password']).md5($data['password']));
			$where['tel'] = $data['username'];
			$res = M('member')->find($where);
			//unset($where['tel']);
			//$where['username'] = $data['username'];
			unset($where['pass']);
			$where['token'] = $data['password'];//token登录
			$res1 =  M('member')->find($where);
			$where['email'] = $data['username'];
			unset($where['tel']);
			unset($where['token']);
			$res2 = M('member')->find($where);

			
			if($res || $res1 || $res2){
				if($res1){
					$res = $res1;
				}
				if($res2){
					$res = $res2;
				}
				unset($res['password']);
				//检测权限
				if($res['isshow']!=1){
					if($this->frparam('ajax')){
						JsonReturn(['code'=>1,'msg'=>'您的账户已被冻结！','url'=>$_SESSION['return_url']]);
					}
					Error('您的账户已被冻结！');
				}

				$group = M('member_group')->find(array('id'=>$res['gid']));
				if(!$group){
					JsonReturn(['code'=>1,'msg'=>'未找到您所在分组，请联系管理员处理！','url'=>$_SESSION['return_url']]);
				}
				unset($group['id']);
				//检测分组权限
				if($group['isagree']!=1){
					if($this->frparam('ajax')){
						JsonReturn(['code'=>1,'msg'=>'您所在的分组被限制登录！','url'=>$_SESSION['return_url']]);
					}
					Error('您所在的分组被限制登录！');
				}
				
				$_SESSION['member'] = array_merge($res,$group);
				//$_SESSION['member'] = $res;
				$update['logintime'] = time();
                //是否记住密码登录,更新token
				if($this->frparam('isremember')){
					$update['token'] = $_SESSION['token'];
				}
               
                //检查是否开启登录奖励
                if($this->webconf['login_award_open']==1){
                	$awoard = floatval($this->webconf['login_award']);
                	if($awoard>0){
                		$start = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
						$end = mktime(23, 59, 59, date('m'), date('d'), date('Y'));
                		$sql = " msg = '登录奖励' and addtime>=".$start." and addtime<".$end." and userid=".$_SESSION['member']['id'];
                		if(!M('buylog')->find($sql)){
                			$w['userid'] = $_SESSION['member']['id'];
                			$w['buytype'] = 'jifen';
				   	  		$w['type'] = 3;
				   	  		$w['msg'] = '登录奖励';
				   	  		$w['addtime'] = time();
				   	  		$w['orderno'] = 'No'.date('YmdHis');
				   	  		$w['amount'] = $awoard;
				   	  		$w['money'] = $w['amount']/($this->webconf['money_exchange']);
				   	  		$r = M('buylog')->add($w);
				   	  		if($r){
				   	  			$update['jifen'] = $_SESSION['member']['jifen']+$awoard;
				   	  			$_SESSION['member']['jifen'] = $update['jifen'];
				   	  		}
                		}
                	}
                }
                M('member')->update(array('id'=>$res['id']),$update);
				//兼容ajax登录
				if($this->frparam('ajax')){
					JsonReturn(['code'=>0,'msg'=>'登录成功！','url'=>$_SESSION['return_url']]);
				}
				Redirect($_SESSION['return_url']);
			}else{
				if($this->frparam('ajax')){
					JsonReturn(['code'=>1,'msg'=>'账户密码错误！','url'=>$_SESSION['return_url']]);
				}
				Error('账户密码错误！');
			}
			
		}
		
		$token = getRandChar(32);//系统内置32位随机数,混淆前台规则猜测(MD5)
		$_SESSION['token'] = $token;
		$this->token = $token;
     
      
		$this->display($this->template.'/user/login');
	}

  function register(){
	  
	  if($_POST){
		  if(!$this->frparam('vercode',1) || md5(md5($this->frparam('vercode',1)))!=$_SESSION['reg_vercode']){
				$xdata = array('code'=>1,'msg'=>'验证码错误！');
				if($this->frparam('ajax')){
					JsonReturn($xdata);
				}
				Error('验证码错误！');
			}
		  $w['email'] = $this->frparam('email',1,'');
		  $w['password'] = $this->frparam('password',1);
		  $w['repassword'] = $this->frparam('repassword',1);
		  $w['tel'] = $this->frparam('tel',1);
		  if($w['password']=='' || $w['tel']==''){
				$xdata = array('code'=>1,'msg'=>'账户密码不能为空！');
				if($this->frparam('ajax')){
					JsonReturn($xdata);
				}
				Error('账户密码不能为空！');
		  }
		  if($w['password']!=$w['repassword']){
				$xdata = array('code'=>1,'msg'=>'两次密码不同！');
				if($this->frparam('ajax')){
					JsonReturn($xdata);
				}
				Error('两次密码不同！');
		  }
		if(preg_match("/^(13[0-9]|14[579]|15[0-3,5-9]|16[6]|17[0135678]|18[0-9]|19[89])\\d{8}$/", $w['tel'])){  
			
		}else{  
			$xdata = array('code'=>1,'msg'=>'手机号格式不正确！');
			if($this->frparam('ajax')){
				JsonReturn($xdata);
			}
			Error('手机号格式不正确！');
		}  
		$w['regtime'] = time();
		//检查邮箱
		if($w['email']){
			if(M('member')->find(['email'=>$w['email']])){
				$xdata = array('code'=>1,'msg'=>'您的邮箱已注册！');
				if($this->frparam('ajax')){
					JsonReturn($xdata);
				}
				Error('您的邮箱已注册！');
			}
		}
		//检查是否已被注册
		if(M('member')->find(['tel'=>$w['tel']])){
			$xdata = array('code'=>1,'msg'=>'您的手机号码已注册！');
			if($this->frparam('ajax')){
				JsonReturn($xdata);
			}
			Error('您的手机号码已注册！');
		}
		$w['username'] = $w['tel'];
		$w['pass'] =  md5(md5($w['password']).md5($w['password']));
		$r = M('member')->add($w);
		if($r){
			$xdata = array('code'=>0,'msg'=>'注册成功！','url'=>U('login/index'));
			if($this->frparam('ajax')){
				JsonReturn($xdata);
			}
			Success('注册成功！',U('login/index'));
		}else{
			$xdata = array('code'=>1,'msg'=>'注册失败，请重试~');
			if($this->frparam('ajax')){
				JsonReturn($xdata);
			}
			Error('注册失败，请重试~');
		}
		  
	  }
	  
	  $this->display($this->template.'/user/register');
  }
  
  function forget(){
	  if($_POST && !isset($_POST['reset'])){
		  $username = $this->frparam('username',1);
		  $email = $this->frparam('email',1);
		  $vercode = $this->frparam('vercode',1);
		  if(!$username || !$email){
			  Error('请输入账号和邮箱！');
		  }
		  if($_SESSION['forget_code']!=md5(md5($vercode))){
			  Error('图形验证码错误！');
		  }
		  $user = M('member')->find(['username'=>$username,'email'=>$email]);
		  if($user){
			  //生成随机秘钥
			  $w['logintime'] = time();
			  $w['token'] = getRandChar(32);
			  M('member')->update(['id'=>$user['id']],$w);
			  //发送邮件
			  if($this->webconf['email_server'] && $this->webconf['email_port'] &&  $this->webconf['send_email'] &&  $this->webconf['send_pass']){
				$title = '找回密码-'.$this->webconf['web_name'];
				$body = '您的账号正在进行找回密码操作，如果确定是本人操作，请在10分钟内点击<a href="'.U('login/forget',['token'=>$w['token'],'username'=>$username]).'" target="_blank">《立即找回密码》</a>，过期失效！';
				
				send_mail($this->webconf['send_email'],$this->webconf['send_pass'],$this->webconf['send_name'],$user['email'],$title,$body);
				if(!isset($_SESSION['forget_time'])){
					$_SESSION['forget_time'] = time();
					$_SESSION['forget_num'] = 0;
				}
				
				if(($_SESSION['forget_time']+10*60)<time()){
					$_SESSION['forget_num'] = 0;
				}
				$_SESSION['forget_num']++;
				if($_SESSION['forget_num']>10 && ($_SESSION['forget_time']+10*60)<time()){
					//$this->error('您操作过于频繁，请10分钟后再尝试！');
					if($this->frparam('ajax')){
						JsonReturn(['code'=>0,'msg'=>'您操作过于频繁，请10分钟后再尝试！']);
					}
					Error('您操作过于频繁，请10分钟后再尝试！');
				}

				Success('找回密码邮件已发送，请到您的邮箱查看！',get_domain());
				 
				
			 }else{
				 Error('邮箱服务器未配置，无法发送邮件，请联系管理员找回密码！');
			 }
			  
		  }else{
			   Error('输入的信息有误！');
		  }
	  }
	  if(!isset($_POST['reset']) && $this->frparam('token',1) && $this->frparam('username',1)){
		  //检查token是否正确
		  if($this->frparam('token',1)!='' && $this->frparam('username',1)!=''){
			  $user = M('member')->find(['token'=>$this->frparam('token',1),'username'=>$this->frparam('username',1)]);
			  if($user){
				  //检查是否已过期
				  $t = (time()-$user['logintime'])/60;
				  if($t>10){
					  Error('token已失效！',U('login/forget'));
				  }
				  $this->user = $user;
				  $this->display($this->template.'/user/reset_password');
				  exit;
			  }
		  }
		  
	  }
	  
	  if($_POST && isset($_POST['reset'])){
		  $w['token'] = $this->frparam('reset',1);
		  $w['username'] = $this->frparam('username',1);
		  $pass = $this->frparam('password',1);
		  if($w['token']!='' && $w['username']!='' && $pass!=''){
			 $user = M('member')->find($w);
			 if(!$user){
				 Error('参数错误！',U('login/forget'));
			 }
			 $pass = md5(md5($pass).md5($pass));
			 if(M('member')->update(['id'=>$user['id']],['pass'=>$pass])){
				 Success('密码重置成功,请重新登录！',get_domain());
				 
			 }else{
				 Error('新密码不能与旧密码相同！');
			 }
			  
		  }
		  
 	  }
	  
	  $this->display($this->template.'/user/forget');
  }
  
  
  function nologin(){
  		if($this->islogin){
  			Redirect(U('user/index'));
  		}
  		$this->display($this->template.'/user/nologin');
  }
  
  function loginout(){
  	  $_SESSION['member'] = null;
	  $_SESSION['return_url'] = null;
      Error('安全退出~',get_domain());
  }
  
}




