<?php
defined('IN_IA') or exit('Access Denied');
	isetcookie('__uniacid', '', -7 * 86400);
	isetcookie('__uid', '', -7 * 86400);
	isetcookie('__session', '', -10000);
	isetcookie('__iscontroller', '', -10000);
	$forward = $_GPC['forward'];
	if (empty($forward)) {
		$forward = $_W['siteroot'];
	}
	$url = $_W['siteroot'].'/addons/fm_jiaoyu/admin';
	var_dump(123);die;
	header('Location:' . $url);
// @header('Location: '.wurl('user/login'));
die;