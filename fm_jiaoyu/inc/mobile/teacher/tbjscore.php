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
mload()->model('tea');
$xq_list = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = '{$weid}' AND schoolid ={$schoolid} And type = 'xq_score' ORDER BY ssort DESC");
//查询是否用户登录
$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $userid['id']));
$tid_global = $it['tid'];

$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
if(!empty($userid['id'])){
    if($_GPC['op'] == 'ajax_get'){
        $scoretime = strtotime($_GPC['out_scoretime']);
        $back_result = GetBjScore($schoolid,$weid,$scoretime);
        die(json_encode($back_result));
    }
   // $_GPC['out_scoretime'] = '2018-09-03';
    if(empty($_GPC['out_scoretime'])){
        $scoretime = time();
    }elseif(!empty($_GPC['out_scoretime'])){
        $scoretime = strtotime($_GPC['out_scoretime']);
    }
    $condition .= " and scoretime = '{$scoretime}' ";
    $back_result = GetBjScore($schoolid,$weid,$scoretime);
    $result = $back_result['data'];
    $bjcount = count($result);
    //var_dump($xueqi_rank_arr);
    include $this->template(''.$school['style3'].'/tbjscore');
}else{
    session_destroy();
    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
    header("location:$stopurl");
}
?>