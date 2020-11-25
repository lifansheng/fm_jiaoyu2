<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
	global $_W, $_GPC;
	$weid = $_W['uniacid'];
	$openid = $_W['openid'];	
	$schoolid = intval($_GPC['schoolid']);
	$userid = intval($_GPC['id']);
	if(!empty($userid)){
		$_SESSION['user'] = $userid;
	}
	$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
	$it = pdo_fetch("SELECT * FROM " . GetTableName('user') . " where id = :id ", array(':id' => $_SESSION['user']));
	$school = pdo_fetch("SELECT style2 FROM " . GetTableName('index') . " where id = :id ", array(':id' => $schoolid));
	$students = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where id = :id ", array(':id' => $it['sid']));	
	if($operation == 'tuan'){
		$saletype = 1;
		$saleword = '拼团';
	}
	if($operation == 'zhuli'){
		$saletype = 2;
		$saleword = '助力';
	}
	if($operation == 'tuig'){
		$saleword = '推广';
	}
	$list = pdo_fetchall("SELECT * FROM " . GetTableName('sale_team') . " where userid = '{$_SESSION['user']}' And type = '{$saletype}' And schoolid = '{$schoolid}' ORDER BY endtime DESC ");;
	foreach($list as $key => $row){
		$kcinfo = pdo_fetch("SELECT name,thumb,sale_id,sale_type,cose FROM " . GetTableName('tcourse') . " where id = '{$row['kcid']}' ");
		$saleset = pdo_fetch("SELECT price,allow_again FROM " . GetTableName('kc_saleset') . " WHERE id = :id ", array(':id' => $kcinfo['sale_id']));
		$list[$key]['icon'] = tomedia($kcinfo['thumb']);
		$list[$key]['cose'] = $kcinfo['cose'];
		$list[$key]['kcname'] = $kcinfo['name'];
		$list[$key]['price'] = $saleset['price'];
		$list[$key]['allow_again'] = $saleset['allow_again'];
		$list[$key]['rest'] = CheckTemIsrest($row['masterid']);
		$list[$key]['orderinfo'] = pdo_fetch("SELECT cose,status,team_price,team_dz_price,guigeid FROM " . GetTableName('order') . " where id = '{$row['orderid']}' ");
		$guige = pdo_fetch("SELECT price FROM " . GetTableName('guige') . " where id = '{$list[$key]['orderinfo']['guigeid']}' ");
		if($guige){
			$list[$key]['cose'] = $guige['price'];
		}
		if($list[$key]['rest'] == 0){
			$list[$key]['succes'] = 1;
			$list[$key]['texttip'] = $saleword.'成功';
			$list[$key]['end'] = '';
		}else{
			if($row['endtime'] > time()){
				$list[$key]['texttip'] = $saleword.'中，还差'.$list[$key]['rest'].'人成功';
				$list[$key]['end'] = date('Y/m/d H:i:s',$row['endtime']);
				$list[$key]['succes'] = 2;
			}else{
				$list[$key]['succes'] = 3;
				if($list[$key]['orderinfo']['status'] == 3){
					$tkword = ',已退款';
				}
				$list[$key]['texttip'] = $saleword.'失败'.$tkword;
				$list[$key]['end'] = '';
			}	
		}
	}
	if(!empty($it)){
		include $this->template(''.$school['style2'].'/mysaleinfo');
	}else{
		session_destroy();
		$stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
		header("location:$stopurl");
		exit;
	}
