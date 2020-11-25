<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
	global $_W, $_GPC;
	$weid = $_W['uniacid'];
	$schoolid = intval($_GPC['schoolid']);        
	$s_name = trim($_GPC['s_name']);
	$mobile = trim($_GPC['mobile']);	
	$openid = $_W['openid'];
	$it = pdo_fetch("SELECT sid FROM " . tablename($this->table_user) . " where id = :id And openid = :openid ", array(':id' => $_SESSION['user'],':openid' => $openid));
	$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
	if(!empty($s_name)){
		$student = pdo_fetch("SELECT id,xq_id,bj_id,s_name,icon FROM " . tablename($this->table_students) . " where :schoolid = schoolid And :weid = weid And :s_name = s_name And :mobile = mobile", array(
			':weid' => $weid,
			':schoolid' => $schoolid,
			':mobile'=>$mobile,
			':s_name'=>$s_name
		));
		$sid = 	$student['id'];
	}else{
		$student = pdo_fetch("SELECT id,xq_id,bj_id,s_name,icon FROM " . tablename($this->table_students) . " where :weid = weid And :id = id", array(
			':weid' => $weid,
			':id' => $it['sid']
		));	
		$sid = 	$it['sid'];	
	}
	$mynj = pdo_fetch("SELECT sname,sid FROM " . tablename($this->table_classify) . " where sid = :sid ", array(':sid' => $student['xq_id']));
	$mybj = pdo_fetch("SELECT sname,sid FROM " . tablename($this->table_classify) . " where sid = :sid ", array(':sid' => $student['bj_id']));
	if(!empty($sid)){
		$myallqh = get_myqh($student['bj_id'],$schoolid);
		$list = pdo_fetchall("SELECT id FROM " . tablename($this->table_score) . " where schoolid = :schoolid And weid = :weid And sid = :sid", array(
			':weid' => $weid,
			':schoolid' => $schoolid,
			':sid'=>$sid
		));
		$cjcount = count($list);		
		include $this->template(''.$school['style2'].'/chaxun');
	}else{
		session_destroy();
		$stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
		header("location:$stopurl");
		exit;
	}
?>