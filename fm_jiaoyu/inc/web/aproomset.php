<?php
  global $_GPC, $_W;
   
	$weid = $_W['uniacid'];
	$action = 'apartmentset';
	$this1 = 'no7';
	$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'],$action);
	$schoolid = intval($_GPC['schoolid']);
	$logo = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
	$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where id = :id ", array(':id' => $schoolid));			
	$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
	$apartmentlist = pdo_fetchall('SELECT * FROM ' . tablename($this->table_apartment) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' ORDER BY CONVERT(name USING gbk) ASC ");
	$xueqi = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type  ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'semester', ':schoolid' => $schoolid));
	$bj   = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type  ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'theclass', ':schoolid' => $schoolid));
	$tid_global = $_W['tid'];




	if ($operation == 'display') {
		if (!(IsHasQx($tid_global,1003211,1,$schoolid))){
			$this->imessage('非法访问，您无权操作该页面','','error');	
		}
		$pindex = max(1, intval($_GPC['page']));
		$psize = 10;
		$condition = '';
		if(!empty($_GPC['apid'])){
			$condition .="and apid ='{$_GPC['apid']}' ";
		}
		if(!empty($_GPC['njid']) && empty($_GPC['bjid'])){
			$students = pdo_fetchall('SELECT roomid FROM ' . tablename($this->table_students) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}'and xq_id = '{$_GPC['njid']}' ");
			$roomid_str = '';
			foreach($students as $key_s=>$value_s){
				$roomid_str .= $value_s['roomid'].",";
			}
			$roomid_str = trim($roomid_str,",");
			$condition .="and FIND_IN_SET(id,'{$roomid_str}') ";
		}
		if(!empty($_GPC['bjid'])){
			$students = pdo_fetchall('SELECT roomid FROM ' . tablename($this->table_students) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}'and bj_id = '{$_GPC['bjid']}' ");
			$roomid_str = '';
			foreach($students as $key_s=>$value_s){
				$roomid_str .= $value_s['roomid'].",";
			}
			$roomid_str = trim($roomid_str,",");
			$condition .="and FIND_IN_SET(id,'{$roomid_str}') ";
		}
		if(!empty($_GPC['StuName'])){
			$students = pdo_fetchall('SELECT roomid FROM ' . tablename($this->table_students) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and s_name LIKE '%{$_GPC['StuName']}%' ");
			$roomid_str = '';
			foreach($students as $key_s=>$value_s){
				$roomid_str .= $value_s['roomid'].",";
			}
			$roomid_str = trim($roomid_str,",");
			$condition .="and FIND_IN_SET(id,'{$roomid_str}') ";
		}
		if(!empty($_GPC['RoomName'])){
			$condition .="and name like '%{$_GPC['RoomName']}%' ";
		}		
		$list = pdo_fetchall("SELECT * FROM " . tablename($this->table_aproom) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition ORDER BY apid DESC ,ssort DESC ,CONVERT(name USING gbk) ASC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
		foreach($list as $index => $row){
			$apname = pdo_fetch("SELECT name FROM " . tablename($this->table_apartment) . " WHERE id = '{$row['apid']}'");
			$list[$index]['apname'] = $apname['name'];
			$list[$index]['stuCount']= pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_students) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and roomid = '{$row['id']}'");
		} 
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_aproom) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition");
		$pager = pagination($total, $pindex, $psize);
	}elseif($operation == 'post'){
		if (!(IsHasQx($tid_global,1003212,1,$schoolid))){
			$this->imessage('非法访问，您无权操作该页面','','error');	
		} 
		$id = intval($_GPC['id']);
		if(!empty($id)){
			$item = pdo_fetch("SELECT * FROM " . tablename($this->table_aproom) . " WHERE id = '{$id}'");
			$students = pdo_fetchall('SELECT id,s_name,sex FROM ' . tablename($this->table_students) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}'and roomid = '{$id}' ");
		}else{
			$item = array(
				'ssort' => 0,
			);
		}
		if(checksubmit('submit')){
			if(!empty($id)){
				$ssort = $_GPC['ssort'];
				$name = $_GPC['name'];
				$apid = $_GPC['ApId'];
				$noon_start  = $_GPC['noon_start'];
                $noon_end = $_GPC['noon_end'];
                $morning_start  = $_GPC['morning_start'];
				$morning_end = $_GPC['morning_end'];
                $zdy_start  = $_GPC['zdy_start'];
				$zdy_end = $_GPC['zdy_end'];
                $zdy1_start  = $_GPC['zdy1_start'];
				$zdy1_end = $_GPC['zdy1_end'];
				$night_start = $_GPC['night_start'];
				$night_end = $_GPC['night_end'];
				$stuidarr = $_GPC['stu_id'];
				$noon_deadline = $_GPC['noon_deadline'];
				$night_deadline = $_GPC['night_deadline'];
				if(empty($name)){
					$this->imessage('抱歉，宿舍名称不能为空！', referer(), 'error');
				}
				$check_name = pdo_fetch('SELECT id FROM ' . tablename($this->table_aproom) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}'and name = '{$name}' and id != '{$id}'  ");
				if(!empty($check_name)){
					$this->imessage('抱歉，宿舍名称重复！', referer(), 'error');
				}
				if($morning_end < $morning_start){
					$this->imessage('抱歉，早间出入时段结束时间不能早于开始时间！', referer(), 'error');
				}
				if($zdy_end < $zdy_start){
					$this->imessage('抱歉，自定义出入时段结束时间不能早于开始时间！', referer(), 'error');
				}
				if($zdy1_end < $zdy1_start){
					$this->imessage('抱歉，自定义1出入时段结束时间不能早于开始时间！', referer(), 'error');
				}
				if($noon_end < $noon_start){
					$this->imessage('抱歉，午间出入时段结束时间不能早于开始时间！', referer(), 'error');
				}
				if($night_end < $night_start){
					$this->imessage('抱歉，晚间出入时段结束时间不能早于开始时间！', referer(), 'error');
				}
                $students_old = pdo_fetchall('SELECT id,s_name FROM ' . tablename($this->table_students) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}'and roomid = '{$id}' ");
                foreach($students_old as $value_old){
                    pdo_update($this->table_students,array('roomid'=>0), array('id' => $value_old['id']));
                }
				$data = array(
					'weid'     => $weid,
					'schoolid' => $_GPC['schoolid'],
					'name'     => $name,
					'ssort'    => intval($ssort),
					'apid'     => $apid,
					'morning_start'  => $morning_start,
					'morning_end'    => $morning_end,
					'zdy_start'    => $zdy_start,
					'zdy_end'    => $zdy_end,
					'zdy1_start'    => $zdy1_start,
					'zdy1_end'    => $zdy1_end,
					'noon_start'  => $noon_start,
					'noon_end'    => $noon_end,
					'night_start' => $night_start,
					'night_end'   => $night_end,
					'floornum'    => $_GPC['floornum'],
					'noon_deadline'   => $noon_deadline,
					'night_deadline' => $night_deadline
				);	
				pdo_update($this->table_aproom,$data, array('id' => $id));
				foreach( $stuidarr as $kery_s=>$value_s){
					pdo_update($this->table_students,array('roomid'=>$id), array('id' => $value_s));
				}
			}else{
				$ssort = $_GPC['ssort'];
				$name = $_GPC['name'];
				$apid = $_GPC['ApId'];
				$noon_start  = $_GPC['noon_start'];
				$noon_end = $_GPC['noon_end'];
				$morning_start  = $_GPC['morning_start'];
				$morning_end = $_GPC['morning_end'];
				$zdy_start  = $_GPC['zdy_start'];
				$zdy_end = $_GPC['zdy_end'];
				$zdy1_start  = $_GPC['zdy1_start'];
				$zdy1_end = $_GPC['zdy1_end'];
				$night_start = $_GPC['night_start'];
				$night_end = $_GPC['night_end'];
                $stuidarr = $_GPC['stu_id'];
                $noon_deadline = $_GPC['noon_deadline'];
				$night_deadline = $_GPC['night_deadline'];
				if(empty($name)){
					$this->imessage('抱歉，宿舍名称不能为空！', referer(), 'error');
				}
				$check_name = pdo_fetch('SELECT id FROM ' . tablename($this->table_aproom) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}'and name = '{$name}'  ");
				if(!empty($check_name)){
					$this->imessage('抱歉，宿舍名称重复！', referer(), 'error');
				}
				if($morning_end < $morning_start){
					$this->imessage('抱歉，早间出入时段结束时间不能早于开始时间！', referer(), 'error');
				}
				if($zdy_end < $zdy_start){
					$this->imessage('抱歉，自定义出入时段结束时间不能早于开始时间！', referer(), 'error');
				}
				if($zdy1_end < $zdy1_start){
					$this->imessage('抱歉，自定义1出入时段结束时间不能早于开始时间！', referer(), 'error');
				}
				if($noon_end < $noon_start){
					$this->imessage('抱歉，午间出入时段结束时间不能早于开始时间！', referer(), 'error');
				}
				if($night_end < $night_start){
					$this->imessage('抱歉，晚间出入时段结束时间不能早于开始时间！', referer(), 'error');
				}
				$data = array(
					'weid'     => $weid,
					'schoolid' => $_GPC['schoolid'],
					'name'    => $name,
					'ssort'    => intval($ssort),
					'apid' => $apid,
					'noon_start' => $noon_start,
					'noon_end' => $noon_end,
					'morning_start' => $morning_start,
					'morning_end' => $morning_end,
					'zdy_start' => $zdy_start,
					'zdy_end' => $zdy_end,
					'zdy1_start' => $zdy1_start,
					'zdy1_end' => $zdy1_end,
					'night_start' => $night_start,
                    'night_end' => $night_end,
                    'floornum'    => $_GPC['floornum'],
                    'noon_deadline'   => $noon_deadline,
					'night_deadline' => $night_deadline
				);	
				pdo_insert($this->table_aproom, $data);
				$roomid = pdo_insertid();
				foreach( $stuidarr as $kery_s=>$value_s){
					pdo_update($this->table_students,array('roomid'=>$roomid), array('id' => $value_s));
				}
			}
		   $this->imessage('更新宿舍信息成功！',$this->createWebUrl('aproomset', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
		}
	}elseif ($operation == 'delete') {
		$id = $_GPC['id'];
		$schoolid = $_GPC['schoolid'];
		$check = pdo_fetch('SELECT * FROM ' . tablename($this->table_aproom) . " WHERE schoolid = '{$schoolid}' and id = '{$id}'"); 
		if(empty($check)){
			$this->imessage('删除失败，未查询到该条信息', referer(), 'error');
		}else{
			$students_old = pdo_fetchall('SELECT id,s_name FROM ' . tablename($this->table_students) . " WHERE schoolid = '{$schoolid}'and roomid = '{$id}' ");
			foreach($students_old as $value_old){
				pdo_update($this->table_students,array('roomid'=>0), array('id' => $value_old['id']));
			}
			pdo_delete($this->table_aproom,array('id'=>$id));
			 $this->imessage('删除成功',$this->createWebUrl('aproomset', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
		}	  
	}elseif ($operation == 'getstuByRoomid') {
		$schoolid = $_GPC['schoolid'];
		$roomid = $_GPC['roomid'];
		$stulist = pdo_fetchall("SELECT id,s_name,bj_id,xq_id,icon as thumb FROM " . tablename($this->table_students) . " where schoolid = '{$schoolid}' And  roomid = '{$roomid}' "); 
		foreach($stulist as $key_s =>$value_s){
			$bjname = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE schoolid = '{$schoolid}' and sid = '{$value_s['bj_id']}'");
			$njname = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE schoolid = '{$schoolid}' and sid = '{$value_s['xq_id']}'");
			$stulist[$key_s]['bjname'] = $bjname['sname'];
			$stulist[$key_s]['njname'] = $njname['sname'];
			$stulist[$key_s]['icon'] = $value_s['thumb']?tomedia($value_s['thumb']):tomedia($school['spic']);
		}
		die(json_encode(array(
			'result' => true,
            'stulist' => $stulist
		)));  
	}	  

   include $this->template ( 'web/aproomset' );
?>