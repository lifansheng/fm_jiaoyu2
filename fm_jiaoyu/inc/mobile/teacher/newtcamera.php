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
$type = $_GPC['type'];
$operation = $_GPC['op'] ? $_GPC['op'] : 'display';
$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :openid = openid And :sid = sid", array(':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0));

$school = pdo_fetch("SELECT videoname,videopic,style3,title,spic,tpic,thumb FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
$set = pdo_fetch("SELECT sensitive_word FROM " . tablename($this->table_set) . " where weid = :weid ", array(':weid' => $weid));
$allowpy = 1;		
if(!empty($it)){
    $mac = get_device_type();
    $isonline = true;	

    if($_GPC['type'] == 2){ //公共
        $mybj = pdo_fetch("SELECT * FROM " . tablename($this->table_allcamera) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND id = '{$videoid}'");			
        $myplsl  = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_camerapl) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND carmeraid = '{$videoid}' And type = 2");
        $mydzsl  = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_camerapl) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND carmeraid = '{$videoid}' And type = 1");
        $myisdz = pdo_fetch("SELECT id FROM " . tablename($this->table_camerapl) . " where weid = :weid AND schoolid = :schoolid AND carmeraid = :carmeraid AND userid = :userid", array(':weid' => $weid, ':schoolid' => $schoolid, ':carmeraid' => $videoid, ':userid' => $it['id']));
        $name = $mybj['name'];
        $pic = $mybj['videopic'];
        if($mac != 'ios'){
            $thisvideo = $mybj['videourl'];
            if (preg_match('/lechange/i', $mybj['videourl'])) {
                $thisvideo = $mybj['videourl'].'?v='.getRandomString(32);
            }					
        }else{
            $thisvideo = $mybj['videourl'];
        }
        $thisclick = $mybj['click'];
        $click = $mybj['click'] + 1;
        pdo_update($this->table_allcamera, array('click' =>  $click), array('id' =>  $videoid));
        $allpl = pdo_fetchall("SELECT * FROM " . tablename($this->table_camerapl) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND carmeraid = '{$videoid}' AND type = 2 ORDER BY createtime DESC LIMIT 0,10");
        foreach($allpl as $key => $row){
            $user = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where id = :id ", array(':id' => $row['userid']));
            $allpl[$key]['time'] = sub_day($row['createtime']);
            if(empty($user)){
                $yk = mc_fansinfo($row['openid']);
                $allpl[$key]['name'] = $yk['nickname'].'游客';
                $allpl[$key]['icon'] = $yk['tag']['avatar'];
            }else{
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
                        $item = pdo_fetch("SELECT avatar FROM " . tablename ( 'mc_members' ) . " where uniacid = :uniacid AND uid=:uid ", array(':uid' => $user['id'], ':uniacid' => $weid)); 
                        $allpl[$key]['icon'] = $item['avatar'] ? $item['avatar'] : $school['spic'];
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
        
    }
    if($_GPC['type'] == 1){ //教室
        $classity = pdo_fetch("SELECT video,sname FROM " . tablename($this->table_classify) . " WHERE schoolid = '{$schoolid}' AND sid = '{$videoid}'");
        if($mac != 'ios'){
            $thisvideo = $classity['video'];
            if (preg_match('/lechange/i', $classity['video'])) {
                $thisvideo = $classity['video'].'?v='.getRandomString(32);
            }					
        }else{
            $thisvideo = $classity['video'];
        }
        $name = $classity['sname'];
        $pic = $school['thumb'];
        //获取当前教室正在上课的教室
        $onlineAddr = getJsIsOnline($schoolid,$videoid);
        if(!empty($onlineAddr)){
            $mybj = pdo_fetch("SELECT * FROM " . tablename($this->table_allcamera) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND kcid = '{$onlineAddr['kcid']}' AND ispx = 1");
            $videoid = 0;	
            if($mybj){
                $thisclick = $mybj['click'];
                $click = $mybj['click'] + 1;
                $videoid = $mybj['id'];
                pdo_update($this->table_allcamera, array('click' =>  $click), array('id' =>  $mybj['id']));
                $myplsl  = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_camerapl) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND carmeraid = '{$mybj['id']}' And type = 2");
                $mydzsl  = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_camerapl) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND carmeraid = '{$mybj['id']}' And type = 1");
                $myisdz = pdo_fetch("SELECT id FROM " . tablename($this->table_camerapl) . " where weid = :weid AND schoolid = :schoolid AND carmeraid = :carmeraid AND userid = :userid", array(':weid' => $weid, ':schoolid' => $schoolid, ':carmeraid' => $mybj['id'], ':userid' => $it['id']));	 
                $allpl = pdo_fetchall("SELECT * FROM " . tablename($this->table_camerapl) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND carmeraid = '{$mybj['id']}' AND carmeraid != '0' AND type = 2 ORDER BY createtime DESC LIMIT 0,10");
                foreach($allpl as $key => $row){
                    $user = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where id = :id ", array(':id' => $row['userid']));
                    $allpl[$key]['time'] = sub_day($row['createtime']);
                    if(empty($user)){
                        $yk = mc_fansinfo($row['openid']);
                        $allpl[$key]['name'] = $yk['nickname'].'游客';
                        $allpl[$key]['icon'] = $yk['tag']['avatar'];
                    }else{
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
                                $item = pdo_fetch("SELECT avatar FROM " . tablename ( 'mc_members' ) . " where uniacid = :uniacid AND uid=:uid ", array(':uid' => $user['id'], ':uniacid' => $weid)); 
                                $allpl[$key]['icon'] = $item['avatar'] ? $item['avatar'] : $school['spic'];
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
            }else{
                $isonline = false;
            }
        }else{
            $isonline = false;
        }
        
    }
	if (!empty($_W['setting']['remote']['type'])) { 
        $urls = $_W['attachurl']; 
    } else {
        $urls = $_W['siteroot'].'attachment/';
    }	
    $my = pdo_fetch("SELECT thumb FROM " . tablename($this->table_teachers) . " where id = :id ", array(':id' => $it['tid']));
    $myicon = empty($my['thumb']) ? $school['tpic'] : $my['thumb'];
        
	if($operation == 'scroll_more'){
		$time = $_GPC['LiData']['time'];
        $limit_start = $time + 1;
        $conditions = " AND carmeraid = '{$_GPC['id']}' ";
        $allpl = pdo_fetchall("SELECT * FROM " . tablename($this->table_camerapl) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND type = 2 $conditions ORDER BY createtime DESC LIMIT {$limit_start},10");
        foreach($allpl as $key => $row){
            $user = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where id = :id ", array(':id' => $row['userid']));
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
                    $item = pdo_fetch("SELECT avatar FROM " . tablename ( 'mc_members' ) . " where uniacid = :uniacid AND uid=:uid ", array(':uid' => $user['uid'], ':uniacid' => $weid)); 
                    $allpl[$key]['icon'] = $item['avatar'];
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
		include $this->template('comtool/tcamera');
        exit;
	}
	
	include $this->template(''.$school['style3'].'/newtcamera');
}else{
	session_destroy();
	$stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
	header("location:$stopurl");
	exit;
}        
?>