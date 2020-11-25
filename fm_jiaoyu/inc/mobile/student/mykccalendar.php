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
$time = $_GPC['time'];
$logid = trim($_GPC['logid']);	
if (!empty($_GPC['userid'])){
    $_SESSION['user'] = $_GPC['userid'];
}
$it = pdo_fetch("SELECT * FROM " . GetTableName('user') . " where id = :id And openid = :openid ", array(':id' => $_SESSION['user'],':openid' => $openid));
$school = pdo_fetch("SELECT spic,style2 FROM " . GetTableName('index') . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
$student = pdo_fetch("SELECT s_name,bj_id FROM " . GetTableName('students') . " where id = :id AND schoolid = :schoolid ", array(':id' => $it['sid'], ':schoolid' => $schoolid));
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if(!empty($it)){
    if($operation == 'display'){
        $timeframe = pdo_fetchall("SELECT * FROM " .  GetTableName('classify')  . " WHERE weid = '{$weid}' And type = 'timeframe' And schoolid = '{$schoolid}' ORDER BY ssort DESC, sid ASC");
        $mykclist = pdo_fetchall("SELECT kcid,sid FROM " . GetTableName('coursebuy') . " WHERE schoolid = :schoolid And sid = :sid and is_change != :is_change group by kcid ", array( ':schoolid' => $schoolid, ':sid' => $it['sid'], 'is_change' => 1 )); 
        if($mykclist){
            foreach($mykclist as $key=> $row){
                $kcinfo = pdo_fetch("SELECT name,kc_type FROM ".GetTableName('tcourse')." WHERE  id = '{$row['kcid']}' ");
                if($kcinfo && $kcinfo['kc_type'] == 0){
                    $mykclist[$key]['kcname'] = $kcinfo['name'];
                }else{
                    unset($mykclist[$key]);
                }
            }
        }
		include $this->template(''.$school['style2'].'/kc/kctable');
    }
    if($operation == 'week_header'){
        $w = date('w')? date('w') : 7;
        $nowweekstart = mktime(0,0,0,date('m'),date('d')-$w+1,date('Y')) + $_GPC['dtweek'] * 7 * 86400;
        $nowweekend = mktime(0,0,0,date('m'),date('d')-$w+8,date('Y')) + $_GPC['dtweek'] * 7 * 86400;
        if($_GPC['dtweek'] != 0){
            $result['tiptitle'] = '回本周';
        }else{
            $result['tiptitle'] = '本周';
        }
        $result['tiptime'] = date('Y.n.j',$nowweekstart).'-'.date('n.j',$nowweekend);
        die(json_encode($result));
    }
    if($operation == 'tx_ls'){
        $checksign = pdo_fetch("SELECT * FROM ".GetTableName('kcsign')." WHERE  id = '{$_GPC['signid']}'   ");
        if(!empty($checksign)){
            $this->sendMobileXsqdks($_GPC['signid'], $checksign['schoolid'], $checksign['weid']);
            $result['result'] = true;  
            $result['msg'] = '提醒老师成功，请等待老师确认';
        }else{
            $result['result'] = false;  
            $result['msg'] = '抱歉,未查询到你的签到记录,请刷新本页';
        }
        die(json_encode($result));
    }
    if($operation == 'sign_ks'){
        $ksid = intval($_GPC['ksid']);
        $checksign = pdo_fetch("SELECT * FROM ".GetTableName('kcsign')." WHERE  ksid = '{$ksid}'  And sid = '{$it['sid']}' ");
        $result['xugou'] = false;
        if(empty($checksign)){
            $ksinfo = pdo_fetch("SELECT weid,kcid,schoolid,costnum FROM ".GetTableName('kcbiao')." WHERE  id  = '{$ksid}'  ");
            $kcinfo = pdo_fetch("SELECT sign_pl_set,allow_pl,name FROM ".GetTableName('tcourse')." WHERE  id  = '{$ksinfo['kcid']}'  ");
            $signset = pdo_fetch("SELECT stu_sign_confirm FROM " . GetTableName('kc_signset') . " WHERE id = :id ", array(':id' => $kcinfo['sign_pl_set']));
            $checkrestks = GetRestKsBySid($ksinfo['kcid'],$it['sid']);
            $restks = $checkrestks['restnumber'];
            if($restks > $ksinfo['costnum']){
                $data = array(
                    'kcid' => $ksinfo['kcid'],
                    'ksid' => $ksid,
                    'schoolid' => $ksinfo['schoolid'],
                    'weid' => $ksinfo['weid'],
                    'sid'  => $it['sid'],
                    'kcname'  => $kcinfo['name'],
                    'createtime' => time(),
                    'status' => 1,
                    'type' => 1 ,
                    'costnum' => $ksinfo['costnum'] ,
                    'signtype' => 1 ,
                );
                if($signset['stu_sign_confirm'] == 1){
                    $needconfirm = true;
                    $result['msg'] = '签到成功，请等待老师确认';
                }else{
                    $data['status'] = 2;
                    $needconfirm = false;
                    $result['msg'] = '签到成功！';
                }
                pdo_insert(GetTableName('kcsign',false), $data);
                $insertid = pdo_insertid();
                $this->sendMobileXsqdks($insertid, $ksinfo['schoolid'], $ksinfo['weid']);
                if($kcinfo['allow_pl'] == 1){
                    $needpl = true;
                }else{
                    $needpl = false;
                }
                $result['kcid'] = $ksinfo['kcid'];  
                $result['ksid'] =  $ksid;  
                $result['insertid'] =  $insertid; 
                $result['needpl'] = $needpl;  
                $result['needconfirm'] = $needconfirm;  
                $result['result'] = true;  
            }else{
                $result['xugou'] = true;
                $result['msg'] = "抱歉，签到本课需要".$ksinfo['costnum']."课时,你当前剩余".$restks."课时不足扣除，是否立即前往续购";  
                $result['result'] = false;     
            }
        }else{
            if($checksign['status'] == 2){
                $result['msg'] = '抱歉，您已签到本节，请勿重复签到';
            }
            if($checksign['status'] == 3){
                $result['msg'] = '抱歉，本节您已经标记为请假状态，如需签到请联系老师';
            }
            if($checksign['status'] == 1){
                $result['msg'] = '抱歉，本节您已签到，请等待老师确认哦';
            }
            if($checksign['status'] == 0){
                $result['msg'] = '抱歉，本节您已经标记为缺课状态，如需签到请联系老师';
            }
            $result['result'] = false;
        }
        die(json_encode($result));
    }
    if($operation == 'kslist'){
        $nowtime = time();
        $w = date('w')? date('w') : 7;
        $nowweekstart = mktime(0,0,0,date('m'),date('d')-$w+1,date('Y')) + $_GPC['dtweek'] * 7 * 86400;
        $nowweekend = mktime(0,0,0,date('m'),date('d')-$w+8,date('Y')) + $_GPC['dtweek'] * 7 * 86400;
        $condition = ''; $kcarr = false;
        $mykclist = pdo_fetchall("SELECT kcid,sid FROM " . GetTableName('coursebuy') . " WHERE schoolid = :schoolid And sid = :sid and is_change != :is_change group by kcid ", array( ':schoolid' => $schoolid, ':sid' => $it['sid'], 'is_change' => 1 ));
        $kcarr = array();
        if(!empty($mykclist)){
            foreach($mykclist as $k =>$r ){
                $kcarr[] = $r['kcid'];
            }
        }else{
            $kcarr[] = 0;//防止没有任何报名的学生取值
        }
        $kcid = 0;
        mload()->model('kc');
        $list = GetKcInfo($weid, $schoolid, $condition,$kcid,$nowweekstart,$nowweekend,$kcarr);
        $mykcnub = count($list);
        $Data = array(
            0=>array(
                'title' => "周一",
                'date' => date('n月j日',$nowweekstart),
                'index'=> 7
            ),
            1=>array(
                'title' => "周二",
                'date' => date('n月j日',$nowweekstart + 86400 ),
                'index'=> 6
            ),
            2=>array(
                'title' => "周三",
                'date' => date('n月j日',$nowweekstart + 86400*2 ),
                'index'=> 5
            ),
            3=>array(
                'title' => "周四",
                'date' => date('n月j日',$nowweekstart + 86400*3 ),
                'index'=> 4
            ),
            4=>array(
                'title' => "周五",
                'date' => date('n月j日',$nowweekstart + 86400*4 ),
                'index'=> 3
            ),
            5=>array(
                'title' => "周六",
                'date' => date('n月j日',$nowweekstart + 86400*5 ),
                'index'=> 2
            ),
            6=>array(
                'title' => "周日",
                'date' => date('n月j日',$nowweekstart + 86400*6 ),
                'index'=> 1
            ),
        );
        foreach($list as $key => $value){
            $weekOrder = $value['week'];
            $checksign = pdo_fetch("SELECT * FROM ".GetTableName('kcsign')." WHERE  ksid = '{$value['id']}'  And sid = '{$it['sid']}' ");
            $checkstupj = pdo_fetch("SELECT * FROM ".GetTableName('kcpingjia')." WHERE  ksid = '{$value['id']}'  And sid = '{$it['sid']}' And tid = 0 ");
            $checkteapj = pdo_fetch("SELECT * FROM ".GetTableName('kcpingjia')." WHERE  ksid = '{$value['id']}'  And tosid = '{$it['sid']}'  And tid > 0 ");
            $stupj = false;$teapj = true; if(!empty($checkstupj)){ $stupj = true; } if(!empty($checkteapj)){ $teapj = true; }
            if($weekOrder != 0){
                $Data[$weekOrder - 1]['data'][$value['sd_id']]['teapj'] = $teapj;
                $Data[$weekOrder - 1]['data'][$value['sd_id']]['stupj'] = $stupj;
                $Data[$weekOrder - 1]['data'][$value['sd_id']]['checksign'] = $checksign;
                $Data[$weekOrder - 1]['data'][$value['sd_id']]['data'][] = $value;
                $Data[$weekOrder - 1]['data'][$value['sd_id']]['start_time'] = $value['sd_start'];
                $Data[$weekOrder - 1]['data'][$value['sd_id']]['end_time'] = $value['sd_end'];
            }else{
                $Data[$weekOrder + 6]['data'][$value['sd_id']]['teapj'] = $teapj;
                $Data[$weekOrder + 6]['data'][$value['sd_id']]['stupj'] = $stupj;
                $Data[$weekOrder + 6]['data'][$value['sd_id']]['checksign'] = $checksign;
                $Data[$weekOrder + 6]['data'][$value['sd_id']]['data'][] = $value;
                $Data[$weekOrder + 6]['data'][$value['sd_id']]['start_time'] = $value['sd_start'];
                $Data[$weekOrder + 6]['data'][$value['sd_id']]['end_time'] = $value['sd_end'];
            } 
        }
        // var_dump($list);
        include $this->template(''.$school['style2'].'/kc/kctable_temp');
    }
}else{
    session_destroy();
    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
    header("location:$stopurl");
    exit;
}        
?>