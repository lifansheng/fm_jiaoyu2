<?php
/**
 * 沃土 考勤机回调文件
 * @author Hannibal·Lee
 * 微美科技
 */

global $_GPC, $_W;
$operation = in_array($_GPC['op'], array('default', 'callbackurl')) ? $_GPC['op'] : 'default';
$weid = $_GPC['i'];
$schoolid = $_GPC['schoolid'];
$macid = $_GPC['deviceKey'];
/* 
ob_start();
echo "NO ";
echo date("Y-m-d",time());
echo "\r\n";
echo "********************************start***********************************";
echo "\r\n";
 */
$ckmac  = pdo_fetch("SELECT * FROM " . GetTableName('checkmac') . " WHERE macid = '{$macid}' And weid = '{$weid}' And schoolid = '{$schoolid}' ");
$school = pdo_fetch("SELECT * FROM " . GetTableName('index') . " WHERE id = '{$schoolid}' ");
$xk_type = pdo_fetch("SELECT * FROM " . GetTableName('schoolset') . " WHERE schoolid = '{$schoolid}' ");
if ($operation == 'default') {
    echo ("对不起，你的请求不存在！");
    die();
    //echo "\r\n";
}
if (empty($school)) {
    echo ("找不到本校，设备未关联学校");
    die();
    //echo "\r\n";
}
if (empty($ckmac)) {
    echo ("没找到设备,请添加设备");
    die();
    //echo "\r\n";
}
if ($school['is_recordmac'] == 2) {
    echo ("本校无权使用设备,请联系管理员");
    die();
    //echo "\r\n";
}
if ($ckmac['is_on'] == 2) {
    echo ("本设备已关闭,请在管理后台打开");
    die();
    //echo "\r\n";
}

if ($operation == 'callbackurl') {
    $fstype   = false;
    $ckuser   = [];
    $stu_tea  = 'notFound';
    $signTime = strtotime($_GPC['showTime']);
    $recMode = $_GPC['recMode']; //识别模式，1:刷脸，2:刷卡
    $signData = json_decode($_GPC['data'], true); //包含了卡信息
    $tempSignData =  htmlspecialchars_decode($_GPC['data']);
    $NewSignData = json_decode($tempSignData);
    $guid = $_GPC['personGuid'];
    if (!empty($guid)) {
        $ckuser = pdo_fetch("SELECT * FROM " . GetTableName('idcard') . " WHERE  guid = '{$guid}' And weid = '{$weid}' And schoolid = '{$schoolid}' ");
        if (!empty($ckuser)) {
            $checkthisdata = [];
            if ($ckuser['usertype'] == 0) { //学生
                $checkthisdata = pdo_fetch("SELECT * FROM " . GetTableName('checklog') . " WHERE sid = '{$ckuser['sid']}' And createtime = '{$signTime}' And schoolid = '{$schoolid}' ");
            } elseif ($ckuser['usertype'] == 1) { //老师
                $checkthisdata = pdo_fetch("SELECT * FROM " . GetTableName('checklog') . " WHERE tid = '{$ckuser['tid']}' And createtime = '{$signTime}' And schoolid = '{$schoolid}' ");
            }
            if (empty($checkthisdata)) { //如果不是重复刷卡
                $times   = TIMESTAMP;
                $pic     = $_GPC['photoUrl'];
                $nowtime = date('H:i', $signTime);
                include 'checktime.php';
                if ($ckuser['usertype'] == 0) {
                    //如果是学生
                    $data = array(
                        'weid'       => $weid,
                        'schoolid'   => $schoolid,
                        'macid'      => $ckmac['id'],
                        'sid'        => $ckuser['sid'],
                        'bj_id'      => $ckuser['bj_id'],
                        'type'       => $type,
                        'pic'        => $pic,
                        'leixing'    => $leixing,
                        'createtime' => $signTime,
                        'pard'       => $ckuser['pard'],
                        'pname'      => $ckuser['pname'],
                        'surestatus'  => $xk_type['is_sure_kq'] == 1 ? 0 : 1,
                    );
                    //如果刷卡
                    if ($sigrecModenType == 2) {
                        $data['cardid'] = $NewSignData['idNo'];
                    }
                    pdo_insert($this->table_checklog, $data);
                    $result['info'] = "成功-学生";
                    $checkid = pdo_insertid();
                    
                    if ($school['send_overtime'] >= 1) {
                        $overtime = $school['send_overtime'] * 60;
                        $timecha  = $times - $signTime;
                        if ($overtime >= $timecha) {
                            if ($ckuser['severend'] >= $times) {
                                $this->sendMobileJxlxtz($schoolid, $weid, $ckuser['bj_id'], $ckuser['sid'], $type, $leixing, $checkid, 1);
                            }
                        } else {
                            $result['info'] = "延迟发送之数据将不推送刷卡提示";
                        }
                    } else {
                        if ($ckuser['severend'] >= $times) {
                            $this->sendMobileJxlxtz($schoolid, $weid, $ckuser['bj_id'], $ckuser['sid'], $type, $leixing, $checkid, 1);
                        }
                    }
                } elseif ($ckuser['usertype'] == 1) {
                    $data = array(
                        'weid'       => $weid,
                        'schoolid'   => $schoolid,
                        'macid'      => $ckmac['id'],
                        'tid'        => $ckuser['tid'],
                        'type'       => $type,
                        'leixing'    => $leixing,
                        'pic'        => $pic,
                        'pard'       => 1,
                        'createtime' => $signTime,
                        'surestatus'  => $xk_type['is_sure_kq'] == 1 ? 0 : 1,
                    );
                    if ($recMode == 2) {
                        $data['cardid'] = $NewSignData['idNo'];
                    }
                    pdo_insert($this->table_checklog, $data);
                    $result['info'] = "成功-教师";
                    $fstype = true;
                }
            } else {
                $fstype = true;
                $result['info'] = "此数据为重复提交";
            }
        } else {
            $fstype = true;
            $result['info'] = "无此人员";
        }
    } else {
        $result['info'] = "未获取到人员";
    }
    echo json_encode($result);
    die();
    //echo "\r\n";
}


/* var_dump($_GPC);
var_dump($result);
var_dump($pic);

var_dump($signData);
var_dump($tempSignData);
var_dump($NewSignData);
echo '*********************************end************************************';
echo "\r\n";
echo "\r\n";
echo "\r\n";
echo "\r\n";
$test = ob_get_clean();


//写入日志文件
$txtname = 'wtlog.txt';
$txtpath_name = IA_ROOT . '/attachment/down/' . $txtname;


$file = fopen($txtpath_name, "a+");
fwrite($file, $test);
fclose($file); */
