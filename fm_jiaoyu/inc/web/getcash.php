<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_GPC, $_W;

$weid = $_W['uniacid'];
$action = 'getcash';
$this1 = 'no4';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'],$action);
$schoolid = intval($_GPC['schoolid']);
$logo = pdo_fetch("SELECT logo,title FROM " . GetTableName('index') . " WHERE id = '{$schoolid}'");
$school = pdo_fetch("SELECT spic,tpic FROM " . GetTableName('index') . " where id = :id ", array(':id' => $schoolid));
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$tid_global = $_W['tid'];
if (!(IsHasQx($tid_global,1006002,1,$schoolid))){
	$this->imessage('非法访问，您无权操作该页面','','error');	
}
mload()->model('kc');
if ($operation == 'ruleset') {
	$ruleset = pdo_fetch("SELECT * FROM " . GetTableName('getcashrule') . " WHERE weid = :weid And schoolid = :schoolid ", array(':weid' => $weid,':schoolid' => $schoolid));
	$payweid = pdo_fetchall("SELECT * FROM " . tablename('account_wechats') . " where level = 4 ORDER BY acid ASC");
} elseif ($operation == 'saverule') {
	$ruleset = pdo_fetch("SELECT id FROM " . GetTableName('getcashrule') . " WHERE weid = :weid And schoolid = :schoolid ", array(':weid' => $weid,':schoolid' => $schoolid));
	$txsetdata = array(
		'weid'     => $weid,
		'schoolid' => $schoolid,
		'payweid' => empty($_GPC['payweid']) ? $weid : $_GPC['payweid'],
		'user_max' => $_GPC['user_max'],
		'user_min' => $_GPC['user_min'],
		'user_oneorder_max' => $_GPC['user_oneorder_max'],
		'getcashtimes' => $_GPC['getcashtimes'],
		'every_days' => $_GPC['every_days'],
		'ruleword' => urldecode($_GPC['ruleword']),
	);
	if (empty($ruleset)) {
		pdo_insert(GetTableName('getcashrule',false), $txsetdata);
	}else{
		pdo_update(GetTableName('getcashrule',false), $txsetdata, array('id' => $ruleset['id']));
	}
	$result['result'] = true;
	die(json_encode($result));
} elseif ($operation == 'display') {
	$pindex = max(1, intval($_GPC['page']));
	$psize = 15;
	$condition = '';
	if (!empty($_GPC['number'])) {
		$condition .= " AND id = '{$_GPC['number']}'";
	}		
	if (!empty($_GPC['realname'])) {
		$realname = intval($_GPC['realname']);
		$condition .= " AND realname = '{$realname}' ";
	}
	if (!empty($_GPC['mobile'])) {
		$mobile = intval($_GPC['mobile']);
		$condition .= " AND mobile = '{$mobile}' ";
	}
		
	$is_pay = isset($_GPC['is_pay']) ? intval($_GPC['is_pay']) : -1;
	if($is_pay >= 0) {
		$params[':is_pay'] = $is_pay;
		if($is_pay == 1){
			$condition .= " AND (approval = 0 OR approval = '')";
		}
		if($is_pay == 2){
			$condition .= " AND (approval = 1 OR approval = 2)";
		}
		if($is_pay == 3){
			$condition .= " AND status = 1 ";
		}
		if($is_pay == 4){
			$condition .= " AND status = 2 ";
		}
	}	
	if(!empty($_GPC['createtime']['start'])) {
		$starttime = strtotime($_GPC['createtime']['start']);
		$endtime = strtotime($_GPC['createtime']['end']) + 86399;
		$condition .= " AND createtime > '{$starttime}' AND createtime < '{$endtime}'";
	} else {
		$starttime = strtotime('-1200 day');
		$endtime = TIMESTAMP;
	}
	$params[':start'] = $starttime;
	$params[':end'] = $endtime;
	$list = pdo_fetchall("SELECT * FROM " . GetTableName('getcash') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
	foreach($list as $index => $row){
		if($row['payweid']){
			$payweid = pdo_fetch("SELECT name FROM " . tablename('account_wechats') . " where uniacid = :uniacid ", array(':uniacid' => $row['payweid']));
		}
		if($row['tid']){
			$tesinfo = pdo_fetch("SELECT tname,thumb FROM " . GetTableName('teachers') . " WHERE id = :id ", array(':id' => $row['tid']));
			$list[$index]['icon'] = !empty($tesinfo['thumb'])?tomedia($tesinfo['thumb']):tomedia($school['tpic']);
		}
		if($row['sid']){
			$stuinfo = pdo_fetch("SELECT s_name,icon FROM " . GetTableName('students') . " WHERE id = :id ", array(':id' => $row['sid']));
			$list[$index]['icon'] = !empty($stuinfo['icon'])?tomedia($stuinfo['icon']):tomedia($school['spic']);
		}
		if($list[$index]['shtid'] == -1) {
			$list[$index]['who'] = "管理员";
		}elseif($list[$index]['shtid'] != -1){
			if($list[$index]['shtid'] =='founder'){
				$list[$index]['who'] = '站长';
			}elseif($list[$index]['shtid'] =='owner'){
				$list[$index]['who'] = '主管理员';
			}else{
				$list[$index]['who'] = pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " WHERE id = :id ", array(':id' => $list[$index]['shtid']))['tname'];
			}
		}
		$list[$index]['payweidname'] = $payweid['name'];
	}
	$total = pdo_fetchcolumn('SELECT COUNT(id) FROM ' . GetTableName('getcash') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition");
	$pager = pagination($total, $pindex, $psize);			
	/*导出数据*/
	if ($_GPC['out_put'] == 'output') {
		$outlist = pdo_fetchall("SELECT * FROM " . GetTableName('getcash') . " WHERE weid = '{$weid}' AND schoolid ={$schoolid} $condition ORDER BY id DESC");
		$ii = 0;
		foreach($outlist as $index => $row){
			$teainfo = pdo_fetch("SELECT tname FROM " . GetTableName('teachers') . " WHERE id = :id ", array(':id' => $row['tid']));		
			$arr[$ii]['id'] = $row['id'];
			$arr[$ii]['tname'] = $teainfo['tname'];
			$arr[$ii]['realname'] = $row['realname'];
			$arr[$ii]['mobile'] = $row['mobile'];
			$arr[$ii]['oldfee'] = $row['oldfee'];
			$arr[$ii]['fee'] = $row['fee'];
			$arr[$ii]['shtname'] = CheckPkUser($row['shtid']);
			$arr[$ii]['payname'] = CheckPkUser($row['paytid']);
			$arr[$ii]['paytype'] = '';
			if($row['paytype'] == 1){
				$arr[$ii]['paytype'] = '微信付款';
			}
			if($row['paytype'] == 2){
				$arr[$ii]['paytype'] = '现金付款';
			}
			if($row['paytype'] == 2){
				$arr[$ii]['paytype'] = '其他方式';
			}
			$arr[$ii]['status'] = '';
			if($row['status'] == 1){
				$arr[$ii]['status'] = '未到账';
			}
			if($row['status'] == 2){
				$arr[$ii]['status'] = '已到账';
			}
			if($row['createtime']){
				$arr[$ii]['createtime'] = date('Y-m-d',$row['createtime']);
			}
			$arr[$ii]['dztime'] = '';
			if($row['dztime']){
				$arr[$ii]['dztime'] = date('Y-m-d H:i',$row['dztime']);
			}
			$ii++;
		}
		$this->exportexcel($arr, array('订单号','提现人','真实姓名','手机号','提现金额','到账金额','审核人','拨款人','付款方式','是否到账','申请时间','到账时间'), '提现记录');
		exit();
	}
} elseif ($operation == 'orderinfo') {
	$webtemp = !empty($_GPC['webtemp']) ? $_GPC['webtemp'] : 'orderinfo';
	$allpayweid = pdo_fetchall("SELECT * FROM " . tablename('account_wechats') . " where level = 4 ORDER BY acid ASC");
	$ruleset = pdo_fetch("SELECT * FROM " . GetTableName('getcashrule') . " WHERE weid = :weid And schoolid = :schoolid ", array(':weid' => $weid,':schoolid' => $schoolid));
	$order = pdo_fetch("SELECT * FROM " . GetTableName('getcash') . " WHERE id = '{$_GPC['orderid']}' AND schoolid = '{$schoolid}' ");
	$allks = pdo_fetchall("SELECT * FROM " . GetTableName('getcash_order') . " WHERE payid = '{$order['id']}' AND schoolid = '{$schoolid}' ORDER BY id ASC ");
	if($allks){
		foreach($allks as $k => $row){
			$signinfo = pdo_fetch("SELECT tid,sid,qrtid,costnum,ismaster_tid,createtime FROM " . GetTableName('kcsign') . " WHERE id = :id ", array(':id' => $row['signid']));
			$ksinfo = pdo_fetch("SELECT kcid FROM " . GetTableName('kcbiao') . " WHERE id = :id ", array(':id' => $row['ksid']));
			$kcinfo = pdo_fetch("SELECT name FROM " . GetTableName('tcourse') . " WHERE id = :id ", array(':id' => $row['kcid']));
			$kctxrule = pdo_fetch("SELECT type FROM " . GetTableName('kc_getcashrule') . " WHERE kcid = :kcid ", array(':kcid' => $ksinfo['kcid']));
			$allks[$k]['kcname'] = $kcinfo['name'];
			$allks[$k]['costnum'] = $signinfo['costnum'];
			$allks[$k]['ismaster_tid'] = $signinfo['ismaster_tid'];
			$ksorder = GetOneKcKsOrder($row['kcid'],$row['ksid']);
			$allks[$k]['ksorder'] = '第'.$ksorder['nuber'].'节';
			$allks[$k]['istea'] = false;
			if($signinfo['sid']){
				$stuinfo = pdo_fetch("SELECT s_name FROM " . GetTableName('students') . " WHERE id = :id ", array(':id' => $signinfo['sid']));
				$allks[$k]['signname'] = $stuinfo['s_name'];
			}
			if($signinfo['tid']){
				$teainfo = pdo_fetch("SELECT tname FROM " . GetTableName('teachers') . " WHERE id = :id ", array(':id' => $signinfo['tid']));
				$allks[$k]['signname'] = $teainfo['tname'];
				$allks[$k]['istea'] = true;
			}
			if($signinfo['qrtid']){
				$shtea = pdo_fetch("SELECT tname FROM " . GetTableName('teachers') . " WHERE id = :id ", array(':id' => $signinfo['qrtid']));
				$allks[$k]['shtname'] = $shtea['tname'];
			}else{
				$allks[$k]['shtname'] = '自动';
			}
			$allks[$k]['kstime'] = GetSignTimeTypeByTime($signinfo['createtime'],$signinfo['ksid']);
			$allks[$k]['ruletype'] = $kctxrule['type'];
			//$txinfo = GetFeeBySignId($row['signid']);
			//$allks[$k]['ksfee'] = $txinfo['fee'];
		}
	}
	//提现人信息
	if($order['payweid']){
		$payweid = pdo_fetch("SELECT name FROM " . tablename('account_wechats') . " where uniacid = :uniacid ", array(':uniacid' => $order['payweid']));
	}
	if($order['tid']){
		$tesinfo = pdo_fetch("SELECT tname,thumb FROM " . GetTableName('teachers') . " WHERE id = :id ", array(':id' => $order['tid']));
		$username = $tesinfo['tname'];
		$usericon = !empty($tesinfo['thumb'])?tomedia($tesinfo['thumb']):tomedia($school['tpic']);
	}
	if($order['sid']){
		$stuinfo = pdo_fetch("SELECT s_name,icon FROM " . GetTableName('students') . " WHERE id = :id ", array(':id' => $order['sid']));
		$username = $stuinfo['s_name'];
		$usericon = !empty($stuinfo['icon'])?tomedia($stuinfo['icon']):tomedia($school['spic']);
	}
	//审核人信息
	$shicon = tomedia($school['tpic']);
	if($order['shtid'] == -1) {
		$shname = "管理员";
	}elseif($order['shtid'] != -1){
		if($order['shtid'] =='founder'){
			$shname = '站长';
		}elseif($order['shtid'] =='owner'){
			$shname = '主管理员';
		}else{
			$shtea = pdo_fetch("SELECT tname,thumb FROM " . GetTableName('teachers') . " WHERE id = :id ", array(':id' => $order['shtid']));
			$shname = $shtea['tname'];
			$shicon = !empty($shtea['thumb'])?tomedia($shtea['thumb']):tomedia($school['tpic']);
		}
	}
	//操作付款人信息
	$payicon = tomedia($school['tpic']);
	if($order['paytid'] == -1) {
		$payname = "管理员";
	}elseif($order['paytid'] != -1){
		if($order['paytid'] =='founder'){
			$payname = '站长';
		}elseif($order['paytid'] =='owner'){
			$payname = '主管理员';
		}else{
			$paytea = pdo_fetch("SELECT tname,thumb FROM " . GetTableName('teachers') . " WHERE id = :id ", array(':id' => $order['paytid']));
			$payname = $paytea['tname'];
			$payicon = !empty($paytea['thumb'])?tomedia($paytea['thumb']):tomedia($school['tpic']);
		}
	}
	if($webtemp == 'orderinfo'){
		include $this->template ( 'web/getcash/orderinfo' );
	}elseif ($webtemp == 'optroder'){
		if ($order['approval']){
			if ($order['approval'] == 1){
				$fri = 'current';
				$friicon = 'fa fa-ban';
			}
			if ($order['approval'] == 2){
				$fri = 'done';
				$friicon = 'fa fa-check-circle';
			}
		}else{
			$fri = 'current';
			$friicon = 'fa fa-clock-o';
		}
		if($order['dztime']){
			$sec = 'done';
		}else{
			if ($order['paytid'] && $order['approval'] == 2){
				$sec = 'current done';
			}
			if (!$order['paytid'] && $order['approval'] == 2){
				$sec = 'current';
			}
			if ($order['approval'] == 1 || $order['approval'] == 0){
				$sec = 'disabled';
			}
		}
		if ($order['paytid'] && $order['approval'] == 2){
			$secicon = 'fa fa-check-circle';
		}
		if (!$order['paytid'] && $order['approval'] == 2){
			$secicon = 'fa fa-clock-o';
		}
		if (!$order['approval'] || $order['approval'] == 1){
			$secicon = '';
		}
		$thi = 'disabled';
		if(!$order['dztime'] && !$order['paytid']){
			$thiicon = '';
			$thi = 'disabled';
		}
		if($order['dztime'] && $order['paytid']){
			$thi = 'done current';
			$thiicon = 'fa fa-check-circle';
		}
		if(!$order['dztime'] && $order['paytid']){
			$thiicon = 'fa fa-clock-o';
			$thi = 'current';
		}
		include $this->template ( 'web/getcash/optorder' );
	}
	die();
} elseif ($operation == 'PassOrder') {
	$order = pdo_fetch("SELECT * FROM " . GetTableName('getcash') . " WHERE id = '{$_GPC['orderid']}' AND schoolid = '{$schoolid}' ");
	if(!empty($order)){
		if($_GPC['approval'] == 'pass'){
			$approval = 2;
		}
		if($_GPC['approval'] == 'defail'){
			$approval = 1;
		}
		$dataarry = array(
			'approval' => $approval,
			'shtid' => $tid_global,
			'shrank' => $_GPC['shrank'],
			'shtime' => time()
		);
		pdo_update(GetTableName('getcash',false), $dataarry, array('id' => $order['id']));
		if($tid_global == 'founder' || $tid_global == 'owner' || $tid_global == 'vice_founder'){
			if($_GPC['approval'] == 'pass'){
				$result['msg'] = '审核通过,请进行下一步拨款操作';
			}
			if($_GPC['approval'] == 'defail'){
				$result['msg'] = '已拒绝本次申请';
			}
		}else{
			if($_GPC['approval'] == 'pass'){
				$result['msg'] = '审核通过,下一步待财务拨款';
			}
			if($_GPC['approval'] == 'defail'){
				$result['msg'] = '已拒绝本次申请';
			}
		}
		$this->sendMobileTxshtz($order['id'], $schoolid, $weid);
		$result['result'] = true;
	}else{
		$result['msg'] = '订单不存在或已删除,请刷新本页面';
		$result['result'] = false;
	}	
	die(json_encode($result));
} elseif ($operation == 'pay') {
	$paytype = $_GPC['paytype'];
	$order = pdo_fetch("SELECT * FROM " . GetTableName('getcash') . " WHERE id = '{$_GPC['orderid']}' AND schoolid = '{$schoolid}' ");
	if(!empty($order)){
		$dataarry = array(
			'paytid' => $tid_global,
			'payweid' => $_GPC['payweid'],
			'status' => 1,
			'fee' => $_GPC['fee'],
			'paytype' => $paytype,
			'payrank' => $_GPC['payrank'],
			'paytime' => time()
		);
		if($paytype == 2 || $paytype == 3){
			$dataarry['payweid'] = 0;
			$dataarry['status'] = 2;
			$dataarry['dztime'] = time();
			pdo_update(GetTableName('getcash_order',false), array('status' => 2), array('payid' => $order['id']));//同步修改子订单的状态
		}
		pdo_update(GetTableName('getcash',false), $dataarry, array('id' => $order['id']));
		$this->sendMobileTxshtz($order['id'], $schoolid, $weid);
		$result['result'] = true;
		$result['msg'] = '处理成功';
	}else{
		$result['msg'] = '订单不存在或已删除,请刷新本页面';
		$result['result'] = false;
	}	
	die(json_encode($result));	
} elseif ($operation == 'DelOneKs') {
	$id = $_GPC['id'];
	$order = pdo_fetch("SELECT payid,fee FROM " . GetTableName('getcash_order') . " WHERE id = '{$id}' ");
	if(!empty($order)){
		$cash = pdo_fetch("SELECT oldfee FROM " . GetTableName('getcash') . " WHERE id = '{$order['payid']}'  ");
		$result['fee'] = $cash['oldfee'] - $order['fee'];
		pdo_update(GetTableName('getcash',false), array('oldfee' => $result['fee']), array('id' => $order['payid']));
		pdo_delete(GetTableName('getcash_order',false), array('id' => $id));
		$result['result'] = true;
		$result['msg'] = '操作成功,提现总额已变动！';
	}else{
		$result['result'] = false;
		$result['msg'] = '本提现课时不存在或已删除,请刷新本页面';
	}	
	die(json_encode($result));
} elseif ($operation == 'delteorder') {	
	$id = $_GPC['orderid'];
	$order = pdo_fetch("SELECT id FROM " . GetTableName('getcash') . " WHERE id = '{$id}' ");
	if(!empty($order)){
		$cashorder = pdo_fetch("SELECT id FROM " . GetTableName('getcash_order') . " WHERE payid = '{$id}' And schoolid = '{$schoolid}' ");
		if(!empty($cashorder)){
			pdo_delete(GetTableName('getcash_order',false), array('payid' => $id));
		}
		pdo_delete(GetTableName('getcash',false), array('id' => $id));
		$result['result'] = true;
		$result['msg'] = '删除本申请和本申请下属提现组成成功！';
	}else{
		$result['result'] = false;
		$result['msg'] = '本提现申请不存在或已删除,请刷新本页面';
	}
	die(json_encode($result));
}
include $this->template ( 'web/getcash/getcash' );
