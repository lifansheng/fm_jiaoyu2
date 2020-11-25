<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_W, $_GPC;
$weid = $_W ['uniacid'];
$openid = $_W['openid'];
$id = intval($_GPC['id']);
$schoolid = intval($_GPC['schoolid']);
if(!empty($_GPC['checktid'])){
    $checktid = $_GPC['checktid'];
}
//检查是否用户登陆
$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ORDER BY id DESC", array(':weid' => $weid, ':id' => $userid['id']));
$school = pdo_fetch("SELECT style3,title,tpic,logo FROM " . tablename($this->table_index) . " where weid = :weid AND id = :id ", array(':weid' => $weid, ':id' => $schoolid));
$schooltype = $_W['schooltype'];
$tid_global = $it['tid'];

//是否附件分离
if (!empty($_W['setting']['remote']['type'])) {
    $urls = $_W['attachurl'];
} else {
    $urls = $_W['siteroot'].'attachment/';
}

if (!(IsHasQx($tid_global,2002701,2,$schoolid))){
    message('您无权查看本页面');
}
if($it){
    //当前登录教师
    $teacher = pdo_fetch("SELECT status FROM " . tablename($this->table_teachers) . " where :id = id ", array(':id' => $it['tid']));

    //获取老师切换列表
    //校长
    if($teacher['status'] == 2){
        $allteacher = pdo_fetchall("SELECT id,tname,thumb FROM " . tablename($this->table_teachers) . " where weid = '{$weid}' AND schoolid = '{$schoolid}' ORDER BY convert(tname using gbk) ASC");
    //年级主任
    }elseif(is_njzr($it['tid'])){
        $njlist = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where schoolid = :schoolid And weid = :weid And type = :type And tid = :tid ORDER BY sid DESC", array(':weid' => $weid,':schoolid' => $schoolid,':type' => 'semester',':tid' => $it['tid']));
        $bjidname = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where :sid = sid ", array(':sid' => $nj_id));
        $allbjarr = pdo_fetchall("SELECT sid FROM " . tablename($this->table_classify) . " where schoolid = :schoolid And weid = :weid And type = :type And parentid = :parentid ORDER BY sid DESC", array(':weid' => $weid,':schoolid' => $schoolid,':type' => 'theclass',':parentid' => $nj_id));
        $allbj = array();
        foreach($allbjarr as $k => $v){
            $allbj[$k] = $v['sid'];
        }
        $alltbj = pdo_fetchall("SELECT bj_id,tid FROM " . tablename($this->table_class) . " where schoolid = :schoolid And weid = :weid ORDER BY id DESC", array(':weid' => $weid,':schoolid' => $schoolid));
        $allls = '';
        foreach($alltbj as $key => $val){
            if(in_array($val['bj_id'],$allbj)){
                if(!in_array($val['tid'],$allls)){
                    $allls .= $val['tid'].',';
                }
            }
        }
        $allls = trim($allls,',');
        $allteacher = pdo_fetchall("SELECT id,tname,thumb FROM " . tablename($this->table_teachers) . " where weid = '{$weid}' AND schoolid = '{$schoolid}' and FIND_IN_SET(id,'{$allls}') ORDER BY convert(tname using gbk) ASC");
    }

    if (IsHasQx($tid_global,2002702,2,$schoolid)){
        $allteacher = pdo_fetchall("SELECT id,tname,thumb FROM " . tablename($this->table_teachers) . " where weid = '{$weid}' AND schoolid = '{$schoolid}' ORDER BY convert(tname using gbk) ASC");
    }

    mload()->model('tea');
    $bjlist = GetAllClassInfoByTid($schoolid,0,$schooltype,$it['tid']);





    if($_GPC['opstype'] == 'teatimetable' || empty($_GPC['opstype'])){

        if(!empty($_GPC['tid'])){
            $tid = $_GPC['tid'];
        } else{
            $tid = $it['tid'];

        }
        $teacher2show = pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " where :id = id ", array(':id' => $tid));
        $starttime = mktime(0,0,0,date("m"),date("d"),date("Y"));
        $endtime = $starttime + 86399;
        mload()->model('kc');
        $this_list =getkctimetableByTid($schoolid,$tid,$starttime,$endtime);

    }elseif($_GPC['opstype'] == 'bjtimetable'){

        $bj_id = $_GPC['bj_id'];
        $bj2show = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where :sid = sid ", array(':sid' => $bj_id));
        $starttime = mktime(0,0,0,date("m"),date("d"),date("Y"));
        $endtime = $starttime + 86399;
        mload()->model('kc');
        $this_list =getkctimetableByBjid($schoolid,$bj_id,$starttime,$endtime);
        //var_dump($this_list);
    }


    include $this->template(''.$school['style3'].'/teatimetable');
}else{
    session_destroy();
    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
    header("location:$stopurl");
    exit;
}
?>