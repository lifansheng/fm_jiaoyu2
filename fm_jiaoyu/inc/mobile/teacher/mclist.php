<?php
/*
 * @Discription:  
 * @Author: Hannibal·Lee
 * @Date: 2020-02-14 14:09:20
 * @LastEditTime: 2020-02-28 11:41:04
 */

global $_W, $_GPC;
$weid = $_W['uniacid'];
$schoolid = intval($_GPC['schoolid']);
$openid = $_W['openid'];
//查询是否用户登录		
$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ORDER BY id DESC", array(':weid' => $weid, ':id' => $userid['id']));		
$teacher = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $_W ['uniacid'], ':id' => $it['tid']));
$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ORDER BY ssort DESC", array(':weid' => $weid, ':id' => $schoolid));
$tid_global = $it['tid'];	
$operation = $_GPC['op'] ? $_GPC['op'] : 'display';	

$bj_id = intval($_GPC['bj_id']);
$createdate = $_GPC['createdate'];
$condition .= " And bj_id = '{$bj_id}' AND createdate = '{$createdate}'";	
$nowbj = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where sid = :sid ", array(':sid' => $bj_id));

if($operation == 'display'){
    if($_GPC['type'] == 1){
        $checklist =pdo_fetchall("SELECT * FROM " . GetTableName('morningcheck') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition GROUP BY sid ORDER BY createdate  DESC LIMIT 0,10");
        foreach ($checklist as $key => $value) {
            $stu = pdo_fetch("SELECT icon,s_name FROM ".GetTableName('students')." WHERE id = '{$value['sid']}' ");
            $checklist[$key]['icon'] = $stu['icon'];
            $checklist[$key]['s_name'] = $stu['s_name'];
        }
    }else{
        //获取已检查的学生id
        $checkstu =pdo_fetchall("SELECT sid FROM " . GetTableName('morningcheck') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition ");
        $stustr = arrayToString($checkstu);
        //未检查的学生列表
        $checklist =pdo_fetchall("SELECT s_name,icon,mobile FROM " . GetTableName('students') . " WHERE schoolid = '{$schoolid}' AND bj_id = '{$bj_id}' AND not FIND_IN_SET(id,'{$stustr}') ORDER BY createdate DESC LIMIT 0,10");
    }
}elseif($operation == 'scroll_more'){
    $time = $_GPC['LiData']['time'];
    $type =  $_GPC['mtype'];
    $limit_start = $time + 1;
    $condition .= " And bj_id = '{$bj_id}' AND createdate = '{$createdate}'";	
    $nowdate = strtotime(date("Y-m-d"));
    if($type == 1){
        $checklist =pdo_fetchall("SELECT * FROM " . GetTableName('morningcheck') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition GROUP BY sid ORDER BY createdate DESC LIMIT {$limit_start},10");
        foreach ($checklist as $key => $value) {
            $stu = pdo_fetch("SELECT icon,s_name FROM ".GetTableName('students')." WHERE id = '{$value['sid']}' ");
            $checklist[$key]['icon'] = $stu['icon'];
            $checklist[$key]['s_name'] = $stu['s_name'];
            $checklist[$key]['location'] = $key + $limit_start;
        }
    }else{
        //获取已检查的学生id
        $checkstu =pdo_fetchall("SELECT sid FROM " . GetTableName('morningcheck') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition GROUP BY sid ");
        $stustr = arrayToString($checkstu);
        //未检查的学生列表
        $checklist =pdo_fetchall("SELECT s_name,icon,mobile FROM " . GetTableName('students') . " WHERE schoolid = '{$schoolid}' AND bj_id = '{$bj_id}' AND not FIND_IN_SET(id,'{$stustr}') GROUP BY sid ORDER BY createdate DESC LIMIT {$limit_start},10");
        foreach ($checklist as $key => $value) {
            $checklist[$key]['location'] = $key + $limit_start;
        }
    }
    include $this->template('comtool/mclist');
    exit;
}

if(!empty($userid['id']) && $userid['sid'] == 0){
    include $this->template(''.$school['style3'].'/mclist');
}else{
    session_destroy();
    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
    header("location:$stopurl");
}     
   
