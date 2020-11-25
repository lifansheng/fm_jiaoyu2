<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_W, $_GPC;
$weid = $_W['uniacid'];
$openid = $_W['openid'];
$schoolid = intval($_GPC['schoolid']);
$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id", array(':weid' => $weid, ':id' => $schoolid));
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if($operation == 'display'){
    $it = pdo_fetch("SELECT tid,uid,id FROM " . tablename($this->table_user) . " where weid = :weid And :schoolid = schoolid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0));			
    $teacher = pdo_fetch("SELECT status FROM " . tablename($this->table_teachers) . " where id = :id", array(':id' => $it['tid']));
    if(!is_TestFz()){
        message('您无权查看本页面');
    }
    if($teacher['status'] != 2){
        message('您无权查看本页面');
    }
    if($it){
        $kclist = pdo_fetchall("SELECT id,name FROM ".GetTableName('tcourse')." WHERE schoolid = '{$schoolid}' AND weid = '{$weid}'");
        $list = pdo_fetchall("SELECT * FROM ".GetTableName('qzkh')." WHERE schoolid = '{$schoolid}' AND weid = '{$weid}'");
        include $this->template(''.$school['style3'].'/tqzkh');
    }else{
        session_destroy();
        $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
        header("location:$stopurl");
        exit;
    }
}
?>