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

class LoginController extends Controller
{
	public function _init(){
		  $webconf = webConf();
		  $template = get_template();
		  $this->webconf = $webconf;
		  $this->template = $template;
		  $this->tpl = Tpl_style.$template.'/';
		  $customconf = get_custom();
		  $this->customconf = $customconf;
		
	}
	public function index(){
		
		if($_POST){
			//$data = $this->frparam();//去除全局获取
			$data['username'] = str_replace("'",'',$this->frparam('username',1));//进行二次过滤校验
			$data['password'] = str_replace("'",'',$this->frparam('password',1));
			
			if($data['username']=='' || $data['password']==''){
				$xdata = array('code'=>1,'msg'=>'账户密码不能为空！');
				JsonReturn($xdata);
			}
			if(md5(md5($this->frparam('vercode',1)))!=$_SESSION['frcode']){
				$xdata = array('code'=>1,'msg'=>'验证码错误！');
				JsonReturn($xdata);
			}
			$where['pass'] = md5(md5($data['password']).'YF');
			$where['name'] = $data['username'];
			
			$res1 = M('level')->find($where);
			unset($where['name']);
			$where['tel'] = $data['username'];
			$res2 = M('level')->find($where);
			unset($where['tel']);
			$where['email'] = $data['username'];
			$res3 = M('level')->find($where);
		
			
			
			
			if($res1 || $res2 || $res3){
			
				$res = ($res1) ? $res1 :($res2 ? $res2 : $res3);
				unset($res['pass']);
				if($res['status']==0){
					$data = array('code'=>1,'msg'=>'该账户已被封禁！');
				}else{
					$group = M('level_group')->find(array('id'=>$res['gid']));
					if($group['isagree']==0){
						$data = array('code'=>1,'msg'=>'该账户已被封禁！');
					}else{
						unset($group['id']);
						$group['group_name'] = $group['name'];
						unset($group['name']);
						$_SESSION['admin'] = array_merge($res,$group);
						M('level')->update(array('id'=>$res['id']),array('logintime'=>time()));
						//写入日志
						if(!StopLog){
							$log['user'] = $_SESSION['admin']['name'];
							$log['userid'] = $_SESSION['admin']['id'];
							register_log($_SESSION['admin'],'login');
						}

						$data = array('code'=>0,'msg'=>'登录成功！');
					}
					
				}
               
			}else{
				$data = array('code'=>1,'msg'=>'账户密码错误！');
			}
			JsonReturn($data);
		}
		
     
      
		$this->display('login');
	}

  function vercode(){
	  frvercode();
  }
  
  
  function loginout(){
  	  $_SESSION['admin'] = null;
      Error('安全退出~',U('index'));
  }
  
}




