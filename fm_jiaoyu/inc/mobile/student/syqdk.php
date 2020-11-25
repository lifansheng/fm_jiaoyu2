<?php
/*
 * @Discription:  
 * @Author: Hannibal·Lee
 * @Date: 2020-02-14 14:09:20
 * @LastEditTime: 2020-02-24 13:55:37
 */

$weid = $_W['uniacid'];
$openid = $_W['openid'];	
$schoolid = $_GPC['schoolid'];
$userss = intval($_GPC['userid']);
$act = "wd";
//查询是否用户登录
if(empty($schoolid)){
	$itess = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = '{$weid}' And openid = '{$openid}' And tid = 0 ");
	if(!empty($itess)){
		$userss = $itess['id'];
	}
}
if(!empty($userss)){
	$ite = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where  id = :id ", array(':id' => $userss));
	if(!empty($ite)){
		$_SESSION['user'] = $ite['id'];
		$schoolid = $ite['schoolid'];
	}else{
		session_destroy();
		$stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('myschool', array('schoolid' => $schoolid));
		header("location:$stopurl");
		exit;
	}			
}else{
	if(empty($_SESSION['user'])){
		$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :tid = tid LIMIT 0,1 ", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':tid' => 0), 'id');
		if(!empty($userid)){
			$_SESSION['user'] = $userid['id'];
			$schoolid = $userid['schoolid'];
		}
	}
}

$user =  get_myallclass($weid,$openid);
$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And openid = :openid AND id=:id ", array(':schoolid' => $schoolid,':openid' => $openid, ':id' => $_SESSION['user']));
//统计连续打卡次数
$dkcount = pdo_fetchcolumn("SELECT count(*) FROM ".GetTableName('yqdk')." WHERE sid = '{$it['sid']}' ");
if($user != false){
//查询是否用户登录		
	$student = pdo_fetch("SELECT icon,s_name,bj_id FROM " . tablename($this->table_students) . " where weid = :weid AND id = :id", array(':weid' => $_W ['uniacid'], ':id' => $it['sid']));
	$bj = pdo_fetch("SELECT sname,sid FROM " . tablename($this->table_classify) . " where sid = :sid", array(':sid' => $student['bj_id']));
	$nj = pdo_fetch("SELECT sid FROM " . tablename($this->table_classify) . " where parentid = :parentid", array(':parentid' => $bj['sid']));
	$school = pdo_fetch("SELECT style2 FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ORDER BY ssort DESC", array(':weid' => $weid, ':id' => $schoolid));
	$operation = $_GPC['op'] ? $_GPC['op'] : 'display';	

	if($operation == 'display'){
		// 获取打卡项设置
		$starttime = mktime(0,0,0,date("m"),date("d"),date("Y"));
		$endtime = $starttime + 86399;
		$condition = " AND createtime > '{$starttime}' AND createtime < '{$endtime}' AND sid = '{$it['sid']}'";
		$log = pdo_fetch("SELECT id FROM " . GetTableName('yqdk') . " WHERE :schoolid = schoolid $condition LIMIT 0,1", array(':schoolid' => $schoolid));
		$schoolset = pdo_fetch("SELECT yqdkset FROM " . tablename($this->table_schoolset) . " where weid = :weid AND schoolid = :schoolid", array(':weid' => $weid, ':schoolid' => $schoolid));
		$set = unserialize($schoolset['yqdkset']);
		$yqdkset = []; //疫情设置结果集
		$yqselect = yqselect(); //疫情选择值
		if(!empty($set)){
			foreach ($set as $key => $value) {
				$yqdkset[$key] = $yqselect[$value];
				$yqdkset[$key]['type'] = $value;
			}
		}
		
		//获取最新一条打卡记录
		$newyqdk =pdo_fetch("SELECT * FROM " . GetTableName('yqdk') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' And sid = '{$it['sid']}' ORDER BY createtime DESC LIMIT 0,1");
		$newyqdk['content'] = unserialize($newyqdk['content']);
	}
    include $this->template(''.$school['style2'].'/syqdk');
}else{
    session_destroy();
    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
    header("location:$stopurl");
}     
   
