<?php

// +----------------------------------------------------------------------
// | JiZhiCMS { 极致CMS，给您极致的建站体验 }  
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2099 http://www.jizhicms.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 留恋风 <2581047041@qq.com>
// +----------------------------------------------------------------------
// | Date：2019/10
// +----------------------------------------------------------------------


namespace A\plugins;
use FrPHP\lib\Controller;
class ImageController extends Controller
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
	
	public function uploads(){
		
		if ($_FILES["file"]["error"] > 0){
		  $data['error'] =  "Error: " . $_FILES["file"]["error"];
		  $data['code'] = 1000;
		}else{
		  // echo "Upload: " . $_FILES["file"]["name"] . "<br />";
		  // echo "Type: " . $_FILES["file"]["type"] . "<br />";
		  // echo "Size: " . ($_FILES["file"]["size"] / 1024) . " Kb<br />";
		  // echo "Stored in: " . $_FILES["file"]["tmp_name"];
		  $pix = explode('.',$_FILES['file']['name']);
		  $pix = end($pix);
		  
		  
		    $fileType = $this->webconf['fileType'];
			if(strpos($fileType,strtolower($pix))===false){
				$data['error'] =  "Error: 文件类型不允许上传！";
				$data['code'] = 1002;
				JsonReturn($data);
			}
			$fileSize = (int)webConf('fileSize');
			if($fileSize!=0 && $_FILES["file"]["size"]>$fileSize){
				$data['error'] =  "Error: 文件大小超过网站内部限制！";
				$data['code'] = 1003;
				JsonReturn($data);
			}
		  
		  $filename =  'Public/Admin/'.date('Ymd').rand(1000,9999).'.'.$pix;
		  $filename_x =  'Public/Admin/'.date('Ymd').rand(1000,9999).'.'.$pix;
		  
			if(move_uploaded_file($_FILES["file"]['tmp_name'],$filename)){
			
				if( (strtolower($pix)=='png' && $this->webconf['ispngcompress']==1) || strtolower($pix)=='jpg' || strtolower($pix)=='jpeg'){
					$imagequlity = (int)$this->webconf['imagequlity'];
					if($imagequlity!=100){
						$image = new \compressimage($filename);
    					$image->percent = 1;
						$image->ispngcompress = $this->webconf['ispngcompress'];
    					$image->quality = $imagequlity=='' ? 75 : $imagequlity;
    					$image->openImage();
    					$image->thumpImage();
    					//$image->showImage();
    					unlink($filename);
    					$image->saveImage($filename_x);
    					$filename = $filename_x;
					}
				   
				}
				//判断是否为图片
				//是否处理gif图
				$plugin = M('plugins')->find(['filepath'=>'imagethumbnail','isopen'=>1]);
				if($plugin){
				   $config = json_decode($plugin['config'],1);
				   if((strtolower($pix)=='gif' && $config['gif_open']==1) || strtolower($pix)!='gif'){
					if(strtolower($pix)=='png' || strtolower($pix)=='jpg' || strtolower($pix)=='jpeg' || strtolower($pix)=='gif' || strtolower($pix)=='wbmp' ){
						//对图片进行处理
						//$filename = 'book_rabbit_rule.jpg'; 
						$filepic = explode('.',$filename);
						$file_name = $filepic[0];
						$filedata = getimagesize($filename);
						$w = $filedata[0];
						$h = $filedata[1];
						/* 读取图档 */ 
						if(strtolower($pix)=='png'){
							$im = imagecreatefrompng($filename); 
						}else if(strtolower($pix)=='jpg' || strtolower($pix)=='jpeg' ){
							$im = imagecreatefromjpeg($filename); 
						}else if(strtolower($pix)=='gif'){
							$im = imagecreatefromgif($filename); 
						}else{
							$im = imagecreatefromwbmp($filename); 
						}
						
						//小图
						if($config['small_open']==1){
							$pic_small = $file_name.'_s.'.$pix;
							if(!$config['small_value_x'] || !$config['small_value_y']){
								//按照尺寸
								if($w<$h){
									$new_img_small_width = $h * $config['small_rate_x'] / $config['small_rate_y'];
									$new_img_small_height = $h; 
									//x:y = w:h
								}else{
									$new_img_small_height = $w * $config['small_rate_y'] / $config['small_rate_x'];
									$new_img_small_width = $w;
								}
								
								$small_w = $new_img_small_width>$w ? $w : $new_img_small_width;
								$small_h = $new_img_small_height>$h ? $h : $new_img_small_height;
							}else{
								$new_img_small_width = $config['small_value_x'];
								$new_img_small_height = $config['small_value_y'];
								$small_w = $w;
								$small_h = $h;
							}
							
							
							
							$newim_small = imagecreatetruecolor($new_img_small_width, $new_img_small_height); 
							imagecopyresampled($newim_small, $im, 0, 0, 0, 0, $new_img_small_width, $new_img_small_height, $small_w, $small_h); 
							if(strtolower($pix)=='png'){
								imagepng($newim_small,$pic_small); 
							}else if(strtolower($pix)=='jpg' || strtolower($pix)=='jpeg' ){
								imagejpeg($newim_small,$pic_small); 
							}else if(strtolower($pix)=='gif'){
								imagegif($newim_small,$pic_small); 
							}else{
								imagewbmp($newim_small,$pic_small); 
							}
							imagedestroy($newim_small); 
							$data['url'] = $pic_small;
							$data['code'] = 0;
							$filesize = round(filesize(APP_PATH.$pic_small)/1024,2);
							M('pictures')->add(['litpic'=>'/'.$pic_small,'addtime'=>time(),'userid'=>$_SESSION['admin']['id'],'size'=>$filesize]);
						}
						
						
						//中图-数据库存储
						if($config['default_open']==1){
							$pic_thumbnail = $filename;
							if(!$config['default_value_x'] || !$config['default_value_y']){
								//按照尺寸
								if($w<$h){
									$new_img_thumbnail_width = $h * $config['default_rate_x'] / $config['default_rate_y'];
									$new_img_thumbnail_height = $h; 
									//x:y = w:h
								}else{
									$new_img_thumbnail_height = $w * $config['default_rate_y'] / $config['default_rate_x'];
									$new_img_thumbnail_width = $w;
								}
								$thumbnail_w = $new_img_thumbnail_width>$w ? $w : $new_img_thumbnail_width;
							    $thumbnail_h = $new_img_thumbnail_height>$h ? $h : $new_img_thumbnail_height;
							}else{
								$new_img_thumbnail_width = $config['default_value_x'];
								$new_img_thumbnail_height = $config['default_value_y'];
								$thumbnail_w = $w;
								$thumbnail_h = $h;
							}
						
							
							$newim_thumbnail = imagecreatetruecolor($new_img_thumbnail_width, $new_img_thumbnail_height); 
							imagecopyresampled($newim_thumbnail, $im, 0, 0, 0, 0, $new_img_thumbnail_width, $new_img_thumbnail_height, $thumbnail_w, $thumbnail_h); 
							if(strtolower($pix)=='png'){
								imagepng($newim_thumbnail,$pic_thumbnail); 
							}else if(strtolower($pix)=='jpg' || strtolower($pix)=='jpeg' ){
								imagejpeg($newim_thumbnail,$pic_thumbnail); 
							}else if(strtolower($pix)=='gif'){
								imagegif($newim_thumbnail,$pic_thumbnail); 
							}else{
								imagewbmp($newim_thumbnail,$pic_thumbnail); 
							}
							imagedestroy($newim_thumbnail);
						}
						//大图
						if($config['large_open']==1){
							$pic_large = $file_name.'_l.'.$pix;
							if(!$config['large_value_x'] || !$config['large_value_y']){
								//按照尺寸
								if($w<$h){
									$new_img_large_width = $h * $config['large_rate_x'] / $config['large_rate_y'];
									$new_img_large_height = $h; 
									//x:y = w:h
								}else{
									$new_img_large_height = $w * $config['large_rate_y'] / $config['large_rate_x'];
									$new_img_large_width = $w;
								}
								$large_w = $new_img_large_width>$w ? $w : $new_img_large_width;
								$large_h = $new_img_large_height>$h ? $h : $new_img_large_height;
							}else{
								$new_img_large_width = $config['large_value_x'];
								$new_img_large_height = $config['large_value_y'];
								$large_w = $w;
								$large_h = $h;
							}
							
							
							$newim_large = imagecreatetruecolor($new_img_large_width, $new_img_large_height); 
							imagecopyresampled($newim_large, $im, 0, 0, 0, 0, $new_img_large_width, $new_img_large_height, $large_w, $large_h); 
							if(strtolower($pix)=='png'){
								imagepng($newim_large,$pic_large); 
							}else if(strtolower($pix)=='jpg' || strtolower($pix)=='jpeg' ){
								imagejpeg($newim_large,$pic_large); 
							}else if(strtolower($pix)=='gif'){
								imagegif($newim_large,$pic_large);
							}else{
								imagewbmp($newim_large,$pic_large);
							}
							imagedestroy($newim_large); 
							$data['url'] = $pic_large;
							$data['code'] = 0;
							$filesize = round(filesize(APP_PATH.$pic_large)/1024,2);
							M('pictures')->add(['litpic'=>'/'.$pic_large,'addtime'=>time(),'userid'=>$_SESSION['admin']['id'],'size'=>$filesize]);
							
						}
					
						imagedestroy($im);
					
					 }
					}
				}
				
				
				
				
				$data['url'] = $filename;
				$data['code'] = 0;
				$filesize = round(filesize(APP_PATH.$filename)/1024,2);
				M('pictures')->add(['litpic'=>'/'.$filename,'addtime'=>time(),'userid'=>$_SESSION['admin']['id'],'size'=>$filesize]);
				
				
			}else{
				$data['error'] =  "Error: 请检查目录[Public/Admin]写入权限";
				$data['code'] = 1001;
				  
			} 

			  
		  
		  }

		  JsonReturn($data);
		
		
	}
	
	
	
	
	
}