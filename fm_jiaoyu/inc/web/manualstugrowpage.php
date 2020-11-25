<?php
/*
 * @Discription:  
 * @Author: Hannibal·Lee
 * @Date: 2020-03-17 16:39:35
 * @LastEditTime: 2020-05-29 12:01:33
 */

global $_GPC, $_W;
$weid              = $_W['uniacid'];
$schoolid          = intval($_GPC['schoolid']);
if($_GPC['op'] == 'display'){
$logo = pdo_fetch("SELECT * FROM ".GetTableName('index')." WHERE id = '{$schoolid}' ");
$manual = pdo_fetch("SELECT title FROM ".GetTableName('growupfile')." WHERE id = '{$_GPC['id']}' ")['title'];
$sname = pdo_fetch("SELECT s_name FROM ".GetTableName('students')." WHERE id = '{$_GPC['sid']}' ")['s_name'];
$bjname = pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE sid = '{$_GPC['bjid']}' ")['sname'];
$title = $manual.' - '.$bjname.' - '.$sname;
include $this->template('web/manualstugrowpage');
}elseif($_GPC['op'] == 'GetFirstPage'){
    mload()->model('manual');
    $item = GetStuManualPage($schoolid, $_GPC['sid'], $_GPC['id']);
    $result['data'] = $item;
    die(json_encode($result));
}