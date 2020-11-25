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
		if ($_GPC['user']){
			$_SESSION['user'] = intval($_GPC['user']);
		}
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where id = :id ", array(':id' => $_SESSION['user']));	
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id", array(':weid' => $weid, ':id' => $schoolid));
		mload()->model('pay');
        if(!empty($it)){
			$rest = check_unpay($it['sid']);
            $card = unserialize($school['cardset']);
			$userinfo = array();
			$userinfo['name'] = $it['realname'];
			$userinfo['mobile'] = $it['mobile'];
			$list = pdo_fetchall("SELECT * FROM " . tablename($this->table_order) . " where :schoolid = schoolid And :weid = weid And :sid = sid ORDER BY createtime DESC", array(
		         ':weid' => $weid,
				 ':schoolid' => $schoolid,
				 ':sid' => $it['sid'],
				 ));
			foreach($list as $key => $row){
				if( $row['type'] == 8){
					$taocan =  pdo_fetch("SELECT * FROM " . tablename($this->table_chongzhi) . " WHERE schoolid ='{$row['schoolid']} And weid = {$row['weid']}'And id = {$row['taocanid']}");
					$list[$key]['chongzhi'] = $taocan['chongzhi'];
				}
				$kc = pdo_fetch("SELECT * FROM ".tablename($this->table_tcourse)." WHERE id = '{$row['kcid']}'");//课程
				$ct = pdo_fetch("SELECT * FROM ".tablename($this->table_cost)." WHERE id = '{$row['costid']}'");//项目
				$sp = pdo_fetch("SELECT * FROM ".tablename($this->table_allcamera)." WHERE id = '{$row['vodid']}'");//视频
				$ls = pdo_fetch("SELECT tname,thumb FROM ".tablename($this->table_teachers)." WHERE id = '{$kc['tid']}'");//老师
				$user = pdo_fetch("SELECT pard FROM ".tablename($this->table_user)." WHERE id = '{$row['userid']}'");
				$stu = pdo_fetch("SELECT s_name FROM ".tablename($this->table_students)." WHERE id = '{$row['sid']}'");
				$list[$key]['kcname'] = $kc['name'];
				$list[$key]['vodname'] = $sp['name'];
				$list[$key]['kcstart'] = $kc['start'];
				$list[$key]['kcend'] = $kc['end'];
				$list[$key]['adrr'] = $kc['adrr'];
				$list[$key]['minge'] = $kc['minge'];
				$list[$key]['yibao'] = $kc['yibao'];				
				$list[$key]['tname'] = $ls['tname'];
				$list[$key]['pard'] = $stu['s_name'].get_guanxi($user['pard']);
				if(!empty($ls['thumb']))
				{
					$list[$key]['ticon'] = $ls['thumb'];	
				}else{
					$list[$key]['ticon'] = $school['tpic'];
				}
				$list[$key]['ob_ison'] = $ct['is_on'];
				$list[$key]['obname'] = $ct['name'];
				$list[$key]['obicon'] = $ct['icon'];
				$list[$key]['obstart'] = $ct['starttime'];
				$list[$key]['obend'] = $ct['endtime'];
				$list[$key]['obtime'] = $ct['dataline'];
				$list[$key]['vodtime'] = $sp['days'];
				$list[$key]['is_time'] = $ct['is_time'];

				if($row['type'] == 20){
					$file =  pdo_fetch("SELECT thumb,title FROM " . GetTableName('growupfile') . " WHERE schoolid ='{$row['schoolid']} And weid = {$row['weid']}' And id = {$row['extra_info']}");
					$list[$key]['growupthumb'] = $file['thumb'];
					$list[$key]['growuptitle'] = $file['title'];
				}

			}	 
		    include $this->template(''.$school['style2'].'/order');
        }else{
			session_destroy();
		    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
			exit;
        }        
?>