<?php
global $_W, $_GPC;
$weid = $_W['uniacid'];
$schoolid = intval($_GPC['schoolid']);
$openid = $_W['openid'];
//查询是否用户登录		
$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ORDER BY id DESC", array(':weid' => $weid, ':id' => $userid['id']));		
$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ORDER BY ssort DESC", array(':weid' => $weid, ':id' => $schoolid));
$teacher = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $_W ['uniacid'], ':id' => $it['tid']));
$tid_global = $it['tid'];				
mload()->model('photo');
mload()->model('tea');
// if(keep_MC()){
	if($_W['schooltype']){
		$teakclist = GetAllClassInfoByTid($schoolid,'',$_W['schooltype'],$tid_global);
		if(!empty($_GPC['kcid'])){
			$kcid = intval($_GPC['kcid']);			
		}else{
			$kcid = $teakclist[0]['sid'];
		}
		$bjphototype = GetPxPhotoType($weid,$schoolid,$kcid,2,'');

		// 获取个人相册
		$condition .= " And kcid = '{$kcid}'";	
		$stuphototype = pdo_fetchall("SELECT * FROM " . tablename($this->table_coursebuy) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition ");
		foreach ($stuphototype as $key => $value) {
			$students = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where weid = :weid AND :schoolid = schoolid AND id=:id", array(
				':weid' => $weid,
				':schoolid' => $schoolid,
				':id' => $value['sid']
				));
			$stotal = pdo_fetchcolumn("SELECT count(*) FROM " . tablename($this->table_media) . " where schoolid = '{$schoolid}' And type = 1 And sid = '{$value['sid']}' $condition");
			$media = pdo_fetch("SELECT picurl FROM " . tablename($this->table_media) . " where schoolid = '{$schoolid}' And weid = '{$weid}' AND sid = '{$value['sid']}'");
			$stuphototype[$key]['sname'] = $students['s_name'];	
			$stuphototype[$key]['picurl'] = $media['picurl'];	
			$stuphototype[$key]['total'] = $stotal;
		}
		//未分配学生和课程和班级
		$nokcmedia = pdo_fetch("SELECT * FROM " . tablename($this->table_media) . " where schoolid = '{$schoolid}' And weid = '{$weid}' AND kcid = 0 AND sid = 0 AND bj_id1 = 0");
		$nokctotal = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename($this->table_media) . " where schoolid = '{$schoolid}' And weid = '{$weid}' AND kcid = 0 AND sid = 0 AND bj_id1 = 0");
	}else{
		$fisrtbj =  pdo_fetch("SELECT bj_id FROM " . tablename($this->table_class) . " where weid = '{$weid}' And schoolid = '{$schoolid}'  And tid = {$it['tid']} ");
		$bjlists = get_mylist($schoolid,$it['tid'],'teacher');	
		if(!empty($_GPC['bj_id'])){
			$bj_id = intval($_GPC['bj_id']);			
		}else{
			if(!$fisrtbj){
				$bj_id = $bjlists[0]['bj_id'];
			}else{
				$bj_id = $fisrtbj['bj_id'];
			}
		}
		$nowbj = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where sid = :sid ", array(':sid' => $bj_id));
		if(is_njzr($teacher['id'])){
			$mynjlist = pdo_fetchall("SELECT * FROM " . tablename($this->table_classify) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And tid = '{$it['tid']}' And type = 'semester' ORDER BY ssort DESC");
			foreach($mynjlist as $key =>$row){
				$mynjlist[$key]['bjlist'] = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And parentid = '{$row['sid']}' And type = 'theclass' ORDER BY sid ASC, ssort DESC");
				foreach($mynjlist[$key]['bjlist'] as $k => $v){
				}
			}
		}else{
			if($teacher['status'] == 2){
				$bjlist = pdo_fetchall("SELECT * FROM " . tablename($this->table_classify) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And type = 'theclass' ORDER BY sid ASC, ssort DESC");
			}			
		}
		//班级圈相册
		$frist = pdo_fetch("SELECT * FROM " . tablename($this->table_media) . " where schoolid = '{$schoolid}' And type = 0 And bj_id1 = '{$bj_id}' ORDER BY createtime DESC LIMIT 0,1");
		$total = pdo_fetchcolumn("SELECT count(*) FROM " . tablename($this->table_media) . " where schoolid = '{$schoolid}' And type = 0 And bj_id1 = '{$bj_id}'");
		// 获取班级相册
		$bjphototype = GetPhotoType($weid,$schoolid,$bj_id,2,'');
		// 获取个人相册
		$condition .= " And bj_id = '{$bj_id}'";	
		$stuphototype =pdo_fetchall("SELECT * FROM " . tablename($this->table_students) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition ");
		foreach ($stuphototype as $key => $value) {
			$stotal = pdo_fetchcolumn("SELECT count(*) FROM " . tablename($this->table_media) . " where schoolid = '{$schoolid}' And type = 1 And sid = '{$value['id']}' ");	 
			$media =pdo_fetch("SELECT picurl FROM " . tablename($this->table_media) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND type= 1 AND sid = '{$value['id']}'"); 
			$stuphototype[$key]['sname'] = $value['s_name'];	
			$stuphototype[$key]['total'] = $stotal;
			$stuphototype[$key]['sid'] = $value['id'];
			$stuphototype[$key]['picurl'] = $media['picurl'];
		}
	}
	if(!empty($userid['id']) && $userid['sid'] == 0){
		include $this->template($school['style3'].'/newxclist');
	}else{
		session_destroy();
		$stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
		header("location:$stopurl");
	}     
// }
// else{
// 	$fisrtbj =  pdo_fetch("SELECT bj_id FROM " . tablename($this->table_class) . " where weid = '{$weid}' And schoolid = '{$schoolid}'  And tid = {$it['tid']} ");
// 	$bjlists = get_mylist($schoolid,$it['tid'],'teacher');	
// 	if(!empty($_GPC['bj_id'])){
// 		$bj_id = intval($_GPC['bj_id']);			
// 	}else{
// 		$bj_id = $fisrtbj['bj_id'];
// 	}
// 	$nowbj = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where sid = :sid ", array(':sid' => $bj_id));
// 	if(is_njzr($teacher['id'])){
// 		$mynjlist = pdo_fetchall("SELECT * FROM " . tablename($this->table_classify) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And tid = '{$it['tid']}' And type = 'semester' ORDER BY ssort DESC");
// 		foreach($mynjlist as $key =>$row){
// 			$mynjlist[$key]['bjlist'] = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And parentid = '{$row['sid']}' And type = 'theclass' ORDER BY sid ASC, ssort DESC");
// 			foreach($mynjlist[$key]['bjlist'] as $k => $v){
	
// 			}
// 		}
// 	}else{
// 		if($teacher['status'] == 2){
// 			$bjlist = pdo_fetchall("SELECT * FROM " . tablename($this->table_classify) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And type = 'theclass' ORDER BY sid ASC, ssort DESC");
// 		}			
// 	}
// 	$frist = pdo_fetch("SELECT * FROM " . tablename($this->table_media) . " where schoolid = '{$schoolid}' And type = 0 And (bj_id1 = '{$bj_id}' or bj_id2 = '{$bj_id}' or bj_id3 = '{$bj_id}') ORDER BY createtime DESC LIMIT 0,1");
	
// 	$total = pdo_fetchcolumn("SELECT count(*) FROM " . tablename($this->table_media) . " where schoolid = '{$schoolid}' And type = 0 And (bj_id1 = '{$bj_id}' or bj_id2 = '{$bj_id}' or bj_id3 = '{$bj_id}')");
	
// 	$cfrist = pdo_fetch("SELECT * FROM " . tablename($this->table_media) . " where schoolid = '{$schoolid}' And type = 2 And (bj_id1 = '{$bj_id}' or bj_id2 = '{$bj_id}' or bj_id3 = '{$bj_id}') ORDER BY id DESC LIMIT 0,1");
	
// 	$ctotal = pdo_fetchcolumn("SELECT count(*) FROM " . tablename($this->table_media) . " where schoolid = '{$schoolid}' And type = 2 And (bj_id1 = '{$bj_id}' or bj_id2 = '{$bj_id}' or bj_id3 = '{$bj_id}')");	
// 	if ($_GPC['getalist']) {
// 		$pageSize = intval($_GPC['pageSize']);
// 		$nowPage = intval($_GPC['nowPage']);
// 		$item_per_page = empty($_GPC['pageSize']) ? 10 : intval($_GPC['pageSize']);
// 		$page_number = max(1, intval($_GPC['nowPage']));	
// 		$condition .= " And bj_id1 = '{$bj_id}'";			
// 		if(!is_numeric($page_number)){  
// 			header('HTTP/1.1 500 Invalid page number!');  
// 			exit();  
// 		}
// 		$position = ($page_number-1) * $item_per_page;
// 		$xclist =pdo_fetchall("SELECT * FROM " . tablename($this->table_media) . " WHERE weid = {$_W['uniacid']} AND type= 1 AND isfm = 1 $condition ORDER BY createtime DESC LIMIT " . $position . ',' . $item_per_page );
// 		foreach ($xclist as $key => $value) {
// 			$students = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where weid = :weid AND :schoolid = schoolid AND id=:id", array(
// 				':weid' => $weid,
// 				':schoolid' => $schoolid,
// 				':id' => $value['sid']
// 				));
// 			$stotal = pdo_fetchcolumn("SELECT count(*) FROM " . tablename($this->table_media) . " where schoolid = '{$schoolid}' And type = 1 And sid = '{$value['sid']}' ");	  
// 			$xclist[$key]['tag'] = $students['s_name'];	
// 			$xclist[$key]['wlzytype'] = $value['sid'];
// 			$xclist[$key]['total'] = $stotal;
// 			$xclist[$key]['tagid'] = $value['uid'];
// 			$xclist[$key]['picurl'] = tomedia($value['fmpicurl']);
// 		}
// 		$datas = array(
// 			'ret' => array('code' => '200','msg' => 'success'),
// 			'data' => array('albumList' => $xclist)
// 		);
// 		echo json_encode($datas);
// 		exit;
// 	}
// 	if(!empty($userid['id']) && $userid['sid'] == 0){
// 		include $this->template(''.$school['style3'].'/xclist');
// 	}else{
// 		session_destroy();
// 		$stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
// 		header("location:$stopurl");
// 	}     
// }
   
