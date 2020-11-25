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
$njid = $_GPC['njid'];
//查询是否用户登录		
$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $userid['id']));
$tid_global = $it['tid'];
$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
mload()->model('tea');
$qh = pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and sid = '{$qhid}' ");
$njname = pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and sid = '{$njid}'")['sname'];
$bjlist = pdo_fetchall("SELECT sname,sid FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and parentid = '{$njid}' ORDER BY sid DESC");
$date = date("Y-m-d",time());
$time = strtotime(date("Y-m-d",time()));
foreach ($bjlist as $key => $value) {
    $IsDone = pdo_fetch("SELECT id FROM ".GetTableName('ddscorelog')." WHERE schoolid = '{$schoolid}' AND bjid = '{$value['sid']}' AND date = '{$time}' ");
    $bjlist[$key]['IsDone'] = $IsDone ? true : false;
}
$op = $_GPC['op'] ? $_GPC['op'] : 'display';
if($op == 'getscorelist'){
    $bjid = $_GPC['bjid'];
    $time = $_GPC['date'] ? strtotime($_GPC['date']) : $time;
    $list = pdo_fetchAll("SELECT id,type,title,addition FROM " . GetTableName('ddscorecategory') .  " WHERE schoolid = :schoolid ORDER BY addition,ssort DESC", array(':schoolid' => $_GPC['schoolid']));
    foreach ($list as $key => $value) {
        $score = pdo_fetch("SELECT score FROM ".GetTableName('ddscorelog')." WHERE schoolid = '{$schoolid}' AND bjid = '{$bjid}' AND date = '{$time}' AND cid = '{$value['id']}' ")['score'];
        if(!empty($score)){
            $list[$key]['score'] = $score;
        }else{
            $list[$key]['score'] = 0;
        }
    }
    $bjname = pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' AND sid = '{$bjid}'")['sname'];
    $IsDone = false;
    if(!empty($score)){
        $IsDone = true;
    }
    die(json_encode(array(
        'list' => $list,
        'IsDone' => $IsDone,
        'bjname' => $bjname ? $bjname : '请选择班级'
    )));
}elseif($op == 'display'){
    if(!empty($userid['id'])){
        include $this->template(''.$school['style3'].'/tddscorelookdetail');
    }else{
        session_destroy();
        $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
        header("location:$stopurl");
    }   
}
            
?>