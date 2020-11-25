<?php
  	global $_GPC, $_W;
	$weid = $_W['uniacid'];
	$action = 'apcheckall';
	$this1 = 'no7';
	$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'],$action);
	$schoolid = intval($_GPC['schoolid']);
	$logo = pdo_fetch("SELECT logo,title FROM " . GetTableName('index') . " WHERE id = '{$schoolid}'");
	$school = pdo_fetch("SELECT * FROM " . GetTableName('index'). " where id = :id ", array(':id' => $schoolid));			
	$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';

	$xueqi = pdo_fetchall("SELECT sid,sname FROM " . GetTableName('classify') . " where weid = :weid AND schoolid = :schoolid And type = :type  ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'semester', ':schoolid' => $schoolid));
	$bj   = pdo_fetchall("SELECT sid,sname FROM " .GetTableName('classify'). " where weid = :weid AND schoolid = :schoolid And type = :type  ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'theclass', ':schoolid' => $schoolid));
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
		if($tid_global = 'founder' || $tid_global == 'owner'){  //如果是管理员账号，可查看所有楼栋
			$apartmentlist = pdo_fetchall('SELECT id,name FROM ' . tablename($this->table_apartment) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' ORDER BY CONVERT(name USING gbk) ASC ");
		}else{
			$tid = intval($tid_global);
			$teacherinfo =  pdo_fetchall('SELECT * FROM ' . tablename($this->table_teachers) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and id ='{$tid}'  ");
			if($teacherinfo['status'] == 2){ //如果是校长，也可以查看所有楼栋
				$apartmentlist = pdo_fetchall('SELECT id,name FROM ' . tablename($this->table_apartment) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' ORDER BY CONVERT(name USING gbk) ASC ");
			}else{ //否则只能查看自己管理的楼栋
				$apartmentlist = pdo_fetchall('SELECT id,name FROM ' . tablename($this->table_apartment) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and FIND_IN_SET({$tid},tid) ORDER BY CONVERT(name USING gbk) ASC ");
			}
		}	
		if(!empty($_GPC['apid'])){
			$apid = $_GPC['apid'];
		}else{
			$apid =$apartmentlist[0]['id']; 
		}
		$apname = pdo_fetch('SELECT name FROM ' . tablename($this->table_apartment) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and id = '{$apid}'  ")['name'];
		$roomlist = pdo_fetchall('SELECT * FROM ' . tablename($this->table_aproom) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and apid = '{$apid}' ORDER BY CONVERT(name USING gbk) ASC ");
		foreach($roomlist as $key_r =>$value_r){
			$students =  pdo_fetchall('SELECT id,s_name,icon,xq_id,bj_id FROM ' . tablename($this->table_students) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}'and roomid = '{$value_r['id']}' ");
			foreach($students as $key_s =>$value_s){
				$ap_type = 0;
				$check_leave =  pdo_fetch('SELECT * FROM ' . tablename($this->table_leave) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and sid = '{$value_s['id']}' and isliuyan = 0 and status = 1 and startime1 <='{$time}' and endtime1 >='{$time}' ");
				if(!empty($check_leave)){
						$ap_type= 3;
				}else{
					$check_ap =  pdo_fetch('SELECT ap_type FROM ' . tablename($this->table_checklog) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and sid = '{$value_s['id']}' and sc_ap = 1 and(ap_type = 1 or  ap_type = 2) order by createtime DESC   ");
					//var_dump($check_ap);
					$ap_type = $check_ap['ap_type'];
				}
				$students[$key_s]['ap_type'] = $ap_type;
				$students[$key_s]['nj_name'] = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where sid = '{$value_s['xq_id']}' and weid = '{$weid}' and schoolid = '{$schoolid}' ")['sname'];
				$students[$key_s]['bj_name'] = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where sid = '{$value_s['bj_id']}' and weid = '{$weid}' and schoolid = '{$schoolid}' ")['sname'];
				$students[$key_s]['icon'] = $value_s['icon']?$value_s['icon']:$school['spic'];	
			}
			$roomlist[$key_r]['students'] = $students;
		}
	}elseif ($operation == 'checknewinfo') {
		$schoolid = $_GPC['schoolid'];
		$apid = $_GPC['apid'];
		$lasttime = $_GPC['lasttime'];
		//and createtime>='{$lasttime}'
		$back_data = pdo_fetchall('SELECT sid,ap_type FROM ' . tablename($this->table_checklog) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and createtime>='{$lasttime}' and sc_ap = 1 and(ap_type = 1 or  ap_type = 2) and apid = '{$apid}' order by createtime DESC  ");
		$time = time();
		if(!empty($back_data)){
			die(json_encode(array(
				'result' => true,
				'back_data' => $back_data,
				'lasttime' => $time,
			)));
		}else{
			die(json_encode(array(
				'result' => false,
				'lasttime' => $lasttime,
			)));
		}		   
	}	
  // include $this->template ( 'web/aproomset' );
  include $this->template ( 'web/apcheckall' );
