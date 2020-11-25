<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_GPC, $_W;

$weid = $_W['uniacid'];
$action = 'zhuanbjlog';
$this1 = 'no2';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'],$action);
$schoolid = intval($_GPC['schoolid']);
$logo = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$checkNowTime = strtotime(date("Y-m-d",time()));
$kcall = pdo_fetchall("SELECT * FROM " . tablename($this->table_tcourse) . " where schoolid ='{$schoolid}' and weid = '{$weid}' and end > $checkNowTime ");
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$tid_global = $_W['tid'];
if ($operation == 'display') { //默认
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $condition = '';
    if(!empty($_GPC['s_name'])){
        $student = pdo_fetchAll("SELECT id FROM ".GetTableName('students')." WHERE s_name LIKE '%{$_GPC['s_name']}%' ");
        $sidstr = arrayToString($student);
        $condition .= " AND FIND_IN_SET(sid,'{$sidstr}')";
    }
    
    if(!empty($_GPC['beforekcid'])){
        $condition .= " AND beforekcid = '{$_GPC['beforekcid']}'";
    }

    if(!empty($_GPC['afterkcid'])){
        $condition .= " AND afterkcid = '{$_GPC['afterkcid']}'";
    }

    if(!empty($_GPC['createtime'])) {
        $starttime = strtotime($_GPC['createtime']['start']);
        $endtime = strtotime($_GPC['createtime']['end']) + 86399;
        $condition .= " AND createtime > '{$starttime}' AND createtime < '{$endtime}'";
    } else {
        $starttime = strtotime('-60 day');
        $endtime = TIMESTAMP;
    }

    $list = pdo_fetchall("SELECT * FROM " . GetTableName('changbjlog') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}'  $condition  ORDER BY id LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
    foreach($list as $index => $row){
        $stu = pdo_fetch("SELECT s_name FROM ".GetTableName('students')." WHERE id = '{$row['sid']}' ");
        $beforekc = pdo_fetch("SELECT name FROM ".GetTableName('tcourse')." WHERE id = '{$row['beforekcid']}' ");
        $afterkc = pdo_fetch("SELECT name FROM ".GetTableName('tcourse')." WHERE id = '{$row['afterkcid']}' ");
        if($row['tid'] != -1){
            $tea = pdo_fetch("SELECT tname FROM ".GetTableName('teachers')." WHERE id='{$row['tid']}' ");
            $list[$index]['czy'] = $tea['tname'];
        }else{
            $list[$index]['czy'] = '管理员';
        }
        $list[$index]['beforekc'] = $beforekc['name'];
        $list[$index]['afterkc'] = $afterkc['name'];
        $list[$index]['s_name'] = $stu['s_name'];
        $list[$index]['createtime'] = date('Y-m-d H:i',$row['createtime']);
    }
    $total = pdo_fetchcolumn("SELECT count(id) FROM " . GetTableName('changbjlog') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}'  $condition ");
    //var_dump($total);
    $pager = pagination($total, $pindex, $psize);

} elseif($operation == 'delete'){
    $id = intval($_GPC['id']);
    $schoolid = intval($_GPC['schoolid']);
    $row = pdo_fetch("SELECT * FROM " . GetTableName('changbjlog') . " WHERE id = :id", array(':id' => $id));
    pdo_delete(GetTableName('changbjlog',false), array('id' => $id));
    $this->imessage('删除成功！', $this->createWebUrl('zhuanbjlog', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
} elseif($operation == 'deleteall'){
    $rowcount    = 0;
    $notrowcount = 0;
    foreach($_GPC['idArr'] as $k => $id){
        $id = intval($id);
        if(!empty($id)){
            pdo_delete(GetTableName('changbjlog',false), array('id' => $id));
            $rowcount++;
        }
    }
    $message = "操作成功！共删除{$rowcount}条数据!";
    $data ['result'] = true;
    $data ['msg'] = $message;
    die (json_encode($data));
}
include $this->template ( 'web/zhuanbjlog' );
?>