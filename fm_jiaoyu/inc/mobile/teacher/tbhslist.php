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
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
		mload()->model('tea');
		$qhlist  = pdo_fetchall("SELECT sname,sid FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and type = 'xq_score' and is_show_qh = 1  ORDER BY sd_end DESC ");
		$qhid_now = pdo_fetch("SELECT sid FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and type = 'xq_score' and is_show_qh = 1  and sd_start < '".time()."' and sd_end > '".time()."' ")['sid'];
		$qhid_last = pdo_fetch("SELECT c.sid FROM ".GetTableName('classify')." as c , ".GetTableName('behaviorscorelog')." as b WHERE b.qhid = c.sid and c.schoolid = '{$schoolid}' and c.is_show_qh = 1  and c.type = 'xq_score' and b.tid = '{$tid_global}' ORDER BY  c.sd_end DESC ")['sid'];
		$qhid = $qhid_now ? $qhid_now : ( $qhid_last ? $qhid_last : $qhlist[0]['sid'] ) ;
		$op = $_GPC['op'] ? $_GPC['op'] : 'display';
		if($op == 'switchxq'){
			$qhid = $_GPC['id'];
			$tid = $_GPC['tid'];
			$bjlist = GetAllClassInfoByTid($schoolid,1,$_W['schooltype'],$tid);
			$ResData = [];
			$Alltotal = 0;
			$AllCountDone = 0 ;
			foreach ($bjlist as $key => $value) {
				$total = pdo_fetchcolumn("SELECT count(id) FROM ".GetTableName('students')." WHERE schoolid = '{$schoolid}' and bj_id = '{$value['sid']}' ");
				$countDone = pdo_fetchcolumn("SELECT count(distinct sid) FROM ".GetTableName('behaviorscorelog')." as b , ".GetTableName('students')." as s  WHERE b.sid = s.id and  b.schoolid = '{$schoolid}' and b.qhid = '{$qhid}' and b.tid = '{$tid}' and s.bj_id = '{$value['sid']}'   ");
				$Alltotal += $total;
				$AllCountDone += $countDone;
				 $ResData[] = array(
					 'sid' => $value['sid'],
					 'sname' => $value['old_sname'],
					 'total' => $total,
					 'cdone' => $countDone,
					 'wait' => $total - $countDone
				 );
			}
			die(json_encode(array(
				'bjdata' => $ResData,
				'alltotal' => $Alltotal,
				'allcdone' => $AllCountDone
			)));

		}elseif($op == 'display'){
			if(!empty($userid['id'])){
				if(!empty($qhlist)){
					$qhid = $qhlist[0]['sid'];
					if(!empty($_GPC['qhid'])){
						$qhid = $_GPC['qhid'];
					}
				}
				$bjlist = GetAllClassInfoByTid($schoolid,0,$_W['schooltype'],$tid_global);
				include $this->template(''.$school['style3'].'/tbhslist');
			}else{
				session_destroy();
				$stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
				header("location:$stopurl");
			}   
		}
		     
?>