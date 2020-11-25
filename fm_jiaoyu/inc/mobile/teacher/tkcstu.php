<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_W, $_GPC;
$weid = $_W['uniacid'];
$schoolid = intval($_GPC['schoolid']);
$kcid = $_GPC['kcid'];
$school = pdo_fetch("SELECT style3,spic FROM " . GetTableName('index') . " where id = :id ", array(':id' => $schoolid));
$kcinfo = pdo_fetch("SELECT sign_pl_set FROM " . GetTableName('tcourse') . " where id = :id ", array( ':id' => $kcid));
$signset = pdo_fetch("SELECT tea_add_ks FROM " . GetTableName('kc_signset') . " WHERE id = :id ", array(':id' => $kcinfo['sign_pl_set']));
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if($operation == 'display'){
	mload()->model('kc');
	$leave = pdo_fetchall("SELECT id,sid FROM " . GetTableName('coursebuy') . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' And  kcid='{$kcid}' And sid != 0 And is_change != 1 GROUP BY kcid,sid ORDER BY id DESC  LIMIT 0,10 ");
	foreach($leave as $key =>$row){
		$students = pdo_fetch("SELECT id,s_name,numberid,xq_id,sex,icon FROM " . GetTableName('students') . " where weid = '{$weid}' And schoolid = '{$schoolid}' And id = {$row['sid']} ");
		if(!empty($students)){
			$banji = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " where sid = :sid And schoolid = :schoolid ", array(':schoolid' => $schoolid,':sid' => $students['xq_id']));
			$kcksinfo = GetRestKsBySid($kcid,$row['sid']);
			$leave[$key] = $students;
			$leave[$key]['yq'] = $kcksinfo['hasSign'] ? $kcksinfo['hasSign'] : 0;
			$leave[$key]['buy'] = $kcksinfo['buycourse'] ? $kcksinfo['buycourse'] : 0;
			$leave[$key]['rest'] = $kcksinfo['restnumber'];
			$leave[$key]['banji'] = $banji['sname'];
			$leave[$key]['sid']	=$leave[$key]['id'];
			$leave[$key]['id'] = $row['id'];
		}else{
			unset ($leave[$key]);
		}
	}
	include $this->template(''.$school['style3'].'/kc/tkcstu');					        		
}
if($operation == 'morestu'){
	$thistime = trim($_GPC['limit']);
	if($thistime){
		$condition = " And id < '{$thistime}'";	
		$leave1 = pdo_fetchall("SELECT id,sid FROM " . GetTableName('coursebuy') . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' And  kcid='{$kcid}' And sid != 0 And is_change != 1 $condition GROUP BY kcid,sid ORDER BY id DESC  LIMIT 0,10 ");
			foreach($leave1 as $key =>$row){
				$students = pdo_fetch("SELECT id,s_name,numberid,xq_id,sex,icon FROM " . GetTableName('students') . " where weid = '{$weid}' And schoolid = '{$schoolid}' And id = {$row['sid']} ");
				if(!empty($students)){
					$banji = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " where sid = :sid And schoolid = :schoolid ", array(':schoolid' => $schoolid,':sid' => $students['xq_id']));
					$kcksinfo = GetRestKsBySid($kcid,$row['sid']);
					$leave1[$key] = $students;
					$leave1[$key]['yq'] = $kcksinfo['hasSign'] ? $kcksinfo['hasSign'] : 0;
					$leave1[$key]['buy'] = $kcksinfo['buycourse'] ? $kcksinfo['buycourse'] : 0;
					$leave1[$key]['rest'] = $kcksinfo['restnumber'];
					$leave1[$key]['banji'] = $banji['sname'];
					$leave1[$key]['sid']=$leave1[$key]['id'];
					$leave1[$key]['id'] = $row['id'];
				}else{
					unset ($leave1[$key]);
				}
			}
			include $this->template(''.$school['style3'].'/kc/tkcstu');	
	}	
}
if($operation == 'search'){
	$condition1 = "";
	if(!empty($_GPC['condition'])){
		$condition1 .= " And s_name LIKE '%{$_GPC['condition']}%' ";
	}
	if(!empty($condition1)){
		$str = '';
		$students = pdo_fetchall('SELECT id FROM ' . GetTableName('students') . " WHERE schoolid = '{$schoolid}' $condition1 ");
		if(!empty($students)){
			foreach($students as $val){
				$str .= $val['id'].',';
			}
			$str = rtrim($str,',');
		}
		if(!empty($str)){
			$condition .= " And FIND_IN_SET(sid,'{$str}') ";
		}
	}
	if(!empty($condition)){
		$leave1 = pdo_fetchall("SELECT id,sid FROM " . GetTableName('coursebuy') . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' And  kcid='{$kcid}' And is_change != 1 $condition GROUP BY kcid,sid ORDER BY id DESC ");
		foreach($leave1 as $key =>$row){
			$students = pdo_fetch("SELECT id,s_name,numberid,xq_id,sex,icon FROM " . GetTableName('students') . " where weid = '{$weid}' And schoolid = '{$schoolid}' And id = {$row['sid']} ");
			if(!empty($students)){
				$banji = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " where sid = :sid And schoolid = :schoolid ", array(':schoolid' => $schoolid,':sid' => $students['xq_id']));
				$yq = pdo_fetchcolumn("SELECT sum(costnum) FROM " . GetTableName('kcsign') . " where weid = '{$weid}' And schoolid = '{$schoolid}' And sid = {$row['sid']} And kcid = '{$kcid}' And status = 2 ");
				$buy = pdo_fetchcolumn("SELECT ksnum FROM " . GetTableName('coursebuy') . " where weid = '{$weid}' And schoolid = '{$schoolid}' And sid = {$row['sid']} And kcid = '{$kcid}' ");
				$leave1[$key] = $students;
				$leave1[$key]['banji'] = $banji['sname'];
				$leave1[$key]['yq'] = $yq;
				$leave1[$key]['buy'] = $buy?$buy:0;
				$rest = $leave1[$key]['buy'] - $yq;
				$leave1[$key]['rest'] = $rest;
				$leave1[$key]['sid']=$leave1[$key]['id'];
				$leave1[$key]['id'] = $row['id'];
			}else{
				unset ($leave1[$key]);
			}
		}
		include $this->template(''.$school['style3'].'/kc/tkcstu');
	}
}
if($operation == 'addks'){
	if($signset['tea_add_ks'] == 1){
		$id = intval($_GPC['buyid']);$ksnub = intval($_GPC['ksnub']);$tid = intval($_GPC['tid']);
		$thisbuy = pdo_fetch("SELECT id,sid,kcid,ksnum FROM " . GetTableName('coursebuy') . " WHERE  id ='{$id}' And is_change != 1 ");
		if(!empty($thisbuy)){
			$buydata = array();
			$buydata['ksnum'] = $thisbuy['ksnum'] + $ksnub;
			$freedata = array(
				'weid' => $weid,
				'schoolid' => $schoolid,
				'sid' => $thisbuy['sid'],
				'kcid' => $thisbuy['kcid'],
				'ksnum' => $ksnub,
				'tid' => $tid,
				'beizhu' => '手机端赠送',
				'createtime' => time()
			);
			pdo_insert(GetTableName('freekslog',false),$freedata);
			pdo_update(GetTableName('coursebuy',false), $buydata, array('id' => $thisbuy['id']));
			$result['msg'] = "操作成功,你已成功为该生赠送'".$ksnub."'节课时";
			$result['result'] = true;
		}else{
			$result['msg'] = '错误,未查询到该生购买信息！';
			$result['result'] = false;
		}
	}else{
		$result['msg'] = '失败,本课禁止为学生赠送课时！';
		$result['result'] = false;
	}
	die(json_encode($result));
}
?>