<?php

global $_W, $_GPC;
$weid = $_W['uniacid'];
$schoolid = intval($_GPC['schoolid']);
$openid = $_W['openid'];
load()->func('tpl');
//查询是否用户登录		
$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ORDER BY id DESC", array(':weid' => $weid, ':id' => $userid['id']));		
$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ORDER BY ssort DESC", array(':weid' => $weid, ':id' => $schoolid));
$teacher = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $_W ['uniacid'], ':id' => $it['tid']));
$tid_global = $it['tid'];	
$operation = $_GPC['op'] ? $_GPC['op'] : 'display';		
$fisrtbj =  pdo_fetch("SELECT bj_id FROM " . tablename($this->table_class) . " where weid = '{$weid}' And schoolid = '{$schoolid}'  And tid = {$it['tid']} ");
$bjlists = get_mylist($schoolid,$it['tid'],'teacher');	
if(keep_MC()){
    $condition2 = " AND is_mc = 1";
}else{
    $condition2 = " AND is_mc = 0";
}
if(!empty($_GPC['bj_id'])){
    $bj_id = intval($_GPC['bj_id']);			
}else{
    $bj_id = $fisrtbj['bj_id'];
}
$nowbj = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where sid = :sid ", array(':sid' => $bj_id));
if(is_njzr($teacher['id'])){
    $mynjlist = pdo_fetchall("SELECT * FROM " . tablename($this->table_classify) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And tid = '{$it['tid']}' And type = 'semester' ORDER BY ssort DESC");
    foreach($mynjlist as $key =>$row){
        $mynjlist[$key]['bjlist'] = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And parentid = '{$row['sid']}' And type = 'theclass' ORDER BY sid ASC, ssort DESC");
    }
}else{
    if($teacher['status'] == 2){
        $bjlist = pdo_fetchall("SELECT * FROM " . tablename($this->table_classify) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And type = 'theclass' ORDER BY sid ASC, ssort DESC");
    }			
}
if($operation == 'display'){
    $nowdate = strtotime(date("Y-m-d"));
    $condition .= " And bj_id = '{$bj_id}'";	
    //获取班级学生并进行记录体温列表
    $students =pdo_fetchall("SELECT s.id,s.bj_id,s.s_name,s.icon,(SELECT count(id) FROM " . GetTableName('morningcheck') ." as m WHERE s.id = m.sid AND createdate = '{$nowdate}' $condition2) as smc FROM " . GetTableName('students') . " as s WHERE s.weid = '{$weid}' AND s.schoolid = '{$schoolid}'  And s.bj_id = '{$bj_id}' ORDER BY s.id DESC");
    
    //获取体温记录统计数据
    $checkdata =pdo_fetchall("SELECT createdate,tiwen,bj_id FROM " . GetTableName('morningcheck') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition $condition2 GROUP BY createdate ORDER BY createdate DESC LIMIT 0,10");
    foreach ($checkdata as $key => $value) {
        //总人数
        $countstu =pdo_fetchcolumn("SELECT count(id) FROM " . tablename($this->table_students) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND bj_id = '{$value['bj_id']}' ");
        //已检测的人数
        $scount =pdo_fetchcolumn("SELECT count(id) FROM " . GetTableName('morningcheck') . " WHERE createdate = '{$value['createdate']}' $condition2 $condition");
        $checkdata[$key]['scount'] = $scount;
        $checkdata[$key]['ecount'] = $countstu - $scount;
        $istoday = $value['createdate'] == $nowdate ? true : false;
        if($istoday){
            $checkdata[$key]['istoday'] = $istoday;
        }else{
            $checkdata[$key]['day'] = date("d", $value['createdate']);
            $checkdata[$key]['month'] = date("Y年-m月", $value['createdate']);
        }
    }
}elseif($operation == 'scroll_more'){
		$time = $_GPC['LiData']['time'];
        $ctype = $_GPC['LiData']['ctype'] ? $_GPC['LiData']['ctype'] : $_GPC['ctype'];
        $limit_start = $time ? $time + 1 : 0;
        $condition .= " And bj_id = '{$_GPC['bj_id']}'";
        $nowdate = strtotime(date("Y-m-d"));
        if($ctype == 'record'){
            $students =pdo_fetchall("SELECT * FROM " . tablename($this->table_students) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition AND id NOT IN (SELECT sid FROM ".GetTableName('morningcheck')." WHERE createdate = '{$nowdate}' $condition2) ORDER BY id DESC LIMIT {$limit_start},10");
            foreach ($students as $key => $value) {
                $students[$key]['location'] = $key + $limit_start;
            }
        }
        if($ctype == 'statistics'){
            if($_GPC['start']){
                $start = strtotime($_GPC['start']);
                $condition .= " And createdate >= '{$start}'";
            }
            if($_GPC['end']){
                $end = strtotime($_GPC['end']);
                $condition .= " And createdate <= '{$end}'";
            }

            //获取体温记录统计数据
            $checkdata =pdo_fetchall("SELECT createdate,tiwen FROM " . GetTableName('morningcheck') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition2 $condition GROUP BY createdate ORDER BY createdate DESC LIMIT {$limit_start},10");
            foreach ($checkdata as $key => $value) {
                //统计正常的数据
                $scount =pdo_fetchcolumn("SELECT count(id) FROM " . GetTableName('morningcheck') . " WHERE createdate = '{$value['createdate']}' AND tiwen between 35.5 and 37.5 $condition2");
                //不正常的数据
                $ecount =pdo_fetchcolumn("SELECT count(id) FROM " . GetTableName('morningcheck') . " WHERE createdate = '{$value['createdate']}' AND (tiwen < 35.5 OR tiwen > 37.5) $condition2");
                $checkdata[$key]['scount'] = $scount;
                $checkdata[$key]['ecount'] = $ecount;
                $istoday = $value['createdate'] == $nowdate ? true : false;
                if($istoday){
                    $checkdata[$key]['istoday'] = $istoday;
                }else{
                    $checkdata[$key]['day'] = date("d", $value['createdate']);
                    $checkdata[$key]['month'] = date("Y年-m月", $value['createdate']);
                }
                $checkdata[$key]['location'] = $key + $limit_start;
            }
        }
        include $this->template('comtool/morningcheck');
        exit;
	}


if(!empty($userid['id']) && $userid['sid'] == 0){
    include $this->template(''.$school['style3'].'/morningcheck');
}else{
    session_destroy();
    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
    header("location:$stopurl");
}     
   
