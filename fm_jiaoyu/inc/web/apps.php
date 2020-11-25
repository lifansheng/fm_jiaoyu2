<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_GPC, $_W;

$weid = $_W['uniacid'];
$action = 'apps';
$this1 = 'no1';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'],$action);
$schoolid = intval($_GPC['schoolid']);
$is_start = !empty($_GPC['is_start'])?$_GPC['is_start'] : -1 ;
$logo = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where id = :id ", array(':id' => $schoolid));
$operation         = !empty($_GPC['op']) ? $_GPC['op'] : 'display';

if($operation == 'display'){
	$bigdata = pdo_fetch("SELECT is_bigdata FROM " . GetTableName('schoolset') . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' ");
	$check_app = pdo_fetch("SELECT * FROM " . GetTableName('app') . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' ");
}



include $this->template('web/apps');

?>