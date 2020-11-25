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
$leaveid = intval($_GPC['id']);
mload()->model('que');

if (!empty($_GPC['userid'])){
	$_SESSION['user'] = $_GPC['userid'];
}
//查询是否用户登录		
$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where id = :id ", array(':id' => $_SESSION['user']));
$userid= $it['id'];
$leave = pdo_fetch("SELECT * FROM " . tablename($this->table_notice) . " where :id = id", array(':id' => $leaveid));
$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ORDER BY ssort DESC", array(':weid' => $weid, ':id' => $schoolid));
$ZY_contents = GetZyContent($leaveid,$schoolid,$weid);
if(!empty($userid)){
	// $recode = pdo_fetch("SELECT readtime,id FROM " . tablename($this->table_record) . " where schoolid = :schoolid And noticeid = :noticeid And tid = :tid And userid = :userid", array(':schoolid' => $schoolid,':noticeid' => $leaveid,':tid' => $it['tid'],':userid' => $it['id']));
	// if ($recode){
	// 	if($recode['readtime'] == 0){
	// 		$date = array(
	// 			'readtime' =>time()
	// 		);
	// 		pdo_update($this->table_record, $date, array('id' => $recode['id']));				
	// 	}			
	// }else{
	// 	$data = array(
	// 		'weid' =>  $weid,
	// 		'schoolid' => $schoolid,
	// 		'noticeid' => $leaveid,
	// 		'tid' => $it['tid'],
	// 		'userid' => $it['id'],
	// 		'openid' => $openid,
	// 		'type' => $leave['type'],
	// 		'createtime' => $leave['createtime'],
	// 		'readtime' =>time()
	// 	);
	// 	pdo_insert($this->table_record, $data);		
	// }
	// $userdatas = explode(',',$leave['userdatas']);
	// $dataarr = array();
	// foreach($userdatas as $row){
	// 	if($row == 0 || $row != ""){
	// 		$dataarr[] = intval($row);
	// 	}	
	// }			
	// if($leave['usertype'] == 'bj'){
	// 	mload()->model('stu');
	// 	$arr = GetClassInfoByArr($dataarr,$_W['schooltype'],$schoolid);
	// }
	// if($leave['usertype'] == 'jsfz'){
	// 	mload()->model('tea');
	// 	$arr = GetFzInfoByArr($dataarr,$_W['schooltype'],$schoolid);
	// }
	// $testAA = GetMyAnswerAll_tea($it['tid'] ,$leaveid,$schoolid,$weid);
	// include $this->template($school['style2'].'/squesforminfo');

	$student = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where weid = :weid AND id = :id", array(':weid' => $_W ['uniacid'], ':id' => $it['sid']));
	$member = pdo_fetch("SELECT * FROM " . tablename ( 'mc_members' ) . " where uniacid = :uniacid AND uid = :uid", array(':uniacid' => $_W ['uniacid'], ':uid'=> $leave['uid']));
	$isbzr = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $_W ['uniacid'], ':id' => $it['tid']));
	$picarr = iunserializer($leave['picarr']);
	$recode = pdo_fetch("SELECT * FROM " . tablename($this->table_record) . " where schoolid = :schoolid And noticeid = :noticeid And sid = :sid And userid = :userid", array(':schoolid' => $schoolid,':noticeid' => $leaveid,':sid' => $it['sid'],':userid' => $it['id']));
	$ZY_contents = GetZyContent($leaveid,$schoolid,$weid);
	if ($recode){
		if($recode['readtime'] == 0){
			$date = array(
				'readtime' =>time()
			);
			pdo_update($this->table_record, $date, array('id' => $recode['id']));				
		}				
	}else{
		$data = array(
			'weid' =>  $weid,
			'schoolid' => $schoolid,
			'noticeid' => $leaveid,
			'sid' => $it['sid'],
			'userid' => $it['id'],
			'openid' => $openid,
			'type' => $leave['type'],
			'createtime' => $leave['createtime'],
			'readtime' =>time()
		);
		pdo_insert($this->table_record, $data);		
	}
	$testAA = GetMyAnswerAll($it['sid'] ,$leaveid,$schoolid,$weid);
	include $this->template(''.$school['style2'].'/squesforminfo');
}else{
	session_destroy();
	$stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
	header("location:$stopurl");
}        
?>