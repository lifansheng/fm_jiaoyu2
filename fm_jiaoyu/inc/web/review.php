<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_GPC, $_W;

$weid              = $_W['uniacid'];
$action            = 'chengji';
$this1             = 'no2';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
$logo              = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");


$xq    = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = '{$weid}' AND schoolid ={$schoolid} And type = 'semester' ORDER BY ssort DESC");
$qh    = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = '{$weid}' AND schoolid ={$schoolid} And type = 'score' and is_review = 1  AND is_show_qh = 1 ORDER BY ssort DESC");

$category = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " WHERE weid =  '{$weid}' AND schoolid ={$schoolid} ORDER BY sid ASC, ssort DESC", array(':weid' => $weid, ':schoolid' => $schoolid), 'sid');

$review_type = pdo_fetch("SELECT review_type FROM ".GetTableName('schoolset')." WHERE schoolid = '{$schoolid}' ")['review_type'];
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$tid_global = $_W['tid'];
if (!(IsHasQx($tid_global,1000805,1,$schoolid))){
	$this->imessage('非法访问，您无权操作该页面','','error');	
}
if($operation == 'display'){
    $pindex    = max(1, intval($_GPC['page']));
    $psize     = 20;
    $condition = '';
	if(!empty($_GPC['search_qh'])){
		$this_qhid = $_GPC['search_qh'];
	}else{
		$this_qhid =$qh[0]['sid'];
	}

	$this_qhinfo =  pdo_fetch("SELECT sid,sname,qhtype,qh_bjlist FROM " . tablename($this->table_classify) . " where weid = '{$weid}' AND schoolid ={$schoolid} and sid='{$this_qhid}' And type = 'score' ORDER BY ssort DESC");
	if($this_qhinfo['qhtype'] == 2){
		$this_bjlist = explode(",",$this_qhinfo['qh_bjlist']);
	}
	
	if(!empty($_GPC['search_nj'])){
		$this_njid = $_GPC['search_nj'];
	}else{
		$this_njid =$xq[0]['sid'];
	}
	$this_njinfo =  pdo_fetch("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = '{$weid}' AND schoolid ={$schoolid} and sid='{$this_njid}' And type = 'semester' ");
	$back_result = GetRviewByQhAndNj($this_qhid,$this_njid,$schoolid);
	// echo(json_encode($back_result));
	// die();
	if($_GPC['excel_review'] == 'excel_review'){
		$out_array = array();
		$title_array = array();
		$ii = 0	;
		foreach($back_result['data'] as $key=>$value){
			$out_array[$ii]['sname'] = $value['sname']; 
			$title_array['bj_name'] = '班级';
			foreach($value['data'] as $key_d=>$value_d){
				$key_avg_str = "avg".$key_d;
				$key_pass_str = "pass".$key_d;
				$key_final_str = "final".$key_d;
				$key_rank_str = "rank".$key_d;
				$title_array[$key_avg_str] =$value_d['km_name']."平均分";
				$title_array[$key_pass_str] =$value_d['km_name']."及格率";
				$title_array[$key_final_str] =$value_d['km_name']."得分";
				$title_array[$key_rank_str] =$value_d['km_name']."排名";
				$out_array[$ii][$key_avg_str] = $value_d['avg_score']; 
				$out_array[$ii][$key_pass_str] =$value_d['avg_per'];
				$out_array[$ii][$key_final_str] = $value_d['final_score']; 
				$out_array[$ii][$key_rank_str] = $value_d['rank']; 
			}
			$out_array[$ii]['allscore'] = $value['allscore']['score']; 
			$out_array[$ii]['allscorerank'] = $value['allscore']['rank_all']; 
			$title_array['allscore'] = "总分";
			$title_array['allscorerank'] = "总分排名";
			$ii++;
		}
		$title_excel = $this_qhinfo['sname'].'-'.$this_njinfo['sname'].' 考察结果';
		$this->exportexcel($out_array, $title_array, $title_excel);
		exit();
	}
	if($_GPC['excel_review_second'] == 'Km'){
		$SubArr = $back_result['Sub_Temp'];
		$BjData = $back_result['data'];
		$Title = ['年级','班级','科目','参评人数','总分','平均分','及格率','优秀率','名次','级段分差','级段平均分'];
		$ExcelData  = [];
		$ii = 0 ;
		foreach($SubArr as $key => $value){
			foreach($BjData as $keyb => $valueb){
				$ExcelData[$ii]['njname'] = $this_njinfo['sname'];
				$ExcelData[$ii]['bjname'] = $valueb['sname'];
				$ExcelData[$ii]['km_name'] = $valueb['data'][$key]['km_name'];
				$ExcelData[$ii]['cpnum'] = $valueb['data'][$key]['reviewcount'];
				$ExcelData[$ii]['allscore'] = $valueb['data'][$key]['all_score'];
				$ExcelData[$ii]['avgscore'] = $valueb['data'][$key]['avg_score'];
				$ExcelData[$ii]['avg_per'] = $valueb['data'][$key]['avg_per'];
				$ExcelData[$ii]['grate_per'] = $valueb['data'][$key]['grate_per'];
				$ExcelData[$ii]['rank'] = $valueb['data'][$key]['rank'];
				$ExcelData[$ii]['FC'] = $value['MaxAvg'] - $value['MinAvg'];
				$ExcelData[$ii]['JDAVG'] = round($value['AllScore'] / $value['AllCount'] ,2);
				$ii++;
			}
		}

		$title_excel = $this_qhinfo['sname'].'-'.$this_njinfo['sname'].' 科目成绩统计';
		$this->exportexcel($ExcelData, $Title, $title_excel);
		exit();
	}

	if($_GPC['excel_review_second'] == 'All'){
		$out_array = array();
		$title_array = array();
		$ii = 0	;
		foreach($back_result['data'] as $key=>$value){
			$out_array[$ii]['sname'] = $value['sname']; 
			$title_array['bj_name'] = '班级';
			$title_array['count'] = '参评人数';
			
			foreach($value['data'] as $key_d=>$value_d){
				$out_array[$ii]['count'] = $value_d['reviewcount']; 
				$key_final_str = "final".$key_d;
				$title_array[$key_final_str] =$value_d['km_name']."总分";
				$out_array[$ii][$key_final_str] = $value_d['all_score']; 
				
			}
			
			$out_array[$ii]['allscore'] = $value['allscore']['score']; 
			$out_array[$ii]['allscore_avg'] = $value['allscore']['avg_score']; 
			$out_array[$ii]['allscorerank'] = $value['allscore']['rank_all']; 
			$title_array['allscore'] = "总分";
			$title_array['avgscore'] = "平均分";
			$title_array['allscorerank'] = "总分排名";
			$ii++;
		}
		$title_excel = $this_qhinfo['sname'].'-'.$this_njinfo['sname'].' 综合统计';
		$this->exportexcel($out_array, $title_array, $title_excel);
		exit();
	}



	if($_GPC['fifty'] == 'fifty'){
		$index_fifty = 0;
		$fiftyOut = [];
		$qhinfo = pdo_fetch("SELECT sname,is_review,addedinfo,qhtype,qh_bjlist FROM " . tablename('wx_school_classify') . " where sid = '{$this_qhid}' And type='score' and schoolid = '{$schoolid}' ");
		$NjList = pdo_fetchall("SELECT distinct(score.xq_id) as xq_id FROM ".GetTableName('score')." as score ,".GetTableName('classify')." as class WHERE score.qh_id = '{$this_qhid}'   AND score.schoolid = '{$schoolid}' and class.sid = score.xq_id ORDER BY class.ssort DESC ");

		$ii_fifty = 0 ;
		$OutArr = [];
		foreach($NjList as $vn){
			$NjName = pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and sid = '{$vn['xq_id']}' ");
			// $count_before = pdo_fetchall(" select SUM(my_score) as tscore,sid,xq_id  FROM " . tablename($this->table_score) . "  where xq_id  = '{$vn['xq_id']}' and   qh_id = '{$this_qhid}'   AND schoolid = '{$schoolid}'  group by sid ORDER BY tscore DESC LIMIT 0,50 " ); 
			$count_before = pdo_fetchall(" select SUM(score.my_score) as tscore,stu.s_name,class.sname as bjname FROM " . GetTableName('score') . " as score , ". GetTableName('students')  ." as stu , ". GetTableName('classify')  ." as class  where score.xq_id  = '{$vn['xq_id']}' and   score.qh_id = '{$this_qhid}'   AND score.schoolid = '{$schoolid}' and stu.id = score.sid and class.sid = score.bj_id  group by score.sid ORDER BY tscore DESC ,stu.id DESC LIMIT 0,100 " );
			foreach($count_before as $vc){
				$rank = pdo_fetchcolumn(" select count(sid) FROM (SELECT distinct sid FROM ".GetTableName('score')." WHERE schoolid = '{$schoolid}' and xq_id = '{$vn['xq_id']}' and qh_id = '{$this_qhid}' group by sid HAVING SUM(my_score) > '{$vc['tscore']}') as scoretemp ") + 1;
				if($rank <= 50){
					$OutArr[$ii_fifty]['njname'] = $NjName['sname'];
					$OutArr[$ii_fifty]['sname'] = $vc['s_name'];
					$OutArr[$ii_fifty]['bjname'] = $vc['bjname'];
					$OutArr[$ii_fifty]['tscore'] = $vc['tscore'];
					$OutArr[$ii_fifty]['rank'] = $rank;
					$ii_fifty++;
				}
				
				 
			}
		}
		$title = ['年级','学生姓名','班级','总分','排名'];
		$title_excel = $this_qhinfo['sname'].'- 年级前五十名';
		$this->exportexcel($OutArr, $title, $title_excel);
		exit();
	}

	if($_GPC['nj3'] == 'nj3'){
		$NjList = pdo_fetchall("SELECT distinct(score.xq_id) as xq_id FROM ".GetTableName('score')." as score ,".GetTableName('classify')." as class WHERE score.qh_id = '{$this_qhid}'   AND score.schoolid = '{$schoolid}' and class.sid = score.xq_id ORDER BY class.ssort DESC ");
		$ii_nj3 = 0 ;
		$OutArr = [];
		foreach($NjList as $vn){
			$back_result = GetRviewByQhAndNj($this_qhid,$vn['xq_id'],$schoolid)['Sub_Temp'];
			$NjName = pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE sid = '{$vn['xq_id']}' ");
			foreach($back_result as $kb=>$vb){
				$KmMame = pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE sid = '{$kb}' ");
				$OutArr[$ii_nj3]['NjName'] = $NjName['sname'];
				$OutArr[$ii_nj3]['KmName'] = $KmMame['sname'];
				$OutArr[$ii_nj3]['AllCount'] = $vb['AllCount'];
				$OutArr[$ii_nj3]['AllScore'] = $vb['AllScore'];
				$OutArr[$ii_nj3]['avg'] = round($vb['AllScore'] / $vb['AllCount'], 2);
				$OutArr[$ii_nj3]['passper'] = round($vb['passCount'] / $vb['AllCount'] * 100 , 2)>100?100:round($vb['passCount'] / $vb['AllCount'] * 100 , 2);
				$OutArr[$ii_nj3]['Greatper'] = round($vb['greatCount'] / $vb['AllCount'] * 100 , 2)>100?100:round($vb['greatCount'] / $vb['AllCount'] * 100 , 2);
				$ii_nj3++;
			}
			
		}
		$title = ['年级','科目','总人数','总分','平均分','及格率','优秀率'];
		$title_excel = $this_qhinfo['sname'].'- 年级三率';
		$this->exportexcel($OutArr, $title, $title_excel);
		exit();
	}

	
	$lastdata = end($back_result['data']);
}
include $this->template('web/review');
?>