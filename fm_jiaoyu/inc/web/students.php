<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_GPC, $_W;
$weid              = $_W['uniacid'];
$action            = 'students';
$this1             = 'no2';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
$schooltype         = $_W['schooltype'];
$school            = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where id = :id ", array(':id' => $schoolid));
$logo              = pdo_fetch("SELECT logo,title,is_stuewcode,spic FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");			
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$tid_global = $_W['tid'];
mload()->model('znl');
mload()->model('xzf');
mload()->model('stu');
if($operation == 'post'){
	if (!(IsHasQx($tid_global,1000702,1,$schoolid))){
		$result['result'] = false;
		$result['msg'] = "非法访问，您无权操作该页面";
		die(json_encode($result));
	}
	load()->func('tpl');
	$id = intval($_GPC['id']);

	mload()->model('sell');
	$allselltea = GetAllSellTea($schoolid,$weid,0);
	if(!empty($id)){
		$item = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " WHERE id = :id", array(':id' => $id));
		if(empty($item)){
			$result['result'] = false;
			$result['msg'] = "抱歉，学生不存在或是已经删除";
			die(json_encode($result));
		}else{
			if(!empty($item['thumb_url'])){
				$item['thumbArr'] = explode('|', $item['thumb_url']);
			}
			if(!empty($item['sellteaid'])){
				$item['sellteaname'] =  pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " WHERE id = :id", array(':id' => $item['sellteaid']))['tname'];
			}
		}
	}
	$xueqi             = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid And schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'semester', ':schoolid' => $schoolid));
	$bj                = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid And schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'theclass', ':schoolid' => $schoolid));
	if($item['code'] == 0){
		$rAndStr = str_shuffle('123456789');
		$rAnd    = substr($rAndStr, 0, 6);
	}else{
		$rAnd = $item['code'];
	}
	if(!empty($_GPC['code'])){
		$rAnd = $_GPC['code'];
	}
	
	if(keep_Blacklist()){
		$hasnum = pdo_fetch("SELECT id FROM " . tablename($this->table_students) . " WHERE numberid = :numberid AND id != :id", array(':numberid' => $_GPC['numberid'],':id'=>$item['id']));
		if($hasnum){
			$result['result'] = false;
			$result['msg'] = "抱歉，学号'{$_GPC['numberid']}'已存在，请重新填写！";
			die(json_encode($result));
		}
	}
	$data  = array(
		'weid'           => $weid,
		'schoolid'       => $schoolid,
		's_name'         => trim($_GPC['s_name']),
		'icon'           => trim($_GPC['icon']),
		'sex'            => intval($_GPC['sex']),
		'is_banzhang'    => intval($_GPC['is_banzhang']),
		's_type'         => !empty($_GPC['s_type'])?intval($_GPC['s_type']):1,
		'bj_id'          => trim($_GPC['bj']),
		'xq_id'          => trim($_GPC['xueqi']),
		'numberid'       => trim($_GPC['numberid']),
		'birthdate'      => strtotime($_GPC['birthdate']),
		'homephone'      => trim($_GPC['tel']),
		'mobile'         => trim($_GPC['mobile']),
		'area_addr'      => trim($_GPC['addr']),
		'seffectivetime' => strtotime($_GPC['seffectivetime']),
		'stheendtime'    => strtotime($_GPC['stheendtime']),
		'note'           => trim($_GPC['note']),
		'code'           => $rAnd,
		'status'         => $_GPC['status'],
	);
	if(!empty($_GPC['status'])){
		$data['status'] = $_GPC['status'];
	}
	if(keep_sk77()){
		$data['sellteaid'] = $_GPC['jsid'];
	}	

	if(CheckXZF($schoolid)){
		$data['identitycard'] = $_GPC['identitycard'];
	}
	$check = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " WHERE s_name = :s_name And mobile = :mobile And schoolid = :schoolid", array(':s_name' => $_GPC['s_name'], ':mobile' => $_GPC['mobile'], ':schoolid' => $schoolid));
	if(!empty($data['s_name'])){
		if(ischeckName($data['s_name']) == false){
			$result['result'] = false;
			$result['msg'] = "禁止使用'测试、test'等作为学生姓名";
			die(json_encode($result));
		}
	}				
	
	if(empty($id)){
		if(!empty($check)){
			$result['result'] = false;
			$result['msg'] = '录入失败，您输入的学生信息有重复，检查手机号和名字是否同时重复！';
			die(json_encode($result));
		}
		if($logo['is_stuewcode'] ==1){ //写入学生之前先判断头像是否设置
			if(empty($_GPC['icon'])){
				if(empty($logo['spic'])){
					$result['result'] = false;
					$result['msg'] = '抱歉,本校开启了用户二维码功能,请上传学生头像或设置校园默认学生头像';
					die(json_encode($result));
				}
			}
		}
		pdo_insert($this->table_students, $data);
		$keysid = pdo_insertid();
		//同步学生接口操作
		if(keep_MC()){
			znlSingleStuInfo($keysid,115);
		}
		$result['keysid'] = $keysid;
		if(keep_Blacklist()){
			pdo_update(GetTableName('students',false),array('numberid'=>$keysid),array('id'=>$keysid));
		}	
		if($logo['is_stuewcode'] ==1){
			load()->func('tpl');
			load()->func('file');
			$barcode = array(
				'expire_seconds' =>2592000,
				'action_name' => '',
				'action_info' => array(
					'scene' => array(
						'scene_id' => $keysid
					),
				),
			);
			$uniacccount = WeAccount::create($wwwweid);
			$barcode['action_name'] = 'QR_SCENE';
			$result = $uniacccount->barCodeCreateDisposable($barcode);
			if (is_error($result)) {
				message($result['message'], referer(), 'fail');
			}
			if (!is_error($result)) {
				$showurl = $this->createImageUrlCenterForUser("https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=" . $result['ticket'], $keysid, 0, $schoolid);
				$urlarr = explode('/',$showurl);
				$qrurls = "images/fm_jiaoyu/".$urlarr['4'];	
				$insert = array(
					'weid' => $weid, 
					'schoolid' => $schoolid,
					'qrcid' => $keysid, 
					'name' => '用户绑定临时二维码', 
					'model' => 1,
					'qr_url' => ltrim($result['url'],"http://weixin.qq.com/q/"),
					'ticket' => $result['ticket'], 
					'show_url' => $qrurls,
					'expire' => $result['expire_seconds'] + time(), 
					'createtime' => time(),
					'status' => '1',
					'type' => '3'
				);
				pdo_insert($this->table_qrinfo, $insert);
				$qrid = pdo_insertid();
				$qrurl = pdo_fetch("SELECT show_url FROM " . tablename($this->table_qrinfo) . " WHERE id = '{$qrid}'");
				$arr = explode('/',$qrurl['show_url']);
				$pathname = "images/fm_jiaoyu/".$arr['2'];
				if (!empty($_W['setting']['remote']['type'])) {
					$remotestatus = file_remote_upload($pathname);
						if (is_error($remotestatus)) {
							$result['result'] = false;
							$result['msg'] = '远程附件上传失败，'.$pathname.'请检查配置并重新上传';
							die(json_encode($result));
						}
				}
			}
		}					
		$temp1 = array(
			'keyid' => $keysid,
			'qrcode_id' => $qrid
		);
		pdo_update($this->table_students, $temp1, array('id' =>$keysid)); 
	}else{
		//修改视频画面到对应的班级
		ChangeVideoWithBj($schoolid,$id,$_GPC['bj']);
		if(!$_W['schooltype']){
			$checkcard = pdo_fetch("SELECT * FROM " . tablename($this->table_idcard) . " WHERE sid = :sid", array(':sid' => $id));
			if($checkcard){
				pdo_update($this->table_idcard, array('bj_id' => trim($_GPC['bj'])), array('sid' => $id));
				pdo_update(GetTableName('schoolset',false), array('top'=>1), array('schoolid' => $schoolid, 'weid'=>$weid));
			}
		}
		
		// $data['numberid'] = $id;
		$data['keyid'] = $id;
		$checkOldSname = pdo_fetch("SELECT s_name FROM ".GetTableName('students')." WHERE schoolid = '{$schoolid}' and id = '{$id}' ");
		if($item['s_name'] != $data['s_name'] || $item['bj_id'] != $data['bj_id'] || $item['identitycard'] != $data['identitycard'] || $item['mobile'] != $data['mobile']){
			setXzfNeedsync($id,'students');
		}
		pdo_update($this->table_students, $data, array('id' => $id));
		//同步学生接口操作
		if(keep_MC()){
			znlSingleStuInfo($id,117);
		}

		//讯贞触发
		$checkSC = pdo_fetch("SELECT idcard FROM ".GetTableName('idcard')." WHERE schoolid = '{$schoolid}' and sid = '{$id}' ");
		if(!empty($checkSC) &&  $checkOldSname['s_name'] != $data['s_name'] ){
			//学生有卡，且名字变了
			xzTriggerCommon($schoolid,$checkSC['idcard'],'update');
		}
			
		$primary = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " WHERE id=:sid And schoolid = :schoolid", array(':sid' => $id,':schoolid' => $schoolid));
		array_splice($primary,0,1);
		$before_sid_arr = pdo_fetchall("SELECT id FROM " . tablename($this->table_students) . " WHERE keyid=:keyid And schoolid = :schoolid", array(':keyid' => $id,':schoolid' => $schoolid));
		$LenOfBSid =  count($before_sid_arr,COUNT_NORMAL);
		if($LenOfBSid >1 && empty($_GPC['sid_before']))
		{
			foreach( $before_sid_arr as $key => $value )
			{
				if($value['id'] != $id )
				{
					//echo "删除的sid:".$value['id']."\n";
				pdo_delete($this->table_students,array('id' =>$value['id']));
				$checkUser = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " WHERE sid=:sid And schoolid = :schoolid", array(':sid' => $value['id'],':schoolid' => $schoolid));
				if($checkUser)
				{
					pdo_delete($this->table_user,array('sid' =>$value['id']));
						//echo "如果 user表里已绑定1，还要删除sid在user表里的数据\n";
				}	
				}
			}
		}
		if(!empty($_GPC['sid_before'])){
			if(in_array($_GPC['bj'],$_GPC['bj_before'] )){
				$result['result'] = false;
				$result['msg'] = '修改失败，修改后的班级有重复！';
				die(json_encode($result));
			}
			$bj_before_arr = array();
			foreach( $_GPC['sid_before'] as $key => $value ){
				if(!empty($_GPC['new'])){
					if(in_array($_GPC['bj_before'][$key], $_GPC['bj_new'] )){
						$result['result'] = false;
						$result['msg'] = '修改失败，修改后的班级有重复！';
						die(json_encode($result));
					}
				}
				$bj_before_arr[$value]['bj_id'] = $_GPC['bj_before'][$key];
				$bj_before_arr[$value]['xq_id'] = $_GPC['xueqi_before'][$key];
				
			}
			foreach( $before_sid_arr as $key => $value ){
				if(in_array($value['id'],$_GPC['sid_before']) ){
					$primaryThis = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " WHERE id=:sid And schoolid = :schoolid", array(':sid' => $value['id'],':schoolid' => $schoolid));
					
					$primaryThis['bj_id'] = $bj_before_arr[$value['id']]['bj_id'];
					$primaryThis['xq_id'] = $bj_before_arr[$value['id']]['xq_id'];
					$primaryThis['s_name'] = trim($_GPC['s_name']);
					$primaryThis['icon'] = trim($_GPC['icon']);
					$primaryThis['sex'] = trim($_GPC['sex']);
					$primaryThis['numberid'] = trim($_GPC['numberid']);
					$primaryThis['birthdate'] = strtotime($_GPC['birthdate']);
					$primaryThis['homephone'] = trim($_GPC['tel']);
					$primaryThis['mobile'] = trim($_GPC['mobile']);
					$primaryThis['area_addr'] = trim($_GPC['addr']);
					$primaryThis['seffectivetime'] = strtotime($_GPC['seffectivetime']);
					$primaryThis['stheendtime'] = strtotime($_GPC['stheendtime']);
					$primaryThis['note'] = trim($_GPC['note']);
					$primaryThis['code'] = $rAnd;
					array_splice($primaryThis,0,1);
					//echo "修改的sid:".$value['id']."\n";
					
					pdo_update($this->table_students,$primaryThis,array('id'=>$value['id']));
					
				}elseif($value['id'] == $id){
					
					
				}else{
					//echo "删除的sid:".$value['id']."\n";
					pdo_delete($this->table_students,array('id' =>$value['id']));
					DeleteStudent($value['id']);
					$checkUser = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " WHERE sid=:sid And schoolid = :schoolid", array(':sid' => $value['id'],':schoolid' => $schoolid));
					if($checkUser){
						pdo_delete($this->table_user,array('sid' =>$value['id']));
							//echo "如果 user表里已绑定1，还要删除sid在user表里的数据\n";
					}	
					
				}
			}
		}
	}
	$result['keysid'] = $keysid;
	$result['result'] = true;
	$result['msg'] = '操作学生信息成功！';
	die(json_encode($result));
}elseif($operation == 'stu_info'){
	if (!(IsHasQx($tid_global,1000702,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面','','error');
	}
	load()->func('tpl');
	$id = intval($_GPC['id']);

	mload()->model('sell');
	$allselltea = GetAllSellTea($schoolid,$weid,0);
	if(!empty($id)){
		$item = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " WHERE id = :id", array(':id' => $id));
		if($item['keyid'] != '0' )
		{
			$other = pdo_fetchall("SELECT * FROM " . tablename($this->table_students) . " WHERE keyid = :id", array(':id' => $item['keyid']));
			foreach( $other as $key => $value )
			{
				if($value['keyid'] != $value['id']){
				$item['all'][] = array(
					'xq_id' => $value['xq_id'],
					'bj_id' => $value['bj_id'],
					'sid'   => $value['id']

				);
			}
			}
		}
		
		if(empty($item)){
			$this->imessage('抱歉，学生不存在或是已经删除1！', '', 'error');
		}else{
			if(!empty($item['thumb_url'])){
				$item['thumbArr'] = explode('|', $item['thumb_url']);
			}
			if(!empty($item['sellteaid'])){
				$item['sellteaname'] =  pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " WHERE id = :id", array(':id' => $item['sellteaid']))['tname'];
			}
		}
	}
	$xueqi = pdo_fetchall("SELECT sid,sname FROM " . GetTableName('classify') . " where weid = :weid And schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'semester', ':schoolid' => $schoolid));
	$bj    = pdo_fetchall("SELECT sid,sname FROM " . GetTableName('classify')  . " where weid = :weid And schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'theclass', ':schoolid' => $schoolid));
	include $this->template('web/stu/edite');
	die();
}elseif($operation == 'stu_bdinfo'){
	if (!(IsHasQx($tid_global,1000707,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面','','error');
	}
	$stuinfo = pdo_fetch("SELECT id,s_name,createdate FROM " . GetTableName('students') . " where schoolid = '{$schoolid}' And id = {$_GPC['id']} ");
	$fanslist =  pdo_fetchall("SELECT * FROM " . GetTableName('user') . " where schoolid = '{$schoolid}' And sid = {$_GPC['id']}  And tid = 0 ");
	if(!empty($fanslist)){
		foreach($fanslist as $key => $val){
			$fansinfo = GetWeFans($weid,$val['openid']);
			$fanslist[$key]['guanxi'] = get_guanxi($val['pard']);
			$fanslist[$key]['nickname'] = $fansinfo['nickname']?$fansinfo['nickname']:$stuinfo['s_name'].$fanslist[$key]['guanxi'];
			$fanslist[$key]['avatar'] = $fansinfo['avatar']?tomedia($fansinfo['avatar']):tomedia($school['spic']);
		}
		$list[$key]['wxbdrs'] = count($fanslist);
	}
	include $this->template('web/stu/wxbdinfo');
	die();	
}elseif($operation == 'stu_cjinfo'){
	if (!(IsHasQx($tid_global,1000706,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面','','error');
	}
	$optype = $_GPC['optype'];
	$stuinfo = pdo_fetch("SELECT id,s_name FROM " . GetTableName('students') . " where schoolid = '{$schoolid}' And id = {$_GPC['id']} ");
	$cjlist =  pdo_fetchall("SELECT * FROM " . GetTableName('score') . " where schoolid = '{$schoolid}' And sid = {$_GPC['id']} ORDER BY createtime DESC ");
	if(!empty($cjlist)){
		foreach($cjlist as $key => $val){
			$qhinfo = pdo_fetch("SELECT sname,is_show_qh FROM " . GetTableName('classify') . " where sid = {$val['qh_id']} ");
			if($qhinfo['is_show_qh'] == 1){
				$kminfo = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " where sid = {$val['km_id']} ");
				$cjlist[$key]['qhname'] = $qhinfo['sname'];
				$cjlist[$key]['kmname'] = $kminfo['sname'];
				$cjlist[$key]['tname'] = '';
				if($val['tid']){
					if(is_numeric($val['tid'])){
						$tinfo = pdo_fetch("SELECT tname FROM " . GetTableName('teachers') . " where id = {$val['tid']} ");
						if($tinfo['tname']){
							$cjlist[$key]['tname'] = $tinfo['tname'];
						}
					}else{
						$cjlist[$key]['tname'] = '管理员';
					}
				}
			}else{
				unset($cjlist[$key]);
			}
		}
	}
	$allkm   = pdo_fetchall("SELECT sid,sname FROM " . GetTableName('classify') . " where schoolid = '{$schoolid}' And type = 'subject' ORDER BY ssort DESC");
	$allqh = get_myqh($_GPC['id'],$schoolid);
	include $this->template('web/stu/addcj');
	die();
}elseif($operation == 'addcj'){
	$sid = intval($_GPC['sid']);
	$stuinfo = pdo_fetch("SELECT bj_id,xq_id FROM " . GetTableName('students') . " where schoolid = '{$schoolid}' And id = {$sid} ");
	if(!empty($_GPC['qh'])){
		$data = array(
			'weid'     => $weid,
			'schoolid' => $schoolid,
			'sid'      => $sid,
			'bj_id'    => $stuinfo['bj_id'],
			'xq_id'    => $stuinfo['xq_id'],
			'tid'      => $tid_global,
			'createtime' => time()
		);
		$suc = 0;$def = 0;$rep=0;
		foreach($_GPC['qh'] as $key => $name){
			$checkcj = pdo_fetch("SELECT bj_id,xq_id FROM " . GetTableName('score') . " where schoolid = '{$schoolid}' And sid = {$sid} And qh_id = {$_GPC['qh'][$key]} And km_id = {$_GPC['km'][$key]} ");
			if(empty($checkcj)){
				$data['qh_id'] = $_GPC['qh'][$key];
				$data['km_id'] = $_GPC['km'][$key];
				$data['my_score'] = $_GPC['score'][$key];
				$data['info'] = $_GPC['info'][$key];
				if(!empty($_GPC['qh'][$key]) && !empty($_GPC['km'][$key]) && !empty($_GPC['score'][$key])){
					pdo_insert(GetTableName('score',false), $data);
					$suc++;
				}else{
					$def++;
				}
			}else{
				$def++;$rep++;
			}
		}
		if($def>0){
			if($rep>0){
				$msg = "成功添加".$suc."条,失败".$def."条,重复数据".$rep."条,请确保每条数据设置了期号、科目、成绩值";
			}else{
				$msg = "成功添加".$suc."条,失败".$def."条,请确保每条数据设置了期号、科目、成绩值";
			}
		}else{
			$msg = "成功添加".$suc."条成绩数据";
		}
		$result['msg'] = $msg;
		$result['result'] = true;
	}else{
		$result['result'] = false;
		$result['msg'] = '请输入成绩数据';
	}
	if($suc == 0){
		$result['result'] = false;
	}
	die(json_encode($result));
}elseif($operation == 'del_cj'){
	pdo_delete(GetTableName('score',false), array('id' => $_GPC['id']));
	$result['result'] = true;
	$result['msg'] = '本条成绩删除成功';
	die(json_encode($result));
}elseif($operation == 'changebjdata'){
		$oldbjdata = pdo_fetchall("SELECT id,bj_id FROM " . tablename($this->table_students) . " WHERE weid = :weid And schoolid = :schoolid ORDER BY id DESC");
		foreach($oldbjdata as $index => $row){
			if($row['bj_id']){
				$data1 = array(
					'weid'     => $weid,
					'schoolid' => $schoolid,
					'sid'      => $row['id'],
					'bj_id'    => $row['bj_id'],
					'type'     => 2,
				);
				pdo_insert($this->table_class, $data1);
			}					
		}
		$this->imessage('操作成功', $this->createWebUrl('students', array('op' => 'display', 'schoolid' => $schoolid)), 'success');			
}elseif($operation == 'display'){
	if (!(IsHasQx($tid_global,1000701,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面','','error');	
	}
	mload()->model('stu');
	//过期班级
	$overBj = getOverClass($schoolid,$schooltype);
	$xueqi             = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid And schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'semester', ':schoolid' => $schoolid));			
	$allbj             = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid And schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'theclass', ':schoolid' => $schoolid));
	mload()->model('tea');
	$allkclist = GetAllClassInfoByTid($schoolid,2,$schooltype,$tid_global);
	$pindex    = max(1, intval($_GPC['page']));
	$psize     = 15;
	$condition = '';
	if(!empty($_GPC['one_sid'])){ //只取一行数据
		$condition .= " And id = '{$_GPC['one_sid']}'";
		$condition1 .= " And s.id = '{$_GPC['one_sid']}'";
	}
	if(!empty($_GPC['keyword'])){
		$condition .= " And s_name LIKE '%{$_GPC['keyword']}%'";
		$condition1 .= " And s.s_name LIKE '%{$_GPC['keyword']}%'";
	}
	if(!empty($_GPC['bd_type'])){
		if($_GPC['bd_type'] == 1){
			$condition .= " And (ouserid != '0' Or muserid != '0' Or duserid != '0' Or otheruserid != '0')";
			$condition1 .= " And (s.ouserid != '0' Or s.muserid != '0' Or s.duserid != '0' Or s.otheruserid != '0')";
		}
		if($_GPC['bd_type'] == 2){
			$condition .= " And ouserid = '0' And muserid = '0' And duserid = '0' And otheruserid = '0' AND own = '0'";
			$condition1 .= " And s.ouserid = '0' And s.muserid = '0' And s.duserid = '0' And s.otheruserid = '0' AND s.own = '0'";
		}				
	}
	if($_GPC['status'] != -1){
		$condition .= " And status = '{$_GPC['status']}'";
		$condition1 .= " And s.status = '{$_GPC['status']}'";
	}
	
	if(!empty($_GPC['nj_id'])){
		$condition .= " And xq_id = '{$_GPC['nj_id']}'";
		$condition1 .= " And s.xq_id = '{$_GPC['nj_id']}'";
	}
	if(!empty($_GPC['s_type'])){
		$condition .= " And s_type = '{$_GPC['s_type']}'";
		$condition1 .= " And s.s_type = '{$_GPC['s_type']}'";
	}
	if(!empty($_GPC['bj_id'])){
		$condition .= " And bj_id = '{$_GPC['bj_id']}'";
		$condition1 .= " And s.bj_id = '{$_GPC['bj_id']}'";
	}
	if(!empty($_GPC['kc_id'])){
		$allbuysid = pdo_fetchall("SELECT distinct sid FROM ".tablename($this->table_order)." where kcid = '{$_GPC['kc_id']}' And type = 1 And status = 2 And sid != 0 ");
		$sidlist = '';
		foreach($allbuysid as $vas){
			$sidlist .= $vas['sid'].",";
		}
		$sidlist = rtrim($sidlist,',');
		$condition .= " AND FIND_IN_SET(id,'{$sidlist}') ";
		$condition1 .= " AND FIND_IN_SET(s.id,'{$sidlist}') ";
	} 
	$checkbjold = pdo_fetch("SELECT * FROM " . tablename($this->table_class) . " WHERE schoolid = :schoolid And type = :type ", array(':schoolid' => $schoolid,':type' => 2));			
	//////////导出数据/////////////////
	if($_GPC['out_putcode'] == 'out_putcode'){
		$lists = pdo_fetchall("SELECT s_name,code,mobile,numberid,bj_id FROM " . tablename($this->table_students) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' $condition ORDER BY id DESC");
		$ii   = 0;
		foreach($lists as $index => $row){
			$bj                = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where sid = '{$row['bj_id']}'");
			$arr[$ii]['s_name'] = trim($row['s_name']);
			$arr[$ii]['code']  = $row['code'];
			$arr[$ii]['mobile']  = $row['mobile'];
			$arr[$ii]['numberid']  = $row['numberid'];
			$arr[$ii]['banji']  = $bj['sname'];
			$ii++;
		}
		$this->exportexcel($arr, array('学生', '绑定码', '报名预留手机号', '学号', '班级'), '学生绑定信息表');
		exit();
	}
	//////////////////////////导出学生班级信息//////////////////////////////////////
	if($_GPC['out_BjExcel'] == 'out_BjExcel'){
		$lists = pdo_fetchall("SELECT id,s_name,code,mobile,numberid,bj_id FROM " . GetTableName('students') . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' $condition ORDER BY bj_id DESC");
		$ii   = 0;
		foreach($lists as $index => $row){
			$bj = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where sid = '{$row['bj_id']}'");
			//$arr[$ii]['sid'] = $row['id'];
			$arr[$ii]['numberid'] = $row['id'];
			$arr[$ii]['s_name'] = trim($row['s_name']);
			$arr[$ii]['banji']  = $bj['sname'];
			$arr[$ii]['newbanji']  = '';
			$arr[$ii]['oldnumberid']  = $row['numberid'];
			$ii++;
		}
		$this->exportexcel($arr, array('学生ID', '学生姓名', '原班级','新班级','原学号','新学号'), '学生绑定信息表');
		exit();
	}
	
	
	//////////////////////////导出学生信息/////////////////////////////////
	if($_GPC['excel_stuinfocard'] == 'excel_stuinfocard'){
	
		$listss = pdo_fetchall("SELECT * FROM " . tablename($this->table_students) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' ORDER BY id DESC");
		$ii   = 0;
		foreach($listss as $index => $row){
			$arr[$ii]['s_name'] = $row['s_name'];
			if($row['sex'] == 1){
				$arr[$ii]['sex'] = '男';
			}else{
				$arr[$ii]['sex'] = '女';
			}
			$infocard = json_decode($row['infocard'],true);
			$arr[$ii]['numberid'] = $row['idcard'];
			$this_bj = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}'and sid = '{$row['bj_id']}'");
			
			
			$arr[$ii]['bj_name'] = $this_bj['sname'];
			$arr[$ii]['idcard'] = $infocard['IDcard'];
			$arr[$ii]['nation'] = $infocard['Nation'];
			$arr[$ii]['birthdate'] = date("Y-m-d",$row['birthdate']);
			$arr[$ii]['seffectivetime'] = date("Y-m-d",$row['seffectivetime']);
			$arr[$ii]['address'] = $row['area_addr'];
			$arr[$ii]['NowAddress'] = $infocard['NowAddress'];
			
			$arr[$ii]['HomeChild'] = $infocard['HomeChild']?'是':'否';
			$arr[$ii]['SingleFamily'] = $infocard['SingleFamily']?'是':'否';
			$arr[$ii]['IsKeep'] = $infocard['IsKeep']?'是':'否';
			if($infocard['DayOrWeek'] == 1){
				$arr[$ii]['IsKeep'] .= ' | 午托';
			}elseif($infocard['DayOrWeek'] == 2){
				$arr[$ii]['DayOrWeek']  .= ' | 周托';
			}
			$Finfo = '【学历】'.$infocard['Fxueli'].' 【职业】'.$infocard['Fwork'].' 【爱好】'.$infocard['Fhobby'].' 【工作单位】'.$infocard['FWorkPlace'];
			$Minfo = '【学历】'.$infocard['Mxueli'].' 【职业】'.$infocard['Mwork'].' 【爱好】'.$infocard['Mhobby'].' 【工作单位】'.$infocard['MWorkPlace'];
			$GrandFinfo = '【学历】'.$infocard['GrandFxueli'].' 【职业】'.$infocard['GrandFwork'].' 【爱好】'.$infocard['GrandFhobby'].' 【工作单位】'.$infocard['GrandFWorkPlace'];
			$GrandMinfo = '【学历】'.$infocard['GrandMxueli'].' 【职业】'.$infocard['GrandMwork'].' 【爱好】'.$infocard['GrandMhobby'].' 【工作单位】'.$infocard['GrandMWorkPlace'];
			$WGrandFinfo = '【学历】'.$infocard['WGrandFxueli'].' 【职业】'.$infocard['WGrandFwork'].' 【爱好】'.$infocard['WGrandFhobby'].' 【工作单位】'.$infocard['WGrandFWorkPlace'];
			$WGrandMinfo = '【学历】'.$infocard['WGrandMxueli'].' 【职业】'.$infocard['WGrandMwork'].' 【爱好】'.$infocard['WGrandMhobby'].' 【工作单位】'.$infocard['WGrandMWorkPlace'];
			$Otherinfo = '【学历】'.$infocard['Otherxueli'].' 【职业】'.$infocard['Otherwork'].' 【爱好】'.$infocard['Otherhobby'].' 【工作单位】'.$infocard['OtherWorkPlace'];
			$arr[$ii]['Finfo'] = $Finfo;
			$arr[$ii]['Minfo'] = $Minfo;
			$arr[$ii]['GrandFinfo'] = $GrandFinfo;
			$arr[$ii]['GrandMinfo'] = $GrandMinfo;
			$arr[$ii]['WGrandFinfo'] = $WGrandFinfo;
			$arr[$ii]['WGrandMinfo'] = $WGrandMinfo;
			$arr[$ii]['Otherinfo'] = $Otherinfo;
			
			$mainwatch = json_decode($infocard['MainWatcharr']);
			//var_dump($mainwatch);
			$watchmans = '';
			if(!empty($infocard['MainWatcharr']) && $infocard['MainWatcharr'] != 'null'){
				//var_dump($infocard['MainWatcharr']);
			
				foreach($mainwatch as $row){
					if($row == 1){
						$watchmans .=' 父亲 |';
					}
					if($row == 2){
						$watchmans .=' 母亲 |';
					}
					if($row == 3){
						$watchmans .=' 爷爷 |';
					}
					if($row == 4){
						$watchmans .=' 奶奶 |';
					}
					if($row == 5){
						$watchmans .=' 外公 |';
					}
					if($row == 6){
						$watchmans .=' 外婆 |';
					}
					if($row == 7){
						$watchmans .=' 其他 |';
					}
				}
				$watchmans = trim($watchmans,'|'); 
			}
			$arr[$ii]['watchmans'] = $watchmans;
			$arr[$ii]['Childhobby'] = $infocard['Childhobby'];
			$arr[$ii]['ChildWord'] = $infocard['ChildWord'];
			$arr[$ii]['SchoolWord'] = $infocard['SchoolWord'];

			$ii++;
		}
		$this->exportexcel($arr, array('姓名','性别','学号','班级','身份证','民族','出生年月','入学时间','家庭住址','现住址','是否留守儿童','是否单亲家庭','是否托管','父亲','母亲','爷爷','奶奶','外公','外婆','其他','监护人','孩子爱好','对孩子的期望','对学校的期望'), '学生详细信息');
		exit();
	}
	
	//////////////////////////导出学生/////////////////////////////////
	if($_GPC['outStu'] == 'outStu'){
		$listss = pdo_fetchall("SELECT s.s_name,s.sex,from_unixtime(s.birthdate,'%Y/%m/%d') as birthdate,s.mobile,s.identitycard,from_unixtime(s.seffectivetime,'%Y/%m/%d') as seffectivetime,from_unixtime(s.stheendtime,'%Y/%m/%d') as stheendtime,s.area_addr,nc.sname as njname,bc.sname as bjname,s.numberid,s.code,s.s_type FROM " . tablename($this->table_students) . " as s LEFT JOIN " . GetTableName('classify') . " as bc ON s.bj_id = bc.sid LEFT JOIN " . GetTableName('classify') . " as nc ON s.xq_id = nc.sid WHERE s.weid = '{$weid}' AND s.schoolid = '{$schoolid}' $condition1 ORDER BY s.id DESC");
		$this->exportexcel($listss, array('姓名','性别','生日','手机号码','身份证','报名时间','终止时间','家庭住址','年级','班级','学号','绑定码','类型'), '学生信息');

		exit();
	}
	
	$category = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " WHERE weid = :weid And schoolid = :schoolid ORDER BY sid ASC, ssort DESC", array(':weid' => $weid, ':schoolid' => $schoolid), 'sid');
	$list = pdo_fetchall("SELECT * FROM " . tablename($this->table_students) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' $condition ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
	$listAfter = array();
	$ybdxs = 0;
	$chong = 0;
	foreach($list as $key => $value){
	
		if($value['ouid'] || $value['ouserid']){
			$ybdxs ++;
		}
		if($value['muid'] || $value['muserid']){
			$ybdxs ++;
		}
		if($value['duid'] || $value['duserid']){
			$ybdxs ++;
		}
		if($value['otheruid'] || $value['otheruserid']){
			$ybdxs ++;
		}
		if(!empty($value['sellteaid'])){
			$list[$key]['sellteaname'] = pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " where id = '{$value['sellteaid']}'")['tname'];
		}
		$list[$key]['fanslist'] = false;
		$fanslist =  pdo_fetchall("SELECT openid FROM " . GetTableName('user') . " where schoolid = '{$schoolid}' And sid = {$value['id']}  And tid = 0 ");
		if(!empty($fanslist)){
			$list[$key]['fanslist'] = array();
			foreach($fanslist as $val){
				$fansinfo = GetWeFans($weid,$val['openid']);
				$list[$key]['fanslist'][] = $fansinfo['avatar']?tomedia($fansinfo['avatar']):tomedia($school['spic']);
			}
			$list[$key]['wxbdrs'] = count($fanslist);
		}
		if($_W['schooltype']){
			$bmorder = getHasSyksStu($schoolid,$value['id']);
			$temporder = array();
			$mutikc = [];
			foreach( $bmorder as $key1 => $value1 ){
				$kc = pdo_fetch("SELECT name FROM " . tablename($this->table_tcourse) . " where id = '{$value1['kcid']}'");
				$buycourse = pdo_fetchcolumn("SELECT ksnum FROM " . tablename($this->table_coursebuy) . " WHERE sid = :sid And kcid=:kcid And  schoolid =:schoolid", array(':sid' => $value['id'],':kcid'=> $value1['kcid'],':schoolid'=> $schoolid));
				$hasSign =  pdo_fetchcolumn("SELECT sum(costnum) FROM " . tablename($this->table_kcsign) . " WHERE sid = :sid And kcid=:kcid And  schoolid =:schoolid and status = 2 ", array(':sid' =>  $value['id'],':kcid'=> $value1['kcid'],':schoolid'=> $schoolid));
				$rest = $buycourse - $hasSign; 
				$temporder[]= $kc['name']."【剩余".$rest."课时】";

				if(keep_mutikc()){
					$freeks = pdo_fetch("SELECT ksnum FROM ".GetTableName('freekslog')." WHERE sid = '{$value['id']}' AND kcid='{$value1['kcid']}' ");
					$mutikc[$key1]['kcname'] = $kc['name'];
					$mutikc[$key1]['allks'] = $buycourse ? $buycourse : 0; //总课时
					$mutikc[$key1]['hasSign'] = $hasSign ? $hasSign : 0; //已用课时
					$mutikc[$key1]['freeks'] = $freeks['ksnum']; //赠送课时
					$mutikc[$key1]['kcid'] = $value1['kcid']; //赠送课时
				}
			}
			if(keep_mutikc()){
				$list[$key]['mutikc'] = $mutikc;
			}
			$list[$key]['bmkc'] = $temporder;
		}
		if($value['qrcode_id']){
			$qrimg = pdo_fetch("SELECT id,show_url,expire,subnum,qrcid FROM " . tablename($this->table_qrinfo) . " where  id = '{$value['qrcode_id']}' ");
			$list[$key]['img_qr'] = tomedia($qrimg['show_url']);
			$list[$key]['qrcid'] = $qrimg['qrcid'];
			$list[$key]['subnum'] = $qrimg['subnum'];
			$list[$key]['overtime'] = true;
			if($qrimg['expire'] < time()){
				$list[$key]['overtime'] = false;
			}else{
				$list[$key]['restday'] = floor(($qrimg['expire'] - time())/86400);
			}
		}
		if(!empty($value['roomid'])){
			$roominfo = pdo_fetch("SELECT name,apid FROM " . tablename($this->table_aproom) . " where  id = '{$value['roomid']}' ");
			$list[$key]['roomname'] = $roominfo['name'];
			$apinfo = pdo_fetch("SELECT name FROM " . tablename($this->table_apartment) . " where  id = '{$roominfo['apid']}' ");
			$list[$key]['apname'] = $apinfo['name'];
		}
	}
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_students) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' $condition");
	$pager = pagination($total, $pindex, $psize);
	if(!empty($_GPC['one_sid'])){ //只取一行数据
		include $this->template('web/stu/one_stu');
		die();
	}
}elseif($operation == 'delete'){
	$id  = intval($_GPC['id']);
	$row = pdo_fetch("SELECT * FROM " .GetTableName('students'). " WHERE id = :id", array(':id' => $id));
	$checkuser = pdo_fetch("SELECT * FROM ".GetTableName('user')." WHERE  schoolid = '{$schoolid}' And sid = '{$id}'  ");
	if(!empty($checkuser)){
		$result['msg'] = '抱歉，学生有绑定信息不可删除！';
		$result['result'] = false;
		die(json_encode($result));
	}
	if(empty($row)){
		$result['msg'] = '抱歉，学生不存在或是已经被删除！';
		$result['result'] = false;
		die(json_encode($result));
	}
	//校智付删除学生操作
	setXzfExtra($id,2);
	xzTriggerCommon($schoolid,0,'delete_stu',$id);
	//同步学生接口操作
	if(keep_MC()){
        $Dres = znlSingleStuInfo($id,116);
        if($Dres['resultCode'] != 0){
            $urlMsg = '删除失败，'.$Dres['resultCode'];
            $result['msg'] = $urlMsg;
            $result['result'] = false;
            die(json_encode($result));
        }
	}

	$sid_arr = pdo_fetchall("SELECT id FROM " . tablename($this->table_students) . " WHERE keyid = :keyid", array(':keyid' =>$id));
		
		if(!empty($sid_arr)){
			foreach($sid_arr as $key => $value)	{
				$rowloop = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " WHERE id = :id", array(':id' => $value['id']));
				if(!empty($rowloop))
				{
					pdo_delete($this->table_students, array('id' => $value['id'], 'schoolid' => $schoolid));
					
					DeleteStudent($value['id']);
					if(!empty($rowloop['otheruserid'])){
						pdo_delete($this->table_user, array('id' => $rowloop['otheruserid']));
					}else{
						pdo_delete($this->table_user, array('sid' => $value['id']));
					}
					if(!empty($rowloop['ouserid'])){
						pdo_delete($this->table_user, array('id' => $rowloop['ouserid']));
					}else{
						pdo_delete($this->table_user, array('sid' => $value['id']));
					}
					if(!empty($rowloop['muserid'])){
						pdo_delete($this->table_user, array('id' => $rowloop['muserid']));
					}else{
						pdo_delete($this->table_user, array('sid' => $value['id']));
					}
					if(!empty($rowloop['duserid'])){
						pdo_delete($this->table_user, array('id' => $rowloop['duserid']));
					}else{
						pdo_delete($this->table_user, array('sid' => $value['id']));
					}
				}
			}
		}else{
			pdo_delete($this->table_students, array('id' => $id, 'schoolid' => $schoolid));
			DeleteStudent($id);
			if(!empty($row['otheruserid'])){
				pdo_delete($this->table_user, array('id' => $row['otheruserid']));
			}else{
				pdo_delete($this->table_user, array('sid' => $id));
			}
			if(!empty($row['ouserid'])){
				pdo_delete($this->table_user, array('id' => $row['ouserid']));
			}else{
				pdo_delete($this->table_user, array('sid' => $id));
			}
			if(!empty($row['muserid'])){
				pdo_delete($this->table_user, array('id' => $row['muserid']));
			}else{
				pdo_delete($this->table_user, array('sid' => $id));
			}
			if(!empty($row['duserid'])){
				pdo_delete($this->table_user, array('id' => $row['duserid']));
			}else{
				pdo_delete($this->table_user, array('sid' => $id));
			}
		}
	if($row['qrcode_id']){
		pdo_delete($this->table_qrinfo, array('id' => $id));
	}
	$urlMsg = '删除成功！';
	$result['msg'] = $urlMsg;
	$result['result'] = true;
	die(json_encode($result));
}elseif($operation == 'unbd'){
	$sid     = intval($_GPC['sid']);
	$userid     = intval($_GPC['userid']);
	$openid = $_GPC['openid'];
	$stu    = pdo_fetch("SELECT * FROM " . GetTableName('students') . " WHERE id = :id", array(':id' => $sid));
	$user    = pdo_fetch("SELECT pard FROM " . GetTableName('user') . " WHERE id = :id", array(':id' => $userid));
	if(empty($stu)){
		$result['msg'] = '抱歉学生不存在,请刷新本页';
		$result['result'] = false;
		die(json_encode($result));
	}
	if(empty($user)){
		$result['msg'] = '抱歉用户不存在,请刷新本页';
		$result['result'] = false;
		die(json_encode($result));
	}
	if($user['pard'] == 4){
		$temp = array( 'own' => 0, 'ouserid' => 0, 'ouid' => 0 );
	}
	if($user['pard'] == 2){
		$temp = array( 'mom' => 0, 'muserid' => 0, 'muid' => 0 );
	}	
	if($user['pard'] == 3){
		$temp = array( 'dad' => 0, 'duserid' => 0, 'duid' => 0 );
	}
	if($user['pard'] == 5){
		$temp = array( 'other' => 0, 'otheruserid' => 0, 'otheruid' => 0 );
	}
	pdo_delete(GetTableName('user',false), array('id' => $userid));
	pdo_update(GetTableName('students',false), $temp, array('id' => $sid));
	$result['msg'] = '解绑成功';
	$result['result'] = true;
	die(json_encode($result));
}elseif($operation == 'fixavatar'){
		$frommembers   = pdo_fetchall("SELECT uid,avatar FROM " . tablename("mc_members")."where avatar LIKE '%/132132' ");
		
		foreach( $frommembers as $key => $value )
		{
			$temp_avatar = substr_replace($value['avatar'],"/132",-7);
			$data= array(
			'avatar' => $temp_avatar
			);
			pdo_update("mc_members", $data, array('uid' => $value['uid']));

		}
		$count = count($frommembers);
		$this->imessage('修复成功！', referer(), 'success');
}elseif($operation == 'makecode'){
	$nocode = pdo_fetchall("SELECT id,code FROM " . tablename($this->table_students) . " WHERE schoolid = :schoolid ", array(':schoolid' => $schoolid));
	if($nocode){
		$notrowcount = 0;
		foreach($nocode as $k => $row){
			if(empty($row['code'])){
				$rAndStr = str_shuffle('123456789');
				$rAnd    = substr($rAndStr, 0, 6);
				$data = array(
					'code'     => $rAnd
				);					
				pdo_update($this->table_students, $data, array('id' => $row['id']));
				$notrowcount++;
			}
		}
		$this->imessage('操作成功,共为'.$notrowcount.'个学生生成了绑定码！', $this->createWebUrl('students', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
	}else{
		$this->imessage('本校学生全部已生成绑定码，无需重复操作！', '', 'error');
	}
}elseif($operation == 'makeallqr'){
	if(empty($logo['spic'])){
		$this->imessage('抱歉,本校开启了用户二维码功能,请设置校园默认学生头像');
	}			
	$notrowcount = 0;
	$gqeqrcount = 0;
	foreach($_GPC['idArr'] as $k => $id){
		load()->func('tpl');
		load()->func('file');
		$id = intval($id);
		$row = pdo_fetch("SELECT id,qrcode_id,keyid FROM " . tablename($this->table_students) . " WHERE id = '{$id}'");
		if($row['keyid'] == 0){
			if(!empty($row['qrcode_id'])){
				$qrinfo = pdo_fetch("SELECT id,expire,qrcid FROM " . tablename($this->table_qrinfo) . " WHERE weid = '{$weid}' And id = '{$row['qrcode_id']}' ");
				if(time() > $qrinfo['expire'] || $qrinfo['qrcid'] != $row['id']){
					$barcode = array(
						'expire_seconds' =>2592000 ,
						'action_name' => '',
						'action_info' => array(
										'scene' => array(
												'scene_id' => $row['id']
										),
						),
					);
					$uniacccount = WeAccount::create($weid);
					$barcode['action_name'] = 'QR_SCENE';
					$result = $uniacccount->barCodeCreateDisposable($barcode);
					if (is_error($result)) {
						message($result['message'], referer(), 'fail');
					}
					if (!is_error($result)) {
						$showurl = $this->createImageUrlCenterForUser("https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=" . $result['ticket'], $row ['id'], 0, $schoolid);
						$urlarr = explode('/',$showurl);
						$qrurls = "images/fm_jiaoyu/".$urlarr['4'];	
						$insert = array(
							'show_url' => $qrurls,
							'qrcid'   => $row['id'],
							'ticket' => $result['ticket'],
							'qr_url' => ltrim($result['url'],"http://weixin.qq.com/q/"),
							'expire' => $result['expire_seconds'] + time(), 
							'createtime' => time(),
						);
						pdo_update($this->table_qrinfo, $insert, array('id' =>$qrinfo ['id']));	
						$qrurl = pdo_fetch("SELECT show_url FROM " . tablename($this->table_qrinfo) . " WHERE id = '{$qrinfo ['id']}'");
						if (!empty($_W['setting']['remote']['type'])) {
							$remotestatus = file_remote_upload($qrurl['show_url']);
								if (is_error($remotestatus)) {
									message('远程附件上传失败，'.$qrurl['show_url'].'请检查配置并重新上传');
								}
						}
						$gqeqrcount++;
					}								
				}								
			}else{
				$barcode = array(
					'expire_seconds' =>2592000,
					'action_name' => '',
					'action_info' => array(
						'scene' => array(
							'scene_id' => $row['id']
						),
					),
				);
				$uniacccount = WeAccount::create($wwwweid);
				$barcode['action_name'] = 'QR_SCENE';
				$result = $uniacccount->barCodeCreateDisposable($barcode);
				if (is_error($result)) {
					message($result['message'], referer(), 'fail');
				}
				if (!is_error($result)) {
					$showurl = $this->createImageUrlCenterForUser("https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=" . $result['ticket'], $row['id'], 0, $schoolid);
					$urlarr = explode('/',$showurl);
					$qrurls = "images/fm_jiaoyu/".$urlarr['4'];	
					$insert = array(
						'weid' => $_W['uniacid'], 
						'schoolid' => $schoolid,
						'qrcid' => $row['id'], 
						'name' => '用户绑定临时二维码', 
						'model' => 1,
						'qr_url' => ltrim($result['url'],"http://weixin.qq.com/q/"),
						'ticket' => $result['ticket'], 
						'show_url' => $qrurls,
						'expire' => $result['expire_seconds'] + time(), 
						'createtime' => time(),
						'status' => '1',
						'type' => '3'
					);
					pdo_insert($this->table_qrinfo, $insert);
					$qrid = pdo_insertid();
					$qrurl = pdo_fetch("SELECT show_url FROM " . tablename($this->table_qrinfo) . " WHERE id = '{$qrid}'");
					$arr = explode('/',$qrurl['show_url']);
					$pathname = "images/fm_jiaoyu/".$arr['2'];
					if (!empty($_W['setting']['remote']['type'])) {
						$remotestatus = file_remote_upload($pathname);
							if (is_error($remotestatus)) {
								message('远程附件上传失败，'.$pathname.'请检查配置并重新上传');
							}
					}
				}								
				pdo_update($this->table_students, array('qrcode_id' => $qrid), array('id' => $row['id']));
				$notrowcount++;
			}							
		}
		if($row['id'] == $row['keyid']){
			if(!empty($row['qrcode_id'])){
				$qrinfo = pdo_fetch("SELECT id,expire,qrcid FROM " . tablename($this->table_qrinfo) . " WHERE weid = '{$weid}' And id = '{$row['qrcode_id']}' ");
				if(time() > $qrinfo['expire'] || $qrinfo['qrcid'] != $row['id']){
					$barcode = array(
						'expire_seconds' =>2592000 ,
						'action_name' => '',
						'action_info' => array(
										'scene' => array(
												'scene_id' => $row['id']
										),
						),
					);
					$uniacccount = WeAccount::create($weid);
					$barcode['action_name'] = 'QR_SCENE';
					$result = $uniacccount->barCodeCreateDisposable($barcode);
					if (is_error($result)) {
						message($result['message'], referer(), 'fail');
					}
					if (!is_error($result)) {
						$showurl = $this->createImageUrlCenterForUser("https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=" . $result['ticket'], $row ['id'], 0, $schoolid);
						$urlarr = explode('/',$showurl);
						$qrurls = "images/fm_jiaoyu/".$urlarr['4'];	
						$insert = array(
							'show_url' => $qrurls,
							'qrcid' => $row['id'],
							'ticket' => $result['ticket'],
							'qr_url' => ltrim($result['url'],"http://weixin.qq.com/q/"),
							'expire' => $result['expire_seconds'] + time(), 
							'createtime' => time(),
						);
						pdo_update($this->table_qrinfo, $insert, array('id' =>$qrinfo ['id']));	
						$qrurl = pdo_fetch("SELECT show_url FROM " . tablename($this->table_qrinfo) . " WHERE id = '{$qrinfo ['id']}'");
						if (!empty($_W['setting']['remote']['type'])) {
							$remotestatus = file_remote_upload($qrurl['show_url']);
								if (is_error($remotestatus)) {
									message('远程附件上传失败，'.$qrurl['show_url'].'请检查配置并重新上传');
								}
						}
						$gqeqrcount++;
					}								
				}								
			}else{
				$barcode = array(
					'expire_seconds' =>2592000,
					'action_name' => '',
					'action_info' => array(
						'scene' => array(
							'scene_id' => $row['id']
						),
					),
				);
				$uniacccount = WeAccount::create($wwwweid);
				$barcode['action_name'] = 'QR_SCENE';
				$result = $uniacccount->barCodeCreateDisposable($barcode);
				if (is_error($result)) {
					message($result['message'], referer(), 'fail');
				}
				if (!is_error($result)) {
					$showurl = $this->createImageUrlCenterForUser("https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=" . $result['ticket'], $row['id'], 0, $schoolid);
					$urlarr = explode('/',$showurl);
					$qrurls = "images/fm_jiaoyu/".$urlarr['4'];	
					$insert = array(
						'weid' => $_W['uniacid'], 
						'schoolid' => $schoolid,
						'qrcid' => $row['id'], 
						'name' => '用户绑定临时二维码', 
						'model' => 1,
						'qr_url' => ltrim($result['url'],"http://weixin.qq.com/q/"),
						'ticket' => $result['ticket'], 
						'show_url' => $qrurls,
						'expire' => $result['expire_seconds'] + time(), 
						'createtime' => time(),
						'status' => '1',
						'type' => '3'
					);
					pdo_insert($this->table_qrinfo, $insert);
					$qrid = pdo_insertid();
					$qrurl = pdo_fetch("SELECT show_url FROM " . tablename($this->table_qrinfo) . " WHERE id = '{$qrid}'");
					$arr = explode('/',$qrurl['show_url']);
					$pathname = "images/fm_jiaoyu/".$arr['2'];
					if (!empty($_W['setting']['remote']['type'])) {
						$remotestatus = file_remote_upload($pathname);
							if (is_error($remotestatus)) {
								message('远程附件上传失败，'.$pathname.'请检查配置并重新上传');
							}
					}
				}								
				pdo_update($this->table_students, array('qrcode_id' => $qrid), array('id' => $row['id']));
				$notrowcount++;
			}
		}			
	}
	$message = "操作成功,共为{$notrowcount}个学生生成了二维码,清理过期二维码并重新生成{$gqeqrcount}个！";
	$data ['result'] = true;
	$data ['msg'] = $message;				
	die (json_encode($data));			
}elseif($operation == 'deleteall'){
	$rowcount    = 0;
	$notrowcount = 0;
	foreach($_GPC['idArr'] as $k => $id){
		$id = intval($id);
		if(!empty($id)){
			$goods = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " WHERE id = :id", array(':id' => $id));
			if(!empty($goods['mom'])){

				$message = '批量删除失败，学生' . $goods['s_name'] . '母亲微信未解绑！';

				die (json_encode(array(
					'result' => false,
					'msg'    => $message
				)));
			}
			if(!empty($goods['dad'])){

				$message = '批量删除失败，学生' . $goods['s_name'] . '父亲微信未解绑！';

				die (json_encode(array(
					'result' => false,
					'msg'    => $message
				)));
			}
			if(!empty($goods['own'])){

				$message = '批量删除失败，学生' . $goods['s_name'] . '本人微信未解绑！';

				die (json_encode(array(
					'result' => false,
					'msg'    => $message
				)));
			}
			if(!empty($goods['other'])){

				$message = '批量删除失败，学生' . $goods['s_name'] . '家长微信未解绑！';

				die (json_encode(array(
					'result' => false,
					'msg'    => $message
				)));
			}					
			if(empty($goods)){
				$notrowcount++;
				continue;
			}
			if($goods['qrcode_id']){
				pdo_delete($this->table_qrinfo, array('qrcid' => $goods['qrcode_id']));
			}
			$sid_arr = pdo_fetchall("SELECT id FROM " . tablename($this->table_students) . " WHERE keyid = :keyid", array(':keyid' =>$id));
			//讯贞触发
			xzTriggerCommon($schoolid,0,'delete_stu',$id);
			//同步学生接口操作
			if(keep_MC()){
                $Dres = znlSingleStuInfo($id,116);
                // $Dres = znlSingleStuInfo($id,116);
                if($Dres['resultCode'] != 0){
                    $notrowcount++;
                    continue;
                }
			}

			//校智付删除学生操作
			setXzfExtra($id,2);
			pdo_delete($this->table_students, array('id' => $id, 'schoolid' => $schoolid));
			pdo_delete($this->table_students, array('keyid' =>$id, 'schoolid' => $schoolid));

			/*  if(keep_wt() && CheckWtOn($schoolid)) {
				mload()->model('wt');
				//人员删除
				$param['guid'] = $goods['guid'];
				personAction($schoolid, $weid, time(), $param, 'delete');
			} */


			if(!empty($sid_arr)){
				foreach($sid_arr as $key => $value)
				{
					DeleteStudent($value['id']);
				}
			} 
			$rowcount++;
		}
	}
	$message = "操作成功！共删除{$rowcount}条数据,{$notrowcount}条数据不能删除!";
	$data ['result'] = true;
	$data ['msg'] = $message;
	die (json_encode($data));
}elseif($operation == 'add_bj'){  //批量增加班级
	$rowcount    = 0;
	$notrowcount = 0;
	$bj_id = intval($_GPC['bj_id']);
	$xueqi = pdo_fetch("SELECT parentid FROM " . tablename($this->table_classify) . " WHERE sid = :sid", array(':sid' => $bj_id));

		
	if(!empty($xueqi)){
		foreach($_GPC['idArr'] as $k => $id){
			$id = intval($id);
			if(!empty($id)){
				$goods = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " WHERE id = :id", array(':id' => $id));				
				if(empty($goods)){
					$notrowcount++;
					continue;
				}
				if($goods['keyid'] == 0 )
				{
					pdo_update($this->table_students, array('keyid'=>$id), array('id' => $id));
				}
				$userOld = 	pdo_fetchall("SELECT sid,tid,weid,schoolid,uid,openid,userinfo,pard,status,is_allowmsg,is_frist,createtime FROM " . tablename($this->table_user) . " WHERE sid = :sid", array(':sid' => $id));
				$stuOld =  pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " WHERE id = :id", array(':id' => $id));				
				$allbj =  pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " WHERE keyid = :keyid And bj_id = :bj_id", array(':keyid' => $stuOld['keyid'],':bj_id'=>$bj_id));
				
				if($allbj )
				{
					
					$notrowcount++ ;
					
					
					continue;
				}
					
					
					
							
				array_splice($stuOld,0,1);
				$stuOld['bj_id'] = $bj_id;
				$stuOld['xq_id'] = $xueqi['parentid'];
				pdo_insert($this->table_students,$stuOld);
				$newsid = pdo_insertid();
				foreach( $userOld as $key => $value )
				{
					$value['sid'] = $newsid;
					pdo_insert($this->table_user,$value);
					$userid = pdo_insertid();
					$this->DoTag($value['schoolid'],$userid); //打标签
				}
				$rowcount++;
			}
		}

		
		$data ['result'] = true;
		$message = "操作成功！共{$rowcount}个学生新增了班级,{$notrowcount}个学生不能新增!";
	}else{
		$data ['result'] = false;
		$message = "操作失败，你选择的班级无归属年级，请前往基本设置班级管理设置!";
	}
	$data ['msg'] = $message;
	die (json_encode($data));
}elseif($operation == 'userinfo'){
	$alluser = pdo_fetchall("SELECT id,userinfo,realname,mobile FROM " . tablename('wx_school_user') . " WHERE schoolid != 0 ");
	foreach($alluser as $row){
		if(!empty($row['userinfo'])){
			$userinfo = iunserializer($row['userinfo']);
			$userdata = array();
			if(!empty($userinfo['name']) && empty($row['realname'])){
				$userdata['realname'] = $userinfo['name'];
			}
			if(!empty($userinfo['mobile']) && empty($row['mobile'])){
				$userdata['mobile'] = $userinfo['mobile'];
			}
			if(!empty($row['userinfo']) && (empty($row['realname']) || empty($row['mobile']))){
				pdo_update(GetTableName('user',false), $userdata, array('id' => $row['id']));
			}
		}
	}
	$this->imessage($message, $this->createWebUrl('students', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
}elseif($operation == 'change_bj'){
	$rowcount    = 0;
	$notrowcount = 0;
	$bj_id = intval($_GPC['bj_id']);
	$xueqi = pdo_fetch("SELECT parentid FROM " . tablename($this->table_classify) . " WHERE sid = :sid", array(':sid' => $bj_id));	
	if(!empty($xueqi)){
		foreach($_GPC['idArr'] as $k => $id){
			$id = intval($id);
			//修改视频画面到对应的班级
			ChangeVideoWithBj($schoolid,$id,$bj_id);
			if(!empty($id)){
				$checkcard = pdo_fetch("SELECT * FROM " . tablename($this->table_idcard) . " WHERE sid = :sid", array(':sid' => $id));
				if($checkcard){
					pdo_update($this->table_idcard, array('bj_id' => $bj_id), array('sid' => $id));
					pdo_update(GetTableName('schoolset',false), array('top'=>1), array('schoolid' => $schoolid, 'weid'=>$weid));
				}						
				$goods = pdo_fetch("SELECT id FROM " . tablename($this->table_students) . " WHERE id = :id", array(':id' => $id));					
				if(empty($goods)){
					$notrowcount++;
					continue;
				}
				//同步学生接口操作
				if(keep_MC()){
					znlSingleStuInfo($id,117);
				}

				if(!$_W['schooltype']){
					$checkcard = pdo_fetch("SELECT * FROM " . tablename($this->table_idcard) . " WHERE sid = :sid", array(':sid' => $id));
					if($checkcard){
						pdo_update($this->table_idcard, array('bj_id' => trim($bj_id)), array('sid' => $id));
						pdo_update(GetTableName('schoolset',false), array('top'=>1), array('schoolid' => $schoolid, 'weid'=>$weid));
					}
				}

				pdo_update($this->table_students, array('bj_id' => $bj_id,'xq_id' => $xueqi['parentid']), array('id' => $id));
				$rowcount++;
			}
		}
		$data ['result'] = true;
		$message = "操作成功！共转移{$rowcount}个学生,{$notrowcount}个学生不能转移!";
	}else{
		$data ['result'] = false;
		$message = "操作失败，你选择的班级无归属年级，请前往基本设置班级管理设置!";
	}
	$data ['msg'] = $message;
	die (json_encode($data));
}elseif($operation == 'deleteallstudents'){
	// pdo_delete($this->table_qrinfo, array('schoolid' => $schoolid, 'weid' => $weid, 'type' => 3));
	// pdo_delete($this->table_students, array('schoolid' => $schoolid, 'weid' => $weid));
	// pdo_delete($this->table_user, array('schoolid' => $schoolid, 'tid' => 0));
	// $this->imessage('已全部删除本校学生！', $this->createWebUrl('students', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
}elseif($operation == 'get_infocard'){
	$stuid = $_GPC['id'];
	$student = pdo_fetch("SELECT s.*,ss.groupfile,ss.ypc,ss.ccyl,ss.growfile FROM " . tablename($this->table_students) . " as s LEFT JOIN " . GetTableName('stuinfo') . " as ss ON s.id = ss.sid WHERE s.id = :id", array(':id' => $stuid));
	$age = DiffDate(date("Y-m-d",$student['birthdate']),date("Y-m-d",time()))[0];
	$cardinfo = json_decode($student['infocard'],true);
	// 团员管理
	$ccyl = json_decode($student['ccyl'],true);
	$ccylnum = 8 - count($ccyl['activityDate']);
	for($i=0;$i<$ccylnum;$i++){
		$ccyl['activityDate'][] = '';
	}
	// var_dump($ccyl);die;
	// 社团管理
	$groupfile = json_decode($student['groupfile'],true);
	$groupfilelnum = 8 - count($groupfile['activityDate']);
	for($i=0;$i<$groupfilelnum;$i++){
		$groupfile['activityDate'][] = '';
	}

	//先锋队管理
	$ypc = json_decode($student['ypc'],true);
	$ypcnum = 8 - count($ypc['activityDate']);
	for($i=0;$i<$ypcnum;$i++){
		$ypc['activityDate'][] = '';
	}

	//成长档案
	$growfile = json_decode($student['growfile'],true);
	$growfilelnum = 8 - count($growfile['activityDate']);
	for($i=0;$i<$growfilelnum;$i++){
		$growfile['activityDate'][] = '';
	}
	// var_dump($growfile);die;

	$MainWatcharr = json_decode($cardinfo['MainWatcharr']);
	include $this->template('web/stu/stu_infocard');
	exit();
}elseif($operation == 'change_cardinfo'){
	$sid = $_GPC['stuId'];
	$this_data = array(
		's_name' => trim($_GPC['StuName_card']),
		'sex' => $_GPC['Sex_card'],
		'numberid' => $_GPC['NumberId_card'],
		'area_addr' => $_GPC['HomeAddress_card'],
		'birthdate' => strtotime($_GPC['Birthdate_card']),
		'seffectivetime' => strtotime($_GPC['Seffectivetime_card']),
		'identitycard' => $_GPC['IDcard_card']
	);
	$infocard = array();
	foreach ($_GPC['InfoCard'] as $key => $value) {
		$infocard[$key] = trim($value);
	}
	$infocard['MainWatcharr'] = json_encode($_GPC['MainWatcharr']);

	$growFile = $_GPC['Grow'];

	$groupFile = $_GPC['Group'];

	$YPC = $_GPC['YPC'];

	$CCYL = $_GPC['CCYL'];
	$stuinfo = pdo_fetch("SELECT id FROM ".GetTableName('stuinfo')." WHERE schoolid = '{$schoolid}' AND sid = '{$sid}' ");
	$stuInfoData = array(
		'weid' => $weid,
		'schoolid' => $schoolid,
		'sid' => $sid,
		'groupfile' => json_encode($groupFile),
		'ypc' => json_encode($YPC),
		'ccyl' => json_encode($CCYL),
		'growfile' => json_encode($growFile),
	);
	if($stuinfo){
		pdo_update(GetTableName('stuinfo',false),$stuInfoData, array('sid' => $sid));
	}else{
		pdo_insert(GetTableName('stuinfo',false),$stuInfoData);
	}
	$this_data['infocard'] = json_encode($infocard);
	pdo_update($this->table_students,$this_data, array('id' => $sid));
	//同步学生接口操作
	if(keep_MC()){
		znlSingleStuInfo($sid,117);
	}

	$result['status'] = true;
	$result['msg'] = "修改成功";
	die(json_encode($result));
}elseif($operation == 'ChangeStatus'){
	$id = $_GPC['id'];
	$status = $_GPC['status'];
	$schoolid = $_GPC['schoolid'];
	pdo_update($this->table_user, array('status' => $status), array('id' =>$id,'schoolid' => $schoolid));
	$result['status'] = true;
	$result['msg'] = "修改成功";
	die(json_encode($result));
}elseif($operation == 'ChangeVideo'){
	$id = $_GPC['id'];
	$status = $_GPC['status'];
	$schoolid = $_GPC['schoolid'];
	pdo_update($this->table_user, array('is_allow_video' => $status), array('id' =>$id,'schoolid' => $schoolid));
	$result['status'] = true;
	$result['msg'] = "修改成功";
	die(json_encode($result));	
}elseif($operation == 'fixnick'){
	$id = $_GPC['id'];
	$pard = $_GPC['pard'];
	$stinfo = pdo_fetch("SELECT {$pard} as allopenid,s_name FROM ".GetTableName('students')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' and id = '{$id}' ");
	$nickname = pdo_fetch("SELECT fanid,nickname FROM ".tablename('mc_mapping_fans')." WHERE  uniacid = '{$weid}'  and openid = '{$stinfo['allopenid']}' ");
	if(empty($nickname['nickname'])){
		pdo_update('mc_mapping_fans',array('nickname'=>$stinfo['s_name']),array('fanid'=>$nickname['fanid']));
	}
	$this->imessage('修复成功', referer(), 'success');

	
}elseif($operation == 'GetAllRoomStu'){
	$Stulist = pdo_fetchall("SELECT distinct( student.id ) ,student.roomid,student.s_name,student.bj_id,idcard.idcard FROM ".GetTableName('students')." as student LEFT JOIN ".GetTableName('idcard')." as idcard on student.id = idcard.sid  WHERE student.schoolid = '{$schoolid}' and student.s_type = 2 GROUP BY student.id ");
	$Cao = pdo_fetchall("SELECT bj.sname as bjname ,nj.sname as njname,bj.sid as sid FROM ".GetTableName('classify')." as bj , ".GetTableName('classify')." as nj WHERE bj.schoolid = :schoolid  and bj.parentid = nj.sid ",array(':schoolid'=>$schoolid),'sid');
		
	$iii = 0;
	$ExArr = [];
	foreach($Stulist as $key=> $value){
		$Bj = pdo_fetch("SELECT bj.sname as bjname ,nj.sname as njname FROM ".GetTableName('classify')." as bj , ".GetTableName('classify')." as nj WHERE bj.schoolid = '{$schoolid}' and bj.sid = '{$value['bj_id']}' and bj.parentid = nj.sid ");
		if($value['roomid'] != 0){
			$Roominfo = pdo_fetch("SELECT room.name as rname ,apart.name as apname FROM ".GetTableName('aproom')." as room , ".GetTableName('apartment')." as apart WHERE room.schoolid = '{$schoolid}' and room.id = '{$value['roomid']}' and apart.id = room.apid ");
			$RoomName = $Roominfo['rname'];
			$ApName = $Roominfo['apname'];

				
		}
		$ExArr[$iii] = array(
			'sname' => $value['s_name'] ? $value['s_name'] : '',
			//'bjname' => $Bj['bjname'] ? $Bj['bjname'] : '',
			//'Njname' => $Bj['njname'] ? $Bj['njname'] : '',
			'bjname' => $Cao[$value['bj_id']]['bjname'],
			'Njname' => $Cao[$value['bj_id']]['njname'],
			'roomname' => $RoomName ?$ApName."-".$RoomName : '',
		//	'apname' => $ApName ? $ApName : '',
			'idcard' => $value['idcard'] ? $value['idcard'] : '',
		);
		$iii++;
	}


	$title = array('学生姓名','班级','年级','宿舍','卡号');
	$this->exportexcel($ExArr, $title, '住校生');
	exit();
}elseif($operation == 'OutBdInfo'){
	$condition = '';
	if(!empty($_GPC['one_sid'])){ //只取一行数据
		$condition .= " And id = '{$_GPC['one_sid']}'";
	}
	if(!empty($_GPC['keyword'])){
		$condition .= " And s_name LIKE '%{$_GPC['keyword']}%'";
	}
	if(!empty($_GPC['bd_type'])){
		if($_GPC['bd_type'] == 1){
			$condition .= " And (ouserid != 0 Or muserid != 0 Or duserid != 0 Or otheruserid != 0)";
		}
		if($_GPC['bd_type'] == 2){
			$condition .= " And ouserid = 0 And muserid = 0 And duserid = 0 And otheruserid = 0 ";
		}				
	}
	if($_GPC['status'] != -1){
		$condition .= " And status = '{$_GPC['status']}'";
	}
	if(!empty($_GPC['nj_id'])){
		$condition .= " And xq_id = '{$_GPC['nj_id']}'";
	}
	if(!empty($_GPC['s_type'])){
		$condition .= " And s_type = '{$_GPC['s_type']}'";
	}
	if(!empty($_GPC['bj_id'])){
		$condition .= " And bj_id = '{$_GPC['bj_id']}'";
	}
	if(!empty($_GPC['kc_id'])){
		$allbuysid = pdo_fetchall("SELECT distinct sid FROM ".tablename($this->table_order)." where kcid = '{$_GPC['kc_id']}' And type = 1 And status = 2 And sid != 0 ");
		$sidlist = '';
		foreach($allbuysid as $vas){
			$sidlist .= $vas['sid'].",";
		}
		$sidlist = rtrim($sidlist,',');
		$condition .= " AND FIND_IN_SET(id,'{$sidlist}') ";
	} 
	$lists = pdo_fetchall("SELECT s_name,code,mobile,numberid,bj_id FROM " . tablename($this->table_students) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' $condition ORDER BY id DESC");
		$ii   = 0;
		foreach($lists as $index => $row){
			$bj                = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where sid = '{$row['bj_id']}'");
			$arr[$ii]['s_name'] = trim($row['s_name']);
			$arr[$ii]['code']  = $row['code'];
			$arr[$ii]['mobile']  = $row['mobile'];
			$arr[$ii]['numberid']  = $row['numberid'];
			$arr[$ii]['banji']  = $bj['sname'];
			$ii++;
		}
		$this->exportexcel($arr, array('学生', '绑定码', '报名预留手机号', '学号', '班级'), '学生绑定信息表');
		exit();
}elseif($operation == 'OutStuInfo'){
	$listss = pdo_fetchall("SELECT * FROM " . tablename($this->table_students) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' ORDER BY id DESC");
		$ii   = 0;
		foreach($listss as $index => $row){
			$arr[$ii]['s_name'] = $row['s_name'];
			if($row['sex'] == 1){
				$arr[$ii]['sex'] = '男';
			}else{
				$arr[$ii]['sex'] = '女';
			}
			$infocard = json_decode($row['infocard'],true);
			$arr[$ii]['numberid'] = $row['idcard'];
			$this_bj = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}'and sid = '{$row['bj_id']}'");
			
			$arr[$ii]['bj_name'] = $this_bj['sname'];
			$arr[$ii]['idcard'] = $infocard['IDcard'];
			$arr[$ii]['nation'] = $infocard['Nation'];
			$arr[$ii]['birthdate'] = date("Y-m-d",$row['birthdate']);
			$arr[$ii]['seffectivetime'] = date("Y-m-d",$row['seffectivetime']);
			$arr[$ii]['address'] = $row['area_addr'];
			$arr[$ii]['NowAddress'] = $infocard['NowAddress'];
			$arr[$ii]['HomeChild'] = $infocard['HomeChild']?'是':'否';
			$arr[$ii]['SingleFamily'] = $infocard['SingleFamily']?'是':'否';
			$arr[$ii]['IsKeep'] = $infocard['IsKeep']?'是':'否';
			if($infocard['DayOrWeek'] == 1){
				$arr[$ii]['IsKeep'] .= ' | 午托';
			}elseif($infocard['DayOrWeek'] == 2){
				$arr[$ii]['DayOrWeek']  .= ' | 周托';
			}
			$Finfo = '【学历】'.$infocard['Fxueli'].' 【职业】'.$infocard['Fwork'].' 【爱好】'.$infocard['Fhobby'].' 【工作单位】'.$infocard['FWorkPlace'];
			$Minfo = '【学历】'.$infocard['Mxueli'].' 【职业】'.$infocard['Mwork'].' 【爱好】'.$infocard['Mhobby'].' 【工作单位】'.$infocard['MWorkPlace'];
			$GrandFinfo = '【学历】'.$infocard['GrandFxueli'].' 【职业】'.$infocard['GrandFwork'].' 【爱好】'.$infocard['GrandFhobby'].' 【工作单位】'.$infocard['GrandFWorkPlace'];
			$GrandMinfo = '【学历】'.$infocard['GrandMxueli'].' 【职业】'.$infocard['GrandMwork'].' 【爱好】'.$infocard['GrandMhobby'].' 【工作单位】'.$infocard['GrandMWorkPlace'];
			$WGrandFinfo = '【学历】'.$infocard['WGrandFxueli'].' 【职业】'.$infocard['WGrandFwork'].' 【爱好】'.$infocard['WGrandFhobby'].' 【工作单位】'.$infocard['WGrandFWorkPlace'];
			$WGrandMinfo = '【学历】'.$infocard['WGrandMxueli'].' 【职业】'.$infocard['WGrandMwork'].' 【爱好】'.$infocard['WGrandMhobby'].' 【工作单位】'.$infocard['WGrandMWorkPlace'];
			$Otherinfo = '【学历】'.$infocard['Otherxueli'].' 【职业】'.$infocard['Otherwork'].' 【爱好】'.$infocard['Otherhobby'].' 【工作单位】'.$infocard['OtherWorkPlace'];
			$arr[$ii]['Finfo'] = $Finfo;
			$arr[$ii]['Minfo'] = $Minfo;
			$arr[$ii]['GrandFinfo'] = $GrandFinfo;
			$arr[$ii]['GrandMinfo'] = $GrandMinfo;
			$arr[$ii]['WGrandFinfo'] = $WGrandFinfo;
			$arr[$ii]['WGrandMinfo'] = $WGrandMinfo;
			$arr[$ii]['Otherinfo'] = $Otherinfo;
			
			$mainwatch = json_decode($infocard['MainWatcharr']);
			$watchmans = '';
			if(!empty($infocard['MainWatcharr']) && $infocard['MainWatcharr'] != 'null'){
			
				foreach($mainwatch as $row){
					if($row == 1){
						$watchmans .=' 父亲 |';
					}
					if($row == 2){
						$watchmans .=' 母亲 |';
					}
					if($row == 3){
						$watchmans .=' 爷爷 |';
					}
					if($row == 4){
						$watchmans .=' 奶奶 |';
					}
					if($row == 5){
						$watchmans .=' 外公 |';
					}
					if($row == 6){
						$watchmans .=' 外婆 |';
					}
					if($row == 7){
						$watchmans .=' 其他 |';
					}
				}
				$watchmans = trim($watchmans,'|'); 
			}
			$arr[$ii]['watchmans'] = $watchmans;
			$arr[$ii]['Childhobby'] = $infocard['Childhobby'];
			$arr[$ii]['ChildWord'] = $infocard['ChildWord'];
			$arr[$ii]['SchoolWord'] = $infocard['SchoolWord'];

			$ii++;
		}
		$this->exportexcel($arr, array('姓名','性别','学号','班级','身份证','民族','出生年月','入学时间','家庭住址','现住址','是否留守儿童','是否单亲家庭','是否托管','父亲','母亲','爷爷','奶奶','外公','外婆','其他','监护人','孩子爱好','对孩子的期望','对学校的期望'), '学生详细信息');
		exit();
}elseif($operation == 'delOverStu'){
	$bj_id = $_GPC['bj_id'];
	pdo_delete(GetTableName('students',false),array('schoolid' => $schoolid,'bj_id'=>$bj_id));
	$result['msg'] = "删除成功";
	die(json_encode($result));
}

if($operation == 'AdvGetStuList'){
	$NjId = $_GPC['NjId'];
	$schoolid = $_GPC['schoolid'];
	if($NjId != -1 && $NjId != 0){
		$StuList = pdo_fetchall("SELECT id,s_name,bj_id FROM ".GetTableName('students')." WHERE schoolid = '{$schoolid}' and xq_id = '{$NjId}' ");
		$BjList = pdo_fetchall("SELECT sid,sname FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and parentid = '{$NjId}' ");
		$HasNone = pdo_fetch("SELECT id FROM ".GetTableName('students')." WHERE schoolid = '{$schoolid}' and xq_id = '{$NjId}' and bj_id = 0 ");
	}
	if($NjId == -1){
		$StuList = pdo_fetchall("SELECT id,s_name FROM ".GetTableName('students')." WHERE schoolid = '{$schoolid}' and xq_id = 0 ");
	}
	include $this->template('web/stu/adv_class');
	die();

}elseif($operation == 'batchFb'){ //批量分班
	$num = 0;
	foreach ($_GPC['sid'] as $id) {
		//批量修改考勤卡
		if(!$_W['schooltype']){
			$idcard = pdo_fetch("SELECT id FROM ".GetTableName('idcard')." WHERE sid = '{$id}' ");
			if(!empty($idcard)){
				pdo_update(GetTableName('idcard',false),array('bj_id'=>$_GPC['Adv_bjSelect']),array('sid'=>$id));
			}
		}
		//修改视频画面到对应的班级
		ChangeVideoWithBj($schoolid,$id,$_GPC['Adv_bjSelect']);
		pdo_update(GetTableName('students',false),array('xq_id'=>$_GPC['Adv_njSelect'],'bj_id'=>$_GPC['Adv_bjSelect']),array('id'=>$id));
		$num++;
	}
	$result['msg'] = "总共修改了{$num}数据";
	die(json_encode($result));
}elseif($operation == 'batchBangding'){ //批量修改班级绑定码
	$num = 0;
	pdo_update(GetTableName('students',false),array('code'=>$_GPC['code']),array('xq_id'=>$_GPC['bangding_nj'],'bj_id'=>$_GPC['bangding_bj']));
	$result['msg'] = "修改成功";
	die(json_encode($result));
}elseif($operation == 'lsChange'){
	pdo_update(GetTableName('students',false),array('isopen'=>$_GPC['isopen']),array('id'=>$_GPC['sid']));
}
if($operation == 'syncstuinfo'){
	mload()->model('xzf'); 
	$total = $_GPC['total'];
	$page = $_GPC['page'];
	$res = SyncStudent($schoolid,$total,$page);
	die(json_encode($res));
}

include $this->template('web/students');
