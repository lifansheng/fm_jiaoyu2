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
		foreach( $user as $key => $value )
		{
			$userinfotemp =  pdo_fetch("SELECT userinfo FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $value['id']));
			$tempunserinfo = unserialize($userinfotemp['userinfo']);
			$user[$key]['userinfo'] = $tempunserinfo ;
		}
		$groupac = pdo_fetch("SELECT * FROM " . tablename($this->table_groupactivity) . " where :schoolid = schoolid And :weid = weid  And :id = id", array(':weid' => $weid, ':schoolid' => $schoolid,  ':id' => $gaid));
		$bjnamearr = array();
		$bjarray =  explode(',', $groupac['bjarray']);
		foreach( $bjarray as  $value )
		{
			$bjname = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where weid = :weid AND sid=:id ", array(':weid' => $weid, ':id' => $value));
			$bjnamearr[$value] = $bjname['sname'];
			
		}
		$imgarr = unserialize($groupac['banner']);
		
        //查询是否用户登录		
	if($_GPC['op'] == 'signup'){
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $userid));
		$checksign =  pdo_fetch("SELECT id FROM " . tablename($this->table_groupsign) . " where :schoolid = schoolid And  :sid = sid  And  :gaid = gaid  ", array(
			':schoolid' => $schoolid,
			':sid'=>$it['sid'],
			':gaid' => $gaid
		));
	}else{
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND openid=:openid ", array(':weid' => $weid, ':openid' => $openid));
	}
		
		

		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));	
if(!empty($it)){
	include $this->template(''.$school['style2'].'/gadetail');
}else{
	 	session_destroy();
		    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
			exit;
}
		
			
        
        
?>