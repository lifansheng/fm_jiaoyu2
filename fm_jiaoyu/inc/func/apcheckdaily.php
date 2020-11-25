<?php
global $_GPC, $_W;
$weid = $_GPC['i'];
$schoolid = $_GPC['schoolid'];
$CheckType = $_GPC['checktype'];

	$roomlist = pdo_fetchall('SELECT noon_start,noon_deadline,night_start,night_deadline,id FROM ' . GetTableName('aproom'). " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}'   "); //寝室列表
	foreach($roomlist as $key_r =>$value_r){
		$date = date("Y-m-d",time());
		$NowTime = date("H:i",time());
		if(!empty($_GPC['date'])){
			$date = date("Y-m-d",$_GPC['date']);
		}
		$noon_start     = strtotime($date.' '.$value_r['noon_start']);
		$noon_deadline  = strtotime($date.' '.$value_r['noon_deadline']);
		$NotcheckNoon = false;
		$NotcheckNight = false;
		if(strtotime($noon_deadline) > strtotime($NowTime)){ //如果中午的deadline比当前时间还要大，说明还不能统计中午的
			$NotcheckNoon = true; 
		}
		$night_start    = strtotime($date.' '.$value_r['night_start']);
		$night_deadline = strtotime($date.' '.$value_r['night_deadline']);

		if(strtotime($night_deadline) > strtotime($NowTime)){ //如果晚上的deadline比当前时间还要大，说明不能统计晚上的
			$NotcheckNight = true;
			 
		}

		$condition_noon = " createtime > '{$noon_start}' and createtime < '{$noon_deadline}' and sc_ap = 1  ORDER BY createtime DESC LIMIT 0 ,1 "; //今天午间归寝时间内的记录 取一条的话就是最近的一次记录
		$condition_night = " createtime > '{$night_start}' and createtime < '{$night_deadline}' and sc_ap = 1 ORDER BY createtime DESC LIMIT 0 ,1 "; //今天晚间归寝时间内的记录 取一条的话就是最近的一次记录
		$students =  pdo_fetchall('SELECT id,s_name,icon,xq_id,bj_id FROM ' . tablename($this->table_students) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and roomid = '{$value_r['id']}' and s_type =2 "); //寝室下的所有住校生 ，半通生 不算
		foreach($students as $value_s) {
			if($CheckType != '3' && NotcheckNoon != true ){ //不是只检查晚上,且不跳过中午
				$check_noon  = pdo_fetch(" SELECT id, FROM ".GetTableName('checklog')." WHERE schoolid = '{$schoolid}' and weid = '{$weid}' and sid = '{$value_s['id']}' and ap_type =1 and $condition_noon ");
				if(empty($check_noon)){ //如果取不到中午的寝室考勤记录，或者中午的考勤记录 不是回寝室，就说明学生没有按时归寝
					$insert_data = array(
						'sid' =>$value_s['id'],
						'roomid' => $value_r['id'],
						'date'=>strtotime($date),
						'weid' => $weid,
						'schoolid' => $schoolid,
						'type' => '1'
					);
					$check = pdo_fetch("SELECT id FROM ".GetTableName('roomcheck')." WHERE sid = '{$value_s['id']}' and roomid = '{$value_r['id']}' and date = '{$insert_data['date']}' and type = '1'  ");
					if(empty($check)){
						pdo_insert(GetTableName('roomcheck',false),$insert_data);
					}
					//$StuList[] = $insert_data;
				};
			}
			if($CheckType != '2' && NotcheckNight != true){
				$check_night = pdo_fetch(" SELECT id,ap_type FROM ".GetTableName('checklog')." WHERE schoolid = '{$schoolid}' and weid = '{$weid}' and sid = '{$value_s['id']}' and ap_type =1 and $condition_night ");
				if(empty($check_night) || $check_night['ap_type'] != 1 ){ //晚上同理。
					$insert_data = array(
						'sid' =>$value_s['id'],
						'roomid' => $value_r['id'],
						'date'=>strtotime($date),
						'weid' => $weid,
						'schoolid' => $schoolid,
						'type' => '2'
					);
					$check = pdo_fetch("SELECT id FROM ".GetTableName('roomcheck')." WHERE sid = '{$value_s['id']}' and roomid = '{$value_r['id']}' and date = '{$insert_data['date']}' and type = '2'  ");
					if(empty($check)){
						pdo_insert(GetTableName('roomcheck',false),$insert_data);
					}
					$StuList[] = $insert_data;
				};
			}
		}	
    }
    $result['status'] = 1;
    $result['msg'] = "操作成功";
	die(json_encode($result));
