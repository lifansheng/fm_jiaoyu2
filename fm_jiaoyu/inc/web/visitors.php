<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_GPC, $_W;
$weid              = $_W['uniacid'];
$action            = 'visitors';
$this1             = 'no5';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
load()->func('tpl');
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$tid_global = $_W['tid'];
$logo = pdo_fetch("SELECT logo,title,spic,tpic,is_cardlist,is_cardpay,cardset FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
// 查询搜索条件的事由
$visireason     = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'visireason' And schoolid = {$schoolid} ORDER BY sid ASC, ssort DESC");



if (!(IsHasQx($tid_global,1004001,1,$schoolid)) && IsHasQx($tid_global,1004011,1,$schoolid)){
    $operation = 'vislog';
}




// 权限管理

if($operation == 'display'){
    if (!(IsHasQx($tid_global,1004001,1,$schoolid))){
        $this->imessage('非法访问，您无权操作该页面','','error');
    }
    $pindex    = max(1, intval($_GPC['page']));
    $psize     = 20;
    $condition = '';
    if($_GPC['status'] == 1){
        $status      = intval($_GPC['status']);
        $condition .= " AND status = '{$status}' ";
    }
    if($_GPC['status'] == 2){
        $status      = intval($_GPC['status']);
        $condition .= " AND status = '{$status}' ";
    }
    if($_GPC['status'] == 3){
        $status      = intval($_GPC['status']);
        $condition .= " AND status = '{$status}' ";
    }
	if($_GPC['status'] == 4){
        $status      = intval($_GPC['status']);
        $condition .= " AND status = '{$status}' ";
    }
    if($_GPC['status'] == 5){
        $status      = intval($_GPC['status']);
        $condition .= " AND status = '{$status}' ";
    }
    if($_GPC['status'] == 6){
        $status      = intval($_GPC['status']);
        $condition .= " AND status = '{$status}' ";
    }
    if(!empty($_GPC['s_name'])){
		$condition .= " AND s_name LIKE '%{$_GPC['s_name']}%'";
	}
	if(!empty($_GPC['tname'])){
		$teacher = pdo_fetch("SELECT id FROM " . tablename($this->table_teachers) . " where :schoolid = schoolid And :weid = weid And :tname = tname", array(':weid' => $weid, ':schoolid' => $schoolid, ':tname' => $_GPC['tname']));
        $condition .= " AND t_id = '{$teacher['id']}' ";
	}
	if(!empty($_GPC['unit'])){
		$condition .= " AND unit LIKE '%{$_GPC['unit']}%'";
	}
	if(!empty($_GPC['plate_num'])){
		$condition .= " AND plate_num LIKE '%{$_GPC['plate_num']}%'";
	}
	if(!empty($_GPC['idcard'])){
		$condition .= " AND idcard LIKE '%{$_GPC['idcard']}%'";
	}
	if(!empty($_GPC['sy_id'])){
        $condition .= " AND sy_id = '{$_GPC['sy_id']}'";
	}
    if(!empty($_GPC['createtime']) || !empty($_GPC['$createtime'])){
		
	    if(!empty($_GPC['createtime']))
	    {
		    $starttime = strtotime($_GPC['createtime']['start']);
		    $condition .= " AND starttime > '{$starttime}' ";
	    }
	    if(!empty($_GPC['createtime']['end']))
	    {
		   $endtime   = strtotime($_GPC['createtime']['end']);
		    $condition .= " AND endtime < '{$endtime}' ";
	    }

    }else{
        $starttime = strtotime('-20 day');
        $endtime   = strtotime('+7 day');
    }
	$list = pdo_fetchall("SELECT * FROM " . tablename($this->table_visitors) . " WHERE weid = '{$weid}'and schoolid = '{$schoolid}' $condition ORDER BY createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
	foreach($list as $k => $v){
        $reason = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE sid = :sid", array(':sid' => $v['sy_id']));
        $teacher = pdo_fetch("SELECT tname,mobile FROM " . tablename($this->table_teachers) . " WHERE id = :id", array(':id' => $v['t_id']));
        $list[$k]['sname']    = $reason['sname'];
        $list[$k]['tname']    = $teacher['tname'];
        $list[$k]['mobile']    = $teacher['mobile'];
	}
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_visitors) . " WHERE weid = '{$weid}' AND schoolid ={$schoolid}  $condition");
    $pager = pagination($total, $pindex, $psize);
    
}
if($operation == 'post'){
    if (!(IsHasQx($tid_global,1004002,1,$schoolid))){
        $this->imessage('非法访问，您无权操作该页面','','error');
    }
	$id = intval($_GPC['id']);
	$data ['id'] = $id;
	$data ['schoolid'] = $schoolid;
	$lastedittime = time();
	#创建心跳任务
	CreateHBtodo_ZB($schoolid,$weid,$lastedittime,17);
	// 生成二维码
	$qrcode = visitors_qrcode(json_encode($data));
	if(empty($id)){
		$this->imessage('抱歉，申请不存在或是已经被删除！', referer(), 'error');
	}
    pdo_update($this->table_visitors, array('status' => 2, 'lastedittime' => $lastedittime, 'qrcode'=>"$qrcode"), array('id' => $id));
	#访问申请结果推送
	$this->sendMobileStuVis($id, $_GPC['schoolid'], $weid);
	$this->imessage('预约成功！', $this->createWebUrl('visitors', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
}elseif($operation == 'refuse'){
	if (!(IsHasQx($tid_global,1004002,1,$schoolid))){
        $this->imessage('非法访问，您无权操作该页面','','error');
    }
	$id = intval($_GPC['id']);
	$refuseinfo = $_GPC['refuseinfo'];
	pdo_update($this->table_visitors, array('status' => 3,'refuseinfo'=>$refuseinfo), array('id' => $id));
    #访问拒绝结果推送
    $this->sendMobileStuVis($id, $_GPC['schoolid'], $weid);
    $result['msg'] = '拒绝成功';
    $result['status'] = 1;
    die(json_encode($result));
}elseif($operation == 'delete'){
	$id = intval($_GPC['id']);
	$lastedittime = time();
	if(empty($id)){
		$this->imessage('抱歉，申请不存在或是已经被删除！', referer(), 'error');
	}
	pdo_delete($this->table_visitors, array('id' => $id));
	$this->imessage('删除成功！', $this->createWebUrl('visitors', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
}elseif($operation == 'deleteall'){
	$rowcount    = 0;
    $notrowcount = 0;
    foreach($_GPC['idArr'] as $k => $id){
        $id = intval($id);
		$lastedittime = time();
        if(!empty($id)){
            $visitors = pdo_fetch("SELECT * FROM " . tablename($this->table_visitors) . " WHERE id = :id", array(':id' => $id));
            if(empty($visitors)){
                $notrowcount++;
                continue;
            }else{
				pdo_delete($this->table_visitors, array('id' => $id));
				$rowcount++;

			}
        }
    }
	$message = "操作成功！共删除{$rowcount}条数据,{$notrowcount}条数据不能删除!";
	$data ['result'] = true;
	$data ['msg'] = $message;
	die (json_encode($data));
	$this->imessage('删除成功！', $this->createWebUrl('visitors', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
}elseif($operation == 'vislog'){
    if (!(IsHasQx($tid_global,1004011,1,$schoolid))){
        $this->imessage('非法访问，您无权操作该页面','','error');
    }
	$pindex    = max(1, intval($_GPC['page']));
    $psize     = 20;
    $condition = '';
    if($_GPC['type'] == 1){
        $type      = intval($_GPC['type']);
        $condition .= " AND type = '{$type}' ";
    }
    if($_GPC['type'] == 2){
        $type      = intval($_GPC['type']);
        $condition .= " AND type = '{$type}' ";
    }
    
    if(!empty($_GPC['s_name'])){
		$condition1 .= " AND s_name = '{$_GPC['s_name']}'";
	}
	if(!empty($_GPC['tname'])){
		$teacher = pdo_fetch("SELECT id FROM " . tablename($this->table_teachers) . " where :schoolid = schoolid And :weid = weid And :tname = tname", array(':weid' => $weid, ':schoolid' => $schoolid, ':tname' => $_GPC['tname']));
        $condition .= " AND tid = '{$teacher['id']}' ";
	}
	if(!empty($_GPC['unit'])){
		$condition1 .= " AND unit LIKE '%{$_GPC['unit']}%'";
	}
	if(!empty($_GPC['plate_num'])){
		$condition .= " AND plate_num LIKE '%{$_GPC['plate_num']}%'";
	}
	if(!empty($_GPC['idcard'])){
		$condition1 .= " AND idcard = '{$_GPC['idcard']}'";
	}
	if(!empty($_GPC['sy_id'])){
        $condition1 .= " AND sy_id = '{$_GPC['sy_id']}'";
	}
    if(!empty($_GPC['createtime']) || !empty($_GPC['$createtime'])){
		
	    if(!empty($_GPC['createtime']))
	    {
		    $starttime = strtotime($_GPC['createtime']['start']);
		    $condition .= " AND createtime > '{$starttime}' ";
	    }
	    if(!empty($_GPC['createtime']['end']))
	    {
		   $endtime   = strtotime($_GPC['createtime']['end']);
		    $condition .= " AND createtime < '{$endtime}' ";
	    }

    }else{
        $starttime = strtotime('-1 day');
        $endtime   = strtotime('+7 day');
    }
	$vis = pdo_fetchall("SELECT id FROM " . tablename($this->table_visitors) . " WHERE weid = '{$weid}'and schoolid = '{$schoolid}' $condition1 ORDER BY createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);


	foreach($vis as $v){
		$vis_str_temp .=$v['id'].",";
	}
	
	$vis_str = trim($vis_str_temp,",");
	

		$condition .= " AND FIND_IN_SET(vis_id,'{$vis_str}')";

    
	$list = pdo_fetchall("SELECT * FROM " . tablename($this->table_vislog) . " WHERE weid = '{$weid}' and schoolid = '{$schoolid}' $condition ORDER BY createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
    
	foreach($list as $k => $v){
        $mac = pdo_fetch("SELECT name FROM " . tablename($this->table_checkmac) . " WHERE id = :macid", array(':macid' => $v['macid']));
        $visitors = pdo_fetch("SELECT * FROM " . tablename($this->table_visitors) . " WHERE id = :vis_id", array(':vis_id' => $v['vis_id']));
        $reason = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE sid = :sid", array(':sid' => $visitors['sy_id']));
        $teacher = pdo_fetch("SELECT tname,mobile FROM " . tablename($this->table_teachers) . " WHERE id = :id", array(':id' => $visitors['t_id']));
        $list[$k]['s_name']    = $visitors['s_name'];
        $list[$k]['unit']    = $visitors['unit'];
        $list[$k]['plate_num']    = $visitors['plate_num'];
        $list[$k]['idcard']    = $visitors['idcard'];
        $list[$k]['tname']    = $teacher['tname'];
        $list[$k]['mobile']    = $teacher['mobile'];
        $list[$k]['sname']    = $reason['sname'];
        $list[$k]['name']    = $mac['name'];
	}
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_vislog) . " WHERE weid = '{$weid}' AND schoolid ={$schoolid}  $condition");
    $pager = pagination($total, $pindex, $psize);
	
}elseif($operation == 'dellog'){
	
	$id = intval($_GPC['id']);
	$lastedittime = time();
	if(empty($id)){
		$this->imessage('抱歉，申请不存在或是已经被删除！', referer(), 'error');
	}
	pdo_delete($this->table_vislog, array('id' => $id));
	$this->imessage('删除成功！', $this->createWebUrl('visitors', array('op' => 'vislog', 'schoolid' => $schoolid)), 'success');
}elseif($operation == 'dellogall'){
	$rowcount    = 0;
    $notrowcount = 0;
    foreach($_GPC['idArr'] as $k => $id){
        $id = intval($id);
		$lastedittime = time();
        if(!empty($id)){
            $visitors = pdo_fetch("SELECT * FROM " . tablename($this->table_vislog) . " WHERE id = :id", array(':id' => $id));
            if(empty($visitors)){
                $notrowcount++;
                continue;
            }else{
				pdo_delete($this->table_vislog, array('id' => $id));
				$rowcount++;

			}
        }
    }
	$message = "操作成功！共删除{$rowcount}条数据,{$notrowcount}条数据不能删除!";
	$data ['result'] = true;
	$data ['msg'] = $message;
	die (json_encode($data));
	$this->imessage('删除成功！', $this->createWebUrl('visitors', array('op' => 'vislog', 'schoolid' => $schoolid)), 'success');
}elseif($operation == 'quxiao'){
    if (!(IsHasQx($tid_global,1004002,1,$schoolid))){
        $this->imessage('非法访问，您无权操作该页面','','error');
    }
    $id = intval($_GPC['id']);
    $lastedittime = time();
    #创建心跳任务
    CreateHBtodo_ZB($schoolid,$weid,$lastedittime,17);
    pdo_update($this->table_visitors, array('status' => 6,'lastedittime'=>$lastedittime), array('id' => $id));
    #访问取消结果推送
    $this->sendMobileStuVis($id, $_GPC['schoolid'], $weid);
    $this->sendMobileTeaVis($id, $_GPC['schoolid'], $weid);
    $result['msg'] = '取消成功';
    $result['status'] = 1;
    die(json_encode($result));
}
include $this->template('web/visitors');
?>