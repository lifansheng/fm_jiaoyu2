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
$kcid = $_GPC['kcid'];
$ksid = $_GPC['ksid'];
$time = $_GPC['time'];
//查询是否用户登录
$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $userid['id']));
$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
$teachers = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $it['tid']));
$tid_global = $it['tid'];
if(!empty($userid['id'])){
    $kcinfo = pdo_fetch("SELECT name,OldOrNew FROM " . tablename($this->table_tcourse) . " where weid = :weid And schoolid=:schoolid AND id = :id", array(':weid' => $weid,':schoolid'=>$schoolid, ':id' => $kcid));

    //$bmlist = pdo_fetchall("SELECT sid FROM " . tablename($this->table_order) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND type = 1 and status=2 And kcid='{$kcid}' and sid != 0 GROUP BY kcid,sid ORDER BY id DESC ");
    $bmlist = pdo_fetchall("SELECT sid FROM " . GetTableName('coursebuy') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND  kcid='{$kcid}' and sid != 0 and is_change != 1 GROUP BY kcid,sid ORDER BY id DESC ");

    $timeStart = strtotime(date('Ymd',$time));
    $timeEnd =$timeStart + 86399;
    //if(!empty($time) && empty($ksid)){
    //	$signList = pdo_fetchall("SELECT id,status,sid,createtime FROM " . tablename($this->table_kcsign) . " WHERE weid ='{$weid}' And schoolid='{$schoolid}' and  kcid='{$kcid}' And tid =0 and sid !=0 And createtime>'{$timeStart}' And createtime<'{$timeEnd}' ");
    //}elseif(empty($time) && !empty($ksid)){
    //	$signList = pdo_fetchall("SELECT id,status,sid,createtime FROM " . tablename($this->table_kcsign) . " WHERE weid ='{$weid}' And schoolid='{$schoolid}' and  kcid='{$kcid}' And tid =0 and sid !=0 And ksid='{$ksid}'  ");
    //}

    //var_dump($signList);
    $NotConfirm = array();//签到未确认
    $HasSign = array();//已签到
    $NotSign = array();//未签到
    $HasQJ = array();
    $i_NC = 0;
    $i_HS = 0;
    $i_NS = 0;
    $i_QJ = 0;
    foreach($bmlist as $index => $row){
        $student = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " WHERE id = :id ", array(':id' => $row['sid']));
        if(!empty($ksid)){
            if(!empty($student)) {
                $ksinfo    = pdo_fetch("SELECT * FROM " . tablename($this->table_kcbiao) . " where weid = :weid And schoolid=:schoolid And kcid=:kcid AND id = :id", array(':weid' => $weid, ':schoolid' => $schoolid, 'kcid' => $kcid, ':id' => $ksid));
                $checknubs = GetOneKcKsOrder($kcid,$ksid);
			    $ksinfo['nub'] = $checknubs['nuber'];
                $sdinfo    = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where weid = :weid And schoolid=:schoolid AND sid = :id", array(':weid' => $weid, ':schoolid' => $schoolid, ':id' => $ksinfo['sd_id']));
                $checksign = pdo_fetch("SELECT id,status,createtime FROM " . tablename($this->table_kcsign) . " WHERE weid ='{$weid}' And schoolid='{$schoolid}' and  kcid='{$kcid}' And ksid='{$ksid}' And sid='{$row['sid']}' ");
                if (!empty($checksign)) {
                    if ($checksign['status'] == 1) {
                        $NotConfirm[$i_NC]['sname']      = $student['s_name'];
                        $NotConfirm[$i_NC]['createtime'] = $checksign['createtime'];
                        $NotConfirm[$i_NC]['id']         = $checksign['id'];
                        $i_NC++;
                    } elseif ($checksign['status'] == 2) {
                        $HasSign[$i_HS]['sname']      = $student['s_name'];
                        $HasSign[$i_HS]['createtime'] = $checksign['createtime'];
                        $HasSign[$i_HS]['id']         = $checksign['id'];
                        $i_HS++;
                    } elseif ($checksign['status'] == 3) {
                        $HasQJ[$i_QJ]['sname']      = $student['s_name'];
                        $HasQJ[$i_QJ]['createtime'] = $checksign['createtime'];
                        $HasQJ[$i_QJ]['id']         = $checksign['id'];
                        $i_QJ++;
                    }
                } else {


                    $NotSign[$i_NS]['sname'] = $student['s_name'];

                    $NotSign[$i_NS]['sid'] = $row['sid'];
                    $i_NS++;
                }
            }
        }elseif(!empty($time)){

            $checksign = pdo_fetchall("SELECT id,status,createtime FROM " . tablename($this->table_kcsign) . " WHERE weid ='{$weid}' And schoolid='{$schoolid}' and  kcid='{$kcid}' And createtime>'{$timeStart}' And createtime<'{$timeEnd}' And sid='{$row['sid']}' ");
            if(!empty($checksign)){
                foreach( $checksign as $key_sign => $value_sign )
                {
                    if($value_sign['status'] == 1){
                        $NotConfirm[$i_NC]['sname']      = $student['s_name'];
                        $NotConfirm[$i_NC]['createtime'] = $value_sign['createtime'];
                        $NotConfirm[$i_NC]['id']         = $value_sign['id'];
                        $i_NC++;
                    }elseif($value_sign['status'] ==2){
                        $HasSign[$i_HS]['sname']      = $student['s_name'];
                        $HasSign[$i_HS]['createtime'] = $value_sign['createtime'];
                        $HasSign[$i_HS]['id']         = $value_sign['id'];
                        $i_HS++;
                    }elseif($value_sign['status'] ==3){
                        $HasQJ[$i_QJ]['sname']      = $student['s_name'];
                        $HasQJ[$i_QJ]['createtime'] = $checksign['createtime'];
                        $HasQJ[$i_QJ]['id']         = $checksign['id'];
                        $i_QJ++;
                    }
                }
            }else{

                $NotSign[$i_NS]['sname'] = $student['s_name'];
                $NotSign[$i_NS]['sid']   = $row['sid'];
                $i_NS++;
            }


        }

    }
    $HScount = count($HasSign);
    $Allcount = count($bmlist);
    $qdl=round($HScount/$Allcount*100, 2);

    include $this->template(''.$school['style3'].'/txsbqkc');
}else{
    session_destroy();
    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
    header("location:$stopurl");
}
?>