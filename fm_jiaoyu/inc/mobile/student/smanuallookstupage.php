<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_W, $_GPC;
$weid = $_W['uniacid'];
$openid = $_W['openid'];	
$schoolid = $_GPC['schoolid'];
$op = $_GPC['op'] ? $_GPC['op'] : 'display';
$isOver = $_GPC['isOver'];
$school = pdo_fetch("SELECT spic,style2,title,qroce,logo FROM " . tablename($this->table_index) . " where id=:id", array(':id' => $schoolid));
$student = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where :schoolid = schoolid AND id=:id", array(':schoolid' => $schoolid, ':id' => $_GPC['sid']));
$class = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " where :schoolid = schoolid AND sid=:sid", array(':schoolid' => $schoolid, ':sid' => $student['bj_id']));
//获取背景音乐
$bgMusic = pdo_fetch("SELECT audio FROM ".GetTableName('growupfile')." WHERE schoolid = '{$schoolid}' AND id = '{$_GPC['id']}' ");
$IsShowSave = pdo_fetch("SELECT pdffile FROM ".GetTableName('growuppage')." WHERE schoolid = '{$schoolid}' AND sid = '{$_GPC['sid']}' AND gid = '{$_GPC['id']}' ")['pdffile'];
if($op == 'display'){
    include $this->template(''.$school['style2'].'/manual/smanuallookstupage');
}elseif($op == 'share'){
    include $this->template(''.$school['style2'].'/manual/smanualsharelookstupage');
}