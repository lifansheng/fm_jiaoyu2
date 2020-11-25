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
//$obid = 2;
$act = "tx";
mload()->model('tea');
$schooltype = $_W['schooltype'];
//查询是否用户登录
$it = pdo_fetch("SELECT id,tid,is_allowmsg FROM " . tablename($this->table_user) . " where  weid = :weid And schoolid = :schoolid And openid = :openid And sid = :sid ", array(
	':weid' => $weid,
	':schoolid' => $schoolid,
	':openid' => $openid,
	':sid' => 0
));
$IsOpenDh   = pdo_fetch("select * from " . tablename($this->table_schoolset) . " where schoolid=:schoolid and weid =:weid", array(':schoolid' => $schoolid, ':weid' => $weid));

$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
$techers = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " WHERE schoolid = :schoolid And id = :id", array(':id' => $it['tid'], ':schoolid' => $schoolid));
if (!empty($it)) {

	if ($schooltype) {
		//所有校长组
		$master = pdo_fetchall("SELECT tname,thumb,mobile,id,status,userid,fz_id FROM " . tablename($this->table_teachers) . " WHERE weid = :weid AND schoolid = :schoolid AND status = :status AND is_show = :is_show ORDER BY sort DESC", array(
			':weid' => $weid,
			':schoolid' => $schoolid,
			':status' => 2,
			':is_show' => 0,
		));
		foreach ($master as $key => $value) {
			$master[$key]['Ttitle'] = GetTeacherTitle($value['status'], $value['fz_id']);
		}
		$masterCount = count($master);
		//所有普通教员			
		$master2 = pdo_fetchall("SELECT tname,thumb,mobile,id,status,userid,fz_id FROM " . tablename($this->table_teachers) . " WHERE weid = :weid AND schoolid = :schoolid AND status = :status AND is_show = :is_show ORDER BY sort DESC", array(
			':weid' => $weid,
			':schoolid' => $schoolid,
			':status' => 1,
			':is_show' => 0,
		));
		foreach ($master2 as $key => $value) {
			$master2[$key]['Ttitle'] = GetTeacherTitle($value['status'], $value['fz_id']);
		}
		$masterCount2 = count($master2);
		//培训机构
		$tid = $it['tid'];
		$time = time();
		//当前身份 普通教员 获取授课班级家长
		if ($techers['status'] == 1 || $techers['status'] == 0) {
			$bjlists = pdo_fetchall("select id as bjsid ,name as bjname, end FROM " . tablename($this->table_tcourse) . " WHERE schoolid = '{$schoolid}'  and (tid like '%,{$tid},%'  or tid like '%,{$tid}' or tid like '{$tid},%' or tid ='{$tid}') ORDER BY end DESC , ssort DESC ");
			foreach ($bjlists as $index => $row) {
				$stulist = GetNowKcStu($schoolid, $row['bjsid']);
				if ($row['end'] < $time) {
					$bjlists[$index]['bjname'] .= '--(已结课)';
				}
				foreach ($stulist['stulist'] as $key => $value) {
					$bjlists[$index]['stulist'][$key]['bjname'] = $value['bjname'];
					$bjlists[$index]['stulist'][$key]['count'] = $value['count'];
					foreach ($value['stulist'] as $key2 => $value2) {
						$stuinfo = pdo_fetchall("SELECT realname,mobile,pard,id,uid,is_allowmsg,sid FROM " . tablename($this->table_user) . " WHERE weid = :weid AND schoolid = :schoolid AND sid = :sid ", array(
							':weid' => $weid,
							':schoolid' => $schoolid,
							':sid' => $value2['sid']
						));
						$s_name = pdo_fetch("SELECT s_name FROM " . tablename($this->table_students) . " WHERE weid = :weid AND schoolid = :schoolid AND id = :id ", array(
							':weid' => $weid,
							':schoolid' => $schoolid,
							':id' => $value2['sid']
						))['s_name'];
						foreach ($stuinfo as $key3 => $value3) {
							$member = pdo_fetch("SELECT avatar FROM " . tablename('mc_members') . " where uniacid = :uniacid And uid = :uid ", array(':uniacid' => $weid, ':uid' => $value3['uid']));
							$bjlists[$index]['stulist'][$key]['sid'][$key2]['avatar'] = $member['avatar'];
							$bjlists[$index]['stulist'][$key]['sid'][$key2]['s_name'] = $s_name;
							$bjlists[$index]['stulist'][$key]['sid'][$key2]['realname'] = $value3['realname'];
							$bjlists[$index]['stulist'][$key]['sid'][$key2]['mobile'] = $value3['mobile'];
							$bjlists[$index]['stulist'][$key]['sid'][$key2]['pard'] = $value3['pard'];
							$bjlists[$index]['stulist'][$key]['sid'][$key2]['id'] = $value3['id'];
							$bjlists[$index]['stulist'][$key]['sid'][$key2]['is_allowmsg'] = $value3['is_allowmsg'];
							$bjlists[$index]['stulist'][$key]['sid'][$key2]['sid'] = $value3['sid'];
							$bjlists[$index]['stulist'][$key]['sid'][$key2]['uid'] = $value3['uid'];
						}
					}
				}
				$bjlists[$index]['rs'] = $stulist['kcallstu'];
			}
		}
		//取全校班级的家长   校长
		if ($techers['status'] == 2) {
			$list =  pdo_fetchall("select id as sid ,name as sname, end FROM " . tablename($this->table_tcourse) . " WHERE schoolid = '{$schoolid}'  ORDER BY end DESC , ssort DESC ");
			foreach ($list as $index => $row) {
				if ($row['end'] < $time) {
					$list[$index]['sname'] .= '--(已结课)';
				}
				$stulist = GetNowKcStu($schoolid, $row['sid']);


				foreach ($stulist['stulist'] as $key => $value) {
					$list[$index]['stulist'][$key]['bjname'] = $value['bjname'];
					$list[$index]['stulist'][$key]['count'] = $value['count'];
					foreach ($value['stulist'] as $key2 => $value2) {
						$stuinfo = pdo_fetchall("SELECT realname,mobile,pard,id,uid,is_allowmsg,sid FROM " . tablename($this->table_user) . " WHERE weid = :weid AND schoolid = :schoolid AND sid = :sid ", array(
							':weid' => $weid,
							':schoolid' => $schoolid,
							':sid' => $value2['sid']
						));
						$s_name = pdo_fetch("SELECT s_name FROM " . tablename($this->table_students) . " WHERE weid = :weid AND schoolid = :schoolid AND id = :id ", array(
							':weid' => $weid,
							':schoolid' => $schoolid,
							':id' => $value2['sid']
						))['s_name'];
						foreach ($stuinfo as $key3 => $value3) {
							$member = pdo_fetch("SELECT avatar FROM " . tablename('mc_members') . " where uniacid = :uniacid And uid = :uid ", array(':uniacid' => $weid, ':uid' => $value3['uid']));
							$list[$index]['stulist'][$key]['sid'][$key2]['avatar'] = $member['avatar'];
							$list[$index]['stulist'][$key]['sid'][$key2]['s_name'] = $s_name;
							$list[$index]['stulist'][$key]['sid'][$key2]['realname'] = $value3['realname'];
							$list[$index]['stulist'][$key]['sid'][$key2]['mobile'] = $value3['mobile'];
							$list[$index]['stulist'][$key]['sid'][$key2]['pard'] = $value3['pard'];
							$list[$index]['stulist'][$key]['sid'][$key2]['id'] = $value3['id'];
							$list[$index]['stulist'][$key]['sid'][$key2]['is_allowmsg'] = $value3['is_allowmsg'];
							$list[$index]['stulist'][$key]['sid'][$key2]['sid'] = $value3['sid'];
							$list[$index]['stulist'][$key]['sid'][$key2]['uid'] = $value3['uid'];
						}
					}
				}
				$list[$index]['rs'] = $stulist['kcallstu'];
			}
		}
		include $this->template('' . $school['style3'] . '/tongxunlupx');
	} else {
		//所有校长组
		$master = pdo_fetchall("SELECT tname,thumb,mobile,id,status,userid,fz_id FROM " . tablename($this->table_teachers) . " WHERE weid = :weid AND schoolid = :schoolid AND status = :status AND is_show = :is_show ORDER BY sort DESC", array(
			':weid' => $weid,
			':schoolid' => $schoolid,
			':status' => 2,
			':is_show' => 0,
		));
		foreach ($master as $key => $value) {
			$master[$key]['Ttitle'] = GetTeacherTitle($value['status'], $value['fz_id']);
		}
		$masterCount = count($master);
		//所有年级主任组
		$master1 = pdo_fetchall("SELECT sid,sname,tid FROM " . tablename($this->table_classify) . " WHERE weid = :weid AND schoolid = :schoolid AND type = :type ORDER BY sid DESC", array(
			':weid' => $weid,
			':schoolid' => $schoolid,
			':type' => 'semester',
		));
		$tidss = array();
		$masterCount1 = 0;
		foreach ($master1 as $key => $row) {
			$techer = pdo_fetch("SELECT tname,thumb,mobile,id,status,userid,fz_id,is_show FROM " . tablename($this->table_teachers) . " WHERE id =  :id ", array(':id' => $row['tid']));
			if ($techer['is_show'] == 0) {
				//计算去重
				if (!in_array($row['tid'], $tidss)) {
					$tidss = array($row['tid']);
					$masterCount1++;
				}

				$master1[$key]['tname'] = $techer['tname'];
				$master1[$key]['thumb'] = $techer['thumb'];
				$master1[$key]['mobile'] = $techer['mobile'];
				$master1[$key]['id'] = $techer['id'];
				$master1[$key]['status'] = $techer['status'];
				$master1[$key]['userid'] = $techer['userid'];
				$master1[$key]['fz_id'] = $techer['fz_id'];
				$master1[$key]['Ttitle'] = GetTeacherTitle($techer['status'], $techer['fz_id']);
			}else{
				unset($master1[$key]);
			}
		}
		//所有普通教员			
		$master2 = pdo_fetchall("SELECT tname,thumb,mobile,id,status,userid,fz_id FROM " . tablename($this->table_teachers) . " WHERE weid = :weid AND schoolid = :schoolid AND status = :status AND is_show = :is_show ORDER BY sort DESC", array(
			':weid' => $weid,
			':schoolid' => $schoolid,
			':status' => 1,
			':is_show' => 0,
		));
		foreach ($master2 as $key => $value) {
			$master2[$key]['Ttitle'] = GetTeacherTitle($value['status'], $value['fz_id']);
		}
		$masterCount2 = count($master2);

		//培训机构
		if ($schooltype) {
			$tid = $it['tid'];
			$time = time();
			//当前身份 普通教员 获取授课班级家长
			if ($techers['status'] == 1 || $techers['status'] == 0) {
				$bjlists = pdo_fetchall("select id as bjsid ,name as bjname, end FROM " . tablename($this->table_tcourse) . " WHERE schoolid = '{$schoolid}'  and (tid like '%,{$tid},%'  or tid like '%,{$tid}' or tid like '{$tid},%' or tid ='{$tid}') ORDER BY end DESC , ssort DESC ");
				foreach ($bjlists as $index => $row) {
					if ($row['end'] < $time) {
						$bjlists[$index]['bjname'] .= '--(已结课)';
					}
					$stu_kc = pdo_fetchall("SELECT distinct sid FROM " . tablename($this->table_order) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND kcid = '{$row['bjsid']}' and type = 1 and status = 2 and sid != 0 ");
					$bjlists[$index]['sid'] = pdo_fetchall("SELECT students.id,students.s_name,students.icon FROM " . tablename('wx_school_students') . " as students ," . tablename('wx_school_order') . " as orders  WHERE orders.schoolid = '{$schoolid}' And orders.kcid = '{$row['bjsid']}' and orders.type = 1 and  orders.status = 2 and students.id = orders.sid GROUP BY students.id ORDER BY students.id  ASC ");
					$bjlists[$index]['rs'] = 0;
					foreach ($bjlists[$index]['sid'] as $k => $r) {
						$bjlists[$index]['sid'][$k]['sid'] = pdo_fetchall("SELECT realname,mobile,pard,id,uid FROM " . tablename($this->table_user) . " WHERE weid = :weid AND schoolid = :schoolid AND sid = :sid ", array(
							':weid' => $weid,
							':schoolid' => $schoolid,
							':sid' => $r['id']
						));
						foreach ($bjlists[$index]['sid'][$k]['sid'] as $key => $row) {
							$member = pdo_fetch("SELECT avatar FROM " . tablename('mc_members') . " where uniacid = :uniacid And uid = :uid ", array(':uniacid' => $weid, ':uid' => $row['uid']));
							$bjlists[$index]['sid'][$k]['sid'][$key]['avatar'] = $member['avatar'];
							$bjlists[$index]['rs']++;
						}
					}
				}
			}

			//取全校班级的家长   校长
			if ($techers['status'] == 2) {
				$list =  pdo_fetchall("select id as sid ,name as sname, end FROM " . tablename($this->table_tcourse) . " WHERE schoolid = '{$schoolid}'  ORDER BY end DESC , ssort DESC ");
				foreach ($list as $index => $row) {
					if ($row['end'] < $time) {
						$list[$index]['sname'] .= '--(已结课)';
					}
					$list[$index]['sid'] = pdo_fetchall("SELECT students.id,students.s_name,students.icon FROM " . tablename('wx_school_students') . " as students ," . tablename('wx_school_order') . " as orders  WHERE orders.schoolid = '{$schoolid}' And orders.kcid = '{$row['sid']}' and orders.type = 1 and  orders.status = 2 and students.id = orders.sid GROUP BY students.id ORDER BY students.id  ASC ");
					$list[$index]['rs'] = 0;
					foreach ($list[$index]['sid'] as $k => $r) {
						$list[$index]['sid'][$k]['sid'] = pdo_fetchall("SELECT realname,mobile,pard,id,uid FROM " . tablename($this->table_user) . " WHERE weid = :weid AND schoolid = :schoolid AND sid = :sid ", array(
							':weid' => $weid,
							':schoolid' => $schoolid,
							':sid' => $r['id']
						));
						foreach ($list[$index]['sid'][$k]['sid'] as $key => $row1) {
							$member = pdo_fetch("SELECT avatar FROM " . tablename('mc_members') . " where uniacid = :uniacid And uid = :uid ", array(':uniacid' => $weid, ':uid' => $row1['uid']));
							$list[$index]['sid'][$k]['sid'][$key]['avatar'] = $member['avatar'];
							$list[$index]['rs']++;
						}
					}
				}
			}

			//年级主任获取学生家长
			if ($techers['status'] == 3) {
				$list = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where schoolid = '{$schoolid}' And tid = '{$tid}' And type = 'semester' ORDER BY ssort DESC");
				foreach ($list as $index => $row) {
					$list[$index]['bj_id'] = pdo_fetchall("SELECT id as sid,name as sname FROM " . tablename($this->table_tcourse) . " where schoolid = '{$schoolid}' And xq_id = '{$row['sid']}' ");
					foreach ($list[$index]['bj_id'] as $kk => $vel) {
						if ($row['end'] < $time) {
							$list[$index]['bj_id'][$kk]['sname'] .= '--(已结课)';
						}
						$list[$index]['bj_id'][$kk]['sd'] =  pdo_fetchall("SELECT students.id,students.s_name,students.icon FROM " . tablename('wx_school_students') . " as students ," . tablename('wx_school_order') . " as orders  WHERE orders.schoolid = '{$schoolid}' And orders.kcid = '{$vel['sid']}' and orders.type = 1 and  orders.status = 2 and students.id = orders.sid GROUP BY students.id ORDER BY students.id  ASC ");
						$bj3count[$vel['sid']] = 0;
						foreach ($list[$index]['bj_id'][$kk]['sd'] as $k => $r) {
							$list[$index]['bj_id'][$kk]['sd'][$k]['sid'] = pdo_fetchall("SELECT realname,mobile,pard,id,uid FROM " . tablename($this->table_user) . " WHERE weid = :weid AND schoolid = :schoolid AND sid = :sid ", array(
								':weid' => $weid,
								':schoolid' => $schoolid,
								':sid' => $r['id']
							));
							foreach ($list[$index]['bj_id'][$kk]['sd'][$k]['sid'] as $key => $i) {
								$member = pdo_fetch("SELECT avatar FROM " . tablename('mc_members') . " where uniacid = :uniacid And uid = :uid ", array(':uniacid' => $weid, ':uid' => $i['uid']));
								$list[$index]['bj_id'][$kk]['sd'][$k]['sid'][$key]['avatar'] = $member['avatar'];
								$bj3count[$vel['sid']]++;
							}
						}
					}
				}
			}
		} else {


			//当前身份 普通教员 获取授课班级家长
			if ($techers['status'] == 1 || $techers['status'] == 0) {
				$bjlists = pdo_fetchall("SELECT * FROM " . tablename($this->table_class) . " WHERE tid = :tid And schoolid = :schoolid ", array(':tid' => $it['tid'], ':schoolid' => $schoolid));

				foreach ($bjlists as $index => $row) {
					if (!in_array($row['bj_id'], $bjlists)) {
						$bjlists[] = $row['bj_id'];
						$bj = pdo_fetch("SELECT sid,sname FROM " . tablename($this->table_classify) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And sid = '{$row['bj_id']}' ");
						$bjlists[$index]['bjsid'] = $bj['sid'];
						$bjlists[$index]['bjname'] = $bj['sname'];
						$bjlists[$index]['sid'] = pdo_fetchall("SELECT id,s_name,icon FROM " . tablename($this->table_students) . " WHERE weid = :weid AND schoolid = :schoolid AND bj_id = :bj_id ", array(
							':weid' => $weid,
							':schoolid' => $schoolid,
							':bj_id' => $row['bj_id']
						));
						$bjlists[$index]['rs'] = 0;
						foreach ($bjlists[$index]['sid'] as $k => $r) {
							$bjlists[$index]['sid'][$k]['sid'] = pdo_fetchall("SELECT realname,mobile,pard,id,uid FROM " . tablename($this->table_user) . " WHERE weid = :weid AND schoolid = :schoolid AND sid = :sid ", array(
								':weid' => $weid,
								':schoolid' => $schoolid,
								':sid' => $r['id']
							));
							foreach ($bjlists[$index]['sid'][$k]['sid'] as $key => $row) {
								$member = pdo_fetch("SELECT avatar FROM " . tablename('mc_members') . " where uniacid = :uniacid And uid = :uid ", array(':uniacid' => $weid, ':uid' => $row['uid']));
								$bjlists[$index]['sid'][$k]['sid'][$key]['avatar'] = $member['avatar'];
								$bjlists[$index]['rs']++;
							}
						}
					}
				}
			}

			//取全校班级的家长   校长
			if ($techers['status'] == 2) {
				$list = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And type = 'theclass'  ORDER BY ssort DESC");
				foreach ($list as $index => $row) {
					$list[$index]['sid'] = pdo_fetchall("SELECT id,s_name,icon FROM " . tablename($this->table_students) . " WHERE weid = :weid AND schoolid = :schoolid AND bj_id = :bj_id ", array(
						':weid' => $weid,
						':schoolid' => $schoolid,
						':bj_id' => $row['sid']
					));
					$list[$index]['rs'] = 0;
					foreach ($list[$index]['sid'] as $k => $r) {
						$list[$index]['sid'][$k]['sid'] = pdo_fetchall("SELECT realname,mobile,pard,id,uid FROM " . tablename($this->table_user) . " WHERE weid = :weid AND schoolid = :schoolid AND sid = :sid ", array(
							':weid' => $weid,
							':schoolid' => $schoolid,
							':sid' => $r['id']
						));
						foreach ($list[$index]['sid'][$k]['sid'] as $key => $row) {
							$member = pdo_fetch("SELECT avatar FROM " . tablename('mc_members') . " where uniacid = :uniacid And uid = :uid ", array(':uniacid' => $weid, ':uid' => $row['uid']));
							$list[$index]['sid'][$k]['sid'][$key]['avatar'] = $member['avatar'];
							$list[$index]['rs']++;
						}
					}
				}
			}

			//年级主任获取学生家长
			if ($techers['status'] == 3) {
				$list = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where schoolid = '{$schoolid}' And tid = '{$techers['id']}' And type = 'semester' ORDER BY ssort DESC");
				foreach ($list as $index => $row) {
					$list[$index]['bj_id'] = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where schoolid = '{$schoolid}' And parentid = '{$row['sid']}' And type = 'theclass' ORDER BY ssort DESC");
					foreach ($list[$index]['bj_id'] as $kk => $vel) {
						$list[$index]['bj_id'][$kk]['sd'] = pdo_fetchall("SELECT id,s_name,icon,bj_id FROM " . tablename($this->table_students) . " WHERE weid = :weid AND schoolid = :schoolid AND bj_id = :bj_id ", array(
							':weid' => $weid,
							':schoolid' => $schoolid,
							':bj_id' => $vel['sid']
						));
						$bj3count[$vel['sid']] = 0;
						foreach ($list[$index]['bj_id'][$kk]['sd'] as $k => $r) {
							$list[$index]['bj_id'][$kk]['sd'][$k]['sid'] = pdo_fetchall("SELECT realname,mobile,pard,id,uid FROM " . tablename($this->table_user) . " WHERE weid = :weid AND schoolid = :schoolid AND sid = :sid ", array(
								':weid' => $weid,
								':schoolid' => $schoolid,
								':sid' => $r['id']
							));
							foreach ($list[$index]['bj_id'][$kk]['sd'][$k]['sid'] as $key => $i) {
								$member = pdo_fetch("SELECT avatar FROM " . tablename('mc_members') . " where uniacid = :uniacid And uid = :uid ", array(':uniacid' => $weid, ':uid' => $i['uid']));
								$list[$index]['bj_id'][$kk]['sd'][$k]['sid'][$key]['avatar'] = $member['avatar'];
								$bj3count[$vel['sid']]++;
							}
						}
					}
				}
			}
		}
		include $this->template('' . $school['style3'] . '/tongxunlu');
	}
} else {
	session_destroy();
	$stopurl = $_W['siteroot'] . 'app/' . $this->createMobileUrl('bangding', array('schoolid' => $schoolid));
	header("location:$stopurl");
}
