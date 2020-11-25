<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_GPC, $_W;

$weid              = $_W['uniacid'];
$action            = 'drug';
$this1             = 'no3';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
$logo              = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");

$bj    = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = '{$weid}' AND schoolid ={$schoolid} And type = 'theclass' ORDER BY ssort DESC");
$nj = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = '{$weid}' AND schoolid ={$schoolid} And type = 'semester' ORDER BY ssort DESC");

$tid_global = $_W['tid'];
//查看是否是喂药管家
$drugmanager = pdo_fetch("SELECT doctorid FROM " . GetTableName('schoolset') . " WHERE schoolid = '{$schoolid}'");
//查看是否是校长
$schoolteacher = pdo_fetch("SELECT status FROM " . GetTableName('teachers') . " WHERE schoolid = '{$schoolid}' AND id = '{$tid_global}' ");

if($drugmanager['doctorid'] == $tid_global || $schoolteacher['status'] == 2 || $_W['isfounder'] || $_W['role'] == 'owner'){
    $defaultop = 'display';
}else{
    if(IsHasQx($tid_global,1004501,1,$schoolid)){
        $defaultop = 'druglog';
    }else{
        $this->imessage('非法访问，您无权操作该页面','','error');	 
    }
}
$operation = !empty($_GPC['op']) ? $_GPC['op'] : $defaultop;
if($operation == 'display'){

    if(!($drugmanager['doctorid'] == $tid_global || $schoolteacher['status'] == 2 || $_W['isfounder'] || $_W['role'] == 'owner')){
        $this->imessage('非法访问，您无权操作该页面','','error');	 
    }

    $pindex    = max(1, intval($_GPC['page']));
    $psize     = 20;
	$status = -1;
    if($_GPC['status'] == 0){
        $status = 0;
        $condition .= " AND status = '{$status}'";
	}
	if($_GPC['status'] == 1){
        $status = 1;
        $condition .= " AND status = '{$status}'";
	}
	if($_GPC['status'] == 2){
        $status = 2;
        $condition .= " AND status = '{$status}'";
	}
    if(!empty($_GPC['createtime'])) {
        $starttime = strtotime($_GPC['createtime']['start']);
        $endtime = strtotime($_GPC['createtime']['end']) + 86399;
        $condition .= " AND NOT (endtime < '{$starttime}' OR starttime > '{$endtime}')";
    } else {
        $starttime = strtotime('-30 day');
        $endtime = TIMESTAMP;
    }

	if(!empty($_GPC['nj_id']) && empty($_GPC['bj_id'])){
		$student = pdo_fetchall("SELECT id FROM " . tablename($this->table_students) . " WHERE schoolid = :schoolid And xq_id = :xq_id ORDER BY id DESC ", array(':schoolid' => $schoolid,':xq_id' => $_GPC['nj_id']));
		$stu_str = '';
		foreach($student as $value){
			$stu_str .=$value['sid'].",";
		}
		$stu_str = tirm($stu_str,',');
		$condition .= " AND FIND_IN_SET(sid,'{$stu_str}')";		
    }
	
	if(!empty($_GPC['bj_id'])){
        $student = pdo_fetchall("SELECT id FROM " . tablename($this->table_students) . " WHERE schoolid = :schoolid And bj_id = :bj_id ORDER BY id DESC ", array(':schoolid' => $schoolid,':bj_id' => $_GPC['bj_id']));
        $stu_str = arrayToString($student);
		$condition .= " AND FIND_IN_SET(sid,'{$stu_str}')";				
    }

	
	$list = pdo_fetchall("SELECT * FROM " . GetTableName('drug') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition ORDER BY createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);

	foreach($list as $key => $row){
		$student = pdo_fetch("SELECT id,icon,s_name,bj_id,xq_id FROM " . tablename($this->table_students) . " WHERE schoolid = :schoolid And id = :id ", array(':schoolid' => $schoolid,':id' => $row['sid']));
        $list[$key]['sicon'] = $student['icon'] ? $student['icon'] : $logo['spic'];
		
		$bj_name = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE schoolid = :schoolid And sid = :sid ", array(':schoolid' => $schoolid,':sid' => $student['bj_id']));
		$nj_name = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE schoolid = :schoolid And sid = :sid ", array(':schoolid' => $schoolid,':sid' => $student['xq_id']));
		$list[$key]['bj_name'] = $bj_name['sname'];
		$list[$key]['nj_name'] = $nj_name['sname'];	
		$list[$key]['s_name'] = $student['s_name'];
        $datetime = unserialize($row['datetime']);
        $list[$key]['headimg'] = unserialize($row['headimg']);
        $list[$key]['datetime'] = arrayToString($datetime);
	}
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . GetTableName('drug') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition ");

	$pager = pagination($total, $pindex, $psize);
	
}elseif($operation == 'delete'){
    $id = intval($_GPC['id']);
    if(empty($id)){
        $this->imessage('抱歉，本条信息不存在在或是已经被删除！');
    }
    pdo_delete(GetTableName('drug',false), array('id' => $id));
    $this->imessage('删除成功！', referer(), 'success');
}elseif($operation == 'deleteall'){
    $rowcount    = 0;
    $notrowcount = 0;
    foreach($_GPC['idArr'] as $k => $id){
        $id = intval($id);
        if(!empty($id)){
            $goods = pdo_fetch("SELECT * FROM " . GetTableName('drug') . " WHERE id = :id", array(':id' => $id));
            if(empty($goods)){
                $notrowcount++;
                continue;
            }
            pdo_delete(GetTableName('drug',false), array('id' => $id));
            $rowcount++;
        }
    }
    $message = "操作成功！共删除{$rowcount}条数据,{$notrowcount}条数据不能删除!";

    $data ['result'] = true;

    $data ['msg'] = $message;

    die (json_encode($data));
}elseif($operation == 'refuse'){
    $id = $_GPC['id'];
    $schoolid = $_GPC['schoolid'];
    $refuse = $_GPC['refuseinfo'];
    $druginfo = pdo_fetch("SELECT id FROM " . GetTableName('drug') . " WHERE schoolid = '{$schoolid}' AND id = '{$id}'");
    if(empty($druginfo)){
        $result['result'] = false;
        $result['msg'] = '抱歉，本条信息不存在或是已经被删除！';
    }else{
        pdo_update(GetTableName('drug',false),array('updatetime'=>time(),'status'=>2,'refuse'=>$refuse),array('id'=>$id));
        $result['result'] = true;
        $result['msg'] = '操作成功！';
    }
    die(json_encode($result));  
}elseif($operation == 'argee'){
    $id = $_GPC['id'];
    $schoolid = $_GPC['schoolid'];
    //获取申请信息
    $druginfo = pdo_fetch("SELECT * FROM " . GetTableName('drug') . " WHERE id = '{$id}'");
    $sd = unserialize($druginfo['datetime']); //喂药时段
    //获取两个时间相差天数
    $diffday = diffBetweenTwoDays(date("Y-m-d",$druginfo['starttime']) ,date("Y-m-d",$druginfo['endtime']) ) +1;
    //获取班主任id
    $student = pdo_fetch("SELECT bj_id FROM " . GetTableName('students') . " WHERE id = '{$druginfo['sid']}'");
    $classify = pdo_fetch("SELECT tid FROM " . GetTableName('classify') . " WHERE schoolid = '{$schoolid}' AND sid = '{$student['bj_id']}'");
    for ($i=0; $i < $diffday; $i++) { 
        foreach ($sd as $key => $value) {
            $datetime = strtotime(date("Y-m-d $value",$druginfo['starttime'])) + $i*86400;
            $data = array(
                'weid' => $weid,
                'schoolid' => $schoolid,
                'drugid' => $id,
                'sid' => $druginfo['sid'],
                'status'=>0,
                'createtime'=>time(),
                'datetime' => $datetime,
                'tid' => $classify['tid'],
            );
            pdo_insert(GetTableName('druglog',false),$data);
        }
    }
    pdo_update(GetTableName('drug',false),array('updatetime'=>time(),'status'=>1),array('id'=>$id));   
    $result['result'] = true;
    $result['msg'] = '操作成功！';
    die(json_encode($result));  
}elseif($operation == 'druglog'){
    if(!(IsHasQx($tid_global,1004501,1,$schoolid))){
        $this->imessage('非法访问，您无权操作该页面','','error');	 
    }
    $pindex    = max(1, intval($_GPC['page']));
    $psize     = 20;
	$status = -1;
    if($_GPC['status'] == 0){
        $status = 0;
        $condition .= " AND status = '{$status}'";
	}
	if($_GPC['status'] == 1){
        $status = 1;
        $condition .= " AND status = '{$status}'";
	}
	if($_GPC['status'] == 2){
        $status = 2;
        $condition .= " AND status = '{$status}'";
	}
    if(!empty($_GPC['createtime'])) {
        $starttime = strtotime($_GPC['createtime']['start']);
        $endtime = strtotime($_GPC['createtime']['end']) + 86399;
        $condition .= " AND datetime >= '{$starttime}' AND datetime <= '{$endtime}'";
    } else {
        $starttime = strtotime('-30 day');
        $endtime = TIMESTAMP;
    }

	if(!empty($_GPC['nj_id']) && empty($_GPC['bj_id'])){
		$student = pdo_fetchall("SELECT id FROM " . tablename($this->table_students) . " WHERE schoolid = :schoolid And xq_id = :xq_id ORDER BY id DESC ", array(':schoolid' => $schoolid,':xq_id' => $_GPC['nj_id']));
		$stu_str = '';
		foreach($student as $value){
			$stu_str .=$value['sid'].",";
		}
		$stu_str = tirm($stu_str,',');
		$condition .= " AND FIND_IN_SET(sid,'{$stu_str}')";		
    }
	
	if(!empty($_GPC['bj_id'])){
        $student = pdo_fetchall("SELECT id FROM " . tablename($this->table_students) . " WHERE schoolid = :schoolid And bj_id = :bj_id ORDER BY id DESC ", array(':schoolid' => $schoolid,':bj_id' => $_GPC['bj_id']));
        $stu_str = arrayToString($student);
		$condition .= " AND FIND_IN_SET(sid,'{$stu_str}')";				
    }

	
	$list = pdo_fetchall("SELECT * FROM " . GetTableName('druglog') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition ORDER BY createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);

	foreach($list as $key => $row){
		$student = pdo_fetch("SELECT id,icon,s_name,bj_id,xq_id FROM " . tablename($this->table_students) . " WHERE schoolid = :schoolid And id = :id ", array(':schoolid' => $schoolid,':id' => $row['sid']));
		$bj_name = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE schoolid = :schoolid And sid = :sid ", array(':schoolid' => $schoolid,':sid' => $student['bj_id']));
        $nj_name = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE schoolid = :schoolid And sid = :sid ", array(':schoolid' => $schoolid,':sid' => $student['xq_id']));
        $drug = pdo_fetch("SELECT content,headimg FROM " . GetTableName('drug') . " WHERE id = :id ", array(':id' => $row['drugid']));
		$list[$key]['content'] = $drug['content'];
		$list[$key]['headimg'] = unserialize($drug['headimg']);
        $list[$key]['sicon'] = $student['icon'] ? $student['icon'] : $logo['spic'];
		$list[$key]['bj_name'] = $bj_name['sname'];
		$list[$key]['nj_name'] = $nj_name['sname'];	
		$list[$key]['s_name'] = $student['s_name'];
		$list[$key]['updatetime'] = $row['updatetime'] ? date("Y-m-d H:i:s",$row['updatetime']) : '尚未开始喂药';
	}
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . GetTableName('druglog') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition ");

	$pager = pagination($total, $pindex, $psize);
}
include $this->template('web/drug');
?>