<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */global $_W, $_GPC;
   $operation = in_array ( $_GPC ['op'], array ('default','reciveConfirm', 'delmallorder', 'mallorder','sigeup','deleteclass','creatorder','xuefeiidcard','xufeiob','buyvod','xgks','chongzhi','buy_charge','make_kcorder','del_ordertid','get_payinfo_byorderid','CheckBuyManual','CheckWelfare','yuepayRemaster','check_orderpay') ) ? $_GPC ['op'] : 'default';

     if ($operation == 'default') {
		die ( json_encode ( array ( 'result' => false, 'msg' => '参数错误' ) ) );
	}
	if ($operation == 'sigeup') {
		$data = explode ( '|', $_GPC ['json'] );
		if (! $_GPC ['schoolid'] || !$_GPC ['weid']) {
			die ( json_encode ( array ( 'result' => false, 'msg' => '非法请求！' ) ) );
	    }
		$shareuserid = $_GPC['shareuserid'];
        $setting = pdo_fetch("SELECT * FROM " . tablename($this->table_set) . " WHERE :weid = weid", array(':weid' => $_GPC['weid']));
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " WHERE :id = id", array(':id' => $_GPC['schoolid']));
		$user = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " WHERE :weid = weid And :schoolid = schoolid And :id = id", array(':weid' => $_GPC['weid'], ':schoolid' => $_GPC['schoolid'], ':id' => $_GPC['user']));
		$student = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " WHERE :weid = weid And :schoolid = schoolid And :id = id", array(':weid' => $_GPC['weid'], ':schoolid' => $_GPC['schoolid'], ':id' => $_GPC['sid']));
		$cose = pdo_fetch("SELECT * FROM " . tablename($this->table_tcourse) . " WHERE :id = id", array(':id' => $_GPC['kcid']));
		$issale = pdo_fetch("SELECT * FROM " . tablename($this->table_order) . " WHERE :weid = weid And :schoolid = schoolid And :kcid = kcid And :sid = sid", array(':weid' => $_GPC['weid'], ':schoolid' => $_GPC['schoolid'], ':kcid' => $_GPC['kcid'], ':sid' => $_GPC['sid']));
		$yb = pdo_fetchcolumn("select count(*) FROM ".tablename($this->table_order)." WHERE kcid = '".$cose['id']."' And status = 2 and type = 1 and schoolid = '{$_GPC['schoolid']}' and weid = '{$_GPC['weid']}' ");
		$rest = $cose['minge'] - $yb;





		if (empty($user['realname']) || empty($user['mobile'])) {
			die ( json_encode ( array ( 'result' => false, 'msg' => '请前往个人中心完善您的联系方式' ) ) );
		}
		if ($rest < 1){
            die ( json_encode ( array ( 'result' => false, 'msg' => '本课程已满' ) ) );
		}
		if (!empty($issale)) {
            die ( json_encode ( array ( 'result' => false, 'msg' => '抱歉,您已报名本课程,请查看订单' ) ) );
		}
		if (time() >= $cose['end']) {
            die ( json_encode ( array ( 'result' => false, 'msg' => '本课程已经结束' ) ) );
		}
		if (empty($_GPC['openid'])) {
            die ( json_encode ( array ( 'result' => false, 'msg' => '非法请求' ) ) );
		}else{
			$schoolid = $_GPC['schoolid'];
			$weid     = $_GPC['weid'];
			$sid      = $_GPC['sid'];
			$userid   = $_GPC['uid'];
			$orderid  = "{$userid}{$sid}";
			$temp = array(
				'weid'       => $_GPC ['weid'],
				'schoolid'   => $_GPC ['schoolid'],
				'sid'        => $_GPC ['sid'],
				'userid'     => $_GPC ['user'],
				'type'       => 1,
				'status'     => 1,
				'kcid'       => $_GPC ['kcid'],
				'uid'        => $_GPC['uid'],
				'cose'       => $cose['cose'],
				'payweid'    => $cose['payweid'],
				'orderid'    => $orderid,
				'createtime' => time(),
			);
			$temp['ksnum'] = $cose['FirstNum'];
			if(!empty($shareuserid)){
				$ShareUserInfo = pdo_fetch("SELECT sid FROM " . tablename($this->table_user) . " WHERE :weid = weid And :schoolid = schoolid And :id = id", array(':weid' => $_GPC['weid'], ':schoolid' => $_GPC['schoolid'], ':id' => $shareuserid));
				if($ShareUserInfo['sid'] != $_GPC['sid']){
					$temp['shareuserid'] = $shareuserid;
				}
			}
			pdo_insert($this->table_order, $temp);
			$order_id = pdo_insertid();
			$data ['result'] = true;
			$data ['msg'] = '报名成功,请前往个人中心查看';
		 	die ( json_encode ( $data ) );
		}
    }

	if ($operation == 'deleteclass') {
		$data = explode ( '|', $_GPC ['json'] );
		if (! $_GPC ['schoolid'] || ! $_GPC ['weid']) {
               die ( json_encode ( array (
                    'result' => false,
                    'msg' => '非法请求！'
		               ) ) );
	         }

		if (empty($_GPC['openid'])) {
                  die ( json_encode ( array (
                    'result' => false,
                    'msg' => '非法请求！'
		               ) ) );
		}else{

			pdo_delete($this->table_order, array('id' => $_GPC['kcid']));

			$data ['result'] = true;

			$data ['msg'] = '删除成功！';

          die ( json_encode ( $data ) );

		}
    }
	if ($operation == 'creatorder') {
		$data = explode ( '|', $_GPC ['json'] );

		$od1 = $_GPC ['od1'];
		$od2 = $_GPC ['od2'];
		$od3 = $_GPC ['od3'];
		$od4 = $_GPC ['od4'];
		$od5 = $_GPC ['od5'];
		$kc1 = pdo_fetch("SELECT * FROM " . tablename($this->table_order) . " where weid = :weid AND schoolid=:schoolid AND id=:id ", array(':weid' => $_GPC ['weid'], ':schoolid' => $_GPC ['schoolid'], ':id' => $od1));
        $kc2 = pdo_fetch("SELECT * FROM " . tablename($this->table_order) . " where weid = :weid AND schoolid=:schoolid AND id=:id ", array(':weid' => $_GPC ['weid'], ':schoolid' => $_GPC ['schoolid'], ':id' => $od2));
		$kc3 = pdo_fetch("SELECT * FROM " . tablename($this->table_order) . " where weid = :weid AND schoolid=:schoolid AND id=:id ", array(':weid' => $_GPC ['weid'], ':schoolid' => $_GPC ['schoolid'], ':id' => $od3));
		$kc4 = pdo_fetch("SELECT * FROM " . tablename($this->table_order) . " where weid = :weid AND schoolid=:schoolid AND id=:id ", array(':weid' => $_GPC ['weid'], ':schoolid' => $_GPC ['schoolid'], ':id' => $od4));
		$kc5 = pdo_fetch("SELECT * FROM " . tablename($this->table_order) . " where weid = :weid AND schoolid=:schoolid AND id=:id ", array(':weid' => $_GPC ['weid'], ':schoolid' => $_GPC ['schoolid'], ':id' => $od5));
        $cose = $kc1['cose'] + $kc2['cose'] + $kc3['cose'] + $kc4['cose'] + $kc5['cose'];

		$temp = array(
		   'weid' => $_GPC ['weid'],
           'schoolid' => $_GPC ['schoolid'],
		   'od1' => $od1,
		   'od2' => $od2,
		   'od3' => $od3,
		   'od4' => $od4,
		   'od5' => $od5,
		   'payweid' => $kc1['payweid'],
		   'cose' => $cose,
		   'status'=>1
		);

		pdo_insert($this->table_wxpay, $temp);

		$wxpay_id = pdo_insertid();

		$data ['result'] = true;
		$url = $this->createMobileUrl('pay', array('schoolid' => $_GPC['schoolid'], 'cose' => $cose, 'wxpay' => $wxpay_id));
		$data ['msg'] = $url;

        die ( json_encode ( $data ) );
    }

	if ($operation == 'xuefeiidcard')  {
		$item = pdo_fetch("SELECT * FROM " . tablename($this->table_idcard) . " WHERE :id = id", array(':id' => $_GPC['id']));
		if (empty($item)) {
               die ( json_encode ( array (
                    'result' => false,
                    'msg' => '无此卡！'
		               ) ) );
	    }else{
			$checkold = $order = pdo_fetch("SELECT * FROM " . tablename($this->table_order) . " WHERE bdcardid = '{$_GPC['id']}' And schoolid = '{$_GPC['schoolid']}' And sid = '{$_GPC['sid']}' And status = 1 And type = 5 ");
			if(!$checkold){
				$school = pdo_fetch("SELECT cardset FROM " . tablename($this->table_index) . " WHERE id = :id ", array(':id' => $_GPC['schoolid']));
				$card = unserialize($school['cardset']);
				$temp1 = array(
								'weid' =>  $_GPC['weid'],
								'schoolid' => $_GPC['schoolid'],
								'type' => 5,
								'status' => 1,
								'uid' => $_GPC['uid'],
								'userid' => $_GPC['userid'],
								'sid' => $_GPC['sid'],
								'cose' => $card['cardcost'],
								'payweid' => $card['payweid'],
								'orderid' => time(),
								'bdcardid' => $_GPC['id'],
								'createtime' => time(),
							);
				pdo_insert($this->table_order, $temp1);
				$order_id = pdo_insertid();
				$url = $this->createMobileUrl('gopay', array('schoolid' => $_GPC['schoolid'], 'orderid' => $order_id));

				$data ['result'] = true;
				$data ['msg'] = $url;
			}else{
				$data ['result'] = false;
				$data ['msg'] = "抱歉,本卡续费订单您已创建,请前往订单中心完成支付";
			}
          die ( json_encode ( $data ) );

		}
    }
	if ($operation == 'xufeiob')  {
		$item = pdo_fetch("SELECT * FROM " . tablename($this->table_order) . " WHERE :lastorderid = lastorderid ", array(':lastorderid' => $_GPC['id']));
		if ($item['status'] == 1){
               die ( json_encode ( array (
                    'result' => false,
                    'msg' => '本项目您尚有未续费订单，请点击待缴费菜单支付！'
		               ) ) );
		}
		if (empty($_GPC['openid'])) {
               die ( json_encode ( array (
                    'result' => false,
                    'msg' => '您无权操作！'
		               ) ) );
	    }else{
			$order = pdo_fetch("SELECT * FROM " . tablename($this->table_order) . " WHERE :id = id ", array(':id' => $_GPC['id']));
			$orderid = "{$order['uid']}{$order['sid']}";
			$date = array(
				'weid' =>  $_GPC['weid'],
				'schoolid' => $_GPC['schoolid'],
				'sid' => $order['sid'],
				'userid' => $order['userid'],
				'type' => 3,
				'status' => 1,
				'obid' => $order['obid'],
				'costid' => $order['costid'],
				'uid' => $order['uid'],
				'cose' => $order['cose'],
				'orderid' => $orderid,
				'lastorderid' => $_GPC['id'],
				'payweid' => $order['payweid'],
				'createtime' => time(),
			);
			pdo_update($this->table_order, array('xufeitype' => 1), array('id' => $order['id']));
			pdo_insert($this->table_order, $date);
			$order_id = pdo_insertid();
			$url = $this->createMobileUrl('gopay', array('schoolid' => $_GPC['schoolid'], 'orderid' => $order_id));

			$data ['result'] = true;
			$data ['msg'] = $url;

          die ( json_encode ( $data ) );

		}
    }
   if ($operation == 'mallorder')  {
		$weid      = $_GPC['weid'];
		$schoolid  = $_GPC['schoolid'];
		$GoodId    = $_GPC['GoodId'];
		$GoodPoint = intval($_GPC['GoodPoint']);
		$GoodPrice = $_GPC['GoodPrice'];
		$AddId     = $_GPC['AddId'];
		$userid    = $_GPC['userid'];
		$NumOfGood = intval($_GPC['NumOfGood']);
		$beizhu    = trim( $_GPC['beizhu']);
		$allPrice  = $GoodPrice * $NumOfGood;
		$allpoint  = $GoodPoint * $NumOfGood;
		$qhtype    = $_GPC['qhtype'];
		$tid       = $_GPC['tid'];
		if(!empty($_GPC['sid'])){
			$sid   = $_GPC['sid'];
		}else{
			$sid   = 0 ;
		}
		$mallinfo = pdo_fetch("SELECT mallsetinfo,Is_point FROM " . tablename($this->table_index) . " WHERE :schoolid = id AND weid=:weid ", array(':schoolid' => $schoolid,':weid'=>$weid ));

		$qty = pdo_fetch("SELECT qty,cop,points FROM " . tablename($this->table_mall) . " WHERE :id = id ", array(':id' => $GoodId));


		$TTaddress = pdo_fetch("SELECT * FROM " . tablename($this->table_address) . " WHERE :id = id ", array(':id' => $AddId));
		$tadd = $TTaddress['province'].$TTaddress['city'].$TTaddress['county'].$TTaddress['address'];
		if($qhtype == 1){
			$tadd = $TTaddress['province'].$TTaddress['city'].$TTaddress['county'].$TTaddress['address'];
		}elseif($qhtype == 2){
			$tadd = "到校自取";
		}

if ($qty['qty'] < $NumOfGood ) {

			$data ['result'] = true;
			$data ['info'] = "下单失败,商品库存不足";
		 die ( json_encode ( $data ) );
	    }else{
		$temp = array(
		'weid'      => $weid,
		'schoolid'  => $schoolid,
		'tid'       => $tid,
		'sid'       => $sid,
		'goodsid'   => $GoodId,
		'allcash'   => $allPrice,
		'allpoint'  => $allpoint,
		'count'     => $NumOfGood,
		'cop'       => $qty['cop'],
		'addressid' => $AddId,
		'tname'     => $TTaddress['name'],
		'tphone'    => $TTaddress['phone'],
		'taddress'  => $tadd,
		'beizhu'    => $beizhu,
		'createtime' => time(),
		'status'    => 1
		);
		if(!empty($sid))
		{
			$temp['userid'] = $userid;
			if($mallinfo['Is_point'] != 1){
				$temp['allpoint'] = 0;
			}

		}
		$uid = $_GPC['uid'];

		$mallinfoDE = iunserializer($mallinfo['mallsetinfo']);

		$payweid = $mallinfoDE['payweid'];
		pdo_insert($this->table_mallorder,$temp);
		$morderid = pdo_insertid();
		//var_dump($temp);

		$orderTemp = array(
		'weid' => $weid,
		'schoolid' => $schoolid,
		'uid'  => $uid,
		'userid' => $userid,
		'tid'       => $tid,
		'sid'       => $sid,
		'cose' => $allPrice,
		'status' => 1,
		'type' => 6,
		'createtime' => time(),
		'morderid' => $morderid,
		'payweid' => $payweid
		);
		pdo_insert($this->table_order,$orderTemp);
		$Torderid = pdo_insertid();
		pdo_update($this->table_mallorder, array('torderid' => $Torderid), array('id' =>$morderid));
		$data ['result'] = true;
		$data ['info'] = "创建订单成功";
		 die ( json_encode ( $data ) );

	  }
   }
     if ($operation == 'delmallorder'){
	     $morderid = $_GPC['morderid'];
	     $orderid = $_GPC['orderid'];
	     $mtemp = pdo_fetch("SELECT * FROM " . tablename($this->table_mallorder) . " WHERE id=:id ", array(':id'=>$morderid ));
	     $otemp = pdo_fetch("SELECT * FROM " . tablename($this->table_order) . " WHERE id=:id ", array(':id'=>$orderid ));
	     if(!empty($mtemp) && !empty($otemp) )
	     {
			pdo_delete($this->table_mallorder,array('id'=>$morderid));
	     	pdo_delete($this->table_order,array('id'=>$orderid));
		    $data ['result'] = true;
			$data ['info'] = "删除订单成功";
		 	die ( json_encode ( $data ) );
	     }else{

	      	$data ['result'] = false;
			$data ['info'] = "未知原因，删除订单失败";
		 	die ( json_encode ( $data ) );
	     }

     }

     if ($operation == 'reciveConfirm'){
	     $morderid = $_GPC['morderid'];
	     $mtemp = pdo_fetch("SELECT * FROM " . tablename($this->table_mallorder) . " WHERE id=:id ", array(':id'=>$morderid ));
	     if(!empty($mtemp) )
	     {
     		pdo_update($this->table_mallorder, array('status' => 4), array('id' => $morderid));
		    $data ['result'] = true;
			$data ['info'] = "确认收货成功";
		 	die ( json_encode ( $data ) );
	     }else{

	      	$data ['result'] = false;
			$data ['info'] = "未知原因，确认收货失败";
		 	die ( json_encode ( $data ) );
	     }

     }

     if ($operation == 'buyvod'){
		if (! $_GPC ['schoolid'] || ! $_GPC ['weid']) {
               die ( json_encode ( array (
                    'result' => false,
                    'msg' => '非法请求！'
		               ) ) );
	    }else{
			$order = pdo_fetch("SELECT * FROM " . tablename($this->table_order) . " WHERE vodid = '{$_GPC['videoid']}' And schoolid = '{$_GPC['schoolid']}' And userid = '{$_GPC['userid']}' And sid = '{$_GPC['sid']}' And status = 1");
			$orderid = "{$_GPC['uid']}{$_GPC['sid']}";
			if(empty($order)){
				if($_GPC['type'] == 'price_one' || $_GPC['type'] == 'price_one_cun'){
					$vodetype = 'one';
				}
				if($_GPC['type'] == 'price_all' || $_GPC['type'] == 'price_all_cun'){
					$vodetype = 'all';
				}
				$vod = pdo_fetch("SELECT * FROM " . tablename($this->table_allcamera) . " WHERE id = '{$_GPC['videoid']}' And schoolid = '{$_GPC['schoolid']}' ");
				$temp = array(
					'weid' =>  $_GPC['weid'],
					'schoolid' => $_GPC['schoolid'],
					'sid' => $_GPC['sid'],
					'userid' => $_GPC['userid'],
					'type' => 7,
					'status' => 1,
					'uid' => $_GPC['uid'],
					'vodid' => $_GPC['videoid'],
					'cose' => trim($vod[$_GPC['type']]),
					'orderid' => $orderid,
					'payweid' => $vod['payweid'],
					'vodtype' => $vodetype,
					'createtime' => time()
				);
				pdo_insert($this->table_order, $temp);
				$order_id = pdo_insertid();
				$data ['result'] = true;
				$data ['msg'] = $temp['cose'];
				$data ['url'] = $this->createMobileUrl('gopay', array('schoolid' => $_GPC['schoolid'], 'orderid' => $order_id));
			}else{
				$data ['result'] = false;
				$data ['msg'] = "抱歉,您尚有未完成的订单,请前往缴费";
				$data ['url'] = $this->createMobileUrl('order', array('schoolid' => $_GPC['schoolid']));
			}
          die ( json_encode ( $data ) );
		}
     }


      if ($operation == 'xgks'){ //课程续费
		if (! $_GPC ['schoolid'] || ! $_GPC ['weid']) {
               die ( json_encode ( array (
                    'result' => false,
                    'msg' => '非法请求1！'
		               ) ) );
	         }

		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " WHERE :id = id", array(':id' => $_GPC['schoolid']));
		$user = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " WHERE :weid = weid And :schoolid = schoolid And :id = id", array(':weid' => $_GPC['weid'], ':schoolid' => $_GPC['schoolid'], ':id' => $_GPC['user']));
		$student = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " WHERE :weid = weid And :schoolid = schoolid And :id = id", array(':weid' => $_GPC['weid'], ':schoolid' => $_GPC['schoolid'], ':id' => $_GPC['sid']));
		$cose = pdo_fetch("SELECT * FROM " . tablename($this->table_tcourse) . " WHERE :id = id", array(':id' => $_GPC['kcid']));
		$order = pdo_fetch("SELECT id FROM " . GetTableName('order') . " WHERE :kcid = kcid AND sid = :sid AND status = 2", array(':kcid' => $_GPC['kcid'],':sid'=>$_GPC['sid']));
		if (time() >= $cose['end']) {
            die ( json_encode ( array (
                    'result' => false,
                    'msg' => '本课程已经结束'
		               ) ) );
		}
		if (empty($_GPC['openid'])) {
            die ( json_encode ( array (
                    'result' => false,
                    'msg' => '非法请求2'
		               ) ) );
		}else{

			$schoolid = $_GPC['schoolid'];
			$weid = $_GPC['weid'];
			$sid = $_GPC['sid'];
			$userid = $_GPC['userid'];
			$orderid = "{$userid}{$sid}";
			$ksxgnum = $_GPC['ksxgnum'];
			$reprice = $_GPC['reprice'];
			$couponlogid = $_GPC['couponlogid'];
			$redpacketlogid = $_GPC['redpacketlogid'];
			$allcost = $ksxgnum * $reprice ;
			if($_GPC['is_point'] == 1 && $school['Is_point']==1){
				$point_dy = $_GPC['point'];
				$dyl = $cose['Point2Cost'];
				$dyfy =sprintf("%.2f",  $point_dy / $dyl);
				$final_cose = $allcost - $dyfy;

				if ($final_cose <= 0) {
		            die ( json_encode ( array (
	                    'result' => false,
	                    'msg' => '抱歉，抵用价格必须小于应支付价格'
	               	) ) );
				}
				$allcost = $final_cose;
			}
			$temp = array(
				'weid' =>  $weid,
				'schoolid' => $schoolid,
				'sid' => $sid,
				'userid' => $userid,
				'type' => 1,
				'status' => 1,
				'xufeitype' => 1 ,
				'kcid' => $_GPC ['kcid'],
				'uid' => $_GPC['uid'],
				'cose' => $allcost,
				'payweid' => $cose['payweid'],
				'orderid' => $orderid,
				'createtime' => time(),
				'ksnum' => $ksxgnum,
				'kcstatus' => $order ? 1 : 0, //1为续购
				'couponlogid' => $couponlogid,
				'redpacketlogid' => $redpacketlogid,
			);
			if(!empty($_GPC['point']) && $school['Is_point']){
				$temp['spoint'] = $_GPC['point'];
				}
			pdo_insert($this->table_order, $temp);
   			$outid = pdo_insertid();
			$order_id = pdo_insertid();
			$data ['result'] = true;
			$data ['msg'] = "续购成功，请至订单中心查看";

		 die ( json_encode ( $data ) );
		}
    }

    if ($operation == 'chongzhi'){ //余额充值
		if (! $_GPC ['schoolid'] || ! $_GPC ['weid']) {
               die ( json_encode ( array (
                    'result' => false,
                    'msg' => '非法请求1！'
		               ) ) );
	         }
	   	$schoolid = $_GPC['schoolid'];
		$weid = $_GPC['weid'];
		$sid = $_GPC['sid'];
		$userid = $_GPC['userid'];
		$id = $_GPC['id'];
     	$taocan =  pdo_fetch("SELECT * FROM " . tablename($this->table_chongzhi) . " WHERE schoolid = '{$schoolid}' And weid = '{$weid}' And id = '{$id}'");
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " WHERE :id = id", array(':id' => $_GPC['schoolid']));
		$user = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " WHERE :weid = weid And :schoolid = schoolid And :id = id", array(':weid' => $_GPC['weid'], ':schoolid' => $_GPC['schoolid'], ':id' => $_GPC['userid']));
		$student = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " WHERE :weid = weid And :schoolid = schoolid And :id = id", array(':weid' => $_GPC['weid'], ':schoolid' => $_GPC['schoolid'], ':id' => $_GPC['sid']));
		$cose = pdo_fetch("SELECT * FROM " . tablename($this->table_tcourse) . " WHERE :id = id", array(':id' => $_GPC['kcid']));



			$orderid = "{$userid}{$sid}";
			$taocanid = $id;
			$allcost = $taocan['cost'] ;

			$temp = array(
				'weid' =>  $weid,
				'schoolid' => $schoolid,
				'sid' => $sid,
				'userid' => $userid,
				'type' => 8,
				'status' => 1,
				'cose' => $allcost,
				'taocanid' => $id,
				'payweid' => $school['chongzhiweid'],
				'orderid' => $orderid,
				'createtime' => time(),
			);
			pdo_insert($this->table_order, $temp);
			$data ['result'] = true;
			$data ['msg'] = "请至订单中心完成付费！";

		 die ( json_encode ( $data ) );

    }
	if ($operation == 'buy_charge'){ //余额充值
		if (! $_GPC['schoolid'] || ! $_GPC['weid']) {
               die ( json_encode ( array (
                    'result' => false,
                    'msg' => '非法请求！'
		               ) ) );
	         }
	   	$schoolid = $_GPC['schoolid'];
		$weid = $_GPC['weid'];
		$sid = $_GPC['sid'];
		$userid = $_GPC['userid'];
		$buy_num = $_GPC['buy_num'];
		$school = pdo_fetch("SELECT chargesetinfo FROM " . tablename($this->table_index) . " WHERE :id = id", array(':id' => $_GPC['schoolid']));
		$chargesetinfo = unserialize($school['chargesetinfo']);
 		if($chargesetinfo['is_charge'] != 1 ){
			$back_data = array(
				'result' => false,
				'msg'	 => "抱歉，当前学校暂未开通充电桩服务",
			);
			die ( json_encode ( $back_data ) );
		}
		if($buy_num < $chargesetinfo['min_num']){
			$back_data = array(
				'result' => false,
				'msg'	 => "抱歉，购买次数不能低于最低次数",
			);
			die ( json_encode ( $back_data ) );
		}
	 	$user = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " WHERE :weid = weid And :schoolid = schoolid And :id = id", array(':weid' => $_GPC['weid'], ':schoolid' => $_GPC['schoolid'], ':id' => $_GPC['userid']));
		$student = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " WHERE :weid = weid And :schoolid = schoolid And :id = id", array(':weid' => $_GPC['weid'], ':schoolid' => $_GPC['schoolid'], ':id' => $_GPC['sid']));
			$orderid = "{$userid}{$sid}";
			$allcost = $_GPC['allcost'] ;

			$temp = array(
				'weid' =>  $weid,
				'schoolid' => $schoolid,
				'sid' => $sid,
				'userid' => $userid,
				'type' => 9, //充电桩次数购买
				'status' => 1,
				'cose' => $allcost,
				'payweid' => $chargesetinfo['chargepayweid'],
				'orderid' => $orderid,
				'createtime' => time(),
				'ksnum' => $buy_num
			);
			pdo_insert($this->table_order, $temp);
			$data['result'] = true;
			$data['msg'] = "请至订单中心完成付费！";
			$data['data'] = $temp;
		 die ( json_encode ( $data ) );

    }
	if ($operation == 'make_kcorder'){//生成课程购买订单
		$ggid = intval($_GPC['ggid']);
		$kcid = intval($_GPC['kcid']);
		$weid = intval($_GPC['weid']);
		$schoolid = intval($_GPC['schoolid']);
		$openid = trim($_GPC['openid']);
		$userid = intval($_GPC['userid']);
		$gmtype = intval($_GPC['gmtype']);
		$join_teamid = intval($_GPC['join_teamid']);
		$s_name = trim($_GPC['stu_name']);
		$mobile = trim($_GPC['stu_mobile']);
		$pard = intval($_GPC['pard']);
		$points = intval($_GPC['points']);
		$mastertid = intval($_GPC['mastertid']);
		$paytype = trim($_GPC['paytype']);
		$zlteamid = intval($_GPC['zlteamid']);
		$result = array();
		mload()->model('kc');
		if (empty($schoolid) || empty($weid) || empty($gmtype)){
			$result['result'] = false;
			$result['msg'] = "非法请求！";
		}else{
			$kcinfo = pdo_fetch("SELECT * FROM " . GetTableName('tcourse') . " WHERE :id = id", array(':id' => $kcid));
			$payweid = $kcinfo['payweid'] ? $kcinfo['payweid'] : $weid;
			if($kcinfo['minge'] > 0){//限制了名额
				$countstu  = GetKcSiguNub($kcid); //取真实已报名人数排除了正在团的人
				if($kcinfo['minge'] < $countstu){//满员情况下直接die掉
					$result['result'] = false;
					$result['msg'] = "抱歉本课程报名已满!";
					die ( json_encode ( $result ) );
				}
			}
			$saleset = pdo_fetch("SELECT * FROM " . GetTableName('kc_saleset') . " WHERE id = :id ", array(':id' => $kcinfo['sale_id']));
			$result['is_newstu'] = 1;
			if(empty($userid) && !empty($s_name) && !empty($mobile)){ //对新增的学生写入库
				mload()->model('stu');
				$checkrep = CheckStuNameForFans($s_name,$schoolid,$openid);
				if($checkrep){
					$result['result'] = false;
					$result['msg'] = '抱歉，您已经添加过该生,请从列表中选择，尝试刷新本页';
					die(json_encode($result));
				}else{
					$userdata = array('weid' => $weid,'schoolid' => $schoolid,'openid' => $openid,'realname' => $s_name,'mobile' => $mobile,'pard' => 4,'status' => 1,'com_from' => 1);
					pdo_insert(GetTableName('user',false), $userdata);
					$nowuserid = pdo_insertid();
					$account_api = WeAccount::create($weid);
					$fans_info = $account_api->fansQueryInfo($openid);
					$students = array('weid' => $weid,'schoolid' => $schoolid,'icon' => $fans_info['headimgurl'],'s_name' => $s_name,'mobile' => $mobile,'ouserid' => $nowuserid,'from_kcid' => $kcid,'superior_tid' => $mastertid,'status' => 1,'seffectivetime' => time(),'createdate' => time());
					pdo_insert(GetTableName('students',false), $students);
					$sid = pdo_insertid();
					$belong = CheckFansBelong($kcid,$userid,$openid);
					if(empty($mastertid) && $belong['tid']){//记录推广员
						pdo_update(GetTableName('students',false), array('superior_tid' => $belong['tid']), array('id' => $sid));
					}
					pdo_update(GetTableName('user',false), array('sid' => $sid), array('id' => $nowuserid));
					$this->DoTag($schoolid,$nowuserid); //打标签
					$result['is_newstu'] = 2;
					$tid = $mastertid;
				}
			}else{
				$user =  pdo_fetch("SELECT sid FROM " . GetTableName('user') . " WHERE :id = id", array(':id' => $userid));
				$stu =  pdo_fetch("SELECT superior_tid FROM " . GetTableName('students') . " WHERE :id = id", array(':id' => $user['sid']));
				$nowuserid  = $userid;
				if(empty($stu['superior_tid'])){//对注册用户没有归属的进行改写
					if(!empty($mastertid)){
						$tid = $mastertid;
					}else{
						$belong = CheckFansBelong($kcid,$userid,$openid);
						$tid = $belong['tid'];
					}
					SetFansInfoByKc($kcid,$openid,$nowuserid,$tid,$masteropenid,$masteruserid,1);
					pdo_update(GetTableName('students',false), array('superior_tid' => $tid), array('id' => $user['sid']));
				}
				$sid = $user['sid'];
			}
			$tuanz_price = $kcinfo['sale_type'] == 1?$saleset['tuanz_price']:0;//团长优惠
			$all_dis_price = $saleset['price'];//组队优惠
			$diyong = 0;
			if(!empty($userid) && !empty($points)){//真实用户和前端输入的积分抵扣
				$diyong = round($points/$kcinfo['Point2Cost'],2);
			}
			if($kcinfo['guigetype'] == 1){
				$guige = pdo_fetch("SELECT price,ksnum FROM ".GetTableName('guige')." WHERE id = '{$ggid}' ");
				$now_price = $guige['price'] - $diyong; //减掉抵扣
			}else{
				$now_price = $kcinfo['cose'] - $diyong; //减掉抵扣
			}


			$yhprice = 0;
			if($_GPC['couponlogid']){
				$couponcose = pdo_fetch("SELECT w.cose,wf.status FROM " . GetTableName('welfarelog') . " as wf LEFT JOIN " . GetTableName('welfare') . " as w ON wf.welfareid = w.id WHERE wf.id = :id ", array(':id' => $_GPC['couponlogid']));
				if($couponcose['status'] != 2){
					$yhprice += $couponcose['cose'];
				}
			}
			if($_GPC['redpacketlogid']){
				$redpacketcose = pdo_fetch("SELECT w.cose,wf.status FROM " . GetTableName('welfarelog') . " as wf LEFT JOIN " . GetTableName('welfare') . " as w ON wf.welfareid = w.id WHERE wf.id = :id ", array(':id' => $_GPC['redpacketlogid']));
				if($redpacketcose['status'] != 2){
					$yhprice += $redpacketcose['cose'];
				}
			}

			if($gmtype == 1){//直接购买
				$cose = $now_price;
				$fee = max(0,$now_price - $yhprice);
				$result['dos'] = 'myclass';
				$result['nowuserid'] = $nowuserid;
				$result['op'] = '';
				$result['jumpurl'] = urlencode($_W['siteroot']."app/index.php?i=".$weid."&c=entry&schoolid=".$schoolid."&userid=".$nowuserid."&id=".$kcid."&do=mykcinfo&m=fm_jiaoyu");
			}
			$team = array( 'weid' => $weid, 'schoolid' => $schoolid, 'kcid' => $kcid, 'userid' => $nowuserid, 'openid' => $openid, 'ismaster' => 1, 'type' => 0, 'createtime' => time() );
			if($saleset['overtimeset']== 1){
				$team['endtime'] = $saleset['endtime'];
			}
			if($saleset['overtimeset']== 2){
				$team['endtime'] = time() + $saleset['overtime']*3600;
			}
			if($gmtype == 2 ){//助力成功后支付
				$team_id = $zlteamid;
				$fee = max(0,$now_price - $all_dis_price - $yhprice);
				$cose = $now_price - $all_dis_price;
				$result['dos'] = 'mysaleinfo';
				$result['nowuserid'] = $nowuserid;
				$result['op'] = 'zhuli';
				$result['jumpurl'] = urlencode($_W['siteroot']."app/index.php?i=".$weid."&c=entry&schoolid=".$schoolid."&id=".$nowuserid."&op=zhuli&do=mysaleinfo&m=fm_jiaoyu");
			}
			if($gmtype == 3){//开团
				$team['type'] = 1;
				pdo_insert(GetTableName('sale_team',false), $team);
				$team_id = pdo_insertid();
				pdo_update(GetTableName('sale_team',false), array('masterid'=>$team_id),array('id'=>$team_id));//修改主ID
				$cose = $now_price - $all_dis_price - $tuanz_price; //开团的减掉团长优惠
				$fee = max(0,$now_price - $all_dis_price - $tuanz_price - $yhprice);
				$result['dos'] = 'mysaleinfo';
				$result['nowuserid'] = $nowuserid;
				$result['op'] = 'tuan';
				$result['jumpurl'] = urlencode($_W['siteroot']."app/index.php?i=".$weid."&c=entry&schoolid=".$schoolid."&id=".$nowuserid."&op=tuan&do=mysaleinfo&m=fm_jiaoyu");
			}
			if($gmtype == 4){//参团
				$masteam = pdo_fetch("SELECT endtime,masterid FROM " . GetTableName('sale_team') . " WHERE id = :id ", array(':id' => $join_teamid));
				$team['type'] = 1;
				$team['ismaster'] = 0;//参团非主ID
				$team['masterid'] = $masteam['masterid'];
				$team['endtime'] = $masteam['endtime'];
				pdo_insert(GetTableName('sale_team',false), $team);
				$team_id = pdo_insertid();
				$cose = $now_price - $all_dis_price;
				$fee = max(0,$now_price - $all_dis_price - $yhprice);
				$result['dos'] = 'mysaleinfo';
				$result['op'] = 'tuan';
				$result['nowuserid'] = $nowuserid;
				$result['jumpurl'] = urlencode($_W['siteroot']."app/index.php?i=".$weid."&c=entry&schoolid=".$schoolid."&id=".$nowuserid."&op=tuan&do=mysaleinfo&m=fm_jiaoyu");
			}

			$fee = round($fee,2);
			$cose = round($cose,2);

			$orderdata = array(
			   'weid' => $weid,
			   'schoolid' => $schoolid,
			   'kcid' => $kcid,
			   'userid' => $nowuserid,
			   'sid' => $sid,
			   'cose' => $cose,
			   'team_price' => $gmtype != 1 ? $all_dis_price : 0,
			   'team_dz_price' => $tuanz_price,
			   'status' => 1,
			   'type' => 1,
			   'guigeid' => $ggid,
			   'pay_type' => $_GPC['payMethod'],
			   'team_id' => $team_id,//此处写入的非主teamid
			   'sale_rule' => $kcinfo['sale_id'],
			   'sale_type' => $kcinfo['sale_type'],
			   'spoint' => $points,
			   'ksnum' => $guige ? $guige['ksnum'] : $kcinfo['FirstNum'],
			   'payweid' => $payweid,
			   'superior_tid' => $tid,
			   'couponlogid' => $_GPC['couponlogid'],
			   'redpacketlogid' => $_GPC['redpacketlogid'],
			   'createtime' => time()
			);
			if($gmtype == 1){
				$orderdata['new_stu'] = 1;
			}
			pdo_insert(GetTableName('order',false), $orderdata);
			$orderid = pdo_insertid();
			if($gmtype != 1){//非直接购买情况下 为team上传orderid
				pdo_update(GetTableName('sale_team',false), array('orderid' => $orderid), array('id' => $team_id));
			}
			if($gmtype == 2 ){//助力的orderid来源于未付订单 助力报名的时候产生
				$checkteam = pdo_fetch("SELECT orderid FROM " . GetTableName('sale_team') . " WHERE :id = id", array(':id' => $zlteamid));
				if($checkteam['orderid']){
					$orderid = $checkteam['orderid'];
					pdo_update(GetTableName('order',false), $orderdata, array('id' => $orderid));
				}
			}
			$wxpay = array(
			   'weid' => $weid,
			   'schoolid' => $schoolid,
			   'od1' => $orderid,
			   'payweid' => $payweid,
			   'cose' => $fee,
			   'openid' => $openid,
			   'status'=>1
			);
			pdo_insert(GetTableName('wxpay',false), $wxpay);
			$wxpay_id = pdo_insertid();
			pdo_update(GetTableName('order',false), array('wxpayid' => $wxpay_id), array('id' => $orderid));
			if($fee <= 0 || $paytype == 'yuezhifu') {//余额支付的情况，直接扣除 触发payResult
				$studnet = pdo_fetch("SELECT chongzhi FROM " . GetTableName('students') . " WHERE :id = id", array(':id' => $sid));
				if($studnet['chongzhi'] >= $fee){
					pdo_update(GetTableName('students',false), array('chongzhi' => $studnet['chongzhi']-$fee), array('id' => $sid));
					$notify_params = array(
						'form' => 'notify',
						'returnurl' => 'returnurl',
						'result' => 'success',
						'type' => '',
						'tid' => $wxpay_id,
					);
					//$payResult = $this->payResult($notify_params);
					pdo_update(GetTableName('order',false), array('pay_type' => 'credit'), array('id' => $orderid));
					$site = WeUtility::createModuleSite('fm_jiaoyu');
					if(!is_error($site)) {
						$site->weid = $weid;
						$site->uniacid = $weid;
						$site->inMobile = true;
						$method = 'payResult';
						if (method_exists($site, $method)) {
							$ret = array();
							$ret['result'] = 'success';
							$ret['type'] = '';
							$ret['from'] = 'notify';
							$ret['tid'] = $wxpay_id;
							$ret['user'] = $openid;
							$ret['fee'] = $fee;
							$ret['returnurl'] = 'returnurl';
							$ret['weid'] = $weid;
							$ret['uniacid'] = $weid;
							$ret['acid'] = $weid;
							$ret['is_usecard'] = '';
							$ret['card_type'] = '';
							$ret['card_fee'] = '';
							$ret['card_id'] = '';
							$site->$method($ret);
						}
					}
					$result['result'] = true;
					$result['msg'] = '支付成功,已经扣除'.$fee.'元';
				}else{
					$result['result'] = false;
					$result['msg'] = '余额不足,请前往学生中心充值';
				}
				$result['fee'] = $fee;
				$result['ordertid'] = intval($wxpay_id);
				$result['orderid'] = intval($orderid);
				die ( json_encode ( $result ) );
			}
			$result['fee'] = $fee;
			$result['title'] = '购买课程';
			$result['ordertid'] = intval($wxpay_id);
			$result['orderid'] = intval($orderid);
			$result['payweid'] = intval($payweid);
			$result['result'] = true;
		}
		die ( json_encode ( $result ) );
	}
	if ($operation == 'check_orderpay'){
		$orderid = intval($_GPC['orderid']);
		$complete = $_GPC['result'];
		//CommonWriteLog($complete,'complete');
		$result['result'] = false;
		$order =  pdo_fetch("SELECT status FROM " . GetTableName('order') . " WHERE :id = id", array(':id' => $orderid));
		if($order['status'] != 2){
			$result['result'] = true;
		}
		die ( json_encode ( $result ) );
	}
	if ($operation == 'del_ordertid'){
		$orderid = intval($_GPC['orderid']);
		$is_newstu = intval($_GPC['is_newstu']);
		$order =  pdo_fetch("SELECT * FROM " . GetTableName('order') . " WHERE :id = id", array(':id' => $orderid));
		if(!empty($order)){
			if($order['sale_type'] != 2){
				pdo_delete('core_paylog', array('tid' => $order['wxpayid']));//删除core_paylog
				pdo_delete(GetTableName('wxpay',false), array('id' => $order['wxpayid']));//删除wxpay
				pdo_delete(GetTableName('sale_team',false), array('id' => $order['team_id']));//删除本次创建的这行team 只删一行 因为有可能是加入队伍
				if($is_newstu == 2){//新增学生的情况下
					pdo_delete(GetTableName('user',false), array('id' => $order['userid']));//删除user表
					pdo_delete(GetTableName('students',false), array('id' => $order['sid']));//删除student表
				}
				pdo_delete(GetTableName('coursebuy',false), array('orderid' => $order['id']));//最后删除本次订单
				pdo_delete(GetTableName('order',false), array('id' => $order['id']));//最后删除本次订单
			}
		}
	}
	if ($operation == 'get_payinfo_byorderid'){
		$weid = $_GPC['weid'];
		$schoolid = $_GPC['schoolid'];
		$orderid = $_GPC['orderid'];
		$openid = $_GPC['openid'];
		$paytype = $_GPC['payMethod'];
		$order =  pdo_fetch("SELECT * FROM " . GetTableName('order') . " WHERE :id = id", array(':id' => $orderid));
		$payweid = $order['payweid'] ? $order['payweid'] : $weid;
		$fee = $order['cose'];
		$wxpay = array(
		   'weid' => $weid,
		   'schoolid' => $schoolid,
		   'od1' => $orderid,
		   'payweid' => $payweid,
		   'cose' => $fee,
		   'openid' => $openid,
		   'status'=>1
		);
		pdo_insert(GetTableName('wxpay',false), $wxpay);
		$wxpay_id = pdo_insertid();
		pdo_update(GetTableName('order',false), array('wxpayid' => $wxpay_id), array('id' => $orderid));
		if($fee <= 0 || $paytype == 'yuezhifu') {//余额支付的情况，直接扣除 触发payResult
			$studnet = pdo_fetch("SELECT chongzhi FROM " . GetTableName('students') . " WHERE :id = id", array(':id' => $order['sid']));
			if($studnet['chongzhi'] > $fee){
				pdo_update(GetTableName('students',false), array('chongzhi' => $studnet['chongzhi']-$fee), array('id' => $order['sid']));
				$notify_params = array(
					'form' => 'notify',
					'returnurl' => 'returnurl',
					'result' => 'success',
					'type' => '',
					'tid' => $wxpay_id,
				);
				//$payResult = $this->payResult($notify_params);
				pdo_update(GetTableName('order',false), array('pay_type' => 'credit'), array('id' => $orderid));
				$site = WeUtility::createModuleSite('fm_jiaoyu');
				if(!is_error($site)) {
					$site->weid = $weid;
					$site->uniacid = $weid;
					$site->inMobile = true;
					$method = 'payResult';
					if (method_exists($site, $method)) {
						$ret = array();
						$ret['result'] = 'success';
						$ret['type'] = '';
						$ret['from'] = 'notify';
						$ret['tid'] = $wxpay_id;
						$ret['user'] = $openid;
						$ret['fee'] = $fee;
						$ret['returnurl'] = 'returnurl';
						$ret['weid'] = $weid;
						$ret['uniacid'] = $weid;
						$ret['acid'] = $weid;
						$ret['is_usecard'] = '';
						$ret['card_type'] = '';
						$ret['card_fee'] = '';
						$ret['card_id'] = '';
						$site->$method($ret);
					}
				}
				$result['result'] = true;
				$result['msg'] = '支付成功,已经扣除'.$fee.'元';
			}else{
				$result['result'] = false;
				$result['msg'] = '余额不足,请前往学生中心充值';
			}
			$result['fee'] = $fee;
			$result['ordertid'] = intval($wxpay_id);
			$result['orderid'] = intval($orderid);
			die ( json_encode ( $result ) );
		}
		$result['jumpurl'] = "";
		if($order['sale_type'] == 2 ){//助力成功后支付
			$result['jumpurl'] = urlencode($_W['siteroot']."app/index.php?i=".$weid."&c=entry&schoolid=".$schoolid."&id=".$nowuserid."&op=zhuli&do=mysaleinfo&m=fm_jiaoyu");
		}
		if($order['sale_type'] == 1){//开团
			$result['jumpurl'] = urlencode($_W['siteroot']."app/index.php?i=".$weid."&c=entry&schoolid=".$schoolid."&id=".$nowuserid."&op=tuan&do=mysaleinfo&m=fm_jiaoyu");
		}
		$result['fee'] = $fee;
		$result['title'] = '购买课程';
		$result['ordertid'] = intval($wxpay_id);
		$result['orderid'] = intval($orderid);
		$result['payweid'] = intval($payweid);
		$result['result'] = true;
		die ( json_encode ( $result ) );
	}

	if ($operation == 'CheckBuyManual'){
		$id = $_GPC['id'];
		$sid = $_GPC['sid'];
		$schoolid = $_GPC['schoolid'];
		//或者订单id
		$orderid =  pdo_fetch("SELECT id FROM " . GetTableName('order') . " WHERE :schoolid = schoolid AND :sid = sid AND :extra_info = extra_info AND :type = type AND :status = status", array(':schoolid' => $_GPC['schoolid'], ':sid' => $_GPC['sid'], ':extra_info' => $_GPC['id'], ':type' => 20, ':status' => 1))['id'];
		//获取用户的userid
		$userid = pdo_fetch("SELECT id FROM " . tablename($this->table_user) . " where sid = :sid", array(':sid' => $_GPC['sid']))['id'];
		if($orderid){ //有订单直接返回订单ID
			$result['orderid'] = $orderid;
			$result['msg'] = '您之前已经下单，是否继续前往支付';
		}else{ //没有订单新增订单
			$file = pdo_fetch("SELECT cose FROM " . GetTableName('growupfile') . " WHERE :schoolid = schoolid AND :id = id", array(':schoolid' => $_GPC['schoolid'], ':id' => $_GPC['id']));
			$data = array(
				'weid' => $_GPC['weid'],
				'schoolid' => $_GPC['schoolid'],
				'orderid' => time(),
				'userid' => $userid,
				'sid' => $_GPC['sid'],
				'cose' => $file['cose'],
				'status' => 1,
				'type' => 20,
				'createtime' => time(),
				'extra_info' => $_GPC['id'],
			);
			$order = pdo_insert(GetTableName('order',false),$data);
			$orderid = pdo_insertid();
			$result['msg'] = '前往支付';
		}
		die(json_encode($result));
	}

	if ($operation == 'CheckWelfare'){
		mload()->model('pay');
		$id = $_GPC['orderid'];
		$schoolid = $_GPC['schoolid'];
		$order =  pdo_fetch("SELECT * FROM " . GetTableName('order') . " WHERE :schoolid = schoolid AND :id = id AND status = :status ", array(':schoolid' => $_GPC['schoolid'], ':id' => $id, ':status' => 1));
		if($order){ //有订单直接返回订单ID
			$result['result'] = false;
			if($order['couponlogid']){
				$coupon = getMyWelfare($order['couponlogid'],2);
				if($coupon){
					$result['result'] = true;
					$result['msg'] = $coupon['title'].'已被使用,请删除当前订单重新下单';
				}
			}
			if($order['redpacketlogid']){
				$redpacket = getMyWelfare($order['redpacketlogid'],2);
				if($redpacket){
					$result['result'] = true;
					$result['msg'] = $redpacket['title'].'已被使用,请删除当前订单重新下单';
				}
			}
		}
		die(json_encode($result));
    }

    //重构，余额支付 或者支付金额为 0 ajax
    if ($operation == 'yuepayRemaster'){

        $weid = $_GPC['weid'];
        $schoolid = $_GPC['schoolid'];
        $orderinfo = pdo_fetch("SELECT * FROM ".GetTableName('order')." WHERE id = '{$_GPC['orderid']}' ");
        $orderid = $_GPC['orderid'];
        $fee = $_GPC['cose'];
        $wxpay_id = $_GPC['wxpayid'];
        $openid = $_GPC['openid'];
            if($_GPC['usertype'] == 'stu'){ //身份为学生
                $user = pdo_fetch("SELECT sid FROM ".GetTableName('user')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' and id = '{$_GPC['userid']}' ");
                $sid = $user['sid'];
                $studnet = pdo_fetch("SELECT chongzhi FROM " . GetTableName('students') . " WHERE :id = id", array(':id' => $user['sid']));
                if($studnet['chongzhi'] >= $fee){ //扣除余额
                    pdo_update(GetTableName('students',false), array('chongzhi' => $studnet['chongzhi']-$fee), array('id' => $sid));
                    $notify_params = array(
                        'form' => 'notify',
                        'returnurl' => 'returnurl',
                        'result' => 'success',
                        'type' => '',
                        'tid' => $wxpay_id,
                    );
                    pdo_update(GetTableName('order',false), array('pay_type' => 'credit'), array('id' => $orderid));
                    $site = WeUtility::createModuleSite('fm_jiaoyu');
                    if(!is_error($site)) {
                        $site->weid = $weid;
                        $site->uniacid = $weid;
                        $site->inMobile = true;
                        $method = 'payResult';
                        if (method_exists($site, $method)) {
                            $ret = array();
                            $ret['result'] = 'success';
                            $ret['type'] = '';
                            $ret['from'] = 'notify';
                            $ret['tid'] = $wxpay_id;
                            $ret['user'] = $openid;
                            $ret['fee'] = $fee;
                            $ret['returnurl'] = 'returnurl';
                            $ret['weid'] = $weid;
                            $ret['uniacid'] = $weid;
                            $ret['acid'] = $weid;
                            $ret['is_usecard'] = '';
                            $ret['card_type'] = '';
                            $ret['card_fee'] = '';
                            $ret['card_id'] = '';
                            if($fee > 0){
                                $ret['chongzhi'] = 'chongzhi';
                            }
                            $site->$method($ret);
                        }
                    }
                    $result['result'] = true;
                    $result['msg'] = '支付成功,已经扣除'.$fee.'元';
                }else{
                    $result['result'] = false;
                    $result['msg'] = '余额不足,请前往学生中心充值';
                }
            }
            if($_GPC['usertype'] == 'tea'){
                $site = WeUtility::createModuleSite('fm_jiaoyu');
                    if(!is_error($site)) {
                        $site->weid = $weid;
                        $site->uniacid = $weid;
                        $site->inMobile = true;
                        $method = 'payResult';
                        if (method_exists($site, $method)) {
                            $ret = array();
                            $ret['result'] = 'success';
                            $ret['type'] = '';
                            $ret['from'] = 'notify';
                            $ret['tid'] = $wxpay_id;
                            $ret['user'] = $openid;
                            $ret['fee'] = $fee;
                            $ret['returnurl'] = 'returnurl';
                            $ret['weid'] = $weid;
                            $ret['uniacid'] = $weid;
                            $ret['acid'] = $weid;
                            $ret['is_usecard'] = '';
                            $ret['card_type'] = '';
                            $ret['card_fee'] = '';
                            $ret['card_id'] = '';
                            $site->$method($ret);
                        }
                    }
            }
            $result['fee'] = $fee;
            $result['ordertid'] = intval($wxpay_id);
            $result['orderid'] = intval($orderid);
            die (json_encode($result) );
    }
?>