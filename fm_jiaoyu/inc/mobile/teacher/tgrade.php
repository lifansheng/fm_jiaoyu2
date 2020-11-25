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
$op = $_GPC['op'] ? $_GPC['op'] : 'display';
//查询是否用户登录
mload()->model('user');
$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where  weid = :weid And schoolid = :schoolid And openid = :openid And sid = :sid ", array(
	':weid' => $weid,
	':schoolid' => $schoolid,
	':openid' => $openid,
	':sid' => 0
));
$tid_global = $it['tid'] ? $it['tid'] : 0;
$AllKc = pdo_fetchAll("SELECT id,name,thumb,schoolid FROM " . GetTableName('tcourse') . " WHERE schoolid = '{$schoolid}' And weid = '{$weid}' and FIND_IN_SET('{$tid_global}',tid)");
if(!empty($_GPC['kcid'])){
    $kcid = $_GPC['kcid'];
    $condition = " AND kcid = '{$kcid}'";
}else{
    $kcid = '-1';
}
$school = pdo_fetch("SELECT style3,title,spic,tpic,title,headcolor,thumb FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id", array(':weid' => $weid, ':id' => $schoolid));
$category = pdo_fetchall("SELECT * FROM " . tablename($this->table_classify) . " WHERE weid = :weid AND schoolid = :schoolid ", array(':weid' => $weid, ':schoolid' => $schoolid), 'sid');

if(!empty($it)){
    if($op == 'display'){
        $kcpingjia = pdo_fetchAll("SELECT sid,createtime,anony,content,kcid FROM " . tablename($this->table_kcpingjia) . " WHERE schoolid = '{$schoolid}' And weid = '{$weid}' and tid ='{$tid_global}' and type=1 $condition GROUP BY kcid,sid ORDER BY id DESC LIMIT 0,5");
        //获取当前课程信息
        $kc = pdo_fetch("SELECT * FROM " . GetTableName('tcourse') . " WHERE id = '{$kcid}'");
        $pfxmAVG = pdo_fetchAll("SELECT AVG(star) as avg,pfxmid FROM " . tablename($this->table_kcpingjia) . " WHERE tid  = '{$tid_global}' $condition GROUP BY pfxmid ORDER BY pfxmid");
        //获取总的平均评论值
        foreach($pfxmAVG as $k => $v){
            $avg = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE sid = '{$v['pfxmid']}'");
            $pfxmAVG[$k]['avgname'] = $avg['sname'];
            $pfxmAVG[$k]['avg'] = round($v['avg'],1);
        }
        //每个学生的评论
        foreach($kcpingjia as $k1 => $v1){
            $pfxm = pdo_fetchAll("SELECT pfxmid,star FROM " . tablename($this->table_kcpingjia) . " WHERE sid ='{$v1['sid']}' and tid = '{$tid_global}' and kcid='{$v1['kcid']}' ORDER BY pfxmid");
           
            foreach($pfxm as $k2 => $v2){
                $sname = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE sid = '{$v2['pfxmid']}'");
                $kcpingjia[$k1]['pfxm'][$k2]['sname'] = $sname['sname'];
                $kcpingjia[$k1]['pfxm'][$k2]['star'] = $v2['star'];
            }
            //评价人
            $student = pdo_fetch("SELECT s_name FROM " . GetTableName('students') . " WHERE id = '{$v1['sid']}'");
            $kcpingjia[$k1]['s_name'] = $v1['anony'] == 0 ? $student['s_name'] : '匿名';
        }
        include $this->template(''.$school['style3'].'/tgrade');
    }elseif($op == 'scroll_more'){
        $time = $_GPC['LiData']['time'];
        $kcid = $_GPC['LiData']['kcid'];
        if($kcid != -1){
            $condition = " AND kcid = '{$kcid}'";
        }
        $limit_start = $time + 1;
        $kcpingjia = pdo_fetchAll("SELECT sid,createtime,anony,content,kcid FROM " . tablename($this->table_kcpingjia) . " WHERE schoolid = '{$schoolid}' And weid = '{$weid}' and tid ='{$tid_global}' and type=1 $condition GROUP BY kcid,sid ORDER BY id DESC LIMIT {$limit_start},5 ");
        //每个学生的评论
        foreach($kcpingjia as $k1 => $v1){
            $pfxm = pdo_fetchAll("SELECT pfxmid,star FROM " . tablename($this->table_kcpingjia) . " WHERE sid ='{$v1['sid']}' and tid = '{$tid_global}' and kcid='{$v1['kcid']}' ORDER BY pfxmid");
           
            foreach($pfxm as $k2 => $v2){
                $sname = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE sid = '{$v2['pfxmid']}'");
                $kcpingjia[$k1]['pfxm'][$k2]['sname'] = $sname['sname'];
                $kcpingjia[$k1]['pfxm'][$k2]['star'] = $v2['star'];
            }
            //评价人
            $student = pdo_fetch("SELECT s_name FROM " . GetTableName('students') . " WHERE id = '{$v1['sid']}'");
            $kcpingjia[$k1]['s_name'] = $v1['anony'] == 0 ? $student['s_name'] : '匿名';
            $kcpingjia[$k1]['location'] = $k1 + $limit_start;
        }
        // var_dump($kcpingjia);die;
        include $this->template('comtool/tgrade');
    }
}else{
	session_destroy();
    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
    header("location:$stopurl");
	exit;
}        
?>