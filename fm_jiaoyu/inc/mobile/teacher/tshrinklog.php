<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_W, $_GPC;
$weid = $this->weid;
$from_user = $this->_fromuser;
$schoolid = intval($_GPC['schoolid']);
$openid = $_W['openid'];
$it = pdo_fetch("SELECT id,tid FROM " . tablename($this->table_user) . " where  weid = :weid And schoolid = :schoolid And openid = :openid And sid = :sid ", array(
	':weid' => $weid,
	':schoolid' => $schoolid,
	':openid' => $openid,
	':sid' => 0
));
$tid_global = $it['tid'] ? $it['tid'] : 0;
if (!empty($schoolid) && empty($it)) {
	$stopurl = $_W['siteroot'] . 'app/' . $this->createMobileUrl('bangding', array('schoolid' => $schoolid));
	header("location:$stopurl");
	exit;
}
//教师列表按教师入职时间先后顺序排列，先入职再前
$list = pdo_fetchall("SELECT * FROM ".GetTableName('psychology')." WHERE schoolid = '{$schoolid}' AND touserid = '{$it['id']}' GROUP BY leaveid ORDER BY createtime DESC");
foreach ($list as $key => $value) {
    if($value['sendtype'] == 'fanstotea'){ // 表示粉丝发送给老师
        $fans = mc_fansinfo($value['openid']);
        $list[$key]['name'] = $fans['nickname'];
        $list[$key]['thumb'] = $fans['headimgurl'];
        $list[$key]['desc'] = '粉丝';
    }else{ // 学生发送给老师
        $sid = pdo_fetch("SELECT sid FROM ".GetTableName('user')." WHERE schoolid = :schoolid AND id = :id ",array(':schoolid'=>$schoolid,':id'=>$value['userid']))['sid'];
        $student = pdo_fetch("SELECT s_name,icon,bj_id FROM ".GetTableName('students')." WHERE schoolid = :schoolid AND id = :id ",array(':schoolid'=>$schoolid,':id'=>$sid));
        $bjname = pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE schoolid = :schoolid AND sid = :sid ",array(':schoolid'=>$schoolid,':sid'=>$student['bj_id']))['sname'];
        $list[$key]['name'] = $student['s_name'];
        $list[$key]['thumb'] = tomedia($student['icon']);
        $list[$key]['desc'] = '班级:'.$bjname;
    }
}
$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ORDER BY ssort DESC", array(':weid' => $weid, ':id' => $schoolid));
include $this->template(''.$school['style3'].'/psychology/tshrinklog');
?>