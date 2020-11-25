<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_W, $_GPC;
$weid = $_W ['uniacid'];
$schoolid = intval($_GPC['schoolid']);
$openid = $_W['openid'];
$logid = trim($_GPC['logid']);
$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $userid['id']));
$school = pdo_fetch("SELECT title,logo,style2,style3,spic,is_showad FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
if(!empty($it)){
    $log = pdo_fetch("SELECT * FROM " . tablename($this->table_checklog) . " where id = :id AND schoolid = :schoolid ", array(':id' => $logid, ':schoolid' => $schoolid));
    $student = pdo_fetch("SELECT icon,s_name FROM " . tablename($this->table_students) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $log['sid']));

    pdo_update($this->table_checklog, array('isread' => 2), array('id' => $logid));
    $classid = $log['bj_id'];
    $class= pdo_fetch("SELECT * FROM " . tablename($this->table_classify) . " where sid = :id AND schoolid = :schoolid ", array(':id' => $classid, ':schoolid' => $schoolid));
    $mac = pdo_fetch("SELECT name FROM " . tablename($this->table_checkmac) . " WHERE id = {$log['macid']} ");
    if (empty($_W['setting']['remote']['type'])) {
        $urls = "http://severwm.oss-cn-shenzhen.aliyuncs.com/";
    } else {
        $urls = "http://severwm.oss-cn-shenzhen.aliyuncs.com/";
    }

    include $this->template(''.$school['style3'].'/tchecklogdetail');
}else{
    session_destroy();
    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
    header("location:$stopurl");
    exit;
}
?>