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
		$schooltype  = $_W['schooltype'];
		$obid = 1;
		
        //查询是否用户登录
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where id = :id ", array(':id' => $_SESSION['user']));
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id", array(':weid' => $weid, ':id' => $schoolid));
		$student = pdo_fetch("SELECT id,bj_id FROM " . tablename($this->table_students) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $it['sid']));		
		$bj_id = $student['bj_id'];
		$restxy = pdo_fetchcolumn("SELECT count(*) FROM ".tablename($this->table_record)." WHERE sid = '{$it['sid']}' And type = 3 And readtime < 1 And userid = '{$it['id']}' ");
		$restbj = pdo_fetchcolumn("SELECT count(*) FROM ".tablename($this->table_record)." WHERE sid = '{$it['sid']}' And type = 1 And readtime < 1 And userid = '{$it['id']}' ");
		$stuallkc = pdo_fetchall("SELECT distinct kcid FROM ".tablename($this->table_order)." where sid = '{$student['id']}' And type = 1 And status = 2 And sid != 0 ");
		$kclallsarr = '';
		foreach($stuallkc as $key){
			$kclallsarr .= $key['kcid'].",";
		}
		$noticeytpe = !empty($_GPC['noticeytpe'])?intval($_GPC['noticeytpe']):2;
		$thistime = trim($_GPC['limit']);
		if(empty($thistime) && empty($_GPC['noticeytpe'])){
			if($noticeytpe == 2){
				$condition = " And( groupid = 1 OR groupid = 3 OR groupid = 4 OR groupid = 5)";
			}
		    $leave = pdo_fetchall("SELECT id,bj_id,kc_id,km_id,title,tname,createtime,type,tid,content,usertype,userdatas,groupid FROM " . tablename($this->table_notice) . " where weid = '{$weid}' And schoolid = '{$schoolid}' And type = '{$noticeytpe}' $condition ORDER BY createtime DESC LIMIT 0,20 ");
			foreach($leave as $key =>$row){
				$banji = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where sid = :sid And schoolid = :schoolid ", array(':schoolid' => $schoolid,':sid' => $row['bj_id']));
				if($row['kc_id']){
					$banji = pdo_fetch("SELECT name as sname FROM " . tablename($this->table_tcourse) . " WHERE :id = id ", array(':id' => $row['kc_id']));
				}
				$teach = pdo_fetch("SELECT status,thumb,tname FROM " . tablename($this->table_teachers) . " where id = :id ", array(':id' => $row['tid']));
				$leave[$key]['banji'] = $banji['sname'];
				$leave[$key]['tname'] = $teach['tname'];
				$leave[$key]['thumb'] = empty($teach['thumb']) ? $school['tpic'] : $teach['thumb'];
				$leave[$key]['shenfen'] = get_teacher($teach['status']);
				mload()->model('read');
				$ydrs = check_readtype($weid,$schoolid,$it['id'],$row['id']);
				if($ydrs == 2){
					$leave[$key]['ydrs'] = "未读";
				}
				if($row['type'] ==1){
					$leave[$key]['tzlx'] = "{$language['snoticelist_bjtz']}";
				}else{
					$leave[$key]['tzlx'] = "{$language['snoticelist_xytz']}";
				}				
				$leave[$key]['time'] = date('Y-m-d H:i', $row['createtime']);
				if($row['usertype'] == 'student'){
					$userdatas = explode(';',$row['userdatas']);
					if(!in_array($student['id'],$userdatas)){
						unset($leave[$key]);
					}
				}
				if($row['usertype'] == 'send_class'){
					$userdatas = explode(';',$row['userdatas']);
					if(!in_array($student['bj_id'],$userdatas)){
						unset($leave[$key]);
					}
				}
			}
			include $this->template(''.$school['style2'].'/snoticelist');	
		}else{
			if($thistime){
				$condition = " AND createtime < '{$thistime}'";	
			}
			if($noticeytpe == 1){
				if($schooltype){
					$condition2 = " AND FIND_IN_SET(kc_id,'{$kclallsarr}') ";
				}else{
					$condition2 = " AND bj_id = '{$bj_id}'";
				}
			}
			if($noticeytpe == 2){
				$condition3 = " AND(groupid = 1 OR groupid = 3 OR groupid = 4 OR groupid = 5)";
			}
		    $leave1 = pdo_fetchall("SELECT id,bj_id,kc_id,km_id,title,tname,createtime,type,tid,content,usertype,userdatas,groupid FROM " . tablename($this->table_notice) . " where weid = '{$weid}' And schoolid = '{$schoolid}' And type = '{$noticeytpe}' $condition2 $condition $condition3 ORDER BY createtime DESC LIMIT 0,20 ");
			foreach($leave1 as $key =>$row){
				$banji = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where sid = :sid And schoolid = :schoolid ", array(':schoolid' => $schoolid,':sid' => $row['bj_id']));
				if($row['kc_id']){
					$banji = pdo_fetch("SELECT name as sname FROM " . tablename($this->table_tcourse) . " WHERE :id = id ", array(':id' => $row['kc_id']));
				}
				$teach = pdo_fetch("SELECT status,thumb,tname FROM " . tablename($this->table_teachers) . " where id = :id ", array(':id' => $row['tid']));
				$leave1[$key]['banji'] = $banji['sname'];
				$leave1[$key]['tname'] = $teach['tname'];
				$leave1[$key]['thumb'] = empty($teach['thumb']) ? $school['tpic'] : $teach['thumb'];
				$leave1[$key]['shenfen'] = get_teacher($teach['status']);
				mload()->model('read');
				$ydrs = check_readtype($weid,$schoolid,$it['id'],$row['id']);
				if($ydrs == 2){
					$leave1[$key]['ydrs'] = "未读";
				}
				if($row['type'] ==1){
					$leave1[$key]['tzlx'] = "{$language['snoticelist_bjtz']}";
				}else{
					$leave1[$key]['tzlx'] = "{$language['snoticelist_xytz']}";
				}				
				$leave1[$key]['time'] = date('Y-m-d H:i', $row['createtime']);
				if($row['usertype'] == 'student'){
					if($noticeytpe == 2){
						$userdatas = explode(';',$row['userdatas']);
						if(!in_array($student['id'],$userdatas)){
							unset($leave1[$key]);
						}
					}else{
						$datass = str_replace('&quot;','"',$row['userdatas']);
						$userdatas = json_decode($datass,true);
						if($schooltype){
							$stulist = explode(',',rtrim($userdatas[$row['kc_id']],','));
							//var_dump($userdatas[$row['kc_id']]);
						}else{
							$stulist = explode(',',rtrim($userdatas[$bj_id],','));
						}
						if(!in_array($student['id'],$stulist)){
							unset($leave1[$key]);
						}
					}
				}
				if($noticeytpe == 2){
					if($row['usertype'] == 'send_class'){
						$userdatas = explode(';',$row['userdatas']);
						if(!in_array($student['bj_id'],$userdatas)){
							unset($leave1[$key]);
						}
					}
				}
			} 
			include $this->template('comtool/snotelist'); 
		}		
        if(!empty($it)){
		
			//$this->checkobjiect($schoolid, $student['id'], $obid);
				 									
        }else{
			session_destroy();
		    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
			exit;
        }        
?>