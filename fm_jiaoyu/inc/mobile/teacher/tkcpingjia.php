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
$it = pdo_fetch("SELECT id,tid FROM " . GetTableName('user') . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0));
$school = pdo_fetch("SELECT title,is_recordmac,style3,headcolor,spic,tpic FROM " . GetTableName('index') . " where weid = :weid AND id=:id", array(':weid' => $weid, ':id' => $schoolid));
$word = $this->GetSensitiveWord($weid);
$tid_global = $it['tid'];
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if($operation == 'display'){
	if(!empty($it)){
		$ksid = $_GPC['ksid'];$fristid = $_GPC['ksid'];
		$ksinfo = pdo_fetch("SELECT * FROM " . GetTableName('kcbiao') . " WHERE schoolid = '{$schoolid}' And id = '{$ksid}' ");
		$kcid = $ksinfo['kcid'];
		$kcinfo = pdo_fetch("SELECT name,allow_pl,sign_pl_set FROM " . GetTableName('tcourse') . " WHERE schoolid = '{$schoolid}' And id = '{$kcid}' ");
		$signset = pdo_fetch("SELECT * FROM " . GetTableName('kc_signset') . " WHERE id = :id ", array(':id' => $kcinfo['sign_pl_set']));
		$allks = GetOneKcKsOrder($kcid);//左侧课时列表
		include $this->template(''.$school['style3'].'/kc/kcpingjia');	
	}else{
		session_destroy();
		$stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
		header("location:$stopurl");
	}
}
if($operation == 'cheackteasign'){
	$ksid = $_GPC['ksid'];$tid = $_GPC['tid'];
	$teasign = pdo_fetch("SELECT id FROM " . GetTableName('kcsign') . " WHERE schoolid = '{$schoolid}' And ksid = '{$ksid}' And sid = 0 And tid = '{$tid}' And status = 2  ");
	if(!empty($teasign)){
		$result['msg'] = '已签！';
		$result['result'] = true;
	}else{
		$result['msg'] = '未签！';
		$result['result'] = false;
	}
	die(json_encode($result));
}
if($operation == 'ksheader'){
	$ksid = $_GPC['ksid'];
	$ksinfo = pdo_fetch("SELECT * FROM " . GetTableName('kcbiao') . " WHERE schoolid = '{$schoolid}' And id = '{$ksid}' ");
	$kcid = $ksinfo['kcid'];
	$kcinfo = pdo_fetch("SELECT name,allow_pl,sign_pl_set FROM " . GetTableName('tcourse') . " WHERE schoolid = '{$schoolid}' And id = '{$kcid}' ");
	$sdinfo = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE schoolid = '{$schoolid}' And sid = '{$ksinfo['sd_id']}' ");
	$thiskstea = '';
	$allkstea = pdo_fetchall("SELECT tid FROM " . GetTableName('kcsign') . " WHERE schoolid = '{$schoolid}' And ksid = '{$ksid}' And sid = 0 And tid > 0 And status = 2  ");
	if(!empty($allkstea)){
		foreach($allkstea as $key => $row){
			$teainfo = pdo_fetch("SELECT tname FROM " . GetTableName('teachers') . " WHERE id = :id ", array(':id' => $row['tid']));
			if(!empty($teainfo)){
				$thiskstea .= $teainfo['tname'].'/';
			}
		}
	}
	$thiskstea = rtrim($thiskstea,'/');
	$order = GetOneKcKsOrder($kcid,$ksid);
	$nuber = $order['nuber'];$kspingfen = 0;$zf = 0;$pftimes = 0;
	$allpf = pdo_fetchall("SELECT star,sid FROM " . GetTableName('kcpingjia') . " WHERE schoolid = '{$schoolid}' And sid > 0 And totid > 0 And ksid = '{$ksid}' And type = 1 And tid = 0 And star >0 ");
	foreach($allpf as $key => $row){
		$stu = pdo_fetch("SELECT id FROM " . GetTableName('students') . " WHERE id = '{$row['sid']}' ");
		if(!empty($stu)){
			$zf = $zf + $row['star'];
			$pftimes++;
		}
	}
	if($pftimes > 0){$kspingfen = round($zf/$pftimes,1);}
	include $this->template(''.$school['style3'].'/kc/kc_pj_temp');
}

if($operation == 'stu_box'){	
	$ksid = $_GPC['ksid'];
	$allplstu = pdo_fetchall("SELECT sid,signtype FROM " . GetTableName('kcsign') . " WHERE schoolid = '{$schoolid}' And ksid = '{$ksid}' And sid > 0 And status = 2  ");
	foreach($allplstu as $key => $row){//循环本节已签到学生
		$stuinfo = pdo_fetch("SELECT s_name,icon FROM " . GetTableName('students') . " WHERE id = '{$row['sid']}' ");
		$allplstu[$key]['s_name'] = $stuinfo['s_name'];
		$allplstu[$key]['icon'] = !empty($stuinfo['icon'])?tomedia($stuinfo['icon']):tomedia($school['spic']);
		$tostu = pdo_fetch("SELECT id FROM " . GetTableName('kcpingjia') . " WHERE schoolid = '{$schoolid}' And tosid = '{$row['sid']}' And ksid = '{$ksid}' ");//老师评学生
		$totea = pdo_fetch("SELECT id FROM " . GetTableName('kcpingjia') . " WHERE schoolid = '{$schoolid}' And totid != 0 And sid = '{$row['sid']}' And ksid = '{$ksid}'  ");//学生评老师
		if(!empty($tostu)){
			$allplstu[$key]['tostu'] = true;
		}else{
			$allplstu[$key]['tostu'] = false;
		}
		if(!empty($totea)){
			$allplstu[$key]['totea'] = true;
		}else{
			$allplstu[$key]['totea'] = false;
		}
	}
	$stunub  = count($allplstu);
	include $this->template(''.$school['style3'].'/kc/kc_pj_temp');
}
if($operation == 'on_pl_stu'){
	$ksid = $_GPC['ksid'];$sid = $_GPC['sid'];
	$ksinfo = pdo_fetch("SELECT * FROM " . GetTableName('kcbiao') . " WHERE schoolid = '{$schoolid}' And id = '{$ksid}' ");
	$kcid = $ksinfo['kcid'];
	$kcinfo = pdo_fetch("SELECT name,allow_pl,sign_pl_set FROM " . GetTableName('tcourse') . " WHERE id = '{$kcid}' ");
	$signset = pdo_fetch("SELECT * FROM " . GetTableName('kc_signset') . " WHERE id = :id ", array(':id' => $kcinfo['sign_pl_set']));
	$sdinfo = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE schoolid = '{$schoolid}' And sid = '{$ksinfo['sd_id']}' ");
	$stuinfo = pdo_fetch("SELECT sex,s_name,icon FROM " . GetTableName('students') . " WHERE  id = '{$sid}' ");
	$stuimg=  !empty($stuinfo['icon'])?tomedia($stuinfo['icon']):tomedia($school['spic']);
	$order = GetOneKcKsOrder($kcid,$ksid);
	$nuber = $order['nuber'];
	//取所有学生对本节老师打分情况
	$allkstea = pdo_fetchall("SELECT tid FROM " . GetTableName('kcsign') . " WHERE schoolid = '{$schoolid}' And ksid = '{$ksid}' And sid = 0 And tid > 0 And status = 2  ");
	if(!empty($allkstea)){
		foreach($allkstea as $key => $row){
			$teainfo = pdo_fetch("SELECT tname FROM " . GetTableName('teachers') . " WHERE id = :id ", array(':id' => $row['tid']));
			$allkstea[$key]['tname'] = $teainfo['tname'];
			$allkstea[$key]['checkpls'] = false;
			$checkpls = pdo_fetch("SELECT id FROM " . GetTableName('kcpingjia') . " WHERE schoolid = '{$schoolid}' And sid = '{$sid}' And ksid = '{$ksid}' And type = 2 And is_master = 1");//学生评论本节
			$checkplsstart = pdo_fetch("SELECT star FROM " . GetTableName('kcpingjia') . " WHERE schoolid = '{$schoolid}' And sid = '{$sid}' And totid = '{$row['tid']}' And ksid = '{$ksid}' And type = 1");//学生评星此老师
			if(!empty($checkpls) || !empty($checkplsstart)){$allkstea[$key]['checkpls'] = true;}
			$allkstea[$key]['star'] = getStarStyle($checkplsstart['star']);
		}
	}
	$allksteacunt = count($allkstea);
	$checkpxs = pdo_fetch("SELECT id FROM " . GetTableName('kcpingjia') . " WHERE schoolid = '{$schoolid}' And tosid = '{$sid}' And ksid = '{$ksid}' And tid > 0 ");//老师评学生打分
	$checkpxsstar = pdo_fetch("SELECT star FROM " . GetTableName('kcpingjia') . " WHERE schoolid = '{$schoolid}' And tosid = '{$sid}' And ksid = '{$ksid}' And tid > 0 And type = 1 And is_master = 1");//老师评学生评星
	$stustar = getStarStyle($checkpxsstar['star']);
	//查询老师给本学生评价 图文 含回复
	$teaplxs = pdo_fetchall("SELECT * FROM " . GetTableName('kcpingjia') . " WHERE schoolid = '{$schoolid}' And tosid = '{$sid}' And ksid = '{$ksid}' And type = 2 And tid > 0 And is_master = 1");
	if($teaplxs){
		foreach($teaplxs as $key => $row){
			$msteainfo = pdo_fetch("SELECT tname,thumb FROM " . GetTableName('teachers') . " WHERE id = :id ", array(':id' => $row['tid']));
			$teaplxs[$key]['mastname'] = $msteainfo['tname'];
			$teaplxs[$key]['masthumb'] = !empty($msteainfo['thumb'])?tomedia($msteainfo['thumb']):tomedia($school['tpic']);
			$teaplxs[$key]['mastime'] = sub_days($row['createtime']);
			if($row['photo']){
				if(strstr($row['photo'],',')){
					$photoarr = explode(',',$row['photo']);
					$teaplxs[$key]['photo'] = '';
					foreach($photoarr as $ph){
						$teaplxs[$key]['photo'] .= '<div class="photo_box" onclick="wxImageShow(this)"><img class="img" src="'.tomedia($ph).'"/></div>';
					}
				}else{
					$teaplxs[$key]['photo'] = '<div class="photo_box" onclick="wxImageShow(this)"><img class="img" src="'.tomedia($row['photo']).'"/></div>';
				}
			}
			if($row['audio']){
				$teaplxs[$key]['audio'] = tomedia($row['audio']);
			}
			$teaplxs[$key]['allrelpy'] =  pdo_fetchall("SELECT * FROM " . GetTableName('kcpingjia') . " WHERE  masterid = '{$row['id']}' ORDER BY createtime DESC");
			foreach($teaplxs[$key]['allrelpy'] as $k => $r){
				$teaplxs[$key]['allrelpy'][$k]['reptime'] = sub_days($r['createtime']);
				if($r['sid'] > 0 && $r['tid'] == 0){
					$userinfo = pdo_fetch("SELECT realname,mobile,pard FROM ".GetTableName('user')." WHERE  id = '{$r['userid']}'  ");
					$guanxi = get_guanxi($userinfo['pard']);
					$teaplxs[$key]['allrelpy'][$k]['mobile'] = $userinfo['mobile'];
					$restuinfo = pdo_fetch("SELECT s_name,icon FROM " . GetTableName('students') . " WHERE id = :id ", array(':id' => $r['sid']));
					$teaplxs[$key]['allrelpy'][$k]['name'] = $restuinfo['s_name'].'('.$guanxi.')';
					$teaplxs[$key]['allrelpy'][$k]['icon'] = !empty($restuinfo['icon'])?tomedia($restuinfo['icon']):tomedia($school['spic']);
				}
				if($r['tid'] > 0 && $r['sid'] == 0){
					$reteainfo = pdo_fetch("SELECT tname,thumb FROM " . GetTableName('teachers') . " WHERE id = :id ", array(':id' => $r['tid']));
					$teaplxs[$key]['allrelpy'][$k]['name'] = $reteainfo['tname'];
					$teaplxs[$key]['allrelpy'][$k]['icon'] = !empty($reteainfo['thumb'])?tomedia($reteainfo['thumb']):tomedia($school['tpic']);
				}
			}
			$teaplxs[$key]['allrelpyss'] = count($teaplxs[$key]['allrelpy']);
		}
	}
	
	//查询本学生给课时评价 图文 含回复
	$stuplks = pdo_fetchall("SELECT * FROM " . GetTableName('kcpingjia') . " WHERE schoolid = '{$schoolid}' And sid = '{$sid}' And ksid = '{$ksid}' And type = 2 And tid = 0 And is_master = 1");
	if($stuplks){
		foreach($stuplks as $key => $ite){
			$msstuinfo = pdo_fetch("SELECT s_name,icon FROM " . GetTableName('students') . " WHERE id = :id ", array(':id' => $ite['sid']));
			$theuser = pdo_fetch("SELECT realname,mobile,pard FROM ".GetTableName('user')." WHERE  id = '{$ite['userid']}'  ");
			$guanxis = get_guanxi($theuser['pard']);
			$stuplks[$key]['mastsname'] = !empty($guanxis)?$msstuinfo['s_name'].'('.$guanxis.')':$msstuinfo['s_name'];
			$stuplks[$key]['masthumb'] = !empty($msstuinfo['icon'])?tomedia($msstuinfo['icon']):tomedia($school['spic']);
			$stuplks[$key]['mastime'] = sub_days($ite['createtime']);
			if($ite['photo']){
				if(strstr($ite['photo'],',')){
					$photoarr = explode(',',$ite['photo']);
					$stuplks[$key]['photo'] = '';
					foreach($photoarr as $ph){
						$stuplks[$key]['photo'] .= '<div class="photo_box" onclick="wxImageShow(this)"><img class="img" src="'.tomedia($ph).'"/></div>';
					}
				}else{
					$stuplks[$key]['photo'] = '<div class="photo_box" onclick="wxImageShow(this)"><img class="img" src="'.tomedia($ite['photo']).'"/></div>';
				}
			}
			if($ite['audio']){
				$stuplks[$key]['audio'] = tomedia($ite['audio']);
			}
			$stuplks[$key]['allrelpy'] =  pdo_fetchall("SELECT * FROM " . GetTableName('kcpingjia') . " WHERE  masterid = '{$ite['id']}' ORDER BY createtime DESC");
			foreach($stuplks[$key]['allrelpy'] as $k => $r){
				$stuplks[$key]['allrelpy'][$k]['reptime'] = sub_days($r['createtime']);
				if($r['sid'] > 0 && $r['tid'] == 0){
					$restuinfo = pdo_fetch("SELECT s_name,icon FROM " . GetTableName('students') . " WHERE id = :id ", array(':id' => $r['sid']));
					$userinfo = pdo_fetch("SELECT realname,mobile,pard FROM ".GetTableName('user')." WHERE  id = '{$r['userid']}'  ");
					$guanxi = get_guanxi($userinfo['pard']);
					$stuplks[$key]['allrelpy'][$k]['name'] = $restuinfo['s_name'].'('.$guanxi.')';
					$stuplks[$key]['allrelpy'][$k]['icon'] = !empty($restuinfo['icon'])?tomedia($restuinfo['icon']):tomedia($school['spic']);
				}
				if($r['tid'] > 0 && $r['sid'] == 0){
					$reteainfo = pdo_fetch("SELECT tname,thumb FROM " . GetTableName('teachers') . " WHERE id = :id ", array(':id' => $r['tid']));
					$stuplks[$key]['allrelpy'][$k]['name'] = $reteainfo['tname'];
					$stuplks[$key]['allrelpy'][$k]['icon'] = !empty($reteainfo['thumb'])?tomedia($reteainfo['thumb']):tomedia($school['tpic']);
				}
			}
			$stuplks[$key]['allrelpyss'] = count($stuplks[$key]['allrelpy']);
		}
	}
	
	
	include $this->template(''.$school['style3'].'/kc/kc_pj_temp');
}
if($operation == 'subpl'){
	$ksid = intval($_GPC['ksid']);$sid = intval($_GPC['sid']);$tid = intval($_GPC['tid']);$editid = intval($_GPC['editid']);$subtype = trim($_GPC['subtype']);
	$thisstar = pdo_fetch("SELECT id FROM ".GetTableName('kcpingjia')." WHERE  ksid = '{$ksid}'  And type =1 And sid = 0  And tosid = '{$sid}' ");
	if(!empty($thisstar) && $subtype == 'new'){
		$result['msg'] = '抱歉，已有其他老师已为该生打分了！,如果需评论请在其他老师的评论下回复即可';
		$restrueult['result'] = true;
		die(json_encode($result));
	}
	$ksinfo = pdo_fetch("SELECT kcid FROM " . GetTableName('kcbiao') . " WHERE schoolid = '{$schoolid}' And id = '{$ksid}' ");
	$kcid = $ksinfo['kcid'];
	$data = array(
		'weid' => $weid,
		'schoolid' => $schoolid,
		'kcid' => $kcid,
		'ksid' => $ksid,
		'userid' => intval($_GPC['userid']),
		'tid' => $tid,
		'is_master' => 1,
		'is_show' => 1,
		'tosid' => $sid,
		'createtime' => time()
	);
	if(!empty($_GPC['content'])){
		$audios = $_GPC ['audioServerid'];
		$audio = $audios[0];
		if($audio){
			$versionfile = IA_ROOT . '/addons/fm_jiaoyu/inc/func/auth2.php';
			require $versionfile;
			$mp3name = str_replace('images/bjq/vioce/','',$audio);
			$mp3 = str_replace('.mp3','',$mp3name);
			delvioce($mp3,FM_JIAOYU_HOST);
			$data['audio'] = $audio;
		}
		$data['content'] = trim($_GPC['content']);
		if($_GPC ['photoUrls']){
			$photoUrls = $_GPC ['photoUrls']; 
			$pothos = '';
			foreach($photoUrls as $key => $v){
				$pothos .= $v.',';
			}
			$data['photo'] = rtrim($pothos,',');
		}
		$data['type'] = 2;
		if($subtype == 'new'){
			pdo_insert(GetTableName('kcpingjia',false), $data);
		}
		if($subtype == 'eidt' && $editid > 0){
			pdo_update(GetTableName('kcpingjia',false),$data,array('id'=>$editid));
		}
	}
	if(!empty($_GPC['check'])){
		unset($data['audio']);
		unset($data['content']);
		unset($data['photo']);
		$data['type'] = 1;
		foreach($_GPC['check'] as $k => $r){
			$data['star'] = $r;
		}
		if($subtype == 'new'){
			pdo_insert(GetTableName('kcpingjia',false), $data);
		}
		if($subtype == 'eidt'){
			if(!empty($thisstar)){
				pdo_update(GetTableName('kcpingjia',false),$data,array('id'=>$thisstar['id']));
			}else{
				pdo_insert(GetTableName('kcpingjia',false), $data);
			}
		}
	}	
	if($subtype == 'new'){
		$this->sendMobileKcpj($ksid,'tostu',$sid);
		$result['msg'] = '评论成功！';
	}else{
		$result['msg'] = '修改评论成功！';
	}
	$result['result'] = true;
	die(json_encode($result));
}
if($operation == 'reply_pl'){
	$mastre = pdo_fetch("SELECT * FROM " . GetTableName('kcpingjia') . " WHERE id = '{$_GPC['masterid']}' ");
	if(!empty($mastre)){
		$data = array(
			'weid' =>  $mastre['weid'],
			'schoolid' =>  $mastre['schoolid'],
			'kcid' => $mastre['kcid'],
			'ksid' => $mastre['ksid'],
			'userid' => intval($_GPC['userid']),
			'tid' => intval($_GPC['tid']),
			'content' => trim($_GPC['content']),
			'tosid' => intval($_GPC['sid']),
			'masterid' => intval($_GPC['masterid']),
			'is_show' => 1,
			'createtime' => time()
		);
		pdo_insert(GetTableName('kcpingjia',false), $data);
		$this->sendMobileKcpj($mastre['ksid'],'tostu',$_GPC['sid']);
		$result['msg'] = '评论成功！';
		$result['result'] = true;
	}else{
		$result['msg'] = '抱歉本条评论不删除或已被删除！';
		$result['result'] = false;
	}
	die(json_encode($result));
}

if($operation == 'setjx'){
	$mastre = pdo_fetch("SELECT * FROM " . GetTableName('kcpingjia') . " WHERE id = '{$_GPC['masterid']}' ");
	if(!empty($mastre)){
		if($mastre['is_show'] == 1){
			$data = array('is_show' => 0);
		}else{
			$data = array('is_show' => 1);
		}
		pdo_update(GetTableName('kcpingjia',false), $data, array('masterid' => $mastre['id']));
		pdo_update(GetTableName('kcpingjia',false), $data, array('id' => $mastre['id']));
		$result['msg'] = '设置成功！';
		$result['is_show'] = $data['is_show'];
		$result['result'] = true;
	}else{
		$result['msg'] = '抱歉本条评论不删除或已被删除！';
		$result['result'] = false;
	}
	die(json_encode($result));
}
if($operation == 'edite_pl'){
	$mastre = pdo_fetch("SELECT * FROM " . GetTableName('kcpingjia') . " WHERE id = '{$_GPC['masterid']}' ");
	if(!empty($mastre)){
		$result['mastre'] = $mastre;
		$result['result'] = true;
	}else{
		$result['msg'] = '抱歉本条评论不删除或已被删除！';
		$result['result'] = false;
	}
	die(json_encode($result));
}
if($operation == 'del_on_masterpl'){
	$mastre = pdo_fetch("SELECT id FROM " . GetTableName('kcpingjia') . " WHERE id = '{$_GPC['mastid']}' ");
	if(!empty($mastre)){
		$checkpxs = pdo_fetch("SELECT id FROM " . GetTableName('kcpingjia') . " WHERE schoolid = '{$schoolid}' And tosid = '{$_GPC['sid']}' And ksid = '{$_GPC['ksid']}' And tid = '{$_GPC['mastid']}' ");//老师评学生打分 只查自己的
		if(!empty($checkpxs)){
			pdo_delete(GetTableName('kcpingjia',false), array('id' => $checkpxs['id']));
		}
		pdo_delete(GetTableName('kcpingjia',false),array('masterid'=>$mastre['id']));
		pdo_delete(GetTableName('kcpingjia',false), array('id' => $mastre['id']));
		$result['msg'] = '删除成功';
		$result['result'] = true;
	}else{
		$result['msg'] = '抱歉本条评论不删除或已被删除！';
		$result['result'] = false;
	}
	die(json_encode($result));
}
if($operation == 'tx_onestu'){
	$this->sendMobileKcpjtx($_GPC['ksid'],'tostu',$_GPC['sid']);
	$result['msg'] = '提醒成功,请勿重复提醒';
	$result['result'] = true;
	die(json_encode($result));
}
if($operation == 'tx_allstu'){
	$this->sendMobileKcpjtx($_GPC['ksid'],'tostu');
	$result['msg'] = '批量提醒成功,请勿重复提醒';
	$result['result'] = true;
	die(json_encode($result));
}
?>