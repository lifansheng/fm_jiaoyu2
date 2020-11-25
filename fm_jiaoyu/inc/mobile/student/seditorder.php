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
		$goodsid =  intval($_GPC['goodsid']);
		$morderid = intval($_GPC['morderid']);
		$userid = $_GPC['id'];
		$op = $_GPC['op'];
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ORDER BY id DESC", array(':weid' => $weid, ':id' => $userid));
		$good = pdo_fetch("SELECT * FROM " . tablename($this->table_mall) . " where :schoolid = schoolid And :weid = weid  And :id = id", array(':weid' => $weid, ':schoolid' => $schoolid,  ':id' => $goodsid));
		$xsxg = intval($good['xsxg']);
		$checkpayd = pdo_fetchall("SELECT count FROM " . tablename($this->table_mallorder) . " where  sid=:sid AND status != :status ", array( ':sid' => $it['sid'] , ':status' => 1 ));
		//var_dump($xsxg);
		$countall = 0 ;
			foreach( $checkpayd as $key => $value )
			{
				$countall = $countall + intval($value['count']);
			}

		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
		if($countall >= $xsxg  && $xsxg != 0 ){
			include $this->template(''.$school['style2'].'/overxg');
			exit();
		}
		
		$NumofSold = pdo_fetchcolumn("SELECT COUNT(1) FROM ".tablename($this->table_mallorder)." where weid = :weid And schoolid = :schoolid And goodsid = :goodsid",array(':weid'=>$weid, ':schoolid'=>$schoolid, ':goodsid'=>$goodsid ));
   		$good['tqty']  = intval($good['qty']) - $NumofSold;
		$good['tsold']  = intval($good['sold']) + $NumofSold;	
		$morder = pdo_fetch("SELECT * FROM " . tablename($this->table_mallorder) . " where :schoolid = schoolid And :weid = weid  And :id = id", array(':weid' => $weid, ':schoolid' => $schoolid,  ':id' => $morderid));	
		$type = iunserializer($good['type']);
		$MyAddress = pdo_fetch("SELECT * FROM " . tablename($this->table_address) . " where :schoolid = schoolid And :weid = weid And :openid = openid ", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid));
		$AddressToShow = $MyAddress['province'].$MyAddress['city'].$MyAddress['county'].$MyAddress['address'];
		
        //查询是否用户登录		
		
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ORDER BY id DESC", array(':weid' => $weid, ':id' => $userid));
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));	
		
		
        if(!empty($userid)){
		
			include $this->template(''.$school['style2'].'/seditorder');
        }else{
			session_destroy();
            $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
        } 
        
?>