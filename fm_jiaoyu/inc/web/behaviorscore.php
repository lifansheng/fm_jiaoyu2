<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_GPC, $_W;

$weid              = $_W['uniacid'];
$action            = 'behaviorscore';
$this1             = 'no2';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
$logo              = pdo_fetch("SELECT logo,title,spic FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$km    = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = '{$weid}' AND schoolid ={$schoolid} And type = 'subject' ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'subject', ':schoolid' => $schoolid));
$bj    = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = '{$weid}' AND schoolid ={$schoolid} And type = 'theclass' ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'theclass', ':schoolid' => $schoolid));
$xq    = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = '{$weid}' AND schoolid ={$schoolid} And type = 'week' ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'week', ':schoolid' => $schoolid));
$sd    = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = '{$weid}' AND schoolid ={$schoolid} And type = 'timeframe' ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'timeframe', ':schoolid' => $schoolid));
$qh    = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = '{$weid}' AND schoolid ={$schoolid} And type = 'score' ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'score', ':schoolid' => $schoolid));
$tid_global = $_W['tid'];
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
mload()->model('tea');


if($tid_global == 'founder' || $tid_global == 'owner'){
	$bjlist = pdo_fetchall("SELECT sname as old_sname ,sid FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and type = 'theclass' ORDER BY ssort DESC ");
}else{
	$bjlist = GetAllClassInfoByTid($schoolid,0,$_W['schooltype'],$tid_global);
}
$qhlist = pdo_fetchall("SELECT sname,sid FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and type = 'xq_score' ORDER BY ssort DESC,CONVERT(sname USING gbk) ASC ");

if($operation == 'post'){
	if (!(IsHasQx($tid_global,1003102,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面','','error');
	}
    load()->func('tpl');
    $id = intval($_GPC['id']);
    if(!empty($id)){
        $item    = pdo_fetch("SELECT * FROM " . tablename($this->table_teascore) . " WHERE id = :id", array(':id' => $id));
        $teacher = pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " where id = :id ", array(':id' => $item['tid']));
		if($item['fromtid'] != 'founder' && $item['fromtid'] !='owner' ){
			$fromteacher = pdo_fetch("SELECT tname,fz_id,status FROM " . tablename($this->table_teachers) . " where id = :id ", array(':id' => $item['fromtid']));
			$fromfz = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where sid = :sid ", array(':sid' => $fromteacher['fz_id']));
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
			'obid'  => $_GPC['ob_id'],
            'score' => trim($_GPC['score']),
        );

        if(empty($id)){
            $this->imessage('抱歉，本条信息不存在在或是已经删除！', '', 'error');
        }else{
            pdo_update($this->table_teascore, $data, array('id' => $id));
        }
        $this->imessage('修改老师评分成功！', $this->createWebUrl('teascore', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
    }
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

	$list = pdo_fetchall("SELECT tid,sum(score) as allscore FROM " . tablename($this->table_teascore) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition  and type = 0  group by tid ORDER BY tid DESC  ");
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
}elseif($operation == 'display'){
	/* if (!(IsHasQx($tid_global,1000801,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面','','error');	
	} */ 
    $pindex    = max(1, intval($_GPC['page']));
    $psize     = 20;
	if($limit == 0 ){
		$condition = '';	
	}elseif($limit == 1){
		$ob_str = '';
		foreach($scoreOb as $key_s=>$value_s){
			$ob_str .= $value_s['sid'].","; 
		}
		$ob_str = trim($ob_str,",");
		$condition = "and FIND_IN_SET(obid,'{$ob_str}')";	
	}
   $scoreT = pdo_fetchall("SELECT distinct scoretime FROM " . tablename($this->table_teascore) . " WHERE weid ='{$weid}' and schoolid = '{$schoolid}' $condition and type = 0 ");
    if(!empty($_GPC['t_name'])){
		$teachers = pdo_fetch("SELECT id FROM " . tablename($this->table_teachers) . " WHERE schoolid = :schoolid And tname = :tname ORDER BY id DESC LIMIT 1", array(':schoolid' => $schoolid,':tname' => $_GPC['t_name']));
		$condition .= " AND tid = '{$teachers['id']}'";		
    }

    if(!empty($_GPC['ob_id'])){
        $cid       = intval($_GPC['ob_id']);
        $condition .= " AND obid = '{$cid}'";
    }
	
	
	if(!empty($_GPC['scoretime'])){
		$starttime = strtotime($_GPC['scoretime']['start']);
		$endtime   = strtotime($_GPC['scoretime']['end']) + 86399;
		$condition .= " AND scoretime <= '{$endtime}'  AND scoretime >= '{$starttime}'";
    }else{
        $starttime = strtotime('-300 day');
        $endtime   = TIMESTAMP;
    }
	
	$list = pdo_fetchall("SELECT * FROM " . tablename($this->table_teascore) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition  and type = 0 ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
	//按成绩排名 
	$test = pdo_fetchall("select t.*,(select count(s.score)+1 FROM " . tablename($this->table_teascore) . " as s  where s.score>t.score and s.weid = '{$weid}' AND s.schoolid = '{$schoolid}') as rank  FROM " . tablename($this->table_teascore) . " as t where t.weid = '{$weid}' AND t.schoolid = '{$schoolid}'  and t.tid = '765'   order by t.score desc  LIMIT " . ($pindex - 1) * $psize . ',' . $psize ); 
	//$sql = "select t.tid, t.score,(select count(s.score)+1 FROM " . tablename($this->table_teascore) . " as s  where s.score>t.score and   s.weid = '{$weid}' AND s.schoolid = '{$schoolid}' AND s.scoretime <= '{$endtime}'  AND s.scoretime >= '{$starttime}') as rank  FROM " . tablename($this->table_teascore) . " as t where t.weid = '{$weid}' AND t.schoolid = '{$schoolid}' AND t.scoretime <= '{$endtime}'  AND t.scoretime >= '{$starttime}'  order by t.score desc ";
	//var_dump($test);
	foreach($list as $key => $row){
		$list[$key]['tname']  = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " where id = :id ", array(':id' => $row['tid']))['tname'];
		if($tid_global != 'founder' && $row['tid'] != 'owner'){
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
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_teascore) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition and type = 0");

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
}elseif($operation == 'getstubybj'){
	$bjid 	 = $_GPC['bjid'];
	$QhId = $_GPC['qhid'];
	$stulist = pdo_fetchall("SELECT s_name,id,icon FROM ".GetTableName('students')." WHERE schoolid = '{$schoolid}' and bj_id = '{$bjid}' order by CONVERT(s_name USING gbk) ASC ");
	foreach ($stulist as $key => $value) {
		$stulist[$key]['stuicon'] = $value['icon'] ? tomedia($value['icon']) : tomedia($logo['spic']);
		$tid = intval($tid_global) > 0 ? $tid_global : -1 ;
		$check = pdo_fetch("SELECT id FROM ".GetTableName('behaviorscorelog')." WHERE schoolid = '{$schoolid}' and sid = '{$value['id']}' and qhid = '{$QhId}'  and tid = '{$tid}' ");
		$stulist[$key]['done'] = false;
		if(!empty($check)){
			$stulist[$key]['done'] = true;
		}
	}
	die(json_encode($stulist));
}elseif($operation == "getscorellist"){
	$bjid = $_GPC['bjid'];
	$stuid = $_GPC['stuid'];
	$qhid = $_GPC['qhid'];
	$tid = intval($tid_global) > 0 ? $tid_global : -1 ;
	$bhs_list = pdo_fetchall("SELECT sname,sid FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and FIND_IN_SET('{$bjid}',qh_bjlist) and type = 'behaviorscore' order by ssort DESC ");
	$StuBHSList = pdo_fetchall("SELECT * FROM ".GetTableName('behaviorscorelog')." WHERE schoolid = '{$schoolid}' and qhid = '{$qhid}' and sid = '{$stuid}' and tid = '{$tid}' ",array(),'bhsid');
	 
	include $this->template('web/behaviorscore_bot');
	die();
}elseif($operation == 'getBHSword'){
	$BHSid = $_GPC['BHSid'];
	$word = pdo_fetch("SELECT addedinfo FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and sid = '{$BHSid}' ");
	die($word['addedinfo']);
}elseif($operation == 'SaveBHS'){
	$Score = $_GPC['score'];
	$Word = $_GPC['word'];
	$StuId = $_GPC['stuid'];
	$QhId = $_GPC['qhid']; 
	// var_dump($Score);
	// die();
	 
	 
	foreach($Score as $key_s => $value_s){
		$insertData = array(
			'weid' => $weid,
			'schoolid' => $schoolid,
			'sid' => $StuId,
			'qhid' => $QhId,
			'score' => $value_s,
			'word' => $Word[$key_s],
			'bhsid' => $key_s,
			'createtime' => time(),
			'tid' => intval($tid_global) <= 0 ? -1 : $tid_global 
		);
		$tid = intval($tid_global) > 0 ? $tid_global : -1 ;
		$check = pdo_fetch("SELECT id FROM ".GetTableName('behaviorscorelog')." WHERE schoolid = '{$schoolid}' and sid = '{$StuId}' and qhid = '{$QhId}' and bhsid = '{$key_s}' and tid = '{$tid}' ");
		if(!empty($check)){
			pdo_update(GetTableName('behaviorscorelog',false),$insertData,array('id' => $check['id']));
		}else{
			pdo_insert(GetTableName('behaviorscorelog',false),$insertData);
		}
	}
	die(json_encode(array(
		'status' => true,
		'msg' => '提交成功'
	)));
}
include $this->template('web/behaviorscore');
?>