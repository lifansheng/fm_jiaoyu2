<?php
    /**
     * 微教育模块
     *
     * @author 高贵血迹
     */
    global $_W, $_GPC;
    $weid = $_W ['uniacid'];
    $schoolid = intval($_GPC['schoolid']);
    $openid = $_W['openid'];
    $leaveid = $_GPC['leaveid'];
    mload()->model('que');
    $XuanXIangCount;
    $tempALL = pdo_fetchall("SELECT distinct tmid FROM " . tablename('wx_school_answers') . " where zyid= :zyid AND  schoolid = :schoolid  AND weid = :weid  ORDER BY tmid ", array( ':zyid' =>$leaveid,':schoolid' =>$schoolid, ':weid' => $weid ));
    foreach($tempALL as $value )
    {
    $tempA = pdo_fetchall("SELECT distinct sid FROM " . tablename('wx_school_answers') . " where zyid= :zyid AND tmid = :tmid  AND schoolid = :schoolid  AND weid = :weid  ", array( ':zyid' =>$leaveid, ':tmid' => $value['tmid'],':schoolid' =>$schoolid, ':weid' => $weid ));
    $XuanXIangCount[$value['tmid']] = count($tempA);

    };
    $ZYNEIRONG = GetZyContent($leaveid,$schoolid,$weid);
    $countOfTm = count($ZYNEIRONG);
    //查询是否用户登录		
    $userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');

 

    //TODO:查询当前问卷调查的发送对象count，已回答人数 ，得到未回答人数

    $leave = pdo_fetch("SELECT * FROM " . tablename($this->table_notice) . " where :id = id", array(':id' => $leaveid));

    if($leave['usertype'] == 'school'){
        $teachers = "SELECT t.id,t.tname as name,c.sname,0 as usertype,IFNULL((SELECT createtime FROM " . GetTableName('answers') ." as a WHERE t.id = a.tid AND a.zyid = '{$leaveid}' LIMIT 1),0) as createtime FROM " . GetTableName('teachers') . " as t LEFT JOIN " . GetTableName('classify') . " as c ON t.fz_id = c.sid WHERE t.weid = '{$weid}' AND t.schoolid = '{$schoolid}'  ORDER BY t.id DESC";

        $students = "SELECT s.id,s.s_name as name,c.sname,0 as usertype,IFNULL((SELECT createtime FROM " . GetTableName('answers') ." as a WHERE s.id = a.sid AND a.zyid = '{$leaveid}' LIMIT 1),0) as createtime FROM " . GetTableName('students') . " as s LEFT JOIN " . GetTableName('classify') . " as c ON s.bj_id = c.sid WHERE s.weid = '{$weid}' AND s.schoolid = '{$schoolid}'  ORDER BY s.id DESC";
        $sql = " SELECT * FROM ( {$teachers} ) as t union ( {$students} ) ";
    }elseif($leave['usertype'] == 'alltea'){
        $sql = "SELECT t.id,t.tname as name,c.sname,0 as usertype,IFNULL((SELECT createtime FROM " . GetTableName('answers') ." as a WHERE t.id = a.tid AND a.zyid = '{$leaveid}' LIMIT 1),0) as createtime FROM " . GetTableName('teachers') . " as t LEFT JOIN " . GetTableName('classify') . " as c ON t.fz_id = c.sid WHERE t.weid = '{$weid}' AND t.schoolid = '{$schoolid}' ";
    }elseif($leave['usertype'] == 'allstu'){
        $sql = "SELECT s.id,s.s_name as name,c.sname,0 as usertype,IFNULL((SELECT createtime FROM " . GetTableName('answers') ." as a WHERE s.id = a.sid AND a.zyid = '{$leaveid}' LIMIT 1),0) as createtime FROM " . GetTableName('students') . " as s LEFT JOIN " . GetTableName('classify') . " as c ON s.bj_id = c.sid WHERE s.weid = '{$weid}' AND s.schoolid = '{$schoolid}' ";
    }elseif($leave['usertype'] == 'bj'){
        $sql = "SELECT s.id,s.s_name as name,c.sname,0 as usertype,IFNULL((SELECT createtime FROM " . GetTableName('answers') ." as a WHERE s.id = a.sid AND a.zyid = '{$leaveid}' LIMIT 1),0) as createtime FROM " . GetTableName('students') . " as s LEFT JOIN " . GetTableName('classify') . " as c ON s.bj_id = c.sid WHERE s.weid = '{$weid}' AND s.schoolid = '{$schoolid}' AND FIND_IN_SET(s.bj_id,'{$leave['userdatas']}') ";
    }elseif($leave['usertype'] == 'jsfz'){
        $sql = "SELECT t.id,t.tname as name,c.sname,0 as usertype,IFNULL((SELECT createtime FROM " . GetTableName('answers') ." as a WHERE t.id = a.tid AND a.zyid = '{$leaveid}' LIMIT 1),0) as createtime FROM " . GetTableName('teachers') . " as t LEFT JOIN " . GetTableName('classify') . " as c ON t.fz_id = c.sid WHERE t.weid = '{$weid}' AND t.schoolid = '{$schoolid}' AND FIND_IN_SET(t.fz_id,'{$leave['userdatas']}') ";
    }
    //  IFNULL(SUM(case when type = '事假' then 1 else 0 end),0) as sj,IFNULL(SUM(case when type = '病假'  then 1 else 0 end),0) as bj
    // $list = pdo_fetchall($sql." ORDER BY  CONVERT(name USING gbk) ASC ");

    $Count = pdo_fetch("SELECT IFNULL(SUM(case when createtime > 0 then 1 else 0 end),0) as ans,count(*) as allc FROM ( ".$sql." ) as aaa");

    $all = $Count['allc'];
    $ans = $Count['ans'];
    $notans = $Count['allc'] - $Count['ans'];

    $it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ORDER BY id DESC", array(':weid' => $weid, ':id' => $userid['id']));
    $school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
    $nowbj =  pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE sid =:sid ", array(':sid' => $leave['bj_id']));
    $nowkm =  pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE sid =:sid ", array(':sid' => $leave['km_id']));		
    if(!empty($userid['id'])){
        $student = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $leave['sid']));
        $member = pdo_fetch("SELECT * FROM " . tablename ( 'mc_members' ) . " where uniacid = :uniacid AND uid = :uid", array(':uniacid' => $weid, ':uid'=> $leave['uid']));
        $isbzr = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $it['tid']));
        $picarr = iunserializer($leave['picarr']);	
        include $this->template($school['style3'].'/quesform/quesformdata');
    }else{
        session_destroy();
        $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
        header("location:$stopurl");
    } 

?>