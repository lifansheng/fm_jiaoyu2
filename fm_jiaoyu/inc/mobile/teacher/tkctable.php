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
$it = pdo_fetch("SELECT id,tid FROM " . GetTableName('user') . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0));
$school = pdo_fetch("SELECT title,is_recordmac,style3,headcolor,spic,tpic FROM " . GetTableName('index') . " where weid = :weid AND id=:id", array(':weid' => $weid, ':id' => $schoolid));
$tid_global = $it['tid'];
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if(!empty($it)){
    if($operation == 'display'){
        $timeframe = pdo_fetchall("SELECT * FROM " .  GetTableName('classify')  . " WHERE weid = '{$weid}' And type = 'timeframe' And schoolid = '{$schoolid}' ORDER BY ssort DESC, sid ASC");
        $mykclist = pdo_fetchall("SELECT id,name FROM " . GetTableName('tcourse') . " WHERE weid = {$weid} And schoolid = '{$schoolid}' And kc_type = 0 And end > '{$nowtime}' And (tid like '{$it['tid']},%' OR tid like '%,{$it['tid']}' OR tid like '%,{$it['tid']},%' OR tid='{$it['tid']}')  ORDER BY id DESC");
		include $this->template(''.$school['style3'].'/kc/tkctable');	
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
    if($operation == 'kslist'){
        $nowtime = time();
        $w = date('w')? date('w') : 7;
        $nowweekstart = mktime(0,0,0,date('m'),date('d')-$w+1,date('Y')) + $_GPC['dtweek'] * 7 * 86400;
        $nowweekend = mktime(0,0,0,date('m'),date('d')-$w+8,date('Y')) + $_GPC['dtweek'] * 7 * 86400;
        $condition = ''; $kcarr = false;
        $mykclist = pdo_fetchall("SELECT id,name FROM " . GetTableName('tcourse') . " WHERE weid = {$weid} And schoolid = '{$schoolid}' And kc_type = 0 And end > '{$nowtime}' And (tid like '{$it['tid']},%' OR tid like '%,{$it['tid']}' OR tid like '%,{$it['tid']},%' OR tid='{$it['tid']}')  ORDER BY id DESC");
        if (!empty($_GPC['kcid'])) {
            $kcid      = intval($_GPC['kcid']);
            $condition .= "And kcid ='{$kcid}' ";
        }else{
            if(!empty($mykclist)){
                $kcarr = array();
                foreach($mykclist as $k =>$r ){
                    $kcarr[] = $r['id'];
                }
            }else{
                $kcarr[] = 0;
            }
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
            if($weekOrder != 0){
                $Data[$weekOrder - 1]['data'][$value['sd_id']]['data'][] = $value;
                $Data[$weekOrder - 1]['data'][$value['sd_id']]['start_time'] = $value['sd_start'];
                $Data[$weekOrder - 1]['data'][$value['sd_id']]['end_time'] = $value['sd_end'];
            }else{
                $Data[$weekOrder + 6]['data'][$value['sd_id']]['data'][] = $value;
                $Data[$weekOrder + 6]['data'][$value['sd_id']]['start_time'] = $value['sd_start'];
                $Data[$weekOrder + 6]['data'][$value['sd_id']]['end_time'] = $value['sd_end'];
            } 
        }
        include $this->template(''.$school['style3'].'/kc/kctable_temp');
    }
}else{
    session_destroy();
    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
    header("location:$stopurl");
}
?>