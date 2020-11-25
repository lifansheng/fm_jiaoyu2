<?php
/*
 * @Discription:  
 * @Author: Hannibal·Lee
 * @Date: 2020-05-30 11:46:40
 * @LastEditTime: 2020-06-06 15:14:17
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
if($_GPC['njid']){
    $njname = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where weid = :weid AND sid=:sid ", array(':weid' => $weid, ':sid' => $_GPC['njid']))['sname'];
}
$title = $_GPC['njid'] ? $njname : $school['title'];
$op = $_GPC['op'] ? $_GPC['op'] : 'display';
mload()->model('tea');
$tid_global = $it['tid'];
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
        include $this->template(''.$school['style3'].'/kqstatistics/tkqbjstatistics');
    }elseif($op == 'getKqList'){
        //传递过来的日期
        $date = $_GPC['date'] ? date("Y-m-d",(strtotime($_GPC['date']) + $_GPC['selectday'] * 86400)) :date('Y-m-d',TIMESTAMP); 
        $time = $_GPC['time'] ? $_GPC['time'] :date('H:i',TIMESTAMP); $istrue = strtotime($date) !== strtotime(date("Y-m-d",time())) ? true : false; //是否在今天(在今天不允许点击后一天)
        // $time = $istrue ? '截止23:59:59' : '截止'.date('H:i:s',TIMESTAMP); //只用作前端显示
        //当前日期对开始与结束
        $start = strtotime($date);
        $end = strtotime($date.$time);
        $list = getKqInfo($weid,$schoolid,$tid_global,$start,$end,$sf,$_GPC['njid'],'nj')['list'];
        include $this->template(''.$school['style3'].'/kqstatistics/comtool/tkqbjstatistics');
        exit;
    }elseif($op == 'getEcharts'){
        //传递过来的日期
        $date = $_GPC['date'] ? date("Y-m-d",(strtotime($_GPC['date']) + $_GPC['selectday'] * 86400)) :date('Y-m-d',TIMESTAMP); 
        $time = $_GPC['time'] ? $_GPC['time'] :date('H:i',TIMESTAMP); 
        $istrue = strtotime($date) !== strtotime(date("Y-m-d",time())) ? true : false; //是否在今天(在今天不允许点击后一天)
        //当前日期对开始与结束
        $start = strtotime($date);
        $end = strtotime($date.$time);
        $zdsTj = getKqInfo($weid,$schoolid,$tid_global,$start,$end,$sf,$_GPC['njid'],'nj')['zdsTj'];
        $zxsTj = getKqInfo($weid,$schoolid,$tid_global,$start,$end,$sf,$_GPC['njid'],'nj')['zxsTj'];
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
        $list = getQjInfo($weid,$schoolid,$tid_global,$start,$end,$sf,'nj',$_GPC['njid']);
        include $this->template(''.$school['style3'].'/kqstatistics/comtool/tkqqjlist');
        exit;
    }
}else{
    session_destroy();
	$stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
	header("location:$stopurl");
}
?>