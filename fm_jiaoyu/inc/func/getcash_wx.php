<?php

/**
 * By 高贵血迹
 */

global $_GPC, $_W;

$weid = $_GPC['i'];
$oldweid = $_GPC['oldweid'];
$orderid = $_GPC['orderid'];
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if($operation == 'display'){
	$order = pdo_fetch("SELECT * FROM " . GetTableName('getcash') . " where id = '{$orderid}' ");
	include $this->template('teacher/getcash/getcash_wx');
}
if($operation == 'getcash'){
	$order = pdo_fetch("SELECT * FROM " . GetTableName('getcash') . " where id = '{$orderid}' ");
	if(!empty($order)){
		mload()->model('wxpay');
		$data = array( 'paynickname' => $_W['fans']['nickname'], 'payopenid' => $_W['openid'], 'status' => 2, 'dztime' => time(), ); 		
		$payweid = empty($order['payweid'])?$order['weid']:$order['payweid'];
		$wxPay = new WxpayService($payweid,'wechat');
		$wxgetcash = $wxPay->createJsBizPackage($_W['openid'], $order['fee'], $order['id'],$order['realname'],$payweid,$order['payrank']);
		if($wxgetcash===true){
			pdo_update(GetTableName('getcash',false), $data , array('id' => $orderid));
			$result['msg'] = '提现成功，已微信到账';
			$result['result'] = true;
		}else{
			$result['msg'] = check_erro($wxgetcash);
			$result['result'] = false;
		}
	}else{
		$result['msg'] = '该申请订单不存在';
		$result['result'] = false;
	}
	die(json_encode($result));
}

function check_erro($key){
	$msgarray = array(
		'NO_AUTH' => '没有该接口权限',
		'NO_AUTH' => '金额超限',
		'PARAM_ERROR' => '参数错误',
		'OPENID_ERROR' => 'Openid错误',
		'SEND_FAILED' => '付款错误',
		'NOTENOUGH' => '余额不足',
		'SYSTEMERROR' => '系统繁忙，请稍后再试。',
		'NAME_MISMATCH' => '姓名校验出错',
		'SIGN_ERROR' => '签名错误',
		'FATAL_ERROR' => '两次请求参数不一致',
		'FREQ_LIMIT' => '超过频率限制，请稍后再试。',
		'MONEY_LIMIT' => '已经达到今日付款总额上限/已达到付款给此用户额度上限',
		'CA_ERROR' => '商户API证书校验出错',
		'V2_ACCOUNT_SIMPLE_BAN' => '无法给未实名用户付款',
		'SENDNUM_LIMIT' => '该用户今日付款次数超过限制'
	);
	return $msgarray[$key];
}
