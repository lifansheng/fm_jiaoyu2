<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
defined('IN_IA') or exit('Access Denied');
load()->func('communication');



/**
* 课程最先的操作   **重要**
* 点击扫描分配后的粉丝归属移动 
* 根据kcid tid sid uid 添加一行推广归属粉丝或开团信息或组队信息
* com_form 1推广海报2团购海报3助力海报4前端分配
		//"https://manger.daren007.com/app/index.php?i=3&c=entry&goto=video_kc&do=hookcom&m=fm_jiaoyu&com_form=5&schoolid=4&kcid=227&masteruid=o0KJxw4Y-ezyMYl4gUub_u9ShQh8&masteruserid=0&mastertid=0"
		SetFansInfoByKc($kcid,$openid,0,$mastertid,$masteruid,$masteruserid,$com_form);
* @author 高贵血迹
**/
function SetFansInfoByKc($kcid,$openid,$userid,$mastertid,$masteropenid,$masteruserid,$com_form){
	if($userid > 0){
		$checfans = pdo_fetch("SELECT * FROM " . GetTableName('promote_fans') . " WHERE kcid = '{$kcid}' And userid = '{$userid}' ");
	}else{
		$checfans = pdo_fetch("SELECT * FROM " . GetTableName('promote_fans') . " WHERE kcid = '{$kcid}' And openid = '{$openid}' ");
	}
	if($masteruserid > 0){
		$com_form = 1;
	}
	$kcinfo = pdo_fetch("SELECT id,weid,schoolid,sale_id,end,sale_type FROM " . GetTableName('tcourse') . " WHERE id = '{$kcid}' ");
	$data = array( //初始化插入数组
		'weid' 			=> $kcinfo['weid'],
		'schoolid'  	=> $kcinfo['schoolid'],
		'kcid' 			=> $kcid,
		'openid' 		=> $openid,//注意 帮忙助力者没有userid 只是粉丝而已
		'userid' 		=> $userid,
		'superior_tid' 	=> $mastertid,
		'superior_uid' 	=> $masteropenid,
		'superior_userid'=> $masteruserid,
		'is_sale' 		=> 0,//些粉丝归属都未付费
		'com_form' 		=> $com_form,//类型从课程信息取
		'createtime' 	=> time(),	
	);
	if(empty($checfans)){
		pdo_insert(GetTableName('promote_fans',false), $data);
		return true;
	}else{
		if($mastertid > 0 && empty($checfans['superior_tid'])){
			unset($data['createtime']);
			unset($data['is_sale']);
			unset($data['superior_userid']);
			unset($data['superior_uid']);
			pdo_update(GetTableName('promote_fans',false), $data, array('id' => $checfans['id']));
			return true;
		}else{
			return false;
		}	
	}
}

//设置用户购买后粉丝表对应关系为已消费
function SetFansSale($kcid,$userid,$openid){
	if($userid > 0){
		$checfans = pdo_fetch("SELECT id FROM " . GetTableName('promote_fans') . " WHERE kcid = '{$kcid}' And userid = '{$userid}' ");
	}
	if(empty($checfans)){
		$checfans = pdo_fetch("SELECT id FROM " . GetTableName('promote_fans') . " WHERE kcid = '{$kcid}' And openid = '{$openid}' ");
	}
	if(!empty($checfans)){
		$data = array('userid'=>$userid,'is_sale' => 1);
		pdo_update(GetTableName('promote_fans',false), $data, array('id' => $checfans['id']));
	}
}
 

/*根据openid kcid 获取此粉丝已报名本课程的学生列表 且有队伍的情况
* 返回 数组
* @author 高贵血迹
*/
function GetStuByOpenid($kcid,$openid,$schoolid){
	$reslut = array();
	$school = pdo_fetch("SELECT spic FROM " . GetTableName('index') . " where id = '{$schoolid}' ");
	$alluser = pdo_fetchall("SELECT id,sid FROM " . GetTableName('user') . " WHERE openid = '{$openid}' And schoolid = '{$schoolid}' And tid = 0 And sid != 0 ");
	$i = 0;
	$nowtime = time();
	foreach($alluser as $key => $row){
		$team = pdo_fetch("SELECT id,masterid,endtime,orderid FROM " . GetTableName('sale_team') . " WHERE kcid = '{$kcid}' And userid = '{$row['id']}' And endtime > '{$nowtime}' ");
		if(!empty($team)){
			$stuinfo = pdo_fetch("SELECT s_name,icon,sex,mobile FROM " . GetTableName('students') . " WHERE  id = '{$row['sid']}' ");
			$master = pdo_fetch("SELECT id FROM " . GetTableName('sale_team') . " WHERE  id = '{$team['masterid']}' ");
			$reslut[$i]['teamid'] = $master['id'];
			$reslut[$i]['order'] = $order['orderid'];
			$reslut[$i]['userid'] = $row['id'];
			$reslut[$i]['name'] = $stuinfo['s_name'];
			$reslut[$i]['sex'] = $stuinfo['sex'];
			$reslut[$i]['mobile'] = $stuinfo['mobile'];
			$reslut[$i]['icon'] = $stuinfo['icon'] ? tomedia($stuinfo['icon']):tomedia($school['spic']);
			$i++;
		}
	}
	return $reslut;
}

/*根据com_form  获取来源中文值
* 返回 类型名称
* @author 高贵血迹
*/
function GetComFrom($type){
	$fromarray = array( 1 => '推广员海报', 2 => '团购海报', 3 => '助力海报', 4 => '前端分配', 5 => '粉丝推广海报', 6 => '访问页面');
	return $fromarray[$type];
}

/**
* 根据kcid userid openid
* 检查是否参团组队，或参与的活动是否结束
* @author 高贵血迹
**/
function CheckIsInSlae($kcid,$userid,$openid){
	$data = array(
		'isinsale' => false,
		'isover' => false,
		'masterid' => false,
	);
	$checkthis = pdo_fetch("SELECT masterid,endtime FROM " . GetTableName('sale_team') . " WHERE kcid = '{$kcid}' And userid = '{$userid}' ORDER BY id ASC ");
	if(empty($checkthis)){
		$checkthis = pdo_fetch("SELECT masterid,endtime FROM " . GetTableName('sale_team') . " WHERE kcid = '{$kcid}' And openid = '{$openid}' ORDER BY id ASC ");
	}
	if($checkthis){
		$data['isinsale'] = true;
		$data['masterid'] = $checkthis['masterid'];
		if($checkthis['endtime'] < time()){
			$data['isover'] = true;
		}
	}
	return $data;
}

/**
* 根据kcid userid openid
* 检查正在参与的未过期的队伍
* @author 高贵血迹
**/
function CheckUnOverTema($kcid,$userid,$openid){
	$data = array(
		'masterid' => false
	);
	$nowtime = time();
	$checkthis = pdo_fetch("SELECT masterid FROM " . GetTableName('sale_team') . " WHERE kcid = '{$kcid}' And endtime > '{$nowtime}' And  userid = '{$userid}' ");
	if($checkthis){
		$data['masterid'] = $checkthis['masterid'];
	}
	return $data;
}
/**
* 报名课程后的开团或参团 开组或助力操作
* 根据kcid userid mastertid type 添加一行开团信息或组队信息
* 开团的人都在students表注册过
* @author 高贵血迹
**/
function SetSaleInfoByKc($kcid,$userid,$openid,$masterid,$type,$orderid){
	$kcinfo = pdo_fetch("SELECT id,weid,schoolid,sale_id,end,sale_type FROM " . GetTableName('tcourse') . " WHERE id = '{$kcid}' ");
	$saleset = pdo_fetch("SELECT suc_munber,overtimeset,overtime,endtime FROM " . GetTableName('kc_saleset') . " WHERE id = '{$kcid['sale_id']}' ");
	$user = pdo_fetch("SELECT * FROM " . GetTableName('user') . " WHERE id = '{$userid}' ");
	if($saleset['overtimeset'] == 1){
		$endtime = $saleset['endtime'];
	}
	if($saleset['overtimeset'] == 2){
		$nowtime = time();
		$endtime = $saleset['overtime']*3600 + $nowtime;
	}
	$data = array( //初始化插入数组
		'weid' 			=> $kcinfo['weid'],
		'schoolid'  	=> $kcinfo['schoolid'],
		'kcid' 			=> $kcid,
		'userid' 		=> $userid,
		'openid' 		=> empty($user['openid'])?$openid:$user['openid'],//注意 帮忙助力者没有userid 只是粉丝而已
		'is_success' 	=> 0,
		'ismaster' 		=> 1,//队长
		'is_sale' 		=> $kcinfo['sale_type'] == 1 ? 1 : 0,//注意 开团和参团都是付费了,助力则反之
		'orderid' 		=> $orderid,//团购队伍必传orderid
		'type' 			=> $kcinfo['sale_type'],//类型从课程信息取
		'endtime' 		=> $endtime,
		'createtime' 	=> time(),	
	);
	if($type == 'open'){
		pdo_insert(GetTableName('sale_team',false), $data);
		$masterid = pdo_insertid();//改写传过来的masterid 0 未新插入的数据
		pdo_update(GetTableName('sale_team',false), array('masterid' => $mastertid) ,array('id' => $masterid));
		$reslut['reslut'] = true;
		$reslut['is_success'] = CheckTemIsFull($masterid);
		$reslut['mag'] = '成功';
		$reslut['masterid'] = $masterid;
	}
	if($type == 'join'){
		unset($data['ismaster']); //参团组队取消队长标识
		$tuannumbers = pdo_fetchcolumn("select count(distinct id) FROM ".GetTableName('sale_team')." WHERE kcid = '{$kcid}' And masterid = '{$masterid}' ");
		$thisteam = pdo_fetch("select endtime FROM ".GetTableName('sale_team')." WHERE id = '{$masterid}' ");
		$data['endtime'] = $thisteam['endtime'];//结束时间以团长的为准
		if($tuannumbers < $saleset['suc_munber']){
			$data['masterid'] = $masterid;
			pdo_insert(GetTableName('sale_team',false), $data);
			$reslut['is_success'] = CheckTemIsFull($masterid);
			$reslut['reslut'] = true;
			$reslut['mag'] = '成功';
			$reslut['masterid'] = $masterid;
		}else{
			$reslut['reslut'] = false;
			$reslut['mag'] = '人数已满';
			$reslut['is_success'] = CheckTemIsFull($masterid);
			$reslut['masterid'] = $masterid;
		}
	}
	return $reslut;
}

/**
 * 根据 kcid或tid 查询课程推广任务完成进度(如tid为0查看课程所有人情况，反之只返回tid的结果)
 * 返回值说明
 * blli :100以内数值（用于显示进度条）
 * count :总任务人数
 * sucnumber :已完成人数（付费了的）
 * doingnumber :报名中人数未付费
 * rest :还剩余未完成人数（含未付费人）
 * @author 高贵血迹
 */
function GetKcTgProcess($kcid,$tid = 0){
	$reslut = array();
	if($tid > 0){
		$kcinfo = pdo_fetch("SELECT id,tg_id,sale_type FROM " . GetTableName('tcourse') . " WHERE id = '{$kcid}' ");
		$promote = pdo_fetch("SELECT team,tg_number FROM " . GetTableName('kc_promote') . " WHERE id = '{$kcinfo['tg_id']}' ");
		$reslut['count'] = $promote['tg_number'];
		$reslut['teams'] = count(explode(',',$promote['team']));
		$salefans = pdo_fetchcolumn("select count(distinct sid) FROM ".GetTableName('order')." WHERE kcid = '{$kcid}' And superior_tid = '{$tid}' And status = 2 And sid > 0 ");//本课归属粉丝购买总人数
		$allfans = pdo_fetchcolumn("select count(id) FROM ".GetTableName('promote_fans')." WHERE kcid = '{$kcid}' And superior_tid = '{$tid}' ");
		$reslut['allfans'] = $salefans;
		$reslut['myfans'] = $allfans;
		if($salefans>0 || $allfans>0){
			$reslut['mybilis']= intval(100*($salefans/$promote['tg_number']));
			$reslut['bilis']= intval($salefans/($allfans>0?$allfans:1));
			$reslut['bili'] = intval(100*($salefans/($allfans>0?$allfans:1)));
			$reslut['sucnumber'] = $salefans;
		}else{
			$reslut['bilis'] = 0;
			$reslut['bili'] = 0;
			$reslut['sucnumber'] = 0;
		}
	}else{
		$kcinfo = pdo_fetch("SELECT id,tg_id FROM " . GetTableName('tcourse') . " WHERE id = '{$kcid}' ");
		$allteam = pdo_fetch("SELECT team,tg_number FROM " . GetTableName('kc_promote') . " WHERE id = '{$kcinfo['tg_id']}' ");
		$count = count(explode(',',$allteam['team']));
		$reslut['count'] = $count*$allteam['tg_number'];
		$yb = GetKcSiguNub($kcid);
		if(count($yb)>0 ){
			$reslut['bilis']= intval($yb/$reslut['count']);
			$reslut['bili'] = intval(100*($yb/$reslut['count']));
		}else{
			$reslut['bili'] = 0;
		}
	}
	return $reslut;
}

/*根据kcid 及condtion获取课程推广员信息列表(老师)
* 返回二维数组
* @author 高贵血迹
*/
function GetProTeamByKc($cond){
	$condition = "";
	if($cond['tid'] > 0){
		$condition .= " And tid = '{$cond['tid']}' ";
	}
	if($cond['kcid'] > 0){
		$condition .= " And kcid = '{$cond['kcid']}' ";
	}
	$result = array();
	$allks = 0;$allzsks = 0;$allstks = 0;
	$allcont = 0;$allsucnub = 0;$zscont = 0;$zssuccont = 0;$stcont = 0;$stsuccont = 0;
	$list = pdo_fetchall("SELECT * FROM " . GetTableName('promote_team') . " WHERE tid > 1 $condition ORDER BY createtime DESC ");
	foreach($list as $key => $row){
		$kcinfo = pdo_fetch("SELECT id,name,start,end,is_try,sale_type,kc_type,sale_id,tg_id,thumb,maintid FROM " . GetTableName('tcourse') . " where id = '{$row['kcid']}' ");
		if(!empty($kcinfo)){
			$tgset = pdo_fetch("SELECT * FROM " . GetTableName('kc_promote') . " where id = '{$kcinfo['tg_id']}' ");
			$list[$key]['tgset'] = $tgset;
			$list[$key]['kcinfo'] = $kcinfo;
			$list[$key]['bmmuber'] = pdo_fetchcolumn("SELECT count(distinct id) FROM " . GetTableName('coursebuy') . " WHERE kcid = '{$row['kcid']}' And is_change != 1  "); 
			$teainf = pdo_fetch("SELECT tname FROM " . GetTableName('teachers') . " where id = '{$kcinfo['maintid']}' ");
			$list[$key]['midteacher'] = $teainf['tname'];
			$kctgproce = GetKcTgProcess($row['kcid'],$row['tid']);		
			$list[$key]['proce'] = $kctgproce;
			if($kcinfo['is_try'] == 1){//试听课
				$allstks++;
				$stcont = $stcont + $kctgproce['count'];
				$stsuccont = $stsuccont + $kctgproce['sucnumber'];
			}else{
				$allzsks++;
				$zscont = $zscont + $kctgproce['count'];
				$zssuccont = $zssuccont + $kctgproce['sucnumber'];
			}
			$allks++;
			$allcont = $allcont + $kctgproce['count'];
			$allsucnub = $allsucnub + $kctgproce['sucnumber'];
		}else{
			unset($list[$key]);
		}	
	}
	$result['allks'] = $allks;$result['allzsks'] = $allzsks;$result['allstks'] = $allstks;
	$result['list'] = $list;$result['allcont'] = $allcont;$result['allsucnub'] = $allsucnub;
	$result['zscont'] = $zscont;$result['zssuccont'] = $zssuccont;$result['stcont'] = $stcont;$result['stsuccont'] = $stsuccont;
	return $result;
}

/**
* 根据teamid 和粉丝uid设置 虚拟拼团或助力的虚拟成员
* @author 高贵血迹
**/
function SetXnFans($teamid,$uidarray,$tid){
	$team = pdo_fetch("SELECT kcid,masterid,endtime FROM " . GetTableName('sale_team') . " where id = '{$teamid}' ");
	$kcinfo = pdo_fetch('SELECT weid,schoolid,sale_id,sale_type FROM ' . GetTableName('tcourse') . " WHERE id = '{$team['kcid']}' ");
	$saleset = pdo_fetch("SELECT suc_munber FROM " . GetTableName('kc_saleset') . " WHERE id = :id ", array(':id' => $kcinfo['sale_id']));
	$allteam = pdo_fetchcolumn("select count(distinct id) FROM ".GetTableName('sale_team')." WHERE kcid = '{$team['kcid']}' And masterid = '{$team['masterid']}' ");
	if($kcinfo['sale_type'] == 1){
		$word = '拼团队伍';
	}
	if($kcinfo['sale_type'] == 2){
		$word = '助力队伍';
	}
	$nowtime = time();
	if($nowtime >= $team['endtime']){
		$result['result'] = false;
		$result['msg'] = '错误！本'.$word.'已过期,不可再添加虚拟成员';
	}else{
		if(CheckTemIsFull($team['masterid'])){
			$result['result'] = false;
			$result['msg'] = '错误！本'.$word.'人数已满,不可再添加虚拟成员';
		}else{
			$restminge = $saleset['suc_munber'] - $allteam;
			if(count($uidarray) <= $restminge){
				$mubers = 0;
				foreach($uidarray as $row){
					$data = array( //初始化插入数组
						'weid' 			=> $kcinfo['weid'],
						'schoolid'  	=> $kcinfo['schoolid'],
						'kcid' 			=> $team['kcid'],
						'openid' 		=> $row,
						'is_success' 	=> 0,
						'ismaster' 		=> 0,//队长
						'is_sale' 		=> $kcinfo['sale_type'] == 1 ? 1 : 0,//注意 开团和参团都是付费了,助力则反之
						'type' 			=> $kcinfo['sale_type'],//类型从课程信息取
						'endtime' 		=> $team['endtime'],//取主ID的结束时间
						'pkuser' 		=> $tid,
						'masterid' 		=> $team['masterid'],//归属队伍ID
						'is_really' 	=> 1,//标记为虚拟粉丝
						'createtime' 	=> time(),	
					);
					pdo_insert(GetTableName('sale_team',false), $data);
					$mubers++;
				}
				$result['is_success'] = CheckTemIsFull($team['masterid']);
				$result['result'] = true;
				$result['msg'] = '成功添加'.$mubers.'个虚拟成员';
				if($result['is_success']){//添加中如遇满员则处理课程购买记录 处理学生解锁
					SetTeamStuStatus($team['masterid'],$kcinfo['sale_type']);
					$result['StuCour'] = SetTeamStuCour($team['kcid'],$team['masterid'],0,$kcinfo['sale_type']);
				}
			}else{
				$result['result'] = false;
				$result['msg'] = '错误！本'.$word.'还可以再添加'.$restminge.'个虚拟成员';
			}
		}
	}
	return $result;
}

/*根据kcid  查询所有队伍信息
* 返回 2维数组
* @author 高贵血迹
*/
function GetAllTeamByKcid($kcid,$isstu = false){
	$condition = '';
	if($isstu){
		$nowtiem = time();
		$condition .= " And endtime > '{$nowtiem}' " ;
	}
	$kcinfo = pdo_fetch('SELECT weid,schoolid,sale_id,sale_type FROM ' . GetTableName('tcourse') . " WHERE id = '{$kcid}' ");
	$school = pdo_fetch("SELECT spic FROM " . GetTableName('index') . " where id = '{$kcinfo['schoolid']}' ");
	$saleset = pdo_fetch("SELECT suc_munber FROM " . GetTableName('kc_saleset') . " WHERE id = :id ", array(':id' => $kcinfo['sale_id']));
	$allteam = pdo_fetchall("SELECT * FROM " . GetTableName('sale_team') . " where kcid = '{$kcid}' And ismaster = 1 $condition ORDER BY endtime DESC,createtime ASC ");
	foreach($allteam as $key => $row){
		$allteam[$key]['enddate'] = date('Y/m/d H:i:s',$row['endtime']);
		$allteam[$key]['is_success'] = CheckTemIsFull($row['id']);
		$allteam[$key]['rest_minge'] = 0;
		if(!$allteam[$key]['is_success']){
			$allteam[$key]['rest_minge'] = CheckTemIsrest($row['id']);
		}
		$dz_fans = GetWeFans($kcinfo['weid'],$row['openid']);
		$dz_user = pdo_fetch("SELECT sid FROM " . GetTableName('user') . " WHERE id = :id ", array(':id' => $row['userid']));
		$dz_student = pdo_fetch("SELECT icon,s_name FROM " . GetTableName('students') . " WHERE id = :id ", array(':id' => $dz_user['sid']));
		$allteam[$key]['dz_name']   = $dz_student['s_name'];
		$allteam[$key]['dz_avatar'] = !empty($dz_student['icon'])?tomedia($dz_student['icon']):(!empty($dz_fans['avatar'])?tomedia($dz_fans['avatar']):tomedia($school['spic']));
		$allteam[$key]['team'] =  pdo_fetchall("SELECT * FROM " . GetTableName('sale_team') . " where kcid = '{$kcid}' And masterid = '{$row['id']}' ORDER BY ismaster DESC,createtime ASC ");
		$allteam[$key]['tuif'] = 0;
		foreach($allteam[$key]['team'] as $k => $r){
			if($r['tuifei'] == 1){
				$allteam[$key]['tuif']++;
			}
			$fansinfo = GetWeFans($kcinfo['weid'],$r['openid']);
			if($r['is_really'] == 1){ //虚拟粉丝直接用openid取头像
					$allteam[$key]['team'][$k]['name'] = '虚拟';
					$allteam[$key]['team'][$k]['avatar']	 = $fansinfo['avatar'];
			}else{
				if($kcinfo['sale_type'] == 1){//团购查用户信息
					$user = pdo_fetch("SELECT sid FROM " . GetTableName('user') . " WHERE id = :id ", array(':id' => $r['userid']));
					$student = pdo_fetch("SELECT icon,s_name FROM " . GetTableName('students') . " WHERE id = :id ", array(':id' => $user['sid']));
					$allteam[$key]['team'][$k]['name']   = $student['s_name'];
					$allteam[$key]['team'][$k]['avatar'] = !empty($fansinfo['avatar'])?$fansinfo['avatar']:(!empty($student['icon'])?tomedia($student['icon']):tomedia($school['spic']));
				}
				if($kcinfo['sale_type'] == 2){//助力查粉丝信息openid查
					$allteam[$key]['team'][$k]['name'] = $fansinfo['nickname'];							
					$allteam[$key]['team'][$k]['avatar']	 = $fansinfo['avatar'];
				}
			}
		}
		$allteam[$key]['nobody'] = array();
		if(count($allteam[$key]['team']) < $saleset['suc_munber']){
			$restnumber = $saleset['suc_munber'] - count($allteam[$key]['team']);
			for($i=0; $i<$restnumber; $i++){
				$allteam[$key]['nobody'][$i] = array(
					'icon' => MODULE_URL.'public/mobile/img/nobody.png'
				);
			}
		}
		if($isstu){//后端需要取成员
			if($allteam[$key]['is_success']){
				unset($allteam[$key]);
			}
		}
	}
	return $allteam;
}

/*
* 根据teamid  查询队伍成员列表
* 返回 2维数组
* @author 高贵血迹
*/
function GetTeamList($teamid,$isstu = false){
	$team = pdo_fetch("SELECT weid,kcid,schoolid,endtime,masterid FROM " . GetTableName('sale_team') . " where id = '{$teamid}' ");
	$school = pdo_fetch("SELECT spic FROM " . GetTableName('index') . " where id = '{$team['schoolid']}' ");
	$kcinfo = pdo_fetch('SELECT sale_id FROM ' . GetTableName('tcourse') . " WHERE id = '{$team['kcid']}' ");
	$saleset = pdo_fetch("SELECT suc_munber FROM " . GetTableName('kc_saleset') . " WHERE id = :id ", array(':id' => $kcinfo['sale_id']));
	$teamlist = pdo_fetchall("SELECT * FROM " . GetTableName('sale_team') . " where masterid = '{$team['masterid']}' ORDER BY ismaster DESC,createtime ASC ");
	foreach($teamlist as $key =>$row){
		$teamlist[$key]['is_success'] = CheckTemIsFull($team['masterid']);
		$teamlist[$key]['ismaster'] = false;
		if($row['ismaster'] == 1){
			$teamlist[$key]['ismaster'] = true;
		}
		$teamlist[$key]['belong'] = array();
		if($row['orderid'] > 0){
			$order =  pdo_fetch('SELECT status FROM ' . GetTableName('order') . " WHERE id = '{$row['orderid']}' ");
			$teamlist[$key]['order_status'] = $order['status'];
		}
		if($row['userid']){
			$user = pdo_fetch("SELECT sid,realname,mobile,openid  FROM " . GetTableName('user') . " WHERE id = '{$row['userid']}' ");
			$userfans = GetWeFans($team['weid'],$user['openid']);
			$student = pdo_fetch("SELECT icon,s_name,mobile FROM " . GetTableName('students') . " WHERE id = '{$user['sid']}' ");
			$teamlist[$key]['mobile'] = $user['mobile']?$user['mobile']:$student['mobile'];
			$teamlist[$key]['name'] = $student['s_name'];
			$teamlist[$key]['icon'] = !empty($student['icon'])?tomedia($student['icon']):tomedia($userfans['avatar']);
		}else{
			$dz_user = GetWeFans($team['weid'],$row['openid']);
			if($dz_user['avatar']){
				$teamlist[$key]['name'] = $dz_user['nickname'];
				$teamlist[$key]['icon']	 = $dz_user['avatar'];
			}else{
				$dz_user = GetWeFans($team['weid'],$row['openid']);
				$teamlist[$key]['name'] = $dz_user['nickname'];
				$teamlist[$key]['icon']	 = $dz_user['avatar'];
			}
		}
		if(!$isstu && $row['is_really'] == 0){//检查 粉丝归属 学生端不需要
			$teamlist[$key]['belong'] = CheckFansBelong($team['kcid'],$row['userid'],$row['openid']);
		}
		if(!$isstu){//学生端不需要
			$teamlist[$key]['pktname'] = '';
			if($row['is_really'] == 1 ||  $order['status'] == 3){//虚拟用户和操作过退费的显示操作人
				$teamlist[$key]['pktname'] = CheckPkUser($row['pkuser']);
			}
			$teamlist[$key]['tfuser'] = CheckPkUser($row['tfuser']);
		}
		if($isstu){//学生端需要nobody
			if(count($teamlist) < $saleset['suc_munber']){
				for($i=count($teamlist); $i<$saleset['suc_munber']; $i++){
					$teamlist[$i]['icon'] = '';
				}
			}
		}
	}
	return $teamlist;
}

/*根据teamid  查询队伍基本信息信息
* 返回 数组
* @author 高贵血迹
*/
function GetTeamBeastInfo($teamid){
	$result = array();
	$nowtime = time();
	$team = pdo_fetch("SELECT masterid FROM " . GetTableName('sale_team') . " where id = '{$teamid}' ");
	$master = pdo_fetch("SELECT id,kcid,schoolid,endtime,masterid FROM " . GetTableName('sale_team') . " where id = '{$team['masterid']}' ");
	$kcinfo = pdo_fetch('SELECT sale_id,sale_type,cose FROM ' . GetTableName('tcourse') . " WHERE id = '{$master['kcid']}' ");
	$saleset = pdo_fetch("SELECT suc_munber,price,tuanz_price FROM " . GetTableName('kc_saleset') . " WHERE id = :id ", array(':id' => $kcinfo['sale_id']));
	$allteam = pdo_fetchcolumn("select count(distinct id) FROM ".GetTableName('sale_team')." where masterid = '{$master['id']}' ");
	$result['allteam'] = $allteam;
	$result['saleset'] = $saleset;
	$result['lastprice'] = round($kcinfo['cose']-$saleset['price'],2);
	$result['rest_me'] = $saleset['suc_munber'] - $allteam;
	$result['endtime'] = date('Y/m/d H:i:s',$master['endtime']);
	if($kcinfo['sale_type'] == 1){
		$leixing = '开团';
	}
	if($kcinfo['sale_type'] == 2){
		$leixing = '助力';
	}
	$result['isfull'] = false;
	if($result['rest_me'] == 0){//满员
		$result['tipword'] = $leixing."成功！";
		$result['isfull'] = true;
	}else{//未满员
		if($nowtime < $master['endtime']){ //未过期
			$result['isover'] = false;
			$result['tipword'] = "再邀请{$result['rest_me']}个新好友即可{$leixing}成功，<span class='bluefont lxftime' lxfday='no' endtime='{$result['endtime']}'></span>";
		}else{
			$result['isover'] = true;
			$result['tipword'] = $leixing."失败，<span class='bluefont'>已过期</span>";
		}
	}
	return $result;
}

/*根据pkuser  查询操作虚拟拼团助力的老师
* 返回 老师姓名
* @author 高贵血迹
*/
function CheckPkUser($pkuser){
	$tname = '';
	if(!empty($pkuser)){
		if($pkuser >= 1){
			$teacher = pdo_fetch("SELECT tname FROM " . GetTableName('teachers') . " where id = :id ", array(':id' => $pkuser));
			$tname = $teacher['tname'];
		}elseif($pkuser == 'auto'){
			$tname = '系统自动';
		}else{
			$tname = '管理员';
		}
	}	
	return $tname;
}

/*根据teamid 主teamid  查询队伍是否满员
* 返回布尔值 
* 满员true
* 未满false
* @author 高贵血迹
*/
function CheckTemIsFull($teamid){
	$team = pdo_fetch("SELECT kcid,masterid FROM " . GetTableName('sale_team') . " where id = '{$teamid}' ");
	$kcinfo = pdo_fetch('SELECT sale_id FROM ' . GetTableName('tcourse') . " WHERE id = '{$team['kcid']}' ");
	$saleset = pdo_fetch("SELECT suc_munber FROM " . GetTableName('kc_saleset') . " WHERE id = :id ", array(':id' => $kcinfo['sale_id']));
	$checkteam = pdo_fetchcolumn("select count(distinct id) FROM ".GetTableName('sale_team')." WHERE kcid = '{$team['kcid']}' And masterid = '{$team['masterid']}' ");
	$is_success = false;
	if($checkteam >= $saleset['suc_munber']){
		$is_success = true;
	}
	return $is_success;
}
//查询队伍剩余名额数量
function CheckTemIsrest($teamid){
	$team = pdo_fetch("SELECT kcid FROM " . GetTableName('sale_team') . " where id = '{$teamid}' ");
	$kcinfo = pdo_fetch('SELECT sale_id FROM ' . GetTableName('tcourse') . " WHERE id = '{$team['kcid']}' ");
	$saleset = pdo_fetch("SELECT suc_munber FROM " . GetTableName('kc_saleset') . " WHERE id = :id ", array(':id' => $kcinfo['sale_id']));
	$checkteam = pdo_fetchcolumn("select count(distinct id) FROM ".GetTableName('sale_team')." WHERE kcid = '{$team['kcid']}' And masterid = '{$teamid}' ");
	$rest = $saleset['suc_munber'] - $checkteam;
	return $rest;
}
/*根据kcid  sid 查询总购买课程、已签课时和剩余课程
* @author 高贵血迹
*/
function GetRestKsBySid($kcid,$sid,$offline = true){
	$buycourse = pdo_fetchcolumn("SELECT ksnum FROM " . GetTableName('coursebuy') . " WHERE sid = :sid And kcid=:kcid ", array(':sid' => $sid,':kcid'=> $kcid));
	if($offline){
		$hasSign =  pdo_fetchcolumn("SELECT sum(costnum) FROM " . GetTableName('kcsign') . " WHERE sid = :sid And kcid=:kcid And status =2 ", array(':sid' => $sid,':kcid'=> $kcid));
	}else{
		$hasSign =  pdo_fetchcolumn("SELECT count(distinct ksid) FROM " . GetTableName('kcsign') . " WHERE sid = :sid And kcid=:kcid ", array(':sid' => $sid,':kcid'=> $kcid));
	}
	$restnumber = $buycourse - $hasSign;
	$reslut['buycourse'] = $buycourse;
	$reslut['hasSign'] = $hasSign;
	$reslut['restnumber'] = $restnumber;
	return $reslut;
}




/*根据team里的id 移除一个虚拟粉丝
* @author 高贵血迹
*/
function DelOneFans($id){
	$teammember = pdo_fetch("SELECT id,masterid FROM " . GetTableName('sale_team') . " where id = '{$id}' ");
	$isfull = CheckTemIsFull($teammember['masterid']);
	if($isfull){
		$reslut['reslut'] = false;
		$reslut['msg'] = '已满队伍不可执行此操作';
	}else{
		if($teammember){
			pdo_delete(GetTableName('sale_team',false),array('id' => $id));
			$reslut['reslut'] = true;
		}else{
			$reslut['reslut'] = false;
			$reslut['msg'] = '词条记录不存在,请刷新页面';
		}
	}
	return $reslut;
}

/*根据kcid查询课程评论列表
* @author 高贵血迹
*/
function GetKcComment($kcid,$comfrom) {
	$condition = '';
	if($comfrom == 'stu'){
		$condition .= 'And is_show = 0';
	}
	$kcinfo = pdo_fetch("SELECT schoolid FROM " . GetTableName('tcourse') . " where id = '{$kcid}' ");
	$school = pdo_fetch("SELECT spic,tpic FROM " . GetTableName('index') . " where id = '{$kcinfo['schoolid']}' ");
	$list = pdo_fetchall('SELECT * FROM ' . GetTableName('kcpingjia') . " WHERE kcid = '{$kcid}' And is_master = 1 And type = 2 $condition ORDER BY createtime DESC ");//查询主评论列表
	if($list){
		foreach($list as $key => $row){
			$list[$key]['ksname'] = false; 
			if($row['ksid']){
				$ksorder = GetOneKcKsOrder($kcid,$row['ksid']);
				$list[$key]['ksname'] = '第'.$ksorder['nuber'].'课';
			}
			if($row['star'] >= 1){
				$h5 = "<li class='imageOne'></li>";
				$list[$key]['h5'] = str_repeat($h5,$row['star']);
			}
			$user = pdo_fetch("SELECT tid,sid,pard FROM " . GetTableName('user') . " where id = '{$row['userid']}' ");
			if(!empty($user)){
				$list[$key]['user'] = $user;
				if($user['tid'] > 0){
					$teacher = pdo_fetch('SELECT tname,thumb FROM ' . GetTableName('teachers') . " WHERE id = '{$user['tid']}'");
					$list[$key]['name'] = $teacher['tname'].' 老师';
					$list[$key]['icon'] = !empty($teacher['thumb'])?tomedia($teacher['thumb']):tomedia($school['tpic']);
				}
				if($user['sid'] > 0){
					$guanxi = get_guanxi($user['pard']);
					$student = pdo_fetch('SELECT s_name,icon FROM ' . GetTableName('students') . " WHERE id = '{$user['sid']}'");
					$list[$key]['name'] = $student['s_name'].' '.$guanxi;
					$list[$key]['icon'] = !empty($student['icon'])?tomedia($student['icon']):tomedia($school['spic']);
				}
				$list[$key]['day'] = sub_day($row['createtime']);
				$list[$key]['list']  = pdo_fetchall('SELECT * FROM ' . GetTableName('kcpingjia') . " WHERE masterid = '{$row['id']}' $condition ORDER BY createtime DESC ");
				if($list[$key]['list']){
					foreach($list[$key]['list'] as $k => $r){
						$thisuser = pdo_fetch("SELECT tid,sid,pard FROM " . GetTableName('user') . " where id = '{$r['userid']}' ");
						if(!empty($thisuser)){
							if($thisuser['tid'] != 0){
								$thistea = pdo_fetch('SELECT tname,thumb FROM ' . GetTableName('teachers') . " WHERE id = '{$thisuser['tid']}'");
								$list[$key]['list'][$k]['name'] = $thistea['tname'].' 老师';
								//$list[$key]['list'][$k]['icon'] = !empty($thistea['thumb'])?tomedia($thistea['thumb']):tomedia($school['tpic']);暂不需要取头像
							}
							if($thisuser['sid'] != 0){
								$gx = get_guanxi($thisuser['pard']);
								$thisstu = pdo_fetch('SELECT s_name,icon FROM ' . GetTableName('students') . " WHERE id = '{$thisuser['sid']}'");
								$list[$key]['list'][$k]['name'] = $thisstu['s_name'].' '.$gx;
								//$list[$key]['list'][$k]['icon'] = !empty($thisstu['icon'])?tomedia($thisstu['icon']):tomedia($school['spic']);
							}
							$list[$key]['list'][$k]['day'] = sub_day($r['createtime']);
						}else{
							unset($list[$key]['list'][$k]);
						}
					}
				}
			}else{
				unset($list[$key]);
			}
		}
	}
	return $list;
}

/*根据plid type 设置评论状态
* @author 高贵血迹
*/
function SetKcComment($plid,$type){
	$comment = pdo_fetch('SELECT * FROM ' . GetTableName('kcpingjia') . " WHERE id = '{$plid}'  ");
	if(!empty($comment)){
		$data = array();
		if($type == 0){
			$data['is_show'] = 0;
			$word = '显示';
		}
		if($type == 1){
			$data['is_show'] = 1;
			$word = '隐藏';
		}
		if($type == 'del'){
			$word = '删除';
		}
		if($comment['is_master'] == 1){
			if($type == 'del'){
				pdo_delete(GetTableName('kcpingjia',false), array('masterid' => $comment['masterid']));
			}else{
				pdo_update(GetTableName('kcpingjia',false), $data, array('id' => $plid));
			}
		}else{
			if($type == 'del'){
				pdo_delete(GetTableName('kcpingjia',false), array('id' => $plid));
			}else{
				pdo_update(GetTableName('kcpingjia',false), $data, array('id' => $plid));
			}
		}
		$reslut['msg'] = $word.'本条评论成功';
		$reslut['reslut'] = true;
	}else{
		$reslut['msg'] = '本条评论不存在,请尝试刷新本页';
		$reslut['reslut'] = false;
	}
	return $reslut;
}

/*根据kcid  获取报名本课程正式学员
* 从coursbuy里查询
* @author 高贵血迹
*/
function GetOneKcStuList($kcid,$cond,$type = false){
	$kcinfo = pdo_fetch("SELECT schoolid FROM " . GetTableName('tcourse') . " where id = :id ", array(':id' => $kcid));
	$school = pdo_fetch("SELECT spic FROM " . GetTableName('index') . " where id = :id ", array(':id' => $kcinfo['schoolid']));
	$allks = pdo_fetchcolumn("SELECT COUNT(id) FROM " . GetTableName('kcbiao') . " where kcid = :kcid ", array( ':kcid' => $kcid));
	$condition = "";
	$pindex    = max(1, intval($cond['page']));
    $psize     = 10;
	$condition1 = "";
	if(!empty($cond['s_name'])){
		$condition1 .= " And s_name LIKE '{$cond['s_name']}' ";
	}
	if(!empty($cond['mobile'])){
		$condition1 .= " And mobile = '{$cond['mobile']}' ";
	}
	if(!empty($condition1)){
		$str = '';
		$students = pdo_fetchall('SELECT id FROM ' . GetTableName('students') . " WHERE schoolid = '{$kcinfo['schoolid']}' $condition1 ");
		if(!empty($students)){
			foreach($students as $val){
				$str .= $val['id'].',';
			}
			$str = rtrim($str,',');
		}
		if(!empty($str)){
			$condition .= " And FIND_IN_SET(sid,'{$str}') ";
		}
	}
	if($cond['ks_type'] > 0){//此处指的是前端的搜索添加 消耗情况
		$list = pdo_fetchall("SELECT * FROM " . GetTableName('coursebuy') . " where schoolid = '{$kcinfo['schoolid']}' And kcid = '{$kcid}' And is_change != 1 $condition ORDER BY createtime DESC,id DESC ");
	}else{
		if($type){
			$list = pdo_fetchall("SELECT * FROM " . GetTableName('coursebuy') . " where schoolid = '{$kcinfo['schoolid']}' And kcid = '{$kcid}' And is_change != 1 ORDER BY createtime DESC,id DESC ");
		}else{
			$list = pdo_fetchall("SELECT * FROM " . GetTableName('coursebuy') . " where schoolid = '{$kcinfo['schoolid']}' And kcid = '{$kcid}' And is_change != 1 $condition ORDER BY createtime DESC,id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
		}
		
	}
	if(!empty($list)){
		foreach($list as $key => $row){
			$stuinfo = pdo_fetch("SELECT s_name,icon,mobile FROM " . GetTableName('students') . " where id = :id ", array(':id' => $row['sid']));
			if(!empty($stuinfo)){
				$list[$key]['s_name'] = $stuinfo['s_name'];
				$list[$key]['icon'] = $stuinfo['icon'] ? tomedia($stuinfo['icon']) : tomedia($school['spic']) ;
				$list[$key]['mobile'] = $stuinfo['mobile'];
				if($kcinfo['kc_type'] == 0){
					$list[$key]['ksinfo'] = GetRestKsBySid($kcid,$row['sid']);
				}else{
					$list[$key]['ksinfo'] = GetRestKsBySid($kcid,$row['sid'],false);
				}
				$list[$key]['order'] = pdo_fetch("SELECT tid,sale_type,pay_type,userid FROM " . GetTableName('order') . " where id = :id ", array(':id' => $row['orderid']));
				$list[$key]['belong'] = '';
				if($list[$key]['order']['userid']){
					$user = pdo_fetch("SELECT openid FROM " . GetTableName('user') . " where id = :id ", array(':id' => $list[$key]['order']['userid']));
					$list[$key]['belong'] = CheckFansBelong($kcid,$list[$key]['order']['userid'],$user['openid']);
				}
				$list[$key]['ks_jd'] = 0;
				$list[$key]['allks'] = $allks;
				$list[$key]['hasSign'] = $list[$key]['ksinfo']['hasSign'];
				$list[$key]['ks_jd'] = round(($list[$key]['hasSign']/$allks)*100,0);
				if(empty($list[$key]['hasSign'])){
					$list[$key]['hasSign'] = 0;
				}
				if($list[$key]['ks_jd'] >= 100){
					$list[$key]['mission'] = 'success';
				}else{
					$list[$key]['mission'] = 'active';
				}
				if($kcinfo['kc_type'] == 0){
					if($cond['ks_type'] == 1){//此处指的是前端的搜索添加 消耗情况
						if($list[$key]['ksinfo']['restnumber'] < 1){
							unset($list[$key]);
						}
					}
					if($cond['ks_type'] == 2){
						if($list[$key]['ksinfo']['restnumber'] > 0 ){
							unset($list[$key]);
						}
					}
					if($cond['ks_type'] == 3){
						if($list[$key]['ksinfo']['hasSign'] > 0 ){
							unset($list[$key]);
						}
					}
				}
			}else{
				unset($list[$key]);//学生表里删除了不显示
			}
		}
		if($cond['ks_type'] > 0){//此处指的是前端的搜索添加 消耗情况

		}else{
			$total = pdo_fetchcolumn('SELECT COUNT(id) FROM ' . GetTableName('coursebuy') . " where kcid = '{$kcid}' And is_change != 1 $condition ");
			$pager = paginations($total, $pindex, $psize,'',array('before' => 0, 'after' => 0, 'ajaxcallback' => true, 'callbackfuncname' => 'page_stulist'));
		}
		$result['list'] = $list;
		$result['condition'] = $condition1;
		$result['total'] = $total;
		$result['pager'] = $pager;
		return $result;
	}else{
		return false;
	}
}

function GetOneKcSignList($kcid,$kc_type,$cond){
	$stulist = GetOneKcStuListForName($kcid);
	$condition = "";
	$pindex    = max(1, intval($cond['page']));
    $psize     = 10;
	if($cond['ksid'] > 0){
		$condition .= " And ksid = '{$cond['ksid']}' ";
		$checknub = GetOneKcKsOrder($kcid,$cond['ksid']);
		$nubmer = $checknub['nuber'];
	}
	if($cond['sign_porsen'] == 1){
		$condition .= " And tid > 0 And sid = 0 ";
	}
	if($cond['sign_porsen'] == 2){
		$condition .= " And sid > 0 And tid = 0 ";
	}
	if($cond['qr_tid'] > 0 || $cond['qr_tid'] == -1){
		$condition .= " And qrtid = '{$cond['qr_tid']}' ";
	}
	if($cond['sign_type'] > 0 || $cond['sign_type'] == -1){
		$condition .= " And status = '{$cond['sign_type']}' ";
	}
	$dknub = 0;$dqrnub = 0;$qknub = 0;$qjnub = 0;
	$list = pdo_fetchall("SELECT * FROM " . GetTableName('kcsign') . " WHERE kcid = '{$kcid}' $condition ORDER BY createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
	foreach($list as $key => $row){
		if($row['status'] == 0){
			$qknub++; //缺课
		}
		if($row['status'] == 1){
			$dqrnub++;//待确认
		}
		if($row['status'] == 2){
			$dknub++;//到课
		}
		if($row['status'] == 3){
			$qjnub++;//请假
		}
		if($kc_type != 1){
			if(empty($cond['ksid'])){//不带ksid的去查第几课 节约消耗
				$checknubs = GetOneKcKsOrder($kcid,$row['ksid']);
				$nubmer = $checknubs['nuber'];
			}
			$list[$key]['nubmer'] = $nubmer;
			$list[$key]['time'] = date('Y-m-d H:i',$row['createtime']);
		}else{
			$ksinfo = pdo_fetch("SELECT name,menu_id FROM " . GetTableName('kcbiao') . " where id = :id ", array(':id' => $row['ksid']));
			if($ksinfo['menu_id']){
				$list[$key]['menuname'] = '默认章节';
				$menu = pdo_fetch("SELECT name FROM " . GetTableName('kc_menu') . " where id = :id ", array(':id' => $ksinfo['menu_id']));
				if($menu['name']){
					$list[$key]['menuname'] = $menu['name'];
				}
			}
			$list[$key]['ksname'] = $ksinfo['name'];
			$list[$key]['time'] = date('Y-m-d H:i',$row['signtime']);
		}
		if($row['qrtid'] > 0 || $row['qrtid'] == -1){
			$qrtea = pdo_fetch("SELECT tname FROM " . GetTableName('teachers') . " where id = :id ", array(':id' => $row['qrtid']));
			if($qrtea){
				$list[$key]['qrname'] = $qrtea['tname'];
			}else{
				$list[$key]['qrname'] = '管理员';
			}
		}
		if($row['sid'] != 0){
			$stuinfo = pdo_fetch("SELECT s_name,mobile FROM " . GetTableName('students') . " where id = :id ", array(':id' => $row['sid']));
			if($stuinfo){
				$list[$key]['name'] = $stuinfo['s_name'];
				$list[$key]['mobile'] = $stuinfo['mobile'];
			}else{
				$list[$key]['name'] = "已删除";
			}
			if($kc_type != 1){
				$checkrestks = GetRestKsBySid($kcid,$row['sid']);
				$list[$key]['restks'] = $checkrestks['restnumber'];
			}
		}
		if($row['tid'] != 0){
			$teainfo = pdo_fetch("SELECT tname,mobile FROM " . GetTableName('teachers') . " where id = :id ", array(':id' => $row['tid']));
			if($teainfo){
				$list[$key]['name'] = $teainfo['tname'];
				$list[$key]['mobile'] = $teainfo['mobile'];
			}else{
				unset($list[$key]);
			}
		}
	}
	$total = pdo_fetchcolumn('SELECT COUNT(id) FROM ' . GetTableName('kcsign') . " WHERE kcid = '{$kcid}' $condition ");
    $pager = paginations($total, $pindex, $psize,'',array('before' => 0, 'after' => 0, 'ajaxcallback' => true, 'callbackfuncname' => 'page_signlist'));
	$result['list'] = $list;
	$result['total'] = $total;
	$result['pager'] = $pager;
	return $result;
}


/*根据kcid  获取报名本课程正式学员 (仅列出sid和sname)
* 从coursbuy里查询
* @author 高贵血迹
*/
function GetOneKcStuListForName($kcid,$header = false){
	if($header){//取头像的操作仅适用于随机只取6人头像
		$kcinfo = pdo_fetch("SELECT schoolid FROM " . GetTableName('tcourse') . " where id = :id ", array(':id' => $kcid));
		$school = pdo_fetch("SELECT spic FROM " . GetTableName('index') . " where id = :id ", array(':id' => $kcinfo['schoolid']));
		$list = pdo_fetchall("SELECT sid FROM " . GetTableName('coursebuy') . " WHERE kcid = :kcid And is_change != :is_change ORDER BY RAND() limit 6 ", array( ':kcid' => $kcid, 'is_change' => 1 )); 
	}else{
		$list = pdo_fetchall("SELECT sid FROM " . GetTableName('coursebuy') . " WHERE kcid = :kcid And is_change != :is_change ", array( ':kcid' => $kcid, 'is_change' => 1 )); 
	}
	if(!empty($list)){
		foreach($list as $key => $row){
			$stuinfo = pdo_fetch("SELECT s_name,icon FROM " . GetTableName('students') . " where id = :id ", array(':id' => $row['sid']));
			if($header){
				$list[$key]['icon'] = !empty($stuinfo['icon'])?tomedia($stuinfo['icon']):tomedia($school['spic']);
			}
			if(!empty($stuinfo)){
				$list[$key]['s_name'] = $stuinfo['s_name'];
			}else{
				unset($list[$key]);
			}
		}
		return $list;
	}else{
		return false;
	}
}

/*根据kcid  获取课程的课表排序
* 返回 第几课 年 月 日 时,ksid
* @author 高贵血迹
*/
function GetOneKcKsOrder($kcid,$ksid = 0){
	$list = pdo_fetchall("SELECT id,date FROM " . GetTableName('kcbiao') . " WHERE kcid = :kcid ORDER BY date ASC ", array( ':kcid' => $kcid)); 
	$thisorder = '';
	$nowtime = time();
	$nowday = strtotime(date('Y/m/d',time()));
	if(!empty($list)){
		$nuber = 1;
		foreach($list as $key => $row){
			$list[$key]['isover'] = false;
			if($nowday > $row['date']){
				$list[$key]['isover'] = true;
			}
			$list[$key]['nuber'] = $nuber;
			$list[$key]['mob_date'] = date('y/m/d',$row['date']);
			$list[$key]['date'] = date('m月d',$row['date']);
			$list[$key]['hour'] = date('H:i',$row['date']);
			$list[$key]['year'] = date('Y',$row['date']);
			if($ksid >= 1 && $row['id'] == $ksid){
				$thisorder = $list[$key];
				break;
			}
			$nuber++;
		}
	}
	if($ksid >= 1){
		return $thisorder;
	}else{
		return $list;
	}
}


/*根据kcid  获取报名本课程总数 不排除团购得订单
* $incxn true加上虚拟人数
* @author 高贵血迹
*/
function GetKcContStu($kcid,$incxn = false){
	// $allstu = pdo_fetchcolumn("select count(distinct id) FROM ".GetTableName('order')." WHERE kcid = '{$kcid}' And status = 2 ");
	$allstu = pdo_fetchcolumn("SELECT count( DISTINCT sid) FROM " . GetTableName('coursebuy') . " where kcid = '{$kcid}' AND sid > 0 ");
	$kcinfo = pdo_fetch("SELECT yibao FROM " . GetTableName('tcourse') . " where id = :id ", array(':id' => $kcid));
	if($incxn){
		$allstu = $allstu + $kcinfo['yibao'];
	}
	return $allstu;
}

/**
* 根据kcid 查询真实报名支付的人数 排除参团和助力未成功的
* @author 高贵血迹
**/
function GetKcSiguNub($kcid){
	// $checkorder = pdo_fetch("select id FROM ".GetTableName('order')." WHERE kcid = '".$kcid."' And status = 2 ");
	// $orders = pdo_fetchall("select team_id FROM ".GetTableName('order')." WHERE kcid = '{$kcid}' And status = 2 GROUP BY sid ");
	$yb = 0;
	$allstunum = pdo_fetchcolumn("SELECT count( DISTINCT sid) FROM " . GetTableName('coursebuy') . " where kcid = '{$kcid}' AND sid > 0 And is_change != 1");
	if($allstunum > 0){
		$yb = $allstunum;
	}
	return $yb;	
}

/*根据kcid 和number 获取随机5个报名用户的头像和名称
* @author 高贵血迹
*/
function GetKcStuFans($kcid,$number){
	$kcinfo = pdo_fetch("SELECT schoolid FROM " . GetTableName('tcourse') . " where id = :id ", array(':id' => $kcid));
	$school = pdo_fetch("SELECT spic FROM " . GetTableName('index') . " where id = '{$kcinfo['schoolid']}' ");
	$allstu = pdo_fetchall("SELECT sid FROM " . GetTableName('order') . " WHERE kcid = '{$kcid}' And type = 1  GROUP BY sid LIMIT 0,".$number);
	if($allstu){
		foreach($allstu as $key => $row){
			$student = pdo_fetch("SELECT s_name,icon FROM " . GetTableName('students') . " WHERE id = '{$row['sid']}' ");
			$allstu[$key]['name'] = $student['s_name'];
			$allstu[$key]['icon'] = !empty($student['icon'])?tomedia($student['icon']):tomedia($school['spic']);
		}
	}
	return $allstu;
}


/*根据kcid $limit 获取粉丝排行榜
* $openid不为空则同时获取此粉丝的排名与上一名的差距
* @author 高贵血迹
*/
function GetNorFansRank($kcid,$limit = 0,$openid = ''){
	$kcinfo = pdo_fetch("SELECT weid,schoolid FROM " . GetTableName('tcourse') . " where id = :id ", array(':id' => $kcid));
	$allfans = pdo_fetchall("SELECT distinct( superior_uid ) FROM " . GetTableName('promote_fans') . " WHERE kcid = '{$kcid}' And superior_uid != '' ");
	if($allfans){		
		foreach($allfans as $k => $r){//计算下属粉丝需排除自己
			$allfans[$k]['count']= pdo_fetchcolumn("SELECT count(distinct openid) FROM " . GetTableName('promote_fans') . " WHERE kcid = '{$kcid}' And openid != '{$r['superior_uid']}' And superior_uid = '{$r['superior_uid']}' ");
			if(!CheckFans($kcinfo['weid'],$r['superior_uid'])){
				unset($allfans[$k]);
			}	
		}
		sort_array_multi($allfans, ['count'], ['desc']);
		$myrank = array();
		if(!empty($openid)){
			$myrank['myrank'] = count($allfans);
			$myrank['mycount'] = 0;
			$myrank['beforrank'] = $allfans[count($allfans)-1]['count']+1;
			foreach($allfans as $in => $it){
				if($openid == $it['superior_uid']){
					$myrank['myrank'] = $in+1;
					$myrank['mycount'] = $it['count'];
				}
			}
			$myrank['beforrank'] = ($allfans[$myrank['myrank']-2]['count'] - $myrank['mycount'])+1;
		}
		if($limit >0){
			$newrank = array_slice($allfans,0,$limit,false);
		}else{
			$newrank = $allfans;
		}
		foreach($newrank as $key => $row){
			$newrank[$key]['rank'] = $key+1;
			$newrank[$key]['oushu'] = false;
			if(($key+1)%2==0){
				$newrank[$key]['oushu'] = true;
			}
			$dz_user = GetWeFans($kcinfo['weid'],$row['superior_uid']);
			$newrank[$key]['name'] = $dz_user['nickname'];
			$newrank[$key]['icon'] = $dz_user['avatar'];
			$newrank[$key]['openid'] = $row['superior_uid'];
		}
	}
	$result['myrank'] = $myrank;
	$result['newrank'] = $newrank;
	$result['result'] = true;
	return $result;
}

/*根据kcid $limit 获取推广员排行榜
* $tid不为空则同时获取此推广员的排名与上一名的差距
* @author 高贵血迹
*/
function GetProTeaRank($kcid,$limit = 0,$tid = ''){
	$kcinfo = pdo_fetch("SELECT weid,schoolid FROM " . GetTableName('tcourse') . " where id = :id ", array(':id' => $kcid));
	$allfans = pdo_fetchall("SELECT tid FROM " . GetTableName('promote_team') . " WHERE kcid = '{$kcid}' And tid != '' And tid > 0 ");
	if($allfans){		
		foreach($allfans as $k => $r){//计算下属粉丝
			$allfans[$k]['count'] = pdo_fetchcolumn("SELECT count(distinct sid) FROM " . GetTableName('order') . " WHERE kcid = '{$kcid}' And status = 2 And sid >0 And superior_tid = '{$r['tid']}' ");
			$allfans[$k]['pro'] = GetKcTgProcess($kcid,$r['tid']);		
		}
		sort_array_multi($allfans, ['count'], ['desc']);
		$myrank = array();
		if($tid>0){
			$myrank['myrank'] = count($allfans);
			$myrank['mycount'] = 0;
			$myrank['beforrank'] = $allfans[count($allfans)-1]['count']+1;
			foreach($allfans as $in => $it){
				if($tid == $it['tid']){
					$myrank['myrank'] = $in+1;
					$myrank['mycount'] = $it['count'];
					$myrank['mypro'] = $it['pro'];
				}
			}
			$myrank['beforrank'] = ($allfans[$myrank['myrank']-2]['count'] - $myrank['mycount'])+1;
		}
		if($limit >0){
			$newrank = array_slice($allfans,0,$limit,false);
		}else{
			$newrank = $allfans;
		}
		foreach($newrank as $key => $row){
			$newrank[$key]['rank'] = $key+1;
			$newrank[$key]['oushu'] = false;
			if(($key+1)%2==0){
				$newrank[$key]['oushu'] = true;
			}
			$teainfo = pdo_fetch("SELECT tname,thumb FROM " . GetTableName('teachers') . " where id = :id ", array(':id' => $row['tid']));
			$newrank[$key]['name'] = $teainfo['tname'];
			$newrank[$key]['icon'] = tomedia($teainfo['thumb']);
		}
	}
	$result['myrank'] = $myrank;
	$result['newrank'] = $newrank;
	$result['result'] = true;
	return $result;
}

/*
* 根据masterid设置队伍学生状态为解锁
* @author 高贵血迹
*/
function SetTeamStuStatus($masterid,$saletype){
	if($saletype == 1){
		$allteam = pdo_fetchall("SELECT userid,kcid,openid FROM " . GetTableName('sale_team') . " where :masterid = masterid ", array(':masterid' => $masterid));
		foreach($allteam as $row){
			if($row['userid'] > 0){
				$stu = pdo_fetch("SELECT sid FROM " . GetTableName('user') . " where :id = id ", array(':id' => $row['userid']));
				if(!empty($stu)){
					SetFansSale($row['kcid'],$row['userid'],$row['openid']);
					pdo_update(GetTableName('user',false), array('status' => 0), array('id' => $row['userid']));
					pdo_update(GetTableName('students',false), array('status' => 0), array('id' => $stu['sid']));
				}
			}
		}
	}
	if($saletype == 2){ //助力只需解锁队长
		$team = pdo_fetch("SELECT userid,kcid,openid FROM " . GetTableName('sale_team') . " where :id = id ", array(':id' => $masterid));
		$stu = pdo_fetch("SELECT sid FROM " . GetTableName('user') . " where :id = id ", array(':id' => $team['userid']));
		if(!empty($stu)){
			SetFansSale($team['kcid'],$team['userid'],$team['openid']);
			pdo_update(GetTableName('user',false), array('status' => 0), array('id' => $team['userid']));
			pdo_update(GetTableName('students',false), array('status' => 0), array('id' => $stu['sid']));
		}
	}
}

/*
* 根据masterid设置队伍为购买课程成 增加课时购买记录
* @author 高贵血迹
*/
function SetTeamStuCour($kcid,$masterid,$ksnum,$saletype){
	$kcinfo =  pdo_fetch("SELECT weid,schoolid,overtimeday,FirstNum FROM " . GetTableName('tcourse') . " where :id = id", array(':id' => $kcid));
	if($saletype == 1){
		$team = pdo_fetch("SELECT masterid FROM " . GetTableName('sale_team') . " where :id = id", array(':id' => $masterid));
		$allteam = pdo_fetchall("SELECT userid,orderid,is_really FROM " . GetTableName('sale_team') . " where :masterid = masterid ", array(':masterid' => $team['masterid']));
		foreach($allteam as $row){
			if($row['userid'] > 0 && $row['is_really'] != 1 ){
				$user = pdo_fetch("SELECT sid FROM " . GetTableName('user') . " where :id = id", array(':id' => $row['userid']));
				if(!empty($user)){
					$ygks = pdo_fetch("SELECT ksnum,id FROM " . GetTableName('coursebuy') . " where kcid=:kcid AND :sid = sid", array(':kcid' => $kcid,':sid'=>$user['sid']));
					$overday = $kcinfo['overtimeday'];
					$overtime = 0;
					if($overday != 0 ){
						$overtime = strtotime(date("Y-m-d",time())) + 86399 + 86400*$overday;
					}
					if(!empty($ygks)){
						$newksnum = $ygks['ksnum'] + $ksnum;
						$data_coursebuy = array(
							'ksnum'      => $newksnum,
							'overtime'  => $overtime
						);
						pdo_update(GetTableName('coursebuy',false),$data_coursebuy,array('id' => $ygks['id']));
					}else{
						$data_coursebuy = array(
							'weid'       => $kcinfo['weid'],
							'schoolid'   => $kcinfo['schoolid'],
							'userid'     => $row['userid'],
							'sid'        => $user['sid'],
							'kcid'       => $kcid,
							'orderid'    => $row['orderid'],
							'ksnum'      => $kcinfo['FirstNum'],
							'overtime'  => $overtime,
							'createtime' => time()
						);
						pdo_insert(GetTableName('coursebuy',false),$data_coursebuy);
					}
				}
			}
		}
	}
	if($saletype == 2){ //助力只写队长的记录
		$team = pdo_fetch("SELECT userid,orderid FROM " . GetTableName('sale_team') . " where :id = id ", array(':id' => $masterid));
		$user = pdo_fetch("SELECT sid FROM " . GetTableName('user') . " where :id = id", array(':id' => $team['userid']));
		if(!empty($user)){
			$ygks = pdo_fetch("SELECT ksnum,id FROM " . GetTableName('coursebuy') . " where kcid=:kcid AND :sid = sid", array(':kcid' => $kcid,':sid'=>$user['sid']));
			$overday = $kcinfo['overtimeday'];
			$overtime = 0;
			if($overday != 0 ){
				$overtime = strtotime(date("Y-m-d",time())) + 86399 + 86400*$overday;
			}
			if(!empty($ygks)){
				$newksnum = $ygks['ksnum'] + $ksnum;
				$data_coursebuy = array(
					'ksnum'      => $newksnum,
					'overtime'  => $overtime
				);
				pdo_update(GetTableName('coursebuy',false),$data_coursebuy,array('id' => $ygks['id']));
			}else{
				$data_coursebuy = array(
					'weid'       => $kcinfo['weid'],
					'schoolid'   => $kcinfo['schoolid'],
					'userid'     => $team['userid'],
					'sid'        => $user['sid'],
					'kcid'       => $kcid,
					'orderid'    => $team['orderid'],
					'ksnum'      => $kcinfo['FirstNum'],
					'overtime'   => $overtime,
					'createtime' => time()
				);
				pdo_insert(GetTableName('coursebuy',false),$data_coursebuy);
			}
		}
	}
	pdo_update(GetTableName('sale_team',false),array('is_success' => 1),array('kcid' => $kcid,'masterid' => $masterid));
	return $data_coursebuy;
}

/*
* 根据kcid openid 获取本课的排名
* @author 高贵血迹
*/
function GetNorFansMyRank($kcid,$openid){
	$kcinfo = pdo_fetch("SELECT weid,schoolid FROM " . GetTableName('tcourse') . " where id = :id ", array(':id' => $kcid));
	$allfans = pdo_fetchall("SELECT distinct( superior_uid ) FROM " . GetTableName('promote_fans') . " WHERE kcid = '{$kcid}' And superior_uid != '' ");
	if($allfans){		
		foreach($allfans as $k => $r){//计算下属粉丝需排除自己
			$allfans[$k]['count']= pdo_fetchcolumn("SELECT count(distinct openid) FROM " . GetTableName('promote_fans') . " WHERE kcid = '{$kcid}' And openid != '{$r['superior_uid']}' And superior_uid = '{$r['superior_uid']}' ");
			if(!CheckFans($kcinfo['weid'],$r['superior_uid'])){
				unset($allfans[$k]);
			}	
		}
		sort_array_multi($allfans, ['count'], ['desc']);
		$myrank = array();
		if(!empty($openid)){
			$myrank['myrank'] = count($allfans);
			$myrank['mycount'] = 0;
			$myrank['beforrank'] = $allfans[count($allfans)-1]['count']+1;
			foreach($allfans as $in => $it){
				if($openid == $it['superior_uid']){
					$myrank['myrank'] = $in+1;
					$myrank['mycount'] = $it['count'];
				}
			}
			$myrank['beforrank'] = ($allfans[$myrank['myrank']-2]['count'] - $myrank['mycount'])+1;
		}
	}
	$result['myrank'] = $myrank;
	$result['result'] = true;
	return $result;
}

/*根据kcid 获取课程招生雷达图
* @author 高贵血迹
*/
// indicator : [
	// {text : '团购或助力', max  : 100},
	// {text : '直接购买', max  : 100},
	// {text : '学员拉人', max  : 100},
	// {text : '粉丝拉人', max  : 100},
	// {text : '手动导入', max  : 100},
	// {text : '推广员招生', max  : 100}
// ],
function GetKcRadar($kcid,$schoolid){
	if($kcid > 0){
		$condition = " And kcid = '{$kcid}' ";
	}
	$allstu = pdo_fetchall("SELECT * FROM " . GetTableName('order') . " WHERE schoolid = '{$schoolid}' And type = 1 And status = 2 $condition GROUP BY kcid,sid ORDER BY id DESC ");
	$count = count($allstu);
	if($count == 0){
		$count = 1;
	}
	$sale = 0;$zhijie = 0;$xuyuan = 0;$fans = 0;$shoudong = 0;$tuiguang = 0;
	foreach($allstu as $row){
		if($row['sale_type'] != 0){
			$sale++;
		}
		if($row['sale_type'] == 0 && $row['pay_type'] != 'cash'){
			$zhijie++;
		}
		if($row['userid']){
			$user = pdo_fetch("SELECT openid FROM " . GetTableName('user') . " WHERE id = '{$row['userid']}' ");
			$checfans = pdo_fetch("SELECT * FROM " . GetTableName('promote_fans') . " WHERE kcid = '{$row['kcid']}' And openid = '{$user['openid']}' ");
			if($checfans['superior_userid'] != $checfans['userid']){
				$xuyuan++;
			}
			if($checfans['superior_uid'] != $checfans['openid']){
				$fans++;
			}
			if($row['pay_type'] == 'cash' && !empty($row['tid'])){
				$shoudong++;
			}
		}
		if($row['superior_tid']){//推广员方面直接查order表
			$tuiguang++;
		}
	}
	$sale = intval(100*($sale/$count));
	$zhijie = intval(100*($zhijie/$count));
	$xuyuan = intval(100*($xuyuan/$count));
	$fans = intval(100*($fans/$count));
	$shoudong = intval(100*($shoudong/$count));
	$tuiguang = intval(100*($tuiguang/$count));
	$datas = array($sale, $zhijie, $xuyuan, $fans, $shoudong, $tuiguang);
	$result['datas'] = $datas;
	$result['result'] = true;
	return $result;
}


/*根据kcid teamid userid或openid 查询本课程粉丝海报
* 返回
* @author 高贵血迹
*/
function GetPopByKc($kcid,$openid,$teamid,$userid,$tid = 0,$type = ''){
	global $_W;
	$kcinfo = pdo_fetch("SELECT weid,schoolid,sale_type,tg_id,sale_id FROM " . GetTableName('tcourse') . " where id = '{$kcid}' ");
	$saleset = pdo_fetch("SELECT use_pop,pop_id,pop_img FROM " . GetTableName('kc_saleset') . " WHERE id = :id ", array(':id' => $kcinfo['sale_id']));
	$tgset = pdo_fetch("SELECT use_pop,pop_id,pop_img FROM " . GetTableName('kc_promote') . " WHERE id = :id ", array(':id' => $kcinfo['tg_id']));
	$school = pdo_fetch("SELECT spic,tpic,logo FROM " . GetTableName('index') . " where id = '{$kcinfo['schoolid']}' ");
	if(empty($teamid)){//推广海报查询
		if($tid == 0){
			if($userid){
				$pop = pdo_fetch("SELECT id,pop_url FROM " . GetTableName('promote_pop') . " where type = 2 And kcid = '{$kcid}' And userid = '{$userid}' ");
			}else{
				$pop = pdo_fetch("SELECT id,pop_url FROM " . GetTableName('promote_pop') . " where type = 2 And kcid = '{$kcid}' And openid = '{$openid}' ");
			}
		}else{//推广员老师查询方法不同
			$pop = pdo_fetch("SELECT id,pop_url FROM " . GetTableName('promote_pop') . " where type = 2 And kcid = '{$kcid}' And tid = '{$tid}' ");
		}
		//$url = $_W['siteroot']."app/index.php?i=3&c=entry&goto=video_kc&do=hookcom&m=fm_jiaoyu&com_form=5&schoolid={$kcinfo['schoolid']}&kcid={$kcid}&masteruid={$openid}&masteruserid={$userid}&mastertid={$tid}";
		//$result['url'] = $url;
		if(empty($pop) || $type == 'refrash'){
			$url = $_W['siteroot']."app/index.php?i={$kcinfo['weid']}&c=entry&goto=video_kc&do=hookcom&m=fm_jiaoyu&com_form=5&schoolid={$kcinfo['schoolid']}&kcid={$kcid}&masteruid={$openid}&masteruserid={$userid}&mastertid={$tid}";
			$qrcode = PosterQrcode($url);
		}else{
			$popurl = tomedia($pop['pop_url']);
		}
	}else{//营销海报查询
		$pop = pdo_fetch("SELECT id,pop_url FROM " . GetTableName('promote_pop') . " where type = 1 And kcid = '{$kcid}' And teamid = '{$teamid}' And userid = '{$userid}' ");
		if(empty($pop) || $type == 'refrash'){
			if($kcinfo['sale_type'] == 1){
				$com_form = 2;
			}
			if($kcinfo['sale_type'] == 2){
				$com_form = 3;
			}
			$url = $_W['siteroot']."app/index.php?i={$kcinfo['weid']}&c=entry&goto=video_kc&do=hookcom&m=fm_jiaoyu&com_form={$com_form}&schoolid={$kcinfo['schoolid']}&kcid={$kcid}&masteruid={$openid}&masteruserid={$userid}&teamid={$teamid}";
			$qrcode = PosterQrcode($url);
		}else{
			$popurl = tomedia($pop['pop_url']);
			$result['result'] = true;
		}
	}
	$account_api = WeAccount::create($weid);
	if(empty($pop) || $type == 'refrash'){//若没有旧海报则生成新的 主动刷新则修改为新的
		$configs = array();
		if(empty($teamid)){//推广海报
			if($tgset['use_pop'] != 1 || empty($tgset['pop_img'])){
				$result['msg'] = '未启用推广海报,或未设置推广海报背景';
				$result['result'] = false;
			}else{
				if($tid){
					$teacher = pdo_fetch("SELECT tname,thumb FROM " . GetTableName('teachers') . " where id = '{$tid}' ");
					$configs['name'] = $teacher['tname'];
					$configs['thumb'] = $teacher['thumb']?tomedia($teacher['thumb']):tomedia($school['tpic']);
				}else{
					$fans_info = $account_api->fansQueryInfo($openid);
					$configs['name'] = filterEmoji($fans_info['nickname']);
					//$configs['name1'] = filterEmoji($fans_info['nickname']);
					$configs['thumb'] = $fans_info['headimgurl'];
				}
				//  var_dump($configs);
				//  return;
				$configs['logo'] = tomedia($school['logo']);
				$popurl = CreatPop($qrcode,$tgset['pop_id'],tomedia($tgset['pop_img']),$configs);
				$data =  array(
					'weid' 		 => $kcinfo['weid'],
					'kcid' 		 => $kcid,
					'schoolid' 	 => $kcinfo['schoolid'],
					'userid' 	 => $userid,
					'openid' 	 => $openid,
					'tid' 		 => $tid,
					'pop_url' 	 => $popurl,
					'createtime' => time(),
					'type' 		 => 2
				);
				if($tid){
					unset($data['userid']);
				}
				if($type == 'refrash' && !empty($pop)){
					if($pop['pop_url']){//删除旧海报
						load()->func('file');
						file_delete($pop['pop_url']);
					}
					pdo_update(GetTableName('promote_pop',false),$data,array('id' =>$pop['id']));
				}else{
					pdo_insert(GetTableName('promote_pop',false),$data);
				}
				$result['popurl'] = $popurl;
				$result['result'] = true;
			}
		}else{//营销海报
			if($saleset['use_pop'] != 1 || empty($saleset['pop_img'])){
				$result['msg'] = '未启用营销海报,或未设置营销海报背景';
				$result['result'] = false;
			}else{
				$fans_info = $account_api->fansQueryInfo($openid);
				$configs['name'] = $fans_info['nickname'];
				$configs['thumb'] = $fans_info['headimgurl'];
				$configs['logo'] = tomedia($school['logo']);
				$popurl = CreatPop($qrcode,$saleset['pop_id'],tomedia($saleset['pop_img']),$configs);
				$data =  array(
					'weid' 		 => $kcinfo['weid'],
					'kcid' 		 => $kcid,
					'schoolid' 	 => $kcinfo['schoolid'],
					'teamid' 	 => $teamid,
					'userid' 	 => $userid,
					'openid' 	 => $openid,
					'pop_url' 	 => $popurl,
					'createtime' => time(),
					'type' 		 => 1
				);
				if($type == 'refrash' && !empty($pop)){
					if($pop['pop_url']){//删除旧海报
						load()->func('file');
						file_delete($pop['pop_url']);
					}
					pdo_update(GetTableName('promote_pop',false),$data,array('id' =>$pop['id']));
				}else{
					pdo_insert(GetTableName('promote_pop',false),$data);
				}
				$result['popurl'] = $popurl;
				$result['result'] = true;
			}
		}
	}else{
		$result['popurl'] = $popurl;
		$result['result'] = true;
	}
	return $result;
}


/*根据kcid userid或uid  查询粉丝归属上级老师和上级粉丝
* 返回
* tname 招生老师 ticon老师头像 
* name 归宿学生 icon归属学生头像 usertype用户类型 comfrom粉丝来源
* @author 高贵血迹
*/
function CheckFansBelong($kcid,$userid,$openid){
	$checfans = pdo_fetch("SELECT * FROM " . GetTableName('promote_fans') . " WHERE kcid = '{$kcid}' And userid = '{$userid}' ");
	if(empty($checfans)){
		$checfans = pdo_fetch("SELECT * FROM " . GetTableName('promote_fans') . " WHERE kcid = '{$kcid}' And openid = '{$openid}' ");
	}
	$school = pdo_fetch("SELECT weid,spic,tpic FROM " . GetTableName('index') . " where id = '{$checfans['schoolid']}' ");
	$belong = array();
	if(!empty($checfans)){
		if($checfans['superior_tid']){
			$teacher = pdo_fetch("SELECT tname,thumb FROM " . GetTableName('teachers') . " WHERE id = '{$checfans['superior_tid']}' ");
			$belong['tname'] = $teacher['tname'];
			$belong['tid'] = $checfans['superior_tid'];
			$belong['ticon'] = !empty($teacher['thumb'])?tomedia($teacher['thumb']):tomedia($school['tpic']);
		}
		if($checfans['superior_userid']){
			$user = pdo_fetch("SELECT sid,openid FROM " . GetTableName('user') . " WHERE id = '{$checfans['superior_userid']}' ");
			$userfans =  GetWeFans($school['weid'],$user['openid']);
			$student = pdo_fetch("SELECT icon,s_name FROM " . GetTableName('students') . " WHERE id = '{$user['sid']}' ");
			$belong['name'] = $student['s_name'];
			$belong['icon'] = !empty($student['icon'])?tomedia($student['icon']):tomedia($userfans['avatar']);
			$belong['usertype'] = '注册用户';
		}else{
			//$account_api = WeAccount::create($weid);
			$dz_user = GetWeFans($school['weid'],$checfans['superior_uid']);//superior_uid 其实是openid 字段名微改而已
			$belong['name'] = $dz_user['nickname'];
			$belong['icon']	= $dz_user['avatar'];
			$belong['usertype'] = '普通粉丝';
		}
		$belong['comfrom'] = GetComFrom($checfans['com_form']);
	}
	return $belong;
}

/*根据kcid 课表安排 必要条件起始时间戳
* @author 高贵血迹
*/
function GetKcInfo($weid, $schoolid, $condition, $kcid = 0,$nowweekstart,$nowweekend,$kcarr = false){
	if ($kcid != 0) {
		$kcinfo = pdo_fetch("SELECT name,txtime FROM " . GetTableName('tcourse') . " where id = :id ", array(':id' => $kcid));
	}
	$list = pdo_fetchall("SELECT * FROM " . GetTableName('kcbiao') . " WHERE schoolid = '{$schoolid}' And (date >= {$nowweekstart} And date <= {$nowweekend}) $condition ORDER BY date ASC ");
	foreach($list as $key => $row){
		$teacher = pdo_fetch("SELECT * FROM " . GetTableName('teachers') . " where id = :id ", array(':id' => $row['tid']));
		if ($kcid != 0) {
			$list[$key]['kcname'] = $teacher['tname'];
		}else{
			$kcinfo = pdo_fetch("SELECT name,kc_type,txtime FROM " . GetTableName('tcourse') . " where id = :id ", array(':id' => $row['kcid']));
			$list[$key]['kcname'] = $kcinfo['name'];
			if($kcinfo['kc_type'] == 1){//如果查出为线上课程则跳出继续下一个查询
				unset($list[$key]);
				continue;
			}
			if(!empty($kcarr)){
				if(!in_array($row['kcid'],$kcarr)){
					unset($list[$key]);
					continue;
				}
			}
		}
		$bmstu =0;
		$allbuy = pdo_fetchall("SELECT sid FROM " . GetTableName('coursebuy') . " WHERE kcid = '{$row['kcid']}' And is_change != 1  "); 
		foreach($allbuy as $v){
			$stu = pdo_fetch("SELECT id FROM " . GetTableName('students') . " where id = :id ", array(':id' => $v['sid']));
			if(!empty($stu)){
				$bmstu++;
			}
		}
		$signstu = pdo_fetchcolumn("SELECT COUNT(distinct sid ) FROM " . GetTableName('kcsign') . " WHERE schoolid = '{$schoolid}'  And status = 2 And kcid = '{$row['kcid']}' And ksid = '{$row['id']}' And sid != 0   " );
		$leavetu = pdo_fetchcolumn("SELECT COUNT(distinct sid ) FROM " . GetTableName('kcsign') . " WHERE schoolid = '{$schoolid}'  And status = 3 And kcid = '{$row['kcid']}' And ksid = '{$row['id']}' And sid != 0   " );
		$signtid = pdo_fetchall("SELECT tid FROM " . GetTableName('kcsign') . " WHERE schoolid = '{$schoolid}'  And status = 2 And kcid = '{$row['kcid']}' And ksid = '{$row['id']}' And sid = 0  And tid != 0  " );
		$teaSigntname = "";
		foreach($signtid as $v){
			$teaSign = pdo_fetch("SELECT tname FROM " . GetTableName('teachers') . " where id = :id ", array(':id' => $v['tid']));
			$teaSigntname .= $teaSign['tname'].'/';
		}
		$sd =  pdo_fetch("SELECT sd_start,sd_end FROM " . GetTableName('classify') . " where sid = :sid", array(':sid' => $row['sd_id']));
		$addr =  pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " where sid = :sid", array(':sid' => $row['addr_id']));
		$list[$key]['tname']  = $teacher['tname'];
		$checknubs = GetOneKcKsOrder($row['kcid'],$row['id']);
		$list[$key]['ksname'] ="第".$checknubs['nuber']."课";
		$re_typearry =array("手动排课","每周固定","隔周固定","日历排课");
		if($row['pkuser'] > 0){
			$pkteahcer = pdo_fetch("select tname FROM ".GetTableName('teachers')." WHERE id = '{$row['pkuser']}' ");
			$list[$key]['pkuser'] = $pkteahcer['tname'];
		}else{
			$list[$key]['pkuser'] = '管理员';
		}
		$list[$key]['type'] = $re_typearry[$row['re_type']];
		$list[$key]['kcnames'] = $kcinfo['name'];
		$list[$key]['adrr']   = $addr['sname'];
		if($bmstu > 0){
			$list[$key]['signbili']  = intval(100*($signstu/$bmstu));
		}
		$list[$key]['istoday']   = false;
		if (date('Y-m-d',time()) == date('Y-m-d',$row['date'])) {
			$list[$key]['istoday']   = true;
		}
		$list[$key]['sk_start'] = strtotime(date("Y-m-d",$row['date']).date(" H:i",$sd['sd_start'])) - $kcinfo['txtime']*60;//本节真实开始和结束时间 计算了提前量
		$list[$key]['timeend'] = strtotime( date('Y-m-d', $row['date']).date(' H:i:s', $sd['sd_end']));
		$list[$key]['bmstu']  = $bmstu;
		$list[$key]['unsign']  = $bmstu - $signstu ;
		$list[$key]['signstu']  = $signstu?$signstu:0;
		$list[$key]['leavetu']  = $leavetu?$leavetu:0;
		$list[$key]['teaSign']  = rtrim($teaSigntname,'/');
		$list[$key]['signtid']  = $signtid?$signtid:0;
		$list[$key]['index']   = $key+1;
		$list[$key]['week'] = date("w",$row['date']);
		$list[$key]['sd_start'] = date('H:i', $sd['sd_start']);
		$list[$key]['sd_end'] = date('H:i', $sd['sd_end']);
		$list[$key]['date'] = date('Y-m-d', $row['date']);
	}
	return $list;
}
/**
 * 根据 sid 与 nowtime 获取该学生在当前时间上的课程与课时。
 * @author Hannibal·Lee
 * @param $schoolid
 * @param $weid
 * @param $sid
 * @param $nowtime
 * @param $is_xk 是否直接消课 boolean true 是 / false 不是
 * @return $result  boolean or array
 */
function GetnearksBySid($schoolid,$weid,$sid,$nowtime,$is_xk){
    $IsHasKC = false;
    $courselist = array();

    $studentsKC = pdo_fetchall("SELECT distinct(kcid) FROM " . GetTableName('order') . " WHERE sid = '{$sid}' And schoolid = '{$schoolid}' And weid = '{$weid}' And type=1 And status = 2  ");

    $kclist_str_temp = '';
    if(!empty($studentsKC)){
        foreach($studentsKC as $key=>$value){
            $kclist_str_temp .= $value['kcid'].',';
        }
        $KclistStr = trim($kclist_str_temp,',');
        $TodayStartTime = strtotime(date("Y-m-d"),$nowtime);
        $TodayEndTime = $TodayStartTime + 86399;
        $allks = pdo_fetchall("SELECT * FROM " . GetTableName('kcbiao') . " WHERE schoolid='{$schoolid}' And weid = '{$weid}' And date >= '{$TodayStartTime}' And date <= '{$TodayEndTime}' And FIND_IN_SET(kcid,'{$KclistStr}') ORDER BY date ASC");

        if($allks){
            foreach($allks as $row){
                /**取即将开始和已经开始的课程**/
                $plustime = 0;
                $sdinfo  = pdo_fetch("SELECT sd_start,sd_end FROM " . GetTableName('classify') . " WHERE  sid = '{$row['sd_id']}'");
                $checkkc = pdo_fetch("SELECT * FROM " . GetTableName('tcourse') . " WHERE id = :id ", array(':id' => $row['kcid']));
                if($checkkc['isSign'] == 1){ //提前签到时间
                    $plustime = $checkkc['signTime']*60;
                }else{
                    $plustime = 20*60;
                }
                $check_start = strtotime(date("Y-m-d",$nowtime).date(" H:i",$sdinfo['sd_start'])) - $plustime; //当前课时开始时间向前延伸到设置的签到的时间
                $check_end   = strtotime(date("Y-m-d",$nowtime).date(" H:i",$sdinfo['sd_end']));

                if($nowtime >= $check_start && $nowtime <= $check_end){
                    $courselist[] = array(
                        'kcname' => $checkkc['name'],
                        'kcid' => $checkkc['id'],
                        'is_print_xk'=>$checkkc['is_print_xk'],
                        'OldOrNew' => 0,
                        'ksinfo' => $row
                    );

                }

            }
        }
        $FreeKClist = pdo_fetchall("SELECT name,id,OldOrNew,is_print_xk FROM " . GetTableName('tcourse') . " WHERE schoolid='{$schoolid}' And weid = '{$weid}' And start <= '{$nowtime}' And (end + 86399) >= '{$nowtime}' And OldOrNew=1 And FIND_IN_SET(id,'{$KclistStr}') ORDER BY id ASC");
        if(!empty($FreeKClist)){
            $courselist = array_merge($courselist,$FreeKClist);
        }
        if(!empty($courselist)){
            $result['courselist'] = $courselist;
            if($is_xk == true){ //如果直接消课
                foreach($courselist as $key_c => $value_c){
                    //已签到课时
                    $checkAll = pdo_fetchcolumn("select count(*) FROM " . GetTableName('kcsign') . " WHERE weid='{$weid}' And schoolid='{$schoolid}' And  kcid = '{$value_c['kcid']}' And sid='{$sid}' And status = 2 ");
                    //已购买课时
                    $buy = pdo_fetch("select ksnum FROM " . GetTableName('coursebuy') . " WHERE weid='{$weid}' And schoolid='{$schoolid}' And  kcid = '{$value_c['kcid']}' And sid='{$sid}'");
                    //return ($checkAll - $buy['ksnum']);
                    if($value_c['OldOrNew'] == 0){ //固定
                        if($buy['ksnum'] - $checkAll >= intval($value_c['ksinfo']['costnum'])){ //课时是否用完
                            $Sign_re = StudentSignKc($schoolid,$weid,$sid,$nowtime,$value_c['kcid'],$value_c['ksinfo']['id']);
                            if($Sign_re['status'] == true){
                                $result['signKC'] .= "{$value_c['kcname']} | ";
                                $result['sendMsgArr'][] = $Sign_re['signid'];
                            }else{
                                $result['OverKC'] .= "{$value_c['kcname']} - {$Sign_re['msg']} | ";
                            }

                        }else{
                            $result['OverKC'] .= "{$value_c['kcname']} - 课时已用完 | ";
                        }
                    }else{ //自由
                        if($buy['ksnum'] - $checkAll >= 1){ //课时是否用完
                            $Sign_re = StudentSignKc($schoolid,$weid,$sid,$nowtime,$value_c['kcid'],0);
                            if($Sign_re['status'] == true){
                                $result['signKC'] .= "{$value_c['kcname']} | ";
                                $result['sendMsgArr'][] = $Sign_re['signid'];
                            }else{
                                $result['OverKC'] .= "{$value_c['kcname']} - {$Sign_re['msg']} | ";
                            }
                        }else{
                            $result['OverKC'] .= "{$value_c['kcname']} - 课时已用完 | ";
                        }
                    }
                }
            }
        }
    }
    return $result;
}


/**
 * Undocumented function
 * @author Hannibal·Lee <No@email.com>
 * @param [int] $schoolid
 * @param [int] $weid
 * @param [int] $sid
 * @param [int] $signtime
 * @param [int] $kcid
 * @param [int] $ksid
 *
 * @return void
 */
function StudentSignKc($schoolid,$weid,$sid,$signtime,$kcid,$ksid){
    $KcInfo = pdo_fetch("select * FROM " . GetTableName('tcourse') . " WHERE weid='{$weid}' And schoolid='{$schoolid}' And  id = '{$kcid}' ");

    $checkAll = pdo_fetchcolumn("select count(*) FROM " . GetTableName('kcsign') . " WHERE weid='{$weid}' And schoolid='{$schoolid}' And  kcid = '{$kcid}' And sid='{$sid}' And status = 2 ");
    //已购买课时
    $buy = pdo_fetch("select ksnum FROM " . GetTableName('coursebuy') . " WHERE weid='{$weid}' And schoolid='{$schoolid}' And  kcid = '{$kcid}' And sid='{$sid}'");


    if(!empty($KcInfo)){
        $data = array(
            'kcid'       => $kcid,
            'schoolid'   => $schoolid,
            'weid'       => $weid,
            'sid'        => $sid,
            'createtime' => $signtime,
            'status'     => 2,
            'type'       => $KcInfo['OldOrNew'],
            'kcname'     => $KcInfo['name'],
			'signtime'   => $signtime,
			'signtype'   => 3
        );
        $CostNum = 1;
        if($KcInfo['OldOrNew'] == 0){
            $data['ksid'] = $ksid;
            $checkKS = pdo_fetch("select * FROM " . GetTableName('kcbiao') . " WHERE weid='{$weid}' And schoolid='{$schoolid}' And  id = '{$ksid}' ");

            if(!empty($checkKS)){
                $CostNum = $checkKS['costnum'];
                $checkSign = pdo_fetch("select * FROM " . GetTableName('kcsign') . " WHERE weid='{$weid}' And schoolid='{$schoolid}' And  ksid = '{$ksid}' And kcid = '{$kcid}' And sid = '{$sid}' And status = 2 ");
                if(!empty($checkSign)){
                    $result['status'] = false;
                    $result['msg'] = "当前课时已签到，请勿重复签到";
                    return $result;
                }
            }else{
                $result['status'] = false;
                $result['msg'] = "当前课时不存在";
                return $result;
            }
        }
        if($buy['ksnum'] - $checkAll >= $CostNum){
			$data['costnum'] = $CostNum;
            pdo_insert(GetTableName('kcsign',false), $data);
            $signid = pdo_insertid();
            if($KcInfo['is_print_xk'] == 1){
                mload()->model('print');
                KsCheck_print($signid,$schoolid,$weid);
            }

            $result['status'] = true;
            $result['msg'] = "签到成功";
            $result['signid'] = $signid;
            return $result;
        }else{
            $result['status'] = false;
            $result['msg'] = "当前课程剩余课时不足";
            return $result;
        }
    }else{
        $result['status'] = false;
        $result['msg'] = "当前课程不存在";
        return $result;
    }
}

//取正1间教室，正在上课或在打卡时间内即将开始打卡上课的课时
function Getnearks($roomid,$starttime,$endtime){
	$allks = pdo_fetchall("SELECT * FROM " . GetTableName('kcbiao') . " WHERE  addr_id = '{$roomid}'  And date > '{$starttime}' And date < '{$endtime}' ORDER BY date ASC");
	if($allks){
		$nowtime = time();
		$nowkc = '';
		$nowks = '';
		foreach($allks as $row){
			/**取即将开始和已经开始的课程**/
			$plustime = 0;
			$sdinfo  = pdo_fetch("SELECT sd_start,sd_end FROM " . GetTableName('classify') . " WHERE  sid = '{$row['sd_id']}'");
			$checkkc = pdo_fetch("SELECT * FROM " . GetTableName('tcourse') . " WHERE id = :id ", array(':id' => $row['kcid']));
			if($checkkc['isSign'] == 1){
				$plustime = $checkkc['signTime']*60;
			}else{
				$plustime = 20*60;
			}
			$check_start = strtotime(date("Y-m-d",$nowtime).date(" H:i",$sdinfo['sd_start'])) - $plustime; //当前课时开始时间向前延伸到设置的签到的时间
			$check_end   = strtotime(date("Y-m-d",$nowtime).date(" H:i",$sdinfo['sd_end']));
			if($nowtime >= $check_start && $nowtime <= $check_end){
				$nowkc[] = $checkkc;
				$nowks[] = $row;
			}
		}
		if($nowkc && $nowks){
			$reslut['nowkc'] = $nowkc;
			$reslut['nowks'] = $nowks;
		}else{
			$reslut = false;
		}
	}else{
		$reslut = false;
	}
	return $reslut ;
}

//万能考勤获取打卡时间内即将开始打卡上课的课时
function Getwnnearks($roomid,$starttime,$endtime){
	$nowtime = time();
	
	$allks = pdo_fetchall("SELECT * FROM " . tablename('wx_school_kcbiao') . " WHERE  addr_id = '{$roomid}'  AND date > '{$starttime}' AND date < '{$endtime}'   ORDER BY date ASC");
	if($allks){
		
		$nowkc = '';
		$nowks = '';
		foreach($allks as $row){
			/**取即将开始和已经开始的课程**/
			$checkkc = pdo_fetch("SELECT * FROM " . tablename('wx_school_tcourse') . " WHERE id = :id ", array(':id' => $row['kcid']));
			
			$sdinfo  = pdo_fetch("SELECT sd_start,sd_end FROM " . tablename('wx_school_classify') . " WHERE  sid = '{$row['sd_id']}'");
			
			if($checkkc['isSign'] == 1){
				$plustime = $checkkc['signTime']*60;
			}
			
			$check_start = strtotime(date("Y-m-d",$nowtime).date(" H:i:s",$sdinfo['sd_start']))-$plustime; //当前课时开始时间向前延伸到设置的签到的时间
			$check_end   = strtotime(date("Y-m-d",$nowtime).date(" H:i:s",$sdinfo['sd_end']));
			//echo $check_end.'//'.$nowtime.']';//print_r($check_start);
			if($nowtime >= $check_start && $nowtime <= $check_end){
				$nowkc[] = $checkkc;
				$nowks[] = $row;
			}
		}
		if($nowkc && $nowks){
			$reslut['nowkc'] = $nowkc;
			$reslut['nowks'] = $nowks;
		}else{
			$reslut = false;
		}
	}else{
		$reslut = false;
	}
	return $reslut;
}

//根据总分和总打分数量 
// $zf 总分  $nub 总打分次数条数
//返回评星h5样式
//$xingh5  平均分返回样式
function getStarByNub($zf,$nub){
	$xingh5 = '';
	$fillh5 = '<img class="star" src="'.OSSURL.'/public/mobile/img/star_comment_fill.png">';
	$halfth5 = '<img class="star" src="'.OSSURL.'/public/mobile/img/star_comment_halft.png">';
	$emptyh5 = '<img class="star" src="'.OSSURL.'/public/mobile/img/star_comment_empty.png">';
	if($zf == 0){
		$xingh5 .= str_repeat($emptyh5,5);
	}else{
		$pingjun = $zf/$nub;//计算平均分
		if((floor($pingjun) - $pingjun ) == 0){
			$copytime = 5 - $pingjun;
			if($copytime > 0){
				$xingh5 .= str_repeat($fillh5,$pingjun);
				$xingh5 .= str_repeat($emptyh5,$copytime);
			}else{
				$xingh5 .= str_repeat($fillh5,5);
			}
		}else{
			$copytime = 5 - intval($pingjun);
			if($copytime == 1){//4星半
				$xingh5 .= str_repeat($fillh5,$pingjun);
				$xingh5 .= $halfth5;
			}else{
				$xingh5 .= str_repeat($fillh5,$pingjun);
				$xingh5 .= $halfth5;
				$xingh5 .= str_repeat($emptyh5,$copytime-1);
			}
		}
	}
	return $xingh5;
}

//根据分数返回星星样式
//返回评星h5样式
//$stu 来自学生端
function getStarStyle($star,$stu =false){
	$xingh5 = '';
	if($stu){
		$fillh5 = '<img class="onestar" src="'.OSSURL.'/public/mobile/img/star_comment_fill.png"/>';
		$halfth5 = '<img class="onestar" src="'.OSSURL.'/public/mobile/img/star_comment_halft1.png"/>';
		$emptyh5 = '<img class="onestar" src="'.OSSURL.'/public/mobile/img/star_comment_empty1.png"/>';
	}else{
		$fillh5 = '<img class="star" src="'.OSSURL.'/public/mobile/img/star_comment_fill.png">';
		$halfth5 = '<img class="star" src="'.OSSURL.'/public/mobile/img/star_comment_halft.png">';
		$emptyh5 = '<img class="star" src="'.OSSURL.'/public/mobile/img/star_comment_empty.png">';
	}
	if($star == 0){
		$xingh5 .= str_repeat($emptyh5,5);
	}else{
		// $copytime = 5 - intval($star);
		// $xingh5 .= str_repeat($fillh5,$star);
		// $xingh5 .= str_repeat($emptyh5,$copytime);
		//戳处理有半星的情况
		if((floor($star) - $star ) == 0){
			$copytime = 5 - $star;
			if($copytime > 0){
				$xingh5 .= str_repeat($fillh5,$star);
				$xingh5 .= str_repeat($emptyh5,$copytime);
			}else{
				$xingh5 .= str_repeat($fillh5,5);
			}
		}else{
			$copytime = 5 - intval($star);
			if($copytime == 1){//4星半
				$xingh5 .= str_repeat($fillh5,$star);
				$xingh5 .= $halfth5;
			}else{
				$xingh5 .= str_repeat($fillh5,$star);
				$xingh5 .= $halfth5;
				$xingh5 .= str_repeat($emptyh5,$copytime-1);
			}
		}
	}
	return $xingh5;
}

//根据教室取指定时间段的课时表
function getksbiao($schoolid,$classid,$starttime,$endtime){
	$allks = pdo_fetchall("SELECT sd_id,kcid,tid FROM " . GetTableName('kcbiao') . " WHERE  addr_id = '{$classid}'  And date > '{$starttime}' And date < '{$endtime}' ORDER BY date ASC");
	$week = date("w",time());
	$section = 0;
	foreach($allks as $key => $row){
		$section ++;
		$sd  = pdo_fetch("SELECT sd_start,sd_end FROM " . GetTableName('classify') . " WHERE  sid = '{$row['sd_id']}'");
		$kc = pdo_fetch("SELECT name FROM " . GetTableName('tcourse') . " WHERE id = :id ", array(':id' => $row['kcid']));
		$teacher = pdo_fetch("SELECT tname,thumb FROM " . GetTableName('teachers') . " WHERE id = '{$row['tid']}'");
		$school = pdo_fetch("SELECT tpic FROM " . GetTableName('index') . " WHERE id = '{$schoolid}'");
		$allks[$key]['week'] = $week;
		$allks[$key]['section'] = $section;
		$allks[$key]['course_name'] = $kc['name'];
		$allks[$key]['start_time'] = date(" H:i",$sd['sd_start']);
		$allks[$key]['end_time'] = date(" H:i",$sd['sd_end']);
		$allks[$key]['teacher_name'] = $teacher['tname'];
		$allks[$key]['teacher_img'] = !empty($teacher['thumb'])?tomedia($school['tpic']):tomedia($teacher['thumb']);
		unset($allks[$key]['sd_id']);
		unset($allks[$key]['kcid']);
		unset($allks[$key]['tid']);
	}
	return $allks ;
}

function GetStuInfoByKs($schoolid,$ksid){
	$ksinfo  = pdo_fetch("SELECT * FROM " . GetTableName('kcbiao') . " WHERE id = '{$ksid}' And schoolid = '{$schoolid}' ");
	$signStu = pdo_fetchall("SELECT distinct sid FROM " . GetTableName('kcsign') . " WHERE ksid = '{$ksid}' And schoolid = '{$schoolid}' And tid = 0 And sid != 0 And status = 2");
	$timeinfo = pdo_fetch("SELECT sd_start,sd_end FROM " . GetTableName('classify') . " WHERE sid = '{$ksinfo['sd_id']}' And schoolid = '{$schoolid}' And type = 'timeframe' ");
	$starttime_str = date("Y-m-d",$ksinfo['date'])." ".date("H:i:s",$timeinfo['sd_start']);
	$endtime_str = date("Y-m-d",$ksinfo['date'])." ".date("H:i:s",$timeinfo['sd_end']);
	$starttime = strtotime($starttime_str);
	$endtime = strtotime($endtime_str);
	$LeaveStu = pdo_fetchall("SELECT distinct leaves.sid FROM " . GetTableName('leave') . " as leaves , " . GetTableName('order') . " as orderTab  WHERE orderTab.kcid = '{$ksinfo['kcid']}' And orderTab.schoolid = '{$schoolid}' And orderTab.type = 1 And orderTab.status = 2  And orderTab.sid != 0 And orderTab.sid = leaves.sid And leaves.startime1 <= '{$starttime}' And leaves.endtime1 >= '{$endtime}' ");
	$AllStu = pdo_fetchall("SELECT distinct sid FROM ". GetTableName('order') . "  WHERE kcid = '{$ksinfo['kcid']}' And schoolid = '{$schoolid}' And type = 1 And status = 2  And sid != 0 ");
	$result['signstu'] = count($signStu);
	$result['leavestu'] = count($LeaveStu);
	$result['allstu'] = count($AllStu);
	return $result;
}

//根据ksid查询 本节设定的开始和结束时间
// 返回 qdstart可签到开始时间 start 正课开始 end 正课结束 签到结束
function GetTimeSetByKs($ksid){
	$ksinfo  = pdo_fetch("SELECT kcid,sd_id,schoolid FROM " . GetTableName('kcbiao') . " WHERE id = '{$ksid}' ");
	$timeinfo = pdo_fetch("SELECT sd_start,sd_end FROM " . GetTableName('classify') . " WHERE sid = '{$ksinfo['sd_id']}' And schoolid = '{$ksinfo['schoolid']}' And type = 'timeframe' ");
	$kcinfo = pdo_fetch("SELECT is_tx,txtime FROM " .GetTableName ('tcourse'). " where id = :id ", array(':id' => $row['kcid']));
	$starttime_str = date("Y-m-d",$ksinfo['date'])." ".date("H:i:s",$timeinfo['sd_start']);
	$endtime_str = date("Y-m-d",$ksinfo['date'])." ".date("H:i:s",$timeinfo['sd_end']);
	$starttime = strtotime($starttime_str);
	$endtime = strtotime($endtime_str);
	if($kcinfo['is_tx'] == 1 && $kcinfo['txtime'] > 1){//如设置了提前签到 签到开始时间重新计算
		$qdstart = strtotime(date("Y-m-d",$ksinfo['date'])." ".date("H:i:s",$timeinfo['sd_start'])) - $kcinfo['txtime']*60;
		$result['qdstart'] = $qdstart;
	}else{
		$result['qdstart'] = $starttime;
	}
	$result['start'] = $starttime;
	$result['end'] = $endtime;
	return $result;
}

//根据课时$ksid和输入$time查询本次签到的状态 .
//返回 1正常签到 2提前签到 3补签
function GetSignTimeTypeByTime($time,$ksid){
	$timeset = GetTimeSetByKs($ksid);
	if($timeset['qdstart'] <= $time && $time <= $timeset['end']){
		return 1;
	}
	if($timeset['qdstart'] > $time && $time < $timeset['end']){
		return 2;
	}
	if($timeset['qdstart'] < $time && $time > $timeset['end']){
		return 3;
	}
}

//根据课时$signid 返回老师应该提现金额
//返回 type主讲 fee提成
function GetFeeBySignId($signid){
	$signinfo = pdo_fetch("SELECT kcid,ksid,costnum,ismaster_tid FROM " . GetTableName('kcsign') . " WHERE id = :id ", array(':id' => $signid));
	$kctxrule = pdo_fetch("SELECT fee1,fee2,type FROM " . GetTableName('kc_getcashrule') . " WHERE kcid = :kcid ", array(':kcid' => $signinfo['kcid']));
	if($signinfo['ismaster_tid'] == 1 || $signinfo['ismaster_tid'] == 0){
		$result['type'] = 1;
		$result['fee'] = $kctxrule['fee1'];
	}
	if($signinfo['ismaster_tid'] == 2){
		$result['type'] = 2;
		$result['fee'] = $kctxrule['fee2'];
	}
	return $result;
}

//查询老师可提成金额
//仅传tid 返回全部金额
//传tid $signarray 返回signarray包含的签到总金额 
function GetMyFeeByTid($schoolid,$tid,$signarray = false){
	$toalfee = 0;
	if($signarray == false){
		$kclist = pdo_fetchall("SELECT id FROM " . GetTableName('tcourse') . " WHERE schoolid = '{$schoolid}' And kc_type != 1 And tea_getcash = 1 And (tid like '{$tid},%' OR tid like '%,{$tid}' OR tid like '%,{$tid},%' OR tid='{$tid}')  ORDER BY end DESC");
		foreach($kclist as $key => $row){
			$kctxrule = pdo_fetch("SELECT type FROM " . GetTableName('kc_getcashrule') . " WHERE kcid = :kcid ", array(':kcid' => $row['id']));
			$mysigninfo = pdo_fetchall("SELECT id,ksid FROM " . GetTableName('kcsign') . " WHERE tid = '{$tid}' And sid = 0 And kcid = '{$row['id']}' And status = 2 ");
			if($kctxrule['type'] == 1){//查老师本身的签到本课程记录
				foreach($mysigninfo as $k){
					$checkcash = pdo_fetch("SELECT id FROM " . GetTableName('getcash_order') . " WHERE tid = '{$tid}' And signid = '{$k['id']}' And kcid = '{$row['id']}' ");
					if(empty($checkcash)){
						$getfee = GetFeeBySignId($k['id']);
						$toalfee = $toalfee + $getfee['fee'];
					}
				}
			}
			if($kctxrule['type'] == 2){//查老师已签到的所有学生签到记录
				foreach($mysigninfo as $r){
					$allsign = pdo_fetchall("SELECT id,costnum FROM " . GetTableName('kcsign') . " WHERE tid = 0 And sid > 0 And ksid = '{$r['ksid']}' And kcid = '{$row['id']}' And status = 2 ");
					foreach($allsign as $i){
						$checkcash = pdo_fetch("SELECT id FROM " . GetTableName('getcash_order') . " WHERE tid = '{$tid}' And signid = '{$i['id']}' And kcid = '{$row['id']}' ");
						if(empty($checkcash)){
							$getfee = GetFeeBySignId($r['id']);
							$toalfee = $toalfee + ($getfee['fee']*$i['costnum']);
						}
					}
				}
			}
		}
	}else{
		foreach($signarray as $key => $row){
			$signinfo = pdo_fetch("SELECT id,ksid,kcid,costnum FROM " . GetTableName('kcsign') . " WHERE id = '{$row}' ");
			$kctxrule = pdo_fetch("SELECT type FROM " . GetTableName('kc_getcashrule') . " WHERE kcid = :kcid ", array(':kcid' => $signinfo['kcid']));
			if($kctxrule['type'] == 1){
				$getfee = GetFeeBySignId($row);
				$toalfee = $toalfee + $getfee['fee'];
			}
			if($kctxrule['type'] == 2){
				$teasign = pdo_fetch("SELECT id FROM " . GetTableName('kcsign') . " WHERE tid = '{$tid}' And sid = 0 And ksid = '{$signinfo['ksid']}' And kcid = '{$signinfo['kcid']}' And status = 2 ");
				if(!empty($teasign)){
					$getfee = GetFeeBySignId($teasign['id']);
					$toalfee = $toalfee + ($getfee['fee']*$signinfo['costnum']);
				}
			}
		}
	}
	return $toalfee;
}

function CheckIsShowKm($tid,$bj_id,$km_id,$schoolid){
    if(!empty($km_id)){
        $checkIsSk = pdo_fetch("SELECT * FROM " . GetTableName('user_class') . " where schoolid = '{$schoolid}' And tid = '{$tid}' And bj_id = '{$bj_id}' And km_id = '{$km_id}' ");
        if(!empty($checkIsSk)){
            return true;
        }
    }
    return false;
};

function GetTinfoBykb($bj_id,$km_id,$schoolid){
    $teainfo = pdo_fetch("SELECT * FROM " . GetTableName('user_class') . " where schoolid = '{$schoolid}'  And bj_id = '{$bj_id}' And km_id = '{$km_id}' ");
    $teacher = pdo_fetch("SELECT id,tname FROM " . GetTableName('teachers') . " where schoolid = '{$schoolid}'  And id = '{$teainfo['tid']}' ");

        if(!empty($teacher)){
            return $teacher['tname'];
        }

};

//判断学生当前课程欠费、结业、退费情况
function GetStuKcStatus($schoolid,$weid,$sid,$kcid){
    $nowtime =strtotime(date("Y-m-d",time()));
    $kcinfo = pdo_fetch("SELECT * FROM " . GetTableName('tcourse') . " WHERE id = '{$kcid}' And weid='{$weid}' And schoolid = '{$schoolid}' ");
    $stuBuyInfo = pdo_fetch("SELECT * FROM " . GetTableName('coursebuy') . " WHERE kcid = '{$kcid}'  And sid = '{$sid}'  And weid='{$weid}' And schoolid = '{$schoolid}' ");
    $stuSignCount = pdo_fetchcolumn("SELECT count(costnum) FROM " . GetTableName('kcsign') . " WHERE kcid = '{$kcid}'  And sid = '{$sid}' And weid='{$weid}' And schoolid = '{$schoolid}' ");
    $stukcorderPayorRet = pdo_fetchcolumn("SELECT count(id) FROM " . GetTableName('order') . " WHERE kcid = '{$kcid}'  And sid = '{$sid}'  And weid='{$weid}' And schoolid = '{$schoolid}' And type = 1 And (status = 3 or status =2) ");
    $stukcorderRet = pdo_fetchcolumn("SELECT count(id) FROM " . GetTableName('order') . " WHERE kcid = '{$kcid}'  And sid = '{$sid}'  And weid='{$weid}' And schoolid = '{$schoolid}' And type = 1 And status = 3  ");
    if($stuSignCount == $kcinfo['AllNum'] && $kcinfo['end'] > $nowtime){
        return 1;//结业
    }elseif($stuSignCount >= $stuBuyInfo && $stuSignCount <$kcinfo['AllNum'] ){
        return 2;//欠费
    }
    if( $stukcorderPayorRet == $stukcorderRet  && $stukcorderPayorRet != 0){
        //return ''.$stukcorderPayorRet.' | '.$stukcorderRet; //退费
        return 3;
    }
    return 0;
}

#链接转二维码
function PosterQrcode($data = ''){
    include_once IA_ROOT . '/framework/library/qrcode/phpqrcode.php';
    ob_start(); // 在服务器打开一个缓冲区来保存所有的输出
    $errorCorrectionLevel = 'L';	//容错级别
    $matrixPointSize = 10;			//生成图片大小
	QRcode::png($data,false , $errorCorrectionLevel, $matrixPointSize,2.2);
    $imageString = ob_get_contents();
    ob_end_clean(); //清除缓冲区的内容，并将缓冲区关闭，但不会输出内容
	$qrpic = 'data:image/jpg;base64,'.base64_encode($imageString);
    return $qrpic;
}

/*根据qrcode pop_id pop_img  合成海报
* 返回 海报路径
* @author 高贵血迹
*/
function CreatPop($qrcode,$pop_id,$pop_img,$configs = array()){
	$config = setSsPoster($qrcode,$pop_id,$pop_img,$configs);//初始化参数
	$jpgname = 'images/fm_jiaoyu/pop/'.random(30) .'.png';
	load()->func('file');
	mkdirs(IA_ROOT . '/attachment/images/fm_jiaoyu', "0777");
	mkdirs(IA_ROOT . '/attachment/images/fm_jiaoyu/pop', "0777");
	$filename = IA_ROOT.'/attachment/'.$jpgname;
	$pop = createSsPoster($config,$filename);//合成
	return $jpgname;
}

/**
 * @param $header
 * @param $poster 设置背景图片
 * @param $text   设置文字
 */

function setSsPoster($qrcode,$pop_id,$pop_img,$config = array()){
    $font = IA_ROOT . "/addons/fm_jiaoyu/public/web/fonts/simhei.ttf";
    if($pop_id == 1){ //全图风格
		$data = array(
			'image'=>array(
				array(
					'url'=>"{$qrcode}", //二维码
					'left'=>467,
					'top'=>975,
					'right'=>0,
					'stream'=>0,
					'bottom'=>0,
					'width'=>144,
					'height'=>144,
					'opacity'=>100,
					'border'=>0
				),
				array(
					'url'=>"{$config['logo']}", //二维码中心LOGO
					'left'=>524,
					'top'=>1030,
					'right'=>0,
					'stream'=>0,
					'bottom'=>0,
					'width'=>32,
					'height'=>32,
					'opacity'=>101,
					'border'=>0
				),
				array(
					'url'=>"{$config['thumb']}", // 用户头像
					'left'=>60,
					'top'=>45,
					'right'=>0,
					'stream'=>0,
					'bottom'=>0,
					'width'=>58,
					'height'=>58,
					'opacity'=>100,
					'border'=>0
				), 
			),
			'text'=>array(
				array( //用户名
					'text'=>"{$config['name']}",
					'left'=>175,
					'top'=>66,
					'fontPath'=>"{$font}",     //字体文件
					'fontSize'=>16,             //字号
					'fontColor'=>'23,23,23,1',       //字体颜色
					'angle'=>0,
				)
			),
			'background'=>"{$pop_img}",
		);
	}
    if($pop_id == 2){ //上下风格
		$data = array(
			'image'=>array(
				array(
					'url'=>"{$qrcode}", //二维码
					'left'=>483,
					'top'=>911,
					'right'=>0,
					'stream'=>0,
					'bottom'=>0,
					'width'=>178,
					'height'=>178,
					'opacity'=>100,
					'border'=>0
				),
				array(
					'url'=>"{$config['logo']}", //二维码中心LOGO
					'left'=>553,
					'top'=>986,
					'right'=>0,
					'stream'=>0,
					'bottom'=>0,
					'width'=>40,
					'height'=>40,
					'opacity'=>101,
					'border'=>0
				),
				array(
					'url'=>"{$config['thumb']}", // 用户头像
					'left'=>89,
					'top'=>929,
					'right'=>0,
					'stream'=>0,
					'bottom'=>0,
					'width'=>62,
					'height'=>62,
					'opacity'=>100,
					'border'=>0
				), 
			),
			'text'=>array(
				array( //用户名
					'text'=>"{$config['name']}",
					'left'=>160,
					'top'=>967,
					'fontPath'=>"{$font}",     //字体文件
					'fontSize'=>23,             //字号
					'fontColor'=>'23,23,23,1',       //字体颜色
					'angle'=>0,
				)
			),
			'background'=>"{$pop_img}",
		);
	}
    return $data;
}

function createSsPoster($config=array(),$filename=""){
	//如果要看报什么错，可以先注释调这个header
	header('Content-Type: text/html; charset=ISO-8859-1');
    if(empty($filename)) header("content-type: image/png");
    $imageDefault = array(
        'left'=>0,
        'top'=>0,
        'right'=>0,
        'bottom'=>0,
        'width'=>100,
        'height'=>100,
        'opacity'=>100
    );
    $textDefault = array(
        'text'=>'',
        'left'=>0,
        'top'=>0,
        'fontSize'=>32,       //字号
        'fontColor'=>'255,255,255', //字体颜色
        'angle'=>0,
    );
    $background = $config['background'];//海报最底层得背景
    //背景方法
    $backgroundInfo = getimagesize($background);
    $backgroundFun = 'imagecreatefrom'.image_type_to_extension($backgroundInfo[2], false);
    $background = $backgroundFun($background);
    $backgroundWidth = imagesx($background);  //背景宽度
    //$backgroundWidth = '604px';  //背景宽度
    $backgroundHeight = imagesy($background);  //背景高度
    //$backgroundHeight = '1008px';  //背景高度
    $imageRes = imageCreatetruecolor($backgroundWidth,$backgroundHeight);
	$color = imagecolorallocate($imageRes, 1,0,0); //原始颜色
    // $color = imagecolorallocatealpha($imageRes, 255, 255, 255,127);
    // $color = imagecolorallocate($imageRes, 255, 255, 255);
    imagefill($imageRes, 0, 0, $color);
	imageColorTransparent($imageRes, $color);  //颜色透明
    imagecopyresampled($imageRes,$background,0,0,0,0,imagesx($background),imagesy($background),imagesx($background),imagesy($background));
    //处理了图片
    if(!empty($config['image'])){
        foreach ($config['image'] as $key => $val) {
            $val = array_merge($imageDefault,$val);
            if($val['border'] == 1){
				$borderpic = tomedia(borderpic($val['url']));
				$info = getimagesize($borderpic);
			}else{
				$info = getimagesize($val['url']);
			}
            $function = 'imagecreatefrom'.image_type_to_extension($info[2], false);
            if($val['stream']){   //如果传的是字符串图像流
                $info = getimagesizefromstring($val['url']);
                $function = 'imagecreatefromstring';
            }
			if($val['border'] == 1){
				$res = $function($borderpic);
			}else{
				$res = $function($val['url']);
			}
            $resWidth = $info[0];
            $resHeight = $info[1];
            //建立画板 ，缩放图片至指定尺寸
			$canvas=imagecreatetruecolor($val['width'], $val['height']);
			
			imagefill($canvas, 0, 0, $color); //处理背板
			imageColorTransparent($canvas, $color);  //颜色透明

			//将源图拷贝到新图上，并设置在保存 PNG 图像时保存完整的 alpha 通道信息  
            //关键函数，参数（目标资源，源，目标资源的开始坐标x,y, 源资源的开始坐标x,y,目标资源的宽高w,h,源资源的宽高w,h）
			imagecopyresampled($canvas, $res, 0, 0, 0, 0, $val['width'], $val['height'],$resWidth,$resHeight);
            $val['left'] = $val['left']<0?$backgroundWidth- abs($val['left']) - $val['width']:$val['left'];
            $val['top'] = $val['top']<0?$backgroundHeight- abs($val['top']) - $val['height']:$val['top'];
            //放置图像
			imagecopymerge($imageRes,$canvas, $val['left'],$val['top'],$val['right'],$val['bottom'],$val['width'],$val['height'],$val['opacity']);//左，上，右，下，宽度，高度，透明度
        }
    }
    //处理文字
    if(!empty($config['text'])){
        foreach ($config['text'] as $key => $val) {
			$val = array_merge($textDefault,$val);
			//计算文字居中偏移量 start 最终以 $left 为结果
			preg_match_all("/[^\x{4e00}-\x{9fa5}]/u",$val['text'],$arrAl); //非汉字
			preg_match_all('/[\x{4e00}-\x{9fa5}]/u',$val['text'],$arrCh); //汉字
			$CountCH = count($arrCh[0]);
			$MoveCH = $val['fontSize'] * ($CountCH - 1) / 2;
			$CountRE = count($arrAl[0]);
			$MoveRE = $val['fontSize'] * ($CountRE - 1) / 3.5;
			$Left = $val['left'] ;
            list($R,$G,$B) = explode(',', $val['fontColor']);
            $fontColor = imagecolorallocate($imageRes, $R, $G, $B);
            $val['left'] = $val['left']<0?$backgroundWidth- abs($val['left']):$val['left'];
            $val['top'] = $val['top']<0?$backgroundHeight- abs($val['top']):$val['top'];
            imagettftext($imageRes,$val['fontSize'],$val['angle'],$Left,$val['top'],$fontColor,$val['fontPath'],$val['text']);
        }
    }
    //生成图片
    if(!empty($filename)){
        $res = imagejpeg ($imageRes,$filename,90); //保存到本地
        imagedestroy($imageRes);
        if(!$res) return false;
		return $filename;
    }else{
        imagejpeg ($imageRes);     //在浏览器上显示
        imagedestroy($imageRes);
    }
}

function borderpic($url){
    $background = $url;//海报最底层得背景
    //背景方法
    $backgroundInfo = getimagesize($background);
    $backgroundFun = 'imagecreatefrom'.image_type_to_extension($backgroundInfo[2], false);
    $background = $backgroundFun($background);
    $w = imagesx($background);  //背景宽度
    $h = imagesy($background);  //背景高度
	//$w = 110;  $h=110; // original size
	$original_path= $url;
	$dest_path = "images/".random(30) .".png";
	$src = imagecreatefromstring(file_get_contents($original_path));
	$newpic = imagecreatetruecolor($w,$h);
	imagealphablending($newpic,false);
	$transparent = imagecolorallocatealpha($newpic, 0, 0, 0, 127);
	$r=$w/2;
	for($x=0;$x<$w;$x++)
		for($y=0;$y<$h;$y++){
			$c = imagecolorat($src,$x,$y);
			$_x = $x - $w/2;
			$_y = $y - $h/2;
			if((($_x*$_x) + ($_y*$_y)) < ($r*$r)){
				imagesetpixel($newpic,$x,$y,$c);
			}else{
				imagesetpixel($newpic,$x,$y,$transparent);
			}
		}
	imagesavealpha($newpic, true);
	// header('Content-Type: image/png');
	imagepng($newpic, IA_ROOT.'/attachment/'.$dest_path);
	imagedestroy($newpic);
	return $dest_path;
}

function getkctimetableByBjid($schoolid,$bj_id,$starttime,$endtime){
    $condition = " And begintime < '{$starttime}' And endtime > '{$endtime}'";
    $cook = pdo_fetch("SELECT * FROM " . GetTableName('timetable') . " WHERE schoolid = :schoolid And bj_id = :bj_id And ishow = 1 $condition", array(':schoolid' => $schoolid,':bj_id' => $bj_id));
    $week = date("w",$endtime);
    $return = array();
    if($week ==1){
        if($cook['monday']){
            $thecook = iunserializer($cook['monday']);
            $return['sd']['sd_1'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['mon_1_sd']}'");
            $return['sd']['sd_2'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['mon_2_sd']}'");
            $return['sd']['sd_3'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['mon_3_sd']}'");
            $return['sd']['sd_4'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['mon_4_sd']}'");
            $return['sd']['sd_5'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['mon_5_sd']}'");
            $return['sd']['sd_6'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['mon_6_sd']}'");
            $return['sd']['sd_7'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['mon_7_sd']}'");
            $return['sd']['sd_8'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['mon_8_sd']}'");
            $return['sd']['sd_9'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['mon_9_sd']}'");
            $return['sd']['sd_10']= pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['mon_10_sd']}'");
            $return['sd']['sd_11']= pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['mon_11_sd']}'");
            $return['sd']['sd_12']= pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['mon_12_sd']}'");
            $return['km']['km_1'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['mon_1_km']}'");
            $return['km']['km_1']['tname'] = GetTinfoBykb($bj_id,$thecook['mon_1_km'],$schoolid);
            $return['km']['km_2'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['mon_2_km']}'");
            $return['km']['km_2']['tname'] = GetTinfoBykb($bj_id,$thecook['mon_2_km'],$schoolid);
            $return['km']['km_3'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['mon_3_km']}'");
            $return['km']['km_3']['tname'] = GetTinfoBykb($bj_id,$thecook['mon_3_km'],$schoolid);
            $return['km']['km_4'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['mon_4_km']}'");
            $return['km']['km_4']['tname'] = GetTinfoBykb($bj_id,$thecook['mon_4_km'],$schoolid);
            $return['km']['km_5'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['mon_5_km']}'");
            $return['km']['km_5']['tname'] = GetTinfoBykb($bj_id,$thecook['mon_5_km'],$schoolid);
            $return['km']['km_6'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['mon_6_km']}'");
            $return['km']['km_6']['tname'] = GetTinfoBykb($bj_id,$thecook['mon_6_km'],$schoolid);
            $return['km']['km_7'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['mon_7_km']}'");
            $return['km']['km_7']['tname'] = GetTinfoBykb($bj_id,$thecook['mon_7_km'],$schoolid);
            $return['km']['km_8'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['mon_8_km']}'");
            $return['km']['km_8']['tname'] = GetTinfoBykb($bj_id,$thecook['mon_8_km'],$schoolid);
            $return['km']['km_9'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['mon_9_km']}'");
            $return['km']['km_9']['tname'] = GetTinfoBykb($bj_id,$thecook['mon_9_km'],$schoolid);
            $return['km']['km_10']= pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['mon_10_km']}'");
            $return['km']['km_10']['tname'] = GetTinfoBykb($bj_id,$thecook['mon_10_km'],$schoolid);
            $return['km']['km_11']= pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['mon_11_km']}'");
            $return['km']['km_11']['tname'] = GetTinfoBykb($bj_id,$thecook['mon_11_km'],$schoolid);
            $return['km']['km_12']= pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['mon_12_km']}'");
            $return['km']['km_12']['tname'] = GetTinfoBykb($bj_id,$thecook['mon_12_km'],$schoolid);
        }
    }
    if($week ==2){
        if($cook['tuesday']){
            $thecook = iunserializer($cook['tuesday']);
            $return['sd']['sd_1'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['tus_1_sd']}'");
            $return['sd']['sd_2'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['tus_2_sd']}'");
            $return['sd']['sd_3'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['tus_3_sd']}'");
            $return['sd']['sd_4'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['tus_4_sd']}'");
            $return['sd']['sd_5'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['tus_5_sd']}'");
            $return['sd']['sd_6'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['tus_6_sd']}'");
            $return['sd']['sd_7'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['tus_7_sd']}'");
            $return['sd']['sd_8'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['tus_8_sd']}'");
            $return['sd']['sd_9'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['tus_9_sd']}'");
            $return['sd']['sd_10'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['tus_10_sd']}'");
            $return['sd']['sd_11'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['tus_11_sd']}'");
            $return['sd']['sd_12'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['tus_12_sd']}'");
            $return['km']['km_1'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['tus_1_km']}'");
            $return['km']['km_1']['tname'] = GetTinfoBykb($bj_id,$thecook['tus_1_km'],$schoolid);
            $return['km']['km_2'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['tus_2_km']}'");
            $return['km']['km_2']['tname'] = GetTinfoBykb($bj_id,$thecook['tus_2_km'],$schoolid);
            $return['km']['km_3'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['tus_3_km']}'");
            $return['km']['km_3']['tname'] = GetTinfoBykb($bj_id,$thecook['tus_3_km'],$schoolid);
            $return['km']['km_4'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['tus_4_km']}'");
            $return['km']['km_4']['tname'] = GetTinfoBykb($bj_id,$thecook['tus_4_km'],$schoolid);
            $return['km']['km_5'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['tus_5_km']}'");
            $return['km']['km_5']['tname'] = GetTinfoBykb($bj_id,$thecook['tus_5_km'],$schoolid);
            $return['km']['km_6'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['tus_6_km']}'");
            $return['km']['km_6']['tname'] = GetTinfoBykb($bj_id,$thecook['tus_6_km'],$schoolid);
            $return['km']['km_7'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['tus_7_km']}'");
            $return['km']['km_7']['tname'] = GetTinfoBykb($bj_id,$thecook['tus_7_km'],$schoolid);
            $return['km']['km_8'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['tus_8_km']}'");
            $return['km']['km_8']['tname'] = GetTinfoBykb($bj_id,$thecook['tus_8_km'],$schoolid);
            $return['km']['km_9'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['tus_9_km']}'");
            $return['km']['km_9']['tname'] = GetTinfoBykb($bj_id,$thecook['tus_9_km'],$schoolid);
            $return['km']['km_10'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['tus_10_km']}'");
            $return['km']['km_10']['tname'] = GetTinfoBykb($bj_id,$thecook['tus_10_km'],$schoolid);
            $return['km']['km_11'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['tus_11_km']}'");
            $return['km']['km_11']['tname'] = GetTinfoBykb($bj_id,$thecook['tus_11_km'],$schoolid);
            $return['km']['km_12'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['tus_12_km']}'");
            $return['km']['km_12']['tname'] = GetTinfoBykb($bj_id,$thecook['tus_12_km'],$schoolid);
        }
    }
    if($week ==3){
        if($cook['wednesday']){
            $thecook = iunserializer($cook['wednesday']);
            $return['sd']['sd_1'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['wed_1_sd']}'");
            $return['sd']['sd_2'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['wed_2_sd']}'");
            $return['sd']['sd_3'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['wed_3_sd']}'");
            $return['sd']['sd_4'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['wed_4_sd']}'");
            $return['sd']['sd_5'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['wed_5_sd']}'");
            $return['sd']['sd_6'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['wed_6_sd']}'");
            $return['sd']['sd_7'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['wed_7_sd']}'");
            $return['sd']['sd_8'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['wed_8_sd']}'");
            $return['sd']['sd_9'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['wed_9_sd']}'");
            $return['sd']['sd_10'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['wed_10_sd']}'");
            $return['sd']['sd_11'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['wed_11_sd']}'");
            $return['sd']['sd_12'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['wed_12_sd']}'");
            $return['km']['km_1'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['wed_1_km']}'");
            $return['km']['km_1']['tname'] = GetTinfoBykb($bj_id,$thecook['wed_1_km'],$schoolid);
            $return['km']['km_2'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['wed_2_km']}'");
            $return['km']['km_2']['tname'] = GetTinfoBykb($bj_id,$thecook['wed_2_km'],$schoolid);
            $return['km']['km_3'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['wed_3_km']}'");
            $return['km']['km_3']['tname'] = GetTinfoBykb($bj_id,$thecook['wed_3_km'],$schoolid);
            $return['km']['km_4'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['wed_4_km']}'");
            $return['km']['km_4']['tname'] = GetTinfoBykb($bj_id,$thecook['wed_4_km'],$schoolid);
            $return['km']['km_5'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['wed_5_km']}'");
            $return['km']['km_5']['tname'] = GetTinfoBykb($bj_id,$thecook['wed_5_km'],$schoolid);
            $return['km']['km_6'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['wed_6_km']}'");
            $return['km']['km_6']['tname'] = GetTinfoBykb($bj_id,$thecook['wed_6_km'],$schoolid);
            $return['km']['km_7'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['wed_7_km']}'");
            $return['km']['km_7']['tname'] = GetTinfoBykb($bj_id,$thecook['wed_7_km'],$schoolid);
            $return['km']['km_8'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['wed_8_km']}'");
            $return['km']['km_8']['tname'] = GetTinfoBykb($bj_id,$thecook['wed_8_km'],$schoolid);
            $return['km']['km_9'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['wed_9_km']}'");
            $return['km']['km_9']['tname'] = GetTinfoBykb($bj_id,$thecook['wed_9_km'],$schoolid);
            $return['km']['km_10'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['wed_10_km']}'");
            $return['km']['km_10']['tname'] = GetTinfoBykb($bj_id,$thecook['wed_10_km'],$schoolid);
            $return['km']['km_11'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['wed_11_km']}'");
            $return['km']['km_11']['tname'] = GetTinfoBykb($bj_id,$thecook['wed_11_km'],$schoolid);
            $return['km']['km_12'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['wed_12_km']}'");
            $return['km']['km_12']['tname'] = GetTinfoBykb($bj_id,$thecook['wed_12_km'],$schoolid);
        }
    }
    if($week ==4){
        if($cook['thursday']){
            $thecook = iunserializer($cook['thursday']);
            $return['sd']['sd_1'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['thu_1_sd']}'");
            $return['sd']['sd_2'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['thu_2_sd']}'");
            $return['sd']['sd_3'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['thu_3_sd']}'");
            $return['sd']['sd_4'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['thu_4_sd']}'");
            $return['sd']['sd_5'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['thu_5_sd']}'");
            $return['sd']['sd_6'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['thu_6_sd']}'");
            $return['sd']['sd_7'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['thu_7_sd']}'");
            $return['sd']['sd_8'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['thu_8_sd']}'");
            $return['sd']['sd_9'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['thu_9_sd']}'");
            $return['sd']['sd_10'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['thu_10_sd']}'");
            $return['sd']['sd_11'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['thu_11_sd']}'");
            $return['sd']['sd_12'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['thu_12_sd']}'");
            $return['km']['km_1'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['thu_1_km']}'");
            $return['km']['km_1']['tname'] = GetTinfoBykb($bj_id,$thecook['thu_1_km'],$schoolid);
            $return['km']['km_2'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['thu_2_km']}'");
            $return['km']['km_2']['tname'] = GetTinfoBykb($bj_id,$thecook['thu_2_km'],$schoolid);
            $return['km']['km_3'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['thu_3_km']}'");
            $return['km']['km_3']['tname'] = GetTinfoBykb($bj_id,$thecook['thu_3_km'],$schoolid);
            $return['km']['km_4'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['thu_4_km']}'");
            $return['km']['km_4']['tname'] = GetTinfoBykb($bj_id,$thecook['thu_4_km'],$schoolid);
            $return['km']['km_5'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['thu_5_km']}'");
            $return['km']['km_5']['tname'] = GetTinfoBykb($bj_id,$thecook['thu_5_km'],$schoolid);
            $return['km']['km_6'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['thu_6_km']}'");
            $return['km']['km_6']['tname'] = GetTinfoBykb($bj_id,$thecook['thu_6_km'],$schoolid);
            $return['km']['km_7'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['thu_7_km']}'");
            $return['km']['km_7']['tname'] = GetTinfoBykb($bj_id,$thecook['thu_7_km'],$schoolid);
            $return['km']['km_8'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['thu_8_km']}'");
            $return['km']['km_8']['tname'] = GetTinfoBykb($bj_id,$thecook['thu_8_km'],$schoolid);
            $return['km']['km_9'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['thu_9_km']}'");
            $return['km']['km_9']['tname'] = GetTinfoBykb($bj_id,$thecook['thu_9_km'],$schoolid);
            $return['km']['km_10'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['thu_10_km']}'");
            $return['km']['km_10']['tname'] = GetTinfoBykb($bj_id,$thecook['thu_10_km'],$schoolid);
            $return['km']['km_11'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['thu_11_km']}'");
            $return['km']['km_11']['tname'] = GetTinfoBykb($bj_id,$thecook['thu_11_km'],$schoolid);
            $return['km']['km_12'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['thu_12_km']}'");
            $return['km']['km_12']['tname'] = GetTinfoBykb($bj_id,$thecook['thu_12_km'],$schoolid);
        }
    }
    if($week ==5){
        if($cook['friday']){
            $thecook = iunserializer($cook['friday']);
            $return['sd']['sd_1'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['fri_1_sd']}'");
            $return['sd']['sd_2'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['fri_2_sd']}'");
            $return['sd']['sd_3'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['fri_3_sd']}'");
            $return['sd']['sd_4'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['fri_4_sd']}'");
            $return['sd']['sd_5'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['fri_5_sd']}'");
            $return['sd']['sd_6'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['fri_6_sd']}'");
            $return['sd']['sd_7'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['fri_7_sd']}'");
            $return['sd']['sd_8'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['fri_8_sd']}'");
            $return['sd']['sd_9'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['fri_9_sd']}'");
            $return['sd']['sd_10'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['fri_10_sd']}'");
            $return['sd']['sd_11'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['fri_11_sd']}'");
            $return['sd']['sd_12'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['fri_12_sd']}'");
            $return['km']['km_1'] = pdo_fetch("SELECT sname,icon,sid as tname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['fri_1_km']}'");
            $return['km']['km_1']['tname'] = GetTinfoBykb($bj_id,$thecook['fri_1_km'],$schoolid);

            $return['km']['km_2'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['fri_2_km']}'");
            $return['km']['km_2']['tname'] = GetTinfoBykb($bj_id,$thecook['fri_2_km'],$schoolid);

            $return['km']['km_3'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['fri_3_km']}'");
            $return['km']['km_3']['tname'] = GetTinfoBykb($bj_id,$thecook['fri_3_km'],$schoolid);
            $return['km']['km_4'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['fri_4_km']}'");
            $return['km']['km_4']['tname'] = GetTinfoBykb($bj_id,$thecook['fri_4_km'],$schoolid);
            $return['km']['km_5'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['fri_5_km']}'");
            $return['km']['km_5']['tname'] = GetTinfoBykb($bj_id,$thecook['fri_5_km'],$schoolid);
            $return['km']['km_6'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['fri_6_km']}'");
            $return['km']['km_6']['tname'] = GetTinfoBykb($bj_id,$thecook['fri_6_km'],$schoolid);
            $return['km']['km_7'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['fri_7_km']}'");
            $return['km']['km_7']['tname'] = GetTinfoBykb($bj_id,$thecook['fri_7_km'],$schoolid);
            $return['km']['km_8'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['fri_8_km']}'");
            $return['km']['km_8']['tname'] = GetTinfoBykb($bj_id,$thecook['fri_8_km'],$schoolid);
            $return['km']['km_9'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['fri_9_km']}'");
            $return['km']['km_9']['tname'] = GetTinfoBykb($bj_id,$thecook['fri_9_km'],$schoolid);
            $return['km']['km_10'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['fri_10_km']}'");
            $return['km']['km_10']['tname'] = GetTinfoBykb($bj_id,$thecook['fri_10_km'],$schoolid);
            $return['km']['km_11'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['fri_11_km']}'");
            $return['km']['km_11']['tname'] = GetTinfoBykb($bj_id,$thecook['fri_11_km'],$schoolid);
            $return['km']['km_12'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['fri_12_km']}'");
            $return['km']['km_12']['tname'] = GetTinfoBykb($bj_id,$thecook['fri_12_km'],$schoolid);
        }
    }
    if($week ==6){
        if($cook['saturday']){
            $thecook = iunserializer($cook['saturday']);
            $return['sd']['sd_1'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sat_1_sd']}'");
            $return['sd']['sd_2'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sat_2_sd']}'");
            $return['sd']['sd_3'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sat_3_sd']}'");
            $return['sd']['sd_4'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sat_4_sd']}'");
            $return['sd']['sd_5'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sat_5_sd']}'");
            $return['sd']['sd_6'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sat_6_sd']}'");
            $return['sd']['sd_7'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sat_7_sd']}'");
            $return['sd']['sd_8'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sat_8_sd']}'");
            $return['sd']['sd_9'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sat_9_sd']}'");
            $return['sd']['sd_10'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sat_10_sd']}'");
            $return['sd']['sd_11'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sat_11_sd']}'");
            $return['sd']['sd_12'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sat_12_sd']}'");
            $return['km']['km_1'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sat_1_km']}'");
            $return['km']['km_1']['tname'] = GetTinfoBykb($bj_id,$thecook['sat_1_km'],$schoolid);
            $return['km']['km_2'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sat_2_km']}'");
            $return['km']['km_2']['tname'] = GetTinfoBykb($bj_id,$thecook['sat_2_km'],$schoolid);
            $return['km']['km_3'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sat_3_km']}'");
            $return['km']['km_3']['tname'] = GetTinfoBykb($bj_id,$thecook['sat_3_km'],$schoolid);
            $return['km']['km_4'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sat_4_km']}'");
            $return['km']['km_4']['tname'] = GetTinfoBykb($bj_id,$thecook['sat_4_km'],$schoolid);
            $return['km']['km_5'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sat_5_km']}'");
            $return['km']['km_5']['tname'] = GetTinfoBykb($bj_id,$thecook['sat_5_km'],$schoolid);
            $return['km']['km_6'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sat_6_km']}'");
            $return['km']['km_6']['tname'] = GetTinfoBykb($bj_id,$thecook['sat_6_km'],$schoolid);
            $return['km']['km_7'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sat_7_km']}'");
            $return['km']['km_7']['tname'] = GetTinfoBykb($bj_id,$thecook['sat_7_km'],$schoolid);
            $return['km']['km_8'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sat_8_km']}'");
            $return['km']['km_8']['tname'] = GetTinfoBykb($bj_id,$thecook['sat_8_km'],$schoolid);
            $return['km']['km_9'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sat_9_km']}'");
            $return['km']['km_9']['tname'] = GetTinfoBykb($bj_id,$thecook['sat_9_km'],$schoolid);
            $return['km']['km_10'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sat_10_km']}'");
            $return['km']['km_10']['tname'] = GetTinfoBykb($bj_id,$thecook['sat_10_km'],$schoolid);
            $return['km']['km_11'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sat_11_km']}'");
            $return['km']['km_11']['tname'] = GetTinfoBykb($bj_id,$thecook['sat_11_km'],$schoolid);
            $return['km']['km_12'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sat_12_km']}'");
            $return['km']['km_12']['tname'] = GetTinfoBykb($bj_id,$thecook['sat_12_km'],$schoolid);
        }
    }
    if($week == 0){
        if($cook['sunday']){
            $thecook = iunserializer($cook['sunday']);
            $return['sd']['sd_1'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sun_1_sd']}'");
            $return['sd']['sd_2'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sun_2_sd']}'");
            $return['sd']['sd_3'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sun_3_sd']}'");
            $return['sd']['sd_4'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sun_4_sd']}'");
            $return['sd']['sd_5'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sun_5_sd']}'");
            $return['sd']['sd_6'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sun_6_sd']}'");
            $return['sd']['sd_7'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sun_7_sd']}'");
            $return['sd']['sd_8'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sun_8_sd']}'");
            $return['sd']['sd_9'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sun_9_sd']}'");
            $return['sd']['sd_10'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sun_10_sd']}'");
            $return['sd']['sd_11'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sun_11_sd']}'");
            $return['sd']['sd_12'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sun_12_sd']}'");
            $return['km']['km_1'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sun_1_km']}'");
            $return['km']['km_1']['tname'] = GetTinfoBykb($bj_id,$thecook['sun_1_km'],$schoolid);
            $return['km']['km_2'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sun_2_km']}'");
            $return['km']['km_2']['tname'] = GetTinfoBykb($bj_id,$thecook['sun_2_km'],$schoolid);
            $return['km']['km_3'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sun_3_km']}'");
            $return['km']['km_3']['tname'] = GetTinfoBykb($bj_id,$thecook['sun_3_km'],$schoolid);
            $return['km']['km_4'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sun_4_km']}'");
            $return['km']['km_4']['tname'] = GetTinfoBykb($bj_id,$thecook['sun_4_km'],$schoolid);
            $return['km']['km_5'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sun_5_km']}'");
            $return['km']['km_5']['tname'] = GetTinfoBykb($bj_id,$thecook['sun_5_km'],$schoolid);
            $return['km']['km_6'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sun_6_km']}'");
            $return['km']['km_6']['tname'] = GetTinfoBykb($bj_id,$thecook['sun_6_km'],$schoolid);
            $return['km']['km_7'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sun_7_km']}'");
            $return['km']['km_7']['tname'] = GetTinfoBykb($bj_id,$thecook['sun_7_km'],$schoolid);
            $return['km']['km_8'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sun_8_km']}'");
            $return['km']['km_8']['tname'] = GetTinfoBykb($bj_id,$thecook['sun_8_km'],$schoolid);
            $return['km']['km_9'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sun_9_km']}'");
            $return['km']['km_9']['tname'] = GetTinfoBykb($bj_id,$thecook['sun_9_km'],$schoolid);
            $return['km']['km_10'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sun_10_km']}'");
            $return['km']['km_10']['tname'] = GetTinfoBykb($bj_id,$thecook['sun_10_km'],$schoolid);
            $return['km']['km_11'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sun_11_km']}'");
            $return['km']['km_11']['tname'] = GetTinfoBykb($bj_id,$thecook['sun_11_km'],$schoolid);
            $return['km']['km_12'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sun_12_km']}'");
            $return['km']['km_12']['tname'] = GetTinfoBykb($bj_id,$thecook['sun_12_km'],$schoolid);
        }
    }
    return $return;
}

function getkctimetableByTid($schoolid,$tid,$starttime,$endtime){
    mload()->model('tea');
    $myskbjlist = get_myskbj($tid);
    $condition = " And begintime < '{$starttime}' And endtime > '{$endtime}'";
    $week = date("w",$endtime);
    $return = array();
    $thecook = '';
    foreach ($myskbjlist as $key=>$value){
		/********排除放假日期*****/
		mload()->model('vod');
		$hasholiday = check_holiday($value['bj_id'],$starttime);
		if($hasholiday == true){
			$cook = pdo_fetch("SELECT * FROM " . GetTableName('timetable') . " WHERE schoolid = :schoolid And bj_id = :bj_id And ishow = 1 $condition", array(':schoolid' => $schoolid,':bj_id' => $value['bj_id']));
		}else{
			$cook = [];
		}
		/********排除放假日期*****/

        $bjinfo =pdo_fetch("SELECT sname,parentid FROM " . GetTableName('classify') . " WHERE schoolid = :schoolid And sid = :sid ", array(':schoolid' => $schoolid,':sid' => $value['bj_id']));
        $njinfo = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE schoolid = :schoolid And sid = :sid ", array(':schoolid' => $schoolid,':sid' => $bjinfo['parentid']));
        if($week ==1){
            if($cook['monday']){
                $thecook = iunserializer($cook['monday']);
                if(!empty($thecook['mon_1_sd'])){
                    $return['sd']['sd_1'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['mon_1_sd']}'");
                }
                if(!empty($thecook['mon_2_sd'])){
                    $return['sd']['sd_2'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['mon_2_sd']}'");
                }
                if(!empty($thecook['mon_3_sd'])){
                    $return['sd']['sd_3'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['mon_3_sd']}'");
                }
                if(!empty($thecook['mon_4_sd'])){
                    $return['sd']['sd_4'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['mon_4_sd']}'");
                }
                if(!empty($thecook['mon_5_sd'])){
                    $return['sd']['sd_5'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['mon_5_sd']}'");
                }
                if(!empty($thecook['mon_6_sd'])){
                    $return['sd']['sd_6'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['mon_6_sd']}'");
                }
                if(!empty($thecook['mon_7_sd'])){
                    $return['sd']['sd_7'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['mon_7_sd']}'");
                }
                if(!empty($thecook['mon_8_sd'])){
                    $return['sd']['sd_8'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['mon_8_sd']}'");
                }
                if(!empty($thecook['mon_9_sd'])){
                    $return['sd']['sd_9'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['mon_9_sd']}'");
                }
                if(!empty($thecook['mon_10_sd'])){
                    $return['sd']['sd_10'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['mon_10_sd']}'");
                }
                if(!empty($thecook['mon_11_sd'])){
                    $return['sd']['sd_11'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['mon_11_sd']}'");
                }
                if(!empty($thecook['mon_12_sd'])){
                    $return['sd']['sd_12'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['mon_12_sd']}'");
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['mon_1_km'],$schoolid)){
                    $return['km']['km_1'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['mon_1_km']}'");
                    $return['km']['km_1']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_1']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['mon_2_km'],$schoolid)){
                    $return['km']['km_2'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['mon_2_km']}'");
                    $return['km']['km_2']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_2']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['mon_3_km'],$schoolid)){
                    $return['km']['km_3'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['mon_3_km']}'");
                    $return['km']['km_3']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_3']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['mon_4_km'],$schoolid)){
                    $return['km']['km_4'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['mon_4_km']}'");
                    $return['km']['km_4']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_4']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['mon_5_km'],$schoolid)){
                    $return['km']['km_5'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['mon_5_km']}'");
                    $return['km']['km_5']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_5']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['mon_6_km'],$schoolid)){
                    $return['km']['km_6'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['mon_6_km']}'");
                    $return['km']['km_6']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_6']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['mon_7_km'],$schoolid)){
                    $return['km']['km_7'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['mon_7_km']}'");
                    $return['km']['km_7']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_7']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['mon_8_km'],$schoolid)){
                    $return['km']['km_8'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['mon_8_km']}'");
                    $return['km']['km_8']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_8']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['mon_9_km'],$schoolid)){
                    $return['km']['km_9'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['mon_9_km']}'");
                    $return['km']['km_9']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_9']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['mon_10_km'],$schoolid)){
                    $return['km']['km_10']= pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['mon_10_km']}'");
                    $return['km']['km_10']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_10']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['mon_11_km'],$schoolid)){
                    $return['km']['km_11']= pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['mon_11_km']}'");
                    $return['km']['km_11']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_11']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['mon_12_km'],$schoolid)){
                    $return['km']['km_12']= pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['mon_12_km']}'");
                    $return['km']['km_12']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_12']['njname'] = $njinfo['sname'];
                }
            }
        }
        if($week ==2){
            if($cook['tuesday']){
                $thecook = iunserializer($cook['tuesday']);
                if(!empty($thecook['tus_1_sd'])){
                    $return['sd']['sd_1'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['tus_1_sd']}'");
                }
                if(!empty($thecook['tus_2_sd'])){
                    $return['sd']['sd_2'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['tus_2_sd']}'");
                }
                if(!empty($thecook['tus_3_sd'])){
                    $return['sd']['sd_3'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['tus_3_sd']}'");
                }
                if(!empty($thecook['tus_4_sd'])){
                    $return['sd']['sd_4'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['tus_4_sd']}'");
                }
                if(!empty($thecook['tus_5_sd'])){
                    $return['sd']['sd_5'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['tus_5_sd']}'");
                }
                if(!empty($thecook['tus_6_sd'])){
                    $return['sd']['sd_6'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['tus_6_sd']}'");
                }
                if(!empty($thecook['tus_7_sd'])){
                    $return['sd']['sd_7'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['tus_7_sd']}'");
                }
                if(!empty($thecook['tus_8_sd'])){
                    $return['sd']['sd_8'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['tus_8_sd']}'");
                }
                if(!empty($thecook['tus_9_sd'])){
                    $return['sd']['sd_9'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['tus_9_sd']}'");
                }
                if(!empty($thecook['tus_10_sd'])){
                    $return['sd']['sd_10'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['tus_10_sd']}'");
                }
                if(!empty($thecook['tus_11_sd'])){
                    $return['sd']['sd_11'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['tus_11_sd']}'");
                }
                if(!empty($thecook['tus_12_sd'])){
                    $return['sd']['sd_12'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['tus_12_sd']}'");
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['tus_1_km'],$schoolid)){
                    $return['km']['km_1'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['tus_1_km']}'");
                    $return['km']['km_1']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_1']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['tus_2_km'],$schoolid)){
                    $return['km']['km_2'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['tus_2_km']}'");
                    $return['km']['km_2']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_2']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['tus_3_km'],$schoolid)){
                    $return['km']['km_3'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['tus_3_km']}'");
                    $return['km']['km_3']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_3']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['tus_4_km'],$schoolid)){
                    $return['km']['km_4'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['tus_4_km']}'");
                    $return['km']['km_4']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_4']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['tus_5_km'],$schoolid)){
                    $return['km']['km_5'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['tus_5_km']}'");
                    $return['km']['km_5']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_5']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['tus_6_km'],$schoolid)){
                    $return['km']['km_6'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['tus_6_km']}'");
                    $return['km']['km_6']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_6']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['tus_7_km'],$schoolid)){
                    $return['km']['km_7'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['tus_7_km']}'");
                    $return['km']['km_7']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_7']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['tus_8_km'],$schoolid)){
                    $return['km']['km_8'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['tus_8_km']}'");
                    $return['km']['km_8']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_8']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['tus_9_km'],$schoolid)){
                    $return['km']['km_9'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['tus_9_km']}'");
                    $return['km']['km_9']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_9']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['tus_10_km'],$schoolid)){
                    $return['km']['km_10']= pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['tus_10_km']}'");
                    $return['km']['km_10']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_10']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['tus_11_km'],$schoolid)){
                    $return['km']['km_11']= pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['tus_11_km']}'");
                    $return['km']['km_11']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_11']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['tus_12_km'],$schoolid)){
                    $return['km']['km_12']= pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['tus_12_km']}'");
                    $return['km']['km_12']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_12']['njname'] = $njinfo['sname'];
                }
            }
        }
        if($week ==3){
            if($cook['wednesday']){
                $thecook = iunserializer($cook['wednesday']);
                if(!empty($thecook['wed_1_sd'])){
                    $return['sd']['sd_1'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['wed_1_sd']}'");
                }
                if(!empty($thecook['wed_2_sd'])){
                    $return['sd']['sd_2'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['wed_2_sd']}'");
                }
                if(!empty($thecook['wed_3_sd'])){
                    $return['sd']['sd_3'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['wed_3_sd']}'");
                }
                if(!empty($thecook['wed_4_sd'])){
                    $return['sd']['sd_4'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['wed_4_sd']}'");
                }
                if(!empty($thecook['wed_5_sd'])){
                    $return['sd']['sd_5'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['wed_5_sd']}'");
                }
                if(!empty($thecook['wed_6_sd'])){
                    $return['sd']['sd_6'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['wed_6_sd']}'");
                }
                if(!empty($thecook['wed_7_sd'])){
                    $return['sd']['sd_7'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['wed_7_sd']}'");
                }
                if(!empty($thecook['wed_8_sd'])){
                    $return['sd']['sd_8'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['wed_8_sd']}'");
                }
                if(!empty($thecook['wed_9_sd'])){
                    $return['sd']['sd_9'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['wed_9_sd']}'");
                }
                if(!empty($thecook['wed_10_sd'])){
                    $return['sd']['sd_10'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['wed_10_sd']}'");
                }
                if(!empty($thecook['wed_11_sd'])){
                    $return['sd']['sd_11'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['wed_11_sd']}'");
                }
                if(!empty($thecook['wed_12_sd'])){
                    $return['sd']['sd_12'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['wed_12_sd']}'");
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['wed_1_km'],$schoolid)){
                    $return['km']['km_1'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['wed_1_km']}'");
                    $return['km']['km_1']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_1']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['wed_2_km'],$schoolid)){
                    $return['km']['km_2'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['wed_2_km']}'");
                    $return['km']['km_2']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_2']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['wed_3_km'],$schoolid)){
                    $return['km']['km_3'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['wed_3_km']}'");
                    $return['km']['km_3']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_3']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['wed_4_km'],$schoolid)){
                    $return['km']['km_4'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['wed_4_km']}'");
                    $return['km']['km_4']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_4']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['wed_5_km'],$schoolid)){
                    $return['km']['km_5'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['wed_5_km']}'");
                    $return['km']['km_5']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_5']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['wed_6_km'],$schoolid)){
                    $return['km']['km_6'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['wed_6_km']}'");
                    $return['km']['km_6']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_6']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['wed_7_km'],$schoolid)){
                    $return['km']['km_7'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['wed_7_km']}'");
                    $return['km']['km_7']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_7']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['wed_8_km'],$schoolid)){
                    $return['km']['km_8'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['wed_8_km']}'");
                    $return['km']['km_8']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_8']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['wed_9_km'],$schoolid)){
                    $return['km']['km_9'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['wed_9_km']}'");
                    $return['km']['km_9']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_9']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['wed_10_km'],$schoolid)){
                    $return['km']['km_10']= pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['wed_10_km']}'");
                    $return['km']['km_10']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_10']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['wed_11_km'],$schoolid)){
                    $return['km']['km_11']= pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['wed_11_km']}'");
                    $return['km']['km_11']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_11']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['wed_12_km'],$schoolid)){
                    $return['km']['km_12']= pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['wed_12_km']}'");
                    $return['km']['km_12']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_12']['njname'] = $njinfo['sname'];
                }
            }
        }
        if($week ==4){
            if($cook['thursday']){
                $thecook = iunserializer($cook['thursday']);
                if(!empty($thecook['thu_1_sd'])){
                    $return['sd']['sd_1'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['thu_1_sd']}'");
                }
                if(!empty($thecook['thu_2_sd'])){
                    $return['sd']['sd_2'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['thu_2_sd']}'");
                }
                if(!empty($thecook['thu_3_sd'])){
                    $return['sd']['sd_3'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['thu_3_sd']}'");
                }
                if(!empty($thecook['thu_4_sd'])){
                    $return['sd']['sd_4'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['thu_4_sd']}'");
                }
                if(!empty($thecook['thu_5_sd'])){
                    $return['sd']['sd_5'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['thu_5_sd']}'");
                }
                if(!empty($thecook['thu_6_sd'])){
                    $return['sd']['sd_6'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['thu_6_sd']}'");
                }
                if(!empty($thecook['thu_7_sd'])){
                    $return['sd']['sd_7'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['thu_7_sd']}'");
                }
                if(!empty($thecook['thu_8_sd'])){
                    $return['sd']['sd_8'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['thu_8_sd']}'");
                }
                if(!empty($thecook['thu_9_sd'])){
                    $return['sd']['sd_9'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['thu_9_sd']}'");
                }
                if(!empty($thecook['thu_10_sd'])){
                    $return['sd']['sd_10'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['thu_10_sd']}'");
                }
                if(!empty($thecook['thu_11_sd'])){
                    $return['sd']['sd_11'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['thu_11_sd']}'");
                }
                if(!empty($thecook['thu_12_sd'])){
                    $return['sd']['sd_12'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['thu_12_sd']}'");
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['thu_1_km'],$schoolid)){
                    $return['km']['km_1'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['thu_1_km']}'");
                    $return['km']['km_1']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_1']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['thu_2_km'],$schoolid)){
                    $return['km']['km_2'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['thu_2_km']}'");
                    $return['km']['km_2']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_2']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['thu_3_km'],$schoolid)){
                    $return['km']['km_3'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['thu_3_km']}'");
                    $return['km']['km_3']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_3']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['thu_4_km'],$schoolid)){
                    $return['km']['km_4'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['thu_4_km']}'");
                    $return['km']['km_4']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_4']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['thu_5_km'],$schoolid)){
                    $return['km']['km_5'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['thu_5_km']}'");
                    $return['km']['km_5']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_5']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['thu_6_km'],$schoolid)){
                    $return['km']['km_6'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['thu_6_km']}'");
                    $return['km']['km_6']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_6']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['thu_7_km'],$schoolid)){
                    $return['km']['km_7'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['thu_7_km']}'");
                    $return['km']['km_7']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_7']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['thu_8_km'],$schoolid)){
                    $return['km']['km_8'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['thu_8_km']}'");
                    $return['km']['km_8']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_8']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['thu_9_km'],$schoolid)){
                    $return['km']['km_9'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['thu_9_km']}'");
                    $return['km']['km_9']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_9']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['thu_10_km'],$schoolid)){
                    $return['km']['km_10']= pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['thu_10_km']}'");
                    $return['km']['km_10']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_10']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['thu_11_km'],$schoolid)){
                    $return['km']['km_11']= pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['thu_11_km']}'");
                    $return['km']['km_11']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_11']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['thu_12_km'],$schoolid)){
                    $return['km']['km_12']= pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['thu_12_km']}'");
                    $return['km']['km_12']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_12']['njname'] = $njinfo['sname'];
                }

            }
        }
        if($week ==5){
            if($cook['friday']){
                $thecook = unserialize($cook['friday']);
                $back = &$thecook;
                if(!empty($thecook['fri_1_sd'])){
                    $return['sd']['sd_1'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['fri_1_sd']}'");
                }
                if(!empty($thecook['fri_2_sd'])){
                    $return['sd']['sd_2'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['fri_2_sd']}'");
                }
                if(!empty($thecook['fri_3_sd'])){
                    $return['sd']['sd_3'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['fri_3_sd']}'");
                }
                if(!empty($thecook['fri_4_sd'])){
                    $return['sd']['sd_4'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['fri_4_sd']}'");
                }
                if(!empty($thecook['fri_5_sd'])){
                    $return['sd']['sd_5'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['fri_5_sd']}'");
                }
                if(!empty($thecook['fri_6_sd'])){
                    $return['sd']['sd_6'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['fri_6_sd']}'");
                }
                if(!empty($thecook['fri_7_sd'])){
                    $return['sd']['sd_7'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['fri_7_sd']}'");
                }
                if(!empty($thecook['fri_8_sd'])){
                    $return['sd']['sd_8'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['fri_8_sd']}'");
                }
                if(!empty($thecook['fri_9_sd'])){
                    $return['sd']['sd_9'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['fri_9_sd']}'");
                }
                if(!empty($thecook['fri_10_sd'])){
                    $return['sd']['sd_10'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['fri_10_sd']}'");
                }
                if(!empty($thecook['fri_11_sd'])){
                    $return['sd']['sd_11'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['fri_11_sd']}'");
                }
                if(!empty($thecook['fri_12_sd'])){
                    $return['sd']['sd_12'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['fri_12_sd']}'");
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['fri_1_km'],$schoolid)){
                    $return['km']['km_1'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['fri_1_km']}'");
                    $return['km']['km_1']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_1']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['fri_2_km'],$schoolid)){
                    $return['km']['km_2'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['fri_2_km']}'");
                    $return['km']['km_2']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_2']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['fri_3_km'],$schoolid)){
                    $return['km']['km_3'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['fri_3_km']}'");
                    $return['km']['km_3']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_3']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['fri_4_km'],$schoolid)){
                    $return['km']['km_4'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['fri_4_km']}'");
                    $return['km']['km_4']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_4']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['fri_5_km'],$schoolid)){
                    $return['km']['km_5'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['fri_5_km']}'");
                    $return['km']['km_5']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_5']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['fri_6_km'],$schoolid)){
                    $return['km']['km_6'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['fri_6_km']}'");
                    $return['km']['km_6']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_6']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['fri_7_km'],$schoolid)){
                    $return['km']['km_7'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['fri_7_km']}'");
                    $return['km']['km_7']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_7']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['fri_8_km'],$schoolid)){
                    $return['km']['km_8'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['fri_8_km']}'");
                    $return['km']['km_8']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_8']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['fri_9_km'],$schoolid)){
                    $return['km']['km_9'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['fri_9_km']}'");
                    $return['km']['km_9']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_9']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['fri_10_km'],$schoolid)){
                    $return['km']['km_10']= pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['fri_10_km']}'");
                    $return['km']['km_10']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_10']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['fri_11_km'],$schoolid)){
                    $return['km']['km_11']= pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['fri_11_km']}'");
                    $return['km']['km_11']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_11']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['fri_12_km'],$schoolid)){
                    $return['km']['km_12']= pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['fri_12_km']}'");
                    $return['km']['km_12']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_12']['njname'] = $njinfo['sname'];
                }

            }
        }
        if($week ==6){
            if($cook['saturday']){
                $thecook = iunserializer($cook['saturday']);
                if(!empty($thecook['sat_1_sd'])){
                    $return['sd']['sd_1'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sat_1_sd']}'");
                }
                if(!empty($thecook['sat_2_sd'])){
                    $return['sd']['sd_2'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sat_2_sd']}'");
                }
                if(!empty($thecook['sat_3_sd'])){
                    $return['sd']['sd_3'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sat_3_sd']}'");
                }
                if(!empty($thecook['sat_4_sd'])){
                    $return['sd']['sd_4'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sat_4_sd']}'");
                }
                if(!empty($thecook['sat_5_sd'])){
                    $return['sd']['sd_5'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sat_5_sd']}'");
                }
                if(!empty($thecook['sat_6_sd'])){
                    $return['sd']['sd_6'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sat_6_sd']}'");
                }
                if(!empty($thecook['sat_7_sd'])){
                    $return['sd']['sd_7'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sat_7_sd']}'");
                }
                if(!empty($thecook['sat_8_sd'])){
                    $return['sd']['sd_8'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sat_8_sd']}'");
                }
                if(!empty($thecook['sat_9_sd'])){
                    $return['sd']['sd_9'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sat_9_sd']}'");
                }
                if(!empty($thecook['sat_10_sd'])){
                    $return['sd']['sd_10'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sat_10_sd']}'");
                }
                if(!empty($thecook['sat_11_sd'])){
                    $return['sd']['sd_11'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sat_11_sd']}'");
                }
                if(!empty($thecook['sat_12_sd'])){
                    $return['sd']['sd_12'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sat_12_sd']}'");
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['sat_1_km'],$schoolid)){
                    $return['km']['km_1'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sat_1_km']}'");
                    $return['km']['km_1']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_1']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['sat_2_km'],$schoolid)){
                    $return['km']['km_2'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sat_2_km']}'");
                    $return['km']['km_2']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_2']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['sat_3_km'],$schoolid)){
                    $return['km']['km_3'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sat_3_km']}'");
                    $return['km']['km_3']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_3']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['sat_4_km'],$schoolid)){
                    $return['km']['km_4'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sat_4_km']}'");
                    $return['km']['km_4']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_4']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['sat_5_km'],$schoolid)){
                    $return['km']['km_5'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sat_5_km']}'");
                    $return['km']['km_5']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_5']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['sat_6_km'],$schoolid)){
                    $return['km']['km_6'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sat_6_km']}'");
                    $return['km']['km_6']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_6']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['sat_7_km'],$schoolid)){
                    $return['km']['km_7'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sat_7_km']}'");
                    $return['km']['km_7']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_7']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['sat_8_km'],$schoolid)){
                    $return['km']['km_8'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sat_8_km']}'");
                    $return['km']['km_8']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_8']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['sat_9_km'],$schoolid)){
                    $return['km']['km_9'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sat_9_km']}'");
                    $return['km']['km_9']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_9']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['sat_10_km'],$schoolid)){
                    $return['km']['km_10']= pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sat_10_km']}'");
                    $return['km']['km_10']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_10']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['sat_11_km'],$schoolid)){
                    $return['km']['km_11']= pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sat_11_km']}'");
                    $return['km']['km_11']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_11']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['sat_12_km'],$schoolid)){
                    $return['km']['km_12']= pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sat_12_km']}'");
                    $return['km']['km_12']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_12']['njname'] = $njinfo['sname'];
                }

            }
        }
        if($week == 0){
            if($cook['sunday']){
                $thecook = iunserializer($cook['sunday']);
                if(!empty($thecook['sun_1_sd'])){
                    $return['sd']['sd_1'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sun_1_sd']}'");
                }
                if(!empty($thecook['sun_2_sd'])){
                    $return['sd']['sd_2'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sun_2_sd']}'");
                }
                if(!empty($thecook['sun_3_sd'])){
                    $return['sd']['sd_3'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sun_3_sd']}'");
                }
                if(!empty($thecook['sun_4_sd'])){
                    $return['sd']['sd_4'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sun_4_sd']}'");
                }
                if(!empty($thecook['sun_5_sd'])){
                    $return['sd']['sd_5'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sun_5_sd']}'");
                }
                if(!empty($thecook['sun_6_sd'])){
                    $return['sd']['sd_6'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sun_6_sd']}'");
                }
                if(!empty($thecook['sun_7_sd'])){
                    $return['sd']['sd_7'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sun_7_sd']}'");
                }
                if(!empty($thecook['sun_8_sd'])){
                    $return['sd']['sd_8'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sun_8_sd']}'");
                }
                if(!empty($thecook['sun_9_sd'])){
                    $return['sd']['sd_9'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sun_9_sd']}'");
                }
                if(!empty($thecook['sun_10_sd'])){
                    $return['sd']['sd_10'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sun_10_sd']}'");
                }
                if(!empty($thecook['sun_11_sd'])){
                    $return['sd']['sd_11'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sun_11_sd']}'");
                }
                if(!empty($thecook['sun_12_sd'])){
                    $return['sd']['sd_12'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sun_12_sd']}'");
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['sun_1_km'],$schoolid)){
                    $return['km']['km_1'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sun_1_km']}'");
                    $return['km']['km_1']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_1']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['sun_2_km'],$schoolid)){
                    $return['km']['km_2'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sun_2_km']}'");
                    $return['km']['km_2']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_2']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['sun_3_km'],$schoolid)){
                    $return['km']['km_3'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sun_3_km']}'");
                    $return['km']['km_3']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_3']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['sun_4_km'],$schoolid)){
                    $return['km']['km_4'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sun_4_km']}'");
                    $return['km']['km_4']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_4']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['sun_5_km'],$schoolid)){
                    $return['km']['km_5'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sun_5_km']}'");
                    $return['km']['km_5']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_5']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['sun_6_km'],$schoolid)){
                    $return['km']['km_6'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sun_6_km']}'");
                    $return['km']['km_6']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_6']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['sun_7_km'],$schoolid)){
                    $return['km']['km_7'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sun_7_km']}'");
                    $return['km']['km_7']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_7']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['sun_8_km'],$schoolid)){
                    $return['km']['km_8'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sun_8_km']}'");
                    $return['km']['km_8']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_8']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['sun_9_km'],$schoolid)){
                    $return['km']['km_9'] = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sun_9_km']}'");
                    $return['km']['km_9']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_9']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['sun_10_km'],$schoolid)){
                    $return['km']['km_10']= pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sun_10_km']}'");
                    $return['km']['km_10']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_10']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['sun_11_km'],$schoolid)){
                    $return['km']['km_11']= pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sun_11_km']}'");
                    $return['km']['km_11']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_11']['njname'] = $njinfo['sname'];
                }
                if(CheckIsShowKm($tid,$value['bj_id'],$thecook['sun_12_km'],$schoolid)){
                    $return['km']['km_12']= pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE sid = '{$thecook['sun_12_km']}'");
                    $return['km']['km_12']['bjname'] = $bjinfo['sname'];
                    $return['km']['km_12']['njname'] = $njinfo['sname'];
                }
            }
        }
    }
    return $return;
}

function check_plugin($name){
    $item = pdo_fetch("SELECT * FROM " . tablename('modules') . " WHERE name = '{$name}' ");
    $result = false;
    if($item){
        $result = true;
    }
    return $result;
}

/**
 * 特别提醒
 *
 * @param [type] $weid
 * @param [type] $schoolid
 * @param [type] $nowtime
 * @param [type] $kcid
 * @param [type] $over_status 状态 1课时不足 2持续未到 -1不限
 * @param [type] $pindex 
 * @param [type] $psize
 * @return 返回数组，和数量
 */
function GetReMind($weid,$schoolid,$nowtime,$kcid,$over_status,$pindex,$psize,$mobile = false){
	//查询出特别提醒条件
    $schoolset = pdo_fetch("SELECT no_kcsign_num,no_ks_num FROM ".GetTableName('schoolset')." WHERE  weid = '{$weid}' AND schoolid = '{$schoolid}' ");
    $no_ks_num = $schoolset['no_ks_num'];//课时不足
	$no_kcsign_num = $schoolset['no_kcsign_num'] * 86400; //持续多久未到
	
	if(empty($no_ks_num) && $over_status == 1){
		$data = array(
			'list' => [], 
			'total' => 0,
		);
		return $data;
	}

	if(empty($schoolset['no_kcsign_num']) && $over_status == 2){
		$data = array(
			'list' => [], 
			'total' => 0,
		);
		return $data;
	}

    $tablecb = GetTableName('coursebuy');
    $tablekcs = GetTableName('kcsign');
    $SQL_F = "";
    //课时不足
    $SQL_A = <<<EOF
        SELECT cb.sid,cb.ksnum,cb.createtime FROM {$tablecb} as cb WHERE cb.kcid = '{$kcid}' having cb.ksnum - (SELECT coalesce(SUM(kcs.costnum),0) FROM {$tablekcs} as kcs WHERE kcs.kcid = cb.kcid AND kcs.sid = cb.sid AND status = 2) < {$no_ks_num} 
EOF;
    //持续未到
    $SQL_B = <<<EOF
        SELECT cb.sid,cb.ksnum,cb.createtime FROM {$tablecb} as cb WHERE cb.kcid = '{$kcid}' having ($nowtime - (SELECT MAX(createtime) FROM {$tablekcs} as kcs WHERE kcs.kcid = cb.kcid AND kcs.sid = cb.sid AND status = 2)) > {$no_kcsign_num} 
EOF;
	if($mobile){
		$newpage = $pindex;
	}else{
		$newpage = ($pindex - 1) * $psize;

	}
    if($over_status  == 1) {
        //课时不足
		$SQL_F = $SQL_A. " ORDER BY cb.createtime LIMIT ". $newpage . ',' . $psize;
		//课时不足总数
		$total = count(pdo_fetchall($SQL_A));
    }elseif($over_status  == 2){ 
        //持续未到
		$SQL_F = $SQL_B. " ORDER BY cb.createtime LIMIT ". $newpage . ',' . $psize;
		//持续未到总数
		$total = count(pdo_fetchall($SQL_B));
    }else{
        //不限条件
		$SQL_F = '('. $SQL_A . ') union (' .$SQL_B. ") ORDER BY createtime LIMIT ". $newpage . ',' . $psize;
        //不限条件总数
		$SQL_COUNTF = '('. $SQL_A . ') union (' .$SQL_B. ") ";
        $total = count(pdo_fetchall($SQL_COUNTF));
	}
	$list = pdo_fetchall($SQL_F);
	$data = array(
		'list' => $list, 
		'total' => $total,
	);
	return $data;
}


/**
 * 预警值不足提醒
 *
 * @param [type] $weid
 * @param [type] $schoolid
 * @param [type] $nowtime
 * @param [type] $kcid
 * @param [type] $over_status 状态 1课时不足 2持续未到 -1不限
 * @param [type] $pindex 
 * @param [type] $psize
 * @return 返回数组，和数量
 */
function GetKsWaring($weid,$schoolid,$nowtime,$kcid,$over_status,$pindex,$psize,$mobile = false){

	//查询出特别提醒条件
	$schoolset = pdo_fetch("SELECT no_ks_num FROM ".GetTableName('schoolset')." WHERE  weid = '{$weid}' AND schoolid = '{$schoolid}' ");
	$no_ks_num = $schoolset['no_ks_num'];//课时不足
	if(empty($no_ks_num)){
		$data = array(
			'list' => [], 
			'total' => 0,
		);
		return $data;
	}

	
    $tablecb = GetTableName('coursebuy');
	$tablekcs = GetTableName('kcsign');
	$tablekc = GetTableName('tcourse');


	$checkNowTime = strtotime(date("Y-m-d",time()));

    $SQL_F = "";
	//课时不足
	if($kcid != 0){
		$SQL_A = <<<EOF
        SELECT cb.sid,cb.ksnum,cb.createtime,cb.kcid FROM {$tablecb} as cb WHERE cb.kcid = '{$kcid}' AND cb.is_change != 1 having cb.ksnum - (SELECT coalesce(SUM(kcs.costnum),0) FROM {$tablekcs} as kcs WHERE kcs.kcid = cb.kcid AND kcs.sid = cb.sid AND status = 2) < {$no_ks_num} 
EOF;
	}else{
		$SQL_A = <<<EOF
        SELECT cb.sid,cb.ksnum,cb.createtime,cb.kcid FROM {$tablecb} as cb , {$tablekc} as kcbiao  WHERE cb.weid = '{$weid}' AND cb.is_change != 1 and cb.schoolid = '{$schoolid}'  AND kcbiao.kc_type != 1 and kcbiao.end > $checkNowTime and cb.kcid = kcbiao.id having cb.ksnum - (SELECT coalesce(SUM(kcs.costnum),0) FROM {$tablekcs} as kcs WHERE kcs.kcid = cb.kcid AND kcs.sid = cb.sid AND status = 2) < {$no_ks_num}
EOF;
	}

	if($mobile){
		$newpage = $pindex;
	}else{
		$newpage = ($pindex - 1) * $psize;

	}
	//课时不足
	$SQL_F = $SQL_A. " ORDER BY cb.createtime LIMIT ". $newpage . ',' . $psize;
	//课时不足总数
	$total = count(pdo_fetchall($SQL_A));
	$list = pdo_fetchall($SQL_F);
	$data = array(
		'list' => $list, 
		'total' => $total,
	);
	return $data;
}

function GetDfl($weid,$schoolid,$year,$type = '4',$tid ='0'){

	$dfl = ''; //最终到访率
	$cdl = ''; //成单率
	$xbl = ''; //续班率
	$xkl = ''; //消课率
	$mbl = ''; //满班率
	$kcid = '';
	if($type == 1){
		$num = 12;
		$cell = 1;
	}elseif($type == 2){
		$num = 4;
		$cell = 3;
	}elseif($type == 3){
		$num = 2;
		$cell = 6;
	}elseif($type == 4){
		$num = 1;
		$cell = 12;
	}

	if($tid != 0 ){
		$condition = " AND tid = '{$tid}' ";
		//当前老师的邀约人数
		$condition1 = " AND superior_tid = '{$tid}' ";
		$condition1_1 = " AND tid = '{$tid}' ";
	}else{

	}
	for($i = 1 ; $i <= $num ; $i++){
		$qdjfnum = 0; //签单缴费人数(非当前试听课程)
		$yystunum = 0 ;
		$ststu = 0;
		$start_int = ($i - 1) * $cell + 1;
		$starttime = strtotime($year.'-'.$start_int);
		$endtime = strtotime($year.'-'.$start_int."+ {$cell} month") - 1;

		$alltrykc = pdo_fetchall("SELECT id FROM " . GetTableName('tcourse') . " where is_try = 1 AND schoolid = '{$schoolid}' AND weid = '{$weid}' AND NOT (start >= '{$endtime}' OR end <= '{$starttime}') $condition"); //试听课程


		//$alltrykc = pdo_fetchall("SELECT id FROM " . GetTableName('tcourse') . " where is_try = 1 AND schoolid = '{$schoolid}' AND weid = '{$weid}' AND  (  ( start <= '{$starttime}'  AND end >= '{$endtime}')  or (start >= '{$starttime}'  AND start <= '{$endtime}' ) or (end >= '{$starttime}'  AND end <= '{$endtime}' ) ) $condition"); //试听课程



		if(!empty($alltrykc)){
			/************************到访率************************/ 
			
			foreach($alltrykc as $k => $v){
				//获取邀约人数
				$nowkcstu = pdo_fetchall("SELECT distinct sid FROM " . GetTableName('order') . " where kcid = '{$v['id']}' $condition1");
				$yystunum += count($nowkcstu);//获取邀约人数
				//当前时间段内总试听人数 +=	当前时间段内试听当前课程的人数 
				if(empty($tid)){
					$stuliss = pdo_fetchall("SELECT DISTINCT sid FROM " . GetTableName('kcsign') . " where kcid = '{$v['id']}' AND status = 2 AND tid =0 and createtime >= '{$starttime}' AND createtime <= '{$endtime}' ");
					$ststu += count($stuliss);
					
				}else{
					$stsidstr = arrayToString($nowkcstu);
					$sidcondition = " AND FIND_IN_SET(sid,'{$stsidstr}')";
					//获取 当前课程 在当前时间段内所有试听学生
					$stuliss = pdo_fetchall("SELECT DISTINCT sid FROM " . GetTableName('kcsign') . " where kcid = '{$v['id']}' AND status = 2 AND tid =0 and createtime >= '{$starttime}' AND createtime <= '{$endtime}' $sidcondition");
					$ststu += count($stuliss);
				}
				
			}
			$dfl[] = !empty($yystunum) ? round(intval($ststu) / intval($yystunum) * 100,2) : 0; //到访率

			//$dfl[] = $ststu; //到访率


			/************************到访率************************/ 

			/************************成单率************************/ 
			$kcid = arrayToString($alltrykc); //当前时间段内试听课程 转换成字符串
			//签单缴费(当前试听课程学生报名了非当前试听课程统计)
			if(!empty($stuliss)){
				foreach($stuliss as $k1 => $v1){
					//缴费人数
					$bmstu = pdo_fetch("SELECT id FROM " . GetTableName('order') . " where NOT FIND_IN_SET(kcid,'{$kcid}') AND sid = '{$v1}' AND status = 2 ");
					if(!empty($bmstu)){
						$qdjfnum++;
					}
				}
			}
			 $cdl[] = !empty($ststu) ? round(intval($qdjfnum) / intval($ststu) * 100,2) : 0;

			//$cdl[] = $yystunum;

			/************************成单率************************/ 
		}else{
			$cdl[] = 0;
			$dfl[]  = 0;
		}

		/************************续班率************************/
		//现有总用户
		$allstunum = pdo_fetchcolumn("SELECT count( DISTINCT sid) FROM " . GetTableName('coursebuy') . " where schoolid = '{$schoolid}' AND weid = '{$weid}' ");
		//续班的用户
		$xbstu = pdo_fetchall("SELECT DISTINCT sid FROM " . GetTableName('order') . " where NOT FIND_IN_SET(kcid,'{$kcid}') AND schoolid = '{$schoolid}' AND weid = '{$weid}' and status = 2 AND type = 1 AND sid !=0 AND kcstatus = 1 AND paytime >= '{$starttime}' AND paytime <= '{$endtime}'");
		//续班率
		$xbl[] = !empty($allstunum) ?  round(intval(count($xbstu)) / intval($allstunum) * 100,2) : 0;
		/************************续班率************************/ 

		/************************消课率************************/ 
		//满勤数量
		$mqnum = 0;
		//获取当前时间段的所有排课 (kcbiao)
		$AllKs = pdo_fetchall("SELECT kcid,id,date,costnum FROM " . GetTableName('kcbiao') . " where NOT FIND_IN_SET(kcid,'{$kcid}') and date >= '{$starttime}' AND date <= '{$endtime}' and schoolid = '{$schoolid}' AND weid = '{$weid}' $condition");
		//去重后的课程并转化为str
		$SJXKNUM = 0 ; //实际消课数
		$AllJHXK = 0 ; //总的计划消课数
		foreach($AllKs as $ks => $vs){
			$CBTable =  GetTableName('coursebuy');
			$KCSTable = GetTableName('kcsign');
			$SQl = <<<EOF

			SELECT 
				cb.ksnum , cb.kcid ,cb.sid,cb.id
			FROM {$CBTable}  as cb 
			where
				cb.kcid = '{$vs['kcid']}'
				AND cb.createtime <= '{$vs['date']}' 
				AND cb.sid != 0
				and schoolid = '{$schoolid}' AND weid = '{$weid}'
			having 
				(cb.ksnum - ifnull((SELECT SUM(costnum) FROM {$KCSTable} where sid = cb.sid and schoolid = '{$schoolid}' AND weid = '{$weid}' and kcid = cb.kcid and status=2 AND createtime <= '{$vs['date']}'),0) >0)
EOF;
			$haskcnum = pdo_fetchall($SQl);
			//去重取出sid并转化为字符串
			$sidstr = arrayToString(array_unique(array_column($haskcnum,'sid')));
			// var_dump($sidstr);

			if(empty($tid)){
				$condition_sjxk = " AND sid != 0 ";
			}else{
				$condition_sjxk = " FIND_IN_SET(sid,'{$sidstr}') ";
			}
			//实际消耗课时
			$SJXKNUM += pdo_fetchcolumn("SELECT SUM(costnum) FROM ".GetTableName('kcsign')." WHERE ksid = '{$vs['id']}' and status=2  and schoolid = '{$schoolid}' AND weid = '{$weid}' and sid != 0  ");
			
			//计划消耗课时
			$CanCheckStuCount = count($haskcnum);
			$AllJHXK += $CanCheckStuCount * $vs	['costnum'];
			//查当前课时有多少学生签到  然后和 $CanCheckStuCount 比较， 相等就是满勤 ，满勤数加一 ， 不等就不是
			if(empty($tid)){
				$nowksstu = pdo_fetchcolumn("SELECT count(*) FROM ".GetTableName('kcsign')." WHERE ksid = '{$vs['id']}' and status=2 AND sid != 0 AND tid = 0 and schoolid = '{$schoolid}' AND weid = '{$weid}'");
			}else{
				$temp_nowksstu = pdo_fetchAll("SELECT id FROM ".GetTableName('kcsign')." WHERE ksid = '{$vs['id']}' and status=2 and FIND_IN_SET(sid,'{$sidstr}')  and schoolid = '{$schoolid}' AND weid = '{$weid}'");
				$nowksstu = count($temp_nowksstu);
			}
			
			if($nowksstu == $CanCheckStuCount && $CanCheckStuCount != 0){
				$mqnum++;
			}	
		}
	
		$xkl[] = !empty($AllJHXK) ? round(intval($SJXKNUM) / intval($AllJHXK) * 100,2) : 0 ;
		/************************消课率************************/ 
		
		/************************满班率************************/

		$mbl[] = !empty($AllKs) ? round(intval($mqnum) / intval(count($AllKs))  * 100,2) : 0;

		/************************满班率************************/ 
	}
	$list = array(
		'0' => $dfl,  
		'1' => $cdl, 
		'2' => $xbl, 
		'3' => $xkl, 
		'4' => $mbl
	);
	// echo  "<br/>>";
	return $list;

}

function GetDayKpi($weid,$schoolid,$start,$daydiff){

	$dfl = ''; //最终到访率
	$dfl_F = ''; //最终到访人
	$dfl_B = ''; //到访基数
	$cdl = ''; //成单率
	$xbl = ''; //续班率
	$xkl = ''; //消课率
	$mbl = ''; //满班率
	
	$kcid = '';
	for($i = 1 ; $i <= $daydiff ; $i++){
		$qdjfnum = 0; //签单缴费人数(非当前试听课程)
		$yystunum = 0 ;
		$ststu = 0;
		$starttime = strtotime($start) + ($i * 86400 - 86400);
		$endtime = strtotime($start) + 86399 +  ($i * 86400 - 86400);
		 $alltrykc = pdo_fetchall("SELECT id FROM " . GetTableName('tcourse') . " where is_try = 1 AND schoolid = '{$schoolid}' AND weid = '{$weid}' AND NOT (start >= '{$endtime}' OR end <= '{$starttime}')"); //试听课程
		//$alltrykc = pdo_fetchall("SELECT id FROM " . GetTableName('tcourse') . " where is_try = 1 AND schoolid = '{$schoolid}' AND weid = '{$weid}' AND  (  ( start <= '{$starttime}'  AND end >= '{$endtime}')  or (start >= '{$starttime}'  AND start <= '{$endtime}' ) or (end >= '{$starttime}'  AND end <= '{$endtime}' ) ) $condition"); //试听课程
		if(!empty($alltrykc)){
			/************************到访率************************/ 
			
			foreach($alltrykc as $k => $v){
				//var_dump($v['id']."-".$i);

				//获取邀约人数
				$nowkcstu = pdo_fetchall("SELECT distinct sid FROM " . GetTableName('order') . " where kcid = '{$v['id']}'");
				$yystunum += count($nowkcstu);//获取邀约人数
				//当前时间段内总试听人数 +=	当前时间段内试听当前课程的人数 
				$stuliss = pdo_fetchall("SELECT DISTINCT sid FROM " . GetTableName('kcsign') . " where kcid = '{$v['id']}' AND status = 2 AND tid =0 and createtime >= '{$starttime}' AND createtime <= '{$endtime}' ");
				$ststu += count($stuliss);
					//$ststu += pdo_fetchcolumn("SELECT count(DISTINCT sid) FROM " . GetTableName('kcsign') . " where kcid = '{$v['id']}' AND status = 2 AND tid =0 and createtime >= '{$starttime}' AND createtime <= '{$endtime}' ");

				
			}
			$dfl[] = !empty($yystunum) ? round(intval($ststu) / intval($yystunum) * 100,2) : 0; //到访率
			$dfl_F[] = $ststu;
			$dfl_B[] = $yystunum;
			//$dfl[] = $ststu; //到访率


			/************************到访率************************/ 

			/************************成单率************************/ 
			$kcid = arrayToString($alltrykc); //当前时间段内试听课程 转换成字符串
			//签单缴费(当前试听课程学生报名了非当前试听课程统计)
			if(!empty($stuliss)){
				foreach($stuliss as $k1 => $v1){
					//缴费人数
					$bmstu = pdo_fetch("SELECT id FROM " . GetTableName('order') . " where NOT FIND_IN_SET(kcid,'{$kcid}') AND sid = '{$v1}' AND status = 2 ");
					if(!empty($bmstu)){
						$qdjfnum++;
					}
				}
			}
			$cdl[] = !empty($ststu) ? round(intval($qdjfnum) / intval($ststu) * 100,2) : 0;
			$cdl_F[] = $qdjfnum;
			$cdl_B[] = $ststu;
			//$cdl[] = $yystunum;

			/************************成单率************************/ 
		}else{
			$dfl[] = 0;
			$dfl_F[] = 0;
			$dfl_B[] = 0;
			$cdl[] = 0 ;
			$cdl_F[] = 0 ;
			$cdl_B[] = 0 ;
		}
		// var_dump($cdl_F);
		/************************续班率************************/
		//现有总用户
		$allstunum = pdo_fetchcolumn("SELECT count( DISTINCT sid) FROM " . GetTableName('coursebuy') . " where schoolid = '{$schoolid}' AND weid = '{$weid}' ");
		//续班的用户
		$xbstu = pdo_fetchall("SELECT DISTINCT sid FROM " . GetTableName('order') . " where NOT FIND_IN_SET(kcid,'{$kcid}') AND schoolid = '{$schoolid}' AND weid = '{$weid}' and status = 2 AND type = 1 AND sid !=0 AND kcstatus = 1 AND paytime >= '{$starttime}' AND paytime <= '{$endtime}'");
		//续班率
		$xbl[] = !empty($allstunum) ?  round(intval(count($xbstu)) / intval($allstunum) * 100,2) : 0;
		$xbl_F[] = count($xbstu) ? count($xbstu) : 0;
		$xbl_B[] = $allstunum ? $allstunum : 0;
		/************************续班率************************/ 

		/************************消课率************************/ 
		//满勤数量
		$mqnum = 0;
		//获取当前时间段的所有排课 (kcbiao)
		$AllKs = pdo_fetchall("SELECT kcid,id,date,costnum FROM " . GetTableName('kcbiao') . " where NOT FIND_IN_SET(kcid,'{$kcid}') and date >= '{$starttime}' AND date <= '{$endtime}' and schoolid = '{$schoolid}' AND weid = '{$weid}'");
		//去重后的课程并转化为str
		$SJXKNUM = 0 ; //实际消课数
		$AllJHXK = 0 ; //总的计划消课数
		foreach($AllKs as $ks => $vs){
			$CBTable =  GetTableName('coursebuy');
			$KCSTable = GetTableName('kcsign');
			$SQl = <<<EOF

			SELECT 
				cb.ksnum , cb.kcid ,cb.sid,cb.id
			FROM {$CBTable}  as cb 
			where
				cb.kcid = '{$vs['kcid']}'
				AND cb.createtime <= '{$vs['date']}' 
				AND cb.sid != 0
				and schoolid = '{$schoolid}' AND weid = '{$weid}'
			having 
				(cb.ksnum - ifnull((SELECT SUM(costnum) FROM {$KCSTable} where sid = cb.sid and schoolid = '{$schoolid}' AND weid = '{$weid}' and kcid = cb.kcid and status=2 AND createtime <= '{$vs['date']}'),0) >0)
EOF;
			$haskcnum = pdo_fetchall($SQl);
			//去重取出sid并转化为字符串
			$sidstr = arrayToString(array_unique(array_column($haskcnum,'sid')));
			
			//实际消耗课时
			$SJXKNUM += pdo_fetchcolumn("SELECT SUM(costnum) FROM ".GetTableName('kcsign')." WHERE ksid = '{$vs['id']}' and status=2  and schoolid = '{$schoolid}' AND weid = '{$weid}' and sid != 0  ");
			
			//计划消耗课时
			$CanCheckStuCount = count($haskcnum);
			$AllJHXK += $CanCheckStuCount * $vs	['costnum'];
			//查当前课时有多少学生签到  然后和 $CanCheckStuCount 比较， 相等就是满勤 ，满勤数加一 ， 不等就不是
			$nowksstu = pdo_fetchcolumn("SELECT count(*) FROM ".GetTableName('kcsign')." WHERE ksid = '{$vs['id']}' and status=2 AND sid != 0 AND tid = 0 and schoolid = '{$schoolid}' AND weid = '{$weid}'");
			
			if($nowksstu == $CanCheckStuCount && $CanCheckStuCount != 0){
				$mqnum++;
			}	
		}
	
		$xkl[] = !empty($AllJHXK) ? round(intval($SJXKNUM) / intval($AllJHXK) * 100,2) : 0 ;
		$xkl_F[] = $SJXKNUM ? $SJXKNUM : 0;
		$xkl_B[] = $AllJHXK ? $AllJHXK : 0;
		/************************消课率************************/ 
		
		/************************满班率************************/

		$mbl[] = !empty($AllKs) ? round(intval($mqnum) / intval(count($AllKs))  * 100,2) : 0;
		$mbl_F[] = $mqnum ? $mqnum : 0;
		$mbl_B[] = count($AllKs) ? count($AllKs) : 0;
		/************************满班率************************/ 
	}
	// $list = array(
	// 	'0' => $dfl_F,  
	// 	'1' => $dfl_B,  
	// 	'2' => $dfl,  
	// 	'3' => $cdl_F, 
	// 	'4' => $cdl_B, 
	// 	'5' => $cdl, 
	// 	'6' => $xbl_F, 
	// 	'7' => $xbl_B, 
	// 	'8' => $xbl,
	// 	'9' => $xkl_F,
	// 	'10' => $xkl_B,
	// 	'11' => $xkl,
	// 	'12' => $mbl_F,
	// 	'13' => $mbl_B,
	// 	'14' => $mbl
	// );
	$list = array(
		'0' => $dfl,  
		'1' => $cdl, 
		'2' => $xbl, 
		'3' => $xkl, 
		'4' => $mbl
	);
	return $list;

}



//过滤微信昵称中的表情图片
 function filterEmoji($str){
    if(!is_string($str)) return $str;
    if(!$str || $str=='undefined') return '';
    $text = json_encode($str); //暴露出unicode
    $text = preg_replace_callback("/(\\\u[2def][0-9a-f]{3})/i",function($str){
		//return addslashes($str[0]);
		return strlen($str[0]) >= 4 ? '' : $str[0];
    },$text); //将emoji的unicode删除，其他不动，这里的正则比原答案增加了d，因为我发现我很多emoji实际上是\ud开头的，反而暂时没发现有\ue开头。
    return json_decode($text);
}
 
function GetKsInfo($weid,$schoolid,$id){
	$category = pdo_fetchall("SELECT * FROM " . GetTableName('classify') . " WHERE weid = :weid And schoolid = :schoolid ORDER BY sid ASC, ssort DESC", array(':weid' => $weid, ':schoolid' => $schoolid), 'sid');
	/***********************************课程课时列表*************************************/
	$ksid = intval($id);
	$kcbiao = pdo_fetch("SELECT * FROM " . GetTableName('kcbiao') . " where schoolid = '{$schoolid}' And id = '{$ksid}' ");
	$kcid = $kcbiao['kcid'];
	$kc = pdo_fetch("SELECT * FROM ".GetTableName('tcourse')." WHERE schoolid = '{$schoolid}' And id = '{$kcid}'");
	$costnum = pdo_fetchcolumn("SELECT SUM(costnum) FROM ".GetTableName('kcsign')." WHERE schoolid = '{$schoolid}' And ksid = '{$ksid}'");
	$xfje = $costnum*$kc['RePrice'];
	$list = pdo_fetchall("SELECT id,date,sd_id,costnum FROM " . GetTableName('kcbiao') . " where schoolid = '{$schoolid}' And kcid = '{$kcid}' ORDER BY date ASC ");
	foreach($list as $key => $row){
		//获取课程单价
		$checknub = GetOneKcKsOrder($kcid,$row['id']);
		$list[$key]['nuber'] = $checknub['nuber'];
		$list[$key]['dates'] = date('Y.m.d',$row['date']);
	}
	/***********************************签到学生列表数据*************************************/
	$Tdata = [];
	//当前课时开始时间
	$start = strtotime(date("Y-m-d",$kcbiao['date']).' '.date("H:i:s",$category[$kcbiao['sd_id']]['sd_start'])); 
	//当前课时结束时间
	$end = strtotime(date("Y-m-d",$kcbiao['date']).' '.date("H:i:s",$category[$kcbiao['sd_id']]['sd_end']));
	//得到当前时段课时状态
	if($start <= time() && time() <= $end){ //授课中
		$Tdata['status'] = 1;
	}elseif(time()<$start){ //未开始
		$Tdata['status'] = 2;
	}elseif(time()>$end){ //已结束
		$Tdata['status'] = 3;
	}

	//获取主讲老师
	$zhujiang = pdo_fetch("SELECT id,tid FROM " . GetTableName('kcsign') . " where ksid = '{$ksid}' And tid > 0 And ismaster_tid = 1 And status = 2 ");
	if(!empty($zhujiang)){
		$zjtea = pdo_fetch("SELECT tname FROM " . GetTableName('teachers') . " where id = :id ", array(':id' => $zhujiang['tid']));
	}

	// 获取辅导老师
	$fudaotea = '';
	$fudao = pdo_fetchall("SELECT id,tid FROM " . GetTableName('kcsign') . " where ksid = '{$ksid}' And tid > 0 And ismaster_tid = 2 And status = 2 ");
	if(!empty($fudao)){
		foreach($fudao as $r){
			$fdtea = pdo_fetch("SELECT tname FROM " . GetTableName('teachers') . " where id = :id ", array(':id' => $r['tid']));
			if(!empty($fdtea)){
				$fudaotea .= $fdtea['tname'].'/';
			}else{//老师如被删除 则删除此记录
				pdo_delete(GetTableName('kcsign',false),array('id' => $r['id']));
			}
		}
	}
	$fdtea = rtrim($fudaotea,'/');

	//当前课程下的学生总数
	$allstu = pdo_fetchall("SELECT sid FROM " . GetTableName('coursebuy') . " WHERE kcid = '{$kcid}' And is_change != 1 ");
	$allstunub = 0;
	foreach($allstu as $row){
		$checkstu = pdo_fetch("SELECT id FROM " . GetTableName('students') . " WHERE schoolid = '{$schoolid}' And id = '{$row['sid']}'");
		if($checkstu){
			$allstunub++;
		}
	}

	//满班率
	$yskks = pdo_fetchcolumn("SELECT COUNT( sid ) FROM " . GetTableName('kcsign') . " WHERE schoolid = '{$schoolid}' And kcid = '{$kcid}' And ksid = '{$ksid}' And status = 2 And tid = 0 ");
	if($yskks >0){
		$jindu = round($yskks/$allstunub*100,1);
	}else{
		$jindu = 0.1;
	}

	/***********************************签到,请假,旷课,未签数据统计*************************************/
	$SignAll = 0;//所有签到
	$SignStu = 0;//自主签到
	$SignTea = 0;//老师代签
	$SignKqk = 0;//考勤卡签
	$SignQj = 0;//请假
	$SignQk = 0;//旷课
	$buystu = pdo_fetchall("SELECT * FROM " . GetTableName('kcsign') . " WHERE schoolid = '{$schoolid}'  And ksid = '{$ksid}' And kcid = '{$kcid}' AND sid !=0 ");
	foreach ($buystu as $key => $value) {
		if($value['status'] == 2 && $value['signtype'] == 1){ //自主签到
			$SignStu++;
			$SignAll++;
		}
		if($value['status'] == 2 && ($value['signtype'] == 0 || $value['signtype'] == 2)){ //老师代签
			$SignTea++;
			$SignAll++;
		}
		if($value['signtype'] == 3){ //考勤卡签
			$SignKqk++;
			$SignAll++;
		}
		if($value['status'] == 3){ //请假
			$SignQj++;
		}
		if($value['status'] == 0){ //旷课
			$SignQk++;
		}
	}
	$SignNo = intval($allstunub) - intval(count($buystu)); //未签到
	// 学生评价统计
	$stupj = pdo_fetchcolumn("SELECT COUNT(id) FROM " . GetTableName('kcpingjia') . " WHERE schoolid = '{$schoolid}' And is_master = 1 And type = 2 And kcid = '{$kcid}' And ksid = '{$ksid}' AND sid !=0 ");
	// 老师评价统计
	$teapj = pdo_fetchcolumn("SELECT COUNT(id) FROM " . GetTableName('kcpingjia') . " WHERE schoolid = '{$schoolid}' And is_master = 1 And type = 2 And kcid = '{$kcid}' And ksid = '{$ksid}' AND tid !=0 ");

	/***********************************签到,请假,旷课,未签数据统计*************************************/

	/***********************************评价后的平均得分计算********************************************/
	$kspingfen = 0;
	$zf = 0;
	$pftimes = 0;
	$allpf = pdo_fetchall("SELECT star,sid FROM " . GetTableName('kcpingjia') . " WHERE schoolid = '{$schoolid}' And sid > 0 And totid > 0 And ksid = '{$ksid}' And type = 1 And tid = 0 And star >0 ");
	foreach($allpf as $key => $row){
		$stu = pdo_fetch("SELECT id FROM " . GetTableName('students') . " WHERE id = '{$row['sid']}' ");
		if(!empty($stu)){
			$zf = $zf + $row['star'];
			$pftimes++;
		}
	}
	if($pftimes > 0){
		$kspingfen = round($zf/$pftimes,1);
	}
	$Tdata['pktname'] = CheckPkUser($kcbiao['pkuser']);
	/*****************************************************平均得分*****************************************************/
	$Tdata['SignAll'] = $SignAll;
	$Tdata['SignStu'] = $SignStu;
	$Tdata['SignTea'] = $SignTea;
	$Tdata['SignKqk'] = $SignKqk;
	$Tdata['SignQj'] = $SignQj;
	$Tdata['SignQk'] = $SignQk;
	$Tdata['SignNo'] = $SignNo;
	$Tdata['stupj'] = $stupj;
	$Tdata['teapj'] = $teapj;
	$Tdata['kspingfen'] = $kspingfen;
	$Tdata['jindu'] = $jindu;
	$Tdata['zjtea'] = $zjtea['tname'] ? $zjtea['tname'] : '管理员';
	$Tdata['fdtea'] = $fdtea;
	$Tdata['title'] = $kc['name'];
	$Tdata['xfje'] = $xfje;
	$Tdata['checknub'] = GetOneKcKsOrder($kcid,$ksid)['nuber'];;
	$Tdata['addinfo'] = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " where sid = :sid ", array(':sid' => $kcbiao['addr_id']))['sname'];
	$Tdata['date'] = date("Y.m.d",$kcbiao['date']);
	$Tdata['sd_id'] = $category[$kcbiao['sd_id']]['sname'] ;
	$Tdata['day'] = get_weeks($kcbiao['date']);
	return $Tdata;
}
 
function GetStuKcSignList($schoolid,$kcid,$ksid,$page){
	$pindex    = max(1, intval($page));
	$psize     = 10;
	$kc = pdo_fetch("SELECT * FROM ".GetTableName('tcourse')." WHERE schoolid = '{$schoolid}' And id = '{$kcid}'");

	// $list = pdo_fetchall("SELECT * FROM " . GetTableName('kcsign') . " WHERE schoolid = '{$schoolid}'  And ksid = '{$ksid}' And kcid = '{$kcid}' AND sid !=0 AND status = 2 ORDER BY id LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
	$list = pdo_fetchall("SELECT ks.*,pj.star FROM " . GetTableName('kcsign') . " ks LEFT JOIN ". GetTableName('kcpingjia') . " pj on ks.ksid = pj.ksid WHERE ks.schoolid = '{$schoolid}'  And ks.ksid = '{$ksid}' And ks.kcid = '{$kcid}' AND ks.sid !=0 AND ks.status = 2 GROUP BY ks.sid ORDER BY pj.star,ks.id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
	foreach($list as $key =>$row){
		$student = pdo_fetch("SELECT s_name FROM ".GetTableName('students')." WHERE schoolid = '{$schoolid}' AND id = '{$row['sid']}' ");
		$restks = GetRestKsBySid($kcid,$row['sid']);
		if($row['status'] == 2 && $row['signtype'] == 1){
			$allstu[$key]['signtype'] = 1;
			$list[$key]['signtype'] = '自主签到'; 
		}
		if($row['status'] == 2 && ($row['signtype'] == 0 || $row['signtype'] == 2)){
			$allstu[$key]['signtype'] = 2;
			$list[$key]['signtype'] = '老师代签'; 
		}
		if($row['signtype'] == 3){
			$allstu[$key]['signtype'] = 3;
			$list[$key]['signtype'] = '考勤签到'; 
		}
		
		$list[$key]['s_name'] = $student['s_name']; 
		$list[$key]['restks'] = $restks['restnumber']; 
		$list[$key]['kxje'] = $kc['RePrice']*$row['costnum']; 
		$tostu = pdo_fetch("SELECT id FROM " . GetTableName('kcpingjia') . " WHERE schoolid = '{$schoolid}' And tosid = '{$row['sid']}' And ksid = '{$ksid}' ");//老师评学生
		$totea = pdo_fetch("SELECT id FROM " . GetTableName('kcpingjia') . " WHERE schoolid = '{$schoolid}' And totid != 0 And sid = '{$row['sid']}' And ksid = '{$ksid}'  ");//学生评老师
		if(!empty($tostu)){
			$checkpxsstar = pdo_fetch("SELECT star FROM " . GetTableName('kcpingjia') . " WHERE schoolid = '{$schoolid}' And tosid = '{$row['sid']}' And ksid = '{$ksid}' And tid > 0 And type = 1 And is_master = 1");//老师评学生评星
			$list[$key]['startostu'] = getStarStyle($checkpxsstar['star']);
			$list[$key]['tostu'] = true;
		}else{
			$list[$key]['tostu'] = false;
		}
		if(!empty($totea)){
			//计算当前课时老师平均得分
			$allkstea = pdo_fetchAll("SELECT star FROM " . GetTableName('kcpingjia') . " WHERE schoolid = '{$schoolid}' And sid = '{$row['sid']}' And totid != 0 And ksid = '{$ksid}' And type = 1");
			$allksteastar = pdo_fetchcolumn("SELECT SUM(star) FROM " . GetTableName('kcpingjia') . " WHERE schoolid = '{$schoolid}' And sid = '{$row['sid']}' And totid != 0 And ksid = '{$ksid}' And type = 1 Group By sid");
			$star = $allksteastar / count($allkstea);
			$list[$key]['startotea'] = getStarStyle($star);
			$list[$key]['totea'] = true;
		}else{
			$list[$key]['totea'] = false;
		}
	}
	$total = pdo_fetchcolumn("SELECT COUNT(id) FROM " . GetTableName('kcsign') . " WHERE schoolid = '{$schoolid}'  And ksid = '{$ksid}' And kcid = '{$kcid}' AND sid !=0 AND status = 2");
	$pager = paginations($total, $pindex, $psize,'',array('before' => 0, 'after' => 0, 'ajaxcallback' => true, 'callbackfuncname' => 'page_signlist'));
	$result['list'] = $list;
	$result['total'] = $total;
	$result['pager'] = $pager;
	return $result;
}
/**
 * 通过课程和班级去出所有学生(培训)
 */
// function getPxBjStu($schoolid,$kcid,$bjid){
// 	$stulist = pdo_fetchAll("SELECT s.id,s.s_name FROM " . GetTableName('coursebuy') . " c LEFT JOIN " . GetTableName('students') . " s ON s.id = c.sid WHERE c.schoolid = '{$schoolid}' And c.kcid='{$kcid}' AND c.bjid='{$bjid}' ");
// 	$result['data'] = $stulist;
// 	die(json_encode($stulist));
// }

/**
 * 获取指定课程下的正在上课的课时
 */
function getAllKcKs($schoolid,$kcid){
	$nowtime = time();
	$endtime = strtotime(date('Y-m-d')) + 86399;
	$mintime = strtotime(date('Y-m-d'));
	$kcbiao = pdo_fetchall("SELECT c.sd_end,kc.id,kc.addr_id,kc.date FROM " . GetTableName('kcbiao') . " as kc LEFT JOIN " . GetTableName('classify') . " as c ON c.sid = kc.sd_id WHERE kc.schoolid ='{$schoolid}' AND kc.kcid = '{$kcid}' And (kc.date BETWEEN {$mintime} and {$endtime}) ORDER BY kc.date ASC");
	$nowks = [];
	foreach ($kcbiao as $key => $value) {
		$hours = strtotime(date("H:i:s",$value['sd_end']));
		$kc = pdo_fetch("SELECT signTime,name FROM ".GetTableName('tcourse')." WHERE id = '{$kcid}' AND schoolid = '{$schoolid}' ");
		$kc = pdo_fetch("SELECT c.video,t.signTime,t.name FROM ".GetTableName('tcourse')." as t LEFT JOIN " . GetTableName('classify') . " as c ON t.adrr = c.sid WHERE t.id = '{$kcid}' AND t.schoolid = '{$schoolid}'");
		$jstime = time() + $kc['signTime']*60; //开始计算时间
		if($hours > $nowtime && $value['date'] <= $jstime){
			$nowks = $value;
			$checknubs = GetOneKcKsOrder($kcid,$value['id']);
			$nowks['nuber'] = $checknubs['nuber'];
			$nowks['videourl'] = $kc['video'];
			$nowks['kcname'] = $kc['name'];
		}
	}
	return $nowks;
}
/**
 * 通过教室ID判断是否当前教室在上课
 */
function getJsIsOnline($schoolid,$jsid){
	$starttime = strtotime(date("Y-m-d",time()));
	$endtime = strtotime(date("Y-m-d",time())) +86399;
	// $nowTime = date("Hi",time());
	$nowTime = '0920';
	$kcbiao = pdo_fetch("SELECT k.sd_id,k.kcid,k.id,from_unixtime(c.sd_start, '%H%i') as start,from_unixtime(c.sd_end, '%H%i') as end,c.sname, c.sd_start, c.sd_end FROM " . GetTableName('kcbiao') . " as k LEFT JOIN " . GetTableName('classify') . " as c ON c.sid = k.sd_id WHERE k.schoolid = '{$schoolid}' AND k.addr_id = '{$jsid}' AND k.date BETWEEN $starttime AND $endtime HAVING $nowTime >= start AND $nowTime <= end");
	return $kcbiao;
}
//通过年级ID获取班级列表
function getBjList($schoolid,$nj_id){
	$bjlist = pdo_fetchall("SELECT * FROM " . GetTableName('classify') . " where schoolid = '{$schoolid}' And parentid = '{$nj_id}' And type = 'theclass' and is_over != 2 ORDER BY ssort DESC");
	return $bjlist;
}
?>