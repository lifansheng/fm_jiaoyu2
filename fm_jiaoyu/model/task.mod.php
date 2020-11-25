<?php 
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
defined('IN_IA') or exit('Access Denied');
load()->func('communication');

function check_all_task($schoolid){
	$suc = 0;
	$def = 0;
	$alljobs = pdo_fetchall("SELECT kcid FROM " . tablename('wx_school_task') . " WHERE schoolid = '{$schoolid}' And status = 1 ");
	if($alljobs){
		foreach($alljobs as $key => $row){
			if($row['type'] == 1){//课程提醒
				$kcinfo = pdo_fetch("SELECT is_tx,txtime FROM " . tablename ('wx_school_tcourse') . " where id = :id ", array(':id' => $row['kcid']));
				if($kcinfo['is_tx'] == 2){
					$send_kctx = send_kctx($row['kcid'],$kcinfo['txtime']);
					if($send_kctx['status']){
						$suc++;
					}else{
						$def++;
					}
				}
			}
		}
	    $data['suc'] = $suc;
		$data['def'] = $def;
		$data['txrs']= $send_kctx['txrs'];
		$data['msg'] = "成执行".$suc."个提醒任务,"."失败".$def."个";
		$data['result'] = true;
	}else{
	    $data['msg'] = "当前无可执行任务";
		$data['result'] = true;
	}
	return $data;
}

function send_kctx($kcid,$txtime){
	$starttime = mktime(0,0,0,date("m"),date("d"),date("Y"));
	$endtime = $starttime + 86399;
	$condition = " AND date > '{$starttime}' AND date < '{$endtime}'";
	$allks = pdo_fetchall("SELECT id,weid,schoolid,sd_id FROM " . tablename('wx_school_kcbiao') . " WHERE kcid = '{$kcid}' $condition ");
	if($allks){
		foreach($allks as $key => $row){
			$sdinfo = pdo_fetch("SELECT sd_start FROM " . tablename ('wx_school_classify') . " where sid = :sid ", array(':sid' => $row['sd_id']));
			if($sdinfo){
				$checksend = pdo_fetchall("SELECT * FROM " . tablename('wx_school_task_list') . " WHERE ksid = '{$row['id']}' And type = 1 ");//查询提醒任务
				if(empty($checksend)){//为空则执行
					$nowtime = time();
					$check_start = strtotime(date("Y-m-d",$$nowtime).date(" H:i",$sdinfo['sd_start']));
					$ksstarttime = $check_start - $txtime*60;
					if($nowtime >= $ksstarttime){//判断时间
						$data = array(
						    'weid' => $row['weid'],
							'schoolid' =>  $row['schoolid'],
							'ksid' => $row['id'],
							'type' => 1,
							'createtime' => $nowtime
						);
						pdo_insert('wx_school_task_list', $data);
						$this->sendMobileJssktx($row['id'],$row['schoolid'],$row['weid']);
					}
				}
			}
		}
	}
}
?>