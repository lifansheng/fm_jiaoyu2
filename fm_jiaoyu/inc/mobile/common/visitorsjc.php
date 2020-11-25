<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
        global $_W, $_GPC;
		$weid = $_W['uniacid'];
		$schoolid = intval($_GPC['schoolid']);
		$openid = $_W['openid'];
		$id = $_GPC['id'];
        
		$item = pdo_fetch("SELECT * FROM " . tablename($this->table_visitors) . " where :id = id", array(':id' => $id));
		$visireason = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'visireason' And schoolid = {$schoolid} And sid = {$item['sid']} ORDER BY sid ASC, ssort DESC");
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id", array(':weid' => $weid, ':id' => $schoolid));
		$teacher = pdo_fetch("SELECT tname,mobile FROM " . tablename($this->table_teachers) . " WHERE :schoolid = schoolid And :id = id", array(
		':schoolid' => $_GPC['schoolid'],
		':id' => $item['t_id'],
		));
		include $this->template(''.$school['style1'].'/visitorsjc');
?>