<?php
    global $_W, $_GPC;
    load()->func('tpl');
    $weid = $_W['uniacid'];
    $schoolid = intval($_GPC['schoolid']);
    $openid = $_W['openid'];

    $it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where id = :id ", array(':id' => $_SESSION['user']));
    if(empty($it)){
        session_destroy();
        $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
        header("location:$stopurl");
        exit;
    }
    $list = pdo_fetchall("SELECT l.*,t.tname,t.mobile FROM " . GetTableName('lxvis') . " as l LEFT JOIN " . GetTableName('teachers') . " as t ON t.id = l.tid where :sid = l.sid AND :schoolid = l.schoolid ORDER BY l.id DESC", array(':schoolid' => $schoolid, ':sid' => $it['sid']));
   
    $school = pdo_fetch("SELECT id,title,style2,headcolor FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id", array(':weid' => $weid, ':id' => $schoolid));

    include $this->template(''.$school['style2'].'/lxsvislist');       
?>