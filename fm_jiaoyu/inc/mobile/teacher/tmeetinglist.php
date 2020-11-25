<?php
/*
 * @Discription:  教师会议列表
 * @Author: Hannibal·Lee
 * @Date: 2020-09-23 14:26:05
 * @LastEditTime: 2020-09-28 14:53:25
 */
 

global $_W, $_GPC;
$weid = $_W['uniacid'];
$from_user = $this->_fromuser;
$schoolid = intval($_GPC['schoolid']);
$openid = $_W['openid'];

$it = pdo_fetch("SELECT * FROM ".GetTableName('user')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' and openid = '{$openid}' and tid != 0 and sid = 0 ");
$tid_global = $it['tid'];
$school = pdo_fetch("SELECT * FROM ".GetTableName('index')." WHERE id = '{$schoolid}' ");
if (empty($it['id'])) {
    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
    header("location:$stopurl");
}else{
    $teafzid = pdo_fetch("SELECT fz_id FROM ".GetTableName('teachers')." WHERE schoolid = '{$schoolid}' and id = '{$it['tid']}' ")['fz_id'];
    if($_GPC['op'] == 'scroll_more'){
        $showType = 'scroll';
        $type = $_GPC['ExtraData']['ctype'];
        $nowtime = time();
        $limit_start = $_GPC['limit'] + 1;
        $condition = " AND (creator_tid = '{$it['tid']}' OR FIND_IN_SET('{$it['tid']}',tidlist) OR FIND_IN_SET('{$teafzid}',fzlist))";
        if($type == 'tea'){ //获取老师数据
            $condition .= ' AND c.type = 1 ';
        }elseif($type == 'stu'){ //获取家长数据
            $condition .= ' AND c.type = 2 ';
        }
        $list = pdo_fetchall( "SELECT c.*,t.tname,m.id as hassign FROM ".GetTableName('checkmeeting')." as c LEFT JOIN " . GetTableName('teachers') . " as t ON c.creator_tid = t.id LEFT JOIN ".GetTableName('meetinglog')." as m ON m.userid = '{$it['id']}' AND m.meeting_id = c.id WHERE c.schoolid = '{$schoolid}' $condition ORDER BY c.starttime DESC LIMIT {$limit_start},10");
        foreach ($list as $key => $value) {
            if($value['hassign']){ // 表示已签到
                $list[$key]['hassign'] = true;
            }else{
                $list[$key]['hassign'] = ($nowtime >= ($value['starttime'] - $value['earlytime'] * 60) && $nowtime <= $value['endtime']) ? false : true;
            }
            $list[$key]['location'] = $key + $limit_start;
        }
        include $this->template(''.$school['style3'].'/tmeetinglist_bot');
        exit;

    }elseif($_GPC['op'] == 'getMettingList'){

        $type = $_GPC['type'];
        $nowtime = time();
        $starttime = strtotime(date("Y-m-d",time()));
        $endtime = strtotime(date("Y-m-d",time())) + 86399;
        $condition = " AND (creator_tid = '{$it['tid']}' OR FIND_IN_SET('{$it['tid']}',tidlist) OR FIND_IN_SET('{$teafzid}',fzlist))";
        if($type == 'tea'){ //获取老师数据
            $condition .= ' AND c.type = 1 ';
        }elseif($type == 'stu'){ //获取家长数据
            $condition .= ' AND c.type = 2 ';
        }
        $showType = 'get';
        //获取今日数据
        $nowdaylist = pdo_fetchall("SELECT c.*,t.tname,m.id as hassign FROM ".GetTableName('checkmeeting')." as c LEFT JOIN " . GetTableName('teachers') . " as t ON c.creator_tid = t.id LEFT JOIN ".GetTableName('meetinglog')." as m ON m.userid = '{$it['id']}' AND m.meeting_id = c.id WHERE c.schoolid = '{$schoolid}' AND (c.starttime BETWEEN '{$starttime}' AND '{$endtime}' OR c.endtime BETWEEN '{$starttime}' AND '{$endtime}') $condition");
        foreach ($nowdaylist as $key => $value) {
            if($value['hassign']){ // 表示已签到
                $nowdaylist[$key]['hassign'] = true;
            }else{
                $nowdaylist[$key]['hassign'] = ($nowtime >= ($value['starttime'] - $value['earlytime'] * 60) && $nowtime <= $value['endtime']) ? false : true;
            }
        }
        $list = pdo_fetchall("SELECT c.*,t.tname,m.id as hassign FROM ".GetTableName('checkmeeting')." as c LEFT JOIN " . GetTableName('teachers') . " as t ON c.creator_tid = t.id LEFT JOIN ".GetTableName('meetinglog')." as m ON m.userid = '{$it['id']}' AND m.meeting_id = c.id WHERE c.schoolid = '{$schoolid}' $condition ORDER BY c.starttime DESC LIMIT 0,10");
        foreach ($list as $key => $value) {
            if($value['hassign']){ // 表示已签到
                $list[$key]['hassign'] = true;
            }else{
                $list[$key]['hassign'] = ($nowtime >= ($value['starttime'] - $value['earlytime'] * 60) && $nowtime <= $value['endtime']) ? false : true;
            }
        }
        include $this->template(''.$school['style3'].'/tmeetinglist_bot');
        die;
    }elseif($_GPC['op'] == 'saveMeeting'){
        $meet_id = $_GPC['meet_id'];
        $type = pdo_fetch("SELECT type FROM ".GetTableName('checkmeeting')." WHERE schoolid = '{$schoolid}' and id = '{$meet_id}' ")['type'];
        $data = array(
            'weid' => $weid,
            'schoolid' => $schoolid,
            'meeting_id' => $meet_id,
            'userid' => $it['id'],
            'tid' => $it['tid'],
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
        if($meetinfo['type'] == 1){//老师
            //获取签到的老师
            $fzlist = pdo_fetchall("SELECT sname FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' AND FIND_IN_SET(sid,'{$meetinfo['fzlist']}')");
            $tealist = pdo_fetchall("SELECT tname FROM ".GetTableName('teachers')." WHERE schoolid = '{$schoolid}' AND FIND_IN_SET(id,'{$meetinfo['tidlist']}')");
            
        }else{
            $bjlist = pdo_fetchall("SELECT sname FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' AND FIND_IN_SET(sid,'{$meetinfo['bjidlist']}')");
            $bjlist = arrayToString($bjlist);
        }
        include $this->template(''.$school['style3'].'/tmeetinglistinfo_bot');
        die;
    }else{
        include $this->template(''.$school['style3'].'/tmeetinglist');
    }
}
