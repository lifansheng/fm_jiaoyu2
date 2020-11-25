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
        // 查询老师
		$teachcers = pdo_fetchall("SELECT id,tname,mobile FROM " . tablename($this->table_teachers) . " where schoolid = '{$schoolid}' and weid='$weid' ORDER BY CONVERT(tname USING gbk) ASC");
		// 查询学校
		$school = pdo_fetch("SELECT style1,title FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id", array(':weid' => $weid, ':id' => $schoolid));
		// 权限判断
		$oauthurl = getoauthurl();	
		// 查询事由
		$visireason = pdo_fetchall("SELECT sname,sid FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'visireason' And schoolid = {$schoolid} ORDER BY sid ASC, ssort DESC");
        //申请记录
       
		include $this->template(''.$school['style1'].'/visitors');       
?>