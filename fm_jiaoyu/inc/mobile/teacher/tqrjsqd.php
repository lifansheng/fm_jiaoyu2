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
    //查询是否用户登录		
	$it = pdo_fetch("SELECT * FROM " . GetTableName('user') . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0));
	$myteainfo = pdo_fetch("SELECT status,tname FROM ".GetTableName('teachers')." WHERE  id = '{$it['tid']}'  ");
	$school = pdo_fetch("SELECT title,style3,tpic FROM " . GetTableName('index') . " where id=:id ", array(':id' => $schoolid));	
	$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
	if(!empty($it)){
		$nowtime = time();
		if ($operation == 'quren') {
			$kcsignid = $_GPC['id'];
			$sign = pdo_fetch("select * FROM ".GetTableName('kcsign')." WHERE  id = '{$kcsignid}' ");
			if(empty($sign)){
				$result['result']	= false;
				$result['msg']	= '该签到记录不存在,或已被删除!';
			}else{
				if($sign['status'] == 2){
					$result['result']	= false;
					$result['msg']	= '抱歉,该记录已经被其他老师审核,请刷新本页!';
				}
				if($sign['status'] == 1){
					$allsign = false;
					if($sign['ismaster_tid'] == 1){
						$checkmaster = pdo_fetch("SELECT id,tid,createtime FROM ".GetTableName('kcsign')." WHERE  ksid = {$sign['ksid']}  And tid >0 And ismaster_tid = 1 And status  = 2 ");
						if(empty($checkmaster)){
							$allsign = true;
						}else{
							$alsigntime = date('y/m/d H:i',$checkmaster['createtime']);
							$masteainfo = pdo_fetch("SELECT tname FROM ".GetTableName('teachers')." WHERE  id = {$checkmaster['tid']} ");
							$result['result']	= false;
							$result['msg']	= '抱歉,本节已由'.$masteainfo['tname'].'于'.$alsigntime.'签到主讲身份,如需继续签到,请拒绝本次签到,并告知老师签到助教身份!';
						}
					}else{
						$allsign = true;
					}
					if($allsign){
						pdo_update(GetTableName('kcsign',false),array('status'=>2,'signtime'=>$nowtime,'qrtid'=>$it['tid']),array('id'=>$sign['id']));
						if($sign['ismaster_tid'] == 1){
							pdo_update(GetTableName('kcbiao',false),array('tid'=>$sign['tid']),array('id'=>$sign['ksid']));
						}
						$this->sendMobileQrjsqdtz($sign['id'],$schoolid,$weid);
						$result['result']	= true;
						$result['msg']	= '确认签到成功!';
					}
				}
			}
			die(json_encode($result));
		}
		if ($operation == 'jujue') {
			$kcsignid = $_GPC['id'];
			$sign = pdo_fetch("select * FROM ".GetTableName('kcsign')." WHERE  id = '{$kcsignid}' ");
			if(empty($sign)){
				$result['result']	= false;
				$result['msg']	= '该签到记录不存在,或已被删除!';
			}else{
				if($sign['status'] == 2){
					$alsigntime = date('y/m/d H:i',$sign['signtime']);
					$shtea = pdo_fetch("SELECT tname FROM ".GetTableName('teachers')." WHERE  id = {$sign['qrtid']} ");
					$result['result']	= false;
					$result['msg']	= '抱歉,该记录已由'.$shtea['tname'].'于'.$alsigntime.'审核,请刷新本页!';
				}
				if($sign['status'] == 1){
					pdo_delete(GetTableName('kcsign',false),array('id'=>$sign['id']));
					$result['result']	= true;
					$result['msg']	= '已拒绝,本条记录已删除!';
				}
			}
			die(json_encode($result));
		}
		if($operation == 'sign_list'){
			$w = date('w')? date('w') : 7;
			$nowweekstart = mktime(0,0,0,date('m'),date('d')-$w+1,date('Y')) + $_GPC['dtweek'] * 7 * 86400;
			$nowweekend = mktime(0,0,0,date('m'),date('d')-$w+8,date('Y')) + $_GPC['dtweek'] * 7 * 86400;
			$condtion1  = " And createtime > '{$nowweekstart}'  And createtime < '{$nowweekend}' ";
			$condtion  = '';
			if($myteainfo['status'] == 3){//校长取全校所有课未结束课程
				$condtion  = '';
			}else{
				$allkc = pdo_fetchall("SELECT kcid FROM " .  GetTableName('kc_signset')  . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}'  And FIND_IN_SET('{$it['tid']}',sh_tea_teacherids) ");
				$Kclist = '';	
				foreach($allkc as $key=> $row){
					$thiskc = pdo_fetch("SELECT name FROM ".GetTableName('tcourse')." WHERE  id= '{$row['kcid']}'  ");
					if(!empty($thiskc)){
						$Kclist .= $row['kcid'].',';
					}else{
						unset($allkc[$key]);
						continue;
					}
				}
				$KclistStr = trim($Kclist,',');
				$condtion .= " And FIND_IN_SET(kcid,'{$KclistStr}')";
			}
			$dqrnubs = 0;
			$signlist = pdo_fetchall("SELECT * FROM ".GetTableName('kcsign')." WHERE  weid = '{$weid}' And schoolid = '{$schoolid}' And tid >0 $condtion  $condtion1 ORDER BY createtime DESC,status ASC");
			foreach($signlist as $key=> $row){
				$teainfo = pdo_fetch("SELECT thumb,tname FROM ".GetTableName('teachers')." WHERE  id = {$row['tid']} ");
				$kcinfo = pdo_fetch("SELECT name,signTime FROM ".GetTableName('tcourse')." WHERE  id = {$row['kcid']} ");
				$ksinfo = pdo_fetch("SELECT sd_id,addr_id,date FROM ".GetTableName('kcbiao')." WHERE  id = {$row['ksid']}  ");
				$sdinfo = pdo_fetch("SELECT sd_start,sd_end FROM ".GetTableName('classify')." WHERE  sid = {$ksinfo['sd_id']}  ");
				$addinfo = pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE  sid = {$ksinfo['addr_id']}  ");
				$ksstart1 = strtotime(date('Y-m-d',$ksinfo['date']).date(' H:i',$sdinfo['sd_start']));
				$ksstart = strtotime(date('Y-m-d',$ksinfo['date']).date(' H:i',$sdinfo['sd_start'])) - $kcinfo['signTime']*60;
				$ksend = strtotime(date('Y-m-d',$ksinfo['date']).date(' H:i',$sdinfo['sd_end']));
				$ksorder = GetOneKcKsOrder($row['kcid'],$row['ksid']);
				$signlist[$key]['ksname'] = '第'.$ksorder['nuber'].'课';
				$signlist[$key]['kcname'] = $kcinfo['name'];
				$signlist[$key]['sd_id'] = $ksinfo['sd_id'];
				$signlist[$key]['tname'] = $teainfo['tname'];
				$signlist[$key]['sdname'] = date('y/m/d H:i',$ksstart1).'-'.date('H:i',$ksend);//本课上课日期
				$signlist[$key]['addr'] = $addinfo['sname'];
				$signlist[$key]['time'] = date('Y.m.d H:i',$row['createtime']);
				$signlist[$key]['icon'] = !empty($teainfo['thumb'])?tomedia($teainfo['thumb']):tomedia($school['tpic']);
				$signlist[$key]['tiqian'] = false; 
				$signlist[$key]['buqian'] = false; 
				if($row['createtime'] < $ksstart){
					$signlist[$key]['tiqian'] = true; 
				}
				if($row['createtime'] > $ksend){
					$signlist[$key]['buqian'] = true; 
				}
				if($row['status'] ==1){
					$dqrnubs++;
				}
				if($row['status'] ==2){
					$qrteainfo = pdo_fetch("SELECT tname FROM ".GetTableName('teachers')." WHERE  id = '{$row['qrtid']}'  ");
					$signlist[$key]['qrtname'] = $qrteainfo['tname'];
				}
			}
			// print_r($signlist);
			include $this->template(''.$school['style3'].'/kc/kctable_temp');
		}
		if($operation == 'week_header'){
			$w = date('w')? date('w') : 7;
			$nowweekstart = mktime(0,0,0,date('m'),date('d')-$w+1,date('Y')) + $_GPC['dtweek'] * 7 * 86400;
			$nowweekend = mktime(0,0,0,date('m'),date('d')-$w+8,date('Y')) + $_GPC['dtweek'] * 7 * 86400;
			if($_GPC['dtweek'] != 0){
				$result['tiptitle'] = '回本周';
			}else{
				$result['tiptitle'] = '本周';
			}
			$result['tiptime'] = date('Y.n.j',$nowweekstart).'-'.date('n.j',$nowweekend);
			die(json_encode($result));
		}
		if($operation == 'display'){
			if($myteainfo['status'] == 2){//校长取全校所有课未结束课程
				$allkc = pdo_fetchall("SELECT id,name FROM " .  GetTableName('tcourse')  . " WHERE weid = '{$weid}' And kc_type = 0 And schoolid = '{$schoolid}' And end > '{$nowtime}' ORDER BY id DESC");
			}else{
				$allkc = pdo_fetchall("SELECT kcid FROM " .  GetTableName('kc_signset')  . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}'  And FIND_IN_SET('{$it['tid']}',sh_tea_teacherids) ");
				foreach($allkc as $key=> $row){
					$thiskc = pdo_fetch("SELECT name FROM ".GetTableName('tcourse')." WHERE  id= '{$row['kcid']}' And end > '{$nowtime}' ");
					if(!empty($thiskc)){
						$allkc[$key]['id'] = $row['kcid'];
						$allkc[$key]['name'] = $thiskc['name'];
					}else{
						unset($allkc[$key]);
					}
				}
			}
			$timeframe = pdo_fetchall("SELECT * FROM " .  GetTableName('classify')  . " WHERE weid = '{$weid}' And type = 'timeframe' And schoolid = '{$schoolid}' ORDER BY ssort DESC, sid ASC");
			include $this->template(''.$school['style3'].'/tqrjsqd');
		}	
	}else{
		session_destroy();
	    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
		header("location:$stopurl");
		exit;
    }       
?>