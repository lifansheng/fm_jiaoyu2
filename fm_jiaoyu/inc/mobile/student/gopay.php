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
		$data = explode ( ',', $_GPC ['str'] );
		$orderid = intval($_GPC['orderid']);
		$s_name = trim($_GPC['s_name']);
		$dos = trim($_GPC['dos']) ? trim($_GPC['dos']) : 'user';
		mload()->model('pay');
		if (empty($_GPC ['str'])){
			$od1 = $orderid;
		}else{
			$od1 = $data[0];
		}
		$school = pdo_fetch("SELECT cardset,title,style2,Is_point,logo,is_chongzhi,chongzhiweid FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
		$enough = true;
		if ($od1){





                $order = pdo_fetch("SELECT * FROM " . tablename($this->table_order) . " where id = :id ", array(':id' => $od1));
                $teaid = pdo_fetch("SELECT * FROM " . tablename($this->table_mallorder) . " where id = :id ", array(':id' => $order['morderid']));
               if($order['type'] == 4){
                   $url = $_W['siteroot'] . 'app/index.php?i=' . $weid. '&c=entry&schoolid=' . $schoolid. '&id=' . $order['signid'] . '&do=signupjc&m=fm_jiaoyu';
               }else if($order['type'] == 5){
                   $url = $_W['siteroot'] . 'app/index.php?i=' . $weid. '&c=entry&schoolid=' . $schoolid . '&do=user&m=fm_jiaoyu';
               }else if($order['type'] == 7){
                   $url = $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid. '&do=user&m=fm_jiaoyu';
               }else if($order['type'] == 6){
                   if(!empty($teaid['tid']) && empty($teaid['sid'])){
                       $url = $_W['siteroot'] . 'app/index.php?i=' . $weid. '&c=entry&schoolid=' . $schoolid. '&do=getorder&m=fm_jiaoyu';
                   }else{
                       $url = $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=sgetorder&userid='.$teaid['userid'].'&op=yes_g&m=fm_jiaoyu';
                   }
               }
               else{
                   $url = $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid. '&do=user&m=fm_jiaoyu';
               }
               $JumpUrl = $url;


			$card = unserialize($school['cardset']);
			$kc1 = pdo_fetch("SELECT * FROM " . tablename($this->table_order) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $od1)); 
			$user = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $_SESSION['user'])); 
			$students = pdo_fetch("SELECT points,chongzhi,id FROM " . tablename($this->table_students) . " WHERE id = '{$user['sid']}' And schoolid = '{$schoolid}' ");
			//var_dump($kc1['payweid']);
			if($school['Is_point']==1 && $kc1['type'] == 1 && $kc1['spoint'] !=0){
				$dy = true;
				
				if($kc1['spoint'] >$students['points'] ){
					$enough = false;
				}elseif($kc1['spoint'] <=$students['points'] ){
					$enough = true;
				}
			}
			$yhcose = 0;
			if($kc1['couponlogid']){
				$coupon = getMyWelfare($kc1['couponlogid'],1);
				$yhcose += $coupon['cose'];
			}
			if($kc1['redpacketlogid']){
				$redpacket = getMyWelfare($kc1['redpacketlogid'],1);
				$yhcose += $redpacket['cose'];
			}
			$cose = round(max(0,$kc1['cose'] - $yhcose),2);
			$flag = 1;
			if ($kc1['status'] ==2)	{
				$flag = 2;
			}
			if ($kc1['type'] ==8 ){
				$taocan =  pdo_fetch("SELECT * FROM " . tablename($this->table_chongzhi) . " WHERE schoolid ='{$kc1['schoolid']} And weid = {$kc1['weid']}'And id = {$kc1['taocanid']}");
			}
			if($kc1['type'] ==7){
				$vod = pdo_fetch("SELECT * FROM " . tablename($this->table_allcamera) . " WHERE id = '{$kc1['vodid']}' And schoolid = '{$schoolid}' ");
			}

			if($kc1['type'] ==20){
				$file = pdo_fetch("SELECT * FROM " . GetTableName('growupfile') . " WHERE id = '{$kc1['extra_info']}' And schoolid = '{$schoolid}' ");
			}
			// var_dump($file);die;
			$kecheng = pdo_fetchall("SELECT * FROM " . tablename($this->table_tcourse) . " WHERE :schoolid = schoolid And :weid = weid", array(':weid' => $_W['uniacid'], ':schoolid' => $schoolid), 'id');
			$teacher = pdo_fetchall("SELECT * FROM " . tablename($this->table_teachers) . " WHERE :schoolid = schoolid And :weid = weid", array(':weid' => $_W['uniacid'], ':schoolid' => $schoolid), 'id');
			$cost = pdo_fetchall("SELECT * FROM " . tablename($this->table_cost) . " WHERE :schoolid = schoolid And :weid = weid", array(':weid' => $_W['uniacid'], ':schoolid' => $schoolid), 'id');
			$uniacid = !empty($kc1['payweid']) ? $kc1['payweid'] : $weid ;
			//$setting = uni_setting($uniacid, 'payment');
			$setting = uni_setting_load('payment',  $uniacid);
			if (!empty($setting['payment']['credit']['switch'])) {
				$credtis = mc_credit_fetch($_W['member']['uid']);
			}
			$temp = array(
			   'weid' => $weid,
			   'schoolid' => $schoolid,	   
			   'od1' => $od1,
			   'payweid' => $kc1['payweid'],
			//    'cose' => $kc1['cose'],
			   'cose' => $cose,
			   'openid' => empty($s_name) ? $_SESSION['user'] : $s_name,
			   'status'=>1
			);
			pdo_insert($this->table_wxpay, $temp);
			$wxpay_id = pdo_insertid();	
			$params = array(
				'tid' => $wxpay_id,      //充值模块中的订单号，此号码用于业务模块中区分订单，交易的识别码
				'ordersn' => time(),  //收银台中显示的订单号
				'title' => '在线缴费',          //收银台中显示的标题
				'fee' => $cose,
				'module' => 'fm_jiaoyu',
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
			pdo_update($this->table_order, array('wxpayid' => $wxpay_id,'userid' => $_SESSION['user']), array('id' => $od1));
			// include $this->template($school['style2'].'/gopay');
			include $this->template($school['style2'].'/newgopay');
		}else{
			include $this->template('common/404');
		}
                
?>