<?php
/*
* @Discription:  
* @Author: Hannibal·Lee
* @Date: 2020-02-10 14:24:11
 * @LastEditTime : 2020-02-11 16:11:57
*/
global $_W, $_GPC;
$weid = $_W['uniacid'];
$schoolid = intval($_GPC['schoolid']);
$openid = $_W['openid'];
$bj_id = $_GPC['bj_id'];
//查询是否用户登录		
//登陆身份
if($_GPC['type'] == 1){
	//老师登陆
	$userid = pdo_fetch("SELECT id FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0));
}else{
	//学生登陆
	$userid = pdo_fetch("SELECT id FROM " . tablename($this->table_user) . " where :sid = sid", array(':sid' => $_GPC['sid'] ));
}
//获取学生名字
$student = pdo_fetch("SELECT s_name FROM " . tablename($this->table_students) . " where id=:id", array( ':id' => $_GPC['sid']));
//获取个人相册分类
mload()->model('photo');
if($_W['schooltype']){
	$phototype = GetPxPhotoType($weid,$schoolid,$_GPC['kcid'],1,$_GPC['sid']);
}else{
	$phototype = GetPhotoType($weid,$schoolid,$_GPC['bj_id'],1,$_GPC['sid']);
}
$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ORDER BY ssort DESC", array(':weid' => $weid, ':id' => $schoolid));
if(!empty($userid['id'])){
	include $this->template('common/photolist');
}else{
	session_destroy();
	$stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
	header("location:$stopurl");
}     
