<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_GPC, $_W;

$weid              = $_W['uniacid'];
$action            = 'behaviorscore';
$this1             = 'no2';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
$logo              = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$tid_global = $_W['tid'];
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';

if($operation == 'post'){
    $id = intval($_GPC['id']);
    if(!empty($id)){
        $item    = pdo_fetch("SELECT * FROM " . GetTableName('behaviorscorelog') . " WHERE id = :id", array(':id' => $id));
        if($item['tid'] > 0){
            $teacher = pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " where id = :id ", array(':id' => $item['tid']));
            $tname = $teacher['tname'];
        }else{
            $tname = '管理员';
        }
        $bhs = pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE sid = '{$item['bhsid']}' and type = 'behaviorscore' ");
        $student = pdo_fetch("SELECT s_name,bj_id FROM ".GetTableName('students')." WHERE id = '{$item['sid']}' ");
        $bj = pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE sid = '{$student['bj_id']}' ");
        $qh = pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE sid = '{$item['qhid']}' and type = 'xq_score' ");
        if(empty($item)){
            $this->imessage('抱歉，本条信息不存在在或是已经删除！', '', 'error');
        }
    }
    if(checksubmit('submit')){
       $word = $_GPC['word'];
       $score = $_GPC['score'];
        if(intval($_GPC['tid']) > 0 ){
            $tid = $_GPC['tid'];
        }else{
            $tid = -1;
        }
        $data = array(
			'word'  => $word,
            'score' => $score,
            'createtime' => time(),
            'tid' => $tid
        );
        if(empty($id)){
            $this->imessage('抱歉，本条信息不存在在或是已经删除！', '', 'error');
        }else{
            pdo_update(GetTableName('behaviorscorelog',false), $data, array('id' => $id));
        }
        $this->imessage('修改评分成功！', $this->createWebUrl('behaviorlog', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
    }
}elseif($operation == 'display'){
    if($tid_global == 'founder' || $tid_global == 'owner'){
        $bjlist = pdo_fetchall("SELECT sname as old_sname ,sid FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and type = 'theclass' ORDER BY ssort DESC ");
    }else{
        $bjlist = GetAllClassInfoByTid($schoolid,0,$_W['schooltype'],$tid_global);
    }
    $qh     = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = '{$weid}' AND schoolid ={$schoolid} And type = 'xq_score' ORDER BY ssort DESC");
    $pindex = max(1, intval($_GPC['page']));
    $psize  = 20;

    $condition = '';
    $s_sname   = $_GPC['sname'];
    $qh_id     = $_GPC['qh_id'];
    $bj_id     = $_GPC['bj_id'];
    if(!empty($s_sname)){
        $condition .= " and s.s_name like '%{$s_sname}%' ";
    }
    if(!empty($bj_id)){
        $condition .= " and s.bj_id = '{$bj_id}' ";
    }

    if(!empty($qh_id)){
        $condition .= " and bhs.qhid = '{$qh_id}' ";
    }
 
	$bjstr = '';
	foreach($bjlist as $v){
		$bjstr .= $v['sid'].',';
	}
	$bjstr = trim($bjstr,",");
	$list = pdo_fetchall("SELECT bhs.* , s.s_name,s.bj_id FROM ".GetTableName('behaviorscorelog')." as bhs , ".GetTableName('students')." as s WHERE s.id = bhs.sid and  bhs.schoolid = '{$schoolid}' and FIND_IN_SET(s.bj_id,'{$bjstr}') {$condition} ORDER BY bhs.createtime DESC , CONVERT(s.s_name USING gbk) ASC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);

	foreach($list as $key => $row){
		if($row['tid'] > 0 ){
			$list[$key]['tname'] = pdo_fetch("SELECT tname as tname  FROM ".GetTableName('teachers')." WHERE id = '{$row['tid']}' ")['tname'];
		}else{
			$list[$key]['tname'] = '管理员';
		}
		$list[$key]['qhname']  = pdo_fetch("SELECT sname  FROM ".GetTableName('classify')." WHERE sid = '{$row['qhid']}' ")['sname'];
		$list[$key]['bjname']  = pdo_fetch("SELECT sname  FROM ".GetTableName('classify')." WHERE sid = '{$row['bj_id']}' ")['sname'];
		$list[$key]['bhsname'] = pdo_fetch("SELECT sname  FROM ".GetTableName('classify')." WHERE sid = '{$row['bhsid']}' ")['sname'];
	}
	$total = pdo_fetchcolumn("SELECT count(bhs.id) FROM ".GetTableName('behaviorscorelog')." as bhs , ".GetTableName('students')." as s WHERE s.id = bhs.sid and  bhs.schoolid = '{$schoolid}' and FIND_IN_SET(s.bj_id,'{$bjstr}') {$condition}  ");
	$pager = pagination($total, $pindex, $psize);
}elseif($operation == 'delete'){
    $id = intval($_GPC['id']);
    if(empty($id)){
        $this->imessage('抱歉，本条信息不存在在或是已经被删除！');
    }
    pdo_delete(GetTableName('behaviorscorelog',false), array('id' => $id));
    $this->imessage('删除成功！', referer(), 'success');
}elseif($operation == 'deleteall'){
    $rowcount    = 0;
    $notrowcount = 0;
    foreach($_GPC['idArr'] as $k => $id){
        $id = intval($id);
        if(!empty($id)){
            $goods = pdo_fetch("SELECT * FROM " . GetTableName('behaviorscorelog') . " WHERE id = :id", array(':id' => $id));
            if(empty($goods)){
                $notrowcount++;
                continue;
            }
            pdo_delete(GetTableName('behaviorscorelog',false), array('id' => $id, 'weid' => $weid));
            $rowcount++;
        }
    }
    $message = "操作成功！共删除{$rowcount}条数据,{$notrowcount}条数据不能删除!";
    $data ['result'] = true;
    $data ['msg'] = $message;
    die (json_encode($data));
}
include $this->template('web/behaviorlog');
?>