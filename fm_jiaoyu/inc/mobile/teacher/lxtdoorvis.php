<?php
    global $_W, $_GPC;
    $weid = $this->weid;
    $from_user = $this->_fromuser;
    $schoolid = intval($_GPC['schoolid']);
    $openid = $_W['openid'];
    $school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ORDER BY ssort DESC", array(':weid' => $weid, ':id' => $schoolid));
    //查询是否用户登录		
    $userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
    $it = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " where weid = :weid AND id=:id ORDER BY id DESC", array(':weid' => $weid, ':id' => $userid['tid']));	
    if(!empty($it)){
        $list = pdo_fetchAll("SELECT s.s_name,l.starttime,l.endtime,l.content,l.status,l.id,t.tname,l.thumb FROM " . GetTableName('lxvis') . " as l LEFT JOIN " . GetTableName('students') . " as s ON s.id = l.sid LEFT JOIN " . GetTableName('teachers') . " as t ON t.id = l.tid WHERE :schoolid = l.schoolid And :weid = l.weid AND l.status != 0 AND l.status != 2 ORDER BY l.id DESC", array(':weid' => $weid, ':schoolid' => $schoolid));
        include $this->template(''.$school['style3'].'/lxtdoorvis');	   
    }else{
        session_destroy();
        $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
        header("location:$stopurl");
    } 	
    