<?php

// +----------------------------------------------------------------------
// | 在线修改文件插件 
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2099  All rights reserved.
// +----------------------------------------------------------------------
// | Author: 留恋风 <2581047041@qq.com>
// +----------------------------------------------------------------------
// | Date：2019/04/23
// +----------------------------------------------------------------------

/**

功能说明：
1、可在线修改HTML及其他js、css文件
2、可新增文件及文件夹、重命名和删除文件，除图片文件外
3、不可上传文件--未来可能添加(或者你们自己添加)
4、保存编辑的文件请用 Ctrl + S 按键组合,等待执行结果

**/

 
session_start();


//设定key秘钥参数名
$keyname = 'key';
//设定path路径参数名
$pathname = 'path';

// if(isset($_GET[$pathname])){
	// $curr_file = dirname(dirname(__FILE__)).$_GET[$pathname]; //默认编辑当前文件
// }else{
	// $curr_file = dirname(dirname(__FILE__)).'../../../../Home/'; //默认编辑当前文件
// }
if(!isset($_GET[$pathname])){
	exit('非法访问！');
}
$curr_file = dirname(dirname(__FILE__)).'../../../../Home/'.$_GET[$pathname];

$curr_file_path = __FILE__;
$ace = 'ace.js'; //编辑器核心js
$tip['core'] = 'alertify.core.min.css';
$tip['css'] = 'alertify.default.min.css';
$tip['js'] = 'alertify.min.js';
$jquery = 'jquery.min.js';

	
	if(isset($_GET[$keyname])){
		if(!file_exists($_GET[$keyname].'.txt')){
			exit('非法使用！');
		}
		
		
	}else{
		exit('非法使用！');
	}




//文件后缀名对应的语法解析器
$lng = array(
	'as' => 'actionscript', 'js' => 'javascript',
	'php' => 'php', 'css' => 'css', 'html' => 'html',
	'htm' => 'html', 'ini' => 'ini', 'json' => 'json',
	'jsp' => 'jsp', 'txt' => 'text', 'sql' => 'mysql',
	'xml' => 'xml', 'yaml' => 'yaml', 'py' => 'python',
	'md' => 'markdown', 'htaccess' => 'apache_conf',
	'bat' => 'batchfile', 'go' => 'golang',
);


//重新载入到本页面
function reload() {
    $file = pathinfo(__FILE__, PATHINFO_BASENAME);
    die(header("Location: {$file}"));
}

//判断请求是否是ajax请求
function is_ajax() {
	$flag = false;
	if ( isset($_SERVER['HTTP_X_REQUESTED_WITH']) ) {
		$flag = strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
	}
    return $flag;
}

//销毁SESSION和COOKIE
function exterminate() {
	$_SESSION = array();
	foreach ( $_COOKIE as $key ) {
		setcookie($key, null);
	}
	session_destroy();
	$_COOKIE = array();
    return true;
}

//获取一个目录下的文件列表
function list_dir($path, $type = 'array') {
	$flag = false;
	$lst = array('dir'=>array(), 'file'=>array());
	$base = !is_dir($path) ? dirname($path) : $path;
	$tmp = scandir($base);
	foreach ( $tmp as $k=>$v ) {
		//过滤掉上级目录,本级目录和程序自身文件名
		if ( !in_array($v, array('.', '..')) ) {
			$file = $full_path = rtrim($base, '/').DIRECTORY_SEPARATOR.$v;
			if ( $full_path == __FILE__ ) {
				continue; //屏蔽自身文件不在列表出现
			}
			$file = str_replace(dirname(__FILE__), '', $file);
			$file = str_replace("\\", '/', $file); //过滤win下的路径
			$file = str_replace('//', '/', $file); //过滤双斜杠
			if ( is_dir($full_path) ) {
				if ( 'html' === $type ) {
					$v = '<li class="dir" path="'.$file
					.'" onclick="load();"><span>'.$v.'</span></li>';
				}
				array_push($lst['dir'], $v);
			} else {
				if ( 'html' === $type ) {
					$v = '<li class="file" path="'.$file
					.'" onclick="load()"><span>'.$v.'</span></li>';
				}
				array_push($lst['file'], $v);
			}
		}
	}
	$lst = array_merge($lst['dir'], $lst['file']);
	$lst = array_filter($lst);
	$flag = $lst;
	if ( 'html' === $type ) {
		$flag = '<ul>'. implode('', $lst) .'</ul>';
	}
	return $flag;
}

//递归删除一个非空目录
function deldir($dir) {
    $dh = opendir($dir);
    while ( $file = readdir($dh) ) {
        if ( $file != '.' && $file != '..' ) {
            $fullpath = $dir.'/'.$file;
            if ( !is_dir($fullpath) ) {
                unlink($fullpath);
            } else {
                deldir($fullpath);
            }
        }
    }
    return rmdir($dir);
}

//退出登录
if ( isset($_GET['logout']) ) {
	if ( exterminate() ) {
		reload();
	}
}

//ajax输出文件内容
if ( is_ajax() && isset($_POST['file']) ) {
	$file = $_POST['file'];
	$ext = pathinfo($file, PATHINFO_EXTENSION);
	$mode = isset($lng[$ext]) ? $lng[$ext] : false;
	die(json_encode(array(
		'file' => $file, 'html' => file_get_contents($file),
		'mode' => $mode,		
	)));
}

//ajax输出目录列表
if (  is_ajax() && isset($_POST['dir']) ) {
	$dir = $_POST['dir'];
	$list_dir = list_dir($dir, 'html');
	die(json_encode(array(
		'dir' => $dir, 'html' => $list_dir,
	)));
}

//ajax保存文件
if (  is_ajax() && isset($_POST['action']) ) {
	$arr = array('result'=>'error', 'msg'=>'文件保存失败！');
	$content = $_POST['content'];
	if ( 'save_file' === $_POST['action'] ) {
		if ( isset($_POST['file_path']) ) {
			$file = $_POST['file_path'];
			file_put_contents($file, $content);
		} else {
			$file = __FILE__;
		}
		
		
		$arr['result'] = 'success';
		$arr['msg'] = '保存成功！';
	}
	die(json_encode($arr));
}

//ajax删除文件或文件夹
if (  is_ajax() && isset($_POST['del']) ) {
	$path = $_POST['del'];
	$arr = array('result'=>'error', 'msg'=>'删除操作失败！');
	if ( $_POST['del'] && $path ) {
        $flag = is_dir($path) ? deldir($path) : unlink($path);
    	if ( $flag ) {
    	   $arr['msg'] = '删除操作成功！';
    	   $arr['result'] = 'success';
    	}
	}
	die(json_encode($arr));
}

//ajax新建文件或文件夹
if (  is_ajax() && isset($_POST['create']) ) {
	$flag = false;
	$arr = array('result'=>'error', 'msg'=>'操作失败！');
	if ( isset($_POST['target']) ) {
	    $target = $_POST['target'];
	    $target = is_dir($target) ? $target : dirname($target);
	}
	if ( $_POST['create'] && $target ) {
        $base_name = pathinfo($_POST['create'], PATHINFO_BASENAME);
        $exp = explode('.', $base_name);
        $full_path = $target.'/'.$base_name;
        $new_path = str_replace(dirname(__FILE__), '', $full_path);
        if ( count($exp) > 1 && isset($lng[array_pop($exp)]) ) {
            file_put_contents($full_path, '');
            $arr['result'] = 'success';
            $arr['msg'] = '新建文件成功！';
            $arr['type'] = 'file';
        } else {
            mkdir($full_path, 0777, true);
            $arr['result'] = 'success';
            $arr['msg'] = '新建目录成功！';
            $arr['type'] = 'dir';
        }
        if ( $base_name && $new_path ) {
            $arr['new_name'] = $base_name;
            $arr['new_path'] = $new_path;
        }
	}
	die(json_encode($arr));
}

//ajax重命名文件或文件夹
if (  is_ajax() && isset($_POST['rename']) ) {
	$arr = array('result'=>'error', 'msg'=>'重命名操作失败！');
	if ( isset($_POST['target']) ) {
	    $target = $_POST['target'];
	}
	if ( $_POST['rename'] ) {
	    $base_name = pathinfo($_POST['rename'], PATHINFO_BASENAME);
	    if ( $base_name ) {
	        $rename = dirname($target).'/'.$base_name;
	        $new_path = str_replace(dirname(__FILE__), '', $rename);
	    }
	}
	if ( $rename && $target && rename($target, $rename) ) {
	   $arr['new_name'] = $base_name;
	   $arr['new_path'] = $new_path;
	   $arr['msg'] = '重命名操作成功！';
	   $arr['result'] = 'success';
	}
	if ( $target == __FILE__ ) {
	    $arr['redirect'] = $new_path;
	}
	die(json_encode($arr));
}

//获取代码文件内容
if(!is_dir($curr_file) && file_exists($curr_file)){
	
	
	
	$code = file_get_contents($curr_file);
	
}else{
	$code = '';
}

$tree = '<ul id="dir_tree">
	<li class="dir" path="'.$curr_file.'" onclick="load()">ROOT'.list_dir($curr_file, 'html').'</li>
</ul>';

//登陆和设置密码共用模版
$first = <<<HTMLSTR
<!DOCTYPE html>
<html><head><meta charset="UTF-8">
<title>【标题】</title>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<style type="text/css" media="screen">
body {
	overflow: hidden; background-color: #2D2D2D; color: #CCCCCC; font-size: 12px; margin: 0;
	font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', 'Consolas', 'source-code-pro', monospace;
}
form { display: none; position: absolute; }
form h5 { font-size: 14px; font-weight: normal; margin: 0; line-height: 2em; }
form input {
    color: #fff; border: 1px solid #369; border-radius: 3px; background: #333; height: 22px;
    line-height: 1.6em; width: 125px; margin-right: 5px; vertical-align: middle;
}
form button {
    line-height: 1.6em; border: 1px solid #369; border-radius: 3px;
    background: #369; color: #fff; vertical-align: middle;
}
</style>
<link rel="stylesheet" href="{$tip['core']}" />
<link rel="stylesheet" href="{$tip['css']}" />
</head>
<body>
	<form method="post">
		<input name="pwd" type="password" /><button type="submit">【动作】</button>
	</form>
	<script src="{$jquery}" type="text/javascript" charset="utf-8"></script>
	<script src="{$ace}" type="text/javascript" charset="utf-8"></script>
	<script src="{$tip['js']}" type="text/javascript"></script>
	<script type="text/javascript">
	var editor = false;
	$(function(){
		$('form').prepend('<h5>'+ document.title +'</h5>');
		$('form').css({
			left: ($(window).width()-$('form').width())/2,
			top: ($(window).height()-$('form').height())/2
		});
		$('form').show();
	});
	</script>
</body></html>
HTMLSTR;



//处理一下html实体
$code = htmlspecialchars($code);

$dir_icon = str_replace(array("\r\n", "\r", "\n"), '',
'data:image/jpg;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAANCAYAAACgu+4kAAAAGXRFWHRTb2Z0d2
FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAQVJREFUeNqkkk1uwjAQhd84bsNP1FUXLCtu0H3XPSoX4Qrd9wR
sCjQEcIY3DiiJUYiqRhp5Mra/92YSUVVgLSW49B7H+NApRh75XkHfFoCG+02tyflUeQTw2y9UYYP8cCStc9SM
PeVA/Sy6Dw555q3au1z+EhBYk1cgO7OSNdaFNT0x5sCkYDha0WPiHZgVqPzLO+8seai6E2jed42bCL06tNyEH
AX9kv3jh3HqH7BctFWLMOmAbcg05mHK5+sQpd1HYijN47zcDUCShGEHtzxtwQS9WTcAQmJROrJDLXQB9s1Tu6
MtRED4bwsHLnUzxEeKac3+GeP6eo8yevhjC3F1qC4CDAAl3HwuyNAIdwAAAABJRU5ErkJggg==');

$file_icon = str_replace(array("\r\n", "\r", "\n"), '',
'data:image/jpg;base64,iVBORw0KGgoAAAANSUhEUgAAAA8AAAAQCAYAAADJViUEAAAAGXRFWHRTb2Z0d2
FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAS1JREFUeNqMU01KxkAMTaez7aYbNwreQdBzeopS6EXEW+jug7Z
C6X+/iUloSr6xioFHJkPee5mUJgBwT7gjpPB3XAgfiBjs5dOyLF/btl0pkEFngdbzPGNRFK/U+0hwJAAMjmcm
DsOA4zge6Pseu67DpmlEqK5rLMvyRkDJor6uq2SGktu2FfdpmpANqqoSASYnO/kthABJkoCOxCASkCBkWSYuQ
qCeNE1fqHz3fMkXzjnJ2sRinL33QBNIzWJ5nh/L8npQohVTJwYTyfFm/d6Oo2HGE8ffwseuZ1PEjhrOutmsRF
0iC8QmPibEtT4hftrhHI95JqJT/HC2JOt0to+zN6MVsZ/oZKqwmyCTA33DkbN1sws0i+Pega6v0kd42H9JB/8
LJl5I6PNbgAEAa9MP7QWoNLoAAAAASUVORK5CYII=');

$loading = str_replace(array("\r\n", "\r", "\n"), '',
'data:image/gif;base64,R0lGODlhFAAUALMIAPh2AP+TMsZiALlcAKNOAOp4ANVqAP+PFv///wAAAAAAAA
AAAAAAAAAAAAAAAAAAACH/C05FVFNDQVBFMi4wAwEAAAAh+QQFCgAIACwAAAAAFAAUAAAEUxDJSau9iBDMteb
TMEjehgTBJYqkiaLWOlZvGs8WDO6UIPCHw8TnAwWDEuKPcxQml0Ynj2cwYACAS7VqwWItWyuiUJB4s2AxmWxG
g9bl6YQtl0cAACH5BAUKAAgALAEAAQASABIAAAROEMkpx6A4W5upENUmEQT2feFIltMJYivbvhnZ3Z1h4FMQI
Dodz+cL7nDEn5CH8DGZhcLtcMBEoxkqlXKVIgAAibbK9YLBYvLtHH5K0J0IACH5BAUKAAgALAEAAQASABIAAA
ROEMkphaA4W5upMdUmDQP2feFIltMJYivbvhnZ3V1R4BNBIDodz+cL7nDEn5CH8DGZAMAtEMBEoxkqlXKVIg4
HibbK9YLBYvLtHH5K0J0IACH5BAUKAAgALAEAAQASABIAAAROEMkpjaE4W5tpKdUmCQL2feFIltMJYivbvhnZ
3R0A4NMwIDodz+cL7nDEn5CH8DGZh8ONQMBEoxkqlXKVIgIBibbK9YLBYvLtHH5K0J0IACH5BAUKAAgALAEAA
QASABIAAAROEMkpS6E4W5spANUmGQb2feFIltMJYivbvhnZ3d1x4JMgIDodz+cL7nDEn5CH8DGZgcBtMMBEox
kqlXKVIggEibbK9YLBYvLtHH5K0J0IACH5BAUKAAgALAEAAQASABIAAAROEMkpAaA4W5vpOdUmFQX2feFIltM
JYivbvhnZ3V0Q4JNhIDodz+cL7nDEn5CH8DGZBMJNIMBEoxkqlXKVIgYDibbK9YLBYvLtHH5K0J0IACH5BAUK
AAgALAEAAQASABIAAAROEMkpz6E4W5tpCNUmAQD2feFIltMJYivbvhnZ3R1B4FNRIDodz+cL7nDEn5CH8DGZg
8HNYMBEoxkqlXKVIgQCibbK9YLBYvLtHH5K0J0IACH5BAkKAAgALAEAAQASABIAAAROEMkpQ6A4W5spIdUmHQ
f2feFIltMJYivbvhnZ3d0w4BMAIDodz+cL7nDEn5CH8DGZAsGtUMBEoxkqlXKVIgwGibbK9YLBYvLtHH5K0J0
IADs=');

//编辑器模版
$html = <<<HTMLSTR
<!DOCTYPE html>
<html><head><meta charset="UTF-8">
<title>ACE代码编辑器</title>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<style type="text/css" media="screen">
a { text-decoration: none; }
body {
	overflow: hidden; background-color: #2D2D2D; font-size: 12px;
	font-family: 'Consolas', 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    scrollbar-arrow-color: #ccc; scrollbar-base-color: #333;
    scrollbar-dark-shadow-color: #00ffff; scrollbar-track-color: #272822;
    scrollbar-highlight-color: #272822; scrollbar-3d-light-color: #272822;
    scrollbar-face-color: #2D2D2D; scrollbar-shadow-color: #333;
}
::-webkit-scrollbar { width:5px; height:6px; background-color:#444; }
::-webkit-scrollbar:hover { background-color:#444; }
::-webkit-scrollbar-thumb:hover { min-height:5px; min-width:5px; background-color: #AAA; }
::-webkit-scrollbar-thumb:active { -webkit-border-radius:20px; background-color: #AAA; }
::-webkit-scrollbar-thumb {
    min-height:5px; min-width:5px; -webkit-border-radius:20px; 
    ::-webkit-border-radius:1px; background-color: #AAA;
}
body > pre { color: #666; }
#sider { margin: 0; position: absolute; top:  25px; bottom: 0; left: 0; right: 85%; }
#editor { margin: 0; position: absolute; top: 0; bottom: 0; left: 15%; right: 0; }
#dir_tree { margin:0; padding: 0; height: 100%; overflow: auto; position: relative; left: 5px; } 
#dir_tree, #dir_tree ul, #dir_tree li { margin: 0; padding: 0; list-style: none inside; }
#dir_tree ul { padding-left: 20px; position: relative; }
#dir_tree li { text-indent: 2em; line-height: 1.6em; cursor: default; color: #ccc; }
#dir_tree li.hover > span, #dir_tree li:hover > span { color: #66D9EF; }
#dir_tree li#on > span { color: red; }
#dir_tree li.dir { background: url({$dir_icon}) no-repeat 3px 3px; }
#dir_tree li.file { background: url({$file_icon}) no-repeat 3px 0; }
#dir_tree li.loading { background: url({$loading}) no-repeat 3px 0; }
#logout { position: absolute; top: 0; left: 0; }
#logout a { display: inline-block; color: #aaa; line-height: 25px; padding: 0 4px; }
#logout a:hover { background: #000; color: #ddd; }
#contextmenu { position: absolute; top: 0; left: 0; background: #fff; color: #333; border: 1px solid #000; padding: 1px; }
#contextmenu span { display: block; line-height: 24px; text-indent: 20px; width: 80px; cursor: default; }
#contextmenu span:hover { background-color: #369; color: #fff; }
#alertify .alertify-message, #alertify .alertify-message {
    text-align: left !important; text-indent: 0; font-weight: bold; font-size: 16px;
}
#alertify .alertify-dialog, #alertify .alertify-dialog {
    font-family: 'Consolas'; padding: 10px !important; color: #333 !important;
}
#alertify .alertify-button { 
    border-radius: 3px !important; font-weight: normal !important; 
    font-size: 14px !important; padding: 3px 15px !important;
}
.alertify-buttons { text-align: right !important; }
</style>
<link rel="stylesheet" href="{$tip['core']}" />
<link rel="stylesheet" href="{$tip['css']}" />
</head><body>
<div id="logout">
    <a href="javascript:void(0);">保存</a>
	<a href="javascript:void(0);">刷新</a>
</div>
<div id="sider">{$tree}</div><pre id="editor">{$code}</pre>
<script src="{$jquery}" type="text/javascript" charset="utf-8"></script>
<script src="{$ace}" type="text/javascript" charset="utf-8"></script>
<script src="{$tip['js']}" type="text/javascript"></script>
<script type="text/javascript">
var load = false;
var curr_file = false;
window.location.hash = '';
alertify.set({delay: 1000}); //n秒后自动消失
alertify.set({labels: {ok:'确定',cancel:'取消'}});
var editor = false;
$(function(){
	//实例化代码编辑器
	editor = ace.edit("editor");
	//设置编辑器的语法和高亮
	editor.setTheme("ace/theme/monokai");
	editor.getSession().setMode("ace/mode/php");
	//设置编辑器自动换行
	editor.getSession().setWrapLimitRange(null, null);
	editor.getSession().setUseWrapMode(true);
	//不显示垂直衬线
	editor.renderer.setShowPrintMargin(false);
	//editor.setReadOnly(true); //设置编辑器为只读
	//editor.gotoLine(325); //跳转到指定行
	//使编辑器获得输入焦点
	editor.focus();
	//绑定组合按键
	var commands = editor.commands;
	commands.addCommand({
		name: "save",
		bindKey: {win: "Ctrl-S", mac: "Command-S"},
		exec: save_file
	});
	//保存动作
	function save_file() {
		if ( false == editor ) { return false; }
		var obj = {
			content: editor.getValue(),
			action: 'save_file'
		};
		if ( false !== curr_file ) {
			obj.file_path = curr_file;
		}
		alertify.log('正在保存...');
		$.post(window.location.href, obj, function(data){
			if ( data.msg && 'success' == data.result ) {
				alertify.success(data.msg);
			} else {
				alertify.error(data.msg);
			}
		}, 'json');
	}
	//加载目录列表或文件
	load = function(ele) {
	    var curr = $(event.srcElement);
	    if ( ele ) { curr = ele; }
	    if ( curr.is('span') ) { curr = curr.parent('li'); }
		$('#dir_tree #on').removeAttr('id');
		curr.attr('id', 'on');
		var type = curr.attr('class');
		var path = curr.attr('path');
		//window.location.hash = path;
		if ( 'file' === type ) {
		    alertify.log('正在加载...');
		    curr.addClass('loading');
			var xd = path.split('.');
			var leng = xd.length;
			var file_ext = xd[leng-1].toLowerCase();
			var limit_ext = ['jpg','jpeg','png','zip','gif','pdf','psd','ico','xlxs','execl','doc','docx'];
			for(var i=0;i<=limit_ext.length;i++){
				if(limit_ext[i]==file_ext){
					alert('该文件无法编辑！');
					window.location.reload();
				}
			}
			
			$.post(window.location.href, {file:path}, function(data){
				curr.removeClass('loading');
				if ( data.mode ) {
				    editor.getSession().setMode("ace/mode/"+data.mode);
				}
				
				//注意，空文件应当允许编辑
				if ( true || data.html ) {
					curr.attr('disabled', 'disabled');
					curr_file = path; //当前编辑的文件路径
					//动态赋值编辑器中的内容
					editor.session.doc.setValue(data.html);
					editor.renderer.scrollToRow(0); //滚动到第一行
					editor.focus(); //编辑器获得焦点
					setTimeout(function(){
						editor.gotoLine(0);
					}, 800);
				}
			}, 'json');
			event.stopPropagation();
			event.preventDefault();
			return false;
		}
		if ( 'dir' === type ) {
			if ( curr.attr('loaded') ) {
				curr.children('ul').toggle();
				event.stopPropagation();
				event.preventDefault();
				return false;
			} else {
				curr.attr('loaded', 'yes');
			}
			alertify.log('正在加载...');
			curr.addClass('loading');
			$.post(window.location.href, {dir:path}, function(data){
				curr.find('ul').remove();
				curr.removeClass('loading');
				if ( data.html ) {
					curr.append(data.html);
				}
			}, 'json');
		}
		return false;
	}
	//绑定右键菜单
	$('#sider').bind('contextmenu', function(e){
	    var path = false;
	    var target = $(event.srcElement);
	    if ( target.is('span') ) {
	        target = target.parent('li');
	    }
	    if ( target.attr('path') ) {
	        path = target.attr('path');
	    } else {
	        return false;
	    }
	    target.addClass('hover');
	    var right_menu = $('#contextmenu');
	    if ( !right_menu.get(0) ) {
	        var timer = false;
	        right_menu = $('<div id="contextmenu"></div>');
	        right_menu.hover(function(){
	            if ( timer ) { clearTimeout(timer); }
	        }, function(){
	            timer = setTimeout(function(){
	                hide_menu(right_menu);
	            }, 500);
	        });
	        $('body').append(right_menu);
	    }
	    if ( path ) {
	        right_menu.html('');
    	    var menu = $('<span>新建</span><span>重命名</span><span>删除</span>');
    	    right_menu.append(menu);
    	    menu_area(right_menu, {left: e.pageX, top: e.pageY});
    	    right_menu.find('span').click(function(){
    	        switch ( $(this).text() ) {
    	            case '新建' : create_new(target, path); break;
    	           // case '浏览' : preview(target, path); break;
    	            case '重命名' : re_name(target, path); break;
    	            case '删除' : del_file(target, path); break;
    	        }
    	        hide_menu(right_menu);
    	    });
	    }
	    path ? right_menu.show() : hide_menu(right_menu);
        return false;
    });
    //隐藏右键菜单
    function hide_menu(menu) {
        $('#sider li.hover').removeClass('hover');
        if ( menu ) {
            menu.hide();
        }
    }
    //右键菜单区域
    function menu_area(menu, cfg) {
        if ( menu && cfg ) {
            var w = $('#sider').width() - menu.width();
            var h = $('#sider').height() - menu.height();
            if ( cfg.left > w ) { cfg.left = w; }
            if ( cfg.top > h ) { cfg.top = h; }
            menu.css(cfg);
        }
    }
	//保存按钮
	$('#logout>a:contains("保存")').click(function(){
		save_file();
		return false;
	});
	//刷新按钮
	$('#logout>a:contains("刷新")').click(function(){
		//window.location.href = window.location.pathname;
		window.location.reload();
		return false;
	});
	//重置按钮
	$('#logout>a:contains("重置")').click(function(){
		alertify.confirm('是否修改 {$curr_file_path} 程序文件名？', function (e) {
		    if ( !e ) { return 'cancel'; }
		    re_name($('<a>'), '{$curr_file_path}');
		});
		return false;
	});
    //新建操作
    function create_new(obj, path) {
		if ( !obj || !path ) { return false; }
		alertify.prompt('请输入新建文件或文件夹名：', function (e, str) {
			if ( !e || !str ) { return false; }
		    alertify.log('正在操作中...');
		    $('#dir_tree #on').removeAttr('loaded').removeAttr('id');
			$.post(window.location.href, {create:str,target:path}, function(data){
			    if ( data.msg && 'success' == data.result ) {
                    alertify.success(data.msg);
                    if ( obj.attr('class') == 'dir' ) {
                        load(obj); //重新加载子节点
                    } else {
                        load(obj.parent().parent());
                    }
                } else {
                    alertify.error(data.msg);
                }
            }, 'json');
		});
    }
    //浏览操作
    function preview(obj, path) {
		if ( !obj || !path ) { return false; }
		window.open(path, '_blank');
    }
    //重命名
    function re_name(obj, path) {
		if ( !obj || !path ) { return false; }
		alertify.prompt('重命名 '+path+' 为：', function (e, str) {
			if ( !e || !str ) { return false; }
		    alertify.log('正在操作中...');
		    $.post(window.location.href, {rename:str,target:path}, function(data){
                if ( data.msg && 'success' == data.result ) {
                    alertify.success(data.msg);
                    if ( data.redirect ) {
                        window.location.href = data.redirect;
                    }
                    if ( data.new_name ) {
                        obj.children('span').first().text(data.new_name);
                        obj.attr('path', data.new_path);
                    }
                } else {
                    alertify.error(data.msg);
                }
            }, 'json');
		});
    }
    //删除文件动作
    function del_file(obj, path) {
        if ( !obj || !path ) { return false; }
        alertify.confirm('您确定要删除：'+path+' 吗？', function (e) {
            if ( !e ) { return 'cancel'; }
            alertify.log('正在删除中...');
            $.post(window.location.href, {del:path}, function(data){
                if ( data.msg && 'success' == data.result ) {
                    alertify.success(data.msg);
                    obj.remove();
                } else {
                    alertify.error(data.msg);
                }
            }, 'json');
        });
    }
});
</script>
</body></html>
HTMLSTR;

//判断是否已经登录
 echo $html;