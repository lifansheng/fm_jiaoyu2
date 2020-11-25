<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */global $_W, $_GPC;
   $operation = in_array($_GPC ['op'], array('default','fabu','pxfabu','mfabu','zfabu','zfabupx','fangxue','sxcfb','xcfb','dellimg','savely','hfavely','hfavelyly','UpdateTypeByActiveId','SavePlanWeek','GetDetailByWeekDay','SendPlanWeek','DeleteWeekPlanByPlanUid','updatabypic','savedatabypicforplan','GetAttendData','GetAttendDataforTeacher','CheckSign','DoSign','fzqd','fzqdqr','checklogbyid','qingjialog','videodz','videopl','delmypl','getcook','zhuida','CheckSignForTeacher','DoSignForTeacher','delnotice','send_mail','mnotpro','notpro','pxnotpro','znotpro','znotpropx','t_piyue','GetHolidayData','get_noticeuser','get_noticebjtz','snotice_qbyd','uploadsence','get_notice_commont','post_notice_commont','delete_notice_commont','GetPlInfo','GetYqDkStuData','writePsychology')) ? $_GPC ['op'] : 'default';
    if ($operation == 'default') {
        die(json_encode(array(
            'result' => false,
            'msg' => '参数错误'
        )));
    }
    if ($operation == 'send_mail') { //课程预约试听
        if (! $_GPC ['schoolid'] || ! $_GPC ['weid']) {
            die(json_encode(array(
                'result' => false,
                'msg' => '非法请求！'
            )));
        }
        $name = $_GPC['name'];
        $tel  = $_GPC['telphone'];
        $tid = $_GPC['totid'];
        $starttime = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
        $endtime = $starttime + 86399;
        $condition = " AND createtime > '{$starttime}' AND createtime < '{$endtime}'";
        $checkyy = pdo_fetch("SELECT id FROM " . tablename($this->table_courseorder) . " where weid = '{$_GPC ['weid']}' And schoolid = '{$_GPC ['schoolid']}' And  fromuserid ='{$_GPC['fromuserid']}'  $condition ");
        if (empty($_GPC['beizhu'])) {
            die(json_encode(array(
                'result' => false,
                'msg' => '请输入留言内容！'
            )));
        }
        if (!empty($checkyy)) {
            die(json_encode(array(
                    'result' => false,
                    'msg' => '抱歉,今日您已经留言过了,明天再来吧！'
                       )));
        } else {
            $tuser =  pdo_fetch("SELECT id FROM " . tablename($this->table_user) . " where weid = '{$_GPC ['weid']}' And schoolid = '{$_GPC ['schoolid']}' And  tid = '{$tid}'");
            $datatemp = array(
                'name'       => $name,
                'tel'        => $tel,
                'beizhu'     => $_GPC['beizhu'],
                'weid'       => $_GPC['weid'],
                'schoolid'   => $_GPC['schoolid'],
                'type'       => 1, //1为信箱
                'totid'   => $tid,
                'fromuserid' => $_GPC['fromuserid'],
                'createtime' => time()
             );
            pdo_insert($this->table_courseorder, $datatemp);
            $insertid = pdo_insertid();
            $this->sendMobileYzxx($insertid, $_GPC['schoolid'], $_GPC['weid']);
            die(json_encode(array(
                'result' => true,
                'msg' => '邮件提交成功,请勿重复提交！'
            )));
        }
    }
    if ($operation == 'savely') {
        $data = explode('|', $_GPC ['json']);
        if (! $_GPC ['schoolid'] || ! $_GPC ['weid']) {
            die(json_encode(array(
                    'result' => false,
                    'msg' => '非法请求！'
                    )));
        }
        $setting = pdo_fetch("SELECT * FROM " . tablename($this->table_set) . " WHERE :weid = weid", array(':weid' => $_GPC['weid']));
        $user = pdo_fetch("SELECT status FROM " . tablename($this->table_user) . " WHERE :id = id", array(':id' => $_GPC['userid']));
        if ($user['status'] == 1) {
            die(json_encode(array(
                    'result' => false,
                    'msg' => '抱歉你已被禁言'
                       )));
        } else {
            $schoolid = $_GPC['schoolid'];
            $userid = $_GPC['userid'];
            $touserid = $_GPC['touserid'];
            $weid = $_GPC['weid'];
            $data = array(
                    'weid' =>  $weid,
                    'schoolid' => $schoolid,
                    'userid' => $userid,
                    'touserid' => $touserid,
                    'conet' => $_GPC ['content'],
                    'isfrist'=>1,
                    'isliuyan'=>2,
                    'createtime' => time()
            );
            $has_leave_id = pdo_fetch("SELECT * FROM ".GetTableName('leave')." WHERE schoolid = '{$schoolid}' AND isliuyan = 2 AND isfrist = 1 AND ((userid = '{$userid}' AND touserid = '{$touserid}') OR (userid = '{$touserid}' AND touserid = '{$userid}')) ");
            pdo_insert($this->table_leave, $data);
            $insertid = pdo_insertid();
            if ($has_leave_id) {
                $leave_id = $has_leave_id['leaveid'];
                pdo_update($this->table_leave, array('leaveid' =>  $leave_id,'isfrist'=>0), array('id' =>  $insertid));
            } else {
                $leave_id = $insertid;
                pdo_update($this->table_leave, array('leaveid' =>  $leave_id), array('id' =>  $insertid));
            }
            $this->sendMobileLyhf($leave_id, $schoolid, $weid);
            $data ['result'] = true;
            $data ['msg'] = '成功发送留言信息，请勿重复发送！';
            die(json_encode($data));
        }
    }
    if ($operation == 'delete_notice_commont') {
        if (! $_GPC ['schoolid'] || ! $_GPC ['weid']) {
            die(json_encode(array(
                    'result' => false,
                    'msg' => '非法请求！'
                    )));
        }
        $weid = $_GPC ['weid'];
        $schoolid = $_GPC ['schoolid'];
        $userid = $_GPC ['userid'];
        $commentid = $_GPC ['commentid'];
        $comment = pdo_fetch("SELECT * FROM " . GetTableName('notice_comment') . " WHERE :id = id", array(':id' => $commentid));
        if (!$comment) {
            die(json_encode(array(
                    'result' => false,
                    'msg' => '该评论已删除'
                       )));
        } else {
            $rep = pdo_fetch("SELECT * FROM " . GetTableName('notice_comment') . " WHERE :commentid = commentid", array(':commentid' => $commentid));
            if ($rep) {
                pdo_delete(GetTableName('notice_comment', false), array('commentid' => $commentid));
            }
            pdo_delete(GetTableName('notice_comment', false), array('id' => $commentid));
            $data ['result'] = true;
            $data ['msg'] = '删除成功！';
            die(json_encode($data));
        }
    }
    if ($operation == 'post_notice_commont') {
        if (! $_GPC ['schoolid'] || ! $_GPC ['weid']) {
            die(json_encode(array(
                    'result' => false,
                    'msg' => '非法请求！'
                )));
        }
        $weid = $_GPC ['weid'];
        $schoolid = $_GPC ['schoolid'];
        $userid = $_GPC ['userid'];
        $noticeid = $_GPC ['noticeid'];
        $commentid = $_GPC ['commentid'];
        $user = pdo_fetch("SELECT status,openid FROM " . GetTableName('user') . " WHERE :id = id", array(':id' => $userid));
        if ($user['status'] == 1) {
            die(json_encode(array(
                    'result' => false,
                    'msg' => '抱歉你已被禁言'
                       )));
        } else {
            $datas = array(
                'weid' =>  $weid,
                'schoolid' => $schoolid,
                'userid' => $userid,
                'noticeid' => $noticeid,
                'comment' => $_GPC['comment'],
                'commentid' => $commentid,
                'createtime' => time()
            );
            pdo_insert(GetTableName('notice_comment', false), $datas);
            $leave_id = pdo_insertid();
            $fans_info = mc_fansinfo($user['openid']);
            $data ['icon'] = $fans_info['headimgurl'];
            $data ['result'] = true;
            $data ['msg'] = '留言成功，请勿重复发送！';
            die(json_encode($data));
        }
    }
    if ($operation == 'get_notice_commont') {
        global $_W;
        if (! $_GPC ['schoolid'] || ! $_GPC ['weid']) {
            die(json_encode(array(
                    'result' => false,
                    'msg' => '非法请求！'
                )));
        }
        $weid = $_GPC ['weid'];
        $schoolid = $_GPC ['schoolid'];
        $userid = $_GPC ['userid'];
        $noticeid = $_GPC ['noticeid'];
        $school = pdo_fetch("SELECT spic,tpic FROM " .  GetTableName('index') . " WHERE :id = id", array(':id' => $schoolid));
        $user = pdo_fetch("SELECT * FROM " .  GetTableName('user') . " WHERE :id = id", array(':id' => $userid));
        $notice = pdo_fetch("SELECT tid,comment FROM " . GetTableName('notice') . " WHERE :id = id", array(':id' => $noticeid));
        $is_owner = false;
        if ($user['tid'] == $notice['tid']) {
            $is_owner = true;
        }
        $count = 0;
        if ($notice['comment'] == 2 || $notice['comment'] == 3) {
            mload()->model('stu');
            $comment = pdo_fetchall("SELECT * FROM " . GetTableName('notice_comment') . " WHERE :noticeid = noticeid And :commentid = commentid ORDER BY createtime DESC", array(':noticeid' => $noticeid,':commentid' => 0));
            $count = count($comment);
            foreach ($comment as $key => $row) {
                $userinfo = pdo_fetch("SELECT sid,tid,pard,openid FROM " . GetTableName('user') . " WHERE :id = id", array(':id' => $row['userid']));
                if ($userinfo['sid'] != 0) {
                    $guanxi = get_guanxi($userinfo['pard']);
                    $student = pdo_fetch("SELECT s_name,icon,bj_id FROM " . GetTableName('students') . " WHERE :id = id", array(':id' => $userinfo['sid']));
                    $bj = '('.GetClassNameByBjId($student['bj_id'], $_W['schooltype']).')';
                    $comment[$key]['username'] = $student['s_name'].$guanxi.$bj;
                    if ($userinfo['sid'] == 4) {
                        $comment[$key]['icon'] = !empty($student['icon'])?tomedia($student['icon']):tomedia($school['spic']);
                    } else {
                        $fans_info1 = mc_fansinfo($userinfo['openid']);
                        $comment[$key]['icon'] = $fans_info1['headimgurl'];
                    }
                }
                if ($userinfo['tid'] != 0) {
                    $teacher = pdo_fetch("SELECT tname,thumb FROM " . GetTableName('teachers') . " WHERE :id = id", array(':id' => $userinfo['tid']));
                    $comment[$key]['username'] = $teacher['tname'].'老师';
                    $comment[$key]['icon'] = !empty($teacher['thumb'])?tomedia($teacher['thumb']):tomedia($school['tpic']);
                }
                $comment[$key]['is_my'] = false;
                if ($row['userid'] == $userid) {
                    $comment[$key]['is_my'] = true;
                }
                $comment[$key]['time'] = date('Y-m-d H:i:s', $row['createtime']);
                $reply = pdo_fetchall("SELECT * FROM " . GetTableName('notice_comment') . " WHERE :commentid = commentid", array(':commentid' => $row['id']));
                if ($reply) {
                    foreach ($reply as $k => $r) {
                        $userinfos = pdo_fetch("SELECT sid,tid,pard,openid FROM " . GetTableName('user') . " WHERE :id = id", array(':id' => $r['userid']));
                        if ($userinfos['sid'] != 0) {
                            $guanxi1 = get_guanxi($userinfos['pard']);
                            $student1 = pdo_fetch("SELECT s_name,bj_id FROM " . GetTableName('students') . " WHERE :id = id", array(':id' => $userinfos['sid']));
                            $bjs = '('.GetClassNameByBjId($student1['bj_id'], $_W['schooltype']).')';
                            $reply[$k]['username'] = $student1['s_name'].$guanxi1.$bjs;
                        }
                        if ($userinfos['tid'] != 0) {
                            $teacher1 = pdo_fetch("SELECT tname FROM " . GetTableName('teachers') . " WHERE :id = id", array(':id' => $userinfos['tid']));
                            $reply[$k]['username'] = $teacher1['tname'];
                        }
                        $reply[$k]['time'] = date('Y-m-d H:i:s', $r['createtime']);
                        $reply[$k]['is_my'] = false;
                        if ($r['userid'] == $userid) {
                            $reply[$k]['is_my'] = true;
                        }
                        if ($notice['comment'] == 3) {
                            if (!$is_owner && $row['userid'] != $userid) {
                                $reply[$k]['comment'] = "评论仅发布者可见";
                            }
                        }
                    }
                }
                if ($notice['comment'] == 3) {
                    if (!$is_owner && $row['userid'] != $userid) {
                        $reply[$k]['comment'] = "评论仅发布者可见";
                    }
                }
                $comment[$key]['reply'] = $reply;
            }
            $data ['count'] = $count;
            $data ['is_owner'] = $is_owner;//发布者
            $data ['comment'] = $comment;
            $data ['result'] = true;
            $data ['msg'] = '评论功能已开启';
            die(json_encode($data));
        } else {
            $data ['result'] = false;
            $data ['msg'] = '关闭了评论';
            die(json_encode($data));
        }
    }
    if ($operation == 'hfavely') {
        $data = explode('|', $_GPC ['json']);
        $setting = pdo_fetch("SELECT * FROM " . tablename($this->table_set) . " WHERE :weid = weid", array(':weid' => $_GPC['weid']));
        $user = pdo_fetch('SELECT status FROM ' . tablename($this->table_user) . ' WHERE id = :id ', array(':id' => $_GPC ['userid']));
        if ($user['status'] ==1) {
            die(json_encode(array(
                    'result' => false,
                    'msg' => '你已经被管理员禁言！'
                )));
        }
        if (empty($_GPC['openid'])) {
            die(json_encode(array(
                    'result' => false,
                    'msg' => '非法请求！'
                       )));
        } else {
            $contenttype = trim($_GPC['contenttype']);
            $schoolid = $_GPC['schoolid'];
            $userid = $_GPC['userid'];
            $touserid = $_GPC['touserid'];
            $weid = $_GPC['weid'];
            if ($contenttype == 'media') {
                $audio = $_GPC['audioServerid'];
                $audioTime = $_GPC['audioTime'];
                if ($audio) {
                    $versionfile = IA_ROOT . '/addons/fm_jiaoyu/inc/func/auth2.php';
                    require $versionfile;
                    $mp3name = str_replace('images/bjq/vioce/', '', $audio);
                    $mp3 = str_replace('.mp3', '', $mp3name);
                    delvioce($mp3, FM_JIAOYU_HOST);
                }
                $data = array(
                    'weid' =>  $weid,
                    'schoolid' => $schoolid,
                    'leaveid' =>  $_GPC['id'],
                    'userid' => $userid,
                    'touserid' => $touserid,
                    'isliuyan'=>2,
                    'createtime' => time()
                );
                $audios = array(
                    'audio' =>  $audio,
                    'audioTime' => $audioTime,
                );
                $data['audio'] = iserializer($audios);
                $urls = $_W['attachurl'];
                $datas ['mediafile'] = $urls.$audio[0];
                $datas ['mediatime'] = $audioTime;
            } elseif ($contenttype == 'img') {
                load()->func('communication');
                load()->func('file');
                $token2 = $this->getAccessToken2();
                $photoUrl = $_GPC ['imgServerid'];
                if (!empty($photoUrl)) {
                    $url = 'https://file.api.weixin.qq.com/cgi-bin/media/get?access_token='.$token2.'&media_id='.$photoUrl;
                    $pic_data = ihttp_request($url);
                    $path = "images/fm_jiaoyu/";
                    $picurl = $path.random(30) .".jpg";
                    file_write($picurl, $pic_data['content']);
                    //$files = IA_ROOT . "/attachment/".$picurl;
                    //cut($files);
                    if (!empty($_W['setting']['remote']['type'])) { //
                        $remotestatus = file_remote_upload($picurl); //
                        if (is_error($remotestatus)) {
                            $datas ['result'] = false;
                            $datas ['msg'] = '发送图片失败！';
                            die(json_encode($datas));
                        }
                    }
                }
                $data = array(
                    'weid' =>  $weid,
                    'schoolid' => $schoolid,
                    'leaveid' =>  $_GPC['id'],
                    'userid' => $userid,
                    'touserid' => $touserid,
                    'isliuyan'=>2,
                    'createtime' => time()
                );
                $data ['picurl'] = $picurl;
                $datas ['mediafile'] = tomedia($picurl);
            } else {
                $data = array(
                    'weid' =>  $weid,
                    'schoolid' => $schoolid,
                    'leaveid' =>  $_GPC['id'],
                    'userid' => $userid,
                    'touserid' => $touserid,
                    'conet' => $_GPC['content'],
                    'isliuyan'=>2,
                    'createtime' => time()
                );
            }
            
            pdo_insert($this->table_leave, $data);
            $leave_id = pdo_insertid();
            $this->sendMobileLyhf($leave_id, $schoolid, $weid);
            $datas ['result'] = true;
            $datas ['msg'] = '成功发送留言信息，请勿重复发送！';
            die(json_encode($datas));
        }
    }
    
    if ($operation == 'hfavelyly') {
        $data = explode('|', $_GPC ['json']);
        $setting = pdo_fetch("SELECT * FROM " . tablename($this->table_set) . " WHERE :weid = weid", array(':weid' => $_GPC['weid']));
        $user = pdo_fetch('SELECT status FROM ' . tablename($this->table_user) . ' WHERE id = :id ', array(':id' => $_GPC ['userid']));
        
        if ($user['status'] ==1) {
            die(json_encode(array(
                    'result' => false,
                    'msg' => '你已经被管理员禁言！'
                )));
        }
        if (empty($_GPC['openid'])) {
            die(json_encode(array(
                    'result' => false,
                    'msg' => '非法请求！'
                       )));
        } else {
            $contenttype = trim($_GPC['contenttype']);
            $schoolid = $_GPC['schoolid'];
            $bj_id = $_GPC['bj_id'];
            $userid = $_GPC['userid'];
            $touserid = $_GPC['touserid'];
            $weid = $_GPC['weid'];
            if ($contenttype == 'media') {
                $audio = $_GPC['audioServerid'];
                $audioTime = $_GPC['audioTime'];
                if ($audio) {
                    $versionfile = IA_ROOT . '/addons/fm_jiaoyu/inc/func/auth2.php';
                    require $versionfile;
                    $mp3name = str_replace('images/bjq/vioce/', '', $audio);
                    $mp3 = str_replace('.mp3', '', $mp3name);
                    delvioce($mp3, FM_JIAOYU_HOST);
                }
                $data = array(
                    'weid' =>  $weid,
                    'schoolid' => $schoolid,
                    'bj_id' => $bj_id,
                    'leaveid' =>  $_GPC['id'],
                    'userid' => $userid,
                    'touserid' => $touserid,
                    'isliuyan'=>2,
                    'createtime' => time()
                );
                $audios = array(
                    'audio' =>  $audio,
                    'audioTime' => $audioTime,
                );
                $data['audio'] = iserializer($audios);
                $urls = $_W['attachurl'];
                $datas ['mediafile'] = tomedia($audio[0]);
                $datas ['mediatime'] = $audioTime;
            } elseif ($contenttype == 'img') {
                load()->func('communication');
                load()->func('file');
                $token2 = $this->getAccessToken2();
                $photoUrl = $_GPC ['imgServerid'];
                if (!empty($photoUrl)) {
                    $url = 'https://file.api.weixin.qq.com/cgi-bin/media/get?access_token='.$token2.'&media_id='.$photoUrl;
                    $pic_data = ihttp_request($url);
                    $path = "images/fm_jiaoyu/";
                    $picurl = $path.random(30) .".jpg";
                    file_write($picurl, $pic_data['content']);
                    //$files = IA_ROOT . "/attachment/".$picurl;
                    //cut($files);
                    if (!empty($_W['setting']['remote']['type'])) { //
                        $remotestatus = file_remote_upload($picurl); //
                        if (is_error($remotestatus)) {
                            $datas ['result'] = false;
                            $datas ['msg'] = '发送图片失败！';
                            die(json_encode($datas));
                        }
                    }
                }
                $data = array(
                    'weid' =>  $weid,
                    'schoolid' => $schoolid,
                    'bj_id' => $bj_id,
                    'leaveid' =>  $_GPC['id'],
                    'userid' => $userid,
                    'touserid' => $touserid,
                    'isliuyan'=>2,
                    'createtime' => time()
                );
                $data ['picurl'] = $picurl;
                $datas ['mediafile'] = tomedia($picurl);
            } else {
                $data = array(
                    'weid' =>  $weid,
                    'schoolid' => $schoolid,
                    'bj_id' => $bj_id,
                    'leaveid' =>  $_GPC['id'],
                    'userid' => $userid,
                    'touserid' => $touserid,
                    'conet' => $_GPC['content'],
                    'isliuyan'=>2,
                    'createtime' => time()
                );
            }
             
            pdo_insert($this->table_liuyan, $data);
            $leave_id = pdo_insertid();
            
            $this->sendMobileLyhf($leave_id, $schoolid, $weid);
            $datas ['result'] = true;
            $datas ['msg'] = '成功发送留言信息，请勿重复发送！';
            /**极光推送 start*/
            $bj = pdo_fetch('SELECT macid FROM ims_wx_school_classcard_mac  WHERE bj_id = :bj_id ', array(':bj_id' => $bj_id));
            $user_openid = pdo_fetch('SELECT openid FROM ' . tablename($this->table_user) . ' WHERE id = :id ', array(':id' => $userid));

            if ($datas['mediafile']!=''||!empty($rs['mediafile'])) {
                $message='{"messageId":"'.$leave_id.'",
    "studentId":"'.$_GPC['id'].'",
    "UnionID":"'.$user_openid['openid'].'",
    "relationship":"'.$weid.'",
     "seconds":"'.$_GPC['audioTime'][0].'",
    "type":"voice",
    "info":"'.$datas['mediafile'].'"}';
            } else {
                $message='{"messageId":"'.$leave_id.'",
    "studentId":"'.$_GPC['id'].'",
    "UnionID":"'.$user_openid['openid'].'",
    "relationship":"'.$weid.'",
    "type":"word",
    "info":"'.$data['conet'].'"}';
            }
     
            $jpush_client_url=IA_ROOT . '/addons/fm_jiaoyu/inc/func//jpush/jpush/autoload.php';
            require $jpush_client_url;
            $app_key='7e223b92c7c8fabe984e2883';
            $master_secret='367551be6399b6f5d992fb91';
            $client = new JPush\Client($app_key, $master_secret);
            $client->push()
                   ->setPlatform(array('ios', 'android'))
                   ->message($message)
                   //->addAlias('28a57998ea4f5038')
                   ->addAlias($bj['macid'])
                   ->send();
            /**极光推送结束*/
            $baidu_client_url=IA_ROOT . '/addons/fm_jiaoyu/inc/func/BaiduPush/sdk.php';
            require $baidu_client_url;
            $sdk = new \PushSDK();
            $channelId = $bj['macid'];
            $opts = array(
                'msg_type' => 0
            );
            $rs=$sdk->pushMsgToTag($channelId, $message, $opts);

            /***百度推送*/
            /***百度推送end */
            /***友盟推送**/
            $appMasterSecret='cujeenoqrx5z6zqx7fxgxos2giqgbmbn';
            $appkey='5ddf4cd24ca3570f00000504';
            $ym_client_url=IA_ROOT . '/addons/fm_jiaoyu/inc/func/notification/android/AndroidCustomizedcast.php';
            require $ym_client_url;
            $customizedcast = new \AndroidCustomizedcast();
            $customizedcast->setAppMasterSecret($appMasterSecret);
            $customizedcast->setPredefinedKeyValue("appkey", $appkey);
            $customizedcast->setPredefinedKeyValue("timestamp", strval(time()));
            $customizedcast->setPredefinedKeyValue("alias", $bj['macid']);
            $customizedcast->setPredefinedKeyValue("alias_type", "DEVICE_ID");
            $customizedcast->setPredefinedKeyValue("ticker", "Android customizedcast ticker");
            $customizedcast->setPredefinedKeyValue("custom", $message);
            $customizedcast->setPredefinedKeyValue("display_type", 'message');
            $customizedcast->send();
            /******/
            die(json_encode($datas));
        }
    }
    if ($operation == 'fabu') {
        load()->func('communication');
        load()->func('file');
        // $photoUrls = explode ( ',', $_GPC ['photoUrls'] );
        $data = explode('|', $_GPC ['json']);
        if ($_GPC ['photoUrls']) {
            $picurl = array();
            $photo = $_GPC ['photoUrls'];
            for ($i = 0; $i <= 9 ; $i++) {
                if (!empty($photo[$i])) {
                    $picurl[$i] = $photo[$i];
                }
            }
            $picstr = array('p1' => $picurl[0],'p2' => $picurl[1],'p3' => $picurl[2],'p4' => $picurl[3],'p5' => $picurl[4],'p6' => $picurl[5],'p7' => $picurl[6],'p8' => $picurl[7],'p9' => $picurl[8],);
        }
        $video = '';
        if (!empty($_GPC['videoMediaId'])) {
            $msgtype = 3;//视频
            mload()->model('ali');
            $aliyun = GetAliApp($_W['uniacid'], $_GPC['schoolid']);
            if ($aliyun['result']) {
                $appid = $aliyun['alivodappid'];
                $key = $aliyun['alivodkey'];
                do {
                    $GetAliVideoUrl = GetAliVideoUrl($appid, $key, trim($_GPC['videoMediaId']));
                } while (empty($GetAliVideoUrl['PlayURL']));
                do {
                    $GetAliVideoCover = GetAliVideoCover($appid, $key, trim($_GPC['videoMediaId']));
                } while (empty($GetAliVideoCover['CoverURL']));
                $video = $GetAliVideoUrl['PlayURL'];
                $videoimg = $GetAliVideoCover['CoverURL'];
            }
        }
        $audios = $_GPC ['audioServerid'];
        $audio = $audios[0];
        if ($audio) {
            $versionfile = IA_ROOT . '/addons/fm_jiaoyu/inc/func/auth2.php';
            require $versionfile;
            $mp3name = str_replace('images/bjq/vioce/', '', $audio);
            $mp3 = str_replace('.mp3', '', $mp3name);
            delvioce($mp3, FM_JIAOYU_HOST);
        }
        if (! $_GPC ['schoolid'] || ! $_GPC ['weid']) {
            die(json_encode(array(
                    'result' => false,
                    'msg' => '非法请求！'
                       )));
        } else {
            if (empty($_GPC['openid'])) {
                die(json_encode(array(
                    'result' => false,
                    'msg' => '非法请求,请刷新页面！'
                       )));
            } else {
                $schoolid = $_GPC['schoolid'];
                $schooltype = $_GPC['schooltype'];
                $title = $_GPC['title'];
                $weid = $_GPC['weid'];
                $content = Emoji($_GPC['content']);
                $tid = $_GPC['tid'];
                $uid = $_GPC['uid'];
                $openid = $_GPC['openid'];
                $usertype = $_GPC['type'];
                $userdatas = $_GPC['datas'];
                $audios = $_GPC ['audioServerid'];
                $audio = $audios[0];
                $audiotimes = $_GPC['audioTime'];
                $audiotime = $audiotimes[0];
                $tname = $_GPC['tname'];
                $shername = $tname;
                $is_private = trim($_GPC['is_private']);
                $is_pl = trim($_GPC['is_pl']);
                $is_see = trim($_GPC['is_see']);
                if ($is_pl == 'Y') {
                    $comment = 2;//允许评论
                    if ($is_see == 'Y') {
                        $comment = 3;//允许评论且仅作者可见
                    }
                } else {
                    $comment = 1;//禁止评论
                }
                if ($usertype == 'student') {
                    $datass = str_replace('&quot;', '"', $_GPC['datas']);
                    $userdatas = json_decode($datass, true);
                }
                if ($usertype == 'send_class') {
                    $userdatas = explode(',', trim($_GPC['datas'], ','));
                }
                
                foreach ($userdatas as $key => $row) {
                    $jsondatas = json_encode(array(
                        "{$key}"=> "{$row}"
                    ));
                    if ($row != '') {
                        if ($usertype == 'send_class') {
                            if ($schooltype) {
                                $bj_id = trim($row, ',');
                            } else {
                                $bj_id = $row;
                            }
                        }
                        if ($usertype == 'student') {
                            $bj_id = $key;
                        }
                        if ($is_private == 'Y') {
                            $bjqdata = array(
                                'weid' =>  $weid,
                                'schoolid' => $schoolid,
                                'uid' => $uid,
                                'shername' => $shername,
                                'audio' => $audio,
                                'audiotime' => $audiotime,
                                'content' => $content,
                                'video' => $video,
                                'openid'=>$openid,
                                'isopen'=>0,
                                'is_private'=>'N',
                                'createtime' => time(),
                                'msgtype'=>7,
                                'type'=>0,
                            );
                            if ($schooltype) {
                                $bjqdata['kc_id']	= $bj_id;
                            } else {
                                $bjqdata['bj_id1']	= $bj_id;
                            }
                            pdo_insert($this->table_bjq, $bjqdata);
                            $bjq_id = pdo_insertid();
                            $data1 = array(
                                'sherid'=>$bjq_id,
                            );
                            pdo_update($this->table_bjq, $data1, array('id' => $bjq_id));
                            if ($_GPC ['photoUrls']) {
                                $photoUrl = $_GPC ['photoUrls'];
                                $order = 1;
                                foreach ($photoUrl as $key => $v) {
                                    if (!empty($v)) {
                                        $data = array(
                                            'weid' =>  $weid,
                                            'schoolid' => $schoolid,
                                            'uid' => $uid,
                                            'picurl' => $v,
                                            'order'=>$order,
                                            'sherid'=>$bjq_id,
                                            'createtime' => time(),
                                        );
                                        if ($schooltype) {
                                            $data['kc_id']	= $bj_id;
                                        } else {
                                            $data['bj_id1']	= $bj_id;
                                        }
                                        pdo_insert($this->table_media, $data);
                                    }
                                    $order++;
                                }
                            }
                        }
                        $temp = array(
                            'weid' =>  $weid,
                            'schoolid' => $schoolid,
                            'tid' => $tid,
                            'tname' => $tname,
                            'title' => $title,
                            'video' => $video,
                            'videopic' => $videoimg,
                            'audio' => $audio,
                            'ali_vod_id' => trim($_GPC['videoMediaId']),
                            'audiotime' => $audiotime,
                            'content' => $content,
                            'comment' => $comment,
                            'usertype' => $usertype,
                            'createtime' => time(),
                            'type'=>1
                        );
                        
                        $temp['bj_id']	= $bj_id;
                        $temp['picarr'] = iserializer($picstr);
                        if ($usertype == 'student') {
                            $temp['userdatas'] =  rtrim($jsondatas, ',');
                        }
                        pdo_insert($this->table_notice, $temp);
                        $notice_id = pdo_insertid();
                        $url = $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&m=fm_jiaoyu&do=sendMsg';
                        $sendData = array(
                            'notice_id' => $notice_id,
                            'schoolid' => $schoolid,
                            'weid' => $weid,
                            'tname' => $tname,
                            'op' => 'sendMobileNewBjtz',
                        );
                        timeOutPost($url, $sendData);
                    }
                }
                if (keep_Bjq()) {
                    mload()->model('hra');
                    newsSync($notice_id);
                }
                $result ['status'] = 1;
                $result ['result'] = true;
                $result ['msg'] = '正在执行发送任务！';
                $customData = array(
                    'touser' => "{$_GPC['openid']}",
                    'msgtype'=> 'text',
                    'text'=> array(
                        'content' => "尊敬的-{$tname}老师，您发布的班级通知({$title})已全部发送成功！"
                    )
                );
                $this->sendCustomNotice($customData);
                $actop = 'bjtz';
                $userid = $_GPC['userid'];
                $point = PointAct($weid, $schoolid, $userid, $actop);
                $point1 = PointMission($weid, $schoolid, $userid, $actop);
                if (!empty($point)) {
                    $result ['msg'] = '群发成功，请勿重复操作!积分+'.$point;
                }
            }
            die(json_encode($result));
        }
    }
    
    if ($operation == 'notpro') {
        $schoolid = $_GPC['schoolid'];
        $weid = $_GPC['weid'];
        $tname = $_GPC['tname']; //老师名称
        $schooltype = $_GPC['schooltype'];
        $usertype = $_GPC['usertype']; //发送类型
        $bjarr = $_GPC['bjarr']; //班级数组
        $stuarr = $_GPC['stuarr'];
        $noticeidarr = $_GPC['noticeidarr'];
        $total = $_GPC['total'];
        $pindex = max(1, intval($_GPC['page']));
        $psize = 2;
        $tp = ceil($total/$psize);
        //echo '第' . $pindex . '次,总共'.$tp.'次';
        if ($pindex <= $tp) {
            session_start();
            if ($usertype == 'send_class') {
                if ($_SESSION['arr'] || $_SESSION['arr'] != "") {
                    $arr = $_SESSION['arr'];
                } else {
                    mload()->model('stu');
                    $arr = StuInfoByclassArr($bjarr, $schoolid, $schooltype);
                    $_SESSION['arr'] = $arr;
                }
                $this->sendMobileBjtzToUserArr($schoolid, $schooltype, $weid, $tname, $arr, $noticeidarr, 'tostu', $pindex, $psize);
            }
            if ($usertype == 'student') {
                if ($_SESSION['arr'] || $_SESSION['arr'] != "") {
                    $arr = $_SESSION['arr'];
                } else {
                    $arr = $stuarr;
                    $_SESSION['arr'] = $arr;
                }
                $this->sendMobileBjtzToUserArr($schoolid, $schooltype, $weid, $tname, $arr, $noticeidarr, 'tostu', $pindex, $psize);
            }
            $mq = round(($pindex / $tp) * 100);
            $msg = '已发送' . $mq . ' %';
            $page = $pindex + 1;
            $data ['pro'] = $msg;
            $data ['arr'] = $arr;
            $data ['bjarr'] = $bjarr;
            $data ['stuarr'] = $stuarr;
            $data ['noticeidarr'] = $noticeidarr;
            $data ['page'] = $page;
            $data ['count'] = count($arr);
            $data ['status'] = 1;
        } else {
            $data ['status'] = 2;
            $_SESSION['arr'] = "";
            $arr = $_SESSION['arr'];
            $data ['userdatas'] = $arr;
        }
        die(json_encode($data));
    }


    if ($operation == 'get_noticebjtz') {
        $schooltype = $_GPC['schooltype'];
        $schoolid = $_GPC['schoolid'];
        $tid = $_GPC['tid'];
        $usertype = $_GPC['type'];
        if ($usertype == "student") {
            mload()->model('tea');
            if ($schooltype) {
                $list = GetNewAllClassInfoByTid($schoolid, 2, $schooltype, $tid);
            } else {
                $list = GetAllClassStuInfoByTid($schoolid, 2, $schooltype, $tid);
            }
        }
        if ($usertype == "send_class") {
            mload()->model('tea');
            $list = GetNewAllClassInfoByTid($schoolid, 2, $schooltype, $tid);
        }
        $school = pdo_fetch("SELECT spic FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
        if ($schooltype) {
            include $this->template('comtool/notice_px_bjtz');
        } else {
            include $this->template('comtool/notice_bjtz');
        }
    }
    if ($operation == 'mfabu') {
        load()->func('communication');
        load()->func('file');
        $data = explode('|', $_GPC ['json']);
        if ($_GPC ['photoUrls']) {
            $picurl = array();
            $photo = $_GPC ['photoUrls'];
            for ($i = 0; $i <= 9 ; $i++) {
                if (!empty($photo[$i])) {
                    $picurl[$i] = $photo[$i];
                }
            }
            $picstr = array('p1' => $picurl[0],'p2' => $picurl[1],'p3' => $picurl[2],'p4' => $picurl[3],'p5' => $picurl[4],'p6' => $picurl[5],'p7' => $picurl[6],'p8' => $picurl[7],'p9' => $picurl[8],);
        }
        $video = '';
        if (!empty($_GPC['videoMediaId'])) {
            $msgtype = 3;//视频
            mload()->model('ali');
            $aliyun = GetAliApp($_W['uniacid'], $_GPC['schoolid']);
            if ($aliyun['result']) {
                $appid = $aliyun['alivodappid'];
                $key = $aliyun['alivodkey'];
                do {
                    $GetAliVideoUrl = GetAliVideoUrl($appid, $key, trim($_GPC['videoMediaId']));
                } while (empty($GetAliVideoUrl['PlayURL']));
                do {
                    $GetAliVideoCover = GetAliVideoCover($appid, $key, trim($_GPC['videoMediaId']));
                } while (empty($GetAliVideoCover['CoverURL']));
                $video = $GetAliVideoUrl['PlayURL'];
                $videoimg = $GetAliVideoCover['CoverURL'];
            }
        }
        $audios = $_GPC ['audioServerid'];
        
        $audio = $audios[0];
        if ($audio) {
            $versionfile = IA_ROOT . '/addons/fm_jiaoyu/inc/func/auth2.php';
            require $versionfile;
            $mp3name = str_replace('images/bjq/vioce/', '', $audio);
            $mp3 = str_replace('.mp3', '', $mp3name);
            delvioce($mp3, FM_JIAOYU_HOST);
        }
        
        $setting = pdo_fetch("SELECT * FROM " . tablename($this->table_set) . " WHERE :weid = weid", array(':weid' => $_GPC['weid']));
        if (! $_GPC ['schoolid'] || ! $_GPC ['weid']) {
            die(json_encode(array(
                    'result' => false,
                    'msg' => '非法请求！'
                       )));
        } else {
            if (empty($_GPC['openid'])) {
                die(json_encode(array(
                    'result' => false,
                    'msg' => '非法请求,请刷新页面！'
                       )));
            } else {
                $schoolid = $_GPC['schoolid'];
                $title = $_GPC['title'];
                $weid = $_GPC['weid'];
                $content = $_GPC['content'];
                $is_pl = trim($_GPC['is_pl']);
                $is_see = trim($_GPC['is_see']);
                if ($is_pl == 'Y') {
                    $comment = 2;//允许评论
                    if ($is_see == 'Y') {
                        $comment = 3;//允许评论且仅作者可见
                    }
                } else {
                    $comment = 1;//禁止评论
                }
                $schooltype = $_GPC['schooltype'];
                $tid = $_GPC['tid'];
                if ($_GPC['type'] == 'school') {
                    $groupid = 1;
                }
                if ($_GPC['type'] == 'alltea') {
                    $groupid = 2;
                }
                if ($_GPC['type'] == 'allstu') {
                    $groupid = 3;
                }
                if ($_GPC['type'] == 'send_class') {
                    $groupid = 4;
                }
                if ($_GPC['type'] == 'student') {
                    $groupid = 5;
                }
                if ($_GPC['type'] == 'staff_jsfz') {
                    $groupid = 6;
                }
                if ($_GPC['type'] == 'staff') {
                    $groupid = 7;
                }
                $audios = $_GPC ['audioServerid'];
                
                $audio = $audios[0];
                
                $audiotimes = $_GPC['audioTime'];
                
                $audiotime = $audiotimes[0];
                
                $tname = $_GPC['tname'];
                
                $is_private = trim($_GPC['is_private']);
                $temp = array(
                    'weid' =>  $weid,
                    'schoolid' => $schoolid,
                    'tid' => $tid,
                    'tname' => $tname,
                    'title' => $title,
                    'video' => $video,
                    'videopic' => $videoimg,
                    'audio' => $audio,
                    'ali_vod_id' => trim($_GPC['videoMediaId']),
                    'audiotime' => $audiotime,
                    'content' => $content,
                    'comment' => $comment,
                    'createtime' => time(),
                    'type'=>2,
                    'groupid'=>$groupid,
                    'usertype'=>$_GPC['type'],
                    'userdatas'=>rtrim($_GPC['datas'], ';')
                );
                $temp['picarr'] = iserializer($picstr);
                if ($is_private == 'Y') {
                    if ($picurl[0]) {
                        $thumb = $picurl[0];
                    } else {
                        $thumb = $school['logo'];
                    }
                    $lastnews = pdo_fetch("SELECT displayorder FROM " . tablename($this->table_news) . " WHERE :schoolid = schoolid And :type = type ORDER BY displayorder DESC LIMIT 1", array(':schoolid' => $schoolid,':type' => 'article'));
                    $displayorder = $lastnews['displayorder'] + 1;
                    $news = array(
                        'weid' => $weid,
                        'schoolid' => $schoolid,
                        'title' => $title,
                        'content' => $content,
                        'thumb' => $thumb,
                        'description' => $content,
                        'author' => $tname,
                        'is_display' => 1,
                        'is_show_home' => 1,
                        'type' => 'article',
                        'displayorder' => $displayorder,
                        'createtime' => time(),
                    );
                    $news['picarr'] = iserializer($picstr);
                    pdo_insert($this->table_news, $news);
                }
                pdo_insert($this->table_notice, $temp);
                $notice_id = pdo_insertid();
                
                if (keep_Bjq()) {
                    mload()->model('hra');
                    newsSync($notice_id);
                }

                if ($_GPC['type'] == 'school' || $_GPC['type'] == 'alltea' || $_GPC['type'] == 'allstu') {
                    $url = $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&m=fm_jiaoyu&do=sendMsg';
                    $data = array(
                        'notice_id' => $notice_id,
                        'schoolid' => $schoolid,
                        'weid' => $weid,
                        'tname' => $tname,
                        'schooltype' => $schooltype,
                        'op' => 'sendMobileNewXytz',
                    );
                    timeOutPost($url, $data);
                } else {
                    $rtrim = rtrim($_GPC['datas'], ';');
                    $userdatas = explode(';', $rtrim);
                    if ($_GPC['type'] == 'send_class') {
                        $total = 0;
                        foreach ($userdatas as $row) {
                            if ($row == 0 || $row != "") {
                                if ($schooltype) {
                                    $nowtotal = pdo_fetchcolumn("SELECT COUNT( distinct sid ) FROM ".tablename($this->table_order)." where schoolid = '{$schoolid}' and kcid = '{$row}' and type = 1 and status = 2 and sid != 0  ");
                                    $total = $total + $nowtotal;
                                } else {
                                    $nowtotal = pdo_fetchcolumn("SELECT COUNT(1) FROM ".tablename($this->table_students)." where bj_id = :bj_id And schoolid = :schoolid", array(':bj_id'=>$row, ':schoolid'=>$schoolid));
                                    $total = $total + $nowtotal;
                                }
                            }
                        }
                    }
                    if ($_GPC['type'] == 'student') {
                        $total = 0;
                        foreach ($userdatas as $row) {
                            if ($row == 0 || $row != "") {
                                $total++;
                            }
                        }
                    }
                    if ($_GPC['type'] == 'staff_jsfz') {
                        foreach ($userdatas as $row) {
                            if ($row == 0 || $row != "") {
                                $staff_jsfz[] = $row;
                            }
                        }
                        mload()->model('tea');
                        $total_temp = TeaInfoByclassArr_BothWay($staff_jsfz, $schoolid);
                        $total = count($total_temp);
                    }
                    if ($_GPC['type'] == 'staff') {
                        $total = 0;
                        foreach ($userdatas as $row) {
                            if ($row == 0 || $row != "") {
                                $total++;
                            }
                        }
                    }
                }
                $url = $_W['siteroot'] . 'app/index.php?i='.$weid.'&c=entry&schoolid='.$schoolid.'&m=fm_jiaoyu&do=sendMsg';
                $data = array(
                    'notice_id' => $notice_id,
                    'schoolid' => $schoolid,
                    'weid' => $weid,
                    'tname' => $tname,
                    'schooltype' => $schooltype,
                    'op' => 'sendMobileNewXytz',
                );
                $res = timeOutPost($url, $data);
                $customData = array(
                    'touser' => "{$_GPC['openid']}",
                    'msgtype'=> 'text',
                    'text'=> array(
                        'content' => "尊敬的-{$tname}老师，您发布的校园通知({$title})已全部发送成功！"
                    )
                );
                $this->sendCustomNotice($customData);
                $actop = 'xyqf';
                $userid = $_GPC['userid'];
                $point = PointAct($weid, $schoolid, $userid, $actop);
                $point1 = PointMission($weid, $schoolid, $userid, $actop);
                $data ['status'] = 1;
                $data ['result'] = true;
                $data ['msg'] = '正在执行发送任务！';
                if (!empty($point)) {
                    $data ['msg'] = '发送成功!积分+'.$point;
                }
            }
            die(json_encode($data));
        }
    }
    if ($operation == 'mnotpro') {
        $notice_id = $_GPC['noticeid'];
        $schoolid = $_GPC['schoolid'];
        $weid = $_GPC['weid'];
        $tname = $_GPC['tname'];
        $groupid = $_GPC['groupid'];
        $schooltype = $_GPC['schooltype'];
        $usertype = $_GPC['usertype'];
        $userdatas = $_GPC['userdatas'];
        $total = $_GPC['total'];
        $pindex = max(1, intval($_GPC['page']));
        $psize = 2;
        $tp = ceil($total/$psize);
        //echo '第' . $pindex . '次,总共'.$tp.'次';
        if ($pindex <= $tp) {
            if ($usertype == 'school' || $usertype == 'alltea' || $usertype == 'allstu') {
                $this->sendMobileXytz($notice_id, $schoolid, $weid, $tname, $groupid, $pindex, $psize);
            } else {
                $temp_userdatas = trim($userdatas, ";");
                $userdatas = explode(';', $temp_userdatas);
                session_start();
                if ($usertype == 'send_class') {
                    if ($_SESSION['arr'] || $_SESSION['arr'] != "") {
                        $arr = $_SESSION['arr'];
                    } else {
                        $send_class = array();
                        foreach ($userdatas as $row) {
                            if ($row == 0 || $row != "") {
                                $send_class[] = $row;
                            }
                        }
                        mload()->model('stu');
                        $arr = StuInfoByclassArr($send_class, $schoolid, $schooltype);
                        $_SESSION['arr'] = $arr;
                    }
                    $this->sendMobileXytzToUserArr($notice_id, $schoolid, $weid, $tname, $arr, 'tostu', $pindex, $psize);
                }
                if ($usertype == 'student') {
                    if ($_SESSION['arr'] || $_SESSION['arr'] != "") {
                        $arr = $_SESSION['arr'];
                    } else {
                        $arr = array();
                        foreach ($userdatas as $row) {
                            if ($row == 0 || $row != "") {
                                $arr[] = intval($row);
                            }
                        }
                        $_SESSION['arr'] = $arr;
                    }
                    $this->sendMobileXytzToUserArr($notice_id, $schoolid, $weid, $tname, $arr, 'tostu', $pindex, $psize);
                }
                if ($usertype == 'staff_jsfz') {
                    if ($_SESSION['arr'] || $_SESSION['arr'] != "") {
                        $arr = $_SESSION['arr'];
                    } else {
                        $staff_jsfz = array();
                        foreach ($userdatas as $row) {
                            if ($row == 0 || $row != " ") {
                                $staff_jsfz[] = $row;
                            }
                        }
                        mload()->model('tea');
                        //$arr = TeaInfoByclassArr($staff_jsfz,$schoolid);
                        $arr = TeaInfoByclassArr_BothWay($staff_jsfz, $schoolid);
                        $_SESSION['arr'] = $arr;
                    }
                    $this->sendMobileXytzToUserArr($notice_id, $schoolid, $weid, $tname, $arr, 'totea', $pindex, $psize);
                }
                if ($usertype == 'staff') {
                    if ($_SESSION['arr'] || $_SESSION['arr'] != "") {
                        $arr = $_SESSION['arr'];
                    } else {
                        $arr = array();
                        foreach ($userdatas as $row) {
                            if ($row == 0 || $row != "") {
                                $arr[] = intval($row);
                            }
                        }
                        $_SESSION['arr'] = $arr;
                    }
                    $res =	$this->sendMobileXytzToUserArr($notice_id, $schoolid, $weid, $tname, $arr, 'totea', $pindex, $psize);
                }
            }
            $mq = round(($pindex / $tp) * 100);
            $msg = '已发送' . $mq . ' %';
            $page = $pindex + 1;
            $data ['pro'] = $msg;
            $data ['page'] = $page;
            $data ['arr'] = $arr;
            $data ['count'] = count($arr);
            $data ['status'] = 1;
            $data['res'] = $res;
        } else {
            $data ['status'] = 2;
            $_SESSION['arr'] = "";
            $arr = $_SESSION['arr'];
            $data ['userdatas'] = $arr;
        }
        die(json_encode($data));
    }
    
    if ($operation == 'get_noticeuser') {
        $schooltype = $_GPC['schooltype'];
        $schoolid = $_GPC['schoolid'];
        $usertype = $_GPC['type'];
        if ($usertype == "student") {
            mload()->model('stu');
            $list = getallclassstuinfo($schoolid, 2, $schooltype);
            if ($schooltype != 1) {
                $list2 = getallclassstuinfo_nobj($schoolid, $schooltype);
            }
        }
        if ($usertype == "send_class") {
            mload()->model('stu');
            $list = getallclassinfo($schoolid, 2, $schooltype);
        }
        if ($usertype == "staff") {
            mload()->model('tea');
            $list = getalljsfzallteainfo($schoolid, 0, $schooltype);
            $list2 = getalljsfzallteainfo_nofz($schoolid, $schooltype);
        }
        if ($usertype == "staff_jsfz") {
            mload()->model('tea');
            $list = getalljsfzinfo($schoolid, 0, $schooltype);
        }
        //die ( json_encode ( $list ) );
        include $this->template('comtool/notice_user');
    }
    
    if ($operation == 'zfabu') {
        load()->func('communication');
        load()->func('file');
        $is_txt = $_GPC['is_txt'];
        $is_img = $_GPC['is_img'];
        $is_audio = $_GPC['is_audio'];
        $is_video = $_GPC['is_video'];
        if ($_GPC ['photoUrls']) {
            $picurl = array();
            $photo = $_GPC ['photoUrls'];
            for ($i = 0; $i <= 9 ; $i++) {
                if (!empty($photo[$i])) {
                    $picurl[$i] = $photo[$i];
                }
            }
            $picstr = array('p1' => $picurl[0],'p2' => $picurl[1],'p3' => $picurl[2],'p4' => $picurl[3],'p5' => $picurl[4],'p6' => $picurl[5],'p7' => $picurl[6],'p8' => $picurl[7],'p9' => $picurl[8],);
        }
        $video = '';
        if (!empty($_GPC['videoMediaId'])) {
            $msgtype = 3;//视频
            mload()->model('ali');
            $aliyun = GetAliApp($_W['uniacid'], $_GPC['schoolid']);
            if ($aliyun['result']) {
                $appid = $aliyun['alivodappid'];
                $key = $aliyun['alivodkey'];
                do {
                    $GetAliVideoUrl = GetAliVideoUrl($appid, $key, trim($_GPC['videoMediaId']));
                } while (empty($GetAliVideoUrl['PlayURL']));
                do {
                    $GetAliVideoCover = GetAliVideoCover($appid, $key, trim($_GPC['videoMediaId']));
                } while (empty($GetAliVideoCover['CoverURL']));
                $video = $GetAliVideoUrl['PlayURL'];
                $videoimg = $GetAliVideoCover['CoverURL'];
            }
        }
        $audios = $_GPC ['audioServerid'];
        
        $audio = $audios[0];
        if ($audio) {
            $versionfile = IA_ROOT . '/addons/fm_jiaoyu/inc/func/auth2.php';
            require $versionfile;
            $mp3name = str_replace('images/bjq/vioce/', '', $audio);
            $mp3 = str_replace('.mp3', '', $mp3name);
            delvioce($mp3, FM_JIAOYU_HOST);
        }
        $setting = pdo_fetch("SELECT * FROM " . tablename($this->table_set) . " WHERE :weid = weid", array(':weid' => $_GPC['weid']));
        if (! $_GPC ['schoolid'] || ! $_GPC ['weid']) {
            die(json_encode(array(
                    'result' => false,
                    'msg' => '非法请求！'
                       )));
        } else {
            if (empty($_GPC['openid'])) {
                die(json_encode(array(
                    'result' => false,
                    'msg' => '非法请求,请刷新页面！'
                       )));
            } else {
                $schoolid = $_GPC['schoolid'];
                $schooltype = $_GPC['schooltype'];
                $title = $_GPC['title'];
                $weid = $_GPC['weid'];
                $content = $_GPC['content'];
                $tid = $_GPC['tid'];
                $uid = $_GPC['uid'];
                $openid = $_GPC['openid'];
                $km_id = $_GPC['km_id'];
                $audios = $_GPC ['audioServerid'];
                $audio = $audios[0];
                $audiotimes = $_GPC['audioTime'];
                $audiotime = $audiotimes[0];
                $tname = $_GPC['tname'];
                $shername = $tname.'老师';
                $usertype = $_GPC['type'];
                $userdatas = $_GPC['datas'];
                $is_personal = trim($_GPC['is_private']);
                $is_pl = trim($_GPC['is_pl']);
                $is_see = trim($_GPC['is_see']);
                if ($is_pl == 'Y') {
                    $comment = 2;//允许评论
                    if ($is_see == 'Y') {
                        $comment = 3;//允许评论且仅作者可见
                    }
                } else {
                    $comment = 1;//禁止评论
                }
                $temp_ans = array(
                    'is_txt' => $is_txt,
                    'is_img' => $is_img,
                    'is_audio' => $is_audio,
                    'is_video' => $is_video
                );
                $ansType = iserializer($temp_ans);
                if ($usertype == 'send_class') {
                    $rtrim = rtrim($_GPC['datas'], ',');
                    $userdatas = explode(',', $rtrim);
                }
                if ($usertype == 'student') {
                    $datass = str_replace('&quot;', '"', $_GPC['datas']);
                    $userdatas = json_decode($datass, true);
                    $stuarr = array();
                }
                $notice_idarr = array();
                $bjarr = array();
                $totals = 0;
                foreach ($userdatas as $key => $row) {
                    if ($row != '') {
                        if ($usertype == 'send_class') {
                            $bj_id = $row;
                        }
                        if ($usertype == 'student') {
                            $bj_id = $key;
                        }
                        $temp = array(
                            'weid' =>  $weid,
                            'schoolid' => $schoolid,
                            'tid' => $tid,
                            'tname' => $tname,
                            'title' => $title,
                            'video' => $video,
                            'videopic' => $videoimg,
                            'audio' => $audio,
                            'audiotime' => $audiotime,
                            'usertype' => $usertype,
                            'content' => $content,
                            'comment' => $comment,
                            'createtime' => time(),
                            'type'=>3,
                            'km_id'=>$km_id,
                        );
                        // if($usertype == 'send_class'){
                        // 	$datass = rtrim($_GPC['datas'],',');
                        // 	$temp['userdatas']	= $datass;
                        // }
                        // if($usertype == 'student'){
                        // 	$datass = str_replace('&quot;','"',$_GPC['datas']);
                        // 	$temp['userdatas']	= $datass;
                        // }
                        $jsondatas = json_encode(array(
                            "{$key}"=> "{$row}"
                        ));
                        $temp['userdatas']	= $jsondatas;
                        if ($schooltype) {
                            $temp['kc_id']	= $bj_id;
                        } else {
                            $temp['bj_id']	= $bj_id;
                        }
                        if ($is_personal == 'Y') {
                            $temp['anstype'] = $ansType;
                        }
                        $temp['picarr'] = iserializer($picstr);
                        pdo_insert($this->table_notice, $temp);
                        $notice_id = pdo_insertid();
                        $url = $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&m=fm_jiaoyu&do=sendMsg';
                        $data = array(
                            'notice_id' => $notice_id,
                            'schoolid' => $schoolid,
                            'weid' => $weid,
                            'tname' => $tname,
                            'bj_id' => $bj_id,
                            'op' => 'sendMobileNewZuoye',
                        );
                        timeOutPost($url, $data);
                    }
                }
                $result ['status'] = 1;
                $result ['result'] = true;
                $result ['msg'] = '正在执行发送任务！';
                $customData = array(
                    'touser' => "{$_GPC['openid']}",
                    'msgtype'=> 'text',
                    'text'=> array(
                        'content' => "尊敬的-{$tname}老师，您发布的作业({$title})已全部发送成功！"
                    )
                );
                $this->sendCustomNotice($customData);
                $actop = 'fbzy';
                $userid = $_GPC['userid'];
                $point = PointAct($weid, $schoolid, $userid, $actop);
                $point1 = PointMission($weid, $schoolid, $userid, $actop);
                if (!empty($point)) {
                    $result ['msg'] = '群发成功，请勿重复操作!积分+'.$point;
                }
            }
            die(json_encode($result));
        }
    }
    if ($operation == 'znotpro') {
        $schoolid = $_GPC['schoolid'];
        $weid = $_GPC['weid'];
        $tname = $_GPC['tname'];
        $schooltype = $_GPC['schooltype'];
        $usertype = $_GPC['usertype'];
        $bjarr = $_GPC['bjarr'];
        $stuarr = $_GPC['stuarr'];
        $noticeidarr = $_GPC['noticeidarr'];
        $total = $_GPC['total'];
        $pindex = max(1, intval($_GPC['page']));
        $psize = 2;
        $tp = ceil($total/$psize);
        //echo '第' . $pindex . '次,总共'.$tp.'次';
        if ($pindex <= $tp) {
            session_start();
            if ($usertype == 'send_class') {
                if ($_SESSION['arr'] || $_SESSION['arr'] != "") {
                    $arr = $_SESSION['arr'];
                } else {
                    mload()->model('stu');
                    $arr = StuInfoByclassArr($bjarr, $schoolid, $schooltype);
                    $_SESSION['arr'] = $arr;
                }
                $this->sendMobileZytzToUserArr($schoolid, $schooltype, $weid, $tname, $arr, $noticeidarr, 'tostu', $pindex, $psize);
            }
            if ($usertype == 'student') {
                if ($_SESSION['arr'] || $_SESSION['arr'] != "") {
                    $arr = $_SESSION['arr'];
                } else {
                    $arr = $stuarr;
                    $_SESSION['arr'] = $arr;
                }
                $this->sendMobileZytzToUserArr($schoolid, $schooltype, $weid, $tname, $arr, $noticeidarr, 'tostu', $pindex, $psize);
            }
            $mq = round(($pindex / $tp) * 100);
            $msg = '已发送' . $mq . ' %';
            $page = $pindex + 1;
            $data ['pro'] = $msg;
            $data ['arr'] = $arr;
            $data ['bjarr'] = $bjarr;
            $data ['stuarr'] = $stuarr;
            $data ['noticeidarr'] = $noticeidarr;
            $data ['page'] = $page;
            $data ['count'] = count($arr);
            $data ['status'] = 1;
        } else {
            $data ['status'] = 2;
            $_SESSION['arr'] = "";
            $arr = $_SESSION['arr'];
            $data ['userdatas'] = $arr;
        }
        die(json_encode($data));
    }
    if ($operation == 'fangxue') {
        $data = explode('|', $_GPC ['json']);
        $setting = pdo_fetch("SELECT * FROM " . tablename($this->table_set) . " WHERE :weid = weid", array(':weid' => $_GPC['weid']));
        if (! $_GPC ['schoolid'] || ! $_GPC ['weid']) {
            die(json_encode(array(
                    'result' => false,
                    'msg' => '非法请求！'
                       )));
        } else {
            if (empty($_GPC['openid'])) {
                die(json_encode(array(
                    'result' => false,
                    'msg' => '非法请求,请刷新页面！'
                       )));
            } else {
                $schoolid = $_GPC['schoolid'];
                $weid = $_GPC['weid'];
                $bj_id = $_GPC['bj_id']; //班级
                $tname = $_GPC['tname'];
                $this->sendMobileFxtz($schoolid, $weid, $tname, $bj_id);
                $data ['result'] = true;
                $data ['msg'] = '群发成功，请勿重复发布！';
                $actop = 'yjfx';
                $userid = $_GPC['userid'];
                $weid = $_GPC['weid'];
                $schoolid = $_GPC['schoolid'];
                $point = PointAct($weid, $schoolid, $userid, $actop);
                $point1 = PointMission($weid, $schoolid, $userid, $actop);
                if (!empty($point)) {
                    $data ['msg'] = '群发成功，请勿重复发布！!积分+'.$point;
                }
            }
            die(json_encode($data));
        }
    }
    if ($operation == 'sxcfb') {
        load()->func('communication');
        load()->func('file');
        $token2 = $this->getAccessToken2();
        $photoUrls = explode(',', $_GPC ['photoUrls']);
        $data = explode('|', $_GPC ['json']);
        $school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id = :id", array(':weid' => $_GPC ['weid'], ':id' => $_GPC ['schoolid']));
        if ($_GPC ['photoUrls']) {
            $picurl = array();
            for ($i = 0; $i <= 9 ; $i++) {
                if (!empty($photoUrls[$i])) {
                    $url = 'https://file.api.weixin.qq.com/cgi-bin/media/get?access_token='.$token2.'&media_id='.$photoUrls[$i];
                    $pic_data = ihttp_request($url);
                    $path = "images/fm_jiaoyu/";
                    $picurl[$i] = $path.random(30) .".jpg";
                    file_write($picurl[$i], $pic_data['content']);
                    if (!empty($_W['setting']['remote']['type'])) { //
                                $remotestatus = file_remote_upload($picurl[$i]); //
                                if (is_error($remotestatus)) {
                                    message('远程附件上传失败，请检查配置并重新上传');
                                }
                    }
                }
            }
        }
        $setting = pdo_fetch("SELECT * FROM " . tablename($this->table_set) . " WHERE :weid = weid", array(':weid' => $_GPC['weid']));
        if (! $_GPC ['schoolid'] || ! $_GPC ['weid']) {
            die(json_encode(array(
                    'result' => false,
                    'msg' => '非法请求！'
                       )));
        } else {
            if (empty($_GPC['openid'])) {
                die(json_encode(array(
                    'result' => false,
                    'msg' => '非法请求,请刷新页面！'
                       )));
            } else {
                $schoolid = $_GPC['schoolid'];
                $weid = $_GPC['weid'];
                $content = $_GPC['content'];
                $uid = $_GPC['uid'];
                $sid = $_GPC['sid'];
                $bj_id = $_GPC['bj_id'];
                $isfmpic = pdo_fetch("SELECT * FROM " . tablename($this->table_media) . " WHERE :weid = weid And :schoolid = schoolid And :sid = sid And :type = type And :bj_id1 = bj_id1 ORDER BY id ASC LIMIT 0,1 ", array(
                         ':weid' => $weid,
                         ':schoolid' => $schoolid,
                         ':sid' => $sid,
                         ':bj_id1' => $bj_id,
                         ':type' => 1
                         ));
                if (!empty($isfmpic['fmpicurl'])) {
                    if (!empty($photoUrls[0])) {
                        $data = array(
                        'weid' =>  $weid,
                        'schoolid' => $schoolid,
                        'uid' => $uid,
                        'sid' => $sid,
                        'picurl' => $picurl[0],
                        'bj_id1' => $bj_id,
                        'order'=>1,
                        'createtime' => time(),
                        'type'=>1,
                       );
                        pdo_insert($this->table_media, $data);
                    }
                } else {
                    if (!empty($photoUrls[0])) {
                        $data = array(
                        'weid' =>  $weid,
                        'schoolid' => $schoolid,
                        'uid' => $uid,
                        'sid' => $sid,
                        'picurl' => $picurl[0],
                        'fmpicurl' => $picurl[0],
                        'bj_id1' => $bj_id,
                        'order'=>1,
                        'createtime' => time(),
                        'type'=>1,
                        'isfm'=>1
                       );
                        pdo_insert($this->table_media, $data);
                    }
                }
                if (!empty($photoUrls[1])) {
                    $data = array(
                    'weid' =>  $weid,
                    'schoolid' => $schoolid,
                    'uid' => $uid,
                    'sid' => $sid,
                    'picurl' => $picurl[1],
                    'bj_id1' => $bj_id,
                    'order'=>2,
                    'createtime' => time(),
                    'type'=>1,
                   );
                    pdo_insert($this->table_media, $data);
                }
                if (!empty($photoUrls[2])) {
                    $data = array(
                    'weid' =>  $weid,
                    'schoolid' => $schoolid,
                    'uid' => $uid,
                    'sid' => $sid,
                    'picurl' => $picurl[2],
                    'bj_id1' => $bj_id,
                    'order'=>3,
                    'createtime' => time(),
                    'type'=>1,
                   );
                    pdo_insert($this->table_media, $data);
                }
                if (!empty($photoUrls[3])) {
                    $data = array(
                    'weid' =>  $weid,
                    'schoolid' => $schoolid,
                    'uid' => $uid,
                    'sid' => $sid,
                    'picurl' => $picurl[3],
                    'bj_id1' => $bj_id,
                    'order'=>4,
                    'createtime' => time(),
                    'type'=>1,
                   );
                    pdo_insert($this->table_media, $data);
                }
                if (!empty($photoUrls[4])) {
                    $data = array(
                    'weid' =>  $weid,
                    'schoolid' => $schoolid,
                    'uid' => $uid,
                    'sid' => $sid,
                    'picurl' => $picurl[4],
                    'bj_id1' => $bj_id,
                    'order'=>5,
                    'createtime' => time(),
                    'type'=>1,
                   );
                    pdo_insert($this->table_media, $data);
                }
                if (!empty($photoUrls[5])) {
                    $data = array(
                    'weid' =>  $weid,
                    'schoolid' => $schoolid,
                    'uid' => $uid,
                    'sid' => $sid,
                    'picurl' => $picurl[5],
                    'bj_id1' => $bj_id,
                    'order'=>6,
                    'createtime' => time(),
                    'type'=>1,
                   );
                    pdo_insert($this->table_media, $data);
                }
                if (!empty($photoUrls[6])) {
                    $data = array(
                    'weid' =>  $weid,
                    'schoolid' => $schoolid,
                    'uid' => $uid,
                    'sid' => $sid,
                    'picurl' => $picurl[6],
                    'bj_id1' => $bj_id,
                    'order'=>7,
                    'createtime' => time(),
                    'type'=>1,
                   );
                    pdo_insert($this->table_media, $data);
                }
                if (!empty($photoUrls[7])) {
                    $data = array(
                    'weid' =>  $weid,
                    'schoolid' => $schoolid,
                    'uid' => $uid,
                    'sid' => $sid,
                    'picurl' => $picurl[7],
                    'bj_id1' => $bj_id,
                    'order'=>8,
                    'createtime' => time(),
                    'type'=>1,
                   );
                    pdo_insert($this->table_media, $data);
                }
                if (!empty($photoUrls[8])) {
                    $data = array(
                    'weid' =>  $weid,
                    'schoolid' => $schoolid,
                    'uid' => $uid,
                    'sid' => $sid,
                    'picurl' => $picurl[8],
                    'bj_id1' => $bj_id,
                    'order'=>9,
                    'createtime' => time(),
                    'type'=>1,
                   );
                    pdo_insert($this->table_media, $data);
                }
                $data ['result'] = true;
                $data ['msg'] = '发布成功，请勿重复发布！';
            }
            die(json_encode($data));
        }
    }
    if ($operation == 'xcfb') {
        load()->func('communication');
        load()->func('file');
        $token2 = $this->getAccessToken2();
        $photoUrls = explode(',', $_GPC ['photoUrls']);
        $bjids = explode(',', $_GPC ['bj_id']);
        $data = explode('|', $_GPC ['json']);
        $school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id = :id", array(':weid' => $_GPC ['weid'], ':id' => $_GPC ['schoolid']));
        if (!empty($photoUrls[0])) {
            $url = 'https://file.api.weixin.qq.com/cgi-bin/media/get?access_token='.$token2.'&media_id='.$photoUrls[0];
            $pic_data = ihttp_request($url);
            $path = "images/fm_jiaoyu/";
            $picurl0 = $path.random(30) .".jpg";
            file_write($picurl0, $pic_data['content']);
            if (!empty($_W['setting']['remote']['type'])) { //
                            $remotestatus = file_remote_upload($picurl0); //
                            if (is_error($remotestatus)) {
                                message('远程附件上传失败，请检查配置并重新上传');
                            }
            }
        }
        if (!empty($photoUrls[1])) {
            $url = 'https://file.api.weixin.qq.com/cgi-bin/media/get?access_token='.$token2.'&media_id='.$photoUrls[1];
            $pic_data = ihttp_request($url);
            $path = "images/fm_jiaoyu/";
            $picurl1 = $path.random(30) .".jpg";
            file_write($picurl1, $pic_data['content']);
            if (!empty($_W['setting']['remote']['type'])) { //
                            $remotestatus = file_remote_upload($picurl1); //
                            if (is_error($remotestatus)) {
                                message('远程附件上传失败，请检查配置并重新上传');
                            }
            }
        }
        if (!empty($photoUrls[2])) {
            $url = 'https://file.api.weixin.qq.com/cgi-bin/media/get?access_token='.$token2.'&media_id='.$photoUrls[2];
            $pic_data = ihttp_request($url);
            $path = "images/fm_jiaoyu/";
            $picurl2 = $path.random(30) .".jpg";
            file_write($picurl2, $pic_data['content']);
            if (!empty($_W['setting']['remote']['type'])) { //
                            $remotestatus = file_remote_upload($picurl2); //
                            if (is_error($remotestatus)) {
                                message('远程附件上传失败，请检查配置并重新上传');
                            }
            }
        }
        if (!empty($photoUrls[3])) {
            $url = 'https://file.api.weixin.qq.com/cgi-bin/media/get?access_token='.$token2.'&media_id='.$photoUrls[3];
            $pic_data = ihttp_request($url);
            $path = "images/fm_jiaoyu/";
            $picurl3 = $path.random(30) .".jpg";
            file_write($picurl3, $pic_data['content']);
            if (!empty($_W['setting']['remote']['type'])) { //
                            $remotestatus = file_remote_upload($picurl3); //
                            if (is_error($remotestatus)) {
                                message('远程附件上传失败，请检查配置并重新上传');
                            }
            }
        }
        if (!empty($photoUrls[4])) {
            $url = 'https://file.api.weixin.qq.com/cgi-bin/media/get?access_token='.$token2.'&media_id='.$photoUrls[4];
            $pic_data = ihttp_request($url);
            $path = "images/fm_jiaoyu/";
            $picurl4 = $path.random(30) .".jpg";
            file_write($picurl4, $pic_data['content']);
            if (!empty($_W['setting']['remote']['type'])) { //
                            $remotestatus = file_remote_upload($picurl4); //
                            if (is_error($remotestatus)) {
                                message('远程附件上传失败，请检查配置并重新上传');
                            }
            }
        }
        if (!empty($photoUrls[5])) {
            $url = 'https://file.api.weixin.qq.com/cgi-bin/media/get?access_token='.$token2.'&media_id='.$photoUrls[5];
            $pic_data = ihttp_request($url);
            $path = "images/fm_jiaoyu/";
            $picurl5 = $path.random(30) .".jpg";
            file_write($picurl5, $pic_data['content']);
            if (!empty($_W['setting']['remote']['type'])) { //
                            $remotestatus = file_remote_upload($picurl5); //
                            if (is_error($remotestatus)) {
                                message('远程附件上传失败，请检查配置并重新上传');
                            }
            }
        }
        if (!empty($photoUrls[6])) {
            $url = 'https://file.api.weixin.qq.com/cgi-bin/media/get?access_token='.$token2.'&media_id='.$photoUrls[6];
            $pic_data = ihttp_request($url);
            $path = "images/fm_jiaoyu/";
            $picurl6 = $path.random(30) .".jpg";
            file_write($picurl6, $pic_data['content']);
            if (!empty($_W['setting']['remote']['type'])) { //
                            $remotestatus = file_remote_upload($picurl6); //
                            if (is_error($remotestatus)) {
                                message('远程附件上传失败，请检查配置并重新上传');
                            }
            }
        }
        if (!empty($photoUrls[7])) {
            $url = 'https://file.api.weixin.qq.com/cgi-bin/media/get?access_token='.$token2.'&media_id='.$photoUrls[7];
            $pic_data = ihttp_request($url);
            $path = "images/fm_jiaoyu/";
            $picurl7 = $path.random(30) .".jpg";
            file_write($picurl7, $pic_data['content']);
            if (!empty($_W['setting']['remote']['type'])) { //
                            $remotestatus = file_remote_upload($picurl7); //
                            if (is_error($remotestatus)) {
                                message('远程附件上传失败，请检查配置并重新上传');
                            }
            }
        }
        if (!empty($photoUrls[8])) {
            $url = 'https://file.api.weixin.qq.com/cgi-bin/media/get?access_token='.$token2.'&media_id='.$photoUrls[8];
            $pic_data = ihttp_request($url);
            $path = "images/fm_jiaoyu/";
            $picurl8 = $path.random(30) .".jpg";
            file_write($picurl8, $pic_data['content']);
            if (!empty($_W['setting']['remote']['type'])) { //
                            $remotestatus = file_remote_upload($picurl8); //
                            if (is_error($remotestatus)) {
                                message('远程附件上传失败，请检查配置并重新上传');
                            }
            }
        }
        $setting = pdo_fetch("SELECT * FROM " . tablename($this->table_set) . " WHERE :weid = weid", array(':weid' => $_GPC['weid']));
        if (! $_GPC ['schoolid'] || ! $_GPC ['weid']) {
            die(json_encode(array(
                    'result' => false,
                    'msg' => '非法请求！'
                       )));
        } else {
            if (empty($_GPC['openid'])) {
                die(json_encode(array(
                    'result' => false,
                    'msg' => '非法请求,请刷新页面！'
                       )));
            } else {
                $schoolid = $_GPC['schoolid'];
                $weid = $_GPC['weid'];
                $content = $_GPC['content'];
                $uid = $_GPC['uid'];
                $sid = $_GPC['sid'];
                $bj_id1 = $bjids[0];
                $bj_id2 = $bjids[1];
                $bj_id3 = $bjids[2];
                if (!empty($photoUrls[0])) {
                    $data = array(
                    'weid' =>  $weid,
                    'schoolid' => $schoolid,
                    'uid' => $uid,
                    'sid' => $sid,
                    'picurl' => $picurl0,
                    'bj_id1' => $bj_id1,
                    'bj_id2' => $bj_id2,
                    'bj_id3' => $bj_id3,
                    'order'=>1,
                    'createtime' => time(),
                    'type'=>2,
                   );
                    pdo_insert($this->table_media, $data);
                }
                if (!empty($photoUrls[1])) {
                    $data = array(
                    'weid' =>  $weid,
                    'schoolid' => $schoolid,
                    'uid' => $uid,
                    'sid' => $sid,
                    'picurl' => $picurl1,
                    'bj_id1' => $bj_id1,
                    'bj_id2' => $bj_id2,
                    'bj_id3' => $bj_id3,
                    'order'=>2,
                    'createtime' => time(),
                    'type'=>2,
                   );
                    pdo_insert($this->table_media, $data);
                }
                if (!empty($photoUrls[2])) {
                    $data = array(
                    'weid' =>  $weid,
                    'schoolid' => $schoolid,
                    'uid' => $uid,
                    'sid' => $sid,
                    'picurl' => $picurl2,
                    'bj_id1' => $bj_id1,
                    'bj_id2' => $bj_id2,
                    'bj_id3' => $bj_id3,
                    'order'=>3,
                    'createtime' => time(),
                    'type'=>2,
                   );
                    pdo_insert($this->table_media, $data);
                }
                if (!empty($photoUrls[3])) {
                    $data = array(
                    'weid' =>  $weid,
                    'schoolid' => $schoolid,
                    'uid' => $uid,
                    'sid' => $sid,
                    'picurl' => $picurl3,
                    'bj_id1' => $bj_id1,
                    'bj_id2' => $bj_id2,
                    'bj_id3' => $bj_id3,
                    'order'=>4,
                    'createtime' => time(),
                    'type'=>2,
                   );
                    pdo_insert($this->table_media, $data);
                }
                if (!empty($photoUrls[4])) {
                    $data = array(
                    'weid' =>  $weid,
                    'schoolid' => $schoolid,
                    'uid' => $uid,
                    'sid' => $sid,
                    'picurl' => $picurl4,
                    'bj_id1' => $bj_id1,
                    'bj_id2' => $bj_id2,
                    'bj_id3' => $bj_id3,
                    'order'=>5,
                    'createtime' => time(),
                    'type'=>2,
                   );
                    pdo_insert($this->table_media, $data);
                }
                if (!empty($photoUrls[5])) {
                    $data = array(
                    'weid' =>  $weid,
                    'schoolid' => $schoolid,
                    'uid' => $uid,
                    'sid' => $sid,
                    'picurl' => $picurl5,
                    'bj_id1' => $bj_id1,
                    'bj_id2' => $bj_id2,
                    'bj_id3' => $bj_id3,
                    'order'=>6,
                    'createtime' => time(),
                    'type'=>2,
                   );
                    pdo_insert($this->table_media, $data);
                }
                if (!empty($photoUrls[6])) {
                    $data = array(
                    'weid' =>  $weid,
                    'schoolid' => $schoolid,
                    'uid' => $uid,
                    'sid' => $sid,
                    'picurl' => $picurl6,
                    'bj_id1' => $bj_id1,
                    'bj_id2' => $bj_id2,
                    'bj_id3' => $bj_id3,
                    'order'=>7,
                    'createtime' => time(),
                    'type'=>2,
                   );
                    pdo_insert($this->table_media, $data);
                }
                if (!empty($photoUrls[7])) {
                    $data = array(
                    'weid' =>  $weid,
                    'schoolid' => $schoolid,
                    'uid' => $uid,
                    'sid' => $sid,
                    'picurl' => $picurl7,
                    'bj_id1' => $bj_id1,
                    'bj_id2' => $bj_id2,
                    'bj_id3' => $bj_id3,
                    'order'=>8,
                    'createtime' => time(),
                    'type'=>2,
                   );
                    pdo_insert($this->table_media, $data);
                }
                if (!empty($photoUrls[8])) {
                    $data = array(
                    'weid' =>  $weid,
                    'schoolid' => $schoolid,
                    'uid' => $uid,
                    'sid' => $sid,
                    'picurl' => $picurl8,
                    'bj_id1' => $bj_id1,
                    'bj_id2' => $bj_id2,
                    'bj_id3' => $bj_id3,
                    'order'=>9,
                    'createtime' => time(),
                    'type'=>2,
                   );
                    pdo_insert($this->table_media, $data);
                }
                $data ['result'] = true;
                $data ['msg'] = '发布成功，请勿重复发布！';
                $actop = 'scxc';
                $userid = $_GPC['userid'];
                $point = PointAct($weid, $schoolid, $userid, $actop);
                $point1 = PointMission($weid, $schoolid, $userid, $actop);
            
                if (!empty($point)) {
                    $data ['msg'] = '发布成功，请勿重复发布!积分+'.$point;
                }
            }
            die(json_encode($data));
        }
    }
    if ($operation == 'dellimg') {
        $dataid = explode(',', $_GPC ['fileids']);
        if (empty($dataid)) {
            die(json_encode(array(
                        'result' => false,
                        'msg' => '您没有选中任何图片！'
                           )));
        } else {
            foreach ($dataid as $mid => $row) {
                $isfm = pdo_fetch("SELECT * FROM " . tablename($this->table_media) . " where id=:id ", array(':id' => $row));
                if ($isfm['isfm'] == 1) {
                    die(json_encode(array(
                            'result' => false,
                            'msg' => '您不能删除封面图片！'
                                )));
                } else {
                    pdo_delete($this->table_media, array('id' => $row));
                    $data ['result'] = true;
                    $data ['msg'] = '删除成功！';
                }
            }
        }
        die(json_encode($data));
    }
    if ($operation == 'savedatabypicforplan') {
        $data = file_get_contents('php://input');
        if ($data) {
            $data = urldecode($data);
            $data = str_replace('JSON=', '', $data);
        }
        $data = json_decode($data, true);
        $starttime = strtotime($data['StartDate']);
        $endtime = strtotime($data['EndDate']);
        $checktime = pdo_fetch("SELECT * FROM " . tablename($this->table_zjh) . " where weid = :weid And schoolid = :schoolid And bj_id = :bj_id And type = :type And start < :start And end > :end", array(
            ':weid' => $_GPC['weid'],
            ':schoolid' => $_GPC['schoolid'],
            ':bj_id' => $_GPC['bj_id'],
            ':type' => 1,
            ':start' => $starttime,
            ':end' => $endtime
        ));
        if ($checktime) {
            $msg ['Status'] = 0;
            $msg ['Result'] = '本时间范围内已有周计划';
            die(json_encode($msg));
        } else {
            $temp = array('weid' => $_GPC['weid'],'schoolid' => $_GPC['schoolid'],'bj_id' => $_GPC['bj_id'],'start' => $starttime,'end' => $endtime,'type' => 1,'is_on' => 2,'createtime' => time(),'tid' => $_GPC['tid']);
            pdo_update($this->table_zjh, $temp, array('planuid' => $data['PlanUid']));
            $msg ['Status'] = 1;
            $msg ['Result'] = '保存周计划成功';
            $actop = 'sczjh';
            $userid = $_GPC['userid'];
            $weid = $_GPC['weid'];
            $schoolid = $_GPC['schoolid'];
            $point = PointAct($weid, $schoolid, $userid, $actop);
            $point1 = PointMission($weid, $schoolid, $userid, $actop);
            if (!empty($point)) {
                $msg ['Result'] = '保存周计划成功!积分+'.$point;
            }
        }
        die(json_encode($msg));
    }
    if ($operation == 'updatabypic') {
        load()->func('communication');
        load()->func('file');
        $token2 = $this->getAccessToken2();
        $photoUrl = $_GPC['serverId'];
        $PlanUid = trim($_GPC['PlanUid']);
        if (!empty($photoUrl)) {
            $url = 'https://file.api.weixin.qq.com/cgi-bin/media/get?access_token='.$token2.'&media_id='.$photoUrl;
            $pic_data = ihttp_request($url);
            $path = "images/fm_jiaoyu/";
            $picurl = $path.random(30) .".jpg";
            file_write($picurl, $pic_data['content']);
            if (!empty($_W['setting']['remote']['type'])) { //
                $remotestatus = file_remote_upload($picurl); //
                if (is_error($remotestatus)) {
                    message('远程附件上传失败，请检查配置并重新上传');
                }
                $urls = $_W['attachurl'];
            } else {
                $urls = $_W['siteroot'].'attachment/';
            }
        }
        $thisplan = pdo_fetch("SELECT * FROM " . tablename($this->table_zjh) . " where weid = :weid And schoolid = :schoolid And bj_id = :bj_id And type = :type And planuid = :planuid ", array(
            ':weid' => $_GPC['weid'],
            ':schoolid' => $_GPC['schoolid'],
            ':bj_id' => $_GPC['bj_id'],
            ':type' => 1,
            ':planuid' => $PlanUid
        ));
        if (empty($thisplan)) {
            $data = array('weid' => $_GPC['weid'],'schoolid' => $_GPC['schoolid'],'bj_id' => $_GPC['bj_id'],'type' => 1,'planuid' => $PlanUid,'picrul' => $picurl,'createtime' => time(),'tid' => $_GPC['tid']);
            pdo_insert($this->table_zjh, $data);
        } else {
            pdo_update($this->table_zjh, array('picrul' => $picurl,'createtime' => time()), array('planuid' => $PlanUid));
        }
        $msg ['status'] = 1;
        $msg ['data'] = $urls.$picurl;
        die(json_encode($msg));
    }
    if ($operation == 'DeleteWeekPlanByPlanUid') {
        if (empty($_GPC['sPlanUid'])) {
            die(json_encode(array(
                    'Status' => 0,
                    'Result' => '出错了！'
                    )));
        } else {
            pdo_delete($this->table_zjh, array('planuid' => $_GPC['sPlanUid']));
            pdo_delete($this->table_zjhset, array('planuid' => $_GPC['sPlanUid']));
            pdo_delete($this->table_zjhdetail, array('planuid' => $_GPC['sPlanUid']));
            $msg ['Status'] = 1;
            $msg ['Result'] = '删除成功';
        }
        die(json_encode($msg));
    }
    if ($operation == 'SendPlanWeek') {
        $data = file_get_contents('php://input');
        if ($data) {
            $data = urldecode($data);
            $data = str_replace('JSON=', '', $data);
        }
        $data = json_decode($data, true);
        if (!$_GPC['weid'] || !$_GPC['schoolid'] || !$_GPC['bj_id']) {
            die(json_encode(array(
                'Status' => 0,
                'Result' => '您不是班主任，无权使用此功能'
               )));
        } else {
            $check = pdo_fetch("SELECT * FROM " . tablename($this->table_zjh) . " where weid = :weid And schoolid = :schoolid And bj_id = :bj_id And type = :type And planuid = :planuid ", array(
                ':weid' => $_GPC['weid'],
                ':schoolid' => $_GPC['schoolid'],
                ':bj_id' => $_GPC['bj_id'],
                ':type' => 2,
                ':planuid' => $data['PlanUid']
            ));
            $date = array('weid' => $_GPC['weid'],'schoolid' => $_GPC['schoolid'],'bj_id' => $_GPC['bj_id'],'start' => strtotime($data['StartDate']),'end' => strtotime($data['EndDate']),'type' => 2,'is_on' => 2,'planuid' => $data['PlanUid'],'tid' => $_GPC['tid']);
           
            if (empty($check)) {
                pdo_insert($this->table_zjh, $date);
            } else {
                pdo_update($this->table_zjh, $date, array('planuid' => $data['PlanUid']));
            }
            //pdo_delete($this->table_zjhset, array('weid' => $data['weid'],'schoolid' => $data['schoolid'],'planuid' => $data['PlanUid']));
            $detail = $data['lstPMPlanType'];
            foreach ($detail as $mid => $row) {
                $thisset = pdo_fetch("SELECT * FROM " . tablename($this->table_zjhset) . " where weid = :weid And schoolid = :schoolid And activetypeid = :activetypeid And type = :type And planuid = :planuid ", array(
                        ':weid' => $data['weid'],
                        ':schoolid' => $data['schoolid'],
                        ':activetypeid' => $row['id'],
                        ':type' => $row['timeType'],
                        ':planuid' => $data['PlanUid']
                    ));
                if (!empty($thisset)) {
                    pdo_update($this->table_zjhset, array('activetypename' => $row['itemName']), array('id' => $thisset['id']));
                } else {
                    $ActiveTypeId = getRandomString(8).'-'.getRandomString(4).'-'.getRandomString(4).'-'.getRandomString(4).'-'.getRandomString(12);
                    $temp = array(
                            'weid' => $data['weid'],
                            'schoolid' => $data['schoolid'],
                            'activetypeid' => !empty($row['id']) ? $row['id'] : $ActiveTypeId,
                            'activetypename' => $row['itemName'],
                            'type' => $row['timeType'],
                            'planuid' => $data['PlanUid']
                        );
                    pdo_insert($this->table_zjhset, $temp);
                }
            }
            $msg ['Status'] = 1;
            $msg ['PlanUid'] = $data['PlanUid'];
            $msg ['Result'] = '修改成功';
            $actop = 'sczjh';
            $userid = $_GPC['userid'];
            $weid = $_GPC['weid'];
            $schoolid = $_GPC['schoolid'];
            $point = PointAct($weid, $schoolid, $userid, $actop);
            $point1 = PointMission($weid, $schoolid, $userid, $actop);
            if (!empty($point)) {
                $msg ['Result'] = '保存周计划成功!积分+'.$point;
            }
        }
        die(json_encode($msg));
    }
    if ($operation == 'UpdateTypeByActiveId') {
        $data = file_get_contents('php://input');
        if ($data) {
            $data = urldecode($data);
            $data = str_replace('JSON=', '', $data);
        }
        $data = json_decode($data, true);
        if (!$data['weid'] || !$data['schoolid'] || !$data['bj_id']) {
            die(json_encode(array(
                'Status' => 0,
                'Result' => '您不是班主任，无权使用此功能'
               )));
        } else {
            $check = pdo_fetch("SELECT * FROM " . tablename($this->table_zjh) . " where weid = :weid And schoolid = :schoolid And bj_id = :bj_id And type = :type And planuid = :planuid ", array(
                ':weid' => $_GPC['weid'],
                ':schoolid' => $_GPC['schoolid'],
                ':bj_id' => $_GPC['bj_id'],
                ':type' => 2,
                ':planuid' => $data['PlanUid']
            ));
            $date = array('weid' => $_GPC['weid'],'schoolid' => $_GPC['schoolid'],'bj_id' => $_GPC['bj_id'],'start' => strtotime($data['StartDate']),'end' => strtotime($data['EndDate']),'type' => 2,'planuid' => $data['PlanUid']);
            if (empty($check)) {
                pdo_insert($this->table_zjh, $date);
            } else {
                pdo_update($this->table_zjh, $date, array('planuid' => $data['PlanUid']));
            }
            //pdo_delete($this->table_zjhset, array('weid' => $data['weid'],'schoolid' => $data['schoolid'],'planuid' => $data['PlanUid']));
            $detail = $data['lstPlanDetail'];
            foreach ($detail as $mid => $row) {
                $thisset = pdo_fetch("SELECT * FROM " . tablename($this->table_zjhset) . " where weid = :weid And schoolid = :schoolid And activetypeid = :activetypeid And type = :type And planuid = :planuid ", array(
                        ':weid' => $data['weid'],
                        ':schoolid' => $data['schoolid'],
                        ':activetypeid' => $row['ActiveTypeId'],
                        ':type' => $row['ActiveTypeIcon'],
                        ':planuid' => $data['PlanUid']
                    ));
                if (!empty($thisset)) {
                    pdo_update($this->table_zjhset, array('activetypename' => $row['ActiveTypeName']), array('id' => $thisset['id']));
                } else {
                    $ActiveTypeId = getRandomString(8).'-'.getRandomString(4).'-'.getRandomString(4).'-'.getRandomString(4).'-'.getRandomString(12);
                    $temp = array(
                            'weid' => $data['weid'],
                            'schoolid' => $data['schoolid'],
                            'activetypeid' => !empty($row['ActiveTypeId']) ? $row['ActiveTypeId'] : $ActiveTypeId,
                            'activetypename' => $row['ActiveTypeName'],
                            'type' => $row['ActiveTypeIcon'],
                            'planuid' => $data['PlanUid']
                        );
                    pdo_insert($this->table_zjhset, $temp);
                }
            }
            $msg ['Status'] = 1;
            $msg ['PlanUid'] = $data['PlanUid'];
            $class = pdo_fetchall("SELECT activetypeid,type FROM " . tablename($this->table_zjhset) . " WHERE weid = '{$data['weid']}' And schoolid = {$data['schoolid']} And planuid = '{$data['PlanUid']}' ORDER BY id ASC");
            foreach ($class as $key => $item) {
                if ($item['type'] == 'AM') {
                    $msg['AMActiveId'] .= $item['activetypeid'].",";
                }
                if ($item['type'] == 'PM') {
                    $msg['PMActiveId'] .= $item['activetypeid'].",";
                }
            }
            //$msg = $class[$key];
            $msg ['Result'] = '修改成功';
        }
        die(json_encode($msg));
    }
    if ($operation == 'SavePlanWeek') {
        $data = file_get_contents('php://input');
        if ($data) {
            $data = urldecode($data);
            $data = str_replace('JSON=', '', $data);
        }
        $data = json_decode($data, true);
        if (!$_GPC['weid'] || !$_GPC['schoolid'] || !$_GPC['bj_id']) {
            die(json_encode(array(
                'Status' => 0,
                'Result' => '您不是班主任，无权使用此功能',
               )));
        } else {
            $check = pdo_fetch("SELECT * FROM " . tablename($this->table_zjh) . " where weid = :weid And schoolid = :schoolid And bj_id = :bj_id And type = :type And planuid = :planuid ", array(
                ':weid' => $_GPC['weid'],
                ':schoolid' => $_GPC['schoolid'],
                ':bj_id' => $_GPC['bj_id'],
                ':type' => 2,
                ':planuid' => $data['PlanUid']
            ));
            $date = array('weid' => $_GPC['weid'],'schoolid' => $_GPC['schoolid'],'bj_id' => $_GPC['bj_id'],'start' => strtotime($data['StartDate']),'end' => strtotime($data['EndDate']),'type' => 2,'planuid' => $data['PlanUid']);
            if (empty($check)) {
                pdo_insert($this->table_zjh, $date);
            } else {
                pdo_update($this->table_zjh, $date, array('planuid' => $data['PlanUid']));
            }
            $thisset = pdo_fetch("SELECT * FROM " . tablename($this->table_zjhset) . " where weid = :weid And schoolid = :schoolid And planuid = :planuid ", array(
                ':weid' => $_GPC['weid'],
                ':schoolid' => $_GPC['schoolid'],
                ':planuid' => $data['PlanUid']
            ));
            if (empty($thisset)) {
                if (is_showgkk()) {
                    pdo_insert($this->table_zjhset, array('weid' => $_GPC['weid'],'schoolid' => $_GPC['schoolid'],'activetypeid' => 'morning_activity','activetypename' => '工作安排','type' => 'AM','planuid' => $data['PlanUid']));
                    pdo_insert($this->table_zjhset, array('weid' => $_GPC['weid'],'schoolid' => $_GPC['schoolid'],'activetypeid' => 'teach_activity','activetypename' => '备注','type' => 'AM','planuid' => $data['PlanUid']));
                    pdo_insert($this->table_zjhset, array('weid' => $_GPC['weid'],'schoolid' => $_GPC['schoolid'],'activetypeid' => 'out_activity','activetypename' => '工作安排','type' => 'PM','planuid' => $data['PlanUid']));
                    pdo_insert($this->table_zjhset, array('weid' => $_GPC['weid'],'schoolid' => $_GPC['schoolid'],'activetypeid' => 'game_activity','activetypename' => '备注','type' => 'PM','planuid' => $data['PlanUid']));
                } else {
                    pdo_insert($this->table_zjhset, array('weid' => $_GPC['weid'],'schoolid' => $_GPC['schoolid'],'activetypeid' => 'morning_activity','activetypename' => '晨间活动','type' => 'AM','planuid' => $data['PlanUid']));
                    pdo_insert($this->table_zjhset, array('weid' => $_GPC['weid'],'schoolid' => $_GPC['schoolid'],'activetypeid' => 'teach_activity','activetypename' => '教学活动','type' => 'AM','planuid' => $data['PlanUid']));
                    pdo_insert($this->table_zjhset, array('weid' => $_GPC['weid'],'schoolid' => $_GPC['schoolid'],'activetypeid' => 'out_activity','activetypename' => '户外活动','type' => 'PM','planuid' => $data['PlanUid']));
                    pdo_insert($this->table_zjhset, array('weid' => $_GPC['weid'],'schoolid' => $_GPC['schoolid'],'activetypeid' => 'game_activity','activetypename' => '游戏活动','type' => 'PM','planuid' => $data['PlanUid']));
                }
            }
            $thisdetail = pdo_fetch("SELECT * FROM " . tablename($this->table_zjhdetail) . " where weid = :weid And schoolid = :schoolid And curactiveid = :curactiveid And week = :week And planuid = :planuid ", array(
                ':weid' => $_GPC['weid'],
                ':schoolid' => $_GPC['schoolid'],
                ':curactiveid' => $data['CurActiveId'],
                ':week' => $data['WeekDay'],
                ':planuid' => $data['PlanUid']
            ));
            if (empty($thisdetail['detailuid'])) { //如果上午下午项目为空 则写入一行set规则
                $detail = $data['lstPlanDetail'];//遍历前端输入的数组
                    $DetailUid = getRandomString(8).'-'.getRandomString(4).'-'.getRandomString(4).'-'.getRandomString(4).'-'.getRandomString(12); //
                    foreach ($detail as $mid => $row) {
                        $temp1 .= $row['ActiveDesc']."\\n";
                    }
                $temp = array(
                        'weid' => $_GPC['weid'],
                        'schoolid' => $_GPC['schoolid'],
                        'detailuid' => !empty($data['CurDetailUid']) ? $data['CurDetailUid'] : $DetailUid,
                        'curactiveid' => $data['CurActiveId'],
                        'curactivename' => $data['CurActiveName'],
                        'week' => $data['WeekDay'],
                        'planuid' => $data['PlanUid']
                    );
                $temp['activedesc'] = $temp1;
                pdo_insert($this->table_zjhdetail, $temp);
                $msg ['Status'] = 1;
                $msg ['ActiveId'] = $data['CurActiveId'];
                $msg ['DetailUid'] = $DetailUid;
                $msg ['PlanUid'] = $data['PlanUid'];
                $msg ['Result'] = '修改成功';
            } else {
                $detail = $data['lstPlanDetail'];//遍历前端输入的数组
                foreach ($detail as $mid => $row) {
                    $temp1 .= $row['ActiveDesc']."\\n";
                }
                $temp = array(
                    'weid' => $_GPC['weid'],
                    'schoolid' => $_GPC['schoolid'],
                    'curactiveid' => $data['CurActiveId'],
                    'curactivename' => $data['CurActiveName'],
                    'week' => $data['WeekDay'],
                    'planuid' => $data['PlanUid']
                );
                $temp['activedesc'] = $temp1;
                pdo_update($this->table_zjhdetail, $temp, array('detailuid' => $thisdetail['detailuid']));
                $msg ['Status'] = 1;
                $msg ['ActiveId'] = $data['CurActiveId'];
                $msg ['DetailUid'] = $thisdetail['detailuid'];
                $msg ['PlanUid'] = $data['PlanUid'];
                $msg ['Result'] = '修改成功';
            }
        }
        die(json_encode($msg));
    }
if ($operation == 'GetDetailByWeekDay') {
    $data = file_get_contents('php://input');
    if ($data) {
        $data = urldecode($data);
    }
    $data = json_decode($data, true);
    if (!$_GPC['weid'] || !$_GPC['schoolid'] || !$_GPC['bj_id']) {
        die(json_encode(array('Status' => 0, 'Result' => '您不是班主任，无权使用此功能',)));
    } else {
        $planuid = $_GPC['sPlanUid'];
        $weekday = $_GPC['nWeekDay'];
        $shangwu = pdo_fetchall("SELECT planuid as PlanUid, activetypeid as ActiveTypeId, activetypename as ActiveTypeName, type as ActiveTypeIcon FROM " . tablename($this->table_zjhset) . " where weid = :weid And schoolid = :schoolid And type = :type And planuid = :planuid ORDER BY id ASC", array(
            ':weid'     => $_GPC['weid'],
            ':schoolid' => $_GPC['schoolid'],
            ':type'     => 'AM',
            ':planuid'  => $_GPC['sPlanUid']
        ));
        foreach ($shangwu as $key => $row) {
            $shangwu[$key]['WeekDay'] = "";
            $detail = pdo_fetchall("SELECT curactiveid,detailuid,activedesc,week,curactivename  FROM " . tablename($this->table_zjhdetail) . " WHERE weid = '{$_GPC['weid']}' And schoolid = '{$_GPC['schoolid']}' And curactiveid = '{$row['ActiveTypeId']}' And planuid = '{$_GPC['sPlanUid']}'  And week = '{$_GPC['nWeekDay']}' ");
            $shangwu[$key]['WeekDay'] = "";
            $shangwu[$key]['ActiveDesc'] = "";
            foreach ($detail as $k => $r) {
                $shangwu[$key]['DetailUid'] = $r['detailuid'];
                $shangwu[$key]['ActiveDesc'] = empty($r['activedesc']) ? "" : $r['activedesc'];
                $shangwu[$key]['WeekDay'] = $r['week'];
            }
        }
        $xiawu = pdo_fetchall("SELECT planuid as PlanUid, activetypeid as ActiveTypeId, activetypename as ActiveTypeName, type as ActiveTypeIcon FROM " . tablename($this->table_zjhset) . " where weid = :weid And schoolid = :schoolid And type = :type And planuid = :planuid ORDER BY id ASC", array(
            ':weid'     => $_GPC['weid'],
            ':schoolid' => $_GPC['schoolid'],
            ':type'     => 'PM',
            ':planuid'  => $_GPC['sPlanUid']
        ));
        foreach ($xiawu as $key => $row) {
            $xiawu[$key]['WeekDay'] = "";
            $xiawu[$key]['ActiveDesc'] = "";
            $detail = pdo_fetchall("SELECT curactiveid,detailuid,activedesc,week,curactivename  FROM " . tablename($this->table_zjhdetail) . " WHERE weid = '{$_GPC['weid']}' And schoolid = '{$_GPC['schoolid']}' And curactiveid = '{$row['ActiveTypeId']}' And planuid = '{$_GPC['sPlanUid']}'  And week = '{$_GPC['nWeekDay']}' ");
            foreach ($detail as $k => $r) {
                $xiawu[$key]['DetailUid'] = $r['detailuid'];
                $xiawu[$key]['ActiveDesc'] = empty($r['activedesc']) ? "" : $r['activedesc'];
                $xiawu[$key]['WeekDay'] = $r['week'];
            }
        }
        $msg ['Status'] = 1;
        $msg ['lstPlanDetail'] = empty($shangwu) ? array() : $shangwu;//上午项目和详细内容
        $msg ['lstPMPlanDetail'] = empty($xiawu) ? array() : $xiawu;//下午项目和详细内容
        $msg ['lstPMPlanType'] = array();
        $msg ['PlanUid'] = $_GPC['sPlanUid'];
        $msg ['Result'] = '修改成功';
    }
    die(json_encode($msg));
}
if ($operation == 'GetAttendDataforTeacher') {
    if ($_GPC['sDate']) {
        $thistime = strtotime($_GPC['sDate']);
        $start_time = strtotime(date('Y-m-01', $thistime));
        $nowstart = strtotime(date('Y-m-01'));
        if ($start_time == $nowstart) {
            $j = date(j);
        } else {
            $j = date('t', $thistime);
        }
    }
    $array = array();
    for ($i=0;$i<$j;$i++) {
        $array[] = array(
                'date' => date('Y-m-d', $start_time+$i*86400),//每隔一天赋值给数组
                'day' => $i +1
        );
    }
    $result['lstAttendInfoOfTeacher'] = "";
    $days = 0;
    $nowtime = date('Y-m-d');
    foreach ($array as $key => $row) {
        $date = explode('-', $row['date']);
        $starttime = mktime(0, 0, 0, $date[1], $date[2], $date[0]);
        $endtime = $starttime + 86399;
        $condition = " AND createtime > '{$starttime}' AND createtime < '{$endtime}'";
        $condition1 = " AND (startime1 < '{$starttime}' AND endtime1 > '{$endtime}' OR startime1 > '{$starttime}' AND endtime1 < '{$endtime}')";
        $log = pdo_fetch("SELECT id FROM " . tablename($this->table_checklog) . " where schoolid = :schoolid AND tid = :tid And isconfirm = 1 $condition ", array(
                ':schoolid' => $_GPC['schoolid'],
                ':tid' => $_GPC['tid']
            ));
        $xsqj = pdo_fetch("SELECT startime1 FROM " . tablename($this->table_leave) . " where schoolid = '{$_GPC['schoolid']}' AND tid = '{$_GPC['tid']}' And sid = 0 And isliuyan = 0 And status = 1 $condition1");
        if ($log['id'] || $xsqj['startime1'] > 0) {
            if (!$xsqj) {
                $list[$key]['Type'] = "wx";
                $days++;
            } else {
                $list[$key]['Type'] = "leave";
            }
            $list[$key]['Date'] = $row['day'];
            $list[$key]['Uid'] = $_GPC['tid'];
            $list[$key]['Name'] = "";
            $list[$key]['Time'] = "";
            $list[$key]['Start'] = "";
            $list[$key]['End'] = "";
            $list[$key]['Url'] = "";
        } else {
            $list[$key]['Type'] = "skip";
            $list[$key]['Date'] = $row['day'];
            $list[$key]['Uid'] = "skip";
            $list[$key]['Name'] = "";
            $list[$key]['Time'] = "";
            $list[$key]['Start'] = "";
            $list[$key]['End'] = "";
            $list[$key]['Url'] = "";
        }
        $list[$key]['end'] = date('Y-m-d H:i:s', $endtime);
        $list[$key]['start'] = date('Y-m-d H:i:s', $starttime);
        $list[$key]['line'] = $xsqj;
    }
    $result['lstAttendInfo'] = $list;
    $result['TeacherName'] = $_GPC['tid'];
    $teacher = pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " where id = :id AND schoolid = :schoolid ", array(':id' => $_GPC['tid'], ':schoolid' => $_GPC['schoolid']));
    $result['StuName'] = $teacher['tname'];
    $result['AttendanceCount'] = $days;
    die(json_encode($result));
}
if ($operation == 'GetAttendData') {
    if ($_GPC['sDate']) {
        $thistime = strtotime($_GPC['sDate']);
        $start_time = strtotime(date('Y-m-01', $thistime));
        $nowstart = strtotime(date('Y-m-01'));
        if ($start_time == $nowstart) {
            $j = date(j);
        } else {
            $j = date('t', $thistime);
        }
    }
    $array = array();
    for ($i=0;$i<$j;$i++) {
        $array[] = array(
                'date' => date('Y-m-d', $start_time+$i*86400),//每隔一天赋值给数组
                'day' => $i +1
        );
    }
    $result['lstAttendInfoOfTeacher'] = "";
    $days = 0;
    $nowtime = date('Y-m-d');
    foreach ($array as $key => $row) {
        $date = explode('-', $row['date']);
        $starttime = mktime(0, 0, 0, $date[1], $date[2], $date[0]);
        $endtime = $starttime + 86399;
        $condition = " AND createtime > '{$starttime}' AND createtime < '{$endtime}'";
        $condition1 = " AND (startime1 < '{$starttime}' AND endtime1 > '{$endtime}' OR startime1 > '{$starttime}' AND endtime1 < '{$endtime}')";
        $log = pdo_fetch("SELECT id FROM " . tablename($this->table_checklog) . " where schoolid = :schoolid AND sid = :sid And isconfirm = 1 $condition ", array(
                ':schoolid' => $_GPC['schoolid'],
                ':sid' => $_GPC['sid']
            ));
        $xsqj = pdo_fetch("SELECT startime1 FROM " . tablename($this->table_leave) . " where schoolid = '{$_GPC['schoolid']}' AND sid = '{$_GPC['sid']}' And tid = 0 And isliuyan = 0 And status = 1 $condition1");
        if ($log['id'] || $xsqj['startime1'] > 0) {
            if (!$xsqj) {
                $list[$key]['Type'] = "wx";
                $days++;
            } else {
                $list[$key]['Type'] = "leave";
            }
            $list[$key]['Date'] = $row['day'];
            $list[$key]['Uid'] = $_GPC['sid'];
            $list[$key]['Name'] = "";
            $list[$key]['Time'] = "";
            $list[$key]['Start'] = "";
            $list[$key]['End'] = "";
            $list[$key]['Url'] = "";
        } else {
            $list[$key]['Type'] = "skip";
            $list[$key]['Date'] = $row['day'];
            $list[$key]['Uid'] = "skip";
            $list[$key]['Name'] = "";
            $list[$key]['Time'] = "";
            $list[$key]['Start'] = "";
            $list[$key]['End'] = "";
            $list[$key]['Url'] = "";
        }
        $list[$key]['end'] = date('Y-m-d H:i:s', $endtime);
        $list[$key]['start'] = date('Y-m-d H:i:s', $starttime);
        $list[$key]['line'] = $xsqj;
    }
    $result['lstAttendInfo'] = $list;
    $result['TeacherName'] = $_GPC['sid'];
    $student = pdo_fetch("SELECT s_name FROM " . tablename($this->table_students) . " where id = :id AND schoolid = :schoolid ", array(':id' => $_GPC['sid'], ':schoolid' => $_GPC['schoolid']));
    $result['StuName'] = $student['s_name'];
    $result['AttendanceCount'] = $days;
    die(json_encode($result));
}
if ($operation == 'CheckSignForTeacher') {
    $starttime = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
    $endtime = $starttime + 86399;
    $condition = " AND createtime > '{$starttime}' AND createtime < '{$endtime}'";
    $logjx = pdo_fetch("SELECT createtime FROM " . tablename($this->table_checklog) . " WHERE :schoolid = schoolid And :tid = tid And checktype = 2 And isconfirm = 1 And leixing = 1 $condition ORDER BY id DESC LIMIT 0,1", array(':schoolid' => $_GPC['schoolid'],':tid' => $_GPC['tid']));
    $loglx = pdo_fetch("SELECT createtime FROM " . tablename($this->table_checklog) . " WHERE :schoolid = schoolid And :tid = tid And checktype = 2 And isconfirm = 1 And leixing = 2 $condition ORDER BY id DESC LIMIT 0,1", array(':schoolid' => $_GPC['schoolid'],':tid' => $_GPC['tid']));

    if ($_GPC['type'] == 1) {
        if ($logjx) {
            $result['status'] = 1;
            $result['data'] = date('H:i:s', $logjx['createtime']);
        } else {
            $result['status'] = 2;
        }
    }
    if ($_GPC['type'] == 2) {
        if ($loglx) {
            $result['status'] = 1;
            $result['data'] = date('H:i:s', $loglx['createtime']);
        } else {
            $result['status'] = 2;
        }
    }
    die(json_encode($result));
}
if ($operation == 'DoSignForTeacher') {
    $school = pdo_fetch("SELECT is_wxsign FROM " . tablename($this->table_index) . " where id = :id ", array(':id' => $_GPC['schoolid']));
    if ($school['is_wxsign'] ==1) {
        if ($_GPC['type'] ==1) {
            $type = "进校";
        } else {
            $type = "离校";
        }
        $data = array(
                'weid' => $_W['uniacid'],
                'schoolid' => $_GPC['schoolid'],
                'tid' => $_GPC['tid'],
                'pard' => 1,
                'checktype' => 2,
                'isconfirm' => 1,
                'isread' => 2,
                'lon' => trim($_GPC['lon']),
                'lat' => trim($_GPC['lat']),
                'bet' => trim($_GPC['bet']),
                'type' => $type,
                'leixing' =>  $_GPC['type'],
                'createtime' => time()
            );
        pdo_insert($this->table_checklog, $data);
        $result['status'] = 1;
        $result['data'] = "long";
        $result['info'] = "签到成功,请勿重复签到";
        if ($type == "离校") {
            $result['info'] = "签离成功,请勿重复签离";
        }
    } else {
        $result['status'] = 2;
        $result['info'] = "抱歉,本校未启用微信辅助签到功能";
    }
    die(json_encode($result));
}
if ($operation == 'CheckSign') {
    $starttime = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
    $endtime = $starttime + 86399;
    $condition = " AND createtime > '{$starttime}' AND createtime < '{$endtime}'";
    $logjx = pdo_fetch("SELECT createtime FROM " . tablename($this->table_checklog) . " WHERE :schoolid = schoolid And :sid = sid And checktype = 2 And isconfirm = 1 And leixing = 1 $condition ORDER BY id DESC LIMIT 0,1", array(':schoolid' => $_GPC['schoolid'],':sid' => $_GPC['sid']));
    $loglx = pdo_fetch("SELECT createtime FROM " . tablename($this->table_checklog) . " WHERE :schoolid = schoolid And :sid = sid And checktype = 2 And isconfirm = 1 And leixing = 2 $condition ORDER BY id DESC LIMIT 0,1", array(':schoolid' => $_GPC['schoolid'],':sid' => $_GPC['sid']));

    if ($_GPC['type'] == 1) {
        if ($logjx) {
            $result['status'] = 1;
            $result['data'] = date('H:i:s', $logjx['createtime']);
        } else {
            $result['status'] = 2;
        }
    }
    if ($_GPC['type'] == 2) {
        if ($loglx) {
            $result['status'] = 1;
            $result['data'] = date('H:i:s', $loglx['createtime']);
        } else {
            $result['status'] = 2;
        }
    }
    die(json_encode($result));
}
if ($operation == 'DoSign') {
    $school = pdo_fetch("SELECT is_signneedcomfim,is_wxsign FROM " . tablename($this->table_index) . " where id = :id ", array(':id' => $_GPC['schoolid']));
    if ($school['is_wxsign'] ==1) {
        if ($_GPC['pard'] == 2) {
            $pard = 2;
        }
        if ($_GPC['pard'] == 3) {
            $pard = 3;
        }
        if ($_GPC['pard'] == 4) {
            $pard = 1;
        }
        if ($_GPC['pard'] == 5) {
            $pard = 10;
        }
        if ($_GPC['type'] ==1) {
            $type = "进校";
        } else {
            $type = "离校";
        }
        if ($school['is_signneedcomfim'] == 1) {
            $data = array(
                'weid' => $_W['uniacid'],
                'schoolid' => $_GPC['schoolid'],
                'sid' => $_GPC['sid'],
                'bj_id' => $_GPC['bj_id'],
                'pard' => $pard,
                'checktype' => 2,
                'isconfirm' => 2,
                'type' => $type,
                'leixing' => $_GPC['type'],
                'createtime' => time()
            );
            pdo_insert($this->table_checklog, $data);
            $logid = pdo_insertid();
            $result['status'] = 1;
            $result['data'] = "wait";
            $result['data1'] = $pard;
            $result['info'] = "签到信息发送成功,请等待确认";
            $this ->sendMobileSignshtz($logid);
        // $this->sendMobileJxlxtz($_GPC['schoolid'], $_W['uniacid'], $_GPC['bj_id'], $_GPC['sid'], $type, $_GPC['type'], $logid, $pard);
        } else {
            $data = array(
                'weid' => $_W['uniacid'],
                'schoolid' => $_GPC['schoolid'],
                'sid' => $_GPC['sid'],
                'bj_id' => $_GPC['bj_id'],
                'pard' => $pard,
                'checktype' => 2,
                'isconfirm' => 1,
                'type' => $type,
                'leixing' =>  $_GPC['type'],
                'createtime' => time()
            );
            pdo_insert($this->table_checklog, $data);
            $result['status'] = 1;
            $result['data'] = "long";
            $result['info'] = "签到成功,请勿重复签到";
        }
    } else {
        $result['status'] = 2;
        $result['info'] = "抱歉,本校未启用微信辅助签到功能";
    }
    die(json_encode($result));
}
if ($operation == 'fzqdqr') {
    $logids = explode(',', $_GPC ['logids']);
    if ($logids) {
        foreach ($logids as $row) {
            if ($row >0) {
                pdo_update($this->table_checklog, array('isconfirm' => 1), array('id' => $row));
                $this ->sendMobileFzqdshjg($row);
            }
        }
        $result['info'] = "确认成功！";
        $actop = 'qdqr';
        $userid = $_GPC['userid'];
        $weid = $_GPC['weid'];
        $schoolid = $_GPC['schoolid'];
        $point = PointAct($weid, $schoolid, $userid, $actop);
        $point1 = PointMission($weid, $schoolid, $userid, $actop);
            
        if (!empty($point)) {
            $result ['info'] = '确认成功!积分+'.$point;
        }
    } else {
        $result['info'] = "您没有选择学生！";
    }
    die(json_encode($result));
}
if ($operation == 'fzqd') {
    $sids = explode(',', $_GPC['sids']);
    if ($sids) {
        if ($_GPC['TimeType'] ==1) {
            $type = "进校";
        } else {
            $type = "离校";
        }
        $rs = 0;
        if (!empty($_GPC['Time'])) {
            $date = explode('-', $_GPC['Time']);
            $starttime = mktime(0, 0, 0, $date[1], $date[2], $date[0]);
            $endtime = $starttime + 30399;
        }
        foreach ($sids as $row) {
            if ($row >0) {
                $data = array(
                    'weid' => $_W['uniacid'],
                    'schoolid' => $_GPC['schoolid'],
                    'sid' => $row,
                    'bj_id' => $_GPC['bj_id'],
                    'pard' => 11,
                    'checktype' => 2,
                    'isconfirm' => 1,
                    'type' => $type,
                    'leixing' =>  $_GPC['TimeType'],
                    'qdtid' =>  $_GPC['tid'],
                    'createtime' => empty($_GPC['Time']) ? time() : $endtime
                );
                $pard = 11;
                pdo_insert($this->table_checklog, $data);
                $logid = pdo_insertid();
                $macid = 'wechatSign';
                if (is_showyl()) {
                    $this->sendMobileJxlxtz_yl($_GPC['schoolid'], $_W['uniacid'], $row, $logid, $macid);
                } else {
                    $this->sendMobileFzqdtx($_GPC['schoolid'], $_W['uniacid'], $_GPC['bj_id'], $row, $type, $_GPC['TimeType'], $logid, $pard);
                }
                $rs ++;
            }
        }
        $result['info'] = "成功签到".$rs."个学生";
        $result['nowtime'] =date("Y-m-d", time());
        $result['signNum'] =$rs;
        $actop = 'bqxs';
        $userid = $_GPC['userid'];
        $weid = $_GPC['weid'];
        $schoolid = $_GPC['schoolid'];
        $point = PointAct($weid, $schoolid, $userid, $actop);
        $point1 = PointMission($weid, $schoolid, $userid, $actop);
            
        if (!empty($point)) {
            $result ['info'] = '补签成功!积分+'.$point;
        }
    } else {
        $result['info'] = "您没有选择学生！";
    }
    die(json_encode($result));
}
if ($operation == 'checklogbyid') {
    $date = explode('-', $_GPC['time']);
    $starttime = mktime(0, 0, 0, $date[1], $date[2], $date[0]);
    $endtime = $starttime + 86399;
    $condition = " AND createtime > '{$starttime}' AND createtime < '{$endtime}'";
    $condition1 = "";
    if ($_GPC['timeType']) {
        $condition1 = " AND leixing = '{$_GPC['timeType']}' ";
    }
    if ($_GPC['sid']) {
        $log = pdo_fetchall("SELECT * FROM " . tablename($this->table_checklog) . " where schoolid = :schoolid AND sid = :sid And isconfirm = 1 $condition1 $condition ORDER BY createtime DESC", array(
            ':schoolid' => $_GPC['schoolid'],
            ':sid' => $_GPC['sid']
        ));
    } else {
        $log = pdo_fetchall("SELECT * FROM " . tablename($this->table_checklog) . " where schoolid = :schoolid AND tid = :tid And isconfirm = 1 $condition1 $condition ORDER BY createtime DESC", array(
            ':schoolid' => $_GPC['schoolid'],
            ':tid' => $_GPC['tid']
        ));
    }
    if ($log) {
        foreach ($log as $key => $row) {
            if ($row['checktype'] ==1) {
                $item   = pdo_fetch("SELECT name FROM " . tablename($this->table_checkmac) . " WHERE id = {$row['macid']} ");
                $log[$key]['Macname'] = $item['name'];
                $log[$key]['Type'] = "card";
            } else {
                $log[$key]['Type'] = "wx";
            }
            if ($row['temperature']) {
                $log[$key]['Temp'] = $row['temperature']."℃";
            } else {
                $log[$key]['Temp'] = "未测";
            }
            $log[$key]['Date'] = 0;
            $log[$key]['Uid'] = "";
            if ($_GPC['tid']) {
                $teacher = pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " where id = :id AND schoolid = :schoolid ", array(':id' => $_GPC['tid'], ':schoolid' => $_GPC['schoolid']));
                $log[$key]['Name'] = $teacher['tname'];
            } else {
                $student = pdo_fetch("SELECT s_name FROM " . tablename($this->table_students) . " where id = :id AND schoolid = :schoolid ", array(':id' => $row['sid'], ':schoolid' => $_GPC['schoolid']));
                $pard = getpard($row['pard']);
                $log[$key]['Name'] = $student['s_name'].$pard;
            }
            
            $log[$key]['Time'] = date('Y-m-d H:i:s', $row['createtime']);
            $log[$key]['Start'] = "";
            $log[$key]['End'] = "";
            if (!empty($row['pic'])) {
                if (preg_match('/(http:\/\/)|(https:\/\/)/i', $row['pic'])) {
                    load()->func('file');
                    if (preg_match('/wmpickq/i', $row['pic']) || preg_match('/kaoqin/i', $row['pic'])) {
                        if (preg_match('/wmpickq/i', $row['pic'])) {
                            $img = getImg($row['pic']);
                            if (!empty($img)) {
                                $path = "images/fm_jiaoyu/check_pic/". date('Y/m/d/');
                                if (!is_dir(IA_ROOT."/attachment/". $path)) {
                                    mkdirs(IA_ROOT."/attachment/". $path, "0777");
                                }
                                $picurl = $path.random(30).".jpg";
                                file_write($picurl, $img);
                                if (!empty($_W['setting']['remote']['type'])) { //
                                        $remotestatus = file_remote_upload($picurl); //
                                        if (is_error($remotestatus)) {
                                            message('远程附件上传失败，请检查配置并重新上传');
                                        }
                                }
                            }
                            pdo_update($this->table_checklog, array('pic' => $picurl), array('id' => $row['id']));
                            $log[$key]['Url'] = $_W['attachurl'].$picurl;
                        }
                        if (preg_match('/kaoqin/i', $row['pic'])) {
                            $log[$key]['Url'] = $row['pic'];
                        }
                    } else {
                        $path = "images/fm_jiaoyu/check/". date('Y/m/d/');
                        if (!is_dir(IA_ROOT."/attachment/". $path)) {
                            mkdirs(IA_ROOT."/attachment/". $path, "0777");
                        }
                        $picurl = $path.random(30) .".jpg";
                        $pic_data = getimg_form_oss($row['pic']);
                        file_write($picurl, $pic_data);
                        if (!empty($_W['setting']['remote']['type'])) {
                            $remotestatus = file_remote_upload($picurl);
                            if (is_error($remotestatus)) {
                                message('远程附件上传失败，请检查配置并重新上传');
                            }
                        }
                        pdo_update($this->table_checklog, array('pic' => $picurl), array('id' => $row['id']));
                        $log[$key]['Url'] = $_W['attachurl'].$picurl;
                    }
                } else {
                    $log[$key]['Url'] = $_W['attachurl'].$row['pic'];
                }
            }
            if (!empty($row['pic2'])) {
                if (preg_match('/(http:\/\/)|(https:\/\/)/i', $row['pic2'])) {
                    load()->func('file');
                    if (preg_match('/wmpickq/i', $row['pic2']) || preg_match('/kaoqin/i', $row['pic2'])) {
                        if (preg_match('/wmpickq/i', $row['pic2'])) {
                            $img = getImg($row['pic2']);
                            if (!empty($img)) {
                                $path = "images/fm_jiaoyu/check_pic/". date('Y/m/d/');
                                if (!is_dir(IA_ROOT."/attachment/". $path)) {
                                    mkdirs(IA_ROOT."/attachment/". $path, "0777");
                                }
                                $picurl2 = $path.random(30).".jpg";
                                file_write($picurl2, $img);
                                if (!empty($_W['setting']['remote']['type'])) { //
                                        $remotestatus = file_remote_upload($picurl2); //
                                        if (is_error($remotestatus)) {
                                            message('远程附件上传失败，请检查配置并重新上传');
                                        }
                                }
                            }
                            pdo_update($this->table_checklog, array('pic2' => $picurl2), array('id' => $row['id']));
                            $log[$key]['Url2'] = $_W['attachurl'].$picurl2;
                        }
                        if (preg_match('/kaoqin/i', $row['pic2'])) {
                            $log[$key]['Url2'] = $row['pic2'];
                        }
                    } else {
                        $path = "images/fm_jiaoyu/check/". date('Y/m/d/');
                        if (!is_dir(IA_ROOT."/attachment/". $path)) {
                            mkdirs(IA_ROOT."/attachment/". $path, "0777");
                        }
                        $picurl2 = $path.random(30) .".jpg";
                        $pic_data = getimg_form_oss($row['pic2']);
                        file_write($picurl2, $pic_data);
                        if (!empty($_W['setting']['remote']['type'])) {
                            $remotestatus = file_remote_upload($picurl2);
                            if (is_error($remotestatus)) {
                                message('远程附件上传失败，请检查配置并重新上传');
                            }
                        }
                        pdo_update($this->table_checklog, array('pic2' => $picurl2), array('id' => $row['id']));
                        $log[$key]['Url2'] = $_W['attachurl'].$picurl2;
                    }
                } else {
                    $log[$key]['Url2'] = $_W['attachurl'].$row['pic2'];
                }
            }
            pdo_update($this->table_checklog, array('isread' => 2), array('id' => $row['id']));
        }
        $result = $log;
    }
    die(json_encode($result));
}
if ($operation == 'qingjialog') {
    $date = explode('-', $_GPC['time']);
    $starttime = mktime(0, 0, 0, $date[1], $date[2], $date[0]);
    $endtime = $starttime + 86399;
    //$condition = " AND startime1 < '{$starttime}' AND endtime1 > '{$endtime}'";
    $condition = " AND (startime1 < '{$starttime}' AND endtime1 > '{$endtime}' OR startime1 > '{$starttime}' AND endtime1 < '{$endtime}')";
    if ($_GPC['sid']) {
        $xsqj = pdo_fetch("SELECT * FROM " . tablename($this->table_leave) . " where schoolid = :schoolid AND sid = :sid And tid = 0 And isliuyan = 0 And status = 1 $condition", array(
            ':schoolid' => $_GPC['schoolid'],
            ':sid' => $_GPC['sid']
        ));
        if ($xsqj) {
            $student = pdo_fetch("SELECT s_name FROM " . tablename($this->table_students) . " where id = :id ", array(':id' => $xsqj['sid']));
            $log['ParentName'] = $student['s_name'];
            $log['StartTime'] = date('m-d H:i', $xsqj['startime1']);
            $log['EndTime'] = date('m-d H:i', $xsqj['endtime1']);
            $log['LeaveContent'] = $xsqj['conet'];
            $log['CreateTime'] = date('Y-m-d H:i', $xsqj['createtime']);
        }
    }
    if ($_GPC['tid']) {
        $xsqj = pdo_fetch("SELECT * FROM " . tablename($this->table_leave) . " where schoolid = :schoolid AND tid = :tid And sid = 0 And isliuyan = 0 And status = 1 $condition", array(
            ':schoolid' => $_GPC['schoolid'],
            ':tid' => $_GPC['tid']
        ));
        if ($xsqj) {
            $teacher = pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " where id = :id ", array(':id' => $xsqj['tid']));
            $log['ParentName'] = $teacher['tname'];
            $log['StartTime'] = date('m-d H:i', $xsqj['startime1']);
            $log['EndTime'] = date('m-d H:i', $xsqj['endtime1']);
            $log['LeaveContent'] = $xsqj['conet'];
            $log['CreateTime'] = date('Y-m-d H:i', $xsqj['createtime']);
        }
    }
    die(json_encode($log));
}
if ($operation == 'videodz') {
    if ($_GPC['thisop'] == 'mybj') {
        $dianz = pdo_fetch("SELECT id FROM " . tablename($this->table_camerapl) . " where schoolid = :schoolid AND bj_id = :bj_id AND userid = :userid AND type = :type", array(
            ':schoolid' => $_GPC['schoolid'],
            ':bj_id' => $_GPC['bj_id'],
            ':userid' => $_GPC['userid'],
            ':type' => 1,
        ));
        if ($dianz) { //有则取消点赞
            pdo_delete($this->table_camerapl, array('id' => $dianz['id']));
        } else {
            $data = array(
                'weid' => $_GPC['weid'],
                'schoolid' => $_GPC['schoolid'],
                'bj_id' => $_GPC['bj_id'],
                'carmeraid' => 0,
                'userid' => $_GPC['userid'],
                'type' => 1,
                'createtime' => time()
            );
            pdo_insert($this->table_camerapl, $data);
        }
        $result['info'] = "点赞成功！";
    } else {
        if (!empty($_GPC['ksid'])) {
            $dianz = pdo_fetch("SELECT id FROM " . tablename($this->table_camerapl) . " where schoolid = :schoolid AND ksid = :ksid AND userid = :userid AND type = :type", array(
                ':schoolid' => trim($_GPC['schoolid']),
                ':ksid' => trim($_GPC['ksid']),
                ':userid' => trim($_GPC['userid']),
                ':type' => 1,
            ));
            if ($dianz) { //有则取消点赞
                pdo_delete($this->table_camerapl, array('id' => $dianz['id']));
            } else {
                $temp = array(
                    'weid' => trim($_GPC['weid']),
                    'schoolid' => trim($_GPC['schoolid']),
                    'ksid' => trim($_GPC['ksid']),
                    'bj_id' => 0,
                    'userid' => trim($_GPC['userid']),
                    'type' => 1,
                    'createtime' => time(),
                );
                pdo_insert($this->table_camerapl, $temp);
            }
        } else {
            $dianz = pdo_fetch("SELECT id FROM " . tablename($this->table_camerapl) . " where schoolid = :schoolid AND carmeraid = :carmeraid AND userid = :userid AND type = :type", array(
                ':schoolid' => trim($_GPC['schoolid']),
                ':carmeraid' => trim($_GPC['videoid']),
                ':userid' => trim($_GPC['userid']),
                ':type' => 1,
            ));
            if ($dianz) { //有则取消点赞
                pdo_delete($this->table_camerapl, array('id' => $dianz['id']));
            } else {
                $temp = array(
                    'weid' => trim($_GPC['weid']),
                    'schoolid' => trim($_GPC['schoolid']),
                    'carmeraid' => trim($_GPC['videoid']),
                    'bj_id' => 0,
                    'userid' => trim($_GPC['userid']),
                    'type' => 1,
                    'createtime' => time(),
                );
                pdo_insert($this->table_camerapl, $temp);
            }
        }
        
        $result['info'] = "点赞成功！";
    }
    die(json_encode($result));
}
if ($operation == 'videopl') {
    if ($_GPC['thisop'] == 'mybj') {
        $data = array(
            'weid' => $_GPC['weid'],
            'schoolid' => $_GPC['schoolid'],
            'bj_id' => $_GPC['bj_id'],
            'carmeraid' => 0,
            'userid' => $_GPC['userid'],
            'conet' => trim($_GPC['commentContext']),
            'type' => 2,
            'createtime' => time()
        );
        pdo_insert($this->table_camerapl, $data);
        $plid = pdo_insertid();
        $result['data'] = $plid;
        $result['info'] = "评论成功！";
    } else {
        $temp = array(
            'weid' => trim($_GPC['weid']),
            'schoolid' => trim($_GPC['schoolid']),
            'carmeraid' => trim($_GPC['videoid']),
            'ksid' => trim($_GPC['ksid']),
            'openid' => $_GPC['openid'],
            'bj_id' => 0,
            'userid' => trim($_GPC['userid']),
            'conet' => trim($_GPC['commentContext']),
            'type' => 2,
            'createtime' => time(),
        );
        pdo_insert($this->table_camerapl, $temp);
        $plid = pdo_insertid();
        $result['data'] = $plid;
        $result['info'] = "评论成功！";
    }
    die(json_encode($result));
}
if ($operation == 'delmypl') {
    $pl = pdo_fetch("SELECT id FROM " . tablename($this->table_camerapl) . " where id = :id ", array(':id' => trim($_GPC['id'])));
    if ($pl['id']) {
        pdo_delete($this->table_camerapl, array('id' => $pl['id']));
        $result['info'] = "删除成功";
    } else {
        $result['info'] = "非法请求";
    }
    die(json_encode($result));
}
if ($operation == 'delnotice') {
    $notie = pdo_fetch("SELECT id,ali_vod_id FROM " . tablename($this->table_notice) . " where id = :id ", array(':id' => trim($_GPC['sNotifyUid'])));
    if ($notie['id']) {
        if ($notie['ali_vod_id']) {
            mload()->model('ali');
            $aliyun = GetAliApp($_W['uniacid'], $_GPC['schoolid']);
            $appid = $aliyun['alivodappid'];
            $key = $aliyun['alivodkey'];
            DelAlivod($appid, $key, $notie['ali_vod_id']);
        }
        pdo_delete($this->table_notice, array('id' => $notie['id']));
        pdo_delete($this->table_record, array('noticeid' => $notie['id']));
        $result['status'] = 1;
        $result['info'] = "删除成功";
    } else {
        $result['status'] = 2;
        $result['info'] = "非法请求";
    }
    die(json_encode($result));
}
if ($operation == 'getcook') {
    $date = date('Y-m-d', $_GPC['time']);
    $riqi = explode('-', $date);
    $starttime = mktime(0, 0, 0, $riqi[1], $riqi[2], $riqi[0]);
    $endtime = $starttime + 86399;
    $condition = " AND begintime <= '{$starttime}' AND endtime >= '{$endtime}'";
    $cook = pdo_fetch("SELECT * FROM " . tablename($this->table_cook) . " WHERE schoolid = :schoolid AND ishow = 1 $condition", array(':schoolid' => $_GPC['schoolid']));
    if ($cook['monday'] || $cook['tuesday'] || $cook['wednesday'] || $cook['thursday'] || $cook['friday'] || $cook['saturday'] || $cook['sunday']) {
        $week = date("w", $endtime);
        if ($week ==1) {
            $thecook = iunserializer($cook['monday']);
            $zc = $thecook['mon_zc'];
            $zcpic = $thecook['mon_zc_pic'];
            $zjc = $thecook['mon_zjc'];
            $zjcpic = $thecook['mon_zjc_pic'];
            $wc = $thecook['mon_wc'];
            $wcpic = $thecook['mon_wc_pic'];
            $wjc = $thecook['mon_wjc'];
            $wjcpic = $thecook['mon_wjc_pic'];
            $wwc = $thecook['mon_wwc'];
            $wwcpic = $thecook['mon_wwc_pic'];
        }
        if ($week ==2) {
            $thecook = iunserializer($cook['tuesday']);
            $zc = $thecook['tus_zc'];
            $zcpic = $thecook['tus_zc_pic'];
            $zjc = $thecook['tus_zjc'];
            $zjcpic = $thecook['tus_zjc_pic'];
            $wc = $thecook['tus_wc'];
            $wcpic = $thecook['tus_wc_pic'];
            $wjc = $thecook['tus_wjc'];
            $wjcpic = $thecook['tus_wjc_pic'];
            $wwc = $thecook['tus_wwc'];
            $wwcpic = $thecook['tus_wwc_pic'];
        }
        if ($week ==3) {
            $thecook = iunserializer($cook['wednesday']);
            $zc = $thecook['wed_zc'];
            $zcpic = $thecook['wed_zc_pic'];
            $zjc = $thecook['wed_zjc'];
            $zjcpic = $thecook['wed_zjc_pic'];
            $wc = $thecook['wed_wc'];
            $wcpic = $thecook['wed_wc_pic'];
            $wjc = $thecook['wed_wjc'];
            $wjcpic = $thecook['wed_wjc_pic'];
            $wwc = $thecook['wed_wwc'];
            $wwcpic = $thecook['wed_wwc_pic'];
        }
        if ($week ==4) {
            $thecook = iunserializer($cook['thursday']);
            $zc = $thecook['thu_zc'];
            $zcpic = $thecook['thu_zc_pic'];
            $zjc = $thecook['thu_zjc'];
            $zjcpic = $thecook['thu_zjc_pic'];
            $wc = $thecook['thu_wc'];
            $wcpic = $thecook['thu_wc_pic'];
            $wjc = $thecook['thu_wjc'];
            $wjcpic = $thecook['thu_wjc_pic'];
            $wwc = $thecook['thu_wwc'];
            $wwcpic = $thecook['thu_wwc_pic'];
        }
        if ($week ==5) {
            $thecook = iunserializer($cook['friday']);
            $zc = $thecook['fri_zc'];
            $zcpic = $thecook['fri_zc_pic'];
            $zjc = $thecook['fri_zjc'];
            $zjcpic = $thecook['fri_zjc_pic'];
            $wc = $thecook['fri_wc'];
            $wcpic = $thecook['fri_wc_pic'];
            $wjc = $thecook['fri_wjc'];
            $wjcpic = $thecook['fri_wjc_pic'];
            $wwc = $thecook['fri_wwc'];
            $wwcpic = $thecook['fri_wwc_pic'];
        }
        if ($week ==6) {
            $thecook = iunserializer($cook['saturday']);
            $zc = $thecook['sat_zc'];
            $zcpic = $thecook['sat_zc_pic'];
            $zjc = $thecook['sat_zjc'];
            $zjcpic = $thecook['sat_zjc_pic'];
            $wc = $thecook['sat_wc'];
            $wcpic = $thecook['sat_wc_pic'];
            $wjc = $thecook['sat_wjc'];
            $wjcpic = $thecook['sat_wjc_pic'];
            $wwc = $thecook['sat_wwc'];
            $wwcpic = $thecook['sat_wwc_pic'];
        }
        if ($week ==0) {
            $thecook = iunserializer($cook['sunday']);
            $zc = $thecook['sun_zc'];
            $zcpic = $thecook['sun_zc_pic'];
            $zjc = $thecook['sun_zjc'];
            $zjcpic = $thecook['sun_zjc_pic'];
            $wc = $thecook['sun_wc'];
            $wcpic = $thecook['sun_wc_pic'];
            $wjc = $thecook['sun_wjc'];
            $wjcpic = $thecook['sun_wjc_pic'];
            $wwc = $thecook['sun_wwc'];
            $wwcpic = $thecook['sun_wwc_pic'];
        }
        if ($zcpic || $zjcpic || $wcpic || $wjcpic || $wwcpic) {
            $result['data']['zc'] = $zc;
            $result['data']['zcpic'] = $zcpic;
            $result['data']['zjc'] = $zjc;
            $result['data']['zjcpic'] = $zjcpic;
            $result['data']['wc'] = $wc;
            $result['data']['wcpic'] = $wcpic;
            $result['data']['wjc'] = $wjc;
            $result['data']['wjcpic'] = $wjcpic;
            $result['data']['wwc'] = $wwc;
            $result['data']['wwcpic'] = $wwcpic;
            $result['info'] = 1;
        } else {
            $result['info'] = 2;
        }
    } else {
        $result['info'] = 2;
    }
    die(json_encode($result));
}
    if ($operation == 'zhuida') {
        load()->func('communication');
        load()->func('file');
        // $photoUrls = explode ( ',', $_GPC ['photoUrls'] );
        $data = explode('|', $_GPC ['json']);
        if ($_GPC ['photoUrls']) {
            $picurl = array();
            $photo = $_GPC ['photoUrls'];
            for ($i = 0; $i <= 9 ; $i++) {
                if (!empty($photo[$i])) {
                    $picurl[$i] = $photo[$i];
                }
            }
            $picstr = array('p1' => $picurl[0],'p2' => $picurl[1],'p3' => $picurl[2],'p4' => $picurl[3],'p5' => $picurl[4],'p6' => $picurl[5],'p7' => $picurl[6],'p8' => $picurl[7],'p9' => $picurl[8],);
        }
        $video = '';
        if (!empty($_GPC['videoMediaId'])) {
            $msgtype = 3;//视频
            mload()->model('ali');
            $aliyun = GetAliApp($_W['uniacid'], $_GPC['schoolid']);
            if ($aliyun['result']) {
                $appid = $aliyun['alivodappid'];
                $key = $aliyun['alivodkey'];
                do {
                    $GetAliVideoUrl = GetAliVideoUrl($appid, $key, trim($_GPC['videoMediaId']));
                } while (empty($GetAliVideoUrl['PlayURL']));
                do {
                    $GetAliVideoCover = GetAliVideoCover($appid, $key, trim($_GPC['videoMediaId']));
                } while (empty($GetAliVideoCover['CoverURL']));
                $video = $GetAliVideoUrl['PlayURL'];
                $videoimg = $GetAliVideoCover['CoverURL'];
            }
        }
        $audios = $_GPC ['audioServerid'];
        
        $audio = $audios[0];
        if ($audio) {
            $versionfile = IA_ROOT . '/addons/fm_jiaoyu/inc/func/auth2.php';
            require $versionfile;
            $mp3name = str_replace('images/bjq/vioce/', '', $audio);
            $mp3 = str_replace('.mp3', '', $mp3name);
            delvioce($mp3, FM_JIAOYU_HOST);
        }
        $setting = pdo_fetch("SELECT * FROM " . tablename($this->table_set) . " WHERE :weid = weid", array(':weid' => $_GPC['weid']));
        if (! $_GPC ['schoolid'] || ! $_GPC ['weid']) {
            die(json_encode(array(
                    'result' => false,
                    'msg' => '非法请求！'
                       )));
        } else {
            if (empty($_GPC['openid'])) {
                die(json_encode(array(
                    'result' => false,
                    'msg' => '非法请求,请刷新页面！'
                       )));
            } else {
                $schoolid   = $_GPC['schoolid'];
                $title      = $_GPC['title'];
                $weid       = $_GPC['weid'];
                $content    = $_GPC['content'];
                $sid        = $_GPC['sid'];
                $uid        = $_GPC['uid'];
                $zyid       = trim($_GPC['zyid']);
                $openid     = $_GPC['openid'];
                $bj_id      = $_GPC['bj_id'];
                $km_id      = $_GPC['km_id'];
                $audios     = $_GPC ['audioServerid'];
                $audio      = $audios[0];
                $audiotimes = $_GPC['audioTime'];
                $audiotime  = $audiotimes[0];
                $tname      = $_GPC['tname'];
                $shername   = $tname.'老师';
                
                $yasuo_temp = array(
                    'text' => $content,
                    'video' => $video,
                    'videopic' => $videoimg,
                    'audio' => $audio,
                    'ali_vod_id'=>trim($_GPC['videoMediaId']),
                    'audiotime' => $audiotime,
                    'picarr'  => $picstr
                );
                $MyAnswer = iserializer($yasuo_temp);
                $temp = array(
                    'weid' =>  $weid,
                    'schoolid' => $schoolid,
                    'sid' => $sid,
                    'zyid' => $zyid,
                    'userid' =>$uid,
                    'type' => 7,
                    'createtime' => time(),
                    'MyAnswer' =>$MyAnswer
                );
                
                pdo_insert($this->table_answers, $temp);
                $notice_id = pdo_insertid();
            
                if (empty($notice_id)) {
                    $data ['status'] = 1;
                    $data ['result'] = true;
                    $data ['msg'] ='回答失败！' ;
                } else {
                    $data ['status'] = 1;
                    $data ['result'] = true;
               
                    $data ['msg'] ='回答成功，请勿重复操作' ;
                }
            }
            die(json_encode($data));
        }
    }
    
if ($operation == 't_piyue') {
    load()->func('communication');
    load()->func('file');
    // $photoUrls = explode ( ',', $_GPC ['photoUrls'] );
    $data = explode('|', $_GPC ['json']);
    if ($_GPC ['photoUrls']) {
        $picurl = array();
        $photo = $_GPC ['photoUrls'];
        for ($i = 0; $i <= 9 ; $i++) {
            if (!empty($photo[$i])) {
                $picurl[$i] = $photo[$i];
            }
        }
        $picstr = array('p1' => $picurl[0],'p2' => $picurl[1],'p3' => $picurl[2],'p4' => $picurl[3],'p5' => $picurl[4],'p6' => $picurl[5],'p7' => $picurl[6],'p8' => $picurl[7],'p9' => $picurl[8],);
    }
    $video = '';
    if (!empty($_GPC['videoMediaId'])) {
        $msgtype = 3;//视频
        mload()->model('ali');
        $aliyun = GetAliApp($_W['uniacid'], $_GPC['schoolid']);
        if ($aliyun['result']) {
            $appid = $aliyun['alivodappid'];
            $key = $aliyun['alivodkey'];
            do {
                $GetAliVideoUrl = GetAliVideoUrl($appid, $key, trim($_GPC['videoMediaId']));
            } while (empty($GetAliVideoUrl['PlayURL']));
            do {
                $GetAliVideoCover = GetAliVideoCover($appid, $key, trim($_GPC['videoMediaId']));
            } while (empty($GetAliVideoCover['CoverURL']));
            $video = $GetAliVideoUrl['PlayURL'];
            $videoimg = $GetAliVideoCover['CoverURL'];
        }
    }
    $audios = $_GPC ['audioServerid'];
    
    $audio = $audios[0];
    if ($audio) {
        $versionfile = IA_ROOT . '/addons/fm_jiaoyu/inc/func/auth2.php';
        require $versionfile;
        $mp3name = str_replace('images/bjq/vioce/', '', $audio);
        $mp3 = str_replace('.mp3', '', $mp3name);
        delvioce($mp3, FM_JIAOYU_HOST);
    }
    $setting = pdo_fetch("SELECT * FROM " . tablename($this->table_set) . " WHERE :weid = weid", array(':weid' => $_GPC['weid']));
    if (! $_GPC ['schoolid'] || ! $_GPC ['weid']) {
        die(json_encode(array(
                'result' => false,
                'msg' => '非法请求！'
                   )));
    } else {
        if (empty($_GPC['openid'])) {
            die(json_encode(array(
                'result' => false,
                'msg' => '非法请求,请刷新页面！'
                   )));
        } else {
            $schoolid   = $_GPC['schoolid'];
            $title      = $_GPC['title'];
            $weid       = $_GPC['weid'];
            $content    = $_GPC['content'];
            $sid        = $_GPC['sid'];
            $uid        = $_GPC['userid'];
            $zyid       = trim($_GPC['zyid']);
            $openid     = $_GPC['openid'];
            $bj_id      = $_GPC['bj_id'];
            $km_id      = $_GPC['km_id'];
            $audios     = $_GPC ['audioServerid'];
            $audio      = $audios[0];
            $audiotimes = $_GPC['audioTime'];
            $audiotime  = $audiotimes[0];
            $tname      = $_GPC['tname'];
            $shername   = $tname.'老师';
            
            $yasuo_temp = array(
                'text' => $content,
                'video' => $video,
                'videopic' => $videoimg,
                'audio' => $audio,
                'ali_vod_id'=>trim($_GPC['videoMediaId']),
                'audiotime' => $audiotime,
                'picarr'  => $picstr
            );
            $MyAnswer = iserializer($yasuo_temp);
            $temp = array(
                'weid' =>  $weid,
                'schoolid' => $schoolid,
                'tid' => $_GPC['tid'],
                'tname'=> $_GPC['tname'],
                'sid' => $sid,
                'zyid' => $zyid,
                'userid' =>$uid,
                'type' => 2,
                'createtime' => time(),
                'content' =>$content,
                'audio' =>  $audio,
                'audiotime' => $audiotime
            );
            
            pdo_insert($this->table_ans_remark, $temp);
            $notice_id = pdo_insertid();
        
            if (empty($notice_id)) {
                $data ['status'] = 1;
                $data ['result'] = true;
                $data ['msg'] ='批阅失败！' ;
            } else {
                $data ['status'] = 1;
                $data ['result'] = true;
           
                $data ['msg'] ='批阅成功，请勿重复操作' ;
            }
        }
        die(json_encode($data));
    }
}
if ($operation == 'GetHolidayData') {
    if ($_GPC['sDate']) {
        $thistime = strtotime($_GPC['sDate']);
        $start_time = strtotime(date('Y-m-01', $thistime));
        $nowstart = strtotime(date('Y-m-01'));
        $j = date('t', $thistime);
        $nowyear = date('Y', $thistime);
    }
    $array = array();
    for ($i=0;$i<$j;$i++) {
        $array[] = array(
                'date' => date('Y-n-j', $start_time+$i*86400),//每隔一天赋值给数组
                'day' => $i +1
        );
    }
    $result = array();
    $dateset =  pdo_fetch("SELECT datesetid FROM " . tablename($this->table_classify) . " WHERE schoolid = '{$_GPC['schoolid']}' and sid = '{$_GPC['bj_id']}' ");
    if (!empty($dateset)) {
        $checkdateset      =  pdo_fetch("SELECT * FROM " . tablename($this->table_checkdateset) . " WHERE schoolid = {$_GPC['schoolid']} and  id = '{$dateset['datesetid']}'");
        $checkdateset_holi =  pdo_fetch("SELECT * FROM " . tablename($this->table_checkdatedetail) . " WHERE schoolid = {$_GPC['schoolid']} and  checkdatesetid = '{$checkdateset['id']}' and year = '{$nowyear}' ");
        foreach ($array as $row) {
            //特殊日子
            $checktime  =  pdo_fetch("SELECT * FROM " . tablename($this->table_checktimeset) . " WHERE  schoolid = {$_GPC['schoolid']} and  checkdatesetid = '{$dateset['datesetid']}' and date = '{$row['date']}' ");
            //是特殊日子
            if (!empty($checktime)) {
                if ($checktime['type'] == 6) {
                    $result[] = $row['day'];
                }
                //不是特殊日子
            } else {
                //寒暑假
                if (($row['date'] >= $checkdateset_holi['win_start'] && $row['date'] <=$checkdateset_holi['win_end']) || ($row['date'] >= $checkdateset_holi['sum_start'] && $row['date'] <=$checkdateset_holi['sum_end'])) {
                    $result[] = $row['day'];
                //不是寒暑假
                } else {
                    //周六
                    if (date('w', strtotime($row['date'])) == 6) {
                        if ($checkdateset['saturday'] != 1) {
                            $result[] = $row['day'];
                        }
                        //周日
                    } elseif (date('w', strtotime($row['date'])) == 0) {
                        if ($checkdateset['sunday'] != 1) {
                            $result[] = $row['day'];
                        }
                    }
                }
            }
        }
    } else {
        foreach ($array as $row) {
            if (date('w', strtotime($row['date'])) == 6 || date('w', strtotime($row['date'])) == 0) {
                $result[] = $row['day'];
            }
        }
    }
    die(json_encode($result));
}
if ($operation == 'snotice_qbyd') { //设置通知作业全部已读
    $recode = pdo_fetchall("SELECT id,readtime,type FROM " . tablename($this->table_record) . " where schoolid = '{$_GPC['schoolid']}' And sid = '{$_GPC['sid']}' And userid = '{$_GPC['userid']}' And type = '{$_GPC['noticetype']}' And readtime < 1");
    if ($recode) {
        $nowtime = time();
        foreach ($recode as $row) {
            pdo_update($this->table_record, array('readtime' => $nowtime), array('id' => $row['id']));
        }
        $result ['result'] = true;
        $result ['msg'] ='设置成功！' ;
    } else {
        $result ['result'] = false;
        $result ['msg'] ='你没有未读消息哦' ;
    }
    die(json_encode($result));
}

if ($operation == 'uploadsence') {
    load()->func('communication');
    load()->func('file');
    if ($_GPC ['photoUrls']) {
        $picurl = array();
        $photo = $_GPC ['photoUrls'];
        for ($i = 0; $i <= 9 ; $i++) {
            if (!empty($photo[$i])) {
                $picurl[$i] = $photo[$i];
            }
        }
        $picstr = array('p1' => $picurl[0],'p2' => $picurl[1],'p3' => $picurl[2],'p4' => $picurl[3],'p5' => $picurl[4],'p6' => $picurl[5],'p7' => $picurl[6],'p8' => $picurl[7],'p9' => $picurl[8],);
    }
    $video = '';
    if (!empty($_GPC['videoMediaId'])) {
        $msgtype = 3;//视频
        mload()->model('ali');
        $aliyun = GetAliApp($_W['uniacid'], $_GPC['schoolid']);
        if ($aliyun['result']) {
            $appid = $aliyun['alivodappid'];
            $key = $aliyun['alivodkey'];
            do {
                $GetAliVideoUrl = GetAliVideoUrl($appid, $key, trim($_GPC['videoMediaId']));
            } while (empty($GetAliVideoUrl['PlayURL']));
            do {
                $GetAliVideoCover = GetAliVideoCover($appid, $key, trim($_GPC['videoMediaId']));
            } while (empty($GetAliVideoCover['CoverURL']));
            $video = $GetAliVideoUrl['PlayURL'];
            $videoimg = $GetAliVideoCover['CoverURL'];
        }
    }
    $audios = $_GPC['audioServerid'];
    $audio = $audios[0];
    if ($audio) {
        $versionfile = IA_ROOT . '/addons/fm_jiaoyu/inc/func/auth2.php';
        require $versionfile;
        $mp3name = str_replace('images/bjq/vioce/', '', $audio);
        $mp3 = str_replace('.mp3', '', $mp3name);
        delvioce($mp3, FM_JIAOYU_HOST);
    }
    $setting = pdo_fetch("SELECT * FROM " . tablename($this->table_set) . " WHERE :weid = weid", array(':weid' => $_GPC['weid']));
    if (! $_GPC ['schoolid'] || ! $_GPC ['weid']) {
        die(json_encode(array(
                'result' => false,
                'msg' => '非法请求！'
                   )));
    } else {
        if (empty($_GPC['openid'])) {
            die(json_encode(array(
                'result' => false,
                'msg' => '非法请求,请刷新页面！'
                   )));
        } else {
            $schoolid = $_GPC['schoolid'];
            $weid = $_GPC['weid'];
            $content = $_GPC['content'];
            $tid = $_GPC['tid'];
            $senceid = $_GPC['senceid'];
            $audios = $_GPC ['audioServerid'];
            $audio = $audios[0];
            $audiotimes = $_GPC['audioTime'];
            $audiotime = $audiotimes[0];
            $temp = array(
                        'weid' =>  $weid,
                        'schoolid' => $schoolid,
                        'tid' => $tid,
                        'senceid' => $senceid,
                        'up_video' => $video,
                        'ali_vod_id'=>trim($_GPC['videoMediaId']),
                        'up_audio' => $audio,
                        'up_word' => $content,
                        'createtime' => time(),

                    );
            $temp['up_imgs'] = json_encode($picstr);
            pdo_insert($this->table_teasencefiles, $temp);

            $result['result'] = true;
            $result['msg'] = "上传成功！";
        }
        die(json_encode($result));
    }
}

if ($operation == 'GetPlInfo') {
    $school = pdo_fetch("SELECT spic,tpic FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $_GPC['weid'], ':id' => $_GPC['schoolid']));
    $pl = pdo_fetch("SELECT * FROM " . tablename($this->table_camerapl) . " WHERE weid = '{$_GPC['weid']}' AND schoolid = '{$_GPC['schoolid']}' AND carmeraid = '{$_GPC['videoid']}' AND type = 2 AND id > '{$_GPC['maxid']}' ORDER BY id ");
    if ($pl) {
        if (!empty($pl['userid'])) {
            $user = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where id = :id ", array(':id' => $pl['userid']));
            if (!empty($user)) {
                if ($user['pard'] == 0) {
                    $teacher = pdo_fetch("SELECT tname,thumb FROM " . tablename($this->table_teachers) . " where id = :id ", array(':id' => $user['tid']));
                    $pl['name'] = $teacher['tname']."老师";
                    $pl['icon'] = !empty($teacher['thumb']) ? tomedia($teacher['thumb']) : tomedia($school['tpic']);
                } else {
                    $student = pdo_fetch("SELECT s_name,icon FROM " . tablename($this->table_students) . " where id = :id ", array(':id' => $user['sid']));
                    if ($user['pard'] == 5) {
                        $pl['name'] = $student['s_name'];
                        $pl['icon'] = !empty($student['icon']) ? tomedia($student['icon']) : tomedia($school['spic']);
                    } else {
                        $fansinfo = GetWeFans($weid, $user['openid']);
                        $pl['icon'] = $fansinfo['avatar'];
                        if ($user['pard'] == 2) {
                            $pl['name'] = $student['s_name']."妈妈";
                        }
                        if ($user['pard'] == 3) {
                            $pl['name'] = $student['s_name']."爸爸";
                        }
                        if ($user['pard'] == 4) {
                            $pl['name'] = $student['s_name']."家长";
                        }
                    }
                }
                $pl['time'] = sub_day($pl['createtime']);
                $result ['result'] = true;
                $result ['data'] = $pl;
            } else {
                $result ['result'] = false;
                pdo_delete(GetTableName('camerapl', false), array('id' => $pl['id']));
            }
        } elseif (!empty($pl['openid'])) {
            $fansinfo = GetWeFans($weid, $pl['openid']);
            $pl['name'] = $fansinfo['nickname']."(游客)";
            $pl['icon'] = $fansinfo['avatar'];
            $pl['time'] = sub_day($pl['createtime']);
            $result ['result'] = true;
            $result ['data'] = $pl;
        } else {
            $result ['result'] = false;
            pdo_delete(GetTableName('camerapl', false), array('id' => $pl['id']));
        }
    } else {
        $result ['result'] = false;
    }
    die(json_encode($result));
}

if ($operation == 'GetYqDkStuData') {
    if ($_GPC['sDate']) {
        $thistime = strtotime($_GPC['sDate']);
        $start_time = strtotime(date('Y-m-01', $thistime));
        $nowstart = strtotime(date('Y-m-01'));
        if ($start_time == $nowstart) {
            $j = date('j');
        } else {
            $j = date('t', $thistime);
        }
    }
    $array = array();
    for ($i=0;$i<$j;$i++) {
        $array[] = array(
                'date' => date('Y-m-d', $start_time+$i*86400),//每隔一天赋值给数组
                'day' => $i +1
        );
    }
    $result['lstAttendInfoOfTeacher'] = "";
    $days = 0;
    $nowtime = date('Y-m-d');
    foreach ($array as $key => $row) {
        $date = explode('-', $row['date']);
        $starttime = mktime(0, 0, 0, $date[1], $date[2], $date[0]);
        $endtime = $starttime + 86399;
        $condition = " AND sid = '{$_GPC['sid']}' AND createtime > '{$starttime}' AND createtime < '{$endtime}'";
        $yqdk = pdo_fetch("SELECT createtime FROM " . GetTableName('yqdk') . " where schoolid = '{$_GPC['schoolid']}'  $condition");
        if (!empty($yqdk)) {
            $list[$key]['Type'] = "wx";
            $days++;
        } else {
            $list[$key]['Type'] = "skip";
        }
        $list[$key]['Date'] = $row['day'];
        $list[$key]['sid'] = $_GPC['sid'];
        $list[$key]['end'] = date('Y-m-d H:i:s', $endtime);
        $list[$key]['start'] = date('Y-m-d H:i:s', $starttime);
        $list[$key]['line'] = $yqdk;
    }
    $result['lstAttendInfo'] = $list;
    $student = pdo_fetch("SELECT s_name FROM " . tablename($this->table_students) . " where id = :id AND schoolid = :schoolid ", array(':id' => $_GPC['sid'], ':schoolid' => $_GPC['schoolid']));
    $result['s_name'] = $student['s_name'];
    $result['AttendanceCount'] = $days;
    die(json_encode($result));
}

if ($operation == 'writePsychology') {
    $data = explode('|', $_GPC ['json']);
    if (empty($_GPC['openid'])) {
        die(json_encode(array(
        'result' => false,
        'msg' => '非法请求！'
            )));
    } else {
        $contenttype = trim($_GPC['contenttype']);
        $schoolid = $_GPC['schoolid'];
        $userid = $_GPC['userid'];
        $touserid = $_GPC['touserid'];
        $openid = $_GPC['openid'];
        $toopenid = $_GPC['toopenid'];
        $weid = $_GPC['weid'];
        $GLid = $_GPC['GLid'];
        $sendtype = $_GPC['sendtype'];
        if ($contenttype == 'media') {
            $audio = $_GPC['audioServerid'];
            $audioTime = $_GPC['audioTime'];
            if ($audio) {
                $versionfile = IA_ROOT . '/addons/fm_jiaoyu/inc/func/auth2.php';
                require $versionfile;
                $mp3name = str_replace('images/bjq/vioce/', '', $audio);
                $mp3 = str_replace('.mp3', '', $mp3name);
                delvioce($mp3, FM_JIAOYU_HOST);
            }
            $data = array(
                'weid' =>  $weid,
                'schoolid' => $schoolid,
                'userid' => $userid,
                'touserid' => $touserid,
                'openid' => $openid,
                'toopenid' => $toopenid,
                'createtime' => time(),
                'leaveid' => $GLid,
                'sendtype' => $sendtype,
            );
            $audios = array(
                'audio' =>  $audio,
                'audioTime' => $audioTime,
            );
            $data['audio'] = iserializer($audios);
            $urls = $_W['attachurl'];
            $datas ['mediafile'] = $urls.$audio[0];
            $datas ['mediatime'] = $audioTime;
        } elseif ($contenttype == 'img') {
            load()->func('communication');
            load()->func('file');
            $token2 = $this->getAccessToken2();
            $photoUrl = $_GPC ['imgServerid'];
            if (!empty($photoUrl)) {
                $url = 'https://file.api.weixin.qq.com/cgi-bin/media/get?access_token='.$token2.'&media_id='.$photoUrl;
                $pic_data = ihttp_request($url);
                $path = "images/fm_jiaoyu/";
                $picurl = $path.random(30) .".jpg";
                file_write($picurl, $pic_data['content']);
                //$files = IA_ROOT . "/attachment/".$picurl;
                //cut($files);
                if (!empty($_W['setting']['remote']['type'])) { //
                    $remotestatus = file_remote_upload($picurl); //
                    if (is_error($remotestatus)) {
                        $datas ['result'] = false;
                        $datas ['msg'] = '发送图片失败！';
                        die(json_encode($datas));
                    }
                }
            }
            $data = array(
                'weid' =>  $weid,
                'schoolid' => $schoolid,
                'userid' => $userid,
                'touserid' => $touserid,
                'openid' => $openid,
                'toopenid' => $toopenid,
                'createtime' => time(),
                'leaveid' => $GLid,
                'sendtype' => $sendtype,
            );
            $data ['picurl'] = $picurl;
            $datas ['mediafile'] = tomedia($picurl);
        } else {
            $data = array(
                'weid' =>  $weid,
                'schoolid' => $schoolid,
                'userid' => $userid,
                'touserid' => $touserid,
                'openid' => $openid,
                'toopenid' => $toopenid,
                'content' => $_GPC['content'],
                'createtime' => time(),
                'leaveid' => $GLid,
                'sendtype' => $sendtype,
            );
        }
        pdo_insert(GetTableName('psychology', false), $data);
        $leaveid = pdo_insertid();

        if ($GLid == 0) {
            $GLid = $leaveid;
            pdo_update(GetTableName('psychology', false), array('leaveid'=>$GLid), array('id'=>$leaveid));
        }
        $this->sendMobilePsychology($leaveid, $schoolid, $weid);
        $datas ['result'] = true;
        $datas ['GLid'] = $GLid;
        $datas ['msg'] = '成功发送留言信息，请勿重复发送！';
        die(json_encode($datas));
    }
}



if ($operation == 'pxfabu') {
    load()->func('communication');
    load()->func('file');
    $data = explode('|', $_GPC ['json']);
    if ($_GPC ['photoUrls']) {
        $picurl = array();
        $photo = $_GPC ['photoUrls'];
        for ($i = 0; $i <= 9 ; $i++) {
            if (!empty($photo[$i])) {
                $picurl[$i] = $photo[$i];
            }
        }
        $picstr = array('p1' => $picurl[0],'p2' => $picurl[1],'p3' => $picurl[2],'p4' => $picurl[3],'p5' => $picurl[4],'p6' => $picurl[5],'p7' => $picurl[6],'p8' => $picurl[7],'p9' => $picurl[8],);
    }
    $video = '';
    if (!empty($_GPC['videoMediaId'])) {
        $msgtype = 3;//视频
        mload()->model('ali');
        $aliyun = GetAliApp($_W['uniacid'], $_GPC['schoolid']);
        if ($aliyun['result']) {
            $appid = $aliyun['alivodappid'];
            $key = $aliyun['alivodkey'];
            do {
                $GetAliVideoUrl = GetAliVideoUrl($appid, $key, trim($_GPC['videoMediaId']));
            } while (empty($GetAliVideoUrl['PlayURL']));
            do {
                $GetAliVideoCover = GetAliVideoCover($appid, $key, trim($_GPC['videoMediaId']));
            } while (empty($GetAliVideoCover['CoverURL']));
            $video = $GetAliVideoUrl['PlayURL'];
            $videoimg = $GetAliVideoCover['CoverURL'];
        }
    }
    $audios = $_GPC ['audioServerid'];
    $audio = $audios[0];
    if ($audio) {
        $versionfile = IA_ROOT . '/addons/fm_jiaoyu/inc/func/auth2.php';
        require $versionfile;
        $mp3name = str_replace('images/bjq/vioce/', '', $audio);
        $mp3 = str_replace('.mp3', '', $mp3name);
        delvioce($mp3, FM_JIAOYU_HOST);
    }
    if (! $_GPC ['schoolid'] || ! $_GPC ['weid']) {
        die(json_encode(array(
                'result' => false,
                'msg' => '非法请求！'
                   )));
    } else {
        if (empty($_GPC['openid'])) {
            die(json_encode(array(
                'result' => false,
                'msg' => '非法请求,请刷新页面！'
                   )));
        } else {
            $schoolid = $_GPC['schoolid'];
            $schooltype = $_GPC['schooltype'];
            $title = $_GPC['title'];
            $weid = $_GPC['weid'];
            $content = $_GPC['content'];
            $tid = $_GPC['tid'];
            $uid = $_GPC['uid'];
            $openid = $_GPC['openid'];
            $usertype = $_GPC['type'];
            $userdatas = $_GPC['datas'];
            $audios = $_GPC ['audioServerid'];
            $audio = $audios[0];
            $audiotimes = $_GPC['audioTime'];
            $audiotime = $audiotimes[0];
            $tname = $_GPC['tname'];
            $shername = $tname;
            $is_private = trim($_GPC['is_private']);
            $is_pl = trim($_GPC['is_pl']);
            $is_see = trim($_GPC['is_see']);
            if ($is_pl == 'Y') {
                $comment = 2;//允许评论
                if ($is_see == 'Y') {
                    $comment = 3;//允许评论且仅作者可见
                }
            } else {
                $comment = 1;//禁止评论
            }
            if ($usertype == 'send_class') {
                $datass = str_replace('&quot;', '"', $_GPC['datas']);
                $userdatas = json_decode($datass, true);
            }
            if ($usertype == 'student') {
                $datass = str_replace('&quot;', '"', $_GPC['datas']);
                $userdatas = json_decode($datass, true);
                $stuarr = array();
            }

            $notice_idarr = array();
            $bjarr = array();
            $totals = 0;
            foreach ($userdatas as $key => $row) {
                $jsondatas = json_encode(array(
                    "{$key}"=> "{$row}"
                ));
                if ($row != '') {
                    if ($usertype == 'send_class') {
                        $bj_id = trim($row, ',');
                        $total = pdo_fetchcolumn("SELECT COUNT( distinct sid ) FROM ". GetTableName('coursebuy') ." where schoolid = '{$schoolid}' and FIND_IN_SET(bjid,'{$bj_id}') AND kcid = '{$key}' ");
                        $totals = $totals + $total;
                    }
                    if ($usertype == 'student') {
                        $bj_id = $key;
                        $vals = explode(',', $row);
                        foreach ($vals as $v) {
                            if ($v != '') {
                                $stuarr[] = $v;
                                $totals++;
                            }
                        }
                    }
                    if ($is_private == 'Y') {
                        $bjqdata = array(
                            'weid' =>  $weid,
                            'schoolid' => $schoolid,
                            'uid' => $uid,
                            'shername' => $shername,
                            'audio' => $audio,
                            'audiotime' => $audiotime,
                            'content' => $content,
                            'video' => $video,
                            'openid'=>$openid,
                            'isopen'=>0,
                            'is_private'=>'N',
                            'createtime' => time(),
                            'msgtype'=>7,
                            'type'=>0,
                        );
                        if ($schooltype) {
                            $bjqdata['kc_id']	= $bj_id;
                        } else {
                            $bjqdata['bj_id1']	= $bj_id;
                        }
                        pdo_insert($this->table_bjq, $bjqdata);
                        $bjq_id = pdo_insertid();
                        $data1 = array(
                            'sherid'=>$bjq_id,
                        );
                        pdo_update($this->table_bjq, $data1, array('id' => $bjq_id));
                        if ($_GPC ['photoUrls']) {
                            $photoUrl = $_GPC ['photoUrls'];
                            $order = 1;
                            foreach ($photoUrl as $key => $v) {
                                if (!empty($v)) {
                                    $data = array(
                                        'weid' =>  $weid,
                                        'schoolid' => $schoolid,
                                        'uid' => $uid,
                                        'picurl' => $v,
                                        'order'=>$order,
                                        'sherid'=>$bjq_id,
                                        'createtime' => time(),
                                       );
                                    if ($schooltype) {
                                        $data['kc_id']	= $bj_id;
                                    } else {
                                        $data['bj_id1']	= $bj_id;
                                    }
                                    pdo_insert($this->table_media, $data);
                                }
                                $order++;
                            }
                        }
                    }
                    $temp = array(
                        'weid' =>  $weid,
                        'schoolid' => $schoolid,
                        'tid' => $tid,
                        'tname' => $tname,
                        'title' => $title,
                        'video' => $video,
                        'videopic' => $videoimg,
                        'audio' => $audio,
                        'ali_vod_id' => trim($_GPC['videoMediaId']),
                        'audiotime' => $audiotime,
                        'content' => $content,
                        'comment' => $comment,
                        'userdatas' => $_GPC['datas'],
                        'usertype' => $usertype,
                        'createtime' => time(),
                        'type'=>1
                    );
                    $temp['userdatas'] = $jsondatas;
                    $temp['kc_id']	= $key;
                    if ($usertype == 'student') {
                        $bjidList = pdo_fetchAll("SELECT DISTINCT(bjid) FROM " . GetTableName('coursebuy') . " where schoolid = '{$schoolid}' AND FIND_IN_SET(sid,'{$row}')");
                        $bjidarr = arrayToString($bjidList);
                        $temp['bjidarr'] = $bjidarr;
                    }
                    if ($usertype == 'send_class') {
                        $temp['bj_id']	= $bj_id;
                        $temp['bjidarr'] = trim($row, ',');
                    }
                    $temp['picarr'] = iserializer($picstr);
                    pdo_insert($this->table_notice, $temp);
                    $notice_id = pdo_insertid();
                    $url = $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&m=fm_jiaoyu&do=sendMsg';
                    $data = array(
                        'notice_id' => $notice_id,
                        'schoolid' => $schoolid,
                        'weid' => $weid,
                        'tname' => $tname,
                        'op' => 'sendMobileNewPxBjtz',
                    );
                    timeOutPost($url, $data);
                }
            }
            $customData = array(
                'touser' => "{$_GPC['openid']}",
                'msgtype'=> 'text',
                'text'=> array(
                    'content' => "尊敬的-{$tname}老师，您发布的班级通知({$title})已全部发送成功！"
                )
            );
            $this->sendCustomNotice($customData);
            $result ['status'] = 1;
            $result ['result'] = true;
            $result ['msg'] = '正在执行发送任务！';
            $actop = 'bjtz';
            $userid = $_GPC['userid'];
            $point = PointAct($weid, $schoolid, $userid, $actop);
            $point1 = PointMission($weid, $schoolid, $userid, $actop);
            if (!empty($point)) {
                $result ['msg'] = '群发成功，请勿重复操作!积分+'.$point;
            }
        }
        die(json_encode($result));
    }
}

if ($operation == 'pxnotpro') {
    $schoolid = $_GPC['schoolid'];
    $weid = $_GPC['weid'];
    $tname = $_GPC['tname']; //老师名称
    $schooltype = $_GPC['schooltype'];
    $usertype = $_GPC['usertype']; //发送类型
    $bjarr = $_GPC['bjarr']; //班级数组
    $userdatas = $_GPC['userdatas']; //班级数组
    $stuarr = $_GPC['stuarr'];
    $noticeidarr = $_GPC['noticeidarr'];
    $total = $_GPC['total'];
    $pindex = max(1, intval($_GPC['page']));
    $psize = 2;
    $tp = ceil($total/$psize);
    if ($pindex <= $tp) {
        session_start();
        if ($usertype == 'send_class') {
            if ($_SESSION['arr'] || $_SESSION['arr'] != "") {
                $arr = $_SESSION['arr'];
            } else {
                mload()->model('stu');
                $arr = PxStuInfoByclassArr($userdatas, $schoolid);
                $_SESSION['arr'] = $arr;
            }

            $this->sendMobilePxBjtzToUserArr($schoolid, $schooltype, $weid, $tname, $arr, $noticeidarr, 'tostu', $pindex, $psize);
        }
        if ($usertype == 'student') {
            if ($_SESSION['arr'] || $_SESSION['arr'] != "") {
                $arr = $_SESSION['arr'];
            } else {
                $arr = $stuarr;
                $_SESSION['arr'] = $arr;
            }
            $this->sendMobilePxBjtzToUserArr($schoolid, $schooltype, $weid, $tname, $arr, $noticeidarr, 'tostu', $pindex, $psize);
        }
        $mq = round(($pindex / $tp) * 100);
        $msg = '已发送' . $mq . ' %';
        $page = $pindex + 1;
        $data ['pro'] = $msg;
        $data ['arr'] = $arr;
        $data ['bjarr'] = $bjarr;
        $data ['stuarr'] = $stuarr;
        $data ['noticeidarr'] = $noticeidarr;
        $data ['page'] = $page;
        $data ['count'] = count($arr);
        $data ['status'] = 1;
    } else {
        $data ['status'] = 2;
        $_SESSION['arr'] = "";
        $arr = $_SESSION['arr'];
        $data ['userdatas'] = $arr;
    }
    die(json_encode($data));
}

if ($operation == 'zfabupx') {
    load()->func('communication');
    load()->func('file');
    $is_txt = $_GPC['is_txt'];
    $is_img = $_GPC['is_img'];
    $is_audio = $_GPC['is_audio'];
    $is_video = $_GPC['is_video'];
    if ($_GPC ['photoUrls']) {
        $picurl = array();
        $photo = $_GPC ['photoUrls'];
        for ($i = 0; $i <= 9 ; $i++) {
            if (!empty($photo[$i])) {
                $picurl[$i] = $photo[$i];
            }
        }
        $picstr = array('p1' => $picurl[0],'p2' => $picurl[1],'p3' => $picurl[2],'p4' => $picurl[3],'p5' => $picurl[4],'p6' => $picurl[5],'p7' => $picurl[6],'p8' => $picurl[7],'p9' => $picurl[8],);
    }
    $video = '';
    if (!empty($_GPC['videoMediaId'])) {
        $msgtype = 3;//视频
        mload()->model('ali');
        $aliyun = GetAliApp($_W['uniacid'], $_GPC['schoolid']);
        if ($aliyun['result']) {
            $appid = $aliyun['alivodappid'];
            $key = $aliyun['alivodkey'];
            do {
                $GetAliVideoUrl = GetAliVideoUrl($appid, $key, trim($_GPC['videoMediaId']));
            } while (empty($GetAliVideoUrl['PlayURL']));
            do {
                $GetAliVideoCover = GetAliVideoCover($appid, $key, trim($_GPC['videoMediaId']));
            } while (empty($GetAliVideoCover['CoverURL']));
            $video = $GetAliVideoUrl['PlayURL'];
            $videoimg = $GetAliVideoCover['CoverURL'];
        }
    }
    $audios = $_GPC ['audioServerid'];
    
    $audio = $audios[0];
    if ($audio) {
        $versionfile = IA_ROOT . '/addons/fm_jiaoyu/inc/func/auth2.php';
        require $versionfile;
        $mp3name = str_replace('images/bjq/vioce/', '', $audio);
        $mp3 = str_replace('.mp3', '', $mp3name);
        delvioce($mp3, FM_JIAOYU_HOST);
    }
    $setting = pdo_fetch("SELECT * FROM " . tablename($this->table_set) . " WHERE :weid = weid", array(':weid' => $_GPC['weid']));
    if (! $_GPC ['schoolid'] || ! $_GPC ['weid']) {
        die(json_encode(array(
                'result' => false,
                'msg' => '非法请求！'
                   )));
    } else {
        if (empty($_GPC['openid'])) {
            die(json_encode(array(
                'result' => false,
                'msg' => '非法请求,请刷新页面！'
                   )));
        } else {
            $schoolid = $_GPC['schoolid'];
            $schooltype = $_GPC['schooltype'];
            $title = $_GPC['title'];
            $weid = $_GPC['weid'];
            $content = $_GPC['content'];
            $tid = $_GPC['tid'];
            $uid = $_GPC['uid'];
            $openid = $_GPC['openid'];
            $km_id = $_GPC['km_id'];
            $audios = $_GPC ['audioServerid'];
            $audio = $audios[0];
            $audiotimes = $_GPC['audioTime'];
            $audiotime = $audiotimes[0];
            $tname = $_GPC['tname'];
            $shername = $tname.'老师';
            $usertype = $_GPC['type'];
            $userdatas = $_GPC['datas'];
            $is_personal = trim($_GPC['is_private']);
            $is_pl = trim($_GPC['is_pl']);
            $is_see = trim($_GPC['is_see']);
            if ($is_pl == 'Y') {
                $comment = 2;//允许评论
                if ($is_see == 'Y') {
                    $comment = 3;//允许评论且仅作者可见
                }
            } else {
                $comment = 1;//禁止评论
            }
            $temp_ans = array(
                'is_txt' => $is_txt,
                'is_img' => $is_img,
                'is_audio' => $is_audio,
                'is_video' => $is_video
            );
            $ansType = iserializer($temp_ans);
            if ($usertype == 'send_class') {
                $datass = str_replace('&quot;', '"', $_GPC['datas']);
                $userdatas = json_decode($datass, true);
            }
            if ($usertype == 'student') {
                $datass = str_replace('&quot;', '"', $_GPC['datas']);
                $userdatas = json_decode($datass, true);
                $stuarr = array();
            }
            $notice_idarr = array();
            $bjarr = array();
            $totals = 0;
            foreach ($userdatas as $key => $row) {
                $bjidarr = '';
                if ($row != '') {
                    if ($usertype == 'send_class') {
                        $bjidarr = trim($row, ',');
                        $total = pdo_fetchcolumn("SELECT COUNT( distinct sid ) FROM ". GetTableName('coursebuy') ." where schoolid = '{$schoolid}' and FIND_IN_SET(bjid,'{$bjidarr}') AND kcid = '{$key}' ");
                        $totals = $totals + $total;
                    }
                    if ($usertype == 'student') {
                        $vals = explode(',', $row);
                        foreach ($vals as $v) {
                            if ($v != '') {
                                $bjid = pdo_fetch("SELECT bjid FROM ". GetTableName('coursebuy') ." where schoolid = '{$schoolid}' and sid = '{$v}' AND kcid = '{$key}' ")['bjid'];
                                $bjidarr .= $bjid.',';
                                $stuarr[] = $v;
                                $totals++;
                            }
                        }
                    }
                    $bjidarr = trim($bjidarr, ',');
                    $temp = array(
                        'weid' =>  $weid,
                        'schoolid' => $schoolid,
                        'tid' => $tid,
                        'tname' => $tname,
                        'title' => $title,
                        'video' => $video,
                        'videopic' => $videoimg,
                        'audio' => $audio,
                        'audiotime' => $audiotime,
                        'userdatas' => $userdatas,
                        'usertype' => $usertype,
                        'content' => $content,
                        'comment' => $comment,
                        'createtime' => time(),
                        'type'=>3,
                        'km_id'=>$km_id,
                    );
                    if ($usertype == 'send_class') {
                        $datass = str_replace('&quot;', '"', $_GPC['datas']);
                        $temp['userdatas']	= $datass;
                    }
                    if ($usertype == 'student') {
                        $datass = str_replace('&quot;', '"', $_GPC['datas']);
                        $temp['userdatas']	= $datass;
                    }
                    
                    if ($is_personal == 'Y') {
                        $temp['anstype'] = $ansType;
                    }
                    $temp['kc_id']	= $key;
                    $temp['bjidarr'] = $bjidarr;
                    $temp['picarr'] = iserializer($picstr);
                    pdo_insert($this->table_notice, $temp);
                    $notice_id = pdo_insertid();
                    $notice_idarr[] = $notice_id;
                    $bjarr[] = $bjidarr;

                    $url = $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&m=fm_jiaoyu&do=sendMsg';
                    $sendData = array(
                        'notice_id' => $notice_id,
                        'schoolid' => $schoolid,
                        'weid' => $weid,
                        'tname' => $tname,
                        'op' => 'sendMobilePxZuoye',
                    );
                    timeOutPost($url, $sendData);
                }
            }
            $result ['status'] = 1;
            $result ['result'] = true;
            $result ['msg'] = '正在执行发送任务！';
            $customData = array(
                'touser' => "{$_GPC['openid']}",
                'msgtype'=> 'text',
                'text'=> array(
                    'content' => "尊敬的-{$tname}老师，您发布的作业({$title})已全部发送成功！"
                )
            );
            $this->sendCustomNotice($customData);
            $actop = 'fbzy';
            $userid = $_GPC['userid'];
            $point = PointAct($weid, $schoolid, $userid, $actop);
            $point1 = PointMission($weid, $schoolid, $userid, $actop);
            if (!empty($point)) {
                $result ['msg'] = '群发成功，请勿重复操作!积分+'.$point;
            }
        }
        die(json_encode($result));
    }
}
if ($operation == 'znotpropx') {
    $schoolid = $_GPC['schoolid'];
    $weid = $_GPC['weid'];
    $tname = $_GPC['tname'];
    $schooltype = $_GPC['schooltype'];
    $usertype = $_GPC['usertype'];
    $bjarr = $_GPC['bjarr'];
    $userdatas = $_GPC['userdatas'];
    $stuarr = $_GPC['stuarr'];
    $noticeidarr = $_GPC['noticeidarr'];
    $total = $_GPC['total'];
    $pindex = max(1, intval($_GPC['page']));
    $psize = 2;
    $tp = ceil($total/$psize);
    //echo '第' . $pindex . '次,总共'.$tp.'次';
    if ($pindex <= $tp) {
        session_start();
        if ($usertype == 'send_class') {
            if ($_SESSION['arr'] || $_SESSION['arr'] != "") {
                $arr = $_SESSION['arr'];
            } else {
                mload()->model('stu');
                $arr = PxStuInfoByclassArr($userdatas, $schoolid);
                $_SESSION['arr'] = $arr;
            }
            $this->sendMobilePxZytzToUserArr($schoolid, $schooltype, $weid, $tname, $arr, $noticeidarr, 'tostu', $pindex, $psize);
        }
        if ($usertype == 'student') {
            if ($_SESSION['arr'] || $_SESSION['arr'] != "") {
                $arr = $_SESSION['arr'];
            } else {
                $arr = $stuarr;
                $_SESSION['arr'] = $arr;
            }
            $this->sendMobilePxZytzToUserArr($schoolid, $schooltype, $weid, $tname, $arr, $noticeidarr, 'tostu', $pindex, $psize);
        }
        $mq = round(($pindex / $tp) * 100);
        $msg = '已发送' . $mq . ' %';
        $page = $pindex + 1;
        $data ['pro'] = $msg;
        $data ['arr'] = $arr;
        $data ['bjarr'] = $bjarr;
        $data ['stuarr'] = $stuarr;
        $data ['noticeidarr'] = $noticeidarr;
        $data ['page'] = $page;
        $data ['count'] = count($arr);
        $data ['status'] = 1;
    } else {
        $data ['status'] = 2;
        $_SESSION['arr'] = "";
        $arr = $_SESSION['arr'];
        $data ['userdatas'] = $arr;
    }
    die(json_encode($data));
}
