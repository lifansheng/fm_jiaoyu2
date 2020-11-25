<?php
/**
* 微教育模块
*
* @author 高贵血迹
*/
	global $_W, $_GPC;
	$weid = $_W['uniacid'];
	$openid = $_W['openid'];	
	$schoolid = intval($_GPC['schoolid']);
	$userid = intval($_GPC['id']);
	if(!empty($userid)){
		$_SESSION['user'] = $userid;
	}
	$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $_SESSION['user']));
	$school = pdo_fetch("SELECT style2 FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
	$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
	if($operation == 'display'){
		if(!empty($it)){
			$students = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where weid = :weid AND id=:id AND schoolid=:schoolid ", array(':weid' => $weid, ':id' => $it['sid'], ':schoolid' => $schoolid));	
			include $this->template(''.$school['style2'].'/useredit');
		}else{
			session_destroy();
			$stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
			exit;
		}
	}
	if($operation == 'out'){
		session_destroy();
		$stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('user', array('schoolid' => $schoolid));
		header("location:$stopurl");
		exit;
	}