<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_W, $_GPC;
$weid = $_W['uniacid'];
$schoolid = intval($_GPC['schoolid']);
$openid = $_W['openid'];
$schooltype  = $_W['schooltype'];
$operation = $_GPC['op'] ?  $_GPC['op'] : 'display';
//查询是否用户登录		
$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ORDER BY id DESC", array(':weid' => $weid, ':id' => $userid['id']));	
$tid_global = $it['tid'];
$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ORDER BY ssort DESC", array(':weid' => $weid, ':id' => $schoolid));
$teachers = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $it['tid']));	
mload()->model('tea');
$bjlists = GetAllClassInfoByTid($schoolid,2,$schooltype,$it['tid']);
if(!empty($_GPC['bj_id'])){
    $bj_id = intval($_GPC['bj_id']);			
}else{
    $bj_id = intval($bjlists[0]['sid']);
}
$nowbj = pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE sid='{$bj_id}' ");
if($operation == 'display'){


    if($_GPC['ctype'] == '-1'){
        $condition = '';
    }elseif($_GPC['ctype'] == 1){
        $condition = " AND i.idcard is NOT Null";
    }elseif($_GPC['ctype'] == 2){
        $condition = " AND i.idcard is Null";
    }
    //数量统计
    $AllNum = pdo_fetchcolumn("SELECT count(s.id) FROM " . GetTableName('students') . " as s LEFT JOIN (SELECT id,sid,pard,cardtype FROM ". GetTableName('idcard') . " WHERE pard = 1 AND cardtype = 1 AND schoolid = '{$schoolid}') as i on s.id = i.sid WHERE s.weid = '{$weid}' AND s.schoolid = '{$schoolid}' AND s.bj_id = '{$bj_id}'");
    $HasBdNum = pdo_fetchcolumn("SELECT count(s.id) FROM " . GetTableName('students') . " as s LEFT JOIN (SELECT id,sid,pard,cardtype,idcard FROM ". GetTableName('idcard') . " WHERE pard = 1 AND cardtype = 1 AND schoolid = '{$schoolid}') as i on s.id = i.sid WHERE s.weid = '{$weid}' AND s.schoolid = '{$schoolid}' AND s.bj_id = '{$bj_id}' AND i.idcard is NOT Null");
    
    $HasNoBdNum = pdo_fetchcolumn("SELECT count(s.id) FROM " . GetTableName('students') . " as s LEFT JOIN (SELECT id,sid,pard,cardtype,idcard FROM ". GetTableName('idcard') . " WHERE pard = 1 AND cardtype = 1 AND schoolid = '{$schoolid}') as i on s.id = i.sid WHERE s.weid = '{$weid}' AND s.schoolid = '{$schoolid}' AND s.bj_id = '{$bj_id}' AND i.idcard is Null");

    $list = pdo_fetchall("SELECT s.s_name,s.id,s.icon,i.spic,i.pard,i.id as cid, i.idcard,s.sex,i.createtime,i.severend,s.mobile FROM " . GetTableName('students') . " as s LEFT JOIN (SELECT id,sid,pard,cardtype,spic,idcard,createtime,severend FROM ". GetTableName('idcard') . " WHERE pard = 1 AND cardtype = 1 AND schoolid = '{$schoolid}') as i on s.id = i.sid WHERE s.weid = '{$weid}' AND s.schoolid = '{$schoolid}' AND s.bj_id = '{$bj_id}'  $condition ORDER BY s.id DESC LIMIT 0,20");

    include $this->template(''.$school['style3'].'/tcardlist');	
}elseif($operation == 'scroll_more'){
    $limit = $_GPC['limit'];
    $Ctype = $_GPC['LiData']['ctype'] ;
    $page_start = $limit + 1 ;
    if($Ctype == '-1'){
        $condition = '';
    }elseif($Ctype == 1){
        $condition = " AND i.idcard is NOT Null";
    }elseif($Ctype == 2){
        $condition = " AND i.idcard is Null";
    }
    $list = pdo_fetchall("SELECT s.s_name,s.id,s.icon,i.spic,i.pard,i.id as cid, i.idcard,s.sex,i.createtime,i.severend,s.mobile FROM " . GetTableName('students') . " as s LEFT JOIN (SELECT id,sid,pard,cardtype,spic,idcard,createtime,severend FROM ". GetTableName('idcard') . " WHERE pard = 1 AND cardtype = 1 AND schoolid = '{$schoolid}') as i on s.id = i.sid WHERE s.weid = '{$weid}' AND s.schoolid = '{$schoolid}' AND s.bj_id = '{$bj_id}' $condition ORDER BY s.id DESC LIMIT ".$page_start.",20");
    foreach ($list as $key => $value) {
        $list[$key]['location'] = $key + $page_start;
    }
    include $this->template('comtool/tcardlist');
    exit;
}
if(empty($userid['id'])){
    session_destroy();
    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
    header("location:$stopurl");
}		
?>