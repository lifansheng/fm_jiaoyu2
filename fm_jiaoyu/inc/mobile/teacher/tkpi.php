<?php
/**
 * 教师端 查看 进出校 以及 归寝 考勤记录 
 * @copyright 2019 微美科技
 * @author Hannibal·Lee <No@email.com>
 */

global $_W, $_GPC;
$weid = $_W['uniacid'];
$schoolid = intval($_GPC['schoolid']);
$openid = $_W['openid'];
$opereation = $_GPC['op'] ? $_GPC['op'] : 'display';
$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $userid['id']));
$tid = $it['tid'];
$tid_global = $it['tid'];
mload()->model('kc');
$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
if(!empty($it)){
    $kclist = pdo_fetchall('SELECT id,name FROM ' . tablename($this->table_tcourse) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND FIND_IN_SET($tid_global,tid) ORDER BY id");
    if(empty($kclist)){
        die();
    }
    if($opereation == 'display'){

        $year = $_GPC['year'] ? $_GPC['year'] : date('Y');
        $type = $_GPC['type'] ? $_GPC['type'] : 1;
        $list = GetDfl($weid,$schoolid,$year,$type,0);

        include $this->template(''.$school['style3'].'/tkpi');
    }elseif($opereation == 'GetFirstData'){
        $year = $_GPC['year'] ? $_GPC['year'] : date('Y');
        $type = $_GPC['type'] ? $_GPC['type'] : 1;
        if($type == 1){
            $num = 12;
        }elseif($type == 2){
            $num = 4;
        }elseif($type == 3){
            $num = 2;
        }elseif($type == 4){
            $num = 1;
        }
        $list = GetDfl($weid,$schoolid,$year,$type,0);
        include $this->template('comtool/tkpi_bot');
    }
}else{
    session_destroy();
    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
    header("location:$stopurl");
    exit;
}