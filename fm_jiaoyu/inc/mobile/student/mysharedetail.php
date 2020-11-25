<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
        global $_W, $_GPC;
        $weid = $this->weid;
        $from_user = $this->_fromuser;
		$schoolid = intval($_GPC['schoolid']);
		$userid = $_GPC['id'];
        $kcid = $_GPC['kcid'];
		//var_dump($userid);
		//var_dump($kcid);
	   
        
        //查询是否用户登录		
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $userid));	
		
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
		
	
	    $list = pdo_fetchall("SELECT * FROM " . tablename($this->table_order) . " where weid = '{$weid}' AND schoolid='{$schoolid}'  And type =1 and status = 2 And kcid='{$kcid}' and shareuserid = '{$userid}' ORDER BY paytime DESC " );
		//var_dump($list);
		
		foreach($list as $key=>$value){
			$student = pdo_fetch("SELECT s_name,icon FROM " . tablename($this->table_students) . " where schoolid = :schoolid AND id=:id ", array(':schoolid' => $schoolid, ':id' => $value['sid']));
			$buyuser = pdo_fetch("SELECT userinfo,pard FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $value['userid']));
			$userinfo = unserialize($buyuser['userinfo']);
			$pard = $buyuser['pard'];
			$guanxi = get_guanxi($pard);
			$list[$key]['s_name'] = $student['s_name'];
			$list[$key]['sicon'] = $student['icon']?$student['icon']:$school['spic'];
			$list[$key]['pard'] = $guanxi?$guanxi:'本人';
			$list[$key]['username'] = $userinfo['name'];
		}
	
	    include $this->template(''.$school['style2'].'/mysharedetail');	
			
		if(empty($it)){
			session_destroy();
		    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
			exit;
        }       

 






?>