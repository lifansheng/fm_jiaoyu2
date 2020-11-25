<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_GPC, $_W;
$weid = $_W['uniacid'];
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$action            = 'newtype';
$this1             = 'no1';
$GLOBALS['frames'] = $this->getNaveMenu('',$action,true);
$logo = pdo_fetch("SELECT a.uniacid as weid ,a.name as title FROM " . tablename('uni_account') . " as a LEFT JOIN". tablename('account'). " as b ON a.uniacid = b.uniacid AND a.default_acid = b.acid where b.isdeleted != 1 And a.default_acid != 0 AND a.uniacid = {$weid}");
$member = pdo_fetch("SELECT username FROM " . tablename('users') . " WHERE uid = {$_W['uid']}");
$logo['logo'] = tomedia('headimg_'.$weid.'.jpg');

if ($operation == 'display') {
    if (!empty($_GPC['ssort'])) {
        foreach ($_GPC['ssort'] as $id => $ssort) {
            pdo_update($this->table_type, array('ssort' => $ssort), array('id' => $id));
        }
        $this->imessage('排序更新成功！', $this->createWebUrl('newtype', array('op' => 'display')), 'success');
    }

    $type = pdo_fetchall("SELECT * FROM " . tablename($this->table_type) . " WHERE weid = '{$weid}'  ORDER BY parentid ASC, ssort DESC");

} elseif ($operation == 'post') {
    $parentid = intval($_GPC['parentid']);
    $id = intval($_GPC['id']);
    if (!empty($id)) {
        $type = pdo_fetch("SELECT * FROM " . tablename($this->table_type) . " WHERE id = '{$id}'");
    } else {
        $type = array(
            'ssort' => 0,
        );
    }

    if (checksubmit('submit')) {
        if (empty($_GPC['catename'])) {
            $this->imessage('请输入类型名称！');
        }

        $data = array(
            'weid' => $weid,
            'name' => $_GPC['catename'],
            'ssort' => intval($_GPC['ssort']),
            'parentid' => intval($parentid),
        );

        if (!empty($id)) {
            unset($data['parentid']);
            pdo_update($this->table_type, $data, array('id' => $id));
        } else {
            pdo_insert($this->table_type, $data);
        }
        $this->imessage('更新学校类型成功！', $this->createWebUrl('newtype', array('op' => 'display')), 'success');
    }
} elseif ($operation == 'delete') {
    $id = intval($_GPC['id']);
    $type = pdo_fetch("SELECT id, parentid FROM " . tablename($this->table_type) . " WHERE id = '{$id}'");
    if (empty($type)) {
        $this->imessage('抱歉，数据不存在或是已经被删除！', $this->createWebUrl('newtype', array('op' => 'display')), 'error');
    }
    pdo_delete($this->table_type, array('id' => $id, 'parentid' => $id), 'OR');
    $this->imessage('数据删除成功！', $this->createWebUrl('newtype', array('op' => 'display')), 'success');
}
include $this->template ( 'web/newtype' );
?>