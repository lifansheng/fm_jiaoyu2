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
$id = intval($_GPC['id']);
mload()->model('user');
$GLid = 0;
$school = pdo_fetch("SELECT style1,spic,tpic FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
if ($id) {
    $thisleave = pdo_fetch("SELECT * FROM " . GetTableName('psychology') . " where weid = :weid AND id = :id ", array(':weid' => $weid, ':id' => $id));
    $GLid = $thisleave['leaveid'];
    $toopenid = $thisleave['openid'];
    $lasttime = pdo_fetch("SELECT id,createtime FROM " . GetTableName('psychology') . " where weid = :weid AND schoolid = :schoolid AND AND leaveid = :leaveid ORDER BY createtime DESC ", array(':weid' => $weid, ':schoolid' => $schoolid, ':leaveid' => $thisleave['leaveid']));
} else {
    $toopenid = $_GPC['toopenid']; //向老师openid发送信息
    $touserid = $_GPC['touserid']; //向老师openid发送信息
    $thisleave = pdo_fetch("SELECT * FROM " . GetTableName('psychology') . " where weid = :weid AND openid = :openid AND toopenid = :toopenid AND userid = :userid ORDER BY createtime ASC", array(':weid' => $weid, ':openid' => $openid, ':toopenid' => $toopenid,':userid' => 0));
    if(!empty($thisleave)){
        $GLid = $thisleave['leaveid'];
    }
}
// 发送者身份为粉丝
$fans_info = mc_fansinfo($openid);
$name = $fans_info['nickname'];
$icon = $fans_info['headimgurl'];
if($GLid !=0){
    $list = pdo_fetchall("SELECT * FROM " . GetTableName('psychology') . " where weid = :weid AND schoolid = :schoolid AND leaveid = :leaveid ORDER BY createtime ASC ", array(':weid' => $weid, ':schoolid' => $schoolid, ':leaveid' => $GLid));
    $lasttime = pdo_fetch("SELECT id,createtime FROM " . GetTableName('psychology') . " where weid = :weid AND schoolid = :schoolid AND leaveid = :leaveid ORDER BY createtime DESC ", array(':weid' => $weid, ':schoolid' => $schoolid, ':leaveid' => $GLid));
    $totea = pdo_fetch("SELECT id,tid FROM " . tablename($this->table_user) . " where sid = :sid And openid = :openid", array(':sid' => 0,':openid' => $toopenid));	
    $img_url = array();
    $iii = 0;
    foreach ($list as $k => $v) {
        if (!empty($v['picurl'])) {
            $img_url[$iii] = tomedia($v['picurl']);
            $iii = $iii + 1;
        }
        $list[$k]['time'] = sub_day($v['createtime']);
        if (empty($v['userid'])) {
            $list[$k]['name'] = $name;
            $list[$k]['icon'] = $icon;
            
        } else {
            $teacher = pdo_fetch("SELECT tname,thumb FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $totea['tid']));
            $list[$k]['name'] = $teacher['tname'] . " 老师";
            $list[$k]['icon'] =  empty($teacher['thumb']) ? $school['tpic'] : $teacher['thumb'];
            if ($v['isread'] == 1) {
                pdo_update(GetTableName('psychology', false), array('isread' =>  2), array('id' =>  $v['id']));
            } 
        }
        if (!empty($v['audio'])) {
            $audios = iunserializer($v['audio']);
            $list[$k]['audios'] = $audios['audio'][0];
            $list[$k]['audioTime'] = $audios['audioTime'][0];
        }
    }
}
$img_url_de = json_encode($img_url);

include $this->template('' . $school['style1'] . '/sfanspsychology');
