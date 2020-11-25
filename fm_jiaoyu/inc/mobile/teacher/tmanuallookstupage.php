<?php
/*
 * @Discription:
 * @Author: Hannibal·Lee
 * @Date: 2019-10-17 15:14:49
 * @LastEditTime: 2020-04-01 13:59:55
 */
global $_W, $_GPC;

$weid = $_W['uniacid'];
$schoolid = intval($_GPC['schoolid']);
$openid = $_W['openid'];
$schooltype = $_W['schooltype'];
$isOver = $_GPC['isOver'] ? $_GPC['isOver'] : 1;
$userss = intval($_GPC['userid']);
mload()->model('photo');
$obid = 1;
//老师登陆
$userid = pdo_fetch("SELECT id FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0));
$school = pdo_fetch("SELECT spic,style3,title,qroce,logo FROM " . tablename($this->table_index) . " where id=:id", array(':id' => $schoolid));
$student = pdo_fetch("SELECT s_name,bj_id FROM " . tablename($this->table_students) . " where :schoolid = schoolid AND id=:id", array(':schoolid' => $schoolid, ':id' => $_GPC['sid']));
$class = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where :schoolid = schoolid AND sid=:sid", array(':schoolid' => $schoolid, ':sid' => $student['bj_id']));
//获取背景音乐
$bgMusic = pdo_fetch("SELECT audio FROM ".GetTableName('growupfile')." WHERE schoolid = '{$schoolid}' AND id = '{$_GPC['id']}' ");
$IsShowSave = pdo_fetch("SELECT pdffile FROM ".GetTableName('growuppage')." WHERE schoolid = '{$schoolid}' AND sid = '{$_GPC['sid']}' AND gid = '{$_GPC['id']}' ")['pdffile'];
include $this->template(''.$school['style3'].'/manual/tmanuallookstupage');

?>