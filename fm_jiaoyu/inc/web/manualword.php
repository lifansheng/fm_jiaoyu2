<?php
/*
 * @Discription:
 * @Author: Hannibal·Lee
 * @Date: 2020-02-15 11:03:14
 * @LastEditTime: 2020-04-16 16:36:36
 */
global $_GPC, $_W;
$weid              = $_W['uniacid'];
$action            = 'manualword';
$this1             = 'no14';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
$logo              = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$commenttype = pdo_fetchall("SELECT sid,sname FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' AND weid = '{$weid}' AND type = 'comment' ORDER BY sid DESC");

//评语类型
$tid_global = $_W['tid'];
if($operation == 'display'){
    $pindex    = max(1, intval($_GPC['page']));
    $psize     = 20;
    $condition = '';
    if(!empty($_GPC['groupid'])){
        if($_GPC['groupid'] == -1){
            $condition .= " and s.sid = 0 ";
        }else {
            $condition .= " and s.sid = {$_GPC['groupid']} ";
        }
    };
  
    $list = pdo_fetchall("SELECT s.id,s.title,s.sid,s.ssort,c.sname FROM " . GetTableName('shoucepyk') . " as s LEFT JOIN ".GetTableName('classify'). " as c on c.sid = s.sid where s.schoolid = '{$schoolid}' {$condition}  ORDER BY s.ssort DESC , s.sid DESC LIMIT ". ($pindex - 1) * $psize ." , ".$psize);

    $total = pdo_fetchcolumn('SELECT COUNT(id) FROM ' . GetTableName('shoucepyk') . " as s WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition");
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/manualword');
}elseif($operation == 'editType'){
    if($_GPC['typeid'] == 0){ //新增
        $insertData = array(
            'weid' => $weid,
            'schoolid' => $schoolid,
            'type' => 'comment',
            'sname' => '新增分类'
        );
        pdo_insert(GetTableName('classify',false),$insertData);
        $typeid = pdo_insertid();
       
    }else {
        $updateData = array(
            'sname' => $_GPC['typeName']
        );
        pdo_update(GetTableName('classify',false),$updateData,array('sid'=>$_GPC['typeid']));
        $typeid = $_GPC['typeid'];
    }
    $result['status'] = true;
    $result['data']['typeid'] = $typeid;
    die(json_encode($result));
}elseif($operation == 'deleteType'){
    if($_GPC['typeid'] != 0){
        $check = pdo_fetch("SELECT sid FROM ".GetTableName('classify')." WHERE sid = '{$_GPC['typeid']}' ");
        if(!empty($check)){
            pdo_delete(GetTableName('classify',false),array('sid'=>$_GPC['typeid']));
            $result['status'] = true;
        }else {
            $result['status'] = false;
            $result['msg'] = "当前分类不存在或已删除";
        }
    }else {
        $result['status'] = false;
        $result['msg'] = "未选择需要删除的分类";
    }
    die(json_encode($result));
}elseif($operation == 'GetAllType'){
    $commenttype = pdo_fetchall("SELECT sid,sname FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' AND weid = '{$weid}' AND type = 'comment' ORDER BY sid DESC");
    $result['status'] = true;
    $result['data']['typelist'] = $commenttype;
    die(json_encode($result));
}elseif($operation == 'AddComment'){
    if($_GPC['editid'] == 0){
        $data = array(
            'weid'       => $weid,
            'schoolid'   => $_GPC['schoolid'],
            'title'      => trim($_GPC['comment']),
            'sid'      => trim($_GPC['commentid']),
            'ssort'      => intval($_GPC['ssort']),
            'createtime' => time()
        );
        pdo_insert($this->table_scpy, $data);
        $result['msg'] = '添加成功';
    }else {
        $data = array(
            'weid'       => $weid,
            'schoolid'   => $_GPC['schoolid'],
            'title'      => trim($_GPC['comment']),
            'sid'      => trim($_GPC['commentid']),
            'ssort'      => intval($_GPC['ssort']),
            'createtime' => time()
        );
        pdo_update(GetTableName('shoucepyk',false),$data,array('id'=>$_GPC['editid']));
        $result['msg'] = '修改成功';
    }
    $result['status'] = true;
    die(json_encode($result));
}elseif($operation == 'delete'){
    $id = $_GPC['id'];
    if(!empty($id)){
        $check = pdo_fetch("SELECT id FROM ".GetTableName('shoucepyk')." WHERE  id = '{$id}'  ");
        if(!empty($check)){
            pdo_delete(GetTableName('shoucepyk',false),array('id'=>$id));
            $result['msg'] = "删除成功";
        }else {
            $result['msg'] = "评语不存在或已删除";
        }
    }else{
        $result['msg'] = "未指定删除评语";
    }
    $result['status'] = true;
    die(json_encode($result));
}elseif($operation == 'deleteall'){
    $rowcount    = 0;
    $notrowcount = 0;
    foreach($_GPC['idArr'] as $k => $id){
        $id = intval($id);
        if(!empty($id)){
            $goods = pdo_fetch("SELECT * FROM " . GetTableName('shoucepyk') . " WHERE id = :id", array(':id' => $id));
            if(empty($goods)){
                $notrowcount++;
                continue;
            }
            pdo_delete(GetTableName('shoucepyk',false), array('id' => $id));
            $rowcount++;
        }
    }
    $message = "操作成功！共删除{$rowcount}条数据,{$notrowcount}条数据不能删除!";

    $data ['result'] = true;

    $data ['msg'] = $message;

    die (json_encode($data));
}

?>