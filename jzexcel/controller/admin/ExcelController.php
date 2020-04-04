<?php

// +----------------------------------------------------------------------
// | JiZhiCMS { 极致CMS，给您极致的建站体验 }  
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2099 http://www.jizhicms.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 留恋风 <2581047041@qq.com>
// +----------------------------------------------------------------------
// | Date：2020/03/30
// +----------------------------------------------------------------------


namespace A\plugins;

use A\c\CommonController;
use FrPHP\lib\Controller;
use FrPHP\Extend\Page;
class ExcelController extends CommonController
{
	
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
			if($fileSize!=0 && $_FILES["file"]["size"]/(1024*1024)>$fileSize){
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
				if( (strtolower($pix)=='png' || strtolower($pix)=='jpg' || strtolower($pix)=='jpeg') && $this->webconf['iswatermark']==1 && $this->webconf['watermark_file']!='' && !empty($this->webconf['watermark_file'])){
					if(file_exists(APP_PATH.$this->webconf['watermark_file'])){
						watermark($filename,APP_PATH.$this->webconf['watermark_file'],$this->webconf['watermark_t'],$this->webconf['watermark_tm']);
					}
					
				}
				$data['url'] = $filename;
				$data['code'] = 0;
				$filesize = round(filesize(APP_PATH.$filename)/1024,2);
				M('pictures')->add(['litpic'=>$data['url'],'addtime'=>time(),'userid'=>$_SESSION['admin']['id'],'size'=>$filesize,'filetype'=>strtolower($pix),'tid'=>$this->frparam('tid',0,0),'molds'=>$this->frparam('molds',1,null)]);
				
				
			}else{
				$data['error'] =  "Error: 请检查目录[Public/Admin]写入权限";
				$data['code'] = 1001;
				  
			} 

			  
		  
		  }

		  JsonReturn($data);
	}
	
	
	function import(){
		$filepath = $this->frparam('excel_path',1);
		$excel_type = $this->frparam('excel_type',0,2005);
		$excel_style = $this->frparam('excel_style',0,2);
		$excel_molds = $this->frparam('excel_molds',1,'article');
		$excel_molds_biaoshi = $this->frparam('newmolds',1);
		$molds_name = $this->frparam('molds_name',1);
		if($filepath==''){
			Error('请上传Excel文件');
		}
		extendFile('excel/PHPExcel.php');
		extendFile('excel/PHPExcel/IOFactory.php');
		if($excel_type==2005){
			extendFile('excel/PHPExcel/Reader/Excel5.php');
			$objReader=\PHPExcel_IOFactory::createReader('Excel5');
		}else{
			extendFile('excel/PHPExcel/Reader/Excel2007.php');
			$objReader=\PHPExcel_IOFactory::createReader('Excel2007');
		}
	
		$objPHPExcel=$objReader->load($filepath);//$file_url即Excel文件的路径
		$sheet=$objPHPExcel->getSheet(0);//获取第一个工作表
		$highestRow=$sheet->getHighestRow();//取得总行数
		$highestColumn=$sheet->getHighestColumn(); //取得总列数
		
		
		/*取图片*/
		$imgData=array();
		$imageFilePath=APP_PATH.'Public/Admin/';//图片保存目录
		$getDrawingCollection = $sheet->getDrawingCollection();


		 //获取excel中全部的图片文件，将各个单元格中的图片文件上传替换成图片路径
		foreach ($sheet->getDrawingCollection() as $key =>$drawing) {  
			   $xy = $drawing->getCoordinates(); //"B2"
			   $cell =  $sheet->getCell($xy);//(2,2)
			  
			$path = $imageFilePath;  //单元格中图片的保存路径
			if ($drawing instanceof \PHPExcel_Worksheet_Drawing) {   // 基于 xlsx  
				$filename = $drawing->getPath();  //文件
				$path = $path . $drawing->getIndexedFilename();    //文件路径
				copy($filename, $path);  //copy(A,B) 函数拷贝文件 A->B。
				//$cell->setValue($drawing->getIndexedFilename());    //将图片的单元格值替换为图片路径
				$cell->setValue('/Public/Admin/'.$drawing->getIndexedFilename());    //将图片的单元格值替换为图片路径
				
				
			} else if ($drawing instanceof \PHPExcel_Worksheet_MemoryDrawing) {  //基于xls
				$image = $drawing->getImageResource();  
				$renderingFunction = $drawing->getRenderingFunction();      
				switch ($renderingFunction) {  
					case \PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG:  
						$filename = $drawing->getIndexedFilename();  
						$path = $path . $drawing->getIndexedFilename();  
						imagejpeg($image, $path);  
						break;  
					case \PHPExcel_Worksheet_MemoryDrawing::RENDERING_GIF: 
						$filename = $drawing->getIndexedFilename();    
						$path = $path . $drawing->getIndexedFilename();  
						imagegif($image, $path);  
						break;  
					case \PHPExcel_Worksheet_MemoryDrawing::RENDERING_PNG:  
						$filename = $drawing->getIndexedFilename();   
						$path = $path . $drawing->getIndexedFilename();  
						imagegif($image, $path);  
						break;  
					case \PHPExcel_Worksheet_MemoryDrawing::RENDERING_DEFAULT: 
						$filename = $drawing->getIndexedFilename();   
						$path = $path . $drawing->getIndexedFilename();  
						imagegif($image, $path);  
						break;  
				}  
			
				$cell->setValue($filename);        //将图片的单元格值替换为图片路径
				
			}  
		} 



		
		//循环读取excel文件
		$table = [];//表头
		$fields = [];//字段
		$exceldata = [];
		$highestColumn++;
		for($j=1;$j<=$highestRow;$j++){//从第一行开始读取数据
			if($j==1){
				//读取表头
				 
				 for($k='A';$k!=$highestColumn;$k++){
				   $str=$objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();//读取单元格
				   if($str!=''){
					   $table[] = $str;
				   }
				   $i++;
				 }
				 
				
			}else{
				if($excel_style==2 && $j==2){
				//读取字段
				 
				 for($k='A';$k!=$highestColumn;$k++){     
				   $str=$objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();//读取单元格
				   if($str!=''){
					   $fields[]=$str;
				   }
				 }
				 
				 
				
				}else{
					 $strs = [];
					 for($k='A';$k!=$highestColumn;$k++){  
						$str = $objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
						$strs[]= $str;
					   
					 }
					 
					$exceldata[] = $strs;
					
					 
				}
			}
			
			 
		}
		
		setCache('excel_import_data',$exceldata);
		setCache('excel_import_fields',$fields);
		setCache('excel_import_table',$table);
		setCache('excel_molds',$excel_molds);
		setCache('excel_molds_biaoshi',$excel_molds_biaoshi);
		setCache('excel_molds_name',$molds_name);
		$this->molds = $excel_molds;
		$this->molds_name = $molds_name;
		$this->excel_molds_biaoshi = $excel_molds_biaoshi;
		$this->fields = $fields;
		$this->table = $table;
		if($excel_molds=='newmolds'){
			
			if($molds_name=='' || $excel_molds_biaoshi==''){
				Error('模块名和标识不能为空');
			}
			
			$sql = "SHOW TABLES";
			$tables = M()->findSql($sql);
			$ttable = array();
			foreach($tables as $value){
				foreach($value as $vv){
					$ttable[] = $vv;
				}
				
			}
			$excel_molds_biaoshi = strtolower($excel_molds_biaoshi);
			//JsonReturn(array('code'=>1,'msg'=>$ttable));
			if(in_array(DB_PREFIX.$excel_molds_biaoshi,$ttable)){
				
				Error('新创建的模块已存在！');
			}
			//检测是否存在该模块
			if(M('Molds')->find(array('biaoshi'=>$excel_molds_biaoshi))){
				
				Error('新创建的模块已存在！');
			}
			
			$n = M('Molds')->add(array('name'=>$molds_name,'biaoshi'=>$excel_molds_biaoshi));
			if($n){
				$sql = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX.$excel_molds_biaoshi."` (
				`id` int(11) unsigned NOT NULL auto_increment,
				`tid` int(11) DEFAULT 0,
				`userid` int(11) DEFAULT 0,
				`orders` int(11) DEFAULT 0,
				`member_id` int(11) DEFAULT 0,
				`comment_num` int(11) DEFAULT 0,
				`htmlurl` varchar(100) DEFAULT NULL,
				`isshow` tinyint(1) DEFAULT 1,
				PRIMARY 
				KEY  (`id`)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
				
				$x = M()->runSql($sql);
				//新增一个扩展字段信息
				$w['field'] = 'member_id';
				$w['molds'] = $excel_molds_biaoshi;
				$w['fieldname'] = '发布会员';
				$w['tips'] = '前台发布会员ID记录';
				$w['fieldtype'] = 13;
				$w['fieldlong'] = 11;
				$w['body'] = '3,username';
				$w['ismust'] = 0;
				$w['isshow'] = 0;
				$w['isadmin'] = 0;
				$w['issearch'] = 0;
				$w['islist'] = 0;
				$w['vdata'] = '0';
				M('field')->add($w);
				
				//if($x){
					//添加权限管理
					$ruler['name'] = $molds_name.'列表';
					$ruler['fc'] = 'Extmolds/index/molds/'.$excel_molds_biaoshi;
					$ruler['pid'] = 77;
					$ruler['isdesktop'] = 1;
					M('Ruler')->add($ruler);
					$ruler['isdesktop'] = 0;
					$ruler['name'] = '新增'.$molds_name;
					$ruler['fc'] = 'Extmolds/addmolds/molds/'.$excel_molds_biaoshi;
					M('Ruler')->add($ruler);
					$ruler['name'] = '修改'.$molds_name;
					$ruler['fc'] = 'Extmolds/editmolds/molds/'.$excel_molds_biaoshi;
					M('Ruler')->add($ruler);
					$ruler['name'] = '复制'.$molds_name;
					$ruler['fc'] = 'Extmolds/copymolds/molds/'.$excel_molds_biaoshi;
					M('Ruler')->add($ruler);
					$ruler['name'] = '删除'.$molds_name;
					$ruler['fc'] = 'Extmolds/deletemolds/molds/'.$excel_molds_biaoshi;
					M('Ruler')->add($ruler);
					$ruler['name'] = '批量删除'.$molds_name;
					$ruler['fc'] = 'Extmolds/deleteAll/molds/'.$excel_molds_biaoshi;
					M('Ruler')->add($ruler);
					$ruler['name'] = '批量修改'.$molds_name.'栏目';
					$ruler['fc'] = 'Extmolds/changeType/molds/'.$excel_molds_biaoshi;
					M('Ruler')->add($ruler);
					$ruler['name'] = '批量复制'.$molds_name;
					$ruler['fc'] = 'Extmolds/copyAll/molds/'.$excel_molds_biaoshi;
					M('Ruler')->add($ruler);
					$ruler['name'] = '批量修改'.$molds_name.'列表';
					$ruler['fc'] = 'Extmolds/editOrders/molds/'.$excel_molds_biaoshi;
					M('Ruler')->add($ruler);
					$ruler['name'] = '批量审核'.$molds_name;
					$ruler['fc'] = 'Extmolds/checkAll/molds/'.$excel_molds_biaoshi;
					M('Ruler')->add($ruler);
					
					
					
				
			}else{
				//Error('添加失败！');
				Error('新创建的模块失败！');
				
			}
			
			$this->field_exits = ['id','tid','userid','orders','member_id','comment_num','htmlurl','isshow'];
			
			
			
		}else{
			if(defined('DB_TYPE') && DB_TYPE=='sqlite'){
				//sqlite
				$sql="pragma table_info(".DB_PREFIX.$excel_molds.")";
				$data = M($excel_molds)->findSql($sql);
				$allow = [];
				foreach($data as $v){
					$allow[] = $v['name'];
					
				}
			}else{
				//$sql="select COLUMN_NAME from information_schema.COLUMNS where table_name = '".DB_PREFIX.$molds."' and table_schema = '".DB_PREFIX.$molds."';";
				$sql = "SHOW FULL COLUMNS FROM ".DB_PREFIX.$excel_molds;
				$data = M($excel_molds)->findSql($sql);
				$allow = [];
				foreach($data as $v){
					$allow[] = $v['Field'];
					
				}
			}
			$this->allowfields = $allow;
		}
		
		$this->display('excel-index');
		
		
		
	}
	
	
	function importover(){
		$molds = $this->frparam('molds',1);
		$fields = $this->frparam('fields',2);
		$table = $this->frparam('title',2);
		$fieldstype = $this->frparam('fieldstype',2);
		$exceldata = getCache('excel_import_data');
		if($molds!='newmolds'){
			
			//$fields = getCache('excel_import_fields');
			//$table = getCache('excel_import_table');
			
			
			//循环输出每行数据
			foreach($exceldata as $k=>$v){
				//循环输出每个数据
				$insert = [];
				foreach($v as $kk=>$vv){
					$f = strtolower($fields[$kk]);
					if($f!='id'){
					switch($fieldstype[$kk]){
						case 3:
							$insert[$f] = strtotime($vv);
						break;
						default:
							$insert[$f] = $vv;
						break;
						}
						
					}
				}
				M($molds)->add($insert);
				$insert = null;
				
				
			}
			
			
			$id = M('plugins')->getField(['filepath'=>'excel'],'id');
			Success('导入成功！',U('plugins/setconf',['id'=>$id]));
			
		}else{
			$excel_molds_biaoshi = $this->frparam('excel_molds_biaoshi',1);
			//创建新模块字段
			$field_exits = ['id','tid','userid','orders','member_id','comment_num','htmlurl','isshow'];
			
			$sql='';
			$insertarray = [];
			
			foreach($fields as $k=>$v){
				$v = strtolower($v);
				if($v!='id'){
					
					if(!in_array($v,$field_exits)){
						
						switch($fieldstype[$k]){
							case 1:
							$sql .= "ALTER TABLE ".DB_PREFIX.$excel_molds_biaoshi." ADD ".$v." VARCHAR(255)   default NULL; ";
							M('fields')->add(['field'=>$v,'molds'=>$excel_molds_biaoshi,'fieldname'=>$table[$k],'fieldtype'=>1,'fieldlong'=>255,'ismust'=>0,'islist'=>1]);
							break;
							case 2:
							$sql .= "ALTER TABLE ".DB_PREFIX.$excel_molds_biaoshi." ADD ".$v." VARCHAR(500)   default NULL; ";
							M('fields')->add(['field'=>$v,'molds'=>$excel_molds_biaoshi,'fieldname'=>$table[$k],'fieldtype'=>2,'fieldlong'=>500,'ismust'=>0,'islist'=>1]);
							break;
							case 3:
							$sql .= "ALTER TABLE ".DB_PREFIX.$excel_molds_biaoshi." ADD ".$v." TEXT   default NULL; ";
							M('fields')->add(['field'=>$v,'molds'=>$excel_molds_biaoshi,'fieldname'=>$table[$k],'fieldtype'=>3,'fieldlong'=>255,'ismust'=>0,'islist'=>1]);
							break;
							case 4:
							$sql .= "ALTER TABLE ".DB_PREFIX.$excel_molds_biaoshi." ADD ".$v." INT(11)   default 0; ";
							M('fields')->add(['field'=>$v,'molds'=>$excel_molds_biaoshi,'fieldname'=>$table[$k],'fieldtype'=>4,'fieldlong'=>11,'ismust'=>0,'islist'=>1]);
							break;
							case 5:
							$sql .= "ALTER TABLE ".DB_PREFIX.$excel_molds_biaoshi." ADD ".$v." VARCHAR(255)   default NULL; ";
							M('fields')->add(['field'=>$v,'molds'=>$excel_molds_biaoshi,'fieldname'=>$table[$k],'fieldtype'=>5,'fieldlong'=>255,'ismust'=>0,'islist'=>1]);
							break;
							case 6:
							$sql .= "ALTER TABLE ".DB_PREFIX.$excel_molds_biaoshi." ADD ".$v." INT(11)   default 0 ;";
							M('fields')->add(['field'=>$v,'molds'=>$excel_molds_biaoshi,'fieldname'=>$table[$k],'fieldtype'=>11,'fieldlong'=>11,'ismust'=>0,'islist'=>1]);
							break;
							case 7:
							$sql .= "ALTER TABLE ".DB_PREFIX.$excel_molds_biaoshi." ADD ".$v." INT(11)   default 0 ;";
							M('fields')->add(['field'=>$v,'molds'=>$excel_molds_biaoshi,'fieldname'=>$table[$k],'fieldtype'=>11,'fieldlong'=>11,'ismust'=>0,'islist'=>1]);
							break;
							case 8:
							$sql .= "ALTER TABLE ".DB_PREFIX.$excel_molds_biaoshi." ADD ".$v." VARCHAR(255)   default NULL ;";
							M('fields')->add(['field'=>$v,'molds'=>$excel_molds_biaoshi,'fieldname'=>$table[$k],'fieldtype'=>9,'fieldlong'=>255,'ismust'=>0,'islist'=>1]);
							break;
						}
						
					}
				}
				
				
				
			}
			M()->runSql($sql);
			
		
			
			//循环输出每行数据
			foreach($exceldata as $k=>$v){
				//循环输出每个数据
				$insert = [];
				foreach($v as $kk=>$vv){
					$f = strtolower($fields[$kk]);
					if($f!='id'){
					switch($fieldstype[$kk]){
						case 6:
							$insert[$f] = strtotime($vv);
						break;
						default:
							$insert[$f] = $vv;
						break;
						}
						
					}
				}
				M($excel_molds_biaoshi)->add($insert);
				$insert = null;
				
				
			}
			$id = M('plugins')->getField(['filepath'=>'excel'],'id');
			Success('导入成功！',U('plugins/setconf',['id'=>$id]));
			
		}
		
		
	}
	
	//导出
	function output(){
		$output_molds = $this->frparam('output_molds',1);
		if(defined('DB_TYPE') && DB_TYPE=='sqlite'){
			//sqlite
			$sql="pragma table_info(".DB_PREFIX.$output_molds.")";
			$data = M($output_molds)->findSql($sql);
			$allow = [];
			foreach($data as $v){
				$allow[] = $v['name'];
				
			}
		}else{
			//$sql="select COLUMN_NAME from information_schema.COLUMNS where table_name = '".DB_PREFIX.$molds."' and table_schema = '".DB_PREFIX.$molds."';";
			$sql = "SHOW FULL COLUMNS FROM ".DB_PREFIX.$output_molds;
			$data = M($output_molds)->findSql($sql);
			$allow = [];
			foreach($data as $v){
				$allow[] = $v['Field'];
				
			}
		}
		$this->allowfields = $allow;
		$this->output_molds = $output_molds;
		
		$this->display('excel-output');
	}
	
	function outputover(){
		$molds = $this->frparam('molds',1);
		$fields = $this->frparam('fields',2);
		$table = $this->frparam('title',2);
		$fieldstype = $this->frparam('fieldstype',2);
		$f = implode(',',$fields);
		
		$count = count($fields);
		
		extendFile('excel/PHPExcel.php');
		$excel = new \PHPExcel();
		//Excel表格式--根据你要到处的字段来定
		$letter_array = array('A','B','C','D','E','F','F','G','H','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
		for($i=0;$i<$count;$i++){
			if($i<26){
				$letter[] = $letter_array[$i];
			}else if($i<52){
				$letter[] = 'A'.$letter_array[$i-26];
			}else{
				$letter[] = 'B'.$letter_array[$i-52];
			}
			
		}
		$classtypedata = classTypeData();
		foreach($classtypedata as $k=>$v){
			$classtypedata[$k]['children'] = get_children($v,$classtypedata);
		}
		//表头数组
		$tableheader = $table;
		//填充表头信息
		for($i = 0;$i < count($tableheader);$i++) {
			$excel->getActiveSheet()->setCellValue("$letter[$i]1","$tableheader[$i]");
		}
		//查询数据
		$sql=" select ".$f." from ".DB_PREFIX.$molds." where 1=1 ;";
		$data=M($molds)->findSql($sql);//这个是系统的查询方法
		for ($i = 1;$i <= count($data) + 1;$i++) {
			if($i==1){
				$j = 0;
				foreach ($table[$i - 1] as $key=>$value) {
					$excel->getActiveSheet()->setCellValue("$letter[$j]$i","$value");
					$j++;
				}	
			}else{
				/**
				<option value="1">文本</option>
						<option value="2">图片</option>
						<option value="3">图集</option>
						<option value="13">栏目tid</option>
						<option value="4">时间(年/月/日)</option>
						<option value="14">时间(年/月/日 H:i:s)</option>
						<option value="5">时间戳(一串数字)</option>
						<option value="6">附件</option>
						<option value="7">单选</option>
						<option value="8">多选</option>
						<option value="9">1是0否</option>
						<option value="10">0是1否</option>
						<option value="11">1是2否</option>
						<option value="12">1否2是</option>
						
				
				*/
				$j = 0;
				foreach ($data[$i - 2] as $key=>$value) {
					//$excel->getActiveSheet()->setCellValue("$letter[$j]$i","$value");
					
					
					
						//$excel->getActiveSheet()->setCellValue("$letter[$j]$i","$value");
						$key1 = array_search($key,$fields);
						switch($fieldstype[$key1]){
							case 1:
							case 5:
							$excel->getActiveSheet()->setCellValue("$letter[$j]$i","$value");
							break;
							case 3:
							if($value){
								$value = explode('||',$value);
								$s = [];
								foreach($value as $vv){
									if(strpos($vv,'http')!==false){
										$s[]=$vv;
									}else{
										$s[]=get_domain().$vv;
									}
								}
								$value = implode(',',$s);
							}
							$excel->getActiveSheet()->setCellValue("$letter[$j]$i","$value");
							break;
							
							case 2:
							if($value){
								if(strpos($value,'http')!==false){
									$image = $value;
								}else if(file_exists('.'.$value)){
									$image = '.'.$value;
									
								}
								$objDrawing = new \PHPExcel_Worksheet_Drawing();
								$objDrawing->setPath($image);
								// 设置图片的宽度
								$objDrawing->setHeight(50);
								$objDrawing->setWidth(50);
								$objDrawing->setCoordinates($letter[$j] . $i);
								$objDrawing->setWorksheet($excel->getActiveSheet());
							}
							
							break;
							case 15:
							if($value){
								$value = strpos($value,'http')!==false ? $value : get_domain().$value;
							}
							
							$excel->getActiveSheet()->setCellValue("$letter[$j]$i","$value");
							break;
							case 4:
							$value = date('Y年m月d日',$value);
							$excel->getActiveSheet()->setCellValue("$letter[$j]$i","$value");
							break;
							case 14:
							$value = date('Y年m月d日 H:i:s',$value);
							$excel->getActiveSheet()->setCellValue("$letter[$j]$i","$value");
							break;
							
							case 6:
							if(strpos($value,'http')===false){
								$value = get_domain().$value;
							}
							$excel->getActiveSheet()->setCellValue("$letter[$j]$i","$value");
							break;
							case 7:
							$value = get_key_field_select($value,$molds,$key);
							$excel->getActiveSheet()->setCellValue("$letter[$j]$i","$value");
							break;
							case 8:
							$value = get_key_field_select($value,$molds,$key);
							$value = implode(',',$value);
							$excel->getActiveSheet()->setCellValue("$letter[$j]$i","$value");
							break;
							case 9:
							$value = $value==1?'是':'否';
							$excel->getActiveSheet()->setCellValue("$letter[$j]$i","$value");
							break;
							case 9:
							case 11:
							$value = $value==1?'是':'否';
							$excel->getActiveSheet()->setCellValue("$letter[$j]$i","$value");
							break;
							case 10:
							case 12:
							$value = $value==1?'否':'是';
							$excel->getActiveSheet()->setCellValue("$letter[$j]$i","$value");
							break;
							case 13:
							$value = $classtypedata[$value]['classname'];
							$excel->getActiveSheet()->setCellValue("$letter[$j]$i","$value");
							break;
							
						}
						
						
					
					
					$excel->getActiveSheet()->getRowDimension($i)->setRowHeight(50);
					
					$j++;
				}	
			}
			
		}
		 
		//创建Excel输入对象，这里指定生成Excel5
		$write = new \PHPExcel_Writer_Excel5($excel);
		$excelname=time();//设置导出的Excel表名
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
		header("Content-Type:application/force-download");
		header("Content-Type:application/vnd.ms-execl");
		header("Content-Type:application/octet-stream");
		header("Content-Type:application/download");;
		header('Content-Disposition:attachment;filename="'.$excelname.'.xls"');
		header("Content-Transfer-Encoding:binary");
		$write->save('php://output');
		
	}
	
	
	
	
}