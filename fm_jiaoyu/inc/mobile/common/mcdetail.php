<?php
/*
 * @Discription:  
 * @Author: Hannibal·Lee
 * @Date: 2020-02-14 16:42:10
 * @LastEditTime : 2020-02-14 17:36:38
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

$school = pdo_fetch("SELECT title FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id", array(':weid' => $weid, ':id' => $schoolid));
$mcdata = pdo_fetch("SELECT * FROM ".GetTableName('morningcheck')." WHERE id = '{$id}' ");
$student = pdo_fetch("SELECT s_name,icon FROM ".GetTableName('students')." WHERE id = '{$mcdata['sid']}' ");
$bj = pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE sid = '{$mcdata['bj_id']}' ");
include $this->template('common/mcdetail');

?>