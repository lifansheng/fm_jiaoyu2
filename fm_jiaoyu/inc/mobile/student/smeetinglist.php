<?php
/*
 * @Discription:  
 * @Author: Hannibal·Lee
 * @Date: 2020-02-14 14:09:20
 * @LastEditTime: 2020-03-07 16:37:16
 */

global $_W, $_GPC;
$weid = $_W['uniacid'];
$schoolid = intval($_GPC['schoolid']);
$openid = $_W['openid'];
//查询是否用户登录		
$userid = !empty($_GPC['userid']) ? intval($_GPC['userid']) : 1;
if(!empty($_GPC['userid'])){
	mload()->model('user');
	$_SESSION['user'] = check_userlogin($weid,$schoolid,$openid,$userid);
    if ($_SESSION['user'] ==2){
		include $this->template('bangding');
	}	
}	
$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where id = :id ", array(':id' => $_SESSION['user']));
$student = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where weid = :weid AND id = :id", array(':weid' => $_W ['uniacid'], ':id' => $it['sid']));

$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ORDER BY ssort DESC", array(':weid' => $weid, ':id' => $schoolid));
if (empty($it['id'])) {
    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
    header("location:$stopurl");
}else{
    if($_GPC['op'] == 'scroll_more'){
        $showType = 'scroll';
        $type = $_GPC['ExtraData']['ctype'];
        $nowtime = time();
        $limit_start = $_GPC['limit'] + 1;
        $condition = ' AND c.type = 2 ';
        $list = pdo_fetchall( "SELECT c.*,t.tname,m.id as hassign FROM ".GetTableName('checkmeeting')." as c LEFT JOIN " . GetTableName('teachers') . " as t ON c.creator_tid = t.id LEFT JOIN ".GetTableName('meetinglog')." as m ON m.userid = '{$it['id']}' AND m.meeting_id = c.id WHERE c.schoolid = '{$schoolid}' $condition ORDER BY c.starttime DESC LIMIT {$limit_start},10");
        foreach ($list as $key => $value) {
            if($value['hassign']){ // 表示已签到
                $list[$key]['hassign'] = true;
            }else{
                $list[$key]['hassign'] = ($nowtime >= ($value['starttime'] - $value['earlytime'] * 60) && $nowtime <= $value['endtime']) ? false : true;
            }
            $list[$key]['location'] = $key + $limit_start;
        }
        include $this->template(''.$school['style2'].'/smeetinglist_bot');
        exit;

    }elseif($_GPC['op'] == 'getMettingList'){

        $type = $_GPC['type'];
        $nowtime = time();
        $starttime = strtotime(date("Y-m-d",time()));
        $endtime = strtotime(date("Y-m-d",time())) + 86399;
        $condition .= ' AND c.type = 2 ';
        $showType = 'get';
        //获取今日数据
        $nowdaylist = pdo_fetchall("SELECT c.*,t.tname,m.id as hassign FROM ".GetTableName('checkmeeting')." as c LEFT JOIN " . GetTableName('teachers') . " as t ON c.creator_tid = t.id LEFT JOIN ".GetTableName('meetinglog')." as m ON m.userid = '{$it['id']}' AND m.meeting_id = c.id WHERE c.schoolid = '{$schoolid}' AND FIND_IN_SET('{$student['bj_id']}',c.bjidlist) AND (c.starttime BETWEEN '{$starttime}' AND '{$endtime}' OR c.endtime BETWEEN '{$starttime}' AND '{$endtime}') $condition");
        foreach ($nowdaylist as $key => $value) {
            if($value['hassign']){ // 表示已签到
                $nowdaylist[$key]['hassign'] = true;
            }else{
                $nowdaylist[$key]['hassign'] = ($nowtime >= ($value['starttime'] - $value['earlytime'] * 60) && $nowtime <= $value['endtime']) ? false : true;
            }
        }
        $list = pdo_fetchall("SELECT c.*,t.tname,m.id as hassign FROM ".GetTableName('checkmeeting')." as c LEFT JOIN " . GetTableName('teachers') . " as t ON c.creator_tid = t.id LEFT JOIN ".GetTableName('meetinglog')." as m ON m.userid = '{$it['id']}' AND m.meeting_id = c.id WHERE c.schoolid = '{$schoolid}' AND FIND_IN_SET('{$student['bj_id']}',c.bjidlist) $condition ORDER BY c.starttime DESC LIMIT 0,10");
        foreach ($list as $key => $value) {
            if($value['hassign']){ // 表示已签到
                $list[$key]['hassign'] = true;
            }else{
                $list[$key]['hassign'] = ($nowtime >= ($value['starttime'] - $value['earlytime'] * 60) && $nowtime <= $value['endtime']) ? false : true;
            }
        }
        include $this->template(''.$school['style2'].'/smeetinglist_bot');
        die;
    }elseif($_GPC['op'] == 'saveMeeting'){
        $meet_id = $_GPC['meet_id'];
        $type = pdo_fetch("SELECT type FROM ".GetTableName('checkmeeting')." WHERE schoolid = '{$schoolid}' and id = '{$meet_id}' ")['type'];
        $data = array(
            'weid' => $weid,
            'schoolid' => $schoolid,
            'meeting_id' => $meet_id,
            'userid' => $it['id'],
            'sid' => $it['sid'],
            'type' => $type,
            'createtime' => time(),
        );
        $hasmeeting = pdo_fetch("SELECT id FROM ".GetTableName('meetinglog')." WHERE schoolid = '{$schoolid}' and meeting_id = '{$meet_id}' AND userid = '{$it['id']}' ");
        if($hasmeeting){
            $result['msg'] = '您已签到，不用再次签到';
            $result['result'] = false; 
        }else{
            pdo_insert(GetTableName('meetinglog',false),$data);
            $result['msg'] = '签到成功';
            $result['result'] = true;
        }
        die(json_encode($result));
    }elseif($_GPC['op'] == 'getInfo'){
        $id = $_GPC['id'];
        $meetinfo = pdo_fetch("SELECT * FROM ".GetTableName('checkmeeting')." WHERE schoolid = '{$schoolid}' and id = '{$id}' ");
        $bjlist = pdo_fetchall("SELECT sname FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' AND FIND_IN_SET(sid,'{$meetinfo['bjidlist']}')");
        $bjlist = arrayToString($bjlist);
        include $this->template(''.$school['style3'].'/tmeetinglistinfo_bot');
        die;
    }else{
        include $this->template(''.$school['style2'].'/smeetinglist');
    }
} 
   
