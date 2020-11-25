<?php
/**
 * By 高贵血迹
 */
	global $_GPC, $_W;
    $GetData = $_GPC['__input'];
    if($GetData['op'] == 'sendMobileNewZuoye'){
        $notice_id = $GetData['notice_id'];
        $weid = $GetData['weid'];
        $schoolid = $GetData['schoolid'];
        $notice = pdo_fetch("SELECT * FROM ".tablename($this->table_notice)." WHERE :weid = weid AND :id = id AND :schoolid = schoolid", array(':weid' => $GetData['weid'], ':id' => $notice_id, ':schoolid' => $GetData['schoolid']));
        $userdatas = trim(arrayToString(json_decode($notice['userdatas'],true)),',');
        if($notice['usertype'] == 'send_class'){
            $total = pdo_fetchcolumn("SELECT count(id) FROM ".GetTableName('students')." where weid = :weid And schoolid = :schoolid And bj_id = :bj_id ORDER BY id DESC",array(':weid'=>$GetData['weid'], ':schoolid'=>$GetData['schoolid'], ':bj_id'=>$GetData['bj_id']));
        }
        if($notice['usertype'] == 'student'){
            $total = pdo_fetchcolumn("SELECT count(id) FROM ".GetTableName('students')." where weid = :weid And schoolid = :schoolid And FIND_IN_SET(id,'{$userdatas}') ORDER BY id DESC",array(':weid'=>$GetData['weid'], ':schoolid'=>$GetData['schoolid']));
        }
        $start = $GetData['start'] ? $GetData['start'] : 0 ;
        $url = $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&m=fm_jiaoyu&do=sendMsg';
        $data = array(
            'notice_id' => $notice_id,
            'schoolid' => $GetData['schoolid'],
            'weid' => $GetData['weid'],
            'tname' => $GetData['tname'],
            'bj_id' => $GetData['bj_id'],
            'op' => 'sendMobileNewZuoye',
            'start' => $start + 5,
        );
        $this->sendMobileNewZuoye($GetData['notice_id'], $GetData['schoolid'], $GetData['weid'], $GetData['tname'], $GetData['bj_id'],$start);
        if($start+5 <$total){
            timeOutPost($url, $data);
        }
    }
    if($GetData['op'] == 'sendMobilePxZuoye'){
        $this->sendMobilePxZuoye($GetData['notice_id'], $GetData['schoolid'], $GetData['weid'], $GetData['tname']);
    }
    if($GetData['op'] == 'sendMobileNewXytz'){
        $notice_id = $GetData['notice_id'];
        $weid = $GetData['weid'];
        $schoolid = $GetData['schoolid'];
        $notice = pdo_fetch("SELECT * FROM ".tablename($this->table_notice)." WHERE :weid = weid AND :id = id AND :schoolid = schoolid", array(':weid' => $GetData['weid'], ':id' => $notice_id, ':schoolid' => $GetData['schoolid']));
        $groupid = $notice['groupid'];
        $usertype = $notice['usertype'];
        $userdatas = str_replace(";",",",$notice['userdatas']);
        if ($usertype == 'school') { //全校
            $total = pdo_fetchcolumn("SELECT count(id) FROM ".GetTableName('user')." where weid = :weid And schoolid = :schoolid",array(':weid'=>$weid, ':schoolid'=>$schoolid));
        }
        if ($usertype == 'alltea') { //全体老师
            $total = pdo_fetchcolumn("SELECT count(id) FROM ".GetTableName('teachers')." where weid = :weid And schoolid = :schoolid",array(':weid'=>$weid, ':schoolid'=>$schoolid));
        }
        if ($usertype == 'allstu') { //全体学生
            $total = pdo_fetchcolumn("SELECT count(id) FROM ".GetTableName('students')." where weid = :weid And schoolid = :schoolid ORDER BY id DESC",array(':weid'=>$weid, ':schoolid'=>$schoolid));
        }
        if ($usertype == 'send_class') { //指定课程
            if(!$schooltype){
                $total = pdo_fetchcolumn("SELECT count(id) FROM ".GetTableName('students')." where weid = :weid And schoolid = :schoolid AND FIND_IN_SET(bj_id,'{$userdatas}') ORDER BY id DESC",array(':weid'=>$weid, ':schoolid'=>$schoolid));
            }else{
                $total = pdo_fetchcolumn("SELECT count(DISTINCT(s.id)) FROM ".GetTableName('students')." as s RIGHT JOIN " . GetTableName('coursebuy') . " as c ON s.id = c.sid where c.weid = :weid And c.schoolid = :schoolid AND c.is_change != 1 AND FIND_IN_SET(c.kcid,'{$userdatas}') ORDER BY c.id DESC",array(':weid'=>$weid, ':schoolid'=>$schoolid));
            }
        }
        if ($usertype == 'staff_jsfz') { //指定教师组
            $total = pdo_fetchcolumn("SELECT count(id) FROM ".GetTableName('teachers')." where weid = :weid And schoolid = :schoolid AND FIND_IN_SET(fz_id,'{$userdatas}') ORDER BY id DESC",array(':weid'=>$weid, ':schoolid'=>$schoolid));
            $fztid = pdo_fetchAll("SELECT tidarr FROM " . tablename($this->table_classify) . " where FIND_IN_SET(sid,'{$userdatas}') and schoolid={$schoolid} And type='jsfz' ");
            $array = explode(',',arrayToString($fztid));
            $str = array_unique($array);
            $total2 = count($str);
        }

        if ($usertype == 'staff') { //指定教师
            $total = pdo_fetchcolumn("SELECT count(id) FROM ".GetTableName('teachers')." where weid = :weid And schoolid = :schoolid AND FIND_IN_SET(id,'{$userdatas}') ORDER BY id DESC",array(':weid'=>$weid, ':schoolid'=>$schoolid));
        }

        if ($usertype == 'student') { //指定学生
            $total = pdo_fetchcolumn("SELECT count(id) FROM ".GetTableName('students')." where weid = :weid And schoolid = :schoolid AND FIND_IN_SET(id,'{$userdatas}') ORDER BY id DESC",array(':weid'=>$weid, ':schoolid'=>$schoolid));
        }
        $start = $GetData['start'] ? $GetData['start'] : 0 ;
        $start2 = $GetData['start2'] ? $GetData['start2'] : 0 ;
        $type = $GetData['type'] ? $GetData['type'] : 'own' ;
        $IsChange = empty($GetData['ischange']) ? false : true;
        $pager = $GetData['type'] == 'other' ? $start2 : $start;
        $url = $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&m=fm_jiaoyu&do=sendMsg';
        $data = array(
            'notice_id' => $notice_id,
            'schoolid' => $schoolid,
            'weid' => $weid,
            'tname' => $GetData['tname'],
            'op' => 'sendMobileNewXytz',
        );
        if($start<$total || $start2 < $total2){
            $this->sendMobileNewXytz($GetData['notice_id'], $GetData['schoolid'], $GetData['weid'], $GetData['tname'],$GetData['schooltype'],$pager,$type);
            if($start < $total){
                $data['start'] = $start + 5;
                $data['start2'] = 0;
                $data['type'] = 'own';
            }elseif($start >= $total && $start2==0 && !$IsChange){
                $data['start'] = $start + 5;
                $data['start2'] = 0;
                $data['type'] = 'other';
                $data['ischange'] = true;
            }elseif($start2 < $total2){
                $data['start'] = $start;
                $data['start2'] = $start2 + 5;
                $data['type'] = 'other';
            }
            timeOutPost($url, $data);
        }
    }
    if($GetData['op'] == 'sendMobileNewPxBjtz'){
        $this->sendMobileNewPxBjtz($GetData['notice_id'], $GetData['schoolid'], $GetData['weid'], $GetData['tname']);
    }
    if($GetData['op'] == 'sendMobileNewHdtz'){
        $notice_id = $GetData['notice_id'];
        $weid = $GetData['weid'];
        $schoolid = $GetData['schoolid'];
        $total=pdo_fetchcolumn("SELECT count(id) FROM ".GetTableName('students')." where weid = :weid And schoolid = :schoolid And bj_id = :bj_id",array(':weid'=>$weid, ':schoolid'=>$schoolid, ':bj_id'=>$GetData['bj_id']));
        $url = $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&m=fm_jiaoyu&do=sendMsg';
        $data = array(
            'notice_id' => $notice_id,
            'schoolid' => $schoolid,
            'weid' => $weid,
            'tname' => $GetData['tname'],
            'op' => $GetData['sendMobileNewHdtz'],
            'bj_id' => $GetData['bj_id'],
            'start' => $start + 5,
        );
        $this->sendMobileNewHdtz($GetData['notice_id'], $GetData['schoolid'], $GetData['weid'], $GetData['tname'], $GetData['bj_id'],$start);
        if($start+5 <$total){
            timeOutPost($url, $data);
        }
    }
    if($GetData['op'] == 'sendMobileNewBjtz'){
        
        $notice_id = $GetData['notice_id'];
        $weid = $GetData['weid'];
        $schoolid = $GetData['schoolid'];
        $notice = pdo_fetch("SELECT * FROM ".tablename($this->table_notice)." WHERE :weid = weid AND :id = id AND :schoolid = schoolid", array(':weid' => $weid, ':id' => $notice_id, ':schoolid' => $schoolid));
        $userdatas = trim(arrayToString(json_decode($notice['userdatas'],true)),',');
        if($notice['usertype'] == 'send_class'){
            $total = pdo_fetchcolumn("SELECT count(id) FROM ".GetTableName('students')." where weid = :weid And schoolid = :schoolid And bj_id = :bj_id ORDER BY id DESC ",array(':weid'=>$weid, ':schoolid'=>$schoolid, ':bj_id'=>$notice['bj_id']));
        }
        if($notice['usertype'] == 'student'){
            $total = pdo_fetchcolumn("SELECT count(id) FROM ".GetTableName('students')." where weid = :weid And schoolid = :schoolid And FIND_IN_SET(id,'{$userdatas}') ORDER BY id DESC ",array(':weid'=>$weid, ':schoolid'=>$schoolid));
        }
        $start = $GetData['start'] ? $GetData['start'] : 0 ;
        $url = $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&m=fm_jiaoyu&do=sendMsg';
        $data = array(
            'notice_id' => $notice_id,
            'schoolid' => $GetData['schoolid'],
            'weid' => $GetData['weid'],
            'tname' => $GetData['tname'],
            'schooltype' => $GetData['schooltype'],
            'op' => 'sendMobileNewBjtz',
            'start' => $start + 5,
        );
        $this->sendMobileNewBjtz($GetData['notice_id'], $GetData['schoolid'], $GetData['weid'], $GetData['tname'],$start);
        if($start+5 <$total){
            timeOutPost($url, $data);
        }
    }

    if($GetData['op'] == 'sendMobileMeeting'){
        $meeting_id = $GetData['id'];
        $weid = $GetData['weid'];
        $schoolid = $GetData['schoolid'];
        $meeting = pdo_fetch("SELECT * FROM ". GetTableName('checkmeeting') ." WHERE :id = id AND :schoolid = schoolid", array(':id' => $meeting_id, ':schoolid' => $GetData['schoolid']));
        
        if ($meeting['type'] == '2') { //家长会议
            $total = pdo_fetchcolumn("SELECT count(id) FROM ".GetTableName('students')." where weid = :weid And schoolid = :schoolid AND FIND_IN_SET(bj_id,'{$meeting['bjidlist']}')",array(':weid'=>$weid, ':schoolid'=>$schoolid));
        }
        if ($meeting['type'] == '1') { //教师会议
            $total = 0;
            if($meeting['fzlist']){ //按分组通知
                $fznum = pdo_fetchcolumn("SELECT count(id) FROM ".GetTableName('teachers')." where weid = :weid And schoolid = :schoolid AND FIND_IN_SET(fz_id,'{$meeting['fzlist']}')",array(':weid'=>$weid, ':schoolid'=>$schoolid));
                $total += $fznum;
            }
            if($meeting['tidlist']){ //指定老师
                $teanum = pdo_fetchcolumn("SELECT count(id) FROM ".GetTableName('teachers')." where weid = :weid And schoolid = :schoolid AND FIND_IN_SET(id,'{$meeting['tidlist']}')",array(':weid'=>$weid, ':schoolid'=>$schoolid));
                $total += $teanum;
            }
        }
        $start = $GetData['start'] ? $GetData['start'] : 0 ;
       
        $url = $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&m=fm_jiaoyu&do=sendMsg';
        $data = array(
            'id' => $meeting_id,
            'schoolid' => $GetData['schoolid'],
            'weid' => $GetData['weid'],
            'op' => 'sendMobileMeeting',
            'start' => $start + 5,
        );
        $this->sendMobileMeeting($meeting_id, $GetData['schoolid'], $GetData['weid'],$start);
        if($start+5 < $total){
            timeOutPost($url, $data);
        }
    }

    if($GetData['op'] == 'sendMobileQuesForm'){
        $notice_id = $GetData['notice_id'];
        $weid = $GetData['weid'];
        $schoolid = $GetData['schoolid'];
        $notice = pdo_fetch("SELECT * FROM ".tablename($this->table_notice)." WHERE :weid = weid AND :id = id AND :schoolid = schoolid", array(':weid' => $GetData['weid'], ':id' => $notice_id, ':schoolid' => $GetData['schoolid']));
        $usertype = $notice['usertype'];
        if ($usertype == 'school') { //全校
            $total = pdo_fetchcolumn("SELECT count(id) FROM ".GetTableName('user')." where weid = :weid And schoolid = :schoolid",array(':weid'=>$weid, ':schoolid'=>$schoolid));
        }
        if ($usertype == 'alltea') { //全体老师
            $total = pdo_fetchcolumn("SELECT count(id) FROM ".GetTableName('teachers')." where weid = :weid And schoolid = :schoolid",array(':weid'=>$weid, ':schoolid'=>$schoolid));
        }

        if ($usertype == 'allstu') { //全体老师
            $total = pdo_fetchcolumn("SELECT count(id) FROM ".GetTableName('students')." where weid = :weid And schoolid = :schoolid",array(':weid'=>$weid, ':schoolid'=>$schoolid));
        }
        if ($usertype == 'bj') { //指定课程
            $total = pdo_fetchcolumn("SELECT count(id) FROM ".GetTableName('students')." where weid = :weid And schoolid = :schoolid AND FIND_IN_SET(bj_id,'{$notice['userdatas']}') ORDER BY id DESC",array(':weid'=>$weid, ':schoolid'=>$schoolid));
           
        }
        if ($usertype == 'jsfz') { //指定教师组
            $total = pdo_fetchcolumn("SELECT count(id) FROM ".GetTableName('teachers')." where weid = :weid And schoolid = :schoolid AND FIND_IN_SET(fz_id,'{$notice['userdatas']}') ORDER BY id DESC",array(':weid'=>$weid, ':schoolid'=>$schoolid));
        }
        $start = $GetData['start'] ? $GetData['start'] : 0 ;
        $url = $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&m=fm_jiaoyu&do=sendMsg';
        $data = array(
            'notice_id' => $notice_id,
            'schoolid' => $schoolid,
            'weid' => $weid,
            'tname' => $GetData['tname'],
            'op' => 'sendMobileQuesForm',
            'start' => $start + 5,
        );
        $res = $this->sendMobileQuesForm($GetData['notice_id'], $GetData['schoolid'], $GetData['weid'], $GetData['tname'],$GetData['schooltype'],$start);
        // var_dump($res);die;
        if($start+5 < $total){
            timeOutPost($url, $data);
        }
    }
	exit;
?>