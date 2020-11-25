<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */

global $_GPC, $_W;

$weid              = $_W['uniacid'];
$action            = 'classcardactivityresult';
$this1             = 'no10';
$schoolid          = intval($_GPC['schoolid']);
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$logo              = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$operation         = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$gaid			  = $_GPC['id'];
$tid_global = $_W['tid'];
if (!(IsHasQx($tid_global,1001701,1,$schoolid))){
    $this->imessage('非法访问，您无权操作该页面','','error');
}
if($operation == 'display'){
    $pindex    = max(1, intval($_GPC['page']));
    $psize     = 10;
    $condition = '';

    $params    = array();


    $list  = pdo_fetchall("SELECT * FROM " . tablename($this->table_classcard_activity_result) . " WHERE  activity_id='{$gaid}'   ORDER BY createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
    foreach( $list as $key => $value )
    {
//        $userinfo =  pdo_fetch('SELECT s_name FROM ' . tablename($this->table_student) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}'  And id='{$value['userid']}' ");
//
//        $usertemp = unserialize($userinfo['userinfo']);
//        $list[$key]['s_name'] = $userinfo['s_name'];
//        $list[$key]['phone'] = $usertemp['mobile'];
//        $list[$key]['pard'] = $pard;
        $student = pdo_fetch('SELECT s_name,bj_id FROM ' . tablename($this->table_students) . " WHERE  id='{$value['userid']}' ");
        $bjname =  pdo_fetch('SELECT sname FROM ' . tablename($this->table_classify) . " WHERE    sid='{$student['bj_id']}' ");
        $list[$key]['sname'] = $student['s_name'];
        $list[$key]['sbj'] = $bjname['sname'];

    }
    //var_dump($list);
    $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_classcard_activity_result) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}'  And activity_id='{$gaid}'  ");
    $pager = pagination($total, $pindex, $psize);
    //////////////////////////////////导出报名记录
}
elseif($operation == 'display1'){
    $pindex    = max(1, intval($_GPC['page']));
    $psize     = 10;
    $condition = '';

    $params    = array();


    $rs  = pdo_fetchall("SELECT r.id,r.createtime, r.userid,r.bj_id,r.options,a.attr,r.activity_id FROM " . tablename($this->table_classcard_activity_result).' as r'
        .' LEFT JOIN '.tablename($this->table_classcard_activity).' as a on a.id=r.activity_id '
        . " WHERE  r.activity_id='{$gaid}'   ORDER BY r.createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
    $list=[];
    foreach( $rs as $key => $value )
    {
//        $userinfo =  pdo_fetch('SELECT s_name FROM ' . tablename($this->table_student) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}'  And id='{$value['userid']}' ");
//
//        $usertemp = unserialize($userinfo['userinfo']);
//        $list[$key]['s_name'] = $userinfo['s_name'];
//        $list[$key]['phone'] = $usertemp['mobile'];
//        $list[$key]['pard'] = $pard;
        $student = pdo_fetch('SELECT s_name,bj_id FROM ' . tablename($this->table_students) . " WHERE  id='{$value['userid']}' ");
        $bjname =  pdo_fetch('SELECT sname FROM ' . tablename($this->table_classify) . " WHERE    sid='{$student['bj_id']}' ");
        $options=explode(',',$value['options']);
        $attr=unserialize($value['attr']);
        $tmp_options='';
        foreach ($options as $k=>$v){
            $tmp_options .=$attr[$k].',';
        }
        $list[$key]['options'] = $tmp_options;
        $list[$key]['id'] = $value['id'];
        $list[$key]['sname'] = $student['s_name'];
        $list[$key]['sbj'] = $bjname['sname'];
        $list[$key]['createtime'] = $value['createtime'];


    }
    //var_dump($list);
    $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_classcard_activity_result).
        " WHERE weid = '{$weid}' And schoolid = '{$schoolid}'  And activity_id='{$gaid}'  ");
    $pager = pagination($total, $pindex, $psize);
   // echo "<pre>";var_dump(pdo_debug());die;
    //////////////////////////////////导出报名记录
}elseif($operation == 'out_putcode1'){
    $listss = pdo_fetchall("SELECT * FROM " .tablename($this->table_classcard_activity_result)." WHERE weid = '{$weid}' And schoolid = '{$schoolid}' And activity_id='{$gaid}' ORDER BY createtime DESC");
    $ii = 0 ;
    foreach( $listss as $key => $value )
    {
        $student = pdo_fetch('SELECT s_name,bj_id FROM ' . tablename($this->table_students) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}'  And id='{$value['userid']}' ");
        $bjname =  pdo_fetch('SELECT sname FROM ' . tablename($this->table_classify) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}'  And sid='{$student['bj_id']}' ");
        $arr[$ii]['sname'] = $student['s_name'];
        $arr[$ii]['sbj'] = $bjname['sname'];
        $arr[$ii]['signtime'] = date('Y-m-d h:i:s',$value['createtime']);
        $ii++;
    }
    $ganame = pdo_fetch('SELECT title FROM ' . tablename($this->table_classcard_activity) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}'  And id='{$gaid}' ");
    $title=$ganame['title']."报名情况";
    $this->exportexcel($arr, array('学生姓名','所属班级','报名时间'), $title );
    exit();

}
elseif($operation == 'out_putcode'){
    $listss = pdo_fetchall("SELECT r.id,r.createtime, r.userid,r.bj_id,r.options,a.attr,r.activity_id FROM " . tablename($this->table_classcard_activity_result).' as r'
        .' LEFT JOIN '.tablename($this->table_classcard_activity).' as a on a.id=r.activity_id '
        ." WHERE a.weid = '{$weid}' And a.schoolid = '{$schoolid}' And r.activity_id='{$gaid}' ORDER BY r.createtime DESC");
    $ii = 0 ;
    foreach( $listss as $key => $value )
    {
        $student = pdo_fetch('SELECT s_name,bj_id FROM ' . tablename($this->table_students) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}'  And id='{$value['userid']}' ");
        $bjname =  pdo_fetch('SELECT sname FROM ' . tablename($this->table_classify) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}'  And sid='{$student['bj_id']}' ");
        $options=explode(',',$value['options']);
        $attr=unserialize($value['attr']);
        $tmp_options='';
        foreach ($options as $k=>$v){
            $tmp_options .=$attr[$k].',';
        }
        $arr[$ii]['sname'] = $student['s_name'];
        $arr[$ii]['sbj'] = $bjname['sname'];
        $arr[$ii]['options'] = $tmp_options;
        $arr[$ii]['signtime'] = date('Y-m-d h:i:s',$value['createtime']);
        $ii++;
    }
    $ganame = pdo_fetch('SELECT title FROM '.tablename($this->table_classcard_activity) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}'  And id='{$gaid}' ");
    $title=$ganame['title']."投票情况";
    $this->exportexcel($arr, array('学生姓名','所属班级','投票项','投票时间'), $title );
    exit();

}elseif($operation == 'delete'){
    $id      = intval($_GPC['sid']);
    $article = pdo_fetch("SELECT id FROM " . tablename($this->table_classcard_activity_result) . " WHERE id = '$id'");
    if(empty($article)){
        $this->imessage('抱歉，报名信息不存在或是已经被删除！', $this->createWebUrl('classcardactivityresult', array('op' => 'display', 'schoolid' => $schoolid)), 'error');
    }
    pdo_delete($this->table_classcard_activity_result, array('id' => $id));
    $this->imessage('报名信息删除成功！', $this->createWebUrl('classcardactivityresult', array('op' => 'display','id'=>$gaid, 'schoolid' => $schoolid)), 'success');
}
elseif($operation == 'delete1'){
    $id      = intval($_GPC['sid']);
    $article = pdo_fetch("SELECT id FROM " . tablename($this->table_classcard_activity_result) . " WHERE id = '$id'");
    if(empty($article)){
        $this->imessage('抱歉，信息不存在或是已经被删除！', $this->createWebUrl('classcardactivityresult', array('op' => 'display1', 'schoolid' => $schoolid)), 'error');
    }
    pdo_delete($this->table_classcard_activity_result, array('id' => $id));
    $this->imessage('信息删除成功！', $this->createWebUrl('classcardactivityresult', array('op' => 'display1','id'=>$gaid, 'schoolid' => $schoolid)), 'success');
}
include $this->template('web/classcardactivityresult');
?>