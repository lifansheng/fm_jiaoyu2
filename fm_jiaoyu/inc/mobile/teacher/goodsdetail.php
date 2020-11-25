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
		$AddressFromSet = $_GPC['default_address'];
		$province = $_GPC['province'];
		$city = $_GPC['city'];
		$county = $_GPC['country'];
		$address = $_GPC['address'];
		$username = $_GPC['name'];
		$phone = $_GPC['phone'];
		$goodsid = $_GPC['goodsid'];

	
		$good = pdo_fetch("SELECT * FROM " . tablename($this->table_mall) . " where :schoolid = schoolid And :weid = weid  And :id = id", array(':weid' => $weid, ':schoolid' => $schoolid,  ':id' => $goodsid));
		$type = iunserializer($good['type']);
		$imgarr = unserialize($good['thumb']);
		$NumofSold = pdo_fetchcolumn("SELECT COUNT(1) FROM ".tablename($this->table_mallorder)." where weid = :weid And schoolid = :schoolid And goodsid = :goodsid",array(':weid'=>$weid, ':schoolid'=>$schoolid, ':goodsid'=>$goodsid ));
   		$good['tqty']  = intval($good['qty']) - $NumofSold;
		$good['tsold']  = intval($good['sold']) + $NumofSold;
        //查询是否用户登录		
		$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ORDER BY id DESC", array(':weid' => $weid, ':id' => $userid['id']));
		$data;
		//查询是否有收货信息
		$MyAddress = pdo_fetch("SELECT * FROM " . tablename($this->table_address) . " where  :weid = weid And :openid = openid ", array(':weid' => $weid,  ':openid' => $openid));
		//修改地址
		if(!empty($MyAddress))
		{
			//var_dump($MyAddress);
			//die();
			$AddressToShow = $MyAddress['province'].$MyAddress['city'].$MyAddress['county'].$MyAddress['address'];
			$data= array(
			'name'     => $username,
			'phone'    => $phone,
			'province' => $province,
			'city'     => $city,
			'county'   => $county,
			'address'  => $address
		);
		}else{
			$data= array(
			'weid'     => $weid,
			'openid'   => $openid,
			'name'     => $username,
			'phone'    => $phone,
			'province' => $province,
			'city'     => $city,
			'county'   => $county,
			'address'  => $address
		);
		}

		if(!empty($AddressFromSet))
				{
					if(!empty($MyAddress)){
						pdo_update($this->table_address,$data, array('openid' =>  $openid));
					}else{
						pdo_insert($this->table_address,$data);
					}
				};
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));	
		$Teacher = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $it['tid']));
        if(!empty($userid['id'])){
	        	$MyAddress2 = pdo_fetch("SELECT * FROM " . tablename($this->table_address) . " where  :weid = weid And :openid = openid ", array(':weid' => $weid,  ':openid' => $openid));
			if(!empty($good))
			{
				include $this->template(''.$school['style3'].'/goodsdetail');
			}else{
				include $this->template(''.$school['style3'].'/nogoods');
			}
        }else{
			session_destroy();
            $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
        } 
        
?>