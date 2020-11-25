<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
        global $_W, $_GPC;
        $weid = $_W ['uniacid']; 
		$openid = $_W['openid'];
		$schoolid = intval($_GPC['schoolid']);
        if($_GPC['userid']){
			$_SESSION['user'] = $_GPC['userid'];
		}
		//教师列表按教师入职时间先后顺序排列，先入职再前

		$it = pdo_fetch("SELECT * FROM " . GetTableName('user') . " where id = :id ", array(':id' => $_SESSION['user']));	
		if($it){
			$school = pdo_fetch("SELECT * FROM " . GetTableName('index') . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
			$student = pdo_fetch("SELECT xq_id,bj_id FROM " . GetTableName('students') . " WHERE schoolid = :schoolid And id = :id", array(':schoolid' => $schoolid,':id' => $it['sid']));
            mload()->model('stu');
			$list = getHasSyksStu($schoolid,$it['sid']);
            mload()->model('kc');
			foreach($list as $key => $row){
				$kcinfo = pdo_fetch("SELECT * FROM " . GetTableName('tcourse') . " WHERE :schoolid = schoolid And :id = id", array(':id' => $row['kcid'], ':schoolid' => $schoolid));
				if(!empty($kcinfo)){
					$km  = pdo_fetch("SELECT sname,icon FROM " . GetTableName('classify') . " WHERE :schoolid = schoolid And :sid = sid", array(':sid' => $kcinfo['km_id'], ':schoolid' => $schoolid));
					$js  = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE :schoolid = schoolid And :sid = sid", array(':sid' => $kcinfo['adrr'], ':schoolid' => $schoolid));
					$teacher = pdo_fetch("SELECT tname FROM " . GetTableName('teachers') . " WHERE :schoolid = schoolid And :id = id", array(':id' => $kcinfo['maintid'], ':schoolid' => $schoolid)); 
					$check = pdo_fetch("SELECT content FROM " . GetTableName('kcpingjia') . " WHERE schoolid = '{$schoolid}' And weid = '{$weid}' and sid ='{$it['sid']}' And kcid = '{$row['kcid']}' and type=2 ");
					$checkovertime = pdo_fetch("SELECT overtime FROM " .GetTableName('coursebuy'). " WHERE schoolid = '{$schoolid}' And weid = '{$weid}' and sid ='{$it['sid']}' And kcid = '{$row['kcid']}' ");
					if(keep_sk77()){
						if($checkovertime['overtime'] != 0 ){
							$overtimeRemind = $checkovertime['overtime'] - $kcinfo['remindday']*86400;
							if(time() > $overtimeRemind && time() < $checkovertime['overtime'] ){
								$list[$key]['isOver'] = 'near';
							}elseif(time() >= $checkovertime['overtime'] ){
								$list[$key]['isOver'] = 'over';
							}
						}
					}
					if(keep_sk77()){
						$list[$key]['kcstatus'] = GetStuKcStatus($schoolid,$weid,$row['sid'],$row['kcid']);
					}
					$list[$key]['is_try'] = $kcinfo['is_try'];
					$list[$key]['name']   = $kcinfo['name'];
					$list[$key]['kmname'] = $km['sname'];
					$list[$key]['jsname'] = $js['sname'];
					$list[$key]['zjanme'] = $teacher['tname'];
					$list[$key]['kmicon'] = tomedia($km['icon']);
					$list[$key]['kcicon'] = tomedia($kcinfo['thumb']);
					$list[$key]['kctype'] = $kcinfo['OldOrNew'];
					$list[$key]['ReNum']  = $kcinfo['ReNum'];
					$list[$key]['end']    = $kcinfo['end'];
					$list[$key]['allow_pl']= $kcinfo['allow_pl'];
					$list[$key]['check']  = $check;
					$list[$key]['kc_type']  = $kcinfo['kc_type'];
					if($kcinfo['kc_type'] == 1){
						if($kcinfo['allow_menu'] == 1){//启用章节
							$menumuber = pdo_fetchall("SELECT id FROM " . GetTableName('kc_menu') . " WHERE schoolid = '{$schoolid}' And kcid = '{$row['kcid']}' ");
							$list[$key]['menumuber'] = count($menumuber);
						}else{
							$list[$key]['menumuber'] = 1;
						}
						$kslist = pdo_fetchall("SELECT id FROM " . GetTableName('kcbiao') . " WHERE schoolid = '{$schoolid}' And kcid = '{$row['kcid']}' ");
						$list[$key]['ksmuber']  = count($kslist);
						$list[$key]['hssnewks'] = false;
						foreach($kslist as $k => $r){
							$hasSign =  pdo_fetch("SELECT id FROM " . GetTableName('kcsign') . " WHERE sid = :sid And ksid=:ksid And status = 2 ", array(':sid' => $it['sid'],':ksid'=> $r['id']));
							if(empty($hasSign)){
								$list[$key]['hssnewks'] = true;
								break;
							}
						}
						$list[$key]['already'] = false;
						$nowtime = time();
						$losttime = $kcinfo['txtime']*60;
						$conditions = " And sk_start < '{$nowtime}' And sk_end > '{$nowtime}'";	
						$check_video = pdo_fetch('SELECT id,sk_start FROM ' . GetTableName('kcbiao') . " WHERE kcid = '{$row['kcid']}' And content_type = 1 $conditions ");
						if($check_video){
							$checksssss = $check_video['sk_start'] - $losttime;
							if($nowtime > $checksssss){
								$list[$key]['already'] = true;
							}
						}
					}else{
						if($kcinfo['OldOrNew'] == 0){
							$nowtime = time();
							$starttime = mktime(0,0,0,date("m"),date("d"),date("Y"));
							$endtime = $starttime + 86399;
							$condition1 = " AND date > '{$starttime}' AND date < '{$endtime}'";	
							$condition2 = " AND createtime > '{$starttime}' AND createtime < '{$endtime}'";
							$today = pdo_fetchcolumn('SELECT sum(costnum) FROM ' . GetTableName('kcbiao') . " WHERE kcid = '{$kcinfo['id']}' AND schoolid = '{$schoolid}' $condition1 ");
							$list[$key]['todays'] = $today;
							if($today){
								$list[$key]['today'] = true;
							}
							$checkks = GetRestKsBySid($row['kcid'],$it['sid']);
							$list[$key]['buyks'] = $checkks['buycourse'];//总计购买课时
							$list[$key]['yqks'] = $checkks['hasSign']; //已签课时
							$list[$key]['restks'] = $checkks['restnumber'] - $restks;//剩余课时
							$istodyqd = pdo_fetch('SELECT id FROM ' . GetTableName('kcsign') . " WHERE kcid = '{$row['kcid']}' AND schoolid = '{$schoolid}' And sid = '{$it['sid']}' And status= 2 $condition2");
							if($istodyqd){
								$list[$key]['todayqd'] = true;
							}
						}
						if($kcinfo['OldOrNew'] == 1){ //查询签到课时和剩余课时
							$starttime = mktime(0,0,0,date("m"),date("d"),date("Y"));
							$endtime = $starttime + 86399;
							$condition2 = " AND createtime > '{$starttime}' AND createtime < '{$endtime}'";	
							$checkks = GetRestKsBySid($row['kcid'],$it['sid']);
							$list[$key]['buyks'] = $checkks['buycourse'];//总计购买课时
							$list[$key]['yqks'] = $checkks['hasSign']; //已签课时
							$list[$key]['restks'] = $checkks['restnumber'] - $restks;//剩余课时
							$istodyqd = pdo_fetch('SELECT id FROM ' . GetTableName('kcsign') . " WHERE kcid = '{$row['kcid']}' AND schoolid = '{$schoolid}' And sid = '{$it['sid']}' And status = 2 $condition2");
							if($istodyqd){
								$list[$key]['todayqd'] = true;
							}
						}
						if($kcinfo['start'] > time()){
							$list[$key]['type'] = 1;//未开始
						}
						if($kcinfo['end'] < time()){
							$list[$key]['type'] = 2;//已结课
						}				
						if($kcinfo['start'] < time() && time() < $kcinfo['end']){
							$list[$key]['type'] = 3;//授课中
						}
					}
				}else{
					unset($list[$key]);
				}
			}	
			// var_dump($list);die;
			if(!empty($list)){
				sort_array_multi($list, ['end'], ['desc']);
			}
			include $this->template(''.$school['style2'].'/myclass');
		}else{
			session_destroy();
		    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
			exit;
		}
?>