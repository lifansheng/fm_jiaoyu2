<?php
/*
 * @Discription:  
 * @Author: Hannibal·Lee
 * @Date: 2020-02-14 14:09:20
 * @LastEditTime: 2020-02-27 15:27:18
 */

global $_W, $_GPC;
$weid = $_W['uniacid'];
$schoolid = intval($_GPC['schoolid']);
$schooltype = $_W['schooltype'];
$openid = $_W['openid'];
//查询是否用户登录		
$userid = pdo_fetch("SELECT * FROM " . GetTableName('user') . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
$it = pdo_fetch("SELECT * FROM " . GetTableName('user'). " where weid = :weid AND id=:id ORDER BY id DESC", array(':weid' => $weid, ':id' => $userid['id']));	
$tid_global = $it['tid'];
mload()->model('tea');
$allkclist = GetAllClassInfoByTid($schoolid,2,$schooltype,$tid_global);
if($_GPC['bj_id']){
    $bj_id = $_GPC['bj_id'];
}else{
    $bj_id = $allkclist[0]['sid'];
}
$bj = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where sid = :sid", array(':sid' => $bj_id));
$school = pdo_fetch("SELECT style3,spic FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ORDER BY ssort DESC", array(':weid' => $weid, ':id' => $schoolid));
$operation = $_GPC['op'] ? $_GPC['op'] : 'display';	
$yqselect = yqselect(); //疫情选择值
if($operation == 'display'){
    // 默认获取当前疫情打卡学生
    $start = strtotime(date("Y-m-d",time()));
    $end = strtotime(date("Y-m-d",time())) + 83699;
    // $yqdk =pdo_fetchall("SELECT * FROM " . GetTableName('yqdk') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND createtime BETWEEN '{$start}' AND '{$end}' AND bj_id = '{$bj_id}' ORDER BY createtime DESC LIMIT 0,5");
    //获取当前班级所有学生
    $students = pdo_fetchall("SELECT * FROM " . GetTableName('students') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND bj_id = '{$bj_id}' ORDER BY id DESC");
    $hasdk = [];
    $nohasdk = [];
    foreach ($students as $key => $value) {
        //查询当前学生是否已经体检
        $checkdk =pdo_fetch("SELECT id FROM " . GetTableName('yqdk') . " WHERE sid = '{$value['id']}' AND createtime BETWEEN '{$start}' AND '{$end}'");
        if(!empty($checkdk)){
            //已经体检
            $hasdk[$key]['icon'] = $value['icon'] ? tomedia($value['icon']) : tomedia($school['spic']);
            $hasdk[$key]['s_name'] = $value['s_name'];
            $hasdk[$key]['id'] = $checkdk['id'];
        }else{
            if($value['s_name']){
                $nohasdk[$key]['icon'] = $value['icon'] ? tomedia($value['icon']) : tomedia($school['spic']);
                $nohasdk[$key]['s_name'] = $value['s_name'];
            }
        }
    }
}elseif($operation == 'scroll_more'){
    $time = $_GPC['LiData']['time'];
    $limit_start = $time ? $time + 1 : 0;
    $condition .= " AND bj_id = '{$_GPC['bj_id']}' ";
    $start = $_GPC['LiData']['start'] ? $_GPC['LiData']['start'] : strtotime($_GPC['start']) + 86399;
    $end = $_GPC['LiData']['end'] ? $_GPC['LiData']['end'] : strtotime($_GPC['end']) + 86399;
    if(($start != -1) && ($end != -1)){
        $condition .= " AND createtime BETWEEN '{$start}'  AND '{$end}' ";
    }
    $yqdk =pdo_fetchall("SELECT * FROM " . GetTableName('yqdk') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition ORDER BY createtime DESC LIMIT {$limit_start},5");
    foreach ($yqdk as $key => $value) {
        $xiabao = $key +1;
        $student = pdo_fetch("SELECT s_name FROM ".GetTableName('students')." WHERE id = '{$value['sid']}' limit 0,1 ");
        $last = pdo_fetch("SELECT lng,lat FROM ".GetTableName('yqdk')." WHERE schoolid= '{$schoolid}' AND sid = '{$value['sid']}' AND createtime < '{$value['createtime']}' ORDER BY id DESC limit 0,1 ");
        $range = $last ? getDistance($value['lat'],$value['lng'],$last['lat'],$last['lng']) : 0;
        $yqdk[$key]['range'] =intval($range);
        $yqdk[$key]['s_name'] =$student['s_name'];
        $yqdk[$key]['location'] = $key + $limit_start;
    }
    include $this->template('comtool/tyqdklist');
    exit;
}elseif($operation == 'DateSearch'){
    // 默认获取当前疫情打卡学生
    $start = strtotime($_GPC['start']);
    $end = $start + 83699;
    //获取当前班级所有学生
    $students = pdo_fetchall("SELECT * FROM " . GetTableName('students') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND bj_id = '{$_GPC['bj_id']}' ORDER BY id DESC");
    $hasdk = [];
    $nohasdk = [];
    foreach ($students as $key => $value) {
        //查询当前学生是否已经体检
        $checkdk =pdo_fetch("SELECT id FROM " . GetTableName('yqdk') . " WHERE sid = '{$value['id']}' AND createtime BETWEEN '{$start}' AND '{$end}'");
        if(!empty($checkdk)){
            //已经体检
            $hasdk[$key]['icon'] = $value['icon'] ? tomedia($value['icon']) : tomedia($school['spic']);
            $hasdk[$key]['s_name'] = $value['s_name'];
            $hasdk[$key]['id'] = $checkdk['id'];
        }else{
            if($value['s_name']){
                $nohasdk[$key]['icon'] = $value['icon'] ? tomedia($value['icon']) : tomedia($school['spic']);
                $nohasdk[$key]['s_name'] = $value['s_name'];
            }
        }
    }
    include $this->template('comtool/tyqdklist');
    exit;
}

if(!empty($it)){
    include $this->template(''.$school['style3'].'/tyqdklist');
}else{
    session_destroy();
    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
    header("location:$stopurl");
}     
   
