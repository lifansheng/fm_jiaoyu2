<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
	global $_W, $_GPC;
	$weid = $_W ['uniacid'];
	$openid = $_W['openid'];
	$bjid 	  = intval($_GPC['bjid']); 
	$schoolid = intval($_GPC['schoolid']); 
    $expire     = trim($_GPC['expire']); 
    $school = pdo_fetch("SELECT title,bd_type,headcolor,spic,style1 FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}' ");
    $bjname = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$bjid}' ")['sname'];
	if(time() > $expire || empty($school)) {	
		if(time() > $expire){
			$tip = "抱歉,该班级二维码已失效请联系校方或其他家长重新获取二维码";
		}
		if($school){
			$tip = "抱歉,本链接已失效请联系校方或其他家长重新获取二维码";
		}		
		include $this->template(''.$school['style1'].'/qkbindingtip');
		exit();
	}	
	include $this->template('bjbangding');	
?>