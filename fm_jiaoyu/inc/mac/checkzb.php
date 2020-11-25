<?php
/**
 * By 高贵血迹
 */

global $_GPC, $_W;

$operation = in_array ( $_GPC ['op'], array ('default', 'login','class', 'users', 'check', 'gps', 'banner', 'video','getleave','getroomlist','online','report','getvisitors','reportvisitors','addvisitors','allsy') ) ? $_GPC ['op'] : 'default';
$weid = $_GPC['i'];
$schoolid = $_GPC['schoolid'];
$macid = $_GPC['macid'];
$ckmac = pdo_fetch("SELECT * FROM " . tablename($this->table_checkmac) . " WHERE macid = '{$macid}' And schoolid = '{$schoolid}' ");
$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}' ");
$xk_type = pdo_fetch("SELECT * FROM " . GetTableName('schoolset') . " WHERE schoolid = '{$schoolid}' ");
$school['xk_type'] =$xk_type['xk_type'];
if ($operation == 'default') {
    echo("错误，未知操作");
    exit;
}
if(empty($school)){
    echo("找不到本校");
    exit;
}
if(empty($ckmac)){
    echo("没找到设备");
    exit;
}
if($school['is_recordmac'] == 2){
    echo("本校无权使用设备");
    exit;
}
if ($ckmac['is_on'] ==2){
    echo("本设备已关闭");
    exit;
}
if (!empty($_W['setting']['remote']['type'])) {
    $urls = $_W['attachurl'];
} else {
    $urls = $_W['siteroot'].'attachment/';
}
if ($operation == 'login') {
    if(!empty($ckmac)){
        $result['data']['schoolinfo'] = array(
            'name' => $school['title'],
            'schoolid' => $school['id'],
            'logo' => $urls.$school['logo'],
            'tel' => $school['tel']
        );
        if($ckmac['twmac'] == -1){
            $result['data']['tempid'] = 1;
        }else{
            $result['data']['tempid'] = $ckmac['twmac'];
        }

        $result['data']['apid'] = $ckmac['apid'];
        $result['data']['onlineurl']       = urlencode($_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkzb&op=online&m=fm_jiaoyu');
        $result['data']['reporturl']       = urlencode($_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkzb&op=report&m=fm_jiaoyu');
        $result['data']['bannerurl']       = urlencode($_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkzb&op=banner&m=fm_jiaoyu');
        $result['data']['videourl']        = urlencode($_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkzb&op=video&m=fm_jiaoyu');
        $result['data']['classurl']        = urlencode($_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkzb&op=class&m=fm_jiaoyu');
        $result['data']['usersurl']        = urlencode($_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkzb&op=users&m=fm_jiaoyu');
        $result['data']['checkurl']        = urlencode($_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkzb&op=check&m=fm_jiaoyu');
        $result['data']['getleaveurl']     = urlencode($_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkzb&op=getleave&m=fm_jiaoyu');
        $result['data']['getroomlisturl']  = urlencode($_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkzb&op=getroomlist&m=fm_jiaoyu');
        $result['data']['getvisitorsurl']  = urlencode($_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkzb&op=getvisitors&m=fm_jiaoyu');
        $result['data']['reportvisitorsurl']  = urlencode($_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkzb&op=reportvisitors&m=fm_jiaoyu');
        $result['data']['allsyurl']  = urlencode($_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkzb&op=allsy&m=fm_jiaoyu');
        $result['code'] = 1000;
        $result['msg'] = "success";
        $result['ServerTime'] = date('Y-m-d H:i:s',time());
        echo json_encode($result);
    }
}


if ($operation == 'class') {//获取班级信息
    if(!empty($ckmac)){
        if(!empty($_GPC['lastedittime'])){
            $condition = "and lastedittime >= '{$_GPC['lastedittime']}' ";
        }else{
            $condition = '';
        }
        $class = pdo_fetchall("SELECT sid as id, sname as classname ,  schoolid , ssort , tid,datesetid FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'theclass' And schoolid = {$school['id']} $condition ORDER BY ssort DESC");
        if($class){
            $nowdate = date("Y-n-j",time());
            $nowyear = date("Y",time());
            $nowweek = date("w",time()); //今天是星期几
            $result['code'] = 1000;
            $result['msg'] = "success";
            foreach($class as $key =>$row) {
                $todaytype = 0;
                $todaytimeset = array(
                    array(
                        'start'=>'00:00',
                        'end'  =>'23:59'
                    ),
                );
                if(!empty($row['datesetid'])){
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
                            $timeset_work = pdo_fetchall("SELECT start,end FROM " . tablename($this->table_checktimeset) . " WHERE weid = '{$weid}' And schoolid = {$school['id']} and  checkdatesetid = '{$row['datesetid']}' and type=1 ORDER BY id ASC ");
                            //星期五
                            if($nowweek == 5){
                                $todaytype = 2;
                                if($checkdateset['friday'] == 1){
                                    $timeset_fri = pdo_fetchall("SELECT start,end FROM " . tablename($this->table_checktimeset) . " WHERE weid = '{$weid}' And schoolid = {$school['id']} and  checkdatesetid = '{$row['datesetid']}' and type=2 ORDER BY id ASC ");
                                    $todaytimeset = $timeset_fri;
                                }else{
                                    $todaytimeset = $timeset_work;
                                }
                                //星期六
                            }elseif($nowweek == 6){
                                if($checkdateset['saturday'] == 1){
                                    $timeset_sat = pdo_fetchall("SELECT start,end FROM " . tablename($this->table_checktimeset) . " WHERE weid = '{$weid}' And schoolid = {$school['id']} and  checkdatesetid = '{$row['datesetid']}' and type=3 ORDER BY id ASC ");
                                    $todaytype = 2;
                                    $todaytimeset = $timeset_sat;
                                }else{
                                    $todaytype = 1;
                                }

                                //星期天
                            }elseif($nowweek == 0){
                                if($checkdateset['sunday'] == 1){
                                    $timeset_sun = pdo_fetchall("SELECT start,end FROM " . tablename($this->table_checktimeset) . " WHERE weid = '{$weid}' And schoolid = {$school['id']} and  checkdatesetid = '{$row['datesetid']}' and type=4 ORDER BY id ASC ");
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
                if(!empty($ckmac['apid'])){
                    $todaytype = 0;
                    $todaytimeset = array(
                        array(
                            'start'=>'00:00',
                            'end'  =>'23:59'
                        ),
                    );
                }
                $class[$key]['todaytype']    = $todaytype;
                $class[$key]['todaytimeset'] = $todaytimeset;
                unset($class[$key]['sid']);
                unset($class[$key]['datesetid']);
                unset($class[$key]['tid']);
            }
            $result['data'] = $class;
            $result['servertime'] = date('Y-m-d H:i:s',time());
        }else{
            $result['code'] = 1000;
            $result['msg'] = "本校未添加班级信息";
        }
        echo json_encode($result);
    }
}

if ($operation == 'users') { //getstatus获取学生信息
    if(!empty($ckmac)){
        if(!empty($_GPC['lastedittime'])){
            $condition = "and lastedittime >= '{$_GPC['lastedittime']}' ";
        }else{
            $condition = '';
        }
        $users = pdo_fetchall("SELECT id,idcard, sid, bj_id, usertype,spic,tpic,tid,cardtype FROM " . tablename($this->table_idcard) . " WHERE weid = '{$weid}' And schoolid = {$school['id']} And is_on = 1 $condition ORDER BY id DESC");
        if($users){
            $result['code'] = 1000;
            $result['msg'] = "success";
            foreach($users as $key =>$row) {
                if($row['usertype'] == 1){
                    $teacher = pdo_fetch("SELECT tname,thumb,sex,plate_num,mobile  FROM " . tablename($this->table_teachers) . " WHERE id = '{$row['tid']}' ");
                    $users[$key]['relationship'] = '';
                    $users[$key]['car_cards'] =$teacher['plate_num'];
                    //$users[$key]['id']           = "02".$row['tid'];
                    $users[$key]['sex']          = $teacher['sex'];
                    $users[$key]['name']         = $teacher['tname'];
                    $users[$key]['s_type']       = 2;	//暂时全部告诉考勤机都是走读学
                    $users[$key]['idcard']       = $row['idcard'];
                    $users[$key]['mobile']       = $teacher['mobile'];
                    $users[$key]['cid']          = '';
                    $users[$key]['type']         = 2;
                    $users[$key]['picrul']       = !empty($row['tpic']) ? tomedia($row['tpic'],false,true) : tomedia($teacher['thumb'],false,true);

                }elseif($row['usertype'] == 0){
                    $student = pdo_fetch("SELECT s_name,icon,numberid,sex,s_type,mobile  FROM " . tablename($this->table_students) . " WHERE id = '{$row['sid']}' ");
                    //修改开始
                    $users[$key]['cid'] = $row['bj_id'];
                    $users[$key]['name'] = $student['s_name'];
                    $users[$key]['s_type'] = $student['s_type'];

                    $relation = pdo_fetch("SELECT  pard,idcard  FROM " . tablename($this->table_idcard) . " WHERE idcard = '{$row['idcard']}' ");
                    if($relation['pard']=='1'){
                        $users[$key]['relationship'] = '';
                    }elseif($relation['pard']=='2'){
                        $users[$key]['relationship'] = '母亲';
                    }elseif($relation['pard']=='3'){
                        $users[$key]['relationship'] = '父亲';
                    }elseif($relation['pard']=='4'){
                        $users[$key]['relationship'] = '爷爷';
                    }elseif($relation['pard']=='5'){
                        $users[$key]['relationship'] = '奶奶';
                    }elseif($relation['pard']=='6'){
                        $users[$key]['relationship'] = '外公';
                    }elseif($relation['pard']=='7'){
                        $users[$key]['relationship'] = '外婆';
                    }elseif($relation['pard']=='8'){
                        $users[$key]['relationship'] = '叔叔';
                    }elseif($relation['pard']=='9'){
                        $users[$key]['relationship'] = '阿姨';
                    }elseif($relation['pard']=='10'){
                        $users[$key]['relationship'] = '其他';
                    }
                    $users[$key]['car_cards'] ='';
                    //修改结束
                    //$users[$key]['id']      = $row['sid'];
                    $users[$key]['sex']     = $student['sex'];
                    $users[$key]['name']    = $student['s_name'];
                    //暂时全部告诉考勤机都是走读学
                    $users[$key]['idcard']  = $row['idcard'];
                    $users[$key]['mobile']       = $student['mobile'];
                    $studentidcard = pdo_fetch("SELECT idcard  FROM " . tablename($this->table_idcard) . " WHERE sid = '{$row['sid']}' ");
                    $users[$key]['cid']          = $row['bj_id'];
                    $users[$key]['type']         = 1;
                    if($row['spic']){
                        $picrul = tomedia($row['spic'],false,true);
                    }else{
                        $picrul = empty($student['icon']) ? tomedia($school['spic'],false,true) : tomedia($student['icon'],false,true);//未设置头像，取默认头像
                    }
                    $users[$key]['picrul']       = $picrul;

                }elseif($row['cardtype'] == 2 && $row['usertype'] == 0){

                    //修改开始
                    $users[$key]['cid'] = $row['bj_id'];
                    $users[$key]['name'] = '班级卡';
                    $users[$key]['s_type'] = 1;	//暂时全部告诉考勤机都是走读学
                    $users[$key]['relationship'] = '';
                    $users[$key]['fingercards'] = array();
                    $users[$key]['car_cards'] =array();
                    //修改结束
                    //$users[$key]['id']      = $row['sid'];
                    $users[$key]['sex']     = 1;
                    $users[$key]['iccode']  = $row['idcard'];
                    $users[$key]['type']         = 1;
                    if($row['spic']){
                        $picrul = tomedia($row['spic'],false,true);
                    }else{
                        $picrul = empty($student['icon']) ? tomedia($school['spic'],false,true) : tomedia($student['icon'],false,true);//未设置头像，取默认头像
                    }
                    $users[$key]['picrul']       = $picrul;
                }
                unset($users[$key]['cardtype']);
                unset($users[$key]['usertype']);
                unset($users[$key]['sid']);
                unset($users[$key]['tid']);
                unset($users[$key]['bj_id']);

            }
            $result['data'] = $users;
            $result['servertime'] = date('Y-m-d H:i:s',time());
        }else{
            $result['code'] = 1000;
            $result['msg']    = "没有有效考勤卡信息";
        }
        echo json_encode($result);
        exit;
    }
}

if ($operation == 'check') {
    $fstype = false;
    $ckuser = pdo_fetch("SELECT * FROM " . tablename($this->table_idcard) . " WHERE idcard = :idcard And schoolid = :schoolid ", array(':idcard' =>$_GPC['signId'],':schoolid' =>$schoolid));
    $signTime = strtotime($_GPC['signTime']);
    $checkthisdata = pdo_fetch("SELECT * FROM " . tablename($this->table_checklog) . " WHERE cardid = :cardid And schoolid = :schoolid And createtime = :createtime ", array(':cardid' =>$_GPC['signId'],':schoolid' =>$schoolid,':createtime' =>$signTime));
    if(empty($checkthisdata)){
        if(!empty($ckuser)){
            $times = TIMESTAMP;
            $nowtime = date('H:i',$signTime);
            if($_GPC['__input']['picurl']) {
                load()->func('file');
                $urls = "https://mac.weimeizhan.com/attachment/";
                $path = "images/fm_jiaoyu/check/". date('Y/m/d/');
                if (!is_dir(IA_ROOT."/attachment/". $path)) {
                    mkdirs(IA_ROOT."/attachment/". $path, "0777");
                }
                $rand = random(30);
                if(!empty($_GPC['__input']['picurl'])) {
                    $picurl = $path.$rand."_1.jpg";

                        $pic_url = base64_decode(str_replace(" ","+",$_GPC['__input']['picurl']));

                    file_write($picurl,$pic_url);
                    if (!empty($_W['setting']['remote']['type'])){
                        $remotestatus = file_remote_upload($picurl);
                    }
                    $pic = $picurl;
                }
                if(!empty($_GPC['__input']['picurl2'])) {
                    $picurl2 = $path.$rand."_2.jpg";

                        $pic_url2 = base64_decode(str_replace(" ","+",$_GPC['__input']['picurl2']));

                    file_write($picurl2,$pic_url2);
                    if (!empty($_W['setting']['remote']['type'])){
                        $remotestatus = file_remote_upload($picurl2);
                    }
                    $pic2 = $picurl2;
                }
            }
            $signMode = $_GPC['signMode'];
            if($ckmac['type'] != 0){
                include 'checktime2.php';
            }else{
                include 'checktime.php';
            }
            if($_GPC['signId'] == '999999999'){
                $data = array(
                    'weid' => $weid,
                    'schoolid' => $schoolid,
                    'macid' => $ckmac['id'],
                    'lon' => $_GPC['lon'],
                    'lat' => $_GPC['lat'],
                    'cardid' => $_GPC ['signId'],
                    'type' => "无卡进出",
                    'pic' => $pic,
                    'pic2' => $pic2,
                    'leixing' => $leixing,
                    'createtime' => $signTime,
                    'surestatus'  => $xk_type['is_sure_kq'] == 1 ? 0 : 1,
                );
                pdo_insert($this->table_checklog, $data);
                $fstype = true;
            }
            if($ckuser['cardtype'] == 1) {


                if (!empty($ckuser['sid'])) {
                    $bj = pdo_fetch("SELECT bj_id,roomid,isopen FROM " . tablename($this->table_students) . " WHERE id = :id ", array(':id' => $ckuser['sid']));
                    if ($school['is_cardpay'] == 1) {
                        
                            if (!empty($ckmac['apid'])) {
                                if (!empty($bj['roomid'])) {
                                    $this_roomid = $bj['roomid'];
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
                        if ($ckuser['severend'] > $times && $bj['isopen'] == 1) {
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
                            if (!empty($bj['roomid'])) {
                                $this_roomid = $bj['roomid'];
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
                        if ($ckuser['severend'] > $times && $bj['isopen'] == 1) {
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
                            $result['info'] = "延迟发送之数据将不推送刷卡提示";
                        }
                        $fstype = true;
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
                        'lon'         => $_GPC['lon'],
                        'lat'         => $_GPC['lat'],
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
            }
        }else{
            $result['info'] = "本卡未绑定任何学生或老师";
        }
    }else{
        $fstype = true;
        $result['info'] = "不可重复相同刷卡数据";
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
    if ($fstype ==true){
        $result['data'] = "";
        $result['code'] = 1000;
        $result['msg'] = "success";
        $result['ServerTime'] = date('Y-m-d H:i:s',time());
        echo json_encode($result);
        exit;
    }else{
        $result['data'] = "";
        $result['code'] = 300;
        $result['msg'] = "lose";
        $result['ServerTime'] = date('Y-m-d H:i:s',time());
        echo json_encode($result);
        exit;
    }
}


if ($operation == 'banner') {
    $banner = unserialize($ckmac['banner']);
    $ims = tomedia($banner['pic1']).'#'.tomedia($banner['pic2']).'#'.tomedia($banner['pic3']).'#'.tomedia($banner['pic4']);
    $result['data'] = array(
        'img' => $ims,
        'mc' => $banner['pop']
    );
    $result['code'] = 1000;
    $result['msg'] = "success";
    $result['ServerTime'] = date('Y-m-d H:i:s',time());
    $banner['isflow'] = 2;
    $temp1['banner'] = serialize($banner);
    pdo_update($this->table_checkmac, $temp1, array('id' => $ckmac['id']));
    echo json_encode($result);
    exit;
}

if ($operation == 'video') {
    $banner = unserialize($ckmac['banner']);
    $result['data'] = array(
        'videourl' => $banner['video']
    );
    $result['code'] = 1000;
    $result['msg'] = "success";
    $result['ServerTime'] = date('Y-m-d H:i:s',time());
    $banner['isflow'] = 2;
    $temp1['banner'] = serialize($banner);
    pdo_update($this->table_checkmac, $temp1, array('id' => $ckmac['id']));
    echo json_encode($result);
    exit;
}

if ($operation == 'getleave') {
    $time = $_GPC['signTime'];
    $ckuser        = pdo_fetch("SELECT sid FROM " . tablename($this->table_idcard) . " WHERE idcard = '{$_GPC['signId']}' And weid = '{$weid}' And schoolid = '{$schoolid}' ");
    $leave        =  pdo_fetch("SELECT sid,startime1,endtime1 FROM " . tablename($this->table_leave) . " WHERE weid = '{$weid}'  And schoolid = '{$schoolid}' and isliuyan = 0 and status = 1 and startime1 <= '{$time}' and endtime1 >= '{$time}' and sid = '{$ckuser['sid']}' ");
    $result['code'] = 1000;
    $result['msg']    = "success";
    if(!empty($leave)){
        $result['data']['openDoor']   = 0;
    }else{
        $result['data']['openDoor']   = 1;
    }
    $result['ServerTime'] = date('Y-m-d H:i:s',time());
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
    $studentlist =pdo_fetchall("SELECT id , bj_id as cid,s_name  as name ,roomid FROM " . tablename($this->table_students) . " WHERE weid = '{$weid}' And schoolid = {$school['id']}  $condition ORDER BY id DESC");
    foreach($studentlist as $key =>$row) {
        $this_todaytype = $allclasstimeset[$row['cid']]['timeset']['todaytype'];
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
                $data[$ii]['cid'] = 0;
                $data[$ii]['roomid'] = 0;
                $data[$ii]['timeset'] = array(array('start'=>'00:00','end'=>'23:59'));
                $ii++;
            }
        }
    }
    $result['ServerTime'] = date('Y-m-d H:i:s',time());
    $result['code'] = 1000;
    $result['msg']    = "success";
    $result['data']   = $data;
    echo json_encode($result);
    exit;
}

if ($operation == 'online') {
    $checkorder = pdo_fetch("SELECT * FROM " . tablename($this->table_online) . " WHERE macid = '{$ckmac['id']}' And result = 2 And isread = 2 order by createtime ASC LIMIT 1 ");
    $nowtimes = time();
    $nowtime = date('Y-m-d H:i:s',$nowtimes);
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


if ($operation == 'report') {
    $order = pdo_fetch("SELECT id,result FROM " . tablename($this->table_online) . " WHERE :id = id", array(':id' => trim($_GPC['command_id'])));
    if($order){
        if($order['result'] == 2){
            $data = array(
                'result' => trim($_GPC['result']),
                'dotime' => time()
            );
            pdo_update($this->table_online, $data, array('id' => $order['id']));
            $result['ServerTime'] = date('Y-m-d H:i:s',time());
            $result['code'] = 1000;
            $result['msg']    = "success";
        }else{
            $result['ServerTime'] = date('Y-m-d H:i:s',time());
            $result['code'] = 300;
            $result['msg']    = "本条任务已执行";
        }
    }else{
        $result['ServerTime'] = date('Y-m-d H:i:s',time());
        $result['code'] = 300;
        $result['msg']    = "本条任务不存在或者已删除";
    }
    echo json_encode($result);
    exit;
}

if ($operation == 'getvisitors') {
    $nowtime = time();
    $nowdate_start = strtotime(date("Y-m-d",$nowtime));
    if(!empty($_GPC['lastedittime'])){
        $condition = "and lastedittime >= '{$_GPC['lastedittime']}' ";
    }else{
        $condition = '';
    }
    $visitorslist = pdo_fetchall("SELECT id,t_id,sy_id,icon,s_name,unit,plate_num,idcard,starttime,endtime,status,lastedittime,tel FROM " . tablename('wx_school_visitors') . " WHERE weid ='{$weid}' and schoolid = '{$schoolid}' and starttime >= '{$nowdate_start}' and (status = 2 or status=4 or status=6) $condition ");
    $data = array();
    $ii = 0 ;
    if(!empty($visitorslist)){
        foreach ($visitorslist as $key=>$value){
            $data[$ii]['visitorid'] = $value['id'];
            $data[$ii]['unit'] = $value['unit'];
            $data[$ii]['vmobile'] = $value['tel'];
            $data[$ii]['name'] = $value['s_name'];
            $data[$ii]['idcard'] = $value['idcard'];
            $data[$ii]['icon'] = tomedia($value['icon']);
            $data[$ii]['status'] = $value['status'];
            $data[$ii]['plate_num'] = $value['plate_num'];
            $data[$ii]['starttime'] =date('Y-m-d H:i:s', $value['starttime']);
            $data[$ii]['endtime'] =date('Y-m-d H:i:s', $value['endtime']);
            $teacher = pdo_fetch("SELECT tname,mobile FROM " . tablename('wx_school_teachers') . " WHERE weid ='{$weid}' and schoolid = '{$schoolid}' and id = '{$value['t_id']}' ");
            $data[$ii]['tname'] =$teacher['tname'];
            $data[$ii]['mobile'] =$teacher['mobile'];
            $shiyou = pdo_fetch("SELECT sname FROM " . tablename('wx_school_classify') . " WHERE weid ='{$weid}' and schoolid = '{$schoolid}' and sid = '{$value['sy_id']}' ");
            $data[$ii]['reason'] =$shiyou['sname'];
            $data[$ii]['lastedittime'] =$value['lastedittime'];
            $data[$ii]['condition'] =$condition;
            $ii++;
        }
        $result['data'] = $data;
    }else{
        $result['data'] = array();
    }
    $result['ServerTime'] = date('Y-m-d H:i:s',time());
    $result['code'] = 1000;
    $result['msg']    = "success";
    echo json_encode($result);
    exit;
}



if ($operation == 'reportvisitors') {
    $fstype = false;
    $result['back_type'] = "S1";
    $signTime = strtotime($_GPC['visTime']);

    //如果是线上预约的
    $visitorid = $_GPC['visitorId'];
    if(!empty($visitorid)) {
        $ckvisitor = pdo_fetch("SELECT * FROM " . tablename('wx_school_visitors') . " WHERE schoolid = '{$schoolid}' and weid = '{$weid}' and id = '{$visitorid}' ");
        $result['back_type'] .= "A1";
        $result['visitorid'] = $visitorid;
        $result['visitorid_b'] = $_GPC['visitorId'];
        //如果不是线上预约的
    }elseif(empty($visitorid) && !empty($_GPC['visUnicid'])){
        //如果能够查到，则表示为线下直接访问的，且是离校记录
        $ckvisitor = pdo_fetch("SELECT * FROM " . tablename('wx_school_visitors') . " WHERE schoolid = '{$schoolid}' and weid = '{$weid}' and zb_unicid = '{$_GPC['visUnicid']}' ");
        $visitorid = $ckvisitor['id'];
        $result['back_type'] .= "B1";
        //如果查不到，则表示为线下直接访问的，且是进校记录，所以要往预约表里写
        if(empty($ckvisitor)){
            $data = array();
            $result['back_type'] .= "B4";
            $tname   = $_GPC['visTname'];
            $tmobile = $_GPC['visTmobile'];
            if(!empty($tname) && !empty($tmobile)){
                $teacher = pdo_fetch("SELECT id FROM " . tablename('wx_school_teachers') . " WHERE weid = :weid And schoolid = :schoolid And tname like :tname and mobile = :mobile ", array(':weid' => $weid, ':schoolid' => $schoolid, ':tname' => $tname, 'mobile' => $tmobile));
                if(!empty($teacher)){

                    if($_GPC['__input']['headimg']) {
                        load()->func('file');
                        $urls = "https://mac.weimeizhan.com/attachment/";
                        $path = "images/fm_jiaoyu/check/". date('Y/m/d/');
                        if (!is_dir(IA_ROOT."/attachment/". $path)) {
                            mkdirs(IA_ROOT."/attachment/". $path, "0777");
                        }
                        $rand = random(30);
                        if(!empty($_GPC['__input']['headimg'])) {
                            $picurl = $path.$rand."_1.jpg";

                                $pic_url = base64_decode(str_replace(" ","+",$_GPC['__input']['headimg']));

                            file_write($picurl,$pic_url);
                            if (!empty($_W['setting']['remote']['type'])){
                                $remotestatus = file_remote_upload($picurl);
                            }
                            $pic = $picurl;
                        }
                    }
                    $starttime = strtotime($_GPC['visTime']);
                    $endtime = strtotime(date("Y-m-d",$starttime))+86399;
                    $data['t_id']       = $teacher['id'];
                    $data['schoolid']   = $schoolid;
                    $data['weid']       = $weid;
                    $data['icon']       = $pic;
                    $data['s_name']     = $_GPC['visName'];
                    $data['unit']       = $_GPC['visUnit'];
                    $data['plate_num']  = $_GPC['visPlateNum'];
                    $data['idcard']     = $_GPC['visIdcard'];
                    $data['sy_id']      = $_GPC['visReasonid'];
                    $data['starttime']  = strtotime($_GPC['visTime']);
                    $data['endtime']  = $endtime;
                    $data['tel']        = $_GPC['vphone'];
                    $data['zb_unicid']  = $_GPC['visUnicid'];
                    $data['status']     = 3;
                    $data['createtime'] = strtotime($_GPC['visTime']);

                    $result['other_info'] = "已找到";
                    pdo_insert('wx_school_visitors',$data);
                    $visitorid = pdo_insertid();
                    $result['back_type'] .= "B2";
                    $ckvisitor = pdo_fetch("SELECT * FROM " . tablename('wx_school_visitors') . " WHERE schoolid = '{$schoolid}' and weid = '{$weid}' and id = '{$visitorid}' ");
                }else{
                    $fstype =false;
                    $result['back_type'] .= "B3";
                    $result['other_info'] = "找不到该教师";
                }
            }
        }
    }
    $result['back_type'] .= "C1";
    $checkthisdata = pdo_fetch("SELECT * FROM " . tablename('wx_school_vislog') . " WHERE vis_id = :vis_id And schoolid = :schoolid And createtime = :createtime ", array(':vis_id' =>$visitorid,':schoolid' =>$schoolid,':createtime' =>$signTime));
    if(empty($checkthisdata)){
        $result['back_type'] .= "S6";
        if(!empty($ckvisitor)){
            $result['back_type'] .= "S7";
            $times = TIMESTAMP;
            $nowtime = date('H:i',$signTime);
            if($_GPC['__input']['picurl']) {
                load()->func('file');
                $urls = "https://mac.weimeizhan.com/attachment/";
                $path = "images/fm_jiaoyu/check/". date('Y/m/d/');
                if (!is_dir(IA_ROOT."/attachment/". $path)) {
                    mkdirs(IA_ROOT."/attachment/". $path, "0777");
                }
                $rand = random(30);
                if(!empty($_GPC['__input']['picurl'])) {
                    $picurl = $path.$rand."_1.jpg";

                        $pic_url = base64_decode(str_replace(" ","+",$_GPC['__input']['picurl']));

                    file_write($picurl,$pic_url);
                    if (!empty($_W['setting']['remote']['type'])){
                        $remotestatus = file_remote_upload($picurl);
                    }
                    $pic = $picurl;
                }
                if(!empty($_GPC['__input']['picurl2'])) {
                    $picurl2 = $path.$rand."_2.jpg";

                        $pic_url2 = base64_decode(str_replace(" ","+",$_GPC['__input']['picurl2']));

                    file_write($picurl2,$pic_url2);
                    if (!empty($_W['setting']['remote']['type'])){
                        $remotestatus = file_remote_upload($picurl2);
                    }
                    $pic2 = $picurl2;
                }
            }
                $signMode = $_GPC['signMode'];
            if($signMode == 65){
                $leixing = '进校';
                $check_type = 1;
            }elseif($signMode == 66){
                $leixing = '离校';
                $check_type = 2;
            }
            $data = array(
                'weid' => $weid,
                'schoolid' => $schoolid,
                'macid' => $ckmac['id'],
                'vis_id' => $visitorid,
                'tid' => $ckvisitor['t_id'],
                'plate_num' => $ckvisitor['plate_num'],
                'pic' => $pic,
                'pic2' => $pic2,
                'type' => $check_type,
                'leixing' => $leixing,
                'createtime' => $signTime
            );
            pdo_insert($this->table_vislog, $data);
            if($check_type == 1){
                pdo_update($this->table_visitors,array('status'=>4),array('id'=>$visitorid));
            }elseif($check_type == 2){
                pdo_update($this->table_visitors,array('status'=>5),array('id'=>$visitorid));
                if(!empty($ckvisitor['zb_unicid']) && empty($ckvisitor['endtime'])){
                    pdo_update($this->table_visitors,array('endtime'=>$signTime),array('id'=>$visitorid));
                }
            }
            $fstype = true;

        }else{
            $result['info'] = "本条访客记录不存在或已删除";
        }
    }else{
        $fstype = true;
        $result['info'] = "不可重复相同访客数据";
    }
    if ($fstype ==true){
        $result['code'] = 1000;
        $result['msg'] = "success";
        $result['ServerTime'] = date('Y-m-d H:i:s',time());
        echo json_encode($result);
        exit;
    }else{
        $result['code'] = 300;
        $result['msg'] = "lose";
        $result['ServerTime'] = date('Y-m-d H:i:s',time());
        echo json_encode($result);
        exit;
    }
}



if ($operation == 'allsy'){
    //visireason
    $list = pdo_fetchall("SELECT sname as title,sid as id FROM " . tablename('wx_school_classify') . " WHERE weid = :weid And schoolid = :schoolid And type=:type ", array(':weid' =>$weid,':schoolid' =>$schoolid,':type' =>'visireason'));
    $result['code'] = 1000;
    $result['msg'] = "success";
    $result['data'] = $list;
    $result['ServerTime'] = date('Y-m-d H:i:s',time());
    echo json_encode($result);
    exit;
}
