<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
        global $_W, $_GPC;
        $weid = $_W['uniacid'];
		$schoolid = intval($_GPC['schoolid']);
		$openid = $_W['openid'];
		$obid = 2;
		$userss = intval($_GPC['userid']);
		$act = "bjq";
		//获取用户信息   （Lee 0721） 
		if(!empty($schoolid)){
			$condis = "And schoolid = '{$schoolid}'";
		}
		$user = pdo_fetchall("SELECT * FROM " . tablename($this->table_user) . " where weid = '{$weid}' And openid = '{$openid}' And tid = 0 $condis ");		
		if(empty($user)){
			session_destroy();
			if(empty($_GPC['schoolid'])){
				$stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bjq');
			}else{
				$stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bjq', array('schoolid' => $schoolid));
			}
			header("location:$stopurl");
			exit;		
		}
		$num = count($user);
		$flag = 1;
		if ($num > 1){
			$flag = 2;
		}
		$icons1 = pdo_fetchall("SELECT * FROM " .GetTableName('icon') . " where weid = :weid And schoolid = :schoolid And place = :place AND status = :status ORDER by id ASC", array(':weid' => $weid, ':schoolid' => $schoolid, ':place' => 26,':status'=>1));
    	$icons2 = pdo_fetchall("SELECT * FROM " .GetTableName('icon') . " where weid = :weid And schoolid = :schoolid And place = :place AND status = :status ORDER by id ASC", array(':weid' => $weid, ':schoolid' => $schoolid, ':place' => 27,':status'=>1));
		foreach($user as $key => $row){
			//获取学生信息   （Lee 0721） 
			$student = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where id=:id ", array(':id' => $row['sid']));
			$bajinam = pdo_fetch("SELECT * FROM " . tablename($this->table_classify) . " where sid=:sid ", array(':sid' => $student['bj_id']));
			$user[$key]['s_name'] = $student['s_name'];
			$user[$key]['bjname'] = $bajinam['sname'];
			$user[$key]['sid'] = $student['id'];
			$user[$key]['schoolid'] = $student['schoolid'];
		}
		if(!empty($userss)){
			$ite = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $userss));
			if(!empty($ite)){
				// 如果这个userss在user表里存在，就把ID写入session （Lee 0721）
				$_SESSION['user'] = $ite['id'];
				$schoolid = $ite['schoolid'];
			}else{
				//如果这个userss在user表里不存在，就跳转，查这个微信是否绑定了教师  （Lee 0721） 
				//同一个微信，绑定学生和绑定教师的话在user表里的ID是不同的  （Lee 0721）
				$stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('myschool', array('schoolid' => $schoolid));
				header("location:$stopurl");
				exit;
			}
		}else{
			//如果从GPC里没有获取到ID,就采用查询表的方式取得ID并写入session （Lee 0721）
			if(empty($_SESSION['user'])){
				if(!empty($_GPC['schoolid'])){
					$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :weid = weid And :schoolid = schoolid And :openid = openid And :tid = tid LIMIT 0,1 ", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':tid' => 0), 'id');
					$_SESSION['user'] = $userid['id'];					
				}else{
					$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :weid = weid And :openid = openid And :tid = tid LIMIT 0,1 ", array(':weid' => $weid, ':openid' => $openid, ':tid' => 0));
					$_SESSION['user'] = $userid['id'];
					$schoolid = $userid['schoolid'];
				}
			}
		}
        //查询是否用户登录
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $_SESSION['user']));	
		if(empty($_GPC['schoolid'])){
			$schoolid = $it['schoolid'];
		}
		$school = pdo_fetch("SELECT style2,title,bjqstyle,spic,isopen,headcolor,Is_point FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id", array(':weid' => $weid, ':id' => $schoolid));
		$students = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $it['sid']));
        $shenfen = "";	
		$isopen = 0;
		if ($it['pard'] == 2){
			$shenfen = "母亲";
		}else if($it['pard'] == 3){	
		    $shenfen = "父亲";
		}else if($it['pard'] == 4){	
		    $shenfen = "本人";			
		}else if($it['pard'] == 5){	
		    $shenfen = "家长";			
		}		
        $bj_id = $students['bj_id'];
		$bjset = pdo_fetch("SELECT is_bjzx,star,addedinfo FROM " . tablename($this->table_classify) . " where schoolid = :schoolid AND sid=:sid", array(':schoolid' => $schoolid, ':sid' => $bj_id));
		$star = unserialize($bjset['star']);
		$thistime = strtotime($_GPC['limit']);
		$lasttime = strtotime($_GPC['lasttime']);
        if (is_showgkk())
            $starname = $bjset['addedinfo']?json_decode($bjset['addedinfo'],true):array();
    	$condition = '';
		if($school['bjqstyle'] =='new'){
			$condition .= " And ( bj_id1 = '{$bj_id}' Or bj_id2 = '{$bj_id}' Or bj_id3 = '{$bj_id}' or is_all = '1' ) ";
		}
		mload()->model('ali');
		$aliyun = GetAliApp($_W['uniacid'],$_GPC['schoolid']);
		$appid = $aliyun['alivodappid'];
		$key = $aliyun['alivodkey'];
		if($thistime || $lasttime){
			//下拉后的内容 （lee 0721）
			if($thistime){
				$condition .= " AND createtime < '{$thistime}'";
				$list1 = pdo_fetchall("SELECT * FROM " . tablename($this->table_bjq) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And type = 0 And (isopen = 0 Or uid = '{$it['uid']}') $condition ORDER BY createtime DESC LIMIT 0,10");
				foreach ($list1 as $index => $v) {
					if (!empty($v['sherid'])) {
						$list1[$index]['picurl'] = pdo_fetchall("SELECT * FROM " . tablename($this->table_media) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND sherid = '{$v['sherid']}'  ORDER BY id ASC" );
						//获取点赞信息  如果点赞的不止一个人，则$list1[$index]['znames']里是数组  （Lee 0721）
						$list1[$index]['znames'] = pdo_fetchall("SELECT * FROM " . tablename($this->table_dianzan) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND sherid = '{$v['sherid']}'  ORDER BY createtime ASC LIMIT 0,4" );
						
						//点赞人数 （lee 0721）
						$list1[$index]['num']= count($list1[$index]['znames']); //（lee 0721）
						
						$list1[$index]['contents'] = pdo_fetchall("SELECT * FROM " . tablename($this->table_bjq) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND type=1 AND sherid ={$v['sherid']}  ORDER BY createtime ASC" );
						$list1[$index]['isdianz'] = false;
						$isdianz = pdo_fetch("SELECT id FROM " . tablename($this->table_dianzan) . " where schoolid = '{$schoolid}' And sherid = '{$v['id']}' And ( userid = '{$it['id']}' Or uid = '{$it['uid']}')");
						if($isdianz){
							$list1[$index]['isdianz'] = true;
						}
					}
					if($aliyun['result'] && $v['ali_vod_id']){
						if(empty($v['video'])){
							$GetAliVideoUrl = GetAliVideoUrl($appid,$key,trim($v['ali_vod_id']));
							$video = $GetAliVideoUrl['PlayURL'];
							pdo_update($this->table_bjq,array('video'=>$video),array('id'=>$v['id']));
						}
						if(empty($v['videoimg'])){
							$GetAliVideoCover = GetAliVideoCover($appid,$key,trim($v['ali_vod_id']));
							$videoimg = $GetAliVideoCover['CoverURL'];
							pdo_update($this->table_bjq,array('videoimg'=>$videoimg),array('id'=>$v['id']));
						}
					}					
					//$members = pdo_fetch("SELECT avatar FROM " . tablename ( 'mc_members' ) . " where uniacid = :uniacid And uid = :uid ORDER BY uid ASC", array(':uniacid' => $weid, ':uid' => $v['uid']));
					$member =  GetWeFans($weid,$v['openid']);
					$list1[$index]['avatar'] = $member['avatar'];
					$list1[$index]['time'] = sub_day($v['createtime']);
				}
			}
			if($lasttime){
				$condition .= " AND createtime > '{$lasttime}'";
				$list1 = pdo_fetchall("SELECT * FROM " . tablename($this->table_bjq) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And type = 0 And (isopen = 0 Or uid = '{$it['uid']}') $condition ORDER BY createtime DESC LIMIT 0,10");
				foreach ($list1 as $index => $v) {
					if (!empty($v['sherid'])) {
						$list1[$index]['picurl'] = pdo_fetchall("SELECT * FROM " . tablename($this->table_media) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND sherid = '{$v['sherid']}'  ORDER BY id ASC" );
						//获取点赞信息  如果点赞的不止一个人，则$list1[$index]['znames']里是数组  （Lee 0721）
						$list1[$index]['znames'] = pdo_fetchall("SELECT * FROM " . tablename($this->table_dianzan) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND sherid = '{$v['sherid']}'  ORDER BY createtime ASC LIMIT 0,4" );
						
						//点赞人数 （lee 0721）
						$list1[$index]['num']= count($list1[$index]['znames']); //（lee 0721）
						
						$list1[$index]['contents'] = pdo_fetchall("SELECT * FROM " . tablename($this->table_bjq) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND type=1 AND sherid ={$v['sherid']}  ORDER BY createtime ASC" );
						$list1[$index]['isdianz'] = false;
						$isdianz = pdo_fetch("SELECT id FROM " . tablename($this->table_dianzan) . " where schoolid = '{$schoolid}' And sherid = '{$v['id']}' And ( userid = '{$it['id']}' Or uid = '{$it['uid']}')");
						if($isdianz){
							$list1[$index]['isdianz'] = true;
						}
					}
					if($aliyun['result'] && $v['ali_vod_id']){
						if(empty($v['video'])){
							$GetAliVideoUrl = GetAliVideoUrl($appid,$key,trim($v['ali_vod_id']));
							$video = $GetAliVideoUrl['PlayURL'];
							pdo_update($this->table_bjq,array('video'=>$video),array('id'=>$v['id']));
						}
						if(empty($v['videoimg'])){
							$GetAliVideoCover = GetAliVideoCover($appid,$key,trim($v['ali_vod_id']));
							$videoimg = $GetAliVideoCover['CoverURL'];
							pdo_update($this->table_bjq,array('videoimg'=>$videoimg),array('id'=>$v['id']));
						}
					}					
					// $members = pdo_fetch("SELECT avatar FROM " . tablename('mc_members') . " where uniacid = :uniacid And uid = :uid ORDER BY uid ASC", array(':uniacid' => $weid, ':uid' => $v['uid']));
					$member =  GetWeFans($weid,$v['openid']);
					$list1[$index]['avatar'] = $member['avatar'];
					$list1[$index]['time'] = sub_day($v['createtime']);
				}
			}			
			include $this->template('comtool/bjqlist');
		}else{
			//一开始的内容 （lee 0721）
			if(!empty($it)){
				$list = pdo_fetchall("SELECT * FROM " . tablename($this->table_bjq) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And type = 0 And (isopen = 0 Or uid = '{$it['uid']}') $condition ORDER BY createtime DESC LIMIT 0,10 ");
				foreach ($list as $index => $row) {
					 if (!empty($row['sherid'])) {
						$list[$index]['picurl'] = pdo_fetchall("SELECT * FROM " . tablename($this->table_media) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND sherid = '{$row['sherid']}'  ORDER BY id ASC" );
						$list[$index]['zname'] = pdo_fetchall("SELECT * FROM " . tablename($this->table_dianzan) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND sherid = '{$row['sherid']}'  ORDER BY createtime ASC" );
						$list[$index]['num']= count($list[$index]['zname']);
						$list[$index]['contents'] = pdo_fetchall("SELECT * FROM " . tablename($this->table_bjq) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND type=1 AND sherid = '{$row['sherid']}'  ORDER BY createtime ASC" );
						$list[$index]['isdianz'] = false;
						$isdianz = pdo_fetch("SELECT id FROM " . tablename($this->table_dianzan) . " where schoolid = '{$schoolid}' And sherid = '{$row['id']}' And ( userid = '{$it['id']}' Or uid = '{$it['uid']}')");
						if($isdianz){
							$list[$index]['isdianz'] = true;
						}
					}
					if($aliyun['result'] && $row['ali_vod_id']){
						if(empty($row['video'])){
							$GetAliVideoUrl = GetAliVideoUrl($appid,$key,trim($row['ali_vod_id']));
							$video = $GetAliVideoUrl['PlayURL'];
							pdo_update($this->table_bjq,array('video'=>$video),array('id'=>$row['id']));
						}
						if(empty($row['videoimg'])){
							$GetAliVideoCover = GetAliVideoCover($appid,$key,trim($row['ali_vod_id']));
							$videoimg = $GetAliVideoCover['CoverURL'];
							pdo_update($this->table_bjq,array('videoimg'=>$videoimg),array('id'=>$row['id']));
						}
					}
					// $member = pdo_fetch("SELECT avatar FROM " . tablename ( 'mc_members' ) . " where uniacid = :uniacid And uid = :uid ", array(':uniacid' => $weid, ':uid' => $row['uid']));
					$member =  GetWeFans($weid,$row['openid']);
					$list[$index]['avatar'] = $member['avatar'];	
					$list[$index]['time'] = sub_day($row['createtime']);	
				}
			 	//检查用户是否付费开通了班级圈功能	 （lee 0721）		
				$this->checkpay($schoolid, $students['id'], $it['id'], $it['uid']);
				$this->checkobjiect($schoolid, $students['id'], $obid);
				include $this->template(''.$school['style2'].'/sbjqnew');
			}
		}   
		    
?>