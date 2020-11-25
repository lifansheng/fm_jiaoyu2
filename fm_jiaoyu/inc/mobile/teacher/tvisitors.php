<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
        global $_W, $_GPC;
        $weid = $this->weid;
        $from_user = $this->_fromuser;
		$schoolid = intval($_GPC['schoolid']);
		$openid = $_W['openid'];
        $school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ORDER BY ssort DESC", array(':weid' => $weid, ':id' => $schoolid));
        //查询是否用户登录		
		$teacher = pdo_fetch("SELECT id FROM " . tablename($this->table_teachers) . " where :schoolid = schoolid And :weid = weid And :openid = openid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid));
		$list = pdo_fetchAll("SELECT * FROM " . tablename($this->table_visitors) . " where :schoolid = schoolid And :weid = weid And :t_id = t_id ORDER BY id DESC", array(':weid' => $weid, ':schoolid' => $schoolid, ':t_id' => $teacher['id']));
		foreach($list as $k => $v){
			$reason = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE sid = :sid", array(':sid' => $v['sid']));
			$list[$k]['sname'] = $reason['sname'];
		}
		include $this->template(''.$school['style3'].'/tvisitors');	   








?>