<?php
/**
 * By 高贵血迹
 */
global $_W, $_GPC;
$weid = $_W['uniacid'];

session_start();
$state = pdo_fetch("SELECT * FROM ".tablename('uni_account_users')." WHERE uid = :uid And uniacid = :uniacid", array(':uid' => $_W['uid'],':uniacid' => $_W['uniacid']));
$_W['role']  = $state['role'];
if($_GPC['from'] == 'depend'){
    $_SESSION["stand_uid"] = $_GPC['uid'];
    $_W['uid'] =  $_SESSION["stand_uid"];
}
$_W['uid'] = $_W['uid']?$_W['uid']:$_SESSION["stand_uid"];


if($_GPC['from'] == 'depend'){
    $_SESSION["tid"] = $_W['tid'];
}
?>