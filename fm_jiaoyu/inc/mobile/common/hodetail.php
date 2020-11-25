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
		$gaid = $_GPC['gaid'];
		$userid = $_GPC['userid'];
		$user =  get_myallclass($weid,$openid);

		if (!empty($userid)){
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id AND tid=:tid ", array(':weid' => $weid, ':id' => $userid ,':tid'=> 0 ));
	}else{
			$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND openid=:openid ", array(':weid' => $weid, ':openid' => $openid));
	}

	//查询是否用户登录
	if(empty($it)){
	 	session_destroy();
		    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
			exit;
	}

	
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));	
		foreach( $user as $key => $value )
		{
			$userinfotemp =  pdo_fetch("SELECT userinfo FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $value['id']));
			$tempunserinfo = unserialize($userinfotemp['userinfo']);
			$user[$key]['userinfo'] = $tempunserinfo ;
		}
		
		$groupac = pdo_fetch("SELECT * FROM " . tablename($this->table_groupactivity) . " where :schoolid = schoolid And :weid = weid  And :id = id", array(':weid' => $weid, ':schoolid' => $schoolid,  ':id' => $gaid));
		if($_GPC['op'] == 'signup'){
			
			$checksign = pdo_fetch("SELECT createtime,servetime,userid,id FROM " . tablename($this->table_groupsign) . " where :schoolid = schoolid And :weid = weid and sid=:sid And :gaid = gaid AND type != :type", array(':weid' => $weid, ':schoolid' => $schoolid, ':sid'=>$it['sid'], ':gaid' => $gaid,':type' => 1 ));
			$userinfo = pdo_fetch("SELECT userinfo,pard FROM " . tablename($this->table_user) . " where id = :id AND schoolid=:schoolid  AND tid=:tid  AND weid=:weid", array(':id' => $checksign['userid'],':schoolid'=> $schoolid,':weid'=> $weid ,':tid'=> 0 ));
			$pard = get_guanxi($userinfo['pard']);
					if(!$pard){
						$pard = '本人';
					}
			$checksign['pard'] = $pard;		
			$usertemp = unserialize($userinfo['userinfo']);
			$checksign['username'] = $usertemp['name'];
			
		}
		
        //查询是否用户登录		
	
		
		


	

		include $this->template(''.$school['style1'].'/hodetail');
			
        
        
?>