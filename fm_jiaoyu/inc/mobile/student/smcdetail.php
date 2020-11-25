<?php
/*
 * @Discription:  
 * @Author: Hannibal·Lee
 * @Date: 2020-02-14 16:42:10
 * @LastEditTime: 2020-08-22 14:53:07
 */
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_W, $_GPC;
$weid = $_W['uniacid'];
$openid = $_W['openid'];
$schoolid = intval($_GPC['schoolid']);
$id = intval($_GPC['id']);
$operation = $_GPC['op'] ? $_GPC['op'] : 'display';
if (!empty($_GPC['userid'])){
    $_SESSION['user'] = $_GPC['userid'];
}
$school = pdo_fetch("SELECT title,spic FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id", array(':weid' => $weid, ':id' => $schoolid));
$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where id = :id ", array(':id' => $_SESSION['user']));
if(!empty($it)){
    $student = pdo_fetch("SELECT s_name,icon,bj_id FROM ".GetTableName('students')." WHERE id = '{$it['sid']}' ");
    $bj = pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE sid = '{$student['bj_id']}' ");
    if($operation == 'display'){
        $first = pdo_fetch("SELECT createdate FROM ".GetTableName('morningcheck')." WHERE id = '{$id}'");
    }elseif($operation == 'GetStuMcData'){
        $sid = $_GPC['sid'];
        mload()->model('mc');
        $return_data = GetStuMcData($sid,'');
    }elseif($operation == 'getMcData'){
        $nowTime = $_GPC['date'] ? strtotime($_GPC['date']) : strtotime(date("Y-m-d",time()));
        $mcdata = pdo_fetch("SELECT * FROM ".GetTableName('morningcheck')." WHERE sid = '{$it['sid']}' AND createdate = '{$nowTime}'");
        include $this->template('comtool/mccominfo');
        die;
    }
    // include $this->template('students/smcdetail');
    include $this->template('students/smcdetail_new');
}else{
    session_destroy();
    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
    header("location:$stopurl");
}

?>