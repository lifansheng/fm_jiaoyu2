<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */global $_W, $_GPC;
   $operation = in_array ( $_GPC ['op'], array ('default','getbjlist','bdxs','skcsign','tkcsign','xskcqdqr','xskcbq','qrjsqd','txsk','signup','newstu','get_kslist','get_all_kslist','delsign_one','getxgtemplte','sqingjia','kcpingjia','newkcpingjia','txkcpj','deletetempstu','get_ks_conent','get_stu_payinfo','get_mysherecard','add_ks_clik','set_rank_list','show_rank_list','make_zl_team','join_zl_team','get_onteam_info','get_onorder_info','again_team','get_kc_shareword','get_kc_tgword','team_info','xu_zd','GetTeamData','CheckKcMingE','check_online_room','check_team_isfull','getNowKs','stuqj_px','getWelfare') ) ? $_GPC ['op'] : 'default';
    if ($operation == 'default') {
       	die ( json_encode ( array (
	        'result' => false,
	        'msg' => '参数错误'
            ) ) );
  	}
    if ($operation == 'getxgtemplte') {
		if (empty($_GPC ['schoolid'])) {
               die ( json_encode ( array (
                    'result' => false,
                    'msg' => '非法请求！' 
		               ) ) );
	    }else{
		    $school=  pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where  id=:id", array(':id' => $_GPC['schoolid']));
			$student = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where   schoolid=:schoolid AND id=:id", array(':schoolid' => $_GPC['schoolid'], ':id' => $_GPC['sid']));
			$stup = $student['points']?$student['points']:0;			
			$item = pdo_fetch("SELECT * FROM " . tablename($this->table_tcourse) . " WHERE id = :id ", array(':id' => $_GPC['kcid']));
			if(sale()){
				mload()->model('pay');
				$redpacket = getMyAllWelfare($_GPC['openid'],$_GPC ['schoolid'],1);
				$coupon = getMyAllWelfare($_GPC['openid'],$_GPC ['schoolid'],2);
			}else{
				$redpacket = [];
				$coupon = [];
			}
			
			include $this->template('comtool/xgtemple');
		}
  	}
	if ($operation == 'get_kc_shareword')  {
		$kcid   = $_GPC['kcid'];
		$schoolid = $_GPC['schoolid'];
		if (empty($schoolid) || empty($kcid)) {
			$result ['result'] = false;
			$result ['msg'] = '非常参数！';
	    }else{
			mload()->model('kc');
			$kcinfo = pdo_fetch("SELECT sale_id FROM " . GetTableName('tcourse') . " WHERE id = :id ", array(':id' => $kcid));
			$saleset = pdo_fetch("SELECT share_word FROM " . GetTableName('kc_saleset') . " WHERE id = :id ", array(':id' => $kcinfo['sale_id']));
			if(!empty($saleset['share_word'])){
				$result ['shareword'] = $saleset['share_word'];
				$result ['msg'] = '获取成功';
				$result ['result'] = true;
			}else{
				$result ['msg'] = '获取失败,本课为设置分享文案哦';
				$result ['result'] = false;
			}
		}
		die ( json_encode ( $result ) );
	}
	if ($operation == 'get_kc_tgword')  {
		$kcid   = $_GPC['kcid'];
		$schoolid = $_GPC['schoolid'];
		if (empty($schoolid) || empty($kcid)) {
			$result ['result'] = false;
			$result ['msg'] = '非常参数！';
	    }else{
			$kcinfo = pdo_fetch("SELECT tg_id FROM " . GetTableName('tcourse') . " WHERE id = :id ", array(':id' => $kcid));
			$saleset = pdo_fetch("SELECT share_word FROM " . GetTableName('kc_promote') . " WHERE id = :id ", array(':id' => $kcinfo['tg_id']));
			if(!empty($saleset['share_word'])){
				$result ['shareword'] = $saleset['share_word'];
				$result ['msg'] = '获取成功';
				$result ['result'] = true;
			}else{
				$result ['msg'] = '获取失败,本课为设置分享文案哦';
				$result ['result'] = false;
			}
		}
		die ( json_encode ( $result ) );
	}
	if ($operation == 'get_mysherecard')  {
		$kcid   = $_GPC['kcid'];
		$tid   = $_GPC['tid'];
		$openid = $_GPC['openid'];	
		$teamid = $_GPC['teamid'];
		$userid = $_GPC['userid'];
		$type = $_GPC['refrash'];
		$schoolid = $_GPC['schoolid'];
		if (empty($schoolid) || empty($kcid)) {
			$result ['result'] = false;
			$result ['msg'] = '非常参数！';
	    }else{
			mload()->model('kc');
			$popurl = GetPopByKc($kcid,$openid,$teamid,$userid,$tid,$type);
			$result ['pic'] = urlencode(tomedia($popurl['popurl']));
			$result ['result'] = $popurl['result'];
			$result ['msg'] = $popurl['msg'];
		}
		 
		die ( json_encode ( $result ) );
	}
	if ($operation == 'get_kslist')  {
		if (! $_GPC ['schoolid']) {
               die ( json_encode ( array (
                    'result' => false,
                    'msg' => '非法请求！' 
		               ) ) );
	    }else{
			$starttime = mktime(0,0,0,date("m"),date("d"),date("Y"));
			$endtime = $starttime + 86399;
			$kecheng = pdo_fetch("SELECT OldOrNew FROM " . tablename($this->table_tcourse) . " where schoolid = '{$_GPC['schoolid']}' And id = '{$_GPC['kcid']}' ");
			if($kecheng['OldOrNew'] == 0){
				$condition = " AND date > '{$starttime}' AND date < '{$endtime}'";	
				$kslist = pdo_fetchall("SELECT id,sd_id FROM " . GetTableName('kcbiao') . " where schoolid = '{$_GPC['schoolid']}' And kcid = '{$_GPC['kcid']}'  $condition ORDER BY date DESC");
				foreach($kslist as $key => $row){
					$teacher = pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " where id = '{$row['qrtid']}' ");
					$sd = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where sid = '{$row['sd_id']}' ");
					$kslist[$key]['teacher'] = $teacher['tname'];
					$isqd = pdo_fetch("SELECT status,createtime FROM " . tablename($this->table_kcsign) . " where sid = '{$_GPC['sid']}' And kcid = '{$_GPC['kcid']}' AND ksid = '{$row['id']}' ");
					$kslist[$key]['isqd'] = false;
					$kslist[$key]['isqr'] = false;
					if($isqd['status'] == 2 || $isqd){
						$kslist[$key]['isqd'] = true;
						if($isqd['status'] == 2){
							$kslist[$key]['isqr'] = true;
						}
					}
					$kslist[$key]['qdtime'] = date('H:i',$isqd['createtime']);
					$kslist[$key]['sdname'] = $sd['sname'];
				}
			}
			if($kecheng['OldOrNew'] == 1){
				$condition = " AND createtime > '{$starttime}' AND createtime < '{$endtime}'";	
				$kslist = pdo_fetchall("SELECT id,createtime,qrtid,status FROM " . tablename($this->table_kcsign) . " where schoolid = '{$_GPC['schoolid']}' And kcid = '{$_GPC['kcid']}' AND sid = '{$_GPC['sid']}' $condition ORDER BY createtime DESC");
				foreach($kslist as $key => $row){
					$teacher = pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " where id = '{$row['qrtid']}' ");
					$kslist[$key]['isqr'] = false;
					$kslist[$key]['teacher'] = $teacher['tname'];
					if($row['status'] == 2){
						$kslist[$key]['isqr'] = true;
					}
					$kslist[$key]['sdname'] = "签到".date('H:i',$row['createtime']);
				}
			}
			$data ['OldOrNew'] = $kecheng['OldOrNew'];
   			$data ['kslist'] = $kslist;
			$data ['result'] = true;
			$data ['msg'] = '成功获取！';
          	die ( json_encode ( $data ) );
		}
	}
	if ($operation == 'get_all_kslist')  {
		if (! $_GPC ['schoolid']) {
               die ( json_encode ( array (
                    'result' => false,
                    'msg' => '非法请求！' 
		               ) ) );
	    }else{
			$starttime = mktime(0,0,0,date("m"),date($_GPC['day']),date("Y"));
			$endtime = $starttime + 86399;
			$coursebuy = pdo_fetchAll("SELECT kcid FROM " . tablename($this->table_coursebuy) . " where schoolid = '{$_GPC['schoolid']}' AND sid = '{$_GPC['sid']}' AND kcid != 0 ");
			foreach ($coursebuy as $key => $value) {
				$kecheng = pdo_fetch("SELECT OldOrNew,name FROM " . tablename($this->table_tcourse) . " where schoolid = '{$_GPC['schoolid']}' AND id = '{$value['kcid']}' ");
				if($kecheng['OldOrNew'] == 0){
					$condition = " AND date > '{$starttime}' AND date < '{$endtime}'";	
					$kslist = pdo_fetchall("SELECT id,sd_id FROM " . GetTableName('kcbiao') . " where schoolid = '{$_GPC['schoolid']}' And kcid = '{$value['kcid']}'  $condition ORDER BY date DESC");
					foreach($kslist as $key1 => $row){
						$teacher = pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " where id = '{$row['qrtid']}' ");
						$sd = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where sid = '{$row['sd_id']}' ");
						$kslist[$key1]['teacher'] = $teacher['tname'];
						$isqd = pdo_fetch("SELECT status,createtime FROM " . tablename($this->table_kcsign) . " where sid = '{$_GPC['sid']}' And kcid = '{$value['kcid']}' AND ksid = '{$row['id']}' ");
						$kslist[$key1]['isqd'] = false;
						$kslist[$key1]['isqr'] = false;
						if($isqd['status'] == 2 || $isqd){
							$kslist[$key1]['isqd'] = true;
							if($isqd['status'] == 2){
								$kslist[$key1]['isqr'] = true;
							}
						}
						$kslist[$key1]['qdtime'] = date('H:i',$isqd['createtime']);
						$kslist[$key1]['sdname'] = $sd['sname'];
					}
				}

				if($kecheng['OldOrNew'] == 1){
					$condition = " AND createtime > '{$starttime}' AND createtime < '{$endtime}'";	
					$kslist = pdo_fetchall("SELECT id,createtime,qrtid,status FROM " . tablename($this->table_kcsign) . " where schoolid = '{$_GPC['schoolid']}' And kcid = '{$value['kcid']}' AND sid = '{$_GPC['sid']}' $condition ORDER BY createtime DESC");
					foreach($kslist as $key2 => $row2){
						$teacher = pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " where id = '{$row2['qrtid']}' ");
						$kslist[$key2]['isqr'] = false;
						$kslist[$key2]['teacher'] = $teacher['tname'];
						if($row2['status'] == 2){
							$kslist[$key2]['isqr'] = true;
						}
						$kslist[$key2]['sdname'] = "签到".date('H:i',$row2['createtime']);
					}
				}

				$coursebuy[$key]['kslist'] = $kslist;
				$coursebuy[$key]['kcname'] = $kecheng['name'];
				$coursebuy[$key]['OldOrNew'] = $kecheng['OldOrNew'];
			}
   			$data ['kslist'] = $coursebuy;
			$data ['result'] = true;
			$data ['msg'] = '成功获取！';
          	die ( json_encode ( $data ) );
		}
    }
	if ($operation == 'getbjlist')  {
		if (! $_GPC ['schoolid']) {
               die ( json_encode ( array (
                    'result' => false,
                    'msg' => '非法请求！' 
		               ) ) );
	    }else{
			$data = array();
			$bjlist = pdo_fetchall("SELECT * FROM " . tablename($this->table_classify) . " where schoolid = '{$_GPC['schoolid']}' And parentid = '{$_GPC['gradeId']}' And type = 'theclass' ORDER BY ssort DESC");
   			$data ['bjlist'] = $bjlist;
			$data ['result'] = true;
			$data ['msg'] = '成功获取！';
          	die ( json_encode ( $data ) );
		}
    }

    if ($operation == 'bdxs') {
		$data = explode ( '|', $_GPC ['json'] );
		if (! $_GPC ['schoolid'] || ! $_W ['openid']) {
               die ( json_encode ( array (
                    'result' => false,
                    'msg' => '非法请求！' 
		               ) ) );
	    }
		$subjectId = trim($_GPC['subjectId']);
		$school = pdo_fetch("SELECT bd_type FROM " . tablename($this->table_index) . " WHERE id = {$_GPC['schoolid']} ");
		if ($school['bd_type'] ==1 || $school['bd_type'] ==4 || $school['bd_type'] ==5 || $school['bd_type'] ==7){
			$bdset = get_weidset($_GPC['weid'],'bd_set');
			$sms_set = get_school_sms_set($_GPC ['schoolid']);
			if($sms_set['code'] ==1 && $bdset['sms_SignName'] && $bdset['sms_Code']){
				$mobile = !empty($_GPC['mymobile']) ? $_GPC['mymobile'] : $_GPC['mobile'];
				$status = check_verifycode($mobile, $_GPC['mobilecode'], $_GPC['weid']);
				if(!$status) {
					 die ( json_encode ( array (
					 'result' => false,
					 'msg' => '短信验证码错误或已过期！' 
					  ) ) );
				}				
			}else{
				if(empty($_GPC['mymobile'])){
					$condition .= " AND mobile = '{$_GPC['mobile']}'";
				}
			}
		}
		if ($school['bd_type'] ==2 || $school['bd_type'] ==4 || $school['bd_type'] ==6 || $school['bd_type'] ==7){
			$condition .= " AND code = '{$_GPC['code']}'";
		}
		if ($school['bd_type'] ==3 || $school['bd_type'] ==5 || $school['bd_type'] ==6 || $school['bd_type'] ==7){
			$condition .= " AND numberid = '{$_GPC['xuehao']}'";
		}
		if(empty($_GPC['sid'])){
			$sid = pdo_fetch("SELECT id FROM " . tablename($this->table_students) . " where :schoolid = schoolid And :weid = weid And :s_name = s_name $condition", array(
					 ':weid' => $_GPC ['weid'],
					 ':schoolid' => $_GPC ['schoolid'],
					 ':s_name'=>$_GPC ['s_name']
					));
			$stuid = $sid['id'];
		}else{
			$stuid = $_GPC['sid'];
		}		  
		$user = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And weid = :weid AND sid=:sid And uid =:uid ", array(
		         ':weid' => $_GPC ['weid'],
                 ':schoolid' => $_GPC ['schoolid'],				 
		         ':sid' => $stuid,
				 ':uid' => $_GPC['uid'],
	           	  ));				  
        $item = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where :schoolid = schoolid And weid = :weid AND id=:id ", array(
		         ':weid' => $_GPC ['weid'],
                 ':schoolid' => $_GPC ['schoolid'],				 
		         ':id' => $stuid
	           	  ));
		if(!empty($user)){
		    die ( json_encode ( array (
	            'result' => false,
	            'msg' => '您已绑定本学生,不可重复绑定！' 
	        ) ) );
		}				  
		if(empty($stuid)){
		    die ( json_encode ( array (
                'result' => false,
                'msg' => '没有找到该生信息,或信息输入有误！' 
	         ) ) );
		}
		if($subjectId == 2){	
			if (!empty($item['mom'])){
				die ( json_encode ( array (
	                'result' => false,
	                'msg' => '绑定失败，此学生母亲已经绑定了其他微信号！' 
		        ) ) );
			}	  
        }
		if($subjectId == 3){
			if (!empty($item['dad'])){
			  	die ( json_encode ( array (
	                'result' => false,
	                'msg' => '绑定失败，此学生父亲已经绑定了其他微信号！' 
	          	) ) );
			}
        }
		if($subjectId == 4){
			if (!empty($item['own'])){
				die ( json_encode ( array (
	                'result' => false,
	                'msg' => '绑定失败，此学生本人已经绑定了其他微信号！' 
		        ) ) );
			}
        }
		if($subjectId == 5){
			if (!empty($item['other'])){
				die ( json_encode ( array (
	                'result' => false,
	                'msg' => '绑定失败，此学生家长已经绑定了其他微信号！' 
		        ) ) );
			}
        }		
		if (empty($_GPC['openid'])) {
                die ( json_encode ( array (
                    'result' => false,
                    'msg' => '非法请求！' 
		        ) ) );
		}else{
			if($item['keyid'] != 0 ){
				$allstu = pdo_fetchall("SELECT * FROM " . tablename($this->table_students) . " where :schoolid = schoolid And weid = :weid AND keyid=:keyid ", array(
				':weid' => $_GPC ['weid'],
				':schoolid' => $_GPC ['schoolid'],				 
				':keyid' => $item['keyid']
				));

				foreach( $allstu as $key => $value )
				{
					$userdata = array(
						'sid' => $value['id'],
						'weid' =>  $_GPC ['weid'],
						'schoolid' => $_GPC ['schoolid'],
						'openid' => $_W ['openid'],
						'pard' => $subjectId,
						'uid' => $_GPC['uid'],
						'createtime' => time()
					);
					
					if(!empty($_GPC['mobile']) || !empty($_GPC['mymobile'])){
						if(!$_GPC['mymobile']){
							$userinfo = array(
								'name' => $_GPC['s_name'].get_guanxi($subjectId),
								'mobile' => trim($_GPC['mobile'])
							);								
						}else{
							$userinfo = array(
								'name' => $_GPC['realname'],
								'mobile' => trim($_GPC['mymobile'])
							);								
						}
						$userdata['realname'] = $userinfo['name'];
						$userdata['mobile'] = $userinfo['mobile'];
					}	
					pdo_insert($this->table_user, $userdata);
					$userid = pdo_insertid();
					if($subjectId == 2){
						$temp = array( 
							'mom' => $_GPC['openid'],
							'muserid' => $userid,
							'muid'=> $_GPC['uid']
						);
					}
					if($subjectId == 3){
						$temp = array(
							'dad' => $_GPC['openid'],
							'duserid' => $userid,
							'duid'=> $_GPC['uid']
						);
					}
					if($subjectId == 4){
						$temp = array(
							'own' => $_GPC['openid'],
							'ouserid' => $userid,
							'ouid'=> $_GPC['uid']
						);
					}
					if($subjectId == 5){
						$temp = array(
							'other' => $_GPC['openid'],
							'otheruserid' => $userid,
							'otheruid'=> $_GPC['uid']
						);
					}			
					pdo_update($this->table_students, $temp, array('id' => $value['id']));  
					$this->DoTag($_GPC['schoolid'],$userid); //打标签
				}
			}else{
				$userdata = array(
					'sid' => trim($stuid),
					'weid' =>  $_GPC ['weid'],
					'schoolid' => $_GPC ['schoolid'],
					'openid' => $_W ['openid'],
					'pard' => $subjectId,
					'uid' => $_GPC['uid'],
					'createtime' => time()
				);
				if(!empty($_GPC['mobile']) || !empty($_GPC['mymobile'])){
					if(!$_GPC['mymobile']){
						$userinfo = array(
							'name' => $_GPC['s_name'].get_guanxi($subjectId),
							'mobile' => trim($_GPC['mobile'])
						);								
					}else{
						$userinfo = array(
							'name' => $_GPC['realname'],
							'mobile' => trim($_GPC['mymobile'])
						);								
					}
					$userdata['realname'] = $userinfo['name'];
					$userdata['mobile'] = $userinfo['mobile'];
				}					
				pdo_insert($this->table_user, $userdata);			
				$userid = pdo_insertid();
				if($subjectId == 2){
					$temp = array( 
						'mom' => $_GPC['openid'],
						'muserid' => $userid,
						'muid'=> $_GPC['uid']
						);
				}
				if($subjectId == 3){
					$temp = array(
						'dad' => $_GPC['openid'],
						'duserid' => $userid,
						'duid'=> $_GPC['uid']
						);
				}
				if($subjectId == 4){
					$temp = array(
						'own' => $_GPC['openid'],
						'ouserid' => $userid,
						'ouid'=> $_GPC['uid']
						);
				}
				if($subjectId == 5){
					$temp = array(
						'other' => $_GPC['openid'],
						'otheruserid' => $userid,
						'otheruid'=> $_GPC['uid']
						);
				}			
				pdo_update($this->table_students, $temp, array('id' => $stuid));  
				$this->DoTag($_GPC['schoolid'],$userid); //打标签 
			}
			$data ['result'] = true;			
			$data ['msg'] = '绑定成功,即将跳转...';
		 die ( json_encode ( $data ) );
		}
    }

	if ($operation == 'skcsign') {
		
		if (! $_GPC ['schoolid'] || ! $_GPC ['weid']) {
               die ( json_encode ( array (
                    'result' => false,
                    'msg' => '非法请求！' 
		               ) ) );
		}else{
			$checkkc = pdo_fetch("select id,tid,maintid,OldOrNew,FirstNum,ReNum FROM ".tablename($this->table_tcourse)." WHERE weid='{$_GPC['weid']}' And schoolid='{$_GPC['schoolid']}' And  id = '{$_GPC['kcid']}'");
			if(empty($checkkc)){
				die ( json_encode ( array (
                    'result' => false,
                    'msg' => '该课程不存在！' 
		               ) ) );
			}
		    $data = array(
				'kcid' => $_GPC['kcid'],
				'schoolid' => $_GPC['schoolid'],
				'weid' => $_GPC['weid'],
				'sid'  => $_GPC['sid'],
				'createtime' => time(),
				'status' => 1,
				'type' => 1 ,
				'signtype' => 1 ,
		    );
		    if($checkkc['OldOrNew'] == 1){
			    $checkAll = pdo_fetchcolumn("select count(*) FROM ".tablename($this->table_kcsign)." WHERE weid='{$_GPC['weid']}' And schoolid='{$_GPC['schoolid']}' And  kcid = '{$_GPC['kcid']}' And sid='{$_GPC['sid']}' AND status=2 ");
			    $buy = pdo_fetch("select ksnum FROM ".tablename($this->table_coursebuy)." WHERE weid='{$_GPC['weid']}' And schoolid='{$_GPC['schoolid']}' And  kcid = '{$_GPC['kcid']}' And sid='{$_GPC['sid']}'");
			    $timeUp = strtotime(date("Ymd",time()));
			    $timeDown = $timeUp + 86399;
			    $checkteacher = pdo_fetch("select id FROM ".tablename($this->table_kcsign)." WHERE weid='{$_GPC['weid']}' And schoolid='{$_GPC['schoolid']}' And  kcid = '{$_GPC['kcid']}' And createtime>{$timeUp} And createtime<{$timeDown} ");
			    if($checkAll >=$buy['ksnum']){
				    die ( json_encode ( array (
                    'result' => false,
                    'msg' => '您的购买课时已用完，请续费后重新签到！' 
		               ) ) );
			    }
		  		pdo_insert($this->table_kcsign, $data);
		    }elseif($checkkc['OldOrNew'] ==0){
				$checkAll = pdo_fetchcolumn("select sum(costnum) FROM ".tablename($this->table_kcsign)." WHERE weid='{$_GPC['weid']}' And schoolid='{$_GPC['schoolid']}' And  kcid = '{$_GPC['kcid']}' And sid='{$_GPC['sid']}' AND status = 2 ");
				$buy = pdo_fetch("select ksnum FROM ".tablename($this->table_coursebuy)." WHERE weid='{$_GPC['weid']}' And schoolid='{$_GPC['schoolid']}' And  kcid = '{$_GPC['kcid']}' And sid='{$_GPC['sid']}'");
				$checkks = pdo_fetch("select id,costnum FROM ".GetTableName('kcbiao')." WHERE weid='{$_GPC['weid']}' And schoolid='{$_GPC['schoolid']}' And  id = '{$_GPC['ksid']}'");
			    if(empty($checkks)){
					die ( json_encode ( array (
	                    'result' => false,
	                    'msg' => '该课时不存在！' 
			               ) ) );
				}
				if(!empty($checkkc['ReNum'])){
				  if($checkAll >=$buy['ksnum']){
				    die ( json_encode ( array (
                    'result' => false,
                    'msg' => '您的购买课时已用完，请续费后重新签到！' 
		               ) ) );
			    	}
		    	}
				$checkteacher = pdo_fetch("select id FROM ".tablename($this->table_kcsign)." WHERE weid='{$_GPC['weid']}' And schoolid='{$_GPC['schoolid']}' And  kcid = '{$_GPC['kcid']}' And  ksid = '{$_GPC['ksid']}' ");
			   	$data['ksid'] = $_GPC['ksid'];
			   	$data['type'] = 0 ;
                $data['costnum'] = $checkks['costnum'] ;
				pdo_insert($this->table_kcsign, $data);
		    }
		    $insertid = pdo_insertid();
		    if(!empty($insertid)){
			    if(empty($checkteacher)){
			      	$this->sendMobileTxjsqd($_GPC['kcid'],$_GPC['schoolid'],$_GPC['weid']);
			    }
			    $data_r ['result'] = true;
				$data_r ['msg'] = '签到成功，请勿重复签到！';	
		      	die ( json_encode ( $data_r ) );
		    }else{
			    $data_r ['result'] = false;
				$data_r ['msg'] = '签到失败，数据无法写入！';	
		      	die ( json_encode ( $data_r ) );
		    }
	    }
	}

	if ($operation == 'tkcsign') {
		if (! $_GPC ['schoolid'] || ! $_GPC ['weid']) {
               die ( json_encode ( array (
                    'result' => false,
                    'msg' => '非法请求！' 
		               ) ) );
		}else{
			$checkkc = pdo_fetch("select * FROM ".tablename($this->table_tcourse)." WHERE weid='{$_GPC['weid']}' And schoolid='{$_GPC['schoolid']}' And  id = '{$_GPC['kcid']}'");
			if(empty($checkkc)){
				die ( json_encode ( array (
                    'result' => false,
                    'msg' => '该课程不存在！' 
		               ) ) );
			}
		    $data = array(
				'kcid' => $_GPC['kcid'],
				'schoolid' => $_GPC['schoolid'],
				'weid' => $_GPC['weid'],
				'tid'  => $_GPC['tid'],
				'createtime' => time(),
				'status' => 1,
				'type' => 1 
		    );
		   
		    if($_GPC['OldOrNew'] == 1){
		    	$time = strtotime(date('Ymd'));
				$time1 =$time + 86399;
				$checksignNotMy = pdo_fetch("select id FROM ".tablename($this->table_kcsign)." WHERE createtime>='{$time}' AND createtime<'{$time1}' And kcid = '{$_GPC['kcid']}' AND tid!='{$_GPC['tid']}' AND sid=0 and status=2 ");
			$checksignMy = pdo_fetch("select id,status FROM ".tablename($this->table_kcsign)." WHERE createtime>='{$time}' AND createtime<'{$time1}' And kcid = '{$_GPC['kcid']}' AND tid='{$_GPC['tid']}' ");
				if(!empty($checksignMy)){
					if($checksignMy['status'] ==1){
						die ( json_encode ( array (
		                    'result' => false,
		                    'msg' => '签到失败！您已经签到，请等待确认' 
		               ) ) );
					}elseif($checksignMy['status'] ==2){
						die ( json_encode ( array (
		                    'result' => false,
		                    'msg' => '签到失败！您已经签到并被确认' 
		               ) ) );
					}
				}
				if(!empty($checksignNotMy)){
					die ( json_encode ( array (
                    'result' => false,
                    'msg' => '签到失败！该课程今日已有其他老师签到' 
		               ) ) );
				}
			 	if(($_GPC['is_dq'] == 'njzrdq') || $checkkc['tea_sign_confirm'] != 1){
			    	$data['status'] = 2;
		    	};
				pdo_insert($this->table_kcsign, $data);
				$insertid = pdo_insertid();
		    }elseif($_GPC['OldOrNew'] ==0){
			    $checkks = pdo_fetch("select id,costnum FROM ".GetTableName('kcbiao')." WHERE weid='{$_GPC['weid']}' And schoolid='{$_GPC['schoolid']}' And  id = '{$_GPC['ksid']}'");
			    if(empty($checkks)){
				die ( json_encode ( array (
                    'result' => false,
                    'msg' => '该课时不存在！' 
		               ) ) );
				}
				$checksignMy = pdo_fetch("select id,status FROM ".tablename($this->table_kcsign)." WHERE weid='{$_GPC['weid']}' And schoolid='{$_GPC['schoolid']}' And ksid = '{$_GPC['ksid']}' and  tid = '{$_GPC['tid']}'");
				$checksignNotMy = pdo_fetch("select id FROM ".tablename($this->table_kcsign)." WHERE weid='{$_GPC['weid']}' And schoolid='{$_GPC['schoolid']}' And ksid = '{$_GPC['ksid']}' And status=2 And tid != '{$_GPC['tid']}'");
				if(!empty($checksignMy)){
					if($checksignMy['status'] ==1){
						die ( json_encode ( array (
		                    'result' => false,
		                    'msg' => '签到失败！您已经签到，请等待确认' 
		               ) ) );
					}elseif($checksignMy['status'] ==2){
						die ( json_encode ( array (
		                    'result' => false,
		                    'msg' => '签到失败！您已经签到并被确认' 
		               ) ) );
					}
				}
				if(!empty($checksignNotMy)){
					die ( json_encode ( array (
                    'result' => false,
                    'msg' => '签到失败！该课时已有其他老师签到' 
		               ) ) );
				}
			   	$data['ksid'] = $_GPC['ksid'];
			   	$data['type'] = 0 ;
			 	if(($_GPC['is_dq'] == 'njzrdq') || $checkkc['tea_sign_confirm'] != 1){
			    	$data['status'] = 2;
		    	};
                $data['costnum'] = $checkks['costnum'];
				pdo_insert($this->table_kcsign, $data);
				$insertid = pdo_insertid();
		    }
		    
		    if(!empty($insertid)){
			    if($data['status'] == 1){
				   $this->sendMobileJsqrqdtz($insertid, $_GPC ['schoolid'], $_W['uniacid']);
		    	};
			    $data_r ['result'] = true;
				$data_r ['msg'] = '签到成功，请勿重复签到！';	
		      	die ( json_encode ( $data_r ) );
		    }else{
			    $data_r ['result'] = false;
				$data_r ['msg'] = '签到失败，数据无法写入！';	
		      	die ( json_encode ( $data_r ) );
		    }
	    }
	}

	if ($operation == 'xskcqdqr') {
		if (! $_GPC ['schoolid'] || ! $_GPC ['weid']) {
               die ( json_encode ( array (
                    'result' => false,
                    'msg' => '非法请求！' 
		               ) ) );
		}else{
			$signids = explode ( ',', $_GPC ['logids'] );
			if($signids){
				foreach($signids as $row){
					if($row > 0 ){
						pdo_update($this->table_kcsign, array('status' => 2,'qrtid'=>$_GPC['qrtid']), array('id' => $row));
						$this->sendMobileXsqrqdtz($row, $_GPC ['schoolid'], $_W['uniacid']);
					}
				}
				die ( json_encode ( array (
                    'result' => true,
                    'msg' => '签到确认成功！' 
		               ) ) );
			}else{
				die ( json_encode ( array (
                    'result' => false,
                    'msg' => '您没有选择学生！' 
		               ) ) );
			}
	    }
	}

	if ($operation == 'xskcbq') {
		if (! $_GPC ['schoolid'] || ! $_GPC ['weid']) {
               die ( json_encode ( array (
                    'result' => false,
                    'msg' => '非法请求！' 
		               ) ) );
		}else{
			$StuUid = explode ( ',', $_GPC ['StuUid'] );
			$not = '';
			$do = 0;
			$back_stu_arr = array();
			if($StuUid){
				foreach($StuUid as $row){
					if($row > 0 ){
                        $student =  pdo_fetch("select s_name FROM ".tablename($this->table_students)." WHERE weid='{$_GPC['weid']}' And schoolid='{$_GPC['schoolid']}' And id='{$row}'");
						$hasbuynum = pdo_fetchcolumn("select ksnum FROM ".tablename($this->table_coursebuy)." WHERE weid='{$_GPC['weid']}' And schoolid='{$_GPC['schoolid']}' And  kcid = '{$_GPC['kcid']}' AND sid = '{$row}'");
				  		$checkAll = pdo_fetchcolumn("select count(*) FROM ".tablename($this->table_kcsign)." WHERE weid='{$_GPC['weid']}' And schoolid='{$_GPC['schoolid']}' And  kcid = '{$_GPC['kcid']}' And sid='{$row}'");
						$data = array(
							'kcid' => $_GPC['kcid'],
							'schoolid' => $_GPC['schoolid'],
							'weid' => $_GPC['weid'],
							'sid'  => $row,
							'createtime' => $_GPC['time'],
							'status' => 2,
							'type' => 1,
							'qrtid'=>$_GPC['qrtid'] 
					    );
					    if($checkAll >= $hasbuynum){

						    $not .= $student['s_name'].'/';
					    }elseif($checkAll < $hasbuynum){
					       	if($_GPC['OldOrNew'] == 1){
			  					pdo_insert($this->table_kcsign, $data);

			  					$insertid = pdo_insertid();
                                $back_stu_arr[] = array(
                                    'sname' => $student['s_name'],
                                    'id' => $row,
                                    'time' =>date("H:i",time())
                                );
						    }elseif($_GPC['OldOrNew'] ==0){
							    $checkks = pdo_fetch("select id,costnum FROM ".GetTableName('kcbiao')." WHERE weid='{$_GPC['weid']}' And schoolid='{$_GPC['schoolid']}' And  id = '{$_GPC['ksid']}'");
							    if(empty($checkks)){
								die ( json_encode (array(
				                    'result' => false,
				                    'msg' => '该课时不存在！' 
						               ) ) );
								}
							   	$data['ksid'] = $_GPC['ksid'];
							   	$data['type'] = 0 ;
						   		$data['createtime'] = time() ;
                                $data['costnum'] = $checkks['costnum'];
							   	pdo_insert($this->table_kcsign, $data);
							   	$insertid = pdo_insertid();
                                $back_stu_arr[] = array(
                                    'sname' => $student['s_name'],
                                    'id' => $row,
                                    'time' =>date("H:i",time())
                                );
						    }
							$this->sendMobileXsqrqdtz($insertid, $_GPC ['schoolid'], $_W['uniacid']);
							$do++;
						}
					}
				}
				$backstr = $do."名学生操作成功！";
				if($not !=''){
					$backstr.="\n下列学生课时已用完，签到失败：\n".$not;	
				}
				
				die ( json_encode ( array (
                    'result' => true,
                    'msg' =>$backstr,
                    'back_data' => $back_stu_arr
		               ) ) );
			}else{
				die ( json_encode ( array (
                    'result' => false,
                    'msg' => '您没有选择学生！' 
		               ) ) );
			}
	    }
	}

    if ($operation == 'txsk') {
		if (! $_GPC ['schoolid'] || ! $_GPC ['weid']) {
               die ( json_encode ( array (
                    'result' => false,
                    'msg' => '非法请求！' 
		               ) ) );
		}else{
			$id = intval($_GPC['id']);
		    $schoolid = intval($_GPC['schoolid']);
		    $weid = intval($_GPC['weid']);
		    if(empty($id)){
			     die ( json_encode ( array (
                    'result' => false,
                    'msg' => '抱歉，本条信息不存在在或是已经被删除！！' 
		            ) ) );
		    }
		    $this->sendMobileJssktx($id,$schoolid,$weid);
			pdo_update($this->table_kcbiao,array('is_remind'=>1),array('id'=>$id));
			die ( json_encode ( array (
                'result' => true,
                'msg' => '提醒教师成功！' 
	        ) ) );
		}
	}

	if ($operation == 'signup'){
		$data = explode ( '|', $_GPC ['json'] );
		if (! $_GPC ['schoolid'] || ! $_GPC ['weid']) {
               die ( json_encode ( array (
                    'result' => false,
                    'msg' => '非法请求！' 
		               ) ) );
	         }
			 
			 
		$shareuserid = $_GPC['shareuserid'];
	
        $setting = pdo_fetch("SELECT * FROM " . tablename($this->table_set) . " WHERE :weid = weid", array(':weid' => $_GPC['weid']));
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " WHERE :id = id", array(':id' => $_GPC['schoolid']));
		$user = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " WHERE :weid = weid And :schoolid = schoolid And :id = id", array(':weid' => $_GPC['weid'], ':schoolid' => $_GPC['schoolid'], ':id' => $_GPC['user']));
		$student = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " WHERE :weid = weid And :schoolid = schoolid And :id = id", array(':weid' => $_GPC['weid'], ':schoolid' => $_GPC['schoolid'], ':id' => $_GPC['sid']));
		$cose = pdo_fetch("SELECT * FROM " . tablename($this->table_tcourse) . " WHERE :id = id", array(':id' => $_GPC['kcid'])); 
		if($_GPC['is_point'] != 0){
			$point_dy = intval($_GPC['point']);
			$dyl = intval($cose['Point2Cost']);
			$dyfy =sprintf("%.2f",  $point_dy / $dyl);;
			$final_cose = $cose['cose'] - $dyfy;
		}else{
			$final_cose =$cose['cose'];
		}
		
		if ($final_cose <= 0) {
            die ( json_encode ( array (
                    'result' => false,
                    'msg' => '抱歉，抵用价格必须小于课程价格'
		               ) ) );			
		}
		$issale = pdo_fetch("SELECT * FROM " . tablename($this->table_order) . " WHERE :weid = weid And :schoolid = schoolid And :kcid = kcid And :sid = sid", array(':weid' => $_GPC['weid'], ':schoolid' => $_GPC['schoolid'], ':kcid' => $_GPC['kcid'], ':sid' => $_GPC['sid'])); 
		$yb = pdo_fetchcolumn("select count(*) FROM ".tablename('wx_school_order')." WHERE kcid = '".$cose['id']."' And (status = 2 or type = 2) ");
		$rest = $cose['minge'] - $yb;
		//if ($cose['xq_id'] != 0) {
		//	if ($cose['xq_id'] != $student['xq_id']) {
		//			die ( json_encode ( array (
		//				'result' => false,
		//				'msg' => '本课程只限本年级学生报名！'
		//				) ) );
  //          }					   
		//}
		
		if (empty($user['realname']) || empty($user['mobile'])) {
            die ( json_encode ( array (
                    'result' => false,
                    'msg' => '请前往个人中心完善您的联系方式'
		               ) ) );			
		}		
		if ($rest < 1){
            die ( json_encode ( array (
                    'result' => false,
                    'msg' => '本课程已满'
		               ) ) );			
		}
		if (!empty($issale)) {
            die ( json_encode ( array (
                    'result' => false,
                    'msg' => '抱歉,您已报名本课程,请查看订单'
		               ) ) );			
		}		
		if (time() >= $cose['end']) {
            die ( json_encode ( array (
                    'result' => false,
                    'msg' => '本课程已经结束'
		               ) ) );
		}		
		if (empty($_GPC['openid'])) {
            die ( json_encode ( array (
                    'result' => false,
                    'msg' => '非法请求'
		               ) ) );			
		}else{
			$schoolid = $_GPC['schoolid'];
			$weid = $_GPC['weid'];
			$sid = $_GPC['sid'];
			$userid = $_GPC['uid'];
			$orderid = "{$userid}{$sid}";
			$temp = array(
					'weid' =>  $_GPC ['weid'],
					'schoolid' => $_GPC ['schoolid'],
					'sid' => $_GPC ['sid'],
					'userid' => $_GPC ['user'],
					'type' => 1,
					'status' => 1,
					'kcid' => $_GPC ['kcid'],
					'uid' => $_GPC['uid'],
					'cose' => $final_cose,
					'payweid' => $cose['payweid'],
					'orderid' => $orderid,
					'createtime' => time(),
					'spoint' => $point_dy
			);
			
			$temp['ksnum'] = $cose['FirstNum'];
			
			if(!empty($shareuserid)){
				$ShareUserInfo = pdo_fetch("SELECT sid FROM " . tablename($this->table_user) . " WHERE :weid = weid And :schoolid = schoolid And :id = id", array(':weid' => $_GPC['weid'], ':schoolid' => $_GPC['schoolid'], ':id' => $shareuserid));
				if($ShareUserInfo['sid'] != $_GPC['sid']){
					$temp['shareuserid'] = $shareuserid;
				}
				
			}
			pdo_insert($this->table_order, $temp);
			$order_id = pdo_insertid();
			$data ['result'] = true;
			$data ['msg'] = '报名成功,请前往个人中心查看';
		 die ( json_encode ( $data ) );
		}
    }

	if ($operation == 'newstu'){

		if (! $_GPC['schoolid'] || ! $_GPC['weid']) {
               die ( json_encode ( array (
                    'result' => false,
                    'msg' => '非法请求！' 
		               ) ) );
		}else{
			if (empty($_GPC['openid'])) {
	           die ( json_encode ( array (
	                    'result' => false,
	                    'msg' => '非法请求'
			               ) ) );			
			}else{
			    $schoolid = intval($_GPC['schoolid']);
			    $weid     = intval($_GPC['weid']);
			    $openid   = $_GPC['openid'];
			    $kcid     = intval($_GPC['kcid']);
			    $uid      = $_GPC['uid'];
			    $sname    = $_GPC ['sname'];
			    $sex      = $_GPC['sex'];
			    $mobile   = $_GPC['mobile'];
			    $addr     = $_GPC['addr'];
			    $nj_id    = $_GPC['nj'];
			    $bj_id    = $_GPC['bj'];
			    $pard     = $_GPC['pard'];
				$shareuserid = $_GPC['shareuserid'];
				$cose = pdo_fetch("SELECT * FROM " . tablename($this->table_tcourse) . " WHERE :id = id", array(':id' => $kcid)); 
				$yb = pdo_fetchcolumn("select count(*) FROM ".tablename($this->table_order)." WHERE kcid = '".$cose['id']."' And status = 2 and type = 1 and schoolid = '{$schoolid}' and weid = '{weid}' ");
				$rest = $cose['minge'] - $yb;
				if ($rest < 1){
					die ( json_encode ( array ( 'result' => false, 'msg' => '本课程已满' ) ) );			
				}
				$checknewstu = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " WHERE weid = {$weid} And schoolid = {$schoolid} And s_name like '{$sname}' And mobile={$mobile} And sex = {$sex} "); 
				if(!empty($checknewstu)){
					 die ( json_encode ( array ( 'result' => false, 'msg' => '对不起，该学生已经存在！' ) ) );
				}

				$checknewstu1 = pdo_fetch("SELECT id,sname FROM " . tablename($this->table_tempstudent) . " WHERE weid = {$weid} And schoolid = {$schoolid} And sname like '{$sname}' And mobile={$mobile} And sex = {$sex} "); 
				if(!empty($checknewstu1)){

					$hasOrder = pdo_fetch("SELECT id FROM " . tablename($this->table_order) . " WHERE weid = {$weid} And schoolid = {$schoolid} And tempsid = '{$checknewstu1['id']}' And kcid={$_GPC['kcid']} And status = 1 "); 
					if(!empty($hasOrder)){
				 		die ( json_encode ( array (
				                    'result' => false,
				                    'is_order' => true,
				                    'orderId' => $hasOrder['id'],
				                    'tempstuid'=>$checknewstu1['id'],
				                   	'msg' => '对不起，该学生已报名但未支付！' 
			               		) ) );
	            		 	}
				}
				$data_tempstu = array(
					'weid' => $weid,
					'schoolid' => $schoolid,
					'sname' =>$sname,
					'mobile'=> $mobile,
					'sex' => $sex,
					'addr' => $addr,
					'nj_id' =>$nj_id,
					'bj_id' => $bj_id,
					'pard' => $pard,
					'openid' => $openid,
					'uid' => $uid
				);
				pdo_insert($this->table_tempstudent,$data_tempstu);
				$tempstuid = pdo_insertid();
				$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " WHERE :id = id", array(':id' => $_GPC['schoolid']));
				$cose = pdo_fetch("SELECT * FROM " . tablename($this->table_tcourse) . " WHERE :id = id", array(':id' => $_GPC['kcid'])); 
				$final_cose =$cose['cose'];
				$yb = pdo_fetchcolumn("select count(*) FROM ".tablename($this->table_order)." WHERE kcid = '".$cose['id']."' And (status = 2 or type = 2) ");
				$rest = $cose['minge'] - $yb;
				if ($rest < 1){
		            die ( json_encode ( array (
		                    'result' => false,
		                    'msg' => '本课程已满'
				               ) ) );			
				}
				if (time() >= $cose['end']) {
		            die ( json_encode ( array (
		                    'result' => false,
		                    'msg' => '本课程已经结束'
				               ) ) );
				}		
				$orderid = "{$uid}{$tempstuid}";
				$temp = array(
						'weid' =>  $_GPC ['weid'],
						'schoolid' => $_GPC ['schoolid'],
						'tempsid' => $tempstuid,
						'tempopenid' => $openid,
						'type' => 1,
						'status' => 1,
						'kcid' => $_GPC ['kcid'],
						'uid' => $uid,
						'cose' => $final_cose,
						'payweid' => $cose['payweid'],
						'orderid' => $orderid,
						'createtime' => time(),
				);
				
				$temp['ksnum'] = $cose['FirstNum'];
				if(!empty($shareuserid)){
					$temp['shareuserid'] = $shareuserid;
				}
				pdo_insert($this->table_order, $temp);
				$order_id = pdo_insertid();
				$data ['result'] = true;
				$data ['msg'] = '报名成功,请前往个人中心查看';
				$data ['orderid'] = $order_id;
			 	die ( json_encode ( $data ) );
			}
		}
	} 
	if ($operation == 'delsign_one'){
		if (! $_GPC['schoolid'] || ! $_GPC['weid']) {
               die ( json_encode ( array (
                    'result' => false,
                    'msg' => '非法请求！' 
		               ) ) );
		}else{
			$signid= $_GPC['signid'];
			$check = pdo_fetch("SELECT id,status FROM " . tablename($this->table_kcsign) . " WHERE :id = id", array(':id' => $signid));
			if(empty($check)){
				 die ( json_encode ( array (
                    'result' => false,
                    'msg' => '该签到记录不存在！' 
		               ) ) );
			}
			if($check['status'] ==2){
				die ( json_encode ( array (
                    'result' => false,
                    'msg' => '该签到记录已被确认，不可删除！' 
		               ) ) );
			}else{
				pdo_delete($this->table_kcsign,array('id'=>$signid));
				die ( json_encode ( array (
                    'result' => true,
                    'msg' => '删除成功！' 
		               ) ) );
			}
		}
	} 
	
	if ($operation == 'sqingjia') {
		if (! $_GPC ['schoolid'] || ! $_GPC ['weid']) {
               die ( json_encode ( array (
                    'result' => false,
                    'msg' => '非法请求！' 
		               ) ) );
		}else{
			$StuUid = explode ( ',', $_GPC ['StuUid'] );
				//die(json_encode($_GPC));
			if($StuUid){
				foreach($StuUid as $row){
					if($row > 0 ){
						 $data = array(
							'kcid' => $_GPC['kcid'],
							'schoolid' => $_GPC['schoolid'],
							'weid' => $_GPC['weid'],
							'sid'  => $row,
							'createtime' => $_GPC['time'],
							'status' => 3,
							'type' => 1,
							'qrtid'=>$_GPC['qrtid'] 
					    );
				       if($_GPC['OldOrNew'] == 1){
		  					pdo_insert($this->table_kcsign, $data);
		  					$insertid = pdo_insertid();
					    }elseif($_GPC['OldOrNew'] ==0){
						    $checkks = pdo_fetch("select id FROM ".GetTableName('kcbiao')." WHERE weid='{$_GPC['weid']}' And schoolid='{$_GPC['schoolid']}' And  id = '{$_GPC['ksid']}'");
						    if(empty($checkks)){
							die ( json_encode ( array (
			                    'result' => false,
			                    'msg' => '该课时不存在！' 
					               ) ) );
							}
						   	$data['ksid'] = $_GPC['ksid'];
						   	$data['type'] = 0 ;
							   $data['createtime'] = time() ;
							   $data['costnum'] = 0 ;
						   	pdo_insert($this->table_kcsign, $data);
						   	$insertid = pdo_insertid();
					    }
					}
				}
				die ( json_encode ( array (
                    'result' => true,
                    'msg' => '请假成功！' ,
                    'back_time' => date("H:i",time())
		               ) ) );
			}else{
				die ( json_encode ( array (
                    'result' => false,
                    'msg' => '您没有选择学生！' 
		               ) ) );
			}
	    }
	}

	if ($operation == 'kcpingjia') {
		if (! $_GPC ['schoolid'] || ! $_GPC ['weid']) {
			$data ['result'] = false;
			$data ['msg'] = '非法请求';
		}else{
			$check = $_GPC['check'];
			$schoolid = $_GPC['schoolid'];
			$weid = $_GPC['weid'];
			$kcid = $_GPC['kcid'];
			$sid = $_GPC['sid'];
			$userid = $_GPC['userid'];
			$backstr = '';
			$pingjia = $_GPC['pingjia'];
			$checkpj = pdo_fetch("SELECT content FROM " . GetTableName('kcpingjia') . " WHERE schoolid = '{$schoolid}' And weid = '{$weid}' and sid ='{$sid}' And kcid = '{$kcid}' and type=2 ");
			if(empty($checkpj)){
				foreach( $check as $key => $value )	{
					if($value != 0){
						$temp = array(
							'weid' => $weid,
							'schoolid' => $schoolid,
							'kcid' => $kcid,
							'tid' => $key,
							'sid' => $sid,
							'userid' => $userid,
							'type' => 1,
							'star' => $value,
							'createtime' => time()
						);
						pdo_insert($this->table_kcpingjia,$temp);
						$pingjun =pdo_fetchcolumn("select AVG(star) FROM ".tablename($this->table_kcpingjia)." WHERE weid='{$_GPC['weid']}' And schoolid='{$_GPC['schoolid']}' And  tid = '{$key}' AND star != 0 ");
						pdo_update($this->table_teachers,array('star'=>$pingjun),array('id'=> $key)); 
					}
				}
				if(!empty($pingjia)){
					$temp = array(
							'weid' => $weid,
							'schoolid' => $schoolid,
							'kcid' => $kcid,
							'sid' => $sid,
							'userid' => $userid,
							'is_master' => 1,
							'type' => 2,
							'content' => $pingjia,
							'createtime' => time()
						);
						pdo_insert($this->table_kcpingjia,$temp);
				}
				$data ['result'] = false;
				$data ['msg'] = '评价完成';
			}else{
				$data ['result'] = true;
				$data ['msg'] = '抱歉，您已经评论过此课程,不可重复评论！';
			}	   
		}
		die ( json_encode ( $data ) );
    }

	if ($operation == 'newkcpingjia') {
		if (! $_GPC ['schoolid'] || ! $_GPC ['weid']) {
			$data ['result'] = false;
			$data ['msg'] = '非法请求';
		}else{
			$check = $_GPC['check'];
			$text_t = $_GPC['text_t'];
			$schoolid = $_GPC['schoolid'];
			$anony = $_GPC['anony'];
			$weid = $_GPC['weid'];
			$kcid = $_GPC['kcid'];
			$sid = $_GPC['sid'];
			$userid = $_GPC['userid'];
			$backstr = '';
			$pingjia = $_GPC['pingjia'];
			$checkpj = pdo_fetch("SELECT content FROM " . GetTableName('kcpingjia') . " WHERE schoolid = '{$schoolid}' And weid = '{$weid}' and sid ='{$sid}' And kcid = '{$kcid}' and type=2 ");
			if(empty($checkpj)){
				foreach( $check as $key => $value )	{
					// if($value != 0){
						foreach($value as $key1 => $value1){
							$temp = array(
								'weid' => $weid,
								'schoolid' => $schoolid,
								'kcid' => $kcid,
								'tid' => $key,
								'sid' => $sid,
								'userid' => $userid,
								'type' => 1,
								'star' => $value1,
								'pfxmid' => $key1,
								'anony' => $anony,
								'content' => $text_t[$key],
								'createtime' => time()
							);
							pdo_insert($this->table_kcpingjia,$temp);
						}
					$pingjun =pdo_fetchcolumn("select AVG(star) FROM ".tablename($this->table_kcpingjia)." WHERE weid='{$_GPC['weid']}' And schoolid='{$_GPC['schoolid']}' And  tid = '{$key}' AND star != 0 ");
					pdo_update($this->table_teachers,array('star'=>$pingjun),array('id'=> $key)); 
				}
				if(!empty($pingjia)){
					$temp = array(
						'weid' => $weid,
						'schoolid' => $schoolid,
						'kcid' => $kcid,
						'sid' => $sid,
						'userid' => $userid,
						'type' => 2,
						'content' => $pingjia,
						'createtime' => time()
					);
					pdo_insert($this->table_kcpingjia,$temp);
				}
				$data ['result'] = true;
				$data ['msg'] = '评价完成';
			}else{
				$data ['result'] = false;
				$data ['msg'] = '抱歉，您已经评论过此课程,不可重复评论！';
			}	   
		}
		die ( json_encode ( $data ) );
	}
	
    if ($operation == 'txkcpj') {
		if (! $_GPC ['schoolid'] || ! $_GPC ['weid']) {
               die ( json_encode ( array (
                    'status' => 0,
                    'msg' => '非法请求！' 
		               ) ) );
		}else{
			$schoolid = $_GPC['schoolid'];
			$weid = $_GPC['weid'];
			$kcid = $_GPC['kcid'];
			$kcinfo = pdo_fetch("SELECT * FROM " . tablename($this->table_tcourse) . " WHERE :id = id", array(':id' => $kcid));
			if($kcinfo['is_remind_pj'] == 0){
		 		$send = $this->sendMobileTxkcpj($kcid,$schoolid,$weid);
				 if($send){
				 	pdo_update($this->table_tcourse,array('is_remind_pj' =>1),array('id'=>$kcid));
					die ( json_encode ( array (
		                'result' => true,
		                'msg' => '提醒成功，请勿重复提醒！'
			               ) ) );
		        }else{
			        die ( json_encode ( array (
		                'result' => false,
		                'msg' => '提醒失败，请稍后重试！'
			               ) ) );
		        }
	        }elseif($kcinfo['is_remind_pj'] == 1){
		        die ( json_encode ( array (
		                'result' => false,
		                'msg' => '该课程已经提醒评价，请勿重复提醒！'
			               ) ) );
		        
	        }
		}
    }

	if ($operation == 'deletetempstu') {
		if (! $_GPC ['schoolid'] || ! $_GPC ['weid']) {
               die ( json_encode ( array (
                    'status' => 0,
                    'msg' => '非法请求！' 
		               ) ) );
		}else{ 
			$schoolid = $_GPC['schoolid'];
			$weid = $_GPC['weid'];
			$orderid = $_GPC['orderid'];
			$tempstuid = $_GPC['tempstuid'];
			$kcid = $_GPC['kcid'];
			$orderinfo = pdo_fetch("SELECT tempsid FROM " . tablename($this->table_order) . " WHERE :id = id", array(':id' => $orderid));
			if($orderinfo['tempsid'] == $tempstuid){
				pdo_delete($this->table_order,array('id'=>$orderid));
				pdo_delete($this->table_tempstudent,array('id'=>$tempstuid));
				 die ( json_encode ( array (
		                'result' => true,
		                'msg' => '操作成功！'
			               ) ) );
			}else{
		        die ( json_encode ( array (
		                'result' => false,
		                'msg' => '操作失败，请重试！'
			               ) ) );
	        }
		}
    }
	if ($operation == 'get_ks_conent') {
		if (! $_GPC ['schoolid'] || ! $_GPC ['weid']) {
               die ( json_encode ( array (
                    'status' => 0,
                    'msg' => '非法请求！' 
		               ) ) );
		}else{ 
			$schoolid = $_GPC['schoolid'];
			$weid = $_GPC['weid'];
			$ksid = $_GPC['ksid'];
			$ksinfo = pdo_fetch("SELECT * FROM " . GetTableName('kcbiao') . " WHERE :id = id", array(':id' => $ksid));
			$result['conent'] = htmlspecialchars_decode($ksinfo['content']);
			$result['result'] = true;
			$result['msg'] = '获取成功';
			die(json_encode($result));
		}
    }
	if ($operation == 'add_ks_clik') {
		if (! $_GPC ['schoolid'] || ! $_GPC ['weid']) {
               die ( json_encode ( array (
                    'status' => 0,
                    'msg' => '非法请求！' 
		               ) ) );
		}else{ 
			$schoolid = $_GPC['schoolid'];
			$weid = $_GPC['weid'];
			$ksid = $_GPC['ksid'];
			$userid = $_GPC['userid'];
			$sid = $_GPC['sid'];
			$kcid = $_GPC['kcid'];
			$ksinfo = pdo_fetch("SELECT clicks FROM " . GetTableName('kcbiao') . " WHERE :id = id", array(':id' => $ksid));
			pdo_update(GetTableName('kcbiao',false), array('clicks'=>$ksinfo['clicks']+1), array('id' => $ksid));
			if(!empty($userid) && !empty($sid)){
				$signarr = array(
					'weid' 		=>$weid,
					'schoolid'  =>$schoolid,
					'sid' 		=>$sid,
					'kcid' 		=>$kcid,
					'ksid' 		=>$ksid,
					'status' 	=>2,
					'createtime'=>time(),
					'signtime' 	=>time(),
				);
				$checksign = pdo_fetch("SELECT id FROM " . GetTableName('kcsign') . " WHERE :ksid = ksid And :sid = sid", array(':sid' => $sid,':ksid' => $ksid));
				if(!empty($checksign)){
					unset($signarr['createtime']);
					pdo_update(GetTableName('kcsign',false), $signarr, array('id' => $checksign['id']));
				}else{
					pdo_insert(GetTableName('kcsign',false), $signarr);
				}
			}
			$result['result'] = true;
			$result['msg'] = '成功';
			die(json_encode($result));
		}
	}
	if ($operation == 'set_rank_list') {
		if (! $_GPC ['schoolid'] || ! $_GPC ['weid']) {
               die ( json_encode ( array (
                    'result' => false,
                    'msg' => '非法请求！' 
		               ) ) );
		}else{ 
			$kcid = $_GPC['kcid'];
			$openid = $_GPC['openid'];
			mload()->model('kc');
			$kcinfo = pdo_fetch("SELECT * FROM " . GetTableName('tcourse') . " WHERE :id = id", array(':id' => $kcid));
			$tgset = pdo_fetch("SELECT allow_normal FROM " . GetTableName('kc_promote') . " WHERE id = :id ", array(':id' => $kcinfo['tg_id']));
			if($tgset['allow_normal'] == 1){//开启了普通粉丝参与推广 显示排名
				$normaldata = GetNorFansRank($kcid,10,$openid);
				if($normaldata){
					$newrank = $normaldata['newrank'];
					foreach($newrank as $key => $row){
						if(empty($row['name']) || empty($row['icon'])){//处理个别2货取关无法查询头像的情况
							$da_user = GetWeFans($_GPC ['weid'],$row['openid']);
							$newrank[$key]['name'] = $da_user['nickname'];
							$newrank[$key]['icon'] = $da_user['avatar'];
						}
					}
					$result['newrank'] = $newrank;
					$result['result'] = true;
					$result['msg'] = '成功';
				}else{
					$result['result'] = false;
					$result['msg'] = '暂无排名数据';
				}
			}else{
				$result['result'] = false;
				$result['msg'] = '抱歉，本课程已关闭排名功能';
			}
			die(json_encode($result));
		}
    }
	if ($operation == 'show_rank_list') {
		if (! $_GPC ['schoolid'] || ! $_GPC ['weid']) {
               die ( json_encode ( array (
                    'result' => false,
                    'msg' => '非法请求！' 
		               ) ) );
		}else{ 
			mload()->model('kc');
			$kcid = $_GPC['kcid'];
			$tid = $_GPC['tid'];
			$kcinfo = pdo_fetch("SELECT tg_id FROM " . GetTableName('tcourse') . " WHERE :id = id", array(':id' => $kcid));
			$tgset = pdo_fetch("SELECT show_ranking FROM " . GetTableName('kc_promote') . " WHERE id = :id ", array(':id' => $kcinfo['tg_id']));
			if($tgset['show_ranking'] == 1){//开启了普通粉丝参与推广 显示排名
				$rankdata = GetProTeaRank($kcid,5,$tid);
				if($rankdata){
					$result['newrank'] = $rankdata['newrank'];
					$result['myrank'] = $rankdata['myrank'];
					$result['result'] = true;
					$result['msg'] = '成功';
				}else{
					$result['result'] = false;
					$result['msg'] = '暂无排名数据';
				}
			}else{
				$result['result'] = false;
				$result['msg'] = '抱歉，本课程已关闭排名功能';
			}
			die(json_encode($result));
		}
    }
	if ($operation == 'xu_zd') {
		if (! $_GPC ['schoolid'] || ! $_GPC ['weid']) {
			$result['result'] = false;
			$result['msg'] = '非法请求';
		}else{
			mload()->model('kc');
			$uidarray = array();
			$all_fans = $_GPC['all_fans'];
			if(count($all_fans) > 0){
				foreach($all_fans as $row){
					$uidarray[] = $row['openid'];
				}
				$teamid = $_GPC['teamid'];
				$pkuser = $_GPC['tid'];
				$team = pdo_fetch("SELECT kcid FROM " . GetTableName('sale_team') . " WHERE id = {$teamid}");
				$kcinfo = pdo_fetch("SELECT sale_type FROM " . GetTableName('tcourse') . " WHERE id = {$team['kcid']}");
				$result = SetXnFans($teamid,$uidarray,$pkuser);//调用虚拟插入粉丝方法
				foreach($uidarray as $openid){
					if($kcinfo['sale_type'] == 1){
						$this->sendMobilePttz($teamid,$openid);
					}
					if($kcinfo['sale_type'] == 2){
						$this->sendMobileZltz($teamid,$openid);
					}
				}
				$result['result'] = true;
			}else{
				$result['result'] = false;
				$result['msg'] = '请至少选择一个粉丝';
			}

		}
		die ( json_encode($result) );
	}
	if ($operation == 'make_zl_team') {
		if (! $_GPC ['schoolid'] || ! $_GPC ['weid']) {
			$result['result'] = false;
			$result['msg'] = '非法请求';
		}else{
			mload()->model('kc');
			$schoolid = $_GPC['schoolid'];
			$weid = $_GPC['weid'];
			$kcid = $_GPC['kcid'];
			$ggid = $_GPC['ggid'];
			$openid = $_GPC['openid'];
			$userid = $_GPC['userid'];
			$mastertid = $_GPC['mastertid'];
			$s_name = trim($_GPC['stu_name']);
			$mobile = trim($_GPC['stu_mobile']);
			$kcinfo = pdo_fetch("SELECT cose,sale_id,FirstNum,payweid,guigetype FROM " . GetTableName('tcourse') . " WHERE :id = id", array(':id' => $kcid));
			$saleset = pdo_fetch("SELECT price,overtime,overtimeset,endtime FROM " . GetTableName('kc_saleset') . " WHERE id = :id ", array(':id' => $kcinfo['sale_id']));
			if(empty($userid) && !empty($s_name) && !empty($mobile)){ //对新增的学生写入库
				mload()->model('stu');
				$checkrep = CheckStuNameForFans($s_name,$schoolid,$openid);
				if($checkrep){
					$result['result'] = false;
					$result['msg'] = '抱歉，您已经添加过该生,请从列表中选择，尝试刷新本页';
					die(json_encode($result));
				}else{
					$userdata = array('weid' => $weid,'schoolid' => $schoolid,'openid' => $openid,'realname' => $s_name,'mobile' => $mobile,'pard' => 4,'status' => 1,'com_from' => 1);
					pdo_insert(GetTableName('user',false), $userdata);
					$nowuserid = pdo_insertid();
					$account_api = WeAccount::create($weid);
					$fans_info = $account_api->fansQueryInfo($openid);
					$students = array('weid' => $weid,'schoolid' => $schoolid,'icon' => $fans_info['headimgurl'],'s_name' => $s_name,'mobile' => $mobile,'ouserid' => $nowuserid,'from_kcid' => $kcid,'superior_tid' => $mastertid,'status' => 1,'seffectivetime' => time(),'createdate' => time());
					pdo_insert(GetTableName('students',false), $students);
					$sid = pdo_insertid();
					pdo_update(GetTableName('user',false), array('sid' => $sid), array('id' => $nowuserid));
					$this->DoTag($schoolid,$nowuserid); //打标签
					$belong = CheckFansBelong($kcid,$userid,$openid);
					if(empty($mastertid) && $belong['tid']){//记录推广员
						pdo_update(GetTableName('students',false), array('superior_tid' => $belong['tid']), array('id' => $sid));
					}
					$result['is_newstu'] = true;
					$tid = $mastertid;
				}
			}else{
				$user =  pdo_fetch("SELECT sid FROM " . GetTableName('user') . " WHERE :id = id", array(':id' => $userid));
				$stu =  pdo_fetch("SELECT superior_tid FROM " . GetTableName('students') . " WHERE :id = id", array(':id' => $user['sid']));
				$nowuserid  = $userid;
				if(empty($stu['superior_tid'])){//对注册用户没有归属的进行改写
					if(!empty($mastertid)){
						$tid = $mastertid;
					}else{
						$belong = CheckFansBelong($kcid,$userid,$openid);
						$tid = $belong['tid'];
					}
					SetFansInfoByKc($kcid,$openid,$nowuserid,$tid,$masteropenid,$masteruserid,1);
					pdo_update(GetTableName('students',false), array('superior_tid' => $tid), array('id' => $sid));
				}
				$sid = $user['sid'];
				$result['is_newstu'] = false;
			}
			$team = array( 'weid' => $weid, 'schoolid' => $schoolid, 'kcid' => $kcid, 'userid' => $nowuserid, 'openid' => $openid, 'ismaster' => 1, 'type' => 2, 'createtime' => time() );
			if($saleset['overtimeset']== 1){
				$team['endtime'] = $saleset['endtime'];
			}
			if($saleset['overtimeset']== 2){
				$team['endtime'] = time() + $saleset['overtime']*3600;
			}
			pdo_insert(GetTableName('sale_team',false), $team);
			$team_id = pdo_insertid();
			pdo_update(GetTableName('sale_team',false), array('masterid'=>$team_id),array('id'=>$team_id));//修改主ID
			//创建未付订单
			$payweid = $kcinfo['payweid'] ? $kcinfo['payweid'] : $weid;
			$all_dis_price = $saleset['price'];//组队优惠
			if($kcinfo['guigetype'] == 1){
				$guige = pdo_fetch("SELECT price,ksnum FROM ".GetTableName('guige')." WHERE id = '{$ggid}' ");
				$fee = $guige['price'] - $all_dis_price; //减掉优惠
				$fee = round($fee,2);
			}else{
				$fee = $kcinfo['cose'] - $all_dis_price; //减掉优惠
				$fee = round($fee,2);
			}
			$orderdata = array(
			   'weid' => $weid,
			   'schoolid' => $schoolid,
			   'kcid' => $kcid,
			   'userid' => $nowuserid,
			   'sid' => $sid,
			   'cose' => $fee,
			   'team_price' => $saleset['price'],
			   'status' => 1,
			   'type' => 1,
			   'guigeid' => $ggid,
			   'team_id' => $team_id,//teamid
			   'sale_rule' => $kcinfo['sale_id'],
			   'sale_type' => 2,
			   'superior_tid' => $tid,
			   'ksnum' => $guige ? $guige['ksnum'] : $kcinfo['FirstNum'],
			   'payweid' => $payweid,
			   'createtime' => time()
			);
			if($gmtype == 1){
				$orderdata['new_stu'] = 1;
			}
			pdo_insert(GetTableName('order',false), $orderdata);
			$orderid = pdo_insertid();
			if($gmtype != 1){//非直接购买情况下 为team上传orderid
				pdo_update(GetTableName('sale_team',false), array('orderid' => $orderid), array('id' => $team_id));
			}
			$myteam = GetTeamList($team_id,true);
			$nowteaminfo = GetTeamBeastInfo($team_id);
			$result['nowuserid'] = $nowuserid;
			$result['nowteamid'] = $team_id;
			$result['topword'] = $nowteaminfo['tipword'];
			$result['myteam'] = $myteam;
			$result['msg'] = '创建助力活动成功';
			$result['result'] = true;
		}
		die(json_encode($result));
	}
	if ($operation == 'join_zl_team') {
		if (! $_GPC ['schoolid'] || ! $_GPC ['weid']) {
			$result['result'] = false;
			$result['msg'] = '非法请求';
		}else{
			$schoolid = $_GPC['schoolid'];
			$weid = $_GPC['weid'];
			$kcid = $_GPC['kcid'];
			$openid = $_GPC['openid'];
			$teamid = $_GPC['teamid'];
			$thisteam = pdo_fetch("SELECT openid FROM " . GetTableName('sale_team') . " WHERE :id = id ", array(':id' => $teamid));
			if($openid == $thisteam['openid']){
				$result['msg'] = '助力失败，您不可为自己发起的活动助力哦';
				$result['result'] = false;
			}else{
				$checkteam = pdo_fetch("SELECT id FROM " . GetTableName('sale_team') . " WHERE :kcid = kcid And :openid = openid", array(':kcid' => $kcid,':openid' => $openid));
				if(empty($checkteam)){
					mload()->model('kc');
					$teamisfull = CheckTemIsFull($teamid);
					if($teamisfull['isfull']){
						$result['msg'] = '失败,本队伍已经满';
						$result['result'] = false;
					}else{
						$kcinfo = pdo_fetch("SELECT sale_id FROM " . GetTableName('tcourse') . " WHERE :id = id", array(':id' => $kcid));
						$saleset = pdo_fetch("SELECT overtime,overtimeset FROM " . GetTableName('kc_saleset') . " WHERE id = :id ", array(':id' => $kcinfo['sale_id']));
						$team = array( 'weid' => $weid, 'schoolid' => $schoolid, 'kcid' => $kcid, 'openid' => $openid, 'masterid' => $teamid, 'ismaster' => 0, 'type' => 2, 'createtime' => time() );
						if($saleset['overtimeset']== 1){
							$team['endtime'] = $saleset['endtime'];
						}
						if($saleset['overtimeset']== 2){
							$team['endtime'] = time() + $saleset['overtime']*3600;
						}
						pdo_insert(GetTableName('sale_team',false), $team);
						$isfull = CheckTemIsFull($teamid);//再次检查是否满员
						if($isfull['isfull']){
							//SetTeamStuStatus($teamid,2); 此处不改写学生锁定状态，支付后再改写
						}
						$this->sendMobileZltz($teamid,$openid);
						$result['isfull'] = $isfull['isfull'];
						$result['msg'] = '助力成功';
						$result['result'] = true;
					}
				}else{
					$result['msg'] = '助力失败，您已经帮其他人助力过本课程了';
					$result['result'] = false;
				}
			}
		}
		die(json_encode($result));
	}
	if ($operation == 'get_onteam_info') {
		if (! $_GPC ['schoolid'] || ! $_GPC ['weid']) {
			$result['result'] = false;
			$result['msg'] = '非法请求';
		}else{
			$schoolid = $_GPC['schoolid'];
			$weid = $_GPC['weid'];
			$kcid = $_GPC['kcid'];
			$openid = $_GPC['openid'];
			$teamid = $_GPC['teamid'];
			$checkteam = pdo_fetch("SELECT id,kcid,masterid FROM " . GetTableName('sale_team') . " WHERE :id = id ", array(':id' => $teamid));
			if(!empty($checkteam)){
				mload()->model('kc');
				$kcinfo = pdo_fetch("SELECT name,cose,sale_type,sale_id FROM " . GetTableName('tcourse') . " WHERE :id = id ", array(':id' => $checkteam['kcid']));
				$saleset = pdo_fetch("SELECT suc_munber,price,rule_word FROM " . GetTableName('kc_saleset') . " WHERE id = :id ", array(':id' => $kcinfo['sale_id']));
				$result['kcinfo'] = $kcinfo;
				$result['saleset'] = $saleset;
				$result['nowpirce'] = round($kcinfo['cose'] - $saleset['price'],2) ;
				$result['rule'] = $saleset['rule_word'];
				$result['sale_type'] = $kcinfo['sale_type'] == 1 ? "拼团须知" : "助力须知";
				$result['teaminfo'] = GetTeamBeastInfo($checkteam['masterid']);
				$result['teamlist'] = GetTeamList($checkteam['masterid'],true);
				$result['result'] = true;
			}else{
				$result['msg'] = '没有查询到队伍信息';
				$result['result'] = false;
			}	
		}
		die(json_encode($result));
	}
	if ($operation == 'get_onorder_info') {
		if (! $_GPC ['schoolid'] || ! $_GPC ['weid']) {
			$result['result'] = false;
			$result['msg'] = '非法请求';
		}else{
			$schoolid = $_GPC['schoolid'];
			$weid = $_GPC['weid'];
			$kcid = $_GPC['kcid'];
			$openid = $_GPC['openid'];
			$orderid = $_GPC['orderid'];
			$order = pdo_fetch("SELECT * FROM " . GetTableName('order') . " WHERE :id = id ", array(':id' => $orderid));
			if(!empty($order)){
				mload()->model('kc');
				$kcinfo = pdo_fetch("SELECT Point2Cost,MinPoint,MaxPoint,sale_id FROM " . GetTableName('tcourse') . " WHERE :id = id ", array(':id' => $order['kcid']));
				$teaminfo = pdo_fetch("SELECT ismaster FROM " . GetTableName('sale_team') . " WHERE :id = id ", array(':id' => $order['team_id']));
				$school = pdo_fetch("SELECT spic FROM " . GetTableName('index') . " WHERE :id = id ", array(':id' => $order['schoolid']));
				$user = pdo_fetch("SELECT mobile FROM " . GetTableName('user') . " WHERE :id = id ", array(':id' => $order['userid']));
				$stuinfo =  pdo_fetch("SELECT s_name,icon,mobile,points FROM " . GetTableName('students') . " WHERE :id = id ", array(':id' => $order['sid']));
				$result['icon'] = !empty($stuinfo['icon']) ? tomedia($stuinfo['icon']) : tomedia($school['spic']);
				$result['s_name'] = !empty($user['mobile']) ? $stuinfo['s_name'].' '.$user['mobile'] : $stuinfo['s_name'].' '.$stuinfo['mobile'];
				$result['order'] = $order;
				$result['ordertiem'] = date('Y-m-d H:i',$order['createtime']);
				$result['paytime'] = date('Y-m-d H:i',$order['paytime']);
				$result['jfuize'] = "{$kcinfo['Point2Cost']}积分1元 · 最低{$kcinfo['MinPoint']}分 · 最高{$kcinfo['MaxPoint']}分";
				$result['myjf'] = $stuinfo['points'];
				if($order['spoint'] > 0 && $kcinfo['Point2Cost'] > 0){
					$result['ydk'] = round($order['spoint']/$kcinfo['Point2Cost'],2);
				}else{
					$result['ydk'] = 0;
				}
				$result['all_dis'] = round($order['team_price'] + $order['team_dz_price'] + $result['ydk'],2);
				$result['result'] = true;
				$result['ismaster'] = $teaminfo['ismaster'] == 1 ? true : false;
			}else{
				$result['msg'] = '没有查询到本订单信息';
				$result['result'] = false;
			}	
		}
		die(json_encode($result));
	}
	if ($operation == 'check_online_room') {
		if (! $_GPC ['schoolid'] || ! $_GPC ['weid']) {
			$result['result'] = false;
			$result['msg'] = '非法请求';
		}else{
			$schoolid = $_GPC['schoolid'];
			$weid = $_GPC['weid'];
			$ksid = $_GPC['ksid'];
			$nowtime = time();
			$ksinfo = pdo_fetch("SELECT sk_start,sk_end,kcid FROM " . GetTableName('kcbiao') . " WHERE :id = id ", array(':id' => $ksid));
			$kcinfo = pdo_fetch("SELECT txtime FROM " . GetTableName('tcourse') . " WHERE :id = id ", array(':id' => $ksinfo['kcid']));
			if($ksinfo){
				if($_GPC['comform'] == 'teacher'){
					$result['result'] = true;
				}else{
					if($ksinfo['sk_end'] > $nowtime){
						$checktime = $ksinfo['sk_start'] - $kcinfo['txtime']*60;
						if($nowtime >= $checktime){
							$result['result'] = true;
						}else{
							$result['msg'] = '抱歉，本次直播未开始！您可以在'.date('Y/m/d H:i',$checktime).'进入直播间';
							$result['result'] = false;
						}
					}else{
						$result['msg'] = '本次直播已结束';
						$result['result'] = false;
					}
				}
			}else{
				$result['msg'] = '本课时不存在,请刷新本页';
				$result['result'] = false;
			}
		}
		die(json_encode($result));
	}
	if ($operation == 'team_info') {
		if (! $_GPC ['schoolid'] || ! $_GPC ['weid']) {
			$result['result'] = false;
			$result['msg'] = '非法请求';
		}else{
			mload()->model('kc');
			$schoolid = $_GPC['schoolid'];
			$weid = $_GPC['weid'];
			$teamid = $_GPC['teamid'];
			$result['team'] = GetTeamList($teamid,true);
			$result['info'] = GetTeamBeastInfo($teamid);
			$result['result'] = true;	
		}
		die(json_encode($result));
	}
	if ($operation == 'tuif_sq') {
		if (! $_GPC ['schoolid'] || ! $_GPC ['weid']) {
			$result['result'] = false;
			$result['msg'] = '非法请求';
		}else{
			$schoolid = $_GPC['schoolid'];
			$weid = $_GPC['weid'];
			$kcid = $_GPC['kcid'];
			$openid = $_GPC['openid'];
			$teamid = $_GPC['teamid'];
			$team = pdo_fetch("SELECT tuifei FROM " . GetTableName('sale_team') . " WHERE :id = id ", array(':id' => $teamid));
			if(!empty($team)){
				if($team['tuifei'] == 1){
					$result['result'] = false;
					$result['msg'] = '您已发起退费申请,请等待审核';
				}else{
					$result['msg'] = '退费申请成功,请耐心等待审核';
					$result['false'] = true;
				}
			}else{
				$result['msg'] = '没有查询本团队信息';
				$result['result'] = false;
			}	
		}
		die(json_encode($result));
	}
	if ($operation == 'again_team') {
		if (! $_GPC ['schoolid'] || ! $_GPC ['weid']) {
			$result['result'] = false;
			$result['msg'] = '非法请求';
		}else{
			$schoolid = $_GPC['schoolid'];
			$weid = $_GPC['weid'];
			$kcid = $_GPC['kcid'];
			$type = $_GPC['type'];
			$teamid = $_GPC['teamid'];
			$team = pdo_fetch("SELECT * FROM " . GetTableName('sale_team') . " WHERE :id = id ", array(':id' => $teamid));
			if(!empty($team)){
				$kcinfo = pdo_fetch("SELECT sale_id,sale_type,end FROM " . GetTableName('tcourse') . " WHERE :id = id ", array(':id' => $team['kcid']));
				$saleset = pdo_fetch("SELECT allow_again,overtimeset,overtime FROM " . GetTableName('kc_saleset') . " WHERE id = :id ", array(':id' => $kcinfo['sale_id']));
				$word = $kcinfo['sale_type'] == 1 ? '开团' : '助力';
				if($saleset['allow_again'] == 1 || $type == 'gopay'){
					$nowtiem = time();
					if($saleset['overtimeset'] = 1){
						$overtime = $kcinfo['end'];
					}else{
						$overtime = $saleset['overtime'];
					}
					if($overtime > $nowtiem){
						$result['result'] = true;
						$result['kcid'] = $team['kcid'];
						$result['msg'] = '正在跳转至课程购买页';
					}else{
						$result['result'] = false;
						$result['msg'] = '抱歉,本次活动已结束';
					}
				}else{
					$result['msg'] = '本课不允许失败后重新'.$word;
					$result['false'] = false;
				}
				
			}else{
				$result['msg'] = '没有查询本团队信息';
				$result['result'] = false;
			}	
		}
		die(json_encode($result));	
	}
	if ($operation == 'get_stu_payinfo') {
		if (! $_GPC ['schoolid'] || ! $_GPC ['weid']) {
               die ( json_encode ( array (
                    'result' => false,
                    'msg' => '非法请求！' 
		               ) ) );
		}else{ 
			$schoolid = $_GPC['schoolid'];
			$weid = $_GPC['weid'];
			$kcid = $_GPC['kcid'];
			$gmtype = $_GPC['gmtype'];
			$teamid = $_GPC['teamid'];
			$userid = $_GPC['userid'];
			$result['is_pay'] = false;//本学生是否已开团
			$result['is_tuan'] = false;//本学生是否已开团
			$result['is_jointuan'] = false;//本学生是否已经参加了别人的团
			$result['is_zhuli'] = false;//本学生是否已开团
			$result['is_sucess'] = false;
			$result['isfull'] = false;
			$jf = false;
			$kcinfo = pdo_fetch("SELECT * FROM " . GetTableName('tcourse') . " WHERE :id = id", array(':id' => $kcid));
			$saleset = pdo_fetch("SELECT * FROM " . GetTableName('kc_saleset') . " WHERE id = :id ", array(':id' => $kcinfo['sale_id']));
			$school = pdo_fetch("SELECT Is_point FROM " . GetTableName('index') . " WHERE :id = id", array(':id' => $schoolid));
			mload()->model('kc');
			if($userid != 0){
				$user = pdo_fetch("SELECT sid,openid,mobile FROM " . GetTableName('user') . " WHERE :id = id", array(':id' => $userid));
				$checkorder = pdo_fetch("SELECT id FROM " . GetTableName('order') . " WHERE :kcid = kcid And :sid = sid And status = 2", array(':kcid' => $kcid,':sid' => $user['sid']));
				if($kcinfo['sale_type'] == 1){
					$CheckIsInSlae = CheckIsInSlae($kcid,$userid,0);
				}
				if($kcinfo['sale_type'] == 2){
					$CheckIsInSlae = CheckIsInSlae($kcid,$userid,0);
				}	
				$stu = pdo_fetch("SELECT points,icon,s_name,mobile,chongzhi FROM " . GetTableName('students') . " WHERE :id = id", array(':id' => $user['sid']));
				if($user['mobile']){
					$stu['mobile'] = $user['mobile'];
				}
				if($stu['icon']){
					$stu['icon'] = tomedia($stu['icon']);
				}
				if($school['Is_point']==1 && $kcinfo['Point2Cost'] > 0 && $stu['points'] > 0){
					$jf = true;
				}
				$isfull = false;//未满
				if($CheckIsInSlae['isinsale']){
					$isfull = CheckTemIsFull($CheckIsInSlae['masterid']);//查询队伍是否满员
					$nowteamid = $CheckIsInSlae['masterid'];
					if($CheckIsInSlae['isover']){//过期
						$CheckUnOverTema = CheckUnOverTema($kcid,$userid,$user['openid']);
						if($CheckUnOverTema['masterid'] > 0){//查询未过期的其他队伍
							$nowteamid = $CheckUnOverTema['masterid'];
							$isfull = CheckTemIsFull($CheckUnOverTema['masterid']);//根据未过期的队伍变更条件
							if($isfull){//满员
								$is_sucess = 1;
							}else{
								$is_sucess = 2;
							}
						}else{
							if($isfull){//满员
								$is_sucess = 1;
							}else{
								$is_sucess = 3;
							}
						}
					}else{//未过期
						if($isfull){//满员
							$is_sucess = 1;
						}else{//未满员情况下才是开团或助力中
							$is_sucess = 2;
						}
					}
				}else{
					$is_sucess = 4;//查询不到队伍皆为未参与
				}
				if($kcinfo['sale_type'] == 1){
					$leixing = '开团';
					$isfull = CheckTemIsFull($teamid);//再次检查是否满员
					if($isfull['isfull']){
						$result['isfull'] = true;
					}
				}
				if($kcinfo['sale_type'] == 2){
					$leixing = '助力';
					$isfull = CheckTemIsFull($teamid);//再次检查是否满员
					if($isfull['isfull']){
						$result['isfull'] = true;
					}
				}
				if($gmtype == 1){ //直接购买
					if(!empty($checkorder)){
						$result['is_pay'] = true;
					}
				}
				if($gmtype == 2 || $gmtype == 3){
					$result['is_sucess'] = $is_sucess;//1成功2进行中3失败4未发起过
					if($kcinfo['sale_type'] == 1){//开团
						$result['is_tuan'] = true;
					}
					if($kcinfo['sale_type'] == 2){//助力支付
						$result['is_zhuli'] = true;
						$result['sus_zl_num'] = $saleset['suc_munber'];
					}
					if($result['is_sucess'] == 1 || $result['is_sucess'] == 2 || $result['is_sucess'] == 3){ //查询到有队伍的条件
						if($kcinfo['sale_type'] == 2){//助力支付
							$myteam = GetTeamList($nowteamid,true);
							$result['myteam'] = $myteam;
							$nowteaminfo = GetTeamBeastInfo($nowteamid);
						}else{
							if($result['is_sucess'] == 3){
								$myteam = GetTeamList($CheckIsInSlae['masterid'],true);
								$result['myteam'] = $myteam;
								$nowteaminfo = GetTeamBeastInfo($CheckIsInSlae['masterid']);
							}else{
								$myteam = GetTeamList($nowteamid,true);
								$result['myteam'] = $myteam;
								$nowteaminfo = GetTeamBeastInfo($nowteamid);
							}
						}
						$checkteam = pdo_fetch("SELECT orderid FROM " . GetTableName('sale_team') . " WHERE :id = id", array(':id' => $nowteamid)); 
						$checkorder = pdo_fetch("SELECT status FROM " . GetTableName('order') . " WHERE :id = id", array(':id' => $checkteam['orderid'])); 
						$result['teamorderpay'] = false;
						if($checkorder['status'] == 2){
							$result['teamorderpay'] = true;
						}
						$result['topword'] = $nowteaminfo['tipword'];
						$result['nowteamid'] = $nowteamid;
						$result['nowuserid'] = $userid;
					}
					if($result['is_sucess'] == 3){
						if($saleset['allow_again'] == 1){
							$result['topword'] = "您可以再次发起".$leixing;
							$result['allow_again'] = true;//失败后 读取设置是否允许再次，允许则显示立即按钮 后续读取最新数据 粉丝列表数据仍是是上一条
						}
					}
					if($result['is_sucess'] == 4){
						$result['topword'] = '邀请'.$saleset['suc_munber'].'个好友,即可'.$leixing.'成功';
						$result['myteam'] = array();
						for($i=0; $i<$saleset['suc_munber']; $i++){
							$result['myteam'][$i]['icon'] = '';
						}
					}
					$result['is_inkc'] = false;	
					if(!empty($checkorder)){
						$result['is_inkc'] = true;	
					}
				}
				if($gmtype == 4){//参团
					if($kcinfo['sale_type'] == 1){//开团
						$result['is_tuan'] = true;
					}
					if($kcinfo['sale_type'] == 2){//发起助力
						$result['is_zhuli'] = true;
						$result['sus_zl_num'] = $saleset['suc_munber'];
					}
					$result['is_sucess'] = $is_sucess;//1成功2进行中3失败4未发起过
					if($CheckIsInSlae['isinsale'] && $CheckIsInSlae['isover']){
						if($saleset['allow_again'] != 1){
							$result['is_jointuan'] = true;
						}
					}
					if(!empty($checkorder)){
						$result['is_pay'] = true;	
					}
				}
			}else{
				if($gmtype == 2){
					$sus_zl_num = $saleset['suc_munber'];//助力成功人数
					$result['is_zhuli'] = true;
					$result['is_sucess'] = 4;//4未发起助力 
					$result['topword'] = "邀请{$sus_zl_num}个新好友助力即可低价购买学习本课，<span class='bluefont lxftime' lxfday='no' endtime='".date('Y/m/d H:i:s',$saleset['endtime'])."'></span>";
					$result['myteam'] = array(
						0 => array('icon' => ''),
						1 => array('icon' => ''),
						2 => array('icon' => ''),
						4 => array('icon' => ''),
						5 => array('icon' => ''),
					);
				}
				if($gmtype == 3){
					$result['is_sucess'] = 4;//4未开团  
					$result['is_tuan'] = true;
					$result['myteam'] = array();
					for($i=0; $i<$saleset['suc_munber']; $i++){
						$result['myteam'][$i]['icon'] = '';
					}
				}
				$result['is_jointuan'] = false;	
			}
			$result['stu'] = $stu;
			$result['jf'] = $jf;
			$result['Point2Cost'] = $kcinfo['Point2Cost'];
			$result['MinPoint'] = $kcinfo['MinPoint'];
			$result['MaxPoint'] = $kcinfo['MaxPoint'];
			$result['result'] = true;
			$result['msg'] = '获取成功';
			die(json_encode($result));
		}
    }
	if($operation == 'GetTeamData'){
		$schoolid = $_GPC['schoolid'];
		$weid = $_GPC['weid'];
		$id = $_GPC['id'];
		$team = pdo_fetch("SELECT orderid FROM ".GetTableName('sale_team')." WHERE id = '{$id}' ");
		$order = pdo_fetch("SELECT cose,guigeid,ksnum FROM ".GetTableName('order')." WHERE id = '{$team['orderid']}' ");
		$guige = pdo_fetch("SELECT price FROM ".GetTableName('guige')." WHERE id = '{$order['guigeid']}' ");
		$result['newordercose'] = $order['cose'];
		$result['orderksnum'] = $order['ksnum'];
		$result['oldordercose'] = $guige['price'];
		$result['ggid'] = $order['guigeid'];
		$result['result'] = true;
		die(json_encode($result));	
	}
	if($operation == 'check_team_isfull'){
		$schoolid = $_GPC['schoolid'];
		$weid = $_GPC['weid'];
		$teamid = $_GPC['teamid'];
		mload()->model('kc');
		$full = CheckTemIsFull($teamid);
		$result['result'] = $full;
		die(json_encode($result));	
	}
	if($operation == 'CheckKcMingE'){
		$data = explode ( '|', $_GPC ['json'] );
		if (! $_GPC ['schoolid'] || !$_GPC ['weid']) {
			die ( json_encode ( array ( 'result' => false, 'msg' => '非法请求！' ) ) );
	    }
		$cose = pdo_fetch("SELECT * FROM " . tablename($this->table_tcourse) . " WHERE :id = id", array(':id' => $_GPC['kcid'])); 
		$yb = pdo_fetchcolumn("select count(*) FROM ".tablename($this->table_order)." WHERE kcid = '".$cose['id']."' And status = 2 and type = 1 and schoolid = '{$_GPC['schoolid']}' and weid = '{$_GPC['weid']}' ");
		$rest = $cose['minge'] - $yb;		
		if ($rest < 1){
            die ( json_encode ( array ( 'result' => false, 'msg' => '本课程已满' ) ) );			
		}else{
			die ( json_encode ( array ( 'result' => true, 'msg' => '课程有剩余' ) ) );
		}	
	}
	if($operation == 'getNowKs'){ //获取当前时间段的课时
		if (! $_GPC ['schoolid'] || !$_GPC ['weid']) {
			die ( json_encode ( array ( 'result' => false, 'msg' => '非法请求！' ) ) );
	    }
		$nowtime = time(); //当前时间
		$endtime = strtotime($_GPC['endTime']) + 86399; //结束时间
		$kcid = $_GPC['kcid'];
		$ks = pdo_fetchall("SELECT k.id,from_unixtime(k.date,'%m月-%d日') as date,from_unixtime(c.sd_start,'%H:%s') as start,from_unixtime(c.sd_end,'%H:%s') as end FROM ".GetTableName('kcbiao')." as k LEFT JOIN " . GetTableName('classify') . " as c ON c.sid = k.sd_id WHERE k.schoolid = '{$_GPC['schoolid']}' AND k.kcid = '{$kcid}' AND k.date BETWEEN $nowtime AND $endtime ");
		if($ks){
			$result['data'] = $ks;
			$result['result'] = true;
		}else{
			$result['result'] = false;
			$result['msg'] = '当前时段没有排课，无需请假';
		}
		die ( json_encode ( $result ) );			
	}

	if ($operation == 'stuqj_px') {
		if (! $_GPC ['schoolid'] || ! $_GPC ['weid']) {
			$result ['result'] = false;
			$result ['msg'] = '非法请求！';
		}else{
			if($_GPC['opt'] == 'check'){
				$startime = time();
				$endtime = strtotime($_GPC ['endTime']) + 86399;
				$sid = $_GPC['sid'];
				$ksid = explode(',',trim($_GPC['ksidstr'],','));
				//判断是否已经请假
				$hasqj = pdo_fetch("SELECT ksid FROM ".GetTableName('leave')." WHERE schoolid = '{$_GPC ['schoolid']}' AND sid = '{$sid}' AND ((startime1 <= $startime AND endtime1 <= $endtime) OR startime1 <= $endtime OR endtime1 <= $endtime OR (startime1 >= $startime AND endtime1 <= $endtime)) AND status = 0");
				$qjnum = 0;
				if(!empty($hasqj)){
					$qjksidarr = explode(',',$hasqj['ksid']);
					foreach ($ksid as $value) {
						if(in_array($value,$qjksidarr)){ //表示当前时间段有请假
							$qjnum++;
						}
					}
				}
				if($qjnum > 0){
					$result ['result'] = false;
					$result ['msg'] = '当前时间段已有'.$qjnum.'次请假,请更换请假课时';
				}else{
					$result ['result'] = true;
				}
			}else{
				$schoolid = $_GPC['schoolid'];
				$weid = $_GPC['weid'];
				$ksarr = $_GPC['ksarr'];
				$data = array(
					'weid' =>  $_GPC ['weid'],
					'schoolid' => $_GPC ['schoolid'],
					'openid' => $_GPC ['openid'],
					'sid' => $_GPC ['sid'],
					'uid' => $_GPC['uid'],
					'type' => $_GPC ['type'],
					'startime1' => time(),
					'endtime1' => strtotime($_GPC ['endTime']) + 86399,
					'conet' => $_GPC ['content'],
					'createtime' => time(),
					'ksid' => trim($_GPC['ksidstr'],','),
					'kcid' => $_GPC['kcid'],
				);
				pdo_insert($this->table_leave, $data);
				$leave_id = pdo_insertid();
				$ksid = explode(',',trim($_GPC['ksidstr'],','));
				foreach ($ksid as $value) {
					//为kcsign添加一条待老师确认请假记录
					$data2 = array(
						'weid' => $_GPC ['weid'],
						'schoolid' => $_GPC ['schoolid'],
						'kcid' => $_GPC['kcid'],
						'ksid' => $value,
						'sid' => $_GPC ['sid'],
						'createtime' => time(),
						'signtime' => time(),
						'status' => 1,
						'qrtid' => 0,
						'qjid' => $leave_id,
					);
					pdo_insert($this->table_kcsign, $data2);
				}
				$sktid = pdo_fetch("SELECT tid FROM ".GetTableName('tcourse')." WHERE schoolid = '{$_GPC ['schoolid']}' AND id = '{$_GPC['kcid']}'")['tid'];
				$tid = explode(',',trim($sktid,','));
				foreach ($tid as $totid) {
					$this->sendMobileXsqj($leave_id, $schoolid, $weid, $totid,$_GPC['schooltype']);
				}
				$result ['result'] = true;
				$result ['msg'] = '已将请假申请发送至覆盖课程的主讲老师，请勿重复申请！';
			}
		}
		die ( json_encode ( $result ) );
	}

	if($operation == 'getWelfare'){ //获取当前时间段的课时
		if (! $_GPC ['schoolid'] || !$_GPC ['weid']) {
			die ( json_encode ( array ( 'result' => false, 'msg' => '非法请求！' ) ) );
		}
		mload()->model('pay');
		//获取当前用户的优惠券及红包
		$redpacket = getMyAllWelfare($_GPC['openid'],$_GPC ['schoolid'],1);
		$coupon = getMyAllWelfare($_GPC['openid'],$_GPC ['schoolid'],2);
		if($redpacket || $coupon){
			$result['redpacket'] = $redpacket;
			$result['coupon'] = $coupon;
			$result['result'] = true;
		}else{
			$result['result'] = false;
			$result['msg'] = '没有可以使用的优惠券或红包';
		}
		die ( json_encode ( $result ) );			
	}
?>