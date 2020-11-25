<?php
    global $_W, $_GPC;
    $weid = $_W['uniacid'];
    $schoolid = intval($_GPC['schoolid']);
    $openid = $_W['openid'];
    $id = $_GPC['id'];
    $school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ORDER BY ssort DESC", array(':weid' => $weid, ':id' => $schoolid));

    $item = pdo_fetch("SELECT lo.pic,lo.pic2,lo.type,lo.signtime,t.tname,s.s_name,u.realname,u.pard,c.name,l.content,l.status,l.thumb FROM " . GetTableName('lxvis') . " as l LEFT JOIN " . GetTableName('lxvislog') . " as lo ON lo.lxvisid = l.id LEFT JOIN " . GetTableName('teachers') . " as t ON t.id = l.tid LEFT JOIN " . GetTableName('students') . " as s ON s.id = l.sid LEFT JOIN " . GetTableName('user') . " as u ON l.userid = u.id LEFT JOIN " . GetTableName('checkmac') . " as c ON c.id = lo.macid WHERE l.schoolid = '{$schoolid}' AND l.id = '{$id}' ORDER BY lo.id DESC");
    include $this->template($school['style3'].'/lxtvislog');
?>