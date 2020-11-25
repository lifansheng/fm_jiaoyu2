<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
        global $_W, $_GPC;
        $weid = $_W['uniacid'];
		$schoolid = intval($_GPC['schoolid']);
		$openid = $_W['openid'];
        
        //查询是否用户登录		
		$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $userid['id']));
		$tid_global = $it['tid'];
		$teacherinfo = pdo_fetch("SELECT status,fz_id FROM " . tablename($this->table_teachers) . " where id = '{$tid_global}' ");
		if($teacherinfo['status'] == 2){
			$apartmentlist = pdo_fetchall('SELECT id,name FROM ' . tablename($this->table_apartment) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' ORDER BY CONVERT(name USING gbk) ASC ");
		}else{
			$apartmentlist = pdo_fetchall('SELECT id,name FROM ' . tablename($this->table_apartment) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and  FIND_IN_SET('{$tid_global}',tid) ORDER BY CONVERT(name USING gbk) ASC ");
		}

		if(!empty($_GPC['apid'])){
			$apid = $_GPC['apid'];
		}else{
			$apid = $apartmentlist[0]['id'];
		}
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
		//$apid = 3 ;
		$apartmentinfo = pdo_fetch('SELECT id,name FROM ' . tablename($this->table_apartment) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and id = '{$apid}' ");
		
        if(!empty($userid['id'])){
			
			$roomlist = pdo_fetchall('SELECT * FROM ' . tablename($this->table_aproom) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and apid = '{$apid}' ORDER BY CONVERT(name USING gbk) ASC ");
			$room_str = '';

			
/* 			$studentapall = pdo_fetchall('SELECT * FROM ' . tablename($this->table_students) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and FIND_IN_SET(roomid,'{$room_str}')  ");
			$apcount = count($studentapall); */
			
			$allcount = 0 ;
			$all_in = 0 ;
			$all_out = 0 ;
			$all_errorr = 0 ;
			$all_leave = 0 ;
			$time = time();
			$roomlist = pdo_fetchall('SELECT * FROM ' . tablename($this->table_aproom) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and apid = '{$apid}' ORDER BY CONVERT(name USING gbk) ASC  ");
			foreach($roomlist as $key_r =>$value_r){
				$this_in = 0 ;
				$this_out = 0 ;
				$this_errorr = 0 ;
				$this_leave = 0 ;
				$student_all = pdo_fetchall('SELECT * FROM ' . tablename($this->table_students) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and roomid = '{$value_r['id']}'  ");
				foreach($student_all as $key_s => $value_s){
					$allcount ++ ;
					$check_leave =  pdo_fetch('SELECT * FROM ' . tablename($this->table_leave) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and sid = '{$value_s['id']}' and isliuyan = 0 and status = 1  and startime1 <='{$time}' and endtime1 >='{$time}' ");
					if(!empty($check_leave)){
							$this_leave++ ;
							$all_leave++;
					}else{
						$check_ap =  pdo_fetch('SELECT ap_type FROM ' . tablename($this->table_checklog) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and roomid = '{$value_r['id']}' and sid = '{$value_s['id']}' and sc_ap = 1 and (ap_type = 1 or ap_type = 2) order by createtime DESC   ");
						if($check_ap['ap_type'] == 1){
							$this_in++ ;
							$all_in++;
						}elseif($check_ap['ap_type'] == 2){
							$this_out++ ;
							$all_out++;
						}else{
							$this_errorr++ ;
							$all_errorr++;
						}
					}
				}
				$roomlist[$key_r]['count_in'] =$this_in;
				$roomlist[$key_r]['count_out'] =$this_out;
				$roomlist[$key_r]['count_errorr'] =$this_errorr;
				$roomlist[$key_r]['count_leave'] =$this_leave;
				$roomlist[$key_r]['count_all'] =count($student_all);				

				
			}
			//var_dump($roomlist);
			include $this->template(''.$school['style3'].'/tstuapinfo');
			

        }else{
			session_destroy();
            $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
        }        
?>