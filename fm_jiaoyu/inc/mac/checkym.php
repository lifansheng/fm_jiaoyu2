<?php
/**
 * By 高贵血迹
 */

global $_GPC, $_W;


$operation = in_array ( $_GPC ['op'], array ('default', 'login', 'classinfo', 'check', 'busgps', 'banner', 'video', 'disinfect' ,'start','timeset','getleave','chargeflow','getroomlist') ) ? $_GPC ['op'] : 'default';
$weid = $_GPC['i'];
$schoolid = $_GPC['schoolid'];
$macid = $_GPC['macid'];
$ckmac = pdo_fetch("SELECT * FROM " . tablename($this->table_checkmac) . " WHERE macid = '{$macid}' And weid = '{$weid}' And schoolid = '{$schoolid}' ");
$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}' ");
$xk_type = pdo_fetch("SELECT * FROM " . GetTableName('schoolset') . " WHERE schoolid = '{$schoolid}' ");
$school['xk_type'] =$xk_type['xk_type'];
if($ckmac['is_bobao'] == '15' && $operation == 'check'){
    $operation = 'disinfect';
    $_GPC['idcard'] = $_GPC['signId'];
    $_GPC['signTime'] = strtotime($_GPC['signTime']) ? strtotime($_GPC['signTime']) : time();
}
if ($operation == 'default') {
    echo("对不起，你的请求不存在！");
    exit;
}
if(empty($school)){
    echo("找不到本校，设备未关联学校");
    exit;
}
if(empty($ckmac)){
    echo("没找到设备,请添加设备");
    exit;
}
if($school['is_recordmac'] == 2){
    echo("本校无权使用设备,请联系管理员");
    exit;
}
if ($ckmac['is_on'] == 2){
    echo("本设备已关闭,请在管理后台打开");
    exit;
}
if (empty($_W['setting']['remote']['type'])) {
    $urls = $_W['SITEROOT'].$_W['config']['upload']['attachdir'].'/';
} else {
    $urls = $_W['attachurl'];
}
if ($operation == 'disinfect') {
    $idcard = $_GPC['idcard'];
    $signTime = $_GPC['signTime'];
	$cardinfo = pdo_fetch("SELECT sid,schoolid,weid,bj_id FROM " . GetTableName('idcard') . " WHERE idcard = '{$idcard}' And schoolid = '{$schoolid}' ");
    if(!empty($cardinfo) && $cardinfo['sid'] > 0){
        $this->sendMobileDisinfect($cardinfo['sid'],$signTime,$schoolid,$weid);
    }
	$result['returnCode'] = "000";
	$result['insertKqInfo'] = array(
		array(
			'COLNUM' => "1"
		)
	);
    echo json_encode($result);
    exit;
}
if ($operation == 'start') {
    if(!empty($ckmac)){
        $result['returnCode'] = "000";
        $result['insertKqConfig'] = array(
            array(
                'COLNUM' => "1"
            )
        );
        $result['getBasic'] = array(
            array(
                'TENANT_ID' => '',
                'ORG_ID' => '',
                'ORG_NAME' => $school['title'],
                'ST1' => $school['jxstart'],
                'ST2' => $school['jxend'],
                'ET1' => $school['lxstart'],
                'ET2' => $school['lxend'],
                'SBTIME' => $school['jxend'],
                'XBTIME' => $school['lxstart'],
                'STU1' => $ckmac['stu1'],
                'STU2' => $ckmac['stu2'],
                'STU3' => $ckmac['stu3'],
                'CHECK_URL'      => $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkym&op=check&m=fm_jiaoyu',
                'LEAVE_URL'      => $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkym&op=getleave&m=fm_jiaoyu',
                'OUTTIME_URL'    => $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkym&op=timeset&m=fm_jiaoyu',
                'BUSGPS' 		 => $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkym&op=busgps&m=fm_jiaoyu',
				'DISINFECT' 	 => $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkym&op=disinfect&m=fm_jiaoyu',
                'getroomlisturl' => $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkym&op=getroomlist&m=fm_jiaoyu',

            )
        );
        echo json_encode($result);
        exit;
    }
}

if ($operation == 'login') {
    if(!empty($ckmac)){
        $banner = unserialize($ckmac['banner']);
        $result['returnCode'] = "000";
        $result['apid'] = $ckmac['apid'];
        $result['getBasic'] = array(
            array(
                'INPRE' => "尊敬的家长您好,您的孩子#name#于#datatime#执卡[#cardId#]进入[设备(#devId#)]区域",
                'VOICEPRE' => $banner['VOICEPRE'],
                'NOTICE' => $banner['pop'],
                'TENANT_ID' => '',
                'ORG_ID' => '',
                'ORG_NAME' => $school['title'],
                'ST1' => $school['jxstart'],
                'ST2' => $school['jxend'],
                'ET1' => $school['lxstart'],
                'ET2' => $school['lxend'],
                'SBTIME' => $school['jxend'],
                'XBTIME' => $school['lxstart'],
                'STU1' => $ckmac['stu1'],
                'STU2' => $ckmac['stu2'],
                'STU3' => $ckmac['stu3'],
                'CHECK_URL'      => $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkym&op=check&m=fm_jiaoyu',
                'LEAVE_URL'      => $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkym&op=getleave&m=fm_jiaoyu',
                'OUTTIME_URL'    => $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkym&op=timeset&m=fm_jiaoyu',
                'BUSGPS' 		 => $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkym&op=busgps&m=fm_jiaoyu',
				'DISINFECT' 	 => $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkym&op=disinfect&m=fm_jiaoyu',
                'getroomlisturl' => $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkym&op=getroomlist&m=fm_jiaoyu',
            )
        );
        $p1 = explode('/',$banner['pic1']);
        $p2 = explode('/',$banner['pic2']);
        $p3 = explode('/',$banner['pic3']);
        $p4 = explode('/',$banner['pic4']);
        $p5 = explode('/',$school['logo']);
        if(!empty($banner['video'])){
            $result['getVideoAndImages'] = array(
                array(
                    'FILE_NAME' => $banner['video'],
                    'FILE_PATH' => $banner['video'],
                ),
                array(
                    'FILE_NAME' => $p1[4],
                    'FILE_PATH' => $banner['pic1'],
                ),
                array(
                    'FILE_NAME' => $p2[4],
                    'FILE_PATH' => $banner['pic2'],
                ),
                array(
                    'FILE_NAME' => $p3[4],
                    'FILE_PATH' => $banner['pic3'],
                ),
                array(
                    'FILE_NAME' => $p4[4],
                    'FILE_PATH' => $banner['pic4'],
                ),
                array(
                    'FILE_NAME' => $p5[4],
                    'FILE_PATH' => $school['logo'],
                ),
            );
        }else{
            $result['getVideoAndImages'] = array(
                array(
                    'FILE_NAME' => $p1[4],
                    'FILE_PATH' => $banner['pic1'],
                ),
                array(
                    'FILE_NAME' => $p2[4],
                    'FILE_PATH' => $banner['pic2'],
                ),
                array(
                    'FILE_NAME' => $p3[4],
                    'FILE_PATH' => $banner['pic3'],
                ),
                array(
                    'FILE_NAME' => $p4[4],
                    'FILE_PATH' => $banner['pic4'],
                ),
                array(
                    'FILE_NAME' => $p5[4],
                    'FILE_PATH' => $school['logo'],
                ),
            );
        }
        $temp = array(
            'isflow' => 2,
            'pop' => $banner['pop'],
            'video' => $banner['video'],
            'pic1' => $banner['pic1'],
            'pic1' => $banner['pic1'],
            'pic2' => $banner['pic2'],
            'pic3' => $banner['pic3'],
            'pic4' => $banner['pic4'],
            'VOICEPRE' => $banner['VOICEPRE'],
        );
        $temp1['banner'] = serialize($temp);
        pdo_update($this->table_checkmac, $temp1, array('id' => $ckmac['id']));
        echo json_encode($result);
        exit;
    }
}

if ($operation == 'classinfo') {
    $classid = $_GPC['classId'];
    if(!empty($ckmac)){
        $result['returnCode'] = "000";
        $users = pdo_fetchall("SELECT idcard as CARD_ID, sid as USERID, bj_id as CLASS_ID, usertype as USERTYPE, sid as SID, tid as TID,spic FROM " . tablename($this->table_idcard) . " WHERE weid = '{$weid}' And schoolid = {$school['id']} And is_on = 1 ORDER BY id DESC");
        foreach($users as $key =>$row) {
            if($row['USERTYPE'] == 1){
                $teacher = pdo_fetch("SELECT tname,thumb  FROM " . tablename($this->table_teachers) . " WHERE id = '{$row['TID']}' ");
                $users[$key]['USER_ID'] = "02" .$row['TID'];
                $users[$key]['NAME'] = $teacher['tname'];
                $users[$key]['PIC_SRC'] = empty($teacher['thumb']) ? $school['tpic'] : $teacher['thumb'];//未设置头像，取默认头像
                $users[$key]['USERNAME'] = "02" .$row['TID'];
                $users[$key]['CLASS_NAME'] = "老师";
            }else{
                $student = pdo_fetch("SELECT s_name,icon ,s_type FROM " . tablename($this->table_students) . " WHERE id = '{$row['SID']}' ");
                $bjinfo = pdo_fetch("SELECT sname  FROM " . tablename($this->table_classify) . " WHERE sid = '{$row['CLASS_ID']}' ");
                $users[$key]['USER_ID'] = $row['USERID'];
                $users[$key]['NAME'] = $student['s_name'];
                $users[$key]['PIC_SRC'] = empty($student['icon']) ? $row['spic'] : $student['icon'];
                $users[$key]['USERNAME'] = $row['SID'];
                $users[$key]['S_TYPE'] = $student['s_type'];//1走读2住校3半通
                $users[$key]['CLASS_NAME'] = $bjinfo['sname'];
            }
        }
        $result['getTeachersAndStudents'] = $users;
        $parter = pdo_fetchall("SELECT idcard as CARD_ID, pname as PNAME, id as ID, sid as STUDENT_CUID, spic as PIC_SRC, pard as PARD, usertype as UTYPE FROM " . tablename($this->table_idcard) . " WHERE weid = '{$weid}' And schoolid = {$school['id']} And is_on = 1 ORDER BY id DESC");
        foreach($parter as $key =>$row) {
            $parter[$key]['USERNAME'] = "01" . $row['ID'];
            if($row['UTYPE'] ==1){
                $parter[$key]['PTITLE'] = "老师";
            }else{
                if($row['PARD'] == 1){
                    $pard = "本人";
                }
                if($row['PARD'] == 2){
                    $pard = "妈妈";
                }
                if($row['PARD'] == 3){
                    $pard = "爸爸";
                }
                if($row['PARD'] == 4){
                    $pard = "爷爷";
                }
                if($row['PARD'] == 5){
                    $pard = "奶奶";
                }
                if($row['PARD'] == 6){
                    $pard = "外公";
                }
                if($row['PARD'] == 7){
                    $pard = "外婆";
                }
                if($row['PARD'] == 8){
                    $pard = "叔叔";
                }
                if($row['PARD'] == 9){
                    $pard = "阿姨";
                }
                if($row['PARD'] == 10){
                    $pard = "家长";
                }
                $parter[$key]['PTITLE'] = $pard;
            }
        }
        $result['getParents'] = $parter;
        echo json_encode($result);
        exit;
    }
}
if ($operation == 'check') {
    $fstype = false;
    $ckuser = pdo_fetch("SELECT sid,pard,tid,severend,cardtype FROM " . tablename($this->table_idcard) . " WHERE idcard = '{$_GPC['signId']}' And weid = '{$weid}' And schoolid = '{$schoolid}' ");
    $bj = pdo_fetch("SELECT bj_id FROM " . tablename($this->table_students) . " WHERE id = '{$ckuser['sid']}' ");
    $signTime = strtotime($_GPC['signTime']) ? strtotime($_GPC['signTime']) : time();
    $checkthisdata = pdo_fetch("SELECT * FROM " . tablename($this->table_checklog) . " WHERE cardid = '{$_GPC['signId']}' And createtime = '{$signTime}' And schoolid = '{$schoolid}' ");
    $student   = pdo_fetch("SELECT bj_id,roomid,isopen FROM " . tablename($this->table_students) . " WHERE  schoolid = '{$schoolid}' and id = '{$ckuser['sid']}' ");

    if(empty($checkthisdata)){
        if(!empty($ckuser)){
            $times = TIMESTAMP;
            $pic = $_GPC['picurl'];
            $pic2 = $_GPC['picurl2'];
            if(!empty($ckuser['sid']) || !empty($ckuser['tid'])){
                if(!empty($pic) || !empty($pic2)){
                    load()->func('file');
                    load()->func('communication');
                    $path = "images/fm_jiaoyu/check/". date('Y/m/d/');
                    $rand = random(30);
                    if(!empty($pic)){
                        $picurl = $path.$rand."_1.jpg";
                        $files_image = base64_decode($pic);
                        file_write($picurl,$files_image);
                        if (!empty($_W['setting']['remote']['type'])) {
                            file_remote_upload($picurl);
                        }
                        $pic = $picurl;
                    }
                    if(!empty($pic2)){
                        $picurl2 = $path.$rand."_2.jpg";
                        $files_image2 = base64_decode($pic2);
                        file_write($picurl2,$files_image2);
                        if (!empty($_W['setting']['remote']['type'])) {
                            file_remote_upload($picurl2);
                        }
                        $pic2 = $picurl2;
                    }
                }
            }else{
                $fstype = true;
                $result['info'] = "空卡,未绑定用户";
            }
            $nowtime = date('H:i',$signTime);
            if($ckmac['type'] !=0){
                include 'checktime2.php';
            }else{
                include 'checktime.php';
            }

            if($ckuser['cardtype'] == 1) {


                if (!empty($ckuser['sid'])) {

                    if (!empty($ckmac['apid'])) {
                        $nowdate = date("Y-n-j", $signTime);
                        $nowweek = date("w", $signTime);

                        $stu_class = pdo_fetch("SELECT datesetid FROM " . tablename($this->table_classify) . " WHERE  schoolid = '{$schoolid}' and sid = '{$student['bj_id']}' ");
                        $checktime = pdo_fetch("SELECT * FROM " . tablename($this->table_checktimeset) . " WHERE weid = '{$weid}' And schoolid = {$school['id']} and  checkdatesetid = '{$stu_class['datesetid']}' and date = '{$nowdate}' ");

                        if (!empty($checktime)) {
                            if ($checktime['type'] == 6) {
                                $todaytype = 1;//放假
                            } elseif ($checktime['type'] == 5) {
                                $todaytype = 2;//上课
                            }
                        } else {
                            if ($nowweek != 6 && $nowweek != 0) {
                                $todaytype = 2;//上课
                            } else {
                                $todaytype = 1;//放假
                            }
                        }


                        if (!empty($student['roomid'])) {
                            $roominfo = pdo_fetch("SELECT * FROM " . tablename($this->table_checktimeset) . " WHERE weid = '{$weid}' And schoolid = {$school['id']} and  id = '{$student['roomid']}' ");

                            if ($roominfo['noon_start'] != $roominfo['noon_end']) {
                                $noon_start = $roominfo['noon_start'];
                                $noon_end   = $roominfo['noon_end'];
                            } else {
                                $noon_start = '00:00';
                                $noon_end   = '23:59';
                            }
                            if ($roominfo['night_start'] != $roominfo['night_end']) {
                                $night_start = $roominfo['night_start'];
                                $night_end   = $roominfo['night_end'];
                            } else {
                                $night_start = '00:00';
                                $night_end   = '23:59';
                            }
                        } else {
                            $noon_start  = '00:00';
                            $noon_end    = '23:59';
                            $night_start = '00:00';
                            $night_end   = '23:59';
                        }


                        //放假期间，不经过时段判断
                        if ($todaytype == 1) {
                            $isCanCheck = 1;
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
                        if ($school['is_cardpay'] == 1) {
                            if (!empty($ckmac['apid'])) {
                                if (!empty($roominfo)) {
                                    $this_roomid = $roominfo['id'];
                                    $this_apid   = $ckmac['apid'];
                                } else {
                                    $this_roomid = 0;
                                    $this_apid   = 0;
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
                                    'cardid'     => $_GPC['signId'],
                                    'sid'        => $ckuser['sid'],
                                    'bj_id'      => $bj['bj_id'],
                                    'lon'        => $_GPC['lon'],
                                    'lat'        => $_GPC['lat'],
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
                                    'cardid'      => $_GPC ['signId'],
                                    'sid'         => $ckuser['sid'],
                                    'bj_id'       => $bj['bj_id'],
                                    'type'        => $type,
                                    'pic'         => $pic,
                                    'pic2'        => $pic2,
                                    'lon'         => $_GPC['lon'],
                                    'lat'         => $_GPC['lat'],
                                    'temperature' => $_GPC ['signTemp'],
                                    'leixing'     => $leixing,
                                    'pard'        => $ckuser['pard'],
                                    'createtime'  => $signTime,
                                    'surestatus'  => $xk_type['is_sure_kq'] == 1 ? 0 : 1,
                                );

                            }
                            if($school['xk_type'] == '1'){
                                mload()->model('kc');
                                $back = GetnearksBySid($schoolid,$weid,$ckuser['sid'],$signTime,true);
                                $result['bb'] = $back;
                            }
                            if(!empty($back['sendMsgArr'])){
                                foreach ($back['sendMsgArr'] as $row){
                                    $this->sendMobileXsqrqdtz($row, $schoolid, $weid);
                                }
                            }

                            pdo_insert($this->table_checklog, $data);
                            $checkid = pdo_insertid();
                            if(!empty($_GPC ['signTemp'])){
                                $this->sendMobileTwtz($ckuser['sid'],$_GPC ['signTemp'],1,$_W['schooltype'],$checkid);
                            }
                            if ($ckuser['severend'] > $times && $student['isopen'] == 1) {
                                if ($school['send_overtime'] >= 1) {
                                    $overtime = $school['send_overtime'] * 60;
                                    $timecha  = $times - $signTime;
                                    if ($overtime >= $timecha) {
                                        if (is_showyl()) {
                                            $this->sendMobileJxlxtz_yl($schoolid, $weid, $ckuser['sid'], $checkid, $ckmac['id']);
                                        } else {
                                            $this->sendMobileJxlxtz($schoolid, $weid, $bj['bj_id'], $ckuser['sid'], $type, $leixing, $checkid, $ckuser['pard']);
                                        }
                                    } else {
                                        $result['info'] = "延迟发送之数据将不推送刷卡提示";
                                    }
                                } else {
                                    if (is_showyl()) {
                                        $this->sendMobileJxlxtz_yl($schoolid, $weid, $ckuser['sid'], $checkid, $ckmac['id']);
                                    } else {
                                        $this->sendMobileJxlxtz($schoolid, $weid, $bj['bj_id'], $ckuser['sid'], $type, $leixing, $checkid, $ckuser['pard']);
                                    }
                                }
                            } else {
                                $result['info'] = "本卡已失效,请联系学校管理员";
                            }
                            $fstype = true;
                        } else {
                            if (!empty($ckmac['apid'])) {
                                if (!empty($roominfo)) {
                                    $this_roomid = $roominfo['id'];
                                    $this_apid   = $ckmac['apid'];
                                } else {
                                    $this_roomid = 0;
                                    $this_apid   = 0;
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
                                    'cardid'     => $_GPC['signId'],
                                    'sid'        => $ckuser['sid'],
                                    'bj_id'      => $bj['bj_id'],
                                    'lon'        => $_GPC['lon'],
                                    'lat'        => $_GPC['lat'],
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
                                    'cardid'      => $_GPC ['signId'],
                                    'sid'         => $ckuser['sid'],
                                    'bj_id'       => $bj['bj_id'],
                                    'lon'         => $_GPC['lon'],
                                    'lat'         => $_GPC['lat'],
                                    'type'        => $type,
                                    'pic'         => $pic,
                                    'pic2'        => $pic2,
                                    'temperature' => $_GPC ['signTemp'],
                                    'leixing'     => $leixing,
                                    'pard'        => $ckuser['pard'],
                                    'createtime'  => $signTime,
                                    'surestatus'  => $xk_type['is_sure_kq'] == 1 ? 0 : 1,
                                );
                            }
                            if($school['xk_type'] == '1'){
                                mload()->model('kc');
                                $back = GetnearksBySid($schoolid,$weid,$ckuser['sid'],$signTime,true);
                                $result['bb'] = $back;
                            }
                            if(!empty($back['sendMsgArr'])){
                                foreach ($back['sendMsgArr'] as $row){
                                    $this->sendMobileXsqrqdtz($row, $schoolid, $weid);
                                }
                            }
                            pdo_insert($this->table_checklog, $data);
                            $checkid = pdo_insertid();
                            if(!empty($_GPC ['signTemp'])){
                                $this->sendMobileTwtz($ckuser['sid'],$_GPC ['signTemp'],1,$_W['schooltype'],$checkid);
                            }
                            if ($ckuser['severend'] > $times && $student['isopen'] == 1) {
                                if ($school['send_overtime'] >= 1) {
                                    $overtime = $school['send_overtime'] * 60;
                                    $timecha  = $times - $signTime;
                                    if ($overtime >= $timecha) {
                                        if (is_showyl()) {
                                            $this->sendMobileJxlxtz_yl($schoolid, $weid, $ckuser['sid'], $checkid, $ckmac['id']);
                                        } else {
                                            $this->sendMobileJxlxtz($schoolid, $weid, $bj['bj_id'], $ckuser['sid'], $type, $leixing, $checkid, $ckuser['pard']);
                                        }
                                    } else {
                                        $result['info'] = "延迟发送之数据将不推送刷卡提示";
                                    }
                                } else {
                                    if (is_showyl()) {
                                        $this->sendMobileJxlxtz_yl($schoolid, $weid, $ckuser['sid'], $checkid, $ckmac['id']);
                                    } else {
                                        $this->sendMobileJxlxtz($schoolid, $weid, $bj['bj_id'], $ckuser['sid'], $type, $leixing, $checkid, $ckuser['pard']);
                                    }
                                }
                            } else {
                                $result['info'] = "本卡已失效,请联系学校管理员";
                            }
                            $fstype = true;
                        }
                    }
                }
                if (!empty($ckuser['tid'])) {
                    $data = array(
                        'weid'       => $weid,
                        'schoolid'   => $schoolid,
                        'macid'      => $ckmac['id'],
                        'cardid'     => $_GPC ['signId'],
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
                    $fstype = true;
                }
            }elseif($ckuser['cardtype'] == 2){

                //班级卡处理
                $bj_id = $ckuser['bj_id'];
                $ThisCardStudents = pdo_fetchall("SELECT id FROM " . tablename($this->table_students) . " WHERE bj_id = :bj_id and schoolid = :schoolid", array(':bj_id' =>$bj_id,':schoolid'=>$schoolid));
                foreach ($ThisCardStudents as $key=>$value){
                    $data = array(
                        'weid' => $weid,
                        'schoolid' => $schoolid,
                        'macid' => $ckmac['id'],
                        'cardid' => $_GPC['signId'],
                        'sid' => $value['id'],
                        'bj_id' => $bj_id,
                        'type' => $type,
                        'pic' => $pic,
                        'pic2' => $pic2,
                        'lon' => $_GPC['lon'],
                        'lat' => $_GPC['lat'],
                        'temperature' => $_GPC['signTemp'],
                        'leixing' => $leixing,
                        'pard' => $ckuser['pard'],
                        'createtime' => $signTime,
                        'surestatus'  => $xk_type['is_sure_kq'] == 1 ? 0 : 1,
                    );
                    pdo_insert($this->table_checklog, $data);
                    $checkid = pdo_insertid();
                    if(!empty($_GPC ['signTemp'])){
                        $this->sendMobileTwtz($value['id'],$_GPC ['signTemp'],1,$_W['schooltype'],$checkid);
                    }
                    if($school['send_overtime'] >= 1){
                        $overtime = $school['send_overtime']*60;
                        $timecha = $times - $signTime;
                        if($overtime >= $timecha){
                            $this->sendMobileJxlxtz($schoolid, $weid, $bj_id, $value['id'], $type, $leixing, $checkid, $ckuser['pard']);
                        }else{
                            $result['info'] = "延迟发送之数据将不推送刷卡提示";
                        }
                    }else{
                        $this->sendMobileJxlxtz($schoolid, $weid,$bj_id,$value['id'], $type, $leixing, $checkid, $ckuser['pard']);
                    }
                }
                $fstype = true;
            }else{
                $fstype = true;
                $result['info'] = "此卡非班级卡，也非用户卡";
            }
        }else{
            $fstype = true;
            $result['info'] = "本校无此卡";
        }
    }else{
        $fstype = true;
        $result['info'] = "此数据为重复提交";
    }
    //晨检新增一条数据
    if(keep_MC()){
        $mcdata = array(
            'weid'        => $weid,
            'schoolid'    => $schoolid,
            'macid'       => $ckmac['id'],
            'sid'         => $ckuser['sid'],
            'bj_id'       => $bj['bj_id'],
            'tiwen' => $_GPC ['signTemp'],
            'createtime'  => $signTime,
            'createdate'  => strtotime(date("Y-m-d",$signTime)),
            'is_mc'       => 1,
        );
        pdo_insert(GetTableName('morningcheck',false),$mcdata);
    }
    if ($fstype !=false){
        $result['returnCode'] = "000";
        $result['insertKqInfo'] = array(
            array(
                'COLNUM' => "1"
            )
        );
        echo json_encode($result);
        exit;
    }else{
        $result['returnCode'] = "222";
        $result['insertKqInfo'] = array(
            array(
                'COLNUM' => "2"
            )
        );
        echo json_encode($result);
        exit;
    }
}


if ($operation == 'timeset') {//获取班级信息
    if(!empty($ckmac)){
        $class = pdo_fetchall("SELECT sid as CLASS_ID, datesetid FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'theclass' And schoolid = {$school['id']} ORDER BY ssort DESC");
        if($class){
            $nowdate = date("Y-n-j",time());
            $nowyear = date("Y",time());
            $nowweek = date("w",time()); //今天是星期几
            $result['returnCode'] = "000";

            foreach($class as $key =>$row) {
                $todaytype = 0;
                $todaytimeset = array(
                    array(
                        'start'=>'00:00',
                        'end'  =>'23:59'
                    ),
                );
                if(empty($ckmac['apid']) && !empty($row['datesetid'])){
                    $checkdateset      =  pdo_fetch("SELECT * FROM " . tablename($this->table_checkdateset) . " WHERE weid = '{$weid}' And schoolid = {$school['id']} and  id = '{$row['datesetid']}'");
                    $checkdateset_holi =  pdo_fetch("SELECT * FROM " . tablename($this->table_checkdatedetail) . " WHERE weid = '{$weid}' And schoolid = {$school['id']} and  checkdatesetid = '{$row['datesetid']}' and year = '{$nowyear}' ");

                    $checktime         =  pdo_fetchall("SELECT * FROM " . tablename($this->table_checktimeset) . " WHERE weid = '{$weid}' And schoolid = {$school['id']} and  checkdatesetid = '{$row['datesetid']}' and date = '{$nowdate}' ORDER BY id ASC ");
                    if(!empty($checktime)){
                        if($checktime[0]['type'] == 6){
                            //1放假2上课
                            $todaytype = 1;
                        }elseif($checktime[0]['type'] == 5){
                            $todaytype    = 2;
                            $todaytimeset = $checktime;
                        }
                    }else{
                        if(($nowdate >= $checkdateset_holi['win_start'] && $nowdate <=$checkdateset_holi['win_end']) || ($nowdate >= $checkdateset_holi['sum_start'] && $nowdate <=$checkdateset_holi['sum_end'])){
                            $todaytype = 1;
                        }else{
                            $timeset_work = pdo_fetchall("SELECT start,end,s_type,out_in FROM " . tablename($this->table_checktimeset) . " WHERE weid = '{$weid}' And schoolid = {$school['id']} and  checkdatesetid = '{$row['datesetid']}' and type=1 ORDER BY id ASC ");
                            //星期五
                            if($nowweek == 5){
                                $todaytype = 2;
                                if($checkdateset['friday'] == 1){
                                    $timeset_fri = pdo_fetchall("SELECT start,end,s_type,out_in FROM " . tablename($this->table_checktimeset) . " WHERE weid = '{$weid}' And schoolid = {$school['id']} and  checkdatesetid = '{$row['datesetid']}' and type=2 ORDER BY id ASC ");
                                    $todaytimeset = $timeset_fri;
                                }else{
                                    $todaytimeset = $timeset_work;
                                }
                                //星期六
                            }elseif($nowweek == 6){
                                if($checkdateset['saturday'] == 1){
                                    $timeset_sat = pdo_fetchall("SELECT start,end,s_type,out_in FROM " . tablename($this->table_checktimeset) . " WHERE weid = '{$weid}' And schoolid = {$school['id']} and  checkdatesetid = '{$row['datesetid']}' and type=3 ORDER BY id ASC ");
                                    $todaytype = 2;
                                    $todaytimeset = $timeset_sat;
                                }else{
                                    $todaytype = 1;
                                }

                                //星期天
                            }elseif($nowweek == 0){
                                if($checkdateset['sunday'] == 1){
                                    $timeset_sun = pdo_fetchall("SELECT start,end,s_type,out_in FROM " . tablename($this->table_checktimeset) . " WHERE weid = '{$weid}' And schoolid = {$school['id']} and  checkdatesetid = '{$row['datesetid']}' and type=4 ORDER BY id ASC ");
                                    $todaytype    = 2;
                                    $todaytimeset = $timeset_sun;
                                }else{
                                    $todaytype    = 1;
                                }
                                //工作日
                            }else{
                                $todaytype    = 2;
                                $todaytimeset = $timeset_work;
                            }
                        }
                    }
                }
                $class[$key]['todaytype']    = $todaytype;
                $class[$key]['todaytimeset'] = $todaytimeset;
            }
            $result['data'] = $class;

        }else{
            $result['returnCode'] = "222";
            $result['msg'] = "本校未添加班级信息";
        }
        echo json_encode($result);
    }
}

if ($operation == 'getleave') {
    $time = $_GPC['signTime'];
    $ckuser        = pdo_fetch("SELECT sid FROM " . tablename($this->table_idcard) . " WHERE idcard = '{$_GPC['signId']}' And weid = '{$weid}' And schoolid = '{$schoolid}' ");
    $leave        =  pdo_fetch("SELECT sid,startime1,endtime1 FROM " . tablename($this->table_leave) . " WHERE weid = '{$weid}'  And schoolid = '{$schoolid}' and isliuyan = 0 and status = 1 and startime1 <= '{$time}' and endtime1 >= '{$time}' and sid = '{$ckuser['sid']}' ");
    $result['returnCode'] = "000";

    if(!empty($leave)){
        $result['data']['openDoor']   = 0;
    }else{
        $result['data']['openDoor']   = 1;
    }

    echo json_encode($result);
    exit;
}
if ($operation == 'chargeflow'){
    $cardid = $_GPC['cardID'];
    $nowtime = time();
    $lasttime = $nowtime - 1;
    $ckuser = pdo_fetch("SELECT sid FROM " . tablename($this->table_idcard) . " WHERE idcard = '{$cardid}' And weid = '{$weid}' And schoolid = '{$schoolid}' and pard = 1 ");
    $student = pdo_fetch("SELECT id,chargenum FROM " . tablename($this->table_students) . " WHERE id = '{$ckuser['sid']}' And weid = '{$weid}' And schoolid = '{$schoolid}' ");
    $result['returnCode'] = "222";
    $result['msg']    = $student;
    $lastcharge =  pdo_fetch("SELECT * FROM " . tablename($this->table_yuecostlog) . " WHERE sid = '{$ckuser['sid']}' And weid = '{$weid}' And schoolid = '{$schoolid}' and yue_type = 3 and macid = '{$_GPC['macid']}' and cost = 1 and costtime > '{$lasttime}'  ");

    if(empty($lastcharge)){
        if($student['chargenum'] != 0 ){
            $now_num = $student['chargenum'] - 1 ;
            $data_this = array(
                'chargenum'=> $now_num
            );
            pdo_update($this->table_students,$data_this,array('id'=>$student['id']));
            $this->sendMobileOfflinexf($ckuser['sid'],$cost,$_GPC['macid'],$nowtime,$schoolid,$weid,2);
            $chargelog = array(
                'schoolid' 		=> $schoolid,
                'weid'	   		=> $weid,
                'sid'	   		=> $ckuser['sid'],
                'yue_type' 		=> 3, //充电桩
                'cost' 	   		=> 1,
                'costtime' 		=> $nowtime,
                'cost_type'		=> 2,
                'macid'			=> $_GPC['macid'],
                'on_offline' 	=> 2,
                'createtime' => time()
            );
            pdo_insert($this->table_yuecostlog,$chargelog);
            $result['returnCode'] = "000";
            $result['msg']    = "success";
        }else{
            $result['returnCode'] = "222";
            $result['msg']    = "当前学生剩余次数不足";

        }
    }else{
        $result['returnCode'] = "333";
        $result['msg']    = "刷卡过于频繁，操作失败";
    }
    echo json_encode($result);
    exit;
}

if ($operation == 'getroomlist'){
    $data = array();
    $ii = 0;
    $allclasstimeset = GetDatesetWithBj($school['id'],$weid);
    $allroomtimeset = GetDatesetWithRoom($school['id'],$weid,$ckmac['apid']);
    $roomlist = pdo_fetchall("SELECT id FROM " . tablename($this->table_aproom) . " WHERE apid = '{$ckmac['apid']}' and schoolid = '{$school['id']}' and weid = '{$weid}' ORDER BY id DESC");
    $room_str = '';
    foreach($roomlist as $key_r=>$value_r){
        $room_str .=$value_r['id'].',';
    }
    $room_str = trim($room_str,',');
    $condition = " and FIND_IN_SET(roomid,'{$room_str}') ";
    $studentlist =pdo_fetchall("SELECT id , bj_id,s_name  as name ,roomid FROM " . tablename($this->table_students) . " WHERE weid = '{$weid}' And schoolid = {$school['id']}  $condition ORDER BY id DESC");
    foreach($studentlist as $key =>$row) {
        $this_todaytype = $allclasstimeset[$row['bj_id']]['timeset']['todaytype'];
        if($this_todaytype == 1){
            $studentlist[$key]['timeset'] = array(array('start'=>'00:00','end'=>'23:59'));
        }else{
            $studentlist[$key]['timeset'] = $allroomtimeset[$row['roomid']]['time'];
        }
        $card = pdo_fetchall("SELECT idcard  FROM " . tablename($this->table_idcard) . " WHERE sid = '{$row['id']}' ORDER BY id DESC");
        $studentlist[$key]['rfid'] = $card;
        if(!empty($card)){
            foreach ($card as $key_c =>$value_c){
                $data[$ii] = $row;
                $data[$ii]['idcard'] = $value_c['idcard'];
                if($this_todaytype == 1){
                    $data[$ii]['timeset'] = array(array('start'=>'00:00','end'=>'23:59'));
                }else{
                    $data[$ii]['timeset'] = $allroomtimeset[$row['roomid']]['time'];
                }
                $ii++;
            }
        }
    }
    $teacherlist = pdo_fetchall("SELECT id ,tname as name FROM " . tablename($this->table_teachers) . " WHERE weid = '{$weid}' And schoolid = {$schoolid}  ORDER BY id DESC");
    foreach( $teacherlist as $key=>$row){
        $card = pdo_fetchall("SELECT idcard  FROM " . tablename($this->table_idcard) . " WHERE tid = '{$row['id']}' ORDER BY id DESC");
        if(!empty($card)){
            foreach ($card as $key_c =>$value_c){
                $data[$ii] = $row;
                $data[$ii]['idcard'] = $value_c['idcard'];
                $data[$ii]['bj_id'] = 0;
                $data[$ii]['roomid'] = 0;
                $data[$ii]['timeset'] = array(array('start'=>'00:00','end'=>'23:59'));
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
if ($operation == 'busgps') {
    $checkgpsdata = pdo_fetch("SELECT id FROM " . tablename($this->table_busgps) . " where schoolid = '{$schoolid}' And macid = '{$macid}' And createtime = '{$_GPC['time']}' ");
    if($checkgpsdata){
        $result['status'] = 1;
        $result['msg']    = "本条数据重复";
    }else{
        $bdmap = bd_encrypt($_GPC['lon'],$_GPC['lat']);
        $data = array(
            'weid'       => $weid,
            'schoolid'   => $schoolid,
            'macid'      => $macid,
            'lon'        => $bdmap['bd_lon'],
            'lat' 		 => $bdmap['bd_lat'],
            'createtime' => $_GPC['time'],
        );
        pdo_insert($this->table_busgps,$data);
        $result['status'] = 0;
        $result['msg']    = "上传GPS定位成功";
    }
    echo json_encode($result);
    exit;
}
