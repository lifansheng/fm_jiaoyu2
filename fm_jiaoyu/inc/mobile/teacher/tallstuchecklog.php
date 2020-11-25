<?php
/**
 * 教师端 查看 进出校 以及 归寝 考勤记录 
 * @copyright 2019 微美科技
 * @author Hannibal·Lee <No@email.com>
 */

global $_W, $_GPC;
$weid = $_W['uniacid'];
$schoolid = intval($_GPC['schoolid']);
$openid = $_W['openid'];
$opereation = $_GPC['op'] ? $_GPC['op'] : 'display';
$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $userid['id']));
$tid = $it['tid'];
$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
if(!empty($it)){
    $teacher = pdo_fetch("SELECT * FROM ".GetTableName("teachers")." WHERE id = '{$tid}' ");
    mload()->model('tea');
    $njlist = GetTeaManagerBjlist($weid,$schoolid,$tid);

    if($teacher['status'] != 2){ //如果不是校长 取可管辖年级
        $firstNj = $njlist[0];
        if(!empty($firstNj)){ //如果有可管辖年级
            $firstBj = $firstNj['bjlist'][0];
            $NjN = $firstNj['sname'];
            $NjId = $firstNj['sid'];
            $NjKey = 0;
            $Nj_Bj = pdo_fetchall("SELECT sid FROM ".GetTableName('classify')." WHERE parentid = '{$NjId}' and type = 'theclass' and weid = '{$weid}' and schoolid = '{$schoolid}'  ");
            $Nj_Bj_str = '';
            foreach($Nj_Bj as $value){
                $Nj_Bj_str .= $value['sid'].",";
            }
            $BjStr = trim($Nj_Bj_str,',');
            $bjcondition =" AND FIND_IN_SET(bj_id,'{$BjStr}') "; //查看年级下的所有


        }else{
            $NjN = "无管辖年级";
            $NjKey = -1;
            $BjId = -1;
            $NjId = -1;
        }
        if($teacher['status'] != 3){ //但是，如果不是年级主任 则只能取年级下的可管辖班级
            if(!empty($firstBj)){
                $bjcondition = "and bj_id = '{$firstBj['sid']}'"; //条件变更为查看班级
                $BjN = $firstBj['sname'];
                $BjId = $firstBj['sid'];
            }else{
                $BjN = "无管辖班级";
            }
        }else{
            $BjId = -1;
            $BjN = "不限班级";
        }
        
    }else{ //如果是校长
        $NjId = -1;
        $NjKey = -1;
        $BjId = -1;
        $NjN = "不限年级";
        $BjN = "不限班级";
        
    }
  
    if($opereation == 'display'){
        $endtime = strtotime(date("Y-m-d",time())) + 86399 ;
        $starttime = $endtime - 8*86400 + 1;
        $list = pdo_fetchall("SELECT * FROM ".GetTableName('checklog') ." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' {$bjcondition} and sid != 0 and tid = 0  and createtime >='{$starttime}' and createtime <='{$endtime}'  ORDER BY createtime DESC LIMIT 0,20 ");

        foreach($list as $key=>$value){
            $student = pdo_fetch("SELECT * FROM ".GetTableName('students')." WHERE id = '{$value['sid']}' ");
            $list[$key]['icon'] = $student['icon'] ?$student['icon'] : $school['spic'] ;
            $list[$key]['sname'] = $student['s_name'];
            if($value['sc_ap'] == 0){
                $list[$key]['logtype'] = $value['type']; 
            }elseif($value['sc_ap'] == 1){
                $list[$key]['logtype'] = $value['ap_type'] == 1 ? "进寝":"离寝"; 
            }
        }
        include $this->template(''.$school['style3'].'/tallstuchecklog');
    }elseif($opereation == 'More_Data'){
        $More_starttime = strtotime($_GPC['StartDate']);
        $More_endtime = strtotime($_GPC['EndDate']) + 86399;
        $Limit_start = $_GPC['LiData']['time'] ? $_GPC['LiData']['time'] +1 : 0 ;
        $condition = '';
        if( $_GPC['InSch'] == 'false' && $_GPC['OutSch'] == 'false' && $_GPC['ErrorSch'] == 'false' ){
            $condition .= ' and sc_ap != 0  ';
        }else{
            if($_GPC['InSch'] == 'false' ){
                $condition .= ' and leixing != 1  ';
            }
            if($_GPC['OutSch'] == 'false' ){
                $condition .= ' and leixing != 2  ';
            }
            if($_GPC['ErrorSch'] == 'false' ){
                $condition .= ' and leixing != 3  ';
            }
        }
        if($_GPC['InAp'] == 'false' && $_GPC['OutAp'] == 'false'){
            $condition .= ' and sc_ap != 1  ';
        }else{
            if($_GPC['InAp'] == 'false' ){
                $condition .= ' and ap_type != 1  ';
            }
            if($_GPC['OutAp'] == 'false' ){
                $condition .= ' and leixing != 2  ';
            } 
        }
        if($_GPC['Njid'] != -1 && $_GPC['Bjid'] == -1){
            $Nj_Bj = pdo_fetchall("SELECT sid FROM ".GetTableName('classify')." WHERE parentid = '{$_GPC['Njid']}' and type = 'theclass' and weid = '{$weid}' and schoolid = '{$schoolid}'  ");
            $Nj_Bj_str = '';
            foreach($Nj_Bj as $value){
                $Nj_Bj_str .= $value['sid'].",";
            }
            $BjStr = trim($Nj_Bj_str,',');
            $condition .=" AND FIND_IN_SET(bj_id,'{$BjStr}') ";
            
        }elseif($_GPC['Bjid'] != -1 && $_GPC['Bjid'] != 0){
            $condition .=" AND bj_id = '{$_GPC['Bjid']}' ";
        }
        $list1 = pdo_fetchall("SELECT * FROM ".GetTableName('checklog') ." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' And sid != 0  {$condition} and createtime >='{$More_starttime}' and createtime <='{$More_endtime}'    ORDER BY createtime DESC LIMIT {$Limit_start},20 ");
        
        foreach($list1 as  $key_1=>$value_1){
            $student = pdo_fetch("SELECT * FROM ".GetTableName('students')." WHERE id = '{$value_1['sid']}' ");
            $list1[$key_1]['icon'] = $student['icon'] ?$student['icon'] : $school['spic'] ;
            $list1[$key_1]['sname'] = $student['s_name'];
            if($value_1['sc_ap'] == 0){
                $list1[$key_1]['logtype'] = $value_1['type']; 
            }elseif($value_1['sc_ap'] == 1){
                $list1[$key_1]['logtype'] = $value_1['ap_type'] == 1 ? "进寝":"离寝"; 
            }
            $list1[$key_1]['location'] = $key_1 + $Limit_start;
        }
        //var_dump( $More_starttime);
        include $this->template('comtool/tallstuchecklog');
    }elseif($opereation == 'GetDetail'){
        $id = $_GPC['id'];
        $Info = pdo_fetch("SELECT * FROM ".GetTableName('checklog')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' and id = '{$id}' ");
        //var_dump($Info);
        $CheckStu = pdo_fetch("SELECT s_name,bj_id FROM ".GetTableName('students'). " WHERE id = '{$Info['sid']}' ");
        $bj_name = pdo_fetch("SELECT * FROM ".GetTableName('classify')." WHERE sid = '{$CheckStu['bj_id']}' and type = 'theclass' ");
        $Nj_name = pdo_fetch("SELECT * FROM ".GetTableName('classify')." WHERE sid = '{$bj_name['parentid']}' ");
        $Mac = pdo_fetch("SELECT name FROM ".GetTableName('checkmac'). " WHERE id = '{$Info['macid']}' ");
        if($Info['checktype'] == 2){
            $Mac['name'] = '微信签到';
        }
        $pard = '';
        switch ($Info['pard']) {
            case 1:
                $pard = '本人';
                break;
            case 2:
                $pard = '母亲';
                break;
            case 3:
                $pard = '父亲';
                break;
            default:
                $pard = '其他家长';
                break;
        }
        $status = '';
        if($Info['sc_ap'] == 0){
            $status = $Info['type']; 
        }elseif($Info['sc_ap'] == 1){
            $status = $Info['ap_type'] == 1 ? "进寝":"离寝"; 
        }
        $result['status'] = true;
        $result['data'] = array(
            'pard'=>$pard,
            'bjname'=>$bj_name['sname'],
            'njname'=>$Nj_name['sname'],
            'StuName'=>$CheckStu['s_name'],
            'MacName'=>$Mac['name']?$Mac['name']:'未知设备',
            'Status' => $status,
            'CheckTime' => date("Y-m-d H:i",$Info['createtime'])
        );
        die(json_encode($result));
    }elseif($opereation == 'GetNjListData'){
        $firstNj = $njlist[0];
        if(!empty($firstNj)){
            $firstBj = $firstNj['bjlist'][0];
            $NjN = $firstNj['sname'];
        }else{
            $NjN = "不限年级";
        }
        if(!empty($firstBj)){
            $bjcondition = "and bj_id = '{$firstBj['sid']}'";
            $BjN = $firstBj['sname'];
        }else{
            $BjN = "不限班级";
        }
        die(json_encode($njlist));

    }
}else{
    session_destroy();
    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
    header("location:$stopurl");
    exit;
}