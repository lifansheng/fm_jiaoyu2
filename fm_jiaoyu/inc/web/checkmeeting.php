<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */

global $_GPC, $_W;

$weid              = $_W['uniacid'];
$action            = 'groupactivity';
$this1             = 'no3';
$schoolid          = intval($_GPC['schoolid']);
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$logo              = pdo_fetch("SELECT logo,title,spic,tpic FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$operation         = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$tid_global = $_W['tid'];
// if (!(IsHasQx($tid_global,1001701,1,$schoolid))){
// 	$this->imessage('非法访问，您无权操作该页面','','error');
// }
 
if ($operation == 'display') {
    $pindex    = max(1, intval($_GPC['page']));
    $psize     = 10;
    $condition = '';
    
    $params    = array();
    if (!empty($_GPC['meeting_name'])) {
        $meeting_name   = $_GPC['meeting_name'];
        $condition   .= " AND c.title LIKE '%{$meeting_name}%'";
    }
    $searchStime = strtotime(date("- 300 day"));
    $searchEtime = strtotime(date("Y-m-d")) + 86399;
    if (!empty($_GPC['searchtime']['start'])) {
        $searchStime  = strtotime($_GPC['searchtime']['start']);
        $searchEtime  = strtotime($_GPC['searchtime']['end']) + 86399 ;
        if ($searchStime != '-28800' && $searchEtime != '-28800') {
            $condition  .= " AND ( ( c.starttime >= {$searchStime} AND c.starttime <= {$searchEtime} ) OR ( c.endtime >= {$searchStime} AND c.endtime <= {$searchEtime} ) )";
        }
    }
    $list = pdo_fetchall("SELECT c.*,t.tname FROM ".GetTableName('checkmeeting')." as c LEFT JOIN ".GetTableName('teachers')." as t ON t.id = c.creator_tid WHERE c.weid = '{$weid}' and c.schoolid = '{$schoolid}' {$condition}  ORDER BY c.createtime DESC  LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
    foreach($list as &$value){
        if($value['type'] == 1){
            $value['FzList'] = pdo_fetchall("SELECT sname FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and FIND_IN_SET(sid,'{$value['fzlist']}') and type = 'jsfz' ");
            $value['TeaList'] = pdo_fetchall("SELECT tname FROM ".GetTableName('teachers')." WHERE schoolid = '{$schoolid}' and FIND_IN_SET(id,'{$value['tidlist']}') ");
        }else{
            $value['BjList'] = pdo_fetchall("SELECT sname FROM ".GetTableName('classify')." WHERE  schoolid = '{$schoolid}' and FIND_IN_SET(sid,'{$value['bjidlist']}') and  type = 'theclass'  ");
        }
        if(($value['starttime'] - $value['earlytime'] * 60) < time()){
            $value['hasBegin'] = true;
        }
    }

    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".GetTableName('checkmeeting')." as c LEFT JOIN ".GetTableName('teachers')." as t ON t.id = c.creator_tid WHERE c.weid = '{$weid}' and c.schoolid = '{$schoolid}' {$condition}  ORDER BY c.createtime DESC ");
    $pager = pagination($total, $pindex, $psize);

} elseif ($operation == 'post') {
    // if (!(IsHasQx($tid_global, 1001702, 1, $schoolid))) {
    //     $this->imessage('非法访问，您无权操作该页面', '', 'error');
    // }
    $teachers = pdo_fetchall("SELECT tname,id FROM " . tablename($this->table_teachers) . " WHERE weid = '{$weid}' AND schoolid ={$schoolid} ORDER BY CONVERT(tname USING gbk) ASC");
    $BjList = pdo_fetchall("SELECT sid,sname FROM ".GetTableName('classify')." WHERE  schoolid = '{$schoolid}' and weid = '{$weid}' and type = 'theclass'  ");
    $NjList = pdo_fetchall("SELECT sid,sname FROM ".GetTableName('classify')." WHERE  schoolid = '{$schoolid}' and weid = '{$weid}' and type = 'semester'  ");
    $TeaFzList = pdo_fetchall("SELECT sid,sname FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and weid = '{$weid}' and type = 'jsfz' ");
    $id = intval($_GPC['id']);
    if (!empty($id)) {
        $checkItem = pdo_fetch("SELECT * FROM " .GetTableName('checkmeeting') . " WHERE id = '{$id}' ");
        $fzlist = pdo_fetchall("SELECT sname,sid FROM ".GetTableName('classify')." WHERE schoolid='{$schoolid}' AND FIND_IN_SET(sid,'{$checkItem['fzlist']}') ");
        $extralist = pdo_fetchall("SELECT tname,id FROM ".GetTableName('teachers')." WHERE schoolid='{$schoolid}' AND FIND_IN_SET(id,'{$checkItem['tidlist']}') ");
        if (empty($checkItem)) {
            $this->imessage('抱歉，本条信息不存在在或是已经删除！', $this->createWebUrl('checkmeeting', array('op' => 'display', 'schoolid' => $schoolid)), 'error');
        }
    }
    if (checksubmit('submit')) {
        $starttime = strtotime($_GPC['starttime']);
        $endtime = strtotime($_GPC['endtime']);
        if ($starttime > $endtime) {
            $this->imessage('时间范围设置错误,开始时间不能大于结束时间！', '', 'error');
        }
        $data = array(
            'weid' => $weid,
            'schoolid' => $schoolid,
            'title' => trim($_GPC['title']),
            'starttime' => $starttime,
            'endtime' => $endtime,
            'thumb' => $_GPC['thumb'],
            'content' => $_GPC['content'],
            'type' => $_GPC['type'],
            'earlytime' => intval($_GPC['earlytime']),
            'creator_tid' => $_GPC['creator_tid'],
        );
        if ($_GPC['type'] == 1) { //教师会议
            $FzList = $_GPC['FzArr'];
            $TeaList = $_GPC['TeaArr'];
            $data['fzlist'] = implode(',', $FzList);
            $data['tidlist'] = implode(',', $TeaList);
        } else {
            $data['bjidlist'] = $_GPC['bjid'];
            $data['njid'] = $_GPC['njid'];
        }
        if (!empty($id)) {
            pdo_update(GetTableName('checkmeeting', false), $data, array('id'=>$id));
        } else {
            $data['createtime'] = time();
            pdo_insert(GetTableName('checkmeeting', false), $data);
            $id = pdo_insertid();
        }
        //群发操作
        if ($_GPC['is_allsend'] == 1) {
            $url = $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&m=fm_jiaoyu&do=sendMsg';
            $data = array(
                'id' => $id,
                'schoolid' => $schoolid,
                'weid' => $weid,
                'op' => 'sendMobileMeeting',
            );
            timeOutPost($url, $data);
        }
        $this->imessage('操作成功', $this->createWebUrl('checkmeeting', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
    }
} elseif ($operation == 'delete') {
    $id      = intval($_GPC['id']);
    $article = pdo_fetch("SELECT id FROM " . GetTableName('checkmeeting') . " WHERE id = '$id'");
    if (empty($article)) {
        $this->imessage('抱歉，不存在或是已经被删除！', $this->createWebUrl('checkmeeting', array('op' => 'display', 'schoolid' => $schoolid)), 'error');
    }
    pdo_delete(GetTableName('checkmeeting',false), array('id' => $id));
    pdo_delete(GetTableName('meetinglog',false), array('meeting_id' => $id));
    $this->imessage('删除成功！', $this->createWebUrl('checkmeeting', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
} elseif ($operation == 'GetTeaListByFz') {
    $id = $_GPC['id'];
    if (empty($id)) {
        $result['status'] = false;
        $result['msg'] = '分组ID为空';
    } else {
        if ($id == -1) {
            $id = 0;
        }
        $TeaList = pdo_fetchall("SELECT id,tname FROM ".GetTableName('teachers')." WHERE schoolid = '{$schoolid}' and fz_id = '{$id}' ORDER BY CONVERT(tname USING gbk) ASC");
        $result['status'] = true;
        $result['data']   = $TeaList;
        $result['msg']    = "获取成功";
    }
    die(json_encode($result, JSON_UNESCAPED_UNICODE));
} elseif ($operation == 'signlist'){
    $id = $_GPC['id'];
    $title = pdo_fetch("SELECT title FROM ".GetTableName('checkmeeting')." WHERE id = '{$id}'")['title'];
    // $creator = pdo_fetchall("SELECT ml.id,t.tname as username, (case when t.thumb then t.thumb else '{$logo['tpic']}' end) as icon,'发起人' as tips,ml.createtime FROM ".GetTableName('checkmeeting')." as cm LEFT JOIN " . GetTableName('meetinglog') . " as ml ON ml.meeting_id = cm.id LEFT JOIN " . GetTableName('user') . " as u ON u.id = ml.userid LEFT JOIN " . GetTableName('teachers') . " as t ON t.id = cm.creator_tid  WHERE cm.schoolid='{$schoolid}' AND cm.id = '{$id}' AND ml.meeting_id = '{$id}' AND u.tid = cm.creator_tid ");
    $creator = pdo_fetchall("SELECT log.id as id,log.createtime,(case when tt.thumb then tt.thumb else '{$logo['tpic']}' end) as icon,'发起人' as tips,tt.tname as username FROM ".GetTableName('checkmeeting')." as cm LEFT JOIN ( select ml.meeting_id,ml.id,ml.createtime FROM ".GetTableName('checkmeeting')." as m LEFT  JOIN ".GetTableName('meetinglog')." as ml ON ml.meeting_id = m.id  LEFT JOIN ".GetTableName('user')." as u ON u.tid = m.creator_tid LEFT JOIN ".GetTableName('teachers')." as t ON t.id = m.creator_tid WHERE  t.id = m.creator_tid AND ml.userid = u.id) as log ON log.meeting_id = cm.id LEFT JOIN ".GetTableName('teachers')." as tt ON tt.id = cm.creator_tid WHERE cm.id = '{$id}'");
    foreach ($creator as $key => $value) {
        $creator[$key]['createtime'] = $value['createtime'] ? date('Y-m-d H:i',$value['createtime']) : '未签到';
        $creator[$key]['icon'] = tomedia($value['icon']);
    }
    foreach ($creator as &$vc) {
        $vc['icon'] = tomedia($vc['icon']);
        $vc['createtime'] = $vc['createtime'] ? date('Y-m-d H:i',$vc['createtime']) : '未签到';
    }
    $list = [];
    $meetinfo = pdo_fetch("SELECT type,fzlist,tidlist,bjidlist FROM" . GetTableName('checkmeeting') . " WHERE id = '{$id}'");
    if($meetinfo['type'] == 1){//老师
        $teainfo = pdo_fetchAll("SELECT t.tname as username,t.thumb as icon,c.sname as tips,(case when u.id = m.userid then m.id else 0 end) as signid,m.createtime FROM ".GetTableName('teachers')." as t LEFT JOIN ".GetTableName('user')." as u ON u.tid = t.id LEFT JOIN ".GetTableName('classify')." as c ON c.sid = t.fz_id LEFT JOIN ".GetTableName('meetinglog')." as m ON u.id = m.userid WHERE FIND_IN_SET(t.fz_id,'{$meetinfo['fzlist']}') OR FIND_IN_SET(t.id,'{$meetinfo['tidlist']}') ORDER BY signid DESC, CONVERT(username USING gbk) ASC");
        foreach ($teainfo as $key => $value) {
            $list[$key]['icon'] = !empty($value['icon'])? tomedia($value['icon']) : tomedia($logo['tpic']);
            $list[$key]['username'] = $value['username'];
            $list[$key]['tips'] = !empty($value['tips'])? $value['tips'] : '未分组';
            $list[$key]['id'] = $value['signid'];
            $list[$key]['createtime'] = $value['signid'] ? date('Y-m-d H:i',$value['createtime']) : '未签到';
        }
    }else{
        $stulist = pdo_fetchAll("SELECT u.realname as username, s.s_name as stuname, u.pard, s.icon,u.id,(case when u.id = m.userid then m.id else 0 end) as signid,m.createtime FROM ".GetTableName('students')." as s LEFT JOIN ".GetTableName('user')." as u ON u.sid = s.id LEFT JOIN ".GetTableName('meetinglog')." as m ON u.id = m.userid WHERE FIND_IN_SET(s.bj_id,'{$meetinfo['bjidlist']}') ORDER BY signid DESC, CONVERT(username USING gbk) ASC");
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
            $list[$key]['id'] = $value['signid'];
            $list[$key]['icon'] = !empty($value['icon'])? tomedia($value['icon']) : tomedia($logo['spic']);
            $list[$key]['username'] = !empty($value['username'])? $value['username'] : $value['username'].$guanxi;
            $list[$key]['tips'] = !empty($guanxi)? $value['stuname'].$guanxi : $value['stuname']."本人";
            $list[$key]['createtime'] = $value['signid'] ? date('Y-m-d H:i',$value['createtime']) : '未签到';
        }
    }
    $newlist = array_merge($creator,$list);
    $result['data'] = $newlist;
    $result['title'] = $title;
    die(json_encode($result));
} elseif ($operation == 'delLog'){
    $id = $_GPC['id'];
    pdo_delete(GetTableName('meetinglog',false),array('id'=>$id));
    $result['msg'] = '删除成功';
    $result['result'] = true;
    die(json_encode($result));
}
include $this->template('web/checkmeeting');
