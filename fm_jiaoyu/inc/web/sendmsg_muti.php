<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
	global $_GPC,$_W;
	$notice_id = $_GPC['notice_id'];
	$schoolid = $_GPC['schoolid'];
	$weid = $_GPC['weid'];
	$tname = $_GPC['tname'];
	$type = $_GPC['type'];
	$pindex = max(1, intval($_GPC['page']));
	$psize = 2;
	$schooltype  =GetSchoolType($schoolid,$weid); //培训模式的群发还没有做完
	// $schooltype = false ;
	//班级通知
	if($type == 1){
		if($schooltype == true){
			$kc_id = $_GPC['kc_id'];
			$pindex = max(1, intval($_GPC['page']));
			// $total = pdo_fetchall("SELECT distinct sid FROM ".tablename($this->table_order)." where weid = :weid And schoolid = :schoolid And kcid = :kc_id and type = 1 and status = 2 ",array(':weid'=>$weid, ':schoolid'=>$schoolid, ':kc_id'=>$kc_id));
			$total = pdo_fetchcolumn("SELECT count(distinct sid ) FROM ".tablename($this->table_order)." where weid = :weid And schoolid = :schoolid And kcid = :kc_id and type = 1 and status = 2 ",array(':weid'=>$weid, ':schoolid'=>$schoolid, ':kc_id'=>$kc_id));
		}elseif($schooltype == false){
			$bj_id = $_GPC['bj_id'];
			$pindex = max(1, intval($_GPC['page']));
			if (is_array($bj_id)) {
				$mutiBj_id = 1 ;
				$from = $_GPC['from'];
				foreach( $bj_id as $key => $value )
				{
					$temp_bj .= $value.",";
				}
				$bj_id_fin = trim($temp_bj,",");
				//var_dump($bj_id_fin);
				$total = pdo_fetchcolumn("SELECT count(distinct id ) FROM ".tablename($this->table_students)." where weid = :weid And schoolid = :schoolid And FIND_IN_SET(bj_id,:bj_id)",array(':weid'=>$weid, ':schoolid'=>$schoolid, ':bj_id'=>$bj_id_fin));
				$bj_id = json_encode($bj_id);
			}else{
				$total = pdo_fetchcolumn("SELECT COUNT(1) FROM ".tablename($this->table_students)." where weid = :weid And schoolid = :schoolid And bj_id = :bj_id",array(':weid'=>$weid, ':schoolid'=>$schoolid, ':bj_id'=>$bj_id));
			}
			$tp = ceil($total/$psize);
		}
	
	//校园通知
	}elseif($type == 2){
		$groupid = $_GPC['groupid'];
		if ($groupid >= 4) {
			$total = pdo_fetchcolumn("SELECT count(id) FROM ".tablename($this->table_teachers)." where weid = :weid And schoolid = :schoolid And fz_id = :fz_id " ,array(':weid'=>$weid, ':schoolid'=>$schoolid, ':fz_id'=>$groupid));	
		}else{		
			if ($groupid == 1) {
				$total = pdo_fetchcolumn("SELECT COUNT(1) FROM ".tablename($this->table_user)." where weid = :weid And schoolid = :schoolid",array(':weid'=>$weid, ':schoolid'=>$schoolid));
			}
			if ($groupid == 2) {
				$total = pdo_fetchcolumn("SELECT COUNT(1) FROM ".tablename($this->table_teachers)." where weid = :weid And schoolid = :schoolid",array(':weid'=>$weid, ':schoolid'=>$schoolid));
			}
			if ($groupid == 3) {
				$total = pdo_fetchcolumn("SELECT COUNT(1) FROM ".tablename($this->table_students)." where weid = :weid And schoolid = :schoolid",array(':weid'=>$weid, ':schoolid'=>$schoolid));
			}
		}
		$tp = ceil($total/$psize);
		//作业群发
	}elseif($type == 3){
		if($schooltype == true){
			$kc_id = $_GPC['kc_id'];
			$pindex = max(1, intval($_GPC['page']));
			$total = $_GPC['total'];
			// $total = pdo_fetchall("SELECT distinct sid FROM ".tablename($this->table_order)." where weid = :weid And schoolid = :schoolid And kcid = :kc_id and type = 1 and status = 2 ",array(':weid'=>$weid, ':schoolid'=>$schoolid, ':kc_id'=>$kc_id));
			include $this->template('web/sendmsg_px_muti');
		}elseif($schooltype == false){
			$bj_id = $_GPC['bj_id'];
			$pindex = max(1, intval($_GPC['page']));
			$total = pdo_fetchcolumn("SELECT COUNT(1) FROM ".tablename($this->table_students)." where weid = :weid And schoolid = :schoolid And bj_id = :bj_id",array(':weid'=>$weid, ':schoolid'=>$schoolid, ':bj_id'=>$bj_id));
			$tp = ceil($total/$psize);
			include $this->template('web/sendmsg_muti');
		}
	}
	include $this->template('web/sendmsg_muti');
?>
