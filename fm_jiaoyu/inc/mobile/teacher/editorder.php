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
		$userid1 = $_GPC['id'];
		$op = $_GPC['op'];
		$good = pdo_fetch("SELECT * FROM " . tablename($this->table_mall) . " where :schoolid = schoolid And :weid = weid  And :id = id", array(':weid' => $weid, ':schoolid' => $schoolid,  ':id' => $goodsid));
		$NumofSold = pdo_fetchcolumn("SELECT COUNT(1) FROM ".tablename($this->table_mallorder)." where weid = :weid And schoolid = :schoolid And goodsid = :goodsid",array(':weid'=>$weid, ':schoolid'=>$schoolid, ':goodsid'=>$goodsid ));
   		$good['tqty']  = intval($good['qty']) - $NumofSold;
		$good['tsold']  = intval($good['sold']) + $NumofSold;	
		$morder = pdo_fetch("SELECT * FROM " . tablename($this->table_mallorder) . " where :schoolid = schoolid And :weid = weid  And :id = id", array(':weid' => $weid, ':schoolid' => $schoolid,  ':id' => $morderid));	
		$type = iunserializer($good['type']);
		$MyAddress = pdo_fetch("SELECT * FROM " . tablename($this->table_address) . " where :schoolid = schoolid And :weid = weid And :openid = openid ", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid));
		$AddressToShow = $MyAddress['province'].$MyAddress['city'].$MyAddress['county'].$MyAddress['address'];
		
        //查询是否用户登录		
		$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ORDER BY id DESC", array(':weid' => $weid, ':id' => $userid['id']));
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));	
		$Teacher = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $it['tid']));
		$tid = $Teacher['id'];
		
        if(!empty($userid['id'])){
		
			include $this->template(''.$school['style3'].'/editorder');
        }else{
			session_destroy();
            $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
        } 
        
?>