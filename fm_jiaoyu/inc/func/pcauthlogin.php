<?php

/**
 * By 高贵血迹
 */

global $_GPC, $_W;
$weid = $_GPC['i'];
$token = $_GPC['token'];
$openid = $_W['openid'];
$schoolid = $_GPC['schoolid'];
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if($operation == 'display'){
	include $this->template('common/pcauthlogin');
}

if($operation == 'login'){
	$logo  = pdo_fetch("SELECT wqgroupid FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
	$group = pdo_fetch("SELECT id,timelimit FROM " . tablename('users_group') . " WHERE id = :id", array(':id' => $logo['wqgroupid']));
    $timelimit = intval($group['timelimit']);
    $timeadd   = 0;
    if($timelimit > 0){
        $timeadd = strtotime($timelimit . ' days');
    }
	$user = pdo_fetch("SELECT * FROM " . tablename('wx_school_user') . " where weid = :weid And :schoolid = schoolid And :openid = openid And :sid = sid", array(':weid' => $weid,':schoolid' => $schoolid,':openid' => $openid,':sid' => 0));
	if(!empty($user)){
		$teainfo = pdo_fetch("SELECT id,tname,mobile,password FROM " . tablename('wx_school_teachers') . " where id = '{$user['tid']}' ");
		if($teainfo['mobile']){
			load()->model('user');
			$cheisbd = pdo_fetch("SELECT * FROM " . tablename('users') . " WHERE tid = :tid And schoolid = :schoolid", array(':tid' => $user['tid'],':schoolid' => $schoolid));
			if(!empty($cheisbd)){//已有账号
				if(!empty($teainfo['password'])){//已创建过扫码账号 
					pdo_update('wx_school_user', array('er_token' => $token), array('id' => $user['id']));
				}else{//需修改密码
					$tuid = $cheisbd['uid'];
					$userdata              = array();
					$userdata['uid']       = $tuid;
					$userdata['username']  = $cheisbd['username'];
					$userdata['schoolid']  = $schoolid;
					$userdata['password']  = $token;
					$userdata['remark']    = "微教育学校专用账户，不可以做其他操作，扫码自动生成";
					$userdata['tid']       = $user['tid'];
					$userdata['groupid']   = $logo['wqgroupid'];
					$userdata['starttime'] = TIMESTAMP;
					$userdata['endtime']   = $timeadd;
					$isup  = user_updates($userdata);
					if($isup != 0){
						unset($userdata['password']);
						$exists = pdo_fetch("SELECT * FROM " . tablename('uni_account_users') . " WHERE uid = :uid AND uniacid = :uniacid", array(':uniacid' => $weid, ':uid' => $tuid));
						if(empty($exists)){
							$data['role']    = 'clerk';
							$data['uid']     = $tuid;
							$data['uniacid'] = $weid;
							pdo_insert('uni_account_users', $data);
						}
					}
					pdo_update('wx_school_user', array('er_token' => $token), array('id' => $user['id']));
					pdo_update('wx_school_teachers', array('password' => $token), array('id' => $user['tid']));
				}
			}else{//需要创建新账号
				mload()->model('py_class');
				$py = new py_class();
				$username = $py->str2pys($teainfo['tname']).$schoolid;
				$userdata              = array();
				$userdata['username']  = $username;
				$userdata['schoolid']  = $schoolid;
				$userdata['password']  = $token;
				$userdata['remark']    = "微教育学校专用账户，不可以做其他操作，扫码自动生成";
				$userdata['tid']       = $user['tid'];
				$userdata['groupid']   = $logo['wqgroupid'];
				$userdata['starttime'] = TIMESTAMP;
				$userdata['endtime']   = $timeadd;
				$restuid = user_register($userdata,'');
				if($restuid > 0 && is_numeric($restuid)){
					unset($userdata['password']);
					$exists = pdo_fetch("SELECT * FROM " . tablename('uni_account_users') . " WHERE uid = :uid AND uniacid = :uniacid", array(':uniacid' => $weid, ':uid' => $restuid));
					if(empty($exists)){
						$data['role']    = 'clerk';
						$data['uid']     = $restuid;
						$data['uniacid'] = $weid;
						pdo_insert('uni_account_users', $data);
						$insert = array(
							'uniacid'    => $weid,
							'uid'        => $restuid,
							'type'       => 'fm_jiaoyu',
							'permission' => 'fm_jiaoyu_rule|fm_jiaoyu_menu_school',
						);
						pdo_insert('users_permission', $insert);
					}
				}
				pdo_update('wx_school_user', array('er_token' => $token), array('id' => $user['id']));
				pdo_update('wx_school_teachers', array('password' => $token), array('id' => $user['tid']));
			}
			$result['userinfo'] = true;
			$result['msg'] = '登录成功';
			$result['result'] = true;
		}else{
			$result['tid'] = $teainfo['id'];
			$result['tname'] = $teainfo['tname'];
			$result['mobile'] = $teainfo['mobile'];
			$result['msg'] = '请完善您的联系方式';
			$result['userinfo'] = false;
			$result['result'] = true;
		}
	}else{
		$result['msg'] = '抱歉，您尚未绑定老师';
		$result['result'] = false;
	}
	die(json_encode($result));
}

if($operation == 'edit_user'){
	$teainfo = pdo_fetch("SELECT id FROM " . tablename('wx_school_user') . " where tid = '{$_GPC['tid']}' ");
	if($teainfo){
		pdo_update('wx_school_user', array('mobile' => $_GPC['mobile']), array('id' => $teainfo['id']));
		pdo_update('wx_school_teachers', array('mobile' => $_GPC['mobile']), array('id' => $_GPC['tid']));
		$result['msg'] = '修改成功';
		$result['result'] = true;
	}else{
		$result['msg'] = '用户不存在';
		$result['result'] = false;
	}
	die(json_encode($result));
}

function user_updates($user) {
    if(empty($user) || !is_array($user)){
        return 0;
    }
    if(!$user['uid']){
        return 0;
    }
    $user['salt']      = random(8);
    $user['password']  = user_hashs($user['password'], $user['salt']);
    $user['joinip']    = CLIENT_IP;
    $user['joindate']  = TIMESTAMP;
    $user['lastip']    = CLIENT_IP;
    $user['lastvisit'] = TIMESTAMP;
    if(empty($user['status'])){
        $user['status'] = 2;
    }
    pdo_update('users', $user, array('uid' => intval($user['uid'])));

    return 1;
}
function user_hashs($passwordinput, $salt) {
	global $_W;
	$passwordinput = "{$passwordinput}-{$salt}-{$_W['config']['setting']['authkey']}";
	return sha1($passwordinput);
}