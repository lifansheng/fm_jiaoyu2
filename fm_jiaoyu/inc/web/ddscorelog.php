<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_GPC, $_W;

$weid              = $_W['uniacid'];
$action            = 'ddscorelog';
$this1             = 'no2';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
$logo              = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$tid_global = $_W['tid'];
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';


if($tid_global !='founder' && $tid_global != 'owner'){
    $fz_id =  pdo_fetch("SELECT fz_id FROM " . GetTableName ('teachers') . " where weid = :weid And schoolid = :schoolid And id =:id ", array(':weid' => $weid,':schoolid' => $schoolid,':id'=>$tid_global))['fz_id'];
	$qxarr = GetQxByFz($fz_id,1,$schoolid);
	$toPage = 'ddscorelog';
	if( !(strstr($qxarr,'1005001'))){
		$toPage = 'ddcheckscore';
	}
	if(!(strstr($qxarr,'1005006')) && $toPage == 'ddcheckscore'){
		$toPage = 'ddscorecategory';
	}
	if(!(strstr($qxarr,'1005003')) && $toPage == 'ddscorecategory'){
		$toPage = 'NoAccess';
    }
	if($toPage != 'NoAccess' && $toPage != 'ddscorelog' ){
		$stopurl = $_W['siteroot'] .'web/'.$this->createWebUrl($toPage, array('schoolid' => $schoolid,'op'=>'display'));
		header("location:$stopurl");
	}elseif($toPage == 'NoAccess' ){
		$this->imessage('非法访问，您无权操作该页面','','error');	
	}
}
$type = $_GPC['type'] ? $_GPC['type'] : 1;

if($operation == 'display'){
    if($tid_global == 'founder' || $tid_global == 'owner'){
        $bjlist = pdo_fetchall("SELECT sname as old_sname ,sid FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and type = 'theclass' ORDER BY ssort DESC ");
    }else{
        mload()->model('tea');
        $bjlist = GetAllClassInfoByTid($schoolid,0,$_W['schooltype'],$tid_global);
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize  = 20;
    $condition = '';
    if(!empty($_GPC['createtime']['start'])){
		$starttime = strtotime($_GPC['createtime']['start']);
		$endtime   = strtotime($_GPC['createtime']['end']) + 86399;
		$condition .= " AND d.date <= '{$endtime}' AND d.date >= '{$starttime}'";
    }else{
        $starttime = strtotime('-30 day');
        $endtime   = TIMESTAMP;
		$condition .= "";
    }
    if($type == 1){ //单日
        if($_GPC['bjid']){
            $condition .= " and d.bjid = '{$_GPC['bjid']}' ";
        }
        $list = pdo_fetchall("SELECT d.*, c.sname, t.tname, 1 as hasbj FROM ".GetTableName('ddscorelog')." as d LEFT JOIN ".GetTableName('classify')." as c ON c.sid = d.bjid LEFT JOIN" . GetTableName('teachers') . " as t ON t.id = d.tid WHERE d.schoolid = '{$schoolid}' {$condition} GROUP BY d.date,d.bjid ORDER BY ssort DESC  LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
        $list2 = pdo_fetchAll("SELECT d.*, c.sname, t.tname, 1 as hasbj FROM ".GetTableName('ddscorelog')." as d LEFT JOIN ".GetTableName('classify')." as c ON c.sid = d.bjid LEFT JOIN" . GetTableName('teachers') . " as t ON t.id = d.tid WHERE d.schoolid = '{$schoolid}' {$condition} GROUP BY d.date,d.bjid ORDER BY ssort DESC");
        $total = count($list2);
    }else{ //统计
        $list = pdo_fetchall("SELECT d.*, c.sname, t.tname, 0 as hasbj FROM ".GetTableName('ddscorelog')." as d LEFT JOIN ".GetTableName('classify')." as c ON c.sid = d.bjid LEFT JOIN" . GetTableName('teachers') . " as t ON t.id = d.tid WHERE d.schoolid = '{$schoolid}' {$condition} GROUP BY d.bjid ORDER BY ssort DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
        $list2 = pdo_fetchall("SELECT d.*, c.sname, t.tname, 0 as hasbj FROM ".GetTableName('ddscorelog')." as d LEFT JOIN ".GetTableName('classify')." as c ON c.sid = d.bjid LEFT JOIN" . GetTableName('teachers') . " as t ON t.id = d.tid WHERE d.schoolid = '{$schoolid}' {$condition} GROUP BY d.bjid ORDER BY ssort DESC ");
        $total = count($list2);
    }
    
    $bjtotal = 0;
    $bjavg = 0;
    $teatotal = 0;
    $scoretotal = 0;
    foreach ($list as $key => $value) {
        //获取当前班级当前日期的考核记录
        if($value['hasbj'] == 1){
            $log = pdo_fetchall("SELECT dl.cid,dl.score as num,dl.id,dc.* FROM ".GetTableName('ddscorelog')." as dl LEFT JOIN " . GetTableName('ddscorecategory'). " as dc ON dl.cid = dc.id WHERE dl.bjid = '{$value['bjid']}' AND dl.date = '{$value['date']}'");
        }else{
            $log = pdo_fetchall("SELECT dl.cid, SUM(dl.score) as num,dl.id,dc.* FROM ".GetTableName('ddscorelog')." as dl LEFT JOIN " . GetTableName('ddscorecategory'). " as dc ON dl.cid = dc.id WHERE dl.bjid = '{$value['bjid']}' AND dl.date BETWEEN '{$starttime}' AND '{$endtime}' GROUP BY dl.cid");
        }
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
        $bjtotal += $totalscore[1];
        $bjavg += round($totalscore[1] / $stutotal,2);
        $teatotal +=  $totalscore[2];
        $scoretotal += $totalscore[1]+$totalscore[2];
    }
    if($_GPC['out'] == 'out'){
        foreach ($list2 as $key => $value) {
            //获取当前班级当前日期的考核记录
            if($value['hasbj'] == 1){
                $log = pdo_fetchall("SELECT dl.cid,dl.score as num,dl.id,dc.* FROM ".GetTableName('ddscorelog')." as dl LEFT JOIN " . GetTableName('ddscorecategory'). " as dc ON dl.cid = dc.id WHERE dl.bjid = '{$value['bjid']}' AND dl.date = '{$value['date']}'");
            }else{
                $log = pdo_fetchall("SELECT dl.cid, SUM(dl.score) as num,dl.id,dc.* FROM ".GetTableName('ddscorelog')." as dl LEFT JOIN " . GetTableName('ddscorecategory'). " as dc ON dl.cid = dc.id WHERE dl.bjid = '{$value['bjid']}' AND dl.date BETWEEN '{$starttime}' AND '{$endtime}' GROUP BY dl.cid");
            }
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
            $list2[$key]['tname'] = $value['tname'] ? $value['tname'] : '总管理';
            $list2[$key]['bjtotal'] = $totalscore[1];
            $list2[$key]['bjavg'] = round($totalscore[1] / $stutotal,2);
            $list2[$key]['teatotal'] = $totalscore[2];
            $list2[$key]['scoretotal'] = $totalscore[1]+$totalscore[2];
            $bjtotal += $totalscore[1];
            $bjavg += round($totalscore[1] / $stutotal,2);
            $teatotal +=  $totalscore[2];
            $scoretotal += $totalscore[1]+$totalscore[2];
        }
        $title = '考核日期';
        if($type == 1 && $_GPC['bjid']){
            $arr = [
                0=>array(
                    'id'=> '',
                    'bjname'=> $list[0]['sname'],
                    'tname'=> '统计',
                    'date'=> date("Y/m/d",$starttime).'到'.date("Y/m/d",$endtime),
                    'bjtotal'=> $bjtotal.'分',
                    'teatotal'=> $teatotal.'分',
                    'scoretotal'=> $scoretotal.'分',
                    'bjavg'=> $bjavg.'分',
                )
            ];
            $ii = 1;
            $title2 = '单日统计-'.$list[0]['sname'].date("Y/m/d",$starttime).'-'.date("Y/m/d",$endtime);
        }else{
            $arr = [];
            $ii = 0;
            if($type == 1){
                $title2 = '单日'.date("Y/m/d",$starttime).'-'.date("Y/m/d",$endtime);
            }else{
                $title = '日期范围';
                $title2 = '统计'.date("Y/m/d",$starttime).'-'.date("Y/m/d",$endtime);
            }
        }
        foreach($list2 as $index => $row){
			$arr[$ii]['id'] = $row['id'];
			$arr[$ii]['bjname']  = $row['sname'];
			$arr[$ii]['tname']  = $type == 1 ? $row['tname'] : '';
            $arr[$ii]['date']  = $type == 1 ? date("Y/m/d",$row['date']) : date("Y/m/d",$starttime).'到'.date("Y/m/d",$endtime);
			$arr[$ii]['bjtotal'] = $row['bjtotal'].'分';
			$arr[$ii]['teatotal'] = $row['teatotal'].'分';
			$arr[$ii]['scoretotal'] = $row['scoretotal'].'分';
			$arr[$ii]['bjavg'] = $row['bjavg'].'分';
			$ii++;
        }
        $this->exportexcel($arr, array('ID','班级','考核老师',$title,'班级考核项总分','班主任考核项总分','考核项目合计总分','班级考核平均分值'), $title2);
		exit();
    }
	$pager = pagination($total, $pindex, $psize);
}elseif($operation == 'delete'){
    pdo_delete(GetTableName('ddscorelog',false), array('bjid' => $_GPC['bjid'],'date'=>$_GPC['date']));
    $this->imessage('删除成功！', referer(), 'success');
}elseif($operation == 'deleteall'){
    $rowcount    = 0;
    $notrowcount = 0;
    foreach($_GPC['idArr'] as $k => $id){
        $id = intval($id);
        if(!empty($id)){
            $item = pdo_fetch("SELECT * FROM " . GetTableName('ddscorelog') . " WHERE id = :id", array(':id' => $id));
            if(empty($item)){
                $notrowcount++;
                continue;
            }
            pdo_delete(GetTableName('ddscorelog',false), array('bjid' => $item['bjid'], 'date' => $item['date']));
            $rowcount++;
        }
    }
    $message = "操作成功！共删除{$rowcount}条数据,{$notrowcount}条数据不能删除!";
    $data ['result'] = true;
    $data ['msg'] = $message;
    die (json_encode($data));
}elseif($operation == 'getInfo'){
    if(is_numeric($tid_global)){
        $condition .= " AND FIND_IN_SET($tid_global,dc.tid) ";
    }
    $item = pdo_fetchAll("SELECT ds.score,ds.remark,dc.title,dc.addition,t.tname,dc.type FROM " . GetTableName('ddscorelog') . " as ds RIGHT JOIN " . GetTableName('ddscorecategory') .  " as dc ON dc.id = ds.cid LEFT JOIN " . GetTableName('teachers') .  " as t ON ds.tid = t.id WHERE ds.bjid = :bjid AND ds.date = :date $condition ORDER BY dc.ssort DESC", array(':bjid' => $_GPC['bjid'],':date'=>$_GPC['date']));
    include $this->template('web/ddscorelog_bot');
    exit;
}
include $this->template('web/ddscorelog');
?>