<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_W, $_GPC;
$weid = $this->weid;
$from_user = $this->_fromuser;
$schoolid = intval($_GPC['schoolid']);
$openid = $_W['openid'];
$fzstr = GetFzByQx(2001001,2,$schoolid);
$fzarr = explode(',',$fzstr);
//查询是否用户登录		
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');$usertemp = 'http://wmeiapi-session.stor.sinaapp.com';$MODOLE_URL = $usertemp;
$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ORDER BY id DESC", array(':weid' => $weid, ':id' => $userid['id']));	
$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ORDER BY ssort DESC", array(':weid' => $weid, ':id' => $schoolid));
$tid_global = $it['tid'];
if(!empty($userid['id'])){
	if($operation == 'display'){
		if(is_showgkk()) {
			$isxz = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $_W ['uniacid'], ':id' => $it['tid']));
			$is_njzr = IsHasQx($it['tid'],'shjsqj',2,$schoolid);
			
			$leave = pdo_fetchall("SELECT * FROM " . tablename($this->table_leave) . " where :schoolid = schoolid And :weid = weid And :sid = sid And ((status = 0 And :njzrtid = tonjzrtid) or ( status != 0 )) And tid <> 0  ORDER BY createtime DESC LIMIT 0,4", array( ':weid' => $weid, ':schoolid' => $schoolid, ':sid' => 0, ':njzrtid'=> 0 ));
			if($is_njzr ) {
				$xzlist = pdo_fetchall("SELECT tname,openid,id FROM " . tablename($this->table_teachers) . " where weid = :weid AND schoolid=:schoolid AND status=:status", array(':weid' => $weid, ':schoolid' => $schoolid, ':status' => 2)); 
				if($isxz['status'] != '2') {
					$leave = pdo_fetchall("SELECT * FROM " . tablename($this->table_leave) . " where :schoolid = schoolid And :weid = weid And :sid = sid And tid <> 0  and tonjzrtid = :tid ORDER BY createtime DESC LIMIT 0,4", array(
						':weid' => $weid,
						':schoolid' => $schoolid,
						':sid' => 0,
						':tid' => $isxz['id'],
						));
				}elseif($isxz['status'] == '2'){
					$leave = pdo_fetchall("SELECT * FROM " . tablename($this->table_leave) . " where :schoolid = schoolid And :weid = weid And :sid = sid And tid <> 0   ORDER BY createtime DESC LIMIT 0,4", array( ':weid' => $weid, ':schoolid' => $schoolid, ':sid' => 0, ));
				}
			}
			foreach($leave as $key => $row){
				$teacher = pdo_fetch("SELECT thumb,tname FROM " . tablename($this->table_teachers) . " where schoolid = :schoolid And id = :id ", array(':schoolid' => $schoolid,':id' => $row['tid']));
				$teacher_cl = pdo_fetch("SELECT thumb,tname FROM " . tablename($this->table_teachers) . " where schoolid = :schoolid And id = :id ", array(':schoolid' => $schoolid,':id' => $row['cltid']));
				$member = pdo_fetch("SELECT * FROM " . tablename ( 'mc_members' ) . " where uniacid = :uniacid And uid = :uid ", array(':uniacid' => $_W ['uniacid'],':uid' => $row['uid']));
				if(!empty($teacher['thumb'])){
					$leave[$key]['clicon'] = $teacher_cl['thumb'];
				}else{
					$leave[$key]['clicon'] = $member['avatar'];
				}
				$leave[$key]['tname'] = $teacher['tname'];
				$leave[$key]['cltname'] = $teacher_cl['tname'];
				$teacher_njzr = pdo_fetch("SELECT thumb,tname FROM " . tablename($this->table_teachers) . " where schoolid = :schoolid And id = :id ", array(':schoolid' => $schoolid,':id' => $row['tonjzrtid']));
				$leave[$key]['njzrtname'] = $teacher_njzr['tname'];
					if(!empty($teacher['thumb'])){
					$leave[$key]['njzricon'] = $teacher_njzr['thumb'];
				}else{
					$leave[$key]['njzricon'] = $member['avatar'];
				}
			}		
			include $this->template(''.$school['style3'].'/tmssage');
		}else{
			$beginThismonth=mktime(0,0,0,date('m'),1,date('Y'));
			$endThismonth=mktime(23,59,59,date('m'),date('t'),date('Y'));
			$now_week_th = date("w",time());
			if($now_week_th == 0){
				$now_week_th = 6;
			}else{
				$now_week_th = $now_week_th -1 ;
			}
			$beginThisweek =  strtotime(date('Y-m-d', time()))-$now_week_th*86400;
			$endThisweek = $beginThisweek + 7*86400 - 1 ;
			$beginTihstoday = strtotime(date("Y-m-d",time()));
			$endThistoday = $beginTihstoday + 86399;
			$isxz = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $_W ['uniacid'], ':id' => $it['tid']));
			$this_condition = ' ';
			if($isxz['status'] != 2){
				$this_condition = " and cltid = '{$it['tid']}' ";
			}
			$leave = pdo_fetchall("SELECT * FROM " . tablename($this->table_leave) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And sid = 0 And tid <> 0 {$this_condition} ORDER BY createtime DESC LIMIT 0,9");
			$leave_today = pdo_fetchcolumn("SELECT count(1) FROM " . tablename($this->table_leave) . " where schoolid = '{$schoolid}' And weid ='{$weid}' And sid = 0 And tid <> 0 and status = 1  and createtime>='{$beginTihstoday}' and createtime <= '{$endThistoday}'  {$this_condition} ORDER BY createtime DESC");
			$leave_week = pdo_fetchcolumn("SELECT count(1) FROM " . tablename($this->table_leave) . " where schoolid = '{$schoolid}' And weid ='{$weid}' And sid = 0 And tid <> 0 and status = 1  and createtime>='{$beginThisweek}' and createtime <= '{$endThisweek}'  {$this_condition} ORDER BY createtime DESC");
			$leave_month = pdo_fetchcolumn("SELECT count(1) FROM " . tablename($this->table_leave) . " where schoolid = '{$schoolid}' And weid ='{$weid}' And sid = 0 And tid <> 0 and status = 1  and createtime>='{$beginThismonth}' and createtime <= '{$endThismonth}'  {$this_condition} ORDER BY createtime DESC");
			foreach($leave as $key => $row){
				$teacher = pdo_fetch("SELECT thumb,tname FROM " . tablename($this->table_teachers) . " where schoolid = :schoolid And id = :id ", array(':schoolid' => $schoolid,':id' => $row['tid']));
				$member = pdo_fetch("SELECT * FROM " . tablename ( 'mc_members' ) . " where uniacid = :uniacid And uid = :uid ", array(':uniacid' => $_W ['uniacid'],':uid' => $row['uid']));
				if(!empty($teacher['thumb'])){
					$leave[$key]['icon'] = $teacher['thumb'];
				}else{
					$leave[$key]['icon'] = $member['avatar'];
				}
				$leave[$key]['tname'] = $teacher['tname'];
			}		
			include $this->template(''.$school['style3'].'/tmssage');
		}
	}elseif($operation == 'scroll_more'){
		$limit = $_GPC['limit'];
		$Ctype = $_GPC['ctype'] ;
		$page_start = $limit + 1 ;
		if(is_showgkk()) {
			$isxz = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $_W ['uniacid'], ':id' => $it['tid']));
			$is_njzr = IsHasQx($it['tid'],'shjsqj',2,$schoolid);
			$leave = pdo_fetchall("SELECT * FROM " . tablename($this->table_leave) . " where :schoolid = schoolid And :weid = weid And :sid = sid And ((status = 0 And :njzrtid = tonjzrtid) or ( status != 0 )) And tid <> 0  ORDER BY createtime DESC LIMIT ".$page_start.",4", array( ':weid' => $weid, ':schoolid' => $schoolid, ':sid' => 0, ':njzrtid'=> 0 ));
			if($is_njzr ) {
				$xzlist = pdo_fetchall("SELECT tname,openid,id FROM " . tablename($this->table_teachers) . " where weid = :weid AND schoolid=:schoolid AND status=:status", array(':weid' => $weid, ':schoolid' => $schoolid, ':status' => 2)); 
				if($isxz['status'] != '2') {
					$leave = pdo_fetchall("SELECT * FROM " . tablename($this->table_leave) . " where :schoolid = schoolid And :weid = weid And :sid = sid And tid <> 0  and tonjzrtid = :tid ORDER BY createtime DESC LIMIT ".$page_start.",4", array(':weid' => $weid, ':schoolid' => $schoolid, ':sid' => 0, ':tid' => $isxz['id']));
				}elseif($isxz['status'] == '2'){
					$leave = pdo_fetchall("SELECT * FROM " . tablename($this->table_leave) . " where :schoolid = schoolid And :weid = weid And :sid = sid And tid <> 0   ORDER BY createtime DESC LIMIT ".$page_start.",4", array( ':weid' => $weid, ':schoolid' => $schoolid, ':sid' => 0));
				}
			}
			foreach($leave as $key => $row){
				$teacher = pdo_fetch("SELECT thumb,tname FROM " . tablename($this->table_teachers) . " where schoolid = :schoolid And id = :id ", array(':schoolid' => $schoolid,':id' => $row['tid']));
				$teacher_cl = pdo_fetch("SELECT thumb,tname FROM " . tablename($this->table_teachers) . " where schoolid = :schoolid And id = :id ", array(':schoolid' => $schoolid,':id' => $row['cltid']));
				$member = pdo_fetch("SELECT * FROM " . tablename ( 'mc_members' ) . " where uniacid = :uniacid And uid = :uid ", array(':uniacid' => $_W ['uniacid'],':uid' => $row['uid']));
				if(!empty($teacher['thumb'])){
					$leave[$key]['clicon'] = $teacher_cl['thumb'];
				}else{
					$leave[$key]['clicon'] = $member['avatar'];
				}
				$leave[$key]['tname'] = $teacher['tname'];
				$leave[$key]['cltname'] = $teacher_cl['tname'];
				$teacher_njzr = pdo_fetch("SELECT thumb,tname FROM " . tablename($this->table_teachers) . " where schoolid = :schoolid And id = :id ", array(':schoolid' => $schoolid,':id' => $row['tonjzrtid']));
				$leave[$key]['njzrtname'] = $teacher_njzr['tname'];
				if(!empty($teacher['thumb'])){
					$leave[$key]['njzricon'] = $teacher_njzr['thumb'];
				}else{
					$leave[$key]['njzricon'] = $member['avatar'];
				}
				$leave[$key]['localtion'] = $page_start + $key;
			}		
		}else{
			$beginThismonth=mktime(0,0,0,date('m'),1,date('Y'));
			$endThismonth=mktime(23,59,59,date('m'),date('t'),date('Y'));
			$now_week_th = date("w",time());
			if($now_week_th == 0){
				$now_week_th = 6;
			}else{
				$now_week_th = $now_week_th -1 ;
			}
			$beginThisweek =  strtotime(date('Y-m-d', time()))-$now_week_th*86400;
			$endThisweek = $beginThisweek + 7*86400 - 1 ;
			$beginTihstoday = strtotime(date("Y-m-d",time()));
			$endThistoday = $beginTihstoday + 86399;
			$isxz = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $_W ['uniacid'], ':id' => $it['tid']));
			$this_condition = ' ';
			if($isxz['status'] != 2){
				$this_condition = " and cltid = '{$it['tid']}' ";
			}
			$leave = pdo_fetchall("SELECT * FROM " . tablename($this->table_leave) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And sid = 0 And tid <> 0 {$this_condition} ORDER BY createtime DESC LIMIT ".$page_start.",20");
			$leave_today = pdo_fetchcolumn("SELECT count(1) FROM " . tablename($this->table_leave) . " where schoolid = '{$schoolid}' And weid ='{$weid}' And sid = 0 And tid <> 0 and status = 1  and createtime>='{$beginTihstoday}' and createtime <= '{$endThistoday}'  {$this_condition} ORDER BY createtime DESC");
			$leave_week = pdo_fetchcolumn("SELECT count(1) FROM " . tablename($this->table_leave) . " where schoolid = '{$schoolid}' And weid ='{$weid}' And sid = 0 And tid <> 0 and status = 1  and createtime>='{$beginThisweek}' and createtime <= '{$endThisweek}'  {$this_condition} ORDER BY createtime DESC");
			$leave_month = pdo_fetchcolumn("SELECT count(1) FROM " . tablename($this->table_leave) . " where schoolid = '{$schoolid}' And weid ='{$weid}' And sid = 0 And tid <> 0 and status = 1  and createtime>='{$beginThismonth}' and createtime <= '{$endThismonth}'  {$this_condition} ORDER BY createtime DESC");
			foreach($leave as $key => $row){
				$teacher = pdo_fetch("SELECT thumb,tname FROM " . tablename($this->table_teachers) . " where schoolid = :schoolid And id = :id ", array(':schoolid' => $schoolid,':id' => $row['tid']));
				$member = pdo_fetch("SELECT * FROM " . tablename ( 'mc_members' ) . " where uniacid = :uniacid And uid = :uid ", array(':uniacid' => $_W ['uniacid'],':uid' => $row['uid']));
				if(!empty($teacher['thumb'])){
					$leave[$key]['icon'] = $teacher['thumb'];
				}else{
					$leave[$key]['icon'] = $member['avatar'];
				}
				$leave[$key]['tname'] = $teacher['tname'];
				$leave[$key]['localtion'] = $page_start + $key;
			}	
		}		
		include $this->template('comtool/tmssage');
	}
}else{
	session_destroy();
	$stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
	header("location:$stopurl");
}        
?>