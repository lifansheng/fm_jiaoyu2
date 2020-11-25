<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

$dos = array('get_setting', 'display', 'save_setting');
$do = in_array($do, $dos) ? $do : 'display';
permission_check_account_user('wxapp_payment_pay');

$setting = uni_setting_load('payment', $_W['uniacid']);
$pay_setting = $setting['payment'];
$wxapp_info = miniapp_fetch($_W['uniacid']);

if ('get_setting' == $do) {
	iajax(0, $pay_setting, '');
}

if ('display' == $do) {
	if (empty($pay_setting) || empty($pay_setting['wechat'])) {
		$pay_setting = array(
			'wechat' => array('mchid' => '', 'signkey' => ''),
		);
	}
}

if ('save_setting' == $do) {
	if (!$_W['isajax'] || !$_W['ispost']) {
		iajax(-1, '非法访问');
	}
	$type = $_GPC['type'];
	if (!in_array($type, array('wechat', 'wechat_facilitator'))) {
		iajax(-1, '参数错误');
	}
	$version_id = empty($_GPC['version_id']) ? STATUS_OFF : $_GPC['version_id'];
	$param = $_GPC['param'];
	$param['account'] = $_W['acid'];
	$param['status'] = STATUS_OFF;
	$pay_setting['pay_type'] = '';
	if ($type == 'wechat' && $param['switch'] === 'true') {
		$param['switch'] = PAYMENT_WECHAT_TYPE_NORMAL;
		$param['status'] = STATUS_ON;
		$pay_setting['wechat_facilitator']['status'] = $pay_setting['wechat_facilitator']['status'] == STATUS_ON ? STATUS_OFF : STATUS_OFF;
		$pay_setting['pay_type'] = 'wechat';
	}
	if ($type == 'wechat_facilitator' && $param['switch'] === 'true') {
		$pay_setting['wechat']['switch'] = PAYMENT_WECHAT_TYPE_SERVICE;
		$param['switch'] = PAYMENT_WECHAT_TYPE_SERVICE;
		$param['status'] = STATUS_ON;
		$pay_setting['wechat']['status'] = $pay_setting['wechat']['status'] == STATUS_ON ? STATUS_OFF : STATUS_OFF;
		$pay_setting['pay_type'] = 'wechat_facilitator';
	}
	$pay_type = $type == 'wechat' ? 'wechat_facilitator' : 'wechat';
	if (empty($pay_setting['pay_type']) && empty($param['status']) && empty($pay_setting[$pay_type]['status'])) {
		$pay_setting['pay_type'] = '';
	}
	$pay_setting[$type] = $param;
	$payment = iserializer($pay_setting);
	uni_setting_save('payment', $payment);
	iajax(0, '设置成功', url('wxapp/payment', array('version_id' => $version_id)));
}
template('wxapp/payment');