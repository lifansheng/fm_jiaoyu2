<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */

global $_GPC, $_W;
$weid              = $_W['uniacid'];
$action1           = 'yuerefound';
$this1             = 'no4';
$action            = 'yuerefound';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
$logo              = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$operation         = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$tid_global = $_W['tid'];




if($operation =='display' && !(IsHasQx($tid_global,1004401,1,$schoolid))  && IsHasQx($tid_global,1004402,1,$schoolid) ){
	$operation = 'cardrefound';
}
 

if($operation =='cardrefound' && !(IsHasQx($tid_global,1004402,1,$schoolid)) && IsHasQx($tid_global,1004401,1,$schoolid) ){
    $operation = 'display';
}

if($operation =='display' && !(IsHasQx($tid_global,1004401,1,$schoolid)) ){
	$this->imessage('非法访问，您无权操作该页面','','error');	
}

if($operation =='cardrefound' && !(IsHasQx($tid_global,1004402,1,$schoolid)) ){
	$this->imessage('非法访问，您无权操作该页面','','error');	
}



if($operation == 'display'){
    $pindex    = max(1, intval($_GPC['page']));
    $psize     = 20;
    if(!empty($_GPC['stuname'])){
		$student = pdo_fetch("SELECT id FROM " . tablename($this->table_students) . " WHERE schoolid = :schoolid And s_name = :s_name ORDER BY id DESC LIMIT 1", array(':schoolid' => $schoolid,':s_name' => $_GPC['stuname']));
		$condition .= " AND sid = '{$student['id']}'";		
    }
	if(!empty($_GPC['createtime'])) {
		$starttime = strtotime($_GPC['createtime']['start']);
		$endtime   = strtotime($_GPC['createtime']['end']) + 86399;
		$condition .= " AND createtime > '{$starttime}' AND createtime < '{$endtime}'";
	} else {
		$starttime = strtotime('-600 day');
		$endtime = TIMESTAMP;
	}
	$list = pdo_fetchall(" SELECT * FROM ".GetTableName('yuecostlog')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' and yue_type = 2 and cost_type = 4 $condition ORDER BY createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
	foreach($list as $key => $row){
		$student = pdo_fetch("SELECT id,icon,s_name,bj_id,xq_id FROM " . tablename($this->table_students) . " WHERE schoolid = :schoolid And id = :id ", array(':schoolid' => $schoolid,':id' => $row['sid']));
		if(!empty($student['icon'])){
			$list[$key]['sicon'] = $student['icon'];
		}else{
			$list[$key]['sicon'] = $logo['spic']; 
		}
		if($row['cost_type'] == 4){
			if($row['cztid'] == -1){
				$list[$key]['cztname'] = "管理员";
			}else{
				$cztea = pdo_fetch("SELECT tname FROM ".GetTableName('teachers')." WHERE id = '{$row['cztid']}' ");
				$list[$key]['cztname'] = $cztea['tname'];
			}
		}
		$bj_name = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE schoolid = :schoolid And sid = :sid ", array(':schoolid' => $schoolid,':sid' => $student['bj_id']));
		$nj_name = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE schoolid = :schoolid And sid = :sid ", array(':schoolid' => $schoolid,':sid' => $student['xq_id']));
		$list[$key]['bj_name'] = $bj_name['sname'];
		$list[$key]['nj_name'] = $nj_name['sname'];	
		$list[$key]['s_name'] = $student['s_name'];
	}
	$total = pdo_fetchcolumn(" SELECT count(*) FROM ".GetTableName('yuecostlog')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' and yue_type = 2 and cost_type = 4  $condition ORDER BY createtime DESC ");
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/yuerefound');
}
elseif($operation == 'card_bot'){
	$card = $_GPC['stuCard'];
	$is_find = false;
	$card = pdo_fetch("SELECT * FROM ".GetTableName('idcard')." WHERE schoolid = '{$schoolid}' and  idcard = '{$card}' ");
	$students = pdo_fetch("SELECT * FROM ".GetTableName('students')." WHERE id = '{$card['sid']}' ");
	if(!empty($students)){
		$is_find = true;
		$students['njname'] = pdo_fetch('SELECT sname FROM ' . tablename($this->table_classify) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and type='semester' and sid = '{$students['xq_id']}'")['sname'];  
		$students['bjname'] = pdo_fetch('SELECT sname FROM ' . tablename($this->table_classify) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and type='theclass' and sid = '{$students['bj_id']}'")['sname'];  
	}

	include $this->template ( 'web/yuerefound_bot' );	
	die();
}elseif($operation == 'changecardrefound'){
	$refound = $_GPC['refound'];
	$stuid = $_GPC['stuid'];
	//$op    = $_GPC['dealop'];
	$student = pdo_fetch("SELECT * FROM ".GetTableName('students')." WHERE id = '{$stuid}' ");
	$old_chongzhi = $student['chongzhi'];

	  //增加余额
		$new_chongzhi = $old_chongzhi + $refound;
		$cost = $refound;

	//}

	$yuelogdata = array(
		'schoolid' => $schoolid,
		'weid' => $weid,
		'sid' => $stuid,
		'yue_type' => 2,
		'cost' => $cost,
		'cost_type' => 4,
		'createtime' => time(),
		'cztid' => intval($tid_global) >0 ? $tid_global : -1 
	);
	pdo_insert(GetTableName('yuecostlog',false),$yuelogdata);
	pdo_update(GetTableName('students',false),array('chongzhi'=>$new_chongzhi),array('id'=>$stuid));
	$result['result'] = true;
	$result['msg'] = "退款成功！";
	die(json_encode($result));
}
elseif($operation == "cardrefound"){
    include $this->template('web/yuerefound');
}
elseif($operation == "deleteall"){
 
    $rowcount    = 0;
    $notrowcount = 0;
    foreach($_GPC['idArr'] as $k => $id){
        $id = intval($id);
        if(!empty($id)){
            $goods = pdo_fetch("SELECT * FROM " . GetTableName('yuecostlog') . " WHERE id = :id", array(':id' => $id));
            if(empty($goods)){
                $notrowcount++;
                continue;
            }
            pdo_delete(GetTableName('yuecostlog',false), array('id' => $id, 'weid' => $weid));
            $rowcount++;
        }
    }
    $message = "操作成功！共删除{$rowcount}条数据,{$notrowcount}条数据不能删除!";

    $data ['result'] = true;

    $data ['msg'] = $message;

    die (json_encode($data));



}elseif($operation == 'delete'){
    $id = intval($_GPC['id']);
    if(empty($id)){
        $this->imessage('抱歉，本条信息不存在在或是已经被删除！');
    }
    pdo_delete($this->table_yuecostlog, array('id' => $id));
    $this->imessage('删除成功！', referer(), 'success');
}


?>