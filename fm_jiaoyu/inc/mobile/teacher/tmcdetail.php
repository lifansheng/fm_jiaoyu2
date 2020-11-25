<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_W, $_GPC;
$weid = $_W['uniacid'];
$openid = $_W['openid'];
$schoolid = intval($_GPC['schoolid']);
$sid = intval($_GPC['sid']);
$id = intval($_GPC['id']);
$school = pdo_fetch("SELECT title,spic FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id", array(':weid' => $weid, ':id' => $schoolid));
$operation = $_GPC['op'] ? $_GPC['op'] : 'display';
if (!empty($_GPC['userid'])){
    $_SESSION['user'] = $_GPC['userid'];
}
//查询是否用户登录		
$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
if(!empty($userid)){
    if($id){
        $first = pdo_fetch("SELECT * FROM ".GetTableName('morningcheck')." WHERE id = '{$id}'");
    }else{
        $first = pdo_fetch("SELECT * FROM ".GetTableName('morningcheck')." WHERE sid = '{$sid}' ORDER BY id DESC" );
    }
    if($operation == 'display'){
        mload()->model('stu');
        $stulist = GetBjStuList($_GPC['bj_id'],$schoolid);
        $student = pdo_fetch("SELECT s_name,icon FROM ".GetTableName('students')." WHERE id = '{$first['sid']}' ");
        $bj = pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE sid = '{$first['bj_id']}' ");
    }elseif($operation == 'GetStuMcData'){
        $sid = $_GPC['sid'];
        mload()->model('mc');
        $return_data = GetStuMcData($sid,'');
    }elseif($operation == 'getMcData'){
        $nowTime = $_GPC['date'] ? strtotime($_GPC['date']) : strtotime(date("Y-m-d",time()));
        $mcdata = pdo_fetch("SELECT * FROM ".GetTableName('morningcheck')." WHERE sid = '{$_GPC['sid']}' AND createdate = '{$nowTime}'");
        include $this->template('comtool/mccominfo');
        die;
    }
    include $this->template('teacher/tmcdetail');
}else{
    session_destroy();
    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
    header("location:$stopurl");
}

?>