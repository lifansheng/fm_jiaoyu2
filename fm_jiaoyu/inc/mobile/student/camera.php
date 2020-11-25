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
$videoid = $_GPC['id'];
$operation = $_GPC['op'] ? $_GPC['op'] : 'display';
$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where id = :id ", array(':id' => $_SESSION['user']));
$isios = get_device_type();
$school = pdo_fetch("SELECT videoname,videopic,style2,title,spic,tpic,headcolor FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
$set = pdo_fetch("SELECT sensitive_word FROM " . tablename($this->table_set) . " where weid = :weid ", array(':weid' => $weid));
$allowpy = 1;
$video_line = empty($_GPC['video_line'])?1:$_GPC['video_line'];
if(!empty($it)){
	$student = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $it['sid']));	
	$mac = get_device_type();
	if($_GPC['op'] == 'mybj'){		
		$mybj = pdo_fetch("SELECT * FROM " . tablename($this->table_classify) . " WHERE weid = :weid And schoolid = :schoolid And sid = :sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':sid' => $student['bj_id']));
		if($mybj['allowpy'] == 2){
			$allowpy = 2;
		}
		$start = $mybj['videostart'];
		$end = $mybj['videoend'];

		$is_ontime = 0 ;
		if (date('H:i',TIMESTAMP) > $start && $end > date('H:i',TIMESTAMP)){
			$is_ontime = 1;
			} 
		$pic = $school['videopic'];
		if($mac != 'ios'){
			$thisvideo = $mybj['video'];
			if (preg_match('/lechange/i', $mybj['video'])) {
				$thisvideo = $mybj['video'].'?v='.getRandomString(32);
			}
		}else{
			$thisvideo = $mybj['video'];
		}
		$myplsl  = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_camerapl) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND bj_id = '{$student['bj_id']}' And type = 2");
		$mydzsl  = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_camerapl) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND bj_id = '{$student['bj_id']}' And type = 1");
		$myisdz = pdo_fetch("SELECT id FROM " . tablename($this->table_camerapl) . " where weid = :weid AND schoolid = :schoolid AND bj_id = :bj_id AND userid = :userid", array(':weid' => $weid, ':schoolid' => $schoolid, ':bj_id' => $student['bj_id'], ':userid' => $it['id']));
		$name = $mybj['sname'];
		$thisclick = $mybj['videoclick'];
		$click = $mybj['videoclick'] + 1;
		pdo_update($this->table_classify, array('videoclick' =>  $click), array('sid' =>  $student['bj_id']));
		$allpl = pdo_fetchall("SELECT * FROM " . tablename($this->table_camerapl) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND bj_id = '{$student['bj_id']}' AND type = 2 ORDER BY createtime DESC");
		$obid = 3;
		$this->checkobjiect($schoolid, $student['id'], $obid);				
	}else{
		if($_W['schooltype']){
			$mybj = pdo_fetch("SELECT * FROM " . tablename($this->table_allcamera) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND id = '{$videoid}'");
			$is_ontime = 0 ;

			if($mybj['videotype'] != 2){
				$kcidarr = explode(',',$mybj['kcidstr']);
				$nowtime = time();
				$endtime = strtotime(date('Y-m-d')) + 86399;
				$mintime = strtotime(date('Y-m-d'));
				$allks = pdo_fetchall("SELECT c.sd_end,kc.kcid,kc.addr_id,kc.date FROM " . GetTableName('kcbiao') . " as kc LEFT JOIN " . GetTableName('classify') . " as c ON c.sid = kc.sd_id WHERE kc.schoolid ='{$schoolid}' AND FIND_IN_SET(kc.kcid,'{$mybj['kcidstr']}') And (kc.date BETWEEN {$mintime} and {$endtime}) ORDER BY kc.date ASC");
				$nowkcid = [];
				$addr_id = '';
				foreach ($allks as $key => $value) {
					$hours = strtotime(date("H:i:s",$value['sd_end']));
					$signTime = pdo_fetch("SELECT signTime FROM ".GetTableName('tcourse')." WHERE id = '{$value['kcid']}' AND schoolid = '{$schoolid}' ")['signTime'];
					$jstime = time() + $signTime*60; //开始计算时间
					if($hours > $nowtime && $value['date'] <= $jstime){
						$nowkcid[]['kcid'] = $value['kcid'];
						$nowkcid[]['addr_id'] = $value['addr_id'];
						
					}
				}
				$isonline = false;
				$addr_id = trim($addr_id,',');
				foreach ($nowkcid as $value) {
					if(in_array($value['kcid'],$kcidarr)){
						$isonline = true;
					}
					$video = pdo_fetch("SELECT video FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' AND sid = '{$value['addr_id']}' ")['video']; //推流地址
				}
			}
			
			if($mybj['is_pay'] == 1){
				$allow_sk = false;
				mload()->model('vod');
				$allow_look = check_vod_pay($schoolid, $it['sid'], $videoid, $it['id']);
				if($mybj['is_try'] == 1 && $mac == 'ios'){
					if(check_have_try($schoolid,$videoid,$it['id'])){
						$allow_sk = true;//检测是否有试看权限，排除安卓设备
					}
				}
			}else{
				$obid = 3;
				$this->checkobjiect($schoolid, $student['id'], $obid);						
			}		
			if($mybj['allowpy'] == 2){
				$allowpy = 2;
			}
					
			$pic = $mybj['videopic'];
			if($mac != 'ios'){
				if($mybj['videotype'] != 2 ){
					$thisvideo = $video;
				}else{
					$thisvideo = $mybj['videourl'];
				}
				if (preg_match('/lechange/i', $thisvideo)) {
					$thisvideo = $thisvideo.'?v='.getRandomString(32);
				}					
			}else{
				if($mybj['videotype'] != 2 ){
					$thisvideo = $video;
				}else{
					$thisvideo = $mybj['videourl'];
				}
				// $thisvideo = $video;
			}	
			$start1    = $mybj['starttime1'];
			$end1      = $mybj['endtime1'];
			$start2    = $mybj['starttime2'];
			$end2      = $mybj['endtime2'];
			$start3    = $mybj['starttime3'];
			$end3      = $mybj['endtime3'];
			if ( $start1 != -1 && date('H:i',TIMESTAMP) > $start1 && $end1 > date('H:i',TIMESTAMP)){
				$is_ontime = 1;
			} 
			if ( $start2 != -1 && date('H:i',TIMESTAMP) > $start2 && $end2 > date('H:i',TIMESTAMP)){
				$is_ontime = 1;
			}
			if ( $start3 != -1 && date('H:i',TIMESTAMP) > $start3 && $end3 > date('H:i',TIMESTAMP)){
				$is_ontime = 1;
			}
			if(!empty($mybj['kcidstr'])){
				$is_ontime = 1;
			}	
			$myplsl  = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_camerapl) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND carmeraid = '{$videoid}' And type = 2");
			$mydzsl  = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_camerapl) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND carmeraid = '{$videoid}' And type = 1");
			$myisdz = pdo_fetch("SELECT id FROM " . tablename($this->table_camerapl) . " where weid = :weid AND schoolid = :schoolid AND carmeraid = :carmeraid AND userid = :userid", array(':weid' => $weid, ':schoolid' => $schoolid, ':carmeraid' => $videoid, ':userid' => $it['id']));
			$name = $mybj['name'];
			$thisclick = $mybj['click'];
			$click = $mybj['click'] + 1;
			pdo_update($this->table_allcamera, array('click' =>  $click), array('id' =>  $videoid));
			$allpl = pdo_fetchall("SELECT * FROM " . tablename($this->table_camerapl) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND carmeraid = '{$videoid}' AND type = 2 ORDER BY createtime DESC LIMIT 0,10");
		}else{
			$mybj = pdo_fetch("SELECT * FROM " . tablename($this->table_allcamera) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND id = '{$videoid}'");
			mload()->model('vod');
			if($mybj['is_pay'] == 1){
				$allow_sk = false;
				$allow_look = check_vod_pay($schoolid, $it['sid'], $videoid, $it['id']);
				if($mybj['is_try'] == 1 && $mac == 'ios'){
					if(check_have_try($schoolid,$videoid,$it['id'])){
						$allow_sk = true;//检测是否有试看权限，排除安卓设备
					}
				}
			}else{
				$obid = 3;
				$this->checkobjiect($schoolid, $student['id'], $obid);						
			}		
			if($mybj['allowpy'] == 2){
				$allowpy = 2;
			}
			$start1    = $mybj['starttime1'];
			$end1      = $mybj['endtime1'];
			$start2    = $mybj['starttime2'];
			$end2      = $mybj['endtime2'];
			$start3    = $mybj['starttime3'];
			$end3      = $mybj['endtime3'];
			$is_ontime = 0 ;
			if ( $start1 != -1 && date('H:i',TIMESTAMP) > $start1 && $end1 > date('H:i',TIMESTAMP)){
				$is_ontime = 1;
			} 
			if ( $start2 != -1 && date('H:i',TIMESTAMP) > $start2 && $end2 > date('H:i',TIMESTAMP)){
				$is_ontime = 1;
			}
			if ( $start3 != -1 && date('H:i',TIMESTAMP) > $start3 && $end3 > date('H:i',TIMESTAMP)){
				$is_ontime = 1;
			}
			$isholiday = check_holiday($student['bj_id'],time());
			if($isholiday == true && $is_ontime == 1){
				$is_ontime = 1;
			}else{
				$is_ontime = 0;
			}
			if(keep_xdtx()){
				if($it['is_allow_video'] == 1){
					$is_ontime = 0;
				}
			}
			$pic = $mybj['videopic'];
			if($mac != 'ios'){
				$thisvideo = $mybj['videourl'];
				if (preg_match('/lechange/i', $mybj['videourl'])) {
					$thisvideo = $mybj['videourl'].'?v='.getRandomString(32);
				}					
			}else{
				$thisvideo = $mybj['videourl'];
			}				
			$myplsl  = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_camerapl) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND carmeraid = '{$videoid}' And type = 2");
			$mydzsl  = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_camerapl) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND carmeraid = '{$videoid}' And type = 1");
			$myisdz = pdo_fetch("SELECT id FROM " . tablename($this->table_camerapl) . " where weid = :weid AND schoolid = :schoolid AND carmeraid = :carmeraid AND userid = :userid", array(':weid' => $weid, ':schoolid' => $schoolid, ':carmeraid' => $videoid, ':userid' => $it['id']));
			$name = $mybj['name'];
			$thisclick = $mybj['click'];
			$click = $mybj['click'] + 1;
			pdo_update($this->table_allcamera, array('click' =>  $click), array('id' =>  $videoid));
			$allpl = pdo_fetchall("SELECT * FROM " . tablename($this->table_camerapl) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND carmeraid = '{$videoid}' AND type = 2 ORDER BY createtime DESC LIMIT 0,10");
		}
		$is_live = true;
		if (strstr($thisvideo,'.mp4')) {
			$is_live = false;
		}
		$videotype = 'mp4';
		if (strstr($thisvideo,'.m3u8')) {
			$videotype = 'hls';
		}
		if (strstr($thisvideo,'.mpd')) {
			$videotype = 'dash';
		}
		if (strstr($thisvideo,'.flv')) {
			$videotype = 'flv';
		}
		if (strstr($thisvideo,'magnet:')) {
			$videotype = 'webtorrent';
		}	
	}
	foreach($allpl as $key => $row){
		$user = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where id = :id ", array(':id' => $row['userid']));
		if(empty($user)){
				unset($allpl[$key]);
				$myplsl--;
		}else{
			$allpl[$key]['time'] = sub_day($row['createtime']);
			if($user['pard'] == 0){
				$teacher = pdo_fetch("SELECT tname,thumb FROM " . tablename($this->table_teachers) . " where id = :id ", array(':id' => $user['tid']));
				$allpl[$key]['name'] = $teacher['tname']."老师";
				$allpl[$key]['icon'] = !empty($teacher['thumb']) ? $teacher['thumb'] : $school['tpic'];
			}else{
				$studen = pdo_fetch("SELECT s_name,icon FROM " . tablename($this->table_students) . " where id = :id ", array(':id' => $user['sid']));
				if($user['pard'] == 5){	
					$allpl[$key]['name'] = $studen['s_name'];
					$allpl[$key]['icon'] = !empty($studen['icon']) ? $studen['icon'] : $school['spic'];
				}else{
					$fansinfo = GetWeFans($weid,$user['openid']);
					$allpl[$key]['icon'] = $fansinfo['avatar'];
					if($user['pard'] == 2){
						$allpl[$key]['name'] = $studen['s_name']."妈妈";
					}
					if($user['pard'] == 3){
						$allpl[$key]['name'] = $studen['s_name']."爸爸";
					}
					if($user['pard'] == 4){
						$allpl[$key]['name'] = $studen['s_name']."家长";
					}						
				}
			}
		}
	}
	if($it['pard'] == 0){
		$my = pdo_fetch("SELECT thumb FROM " . tablename($this->table_teachers) . " where id = :id ", array(':id' => $it['tid']));
		$myicon = empty($my['thumb']) ? $school['tpic'] : $my['thumb'];
	}else{
		if($it['pard'] == 4){
			$my = pdo_fetch("SELECT icon FROM " . tablename($this->table_students) . " where id = :id ", array(':id' => $it['sid']));
			$myicon = empty($my['icon']) ? $school['spic'] : $my['icon'];
		}else{
			$fansinfo = GetWeFans($weid,$it['openid']);
			$myicon = $fansinfo['avatar'];
		}
	}
	if (!empty($_W['setting']['remote']['type'])) { 
		$urls = $_W['attachurl']; 
	} else {
		$urls = $_W['siteroot'].'attachment/';
	}	
	if($operation == 'scroll_more'){
		$time = $_GPC['LiData']['time'];
		$limit_start = $time + 1;
		$allpl = pdo_fetchall("SELECT * FROM " . tablename($this->table_camerapl) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND carmeraid = '{$_GPC['id']}' AND type = 2 ORDER BY createtime DESC LIMIT {$limit_start},10");
		foreach($allpl as $key => $row){
			$user = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where id = :id ", array(':id' => $row['userid']));
			if(empty($user)){
				unset($allpl[$key]);
				$myplsl--;
			}else{
				$allpl[$key]['time'] = sub_day($row['createtime']);
				if($user['pard'] == 0){
					$teacher = pdo_fetch("SELECT tname,thumb FROM " . tablename($this->table_teachers) . " where id = :id ", array(':id' => $user['tid']));
					$allpl[$key]['name'] = $teacher['tname']."老师";
					$allpl[$key]['icon'] = !empty($teacher['thumb']) ? $teacher['thumb'] : $school['tpic'];
				}else{
					$studen = pdo_fetch("SELECT s_name,icon FROM " . tablename($this->table_students) . " where id = :id ", array(':id' => $user['sid']));
					if($user['pard'] == 5){	
						$allpl[$key]['name'] = $studen['s_name'];
						$allpl[$key]['icon'] = !empty($studen['icon']) ? $studen['icon'] : $school['spic'];
					}else{
						$fansinfo = GetWeFans($weid,$user['openid']);
						$allpl[$key]['icon'] = $fansinfo['avatar'];
						if($user['pard'] == 2){
							$allpl[$key]['name'] = $studen['s_name']."妈妈";
						}
						if($user['pard'] == 3){
							$allpl[$key]['name'] = $studen['s_name']."爸爸";
						}
						if($user['pard'] == 4){
							$allpl[$key]['name'] = $studen['s_name']."家长";
						}						
					}
				}
				$allpl[$key]['location'] = $key + $limit_start;
			}
		}
		include $this->template('comtool/camera');
		exit;
	}
	include $this->template(''.$school['style2'].'/camera');
}else{
	session_destroy();
	$stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
	header("location:$stopurl");
	exit;
}        
?>