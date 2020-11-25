<?php
/**
 * By 高贵血迹
 */

global $_GPC, $_W;
$operation = in_array ( $_GPC ['op'], array ('default','info', 'login', 'rtc','postVideo') ) ? $_GPC ['op'] : 'default';
$weid      = $_GPC['i'];
$schoolid  = $_GPC['schoolid'];

$school    = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}' ");

if ($operation == 'default') {
	$result['status'] = -1;
	$result['msg']    = "对不起，你的请求不存在！";
	echo json_encode($result);
	exit;
}
if(empty($school)) {
	$result['status'] = -1;
	$result['msg']    = "找不到本校，设备未关联学校?";
	echo json_encode($result);
	exit;
}

if (empty($_W['setting']['remote']['type'])) {
	$urls = $_W['SITEROOT'].$_W['config']['upload']['attachdir'].'/';
} else {
	$urls = $_W['attachurl'];
}
if($operation == 'postVideo'){
    /**极光推送 start**/
	$UnionID = isset($_GPC['UnionID']) ? $_GPC['UnionID'] : "";
	$param = '{"messageId":"' . $_GPC['messageId'] . '","studentId":"' . $_GPC['studentId'] . '","UnionID":"' . $_GPC['UnionID'] . '","deviceId":"' . $_GPC['deviceId'] . '","cardCode":"' . $_GPC['cardCode'] . '"}';
	$jpush_client_url = IA_ROOT . '/addons/fm_jiaoyu/inc/func//jpush/jpush/autoload.php';
	require $jpush_client_url;
	$app_key = '578206afb439ef345fd694c6';
	$master_secret = '47a2bf5dfa20f721f3ea44c5';
	$client = new JPush\Client($app_key, $master_secret);
	try {
		$rs = $client->push()
			->setPlatform(array('ios', 'android'))
			->message($param)
			//->addAlias('28a57998ea4f5038')
			->addAlias($UnionID)
			->send();
	} catch (JPush\Exceptions\APIConnectionException $e) {
		// try something here
		print $e;
} catch (JPush\Exceptions\APIRequestException $e) {
    // try something here
    print $e;
}                
        /**极光推送结束*/
          
}
if ($operation == 'info') {
	if(!empty($school)) {
		$result['status'] = 0;
		$result['msg'] = "获取数据成功";
		$result['data'] = array(
				'loginUrl'   => $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkdy&op=login&m=fm_jiaoyu',
				'RTC'    => $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkdy&op=rtc&m=fm_jiaoyu',
			);
		$result['servertime'] = time();
		echo json_encode($result);
		exit;
	}
}
if ($operation == 'login') {
	//获取设备信息
	$username=urldecode($_GPC['userName']);
	
	$password=$_GPC['passWord'];
	
	$student = pdo_fetch("SELECT id,s_name,bj_id,code FROM " . tablename($this->table_students) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' And code = '{$password}' and s_name='{$username}' " );
	
	if(empty($student)) {
		$result['status'] = -1;
		$result['msg']    = "用户名或者密码输入错误";
		echo json_encode($result);
		exit;
	}
	
	$brcard = pdo_fetch("SELECT  idcard  FROM " . tablename($this->table_idcard) . " WHERE sid = '{$student['id']}' and pard=1 ");
	
	
	$class = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'theclass' And schoolid = {$schoolid} And sid = {$student['bj_id']} ");
	
	$ckmac = pdo_fetch("SELECT macid FROM " . tablename($this->table_classcard_mac) . " WHERE bj_id = '{$student['bj_id']}' And weid = '{$weid}' And schoolid = '{$schoolid}' ");
	
	$family=pdo_fetchall("SELECT * FROM " . tablename($this->table_user) . " where  weid = :weid And  schoolid = :schoolid And sid = :sid ", array(':weid' => $weid, ':schoolid' => $schoolid,'sid'=>$student['id']));
	
	$data=array();
	
	$fam=array();
	
	foreach($family as $key=>$row){
		
		$fam[$key]['UnionID']=$row['id'];
		
		if($row['pard']=='2') {

			$fam[$key]['UnionIDname'] = '妈妈';

		} elseif($row['pard']=='3') {

			$fam[$key]['UnionIDname'] = '爸爸';

		} elseif($row['pard']=='4') {

			$fam[$key]['UnionIDname'] = '爷爷';

		} elseif($row['pard']=='5') {

			$fam[$key]['UnionIDname'] = '奶奶';

		} elseif($row['pard']=='6') {

			$fam[$key]['UnionIDname'] = '外公';

		} elseif($row['pard']=='7') {

			$fam[$key]['UnionIDname'] = '外婆';

		} elseif($row['pard']=='8') {

			$fam[$key]['UnionIDname'] = '叔叔';

		} elseif($row['pard']=='9') {

			$fam[$key]['UnionIDname'] = '阿姨';

		} elseif($row['pard']=='10') {

			$fam[$key]['UnionIDname'] = '其他';

		}
		
		}
	$data['family']=$fam;
	$data['studentId']=$student['id'];
	$data['studentName']=$student['s_name'];
	$data['cardCode']=$brcard['idcard'];
	$data['classId']=$student['bj_id'];
	$data['className']=$class['sname'];
	$data['deviceId']=$ckmac['macid'];
	
	$result['status'] = 0;
	$result['msg'] = "获取数据成功";
	
	$result['data'] = $data;
	$result['servertime'] = time();
	echo json_encode($result);
	exit;
}

if ($operation == 'rtc') {
	
	include_once "TLSSigAPIv2.php";
	
	$classcardset=pdo_fetch("SELECT * FROM " . tablename($this->table_classcard_set) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' ");
	 
	
	$appid=$classcardset['tappId'];
	
	$appkey=$classcardset['tappKey'];
	
	$api = new \Tencent\TLSSigAPIv2($appid,$appkey);
	
	$user = $_GPC['user'];
	
	$sig = $api->genSig($user);
	
	$data['appid']=$appid;
	
	$data['UserSig']=$sig;
	
	$result['status'] = 0;
	$result['msg'] = "获取数据成功";
	
	$result['data']= $data;
	$result['servertime'] = time();
	echo json_encode($result);
	exit;
	
}
/*if ($operation == 'rtc') {
	
	$channel_id = $_GPC['room'];
	
	$user = $_GPC['user'];
	
	$classcardset=pdo_fetch("SELECT * FROM " . tablename($this->table_classcard_set) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' ");
	
	$listen = 8080;

	$app_id = $classcardset['appId']; 
	
	$app_key = $classcardset['appKey']; 
	
	$gslb = 'https://rgslb.rtc.aliyuncs.com';
	
	$user_id = CreateUserID($channel_id, $user);
	
	$nonce = 'AK-' . uniqid();
	
	$timestamp = strtotime(date('Y-m-d H:i:s', strtotime('+2day')));
	
	$token = CreateToken($app_id, $app_key, $channel_id, $user_id, $nonce, $timestamp);
	
	$username = $user_id . '?appid=' . $app_id . '&channel=' . $channel_id . '&nonce=' . $nonce . '&timestamp=' . $timestamp;
	
	error_log('Login: appID=' . $app_id . ', appKey=' . $app_key . ', channelID='
			. $channel_id . ', userID=' . $user_id . ', nonce=' . $nonce . ', timestamp='
			. $timestamp . ', user=' . $user . ', userName=' . $username . ', token=' . $token);
	
	echo json_encode(array(
		'code' => 0,
		'data' => array(
			'appid' => $app_id,
			'userid' => $user_id,
			'gslb' => array($gslb),
			'token' => $token,
			'nonce' => $nonce,
			'timestamp' => $timestamp,
			'turn' => array(
				'username' => $username,
				'password' => $token
			)
		)
	));
	
}


function CreateUserID($channel_id, $user)
{
	$s = $channel_id . '/' . $user;
	$uid = hash('sha256', $s);
	return substr($uid, 0, 16);
}

function CreateToken($app_id, $app_key, $channel_id, $user_id, $nonce, $timestamp)
{
	$s = $app_id . $app_key . $channel_id . $user_id . $nonce . $timestamp;
	$token = hash('sha256', $s);
	return $token;
}*/
