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
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
		$tid_global = $it['tid'];
		$teacherinfo = pdo_fetch("SELECT status,fz_id FROM " . tablename($this->table_teachers) . " where id = '{$tid_global}' ");
		

		
        if(!empty($userid['id'])){
			$roomid = $_GPC['roomid'];
			
			$roominfo = pdo_fetch('SELECT * FROM ' . tablename($this->table_aproom) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and id = {$roomid} ");
			
			$roomlist = pdo_fetchall('SELECT * FROM ' . tablename($this->table_aproom) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and apid = '{$roominfo['apid']}' ORDER BY CONVERT(name USING gbk) ASC ");
			
			$student_all = pdo_fetchall('SELECT * FROM ' . tablename($this->table_students) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and roomid = '{$roomid}'  ");
			$this_in = 0 ;
			$this_out = 0 ;
			$this_errorr = 0 ;
			$this_leave = 0 ;
			$all_count = count($student_all);
			$time = time();

		 	foreach($student_all as $key_s => $value_s){
				if(empty($value_s['icon'])){
					$student_all[$key_s]['icon'] = $school['spic'];
				}
				$check_leave =  pdo_fetch('SELECT * FROM ' . tablename($this->table_leave) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and sid = '{$value_s['id']}' and isliuyan = 0 and status = 1  and startime1 <='{$time}' and endtime1 >='{$time}' ");
				if(!empty($check_leave)){
					$this_leave++ ;
					$student_all[$key_s]['ap_type'] = "请假";
					$student_all[$key_s]['this_time'] =date("m月d日H:i",$check_leave['endtime1'])."结束";
				}else{
					$check_ap =  pdo_fetch('SELECT ap_type,createtime FROM ' . tablename($this->table_checklog) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and roomid = '{$roomid}' and sid = '{$value_s['id']}' and sc_ap = 1 and (ap_type = 1 or ap_type = 2) order by createtime DESC   ");
					if($check_ap['ap_type'] == 1){
						$this_in++ ;
						$student_all[$key_s]['ap_type'] = "归寝";
						$student_all[$key_s]['this_time'] =date("m-d H:i",$check_ap['createtime']);
					}elseif($check_ap['ap_type'] == 2){
						$this_out++ ;
						$student_all[$key_s]['ap_type'] = "离寝";
						$student_all[$key_s]['this_time'] =date("m-d H:i",$check_ap['createtime']);
					}else{
						$this_errorr++ ;
						$student_all[$key_s]['ap_type'] = "异常";
						$student_all[$key_s]['this_time'] ="异常";
					}
				}
			} 
			
			include $this->template(''.$school['style3'].'/tsturoominfo');
			

        }else{
			session_destroy();
            $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
        }        
?>