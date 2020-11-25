<?php

/**
 * By 高贵血迹
 */

global $_GPC, $_W;

$operation = in_array($_GPC['op'], array('default', 'login', 'classinfo', 'check', 'gps', 'banner', 'video', 'getleave', 'getroomlist', 'online', 'onlineflow', 'offlineflow', 'busgps', 'classinfo_withphone', 'addMc', 'getBjNotice', 'getSchoolNotice', 'getSchoolKq', 'getClassKq', 'SendCoseMsg')) ? $_GPC['op'] : 'default';
$weid = $_GPC['i'];
$schoolid = $_GPC['schoolid'];
$macid = $_GPC['macid'];
$ckmac = pdo_fetch("SELECT * FROM " . GetTableName('checkmac') . " WHERE macid = '{$macid}' And schoolid = '{$schoolid}' ");
if($ckmac['is_bobao'] == '13' && $operation == 'check'){
    $operation = 'addMc';
    $_GPC['idcard'] = $_GPC['signId'];
    $_GPC['tiwen'] = $_GPC['signTemp'];
    if ($_GPC['mactype'] == 'other') {
        $_GPC['createtime'] = strtotime($_GPC['signTime']);
    } else {
        $_GPC['createtime'] = trim($_GPC['signTime']);
    }
}
if(empty($ckmac) && $operation == 'SendCoseMsg'){//非后端添加消费及调用消费推送
    $ckmac = array();
    $ckmac['is_on'] = 1;
}
$school = pdo_fetch("SELECT * FROM " . GetTableName('index') . " WHERE id = '{$schoolid}' ");
$xk_type = pdo_fetch("SELECT * FROM " . GetTableName('schoolset') . " WHERE schoolid = '{$schoolid}' ");
$school['xk_type'] = $xk_type['xk_type'];
if ($operation == 'default') {
    echo ("错误，未知操作");
    exit;
}
if (empty($school)) {
    echo ("找不到本校");
    exit;
}

if (empty($ckmac)) {
    echo ("没找到设备");
    exit;
}
if ($school['is_recordmac'] == 2) {
    echo ("本校无权使用设备");
    exit;
}
if ($ckmac['is_on'] == 2) {
    echo ("本设备已关闭");
    exit;
}
if (!empty($_W['setting']['remote']['type'])) {
    $urls = $_W['attachurl'];
} else {
    $urls = $_W['siteroot'] . 'attachment/';
}
if ($operation == 'login') {
    if (!empty($ckmac)) {
        $class = pdo_fetchall("SELECT sid as classId, sname as className  FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And (type = 'theclass' OR type = 'jsfz') And schoolid = {$school['id']} ORDER BY sid DESC");
        foreach ($class as $key => $row) {
            $checkclass = pdo_fetch("SELECT type,pname,sname  FROM " . tablename($this->table_classify) . " WHERE sid = '{$row['classId']}'");
            if ($checkclass['type'] == 'theclass') {
                $class[$key]['className'] = $row['className'];
                $class[$key]['channel'] = $row['classId'];
                $class[$key]['channeldesc'] = $row['className'];
            } else {
                $class[$key]['className'] = $checkclass['sname'];
                $class[$key]['channel'] = $row['classId'];
                $class[$key]['channeldesc'] = $checkclass['pname'];
            }
        }
        $result['data']['classInfo'] = $class;
        $result['data']['schoolInfo'] = array(
            'name' => $school['title'],
            'schoolId' => $school['id'],
            'logo' => $urls . $school['logo'],
            'tel' => $school['tel'],
        );
        $banner = unserialize($ckmac['banner']);
        $result['data']['schoolInfo'] = array_merge($result['data']['schoolInfo'], $banner);
        if ($ckmac['twmac'] == -1) {
            $result['data']['tempid'] = 1;
        } else {
            $result['data']['tempid'] = $ckmac['twmac'];
        }
        if ($ckmac['cardtype'] == 1) {
            $result['data']['cardtype'] = 1;
        }
        if ($ckmac['cardtype'] == 2) {
            $result['data']['cardtype'] = 2;
        }
        $result['data']['apid'] = $ckmac['apid'];
        $result['data']['apiclassIdd'] = $ckmac['bj_id'];
        $result['data']['Classid'] = $ckmac['bj_id'];
        $result['data']['STU1'] = $ckmac['stu1'];
        $result['data']['STU2'] = $ckmac['stu2'];
        $result['data']['STU3'] = $ckmac['stu3'];
        $result['data']['CHECK_URL'] = urlencode($_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkhx&op=check&m=fm_jiaoyu');
        $result['data']['LEAVE_URL'] = urlencode($_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkhx&op=getleave&m=fm_jiaoyu');
		$result['data']['onlineurl'] = urlencode($_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkhx&op=online&m=fm_jiaoyu');
        $result['data']['OUTTIME_URL'] = urlencode($_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkhx&op=classinfo&m=fm_jiaoyu');
        $result['data']['getroomlisturl'] = urlencode($_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkhx&op=getroomlist&m=fm_jiaoyu');
        $result['data']['busgps'] = urlencode($_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkhx&op=busgps&m=fm_jiaoyu');
        $result['data']['schoolnotice'] = urlencode($_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkhx&op=getSchoolNotice&m=fm_jiaoyu');
        $result['data']['classnotice'] = urlencode($_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkhx&op=getBjNotice&m=fm_jiaoyu');
        $result['data']['getclasskq'] = urlencode($_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkhx&op=getClassKq&m=fm_jiaoyu');
        $result['code'] = 1000;
        $result['msg'] = "success";
        $result['ServerTime'] = date('Y-m-d H:i:s', time());
        echo json_encode($result);
    }
}

if ($operation == 'classinfo') {
    $classid = $_GPC['classId'];
    $isfz = pdo_fetch("SELECT type,datesetid  FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And schoolid = '{$school['id']}' And sid = '{$classid}'");
    if ($isfz['type'] == 'theclass' && $classid > 0) {
        if (!empty($ckmac)) {
            $nowdate = date("Y-n-j", time());
            $nowyear = date("Y", time());
            $nowweek = date("w", time());
            $todaytype = 0;
            $todaytimeset = array(
                array(
                    'start' => '00:00',
                    'end' => '23:59',
                ),
            );
            if (!empty($isfz['datesetid'])) {
                $checkdateset = pdo_fetch("SELECT * FROM " . tablename($this->table_checkdateset) . " WHERE weid = '{$weid}' And schoolid = {$school['id']} and  id = '{$isfz['datesetid']}'");
                $checkdateset_holi = pdo_fetch("SELECT * FROM " . tablename($this->table_checkdatedetail) . " WHERE weid = '{$weid}' And schoolid = {$school['id']} and  checkdatesetid = '{$isfz['datesetid']}' and year = '{$nowyear}' ");

                $checktime = pdo_fetchall("SELECT * FROM " . tablename($this->table_checktimeset) . " WHERE weid = '{$weid}' And schoolid = {$school['id']} and  checkdatesetid = '{$isfz['datesetid']}' and date = '{$nowdate}' ORDER BY id ASC ");
                if (!empty($checktime)) {
                    if ($checktime[0]['type'] == 6) {
                        //1放假2上课
                        $todaytype = 1;
                    } elseif ($checktime[0]['type'] == 5) {
                        $todaytype = 2;
                        $todaytimeset = $checktime;
                    }
                } else {
                    if ((strtotime($nowdate) >= strtotime($checkdateset_holi['win_start']) && strtotime($nowdate) <= strtotime($checkdateset_holi['win_end'])) || (strtotime($nowdate) >= strtotime($checkdateset_holi['sum_start']) && strtotime($nowdate) <= strtotime($checkdateset_holi['sum_end']))) {
                        $todaytype = 1;
                    } else {
                        $timeset_work = pdo_fetchall("SELECT start,end,out_in,s_type FROM " . tablename($this->table_checktimeset) . " WHERE weid = '{$weid}' And schoolid = {$school['id']} and  checkdatesetid = '{$isfz['datesetid']}' and type=1 ORDER BY id ASC ");
                        //星期五
                        if ($nowweek == 5) {
                            $todaytype = 2;
                            if ($checkdateset['friday'] == 1) {
                                $timeset_fri = pdo_fetchall("SELECT start,end,out_in,s_type FROM " . tablename($this->table_checktimeset) . " WHERE weid = '{$weid}' And schoolid = {$school['id']} and  checkdatesetid = '{$isfz['datesetid']}' and type=2 ORDER BY id ASC ");
                                $todaytimeset = $timeset_fri;
                            } else {
                                $todaytimeset = $timeset_work;
                            }
                            //星期六
                        } elseif ($nowweek == 6) {
                            if ($checkdateset['saturday'] == 1) {
                                $timeset_sat = pdo_fetchall("SELECT start,end,out_in,s_type FROM " . tablename($this->table_checktimeset) . " WHERE weid = '{$weid}' And schoolid = {$school['id']} and  checkdatesetid = '{$isfz['datesetid']}' and type=3 ORDER BY id ASC ");
                                $todaytype = 2;
                                $todaytimeset = $timeset_sat;
                            } else {
                                $todaytype = 1;
                            }
                            //星期天
                        } elseif ($nowweek == 0) {
                            if ($checkdateset['sunday'] == 1) {
                                $timeset_sun = pdo_fetchall("SELECT start,end,out_in,s_type FROM " . tablename($this->table_checktimeset) . " WHERE weid = '{$weid}' And schoolid = {$school['id']} and  checkdatesetid = '{$isfz['datesetid']}' and type=4 ORDER BY id ASC ");
                                $todaytype = 2;
                                $todaytimeset = $timeset_sun;
                            } else {
                                $todaytype = 1;
                            }
                            //工作日
                        } else {
                            $todaytype = 2;
                            $todaytimeset = $timeset_work;
                        }
                    }
                }
            }
            if (!empty($ckmac['apid'])) {
                $todaytype = 0;
                $todaytimeset = array(
                    array(
                        'start' => '00:00',
                        'end' => '23:59',
                    ),
                );
            }
            $result['data']['todaytype'] = $todaytype;
            $result['data']['todaytimeset'] = $todaytimeset;

            $class = pdo_fetchall("SELECT id as childId, bj_id as classId, icon as headIcon, s_name as name,s_type,roomid,homephone as tel,mobile FROM " . tablename($this->table_students) . " WHERE weid = '{$weid}' And schoolid = {$school['id']} And bj_id = '{$classid}' ORDER BY id DESC");
            $bzr = pdo_fetch("SELECT tid FROM " . GetTableName('classify') . " WHERE sid = '{$classid}' ");
            $bzrinfo = pdo_fetch("SELECT tel ,mobile FROM " . GetTableName('teachers') . " WHERE id = '{$bzr['tid']}' ");
            $result['data']['bzrtel'] = $bzrinfo['tel'];
            $result['data']['bzrmobile'] = $bzrinfo['mobile'];
            foreach ($class as $key => $row) {
                if ($row['roomid'] != 0) {
                    $roominfo = pdo_fetch("SELECT apid,name FROM " . GetTableName('aproom') . " WHERE id = '{$row['roomid']}' ");
                    $apartinfo = pdo_fetch("SELECT name  FROM " . GetTableName('apartment') . " WHERE  id = '{$roominfo['apid']}'  ");
                    $RmName = $roominfo['name'] ? $roominfo['name'] : '';
                    $ApId = $roominfo['apid'] ? $roominfo['apid'] : '';
                    $ApName = $apartinfo['name'] ? $apartinfo['name'] : '';
                } else {
                    $RmName = '';
                    $ApName = '';
                    $ApId = 0;
                }
                $class[$key]['roomName'] = $RmName;
                $class[$key]['roomAreaId'] = $ApId;
                $class[$key]['roomAreaName'] = $ApName;

                if (!empty($row['headIcon'])) {
                    $class[$key]['headIcon'] = $urls . $row['headIcon'];
                } else {
                    $class[$key]['headIcon'] = !empty($school['spic']) ? $urls . $school['spic'] : "";
                }
                $class[$key]['name'] = $row['name'];
                $class[$key]['signId'] = "";
                $card = pdo_fetchall("SELECT idcard,spic  FROM " . tablename($this->table_idcard) . " WHERE sid = '{$row['childId']}' ORDER BY id DESC");
                $num = count($card);
                if ($num > 1) {
                    foreach ($card as $k => $r) {
                        if (!empty($r['idcard'])) {
                            $class[$key]['signId'] .= "#" . $r['idcard'];
                            $class[$key]['card2icon'][$r['idcard']] = tomedia($r['spic'], false, true);
                        }
                    }
                } else {
                    $class[$key]['signId'] = $card['0']['idcard'];
                    $card2icon_key = $card['0']['idcard'] ? $card['0']['idcard'] : 0;
                    $class[$key]['card2icon'][$card2icon_key] = tomedia($card['0']['spic'], false, true);
                }
                $class[$key]['fingerid1'] = "-1";
                $class[$key]['fingerid2'] = "-1";
                $class[$key]['fingerid3'] = "-1";
                $class[$key]['fingerid4'] = "-1";
                $class[$key]['fingerid5'] = "-1";
            }
            $result['data']['childs'] = $class;
            $result['code'] = 1000;
            $result['msg'] = "success";
            $result['ServerTime'] = date('Y-m-d H:i:s', time());
            echo json_encode($result);
        }
    } else {
        if (!empty($ckmac)) {
            $class = pdo_fetchall("SELECT id as TID, fz_id as classId, thumb as headIcon, tname as name,tel as homephone,mobile FROM " . tablename($this->table_teachers) . " WHERE weid = '{$weid}' And schoolid = {$school['id']} And fz_id = '{$classid}' ORDER BY id DESC");
            foreach ($class as $key => $row) {
                if (!empty($row['headIcon'])) {
                    $class[$key]['headIcon'] = $urls . $row['headIcon'];
                } else {
                    $class[$key]['headIcon'] = !empty($school['tpic']) ? $urls . $school['tpic'] : "";
                }
                $class[$key]['childId'] = "909" . $row['TID'];
                $class[$key]['name'] = $row['name'];
                $class[$key]['signId'] = "";
                $card = pdo_fetchall("SELECT idcard  FROM " . tablename($this->table_idcard) . " WHERE tid = '{$row['TID']}' ORDER BY id DESC");
                $num = count($card);
                if ($num > 1) {
                    foreach ($card as $k => $r) {
                        if (!empty($r['idcard'])) {
                            $class[$key]['signId'] .= "#" . $r['idcard'];
                            $class[$key]['card2icon'][$r['idcard']] = tomedia($row['headIcon'], false, true);
                        }
                    }
                } else {
                    $class[$key]['signId'] = $card['0']['idcard'];
                    $card2icon_key = $card['0']['idcard'] ? $card['0']['idcard'] : 0;
                    $class[$key]['card2icon'][$card2icon_key] = tomedia($row['headIcon'], false, true);
                }
            }
            $result['data']['childs'] = $class;
            $result['code'] = 1000;
            $result['msg'] = "success";
            $result['ServerTime'] = date('Y-m-d H:i:s', time());
            echo json_encode($result);
        }
    }
}

if ($operation == 'check') {
    $fstype = false;
    $ckuser = pdo_fetch("SELECT * FROM " . tablename($this->table_idcard) . " WHERE idcard = :idcard And schoolid = :schoolid ", array(':idcard' => $_GPC['signId'], ':schoolid' => $schoolid));
    if ($_GPC['mactype'] == 'other') {
        $signTime = strtotime($_GPC['signTime']);
    } else {
        $signTime = trim($_GPC['signTime']);
    }
    $checkthisdata = pdo_fetch("SELECT * FROM " . tablename($this->table_checklog) . " WHERE cardid = :cardid And schoolid = :schoolid And createtime = :createtime ", array(':cardid' => $_GPC['signId'], ':schoolid' => $schoolid, ':createtime' => $signTime));
    if (empty($checkthisdata)) {
        if (!empty($ckuser)) {
            $times = TIMESTAMP;
            $nowtime = date('H:i', $signTime);
            if ($_GPC['picurl']) {
                load()->func('file');
                $path = "images/fm_jiaoyu/check/" . date('Y/m/d/');
                if (!is_dir(IA_ROOT . "/attachment/" . $path)) {
                    mkdirs(IA_ROOT . "/attachment/" . $path, "0777");
                }
                $rand = random(30);
                if (!empty($_GPC['picurl'])) {
                    $picurl = $path . $rand . "_1.jpg";
                    if ($_GPC['mactype'] == 'other') {
                        $pic_url = base64_decode(str_replace(" ", "+", $_GPC['picurl']));
                    } else {
                        $pic_url = file_get_contents($_GPC['picurl']);
                    }
                    file_write($picurl, $pic_url);
                    if (!empty($_W['setting']['remote']['type'])) {
                        $remotestatus = file_remote_upload($picurl);
                    }
                    $pic = $picurl;
                }
                if (!empty($_GPC['picurl2'])) {
                    $picurl2 = $path . $rand . "_2.jpg";
                    if ($_GPC['mactype'] == 'other') {
                        $pic_url2 = base64_decode(str_replace(" ", "+", $_GPC['picurl2']));
                    } else {
                        $pic_url2 = file_get_contents($_GPC['picurl2']);
                    }
                    file_write($picurl2, $pic_url2);
                    if (!empty($_W['setting']['remote']['type'])) {
                        $remotestatus = file_remote_upload($picurl2);
                    }
                    $pic2 = $picurl2;
                }
            }
            $signMode = $_GPC['signMode'];
            if ($ckmac['type'] != 0) {
                include 'checktime2.php';
            } else {
                include 'checktime.php';
            }
            if ($_GPC['signId'] == '999999999') {
                $data = array(
                    'weid' => $weid,
                    'schoolid' => $schoolid,
                    'macid' => $ckmac['id'],
                    'lon' => $_GPC['lon'],
                    'lat' => $_GPC['lat'],
                    'cardid' => $_GPC['signId'],
                    'type' => "无卡进出",
                    'pic' => $pic,
                    'pic2' => $pic2,
                    'leixing' => $leixing,
                    'createtime' => $signTime,
                    'surestatus' => $xk_type['is_sure_kq'] == 1 ? 0 : 1,
                );
                pdo_insert($this->table_checklog, $data);
                $fstype = true;
            }

            if ($ckuser['cardtype'] == 1) {
                if (!empty($ckuser['sid'])) {
                    $bj = pdo_fetch("SELECT bj_id,roomid,isopen FROM " . tablename($this->table_students) . " WHERE id = :id ", array(':id' => $ckuser['sid']));
                    if ($school['is_cardpay'] == 1) {
                        if (!empty($ckmac['apid'])) {
                            if (!empty($bj['roomid'])) {
                                $this_roomid = $bj['roomid'];
                                $this_apid = $ckmac['apid'];
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
                                'weid' => $weid,
                                'schoolid' => $schoolid,
                                'macid' => $ckmac['id'],
                                'cardid' => $_GPC['signId'],
                                'sid' => $ckuser['sid'],
                                'bj_id' => $bj['bj_id'],
                                'lon' => $_GPC['lon'],
                                'lat' => $_GPC['lat'],
                                'pic' => $pic,
                                'pic2' => $pic2,
                                'sc_ap' => 1,
                                'ap_type' => $ap_type,
                                'roomid' => $this_roomid,
                                'apid' => $this_apid,
                                'createtime' => $signTime,
                                'surestatus' => $xk_type['is_sure_kq'] == 1 ? 0 : 1,
                            );
                        } else {
                            $data = array(
                                'weid' => $weid,
                                'schoolid' => $schoolid,
                                'macid' => $ckmac['id'],
                                'cardid' => $_GPC['signId'],
                                'sid' => $ckuser['sid'],
                                'bj_id' => $bj['bj_id'],
                                'type' => $type,
                                'pic' => $pic,
                                'pic2' => $pic2,
                                'lon' => $_GPC['lon'],
                                'lat' => $_GPC['lat'],
                                'temperature' => $_GPC['signTemp'],
                                'leixing' => $leixing,
                                'pard' => $ckuser['pard'],
                                'createtime' => $signTime,
                                'surestatus' => $xk_type['is_sure_kq'] == 1 ? 0 : 1,

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
                        if ($ckuser['severend'] > $times && $bj['isopen'] == 1) {
                            if ($school['send_overtime'] >= 1) {
                                $overtime = $school['send_overtime'] * 60;
                                $timecha = $times - $signTime;
                                if ($overtime >= $timecha) {
                                    if (is_showyl()) {
                                        $this->sendMobileJxlxtz_yl($schoolid, $weid, $ckuser['sid'], $checkid, $ckmac['id']);
                                    } else {
                                        $this->sendMobileJxlxtz($schoolid, $weid, $bj['bj_id'], $ckuser['sid'], $type, $leixing, $checkid, $ckuser['pard']);
                                    }
                                } else {
                                    $result['msg'] = "延迟发送之数据将不推送刷卡提示";
                                }
                            } else {
                                if (is_showyl()) {
                                    $this->sendMobileJxlxtz_yl($schoolid, $weid, $ckuser['sid'], $checkid, $ckmac['id']);
                                } else {
                                    $this->sendMobileJxlxtz($schoolid, $weid, $bj['bj_id'], $ckuser['sid'], $type, $leixing, $checkid, $ckuser['pard']);
                                }
                            }
                        } else {
                            $result['msg'] = "本卡已失效,请联系学校管理员";
                        }
                        $fstype = true;
                    } else {
                        if (!empty($ckmac['apid'])) {
                            if (!empty($bj['roomid'])) {
                                $this_roomid = $bj['roomid'];
                                $this_apid = $ckmac['apid'];
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
                                'weid' => $weid,
                                'schoolid' => $schoolid,
                                'macid' => $ckmac['id'],
                                'cardid' => $_GPC['signId'],
                                'sid' => $ckuser['sid'],
                                'bj_id' => $bj['bj_id'],
                                'lon' => $_GPC['lon'],
                                'lat' => $_GPC['lat'],
                                'pic' => $pic,
                                'pic2' => $pic2,
                                'sc_ap' => 1,
                                'ap_type' => $ap_type,
                                'roomid' => $this_roomid,
                                'apid' => $this_apid,
                                'createtime' => $signTime,
                                'surestatus' => $xk_type['is_sure_kq'] == 1 ? 0 : 1,
                            );
                        } else {
                            $data = array(
                                'weid' => $weid,
                                'schoolid' => $schoolid,
                                'macid' => $ckmac['id'],
                                'cardid' => $_GPC['signId'],
                                'sid' => $ckuser['sid'],
                                'bj_id' => $bj['bj_id'],
                                'type' => $type,
                                'pic' => $pic,
                                'pic2' => $pic2,
                                'lon' => $_GPC['lon'],
                                'lat' => $_GPC['lat'],
                                'temperature' => $_GPC['signTemp'],
                                'leixing' => $leixing,
                                'pard' => $ckuser['pard'],
                                'createtime' => $signTime,
                                'surestatus' => $xk_type['is_sure_kq'] == 1 ? 0 : 1,
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
                        if ($ckuser['severend'] > $times && $bj['isopen'] == 1) {
                            if ($school['send_overtime'] >= 1) {
                                $overtime = $school['send_overtime'] * 60;
                                $timecha = $times - $signTime;
                                if ($overtime >= $timecha) {
                                    if (is_showyl()) {
                                        $this->sendMobileJxlxtz_yl($schoolid, $weid, $ckuser['sid'], $checkid, $ckmac['id']);
                                    } else {
                                        $this->sendMobileJxlxtz($schoolid, $weid, $bj['bj_id'], $ckuser['sid'], $type, $leixing, $checkid, $ckuser['pard']);
                                    }
                                } else {
                                    $result['msg'] = "延迟发送之数据将不推送刷卡提示";
                                }
                            } else {
                                if (is_showyl()) {
                                    $this->sendMobileJxlxtz_yl($schoolid, $weid, $ckuser['sid'], $checkid, $ckmac['id']);
                                } else {
                                    $this->sendMobileJxlxtz($schoolid, $weid, $bj['bj_id'], $ckuser['sid'], $type, $leixing, $checkid, $ckuser['pard']);
                                }
                            }
                        } else {
                            $result['msg'] = "本卡已失效,请联系学校管理员";
                        }
                        $fstype = true;
                    }
                }
                if (!empty($ckuser['tid'])) {
                    $data = array(
                        'weid' => $weid,
                        'schoolid' => $schoolid,
                        'macid' => $ckmac['id'],
                        'cardid' => $_GPC['signId'],
                        'tid' => $ckuser['tid'],
                        'type' => $type,
                        'leixing' => $leixing,
                        'temperature' => $_GPC['signTemp'],
                        'pic' => $pic,
                        'pic2' => $pic2,
                        'pard' => 1,
                        'createtime' => $signTime,
                        'surestatus' => $xk_type['is_sure_kq'] == 1 ? 0 : 1,
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
                        'surestatus' => $xk_type['is_sure_kq'] == 1 ? 0 : 1,
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
                            $this->sendMobileJxlxtz($schoolid, $weid, $bj_id, $value['id'], $type, $leixing, $checkid, $ckuser['pard']);
                        } else {
                            $result['msg'] = "延迟发送之数据将不推送刷卡提示";
                        }
                    } else {
                        $this->sendMobileJxlxtz($schoolid, $weid, $bj_id, $value['id'], $type, $leixing, $checkid, $ckuser['pard']);
                    }
                }
                $fstype = true;
            }
        } else {
            $result['msg'] = "本卡未绑定任何学生或老师";
        }
    } else {
        $fstype = true;
        $result['msg'] = "不可重复相同刷卡数据";
    }

    //晨检新增一条数据
    if (keep_MC()) {
        $mcdata = array(
            'weid' => $weid,
            'schoolid' => $schoolid,
            'macid' => $ckmac['id'],
            'sid' => $ckuser['sid'],
            'bj_id' => $bj['bj_id'],
            'tiwen' => $_GPC['signTemp'],
            'createtime' => $signTime,
            'createdate' => strtotime(date("Y-m-d", $signTime)),
            'is_mc' => 1,
        );
        pdo_insert(GetTableName('morningcheck', false), $mcdata);
    }
    if ($fstype == true) {
        $result['data'] = "";
        $result['code'] = 1000;
        $result['msg'] = "success";
        $result['ServerTime'] = date('Y-m-d H:i:s', time());
        echo json_encode($result);
        exit;
    } else {
        $result['data'] = "";
        $result['code'] = 300;
        $result['ServerTime'] = date('Y-m-d H:i:s', time());
        echo json_encode($result);
        exit;
    }
}
if ($operation == 'gps') {
    $fstype = false;
    $ckuser = pdo_fetch("SELECT * FROM " . tablename($this->table_idcard) . " WHERE idcard = '{$_GPC['signId']}' And weid = '{$weid}' And schoolid = '{$schoolid}' ");
    $bj = pdo_fetch("SELECT bj_id,isopen FROM " . tablename($this->table_students) . " WHERE id = '{$ckuser['sid']}' ");
    $checkthisdata = pdo_fetch("SELECT * FROM " . tablename($this->table_checklog) . " WHERE cardid = '{$_GPC['signId']}' And createtime = '{$_GPC['signTime']}' And schoolid = '{$schoolid}' ");
    if (empty($checkthisdata)) {
        if (!empty($ckuser)) {
            $times = TIMESTAMP;
            $nowtime = date('H:i', $times);
            if ($ckmac['type'] != 0) {
                include 'checktime2.php';
            } else {
                include 'checktime.php';
            }
            $signTime = trim($_GPC['signTime']);

            if ($ckuser['cardtype'] == 1) {

                if (!empty($ckuser['sid'])) {
                    if ($school['is_cardpay'] == 1) {

                        $data = array(
                            'weid' => $weid,
                            'schoolid' => $schoolid,
                            'macid' => $ckmac['id'],
                            'cardid' => $_GPC['signId'],
                            'sid' => $ckuser['sid'],
                            'bj_id' => $bj['bj_id'],
                            'type' => $type,
                            'temperature' => $_GPC['signTemp'],
                            'leixing' => $leixing,
                            'pard' => $ckuser['pard'],
                            'lon' => $_GPC['lon'],
                            'lat' => $_GPC['lat'],
                            'createtime' => $signTime,
                            'surestatus' => $xk_type['is_sure_kq'] == 1 ? 0 : 1,
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
                        pdo_insert($this->table_checklog, $data);
                        $checkid = pdo_insertid();
                        if (!empty($_GPC['signTemp'])) {
                            $this->sendMobileTwtz($ckuser['sid'], $_GPC['signTemp'], 1, $_W['schooltype'], $checkid);
                        }
                        if ($ckuser['severend'] > $times && $bj['isopen'] == 1) {
                            if ($school['send_overtime'] >= 1) {
                                $overtime = $school['send_overtime'] * 60;
                                $timecha = $times - $signTime;
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
                        }
                        $fstype = true;
                    } else {
                        $data = array(
                            'weid' => $weid,
                            'schoolid' => $schoolid,
                            'macid' => $ckmac['id'],
                            'cardid' => $_GPC['signId'],
                            'sid' => $ckuser['sid'],
                            'bj_id' => $bj['bj_id'],
                            'type' => $type,
                            'temperature' => $_GPC['signTemp'],
                            'leixing' => $leixing,
                            'lon' => $_GPC['lon'],
                            'lat' => $_GPC['lat'],
                            'pard' => $ckuser['pard'],
                            'createtime' => $signTime,
                            'surestatus' => $xk_type['is_sure_kq'] == 1 ? 0 : 1,
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
                        pdo_insert($this->table_checklog, $data);
                        $checkid = pdo_insertid();
                        if (!empty($_GPC['signTemp'])) {
                            $this->sendMobileTwtz($ckuser['sid'], $_GPC['signTemp'], 1, $_W['schooltype'], $checkid);
                        }
                        if ($ckuser['severend'] > $times && $bj['isopen'] == 1) {
                            if ($school['send_overtime'] >= 1) {
                                $overtime = $school['send_overtime'] * 60;
                                $timecha = $times - $signTime;
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
                        }
                        $fstype = true;
                    }
                }
                if (!empty($ckuser['tid'])) {
                    $data = array(
                        'weid' => $weid,
                        'schoolid' => $schoolid,
                        'macid' => $ckmac['id'],
                        'cardid' => $_GPC['signId'],
                        'tid' => $ckuser['tid'],
                        'type' => $type,
                        'leixing' => $leixing,
                        'pard' => 1,
                        'lon' => $_GPC['lon'],
                        'lat' => $_GPC['lat'],
                        'createtime' => $signTime,
                        'surestatus' => $xk_type['is_sure_kq'] == 1 ? 0 : 1,
                    );
                    pdo_insert($this->table_checklog, $data);
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
                        'surestatus' => $xk_type['is_sure_kq'] == 1 ? 0 : 1,
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
                            $this->sendMobileJxlxtz($schoolid, $weid, $bj_id, $value['id'], $type, $leixing, $checkid, $ckuser['pard']);
                        } else {
                            $result['info'] = "延迟发送之数据将不推送刷卡提示";
                        }
                    } else {
                        $this->sendMobileJxlxtz($schoolid, $weid, $bj_id, $value['id'], $type, $leixing, $checkid, $ckuser['pard']);
                    }
                }
                $fstype = true;
            }
        }
    }
    if ($fstype != false) {
        $result['data'] = "";
        $result['code'] = 1000;
        $result['msg'] = "success";
        $result['ServerTime'] = date('Y-m-d H:i:s', time());
        echo json_encode($result);
        exit;
        //print_r($signData);
    } else {
        $result['data'] = "";
        $result['code'] = 300;
        $result['msg'] = "lose";
        $result['ServerTime'] = date('Y-m-d H:i:s', time());
        echo json_encode($result);
        exit;
        //print_r($signData);
    }
}

if ($operation == 'online') {
    $nowtimes = time();
    $nowtime = date('Y-m-d H:i:s',$nowtimes);
	//在线心跳
	$online = pdo_fetch("SELECT * FROM " . tablename($this->table_online) . " WHERE macid = '{$ckmac['id']}' And type = 2 ");
	if(empty($online)){
		$dataarry = array(
			'weid'     => $weid,
			'schoolid'  => $schoolid,
			'macid'  => $ckmac['id'],
			'createtime'  => $nowtimes,
			'lastedittime'  => $nowtimes,
			'type'  => 2,
        );
		pdo_insert($this->table_online, $dataarry);
    }else{
		pdo_update($this->table_online, array('lastedittime' => $nowtimes), array('id' => $online['id']));
    }
	//查询任务
    $checkorder = pdo_fetch("SELECT * FROM " . tablename($this->table_online) . " WHERE macid = '{$ckmac['id']}' And result = 2 And isread = 2 And type = 1 order by createtime ASC LIMIT 1 ");
    if($checkorder){
        $result['data'] = array(
                'command'     => $checkorder['commond'],
                'command_id'  => $checkorder['id'],
                'lastedittime'  => $checkorder['lastedittime']
        );
        pdo_update($this->table_online, array('isread' => 2), array('id' => $checkorder['id']));
    }else{
        $result['data'] = array(
                'command'     => 0,
                'command_id'  => '',
                'lastedittime'  => ''
        );
    }
    $result['ServerTime'] = date('Y-m-d H:i:s',time());
    $result['code'] = 1000;
    $result['msg']    = "success";
    $result['macid']    = $ckmac['id'];
    echo json_encode($result);
    exit;
}

if ($operation == 'banner') {
    $banner = unserialize($ckmac['banner']);
    $ims = tomedia($banner['pic1']) . '#' . tomedia($banner['pic2']) . '#' . tomedia($banner['pic3']) . '#' . tomedia($banner['pic4']);
    $result['data'] = array(
        'img' => $ims,
        'mc' => $banner['pop'],
    );
    $result['code'] = 1000;
    $result['msg'] = "success";
    $result['ServerTime'] = date('Y-m-d H:i:s', time());
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

if ($operation == 'video') {
    $banner = unserialize($ckmac['banner']);
    $result['data'] = array(
        'videoId' => 2,
        'videoUrl' => $banner['video'],
    );
    $result['code'] = 1000;
    $result['msg'] = "success";
    $result['ServerTime'] = date('Y-m-d H:i:s', time());
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

if ($operation == 'getleave') {
    $time = $_GPC['signtime'];
    $ckuser = pdo_fetch("SELECT sid FROM " . tablename($this->table_idcard) . " WHERE idcard = '{$_GPC['iccode']}' And weid = '{$weid}' And schoolid = '{$schoolid}' ");
    $leave = pdo_fetch("SELECT sid,startime1,endtime1 FROM " . tablename($this->table_leave) . " WHERE weid = '{$weid}'  And schoolid = '{$schoolid}' and isliuyan = 0 and status = 1 and startime1 <= '{$time}' and endtime1 >= '{$time}' and sid = '{$ckuser['sid']}' ");
    $result['code'] = 10000;
    $result['msg'] = "success";
    if (!empty($leave)) {
        $result['data']['openDoor'] = 0;
    } else {
        $result['data']['openDoor'] = 1;
    }

    echo json_encode($result);
    exit;
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
        $card = pdo_fetchall("SELECT idcard,spic  FROM " . tablename($this->table_idcard) . " WHERE sid = '{$row['id']}' And pard = 1 ORDER BY id DESC");
        $studentlist[$key]['rfid'] = $card;
        if (!empty($card)) {
            foreach ($card as $key_c => $value_c) {
                $data[$ii] = $row;
                $data[$ii]['idcard'] = $value_c['idcard'];
				$data[$ii]['faceimg']= tomedia($value_c['spic'], false, true);
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
        $card = pdo_fetchall("SELECT idcard,tpic  FROM " . tablename($this->table_idcard) . " WHERE tid = '{$row['id']}' ORDER BY id DESC");
        if (!empty($card)) {
            foreach ($card as $key_c => $value_c) {
                $data[$ii] = $row;
                $data[$ii]['idcard'] = $value_c['idcard'];
				$data[$ii]['faceimg']= tomedia($value_c['tpic'], false, true);
                $data[$ii]['bj_id'] = 0;
                $data[$ii]['roomid'] = 0;
                $data[$ii]['timeset'] = array(array('start' => '00:00', 'end' => '23:59'));
                $ii++;
            }
        }
    }

    $result['status'] = 10000;
    $result['msg'] = "获取数据成功";
    $result['data'] = $data;
    echo json_encode($result);
    exit;
}

if ($operation == 'onlineflow') {
    $lastId = $_GPC['lastOnlineFlowRecordId'];
    $flowlist = pdo_fetchall("SELECT id,sid,yue_type,cost,cost_type FROM " . tablename($this->table_yuecostlog) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' and id >'{$lastId}' and on_offline = 1 ORDER BY ID ASC");
    $result['status'] = 10000;
    $result['msg'] = "success";
    if (!empty($flowlist)) {
        $data['flowlist'] = $flowlist;
    } else {
        $data['flowlist'] = array();
    }

    include '3DES.php';
    $plaintext = json_encode($data);
    $key_3DES = "r0uScmDuH5FLO37AJV2FN72J"; // 加密所需的密钥
    $iv_3DES = "1eX24DCe"; // 初始化向量
    $ciphertext = TDEA::encrypt($plaintext, $key_3DES, $iv_3DES); //加密
    $result['data'] = $ciphertext;
    echo json_encode($result);
    exit;
}

if ($operation == 'offlineflow') {
    $time = time();
    include '3DES.php';
    $key_3DES = "r0uScmDuH5FLO37AJV2FN72J"; // 加密所需的密钥
    $iv_3DES = "1eX24DCe"; // 初始化向量

    $data_h = htmlspecialchars_decode($_GPC['data']);
    $plaintext2 = TDEA::decrypt($data_h, $key_3DES, $iv_3DES); //解密
    $data = json_decode($plaintext2, true);
    foreach ($data as $row) {
        $sid = $row['userno'];
        $cardid = $row['cardid'];
        $thisMacId = $row['macid'];
        $cost = $row['paymoney'];
        $payTime = intval($row['paytime'] / 1000);
        $payKind = $row['paykind'];
        $flowid = $row['fid'];
        $studentyue = pdo_fetch("SELECT id,chongzhi FROM " . tablename($this->table_students) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' and id = '{$sid}' ");
        if (!empty($studentyue)) {
            if ($payKind == 15 || $payKind == 16) {
                $nowyue = $studentyue['chongzhi'];
                if ($school['is_buzhu'] != 0) {

                    $studentbuzhu = pdo_fetch("SELECT id,now_yue FROM " . tablename($this->table_buzhulog) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' and sid = '{$sid}' ORDER BY createtime DESC ");
                    $nowbuzhu = $studentbuzhu['now_yue'] ? $studentbuzhu['now_yue'] : 0;
                    $allyue = $nowbuzhu + $nowyue;
                    //补助
                    if ($cost <= $nowbuzhu) {
                        $this_this = 1;
                        $after_buzhu = $nowbuzhu - $cost;
                        $buzhu_data = array(
                            'now_yue' => $after_buzhu,
                        );
                        pdo_update($this->table_buzhulog, $buzhu_data, array('id' => $studentbuzhu['id']));
                        //消费记录
                        $yuelog = array(
                            'schoolid' => $schoolid,
                            'weid' => $weid,
                            'sid' => $sid,
                            'yue_type' => 1,
                            'cost' => $cost,
                            'costtime' => $payTime,
                            'cost_type' => 2,
                            'macid' => $thisMacId,
                            'on_offline' => 2,
                            'createtime' => time(),
                        );
                        pdo_insert($this->table_yuecostlog, $yuelog);
                        //补助和余额
                    } elseif ($cost > $nowbuzhu && $nowbuzhu != 0 && $allyue >= $cost) {
                        $this_this = 2;
                        $after_buzhu = 0;
                        $cost_yue = $cost - $nowbuzhu;
                        $after_yue = $nowyue - $cost_yue;
                        $buzhu_data = array(
                            'now_yue' => $after_buzhu,
                        );
                        pdo_update($this->table_buzhulog, $buzhu_data, array('id' => $studentbuzhu['id']));
                        //消费记录
                        $yuelog = array(
                            'schoolid' => $schoolid,
                            'weid' => $weid,
                            'sid' => $sid,
                            'yue_type' => 1,
                            'cost' => $nowbuzhu,
                            'costtime' => $payTime,
                            'cost_type' => 2,
                            'macid' => $thisMacId,
                            'on_offline' => 2,
                            'createtime' => time(),
                        );
                        pdo_insert($this->table_yuecostlog, $yuelog);
                        $yue_data = array(
                            'chongzhi' => $after_yue,
                        );
                        pdo_update($this->table_students, $yue_data, array('id' => $studentyue['id']));
                        //消费记录
                        $yuelog_yue = array(
                            'schoolid' => $schoolid,
                            'weid' => $weid,
                            'sid' => $sid,
                            'yue_type' => 2,
                            'cost' => $cost_yue,
                            'costtime' => $payTime,
                            'cost_type' => 2,
                            'macid' => $thisMacId,
                            'on_offline' => 2,
                            'createtime' => time(),
                        );
                        pdo_insert($this->table_yuecostlog, $yuelog_yue);
                        //余额
                    } elseif ($nowbuzhu == 0 && $cost <= $nowyue) {
                        $this_this = 3;
                        $after_yue = $nowyue - $cost;
                        $yue_data = array(
                            'chongzhi' => $after_yue,
                        );
                        pdo_update($this->table_students, $yue_data, array('id' => $studentyue['id']));
                        //消费记录
                        $yuelog_yue = array(
                            'schoolid' => $schoolid,
                            'weid' => $weid,
                            'sid' => $sid,
                            'yue_type' => 2,
                            'cost' => $cost,
                            'costtime' => $payTime,
                            'cost_type' => 2,
                            'macid' => $thisMacId,
                            'on_offline' => 2,
                            'createtime' => time(),
                        );
                        pdo_insert($this->table_yuecostlog, $yuelog_yue);
                    }
                } else {
                    $this_this = 3;
                    $after_yue = $nowyue - $cost;
                    $yue_data = array(
                        'chongzhi' => $after_yue,
                    );
                    pdo_update($this->table_students, $yue_data, array('id' => $studentyue['id']));
                    //消费记录
                    $yuelog_yue = array(
                        'schoolid' => $schoolid,
                        'weid' => $weid,
                        'sid' => $sid,
                        'yue_type' => 2,
                        'cost' => $cost,
                        'costtime' => $payTime,
                        'cost_type' => 2,
                        'macid' => $thisMacId,
                        'on_offline' => 2,
                        'createtime' => time(),
                    );
                    pdo_insert($this->table_yuecostlog, $yuelog_yue);
                }
                $this->sendMobileOfflinexf($sid, $cost, $thisMacId, $payTime, $schoolid, $weid, 1);
            }
        }
    }
    $result['status'] = 10000;
    $result['msg'] = "success";

    echo json_encode($result);
    exit;
}

if ($operation == 'busgps') {
    $checkgpsdata = pdo_fetch("SELECT id FROM " . tablename($this->table_busgps) . " where schoolid = '{$schoolid}' And macid = '{$macid}' And createtime = '{$_GPC['time']}' ");
    if ($checkgpsdata) {
        $result['status'] = 1;
        $result['msg'] = "本条数据重复";
    } else {
        $data = array(
            'weid' => $weid,
            'schoolid' => $schoolid,
            'macid' => $macid,
            'lon' => $_GPC['lon'],
            'lat' => $_GPC['lat'],
            'createtime' => $_GPC['time'],
        );
        pdo_insert($this->table_busgps, $data);
        $result['status'] = 10000;
        $result['msg'] = "上传GPS定位成功";
    }
    echo json_encode($result);
    exit;
}

if ($operation == 'addMc') {
    $idcard = $_GPC['idcard'];
    $tiwen = $_GPC['tiwen'];
    $createtime = $_GPC['createtime'];
    $cardinfo = pdo_fetch("SELECT sid,schoolid,weid,bj_id FROM " . GetTableName('idcard') . " WHERE idcard = '{$idcard}' ");
    $data = array(
        'weid' => $cardinfo['weid'],
        'schoolid' => $cardinfo['schoolid'],
        'sid' => $cardinfo['sid'],
        'bj_id' => $cardinfo['bj_id'],
        'tiwen' => $tiwen,
        'macid' => $macid,
        'createtime' => $createtime,
        'createdate' => strtotime(date("Y-m-d", $createtime)),
        'issb' => 1,
        'is_mc' => 0,
    );
    $check = pdo_fetch("SELECT id FROM ".GetTableName('morningcheck')." WHERE weid = '{$weid}' and createtime = '{$createtime}' and sid = '{$cardinfo['sid']}' ");
    if(!empty($chcek)){
        
        $result['code'] = 300;
        $result['ServerTime'] = date('Y-m-d H:i:s', time());
        $result['msg'] = "fail";
        echo json_encode($result);
        exit;
    }
    if (pdo_insert(GetTableName('morningcheck', false), $data)) {
        $MCid = pdo_insertid();
        $this->sendMobileTwtz($cardinfo['sid'], $tiwen, 3, $_W['schooltype'], $MCid);
        $result['code'] = 1000;
        $result['ServerTime'] = date('Y-m-d H:i:s', time());
        $result['msg'] = "success";
    } else {
        $result['code'] = 300;
        $result['ServerTime'] = date('Y-m-d H:i:s', time());
        $result['msg'] = "fail";
    }
    echo json_encode($result);
    exit;
}

if ($operation == 'SendCoseMsg') {
    $idcard = $_GPC['idcard'];
    $cose = $_GPC['cose'];
    $rest = $_GPC['rest'];
    $paytype = trim($_GPC['paytype']);
    $payresult = $_GPC['payresult'];
    $paytime = $_GPC['paytime'];
	$cardinfo = pdo_fetch("SELECT sid,schoolid,weid,bj_id FROM " . GetTableName('idcard') . " WHERE idcard = '{$idcard}' And schoolid = '{$schoolid}' ");
    if(!empty($cardinfo) && $cardinfo['sid'] > 0){
        $this->sendMobileCoseMsg($cardinfo['sid'],$idcard,$cose,$rest,$paytype,$payresult,$paytime,$schoolid,$weid);
        $result['code'] = 1000;
        $result['ServerTime'] = date('Y-m-d H:i:s', time());
        $result['msg'] = "success";
		$result['idcard'] = $_GPC['idcard'];
    }else{
        $result['code'] = 300;
		$result['idcard'] = $_GPC['idcard'];
        $result['ServerTime'] = date('Y-m-d H:i:s', time());
        $result['msg'] = "fail";
    }
    echo json_encode($result);
    exit;
}

 
    
if ($operation == 'getBjNotice') {
    //1班级2校园
    $bj_id = $ckmac['bj_id'];
    $createtime = $_GPC['createtime'];
    $res = pdo_fetchAll("SELECT title,content,tname,picarr,createtime,bj_id as classid FROM " . GetTableName('notice') . " WHERE schoolid = '{$schoolid}' AND bj_id = '{$bj_id}' AND createtime >= '{$createtime}' AND type = 1");
    foreach ($res as &$value) {
        $value['content'] = htmlspecialchars_decode($value['content']);
        $tempPic = unserialize($value['picarr']);
        $value['picarr'] = [];
        foreach ($tempPic as $key => &$vp) {
            if (!empty($vp)) {
                $value['picarr'][] = tomedia($vp);
            }
        }
        unset($tempPic);
    }
    if ($res) {
        $result['status'] = 10000;
        $result['msg'] = "获取数据成功";
        $result['data'] = $res;
    } else {
        $result['status'] = 10000;
        $result['msg'] = "没有最新数据";
    }
    echo json_encode($result);
    exit;
}

if ($operation == 'getSchoolNotice') {
    //1班级2校园
    $createtime = $_GPC['createtime'];
    $res = pdo_fetchAll("SELECT title,content,tname,picarr,createtime FROM " . GetTableName('notice') . " WHERE schoolid = '{$schoolid}' AND createtime >= '{$createtime}' AND type = 2");
    foreach ($res as &$value) {
        $value['content'] = htmlspecialchars_decode($value['content']);
        $tempPic = unserialize($value['picarr']);
        $value['picarr'] = [];
        foreach ($tempPic as $key => &$vp) {
            if (!empty($vp)) {
                $value['picarr'][] = tomedia($vp);
            }
        }
        unset($tempPic);
    }
    if ($res) {
        $result['status'] = 10000;
        $result['msg'] = "获取数据成功";
        $result['data'] = $res;
    } else {
        $result['status'] = 10000;
        $result['msg'] = "没有最新数据";
    }
    echo json_encode($result);
    exit;
}
if ($operation == 'getClassKq') {
    $starttime = strtotime(date('Y-m-d', time()));
    $endtime = time();
    $bj_id = $ckmac['bj_id'];
    $returnData = array(
        'onSchool' => [],
        'notOnSchool' => [],
        'queqin' => []
    );
    $list = pdo_fetchall("SELECT s.s_name,s.icon,s.sex,c.leixing,c.createtime,s.bj_id as classid,s.id FROM " . GetTableName('students') . " as s LEFT JOIN " . GetTableName('checklog') . " as c ON s.id = c.sid AND c.createtime BETWEEN '{$starttime}' AND '{$endtime}'  WHERE  s.weid = '{$weid}' And s.schoolid = '{$schoolid}' AND s.bj_id = '{$bj_id}' ORDER BY s.id DESC");
    foreach ($list as $key => $value) {
        $leixing = $value['leixing'];
        unset($value['leixing']);
        $value['signtime'] =  date("Y-m-d H:i:s", $value['createtime']);
        $value['icon'] = tomedia($value['icon']);
        $value['sex'] = $value['sex'] == 1 ? '男' : '女';
        if ($leixing == 1) {
            $returnData['arrive'][] = $value; //到校
        } else if ($leixing == 2) {
            $returnData['leave'][] = $value; //离校
        } else {
            $returnData['absent'][] = $value; //缺勤
        }
    }
    $result['status'] = 10000;
    $result['msg'] = "获取数据成功";
    $result['data'] = $returnData;
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    exit;
}
