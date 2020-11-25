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
		$schooltype = $_W['schooltype'];
        
        //查询是否用户登录		
		$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');	
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ORDER BY id DESC", array(':weid' => $weid, ':id' => $userid['id']));
		$tid_global = $it['tid'];
		if (!(IsHasQx($tid_global,2000301,2,$schoolid))){
			message('您无权查看本页面');
		}		
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ORDER BY ssort DESC", array(':weid' => $weid, ':id' => $schoolid));
		$teachers = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $it['tid']));		
		$bjlists = get_mylist($schoolid,$it['tid'],'teacher');	
		mload()->model('tea');
		$bjlists = GetAllClassInfoByTid($schoolid,2,$schooltype,$it['tid']);
		if(!empty($_GPC['bj_id'])){
			$bj_id = intval($_GPC['bj_id']);			
		}else{
			$bj_id = intval($bjlists[0]['sid']);
		}
		if($schooltype){
			$nowbj = pdo_fetch("SELECT name as sname FROM " . tablename($this->table_tcourse) . " where id = :id ", array(':id' => $bj_id));
			$condition2 = " AND kc_id = '{$bj_id}' ";
		}else{
			$nowbj = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where sid = :sid ", array(':sid' => $bj_id));
			$condition2 = " AND bj_id = '{$bj_id}' ";
		}		
		$thistime = trim($_GPC['limit']);
		if($thistime){
			$condition = " AND createtime < '{$thistime}'";	
			$leave1 = pdo_fetchall("SELECT id,bj_id,title,tname,tid,createtime,content,ismobile,km_id,usertype,kc_id FROM " . tablename($this->table_notice) . " where weid = '{$weid}' And schoolid = '{$schoolid}' And type = 3 $condition2 $condition ORDER BY createtime DESC LIMIT 0,10 ");
			foreach($leave1 as $key =>$row){
				if($schooltype){
					$banji = pdo_fetch("SELECT name as sname FROM " . tablename($this->table_tcourse) . " where id = :id ", array(':id' => $row['kc_id']));
				}else{
					$banji = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where sid = :sid ", array(':sid' => $row['bj_id']));
				}
				$kemu = pdo_fetch("SELECT sname,icon FROM " . tablename($this->table_classify) . " where sid = :sid And schoolid = :schoolid ", array(':schoolid' => $schoolid,':sid' => $row['km_id']));
				$teach = pdo_fetch("SELECT status,thumb,tname FROM " . tablename($this->table_teachers) . " where id = :id ", array(':id' => $row['tid']));
				$leave1[$key]['kmname'] = $kemu['sname'];
				$leave1[$key]['kmicon'] = empty($kemu['icon']) ? $school['logo'] : $kemu['icon'];
				$leave1[$key]['banji'] = $banji['sname'];
				$leave1[$key]['name'] = $teach['tname'];
				$list2 = pdo_fetchall("SELECT id FROM ".tablename($this->table_record)." WHERE weid = '{$weid}' And schoolid = '{$schoolid}' And noticeid = '{$row['id']}' And readtime > 5000 ");
				$leave1[$key]['is_njzr'] = check_bj($it['tid'],$row['bj_id']);
				$leave1[$key]['ydrs'] = "已读".count($list2)."人";
				$leave1[$key]['time'] = date('Y-m-d H:i', $row['createtime']);	
			} 
			include $this->template('comtool/zuoyelist'); 
		}else{
			$leave = pdo_fetchall("SELECT id,bj_id,title,tname,tid,createtime,content,ismobile,km_id,usertype,kc_id FROM " . tablename($this->table_notice) . " where weid = '{$weid}' And schoolid = '{$schoolid}' And type = 3 $condition2 ORDER BY createtime DESC LIMIT 0,10 ");
			//print_r($leave1);
			foreach($leave as $key =>$row){
				if($schooltype){
					$banji = pdo_fetch("SELECT name as sname FROM " . tablename($this->table_tcourse) . " where id = :id ", array(':id' => $row['kc_id']));
				}else{
					$banji = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where sid = :sid ", array(':sid' => $row['bj_id']));
				}
				$teach = pdo_fetch("SELECT status,thumb,tname FROM " . tablename($this->table_teachers) . " where id = :id ", array(':id' => $row['tid']));
				$kemu = pdo_fetch("SELECT sname,icon FROM " . tablename($this->table_classify) . " where sid = :sid And schoolid = :schoolid ", array(':schoolid' => $schoolid,':sid' => $row['km_id']));
				$teach = pdo_fetch("SELECT status,thumb,tname FROM " . tablename($this->table_teachers) . " where id = :id ", array(':id' => $row['tid']));
				$leave[$key]['kmname'] = $kemu['sname'];
				$leave[$key]['kmicon'] = empty($kemu['icon']) ? $school['logo'] : $kemu['icon'];
				$leave[$key]['banji'] = $banji['sname'];
				$leave[$key]['name'] = $teach['tname'];
				$leave[$key]['is_njzr'] = check_bj($it['tid'],$row['bj_id']);
				$list2 = pdo_fetchall("SELECT id FROM ".tablename($this->table_record)." WHERE weid = '{$weid}' And schoolid = '{$schoolid}' And noticeid = '{$row['id']}' And readtime > 5000 ");
				$leave[$key]['ydrs'] = "已读".count($list2)."人";	
				$leave[$key]['time'] = date('Y-m-d H:i', $row['createtime']);	
			} 
			include $this->template(''.$school['style3'].'/zuoyelist');	
		}				        		
        if(empty($userid['id'])){
			session_destroy();
            $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
        }        
?>