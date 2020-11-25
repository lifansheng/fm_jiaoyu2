<?php
/*
* @Discription:  
* @Author: Hannibal·Lee
* @Date: 2020-01-13 15:49:46
 * @LastEditTime : 2020-01-14 13:30:40
*/
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */

global $_GPC, $_W;
$weid = $_W['uniacid'];
load()->func('tpl');

$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';    
if ($operation == 'display') {
    $item = pdo_fetch("SELECT id,facesecret,faceid,faceset FROM " . tablename($this->table_set) . " WHERE weid = :weid ",array(':weid' => $weid));
    if(checksubmit('submit')){
        $id = intval($_GPC['id']);
        $data['faceset'] = trim($_GPC['faceset']);
        $data['faceid'] = trim($_GPC['faceid']);
        $data['facesecret'] = trim($_GPC['facesecret']);
        if(!empty($id)){
            pdo_update($this->table_set, $data, array('id' => $id));
        }else{
            $data['weid'] = $weid;
            pdo_insert($this->table_set, $data);
        }
        message('设置成功', '', 'success');
    }
} else{
    message('操作失败, 非法访问.');
}			
include $this->template ( 'web/faceset' );
?>