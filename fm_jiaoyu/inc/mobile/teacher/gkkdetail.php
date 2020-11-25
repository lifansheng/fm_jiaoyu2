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
		$gkkid = $_GPC['gkkid'];
		$is_bd ="yes" ;
		
        //查询是否用户登录		
		$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');	
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ORDER BY id DESC", array(':weid' => $weid, ':id' => $userid['id']));	
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ORDER BY ssort DESC", array(':weid' => $weid, ':id' => $schoolid));
	
		$school['is_showew'] = 2 ;


		  if(empty($userid['id'])){
			$is_bd ="no" ;
        }	


			$gkkinfo = pdo_fetch("SELECT * FROM " . tablename($this->table_gongkaike) . " where weid = '{$weid}' And schoolid = '{$schoolid}' And id='{$gkkid}'   ");
			$checkpj = pdo_fetch("SELECT id FROM " . tablename($this->table_gkkpj) . " where  gkkid = :gkkid And schoolid=:schoolid ", array(':gkkid' => $gkkid,':schoolid' => $schoolid));
			$qrinfo = pdo_fetch("SELECT show_url FROM " . tablename($this->table_qrinfo) . " where weid = '{$weid}'  And id='{$gkkinfo['qrid']}'   ");	
			


				$banji = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where sid = :sid And schoolid = :schoolid ", array(':schoolid' => $schoolid,':sid' => $gkkinfo['bj_id']));
				$nianji = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where sid = :sid And schoolid = :schoolid ", array(':schoolid' => $schoolid,':sid' => $gkkinfo['xq_id']));
				$teach = pdo_fetch("SELECT status,thumb,tname FROM " . tablename($this->table_teachers) . " where id = :id ", array(':id' => $gkkinfo['tid']));
				$kemu = pdo_fetch("SELECT sname,icon FROM " . tablename($this->table_classify) . " where sid = :sid And schoolid = :schoolid ", array(':schoolid' => $schoolid,':sid' => $gkkinfo['km_id']));
				
				$gkkinfo['kmname'] = $kemu['sname'];
				$gkkinfo['kmicon'] = empty($kemu['icon']) ? $school['logo'] : $kemu['icon'];
				$gkkinfo['banji'] = $banji['sname'];
				$gkkinfo['nianji'] = $nianji['sname'];
				$gkkinfo['tname'] = $teach['tname'];
				$gkkinfo['t_thumb'] = $teach['thumb'];
				$gkkinfo['qr_url'] = $qrinfo['show_url'];
				
		$sharetitle ="欢迎旁听".$gkkinfo['tname']."的公开课:". $gkkinfo['name'];
		$sharedesc = "欢迎旁听".$gkkinfo['tname']."的公开课";
		$shareimgUrl = tomedia($gkkinfo['t_thumb']);
		$links = $_W['siteroot'] .'app/'.$this->createMobileUrl('gkkdetail', array('schoolid' => $schoolid,'gkkid' => $gkkid));

			include $this->template(''.$school['style3'].'/gkkdetail');	

	        
	
				
						        		
             
?>