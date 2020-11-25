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
		mload()->model('tea');
		$xq_list = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = '{$weid}' AND schoolid ={$schoolid} And type = 'xq_score' ORDER BY ssort DESC");
        //查询是否用户登录		
		$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $userid['id']));
		$tid_global = $it['tid'];
		
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
        if(!empty($userid['id'])){
			$bjlists = GetAllClassInfoByTid($schoolid,2,false,$tid_global);
			//$bjlists = get_mylist($schoolid,$it['tid'],'teacher');	
			//var_dump($bjlists);
			//获取首次进入时的班级
			
			
			
			$bj_t = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type and is_over!=:is_over ORDER BY CONVERT(sname USING gbk) ASC", array(':weid' => $weid, ':type' => 'theclass', ':schoolid' => $schoolid,':is_over'=>"2"));
			$nj_t = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type and is_over!=:is_over ORDER BY CONVERT(sname USING gbk) ASC", array(':weid' => $weid, ':type' => 'semester', ':schoolid' => $schoolid,':is_over'=>"2"));
			$bj_str_temp = '0,';
			foreach($bj_t as $key_b=>$value_b){
				$bj_str_temp .=$value_b['sid'].",";
			}
			$bj_str = trim($bj_str_temp,",");
			$nj_str_temp = '0,';
			foreach($nj_t as $key_n=>$value_n){
				$nj_str_temp .=$value_n['sid'].",";
			}
			$nj_str = trim($nj_str_temp,",");
			
			
			if(is_njzr($teachers['id'])){
				$myfisrtnj =  pdo_fetch("SELECT sid FROM " . tablename($this->table_classify) . " where weid = '{$weid}' And schoolid = '{$schoolid}' And tid = '{$it['tid']}' And type = 'semester' and is_over != 2");
				$fisrtbj =  pdo_fetch("SELECT sid as bj_id FROM " . tablename($this->table_classify) . " where weid = '{$weid}' And schoolid = '{$schoolid}' And parentid = '{$myfisrtnj['sid']}' and is_over != 2");
			}else{
				$fisrtbj =  pdo_fetch("SELECT bj_id FROM " . tablename($this->table_class) . " where weid = '{$weid}' And schoolid = '{$schoolid}'  And tid = {$it['tid']} and FIND_IN_SET(bj_id,'{$bj_str}') ");
				if($teachers['status'] == 2){
					$fisrtbj =  pdo_fetch("SELECT sid as bj_id FROM " . tablename($this->table_classify) . " where weid = '{$weid}' And schoolid = '{$schoolid}' And type = 'theclass' and is_over != 2 ");
				}			
			}
			//var_dump($fisrtbj);
			//接收选择的班级id
			if(!empty($_GPC['bj_id'])){
				$bj_id = intval($_GPC['bj_id']);			
			}else{
				$bj_id = $fisrtbj['bj_id'];
			}
			
			$bjinfo =  pdo_fetch("SELECT * FROM " . tablename($this->table_classify) . " where weid = '{$weid}' And schoolid = '{$schoolid}' And type = 'theclass' and sid = '{$bj_id}' ");
		
			if(!empty($this_xueqi)){
				$xueqi = pdo_fetch("SELECT * FROM ".tablename($this->table_classify)." WHERE schoolid = '{$schoolid}' and sid = '{$this_xueqi}' and type='xq_score' ");
			}else{
				$time = time();
				$xueqi = pdo_fetch("SELECT * FROM ".tablename($this->table_classify)." WHERE schoolid = '{$schoolid}' and sd_start <= '{$time}' and sd_end >= '{$time}' and type='xq_score' ");
			}
			$this_xueqi = $xueqi['sid'];
			$score_list = pdo_fetchall("SELECT sum(score) as all_score,bj_id,nj_id,sid FROM ".tablename($this->table_teascore)." WHERE schoolid='{$schoolid}' and tid = 0  and scoretime >='{$xueqi['sd_start']}' and scoretime <='{$xueqi['sd_end']}'and bj_id = '{$bj_id}' and type = 1 group by sid order by all_score DESC ");
			$numOfStu = count($score_list);
			foreach($score_list as $key_s=>$value_s){
				$stuinfo = pdo_fetch("SELECT s_name,icon FROM ". tablename($this->table_students) ." where schoolid = '{$schoolid}' and id = '{$value_s['sid']}' ");
				$score_list[$key_s]['stuinfo'] = $stuinfo;
				$count_before = pdo_fetchall(" select sid  FROM " . tablename($this->table_teascore) . "  where  bj_id = '{$value_s['bj_id']}' AND scoretime >='{$xueqi['sd_start']}' AND scoretime <='{$xueqi['sd_end']}'  AND schoolid = '{$schoolid}'  group by sid   HAVING SUM(score)>'{$value_s['all_score']}'    " );  
				$bj_rank_all = count($count_before)+1;
				$score_list[$key_s]['bj_rank'] = $bj_rank_all;
				$count_before_nj = pdo_fetchall(" select sid  FROM " . tablename($this->table_teascore) . "  where  nj_id = '{$value_s['nj_id']}' AND scoretime >='{$xueqi['sd_start']}' AND scoretime <='{$xueqi['sd_end']}'  AND schoolid = '{$schoolid}'  group by sid   HAVING SUM(score)>'{$value_s['all_score']}'     " );  
				$nj_rank_all = count($count_before_nj)+1;
				$score_list[$key_s]['nj_rank'] = $nj_rank_all;
			}
			//var_dump($score_list);
			
			

			include $this->template(''.$school['style3'].'/tstuscore');
        }else{
			session_destroy();
            $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
        }        
?>