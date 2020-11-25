<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_GPC, $_W;
$weid              = $_W['uniacid'];
$action            = 'classepraise';
$this1             = 'no10';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$operation         = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$schoolid          = intval($_GPC['schoolid']);

$logo = pdo_fetch("SELECT logo,title,is_kb FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$bj = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'theclass', ':schoolid' => $schoolid));
$tid_global = $_W['tid'];
if($operation == 'post'){
    load()->func('tpl');
    $id     = intval($_GPC['id']);//编辑操作
	$bj_id     = intval($_GPC['bj_id']);
	if(empty($bj_id)){
		$this->imessage('请选择班级','','error');
		}
    $banji = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE weid = :weid And schoolid = :schoolid And sid = :sid ", array(':weid' => $weid, ':schoolid' => $schoolid, ':sid' => $bj_id));
	if(!empty($id)){
		$item     = pdo_fetch("SELECT * FROM " . tablename($this->table_classcard_praise) . " WHERE schoolid = :schoolid And bj_id = :bj_id ", array(':schoolid' => $schoolid, ':bj_id' => $bj_id));
		$praise=iunserializer($item['praise']);
	}
	$type    = pdo_fetchall("SELECT * FROM " . tablename($this->table_classcard_praise_type) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' ORDER BY id DESC ");
	$py    = pdo_fetchall("SELECT * FROM " . tablename($this->table_classcard_praise_comment) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' ORDER BY ssort ASC " );
    $students = pdo_fetchall("SELECT id,s_name,icon FROM " . tablename($this->table_students) . " WHERE weid = :weid And schoolid = :schoolid And bj_id = :bj_id ORDER BY id DESC", array(':weid' => $weid, ':schoolid' => $schoolid, ':bj_id' => $bj_id));
    if(checksubmit('submit')){// 添加操作
        $data           = array(
            'weid'      => $weid,
            'schoolid'  => $schoolid,
            'bj_id'     => trim($_GPC['bj_id']),
			'zhu'     => $_GPC['zhu'],
            'createtime' => time()
        );
		foreach($_GPC['students'] as $key=>$row){
			if(!empty($row['id'])){
				$praises[$key]=$row;
				}
			}
        $data['praise'] = iserializer($praises);
        if(!$_GPC['bj_id']){
            $this->imessage('抱歉！请选择班级');
        }
		$check = pdo_fetch("SELECT * FROM " . tablename($this->table_classcard_praise) . " WHERE schoolid = :schoolid And bj_id = :bj_id ", array(':schoolid' => $schoolid, ':bj_id' => $bj_id));
        if(empty($id)){
            pdo_insert($this->table_classcard_praise, $data);
        }else{
            pdo_update($this->table_classcard_praise, $data, array('id' => $id));
        }
        $this->imessage('操作成功！', $this->createWebUrl('classepraise', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
    }

}elseif($operation == 'display'){//
    $pindex    = max(1, intval($_GPC['page']));
    $psize     = 10;
    $condition = '';
    if(!empty($_GPC['bj_id'])){
        $condition .= " AND bj_id = '{$_GPC['title']}' ";
    }
    $list = pdo_fetchall("SELECT * FROM " . tablename($this->table_classcard_praise) . " WHERE weid = '{$weid}' AND schoolid ={$schoolid} $condition ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
    foreach($list as $key => $r){
        $bjname               = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE sid = :sid", array(':sid' => $r['bj_id']));
         $list[$key]['num']=count(iunserializer($r['praise']));
        $list[$key]['bjname'] = $bjname['sname'];
    }
    $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_classcard_praise) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition");
    $pager = pagination($total, $pindex, $psize);
}elseif($operation == 'delete'){
    $id = intval($_GPC['id']);
    if(empty($id)){

        $this->imessage('抱歉，本条信息不存在在或是已经被删除！');
    }

    pdo_delete($this->table_classcard_praise, array('id' => $id));

    $this->imessage('删除成功！', $this->createWebUrl('classepraise', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
    #$this->imessage('删除成功！', 'referer', 'success');

}


include $this->template('web/classepraise');
?>