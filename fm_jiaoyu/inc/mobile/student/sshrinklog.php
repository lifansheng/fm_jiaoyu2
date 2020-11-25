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
$it = pdo_fetch("SELECT id,sid FROM " . tablename($this->table_user) . " where id = :id And openid = :openid ", array(':id' => $_SESSION['user'], ':openid' => $openid));
$school = pdo_fetch("SELECT style2 FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
if (!empty($it)) {
    //教师列表按教师入职时间先后顺序排列，先入职再前
    $list = pdo_fetchall("SELECT * FROM " . GetTableName('psychology') . " WHERE schoolid = '{$schoolid}' AND userid = '{$it['id']}' GROUP BY leaveid ORDER BY createtime DESC");
    foreach ($list as $key => $value) {
        $tid = pdo_fetch("SELECT tid FROM " . GetTableName('user') . " WHERE schoolid = :schoolid AND id = :id ", array(':schoolid' => $schoolid, ':id' => $value['touserid']))['tid'];
        $teacher = pdo_fetch("SELECT tname,thumb,id,fz_id FROM ".GetTableName('teachers')." WHERE schoolid = :schoolid AND id = :id ",array(':schoolid'=>$schoolid,':id'=>$tid));
        $list[$key]['tname'] = $teacher['tname'];
        $list[$key]['sj'] = sub_day($value['createtime']);
        $list[$key]['thumb'] = tomedia($teacher['thumb']);
        $list[$key]['description'] = $teacher['description'];
    }
    include $this->template('' . $school['style2'] . '/psychology/sshrinklog');
} else {
    session_destroy();
    $stopurl = $_W['siteroot'] . 'app/' . $this->createMobileUrl('bangding', array('schoolid' => $schoolid));
    header("location:$stopurl");
    exit;
}
