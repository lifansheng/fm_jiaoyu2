<?php
global $_GPC,$_W;
$op = $_GPC['op'] ? $_GPC['op'] : 'display' ;
$schoolid = $_GPC['schoolid'];
$weid = $_GPC['i'];
mload()->model('vod');
if($op == 'display'){
    $isopen = pdo_fetch("SELECT is_tx_tea FROM ".GetTableName('schoolset')." WHERE schoolid = '{$schoolid}' AND weid = '{$weid}' ")['is_tx_tea'];
    if($isopen == 1){ //开始上课提醒
        $nowdate = strtotime(date("Y-m-d",time()));
        // 一对一老师
        $kebiao = pdo_fetchall("SELECT g.*,t.bj_id,t.title,u.tid FROM ".GetTableName('glkebiao')." AS g LEFT JOIN " . GetTableName('timetable') . " AS t ON g.kbid = t.id LEFT JOIN " . GetTableName('user_class') . " as u ON u.km_id = g.kmid AND u.bj_id = t.bj_id AND u.type = 1 WHERE g.schoolid = '{$schoolid}' AND g.date = '{$nowdate}' AND g.is_send = 0 ");
        //一对多
        // $kebiao = pdo_fetchall("SELECT g.*,t.bj_id,t.title FROM ".GetTableName('glkebiao')." AS g LEFT JOIN " . GetTableName('timetable') . " AS t ON g.kbid = t.id WHERE g.schoolid = '{$schoolid}' AND g.date = '{$nowdate}' ");
        if(!empty($kebiao)){
            foreach ($kebiao as $key => $value) {
                //开课时间
                $starttime = $value['starttime'] - 10*60;
                //提醒时间
                $nowtime = time();
                if($nowtime >= $starttime){
                    /*******************8这是一对多，发送给多个老师(含助教)TODO:暂时不用 ***********/
                    // $tidarr = pdo_fetchall("SELECT tid FROM ".GetTableName('user_class')." WHERE km_id = '{$value['kmid']}' AND bj_id = '{$value['bj_id']}' AND type = 1 ");
                    // if(!empty($tidarr)){
                    //     $tid = arrayToString($tidarr);
                    // }
                    // $this->sendMobileZHXZYJssktx($value['id'],$tid);
                    /*******************8这是一对多，发送给多个老师(含助教)TODO:暂时不用 ***********/
                    if(!empty($value['tid'])){
                        mload()->model('vod');
                        $hasholiday = check_holiday($value['bj_id'],$value['starttime']);
                        $isshow = pdo_fetch("SELECT ishow FROM ".GetTableName('timetable')." WHERE schoolid = '{$schoolid}' AND bj_id = '{$value['bj_id']}' ")['ishow'];
                        if($hasholiday == true && $isshow == 1){
                            $this->sendMobileZHXZYJssktx($value['id'],$value['tid'],$value['bj_id']);
                        }
                        pdo_update(GetTableName('glkebiao',false),array('is_send'=>1),array('id'=>$value['id']));
                    }
                }
            }
        }
    }
}
