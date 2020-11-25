<?php
/**
 * 学生端 查看 进出校 以及 归寝 考勤记录 
 * @copyright 2019 微美科技
 * @author Hannibal·Lee <No@email.com>
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
$opereation = $_GPC['op'] ? $_GPC['op'] : 'display';
$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where id = :id And openid = :openid ", array(':id' => $_SESSION['user'],':openid' => $openid));
$school = pdo_fetch(" SELECT * FROM".GetTableName('index')." WHERE weid = '{$weid}' AND id = '{$schoolid}' ");
if(!empty($it)){
    if($opereation == 'display'){
        $endtime = strtotime(date("Y-m-d",time())) + 86399 ;
        $starttime = $endtime - 8*86400 + 1;
        $list = pdo_fetchall("SELECT * FROM ".GetTableName('checklog') ." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' And sid = '{$it['sid']}' and createtime >='{$starttime}' and createtime <='{$endtime}'  ORDER BY createtime DESC LIMIT 0,20 ");
        foreach($list as $key=>$value){
            if($value['sc_ap'] == 0){
                $list[$key]['logtype'] = $value['type']; 
            }elseif($value['sc_ap'] == 1){
                $list[$key]['logtype'] = $value['ap_type'] == 1 ? "进寝":"离寝"; 
            }
        }
        //var_dump($list);
        include $this->template(''.$school['style2'].'/smychecklog');
    }elseif($opereation == 'More_Data'){
        $More_starttime = strtotime($_GPC['StartDate']);
        $More_endtime = strtotime($_GPC['EndDate']) + 86399;
        $Limit_start = $_GPC['LiData']['time'] +1 ;
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
        $list1 = pdo_fetchall("SELECT * FROM ".GetTableName('checklog') ." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' And sid = '{$it['sid']}' {$condition} and createtime >='{$More_starttime}' and createtime <='{$More_endtime}'    ORDER BY createtime DESC LIMIT {$Limit_start},20 ");
        foreach($list1 as  $key_1=>$value_1){
            if($value_1['sc_ap'] == 0){
                $list1[$key_1]['logtype'] = $value_1['type']; 
            }elseif($value_1['sc_ap'] == 1){
                $list1[$key_1]['logtype'] = $value_1['ap_type'] == 1 ? "进寝":"离寝"; 
            }
            $list1[$key_1]['location'] = $key_1 + $Limit_start;
        }
        //var_dump( $More_starttime);
        include $this->template('comtool/smychecklog');
    }elseif($opereation == 'GetDetail'){
        $id = $_GPC['id'];
        $Info = pdo_fetch("SELECT * FROM ".GetTableName('checklog')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' and id = '{$id}' ");
        $CheckStu = pdo_fetch("SELECT s_name FROM ".GetTableName('students'). " WHERE id = '{$Info['sid']}' ");
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
            'StuName'=>$CheckStu['s_name'],
            'MacName'=>$Mac['name']?$Mac['name']:'未知设备',
            'Status' => $status,
            'CheckTime' => date("Y-m-d H:i",$Info['createtime'])
        );
        die(json_encode($result));


    }
}else{
    session_destroy();
    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
    header("location:$stopurl");
    exit;
}