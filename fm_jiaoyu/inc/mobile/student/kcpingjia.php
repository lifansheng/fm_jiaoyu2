<?php
/**
 * 微教育模块
 * @author 高贵血迹
 */       
global $_W, $_GPC;
$weid = $_W['uniacid'];
$schoolid = intval($_GPC['schoolid']);
$openid = $_W['openid'];
$kcid = $_GPC['kcid'];
$sid = $_GPC['sid'];
//查询是否用户登录
if (!empty($_GPC['userid'])){
	$_SESSION['user'] = $_GPC['userid'];
}
//查询是否用户登录
$it = pdo_fetch("SELECT * FROM " . GetTableName('user') . " where id = :id ", array(':id' => $_SESSION['user']));
$word = $this->GetSensitiveWord($weid);$pard = get_guanxi($it['pard']);if(!$pard){$pard = '本人';}
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$school = pdo_fetch("SELECT style2,title,spic,tpic,title,headcolor,thumb FROM " . GetTableName('index') . " where id= :id", array(':id' => $schoolid));
if($operation == 'display'){
	if(!empty($it)){
		$ksid = $_GPC['ksid'];
		$allks = GetOneKcKsOrder($kcid);//查询我上课列表
		$nowfrist = false;
		foreach($allks as $key => $row){
			$checksign = pdo_fetch("SELECT * FROM ".GetTableName('kcsign')." WHERE  schoolid = '{$schoolid}' And ksid = '{$row['id']}' And sid = '{$it['sid']}' And status = 2  ");
			if(empty($checksign)){
				unset($allks[$key]);
			}else{
				if(!$nowfrist){ //取第一个未默认选中
					$nowfrist = $row['id'];
				}
			}
		}
		$fristid = !empty($_GPC['ksid'])?$_GPC['ksid']:$nowfrist;//不带ksid默认选中第一节
		include $this->template(''.$school['style2'].'/kc/kcpingjia');
	}else{
		session_destroy();
		$stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
		header("location:$stopurl");
		exit;
	}  
} 
// 切换课时头部信息
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
	$nuber = $order['nuber'];$mydef = 0;
	$checkmypf = pdo_fetch("SELECT star FROM " . GetTableName('kcpingjia') . " WHERE schoolid = '{$schoolid}' And tid > 0  And ksid = '{$ksid}' And type = 1 And tosid = '{$it['sid']}'  ");
	if(!empty($checkmypf)){
		$mydef = $checkmypf['star'];
	}
	include $this->template(''.$school['style2'].'/kc/kcpj_temp');
}
 // 获取单个课时的评论
if($operation == 'stu_pllist'){
	$sid = intval($_GPC['sid']);$ksid = intval($_GPC['ksid']);
	$zhujiang = pdo_fetch("SELECT tid FROM ".GetTableName('kcsign')." WHERE  ksid = '{$ksid}' And tid > 0 And status = 2 And ismaster_tid > 0 ORDER BY ismaster_tid ASC ");
	$zjteainfo = pdo_fetch("SELECT tname,thumb FROM " . GetTableName('teachers') . " WHERE id = :id ", array(':id' => $zhujiang['tid']));
	$zjteainfo['thumb'] = !empty($zjteainfo['thumb'])?tomedia($zjteainfo['thumb']):tomedia($school['tpic']);
	$stuinfo = pdo_fetch("SELECT s_name,icon FROM " . GetTableName('students') . " WHERE id = :id ", array(':id' => $sid));
	$stuinfo['icon'] = !empty($stuinfo['icon'])?tomedia($stuinfo['icon']):tomedia($school['spic']);
	// 查询学生对本节授课老师打分数据
	$stu_star_tea = pdo_fetchall("SELECT totid,star FROM " . GetTableName('kcpingjia') . " WHERE schoolid = '{$schoolid}' And sid = '{$sid}' And ksid = '{$ksid}' And tid = 0 And type = 1 And totid > 0 ");
	foreach($stu_star_tea as $k => $v){
		$thistea = pdo_fetch("SELECT tname FROM ".GetTableName('teachers')." WHERE  id = '{$v['totid']}'  ");
		$stu_star_tea[$k]['tname'] = $thistea['tname'];
		$stu_star_tea[$k]['starh5'] = getStarStyle($v['star'],true);
	}
	//查询本学生给课时评价 图文 含回复
	$stuplks = pdo_fetchall("SELECT * FROM " . GetTableName('kcpingjia') . " WHERE schoolid = '{$schoolid}' And sid = '{$sid}' And ksid = '{$ksid}' And type = 2 And tid = 0 And is_master = 1");
	if($stuplks){
		foreach($stuplks as $key => $row){
			$stuplks[$key]['mastime'] = sub_days($row['createtime']);
			if($row['photo']){
				if(strstr($row['photo'],',')){
					$photoarr = explode(',',$row['photo']);
					$stuplks[$key]['photo'] = '';
					foreach($photoarr as $ph){
						$stuplks[$key]['photo'] .= '<div class="photo_box" onclick="wxImageShow(this)"><img class="img" src="'.tomedia($ph).'"/></div>';
					}
				}else{
					$stuplks[$key]['photo'] = '<div class="photo_box" onclick="wxImageShow(this)"><img class="img" src="'.tomedia($row['photo']).'"/></div>';
				}
			}
			if($row['audio']){
				$stuplks[$key]['audio'] = tomedia($row['audio']);
			}
			$stuplks[$key]['allrelpyss'] = 0;
			$stuplks[$key]['allrelpy'] =  pdo_fetchall("SELECT * FROM " . GetTableName('kcpingjia') . " WHERE  masterid = '{$row['id']}' ORDER BY createtime DESC");
			foreach($stuplks[$key]['allrelpy'] as $k => $r){
				$stuplks[$key]['allrelpy'][$k]['reptime'] = sub_days($r['createtime']);
				if($r['sid'] > 0 && $r['tid'] == 0){
					$restuinfo = pdo_fetch("SELECT s_name,icon FROM " . GetTableName('students') . " WHERE id = :id ", array(':id' => $r['sid']));
					$stuplks[$key]['allrelpy'][$k]['name'] = $restuinfo['s_name'];
					$stuplks[$key]['allrelpy'][$k]['icon'] = !empty($restuinfo['icon'])?tomedia($restuinfo['icon']):tomedia($school['spic']);
				}
				if($r['tid'] > 0 && $r['sid'] == 0){
					$reteainfo = pdo_fetch("SELECT tname,thumb FROM " . GetTableName('teachers') . " WHERE id = :id ", array(':id' => $r['tid']));
					$stuplks[$key]['allrelpy'][$k]['name'] = $reteainfo['tname'];
					$stuplks[$key]['allrelpy'][$k]['icon'] = !empty($reteainfo['thumb'])?tomedia($reteainfo['thumb']):tomedia($school['tpic']);
				}
				$stuplks[$key]['allrelpyss']++;
			}
		}
	}
	// 查询对本节老师对学生打分数据
	$tea_star_stu = pdo_fetchall("SELECT tid,star FROM " . GetTableName('kcpingjia') . " WHERE schoolid = '{$schoolid}' And sid = 0 And ksid = '{$ksid}' And tid > 0 And type = 1 And tosid = '{$sid}' ");
	foreach($tea_star_stu as $i => $vu){
		$thisteas = pdo_fetch("SELECT tname FROM ".GetTableName('teachers')." WHERE  id = '{$vu['tid']}'  ");
		$tea_star_stu[$i]['tname'] = $thisteas['tname'];
		$tea_star_stu[$i]['starh5'] = getStarStyle($vu['star'],true);
	}
	//查询本学生给课时评价 图文 含回复
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
					$restuinfo = pdo_fetch("SELECT s_name,icon FROM " . GetTableName('students') . " WHERE id = :id ", array(':id' => $r['sid']));
					$teaplxs[$key]['allrelpy'][$k]['name'] = $restuinfo['s_name'];
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
	include $this->template(''.$school['style2'].'/kc/kcpj_temp');
}

if($operation == 'on_pl_stu'){
	$sid = intval($_GPC['sid']);$ksid = intval($_GPC['ksid']);$type = $_GPC['type'];
	if($type == 'edit'){
		// 查询学生对本节授课老师打分数据
		$stu_star_tea = pdo_fetchall("SELECT totid,star FROM " . GetTableName('kcpingjia') . " WHERE schoolid = '{$schoolid}' And sid = '{$sid}' And ksid = '{$ksid}' And tid = 0 And type = 1 And totid > 0 ");
		foreach($stu_star_tea as $k => $v){
			$thistea = pdo_fetch("SELECT tname FROM ".GetTableName('teachers')." WHERE  id = '{$v['totid']}'  ");
			$stu_star_tea[$k]['tname'] = $thistea['tname'];
		}
		//查询本学生给课时评价 图文 含回复
		$stuplks = pdo_fetchall("SELECT * FROM " . GetTableName('kcpingjia') . " WHERE schoolid = '{$schoolid}' And sid = '{$sid}' And ksid = '{$ksid}' And type = 2 And tid = 0 And is_master = 1");
		if($stuplks){
			foreach($stuplks as $key => $row){
				$stuplks[$key]['mastime'] = sub_days($row['createtime']);
				if($row['photo']){
					if(strstr($row['photo'],',')){
						$photoarr = explode(',',$row['photo']);
						$stuplks[$key]['photo'] = '';
						foreach($photoarr as $ph){
							$stuplks[$key]['photo'] .= '<div class="photo_box" onclick="wxImageShow(this)"><img class="img" src="'.tomedia($ph).'"/></div>';
						}
					}else{
						$stuplks[$key]['photo'] = '<div class="photo_box" onclick="wxImageShow(this)"><img class="img" src="'.tomedia($row['photo']).'"/></div>';
					}
				}
				if($row['audio']){
					$stuplks[$key]['audio'] = tomedia($row['audio']);
				}
			}
		}
		$signteanub = count($allsigntea);
	}
	if($type == 'new'){
		$allsigntea = pdo_fetchall("SELECT id,tid FROM ".GetTableName('kcsign')." WHERE  ksid = '{$ksid}'  And tid > 0 And sid = 0 And status = 2 ORDER BY ismaster_tid ASC ");
		foreach($allsigntea as $key => $row){
			$teainfo = pdo_fetch("SELECT tname FROM ".GetTableName('teachers')." WHERE  id = '{$row['tid']}'  ");
			if(!empty($teainfo)){
				$allsigntea[$key]['tname'] = $teainfo['tname'];
			}else{
				pdo_delete(GetTableName('kcsign',false),array('id'=>$row['id']));
				unset($allsigntea[$key]);
			}
		}
		$signteanub = count($stu_star_tea);
	}
	include $this->template(''.$school['style2'].'/kc/kcpj_temp');
}
if($operation == 'subpl'){
	$type = $_GPC['subtype'];
	$ksid = intval($_GPC['ksid']);$sid = intval($_GPC['sid']);
	$checkpl = pdo_fetch("SELECT id FROM ".GetTableName('kcpingjia')." WHERE  ksid = '{$ksid}'  And type =1 And sid = '{$sid}' And tid = 0  And totid > 0 ");
	if(!empty($checkpl) && $type == 'new'){
		$result['msg'] = '抱歉，你已经评价了本节,请勿重复提交';
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
		'is_master' => 1,
		'is_show' => 1,
		'sid' => $sid,
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
		if($type == 'new'){
			pdo_insert(GetTableName('kcpingjia',false), $data);
		}
		if($type == 'edit'){
			pdo_update(GetTableName('kcpingjia',false),$data,array('id'=>$_GPC['contenid']));
			//pdo_insert(GetTableName('kcpingjia',false), $data);
		}
	}
	if(!empty($_GPC['check'])){
		unset($data['audio']);
		unset($data['content']);
		unset($data['photo']);
		$data['type'] = 1;
		foreach($_GPC['check'] as $k => $r){
			$data['star'] = $r;
			if($type == 'new'){
				$data['totid'] = $k;
				pdo_insert(GetTableName('kcpingjia',false), $data);
			}
			if($type == 'edit'){
				$thisstar = pdo_fetch("SELECT id FROM ".GetTableName('kcpingjia')." WHERE  ksid = '{$ksid}'  And type =1 And sid = '{$sid}' And tid = 0  And totid = '{$k}' ");
				if(!empty($thisstar)){
					pdo_update(GetTableName('kcpingjia',false),$data,array('id'=>$thisstar['id']));
				}else{
					$data['totid'] = $k;
					pdo_insert(GetTableName('kcpingjia',false), $data);
				}
			}
		}
	}
	if($type == 'edit'){
		$result['msg'] = '修改评论成功！';
	}else{
		$this->sendMobileKcpj($ksid,'totea');
		$result['msg'] = '评论成功！';
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
			'sid' => intval($_GPC['sid']),
			'content' => trim($_GPC['content']),
			'masterid' => intval($_GPC['masterid']),
			'is_show' => 1,
			'createtime' => time()
		);
		pdo_insert(GetTableName('kcpingjia',false), $data);
		$this->sendMobileKcpj($mastre['ksid'],'totea');
		$result['msg'] = '评论成功！';
		$result['result'] = true;
	}else{
		$result['msg'] = '抱歉本条评论不删除或已被删除！';
		$result['result'] = false;
	}
	die(json_encode($result));
}
if($operation == 'tx_tea'){
	$this->sendMobileKcpjtx($_GPC['ksid'],'totea');
	$result['msg'] = '提醒成功,请勿重复提醒';
	$result['result'] = true;
	die(json_encode($result));
}
?>