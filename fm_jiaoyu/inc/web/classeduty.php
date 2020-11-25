<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_GPC, $_W;
$weid              = $_W['uniacid'];
$action            = 'classeduty';
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
    $students = pdo_fetchall("SELECT id,s_name,icon FROM " . tablename($this->table_students) . " WHERE weid = :weid And schoolid = :schoolid And bj_id = :bj_id ORDER BY id DESC", array(':weid' => $weid, ':schoolid' => $schoolid, ':bj_id' => $bj_id));
    if(!empty($id)){
		$item   = pdo_fetch("SELECT * FROM " . tablename($this->table_classcard_duty) . " WHERE schoolid = :schoolid And bj_id = :bj_id ", array(':schoolid' => $schoolid, ':bj_id' => $bj_id));
		$monarr = iunserializer($item['monday']);
		$tusarr = iunserializer($item['tuesday']);
		$wedarr = iunserializer($item['wednesday']);
		$thuarr = iunserializer($item['thursday']);
		$friarr = iunserializer($item['friday']);
		$satarr = iunserializer($item['saturday']);
        $sunarr = iunserializer($item['sunday']);
        if(empty($item)){
            $this->imessage('抱歉，本条信息不存在在或是已经删除！', '', 'error');
        }
    }
    if(checksubmit('submit')){// 添加操作
        $data           = array(
            'weid'      => $weid,
            'schoolid'  => $schoolid,
            'bj_id'     => trim($_GPC['bj_id']),
            'createtime' => time()
        );
        $data['monday'] = iserializer($_GPC['mon_students']);
        $data['tuesday'] = iserializer($_GPC['tus_students']);
        $data['wednesday'] = iserializer($_GPC['wed_students']);
        $data['thursday'] = iserializer($_GPC['thu_students']);
        $data['friday'] = iserializer($_GPC['fri_students']);
        $data['saturday'] = iserializer($_GPC['sat_students']);
        $data['sunday'] = iserializer($_GPC['sun_students']);
        if(!$_GPC['bj_id']){
            $this->imessage('抱歉！请选择班级');	
        }
		$check     = pdo_fetch("SELECT * FROM " . tablename($this->table_classcard_duty) . " WHERE schoolid = :schoolid And bj_id = :bj_id ", array(':schoolid' => $schoolid, ':bj_id' => $bj_id));
        if($check && empty($id)){
            $this->imessage('抱歉！本班已经添加了值日生，请勿重复');
        }
        if(empty($id)){
            pdo_insert($this->table_classcard_duty, $data);
        }else{
            pdo_update($this->table_classcard_duty, $data, array('id' => $id));
        }
        $this->imessage('操作成功！', $this->createWebUrl('classeduty', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
    }
}elseif($operation == 'display'){//
    $pindex    = max(1, intval($_GPC['page']));
    $psize     = 10;
    $condition = '';
    if(!empty($_GPC['bj_id'])){
        $condition .= " AND bj_id = '{$_GPC['title']}' ";
    }
    $list = pdo_fetchall("SELECT * FROM " . tablename($this->table_classcard_duty) . " WHERE weid = '{$weid}' AND schoolid ={$schoolid} $condition ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
    foreach($list as $key => $r){
        $bjname               = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE sid = :sid", array(':sid' => $r['bj_id'])); 
        $list[$key]['bjname'] = $bjname['sname'];
    }
    $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_classcard_duty) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition");
    $pager = pagination($total, $pindex, $psize);
}elseif($operation == 'delete'){
    $id = intval($_GPC['id']);
    if(empty($id)){
        $this->imessage('抱歉，本条信息不存在在或是已经被删除！');
    }
    pdo_delete($this->table_classcard_duty, array('id' => $id));
    $this->imessage('删除成功！', $this->createWebUrl('classeduty', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
}
include $this->template('web/classeduty');
?>