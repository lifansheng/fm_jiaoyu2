<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
        global $_W, $_GPC;
        $weid = $_W ['uniacid'];
        $from_user = $_W['openid'];
		$schoolid = intval($_GPC['schoolid']);
		$openid = $_W['openid'];
		$act = "bjq";
        if(empty($_GPC['schoolid'])){
			$it = pdo_fetch("SELECT tid,uid,schoolid ,id FROM " . tablename($this->table_user) . " where :weid = weid And :openid = openid And :sid = sid LIMIT 0,1 ", array(':weid' => $weid, ':openid' => $openid, ':sid' => 0));
			$schoolid = $it['schoolid'];	
		}else{
			$it = pdo_fetch("SELECT tid,uid,id FROM " . tablename($this->table_user) . " where weid = :weid And :schoolid = schoolid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0));			
		}
		if(empty($it)){
			session_destroy();
			if(empty($_GPC['schoolid'])){
				$stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('binding');
			}else{
				$stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			}
			header("location:$stopurl");
			exit;
		}
		$schoollist = get_myschool($weid,$openid);
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id", array(':weid' => $weid, ':id' => $schoolid));
		$mallsetinfo = unserialize($school['mallsetinfo']);
		$teachers = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " where weid = :weid And schoolid = :schoolid AND id = :id", array(':weid' => $weid, ':schoolid' => $schoolid, ':id' => $it['tid']));
		if(is_njzr($teachers['id'])){
			$myfisrtnj =  pdo_fetch("SELECT sid FROM " . tablename($this->table_classify) . " where weid = '{$weid}' And schoolid = '{$schoolid}' And tid = '{$it['tid']}' And type = 'semester'");
			$fisrtbj =  pdo_fetch("SELECT sid as bj_id FROM " . tablename($this->table_classify) . " where weid = '{$weid}' And schoolid = '{$schoolid}' And parentid = '{$myfisrtnj['sid']}'");
		}else{
			if($teachers['status'] == 2){
				$myfisrtnj =  pdo_fetch("SELECT sid FROM " . tablename($this->table_classify) . " where weid = '{$weid}' And schoolid = '{$schoolid}' And tid = '{$it['tid']}' And type = 'semester'");
				$fisrtbj =  pdo_fetch("SELECT sid as bj_id FROM " . tablename($this->table_classify) . " where weid = '{$weid}' And schoolid = '{$schoolid}' And parentid = '{$myfisrtnj['sid']}'");
			}else{
				$fisrtbj =  pdo_fetch("SELECT bj_id FROM " . tablename($this->table_class) . " where weid = '{$weid}' And schoolid = '{$schoolid}'  And tid = {$it['tid']} ");
			}			
		}
		if(!empty($_GPC['bj_id'])){
			$bj_id = intval($_GPC['bj_id']);			
		}else{
			$bj_id = $fisrtbj['bj_id'];
		}
		$nowbj = pdo_fetch("SELECT sname,parentid,is_bjzx,star,addedinfo FROM " . tablename($this->table_classify) . " where sid = :sid ", array(':sid' => $bj_id));
		$star = unserialize($nowbj['star']);
        if (is_showgkk())
		    $starname = $nowbj['addedinfo']?json_decode($nowbj['addedinfo'],true):array();
		$bzj = pdo_fetch("SELECT sid FROM " . tablename($this->table_classify) . " where weid = :weid And schoolid = :schoolid And tid = :tid And type = 'theclass' And sid = :sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':tid' => $it['tid'], ':sid' => $bj_id));
		if($bzj){
			$thisclassstu = pdo_fetchall("SELECT id,icon,s_name FROM " . tablename($this->table_students) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And bj_id = '{$bj_id}' ORDER BY id DESC");
		}
		$bnjzr = pdo_fetch("SELECT sid FROM " . tablename($this->table_classify) . " where weid = :weid And schoolid = :schoolid And tid = :tid And sid = :sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':tid' => $it['tid'], ':sid' => $nowbj['parentid']));
		$bjlists = get_mylist($schoolid,$it['tid'],'teacher');
		if($teachers['status'] == 2){
			$bjlist = pdo_fetchall("SELECT * FROM " . tablename($this->table_classify) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And type = 'theclass' ORDER BY sid ASC, ssort DESC");
		}
		if(is_njzr($teachers['id'])){
			$mynjlist = pdo_fetchall("SELECT * FROM " . tablename($this->table_classify) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And tid = '{$it['tid']}' And type = 'semester' ORDER BY ssort DESC");
			foreach($mynjlist as $key =>$row){
				$mynjlist[$key]['bjlist'] = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And parentid = '{$row['sid']}' And type = 'theclass' ORDER BY sid ASC, ssort DESC");
				foreach($mynjlist[$key]['bjlist'] as $k => $v){

				}
			}
		}		
		$thistime = strtotime($_GPC['limit']);
		$lasttime = strtotime($_GPC['lasttime']);
		$condition = '';
		$manger = false;
		if($school['bjqstyle'] =='new'){
			$condition .= " And ( bj_id1 = '{$bj_id}' Or bj_id2 = '{$bj_id}' Or bj_id3 = '{$bj_id}' Or is_all='1') ";
			$sh_teacherids = explode(',',$school['sh_teacherids']);
			if(in_array($it['tid'],$sh_teacherids)){
				$manger = true;
			}
		}
		mload()->model('ali');
		$aliyun = GetAliApp($_W['uniacid'],$_GPC['schoolid']);
		$appid = $aliyun['alivodappid'];
		$key = $aliyun['alivodkey'];
		if($thistime || $lasttime){
			if($thistime){
				$condition .= " AND createtime < '{$thistime}'";
				if(is_showgkk())
				{
						$list1 = pdo_fetchall("SELECT * FROM " . tablename($this->table_bjq) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And ((type = 0) Or ( type = 2 )) $condition ORDER BY createtime DESC LIMIT 0,10");
				}else{
						$list1 = pdo_fetchall("SELECT * FROM " . tablename($this->table_bjq) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And type = 0 $condition ORDER BY createtime DESC LIMIT 0,10");
				}
				
				foreach ($list1 as $index => $v) {
					if (!empty($v['sherid'])) {
						$list1[$index]['picurl'] = pdo_fetchall("SELECT * FROM " . tablename($this->table_media) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND sherid =:sherid  ORDER BY id ASC", array(':sherid'=>$v['sherid']));
						$list1[$index]['znames'] = pdo_fetchall("SELECT * FROM " . tablename($this->table_dianzan) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND sherid =:sherid  ORDER BY createtime ASC ", array(':sherid'=>$v['sherid']));
						$num = count($list1[$index]['zname']);
						$list1[$index]['contents'] = pdo_fetchall("SELECT * FROM " . tablename($this->table_bjq) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND type=1 AND sherid =:sherid  ORDER BY createtime ASC", array(':sherid'=>$v['sherid']));
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
					// $members = pdo_fetch("SELECT avatar FROM " . tablename ( 'mc_members' ) . " where uniacid = :uniacid And uid = :uid ORDER BY uid ASC", array(':uniacid' => $weid, ':uid' => $v['uid']));
					$member =  GetWeFans($weid,$v['openid']);
					$list1[$index]['avatar'] = $member['avatar'];
					$list1[$index]['time'] = sub_day($v['createtime']);
				}
			}
			if($lasttime){
				$condition .= " AND createtime > '{$lasttime}'";
				$list1 = pdo_fetchall("SELECT * FROM " . tablename($this->table_bjq) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And type = 0  $condition ORDER BY createtime DESC LIMIT 0,10");
				foreach ($list1 as $index => $v) {
					if (!empty($v['sherid'])) {
						$list1[$index]['picurl'] = pdo_fetchall("SELECT * FROM " . tablename($this->table_media) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND sherid =:sherid  ORDER BY id ASC", array(':sherid'=>$v['sherid']));
						$list1[$index]['znames'] = pdo_fetchall("SELECT * FROM " . tablename($this->table_dianzan) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND sherid =:sherid  ORDER BY createtime ASC ", array(':sherid'=>$v['sherid']));
						$num = count($list1[$index]['zname']);
						$list1[$index]['contents'] = pdo_fetchall("SELECT * FROM " . tablename($this->table_bjq) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND type=1 AND sherid =:sherid  ORDER BY createtime ASC", array(':sherid'=>$v['sherid']));
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
					// $members = pdo_fetch("SELECT avatar FROM " . tablename ( 'mc_members' ) . " where uniacid = :uniacid And uid = :uid ORDER BY uid ASC", array(':uniacid' => $weid, ':uid' => $v['uid']));
					$member =  GetWeFans($weid,$v['openid']);
					$list1[$index]['avatar'] = $member['avatar'];
					$list1[$index]['time'] = sub_day($v['createtime']);
				}
			}			
			include $this->template('comtool/bjqlist');
		}else{
			if(is_showgkk())
			{
				$list = pdo_fetchall("SELECT * FROM " . tablename($this->table_bjq) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And ((type = 0) Or ( type = 2 ))  $condition ORDER BY createtime DESC LIMIT 0,10 ");
			}else{
				$list = pdo_fetchall("SELECT * FROM " . tablename($this->table_bjq) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And type = 0 $condition ORDER BY createtime DESC LIMIT 0,10 ");
			} 		
		   // $children = array();
			foreach ($list as $index => $row) {
				if (!empty($row['sherid'])) {
					$list[$index]['picurl'] = pdo_fetchall("SELECT * FROM " . tablename($this->table_media) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND sherid =:sherid  ORDER BY id ASC", array(':sherid'=>$row['sherid']));
					$list[$index]['zname'] = pdo_fetchall("SELECT * FROM " . tablename($this->table_dianzan) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND sherid =:sherid  ORDER BY createtime ASC ", array(':sherid'=>$row['sherid']));
					$list[$index]['contents'] = pdo_fetchall("SELECT * FROM " . tablename($this->table_bjq) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND type=1 AND sherid =:sherid  ORDER BY createtime ASC", array(':sherid'=>$row['sherid']));
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
				// $member = pdo_fetch("SELECT * FROM " . tablename ( 'mc_members' ) . " where uniacid = :uniacid And uid = :uid ORDER BY uid ASC", array(':uniacid' => $weid, ':uid' => $row['uid']));
				$member =  GetWeFans($weid,$row['openid']);
				$list[$index]['avatar'] = $member['avatar'];
				$list[$index]['time'] = sub_day($row['createtime']);
			}					
			include $this->template(''.$school['style3'].'/bjqnew');						
		}        
?>