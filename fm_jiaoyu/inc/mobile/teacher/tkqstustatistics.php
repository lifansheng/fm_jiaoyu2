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
$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid And :schoolid = schoolid And :openid = openid And :sid = sid", array(':weid' => $weid,':schoolid' => $schoolid,':openid' => $openid,':sid' => 0));
$school = pdo_fetch("SELECT title,style3,headcolor FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
$op = $_GPC['op'] ? $_GPC['op'] : 'display';
$title = pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE sid = '{$_GPC['bj_id']}' ")['sname'];
mload()->model('tea');
$tid_global = $it['tid'];
mload()->model('kq');
if(!empty($it)){
    if($op == 'display'){
        include $this->template(''.$school['style3'].'/kqstatistics/tkqstustatistics');
    }elseif($op == 'getKqList'){
        //传递过来的日期
        $date = $_GPC['date'] ? date("Y-m-d",(strtotime($_GPC['date']) + $_GPC['selectday'] * 86400)) :date('Y-m-d',TIMESTAMP); 
        $time = $_GPC['time'] ? $_GPC['time'] :date('H:i',TIMESTAMP); 
        $istrue = strtotime($date) !== strtotime(date("Y-m-d",time())) ? true : false; //是否在今天(在今天不允许点击后一天)
        // $time = $istrue ? '截止23:59:59' : '截止'.date('H:i:s',TIMESTAMP); //只用作前端显示
        //当前日期对开始与结束
        $start = strtotime($date);
        $end = strtotime($date.$time);
        $list = getStuKqInfo($weid,$schoolid,$start,$end,$_GPC['bj_id'])['list'];
        include $this->template(''.$school['style3'].'/kqstatistics/comtool/tkqstustatistics');
        exit;
    }elseif($op == 'getEcharts'){
        //传递过来的日期
        $date = $_GPC['date'] ? date("Y-m-d",(strtotime($_GPC['date']) + $_GPC['selectday'] * 86400)) :date('Y-m-d',TIMESTAMP); 
        $time = $_GPC['time'] ? $_GPC['time'] :date('H:i',TIMESTAMP); 
        $istrue = strtotime($date) !== strtotime(date("Y-m-d",time())) ? true : false; //是否在今天(在今天不允许点击后一天)
        //当前日期对开始与结束
        $start = strtotime($date);
        $end = strtotime($date.$time);
        $zdsTj = getStuKqInfo($weid,$schoolid,$start,$end,$_GPC['bj_id'])['zdsTj'];
        $zxsTj = getStuKqInfo($weid,$schoolid,$start,$end,$_GPC['bj_id'])['zxsTj'];
        $result_data['zdsTj'] = $zdsTj;
        $result_data['zxsTj'] = $zxsTj;
        $result_data['istrue'] = $istrue;
        $result_data['date'] = $date;
        $result_data['time'] = $time;
        die(json_encode($result_data));
    }
}else{
    session_destroy();
	$stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
	header("location:$stopurl");
}
?>