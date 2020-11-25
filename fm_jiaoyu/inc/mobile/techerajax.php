<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
   global $_W, $_GPC;  
   $operation = in_array ( $_GPC ['op'], array ('default','get_stu_info','change_msg','delstu','set_stu_info','search_stu_info','jzjb','set_myinfo','any_fanslist','get_plate_num','set_plate_num','edit_visitors','get_bj_send_set','set_bj_send_set', 'refuse_visitors','checkVisData','edit_stu_info','get_oneorderinfo','get_onkc_kslist','set_oneks_issign','kc_vislog','essssssd','FpTgy','AddPhotoType','AddMC','EditMC','GetOneMC','search_stu_card_info') ) ? $_GPC ['op'] : 'default';

    if ($operation == 'default') {
	   die ( json_encode ( array (
			 'result' => false,
			 'msg' => '参数错误'
			) ) );		
    }
	if ($operation == 'essssssd') {
		pdo_update(GetTableName('order',false),array('superior_tid' => $_GPC['tid']),array('id' => $_GPC['id']));
	}
	if ($operation == 'any_fanslist') {
		$list = pdo_fetchall("SELECT nickname,avatar,uid FROM " . tablename('mc_members') . " where avatar != '' And nickname != '' And uniacid = '{$_W['uniacid']}' ORDER BY RAND() limit 0,5");
		foreach($list as $key => $row){
			$checkopenid = pdo_fetch("SELECT openid FROM " . tablename('mc_mapping_fans') . " where uid = '{$row['uid']}' And uniacid = '{$_W['uniacid']}' ");
			$list[$key]['openid'] = $checkopenid['openid'];
			$fansinfo = mc_fansinfo($checkopenid['openid']);
			if(empty($fansinfo['headimgurl'])){
				unset($list[$key]);
			}
		}
		include $this->template('comtool/anyfans_list');
	}
	if ($operation == 'jzjb') {
		if (! $_GPC ['schoolid']) {
		    die ( json_encode ( array (
				'result' => false,
				'msg' => '非法请求！' 
			) ) );
	    }
		$user = pdo_fetch("SELECT id,pard,openid,sid FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :id = id", array(':schoolid' => $_GPC ['schoolid'], ':id'=>$_GPC ['userid']));
		if (empty($user)) {
			die ( json_encode ( array (
				'result' => false,
				'msg' => '非法请求，没找用户信息！' 
			) ) );
		}				  
		if (empty($user['openid'])) {
                  die ( json_encode ( array (
                    'result' => false,
                    'msg' => '非法请求！' 
		               ) ) );
		}else{
			$student = pdo_fetch("SELECT keyid FROM " . tablename($this->table_students) . " where :schoolid = schoolid And :id = id", array(':schoolid' => $_GPC ['schoolid'],':id'=>$user ['sid']));
			if($student['keyid'] != '0' ){
				$otherStu = pdo_fetchall("SELECT * FROM " . tablename($this->table_students) . " where :schoolid = schoolid And :weid = weid And :keyid = keyid", array(
	         	':weid' => $_W ['weid'],
			 	':schoolid' => $_GPC ['schoolid'],
			 	':keyid'=>$student ['keyid']
			  	));
			  	
			  	foreach( $otherStu as $key => $value ){
					$thisuser = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :sid = sid And :openid = openid", array(':schoolid' => $_GPC ['schoolid'],':sid' => $value ['id'],':openid'=>$user ['openid']));
			  		if($user['pard'] == 2){
						$temp = array( 
						    'mom' => 0,
							'muserid' => 0,
							'muid'=> 0
					    );
					}
					if($user['pard'] == 3){
						$temp = array(
						    'dad' => 0,
							'duserid' => 0,
							'duid'=> 0
						    );
					}
					if($user['pard'] == 4){
						$temp = array(
						    'own' => 0,
							'ouserid' => 0,
							'ouid'=> 0
						    );
					}
					if($user['pard'] == 5){
						$temp = array(
						    'other' => 0,
							'otheruserid' => 0,
							'otheruid'=> 0
						    );
					}
		            pdo_update($this->table_students, $temp, array('id' => $value['id']));
					pdo_delete($this->table_leave, array('userid' => $thisuser['id']));
					pdo_delete($this->table_camerapl, array('userid' => $thisuser['id']));
					pdo_delete($this->table_bjq, array('userid' => $thisuser['id']));
					pdo_delete($this->table_user, array('id' => $thisuser['id']));
			  	}				
			}else{
				if($user['pard'] == 2){
					$temp = array( 
					    'mom' => 0,
						'muserid' => 0,
						'muid'=> 0
				    );
				}
				if($user['pard'] == 3){
					$temp = array(
					    'dad' => 0,
						'duserid' => 0,
						'duid'=> 0
				    );
				}
				if($user['pard'] == 4){
					$temp = array(
					    'own' => 0,
						'ouserid' => 0,
						'ouid'=> 0
				    );
				}
				if($user['pard'] == 5){
					$temp = array(
					    'other' => 0,
						'otheruserid' => 0,
						'otheruid'=> 0
				    );
				}
		        pdo_update($this->table_students, $temp, array('id' => $user['sid']));			   
		        pdo_delete($this->table_user, array('id' => $user['id']));	
				pdo_delete($this->table_leave, array('userid' => $user['id']));
				pdo_delete($this->table_camerapl, array('userid' => $user['id']));
				pdo_delete($this->table_bjq, array('userid' => $user['id']));				
			}
			$data ['result'] = true;
			$data ['msg'] = '解绑成功！';
		 die ( json_encode ( $data ) );
		}
    }
	if ($operation == 'kc_vislog') {
		if (! $_GPC ['schoolid']) {
            $result ['result'] = false;
			$result ['msg'] = '非法请求';	
	    }else{
			$weid = $_GPC ['weid'];
			$schoolid = $_GPC ['schoolid'];
			$kcid = $_GPC ['kcid'];
			$sid = $_GPC ['sid'];
			$tid = $_GPC ['tid'];
			if($_GPC['type'] == 'add'){
				$data = array(
					'weid'       => $weid,
					'schoolid'   => $schoolid,
					'sid'        => $sid,
					'kcid'       => $kcid,
					'log'		 => trim($_GPC['content']),
					'tid'      	 => $tid,
					'createtime' => strtotime($_GPC['time']),
				);
				pdo_insert(GetTableName('kc_vislog',false), $data);
				$result ['result'] = true;
				$result ['msg'] = '新增成功';
			}
			$school = pdo_fetch("select tpic from " . GetTableName('index') . " where id = :id ", array(':id' => $schoolid));
			if($_GPC['type'] == 'list'){
				$list = pdo_fetchall("select * FROM " . GetTableName('kc_vislog') . " WHERE sid = '{$sid}' And kcid = '{$kcid}' ORDER BY createtime DESC ");
				foreach($list as $key =>$row){
					$teainfo = pdo_fetch("select tname,thumb from " . GetTableName('teachers') . " where id = :id ", array(':id' => $row['tid']));
					$list[$key]['tname'] = $teainfo['tname'];
					$list[$key]['thumb'] = $teainfo['thumb']?tomedia($teainfo['thumb']):tomedia($school['tpic']);
					$list[$key]['time']  = date('Y.m.d H:i',$row['createtime']);
				}
				$stuinfo = pdo_fetch("select s_name,mobile,seffectivetime from " . GetTableName('students') . " where id = :id ", array(':id' => $sid));
				$stuinfo['bmtime'] = date('Y.m.d',$stuinfo['seffectivetime']);
				$result ['stuinfo'] = $stuinfo;
				$result ['list'] = $list;
				$result ['result'] = true;
			}
			$result ['type'] = $_GPC['type'];
		}
		die ( json_encode ( $result ) );
	}
	if ($operation == 'set_oneks_issign') {
		if (! $_GPC ['schoolid']) {
            $result ['result'] = false;
			$result ['msg'] = '非法请求';	
	    }else{
			$weid = $_GPC ['weid'];
			$schoolid = $_GPC ['schoolid'];
			$ksid = $_GPC ['ksid'];
			$kcid = $_GPC ['kcid'];
			$sid = $_GPC ['sid'];
			$tid = $_GPC ['tid'];
			$kcinfo = pdo_fetch("select OldOrNew,is_print_xk from " . GetTableName('tcourse') . " where id = :id ", array(':id' => $kcid));
			$kcsign = pdo_fetch("select id,status from " . GetTableName('kcsign') . " where ksid = :ksid And sid = :sid ", array(':ksid' => $ksid,':sid' => $sid));
			$checkKS = pdo_fetch("select costnum FROM " . GetTableName('kcbiao') . " WHERE id = '{$ksid}' ");
			$data = array(
				'kcid'       => $kcid,
				'ksid'       => $ksid,
				'schoolid'   => $schoolid,
				'weid'       => $weid,
				'sid'        => $sid,
				'createtime' => time(),
				'status'     => 2,
				'qrtid'      => $tid,
				'type'       => $kcinfo['OldOrNew'],
				'kcname'     => $kcinfo['name'],
				'costnum'    => $checkKS['costnum'],
				'signtime'   => time(),
			);
			if(!empty($kcsign)){
				if($kcsign['status'] == 2){
					$result ['result'] = false;
					$result ['msg'] = '本节已签,请勿重复签到';
				}else{
					unset($data['createtime']);
					pdo_update(GetTableName('kcsign',false), $data,array('id' => $kcsign['id']));
					$signid = $kcsign['id'];
					$result ['msg'] = '签到成功';
					$result ['result'] = true;
				}
			}else{
				pdo_insert(GetTableName('kcsign',false), $data);
				$signid = pdo_insertid();
				$result ['msg'] = '签到成功';
				$result ['result'] = true;
			}
            if($kcinfo['is_print_xk'] == 1 && $result ['result']){
                mload()->model('print');
                KsCheck_print($signid,$schoolid,$weid);
            }
		}
		die ( json_encode ( $result ) );
	}
	if ($operation == 'get_onkc_kslist') {
		if (! $_GPC ['schoolid']) {
            $result ['result'] = false;
			$result ['msg'] = '非法请求';	
	    }else{
			$weid = $_GPC ['weid'];
			$schoolid = $_GPC ['schoolid'];
			$kcid = $_GPC ['kcid'];
			$sid = $_GPC ['sid'];
			$student = pdo_fetch("select * from " . GetTableName('students') . " where id = :id ", array(':id' => $sid));
			$kcinfo = pdo_fetch("select * from " . GetTableName('tcourse') . " where id = :id ", array(':id' => $kcid));
			$kslist = pdo_fetchall("SELECT id FROM " . GetTableName('kcbiao') . " WHERE schoolid = '{$schoolid}' And kcid = '{$kcid}' ORDER BY date ASC ");
			if($kcinfo && $student && $kslist){
				mload()->model('kc');
				foreach($kslist as $key => $row){
					$kslist[$key]['order'] = GetOneKcKsOrder($kcid,$row['id']);
					$kcsign = pdo_fetch("select id from " . GetTableName('kcsign') . " where ksid = :ksid And sid = :sid And status = 2 ", array(':ksid' => $row['id'],':sid' => $sid));
					$kslist[$key]['sign'] = false;
					if($kcsign){
						$kslist[$key]['sign'] = true;
					}
				}
				$signlist = pdo_fetchall("SELECT ksid FROM " . GetTableName('kcsign') . " WHERE kcid = '{$kcid}' And sid = '{$sid}' And status = 2 ");
				if($signlist){
					foreach($signlist as $k => $v){
						$signlist[$k]['order'] = GetOneKcKsOrder($kcid,$v['ksid']);
					}
				}
				$result ['signlist'] = $signlist;
				$result ['kslist'] = $kslist;
				$result ['result'] = true;
				$result ['msg'] = '获取信息成功';				
			}else{
				$result ['result'] = false;
				if(empty($kcinfo)){
					$result ['msg'] = '查询不到本课';
				}
				if(empty($student)){
					$result ['msg'] = '查询不到本学生信息';
				}
				if(empty($kslist)){
					$result ['msg'] = '当前课程无排课信息,请联系排课老师';
				}
			}
		}
		die ( json_encode ( $result ) );	
	}
	if ($operation == 'get_oneorderinfo')  {
		if (! $_GPC ['schoolid']) {
            $result ['result'] = false;
			$result ['msg'] = '非法请求';	
	    }else{
			$order = pdo_fetch("select * from " . GetTableName('order') . " where id = :id ", array(':id' => $_GPC ['orderid']));
			$student = pdo_fetch("select * from " . GetTableName('students') . " where id = :id ", array(':id' => $order ['sid']));
			$kcinfo = pdo_fetch("select * from " . GetTableName('tcourse') . " where id = :id ", array(':id' => $order ['kcid']));
			if($student){
				$result ['kcinfo'] = $kcinfo;
				$result ['student'] = $student;	
				$result ['order'] = $order;	
				$result ['result'] = true;
				$result ['msg'] = '获取信息成功';				
			}else{
				$result ['result'] = false;
				$result ['msg'] = '获取信息失败';					
			}
		}
		die ( json_encode ( $result ) );
	}
    if ($operation == 'get_stu_info')  {
		global $_W, $_GPC;
		if (! $_GPC ['schoolid']) {
            $datass ['result'] = false;
			$datass ['msg'] = '非法请求';	
	    }else{
			$student = pdo_fetch("select * from " . tablename($this->table_students) . " where id = :id ", array(':id' => $_GPC ['sid']));
			if($student){
				$qrurl = pdo_fetch("SELECT show_url,expire FROM " . tablename($this->table_qrinfo) . " WHERE id = '{$student['qrcode_id']}'");
				$datass ['stuinfo'] = $student;
				$datass ['stuinfo']['birthday'] = date('Y-m-d',$student['birthdate']);
				if($student['sex'] == 1){
					$datass ['stuinfo']['sex'] = '男';
				}else{
					$datass ['stuinfo']['sex'] = '女';
				}
				$datass ['sid'] = $student['id'];
				$datass ['sex'] = $student['sex'];
				$datass ['mobile'] = $student['mobile'];
				$datass ['s_name'] = $student['s_name'];
				$datass ['numberid'] = $student['numberid'];
				$datass ['area_addr'] = $student['area_addr'];
				$datass ['code'] = $student['code'];
				$datass ['overtime'] = true;
				if($qrurl['expire'] > time()){
					$datass ['overtime'] = false;
					$datass ['ercode'] = tomedia($qrurl['show_url']);
				}
				$family = pdo_fetchall("SELECT id,uid,pard,status,realname,mobile FROM " . tablename($this->table_user) . " WHERE sid = '{$student['id']}'");
				if($family){
					foreach($family as $key => $row){
						$member = pdo_fetch("SELECT avatar FROM " . tablename ( 'mc_members' ) . " where uniacid = :uniacid And uid = :uid ", array(':uniacid' => $_W['uniacid'], ':uid' => $row['uid']));
						$family[$key]['avatar'] = $member['avatar'];
						if($row['realname']){
							$family[$key]['username'] = $row['realname'];
						}
						if($row['pard'] == 4){
							$family[$key]['pard'] = '本人';
						}else{
							$family[$key]['pard'] = get_guanxi($row['pard']);
						}
						if($row['status'] == 1){
							$family[$key]['ischeck'] = '';
							$family[$key]['isjy'] = '禁言中';
						}else{
							$family[$key]['ischeck'] = 'checked';
							$family[$key]['isjy'] = '允许发言';
						}
					}
				}
				$datass ['family'] = $family;
				$datass ['result'] = true;
				$datass ['msg'] = '获取信息成功';				
			}else{
				$datass ['result'] = false;
				$datass ['msg'] = '获取信息失败';					
			}
		}
		die ( json_encode ( $datass ) );
    }
	if ($operation == 'edit_stu_info')  {//推广员编辑或新增学生
		if(empty($_GPC ['schoolid'])) {
			$result ['result'] = false;
			$result ['msg'] = '非法请求';
	    }else{
			$randStr = str_shuffle('123456789');
			$rand    = substr($randStr, 0, 6);
			if(!empty($_GPC['code'])){
				$rand = $_GPC['code'];
			}
			$data = array(
				'weid' 	 	=> intval($_GPC['weid']),
				'schoolid'  => intval($_GPC['schoolid']),
				's_name' => trim($_GPC['bName']),
				'birthdate' => strtotime($_GPC['bDay']),
				'code' => $rand,
				'superior_tid' => intval($_GPC['tid']),
				'mobile' => $_GPC['bParentNumber'],
				'sex' => $_GPC['bSex'] == '男'? 1 : 2,
				'province' => trim($_GPC['bPName']),
				'city' => trim($_GPC['bCName']),
				'county' => trim($_GPC['bAName']),
				'area_addr' => trim($_GPC['bStuAddress']),
				'createdate' => time(),
				'seffectivetime' => time(),
				'status' => 1
			);
			if($_GPC['sid'] > 0){
				unset($data['status']);
				unset($data['createdate']);
				unset($data['seffectivetime']);
				pdo_update(GetTableName('students',false),$data,array('id' => $_GPC['sid']));
				$sid = $_GPC['sid'];
				$result ['msg'] = '编辑成功';
			}else{
				$data['from_kcid'] = $_GPC['kcid'];
				pdo_insert(GetTableName('students',false),$data);
				$sid = pdo_insertid();
				$kcinfo = pdo_fetch("SELECT cose,FirstNum,payweid FROM " . GetTableName('tcourse') . " where id = :id ", array(':id' => $_GPC['kcid']));
				$payweid = $kcinfo['payweid'] ? $kcinfo['payweid'] : intval($_GPC['weid']);
				$orderdata = array(
				   'weid' => intval($_GPC['weid']),
				   'schoolid' => intval($_GPC['schoolid']),
				   'kcid' => intval($_GPC['kcid']),
				   'superior_tid' => intval($_GPC['tid']),
				   'userid' => 0,
				   'sid' => $sid,
				   'cose' => $kcinfo['cose'],
				   'status' => 1,
				   'type' => 1,
				   'ksnum' => $kcinfo['FirstNum'],
				   'payweid' => $payweid,
				   'createtime' => time(),
				   'new_stu' => 1
				);
				pdo_insert(GetTableName('order',false),$orderdata);
				$orderid = pdo_insertid();
				$result ['msg'] = '新增成功,请生成二维码发送给学生绑定';
			}
			$result ['orderid'] = $orderid;
			$result ['result'] = true;
			$result ['sid'] = $sid;
		}
		die ( json_encode ( $result ) );
	}
    if ($operation == 'change_msg')  {
		if (! $_GPC ['schoolid']) {
               die ( json_encode ( array (
                    'result' => false,
                    'msg' => '非法请求！' 
		               ) ) );
	    }else{
			$user = pdo_fetch("select status from " . tablename($this->table_user) . " where id = :id ", array(':id' => $_GPC ['id']));
			if($user){
				if($user['status'] == 1){
					pdo_update($this->table_user, array('status' => 0), array('id' =>$_GPC ['id']));
					$datass ['msg'] = '允许发言';
				}else{
					pdo_update($this->table_user, array('status' => 1), array('id' =>$_GPC ['id']));
					$datass ['msg'] = '禁言成功';
				}
				$datass ['result'] = true;
			}else{
				$datass ['result'] = false;
				$datass ['msg'] = '修改失败';					
			}
		}
		die ( json_encode ( $datass ) );
    }
    if ($operation == 'delstu')  {
		if (! $_GPC ['schoolid']) {
               die ( json_encode ( array (
                    'result' => false,
                    'msg' => '非法请求！' 
		               ) ) );
	    }else{
			$student = pdo_fetch("select id,qrcode_id from " . tablename($this->table_students) . " where id = :id ", array(':id' => $_GPC ['sid']));
			if($student){
				pdo_delete($this->table_user,array('sid' =>$student['id']));
				pdo_delete($this->table_qrinfo,array('id' =>$student['qrcode_id']));
				pdo_delete($this->table_students,array('id' =>$student['id']));
				$datass ['result'] = true;
				$datass ['msg'] = '删除成功';					
			}else{
				$datass ['result'] = false;
				$datass ['msg'] = '无法查询到本学生';					
			}
		}
		die ( json_encode ( $datass ) );
    }
    if ($operation == 'set_stu_info')  {
		if (! $_GPC ['schoolid']) {
               die ( json_encode ( array (
                    'result' => false,
                    'msg' => '非法请求！' 
		               ) ) );
	    }else{
			$school = pdo_fetch("select is_stuewcode,spic from " . tablename($this->table_index) . " where id = :id ", array(':id' => $_GPC ['schoolid']));
			$xq_id = pdo_fetch("select parentid,sname from " . tablename($this->table_classify) . " where sid = :sid ", array(':sid' => $_GPC ['bj_id']));
			if($_GPC['sid']){
				$student = pdo_fetch("select id,icon,qrcode_id from " . tablename($this->table_students) . " where id = :id ", array(':id' => $_GPC ['sid']));
				if($student){
					$pard = pdo_fetchall("SELECT pard FROM ".tablename($this->table_user)." WHERE schoolid = '{$_GPC['schoolid']}' And sid = '{$student['id']}' ");
					if($pard){
						foreach($pard as $k => $v){
							$pard[$k]['pardid'] = $v['pard'];
							if($v['pard'] == 4){
								$pard[$k]['guanxi'] = "本人";
							}else{
								$pard[$k]['guanxi'] = get_guanxi($v['pard']);
							}
						}
					}					
					$temp = array(
						's_name' 	=> trim($_GPC ['s_name']),
						'sex' 	 	=> intval($_GPC ['sex']),
						'mobile' 	=> trim($_GPC ['mobile']),
						'area_addr' => trim($_GPC ['area_addr']),
						'numberid'  => trim($_GPC ['numberid']),
						'code'      => trim($_GPC ['code'])
					);
					$sid = $student['id'];
					pdo_update($this->table_students, $temp, array('id' =>$_GPC ['sid']));
					include $this->template('comtool/newstulist');
				}else{
					$datass ['result'] = false;
					$datass ['msg'] = '未查询到学生信息,请刷新本页';
					die ( json_encode ( $datass ) );	
				}
			}else{
				if(!$_GPC['code']){
					$randStr = str_shuffle('123456789');
					$rand    = substr($randStr, 0, 6);
				}else{
					$rand = trim($_GPC['code']);
				}							
				$temp = array(
					'weid' 		=> $_W ['uniacid'],
					'schoolid' 	=> trim($_GPC ['schoolid']),
					's_name' 	=> trim($_GPC ['s_name']),
					'sex' 	 	=> intval($_GPC ['sex']),
					'mobile' 	=> trim($_GPC ['mobile']),
					'bj_id' 	=> trim($_GPC ['bj_id']),
					'xq_id' 	=> $xq_id['parentid'],
					'area_addr' => trim($_GPC ['area_addr']),
					'numberid'  => trim($_GPC ['numberid']),
					'seffectivetime' => time(),
					'code'           => $rand
				);
				pdo_insert($this->table_students, $temp);
                $sid = pdo_insertid();
                if(keep_wt() && CheckWtOn($_GPC['schoolid'])) {
                    mload()->model('wt');
                    //人员录入
                    $param['idcardNo'] = $sid;
                    $param['name']     = trim($_GPC ['s_name']);
                    $result            = personAction($_GPC['schoolid'], $_W['uniacid'], time(), $param, 'insert');
                    if($result['result'] == '1'){
                        $guid =$result['data']['guid'];
                        pdo_update(GetTableName('students',false),array('guid'=>$guid),array('id'=>$sid));
                        $result_device =  People2Device($_GPC['schoolid'], $_W['uniacid'], time(),$guid);
                        if($result_device['result'] != '1') {
                            $back_msg = CheckWtReturnCode($result_device['code']);
                        }
                    }else{
                        $back_msg = CheckWtReturnCode($result['code']);
                    }
                }


				if($school['is_stuewcode'] == 1){
					load()->func('tpl');
					load()->func('file');
					if(empty($school['spic'])){
						$datass ['result'] = false;
						$datass ['msg'] = '创建失败,联系管理员设置校园默认头像';
						 die ( json_encode ( $datass ) );
					}
					$barcode = array(
						'expire_seconds' =>2592000 ,
						'action_name' => '',
						'action_info' => array(
							'scene' => array(
									'scene_id' => $sid
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
						$showurl = $this->createImageUrlCenterForUser("https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=" . $result['ticket'], $sid, 0, $_GPC ['schoolid']);
						$urlarr = explode('/',$showurl);
						$qrurls = "images/fm_jiaoyu/".$urlarr['4'];	
						$insert = array(
							'weid' => $_W['uniacid'],
							'schoolid' => $_GPC['schoolid'],
							'qrcid' => $sid, 
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
				}
				$temps = array(
					'keyid'    => $sid,
					'qrcode_id'=> $qrid,
				);
				pdo_update($this->table_students, $temps, array('id' =>$sid));
				include $this->template('comtool/newstulist');
			}
		}
    } 
	if ($operation == 'search_stu_info')  {
		$bj_id = trim($_GPC['bj_id']);
		$kc_id = trim($_GPC['kc_id']);
		$search = trim($_GPC['search']);
		$schoolid = trim($_GPC['schoolid']);
		$condition = " AND (s_name LIKE '%{$search}%' Or mobile = '{$search}' Or numberid = '{$search}') ";	
		if($_GPC['schoolType'] == 1){
			$thisKcStu = pdo_fetchall("SELECT distinct sid FROM " . tablename($this->table_order) . " where schoolid = '{$schoolid}' And kcid = '{$kc_id}' and type='1' and sid != 0 ORDER BY id DESC ");
			$Stu_str_temp = '';
			foreach($thisKcStu as $u){
				$Stu_str_temp .=$u['sid'].",";
			}
			$stu_str = trim($Stu_str_temp,",");
			$leave2 = pdo_fetchall("SELECT id,s_name,numberid,qrcode_id,bj_id,sex,icon FROM " . tablename($this->table_students) . " where schoolid = '{$schoolid}' And FIND_IN_SET(id,'{$stu_str}') $condition ORDER BY id DESC ");
		}elseif($_GPC['schoolType'] == 2){
			$leave2 = pdo_fetchall("SELECT id,s_name,numberid,qrcode_id,bj_id,sex,icon FROM " . tablename($this->table_students) . " where schoolid = '{$schoolid}' And bj_id = '{$bj_id}' $condition ORDER BY id DESC ");
		}

		$school = pdo_fetch("SELECT spic FROM " . tablename($this->table_index) . " where id = :id ", array(':id' => $schoolid));
		foreach($leave2 as $key =>$row){
			$banji = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where sid = :sid And schoolid = :schoolid ", array(':schoolid' => $schoolid,':sid' => $row['bj_id']));
			$leave2[$key]['banji'] = $banji['sname'];
			$leave2[$key]['pard'] = pdo_fetchall("SELECT pard FROM ".tablename($this->table_user)." WHERE schoolid = '{$schoolid}' And sid = '{$row['id']}' ");
			$yq = pdo_fetchcolumn("SELECT count(*) FROM " . tablename($this->table_kcsign) . " where schoolid = '{$schoolid}' And sid = {$row['id']} And kcid = '{$kc_id}' And status = 2 ");
			$buy = pdo_fetchcolumn("SELECT ksnum FROM " . tablename($this->table_coursebuy) . " where schoolid = '{$schoolid}' And sid = {$row['id']} And kcid = '{$kc_id}' ");
			$leave2[$key]['yq'] = $yq;
			$leave2[$key]['buy'] =$buy?$buy:0;
			$rest = $leave2[$key]['buy'] - $yq;
			$leave2[$key]['rest'] = ($rest>= 0)?$rest:0;
			if($leave2[$key]['pard']){
				foreach($leave2[$key]['pard'] as $k => $v){
					$leave2[$key]['pard'][$k]['pardid'] = $v['pard'];
					if($v['pard'] == 4){
						$leave2[$key]['pard'][$k]['guanxi'] = "本人";
					}else{
						$leave2[$key]['pard'][$k]['guanxi'] = get_guanxi($v['pard']);
					}
				}
			}
		}
		include $this->template('comtool/stulist');		
	}



	if ($operation == 'set_myinfo')  {
		if (! $_GPC ['schoolid']) {
               die ( json_encode ( array (
                    'result' => false,
                    'msg' => '非法请求！' 
		               ) ) );
	    }else{
			$data = array(
				'tname'=>$_GPC['tname'],
				'sex'  =>$_GPC['sex'],
				'birthdate' =>strtotime($_GPC['birthdate']),
				'idcard' =>$_GPC['idcard'],
				'jiguan' =>$_GPC['jiguan'],
				'minzu'	 => $_GPC['minzu'],
				'zzmianmao' =>$_GPC['zzmianmao'],
				'address' =>$_GPC['address'],
				'mobile' =>$_GPC['mobile'],
				'email' =>$_GPC['email'],
			);
			$otherinfo = array(
				'first_xl'     	=> $_GPC['first_xl'],
				'first_zy'     	=> $_GPC['first_zy'],
				'first_yx'     	=> $_GPC['first_yx'],
				'first_bytime' 	=> $_GPC['first_bytime'],
				'top_xl'       	=> $_GPC['top_xl'],
				'top_zy'       	=> $_GPC['top_zy'],
				'top_yx'       	=> $_GPC['top_yx'],
				'top_bytime'   	=> $_GPC['top_bytime'],
				'main_study_jl' => $_GPC['main_study_jl'],
				'time2work' 	=> $_GPC['time2work'],
				'tea_subject' 	=> $_GPC['tea_subject'],
				'zhicheng' 		=> $_GPC['zhicheng'],
				'zc_pstime' 	=> $_GPC['zc_pstime'],
				'zc_prtime' 	=> $_GPC['zc_prtime'],
				'zjzhiwu' 		=> $_GPC['zjzhiwu'],
				'zjzw_pstime' 	=> $_GPC['zjzw_pstime'],
				'zjzw_prtime' 	=> $_GPC['zjzw_prtime'],
				'main_work_jl' 	=> $_GPC['main_work_jl'],
				'jszg_type' 	=> $_GPC['jszg_type'],
				'jszgzs_num'	=> $_GPC['jszgzs_num'],
				'pth_level' 	=> $_GPC['pth_level'],
				'pthzs_num' 	=> $_GPC['pthzs_num'],
				'yzk1_level' 	=> $_GPC['yzk1_level'],
				'yzk1_rank' 	=> $_GPC['yzk1_rank'],
				'yzk1_org' 		=> $_GPC['yzk1_org'],
				'yzk2_level' 	=> $_GPC['yzk2_level'],
				'yzk2_rank' 	=> $_GPC['yzk2_rank'],
				'yzk2_org' 		=> $_GPC['yzk2_org'],
				'zhbz1_level' 	=> $_GPC['zhbz1_level'],
				'zhbz1_rank' 	=> $_GPC['zhbz1_rank'],
				'zhbz1_org' 	=> $_GPC['zhbz1_org'],
				'zhbz2_level' 	=> $_GPC['zhbz2_level'],
				'zhbz2_rank' 	=> $_GPC['zhbz2_rank'],
				'zhbz2_org' 	=> $_GPC['zhbz2_org'],
				'jky1_level' 	=> $_GPC['jky1_level'],
				'jky1_rank' 	=> $_GPC['jky1_rank'],
				'jky1_org' 		=> $_GPC['jky1_org'],
				'jky2_level' 	=> $_GPC['jky2_level'],
				'jky2_rank' 	=> $_GPC['jky2_rank'],
				'jky2_org' 		=> $_GPC['jky2_org'],
				'qtzs1_level' 	=> $_GPC['qtzs1_level'],
				'qtzs1_rank' 	=> $_GPC['qtzs1_rank'],
				'qtzs1_org' 	=> $_GPC['qtzs1_org'],
				'qtzs2_level' 	=> $_GPC['qtzs2_level'],
				'qtzs2_rank' 	=> $_GPC['qtzs2_rank'],
				'qtzs2_org' 	=> $_GPC['qtzs2_org'],
				'qtzs3_level' 	=> $_GPC['qtzs3_level'],
				'qtzs3_rank' 	=> $_GPC['qtzs3_rank'],
				'qtzs3_org' 	=> $_GPC['qtzs3_org'],
			);
			$otherinfo_temp = serialize($otherinfo);
			$data['otherinfo'] = $otherinfo_temp;
			pdo_update($this->table_teachers, $data, array('id' => $_GPC['tid']));
			$result['msg'] = "修改成功！！";
			$result['result'] = $otherinfo;
			die ( json_encode ( $result ) );
			
			
		}
		
	}

if ($operation == 'get_plate_num')  {
    if (! $_GPC['schoolid']) {
        die ( json_encode ( array (
            'result' => false,
            'msg' => '非法请求！'
        ) ) );
    }else{
        $schoolid = $_GPC['schoolid'];
        $tid = $_GPC['tid'];
        $teacher = pdo_fetch("select plate_num from " . tablename($this->table_teachers) . " where id = '{$tid}' ");
        if(!empty($teacher['plate_num'])){

            $datass ['result'] = true;
            $datass ['msg'] = '获取成功';
            $datass ['data'] = $teacher['plate_num'];
        }else{
            $datass ['result'] = false;
            $datass ['data'] = '未设置车牌';
        }
    }
    die ( json_encode ( $datass ) );
}


if ($operation == 'set_plate_num')  {
    if (! $_GPC['schoolid']) {
        die ( json_encode ( array (
            'result' => false,
            'msg' => '非法请求！'
        ) ) );
    }else{
        $schoolid = $_GPC['schoolid'];
        $tid = $_GPC['tid'];
        $plate_num = $_GPC['plate_num'];
        pdo_update($this->table_teachers,array('plate_num'=>$plate_num),array('id'=>$tid));


if(empty($plate_num)){
    $plate_num = '暂未设置车牌';
}
            $datass ['result'] = true;
            $datass ['msg'] = ' 修改成功';
            $datass ['data'] = $plate_num;

    }
    die ( json_encode ( $datass ) );
}
#确定预约访问
if ($operation == 'edit_visitors')  {
    if (! $_GPC['schoolid']) {
        die ( json_encode ( array (
            'result' => false,
            'msg' => '非法请求！'
        ) ) );
    }else{
        $schoolid = $_GPC['schoolid'];
        $id = $_GPC['id'];
		$data ['id'] = $id;
		$data ['schoolid'] = $schoolid;
		$lastedittime = time();
		#创建心跳任务
        if(is_showZB()) {
            CreateHBtodo_ZB($schoolid, $_W['uniacid'], $lastedittime, 17);
        }
		#生成二维码
		$qrcode = visitors_qrcode(json_encode($data));
        pdo_update($this->table_visitors, array('status' => 2, 'lastedittime' => $lastedittime, 'qrcode'=>"$qrcode"), array('id' => $id));
		$data ['id'] = $id;
		$data ['result'] = true;
		$data ['msg'] = ' 预约成功';
        $this->sendMobileStuVis($id, $_GPC['schoolid'], $_W ['weid']);
    }
    die ( json_encode ( $data ) );
}

#查询老师记录预约
if ($operation == 'checkVisData')  {
    if (! $_GPC['schoolid']) {
        die ( json_encode ( array (
            'result' => false,
            'msg' => '非法请求！'
        ) ) );
    }else{
        $id = $_GPC['id'];
		$teacher = pdo_fetch("SELECT t_id,starttime,endtime FROM " . tablename($this->table_visitors) . " WHERE :id = id ", array(':id' => $id));
		$check = pdo_fetch("SELECT * FROM " . tablename($this->table_visitors) . " WHERE :t_id = t_id AND :status = status AND ( (:starttime < starttime AND :endtime > endtime) OR ( :starttime > starttime AND :starttime < endtime) OR ( :endtime > starttime AND :endtime < endtime) ) ", array(':t_id' => $teacher['t_id'] , ':status' => '2' , ':starttime' => $teacher['starttime'] , ':endtime' => $teacher['endtime']));
		if($check){
			$data ['result'] = false;
			$data ['msg'] = '时间段有冲突，是否继续预约';
		}else{
			$data ['result'] = true;
			$data ['msg'] = '确定预约吗？';
		}
		
    }
    die ( json_encode ( $data ) );
}

#拒绝预约
if ($operation == 'refuse_visitors')  {
    if (! $_GPC['schoolid']) {
        die ( json_encode ( array (
            'result' => false,
            'msg' => '非法请求！'
        ) ) );
    }else{
        $id = $_GPC['id'];
        $refuseinfo = htmlspecialchars($_GPC['refuseinfo']);
		pdo_update($this->table_visitors, array('status' => 3, 'refuseinfo' => "{$refuseinfo}"), array('id' => $id));
		$data ['result'] = true;
		$data ['msg'] = ' 拒绝成功';
    }
    $data ['id'] = $id;
    $data ['schoolid'] =$_GPC['schoolid'];
    $data ['weid'] =  $_W ['weid'];
    $this->sendMobileStuVis($id, $_GPC['schoolid'], $_W ['weid']);
    die ( json_encode ( $data ) );
}

if ($operation == 'get_bj_send_set'){
    if (! $_GPC['schoolid']) {
        die ( json_encode ( array (
            'result' => false,
            'msg' => '非法请求！'
        ) ) );
    }else{
        $schoolid = $_GPC['schoolid'];
        $bjid = $_GPC['bjid'];
        $weid = $_GPC['weid'];
        $result = GetSendSet($schoolid,$weid,$bjid);


    }
    die ( json_encode ( $result ) );
}

if ($operation == 'set_bj_send_set'){
    if (! $_GPC['schoolid']) {
        die ( json_encode ( array (
            'result' => false,
            'msg' => '非法请求！'
        ) ) );
    }else{
        $schoolid = $_GPC['schoolid'];
        $bjid     = $_GPC['bjid'];
        $weid     = $_GPC['weid'];
        $is_stu   = $_GPC['is_stu'];
        $is_pare  = $_GPC['is_pare'];
        $is_ht    = $_GPC['is_ht'];
        $is_rt    = $_GPC['is_rt'];
        $input_data = array();
        if($is_stu){
            $input_data[]='students';
        }
        if($is_pare){
            $input_data[]='parents';
        }
        if($is_ht){
            $input_data[]='head_teacher';
        }
        if($is_rt){
            $input_data[]='rest_teacher';
        }
        $data = serialize($input_data);
        pdo_update($this->table_classify,array('checksendset'=> $data),array('sid'=>$bjid,'schoolid'=>$schoolid));
    }
    $result['msg'] = "修改成功";
    $result['data'] = $_GPC;
    die ( json_encode ( $result ) );
}

//校长给推广员分配
if ($operation == 'FpTgy')  {
	if(empty($_GPC ['schoolid'])) {
		$result ['result'] = false;
		$result ['msg'] = '非法请求';
	}else{
		$randStr = str_shuffle('123456789');
		$rand    = substr($randStr, 0, 6);
		if(!empty($_GPC['code'])){
			$rand = $_GPC['code'];
		}
		//获取潜在客户的信息 
		$qzkh = pdo_fetch("SELECT * FROM ".GetTableName('qzkh')." WHERE id = '{$_GPC['fpid']}' ");
		if(!empty($qzkh)){
			pdo_update(GetTableName('qzkh',false),array('status'=>2),array('id'=>$_GPC['fpid']));


			$data = array(
				'weid' 	 	=> intval($_GPC['weid']),
				'schoolid'  => intval($_GPC['schoolid']),
				's_name' => trim($qzkh['sname']),
				'birthdate' => $qzkh['birthday'], //
				// 'pard' => $qzkh['pard'], //
				'code' => $rand,
				'superior_tid' => intval($_GPC['tgyid']),
				'mobile' => $qzkh['mobile'],
				'sex' => $qzkh['sex'],
				'province' => '',
				'city' => '',
				'county' => '',
				'area_addr' => '',
				'createdate' => time(),
				'seffectivetime' => time(),
				// 'own' => $qzkh['openid'],
				'status' => 1
			);
			
			$data['from_kcid'] = $_GPC['kcid'];
			pdo_insert(GetTableName('students',false),$data);
			$sid = pdo_insertid();
			$kcinfo = pdo_fetch("SELECT cose,FirstNum,payweid FROM " . GetTableName('tcourse') . " where id = :id ", array(':id' => $_GPC['kcid']));
			$payweid = $kcinfo['payweid'] ? $kcinfo['payweid'] : intval($_GPC['weid']);
			$orderdata = array(
				'weid' => intval($_GPC['weid']),
				'schoolid' => intval($_GPC['schoolid']),
				'kcid' => intval($_GPC['kcid']),
				'superior_tid' => intval($_GPC['tgyid']),
				'userid' => 0,
				'sid' => $sid,
				'cose' => $kcinfo['cose'],
				'status' => 1,
				'type' => 1,
				'ksnum' => $kcinfo['FirstNum'],
				'payweid' => $payweid,
				'createtime' => time()
			);
			pdo_insert(GetTableName('order',false),$orderdata);
			$userinfo = array(
				'name' =>$qzkh['sname'],
				'mobile' =>$qzkh['mobile'],
			);
			//绑定
			$userdata = array(
				'sid' => $sid,
				'weid' => intval($_GPC['weid']),
				'schoolid' => intval($_GPC['schoolid']),
				'openid' => $qzkh['openid'],
				'userinfo' => serialize($userinfo),
				'mobile' => $qzkh['mobile'],
				'pard' => $qzkh['pard'],
				'status' => 1,
				'superior_tid' => intval($_GPC['tgyid']),
				'createtime' => time()
			);
			pdo_insert(GetTableName('user',false),$userdata);

			
			$userid_tostu = pdo_insertid();
			if($qzkh['pard'] == 2){
				$into_stu['mom'] = $qzkh['openid'];
				$into_stu['muserid'] = $userid_tostu;
			}
			if($qzkh['pard'] == 3){
				$into_stu['dad'] = $qzkh['openid'];
				$into_stu['duserid'] = $userid_tostu;
			}
			if($qzkh['pard'] == 4){
				$into_stu['own'] = $qzkh['openid'];
				$into_stu['ouserid'] = $userid_tostu;
			}
			if($qzkh['pard'] == 5){
				$into_stu['other'] = $qzkh['openid'];
				$into_stu['otheruserid'] = $userid_tostu;
			}
			pdo_update($this->table_students,$into_stu,array('id'=>$sid));
			$this->DoTag($_GPC['schoolid'],$userid_tostu); //打标签
			$result ['msg'] = '分配成功';
			$result ['result'] = true;
		}else{
			$result ['msg'] = '数据不存在，请重新操作';
			$result ['result'] = false;
		}
	}
	die ( json_encode ( $result ) );
}

if ($operation == 'AddPhotoType'){
    if (! $_GPC['schoolid']) {
        die ( json_encode ( array (
            'result' => false,
            'msg' => '非法请求！'
        ) ) );
    }else{
		$data = array(
			'weid' =>  $_GPC['weid'],
			'schoolid' => $_GPC['schoolid'],
			'sname' => trim($_GPC['name']),
			'is_upload' => $_GPC['is_upload'],
			'bjid' => $_GPC['bj_id'],
			'kcid' => $_GPC['kcid'],
			'phototype' => $_GPC['phototype'],
			'type'=>'phototype'
		);
		pdo_insert(GetTableName('classify',false), $data);
		$data['result'] = true;
		$data['msg'] = '操作成功！';
		die(json_encode($data));	
    }
    die ( json_encode ( $data ) );
}
// 新增单条晨检数据
if ($operation == 'AddMC'){
    if (! $_GPC['schoolid']) {
        die ( json_encode ( array (
            'result' => false,
            'msg' => '非法请求！'
        ) ) );
    }else{
		$data = array(
			'weid' 			=>	$_GPC['weid'],
			'schoolid' 		=>	$_GPC['schoolid'],
			'height' 		=> 	$_GPC['height'],
			'weight' 		=> 	$_GPC['weight'],
			'lefteye' 		=> 	$_GPC['lefteye'],
			'righteye' 		=> 	$_GPC['righteye'],
			'tiwen' 		=> 	$_GPC['tiwen'],
			'sid' 			=> 	$_GPC['sid'],
			'bj_id' 		=> 	$_GPC['bj_id'],
			'tid' 			=> 	$_GPC['tid'],
			'createtime' 	=> 	time(),
			'createdate' 	=> 	strtotime(date("Y-m-d")),
			'mouth' 		=> 	$_GPC['mouth'], //口腔
			'cough'			=>	$_GPC['cough'], //咳嗽
			'vomit'			=>  $_GPC['vomit'], //呕吐
			'trauma'		=>  $_GPC['trauma'], //外伤
			'diarrhea'		=>  $_GPC['diarrhea'], //腹泻
			'cold'			=>  $_GPC['cold'], //感冒
			'headache'		=>  $_GPC['headache'], //头痛
			'herpes'		=>  $_GPC['herpes'], //疱疹
			'handHerpes'	=>  $_GPC['handHerpes'], //手掌疱疹
			'nail'			=>  $_GPC['nail'], //指甲
			'is_mc'			=>  keep_MC() ? 1 : 0, //是否定制
		);
		pdo_insert(GetTableName('morningcheck',false), $data);
		$mcid = pdo_insertid();
		$school = pdo_fetch("SELECT spic FROM ".GetTableName('index')." WHERE id = '{$_GPC['schoolid']}' ");
		$student = pdo_fetch("SELECT s_name,icon,id FROM ".GetTableName('students')." WHERE id = '{$_GPC['sid']}' ");
		$student['icon'] = $student['icon'] ? tomedia($student['icon']) : tomedia($school['spic']);
		$this->sendMobileTwtz($student['id'],$_GPC['tiwen'],2,$_W['schooltype'],$mcid);
		$data['result'] = true;
		$data['msg'] = '操作成功！';
		$data['data'] = $student;
		die(json_encode($data));	
    }
}
// 修改单条晨检数据
if ($operation == 'EditMC'){
    if (! $_GPC['schoolid']) {
        die ( json_encode ( array (
            'result' => false,
            'msg' => '非法请求！'
        ) ) );
    }else{
		$data = array(
			'height' 		=> 	$_GPC['height'],
			'weight' 		=> 	$_GPC['weight'],
			'lefteye' 		=> 	$_GPC['lefteye'],
			'righteye' 		=> 	$_GPC['righteye'],
			'tiwen' 		=> 	$_GPC['tiwen'],
			'createtime' 	=> 	time(),
			'mouth' 		=> 	$_GPC['mouth'],
			'cough'			=>	$_GPC['cough'], //咳嗽
			'vomit'			=>  $_GPC['vomit'], //呕吐
			'trauma'		=>  $_GPC['trauma'], //外伤
			'diarrhea'		=>  $_GPC['diarrhea'], //腹泻
			'cold'			=>  $_GPC['cold'], //感冒
			'headache'		=>  $_GPC['headache'], //头痛
			'herpes'		=>  $_GPC['herpes'], //疱疹
			'handHerpes'	=>  $_GPC['handHerpes'], //手掌疱疹
			'nail'			=>  $_GPC['nail'], //指甲
		);
		pdo_update(GetTableName('morningcheck',false),$data,array('id'=>$_GPC['id']));
		$data['result'] = true;
		$data['msg'] = '操作成功！';
		die(json_encode($data));	
    }
}
//获取单条晨检数据
if ($operation == 'GetOneMC'){
    if (! $_GPC['schoolid']) {
        die ( json_encode ( array (
            'result' => false,
            'msg' => '非法请求！'
        ) ) );
    }else{
		if($_GPC['id']){
			$condition = " AND id = '{$_GPC['id']}' ";
		}
		if($_GPC['sid']){
			$condition = " AND sid = '{$_GPC['sid']}' ";
		}
		if(keep_MC()){
			$condition .= " AND is_mc = 1 ";
		}else{
			$condition .= " AND is_mc = 0 ";
		}
		$res = pdo_fetch("SELECT * FROM ".GetTableName('morningcheck')." WHERE schoolid = '{$_GPC['schoolid']}' $condition ORDER BY createtime DESC ");
		if(!$res){
			$data['result'] = false;
			$data['msg'] = '数据不存在';
		}else{
			$data['result'] = true;
			$data['data'] = $res;
		}
		die(json_encode($data));	
    }
}
if ($operation == 'search_stu_card_info')  {
	$bj_id = trim($_GPC['bj_id']);
	$search = trim($_GPC['search']);
	$schoolid = trim($_GPC['schoolid']);
	$condition = " AND s_name LIKE '%{$search}%' ";	
	if(empty($search)){
		$result['msg'] = "请填写需要搜索信息";
		$result['result'] = false;
		die ( json_encode ( $result ) );
	}
	$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where id=:id", array(':id' => $schoolid));
	$list = pdo_fetchall("SELECT s.s_name,s.id,s.icon,i.spic,i.pard,i.id as cid, i.idcard,s.sex,i.createtime,i.severend,s.mobile FROM " . GetTableName('students') . " as s LEFT JOIN (SELECT id,sid,pard,cardtype,spic,idcard,createtime,severend FROM ". GetTableName('idcard') . " WHERE pard = 1 AND cardtype = 1 AND schoolid = '{$schoolid}') as i on s.id = i.sid WHERE s.schoolid = '{$schoolid}' AND s.bj_id = '{$bj_id}' $condition");
	foreach ($list as $key => $value) {
		$list[$key]['icon'] = $value['icon'] ? tomedia($value['icon']) : tomedia($school['spic']);
		$list[$key]['spic'] = $value['spic'] ? tomedia($value['spic']) : MODULE_URL."public/mobile/img/insertImage.png";
		$list[$key]['idcard'] = $value['idcard'] ? '卡号：'.$value['idcard'] : "未绑定";
	}
	if(empty($list)){
		$result['msg'] = "未获取相关数据";
		$result['result'] = false;
	}else{
		$result['msg'] = "成功获取数据";
		$result['result'] = true;
		$result['data'] = $list;
	}
	die ( json_encode ( $result ) );
}
?>