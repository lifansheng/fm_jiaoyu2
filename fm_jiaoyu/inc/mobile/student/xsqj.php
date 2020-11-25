<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_W, $_GPC;
$weid = $_W['uniacid'];
$schoolid = intval($_GPC['schoolid']);
$openid = $_W['openid'];
$schooltype = $_W['schooltype'];
$userss = intval($_GPC['userid']);
$obid = 1;
$schoolset = pdo_fetch("SELECT * FROM ".GetTableName('schoolset')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' ");
if(!empty($userss)){
	$ite = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And weid = :weid AND id=:id ", array(':schoolid' => $schoolid,':weid' => $weid, ':id' => $userss));
	if(!empty($ite)){
		$_SESSION['user'] = $ite['id'];
	}else{
		$stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('myschool', array('schoolid' => $schoolid));
		header("location:$stopurl");
		exit;
	}			
}else{
	if(empty($_SESSION['user'])){
		$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :tid = tid LIMIT 0,1 ", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':tid' => 0), 'id');
		$_SESSION['user'] = $userid['id'];
	}
}
//查询是否用户登录
$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where id = :id ", array(':id' => $_SESSION['user']));

$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
if(keep_Blacklist()){
	//获取病因填写字段内容
	$By_content = unserialize($schoolset['bingyincontent']);
}

$student = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where weid = :weid AND id=:id AND schoolid=:schoolid ", array(':weid' => $weid, ':id' => $it['sid'], ':schoolid' => $schoolid));
$category = pdo_fetch("SELECT * FROM " . tablename($this->table_classify) . " WHERE sid =  :sid AND type = :type ", array(':sid' => $student['bj_id'], ':type' => 'theclass'));

$tid = $category['tid'];
$techers = pdo_fetchall("SELECT * FROM " . tablename($this->table_teachers) . " WHERE weid = :weid AND schoolid = :schoolid ", array(':weid' => $weid, ':schoolid' => $schoolid), 'id');


if(!empty($it)){
	$item = pdo_fetch("SELECT * FROM " . tablename ( 'mc_members' ) . " where uniacid=:uniacid AND uid=:uid ", array(':uid' => $it['uid'], ':uniacid' => $weid));  $userinfo = iunserializer($it['userinfo']);
	$this->checkobjiect($schoolid, $student['id'], $obid);
	if($_W['schooltype']){
        $mykc = pdo_fetchall("SELECT t.id, t.name FROM " . GetTableName('coursebuy') . " as c LEFT JOIN " . GetTableName('tcourse') . " as t ON c.kcid = t.id WHERE c.schoolid = :schoolid And c.sid = :sid and c.is_change != :is_change group by c.kcid ", array( ':schoolid' => $schoolid, ':sid' => $it['sid'], 'is_change' => 1 )); 
		include $this->template(''.$school['style2'].'/xsqjpx');
	}else{
		include $this->template(''.$school['style2'].'/xsqj');
	}
}else{
	session_destroy();
	$stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
	header("location:$stopurl");
	exit;
}        
?>