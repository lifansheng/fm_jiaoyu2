<?php
/**
 * By 高贵血迹
 */
global $_W, $_GPC;
$weid = $_W['uniacid'];
session_start();
$state = pdo_fetch("SELECT * FROM ".tablename('uni_account_users')." WHERE uid = :uid And uniacid = :uniacid", array(':uid' => $_W['uid'],':uniacid' => $_W['uniacid']));
$_W['role']  = $state['role'];
if($_GPC['from'] == 'depend'){
   $_SESSION["stand_uid"] = $_GPC['uid'];
    $_W['uid'] =  $_SESSION["stand_uid"];
}
$_W['uid'] = $_W['uid']?$_W['uid']:$_SESSION["stand_uid"];

if($_W['isfounder'] || $_W['role'] == 'owner' || $_W['role'] == 'vice_founder') {
	$where = "WHERE weid = '{$weid}'";
}else{
	$uid = $_W['user']['uid'];	
	$where = "WHERE weid = '{$weid}' And uid = '{$uid}' And is_show = 1 ";		
}
 if(empty($_W['uid'])){
	$stopurl = $_W['siteroot'].'/addons/fm_jiaoyu/admin';
	header("location:$stopurl");
 }
$schoollist = pdo_fetchall("SELECT id,title,logo FROM " . tablename($this->table_index) . " $where   order by ssort DESC");
$myadmin = pdo_fetch("SELECT tid,schoolid FROM ".tablename('users')." WHERE uid = :uid ", array(':uid' => $_W['uid']));
$schoolid = intval($_GPC['schoolid']);
$thisindex = pdo_fetch("SELECT weid FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}' ");
$doarray = array('indexajax','school','help');
if($thisindex['weid'] != $weid && !in_array($_GPC['do'], $doarray) ){
	message('当前登录公众号下无学校数据，请切换公众号', './index.php?c=home&a=welcome&do=platform&', 'error');
}
if(!$_W['isfounder'] && $_W['role'] != 'owner' && $_W['role'] != 'vice_founder') {
	if(is_TestFz()){
		   $_W['tid'] = $myadmin['tid'];
		   $schoolset = pdo_fetch("SELECT uid FROM ".tablename($this->table_schoolset)." WHERE FIND_IN_SET('{$_W['uid']}',uid) and weid = '{$weid}'  ");
		   if(!empty($schoolset) && !empty($_W['uid'])){
			   $_W['isfounder'] = true;
			   $_W['tid'] = 'founder';
		   }
	}else {
		if ($myadmin['schoolid'] != $schoolid){
			$this->imessage('正在加载!', $this->createWebUrl('start', array('id' => $myadmin['schoolid'], 'schoolid' => $myadmin['schoolid'])), 'sucess');
	   	}else{
		   $mysf = pdo_fetch("SELECT tname,thumb,fz_id FROM ".tablename($this->table_teachers)." WHERE schoolid = :schoolid And id = :id", array(':schoolid' => $schoolid,':id' => $myadmin['tid']));
		   $myfz = pdo_fetch("SELECT sname,pname FROM ".tablename($this->table_classify)." WHERE schoolid = :schoolid And sid = :sid", array(':schoolid' => $schoolid,':sid' => $mysf['fz_id']));
		   $myfz['sname'] = $myfz['pname'];
		   $_W['tid'] = $myadmin['tid']; 
	   	}
	}
}else{
	if($_W['isfounder']){
		$_W['tid'] = 'founder';
	}
	if($_W['role'] == 'owner' || $_W['role'] == 'vice_founder'){
		$_W['tid'] = 'owner';
	}	
}
if($_GPC['from'] == 'depend'){
    $_SESSION["tid"] = $_W['tid']?$_W['tid']:$myadmin['tid'];
}

if($_W['schooltype'] === true || $_W['schooltype'] === false){
}else{
	$_W['schooltype']  = GetSchoolType($schoolid,$weid);
}
if($_W['sale'] === true || $_W['sale'] === false){
}else{
	set_plugin();
}
if($_W['vis'] === true || $_W['vis'] === false){
}else{
	set_plugin();
}
$fenzu = pdo_fetch("SELECT id FROM ".tablename($this->table_group)." WHERE schoolid = :schoolid And is_zhu = :is_zhu", array(':schoolid' => $schoolid,':is_zhu' => 1));
if($fenzu){
	$code = pdo_fetch("SELECT * FROM ".tablename($this->table_qrinfo)." WHERE gpid = :gpid ", array(':gpid' => $fenzu['id']));
}
?>