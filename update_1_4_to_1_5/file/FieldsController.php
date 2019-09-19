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

class FieldsController extends CommonController
{
	
	function index(){
		$page = new Page('Fields');
		$sql = '1=1';
		if($this->frparam('molds',1)==''){
			Error('请选择模块！');
		}
		
		$sql = ['molds'=>$this->frparam('molds',1)];
		$this->molds = M('Molds')->find(array('biaoshi'=>$this->frparam('molds',1)));
		$data = $page->where($sql)->orderby('orders desc,id asc')->page($this->frparam('page',0,1))->go();
		$pages = $page->pageList();
		$this->pages = $pages;
		$this->lists = $data;
		$this->sum = $page->sum;
		
		$this->display('fields-list');
		
		
	}

	function addFields(){
		
		if($this->frparam('go',1)==1){
			
			$data = $this->frparam();
			$data['field'] = strtolower(format_param($data['field'],1));
			if($data['fieldname']=='' || $data['field']==''){
				JsonReturn(array('code'=>1,'msg'=>'字段名和字段标识不能为空！'));
			}
			
			//检测是否存在该模块
			if(M('Fields')->find(array('field'=>$data['field'],'molds'=>$data['molds']))){
				JsonReturn(array('code'=>1,'msg'=>'字段标识已存在！'));
			}
			$sql = "select count(*) as n from information_schema.columns where table_name = '".DB_PREFIX.$data['molds']."' and TABLE_SCHEMA='".DB_PREFIX.$data['molds']."' and column_name = '".$data['field']."'";
			$check = M()->findSql($sql);
			if($check[0]['n']){
				JsonReturn(array('code'=>1,'msg'=>'字段标识已存在！'));
			}
			
			$data['tids'] = ($data['tids']!='')?(','.$data['tids'].','):$data['tids'];
			$sql = "ALTER TABLE ".DB_PREFIX.$data['molds']." ADD ".$data['field']." ";
			$data['fieldlong'] = $this->frparam('fieldlong_'.$data['fieldtype'],1);
			switch($data['fieldtype']){
				case 1:
				case 2:
				$sql .= "VARCHAR(".$data['fieldlong'].") CHARACTER SET utf8 default ";
				if($data['vdata']){
					$sql .=  "'".$data['vdata']."'";
				}else{
					$sql .= ' NULL ';
				}
				break;
				case 3:
				$sql .= "TEXT CHARACTER SET utf8 default ";
				$sql .= ' NULL ';
				
				break;
				case 4:
				if($data['fieldlong']>11 || $data['fieldlong']<=0){
					JsonReturn(array('code'=>1,'msg'=>'字段长度不对！'));
				}
				$sql .= "INT(".$data['fieldlong'].") DEFAULT ";
				if($data['vdata']){
					$sql .=  "'".$data['vdata']."'";
				}else{
					$sql .= " '0' NOT NULL ";
				}
				break;
				case 11:
				if($data['fieldlong']!=11){
					JsonReturn(array('code'=>1,'msg'=>'字段长度不对,时间属性必须长度为11'));
				}
				$sql .= "INT(".$data['fieldlong'].") DEFAULT ";
				if($data['vdata']){
					$sql .=  "'".$data['vdata']."'";
				}else{
					$sql .= " '0' NOT NULL ";
				}
				break;
				case 5:
				case 9:
				$sql .= "VARCHAR(".$data['fieldlong'].") CHARACTER SET utf8 default ";
				if($data['vdata']){
					$sql .=  "'".$data['vdata']."'";
				}else{
					$sql .= " NULL ";
				}
				break;
				case 6:
				case 10:
				$sql .= "VARCHAR(".$data['fieldlong'].") CHARACTER SET utf8 default ";
				if($data['vdata']){
					$sql .=  "'".$data['vdata']."'";
				}else{
					$sql .= " NULL ";
				}
				break;
				case 7:
				case 8:
				case 12:
				$data['body'] = $this->frparam('body_'.$data['fieldtype'],1);
				$sql .= "VARCHAR(".$data['fieldlong'].") CHARACTER SET utf8 default ";
				if($data['vdata']){
					$sql .= "'".$data['vdata']."'";
				}else{
					$sql .= " NULL ";
				}
				break;
				case 13:
				if($data['fieldlong']>11 || $data['fieldlong']<=0){
					JsonReturn(array('code'=>1,'msg'=>'字段长度不对！'));
				}
				$sql .= "INT(".$data['fieldlong'].") DEFAULT ";
				if($data['vdata']){
					$sql .=  "'".$data['vdata']."'";
				}else{
					$sql .= " '0' NOT NULL ";
				}
				$data['body'] = $this->frparam('molds_select',1).','.$this->frparam('molds_list_field',1);
				break;
				
			}
			$x = M()->runSql($sql);
			
			$n = M('Fields')->add($data);
			if(!$n){
				//新增字段记录失败，删除新增字段
				$delsql = "ALTER TABLE ".DB_PREFIX.$data['molds']." DROP COLUMN ".$data['field'];
				M()->runSql($delsql);
				JsonReturn(array('code'=>1,'msg'=>'字段创建成功，但是字段表记录失败，请反馈官方解决！'));
			}
			JsonReturn(array('code'=>0,'msg'=>'字段创建成功！'));
			
			
		}
		
		
		$this->classtypes = $this->classtypetree;
		$this->molds = $this->frparam('molds',1);
		$this->display('fields-add');
	}
	

	
	
	public function editFields(){
		
		if($this->frparam('go',1)==1){

			if($this->frparam('id')){
				$data = $this->frparam();
				$data['field'] = strtolower(format_param($data['field'],1));
				if($data['fieldname']=='' || $data['field']==''){
					JsonReturn(array('code'=>1,'msg'=>'字段名和字段标识不能为空！'));
				}
				
				$data['tids'] = ($data['tids']!='')?(','.$data['tids'].','):$data['tids'];
				$old = M('Fields')->find(array('id'=>$this->frparam('id')));
				$data['fieldlong'] = $this->frparam('fieldlong_'.$data['fieldtype'],1);
				//只是更改样式，不更改字段属性
				if($old['field']==$data['field']){
					
					//判断长度是否不同
					if($data['fieldlong']!=$old['fieldlong'] && $data['fieldtype']!=3){
						$sql =  "ALTER TABLE ".DB_PREFIX.$old['molds']." modify column ".$old['field']." ";
						switch($data['fieldtype']){
							case 1:
							case 2:
							case 5:
							case 6:
							case 7:
							case 8:
							case 9:
							case 10:
							case 12:
							$sql.=" varchar(".$data['fieldlong'].") ";
							break;
							break;
							case 4:
							case 11:
							$sql.=" int(".$data['fieldlong'].") ";
							break;
							
						}
						$x = M()->runSql($sql);
						
					}
					if($data['fieldtype']==7 || $data['fieldtype']==8 || $data['fieldtype']==12){
                    	$data['body'] = $this->frparam('body_'.$data['fieldtype'],1);
                    }
					if($data['fieldtype']==13){
						$data['body'] = $this->frparam('molds_select',1).','.$this->frparam('molds_list_field',1);
					}
					if(M('Fields')->update(array('id'=>$this->frparam('id')),$data)){
						JsonReturn(array('code'=>0,'msg'=>'字段修改成功！'));
					}else{
						JsonReturn(array('code'=>1,'msg'=>'字段修改失败！'));
					}
					
				}
				
				$sql = "ALTER TABLE ".DB_PREFIX.$old['molds']." change ".$old['field']." ".$data['field']." ";
				
				switch($data['fieldtype']){
					case 1:
					case 2:
					
					$sql .= "VARCHAR(".$data['fieldlong'].") CHARACTER SET utf8 default ";
					if($data['vdata']){
						$sql .=  "'".$data['vdata']."'";
					}else{
						$sql .= ' NULL ';
					}
					break;
					case 3:
					$sql .= "TEXT CHARACTER SET utf8 default ";
					$sql .= ' NULL ';
					
					break;
					case 4:
					if($data['fieldlong']>11 || $data['fieldlong']<=0){
						JsonReturn(array('code'=>1,'msg'=>'字段长度不对！'));
					}
					$sql .= "INT(".$data['fieldlong'].") DEFAULT ";
					if($data['vdata']){
						$sql .=  "'".$data['vdata']."'";
					}else{
						$sql .= " '0' NOT NULL ";
					}
					break;
					case 11:
					if($data['fieldlong']!=11){
						JsonReturn(array('code'=>1,'msg'=>'字段长度不对,时间属性必须长度为11'));
					}
					$sql .= "INT(".$data['fieldlong'].") DEFAULT ";
					if($data['vdata']){
						$sql .=  "'".$data['vdata']."'";
					}else{
						$sql .= " '0' NOT NULL ";
					}
					break;
					case 5:
					case 9:
					$sql .= "VARCHAR(".$data['fieldlong'].") CHARACTER SET utf8 default ";
					if($data['vdata']){
						$sql .=  "'".$data['vdata']."'";
					}else{
						$sql .= ' NULL ';
					}
					break;
					case 6:
					case 10:
					$sql .= "VARCHAR(".$data['fieldlong'].") CHARACTER SET utf8 default ";
					if($data['vdata']){
						$sql .=  "'".$data['vdata']."'";
					}else{
						$sql .= ' NULL ';
					}
					break;
					case 7:
					case 8:
					case 12:
					$sql .= "VARCHAR(".$data['fieldlong'].") CHARACTER SET utf8 default ";
					if($data['vdata']){
						$sql .=  "'".$data['vdata']."'";
					}else{
						$sql .= ' NULL ';
					}
					$data['body'] = $this->frparam('body_'.$data['fieldtype'],1);
					break;
					case 13:
					if($data['fieldlong']>11 || $data['fieldlong']<=0){
						
						JsonReturn(array('code'=>1,'msg'=>'字段长度不对！'));
					}
					$sql .= "INT(".$data['fieldlong'].") DEFAULT ";
					if($data['vdata']){
						$sql .=  "'".$data['vdata']."'";
					}else{
						$sql .= " '0' NOT NULL ";
					}
					$data['body'] = $this->frparam('molds_select',1).','.$this->frparam('molds_list_field',1);
					break;
					
				}
				$x = M()->runSql($sql);
				
				if(M('Fields')->update(array('id'=>$this->frparam('id')),$data)){
					JsonReturn(array('code'=>0,'msg'=>'字段修改成功！'));
					exit;
				}else{
					JsonReturn(array('code'=>1,'msg'=>'字段修改失败！'));
					exit;
				}
			}
			
			
			
		}
		if($this->frparam('id')){
			$this->data = M('Fields')->find(array('id'=>$this->frparam('id')));
		}
		
		$this->classtypes = $this->classtypetree;
		$this->display('fields-edit');
		
	}
	
	function get_fields(){
		$tid = $this->frparam('tid');
		$sql = array();
		if($tid!=0){
			$sql[] = " tids like '%,".$tid.",%' "; 
		}
		$molds = $this->frparam('molds',1);
		$id = $this->frparam('id');
		if($id){
			$data = M($molds)->find(array('id'=>$id));
		}else{
			$data = array();
		}
		$sql[] = " molds = '".$molds."' ";
		$sql = implode(' and ',$sql);
		$fields_list = M('Fields')->findAll($sql,'orders desc,id asc');
		$l = '';
		foreach($fields_list as $k=>$v){
			if(!array_key_exists($v['field'],$data)){
				//使用默认值
				$data[$v['field']] = $v['vdata'];
			}
			switch($v['fieldtype']){
				case 1:
				$l .= '<div class="layui-form-item">
                    <label for="'.$v['field'].'" class="layui-form-label">
                        <span class="x-red"></span>'.$v['fieldname'].'
                    </label>
                    <div class="layui-input-block">
                        <input type="text" id="'.$v['field'].'" value="'.$data[$v['field']].'" name="'.$v['field'].'" ';
				if($v['ismust']==1){
					$l.=' required="" lay-verify="required" ';
				}		
                $l .=  'autocomplete="off" class="layui-input">
                    </div>
					<div class="layui-form-mid layui-word-aux">
					  '.$v['tips'].'
					</div>
					
                </div>';
				break;
				case 2:
				$l .= '<div class="layui-form-item  layui-form-text">
                    <label for="'.$v['field'].'" class="layui-form-label">
                        <span class="x-red"></span>'.$v['fieldname'].'
                    </label>
                    <div class="layui-input-block">
                        <textarea  class="layui-textarea" id="'.$v['field'].'"  name="'.$v['field'].'" ';
				if($v['ismust']==1){
					$l.=' required="" lay-verify="required" ';
				}		
                $l .=  '>'.$data[$v['field']].'</textarea>
                    </div>
					<div class="layui-form-mid layui-word-aux">
					  '.$v['tips'].'
					</div>
					
                </div>';
				break;
				case 3:
				$rd = time();
				$l .= '<div class="layui-form-item layui-form-text">
							<label for="'.$v['field'].'" class="layui-form-label">
								<span class="x-red">*</span>'.$v['fieldname'].'
							</label>
							<div class="layui-input-block" style="width:100%;">
							<script id="'.$v['field'].$rd.'" name="'.$v['field'].'" type="text/plain" style="width:100%;height:400px;">'.$data[$v['field']].'</script>
								
							</div>
						</div>
						<script>
						$(document).ready(function(){
						var ue_'.$v['field'].$rd.' = UE.getEditor("'.$v['field'].$rd.'",{';
					if($this->webconf['ueditor_config']!=''){
						$l.= 'toolbars : [['.$this->webconf['ueditor_config'].']]';
					}		
						$l.='}		
						);	
						});
						</script>';
				
				break;
				case 4:
				$l .= '<div class="layui-form-item">
                    <label for="'.$v['field'].'" class="layui-form-label">
                        <span class="x-red"></span>'.$v['fieldname'].'
                    </label>
                    <div class="layui-input-block">
                        <input type="number" id="'.$v['field'].'" value="'.$data[$v['field']].'" name="'.$v['field'].'" ';
				if($v['ismust']==1){
					$l.=' required="" lay-verify="required" ';
				}		
                $l .=  'autocomplete="off" class="layui-input">
                    </div>
					<div class="layui-form-mid layui-word-aux">
					  '.$v['tips'].'
					</div>
					
                </div>';
				break;
				case 11:
				$laydate = ($data[$v['field']]=='' || $data[$v['field']]==0)?time():$data[$v['field']];
				$l .= '<div class="layui-form-item">
                    <label for="'.$v['field'].'" class="layui-form-label">
                        <span class="x-red"></span>'.$v['fieldname'].'
                    </label>
                    <div class="layui-input-block">
                        <input id="laydate_'.$v['field'].'" value="'.date('Y-m-d H:i:s',$laydate).'" name="'.$v['field'].'" ';
				if($v['ismust']==1){
					$l.=' required="" lay-verify="required" ';
				}		
                $l .=  'autocomplete="off" class="layui-input">
                    </div>
					<div class="layui-form-mid layui-word-aux">
					  '.$v['tips'].'
					</div>
                </div><script>
layui.use("laydate", function(){
  var laydate = layui.laydate;
  laydate.render({elem: "#laydate_'.$v['field'].'",type:"datetime" });});</script>';
				break;
				case 5:
				$l .= '<div class="layui-form-item">
                    <label for="'.$v['field'].'" class="layui-form-label">
						<span class="x-red">*</span>'.$v['fieldname'].'  
                    </label>
					
                    <div class="layui-input-inline">
                      <div class="site-demo-upbar">
                      <img src="'.$data[$v['field']].'"  width="400"  id="'.$v['field'].'_img" />
					  <input name="'.$v['field'].'" type="text" class="layui-input" id="'.$v['field'].'" ';
				if($v['ismust']==1){
					$l.=' required="" lay-verify="required" ';
				}
				$l  .=	'value="'.$data[$v['field']].'" />
						<button type="button" class="layui-btn" id="LAY_'.$v['field'].'_upload">
						  <i class="layui-icon">&#xe67c;</i>上传图片
						</button>
                      </div>
                    </div>
					<div class="layui-form-mid layui-word-aux">
					  '.$v['tips'].'
					</div>
                </div>
				<script>
				
				layui.use("upload", function(){
					  var upload_'.$v['field'].' = layui.upload;
					   
					  //执行实例
					  var uploadInst = upload_'.$v['field'].'.render({
						elem: "#LAY_'.$v['field'].'_upload" //绑定元素
						,url: "'.U('Common/uploads').'" //上传接口
						,accept:"images"
						,acceptMime:"image/*"
						,done: function(res){
						  
							if(res.code==0){
								 $("#'.$v['field'].'_img").attr("src","/"+res.url);
								 $("#'.$v['field'].'").val("/"+res.url);
							}else{
								 layer.alert(res.error, {icon: 5});
							}
						}
						,error: function(){
						  //请求异常回调
						  layer.alert("上传异常！");
						}
					  });
					});
				</script>';
				break;
				case 6:
				$l .= '<div class="layui-form-item">
                    <label for="'.$v['field'].'" class="layui-form-label">
						<span class="x-red">*</span>'.$v['fieldname'].'  
                    </label>
                    <div class="layui-input-inline">
                      <div class="site-demo-upbar">
                     <span class="preview_'.$v['field'].'" >';
				if($data[$v['field']]!=''){
					foreach(explode('||',$data[$v['field']]) as $vv){
						$l.='<span class="upload-icon-img" ><div class="upload-pre-item"><img src="'.$vv.'" class="img" max-width="200px" ><input name="'.$v['field'].'_urls[]" type="text" class="layui-input"  value="'.$vv.'" /><i  class="layui-icon delete_file" >&#xe640;</i></div></span>';
					}
				}	 
				$l .= '</span>
						<button type="button" class="layui-btn" id="LAY_'.$v['field'].'_upload">
						  <i class="layui-icon">&#xe67c;</i>上传图片
						</button>
                      </div>
                    </div>
					<div class="layui-form-mid layui-word-aux">
					  '.$v['tips'].'
					</div>
                </div>
				<script>
				
				layui.use("upload", function(){
					  var upload_'.$v['field'].' = layui.upload;
					   
					  //执行实例
					  var uploadInst = upload_'.$v['field'].'.render({
						elem: "#LAY_'.$v['field'].'_upload" //绑定元素
						,url: "'.U('Common/uploads').'" //上传接口
						,accept:"images"
						,multiple: true
						,acceptMime:"image/*"
						,before: function(obj){ 		
							layer.load(); //上传loading
						  }
						,done: function(res){
							layer.closeAll("loading"); //关闭loading
							if(res.code==0){
							
							$(".preview_'.$v['field'].'").append(\'<span class="upload-icon-img" ><div class="upload-pre-item"><img src="/\' + res.url + \'" class="img" max-width="200px" ><input name="'.$v['field'].'_urls[]" type="text" class="layui-input"  value="/\' + res.url + \'" /><i  class="layui-icon delete_file" >&#xe640;</i></div></span>\');
								
								
							}else{
								 layer.alert(res.error, {icon: 5});
							}
						}
						,error: function(){
						  //请求异常回调
						  layer.alert("上传异常！");
						}
					  });
					});
				</script>';
				break;
				case 7:
				$l .= '<div class="layui-form-item">
                    <label for="'.$v['field'].'" class="layui-form-label">
                        <span class="x-red">*</span>'.$v['fieldname'].'  
                    </label>
                    <div class="layui-input-inline">
						<select name="'.$v['field'].'" id="'.$v['field'].'" >';
				foreach(explode(',',$v['body']) as $vv){
					$s=explode('=',$vv);
					$l.='<option value="'.$s[1].'" ';
					if($data[$v['field']]==$s[1]){
						$l.='selected="selected"';
					}
					$l.='>'.$s[0].'</option>';
				}
					$l.=  '</select>
                    </div>
					<div class="layui-form-mid layui-word-aux">
					  '.$v['tips'].'
					</div>
                </div><script>
							layui.use("form", function () {
								var form_'.$v['field'].' = layui.form;
								form_'.$v['field'].'.render();
							});
							 
						</script>';
				break;
				case 12:
				$l .= '<div class="layui-form-item" pane>
                    <label for="'.$v['field'].'" class="layui-form-label">
                        <span class="x-red">*</span>'.$v['fieldname'].'  
                    </label>
                    <div class="layui-input-inline">';
				foreach(explode(',',$v['body']) as $vv){
					$s=explode('=',$vv);
					$l.='<input type="radio" name="'.$v['field'].'" value="'.$s[1].'" title="'.$s[0].'" ';
					if($data[$v['field']]==$s[1]){
						$l.='checked="checked"';
					}
					$l.=' >';
				}
					$l.='</div>
					<div class="layui-form-mid layui-word-aux">
					  '.$v['tips'].'
					</div>
					</div><script>
							layui.use("form", function () {
								var form_'.$v['field'].' = layui.form;
								form_'.$v['field'].'.render();
							});
							 
						</script>';
				break;
				case 8:
				$l .= '<div class="layui-form-item">
						<label for="'.$v['field'].'" class="layui-form-label">
							<span class="x-red">*</span>'.$v['fieldname'].'  
						</label>
						<div class="layui-input-block">';
				foreach(explode(',',$v['body']) as $vv){
					$s=explode('=',$vv);
					$l.='<input type="checkbox" title="'.$s[0].'" name="'.$v['field'].'[]" value="'.$s[1].'" ';
					if(strpos($data[$v['field']],','.$s[1].',')!==false){
						$l.='checked="checked"';};
					$l.='>';
				}
				$l 	.= '</div>
					<div class="layui-form-mid layui-word-aux">
					  '.$v['tips'].'
					</div>
					  </div>
					  <script>
							layui.use("form", function () {
								var form_'.$v['field'].' = layui.form;
								form_'.$v['field'].'.render();
							});
							 
						</script>';
				
				break;
				case 9:
				$l .= '<div class="layui-form-item">
                    <label for="'.$v['field'].'" class="layui-form-label">
						<span class="x-red">*</span>'.$v['fieldname'].'  
                    </label>
					
                    <div class="layui-input-inline">
                      <div class="site-demo-upbar">
                      
					  <input name="'.$v['field'].'" type="text" class="layui-input" id="'.$v['field'].'" ';
				if($v['ismust']==1){
					$l.=' required="" lay-verify="required" ';
				}
				$l  .=	'value="'.$data[$v['field']].'" />
						<button type="button" class="layui-btn" id="LAY_'.$v['field'].'_upload">
						  <i class="layui-icon">&#xe67c;</i>上传附件
						</button>

					  
                      </div>
                    </div>
					<div class="layui-form-mid layui-word-aux">
					  '.$v['tips'].'
					</div>
                </div>
				<script>
				
				layui.use("upload", function(){
					  var upload_'.$v['field'].' = layui.upload;
					   
					  //执行实例
					  var uploadInst = upload_'.$v['field'].'.render({
						elem: "#LAY_'.$v['field'].'_upload" //绑定元素
						,url: "'.U('Common/uploads').'" //上传接口
						,accept:"file"
						,exts: "'.$this->webconf['fileType'].'"
						,done: function(res){
							if(res.code==0){
								
								 $("#'.$v['field'].'").val("/"+res.url);
							}else{
								 layer.alert(res.error, {icon: 5});
							}
						}
						,error: function(){
						  //请求异常回调
						  layer.alert("上传异常！");
						}
					  });
					});
				</script>';
				break;
				case 10:
				$l .= '<div class="layui-form-item">
                    <label for="'.$v['field'].'" class="layui-form-label">
						<span class="x-red">*</span>'.$v['fieldname'].'  
                    </label>
					
                    <div class="layui-input-inline">
                      <div class="site-demo-upbar">
                     <span class="preview_'.$v['field'].'" >';
				if($data[$v['field']]!=''){
					foreach(explode('||',$data[$v['field']]) as $vv){
						$l.='<span class="upload-icon-img" ><div class="upload-pre-item"><i class="layui-icon layui-icon-file"></i><input type="text" value="'.$vv.'" name="'.$v['field'].'_urls[]" class="layui-input" /><i   class="layui-icon delete_file">&#xe640;</i></div></span>';
					}
				}	
				$l	.= '</span>
					  
						<button type="button" class="layui-btn" id="LAY_'.$v['field'].'_upload">
						  <i class="layui-icon">&#xe67c;</i>上传附件
						</button>
                      </div>
                    </div>
					<div class="layui-form-mid layui-word-aux">
					  '.$v['tips'].'
					</div>
                </div>
				<script>
				
				layui.use("upload", function(){
					  var upload_'.$v['field'].' = layui.upload;
					   
					  //执行实例
					  var uploadInst = upload_'.$v['field'].'.render({
						elem: "#LAY_'.$v['field'].'_upload" //绑定元素
						,url: "'.U('Common/uploads').'" //上传接口
						,multiple: true
						,accept:"file"
						,exts: "'.$this->webconf['fileType'].'"
						,before: function(obj){ 		
							layer.load(); //上传loading
						  }
						,done: function(res){
							layer.closeAll("loading"); //关闭loading
							if(res.code==0){
							var fileurl = $("#'.$v['field'].'").val();
							$(".preview_'.$v['field'].'").append(\'<span class="upload-icon-img" ><div class="upload-pre-item"><i class="layui-icon layui-icon-file"></i><input type="text" value="/\'+res.url+\'" name="'.$v['field'].'_urls[]" class="layui-input delete_file" /><i   class="layui-icon">&#xe640;</i></div></span>\');
								
							}else{
								 layer.alert(res.error, {icon: 5});
							}
						}
						,error: function(){
						  //请求异常回调
						  layer.alert("上传异常！");
						}
					  });
					});
				</script>';
				break;
				case 13:
				//tid,field
				$l .= '<div class="layui-form-item">
                    <label for="'.$v['field'].'" class="layui-form-label">
                        <span class="x-red">*</span>'.$v['fieldname'].'  
                    </label>
                    <div class="layui-input-inline">
						<select name="'.$v['field'].'" id="'.$v['field'].'" >';
						$body = explode(',',$v['body']);
				$biaoshi = M('molds')->getField(['id'=>$body[0]],'biaoshi');
				$datalist = M($biaoshi)->findAll();
				foreach($datalist as $vv){
					$l.='<option value="'.$vv['id'].'" ';
					if($data[$v['field']]==$vv['id']){
						$l.='selected="selected"';
					}
					$l.='>'.$vv[$body[1]].'</option>';
				}
					$l.=  '</select>
                    </div>
					<div class="layui-form-mid layui-word-aux">
					  '.$v['tips'].'
					</div>
                </div><script>
							layui.use("form", function () {
								var form_'.$v['field'].' = layui.form;
								form_'.$v['field'].'.render();
							});
							 
						</script>';
				break;
				
				
			}
			
		}
		echo $l;
	}
	
	
	
	
	
	function deleteFields(){
		$id = $this->frparam('id');
		if($id){
			
			$fields = M('fields')->find(array('id'=>$id));
			if(M('Fields')->delete('id='.$id)){
				$sql = "ALTER TABLE ".DB_PREFIX.$fields['molds']." DROP COLUMN ".$fields['field'];
				$x = M()->runSql($sql);
				
				JsonReturn(array('code'=>0,'msg'=>'删除成功！'));
			}else{
				JsonReturn(array('code'=>1,'msg'=>'删除失败！'));
			}
		}
	}
	
	
	
	
	
	
	
	
	
	
	
}