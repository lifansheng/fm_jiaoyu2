<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
        global $_W, $_GPC;
        $weid = $_W['uniacid'];
		$schoolid = intval($_GPC['schoolid']);
		$userss = !empty($_GPC['userid']) ? intval($_GPC['userid']) : 1;
		$openid = $_W['openid'];
		
		if(!empty($_GPC['userid'])){
			mload()->model('user');
			$_SESSION['user'] = check_userlogin($weid,$schoolid,$openid,$userss);
			if ($_SESSION['user'] == 2){
				include $this->template('bangding');
			}
		}
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where id = :id And openid = :openid", array(':id' => $_SESSION['user'],':openid' => $openid));

		$user = pdo_fetchall("SELECT * FROM " . tablename($this->table_user) . " where :weid = weid And :openid = openid And :tid = tid", array(
				':weid' => $weid,
				':openid' => $openid,
				':tid' => 0
		));
		$bdsun = count($user);
		foreach($user as $key => $row){
			$student = pdo_fetch("SELECT s_name,id,schoolid,bj_id FROM " . tablename($this->table_students) . " where id=:id ", array(':id' => $row['sid']));
			$bajinam = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where sid=:sid ", array(':sid' => $student['bj_id']));
			$user[$key]['s_name'] = $student['s_name'];
			$user[$key]['bjname'] = $bajinam['sname'];
			$user[$key]['sid'] = $student['id'];  
			$user[$key]['schoolid'] = $student['schoolid'];
		}		
		$school = pdo_fetch("SELECT style2,title,videopic,headcolor FROM " . tablename($this->table_index) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $schoolid));	
        if(!empty($it)){
			$student = pdo_fetch("SELECT id,bj_id,s_name FROM " . tablename($this->table_students) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $it['sid']));
			$mybj = pdo_fetch("SELECT * FROM " . tablename($this->table_classify) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And sid = '{$student['bj_id']}' ");
			$myplsl  = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_camerapl) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND bj_id = '{$student['bj_id']}' And type = 2");
			$mydzsl  = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_camerapl) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND bj_id = '{$student['bj_id']}' And type = 1");
			$myisdz = pdo_fetch("SELECT id FROM " . tablename($this->table_camerapl) . " where weid = :weid AND schoolid = :schoolid AND bj_id = :bj_id AND userid = :userid AND type =:type", array(':weid' => $weid, ':schoolid' => $schoolid, ':bj_id' => $student['bj_id'], ':userid' => $it['id'],':type' => 1));		
			/**
			 *  一，取出学生所有课程
			 *  二，筛选有剩余课时的课程 $a;
			 *  三, 筛选当前时间点正在上课的课程与课时 $b;
			*  取list find_in_set( $a);
			 */

			if($_W['schooltype']){
				//当前学生的所有课程
				$mykcid = []; //当前有剩余课时的课程
				$mycourse = pdo_fetchall("SELECT c.kcid,c.ksnum,IFNULL(("." SELECT SUM(costnum) FROM ". GetTableName('kcsign') ." as k WHERE k.sid = '{$it['sid']}' AND k.schoolid = '{$schoolid}' AND k.kcid = c.kcid AND k.status = 2),0) as signnum FROM ".GetTableName('coursebuy')." as c WHERE schoolid = '{$schoolid}' AND sid = '{$it['sid']}' ");
				foreach ($mycourse as $key => $value) {
					if($value['ksnum'] - $value['signnum'] <=0){
						unset($mycourse[$key]);
					}else{
						$mykcid[] = $value['kcid'];
					}
				}
				$mykcidstr = arrayToString($mykcid);
				$nowtime = time();
				$endtime = strtotime(date('Y-m-d')) + 86399;
				$mintime = strtotime(date('Y-m-d'));
				$allks = pdo_fetchall("SELECT kc.kcid,c.sd_end,kc.date FROM " . GetTableName('kcbiao') . " as kc LEFT JOIN " . GetTableName('classify') . " as c ON c.sid = kc.sd_id WHERE kc.schoolid ='{$schoolid}' AND FIND_IN_SET(kc.kcid,'{$mykcidstr}') And (kc.date BETWEEN {$mintime} and {$endtime}) ORDER BY kc.date ASC");
				$nowkcid = [];
				foreach ($allks as $key => $value) {
					$signTime = pdo_fetch("SELECT signTime FROM ".GetTableName('tcourse')." WHERE id = '{$value['kcid']}' AND schoolid = '{$schoolid}' ")['signTime'];
					$jstime = time() + $signTime*60; //开始计算时间
					
					$hours = strtotime(date("H:i:s",$value['sd_end']));
					if($hours > $nowtime && $value['date'] < $jstime){
						$nowkcid[] = $value['kcid'];
					}
				}

				$list1 = pdo_fetchall("SELECT * FROM " . tablename($this->table_allcamera) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND ispx = '1' ORDER BY ssort DESC");
				$list = [];
				foreach ($list1 as $key => $row) {
					$kcidarr = explode(',',$row['kcidstr']);
					$IsS = false;
					$IsNow = false;
					foreach ($kcidarr as $value) {
						if(in_array($value,$mykcid) || $row['videotype'] == 2){
							$IsS = true;
						}
						if(in_array($value,$nowkcid)){
							$IsNow = true;
						}
					}
					if(!$IsS){
						continue;
					}
					$plsl  = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_camerapl) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND carmeraid = '{$row['id']}' And type = 2");
					$dzsl  = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_camerapl) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND carmeraid = '{$row['id']}' And type = 1");
					$isdz = pdo_fetch("SELECT id FROM " . tablename($this->table_camerapl) . " where weid = :weid AND schoolid = :schoolid AND carmeraid = :carmeraid AND userid = :userid", array(':weid' => $weid, ':schoolid' => $schoolid, ':carmeraid' => $row['id'], ':userid' => $it['id']));
					$row['plsl'] = $plsl;
					$row['dianzan'] = $dzsl;
					$row['isdz'] = $isdz;					
					$row['IsNow'] = $IsNow;					
					$list[] = $row;
				}
			}else{
				$list = pdo_fetchall("SELECT * FROM " . tablename($this->table_allcamera) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' ORDER BY ssort DESC");
			
				foreach ($list as $key => $row) {
					$plsl  = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_camerapl) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND carmeraid = '{$row['id']}' And type = 2");
					$dzsl  = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_camerapl) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND carmeraid = '{$row['id']}' And type = 1");
					$isdz = pdo_fetch("SELECT id FROM " . tablename($this->table_camerapl) . " where weid = :weid AND schoolid = :schoolid AND carmeraid = :carmeraid AND userid = :userid", array(':weid' => $weid, ':schoolid' => $schoolid, ':carmeraid' => $row['id'], ':userid' => $it['id']));
					$list[$key]['plsl'] = $plsl;
					$list[$key]['dianzan'] = $dzsl;
					$list[$key]['isdz'] = $isdz;					
					if(!empty($row['bj_id'])){
						$list[$key]['myvideo'] = explode(',', $row['bj_id']);
						foreach($list[$key]['myvideo'] as $r) {
								$list[$key]['myvideo']['pic'] == $row['videopic'];
								$list[$key]['myvideo']['name'] == $row['name'];
						}
					}
				}
			}
			
			include $this->template(''.$school['style2'].'/allcamera');
        }else{
			session_destroy();
		    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
			exit;
        }        
?>