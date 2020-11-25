<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_W, $_GPC;
$weid = $_W ['uniacid'];
$tid = intval($_GPC['tid']);
$schoolid = intval($_GPC['schoolid']);
$type = intval($_GPC['type']);
$openid = $_GPC['openid'];
$starttime1 = $_GPC['starttime1'];
$endtime1 = $_GPC['endtime1'];
$orderzong = $_GPC['orderzong'];
if($orderzong == 0){
    $orderzong = 0;
}
$todaystu = $_GPC['todaystu'];
$checklogzj = $_GPC['checklogzj'];
$xszj = $_GPC['xszj'];

$condition1 = " AND createtime > '{$starttime1}' AND createtime < '{$endtime1}'";

$kaishi = date("Y年m月d日",$starttime1);
$jieshu = date("Y年m月d日",$endtime1);

//检查是否用户登陆
$it = pdo_fetch("SELECT * FROM " . GetTableName('user') . " where :schoolid = schoolid And :weid = weid And :openid = openid and :tid=tid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ":tid"=>$tid));
$school = pdo_fetch("SELECT style3,title,spic,tpic,logo FROM " . GetTableName('index') . " where weid = :weid AND id = :id ", array(':weid' => $weid, ':id' => $schoolid));
$tid_global = $it['tid'];
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if($it){
    mload()->model('kc');
    if($operation == 'display'){
        $item = pdo_fetchall("SELECT * FROM " . GetTableName('tcourse') . " WHERE schoolid = '{$schoolid}' ");
        $res = array();
        foreach($item as $k=>$v){
            $arr[$k]['name'] = $v['name'];
            $zaidu = pdo_fetchcolumn("SELECT count(id) FROM " . GetTableName('coursebuy') . " WHERE schoolid = '{$schoolid}' and kcid = '{$v['id']}' and is_change != 1");
            $xiaoke = pdo_fetchcolumn("SELECT sum(costnum) FROM " . GetTableName('kcsign') . " WHERE schoolid = '{$schoolid}' and kcid = '{$v['id']}' and status = 2 and tid = 0 and sid != 0 $condition1");
            $danjia = pdo_fetchcolumn("SELECT RePrice FROM " . GetTableName('tcourse') . " WHERE schoolid = '{$schoolid}' and id = '{$v['id']}'");
            $arr[$k]['stuzong'] = $zaidu;
            $arr[$k]['xiaoke'] = $xiaoke;
            $arr[$k]['zongjia'] = $xiaoke * $danjia;
        }
        if($item['kc_type'] == 1){
            include $this->template(''.$school['style3'].'/tmybaobiao');
        }else{
            include $this->template(''.$school['style3'].'/tmybaobiao');
        }
    }

}else{
    session_destroy();
    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
    header("location:$stopurl");
    exit;
}

?>