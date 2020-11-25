<?php
/*
 * @Discription:  
 * @Author: Hannibal·Lee
 * @Date: 2020-02-26 14:17:06
 * @LastEditTime: 2020-02-27 16:52:41
 */

global $_W, $_GPC;
$weid = $_W['uniacid'];
$schoolid = intval($_GPC['schoolid']);
$openid = $_W['openid'];
load()->func('tpl');
//查询是否用户登录		
$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');

$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ORDER BY ssort DESC", array(':weid' => $weid, ':id' => $schoolid));

$teacher = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $_W ['uniacid'], ':id' => $it['tid']));

$tid_global = $it['tid'];	
$operation = $_GPC['op'] ? $_GPC['op'] : 'display';		
$bj_id = intval($_GPC['bj_id']);
//期号			
$qh    = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = '{$weid}' AND schoolid ={$schoolid} And type = 'xq_score' AND is_show_qh = 1 ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'score', ':schoolid' => $schoolid));
$nowbj = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where sid = :sid ", array(':sid' => $bj_id));
if($operation == 'display'){
    
}elseif($operation == 'GetStuMcData'){
    $qh_id = $_GPC['qh_id'];
    $qhdata = pdo_fetch("SELECT sd_start,sd_end FROM ".GetTableName('classify')." WHERE sid = '{$qh_id}' ");
    if(!empty($qhdata)){
        $condition = " AND m.createdate BETWEEN '{$qhdata['sd_start']}' AND '{$qhdata['sd_end']}' ";
    }
    //统计最新一条身高体重视力数据
    $mc = pdo_fetchall("SELECT s.id,m.weight,m.lefteye,m.righteye,m.height FROM ".GetTableName('students')." as s LEFT JOIN (select * from " . GetTableName('morningcheck') . " order by createdate desc) as m on s.id = m.sid WHERE s.bj_id = '{$_GPC['bj_id']}' $condition GROUP BY m.sid ORDER BY m.id DESC ");
    $heightcount = [];
    $weightcount = [];
    $leftcount = [];
    $rightcount = [];

    foreach ($mc as $key => $value) {
        // 身高数据
        if(intval($value['height']) < 80){
            $heightcount['70']++;
        }elseif($value['height'] >= 140){
            $heightcount['140']++;
        }else{
            $i = floor($value['height'] / 10) * 10;
            $heightcount[$i]++;
        }
        // 体重数据
        if(intval($value['weight']) < 20){
            $weightcount['10']++;
        }elseif($value['weight'] >= 80){
            $weightcount['80']++;
        }else{
            $i = floor($value['weight'] / 10) * 10;
            $weightcount[$i]++;
        }
        // 左眼数据
        if(intval($value['lefteye']) < 1){
            $leftcount['0']++;
        }elseif($value['lefteye'] >= 4){
            $leftcount['4']++;
        }else{
            $i = floor($value['lefteye']);
            $leftcount[$i]++;
        }
        // 右眼数据
        if(intval($value['righteye']) < 1){
            $rightcount['0']++;
        }elseif($value['righteye'] >= 4){
            $rightcount['4']++;
        }else{
            $i = floor($value['righteye']);
            $rightcount[$i]++;
        }
    }
    // 身高数据
    for ($i=70; $i <= 140 ; $i+=10) { 
        $h_num[] = $heightcount[$i] ? $heightcount[$i] : 0;
    }
    // 体重数据
    for ($i=10; $i <= 80 ; $i+=10) { 
        $w_num[] = $weightcount[$i] ? $weightcount[$i] : 0;
    }
    // 左眼数据
    for ($i=0; $i <= 4 ; $i++) { 
        $l_num[] = $leftcount[$i] ? $leftcount[$i] : 0;
    }
    // 右眼数据
    for ($i=0; $i <= 4 ; $i++) { 
        $r_num[] = $rightcount[$i] ? $rightcount[$i] : 0;
    }
    

    /*******体温,口腔********/
    // 获取当前班级下的所有学生数量
    $countstu = pdo_fetchcolumn("SELECT count(*) FROM ".GetTableName('students')." WHERE schoolid = '{$schoolid}' AND bj_id = '{$_GPC['bj_id']}'");

    $nowday = strtotime(date("Y-m-d",time()));
    $tiwennum = [];
    $mouthnum = [];
    for($i=13;$i>=0;$i--){
        $first= $nowday-$i*86400;

        /********************************************体温检测情况**************************************/ 
        //当日体温正常人数
        $tiwennormal = pdo_fetchcolumn("SELECT COUNT(id) FROM " . GetTableName('morningcheck') . " WHERE schoolid = :schoolid AND bj_id = '{$_GPC['bj_id']}' And createdate = :createdate AND tiwen BETWEEN 35.5 AND 37.5", array(':schoolid' => $schoolid, ':createdate' => $first));
        //当日体温异常人数
        $tiwennonormal = pdo_fetchcolumn("SELECT COUNT(id) FROM " . GetTableName('morningcheck') . " WHERE schoolid = :schoolid  AND bj_id = '{$_GPC['bj_id']}' And createdate = :createdate AND (tiwen < 35.5 OR tiwen > 37.5)", array(':schoolid' => $schoolid, ':createdate' => $first));
        $tiwennum[$i]['createdate'] = date("m.d",$first);
        $tiwennum[$i]['tiwennormal'] = intval($tiwennormal);	//正常体温
        $tiwennum[$i]['tiwennonormal'] = intval($tiwennonormal); //不正常体温
        /********************************************体温检测情况**************************************/ 

        /********************************************口腔检测情况**************************************/ 
        //当日体温正常人数
        $mouthnormal = pdo_fetchcolumn("SELECT COUNT(id) FROM " . GetTableName('morningcheck') . " WHERE schoolid = :schoolid AND bj_id = '{$_GPC['bj_id']}' And createdate = :createdate AND mouth = :mouth", array(':schoolid' => $schoolid, ':createdate' => $first,':mouth' => 1));
        //当日体温异常人数
        $mouthnonormal = pdo_fetchcolumn("SELECT COUNT(id) FROM " . GetTableName('morningcheck') . " WHERE schoolid = :schoolid AND bj_id = '{$_GPC['bj_id']}' And createdate = :createdate AND mouth = :mouth", array(':schoolid' => $schoolid, ':createdate' => $first,':mouth' => 2));
        $mouthnum[$i]['mouthnormal'] = intval($mouthnormal);	//正常口腔
        $mouthnum[$i]['mouthnonormal'] = intval($mouthnonormal); //异常口腔
        $mouthnum[$i]['mouthnoexamine'] = intval($countstu) - intval($mouthnormal) - intval($mouthnonormal); //未检测口腔
        /********************************************口腔检测情况**************************************/ 

    }
    $return_data['createdate'] = array_values(array_column($tiwennum,'createdate'));//每天的日期
	$return_data['tiwennormal'] = array_values(array_column($tiwennum,'tiwennormal'));//每天正常体温人数
	$return_data['tiwennonormal'] = array_values(array_column($tiwennum,'tiwennonormal'));//每天异常体温人数
    $return_data['mouthnormal'] = array_values(array_column($mouthnum,'mouthnormal'));//每天正常口腔人数
	$return_data['mouthnonormal'] = array_values(array_column($mouthnum,'mouthnonormal'));//每天异常口腔人数
	$return_data['mouthnoexamine'] = array_values(array_column($mouthnum,'mouthnoexamine'));//每天未检测口腔人数
    /*******体温,口腔********/
    $w_title = ['20以下','20-30','30-40','40-50','50-60','60-70','70-80','80以上']; // 体重数据
    $h_title = ['80以下','80-90','90-100','100-110','110-120','120-130','130-140','140以上']; // 身高数据
    $e_title = array('1.0以下','1.0-2.0','2.0-3.0','3.0-4.0','4.0-5.0'); // 视力数据
    $return_data['w_title'] = $w_title; // 体重数据
    $return_data['h_title'] = $h_title; // 身高数据
    $return_data['e_title'] = $e_title; // 视力数据
    $return_data['l_num'] = $l_num; // 左眼数据
    $return_data['r_num'] = $r_num; // 右眼数据
    $return_data['h_num'] = $h_num; // 身高数据
    $return_data['w_num'] = $w_num; // 体重数据
    die(json_encode($return_data));
}

if(!empty($it['id']) && $it['sid'] == 0){
    include $this->template(''.$school['style3'].'/tmcechars');
}else{
    session_destroy();
    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
    header("location:$stopurl");
}     
   
