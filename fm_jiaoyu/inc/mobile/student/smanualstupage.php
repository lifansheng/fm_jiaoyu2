<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_W, $_GPC;
$weid = $_W['uniacid'];
$openid = $_W['openid'];	
$schoolid = $_GPC['schoolid'];
$school = pdo_fetch("SELECT spic,style2,title,qroce,logo FROM " . tablename($this->table_index) . " where id=:id", array(':id' => $schoolid));
$student = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where :schoolid = schoolid AND id=:id", array(':schoolid' => $schoolid, ':id' => $_GPC['sid']));
$class = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " where :schoolid = schoolid AND sid=:sid", array(':schoolid' => $schoolid, ':sid' => $student['bj_id']));
$bj_id = $student['bj_id'];
mload()->model('photo');
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

// $this->checkobjiect($schoolid, $student['id'], $obid);
include $this->template(''.$school['style2'].'/manual/smanualstupage');