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
	$time = $_GPC['time'];
	$this_xueqi = $_GPC['this_xueqi'];

	$xq_list = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = '{$weid}' AND schoolid ={$schoolid} And type = 'xq_score' ORDER BY ssort DESC");
	//查询是否用户登录		
	$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where schoolid = '{$schoolid}' And id = '{$_SESSION['user']}' ");
	$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $userid['id']));
	$tid_global = $it['tid'];
	$student = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where schoolid = '{$schoolid}' And id = '{$it['sid']}' ");
	
	$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
	if(!empty($userid['id'])){
		if(!empty($this_xueqi)){
			$xueqi = pdo_fetch("SELECT * FROM ".tablename($this->table_classify)." WHERE schoolid = '{$schoolid}' and sid = '{$this_xueqi}' and type='xq_score' ");
		}else{
			$time = time();
			$xueqi = pdo_fetch("SELECT * FROM ".tablename($this->table_classify)." WHERE schoolid = '{$schoolid}' and sd_start <= '{$time}' and sd_end >= '{$time}' and type='xq_score' ");
		}

		$score_list = pdo_fetchall("SELECT * FROM ".tablename($this->table_teascore)." WHERE schoolid='{$schoolid}' and sid = '{$it['sid']}' and scoretime >='{$xueqi['sd_start']}' and scoretime <='{$xueqi['sd_end']}' and type = 1 ");

		$all_score = 0 ;
		foreach($score_list as $key_s=>$value_s){
			$all_score += $value_s['score'];
			$bj_rank = pdo_fetch("select count(score)+1 as rank FROM " . tablename($this->table_teascore) . "  where score>'{$value_s['score']}' and  weid = '{$weid}' AND schoolid = '{$schoolid}' AND scoretime ='{$value_s['scoretime']}' and bj_id = '{$value_s['bj_id']}' ");
			$nj_rank = pdo_fetch("select count(score)+1 as rank FROM " . tablename($this->table_teascore) . "  where score>'{$value_s['score']}' and  weid = '{$weid}' AND schoolid = '{$schoolid}' AND scoretime ='{$value_s['scoretime']}' and nj_id = '{$value_s['nj_id']}' ");
			$score_list[$key_s]['bj_rank'] = $bj_rank['rank'];
			$score_list[$key_s]['nj_rank'] = $nj_rank['rank'];
		}

		$count_before = pdo_fetchall(" select sid  FROM " . tablename($this->table_teascore) . "  where  bj_id = '{$student['bj_id']}' AND scoretime >='{$xueqi['sd_start']}' AND scoretime <='{$xueqi['sd_end']}'  AND schoolid = '{$schoolid}'  group by sid   HAVING SUM(score)>'{$all_score}'    " ); 
		$bj_rank_all = count($count_before)+1;

		$count_before_nj = pdo_fetchall(" select sid  FROM " . tablename($this->table_teascore) . "  where  nj_id = '{$student['xq_id']}' AND scoretime >='{$xueqi['sd_start']}' AND scoretime <='{$xueqi['sd_end']}'  AND schoolid = '{$schoolid}'  group by sid   HAVING SUM(score)>'{$all_score}'     " );  
		$nj_rank_all = count($count_before_nj)+1;

		include $this->template(''.$school['style2'].'/smyscore');
	}else{
		session_destroy();
		$stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
		header("location:$stopurl");
	}        
?>