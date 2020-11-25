<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_GPC, $_W;

$weid              = $_W['uniacid'];
$action            = 'mcmanage';
$this1             = 'no12';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
$logo              = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
//班级
$bj    = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = '{$weid}' AND schoolid ={$schoolid} And type = 'theclass' ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'theclass', ':schoolid' => $schoolid));
//年级
$xueqi    = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = '{$weid}' AND schoolid ={$schoolid} And type = 'semester' ORDER BY ssort DESC");
//疫情设置
$schoolset = pdo_fetch("SELECT yqdkset FROM " . tablename($this->table_schoolset) . " WHERE schoolid = '{$schoolid}'");
$yqset = unserialize($schoolset['yqdkset']);
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$tid_global = $_W['tid'];
if($operation == 'display'){
    $pindex    = max(1, intval($_GPC['page']));
    $psize     = 20;
    $condition = '';
    if(!empty($_GPC['s_name'])){
		$students = pdo_fetch("SELECT id FROM " . tablename($this->table_students) . " WHERE schoolid = :schoolid And s_name = :s_name ORDER BY id DESC LIMIT 1", array(':schoolid' => $schoolid,':s_name' => $_GPC['s_name']));
		$condition .= " AND sid = '{$students['id']}'";		
    }
    if(!empty($_GPC['nj_id']) && empty($_GPC['bj_id'])){
        $bjinfo   = pdo_fetchall("SELECT sid FROM " . tablename($this->table_classify) . " where parentid = '{$_GPC['nj_id']}' and type = 'theclass' ");
        $bj_str = '';
        foreach($bjinfo as $value){
            $bj_str .=$value['sid'].",";
        }
        $bj_str = trim($bj_str,',');
        $condition .= " AND FIND_IN_SET(bj_id,'{$bj_str}')";
    }
    if(!empty($_GPC['bj_id'])){
        $condition .= " AND bj_id = '{$_GPC['bj_id']}'";
    }
    
    if(!empty($_GPC['createtime'])){
		$starttime = strtotime($_GPC['createtime']['start']);
		$endtime   = strtotime($_GPC['createtime']['end']) + 86399;
		$condition .= " AND createtime <= '{$endtime}'  AND createtime >= '{$starttime}'";
    }else{
        $starttime = strtotime('-30 day');
        $endtime   = TIMESTAMP;
    }

    $list = pdo_fetchall("SELECT * FROM " . GetTableName('yqdk') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
    foreach($list as $key => $row){
        $student = pdo_fetch("SELECT s_name FROM " . tablename($this->table_students) . " where id = :id ", array(':id' => $row['sid']));
        $bjinfo  = pdo_fetch("SELECT sname,parentid FROM " . tablename($this->table_classify) . " where sid = :sid ", array(':sid' => $row['bj_id']));
        $njinfo  = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where sid = :sid ", array(':sid' => $bjinfo['parentid']));
        $content = unserialize($row['content']);
        $list[$key]['content']   = $content;
        $list[$key]['s_name']   = $student['s_name'];
        $list[$key]['bj_name']  = $bjinfo['sname'];
        $list[$key]['nj_name']  = $njinfo['sname'];
    }
    $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . GetTableName('yqdk') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition");
    $pager = pagination($total, $pindex, $psize);
}elseif($operation == 'delete'){
    $id = intval($_GPC['id']);
    if(empty($id)){
        $this->imessage('抱歉，本条信息不存在在或是已经被删除！');
    }
    pdo_delete(GetTableName('yqdk',false), array('id' => $id));
    $this->imessage('删除成功！', referer(), 'success');
}elseif($operation == 'deleteall'){
    $rowcount    = 0;
    $notrowcount = 0;
    foreach($_GPC['idArr'] as $k => $id){
        $id = intval($id);
        if(!empty($id)){
            $goods = pdo_fetch("SELECT * FROM " . GetTableName('yqdk') . " WHERE id = :id", array(':id' => $id));
            if(empty($goods)){
                $notrowcount++;
                continue;
            }
            pdo_delete(GetTableName('yqdk',false), array('id' => $id, 'weid' => $weid));
            $rowcount++;
        }
    }
    $message = "操作成功！共删除{$rowcount}条数据,{$notrowcount}条数据不能删除!";

    $data ['result'] = true;

    $data ['msg'] = $message;

    die (json_encode($data));
}elseif($operation == 'AddOtherSet'){
    $data = array(
        'yqdkset' => serialize($_GPC['yqdkset']),
    );
    if(pdo_update(GetTableName('schoolset',false),$data,array('schoolid'=>$_GPC['schoolid']))){
        $result['msg'] = '设置成功';
        $result['result'] = true;
    }else{
        $result['msg'] = '数据有误';
        $result['result'] = false;
    }
    die(json_encode($result));
}
include $this->template('web/yqdk');
?>