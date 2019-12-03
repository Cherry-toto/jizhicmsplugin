<?php

// +----------------------------------------------------------------------
// | JiZhiCMS { 极致CMS，给您极致的建站体验 }  
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2099 http://www.jizhicms.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 留恋风 <2581047041@qq.com>
// +----------------------------------------------------------------------
// | Date：2019/11/15
// +----------------------------------------------------------------------


namespace Home\plugins;

use Home\c\CommonController;

class QqController extends CommonController
{
	  //应用的APPID
	  private $app_id = "";
	  //应用的APPKEY
	  private $app_secret = "";
	  //【成功授权】后的回调地址，即此地址在腾讯的信息中有储存
	  // 如：https://www.jizhicms.cn/QQ/index
	  private $return_url = '';

	  function _init(){
	  	  parent::_init();
	  	  //检查插件是否开启
	  	  //
	  	  $plugin = M('plugins')->find(['filepath'=>'qqlogin','isopen'=>1]);
	  	  if(!$plugin){
	  	  	Error('插件未开启！');
	  	  }
	  	  $config = json_decode($plugin['config'],1);
	  	  if(!isset($config['appid']) || !isset($config['appsecret'])){
	  	  	Error('登录错误：appid和appsecret不能为空！');
	  	  }
	  	 
	  	  $this->app_id = $config['appid'];
	  	  $this->app_secret = $config['appsecret'];
	  	  $this->return_url = get_domain()."/qq/index";

	  	  //检查是否有设置refer
	  	  if(!isset($_SESSION['return_url'])){
	  	  	$referer = (!isset($_SERVER['HTTP_REFERER']) || $_SERVER['HTTP_REFERER']=='') ? U('user/index') : $_SERVER['HTTP_REFERER'];
	  	  	$_SESSION['return_url'] = $referer;
	  	  }
	  }

	  function index(){

		   //Step1：获取Authorization Code
		   $code = $this->frparam("code",1);//存放Authorization Code
		   if(!$code)
		   {
		    //state参数用于防止CSRF攻击，成功授权后回调时会原样带回
		    $_SESSION['state'] = md5(uniqid(rand(), TRUE));
		    //拼接URL
		    $dialog_url = "https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id="
		    .$this->app_id."&redirect_uri=" . urlencode($this->return_url) . "&state="
		     . $_SESSION['state'];

		    
		     echo("<script> top.location.href='" . $dialog_url . "'</script>");
		     exit;
		   }
		  
		   //Step2：通过Authorization Code获取Access Token
		   if($this->frparam('state',1) == $_SESSION['state'])
		   {
		    //拼接URL
		    $token_url = "https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&"
		     . "client_id=" . $this->app_id . "&redirect_uri=" . urlencode($this->return_url)
		     . "&client_secret=" . $this->app_secret . "&code=" . $code;
		    $response = file_get_contents($token_url);
		    if (strpos($response, "callback") !== false)//如果登录用户临时改变主意取消了，返回true!==false,否则执行step3
		    {
		     $lpos = strpos($response, "(");
		     $rpos = strrpos($response, ")");
		     $response = substr($response, $lpos + 1, $rpos - $lpos -1);
		     $msg = json_decode($response);
		     if (isset($msg->error))
		     {
		      echo "<h3>error:</h3>" . $msg->error;
		      echo "<h3>msg :</h3>" . $msg->error_description;
		      exit;
		     }
		    }
		  
		    //Step3：使用Access Token来获取用户的OpenID
		    $params = array();
		    parse_str($response, $params);//把传回来的数据参数变量化
		    $graph_url = "https://graph.qq.com/oauth2.0/me?access_token=".$params['access_token'];
		    $str = file_get_contents($graph_url);
		    if (strpos($str, "callback") !== false)
		    {
		     $lpos = strpos($str, "(");
		     $rpos = strrpos($str, ")");
		     $str = substr($str, $lpos + 1, $rpos - $lpos -1);
		    }
		    $user = json_decode($str);//存放返回的数据 client_id ，openid
		    if (isset($user->error))
		    {
		     echo "<h3>error:</h3>" . $user->error;
		     echo "<h3>msg :</h3>" . $user->error_description;
		     exit;
		    }
		    //echo("Hello " . $user->openid);
		    //echo("Hello " . $params['access_token']);
		  	
		    //Step4：使用<span >openid,</span><span >access_token来获取所接受的用户信息。</span>
		    	$user_data_url = "https://graph.qq.com/user/get_user_info?access_token={$params['access_token']}&oauth_consumer_key={$this->app_id}&openid={$user->openid}&format=json";
		      
		    	$user_data = file_get_contents($user_data_url);//此为获取到的user信息
		    	//var_dump($user_data);
		    	$user_data = json_decode($user_data,1);
		    	if($user_data['ret']!=0){
		    		Error($user_data['msg'],U('Login/index'));
		    	}
		    	$w['username'] = $user_data->nickname;
		    	
		    	$res = M('member')->find(array('qq_openid'=>$user->openid));
			  	if($res){
			  		$group = M('member_group')->find(array('id'=>$res['gid']));

			  		if(!$group){
						//JsonReturn(['code'=>1,'msg'=>'未找到您所在分组，请联系管理员处理！','url'=>$_SESSION['return_url']]);
						Error('未找到您所在分组，请联系管理员处理！',U('Login/index'));
					}
					unset($group['id']);
					//检测分组权限
					if($group['isagree']!=1){
						
						Error('您所在的分组被限制登录！',U('Login/index'));
					}
					
					$_SESSION['member'] = array_merge($res,$group);
					//$_SESSION['member'] = $res;
					$update['logintime'] = time();
					$update['litpic'] = $user_data['figureurl_2'];

	                M('member')->update(array('id'=>$res['id']),$update);
	                M('member')->update(array('id'=>$res['id']),$update);
					
					Redirect($_SESSION['return_url']);


			  	}else{
			  		
			  		//获取QQ
			  		$qq_1 = str_replace('http://qzapp.qlogo.cn/qzapp/','',$user_data['figureurl_2']);
			  		$qq_2 = explode('/',$qq_1);
			  		$w['email'] = $qq_2[0].'@qq.com';
			  		$w['sex'] = (!isset($user_data['gender']) || $user_data['gender']=='男') ? 1 : 2;
			  		$w['username'] = $user_data['nickname'];
			  		$w['litpic'] = $user_data['figureurl_2'];
					$w['pass'] = '123456';
					$w['tel'] = '';
					$w['qq_openid'] = $user->openid;
					$w['gid'] = 1;
					$w['regtime'] = time();
					$w['logintime'] = time();
					$r = M('member')->add($w);
					if($r){
						$w = M('member')->find(['id'=>$r]);//再次查询一次，把默认数据放进去
						unset($w['pass']);
						$group = M('member_group')->find(array('id'=>1));
				  		if(!$group){
							//JsonReturn(['code'=>1,'msg'=>'未找到您所在分组，请联系管理员处理！','url'=>$_SESSION['return_url']]);
							Error('未找到您所在分组，请联系管理员处理！',U('Login/index'));
						}
						unset($group['id']);
						//检测分组权限
						if($group['isagree']!=1){
							
							Error('您所在的分组被限制登录！',U('Login/index'));
						}
						$w['id'] = $r;
						$_SESSION['member'] = array_merge($w,$group);
						Redirect($_SESSION['return_url']);
					}
			  	}


		    }else{
		    	 exit('非法访问！');
		    }



	  }

	
  
   
}