<?php
/*
 * @Discription:  
 * @Author: Hannibal·Lee
 * @Date: 2020-02-15 11:03:14
 * @LastEditTime: 2020-06-01 16:08:49
 */
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_GPC, $_W;

$weid              = $_W['uniacid'];
$action            = 'mcmanage';
$this1             = 'no12';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
$logo              = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
//班级
$bj    = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = '{$weid}' AND schoolid ={$schoolid} And type = 'theclass' ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'theclass', ':schoolid' => $schoolid));
//年级
$xueqi    = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = '{$weid}' AND schoolid ={$schoolid} And type = 'semester' ORDER BY ssort DESC");
// 期号
$qh    = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = '{$weid}' AND schoolid ={$schoolid} And type = 'xq_score'  AND is_show_qh = 1 ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'score', ':schoolid' => $schoolid));
$category = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " WHERE weid =  '{$weid}' AND schoolid ={$schoolid} ORDER BY sid ASC, ssort DESC", array(':weid' => $weid, ':schoolid' => $schoolid), 'sid');

$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$tid_global = $_W['tid'];
if($operation == 'display'){
    $pindex    = max(1, intval($_GPC['page']));
    $psize     = 20;
    $condition = '';
    if(!empty($_GPC['s_name'])){
		$students = pdo_fetch("SELECT id FROM " . tablename($this->table_students) . " WHERE schoolid = :schoolid And s_name = :s_name ORDER BY id DESC LIMIT 1", array(':schoolid' => $schoolid,':s_name' => $_GPC['s_name']));
		$condition .= " AND sid = '{$students['id']}'";		
    }
    if(!empty($_GPC['qh_id'])){
        $qh_id       = intval($_GPC['qh_id']);
        $qhdetail = pdo_fetch("SELECT sd_start,sd_end FROM ".GetTableName('classify')." WHERE sid = '{$qh_id}' ");
        $condition .= " AND createdate between '{$qhdetail['sd_start']}' and '{$qhdetail['sd_end']}'";
    }
    if(!empty($_GPC['nj_id']) && empty($_GPC['bj_id'])){
        $bjinfo   = pdo_fetchall("SELECT sid FROM " . tablename($this->table_classify) . " where parentid = '{$_GPC['nj_id']}' and type = 'theclass' ");
        $bj_str = '';
        foreach($bjinfo as $value){
            $bj_str .=$value['sid'].",";
        }
        $bj_str = trim($bj_str,',');
        $condition .= " AND FIND_IN_SET(bj_id,'{$bj_str}')";
    }
    if(!empty($_GPC['bj_id'])){
        $condition .= " AND bj_id = '{$_GPC['bj_id']}'";
    }


    $list = pdo_fetchall("SELECT * FROM " . GetTableName('morningcheck') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
    foreach($list as $key => $row){
        $ckmac     = pdo_fetch("SELECT * FROM " . tablename($this->table_checkmac) . " WHERE id = '{$row['macid']}'");
        $student = pdo_fetch("SELECT s_name FROM " . tablename($this->table_students) . " where id = :id ", array(':id' => $row['sid']));
        $teacher = pdo_fetch("SELECT tname FROM " . GetTableName('teachers') . " WHERE id = :id", array(':id' => $row['tid'])); 
        $bjinfo  = pdo_fetch("SELECT sname,parentid FROM " . tablename($this->table_classify) . " where sid = :sid ", array(':sid' => $row['bj_id']));
        $njinfo  = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where sid = :sid ", array(':sid' => $bjinfo['parentid']));
        $list[$key]['macname']   = $ckmac['name'] ? $ckmac['name'] : '无';
        $list[$key]['s_name']   = $student['s_name'];
        $list[$key]['tname']    = $teacher['tname'] ? $teacher['tname'] : '无';
        $list[$key]['bj_name']  = $bjinfo['sname'];
        $list[$key]['nj_name']  = $njinfo['sname'];
    }
    /** 导出数据 */
    if($_GPC['out_putcode'] == 'out_putcode'){
        $listss = pdo_fetchall("SELECT * FROM " . GetTableName('morningcheck') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition ORDER BY id DESC");
        $ii   = 0;
        foreach($listss as $index => $row){
            $student = pdo_fetch("SELECT s_name FROM " . tablename($this->table_students) . " where id = :id ", array(':id' => $row['sid']));
            $teacher = pdo_fetch("SELECT tname FROM " . GetTableName('teachers') . " WHERE id = :id", array(':id' => $row['tid'])); 
            $bjinfo  = pdo_fetch("SELECT sname,parentid FROM " . tablename($this->table_classify) . " where sid = :sid ", array(':sid' => $row['bj_id']));
            $njinfo  = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where sid = :sid ", array(':sid' => $bjinfo['parentid']));
            $arr[$ii]['s_name'] = $student['s_name'];
            $arr[$ii]['nj_name']  = $njinfo['sname'];
            $arr[$ii]['bj_name']= $bjinfo['sname'];
            $arr[$ii]['tname']= $teacher['tname'];
            $arr[$ii]['height']= $row['height'].'cm';
            $arr[$ii]['weight']= $row['weight'].'KG';
            $arr[$ii]['tiwen']= $row['tiwen'].'℃';
            $arr[$ii]['lefteye']= $row['lefteye'];
            $arr[$ii]['righteye']= $row['righteye'];
            $arr[$ii]['mouth']= $row['mouth'];
            $arr[$ii]['createdate']= date("Y-m-d",$row['createdate']);
            $ii++;
        }
        $this->exportexcel($arr, array('姓名','年级','班级','检测员','身高','体重','体温','左眼视力','右眼视力','口腔','检测时间'), '学生晨检数据');
        exit();
    }
    $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . GetTableName('morningcheck') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition");
    $pager = pagination($total, $pindex, $psize);
}elseif($operation == 'delete'){
    $id = intval($_GPC['id']);
    if(empty($id)){
        $this->imessage('抱歉，本条信息不存在在或是已经被删除！');
    }
    pdo_delete(GetTableName('morningcheck',false), array('id' => $id));
    $this->imessage('删除成功！', referer(), 'success');
}elseif($operation == 'deleteall'){
    $rowcount    = 0;
    $notrowcount = 0;
    foreach($_GPC['idArr'] as $k => $id){
        $id = intval($id);
        if(!empty($id)){
            $goods = pdo_fetch("SELECT * FROM " . GetTableName('morningcheck') . " WHERE id = :id", array(':id' => $id));
            if(empty($goods)){
                $notrowcount++;
                continue;
            }
            pdo_delete(GetTableName('morningcheck',false), array('id' => $id, 'weid' => $weid));
            $rowcount++;
        }
    }
    $message = "操作成功！共删除{$rowcount}条数据,{$notrowcount}条数据不能删除!";

    $data ['result'] = true;

    $data ['msg'] = $message;

    die (json_encode($data));
}elseif($operation == 'GetMcData'){
    $mcdata = pdo_fetch("SELECT * FROM " . GetTableName('morningcheck') . " WHERE id = :id", array(':id' => $_GPC['id']));
    $student = pdo_fetch("SELECT s_name FROM " . GetTableName('students') . " WHERE id = :id", array(':id' => $mcdata['sid'])); 
    $mcdata['s_name'] = $student['s_name'];
    $result['data'] = $mcdata;
    die(json_encode($result));
}elseif($operation == 'EditMcData'){
    $hasdata = pdo_fetch("SELECT id FROM " . GetTableName('morningcheck') . " WHERE id = :id", array(':id' => $_GPC['id']));
    if(!empty($hasdata)){
        $data = array(
            'height' => $_GPC['height'],
            'weight' => $_GPC['weight'],
            'lefteye' => $_GPC['lefteye'],
            'righteye' => $_GPC['righteye'],
            'mouth' => $_GPC['mouth'],
            'tiwen' => $_GPC['tiwen'],
            'handHerpes' => $_GPC['handHerpes'],
            'nail' => $_GPC['nail'],
            'cough' => $_GPC['cough'],
            'trauma' => $_GPC['trauma'],
            'herpes' => $_GPC['herpes'],
            'vomit' => $_GPC['vomit'],
            'diarrhea' => $_GPC['diarrhea'],
            'cold' => $_GPC['cold'],
            'headache' => $_GPC['headache'],
        );
        if(pdo_update(GetTableName('morningcheck',false),$data,array('id'=>$_GPC['id']))){
            $result['msg'] = '修改成功';
            $result['result'] = true;
        }else{
            $result['msg'] = '数据有误';
            $result['result'] = false;
        }
    }else{
        $result['msg'] = '当前数据不存在';
        $result['result'] = false;
    }
    die(json_encode($result));
}elseif($operation == 'GetStuMcData'){
    $qh_id = $_GPC['qh_id'];
    if($_GPC['sid']){
        $sid = $_GPC['sid'];
    }else{
        $mc = pdo_fetch("SELECT sid FROM ".GetTableName('morningcheck')." WHERE schoolid='{$schoolid}' ORDER BY id DESC");
        $sid = $mc['sid'];
    }
    mload()->model('mc');
    $return_data = GetStuMcData($sid,$qh_id);
}elseif($operation == 'getMcInfo'){
    $hasdata = pdo_fetch("SELECT * FROM " . GetTableName('morningcheck') . " WHERE id = :id", array(':id' => $_GPC['id']));
    if(empty($hasdata)){
        $result['msg'] = '数据有误';
        $result['result'] = false;
    }else{
        $result['result'] = true;
        $result['data'] = $hasdata;
    }
    include $this->template('web/mc/mc_bot');
    exit();
}elseif($operation == 'sendToStu'){
    pdo_update(GetTableName('morningcheck',false),array('is_send'=>1),array('id'=>$_GPC['id']));
    $sendData = pdo_fetch("SELECT sid,tiwen FROM " . GetTableName('morningcheck') . " WHERE id = :id", array(':id' => $_GPC['id']));
    $this->sendMobileTwtz($sendData['sid'],$sendData['tiwen'],3,$_W['schooltype'],$_GPC['id']);
    $result['result'] = true;
    $result['msg'] = '推送成功';
    die(json_encode($result));
}elseif($operation == 'report'){
    $pindex    = max(1, intval($_GPC['page']));
    $psize     = 20;
    $condition = '';
    $synctime = pdo_fetch("SELECT synctime FROM ".GetTableName('schoolset')." WHERE schoolid = '{$schoolid}'")['synctime'];
    $difftime =  time() - $synctime - 86400;
    if($difftime > 0){
        pdo_update(GetTableName('schoolset',false),array('synctime'=>0),array('schoolid'=>$schoolid));
    }
    $year = pdo_fetchall("SELECT DISTINCT(year) FROM ".GetTableName('mcreportlist')." WHERE schoolid = '{$schoolid}' ORDER BY year");
    // $month = pdo_fetchall("SELECT DISTINCT(month) FROM ".GetTableName('mcreportlist')." WHERE schoolid = '{$schoolid}' ORDER BY month");
    // if(!empty($_GPC['s_name'])){
	// 	$condition .= " AND s.s_name LIKE '%{$_GPC['s_name']}%'";	
    // }

    if(!empty($_GPC['year'])){
		$condition .= " AND m.year = '{$_GPC['year']}'";	
    }

    if(!empty($_GPC['type'])){
		$condition .= " AND m.type = '{$_GPC['type']}'";	
    }

    $list = pdo_fetchall("SELECT m.*,s.s_name,s.icon,c.sname FROM ".GetTableName('mcreportlist')." as m LEFT JOIN " . GetTableName('students') . " as s ON s.id = m.sid LEFT JOIN " . GetTableName('classify') . " as c ON c.sid = s.bj_id WHERE m.schoolid = '{$schoolid}' {$condition} ORDER BY m.id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
    foreach ($list as $key => $value) {
        $list[$key]['icon'] = tomedia($value['icon']);
        $list[$key]['createtime'] = date("Y-m-d H:i",$value['createtime']);
    }
}elseif($operation == 'getReportInfo'){
    $type = $_GPC['type'];
    $id = $_GPC['id'];
    if($type == 1){
        //当前条
        $dataTemp = pdo_fetch("SELECT content,sid FROM " . GetTableName('mcreportlist') . " WHERE id = :id", array(':id' => $id));
        //上一条
        $prevdataTemp = pdo_fetch("SELECT content,sid FROM " . GetTableName('mcreportlist') . " WHERE id < :id AND sid = '{$dataTemp['sid']}' ORDER BY id DESC", array(':id' => $id));
        $stuinfo = pdo_fetch("SELECT icon,s_name FROM ".GetTableName('students')." WHERE weid = '{$weid}' and id = '{$dataTemp['sid']}' ");

        if(empty($stuinfo['icon'])){
            $stuinfo['icon'] = $school['spic'];
        }
        $data = json_decode($dataTemp['content'],true);
        $prevdata = json_decode($prevdataTemp['content'],true);
    }elseif($type == 2){
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
        /************上一年的年度数据***********/
        $stuinfo = pdo_fetch("SELECT icon,s_name FROM ".GetTableName('students')." WHERE weid = '{$weid}' and id = '{$dataTemp['sid']}' ");
        if(empty($stuinfo['icon'])){
            $stuinfo['icon'] = $school['spic'];
        }
        $dataContent = json_decode($dataTemp['content'],true);
    }elseif($type == 3){
        /************当前年的年度数据***********/
        $dataTemp = pdo_fetch("SELECT * FROM " . GetTableName('mcreportlist') . " WHERE id = :id AND type = :type", array(':id' => $id,':type'=>3));
        /************当前年的年度数据***********/
        $oldyear = $dataTemp['year'] - 1;
        $olddataTemp = pdo_fetch("SELECT * FROM " . GetTableName('mcreportlist') . " WHERE sid = :sid AND type = :type AND year = :year", array(':sid' => $dataTemp['sid'],':type' => 3,':year' => $oldyear));
        $stuinfo = pdo_fetch("SELECT icon,s_name FROM ".GetTableName('students')." WHERE weid = '{$weid}' and id = '{$dataTemp['sid']}' ");
        if(empty($stuinfo['icon'])){
            $stuinfo['icon'] = $school['spic'];
        }
        $dataContent = json_decode($dataTemp['content'],true);
        $olddataContent = json_decode($olddataTemp['content'],true);
    }
    include $this->template('web/mc/mc_report_bot');
    exit();
}elseif($operation == 'getXqEcharts'){
    $monthDataTemp = pdo_fetchAll("SELECT * FROM " . GetTableName('mcreportlist') . " WHERE schoolid = :schoolid AND type = :type AND sid = :sid GROUP BY month HAVING year+month >= '{$_GPC['starttime']}' AND year+month <= '{$_GPC['endtime']}' ORDER BY month", array(':schoolid' => $schoolid, ':type'=>2,':sid'=>$_GPC['sid']));
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
    $result['monthData'] = $monthData;
    die(json_encode($result));
}elseif($operation == 'getNdEcharts'){
    /************当前年的月度数据***********/
    $monthDataTemp = pdo_fetchAll("SELECT * FROM " . GetTableName('mcreportlist') . " WHERE schoolid = :schoolid AND year = :year AND type = :type AND sid = :sid GROUP BY month ORDER BY month", array(':schoolid' => $schoolid,':year'=>$_GPC['year'],':type'=>1,':sid'=>$_GPC['sid']),'month');
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
}elseif($operation == 'getReport'){
    $url = $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&m=fm_jiaoyu&do=sync';
    $data = array(
        'schoolid' => $schoolid,
        'weid' => $weid,
        'op' => 'startsync',
        'start' => 0,
    );
    timeOutPost($url, $data);
    $result['msg'] = '获取成功!';
    die(json_encode($result));
}
include $this->template('web/mcmanage');
?>