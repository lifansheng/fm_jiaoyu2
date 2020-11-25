<?php

/**
 * 微教育模块
 *
 * @author 高贵血迹
 */

global $_GPC, $_W;
$weid              = $_W['uniacid'];
$action            = 'checkdateset';
$this1             = 'no5';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
$logo              = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$operation         = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if($operation == 'display'){
	$id = intval($_GPC['id']);
	$checksetinfo = pdo_fetch("SELECT * FROM " . tablename($this->table_checkdateset) . " where weid = '{$weid}' And schoolid = '{$schoolid}' And id = '{$id}' ");
	$workdayset = pdo_fetchall("SELECT * FROM " . tablename($this->table_checktimeset) . " where weid = '{$weid}' And schoolid = '{$schoolid}' And checkdatesetid = '{$id}' and type = 1  ORDER BY id ASC ");
	$fridayset = pdo_fetchall("SELECT * FROM " . tablename($this->table_checktimeset) . " where weid = '{$weid}' And schoolid = '{$schoolid}' And checkdatesetid = '{$id}' and type = 2  ORDER BY id ASC ");
	$saturdayset = pdo_fetchall("SELECT * FROM " . tablename($this->table_checktimeset) . " where weid = '{$weid}' And schoolid = '{$schoolid}' And checkdatesetid = '{$id}' and type = 3  ORDER BY id ASC ");
	$sundayset = pdo_fetchall("SELECT * FROM " . tablename($this->table_checktimeset) . " where weid = '{$weid}' And schoolid = '{$schoolid}' And checkdatesetid = '{$id}' and type = 4  ORDER BY id ASC ");
	include $this->template('web/checktimeset');
}elseif($operation == 'submit'){
	$timeid = intval($_GPC['id']);
	$checksetinfo = pdo_fetch("SELECT * FROM " . tablename($this->table_checkdateset) . " where weid = '{$weid}' And schoolid = '{$schoolid}' And id = '{$timeid}' ");
	if($_GPC['one_four']){
		$one_four = $_GPC['one_four'];
		foreach($one_four['id'] as $key => $row){
			$one_fourid = $row;
			$one_fourstart = $one_four['start'][$key];
			$one_fourend = $one_four['end'][$key];
			$one_fourout_in = $one_four['out_in'][$key];
			$one_four_s_type = $one_four['s_type'][$key];
			$dataw = array(
				'weid'=>$weid,
				'schoolid'=>$schoolid,
				'checkdatesetid' => $timeid,
				'start' => $one_fourstart,
				'end' => $one_fourend,
				'out_in' => $one_fourout_in,
				's_type' => $one_four_s_type,
				'type'=>1
			);
			if(($one_fourstart != '00:00' && !empty($one_fourstart)) || ($one_fourend != '00:00' && !empty($one_fourend))){
				if($one_fourid){
					pdo_update($this->table_checktimeset, $dataw, array('id'=>$one_fourid));
				}else{
					pdo_insert($this->table_checktimeset, $dataw);
				}
			}
		}
	}
	if($_GPC['friday'] && $checksetinfo['friday'] == 1){
		$friday = $_GPC['friday'];
		foreach($friday['id'] as $key => $row){
			$friday_id = $row;
			$friday_start = $friday['start'][$key];
			$friday_end = $friday['end'][$key];
			$friday_out_in = $friday['out_in'][$key];
			$friday_s_type = $friday['s_type'][$key];
			$datasa = array(
				'weid'=>$weid,
				'schoolid'=>$schoolid,
				'checkdatesetid' => $timeid,
				'start' => $friday_start,
				'end' => $friday_end,
				'out_in' => $friday_out_in,
				's_type' => $friday_s_type,
				'type'=>2
			);
			if(($friday_start != '00:00' && !empty($friday_start)) || ($friday_end != '00:00' && !empty($friday_end))){
				if($friday_id){
					pdo_update($this->table_checktimeset, $datasa, array('id'=>$friday_id));
				}else{
					pdo_insert($this->table_checktimeset, $datasa);
				}
			}
		}
	}
	if($_GPC['saturday'] && $checksetinfo['saturday'] == 1){
		$saturday = $_GPC['saturday'];
		foreach($saturday['id'] as $key => $row){
			$saturday_id = $row;
			$saturday_start = $saturday['start'][$key];
			$saturday_end = $saturday['end'][$key];
			$saturday_out_in = $saturday['out_in'][$key];
			$saturday_s_type = $saturday['s_type'][$key];
			$datasf = array(
				'weid'=>$weid,
				'schoolid'=>$schoolid,
				'checkdatesetid' => $timeid,
				'start' => $saturday_start,
				'end' => $saturday_end,
				'out_in' => $saturday_out_in,
				's_type' => $saturday_s_type,
				'type'=>3
			);
			if(($saturday_start != '00:00' && !empty($saturday_start)) || ($saturday_end != '00:00' && !empty($saturday_end))){
				if($saturday_id){
					pdo_update($this->table_checktimeset, $datasf, array('id'=>$saturday_id));
				}else{
					pdo_insert($this->table_checktimeset, $datasf);
				}
			}
		}
	}
	if($_GPC['sunday'] && $checksetinfo['sunday'] == 1){
		$sunday = $_GPC['sunday'];
		foreach($sunday['id'] as $key => $row){
			$sunday_id = $row;
			$sunday_start = $sunday['start'][$key];
			$sunday_end = $sunday['end'][$key];
			$sunday_out_in = $sunday['out_in'][$key];
			$sunday_s_type = $sunday['s_type'][$key];
			$datass = array(
				'weid'=>$weid,
				'schoolid'=>$schoolid,
				'checkdatesetid' => $timeid,
				'start' => $sunday_start,
				'end' => $sunday_end,
				'out_in' => $sunday_out_in,
				's_type' => $sunday_s_type,
				'type'=>4
			);
			if(($sunday_start != '00:00' && !empty($sunday_start)) || ($sunday_end != '00:00' && !empty($sunday_end))){
				if($sunday_id){
					pdo_update($this->table_checktimeset, $datass, array('id'=>$sunday_id));
				}else{
					pdo_insert($this->table_checktimeset, $datass);
				}
			}
		}
	}
	mload()->model('xz');
	XzGetSetTimeData($schoolid,$timeid,'update');
	$result['result'] = true;
	die (json_encode($result));
}elseif($operation == 'del_timeset'){
	$checkset = pdo_fetch("SELECT * FROM " . tablename($this->table_checktimeset) . " where id = '{$_GPC['timesetid']}' ");
	if($checkset){
		pdo_delete($this->table_checktimeset, array('id'=>$_GPC['timesetid']));
	}else{

	}
	mload()->model('xz');
	$res = XzGetSetTimeData($schoolid,$_GPC['timesetid'],'update');
	$result['result'] = true;
	die (json_encode($result));
}
?>