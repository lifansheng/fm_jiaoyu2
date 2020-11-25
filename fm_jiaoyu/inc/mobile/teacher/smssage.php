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
		$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id = :id ", array(':weid' => $weid, ':id' => $userid['id']));	
		$tid_global = $it['tid'];
		if (!(IsHasQx($tid_global,2000401,2,$schoolid))){
			message('您无权查看本页面');
		}
		$schoolset = pdo_fetch("SELECT * FROM ".GetTableName('schoolset')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' ");
		if(keep_Blacklist()){
			//获取病因填写字段内容
			$By_content = unserialize($schoolset['bingyincontent']);
		}

		if(keep_DD()){
			$senddoor = $schoolset['senddoor'];
		}
		
		$teachers = pdo_fetch("SELECT id,tname,status FROM " . tablename($this->table_teachers) . " where weid = :weid AND id=:id", array(':weid' => $weid, ':id' => $it['tid']));		
		$school = pdo_fetch("SELECT style3,title,tpic FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id", array(':weid' => $weid, ':id' => $schoolid));
        if(!empty($userid['id'])){

			if($_W['schooltype']){
				if(!empty($_GPC['kcid'])){
					$mykc = pdo_fetch("SELECT name FROM " . tablename($this->table_tcourse) . " where schoolid = {$schoolid} And id = '{$_GPC['kcid']}' LIMIT 0,1 ");
					$kcid = $_GPC['kcid'];
				}else{
					if($teachers['status'] == 2){
						$mykc = pdo_fetch("SELECT id,name FROM " . tablename($this->table_tcourse) . " where schoolid = {$schoolid} ORDER BY id DESC LIMIT 0,1 ");
					}else{
						$mykc = pdo_fetch("SELECT id,name FROM " . tablename($this->table_tcourse) . " where schoolid = {$schoolid} And FIND_IN_SET('{$tid_global}',tid) ORDER BY id DESC LIMIT 0,1 ");
					}
					$kcid = $mykc['id'];
				}
				// 是否为校长
				if($teachers['status'] == 2){
					$kclist = pdo_fetchAll("SELECT id,name FROM " . tablename($this->table_tcourse) . " where schoolid = {$schoolid} ORDER BY id DESC");
					
				}else{
					$kclist = pdo_fetchAll("SELECT id,name FROM " . tablename($this->table_tcourse) . " where schoolid = {$schoolid} And FIND_IN_SET('{$tid_global}',tid) ORDER BY id DESC");
				}
				
				foreach ($kclist as $key => $value) {
					$stunum = pdo_fetchcolumn("SELECT count(DISTINCT sid) FROM ".GetTableName('coursebuy')." WHERE kcid = '{$value['id']}' ");
					$kclist[$key]['stunum'] = $stunum ? $stunum : 0;
				}
				
				$leave = pdo_fetchall("SELECT * FROM " . tablename($this->table_leave) . " where :schoolid = schoolid And :weid = weid And :tid = tid And :kcid = kcid And :isliuyan = isliuyan AND status != :status ORDER BY status ASC , createtime DESC", array(
					':weid' => $weid,
					':schoolid' => $schoolid,
					':kcid' => $kcid,
					':tid' => 0,
					':isliuyan' => 0,
					':status' => 3
					));
			   $thisid = 0;	 
			   foreach ($leave as $index => $row) {
				   //病因
				   if(keep_Blacklist()){
					   $byinfo = pdo_fetch("SELECT is_heal FROM ".GetTableName('byinfo')." WHERE id = '{$row['byid']}' ");
					   $leave[$index]['is_heal'] = $byinfo['is_heal'];
				   }

				   $member = pdo_fetch("SELECT * FROM " . tablename ( 'mc_members' ) . " where uniacid = :uniacid And uid = :uid ", array(':uniacid' => $weid, ':uid' => $row['uid']));
				   $student = pdo_fetch("SELECT * FROM " . tablename ($this->table_students) . " where weid = :weid And id = :id ", array(':weid' => $weid, ':id' => $row['sid']));
				   $user = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where uid = :uid And openid = :openid And sid = :sid ", array(':uid' => $row['uid'],':openid' => $row['openid'],':sid' => $row['sid']));
				   if($user['pard'] ==2){
					   $leave[$index]['guanxi'] = "妈妈";
				   }
				   if($user['pard'] ==3){
					   $leave[$index]['guanxi'] = "爸爸";
				   }
				   if($user['pard'] ==4){
					   $leave[$index]['guanxi'] = "本人";
				   }
				   if($user['pard'] ==5){
					   $leave[$index]['guanxi'] = "家长";
				   }
				   if(!$row['cltid']){
					   $teacher = pdo_fetch("SELECT tname,thumb FROM " . tablename($this->table_teachers) . " where id = :id AND schoolid = :schoolid ", array(':id' => $it['tid'], ':schoolid' => $schoolid));
					   $leave[$index]['tname'] = $teacher['tname'];
					   $leave[$index]['thumb'] = !empty($teacher['thumb'])?$teacher['thumb']:$school['tpic'];
				   }else{
					   $teacher = pdo_fetch("SELECT tname,thumb FROM " . tablename($this->table_teachers) . " where id = :id AND schoolid = :schoolid ", array(':id' => $row['cltid'], ':schoolid' => $schoolid));
					   $leave[$index]['tname'] = $teacher['tname'];
					   $leave[$index]['thumb'] = !empty($teacher['thumb'])?$teacher['thumb']:$school['tpic'];
				   }
				   $leave[$index]['s_name'] = $student['s_name'];
				   $leave[$index]['key'] = $thisid;
				   $thisid ++;
			   }	 

			}else{
				if(!empty($_GPC['bj_id'])){
					$bj_id = $_GPC['bj_id'];
				}else{
					$mybjlist = pdo_fetch("SELECT * FROM " . tablename($this->table_classify) . " where schoolid = {$schoolid} And type = 'theclass' And tid = {$it['tid']} ORDER BY ssort DESC LIMIT 0,1 ");
					$bj_id = $mybjlist['sid'];
					if($teachers['status'] == 2){
						$mybjlist = pdo_fetch("SELECT * FROM " . tablename($this->table_classify) . " where schoolid = {$schoolid} And type = 'theclass' ORDER BY ssort DESC ");
						 	
						$bj_id = $mybjlist['sid'];
					}
				}
				$bjlist = pdo_fetchall("SELECT * FROM " . tablename($this->table_classify) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And tid = '{$it['tid']}' And type = 'theclass' ORDER BY sid ASC, ssort DESC");		
				if($teachers['status'] == 2){
					$bjlist = pdo_fetchall("SELECT * FROM " . tablename($this->table_classify) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And type = 'theclass' ORDER BY sid ASC, ssort DESC");		
					 
				}	
				$bjidname = pdo_fetch("SELECT * FROM " . tablename($this->table_classify) . " where :sid = sid ", array(':sid' => $bj_id));			
				$leave = pdo_fetchall("SELECT * FROM " . tablename($this->table_leave) . " where :schoolid = schoolid And :weid = weid And :tid = tid And :bj_id = bj_id And :isliuyan = isliuyan AND status != :status ORDER BY status ASC , createtime DESC", array(
					 ':weid' => $weid,
					 ':schoolid' => $schoolid,
					 ':bj_id' => $bj_id,
					 ':tid' => 0,
					 ':isliuyan' => 0,
					 ':status' => 3
					 ));
				$thisid = 0;	 
				foreach ($leave as $index => $row) {
					//病因
					if(keep_Blacklist()){
						$byinfo = pdo_fetch("SELECT is_heal FROM ".GetTableName('byinfo')." WHERE id = '{$row['byid']}' ");
						$leave[$index]['is_heal'] = $byinfo['is_heal'];
					}
					$member = pdo_fetch("SELECT * FROM " . tablename ( 'mc_members' ) . " where uniacid = :uniacid And uid = :uid ", array(':uniacid' => $weid, ':uid' => $row['uid']));
					$student = pdo_fetch("SELECT * FROM " . tablename ($this->table_students) . " where weid = :weid And id = :id ", array(':weid' => $weid, ':id' => $row['sid']));
					$user = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where uid = :uid And openid = :openid And sid = :sid ", array(':uid' => $row['uid'],':openid' => $row['openid'],':sid' => $row['sid']));
					if($user['pard'] ==2){
						$leave[$index]['guanxi'] = "妈妈";
					}
					if($user['pard'] ==3){
						$leave[$index]['guanxi'] = "爸爸";
					}
					if($user['pard'] ==4){
						$leave[$index]['guanxi'] = "本人";
					}
					if($user['pard'] ==5){
						$leave[$index]['guanxi'] = "家长";
					}
					if(keep_Ls()){
						$audios = iunserializer($row['audio']);
						$urls = $_W['attachurl']; 
						$leave[$index]['audio'] = $urls.$audios['audio'][0];
						$leave[$index]['audioTime'] = $audios['audioTime'][0];
					}
					if(!$row['cltid']){
						$teacher = pdo_fetch("SELECT tname,thumb FROM " . tablename($this->table_teachers) . " where id = :id AND schoolid = :schoolid ", array(':id' => $it['tid'], ':schoolid' => $schoolid));
						$leave[$index]['tname'] = $teacher['tname'];
						$leave[$index]['thumb'] = !empty($teacher['thumb'])?$teacher['thumb']:$school['tpic'];					
					}else{
						$teacher = pdo_fetch("SELECT tname,thumb FROM " . tablename($this->table_teachers) . " where id = :id AND schoolid = :schoolid ", array(':id' => $row['cltid'], ':schoolid' => $schoolid));
						$leave[$index]['tname'] = $teacher['tname'];
						$leave[$index]['thumb'] = !empty($teacher['thumb'])?$teacher['thumb']:$school['tpic'];
					}
					$leave[$index]['s_name'] = $student['s_name'];
					$leave[$index]['key'] = $thisid;
					$thisid ++;
				}	 			
			}
			
			include $this->template(''.$school['style3'].'/smssage');
        }else{
			session_destroy();
            $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
        }        
?>