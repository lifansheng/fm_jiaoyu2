<?php
/*
 * @Discription:  
 * @Author: Hannibal·Lee
 * @Date: 2020-05-30 11:46:03
 * @LastEditTime: 2020-06-05 16:09:07
 */ 
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_W, $_GPC;
$weid = $_W['uniacid'];
$schoolid = intval($_GPC['schoolid']);
$openid = $_W['openid'];
$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid And :schoolid = schoolid And :openid = openid And :sid = sid", array(':weid' => $weid,':schoolid' => $schoolid,':openid' => $openid,':sid' => 0));
$school = pdo_fetch("SELECT title,style3,headcolor FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
$title = $school['title'];
$op = $_GPC['op'] ? $_GPC['op'] : 'display';
mload()->model('tea');
$tid_global = $it['tid'];
$allkclist = GetAllClassInfoByTid($schoolid,2,$_W['schooltype'],$tid_global);
$sf = '';

$Njlist = pdo_fetchall("SELECT sid,sname FROM ".GetTableName('classify')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' and type = 'semester'  ",array(),'sid');
$Bjlist = pdo_fetchall("SELECT sid,sname FROM ".GetTableName('classify')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' and type = 'theclass'  ",array(),'sid');

mload()->model('kq');
if(!empty($it)){
    if(is_njzr($tid_global) || (is_xiaozhang($tid_global) || $tid_global == 'founder' || $tid_global == 'owner')){
        if(is_njzr($tid_global)){
        $sf = 'director';
        }else{
            $sf = 'xiaozhang';
        }
    }else{
        $sf = 'teachers';
    }
    if($op == 'display'){
        if($sf == 'teachers'){
            include $this->template(''.$school['style3'].'/kqstatistics/tkqbjstatistics');
        }else{
            include $this->template(''.$school['style3'].'/kqstatistics/tkqstatistics');
        }
    }elseif($op == 'getKqList'){
        //传递过来的日期
        $date = $_GPC['date'] ? date("Y-m-d",(strtotime($_GPC['date']) + $_GPC['selectday'] * 86400)) :date('Y-m-d',TIMESTAMP); 
        $time = $_GPC['time'] ? $_GPC['time'] :date('H:i',TIMESTAMP); 
        $istrue = strtotime($date) !== strtotime(date("Y-m-d",time())) ? true : false; //是否在今天(在今天不允许点击后一天)
        // $time = $istrue ? '23:59' : $time; //只用作前端显示
        //当前日期对开始与结束
        $start = strtotime($date);
        $end = strtotime($date.$time);
        $list = getKqInfo($weid,$schoolid,$tid_global,$start,$end,$sf)['list'];
        include $this->template(''.$school['style3'].'/kqstatistics/comtool/tkqstatistics');
        exit;
    }elseif($op == 'getEcharts'){
        //传递过来的日期
        $date = $_GPC['date'] ? date("Y-m-d",(strtotime($_GPC['date']) + $_GPC['selectday'] * 86400)) :date('Y-m-d',TIMESTAMP); 
        $time = $_GPC['time'] ? $_GPC['time'] :date('H:i',TIMESTAMP); 
        $istrue = strtotime($date) !== strtotime(date("Y-m-d",time())) ? true : false; //是否在今天(在今天不允许点击后一天)
        //当前日期对开始与结束
        $start = strtotime($date);
        $end = strtotime($date.$time);
        $zdsTj = getKqInfo($weid,$schoolid,$tid_global,$start,$end,$sf)['zdsTj'];
        $zxsTj = getKqInfo($weid,$schoolid,$tid_global,$start,$end,$sf)['zxsTj'];
        $result_data['zdsTj'] = $zdsTj;
        $result_data['zxsTj'] = $zxsTj;
        $result_data['istrue'] = $istrue;
        $result_data['date'] = $date;
        $result_data['time'] = $time;
        die(json_encode($result_data));
    }elseif($op == 'getQjList'){
        //传递过来的日期
        $date = $_GPC['date'] ? date("Y-m-d",(strtotime($_GPC['date']) + $_GPC['selectday'] * 86400)) :date('Y-m-d',TIMESTAMP); 
        $time = $_GPC['time'] ? $_GPC['time'] :date('H:i',TIMESTAMP); $istrue = strtotime($date) !== strtotime(date("Y-m-d",time())) ? true : false; //是否在今天(在今天不允许点击后一天)
        // $time = $istrue ? '截止23:59:59' : '截止'.date('H:i:s',TIMESTAMP); //只用作前端显示
        //当前日期对开始与结束
        $start = strtotime($date);
        $end = strtotime($date.$time);
        $list = getQjInfo($weid,$schoolid,$tid_global,$start,$end,$sf);
        include $this->template(''.$school['style3'].'/kqstatistics/comtool/tkqqjlist');
        exit;
    }
}else{
    session_destroy();
	$stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
	header("location:$stopurl");
}
?>