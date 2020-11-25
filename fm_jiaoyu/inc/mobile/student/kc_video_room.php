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
$ksid = $_GPC['ksid'];
$operation = $_GPC['op'] ? $_GPC['op'] : 'display';
$ksvideo =  pdo_fetch("SELECT * FROM " . GetTableName('kcbiao') . " where id='{$ksid}'");
if(!empty($_GPC['userid'])){
    $_SESSION['user'] = $_GPC['userid'];
    if($ksvideo['is_allow_reply'] == 1){
        $openly = true;
    }else{
        $openly = false;
    }
}else{
    $_SESSION['user'] = '';
    if($_SESSION['user']){
        if($ksvideo['is_allow_reply'] == 1){
            $openly = true;
        }else{
            $openly = false;
        }
    }else{
        $istourist = true; //游客不允许点赞
        if($ksvideo['is_allow_ykreply'] == 1){
            $myicon = $_W['fans']['tag']['avatar'];
            $openly = true; //游客允许评论
            $ykopenid = $_W['openid'];
        }else{
            $openly = false;
        }
    }
}
$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where id = :id ", array(':id' => $_SESSION['user']));
$school = pdo_fetch("SELECT videoname,videopic,style2,title,spic,tpic,headcolor FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
$set = pdo_fetch("SELECT sensitive_word FROM " . tablename($this->table_set) . " where weid = :weid ", array(':weid' => $weid));
$allowpy = 1;		
$student = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $it['sid']));	
$mac = get_device_type();

$ksstatus = true;
//判断是否在直播时间段
if($ksvideo['sk_start'] > time()){
    $ksmsg = '直播时间未到，请耐心等待，开始时间是：'.date("H:i:s",$ksvideo['sk_start']);
    $ksstatus = false;
}elseif($ksvideo['sk_end'] < time()){
    $ksmsg = '已到直播结束时间，下回再见！';
    $ksstatus = false;
    $noshowpl = true; //不显示评论
}
if($mac != 'ios'){
    $thisvideo = $ksvideo['content'];
    if (preg_match('/lechange/i', $ksvideo['content'])) {
        $thisvideo = $ksvideo['content'].'?v='.getRandomString(32);
    }					
}else{
    $thisvideo = $ksvideo['content'];
}	
//直播评论数量
$myplsl  = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_camerapl) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND ksid = '{$ksid}' And type = 2");
//直播点赞数量
$mydzsl  = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_camerapl) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND ksid = '{$ksid}' And type = 1");
//当前学生是否点赞
$myisdz = pdo_fetch("SELECT id FROM " . tablename($this->table_camerapl) . " where weid = :weid AND schoolid = :schoolid AND ksid = :ksid AND userid = :userid And type = 1", array(':weid' => $weid, ':schoolid' => $schoolid, ':ksid' => $ksid, ':userid' => $it['id']));
$name = $ksvideo['name'];
//获取点击数量并更新
$thisclick = $ksvideo['clicks'];
$click = $ksvideo['clicks'] + 1;
pdo_update($this->table_kcbiao, array('clicks' =>  $click), array('id' =>  $ksid));
$allpl = pdo_fetchall("SELECT * FROM " . tablename($this->table_camerapl) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND ksid = '{$ksid}' AND type = 2 ORDER BY createtime DESC LIMIT 0,10");
foreach($allpl as $key => $row){
    if(empty($row['userid'])){
        $fansinfo = GetWeFans($weid,$row['openid']);
        $allpl[$key]['name'] = $fansinfo['nickname']."(游客)";
        $allpl[$key]['icon'] = $fansinfo['avatar'];
    }else{
		$user = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where id = :id ", array(':id' => $row['userid']));
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
	$allpl[$key]['time'] = sub_day($row['createtime']);
}

if($it['pard'] == 4){
    $my = pdo_fetch("SELECT icon FROM " . tablename($this->table_students) . " where id = :id ", array(':id' => $it['sid']));
    $myicon = empty($my['icon']) ? $school['spic'] : $my['icon'];
}else{
    $fansinfo = GetWeFans($weid,$openid);
    $myicon = $fansinfo['avatar'];
}
if($operation == 'scroll_more'){
    $time = $_GPC['LiData']['time'];
    $limit_start = $time + 1;
    $allpl = pdo_fetchall("SELECT * FROM " . tablename($this->table_camerapl) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND ksid = '{$_GPC['ksid']}' AND type = 2 ORDER BY createtime DESC LIMIT {$limit_start},10");
    foreach($allpl as $key => $row){
		if(empty($row['userid'])){
			$fansinfo = GetWeFans($weid,$row['openid']);
			$allpl[$key]['name'] = $fansinfo['nickname']."(游客)";
			$allpl[$key]['icon'] = $fansinfo['avatar'];
		}else{
			$user = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where id = :id ", array(':id' => $row['userid']));
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
		$allpl[$key]['time'] = sub_day($row['createtime']);
		$allpl[$key]['location'] = $key + $limit_start;
    }
    include $this->template('comtool/kc_video_room');
    exit;
}
include $this->template(''.$school['style2'].'/kc_video_room');
    
?>