<?php
/*
 * @Discription:  
 * @Author: Hannibal·Lee
 * @Date: 2020-02-25 18:09:15
 * @LastEditTime: 2020-03-07 17:25:25
 */

function GetStuMcData($sid,$qh_id = ''){
    //查询学生姓名
    $student = pdo_fetch("SELECT s_name FROM ".GetTableName('students')." WHERE id='{$sid}' LIMIT 1");
    // 查询期号起止时间
    $qhdata = pdo_fetch("SELECT sd_start,sd_end FROM ".GetTableName('classify')." WHERE sid = '{$qh_id}' ");
    if(!empty($qhdata)){
        $condition = " AND createdate BETWEEN '{$qhdata['sd_start']}' AND '{$qhdata['sd_end']}' ";
    }
    // 查询当前学生所在期号的晨检情况
    $date = []; //日期
    $height = []; //身高
    $weight = []; //体重
    $tiwen = []; //体温
    $lefteye = []; //左眼视力
    $righteye = []; //右眼视力
    $mc = pdo_fetchall("SELECT * FROM ".GetTableName('morningcheck')." WHERE sid = '{$sid}' $condition GROUP BY sid ORDER BY createtime LIMIT 0,13");
    foreach ($mc as $key => $value) {
        $date[$key] = date("m-d",$value['createdate']);
        $height[$key] = $value['height'];
        $weight[$key] = $value['weight'];
        $tiwen[$key] = $value['tiwen'];
        $lefteye[$key] = $value['lefteye'];
        $righteye[$key] = $value['righteye'];
    }
    $return_data['date'] = $date;
    $return_data['height'] = $height;
    $return_data['weight'] = $weight;
    $return_data['tiwen'] = $tiwen;
    $return_data['lefteye'] = $lefteye;
    $return_data['righteye'] = $righteye;
    $return_data['student'] = $student;
    die(json_encode($return_data));
}

function GetHealReport($schoolid,$date,$bjstr = '',$bj_id = ''){
    $start = strtotime($date);
    $end = $start + 86399;
    $condition = " AND schoolid = '{$schoolid}' AND createtime BETWEEN '{$start}' AND '{$end}' ";
    if(!empty($bjstr)){
        $condition .=" AND FIND_IN_SET(bj_id,'{$bjstr}')";
    }
    if(!empty($bj_id)){
        $condition .=" AND bj_id = '{$bj_id}'";
    }
    if(keep_MC()){
        $Checkup = pdo_fetchcolumn("SELECT count(sid) FROM ".GetTableName('morningcheck')." WHERE is_mc = 1 $condition"); //检测人数
        $CheckupIsNormal = pdo_fetch("SELECT IFNULL(SUM(case when (tiwen > 37.5 OR cough = 1 OR vomit = 1 OR trauma = 1 OR diarrhea = 1 OR cold = 1 OR headache = 1 OR mouth = 2) then 1 else 0 end),0) as abnormal,IFNULL(SUM(case when ((tiwen BETWEEN 35.5 AND 37.5) AND cough = 2 AND vomit = 2 AND trauma = 2 AND diarrhea = 2 AND cold = 2 AND headache = 2 AND mouth = 1) then 1 else 0 end),0) as normal FROM ".GetTableName('morningcheck')." WHERE is_mc = 1 $condition"); //正常与异常人数
    }else{
        $Checkup = pdo_fetchcolumn("SELECT count(sid) FROM ".GetTableName('morningcheck')." WHERE is_mc = 0 $condition"); //检测人数
        $CheckupIsNormal = pdo_fetch("SELECT IFNULL(SUM(case when (tiwen > 37.5 OR cough = 1 OR vomit = 1 OR trauma = 1 OR diarrhea = 1 OR cold = 1 OR headache = 1) then 1 else 0 end),0) as abnormal,IFNULL(SUM(case when ((tiwen BETWEEN 35.5 AND 37.5) AND cough = 2 AND vomit = 2 AND trauma = 2 AND diarrhea = 2 AND cold = 2 AND headache = 2) then 1 else 0 end),0) as normal FROM ".GetTableName('morningcheck')." WHERE is_mc = 0 $condition"); //正常与异常人数
    }

    $return_data['Checkup'] = $Checkup;
    $return_data['CheckupIsNormal'] = $CheckupIsNormal;
    return $return_data;
}

function GetHealCharts($schoolid,$date){
    $start = strtotime($date);
    $end = $start + 86399;
    if(keep_MC()){
        $condition = " AND is_mc = 1";
    }else{
        $condition = " AND is_mc = 0";
    }
    $condition .= " AND schoolid = '{$schoolid}' AND createtime BETWEEN '{$start}' AND '{$end}' ";
    $symptomchart = pdo_fetch("SELECT IFNULL(SUM(case when cough = 1 then 1 else 0 end),0) as cough,IFNULL(SUM(case when vomit = 1 then 1 else 0 end),0) as vomit,IFNULL(SUM(case when trauma = 1 then 1 else 0 end),0) as trauma,IFNULL(SUM(case when diarrhea = 1 then 1 else 0 end),0) as diarrhea,IFNULL(SUM(case when cold = 1 then 1 else 0 end),0) as cold,IFNULL(SUM(case when headache = 1 then 1 else 0 end),0) as headache,IFNULL(SUM(case when mouth = 2 then 1 else 0 end),0) as mouth FROM ".GetTableName('morningcheck')." WHERE schoolid = '{$schoolid}' $condition");

    $return_data['symptomchart'] = $symptomchart;
    return $return_data;

}
function GetSymptom($schoolid,$day){
    $nowday = strtotime(date("Y-m-d",time()));
    $start= $nowday - $day*86400;
    $end = $nowday + 86399;
    if(keep_MC()){
        $condition = " AND is_mc = 1 AND createdate BETWEEN '{$start}' AND '{$end}' ";
        $AllSymptom = pdo_fetchcolumn("SELECT count(sid) FROM ".GetTableName('morningcheck')." WHERE schoolid = '{$schoolid}' AND (cough = 1 OR vomit = 1 OR trauma = 1 OR diarrhea = 1 OR cold = 1 OR headache = 1 OR mouth = 2) $condition ");
    }else{
        $condition = " AND is_mc = 0 AND createdate BETWEEN '{$start}' AND '{$end}' ";
        $AllSymptom = pdo_fetchcolumn("SELECT count(sid) FROM ".GetTableName('morningcheck')." WHERE schoolid = '{$schoolid}' AND (cough = 1 OR vomit = 1 OR trauma = 1 OR diarrhea = 1 OR cold = 1 OR headache = 1) $condition ");
    }
    $cough = pdo_fetchcolumn("SELECT count(sid) FROM ".GetTableName('morningcheck')." WHERE schoolid = '{$schoolid}' AND cough = 1 $condition");
    $vomit = pdo_fetchcolumn("SELECT count(sid) FROM ".GetTableName('morningcheck')." WHERE schoolid = '{$schoolid}' AND vomit = 1 $condition");
    $trauma = pdo_fetchcolumn("SELECT count(sid) FROM ".GetTableName('morningcheck')." WHERE schoolid = '{$schoolid}' AND trauma = 1 $condition");
    $diarrhea = pdo_fetchcolumn("SELECT count(sid) FROM ".GetTableName('morningcheck')." WHERE schoolid = '{$schoolid}' AND diarrhea = 1 $condition");
    $cold = pdo_fetchcolumn("SELECT count(sid) FROM ".GetTableName('morningcheck')." WHERE schoolid = '{$schoolid}' AND cold = 1 $condition");
    $headache = pdo_fetchcolumn("SELECT count(sid) FROM ".GetTableName('morningcheck')." WHERE schoolid = '{$schoolid}' AND headache = 1 $condition");
    $mouth = pdo_fetchcolumn("SELECT count(sid) FROM ".GetTableName('morningcheck')." WHERE schoolid = '{$schoolid}' AND mouth = 2 $condition");

    $coughbl = round($cough / $AllSymptom * 100,2).'%';
    $vomitbl = round($vomit / $AllSymptom * 100,2).'%';
    $traumabl = round($trauma / $AllSymptom * 100,2).'%';
    $diarrheabl = round($diarrhea / $AllSymptom * 100,2).'%';
    $coldbl = round($cold / $AllSymptom * 100,2).'%';
    $headachebl = round($headache / $AllSymptom * 100,2).'%';
    $mouthbl = round($mouth / $AllSymptom * 100,2).'%';
    $return_data['data'][0] = array('value' => $cough, 'name' => '咳嗽 '.$cough.'人 '.$coughbl);
    $return_data['data'][1] = array('value' => $vomit, 'name' => '呕吐 '.$vomit.'人 '.$vomitbl);
    $return_data['data'][2] = array('value' => $trauma, 'name' => '外伤 '.$trauma.'人 '.$traumabl);
    $return_data['data'][3] = array('value' => $diarrhea, 'name' => '腹泻 '.$diarrhea.'人 '.$diarrheabl);
    $return_data['data'][4] = array('value' => $cold, 'name' => '感冒 '.$cold.'人 '.$coldbl);
    $return_data['data'][5] = array('value' => $headache, 'name' => '头痛 '.$headache.'人 '.$headachebl);
    if(keep_MC()){
        $return_data['title'] = ['咳嗽 '.$cough.'人 '.$coughbl,'呕吐 '.$vomit.'人 '.$vomitbl,'外伤 '.$trauma.'人 '.$traumabl,'腹泻 '.$diarrhea.'人 '.$diarrheabl,'感冒 '.$cold.'人 '.$coldbl,'头痛 '.$headache.'人 '.$headachebl,'口腔 '.$mouth.'人 '.$mouthbl];
        $return_data['data'][6] = array('value' => $mouth, 'name' => '口腔 '.$mouth.'人 '.$mouthbl);
    }else{
        $return_data['title'] = ['咳嗽 '.$cough.'人 '.$coughbl,'呕吐 '.$vomit.'人 '.$vomitbl,'外伤 '.$trauma.'人 '.$traumabl,'腹泻 '.$diarrhea.'人 '.$diarrheabl,'感冒 '.$cold.'人 '.$coldbl,'头痛 '.$headache.'人 '.$headachebl];
    }
    return $return_data;
}
function GetTrend($schoolid, $day){
    $nowday = strtotime(date("Y-m-d",time()));
    for($i=$day;$i>=0;$i--){
        $first= $nowday-$i*86400;
        $last = $nowday-$i*86400 + 86399;
        if(keep_MC()){
            $condition = " AND is_mc = 1 AND createtime BETWEEN $first AND $last ";
        }else{
            $condition = " AND is_mc = 0 AND createtime BETWEEN $first AND $last ";
        }
        $cough[$i] = pdo_fetchcolumn("SELECT count(sid) FROM ".GetTableName('morningcheck')." WHERE schoolid = '{$schoolid}' AND cough = 1 $condition");
        $vomit[$i] = pdo_fetchcolumn("SELECT count(sid) FROM ".GetTableName('morningcheck')." WHERE schoolid = '{$schoolid}'  AND vomit = 1 $condition");
        $trauma[$i] = pdo_fetchcolumn("SELECT count(sid) FROM ".GetTableName('morningcheck')." WHERE schoolid = '{$schoolid}'  AND trauma = 1 $condition");
        $diarrhea[$i] = pdo_fetchcolumn("SELECT count(sid) FROM ".GetTableName('morningcheck')." WHERE schoolid = '{$schoolid}' AND diarrhea = 1 $condition");
        $cold[$i] = pdo_fetchcolumn("SELECT count(sid) FROM ".GetTableName('morningcheck')." WHERE schoolid = '{$schoolid}' AND cold = 1 $condition");
        $headache[$i] = pdo_fetchcolumn("SELECT count(sid) FROM ".GetTableName('morningcheck')." WHERE schoolid = '{$schoolid}' AND headache = 1 $condition");
        $mouth[$i] = pdo_fetchcolumn("SELECT count(sid) FROM ".GetTableName('morningcheck')." WHERE schoolid = '{$schoolid}' AND mouth = 2 $condition");
        $createtime[$i] = date("m-d",$first);
    }

    $return_data['createtime'] = array_values($createtime);
    $return_data['cough'] = array_values($cough);
    $return_data['vomit'] = array_values($vomit);
    $return_data['trauma'] = array_values($trauma);
    $return_data['diarrhea'] = array_values($diarrhea);
    $return_data['cold'] = array_values($cold);
    $return_data['headache'] = array_values($headache);
    $return_data['mouth'] = array_values($mouth);
    return $return_data; 
}