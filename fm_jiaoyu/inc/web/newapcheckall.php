<?php
  global $_GPC, $_W;
   
	$weid = $_W['uniacid'];
	$action = 'apcheckall';
	$this1 = 'no7';
	$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'],$action);
	$schoolid = intval($_GPC['schoolid']);
	$logo = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
	$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where id = :id ", array(':id' => $schoolid));			
	$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
	$xueqi = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type  ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'semester', ':schoolid' => $schoolid));
	$bj   = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type  ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'theclass', ':schoolid' => $schoolid));
	
	if($_GPC['hide_show']){
		$hide_show = $_GPC['hide_show'];
	}else{
		$hide_show = 0 ;
	}
	
	if($_GPC['big_small']){
		$big_small = $_GPC['big_small'];
	}else{
		$big_small = 1 ;
	}
	
	$time = time();

	$tid_global = $_W['tid'];
	if ($operation == 'display') {	
		if (!(IsHasQx($tid_global,1003501,1,$schoolid))){
			$this->imessage('非法访问，您无权操作该页面','','error');	
		} 
		if($tid_global = 'founder' || $tid_global == 'owner'){
			$apartmentlist = pdo_fetchall('SELECT id,name FROM ' . tablename($this->table_apartment) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' ORDER BY CONVERT(name USING gbk) ASC ");
		}else{
			$tid = intval($tid_global);
			
			$teacherinfo =  pdo_fetchall('SELECT * FROM ' . tablename($this->table_teachers) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and id ='{$tid}'  ");
			if($teacherinfo['status'] == 2){
				$apartmentlist = pdo_fetchall('SELECT id,name FROM ' . tablename($this->table_apartment) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' ORDER BY CONVERT(name USING gbk) ASC ");
			}else{
				$apartmentlist = pdo_fetchall('SELECT id,name FROM ' . tablename($this->table_apartment) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and FIND_IN_SET('{$tid}',tid) ORDER BY CONVERT(name USING gbk) ASC ");
			}
		}	
		if(!empty($_GPC['apid'])){
			$apid = $_GPC['apid'];
		}else{
			$apid =$apartmentlist[0]['id']; 
		}
		
		$apname = pdo_fetch('SELECT name FROM ' . tablename($this->table_apartment) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and id = '{$apid}'  ")['name'];
		$roomlist = pdo_fetchall('SELECT * FROM ' . tablename($this->table_aproom) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and apid = '{$apid}' ORDER BY CONVERT(name USING gbk) ASC ");
		$roomlist = pdo_fetchall('SELECT * FROM ' . tablename($this->table_aproom) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and apid = '{$apid}' ORDER BY CONVERT(name USING gbk) ASC ");
		$floorlist = pdo_fetchall('SELECT distinct(floornum) FROM ' . tablename($this->table_aproom) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and apid = '{$apid}' order by floornum ");
		$all_leave = 0 ;
		$all_in = 0 ;
		$all_out = 0 ;
		$all_err = 0 ;
		$all_num = 0 ;
		foreach($floorlist as $key_f=>$value_f){
			$roomlist = pdo_fetchall('SELECT * FROM ' . tablename($this->table_aproom) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and apid = '{$apid}' and floornum = '{$value_f['floornum']}' ORDER BY CONVERT(name USING gbk) ASC ");
			
			foreach($roomlist as $key_r =>$value_r){
				$students =  pdo_fetchall('SELECT id,s_name,icon,xq_id,bj_id FROM ' . tablename($this->table_students) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}'and roomid = '{$value_r['id']}' ");
				$all_num += count($students);
				$stu_in = 0;
				$stu_out = 0;
				$stu_err = 0;
				foreach($students as $key_s =>$value_s){
					$ap_type = 0;
					$check_leave =  pdo_fetch('SELECT * FROM ' . tablename($this->table_leave) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and sid = '{$value_s['id']}' and isliuyan = 0 and status = 1 and startime1 <='{$time}' and endtime1 >='{$time}' ");
					if(!empty($check_leave)){
							$ap_type= 3;
							$all_leave ++ ;
							
					}else{
						$check_ap =  pdo_fetch('SELECT ap_type FROM ' . tablename($this->table_checklog) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and sid = '{$value_s['id']}' and sc_ap = 1 and(ap_type = 1 or  ap_type = 2) order by createtime DESC   ");
						//var_dump($check_ap);
						if($check_ap['ap_type'] == 1){
							$stu_in ++;
							$all_in ++ ;
						}elseif($check_ap['ap_type'] == 2){
							$stu_out++;
							$all_out ++;
						}else{
							$stu_err ++;
							$all_err ++ ;
						}
					}	
				}
				$roomlist[$key_r]['stu_in'] = $stu_in;
				$roomlist[$key_r]['stu_out'] = $stu_out;
				$roomlist[$key_r]['stu_err'] = $stu_err;
			}
			$floorlist[$key_f]['foomlist'] = $roomlist;
			
			
		}
		if($all_num != 0 ){
			$in_percent = round($all_in / $all_num * 100,2);		
		}else{
			$in_percent = 0 ;
		}
		
	
	//var_dump($in_percent);
	}elseif ($operation == 'checknewinfo') {
		$schoolid = $_GPC['schoolid'];
		$apid = $_GPC['apid'];
		$lasttime = $_GPC['lasttime'];
		$in_num = $_GPC['in_num'];
		$out_num = $_GPC['out_num'];
		$err_num = $_GPC['err_num'];
		$all_num = $_GPC['all_num'];
		$back_data = pdo_fetchall('SELECT sid,ap_type FROM ' . tablename($this->table_checklog) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and sc_ap = 1 and(ap_type = 1 or  ap_type = 2) and apid = '{$apid}' and createtime>='{$lasttime}' order by createtime DESC  ");
		$time = time();
		$change_room = array();
		if(!empty($back_data)){
			foreach($back_data as $key=>$value){
				$student =  pdo_fetch('SELECT roomid FROM ' . tablename($this->table_students) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and id = '{$value['sid']}' ");
				if($value['ap_type'] == 1){
					$in_num ++ ;
					$out_num -- ;
					if(empty($change_room[$student['roomid']]['in'])){
						$change_room[$student['roomid']]['in'] = 1;
					}else{
						$change_room[$student['roomid']]['in'] = $change_room[$student['roomid']]['in'] + 1;
					}
				}elseif($value['ap_type'] == 2){
					if(empty($change_room[$student['roomid']]['out'])){
						$change_room[$student['roomid']]['out'] = 1;
					}else{
						$change_room[$student['roomid']]['out'] = $change_room[$student['roomid']]['out'] + 1;
					}
					$out_num ++ ;
					$in_num -- ;
				}else{
					$err_num ++;
				}
			}
			$in_percent = $all_in / $all_num * 100; 
			$return_data = array();
			foreach($change_room as $key_c=>$value_c){
				$this_data['roomid'] = $key_c;
				$this_data['out_num'] = $value_c['out'];
				$this_data['in_num'] = $value_c['in'];
				$return_data[] = $this_data;
			}
			die(json_encode(array(
				'result' => true,
				'back_data' => $return_data,
				'in_num' => $in_num,
				'out_num' => $out_num,
				'err_num' => $err_num,
				'in_percent' => $in_percent,
				'lasttime' =>$time ,
			)));
			
		}else{
			die(json_encode(array(
				'result' => false,
				'lasttime' => $lasttime,
			)));
		}
				
			   
	}	

  // include $this->template ( 'web/aproomset' );
  include $this->template ( 'web/newapcheckall' );
?>