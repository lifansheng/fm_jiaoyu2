<?php
/**
 * 微教育模块
 * @author 高贵血迹
 * @url https://mac.weimeizhan.com
 */ 
//商城订单处理
function DealMallPayResult($order)
{
	$mallinfo = pdo_fetch("SELECT mallsetinfo FROM " . GetTableName('index') . " WHERE :schoolid = id AND weid=:weid ", array(':schoolid' => $order['schoolid'],':weid'=>$order['weid'] ));
    $mallinfoDE = iunserializer($mallinfo['mallsetinfo']);
    $auto = $mallinfoDE['isAuto'];
    if($auto == 1  ){
        pdo_update(GetTableName('mallorder',false), array('status' => 3), array('id' => $order['morderid']));
    }else{
        pdo_update(GetTableName('mallorder',false), array('status' => 2), array('id' => $order['morderid']));
    }
    $teaid = pdo_fetch("SELECT * FROM " . GetTableName('mallorder') . " where id = :id ", array(':id' => $order['morderid']));
    //教师订单
    if(!empty($teaid['tid']) && empty($teaid['sid'])){
        $teacher = pdo_fetch("SELECT point FROM " . GetTableName('teachers') . " where id = :id ", array(':id' => $teaid['tid']));
        if($teacher['point'] == $teaid['allpoint']){
            $new_point = 0 ;
        }else{
            $new_point = intval($teacher['point']) - intval($teaid['allpoint']);
        }
        pdo_update(GetTableName('teachers',false), array('point' => $new_point ), array('id' => $teaid['tid']));
    //学生订单
    }elseif(empty($teaid['tid'] ) && !empty($teaid['sid'])){
        $JFinfo =  pdo_fetch("SELECT Is_point,Cost2Point FROM " . GetTableName('index') . " WHERE :schoolid = id AND weid=:weid ", array(':schoolid' => $order['schoolid'],':weid'=>$order['weid'] ));
        if($JFinfo['Is_point'] ==1){
            $students = pdo_fetch("SELECT * FROM " . GetTableName('students') . " where id = :id ", array(':id' => $teaid['sid']));
            $money = $order['cose'];
            $Cost2Point = $JFinfo['Cost2Point'];
            $addpoint = intval($money * $Cost2Point);
            if($students['points'] == $teaid['allpoint']){
                $new_point = 0 + $addpoint;
            }else{
                $new_point = intval($students['points']) - intval($teaid['allpoint']) + $addpoint;
            }
            pdo_update(GetTableName('students',false), array('points' => $new_point ), array('id' => $teaid['sid']));
        }
    }
}

//充值订单
function DealCzPayResult($order)
{
    $sid = $order['sid'];
    $students = pdo_fetch("SELECT chongzhi FROM " . GetTableName('students') . " where :id = id", array(':id' =>$sid));
    $taocan = pdo_fetch("SELECT chongzhi FROM " . GetTableName('chongzhi') . " where :id = id", array(':id' =>$order['taocanid']));
    $new = $students['chongzhi'] + $taocan['chongzhi'];
    pdo_update(GetTableName('students',false),array('chongzhi'=>$new),array('id'=>$sid));
    $data_chongzhilog = array(
        'schoolid' 	=> $order['schoolid'],
        'weid'	   	=> $order['weid'],
        'sid'	   	=> $order['sid'],
        'yue_type' 	=> 2,
        'cost_type' => 1,
        'cost'	   	=> $taocan['chongzhi'],
        'costtime' 	=> $order['paytime'],
        'orderid'  	=> $order['id'], 
        'on_offline' => 1,
        'createtime' => time()
    );
    pdo_insert(GetTableName('yuecostlog',false),$data_chongzhilog);
}

//考勤卡费
function DealKqkfPayResult($order){
    $school = pdo_fetch("SELECT cardset FROM " . GetTableName('index') . " WHERE id = :id ", array(':id' => $order['schoolid']));
    $chard = pdo_fetch("SELECT severend FROM " . GetTableName('idcard') . " WHERE id = :id ", array(':id' => $order['bdcardid']));
    $card = unserialize($school['cardset']);
        if($card['cardtime'] == 1){
            $severend = $card['endtime1'] * 86400 + $chard['severend'];
        }else{
            $severend = $card['endtime2'];
        }				
    pdo_update(GetTableName('idcard',false), array('severend' => $severend), array('id' => $order['bdcardid']));
}

//充电桩充值
function DealCdzCzPayResult($order){
    $sid = $order['sid'];
    $students = pdo_fetch("SELECT chargenum FROM " . GetTableName('students') . " where :id = id", array(':id' =>$sid));
    $new = $students['chargenum'] + $order['ksnum'];
    pdo_update(GetTableName('students',false),array('chargenum'=>$new),array('id'=>$sid));
    $data_chongzhilog = array(
        'schoolid' 	=> $order['schoolid'],
        'weid'	   	=> $order['weid'],
        'sid'	   	=> $sid,
        'yue_type' 	=> 3,
        'cost_type' => 1,
        'cost'	   	=> $order['ksnum'],
        'costtime' 	=> $order['paytime'],
        'orderid'  	=> $order['id'],
        'on_offline' => 1,
        'createtime' => time()
    );
    pdo_insert(GetTableName('yuecostlog',false),$data_chongzhilog);
}

//课程订单处理
function DealKcPayResult($order)
{
	//新增学生
    mload()->model('kc');
    $shareset_t = pdo_fetch("SELECT shareset FROM " . GetTableName('index') . " WHERE :schoolid = id AND weid=:weid ", array(':schoolid' => $order['schoolid'],':weid'=>$order['weid'] ));
    $shareset = unserialize($shareset_t['shareset']);
    // var_dump($shareset);die;
    if($order['tempsid'] != 0){
        $tempstu = pdo_fetch("SELECT * FROM " . GetTableName('tempstudent') . " where :id = id", array(':id' => $order['tempsid']));
        $randStr = str_shuffle('123456789');
        $rand = substr($randStr,0,6);	
        $nj_id = pdo_fetch("SELECT parentid FROM " . GetTableName('classify') . " where :sid = sid", array(':sid' => $tempstu['bj_id']));
        $tempstudata = array(
            'schoolid' => $tempstu['schoolid'],
            'bj_id'=> $tempstu['bj_id'],
            'xq_id' => $nj_id['parentid'],
            'sex' => $tempstu['sex'],
            'createdate'=> time(),
            'seffectivetime' => time(),
            'code' => $rand,
            's_name' => $tempstu['sname'],
            'mobile'=> $tempstu['mobile'],
            'area_addr'=> $tempstu['adde'],
            'weid' => $tempstu['weid'],
        );
        pdo_insert(GetTableName('students',false),$tempstudata);
        $sid = pdo_insertid();
        pdo_update(GetTableName('students',false),array('keyid'=> $sid),array('id'=>$sid));
        $tempuinfo = array(
            'name' => '',
            'mobile'=> $tempstu['mobile']
        );
        $uinfo = serialize($tempuinfo);
        $userinsert = array(
            'sid' => $sid,
            'weid' => $tempstu['weid'],
            'schoolid' => $tempstu['schoolid'],
            'uid' => $tempstu['uid'],
            'openid' => $tempstu['openid'],
            'pard' => $tempstu['pard'],
            'userinfo' => $uinfo
        );
        pdo_insert(GetTableName('user',false),$userinsert);
        $userid_tostu = pdo_insertid();
        $into_stu = array();
        if($tempstu['pard'] == 2){
            $into_stu['mom'] = $tempstu['openid'];
            $into_stu['muserid'] = $userid_tostu;
            $into_stu['muid'] = $tempstu['uid']; 
        }
        if($tempstu['pard'] == 3){
            $into_stu['dad'] = $tempstu['openid'];
            $into_stu['duserid'] = $userid_tostu;
            $into_stu['duid'] = $tempstu['uid']; 
        }
        if($tempstu['pard'] == 4){
            $into_stu['own'] = $tempstu['openid'];
            $into_stu['ouserid'] = $userid_tostu;
            $into_stu['ouid'] = $tempstu['uid']; 
        }
        if($tempstu['pard'] == 5){
            $into_stu['other'] = $tempstu['openid'];
            $into_stu['otheruserid'] = $userid_tostu;
            $into_stu['otheruid'] = $tempstu['uid']; 
        }
        pdo_update(GetTableName('students',false),$into_stu,array('id'=>$sid));
        $into_order = array(
            'userid' => $userid_tostu,
            'sid' => $sid
        );
        pdo_update(GetTableName('order',false),$into_order,array('id'=>$order['id']));
        $order = pdo_fetch("SELECT * FROM " . GetTableName('order') . " where id = :id ", array(':id' =>$order['id']));
    }
    //课时购买/续购
    $kcinfo =  pdo_fetch("SELECT overtimeday,FirstNum,kc_type FROM " . GetTableName('tcourse') . " where :id = id", array(':id' => $order['kcid']));
    // var_dump($order);die;
    if($order['ksnum'] >= 1 || $kcinfo['kc_type'] == 1){
        if($order['team_id'] >= 1){//队伍订单，处理满员情况写入课时购买表内
            $team = pdo_fetch("SELECT id,masterid FROM " . GetTableName('sale_team') . " where :id = id ", array(':id' => $order['team_id']));
            if(!empty($team)){
                $teamisfull = CheckTemIsFull($team['id']);
                if($teamisfull){
                    SetTeamStuCour($order['kcid'],$team['masterid'],$order['ksnum'],$order['sale_type']);
                }
            }
        }else{
            $userinfo = pdo_fetch("SELECT sid,openid FROM " . GetTableName('user') . " where :id = id", array(':id' => $order['userid']));
            $ygks = pdo_fetch("SELECT ksnum,id FROM " . GetTableName('coursebuy') . " where kcid=:kcid AND :sid = sid", array(':kcid' => $order['kcid'],':sid'=>$userinfo['sid']));
            $overday = $kcinfo['overtimeday'];
            $overtime = 0;
            if($overday != 0 ){
                $overtime = strtotime(date("Y-m-d",time())) + 86399 + 86400*$overday;
            }
            if(!empty($ygks)){
                $newksnum = $ygks['ksnum'] + $order['ksnum'];
                $data_coursebuy = array(
                    'ksnum'      => $newksnum,
                    'overtime'  => $overtime
                );
                pdo_update(GetTableName('coursebuy',false),$data_coursebuy,array('id' => $ygks['id']));
            }else{
                $data_coursebuy = array(
                    'weid'       => $order['weid'],
                    'schoolid'   => $order['schoolid'],
                    'userid'     => $order['userid'],
                    'orderid'    => $order['id'],
                    'sid'        => $userinfo['sid'],
                    'kcid'       => $order['kcid'],
                    'ksnum'      => $kcinfo['FirstNum'],
                    'overtime'  => $overtime,
                    'createtime' => time()
                );
                pdo_insert(GetTableName('coursebuy',false),$data_coursebuy);
            }
            SetFansSale($order['kcid'],$order['userid'],0);
            if($kcinfo['kc_type'] == 1){
                pdo_update(GetTableName('user',false), array('status' => 0), array('id' => $order['userid']));
                pdo_update(GetTableName('students',false), array('status' => 0), array('id' => $order['sid']));
                if($order['superior_tid'] >0){
                    SetFansInfoByKc($order['kcid'],$userinfo['openid'],$order['userid'],$order['superior_tid'],$masteropenid,$masteruserid,1);
                }
            }
        }
    }
    $JFinfo=  pdo_fetch("SELECT Is_point,Cost2Point FROM " . GetTableName('index') . " WHERE :id = id ", array(':id' => $order['schoolid']));
    $student = pdo_fetch("SELECT points FROM " . GetTableName('students') . " where :id = id", array(':id' => $order['sid']));
    if($JFinfo['Is_point'] ==1){//课程购买赠送积分
        $money = $order['cose'];
        $Cost2Point = $JFinfo['Cost2Point'];
        $addpoint = $money * $Cost2Point;
        $costpoint = $order['spoint'];
        $oldpoint = $student['points'];
        $newpoint = $oldpoint - $costpoint + $addpoint;
    }else{
        $costpoint = $order['spoint'];
        $oldpoint = $student['points'];
        $newpoint = $oldpoint - $costpoint;
    }
    pdo_update(GetTableName('students',false), array('points' => $newpoint ), array('id' => $order['sid']));
    //分享增加积分、余额、课程课时
    if($shareset['is_share'] != 0 ){
         if($order['shareuserid'] != 0){
            $sharesid = pdo_fetch("SELECT sid FROM " . GetTableName('user') . " where :id = id", array(':id' => $order['shareuserid']));
             $student_share = pdo_fetch("SELECT * FROM " . GetTableName('students') . " where :id = id", array(':id' => $sharesid['sid']));
            $temp_student = array();
            //给分享源用户新增积分、余额、课时
            if($shareset['is_share'] == 1){
                //新增积分
                $AddJF = $shareset['addJF'];
                $oldJF = $student_share['points'];
                $newJF = $AddJF + $oldJF;
                //$temp_student['points'] = $newJF;
                pdo_update(GetTableName('students',false), array('points' => $newJF ), array('id' => $sharesid['sid']));
            }elseif($shareset['is_share'] == 2){
                //新增余额
                $AddYE = $shareset['addYE'];
                $oldYE = $student_share['chongzhi'];
                $newYE = $AddYE + $oldYE;
                //$temp_student['chongzhi'] = $newYE;
                pdo_update(GetTableName('students',false), array('chongzhi' => $newYE ), array('id' => $sharesid['sid']));
            }elseif($shareset['is_share'] == 3){
                //新增课时
                $AddKC = $order['kcid'];
                $AddKS = $shareset['addKS'];
                $kcinfo_share =  pdo_fetch("SELECT * FROM " . GetTableName('tcourse') . " where :id = id", array(':id' => $AddKC));
                $coursebuy =  pdo_fetch("SELECT ksnum,id FROM " . GetTableName('coursebuy') . " where kcid=:kcid AND :sid = sid", array(':kcid' => $AddKC,':sid'=>$sharesid['sid']));
                if(!empty($coursebuy)){
                    $newksnum = $coursebuy['ksnum'] + $AddKS;
                    if($newksnum > $kcinfo_share['AllNum']){
                        $newksnum = $kcinfo_share['AllNum'];
                    }
                    pdo_update(GetTableName('coursebuy',false), array('ksnum' => $newksnum ), array('id' => $coursebuy['id']));
                }
            } 
        }
    }
}

function getMyWelfare($logid,$status){
    $condition = " AND wf.status = '{$status}'";
    $welfare = pdo_fetch("SELECT wf.id,w.cose,w.maxrange,w.title FROM ".GetTableName('welfarelog')." as wf LEFT JOIN " . GetTableName('welfare') . " as w ON wf.welfareid = w.id WHERE wf.id = '{$logid}' $condition AND wf.time <= (w.day * 86400 + unix_timestamp(now()))");
    return $welfare;
}


function getMyAllWelfare($openid,$schoolid,$type){
    $welfare = pdo_fetchall("SELECT wf.id,w.cose,w.maxrange FROM ".GetTableName('welfarelog')." as wf LEFT JOIN " . GetTableName('welfare') . " as  w ON wf.welfareid = w.id AND w.type = '{$type}' WHERE wf.openid = '{$openid}' AND wf.schoolid = '{$schoolid}' AND wf.status = 1 AND wf.time <= (w.day * 86400 + unix_timestamp(now())) ");
    return $welfare;
}

