<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_GPC, $_W;

$weid = $_W['uniacid'];
$action = 'kecheng';
$this1 = 'no2';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'],$action);
$schoolid = intval($_GPC['schoolid']);
$kcid1 = intval($_GPC['kcid']);
$logo = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where id = :id ORDER BY ssort DESC", array(':id' => $schoolid));
$over_status = ($_GPC['over_status']) ? intval($_GPC['over_status']) : -1;
$kecheng = pdo_fetch("SELECT * FROM " . tablename($this->table_tcourse) . " where id = :id", array(':id' => $kcid1));
$remindday = pdo_fetch("select id,remindday from " . tablename($this->table_schoolset) . " where schoolid=:schoolid and weid =:weid", array(':schoolid' => $schoolid, ':weid' => $weid));
$checkNowTime = strtotime(date("Y-m-d",time()));
$kcall = pdo_fetchall("SELECT * FROM " . tablename($this->table_tcourse) . " where schoolid ='{$schoolid}' and weid = '{$weid}' and end > $checkNowTime ");
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$tid_global = $_W['tid'];
if ($operation == 'display') { //默认
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $nowtime = time();
    $checktime   = strtotime(date("Y-m-d",time()));
    $condition = '';
    $conditionKc = '';
    //即将过期
    if($over_status  == 1) {
        $remindtime = $nowtime + $remindday['remindday'] * 86400;
        $condition .= " and overtime < {$remindtime} and overtime > {$nowtime} and overtime != 0 ";
    //已过期
    }elseif($over_status  == 2){
        $condition .= " and overtime <= {$nowtime} and overtime != 0 ";
    //已毕业
    }elseif($over_status  == 3){

        $conditionKc = " and end <={$checktime}    ";
    //正常
    }elseif($over_status  == 4){
        $conditionKc = " and end >{$checktime}    ";
        $remindtime = $nowtime + $remindday['remindday'] * 86400;
        $condition .= " and ( overtime > {$remindtime} or overtime = 0 ) ";
    }
    if(!empty($_GPC['stuname'])){
        $stuname = trim($_GPC['stuname']);
        $conditionstu .= " and s_name LIKE '%{$stuname}%' ";
    }
    if($conditionstu != ''){
        $stusearch = pdo_fetchall("SELECT id FROM " . GetTableName('students') . " WHERE weid='{$weid}' AND schoolid='{$schoolid}'  $conditionstu ");
        $stuid_temp = '';
        if(!empty($stusearch)){
            foreach( $stusearch as $key => $value )
            {
                $stuid_temp .=$value['id'].",";
            }
            $stuid_str = trim($stuid_temp,",");
            $condition .= " AND  FIND_IN_SET (sid,'{$stuid_str}') ";
        }
        else{
            $condition .= " AND sid = 0 ";
        }
    }



    if(!empty($_GPC['kcname'])){
        $kcname = trim($_GPC['kcname']);
        $conditionKc .= " and name LIKE '%{$kcname}%' ";
    }

    if($conditionKc != ''){
        $kcsearch = pdo_fetchall("SELECT id FROM " . tablename($this->table_tcourse) . " WHERE weid='{$weid}' AND schoolid='{$schoolid}'  $conditionKc ");
        $kcid_temp = '';
        if(!empty($kcsearch)){
            foreach( $kcsearch as $key => $value )
            {
                $kcid_temp .=$value['id'].",";
            }
            $kcid_str = trim($kcid_temp,",");
            $condition .= " AND  FIND_IN_SET (kcid,'{$kcid_str}') ";
        }
        else{
            $condition .= " AND kcid =0 ";
        }
    }



    $params[':start'] = $starttime;
    $params[':end'] = $endtime;
    mload()->model('kc');
    mload()->model('sell');

    $conditionOver = '';
    if($tid_global != 'founder' && $tid_global != 'owner' && $tid_global != 0 ){
        $loginTeaFzid =  pdo_fetch("SELECT is_sell FROM " . tablename ($this->table_teachers) . " where weid = :weid And schoolid = :schoolid And id =:id ", array(':weid' => $weid,':schoolid' => $schoolid,':id'=>$tid_global));
        $stulist = pdo_fetchall("SELECT id FROM " . tablename($this->table_students) . " WHERE sellteaid = :sellteaid ", array(':sellteaid' => $tid_global));
        $stustr = '';
        if(!empty($stulist)){
            foreach ($stulist as $item) {
                $stustr .= $item['id'].',';
            }
            $stustr = trim($stustr,',');
            if($loginTeaFzid['is_sell'] == 1){
                $conditionOver = " and FIND_IN_SET(sid,'{$stustr}') ";
            }
        }
    }

    if($_GPC['out_putcode'] == 'out_putcode'){
        $list_out = pdo_fetchall("SELECT * FROM " . tablename($this->table_coursebuy) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}'  $condition $conditionOver GROUP BY kcid,sid ORDER BY id DESC  " );
        $arr = [];
        $ii   = 0;
        foreach($list_out as $index => $row){
            $student = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " WHERE id = :id ", array(':id' => $row['sid']));
            $kc = pdo_fetch("SELECT * FROM " . tablename($this->table_tcourse) . " WHERE id = :id ", array(':id' => $row['kcid']));
            $buycourse = pdo_fetchcolumn("SELECT ksnum FROM " . tablename($this->table_coursebuy) . " WHERE sid = :sid AND kcid=:kcid and  schoolid =:schoolid", array(':sid' => $row['sid'],':kcid'=> $row['kcid'],':schoolid'=> $schoolid));
            $hasSign =  pdo_fetchcolumn("SELECT sum(costnum) FROM " . tablename($this->table_kcsign) . " WHERE sid = :sid AND kcid=:kcid and  schoolid =:schoolid AND status =2 ", array(':sid' => $row['sid'],':kcid'=> $row['kcid'],':schoolid'=> $schoolid));
            $sellTea = GetSellTeaByStuId($schoolid,$weid,$row['sid']);
            $arr[$ii]['s_name'] = trim($student['s_name']);
            $arr[$ii]['mobile'] = $student['mobile'];
            $arr[$ii]['kcname'] = $kc['name'];
            $arr[$ii]['restnum'] = $buycourse - $hasSign;
            if ($sellTea['status'] == true){
                $arr[$ii]['SellTea'] = $sellTea['sellteaname'];
            }else{
                $arr[$ii]['SellTea'] ='未指定';
            }
            if($row['overtime'] != 0) {
                if ($kc['end'] <= $checktime) {
                    $arr[$ii]['status'] = '毕业';
                } elseif ($row['overtime'] < $remindtime && $row['overtime'] > $nowtime) {
                    $arr[$ii]['status'] = '即将过期';
                } elseif ($row['overtime'] < $nowtime) {
                    $arr[$ii]['status'] = '过期';
                } elseif ($row['overtime'] > $remindtime) {
                    $arr[$ii]['status'] = '正常';
                }
                $arr[$ii]['overtime'] = date("Y-m-d",$row['overtime']);
            }elseif($row['overtime'] == 0){
                $arr[$ii]['status'] = '不过期';
                $arr[$ii]['overtime'] = '不过期';
            }
            $arr[$ii]['KcOverTime'] = date("Y-m-d",$kc['end']);
            $ii++;
        }
        $this->exportexcel($arr, array('学生', '联系方式','课程名称','剩余课时','业务员', '过期状态','过期时间','课程结束时间',), '学生过期信息表');
        exit();
    }





    $list = pdo_fetchall("SELECT * FROM " . tablename($this->table_coursebuy) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}'  $condition $conditionOver GROUP BY kcid,sid ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
    foreach($list as $index => $row){


        if(keep_sk77()){
            $list[$index]['kcstatus'] = GetStuKcStatus($schoolid,$weid,$row['sid'],$row['kcid']);
        }
        $kc = pdo_fetch("SELECT * FROM " . tablename($this->table_tcourse) . " WHERE id = :id ", array(':id' => $row['kcid']));
        $student = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " WHERE id = :id ", array(':id' => $row['sid']));
        $user = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " WHERE id = :id ", array(':id' => $row['userid']));
        $buycourse = pdo_fetchcolumn("SELECT ksnum FROM " . tablename($this->table_coursebuy) . " WHERE sid = :sid AND kcid=:kcid and  schoolid =:schoolid", array(':sid' => $row['sid'],':kcid'=> $row['kcid'],':schoolid'=> $schoolid));
        $hasSign =  pdo_fetchcolumn("SELECT sum(costnum) FROM " . tablename($this->table_kcsign) . " WHERE sid = :sid AND kcid=:kcid and  schoolid =:schoolid AND status =2 ", array(':sid' => $row['sid'],':kcid'=> $row['kcid'],':schoolid'=> $schoolid));
        $list[$index]['selltea'] = GetSellTeaByStuId($schoolid,$weid,$row['sid']);
        if($kc['end'] <=$checktime){
            $list[$index]['status'] = 3;
        }elseif($row['overtime'] <$remindtime && $row['overtime'] >$nowtime ){
            $list[$index]['status'] = 1;
        }elseif($row['overtime'] <$nowtime){
            $list[$index]['status'] = 2;
        }elseif($row['overtime'] > $remindtime){
            $list[$index]['status'] = 0;
        }
        if($row['is_change'] == 1){
            $ChangeBjToName = pdo_fetch("SELECT name FROM ".GetTableName('tcourse')." WHERE id = '{$row['change_id']}' ");
            $list[$index]['ChangeBjToName'] = $ChangeBjToName['name'];
        }
        $list[$index]['kcendtime'] = $kc['end'];
        $list[$index]['restnum'] = $buycourse - $hasSign;
        $list[$index]['buycourse'] = $buycourse;
        $list[$index]['hasSign'] = $hasSign;
        $list[$index]['kcnanme'] = $kc['name'];
        $list[$index]['s_name'] = $student['s_name'];
        $list[$index]['mobile'] = $student['mobile'];
        $list[$index]['userinfo'] = $user['userinfo'];
        $list[$index]['pard'] = $user['pard'];
    }
    $total_t = pdo_fetchall("SELECT * FROM " . tablename($this->table_coursebuy) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}'  $condition GROUP BY kcid,sid ORDER BY id DESC  ");
    $total = count($total_t);
    //var_dump($total);
    $pager = pagination($total, $pindex, $psize);

}elseif($operation == 'change_bj'){ //转班
    $rowcount    = 0;
    $notrowcount = 0;
    $kcid = intval($_GPC['bj_id']);
    //调后课程信息
    $kcinfo = pdo_fetch("SELECT * FROM " .GetTableName('tcourse') . " WHERE id = :id", array(':id' => $kcid));
    $overtimeday = $kcinfo['overtimeday']; //获取过期天数
    $nowtime = time();
    $kcbuyovertime = 0;
    if($overtimeday != 0 ){
        $kcbuyovertime = strtotime(date("Y-m-d",$nowtime)) + 86399 + 86400 * $overtimeday;
    }
    if(!empty($kcid)){
        foreach($_GPC['idArr'] as $k => $id){
            if(!empty($id)){
                $bid = $id['id']; //coursebuy id
                $thiskcid = $id['kcid']; //转班前的kcid
                //调前课程buy信息
                $checkcoursebuy = pdo_fetch("SELECT * FROM " .GetTableName('coursebuy') . " WHERE kcid = '{$thiskcid}' and id = '{$bid}' "); //123
                $sid = $checkcoursebuy['sid']; //学生ID 
                $stusign = pdo_fetchcolumn("SELECT SUM(costnum) FROM " .GetTableName('kcsign') . " WHERE kcid = '{$thiskcid}' and sid = {$sid} and status = 2 "); //学生在之前班级消耗的课时
                $insertKcBuyData = array(
                    'weid' =>$checkcoursebuy['weid'],
                    'schoolid' =>$checkcoursebuy['schoolid'],
                    'userid' => $checkcoursebuy['userid'],
                    'sid' => $checkcoursebuy['sid'],
                    'kcid' => $kcid,
                    'ksnum' => $checkcoursebuy['ksnum'] - $stusign,
                    'createtime' => $nowtime,
                    'overtime' => $checkcoursebuy['overtime'],
                    'is_change' => 2,
                    'change_id'=>$checkcoursebuy['id']
                );

                $insertOrderData = array(
                    'weid' => $checkcoursebuy['weid'],
                    'schoolid' =>$checkcoursebuy['schoolid'],
                    'sid' => $checkcoursebuy['sid'],
                    'kcid' => $kcid,
                    'status' => 2,
                    'type' => 1,
                    'createtime' => $nowtime,
                    'ksnum' =>  $checkcoursebuy['ksnum'] - $stusign,
                );
                $checkIsBuy = pdo_fetch("SELECT id FROM " .GetTableName('coursebuy') . " WHERE kcid = '{$kcid}' and sid = '{$sid}' ");
                if(!empty($checkIsBuy)){ //如果目标班级是已经出现过的
                    /**
                     * 逻辑：
                     *  如果是转到之前的班级，那么剩余课时数等于 当前学生的剩余课时数 加上 在之前的班级已经上过的课时数
                     *  然后过期时间不变
                     */
                    $targetUsedKsnum = pdo_fetchcolumn("SELECT SUM(costnum) FROM " .GetTableName('kcsign') . " WHERE kcid = '{$kcid}' and sid = {$sid} and status = 2  "); //学生在新的班级消耗的课时
                    $ChangeData = array(
                        'ksnum' => $checkcoursebuy['ksnum'] - $stusign + $targetUsedKsnum,
                        'is_change' => 2
                    );
                    pdo_update(GetTableName('coursebuy',false),$ChangeData,array('id'=>$checkIsBuy['id']));
                    pdo_update(GetTableName('coursebuy',false),array('ksnum'=>0,'is_change' => 1,'change_id'=>$kcid),array('id'=>$checkcoursebuy['id']));
                    $rowcount++;
                    $BeforeKcName = pdo_fetch("SELECT name FROM " .GetTableName('tcourse') . " WHERE id = :id", array(':id' => $checkcoursebuy['kcid']));
                    $this->sendMobileZbtz($sid,$kcid,$BeforeKcName['name'],$checkcoursebuy['schoolid'], $checkcoursebuy['weid']);
                    // $notrowcount++;
                }else{ //如果目标班级未出现过
                    pdo_insert(GetTableName('order',false),$insertOrderData);
                    pdo_insert(GetTableName('coursebuy',false),$insertKcBuyData);
                    pdo_update(GetTableName('coursebuy',false),array('ksnum'=>0,'is_change' => 1,'change_id'=>$kcid),array('id'=>$checkcoursebuy['id']));
                    $rowcount++;
                    //发送模版消息
                    $BeforeKcName = pdo_fetch("SELECT name FROM " .GetTableName('tcourse') . " WHERE id = :id", array(':id' => $checkcoursebuy['kcid']));
                    $this->sendMobileZbtz($sid,$kcid,$BeforeKcName['name'],$checkcoursebuy['schoolid'], $checkcoursebuy['weid']);
                }
            }
        }

        $data ['result'] = true;
        $message = "操作成功！共转移{$rowcount}个学生，{$notrowcount}个学生失败!";
    }else{
        $data ['result'] = false;
        $message = "操作失败，你选择的课程不存在或已删除!";
    }
    $data ['msg'] = $message;
    die (json_encode($data));
}elseif($operation == 'GetSearchKc'){
  
    if (! $_GPC ['schoolid']) {
            die ( json_encode ( array (
                'result' => false,
                'msg' => '非法请求！' 
                    ) ) );
    }else{
        $data = array();
        if(trim($_GPC['kcname']) != ''){
            $kclist = pdo_fetchall("SELECT id,name FROM " . GetTableName('tcourse') . " where schoolid = '{$_GPC['schoolid']}' and weid='{$_W['uniacid']}' And name LIKE '%{$_GPC['kcname']}%' ORDER BY id DESC");
        }else{
            $kclist = pdo_fetchall("SELECT id,name FROM " . GetTableName('tcourse') . " where schoolid = '{$_GPC['schoolid']}' and weid='{$_W['uniacid']}'  ORDER BY id DESC");
        }

        if($kclist){
            $data ['kclist'] = $kclist;
            $data ['result'] = true;
            $data ['msg'] = '成功获取！';
        }else{
            $data ['result'] = false;
            $data ['dd'] = $_GPC['kcname'];
            $data ['msg'] = '无法查找到此课程，请确认名称';			
        }
        die ( json_encode ( $data ) );
        
    }
  
}elseif($operation=='ChangeOverTime'){
    $idArr = $_GPC['idArr'];
    $OverTime = strtotime(trim($_GPC['NewOverTime'])) + 86399;
    foreach($idArr as $value){
        pdo_update(GetTableName('coursebuy',false),array('overtime'=>$OverTime),array('id'=>$value));
    }
    $data ['result'] = true;
    $data ['msg'] = "操作成功！";
    die (json_encode($data));
}elseif($operation=='GetStuHuiFang'){
    $id = $_GPC['id'];
    $info = pdo_fetch("SELECT * FROM ".GetTableName('coursebuy')." WHERE  id = '{$id}'  ");
    $studentName = pdo_fetch("SELECT s_name FROM ".GetTableName('students')." WHERE  id = '{$info['sid']}'  ")['s_name'];
    $coursename = pdo_fetch("SELECT name FROM ".GetTableName('tcourse')." WHERE  id = '{$info['kcid']}'  ")['name'];
    $huifanglist = pdo_fetchall("SELECT * FROM ".GetTableName('stuoverhuifang')." WHERE recordid = '{$id}' ");
    $reallist =[];
    foreach ($huifanglist as $key => $value) {
        if($value['tid'] != -1){
            $teacher = pdo_fetch("SELECT tname FROM ".GetTableName('teachers')." WHERE id = '{$value['tid']}' ");
            $tname = $teacher['tname'];
        }else{
            $tname= '管理员';
        }
        $ctime = date("m-d H:i",$value['createtime']);
        $reallist[] = array(
            'content' => $value['content'],
            'tname' =>  $tname,
            'ctime' => $ctime
        );
    } 
    if(!empty($reallist)){
        $is_Data = true;
    }else {
        $is_Data = false;
    }
    $return_Data = array(
        'sid' => $info['sid'],
        'sname' => $studentName,
        'kcname' =>$coursename,
        'list' => $reallist,
        'is_Data' => $is_Data
    );
    $data['result'] = true;
    $data['data'] = $return_Data;
    $data['msg'] = "操作成功！";
    die (json_encode($data));
}elseif($operation=='SetHuiFang'){
    $text = $_GPC['huifangtext'];
    $sid = $_GPC['sid'];
    $recordid = $_GPC['recordid'];
    $tid = $_GPC['tid'];
    
    $checkTea = pdo_fetch("SELECT * FROM ".GetTableName('teachers')." WHERE id = '{$tid}' ");
    if(!empty($checkTea)){
        $tid_real = $tid;
    }else{
        $tid_real = -1;
    }
    $insertData = array(
        'schoolid' => $schoolid,
        'weid' => $weid,
        'sid' =>  $sid,
        'tid' => $tid_real,
        'recordid' => $recordid,
        'content' => $text,
        'createtime' => time()
    );
    pdo_insert(GetTableName('stuoverhuifang',false),$insertData);
    $data ['result'] = true;
    $data ['msg'] = "操作成功！";
    die (json_encode($data));


     
}
include $this->template ( 'web/stuovertime' );
?>