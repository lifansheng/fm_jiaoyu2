<?php
/*
 * @Discription:
 * @Author: Hannibal·Lee
 * @Date: 2020-02-15 11:03:14
 * @LastEditTime: 2020-04-15 15:25:30
 */
global $_GPC, $_W;
$weid              = $_W['uniacid'];
$action            = 'manualmuban';
$this1             = 'no14';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
$logo              = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';

//评语类型
$tid_global = $_W['tid'];
if($operation == 'display'){
    $pindex    = max(1, intval($_GPC['page']));
    $psize     = 20;
    $condition = '';
    if(!empty($_GPC['MubanName'])){
        $condition .= " and title like '%{$_GPC['MubanName']}%' ";
    };
    if(!empty($_GPC['xctype'])){
        $condition .= " AND type = '{$_GPC['xctype']}'";
    }
    $list = pdo_fetchall("SELECT *,(select count(id) FROM ".GetTableName('mubanpage')." WHERE mid = m.id  ) as pagec FROM ".GetTableName('muban')." as m WHERE weid = :weid and schoolid = :schoolid $condition group by id ASC ",array(":weid" => $weid,":schoolid" => $schoolid));
    $total = pdo_fetchcolumn('SELECT COUNT(id) FROM ' . GetTableName('muban') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition");
    $pager = pagination($total, $pindex, $psize);
}elseif($operation == 'checkpage'){
    $mid = $_GPC['id'];
    $MubanInfo = pdo_fetch("SELECT title FROM ".GetTableName('muban')." WHERE id = '{$mid}' ");
    $list =pdo_fetchall("SELECT * FROM ".GetTableName('mubanpage')." WHERE mid = {$mid} ORDER BY ssort ASC ");
}elseif($operation == 'delpage'){
    $id = $_GPC['id'];
    pdo_delete(GetTableName('mubanpage',false),array('id'=>$id));
    $this->imessage('删除成功！', referer(), 'success');
}elseif($operation == 'ChangeSsort'){
    foreach ($_GPC['SortList'] as $key => $value) {
        pdo_update(GetTableName('mubanpage',false),array('ssort'=>$value['index']),array('id'=>$value['id']));
    }
}elseif($operation == 'delete'){
    $id = $_GPC['id'];
    pdo_delete(GetTableName('muban',false),array('id'=>$id));
    $this->imessage('删除成功！', referer(), 'success');
}elseif($operation == 'GetMuBanInfo'){
    $item = pdo_fetch("SELECT title,img,type,description FROM ".GetTableName('muban')." WHERE id = '{$_GPC['id']}'");
    $item['picurl'] = tomedia($item['img']);
    $result['data'] = $item;
    die(json_encode($result));
}
include $this->template('web/manualmuban');

?>