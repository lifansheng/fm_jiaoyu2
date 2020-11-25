<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_GPC, $_W;

$weid              = $_W['uniacid'];
$action            = 'buzhu';
$this1             = 'no4';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
$logo              = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");

$bj    = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = '{$weid}' AND schoolid ={$schoolid} And type = 'theclass' ORDER BY ssort DESC");
$nj = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = '{$weid}' AND schoolid ={$schoolid} And type = 'semester' ORDER BY ssort DESC");

$tid_global = $_W['tid'];
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';

/* if (!(IsHasQx($tid_global,1003301,1,$schoolid))){
	$this->imessage('非法访问，您无权操作该页面2','','error');	
} */


if($operation == 'post'){
	if (!(IsHasQx($tid_global,1003302,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面1','','error');	
	}
    load()->func('tpl');
    $id = intval($_GPC['id']);
    if(!empty($id)){
        $item    = pdo_fetch("SELECT * FROM " . tablename($this->table_teascore) . " WHERE id = :id", array(':id' => $id));
        $student = pdo_fetch("SELECT s_name FROM " . tablename($this->table_students) . " where id = :id ", array(':id' => $item['sid']));
		$bj_name = pdo_fetch("select sname FROM " . tablename($this->table_classify) . "  where  weid = '{$weid}' AND schoolid = '{$schoolid}' AND sid = '{$item['bj_id']}' ");
		$nj_name = pdo_fetch("select sname FROM " . tablename($this->table_classify) . "  where  weid = '{$weid}' AND schoolid = '{$schoolid}' AND sid = '{$item['nj_id']}' ");
		
		if($item['fromtid'] != 'founder' && $item['fromtid'] !='owner' ){
			$fromteacher = pdo_fetch("SELECT tname,fz_id,status FROM " . tablename($this->table_teachers) . " where id = :id ", array(':id' => $item['fromtid']));
		}else{
			$fromteacher['tname'] = "管理员";
			$fromfz['name'] = "管理员";
		}

      
        if(empty($item)){
            $this->imessage('抱歉，本条信息不存在在或是已经删除！', '', 'error');
        }
    }
    if(checksubmit('submit')){
        $data = array(
            'score' => trim($_GPC['score']),
        );

        if(empty($id)){
            $this->imessage('抱歉，本条信息不存在在或是已经删除！', '', 'error');
        }else{
            pdo_update($this->table_teascore, $data, array('id' => $id));
        }
        $this->imessage('修改学生量化评分成功！', $this->createWebUrl('studentscore', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
    }
}elseif($operation == 'display'){

    $pindex    = max(1, intval($_GPC['page']));
    $psize     = 20;

    if(!empty($_GPC['stuname'])){
		$student = pdo_fetch("SELECT id FROM " . tablename($this->table_students) . " WHERE schoolid = :schoolid And s_name = :s_name ORDER BY id DESC LIMIT 1", array(':schoolid' => $schoolid,':s_name' => $_GPC['stuname']));
		$condition .= " AND sid = '{$student['id']}'";		
    }
	$is_able = -1;
	$able_time = time();
	if($_GPC['is_able'] == 1){
		$is_able = 1;
		$condition .= " and starttime > '{$able_time}'  ";
	}
	if($_GPC['is_able'] == 2){
		$is_able = 2;
		$condition .= " and starttime <= '{$able_time}' and endtime >= '{$able_time}'  ";
	}
	
	if($_GPC['is_able'] == 3){
		$is_able = 3;
		$condition .= " and endtime < '{$able_time}'  ";
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
		$stu_str = '';
		foreach($student as $value){
			$stu_str .=$value['sid'].",";
		}
		$stu_str = tirm($stu_str,',');
		$condition .= " AND FIND_IN_SET(sid,'{$stu_str}')";				
    }

	
	$list = pdo_fetchall("SELECT * FROM " . tablename($this->table_buzhulog) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition   ORDER BY createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);


	foreach($list as $key => $row){
		$student = pdo_fetch("SELECT id,icon,s_name,bj_id,xq_id FROM " . tablename($this->table_students) . " WHERE schoolid = :schoolid And id = :id ", array(':schoolid' => $schoolid,':id' => $row['sid']));
		if(!empty($student['icon'])){
			$list[$key]['sicon'] = $student['icon'];
		}else{
			$list[$key]['sicon'] = $logo['spic']; 
		}
		$bj_name = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE schoolid = :schoolid And sid = :sid ", array(':schoolid' => $schoolid,':sid' => $student['bj_id']));
		$nj_name = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE schoolid = :schoolid And sid = :sid ", array(':schoolid' => $schoolid,':sid' => $student['xq_id']));
		$list[$key]['bj_name'] = $bj_name['sname'];
		$list[$key]['nj_name'] = $nj_name['sname'];	
		$list[$key]['s_name'] = $student['s_name'];
		$this_time = time();
		if($this_time>=$row['starttime'] && $this_time <= $row['endtime']){
			$list[$key]['status'] = 2; //生效中
		}elseif($this_time<$row['starttime']){
			$list[$key]['status'] = 1; //未生效
		}elseif($this_time>$row['endtime']){
			$list[$key]['status'] = 3 ; //已失效
		}		
	}
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_buzhulog) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition ");

	$pager = pagination($total, $pindex, $psize);
	
}elseif($operation == 'delete'){
    $id = intval($_GPC['id']);
    if(empty($id)){
        $this->imessage('抱歉，本条信息不存在在或是已经被删除！');
    }
    pdo_delete($this->table_buzhulog, array('id' => $id));
    $this->imessage('删除成功！', referer(), 'success');
}elseif($operation == 'deleteall'){
    $rowcount    = 0;
    $notrowcount = 0;
    foreach($_GPC['idArr'] as $k => $id){
        $id = intval($id);
        if(!empty($id)){
            $goods = pdo_fetch("SELECT * FROM " . tablename($this->table_buzhulog) . " WHERE id = :id", array(':id' => $id));
            if(empty($goods)){
                $notrowcount++;
                continue;
            }
            pdo_delete($this->table_buzhulog, array('id' => $id, 'weid' => $weid));
            $rowcount++;
        }
    }
    $message = "操作成功！共删除{$rowcount}条数据,{$notrowcount}条数据不能删除!";

    $data ['result'] = true;

    $data ['msg'] = $message;

    die (json_encode($data));
}
include $this->template('web/buzhu');
?>