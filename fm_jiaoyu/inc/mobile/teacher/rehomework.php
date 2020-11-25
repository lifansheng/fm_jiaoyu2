<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_W, $_GPC;
$weid = $_W ['uniacid'];
$schoolid = intval($_GPC['schoolid']);
$noticeid = intval($_GPC['noticeid']);



$noticeinfo = pdo_fetch("SELECT title,anstype FROM " . tablename($this->table_notice) . " where :id = id", array(':id' => $noticeid));

$openid = $_W['openid'];
mload()->model('que');






$bj_id = intval($_GPC['bj_id']);
/* if(!empty($_GPC['sid'])){
	$sid = intval($_GPC['sid']);
}else{
	$fristsid = pdo_fetch("SELECT id FROM " . tablename($this->table_students) . " where schoolid = :schoolid And bj_id = :bj_id ORDER BY id DESC", array(':schoolid' => $schoolid, ':bj_id' => $bj_id));
	$sid = $fristsid['sid'];
} */

//检查老师登陆
$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0));
$teacher = pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " where :id = id", array(':id' => $it['tid']));
if(!empty($it)){

	$school = pdo_fetch("SELECT style3,title,spic,headcolor FROM " . GetTableName('index') . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
	$leave = pdo_fetch("SELECT title,anstype,bj_id,kc_id FROM " . GetTableName('notice') . " where :id = id", array(':id' => $noticeid));
	$anstype = unserialize($leave['anstype']);

	if($_W['schooltype'] == false ){ //公立模式
		$bj_id = intval($_GET['bj_id']);
		$nowbj = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " where :sid = sid", array(':sid' => $bj_id));
		$allstud = pdo_fetchall("SELECT id,s_name,icon FROM " . tablename($this->table_students) . " where schoolid = :schoolid And bj_id = :bj_id ORDER BY id DESC", array(':schoolid' => $schoolid, ':bj_id' => $bj_id));
	}elseif($_W['schooltype'] == true){//培训模式
		$kc_id = $leave['kc_id'];
		$nowbj = pdo_fetch("SELECT name as  sname FROM " . GetTableName('tcourse') . " where :id = id", array(':id' => $kc_id));
		$allstud = pdo_fetchall("SELECT students.id,students.s_name,students.icon FROM " . GetTableName('order') . " as stuorder , " . GetTableName('students') . " as students where stuorder.schoolid = '{$schoolid}' And stuorder.kcid = '{$kc_id}' and stuorder.status = 2 and stuorder.type = 1 and stuorder.sid = students.id group by students.id  ORDER BY students.id DESC ");
		
	}
	//var_dump($_W['schooltype']);
	

	if(!empty($_GPC['sid'])){
		$sid = intval($_GPC['sid']);
	}else{
		$sid = $allstud[0]['sid'];
	}
	
	 
	//如果是电脑端发布的作业，$ZY_contents不为空
	$ZY_contents = GetZyContent($noticeid,$schoolid,$weid);
	//$answer获取的是手机端发布的作业的回答内容
	
	$answer = GetMyAnswer_type7($sid,$noticeid);
	$remark = pdo_fetch("SELECT * FROM " . tablename($this->table_ans_remark) . " where weid='{$weid}' and  schoolid='{$schoolid}' And tid ='{$it['tid']}' And sid = '{$sid}' And zyid ='{$noticeid}' ");
	//$testAA获取的是电脑端发布的作业的回答内容
	$testAA =GetMyAnswerAll($sid,$noticeid);
	if(is_showgkk())
	{
	//$teaPy 获取当前回答的批阅_C(不管是不是这个老师的)
	$teaPy = GetPyContent_c($sid,$noticeid);
	//$teaPy 获取当前回答的批阅_P(不管是不是这个老师的)
	$teaPy_p = GetPyContent_p($sid,$noticeid);
	}
	
	
	$student = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where schoolid = :schoolid AND id = :id", array(':schoolid' => $schoolid, ':id' => $sid));
	include $this->template(''.$school['style3'].'/rehomework');
}else{
	session_destroy();
	$stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
	header("location:$stopurl");
}
