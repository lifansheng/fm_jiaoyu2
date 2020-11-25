<?php
/**
 * 微教育模块
 * 微教育官方www.daren007.com
0 * @author 高贵血迹
 */

global $_GPC, $_W;
load()->func('tpl');
$weid              = $_W['uniacid'];
$action            = 'usercenter';
$this1             = 'no1';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
$schooltype         = $_W['schooltype'];
$school            = pdo_fetch("SELECT * FROM " . GetTableName('index') . " where id = :id ", array(':id' => $schoolid));
$logo              = pdo_fetch("SELECT logo,title,is_stuewcode,spic FROM " . GetTableName('index') . " WHERE id = '{$schoolid}'");		
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
/**初始化页面**/
if ($operation == 'display') {
	$page = $_GPC['page'];
	/**初始老师 重要**/
	$myself = false;
	if(is_numeric($_W['tid'])){
		$nowtid = $_W['tid'];
		$myself = true;//本人账号登录
	}else{
		if($_W['isfounder'] || $_W['role'] == 'owner' || $_W['role'] == 'vice_founder') {
			$nowtid = $_GPC['tid'];
		}
	}
	/**初始老师 end**/
	if(!is_numeric($nowtid)){
		$this->imessage('操作失败, 非法访问.');
	}
	$user = pdo_fetch("SELECT openid,id FROM " . tablename($this->table_user) . " WHERE tid = '{$nowtid}' ");
	$openid = $user['openid'];
	$userid = $user['id'];
	$myteacherinfo = pdo_fetch("SELECT * FROM " . GetTableName('teachers') . " WHERE id = '{$nowtid}'");
	$mythumb = !empty($myteacherinfo['thumb']) ? tomedia($myteacherinfo['thumb']) : tomedia($school['tpic']);
	$myTitle = GetTeacherTitle($myteacherinfo['status'],$myteacherinfo['fz_id']);
	mload()->model('tea');
	$bjlists = GetAllClassInfoByTid($schoolid,1,$schooltype,$nowtid);
	if(!empty($_GPC['bj_id'])){
		$bj_id = intval($_GPC['bj_id']);			
	}else{
		$bj_id = intval($bjlists[0]['sid']);
	}
	if($schooltype){
		$nowbjinfo = pdo_fetch("SELECT name FROM " . GetTableName('tcourse') . " WHERE id = '{$bj_id}'");
		$nowbjname = $nowbjinfo['name'];
	}else{
		$nowbjinfo = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$bj_id}'");
		$nowbjname = $nowbjinfo['sname'];
	}
	/******************留言信息******************/
	$lyList = pdo_fetchall("SELECT id,touserid,userid FROM " . tablename($this->table_leave) . " WHERE weid = :weid AND schoolid =:schoolid  AND isfrist = :isfrist And :isliuyan = isliuyan And (userid = :userid OR touserid = :touserid) ORDER BY id DESC", array(
		':weid' => $weid,
		':schoolid' => $schoolid,
		':isfrist' => 1,
		':isliuyan' => 2,
		':userid' => $userid,
		':touserid' => $userid
	));
	foreach ($lyList as $key => $row) {
		if($row['userid'] == $userid){ //发起者(老师)
			$user = pdo_fetch("SELECT pard,sid,tid FROM " . tablename($this->table_user) . " where schoolid = :schoolid AND id = :id", array(':schoolid' => $schoolid, ':id' => $row['touserid']));
			if($user['sid']){
				$student = pdo_fetch("SELECT s_name,bj_id,icon FROM " . tablename($this->table_students) . " where schoolid = :schoolid AND id = :id", array(':schoolid' => $schoolid, ':id' => $user['sid']));
				$bj = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where schoolid = :schoolid AND sid = :sid", array(':schoolid' => $schoolid, ':sid' => $student['bj_id']));
				$lyList[$key]['bjname'] = $bj['sname'];
				$lyList[$key]['name'] = $student['s_name'].get_guanxi($user['pard']);
				$lyList[$key]['icon'] = $student['icon']?tomedia($student['icon']):tomedia($school['spic']);
			}
			if($user['tid']){
				$teacher = pdo_fetch("SELECT tname,thumb,fz_id FROM " . tablename($this->table_teachers) . " where schoolid = :schoolid AND id = :id", array(':schoolid' => $schoolid, ':id' => $user['tid']));
				$fz = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where schoolid = :schoolid AND sid = :sid", array(':schoolid' => $schoolid, ':sid' => $teacher['fz_id']));
				$lyList[$key]['name'] = $teacher['tname'].'老师';
				$lyList[$key]['bjname'] = $fz['sname'];
				$lyList[$key]['icon'] = $teacher['thumb']?tomedia($teacher['thumb']):tomedia($school['tpic']);
			}	

		}	
		if($row['touserid'] == $userid){ //接收者(家长)
			$user = pdo_fetch("SELECT pard,sid,tid FROM " . tablename($this->table_user) . " where schoolid = :schoolid AND id = :id", array(':schoolid' => $schoolid, ':id' => $row['userid']));
			
			if($user['sid']){
				$student = pdo_fetch("SELECT s_name,bj_id,icon FROM " . tablename($this->table_students) . " where schoolid = :schoolid AND id = :id", array(':schoolid' => $schoolid, ':id' => $user['sid']));
				$bj = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where schoolid = :schoolid AND sid = :sid", array(':schoolid' => $schoolid, ':sid' => $student['bj_id']));
				$lyList[$key]['bjname'] = $bj['sname'];	$lyList[$key]['name'] = $student['s_name'].get_guanxi($user['pard']);
				$lyList[$key]['icon'] = $student['icon']?tomedia($student['icon']):tomedia($school['spic']);
			}
			if($user['tid']){
				$teacher = pdo_fetch("SELECT tname,thumb,fz_id FROM " . tablename($this->table_teachers) . " where schoolid = :schoolid AND id = :id", array(':schoolid' => $schoolid, ':id' => $user['tid']));
				$fz = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where schoolid = :schoolid AND sid = :sid", array(':schoolid' => $schoolid, ':sid' => $teacher['fz_id']));
				$lyList[$key]['name'] = $teacher['tname'].'老师';
				$lyList[$key]['bjname'] = $fz['sname'];
				$lyList[$key]['icon'] = $teacher['thumb']?tomedia($teacher['thumb']):tomedia($school['tpic']);
			}					
		}	
		$lyList[$key]['count'] = pdo_fetchcolumn("SELECT count(id) FROM " . tablename($this->table_leave) . " where schoolid = :schoolid AND leaveid = :leaveid AND isread = 1 AND touserid = '{$userid}'", array(':schoolid' => $schoolid, ':leaveid' => $row['id']));	

	}
	if ($page == 'home') {
		mload()->model('tea');
		$myKcList = myKcList($schoolid,$nowtid);
		foreach ($myKcList as $key => $value) {
			if($value['start'] > time()){
				$myKcList[$key]['status'] = '(未开始)';
			}elseif($value['start'] < time() && $value['end'] > time()){
				$myKcList[$key]['status'] = '(进行中)';
			}elseif($value['end'] < time()){
				$myKcList[$key]['status'] = '(已结束)';
			}
		}
		$kcid = $myKcList[0]['id'];
		
		//加载3种文章类型
		$gonggao = pdo_fetchall("SELECT n.*,i.title as name FROM " . GetTableName('news') . " as n LEFT JOIN " . GetTableName('index') . " as i ON i.id = n.schoolid WHERE n.weid = '{$weid}' And (n.schoolid = '{$schoolid}' OR FIND_IN_SET('{$schoolid}',n.schoolidstr)) And n.type = 'article' $condition ORDER BY n.createtime DESC, n.id DESC LIMIT 0,5");
		foreach ($gonggao as $key => $value) {
			$ggcount = pdo_fetchcolumn("SELECT count(id) FROM ".GetTableName('articlepl')." WHERE schoolid = '{$schoolid}' AND weid = '{$weid}' AND a_id = '{$value['id']}'");
			$ggdznum = pdo_fetchcolumn("SELECT count(*) FROM ".GetTableName('articledz')." WHERE a_id = '{$value['id']}' and status = 1 ");
			//判断是否已经点赞
			$ggIsDz = pdo_fetch("SELECT id FROM ".GetTableName('articledz')." WHERE a_id = '{$value['id']}' AND openid = '{$openid}' AND status = 1 ");
			$gonggao[$key]['isDz'] = $ggIsDz ? true : false;
			$gonggao[$key]['dznum'] = $ggdznum;
			$gonggao[$key]['count'] = $ggcount;
		}
		$xinwen = pdo_fetchall("SELECT n.*,i.title as name FROM " . GetTableName('news') . " as n LEFT JOIN " . GetTableName('index') . " as i ON i.id = n.schoolid WHERE n.weid = '{$weid}' And (n.schoolid = '{$schoolid}' OR FIND_IN_SET('{$schoolid}',n.schoolidstr)) And n.type = 'news' $condition ORDER BY n.createtime DESC, n.id DESC LIMIT 0,5");
		foreach ($xinwen as $key => $value) {
			$xwcount = pdo_fetchcolumn("SELECT count(id) FROM ".GetTableName('articlepl')." WHERE schoolid = '{$schoolid}' AND weid = '{$weid}' AND a_id = '{$value['id']}'");
			$xwdznum = pdo_fetchcolumn("SELECT count(*) FROM ".GetTableName('articledz')." WHERE a_id = '{$value['id']}' and status = 1 ");
			$xwIsDz = pdo_fetch("SELECT id FROM ".GetTableName('articledz')." WHERE a_id = '{$value['id']}' AND openid = '{$openid}' AND status = 1 ");
			$xinwen[$key]['isDz'] = $xwIsDz ? true : false;
			$xinwen[$key]['dznum'] = $xwdznum;
			$xinwen[$key]['count'] = $xwcount;
		}
		$wenzhang = pdo_fetchall("SELECT n.*,i.title as name FROM " . GetTableName('news') . " as n LEFT JOIN " . GetTableName('index') . " as i ON i.id = n.schoolid WHERE n.weid = '{$weid}' And (n.schoolid = '{$schoolid}' OR FIND_IN_SET('{$schoolid}',n.schoolidstr)) And n.type = 'wenzhang' $condition ORDER BY n.createtime DESC, n.id DESC LIMIT 0,5");
		foreach ($wenzhang as $key => $value) {
			$wzcount = pdo_fetchcolumn("SELECT count(id) FROM ".GetTableName('articlepl')." WHERE schoolid = '{$schoolid}' AND weid = '{$weid}' AND a_id = '{$value['id']}'");
			$wzdznum = pdo_fetchcolumn("SELECT count(*) FROM ".GetTableName('articledz')." WHERE a_id = '{$value['id']}' and status = 1 ");
			$wzIsDz = pdo_fetch("SELECT id FROM ".GetTableName('articledz')." WHERE a_id = '{$value['id']}' AND openid = '{$openid}' AND status = 1 ");
			$wenzhang[$key]['isDz'] = $wzIsDz ? true : false;
			$wenzhang[$key]['dznum'] = $wzdznum;
			$wenzhang[$key]['count'] = $wzcount;
		}
		include $this->template ( 'web/usercenter/home' );
		die();
	}elseif ($page == 'bjq') {
		include $this->template ( 'web/usercenter/bjq' );
		die();
	}elseif ($page == 'user') {	
		include $this->template ( 'web/usercenter/user' );
		die();
	}elseif ($page == 'callbook') {//暂未启用
		include $this->template ( 'web/usercenter/callbook' );
		die();
	}
	$userid = pdo_fetch("SELECT id FROM " . GetTableName('user') . " WHERE tid = '{$nowtid}'")['id'];
	if($_W['schooltype']){
		//作业数量统计
		$sql_zy = "SELECT COUNT(id) FROM " .GetTableName('kcsign') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND tid = '{$nowtid}' AND status = 2";

		$nowweekstart = strtotime(date("Y-m-d",time()));
        $nowweekend = strtotime(date("Y-m-d",time()))+86399;
		mload()->model('kc');
		$list = GetKcInfo($weid, $schoolid, '','',$nowweekstart,$nowweekend,'');
		$kcbiao = [];
		foreach ($list as $key => $value) {
			$kcbiao[$key]['name'] = $value['kcnames'];
			$kcbiao[$key]['bjname'] = $value['adrr'];
			$kcbiao[$key]['icon'] = $mythumb;
			$kcbiao[$key]['sdname'] = $value['sd_start'].'-'.$value['sd_end'];
			$kcbiao[$key]['othertips'] = "({$value['ksname']})";
		}
	}else{
		//作业数量统计
		$sql_zy = "SELECT COUNT(id) FROM " .GetTableName('notice') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND tid = '{$nowtid}' AND type = 3";
		$datetime = strtotime(date("Y-m-d",time()));
		$kcbiao = pdo_fetchall("SELECT km.sname as name,bj.sname as bjname,km.icon,sd.sname as sdname FROM ".GetTableName('glkebiao')." as kb LEFT JOIN ".GetTableName('classify')." as km ON kb.kmid = km.sid LEFT JOIN ".GetTableName('timetable')." as tt ON tt.id = kb.kbid LEFT JOIN ".GetTableName('classify')." as sd ON sd.sid = kb.sdid LEFT JOIN ".GetTableName('classify')." as bj ON bj.sid = tt.bj_id WHERE kb.schoolid = '{$schoolid}' AND kb.weid = '{$weid}' AND kb.date = '{$datetime}' ORDER BY kb.kbid,kb.num");
		foreach ($kcbiao as $key => $value) {
			$kcbiao[$key]['icon'] = $value['icon'] ? tomedia($value['icon']) : tomedia($school['logo']);
			$kcbiao[$key]['status'] = getStatus($key);
		}
	}
	
	
	//班级通知数量统计
	$sql_bjtz = "SELECT COUNT(id) FROM " .GetTableName('notice') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND tid = '{$nowtid}' AND type = 1";

	//签到数量统计
	$sql_qd = "SELECT COUNT(id) FROM " .GetTableName('checklog') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND tid = '{$nowtid}'";

	//班级圈数量统计
	$sql_bjq = "SELECT COUNT(id) FROM " .GetTableName('bjq') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND userid = '{$userid}'";

	//留言数量统计
	$sql_ly = "SELECT COUNT(id) FROM " .GetTableName('leave') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND isliuyan = 2 AND (userid = '{$userid}' OR touserid = '{$userid}')";

	//请假数量统计
	$sql_qj = "SELECT COUNT(id) FROM " .GetTableName('leave') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND tid = '{$nowtid}'";
	
	$mytj = pdo_fetch("SELECT ({$sql_zy}) as zy , ({$sql_bjtz}) as bjtz, ({$sql_qd}) as qd, ({$sql_bjq}) as bjq, ({$sql_ly}) as ly, ({$sql_qj}) as qj");
/**通知作业等弹框**/
}elseif ($operation == 'notcie') {
	$page = $_GPC['page'];
	if ($page == 'detail') {
		$id = intval($_GPC['noticeid']);
		$notcie = pdo_fetch("SELECT * FROM " . GetTableName('notice') . " WHERE id = '{$id}'");
		$picarr = iunserializer($notcie['picarr']);
	}
	if ($page == 'list') {
		$bj_id = intval($_GPC['bj_id']);
		$type = intval($_GPC['tztype']);
		if($type != 2){
			if($schooltype){
				$condition2 = " AND kc_id = '{$bj_id}' ";
			}else{
				$condition2 = " AND bj_id = '{$bj_id}' ";
			}
		}else{	
			if (!(IsHasQx($nowtid,2000202,2,$schoolid))){
				$condition2 = " And type = 2 AND ( groupid = 1 Or groupid = 2 Or groupid = 6 Or groupid = 7 Or usertype = 'school' Or usertype = 'alltea' Or usertype = 'staff_jsfz' Or usertype = 'staff' Or tid = '{$nowtid}') ";
			}else{
				$condition2 = " And type = 2 ";	
			}
		}
		$alllist = pdo_fetchall("SELECT id,bj_id,kc_id,title,tname,tid,km_id,createtime,content,ismobile,usertype,type FROM " . GetTableName('notice') . " where weid = '{$weid}' And schoolid = '{$schoolid}' And type = '{$type}' $condition2 ORDER BY createtime DESC LIMIT 0,10 ");
		foreach($alllist as $key =>$row){
			if($row['type'] == 3){
				$kemu = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " where sid = :sid ", array(':sid' => $row['km_id']));
				$alllist[$key]['kmname'] = $kemu['sname'];
			}else{
				$teach = pdo_fetch("SELECT status,thumb,tname FROM " . GetTableName('teachers') . " where id = :id ", array(':id' => $row['tid']));
				$alllist[$key]['name'] = $teach['tname'];
				$alllist[$key]['thumb'] = empty($teach['thumb']) ? tomedia($school['tpic']) : tomedia($teach['thumb']);
			}
		}
	}
	include $this->template ( 'web/usercenter/notice' );
	die();	
/**班级圈瀑布流首次打开模板**/
}elseif ($operation == 'bjqlist') {
	$bjid = intval($_GPC['bjid']);
	$condition = '';
	if($school['bjqstyle'] != 'old'){
		if(!empty($bjid)){
			$condition .= " And (bj_id1 = '{$bjid}' or bj_id2 = '{$bjid}' or bj_id3 = '{$bjid}')";
		}
	}

	if($_W['schooltype']){
		if(is_showgkk()){
			$list = pdo_fetchall("SELECT * FROM " . tablename($this->table_bjq) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And ((type = 0) Or ( type = 2 )) ORDER BY id DESC LIMIT 0,20");
		}else{
			$list = pdo_fetchall("SELECT * FROM " . tablename($this->table_bjq) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And type = 0 ORDER BY id DESC LIMIT 0,20");
		}
	}else{
		$list = pdo_fetchall("SELECT * FROM " . tablename($this->table_bjq) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And type = 0 $condition ORDER BY id DESC LIMIT 0,20");
	}

    foreach($list as $index => $row){
		$sf = pdo_fetch("SELECT sid,tid FROM ".GetTableName('user')." WHERE id = '{$row['userid']}' ");
		if($sf['sid']){ //家长
			$avatar = pdo_fetch("SELECT icon FROM ".GetTableName('students')." WHERE id='{$sf['sid']}'")['icon'];
			$list[$index]['sf'] = 'jz';
			$list[$index]['avatar'] = tomedia($avatar);
		}
		if($sf['tid']){ //老师
			$avatar = pdo_fetch("SELECT thumb FROM ".GetTableName('teachers')." WHERE id='{$sf['tid']}'")['thumb'];
			$list[$index]['sf'] = 'tea';
			$list[$index]['avatar'] = tomedia($avatar);
		}
		$datalist[$index]['hasvideo'] = false;
		if($row['video']){
			$list[$index]['hasvideo'] = true;
			if($row['videoimg']){
				$list[$index]['picurl'] = tomedia($row['videoimg']);
			}else{
				if($row['ali_vod_id']){
					mload()->model('ali');
					$aliyun = GetAliApp($weid,$schoolid);
					if($aliyun['result']){
						$appid = $aliyun['alivodappid'];
						$key = $aliyun['alivodkey'];
						do {
						   $GetAliVideoCover = GetAliVideoCover($appid,$key,trim($row['ali_vod_id']));
						} while (empty($GetAliVideoCover['CoverURL']));
						$list[$index]['picurl'] = tomeida($GetAliVideoCover['CoverURL']);
					}
					pdo_update(GetTableName('bjq',false),array('videoimg'=>$GetAliVideoCover['CoverURL']),array('id'=>$row['id']));
				}else{
					$list[$index]['picurl'] = tomedia($school['logo']);
				}
			}
		}
		$picurl = pdo_fetch("SELECT picurl FROM " . tablename($this->table_media) . " WHERE weid = :weid AND schoolid = :schoolid AND sherid =:sherid  ORDER BY id ASC", array(':weid' => $weid,':schoolid' => $schoolid,':sherid' => $row['sherid']))['picurl'];
		if($picurl){
			$list[$index]['picurl'] = tomedia($picurl);
		}

		$dznum = pdo_fetchcolumn("SELECT count(id) FROM " . tablename($this->table_dianzan) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND sherid = '{$row['sherid']}'" );
		$list[$index]['dznum'] = $dznum;
		$plnum = pdo_fetchcolumn("SELECT count(id) FROM " . tablename($this->table_bjq) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND type=1 AND sherid ={$row['sherid']}" );
		$list[$index]['plnum'] = $plnum;
	}
	include $this->template ( 'web/usercenter/bjqlist' );
	die();
/**班级圈左侧弹框**/
}elseif ($operation == 'scroll_bjlist'){
	$bjid = intval($_GPC['bjid']);
	$dataid = intval($_GPC['dataid']);
	$condition = " And id < '{$dataid}'";
	if($school['bjqstyle'] != 'old'){
		if(!empty($bjid)){
			$condition .= " And (bj_id1 = '{$bjid}' or bj_id2 = '{$bjid}' or bj_id3 = '{$bjid}')";
		}
	}
	$datalist = [];
	if($_W['schooltype']){
		if(is_showgkk()){
			$list = pdo_fetchall("SELECT * FROM " . tablename($this->table_bjq) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And ((type = 0) Or ( type = 2 )) And id < '{$dataid}' ORDER BY id DESC LIMIT 0,10");
		}else{
			$list = pdo_fetchall("SELECT * FROM " . tablename($this->table_bjq) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And type = 0 And id < '{$dataid}' ORDER BY id DESC LIMIT 0,10");
		}
	}else{
		$list = pdo_fetchall("SELECT * FROM " . tablename($this->table_bjq) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And type = 0 $condition ORDER BY id DESC LIMIT 0,4");
	}
    foreach($list as $index => $row){
		$sf = pdo_fetch("SELECT sid,tid FROM ".GetTableName('user')." WHERE id = '{$row['userid']}' ");
		if($sf['sid']){ //家长
			$list[$index]['sf'] = 'jz';
		}
		if($sf['tid']){ //老师
			$list[$index]['sf'] = 'tea';
		}
		$datalist[$index]['hasvideo'] = false;

		if($row['video']){
			$datalist[$index]['hasvideo'] = true;
			if($row['videoimg']){
				$datalist[$index]['src'] = tomedia($row['videoimg']);
			}else{
				if($row['ali_vod_id']){
					mload()->model('ali');
					$aliyun = GetAliApp($weid,$schoolid);
					if($aliyun['result']){
						$appid = $aliyun['alivodappid'];
						$key = $aliyun['alivodkey'];
						do {
						   $GetAliVideoCover = GetAliVideoCover($appid,$key,trim($row['ali_vod_id']));
						} while (empty($GetAliVideoCover['CoverURL']));
						$datalist[$index]['src'] = tomeida($GetAliVideoCover['CoverURL']);
					}
					pdo_update(GetTableName('bjq',false),array('videoimg'=>$GetAliVideoCover['CoverURL']),array('id'=>$row['id']));
				}else{
					$datalist[$index]['src'] = tomedia($school['logo']);
				}
			}
		}

		$picurl = pdo_fetch("SELECT picurl FROM " . tablename($this->table_media) . " WHERE weid = :weid AND schoolid = :schoolid AND sherid =:sherid", array(':weid' => $weid,':schoolid' => $schoolid,':sherid' => $row['sherid']))['picurl'];
		if($picurl){
			$datalist[$index]['src'] = tomedia($picurl);
		}
		
		$datalist[$index]['id'] = $row['id'];
		$datalist[$index]['coment'] = $row['content'];
		
		$sf = pdo_fetch("SELECT sid,tid FROM ".GetTableName('user')." WHERE id = '{$row['userid']}' ");
		if($sf['sid']){ //家长
			$avatar = pdo_fetch("SELECT icon FROM ".GetTableName('students')." WHERE id='{$sf['sid']}'")['icon'];
			$datalist[$index]['sf'] = 'jz';
			$datalist[$index]['avatar'] = tomedia($avatar);
			$datalist[$index]['tip'] = 'success';
		}
		if($sf['tid']){ //老师
			$avatar = pdo_fetch("SELECT thumb FROM ".GetTableName('teachers')." WHERE id='{$sf['tid']}'")['thumb'];
			$datalist[$index]['sf'] = 'tea';
			$datalist[$index]['avatar'] = tomedia($avatar);
			$datalist[$index]['tip'] = 'parmary';
		}
		$datalist[$index]['username'] = $row['shername'];
		$datalist[$index]['times'] = date("Y-m-d H:i:s",$row['createtime']);
		$dznum = pdo_fetchcolumn("SELECT count(id) FROM " . tablename($this->table_dianzan) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND sherid = '{$row['sherid']}'" );
		$plnum = pdo_fetchcolumn("SELECT count(id) FROM " . tablename($this->table_bjq) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND type=1 AND sherid ={$row['sherid']}" );
		$datalist[$index]['reply'] = $plnum;
		$datalist[$index]['hits'] = $dznum;
		$datalist[$index]['isopen'] = $row['isopen'];
	}
	die(json_encode($datalist));
}elseif ($operation == 'bjqinfo') {
	$bjid = intval($_GPC['bjid']);
	$bjqInfo = pdo_fetch("SELECT bj.sid,bj.sname as bjname,nj.sname as njname,count(DISTINCT(s.id)) as snum,count(DISTINCT(bjq.id)) as bjqnum FROM " . tablename($this->table_classify) . " as bj LEFT JOIN ".GetTableName('classify')." as nj ON nj.sid = bj.parentid  LEFT JOIN ".GetTableName('students')." as s ON s.bj_id = bj.sid LEFT JOIN ".GetTableName('bjq')." as bjq ON (bjq.bj_id1 = bj.sid OR bjq.bj_id2 = bj.sid OR bjq.bj_id3 = bj.sid) AND bjq.type = 0 where bj.schoolid = '{$schoolid}' And bj.weid = '{$weid}' AND bj.sid = '{$bjid}'");
	$content = pdo_fetch("SELECT content FROM ".GetTableName('bjq')." WHERE schoolid = '{$schoolid}' AND (bj_id1 = '{$bjid}' or bj_id2 = '{$bjid}' or bj_id3 = '{$bjid}') AND type = 0 AND content is not null ORDER BY createtime DESC");
	//授课老师
    $AllTea = pdo_fetchall("SELECT t.tname,c.km_id FROM ".GetTableName('teachers')." as t , ".GetTableName('user_class')." as c WHERE  c.bj_id = '{$bjid}' and  t.id = c.tid and c.schoolid = '{$schoolid}'  group by c.tid   ");
    foreach($AllTea as $key=> $value){
        $SubStr = '';
        $AllSub = pdo_fetchall("SELECT sname FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and sid = '{$value['km_id']}' ");
        foreach($AllSub as $vs){
            $SubStr .= $vs['sname'].',';
        }
        $SubStr = rtrim($SubStr,',');
        $AllTea[$key]['sub'] = $SubStr;
	}
	$active = pdo_fetchall("SELECT shername FROM " . tablename($this->table_bjq) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And type = 0 And (bj_id1 = '{$bjid}' or bj_id2 = '{$bjid}' or bj_id3 = '{$bjid}') ORDER BY id DESC LIMIT 0,5");
	include $this->template ( 'web/usercenter/bjqinfo' );
	die();
/**班级圈右侧弹框**/	
}elseif ($operation == 'bjqdetail') {
	$bjqid = intval($_GPC['dataid']);
	$bjqInfo = pdo_fetch("SELECT * FROM " . tablename($this->table_bjq) . " where schoolid = '{$schoolid}' And weid = '{$weid}' AND id = '{$bjqid}'");
	if($bjqInfo['msgtype'] == 2){
		$member =  GetWeFans($weid,$bjqInfo['openid']);
		$bjqInfo['avatar'] = $member['avatar'];
	}
	$strlen  = mb_strlen($bjqInfo['content'], 'UTF-8');
	$picurlList = pdo_fetchall("SELECT * FROM " . tablename($this->table_media) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND sherid = '{$bjqInfo['sherid']}'  ORDER BY id ASC" );
	$dzList = pdo_fetchall("SELECT zname,userid FROM " . tablename($this->table_dianzan) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND sherid = '{$bjqInfo['sherid']}'  ORDER BY createtime ASC" );

	foreach ($dzList as $key => $value) {
		$dzsf = pdo_fetch("SELECT sid,tid FROM ".GetTableName('user')." WHERE id = '{$value['userid']}' ");
		// var_dump($dzsf);
		if($dzsf['sid']){ //家长
			$avatar = pdo_fetch("SELECT icon FROM ".GetTableName('students')." WHERE id='{$dzsf['sid']}'")['icon'];
		}
		if($dzsf['tid']){ //老师
			$avatar = pdo_fetch("SELECT thumb FROM ".GetTableName('teachers')." WHERE id='{$dzsf['tid']}'")['thumb'];
		}
		$dzList[$key]['avatar'] = tomedia($avatar);
	}
	
	$plList = pdo_fetchall("SELECT id,userid,content,shername,createtime,audiotime,sherid FROM " . tablename($this->table_bjq) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND type=1 AND sherid ={$bjqInfo['sherid']}  ORDER BY createtime ASC" );
	
	foreach ($plList as $key => $value) {
		$plList[$key]['time'] = sub_day($value['createtime']);
		$sf = pdo_fetch("SELECT sid,tid FROM ".GetTableName('user')." WHERE id = '{$value['userid']}' ");
		if($sf['sid']){ //家长
			$avatar = pdo_fetch("SELECT icon FROM ".GetTableName('students')." WHERE id='{$sf['sid']}'")['icon'];
		}
		if($sf['tid']){ //老师
			$avatar = pdo_fetch("SELECT thumb FROM ".GetTableName('teachers')." WHERE id='{$sf['tid']}'")['thumb'];
		}
		$status = getStatus($key);
		$plList[$key]['status'] = $status;
		
		$plList[$key]['avatar'] = tomedia($avatar);
	}
	include $this->template ( 'web/usercenter/bjqdetail' );
	die();
/**删除班级圈**/
}elseif ($operation == 'delbjq'){
	$id = $_GPC['id'];
	$bjqInfo = pdo_fetch("SELECT * FROM " . tablename($this->table_bjq) . " where schoolid = '{$schoolid}' And weid = '{$weid}' AND id = '{$id}'");
	$media = pdo_fetchall("SELECT picurl FROM ".GetTableName('media')." WHERE sherid = '{$id}' ");
	if($media){
		foreach ($media as $key => $value) {
			if (!empty($_W['setting']['remote']['type'])) { //远程附件
				file_remote_delete($value['picurl']);
			}else{
				file_delete($value['picurl']);
			}
		}
	}
	if($bjqInfo['audio']){
		if (!empty($_W['setting']['remote']['type'])) { //远程附件
			file_remote_delete($bjqInfo['audio']);
		}else{
			file_delete($bjqInfo['audio']);
		}
	}
	if($bjqInfo['video']){
		if($bjqInfo['ali_vod_id']){
			mload()->model('ali');
			$aliyun = GetAliApp($weid,$schoolid);
			if($aliyun['result']){
				$appid = $aliyun['alivodappid'];
				$key = $aliyun['alivodkey'];
				$GetAliVideoUrl = DelAlivod($appid,$key,trim($bjqInfo['ali_vod_id']));
			}
		}else{
			if (!empty($_W['setting']['remote']['type'])) { //远程附件
				file_remote_delete($bjqInfo['video']);
			}else{
				file_delete($bjqInfo['video']);
			}
		}
	}
	pdo_delete(GetTableName('bjq',false),array('id'=>$id)); //删除班级圈
	pdo_delete(GetTableName('dianzan',false),array('sherid'=>$id)); //删除点赞
	pdo_delete(GetTableName('bjq',false),array('sherid'=>$id)); //删除评论
	pdo_delete(GetTableName('media',false),array('sherid'=>$id)); //删除图片
	$result['msg'] = '删除成功';
	$result['result'] = true;
	die(json_encode($result));
/**删除评论**/
}elseif ($operation == 'delpl'){
	$id = $_GPC['plid'];
	pdo_delete(GetTableName('bjq',false),array('id'=>$id));
	$result['msg'] = '删除成功';
	$result['result'] = true;
	die(json_encode($result));
/**班级圈审核开关**/
}elseif ($operation == 'bjqswitch'){
	$bjqid = $_GPC['bjqid'];
	if($_GPC['type'] == 'isopen'){
		$data = array(
			'isopen' => $_GPC['isopen']
		);
	}
	if($_GPC['type'] == 'allow_pl'){
		$data = array(
			'is_private' => $_GPC['allow_pl']
		);
	}
	pdo_update(GetTableName('bjq',false),$data,array('id'=>$_GPC['bjqid']));
/**创建新的班级圈**/
}elseif ($operation == 'savebjq'){
	
	$bjqpic = $_GPC['bjqpic'];
	$bjqvod = $_GPC['bjqvod'];
	$nowbjq_setid = $_GPC['nowbjq_setid'];
	$shername = pdo_fetch("SELECT tname FROM ". GetTableName('teachers')." WHERE id = '{$_GPC['tid']}'")['tname'];
	$userid = pdo_fetch("SELECT id FROM ". GetTableName('user')." WHERE tid = '{$_GPC['tid']}'")['id'];
	if(!$userid){
		$data ['info'] ="{$shername}老师尚未绑定微信,请在手机端绑定微信后方可继续操作！" ;	
		$data ['result'] = false;
		die(json_encode($data));
	}
	$msgtype = 1;
	if($bjqvod){
		$msgtype = 3;//视频
	}
	$temp = array(
		'weid' =>  $weid,
		'schoolid' => $schoolid,
		'uid' => $_W['uid'],
		'userid' => $userid,
		'shername' => $shername,
		'content' => $_GPC['content'],
		'video' => $bjqvod,
		'bj_id1' => $nowbjq_setid,
		'createtime' => time(),
		'msgtype'=>$msgtype,
		'type'=>0,
	);
	if(is_showgkk())
	{
		$iszy = $_GPC['is_zy'];
		if($iszy == 1 )
		{
			$temp['type'] = 2;
		}
	}
	pdo_insert($this->table_bjq, $temp);
	$bjq_id = pdo_insertid();
	$data1 = array(
		'sherid'=>$bjq_id,
	);
	pdo_update($this->table_bjq, $data1, array ('id' => $bjq_id) );
	if($bjqpic){
		foreach($bjqpic as $key => $v){
			if(!empty($v)) {
			   $data = array(
				'weid' =>  $weid,
				'schoolid' => $schoolid,
				'uid' => $_W['uid'],
				'picurl' => $v,	
				'bj_id1' => $nowbjq_setid,
				'order'=>$key+1,
				'sherid'=>$bjq_id,
				'createtime' => time(),
			   );
			   pdo_insert($this->table_media, $data);							
			}
		}
	}
	$data ['status'] = 1;
	$data ['info'] ='发布成功，请勿重复发布！' ;	
	$data ['result'] = true;
	die(json_encode($data));
}elseif ($operation == 'mycander') {
	$nowmonth = $_GPC['nowmonth'];
	$starttime = strtotime($nowmonth);
	$endtime = endDayOfMonth($nowmonth);
	
	$cander = array();
	$cqdata = pdo_fetchAll("SELECT FROM_UNIXTIME(createtime,'%Y-%c-%e') as date FROM " . tablename($this->table_checklog) . " where schoolid = :schoolid AND tid = :tid And isconfirm = 1 AND createtime BETWEEN '{$starttime}' AND '{$endtime}' ", array(
		':schoolid' => $schoolid,
		':tid' => $_GPC['tid']
	));
	$cander['cqdata'] = array_column($cqdata,'date');

	$qjdata = pdo_fetchAll("SELECT FROM_UNIXTIME(startime1,'%Y-%c-%e') as date FROM " . tablename($this->table_leave) . " where schoolid = '{$schoolid}' AND tid = '{$_GPC['tid']}' And sid = 0 And isliuyan = 0 And status = 1 AND (startime1 < '{$starttime}' AND endtime1 > '{$endtime}' OR startime1 > '{$starttime}' AND endtime1 < '{$endtime}')");
	$cander['qjdata'] = array_column($qjdata,'date');
	// $cander['qjdata'] = array("2020-9-3", "2020-9-2", "2020-9-4");
	
	$result['candertip'] = date('n',$starttime).'月出勤'.count($cander['cqdata']).'天';
	$result['msg'] = '获取成功';
	$result['cander'] = $cander;
	$result['result'] = true;
	die(json_encode($result));
//管理员身份点击顶部切换老师渲染模板用	
}elseif ($operation == 'userlist') {
	//加载全校所有老师
	$allfenzu = pdo_fetchall("SELECT sid,sname,pname FROM ".GetTableName('classify')." WHERE schoolid = :schoolid And type = :type ORDER BY sid DESC", array(':schoolid' => $schoolid,':type' => 'jsfz'));
	foreach($allfenzu as $key => $row){
		$allfenzu[$key]['alltea'] = pdo_fetchall("SELECT id,tname,thumb,status FROM ".GetTableName('teachers')." WHERE schoolid = :schoolid And fz_id = :fz_id ORDER BY id DESC", array(':schoolid' => $schoolid,':fz_id' => $row['sid']));
		foreach($allfenzu[$key]['alltea'] as $k => $val){
			$allfenzu[$key]['alltea'][$k]['tname'] = $val['tname'];
			$allfenzu[$key]['alltea'][$k]['thumb'] = !empty($val['thumb']) ? tomedia($val['thumb']) : tomedia($school['tpic']);
			if($val['status'] == 2){
				$allfenzu[$key]['alltea'][$k]['tips'] = '校长';
			}else{
				$allfenzu[$key]['alltea'][$k]['tips'] = '职工';
			}
		}
		$allfenzu[$key]['fznumber'] = count($allfenzu[$key]['alltea']);
	}
	$allnowfz = pdo_fetchall("SELECT id,tname,thumb,status FROM ".GetTableName('teachers')." WHERE schoolid = :schoolid And fz_id = :fz_id ORDER BY id DESC", array(':schoolid' => $schoolid,':fz_id' => 0));
	foreach($allnowfz as $k => $val){
		$allnowfz[$k]['thumb'] = !empty($val['thumb']) ? tomedia($val['thumb']) : tomedia($school['tpic']);
		if($val['status'] == 2){
			$allnowfz[$k]['tips'] = '校长';
		}else{
			$allnowfz[$k]['tips'] = '职工';
		}
	}
	$nofznumber = count($allnowfz);
	include $this->template ( 'web/usercenter/userlist' );
	die();	
}elseif ($operation == 'getkcinfo'){
	$kcid = $_GPC['kcid'];
	$tid = $_GPC['tid'];
	$kcinfo = getInfo($schoolid,$kcid,$tid);
	die(json_encode($kcinfo));
}elseif ($operation == 'edit_tea'){
	$data = array(
		'tname'     => trim($_GPC['tname']),
		'thumb'     => trim($_GPC['thumb']),
		'sex'       => intval($_GPC['sex']),
		'birthdate' => strtotime($_GPC['birthdate']),
		'tel'       => trim($_GPC['tel']),
		'mobile'    => trim($_GPC['mobile']),
		'email'     => trim($_GPC['email']),
		'address'   => $_GPC['address'],
		'minzu'     => $_GPC['minzu'],
		'idcard'    => $_GPC['idcard'],
		'zzmianmao' => $_GPC['zzmianmao'],
		'headinfo'  => trim($_GPC['headinfo']),
		'info'      => htmlspecialchars_decode($_GPC['info']),
		'jinyan'    => trim($_GPC['jinyan']),
	);
	if($_GPC['thumb'] != $item['thumb']){
		$allcard = pdo_fetchall("SELECT id,idcard,tpic FROM " . GetTableName('idcard') . " WHERE :tid = tid", array(':tid' => $id));
		if($allcard){
			foreach($allcard as $row){
				$path = "images/fm_jiaoyu/cardthumb/".$schoolid."/";
				$picurl2 = $path.random(30) .".jpg";
				$image_file = file_get_contents(ATTACHMENT_ROOT.$_GPC['thumb']);
				file_write($picurl2,$image_file);
				if (!empty($_W['setting']['remote']['type'])) {
					$remotestatus = file_remote_upload($picurl2);
				}
				pdo_update(GetTableName('idcard',false), array('tpic' => $picurl2), array('id' => $row['id']));
			}
			pdo_update(GetTableName('schoolset',false), array('top'=>1), array('schoolid' => $schoolid, 'weid'=>$_GPC['weid']));
		}
	}
	pdo_update($this->table_teachers, $data, array('id' => $_GPC['nowtid']));

	if($item['tname'] != $data['tname'] || $item['idcard'] != $data['idcard'] || $item['mobile'] != $data['mobile']){
		mload()->model('xzf');
		setXzfNeedsync($_GPC['nowtid'],'teachers');
	}

	//讯贞触发
	$checkTC = pdo_fetch("SELECT idcard FROM ".GetTableName('idcard')." WHERE schoolid = '{$schoolid}' and tid = '{$_GPC['nowtid']}' ");
	$chekOldTname = pdo_fetch("SELECT tname FROM ".GetTableName('teachers')." WHERE schoolid = '{$schoolid}' and id = '{$_GPC['nowtid']}' ");
	if(!empty($checkTC) &&  $chekOldTname['tname'] != $data['tname'] ){
		//教师有卡，且名字变了
		xzTriggerCommon($schoolid,$checkTC['idcard'],'update');
	}
	$result['result'] = true;
	$result['msg'] = '修改成功';
	die(json_encode($result));
}elseif ($operation == 'savewenz'){
	if(!$_GPC['userid']){
		$data ['msg'] ="{$_GPC['tname']}老师尚未绑定微信,请在手机端绑定微信后方可继续操作！" ;	
		$data ['result'] = false;
		die(json_encode($data));
	}
	$id = $_GPC['id'];
	$content = $_GPC['content'];
	$news = pdo_fetch("SELECT defaultshow FROM ".GetTableName('news')." WHERE id = '{$id}'");
    $status = $news['defaultshow'] == 2 ? 2 : 1;
    $data = array(
        'schoolid' => $schoolid,
        'weid' => $weid,
        'a_id' => $id,
        'openid' => $_GPC['openid'],
        'content' => $content,
        'createtime' => time(),
        'status' => $status,
	);
	pdo_insert(GetTableName('articlepl',false),$data);
	$result['msg'] = '评论成功';
	$result['result'] = true;
	die(json_encode($result));
}elseif($operation == 'savedz'){
	//判断是否点赞
	$a_id = $_GPC['id'];
	$openid = $_GPC['openid'];
	$res = pdo_fetch("SELECT id,status FROM ".GetTableName('articledz')." WHERE a_id = '{$a_id}' AND openid = '{$openid}' ");
	if(!empty($res)){
		if($res['status'] == 1){
			pdo_update(GetTableName('articledz',false),array('status'=>2,'createtime'=>time()),array('id'=>$res['id']));
			$result ['result'] = false;
			$result ['msg'] = '取消点赞';
			$result ['id'] = $res['id'];
		}else{
			pdo_update(GetTableName('articledz',false),array('status'=>1,'createtime'=>time()),array('id'=>$res['id']));
			$result ['result'] = true;
			$result ['msg'] = '点赞成功';
			$result ['id'] = $res['id'];
		}
	}else{
		$data = array(
			'schoolid' => $_GPC ['schoolid'],
			'weid' => $_GPC ['weid'],
			'a_id' => $a_id,
			'openid' => $openid,
			'createtime' => time(),
			'status' => 1,
		);
		pdo_insert(GetTableName('articledz',false),$data);
		$result ['result'] = true;
		$result ['msg'] = '点赞成功';
	}
	
	die ( json_encode ( $result ) );
}elseif($operation == 'getxzxx'){
	//校长信箱
	if($_GPC['type'] == 1){
		$condition = " AND huifu = ''";
	}elseif($_GPC['type'] == 2){
		$condition = " AND huifu != ''";
	}else{
		$condition = "";
	}
	
	$leave1 = pdo_fetchall("SELECT * FROM " . tablename($this->table_courseorder) . " where weid = '{$weid}' And schoolid = '{$schoolid}'  And type=1 $condition ORDER BY createtime DESC");
	foreach($leave1 as $key =>$row){
		$teach = pdo_fetch("SELECT tname,thumb FROM " . tablename($this->table_teachers) . " where id = :id ", array(':id' => $row['totid']));
		$stu= pdo_fetch("SELECT sid,pard FROM ".tablename($this->table_user)." WHERE :weid = weid AND :id = id AND :schoolid = schoolid", array(':weid' => $weid, ':id' => $row['fromuserid'], ':schoolid' => $schoolid));
		$students = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where weid = :weid AND id=:id", array(':weid' => $weid, ':id' => $stu['sid']));
		$guanxi = "本人";
		if($stu['pard'] == 2){
			$guanxi = "妈妈";
		}else if($stu['pard'] == 3) {
			$guanxi = "爸爸";
		}else if($stu['pard'] == 5) {
			$guanxi = "家长";
		}
		$bjname = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where weid = :weid AND sid=:sid", array(':weid' => $weid, ':sid' => $students['bj_id']))['sname'];
		$leave1[$key]['bjname'] = $bjname;
		$leave1[$key]['tname'] = $teach['tname'] ?  $teach['tname'] : '管理员';
		$leave1[$key]['sname'] = $students['s_name'].$guanxi;
		$leave1[$key]['icon'] = $students['icon']?tomedia($students['icon']):tomedia($school['spic']);
		$leave1[$key]['time'] = date('m-d H:i', $row['createtime']);	
	}
	$result['data'] = $leave1;
	die(json_encode($result));
}elseif($operation == 'savehuifu'){
	if(!$_GPC['userid']){
		$data ['msg'] ="{$_GPC['tname']}老师尚未绑定微信,请在手机端绑定微信后方可继续操作！" ;	
		$data ['result'] = false;
		die(json_encode($data));
	}
	$huifu = $_GPC['huifu'];
	$id = $_GPC['id'];
	$datatemp = array(
		'huifu' =>$huifu,
	);
	pdo_update($this->table_courseorder, $datatemp, array('id' => $id));
	$this->sendMobileYzxxhf($id, $schoolid, $weid);
	die(json_encode(array(
		'result' => true,
		'msg' => '邮件回复成功！'
	)));
}elseif($operation == 'getjlxstulist'){
	$starttime = mktime(0,0,0,date("m"),date("d"),date("Y"));
	$endtime = $starttime + 86399;
	$condition = " AND createtime > '{$starttime}' AND createtime < '{$endtime}'";
	$bj_id = $_GPC['bjid'];
	//重构数据库语句，优化查询方式，提升页面速度
	$Sql = "  SELECT s.icon,s.id,s.s_name,s.bj_id,ca.id as ischeck,  ca.createtime as amtime,cp.id as ischeckpm,cp.createtime as pmtime,dxqr.id as dxqrcheck,lxqr.id as lxqrcheck  FROM " . GetTableName('students') . " as s LEFT JOIN ( select id,createtime,sid FROM ( SELECT * FROM ".GetTableName('checklog')." WHERE  leixing = 1  And isconfirm = 1 $condition  ORDER BY createtime ASC ) as cal  GROUP BY cal.sid  ) as ca ON ca.sid = s.id  LEFT JOIN ( select id,createtime,sid FROM ( SELECT * FROM ".GetTableName('checklog')." WHERE  leixing = 2  And isconfirm = 1 $condition  ORDER BY createtime ASC ) as cpl  GROUP BY cpl.sid  ) as cp ON cp.sid = s.id LEFT JOIN ( select id,createtime,sid FROM ( SELECT * FROM ".GetTableName('checklog')." WHERE  leixing = 1  And isconfirm = 2 $condition  ORDER BY createtime ASC ) as dxqrl  GROUP BY dxqrl.sid  ) as dxqr ON dxqr.sid = s.id LEFT JOIN ( select id,createtime,sid FROM ( SELECT * FROM ".GetTableName('checklog')." WHERE  leixing = 2  And isconfirm = 2 $condition  ORDER BY createtime ASC ) as lxqrl  GROUP BY lxqrl.sid  ) as lxqr ON lxqr.sid = s.id  where s.schoolid = '{$schoolid}' AND s.bj_id = '{$bj_id}'   ORDER BY id ASC  ";
	
	$students = pdo_fetchall($Sql);
	$rlogmub = pdo_fetchcolumn("SELECT count(id) FROM " . tablename($this->table_checklog) . " where bj_id = :bj_id $condition ", array(':bj_id' => $bj_id));
	$nlogmub = pdo_fetchcolumn("SELECT count(id) FROM " . tablename($this->table_checklog) . " where bj_id = :bj_id $condition ", array(':bj_id' => $bj_id));
	$snum = count($students);
	$wqdnum = 0;
	$yqdnum = 0;
	$wqdnumpm = 0;
	$yqdnumpm = 0;
	$dxwqrnum = 0;
	$lxwqrnum = 0;
	foreach($students as $index => $row){

		if(!$row['ischeck']){
			$wqdnum ++;
		}else{
			$yqdnum ++;
		}
		if(!$row['ischeckpm']){
			$wqdnumpm ++;
		}else{
			$yqdnumpm ++;
		}
		if($row['dxqrcheck']){
			$dxwqrnum ++;
		}
		if($row['lxqrcheck']){
			$lxwqrnum ++;
		}
	}
	if($students){
		foreach ($students as $key => $value) {
			$students[$key]['icon'] = $value['icon'] ? tomedia($value['icon']) : tomedia($school['spic']);
		}
	}
	$xsqj = pdo_fetchall("SELECT s.s_name,s.icon FROM " . tablename($this->table_leave) . " as l LEFT JOIN ".GetTableName('students')." as s ON s.id=l.sid where :schoolid = l.schoolid And :weid = l.weid And :tid = l.tid And :bj_id = l.bj_id And :isliuyan = l.isliuyan AND l.status != :status AND ((l.startime1 >= '{$starttime}' AND l.startime1 <='{$endtime}') OR (l.endtime1 >= '{$starttime}' AND l.endtime1 <='{$endtime}')) ORDER BY l.status ASC , l.createtime DESC", array(
		':weid' => $weid,
		':schoolid' => $schoolid,
		':bj_id' => $bj_id,
		':tid' => 0,
		':isliuyan' => 0,
		':status' => 3
		));
	if($xsqj){
		foreach ($xsqj as $key => $value) {
			$xsqj[$key]['icon'] = $value['icon'] ? tomedia($value['icon']) : tomedia($school['spic']);
		}
	}
	include $this->template ( 'web/usercenter/jlxlist' );
	die;
}elseif($operation == 'saveqd'){
	$sids = $_GPC['data'];
	if($sids){
		$rs = 0;
		foreach ($sids as $key => $value) {
			if ($value['leixing'] == 1){
				$type = "进校";
			}else{
				$type = "离校";
			}
			if($value['logid'] != 0){
				pdo_update($this->table_checklog, array('isconfirm' => 1), array('id' => $value['logid']));
			}
			$data = array(
				'weid' => $weid,
				'schoolid' => $schoolid,
				'sid' => $value['sid'],
				'bj_id' => $_GPC['bjid'],
				'pard' => 11,
				'checktype' => 2,
				'isconfirm' => 1,
				'type' => $type,
				'leixing' =>  $value['leixing'],
				'qdtid' =>  $_GPC['tid'],
				'createtime' => time()
			);
			$pard = 11;
			pdo_insert($this->table_checklog, $data);
			$logid = pdo_insertid();
			$macid = 'wechatSign';
			if(is_showyl()){
				$this->sendMobileJxlxtz_yl($schoolid, $weid,$value['sid'], $logid,$macid);
			}else{
				$this->sendMobileFzqdtx($schoolid,$weid,$_GPC['bjid'],$value['sid'],$type,$value['leixing'],$logid,$pard);
			}
			$rs ++;
		}
		$result['info'] = "成功签到".$rs."个学生";
        $result['signNum'] =$rs;
		$actop = 'bqxs';
		$userid = $_GPC['userid'];
		$point = PointAct($weid,$schoolid,$userid,$actop);
		$point1 = PointMission($weid,$schoolid,$userid,$actop);
	}else{
		$result['info'] = "您没有选择学生！";
	}
	die ( json_encode ( $result ) );
}elseif($operation == 'getLyList'){
	$lyList = pdo_fetchall("SELECT id,touserid,userid FROM " . tablename($this->table_leave) . " WHERE weid = :weid AND schoolid =:schoolid  AND isfrist = :isfrist And :isliuyan = isliuyan And :id < id And (userid = :userid OR touserid = :touserid) ORDER BY id DESC", array(
		':weid' => $weid,
		':schoolid' => $schoolid,
		':isfrist' => 1,
		':isliuyan' => 2,
		':userid' => $_GPC['userid'],
		':touserid' => $_GPC['userid'],
		':id' => $_GPC['maxleaveid'],
	));
	foreach ($lyList as $key => $row) {
		if($row['userid'] == $_GPC['userid']){ //发起者(老师)
			$user = pdo_fetch("SELECT pard,sid,tid FROM " . tablename($this->table_user) . " where schoolid = :schoolid AND id = :id", array(':schoolid' => $schoolid, ':id' => $row['touserid']));
			if($user['sid']){
				$student = pdo_fetch("SELECT s_name,bj_id,icon FROM " . tablename($this->table_students) . " where schoolid = :schoolid AND id = :id", array(':schoolid' => $schoolid, ':id' => $user['sid']));
				$bj = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where schoolid = :schoolid AND sid = :sid", array(':schoolid' => $schoolid, ':sid' => $student['bj_id']));
				$lyList[$key]['bjname'] = $bj['sname'];
				$lyList[$key]['name'] = $student['s_name'].get_guanxi($user['pard']);
				$lyList[$key]['icon'] = $student['icon']?tomedia($student['icon']):tomedia($school['spic']);
			}
			if($user['tid']){
				$teacher = pdo_fetch("SELECT tname,thumb,fz_id FROM " . tablename($this->table_teachers) . " where schoolid = :schoolid AND id = :id", array(':schoolid' => $schoolid, ':id' => $user['tid']));
				$fz = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where schoolid = :schoolid AND sid = :sid", array(':schoolid' => $schoolid, ':sid' => $teacher['fz_id']));
				$lyList[$key]['name'] = $teacher['tname'].'老师';
				$lyList[$key]['bjname'] = $fz['sname'];
				$lyList[$key]['icon'] = $teacher['thumb']?tomedia($teacher['thumb']):tomedia($school['tpic']);
			}	
			$lyList[$key]['count'] = pdo_fetchcolumn("SELECT count(id) FROM " . tablename($this->table_leave) . " where schoolid = :schoolid AND leaveid = :leaveid", array(':schoolid' => $schoolid, ':leaveid' => $row['id']));	

		}	
		if($row['touserid'] == $_GPC['userid']){ //接收者(家长)
			$user = pdo_fetch("SELECT pard,sid,tid FROM " . tablename($this->table_user) . " where schoolid = :schoolid AND id = :id", array(':schoolid' => $schoolid, ':id' => $row['userid']));
			if($user['sid']){
				$student = pdo_fetch("SELECT s_name,bj_id,icon FROM " . tablename($this->table_students) . " where schoolid = :schoolid AND id = :id", array(':schoolid' => $schoolid, ':id' => $user['sid']));
				$bj = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where schoolid = :schoolid AND sid = :sid", array(':schoolid' => $schoolid, ':sid' => $student['bj_id']));
				$lyList[$key]['bjname'] = $bj['sname'];
				$lyList[$key]['name'] = $student['s_name'].get_guanxi($user['pard']);
				$lyList[$key]['icon'] = $student['icon']?tomedia($student['icon']):tomedia($school['spic']);
			}
			if($user['tid']){
				$teacher = pdo_fetch("SELECT tname,thumb,fz_id FROM " . tablename($this->table_teachers) . " where schoolid = :schoolid AND id = :id", array(':schoolid' => $schoolid, ':id' => $user['tid']));
				$fz = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where schoolid = :schoolid AND sid = :sid", array(':schoolid' => $schoolid, ':sid' => $teacher['fz_id']));
				$lyList[$key]['name'] = $teacher['tname'].'老师';
				$lyList[$key]['bjname'] = $fz['sname'];
				$lyList[$key]['icon'] = $teacher['thumb']?tomedia($teacher['thumb']):tomedia($school['tpic']);
			}					
			$lyList[$key]['count'] = pdo_fetchcolumn("SELECT count(id) FROM " . tablename($this->table_leave) . " where schoolid = :schoolid AND leaveid = :leaveid", array(':schoolid' => $schoolid, ':leaveid' => $row['id']));	
		}		
	}
	if($lyList){
		$result['result'] = true;
		$result['data'] = $lyList;
	}else{
		$result['result'] = false;
		$result['msg'] = '没有新数据';
	}
	die(json_encode($result));
}elseif($operation == 'getAllLyInfo') {
	$id = $_GPC['leaveid'];
	$list = pdo_fetchall("SELECT * FROM " . tablename($this->table_leave) . " where schoolid = :schoolid AND leaveid = :leaveid ORDER BY createtime ASC ", array(':schoolid' => $schoolid, ':leaveid' => $id));	
	foreach ($list as $k => $v) {
		
		if(!empty($v['picurl'])){
			$img_url[$iii] = tomedia($v['picurl']);
			$iii = $iii + 1 ;
		}
		if($v['userid'] == $_GPC['userid']){
			$list[$k]['isown'] = true;
			$users = pdo_fetch("SELECT pard,sid,tid,userinfo FROM " . tablename($this->table_user) . " where schoolid = :schoolid AND id = :id", array(':schoolid' => $schoolid, ':id' => $v['touserid']));
		}
		if($v['touserid'] == $_GPC['userid']){
			$list[$k]['isown'] = false;
			$users = pdo_fetch("SELECT pard,sid,tid,userinfo FROM " . tablename($this->table_user) . " where schoolid = :schoolid AND id = :id", array(':schoolid' => $schoolid, ':id' => $v['userid']));
			if($v['isread'] ==1){
				pdo_update($this->table_leave, array('isread' =>  2), array('id' =>  $v['id']));
			}
		}	
		$students = pdo_fetch("SELECT s_name,icon,bj_id FROM " . tablename($this->table_students) . " where schoolid = :schoolid AND id = :id", array(':schoolid' => $schoolid, ':id' => $users['sid']));
		$teacher = pdo_fetch("SELECT tname,thumb FROM " . tablename($this->table_teachers) . " where schoolid = :schoolid AND id = :id", array(':schoolid' => $schoolid, ':id' => $users['tid']));
		mload()->model('user');
		$gx = check_gx($users['pard']);
		if($users['userinfo']){
			$userinfo = iunserializer($users['userinfo']);
			$name = $userinfo['name'];
			$guanxi = empty($gx)?'':$gx.$name;
		}
		
		$list[$k]['time'] = sub_day($v['createtime']);
		if ($users['sid']){
			$nowbji = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where sid = :sid", array(':sid' => $students['bj_id']));
			$list[$k]['name'] = $students['s_name'].$guanxi.'('.$nowbji['sname'].')';
			$list[$k]['icon'] = empty($students['icon']) ? $school['spic'] : $students['icon'];					
		}else{
			$list[$k]['name'] = $teacher['tname']." 老师";
			$list[$k]['icon'] =  empty($teacher['thumb']) ? $school['tpic'] : $teacher['thumb'];					
		}
		if(!empty($v['audio'])){
			$audios = iunserializer($v['audio']);
			$list[$k]['audios'] = tomedia($audios['audio'][0]);
			$list[$k]['audioTime'] = $audios['audioTime'][0];
		}				
		if(!empty($v['picurl'])){
			$list[$k]['picurl'] = tomedia($v['picurl']);
		}				
	}
	$lasttime = pdo_fetch("SELECT id,createtime FROM " . tablename($this->table_leave) . " where weid = :weid AND leaveid = :leaveid ORDER BY createtime DESC ", array(':weid' => $weid, ':leaveid' => $id));
	$result['data'] = $list;
	$result['lasttime'] = $lasttime;
	$result['leaveid'] = $id;
	die(json_encode($result));
}elseif($operation == 'getOneLyInfo'){
	$lastmsg = pdo_fetch("SELECT * FROM " . tablename($this->table_leave) . " where schoolid = '{$schoolid}' And leaveid = '{$_GPC['leaveid']}' And touserid = '{$_GPC['userid']}' And createtime > '{$_GPC['lasttime']}'");
	$datas = [];
	if (!empty($lastmsg)) {
		if (!empty($lastmsg['audio'])) {
			$audios = iunserializer($lastmsg['audio']);
			$urls = $_W['attachurl'];
			$datas ['content'] = $urls.$audios['audio'][0];
			$datas ['mediaTime'] = $audios['audioTime'][0];
			$datas ['lastid'] = $lastmsg['id'];
			$datas ['touserid'] = $lastmsg['touserid'];
			$datas ['time'] = sub_day($lastmsg['createtime']);
			$datas ['type'] = 1;
		}
		if (!empty($lastmsg['picurl'])) {
			$picurl = $lastmsg['picurl'];
			$urls = $_W['attachurl'];
			$datas ['content'] =tomedia($picurl);
			$datas ['mediaTime'] = $audios['audioTime'][0];
			$datas ['lastid'] = $lastmsg['id'];
			$datas ['touserid'] = $lastmsg['touserid'];
			$datas ['time'] = sub_day($lastmsg['createtime']);
			$datas ['type'] = 3;
		} elseif (!empty($lastmsg['conet'])) {
			$datas ['touserid'] = $lastmsg['touserid'];
			$datas ['lastid'] = $lastmsg['id'];
			$datas ['content'] = $lastmsg['conet'];
			$datas ['time'] = sub_day($lastmsg['createtime']);
			$datas ['mediaTime'] = 1;
			$datas['type'] = 2;
		}
		$datas['id'] = $lastmsg['leaveid'];
		$datas['time'] = sub_day($lastmsg['createtime']);
		$datas['lasttime'] = $lastmsg['createtime'];
		pdo_update($this->table_leave, array('isread' =>  2), array('touserid' => $_GPC['userid'],'leaveid'=>$lastmsg['leaveid']));
		$datas['result'] = true;
	} else {
		$datas['result'] = false;
	}
	die(json_encode($datas));
}elseif($operation == 'saveLy'){
	if(!$_GPC['userid']){
		$data ['msg'] ="{$_GPC['tname']}老师尚未绑定微信,请在手机端绑定微信后方可继续操作！" ;	
		$data ['result'] = false;
		die(json_encode($data));
	}
	$thisleave = pdo_fetch("SELECT userid,touserid FROM " . tablename($this->table_leave) . " where schoolid = :schoolid AND id = :id ", array(':schoolid' => $schoolid, ':id' => $_GPC['leaveid']));
	$data = array(
		'weid' =>  $weid,
		'schoolid' => $schoolid,
		'leaveid' =>  $_GPC['leaveid'],
		'userid' => $_GPC['userid'],
		'touserid' => $_GPC['userid']==$thisleave['touserid'] ? $thisleave['userid'] : $thisleave['touserid'],
		'conet' => $_GPC['content'],
		'isliuyan'=>2,
		'createtime' => time()
	);
	pdo_insert($this->table_leave, $data);
	$leave_id = pdo_insertid();
	$this->sendMobileLyhf($_GPC['leaveid'], $schoolid, $weid);
	$datas ['result'] = true;
	$datas ['msg'] = '成功发送留言信息，请勿重复发送！';
	die(json_encode($datas));
}else{
    $this->imessage('操作失败, 非法访问.');
}
include $this->template ( 'web/usercenter/index' );

//获取指定日期当月最后一天最后一秒
function endDayOfMonth($date) {
    list($year, $month) = explode('-',$date);
    $nextYear = $year;
    $nexMonth = $month+1;
    //如果是年底12月 下个月就是1月
    if($month == 12) {
        $nexMonth = "01";
        $nextYear = $year+1;
    }
    $end = "{$nextYear}-{$nexMonth}-01 00:00:00";
    $endTimeStamp = strtotime($end) - 1 ;
    return $endTimeStamp;
}

function getInfo($schoolid,$kcid,$nowtid){
	$tpic = pdo_fetch("SELECT tpic FROM " . GetTableName('index') . " where id = :id ", array(':id' => $schoolid))['tpic'];
	$kcinfo = pdo_fetch("SELECT * FROM " . GetTableName('tcourse') . " WHERE id='{$kcid}'");
	$kcinfo['thumb'] = tomedia($kcinfo['thumb']);
	//获取当前课程下有多少老师
	$teanum = count(explode(',',$kcinfo['tid']));
	//当前课程下的学生总数
	$stunum = pdo_fetchcolumn("SELECT count(DISTINCT sid) FROM " . GetTableName('coursebuy') . " WHERE kcid = '{$kcid}' And is_change != 1 ");
	//授课状态
	if($kcinfo['start'] > time()){
		$kcstatus = '未开始';
	}elseif($kcinfo['start'] < time() && $kcinfo['end'] > time()){
		$kcstatus = '进行中';
	}elseif($kcinfo['end'] < time()){
		$kcstatus = '已结束';
	}
	//授课老师
	$tealist = pdo_fetchall("SELECT thumb FROM " . GetTableName('teachers') . " WHERE FIND_IN_SET(id,'{$kcinfo['tid']}')");
	foreach ($tealist as &$v) {
		$v['thumb'] = $v['thumb'] ? tomedia($v['thumb']) : tomedia($tpic);
	}
	//当前老师总签到 qdnum-签到总数 zjqdnum-主讲签到数量 costnum-签到我的
	$qdinfo = pdo_fetch("SELECT count(id) as qdnum, IFNULL(SUM(case when ismaster_tid = 1 then 1 else 0 end),0) as zjqdnum, IFNULL(SUM(costnum),0) as costnum FROM " . GetTableName('kcsign') . " WHERE kcid = '{$kcid}' And tid = '{$nowtid}' AND status = 2");
	//辅导签到数量
	$fdqdnum = $qdinfo['qdnum'] - $qdinfo['zjqdnum'];
	//作为主讲老师的排课数量
	$kcbiaonum = pdo_fetchcolumn("SELECT count(id) FROM " . GetTableName('kcbiao') . " WHERE kcid = '{$kcid}' AND tid = '{$nowtid}' ");
	//进度条数据
	$jdtsj = $qdinfo['zjqdnum'] / $kcbiaonum* 100;
	//课程评价统计
	$pj = pdo_fetch("SELECT IFNULL(SUM(case when type = 1 then 1 else 0 end),0) as pf, IFNULL(SUM(case when type = 2 then 1 else 0 end),0) as pl FROM " . GetTableName('kcpingjia') . " WHERE kcid = '{$kcid}' And tid = '{$nowtid}'");
	$data = array(
		'kcinfo' => $kcinfo,
		'teanum' => $teanum,
		'stunum' => $stunum,
		'kcstatus' => $kcstatus,
		'tealist' => $tealist,
		'qdinfo' => $qdinfo,
		'fdqdnum' => $fdqdnum,
		'kcbiaonum' => $kcbiaonum,
		'jdtsj' => $jdtsj,
		'pj' => $pj,
	);
	return $data;
}

function getStatus($key){
	if($key % 8 == 0){
		$status = 'success';
	}elseif($key % 8 == 1){
		$status = 'info';
	}elseif($key % 8 == 2){
		$status = 'danger';
	}elseif($key % 8 == 3){
		$status = 'primary';
	}elseif($key % 8 == 4){
		$status = 'warning';
	}elseif($key % 8 == 5){
		$status = 'black';
	}elseif($key % 8 == 6){
		$status = 'light';
	}elseif($key % 8 == 7){
		$status = 'secondary';
	}
	return $status;
}
?>