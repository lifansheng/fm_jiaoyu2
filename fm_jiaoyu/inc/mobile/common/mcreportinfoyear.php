<?php
global $_W, $_GPC;
$weid = $this->weid;
$from_user = $this->_fromuser;
$schoolid = intval($_GPC['schoolid']);
$id = intval($_GPC['id']);
$op = $_GPC['op'] ? $_GPC['op'] : "display";
$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $_W['uniacid'], ':id' => $schoolid));
/************当前年的年度数据***********/
$dataTemp = pdo_fetch("SELECT * FROM " . GetTableName('mcreportlist') . " WHERE id = :id AND type = :type", array(':id' => $id,':type'=>3));
/************当前年的年度数据***********/
$oldyear = $dataTemp['year'] - 1;
$olddataTemp = pdo_fetch("SELECT * FROM " . GetTableName('mcreportlist') . " WHERE sid = :sid AND type = :type AND year = :year", array(':sid' => $dataTemp['sid'],':type' => 3,':year' => $oldyear));
if($op == 'display'){
    /************上一年的年度数据***********/
    
    $stuinfo = pdo_fetch("SELECT icon,s_name FROM ".GetTableName('students')." WHERE weid = '{$weid}' and id = '{$dataTemp['sid']}' ");
    if(empty($stuinfo['icon'])){
        $stuinfo['icon'] = $school['spic'];
    }
    $dataContent = json_decode($dataTemp['content'],true);
    $olddataContent = json_decode($olddataTemp['content'],true);
}elseif($op == 'getEcharts'){
    /************当前年的月度数据***********/
    $monthDataTemp = pdo_fetchAll("SELECT * FROM " . GetTableName('mcreportlist') . " WHERE schoolid = :schoolid AND year = :year AND type = :type AND sid = :sid GROUP BY month ORDER BY month", array(':schoolid' => $schoolid,':year'=>$dataTemp['year'],':type'=>1,':sid'=>$dataTemp['sid']),'month');
    $num = 12;
    $monthData = array(
        'height' => [],
        'weight' => [],
        'leftEyeVision' => [],
        'rightEyeVision' => [],
    );
    $heightData = $weightData = $lefteyeData = $righteyeData = 0;
    for ($i=1; $i <= $num; $i++) { 
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
    }
    $minHeight = min($monthData['height']);
    $minWeight = min($monthData['weight']);
    $maxHeight = max($monthData['height']);
    $maxWeight = max($monthData['weight']);
    /************当前年的月度数据***********/
    $oldmonthData = array(
        'height' => [],
        'weight' => [],
        'leftEyeVision' => [],
        'rightEyeVision' => [],
    );
    $oldheightData = $oldweightData = $oldlefteyeData = $oldrighteyeData = 0;
    for ($i=1; $i <= $num; $i++) { 
        if($oldmonthDataTemp[$i]){
            $oldmonthDataContentTemp = json_decode($oldmonthDataTemp[$i]['content'],true);
            $oldheightData = $oldmonthDataContentTemp['height'] ? $oldmonthDataContentTemp['height'] : $oldheightData;
            $oldweightData = $oldmonthDataContentTemp['weight'] ? $oldmonthDataContentTemp['weight'] : $oldweightData;
            $oldlefteyeData = $oldmonthDataContentTemp['leftEyeVision'] ? $oldmonthDataContentTemp['leftEyeVision'] : $oldlefteyeData;
            $oldrighteyeData = $oldmonthDataContentTemp['rightEyeVision'] ? $oldmonthDataContentTemp['rightEyeVision'] : $oldrighteyeData;
        }
        $oldmonthData['height'][] = floatval($oldheightData);
        $oldmonthData['weight'][] = floatval($oldweightData);
        $oldmonthData['leftEyeVision'][] = floatval($oldlefteyeData);
        $oldmonthData['rightEyeVision'][] = floatval($oldrighteyeData);
    }

    $minOldHeight = min($monthData['height']);
    $minOldWeight = min($monthData['weight']);
    $maxOldHeight = max($monthData['height']);
    $maxOldWeight = max($monthData['weight']);
    $heightDiff = 0;
    $weightDiff = 0;
    for ($i=0; $i < $num; $i++) { 
        $heightDiffTemp = abs($monthData['height'][$i] - $oldmonthData['height'][$i]);
        $heightDiff = max($heightDiffTemp,$heightDiff);
        $weightDiffTemp = abs($monthData['weight'][$i] - $oldmonthData['weight'][$i]);
        $weightDiff = max($weightDiffTemp,$weightDiff);
    }

    $maxYheight = max($maxHeight,$maxOldHeight)+$heightDiff+0.3;
    $minYheight = min($minHeight,$minOldHeight)-$heightDiff*3+0.3;

    $maxYweight = max($maxWeight,$maxOldWeight)+$weightDiff+0.3;
    $minYweight = min($minWeight,$minOldWeight)-$weightDiff*3+0.3;

    $result['monthData'] = $monthData;
    $result['oldmonthData'] = $oldmonthData;
    $result['maxYheight'] = $maxYheight;
    $result['minYheight'] = max($minYheight,0);
    $result['maxYweight'] = $maxYweight;
    $result['minYweight'] = max($minYweight,0);
    die(json_encode($result));
}

include $this->template(''.$school['style1'].'/mcreportinfoyear');
?>