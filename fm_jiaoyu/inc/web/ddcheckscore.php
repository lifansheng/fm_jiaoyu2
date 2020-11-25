<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_GPC, $_W;

$weid              = $_W['uniacid'];
$action            = 'ddscorelog';
$this1             = 'no2';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
$logo              = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$nj    = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid =:schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'semester', ':schoolid' => $schoolid));
$type = $_GPC['type'] ? $_GPC['type'] : 1 ;
$tid_global = $_W['tid'];
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if($operation == 'display'){
    
}elseif($operation == 'getbj'){ //获取班级列表
    mload()->model('kc');
    $bjlist = getBjList($_GPC['schoolid'],$_GPC['nj_id']);
    $result['data'] = $bjlist;
    die(json_encode($result));
}elseif($operation == 'getscorecategory'){ //获取评分项目
    $bjid = $_GPC['bjid'];
    if(is_numeric($tid_global)){
        $condition .= " AND FIND_IN_SET($tid_global,tid) ";
    }
    $bjcheck = pdo_fetchall("SELECT * FROM " . GetTableName('ddscorecategory') . " where schoolid = '{$_GPC['schoolid']}' And type = '1' AND (FIND_IN_SET('{$bjid}',bjidstr) OR FIND_IN_SET('{$bjid}',specialbjidstr)) $condition ORDER BY ssort DESC");

    $teacheck = pdo_fetchall("SELECT * FROM " . GetTableName('ddscorecategory') . " where schoolid = '{$_GPC['schoolid']}' And type = '2' AND (FIND_IN_SET('{$bjid}',bjidstr) OR FIND_IN_SET('{$bjid}',specialbjidstr)) $condition ORDER BY ssort DESC");
    $date = strtotime(date("Y-m-d",time()));
    $scoreLog = pdo_fetchall("SELECT * FROM ".GetTableName('ddscorelog')." WHERE schoolid = '{$schoolid}' and bjid = '{$bjid}' and date = '{$date}' ",array(),'cid');
    include $this->template('web/ddcheckscore_bot');
	die();
}elseif($operation == 'Save'){
	$Score = $_GPC['score'];
	$remark = $_GPC['remark'];
	// dd($remark);
	$bjid = $_GPC['bjid'];
	$date = strtotime(date("Y-m-d",time())); 
	foreach($Score as $cid => $value_s){
		$insertData = array(
			'weid' => $weid,
			'schoolid' => $schoolid,
			'bjid' => $bjid,
			'score' => $value_s,
			'remark' => $remark[$cid],
			'cid' => $cid,
			'createtime' => time(),
			'date' => $date,
			'tid' => intval($tid_global) <= 0 ? -1 : $tid_global 
		);
		$check = pdo_fetch("SELECT id FROM ".GetTableName('ddscorelog')." WHERE schoolid = '{$schoolid}' and bjid = '{$bjid}' and date = '{$date}' and cid = '{$cid}'");
		if(!empty($check)){
			pdo_update(GetTableName('ddscorelog',false),$insertData,array('id' => $check['id']));
		}else{
			pdo_insert(GetTableName('ddscorelog',false),$insertData);
		}
	}
	die(json_encode(array(
		'status' => true,
		'msg' => '提交成功'
	)));
}
include $this->template('web/ddcheckscore');
?>