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
        
        //查询是否用户登录		
		$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $userid['id']));
		$tid_global = $it['tid'];
		$teacher = pdo_fetch("SELECT status,fz_id FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $it['tid']));
		$teacher = pdo_fetch("SELECT status,fz_id FROM " . tablename($this->table_teachers) . " where id = '{$tid_global}' ");
		if($teacher['status'] == 2){
			$scoreOb  = pdo_fetchall("SELECT sname,sid,parentid FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'tscoreobject' And schoolid = {$schoolid} And parentid != 0  ORDER BY sid ASC");
			$scoreObPa  = pdo_fetchall("SELECT sname,sid FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'tscoreobject' And schoolid = {$schoolid} And parentid = 0  ORDER BY sid ASC");
			$limit = 0 ;
		}else{
			$scoreOb  = pdo_fetchall("SELECT sname,sid,parentid FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'tscoreobject' And schoolid = {$schoolid} And parentid != 0 and fzid = '{$teacher['fz_id']}'  ORDER BY sid ASC");
			$scoreObPa  = pdo_fetchall("SELECT sname,sid FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'tscoreobject' And schoolid = {$schoolid} And parentid = 0 and fzid = '{$teacher['fz_id']}'  ORDER BY sid ASC");
			$limit = 1 ;
		}
		
		$count_pa = count($scoreObPa) + 3;
		$width = 100/$count_pa;
		
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
		
		if(!empty($_GPC['score_time'])){
			$score_near_time = $_GPC['score_time'];
		}else{
			$score_near_time = pdo_fetchcolumn("SELECT scoretime FROM " . tablename($this->table_teascore) . " WHERE schoolid = '{$schoolid}' $condition and scoretime != 0 and type = 0  order by scoretime DESC");
		}
		
		
		if (!(IsHasQx($tid_global,2000601,2,$schoolid))){
			message('您无权查看本页面');
		}


		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
        if(!empty($userid['id'])){
			$score_list = pdo_fetchall("SELECT distinct scoretime FROM " . tablename($this->table_teascore) . " WHERE schoolid = '{$schoolid}' $condition and scoretime != 0  and type = 0 order by scoretime DESC ");
			$thistime = trim($_GPC['limit']);
			//var_dump($thistime);
			$score_tea_list_all = pdo_fetchall("SELECT distinct tid FROM " . tablename($this->table_teascore) . " WHERE schoolid = '{$schoolid}' $condition  and scoretime = '{$score_near_time}' and type = 0 order by tid DESC  ",array(),'tid');
			$tea_count = count($score_tea_list_all);	
			if($_GPC['op'] == 'scroll'){

				$score_tea_list = pdo_fetchall("SELECT distinct tid FROM " . tablename($this->table_teascore) . " WHERE schoolid = '{$schoolid}' $condition  and scoretime = '{$score_near_time}' and type = 0  and tid < '{$thistime}' order by tid DESC limit 0,10 ",array(),'tid');
				foreach($score_tea_list as $key_s_t=>$value_s_t){
					$teacher_this = pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $value_s_t['tid']));
					$score_tea_list[$key_s_t]['tname'] = $teacher_this['tname'];
					foreach($scoreObPa as $key_p=>$value_p){
						$score_tea_list[$key_s_t][$value_p['sname']] = pdo_fetchcolumn("SELECT sum(score) as allscore FROM " . tablename($this->table_teascore) . " WHERE schoolid = '{$schoolid}' and parentobid = '{$value_p['sid']}'  and scoretime = '{$score_near_time}' and tid = '{$value_s_t['tid']}' ");
						$score_tea_list[$key_s_t]['all'] = pdo_fetchcolumn("SELECT sum(score) as allscore FROM " . tablename($this->table_teascore) . " WHERE schoolid = '{$schoolid}'  and tid = '{$value_s_t['tid']}'  and scoretime = '{$score_near_time}' ");
					}
				}
				include $this->template('comtool/tscoreall');
			}else{ 
				$score_tea_list = pdo_fetchall("SELECT distinct tid FROM " . tablename($this->table_teascore) . " WHERE schoolid = '{$schoolid}' $condition  and scoretime = '{$score_near_time}' order by tid DESC LIMIT 0,10 ",array(),'tid');
				foreach($score_tea_list as $key_s_t=>$value_s_t){
					$teacher_this = pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $value_s_t['tid']));
					$score_tea_list[$key_s_t]['tname'] = $teacher_this['tname'];
					foreach($scoreObPa as $key_p=>$value_p){
						$score_tea_list[$key_s_t][$value_p['sname']] = pdo_fetchcolumn("SELECT sum(score) as allscore FROM " . tablename($this->table_teascore) . " WHERE schoolid = '{$schoolid}' and parentobid = '{$value_p['sid']}'  and scoretime = '{$score_near_time}' and tid = '{$value_s_t['tid']}' ");
						$score_tea_list[$key_s_t]['all'] = pdo_fetchcolumn("SELECT sum(score) as allscore FROM " . tablename($this->table_teascore) . " WHERE schoolid = '{$schoolid}'  and tid = '{$value_s_t['tid']}'  and scoretime = '{$score_near_time}' ");
					}
				}
				
				include $this->template(''.$school['style3'].'/tscoreall');
			} 
			 	/* $score_tea_list = pdo_fetchall("SELECT distinct tid FROM " . tablename($this->table_teascore) . " WHERE schoolid = '{$schoolid}' $condition  and scoretime = '{$score_near_time}' and type = 0 order by tid DESC  ",array(),'tid');
				$tea_count = count($score_tea_list);
				foreach($score_tea_list as $key_s_t=>$value_s_t){
					$teacher_this = pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $value_s_t['tid']));
					$score_tea_list[$key_s_t]['tname'] = $teacher_this['tname'];
					foreach($scoreObPa as $key_p=>$value_p){
						$score_tea_list[$key_s_t][$value_p['sname']] = pdo_fetchcolumn("SELECT sum(score) as allscore FROM " . tablename($this->table_teascore) . " WHERE schoolid = '{$schoolid}' and parentobid = '{$value_p['sid']}'  and scoretime = '{$score_near_time}' and type = 0 and tid = '{$value_s_t['tid']}' ");
						$score_tea_list[$key_s_t]['all'] = pdo_fetchcolumn("SELECT sum(score) as allscore FROM " . tablename($this->table_teascore) . " WHERE schoolid = '{$schoolid}' and type = 0  and tid = '{$value_s_t['tid']}'  and scoretime = '{$score_near_time}' ");
					}

				}
				include $this->template(''.$school['style3'].'/tscoreall'); 
			 */
			
        }else{
			session_destroy();
            $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
        }        
?>