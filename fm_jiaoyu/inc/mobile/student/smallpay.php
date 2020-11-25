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
		$opration = $_GPC['op'];
		$data = explode ( ',', $_GPC ['str'] );
		$orderid = intval($_GPC['orderid']);
		$s_name = trim($_GPC['s_name']);
		if (empty($_GPC ['str'])){
			$od1 = $orderid;
		}else{
			$od1 = $data[0];
        }
        
		$dos = trim($_GPC['dos']) ? trim($_GPC['dos']) : 'user';

		if($opration == 'mallpay')
		{
			$morder = pdo_fetch("SELECT * FROM " . tablename($this->table_mallorder) . " where  id=:id ", array( ':id' => $_GPC['morderid']));
			$good = pdo_fetch("SELECT * FROM " . tablename($this->table_mall) . " where  id=:id ", array( ':id' => $morder['goodsid']));
			$imgarr = unserialize($good['thumb']);
            $xsxg = intval($good['xsxg']);
            $userinfo = pdo_fetch("SELECT * FROM ".GetTableName('user')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' and tid = '{$morder['sid']}'  and openid = '{$openid}'");
            
			$checkpayd = pdo_fetchall("SELECT count FROM " . tablename($this->table_mallorder) . " where  sid=:sid AND status != :status ", array( ':sid' => $morder['sid'] , ':status' => 1 ));
			$countall = 0 ;
			foreach( $checkpayd as $key => $value )
			{
				$countall = $countall + intval($value['count']);
			}
			
			$countpayd = $countall + intval($morder['count']);
			
			$school = pdo_fetch("SELECT cardset,title,style2,is_chongzhi FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
		if($countpayd > $xsxg & $xsxg != 0  ){
				include $this->template(''.$school['style2'].'/overxg');
				exit();
			}
			
		}
		
		$school = pdo_fetch("SELECT cardset,title,style2,is_chongzhi FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
		
		if ($od1){
		$card = unserialize($school['cardset']);
		
		$kc1 = pdo_fetch("SELECT * FROM " . tablename($this->table_order) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $od1)); 
		$user = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $_SESSION['user'])); 
		$students = pdo_fetch("SELECT points,chongzhi,id FROM " . tablename($this->table_students) . " WHERE id = '{$user['sid']}' And schoolid = '{$schoolid}' ");  
		$flag = 1;
        if ($kc1['status'] ==2)	{
            $flag = 2;
        }
		$uniacid = !empty($kc1['payweid']) ? $kc1['payweid'] : $weid ;
		$setting = uni_setting($uniacid, 'payment');
		if (!empty($setting['payment']['credit']['switch'])) {
			$credtis = mc_credit_fetch($_W['member']['uid']);
		}		
		$temp = array(
		   'weid' => $weid,
           'schoolid' => $schoolid,	   
		   'od1' => $od1,
		   'payweid' => $kc1['payweid'],
		   'cose' => $kc1['cose'],
		   'openid' => empty($s_name) ? $_SESSION['user'] : $s_name,
		   'status'=>1
		);
		pdo_insert($this->table_wxpay, $temp);
		$wxpay_id = pdo_insertid();	
        $params = array(
            'tid' => $wxpay_id,      //充值模块中的订单号，此号码用于业务模块中区分订单，交易的识别码
            'ordersn' => time(),  //收银台中显示的订单号
            'title' => '在线缴费',          //收银台中显示的标题
            'fee' => $kc1['cose'],
			'module' => $this->module['name'],
            //'user' => $_W['member']['uid'],     //付款用户, 付款的用户名(选填项)
        );			
		$log = pdo_get('core_paylog', array('uniacid' => $uniacid, 'module' => $params['module'], 'tid' => $params['tid']));
		if (empty($log)) {
			$log = array(
					'uniacid' => $uniacid,
					'acid' => $uniacid,
					'openid' => $_W['member']['uid'],
					'module' => $this->module['name'], 
					'tid' => $params['tid'],
					'fee' => $params['fee'],
					'card_fee' => $params['fee'],
					'status' => '0',
					'is_usecard' => '0',
			);
			pdo_insert('core_paylog', $log);
		}
		
			include $this->template(''.$school['style2'].'/smallpay');
		}else{
			include $this->template('common/404');
		}
                
?>