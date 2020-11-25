<?php
global $_W, $_GPC;
$weid = $this->weid;
$from_user = $this->_fromuser;
$schoolid = intval($_GPC['schoolid']);
$id = intval($_GPC['id']);
$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $_W['uniacid'], ':id' => $schoolid));
//当前条
$dataTemp = pdo_fetch("SELECT content,sid FROM " . GetTableName('mcreportlist') . " WHERE id = :id", array(':id' => $id));
//上一条
$prevdataTemp = pdo_fetch("SELECT content,sid FROM " . GetTableName('mcreportlist') . " WHERE id < :id AND sid = '{$dataTemp['sid']}' ORDER BY id DESC", array(':id' => $id));
$stuinfo = pdo_fetch("SELECT icon,s_name FROM ".GetTableName('students')." WHERE weid = '{$weid}' and id = '{$dataTemp['sid']}' ");

if(empty($stuinfo['icon'])){
    $stuinfo['icon'] = $school['spic'];
}
$data = json_decode($dataTemp['content'],true);
$prevdata = json_decode($prevdataTemp['content'],true);
include $this->template(''.$school['style1'].'/mcreportinfo');
?>