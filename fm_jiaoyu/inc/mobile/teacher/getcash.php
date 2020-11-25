<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_W, $_GPC;
$weid = $_W ['uniacid']; 
$openid = $_W['openid'];
$schoolid = intval($_GPC['schoolid']);
//检查是否用户登陆
$it = pdo_fetch("SELECT * FROM " . GetTableName('user') . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0));
$school = pdo_fetch("SELECT style3,title,spic,tpic,logo FROM " . GetTableName('index') . " where weid = :weid AND id = :id ", array(':weid' => $weid, ':id' => $schoolid));
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if(!empty($it)){
	$kclist = pdo_fetchall("SELECT id,name,thumb FROM " . GetTableName('tcourse') . " WHERE schoolid = '{$schoolid}' And kc_type != 1 And tea_getcash = 1 And (tid like '{$it['tid']},%' OR tid like '%,{$it['tid']}' OR tid like '%,{$it['tid']},%' OR tid='{$it['tid']}')  ORDER BY end DESC");
	if($operation == 'display'){
		$teainfo = pdo_fetch("SELECT tname,thumb FROM " . GetTableName('teachers') . " where id = :id ", array(':id' => $it['tid']));
		$thumb = !empty($teainfo['thumb'])?tomedia($teainfo['thumb']):tomedia($school['tpic']);		
		include $this->template(''.$school['style3'].'/getcash/index');
	}
	if($operation == 'get_cash'){
		mload()->model('kc');
		$signarray = $_GPC['orderarr'];
		$casharr = array(
			'weid' => $weid,
			'schoolid'   => $schoolid,
			'tid' 	     => $it['tid'],
			'openid'     => $openid,
			'realname'   => $_GPC['realname'],
			'mobile'     => $_GPC['mobile'],
			'paytype'    => $_GPC['txtype'],
			'contrank'   => $_GPC['rank'],
			'type'     	 => 1,
			'createtime' => time()
		);
		pdo_insert(GetTableName('getcash',false), $casharr);
		$getcashid = pdo_insertid();
		$toalfee = 0;//总额度
		foreach($signarray as $key => $row){
			$getorder = pdo_fetch("SELECT id FROM " . GetTableName('getcash_order') . " WHERE tid = '{$it['tid']}' And tid = '{$row}' And schoolid = '{$schoolid}'  ");
			if(empty($getorder)){
				$signinfo = pdo_fetch("SELECT id,ksid,kcid,costnum FROM " . GetTableName('kcsign') . " WHERE id = '{$row}' ");
				$kctxrule = pdo_fetch("SELECT type FROM " . GetTableName('kc_getcashrule') . " WHERE kcid = :kcid ", array(':kcid' => $signinfo['kcid']));
				$orderarry = array(
					'weid' => $weid,
					'schoolid'   => $schoolid,
					'tid' 	     => $it['tid'],
					'ksid'       => $signinfo['ksid'],
					'kcid'   	 => $signinfo['kcid'],
					'signid'     => $row,
					'payid'   	 => $getcashid,
					'status'     => 1,
					'type'     	 => 1,
					'createtime' => time()
				);
				if($kctxrule['type'] == 1){
					$onefee = GetFeeBySignId($row);
					$toalfee = $toalfee + $onefee['fee'];
					$orderarry['fee'] = $onefee['fee'];
				}
				if($kctxrule['type'] == 2){
					$teasign = pdo_fetch("SELECT id FROM " . GetTableName('kcsign') . " WHERE tid = '{$it['tid']}' And sid = 0 And ksid = '{$signinfo['ksid']}' And kcid = '{$signinfo['kcid']}' And status = 2 ");
					if(!empty($teasign)){
						$getfee = GetFeeBySignId($teasign['id']);
						$onefee = $getfee['fee']*$signinfo['costnum'];
						$orderarry['fee'] = $onefee;
						$toalfee = $toalfee + $onefee;
					}
				}
				pdo_insert(GetTableName('getcash_order',false), $orderarry);
			}
		}
		pdo_update(GetTableName('getcash',false), array('oldfee' => $toalfee,'fee' => $toalfee) , array('id' => $getcashid));
		$result['msg'] = '提交申请成功';
		$result['result'] = true;
		die(json_encode($result));
	}
	if($operation == 'fqtx'){
		mload()->model('kc');
		foreach($kclist as $key => $row){
			$kclist[$key]['xjfee'] = 0;$kclist[$key]['zjnumber'] = 0;$kclist[$key]['fdnumber'] = 0;
			$kctxrule = pdo_fetch("SELECT type FROM " . GetTableName('kc_getcashrule') . " WHERE kcid = :kcid ", array(':kcid' => $row['id']));
			$kclist[$key]['kctxrule'] = $kctxrule['type'];
			$kclist[$key]['thumb'] = !empty($row['thumb'])?tomedia($row['thumb']):tomedia($school['logo']);
			$kclist[$key]['allmysign'] = pdo_fetchall("SELECT * FROM " . GetTableName('kcsign') . " WHERE tid = '{$it['tid']}' And sid = 0 And kcid = '{$row['id']}' And status = 2 ORDER BY createtime DESC ");
			foreach($kclist[$key]['allmysign'] as $v => $k){
				$ksinfo = pdo_fetch("SELECT id,createtime,date FROM " . GetTableName('kcbiao') . " WHERE id = '{$k['ksid']}' ");
				if($k['ismaster_tid'] == 1){$kclist[$key]['zjnumber']++;}
				if($k['ismaster_tid'] == 2){$kclist[$key]['fdnumber']++;}
				if($kctxrule['type'] == 1){//按老师签到提成
					$kclist[$key]['infoword'] = '按我签到计算,我的签到记录:';
					$checkcash = pdo_fetch("SELECT id,createtime FROM " . GetTableName('getcash_order') . " WHERE tid = '{$it['tid']}' And signid = '{$k['id']}' And kcid = '{$row['id']}' ");
					$kclist[$key]['allmysign'][$v]['cangetcash'] = false;
					if(empty($checkcash)){
						$getcash = GetFeeBySignId($k['id']);
						$kclist[$key]['xjfee'] = $kclist[$key]['xjfee'] + $getcash['fee'];
						$kclist[$key]['allmysign'][$v]['cangetcash'] = true;
					}else{
						$kclist[$key]['allmysign'][$v]['txtime'] = date('Y/m/d',$checkcash['createtime']);
					}
				}
				$ksorder = GetOneKcKsOrder($row['id'],$k['ksid']);
				$kclist[$key]['allmysign'][$v]['ksnub'] = $ksorder['nuber'];
				$kclist[$key]['allmysign'][$v]['ksdate'] =  date('m月d',$ksinfo['date']);
				$kclist[$key]['allmysign'][$v]['qddate'] =  date('Y/m/d',$k['createtime']);
				$kclist[$key]['allmysign'][$v]['shtname'] = !empty($k['qrtid'])?CheckPkUser($k['qrtid']):false;
				$kclist[$key]['allmysign'][$v]['tqdleixing'] = GetSignTimeTypeByTime($k['createtime'],$k['ksid']);
				$kclist[$key]['allmysign'][$v]['tqdtype'] = $k['signtype'] == 2?true:false;
				
				if($kctxrule['type'] == 2){//按学生签到提成
					$kclist[$key]['infoword'] = '按学生签我签到的课时计算,全部记录:';
					$kclist[$key]['allmysign'][$v]['allstusign'] = pdo_fetchall("SELECT * FROM " . GetTableName('kcsign') . " WHERE tid = 0 And sid > 0 And ksid = '{$k['ksid']}' And kcid = '{$row['id']}' And status = 2 ORDER BY createtime DESC ");
					$kclist[$key]['allmysign'][$v]['stunumber'] = count($kclist[$key]['allmysign'][$v]['allstusign']);
					$kclist[$key]['allmysign'][$v]['stuksnum'] = 0;
					if($kclist[$key]['allmysign'][$v]['allstusign']){
						foreach($kclist[$key]['allmysign'][$v]['allstusign'] as $s => $i){
							$checkcash = pdo_fetch("SELECT id FROM " . GetTableName('getcash_order') . " WHERE tid = '{$it['tid']}' And signid = '{$i['id']}' And kcid = '{$row['id']}' ");
							$kclist[$key]['allmysign'][$v]['allstusign'][$s]['cangetcash'] = false;
							if(empty($checkcash)){
								$getcash = GetFeeBySignId($k['id']);
								$kclist[$key]['xjfee'] = $kclist[$key]['xjfee'] + $getcash['fee'];
								$kclist[$key]['allmysign'][$v]['allstusign'][$s]['cangetcash'] = true;
							}else{
								$kclist[$key]['allmysign'][$v]['allstusign'][$s]['txtime'] = date('Y/m/d',$checkcash['createtime']);
							}
							$kclist[$key]['allmysign'][$v]['stuksnum'] = $kclist[$key]['allmysign'][$v]['stuksnum'] + $i['costnum'];
							$stuinfo = pdo_fetch("SELECT s_name FROM " . GetTableName('students') . " WHERE id = :id ", array(':id' => $i['sid']));
							$kclist[$key]['allmysign'][$v]['allstusign'][$s]['s_name'] = $stuinfo['s_name'];
							$kclist[$key]['allmysign'][$v]['allstusign'][$s]['s_shtname'] = !empty($i['qrtid'])?CheckPkUser($i['qrtid']):false;
							$kclist[$key]['allmysign'][$v]['allstusign'][$s]['s_qddate'] = date('Y/m/d',$i['createtime']);
							$kclist[$key]['allmysign'][$v]['allstusign'][$s]['s_leixing'] = GetSignTimeTypeByTime($i['createtime'],$k['ksid']);
							$kclist[$key]['allmysign'][$v]['allstusign'][$s]['s_tqdtype'] = $i['signtype'] == 2?true:false;
						}
					}
				}
			}
			
		}
		$start_time = strtotime(date("Y-m-d",time()));
		$end_time = $start_time+60*60*24;
		$condit = " And createtime > '{$start_time}' And createtime < '{$end_time}' ";		
		$teainfo = pdo_fetch("SELECT tname,mobile FROM " . GetTableName('teachers') . " where id = :id ", array(':id' => $it['tid']));
		$realname = empty($it['realname'])?$teainfo['tname']:$it['realname'];
		$mobile = empty($it['mobile'])?$teainfo['mobile']:$it['mobile'];
		$txrule = pdo_fetch("SELECT * FROM " . GetTableName('getcashrule') . " where weid = :weid And schoolid = :schoolid ", array(':weid' => $weid,':schoolid' => $schoolid));
		$restxtimes = 10;
		$ontxmin = 0.01;
		$ontxmax = 5000;
		$restxfee = 5000;
		$cantime = 1;
		if($txrule){
			$ontxmax = $txrule['user_max'];;
			$ontxmin = $txrule['user_min'];
			$restxfee = $txrule['user_oneorder_max'];
			$restxtimes = $txrule['getcashtimes'];
		}
		$todaymycash = pdo_fetchall("SELECT * FROM " . GetTableName('getcash') . " where schoolid = '{$schoolid}' And tid = '{$it['tid']}' $condit ORDER BY createtime DESC");
		if($todaymycash){
			foreach($todaymycash as $tt){
				$restxfee = $restxfee - $tt['oldfee'];
			}
			$restxtimes = $restxtimes - count($todaymycash);
			if((time() - $todaymycash[0]['createtime']) < ($txrule['every_days']*60)){
				$cantime = 2;
			}
		}
		$htlm = $_GPC['getcash'];
		include $this->template(''.$school['style3'].'/getcash/orderlist');
		die();
	}
	if($operation == 'txlist'){
		$mycash = pdo_fetchall("SELECT * FROM " . GetTableName('getcash') . " where schoolid = '{$schoolid}' And tid = '{$it['tid']}' ORDER BY status ASC, createtime DESC ");
		foreach($mycash as $key => $row){
			$checkorder = pdo_fetchall("SELECT id FROM " . GetTableName('getcash_order') . " WHERE schoolid = '{$schoolid}' And tid = '{$it['tid']}' And payid = '{$row['id']}' ");
			$mycash[$key]['ordernumber'] = count($checkorder);
			$mycash[$key]['shtname'] = CheckPkUser($row['shtid']);
			$mycash[$key]['payname'] = CheckPkUser($row['paytid']);
		}
		include $this->template(''.$school['style3'].'/getcash/txlist');
		die();
	}
	if($operation == 'delorder'){
		$cash = pdo_fetchall("SELECT * FROM " . GetTableName('getcash') . " where schoolid = '{$schoolid}' And id = '{$_GPC['id']}' ");
		if(!empty($cash)){
			$cashorder = pdo_fetch("SELECT id FROM " . GetTableName('getcash_order') . " WHERE payid = '{$_GPC['id']}' And schoolid = '{$schoolid}' ");
			if(!empty($cashorder)){
				pdo_delete(GetTableName('getcash_order',false), array('payid' => $_GPC['id']));
			}
			pdo_delete(GetTableName('getcash',false), array('id' => $_GPC['id']));
			$result['result'] = true;
			$result['msg'] = '删除本申请和本申请下属提现组成成功！';
			$result['result'] = true;
			$result['msg'] = '删除本申请成功';
		}else{
			$result['result'] = false;
			$result['msg'] = '订单不存在或已被删除，请刷新本页';
		}
		die(json_encode($result));
	}
	if($operation == 'startinfo'){
		$result['getfee'] = GetMyFeeByTid($schoolid,$it['tid']);
		$result['getingfee'] = 0;$result['getedfee'] = 0;$result['allgetfee'] = 0;$result['getingcount'] = 0;
		$ingorder = pdo_fetchall("SELECT oldfee,fee,status FROM " . GetTableName('getcash') . " WHERE tid = '{$it['tid']}' And schoolid = '{$schoolid}'  ");
		foreach($ingorder as $r){
			if($r['status'] == 2){
				$result['getedfee'] = $result['getedfee'] + $r['fee'];
			}else{
				$result['getingfee'] = $result['getingfee'] + $r['oldfee'];
				$result['getingcount']++;
			}
			$result['allgetfee'] = $result['allgetfee'] + $r['fee'];
		}
		$result['mykscount'] = count($kclist);
		die(json_encode($result));
	}
	if($operation == 'check_fee'){
		$orderarr = $_GPC['orderarr'];
		$result['fee'] = GetMyFeeByTid($schoolid,$it['tid'],$orderarr);
		die(json_encode($result));
	}
}else{
	session_destroy();
	$stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
	header("location:$stopurl");
}
?>