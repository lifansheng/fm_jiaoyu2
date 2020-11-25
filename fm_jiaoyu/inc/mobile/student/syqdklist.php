<?php
/*
 * @Discription:  
 * @Author: Hannibal·Lee
 * @Date: 2020-02-14 14:09:20
 * @LastEditTime: 2020-02-19 18:00:01
 */

global $_W, $_GPC;
$weid = $_W['uniacid'];
$schoolid = intval($_GPC['schoolid']);
$openid = $_W['openid'];
//查询是否用户登录		
$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where id = :id ", array(':id' => $_SESSION['user']));
$student = pdo_fetch("SELECT icon,s_name,bj_id FROM " . tablename($this->table_students) . " where weid = :weid AND id = :id", array(':weid' => $_W ['uniacid'], ':id' => $it['sid']));
$bj = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where sid = :sid", array(':sid' => $student['bj_id']));
$school = pdo_fetch("SELECT style2 FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ORDER BY ssort DESC", array(':weid' => $weid, ':id' => $schoolid));
$operation = $_GPC['op'] ? $_GPC['op'] : 'display';	
$yqselect = yqselect(); //疫情选择值

if($operation == 'display'){
    $yqdk =pdo_fetchall("SELECT * FROM " . GetTableName('yqdk') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' And sid = '{$it['sid']}' ORDER BY createtime DESC LIMIT 0,4");
    foreach ($yqdk as $key => $value) {
        $xiabao = $key +1;
        $last = pdo_fetch("SELECT lng,lat FROM ".GetTableName('yqdk')." WHERE schoolid= '{$schoolid}' AND sid = '{$value['sid']}' AND createtime < '{$value['createtime']}' ORDER BY id DESC limit 0,1 ");
        $range = $last ? getDistance($value['lat'],$value['lng'],$last['lat'],$last['lng']) : 0;
        $yqdk[$key]['range'] =intval($range);
        $content = unserialize($value['content']);
        $yqdk[$key]['content'] = unserialize($value['content']);
    }
}elseif($operation == 'scroll_more'){
    $time = $_GPC['LiData']['time'];
    $limit_start = $time + 1;
    $yqdk =pdo_fetchall("SELECT * FROM " . GetTableName('yqdk') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' And sid = '{$it['sid']}' ORDER BY createtime DESC LIMIT {$limit_start},10");
    foreach ($yqdk as $key => $value) {
        $xiabao = $key +1;
        $last = pdo_fetch("SELECT lng,lat FROM ".GetTableName('yqdk')." WHERE schoolid= '{$schoolid}' AND sid = '{$value['sid']}' AND createtime < '{$value['createtime']}' ORDER BY id DESC limit 0,1 ");
        $range = $last ? getDistance($value['lat'],$value['lng'],$last['lat'],$last['lng']) : 0;
        $yqdk[$key]['range'] =intval($range);
        $content = unserialize($value['content']);
        $yqdk[$key]['content'] = unserialize($value['content']);
        $yqdk[$key]['location'] = $key + $limit_start;
    }
    include $this->template('comtool/syqdklist');
    exit;
}

if(!empty($it['sid'])){
    include $this->template(''.$school['style2'].'/syqdklist');
}else{
    session_destroy();
    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
    header("location:$stopurl");
}     
   
