<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
        global $_W, $_GPC;
        $weid = $_W['uniacid'];
		$schoolid = intval($_GPC['schoolid']);
       $school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id", array(':weid' => $weid, ':id' => $schoolid));
	   $schooltype = $_W['schooltype'];
		//教师列表按教师入职时间先后顺序排列，先入职再前
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where id = :id ", array(':id' => $_SESSION['user']));
		if($it){
			$student = pdo_fetch("SELECT xq_id,bj_id FROM " . tablename($this->table_students) . " WHERE schoolid = :schoolid And id = :id", array(':schoolid' => $schoolid,':id' => $it['sid']));
			$bj_id = $student['bj_id'];
			if(!$schooltype){
				$list = pdo_fetchall("SELECT distinct(tid) FROM " . tablename($this->table_class) . " WHERE weid = :weid AND schoolid =:schoolid AND bj_id = :bj_id  ", array(
					':weid' => $weid,
					':schoolid' => $schoolid,
					':bj_id' =>$bj_id
				));	
				foreach($list as $key => $row){
					$teacher = pdo_fetch("SELECT tname,thumb FROM " . tablename($this->table_teachers) . " WHERE id = :id", array(':id' => $row['tid']));
					if(!empty($teacher)){
						$list[$key]['tname'] = $teacher['tname'];
						$list[$key]['thumb'] = $teacher['thumb'];
						$list[$key]['kemu'] = pdo_fetchall("SELECT km_id FROM " . tablename($this->table_class) . " WHERE schoolid = :schoolid AND tid =:tid AND bj_id = :bj_id ", array(':schoolid' => $schoolid,':tid' => $row['tid'],':bj_id' =>$bj_id));
						foreach($list[$key]['kemu'] as $k => $r){
							$kemunam = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE sid = :sid", array(':sid' => $r['km_id']));
							$list[$key]['kemu'][$k]['kemus'] = $kemunam['sname'];
						}
					}
				}	
					
			}elseif($schooltype){
				$bmorder = pdo_fetchall("SELECT DISTINCT kcid FROM " . tablename($this->table_order) . " where sid = '{$it['sid']}' AND type =1 and status = 2 ");
				$bm_str_t = '';
				foreach($bmorder as $key=>$value){
					$bm_str_t .=$value['kcid'].",";
				}
				$bm_str = trim($bm_str_t,",");
				$list_t = pdo_fetchall("SELECT tid,name FROM " . tablename($this->table_tcourse) . " WHERE weid = '{$weid}' AND schoolid ='{$schoolid}' AND FIND_IN_SET(id,'{$bm_str}')  ");
				$tid_t = '	';
				foreach($list_t as $ke_t=>$value_t){
					$tid_t .=$value_t['tid'].",";
				}
				$tid_t = trim($tid_t);
				$tid_str = trim($tid_t,",");
				$tid_arr =array_unique(explode(',', $tid_str));
				$list = array();
				if(!empty($tid_arr)){
				foreach($tid_arr as $key_ta=>$value_ta){
						$list[$key_ta] = pdo_fetch("SELECT tname,thumb,id as tid FROM " . tablename($this->table_teachers) . " WHERE id = :id", array(':id' => $value_ta));
						$list[$key_ta]['ttt'] = $value_ta;
						$list[$key_ta]['kemu'] =  pdo_fetchall("SELECT id,name as kemus  FROM " . tablename($this->table_tcourse) . " WHERE schoolid = '{$schoolid}' And ( tid like '%,{$value_ta},%' or  tid like '{$value_ta},%' or tid like '%,{$value_ta}' or tid = {$value_ta})   AND FIND_IN_SET(id,'{$bm_str}')");
					}
				}
			}
			$master2temp = pdo_fetchall("SELECT DISTINCT * FROM " . tablename($this->table_class) . " WHERE weid = :weid AND schoolid = :schoolid  AND bj_id =:bj_id ORDER BY id DESC", array(
				':weid' => $weid,
				':schoolid' => $schoolid,
				
				':bj_id' => $student['bj_id'],
			
			));
 
			$bjname = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where sid = :sid ", array(':sid' => $student['bj_id']));
			$xqname = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where sid = :sid ", array(':sid' => $student['xq_id']));	
 
			include $this->template(''.$school['style2'].'/mytecher');
		}else{
			session_destroy();
		    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
			exit;
		}
?>