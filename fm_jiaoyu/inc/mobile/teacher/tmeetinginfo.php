<?php
/*
 * @Discription:  教师会议列表
 * @Author: Hannibal·Lee
 * @Date: 2020-09-23 14:26:05
 * @LastEditTime: 2020-09-28 14:07:19
 */
 

global $_W, $_GPC;
$weid = $_W['uniacid'];
$schoolid = intval($_GPC['schoolid']);
$openid = $_W['openid'];
$id = $_GPC['id'];
$it = pdo_fetch("SELECT * FROM ".GetTableName('user')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' and openid = '{$openid}' and tid != 0 and sid = 0 ");
$school = pdo_fetch("SELECT * FROM ".GetTableName('index')." WHERE id = '{$schoolid}' ");
if (empty($it['id'])) {
    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
    header("location:$stopurl");
}else{
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
        $list = pdo_fetchall( "SELECT c.*,t.tname FROM ".GetTableName('checkmeeting')." as c LEFT JOIN " . GetTableName('teachers') . " as t ON c.creator_tid = t.id WHERE c.schoolid = '{$schoolid}' $condition ORDER BY c.starttime LIMIT {$limit_start},10");
        foreach ($list as $key => $value) {
            $list[$key]['location'] = $key + $limit_start;
        }
        include $this->template(''.$school['style3'].'/tmeetinginfo_bot');
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
        $list = pdo_fetchall("SELECT c.*,t.tname FROM ".GetTableName('checkmeeting')." as c LEFT JOIN " . GetTableName('teachers') . " as t ON c.creator_tid = t.id WHERE c.schoolid = '{$schoolid}' $condition ORDER BY c.starttime LIMIT 0,10");
        include $this->template(''.$school['style3'].'/tmeetinginfo_bot');
        die;
    }else{
        $meeting = pdo_fetch("SELECT m.*,t.tname as username,t.thumb as icon,ml.id as signid,c.sname as tips,ml.createtime FROM ".GetTableName('checkmeeting')." as m LEFT JOIN ".GetTableName('teachers')." as t ON t.id = m.creator_tid LEFT JOIN ".GetTableName('meetinglog')." as ml ON ml.tid = m.creator_tid LEFT JOIN ".GetTableName('classify')." as c ON c.sid = t.fz_id WHERE m.id = '{$id}' ");
        $creator[] = $meeting;

        $signlist = [];
        $nosignlist = [];
        $newsignlist = [];

        if($meeting['type'] == 1){//老师
            //获取签到的老师
            $fzlist = pdo_fetchall("SELECT sname FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' AND FIND_IN_SET(sid,'{$meeting['fzlist']}')");
            // $fzlist = arrayToString($fzlist);
            $tealist = pdo_fetchall("SELECT tname FROM ".GetTableName('teachers')." WHERE schoolid = '{$schoolid}' AND FIND_IN_SET(id,'{$meeting['tidlist']}')");
            // $tealist = arrayToString($tealist);
            $teainfo = pdo_fetchAll("SELECT t.tname as username,t.thumb as icon,c.sname as tips,(case when u.id = m.userid then m.id else 0 end) as id,m.createtime FROM ".GetTableName('teachers')." as t LEFT JOIN ".GetTableName('user')." as u ON u.tid = t.id LEFT JOIN ".GetTableName('classify')." as c ON c.sid = t.fz_id LEFT JOIN ".GetTableName('meetinglog')." as m ON t.id = m.tid WHERE FIND_IN_SET(t.fz_id,'{$meeting['fzlist']}') OR FIND_IN_SET(t.id,'{$meeting['tidlist']}') ORDER BY id DESC, CONVERT(username USING gbk) ASC");
            foreach ($teainfo as $key => $value) {
                if($value['id'] == $id){
                    $newsignlist[$key]['icon'] = !empty($value['icon'])? tomedia($value['icon']) : tomedia($school['tpic']);
                    $newsignlist[$key]['username'] = $value['username'];
                    $newsignlist[$key]['tips'] = !empty($value['tips'])? $value['tips'] : '未分组';
                    $newsignlist[$key]['createtime'] = date("Y-m-d H:i:s",$value['createtime']);
                }else{
                    $nosignlist[$key]['icon'] = !empty($value['icon'])? tomedia($value['icon']) : tomedia($school['tpic']);
                    $nosignlist[$key]['username'] = $value['username'];
                    $nosignlist[$key]['tips'] = !empty($value['tips'])? $value['tips'] : '未分组';
                }
                
            }
        }else{
            $bjlist = pdo_fetchall("SELECT sname FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' AND FIND_IN_SET(sid,'{$meeting['bjidlist']}')");
            $bjlist = arrayToString($bjlist);

            $creatorlist = [];
            if($meeting['signid']){ //发起人签到
                foreach ($creator as $key => $value) {
                    $creatorlist[$key]['icon'] = !empty($value['icon'])? tomedia($value['icon']) : tomedia($school['tpic']);
                    $creatorlist[$key]['username'] = $value['username'];
                    $creatorlist[$key]['tips'] = !empty($value['tips'])? $value['tips'] : '未分组';
                    $creatorlist[$key]['createtime'] = date("Y-m-d H:i:s",$value['createtime']);
                }
            }
            $stulist = pdo_fetchAll("SELECT u.realname as username, s.s_name as stuname, u.pard, s.icon,(case when s.id = m.sid then m.id else 0 end) as id,m.createtime FROM ".GetTableName('students')." as s LEFT JOIN ".GetTableName('meetinglog')." as m ON m.sid = s.id LEFT JOIN ".GetTableName('user')." as u ON u.id = m.userid WHERE FIND_IN_SET(s.bj_id,'{$meeting['bjidlist']}') ORDER BY  CONVERT(s.s_name USING gbk) ASC , s.id DESC");
            foreach ($stulist as $key => $value) {
                if($value['pard'] == 2){
                    $guanxi = "妈妈";
                }else if($value['pard'] == 3){
                    $guanxi = "爸爸";
                }else if($value['pard'] == 4){
                    $guanxi = "";
                }else if($value['pard'] == 5){
                    $guanxi = "家长";
                }
                if($value['id']){
                    $signlist[$key]['icon'] = !empty($value['icon'])? tomedia($value['icon']) : tomedia($school['spic']);
                    $signlist[$key]['username'] = !empty($value['username'])? $value['username'] : $value['username'].$guanxi;
                    $signlist[$key]['tips'] = !empty($guanxi)? $value['stuname'].$guanxi : $value['stuname']."本人";
                    $signlist[$key]['createtime'] = date("Y-m-d H:i:s",$value['createtime']);
                }else{
                    $nosignlist[$key]['icon'] = !empty($value['icon'])? tomedia($value['icon']) : tomedia($school['spic']);
                    $nosignlist[$key]['username'] = !empty($value['stuname'])? $value['stuname'] : $value['username'].$guanxi;
                    $nosignlist[$key]['tips'] = !empty($guanxi)? $value['stuname'] : $value['stuname']."本人";
                }
            }
            $newsignlist = array_merge($creatorlist,$signlist);
        }
        include $this->template(''.$school['style3'].'/tmeetinginfo');
    }
}
