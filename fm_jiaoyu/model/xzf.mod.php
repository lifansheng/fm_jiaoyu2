<?php
 define('XZF_BASEURL', "https://api.xiaopay.net/openapi/service/"); //线上环境服务地址
 // define('XZF_BASEURL', "https://devapi.xiaopay.net/openapi/service/"); //开发调试环境服务地址
 define('XZF_APPID', "927f2fd1a6d99b6b1a39869c8e16104d");
 define('XZF_SECRET', "4f6992889a08658f");



 /**
  * 获取SN
  *
  * @param array $Data
  * @param string $key
  *
  * @return string
  */
function GetSN($Data,$key){
    ksort($Data);
    $NewStr = '';
    foreach($Data as $k => $v){
        $NewStr .= $k .'-' .$v;
    }
    $NewStr = 'api'.$NewStr.'api';
    $sn = md5(encrypt($NewStr,$key));
    return $sn;
}


/**
 * AES 加密
 *
 * @param string $data
 * @param string $key
 *
 * @return string
 */
function encrypt($data, $key) {
    $data = openssl_encrypt($data, 'aes-128-ecb', $key, OPENSSL_RAW_DATA);
    return base64_encode($data);
}

/**
 * AES 解密
 *
 * @param string $data
 * @param string $key
 *
 * @return string
 */
function decrypt($data, $key) {
    $encrypted = base64_decode($data);
    return openssl_decrypt($encrypted, 'aes-128-ecb', base64_decode($key), OPENSSL_RAW_DATA);
}



function xzf_http_post_json($url, $post_data)
{
    if (empty($url) || empty($post_data)) {
        return false;
    }
    $postUrl = XZF_BASEURL . $url;
    $o = "";
    foreach ( $post_data as $k => $v )
    {
        $o.= $k."=" . urlencode( $v ). "&" ;
    }
    $curlPost = substr($o,0,-1);
    $ch = curl_init();//初始化curl
    curl_setopt($ch, CURLOPT_URL, $postUrl);//抓取指定网页
    curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
    curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Content-Type: application/x-www-form-urlencoded"
    ));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
    $data = curl_exec($ch);//运行curl
    curl_close($ch);
    return $data;
}




/**
 * 获取token
 *
 * @param int $weid
 *
 * @return array
 */
function CheckToken($weid ,$reGet = false){
    $TokenInfo = pdo_fetch("SELECT xzftoken,xzftokentime,xzfstatus FROM ".GetTableName('set')." WHERE weid = '{$weid}' ");
    if($TokenInfo['xzfstatus'] != 1){
        $res['msg'] = '尚未开启校智付';
        $res['result'] = false;
        return $res;
    }
    $NowTime = time();
    if(($TokenInfo['xzftokentime'] + 3500 * 2) > $NowTime && $reGet == false){
        $result['status'] = true;
        $result['token'] = $TokenInfo['xzftoken'];
    }else {
        $res = Login($weid);
        if($res['ack'] == 0){
            $UpdateData = array(
                'xzftoken' => $res['results'],
                'xzftokentime' => time()
            );
            pdo_update(GetTableName('set',false),$UpdateData,array('weid'=>$weid));
            $result['status'] = true;
            $result['token'] = $res['results'];
        }else {
            $result['status'] = false;
            $result['ack'] = $res['ack'];
            $result['msg'] = $res['msg'];
        }

    }
    return $result;
}
/**
 * 登录
 *
 * @param int $weid
 *
 * @return void
 */
function Login($weid){
    $xzf_info = pdo_fetch("SELECT xzfappid,xzfsecret,xzfstatus FROM ".GetTableName('set')." WHERE weid = '{$weid}' ");
    if($xzf_info['xzfstatus'] != 1){
        $res['msg'] = '尚未开启校智付';
        $res['result'] = false;
        return $res;
    }
    $appid = $xzf_info['xzfappid'];
    $secret = $xzf_info['xzfsecret'];
    $data = array(
        'appid' => str_replace(PHP_EOL, '', base64_encode($appid)),
        'timestamp' => getMillisecond()
    );
    $sn = GetSN($data,$secret);
    $data['sn'] =  $sn;
    $res = xzf_http_post_json('login',$data);
    return json_decode($res,true);
}


function SyncGrade($schoolid,$IsTask = false){
    if(!CheckXZF($schoolid)){
        $res['msg'] = '尚未开启校智付';
        $res['result'] = false;
        return $res;
    }
    $scinfo = pdo_fetch("SELECT xzf_scid,weid FROM ".GetTableName('schoolset')." WHERE schoolid = '{$schoolid}' ");
    $scid = $scinfo['xzf_scid'];
    $weid = $scinfo['weid'];
    $xzf_info = pdo_fetch("SELECT xzfappid FROM ".GetTableName('set')." WHERE weid = '{$weid}' ");
    $appid = $xzf_info['xzfappid'];
    $tokenGet = CheckToken($weid);
    if($tokenGet['status'] == true){ //获取成功
        $token = $tokenGet['token'];
        if($IsTask){
            $GradeInfo = pdo_fetchall("SELECT sid as id,sname as gradename,xzf_datastatus as datastatus FROM ".GetTableName('classify')." WHERE  schoolid = '{$schoolid}' and type='semester' and xzf_needsync = 1  ");
        }else {
            $GradeInfo = pdo_fetchall("SELECT sid as id,sname as gradename,xzf_datastatus as datastatus FROM ".GetTableName('classify')." WHERE  schoolid = '{$schoolid}' and type='semester'  ");

        }
        $Datajson = str_replace('"datastatus":1', '"datastatus":"1"', str_replace('"datastatus":2', '"datastatus":"2"', json_encode($GradeInfo,JSON_UNESCAPED_UNICODE + JSON_NUMERIC_CHECK)));
        // return $Datajson;

        $data = array(
            'appid' => str_replace(PHP_EOL, '', base64_encode($appid)),
            'scid' => str_replace(PHP_EOL, '', base64_encode($scid)),
            'datajson' => str_replace(PHP_EOL, '', base64_encode($Datajson)),
            'timestamp' => getMillisecond()
        );
        $sn = GetSN($data,$token);
        $data['sn'] =  $sn;
        $res =  json_decode(xzf_http_post_json('SaveGradeInformation',$data),true);
        if($res['ack'] == 0){
            pdo_update(GetTableName('classify',false),array('xzf_datastatus'=>2,'xzf_needsync'=>0),array('schoolid'=>$schoolid,'type'=>'semester'));
        }
        $res['status'] = true;
        return $res;
    }else { //失败
        return $tokenGet;
    }
}

/**
 * 同步班级
 *
 * @param int $schoolid
 *
 * @return array
 */
function SyncClass($schoolid,$IsTask = false){
    if(!CheckXZF($schoolid)){
        $res['msg'] = '尚未开启校智付';
        $res['result'] = false;
        return $res;
    }
    $scinfo = pdo_fetch("SELECT xzf_scid,weid FROM ".GetTableName('schoolset')." WHERE schoolid = '{$schoolid}' ");
    $scid = $scinfo['xzf_scid'];
    $weid = $scinfo['weid'];
    $xzf_info = pdo_fetch("SELECT xzfappid FROM ".GetTableName('set')." WHERE weid = '{$weid}' ");
    $appid = $xzf_info['xzfappid'];
    $tokenGet = CheckToken($weid);
    if($tokenGet['status'] == true){ //获取成功
        $token = $tokenGet['token'];
        // classify 表 union extra表
        if($IsTask){
            $ClassInfo = pdo_fetchall("SELECT sid as id,sname as classname,xzf_datastatus as datastatus,parentid as gradeid FROM ".GetTableName('classify')." WHERE  schoolid = '{$schoolid}' and type='theclass' and xzf_needsync = 1  ");
        }else {
            $ClassInfo = pdo_fetchall("SELECT * FROM ( SELECT 0 as exid, sid as id,sname as classname,xzf_datastatus as datastatus,parentid as gradeid FROM ".GetTableName('classify')." WHERE  schoolid = '{$schoolid}' and type='theclass'  ) as c union ( SELECT id as exid, fid as id,name as classname , 3 as datastatus, highid as gradeid  FROM ". GetTableName('xzfextra') . "  WHERE schoolid = '{$schoolid}' and isdone = 0 and type = 1  )  ");
        }
        $temparr = $ClassInfo;
        foreach($ClassInfo as &$value){
            $value['id'] = intval($value['id']);
            $value['gradeid'] = intval($value['gradeid']);
            unset($value['exid']);
        };
        $Datajson = json_encode($ClassInfo,JSON_UNESCAPED_UNICODE);
        //return $Datajson;
        $data = array(
            'appid' => str_replace(PHP_EOL, '', base64_encode($appid)),
            'scid' => str_replace(PHP_EOL, '', base64_encode($scid)),
            'datajson' => str_replace(PHP_EOL, '', base64_encode($Datajson)),
            'timestamp' => getMillisecond()
        );
        $sn = GetSN($data,$token);
        $data['sn'] =  $sn;
        $res =  json_decode(xzf_http_post_json('SaveClassInformation',$data),true);
        if($res['ack'] == 0){
            pdo_update(GetTableName('classify',false),array('xzf_datastatus'=>2 ,'xzf_needsync' => 0),array('schoolid'=>$schoolid,'type'=>'theclass'));
            if(!$IsTask){
                pdo_update(GetTableName('extra',false),array('isdone'=>1),array('schoolid'=>$schoolid,'type'=>'1'));
            }
            $res['status'] = true;
        }
        if($res['ack'] == 21 || $res['ack'] == 51) {
            $tokenGet = CheckToken($weid,true);
            $res = SyncClass($schoolid);
        }
        return $res;
    }else { //失败
        return $tokenGet;
    }
}

/**
 * 同步学生（每次最多200条）
 *
 * @param int $schoolid
 * @param int $total 总数
 * @param int $page 当前页
 *
 * @return array
 */
function SyncStudent($schoolid,$total = -1,$page = 1,$IsTask = false){
    if(!CheckXZF($schoolid)){
        $res['msg'] = '尚未开启校智付';
        $res['result'] = false;
        return $res;
    }
    $scinfo = pdo_fetch("SELECT xzf_scid,weid FROM ".GetTableName('schoolset')." WHERE schoolid = '{$schoolid}' ");
    $scid = $scinfo['xzf_scid'];
    $weid = $scinfo['weid'];
    $xzf_info = pdo_fetch("SELECT xzfappid FROM ".GetTableName('set')." WHERE weid = '{$weid}' ");
    $appid = $xzf_info['xzfappid'];
    $tokenGet = CheckToken($weid);
    if($tokenGet['status'] == true){ //获取成功
        $token = $tokenGet['token'];
        if($total < 0){
            if($IsTask){
                $total = pdo_fetchcolumn("SELECT count(id) FROM ".GetTableName('students')." WHERE schoolid = '{$schoolid}' and xzf_needsync = 1 ");
            }else {
                $total = pdo_fetchcolumn("SELECT count(id) FROM (SELECT convert(s.id,UNSIGNED) as id  FROM ".GetTableName('students')." as s LEFT JOIN ".GetTableName('idcard')." as i ON s.id = i.sid WHERE  s.schoolid = '{$schoolid}' AND ( i.pard = 4 or i.pard IS NULL  )  ) as a union ( SELECT convert(fid ,UNSIGNED)as id FROM ".GetTableName('xzfextra'). " WHERE schoolid = '{$schoolid}' and isdone = 0 and type = 2  ) ");
            }
        }
        if($IsTask){
            $studentsinfo = pdo_fetchall("SELECT 0 as exid, convert(s.id,UNSIGNED) as id,s.s_name as studentname, convert(s.bj_id,UNSIGNED) as classid,s.identitycard,s.mobile as phonenumber,s.xzf_datastatus as datastatus,CASE s.sex WHEN 1 THEN 1 ELSE 0 END as sex, CASE i.idcard WHEN NULL THEN 0 ELSE 1 END as cardstatus,i.idcard as cardnumber  FROM ".GetTableName('students')." as s LEFT JOIN ".GetTableName('idcard')." as i ON s.id = i.sid WHERE  s.schoolid = '{$schoolid}' AND ( i.pard = 4 or i.pard IS NULL ) and s.xzf_needsync = 1  LIMIT ".($page - 1 ) * 200 ." , 200 ");
        }else {
            $studentsinfo = pdo_fetchall(" SELECT * FROM (SELECT 0 as exid, convert(s.id,UNSIGNED) as id,s.s_name as studentname, convert(s.bj_id,UNSIGNED) as classid,s.identitycard,s.mobile as phonenumber,s.xzf_datastatus as datastatus,CASE s.sex WHEN 1 THEN 1 ELSE 0 END as sex, CASE i.idcard WHEN NULL THEN 0 ELSE 1 END as cardstatus,i.idcard as cardnumber  FROM ".GetTableName('students')." as s LEFT JOIN    ( SELECT * FROM ".GetTableName('idcard')." WHERE schoolid = '{$schoolid}' and pard = 4)  as i ON s.id = i.sid WHERE   s.schoolid = '{$schoolid}' and s.identitycard != ''  ) as a union ( SELECT id as exid, convert(fid ,UNSIGNED)as id,name as studentname,convert(highid,UNSIGNED)as classid ,identitycard,phonenumber , 3 as datastatus, sex, 1 as cardstatus, 0 as cardnumber FROM ".GetTableName('xzfextra'). " WHERE schoolid = '{$schoolid}' and isdone = 0 and type = 2  )  ORDER BY id ASC LIMIT ".($page - 1 ) * 200 ." , 200 ");

        }
        $temparr = $studentsinfo;
        foreach($studentsinfo as &$value){
            $value['id'] = intval($value['id']);
            $value['classid'] = intval($value['classid']);
        };
        if(empty($studentsinfo)){
            $res['status'] = false;
            $res['msg'] = '无可上传的数据';
            return $res;
        }
        $Datajson = json_encode($studentsinfo,JSON_UNESCAPED_UNICODE );

        $data = array(
            'appid' => str_replace(PHP_EOL, '', base64_encode($appid)),
            'scid' => str_replace(PHP_EOL, '', base64_encode($scid)),
            'datajson' => str_replace(PHP_EOL, '', base64_encode($Datajson)),
            'timestamp' => getMillisecond()
        );
        $sn = GetSN($data,$token);
        $data['sn'] =  $sn;
        $res =  json_decode(xzf_http_post_json('SaveStudentInformation',$data),true);
        if($res['ack'] == 0){
            $stustr = '';
            $exstr = '';
            foreach($temparr as $value){
                if($value['datastatus'] == 1 && $value['exid'] == 0){
                    $stustr .= ",".$value['id'];
                    $stustr = trim($stustr,',');
                }
                if($value['exid']  != 0){ //如果数据来自于extra，则修改isdone
                    $exstr .= ",".$value['exid'];
                    $exstr = trim($exstr,',');
                }
            };
            if(!empty($stustr)){
                pdo_run("UPDATE ".GetTableName('students')." SET `xzf_datastatus` = 2 , `xzf_needsync` = 0  WHERE FIND_IN_SET(id,'{$stustr}') ");
            }
            if(!empty($exstr) && !$IsTask){
                pdo_run("UPDATE ".GetTableName('xzfextra')." SET `isdone` = 1 WHERE FIND_IN_SET(id,'{$exstr}') ");
            }
            $res['status'] = true;
            $res['total'] = intval($total);
            $res['page'] = $page + 1;
        }

        if($res['ack'] == 21) {
            $tokenGet = CheckToken($weid,true);
            $res = SyncStudent($schoolid,$total,$page);
        }
        return $res;
    }else { //失败
        return $tokenGet;
    }

}

/**
 * 同步老师
 *
 * @param int $schoolid
 * @param int $total
 * @param int $page
 *
 * @return void
 */
 function SyncTeachers($schoolid,$total = -1,$page = 1,$IsTask = false){
    if(!CheckXZF($schoolid)){
        $res['msg'] = '尚未开启校智付';
        $res['result'] = false;
        return $res;
    }
    $scinfo = pdo_fetch("SELECT xzf_scid,weid FROM ".GetTableName('schoolset')." WHERE schoolid = '{$schoolid}' ");
    $scid = intval($scinfo['xzf_scid']);
    $weid = $scinfo['weid'];
    $xzf_info = pdo_fetch("SELECT xzfappid FROM ".GetTableName('set')." WHERE weid = '{$weid}' ");
    $appid = $xzf_info['xzfappid'];
    $tokenGet = CheckToken($weid);
    if($tokenGet['status'] == true){ //获取成功
        $token = $tokenGet['token'];
        if($total < 0){
            if($IsTask){
                $total = pdo_fetchcolumn("SELECT t.id as id  FROM ".GetTableName('teachers')." as t LEFT JOIN ".GetTableName('idcard')." as i ON t.id = i.tid  WHERE t.schoolid = '{$schoolid}' and xzf_needsync = 1 ");
            }else {
                $total = pdo_fetchcolumn("SELECT count(id) FROM (SELECT t.id as id  FROM ".GetTableName('teachers')." as t LEFT JOIN ".GetTableName('idcard')." as i ON t.id = i.tid  WHERE t.schoolid = '{$schoolid}' ) as t union  ( SELECT convert(fid ,UNSIGNED)as id FROM ".GetTableName('xzfextra'). " WHERE schoolid = '{$schoolid}' and isdone = 0 and type = 3) ");
            }
        }
        if($IsTask){
            $teachersinfo = pdo_fetchall(" SELECT 0 as exid, t.id as id , t.tname as teachername ,t.idcard as identitycard , t. mobile as phonenumber , i.idcard as cardnumber ,CASE i.idcard WHEN NULL THEN 0 ELSE 1 END as cardstatus ,t.xzf_datastatus as datastatus,t.sex FROM ".GetTableName('teachers')." as t LEFT JOIN ".GetTableName('idcard')." as i ON t.id = i.tid WHERE t.schoolid = '{$schoolid}' and t.idcard != '' and t.xzf_needsync = 1 ORDER BY id ASC LIMIT ".($page - 1 ) * 200 ." , 200 ");
        }else {
            /**
            * 获取数据 teachers表 union extra表
            */
            $teachersinfo = pdo_fetchall("
            SELECT * FROM 
            ( SELECT 
                0 as exid, t.id as id , t.tname as teachername ,t.idcard as identitycard , t. mobile as phonenumber , i.idcard as cardnumber ,CASE i.idcard WHEN NULL THEN 0 ELSE 1 END as cardstatus ,t.xzf_datastatus as datastatus,t.sex 
                FROM ".GetTableName('teachers')." as t 
                LEFT JOIN ".GetTableName('idcard')." as i 
                ON t.id = i.tid  
                WHERE t.schoolid = '{$schoolid}' 
            ) as t 
                union  
            ( SELECT 
                id as exid, convert(fid ,UNSIGNED)as id,name as theachername,identitycard,phonenumber , 3 as datastatus, sex, 1 as cardstatus, 0 as cardnumber 
                FROM ".GetTableName('xzfextra'). " 
                WHERE schoolid = '{$schoolid}' and isdone = 0 and type = 3
            ) 
            ORDER BY id ASC LIMIT ".($page - 1 ) * 200 ." , 200");
        }
     

        $temparr = $teachersinfo;
        foreach($teachersinfo as &$value){
            $value['id'] = intval($value['id']);
            unset($value['exid']);
        };
        $Datajson = json_encode($teachersinfo,JSON_UNESCAPED_UNICODE );
        $data = array(
            'appid' => str_replace(PHP_EOL, '', base64_encode($appid)),
            'scid' => str_replace(PHP_EOL, '', base64_encode($scid)),
            'datajson' => str_replace(PHP_EOL, '', base64_encode($Datajson)),
            'timestamp' => getMillisecond()
        );
        $sn = GetSN($data,$token);
        $data['sn'] =  $sn;
       $res =  json_decode(xzf_http_post_json('SaveTeacherInformation',$data),true);
       
        if($res['ack'] == 0){ //如果提交成功，更改对应数据状态
            $teastr = '';
            $exstr = '';
            foreach($temparr as $value){
                if($value['datastatus'] == '1' && $value['exid'] == '0'){ //如果数据是来自于teachers表，则修改datastatus
                    $teastr .= ",".$value['id'];
                    $teastr = trim($teastr,',');
                }
                if($value['exid']  != 0){ //如果数据来自于extra，则修改isdone
                    $exstr .= ",".$value['exid'];
                    $exstr = trim($exstr,',');
                }
            };
             CommonWriteLog($temparr);
         CommonWriteLog($teastr);
      
            if(!empty($teastr)){
                pdo_run("UPDATE ".GetTableName('teachers')." SET `xzf_datastatus` = 2 ,`xzf_needsync` = 0 WHERE FIND_IN_SET(id,'{$teastr}') ");
            }
            if(!empty($exstr) && !$IsTask){
                pdo_run("UPDATE ".GetTableName('xzfextra')." SET `isdone` = 1 WHERE FIND_IN_SET(id,'{$exstr}') ");
            }
            $res['status'] = true;
            $res['total'] = intval($total);
            $res['page'] = $page + 1;
         }
        return $res;
    }else { //失败
        return $tokenGet;
    }
}

/**
 * 同步卡数据
 *
 * @param [type] $schoolid
 * @param int $total
 * @param int $page
 *
 * @return void
 */
function SyncCard($schoolid,$total = -1,$page = 1){
    if(!CheckXZF($schoolid)){
        $res['msg'] = '尚未开启校智付';
        $res['result'] = false;
        return $res;
    }
    $scinfo = pdo_fetch("SELECT xzf_scid,weid FROM ".GetTableName('schoolset')." WHERE schoolid = '{$schoolid}' ");
    $scid = intval($scinfo['xzf_scid']);
    $weid = $scinfo['weid'];
    $xzf_info = pdo_fetch("SELECT xzfappid FROM ".GetTableName('set')." WHERE weid = '{$weid}' ");
    $appid = $xzf_info['xzfappid'];
    $tokenGet = CheckToken($weid);
    if($tokenGet['status'] == true){ //获取成功
        $token = $tokenGet['token'];
        if($total < 0){
            $total = pdo_fetchcolumn("SELECT count(fid)   FROM ".GetTableName('xzfextra')." WHERE schoolid = '{$schoolid}' and type = 4 and isdone = 0 ORDER BY createtime ASC");
        }
        if($total <= 0){
            $res['status'] = true;
            $res['msg'] = '所有考勤卡均已同步，无更新数据';
            $res['total'] = $total;
            return $res;
        }

        $cardsinfo = pdo_fetchall("SELECT id as exid, fid as userid,oldcardnumber,newcardnumber as cardnumber,card_optype as optype,createtime ,usertype FROM ".GetTableName('xzfextra')." WHERE schoolid = '{$schoolid}' and type = 4 and isdone = 0 ORDER BY createtime ASC LIMIT ".($page - 1 ) * 200 ." , ".$page * 200);
        $temparr = $cardsinfo;
        foreach($cardsinfo as &$value){
            $value['userid'] = intval($value['userid']);
            unset($value['exid']);
            unset($value['createtime']);
        };

        $Datajson = json_encode($cardsinfo,JSON_UNESCAPED_UNICODE);
        $data = array(
            'appid' => str_replace(PHP_EOL, '', base64_encode($appid)),
            'scid' => str_replace(PHP_EOL, '', base64_encode($scid)),
            'datajson' => str_replace(PHP_EOL, '', base64_encode($Datajson)),
            'timestamp' => getMillisecond()
        );
        $sn = GetSN($data,$token);
        $data['sn'] =  $sn;
        $res =  json_decode(xzf_http_post_json('SaveCardInformation',$data),true);
        if($res['ack'] == 0){
            $exstr = '';
            foreach($temparr as $value){
                    $exstr .= ",".$value['exid'];
                    $exstr = trim($exstr,',');
            };
            pdo_run("UPDATE ".GetTableName('xzfextra')." SET `isdone` = 1 WHERE FIND_IN_SET(id,'{$exstr}') ");
            $res['status'] = true;
            $res['total'] = intval($total);
            $res['page'] = $page + 1;
       }
        return $res;
    }else { //失败
        return $tokenGet;
    }
}

/** 
 * 
 * 关于删除学生，老师，班级相关操作
 * $type(类型1删除班级2删除学生3删除老师)
 * 
 */
function setXzfExtra($id,$type){
    if($type == 1){ //班级操作
        $classify = pdo_fetchAll("SELECT sid as id,sname as classname,3 as datastatus,parentid as gradeid,weid,schoolid FROM ".GetTableName('classify')." WHERE sid = '{$id}' ");
        if(!CheckXZF($classify['schoolid'])){
            $res['msg'] = '尚未开启校智付';
            $res['result'] = false;
            return $res;
        }
        $scinfo = pdo_fetch("SELECT xzf_scid,weid FROM ".GetTableName('schoolset')." WHERE schoolid = '{$classify['schoolid']}' ");
        $scid = intval($scinfo['xzf_scid']);
        $weid = $scinfo['weid'];
        $xzf_info = pdo_fetch("SELECT xzfappid FROM ".GetTableName('set')." WHERE weid = '{$weid}' ");
        $appid = $xzf_info['xzfappid'];
        $tokenGet = CheckToken($weid);
        $token = $tokenGet['token'];
        $classify[0]['id'] = intval($classify[0]['id']);
        $classify[0]['gradeid'] = intval($classify[0]['gradeid']);
        $Datajson = json_encode($classify,JSON_UNESCAPED_UNICODE);
        $data = array(
            'appid' => str_replace(PHP_EOL, '', base64_encode($appid)),
            'scid' => str_replace(PHP_EOL, '', base64_encode($scid)),
            'datajson' => str_replace(PHP_EOL, '', base64_encode($Datajson)),
            'timestamp' => getMillisecond()
        );
        $sn = GetSN($data,$token);
        $data['sn'] =  $sn;
        $res =  json_decode(xzf_http_post_json('SaveClassInformation',$data),true);
        
    }

    if($type == 2){ //学生操作
        $student = pdo_fetch("SELECT weid,schoolid,bj_id,s_name,identitycard,mobile,sex,xzf_datastatus FROM ".GetTableName('students')." WHERE id = '{$id}' ");
        if(!CheckXZF($student['schoolid'])){
            $res['msg'] = '尚未开启校智付';
            $res['result'] = false;
            return $res;
        }
        $scinfo = pdo_fetch("SELECT xzf_scid,weid FROM ".GetTableName('schoolset')." WHERE schoolid = '{$student['schoolid']}' ");
        $scid = $scinfo['xzf_scid'];
        $weid = $scinfo['weid'];
        $xzf_info = pdo_fetch("SELECT xzfappid FROM ".GetTableName('set')." WHERE weid = '{$weid}' ");
        $appid = $xzf_info['xzfappid'];
        $tokenGet = CheckToken($weid);

        $studentsinfo = pdo_fetchall("SELECT convert(s.id,UNSIGNED) as id,s.s_name as studentname, convert(s.bj_id,UNSIGNED) as classid,s.identitycard,s.mobile as phonenumber,3 as datastatus,CASE s.sex WHEN 1 THEN 1 ELSE 0 END as sex, CASE i.idcard WHEN NULL THEN 0 ELSE 1 END as cardstatus,i.idcard as cardnumber  FROM ".GetTableName('students')." as s LEFT JOIN ".GetTableName('idcard')." as i ON s.id = i.sid WHERE  s.schoolid = '{$student['schoolid']}' AND ( i.pard = 4 or i.pard IS NULL ) ");
        $studentsinfo[0]['id'] = intval($studentsinfo[0]['id']);
        $studentsinfo[0]['classid'] = intval($studentsinfo[0]['classid']);
        $Datajson = json_encode($studentsinfo,JSON_UNESCAPED_UNICODE );
        $data = array(
            'appid' => str_replace(PHP_EOL, '', base64_encode($appid)),
            'scid' => str_replace(PHP_EOL, '', base64_encode($scid)),
            'datajson' => str_replace(PHP_EOL, '', base64_encode($Datajson)),
            'timestamp' => getMillisecond()
        );
        $sn = GetSN($data,$token);
        $data['sn'] =  $sn;
        $res =  json_decode(xzf_http_post_json('SaveStudentInformation',$data),true);

    }

    if($type == 3){ //老师操作
        $teacher = pdo_fetch("SELECT weid,schoolid,tname,idcard,mobile,sex,xzf_datastatus FROM ".GetTableName('teachers')." WHERE id = '{$id}' ");
        if(!CheckXZF($teacher['schoolid'])){
            $res['msg'] = '尚未开启校智付';
            $res['result'] = false;
            return $res;
        }
        $scinfo = pdo_fetch("SELECT xzf_scid,weid FROM ".GetTableName('schoolset')." WHERE schoolid = '{$teacher['schoolid']}' ");
        $scid = $scinfo['xzf_scid'];
        $weid = $scinfo['weid'];
        $xzf_info = pdo_fetch("SELECT xzfappid FROM ".GetTableName('set')." WHERE weid = '{$weid}' ");
        $appid = $xzf_info['xzfappid'];
        $tokenGet = CheckToken($weid);
        $teachersinfo = pdo_fetchall(" SELECT t.id as id , t.tname as teachername ,t.idcard as identitycard , t. mobile as phonenumber , i.idcard as cardnumber ,CASE i.idcard WHEN NULL THEN 0 ELSE 1 END as cardstatus ,3 as datastatus,t.sex FROM ".GetTableName('teachers')." as t LEFT JOIN ".GetTableName('idcard')." as i ON t.id = i.tid WHERE t.schoolid = '{$teacher['schoolid']}'");
        $teachersinfo[0]['id'] = intval($teachersinfo[0]['id']);
        $Datajson = json_encode($teachersinfo,JSON_UNESCAPED_UNICODE );
        $data = array(
            'appid' => str_replace(PHP_EOL, '', base64_encode($appid)),
            'scid' => str_replace(PHP_EOL, '', base64_encode($scid)),
            'datajson' => str_replace(PHP_EOL, '', base64_encode($Datajson)),
            'timestamp' => getMillisecond()
        );
        $sn = GetSN($data,$token);
        $data['sn'] =  $sn;
        $res =  json_decode(xzf_http_post_json('SaveTeacherInformation',$data),true);
    }

}
// function setXzfExtra($id,$type){
//     if($type == 1){ //班级操作
//         $classify = pdo_fetch("SELECT weid,schoolid,parentid,sname,xzf_datastatus FROM ".GetTableName('classify')." WHERE sid = '{$id}' ");
//         if(!CheckXZF($classify['schoolid'])){
//             $res['msg'] = '尚未开启校智付';
//             $res['result'] = false;
//             return $res;
//         }
//         if($classify['xzf_datastatus'] == 2){
//             $data = array(
//                 'weid' => $classify['weid'],
//                 'schoolid' => $classify['schoolid'],
//                 'type' => $type,
//                 'fid' => $id,
//                 'highid' => $classify['parentid'],
//                 'name' => $classify['sname'],
//                 'datastatus' => 3,
//                 'createtime'=>time(), 
//             );
//             pdo_insert(GetTableName('xzfextra',false),$data);
//         }
        
//     }

//     if($type == 2){ //学生操作
//         $student = pdo_fetch("SELECT weid,schoolid,bj_id,s_name,identitycard,mobile,sex,xzf_datastatus FROM ".GetTableName('students')." WHERE id = '{$id}' ");
//         if(!CheckXZF($student['schoolid'])){
//             $res['msg'] = '尚未开启校智付';
//             $res['result'] = false;
//             return $res;
//         }
//         if($student['xzf_datastatus'] == 2){
//             $data = array(
//                 'weid' => $student['weid'],
//                 'schoolid' => $student['schoolid'],
//                 'type' => $type,
//                 'fid' => $id,
//                 'highid' => $student['bj_id'],
//                 'name' => $student['s_name'],
//                 'identitycard' => $student['identitycard'],
//                 'phonenumber' => $student['mobile'],
//                 'sex' => $student['sex'], 
//                 'datastatus' => 3,
//                 'createtime'=>time(), 
//             );
//             pdo_insert(GetTableName('xzfextra',false),$data);
//         }
//     }

//     if($type == 3){ //老师操作
//         $teacher = pdo_fetch("SELECT weid,schoolid,tname,idcard,mobile,sex,xzf_datastatus FROM ".GetTableName('teachers')." WHERE id = '{$id}' ");
//         if(!CheckXZF($teacher['schoolid'])){
//             $res['msg'] = '尚未开启校智付';
//             $res['result'] = false;
//             return $res;
//         }
//         if($teacher['xzf_datastatus'] == 2){
//             $data = array(
//                 'weid' => $teacher['weid'],
//                 'schoolid' => $teacher['schoolid'],
//                 'type' => $type,
//                 'fid' => $id,
//                 'name' => $teacher['tname'],
//                 'identitycard' => $teacher['idcard'],
//                 'phonenumber' => $teacher['mobile'],
//                 'sex' => $teacher['sex'], 
//                 'datastatus' => 3,
//                 'createtime'=>time(), 
//             );
//             pdo_insert(GetTableName('xzfextra',false),$data);
//         }
        
//     }

// }

/** 
 * 
 * 关于卡绑定相关操作
 * 
 */
function setXzfKaExtra($id,$idcard,$usertype){
    //获取卡信息
    $cardinfo = pdo_fetch("SELECT weid,schoolid FROM ".GetTableName('idcard')." WHERE id = :id LIMIT 1 ",array(':id'=>$id));
    if(!CheckXZF($cardinfo['schoolid'])){
        $res['msg'] = '尚未开启校智付';
        $res['result'] = false;
        return $res;
    }
    $xzfextra = pdo_fetch("SELECT * FROM ".GetTableName('xzfextra')." WHERE fid = :fid AND type = :type AND usertype = :usertype ORDER BY id DESC LIMIT 1 ",array(':fid'=>$id,':type'=>4,':usertype'=>$usertype));
    $data = array(
        'weid' => $cardinfo['weid'],
        'schoolid' => $cardinfo['schoolid'],
        'type' => 4,
        'fid' => $id,
        'newcardnumber' => $idcard,
        'createtime'=>time(), 
        'usertype'=>$usertype, 
    );
    if(empty($xzfextra)){ //直接新增绑定卡
        $data['card_optype'] = 1;
        pdo_insert(GetTableName('xzfextra',false),$data);
    }else{
        if($xzfextra['card_optype'] == 1 || $xzfextra['card_optype'] == 3 || $xzfextra['card_optype'] == 4){
            if($xzfextra['newcardnumber'] != $idcard){
                $data['card_optype'] = 4;
                $data['oldcardnumber'] = $xzfextra['newcardnumber'];
                pdo_insert(GetTableName('xzfextra',false),$data);
            }
        }else{
            if($xzfextra['newcardnumber'] == $idcard){ //解卦
                $data['card_optype'] = 3;
                pdo_insert(GetTableName('xzfextra',false),$data);
            }else{
                $data['card_optype'] = 4;
                $data['oldcardnumber'] = $xzfextra['newcardnumber'];
                pdo_insert(GetTableName('xzfextra',false),$data);
            }
        }
    }
}

/** 
 * 
 * 关于卡绑定相关操作
 * 
 */
function setJbXzfKaExtra($id,$idcard,$usertype){
    //获取卡信息
    $cardinfo = pdo_fetch("SELECT weid,schoolid FROM ".GetTableName('idcard')." WHERE id = :id LIMIT 1 ",array(':id'=>$id));
    if(!CheckXZF($cardinfo['schoolid'])){
        $res['msg'] = '尚未开启校智付';
        $res['result'] = false;
        return $res;
    }
    $data = array(
        'weid' => $cardinfo['weid'],
        'schoolid' => $cardinfo['schoolid'],
        'type' => 4,
        'fid' => $id,
        'newcardnumber' => $idcard,
        'createtime'=>time(), 
        'usertype'=>$usertype, 
        'card_optype'=>2, 
    );
    pdo_insert(GetTableName('xzfextra',false),$data);
}

/** 
 * 
 * 修改年级班级学生老师中的needsync
 * 
 */

function setXzfNeedsync($id,$table){
    $data = array('xzf_needsync'=>1);
    if($table == 'classify'){
        pdo_update(GetTableName("{$table}",false),$data,array('sid'=>$id));
    }else{
        pdo_update(GetTableName("{$table}",false),$data,array('id'=>$id));
    }
}
?>