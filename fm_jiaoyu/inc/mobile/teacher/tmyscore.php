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
        
        //查询是否用户登录		
		$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $userid['id']));
		$tid_global = $it['tid'];
		if (!(IsHasQx($tid_global,2000601,2,$schoolid))){
			message('您无权查看本页面');
		}
		
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
        if(!empty($userid['id'])){
			
			
			$this_tea = pdo_fetch("SELECT status,fz_id FROM " . tablename($this->table_teachers) . " where id = '{$tid_global}' ");
			if($this_tea['status'] == 2){
				$scoreOb  = pdo_fetchall("SELECT sname,sid,parentid FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'tscoreobject' And schoolid = {$schoolid} And parentid != 0  ORDER BY sid ASC");
				$limit = 0 ;
			}else{
				$scoreOb  = pdo_fetchall("SELECT sname,sid,parentid FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'tscoreobject' And schoolid = {$schoolid} And parentid != 0 and fzid = '{$this_tea['fz_id']}'  ORDER BY sid ASC");
				$limit = 1 ;
			}
			
			if($limit == 0 ){
				$condition = '';
				$condition1 = '';				
			}elseif($limit == 1){
				$ob_str = '';
				foreach($scoreOb as $key_s=>$value_s){
					$ob_str .= $value_s['sid'].","; 
				}
				$ob_str = trim($ob_str,",");
				$condition = "and FIND_IN_SET(obid,'{$ob_str}')";
				$condition1 = "and FIND_IN_SET(teascore.obid,'{$ob_str}')";	
			}
			
			if($_GPC['mananger'] == 'mananger'){
				$tid_this = $_GPC['tid'];
				$score_list = pdo_fetchall("SELECT distinct scoretime FROM " . tablename($this->table_teascore) . " WHERE schoolid = '{$schoolid}' $condition and scoretime != 0 and type = 0  order by scoretime DESC");
				
				$score_tea_list_all = pdo_fetchall("SELECT teascore.tid,teachers.tname,teachers.thumb FROM " . tablename($this->table_teascore) . " as teascore, " . tablename($this->table_teachers) . " as teachers WHERE teascore.schoolid = '{$schoolid}' $condition1  and teascore.scoretime = '{$_GPC['score_time']}' and teascore.tid = teachers.id and teascore.type = 0 group by teascore.tid  order by teascore.tid DESC");
			}else{
				if($_GPC['tid']){
					$tid_this = $_GPC['tid'];
				}else{
					$tid_this = $tid_global;
				}
				
				$score_list = pdo_fetchall("SELECT distinct scoretime FROM " . tablename($this->table_teascore) . " WHERE tid = :tid and scoretime != 0 and type = 0  order by scoretime DESC", array(':tid' => $tid_this));
			}
			
			if(!empty($_GPC['score_time'])){
				$score_near_time = $_GPC['score_time'];
			}else{
				$score_near_time = pdo_fetchcolumn("SELECT scoretime FROM " . tablename($this->table_teascore) . " WHERE tid = :tid and schoolid = :schoolid and type = 0 order by scoretime DESC", array(':tid' => $tid_this,':schoolid'=>$schoolid));
			}
			$teachers = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $tid_this));
			$score_pa = pdo_fetchall("SELECT sname,sid FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'tscoreobject' And schoolid = {$schoolid} And parentid = 0  ORDER BY sid ASC",array(),'sid');
			$count = count($score_pa);
			$width = 100/$count;
			
			$score_this_all = pdo_fetchcolumn("SELECT sum(score) as allscore FROM " . tablename($this->table_teascore) . " WHERE tid ='{$tid_this}' and scoretime = '{$score_near_time}' and type = 0 ");
			$score_this_pa = pdo_fetchall("SELECT sum(score) as allscore,parentobid FROM " . tablename($this->table_teascore) . " WHERE tid ='{$tid_this}' and scoretime = '{$score_near_time}' and type = 0 group by parentobid order by parentobid ASC",array(),'parentobid');
			
			$score_detail =  pdo_fetchall("SELECT * FROM " . tablename($this->table_teascore) . " WHERE tid ='{$tid_this}' and scoretime = '{$score_near_time}' and type = 0  order by obid ASC");
			foreach($score_detail as $key_s=>$value_s){
				$score_detail[$key_s]['obsname'] = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where sid = '{$value_s['obid']}'")['sname'];
				$score_detail[$key_s]['obicon'] = pdo_fetch("SELECT icon FROM " . tablename($this->table_classify) . " where sid = '{$value_s['obid']}'")['icon'] ? pdo_fetch("SELECT icon FROM " . tablename($this->table_classify) . " where sid = '{$value_s['obid']}'")['icon'] : $school['icon'] ;
				$score_detail[$key_s]['paobsname'] = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where sid = '{$value_s['parentobid']}'")['sname'];
				
				
			}
			//var_dump($score_detail);

			include $this->template(''.$school['style3'].'/tmyscore');
        }else{
			session_destroy();
            $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
        }        
?>