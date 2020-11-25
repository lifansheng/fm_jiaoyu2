<?php

/**
 * 微教育模块
 *
 * @author Hannibal·Lee
 */

//获取token以及其他信息 返回结果：result 1/0
function GetWTSetInfo($schoolid,$weid,$time){
    $NowToken = pdo_fetch("SELECT id,wt_appid,wt_appkey,wt_appsecret,wt_token,wt_token_time,wt_version FROM " . GetTableName('schoolset') . " WHERE schoolid = '{$schoolid}' and weid = '{$weid}' ");
    $result = [];
    $status = false;
    //如果token即将过期
    if($time - $NowToken['wt_token_time'] >= 86000){
        //调取鉴权   md5
        $url = "http://gs-api.uface.uni-ubi.com/{$NowToken['wt_version']}/{$NowToken['wt_appid']}/auth";
        $param['appId'] = $NowToken['wt_appid'];
        $param['appKey'] = $NowToken['wt_appkey'];
        $param['timestamp'] = $time;
        $sign_before = $NowToken['wt_appkey'].$time.$NowToken['wt_appsecret'];
        $sign = md5($sign_before);
        $param['sign'] = $sign;

        $res = callInterfaceCommon($url,'POST',$param);
        if($res['result'] == '1'){
            $insert_token = array(
               'wt_token' => $res['data'],
               'wt_token_time' =>  $time
            );
            pdo_update(GetTableName('schoolset',false),$insert_token,array('id'=>$NowToken['id']));
            $result['token'] = $res['data'];
            $status = true;
        }else{
            $status = false;
            $result['code'] = $res['code'];
        }
    }else{
        $result['token'] = $NowToken['wt_token'];
        $status = true;
    }
    if($status == true) {
        $result['result']  = 1;
        $result['code']    = 'TOK_SUS_000';
        $result['msg']     = 'get token done';
        $result['appId']   = $NowToken['wt_appid'];
        $result['appKey']  = $NowToken['wt_appkey'];
        $result['token']   = $NowToken['wt_token'];
        $result['version'] = $NowToken['wt_version'];
    }elseif($status == false){
        $result['result'] = 0;
        $result['msg'] = 'create token fail';
    }
    return $result;
}

//人员操作 新增 更新 删除 更新卡
function personAction($schoolid,$weid,$time,$param,$action,$stu_or_tea='stu'){
    $wtSet = GetWTSetInfo($schoolid,$weid,$time);
    if($wtSet['result'] == '0'){
        return $wtSet;
    }else{
        $post_data = [];
        $url = '';
        $type = '';
        $idcardNo = '';
        if($stu_or_tea == 'stu'){
            $idcardNo = 'S'.$param['idcardNo'];
        }elseif($stu_or_tea == 'tea'){
            $idcardNo = 'T'.$param['idcardNo'];
        }elseif($stu_or_tea == 'idcard'){
            $idcardNo = 'C'.$param['idcardNo'];
        }
        switch ($action){
            case "insert" :
                $post_data = array(
                    'appId'    => $wtSet['appId'],
                    'token'    => $wtSet['token'],
                    'name'     => $param['name'],
                    'idcardNo' => $idcardNo,
                    'idNo'  => $param['idNo']
                );
                $type = 'POST';
                $url = "http://gs-api.uface.uni-ubi.com/{$wtSet['version']}/{$wtSet['appId']}/person";
                break;
            case "idcard_update" :
                $post_data = array(
                    'appId' => $wtSet['appId'],
                    'token' => $wtSet['token'],
                    'guid'  => $param['guid'],
                    'idNo'  => $param['idNo']
                );
                $type = 'PUT';
                $url = "http://gs-api.uface.uni-ubi.com/{$wtSet['version']}/{$wtSet['appId']}/person/{$param['guid']}";
                break;
            case "update" :
                $post_data = array(
                    'appId'    => $wtSet['appId'],
                    'token'    => $wtSet['token'],
                    'name'     => $param['name'],
                    'guid'     => $param['guid'],
                    'idcardNo' => $idcardNo,
                    'idNo'  => $param['idNo']
                );
                $type = 'PUT';
                $url = "http://gs-api.uface.uni-ubi.com/{$wtSet['version']}/{$wtSet['appId']}/person/{$param['guid']}";
                break;
            case "delete":
                $post_data = array(
                    'appId' => $wtSet['appId'],
                    'token' => $wtSet['token'],
                    'guid'  => $param['guid']
                );
                $type = 'DELETE';
                $url = "http://gs-api.uface.uni-ubi.com/{$wtSet['version']}/{$wtSet['appId']}/person/{$param['guid']}";
                break;
        }
        $res_json = callInterfaceCommon($url,$type,$post_data);
        return $res_json;
    }
}

//设备操作 新增 更新  删除
function DeviceAction($schoolid,$weid,$time,$deviceKey,$name,$action){
    $wtSet = GetWTSetInfo($schoolid,$weid,$time);
    if($wtSet['result'] == '0'){
        return $wtSet;
    }else{
        $post_data = array(
            'appId' => $wtSet['appId'],
            'token' => $wtSet['token'],
            'deviceKey' => $deviceKey,
        );

        $url = '';
        $type = '';
        switch ($action){
            case "insert":
                $post_data['name'] = $name;
                $type = 'POST';
                $url = "http://gs-api.uface.uni-ubi.com/{$wtSet['version']}/{$wtSet['appId']}/device";
                break;
            case "update" :
                $post_data['name'] = $name;
                $type = 'PUT';
                $url = "http://gs-api.uface.uni-ubi.com/{$wtSet['version']}/{$wtSet['appId']}/device/{$deviceKey}";
                break;
            case "delete":
                $type = 'DELETE';
                $url = "http://gs-api.uface.uni-ubi.com/{$wtSet['version']}/{$wtSet['appId']}/device/{$deviceKey}";
                break;
        }
        $res_json = callInterfaceCommon($url,$type,$post_data);
        return $res_json;
    }
}

//人员批量授权设备
function People2Device($schoolid,$weid,$time,$guid){
   $maclist =  pdo_fetchall("SELECT id,macid FROM " . GetTableName('checkmac') . " WHERE schoolid = '{$schoolid}' and weid = '{$weid}' and macname = 12 ");
    $deviceKeys_temp = '';
    foreach ($maclist as $key => $value){
        $deviceKeys_temp .=$value['macid'].',';
    }

    $deviceKeys = trim($deviceKeys_temp,',');
    $wtSet = GetWTSetInfo($schoolid,$weid,$time);
    if($wtSet['result'] == '0'){
        return $wtSet;
    }else {
        $post_data = array(
            'appId'      => $wtSet['appId'],
            'token'      => $wtSet['token'],
            'guid'       => $guid,
            'deviceKeys' => $deviceKeys,
        );
        $url = "http://gs-api.uface.uni-ubi.com/{$wtSet['version']}/{$wtSet['appId']}/person/{$guid}/devices";
        $res = callInterfaceCommon($url, 'POST', $post_data);
        return $res;
    }
}

//人员照片授权
function PersonFace($schoolid,$weid,$time,$guid,$imgurl){
    $wtSet = GetWTSetInfo($schoolid,$weid,$time);
    if($wtSet['result'] == '0'){
        return $wtSet;
    }else{
        $post_data = array(
            'appId' => $wtSet['appId'],
            'token' => $wtSet['token'],
            'guid' => $guid,
            'imageUrl' =>$imgurl
        );
        $url = "http://gs-api.uface.uni-ubi.com/{$wtSet['version']}/{$wtSet['appId']}/person/{$guid}/face/imageUrl/valid";
        $res = callInterfaceCommon($url,'POST',$post_data);
        return $res;
    }
}

//人员照片删除 返回结果： result 1/0
function DeleteFace($schoolid,$weid,$time,$guid,$photo_guid){
    $wtSet = GetWTSetInfo($schoolid,$weid,$time);
    if($wtSet['result'] == '0'){
        return $wtSet;
    }else{
        $post_data = array(
            'appId'      => $wtSet['appId'],
            'token'      => $wtSet['token'],
            'guid'       => $photo_guid,
            'personGuid' => $guid
        );
        $url = "http://gs-api.uface.uni-ubi.com/{$wtSet['version']}/{$wtSet['appId']}/person/{$guid}/face/{$photo_guid}";
        $res = callInterfaceCommon($url, 'DELETE', $post_data);
        return $res;
    }
}

//设备批量授权人员 返回结果： result 1/0
function DevicePeople($schoolid,$weid,$time,$deviceKey){
    $students = pdo_fetchall("SELECT id,guid FROM " . GetTableName('students') . " WHERE schoolid = '{$schoolid}' and weid = '{$weid}' and guid != 0 ");
    $teachers = pdo_fetchall("SELECT id,guid FROM " . GetTableName('teachers') . " WHERE schoolid = '{$schoolid}' and weid = '{$weid}' and guid != 0 ");
    $personGuids_temp = '';
    foreach ($students as $key => $value){
        $personGuids_temp .=$value['guid'].',';
    }
    foreach ($teachers as $key_t => $value_t){
        $personGuids_temp .=$value_t['guid'].',';
    }
    $personGuids = trim($personGuids_temp,',');
    $wtSet = GetWTSetInfo($schoolid,$weid,$time);
    if($wtSet['result'] == '0'){
        return $wtSet;
    }else{
        $post_data = array(
            'appId'       => $wtSet['appId'],
            'token'       => $wtSet['token'],
            'deviceKey'   => $deviceKey,
            'personGuids' => $personGuids,
        );
        $url = "http://gs-api.uface.uni-ubi.com/{$wtSet['version']}/{$wtSet['appId']}/device/{$deviceKey}/people";
        $res = callInterfaceCommon($url, 'POST', $post_data);
        return $res;
    }
}

//POST、GET、PUT、DELETE请求 返回结果： result 1/0
function callInterfaceCommon($URL,$type,$params){
    if (empty($URL) || empty($params) || empty($type)) {
        return false;
    }
    $o = "";
    foreach ( $params as $k => $v )
    {
        $o.= "$k=" . urlencode( $v ). "&" ;
    }
    $post_data = substr($o,0,-1);
    $postUrl = $URL;
    $curlPost = $post_data;
    $ch = curl_init();
    curl_setopt ($ch, CURLOPT_URL, $postUrl); //发贴地址
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
    switch ($type){
        case "GET" :
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            break;
        case "POST":
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
            break;
        case "PUT" :
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
            break;
        case "DELETE":
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
            break;
    }
    $file_contents = curl_exec($ch);//获得返回值
    curl_close($ch);
    if($file_contents == false){
        $res['result'] = 0;
        $res['code'] = 'FM_CURL_ERROR';
        $res['msg'] = 'request failed';
    }else{
        $res = json_decode($file_contents,true);
    }
    return $res;
}

//检查返回结果
function CheckWtReturnCode($code){
    $return                = [];
    $return['GS_EXP-100']  = '接口授权appKey错误';
    $return['GS_EXP-101']  = '接口授权sign错误';
    $return['GS_EXP-102']  = 'token错误或失效';
    $return['GS_EXP-200']  = '人员录入失败';
    $return['GS_EXP-201']  = '人员查询失败';
    $return['GS_EXP-202']  = '人员创建name为空';
    $return['GS_EXP-203']  = '人员-设备授权deviceKeys为空';
    $return['GS_EXP-204']  = '人员-设备销权deviceKeys为空';
    $return['GS_EXP-205']  = '注册任务状态变更state无效';
    $return['GS_EXP-206']  = '查询注册任务状态失败';
    $return['GS_EXP-207']  = '更新注册任务状态失败';
    $return['GS_EXP-209']  = '拍照注册taskId为空';
    $return['GS_EXP-212']  = '人员不存在';
    $return['GS_EXP-213']  = '注册任务状态无效（<0）';
    $return['GS_EXP-214']  = 'taskId不存在或状态错误';
    $return['GS_EXP-215']  = '输入taskId状态不合法';
    $return['GS_EXP-216']  = 'taskId状态未就绪';
    $return['GS_EXP-217']  = '人员不属于该应用';
    $return['GS_EXP-300']  = '设备通信失败';
    $return['GS_EXP-303']  = '设备录入参数错误';
    $return['GS_EXP-304']  = '设备获取失败';
    $return['GS_EXP-305']  = '设备创建deviceKey为空';
    $return['GS_EXP-308']  = '人员-设备授权personGuids为空';
    $return['GS_EXP-309']  = '设备绑定deviceKey为空';
    $return['GS_EXP-310']  = '设备解绑deviceKey为空';
    $return['GS_EXP-311']  = '设备禁用deviceKey为空';
    $return['GS_EXP-312']  = '设备启用deviceKey为空';
    $return['GS_EXP-313']  = '设备同步deviceKey为空';
    $return['GS_EXP-314']  = '设备重置deviceKey为空';
    $return['GS_EXP-316']  = '设备人员批量销权personGuids参数格式错误';
    $return['GS_EXP-317']  = '设备查询设备名deviceKey为空';
    $return['GS_EXP-318']  = '设备更新设备名deviceKey为空';
    $return['GS_EXP-319']  = '更新设备失败';
    $return['GS_EXP-320']  = '设备离线';
    $return['GS_EXP-321']  = '序列号被占用';
    $return['GS_EXP-322']  = '设备已存在';
    $return['GS_EXP-323']  = '设备不存在';
    $return['GS_EXP-325']  = '没有可更新字段';
    $return['GS_EXP-326']  = '设备不在线';
    $return['GS_EXP-327']  = '设备配置不存在';
    $return['GS_EXP-328']  = '语音配置内容含有非法符号';
    $return['GS_EXP-329']  = '语音模板格式错误';
    $return['GS_EXP-330']  = '自定义内容格式错误';
    $return['GS_EXP-331']  = '显示模板格式错误';
    $return['GS_EXP-332']  = '串口模板格式错误';
    $return['GS_EXP-333']  = '语音模式下自定义内容不能空';
    $return['GS_EXP-334']  = '显示模式下自定义内容不能空';
    $return['GS_EXP-335']  = '串口模式下自定义内容不能空';
    $return['GS_EXP-336']  = '语音模式类型未定义';
    $return['GS_EXP-337']  = '显示模式类型未定义';
    $return['GS_EXP-338']  = '串口模式类型未定义';
    $return['GS_EXP-339']  = '识别距离模式类型未定义';
    $return['GS_EXP-340']  = '预览视频流开关模式类型未定义';
    $return['GS_EXP-341']  = '设备名称显示类型未定义';
    $return['GS_EXP-342']  = '设备未启动';
    $return['GS_EXP-343']  = '识别陌生人类型未定义';
    $return['GS_EXP-344']  = '方向配置类型未定义';
    $return['GS_EXP-345']  = '设备序列号类型错误';
    $return['GS_EXP-352']  = '时间段参数数量不正确或超出3段限制';
    $return['GS_EXP-353']  = '时间段参数后时间段早于前时间段';
    $return['GS_EXP-354']  = '时间段参数超出限制';
    $return['GS_EXP-355']  = '时间段参数格式错误';
    $return['GS_EXP-356']  = 'logoUrl格式错误';
    $return['GS_EXP-357']  = '设备不属于该应用';
    $return['GS_EXP-358']  = '设备不属于任何应用';
    $return['GS_EXP-359']  = '识别模式硬件TTL接口重复';
    $return['GS_EXP-360']  = '识别模式硬件232接口重复';
    $return['GS_EXP-361']  = '硬件类型只能为IC读卡器';
    $return['GS_EXP-362']  = '硬件类型只能为ID读卡器';
    $return['GS_EXP-600']  = '照片补推失败';
    $return['GS_EXP-601']  = '照片状态查询失败';
    $return['GS_EXP-602']  = '照片创建失败';
    $return['GS_EXP-603']  = '照片创建img为空';
    $return['GS_EXP-604']  = '照片更新状态state无效';
    $return['GS_EXP-606']  = '超出照片添加数量';
    $return['GS_EXP-607']  = '照片过大';
    $return['GS_EXP-608']  = 'img为空,可能未传输或者数据过大';
    $return['GS_EXP-609']  = '照片不存在';
    $return['GS_EXP-610']  = '文件不为jpg或png类型';
    $return['GS_EXP-611']  = '照片不属于该应用';
    $return['GS_EXP-1006'] = '邮件服务不可用';
    $return['GS_EXP-1007'] = '消息服务不可用';
    $return['GS_EXP-1008'] = '统一客户服务不可用';
    $return['GS_EXP-1009'] = '设备服务不可用';
    $return['GS_EXP-1300'] = '上传base64非图片';
    $return['GS_EXP-1301'] = '人脸框过小,人脸面积小于图片面积5%';
    $return['GS_EXP-1302'] = '人脸侧角度大于15度';
    $return['GS_EXP-1303'] = '照片中无人脸或不止一张人脸';
    $return['GS_EXP-1304'] = '上传图片过大';
    $return['GS_EXP-1305'] = 'base64可能未上传或图片过大';
    $return['GS_EXP-1306'] = '检测异常';
    $return['OP_EXP-2002'] = '图片没有检测到人脸';
    $return['OP_EXP-2006'] = '图片人脸数量过多';
    $return['OP_EXP-3000'] = '人脸过小';
    $return['OP_EXP-3001'] = '人脸超出或过于靠近图片边界';
    $return['OP_EXP-3002'] = '脸过于模糊';
    $return['OP_EXP-3003'] = '脸光照过暗';
    $return['OP_EXP-3004'] = '脸光照过亮';
    $return['OP_EXP-3005'] = '脸左右亮度不对称';
    $return['OP_EXP-3006'] = '三维旋转之俯仰角度过大';
    $return['OP_EXP-3007'] = '三维旋转之左右旋转角过大';
    $return['OP_EXP-3008'] = '平面内旋转角过大';
    if(!empty($return[$code])){
        return $return[$code];
    }else{
        return '未知错误，操作失败';
    }
}



?>