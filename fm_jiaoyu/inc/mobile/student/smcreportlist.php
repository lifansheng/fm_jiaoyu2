<?php
/*
 * @Discription:  
 * @Author: Hannibal·Lee
 * @Date: 2020-02-14 14:09:20
 * @LastEditTime: 2020-03-07 16:37:16
 */

global $_W, $_GPC;
$weid = $_W['uniacid'];
$schoolid = intval($_GPC['schoolid']);
$openid = $_W['openid'];
//查询是否用户登录		
$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where id = :id ", array(':id' => $_SESSION['user']));
$student = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where weid = :weid AND id = :id", array(':weid' => $_W ['uniacid'], ':id' => $it['sid']));
$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ORDER BY ssort DESC", array(':weid' => $weid, ':id' => $schoolid));
$operation = $_GPC['op'] ? $_GPC['op'] : 'display';	

if($operation == 'display'){
    $list = pdo_fetchall("SELECT m.*,s.s_name,s.icon,c.sname FROM ".GetTableName('mcreportlist')." as m LEFT JOIN " . GetTableName('students') . " as s ON s.id = m.sid LEFT JOIN " . GetTableName('classify') . " as c ON c.sid = s.bj_id WHERE m.schoolid = '{$schoolid}' AND m.sid = '{$it['sid']}' ORDER BY m.id DESC LIMIT 0,10");
}elseif($operation == 'More_Data'){
    $limit_start = $_GPC['LiData']['time'] ? $_GPC['LiData']['time'] + 1 : 0 ;
    $list = pdo_fetchall("SELECT m.*,s.s_name,s.icon,c.sname FROM ".GetTableName('mcreportlist')." as m LEFT JOIN " . GetTableName('students') . " as s ON s.id = m.sid LEFT JOIN " . GetTableName('classify') . " as c ON c.sid = s.bj_id WHERE m.schoolid = '{$schoolid}' AND m.sid = '{$it['sid']}' ORDER BY m.id DESC LIMIT {$limit_start},10");
    foreach ($list as $key => $value) {
        $list[$key]['location'] = $key + $limit_start;
    }
    include $this->template('comtool/smcreportlist');
    exit;
}elseif($operation == 'getStudentReport'){
    mload()->model('znl');
    $StudentList = znlGetReport($schoolid,$_GPC['sid'],'','',0);
    if($StudentList['result']){
        $result['status'] =true;
        $result['msg'] = "获取学生报告成功";
    }else{
        $result['status'] =false;
        $result['msg'] = "没有新数据,请下次再来获取";
    }
    die(json_encode($result));
}

if(!empty($it['sid'])){
    include $this->template(''.$school['style2'].'/smcreportlist');
}else{
    session_destroy();
    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
    header("location:$stopurl");
}     
   
