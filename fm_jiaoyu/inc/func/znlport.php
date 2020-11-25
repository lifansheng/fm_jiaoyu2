


 <?php
/*
 * @Discription:  
 * @Author: Hannibal·Lee
 * @Date: 2020-05-29 14:18:15
 * @LastEditTime: 2020-09-01 18:24:56
 */ 
mload()->model('znl'); 
global $_GPC,$_W;
$op = $_GPC['op'] ? $_GPC['op'] : 'default' ;
// require MODULE_ROOT . '/model/mc.config.php';



$GetappKey = $_SERVER['HTTP_APPKEY'];
$GetNonce = $_SERVER['HTTP_NONCE'];
$GetCurTime = $_SERVER['HTTP_CURTIME'];
$GetCheckSum = $_SERVER['HTTP_CHECKSUM'];



// if(time() - $GetCurTime > 300){
//     $result['resultCode'] = "1";
//     $result['resultStr'] = "数据超时";
//     die(json_encode($result));
// }

if($op == 'default'){
    $result['resultCode'] = "1";
    $result['resultStr'] = "无效请求";
    die(json_encode($result));
}

if($op == 'test'){    
    $PData = json_decode(file_get_contents('php://input'),true);
    $bj_id = pdo_fetch("SELECT bj_id,weid FROM ".GetTableName('students')." WHERE schoolid='{$PData['deptNo']}' AND id = '{$PData['empNo']}' ");
    $GetData = array(
        'weid' => $bj_id['weid'],
        'sid' => $PData['empNo'],
        'bj_id' => $bj_id['bj_id'],
        'createtime' => $PData['checkDate']/1000,
        'createdate' => strtotime(date('Y-m-d',$PData['checkDate']/1000)), 
        'schoolid' => $PData['deptNo'],
        'height' => $PData['height'] * 100,
        'weight' => $PData['weight'],
        'tiwen' => $PData['bodyTemperature'],
        'herpes' => $PData['herpes'] == 'Y' ? 2 : 1,
        'handHerpes' =>  $PData['handHerpes'] == 'U' || $PData['handHerpes'] == 'W' ? 0 : ($PData['handHerpes'] == 'Y' ? 2 : 1),
        'mouth' => $PData['mouthHerpes'] == 'U' || $PData['mouthHerpes'] == 'W' ? 0 : ($PData['mouthHerpes'] == 'Y' ? 2 : 1),
        'nail' => $PData['nail'] == 'Y' ? 2 : 1,
        'cough' => $PData['cough'] == 'Y' ? 2 : 1,
        'trauma' => $PData['wound'] == 'Y' ? 2 : 1 ,
        'userPhoto' => $PData['userPhoto'],
        'handPhoto' => $PData['handPhoto'],
        'mouthPhoto' => $PData['mouthPhoto'],
        'is_mc' => 1,
        'issb' => 1,
    );
    pdo_insert(GetTableName('morningcheck',false),$GetData);
    WriteGlobalLogznl($GetData);
    $result['resultCode'] = 0;
    $result['resultStr'] = '';
    die(json_encode($result));
}


if($op == 'GetData'){
    $PData = json_decode(file_get_contents('php://input'),true);
    // $PData  = $_GPC;
    if(empty($PData)){
	    $result['resultCode'] = -11;
	    $result['resultStr'] = '';
	    die(json_encode($result));	
    }
    $bj_id = pdo_fetch("SELECT bj_id,weid FROM ".GetTableName('students')." WHERE schoolid='{$PData['deptNo']}' AND id = '{$PData['empNo']}' ");
    $App = pdo_fetch("SELECT znl_appid,znl_appsecret FROM ".GetTableName('schoolset')." WHERE schoolid = '{$PData['deptNo']}' ");
    $AppKey = $App['znl_appid'];
    $AppSecret = $App['znl_appsecret'];
    $CheckSumCheck = strtolower(sha1('' . $AppSecret . $GetNonce . $GetCurTime, FALSE));

    if($CheckSumCheck != $GetCheckSum){
        $result['resultCode'] = "-1";
        $result['resultStr'] = "鉴权失败";
         WriteGlobalLogznl($result);
        die(json_encode($result));
    }
    if($GetappKey != $AppKey){
        $result['resultCode'] = "-1";
        $result['resultStr'] = "鉴权失败";
        WriteGlobalLogznl($result);
        die(json_encode($result));
    }


    if($PData['shuttleStatus'] != 'out'){
        $GetData = array(
            'weid' => $bj_id['weid'],
            'sid' => $PData['empNo'],
            'bj_id' => $bj_id['bj_id'],
            'createtime' => $PData['checkDate']/1000,
            'createdate' => strtotime(date('Y-m-d',$PData['checkDate']/1000)), 
            'schoolid' => $PData['deptNo'],
            'height' => $PData['height'] ,
            'weight' => $PData['weight'],
            'tiwen' => $PData['bodyTemperature'],
            'herpes' => $PData['herpes'] == 'Y' ? 2 : 1,
            'handHerpes' =>  $PData['handHerpes'] == 'U' || $PData['handHerpes'] == 'W' ? 0 : ($PData['handHerpes'] == 'Y' ? 2 : 1),
            'mouth' => $PData['mouthHerpes'] == 'U' || $PData['mouthHerpes'] == 'W' ? 0 : ($PData['mouthHerpes'] == 'Y' ? 2 : 1),
            'nail' => $PData['nail'] == 'Y' ? 2 : 1,
            'cough' => $PData['cough'] == 'Y' ? 2 : 1,
            'trauma' => $PData['wound'] == 'Y' ? 2 : 1 ,
            'userPhoto' => $PData['userPhoto'],
            'handPhoto' => $PData['handPhoto'],
            'mouthPhoto' => $PData['mouthPhoto'],
            'is_mc' => 1,
            'issb' => 1,
            'is_send' => 1,
        );
        pdo_insert(GetTableName('morningcheck',false),$GetData);
        $logid = pdo_insertid();
        if(intval($GetData['tiwen']>37.5) || $GetData['herpes'] == 2 || $GetData['handHerpes'] == 2 || $GetData['mouth'] == 2 || $GetData['nail'] == 2 || $GetData['cough'] == 2 || $GetData['trauma'] == 2 ){ //如果有异常就发送给老师
            pdo_update(GetTableName('morningcheck',false),array('is_send'=>0),array('id'=>$logid));
        }
        $this->sendMobileTwtz($PData['empNo'],$PData['bodyTemperature'],3,$_W['schooltype'],$logid);
    }

    $checkdata = array(
        'schoolid' => $PData['deptNo'],
        'weid' => $bj_id['weid'],
        'sid' => $PData['empNo'],
        'bj_id' => $bj_id['bj_id'],
        'temperature' => $PData['bodyTemperature'],
        'pic' => $PData['userPhoto'],
        'type' => $PData['shuttleStatus'] == 'out' ? '离校' :"进校",
        'leixing' => $PData['shuttleStatus'] == 'out' ? 2 : 1 ,
        'pard' => 1,
        'checktype' => 1,
        'isconfirm' => 1,
        'createtime' => $PData['checkDate']/1000,
        'sc_ap' => 0,
        'surestatus' => 1,
    );
    
    pdo_insert(GetTableName('checklog',false),$checkdata);
    WriteGlobalLogznl("获取到的数据：");
    WriteGlobalLogznl($PData);
    $result['resultCode'] = 0;
    $result['resultStr'] = '';
    die(json_encode($result));
}

if($op == 'GetReport'){
    // $PData = json_decode(file_get_contents('php://input'),true);
    $PData = $_GPC;
    // $startDate = date('Y-m-d H:i:s', strtotime(date('Y-m-01') . ' -1 month'));
    // $endDate = date('Y-m-d  H:i:s', strtotime(date('Y-m-01') . ' -1 day'));
    // znlGetReport($PData['deptNo'],$PData['studentNo'],$startDate,$endDate,2);
    znlGetReport($PData['deptNo'],$PData['studentNo'],'','',2);
}


function WriteGlobalLogznl($GetData){
    $txtname = 'GobalLogznl'.date("Y-m-d",time()).'.txt';
    $txtpath_name = IA_ROOT . '/attachment/down/' . $txtname;
	ob_start(); //打开缓冲区
	var_dump($GetData);
	$a = ob_get_contents(); //输出缓冲区内容到$a,相当于赋值给$a
	ob_clean();   //这里清除缓冲区内容

	$fp = fopen($txtpath_name,"a");//打开文件资源通道 不存在则自动创建
    fwrite($fp,date("Y-m-d H:i:s").'   '.$a."\r\n");//写入文件
    fclose($fp);//关闭资源通道
}
