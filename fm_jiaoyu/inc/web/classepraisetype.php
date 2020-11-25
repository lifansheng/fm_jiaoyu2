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
$schoolid          = intval($_GPC['schoolid']);
$logo              = pdo_fetch("SELECT logo,title,shoucename FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$operation         = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$tid_global = $_W['tid'];
if($operation == 'display'){
    if(!empty($_GPC['ssort'])){
        foreach($_GPC['ssort'] as $sid => $ssort){
            pdo_update($this->table_classcard_praise_type, array('ssort' => $ssort), array('id' => $sid));
        }
        message('批量更新排序成功', $this->createWebUrl('classepraisetype', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
    }
    $pindex    = max(1, intval($_GPC['page']));
    $psize     = 10;
    $condition = '';
    $sclist    = pdo_fetchall("SELECT * FROM " . tablename($this->table_classcard_praise_type) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
    foreach($sclist as $key => $value){
        $teacher               = pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " WHERE schoolid = :schoolid And id = :id", array(':schoolid' => $schoolid, ':id' => $value['tid']));
        $sclist[$key]['tname'] = $teacher['tname'];
    }
    $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_classcard_praise_type) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition");
    $pager = pagination($total, $pindex, $psize);
}elseif($operation == 'post'){
    load()->func('tpl');
    $id = intval($_GPC['id']);
    if(!empty($id)){
        $item = pdo_fetch("SELECT * FROM " . tablename($this->table_classcard_praise_type) . " WHERE id = '$id'");
    }else{
        $item = array(
            'ssort' => 0,
        );
    }
    if(checksubmit('submit')){
        
        if(empty($_GPC['title'])){
            $this->imessage('请输入评语内容');
        }
        $data = array(
            'weid'       => $weid,
            'schoolid'   => $_GPC['schoolid'],
            'title'      => trim($_GPC['title']),
            'createtime' => time()
        );
        if(!empty($id)){
            pdo_update($this->table_classcard_praise_type, $data, array('id' => $id));
        }else{
            pdo_insert($this->table_classcard_praise_type, $data);
        }
        $this->imessage('操作成功！@', $this->createWebUrl('classepraisetype', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
    }
}elseif($operation == 'delete'){
    $id       = intval($_GPC['id']);
    $theclass = pdo_fetch("SELECT id FROM " . tablename($this->table_classcard_praise_type) . " WHERE id = '{$id}'");
    if(empty($theclass['id'])){
        message('抱歉，本条不存在或是已经被删除！', $this->createWebUrl('classepraisetype', array('op' => 'pylist', 'schoolid' => $schoolid)), 'error');
    }
    pdo_delete($this->table_classcard_praise_type, array('id' => $id));
    $this->imessage('删除成功！', $this->createWebUrl('classepraisetype', array('op' => 'pylist', 'schoolid' => $schoolid)), 'success');
}
include $this->template('web/classepraisetype');
?>