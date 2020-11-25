<?php
global $_W, $_GPC;
$weid = $this->weid;
$from_user = $this->_fromuser;
$schoolid = intval($_GPC['schoolid']);
$id = intval($_GPC['id']);
$op = $_GPC['op'] ? $_GPC['op'] : "display";
$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $_W['uniacid'], ':id' => $schoolid));
/************当前年的年度数据***********/
$dataTemp = pdo_fetch("SELECT cnbotssemesterid,semestertype,sid,content,title FROM " . GetTableName('mcreportlist') . " WHERE id = :id AND type = :type", array(':id' => $id,':type'=>2));
$xq = pdo_fetch("SELECT * FROM " . GetTableName('classify') . " WHERE cn_yearid = :cn_yearid AND schoolid = :schoolid", array(':schoolid' => $schoolid,':cn_yearid'=>$dataTemp['cnbotssemesterid']));
if($dataTemp['semestertype'] == 1){ //上学期
    $starttime = date("Y",$xq['sd_start']) + date("n",$xq['sd_start']);
    $endtime = date("Y",$xq['firstlast']) + date("n",$xq['firstlast']);
    $startdate = date("Y-m-d",$xq['sd_start']);
    $enddate = date("Y-m-d",$xq['firstlast']);
}
if($dataTemp['semestertype'] == 2){ //下学期
    $starttime = date("Y",$xq['laststart']) + date("n",$xq['laststart']);
    $endtime = date("Y",$xq['sd_end']) + date("n",$xq['sd_end']);
    $startdate = date("Y-m-d",$xq['laststart']);
    $enddate = date("Y-m-d",$xq['sd_end']);
}
/************当前年的年度数据***********/
if($op == 'display'){
    /************上一年的年度数据***********/
    $stuinfo = pdo_fetch("SELECT icon,s_name FROM ".GetTableName('students')." WHERE weid = '{$weid}' and id = '{$dataTemp['sid']}' ");
    if(empty($stuinfo['icon'])){
        $stuinfo['icon'] = $school['spic'];
    }
    $dataContent = json_decode($dataTemp['content'],true);
}elseif($op == 'getEcharts'){
    $monthDataTemp = pdo_fetchAll("SELECT * FROM " . GetTableName('mcreportlist') . " WHERE schoolid = :schoolid AND type = :type AND sid = :sid GROUP BY month HAVING year+month >= '{$starttime}' AND year+month <= '{$endtime}' ORDER BY month", array(':schoolid' => $schoolid, ':type'=>2,':sid'=>$dataTemp['sid']));
    $monthData = array(
        'height' => [],
        'weight' => [],
        'leftEyeVision' => [],
        'rightEyeVision' => [],
    );
    $heightData = $weightData = $lefteyeData = $righteyeData = 0;
    for ($i=0; $i < count($monthDataTemp); $i++) { 
        if($monthDataTemp[$i]){
            $monthDataContentTemp = json_decode($monthDataTemp[$i]['content'],true);
            $heightData = $monthDataContentTemp['height'] ? $monthDataContentTemp['height'] : $heightData;
            $weightData = $monthDataContentTemp['weight'] ? $monthDataContentTemp['weight'] : $weightData;
            $lefteyeData = $monthDataContentTemp['leftEyeVision'] ? $monthDataContentTemp['leftEyeVision'] : $lefteyeData;
            $righteyeData = $monthDataContentTemp['rightEyeVision'] ? $monthDataContentTemp['rightEyeVision'] : $righteyeData;
        }
        $monthData['height'][] = floatval($heightData);
        $monthData['weight'][] = floatval($weightData);
        $monthData['leftEyeVision'][] = floatval($lefteyeData);
        $monthData['rightEyeVision'][] = floatval($righteyeData);
        $monthData['date'][] = $monthDataTemp[$i]['year'].'.'.$monthDataTemp[$i]['month'];
    }
    $minHeight = min($monthData['height']);
    $minWeight = min($monthData['weight']);
    $maxHeight = max($monthData['height']);
    $maxWeight = max($monthData['weight']);

    // $heightDiff = 0;
    // $weightDiff = 0;
    // for ($i=0; $i < count($monthDataTemp); $i++) { 
    //     $heightDiffTemp = abs($monthData['height'][$i] - $oldmonthData['height'][$i]);
    //     $heightDiff = max($heightDiffTemp,$heightDiff);
    //     $weightDiffTemp = abs($monthData['weight'][$i] - $oldmonthData['weight'][$i]);
    //     $weightDiff = max($weightDiffTemp,$weightDiff);
    // }

    // $maxYheight = max($maxHeight,$maxOldHeight)+$heightDiff+0.3;
    // $minYheight = min($minHeight,$minOldHeight)-$heightDiff*3+0.3;

    // $maxYweight = max($maxWeight,$maxOldWeight)+$weightDiff+0.3;
    // $minYweight = min($minWeight,$minOldWeight)-$weightDiff*3+0.3;

    $result['monthData'] = $monthData;
    // $result['maxYheight'] = $maxYheight;
    // $result['minYheight'] = max($minYheight,0);
    // $result['maxYweight'] = $maxYweight;
    // $result['minYweight'] = max($minYweight,0);
    die(json_encode($result));
}

include $this->template(''.$school['style1'].'/mcreportinfoxq');
?>