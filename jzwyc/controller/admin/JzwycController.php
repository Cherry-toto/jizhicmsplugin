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


namespace A\plugins;

use FrPHP\lib\Controller;
use FrPHP\Extend\Page;
class JzwycController extends Controller
{
	function _init(){
    	//检查当前账户是否合乎操作
	  
      if(!isset($_SESSION['admin']) || $_SESSION['admin']['id']==0){
      	   Redirect(U('Login/index'));
        
      }
 
      if($_SESSION['admin']['isadmin']!=1){
		if(strpos($_SESSION['admin']['paction'],','.APP_CONTROLLER.',')!==false){
           
        }else{
			$action = APP_CONTROLLER.'/'.APP_ACTION;
			if(strpos($_SESSION['admin']['paction'],','.$action.',')===false){
			   $ac = M('Ruler')->find(array('fc'=>$action));
			   if($this->frparam('ajax')){
				   
				   JsonReturn(['code'=>1,'msg'=>'您没有【'.$ac['name'].'】的权限！','url'=>U('Index/index')]);
			   }
			   Error('您没有【'.$ac['name'].'】的权限！',U('Index/index'));
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
    
    }
	public function index(){
		
		if($_POST){
			$data = $this->frparam('body',1);
			$num  = strlen($data);
			$info = $this->fy($data);


			echo json_encode(['code'=>0,'msg'=>'success','data'=>$info]);exit;
		}
		
		$this->display('wyc');

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
			$this->error();
		}
	}else{
		$this->error();
	}
}

function error(){
	echo json_encode(['code'=>1,'msg'=>'翻译内容太多或者操作太频繁!','data'=>'']);exit;
}	
	
	
	
}