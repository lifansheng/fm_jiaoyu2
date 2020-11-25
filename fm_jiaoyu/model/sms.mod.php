<?php
/**
 * 微教育模块
 *QQ：332035136
 * @author 高贵血迹
 */
defined('IN_IA') or exit('Access Denied');
load()->func('communication');

function sms_send($mobile, $content, $sms_SignName, $sms_templte, $type, $weid, $schoolid) {
	if($sms_templte && $sms_SignName){
		include IA_ROOT.'/addons/fm_jiaoyu/inc/func/aliyun-php-sdk/aliyun-php-sdk-core/Config.php';
		include_once IA_ROOT.'/addons/fm_jiaoyu/inc/func/aliyun-php-sdk/Dysmsapi/Request/V20170525/SendSmsRequest.php';
		include_once IA_ROOT .'/addons/fm_jiaoyu/inc/func/aliyun-php-sdk/Dysmsapi/Request/V20170525/QuerySendDetailsRequest.php';    
		$bdset = get_weidset($weid,'sms_acss');
		$accessKeyId = $bdset['accessKeyId'];
		$accessKeySecret = $bdset['accessKeySecret'];
		//短信API产品名
		$product = "Dysmsapi";
		//短信API产品域名
		$domain = "dysmsapi.aliyuncs.com";
		//暂时不支持多Region
		$region = "cn-hangzhou";
		
		//初始化访问的acsCleint
		$profile = DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);
		DefaultProfile::addEndpoint("cn-hangzhou", "cn-hangzhou", $product, $domain);
		$acsClient= new DefaultAcsClient($profile);
		$request = new Dysmsapi\Request\V20170525\SendSmsRequest;
		//必填-短信接收号码
		$request->setPhoneNumbers($mobile);
		//必填-短信签名
		$request->setSignName($sms_SignName);
		//必填-短信模板Code
		$request->setTemplateCode($sms_templte);
		//选填-假如模板中存在变量需要替换则为必填(JSON格式)
		$request->setTemplateParam(json_encode($content));
		//选填-发送短信流水号
		//$request->setOutId("1234");
		
		//发起访问请求
		$acsResponse = $acsClient->getAcsResponse($request);
			
		//$result = @json_decode($acsResponse, true);
		$Code = ($acsResponse->Code);
		$Message = ($acsResponse->Message);
		if($Code == 'OK') {
			$status = 1;
			sms_insert_send_log($schoolid, $type, $mobile, $weid, $status, 'OK');
		}else{
			$status = 2;
			sms_insert_send_log($schoolid, $type, $mobile, $weid, $status, $Message);		
		}
		$data['Code'] = $Code;
		$data['Message'] = $Message;
		return $data;
	}
}

function sms_insert_send_log($schoolid, $type, $mobile, $weid, $status, $msg) {
	if(!empty($schoolid)){
		pdo_query('update ' . tablename('wx_school_index') . ' set sms_use_times = sms_use_times + 1 where id = :id', array(':id' => $schoolid));
		pdo_query('update ' . tablename('wx_school_index') . ' set sms_rest_times = sms_rest_times - 1 where id = :id', array(':id' => $schoolid));
	}else{
		pdo_query('update ' . tablename('wx_school_set') . ' set sms_use_times = sms_use_times + 1 where weid = :weid', array(':weid' => $weid));
	}
	$data = array(
		'weid' => $weid,
		'schoolid' => $schoolid,
		'type' => $type,
		'mobile' => $mobile,
		'sendtime' => TIMESTAMP,
	);
	if($status == 1){
		$data['status'] = 1;
		$data['msg'] = 'OK';
	}else{
		$data['status'] = 2;
		$data['msg'] = $msg;
	}	
	pdo_insert('wx_school_sms_log', $data);
	return true;
}

function sms_types($type) {
	$types = array(
		'code' 		=> '手机验证码',
		'bmshtz' 	=> '报名审核提醒',
		'fzqdshjg'  => '微信签到审核结果',
		'signshtz' 	=> '微信签到审核提醒',
		'bmshjgtz' 	=> '报名审核结果通知',
		'bjqshtz' 	=> '班级圈审核提醒',
		'bjqshjg' 	=> '班级圈审核结果通知',
		'zuoye' 	=> '作业群发通知',
		'bjtz' 		=> '班级通知',
		'xsqingjia' => '学生请假提醒',
		'xsqjsh'	=> '学生请假审核提醒',
		'liuyan' 	=> '家长或学生留言',
		'liuyanhf'  => '教师回复家长留言',
		'lyhf' 		=> '通讯录私聊',
		'jsqingjia' => '教师请假提醒',
		'jsqjsh' 	=> '教师请假结果提醒',
		'jxlxtx' 	=> '进校离校提醒',
	);
	return $types[$type];
}
function temp_types($type) {
	$types = array(
		'code' 		=> '手机验证码',
		'bmshtz' 	=> '报名审核提醒',
		'fzqdshjg'  => '微信签到审核结果',
		'signshtz' 	=> '微信签到审核提醒',
		'bmshjgtz' 	=> '报名审核结果通知',
		'bjqshtz' 	=> array('id' => 'OPENTM400047769'),
		'bjqshjg' 	=> array('id' => 'OPENTM400501478'),
		'zuoye' 	=> array('id' => 'OPENTM207873178'),
		'bjtz' 		=> array('id' => 'OPENTM204533457'),
		'xsqingjia' => array('id' => 'TM00190'),
		'xsqjsh'	=> array('id' => 'OPENTM200864357'),
		'liuyan' 	=> array('id' => 'OPENTM412814927'),
		'liuyanhf'  => array('id' => 'OPENTM412814927'),
		'lyhf' 		=> '通讯录私聊',
		'jsqingjia' => array('id' => 'OPENTM203328559'),
		'jsqjsh' 	=> array('id' => 'OPENTM207256255'),
		'jxlxtx' 	=> array('id' => 'TM00188'),
		'xxtongzhi' => array('id' => 'OPENTM204845041'),
		'jfjgtz'    => array('id' => 'OPENTM401619319'),
		'jthd'      => '集体活动通知',
		'rwtz'      => '任务通知',
		'xzxx'      => '校长信箱',
		'xysc'      => '成长手册',
		'qdqrtz'    => '微信代签通知',
		'kcpjtx'    => array('id' => 'OPENTM207330942'),
		'sykstx'    => array('id' => 'OPENTM405457608'),
		'kcyytx'    => array('id' => 'OPENTM400233342'),
		'kcqdtx'    => array('id' => 'OPENTM406123046'),
		'sktxls'    => array('id' => 'OPENTM206931431'),
        'fkyytx'    => array('id' => 'OPENTM413280281'),
		'kcjstz'    => array('id' => 'AT1817'),
		'pttz'    	=> array('id' => 'OPENTM411478656'),
		'zltz'    	=> array('id' => 'OPENTM414103384'),
		'dkcgtz'    => array('id' => 'OPENTM415747686'),
		'twcltz'    => array('id' => 'OPENTM207622303'),
		'jktxtz'    => array('id' => 'OPENTM401671866'),
	);
	return $types[$type];
}
function getBasicset($weid,$schoolid){
	$school = pdo_fetch("SELECT logo,title,issale,tel,address,lat,lng FROM " . tablename('wx_school_index') . " WHERE id = '{$schoolid}'");
	if($school){
		$stumub = pdo_fetchcolumn("select COUNT(id) FROM ".tablename('wx_school_students')." WHERE schoolid = '{$schoolid}' ");
		$teamub = pdo_fetchcolumn("select COUNT(id) FROM ".tablename('wx_school_teachers')." WHERE schoolid = '{$schoolid}' ");
		$wxmub = pdo_fetchcolumn("select COUNT(id) FROM ".tablename('wx_school_user')." WHERE schoolid = '{$schoolid}' ");
		$kcmub = pdo_fetchcolumn("select COUNT(id) FROM ".tablename('wx_school_tcourse')." WHERE schoolid = '{$schoolid}' ");
		$macmub = pdo_fetchcolumn("select COUNT(id) FROM ".tablename('wx_school_checkmac')." WHERE schoolid = '{$schoolid}' ");
		$checklog = pdo_fetchcolumn("select COUNT(id) FROM ".tablename('wx_school_checklog')." WHERE schoolid = '{$schoolid}' ");
		$cost = pdo_fetchall('SELECT SUM(cose) FROM ' . tablename('wx_school_order') . " WHERE schoolid = '{$schoolid}' AND status = 2 ");
		$paymub = $cost[0]['SUM(cose)'];
		$data = array(
			'weid' => $weid,
			'schoolid' => $school['id'],
			'title' => $school['title'],
			'logo' => tomedia($school['logo']),
			'tel' => $school['tel'],
			'address' => $school['address'],
			'schooltype' => $school['issale'],
			'lat' => $school['lat'],
			'lng' => $school['lng'],
			'stumub' => $stumub,
			'teamub' => $teamub,
			'wxmub' => $wxmub,
			'kcmub' => $kcmub,
			'paymub' => $paymub,
			'macmub' => $macmub,
			'checklog' => $checklog,
			'modelname' => 'fm_jiaoyu'
		);
	}
	return $data;
}
