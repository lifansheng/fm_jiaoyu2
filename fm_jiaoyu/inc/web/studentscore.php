<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_GPC, $_W;

$weid              = $_W['uniacid'];
$action            = 'studentscore';
$this1             = 'no2';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
$logo              = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$km    = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = '{$weid}' AND schoolid ={$schoolid} And type = 'subject' ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'subject', ':schoolid' => $schoolid));
$bj    = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = '{$weid}' AND schoolid ={$schoolid} And type = 'theclass' ORDER BY ssort DESC");
$nj = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = '{$weid}' AND schoolid ={$schoolid} And type = 'semester' ORDER BY ssort DESC");
$xq = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = '{$weid}' AND schoolid ={$schoolid} And type = 'xq_score' ORDER BY ssort DESC");
$sd    = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = '{$weid}' AND schoolid ={$schoolid} And type = 'timeframe' ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'timeframe', ':schoolid' => $schoolid));
$qh    = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = '{$weid}' AND schoolid ={$schoolid} And type = 'score' ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'score', ':schoolid' => $schoolid));
$tid_global = $_W['tid'];
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if (!(IsHasQx($tid_global,1003301,1,$schoolid))){
	$this->imessage('非法访问，您无权操作该页面2','','error');	
}

if($tid_global == 'founder' || $tid_global == 'owner'){
	$scoreOb  = pdo_fetchall("SELECT sname,sid,parentid FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'tscoreobject' And schoolid = {$schoolid} And parentid != 0  ORDER BY CONVERT(sname USING gbk) ASC",array(),'sid');
	$scoreObPa = pdo_fetchall("SELECT sname,sid FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'tscoreobject' And schoolid = {$schoolid} And parentid = 0  ORDER BY CONVERT(sname USING gbk) ASC",array(),'sid');
	
	$limit = 0 ;
}else{
	$teacher = pdo_fetch("SELECT status,fz_id FROM " . tablename($this->table_teachers) . " where id = '{$tid_global}' ");
	if($teacher['status'] == 2){
		$scoreOb  = pdo_fetchall("SELECT sname,sid,parentid FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'tscoreobject' And schoolid = {$schoolid} And parentid != 0  ORDER BY CONVERT(sname USING gbk) ASC",array(),'sid');
		$scoreObPa  = pdo_fetchall("SELECT sname,sid FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'tscoreobject' And schoolid = {$schoolid} And parentid = 0  ORDER BY CONVERT(sname USING gbk) ASC",array(),'sid');
		$limit = 0 ;
	}else{
		$scoreOb  = pdo_fetchall("SELECT sname,sid,parentid FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'tscoreobject' And schoolid = {$schoolid} And parentid != 0 and fzid = '{$teacher['fz_id']}'  ORDER BY CONVERT(sname USING gbk) ASC",array(),'sid');
		$scoreObPa  = pdo_fetchall("SELECT sname,sid FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'tscoreobject' And schoolid = {$schoolid} And parentid = 0 and fzid = '{$teacher['fz_id']}'  ORDER BY CONVERT(sname USING gbk) ASC",array(),'sid');
		$limit = 1 ;
	}
	
}
if($operation == 'post'){
	if (!(IsHasQx($tid_global,1003302,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面','','error');	
	}
    load()->func('tpl');
    $id = intval($_GPC['id']);
    if(!empty($id)){
        $item    = pdo_fetch("SELECT * FROM " . tablename($this->table_teascore) . " WHERE id = :id", array(':id' => $id));
        $student = pdo_fetch("SELECT s_name FROM " . tablename($this->table_students) . " where id = :id ", array(':id' => $item['sid']));
		$bj_name = pdo_fetch("select sname FROM " . tablename($this->table_classify) . "  where  weid = '{$weid}' AND schoolid = '{$schoolid}' AND sid = '{$item['bj_id']}' ");
		$nj_name = pdo_fetch("select sname FROM " . tablename($this->table_classify) . "  where  weid = '{$weid}' AND schoolid = '{$schoolid}' AND sid = '{$item['nj_id']}' ");
		
		if($item['fromtid'] != 'founder' && $item['fromtid'] !='owner' ){
			$fromteacher = pdo_fetch("SELECT tname,fz_id,status FROM " . tablename($this->table_teachers) . " where id = :id ", array(':id' => $item['fromtid']));
		}else{
			$fromteacher['tname'] = "管理员";
			$fromfz['name'] = "管理员";
		}

      
        if(empty($item)){
            $this->imessage('抱歉，本条信息不存在在或是已经删除！', '', 'error');
        }
    }
    if(checksubmit('submit')){
        $data = array(
            'score' => trim($_GPC['score']),
        );

        if(empty($id)){
            $this->imessage('抱歉，本条信息不存在在或是已经删除！', '', 'error');
        }else{
            pdo_update($this->table_teascore, $data, array('id' => $id));
        }
        $this->imessage('修改学生量化评分成功！', $this->createWebUrl('studentscore', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
    }
}elseif($operation == 'export'){
	if(checksubmit()) {
			
		$file = upload_file($_FILES['file'], 'excel');
	
		if(is_error($file)) {
			$this->imessage($file['message'],'','error');
		}
		
		$data = read_excel($file);
		
		if(is_error($data)) {
			$this->imessage($data['message'],'','error');
		}
		unset($data[1]);
		if(empty($data)) {
			$this->imessage('没有要导入的数据','','error');
		}
		$suc_num = 0;
		$def_num = 0;
		//print_r($data);
		foreach($data as $strs) {
			
			$bj_id = pdo_fetch("SELECT sid,parentid FROM " . tablename($this->table_classify) . " WHERE sname=:sname AND weid=:weid And schoolid=:schoolid  ", array(':sname' => trim($strs[1]), ':weid' => $weid, ':schoolid'=> $schoolid));
			$sid = pdo_fetch("SELECT id FROM " . tablename($this->table_students) . " WHERE s_name=:s_name AND weid=:weid And schoolid=:schoolid and bj_id = :bj_id  ", array(':s_name' => trim($strs[0]), ':weid' => $weid, ':schoolid'=> $schoolid,'bj_id'=>$bj_id['sid']));
			if(empty($sid)){
				$def_num++;
				continue;
			}else{
				$insert['sid'] = empty($sid) ? 0 : intval($sid['id']);
				$insert['score'] = trim($strs[2]);
				$insert['bj_id'] = $bj_id['sid'];
				$insert['nj_id'] = $bj_id['parentid'];

				$insert['fromtid'] = $tid_global;
				if($tid_global == 'founder' || $tid_global == 'owner' ){
					$insert['fromfzid'] = -1 ;
				}else{
					$fromtea = pdo_fetch("SELECT fz_id FROM " . tablename($this->table_teachers) . " WHERE tid=:tid AND weid=:weid And schoolid=:schoolid  ", array(':tid' => $tid_global, ':weid' => $_W['uniacid'], ':schoolid'=> $schoolid));
					$insert['fromfzid'] = $schoolid;
				}
				$insert['scoretime'] = strtotime($strs[3]);
				$insert['schoolid'] = $schoolid;
				$insert['weid'] = $_W['uniacid'];
				$insert['type'] = 1;
				$insert['createtime'] = time();
				$check = pdo_fetch("SELECT id FROM " . tablename($this->table_teascore) . " WHERE sid='{$sid['id']}' AND weid='{$_W['uniacid']}' And schoolid='{$schoolid}'  and scoretime = '{$insert['scoretime']}' and type = 1 ");
				if(!empty($check)){
					pdo_update($this->table_teascore, $insert,array('id'=>$check['id']));
				}else{
					pdo_insert($this->table_teascore, $insert);
				}
				$suc_num++;
			}

		}
		$this->imessage('导入成功'.$suc_num.'条评分，失败'.$def_num.'', $this->createWebUrl('studentscore', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
	}
	/*
	//导出学生量化积分*****************************************************
}elseif($operation == 'out_list'){
	if($limit == 0 ){
		$condition = ' ';	
	}elseif($limit == 1){
		$ob_str = '';
		foreach($scoreOb as $key_s=>$value_s){
			$ob_str .= $value_s['sid'].","; 
		}
		
		$ob_str = trim($ob_str,",");
		$condition = "and FIND_IN_SET(obid,'{$ob_str}')";	
	}
	$scoretime = $_GPC['scoretime'];
	if(empty($scoretime)){
		 $this->imessage('抱歉，请先选择时间！','','error');
	}
	$condition .= " and scoretime = '{$scoretime}' ";

	$list = pdo_fetchall("SELECT tid,sum(score) as allscore FROM " . tablename($this->table_teascore) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition  and type = 1  group by tid ORDER BY tid DESC  ");
		$ii = 0;
		$array_out = array();
		foreach($list as $key => $row){
			$array_out[$ii]['tname'] = pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " where id = :id ", array(':id' => $row['tid']))['tname'];
			foreach($scoreOb as $key_s=>$value_s){
				$array_out[$ii][$value_s['sname']] = pdo_fetchcolumn("SELECT score FROM " . tablename($this->table_teascore) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and tid = '{$row['tid']}' and scoretime = '{$scoretime}' and  obid = '{$value_s['sid']}' ") ? pdo_fetchcolumn("SELECT score FROM " . tablename($this->table_teascore) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and tid = '{$row['tid']}' and scoretime = '{$scoretime}' and  obid = '{$value_s['sid']}' ") :'0';
			}
			foreach($scoreObPa as $key_sp=>$value_sp){
				$array_out[$ii][$value_sp['sname']] = pdo_fetchcolumn("SELECT sum(score) as countscore FROM " . tablename($this->table_teascore) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and tid = '{$row['tid']}' and scoretime = '{$scoretime}' and  parentobid = '{$value_sp['sid']}' ") ? pdo_fetchcolumn("SELECT sum(score) as countscore FROM " . tablename($this->table_teascore) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and tid = '{$row['tid']}' and scoretime = '{$scoretime}' and  parentobid = '{$value_sp['sid']}' ") : '0';
			}
			$array_out[$ii]['all'] = $row['allscore'];
			 $ii++;
		}
		//var_dump($array_out);
		//die();
		$first_title = array();
		$first_title['tname'] = "教师姓名";
		foreach($scoreOb as $key_s=>$value_s){
			$first_title[$value_s['sid']] =$value_s['sname'];
		}
		foreach($scoreObPa as $key_sp=>$value_sp){
			$first_title[$value_sp['sid']] = $value_sp['sname'].'汇总';
		}
		$first_title[] = '总分';
		    $title="评分信息-".date("Y-m",$scoretime);
		    $this->exportexcel($array_out, $first_title, $title);
			exit();
			*/
}elseif($operation == 'display'){

    $pindex    = max(1, intval($_GPC['page']));
    $psize     = 20;

    if(!empty($_GPC['stuname'])){
		$student = pdo_fetch("SELECT id FROM " . tablename($this->table_students) . " WHERE schoolid = :schoolid And s_name = :s_name ORDER BY id DESC LIMIT 1", array(':schoolid' => $schoolid,':s_name' => $_GPC['stuname']));
		$condition .= " AND sid = '{$student['id']}'";		
    }

    if(!empty($_GPC['pfxq_id'])){
        $pfxq_id      = intval($_GPC['pfxq_id']);
       	$search_xqid  = pdo_fetch("select sd_start,sd_end FROM " . tablename($this->table_classify) . "  where  weid = '{$weid}' AND schoolid = '{$schoolid}' AND sid = '{$pfxq_id}'  ");
		$condition .= " AND scoretime >= '{$search_xqid['sd_start']}'  AND scoretime <= '{$search_xqid['sd_start']}'";
    }
	
	if(!empty($_GPC['nj_id'])){
		$condition .= " AND nj_id = '{$_GPC['nj_id']}'";		
    }
	
	if(!empty($_GPC['bj_id'])){
		$condition .= " AND bj_id = '{$_GPC['bj_id']}'";		
    }
	
	if(!empty($_GPC['scoretime'])){
		$starttime = strtotime($_GPC['scoretime']['start']);
		$endtime   = strtotime($_GPC['scoretime']['end']) + 86399;
		$condition .= " AND scoretime <= '{$endtime}'  AND scoretime >= '{$starttime}'";
    }else{
        $starttime = strtotime('-300 day');
        $endtime   = TIMESTAMP;
    }
	
	$list = pdo_fetchall("SELECT * FROM " . tablename($this->table_teascore) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition  and type = 1 ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
	//按成绩排名 
	$test = pdo_fetchall("select t.*,(select count(s.score)+1 FROM " . tablename($this->table_teascore) . " as s  where s.score>t.score and s.weid = '{$weid}' AND s.schoolid = '{$schoolid}') as rank  FROM " . tablename($this->table_teascore) . " as t where t.weid = '{$weid}' AND t.schoolid = '{$schoolid}'  and t.tid = '765'   order by t.score desc  LIMIT " . ($pindex - 1) * $psize . ',' . $psize ); 

	foreach($list as $key => $row){
		
		$score_xueqi = pdo_fetch("select * FROM " . tablename($this->table_classify) . "  where  weid = '{$weid}' AND schoolid = '{$schoolid}' AND sd_start <='{$row['scoretime']}' and sd_end >='{$row['scoretime']}'  ");
		
		
		$all_score_xueqi = pdo_fetchcolumn("select SUM(score) FROM " . tablename($this->table_teascore) . "  where weid = '{$weid}' AND schoolid = '{$schoolid}' and sid = '{$row['sid']}' AND scoretime >='{$score_xueqi['sd_start']}' AND scoretime <='{$score_xueqi['sd_end']}' ");
		
		$count_before = pdo_fetchall(" select sid  FROM " . tablename($this->table_teascore) . "  where  bj_id = '{$row['bj_id']}' AND scoretime >='{$score_xueqi['sd_start']}' AND scoretime <='{$score_xueqi['sd_end']}'  AND schoolid = '{$schoolid}'  group by sid   HAVING SUM(score)>'{$all_score_xueqi}'    " );  
		$bj_rank_all = count($count_before)+1;
		
		$count_before_nj = pdo_fetchall(" select sid  FROM " . tablename($this->table_teascore) . "  where  nj_id = '{$row['nj_id']}' AND scoretime >='{$score_xueqi['sd_start']}' AND scoretime <='{$score_xueqi['sd_end']}'  AND schoolid = '{$schoolid}'  group by sid   HAVING SUM(score)>'{$all_score_xueqi}'     " );  
		$nj_rank_all = count($count_before_nj)+1;
		  

		$bj_name = pdo_fetch("select sname FROM " . tablename($this->table_classify) . "  where  weid = '{$weid}' AND schoolid = '{$schoolid}' AND sid = '{$row['bj_id']}' ");
		$nj_name = pdo_fetch("select sname FROM " . tablename($this->table_classify) . "  where  weid = '{$weid}' AND schoolid = '{$schoolid}' AND sid = '{$row['nj_id']}' ");
		
		$bj_rank = pdo_fetch("select count(score)+1 as rank FROM " . tablename($this->table_teascore) . "  where score>'{$row['score']}' and  weid = '{$weid}' AND schoolid = '{$schoolid}' AND scoretime ='{$row['scoretime']}' and bj_id = '{$row['bj_id']}' ");
		$nj_rank = pdo_fetch("select count(score)+1 as rank FROM " . tablename($this->table_teascore) . "  where score>'{$row['score']}' and  weid = '{$weid}' AND schoolid = '{$schoolid}' AND scoretime ='{$row['scoretime']}' and nj_id = '{$row['nj_id']}' ");
		
		$list[$key]['bj_name'] = $bj_name['sname'];
		$list[$key]['nj_name'] = $nj_name['sname'];
		$list[$key]['bj_rank'] = $bj_rank['rank'];
		$list[$key]['nj_rank'] = $nj_rank['rank'];
		$list[$key]['bj_sum_rank'] = $bj_rank_all;
		$list[$key]['nj_sum_rank'] = $nj_rank_all;
		$list[$key]['score_xueqi_name'] = $score_xueqi['sname'];
		$list[$key]['score_all'] = $all_score_xueqi;
		
		$list[$key]['s_name']  = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where id = :id ", array(':id' => $row['sid']))['s_name'];
		if($row['fromtid'] != 'founder' && $row['fromtid'] != 'owner'){
			$fromteacher = pdo_fetch("SELECT tname,status,fz_id FROM " . tablename($this->table_teachers) . " where id = :id ", array(':id' => $row['fromtid']));
			$list[$key]['fromtname']  =$fromteacher['tname'];
			$list[$key]['fromtstatus']  =$fromteacher['status'];
			$list[$key]['fromfzname']  = pdo_fetch("SELECT * FROM " . tablename($this->table_classify) . " where sid = :sid ", array(':sid' => $row['fromfzid']))['sname'];
		}else{
			$list[$key]['fromtname']  ='管理员';
			$list[$key]['fromtstatus']  = 0 ;
			$list[$key]['fromfzname']  = '管理员';
		}
		$obinfo = pdo_fetch("SELECT * FROM " . tablename($this->table_classify) . " where sid = :sid ", array(':sid' => $row['obid']));
		$parent_ob = pdo_fetch("SELECT * FROM " . tablename($this->table_classify) . " where sid = :sid ", array(':sid' => $obinfo['parentid']));
		$list[$key]['obname']  = $obinfo['sname'];
		$list[$key]['PAobname']  = $parent_ob['sname'];
	   
	}
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_teascore) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition and type = 1");

	$pager = pagination($total, $pindex, $psize);
	
}elseif($operation == 'delete'){
    $id = intval($_GPC['id']);
    if(empty($id)){
        $this->imessage('抱歉，本条信息不存在在或是已经被删除！');
    }
    pdo_delete($this->table_teascore, array('id' => $id));
    $this->imessage('删除成功！', referer(), 'success');
}elseif($operation == 'deleteall'){
    $rowcount    = 0;
    $notrowcount = 0;
    foreach($_GPC['idArr'] as $k => $id){
        $id = intval($id);
        if(!empty($id)){
            $goods = pdo_fetch("SELECT * FROM " . tablename($this->table_teascore) . " WHERE id = :id", array(':id' => $id));
            if(empty($goods)){
                $notrowcount++;
                continue;
            }
            pdo_delete($this->table_teascore, array('id' => $id, 'weid' => $weid));
            $rowcount++;
        }
    }
    $message = "操作成功！共删除{$rowcount}条数据,{$notrowcount}条数据不能删除!";

    $data ['result'] = true;

    $data ['msg'] = $message;

    die (json_encode($data));
}
include $this->template('web/studentscore');
?>