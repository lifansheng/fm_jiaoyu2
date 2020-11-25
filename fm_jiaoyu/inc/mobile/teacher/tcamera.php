<?php
/*
 * @Discription:  
 * @Author: Hannibal·Lee
 * @Date: 2018-12-14 19:49:07
 * @LastEditTime: 2020-02-17 15:42:53
 */
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
$ksid = $_GPC['ksid'];
$bj_id = $_GPC['bj_id'];
$operation = $_GPC['op'] ? $_GPC['op'] : 'display';
$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :openid = openid And :sid = sid", array(':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0));

$school = pdo_fetch("SELECT videoname,videopic,style3,title,spic,tpic FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
$set = pdo_fetch("SELECT sensitive_word FROM " . tablename($this->table_set) . " where weid = :weid ", array(':weid' => $weid));
$allowpy = 1;		
if(!empty($it)){
	//获取关于课程的信息
	if(!empty($ksid)){
		$mac = get_device_type();
		$mybj =  pdo_fetch("SELECT * FROM " . GetTableName('kcbiao') . " where id='{$ksid}'");	
		$myplsl  = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_camerapl) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND ksid = '{$ksid}' And type = 2");
		$mydzsl  = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_camerapl) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND ksid = '{$ksid}' And type = 1");
		$myisdz = pdo_fetch("SELECT id FROM " . tablename($this->table_camerapl) . " where weid = :weid AND schoolid = :schoolid AND ksid = :ksid AND userid = :userid", array(':weid' => $weid, ':schoolid' => $schoolid, ':ksid' => $ksid, ':userid' => $it['id']));
		$name = $mybj['name'];
		$pic = $kc['thumb'];
		if($mac != 'ios'){
			$thisvideo = $mybj['content'];
			if (preg_match('/lechange/i', $mybj['content'])) {
				$thisvideo = $mybj['content'].'?v='.getRandomString(32);
			}					
		}else{
			$thisvideo = $mybj['content'];
		}
		$thisclick = $mybj['clicks'];
		$click = $mybj['clicks'] + 1;
		pdo_update($this->table_kcbiao, array('clicks' =>  $click), array('id' =>  $ksid));
		$allpl = pdo_fetchall("SELECT * FROM " . tablename($this->table_camerapl) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND ksid = '{$ksid}' AND type = 2 ORDER BY createtime DESC LIMIT 0,10");
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
		$my = pdo_fetch("SELECT thumb FROM " . tablename($this->table_teachers) . " where id = :id ", array(':id' => $it['tid']));
		$myicon = empty($my['thumb']) ? $school['tpic'] : $my['thumb'];
		if (!empty($_W['setting']['remote']['type'])) { 
			$urls = $_W['attachurl']; 
		} else {
			$urls = $_W['siteroot'].'attachment/';
		}	
	}
	//获取视频信息
	if(!empty($videoid)){
		$mac = get_device_type();
		$mybj = pdo_fetch("SELECT * FROM " . tablename($this->table_allcamera) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND id = '{$videoid}'");			
		$myplsl  = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_camerapl) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND carmeraid = '{$videoid}' And type = 2");
		$mydzsl  = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_camerapl) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND carmeraid = '{$videoid}' And type = 1");
		$myisdz = pdo_fetch("SELECT id FROM " . tablename($this->table_camerapl) . " where weid = :weid AND schoolid = :schoolid AND carmeraid = :carmeraid AND userid = :userid", array(':weid' => $weid, ':schoolid' => $schoolid, ':carmeraid' => $videoid, ':userid' => $it['id']));
		$name = $mybj['name'];
		$pic = $mybj['videopic'];

		if($_W['schooltype']){
			$nowhours = date("Hi",time());
			$endtime = strtotime(date('Y-m-d')) + 86399;
			$mintime = strtotime(date('Y-m-d'));
			$video = pdo_fetch("SELECT from_unixtime(c.sd_start, '%H%i') as start,from_unixtime(c.sd_end, '%H%i') as end,ac.video FROM " . GetTableName('kcbiao') . " as kc LEFT JOIN " . GetTableName('classify') . " as c ON c.sid = kc.sd_id LEFT JOIN " . GetTableName('classify') . " as ac ON ac.sid = kc.addr_id WHERE kc.schoolid ='{$schoolid}' AND kc.kcid = '{$_GPC['kcid']}' And (kc.date BETWEEN {$mintime} and {$endtime}) HAVING start <= '{$nowhours}' AND end >= '{$nowhours}' ");
			if($mac != 'ios'){
				// $thisvideo = $mybj['videourl'];
				// if (preg_match('/lechange/i', $mybj['videourl'])) {
				// 	$thisvideo = $mybj['videourl'].'?v='.getRandomString(32);
				// }		
				if($mybj['videotype'] != 2 ){
					$thisvideo = $video['video'];
				}else{
					$thisvideo = $mybj['videourl'];
				}			
				// $thisvideo = $video['video'];
				if (preg_match('/lechange/i', $video['video'])) {
					$thisvideo = $mybj['video'].'?v='.getRandomString(32);
				}					
			}else{
				if($mybj['videotype'] != 2 ){
					$thisvideo = $video['video'];
				}else{
					$thisvideo = $mybj['videourl'];
				}	
				// $thisvideo = $video['video'];
			}
		}else{
			if($mac != 'ios'){
				$thisvideo = $mybj['videourl'];
				if (preg_match('/lechange/i', $mybj['videourl'])) {
					$thisvideo = $mybj['videourl'].'?v='.getRandomString(32);
				}					
			}else{
				$thisvideo = $mybj['videourl'];
			}
		}

		
		$thisclick = $mybj['click'];
		$click = $mybj['click'] + 1;
		pdo_update($this->table_allcamera, array('click' =>  $click), array('id' =>  $videoid));
		$allpl = pdo_fetchall("SELECT * FROM " . tablename($this->table_camerapl) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND carmeraid = '{$videoid}' AND type = 2 ORDER BY createtime DESC LIMIT 0,10");
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
		$my = pdo_fetch("SELECT thumb FROM " . tablename($this->table_teachers) . " where id = :id ", array(':id' => $it['tid']));
		$myicon = empty($my['thumb']) ? $school['tpic'] : $my['thumb'];
		if (!empty($_W['setting']['remote']['type'])) { 
			$urls = $_W['attachurl']; 
		} else {
			$urls = $_W['siteroot'].'attachment/';
		}	
	}
	
	if($operation == 'scroll_more'){
		$time = $_GPC['LiData']['time'];
		$limit_start = $time + 1;
		if($_GPC['id']){
			$conditions = " AND carmeraid = '{$_GPC['id']}' ";
		}elseif($_GPC['ksid']){
			$conditions = " AND ksid = '{$_GPC['ksid']}' ";
		}
		$allpl = pdo_fetchall("SELECT * FROM " . tablename($this->table_camerapl) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND type = 2 $conditions ORDER BY createtime DESC LIMIT {$limit_start},10");
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
		include $this->template('comtool/tcamera');
        exit;
	}
	
	include $this->template(''.$school['style3'].'/tcamera');
}else{
	session_destroy();
	$stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
	header("location:$stopurl");
	exit;
}        
?>