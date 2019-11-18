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


namespace A\plugins;

use FrPHP\lib\Controller;
use FrPHP\Extend\Page;
class ClasstypeTemplateController extends Controller
{
	function _init(){
    	//检查当前账户是否合乎操作
	  
      if(!isset($_SESSION['admin']) || $_SESSION['admin']['id']==0){
      	   Redirect(U('Login/index'));
        
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
	public function get_html(){
		$molds = $this->frparam('molds',1,'article');
		$plugins = M('plugins')->find(['filepath'=>'classtypetemplate']);
		if(!$plugins){
			JsonReturn(['code'=>1,'msg'=>'【栏目模板便签】插件不存在，请重新安装！']);
		}
		if($plugins['config']==''){
			$template = 'template';
		}else{
			$config = json_decode($plugins['config'],1);
			$template = $config['template'];
		}
		
		$dir = APP_PATH.'Home/'.$template.'/'.$this->webconf['pc_template'].'/'.strtolower($molds);
		$fileArray=array();
		if(is_dir($dir)){

			if (false != ($handle = opendir ( $dir ))) {
				$i=0;
				while ( false !== ($file = readdir ( $handle )) ) {
					//去掉"“.”、“..”以及带“.xxx”后缀的文件
					if ($file != "." && $file != ".."&& strpos($file,".html")!==false) {
						$fileArray[$i]=['html'=>$file,'value'=>str_replace('.html','',$file)];
						
						$i++;
					}
				}
				//关闭句柄
				closedir ( $handle );
			}
		}
		
		JsonReturn(['code'=>0,'data'=>$fileArray,'path'=>$dir]);

	}
	
	
	
	
	
}