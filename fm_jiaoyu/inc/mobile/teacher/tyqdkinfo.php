<?php
/*
 * @Discription:  
 * @Author: Hannibal·Lee
 * @Date: 2020-02-14 14:09:20
 * @LastEditTime: 2020-02-20 10:51:01
 */

global $_W, $_GPC;
$weid = $_W['uniacid'];
$schoolid = intval($_GPC['schoolid']);
$openid = $_W['openid'];
$id = $_GPC['id'];     
//查询是否用户登录		
$userid = pdo_fetch("SELECT * FROM " . GetTableName('user') . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
$it = pdo_fetch("SELECT * FROM " . GetTableName('user'). " where weid = :weid AND id=:id ORDER BY id DESC", array(':weid' => $weid, ':id' => $userid['id']));	
$tid_global = $it['tid'];

//当前学生打卡情况
$yqdk = pdo_fetch("SELECT * FROM ".GetTableName('yqdk')." WHERE id = '{$id}' ");
$yqdk['content'] = unserialize($yqdk['content']);
$student = pdo_fetch("SELECT icon,s_name,bj_id FROM " . tablename($this->table_students) . " where id = :id", array(':id' => $yqdk['sid']));
$bj = pdo_fetch("SELECT sname,sid FROM " . tablename($this->table_classify) . " where sid = :sid", array(':sid' => $yqdk['bj_id']));

$school = pdo_fetch("SELECT style3 FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ORDER BY ssort DESC", array(':weid' => $weid, ':id' => $schoolid));

if(!empty($userid['id'])){
    include $this->template(''.$school['style3'].'/tyqdkinfo');
}else{
    session_destroy();
    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
    header("location:$stopurl");
}     
   
