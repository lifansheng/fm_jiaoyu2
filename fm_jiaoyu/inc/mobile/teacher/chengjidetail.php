<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_W, $_GPC;
$weid = $_W['uniacid'];
$schoolid = intval($_GPC['schoolid']);
$openid = $_W['openid'];
$time = $_GPC['time'];

//查询是否用户登录
$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $userid['id']));
$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));

$tid_global = $it['tid'];

$schooltype = $_W['schooltype'];

mload()->model('tea');
if(!empty($userid['id'])){

    if($_GPC['op'] == 'ajax_get'){
        $qhid = $_GPC['qhid'];
        $bjid = $_GPC['njid'];

        $back_result = GetStuScore($tid_global,$schoolid,$qhid,$bjid,$weid);

        die(json_encode($back_result));
    }

    $mynjlist = is_njzr($tid_global);
    $nowtime = time();
    $bjlists = get_myskbj($tid_global);//默认取未毕业班级
    foreach($bjlists as $i => $v){
        $total = pdo_fetchcolumn("select COUNT(id) FROM ".tablename('wx_school_students')." WHERE bj_id = '{$v['bj_id']}' ");
        $bjinfo = pdo_fetch("SELECT is_over,sname FROM " . tablename('wx_school_classify') . " where sid = :sid", array(':sid' => $v['bj_id']));
        $bjlists[$i]['old_sname'] = $bjinfo['sname'];//只有名字，没有其他信息
        $bjlists[$i]['sid'] = $v['bj_id'];
        $bjlists[$i]['is_over'] = $bjinfo['is_over'];
    }
    if(is_xiaozhang($tid_global) || IsHasQx($tid_global,2002402,2,$schoolid)){//校长或拥有权限  取全校数据
        $condition = '';
        $datas = pdo_fetchall("SELECT sid,sname,is_over FROM ".tablename('wx_school_classify')." WHERE  type ='theclass' And schoolid='{$schoolid}' $condition ORDER BY parentid ASC ");
        foreach($datas as $key => $row){
            $total = pdo_fetchcolumn("select COUNT(id) FROM ".tablename('wx_school_students')." WHERE bj_id = '{$row['sid']}' ");
            $datas[$key]['old_sname'] = $row['sname'];
        }
    }elseif($mynjlist){
        $getallnj = getallnj($tid_global);
        $condition = '';
        $datas = array();
        $datas = array_merge($datas,$bjlists);
        foreach($getallnj as $val){
            $classify = pdo_fetchall("SELECT sid,sname,is_over FROM ".tablename('wx_school_classify')." WHERE  type ='theclass' And schoolid='{$schoolid}' And parentid='{$val['sid']}' $condition ORDER BY ssort DESC ");
            foreach($classify as $key => $row){
                $total = pdo_fetchcolumn("select COUNT(id) FROM ".tablename('wx_school_students')." WHERE bj_id = '{$row['sid']}' ");
                $classify[$key]['old_sname'] = $row['sname'];
            }
            $datas = array_merge($datas,$classify);
        }
    }else{ //普通老师取授课班级
        $datas = $bjlists;//默认取未毕业班级
    }
    $datas = array_sorts($datas,'is_over','asc');
    $bj =$datas;
    $qh = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = '{$weid}' AND schoolid ={$schoolid} And type = 'score'  AND is_show_qh = 1 ORDER BY ssort DESC");

    if(!empty($_GPC['search_qh'])){
        $this_qhid = $_GPC['search_qh'];
    }else{
        $this_qhid =$qh[0]['sid'];
    }
    if(!empty($_GPC['search_bj'])){
        $this_bjid = $_GPC['search_bj'];
    }else{
        $this_bjid =$bj[0]['sid'];
    }
    $back_result = GetStuScore($tid_global,$schoolid,$this_qhid,$this_bjid,$weid);
    // var_dump($back_result);
    $this_qhinfo =  pdo_fetch("SELECT sid,sname,qhtype,qh_bjlist,addedinfo,is_review FROM " . tablename($this->table_classify) . " where weid = '{$weid}' AND schoolid ={$schoolid} and sid='{$this_qhid}' And type = 'score' ");
    $this_bjinfo =  pdo_fetch("SELECT sid,sname,qhtype,qh_bjlist,addedinfo,is_review FROM " . tablename($this->table_classify) . " where weid = '{$weid}' AND schoolid ={$schoolid} and sid='{$this_bjid}' And type = 'theclass' ");
    include $this->template(''.$school['style3'].'/chengjidetail');
}else{
    session_destroy();
    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
    header("location:$stopurl");
}
?>