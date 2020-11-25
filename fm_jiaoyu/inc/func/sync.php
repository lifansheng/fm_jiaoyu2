<?php
/**
 * By 高贵血迹
 */
	global $_GPC, $_W;
    $GetData = $_GPC['__input'];
    mload()->model('znl');
    if($GetData['op'] == 'startsync'){
        $schoolid = $GetData['schoolid'];
        $weid = $GetData['weid'];
        $res = pdo_update(GetTableName('schoolset',false),array('synctime'=>time()),array('schoolid'=>$schoolid));
        $total = pdo_fetchcolumn("SELECT count(id) FROM ".GetTableName('students')." WHERE schoolid = '{$schoolid}' ");
        $sid = pdo_fetch("SELECT id FROM ".GetTableName('students')." WHERE schoolid = '{$schoolid}' ORDER BY id ASC ")['id'];
        $url = $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&m=fm_jiaoyu&do=sync';
        $start = $GetData['start'] ? $GetData['start'] : 0;
        $data = array(
            'schoolid' => $schoolid,
            'weid' => $weid,
            'op' => 'getsync',
            'minsid' => $GetData['sid'] ? $GetData['sid'] : $sid,
            'total' => $total,
            'start' => $start,
        );
        CommonWriteLog('first:','syncstudentreport');
        CommonWriteLog($data,'syncstudentreport');
        // znlGetReport($schoolid,$sid,'','','');
        timeOutPost($url, $data);
    }
    if($GetData['op'] == 'getsync'){
        $schoolid = $GetData['schoolid'];
        $weid = $GetData['weid'];
        mload()->model('znl');
        $result = znlGetReport($schoolid,'','','',$type);
        $students = pdo_fetchAll("SELECT id FROM ".GetTableName('students')." WHERE schoolid = '{$schoolid}' AND id > '{$GetData['minsid']}' ORDER BY id ASC LIMIT 0,5 ");
        $minsid = $students[0]['id'];
        if($students){
            foreach ($students as $v) {
                $minsid = $v['id'] > $minsid ? $v['id'] : $minsid;
                $result = znlGetReport($schoolid,$v['id'],'','','');
                CommonWriteLog('获取结果：'.$v['id'],'syncstudentreport');
                CommonWriteLog($result,'syncstudentreport');
            }
        }
        $url = $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&m=fm_jiaoyu&do=sync';
        $data = array(
            'schoolid' => $schoolid,
            'weid' => $weid,
            'op' => 'getsync',
            'minsid' => $minsid,
            'total' => $GetData['total'],
            'start' => $GetData['start'] + 5,
        );
       
        if($GetData['start']+5 < $GetData['total']){
            timeOutPost($url, $data);
        }else{
            pdo_update(GetTableName('schoolset',false),array('synctime'=>0),array('schoolid'=>$schoolid));
            CommonWriteLog('end','syncstudentreport');
        }
    }
	exit;
?>