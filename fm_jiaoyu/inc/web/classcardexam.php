<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_GPC, $_W;
$action            = 'classcardexam';
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
if($operation == 'post1'){
    if($_GPC['exam_name_id']!=''||!empty($_GPC['exam_name_id'])){
        pdo_query('delete '.tablename($this->table_classcard_exam_detail)
            ."," . tablename($this->table_classcard_exam)
            ." FROM " . tablename($this->table_classcard_exam_detail)
            ." LEFT JOIN " . tablename($this->table_classcard_exam)
            ." ON " . tablename($this->table_classcard_exam_detail)
            .".exam_id = " . tablename($this->table_classcard_exam)
            .".id WHERE " . tablename($this->table_classcard_exam)
            .".exam_name = :exam_name",array(':exam_name'=>trim($_GPC['exam_name_id'])));
    }
    $param=$_GPC['param'];
    $exam_name=$_GPC['exam_name'];
    $course_ids=[];
    foreach ($param as $k=>$v){
        $tm='';
        $tm=explode(',',$v);
        $data = array(
            'start_time'=>strtotime($tm[0]." ".$tm[1].":00"),
            'end_time'  => strtotime($tm[0]." ".$tm[2].":00"),
            'course_id'=>$tm[3],
            'exam_name'=>trim($exam_name)
        );
        $id=pdo_insert($this->table_classcard_exam, $data);
        $course_ids[pdo_insertid()]=$tm[3];


    }
    $param1=$_GPC['param1'];
    foreach ($param1 as $k=>$v){
        $tm=explode(',',$v);
        $step=0;
        foreach ($course_ids as $i=>$j){
            $aa=explode('|',$tm[2+$step]);
            $data = array(
                'bj_id'=>$tm[0],
                'code'=>$tm[1],
                'teacher_id'=>$aa[0],
                'teacher_id1'=>$aa[1],
                'course_id'=>$j,
                'exam_id'=>$i,
            );
            pdo_insert($this->table_classcard_exam_detail, $data);
            $step +=1;
        }

    }
    echo json_encode(['msg'=>'','code'=>200]);
    die;

}
if($operation == 'post'){
    //检索已经设置的班级
    $teachers = pdo_fetchall("SELECT id,tname FROM " . GetTableName ('teachers') . " where weid = :weid And schoolid = :schoolid ORDER BY  CONVERT(tname USING gbk)  ASC ", array(
        ':weid' => $weid,
        ':schoolid' => $schoolid
    ) );
    $allkm = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " WHERE weid = :weid And schoolid = :schoolid And type = :type", array(':weid' => $weid, ':schoolid' => $schoolid, ':type' => 'subject'));
    $banji  = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid And schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'theclass', ':schoolid' => $schoolid));

    load()->func('tpl');
    $exam_name = $_GPC['exam_name'];

    $item = pdo_fetchall("SELECT * FROM " . tablename($this->table_classcard_exam) . " WHERE exam_name = :exam_name", array(':exam_name' => $exam_name));

    //考试详情
    $exam_details = pdo_fetchall("SELECT * FROM " . tablename($this->table_classcard_exam_detail) .
        " as a left join ".tablename($this->table_classcard_exam)." as b on a.exam_id=b.id WHERE b.exam_name = :exam_name", array(':exam_name' => $exam_name));
    $exam_bj = pdo_fetchall("SELECT * FROM " . tablename($this->table_classcard_exam_detail) .
        " as a left join ".tablename($this->table_classcard_exam)." as b on a.exam_id=b.id WHERE b.exam_name = :exam_name GROUP BY a.bj_id", array(':exam_name' => $exam_name));

    $bj_ids = pdo_fetchall("SELECT bj_id FROM " . tablename($this->table_classcard_exam));
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
        $this->imessage($urlMsg, $this->createWebUrl('classcardexam', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
    }
}elseif($operation == 'display'){
    $pindex    = max(1, intval($_GPC['page']));
    $psize     = 8;
    $condition = '';
    if(!empty($_GPC['exam_name'])){
        $exam_name     = $_GPC['exam_name'];
        $condition .= " AND exam_name like '%{$exam_name}%' ";
    }


   // $list = pdo_fetchall("SELECT * FROM " . tablename($this->table_classcard_exam) . " WHERE weid = '{$weid}' AND schoolid ={$schoolid} $condition ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);

    $list = pdo_fetchall("SELECT DISTINCT exam_name FROM " . tablename($this->table_classcard_exam) . " WHERE 1=1 $condition ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
    //$total =pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_classcard_countdown) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition");
    $total =pdo_fetchcolumn('SELECT COUNT(DISTINCT exam_name) FROM ' . tablename($this->table_classcard_exam) . " WHERE 1=1 $condition");
    $pager = pagination($total, $pindex, $psize);

}elseif($operation == 'delete'){

    $exam_name = $_GPC['exam_name'];
    $row = pdo_fetch("SELECT * FROM " . tablename($this->table_classcard_exam) . " WHERE exam_name = :exam_name", array(':exam_name' => $exam_name));
    if(empty($row)){
        $this->imessage('抱歉，不存在或是已经被删除！', referer(), 'error');
    }

   $rs= pdo_query('delete '.tablename($this->table_classcard_exam_detail)
        ."," . tablename($this->table_classcard_exam)
        ." FROM " . tablename($this->table_classcard_exam_detail)
        ." LEFT JOIN " . tablename($this->table_classcard_exam)
        ." ON " . tablename($this->table_classcard_exam_detail)
        .".exam_id = " . tablename($this->table_classcard_exam)
        .".id WHERE " . tablename($this->table_classcard_exam)
        .".exam_name =:exam_name",array(':exam_name'=>trim($exam_name)));
         $urlMsg = '删除成功！';
//    echo "<pre>";
//    var_dump(pdo_debug());
//    die;
    $this->imessage($urlMsg, $this->createWebUrl('classcardexam', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
}
include $this->template('web/classcardexam');
?>