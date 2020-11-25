<?php

/**
 * By 高贵血迹
 */
include_once 'aliyun-php-sdk-core/Config.php';

use Sts\Request\V20150401 as Sts;

global $_GPC, $_W;
$operation = in_array($_GPC['op'], array('default', 'login', 'class', 'check', 'gps', 'banner', 'video', 'start', 'notice', 'users', 'getdate', 'getleave', 'command', 'getdevremote', 'checkap', 'getroomlist', 'course', 'checkforks', 'token')) ? $_GPC['op'] : 'default';
$weid      = $_GPC['i'];
$schoolid  = $_GPC['schoolid'];
$macid     = empty($_GPC['macid']) ? $_GPC['deviceId'] : $_GPC['macid'];
$ckmac     = pdo_fetch("SELECT * FROM " . tablename($this->table_checkmac) . " WHERE macid = '{$macid}' And weid = '{$weid}' And schoolid = '{$schoolid}' ");
$school    = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}' ");
$xk_type = pdo_fetch("SELECT * FROM " . GetTableName('schoolset') . " WHERE schoolid = '{$schoolid}' ");
$school['xk_type'] = $xk_type['xk_type'];
if ($operation == 'default') {
	$result['status'] = -1;
	$result['msg']    = "对不起，你的请求不存在！";
	echo json_encode($result);
	exit;
}
if (empty($school)) {
	$result['status'] = -1;
	$result['msg']    = "找不到本校，设备未关联学校?";
	echo json_encode($result);
	exit;
}
if (empty($ckmac)) {
	$result['status'] = -1;
	$result['msg']    = "没找到设备,请添加设备";
	echo json_encode($result);
	exit;
}
if ($school['is_recordmac'] == 2) {
	$result['status'] = -1;
	$result['msg']    = "本校无权使用设备,请联系管理员";
	echo json_encode($result);
	exit;
}
if ($ckmac['is_on'] == 2) {
	$result['status'] = -1;
	$result['msg'] = "本设备已关闭,请联系管理员";
	echo json_encode($result);
	exit;
}
if (empty($_W['setting']['remote']['type'])) {
	$urls = $_W['SITEROOT'] . $_W['config']['upload']['attachdir'] . '/';
} else {
	$urls = $_W['attachurl'];
}
if ($operation == 'notice') { //getNotice
	if (!empty($school)) {
		$banner = unserialize($ckmac['banner']);
		$result['status'] = 0;
		$result['msg'] = "获取数据成功";
		$result['data'] = array(
			/*'mactag'=>$ckmac['name'],
				'welcome'=>$banner['welcome'],*/
			'hosturl' => getoauthurl(),
			'getDeviceInfo'  => $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkwn&op=login&m=fm_jiaoyu',
			'getClassInfo'   => $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkwn&op=class&m=fm_jiaoyu',
			'getStudentInfo' => $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkwn&op=users&m=fm_jiaoyu',
			'postUrl'        => $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkwn&op=check&m=fm_jiaoyu',
			'postUrl2' => '',
			'leaveUrl'       => $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkwn&op=getleave&m=fm_jiaoyu',
			'detectionUrl' => '',
			'gpsUrl' => $_W['siteroot'] . '',
			'commandUrl'     => $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkwn&op=command&m=fm_jiaoyu',

			/*'consumptionurl'=>'',
				'brandurl'=>'',
                'getschoolinfo'  => $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkwn&op=login&m=fm_jiaoyu',*/
			'postMoneyUrl' => '',
			'getMoneyUrl' => '',
			'deviceSetUrl'   => $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkwn&op=getdevremote&m=fm_jiaoyu',
			'getAttendanceLogUrl' => '',
			'getConsumptionLogUrl' => '',
			'getroomlisturl' => $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkxz&op=getroomlist&m=fm_jiaoyu',
			'courseUrl'      => $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkwn&op=course&m=fm_jiaoyu',
			'postCourseUrl'      => $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkwn&op=checkforks&m=fm_jiaoyu',
			'getAliossTokenUrl' => $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkwn&op=token&m=fm_jiaoyu',
			/*'outTimeUrl'     => $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkwn&op=class&m=fm_jiaoyu',
                'checkapUrl'     => $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkwn&op=checkap&m=fm_jiaoyu',
                'getroomlisturl' => $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkxz&op=getroomlist&m=fm_jiaoyu',
				
				'posturl1'=>'',
				'deviceType'=>$ckmac['apid']?11:$ckmac['is_bobao'],*/
		);
		$result['servertime'] = time();
		echo json_encode($result);
		exit;
	}
}
if ($operation == 'users') { //getstatus获取学生信息
	if (!empty($ckmac)) {
		$users = pdo_fetchall("SELECT id,idcard, sid, bj_id, usertype,spic,tid,cardtype FROM " . tablename($this->table_idcard) . " WHERE weid = '{$weid}' And schoolid = {$school['id']} And is_on = 1 ORDER BY id DESC");
		if ($users) {
			$result['status'] = 0;
			$result['msg'] = "获取数据成功";
			$result['countpage'] = "1";
			foreach ($users as $key => $row) {
				$users[$key]['cardStatus'] = 0;
				if ($row['usertype'] == 1) {
					$teacher = pdo_fetch("SELECT tname,thumb,sex  FROM " . tablename($this->table_teachers) . " WHERE id = '{$row['tid']}' ");
					$users[$key]['relationship'] = '';
					$users[$key]['fingercards'] = array();
					$users[$key]['car_cards'] = array();
					//$users[$key]['id']           = "02".$row['tid'];
					$users[$key]['sex']          = $teacher['sex'];
					$users[$key]['name']         = $teacher['tname'];
					$users[$key]['cardCode']       = $row['idcard'];

					$users[$key]['type']         = 2;
					$users[$key]['faceUrl']       = empty($teacher['thumb']) ? tomedia($school['tpic'], false, true) : tomedia($teacher['thumb'], false, true);
				} elseif ($row['usertype'] == 0) {
					$student = pdo_fetch("SELECT  id,s_name,icon,numberid,sex,s_type  FROM " . tablename($this->table_students) . " WHERE id = '{$row['sid']}' ");

					$users[$key]['cardCode']  = $row['idcard'];
					$users[$key]['studentId'] = $student['id'];
					$users[$key]['type']         = 1;
					$users[$key]['studentType'] = $student['s_type'];
					if ($row['spic']) {
						$picrul = tomedia($row['spic']);
					} else {
						$picrul = empty($student['icon']) ? tomedia($school['spic'], false, true) : tomedia($student['icon'], false, true); //未设置头像，取默认头像
					}
					$users[$key]['faceUrl']       = $picrul;
					$users[$key]['classId'] = $row['bj_id'];
					$users[$key]['sex']     = $student['sex'];
					$users[$key]['name'] = $student['s_name'];
					$users[$key]['car_cards'] = array();

					$relation = pdo_fetch("SELECT  pard,idcard  FROM " . tablename($this->table_idcard) . " WHERE idcard = '{$row['idcard']}' ");
					if ($relation['pard'] == '1') {
						$users[$key]['relationship'] = '';
					} elseif ($relation['pard'] == '2') {
						$users[$key]['relationship'] = '母亲';
					} elseif ($relation['pard'] == '3') {
						$users[$key]['relationship'] = '父亲';
					} elseif ($relation['pard'] == '4') {
						$users[$key]['relationship'] = '爷爷';
					} elseif ($relation['pard'] == '5') {
						$users[$key]['relationship'] = '奶奶';
					} elseif ($relation['pard'] == '6') {
						$users[$key]['relationship'] = '外公';
					} elseif ($relation['pard'] == '7') {
						$users[$key]['relationship'] = '外婆';
					} elseif ($relation['pard'] == '8') {
						$users[$key]['relationship'] = '叔叔';
					} elseif ($relation['pard'] == '9') {
						$users[$key]['relationship'] = '阿姨';
					} elseif ($relation['pard'] == '10') {
						$users[$key]['relationship'] = '其他';
					}
					$users[$key]['fingerCards'] = array();
					$users[$key]['carCards'] = array();
					$users[$key]['IdCard'] = '';
					//$users[$key]['id']      = $row['sid'];

					unset($users[$key]['spic']);
					$studentidcard = pdo_fetch("SELECT idcard  FROM " . tablename($this->table_idcard) . " WHERE sid = '{$row['sid']}' ");
				} elseif ($row['cardtype'] == 2 && $row['usertype'] == 0) {

					$users[$key]['classId'] = $row['bj_id'];
					$users[$key]['name'] = '班级卡';
					$users[$key]['type'] = 1;
					$users[$key]['relationship'] = '';
					$users[$key]['fingercards'] = array();
					$users[$key]['car_cards'] = array();

					//$users[$key]['id']      = $row['sid'];
					$users[$key]['sex']     = 1;
					$users[$key]['cardCode']  = $row['idcard'];
					$users[$key]['type']         = 1;
					if ($row['spic']) {
						$picrul = tomedia($row['spic'], false, true);
					} else {
						$picrul = empty($student['icon']) ? tomedia($school['spic'], false, true) : tomedia($student['icon'], false, true); //未设置头像，取默认头像
					}
					$users[$key]['faceUrl']       = $picrul;
				}
				unset($users[$key]['usertype']);
				unset($users[$key]['cardtype']);
				unset($users[$key]['sid']);
				unset($users[$key]['tid']);
				unset($users[$key]['bj_id']);
				unset($users[$key]['idcard']);
				unset($users[$key]['spic']);
			}
			$result['data'] = $users;
			$result['servertime'] = time();
		} else {
			$result['status'] = -1;
			$result['msg']    = "没有有效考勤卡信息";
		}
		echo json_encode($result);
		exit;
	}
}
if ($operation == 'class') { //获取班级信息
	if (!empty($ckmac)) {
		$class = pdo_fetchall("SELECT sid as classId, sname as className, class_device as classDevice, schoolid as sid, ssort as score, tid,datesetid FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'theclass' And schoolid = {$school['id']} ORDER BY ssort DESC");
		if ($class) {
			$nowdate = date("Y-n-j", time());
			$nowyear = date("Y", time());
			$nowweek = date("w", time()); //今天是星期几
			$result['status'] = 0;
			$result['msg'] = "获取班级数据成功";
			foreach ($class as $key => $row) {
				$todayType = 0;
				$todayTimeSet = array(
					array(
						'start' => '00:00',
						'end'  => '23:59'
					),
				);
				if (!empty($row['datesetid'])) {
					$checkdateset      =  pdo_fetch("SELECT * FROM " . tablename($this->table_checkdateset) . " WHERE weid = '{$weid}' And schoolid = {$school['id']} and  id = '{$row['datesetid']}'");
					$checkdateset_holi =  pdo_fetch("SELECT * FROM " . tablename($this->table_checkdatedetail) . " WHERE weid = '{$weid}' And schoolid = {$school['id']} and  checkdatesetid = '{$row['datesetid']}' and year = '{$nowyear}' ");

					$checktime         =  pdo_fetchall("SELECT * FROM " . tablename($this->table_checktimeset) . " WHERE weid = '{$weid}' And schoolid = {$school['id']} and  checkdatesetid = '{$row['datesetid']}' and date = '{$nowdate}' ORDER BY id ASC ");
					if (!empty($checktime)) {
						if ($checktime[0]['type'] == 6) {
							//1放假2上课
							$todayType = 1;
						} elseif ($checktime[0]['type'] == 5) {
							$todayType    = 2;
							$todayTimeSet = $checktime;
						}
					} else {
						if ((strtotime($nowdate) >= strtotime($checkdateset_holi['win_start']) && strtotime($nowdate) <= strtotime($checkdateset_holi['win_end'])) || (strtotime($nowdate) >= strtotime($checkdateset_holi['sum_start']) && strtotime($nowdate) <= strtotime($checkdateset_holi['sum_end']))) {
							$todayType = 1;
						} else {
							$timeset_work = pdo_fetchall("SELECT start,end,s_type,out_in FROM " . tablename($this->table_checktimeset) . " WHERE weid = '{$weid}' And schoolid = {$school['id']} and  checkdatesetid = '{$row['datesetid']}' and type=1 ORDER BY id ASC ");
							//星期五
							if ($nowweek == 5) {
								$todayType = 2;
								if ($checkdateset['friday'] == 1) {
									$timeset_fri = pdo_fetchall("SELECT start,end,s_type,out_in FROM " . tablename($this->table_checktimeset) . " WHERE weid = '{$weid}' And schoolid = {$school['id']} and  checkdatesetid = '{$row['datesetid']}' and type=2 ORDER BY id ASC ");
									$todayTimeSet = $timeset_fri;
								} else {
									$todayTimeSet = $timeset_work;
								}
								//星期六
							} elseif ($nowweek == 6) {
								if ($checkdateset['saturday'] == 1) {
									$timeset_sat = pdo_fetchall("SELECT start,end,s_type,out_in FROM " . tablename($this->table_checktimeset) . " WHERE weid = '{$weid}' And schoolid = {$school['id']} and  checkdatesetid = '{$row['datesetid']}' and type=3 ORDER BY id ASC ");
									$todayType = 2;
									$todayTimeSet = $timeset_sat;
								} else {
									$todayType = 1;
								}

								//星期天
							} elseif ($nowweek == 0) {
								if ($checkdateset['sunday'] == 1) {
									$timeset_sun = pdo_fetchall("SELECT start,end,s_type,out_in FROM " . tablename($this->table_checktimeset) . " WHERE weid = '{$weid}' And schoolid = {$school['id']} and  checkdatesetid = '{$row['datesetid']}' and type=4 ORDER BY id ASC ");
									$todayType    = 2;
									$todayTimeSet = $timeset_sun;
								} else {
									$todayType    = 1;
								}
								//工作日	
							} else {
								$todayType    = 2;
								$todayTimeSet = $timeset_work;
							}
						}
					}
				}
				if (!empty($ckmac['apid'])) {
					$todayType = 0;
					$todayTimeSet = array(
						array(
							'start' => '00:00',
							'end'  => '23:59'
						),
					);
				}
				//$class[$key]['outDoorTime'] = array();
				$class[$key]['todayType']    = $todayType;
				$class[$key]['todayTimeSet'] = $todayTimeSet;
				$class[$key]['studentType'] = '';
				$class[$key]['outIn'] = '';
				unset($class[$key]['sid']);
				unset($class[$key]['datesetid']);
				unset($class[$key]['tid']);
				unset($class[$key]['score']);
			}
			$result['data'] = $class;
			$result['servertime'] = time();
		} else {
			$result['status'] = -1;
			$result['msg'] = "本校未添加班级信息";
		}
		echo json_encode($result);
	}
}
if ($operation == 'login') { //获取学校信息
	$voice = '';
	if (!empty($ckmac)) {
		$result['status'] = 0;
		$result['msg'] = "获取数据成功";
		if ($ckmac['banner']) {
			$banner = unserialize($ckmac['banner']);
			if ($banner['pic1']) {
				$pictures = array(tomedia($banner['pic1']));
			}
			if ($banner['pic1'] && $banner['pic2']) {
				$pictures = array(tomedia($banner['pic1']), tomedia($banner['pic2']));
			}
			if ($banner['pic1'] && $banner['pic2'] && $banner['pic3']) {
				$pictures = array(tomedia($banner['pic1']), tomedia($banner['pic2']), tomedia($banner['pic3']));
			}
			if ($banner['pic1'] && $banner['pic2'] && $banner['pic3'] && $banner['pic4']) {
				$pictures = array(tomedia($banner['pic1']), tomedia($banner['pic2']), tomedia($banner['pic3']), tomedia($banner['pic4']));
			}
			if ($banner['pic1'] && $banner['pic2'] && $banner['pic3'] && $banner['pic4']  && $banner['pic5']) {
				$pictures = array(tomedia($banner['pic1']), tomedia($banner['pic2']), tomedia($banner['pic3']), tomedia($banner['pic4']), tomedia($banner['pic5']));
			}
			if ($banner['VOICEPRE2']) {
				$voice = $banner['VOICEPRE2'];
			}
			$result['data']['position3']['picture'] = $pictures;
			$temp = array(
				'isflow' => 2,
				'pop'  	 	=> $banner['pop'],
				'bgimg'  	=> $banner['bgimg'],
				'qrcode'	=> $banner['qrcode'],
				'video' 	=> $banner['video'],
				'pic1'  	=> $banner['pic1'],
				'pic2'  	=> $banner['pic2'],
				'pic3'   	=> $banner['pic3'],
				'pic4'  	=> $banner['pic4'],
				'pic5'   	=> $banner['pic5'],
				'welcome'      => $banner['welcome'],
				'password'      => $banner['password'],
				'starttime'      => $banner['starttime'],
				'shutdowntime'      => $banner['shutdowntime'],
				'voice'			=> $banner['voice'],
				'VOICEPRE'	=> $banner['VOICEPRE'],
				'VOICEPRE2'	=> $banner['VOICEPRE2']
			);
			$data['banner'] = serialize($temp);
			pdo_update($this->table_checkmac, $data, array('id' => $ckmac['id']));
		}

		$classcardset = pdo_fetch("SELECT * FROM " . tablename($this->table_classcard_set) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' ");
		$result['data'] = array(
			'schoolName'     => $school['title'],
			'schoolLogo'     => tomedia($school['logo']),
			'deviceTag'     => $ckmac['name'],
			'welcome' => $banner['welcome'],
			'banner'   => array(tomedia($banner['pic1']), tomedia($banner['pic2']), tomedia($banner['pic3']), tomedia($banner['pic4'])),
			'video'    => $banner['video'],
			'info'     => $banner['pop'],
			//'da_start' => array("1", "2", "3", "4", "5", "6", "7"),
			'classVoice'    => $banner['voice'],
			'voice'    => $banner['newvoice'],
			'password' => $banner['password'],
			'startTime' => $banner['starttime'],
			'shutdownTime' => $banner['shutdowntime'],
			'endpoint'     => $classcardset['endpoint'],
			'bucket'     => $classcardset['bucket'],
			'accessKeyID'     => $classcardset['accessKeyID'],
			'accessKeySecret'     => $classcardset['accessKeySecret'],

			//'address'  => $school['address'],

			'stu1' => $ckmac['stu1'],
			'stu2' => $ckmac['stu2'],
			'stu3' => $ckmac['stu3'],
			'posMaxNumber'     => '',
			'posMaxMoney'     => ''
		);
		$result['servertime'] = time();
		echo json_encode($result);
		exit;
	}
}

if ($operation == 'command') {
	if (!empty($ckmac)) {
		$order = pdo_fetch("SELECT * FROM " . tablename($this->table_online) . " WHERE :macid = macid And result = 2", array(':macid' => trim($ckmac['id'])));
		if ($order) {
			$result['status'] = 0;
			$result['msg'] = '请求成功';
			$result['data']['command'] = $order['commond'];
		} else {
			$result['status'] = 0;
			$result['msg'] = '请求成功';
			$result['data']['command'] = 0;
		}
		$result['servertime'] = time();
	}
	if ($order) {
		pdo_update($this->table_online, array('result' => 1), array('id' => $order['id']));
	}
	echo json_encode($result);

	exit;
}

if ($operation == 'start') {
}
if ($operation == 'gps') {
	$result['status'] = 0;
	$result['msg']    = "定位上传成功";
	$result['data']   = array();
	echo json_encode($result);
	exit;
}

if ($operation == 'getdate') {
	$result['status'] = "0";
	$result['msg']    = "获取数据成功";
	$result['data'] = array(
		'da_start' => array(1, 2, 3, 4, 5, 6, 7)
	);
	echo json_encode($result);
	exit;
}
if ($operation == 'check') { //刷卡操作
	$fstype   = false;
	$signTime = $_GPC['time'];
	$ckuser = pdo_fetch("SELECT * FROM " . tablename($this->table_idcard) . " WHERE idcard = '{$_GPC['cardCode']}' And weid = '{$weid}' And schoolid = '{$schoolid}' ");
	$bj     = pdo_fetch("SELECT bj_id FROM " . tablename($this->table_students) . " WHERE id = '{$ckuser['sid']}' ");
	$checkthisdata = pdo_fetch("SELECT id FROM " . tablename($this->table_checklog) . " WHERE cardid = '{$_GPC['cardCode']}' And createtime = '{$signTime}' And schoolid = '{$schoolid}' ");
	$pictureUrls = $_GPC['pictureUrls'];
	$pictureDatas = $_GPC['pictureDatas'];


	if (empty($checkthisdata)) {
		if (!empty($ckuser)) {
			$times = time();
			if (!empty($pictureUrls)) {

				$pic = urldecode($pictureUrls[0]);

				$pic2 = urldecode($pictureUrls[1]);
			} elseif (!empty($pictureDatas)) {
				load()->func('file');
				load()->func('communication');
				$path = "images/fm_jiaoyu/checkwn/" . date('Y/m/d/');
				$rand = random(30);
				if (!empty($_GPC['pictureDatas'][0])) {
					$picurl = $path . $rand . "_1.jpg";
					$files_image = base64_decode($_GPC['headerimg']);
					file_write($picurl, $files_image);
					if (!empty($_W['setting']['remote']['type'])) {
						file_remote_upload($picurl);
					}
					$pic = $picurl;
				}
				if (!empty($_GPC['pictureDatas'][1])) {
					$picurl2 = $path . $rand . "_2.jpg";
					$files_image2 = base64_decode($_GPC['headerimg_second']);
					file_write($picurl2, $files_image2);
					if (!empty($_W['setting']['remote']['type'])) {
						file_remote_upload($picurl2);
					}
					$pic2 = $picurl2;
				}
			}
			$nowtime = date('H:i', $signTime);
			if ($ckmac['type'] != 0) {
				include 'checktime2.php';
			} else {
				$signMode = $_GPC['stateType'];
				include 'checktime.php';
			}

			if ($ckuser['cardtype'] == 1) {
				//学生卡或者老师卡
				if (!empty($ckuser['sid'])) {
					//如果是学生
					$roominfo = pdo_fetch("SELECT roomid,isopen FROM " . tablename($this->table_students) . " WHERE id = '{$ckuser['sid']}' ");
					if ($school['is_cardpay'] == 1) {
						if (!empty($ckmac['apid'])) {
							//如果是宿舍考勤机
							if (!empty($roominfo['roomid'])) {
								$this_roomid = $roominfo['roomid'];
								$this_apid   = $ckmac['apid'];
							} else {
								$this_roomid = 0;
								$this_apid = 0;
							}
							if ($leixing == 1) {
								$ap_type = 1;
							} elseif ($leixing == 2) {
								$ap_type = 2;
							} else {
								$ap_type = 0;
							}
							$data = array(
								'weid'       => $weid,
								'schoolid'   => $schoolid,
								'macid'      => $ckmac['id'],
								'cardid'     => $_GPC['cardCode'],
								'sid'        => $ckuser['sid'],
								'bj_id'      => $bj['bj_id'],
								'lon'        => $_GPC['longitude'],
								'lat'        => $_GPC['latitude'],
								'pic'        => $pic,
								'pic2'       => $pic2,
								'sc_ap'      => 1,
								'ap_type'    => $ap_type,
								'roomid'     => $this_roomid,
								'apid'       => $this_apid,
								'createtime' => $signTime,
								'surestatus'  => $xk_type['is_sure_kq'] == 1 ? 0 : 1,
							);
						} else { //如果不是宿舍考勤机
							$data = array(
								'weid'        => $weid,
								'schoolid'    => $schoolid,
								'macid'       => $ckmac['id'],
								'cardid'      => $_GPC['cardCode'],
								'sid'         => $ckuser['sid'],
								'bj_id'       => $bj['bj_id'],
								'lon'         => $_GPC['longitude'],
								'lat'         => $_GPC['latitude'],
								'type'        => $type,
								'pic'         => $pic,
								'pic2'        => $pic2,
								'temperature' => $_GPC['signTemp'],
								'leixing'     => $leixing,
								'pard'        => $ckuser['pard'],
								'createtime'  => $signTime,
								'surestatus'  => $xk_type['is_sure_kq'] == 1 ? 0 : 1,
							);
							if ($school['xk_type'] == '1') {
								mload()->model('kc');
								$back = GetnearksBySid($schoolid, $weid, $ckuser['sid'], $signTime, true);
							}
							if (!empty($back['sendMsgArr'])) {
								foreach ($back['sendMsgArr'] as $row) {
									$this->sendMobileXsqrqdtz($row, $schoolid, $weid);
								}
							}
						}
						pdo_insert($this->table_checklog, $data);
						$checkid = pdo_insertid();
						if (!empty($_GPC['signTemp'])) {
							$this->sendMobileTwtz($ckuser['sid'], $_GPC['signTemp'], 1, $_W['schooltype'], $checkid);
						}
						if ($ckuser['severend'] > $times  && $roominfo['isopen'] == 1) {
							if ($school['send_overtime'] >= 1) {
								$overtime = $school['send_overtime'] * 60;
								$timecha  = $times - $signTime;
								if ($overtime >= $timecha) {
									if (is_showyl()) {
										$this->sendMobileJxlxtz_yl($schoolid, $weid, $ckuser['sid'], $checkid, $ckmac['id']);
									} else {
										$this->sendMobileJxlxtz($schoolid, $weid, $bj['bj_id'], $ckuser['sid'], $type, $leixing, $checkid, $ckuser['pard']);
									}
								}
							} else {
								if (is_showyl()) {
									$this->sendMobileJxlxtz_yl($schoolid, $weid, $ckuser['sid'], $checkid, $ckmac['id']);
								} else {
									$this->sendMobileJxlxtz($schoolid, $weid, $bj['bj_id'], $ckuser['sid'], $type, $leixing, $checkid, $ckuser['pard']);
								}
							}
							$fstype = true;
						} else {
							$fstype        = false;
							$result['msg'] = "刷卡失败,本卡已过有效期";
						}
					} else {

						if (!empty($ckmac['apid'])) {
							if (!empty($roominfo['roomid'])) {
								$this_roomid = $roominfo['roomid'];
								$this_apid   = $ckmac['apid'];
							}
							if ($leixing == 1) {
								$ap_type = 1;
							} elseif ($leixing == 2) {
								$ap_type = 2;
							} else {
								$ap_type = 0;
							}
							$data = array(
								'weid'       => $weid,
								'schoolid'   => $schoolid,
								'macid'      => $ckmac['id'],
								'cardid'     => $_GPC['cardCode'],
								'sid'        => $ckuser['sid'],
								'bj_id'      => $bj['bj_id'],
								'lon'         => $_GPC['longitude'],
								'lat'         => $_GPC['latitude'],
								'pic'        => $pic,
								'pic2'       => $pic2,
								'sc_ap'      => 1,
								'ap_type'    => $ap_type,
								'roomid'     => $this_roomid,
								'apid'       => $this_apid,
								'createtime' => $signTime,
								'surestatus'  => $xk_type['is_sure_kq'] == 1 ? 0 : 1,
							);
						} else {
							$data = array(
								'weid'        => $weid,
								'schoolid'    => $schoolid,
								'macid'       => $ckmac['id'],
								'cardid'      => $_GPC['cardCode'],
								'sid'         => $ckuser['sid'],
								'bj_id'       => $bj['bj_id'],
								'lon'         => $_GPC['longitude'],
								'lat'         => $_GPC['latitude'],
								'type'        => $type,
								'pic'         => $pic,
								'pic2'        => $pic2,
								'temperature' => $_GPC['signTemp'],
								'leixing'     => $leixing,
								'pard'        => $ckuser['pard'],
								'createtime'  => $signTime,
								'surestatus'  => $xk_type['is_sure_kq'] == 1 ? 0 : 1,
							);
						}
						if ($school['xk_type'] == '1') {
							mload()->model('kc');
							$back = GetnearksBySid($schoolid, $weid, $ckuser['sid'], $signTime, true);
						}
						if (!empty($back['sendMsgArr'])) {
							foreach ($back['sendMsgArr'] as $row) {
								$this->sendMobileXsqrqdtz($row, $schoolid, $weid);
							}
						}
						pdo_insert($this->table_checklog, $data);
						$checkid = pdo_insertid();
						if (!empty($_GPC['signTemp'])) {
							$this->sendMobileTwtz($ckuser['sid'], $_GPC['signTemp'], 1, $_W['schooltype'], $checkid);
						}
						if ($ckuser['severend'] > $times  && $roominfo['isopen'] == 1) {
							if ($school['send_overtime'] >= 1) {
								$overtime = $school['send_overtime'] * 60;
								$timecha  = $times - $signTime;
								if ($overtime >= $timecha) {
									if (is_showyl()) {
										$this->sendMobileJxlxtz_yl($schoolid, $weid, $ckuser['sid'], $checkid, $ckmac['id']);
									} else {
										$this->sendMobileJxlxtz($schoolid, $weid, $bj['bj_id'], $ckuser['sid'], $type, $leixing, $checkid, $ckuser['pard']);
									}
								}
							} else {
								if (is_showyl()) {
									$this->sendMobileJxlxtz_yl($schoolid, $weid, $ckuser['sid'], $checkid, $ckmac['id']);
								} else {
									$this->sendMobileJxlxtz($schoolid, $weid, $bj['bj_id'], $ckuser['sid'], $type, $leixing, $checkid, $ckuser['pard']);
								}
							}
						}
						$fstype = true;
					}
				}
				if (!empty($ckuser['tid'])) {
					$data = array(
						'weid'       => $weid,
						'schoolid'   => $schoolid,
						'macid'      => $ckmac['id'],
						'cardid'     => $_GPC['cardCode'],
						'tid'        => $ckuser['tid'],
						'type'       => $type,
						'leixing'    => $leixing,
						'temperature' => $_GPC['signTemp'],
						'pic'        => $pic,
						'pic2'       => $pic2,
						'pard'       => 1,
						'createtime' => $signTime,
						'surestatus'  => $xk_type['is_sure_kq'] == 1 ? 0 : 1,
					);
					pdo_insert($this->table_checklog, $data);
                    $checkid = pdo_insertid();
                    CheckUnusual($checkid); //DD
					$fstype = true;
				}
			} elseif ($ckuser['cardtype'] == 2) {
				//班级卡处理
				$bj_id = $ckuser['bj_id'];
				$ThisCardStudents = pdo_fetchall("SELECT id FROM " . tablename($this->table_students) . " WHERE bj_id = :bj_id and schoolid = :schoolid", array(':bj_id' => $bj_id, ':schoolid' => $schoolid));
				foreach ($ThisCardStudents as $key => $value) {
					$data = array(
						'weid' => $weid,
						'schoolid' => $schoolid,
						'macid' => $ckmac['id'],
						'cardid' => $_GPC['cardCode'],
						'sid' => $value['id'],
						'bj_id' => $bj_id,
						'type' => $type,
						'lon'         => $_GPC['longitude'],
						'lat'         => $_GPC['latitude'],
						'pic'        => $pic,
						'pic2'       => $pic2,
						'leixing' => $leixing,
						'pard' => $ckuser['pard'],
						'createtime' => $signTime,
						'surestatus'  => $xk_type['is_sure_kq'] == 1 ? 0 : 1,
					);
					pdo_insert($this->table_checklog, $data);
					$checkid = pdo_insertid();
					if (!empty($_GPC['signTemp'])) {
						$this->sendMobileTwtz($value['id'], $_GPC['signTemp'], 1, $_W['schooltype'], $checkid);
					}
					if ($school['send_overtime'] >= 1) {
						$overtime = $school['send_overtime'] * 60;
						$timecha = $times - $signTime;
						if ($overtime >= $timecha) {
							$back =  $this->sendMobileJxlxtz($schoolid, $weid, $bj_id, $value['id'], $type, $leixing, $checkid, $ckuser['pard']);
						} else {
							$result['info'] = "延迟发送之数据将不推送刷卡提示";
						}
					} else {
						$back = $this->sendMobileJxlxtz($schoolid, $weid, $bj_id, $value['id'], $type, $leixing, $checkid, $ckuser['pard']);
					}
				}
				$fstype = true;
			}
		} else {
			$fstype = false;
			$result['msg'] = "未查询到本卡绑定情况";
		}
	} else {
		$fstype = false;
		$result['msg'] = "失败,本次刷卡为重复提交不写入记录";
	}
	//晨检新增一条数据
	if (keep_MC()) {
		$mcdata = array(
			'weid'        => $weid,
			'schoolid'    => $schoolid,
			'macid'       => $ckmac['id'],
			'sid'         => $ckuser['sid'],
			'bj_id'       => $bj['bj_id'],
			'tiwen' => $_GPC['signTemp'],
			'createtime'  => $signTime,
			'createdate'  => strtotime(date("Y-m-d", $signTime)),
			'is_mc'       => 1,
		);
		pdo_insert(GetTableName('morningcheck', false), $mcdata);
	}
	if ($fstype == true) {
		$result['status'] = 0;
		$result['msg']    = "刷卡成功";
		echo json_encode($result);
		exit;
	} else {
		$result['status'] = -1;
		//$result['msg'] = "失败";
		echo json_encode($result);
		exit;
	}
}

if ($operation == 'getleave') {
	$time = $_GPC['signtime'];
	$ckuser        = pdo_fetch("SELECT sid FROM " . tablename($this->table_idcard) . " WHERE idcard = '{$_GPC['iccode']}' And weid = '{$weid}' And schoolid = '{$schoolid}' ");
	$leave        =  pdo_fetch("SELECT sid,startime1,endtime1 FROM " . tablename($this->table_leave) . " WHERE weid = '{$weid}'  And schoolid = '{$schoolid}' and isliuyan = 0 and status = 1 and startime1 <= '{$time}' and endtime1 >= '{$time}' and sid = '{$ckuser['sid']}' ");
	$result['status'] = "0";
	if (!empty($leave)) {
		$result['data']['openDoor']   = 0;
		$result['msg']    = "该学生已请假";
	} else {
		$result['data']['openDoor']   = 1;
		$result['msg']    = "当前时间禁止外出";
	}

	echo json_encode($result);
	exit;
}
if ($operation == 'getdevremote') {
	$time = $_GPC['signtime'];
	$pid = $ckmac['id'];
	$list = pdo_fetchall("SELECT deviceId,passType,passDeviceId,cameras FROM " . tablename('wx_school_checkmac_remote') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and pid='{$pid}' ORDER BY id DESC");
	foreach ($list as $key => $row) {

		$list[$key]['cameras'] = unserialize($row['cameras']);
	}

	if (!empty($list)) {
		$result['status'] = "0";
		$result['msg']    = "获取数据成功";
		$result['data']   = $list;
	} else {
		$result['status'] = "1";
		$result['msg']    = "空数据";
	}

	echo json_encode($result);
	exit;
}

if ($operation == 'checkap') {
	$fstype        = false;
	$ckuser        = pdo_fetch("SELECT sid,pard,tid,severend FROM " . tablename($this->table_idcard) . " WHERE idcard = '{$_GPC['iccode']}' And weid = '{$weid}' And schoolid = '{$schoolid}' ");
	$bj            = pdo_fetch("SELECT bj_id FROM " . tablename($this->table_students) . " WHERE id = '{$ckuser['sid']}' ");
	$signTime = $_GPC['signtime'];
	if (!empty($ckuser)) {
		$times = time();
		$nowtime = date('H:i', $signTime);
		if ($ckmac['type'] != 0) {
			include 'checktime2.php';
		} else {
			$signMode = $_GPC['stateType'];
			include 'checktime.php';
		}
		if (!empty($ckuser['sid'])) {
			if (!empty($ckmac['apid'])) {
				$nowdate = date("Y-n-j", $signTime);
				$nowweek = date("w", $signTime);
				$student = pdo_fetch("SELECT bj_id,roomid FROM " . tablename($this->table_students) . " WHERE  schoolid = '{$schoolid}' and id = '{$ckuser['sid']}' ");
				$stu_class = pdo_fetch("SELECT datesetid FROM " . tablename($this->table_classify) . " WHERE  schoolid = '{$schoolid}' and sid = '{$student['bj_id']}' ");
				$checktime  =  pdo_fetch("SELECT * FROM " . tablename($this->table_checktimeset) . " WHERE weid = '{$weid}' And schoolid = {$school['id']} and  checkdatesetid = '{$stu_class['datesetid']}' and date = '{$nowdate}' ");
				if (!empty($checktime)) {
					if ($checktime['type'] == 6) {
						$todaytype = 1; //放假
					} elseif ($checktime['type'] == 5) {
						$todaytype = 2; //上课
					}
				} else {
					if ($nowweek != 6 && $nowweek != 0) {
						$todaytype = 2; //上课
					} else {
						$todaytype = 1; //放假
					}
				}
				if (!empty($student['roomid'])) {
					$roominfo =  pdo_fetch("SELECT * FROM " . tablename($this->table_checktimeset) . " WHERE weid = '{$weid}' And schoolid = {$school['id']} and  id = '{$student['roomid']}' ");

					if ($roominfo['noon_start'] != $roominfo['noon_end']) {
						$noon_start = $roominfo['noon_start'];
						$noon_end = $roominfo['noon_end'];
					} else {
						$noon_start = '00:00';
						$noon_end = '23:59';
					}
					if ($roominfo['night_start'] != $roominfo['night_end']) {
						$night_start = $roominfo['night_start'];
						$night_end = $roominfo['night_end'];
					} else {
						$night_start = '00:00';
						$night_end = '23:59';
					}
				} else {
					$noon_start  = '00:00';
					$noon_end 	 = '23:59';
					$night_start = '00:00';
					$night_end 	 = '23:59';
				}

				//放假期间，不经过时段判断
				if ($todaytype == 1) {
					$isCanCheck = 1; //1能开门2不能开门
					//上课期间，经过时段判断						
				} elseif ($todaytype == 2) {
					if (($nowtime >= $noon_start && $nowtime <= $noon_end) || ($nowtime >= $night_start && $nowtime <= $night_end)) {
						$isCanCheck = 1;
					} else {
						$isCanCheck = 0;
					}
				}
				if ($roominfo['apid'] != $ckmac['apid']) {
					$isCanCheck = 0;
				}
			} else {
				$isCanCheck = 1;
			}
			if ($isCanCheck == 1) {
				$fstype = true;
			} elseif ($isCanCheck == 0) {
				$fstype = false;
				$result['msg'] = "本时段禁止放行";
			}
		} elseif (!empty($ckuser['tid'])) {
			$fstype = true;
		}
	} else {
		$fstype = false;
		$result['msg'] = "未查询到本卡绑定情况";
	}
	if ($fstype == true) {
		$result['status'] = 0;
		$result['msg']    = "允许开门";
		echo json_encode($result);
		exit;
	} else {
		$result['status'] = -1;
		//$result['msg'] = "失败";
		echo json_encode($result);
		exit;
	}
}

if ($operation == 'getroomlist') {
	$data = array();
	$ii = 0;
	$allclasstimeset = GetDatesetWithBj($school['id'], $weid);
	$allroomtimeset = GetDatesetWithRoom($school['id'], $weid, $ckmac['apid']);
	$roomlist = pdo_fetchall("SELECT id FROM " . tablename($this->table_aproom) . " WHERE apid = '{$ckmac['apid']}' and schoolid = '{$school['id']}' and weid = '{$weid}' ORDER BY id DESC");
	$room_str = '';
	foreach ($roomlist as $key_r => $value_r) {
		$room_str .= $value_r['id'] . ',';
	}
	$room_str = trim($room_str, ',');
	$condition = " and FIND_IN_SET(roomid,'{$room_str}') ";
	$studentlist = pdo_fetchall("SELECT id , bj_id,s_name  as name ,roomid FROM " . tablename($this->table_students) . " WHERE weid = '{$weid}' And schoolid = {$school['id']}  $condition ORDER BY id DESC");
	foreach ($studentlist as $key => $row) {
		$this_todaytype = $allclasstimeset[$row['bj_id']]['timeset']['todaytype'];
		if ($this_todaytype == 1) {
			$studentlist[$key]['timeset'] = array(array('start' => '00:00', 'end' => '23:59'));
		} else {
			$studentlist[$key]['timeset'] = $allroomtimeset[$row['roomid']]['time'];
		}
		$card = pdo_fetchall("SELECT idcard  FROM " . tablename($this->table_idcard) . " WHERE sid = '{$row['id']}' ORDER BY id DESC");
		$studentlist[$key]['rfid'] = $card;
		if (!empty($card)) {
			foreach ($card as $key_c => $value_c) {
				$data[$ii] = $row;
				$data[$ii]['idcard'] = $value_c['idcard'];
				if ($this_todaytype == 1) {
					$data[$ii]['timeset'] = array(array('start' => '00:00', 'end' => '23:59'));
				} else {
					$data[$ii]['timeset'] = $allroomtimeset[$row['roomid']]['time'];
				}
				$ii++;
			}
		}
	}
	$teacherlist = pdo_fetchall("SELECT id ,tname as name FROM " . tablename($this->table_teachers) . " WHERE weid = '{$weid}' And schoolid = {$schoolid}  ORDER BY id DESC");
	foreach ($teacherlist as $key => $row) {
		$card = pdo_fetchall("SELECT idcard  FROM " . tablename($this->table_idcard) . " WHERE tid = '{$row['id']}' ORDER BY id DESC");
		if (!empty($card)) {
			foreach ($card as $key_c => $value_c) {
				$data[$ii] = $row;
				$data[$ii]['idcard'] = $value_c['idcard'];
				$data[$ii]['bj_id'] = 0;
				$data[$ii]['roomid'] = 0;
				$data[$ii]['timeset'] = array(array('start' => '00:00', 'end' => '23:59'));
				$ii++;
			}
		}
	}

	$result['status'] = 0;
	$result['msg']    = "获取数据成功";
	$result['data']   = $data;
	echo json_encode($result);
	exit;
}

if ($operation == 'course') {
	$data = array();
	$js_id = $ckmac['js_id'];
	$category = pdo_fetchall("SELECT * FROM " . tablename($this->table_classify) . " WHERE weid = :weid AND schoolid = :schoolid ORDER BY sid ASC, ssort DESC", array(':weid' => $weid, ':schoolid' => $school['id']), 'sid');

	$now0 = strtotime(date('Y-m-d'));
	$now1 = strtotime(date('Y-m-d') . ' 23:59:59');

	$list = pdo_fetchall("SELECT * FROM " . tablename($this->table_kcbiao) . " WHERE weid = '{$weid}' AND schoolid = '{$school['id']}' and addr_id='{$js_id}' and date>='{$now0}' and date<='{$now1}' ORDER BY id DESC ");

	foreach ($list as $key => $row) {
		$kc = pdo_fetch("SELECT * FROM " . tablename($this->table_tcourse) . " where id = :id ", array(':id' => $row['kcid']));
		$data[$key]['id'] = $row['id'];
		$data[$key]['name'] = $kc['name'];
		$data[$key]['classid'] = $row['addr_id'];
		$data[$key]['className'] = $category[$row['addr_id']]['sname'];
		$tidarray =  explode(',', $row['tid']);

		$teacher = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " where id = :id ", array(':id' => $row['tid']));

		$data[$key]['teacherName'] = $teacher['tname'];
		$data[$key]['teacherAvatar'] = empty($teacher['thumb']) ? tomedia($school['tpic']) : tomedia($teacher['thumb']);
		$data[$key]['teacherPhoneNumber'] = $teacher['mobile'];
		$data[$key]['teacherProfile'] = strip_tags(ihtml_entity_decode($teacher['jinyan']));
		$data[$key]['profile'] = strip_tags(ihtml_entity_decode($kc['dagang']));
		$data[$key]['startTime'] = date('H:i:s', $category[$row['sd_id']]['sd_start']);
		$data[$key]['endTime'] = date('H:i:s', $category[$row['sd_id']]['sd_end']);
		$data[$key]['Time'] = date('Y-m-d H:i:s', $row['date']);


		$order = pdo_fetchall("SELECT * FROM " . tablename($this->table_order) . " WHERE weid = '{$weid}' AND schoolid = '{$school['id']}' AND type = 1 AND kcid = '{$row['kcid']}' GROUP BY kcid,sid ORDER BY id DESC ");

		$students = array();
		foreach ($order as $index => $row1) {
			$student = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " WHERE id = :id ", array(':id' => $row1['sid']));

			$students[$index]['id'] = $student['id'];
			$students[$index]['cid'] = $student['bj_id'];
			$students[$index]['sex'] = $student['sex'];
			$students[$index]['name'] = $student['s_name'];
			$students[$index]['s_type'] = $student['s_type'];
			$idcard = pdo_fetch("SELECT idcard FROM " . tablename($this->table_idcard) . " WHERE weid = '{$weid}' And schoolid = {$school['id']} And is_on = 1 and sid='{$student['id']}' ");
			$students[$index]['iccode'] = $idcard['idcard'];
			$relation = pdo_fetch("SELECT  pard,idcard  FROM " . tablename($this->table_idcard) . " WHERE idcard = '{$idcard['idcard']}' ");
			if ($relation['pard'] == '1') {
				$users[$key]['relationship'] = '';
			} elseif ($relation['pard'] == '2') {
				$users[$key]['relationship'] = '母亲';
			} elseif ($relation['pard'] == '3') {
				$users[$key]['relationship'] = '父亲';
			} elseif ($relation['pard'] == '4') {
				$users[$key]['relationship'] = '爷爷';
			} elseif ($relation['pard'] == '5') {
				$users[$key]['relationship'] = '奶奶';
			} elseif ($relation['pard'] == '6') {
				$users[$key]['relationship'] = '外公';
			} elseif ($relation['pard'] == '7') {
				$users[$key]['relationship'] = '外婆';
			} elseif ($relation['pard'] == '8') {
				$users[$key]['relationship'] = '叔叔';
			} elseif ($relation['pard'] == '9') {
				$users[$key]['relationship'] = '阿姨';
			} elseif ($relation['pard'] == '10') {
				$users[$key]['relationship'] = '其他';
			}
			$students[$index]['relationship'] = $student['relationship'];
			$students[$index]['type'] = 1;
			$students[$index]['picrul'] = empty($student['icon']) ? tomedia($school['tpic']) : tomedia($student['icon']);
			$kcsign = pdo_fetch("SELECT * FROM " . tablename($this->table_kcsign) . " WHERE weid = '{$weid}' AND schoolid = '{$school['id']}' and sid ='{$student['id']}' and kcid='{$row['kcid']}' and ksid='{$row['id']}' ");
			if ($kcsign['status'] == '2') {
				$kcsign['status'] = 1;
			}
			if (empty($kcsign)) {
				$kcsign['status'] = 0;
			}
			$students[$index]['status'] = $kcsign['status'];
			$students[$index]['courseId'] = $row['id'];
		}

		$data[$key]['students'] = $students;
	}
	$result['status'] = 0;
	$result['msg']    = "获取数据成功";
	$result['data']   = $data;
	echo json_encode($result);
	exit;
}

if ($operation == 'checkforks') {

	$iccode = $_GPC['iccode'];
	$classid   = $ckmac['js_id'];

	//$signTime = $_GPC['signtime'];
	$signTime = time();
	$nowtime   = time();
	$overtime = $nowtime - $signTime;
	$date      = date('Y-m-d', $signTime);
	$riqi      = explode('-', $date);
	$starttime = mktime(0, 0, 0, $riqi[1], $riqi[2], $riqi[0]);
	$endtime   = $starttime + 86399;
	$ckuser    = pdo_fetch("SELECT * FROM " . tablename($this->table_idcard) . " WHERE idcard = :idcard And schoolid = :schoolid and (sid !=:sid or tid != :tid) ", array(':idcard' => $iccode, ':schoolid' => $schoolid, ':sid' => 0, ':tid' => 0));

	if (!empty($ckuser['sid'])) {

		$student = pdo_fetch("SELECT s_name FROM " . tablename($this->table_students) . " WHERE id='{$ckuser['sid']}' And schoolid = '{$schoolid}' ");
		mload()->model('kc');

		$nowkc_ks = Getwnnearks($classid, $starttime, $endtime);

		$nowkc    = $nowkc_ks['nowkc'][0]; //当前课程
		$nowks    = $nowkc_ks['nowks'][0]; //当前课时

		if (!$nowkc_ks) {
			$result['status'] = 1;
			if ($overtime <= 60) {
				$result['msg']  = $student['s_name'] . '当前无课时';
			}
			die(json_encode($result));
		}
		//已签到课时
		$checkAll = pdo_fetchcolumn("select count(*) FROM " . tablename($this->table_kcsign) . " WHERE weid='{$weid}' And schoolid='{$schoolid}' And  kcid = '{$nowkc['id']}' And sid='{$ckuser['sid']}' AND status = 2 ");
		//已购买课时
		$buy     = pdo_fetch("select ksnum FROM " . tablename($this->table_coursebuy) . " WHERE weid='{$weid}' And schoolid='{$schoolid}' And  kcid = '{$nowkc['id']}' And sid='{$ckuser['sid']}'");
		$checkks = pdo_fetch("select id FROM " . tablename($this->table_kcbiao) . " WHERE weid='{$weid}' And schoolid='{$schoolid}' And  id = '{$nowks['id']}'");
		//课时不存在
		if (empty($checkks)) {
			$result['status'] = 1;
			if ($overtime <= 60) {
				$result['msg']  = $student['s_name'] . '当前无课时';
			}
			die(json_encode($result));
		}
		//当前学生课时已用完
		if ($checkAll >= $buy['ksnum']) {
			$result['status'] = 1;
			if ($overtime <= 60) {
				$result['msg'] = $student['s_name'] . '无剩余可用课时';
			}
			die(json_encode($result));
		}
		//检查当前课时是否已经签到
		$checkqd = pdo_fetch("select * FROM " . tablename($this->table_kcsign) . " WHERE weid='{$weid}' And schoolid='{$schoolid}' And  kcid = '{$nowkc['id']}' And ksid = '{$nowks['id']}' And sid='{$ckuser['sid']}' AND (status = 2 or status =1) ");
		//重复签到
		if (!empty($checkqd)) {
			$result['status'] = 1;
			if ($overtime <= 60) {
				$result['msg'] = $student['s_name'] . "请勿重复签到！";
			}
			die(json_encode($result));
		}
		$kcname = pdo_fetch("select name FROM " . tablename($this->table_tcourse) . " WHERE weid='{$weid}' And schoolid='{$schoolid}' and id = '{$nowkc['id']}' ");
		$data = array(
			'kcid'       => $nowkc['id'],
			'ksid'       => $nowks['id'],
			'schoolid'   => $schoolid,
			'weid'       => $weid,
			'sid'        => $ckuser['sid'],
			'createtime' => time(),
			'signtype'   => 1,
			'status'     => 2,
			'type'       => 0,
			'kcname'     => $kcname['name'],
			'signtype'   => 3
		);
		pdo_insert($this->table_kcsign, $data);
		$signid = pdo_insertid();
		$this->sendMobileXsqrqdtz($signid, $schoolid, $weid);
		$result['status'] = 0;
		if ($overtime <= 60) {
			$result['msg'] = $student['s_name'] . "扣课时成功";
		}
		die(json_encode($result));
	} else {
		$result['status'] = 1;
		$result['msg'] = "未提交数据";
		$result['ServerTime'] = date('Y-m-d H:i:s', time());
		echo json_encode($result);
		exit;
	}
}

if ($operation == 'token') {

	$classcardset = pdo_fetch("SELECT * FROM " . tablename($this->table_classcard_set) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' ");

	$accessKeyID = $classcardset['accessKeyID'];
	$accessKeySecret = $classcardset['accessKeySecret'];
	$roleArn = $classcardset['roleArn'];
	$tokenExpire = '900';
	$policy = read_file(MODULE_ROOT . '/inc/mac/policy/all_policy.txt');


	$iClientProfile = DefaultProfile::getProfile("cn-hangzhou", $accessKeyID, $accessKeySecret);
	$client = new DefaultAcsClient($iClientProfile);

	$request = new Sts\AssumeRoleRequest();
	$request->setRoleSessionName("client_name");
	$request->setRoleArn($roleArn);
	$request->setPolicy($policy);
	$request->setDurationSeconds($tokenExpire);
	$response = $client->doAction($request);

	$rows = array();
	$body = $response->getBody();
	$content = json_decode($body);
	if ($response->getStatus() == 200) {
		$result['StatusCode'] = 200;
		$result['AccessKeyId'] = $content->Credentials->AccessKeyId;
		$result['AccessKeySecret'] = $content->Credentials->AccessKeySecret;
		$result['Expiration'] = $content->Credentials->Expiration;
		$result['SecurityToken'] = $content->Credentials->SecurityToken;
	} else {
		$result['StatusCode'] = 500;
		$result['ErrorCode'] = $content->Code;
		$result['ErrorMessage'] = $content->Message;
	}
	echo json_encode($result);
	exit;
}

function read_file($fname)
{
	$content = '';
	if (!file_exists($fname)) {
		echo "The file $fname does not exist\n";
		exit(0);
	}
	$handle = fopen($fname, "rb");
	while (!feof($handle)) {
		$content .= fread($handle, 10000);
	}
	fclose($handle);
	return $content;
}
