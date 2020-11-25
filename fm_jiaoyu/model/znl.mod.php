<?php
/*
 * @Discription:  重庆正能量设备 mod
 * @Author: Hannibal·Lee
 * @Date: 2020-05-28 11:09:18
 * @LastEditTime: 2020-11-12 11:43:04
 */
// define('ZNL_BASEURL', "http://test.frp.hncnbot.com"); //开发调试环境服务地址
 define('ZNL_BASEURL', "http://server.hncnbot.com"); //线上环境服务地址



function znl_http_post_json($url, $post_data, $IsArray = false,$schoolid)
{
    // $filename =  MODULE_ROOT . '/model/mc.config.php';
    // require $filename;

    if (empty($url) || empty($post_data)) {
        return false;
    }

    //TODO:采用真实的ID

    $App = pdo_fetch("SELECT znl_appid,znl_appsecret FROM ".GetTableName('schoolset')." WHERE schoolid = '{$schoolid}' ");
    $AppKey = $App['znl_appid'];
    $AppSecret = $App['znl_appsecret'];
    if(empty($AppKey) || empty($AppSecret)){
        return;
    }
    $Nonce = rand(10000, 1000000000);
    $CurTime = time();
    $postUrl = ZNL_BASEURL . $url;
    $CheckSum = strtolower(sha1('' . $AppSecret . $Nonce . $CurTime, FALSE));
    $curlPost = json_encode($post_data);
    $ch = curl_init();//初始化curl
    curl_setopt($ch, CURLOPT_URL, $postUrl);//抓取指定网页
    curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
    curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "appKey:{$AppKey}",
        "Nonce:{$Nonce}",
        "CurTime:{$CurTime}",
        "CheckSum:{$CheckSum}",
        "Content-Type:application/json"

    ));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
    $data = curl_exec($ch);//运行curl
    curl_close($ch);
    return $data;
}

function znl_http_get_json($url,$get_data,$schoolid,$IsWithParam = false){
    if (empty($url) || empty($get_data)) {
        return false;
    }

    $App = pdo_fetch("SELECT znl_appid,znl_appsecret FROM ".GetTableName('schoolset')." WHERE schoolid = '{$schoolid}' ");
    $AppKey = $App['znl_appid'];
    $AppSecret = $App['znl_appsecret'];
    $Nonce = rand(10000, 1000000000);
    $CurTime = time();
    $postUrl = ZNL_BASEURL . $url;
    $CheckSum = strtolower(sha1('' . $AppSecret . $Nonce . $CurTime, FALSE));



    foreach ( $get_data as $k => $v )
    {
        $o.= $k."=" . urlencode( $v ). "&" ;
    }
    $curlGet = substr($o,0,-1);
    if(!$IsWithParam){
        $curlGet = '?'.$curlGet;
    }
    $ch = curl_init();//初始化curl
    curl_setopt($ch, CURLOPT_URL, $postUrl.$curlGet);//抓取指定网页
    curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "appKey:{$AppKey}",
        "Nonce:{$Nonce}",
        "CurTime:{$CurTime}",
        "CheckSum:{$CheckSum}",
        "Content-Type:application/json"
    ));
    $data = curl_exec($ch);//运行curl
    curl_close($ch);
    return $data;

}


 


/**
 * 同步学生信息（按班级）
 *
 * @param [type] $classid
 *
 *
 * @return void
 */
function znlMutiStuInfoByClass($classid)
{
    $StuList = pdo_fetchall("SELECT s.id as studentNo,s.s_name as studentName,c.spic,FROM_UNIXTIME(s.birthdate,'%Y-%m-%d') as birthday,CASE s.sex WHEN 1 THEN 'M' ELSE 'F' END gender FROM " . GetTableName('students') . " as s LEFT JOIN " . GetTableName('idcard') . " as c ON s.id = c.sid and c.pard = 1 WHERE s.bj_id = '{$classid}'   ");
    $classInfo = pdo_fetch("SELECT sname,schoolid FROM " . GetTableName('classify') . " WHERE sid = '{$classid}' ");
    $schoolInfo = pdo_fetch("SELECT title FROM " . GetTableName('index') . " WHERE id = '{$classInfo['schoolid']}' ");
    if(!empty($StuList)){
        foreach ($StuList as $key => $value) {
            $StuList[$key]['studentImg'] = tomedia($value['spic']);
            $StuList[$key]['deptNo'] = $classInfo['schoolid'];
            $StuList[$key]['deptName'] = $schoolInfo['title'];
            $StuList[$key]['className'] = $classInfo['sname'];
            unset($StuList[$key]['spic']);
        }
        $res = json_decode(znl_http_post_json('/cnbots/rest/kindergarten/openapi/user/saveStuInfos', $StuList, true,$classInfo['schoolid']), true);
        CommonWriteLog("同步整班学生",'cnStuSync','cn');
        CommonWriteLog($StuList,'cnStuSync','cn');
        CommonWriteLog("返回结果",'cnStuSync','cn');
        CommonWriteLog($res,'cnStuSync','cn');
        return $res;
    }else {
        $result['resultCode'] = '1';
        $result['resultStr'] = '当前班级无学生';
        return $result;
    }
  
}

/**
 * 同步单个学生信息
 *IVLTRNOKUNHJGHUB
 * @param [type] $sid
 *  @param [type] $opType 115 新增/116 删除/ 117 修改
 *
 * @return void
 */
function znlSingleStuInfo($sid, $opType)
{
    if(!keep_MC()){
        return;
    }
    $StuInfo = pdo_fetch("SELECT s.id as studentNo,s.s_name as studentName,c.spic,FROM_UNIXTIME(s.birthdate,'%Y-%m-%d') as birthday,CASE s.sex WHEN 1 THEN 'M' ELSE 'F' END gender,s.bj_id FROM " . GetTableName('students') . " as s LEFT JOIN " . GetTableName('idcard') . " as c ON s.id = c.sid and c.pard = 1 WHERE s.id = '{$sid}'  ");
    $classInfo = pdo_fetch("SELECT sname,schoolid FROM " . GetTableName('classify') . " WHERE sid = '{$StuInfo['bj_id']}' ");
    $schoolInfo = pdo_fetch("SELECT title FROM " . GetTableName('index') . " WHERE id = '{$classInfo['schoolid']}' ");
    $StuInfo['studentImg'] = tomedia($StuInfo['spic']);
    $StuInfo['deptNo'] = $classInfo['schoolid'];
    $StuInfo['deptName'] = $schoolInfo['title'];
    $StuInfo['className'] = $classInfo['sname'];
    $StuInfo['opType'] = $opType;
    unset($StuInfo['spic']);
    unset($StuInfo['bj_id']);
    $res = json_decode(znl_http_post_json('/cnbots/rest/kindergarten/openapi/user/saveStuInfo', $StuInfo, true,$classInfo['schoolid']), true);
    CommonWriteLog("同步单个学生",'cnStuSync','cn');
    CommonWriteLog($StuInfo,'cnStuSync','cn');
    CommonWriteLog("返回结果",'cnStuSync','cn');
    CommonWriteLog($res,'cnStuSync','cn');
    return $res;
}


function znlGetReport($schoolid,$sid = 0,$startDate = '',$endDate = '',$type = 0){
    $sendIunfo = array(
        'deptNo' => $schoolid,
    );
    if(!empty($sid)){
        $sendIunfo['studentNo'] = $sid;
    }
    if(!empty($type)){
        $sendIunfo['healthRepotType'] = $type;
    }
    if(!empty($startDate) && !empty($endDate)){
        $sendIunfo['beginDate'] = $startDate;
        $sendIunfo['endDate'] = $endDate;
    }
    $bjid = pdo_fetch("SELECT bj_id FROM ".GetTableName('students')." WHERE schoolid = '{$schoolid}' AND id = '{$sid}' ")['bj_id'];
    $res = json_decode(znl_http_get_json('/cnbots/rest/kindergarten/openapi/user/selectStudentHealthReport', $sendIunfo, $schoolid), true);
    if($res['resultCode'] == 0){
        foreach ($res['result'] as $value) {
            $weid = pdo_fetch("SELECT weid FROM ".GetTableName('index')." WHERE id = '{$value['deptNo']}'")['weid'];
            $hasreport = pdo_fetch("SELECT id FROM ".GetTableName('mcreportlist')." WHERE schoolid = '{$value['deptNo']}' AND sid = '{$value['studentNo']}' AND gettime = '{$value['createTime']}' AND type = '{$value['healthRepotType']}' ");
            if(empty($hasreport)){
                $reportData = array(
                    'weid' => $weid,
                    'schoolid' => $value['deptNo'],
                    'sid' => $value['studentNo'],
                    'bjid' => $bjid,
                    'type' => $value['healthRepotType'],
                    'semestertype' => $value['semesterType'],
                    'cnbotssemesterid' => $value['cnbotsSemesterId'],
                    'title' => $value['healthRepotTitle'],
                    'year' => $value['currentYear'],
                    'month' => $value['currentMonth'],
                    'gettime' => $value['createTime'],
                    'createtime' => time(),
                    'content' => json_encode($value),
                );
                pdo_insert(GetTableName('mcreportlist',false),$reportData);
                $res['status'] = true;
            }else{
                $res['status'] = false;
            }
            // CommonWriteLog($value['studentNo'].'//'.$value['createTime'],'ababab');
        }
    }
    return $res;
}


//xq_score
function znlSyncSemesterMuti($schoolid){
    $list = pdo_fetchall("SELECT * FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and type = 'xq_score' ");
}

//同步单个学期
function znlSyncSemester($id,$schoolid){
    $info = pdo_fetch("SELECT * FROM ".GetTableName('classify')." WHERE sid = '{$id}' ");
    $SendData = [];
    $SendData['opType'] = 115;
    $SendData['deptNo'] = $info['schoolid'];
    $SendData['id'] = $info['cn_yearid'];
    $SendData['schoolYearStr'] = date("Y",$info['sd_start']);
    $SendData['schoolYearEnd'] = date("Y",$info['sd_end']);
    $SendData['lastSemesterStr'] = date("Y-m-d",$info['sd_start']);
    $SendData['lastSemesterEnd'] = date("Y-m-d",$info['firstlast']);
    $SendData['nextSemesterStr'] = date("Y-m-d",$info['laststart']);
    $SendData['nextSemesterEnd'] = date("Y-m-d",$info['sd_end']);

    $res = json_decode(znl_http_post_json('/cnbots/rest/kindergarten/openapi/depart/saveWechatSemester', $SendData, true,$schoolid), true);
    CommonWriteLog("同步单个学期",'cnSemesterSync','cn');
    CommonWriteLog($SendData,'cnSemesterSync','cn');
    CommonWriteLog("返回结果",'cnSemesterSync','cn');
    CommonWriteLog($res,'cnSemesterSync','cn');
    if($res['resultCode'] != 0){
        $result['status'] = false;
        $result['msg'] = $res['resultStr'];
    }else {
        $result['status'] = true;
        $cn_yearid = $res['result']['id'];
        pdo_update(GetTableName('classify',false),array('cn_yearid'=>$cn_yearid),array('sid'=>$id));
        $result['msg'] = "同步学年成功";
    }
    return json_encode($result);
    //return $res;
}
