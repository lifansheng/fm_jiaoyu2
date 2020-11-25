<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */



//目前本页面只做了  班级通知  且为sk77独享


/*
 * $result    获取到的学生id,数组，已去重，最终结果。
 * $total     总人数
 * $pageSize  分页大小
 * $pageIndex 当前页
 * $NoticeIdArr  通知id数组
 *
 */
global $_GPC,$_W;
$notice_id = $_GPC['notice_id'];
$schoolid = $_GPC['schoolid'];
$weid = $_GPC['weid'];
$tname = $_GPC['tname'];
$type = $_GPC['type'];
$pageIndex = max(1, intval($_GPC['page']));
$pageSize = 2;
$total = 0;
$result = [];
$NoticeIdArr = $_GPC['NoticeIdArr'];
$sendArray = $_GPC['sendArray'];
$sendType = $_GPC['sendType'] ;
//var_dump($_W['schooltype']);
$schooltype = $_W['schooltype'];
//班级通知




if($type == 1){
    if($sendType == 1){
        mload()->model('stu');
        $arr = StuInfoByclassArr($sendArray,$schoolid,$_W['schooltype']);
        $result = array_unique($arr);
       // var_dump($result);
        $total = count($result);
    }elseif($sendType ==2 ){
        $result = [];
        array_walk_recursive($sendArray, function($value) use (&$result) {
            array_push($result, $value);
        });
        $total = count($result);
    }
    $tp = ceil($total/$pageSize);
   // session_start();
    //if( !$_SESSION['stuarr'] && $_SESSION['stuarr'] == ""){
      //  $_SESSION['stuarr'] = $result;
   // }
}



// var_dump($NoticeIdArr);
//die();


/*
if($type == 1){
    if($schooltype == true){
        $kc_id = $_GPC['kc_id'];
        $pindex = max(1, intval($_GPC['page']));
        $total = pdo_fetchall("SELECT distinct sid FROM ".tablename($this->table_order)." where weid = :weid And schoolid = :schoolid And kcid = :kc_id and type = 1 and status = 2 ",array(':weid'=>$weid, ':schoolid'=>$schoolid, ':kc_id'=>$kc_id));
    }elseif($schooltype == false){
        $bj_id = $_GPC['bj_id'];
        $pindex = max(1, intval($_GPC['page']));
        if (is_array($bj_id)) {
            $mutiBj_id = 1 ;
            $from = $_GPC['from'];
            foreach( $bj_id as $key => $value )
            {
                $temp_bj .= $value.",";
            }
            $bj_id_fin = trim($temp_bj,",");
            //var_dump($bj_id_fin);
            $total = pdo_fetchcolumn("SELECT count(distinct id ) FROM ".tablename($this->table_students)." where weid = :weid And schoolid = :schoolid And FIND_IN_SET(bj_id,:bj_id)",array(':weid'=>$weid, ':schoolid'=>$schoolid, ':bj_id'=>$bj_id_fin));
            $bj_id = json_encode($bj_id);
        }else{
            $total = pdo_fetchcolumn("SELECT COUNT(1) FROM ".tablename($this->table_students)." where weid = :weid And schoolid = :schoolid And bj_id = :bj_id",array(':weid'=>$weid, ':schoolid'=>$schoolid, ':bj_id'=>$bj_id));
        }
        $tp = ceil($total/$psize);
    }

    //校园通知
}elseif($type == 2){
    $groupid = $_GPC['groupid'];
    if ($groupid >= 4) {
        $total = pdo_fetchcolumn("SELECT id FROM ".tablename($this->table_teachers)." where weid = :weid And schoolid = :schoolid And fz_id = :fz_id " ,array(':weid'=>$weid, ':schoolid'=>$schoolid, ':fz_id'=>$groupid));
    }else{
        if ($groupid == 1) {
            $total = pdo_fetchcolumn("SELECT COUNT(1) FROM ".tablename($this->table_user)." where weid = :weid And schoolid = :schoolid",array(':weid'=>$weid, ':schoolid'=>$schoolid));
        }
        if ($groupid == 2) {
            $total = pdo_fetchcolumn("SELECT COUNT(1) FROM ".tablename($this->table_teachers)." where weid = :weid And schoolid = :schoolid",array(':weid'=>$weid, ':schoolid'=>$schoolid));
        }
        if ($groupid == 3) {
            $total = pdo_fetchcolumn("SELECT COUNT(1) FROM ".tablename($this->table_students)." where weid = :weid And schoolid = :schoolid",array(':weid'=>$weid, ':schoolid'=>$schoolid));
        }
    }
    $tp = ceil($total/$psize);
    //作业群发
}elseif($type == 3){
    if($schooltype == true){
        $kc_id = $_GPC['kc_id'];
        $pindex = max(1, intval($_GPC['page']));
        $total = pdo_fetchall("SELECT distinct sid FROM ".tablename($this->table_order)." where weid = :weid And schoolid = :schoolid And kcid = :kc_id and type = 1 and status = 2 ",array(':weid'=>$weid, ':schoolid'=>$schoolid, ':kc_id'=>$kc_id));
    }elseif($schooltype == false){
        $bj_id = $_GPC['bj_id'];
        $pindex = max(1, intval($_GPC['page']));
        $total = pdo_fetchcolumn("SELECT COUNT(1) FROM ".tablename($this->table_students)." where weid = :weid And schoolid = :schoolid And bj_id = :bj_id",array(':weid'=>$weid, ':schoolid'=>$schoolid, ':bj_id'=>$bj_id));
        $tp = ceil($total/$psize);
    }
}*/



//var_dump($total);
//die();
include $this->template('web/sendmsgbyall');
?>
