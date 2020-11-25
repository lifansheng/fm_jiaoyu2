<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_GPC, $_W;
$action            = 'classcardcountdown';
$this1             = 'no10';
$schoolid          = intval($_GPC['schoolid']);
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$weid              = $_W['uniacid'];
$logo              = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where id = :id ", array(':id' => $schoolid));
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$tid_global = $_W['tid'];

$bj = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'theclass', ':schoolid' => $schoolid));

if(empty($schoolid)){
    $this->imessage('非法操作!', referer(), 'error');
}
if($operation == 'post'){
    //检索已经设置的班级

    $banji  = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid And schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'theclass', ':schoolid' => $schoolid));

    load()->func('tpl');
    $id = intval($_GPC['id']);

    $item = pdo_fetch("SELECT * FROM " . tablename($this->table_classcard_countdown) . " WHERE id = :id", array(':id' => $id));

    $bj_ids = pdo_fetchall("SELECT bj_id FROM " . tablename($this->table_classcard_countdown));
    $uniarr=[];
    if($id){
        $uniarr=[$item['bj_id']];
    }else{
        foreach ($bj_ids as $k=>$v){
            $uniarr[]=$v['bj_id'];
        }
    }
    if(checksubmit('submit')){
        $bjarray=$_GPC['arr'];
        if(empty($bjarray)){
            $this->imessage('班级不能为空！', referer(), 'error');
        }

        if(empty($_GPC['project'])){
            $this->imessage('请输入项目名称！', referer(), 'error');
        }
        if(empty($_GPC['count_down'])){
            $this->imessage('请输入倒计时！', referer(), 'error');
        }

        foreach ($bjarray as $k=>$v){
            $is_bj = pdo_fetch("SELECT bj_id FROM " . tablename($this->table_classcard_countdown) . " WHERE bj_id = :bj_id", array(':bj_id' => $v));
            if($is_bj){
                $data = array(
                    'weid'      => $weid,
                    'schoolid'  => $schoolid,
                    'project'=> trim($_GPC['project']),
                    'count_down' =>strtotime($_GPC['count_down']),
                );
                pdo_update($this->table_classcard_countdown, $data, array('bj_id' =>  $v));
            }else{
                $data = array(
                    'weid'      => $weid,
                    'schoolid'  => $schoolid,
                    'project'=> trim($_GPC['project']),
                    'bj_id'  => $v,
                    'count_down' =>strtotime($_GPC['count_down']),
                );
                pdo_insert($this->table_classcard_countdown, $data);
            }
        }
        $urlMsg = '操作成功';
        $this->imessage($urlMsg, $this->createWebUrl('classcardcountdown', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
    }
}elseif($operation == 'display'){
    $pindex    = max(1, intval($_GPC['page']));
    $psize     = 8;
    $condition = '';

    if(!empty($_GPC['bj_id'])){
        $bj_id     = $_GPC['bj_id'];
        $condition .= " AND bj_id = '{$bj_id}' ";
    }


    $list = pdo_fetchall("SELECT * FROM " . tablename($this->table_classcard_countdown) . " WHERE weid = '{$weid}' AND schoolid ={$schoolid} $condition ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);

    foreach($list as $key => $value){
        $bjname=pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And sid = :sid ", array(':weid' => $weid, ':sid' =>$value['bj_id'] , ':schoolid' => $schoolid));
        $list[$key]['bj'] = $bjname['sname'];
    }
    $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_classcard_countdown) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition");
    $pager = pagination($total, $pindex, $psize);

}elseif($operation == 'delete'){
    $tid = intval($_GPC['id']);
    $row = pdo_fetch("SELECT * FROM " . tablename($this->table_classcard_countdown) . " WHERE id = :id", array(':id' => $tid));
    if(empty($row)){
        $this->imessage('抱歉，口号不存在或是已经被删除！', referer(), 'error');
    }


    pdo_delete($this->table_classcard_countdown, array('id' => $tid));

    $urlMsg = '删除成功！';

    $this->imessage($urlMsg, $this->createWebUrl('classcardcountdown', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
}
include $this->template('web/classcardcountdown');
?>