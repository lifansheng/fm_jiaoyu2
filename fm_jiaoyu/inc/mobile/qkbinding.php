<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
	global $_W, $_GPC;
	$weid = $_W ['uniacid'];
	$openid = $_W['openid'];
	$sid 	  = intval($_GPC['sid']); 
	$schoolid = intval($_GPC['schoolid']); 
	$type     = trim($_GPC['type']); 
	$expire     = trim($_GPC['expire']); 
	$school = pdo_fetch("SELECT title,bd_type,headcolor,spic,style1 FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}' ");
	$student = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " WHERE schoolid = '{$schoolid}' And id = '{$sid}'");
	$user = pdo_fetch("SELECT id FROM " . tablename($this->table_user) . " WHERE openid = '{$openid}' And sid = '{$sid}'");
	if(time() > $expire || empty($school) || empty($student) || $user) {	
		if($user){
			$tip = "抱歉,您已经绑定了本学生";
		}
		if(time() > $expire){
			$tip = "抱歉,该二维码已失效请联系校方或其他家长重新获取二维码";
		}
		if($school){
			$tip = "抱歉,本链接已失效请联系校方或其他家长重新获取二维码";
		}
		if($student){
			$tip = "抱歉,该二维码已失效请联系校方或其他家长重新获取二维码";
		}			
		include $this->template(''.$school['style1'].'/qkbindingtip');
		exit();
	}	
	$bdset = get_weidset($weid,'bd_set');
	$sms_set = get_school_sms_set($schoolid);
	include $this->template('qkbinding');	
?>