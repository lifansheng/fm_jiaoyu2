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
		if ($_GPC['userid']){
			$_SESSION['user'] = intval($_GPC['userid']);
		}
		
		$userid =$_SESSION['user'];
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where id = :id ", array(':id' => $userid));	
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id", array(':weid' => $weid, ':id' => $schoolid));
		  if(!empty($userid)){
			
            $card = unserialize($school['cardset']);
			$teacher = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " where id = :id ", array(':id' =>$it['tid']));
			$userinfo = iunserializer($it['userinfo']);
			$list = pdo_fetchall("SELECT * FROM " . tablename($this->table_mallorder) . " where :schoolid = schoolid And :weid = weid and sid = :sid order by createtime DESC", array(
		         ':weid' => $weid,
				 ':schoolid' => $schoolid,
				'sid'    => $it['sid']
				 ));
			foreach($list as $key => $row){
				$good = pdo_fetch("SELECT * FROM ".tablename($this->table_mall)." WHERE id = '{$row['goodsid']}'");//课程
				$ct = pdo_fetch("SELECT * FROM ".tablename($this->table_cost)." WHERE id = '{$row['costid']}'");//项目
				
				$ddid = pdo_fetch("SELECT id FROM ".tablename($this->table_order)." WHERE morderid = '{$list[$key]['id']}'");
				$list[$key]['tname'] = $good['title'];
				$list[$key]['ddid'] = $ddid['id'];
				$list[$key]['ticon'] = $good['thumb'];
				$list[$key]['obname'] = $good['title'];
				$thumb = unserialize($good['thumb']);
				$list[$key]['obicon'] = $thumb[0];
				$list[$key]['price'] = $good['new_price'];
				$list[$key]['point'] = $good['points'];
				$list[$key]['allpoint'] = intval($good['points']) * intval($list[$key]['count']);
				$list[$key]['allprice'] = $good['new_price'] * intval($list[$key]['count']);
			}	
		    include $this->template(''.$school['style2'].'/sgetorder');
		}else{
         	session_destroy();
		    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
			exit;
		} 
		
	
?>