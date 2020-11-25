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
$id = $_GPC['id'];
$op = $_GPC['op'] ? $_GPC['op'] : 'display';
$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where id = :id ", array(':id' => $_SESSION['user']));
$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id", array(':weid' => $weid, ':id' => $schoolid));  
if(!empty($it['id'])){
    $student = pdo_fetch("SELECT * FROM " . GetTableName('students') . " where id = :id ", array(':id' => $it['sid']));
    $bj_id = $student['bj_id'];
    $start = date("Y-m-d",strtotime('-7 day'));
    $end   = date("Y-m-d",TIMESTAMP);
    $bjtotal = 0;
    $bjavg = 0;
    if($op == 'display'){
        $starttime = strtotime($start);
        $endtime = strtotime($end) + 86399;
        $condition .= " AND d.bjid = '{$bj_id}' ";
        $condition .= " AND d.date >= '{$starttime}' AND d.date <= '{$endtime}'";
        $list = pdo_fetchall("SELECT d.*, c.sname, t.tname FROM ".GetTableName('ddscorelog')." as d LEFT JOIN ".GetTableName('classify')." as c ON c.sid = d.bjid LEFT JOIN" . GetTableName('teachers') . " as t ON t.id = d.tid WHERE d.schoolid = '{$schoolid}' $condition GROUP BY d.date ORDER BY d.date DESC LIMIT 0,10");
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
        }

        //统计数据
        $tjlist = pdo_fetchall("SELECT d.date,d.bjid FROM ".GetTableName('ddscorelog')." as d LEFT JOIN ".GetTableName('classify')." as c ON c.sid = d.bjid LEFT JOIN" . GetTableName('teachers') . " as t ON t.id = d.tid WHERE d.schoolid = '{$schoolid}' $condition GROUP BY d.date ORDER BY d.date DESC");
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
        }

        include $this->template(''.$school['style2'].'/sddscorelooklist');
        die;
    }elseif($op == 'getscorelist'){
        $start = strtotime($_GPC['start']);
        $end = strtotime($_GPC['end']) + 86399;
        $condition .= " AND d.bjid = '{$bj_id}' ";
        $condition .= " AND d.date >= '{$start}' AND d.date <= '{$end}'";
        $list = pdo_fetchall("SELECT d.*, c.sname, t.tname FROM ".GetTableName('ddscorelog')." as d LEFT JOIN ".GetTableName('classify')." as c ON c.sid = d.bjid LEFT JOIN" . GetTableName('teachers') . " as t ON t.id = d.tid WHERE d.schoolid = '{$schoolid}' $condition GROUP BY d.date ORDER BY d.date DESC LIMIT 0,10");
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
        }
        include $this->template(''.$school['style2'].'/sddscorelooklist_bot');
        die;
    }elseif($op == 'scroll_more'){
        $scroll = true;
        $start = strtotime($_GPC['ExtraData']['start']);
        $end = strtotime($_GPC['ExtraData']['end']) + 86399;
        $condition .= " AND d.bjid = '{$bj_id}' ";
        $condition .= " AND d.date >= '{$start}' AND d.date <= '{$end}'";
        $limit_start = $_GPC['ExtraData']['time'] + 1;
        $list = pdo_fetchall("SELECT d.*, c.sname, t.tname FROM ".GetTableName('ddscorelog')." as d LEFT JOIN ".GetTableName('classify')." as c ON c.sid = d.bjid LEFT JOIN" . GetTableName('teachers') . " as t ON t.id = d.tid WHERE d.schoolid = '{$schoolid}' $condition GROUP BY d.date ORDER BY d.date DESC LIMIT {$limit_start},10");
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
            $list[$key]['location'] = $key + $limit_start;
        }
        include $this->template(''.$school['style2'].'/sddscorelooklist_bot');
        die;
    }elseif($op == 'getInfo'){
        $list = pdo_fetchAll("SELECT id,type,title,addition FROM " . GetTableName('ddscorecategory') .  " WHERE schoolid = :schoolid AND type = 1 ORDER BY addition,ssort DESC", array(':schoolid' => $_GPC['schoolid']));
        $bjrate = pdo_fetch("SELECT bjrate FROM ".GetTableName('classify')." WHERE schoolid = '{$_GPC['schoolid']}' AND sid = '{$bj_id}' ")['bjrate'];
        foreach ($list as $key => $value) {
            $score = pdo_fetch("SELECT score FROM ".GetTableName('ddscorelog')." WHERE schoolid = '{$schoolid}' AND bjid = '{$bj_id}' AND date = '{$_GPC['date']}' AND cid = '{$value['id']}' ")['score'];
            if(!empty($score)){
                $list[$key]['score'] = $score;
            }else{
                $list[$key]['score'] = 0;
            }
            if($value['addition'] == 1){
                $list[$key]['typeName'] = '<span style="color: #A9D86E; font-size: 12px;">(加分项)</span>';
            }else{
                $list[$key]['typeName'] = '<span style="color: #FF6C60; font-size: 12px;">(减分项)</span>';
            }
        }
        $result['data'] = $list;
        $result['bjrate'] = $bjrate;
        die(json_encode($result));
    }
}else{
    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
    header("location:$stopurl");
}        
?>