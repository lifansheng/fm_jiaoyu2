<?php
/**
 * 微教育 高贵血迹
 */
defined('IN_IA') or exit('Access Denied');
if(checksubmit()) {
	_login($_GPC['referer']);
}
$setting = $_W['setting'];
$item = pdo_fetch("SELECT htname,is_new,newcenteriocn,banquan,bgimg,bgcolor,banner1,banner2,banner3,banner4 FROM " . tablename('wx_school_set') . " ORDER BY id ASC LIMIT 0,1");
$urls = "../../../attachment/";
$opreation = $_GPC['op']?$_GPC['op']:'display';
if($opreation == 'display'){
	if($item['is_new'] == 1){
		template('user/login');
	}
	if($item['is_new'] == 2){
		template('user/login_new');
	}
}
if($opreation == 'url'){
	$rAndStr = str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
	$school = pdo_fetch("SELECT weid FROM " . tablename('wx_school_index') . " where id = '{$_GPC['schoolid']}' ");
	do {
	  $token = substr($rAndStr, 0, 8);
	  $checktoken = checkuntoken($token);
	} while ($checktoken == true);
	$result['token'] = $token;
	$result['url'] = $_W['siteroot'].'app/index.php?i='.$school['weid'] .'&c=entry&schoolid='.$_GPC['schoolid'].'&m=fm_jiaoyu&do=pcauthlogin&token='.$token;
	die(json_encode($result));
}
if($opreation == 'checktoken'){
	$token = pdo_fetch("SELECT id,tid FROM " . tablename('wx_school_user') . " WHERE er_token = :er_token And schoolid = :schoolid ", array(':er_token' => $_GPC['token'],':schoolid' => $_GPC['schoolid']));
	if(!empty($token)){
		$result['result'] = true;
	}else{
		$result['result'] = false;
	}
	die(json_encode($result));
}
function checkuntoken($token){
	$checktoken = pdo_fetch("SELECT id FROM " . tablename('wx_school_user') . " WHERE er_token = :er_token ", array(':er_token' => $token));
	if(!empty($checktoken)){
		return true;
	}else{
		return false;
	}
}
if($opreation == 'authlogin'){
	global $_GPC, $_W;
	load()->model('user');
	$user = pdo_fetch("SELECT id,tid FROM " . tablename('wx_school_user') . " WHERE er_token = :er_token And schoolid = :schoolid ", array(':er_token' => $_GPC['token'],':schoolid' => $_GPC['schoolid']));
	$cheisbd = pdo_fetch("SELECT * FROM " . tablename('users') . " WHERE tid = :tid And schoolid = :schoolid", array(':tid' => $user['tid'],':schoolid' => $_GPC['schoolid']));
	$teainfo = pdo_fetch("SELECT password FROM " . tablename('wx_school_teachers') . " where id = '{$user['tid']}' ");
	$username = $cheisbd['username'];
	$password = $teainfo['password'];
	$member['username'] = $username;
	$member['password'] = $password;
	$failed = pdo_get('users_failed_login', array('username' => trim($username)));
	$record = user_single($member);
    if(!empty($record)) {
		if($record['status'] == 1) {
			message('您的账号正在审核或是已经被系统禁止，请联系网站管理员解决！');
		}
		$founders = explode(',', $_W['config']['setting']['founder']);
		$_W['isfounder'] = in_array($record['uid'], $founders);
		if (!empty($_W['siteclose']) && empty($_W['isfounder'])) {
			message('站点已关闭，关闭原因：' . $_W['setting']['copyright']['reason']);
		}
		$cookie = array();
		$cookie['uid'] = $record['uid'];
		$cookie['lastvisit'] = $record['lastvisit'];
		$cookie['lastip'] = $record['lastip'];
		$cookie['hash'] = !empty($record['hash']) ? $record['hash'] : md5($record['password'] . $record['salt']);
		$cookie['rember'] = $_GPC['rember'];
		$session = authcode(json_encode($cookie), 'encode');
		isetcookie('__session', $session, !empty($_GPC['rember']) ? 7 * 86400 : 0, true);
		pdo_update('users', array('lastvisit' => TIMESTAMP, 'lastip' => $_W['clientip']), array('uid' => $record['uid']));
		$tid = intval($record['tid']);
		$teacher = pdo_fetch("SELECT weid,schoolid FROM " . tablename('wx_school_teachers') . " WHERE id = '{$tid}'");	
		$schoolid = $teacher['schoolid'];
		$uniacid = $teacher['weid'];
		$logo = pdo_fetch("SELECT is_openht FROM " . tablename('wx_school_index') . " WHERE id = '{$schoolid}'");	
		if($logo['is_openht'] == 2 || empty($teacher)) {
			message('抱歉!本站点已经关闭,请联系管理员.');
		}		
		isetcookie('__uniacid', $uniacid, 7 * 86400);
		isetcookie('__uid', $record['uid'], 7 * 86400);
		
		if ($record['uid'] != $_GPC['__uid']) {
			isetcookie('__uniacid', '', -7 * 86400);
			isetcookie('__uid', '', -7 * 86400);
		}
		if (!empty($failed)) {
			pdo_delete('users_failed_login', array('id' => $failed['id']));
		}
	
        session_start();
        $_SESSION["from"] = 'depend';
		$_SESSION["testttt"] = 'depend';
		switch_save_account_display($uniacid);
		$site = WeUtility::createModule('fm_jiaoyu');
		$site = WeUtility::createModuleSite('fm_jiaoyu');
		$_W['current_module'] = 'fm_jiaoyu';
		define('IN_MODULE', 'fm_jiaoyu');
		if(is_TestFz()){
			$HasQxLogin = pdo_fetch("SELECT id FROM ".GetTableName('schoolset')." WHERE schoolid = {$schoolid} AND FIND_IN_SET({$record['uid']},uid) ");
			if(!empty($HasQxLogin)){
				$furl = $_W['siteroot'].'web/index.php?c=site&a=entry&uid='.$record['uid'].'&from=depend&do=newtype&m=fm_jiaoyu';
			}else{
				$furl = $_W['siteroot'].'web/index.php?c=site&a=entry&uid='.$record['uid'].'&from=depend&do=start&id='.$schoolid.'&i='.$uniacid.'&schoolid='.$schoolid.'&m=fm_jiaoyu';
			}
		}else{
			$furl = $_W['siteroot'].'web/index.php?c=site&a=entry&uid='.$record['uid'].'&from=depend&do=start&id='.$schoolid.'&i='.$uniacid.'&schoolid='.$schoolid.'&m=fm_jiaoyu';
			//$furl = $_W['siteroot'].'web/index.php?c=site&a=entry&op=display&schoolid='.$schoolid.'&do=start&m=fm_jiaoyu';
		}
		
        // $furl = $_W['siteroot'].'web/index.php?c=site&a=entry&uid='.$record['uid'].'&from=depend&do=start&id='.$schoolid.'&i='.$uniacid.'&schoolid='.$schoolid.'&m=fm_jiaoyu';
		header('Location:'.$furl);
    } else {
		if (empty($failed)) {
			pdo_insert('users_failed_login', array('ip' => CLIENT_IP, 'username' => $username, 'count' => '1', 'lastupdate' => TIMESTAMP));
		} else {
			pdo_update('users_failed_login', array('count' => $failed['count'] + 1, 'lastupdate' => TIMESTAMP), array('id' => $failed['id']));
		}
		message('登录失败，请检查您输入的用户名和密码！');
	}
}
function _login($forward = '') {
	global $_GPC, $_W;
	
	load()->model('user');
	if (!empty($_W['setting']['copyright']['verifycode'])) {
		$verify = trim($_GPC['verify']);
		if(empty($verify)) {
			message('请输入验证码');
		}
		$result = checkcaptcha($verify);
		if (empty($result)) {
			message('输入验证码错误');
		}
	}	
	$username = trim($_GPC['username']);
	if(empty($username)) {
		message('请输入要登录的用户名');
	}
	$password = trim($_GPC['password']);
	if(empty($password)) {
		message('请输入密码');
	}
	$member['username'] = $username;
	$member['password'] = $_GPC['password'];
	$record = user_single($member);
	$failed = pdo_get('users_failed_login', array('username' => trim($_GPC['username'])));
    if(!empty($record)) {
		if($record['status'] == 1) {
			message('您的账号正在审核或是已经被系统禁止，请联系网站管理员解决！');
		}
		$founders = explode(',', $_W['config']['setting']['founder']);
		$_W['isfounder'] = in_array($record['uid'], $founders);
		if (!empty($_W['siteclose']) && empty($_W['isfounder'])) {
			message('站点已关闭，关闭原因：' . $_W['setting']['copyright']['reason']);
		}
		$cookie = array();
		$cookie['uid'] = $record['uid'];
		$cookie['lastvisit'] = $record['lastvisit'];
		$cookie['lastip'] = $record['lastip'];
		$cookie['hash'] = !empty($record['hash']) ? $record['hash'] : md5($record['password'] . $record['salt']);
		$cookie['rember'] = $_GPC['rember'];
		$session = authcode(json_encode($cookie), 'encode');
		isetcookie('__session', $session, !empty($_GPC['rember']) ? 7 * 86400 : 0, true);
		pdo_update('users', array('lastvisit' => TIMESTAMP, 'lastip' => $_W['clientip']), array('uid' => $record['uid']));
		if(empty($forward)) {
			$forward = $_GPC['forward'];
		}
		$tid = intval($record['tid']);
		$teacher = pdo_fetch("SELECT weid,schoolid FROM " . tablename('wx_school_teachers') . " WHERE id = '{$tid}'");	
		$schoolid = $teacher['schoolid'];
		$uniacid = $teacher['weid'];
		$logo = pdo_fetch("SELECT is_openht FROM " . tablename('wx_school_index') . " WHERE id = '{$schoolid}'");	
		if($logo['is_openht'] == 2 || empty($teacher)) {
			message('抱歉!本站点已经关闭,请联系管理员.');
		}		
		isetcookie('__uniacid', $uniacid, 7 * 86400);
		isetcookie('__uid', $record['uid'], 7 * 86400);
		
		if ($record['uid'] != $_GPC['__uid']) {
			isetcookie('__uniacid', '', -7 * 86400);
			isetcookie('__uid', '', -7 * 86400);
		}
		if (!empty($failed)) {
			pdo_delete('users_failed_login', array('id' => $failed['id']));
		}
	
        session_start();
        $_SESSION["from"] = 'depend';
		$_SESSION["testttt"] = 'depend';
		switch_save_account_display($uniacid);
		$site = WeUtility::createModule('fm_jiaoyu');
		$site = WeUtility::createModuleSite('fm_jiaoyu');
		$_W['current_module'] = 'fm_jiaoyu';
		define('IN_MODULE', 'fm_jiaoyu');
		if(is_TestFz()){
			$HasQxLogin = pdo_fetch("SELECT id FROM ".GetTableName('schoolset')." WHERE schoolid = {$schoolid} AND FIND_IN_SET({$record['uid']},uid) ");
			if(!empty($HasQxLogin)){
				$furl = $_W['siteroot'].'web/index.php?c=site&a=entry&uid='.$record['uid'].'&from=depend&do=newtype&m=fm_jiaoyu';
			}else{
				$furl = $_W['siteroot'].'web/index.php?c=site&a=entry&uid='.$record['uid'].'&from=depend&do=start&id='.$schoolid.'&i='.$uniacid.'&schoolid='.$schoolid.'&m=fm_jiaoyu';
			}
		}else{
			$furl = $_W['siteroot'].'web/index.php?c=site&a=entry&uid='.$record['uid'].'&from=depend&do=start&id='.$schoolid.'&i='.$uniacid.'&schoolid='.$schoolid.'&m=fm_jiaoyu';
			//$furl = $_W['siteroot'].'web/index.php?c=site&a=entry&op=display&schoolid='.$schoolid.'&do=start&m=fm_jiaoyu';
		}
		
        // $furl = $_W['siteroot'].'web/index.php?c=site&a=entry&uid='.$record['uid'].'&from=depend&do=start&id='.$schoolid.'&i='.$uniacid.'&schoolid='.$schoolid.'&m=fm_jiaoyu';
		header('Location:'.$furl);
    } else {
		if (empty($failed)) {
			pdo_insert('users_failed_login', array('ip' => CLIENT_IP, 'username' => $username, 'count' => '1', 'lastupdate' => TIMESTAMP));
		} else {
			pdo_update('users_failed_login', array('count' => $failed['count'] + 1, 'lastupdate' => TIMESTAMP), array('id' => $failed['id']));
		}
		message('登录失败，请检查您输入的用户名和密码！');
	}
}

