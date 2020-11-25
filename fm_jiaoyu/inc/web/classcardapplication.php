<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */

global $_GPC, $_W;
$weid              = $_W['uniacid'];
$action            = 'classcardapplication';
$this1             = 'no10';
$schoolid          = intval($_GPC['schoolid']);
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$logo              = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
load()->func('tpl');
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$tid_global = $_W['tid'];

if($operation == 'display'){

    $list = pdo_fetchall("SELECT * FROM " . tablename($this->table_classcard_application) . " WHERE weid = '{$weid}' AND school_id = '{$schoolid}' ");

}elseif($operation == 'post'){

    $banji  = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid And schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'theclass', ':schoolid' => $schoolid));


    $id = intval($_GPC['id']);
    if(checksubmit('submit')){
        $data = array(
            'weid'         => intval($weid),
            'uniacid'      => intval($weid),
            'school_id'     => $schoolid,
            'application_name'   => $_GPC['application_name'],
            'application_id'         => $_GPC['application_id'],
            'application_icon'         => $_GPC['application_icon'],
            'download_url'        => $_GPC['download_url'],
            'application_id'      => $_GPC['application_id'],
            'version_code' => $_GPC['version_code'],
            'version_name' => $_GPC['version_name'],
            'bjarray' =>  implode(',', $_GPC['arr'])
        );

        if(!empty($id)){
            pdo_update($this->table_classcard_application, $data, array('id' => $id));
            //load()->func('file');
           // file_delete($_GPC['application_icon']);
        }else{
            pdo_insert($this->table_classcard_application, $data);
            //         $id = pdo_insertid();
        }
        $this->imessage('更新成功！', referer(), 'success');
    }
    $sql="select * from " . tablename($this->table_classcard_application) . " where id=:id limit 1";
    $vo = pdo_fetch($sql, array(":id" => $id));
    $uniarr = explode(',', $vo['bjarray']);
    //include $this->template('banner_post');

}elseif($operation == 'delete'){
    $id     = intval($_GPC['id']);
    $item = pdo_fetch("SELECT id  FROM " . tablename($this->table_classcard_application) . " WHERE id = '{$id}'");
    
    if(empty($item)){
        $this->imessage('抱歉，不存在或是已经被删除！', referer(), 'error');
    }
    pdo_delete($this->table_classcard_application, array('id' => $id));
    $this->imessage('删除成功!！', referer(), 'success');
}else{
    $this->imessage('请求方式不存在');
}
include $this->template('web/classcardapplication');
?>