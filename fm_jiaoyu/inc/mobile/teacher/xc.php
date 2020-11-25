<?php
/*
* @Discription:  
* @Author: Hannibal·Lee
* @Date: 2017-11-14 15:57:08
 * @LastEditTime : 2020-02-12 10:25:26
*/
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_W, $_GPC;
$weid = $this->weid;
$from_user = $this->_fromuser;
$schoolid = intval($_GPC['schoolid']);
$scsid = intval($_GPC['sid']);
$type = intval($_GPC['type']);
$openid = $_W['openid'];
$bj_id = intval($_GPC['bj_id']);
$kcid = intval($_GPC['kcid']);
$operation = $_GPC['op'] ? $_GPC['op'] : 'display';
if($_GPC['istea']){
	//老师登陆
	$userid = pdo_fetch("SELECT id FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0));
}else{
	//学生登陆
	$userid = pdo_fetch("SELECT id FROM " . tablename($this->table_user) . " where :sid = sid", array(':sid' => $_GPC['sid'] ));
}

$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ORDER BY id DESC", array(':weid' => $weid, ':id' => $userid['id']));

$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ORDER BY ssort DESC", array(':weid' => $weid, ':id' => $schoolid));

$name = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where weid = :weid AND :schoolid = schoolid AND id=:id", array(':weid' => $weid, ':schoolid' => $schoolid, ':id' => $scsid));

$last = pdo_fetch("SELECT * FROM " . tablename($this->table_media) . " where weid = :weid AND :schoolid = schoolid AND sid=:sid AND ctype = '{$_GPC['ctype']}' ORDER BY createtime DESC LIMIT 0,1 ", array(':weid' => $weid, ':schoolid' => $schoolid, ':sid' => $scsid));

if($_W['schooltype']){
	$total = pdo_fetchcolumn("SELECT count(*) FROM " . tablename($this->table_media) . " where schoolid = '{$schoolid}' And type = 1 And sid = '{$scsid}' AND kcid = '{$kcid}' AND ctype = '{$_GPC['ctype']}' ");	
}else{
	$total = pdo_fetchcolumn("SELECT count(*) FROM " . tablename($this->table_media) . " where schoolid = '{$schoolid}' And type = 1 And sid = '{$scsid}' AND bj_id1 = '{$bj_id}' AND ctype = '{$_GPC['ctype']}' ");	
}
// if(keep_MC()){
	if($operation == 'display'){
		if($_GPC['ctype'] != '0' && is_numeric($_GPC['ctype'])){
			//判断自定义分类是否允许上传
			$allowup = pdo_fetch("SELECT sid FROM ".GetTableName('classify')." WHERE sid = '{$_GPC['ctype']}' AND is_upload = 1 ");
			if($allowup){
				$isallow = true;
			}else{
				$isallow = false;
			}
		}else{
			if(!$_GPC['istea'] && $_GPC['type'] == 2){
				$bjallow = pdo_fetch("SELECT id FROM ".GetTableName('schoolset')." WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND isallowup = 1 ");
				// var_dump($bjallow);die;
				if($bjallow){
					$isallow = true;
				}else{
					$isallow = false;
				}
			}else{
				$isallow = true;
			}
		}
		if($_W['schooltype']){
			if (!empty($_GPC['kcid'])) {
				$condition .= " And kcid = '{$_GPC['kcid']}'";
			}
			if (!empty($scsid) && $_GPC['type'] == 1) {
				$condition .= " And sid= '{$scsid}'";
			}
			if($_GPC['ctype'] === '0'){
				$condition .= " And (ctype = '0' || ctype = '')";
			}else{
				$condition .= " And ctype = '{$_GPC['ctype']}'";
			}
		}else{
			if($_GPC['ctype'] == 'teaactivity'){
				$groupactivity = pdo_fetchAll("SELECT title,id FROM " . tablename($this->table_groupactivity) . " WHERE schoolid = '{$schoolid}' AND FIND_IN_SET($bj_id,bjarray)");
			}
			if (!empty($_GPC['bj_id'])) {
				$condition .= " And bj_id1 = '{$_GPC['bj_id']}'";
			}
			if (!empty($scsid) && $_GPC['type'] == 1) {
				$condition .= " And sid= '{$scsid}'";
			}
			if($_GPC['ctype'] === '0'){
				$condition .= " And (ctype = '0' || ctype = '')";
			}else{
				$condition .= " And ctype = '{$_GPC['ctype']}'";
			}
		}
		$condition .= " And type = '{$_GPC['type']}'";

		$xclist =pdo_fetchall("SELECT * FROM " . tablename($this->table_media) . " WHERE weid = {$_W['uniacid']} $condition ORDER BY createtime DESC LIMIT 0,30 ");
		foreach ($xclist as $key => $value) {
			$students = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where weid = :weid AND :schoolid = schoolid AND id=:id", array(
				':weid' => $weid,
				':schoolid' => $schoolid,
				':id' => $value['sid']
				));
			$stotal = pdo_fetchcolumn("SELECT count(*) FROM " . tablename($this->table_media) . " where schoolid = '{$schoolid}' And type = 1 And sid = '{$value['sid']}' ");	  
			$xclist[$key]['image'] = tomedia($value['picurl']);
		}
	}elseif($operation == 'scroll_more'){
		$time = $_GPC['LiData']['time'];
		$type = $_GPC['LiData']['type'];
		$limit_start = $time + 1;

		if($_W['schooltype']){
			if (!empty($_GPC['kcid'])) {
				$condition .= " And kcid = '{$_GPC['kcid']}'";
			}
			if (!empty($_GPC['sid']) && $_GPC['type'] == 1) {
				$condition .= " And sid= '{$_GPC['sid']}'";
			}
			if($_GPC['ctype'] === '0'){
				$condition .= " And (ctype = '0' || ctype = '')";
			}else{
				$condition .= " And ctype = '{$_GPC['ctype']}'";
			}
		}else{
			if($_GPC['ctype'] == 'teaactivity'){
				$groupactivity = pdo_fetchAll("SELECT title,id FROM " . tablename($this->table_groupactivity) . " WHERE schoolid = '{$schoolid}' AND FIND_IN_SET($bj_id,bjarray)");
			}
			if (!empty($_GPC['bj_id'])) {
				$condition .= " And bj_id1 = '{$_GPC['bj_id']}'";
			}
			if (!empty($_GPC['sid']) && $_GPC['type'] == 1) {
				$condition .= " And sid= '{$_GPC['sid']}'";
			}
			if($_GPC['ctype'] === '0'){
				$condition .= " And (ctype = '0' || ctype = '')";
			}else{
				$condition .= " And ctype = '{$_GPC['ctype']}'";
			}
		}
		$condition .= " And type = '{$_GPC['selecttype']}'";

		$xclist = pdo_fetchall("SELECT * FROM ".GetTableName('media')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' $condition ORDER BY createtime DESC LIMIT {$limit_start},30 ");
		foreach ($xclist as $key => $value) {
			$students = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where weid = :weid AND :schoolid = schoolid AND id=:id", array(
				':weid' => $weid,
				':schoolid' => $schoolid,
				':id' => $value['sid']
				));
			$stotal = pdo_fetchcolumn("SELECT count(*) FROM " . tablename($this->table_media) . " where schoolid = '{$schoolid}' And type = 1 And sid = '{$value['sid']}' ");	  
			$xclist[$key]['type'] = $type;
			$xclist[$key]['ctype'] = $ctype;
			$xclist[$key]['image'] = tomedia($value['picurl']);
			$xclist[$key]['location'] = $key + $limit_start;
		}
		include $this->template('comtool/photolist');
		exit;
	}
// }else{
// 	if ($_GPC['getalist']) {
// 		$pageSize = intval($_GPC['pageSize']);
// 		$nowPage = intval($_GPC['nowPage']);
// 		$item_per_page = empty($_GPC['pageSize']) ? 10 : intval($_GPC['pageSize']);
// 		$page_number = max(1, intval($_GPC['nowPage']));  
// 		if (!empty($_GPC['bj_id'])) {
// 			$condition .= " And (bj_id1 = '{$_GPC['bj_id']}' or bj_id2 = '{$_GPC['bj_id']}' or bj_id3 = '{$_GPC['bj_id']}')";
// 		}
// 		if(!is_numeric($page_number)){  
// 				header('HTTP/1.1 500 Invalid page number!');  
// 			exit();  
// 		}
// 		$position = ($page_number-1) * $item_per_page;
// 		$condition .= " And schoolid=" .$schoolid;
// 		if (!empty($scsid)) {
// 			$condition .= " And sid= '{$scsid}'";
// 		}
// 		$condition .= " And type= '{$type}'";
// 		$xclist =pdo_fetchall("SELECT * FROM " . tablename($this->table_media) . " WHERE weid = {$_W['uniacid']} $condition ORDER BY createtime DESC, isfm DESC LIMIT " . $position . ',' . $item_per_page );
// 		foreach ($xclist as $key => $value) {
// 			$students = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where weid = :weid AND :schoolid = schoolid AND id=:id", array(
// 				':weid' => $weid,
// 				':schoolid' => $schoolid,
// 				':id' => $value['sid']
// 				));
// 			$stotal = pdo_fetchcolumn("SELECT count(*) FROM " . tablename($this->table_media) . " where schoolid = '{$schoolid}' And type = 1 And sid = '{$value['sid']}' ");	  
// 			$xclist[$key]['id'] = $value['id'];	
// 			$xclist[$key]['date'] = date('Y-m-d', $value['createtime']);	
// 			$xclist[$key]['tag'] = $students['s_name'];	
// 			$xclist[$key]['wlzytype'] = $value['sid'];
// 			$xclist[$key]['total'] = $stotal;
// 			$xclist[$key]['tagid'] = $value['uid'];
// 			$xclist[$key]['image'] = tomedia($value['picurl']);
// 		}
// 		$datas = array(
// 			'ret' => array('code' => '200','msg' => 'success'),
// 			'data' => array(
// 						'imageList' => $xclist,
// 							)
// 					);
					
// 			echo json_encode($datas);
// 			exit;
// 	}
// }
if($_GPC['type'] == '0'){
	$title = $school['title'];
}elseif($_GPC['type'] == 1){
	$title = $name['s_name'].'的相册';
}else{
	mload()->model('photo');
	if($_GPC['ctype'] === '0' ){
		$title = $school['title'];
	}else{
		// var_dump($_GPC['ctype']);die;
		if(is_numeric($_GPC['ctype'])){
			$title = $school['title'];
		}else{
			$title = GetphotoTypeName($_GPC['ctype']);
		}
	}
}
if(!empty($userid['id'])){
	// if(keep_MC()){
		include $this->template(''.$school['style1'].'/newxc');
	// }else{
		// include $this->template(''.$school['style3'].'/xc');
	// }
}else{
	session_destroy();
	$stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
	header("location:$stopurl");
}        
