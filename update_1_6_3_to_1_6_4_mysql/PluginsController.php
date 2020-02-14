<?php

// +----------------------------------------------------------------------
// | JiZhiCMS { 极致CMS，给您极致的建站体验 }  
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2099 http://www.jizhicms.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 留恋风 <2581047041@qq.com>
// +----------------------------------------------------------------------
// | Date：2020/01/04
// +----------------------------------------------------------------------

namespace A\exts;

use FrPHP\lib\Controller;
use FrPHP\Extend\Page;
class PluginsController extends Controller {
	
	
	//自动执行
	public function _init(){
		/**
			继承系统默认配置
		
		**/
		
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
	  
	    $webconf = webConf();
	    $this->webconf = $webconf;
	    $customconf = get_custom();
	    $this->customconf = $customconf;
		
		//插件模板页目录
		
		$this->tpl = '@'.dirname(__FILE__).'/tpl/';
		
		/**
			在下面添加自定义操作
		**/
		
		
	}
	
	//执行SQL语句在此处处理,或者移动文件也可以在此处理
	public  function install(){
		
		//检测版本号
		if(!version_compare($this->webconf['web_version'],'1.6.3','==')){
			JsonReturn(['code'=>1,'msg'=>'您的软件系统版本为'.$this->webconf['web_version'].'，该插件仅支持1.6.3版本升级到1.6.4版本！']);
		}
		if(defined('DB_TYPE')){
			JsonReturn(['code'=>1,'msg'=>'当前升级插件不支持sqlite版本升级！']);
		}
		$sqlx = 'SHOW COLUMNS FROM '.DB_PREFIX.'classtype';
		$list = M()->findSql($sqlx);
		$isgo = true;
		foreach($list as $v){
			if($v['Field']=='ishome'){
				$isgo = false;
				
			}
		}
		$sql = '';
		if($isgo){
			//新增字段
		$sql = "ALTER TABLE ".DB_PREFIX."classtype ADD ishome tinyint(1)  DEFAULT 1 ;";
	
		}
		$sqlx = 'SHOW COLUMNS FROM '.DB_PREFIX.'fields';
		$list = M()->findSql($sqlx);
		$isgo = true;
		foreach($list as $v){
			if($v['Field']=='isadmin'){
				$isgo = false;
				
			}
		}
		if($isgo){
			$sql .= "ALTER TABLE ".DB_PREFIX."fields ADD isadmin tinyint(1)  DEFAULT 1 ;";
		}
		$sqlx = 'SHOW COLUMNS FROM '.DB_PREFIX.'links';
		$list = M()->findSql($sqlx);
		$isgo = true;
		foreach($list as $v){
			if($v['Field']=='member_id'){
				$isgo = false;
				
			}
		}
		if($isgo){
			$sql .= "ALTER TABLE ".DB_PREFIX."links ADD member_id INT(11)  DEFAULT 0 ;";
		}
		$sqlx = 'SHOW COLUMNS FROM '.DB_PREFIX.'member';
		$list = M()->findSql($sqlx);
		$isgo_signature = true;
		$isgo_birthday = true;
		$isgo_follow = true;
		$isgo_fans = true;
		$isgo_ismsg = true;
		$isgo_iscomment = true;
		$isgo_iscollect = true;
		$isgo_islikes = true;
		$isgo_isat = true;
		$isgo_isrechange = true;

		foreach($list as $v){
			if($v['Field']=='signature'){
				$isgo_signature = false;
			}
			if($v['Field']=='birthday'){
				$isgo_birthday = false;
			}
			if($v['Field']=='follow'){
				$isgo_follow = false;
			}
			if($v['Field']=='fans'){
				$isgo_fans = false;
			}
			if($v['Field']=='ismsg'){
				$isgo_ismsg = false;
			}
			if($v['Field']=='iscomment'){
				$isgo_iscomment = false;
			}
			if($v['Field']=='iscollect'){
				$isgo_iscollect = false;
			}
			if($v['Field']=='islikes'){
				$isgo_islikes = false;
			}
			if($v['Field']=='isat'){
				$isgo_isat = false;
			}
			if($v['Field']=='isrechange'){
				$isgo_isrechange = false;
			}
		}
		if($isgo_signature){
		$sql .= "ALTER TABLE ".DB_PREFIX."member ADD signature VARCHAR(255)  DEFAULT NULL ;";
		}
		if($isgo_birthday){
		$sql .= "ALTER TABLE ".DB_PREFIX."member ADD birthday VARCHAR(255)  DEFAULT NULL ;";
		}
		if($isgo_follow){
		$sql .= "ALTER TABLE ".DB_PREFIX."member ADD follow TEXT  DEFAULT NULL ;";
		}
		if($isgo_fans){
		$sql .= "ALTER TABLE ".DB_PREFIX."member ADD fans INT(11)  DEFAULT 0 ;";
		}
		if($isgo_ismsg){
		$sql .= "ALTER TABLE ".DB_PREFIX."member ADD ismsg tinyint(1)  DEFAULT 1 ;";
		}
		if($isgo_iscomment){
		$sql .= "ALTER TABLE ".DB_PREFIX."member ADD iscomment tinyint(1)  DEFAULT 1 ;";
		}
		if($isgo_iscollect){
		$sql .= "ALTER TABLE ".DB_PREFIX."member ADD iscollect tinyint(1)  DEFAULT 1 ;";
		}
		if($isgo_islikes){
		$sql .= "ALTER TABLE ".DB_PREFIX."member ADD islikes tinyint(1)  DEFAULT 1 ;";
		}
		if($isgo_isat){
		$sql .= "ALTER TABLE ".DB_PREFIX."member ADD isat tinyint(1)  DEFAULT 1 ;";
		}
		if($isgo_isrechange){
		$sql .= "ALTER TABLE ".DB_PREFIX."member ADD isrechange tinyint(1)  DEFAULT 1 ;";
		}
		$sqlx = 'SHOW COLUMNS FROM '.DB_PREFIX.'orders';
		$list = M()->findSql($sqlx);
		$isgo = true;
		foreach($list as $v){
			if($v['Field']=='ptype'){
				$isgo = false;
				
			}
		}
		if($isgo){
		$sql .= "ALTER TABLE ".DB_PREFIX."orders ADD ptype tinyint(1)  DEFAULT 1 ;";
		}
		$sqlx = 'SHOW COLUMNS FROM '.DB_PREFIX.'pictures';
		$list = M()->findSql($sqlx);
		$isgo_molds = true;
		$isgo_path = true;
		$isgo_filetype = true;
		foreach($list as $v){
			if($v['Field']=='molds'){
				$isgo_molds = false;
			}
			if($v['Field']=='path'){
				$isgo_path = false;
			}
			if($v['Field']=='filetype'){
				$isgo_filetype = false;
			}
		}
		if($isgo_molds){
		$sql .= "ALTER TABLE ".DB_PREFIX."pictures ADD molds VARCHAR(50)  DEFAULT NULL ;";
		}
		if($isgo_path){
		$sql .= "ALTER TABLE ".DB_PREFIX."pictures ADD path VARCHAR(20)  DEFAULT 'Admin' ;";
		}
		if($isgo_filetype){
		$sql .= "ALTER TABLE ".DB_PREFIX."pictures ADD filetype VARCHAR(20)  DEFAULT NULL ;";
		}
		M()->runSql($sql);
		
		$sql = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."buylog` (
				`id` int(11) unsigned NOT NULL auto_increment,
				`userid` int(11) DEFAULT 0,
				`orderno` varchar(255) DEFAULT NULL,
				`type` tinyint(1) DEFAULT 1,
				`buytype` varchar(20) DEFAULT NULL,
				`msg` varchar(255) DEFAULT NULL,
				`amount` decimal(10,2) DEFAULT '0.00',
				`money` decimal(10,2) DEFAULT '0.00',
				`addtime` int(11) DEFAULT 0,
				PRIMARY 
				KEY  (`id`)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
				
		M()->runSql($sql);
		$sql = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."task` (
				`id` int(11) unsigned NOT NULL auto_increment,
				`tid` int(11) DEFAULT 0,
				`aid` int(11) DEFAULT 0,
				`userid` int(11) DEFAULT 0,
				`puserid` int(11) DEFAULT 0,
				`molds` varchar(50) DEFAULT NULL,
				`type` varchar(50) DEFAULT NULL,
				`body` varchar(255) DEFAULT NULL,
				`url` varchar(255) DEFAULT NULL,
				`isread` tinyint(1) DEFAULT 0,
				`isshow` tinyint(1) DEFAULT 1,
				`readtime` int(11) DEFAULT 0,
				`addtime` int(11) DEFAULT 0,
				PRIMARY 
				KEY  (`id`)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
				
		M()->runSql($sql);
		
		$sql="truncate table ".DB_PREFIX."power;";
		M()->runSql($sql);
		$sql="INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('1','Common','公共权限','0','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('2','Home','前台网站','0','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('3','User','个人中心','0','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('4','Login','会员登录','0','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('5','Message','站内留言','0','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('6','Comment','会员评论','0','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('7','Screen','网站筛选','0','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('8','Order','会员下单','0','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('9','Mypay','网站支付','0','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('10','Jzpay','极致支付','0','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('11','Tags','TAG标签','0','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('12','Wechat','微信模块','0','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('13','Common/vercode','验证码生成','1','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('14','Common/checklogin','检查是否登录','1','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('15','Common/multiuploads','多附件上传','1','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('16','Common/uploads','单附件上传','1','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('17','Common/qrcode','二维码生成','1','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('18','Common/get_fields','获取扩展信息','1','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('19','Common/jizhi','链接错误提示','1','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('20','Common/error','报错提示','1','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('21','Home/index','网站首页','2','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('22','Home/jizhi','网站内容','2','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('23','Home/auto_url','自定义链接','2','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('24','Home/jizhi_details','详情内容','2','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('25','Home/search','网站搜索','2','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('26','Home/searchAll','网站多模块搜索','2','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('27','Home/start_cache','开启网站缓存','2','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('28','Home/end_cache','输出缓存','2','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('29','User/checklogin','检查是否登录','3','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('30','User/index','个人中心首页','3','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('31','User/userinfo','会员资料','3','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('32','User/orders','订单记录','3','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('33','User/orderdetails','订单详情','3','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('34','User/payment','订单支付','3','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('35','User/orderdel','删除订单','3','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('36','User/changeimg','上传头像','3','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('37','User/comment','评论列表','3','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('38','User/commentdel','删除评论','3','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('39','User/likesAction','点赞文章','3','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('40','User/likes','点赞列表','3','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('41','User/likesdel','取消点赞','3','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('42','User/collectAction','收藏文章','3','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('43','User/collect','收藏列表','3','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('44','User/collectdel','删除收藏','3','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('45','User/cart','购物车','3','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('46','User/addcart','添加购物车','3','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('47','User/delcart','删除购物车','3','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('48','User/posts','发布管理','3','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('49','User/release','会员发布','3','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('50','User/del','删除发布','3','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('51','User/uploads','会员上传附件','3','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('52','User/jizhi','404提示','3','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('53','User/follow','关注用户','3','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('54','User/nofollow','取消关注','3','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('55','User/fans','粉丝列表','3','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('56','User/notify','消息提醒','3','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('57','User/notifyto','查看消息','3','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('58','User/notifydel','删除消息','3','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('59','User/active','公共主页','3','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('60','User/setmsg','消息提醒设置','3','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('61','User/getclass','获取栏目列表','2','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('62','User/wallet','用户钱包','3','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('63','User/buy','会员充值','3','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('64','User/buylist','充值列表','3','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('65','User/buydetails','交易详情','3','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('66','Login/index','登录首页','4','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('67','Login/register','注册页面','4','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('68','Login/forget','忘记密码','4','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('69','Login/nologin','未登录页面','4','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('70','Login/loginout','退出登录','4','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('71','Message/index','发送留言','5','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('72','Comment/index','发表评论','6','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('73','Screen/index','筛选列表','7','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('74','Order/create','创建订单','8','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('75','Order/pay','订单支付','8','1');
INSERT INTO `".DB_PREFIX."power` (`id`,`action`,`name`,`pid`,`isagree`) VALUES ('76','Tags/index','TAG标签列表','11','1');";
		M()->runSql($sql);
		
		
		//插入SQL
		if(!M('ruler')->find(['fc'=>'Order/czlist'])){
			$w['name'] ='充值列表'; 
			$w['fc'] ='Order/czlist'; 
			$w['pid'] = 128; 
			$w['isdesktop'] = 1; 
			$w['sys'] = 1; 
			M('ruler')->add($w);
		}
		if(!M('ruler')->find(['fc'=>'Order/chongzhi'])){
			$w['name'] ='手动充值'; 
			$w['fc'] ='Order/chongzhi'; 
			$w['pid'] = 128; 
			$w['isdesktop'] = 0; 
			$w['sys'] = 1; 
			M('ruler')->add($w);
		}
		if(!M('ruler')->find(['fc'=>'Order/delbuylog'])){
			$w['name'] ='删除记录'; 
			$w['fc'] ='Order/delbuylog'; 
			$w['pid'] = 128; 
			$w['isdesktop'] = 0; 
			$w['sys'] = 1; 
			M('ruler')->add($w);
		}
		if(!M('ruler')->find(['fc'=>'Order/delAllbuylog'])){
			$w['name'] ='批量删除记录'; 
			$w['fc'] ='Order/delAllbuylog'; 
			$w['pid'] = 128; 
			$w['isdesktop'] = 0; 
			$w['sys'] = 1; 
			M('ruler')->add($w);
		}
		if(!M('ruler')->find(['fc'=>'Sys/jifenset'])){
			$w['name'] ='积分配置'; 
			$w['fc'] ='Sys/jifenset'; 
			$w['pid'] = 39; 
			$w['isdesktop'] = 0; 
			$w['sys'] = 1; 
			M('ruler')->add($w);
		}
		if(!M('ruler')->find(['fc'=>'Plugins/update'])){
			$w['name'] ='插件更新'; 
			$w['fc'] ='Plugins/update'; 
			$w['pid'] = 75; 
			$w['isdesktop'] = 0; 
			$w['sys'] = 1; 
			M('ruler')->add($w);
		}
		$w=[];
		if(!M('molds')->find(['biaoshi'=>'page'])){
			$w['name'] ='单页'; 
			$w['biaoshi'] ='page'; 
			$w['sys'] = 1; 
			$w['isopen'] = 1; 
			$w['isclasstype'] = 1; 
			M('molds')->add($w);
		}
		
		$w = [];
		if(!M('sysconfig')->find(['field'=>'iswatermark'])){
			$w['field'] ='iswatermark'; 
			$w['title'] ='是否开启水印'; 
			$w['tip'] = '开启水印需要上传水印图片'; 
			$w['type'] = 0; 
			$w['data'] = '0'; 
			M('sysconfig')->add($w);
		}
		if(!M('sysconfig')->find(['field'=>'watermark_file'])){
			$w['field'] ='watermark_file'; 
			$w['title'] ='水印图片'; 
			$w['tip'] = '水印图片在250px以内'; 
			$w['type'] = 0; 
			$w['data'] = NULL; 
			M('sysconfig')->add($w);
		}
		if(!M('sysconfig')->find(['field'=>'watermark_t'])){
			$w['field'] ='watermark_t'; 
			$w['title'] ='水印位置'; 
			$w['tip'] = '参考键盘九宫格1-9'; 
			$w['type'] = 0; 
			$w['data'] = '9'; 
			M('sysconfig')->add($w);
		}
		if(!M('sysconfig')->find(['field'=>'watermark_tm'])){
			$w['field'] ='watermark_tm'; 
			$w['title'] ='水印透明度'; 
			$w['tip'] = '透明度越大，越难看清楚水印'; 
			$w['type'] = 0; 
			$w['data'] = NULL; 
			M('sysconfig')->add($w);
		}
		if(!M('sysconfig')->find(['field'=>'money_exchange'])){
			$w['field'] ='money_exchange'; 
			$w['title'] ='钱包兑换率'; 
			$w['tip'] = '站内钱包与RMB的兑换率，即1元=多少金币'; 
			$w['type'] = 0; 
			$w['data'] = '1'; 
			M('sysconfig')->add($w);
		}
		if(!M('sysconfig')->find(['field'=>'jifen_exchange'])){
			$w['field'] ='jifen_exchange'; 
			$w['title'] ='积分兑换率'; 
			$w['tip'] = '站内积分与RMB的兑换率，即1元=多少积分'; 
			$w['type'] = 0; 
			$w['data'] = '100'; 
			M('sysconfig')->add($w);
		}
		if(!M('sysconfig')->find(['field'=>'isopenjifen'])){
			$w['field'] ='isopenjifen'; 
			$w['title'] ='积分支付'; 
			$w['tip'] = '开启积分支付后，商品可以用积分支付'; 
			$w['type'] = 0; 
			$w['data'] = '1'; 
			M('sysconfig')->add($w);
		}
		if(!M('sysconfig')->find(['field'=>'isopenqianbao'])){
			$w['field'] ='isopenqianbao'; 
			$w['title'] ='钱包支付'; 
			$w['tip'] = '开启钱包支付后，商品可以用钱包支付'; 
			$w['type'] = 0; 
			$w['data'] = '1'; 
			M('sysconfig')->add($w);
		}
		if(!M('sysconfig')->find(['field'=>'isopenweixin'])){
			$w['field'] ='isopenweixin'; 
			$w['title'] ='微信支付'; 
			$w['tip'] = '开启微信支付后，商品可以用微信支付'; 
			$w['type'] = 0; 
			$w['data'] = '1'; 
			M('sysconfig')->add($w);
		}
		if(!M('sysconfig')->find(['field'=>'isopenzfb'])){
			$w['field'] ='isopenzfb'; 
			$w['title'] ='支付宝支付'; 
			$w['tip'] = '开启支付宝支付后，商品可以用支付宝支付'; 
			$w['type'] = 0; 
			$w['data'] = '1'; 
			M('sysconfig')->add($w);
		}
		if(!M('sysconfig')->find(['field'=>'login_award'])){
			$w['field'] ='login_award'; 
			$w['title'] ='每次登录奖励'; 
			$w['tip'] = '每天登录奖励积分数，最小为0，每天登录只奖励一次'; 
			$w['type'] = 0; 
			$w['data'] = '1'; 
			M('sysconfig')->add($w);
		}
		if(!M('sysconfig')->find(['field'=>'login_award_open'])){
			$w['field'] ='login_award_open'; 
			$w['title'] ='登录奖励'; 
			$w['tip'] = '开启登录奖励后，登录后就会获得积分奖励'; 
			$w['type'] = 0; 
			$w['data'] = '1'; 
			M('sysconfig')->add($w);
		}
		if(!M('sysconfig')->find(['field'=>'release_award_open'])){
			$w['field'] ='release_award_open'; 
			$w['title'] ='发布奖励'; 
			$w['tip'] = '开启后，发布内容会奖励积分'; 
			$w['type'] = 0; 
			$w['data'] = '1'; 
			M('sysconfig')->add($w);
		}
		if(!M('sysconfig')->find(['field'=>'release_award'])){
			$w['field'] ='release_award'; 
			$w['title'] ='每次发布奖励'; 
			$w['tip'] = '每次发布内容奖励积分数'; 
			$w['type'] = 0; 
			$w['data'] = '1'; 
			M('sysconfig')->add($w);
		}
		if(!M('sysconfig')->find(['field'=>'release_max_award'])){
			$w['field'] ='release_max_award'; 
			$w['title'] ='每天发布最高奖励'; 
			$w['tip'] = '每天奖励不超过积分上限，设置0则无上限'; 
			$w['type'] = 0; 
			$w['data'] = '0'; 
			M('sysconfig')->add($w);
		}
		if(!M('sysconfig')->find(['field'=>'collect_award_open'])){
			$w['field'] ='collect_award_open'; 
			$w['title'] ='收藏奖励'; 
			$w['tip'] = '开启后，发布内容被收藏会奖励积分'; 
			$w['type'] = 0; 
			$w['data'] = '1'; 
			M('sysconfig')->add($w);
		}
		if(!M('sysconfig')->find(['field'=>'collect_award'])){
			$w['field'] ='collect_award'; 
			$w['title'] ='每次收藏奖励'; 
			$w['tip'] = '每次发布内容被收藏奖励积分数'; 
			$w['type'] = 0; 
			$w['data'] = '1'; 
			M('sysconfig')->add($w);
		}
		if(!M('sysconfig')->find(['field'=>'collect_max_award'])){
			$w['field'] ='collect_max_award'; 
			$w['title'] ='每天收藏最高奖励'; 
			$w['tip'] = '每天奖励不超过积分上限，设置0则无上限'; 
			$w['type'] = 0; 
			$w['data'] = '0'; 
			M('sysconfig')->add($w);
		}
		if(!M('sysconfig')->find(['field'=>'likes_award_open'])){
			$w['field'] ='likes_award_open'; 
			$w['title'] ='点赞奖励'; 
			$w['tip'] = '开启后，发布内容被点赞会奖励积分'; 
			$w['type'] = 0; 
			$w['data'] = '1'; 
			M('sysconfig')->add($w);
		}
		if(!M('sysconfig')->find(['field'=>'likes_award'])){
			$w['field'] ='likes_award'; 
			$w['title'] ='每次点赞奖励'; 
			$w['tip'] = '每次发布内容被点赞奖励积分数'; 
			$w['type'] = 0; 
			$w['data'] = '1'; 
			M('sysconfig')->add($w);
		}
		if(!M('sysconfig')->find(['field'=>'likes_max_award'])){
			$w['field'] ='likes_max_award'; 
			$w['title'] ='每天点赞最高奖励'; 
			$w['tip'] = '每天奖励不超过积分上限，设置0则无上限'; 
			$w['type'] = 0; 
			$w['data'] = '0'; 
			M('sysconfig')->add($w);
		}
		if(!M('sysconfig')->find(['field'=>'comment_award_open'])){
			$w['field'] ='comment_award_open'; 
			$w['title'] ='评论奖励'; 
			$w['tip'] = '开启后，发布内容被评论会奖励积分'; 
			$w['type'] = 0; 
			$w['data'] = '1'; 
			M('sysconfig')->add($w);
		}
		if(!M('sysconfig')->find(['field'=>'comment_award'])){
			$w['field'] ='comment_award'; 
			$w['title'] ='每次评论奖励'; 
			$w['tip'] = '每次发布内容被评论奖励积分数'; 
			$w['type'] = 0; 
			$w['data'] = '1'; 
			M('sysconfig')->add($w);
		}
		if(!M('sysconfig')->find(['field'=>'comment_max_award'])){
			$w['field'] ='comment_max_award'; 
			$w['title'] ='每天评论最高奖励'; 
			$w['tip'] = '每天奖励不超过积分上限，设置0则无上限'; 
			$w['type'] = 0; 
			$w['data'] = '0'; 
			M('sysconfig')->add($w);
		}
		if(!M('sysconfig')->find(['field'=>'follow_award_open'])){
			$w['field'] ='follow_award_open'; 
			$w['title'] ='关注奖励'; 
			$w['tip'] = '开启后，用户被粉丝关注会奖励积分'; 
			$w['type'] = 0; 
			$w['data'] = '1'; 
			M('sysconfig')->add($w);
		}
		if(!M('sysconfig')->find(['field'=>'follow_award'])){
			$w['field'] ='follow_award'; 
			$w['title'] ='每次关注奖励'; 
			$w['tip'] = '每次被关注奖励积分数'; 
			$w['type'] = 0; 
			$w['data'] = '1'; 
			M('sysconfig')->add($w);
		}
		if(!M('sysconfig')->find(['field'=>'follow_max_award'])){
			$w['field'] ='follow_max_award'; 
			$w['title'] ='每天关注最高奖励'; 
			$w['tip'] = '每天关注奖励不超过积分上限，设置0则无上限'; 
			$w['type'] = 0; 
			$w['data'] = '0'; 
			M('sysconfig')->add($w);
		}
		if(!M('sysconfig')->find(['field'=>'isopenemail'])){
			$w['field'] ='isopenemail'; 
			$w['title'] ='发送邮件'; 
			$w['tip'] = '是否开启邮件发送'; 
			$w['type'] = 0; 
			$w['data'] = '1'; 
			M('sysconfig')->add($w);
		}
		
		$w = [];
		if(!M('fields')->find(['field'=>'member_id'])){
			$w['field'] ='member_id'; 
			$w['molds'] ='links'; 
			$w['fieldname'] = '发布用户'; 
			$w['tip'] = '前台会员，无需填写';
			$w['fieldtype'] = 13; 
			$w['fieldlong'] = 11; 
			$w['body'] = '3,username'; 
			$w['isadmin'] = 0; 
			$w['vdata'] = 0; 
			M('fields')->add($w);
		}
		
		M('fields')->update(['field'=>'member_id'],['isadmin'=>0]);
		
		$sid = M('ruler')->getField(['fc'=>'Order/czlist'],'id');
		//更新
		M('layout')->update(['id'=>1],['left_layout'=>'[{"name":"网站管理","icon":"&amp;#xe699;","nav":["42","9","95","83","147","22"]},{"name":"商品管理","icon":"&amp;#xe698;","nav":["105","129","2","118","123","16",'.$sid.']},{"name":"扩展管理","icon":"&amp;#xe6ce;","nav":["76","116","141","142","143","35","61","154","153"]},{"name":"系统设置","icon":"&amp;#xe6ae;","nav":["40","54","49","70","115","114","66"]}]']);
		
		//检查系统是版本，特殊文件替换，及相关SQL操作
		
		$path = APP_PATH;
		$tmp_path = APP_PATH.'A/exts/update_1_6_3_to_1_6_4_mysql/file/update.zip';
		$resource = zip_open($tmp_path);//打开压缩包
		while($dir_resource = zip_read($resource)){//遍历读取压缩包里面的一个个文件  
			if(zip_entry_open($resource,$dir_resource)){//如果能打开则继续
				$file_name = $path.zip_entry_name($dir_resource);//获取当前项目的名称,即压缩包里面当前对应的文件名  
				$file_path=substr($file_name,0,strrpos($file_name,"/"));
				if (!is_dir($file_path)){//如果路径不存在，则创建一个目录，true表示可以创建多级目录  
					mkdir($file_path, 0777, true);
				}
				if(!is_dir($file_name)){//如果不是目录，则写入文件
					$file_size=zip_entry_filesize($dir_resource);//读取这个文件
					$file_content = zip_entry_read($dir_resource, $file_size);
					file_put_contents($file_name, $file_content);
				}
				zip_entry_close($dir_resource);//关闭当前
			}
		} 
		zip_close($resource); //关闭压缩包
		
		
		//更新系统版本
		M('sysconfig')->update(['field'=>'web_version'],['data'=>'1.6.4']);
		
		
		
		
		//更新配置
		setCache('webconfig',null);
		setCache('customconfig',null);
	    setCache('classtype',null);
	    setCache('mobileclasstype',null);
	    setCache('classtypetree',null);
		
		
		return true;
		
	}
	
	
	//卸载程序,对新增字段、表等进行删除SQL操作，或者其他操作
	public function uninstall(){
		//下面是删除test表的SQL操作
		
		return true;
	}
	
	//安装页面介绍,操作说明
	public function desc(){
		
		$this->display($this->tpl.'plugins-description.html');
	}
	
	//配置文件,插件相关账号密码等操作
	public  function setconf($plugins){
		//将插件赋值到模板中
		$this->plugins = $plugins;
		$this->config = json_decode($plugins['config'],1);
		
		$this->display($this->tpl.'plugins-body.html');
	}
	//获取插件内提交的数据处理
	public function setconfigdata($data){
		
		JsonReturn(['code'=>0,'msg'=>'设置成功！']);
		
	}
	
	
}




