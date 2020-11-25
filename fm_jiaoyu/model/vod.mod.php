<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
defined('IN_IA') or exit('Access Denied');
load()->func('communication');

function check_have_try($schoolid,$videoid,$userid){ //检查是否试看过本视频
	$check_try = pdo_fetch("SELECT id FROM " . GetTableName('camerask') . " WHERE schoolid = '{$schoolid}' And carmeraid = '{$videoid}' And userid = '{$userid}' ");
	if(!$check_try){
		return true;
	}else{
		return false;
	}
}

function check_vod_pay($schoolid, $sid, $videoid, $userid) { //查询是否有观看视频 并返回 1 禁止观看  2允许观看 3请续费
	//检查全家情况
	$check_vod_order = pdo_fetchall("SELECT userid,vodtype,paytime FROM " . GetTableName('order') . " where schoolid = '{$schoolid}' And sid = '{$sid}' And vodid = '{$videoid}' And type = 7 And status = 2 ORDER BY id DESC");
	$allow = 1;//禁止观看,请购买
	if($check_vod_order){
		$num = count($check_vod_order);
		$vod = pdo_fetch("SELECT days FROM " . GetTableName('allcamera') . " where schoolid = '{$schoolid}' And id = '{$videoid}'");		
		foreach ($check_vod_order as $key => $value) {
			if($value['vodtype'] == 'one'){ 
				if($value['userid'] == $userid){  //检查到自己的ID
					$time = $vod['days'] * 86400;
					$times = $time + $value['paytime'];
					if($times > time()){
						$allow = 2;//允许观看
						break;						
					}else{
						$allow = 3;//单人观看续费
					}
				}					
			}else{ //非自己ID处理				
				$time = $vod['days'] * 86400;
				$times = $time + $value['paytime'];
				if($times > time()){
					$allow = 2;//允许观看
					break;						
				}else{
					$allow = 4;//全家观看续费
				}
			}			
		}
	}
	return $allow;
}

function check_holiday($classid,$time){ //根据班级检查今天是否假日 true = 上课 false = 放假
	$allow = false;
	$classinfo = pdo_fetch("SELECT weid,schoolid,datesetid FROM " . GetTableName('classify') . " WHERE sid = '{$classid}'");
	$weid = $classinfo['weid'];
	$schoolid = $classinfo['schoolid'];
	$nowdate = date("Y-n-j", $time); 
	$nowyear = date("Y", $time); 
	$nowweek = date("w", $time);
	if (!empty($classinfo['datesetid'])) {
		$checkdateset = pdo_fetch("SELECT * FROM " . GetTableName('checkdateset') . " WHERE weid = '{$weid}' And schoolid = {$schoolid} and  id = '{$classinfo['datesetid']}'");
		$checkdateset_holi = pdo_fetch("SELECT * FROM " . GetTableName('checkdatedetail') . " WHERE weid = '{$weid}' And schoolid = {$schoolid} and  checkdatesetid = '{$classinfo['datesetid']}' and year = '{$nowyear}' ");
		$checktime = pdo_fetchall("SELECT * FROM " . GetTableName('checktimeset') . " WHERE weid = '{$weid}' And schoolid = {$schoolid} and  checkdatesetid = '{$classinfo['datesetid']}' and date = '{$nowdate}' ORDER BY id ASC ");
		if (!empty($checktime)) {
			if ($checktime[0]['type'] == 6) {
				//1放假2上课
				$todaytype = 1;
				$allow = false;
			} elseif ($checktime[0]['type'] == 5) {
				$todaytype = 2;
				$allow = true;
			}
		} else {
			if (($nowdate >= $checkdateset_holi['win_start'] && $nowdate <= $checkdateset_holi['win_end']) || ($nowdate >= $checkdateset_holi['sum_start'] && $nowdate <= $checkdateset_holi['sum_end'])) {
				$todaytype = 1;
				$allow = false;
			} else {
				//星期五
				if ($nowweek == 5) {
					$todaytype = 2;
					$allow = true;
					//星期六
				} elseif ($nowweek == 6) {
					if ($checkdateset['saturday'] == 1) {
						$todaytype = 2;
						$allow = true;
					} else {
						$todaytype = 1;
						$allow = false;
					}
					//星期天
				} elseif ($nowweek == 0) {
					if ($checkdateset['sunday'] == 1) {
						$todaytype = 2;
						$allow = true;
					} else {
						$todaytype = 1;
						$allow = false;
					}
					//工作日
				} else {
					$todaytype = 2;
					$allow = true;
				}
			}
		}
	}else{
		$allow = true;
	}
	return $allow;	
}
