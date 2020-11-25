<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */

global $_GPC, $_W;
$weid = $_W['uniacid'];
$action = 'new_manger_apps';
$this1 = 'no1';
$GLOBALS['frames'] = $this->getNaveMenu('',$action,true);
$logo = pdo_fetch("SELECT a.uniacid as weid ,a.name as title FROM " . tablename('uni_account') . " as a LEFT JOIN". tablename('account'). " as b ON a.uniacid = b.uniacid AND a.default_acid = b.acid where b.isdeleted != 1 And a.default_acid != 0 AND a.uniacid = {$weid}");
$member = pdo_fetch("SELECT username FROM " . tablename('users') . " WHERE uid = {$_W['uid']}");
$logo['logo'] = tomedia('headimg_'.$weid.'.jpg');
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$sale = sale();
$bigdata = bigdata();
if ($operation == 'display') {
	$area = pdo_fetchall("SELECT id,name FROM " . GetTableName('area') . " where weid = '{$weid}' And type = '' ORDER BY ssort DESC");
	$schooltype = pdo_fetchall("SELECT id,name FROM " . GetTableName('type') . " where weid = '{$weid}' ORDER BY ssort DESC");
	include $this->template ( 'web/new_manger_apps' );
}elseif($operation == 'get_schoolist'){
	$app = $_GPC['app'];
	$condtion = '';
	if (!empty($_GPC['areaid'])) {
		$areaid = $_GPC['areaid'];
		$condition .= " AND areaid = '{$areaid}'";
	}
	if (!empty($_GPC['typeid'])) {
		$typeid = $_GPC['typeid'];
		$condition .= " AND typeid = '{$typeid}'";
	}
	if (!empty($_GPC['keyword'])) {
		$condition .= " AND title LIKE '%{$_GPC['keyword']}%'";
	}
	$list = pdo_fetchall("SELECT id,title,logo FROM " . GetTableName('index') . " WHERE weid = '{$weid}' $condition ORDER BY ssort DESC");
	foreach($list as $key =>$row){
		$list[$key]['app'] = false;
		if($app == 'bigdata'){
			$check_app = pdo_fetch("SELECT is_bigdata FROM " . GetTableName('schoolset') . " WHERE weid = '{$weid}' And schoolid = '{$row['id']}' ");
			if($check_app['is_bigdata'] == 1){
				$list[$key]['app'] = true;
			}
		}else{
			$check_app = pdo_fetch("SELECT * FROM " . GetTableName('app') . " WHERE weid = '{$weid}' And schoolid = '{$row['id']}' ");
			if($check_app[$app] == 1){
				$list[$key]['app'] = true;
			}	
		}
		$list[$key]['logo'] = tomedia($row['logo']);
	}
	if($list){
		$result['list'] = $list;
		$result['msg'] = '获取成功';
		$result['result'] = true;
	}else{
		$result['msg'] = '获取失败';
		$result['result'] = false;
	}
	$result['lsistt'] = $_GPC['schoolist'];
	die(json_encode($result));
}elseif($operation == 'get_schoolists'){
	$result['result'] = true;
	$result['data'] = check_sales($sale);
	die(json_encode($result));
}elseif($operation == 'set_to_school'){
	$app = trim($_GPC['app']);
	$schoolist = $_GPC['schoolist'];
	$nub = 0;
	$qx = 0;
	if($app == 'bigdata'){
		$allapps = pdo_fetchall("SELECT id,schoolid FROM " . GetTableName('schoolset') . " WHERE weid = '{$weid}' And is_bigdata = 1");
		foreach($allapps as $r){
			if(!in_array($r['schoolid'],$schoolist)){
				$insert = array();
				$insert['is_bigdata'] = 0;
				pdo_update( GetTableName('schoolset',false), $insert , array('id' => $r['id']));
				$qx++;
			}
		}
		foreach($schoolist as $row){
			if($row){
				$insert = array(
					'weid' 		=> $weid,
					'schoolid'  => $row,
				);
				$insert['is_bigdata'] = 1;
				$check_app = pdo_fetch("SELECT id FROM " . GetTableName('schoolset') . " WHERE weid = '{$weid}' And schoolid = '{$row}' ");
				if($check_app){
					pdo_update( GetTableName('schoolset',false), $insert , array('id' => $check_app['id']));
					$nub++;
				}else{
					pdo_insert( GetTableName('schoolset',false), $insert);
					$nub++;
				}
			}
		}
	}else{
		$check_apps = check_apps($app);
		$allapps = pdo_fetchall("SELECT id,schoolid FROM " . GetTableName('schoolset') . " WHERE weid = '{$weid}' And  {$app} = 1");
		foreach($allapps as $r){
			if(!in_array($r['schoolid'],$schoolist)){
				$insert = array();
				$insert[$app] = 0;
				pdo_update( GetTableName('app',false), $insert , array('id' => $r['id']));
				$qx++;
			}
		}
		foreach($schoolist as $row){
			if($row && $check_apps){
				$insert = array(
					'weid' 		=> $weid,
					'schoolid'  => $row,
					'createtime'  => time()
				);
				$insert[$app] = 1;
				$check_app = pdo_fetch("SELECT id FROM " . GetTableName('app') . " WHERE weid = '{$weid}' And schoolid = '{$row}' ");
				if($check_app){
					unset($insert['createtime']);
					pdo_update( GetTableName('app',false), $insert , array('id' => $check_app['id']));
					$nub++;
				}else{
					pdo_insert( GetTableName('app',false), $insert);
					$nub++;
				}
			}
		}
	}
	if($qx != 0){
		$result['msg'] = "成功部署{$nub}个学校,取消{$qx}个";
	}else{
		$result['msg'] = "成功部署{$nub}个学校";
	}
	$result['result'] = true;
	die(json_encode($result));	
}else{
    // message('操作失败, 非法访问.');
    $this->imessage('操作失败, 非法访问', $this->createWebUrl('new_manager_apps', array('op' => 'display')), 'error');
}			
		
  
?>