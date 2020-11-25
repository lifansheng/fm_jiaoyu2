<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_GPC, $_W;

$weid = $_W['uniacid'];
$this1 = 'no1';
$action = 'start';
if($_W['os'] == 'mobile' && (!empty($_GPC['i']) || !empty($_SERVER['QUERY_STRING']))) {
	//$this->imessage('抱歉，请在电脑端打开本后台！', referer(), 'error');
}
if($_GPC['from'] == 'depend'){
	$this->imessage('登陆中!', $this->createWebUrl('usercenter', array('id' => $myadmin['schoolid'], 'schoolid' => $myadmin['schoolid'])), 'sucess');
}
$schoolid = $_GPC['schoolid'];
$tid_global = $_W['tid'];
if (!(IsHasQx($tid_global,1005401,1,$schoolid))){
	$this->imessage('个人中心!', $this->createWebUrl('usercenter', array('id' => $myadmin['schoolid'], 'schoolid' => $myadmin['schoolid'])), 'sucess');
}
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'],$action);
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$logo = pdo_fetch("SELECT logo,title,is_cost,tpic,spic,issale FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$schoolset = pdo_fetch("SELECT is_mc FROM " . tablename($this->table_schoolset) . " WHERE schoolid = '{$schoolid}'");
$schooltype  = $_W['schooltype'];
$schooltypes = unitchecksctype();
if($operation == 'GetMiddleData'){
	$data = [];
	if(!empty($_GPC['start']) || !empty($_GPC['end'])) {
        $starttime = strtotime($_GPC['start']);
        $endtime = strtotime($_GPC['end']) + 86399;
        $condition1 = " AND createtime > '{$starttime}' AND createtime < '{$endtime}'";
        $condition7 = " AND jiontime > '{$starttime}' AND jiontime < '{$endtime}'";
        $condition2 = " AND paytime > '{$starttime}' AND paytime < '{$endtime}'";
    } else {
	   	$starttime = 0;
        $endtime = TIMESTAMP;
	}
	$notime = time();
	$sql_ybjs = "SELECT COUNT(id) FROM " .GetTableName('user'). " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND sid = 0 $condition1";
	$sql_ybxs = "SELECT COUNT(id) FROM " .GetTableName('user'). " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND tid = 0 $condition1";
	if ($_W['schooltype']){
		$sql_baomzj     = "SELECT COUNT(id) FROM " .GetTableName('order') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and type = 1 and status = 2 $condition2 ";
		$sql_checklogzj = "SELECT COUNT(id) FROM " .GetTableName('kcsign') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and status = 2 and tid = 0 and sid != 0 and kcid != 0  $condition1";
		$sql_xszjGuoqi       = "SELECT count(id) FROM " .GetTableName("tcourse"). " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND end < $notime ";
		$sql_xszj  = "SELECT count(id) FROM " .GetTableName('tcourse'). " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND end > $notime ";
	}else{
		$sql_baomzj     = "SELECT COUNT(id) FROM " .GetTableName('signup') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition1";
		$sql_checklogzj = 'SELECT COUNT(id) FROM ' .GetTableName('checklog') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition1";
		$sql_xszj       = "SELECT count(st.id) FROM ".GetTableName('students')." as st , ".GetTableName('classify'). " as cl WHERE  st.bj_id = cl.sid AND st.weid = '{$weid}' AND st.schoolid = '{$schoolid}' AND cl.type = 'theclass' AND cl.is_over != 2";
		$sql_xszjGuoqi  = "SELECT count(st.id) FROM ".GetTableName('students')." as st , ".GetTableName('classify')." as cl WHERE  st.bj_id = cl.sid AND st.weid = '{$weid}' AND st.schoolid = '{$schoolid}' AND cl.type = 'theclass' AND cl.is_over = 2";
	}
	$sql_bjqzj = "SELECT COUNT(id) FROM " . GetTableName('bjq') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition1";
	$sql_xczj = "SELECT COUNT(id) FROM " . GetTableName('media') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition1";
	$sql_jszj = "SELECT COUNT(id) FROM " . GetTableName('teachers') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition7";
	
	if($logo['is_cost'] == 1 || $_W['isfounder'] || $_W['role'] == 'owner' || $_W['role'] == 'vice_founder'){
		$sql_cose1 = ", ( SELECT SUM(cose) FROM " . GetTableName('order') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND status = 2 $condition2 )  as cose1  ";
	}
	$Dosql = pdo_fetch("SELECT ({$sql_ybjs}) as ybjs , ({$sql_ybxs}) as ybxs ,({$sql_baomzj}) as baomzj ,({$sql_checklogzj}) as checklogzj ,({$sql_ybjs}) as ybjs ,({$sql_xszj}) as xszj ,({$sql_xszjGuoqi}) as xszjGuoqi ,({$sql_bjqzj}) as bjqzj ,({$sql_xczj}) as xczj ,({$sql_jszj}) as jszj {$sql_cose1} ");
	$result['status'] = 1;
	$result['data'] = $Dosql;
	die(json_encode($result));
} 
 
if($operation == 'GetKcAnalysis'){

	$start = mktime(0,0,0,date("m"),date("d"),date("Y"));
    $end = $start + 86399;
	if(!empty($_GPC['start']) && $_GPC['start'] != '1970-01-01') {
		$starttime1 = strtotime($_GPC['start']);
		$endtime1 = strtotime($_GPC['end']) + 86399;
		$day = timediff($starttime1,$endtime1);
		$day_num =  $day['day']+1;
		$condition9 .= " AND createtime > '{$starttime1}' AND createtime < '{$endtime1}'";
		$condition8 .= " AND ( (startime1 < '{$starttime1}' AND endtime1 > '{$endtime1}') OR ( startime1 > '{$starttime1}' AND startime1 < '{$endtime1}') OR ( endtime1 > '{$starttime1}' AND endtime1 < '{$endtime1}'))";
	} else {
		$condition9 .= " AND createtime > '{$start}' AND createtime < '{$end}'";
		$condition8 .= " AND ( (startime1 < '{$start}' AND endtime1 > '{$end}') OR ( startime1 > '{$start}' AND startime1 < '{$end}') OR ( endtime1 > '{$start}' AND endtime1 < '{$end}'))";
	}

 
	//培训机构，按课程类型统计
	if(GetSchoolType($schoolid,$weid)){
		//获取总共有多少种课程类型
		// $njchecklog = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND ( type = 'semester' Or type = 'kcclass' ) ORDER BY ssort DESC ,sid DESC ");
		$njchecklog = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND type = 'semester' ORDER BY ssort DESC ,sid DESC ");
		// array_unshift($njchecklog,array('sid'=>0,'sname'=>'默认类型'));
		if($day_num){
			$days = array();
			$daykey = array();
			for($i = 0; $i < $day_num; $i++){
				$keys = date('Y-m-d', $starttime + 86400 * $i);
				$days[$keys] = 0;
				$daykey[$keys] = 0;
			}
			//计算每一种课程类型的统计结果
			foreach($njchecklog as $key =>$row){
				$njchecklog[$key]['NeedSignNum']  = 0;
				$njchecklog[$key]['DoSignNum'] = 0;
				$njchecklog[$key]['QingJiaNum'] = 0;
				$njchecklog[$key]['ksnum'] = 0;
				$bjqksm = 0;
				foreach($daykey as $key_d=>$value_d){
					$start_d = strtotime($key_d);
					$end_d = $start_d + 86399;
					//var_dump($key_d);
					//var_dump($start_d);
					$allthisbj = pdo_fetchall("SELECT id,name,OldOrNew FROM " . tablename($this->table_tcourse) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND Ctype = '{$row['sid']}' AND ( (start <= '{$start_d}' AND end >= '{$end_d}') OR ( start >= '{$start_d}' AND start <= '{$end_d}') OR ( end >= '{$start_d}' AND end <= '{$end_d}'))  ORDER BY ssort DESC ,id DESC ");
					foreach($allthisbj as $index => $v){
						$stuNum = pdo_fetchcolumn("SELECT COUNT(distinct sid ) FROM " . tablename($this->table_order) . " WHERE schoolid = '{$schoolid}' and weid='{$weid}' and kcid = '{$v['id']}' and type =1 and status = 2 and paytime < '{$end_d}' ");
						if($v['OldOrNew'] == 0){
							$ksSum = pdo_fetchall("SELECT  count(id)  FROM " . tablename($this->table_kcbiao) . " WHERE schoolid = '{$schoolid}' and weid='{$weid}' and kcid = '{$v['id']}' and date >= '{$start_d}' and date <= '{$end_d}' ");
							$njchecklog[$key]['NeedSignNum'] += $stuNum * count($ksSum);
							$njchecklog[$key]['ksnum'] += count($ksSum);
							foreach($ksSum as $key_k=>$value_k){
								$stuSign = pdo_fetchcolumn("SELECT  COUNT(distinct sid )  FROM " . tablename($this->table_kcsign) . " WHERE schoolid = '{$schoolid}' and tid = 0 and weid='{$weid}' and kcid='{$v['id']}' and  ksid = '{$value_k['id']}' and status = 2 ");
								$njchecklog[$key]['DoSignNum'] += $stuSign;
								$stuQingJia = pdo_fetchcolumn("SELECT  COUNT(distinct sid )  FROM " . tablename($this->table_kcsign) . " WHERE schoolid = '{$schoolid}' and weid='{$weid}' and kcid='{$v['id']}' and  ksid = '{$value_k['id']}' and status = 3 ");
								$njchecklog[$key]['QingJiaNum'] += $stuQingJia;
							}
						}elseif($v['OldOrNew'] == 1){
							$ksSum = pdo_fetchcolumn("SELECT  count(1)  FROM " . tablename($this->table_kcsign) . " WHERE schoolid = '{$schoolid}' and weid='{$weid}' and kcid = '{$v['id']}' and sid = 0 and tid != 0 and createtime >= '{$start_d}' and createtime <= '{$end_d}' and status = 2 ");
							$njchecklog[$key]['NeedSignNum'] += $stuNum * $ksSum;
							$njchecklog[$key]['ksnum'] += $ksSum;
							$stuSign = pdo_fetchcolumn("SELECT count(a.id) FROM " . tablename($this->table_kcsign) . " AS a WHERE (SELECT COUNT(*) FROM " . tablename($this->table_kcsign) . " AS b  WHERE b.sid = a.sid and b.createtime >= '{$start_d}' and b.createtime <= '{$end_d}' and b.status = 2 and b.schoolid = '{$schoolid}' and b.weid='{$weid}' and b.kcid = '{$v['id']}' ) <= '{$ksSum}' and a.createtime >= '{$start_d}' and a.createtime <= '{$end_d}' and a.status = 2 and a.schoolid = '{$schoolid}' and a.weid='{$weid}' and a.kcid = '{$v['id']}' ORDER BY a.sid ASC  ");
							//var_dump($stuSign);
							$njchecklog[$key]['DoSignNum'] += $stuSign;
							$stuQingJia = pdo_fetchcolumn("SELECT  count(a.sid) FROM " . tablename($this->table_kcsign) . " AS a WHERE (SELECT COUNT(*) FROM " . tablename($this->table_kcsign) . " AS b  WHERE b.sid = a.sid and b.createtime >= '{$start_d}' and b.createtime <= '{$end_d}' and status = 3 and b.schoolid = '{$schoolid}' and b.weid='{$weid}' and b.kcid = '{$v['id']}' ) <= '{$ksSum}' and a.createtime >= '{$start_d}' and a.createtime <= '{$end_d}' and a.status = 3 and a.schoolid = '{$schoolid}' and a.weid='{$weid}' and a.kcid = '{$v['id']}'  ORDER BY a.sid ASC  ");
							$njchecklog[$key]['QingJiaNum'] += $stuQingJia;
						}
					}
					//缺勤人数
					$njchecklog[$key]['NotSignNum'] = ($njchecklog[$key]['NeedSignNum'] - $njchecklog[$key]['DoSignNum'] - $njchecklog[$key]['QingJiaNum'])>0?$njchecklog[$key]['NeedSignNum'] - $njchecklog[$key]['DoSignNum'] - $njchecklog[$key]['QingJiaNum']:0 ;
				}
			}
		}else{
			foreach($njchecklog as $key =>$row){

				$chlid = pdo_fetchAll("SELECT sid FROM " . GetTableName('classify') . " WHERE schoolid = '{$schoolid}' And parentid = '{$row['sid']}' AND type = 'kcclass' ");
				$chlidstr = arrayToString($chlid);
				$condition = " And (xq_id = '{$row['sid']}' OR FIND_IN_SET(xq_id,'{$chlidstr}'))";

				// $allthisbj = pdo_fetchall("SELECT id,name,OldOrNew FROM " . tablename($this->table_tcourse) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND Ctype = '{$row['sid']}' AND ( (start <= '{$start}' AND end >= '{$end}') OR ( start >= '{$start}' AND start <= '{$end}') OR ( end >= '{$start}' AND end <= '{$end}'))  ORDER BY ssort DESC ,id DESC ");
				$allthisbj = pdo_fetchall("SELECT id,name,OldOrNew FROM " . tablename($this->table_tcourse) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND kc_type != 1 AND ( (start <= '{$start}' AND end >= '{$end}') OR ( start >= '{$start}' AND start <= '{$end}') OR ( end >= '{$start}' AND end <= '{$end}')) $condition ORDER BY ssort DESC ,id DESC ");
				$njchecklog[$key]['NeedSignNum']  = 0;
				$njchecklog[$key]['DoSignNum'] = 0;
				$njchecklog[$key]['QingJiaNum'] = 0;
				$njchecklog[$key]['ksnum'] = 0;
				$bjqksm = 0;
				foreach($allthisbj as $index => $v){
					$stuNum = pdo_fetchcolumn("SELECT COUNT(distinct sid ) FROM " . tablename($this->table_order) . " WHERE schoolid = '{$schoolid}' and weid='{$weid}' and kcid = '{$v['id']}' and type =1 and status = 2 ");
					if($v['OldOrNew'] == 0){
						$ksSum = pdo_fetchall("SELECT  id  FROM " . tablename($this->table_kcbiao) . " WHERE schoolid = '{$schoolid}' and weid='{$weid}' and kcid = '{$v['id']}' and date >= '{$start}' and date <= '{$end}' ");
						$njchecklog[$key]['NeedSignNum'] += $stuNum * count($ksSum);
						$njchecklog[$key]['ksnum'] += count($ksSum);
						foreach($ksSum as $key_k=>$value_k){
							$stuSign = pdo_fetchcolumn("SELECT  COUNT(distinct sid )  FROM " . tablename($this->table_kcsign) . " WHERE schoolid = '{$schoolid}' and weid='{$weid}' and kcid='{$v['id']}' and  ksid = '{$value_k['id']}' and status = 2 ");
							$njchecklog[$key]['DoSignNum'] += $stuSign;
							$stuQingJia = pdo_fetchcolumn("SELECT  COUNT(distinct sid )  FROM " . tablename($this->table_kcsign) . " WHERE schoolid = '{$schoolid}' and weid='{$weid}' and kcid='{$v['id']}' and  ksid = '{$value_k['id']}'  and status = 3 ");
							$njchecklog[$key]['QingJiaNum'] += $stuQingJia;
						}
					}elseif($v['OldOrNew'] == 1){
						$ksSum = pdo_fetchcolumn("SELECT  count(1)  FROM " . tablename($this->table_kcsign) . " WHERE schoolid = '{$schoolid}' and weid='{$weid}' and kcid = '{$v['id']}' and sid = 0 and tid != 0 and createtime >= '{$start}' and createtime <= '{$end}' and status = 2 ");
						$njchecklog[$key]['NeedSignNum'] += $stuNum * $ksSum;
						$njchecklog[$key]['ksnum'] += $ksSum;
					 	$stuSign = pdo_fetchcolumn("SELECT count(a.id) FROM " . tablename($this->table_kcsign) . " AS a WHERE (SELECT COUNT(*) FROM " . tablename($this->table_kcsign) . " AS b  WHERE b.sid = a.sid and b.createtime >= '{$start}' and b.createtime <= '{$end}' and b.status = 2 and b.schoolid = '{$schoolid}' and b.weid='{$weid}' and b.kcid = '{$v['id']}' ) <= '{$ksSum}' and a.createtime >= '{$start}' and a.createtime <= '{$end}' and a.status = 2 and a.schoolid = '{$schoolid}' and a.weid='{$weid}' and a.kcid = '{$v['id']}' ORDER BY a.sid ASC  ");
						//var_dump($stuSign);
						$njchecklog[$key]['DoSignNum'] += $stuSign;
						$stuQingJia = pdo_fetchcolumn("SELECT count(a.sid) FROM " . tablename($this->table_kcsign) . " AS a WHERE (SELECT COUNT(*) FROM " . tablename($this->table_kcsign) . " AS b  WHERE b.sid = a.sid and b.createtime >= '{$start}' and b.createtime <= '{$end}' and status = 3 and b.schoolid = '{$schoolid}' and b.weid='{$weid}' and b.kcid = '{$v['id']}' ) <= '{$ksSum}' and a.createtime >= '{$start}' and a.createtime <= '{$end}' and a.status = 3 and a.schoolid = '{$schoolid}' and a.weid='{$weid}' and a.kcid = '{$v['id']}'  ORDER BY a.sid ASC  ");
						$njchecklog[$key]['QingJiaNum'] += $stuQingJia;
					}
				}
				//缺勤人数
				$njchecklog[$key]['NotSignNum'] = ($njchecklog[$key]['NeedSignNum'] - $njchecklog[$key]['DoSignNum'] - $njchecklog[$key]['QingJiaNum'] )>0?$njchecklog[$key]['NeedSignNum'] - $njchecklog[$key]['DoSignNum'] - $njchecklog[$key]['QingJiaNum'] :0;
			}
		}
	}else{
		/**各年级出勤情况**/
		$njchecklog = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND ( type = 'semester' Or type = 'kcclass') AND is_over = 1 ORDER BY ssort DESC ,sid DESC ");
		if($day_num){
			$days = array();
			$daykey = array();
			for($i = 0; $i < $day_num; $i++){
				$keys = date('Y-m-d', $starttime + 86400 * $i);
				$days[$keys] = 0;
				$daykey[$keys] = 0;
			}
			foreach($njchecklog as $key =>$row){
				$njzrs = pdo_fetchcolumn("SELECT COUNT(id) FROM " . tablename($this->table_students) . " WHERE schoolid = :schoolid And xq_id = :xq_id", array(':schoolid' => $schoolid, ':xq_id' => $row['sid']));
				$njchecklog[$key]['njzrs'] = $njzrs;
				$njchecklog[$key]['njcqzs'] = 0;
				$njchecklog[$key]['njqjrs'] = 0;
				$allthisbj = pdo_fetchall("SELECT sid FROM " . tablename($this->table_classify) . " WHERE parentid = '{$row['sid']}' AND is_over = 1 ");
				$bjqksm = 0;
				foreach($allthisbj as $index => $v){
					$allbjqksm = pdo_fetchall("SELECT DISTINCT sid,createtime FROM " . tablename($this->table_checklog) . " WHERE bj_id = '{$v['sid']}' AND leixing = 1 AND isconfirm = 1  $condition9 ");
					foreach($allbjqksm as $da) {
						$k = date('Y-m-d', $da['createtime']);
						if(in_array($k, array_keys($days))) {
							if(!in_array($da['sid'], $daykey[$k][$key][$index])) {
								$daykey[$k][$key][$index] = $da['sid'];
								$njchecklog[$key]['njcqzs']++;
							}
						}
					}
					$bjqjsm = pdo_fetchcolumn('SELECT COUNT(id) FROM ' . tablename($this->table_leave) . " WHERE bj_id = '{$v['sid']}' And isliuyan = 0 $condition8 ");
					$njchecklog[$key]['njqjrs'] =  $njchecklog[$key]['njqjrs'] + $bjqjsm;
				}
				$njchecklog[$key]['qqzrs'] = $njzrs*$day_num - $njchecklog[$key]['njcqzs'];
			}
		}else{
			foreach($njchecklog as $key =>$row){
				$njzrs = pdo_fetchcolumn("SELECT COUNT(id) FROM " . tablename($this->table_students) . " WHERE schoolid = :schoolid And xq_id = :xq_id", array(':schoolid' => $schoolid, ':xq_id' => $row['sid']));
				$njchecklog[$key]['njzrs'] = $njzrs;
				$njchecklog[$key]['njcqzs'] = 0;
				$njchecklog[$key]['njqjrs'] = 0;
				$allthisbj = pdo_fetchall("SELECT sid FROM " . tablename($this->table_classify) . " WHERE parentid = '{$row['sid']}' AND is_over = 1 ");
				//var_dump($allthisbj);
				foreach($allthisbj as $index => $v){
					$bjqksm = pdo_fetchcolumn("SELECT COUNT(distinct sid) FROM " . tablename($this->table_checklog) . " WHERE bj_id = '{$v['sid']}' AND leixing = 1 AND isconfirm = 1 and sc_ap = 0 and sid != 0  $condition9 ");
					$bjqjsm = pdo_fetchcolumn('SELECT COUNT(id) FROM ' . tablename($this->table_leave) . " WHERE bj_id = '{$v['sid']}' And isliuyan = 0 $condition8 ");
					$njchecklog[$key]['njcqzs'] =  $njchecklog[$key]['njcqzs'] + $bjqksm;
					$njchecklog[$key]['njqjrs'] =  $njchecklog[$key]['njqjrs'] + $bjqjsm;
				}
				$njchecklog[$key]['qqzrs'] = $njzrs - $njchecklog[$key]['njcqzs'] - $njchecklog[$key]['njqjrs'];
			}
		}
	}
	include $this->template ( 'web/start_bot' );

}

if ($operation == 'display') {

	if(keep_MC() && $schoolset['is_mc'] == 1){
		$nowday = strtotime(date("Y-m-d",time()));
		//获取所有年级
		$AllNj = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND type = 'semester' AND is_over = 1 ORDER BY ssort DESC ,sid DESC ");
		// 获取当前年级下的所有班级
		$nowbj = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND parentid = '{$AllNj[0]['sid']}' ORDER BY ssort DESC");
		if(!empty($nowbj)){
			foreach ($nowbj as $key => $value) {
				//查询当前班级所有学生人数
				$studentsum = pdo_fetchcolumn("SELECT count(*) FROM ".GetTableName('students')." WHERE bj_id = '{$value['sid']}' ");
				//已检测的学生人数
				$mcsum = pdo_fetchcolumn("SELECT count(*) FROM ".GetTableName('morningcheck')." WHERE bj_id = '{$value['sid']}' AND createdate = '{$nowday}'");
				//未检测是学生人数
				$nomcsum = intval($studentsum) - intval($mcsum);
				$nowbj[$key]['mcsum'] = $mcsum;
				$nowbj[$key]['nomcsum'] = $nomcsum;
			}
		}
	}
	
	$nowdatatype = SchoolTypeFromLocal($schoolid,$weid);
	$rlsrll = $_W['siteroot'] . 'web/index.php?c=site&a=entry&schoolid=' . $schoolid . '&do=indexajax&op=changeschooltype&m=fm_jiaoyu';
    if(!empty($_GPC['addtime'])) {
        $starttime = strtotime($_GPC['addtime']['start']);
        $endtime = strtotime($_GPC['addtime']['end']) + 86399;
        $condition1 .= " AND createtime > '{$starttime}' AND createtime < '{$endtime}'";
        $condition5 .= " AND createtime > '{$endtime}' AND createtime < '{$_GPC['addtime']['end']}'";
        $condition6 .= " AND createdate > '{$starttime}' AND createdate < '{$endtime}'";
        $condition7 .= " AND jiontime > '{$starttime}' AND jiontime < '{$endtime}'";
        $condition2 .= " AND paytime > '{$starttime}' AND paytime < '{$endtime}'";
    } else {
	   // $starttime = strtotime('-180 day');
	   $starttime = 0;
        $endtime = TIMESTAMP;
    }

    $start = mktime(0,0,0,date("m"),date("d"),date("Y"));
    $end = $start + 86399;
    $condition3 = " AND createtime > '{$start}' AND createtime < '{$end}'";
    $condition4 = " AND paytime > '{$start}' AND paytime < '{$end}'";
    $params[':start'] = $starttime;
	$params[':end'] = $endtime;
	
	if($_W['schooltype']){ //培训模式
		 
		$sql_todaykc   = "SELECT count(id) FROM " .GetTableName('kcbiao')." WHERE weid = '{$weid}' AND schoolid = '{$schoolid}'  AND date > '{$start}' AND date < '{$end}' "; 
		$sql_allkc     = "SELECT COUNT(id) FROM " .GetTableName('kcbiao'). " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' ";                                                         
		$sql_todaysign = "SELECT COUNT(id) FROM " .GetTableName('kcsign'). " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition3 ";                                           
		$sql_allsign   = "SELECT COUNT(id) FROM " .GetTableName('kcsign'). " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}'  ";
		$sql_todaybuy  = "SELECT COUNT(distinct sid) FROM " .GetTableName('kcsign') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND type =1 AND status = 2 $condition3 ";
		$sql_allbuy    = "SELECT COUNT(id) FROM " .GetTableName('kcsign'). " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND type =1 AND status = 2 ";
		$sql_todaystu  = "SELECT COUNT(id) FROM " .GetTableName('students')." WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND createdate > '{$start}' AND createdate < '{$end}'";
		$sql_allstu    = "SELECT COUNT(id) FROM " .GetTableName('students')." WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' ";

		$DoSQL = pdo_fetch(" SELECT ({$sql_todaykc}) as todaykc , ({$sql_allkc}) as allkc , ({$sql_todaysign}) as todaysign , ({$sql_allsign}) as allsign ,  ({$sql_todaybuy}) as todaybuy , ({$sql_allbuy}) as allbuy , ({$sql_todaystu}) as todaystu , ({$sql_allstu}) as allstu  ");
		$todayKc = $DoSQL['todaykc'] ;
		$allKc = $DoSQL['allkc'];
		$todaySign = $DoSQL['todaysign'];
		$allSign = $DoSQL['allsign'];
		$todayBuy = $DoSQL['todaybuy'];
		$allBuy = $DoSQL['allbuy'];
		$todayStu = $DoSQL['todaystu'];
		$allStu = $DoSQL['allstu'];
	}else{ //公立模式
	
		$sql_todaybm  = "SELECT COUNT(id) FROM " .GetTableName('signup'). " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition3 ";
		$sql_allbm    = "SELECT COUNT(id) FROM " .GetTableName('signup'). " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' ";
		$sql_todaybjq = "SELECT COUNT(id) FROM " .GetTableName('bjq'). " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition3 ";
		$sql_allbjq   = "SELECT COUNT(id) FROM " .GetTableName('bjq'). " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' ";
		$sql_todaykq  = "SELECT COUNT(id) FROM " .GetTableName('checklog'). " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' And isconfirm = 1 $condition3 ";
		$sql_allkq    = "SELECT COUNT(id) FROM " .GetTableName('checklog'). " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' ";
		$DoSQL = pdo_fetch(" SELECT ({$sql_todaybm}) as todaybm , ({$sql_allbm}) as allbm , ({$sql_todaybjq}) as todaybjq , ({$sql_allbjq}) as allbjq ,  ({$sql_todaykq}) as todaykq , ({$sql_allkq}) as allkq");
		$baom = $DoSQL['todaybm'];
		$bm = $DoSQL['allbm'];
		$bjqz = $DoSQL['todaybjq'];
		$bjq = $DoSQL['allbjq'];
		$checklog = $DoSQL['todaykq'];
		$kq = $DoSQL['allkq'];
	}

	if($logo['is_cost'] == 1 || $_W['isfounder'] || $_W['role'] == 'owner' || $_W['role'] == 'vice_founder'){
		//订单统计
		$ddzj = pdo_fetchcolumn('SELECT SUM(cose) FROM ' . tablename($this->table_order) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND status = 2 "); //总收入额
		$cose = pdo_fetchcolumn('SELECT SUM(cose) FROM ' . tablename($this->table_order) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND status = 2 AND paytime > '{$start}' AND paytime < '{$end}'");
	}



	//统计最近 50条订单，并按日期归类
    $data = pdo_fetchall('SELECT * FROM ' . tablename($this->table_order) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition2 ORDER BY paytime DESC LIMIT 0,50");
    $total = array();
    if(!empty($data)) {
        foreach($data as &$da) {
            $total_price = $da['cose'];
            $ky = date('Y-m-d', $da['paytime']);
            $return[$ky]['cose'] += $total_price;
            $return[$ky]['count'] += 1;
            $total['total_price'] += $total_price;
            $total['total_count'] += 1;
            if($da['paytype'] == '1') {
                $return[$ky]['1'] += $total_price;
                $total['total_alipay'] += $total_price;
            } elseif($da['paytype'] == '2') {
                $return[$ky]['2'] += $total_price;
                $total['total_wechat'] += $total_price;
            }
        }
    } 
	$lastbjq = pdo_fetchall("SELECT bjq.content,bjq.isopen,bjq.msgtype,bjq.shername,bjq.createtime,member.avatar,classify.sname as bjname FROM ".GetTableName('bjq')." as bjq , ".GetTableName('classify')." as classify , ".tablename ('mc_members')." as member WHERE member.uid = bjq.uid and classify.sid = bjq.bj_id1 and bjq.schoolid = '{$schoolid}' and bjq.weid = '{$weid}' and bjq.type = 0 ORDER BY bjq.createtime DESC LIMIT 0,10  ");
	$lasttz = pdo_fetchall("SELECT n.type,n.title,n.ismobile,n.createtime,t.thumb,t.tname,c.sname as bjname FROM ".GetTableName('notice')." as n , ".GetTableName('classify')." as c , ".GetTableName('teachers')." as t WHERE c.sid = n.bj_id and t.id = n.tid and n.weid = '{$weid}' And n.schoolid = '{$schoolid}' ORDER BY n.createtime DESC LIMIT 0,10  ");
	if($schooltype){
		$lastxk = pdo_fetchall("SELECT stu.s_name as s_name , stu.icon as sicon , course.name as kcname ,kcs.createtime as createtime FROM ".GetTableName('kcsign')." as kcs , ".GetTableName('students')." as stu , ".GetTableName('tcourse')." as course  WHERE stu.id = kcs.sid and course.id = kcs.kcid and  kcs.weid = '{$weid}' AND kcs.schoolid = '{$schoolid}' and kcs.sid != 0 and kcs.tid = 0 ORDER BY kcs.createtime DESC LIMIT 0,10  ");
	}else{
		$lastkq = pdo_fetchall("SELECT * FROM " . tablename($this->table_checklog) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' ORDER BY createtime DESC LIMIT 0,10");
		foreach($lastkq as $index =>$row){
			$student = pdo_fetch("SELECT s_name,icon FROM " . tablename($this->table_students) . " WHERE id = '{$row['sid']}' ");
			$teacher = pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " WHERE id = '{$row['tid']}' ");
			$qdtid = pdo_fetch("SELECT tname,thumb FROM " . tablename($this->table_teachers) . " WHERE id = '{$row['qdtid']}' ");
			$idcard = pdo_fetch("SELECT pname FROM " . tablename($this->table_idcard) . " WHERE idcard = '{$row['cardid']}' ");
			$mac = pdo_fetch("SELECT name FROM " . tablename($this->table_checkmac) . " WHERE schoolid = '{$row['schoolid']}' And id = '{$row['macid']}' ");
			$banji = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE sid = '{$row['bj_id']}' ");
			$lastkq[$index]['s_name'] = $student['s_name'];
			$lastkq[$index]['sicon'] = $student['icon'];
			$lastkq[$index]['tname'] = $teacher['tname'];
			$lastkq[$index]['thumb'] = $teacher['thumb'];
			$lastkq[$index]['qdtname'] = $qdtid['tname'];
			$lastkq[$index]['mac'] = $mac['name'];
			$lastkq[$index]['pname'] = $idcard['pname'];
			$lastkq[$index]['bj_name'] = $banji['sname'];
			$lastkq[$index]['time'] = sub_day($row['createtime']);
		}
	}
	if(!empty($_GPC['addtime'])) {
		$starttime1 = strtotime($_GPC['addtime']['start']);
		$endtime1 = strtotime($_GPC['addtime']['end']) + 86399;
		$day = timediff($starttime1,$endtime1);
		$day_num =  $day['day']+1;
		$condition9 .= " AND createtime > '{$starttime1}' AND createtime < '{$endtime1}'";
		$condition8 .= " AND ( (startime1 < '{$starttime1}' AND endtime1 > '{$endtime1}') OR ( startime1 > '{$starttime1}' AND startime1 < '{$endtime1}') OR ( endtime1 > '{$starttime1}' AND endtime1 < '{$endtime1}'))";
	} else {
		$condition9 .= " AND createtime > '{$start}' AND createtime < '{$end}'";
		$condition8 .= " AND ( (startime1 < '{$start}' AND endtime1 > '{$end}') OR ( startime1 > '{$start}' AND startime1 < '{$end}') OR ( endtime1 > '{$start}' AND endtime1 < '{$end}'))";
	}
	if(GetSchoolType($schoolid,$weid)){
		// $njchecklog = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND ( type = 'semester' OR type = 'kcclass') ORDER BY ssort DESC ,sid DESC ");
		$njchecklog = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND type = 'semester' ORDER BY ssort DESC ,sid DESC ");
		// array_unshift($njchecklog,array('sid'=>0,'sname'=>'默认类型'));
	}else{
		$njchecklog = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND type = 'semester' AND is_over = 1 ORDER BY ssort DESC ,sid DESC ");

	}
 


	//定制，统计预警
	if(keep_mutikc()){
		mload()->model('kc');
		$Reminddata = GetKsWaring($weid,$schoolid,time(),0,-1,1,20);
		$remindlist = $Reminddata['list'];
		foreach($remindlist as $index => $row){
			//获取绑定者相关信息
			$userdata = pdo_fetch("SELECT userinfo,pard FROM " . tablename($this->table_user) . " WHERE sid = :sid ", array(':sid' => $row['sid']));
			$userinfo = unserialize($userdata['userinfo']);
			$guanxi = get_guanxi($userdata['pard']);
			$user = array(
				'guanxi' => $guanxi,
				'name' => $userinfo['name'],
				'mobile' => $userinfo['mobile'],
			);
			$student = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " WHERE id = :id ", array(':id' => $row['sid']));
			$kc = pdo_fetch("SELECT name FROM " . tablename($this->table_tcourse) . " WHERE id = :id ", array(':id' => $row['kcid']));
			$buycourse = pdo_fetchcolumn("SELECT ksnum FROM " . tablename($this->table_coursebuy) . " WHERE sid = :sid AND kcid=:kcid and  schoolid =:schoolid", array(':sid' => $row['sid'],':kcid'=> $row['kcid'],':schoolid'=> $schoolid));
			$hasSign =  pdo_fetchcolumn("SELECT sum(costnum) FROM " . tablename($this->table_kcsign) . " WHERE sid = :sid AND kcid=:kcid and  schoolid =:schoolid AND status =2 ", array(':sid' => $row['sid'],':kcid'=> $row['kcid'],':schoolid'=> $schoolid));
			//获取最近一次签到时间
			$nearkcsign =  pdo_fetchcolumn("SELECT MAX(createtime) FROM " . tablename($this->table_kcsign) . " WHERE sid = :sid AND kcid=:kcid and  schoolid =:schoolid AND status =2 ", array(':sid' => $row['sid'],':kcid'=> $row['kcid'],':schoolid'=> $schoolid));
			if(empty($student)){
				unset($remindlist[$index]);
			}else{
				$remindlist[$index]['userinfo'] = $user;
				$remindlist[$index]['s_name'] = trim($student['s_name']);
				$remindlist[$index]['mobile'] = $student['mobile'];
				$remindlist[$index]['kcname'] = $kc['name'];
				$remindlist[$index]['restnum'] = $buycourse - $hasSign;
				$remindlist[$index]['nearkcsign'] =  $nearkcsign ? date('m-d H:i',$nearkcsign) : '无上课记录';
			}
			
		}
		//var_dump($Reminddata['list']);
	}
	/**end**/
    include $this->template ( 'web/start' );
}
/**各课程情况**/
if($operation == 'd') {
	$kcid = $_GPC['kcid'];
	$kcinfo = pdo_fetch("SELECT * FROM " . tablename($this->table_tcourse) . " WHERE schoolid = :schoolid And id = :id", array(':schoolid' => $schoolid, ':id' => $kcid));
	$start = mktime(0,0,0,date("m"),date("d"),date("Y"));
	$end = $start + 86399;
	if(!empty($_GPC['start']) && $_GPC['start'] != '1970-01-01') {
		$starttime = strtotime($_GPC['start']);
		//var_dump($starttime);
		$endtime = strtotime($_GPC['end']) + 86399;
		if($kcinfo['start'] >$starttime && $kcinfo['start']<$endtime){
			$starttime = $kcinfo['start'];
		}
		if($kcinfo['end'] >$starttime && $kcinfo['end']<$endtime){
			$endtime = $kcinfo['end'];
		}
		//var_dump($kcinfo['start']);
		$condition3 .= " AND createtime > '{$starttime}' AND createtime < '{$endtime}'";
		$day = timediff($starttime,$endtime);
		$day_num =  $day['day']+1;
	} else {
		$condition3 .= " AND createtime > '{$start}' AND createtime < '{$end}'";
		$condition5 .= " AND ( (startime1 < '{$start}' AND endtime1 > '{$end}') OR ( startime1 > '{$start}' AND startime1 < '{$end}') OR ( endtime1 > '{$start}' AND endtime1 < '{$end}'))";
	}
	//var_dump($starttime);
	$NeedSignKc = 0;
	$DoSignKc = 0;
	$QingJiaKc = 0 ;
	$numOfks = 0 ;
	$numofPay = 0 ;
	$numofPrice = 0 ;

	if($day_num){
		$days = array();
		$daykey = array();
		for($i = 0; $i < $day_num; $i++){
			$keys = date('Y-m-d', $starttime + 86400 * $i);
			$days[$keys] = 0;
			$daykey[$keys] = 0;
		}
		foreach($daykey as $key_d=>$value_d){
			$start_d = strtotime($key_d);
			$end_d = $start_d + 86399;
			$stuNum = pdo_fetchcolumn("SELECT COUNT(distinct sid ) FROM " . tablename($this->table_order) . " WHERE schoolid = '{$schoolid}' and weid='{$weid}' and kcid = '{$kcid}' and type =1 and status = 2 ");
			$newPay = pdo_fetchcolumn("SELECT count(id)  FROM " . tablename($this->table_order) . " WHERE schoolid = '{$schoolid}' and weid='{$weid}' and kcid='{$kcid}' and type = 1 and status = 2 and tid = 0 and sid != 0  and paytime >= '{$start_d}' and paytime <= '{$end_d}' ");
			//var_dump($newPay);
			$newprice =  pdo_fetch("SELECT  sum(cose) as cose  FROM " . tablename($this->table_order) . " WHERE schoolid = '{$schoolid}' and weid='{$weid}' and kcid='{$kcid}' and status = 2 and type = 1 and paytime > '{$start_d}' and paytime < '{$end_d}' ");
			if($kcinfo['OldOrNew'] == 0){
				$ksSum = pdo_fetchall("SELECT  id  FROM " . tablename($this->table_kcbiao) . " WHERE schoolid = '{$schoolid}' and weid='{$weid}' and kcid = '{$kcid}' and date > '{$start_d}' and date < '{$end_d}' ");
				$numOfks += count($ksSum);
				$NeedSignKc += count($ksSum)* $stuNum;
				foreach($ksSum as $key_k=>$value_k){
					$stuSign = pdo_fetchcolumn("SELECT  COUNT(distinct sid )  FROM " . tablename($this->table_kcsign) . " WHERE schoolid = '{$schoolid}' and weid='{$weid}' and kcid='{$kcid}' and  ksid = '{$value_k['id']}' and status = 2 ");
					$DoSignKc += $stuSign;
					$stuQingJia = pdo_fetchcolumn("SELECT  COUNT(distinct sid )  FROM " . tablename($this->table_kcsign) . " WHERE schoolid = '{$schoolid}' and weid='{$weid}' and kcid='{$kcid}' and  ksid = '{$value_k['id']}'  and status = 3 ");
					$QingJiaKc += $stuQingJia;

				}
			}elseif($kcinfo['OldOrNew'] == 1){
				$ksSum = pdo_fetchcolumn("SELECT  count(1)  FROM " . tablename($this->table_kcsign) . " WHERE schoolid = '{$schoolid}' and weid='{$weid}' and kcid = '{$kcid}' and sid = 0 and tid != 0 and createtime > '{$start_d}' and createtime < '{$end_d}' and status = 2 ");
				$numOfks += $ksSum;
					/*  if($kcid == 205){
								var_dump($v['id']);
								var_dump($key_d);
								var_dump($ksSum);
							}   */
				$NeedSignKc += $ksSum* $stuNum;
				$stuSign = pdo_fetchcolumn("SELECT count(a.id) FROM " . tablename($this->table_kcsign) . " AS a WHERE (SELECT COUNT(*) FROM " . tablename($this->table_kcsign) . " AS b  WHERE b.sid = a.sid and b.createtime > '{$start_d}' and b.createtime < '{$end_d}' and b.status = 2 and b.schoolid = '{$schoolid}' and b.weid='{$weid}' and b.kcid = '{$kcid}' ) <= '{$ksSum}' and a.createtime > '{$start_d}' and a.createtime < '{$end_d}' and a.status = 2 and a.schoolid = '{$schoolid}' and a.weid='{$weid}' and a.kcid = '{$kcid}' ORDER BY a.sid ASC  ");
						//var_dump($stuSign);
				$DoSignKc += $stuSign;
				$stuQingJia = pdo_fetchcolumn("SELECT count(a.sid) FROM " . tablename($this->table_kcsign) . " AS a WHERE (SELECT COUNT(*) FROM " . tablename($this->table_kcsign) . " AS b  WHERE b.sid = a.sid and b.createtime > '{$start_d}' and b.createtime < '{$end_d}' and status = 3 and b.schoolid = '{$schoolid}' and b.weid='{$weid}' and b.kcid = '{$kcid}' ) <= '{$ksSum}' and a.createtime > '{$start_d}' and a.createtime < '{$end_d}' and a.status = 3 and a.schoolid = '{$schoolid}' and a.weid='{$weid}' and a.kcid = '{$kcid}'  ORDER BY a.sid ASC  ");
				$QingJiaKc +=  $stuQingJia;
			}
			$numofPay += $newPay;
			$numofPrice += $newprice['cose']?$newprice['cose']:0;

		}
	}else{
		$kcinfo = pdo_fetch("SELECT * FROM " . tablename($this->table_tcourse) . " WHERE schoolid = :schoolid And id = :id", array(':schoolid' => $schoolid, ':id' => $kcid));
		$stuNum = pdo_fetchcolumn("SELECT COUNT(distinct sid ) FROM " . tablename($this->table_order) . " WHERE schoolid = '{$schoolid}' and weid='{$weid}' and kcid = '{$kcid}' and type =1 and status = 2 ");
		$newPay = pdo_fetchcolumn("SELECT  COUNT(distinct sid )  FROM " . tablename($this->table_coursebuy) . " WHERE schoolid = '{$schoolid}' and weid='{$weid}' and kcid='{$kcid}' and createtime > '{$start}' and createtime < '{$end}' ");
		$newprice =  pdo_fetch("SELECT  sum(cose) as cose  FROM " . tablename($this->table_order) . " WHERE schoolid = '{$schoolid}' and weid='{$weid}' and kcid='{$kcid}' and status = 2 and type = 1 and paytime > '{$start}' and paytime < '{$end}' ");
		if($kcinfo['OldOrNew'] == 0){
			$ksSum = pdo_fetchall("SELECT  id  FROM " . tablename($this->table_kcbiao) . " WHERE schoolid = '{$schoolid}' and weid='{$weid}' and kcid = '{$kcid}' and date > '{$start}' and date < '{$end}' ");
			$numOfks = count($ksSum);
			$NeedSignKc = count($ksSum)* $stuNum;
			foreach($ksSum as $key_k=>$value_k){
				$stuSign = pdo_fetchcolumn("SELECT  COUNT(distinct sid )  FROM " . tablename($this->table_kcsign) . " WHERE schoolid = '{$schoolid}' and weid='{$weid}' and kcid='{$kcid}' and  ksid = '{$value_k['id']}' and status = 2 ");
				$DoSignKc += $stuSign;
				$stuQingJia = pdo_fetchcolumn("SELECT  COUNT(distinct sid )  FROM " . tablename($this->table_kcsign) . " WHERE schoolid = '{$schoolid}' and weid='{$weid}' and kcid='{$kcid}' and  ksid = '{$value_k['id']}'  and status = 3 ");
				$QingJiaKc += $stuQingJia;

			}
		}elseif($kcinfo['OldOrNew'] == 1){
			$ksSum = pdo_fetchcolumn("SELECT  count(1)  FROM " . tablename($this->table_kcsign) . " WHERE schoolid = '{$schoolid}' and weid='{$weid}' and kcid = '{$kcid}' and sid = 0 and tid != 0 and createtime > '{$start}' and createtime < '{$end}' and status = 2 ");
			$numOfks = $ksSum;
			$NeedSignKc = $ksSum* $stuNum;
			$stuSign = pdo_fetchcolumn("SELECT count(a.id) FROM " . tablename($this->table_kcsign) . " AS a WHERE (SELECT COUNT(*) FROM " . tablename($this->table_kcsign) . " AS b  WHERE b.sid = a.sid and b.createtime > '{$start}' and b.createtime < '{$end}' and b.status = 2 and b.schoolid = '{$schoolid}' and b.weid='{$weid}' and b.kcid = '{$kcid}' ) <= '{$ksSum}' and a.createtime > '{$start}' and a.createtime < '{$end}' and a.status = 2 and a.schoolid = '{$schoolid}' and a.weid='{$weid}' and a.kcid = '{$kcid}' ORDER BY a.sid ASC  ");
			$DoSignKc += $stuSign;
			$stuQingJia = pdo_fetchcolumn("SELECT count(a.sid) FROM " . tablename($this->table_kcsign) . " AS a WHERE (SELECT COUNT(*) FROM " . tablename($this->table_kcsign) . " AS b  WHERE b.sid = a.sid and b.createtime > '{$start}' and b.createtime < '{$end}' and status = 3 and b.schoolid = '{$schoolid}' and b.weid='{$weid}' and b.kcid = '{$kcid}' ) <= '{$ksSum}' and a.createtime > '{$start}' and a.createtime < '{$end}' and a.status = 3 and a.schoolid = '{$schoolid}' and a.weid='{$weid}' and a.kcid = '{$kcid}'  ORDER BY a.sid ASC  ");
			$QingJiaKc +=  $stuQingJia;
		}
		$numofPay = intval($newPay);
		$numofPrice = $newprice['cose']?$newprice['cose']:0;

	}
	$data['allthisbj'][] = ' ';
	$data['ksnum'][] =$numOfks ;
	$data['dosign'][] = $DoSignKc;
	$data['qingjia'][] = $QingJiaKc;
	$data['notsign'][] = ($NeedSignKc - $DoSignKc - $QingJiaKc)>0?$NeedSignKc - $DoSignKc - $QingJiaKc:0;
	$data['newPay'][] = $numofPay;
	$data['newcose'][] = $numofPrice;
	die ( json_encode ( $data ) );

}
/**end**/
/**各班出勤情况**/
if($operation == 'c') {
	if($_GPC['njid']) {
		$njid = $_GPC['njid'];
	} else {
		$frnjid = pdo_fetch("SELECT sid FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND type = 'semester' AND is_over = 1 ORDER BY ssort DESC ,sid DESC ");
		$njid = $frnjid['sid'];
	}
	$start = mktime(0,0,0,date("m"),date("d"),date("Y"));
	$end = $start + 86399;
	if(!empty($_GPC['start']) && $_GPC['start'] != '1970-01-01') {
		$starttime = strtotime($_GPC['start']);
		$endtime = strtotime($_GPC['end']) + 86399;
		$condition3 .= " AND createtime > '{$starttime}' AND createtime < '{$endtime}'";
		$day = timediff($starttime,$endtime);
		$day_num =  $day['day']+1;
	} else {
		$condition3 .= " AND createtime > '{$start}' AND createtime < '{$end}'";
		$condition5 .= " AND ( (startime1 < '{$start}' AND endtime1 > '{$end}') OR ( startime1 > '{$start}' AND startime1 < '{$end}') OR ( endtime1 > '{$start}' AND endtime1 < '{$end}'))";
	}

	$allthisbj = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " WHERE parentid = '{$njid}' ORDER BY ssort DESC ,sid DESC ");
	$allthisbjsname = array();
	$njcqzssss = array();
	$bjkqbl = array();
	$bjzrss = array();
	if($day_num){
		$days = array();
		$daykey = array();
		for($i = 0; $i < $day_num; $i++){
			$keys = date('Y-m-d', $starttime + 86400 * $i);
			$days[$keys] =[];
			$daykey[$keys] = [];
		}
		foreach($allthisbj as $index => $v){
			$bjzrs = pdo_fetchcolumn("SELECT COUNT(id) FROM " . tablename($this->table_students) . " WHERE schoolid = :schoolid And bj_id = :bj_id", array(':schoolid' => $schoolid, ':bj_id' => $v['sid']));
			$allbjqksm = pdo_fetchall("SELECT sid,createtime FROM " . tablename($this->table_checklog) . " WHERE bj_id = '{$v['sid']}' AND leixing = 1 AND isconfirm = 1  $condition3 ");
			$bjqksm = 0;
			foreach($allbjqksm as $da) {
				$key = date('Y-m-d', $da['createtime']);
				if(in_array($key, array_keys($days))) {
					if(!in_array($da['sid'], $daykey)) {
						$daykey[$key] = $da['sid'];
						$bjqksm++;
					}
				}
			}
			$bjzrss[] = $bjzrs;
			$njcqzssss[] =  $bjqksm;
			$bjkqbl[] =  round($bjqksm/($bjzrs*$day_num)*100,2);
			$allthisbjsname[] = $v['sname'];
		}
	}else{
		foreach($allthisbj as $index => $v){
			$bjzrs = pdo_fetchcolumn("SELECT COUNT(id) FROM " . tablename($this->table_students) . " WHERE schoolid = :schoolid And bj_id = :bj_id", array(':schoolid' => $schoolid, ':bj_id' => $v['sid']));
			$bjqksm = pdo_fetchcolumn("SELECT COUNT(distinct sid) FROM " . tablename($this->table_checklog) . " WHERE bj_id = '{$v['sid']}' AND leixing = 1 AND isconfirm = 1  $condition3 ");
			$njcqzssss[] =  $bjqksm;
			$allthisbjsname[] = $v['sname'];
			$bjkqbl[] = $bjzrs != 0 ?  round($bjqksm/$bjzrs*100,2) : 0 ;
		}
	}
	$data['allthisbj'] = $allthisbjsname;
	$data['bjcqzs'] = $njcqzssss;
	$data['bjkqbl'] = $bjkqbl;
	die ( json_encode ( $data ) );

}
/**end**/

if($operation == 'mc') {
	$normal = array(); //正常
	$nonormal = array(); //不正常
	$noexamine = array(); //未检测
	// 获取未毕业的班级所有学生数量
	$bj = pdo_fetchall("SELECT sid FROM ".GetTableName('classify')." WHERE schoolid = :schoolid AND is_over = :is_over ",array(':schoolid'=>$schoolid,':is_over'=>1));
	$bjstr = arrayToString($bj);
	$countstu = pdo_fetchcolumn("SELECT COUNT(id) FROM " . GetTableName('students') . " WHERE FIND_IN_SET(bj_id,'{$bjstr}')");
	/*身高-体重*/
	$nowday = strtotime(date('Y-m-d',time()));
	$basic = pdo_fetchcolumn("SELECT COUNT(id) FROM " . GetTableName('morningcheck') . " WHERE schoolid = :schoolid And createdate = :createdate", array(':schoolid' => $schoolid, ':createdate' => $nowday));
	$normal[0] = intval($basic);	//身高
	$nonormal[0] = 0;
	$normal[1] = intval($basic);	//体重
	$nonormal[1] = 0;
	$noexamine[0] = intval($countstu) - intval($basic);
	$noexamine[1] = intval($countstu) - intval($basic);
	/*身高-体重*/
	/*体温*/
	$normal_tiwen = pdo_fetchcolumn("SELECT COUNT(id) FROM " . GetTableName('morningcheck') . " WHERE schoolid = :schoolid And createdate = :createdate AND tiwen BETWEEN 35.5 AND 37.5", array(':schoolid' => $schoolid, ':createdate' => $nowday));
	$normal[2] = intval($normal_tiwen);	//正常体温
	$nonormal[2] =  intval($basic) - intval($normal_tiwen); //不正常体温
	$noexamine[2] = intval($countstu) - intval($basic);
	/*体温*/

	/*口腔*/
	$normal_mouth = pdo_fetchcolumn("SELECT COUNT(id) FROM " . GetTableName('morningcheck') . " WHERE schoolid = :schoolid And createdate = :createdate AND mouth = :mouth", array(':schoolid' => $schoolid, ':createdate' => $nowday,':mouth'=>1));
	$normal[3] = intval($normal_mouth); //正常口腔
	$nonormal[3] =  intval($basic) - intval($normal_mouth); //不正常口腔
	$noexamine[3] = intval($countstu) - intval($basic);
	/*口腔*/

	/*视力*/
	$normal_eye = pdo_fetchcolumn("SELECT COUNT(id) FROM " . GetTableName('morningcheck') . " WHERE schoolid = :schoolid And createdate = :createdate AND ((lefteye BETWEEN 4.5 AND 5.0) AND (righteye BETWEEN 4.5 AND 5.0))", array(':schoolid' => $schoolid, ':createdate' => $nowday));
	$normal[4] = intval($normal_eye);	//正常体温
	$nonormal[4] =  intval($basic) - intval($normal_eye); //不正常体温
	$noexamine[4] = intval($countstu) - intval($basic);
	/*体温*/
	$data['leixing'] = array('身高','体重','体温','口腔','视力');
	$data['normal'] = $normal;
	$data['nonormal'] = $nonormal;
	$data['noexamine'] = $noexamine;
	die ( json_encode ( $data ) );

}

if($operation == 'a') {
    if(!empty($_GPC['start']) && $_GPC['start'] != '1970-01-01' ) {
        $starttime = strtotime($_GPC['start']);
        $endtime = strtotime($_GPC['end']) + 86399;
    } else {
        $starttime = 0;
        $endtime = TIMESTAMP;
    }
    if($_W['isajax'] && $_W['ispost']) {
        $datasets = array(
            'unionpay' => array('name' => '银联支付', 'value' => 0),
            'alipay' => array('name' => '支付宝支付', 'value' => 0),
            'baifubao' => array('name' => '百付宝支付', 'value' => 0),
            'wechat' => array('name' => '微信支付', 'value' => 0),
            'cash' => array('name' => '现金支付', 'value' => 0),
            'credit' => array('name' => '余额支付', 'value' => 0)
        );
        $data = pdo_fetchall("SELECT id,pay_type FROM " . tablename($this->table_order) . 'WHERE weid = :weid AND schoolid = :schoolid and status = 2 and paytime >= :starttime and paytime <= :endtime', array(':weid' => $weid, ':schoolid' => $schoolid, ':starttime' => $starttime, 'endtime' => $endtime));
        foreach($data as $da) {
            if(in_array($da['pay_type'], array_keys($datasets))) {
                $datasets[$da['pay_type']]['value'] += 1;
            }
        }
        $datasets = array_values($datasets);
        message(error(0, $datasets), '', 'ajax');
    }
}
if($operation == 'b') {
    if(!empty($_GPC['start']) && $_GPC['start'] != '1970-01-01') {
        $starttime = strtotime($_GPC['start']);
        $endtime = strtotime($_GPC['end']) + 86399;
        $condition .= " AND createtime > '{$starttime}' AND createtime < '{$endtime}'";
    } else {
        $starttime = strtotime('-30 day');
        $endtime = TIMESTAMP;
        $condition .= "";
    }
    $bjq = pdo_fetchcolumn('SELECT COUNT(id) FROM ' . tablename($this->table_bjq) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition ");
    $bm = pdo_fetchcolumn('SELECT COUNT(id) FROM ' . tablename($this->table_signup) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition ");
    $xc = pdo_fetchcolumn('SELECT COUNT(id) FROM ' . tablename($this->table_media) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' And type = 2 $condition ");
    $tz = pdo_fetchcolumn('SELECT COUNT(id) FROM ' . tablename($this->table_notice) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition ");
    $kq = pdo_fetchcolumn('SELECT COUNT(id) FROM ' . tablename($this->table_checklog) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition ");
    $ly = pdo_fetchcolumn('SELECT COUNT(id) FROM ' . tablename($this->table_leave) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' And isliuyan = 2 And isfrist = 1 $condition ");
    $qj = pdo_fetchcolumn('SELECT COUNT(id) FROM ' . tablename($this->table_leave) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' And isliuyan = 0 $condition ");
    if($_W['isajax'] && $_W['ispost']) {
        $datasets = array(
            'bjq' => array('name' => '班级圈', 'value' => $bjq),
            'bm' => array('name' => '在线报名', 'value' => $bm),
            'tz' => array('name' => '通知公告', 'value' => $tz),
            'kq' => array('name' => '打卡考勤', 'value' => $kq),
            'ly' => array('name' => '在线留言', 'value' => $ly),
            'xc' => array('name' => '相册', 'value' => $xc),
            'qj' => array('name' => '在线请假', 'value' => $qj)
        );
        $datasets = array_values($datasets);
        message(error(0, $datasets), '', 'ajax');
    }
}

function timediff($begin_time,$end_time){
      if($begin_time < $end_time){
         $starttime = $begin_time;
         $endtime = $end_time;
      }else{
         $starttime = $end_time;
         $endtime = $begin_time;
      }

      //计算天数
      $timediff = $endtime-$starttime;
      $days = intval($timediff/86400);
      //计算小时数
      $remain = $timediff%86400;
      $hours = intval($remain/3600);
      //计算分钟数
      $remain = $remain%3600;
      $mins = intval($remain/60);
      //计算秒数
      $secs = $remain%60;
      $res = array("day" => $days,"hour" => $hours,"min" => $mins,"sec" => $secs);
      return $res;
}

?>