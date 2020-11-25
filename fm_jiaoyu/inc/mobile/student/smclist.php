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
$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where id = :id ", array(':id' => $_SESSION['user']));
$student = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where weid = :weid AND id = :id", array(':weid' => $_W ['uniacid'], ':id' => $it['sid']));
$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ORDER BY ssort DESC", array(':weid' => $weid, ':id' => $schoolid));
$operation = $_GPC['op'] ? $_GPC['op'] : 'display';	

$bj_id = intval($student['bj_id']);
$condition .= " And sid = '{$it['sid']}' ";	
if($operation == 'display'){
    $checklist =pdo_fetchall("SELECT createdate,id,tiwen,sid,id FROM " . GetTableName('morningcheck') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition ORDER BY createdate DESC LIMIT 0,10");
}elseif($operation == 'scroll_more'){
    $time = $_GPC['LiData']['time'];
    $type =  $_GPC['mtype'];
    $limit_start = $time + 1;
    $condition .= " And sid = '{$it['sid']}' ";	
    $nowdate = strtotime(date("Y-m-d"));
    $checklist =pdo_fetchall("SELECT createdate,id,tiwen,sid,id FROM " . GetTableName('morningcheck') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition ORDER BY createdate DESC LIMIT {$limit_start},10");
    foreach ($checklist as $key => $value) {
        $checklist[$key]['location'] = $key + $limit_start;
    }
    include $this->template('comtool/smclist');
    exit;
}

if(!empty($it['sid'])){
    include $this->template(''.$school['style2'].'/smclist');
}else{
    session_destroy();
    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
    header("location:$stopurl");
}     
   
