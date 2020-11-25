<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
        global $_W, $_GPC;
		load()->func('tpl');
        $weid = $_W['uniacid'];
		$schoolid = intval($_GPC['schoolid']);
		$openid = $_W['openid'];

		$list = pdo_fetchall("SELECT * FROM " . tablename($this->table_visitors) . " where :openid = openid And :weid = weid And :schoolid = schoolid ORDER BY createtime DESC", array(
				':weid' => $weid,
				':schoolid' => $schoolid,
				':openid' => $openid,
			));
		foreach($list as $k => $v){
			$res = pdo_fetch("SELECT tname,mobile FROM " . tablename($this->table_teachers) . " WHERE :schoolid = schoolid And :id = id", array(
			':schoolid' => $_GPC['schoolid'],
			':id' => $v['t_id'],
			));
			$visireason = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'visireason' And schoolid = {$schoolid} And sid = {$v['sy_id']} ORDER BY sid ASC, ssort DESC");
			$list[$k]['sname'] = $visireason['sname'];
			$list[$k]['tname'] = $res['tname'];
			$list[$k]['mobile'] = $res['mobile'];
		}
		$school = pdo_fetch("SELECT id,title,style1,headcolor FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id", array(':weid' => $weid, ':id' => $schoolid));

		include $this->template(''.$school['style1'].'/visitorslist');       
?>