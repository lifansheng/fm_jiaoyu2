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
		$time = $_GPC['time'];
        
        //查询是否用户登录		
		$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $userid['id']));
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid)); 		
        if(!empty($userid['id'])){
			$user = pdo_fetch("SELECT status FROM " . tablename($this->table_teachers) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $it['tid']));
			if(empty($time)){
				$starttime = mktime(0,0,0,date("m"),date("d"),date("Y"));	 
				$endtime = $starttime + 86399;	
				$condition .= " AND createtime > '{$starttime}' AND createtime < '{$endtime}'";
			}else{				
				$date = explode ( '-', $time );				
				$starttime = mktime(0,0,0,$date[1],$date[2],$date[0]);				
				$endtime = $starttime + 86399;				
				$condition .= " AND createtime > '{$starttime}' AND createtime < '{$endtime}'";
			}			
			if(!empty($_GPC['nj_id'])){
				$nj_id = $_GPC['nj_id'];
			}else{
				$mybjlist = pdo_fetch("SELECT sid FROM " . tablename($this->table_classify) . " where schoolid = {$schoolid} And type = 'semester' And tid = {$it['tid']} ORDER BY ssort DESC LIMIT 0,1 ");
				$nj_id = $mybjlist['sid'];
			}


			// if ($user['status'] == 2){       
				$teacher = pdo_fetchall("SELECT id,tname FROM " . tablename($this->table_teachers) . " where weid = '{$weid}' AND schoolid = '{$schoolid}' ORDER BY id ASC");
				$snum = count($teacher);
				$notrowcount = 0;
				foreach($teacher as $index => $row){
					$ischeck = pdo_fetch("SELECT id FROM " . tablename($this->table_checklog) . " where tid = {$row['id']} $condition ");
					$jxlog = pdo_fetchcolumn("SELECT count(id) FROM " . tablename($this->table_checklog) . " where tid = {$row['id']} And leixing = 1 AND type != '异常进校' $condition ");
					$lxlog = pdo_fetchcolumn("SELECT count(id) FROM " . tablename($this->table_checklog) . " where tid = {$row['id']} And leixing = 2 And type != '异常离校' $condition ");
					$ycjxlog = pdo_fetchcolumn("SELECT count(id) FROM " . tablename($this->table_checklog) . " where tid = {$row['id']} And type = '异常进校' $condition ");
					$yclxlog = pdo_fetchcolumn("SELECT count(id) FROM " . tablename($this->table_checklog) . " where tid = {$row['id']} And type = '异常离校' $condition ");
					$teacher[$index]['ischeck'] = $ischeck['id'];	
					$teacher[$index]['jxnum'] = $jxlog;
					$teacher[$index]['lxnum'] = $lxlog;
					$teacher[$index]['ycjxlog'] = $ycjxlog;	
					$teacher[$index]['yclxlog'] = $yclxlog;	
					if (!empty($ischeck)){
						$notrowcount++;
					}						
				}	
			// }

			// if ($user['status'] == 3){
			// 	$njlist = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where schoolid = :schoolid And weid = :weid And type = :type And tid = :tid ORDER BY sid DESC", array(':weid' => $weid,':schoolid' => $schoolid,':type' => 'semester',':tid' => $it['tid']));	
			// 	$bjidname = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where :sid = sid ", array(':sid' => $nj_id));          
			// 	$allbjarr = pdo_fetchall("SELECT sid FROM " . tablename($this->table_classify) . " where schoolid = :schoolid And weid = :weid And type = :type And parentid = :parentid ORDER BY sid DESC", array(':weid' => $weid,':schoolid' => $schoolid,':type' => 'theclass',':parentid' => $nj_id));	
			// 	$allbj = array();
			// 	foreach($allbjarr as $k => $v){
			// 		$allbj[$k] = $v['sid'];
			// 	}
			// 	$alltbj = pdo_fetchall("SELECT bj_id,tid FROM " . tablename($this->table_class) . " where schoolid = :schoolid And weid = :weid ORDER BY id DESC", array(':weid' => $weid,':schoolid' => $schoolid));
			// 	$allls = array();
			// 	$order = 1;
			// 	foreach($alltbj as $key => $val){
			// 		if(in_array($val['bj_id'],$allbj)){
			// 			if(!in_array($val['tid'],$allls)){
			// 				$allls[$order] = $val['tid'];
			// 				$order ++;
			// 			}
			// 		}
			// 	}
			// 	$snum = count($allls);
			// 	$notrowcount = 0;
			// 	$teacher = pdo_fetchall("SELECT id,tname FROM " . tablename($this->table_teachers) . " where weid = '{$weid}' AND schoolid = '{$schoolid}' ORDER BY id ASC");
			// 	foreach($teacher as $index => $row){
			// 		if(in_array($row['id'],$allls)){
			// 			$ischeck = pdo_fetch("SELECT id FROM " . tablename($this->table_checklog) . " where tid = {$row['id']} $condition ");
			// 			$jxlog = pdo_fetchcolumn("SELECT count(id) FROM " . tablename($this->table_checklog) . " where tid = {$row['id']} And leixing = 1 $condition ");
			// 			$lxlog = pdo_fetchcolumn("SELECT count(id) FROM " . tablename($this->table_checklog) . " where tid = {$row['id']} And leixing = 2 $condition ");
			// 			$yclog = pdo_fetchcolumn("SELECT count(id) FROM " . tablename($this->table_checklog) . " where tid = {$row['id']} And leixing = 3 $condition ");
			// 			$teacher[$index]['ischeck'] = $ischeck['id'];	
			// 			$teacher[$index]['jxnum'] = $jxlog;
			// 			$teacher[$index]['lxnum'] = $lxlog;
			// 			$teacher[$index]['ycnum'] = $yclog;	
			// 			if (!empty($ischeck)){
			// 				$notrowcount++;
			// 			}
			// 		}	
			// 	}			
			// }
			include $this->template(''.$school['style3'].'/jschecklog');
        }else{
			session_destroy();
            $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
        }        
?>