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

//查询是否用户登录		
$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $userid['id']));
$tid_global = $it['tid'];
$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
$op = $_GPC['op'] ? $_GPC['op'] : 'display';
if(!empty($userid['id'])){
    mload()->model('tea');
    $hasxz = pdo_fetch("SELECT status FROM ".GetTableName('teachers')." WHERE id = '{$tid_global}' ")['status'];
    if (IsHasQx($tid_global,2007103,2,$schoolid) || $hasxz == 2){
        $bjlist = pdo_fetchAll("SELECT sid,sname as old_sname,0 as khy FROM " . GetTableName('classify') . " where weid=:weid AND schoolid=:schoolid AND type=:type ORDER BY sid DESC", array(':weid' => $weid, ':schoolid' => $schoolid,':type' => 'theclass'));
        $bjstr = arrayToString(array_column($bjlist,'sid'));
    }else{
        $bjlist = GetAllClassInfoByTid($schoolid,2,$schooltype,$tid_global);
        foreach ($bjlist as $key => $value) {
            $bjlist[$key]['khy'] = 0;
            unset($bjlist[$key]['is_over']);
            unset($bjlist[$key]['ssort']);
            unset($bjlist[$key]['sname']);
            unset($bjlist[$key]['bj_id']);
        }
        $bjstr = arrayToString(array_column($bjlist,'sid'));
    }
    $khycate = pdo_fetchall("SELECT bjidstr FROM ".GetTableName('ddscorecategory')." WHERE schoolid='{$schoolid}' AND FIND_IN_SET('{$tid_global}',tid)");
    $khyBjidarr = [];
    foreach ($khycate as $key => $value) {
        $khyBjidarr[] = explode(',',$value['bjidstr']);
    }
    $khybjlist = [];
    if(!empty($khyBjidarr)){
        $khyBjidStr = arrayToString(array_unique(array_reduce($khyBjidarr, 'array_merge', array())));
        $khybjlist = pdo_fetchAll("SELECT sid,sname as old_sname,1 as khy FROM " . GetTableName('classify') . " where weid=:weid AND schoolid=:schoolid AND type=:type AND NOT FIND_IN_SET(sid,'{$bjstr}') AND FIND_IN_SET(sid,'{$khyBjidStr}') ORDER BY sid DESC", array(':weid' => $weid, ':schoolid' => $schoolid,':type' => 'theclass'));
    }
    $bjlist = array_merge($bjlist,$khybjlist);
    if(!empty($_GPC['bj_id'])){
        $bj_id = intval($_GPC['bj_id']);			
    }else{
        $bj_id = intval($bjlist[0]['sid']);
    }
    $start = date("Y-m-d",strtotime('-7 day'));
    $end   = date("Y-m-d",TIMESTAMP);
    $bjtotal = 0;
    $bjavg = 0;
    $teatotal = 0;
    $scoretotal = 0;
    $isKhy = pdo_fetch("SELECT id FROM ".GetTableName('ddscorecategory')." WHERE tid = '{$tid_global}' AND FIND_IN_SET('{$bj_id}',bjidstr) ");
    if($isKhy && !IsHasQx($tid_global,2007103,2,$schoolid) && $hasxz != 2){
        $khy = 1;
    }else{
        $khy = 0;
    }
    if($op == 'display'){
        $starttime = strtotime($start);
        $endtime = strtotime($end) + 86399;
        $condition .= " AND d.bjid = '{$bj_id}' ";
        $condition .= " AND d.date >= '{$starttime}' AND d.date <= '{$endtime}'";
        $list = pdo_fetchall("SELECT d.*, c.sname, t.tname FROM ".GetTableName('ddscorelog')." as d LEFT JOIN ".GetTableName('classify')." as c ON c.sid = d.bjid LEFT JOIN" . GetTableName('teachers') . " as t ON t.id = d.tid WHERE d.schoolid = '{$schoolid}' $condition GROUP BY d.date,d.bjid ORDER BY d.date DESC LIMIT 0,10");
        foreach ($list as $key => $value) {
            //获取当前班级当前日期的考核记录
            $log = pdo_fetchall("SELECT dl.cid,dl.score as num,dl.id,dc.* FROM ".GetTableName('ddscorelog')." as dl LEFT JOIN " . GetTableName('ddscorecategory'). " as dc ON dl.cid = dc.id WHERE dl.bjid = '{$bj_id}' AND dl.date = '{$value['date']}' ORDER BY dc.ssort DESC");
            
            //获取当前班级的总人数
            $stutotal = pdo_fetchcolumn("SELECT count(id) FROM ".GetTableName('students')." WHERE schoolid = '{$schoolid}' AND bj_id = '{$value['bjid']}' ");
            //当前班级的倍率
            $bjrate = pdo_fetch("SELECT bjrate,bzrrate FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' AND sid = '{$value['bjid']}' ");
            $totalscore = [
                1=>0,
                2=>0,
            ];
            foreach ($log as $k => $v) {
                if($v['type'] == 1){
                    $rate = $bjrate['bjrate'];
                }else{
                    $rate = $bjrate['bzrrate'];
                }
                if($v['addition'] == 1){
                    $totalscore[$v['type']] += $v['num'] * $rate;
                }else{  
                    $totalscore[$v['type']] -= $v['num'] * $rate;
                }
            }
            $list[$key]['tname'] = $value['tname'] ? $value['tname'] : '总管理';
            $list[$key]['bjtotal'] = $totalscore[1];
            $list[$key]['bjavg'] = round($totalscore[1] / $stutotal,2);
            $list[$key]['teatotal'] = $totalscore[2];
            $list[$key]['scoretotal'] = $totalscore[1]+$totalscore[2];
        }
        include $this->template(''.$school['style3'].'/tddscorelooklist');
        die;
    }elseif($op == 'getscorelist'){
        $bj_id = $_GPC['bj_id'];
        $khy = $_GPC['khy'];
        $start = strtotime($_GPC['start']);
        $end = strtotime($_GPC['end']) + 86399;
        $condition .= " AND d.bjid = '{$bj_id}' ";
        $condition .= " AND d.date >= '{$start}' AND d.date <= '{$end}'";
        $list = pdo_fetchall("SELECT d.*, c.sname, t.tname FROM ".GetTableName('ddscorelog')." as d LEFT JOIN ".GetTableName('classify')." as c ON c.sid = d.bjid LEFT JOIN" . GetTableName('teachers') . " as t ON t.id = d.tid WHERE d.schoolid = '{$schoolid}' $condition GROUP BY d.date,d.bjid ORDER BY d.date DESC LIMIT 0,10");
        foreach ($list as $key => $value) {
            //获取当前班级当前日期的考核记录
            $log = pdo_fetchall("SELECT dl.cid,dl.score as num,dl.id,dc.* FROM ".GetTableName('ddscorelog')." as dl LEFT JOIN " . GetTableName('ddscorecategory'). " as dc ON dl.cid = dc.id WHERE dl.bjid = '{$bj_id}' AND dl.date = '{$value['date']}' ORDER BY dc.ssort DESC");
            
            //获取当前班级的总人数
            $stutotal = pdo_fetchcolumn("SELECT count(id) FROM ".GetTableName('students')." WHERE schoolid = '{$schoolid}' AND bj_id = '{$value['bjid']}' ");
            //当前班级的倍率
            $bjrate = pdo_fetch("SELECT bjrate,bzrrate FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' AND sid = '{$value['bjid']}' ");
            $totalscore = [
                1=>0,
                2=>0,
            ];
            foreach ($log as $k => $v) {
                if($v['type'] == 1){
                    $rate = $bjrate['bjrate'];
                }else{
                    $rate = $bjrate['bzrrate'];
                }
                if($v['addition'] == 1){
                    $totalscore[$v['type']] += $v['num'] * $rate;
                }else{  
                    $totalscore[$v['type']] -= $v['num'] * $rate;
                }
            }
            $list[$key]['tname'] = $value['tname'] ? $value['tname'] : '总管理';
            $list[$key]['bjtotal'] = $totalscore[1];
            $list[$key]['bjavg'] = round($totalscore[1] / $stutotal,2);
            $list[$key]['teatotal'] = $totalscore[2];
            $list[$key]['scoretotal'] = $totalscore[1]+$totalscore[2];
        }
        //统计数据
        $tjlist = pdo_fetchall("SELECT d.date,d.bjid FROM ".GetTableName('ddscorelog')." as d LEFT JOIN ".GetTableName('classify')." as c ON c.sid = d.bjid LEFT JOIN" . GetTableName('teachers') . " as t ON t.id = d.tid WHERE d.schoolid = '{$schoolid}' $condition GROUP BY d.date,d.bjid ORDER BY d.date DESC");
        foreach ($tjlist as $key => $value) {
            //获取当前班级当前日期的考核记录
            $log = pdo_fetchall("SELECT dl.cid,dl.score as num,dl.id,dc.* FROM ".GetTableName('ddscorelog')." as dl LEFT JOIN " . GetTableName('ddscorecategory'). " as dc ON dl.cid = dc.id WHERE dl.bjid = '{$bj_id}' AND dl.date = '{$value['date']}' ORDER BY dc.ssort DESC");
            //获取当前班级的总人数
            $stutotal = pdo_fetchcolumn("SELECT count(id) FROM ".GetTableName('students')." WHERE schoolid = '{$schoolid}' AND bj_id = '{$value['bjid']}' ");
            //当前班级的倍率
            $bjrate = pdo_fetch("SELECT bjrate,bzrrate FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' AND sid = '{$value['bjid']}' ");
            $totalscore = [
                1=>0,
                2=>0,
            ];
            foreach ($log as $k => $v) {
                if($v['type'] == 1){
                    $rate = $bjrate['bjrate'];
                }else{
                    $rate = $bjrate['bzrrate'];
                }
                if($v['addition'] == 1){
                    $totalscore[$v['type']] += $v['num'] * $rate;
                }else{  
                    $totalscore[$v['type']] -= $v['num'] * $rate;
                }
            }
            $bjtotal += $totalscore[1];
            $bjavg += round($totalscore[1] / $stutotal,2);
            $teatotal +=  $totalscore[2];
            $scoretotal += $totalscore[1]+$totalscore[2];
        }
        include $this->template(''.$school['style3'].'/tddscorelookdetail');
        die;
    }elseif($op == 'scroll_more'){
        $scroll = true;
        $khy = $_GPC['khy'];
        $bj_id = $_GPC['ExtraData']['bj_id'];
        $start = strtotime($_GPC['ExtraData']['start']);
        $end = strtotime($_GPC['ExtraData']['end']) + 86399;
        $condition .= " AND d.bjid = '{$bj_id}' ";
        $condition .= " AND d.date >= '{$start}' AND d.date <= '{$end}'";
        $limit_start = $_GPC['ExtraData']['time'] + 1;
        //TODO:测试下啦滚动
        $list = pdo_fetchall("SELECT d.*, c.sname, t.tname FROM ".GetTableName('ddscorelog')." as d LEFT JOIN ".GetTableName('classify')." as c ON c.sid = d.bjid LEFT JOIN" . GetTableName('teachers') . " as t ON t.id = d.tid WHERE d.schoolid = '{$schoolid}' $condition GROUP BY d.date,d.bjid ORDER BY d.date DESC LIMIT {$limit_start},10");
        foreach ($list as $key => $value) {
            //获取当前班级当前日期的考核记录
            $log = pdo_fetchall("SELECT dl.cid,dl.score as num,dl.id,dc.* FROM ".GetTableName('ddscorelog')." as dl LEFT JOIN " . GetTableName('ddscorecategory'). " as dc ON dl.cid = dc.id WHERE dl.bjid = '{$bj_id}' AND dl.date = '{$value['date']}' ORDER BY dc.ssort DESC");
            
            //获取当前班级的总人数
            $stutotal = pdo_fetchcolumn("SELECT count(id) FROM ".GetTableName('students')." WHERE schoolid = '{$schoolid}' AND bj_id = '{$value['bjid']}' ");
            //当前班级的倍率
            $bjrate = pdo_fetch("SELECT bjrate,bzrrate FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' AND sid = '{$value['bjid']}' ");
            $totalscore = [
                1=>0,
                2=>0,
            ];
            foreach ($log as $k => $v) {
                if($v['type'] == 1){
                    $rate = $bjrate['bjrate'];
                }else{
                    $rate = $bjrate['bzrrate'];
                }
                if($v['addition'] == 1){
                    $totalscore[$v['type']] += $v['num'] * $rate;
                }else{  
                    $totalscore[$v['type']] -= $v['num'] * $rate;
                }
            }
            $list[$key]['tname'] = $value['tname'] ? $value['tname'] : '总管理';
            $list[$key]['bjtotal'] = $totalscore[1];
            $list[$key]['bjavg'] = round($totalscore[1] / $stutotal,2);
            $list[$key]['teatotal'] = $totalscore[2];
            $list[$key]['scoretotal'] = $totalscore[1]+$totalscore[2];
            $list[$key]['location'] = $key + $limit_start;
        }
        include $this->template(''.$school['style3'].'/tddscorelookdetail');
        die;
    }elseif($op == 'getInfo'){
        $condition = '';
        if($khy == 1){
            $condition = " AND FIND_IN_SET('{$tid_global}',tid)";
        }
        $list = pdo_fetchAll("SELECT id,type,title,addition FROM " . GetTableName('ddscorecategory') .  " WHERE schoolid = :schoolid $condition ORDER BY addition,ssort DESC", array(':schoolid' => $_GPC['schoolid']));
        $bjInfoList = [];
        $teaInfoList = [];
        foreach ($list as $key => $value) {
            $ddlog = pdo_fetch("SELECT score,remark FROM ".GetTableName('ddscorelog')." WHERE schoolid = '{$schoolid}' AND bjid = '{$_GPC['bj_id']}' AND date = '{$_GPC['date']}' AND cid = '{$value['id']}' ");
            $bjrate = pdo_fetch("SELECT bjrate,bzrrate FROM ".GetTableName('classify')." WHERE schoolid = '{$_GPC['schoolid']}' AND sid = '{$_GPC['bj_id']}' ");
          
            if($value['type'] == 1){
                if(!empty($ddlog)){
                    $bjInfoList[$key]['score'] = $ddlog['score'];
                }else{
                    $bjInfoList[$key]['score'] = 0;
                }
                $bjInfoList[$key]['remark'] = $ddlog['remark'];
                $bjInfoList[$key]['title'] = $value['title'];
                if($value['addition'] == 1){
                    $bjInfoList[$key]['typeName'] = '<span style="color: #A9D86E; font-size: 12px;">(加分项)</span>';
                }else{
                    $bjInfoList[$key]['typeName'] = '<span style="color: #FF6C60; font-size: 12px;">(减分项)</span>';
                }
            }else{
                if(!empty($ddlog)){
                    $teaInfoList[$key]['score'] = $ddlog['score'];
                }else{
                    $teaInfoList[$key]['score'] = 0;
                }
                $teaInfoList[$key]['remark'] = $ddlog['remark'];
                $teaInfoList[$key]['title'] = $value['title'];
                if($value['addition'] == 1){
                    $teaInfoList[$key]['typeName'] = '<span style="color: #A9D86E; font-size: 12px;">(加分项)</span>';
                }else{
                    $teaInfoList[$key]['typeName'] = '<span style="color: #FF6C60; font-size: 12px;">(减分项)</span>';
                }
            }
        }
        include $this->template($school['style3'].'/tddscorelooklist_bot');
        die;
    }
}else{
    session_destroy();
    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
    header("location:$stopurl");
}   
        
?>