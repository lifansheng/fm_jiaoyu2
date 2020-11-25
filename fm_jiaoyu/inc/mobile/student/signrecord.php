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
	 	$userid = $_GPC['userid'];

	 	//查询是否用户登录	
       	
   		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where id = :id AND schoolid=:schoolid  AND tid=:tid  AND weid=:weid", array(':id' => $userid,':schoolid'=> $schoolid,':weid'=> $weid ,':tid'=> 0 ));
   		if(empty($it)){
   			session_destroy();
		    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
			exit;
   		}

		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
		$thistime = trim($_GPC['limit']);
		if($thistime){
			
			$condition = " AND createtime < '{$thistime}'";	
			//var_dump($condition);
			$list1 =  pdo_fetchall("SELECT * FROM " . tablename($this->table_groupsign) . " where weid = '{$weid}' And schoolid = '{$schoolid}' AND sid ='{$it['sid']}' AND type = 1  $condition  ORDER BY createtime DESC LIMIT 0,10 ");

	      		$students1 = pdo_fetch("SELECT s_name,icon,bj_id FROM " . tablename($this->table_students) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $it['sid']));
	      		$bjname1 = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where weid = :weid AND sid = :sid", array(':weid' => $weid, ':sid' => $students1['bj_id']));
	      		foreach( $list1 as $key => $value ){
		      		$userinfo1 = pdo_fetch("SELECT userinfo,pard FROM " . tablename($this->table_user) . " where id = :id AND schoolid=:schoolid  AND tid=:tid  AND weid=:weid", array(':id' => $value['userid'],':schoolid'=> $schoolid,':weid'=> $weid ,':tid'=> 0 ));
		      		$pard = get_guanxi($userinfo1['pard']);
					if(!$pard){
						$pard = '本人';
					}
			    	$usertemp1 = unserialize($userinfo1['userinfo']);
			    	$list1[$key]['username'] = $usertemp1['name'];
			    	$list1[$key]['phone'] = $usertemp1['mobile'];
			    	$list1[$key]['pard'] = $pard;
			    	$list1[$key]['bjname'] = $bjname1['sname'];
			    	$list1[$key]['sname'] = $students1['s_name'];
			    	$list1[$key]['sicon'] = $students1['icon'];
			    	$gainfo = pdo_fetch("SELECT title,starttime,endtime FROM " . tablename($this->table_groupactivity) . " where id = :id AND schoolid=:schoolid  AND weid=:weid", array(':id' => $value['gaid'],':schoolid'=> $schoolid,':weid'=> $weid  ));
			    	$list1[$key]['gatitle'] = $gainfo['title'];
			    	$list1[$key]['gastarttime'] = $gainfo['starttime'];
			    	$list1[$key]['gaendtime'] = $gainfo['endtime'];
				}
      		

			include $this->template('comtool/signrecord');	 
		}else{
				$list =  pdo_fetchall("SELECT * FROM " . tablename($this->table_groupsign) . " where weid = '{$weid}' And schoolid = '{$schoolid}' AND sid ='{$it['sid']}' AND type = 1    ORDER BY createtime DESC LIMIT 0,10 ");
	      		$students = pdo_fetch("SELECT s_name,icon,bj_id FROM " . tablename($this->table_students) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $it['sid']));
	      		$bjname = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where weid = :weid AND sid = :sid", array(':weid' => $weid, ':sid' => $students['bj_id']));
	      		foreach( $list as $key => $value ){
		      		$userinfo = pdo_fetch("SELECT userinfo,pard FROM " . tablename($this->table_user) . " where id = :id AND schoolid=:schoolid  AND tid=:tid  AND weid=:weid", array(':id' => $value['userid'],':schoolid'=> $schoolid,':weid'=> $weid ,':tid'=> 0 ));
		      		$pard = get_guanxi($userinfo['pard']);
					if(!$pard){
						$pard = '本人';
					}
			    	$usertemp = unserialize($userinfo['userinfo']);
			    	$list[$key]['username'] = $usertemp['name'];
			    	$list[$key]['phone'] = $usertemp['mobile'];
			    	$list[$key]['pard'] = $pard;
			    	$list[$key]['bjname'] = $bjname['sname'];
			    	$list[$key]['sname'] = $students['s_name'];
			    	$list[$key]['sicon'] = $students['icon'];
			    	$gainfo = pdo_fetch("SELECT title,starttime,endtime FROM " . tablename($this->table_groupactivity) . " where id = :id AND schoolid=:schoolid  AND weid=:weid", array(':id' => $value['gaid'],':schoolid'=> $schoolid,':weid'=> $weid  ));
			    	$list[$key]['gatitle'] = $gainfo['title'];
			    	$list[$key]['gastarttime'] = $gainfo['starttime'];
			    	$list[$key]['gaendtime'] = $gainfo['endtime'];
			    	
				}
				
			include $this->template(''.$school['style2'].'/signrecord');	
		}				        		
           
?>