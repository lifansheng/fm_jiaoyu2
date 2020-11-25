<?php

mload()->model('snowflake'); 


function XzPortSendData($port,$sendData){
	$url = 'http://yz.kstms.com'.$port;

	$post_data = json_encode($sendData);
	if (empty($url) || empty($post_data)) {
        return false;
    }
    $postUrl = $url;
    $curlPost = $post_data;
    $curl = curl_init();
	if(version_compare(PHP_VERSION,'5.5.0','>=')){
		curl_setopt($curl, CURLOPT_URL, $postUrl);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
		$response = curl_exec($curl);
	}else{
		curl_setopt($curl, CURLOPT_URL, $postUrl);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_TIMEOUT, 100);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
		$response = curl_exec($curl);
	}
	 CommonWriteLog(array('url' =>$postUrl,'data'=>$curlPost,'result' => $response ),'PortLog','XunZhen');
    return $response;
}

/**
 * 访客通知讯贞
 *
 * @param int $VisId
 *
 * @return void
 */
function XzAddVistorAct($VisId){
    $VisInfo = pdo_fetch("SELECT * FROM ".GetTableName('lxvis')." WHERE id = '{$VisId}' ");
    $sid = $VisInfo['sid'];
    $userid = $VisInfo['userid'];
    $schoolid = $VisInfo['schoolid'];
    $weid= $VisInfo['weid'];
    $UserInfo = pdo_fetch("SELECT realname,pard FROM ".GetTableName('user')." WHERE id = '{$userid}' ");
    $studentInfo = pdo_fetch("SELECT s_name FROM ".GetTableName('students')." WHERE id = '{$sid}' ");
    $PardName = array(
        '2' => '母亲',
        '3' => '父亲',
        '4' => '',
        '5' => '家长'
    );

    $checkVIsGroup = pdo_fetch("SELECT sid FROM ".GetTableName('classify')." WHERE type='visgroup' and sname = '访客' and  schoolid = '{$schoolid}' ");
    if(empty($checkVIsGroup)){
        $insertData = array(
            'weid' => $weid,
            'schoolid' => $schoolid,
            'sname' => '访客',
            'type' => 'visgroup'
        );
        pdo_insert(GetTableName('classify',false),$insertData);
        $VGID = pdo_insertid();
    }else{
        $VGID = $checkVIsGroup['sid'];
    }
    $UserName = $UserInfo['realname'] ? $UserInfo['realname'] : $studentInfo['s_name'].$PardName[$UserInfo['pard']];
    $schoolid = $VisInfo['schoolid'];
    $weid = $VisInfo['weid'];
    $checkmac = pdo_fetch("SELECT macid FROM ".GetTableName('checkmac')." WHERE schoolid = '{$schoolid}' AND macname = 2 ORDER BY id DESC ");
    $userInfos  = array(
        'idCardNum' => '',
        'imagePath' => tomedia($VisInfo['thumb']),
        'rfid'      => $VisInfo['tempcard'],
        'userName'  => $UserName,
        'userNo'    => $VisInfo['snowid'],
        'userType'  => 3,
        'groupNo'   => $VGID,
        'groupName' => '访客',
        'timeSet'  =>array(
            'timeType' => 'date',
            'date' => array(
                'day' => date("Y-m-d",$VisInfo['starttime']),
                'passTime'=>array(
                    0 => array(
                        'start' => date("H:i",$VisInfo['starttime']),
                        'end' => date("H:i",$VisInfo['endtime'])
                    )
                   
                )
            )
        )
    );
    $sendData['deviceNo']  = $checkmac['macid'];
    $sendData['userInfos'][] = $userInfos;
    CommonWriteLog($sendData,'xzlog','xunzhen');
    $porturl = '/add_users_by_device.do';
    $res = json_decode(XzPortSendData($porturl,$sendData),true);
    return $res;
}


function XzGetSetTimeData($schoolid,$id,$opera){

    $checkmac = pdo_fetch("SELECT macid FROM ".GetTableName('checkmac')." WHERE schoolid = '{$schoolid}' AND macname = 2 ORDER BY id DESC ");
    if(empty($checkmac['macid'])){
        return false;
    }

    $checkdateset = pdo_fetch("SELECT * FROM ".GetTableName('checkdateset')." WHERE schoolid = '{$schoolid}' AND id = '{$id}' ");
    $bjlist = pdo_fetchall("SELECT sid FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' AND datesetid = '{$id}' ");
    $nowdate = strtotime(date("Ymd",time()));
    $data = [];
    // for ($i=0; $i < 7; $i++) { 
    //     $datatemp = [];
    //     if($opera == 'del'){
    //         $date = date("Y-m-j",$nowdate + 86400*$i);
    //         $datatemp['timeSet'] = array(
    //             'stype'=>0,
    //             'start'=>'00:00',
    //             'end'=>'23:59',
    //         );
    //     }else{
    //         $unixtime = $nowdate + 86400*$i;
    //         $date = date("Y-m-j",$nowdate + 86400*$i);
    //         $datatemp['timeList'][$i]['date'] = $date; //TODO:
    //         // 取出当天数据是否为特殊设置
    //         $list = pdo_fetchall("SELECT * FROM ".GetTableName('checktimeset')." WHERE schoolid = '{$schoolid}' AND date = '{$date}' AND checkdatesetid = '{$id}'");
    //         if($list){
    //             foreach ($list as $k => $v) {
    //                 if($v['out_in'] == 0){
    //                     $datatemp['timeSet'][] = array(
    //                         'stype'=>$v['s_type'],
    //                         'start'=>$v['start'],
    //                         'end'=>$v['end'],
    //                     );
    //                 }
    //                 if($v['out_in'] == 1){
    //                    $datatemp['timeSetIn'][] = array(
    //                         'stype'=>$v['s_type'],
    //                         'start'=>$v['start'],
    //                         'end'=>$v['end'],
    //                     );
    //                 }
    //                 if($v['out_in'] == 2){
    //                     $datatemp['timeSetOut'][] = array(
    //                         'stype'=>$v['s_type'],
    //                         'start'=>$v['start'],
    //                         'end'=>$v['end'],
    //                     );
    //                 }
                    
    //             }
    //         }else{ //判断是否属于寒暑假日期范围
    //             $holiday = pdo_fetch("SELECT id FROM ".GetTableName('checkdatedetail')." WHERE schoolid = '{$schoolid}' AND checkdatesetid = '{$id}' AND ((unix_timestamp(sum_start) <= '{$unixtime}' AND unix_timestamp(sum_end) >= '{$unixtime}') OR (unix_timestamp(win_start) <= '{$unixtime}' AND unix_timestamp(win_end) >= '{$unixtime}'))");
    //             if($holiday){ //在假期范围内
    //                 $datatemp['timeSet'][] = array(
    //                     'stype'=>0,
    //                     'start'=>'00:00',
    //                     'end'=>'23:59',
    //                 );
    //             }else{
    //                 $nowday = date("w",$unixtime);
    //                 if($nowday == 5){
    //                     $type = '2';
    //                     if($checkdateset['friday'] == 1){
    //                         $speciallist = pdo_fetchall("SELECT * FROM ".GetTableName('checktimeset')." WHERE schoolid = '{$schoolid}' AND type = '{$type}' AND checkdatesetid = '{$id}'");
    //                     }else{
    //                         $speciallist = pdo_fetchall("SELECT * FROM ".GetTableName('checktimeset')." WHERE schoolid = '{$schoolid}' AND type = '1' AND checkdatesetid = '{$id}'");
    //                     }
    //                 }elseif($nowday == 6){
    //                     $type = '3';
    //                     if($checkdateset['saturday'] == 1){
    //                         $speciallist = pdo_fetchall("SELECT * FROM ".GetTableName('checktimeset')." WHERE schoolid = '{$schoolid}' AND type = '{$type}' AND checkdatesetid = '{$id}'");
    //                     }
    //                 }elseif($nowday == 0){
    //                     $type = '4';
    //                     if($checkdateset['sunday'] == 1){
    //                         $speciallist = pdo_fetchall("SELECT * FROM ".GetTableName('checktimeset')." WHERE schoolid = '{$schoolid}' AND type = '{$type}' AND checkdatesetid = '{$id}'");
    //                     }
    //                 }else{
    //                     $type = '1';
    //                     $speciallist = pdo_fetchall("SELECT * FROM ".GetTableName('checktimeset')." WHERE schoolid = '{$schoolid}' AND type = '{$type}' AND checkdatesetid = '{$id}'");
    //                 }
        
    //                 if($speciallist){
    //                     foreach ($speciallist as $k1 => $v1) {
    //                         if($v1['out_in'] == 0){
    //                             $datatemp['timeSet'][] = array(
    //                                 'stype'=>$v1['s_type'],
    //                                 'start'=>$v1['start'],
    //                                 'end'=>$v1['end'],
    //                                 'id'=>$v1['id'],
    //                             );
    //                         }
    //                         if($v1['out_in'] == 1){
    //                             $datatemp['timeSetIn'][] = array(
    //                                 'stype'=>$v1['s_type'],
    //                                 'start'=>$v1['start'],
    //                                 'end'=>$v1['end'],
    //                                 'id'=>$v1['id'],
    //                             );
    //                         }
    //                         if($v1['out_in'] == 2){
    //                             $datatemp['timeSetOut'][] = array(
    //                                 'stype'=>$v1['s_type'],
    //                                 'start'=>$v1['start'],
    //                                 'end'=>$v1['end'],
    //                                 'id'=>$v1['id'],
    //                             );
    //                         }
    //                     }
    //                 }else{
    //                     $datatemp['timeSet'][] = array(
    //                         'stype'=>0,
    //                         'start'=>'00:00',
    //                         'end'=>'23:59',
    //                     );
    //                 }
    //             }
    //         }
    //     }
    //     foreach ($bjlist as $key => $value) {
    //         $datatemp['classid'] = $value['sid'];
    //         $data['timeList'][] = $datatemp;
    //     }
    // }
    $datatemp = [];
    if($opera == 'del'){
        $datatemp['timeSet'][] = array(
            'stype'=>0,
            'start'=>'00:00',
            'end'=>'23:59',
        );
        $datatemp['timeSetIn'][] = array(
            'stype'=>0,
            'start'=>'00:00',
            'end'=>'23:59',
        );
        $datatemp['timeSetOut'][] = array(
            'stype'=>0,
            'start'=>'00:00',
            'end'=>'23:59',
        );
    }else{
        $date = date("Y-m-j",$nowdate);
        // 取出当天数据是否为特殊设置
        $list = pdo_fetchall("SELECT * FROM ".GetTableName('checktimeset')." WHERE schoolid = '{$schoolid}' AND date = '{$date}' AND checkdatesetid = '{$id}'");
        //如果有特殊设置，根据特殊设置的来
        if($list){
            foreach ($list as $k => $v) {
                if($v['out_in'] == 0){
                    $datatemp['timeSet'][] = array(
                        'stype'=>$v['s_type'],
                        'start'=>$v['start'],
                        'end'=>$v['end'],
                    );
                }
                if($v['out_in'] == 1){
                    $datatemp['timeSetIn'][] = array(
                        'stype'=>$v['s_type'],
                        'start'=>$v['start'],
                        'end'=>$v['end'],
                    );
                }
                if($v['out_in'] == 2){
                    $datatemp['timeSetOut'][] = array(
                        'stype'=>$v['s_type'],
                        'start'=>$v['start'],
                        'end'=>$v['end'],
                    );
                }
                
            }
        }else{ //判断是否属于寒暑假日期范围
            $holiday = pdo_fetch("SELECT id FROM ".GetTableName('checkdatedetail')." WHERE schoolid = '{$schoolid}' AND checkdatesetid = '{$id}' AND ((unix_timestamp(sum_start) <= '{$nowdate}' AND unix_timestamp(sum_end) >= '{$nowdate}') OR (unix_timestamp(win_start) <= '{$nowdate}' AND unix_timestamp(win_end) >= '{$nowdate}'))");
            if($holiday){ //在假期范围内，则为全天
                $datatemp['timeSet'][] = array(
                    'stype'=>0,
                    'start'=>'00:00',
                    'end'=>'23:59',
                );
                $datatemp['timeSetIn'][] = array(
                    'stype'=>0,
                    'start'=>'00:00',
                    'end'=>'23:59',
                );
                $datatemp['timeSetOut'][] = array(
                    'stype'=>0,
                    'start'=>'00:00',
                    'end'=>'23:59',
                );
            }else{ //既不是特殊设置，也不是假期
                $nowday = date("w",$nowdate);
                if($nowday == 5){ //周五
                    $type = '2';
                    if($checkdateset['friday'] == 1){ //如果启用了周五单独设置
                        $speciallist = pdo_fetchall("SELECT * FROM ".GetTableName('checktimeset')." WHERE schoolid = '{$schoolid}' AND type = '{$type}' AND checkdatesetid = '{$id}'");
                    }else{
                        $speciallist = pdo_fetchall("SELECT * FROM ".GetTableName('checktimeset')." WHERE schoolid = '{$schoolid}' AND type = '1' AND checkdatesetid = '{$id}'");
                    }
                }elseif($nowday == 6){ //周六
                    $type = '3';
                    if($checkdateset['saturday'] == 1){ //如果启用了周六单独设置
                        $speciallist = pdo_fetchall("SELECT * FROM ".GetTableName('checktimeset')." WHERE schoolid = '{$schoolid}' AND type = '{$type}' AND checkdatesetid = '{$id}'");
                    }
                }elseif($nowday == 0){
                    $type = '4';
                    if($checkdateset['sunday'] == 1){ //如果启用了周日单独设置
                        $speciallist = pdo_fetchall("SELECT * FROM ".GetTableName('checktimeset')." WHERE schoolid = '{$schoolid}' AND type = '{$type}' AND checkdatesetid = '{$id}'");
                    }
                }else{ //周一至周四
                    $type = '1';
                    $speciallist = pdo_fetchall("SELECT * FROM ".GetTableName('checktimeset')." WHERE schoolid = '{$schoolid}' AND type = '{$type}' AND checkdatesetid = '{$id}'");
                }
    
                if($speciallist){ //查出来了就按照查出来的
                    foreach ($speciallist as $k1 => $v1) {
                        if($v1['out_in'] == 0){
                            $datatemp['timeSet'][] = array(
                                'stype'=>$v1['s_type'],
                                'start'=>$v1['start'],
                                'end'=>$v1['end'],
                                // 'id'=>$v1['id'],
                            );
                        }
                        if($v1['out_in'] == 1){
                            $datatemp['timeSetIn'][] = array(
                                'stype'=>$v1['s_type'],
                                'start'=>$v1['start'],
                                'end'=>$v1['end'],
                                // 'id'=>$v1['id'],
                            );
                        }
                        if($v1['out_in'] == 2){
                            $datatemp['timeSetOut'][] = array(
                                'stype'=>$v1['s_type'],
                                'start'=>$v1['start'],
                                'end'=>$v1['end'],
                                // 'id'=>$v1['id'],
                            );
                        }
                    }
                }
                else{ //没查出来就全天
                    $datatemp['timeSet'][] = array(
                        'stype'=>0,
                        'start'=>'00:00',
                        'end'=>'23:59',
                    );
                    $datatemp['timeSetIn'][] = array(
                        'stype'=>0,
                        'start'=>'00:00',
                        'end'=>'23:59',
                    );
                    $datatemp['timeSetOut'][] = array(
                        'stype'=>0,
                        'start'=>'00:00',
                        'end'=>'23:59',
                    );
                }
            }
        }
    }
    if(empty($datatemp['timeSetOut'])){
        $datatemp['timeSetOut'][] = array(
            'stype'=>0,
            'start'=>'00:00',
            'end'=>'23:59',
        );
    }
    if(empty($datatemp['timeSetIn'])){
        $datatemp['timeSetIn'][] = array(
            'stype'=>0,
            'start'=>'00:00',
            'end'=>'23:59',
        );
    }
    if(empty($datatemp['timeSet'])){
        $datatemp['timeSet'][] = array(
            'stype'=>0,
            'start'=>'00:00',
            'end'=>'23:59',
        );
    }
    foreach ($bjlist as $key => $value) {
        $datatemp['classid'] = $value['sid'];
        $data['timeList'][] = $datatemp;
    }
    $data['deviceNo']  = $checkmac['macid'];
	 CommonWriteLog('提交数据','PortLog','XunZhen');
	 CommonWriteLog($data,'PortLog','XunZhen');

    $porturl = '/wjy/reset_time_set.do';
    $res = json_decode(XzPortSendData($porturl,$data),true);
	 CommonWriteLog('接口返回','PortLog','XunZhen');
	 CommonWriteLog($res,'PortLog','XunZhen');

	return $data;
}



