<?php
/**
 *  和校园 mod
 * 
 */


/**
 * 获取是否采用统一平台短信发送
 *
 * @param [type] $schoolid
 *
 * @return void
 */
function CheckTyptSMSSet($schoolid){
    $school = pdo_fetch("SELECT typt_school_id,typt_ec_code FROM ".GetTableName('index')." WHERE  id = '{$schoolid}'  ");
    if(empty($school['typt_school_id']) || empty($school['typt_ec_code'])){
        return; 
    }else{
        $schoolset = pdo_fetch("SELECT msgsendtype,typt_admin_tid FROM ".GetTableName('schoolset')." WHERE  schoolid = '{$schoolid}'  ");
        $sendtyptid = pdo_fetch("SELECT typt_user_id FROM ".GetTableName('teachers')." WHERE  id = '{$schoolset['typt_admin_tid']}'  ");
        $result['msgsendtype']    = $schoolset['msgsendtype'];
        $result['typt_admin_tid'] = $schoolset['typt_admin_tid'];
        $result['sendtyptid']     = $sendtyptid['typt_user_id'];
        $result['ec_code']        = $school['typt_ec_code'];
        $result['typt_school_id'] = $school['typt_school_id'];
        return $result;
    }
}


/**
 * 发送 post 请求 function
 *
 * @param [type] $url
 * @param [type] $jsonStr
 *
 * @return void
 */
function hxy_http_post_json($url, $post_data,$IsArray = false){
    if (empty($url) || empty($post_data)) {
        return false;
    }
    if($IsArray == true){
        $o = "";
        foreach ( $post_data as $k => $v ) 
        { 
            $o.= $k."=" . urlencode( $v ). "&" ;
        }
        $post_data = substr($o,0,-1);
    }else{
        $post_data = "data=".$post_data;
    }

    $postUrl = $url;
    $curlPost = $post_data;
    $ch = curl_init();//初始化curl
    curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
    curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
    curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
    curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
    $data = curl_exec($ch);//运行curl
    curl_close($ch);

    return $data;
}


 function GetSchoolInfo($hxy_schoolid,$hxy_ec_code,$appid){
    $post_data_orgin = array(
        'appid' => $appid,
        'schoolid'=> $hxy_schoolid
    );
    $post_data_json = json_encode($post_data_orgin);
    $url = "http://yntyptoauthtest.jiaxiaoquan.com/typtOauth/typt/qry_batch_grade";
    $GetInfo = hxy_http_post_json($url,$post_data_json);
    return $GetInfo;
 }



/**
 * 根据统一平台schoolid同步学校信息以及下属信息
 *
 * @param [type] $hxy_schoolid
 * @param [type] $appid
 *
 * @return void
 */
 function SyncSchoolAndGradeInfo($hxy_schoolid,$appid){
    $post_data_orgin = array(
        'appid' => $appid,
        'schoolid'=> $hxy_schoolid
    );
    $post_data_json = json_encode($post_data_orgin);
 
    $url = "http://oauth.f.jiaxiaoquan.com/typtOauth/typt/qry_batch_grade";
    $GetInfo =json_decode(hxy_http_post_json($url,$post_data_json),true) ;  
    if(empty($GetInfo) || ( !empty($GetInfo['error_code']) && $GetInfo['error_code'] != '00') ){
        $result['status'] = false;
        $result['msg'] = "未获取到有效信息";
        WriteLog($GetInfo);
    }else{
        $GradeInfo = $GetInfo['grades']; //年级数据
        $SchoolName = $GetInfo['schoolname']; //学校名字
        $CheckSchool = pdo_fetch("SELECT id,weid FROM ".GetTableName('index')." WHERE typt_school_id = '{$hxy_schoolid}' ");
        $schoolid = $CheckSchool['id'];
        $weid = $CheckSchool['weid'];
        pdo_update(GetTableName('index',false),array('title'=>$SchoolName),array('id'=>$schoolid)); //每次获取的时候都更新学校名字
        foreach($GradeInfo as $key =>$value){
            $njname = $value['gradename'];
            $typt_gradeid = $value['gradeid'];
            //检查年级是否已经存在
            /**
             * 两种情况，第一种，年级在微教育里已经存在，但没有与和校园同步，那么查询重名就行
             * 第二种，年级在微教育里存在，在和校园里也存在，但名字可能不一样，那么查询typt_id就行
             * 如果名字一样，且typtid 是 0 ，说明就是这个年级，但还没有被同步
             * 如果存在typtid匹配的，说明就是这个年级
             * 应该先检查typtid
             * 
             */
            $checkGrade = pdo_fetch("SELECT sid FROM ".GetTableName('classify')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' and ( typt_id = '{$typt_gradeid}'  or ( sname='{$njname}' and typt_id = 0 )  ) and type = 'semester' ");
            $insert_grade_data = array(
                'weid' => $weid,
                'schoolid' => $schoolid,
                'sname' => $njname,
                'typt_id' => $typt_gradeid,
                'type' => 'semester'
            );
            if(empty($checkGrade)){ //如果不存在
                pdo_insert(GetTableName('classify',false),$insert_grade_data);
            }else{
                pdo_update(GetTableName('classify',false),$insert_grade_data,array('sid'=>$checkGrade['sid']));
            }
           $GradeBack = @SyncClassInfo($typt_gradeid,$appid);
         
        }
          @SyncAllTea($hxy_schoolid,$appid);
        $result['status'] = true;
        $result['msg'] = "同步学校信息成功！";
        if($GradeBack['status'] == false ){
            $result['msg'] .= " | ".$GradeBack['msg']; 
        }
    }
    return $result; 
 }



/**
 * 根据统一平台年级ID 同步班级信息
 *
 * @param [type] $gradeid 年级ID
 * @param [type] $appid   appID
 *
 * @return void
 */
 function SyncClassInfo($gradeid,$appid){
    $post_data_orgin = array(
        'appid' => $appid,
        'gradeid'=> $gradeid
    );
    $post_data_json = json_encode($post_data_orgin);
    $url = "http://oauth.f.jiaxiaoquan.com/typtOauth/typt/qry_batch_class";
    $GetInfo =json_decode(hxy_http_post_json($url,$post_data_json),true) ;  
    if(empty($GetInfo) || ( !empty($GetInfo['error_code']) && $GetInfo['error_code'] != '00') ){
        $result['status'] = false;
        $result['msg'] = "未获取到有效信息";
        return $result;
    }else{
        $ClassInfo = $GetInfo['classes'];
        $GradeName = $GetInfo['gradename'];
         
        $CheckGrade = pdo_fetch("SELECT sid,weid,schoolid  FROM ".GetTableName('classify')." WHERE typt_id = '{$gradeid}' and type = 'semester'  ");
        $schoolid = $CheckGrade['schoolid'];
        $weid = $CheckGrade['weid'];
        pdo_update(GetTableName('classify',false),array('sname'=>$GradeName),array('sid'=>$gradeid));
        foreach($ClassInfo as $key =>$value){
            $ClassName = $value['classname'];
            $typt_classid = $value['classid'];
            /**
             * 检查班级是否存在，两种情况
             * 第一种，班级在微教育里已经存在了，但没有与和校园同步，检查名字是否存在，以及其上级年级是否是对应的年级
             * 第二种，班级在微教育里存在，且已经与和校园同步了，只是名字不一定相同 ，检查 typtid
             */
            $checkClass = pdo_fetch("SELECT sid FROM ".GetTableName('classify')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' and (  typt_id = '{$typt_classid}' or ( sname='{$ClassName}' and parentid = '{$CheckGrade['sid']}' and typt_id = 0 )  ) and type = 'theclass' ");
            $insert_class_data = array(
                'weid' => $weid,
                'schoolid' => $schoolid,
                'sname' => $ClassName,
                'typt_id' => $typt_classid,
                'type' => 'theclass',
                'parentid' => $CheckGrade['sid']
            );
            if(empty($checkClass)){ //如果不存在
                pdo_insert(GetTableName('classify',false),$insert_class_data);
            }else{
                pdo_update(GetTableName('classify',false),$insert_class_data,array('sid'=>$checkClass['sid']));
            }
            $GradeBack = @SyncStuInfo($typt_classid,$appid);
            $GradeBackTea = @SyncTeaInfo($typt_classid,$appid);
        }
        $result['status'] = true;
        $result['msg'] = "同步年级所有班级信息成功！";
        if($GradeBack['status'] == false ){
            $result['msg'] .= " | ".$GradeBack['msg']; 
        }
        if($GradeBackTea['status'] == false ){
            $result['msg'] .= " | ".$GradeBack['msg']; 
        }
    }
    return $result;
 }

 /**
  * 根据统一平台班级ID 同步下属学生信息
  *
  * @param [type] $classid
  * @param [type] $appid
  *
  * @return void
  */
 function SyncStuInfo($classid,$appid){
    $post_data_orgin = array(
        'appid' => $appid,
        'classid'=> $classid
    );
    $post_data_json = json_encode($post_data_orgin);
    $url = "http://oauth.f.jiaxiaoquan.com/typtOauth/typt/qry_class_student";
    $GetInfo =json_decode(hxy_http_post_json($url,$post_data_json),true) ;
  
    WriteLog($GetInfo);
    if(empty($GetInfo) || ( !empty($GetInfo['error_code']) && $GetInfo['error_code'] != '00') ){
        $result['status'] = false;
        $result['msg'] = "未获取到有效信息";
       
    }else{
        $Stuinfo = $GetInfo['students'];
         WriteLog($Stuinfo);
        //查询班级信息。执行这一步的时候班级肯定已经有了，所以是肯定能够查得到的
        $CheckClass = pdo_fetch("SELECT sid,weid,schoolid,parentid  FROM ".GetTableName('classify')." WHERE typt_id = '{$classid}' and type = 'theclass'  ");
        $schoolid = $CheckClass['schoolid'];
        $weid = $CheckClass['weid'];
        foreach($Stuinfo as $key =>$value){
            $rAndStr = str_shuffle('123456789');
            $rAnd    = substr($rAndStr, 0, 6);
            $StuName = $value['studentname'];
            $typt_user_id = $value['studentid'];
            $mobile = $value['usermobile'];
            $numberid = $value['stunumber'];
            $idcard = $value['cardcode'];
            $createtime = $value['createtime'];
            $Stu_Insert_data = array(
                'weid' => $weid,
                'schoolid' => $schoolid,
                'numberid' => $numberid,
                'xq_id' => $CheckClass['parentid'],
                'bj_id' => $CheckClass['sid'],
                'mobile' => $mobile,
                'seffectivetime' => $createtime,
                's_name' => $StuName,
                'typt_user_id' => $typt_user_id,
                'code' => $rAnd 
            );
            /**
             * 如果学生的typtid 相同 ，或者 名字和电话号码同时相同，就认定为存在
             */
            $CheckStu = pdo_fetch("SELECT id,code FROM ".GetTableName('students')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' and  (   typt_user_id = '{$typt_user_id}' or ( s_name = '{$StuName}' and mobile = $mobile and typt_user_id = 0  )  )");
            $stuId = 0;
            if(!empty($CheckStu)){
                $Stu_Insert_data['code'] = $CheckStu['code'] ? $CheckStu['code'] : $rAnd ;
                pdo_update(GetTableName('students',false),$Stu_Insert_data,array('id'=>$CheckStu['id']));
                $stuId = $CheckStu['id'];
            }else{
                pdo_insert(GetTableName('students',false),$Stu_Insert_data);
                $stuId = pdo_insertid();
            }
         
          
            /**如果传过来的数据中有卡号信息 */
            if(!empty($idcard)){
                $CardInsertData = array(
                    'weid' => $weid,
                    'schoolid' => $schoolid,
                    'sid' => $stuId,
                    'bj_id' => $CheckClass['sid'],
                    'pname' =>  $StuName,
                    'idcard' => str_pad(intval($idcard),10,"0",STR_PAD_LEFT),
                    'pard' => 1,
                    'is_on' => 1,
                    'createtime' => time()
                );
                /**
                 * 如果学生本人在卡记录表里有记录，就认定为存在
                 */
                $CheckCard = pdo_fetch("SELECT id FROM ".GetTableName('idcard')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' and sid = '{$stuId}' and pard = 1 ");
                if(!empty($CheckCard)){
                    pdo_update(GetTableName('idcard',false),$CardInsertData,array('id'=>$CheckCard['id']));
                }else{
                    pdo_insert(GetTableName('idcard',false),$CardInsertData);
                }
            }   
        }
        $result['status'] = true;
        $result['msg'] = "同步班级下的学生信息成功！";
    }
    return $result;
 }

 /**
  * 根据统一平台班级ID 同步教师信息
  *
  * @param [type] $classid
  * @param [type] $appid
  *
  * @return void
  */
 function SyncTeaInfo($classid,$appid){
    $post_data_orgin = array(
        'appid' => $appid,
        'classid'=> $classid
    );
    $post_data_json = json_encode($post_data_orgin);
    $url = "http://oauth.f.jiaxiaoquan.com/typtOauth/typt/qry_class_teacher";
    $GetInfo =json_decode(hxy_http_post_json($url,$post_data_json),true) ;
    if(empty($GetInfo) || ( !empty($GetInfo['error_code']) && $GetInfo['error_code'] != '00')){
        $result['status'] = false;
        $result['msg'] = "未获取到有效信息";  
    }else{
        $teainfo = $GetInfo['teachers'];
        $CheckClass = pdo_fetch("SELECT sid,weid,schoolid,parentid  FROM ".GetTableName('classify')." WHERE typt_id = '{$classid}' and type = 'theclass'  ");
        $schoolid = $CheckClass['schoolid'];
        $weid = $CheckClass['weid'];
        foreach($teainfo as $key =>$value){
            $TeaName      = $value['teachername'];
            $typt_user_id = $value['teacherid'];
            $TeaRole = $value['teacherrole'];
            $SubJectId = $value['subjectid'];
            $SubName = $value['subjectname'];
            $rAndStr = str_shuffle('123456789');
            $rAnd    = substr($rAndStr, 0, 6);
            $TeaInsertData = array(
                'weid' => $weid,
                'schoolid' => $schoolid,
                'tname' => $TeaName,
                'typt_user_id' => $typt_user_id,
                'code' => $rAnd 
            );
            //如果老师名存在，或者typtid存在，则认定为存在
            $CheckTea = pdo_fetch("SELECT id,code FROM ".GetTableName('teachers')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' and   (typt_user_id = '{$typt_user_id}' or  ( tname = '{$TeaName}' and typt_user_id = 0 ) ) ");
            $TeaId = 0;
            if(!empty($CheckTea)){ //如果查到了老师 就修改
                $TeaInsertData['code'] =  $CheckTea['code'] ? $CheckTea['code'] : $rAnd;
                pdo_update(GetTableName('teachers',false),$TeaInsertData,array('id'=>$CheckTea['id']));
                $TeaId = $CheckTea['id'];
            }else{ //如果没查到，就新增
                pdo_insert(GetTableName('teachers',false),$TeaInsertData);
                $TeaId = pdo_insertid();
            }

            if($TeaRole == 0){ //如果是班主任
                pdo_update(GetTableName('classify',false),array('tid' => $TeaId ),array('sid'=>$CheckClass['sid']));
            }

            //检查科目
            $CheckSub = pdo_fetch("SELECT sid FROM ".GetTableName('classify')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' and  ( typt_id = '{$SubJectId}' or ( sname = '{$SubName}' and typt_id = 0 ) ) and type = 'subject' ");
            $SubId = 0 ;
            if(!empty($CheckSub)){
                pdo_update(GetTableName('classify',false),array('sname'=>$SubName),array('sid'=>$CheckSub['sid']));
                $SubId = $CheckSub['sid'];
            }else{
                $SubInsertData = array(
                    'weid' => $weid,
                    'schoolid' => $schoolid,
                    'sname' => $SubName,
                    'type' => 'subject',
                    'typt_id' => $SubJectId
                );
                pdo_insert(GetTableName('classify',false),$SubInsertData);
                $SubId = pdo_insertid();
            }

            //检查user_class
            $CheckTeaClass = pdo_fetch("SELECT * FROM ".GetTableName('user_class')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' and tid = '{$TeaId}' and bj_id = '{$CheckClass['sid']}' and km_id = '{$SubId}' ");
            if(empty($CheckTeaClass)){
                $UCinsertData = array(
                    'weid' => $weid,
                    'schoolid' => $schoolid,
                    'tid' => $TeaId,
                    'bj_id' => $CheckClass['sid'],
                    'km_id' => $SubId,
                    'type' => 1
                );
                pdo_insert(GetTableName('user_class',false),$UCinsertData);
            }
        }
        $result['status'] = true;
        $result['msg'] = "同步班级下的老师信息成功！";
          
    }
    return $result;
 }

/**
 * 获取学校所有老师
 *
 * @param [type] $schoolid 统一平台上的学校schoolid
 * @param [type] $appid
 *
 * @return void
 */
 function SyncAllTea($schoolid,$appid){
    $post_data_orgin = array(
        'appid' => $appid,
        'schoolid'=> $schoolid
    );
    $post_data_json = json_encode($post_data_orgin);
    $url = "http://oauth.f.jiaxiaoquan.com/typtOauth/typt/qry_school_teacher";
    $GetInfo =json_decode(hxy_http_post_json($url,$post_data_json),true) ;
    
    if(empty($GetInfo) || ( !empty($GetInfo['error_code']) && $GetInfo['error_code'] != '00')){
        $result['status'] = false;
        $result['msg'] = "未获取到有效信息";
       
    }else{
        $teainfo = $GetInfo['teachers'];
        $CheckSchool = pdo_fetch("SELECT id,weid FROM ".GetTableName('index')." WHERE typt_school_id = '{$schoolid}' ");
        $schoolid = $CheckSchool['id'];
        $weid = $CheckSchool['weid'];
        foreach($teainfo as $key =>$value){
        
            $TeaName      = $value['teachername'];
            $typt_user_id = $value['teacherid'];
            $mobile       = $value['mobile'];
            $TeaRole      = $value['role'] == 4 ? 1 : 0;
            $TeaCard      = $value['cardcode'];
            $rAndStr      = str_shuffle('123456789');
            $rAnd         = substr($rAndStr, 0, 6);
            $TeaInsertData = array(
                'weid' => $weid,
                'schoolid' => $schoolid,
                'tname' => $TeaName,
                'typt_user_id' => $typt_user_id,
                'code' => $rAnd,
                'mobile' => $mobile,
                'typt_is_admin' => $TeaRole
            );
            $CheckTea = pdo_fetch("SELECT id,code FROM ".GetTableName('teachers')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' and  ( typt_user_id = '{$typt_user_id}' or   ( tname = '{$TeaName}' and mobile = '{$mobile}' and typt_user_id = 0 ) ) ");
            $TeaId = 0;
            if(!empty($CheckTea)){
                $TeaInsertData['code'] =  $CheckTea['code'] ? $CheckTea['code'] : $rAnd;
                pdo_update(GetTableName('teachers',false),$TeaInsertData,array('id'=>$CheckTea['id']));
                $TeaId = $CheckTea['id'];
            }else{
                pdo_insert(GetTableName('teachers',false),$TeaInsertData);
                $TeaId = pdo_insertid();
            }

            $CardInsertData = array(
                'weid' => $weid,
                'schoolid' => $schoolid,
                'tid' => $TeaId,
                'pname' =>  $TeaName,
                'idcard' => str_pad(intval($TeaCard),10,"0",STR_PAD_LEFT) ,
               'usertype' => 1,
                'is_on' => 1,
                'createtime' => time()
            );
            if(!empty($TeaCard)){
                $CheckCard = pdo_fetch("SELECT id FROM ".GetTableName('idcard')." WHERE tid = '{$TeaId}' and schoolid = '{$schoolid}' and weid = '{$weid}'  ");
                if(!empty($CheckCard)){
                    pdo_update(GetTableName('idcard',false),$CardInsertData,array('id'=>$CheckCard['id']));
                }else{
                    pdo_insert(GetTableName('idcard',false),$CardInsertData);
                }
            } 
        }
        $result['status'] = true;
        $result['msg'] = "同步班级下的老师信息成功！";
         
    }
    return $result;
 }

/**
 * 统一平台发送通用消息
 *
 * @param [type] $appid
 * @param [type] $op_time
 * @param [type] $ec_code
 * @param [type] $smscontent
 * @param [type] $receivers
 * @param [type] $sendid
 *
 * @return void
 */
 function SendHXYsms($appid,$op_time,$ec_code,$smscontent,$receivers,$sendid){
    $post_data_orgin = array(
        'appid' => $appid,
        'op_time'=> $op_time,
        'eccode' => $ec_code,
        'smscontent' => $smscontent,
        'receivers' => $receivers,
        'sendid' => $sendid
    );
    $post_data_json = json_encode($post_data_orgin);
    $PD = array(
        'Data' => $post_data_json,
        'MsgType'=>'APP_SEND_COMMON_MSGSYNC'
    );
    $url = "http://inter.yn.jiaxiaoquan.com/typtInterface/sms/smsSend";
    $GetInfo = hxy_http_post_json($url,$PD,true) ;
    
    $LogD = array(
    	'postData' => $post_data_orgin,
    	'postjson' => $post_data_json,
    	'posturl'  => $url,
    	'msgtype'  => 'APP_SEND_COMMON_MSGSYNC',
    	'ResultInfo' => $GetInfo
    	
    	);
    
    WriteLog($LogD);
    return $GetInfo;
 }


/**
 * 统一平台 同步学生考勤信息
 *
 * @param [type] $appid 
 * @param [type] $op_time
 * @param [type] $ec_code
 * @param [type] $ChecklogID
 *
 * @return void
 */
 function SendHXYCheckSms($appid,$ec_code,$ChecklogID){
    $checkinfo = pdo_fetch("SELECT * FROM ".GetTableName('checklog')." WHERE id = '{$ChecklogID}' ");
    $stuinfo = pdo_fetch("SELECT * FROM ".GetTableName('students')." WHERE id = '{$checkinfo['sid']}' ");
    $terminal_name = '微信签到';
    if($checkinfo['checktype'] == 1){ //刷卡
        $macinfo = pdo_fetch("SELECT name FROM ".GetTableName('checkmac')." WHERE id = '{$checkinfo['macid']}' ");
        $terminal_name = $macinfo['name'] ? $macinfo['name'] : '未知设备';
    }
    $checkin_type = 0;
    if($checkinfo['leixing'] == 1){
        $checkin_type = 1;
    }elseif($checkinfo['leixing'] == 2){
        $checkin_type = 2;
    }
    $stunum = $stuinfo['numberid'];
    $checkin_time = date("Y-m-d H:i:s",$checkinfo['createtime']);
    $card_code =intval($checkinfo['cardid']);
    $img_src = tomedia($checkinfo['pic']);
    $post_data_orgin = array(
        'appid' => $appid,
        'eccode' => $ec_code,
        'checkin_type' => $checkin_type,
        'stunum' => $stunum,
        'card_code' => $card_code,
        'checkin_time' => $checkin_time,
        'img_src' => $img_src,
        'terminal_name' => $terminal_name
    );
    $post_data_json = json_encode($post_data_orgin);
    $PD = array(
        'Data' => $post_data_json,
        'MsgType'=>'APP_SEND_CARDCHECKIN_BYSTUNUM'
    );
    //$url = "http://oauth.f.jiaxiaoquan.com/typtInterface/interface/sendCardCodeOperate"; //测试环境
    $url = "http://inter.yn.jiaxiaoquan.com/typtInterface/interface/sendCardCodeOperate"; //正式环境

    $GetInfo = hxy_http_post_json($url,$PD,true) ;
        
    $LogD = array(
    	'postData' => $post_data_orgin,
    	'postjson' => $post_data_json,
    	'posturl'  => $url,
    	'msgtype'  => 'APP_SEND_CARDCHECKIN_BYSTUNUM',
    	'ResultInfo' => $GetInfo
    	
    	);
    var_dump($post_data_orgin);
    WriteLog($LogD);
    return $GetInfo;
 }

/**
 * 统一平台发送消费通知
 *
 * @param [type] $appid
 * @param [type] $op_time YYYY-mm-dd hh24:mi:ss 示例：2017-11-09 16:47:29
 * @param [type] $ec_code
 * @param [type] $PaylogId
 * @param [type] $paytype 1 yuecostlog ，2 order
 * @param [type] $cost_type 1 充值 ，2 消费  
 * @param [type] $is_yue 1 消耗余额 ，2 不是余额
 *
 * @return void
 */
 function SendHXYPaySms($appid,$op_time,$ec_code,$cost,$macid,$sid,$paytype = 1,$cost_type = 2,$is_yue = 1){ 
    $schoolinfo = pdo_fetch("SELECT id,is_buzhu,title FROM ".GetTableName('index')." WHERE typt_ec_code = '{$ec_code}' ");
    $stuinfo = pdo_fetch("SELECT * FROM ".GetTableName('students')." WHERE id = '{$sid}' ");
    $cardinfo = pdo_fetch("SELECT idcard FROM ".GetTableName('idcard')." WHERE sid = '{$sid}' ");
    $consume_price = $cost;
    $terminal_name = '线上消费'; //默认为线上消费
    $terminal_id = '99999999'; //
    $terminal_location = '线上消费';
    $card_code = intval($cardinfo['idcard']);
    $stunum = $stuinfo['numberid'];
    $account_balance = '';
    if($paytype == 1){ //余额消费
        $terminal_name = "{$macid}号消费机";
        $terminal_location = $schoolinfo['title'].$terminal_name;
        $terminal_id = "{$macid}";
        if($schoolinfo['is_buzhu'] != 1){ //启用补助
            $yue = $stuinfo['chongzhi'] + $stuinfo['buzhu'];
            $account_balance = "{$yue}";
        }else{
            $account_balance = "{$stuinfo['chongzhi']}";
        }
    }elseif($paytype == 2){ //订单缴费
        if($is_yue == 1){ //消耗的余额
            if($schoolinfo['is_buzhu'] != 1){ //启用补助
                $yue = $stuinfo['chongzhi'] + $stuinfo['buzhu'];
                $account_balance = "{$yue}";
            }else{
                $account_balance = "{$stuinfo['chongzhi']}";
            } 
        }else{ //不是消耗的余额
            $account_balance = '';
        }
    }
    $post_data_orgin = array(
        'appid' => $appid,
        'eccode' => $ec_code,
        'consume_type' => $cost_type,
        'stunum' => $stunum,
        'card_code' => $card_code,
        'consume_price' => $consume_price * 100 .'',
        'account_balance' => $account_balance * 100 .'',
        'op_time' => $op_time,
        'terminal_id' => $terminal_id,
        'terminal_location' => $terminal_location,
        'terminal_name' => $terminal_name
    );
    $post_data_json = json_encode($post_data_orgin);
    $PD = array(
        'Data' => $post_data_json,
        'MsgType'=>'APP_SEND_CARDCONSUME_BYSTUNUM'
    );
    $url = "http://inter.yn.jiaxiaoquan.com/typtInterface/interface/sendCardCodeOperate";
    $GetInfo = hxy_http_post_json($url,$PD,true) ;
       $LogD = array(
    	'postData' => $post_data_orgin,
    	'postjson' => $post_data_json,
    	'posturl'  => $url,
    	'msgtype'  => 'APP_SEND_CARDCONSUME_BYSTUNUM',
    	'ResultInfo' => $GetInfo
    	
    	);
    
    WriteLog($LogD);
    return $GetInfo;
 }


function WriteLog($GetData){ 
  
    $txtname = 'hxyportSendLog.txt';
    $txtpath_name = IA_ROOT . '/attachment/down/' . $txtname;


      ob_start(); //打开缓冲区
      var_dump($GetData);
      $a = ob_get_contents(); //输出缓冲区内容到$a,相当于赋值给$a
      ob_clean();   //这里清除缓冲区内容
  
      $fp = fopen($txtpath_name,"a");//打开文件资源通道 不存在则自动创建       
    fwrite($fp,date("Y-m-d H:i:s").'   '.$a."\r\n");//写入文件
    fclose($fp);//关闭资源通道
}




//检查增量老师数据
function CheckZlTeaInfo($appid,$tea_user_id){
    $post_data_orgin = array(
        'appid' => $appid,
        'teacherid' => $tea_user_id
    );
    $post_data_json = json_encode($post_data_orgin);
    $url = "http://oauth.f.jiaxiaoquan.com/typtOauth/typt/qry_teacher_info";
    $GetInfo =json_decode(hxy_http_post_json($url,$post_data_json),true) ;
    if(empty($GetInfo) || ( !empty($GetInfo['error_code']) && $GetInfo['error_code'] != '00')){
        $result['status'] = false;
        $result['msg'] = "未获取到有效信息"; 
    }else{
        $TeaClass = $GetInfo['classes'];
        $typt_schoolid = $GetInfo['schoolid'];
        $CheckSchool = pdo_fetch("SELECT id,weid FROM ".GetTableName('index')." WHERE typt_school_id = '{$typt_schoolid}' ");
        $schoolid = $CheckSchool['id'];
        $weid = $CheckSchool['weid'];
        $teacher = pdo_fetch("SELECT id FROM ".GetTableName('teachers')." WHERE schoolid = '{$schoolid}' and typt_id = '{$tea_user_id}' ");
        $TeaOldClass = pdo_fetch("SELECT id FROM ".GetTableName('user_class')." WHERE schoolid = '{$schoolid}' and tid = '{$teacher['id']}' "); 
        if(!empty($TeaOldClass)){
            pdo_delete(GetTableName('user_class',false),array('tid'=>$teacher['id'],'type' => 1 ,'schoolid' => $schoolid));
        }
        foreach($TeaClass as $k=>$v){
            $GradeId = $v['gradeid'];
            $GradeName = $v['gradename'];
            $ClassId = $v['classid'];
            $ClassName = $v['classname'];
            $SubjectId = $v['subjectid'];
            $SubjectName = $v['subjectname'];
            $CheckGrade = pdo_fetch("SELECT sid FROM ".GetTableName('classify')." WHERE  schoolid = '{$schoolid}' and typt_id = '{$GradeId}'  ");
            if(empty($CheckGrade)){
                $CkGRaName = pdo_fetch("SELECT sid FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and sname = '{$GradeName}' and typt_id = 0 and type = 'semester' ");
                if(!empty($CkGRaName)){
                    pdo_update(GetTableName('classify',false),array('typt_id' => $GradeId),array('sid'=>$CkGRaName['sid']));
                    $njid = $CkGRaName['sid'];
                }else{
                    $InsGradeData = array(
                        'weid' => $weid,
                        'schoolid' => $schoolid,
                        'sname' => $GradeName,
                        'typt_id' => $GradeId,
                        'type' => 'semester'
                    );
                    pdo_insert(GetTableName('classify',false),$InsGradeData);
                    $njid = pdo_insertid();
                }
            }else{
                $njid = $CheckGrade['sid'];
            }
            
            $CheckClass = pdo_fetch("SELECT sid,parentid FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and type_id = '{$ClassId}' ");
            if(empty($CheckClass)){
                $CkClsName = pdo_fetch("SELECT sid FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and sname = '{$ClassName}' and typt_id = 0 and type = 'theclass' and parentid = '{$njid}' ");
                if(!empty($CkClsName)){
                    pdo_update(GetTableName('classify',false),array('typt_id' => $ClassId),array('sid'=>$CkClsName['sid']));
                    $bjid = $CkClsName['sid'];
                }else{
                    $InsClassData = array(
                        'weid' => $weid,
                        'schoolid' => $schoolid,
                        'sname' => $ClassName,
                        'typt_id' => $ClassId,
                        'type' => 'theclass',
                        'parentid' => $njid
                    );
                    pdo_insert(GetTableName('classify',false),$InsClassData);
                    $bjid = pdo_insertid();
                }
            }else{
                if($CheckClass['parentid'] != $njid){
                    pdo_update(GetTableName('classify',false),array('parentid'=>$njid),array('id'=>$CheckClass['sid']));
                }
                $bjid = $CheckClass['sid'];
            }
            $CheckSub = pdo_fetch("SELECT sid FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and typt_id = '{$SubjectId}' and type = 'subject' ");
            if(empty($CheckSub)){
                $CkSubName = pdo_fetch("SELECT sid FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and sname = '{$SubjectName}' and typt_id = 0 and type = 'subject'  ");
                if(!empty($CkSubName)){
                    pdo_update(GetTableName('classify',false),array('typt_id' => $SubjectId),array('id'=>$CkSubName['sid']));
                    $subid = $CkSubName['sid'];
                }else{
                    $InsSubData = array(
                        'weid' => $weid,
                        'schoolid' => $schoolid,
                        'sname' => $SubjectName,
                        'typt_id' => $SubjectId,
                        'type' => 'subject'
                    );
                    pdo_insert(GetTableName('classify',false),$InsSubData);
                    $subid = pdo_insertid();
                }
            }else{
                $subid = $CheckSub['sid'];
            }

            $InsTCData = array(
                'weid' => $weid,
                'schoolid' => $schoolid,
                'tid' =>$teacher['id'],
                'bj_id' => $bjid,
                'km_id' => $subid,
                'type' => 1
            );

            pdo_insert(GetTableName('user_class',false),$InsTCData);

        }
    }
}

 
