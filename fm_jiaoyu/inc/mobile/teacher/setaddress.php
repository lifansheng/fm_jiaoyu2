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
		$leaveid = $_GPC['leaveid'];
		$addressid = $_GPC['addressid'];
		

		//$MyAddress = pdo_fetch("SELECT * FROM " . tablename($this->table_address) . " where :schoolid = schoolid And :weid = weid And :openid = openid ", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid));
		$MyAddress = pdo_fetch("SELECT * FROM " . tablename($this->table_address) . " where :schoolid = schoolid And :weid = weid And id = :id ", array(':weid' => $weid, ':schoolid' => $schoolid, ':id' => $addressid));

        //查询是否用户登录		
		$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ORDER BY id DESC", array(':weid' => $weid, ':id' => $userid['id']));
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));	
		$Teacher = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $it['tid']));
		$AddressFromTable = $Teacher['address'];
        if(!empty($userid['id'])){
		
			include $this->template(''.$school['style3'].'/setaddress');
        }else{
			session_destroy();
            $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
        } 
        
?>