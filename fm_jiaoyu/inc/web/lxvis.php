<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_GPC, $_W;
$weid              = $_W['uniacid'];
$action            = 'lxvis';
$this1             = 'no5';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
load()->func('tpl');
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$tid_global = $_W['tid'];
mload()->model('tea');
$logo = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where id = :id ", array(':id' => $schoolid));
$fztea = getalljsfzallteainfo($schoolid,0,$_W['schooltype']);
$nofztea = getalljsfzallteainfo_nofz($schoolid,$_W['schooltype']);
if($operation == 'display'){
    $pindex    = max(1, intval($_GPC['page']));
    $psize     = 20;
    $condition = '';
    $status = $_GPC['status'];
        if($_GPC['status'] != -1){
            $condition .= " AND l.status = '{$_GPC['status']}' ";
        }
    if(!empty($_GPC['s_name'])){
        $student = pdo_fetch("SELECT id FROM " . tablename($this->table_students) . " where :schoolid = schoolid And :weid = weid And :s_name = s_name", array(':weid' => $weid, ':schoolid' => $schoolid, ':s_name' => $_GPC['s_name']));
        $condition .= " AND l.sid = '{$student['id']}' ";
	}
	if(!empty($_GPC['tname'])){
		$teacher = pdo_fetch("SELECT id FROM " . tablename($this->table_teachers) . " where :schoolid = schoolid And :weid = weid And :tname = tname", array(':weid' => $weid, ':schoolid' => $schoolid, ':tname' => $_GPC['tname']));
        $condition .= " AND l.tid = '{$teacher['id']}' ";
	}
    if(!empty($_GPC['createtime']) || !empty($_GPC['createtime'])){
	    if(!empty($_GPC['createtime']))
	    {
		    $starttime = strtotime($_GPC['createtime']['start']);
		    $condition .= " AND l.starttime > '{$starttime}' ";
	    }
	    if(!empty($_GPC['createtime']['end']))
	    {
		    $endtime   = strtotime($_GPC['createtime']['end']);
		    $condition .= " AND l.endtime < '{$endtime}' ";
	    }

    }else{
        $starttime = strtotime('-20 day');
        $endtime   = strtotime('+7 day');
        $condition .= " AND l.starttime > '{$starttime}' ";
        $condition .= " AND l.endtime < '{$endtime}' ";
    }
    $list = pdo_fetchall("SELECT l.*,t.tname,s.s_name,u.realname,u.pard FROM " . GetTableName('lxvis') . " as l LEFT JOIN " . GetTableName('teachers') . " as t ON t.id = l.tid LEFT JOIN " . GetTableName('students') . " as s ON s.id = l.sid LEFT JOIN " . GetTableName('user') . " as u ON l.userid = u.id WHERE l.schoolid = '{$schoolid}' $condition ORDER BY l.id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
    
	$total = pdo_fetchcolumn("SELECT COUNT(l.id) FROM " . GetTableName('lxvis') . " as l LEFT JOIN " . GetTableName('teachers') . " as t ON t.id = l.tid LEFT JOIN " . GetTableName('students') . " as s ON s.id = l.sid WHERE l.schoolid = '{$schoolid}' $condition");
    $pager = pagination($total, $pindex, $psize);
} else if ($operation == 'getAllTea'){
    $fztea = getalljsfzallteainfo($schoolid,0,$_W['schooltype']);
    $nofztea = getalljsfzallteainfo_nofz($schoolid,$_W['schooltype']);
    if($_GPC['type'] == 1){ //拜访老师
        foreach ($fztea as $key => $value) {
            foreach ($value['alltea'] as $key2 => $v) {
                $islxvis = pdo_fetch("SELECT lxvis FROM ".GetTableName('teachers')." WHERE id= '{$v['id']}' ")['lxvis'];
                if($islxvis == 1){
                    $fztea[$key]['alltea'][$key2]['checked'] = true;
                }else{
                    $fztea[$key]['alltea'][$key2]['checked'] = false;
                }
            }
        }

        foreach ($nofztea as $key => $value) {
            $islxvis = pdo_fetch("SELECT lxvis FROM ".GetTableName('teachers')." WHERE id= '{$value['id']}' ")['lxvis'];
            if($islxvis == 1){
                $nofztea[$key]['checked'] = true;
            }else{
                $nofztea[$key]['checked'] = false;
            }
        }

    }
    if($_GPC['type'] == 2){ //拜访门卫
        foreach ($fztea as $key => $value) {
            foreach ($value['alltea'] as $key2 => $v) {
                $islxdoorman = pdo_fetch("SELECT lxdoorman FROM ".GetTableName('teachers')." WHERE id= '{$v['id']}' ")['lxdoorman'];
                if($islxdoorman == 1){
                    $fztea[$key]['alltea'][$key2]['checked'] = true;
                }else{
                    $fztea[$key]['alltea'][$key2]['checked'] = false;
                }
            }
        }

        foreach ($nofztea as $key => $value) {
            $islxdoorman = pdo_fetch("SELECT lxdoorman FROM ".GetTableName('teachers')." WHERE id= '{$value['id']}' ")['lxdoorman'];
            if($islxdoorman == 1){
                $nofztea[$key]['checked'] = true;
            }else{
                $nofztea[$key]['checked'] = false;
            }
        }
    }
    include $this->template('public/lxvis');
    die();
} else if ($operation == 'setVistors'){
    $tidStr = trim($_GPC['tidStr'],',');
    $tidArr = explode(',',$tidStr);
    if($_GPC['type'] == 1){ //修改拜访老师
        pdo_update(GetTableName('teachers',false),array('lxvis'=>0));
        foreach ($tidArr as $id) {
            pdo_update(GetTableName('teachers',false),array('lxvis'=>1),array('id'=>$id));
        }
    }

    if($_GPC['type'] == 2){ //修改门卫
        pdo_update(GetTableName('teachers',false),array('lxdoorman'=>0));
        foreach ($tidArr as $id) {
            pdo_update(GetTableName('teachers',false),array('lxdoorman'=>1),array('id'=>$id));
        }
    }
    $result['msg'] = '设置成功';
    $result['status'] = true;
    die(json_encode($result));
} else if ($operation == 'post'){
	$id = intval($_GPC['id']);
	if(empty($id)){
		$this->imessage('抱歉，申请不存在或是已经被删除！', referer(), 'error');
    }
    mload()->model('snowflake');
    $snowid = GetSnowId();
    $tempcard = time().mt_rand(100000,999999);
    $data = array(
        'status' => 1,
        'confirmtime' => time(),
        'snowid' => $snowid,
        'tempcard' => $tempcard,
    );
    mload()->model('xz');
    XzAddVistorAct($id);
    pdo_update(GetTableName('lxvis',false), $data, array('id' => $id));
    $this->sendMobileLxVis($id, $_GPC['schoolid'], $weid);

    if(!empty($lxvislog['type'])){
        if($lxvislog['type'] == 1){
            $visDataLog['type'] = 2; //离校
        }else{
            $visDataLog['type'] = 1; //离校
        }
    }else{
        $visDataLog['type'] = 1; //进校
    }
    $visDataLog['signtime'] = time();
    pdo_insert(GetTableName('lxvislog',false),$visDataLog);
    $fstype = true;

	$this->imessage('已同意！', $this->createWebUrl('lxvis', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
} else if ($operation == 'refuse'){
	if (!(IsHasQx($tid_global,1004002,1,$schoolid))){
        $this->imessage('非法访问，您无权操作该页面','','error');
    }
	$id = intval($_GPC['id']);
    $data['status'] = 2;
    $data['refuseinfo'] = $_GPC['refuseinfo'];
    $data['confirmtime'] = time();
    
	pdo_update(GetTableName('lxvis',false), $data, array('id' => $id));
    #访问拒绝结果推送
	$this->sendMobileLxVis($id, $_GPC['schoolid'], $weid);
    $result['msg'] = '已拒绝';
    $result['status'] = 2;
    die(json_encode($result));
} else if ($operation == 'delete'){
	$id = intval($_GPC['id']);
	if(empty($id)){
		$this->imessage('抱歉，申请不存在或是已经被删除！', referer(), 'error');
	}
	pdo_delete(GetTableName('lxvis',false), array('id' => $id));
	$this->imessage('删除成功！', $this->createWebUrl('lxvis', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
} else if ($operation == 'deleteall'){
	$rowcount    = 0;
    $notrowcount = 0;
    foreach($_GPC['idArr'] as $k => $id){
        $id = intval($id);
		$lastedittime = time();
        if(!empty($id)){
            $lxvis = pdo_fetch("SELECT * FROM " . GetTableName('lxvis') . " WHERE id = :id", array(':id' => $id));
            if(empty($lxvis)){
                $notrowcount++;
                continue;
            }else{
                pdo_delete(GetTableName('lxvis',false), array('id' => $id));
				$rowcount++;

			}
        }
    }
	$message = "操作成功！共删除{$rowcount}条数据,{$notrowcount}条数据不能删除!";
	$data ['result'] = true;
	$data ['msg'] = $message;
	die (json_encode($data));
	$this->imessage('删除成功！', $this->createWebUrl('lxvis', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
} else if ($operation == 'vislog'){
	$pindex    = max(1, intval($_GPC['page']));
    $psize     = 20;
    $condition = '';
    if(!empty($_GPC['type'])){
        $type      = intval($_GPC['type']);
        $condition .= " AND lo.type = '{$type}' ";
    }
    if(!empty($_GPC['id'])){
        $condition .= " AND lo.lxvisid = '{$_GPC['id']}' ";
    }
    
    if(!empty($_GPC['s_name'])){
		$student = pdo_fetch("SELECT id FROM " . tablename($this->table_students) . " where :schoolid = schoolid And :weid = weid And :s_name = s_name", array(':weid' => $weid, ':schoolid' => $schoolid, ':s_name' => $_GPC['s_name']));
        $condition .= " AND l.sid = '{$student['id']}' ";
	}
	
    if(!empty($_GPC['createtime']['start'])){
        $starttime = strtotime($_GPC['createtime']['start']);
        $endtime = strtotime($_GPC['createtime']['end']);
        $condition .= " AND lo.signtime BETWEEN '{$starttime}' and '{$endtime}'";
    }
	$list = pdo_fetchall("SELECT lo.id,lo.pic,lo.pic2,lo.type,lo.signtime,t.tname,s.s_name,u.realname,u.pard,c.name,l.content FROM " . GetTableName('lxvislog') . " as lo LEFT JOIN " . GetTableName('lxvis') . " as l ON lo.lxvisid = l.id LEFT JOIN " . GetTableName('teachers') . " as t ON t.id = l.tid LEFT JOIN " . GetTableName('students') . " as s ON s.id = l.sid LEFT JOIN " . GetTableName('user') . " as u ON l.userid = u.id LEFT JOIN " . GetTableName('checkmac') . " as c ON c.id = lo.macid WHERE l.schoolid = '{$schoolid}' $condition ORDER BY lo.id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
    $pager = pagination(count($list), $pindex, $psize);
} else if ($operation == 'dellog'){
	
	$id = intval($_GPC['id']);
	$lastedittime = time();
	if(empty($id)){
		$this->imessage('抱歉，申请不存在或是已经被删除！', referer(), 'error');
	}
	pdo_delete(GetTableName('lxvislog',false), array('id' => $id));
	$this->imessage('删除成功！', $this->createWebUrl('lxvis', array('op' => 'vislog', 'schoolid' => $schoolid)), 'success');
} else if ($operation == 'dellogall'){
	$rowcount    = 0;
    $notrowcount = 0;
    foreach($_GPC['idArr'] as $k => $id){
        $id = intval($id);
		$lastedittime = time();
        if(!empty($id)){
            $visitors = pdo_fetch("SELECT * FROM " . GetTableName('lxvislog') . " WHERE id = :id", array(':id' => $id));
            if(empty($visitors)){
                $notrowcount++;
                continue;
            }else{
				pdo_delete(GetTableName('lxvislog',false), array('id' => $id));
				$rowcount++;
			}
        }
    }
	$message = "操作成功！共删除{$rowcount}条数据,{$notrowcount}条数据不能删除!";
	$data ['result'] = true;
	$data ['msg'] = $message;
	die (json_encode($data));
	$this->imessage('删除成功！', $this->createWebUrl('lxvis', array('op' => 'vislog', 'schoolid' => $schoolid)), 'success');
}
include $this->template('web/lxvis');
?>