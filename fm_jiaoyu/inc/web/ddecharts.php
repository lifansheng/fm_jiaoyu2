<?php
global $_GPC, $_W;
$weid              = $_W['uniacid'];
$action            = 'ddscorelog';
$this1             = 'no2';
$schoolid          = intval($_GPC['schoolid']);
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$logo              = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$operation         = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$tid_global = $_W['tid'];
mload()->model('kc');
$starttime = $_GPC['createtime']['start'] ? strtotime($_GPC['createtime']['start']) : strtotime('-30 day');
$endtime = $_GPC['createtime']['end'] ? strtotime($_GPC['createtime']['end']) + 86399 : TIMESTAMP;

if($operation == 'display'){
    $diffday = diffBetweenTwoDays($_GPC['createtime']['start'],$_GPC['createtime']['end']); //日期差
    if($tid_global == 'founder' || $tid_global == 'owner'){
        $bjlist = pdo_fetchall("SELECT sname as old_sname ,sid FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and type = 'theclass' ORDER BY ssort DESC ");
    }else{
        mload()->model('tea');
        $bjlist = GetAllClassInfoByTid($schoolid,0,$_W['schooltype'],$tid_global);
    }
 
    include $this->template('web/ddecharts');
}elseif($operation == 'GetData'){
    $starttime = $_GPC['starttime'] ? $_GPC['starttime'] : strtotime('-30 day');
    $endtime = $_GPC['endtime'] ? $_GPC['endtime'] : TIMESTAMP;
    $bjid = $_GPC['bjid'];
    $title = []; //标题
    $datetitle = []; //标题
    $data = []; //最终的数据
    $startdate = date("Y-m-d",$starttime); //默认开始日期
    $enddate = date("Y-m-d",$endtime); //默认结束日期
    $diffday = diffBetweenTwoDays($startdate,$enddate); //日期差
    $title = ['班级考核项总分','教师考核项总分','考核项目合计总分','班级考核平均分值'];
    $bjrate = pdo_fetch("SELECT bjrate,bzrrate FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' AND sid = '{$bjid}' ");
    $stutotal = pdo_fetchcolumn("SELECT count(id) FROM ".GetTableName('students')." WHERE schoolid = '{$schoolid}' AND bj_id = '{$bjid}' ");
    for ($i=0; $i <= $diffday ; $i++) { 
        $datetitle[$i] = date("m/d",strtotime($startdate)+86400*$i); //按日期的x轴标题
        $minstart = strtotime($startdate)+86400*$i; //当天开始
        $maxend = strtotime($startdate)+86400*$i+86399; //当天结束

        $condition = " AND dl.bjid = '{$bjid}' AND dl.date BETWEEN $minstart AND $maxend";
        $list = pdo_fetchall("SELECT dl.cid, SUM(dl.score) as num,dl.id,dc.* FROM ".GetTableName('ddscorelog')." as dl LEFT JOIN " . GetTableName('ddscorecategory'). " as dc ON dl.cid = dc.id WHERE dl.schoolid = '{$schoolid}' $condition GROUP BY dl.cid");

        $totalscore = [
            1=>0,
            2=>0,
        ];
        foreach ($list as $k => $v) {
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
        foreach ($title as $key => $value) {
            $data[$key]['name'] = $value;
            $data[$key]['type'] = 'line';
            $data[$key]['smooth'] = true;
            if($value == '班级考核项总分'){
                $score = $totalscore[1];
            }elseif($value == '教师考核项总分'){
                $score = $totalscore[2];
            }elseif($value == '考核项目合计总分'){
                $score = $totalscore[1]+$totalscore[2];
            }elseif($value == '班级考核平均分值'){
                $score = round($totalscore[1] / $stutotal,2);
                $data[$key]['yAxisIndex'] = 1;
                $data[$key]['lineStyle']['color'] = '#fb9960';
            }
            $data[$key]['data'][$i] = $score;
        }
    }
    $return_data = array(
        'return_data_a' => array(
            'title' => $title,
            'xAxis_data' => $datetitle,
            'series' => $data,
        )
    );
    // dd($return_data);
    die(json_encode($return_data));
}
 
?>