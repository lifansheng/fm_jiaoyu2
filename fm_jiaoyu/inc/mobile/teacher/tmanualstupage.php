<?php
/*
 * @Discription:
 * @Author: Hannibal·Lee
 * @Date: 2019-10-17 15:14:49
 * @LastEditTime: 2020-04-01 13:59:55
 */
global $_W, $_GPC;

$weid = $_W['uniacid'];
$schoolid = intval($_GPC['schoolid']);
$openid = $_W['openid'];
$schooltype = $_W['schooltype'];
$userss = intval($_GPC['userid']);
mload()->model('photo');
$obid = 1;
//老师登陆
$userid = pdo_fetch("SELECT id FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0));

$item = pdo_fetch("SELECT * FROM " . tablename ( 'mc_members' ) . " where uniacid=:uniacid AND uid=:uid ", array(':uid' => $it['uid'], ':uniacid' => $weid));  $userinfo = iunserializer($it['userinfo']);
$school = pdo_fetch("SELECT spic,style3,title,qroce,logo FROM " . tablename($this->table_index) . " where id=:id", array(':id' => $schoolid));
$student = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where :schoolid = schoolid AND id=:id", array(':schoolid' => $schoolid, ':id' => $_GPC['sid']));
$class = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where :schoolid = schoolid AND sid=:sid", array(':schoolid' => $schoolid, ':sid' => $student['bj_id']));
$bj_id = $student['bj_id'];
// 班级相册分类
$bjphototype = GetPhotoType($weid,$schoolid,$bj_id,2,'',0,true);
// 班级视频分类
$bjvideotype = GetPhotoType($weid,$schoolid,$bj_id,2,'',1,true);
// 个人相册
$grphototype = GetPhotoType($weid,$schoolid,$bj_id,1,$student['id'],0,true);
// 个人视频
$grvideotype = GetPhotoType($weid,$schoolid,$bj_id,1,$student['id'],1,true);
// 获取个人相册
$condition .= " And bj_id = '{$bj_id}'";	
$bjstu =pdo_fetchall("SELECT * FROM " . tablename($this->table_students) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' And bj_id = '{$bj_id}' ");
foreach ($bjstu as $key => $value) {
	$stotal = pdo_fetchcolumn("SELECT count(*) FROM " . tablename($this->table_media) . " where schoolid = '{$schoolid}' And type = 1 And sid = '{$value['id']}' AND is_video = 0 ");	 
	$bjstu[$key]['sname'] = $value['s_name'];	
	$bjstu[$key]['total'] = $stotal;
}
// 获取个人相册
$bjstuVideo =pdo_fetchall("SELECT * FROM " . tablename($this->table_students) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' And bj_id = '{$bj_id}' ");
foreach ($bjstuVideo as $key => $value) {
	$stotal = pdo_fetchcolumn("SELECT count(*) FROM " . tablename($this->table_media) . " where schoolid = '{$schoolid}' And type = 1 And sid = '{$value['id']}' AND is_video = 1 ");	 
	$bjstuVideo[$key]['sname'] = $value['s_name'];	
	$bjstuVideo[$key]['total'] = $stotal;
}

$this->checkobjiect($schoolid, $student['id'], $obid);
include $this->template(''.$school['style3'].'/manual/tmanualstupage');

?>